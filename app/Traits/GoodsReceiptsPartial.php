<?php

namespace App\Traits;

use App\Models\SupplyChain\GoodsReceiptItem;
use App\Models\SupplyChain\PurchaseOrder;
use App\Models\SupplyChain\PurchaseOrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Trait para processamento de recebimentos parciais
 */
trait GoodsReceiptsPartial
{
    /**
     * Atualiza o status de um item específico da ordem de compra
     * 
     * @param int $purchaseOrderItemId ID do item da ordem de compra
     * @param float $totalReceived Quantidade total recebida até o momento
     * @param float $expectedQuantity Quantidade total esperada
     * @return void
     */
    protected function updatePurchaseOrderItemStatus($purchaseOrderItemId, $totalReceived, $expectedQuantity)
    {
        try {
            $purchaseOrderItem = PurchaseOrderItem::find($purchaseOrderItemId);
            
            if ($purchaseOrderItem) {
                $status = 'pending';
                
                if ($totalReceived >= $expectedQuantity) {
                    $status = 'completed';
                } elseif ($totalReceived > 0) {
                    $status = 'partially_received';
                }
                
                $purchaseOrderItem->update([
                    'received_quantity' => $totalReceived,
                    'status' => $status,
                    'remaining_quantity' => max(0, $expectedQuantity - $totalReceived)
                ]);
                
                // Atualizar o status da ordem de compra
                $this->updatePurchaseOrderStatus($purchaseOrderItem->purchase_order_id);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar status do item da ordem de compra', [
                'purchase_order_item_id' => $purchaseOrderItemId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Atualiza o status da ordem de compra com base nos itens recebidos
     * 
     * @param int $purchaseOrderId ID da ordem de compra
     * @return void
     */
    protected function updatePurchaseOrderStatus($purchaseOrderId)
    {
        try {
            $purchaseOrder = PurchaseOrder::with('items')->find($purchaseOrderId);
            
            if (!$purchaseOrder) {
                return;
            }
            
            $allItemsReceived = true;
            $anyItemReceived = false;
            $allItemsPending = true;
            
            foreach ($purchaseOrder->items as $item) {
                if ($item->status === 'partially_received') {
                    $allItemsReceived = false;
                    $anyItemReceived = true;
                    $allItemsPending = false;
                } elseif ($item->status === 'completed') {
                    $anyItemReceived = true;
                    $allItemsPending = false;
                } elseif ($item->status === 'pending') {
                    $allItemsReceived = false;
                }
            }
            
            $newStatus = $purchaseOrder->status;
            
            if ($allItemsReceived) {
                $newStatus = 'completed';
            } elseif ($anyItemReceived) {
                $newStatus = 'partially_received';
            } elseif ($allItemsPending) {
                $newStatus = 'pending';
            }
            
            if ($purchaseOrder->status !== $newStatus) {
                $purchaseOrder->update(['status' => $newStatus]);
                
                Log::info('Status da ordem de compra atualizado', [
                    'purchase_order_id' => $purchaseOrder->id,
                    'old_status' => $purchaseOrder->status,
                    'new_status' => $newStatus
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar status da ordem de compra', [
                'purchase_order_id' => $purchaseOrderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Processa os itens do recebimento, tratando recebimentos parciais
     * 
     * @param array $items Itens a serem processados
     * @param \App\Models\SupplyChain\GoodsReceipt $receipt Instância do recebimento
     * @return array Lista de IDs de produtos processados
     */
    protected function processReceiptItems($items, $receipt)
{
    $processedProductIds = [];
    
    // First pass: calculate totals per product
    $productTotals = [];
    foreach ($items as $item) {
        if (empty($item['accepted_quantity']) && empty($item['rejected_quantity'])) {
            continue;
        }
        
        $productId = $item['product_id'];
        if (!isset($productTotals[$productId])) {
            $productTotals[$productId] = [
                'accepted' => 0,
                'rejected' => 0,
                'expected' => $item['ordered_quantity'] ?? $item['quantity'] ?? 0,
                'unit_cost' => $item['unit_cost'] ?? 0,
                'previously_received' => $item['previously_received'] ?? 0, // Add previously received quantity
            ];
        }
        
        $productTotals[$productId]['accepted'] += (float)($item['accepted_quantity'] ?? 0);
        $productTotals[$productId]['rejected'] += (float)($item['rejected_quantity'] ?? 0);
    }
    
    // Second pass: process each item
    foreach ($items as $item) {
        if (empty($item['accepted_quantity']) && empty($item['rejected_quantity'])) {
            continue;
        }
        
        $productId = $item['product_id'];
        $purchaseOrderItemId = $item['purchase_order_item_id'] ?? null;
        $processedProductIds[] = $productId;
        
        // Get quantities for this specific entry
        $acceptedQty = (float)($item['accepted_quantity'] ?? 0);
        $rejectedQty = (float)($item['rejected_quantity'] ?? 0);
        $unitCost = (float)($item['unit_cost'] ?? 0);
        $expectedQuantity = $productTotals[$productId]['expected'] ?? 0;
        $previouslyReceived = (float)($productTotals[$productId]['previously_received'] ?? 0);
        
        // Get totals for this product in current receipt
        $totalReceivedInThisReceipt = $productTotals[$productId]['accepted'] ?? 0;
        $totalRejectedInThisReceipt = $productTotals[$productId]['rejected'] ?? 0;
        
        // Get the remaining quantity from the purchase order
        $remainingFromPO = $expectedQuantity - $previouslyReceived;
        
        // If we're editing, adjust the remaining quantity by adding back what was previously received in this receipt
        if (isset($item['id'])) {
            $existingItem = $receipt->items()->find($item['id']);
            if ($existingItem) {
                $remainingFromPO += $existingItem->accepted_quantity;
            }
        }
        
        // Ensure we don't receive more than remaining
        $adjustedQty = min($acceptedQty, $remainingFromPO);
        
        // Calculate total received including previous receipts
        $currentReceiptReceived = $receipt->items()
            ->where('product_id', $productId)
            ->where('id', '!=', $item['id'] ?? 0) // Exclude current item if editing
            ->sum('accepted_quantity');
            
        // Calculate total received as: previous receipts + current receipt's other items + adjusted quantity
        $totalReceived = $previouslyReceived + $currentReceiptReceived + $adjustedQty;
        
        // Calculate remaining quantity, ensuring it's never negative
        $remainingQty = max(0, $expectedQuantity - $totalReceived);
        
        Log::debug('Cálculo de quantidade restante', [
            'product_id' => $productId,
            'expected_quantity' => $expectedQuantity,
            'previously_received' => $previouslyReceived,
            'remaining_from_po' => $remainingFromPO,
            'current_receipt_received' => $currentReceiptReceived,
            'requested_qty' => $acceptedQty,
            'adjusted_qty' => $adjustedQty,
            'total_received' => $totalReceived,
            'remaining_quantity' => $remainingQty,
            'is_editing' => isset($item['id']) ? 'sim' : 'não',
            'purchase_order_item_id' => $purchaseOrderItemId
        ]);
        
        // Update the accepted quantity to the adjusted value
        $acceptedQty = $adjustedQty;
        
        // Mark all previous items as no longer the latest
        $receipt->items()
            ->where('product_id', $productId)
            ->where('is_latest', true)
            ->update(['is_latest' => false]);
        
        // Create a new record for this partial receipt
        $receiptItem = new GoodsReceiptItem([
            'goods_receipt_id' => $receipt->id,
            'purchase_order_item_id' => $purchaseOrderItemId,
            'product_id' => $productId,
            'expected_quantity' => $expectedQuantity,
            'quantity' => $expectedQuantity,
            'received_quantity' => $acceptedQty,
            'accepted_quantity' => $acceptedQty,
            'rejected_quantity' => $rejectedQty,
            'total_accepted' => $totalReceived, // Total including previous receipts
            'total_rejected' => $totalRejectedInThisReceipt,
            'remaining_quantity' => $remainingQty,
            'unit_cost' => $unitCost,
            'subtotal' => ($acceptedQty * $unitCost),
            'status' => $remainingQty > 0 ? 'partially_accepted' : 'accepted',
            'is_latest' => true,
            'received_by' => Auth::id(),
            'received_at' => now(),
            'notes' => $item['notes'] ?? null,
        ]);
        
        $receipt->items()->save($receiptItem);
        
        // Update purchase order item status if there's a purchase order item ID
        if ($purchaseOrderItemId) {
            $this->updatePurchaseOrderItemStatus(
                $purchaseOrderItemId,
                $totalReceived, // Total received including previous receipts
                $expectedQuantity
            );
        }
        
        Log::info('Processed receipt item', [
            'receipt_id' => $receipt->id,
            'product_id' => $productId,
            'accepted_quantity' => $acceptedQty,
            'rejected_quantity' => $rejectedQty,
            'total_previously_received' => $previouslyReceived,
            'total_accepted' => $totalReceived,
            'total_rejected' => $totalRejectedInThisReceipt,
            'remaining_quantity' => $remainingQty,
            'status' => $remainingQty > 0 ? 'partially_accepted' : 'accepted'
        ]);
    }
    
    return $processedProductIds;
}
}
