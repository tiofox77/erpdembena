<?php

namespace App\Livewire\SupplyChain;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SupplyChain\Product;
use App\Models\SupplyChain\InventoryItem;
use App\Models\SupplyChain\InventoryLocation;
use App\Models\SupplyChain\InventoryTransaction;
use App\Models\SupplyChain\ProductCategory;
use Illuminate\Support\Facades\Auth;

class Inventory extends Component
{
    use WithPagination;
    
    public $search = '';
    public $perPage = 10;
    public $locationFilter = '';
    public $categoryFilter = '';
    public $stockFilter = '';
    
    // For adjustment modal
    public $showAdjustmentModal = false;
    public $adjustmentItem = null;
    public $adjustmentQuantity = '';
    public $adjustmentReason = '';
    public $adjustmentNotes = '';
    public $selectedProductId = '';
    public $selectedLocationId = '';
    public $adjustmentType = 'add'; // Default to add stock
    
    // Lista de produtos para o select do modal de ajuste de estoque
    public $availableProducts = [];
    public $availableLocations = [];
    
    // For transfer modal
    public $showTransferModal = false;
    public $transferItem = null;
    public $transferQuantity = '';
    public $transferDestinationId = '';
    public $transferReason = '';
    public $transferNotes = '';
    
    // For history modal
    public $showHistoryModal = false;
    public $inventoryItemHistory = [];
    public $historyItem = null;
    
    protected $listeners = [
        'refreshInventory' => '$refresh'
    ];
    
    public function mount()
    {
        // Inicializar as localizações disponíveis para evitar erros
        $this->availableLocations = InventoryLocation::where('is_active', true)
            ->orderBy('name')
            ->get();
    }
    
    public function render()
    {
        $locations = InventoryLocation::orderBy('name')->get();
        $categories = ProductCategory::orderBy('name')->get();
        
        $inventoryItemsQuery = InventoryItem::query()
            ->with(['product', 'product.category', 'location'])
            ->join('sc_products', 'sc_inventory_items.product_id', '=', 'sc_products.id')
            ->select('sc_inventory_items.*');
        
        if ($this->search) {
            $inventoryItemsQuery->where(function($query) {
                $query->whereHas('product', function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('sku', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('location', function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            });
        }
        
        if ($this->locationFilter) {
            $inventoryItemsQuery->where('location_id', $this->locationFilter);
        }
        
        if ($this->categoryFilter) {
            $inventoryItemsQuery->whereHas('product', function($q) {
                $q->where('category_id', $this->categoryFilter);
            });
        }
        
        if ($this->stockFilter) {
            if ($this->stockFilter === 'low') {
                $inventoryItemsQuery->whereRaw('sc_inventory_items.quantity <= sc_products.reorder_point')
                                   ->whereRaw('sc_inventory_items.quantity > 0');
            } elseif ($this->stockFilter === 'out') {
                $inventoryItemsQuery->where('quantity', 0);
            } elseif ($this->stockFilter === 'in') {
                $inventoryItemsQuery->whereRaw('sc_inventory_items.quantity > sc_products.reorder_point');
            }
        }
        
        $inventoryItems = $inventoryItemsQuery->paginate($this->perPage);
        
        // Add stock status flags to each item
        foreach ($inventoryItems as $item) {
            $item->is_low_stock = $item->quantity <= $item->product->reorder_point && $item->quantity > 0;
            $item->is_out_of_stock = $item->quantity == 0;
            $item->total_value = $item->quantity * $item->unit_cost;
        }
        
        // Recent transactions
        $recentTransactions = InventoryTransaction::with(['product', 'sourceLocation', 'destinationLocation', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('livewire.supply-chain.inventory', [
            'inventoryItems' => $inventoryItems,
            'locations' => $locations,
            'categories' => $categories,
            'recentTransactions' => $recentTransactions
        ]);
    }
    
    public function resetFilters()
    {
        $this->search = '';
        $this->locationFilter = '';
        $this->categoryFilter = '';
        $this->stockFilter = '';
    }
    
    public function openAdjustmentModal($inventoryItemId = null)
    {
        // Carregar lista de produtos disponíveis para o modal de ajuste
        $this->availableProducts = Product::with(['inventoryItems' => function($query) {
            $query->with('location');
        }])->orderBy('name')->get();
        
        // Resetar as propriedades do modal
        $this->adjustmentQuantity = '';
        $this->adjustmentReason = '';
        $this->adjustmentNotes = '';
        $this->selectedProductId = '';
        $this->selectedLocationId = '';
        $this->adjustmentType = 'add';
        
        // Carregar localizações disponíveis
        $this->availableLocations = InventoryLocation::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        if ($inventoryItemId) {
            $this->adjustmentItem = InventoryItem::with('product', 'location')->find($inventoryItemId);
            if (!$this->adjustmentItem) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'title' => __('messages.error'),
                    'message' => __('messages.inventory_item_not_found')
                ]);
                return;
            }
        } else {
            $this->adjustmentItem = null;
        }
        
        $this->showAdjustmentModal = true;
    }
    
    public function closeAdjustmentModal()
    {
        $this->showAdjustmentModal = false;
        $this->adjustmentItem = null;
        $this->adjustmentQuantity = '';
        $this->adjustmentReason = '';
        $this->adjustmentNotes = '';
        $this->selectedProductId = '';
        $this->selectedLocationId = '';
        $this->adjustmentType = 'add';
    }
    
    public function updatedSelectedProductId($productId)
    {
        $this->selectedLocationId = '';
        if (!empty($productId)) {
            $this->availableLocations = InventoryLocation::where('is_active', true)
                ->orderBy('name')
                ->get();
        }
    }
    
    public function updatedSelectedLocationId($locationId)
    {
        if (!empty($this->selectedProductId) && !empty($locationId)) {
            // Buscar o item de inventário com o produto e localização selecionados
            $this->adjustmentItem = InventoryItem::where('product_id', $this->selectedProductId)
                ->where('location_id', $locationId)
                ->with('product', 'location')
                ->first();
                
            // Se não existir, criar um novo item de inventário
            if (!$this->adjustmentItem) {
                $product = Product::find($this->selectedProductId);
                $location = InventoryLocation::find($locationId);
                
                if ($product && $location) {
                    $this->adjustmentItem = new InventoryItem();
                    $this->adjustmentItem->product_id = $product->id;
                    $this->adjustmentItem->location_id = $location->id;
                    $this->adjustmentItem->quantity = 0;
                    $this->adjustmentItem->unit_cost = $product->cost_price ?: 0;
                    $this->adjustmentItem->product = $product;
                    $this->adjustmentItem->location = $location;
                }
            }
        }
    }
    
    public function saveAdjustment()
    {
        $this->validate([
            'adjustmentQuantity' => 'required|numeric',
            'adjustmentReason' => 'required|string|max:255',
            'selectedProductId' => 'required_without:adjustmentItem',
            'selectedLocationId' => 'required_without:adjustmentItem',
        ]);
        
        if (!$this->adjustmentItem) {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => __('messages.inventory_item_not_found')
            ]);
            return;
        }
        
        // Determine the actual quantity based on adjustment type
        $finalQuantity = $this->adjustmentQuantity;
        if ($this->adjustmentType === 'remove') {
            $finalQuantity = -1 * abs($this->adjustmentQuantity);  // Ensure it's negative
            
            // Check if we're trying to remove more than exists
            if (abs($finalQuantity) > $this->adjustmentItem->quantity) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'title' => __('messages.error'),
                    'message' => __('messages.insufficient_stock')
                ]);
                return;
            }
        } elseif ($this->adjustmentType === 'set') {
            $finalQuantity = $this->adjustmentQuantity - $this->adjustmentItem->quantity;
            // If final quantity is 0, no transaction needed
            if ($finalQuantity == 0) {
                $this->closeAdjustmentModal();
                $this->dispatch('notify', [
                    'type' => 'info',
                    'title' => __('messages.info'),
                    'message' => __('messages.no_adjustment_needed')
                ]);
                return;
            }
        }
        
        // Create transaction record
        $transaction = new InventoryTransaction();
        $transaction->transaction_number = 'ADJ' . now()->format('YmdHis') . rand(100, 999);
        $transaction->transaction_type = 'adjustment';
        $transaction->product_id = $this->adjustmentItem->product_id;
        $transaction->quantity = $finalQuantity;
        $transaction->destination_location_id = $this->adjustmentItem->location_id;
        $transaction->unit_cost = $this->adjustmentItem->unit_cost;
        $transaction->total_cost = $finalQuantity * $this->adjustmentItem->unit_cost;
        $transaction->reference = $this->adjustmentReason;
        $transaction->notes = $this->adjustmentNotes;
        $transaction->created_by = Auth::id();
        $transaction->save();
        
        // Update inventory item quantity
        if ($this->adjustmentType === 'set') {
            $this->adjustmentItem->quantity = $this->adjustmentQuantity;
        } else {
            $this->adjustmentItem->quantity += $finalQuantity;
        }
        
        $this->adjustmentItem->save();
        
        $this->closeAdjustmentModal();
        
        $this->dispatch('notify', [
            'type' => 'success',
            'title' => __('messages.success'),
            'message' => __('messages.inventory_adjustment_successful')
        ]);
    }
    
    public function openTransferModal($inventoryItemId = null)
    {
        $this->showTransferModal = true;
        $this->transferQuantity = '';
        $this->transferDestinationId = '';
        $this->transferReason = '';
        $this->transferNotes = '';

        if ($inventoryItemId) {
            $this->transferItem = InventoryItem::with(['product', 'location'])->find($inventoryItemId);
            if (!$this->transferItem) {
                $this->dispatch('toast', [
                    'type' => 'error',
                    'title' => __('messages.error'),
                    'message' => __('messages.inventory_item_not_found')
                ]);
                $this->showTransferModal = false;
                return;
            }
        } else {
            $this->transferItem = null;
        }

        // Load available locations for transfer
        $this->availableLocations = InventoryLocation::where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function saveTransfer()
    {
        // Validate product selection first
        if (!$this->transferItem) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => __('messages.no_product_selected')
            ]);
            return;
        }

        $this->validate([
            'transferQuantity' => 'required|numeric|min:1',
            'transferDestinationId' => 'required|exists:sc_inventory_locations,id',
            'transferReason' => 'required|string',
            'transferNotes' => 'nullable|string',
        ]);

        // Verify if transfer quantity exceeds available stock
        if ($this->transferQuantity > $this->transferItem->quantity) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => __('messages.insufficient_stock')
            ]);
            return;
        }

        // Verify if source and destination are different
        if ($this->transferItem->location_id == $this->transferDestinationId) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => __('messages.same_location_transfer')
            ]);
            return;
        }

        // Create transaction record
        $transaction = new InventoryTransaction();
        $transaction->transaction_number = 'TRF' . now()->format('YmdHis') . rand(100, 999);
        $transaction->product_id = $this->transferItem->product_id;
        $transaction->transaction_type = 'transfer';
        $transaction->source_location_id = $this->transferItem->location_id;
        $transaction->destination_location_id = $this->transferDestinationId;
        $transaction->quantity = $this->transferQuantity;
        $transaction->unit_cost = $this->transferItem->unit_cost;
        $transaction->reference = $this->transferReason;
        $transaction->notes = $this->transferNotes;
        $transaction->created_by = Auth::id();
        $transaction->save();

        // Update source location stock (reduce)
        $this->transferItem->quantity -= $this->transferQuantity;
        $this->transferItem->save();

        // Check if item exists in destination location
        $destinationItem = InventoryItem::where('product_id', $this->transferItem->product_id)
            ->where('location_id', $this->transferDestinationId)
            ->first();
            
        if ($destinationItem) {
            // Update existing stock (increase)
            $destinationItem->quantity += $this->transferQuantity;
            $destinationItem->save();
        } else {
            // Create new inventory item in destination
            $newItem = new InventoryItem();
            $newItem->product_id = $this->transferItem->product_id;
            $newItem->location_id = $this->transferDestinationId;
            $newItem->quantity = $this->transferQuantity;
            $newItem->unit_cost = $this->transferItem->unit_cost;
            $newItem->save();
        }

        $this->closeTransferModal();
        
        $this->dispatch('toast', [
            'type' => 'success',
            'title' => __('messages.success'),
            'message' => __('messages.inventory_transfer_successful')
        ]);
    }
    
    public function closeTransferModal()
    {
        $this->showTransferModal = false;
        $this->transferItem = null;
        $this->transferQuantity = '';
        $this->transferDestinationId = '';
        $this->transferReason = '';
        $this->transferNotes = '';
    }
    
    public function openHistoryModal($inventoryItemId)
    {
        $this->historyItem = InventoryItem::with(['product', 'location'])->find($inventoryItemId);
        
        if (!$this->historyItem) {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => __('messages.inventory_item_not_found')
            ]);
            return;
        }
        
        // Get transaction history for this inventory item
        $this->inventoryItemHistory = InventoryTransaction::where('product_id', $this->historyItem->product_id)
            ->where(function($query) {
                $query->where('source_location_id', $this->historyItem->location_id)
                      ->orWhere('destination_location_id', $this->historyItem->location_id);
            })
            ->with(['product', 'sourceLocation', 'destinationLocation', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
        
        $this->showHistoryModal = true;
    }
    
    public function closeHistoryModal()
    {
        $this->showHistoryModal = false;
        $this->historyItem = null;
        $this->inventoryItemHistory = [];
    }
}