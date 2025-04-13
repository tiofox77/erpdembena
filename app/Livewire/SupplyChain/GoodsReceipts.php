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
            ->with(['supplier', 'purchaseOrder', 'location', 'receivedBy', 'items'])
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
        
        return view('livewire.supply-chain.goods-receipts', [
            'goodsReceipts' => $goodsReceipts,
            'suppliers' => $suppliers,
            'locations' => $locations,
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
        $this->viewingReceipt = GoodsReceipt::with(['supplier', 'purchaseOrder', 'location', 'receivedBy', 'items.product'])->find($id);
        
        if(!$this->viewingReceipt) {
            return $this->dispatchBrowserEvent('toast', [
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
            return $this->dispatchBrowserEvent('toast', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => __('messages.goods_receipt_not_found')
            ]);
        }
        
        if($receipt->status === 'completed' || $receipt->status === 'cancelled') {
            return $this->dispatchBrowserEvent('toast', [
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
            return $this->dispatchBrowserEvent('toast', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => __('messages.goods_receipt_not_found')
            ]);
        }
        
        if($receipt->status !== 'pending') {
            return $this->dispatchBrowserEvent('toast', [
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
            return $this->dispatchBrowserEvent('toast', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => __('messages.goods_receipt_not_found')
            ]);
        }
        
        if($receipt->status !== 'pending') {
            return $this->dispatchBrowserEvent('toast', [
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
            
            $this->dispatchBrowserEvent('toast', [
                'type' => 'success',
                'title' => __('messages.success'),
                'message' => __('messages.goods_receipt_deleted')
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->dispatchBrowserEvent('toast', [
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
}
