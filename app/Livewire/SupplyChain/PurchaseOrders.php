<?php

namespace App\Livewire\SupplyChain;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\Product;
use App\Models\SupplyChain\PurchaseOrder;
use App\Models\SupplyChain\PurchaseOrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrders extends Component
{
    use WithPagination;
    
    public $search = '';
    public $perPage = 10;
    public $statusFilter = '';
    public $supplierFilter = '';
    public $sortField = 'order_date';
    public $sortDirection = 'desc';
    
    // Create/Edit Modal Properties
    public $showModal = false;
    public $editMode = false;
    public $purchaseOrder = [];
    public $orderItems = [];
    public $selectedSupplier = null;
    public $selectedProducts = [];
    
    // View Modal Properties
    public $showViewModal = false;
    public $viewingOrder = null;
    
    // Delete Confirmation Properties
    public $showConfirmDelete = false;
    public $deleteId = null;
    
    protected $listeners = [
        'productSelected' => 'addProduct',
        'refreshPurchaseOrders' => '$refresh'
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
        
        $purchaseOrdersQuery = PurchaseOrder::query()
            ->with(['supplier', 'createdBy', 'items'])
            ->when($this->search, function($query) {
                return $query->where('order_number', 'like', '%' . $this->search . '%')
                    ->orWhereHas('supplier', function($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->when($this->statusFilter, function($query) {
                return $query->where('status', $this->statusFilter);
            })
            ->when($this->supplierFilter, function($query) {
                return $query->where('supplier_id', $this->supplierFilter);
            })
            ->orderBy($this->sortField, $this->sortDirection);
        
        $purchaseOrders = $purchaseOrdersQuery->paginate($this->perPage);
        
        // Calculate total amounts and check if orders are overdue
        foreach($purchaseOrders as $order) {
            $order->total_amount = $order->items->sum(function($item) {
                return $item->quantity * $item->unit_price;
            });
            
            $order->is_overdue = $order->expected_delivery && 
                in_array($order->status, ['ordered', 'approved', 'partially_received']) && 
                now()->gt($order->expected_delivery);
        }
        
        return view('livewire.supply-chain.purchase-orders', [
            'purchaseOrders' => $purchaseOrders,
            'suppliers' => $suppliers,
            'products' => Product::orderBy('name')->get(),
            'statuses' => [
                'draft' => __('messages.draft'),
                'pending_approval' => __('messages.pending_approval'),
                'approved' => __('messages.approved'),
                'ordered' => __('messages.ordered'),
                'partially_received' => __('messages.partially_received'),
                'completed' => __('messages.completed'),
                'cancelled' => __('messages.cancelled'),
            ]
        ]);
    }
    
    public function openCreateModal()
    {
        $this->resetValidation();
        $this->resetExcept(['search', 'perPage', 'statusFilter', 'supplierFilter', 'sortField', 'sortDirection']);
        
        $this->purchaseOrder = [
            'order_number' => 'PO' . now()->format('ymd') . rand(1000, 9999),
            'supplier_id' => '',
            'order_date' => now()->format('Y-m-d'),
            'expected_delivery' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'draft',
            'shipping_address' => '',
            'notes' => '',
            'terms_conditions' => '',
        ];
        
        $this->orderItems = [];
        $this->showModal = true;
        $this->editMode = false;
    }
    
    public function viewOrder($id)
    {
        $this->viewingOrder = PurchaseOrder::with(['supplier', 'items.product', 'createdBy', 'approvedBy'])->find($id);
        
        if(!$this->viewingOrder) {
            return $this->dispatchBrowserEvent('toast', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => __('messages.purchase_order_not_found')
            ]);
        }
        
        $this->viewingOrder->total_amount = $this->viewingOrder->items->sum(function($item) {
            return $item->quantity * $item->unit_price;
        });
        
        $this->showViewModal = true;
    }
    
    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewingOrder = null;
    }
    
    public function editOrder($id)
    {
        $order = PurchaseOrder::with('items.product')->find($id);
        
        if(!$order) {
            return $this->dispatchBrowserEvent('toast', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => __('messages.purchase_order_not_found')
            ]);
        }
        
        if(in_array($order->status, ['completed', 'cancelled'])) {
            return $this->dispatchBrowserEvent('toast', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => __('messages.cannot_edit_completed_cancelled_order')
            ]);
        }
        
        $this->resetValidation();
        $this->resetExcept(['search', 'perPage', 'statusFilter', 'supplierFilter', 'sortField', 'sortDirection']);
        
        $this->purchaseOrder = [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'supplier_id' => $order->supplier_id,
            'order_date' => $order->order_date,
            'expected_delivery' => $order->expected_delivery,
            'status' => $order->status,
            'shipping_address' => $order->shipping_address,
            'notes' => $order->notes,
            'terms_conditions' => $order->terms_conditions,
        ];
        
        $this->orderItems = $order->items->map(function($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'line_total' => $item->quantity * $item->unit_price,
            ];
        })->toArray();
        
        $this->showModal = true;
        $this->editMode = true;
    }
    
    public function closeModal()
    {
        $this->showModal = false;
        $this->purchaseOrder = [];
        $this->orderItems = [];
        $this->editMode = false;
    }
    
    public function confirmDelete($id)
    {
        $order = PurchaseOrder::find($id);
        
        if(!$order) {
            return $this->dispatchBrowserEvent('toast', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => __('messages.purchase_order_not_found')
            ]);
        }
        
        if(!in_array($order->status, ['draft', 'pending_approval'])) {
            return $this->dispatchBrowserEvent('toast', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => __('messages.cannot_delete_processed_order')
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
        $order = PurchaseOrder::find($this->deleteId);
        
        if(!$order) {
            return $this->dispatchBrowserEvent('toast', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => __('messages.purchase_order_not_found')
            ]);
        }
        
        if(!in_array($order->status, ['draft', 'pending_approval'])) {
            return $this->dispatchBrowserEvent('toast', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => __('messages.cannot_delete_processed_order')
            ]);
        }
        
        try {
            DB::beginTransaction();
            
            // Delete order items first
            PurchaseOrderItem::where('purchase_order_id', $order->id)->delete();
            
            // Delete the order
            $order->delete();
            
            DB::commit();
            
            $this->dispatchBrowserEvent('toast', [
                'type' => 'success',
                'title' => __('messages.success'),
                'message' => __('messages.purchase_order_deleted')
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->dispatchBrowserEvent('toast', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => __('messages.purchase_order_delete_failed')
            ]);
        }
        
        $this->showConfirmDelete = false;
        $this->deleteId = null;
    }
}
