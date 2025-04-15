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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
    public $selectedProducts = [];
    public $selectedLocationId = '';
    public $adjustmentType = '';
    public $adjustmentQuantities = [];
    public $adjustmentReason = '';
    public $adjustmentNotes = '';
    
    // For transfer modal
    public $showTransferModal = false;
    public $transferItem = null;
    public $transferQuantity = '';
    public $transferDestinationId = '';
    public $transferReason = '';
    public $transferNotes = '';
    public $selectedTransferProducts = [];
    public $transferQuantities = [];
    public $availableLocations = [];
    public $transferSourceId = '';
    
    // Lista de produtos para o select do modal de ajuste de estoque
    public $availableProducts = [];
    
    // For history modal
    public $showHistoryModal = false;
    public $inventoryItemHistory = [];
    public $historyItem = null;
    
    protected $listeners = [
        'refreshInventory' => '$refresh'
    ];
    
    protected function rules()
    {
        return [
            'selectedProducts' => 'required|array|min:1',
            'selectedLocationId' => 'required|exists:sc_inventory_locations,id',
            'adjustmentType' => 'required|in:add,remove,set',
            'adjustmentQuantities.*' => 'required|numeric|min:0',
            'adjustmentReason' => 'required|string|max:255',
            'adjustmentNotes' => 'nullable|string|max:1000',
        ];
    }
    
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
                $inventoryItemsQuery->whereRaw('sc_inventory_items.quantity_on_hand <= sc_products.reorder_point')
                                   ->whereRaw('sc_inventory_items.quantity_on_hand > 0');
            } elseif ($this->stockFilter === 'out') {
                $inventoryItemsQuery->where('quantity_on_hand', 0);
            } elseif ($this->stockFilter === 'in') {
                $inventoryItemsQuery->whereRaw('sc_inventory_items.quantity_on_hand > sc_products.reorder_point');
            }
        }
        
        $inventoryItems = $inventoryItemsQuery->paginate($this->perPage);
        
        // Add stock status flags to each item
        foreach ($inventoryItems as $item) {
            $item->is_low_stock = $item->quantity_on_hand <= $item->product->reorder_point && $item->quantity_on_hand > 0;
            $item->is_out_of_stock = $item->quantity_on_hand == 0;
            $item->total_value = $item->quantity_on_hand * $item->unit_cost;
        }
        
        // Recent transactions
        $recentTransactions = InventoryTransaction::with(['product', 'sourceLocation', 'destinationLocation', 'creator'])
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
        $this->resetAdjustmentFields();
        
        // Carregar produtos disponíveis
        $this->availableProducts = Product::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        // Carregar localizações disponíveis
        $this->availableLocations = InventoryLocation::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        if ($inventoryItemId) {
            $this->adjustmentItem = InventoryItem::with('product', 'location')->find($inventoryItemId);
            if ($this->adjustmentItem) {
                $this->selectedProducts = [$this->adjustmentItem->product_id];
                $this->selectedLocationId = $this->adjustmentItem->location_id;
            }
        }
        
        $this->showAdjustmentModal = true;
    }

    public function resetAdjustmentFields()
    {
        $this->adjustmentItem = null;
        $this->selectedProducts = [];
        $this->selectedLocationId = '';
        $this->adjustmentType = '';
        $this->adjustmentQuantities = [];
        $this->adjustmentReason = '';
        $this->adjustmentNotes = '';
    }

    public function openTransferModal($inventoryItemId = null)
    {
        $this->resetTransferFields();
        
        // Carregar produtos disponíveis
        $this->availableProducts = Product::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        // Carregar localizações disponíveis
        $this->availableLocations = InventoryLocation::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        if ($inventoryItemId) {
            $this->transferItem = InventoryItem::with('product', 'location')->find($inventoryItemId);
            if ($this->transferItem) {
                $this->selectedTransferProducts = [$this->transferItem->product_id];
                $this->transferSourceId = $this->transferItem->location_id;
            }
        }
        
        $this->showTransferModal = true;
    }

    public function resetTransferFields()
    {
        $this->transferItem = null;
        $this->transferQuantity = '';
        $this->transferDestinationId = '';
        $this->transferReason = '';
        $this->transferNotes = '';
        $this->selectedTransferProducts = [];
        $this->transferQuantities = [];
        $this->transferSourceId = '';
    }

    public function updatedSelectedProducts($value)
    {
        // Limpa as quantidades quando os produtos são alterados
        $this->adjustmentQuantities = [];
    }

    public function updatedSelectedLocationId()
    {
        // Limpa as quantidades quando a localização é alterada
        $this->adjustmentQuantities = [];
    }

    public function updatedAdjustmentType()
    {
        // Limpa as quantidades quando o tipo de ajuste é alterado
        $this->adjustmentQuantities = [];
    }

    public function saveAdjustment()
    {
        $this->validate([
            'selectedProducts' => 'required|array|min:1',
            'selectedLocationId' => 'required|exists:sc_inventory_locations,id',
            'adjustmentType' => 'required|in:add,remove,set',
            'adjustmentQuantities.*' => 'required|numeric|min:0',
            'adjustmentReason' => 'required|string|max:255',
            'adjustmentNotes' => 'nullable|string|max:1000',
        ], [], [
            'selectedProducts' => __('messages.products'),
            'selectedLocationId' => __('messages.location'),
            'adjustmentType' => __('messages.adjustment_type'),
            'adjustmentQuantities.*' => __('messages.quantity'),
            'adjustmentReason' => __('messages.reason'),
            'adjustmentNotes' => __('messages.notes'),
        ]);

        try {
            DB::beginTransaction();

            foreach ($this->selectedProducts as $productId) {
                $quantity = $this->adjustmentQuantities[$productId] ?? 0;
                if ($quantity <= 0) {
                    $this->dispatch('notify', [
                        'type' => 'error',
                        'message' => __('messages.invalid_quantity')
                    ]);
                    return;
                }

                $product = Product::findOrFail($productId);
                $inventoryItem = InventoryItem::firstOrNew([
                    'product_id' => $productId,
                    'location_id' => $this->selectedLocationId,
                ]);

                if (!$inventoryItem->exists) {
                    $inventoryItem->quantity_on_hand = 0;
                    $inventoryItem->quantity_allocated = 0;
                    $inventoryItem->unit_cost = $product->cost ?? 0;
                    $inventoryItem->save();
                }

                $currentQuantity = $inventoryItem->quantity_on_hand;
                $newQuantity = match ($this->adjustmentType) {
                    'add' => $currentQuantity + $quantity,
                    'remove' => $currentQuantity - $quantity,
                    'set' => $quantity,
                };

                if ($this->adjustmentType === 'remove' && $currentQuantity < $quantity) {
                    $this->dispatch('notify', [
                        'type' => 'error',
                        'message' => __('messages.insufficient_stock')
                    ]);
                    continue;
                }

                // Create transaction record
                $transaction = new InventoryTransaction();
                $transaction->transaction_number = InventoryTransaction::generateTransactionNumber('ADJ');
                $transaction->transaction_type = 'adjustment';
                $transaction->product_id = $productId;
                $transaction->source_location_id = $this->selectedLocationId;
                $transaction->destination_location_id = $this->selectedLocationId;
                $transaction->quantity = $this->adjustmentType === 'remove' ? -$quantity : $quantity;
                $transaction->unit_cost = $inventoryItem->unit_cost ?? 0;
                $transaction->total_cost = $quantity * ($inventoryItem->unit_cost ?? 0);
                $transaction->reference_type = $this->adjustmentType;
                $transaction->reference_id = null;
                $transaction->notes = $this->adjustmentNotes . "\nReason: " . $this->adjustmentReason;
                $transaction->created_by = Auth::id();
                $transaction->save();

                // Update inventory
                $inventoryItem->quantity_on_hand = $newQuantity;
                $inventoryItem->quantity_available = $newQuantity - ($inventoryItem->quantity_allocated ?? 0);
                $inventoryItem->save();
            }

            DB::commit();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => __('messages.stock_adjusted_successfully')
            ]);

            $this->closeAdjustmentModal();
            $this->resetAdjustment();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao ajustar estoque', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('messages.error_adjusting_stock')
            ]);
        }
    }

    private function resetAdjustment()
    {
        $this->selectedProducts = [];
        $this->selectedLocationId = '';
        $this->adjustmentType = '';
        $this->adjustmentQuantities = [];
        $this->adjustmentReason = '';
        $this->adjustmentNotes = '';
    }

    public function saveTransfer()
    {
        $this->validate([
            'selectedTransferProducts' => 'required|array|min:1',
            'transferSourceId' => 'required|exists:sc_inventory_locations,id',
            'transferDestinationId' => 'required|exists:sc_inventory_locations,id|different:transferSourceId',
            'transferReason' => 'required|string|min:3',
            'transferNotes' => 'nullable|string',
            'transferQuantities.*' => 'required|numeric|min:1',
        ], [], [
            'selectedTransferProducts' => __('messages.products'),
            'transferSourceId' => __('messages.source_location'),
            'transferDestinationId' => __('messages.destination_location'),
            'transferReason' => __('messages.transfer_reason'),
            'transferNotes' => __('messages.notes'),
            'transferQuantities.*' => __('messages.quantity'),
        ]);

        try {
            DB::beginTransaction();

            foreach ($this->selectedTransferProducts as $productId) {
                $quantity = $this->transferQuantities[$productId] ?? 0;
                if ($quantity <= 0) {
                    $this->dispatch('notify', [
                        'type' => 'error',
                        'message' => __('messages.invalid_quantity')
                    ]);
                    return;
                }

                // Check source inventory
                $sourceItem = InventoryItem::where('product_id', $productId)
                    ->where('location_id', $this->transferSourceId)
                    ->first();

                if (!$sourceItem || $sourceItem->quantity_on_hand < $quantity) {
                    $this->dispatch('notify', [
                        'type' => 'error',
                        'message' => __('messages.insufficient_stock')
                    ]);
                    return;
                }

                // Create transaction record
                $transaction = new InventoryTransaction();
                $transaction->transaction_number = InventoryTransaction::generateTransactionNumber('TRF');
                $transaction->transaction_type = 'transfer';
                $transaction->product_id = $productId;
                $transaction->source_location_id = $this->transferSourceId;
                $transaction->destination_location_id = $this->transferDestinationId;
                $transaction->quantity = $quantity;
                $transaction->unit_cost = $sourceItem->unit_cost ?? 0;
                $transaction->total_cost = $quantity * ($sourceItem->unit_cost ?? 0);
                $transaction->reference_type = 'transfer';
                $transaction->reference_id = null;
                $transaction->notes = $this->transferNotes . "\nReason: " . $this->transferReason;
                $transaction->created_by = Auth::id();
                $transaction->save();

                // Update source inventory
                $sourceItem->quantity_on_hand -= $quantity;
                $sourceItem->quantity_available = $sourceItem->quantity_on_hand - ($sourceItem->quantity_allocated ?? 0);
                $sourceItem->save();

                // Update or create destination inventory
                $destinationItem = InventoryItem::firstOrNew([
                    'product_id' => $productId,
                    'location_id' => $this->transferDestinationId,
                ]);

                if (!$destinationItem->exists) {
                    $destinationItem->quantity_on_hand = 0;
                    $destinationItem->quantity_allocated = 0;
                    $destinationItem->unit_cost = $sourceItem->unit_cost;
                }

                $destinationItem->quantity_on_hand += $quantity;
                $destinationItem->quantity_available = $destinationItem->quantity_on_hand - ($destinationItem->quantity_allocated ?? 0);
                $destinationItem->save();
            }

            DB::commit();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => __('messages.stock_transferred_successfully')
            ]);

            $this->closeTransferModal();
            $this->resetTransfer();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao transferir estoque', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('messages.error_transferring_stock')
            ]);
        }
    }
    
    public function closeAdjustmentModal()
    {
        $this->showAdjustmentModal = false;
        $this->adjustmentItem = null;
        $this->selectedProducts = [];
        $this->selectedLocationId = '';
        $this->adjustmentType = '';
        $this->adjustmentQuantities = [];
        $this->adjustmentReason = '';
        $this->adjustmentNotes = '';
    }
    
    public function closeTransferModal()
    {
        $this->showTransferModal = false;
        $this->transferItem = null;
        $this->transferQuantity = '';
        $this->transferDestinationId = '';
        $this->transferReason = '';
        $this->transferNotes = '';
        $this->selectedTransferProducts = [];
        $this->transferQuantities = [];
        $this->transferSourceId = '';
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
            ->with(['product', 'sourceLocation', 'destinationLocation', 'creator'])
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