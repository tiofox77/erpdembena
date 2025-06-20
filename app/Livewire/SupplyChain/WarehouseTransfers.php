<?php

namespace App\Livewire\SupplyChain;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SupplyChain\WarehouseTransferRequest;
use App\Models\SupplyChain\WarehouseTransferRequestItem;
use App\Models\SupplyChain\Inventory;
use App\Models\SupplyChain\InventoryTransaction;
use App\Models\SupplyChain\Product;
use App\Models\SupplyChain\InventoryLocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class WarehouseTransfers extends Component
{
    use WithPagination;

    // Component states
    public $isOpenRequestModal = false;  // For edit/create modal
    public $isOpenViewModal = false;     // For view-only modal
    public $isOpenApprovalModal = false;
    public $isOpenDeleteModal = false;
    public $isOpenItemModal = false;
    public $showTransferModal = false;
    public $editMode = false;
    public $viewMode = false;
    
    // Approval data
    public $approvalNotes = '';
    
    // Form data
    public $transferRequest = [
        'id' => null,
        'source_location_id' => '',
        'destination_location_id' => '',
        'priority' => 'normal',
        'requested_date' => '',
        'required_by_date' => '',
        'notes' => '',
    ];
    
    // Item data
    public $transferItem = [
        'id' => null,
        'product_id' => '',
        'quantity_requested' => 1,
        'notes' => '',
    ];
    
    // Selected data
    public $selectedTransferRequestId;
    public $selectedTransferRequest;
    public $selectedItemIndex;
    public $transferRequestToDelete;
    
    // Data lists
    public $locations = [];
    public $products = [];
    public $items = [];
    
    // Priority options
    public $priorities = [
        'low' => 'Low',
        'normal' => 'Normal',
        'high' => 'High',
        'urgent' => 'Urgent'
    ];
    
    // Alias for view compatibility
    public $priorityOptions = [];
    
    // Available products for selection with stock info
    public $availableProducts = [];
    public $productSearch = '';
    public $filteredProducts = [];
    public $selectedSourceStock = [];
    
    // Track selected item for editing
    public $selectedItem = null;
    
    // Tab navigation
    public $currentTab = 'general';
    
    // Stock level thresholds
    protected $lowStockThreshold = 10;
    protected $mediumStockThreshold = 25;
    
    // Filter and sort
    public $search = '';
    public $statusFilter = '';
    public $priorityFilter = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    
    // Validation rules
    protected function rules()
    {
        return [
            'transferRequest.source_location_id' => 'required|integer|different:transferRequest.destination_location_id',
            'transferRequest.destination_location_id' => 'required|integer|different:transferRequest.source_location_id',
            'transferRequest.priority' => 'required|in:low,normal,high,urgent',
            'transferRequest.requested_date' => 'required|date',
            'transferRequest.required_by_date' => 'nullable|date|after_or_equal:transferRequest.requested_date',
            'transferRequest.notes' => 'nullable|string|max:1000',
            'transferItem.product_id' => 'required|integer|exists:sc_products,id',
            'transferItem.quantity_requested' => 'required|numeric|min:0.01',
            'transferItem.notes' => 'nullable|string|max:500',
        ];
    }
    
    // Validation attributes for custom messages
    protected $validationAttributes = [
        'transferRequest.source_location_id' => 'source location',
        'transferRequest.destination_location_id' => 'destination location',
        'transferRequest.priority' => 'priority',
        'transferRequest.requested_date' => 'requested date',
        'transferRequest.required_by_date' => 'required by date',
    ];
    
    // Event listeners
    protected $listeners = ['refreshTable' => '$refresh'];
    
    /**
     * Initialize the component
     */
    public function mount()
    {
        // Initialize dates
        $this->transferRequest['requested_date'] = now()->format('Y-m-d');
        $this->transferRequest['required_by_date'] = now()->addDays(3)->format('Y-m-d');
        
        // Initialize priorities
        $this->priorityOptions = $this->priorities;
        
        // Load initial data
        $this->loadLocations();
        $this->loadProducts();
        
        // Initialize available products
        $this->availableProducts = $this->products->mapWithKeys(function($product) {
            return [$product->id => $product->name];
        })->toArray();
        
        // Initialize selected item
        $this->selectedItem = null;
        
        // Carregar transferências iniciais
        $this->loadTransferRequests();
        
        $this->filterProducts();
    }
    
    /**
     * Carrega a lista de pedidos de transferência com filtros aplicados
     */
    public function loadTransferRequests()
    {
        $query = WarehouseTransferRequest::query()
            ->with(['sourceLocation', 'destinationLocation', 'requestedBy', 'approvedBy'])
            ->orderBy($this->sortField, $this->sortDirection);
            
        // Aplicar filtros
        if ($this->search) {
            $query->where(function($q) {
                $q->where('request_number', 'like', '%' . $this->search . '%')
                  ->orWhereHas('sourceLocation', function($q) {
                      $q->where('name', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('destinationLocation', function($q) {
                      $q->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }
        
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }
        
        if ($this->priorityFilter) {
            $query->where('priority', $this->priorityFilter);
        }
        
        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }
        
        $this->transferRequests = $query->paginate(10);
    }
    
    /**
     * Load locations for dropdowns
     */
    public function loadLocations()
    {
        $this->locations = \App\Models\SupplyChain\InventoryLocation::orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }
    
    /**
     * Load available products based on source location
     */
    public function loadProducts()
    {
        if (!empty($this->transferRequest['source_location_id'])) {
            // Load products that have stock in the selected source location
            $this->products = Product::select('sc_products.id', 'sc_products.name', 'sc_products.sku', 'sc_products.unit_of_measure', 'sc_products.cost_price')
                ->join('sc_inventory_items', 'sc_products.id', '=', 'sc_inventory_items.product_id')
                ->where('sc_products.is_active', true)
                ->where('sc_inventory_items.location_id', $this->transferRequest['source_location_id'])
                ->where('sc_inventory_items.quantity_available', '>', 0)
                ->where('sc_inventory_items.status', 'available')
                ->orderBy('sc_products.name')
                ->get();
        } else {
            // If no source location selected, load all active products
            $this->products = Product::select('id', 'name', 'sku', 'unit_of_measure', 'cost_price')
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }
        
        // Initialize filtered products
        $this->filteredProducts = $this->products->toArray();
    }

    /**
     * Filter products based on search term and source location stock
     */
    public function filterProducts()
    {
        if (empty($this->productSearch)) {
            $this->filteredProducts = $this->products->toArray();
        } else {
            $this->filteredProducts = $this->products->filter(function($product) {
                return stripos($product->name, $this->productSearch) !== false ||
                       stripos($product->sku, $this->productSearch) !== false;
            })->values()->toArray();
        }
    }
    
    /**
     * Called automatically when productSearch property changes
     */
    public function updatedProductSearch()
    {
        $this->filterProducts();
    }

    /**
     * Called automatically when source location changes
     */
    public function updatedTransferRequestSourceLocationId()
    {
        // Reload products when source location changes
        $this->loadProducts();
        
        // Clear current search
        $this->productSearch = '';
        $this->filteredProducts = $this->products->toArray();
    }

    /**
     * Get stock quantity for a product in the source location
     */
    public function getProductStock($productId)
    {
        if (empty($this->transferRequest['source_location_id'])) {
            return 0;
        }

        $inventoryItem = \App\Models\SupplyChain\InventoryItem::where('product_id', $productId)
            ->where('location_id', $this->transferRequest['source_location_id'])
            ->where('status', 'available')
            ->first();

        return $inventoryItem ? $inventoryItem->quantity_available : 0;
    }

    /**
     * Select a product from the search results
     */
    public function selectProduct($productId)
    {
        $product = \App\Models\SupplyChain\Product::find($productId);
        
        if (!$product) {
            $this->dispatch('notify', 
                type: 'error',
                title: __('messages.error'),
                message: __('messages.product_not_found')
            );
            return;
        }

        // Check if product is already added
        foreach ($this->items as $item) {
            if ($item['product_id'] == $productId) {
                $this->dispatch('notify', 
                    type: 'info',
                    title: __('messages.information'),
                    message: __('messages.product_already_in_list')
                );
                return;
            }
        }

        // Add product to items list
        $this->items[] = [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'quantity_requested' => 1,
            'notes' => '',
            'unit_cost' => $product->cost_price ?? 0,
            'total_cost' => $product->cost_price ?? 0
        ];

        // Clear search and restore full product list
        $this->productSearch = '';
        $this->filteredProducts = $this->products->toArray(); // Restore full list

        $this->dispatch('notify', 
            type: 'success',
            title: __('messages.success'),
            message: __('messages.product_added_successfully')
        );
    }

    /**
     * Quick add product (for popular products section)
     */
    public function quickAddProduct($productId)
    {
        $this->selectProduct($productId);
    }

    /**
     * Remove item from transfer request
     */
    public function removeItem($index)
    {
        if (isset($this->items[$index])) {
            unset($this->items[$index]);
            $this->items = array_values($this->items); // Re-index array
            
            $this->dispatch('notify', 
                type: 'success',
                title: __('messages.success'),
                message: __('messages.product_removed_from_transfer')
            );
        }
    }

    /**
     * Update item quantity and recalculate total
     */
    public function updatedItems($value, $name)
    {
        // Extract index and field from the name (e.g., "0.quantity_requested")
        $nameParts = explode('.', $name);
        if (count($nameParts) == 2) {
            $index = $nameParts[0];
            $field = $nameParts[1];
            
            if ($field == 'quantity_requested' && isset($this->items[$index])) {
                $quantity = (float) $this->items[$index]['quantity_requested'];
                $unitCost = (float) $this->items[$index]['unit_cost'];
                $this->items[$index]['total_cost'] = $quantity * $unitCost;
            }
        }
    }
    
    /**
     * Reset the transfer request form
     */
    public function resetTransferRequest()
    {
        $this->transferRequest = [
            'id' => null,
            'source_location_id' => '',
            'destination_location_id' => '',
            'priority' => 'normal',
            'requested_date' => now()->format('Y-m-d'),
            'required_by_date' => now()->addDays(3)->format('Y-m-d'),
            'notes' => '',
        ];
        $this->resetErrorBag();
    }
    
    /**
     * Reset the transfer item form
     */
    public function resetItem()
    {
        $this->transferItem = [
            'id' => null,
            'product_id' => '',
            'quantity_requested' => 1,
            'notes' => '',
        ];
        $this->selectedItem = null;
        $this->resetErrorBag();
        $this->dispatch('item-form-reset');
    }
    
    /**
     * Open the create transfer request modal
     * Alias for createTransferRequest to match view
     */
    public function openCreateModal()
    {
        return $this->createTransferRequest();
    }
    
    /**
     * Open the create transfer request modal
     */
    public function createTransferRequest()
    {
        $this->resetTransferRequest();
        $this->items = [];
        $this->currentTab = 'general'; // Reset to first tab
        $this->isOpenRequestModal = true;
    }
    
    /**
     * Open the edit transfer request modal
     */
    public function editTransferRequest($id)
    {
        Log::info('=== INÍCIO editTransferRequest ===', ['id' => $id]);
        
        try {
            $transfer = WarehouseTransferRequest::with(['items.product', 'sourceLocation', 'destinationLocation'])->findOrFail($id);
            Log::info('Transfer loaded', ['transfer' => $transfer->toArray()]);

            if (!$transfer->isEditable()) {
                Log::warning('Transfer not editable', ['status' => $transfer->status]);
                $this->dispatch('alert', ['type' => 'error', 'message' => 'This transfer request cannot be edited in its current status.']);
                return;
            }

            // Reset form and populate with transfer data
            $this->resetForm();
            
            // Map database fields to form fields correctly
            $this->transferRequest = [
                'id' => $transfer->id,
                'source_location_id' => $transfer->from_warehouse_id,  // Map from_warehouse_id to source_location_id
                'destination_location_id' => $transfer->to_warehouse_id,  // Map to_warehouse_id to destination_location_id
                'priority' => $transfer->priority,
                'requested_date' => $transfer->requested_date ? $transfer->requested_date->format('Y-m-d') : null,
                'required_by_date' => $transfer->required_by_date ? $transfer->required_by_date->format('Y-m-d') : null,
                'notes' => $transfer->notes,
            ];

            Log::info('Form populated', ['transferRequest' => $this->transferRequest]);

            // Load existing items
            $this->selectedProducts = [];
            foreach ($transfer->items as $item) {
                $this->selectedProducts[$item->product_id] = [
                    'quantity' => $item->quantity_requested,
                    'unit' => $item->unit,
                    'notes' => $item->notes,
                ];
            }

            Log::info('Products loaded', ['selectedProducts' => $this->selectedProducts]);

            // Set flags for editing mode
            $this->isEditing = true;
            $this->showTransferModal = false;
            $this->isOpenRequestModal = true;
            $this->activeTab = 'general';

            Log::info('Modal opened for editing');
        } catch (\Exception $e) {
            Log::error('Error in editTransferRequest', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->dispatch('alert', ['type' => 'error', 'message' => 'Error loading transfer request.']);
        }
        
        Log::info('=== FIM editTransferRequest ===');
    }
    
    /**
     * Save or update a transfer request
     */
    public function saveTransferRequest()
    {
        $this->validate();
        
        try {
            DB::beginTransaction();
            
            // Log para debug
            \Illuminate\Support\Facades\Log::info('=== INÍCIO saveTransferRequest ===', [
                'transferRequest' => $this->transferRequest,
            ]);
            
            // Mapear campos do formulário para os campos corretos da base de dados
            $data = [
                'from_warehouse_id' => $this->transferRequest['source_location_id'], // Corrigido
                'to_warehouse_id' => $this->transferRequest['destination_location_id'], // Corrigido
                'priority' => $this->transferRequest['priority'],
                'requested_date' => $this->transferRequest['requested_date'],
                'required_date' => $this->transferRequest['required_by_date'], // Corrigido
                'notes' => $this->transferRequest['notes'],
                'status' => 'draft',
                'requested_by' => Auth::id(),
            ];
            
            // Se não for uma atualização, precisamos gerar o número do pedido
            if (!$this->transferRequest['id']) {
                $data['request_number'] = WarehouseTransferRequest::generateRequestNumber();
                
                \Illuminate\Support\Facades\Log::info('Gerado número de pedido', [
                    'request_number' => $data['request_number']
                ]);
            }
            
            if ($this->transferRequest['id']) {
                // Update existing
                $transferRequest = WarehouseTransferRequest::findOrFail($this->transferRequest['id']);
                $transferRequest->update($data);
                $message = __('messages.transfer_request_updated');
            } else {
                // Create new
                $transferRequest = WarehouseTransferRequest::create($data);
                $this->selectedTransferRequestId = $transferRequest->id;
                $message = __('messages.transfer_request_created');
            }
            
            // Sync items
            $itemsToSync = [];
            foreach ($this->items as $item) {
                $itemsToSync[$item['product_id']] = [
                    'quantity_requested' => $item['quantity_requested'],
                    'notes' => $item['notes'] ?? null,
                ];
            }
            
            $transferRequest->items()->sync($itemsToSync);
            
            DB::commit();
            
            $this->isOpenRequestModal = false;
            $this->dispatch('notify', 
                type: 'success',
                message: $message
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', 
                type: 'error',
                message: __('messages.error_saving_transfer')
            );
            \Log::error('Error saving transfer request: ' . $e->getMessage());
        }
    }
    
    /**
     * Submit transfer request for approval
     */
    public function submitForApproval($id)
    {
        $transferRequest = WarehouseTransferRequest::findOrFail($id);
        
        if (!$transferRequest->canBeSubmitted()) {
            $this->dispatch('notify', 
                type: 'error',
                message: __('messages.cannot_submit_transfer')
            );
            return;
        }
        
        try {
            $transferRequest->update([
                'status' => 'pending_approval',
                'submitted_at' => now(),
            ]);
            
            $this->dispatch('notify', 
                type: 'success',
                message: __('messages.transfer_submitted')
            );
            
        } catch (\Exception $e) {
            $this->dispatch('notify', 
                type: 'error',
                message: __('messages.error_submitting_transfer')
            );
            \Log::error('Error submitting transfer request: ' . $e->getMessage());
        }
    }
    
    /**
     * Confirm delete transfer request modal
     */
    public function confirmDeleteTransferRequest($id)
    {
        $this->transferRequestToDelete = $id;
        $this->isOpenDeleteModal = true;
    }
    
    /**
     * Approve transfer request
     */
    public function approveTransferRequest($id)
    {
        $transferRequest = WarehouseTransferRequest::findOrFail($id);
        
        if (!$transferRequest->canBeApproved()) {
            $this->dispatch('notify', 
                type: 'error',
                message: __('messages.cannot_approve_transfer')
            );
            return;
        }
        
        try {
            DB::beginTransaction();
            
            $transferRequest->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
            
            // Process inventory movements here
            // This is a simplified example - you'll need to implement your inventory logic
            
            DB::commit();
            
            $this->isOpenApprovalModal = false;
            $this->dispatch('notify', 
                type: 'success',
                message: __('messages.transfer_approved')
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', 
                type: 'error',
                message: __('messages.error_approving_transfer')
            );
            \Log::error('Error approving transfer request: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete transfer request
     */
    public function deleteTransferRequest()
    {
        // Usar transferRequestToDelete (definido em confirmDeleteTransferRequest)
        // ou manter compatibilidade com selectedTransferRequestId para não quebrar funcionalidades existentes
        $idToDelete = $this->transferRequestToDelete ?? $this->selectedTransferRequestId;
        
        if (!$idToDelete) {
            return;
        }
        
        try {
            // Log para debug
            \Illuminate\Support\Facades\Log::info('=== INÍCIO deleteTransferRequest ===', [
                'id' => $idToDelete
            ]);
            
            $transferRequest = WarehouseTransferRequest::findOrFail($idToDelete);
            
            // Verificação de segurança para não eliminar pedidos em processamento
            $nonDeletableStatuses = ['in_progress', 'completed'];
            if (in_array($transferRequest->status, $nonDeletableStatuses) || 
                (method_exists($transferRequest, 'isDeletable') && !$transferRequest->isDeletable())) {
                $this->dispatch('notify', 
                    type: 'error',
                    message: __('messages.cannot_delete_transfer')
                );
                return;
            }
            
            // Usar forceDelete() para eliminação permanente em vez de soft delete
            $transferRequest->forceDelete();
            
            \Illuminate\Support\Facades\Log::info('Registo eliminado permanentemente:', [
                'id' => $idToDelete,
                'request_number' => $transferRequest->request_number
            ]);
            
            // Limpar todas as variáveis relacionadas
            $this->isOpenDeleteModal = false;
            $this->transferRequestToDelete = null;
            
            $this->dispatch('notify', 
                type: 'success',
                message: __('messages.transfer_deleted')
            );
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao excluir pedido de transferência: ' . $e->getMessage(), [
                'exception' => $e,
                'id' => $idToDelete
            ]);
            
            $this->dispatch('notify', 
                type: 'error',
                message: __('messages.error_deleting_transfer')
            );
        }
    }
    
    /**
     * Incrementar a quantidade de um item na lista
     */
    public function incrementQuantity($index)
    {
        \Illuminate\Support\Facades\Log::info('=== INÍCIO incrementQuantity ===', [
            'index' => $index,
            'valor_atual' => $this->items[$index]['quantity_requested'] ?? 'não definido'
        ]);
        
        if (!isset($this->items[$index])) {
            return;
        }
        
        // Incrementar o valor em 1 (ou 0.01 para produtos com casa decimal)
        $atual = (float) $this->items[$index]['quantity_requested'];
        $this->items[$index]['quantity_requested'] = $atual + 1;
        
        \Illuminate\Support\Facades\Log::info('=== FIM incrementQuantity ===', [
            'novo_valor' => $this->items[$index]['quantity_requested']
        ]);
    }
    
    /**
     * Decrementar a quantidade de um item na lista
     */
    public function decrementQuantity($index)
    {
        \Illuminate\Support\Facades\Log::info('=== INÍCIO decrementQuantity ===', [
            'index' => $index,
            'valor_atual' => $this->items[$index]['quantity_requested'] ?? 'não definido'
        ]);
        
        if (!isset($this->items[$index])) {
            return;
        }
        
        // Decrementar o valor em 1 (ou 0.01 para produtos com casa decimal)
        // Garantir que não fique abaixo de 0.01
        $atual = (float) $this->items[$index]['quantity_requested'];
        $novo = max(0.01, $atual - 1);
        $this->items[$index]['quantity_requested'] = $novo;
        
        \Illuminate\Support\Facades\Log::info('=== FIM decrementQuantity ===', [
            'novo_valor' => $this->items[$index]['quantity_requested']
        ]);
    }
    
    /**
     * Open the add item modal
     */
    public function openItemModal()
    {
        $this->resetItem();
        $this->isOpenItemModal = true;
    }
    
    /**
     * Add a new item to the transfer
     */
    public function addItem()
    {
        try {
            $this->validate([
                'transferItem.product_id' => 'required|exists:sc_products,id',
                'transferItem.quantity_requested' => 'required|numeric|min:0.0001',
            ]);

            $product = \App\Models\SupplyChain\Product::findOrFail($this->transferItem['product_id']);

            $this->items[] = [
                'id' => uniqid('item_'),
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_code' => $product->code ?? $product->sku ?? 'N/A',
                'quantity_requested' => (float)$this->transferItem['quantity_requested'],
                'notes' => $this->transferItem['notes'] ?? null,
            ];

            $this->resetItem();
            
            $this->dispatch('notify',
                type: 'success',
                title: __('messages.success'),
                message: __('Item adicionado com sucesso!')
            );
            
            // Close the modal if it's open
            $this->isOpenItemModal = false;
            
        } catch (\Exception $e) {
            $this->dispatch('notify',
                type: 'error',
                title: __('messages.error'),
                message: __('Erro ao adicionar item: ') . $e->getMessage()
            );
        }
    }
    
    /**
     * Edit an existing item
     */
    public function editItem($index)
    {
        try {
            if (!isset($this->items[$index])) {
                throw new \Exception('Item not found');
            }

            $this->selectedItem = $index;
            $this->transferItem = [
                'id' => $this->items[$index]['id'] ?? null,
                'product_id' => $this->items[$index]['product_id'],
                'quantity_requested' => $this->items[$index]['quantity_requested'],
                'notes' => $this->items[$index]['notes'] ?? '',
            ];

            $this->isOpenItemModal = true;
            
            $this->dispatch('notify',
                type: 'info',
                title: __('messages.info'),
                message: __('Editando item')
            );
            
        } catch (\Exception $e) {
            $this->dispatch('notify',
                type: 'error',
                title: __('messages.error'),
                message: __('Erro ao editar item: ') . $e->getMessage()
            );
            $this->resetItem();
        }
    }
    
    /**
     * Close all modals
     */
    public function closeModal()
    {
        $this->isOpenRequestModal = false;
        $this->isOpenApprovalModal = false;
        $this->isOpenViewModal = false;
        $this->isOpenDeleteModal = false;
        $this->isOpenItemModal = false;
        $this->currentTab = 'general'; // Reset tab navigation
        $this->resetTransferRequest();
        $this->resetItem();
    }
    
    /**
     * Set current tab
     */
    public function setTab($tab)
    {
        $this->currentTab = $tab;
    }
    
    /**
     * Navigate to next tab
     */
    public function nextTab()
    {
        switch ($this->currentTab) {
            case 'general':
                $this->currentTab = 'products';
                break;
            case 'products':
                $this->currentTab = 'review';
                break;
            default:
                $this->currentTab = 'general';
                break;
        }
    }
    
    /**
     * Navigate to previous tab
     */
    public function previousTab()
    {
        switch ($this->currentTab) {
            case 'review':
                $this->currentTab = 'products';
                break;
            case 'products':
                $this->currentTab = 'general';
                break;
            default:
                $this->currentTab = 'general';
                break;
        }
    }
    
    /**
     * Save transfer request as draft
     */
    public function saveDraft()
    {
        try {
            $this->validate([
                'transferRequest.source_location_id' => 'required|integer|different:transferRequest.destination_location_id',
                'transferRequest.destination_location_id' => 'required|integer|different:transferRequest.source_location_id',
                'transferRequest.priority' => 'required|in:low,normal,high,urgent',
            ]);

            if ($this->selectedTransferRequestId) {
                // Update existing request
                $transferRequest = WarehouseTransferRequest::findOrFail($this->selectedTransferRequestId);
                $transferRequest->update([
                    'from_warehouse_id' => $this->transferRequest['source_location_id'],
                    'to_warehouse_id' => $this->transferRequest['destination_location_id'],
                    'priority' => $this->transferRequest['priority'],
                    'requested_date' => $this->transferRequest['requested_date'] ?: now()->format('Y-m-d'),
                    'required_date' => $this->transferRequest['required_by_date'],
                    'notes' => $this->transferRequest['notes'],
                    'status' => 'draft',
                ]);
            } else {
                // Create new request
                $transferRequest = WarehouseTransferRequest::create([
                    'request_number' => 'TR-' . date('Ymd') . '-' . str_pad(WarehouseTransferRequest::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT),
                    'from_warehouse_id' => $this->transferRequest['source_location_id'],
                    'to_warehouse_id' => $this->transferRequest['destination_location_id'],
                    'priority' => $this->transferRequest['priority'],
                    'requested_date' => $this->transferRequest['requested_date'] ?: now()->format('Y-m-d'),
                    'required_date' => $this->transferRequest['required_by_date'],
                    'notes' => $this->transferRequest['notes'],
                    'status' => 'draft',
                    'requested_by' => Auth::id(),
                ]);
                
                $this->selectedTransferRequestId = $transferRequest->id;
            }

            // Save items if any
            if (!empty($this->items)) {
                // Clear existing items
                $transferRequest->items()->delete();
                
                // Add new items
                foreach ($this->items as $item) {
                    $transferRequest->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity_requested' => $item['quantity_requested'],
                        'notes' => $item['notes'] ?? null,
                    ]);
                }
            }

            $this->dispatch('notify',
                type: 'success',
                title: __('messages.success'),
                message: __('Transfer request saved as draft successfully')
            );

        } catch (\Exception $e) {
            $this->dispatch('notify',
                type: 'error',
                title: __('messages.error'),
                message: __('Error saving draft: ') . $e->getMessage()
            );
        }
    }
    
    /**
     * Get the transfer requests for the current page
     */
    public function getTransferRequestsProperty()
    {
        $query = WarehouseTransferRequest::with(['sourceLocation', 'destinationLocation', 'requestedBy'])
            ->orderBy($this->sortField, $this->sortDirection);
        
        // Apply filters
        if ($this->search) {
            $query->where(function($q) {
                $q->where('reference_number', 'like', '%' . $this->search . '%')
                  ->orWhere('notes', 'like', '%' . $this->search . '%')
                  ->orWhereHas('sourceLocation', function($q) {
                      $q->where('name', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('destinationLocation', function($q) {
                      $q->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }
        
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }
        
        if ($this->priorityFilter) {
            $query->where('priority', $this->priorityFilter);
        }
        
        if ($this->dateFrom) {
            $query->whereDate('requested_date', '>=', $this->dateFrom);
        }
        
        if ($this->dateTo) {
            $query->whereDate('requested_date', '<=', $this->dateTo);
        }
        
        return $query->paginate(10);
    }
    
    /**
     * Sort the table by the given field
     */
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        
        $this->sortField = $field;
    }

    /**
 * Submit transfer request for approval
 */
public function submitRequest()
{
    \Log::info('WarehouseTransfers::submitRequest() - Starting submission process');
    
    try {
        \Log::info('WarehouseTransfers::submitRequest() - Validating form data', [
            'transferRequest' => $this->transferRequest,
            'items_count' => count($this->items)
        ]);
        
        $this->validate([
            'transferRequest.source_location_id' => 'required|integer|different:transferRequest.destination_location_id',
            'transferRequest.destination_location_id' => 'required|integer|different:transferRequest.source_location_id',
            'transferRequest.priority' => 'required|in:low,normal,high,urgent',
            'transferRequest.requested_date' => 'required|date',
            'transferRequest.required_by_date' => 'nullable|date|after_or_equal:transferRequest.requested_date',
            'transferRequest.notes' => 'nullable|string|max:1000',
        ]);

        \Log::info('WarehouseTransfers::submitRequest() - Validation passed');

        // Validate that there are items
        if (empty($this->items)) {
            \Log::warning('WarehouseTransfers::submitRequest() - No items in transfer request');
            $this->dispatch('notify',
                type: 'error',
                title: __('messages.error'),
                message: __('Please add at least one product to the transfer request')
            );
            return;
        }

        \Log::info('WarehouseTransfers::submitRequest() - Starting database transaction');
        DB::beginTransaction();

        if ($this->selectedTransferRequestId) {
            \Log::info('WarehouseTransfers::submitRequest() - Updating existing request', ['id' => $this->selectedTransferRequestId]);
            
            // Update existing request
            $transferRequest = WarehouseTransferRequest::findOrFail($this->selectedTransferRequestId);
            $transferRequest->update([
                'from_warehouse_id' => $this->transferRequest['source_location_id'],
                'to_warehouse_id' => $this->transferRequest['destination_location_id'],
                'priority' => $this->transferRequest['priority'],
                'requested_date' => $this->transferRequest['requested_date'] ?: now()->format('Y-m-d'),
                'required_date' => $this->transferRequest['required_by_date'],
                'notes' => $this->transferRequest['notes'],
                'status' => 'pending_approval',
            ]);
        } else {
            \Log::info('WarehouseTransfers::submitRequest() - Creating new request');
            
            // Create new request
            $transferRequest = WarehouseTransferRequest::create([
                'request_number' => 'TR-' . date('Ymd') . '-' . str_pad(WarehouseTransferRequest::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT),
                'from_warehouse_id' => $this->transferRequest['source_location_id'],
                'to_warehouse_id' => $this->transferRequest['destination_location_id'],
                'priority' => $this->transferRequest['priority'],
                'requested_date' => $this->transferRequest['requested_date'] ?: now()->format('Y-m-d'),
                'required_date' => $this->transferRequest['required_by_date'],
                'notes' => $this->transferRequest['notes'],
                'status' => 'pending_approval',
                'requested_by' => Auth::id(),
            ]);
            
            $this->selectedTransferRequestId = $transferRequest->id;
        }

        \Log::info('WarehouseTransfers::submitRequest() - Transfer request saved', ['request_id' => $transferRequest->id]);

        // Save items if any
        if (!empty($this->items)) {
            \Log::info('WarehouseTransfers::submitRequest() - Saving items', ['items_count' => count($this->items)]);
            
            // Clear existing items
            $transferRequest->items()->delete();
            
            // Add new items
            foreach ($this->items as $item) {
                $transferRequest->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity_requested' => $item['quantity_requested'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }
            
            \Log::info('WarehouseTransfers::submitRequest() - Items saved successfully');
        }

        DB::commit();
        \Log::info('WarehouseTransfers::submitRequest() - Transaction committed successfully');

        $this->dispatch('notify',
            type: 'success',
            title: __('messages.success'),
            message: __('Transfer request submitted successfully')
        );

        // Reset form and close modal
        \Log::info('WarehouseTransfers::submitRequest() - Resetting form and closing modal');
        $this->resetForm();
        $this->showTransferModal = false;

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('WarehouseTransfers::submitRequest() - Error occurred', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        $this->dispatch('notify',
            type: 'error',
            title: __('messages.error'),
            message: __('Error submitting request: ') . $e->getMessage()
        );
    }
}

    /**
     * Reset the form to initial state
     */
    public function resetForm()
    {
        \Log::info('WarehouseTransfers::resetForm() - Resetting form');
        
        $this->resetTransferRequest();
        $this->resetItem();
        $this->items = [];
        $this->selectedTransferRequestId = null;
        $this->selectedTransferRequest = null;
        $this->approvalNotes = '';
        $this->currentTab = 'general';
        $this->editMode = false;
        $this->viewMode = false;
        $this->showTransferModal = false;
        $this->isOpenRequestModal = false;
        $this->isOpenViewModal = false;
        $this->isOpenApprovalModal = false;
        
        \Log::info('WarehouseTransfers::resetForm() - Form reset complete');
    }

    /**
     * View transfer request details
     */
    public function viewTransferRequest($id)
    {
        $transferRequest = WarehouseTransferRequest::with(['sourceLocation', 'destinationLocation', 'requestedBy', 'items.product'])
            ->findOrFail($id);

        $this->selectedTransferRequestId = $transferRequest->id;
        $this->selectedTransferRequest = $transferRequest;
        
        // Handle dates - check if they are strings or Carbon objects
        $requestedDate = $transferRequest->requested_date;
        $requiredDate = $transferRequest->required_date;
        
        if (is_string($requestedDate) && !empty($requestedDate)) {
            $requestedDateFormatted = $requestedDate;
        } elseif ($requestedDate instanceof \Carbon\Carbon) {
            $requestedDateFormatted = $requestedDate->format('Y-m-d');
        } else {
            $requestedDateFormatted = '';
        }
        
        if (is_string($requiredDate) && !empty($requiredDate)) {
            $requiredDateFormatted = $requiredDate;
        } elseif ($requiredDate instanceof \Carbon\Carbon) {
            $requiredDateFormatted = $requiredDate->format('Y-m-d');
        } else {
            $requiredDateFormatted = '';
        }
        
        $this->transferRequest = [
            'source_location_id' => $transferRequest->from_warehouse_id,
            'destination_location_id' => $transferRequest->to_warehouse_id,
            'priority' => $transferRequest->priority,
            'requested_date' => $requestedDateFormatted,
            'required_by_date' => $requiredDateFormatted,
            'notes' => $transferRequest->notes,
            'status' => $transferRequest->status,
            'request_number' => $transferRequest->request_number,
        ];

        // Load items
        $this->items = $transferRequest->items->map(function($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name ?? 'N/A',
                'quantity_requested' => $item->quantity_requested,
                'notes' => $item->notes,
            ];
        })->toArray();

        $this->editMode = false;
        $this->viewMode = true;
        $this->currentTab = 'general';
        $this->isOpenViewModal = true;  // Changed from showTransferModal
    }

    /**
     * Check if source warehouse is selected
     */
    public function getHasSourceWarehouseProperty()
    {
        return !empty($this->transferRequest['source_location_id']) && 
               $this->transferRequest['source_location_id'] !== '' && 
               $this->transferRequest['source_location_id'] !== null;
    }

    /**
     * Get the selected source warehouse name
     */
    public function getSourceWarehouseNameProperty()
    {
        if ($this->hasSourceWarehouse) {
            return $this->locations[$this->transferRequest['source_location_id']] ?? 'Unknown Warehouse';
        }
        return null;
    }

    /**
     * Check if destination warehouse is selected
     */
    public function getHasDestinationWarehouseProperty()
    {
        return !empty($this->transferRequest['destination_location_id']) && 
               $this->transferRequest['destination_location_id'] !== '' && 
               $this->transferRequest['destination_location_id'] !== null;
    }

    /**
     * Get the selected destination warehouse name
     */
    public function getDestinationWarehouseNameProperty()
    {
        if ($this->hasDestinationWarehouse) {
            return $this->locations[$this->transferRequest['destination_location_id']] ?? 'Unknown Warehouse';
        }
        return null;
    }
    
    /**
     * Accept a transfer request and process inventory stock movement
     */
    public function acceptTransferRequest()
    {
        try {
            DB::beginTransaction();
            
            $transferRequest = WarehouseTransferRequest::with('items.product')->findOrFail($this->selectedTransferRequestId);
            
            if (!$transferRequest->canBeApproved()) {
                throw new \Exception('Transfer request cannot be approved in current status: ' . $transferRequest->status);
            }
            
            // Update status to approved
            $transferRequest->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'approval_notes' => $this->approvalNotes, // Include approval notes
            ]);
            
            // Processar movimentação de stock
            foreach ($transferRequest->items as $item) {
                // 1. Criar transação de inventário para registar o movimento
                $transaction = InventoryTransaction::create([
                    'transaction_number' => 'TRTX-' . $transferRequest->request_number . '-' . $item->id,
                    'transaction_type' => InventoryTransaction::TYPE_TRANSFER,
                    'product_id' => $item->product_id,
                    'source_location_id' => $transferRequest->from_warehouse_id,
                    'destination_location_id' => $transferRequest->to_warehouse_id,
                    'quantity' => $item->quantity_requested,
                    'reference_id' => $transferRequest->id,
                    'reference_type' => WarehouseTransferRequest::class,
                    'created_by' => auth()->id(),
                    'notes' => 'Transferência automática: ' . $transferRequest->request_number,
                ]);
                
                // 2. Decrementar stock na localização de origem
                $sourceInventory = Inventory::where('product_id', $item->product_id)
                    ->where('location_id', $transferRequest->from_warehouse_id)
                    ->first();
                    
                if (!$sourceInventory || $sourceInventory->quantity_on_hand < $item->quantity_requested) {
                    throw new \Exception('Stock insuficiente para produto ' . ($item->product->name ?? $item->product_id) . 
                        ' no armazém de origem. Disponível: ' . ($sourceInventory->quantity_on_hand ?? 0));
                }
                
                $sourceInventory->update([
                    'quantity_on_hand' => $sourceInventory->quantity_on_hand - $item->quantity_requested,
                    'quantity_available' => $sourceInventory->quantity_available - $item->quantity_requested,
                ]);
                
                // 3. Incrementar stock na localização de destino
                $destinationInventory = Inventory::firstOrNew(
                    [
                        'product_id' => $item->product_id,
                        'location_id' => $transferRequest->to_warehouse_id,
                    ],
                    [
                        'quantity_on_hand' => 0,
                        'quantity_allocated' => 0,
                        'quantity_available' => 0,
                    ]
                );
                
                if (!$destinationInventory->exists) {
                    $destinationInventory->quantity_on_hand = $item->quantity_requested;
                    $destinationInventory->quantity_available = $item->quantity_requested;
                    $destinationInventory->save();
                } else {
                    $destinationInventory->update([
                        'quantity_on_hand' => $destinationInventory->quantity_on_hand + $item->quantity_requested,
                        'quantity_available' => $destinationInventory->quantity_available + $item->quantity_requested,
                    ]);
                }
                
                Log::info('Stock transferred successfully', [
                    'transaction_id' => $transaction->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity_requested,
                    'from_warehouse' => $transferRequest->from_warehouse_id,
                    'to_warehouse' => $transferRequest->to_warehouse_id,
                ]);
            }
            
            DB::commit();
            
            $this->dispatch('notify', 
                type: 'success',
                title: __('messages.success'),
                message: __('messages.transfer_accepted_successfully')
            );
            
            // Close approval modal and reset
            $this->isOpenApprovalModal = false;
            $this->resetForm();
            $this->loadTransferRequests();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error accepting transfer request: ' . $e->getMessage());
            $this->dispatch('notify', 
                type: 'error',
                title: __('messages.error'),
                message: $e->getMessage()
            );
        }
    }
    
    /**
     * Reject a transfer request
     */
    public function rejectTransferRequest()
    {
        try {
            DB::beginTransaction();
            
            $transferRequest = WarehouseTransferRequest::findOrFail($this->selectedTransferRequestId);
            
            if (!$transferRequest->canBeRejected()) {
                $this->dispatch('notify', 
                    type: 'error',
                    message: __('messages.transfer_not_pending')
                );
                return;
            }
            
            // Verificar se foi fornecida uma nota para rejeição
            if (empty($this->approvalNotes)) {
                $this->dispatch('notify', 
                    type: 'error',
                    message: __('messages.rejection_notes_required')
                );
                return;
            }
            
            $transferRequest->update([
                'status' => WarehouseTransferRequest::STATUS_REJECTED,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'approval_notes' => $this->approvalNotes, // Include approval notes
            ]);
            
            DB::commit();
            
            $this->dispatch('notify', 
                type: 'success',
                message: __('messages.transfer_rejected_successfully')
            );
            
            // Close modals and reset
            $this->isOpenViewModal = false;
            $this->isOpenApprovalModal = false;
            $this->resetForm();
            $this->loadTransferRequests();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting transfer request: ' . $e->getMessage());
            $this->dispatch('notify', 
                type: 'error',
                message: __('messages.error_rejecting_transfer')
            );
        }
    }
    
    /**
     * Close view modal
     */
    public function closeViewModal()
    {
        Log::info('=== CLOSING VIEW MODAL ===');
        
        $this->isOpenViewModal = false;
        $this->selectedTransferRequestId = null;
        $this->selectedTransferRequest = null;
        
        Log::info('View modal closed successfully');
    }
    
    /**
     * Open approval modal
     */
    public function openApprovalModal()
    {
        Log::info('=== OPENING APPROVAL MODAL ===', ['selected_id' => $this->selectedTransferRequestId]);
        
        if (!$this->selectedTransferRequestId) {
            Log::error('No transfer request selected');
            $this->dispatch('notify', 
                type: 'error', 
                message: __('messages.no_transfer_selected')
            );
            return;
        }
        
        try {
            $transferRequest = WarehouseTransferRequest::with(['sourceLocation', 'destinationLocation', 'requestedBy', 'items.product'])
                ->findOrFail($this->selectedTransferRequestId);
            
            Log::info('Transfer request loaded', [
                'id' => $transferRequest->id,
                'status' => $transferRequest->status,
                'can_be_approved' => $transferRequest->canBeApproved()
            ]);
            
            if (!$transferRequest->canBeApproved()) {
                Log::error('Transfer request cannot be approved', [
                    'status' => $transferRequest->status,
                    'expected_status' => WarehouseTransferRequest::STATUS_PENDING
                ]);
                $this->dispatch('notify', 
                    type: 'error', 
                    message: __('Transfer request cannot be approved. Current status: ' . $transferRequest->status)
                );
                return;
            }
            
            $this->selectedTransferRequest = $transferRequest;
            
            // Close view modal and open approval modal
            $this->isOpenViewModal = false;
            $this->isOpenApprovalModal = true;
            
            Log::info('Approval modal opened successfully', [
                'request_id' => $this->selectedTransferRequestId,
                'isOpenApprovalModal' => $this->isOpenApprovalModal
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error opening approval modal: ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString()
            ]);
            $this->dispatch('notify', 
                type: 'error',
                message: __('Error loading transfer: ' . $e->getMessage())
            );
        }
    }

    /**
     * Render the component
     */
    public function render()
    {
        // Format products for select dropdown
        $availableProducts = $this->products->mapWithKeys(function($product) {
            return [$product->id => $product->name];
        })->toArray();

        return view('livewire.supply-chain.warehouse-transfers', [
            'transferRequests' => $this->transferRequests,
            'availableProducts' => $availableProducts,
            'statusOptions' => [
                'draft' => __('Draft'),
                'pending_approval' => __('Pending Approval'),
                'approved' => __('Approved'),
                'in_transit' => __('In Transit'),
                'completed' => __('Completed'),
                'rejected' => __('Rejected'),
                'cancelled' => __('Cancelled'),
            ],
            'priorityOptions' => [
                'low' => __('Low'),
                'normal' => __('Normal'),
                'high' => __('High'),
                'urgent' => __('Urgent'),
            ],
        ]);
    }
}
