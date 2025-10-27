<?php

namespace App\Livewire\SupplyChain;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\SupplyChain\GoodsReceipt;
use App\Models\SupplyChain\GoodsReceiptItem;
use App\Models\SupplyChain\PurchaseOrder;
use App\Models\SupplyChain\PurchaseOrderItem;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\InventoryItem;
use App\Models\SupplyChain\InventoryLocation;
use App\Models\Inventory\InventoryTransaction;
use App\Models\Product\Product;
use App\Traits\GoodsReceiptsPartial;
use App\Traits\InventoryProcessing;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class GoodsReceipts extends Component
{
    use WithPagination, WithFileUploads, GoodsReceiptsPartial, InventoryProcessing;
    
    public $search = '';
    public $perPage = 10;
    public $statusFilter = '';
    public $supplierFilter = '';
    public $locationFilter = '';
    public $sortField = 'receipt_date';
    public $sortDirection = 'desc';
    
    // Create/Edit Modal Properties
    public $showModal = false;
    public $editMode = false;
    public $goodsReceipt = [];
    public $receiptItems = [];
    public $selectedPurchaseOrder = null;
    public $selectedSupplier = null;
    public $selectedLocation = null;
    public $purchaseOrderItems = [];
    
    // View Modal Properties
    public $showViewModal = false;
    public $viewingReceipt = null;
    public $activeTab = 'details';
    
    // Delete Confirmation Properties
    public $showConfirmDelete = false;
    public $deleteId = null;

    // O método addItem() foi removido
    
    protected $listeners = [
        'productSelected' => 'addProduct',
        'refreshGoodsReceipts' => '$refresh',
        'receiptItemUpdated' => 'onReceiptItemUpdated'
    ];
    
    public function mount()
    {
        $this->resetReceipt();
    }
    
    protected function resetReceipt()
    {
        $this->goodsReceipt = [
            'receipt_number' => 'GR' . now()->format('ymd') . rand(1000, 9999),
            'purchase_order_id' => null,
            'supplier_id' => '',
            'location_id' => '',
            'receipt_date' => now()->format('Y-m-d'),
            'reference_number' => '',
            'status' => 'pending',
            'notes' => '',
            'total_amount' => 0,
        ];
        
        $this->receiptItems = [];
        $this->purchaseOrderItems = [];
        $this->selectedPurchaseOrder = null;
        $this->selectedSupplier = null;
        $this->selectedLocation = null;
        $this->editMode = false;
        $this->showModal = false;
    }
    
    protected function loadPurchaseOrders()
    {
        // This method is not needed as we load purchase orders in the render method
    }
    
    protected function loadSuppliers()
    {
        // This method is not needed as we load suppliers in the render method
    }
    
    protected function loadLocations()
    {
        // This method is not needed as we load locations in the render method
    }
    
    public function updated($propertyName)
    {
        // Handle updates to receipt items
        if (str_starts_with($propertyName, 'receiptItems.')) {
            $parts = explode('.', $propertyName);
            if (count($parts) >= 3) {
                $index = $parts[1];
                $this->updateItemCalculations($index);
            }
        }
    }
    
    public function onReceiptItemUpdated($index, $field, $value)
    {
        if (isset($this->receiptItems[$index])) {
            $this->receiptItems[$index][$field] = $value;
            $this->updateItemCalculations($index);
        }
    }
    
    protected function updateItemCalculations($index)
    {
        if (!isset($this->receiptItems[$index])) {
            return;
        }
        
        $item = &$this->receiptItems[$index];
        
        // Ensure quantity is a valid number
        $quantity = isset($item['quantity']) ? (float)$item['quantity'] : 0;
        $rejected = isset($item['rejected_quantity']) ? (float)$item['rejected_quantity'] : 0;
        
        // Ensure rejected quantity doesn't exceed quantity
        if ($rejected > $quantity) {
            $rejected = $quantity;
            $item['rejected_quantity'] = $rejected;
        }
        
        // Calculate accepted quantity
        $accepted = $quantity - $rejected;
        $item['accepted_quantity'] = $accepted;
        
        // Update line total if unit cost exists
        if (isset($item['unit_cost'])) {
            $item['subtotal'] = $accepted * (float)$item['unit_cost'];
        }
        
        // Update remaining quantity
        if (isset($item['ordered_quantity']) && isset($item['previously_received'])) {
            $totalReceived = $item['previously_received'] + $accepted;
            $item['remaining_quantity'] = $item['ordered_quantity'] - $totalReceived;
        }
    }
    
    protected $queryString = [
        'search' => ['except' => '','as' => 's'],
        'statusFilter' => ['except' => '','as' => 'status'],
        'supplierFilter' => ['except' => '','as' => 'supplier'],
        'locationFilter' => ['except' => '','as' => 'location'],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'receipt_date'],
        'sortDirection' => ['except' => 'desc']
    ];
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingStatusFilter()
    {
        $this->resetPage();
    }
    
    public function updatingSupplierFilter()
    {
        $this->resetPage();
    }
    
    public function updatingLocationFilter()
    {
        $this->resetPage();
    }
    
    public function updatingPerPage()
    {
        $this->resetPage();
    }
    
    public function resetFilters()
    {
        $this->reset(['search', 'statusFilter', 'supplierFilter', 'locationFilter']);
        $this->resetPage();
    }
    
    public function sortBy($field)
    {
        if($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }
    
    public function render()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $locations = InventoryLocation::orderBy('name')->get();
        
        $goodsReceiptsQuery = GoodsReceipt::query()
            ->with(['supplier', 'purchaseOrder', 'location', 'receiver', 'items'])
            ->when($this->search, function($query) {
                $search = '%' . $this->search . '%';
                return $query->where(function($q) use ($search) {
                    $q->where('receipt_number', 'like', $search)
                      ->orWhereHas('supplier', function($sq) use ($search) {
                          $sq->where('name', 'like', $search);
                      })
                      ->orWhereHas('purchaseOrder', function($sq) use ($search) {
                          $sq->where('order_number', 'like', $search);
                      });
                });
            })
            ->when($this->statusFilter, function($query) {
                return $query->where('status', $this->statusFilter);
            })
            ->when($this->supplierFilter, function($query) {
                return $query->where('supplier_id', $this->supplierFilter);
            })
            ->when($this->locationFilter, function($query) {
                return $query->where('location_id', $this->locationFilter);
            })
            ->orderBy($this->sortField, $this->sortDirection);
        
        $goodsReceipts = $goodsReceiptsQuery->paginate($this->perPage);

        // Get purchase orders for the dropdown - only completed purchase orders that don't have a completed goods receipt
        $purchaseOrders = PurchaseOrder::where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->take(100) // Limit to 100 most recent to improve performance
            ->get();
        
        return view('livewire.supply-chain.goods-receipts', [
            'goodsReceipts' => $goodsReceipts,
            'suppliers' => $suppliers,
            'locations' => $locations,
            'purchaseOrders' => $purchaseOrders,
            'statuses' => [
                'pending' => __('messages.pending'),
                'partially_processed' => __('messages.partially_processed'),
                'completed' => __('messages.completed'),
                'discrepancy' => __('messages.discrepancy'),
            ]
        ]);
    }
    
    public function openCreateModal()
    {
        $this->resetValidation();
        $this->resetExcept(['search', 'perPage', 'statusFilter', 'supplierFilter', 'locationFilter', 'sortField', 'sortDirection']);
        
        $this->goodsReceipt = [
            'receipt_number' => 'GR' . now()->format('ymd') . rand(1000, 9999),
            'purchase_order_id' => null,
            'supplier_id' => '',
            'location_id' => '',
            'receipt_date' => now()->format('Y-m-d'),
            'status' => 'pending',
            'notes' => '',
        ];
        
        $this->receiptItems = [];
        $this->purchaseOrderItems = [];
        $this->showModal = true;
        $this->editMode = false;
    }
    
    public function viewReceipt($id)
    {
        $this->viewingReceipt = GoodsReceipt::with(['supplier', 'purchaseOrder', 'location', 'receiver', 'items.product'])->find($id);
        
        if(!$this->viewingReceipt) {
            return $this->dispatch('notify', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => __('messages.goods_receipt_not_found')
            ]);
        }
        
        $this->showViewModal = true;
    }
    
    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewingReceipt = null;
    }
    
    public function editReceipt($id)
    {
        $this->resetValidation();
        $this->reset(['goodsReceipt', 'receiptItems', 'editMode']);
        
        $receipt = GoodsReceipt::with(['items.product', 'purchaseOrder.items'])->findOrFail($id);
        
        $this->goodsReceipt = [
            'id' => $receipt->id,
            'receipt_number' => $receipt->receipt_number,
            'purchase_order_id' => $receipt->purchase_order_id,
            'supplier_id' => $receipt->supplier_id,
            'location_id' => $receipt->location_id,
            'receipt_date' => $receipt->receipt_date->format('Y-m-d'),
            'reference_number' => $receipt->reference_number,
            'status' => $receipt->status,
            'notes' => $receipt->notes,
            'total_amount' => $receipt->total_amount,
        ];
        
        // For partially processed receipts, we need to load the PO items to calculate remaining quantities
        if ($receipt->status === 'partially_processed') {
            // Load PO items with previously received quantities
            $this->loadPurchaseOrderItems();
            
            // Update with actual received quantities from this receipt
            foreach ($receipt->items as $item) {
                foreach ($this->receiptItems as &$receiptItem) {
                    if ($receiptItem['product_id'] == $item->product_id) {
                        // Store the original received quantities
                        $receiptItem['original_accepted'] = (float) $item->accepted_quantity;
                        $receiptItem['original_rejected'] = (float) $item->rejected_quantity;
                        
                        // Calculate remaining quantity to be received
                        $remainingQty = $receiptItem['ordered_quantity'] - $receiptItem['previously_received'];
                        
                        // Set the current quantities for editing
                        $receiptItem['quantity'] = max(0, $remainingQty); // This is the remaining quantity to be received
                        $receiptItem['accepted_quantity'] = (float) $item->accepted_quantity;
                        $receiptItem['rejected_quantity'] = (float) $item->rejected_quantity;
                        $receiptItem['unit_cost'] = (float) $item->unit_cost;
                        $receiptItem['goods_receipt_item_id'] = $item->id;
                        
                        // Calculate remaining quantity that can be received
                        $totalReceived = $receiptItem['previously_received'] + $item->accepted_quantity;
                        $remaining = $receiptItem['ordered_quantity'] - $totalReceived;
                        
                        // Ensure remaining quantity is never negative
                        $receiptItem['remaining_quantity'] = max(0, $remaining);
                        
                        // Set the max quantity that can be received in this edit
                        // Add back the current item's accepted quantity since we're editing it
                        $receiptItem['max_receivable'] = $receiptItem['remaining_quantity'] + $item->accepted_quantity;
                        
                        // Set the accepted_quantity to the REMAINING quantity (not the current value)
                        // This shows how much more can be received based on what's remaining
                        // Ensure it's at least 1 even if remaining is zero
                        $receiptItem['accepted_quantity'] = max(0, $receiptItem['remaining_quantity']);
                        
                        break;
                    }
                }
            }
        } else {
            // For other statuses, load items normally
            $this->loadPurchaseOrderItems();
            
            // Update with remaining quantities and other values
            foreach ($receipt->items as $item) {
                foreach ($this->receiptItems as &$receiptItem) {
                    if ($receiptItem['product_id'] == $item->product_id) {
                        // Store the original values
                        $receiptItem['original_accepted'] = (float) $item->accepted_quantity;
                        $receiptItem['rejected_quantity'] = (float) $item->rejected_quantity;
                        $receiptItem['unit_cost'] = (float) $item->unit_cost;
                        $receiptItem['quantity'] = (float) $item->quantity;
                        $receiptItem['goods_receipt_item_id'] = $item->id;
                        
                        // Calculate remaining quantity that can still be received
                        $remainingQty = max(0, $receiptItem['ordered_quantity'] - $receiptItem['previously_received']);
                        $receiptItem['remaining_quantity'] = $remainingQty;
                        
                        // Set the max receivable quantity
                        $receiptItem['max_receivable'] = $remainingQty + $item->accepted_quantity;
                        
                        // Set the accepted quantity to the remaining quantity
                        // Ensure it's never less than 1, even if remaining is zero
                        $receiptItem['accepted_quantity'] = max(0, $remainingQty);
                        break;
                    }
                }
            }
        }
        
        $this->showModal = true;
        $this->editMode = true;
    }
    
    public function closeModal()
    {
        $this->showModal = false;
        $this->goodsReceipt = [];
        $this->receiptItems = [];
        $this->purchaseOrderItems = [];
        $this->selectedPurchaseOrder = null;
        $this->selectedSupplier = null;
        $this->selectedLocation = null;
        $this->editMode = false;
    }
    
    public function confirmDelete($id)
    {
        $receipt = GoodsReceipt::find($id);
        
        if(!$receipt) {
            return $this->dispatch('notify', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => __('messages.goods_receipt_not_found')
            ]);
        }
        
        if($receipt->status !== 'pending') {
            return $this->dispatch('notify', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => __('messages.cannot_delete_processed_receipt')
            ]);
        }
        
        $this->deleteId = $id;
        $this->showConfirmDelete = true;
    }
    
    public function cancelDelete()
    {
        $this->showConfirmDelete = false;
        $this->deleteId = null;
    }
    
    public function delete()
    {
        $receipt = GoodsReceipt::find($this->deleteId);
        
        if(!$receipt) {
            return $this->dispatch('notify', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => __('messages.goods_receipt_not_found')
            ]);
        }
        
        if($receipt->status !== 'pending') {
            return $this->dispatch('notify', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => __('messages.cannot_delete_processed_receipt')
            ]);
        }
        
        try {
            DB::beginTransaction();
            
            // Delete receipt items first
            GoodsReceiptItem::where('goods_receipt_id', $receipt->id)->delete();
            
            // Delete the receipt
            $receipt->delete();
            
            DB::commit();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'title' => __('messages.success'),
                'message' => __('messages.goods_receipt_deleted')
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => __('messages.goods_receipt_delete_failed')
            ]);
        }
        
        $this->showConfirmDelete = false;
        $this->deleteId = null;
    }
    
    public function viewPurchaseOrder($id)
    {
        $this->emit('viewPurchaseOrder', $id);
    }
    
    /**
     * Método para carregar explicitamente os itens de uma ordem de compra
     */
    public function loadPurchaseOrderItems()
    {
        // Clear current items
        $this->receiptItems = [];
        
        if (!empty($this->goodsReceipt['purchase_order_id'])) {
            $purchaseOrderId = $this->goodsReceipt['purchase_order_id'];
            
            Log::info('Loading purchase order items', [
                'purchase_order_id' => $purchaseOrderId,
                'edit_mode' => $this->editMode,
                'receipt_id' => $this->goodsReceipt['id'] ?? 'new'
            ]);
            
            // Load purchase order with items and their previous receipts
            $purchaseOrder = PurchaseOrder::with(['items.product', 'supplier', 'items.receiptItems'])
                ->find($purchaseOrderId);
            
            if (!$purchaseOrder) {
                Log::warning('Purchase order not found', ['purchase_order_id' => $purchaseOrderId]);
                return;
            }
            
            // Set supplier from PO
            $this->goodsReceipt['supplier_id'] = $purchaseOrder->supplier_id;
            
            // Get all previous receipt items for this PO
            $previousReceipts = GoodsReceipt::where('purchase_order_id', $purchaseOrderId)
                ->when($this->editMode && !empty($this->goodsReceipt['id']), function($query) {
                    $query->where('id', '!=', $this->goodsReceipt['id']);
                })
                ->with('items')
                ->get();
            
            // Create a map of product_id to total received quantity from previous receipts
            $previouslyReceived = [];
            foreach ($previousReceipts as $previousReceipt) {
                foreach ($previousReceipt->items as $item) {
                    if (!isset($previouslyReceived[$item->product_id])) {
                        $previouslyReceived[$item->product_id] = 0;
                    }
                    $previouslyReceived[$item->product_id] += $item->accepted_quantity;
                }
            }
            
            // Process each PO item
            foreach ($purchaseOrder->items as $item) {
                $orderedQty = (float) $item->quantity;
                $previouslyReceivedQty = (float) ($previouslyReceived[$item->product_id] ?? 0);
                $remainingQty = max(0, $orderedQty - $previouslyReceivedQty);
                
                // Only add items that still need to be received
                if ($remainingQty > 0) {
                    // If in edit mode, get the current receipt's accepted quantity
                    $currentReceiptQty = 0;
                    if ($this->editMode && !empty($this->goodsReceipt['id'])) {
                        $currentItem = GoodsReceiptItem::where('goods_receipt_id', $this->goodsReceipt['id'])
                            ->where('product_id', $item->product_id)
                            ->first();
                        if ($currentItem) {
                            $currentReceiptQty = (float) $currentItem->accepted_quantity;
                            // For editing existing receipt, we want to show what was received in this receipt
                            $acceptedQty = $currentReceiptQty;
                        } else {
                            // New item in existing receipt, default to remaining
                            $acceptedQty = $remainingQty;
                        }
                    } else {
                        // New receipt, default to remaining quantity
                        $acceptedQty = $remainingQty;
                    }
                    
                    // Ensure we don't exceed the remaining quantity
                    $acceptedQty = min($acceptedQty, $remainingQty);
                    
                    // Calculate remaining after this receipt
                    $remainingAfterThis = max(0, $remainingQty - $acceptedQty);
                    
                    $this->receiptItems[] = [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name,
                        'product_code' => $item->product->sku ?? $item->product->code ?? '',
                        'ordered_quantity' => $orderedQty,
                        'previously_received' => $previouslyReceivedQty,
                        'quantity' => $remainingQty, // Total remaining to receive
                        'accepted_quantity' => $acceptedQty, // Default to remaining quantity for new receipts
                        'rejected_quantity' => 0,
                        'unit_cost' => $item->unit_price,
                        'purchase_order_item_id' => $item->id,
                        'status' => 'pending',
                        'remaining_quantity' => $remainingAfterThis,
                        'max_receivable' => $remainingQty // Maximum that can be received in this receipt
                    ];
                }
            }
            
            // Recalculate receipt total after loading items
            $this->recalculateReceiptTotal();
            
            Log::info('PO items loaded for receipt', [
                'po_id' => $purchaseOrder->id,
                'items_count' => count($this->receiptItems),
                'supplier_id' => $purchaseOrder->supplier_id,
                'has_previous_receipts' => $previousReceipts->count() > 0
            ]);
        } else {
            $this->dispatch('notify', [
                'type' => 'warning',
                'title' => __('messages.warning'),
                'message' => __('messages.no_items_available_for_receipt')
            ]);
        }
    }

    /**
     * Método chamado quando o purchase_order_id é atualizado no formulário
     */
    public function updatedGoodsReceiptPurchaseOrderId()
    {
        $this->loadPurchaseOrderItems();
    }
    
    /**
     * Atualiza os cálculos quando a quantidade aceita for alterada
     */
    public function updatedReceiptItemsAcceptedQuantity($value, $index)
    {
        if (isset($this->receiptItems[$index])) {
            $acceptedQty = floatval($value);
            $remainingQty = floatval($this->receiptItems[$index]['quantity'] ?? 0);
            
            // Ensure accepted quantity doesn't exceed remaining quantity
            if ($acceptedQty > $remainingQty) {
                $acceptedQty = $remainingQty;
                $this->receiptItems[$index]['accepted_quantity'] = $acceptedQty;
            }
            
            // Update rejected quantity
            $this->receiptItems[$index]['rejected_quantity'] = $remainingQty - $acceptedQty;
            
            // Update remaining quantity for display
            $this->receiptItems[$index]['remaining_quantity'] = $remainingQty - $acceptedQty;
            
            // Recalculate totals
            $this->recalculateItemTotals($index);
            $this->recalculateReceiptTotal();
        }
    }
    
    /**
     * Atualiza os cálculos quando a quantidade rejeitada for alterada
     */
    public function updatedReceiptItemsRejectedQuantity($value, $index)
    {
        if (isset($this->receiptItems[$index])) {
            $rejectedQty = floatval($value);
            $remainingQty = floatval($this->receiptItems[$index]['quantity'] ?? 0);
            
            // Ensure rejected quantity doesn't exceed remaining quantity
            if ($rejectedQty > $remainingQty) {
                $rejectedQty = $remainingQty;
                $this->receiptItems[$index]['rejected_quantity'] = $rejectedQty;
            }
            
            // Update accepted quantity
            $this->receiptItems[$index]['accepted_quantity'] = $remainingQty - $rejectedQty;
            
            // Update remaining quantity for display
            $this->receiptItems[$index]['remaining_quantity'] = $remainingQty - ($remainingQty - $rejectedQty);
            
            // Recalculate totals
            $this->recalculateItemTotals($index);
            $this->recalculateReceiptTotal();
        }
    }
    
    /**
     * Atualiza os cálculos quando o custo unitário for alterado
     */
    public function updatedReceiptItemsUnitCost($value, $index)
    {
        // Atualiza o custo unitário no array de itens
        if (isset($this->receiptItems[$index])) {
            $this->receiptItems[$index]['unit_cost'] = (float) $value;
            
            // Recalcula os totais para este item
            $this->recalculateItemTotals($index);
            
            // Recalcula o total geral do recebimento
            $this->recalculateReceiptTotal();
        }
    }
    
    /**
     * Atualiza a quantidade aceita com base na quantidade rejeitada
     * 
     * @param int $index The index of the item in the receiptItems array
     * @return void
     */
    protected function updateAcceptedQuantity($index)
    {
        if (isset($this->receiptItems[$index])) {
            $quantity = (float) ($this->receiptItems[$index]['quantity'] ?? 0);
            $rejected = (float) ($this->receiptItems[$index]['rejected_quantity'] ?? 0);
            
            // Calculate accepted quantity as (quantity - rejected)
            $accepted = max(0, $quantity - $rejected);
            $this->receiptItems[$index]['accepted_quantity'] = $accepted;
            
            // Update remaining quantity for display (remaining from PO - this receipt's accepted)
            $remainingFromPO = $this->receiptItems[$index]['ordered_quantity'] - 
                             ($this->receiptItems[$index]['previously_received'] ?? 0);
            $this->receiptItems[$index]['remaining_quantity'] = max(0, $remainingFromPO - $accepted);
            
            // Recalculate item totals if unit cost is set
            if (isset($this->receiptItems[$index]['unit_cost'])) {
                $this->recalculateItemTotals($index);
                $this->recalculateReceiptTotal();
            }
        }
    }
    
    // Update calculations when quantity is changed
    public function updatedReceiptItemsQuantity($value, $index)
    {
        if (isset($this->receiptItems[$index])) {
            $newQuantity = (float) $value;
            $maxReceivable = (float) $this->receiptItems[$index]['max_receivable'];
            
            // Ensure quantity doesn't exceed max receivable
            if ($newQuantity > $maxReceivable) {
                $newQuantity = $maxReceivable;
                $this->receiptItems[$index]['quantity'] = $newQuantity;
            }
            
            // Update accepted quantity to match the new quantity (this receipt)
            $this->receiptItems[$index]['accepted_quantity'] = $newQuantity;
            
            // Reset rejected quantity since we're setting a new receipt quantity
            $this->receiptItems[$index]['rejected_quantity'] = 0;
            
            // Update remaining quantity
            $remainingQty = $this->receiptItems[$index]['ordered_quantity'] - 
                          ($this->receiptItems[$index]['previously_received'] + $newQuantity);
            $this->receiptItems[$index]['remaining_quantity'] = max(0, $remainingQty);
            
            // Recalculate totals
            $this->recalculateItemTotals($index);
            $this->recalculateReceiptTotal();
        }
    }
    
    /**
     * Recalcula o total de um item específico
     */
    protected function recalculateItemTotals($index)
    {
        if (isset($this->receiptItems[$index])) {
            $acceptedQuantity = $this->receiptItems[$index]['accepted_quantity'] ?? 0;
            $unitCost = $this->receiptItems[$index]['unit_cost'] ?? 0;
            
            // Calcular subtotal
            $this->receiptItems[$index]['subtotal'] = $acceptedQuantity * $unitCost;
            
            // Log para debug
            Log::info('Item recalculado', [
                'index' => $index,
                'accepted_quantity' => $acceptedQuantity,
                'unit_cost' => $unitCost,
                'subtotal' => $this->receiptItems[$index]['subtotal']
            ]);
        }
    }
    
    /**
     * Recalcula o total geral do recebimento
     */
    protected function recalculateReceiptTotal()
    {
        $total = 0;
        
        foreach ($this->receiptItems as $item) {
            $total += $item['subtotal'] ?? 0;
        }
        
        $this->goodsReceipt['total_amount'] = $total;
        
        // Log para debug
        Log::info('Recebimento recalculado', [
            'total' => $total,
            'items_count' => count($this->receiptItems)
        ]);
    }
    
    /**
     * Determina o status do recebimento com base nas quantidades recebidas
     * - Se recebeu tudo: 'completed'
     * - Se recebeu parcialmente: 'partially_processed'
     * - Se houver divergência: 'discrepancy'
     * - Senão: 'pending'
     * 
     * @param array $receiptItems
     * @return string
     */
    protected function determineReceiptStatus($receiptItems)
    {
        if (empty($receiptItems)) {
            return 'pending';
        }
        
        $allItemsFullyProcessed = true;
        $anyReceived = false;
        $anyRejected = false;
        $anyDiscrepancy = false;
        $allItemsAtLeastPartiallyProcessed = true;
        
        foreach ($receiptItems as $item) {
            $ordered = (float)($item['ordered_quantity'] ?? $item['quantity'] ?? 0);
            $previouslyReceived = (float)($item['previously_received'] ?? 0);
            $accepted = (float)($item['accepted_quantity'] ?? 0);
            $rejected = (float)($item['rejected_quantity'] ?? 0);
            
            $totalReceived = $previouslyReceived + $accepted + $rejected;
            $isFullyReceived = (abs($totalReceived - $ordered) < 0.0001); // Comparação segura para float
            $isPartiallyReceived = (($accepted + $rejected) > 0) && !$isFullyReceived;
            
            Log::debug('Verificando status do item', [
                'item' => $item['id'] ?? 'new',
                'ordered' => $ordered,
                'previouslyReceived' => $previouslyReceived,
                'accepted' => $accepted,
                'rejected' => $rejected,
                'totalReceived' => $totalReceived,
                'isFullyReceived' => $isFullyReceived ? 'sim' : 'não',
                'isPartiallyReceived' => $isPartiallyReceived ? 'sim' : 'não'
            ]);
            
            if (!$isFullyReceived) {
                $allItemsFullyProcessed = false;
            }
            
            // Se não recebeu nada neste item
            if (($accepted + $rejected) <= 0) {
                $allItemsAtLeastPartiallyProcessed = false;
            }
            
            if ($accepted > 0) {
                $anyReceived = true;
            }
            
            if ($rejected > 0) {
                $anyRejected = true;
                $anyDiscrepancy = true;
            }
            
            // Se recebeu mais do que o pedido, é uma discrepância
            if (($totalReceived - $ordered) > 0.0001) { // Tolerância para comparação de float
                $anyDiscrepancy = true;
                Log::debug('Discrepância detectada: recebido > pedido', [
                    'ordered' => $ordered,
                    'totalReceived' => $totalReceived,
                    'difference' => $totalReceived - $ordered
                ]);
            }
        }
        
        Log::debug('Status do recebimento', [
            'allItemsFullyProcessed' => $allItemsFullyProcessed ? 'sim' : 'não',
            'allItemsAtLeastPartiallyProcessed' => $allItemsAtLeastPartiallyProcessed ? 'sim' : 'não',
            'anyReceived' => $anyReceived ? 'sim' : 'não',
            'anyRejected' => $anyRejected ? 'sim' : 'não',
            'anyDiscrepancy' => $anyDiscrepancy ? 'sim' : 'não'
        ]);
        
        // Se todos os itens foram totalmente recebidos
        if ($allItemsFullyProcessed) {
            Log::info('Todos os itens foram totalmente recebidos', [
                'status' => $anyRejected ? 'discrepancy' : 'completed'
            ]);
            return $anyRejected ? 'discrepancy' : 'completed';
        }
        
        // Se todos os itens têm pelo menos algum recebimento, mas ainda não estão completos
        if ($allItemsAtLeastPartiallyProcessed) {
            Log::info('Todos os itens têm pelo menos algum recebimento', [
                'status' => $anyDiscrepancy ? 'discrepancy' : 'partially_processed'
            ]);
            return $anyDiscrepancy ? 'discrepancy' : 'partially_processed';
        }
        
        // Para recebimentos parciais, retorna 'partially_processed' se recebeu algo
        if ($anyReceived || $anyRejected) {
            return 'partially_processed';
        }
        
        return 'pending';
    }
    
    /**
     * Salva um recebimento de mercadorias
     */
    public function save()
    {
        // Definir regras de validação
        $rules = [
            'goodsReceipt.receipt_number' => 'required|string|max:50',
            'goodsReceipt.receipt_date' => 'required|date',
            'goodsReceipt.purchase_order_id' => 'required|exists:sc_purchase_orders,id',
            'goodsReceipt.supplier_id' => 'required|exists:sc_suppliers,id',
            'goodsReceipt.location_id' => 'required|exists:sc_inventory_locations,id',
            'goodsReceipt.status' => 'required|in:pending,partially_processed,completed,discrepancy',
            'receiptItems.*.accepted_quantity' => 'required|numeric|min:0.01',
            'receiptItems.*.rejected_quantity' => 'required|numeric|min:0',
        ];
        
        // Validação - NãO envolva em try-catch para permitir que o Livewire exiba os erros automaticamente
        $this->validate($rules);
        
        // Verificar se existem itens para processar
        if (empty($this->receiptItems)) {
            $this->addError('items', __('messages.at_least_one_item_required'));
            return;
        }

        // Verificar se algum item tem quantidade aceita maior que a quantidade pedida
        foreach ($this->receiptItems as $index => $item) {
            if (isset($item['ordered_qty']) && floatval($item['accepted_quantity']) > floatval($item['ordered_qty'])) {
                $this->dispatch('notify',
                    type: 'error',
                    title: __('messages.error'),
                    message: __('messages.exceed_ordered_qty')
                );
                return;
            }
        }
        
        try {
            // Iniciar transação
            DB::beginTransaction();

            // Criar ou atualizar o recebimento
            $receipt = $this->createOrUpdateReceipt();

            // Processar itens do recebimento
            $processedItems = $this->processReceiptItems($this->receiptItems, $receipt);

            // Atualizar o status do recebimento (retorna se deve atualizar estoque)
            $shouldUpdateStock = $this->updateReceiptStatus($receipt);
            
            // Verificar se devemos atualizar o estoque
            if ($shouldUpdateStock) {
                // Processar atualizações de inventário
                $this->processInventoryUpdates(
                    $this->receiptItems,
                    $receipt->location_id,
                    $receipt->receipt_number,
                    $receipt->id
                );
                
                Log::info('Estoque atualizado para o recebimento', [
                    'receipt_id' => $receipt->id,
                    'status' => $receipt->status
                ]);
            } else {
                Log::info('Atualização de estoque ignorada para o status: ' . $receipt->status, [
                    'receipt_id' => $receipt->id
                ]);
            }

            // Atualizar o status da ordem de compra, se aplicável
            if ($receipt->purchase_order_id) {
                $this->updatePurchaseOrderStatus($receipt->purchase_order_id, $shouldUpdateStock);
            }

            // Confirmar transação
            DB::commit();

            // Notificar sucesso
            $this->notifySuccess($receipt);
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->handleError($e);
        }
    }

    /**
     * Valida os dados do recebimento
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateReceiptData()
    {
        $rules = [
            'goodsReceipt.receipt_number' => 'required|string|max:50',
            'goodsReceipt.receipt_date' => 'required|date',
            'goodsReceipt.supplier_id' => 'required|exists:sc_suppliers,id',
            'goodsReceipt.location_id' => 'required|exists:sc_inventory_locations,id',
            'goodsReceipt.status' => 'required|in:pending,partially_processed,completed,discrepancy',
            'receiptItems.*.accepted_quantity' => 'required|numeric|min:0.01',
            'receiptItems.*.rejected_quantity' => 'required|numeric|min:0',
        ];

        // Log dos dados para depuração
        Log::info('Validating goods receipt data', [
            'goods_receipt' => $this->goodsReceipt,
            'receipt_items_count' => count($this->receiptItems ?? []),
            'edit_mode' => $this->editMode
        ]);

        $this->validate($rules);

        // Verificar se existem itens para processar
        if (empty($this->receiptItems)) {
            throw new \RuntimeException(__('messages.at_least_one_item_required'));
        }
    }

    /**
     * Cria ou atualiza um recebimento
     *
     * @return \App\Models\SupplyChain\GoodsReceipt
     * @throws \RuntimeException
     */
    protected function createOrUpdateReceipt()
    {
        if ($this->editMode && isset($this->goodsReceipt['id'])) {
            $receipt = GoodsReceipt::findOrFail($this->goodsReceipt['id']);
            
            // Verificar se é permitido editar
            if ($receipt->status !== 'pending' && $receipt->status !== $this->goodsReceipt['status']) {
                throw new \RuntimeException(__('messages.cannot_edit_processed_receipt_status'));
            }
            
            // Atualizar informações do recebimento
            $receipt->update([
                'purchase_order_id' => $this->goodsReceipt['purchase_order_id'] ?? null,
                'supplier_id' => $this->goodsReceipt['supplier_id'],
                'location_id' => $this->goodsReceipt['location_id'],
                'receipt_date' => $this->goodsReceipt['receipt_date'],
                'reference_number' => $this->goodsReceipt['reference_number'] ?? null,
                'notes' => $this->goodsReceipt['notes'] ?? null,
                'total_amount' => $this->goodsReceipt['total_amount'] ?? 0,
            ]);
            
            Log::info('Recebimento atualizado', ['receipt_id' => $receipt->id]);
            
            return $receipt;
        } else {
            // Criar novo recebimento
            $receipt = GoodsReceipt::create([
                'receipt_number' => $this->goodsReceipt['receipt_number'],
                'purchase_order_id' => $this->goodsReceipt['purchase_order_id'] ?? null,
                'supplier_id' => $this->goodsReceipt['supplier_id'],
                'location_id' => $this->goodsReceipt['location_id'],
                'receipt_date' => $this->goodsReceipt['receipt_date'],
                'reference_number' => $this->goodsReceipt['reference_number'] ?? null,
                'status' => 'pending', // Será atualizado após o processamento
                'notes' => $this->goodsReceipt['notes'] ?? null,
                'received_by' => Auth::id(),
                'total_amount' => $this->goodsReceipt['total_amount'] ?? 0,
            ]);
            
            Log::info('Novo recebimento criado', ['receipt_id' => $receipt->id]);
            
            return $receipt;
        }
    }
    
  /**
 * Atualiza o status do recebimento, respeitando a seleção do usuário
 * quando for um recebimento parcial.
 *
 * @param \App\Models\SupplyChain\GoodsReceipt $receipt
 * @return bool Retorna true se o estoque deve ser atualizado
 */
protected function updateReceiptStatus($receipt)
{
    $previousStatus = $receipt->status;
    $calculatedStatus = $this->determineReceiptStatus($receipt->items->toArray());
    $userSelectedStatus = $this->goodsReceipt['status'] ?? null;
    
    // Se o usuário selecionou um status manualmente, respeitamos sua escolha
    if ($userSelectedStatus === 'partially_processed') {
        $status = 'partially_processed';
    } 
    // Se o usuário selecionou 'completed', respeitamos sua escolha
    elseif ($userSelectedStatus === 'completed') {
        $status = 'completed';
        
        // Log da decisão de respeitar a escolha do usuário
        Log::info('Usuário selecionou completed manualmente. Respeitando escolha do usuário.', [
            'receipt_id' => $receipt->id,
            'calculatedStatus' => $calculatedStatus
        ]);
    }
    // Se houver itens rejeitados ou discrepância, marcar apenas se o usuário não escolheu explicitamente
    elseif ($calculatedStatus === 'discrepancy') {
        $status = 'discrepancy';
    }
    // Caso contrário, usar o status calculado
    else {
        $status = $calculatedStatus;
    }
    
    // Atualizar o status
    if ($receipt->status !== $status) {
        $receipt->update(['status' => $status]);
        
        Log::info('Status do recebimento atualizado', [
            'receipt_id' => $receipt->id,
            'old_status' => $previousStatus,
            'new_status' => $status,
            'status_selecionado' => $userSelectedStatus
        ]);
    }
    
    // Retornar se o status permite atualização de estoque
    return in_array($status, ['partially_processed', 'completed']);
}

/**
 * Notifica o usuário sobre o sucesso da operação
 *
 * @param \App\Models\SupplyChain\GoodsReceipt $receipt
 * @return void
 */
protected function notifySuccess($receipt)
    {
        $this->dispatch('notify',
            type: 'success',
            title: $this->editMode ? __('messages.success_update') : __('messages.success_create'),
            message: $this->editMode 
                ? __('messages.goods_receipt_updated')
                : __('messages.goods_receipt_created')
        );
        
        // Resetar formulário e fechar modal
        $this->resetReceipt();
        $this->closeModal();
        
        // Atualizar a lista
        $this->loadPurchaseOrders();
    }
    
    /**
     * Trata erros ocorridos durante o processamento
     *
     * @param \Exception $e
     * @return void
     */
    protected function handleError(\Exception $e)
    {
        Log::error('Erro ao salvar recebimento', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        $this->dispatch('notify',
            type: 'error',
            title: __('messages.error'),
            message: __('messages.error_saving_receipt')
        );
    }
    
    public function changeTab($tab)
    {
        $this->activeTab = $tab;
    }
    
    public function generatePdf($id)
    {
        try {
            $receipt = GoodsReceipt::with(['supplier', 'purchaseOrder', 'location', 'receiver', 'items.product'])->findOrFail($id);
            
            $pdf = app('dompdf.wrapper');
            $pdf->loadView('pdf.goods-receipt', [
                'receipt' => $receipt,
            ]);
            
            $filename = 'goods_receipt_' . $receipt->receipt_number . '.pdf';
            
            $this->dispatch('notify', 
                type: 'success', 
                message: __('messages.pdf_generated_successfully')
            );
            
            return response()->streamDownload(
                fn () => print($pdf->output()),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Exception $e) {
            Log::error('Error generating PDF: ' . $e->getMessage());
            $this->dispatch('notify', 
                type: 'error', 
                message: __('messages.pdf_generation_failed')
            );
            return null;
        }
    }
    
    /**
     * Completa um recebimento parcial, alterando seu status para completed
     *
     * @param int $id ID do recebimento
     * @return void
     */
    public function completeReceipt($id)
    {
        try {
            // Buscar o recebimento diretamente da tabela sc_goods_receipts
            $receipt = GoodsReceipt::with('items')->findOrFail($id);
            
            // Verificar se o status é partially_processed ou discrepancy
            // Agora permite completar mesmo em status de discrepância
            if (!in_array($receipt->status, ['partially_processed', 'discrepancy'])) {
                throw new \Exception(__('messages.receipt_must_be_partially_processed_or_discrepancy'));
            }
            
            // Iniciar transação
            DB::beginTransaction();
            
            // Verificar se ainda há itens pendentes e ajustá-los quando necessário
            foreach ($receipt->items as $item) {
                $ordered = (float)($item->ordered_quantity ?? $item->quantity ?? 0);
                $previouslyReceived = (float)($item->previously_received ?? 0);
                $accepted = (float)($item->accepted_quantity ?? 0);
                $rejected = (float)($item->rejected_quantity ?? 0);
                
                $totalReceived = $previouslyReceived + $accepted + $rejected;
                
                // Se ainda há quantidade pendente, assumimos que ela foi aceita
                // quando o usuário marca recebimento como completed
                if ($totalReceived < $ordered) {
                    $difference = $ordered - $totalReceived;
                    
                    // Atualiza o item para aceitar a quantidade restante
                    $item->update([
                        'accepted_quantity' => $accepted + $difference,
                        'updated_at' => now()
                    ]);
                }
            }
            
            // Atualizar diretamente na tabela para garantir a mudança
            DB::table('sc_goods_receipts')
                ->where('id', $id)
                ->update([
                    'status' => 'completed',
                    'updated_at' => now()
                ]);
            
            // Commit da transação
            DB::commit();
            
            // Notificar sucesso
            $this->dispatch('notify', 
                type: 'success', 
                message: __('messages.receipt_completed_successfully')
            );
            
            // Forçar atualização da listagem
            $this->dispatch('refreshGoodsReceipts');
            
        } catch (\Exception $e) {
            // Rollback em caso de erro
            DB::rollBack();
            
            // Log do erro
            Log::error('Erro ao completar recebimento: ' . $e->getMessage());
            
            // Notificar erro
            $this->dispatch('notify', 
                type: 'error', 
                message: __('messages.error_completing_receipt') . ': ' . $e->getMessage()
            );
        }
    }
    
    public function generateListPdf()
    {
        try {
            // Aplicar os mesmos filtros da listagem atual
            $query = GoodsReceipt::query()
                ->with(['supplier', 'purchaseOrder', 'location', 'receiver']);
            
            // Filtros
            if ($this->statusFilter) {
                $query->where('status', $this->statusFilter);
            }
            
            if ($this->supplierFilter) {
                $query->where('supplier_id', $this->supplierFilter);
            }
            
            if ($this->locationFilter) {
                $query->where('location_id', $this->locationFilter);
            }
            
            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('receipt_number', 'like', '%' . $this->search . '%')
                      ->orWhereHas('supplier', function ($sq) {
                          $sq->where('name', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('purchaseOrder', function ($sq) {
                          $sq->where('order_number', 'like', '%' . $this->search . '%');
                      });
                });
            }
            
            // Ordenação
            if ($this->sortField) {
                $query->orderBy($this->sortField, $this->sortDirection);
            } else {
                $query->orderBy('created_at', 'desc');
            }
            
            // Limitar a 100 registros para o PDF não ficar muito grande
            $receipts = $query->limit(100)->get();
            
            // Carregar a view do PDF com os dados
            $pdf = app('dompdf.wrapper');
            $pdf->loadView('pdf.goods-receipts-list', [
                'receipts' => $receipts,
                'filters' => [
                    'status' => $this->statusFilter,
                    'supplier' => $this->supplierFilter ? Supplier::find($this->supplierFilter)->name : null,
                    'location' => $this->locationFilter ? InventoryLocation::find($this->locationFilter)->name : null,
                    'search' => $this->search,
                ],
                'generatedAt' => now()->format('Y-m-d H:i:s'),
            ]);
            
            $filename = 'goods_receipts_list_' . now()->format('Y-m-d') . '.pdf';
            
            $this->dispatch('notify', 
                type: 'success', 
                message: __('messages.pdf_list_generated_successfully')
            );
            
            return response()->streamDownload(
                fn () => print($pdf->output()),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Exception $e) {
            Log::error('Error generating list PDF: ' . $e->getMessage());
            $this->dispatch('notify', 
                type: 'error', 
                message: __('messages.pdf_generation_failed')
            );
            return null;
        }
    }
}
