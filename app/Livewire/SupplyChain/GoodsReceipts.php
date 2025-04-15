<?php

namespace App\Livewire\SupplyChain;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\Product;
use App\Models\SupplyChain\PurchaseOrder;
use App\Models\SupplyChain\PurchaseOrderItem;
use App\Models\SupplyChain\GoodsReceipt;
use App\Models\SupplyChain\GoodsReceiptItem;
use App\Models\SupplyChain\InventoryLocation;
use App\Models\SupplyChain\InventoryItem;
use App\Models\SupplyChain\InventoryTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class GoodsReceipts extends Component
{
    use WithPagination;
    
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
    
    protected $listeners = [
        'productSelected' => 'addProduct',
        'refreshGoodsReceipts' => '$refresh'
    ];
    
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
                return $query->where('receipt_number', 'like', '%' . $this->search . '%')
                    ->orWhereHas('supplier', function($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('purchaseOrder', function($q) {
                        $q->where('order_number', 'like', '%' . $this->search . '%');
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

        // Get purchase orders for the dropdown
        $purchaseOrders = PurchaseOrder::whereIn('status', ['approved', 'ordered', 'partially_received', 'completed', 'delivered'])
            ->orderBy('order_date', 'desc')
            ->get();
        
        return view('livewire.supply-chain.goods-receipts', [
            'goodsReceipts' => $goodsReceipts,
            'suppliers' => $suppliers,
            'locations' => $locations,
            'purchaseOrders' => $purchaseOrders,
            'statuses' => [
                'pending' => __('messages.pending'),
                'processing' => __('messages.processing'),
                'completed' => __('messages.completed'),
                'cancelled' => __('messages.cancelled'),
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
        $receipt = GoodsReceipt::with('items.product')->find($id);
        
        if(!$receipt) {
            return $this->dispatch('notify', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => __('messages.goods_receipt_not_found')
            ]);
        }
        
        if($receipt->status === 'completed' || $receipt->status === 'cancelled') {
            return $this->dispatch('notify', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => __('messages.cannot_edit_completed_cancelled_receipt')
            ]);
        }
        
        $this->resetValidation();
        $this->resetExcept(['search', 'perPage', 'statusFilter', 'supplierFilter', 'locationFilter', 'sortField', 'sortDirection']);
        
        $this->goodsReceipt = [
            'id' => $receipt->id,
            'receipt_number' => $receipt->receipt_number,
            'purchase_order_id' => $receipt->purchase_order_id,
            'supplier_id' => $receipt->supplier_id,
            'location_id' => $receipt->location_id,
            'receipt_date' => $receipt->receipt_date,
            'status' => $receipt->status,
            'notes' => $receipt->notes,
        ];
        
        $this->receiptItems = $receipt->items->map(function($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'purchase_order_item_id' => $item->purchase_order_item_id,
                'expected_quantity' => $item->expected_quantity,
                'received_quantity' => $item->received_quantity,
                'accepted_quantity' => $item->accepted_quantity,
                'rejected_quantity' => $item->rejected_quantity,
                'unit_cost' => $item->unit_cost,
            ];
        })->toArray();
        
        // If receipt is linked to a purchase order, load its items
        if($receipt->purchase_order_id) {
            $purchaseOrder = PurchaseOrder::with('items.product')->find($receipt->purchase_order_id);
            if($purchaseOrder) {
                $this->purchaseOrderItems = $purchaseOrder->items->map(function($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name,
                        'quantity' => $item->quantity,
                        'received_quantity' => $item->received_quantity,
                        'remaining_quantity' => $item->quantity - $item->received_quantity,
                        'unit_price' => $item->unit_price,
                    ];
                })->toArray();
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
        // Limpa os itens atuais
        $this->receiptItems = [];
        
        if (!empty($this->goodsReceipt['purchase_order_id'])) {
            // Log para debug
            Log::info('Carregando itens da ordem de compra', [
                'purchase_order_id' => $this->goodsReceipt['purchase_order_id']
            ]);
            
            $purchaseOrder = PurchaseOrder::with(['items.product', 'supplier'])
                ->find($this->goodsReceipt['purchase_order_id']);
            
            if (!$purchaseOrder) {
                Log::warning('Ordem de compra não encontrada', [
                    'purchase_order_id' => $this->goodsReceipt['purchase_order_id']
                ]);
                return;
            }
            
            // Atualiza o fornecedor automaticamente
            $this->goodsReceipt['supplier_id'] = $purchaseOrder->supplier_id;
            
            // Log para debug
            Log::info('Fornecedor atualizado', [
                'supplier_id' => $purchaseOrder->supplier_id,
                'supplier_name' => $purchaseOrder->supplier->name ?? 'N/A'
            ]);
            
            // Popula os itens do recebimento com base na ordem de compra
            foreach ($purchaseOrder->items as $item) {
                // Calcula a quantidade restante a receber
                $remainingQuantity = $item->quantity - ($item->received_quantity ?? 0);
                
                if ($remainingQuantity > 0) {
                    $this->receiptItems[] = [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name,
                        'product_code' => $item->product->sku ?? $item->product->code ?? '',
                        'quantity' => $remainingQuantity,
                        'accepted_quantity' => $remainingQuantity,
                        'rejected_quantity' => 0,
                        'unit_cost' => $item->unit_price,
                        'purchase_order_item_id' => $item->id
                    ];
                }
            }
            
            // Log para debug
            Log::info('Itens carregados', [
                'count' => count($this->receiptItems)
            ]);
            
            // Emitir notificação visual em caso de sucesso
            if (count($this->receiptItems) > 0) {
                $this->dispatch('notify', [
                    'type' => 'success',
                    'title' => __('messages.success'),
                    'message' => __('messages.items_loaded_successfully')
                ]);
            } else {
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'title' => __('messages.warning'),
                    'message' => __('messages.no_items_available_for_receipt')
                ]);
            }
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
        $this->updateRejectedQuantity($index);
        $this->recalculateItemTotals($index);
        $this->recalculateReceiptTotal();
    }
    
    /**
     * Atualiza os cálculos quando a quantidade rejeitada for alterada
     */
    public function updatedReceiptItemsRejectedQuantity($value, $index)
    {
        $this->updateAcceptedQuantity($index);
        $this->recalculateItemTotals($index);
        $this->recalculateReceiptTotal();
    }
    
    /**
     * Atualiza os cálculos quando o custo unitário for alterado
     */
    public function updatedReceiptItemsUnitCost($value, $index)
    {
        $this->recalculateItemTotals($index);
        $this->recalculateReceiptTotal();
    }
    
    /**
     * Atualiza a quantidade rejeitada com base na quantidade aceita
     */
    protected function updateRejectedQuantity($index)
    {
        if (isset($this->receiptItems[$index])) {
            $totalQuantity = $this->receiptItems[$index]['quantity'] ?? 0;
            $acceptedQuantity = $this->receiptItems[$index]['accepted_quantity'] ?? 0;
            
            // Garantir que a quantidade aceita não seja maior que a quantidade total
            if ($acceptedQuantity > $totalQuantity) {
                $this->receiptItems[$index]['accepted_quantity'] = $totalQuantity;
                $acceptedQuantity = $totalQuantity;
            }
            
            // Calcular quantidade rejeitada
            $this->receiptItems[$index]['rejected_quantity'] = $totalQuantity - $acceptedQuantity;
        }
    }
    
    /**
     * Atualiza a quantidade aceita com base na quantidade rejeitada
     */
    protected function updateAcceptedQuantity($index)
    {
        if (isset($this->receiptItems[$index])) {
            $totalQuantity = $this->receiptItems[$index]['quantity'] ?? 0;
            $rejectedQuantity = $this->receiptItems[$index]['rejected_quantity'] ?? 0;
            
            // Garantir que a quantidade rejeitada não seja maior que a quantidade total
            if ($rejectedQuantity > $totalQuantity) {
                $this->receiptItems[$index]['rejected_quantity'] = $totalQuantity;
                $rejectedQuantity = $totalQuantity;
            }
            
            // Calcular quantidade aceita
            $this->receiptItems[$index]['accepted_quantity'] = $totalQuantity - $rejectedQuantity;
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
     * Salva um recebimento de mercadorias
     */
    public function save()
    {
        // Validação
        $rules = [
            'goodsReceipt.receipt_number' => 'required|string|max:50',
            'goodsReceipt.receipt_date' => 'required|date',
            'goodsReceipt.supplier_id' => 'required|exists:sc_suppliers,id',
            'goodsReceipt.location_id' => 'required|exists:sc_inventory_locations,id',
            'goodsReceipt.status' => 'required|in:pending,processing,completed,cancelled',
        ];
        
        $this->validate($rules);

        // Validar que existem itens
        if (empty($this->receiptItems)) {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => __('messages.at_least_one_item_required')
            ]);
            return;
        }

        DB::beginTransaction();
        
        try {
            // Criar ou atualizar o recebimento
            if ($this->editMode && isset($this->goodsReceipt['id'])) {
                $receipt = GoodsReceipt::findOrFail($this->goodsReceipt['id']);
                
                // Se o status era diferente de 'pending', não permitir edição
                if ($receipt->status !== 'pending' && $receipt->status !== $this->goodsReceipt['status']) {
                    $this->dispatch('notify', [
                        'type' => 'error',
                        'title' => __('messages.error'),
                        'message' => __('messages.cannot_edit_processed_receipt_status')
                    ]);
                    DB::rollBack();
                    return;
                }
                
                $receipt->update([
                    'purchase_order_id' => $this->goodsReceipt['purchase_order_id'] ?? null,
                    'supplier_id' => $this->goodsReceipt['supplier_id'],
                    'location_id' => $this->goodsReceipt['location_id'],
                    'receipt_date' => $this->goodsReceipt['receipt_date'],
                    'reference_number' => $this->goodsReceipt['reference_number'] ?? null,
                    'status' => $this->goodsReceipt['status'],
                    'notes' => $this->goodsReceipt['notes'] ?? null,
                    'total_amount' => $this->goodsReceipt['total_amount'] ?? 0,
                ]);
                
                // Remover itens antigos e adicionar novos
                $receipt->items()->delete();
            } else {
                // Criar novo recebimento
                $receipt = GoodsReceipt::create([
                    'receipt_number' => $this->goodsReceipt['receipt_number'],
                    'purchase_order_id' => $this->goodsReceipt['purchase_order_id'] ?? null,
                    'supplier_id' => $this->goodsReceipt['supplier_id'],
                    'location_id' => $this->goodsReceipt['location_id'],
                    'receipt_date' => $this->goodsReceipt['receipt_date'],
                    'reference_number' => $this->goodsReceipt['reference_number'] ?? null,
                    'status' => $this->goodsReceipt['status'],
                    'notes' => $this->goodsReceipt['notes'] ?? null,
                    'received_by' => Auth::id(),
                    'total_amount' => $this->goodsReceipt['total_amount'] ?? 0,
                ]);
            }
            
            // Registrar os itens
            foreach ($this->receiptItems as $item) {
                $receiptItem = new GoodsReceiptItem([
                    'goods_receipt_id' => $receipt->id,
                    'product_id' => $item['product_id'],
                    'purchase_order_item_id' => $item['purchase_order_item_id'] ?? null,
                    'quantity' => $item['quantity'] ?? $item['accepted_quantity'] ?? 0,
                    'accepted_quantity' => $item['accepted_quantity'],
                    'rejected_quantity' => $item['rejected_quantity'],
                    'received_quantity' => $item['accepted_quantity'], // Adicionando o campo faltante
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => ($item['accepted_quantity'] * $item['unit_cost']),
                ]);
                
                $receipt->items()->save($receiptItem);
                
                // Se houver um item relacionado de ordem de compra, atualizar a quantidade recebida
                if (!empty($item['purchase_order_item_id'])) {
                    $poItem = PurchaseOrderItem::find($item['purchase_order_item_id']);
                    if ($poItem) {
                        $poItem->received_quantity = ($poItem->received_quantity ?? 0) + $item['accepted_quantity'];
                        $poItem->save();
                        
                        // Atualizar o status da ordem de compra se necessário
                        $this->updatePurchaseOrderStatus($poItem->purchase_order_id);
                    }
                }
                
                // Se o status for 'completed', atualizar o estoque
                if ($this->goodsReceipt['status'] === 'completed') {
                    // Verificar se já existe um item de inventário para este produto
                    $inventoryItem = InventoryItem::where('product_id', $item['product_id'])
                        ->where('location_id', $receipt->location_id)
                        ->first();
                    
                    if ($inventoryItem) {
                        // Atualizar quantidade
                        $inventoryItem->quantity_on_hand += $item['accepted_quantity'];
                        $inventoryItem->save();
                    } else {
                        // Criar novo item de inventário
                        $inventoryItem = new InventoryItem([
                            'product_id' => $item['product_id'],
                            'location_id' => $receipt->location_id,
                            'quantity_on_hand' => $item['accepted_quantity'],
                            'quantity_allocated' => 0,
                            'unit_cost' => $item['unit_cost'],
                        ]);
                        $inventoryItem->save();
                    }
                    
                    // Registrar a transação de inventário
                    $transaction = new InventoryTransaction([
                        'transaction_number' => InventoryTransaction::generateTransactionNumber(),
                        'transaction_type' => 'purchase_receipt',
                        'reference_type' => 'goods_receipt',
                        'reference_id' => $receipt->id,
                        'product_id' => $item['product_id'],
                        'source_location_id' => null, // Entrada no estoque, não há origem
                        'destination_location_id' => $receipt->location_id,
                        'quantity' => $item['accepted_quantity'],
                        'unit_cost' => $item['unit_cost'],
                        'transaction_date' => now(),
                        'notes' => "Goods Receipt #{$receipt->receipt_number}",
                        'created_by' => Auth::id(),
                    ]);
                    $transaction->save();
                }
            }
            
            DB::commit();
            
            // Fechar o modal e redefinir as propriedades
            $this->closeModal();
            
            // Mostrar mensagem de sucesso
            $this->dispatch('notify', [
                'type' => 'success',
                'title' => __('messages.success'),
                'message' => $this->editMode 
                    ? __('messages.goods_receipt_updated') 
                    : __('messages.goods_receipt_created')
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log do erro
            Log::error('Erro ao salvar recebimento de mercadorias: ' . $e->getMessage(), [
                'exception' => $e,
                'goods_receipt' => $this->goodsReceipt,
                'receipt_items' => $this->receiptItems
            ]);
            
            // Mostrar mensagem de erro
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => __('messages.goods_receipt_save_failed') . ': ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Atualiza o status da ordem de compra com base nos itens recebidos
     */
    protected function updatePurchaseOrderStatus($purchaseOrderId)
    {
        $purchaseOrder = PurchaseOrder::with('items')->find($purchaseOrderId);
        
        if (!$purchaseOrder) {
            return;
        }
        
        $allItemsReceived = true;
        $anyItemReceived = false;
        
        foreach ($purchaseOrder->items as $item) {
            if (($item->received_quantity ?? 0) > 0) {
                $anyItemReceived = true;
            }
            
            if (($item->received_quantity ?? 0) < $item->quantity) {
                $allItemsReceived = false;
            }
        }
        
        if ($allItemsReceived) {
            $purchaseOrder->status = 'completed';
        } else if ($anyItemReceived) {
            $purchaseOrder->status = 'partially_received';
        }
        
        $purchaseOrder->save();
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
