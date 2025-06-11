<?php

namespace App\Traits;

use App\Models\SupplyChain\InventoryItem;
use App\Models\SupplyChain\InventoryTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait InventoryProcessing
{
    /**
     * Processa as atualizações de inventário para os itens do recebimento
     *
     * @param array $items Itens do recebimento
     * @param int $locationId ID do local de estoque
     * @param string $receiptNumber Número do recebimento
     * @param int $receiptId ID do recebimento
     * @return void
     */
    protected function processInventoryUpdates($items, $locationId, $receiptNumber, $receiptId)
    {
        foreach ($items as $item) {
            if (empty($item['accepted_quantity']) || empty($item['product_id'])) {
                continue;
            }

            $productId = $item['product_id'];
            $acceptedQty = (float)$item['accepted_quantity'];
            $unitCost = (float)($item['unit_cost'] ?? 0);

            // Log da atualização de inventário
            Log::info('Processando atualização de inventário', [
                'product_id' => $productId,
                'accepted_quantity' => $acceptedQty,
                'location_id' => $locationId,
                'receipt_id' => $receiptId
            ]);

            // Atualizar ou criar item de inventário
            $this->updateOrCreateInventoryItem(
                $productId, 
                $locationId, 
                $acceptedQty, 
                $unitCost, 
                $receiptId, 
                $receiptNumber
            );
        }
    }

    /**
     * Atualiza ou cria um item de inventário
     *
     * @param int $productId
     * @param int $locationId
     * @param float $quantity
     * @param float $unitCost
     * @param int $receiptId
     * @param string $receiptNumber
     * @return void
     */
    protected function updateOrCreateInventoryItem($productId, $locationId, $quantity, $unitCost, $receiptId, $receiptNumber)
    {
        // Verificar se já existe um item de inventário para este produto
        $inventoryItem = InventoryItem::where('product_id', $productId)
            ->where('location_id', $locationId)
            ->first();

        if ($inventoryItem) {
            // Atualizar quantidade existente
            $inventoryItem->quantity_on_hand += $quantity;
            $inventoryItem->save();
            
            Log::info('Item de inventário atualizado', [
                'inventory_item_id' => $inventoryItem->id,
                'new_quantity' => $inventoryItem->quantity_on_hand
            ]);
        } else {
            // Criar novo item de inventário
            $inventoryItem = new InventoryItem([
                'product_id' => $productId,
                'location_id' => $locationId,
                'quantity_on_hand' => $quantity,
                'quantity_allocated' => 0,
                'unit_cost' => $unitCost,
            ]);
            $inventoryItem->save();
            
            Log::info('Novo item de inventário criado', [
                'inventory_item_id' => $inventoryItem->id,
                'product_id' => $productId,
                'location_id' => $locationId
            ]);
        }

        // Registrar a transação de inventário
        $this->createInventoryTransaction(
            $inventoryItem->id,
            $productId,
            $locationId,
            $quantity,
            $unitCost,
            $receiptId,
            $receiptNumber
        );
    }

    /**
     * Cria uma transação de inventário
     *
     * @param int $inventoryItemId
     * @param int $productId
     * @param int $locationId
     * @param float $quantity
     * @param float $unitCost
     * @param int $referenceId
     * @param string $referenceNumber
     * @return void
     */
    protected function createInventoryTransaction($inventoryItemId, $productId, $locationId, $quantity, $unitCost, $referenceId, $referenceNumber)
    {
        $transaction = new InventoryTransaction([
            'transaction_number' => InventoryTransaction::generateTransactionNumber(),
            'transaction_type' => 'purchase_receipt',
            'reference_type' => 'goods_receipt',
            'reference_id' => $referenceId,
            'inventory_item_id' => $inventoryItemId,
            'product_id' => $productId,
            'source_location_id' => null, // Entrada no estoque, não há origem
            'destination_location_id' => $locationId,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'transaction_date' => now(),
            'notes' => "Recebimento #{$referenceNumber}",
            'created_by' => Auth::id(),
        ]);
        
        $transaction->save();
        
        Log::info('Transação de inventário criada', [
            'transaction_id' => $transaction->id,
            'product_id' => $productId,
            'quantity' => $quantity
        ]);
    }
}
