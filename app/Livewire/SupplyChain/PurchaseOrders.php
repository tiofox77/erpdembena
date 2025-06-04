<?php

namespace App\Livewire\SupplyChain;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\Product;
use App\Models\SupplyChain\PurchaseOrder;
use App\Models\SupplyChain\PurchaseOrderItem;
use App\Models\SupplyChain\ShippingNote;
use App\Models\SupplyChain\CustomForm;
use App\Models\SupplyChain\CustomFormSubmission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseOrders extends Component
{
    use WithPagination;
    use \Livewire\WithFileUploads;
    
    public $search = '';
    public $perPage = 10;
    public $statusFilter = '';
    public $supplierFilter = '';
    public $activeFilter = 'active'; // Padrão: mostrar apenas ordens ativas
    public $monthFilter = ''; // Filtro por mês (1-12)
    public $yearFilter = ''; // Filtro por ano
    public $dateField = 'order_date'; // Campo de data a ser filtrado (order_date, expected_delivery_date, etc)
    public $customFormFilter = ''; // Filtro por formulário personalizado
    public $customFormStatusFilter = ''; // Filtro por status de formulário personalizado
    public $sortField = 'order_date';
    public $sortDirection = 'desc';
    
    // Create/Edit Modal Properties
    public $showModal = false;
    public $editMode = false;
    public $purchaseOrder = [];
    public $orderItems = [];
    public $orderTotal = 0;
    public $selectedSupplier = null;
    public $selectedProducts = [];
    
    // View Modal Properties
    public $showViewModal = false;
    public $viewingOrder = null;
    public $activeTab = 'details';
    
    // Delete Confirmation Properties
    public $showConfirmDelete = false;
    public $deleteId = null;
    public $showDeleteModal = false;
    public $deleteOrderId = null;
    
    // Product Selection Properties
    public $productSearch = '';
    public $products = [];
    
    // Shipping Notes Modal Properties
    public $showShippingNotesModal = false;
    public $viewingOrderId = null;
    public $shippingNote = [
        'status' => '',
        'note' => '',
        'custom_form_id' => null,
    ];
    public $shippingAttachment = null;
    public $shippingNotes = [];
    public $selectedCustomForm = null;
    public $renderCustomForm = false;
    public $customFormFields = [];
    public $formData = [];

    protected $listeners = [
        'productSelected' => 'addProduct',
        'refreshPurchaseOrders' => '$refresh',
        'refreshShippingNotes' => '$refresh'
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
    
    // Reset pagination when any filter changes
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
    
    public function updatingActiveFilter()
    {
        $this->resetPage();
    }
    
    public function updatingMonthFilter()
    {
        $this->resetPage();
    }
    
    public function updatingYearFilter()
    {
        $this->resetPage();
    }
    
    public function updatingDateField()
    {
        $this->resetPage();
    }
    
    public function updatingCustomFormFilter()
    {
        $this->resetPage();
    }
    
    public function updatingCustomFormStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->supplierFilter = '';
        $this->activeFilter = 'active';
        $this->monthFilter = '';
        $this->yearFilter = '';
        $this->dateField = 'order_date';
        $this->customFormFilter = '';
        $this->customFormStatusFilter = '';
        $this->perPage = 10;
        $this->resetPage();
    }

    public function render()
    {
        $suppliers = Supplier::orderBy('name')->get();
        
        // Carregar formulários personalizados ativos para shipping notes
        $customForms = \App\Models\SupplyChain\CustomForm::where('entity_type', 'shipping_note')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        $purchaseOrdersQuery = PurchaseOrder::with(['supplier', 'createdBy', 'items'])
            ->when($this->search, function($query) {
                return $query->where(function($q) {
                    $q->where('order_number', 'like', '%' . $this->search . '%')
                      ->orWhereHas('supplier', function($subQuery) {
                          $subQuery->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->statusFilter, function($query) {
                return $query->where('status', $this->statusFilter);
            })
            ->when($this->activeFilter, function($query) {
                if ($this->activeFilter === 'active') {
                    return $query->where('is_active', true);
                } elseif ($this->activeFilter === 'inactive') {
                    return $query->where('is_active', false);
                }
                // Se for 'all', não aplica filtro
                return $query;
            })
            // Filtro por mês
            ->when($this->monthFilter, function($query) {
                return $query->whereRaw("MONTH({$this->dateField}) = ?", [$this->monthFilter]);
            })
            // Filtro por ano
            ->when($this->yearFilter, function($query) {
                return $query->whereRaw("YEAR({$this->dateField}) = ?", [$this->yearFilter]);
            })
            ->when($this->supplierFilter, function($query) {
                return $query->where('supplier_id', $this->supplierFilter);
            })
            ->when($this->customFormFilter, function($query) {
                $query = $query->whereHas('shippingNotes', function($q) {
                    $q->where('custom_form_id', $this->customFormFilter);
                });
                
                // Se tiver filtro de status, aplicar um filtro adicional
                if ($this->customFormStatusFilter) {
                    // Obter o ID do campo configurado como campo de status
                    $statusFieldId = DB::table('sc_custom_forms')
                        ->where('id', $this->customFormFilter)
                        ->whereRaw('JSON_EXTRACT(status_display_config, "$.enabled") = true')
                        ->value(DB::raw('JSON_UNQUOTE(JSON_EXTRACT(status_display_config, "$.field_id"))'));
                    
                    if ($statusFieldId) {
                        // Obter IDs das shipping notes que têm o status selecionado
                        $shippingNoteIds = DB::table('sc_custom_form_submissions as s')
                            ->join('sc_custom_form_field_values as v', 's.id', '=', 'v.submission_id')
                            ->where('s.form_id', $this->customFormFilter)
                            ->where('v.field_id', $statusFieldId)
                            ->where('v.value', $this->customFormStatusFilter)
                            ->pluck('s.entity_id')
                            ->toArray();
                        
                        // Filtrar purchase orders que tenham shipping notes com os IDs filtrados
                        if (!empty($shippingNoteIds)) {
                            $query->whereHas('shippingNotes', function($q) use ($shippingNoteIds) {
                                $q->whereIn('id', $shippingNoteIds);
                            });
                        } else {
                            // Se não encontrar nenhum registro com este status, força retornar vazio
                            $query->whereRaw('1 = 0');
                        }
                    }
                }
                
                return $query;
            })
            ->orderBy($this->sortField, $this->sortDirection);
        
        $purchaseOrders = $purchaseOrdersQuery->paginate($this->perPage);
        
        // Calculate total amounts and check if orders are overdue
        foreach($purchaseOrders as $order) {
            $order->total_amount = $order->items->sum(function($item) {
                return $item->quantity * $item->unit_price;
            });
            
            $order->is_overdue = $order->expected_delivery_date && 
                in_array($order->status, ['ordered', 'approved', 'partially_received']) && 
                now()->gt($order->expected_delivery_date);
                
            // Pegar a shipping note mais recente para cada pedido
            $latestShippingNote = ShippingNote::where('purchase_order_id', $order->id)
                ->orderBy('created_at', 'desc')
                ->first();
                
            if ($latestShippingNote) {
                $order->latest_shipping_note = $latestShippingNote;
                $order->shipping_status = $latestShippingNote->status;
                $order->shipping_status_date = $latestShippingNote->created_at;
                Log::info('Shipping status encontrado', [
                    'orderId' => $order->id,
                    'status' => $latestShippingNote->status,
                    'date' => $latestShippingNote->created_at
                ]);
            } else {
                $order->shipping_status = null;
                $order->shipping_status_date = null;
                $order->latest_shipping_note = null;
                Log::info('Nenhum shipping status encontrado', ['orderId' => $order->id]);
            }
        }
        
        return view('livewire.supply-chain.purchase-orders', [
            'purchaseOrders' => $purchaseOrders,
            'suppliers' => $suppliers,
            'products' => Product::orderBy('name')->get(),
            'customForms' => $customForms,
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
    
    public $productPage = 1;
    public $productPerPage = 15;
    public $hasMoreProducts = true;
    public $isLoadingProducts = false;

    public function updatedProductSearch($value)
    {
        $this->productPage = 1;
        $this->hasMoreProducts = true;
        $this->loadProducts();
    }
    
    public $totalProducts = 0;
    
    public function loadProducts($loadMore = false)
    {
        if ($loadMore) {
            $this->productPage++;
        } else {
            $this->productPage = 1;
            $this->products = [];
        }

        $this->isLoadingProducts = true;
        $this->dispatch('loading', loading: true);

        try {
            $query = Product::query()
                ->when($this->productSearch, function($query) {
                    $search = strtolower($this->productSearch);
                    return $query->where(function($q) use ($search) {
                        $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                          ->orWhereRaw('LOWER(sku) LIKE ?', ["%{$search}%"])
                          ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"]);
                    });
                });
                
            $products = $query->paginate(
                $this->productPerPage,
                ['*'],
                'page',
                $this->productPage
            );

            $this->products = $loadMore 
                ? array_merge($this->products, $products->items())
                : $products->items();

            $this->hasMoreProducts = $products->hasMorePages();
            
            // Reset to first page if no results and we have a search term
            if (empty($this->products) && $this->productSearch && $this->productPage > 1) {
                $this->productPage = 1;
                $this->loadProducts();
                return;
            }
            
        } catch (\Exception $e) {
            Log::error('Error loading products: ' . $e->getMessage());
            $this->dispatch('notify', 
                type: 'error',
                message: __('messages.error_loading_products')
            );
            session()->flash('error', 'Error loading products. Please try again.');
        } finally {
            $this->isLoadingProducts = false;
        }
    }
    
    public function loadMoreProducts()
    {
        // Prevent multiple simultaneous loads and check if there are more products to load
        if ($this->isLoadingProducts || !$this->hasMoreProducts) {
            return;
        }
        
        $this->isLoadingProducts = true;
        
        try {
            // Load the next page of products
            $this->productPage++;
            
            $query = Product::query()
                ->when($this->productSearch, function($query) {
                    $search = strtolower($this->productSearch);
                    return $query->where(function($q) use ($search) {
                        $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                          ->orWhereRaw('LOWER(product_code) LIKE ?', ["%{$search}%"])
                          ->orWhereRaw('LOWER(barcode) LIKE ?', ["%{$search}%"])
                          ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"]);
                    });
                });
                
            // Get paginated results
            $products = $query->orderBy('name')
                ->paginate($this->productPerPage, ['*'], 'page', $this->productPage);
                
            $this->hasMoreProducts = $products->hasMorePages();
            
            // Merge new products with existing ones
            $this->products = array_merge($this->products, $products->items());
            
            // Update total count if this is the first load
            if ($this->productPage === 1) {
                $this->totalProducts = $products->total();
            }
            
            // Let the view know we've loaded products
            $this->dispatch('productsLoaded');
            
        } catch (\Exception $e) {
            // Log the error and reset loading state
            \Log::error('Error loading more products: ' . $e->getMessage());
            $this->productPage = max(1, $this->productPage - 1);
        } finally {
            $this->isLoadingProducts = false;
        }
    }
    
    public function openCreateModal()
    {
        $this->resetValidation();
        $this->resetExcept(['search', 'perPage', 'statusFilter', 'supplierFilter', 'sortField', 'sortDirection']);
        
        $this->purchaseOrder = [
            'order_number' => 'PO' . now()->format('ymd') . rand(1000, 9999),
            'supplier_id' => '',
            'order_date' => now()->format('Y-m-d'),
            'expected_delivery_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'draft',
            'notes' => '',
            'shipping_amount' => 0.00,
        ];
        
        $this->orderItems = [];
        $this->orderTotal = 0;
        $this->showModal = true;
        $this->editMode = false;
    }
    
    public function viewOrder($id)
    {
        $order = PurchaseOrder::with(['supplier', 'createdBy', 'items.product', 'shippingNotes'])
            ->findOrFail($id);
        
        $this->viewingOrder = $order;
        $this->showViewModal = true;
        $this->activeTab = 'details';
    }
    
    public function changeTab($tab)
    {
        $this->activeTab = $tab;
    }
    
    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewingOrder = null;
        $this->activeTab = 'details';
    }
    
    public function editOrder($id)
    {
        $order = PurchaseOrder::with('items.product')->find($id);
        
        if(!$order) {
            return $this->dispatch('notify', type: 'error', title: __('messages.error'), message: __('messages.purchase_order_not_found'));
        }
        
        if(in_array($order->status, ['completed', 'cancelled'])) {
            return $this->dispatch('notify', type: 'error', title: __('messages.error'), message: __('messages.cannot_edit_completed_cancelled_order'));

            $this->dispatch('notify', type: 'error', title: __('messages.access_denied'), message: __('messages.no_permission'));
            
        }
        
        $this->resetValidation();
        $this->resetExcept(['search', 'perPage', 'statusFilter', 'supplierFilter', 'sortField', 'sortDirection']);
        
        $this->purchaseOrder = [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'supplier_id' => $order->supplier_id,
            'order_date' => $order->order_date ? date('Y-m-d', strtotime($order->order_date)) : null,
            'expected_delivery_date' => $order->expected_delivery_date ? date('Y-m-d', strtotime($order->expected_delivery_date)) : null,
            'other_reference' => $order->other_reference,
            'status' => $order->status,
            'notes' => $order->notes,
            'shipping_amount' => $order->shipping_amount ?? 0.00,
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
        
        $this->orderTotal = array_sum(array_column($this->orderItems, 'line_total'));
        
        $this->showModal = true;
        $this->editMode = true;
    }
    
    public function closeModal()
    {
        $this->showModal = false;
        $this->editMode = false;
        $this->purchaseOrder = [];
        $this->orderItems = [];
        $this->orderTotal = 0;
        $this->selectedSupplier = null;
        $this->selectedProducts = [];
        $this->resetValidation();
    }
    
    public function addItem()
    {
        // Adicionar um item em branco para ser preenchido
        $this->orderItems[] = [
            'product_id' => null,
            'product_name' => '',
            'product_code' => '',
            'quantity' => 1,
            'unit_cost' => 0,
        ];
    }
    
    public function confirmDelete($id)
    {
        $order = PurchaseOrder::find($id);
        
        if(!$order) {
            return $this->dispatch('notify', type: 'error', title: __('messages.error'), message: __('messages.purchase_order_not_found'));
        }
        
        if(!in_array($order->status, ['draft', 'pending_approval'])) {
            return $this->dispatch('notify', type: 'error', title: __('messages.error'), message: __('messages.cannot_delete_processed_order'));
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
            return $this->dispatch('notify', type: 'error', title: __('messages.error'), message: __('messages.purchase_order_not_found'));
        }
        
        if(!in_array($order->status, ['draft', 'pending_approval'])) {
            return $this->dispatch('notify', type: 'error', title: __('messages.error'), message: __('messages.cannot_delete_processed_order'));
        }
        
        try {
            DB::beginTransaction();
            
            // Delete order items first
            PurchaseOrderItem::where('purchase_order_id', $order->id)->delete();
            
            // Delete the order
            $order->delete();
            
            DB::commit();
            
            $this->dispatch('notify', type: 'success', title: __('messages.success'), message: __('messages.purchase_order_deleted'));
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->dispatch('notify', type: 'error', title: __('messages.error'), message: __('messages.purchase_order_delete_failed'));
        }
        
        $this->showConfirmDelete = false;
        $this->deleteId = null;
    }
    
    public function addProduct($productId)
    {
        try {
            $product = Product::findOrFail($productId);
            
            // Verificar se o produto já existe na ordem
            $existingProductIndex = null;
            foreach ($this->orderItems as $index => $item) {
                if ($item['product_id'] == $product->id) {
                    $existingProductIndex = $index;
                    break;
                }
            }
            
            // Se o produto já existe, aumentar a quantidade em vez de duplicar
            if ($existingProductIndex !== null) {
                // Aumenta a quantidade em 1
                $this->orderItems[$existingProductIndex]['quantity'] += 1;
                
                // Recalcula o total da linha
                $quantity = $this->orderItems[$existingProductIndex]['quantity'];
                $unitPrice = $this->orderItems[$existingProductIndex]['unit_price'];
                $this->orderItems[$existingProductIndex]['line_total'] = $quantity * $unitPrice;
                
                // Notificação de atualização de quantidade
                $message = __('Quantity updated for product: :product', ['product' => $product->name]);
            } else {
                // Adicionar novo produto à ordem
                $this->orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'description' => $product->description,
                    'quantity' => 1,
                    'unit_price' => $product->price,
                    'unit_of_measure' => $product->unit_of_measure ?? 'und',
                    'line_total' => $product->price
                ];
                
                // Mensagem para produto adicionado
                $message = __('Product added to order: :product', ['product' => $product->name]);
            }
            
            $this->calculateOrderTotal();
            $this->closeProductSelector();
            
            // Show success notification
            $this->dispatch('notify', 
                type: 'success',
                title: __('messages.success'),
                message: $message
            );
            
        } catch (\Exception $e) {
            Log::error('Error adding product to order: ' . $e->getMessage());
            
            $this->dispatch('notify', 
                type: 'error',
                title: __('messages.error'),
                message: __('Failed to add product to order')
            );
        }
    }
    
    public function openProductSelector()
    {
        $this->reset(['productSearch', 'productPage']);
        $this->loadProducts();
        $this->dispatch('openProductSelectorModal');
    }

    public function closeProductSelector()
    {
        // This method is called to close the product selector modal
        // No need to emit an event since we're using Alpine.js to handle the modal state
        // The product selector modal is closed by setting open = false in Alpine.js
    }
    
    public function removeItem($index)
    {
        unset($this->orderItems[$index]);
        $this->orderItems = array_values($this->orderItems);
        $this->calculateOrderTotal();
    }
    
    public function calculateLineTotal($index)
    {
        if (isset($this->orderItems[$index])) {
            $item = $this->orderItems[$index];
            $quantity = floatval($item['quantity'] ?? 0);
            $unitPrice = floatval($item['unit_price'] ?? 0);
            $this->orderItems[$index]['line_total'] = $quantity * $unitPrice;
        }
        $this->calculateOrderTotal();
    }
    
    public function calculateOrderTotal()
    {
        $subtotal = 0;
        foreach ($this->orderItems as $item) {
            $subtotal += floatval($item['line_total'] ?? 0);
        }
        
        $shipping = floatval($this->purchaseOrder['shipping_amount'] ?? 0);
        $this->orderTotal = $subtotal + $shipping;
    }
    
    public function savePurchaseOrder()
    {
        Log::info('Método savePurchaseOrder chamado');
        $this->save();
    }
    
    public function save()
    {
        Log::info('Método save chamado', [
            'editMode' => $this->editMode,
            'purchaseOrder' => $this->purchaseOrder,
            'orderItems' => $this->orderItems
        ]);
        
        try {
            $this->validate([
                'purchaseOrder.supplier_id' => 'required',
                'purchaseOrder.order_date' => 'required|date',
                'purchaseOrder.expected_delivery_date' => 'required|date',
                'purchaseOrder.other_reference' => 'nullable|string|max:255',
            ]);
            
            Log::info('Validação passou');
            
            // Validação dos campos de quantidade e preço unitário para cada item
            foreach ($this->orderItems as $index => $item) {
                if (empty($item['quantity']) || $item['quantity'] <= 0) {
                    $this->dispatch('notify', 
                        type: 'error', 
                        message: __('messages.quantity_required')
                    );
                    return;
                }
                
                if (empty($item['unit_price']) || $item['unit_price'] < 0) {
                    $this->dispatch('notify', 
                        type: 'error', 
                        message: __('messages.unit_price_required')
                    );
                    return;
                }
            }
            
            if (empty($this->orderItems)) {
                Log::warning('Nenhum item na ordem');
                $this->dispatch('notify', type: 'error', message: __('messages.order_must_have_items'));
                return;
            }
            
            DB::beginTransaction();
            Log::info('Iniciou transação no banco de dados');
            
            try {
                if ($this->editMode) {
                    $order = PurchaseOrder::findOrFail($this->purchaseOrder['id']);
                    Log::info('Editando ordem existente', ['order_id' => $order->id]);
                } else {
                    $order = new PurchaseOrder();
                    $order->order_number = $this->purchaseOrder['order_number'];
                    $order->created_by = Auth::id();
                    Log::info('Criando nova ordem', ['order_number' => $order->order_number]);
                }
                
                $order->supplier_id = $this->purchaseOrder['supplier_id'];
                $order->order_date = $this->purchaseOrder['order_date'];
                $order->expected_delivery_date = $this->purchaseOrder['expected_delivery_date'];
                $order->other_reference = $this->purchaseOrder['other_reference'] ?? null;
                $order->shipping_amount = floatval($this->purchaseOrder['shipping_amount'] ?? 0);
                
                // Removendo campos que não existem na tabela
                $order->notes = $this->purchaseOrder['notes'] ?? null;
                
                if (!$this->editMode) {
                    $order->status = 'ordered'; // Alterando para 'ordered' em vez de 'draft'
                } else if (isset($this->purchaseOrder['status'])) {
                    $order->status = $this->purchaseOrder['status'];
                }
                
                Log::info('Dados da ordem preparados', ['order' => $order->toArray()]);
                $order->save();
                Log::info('Ordem salva', ['order_id' => $order->id]);
                
                // Delete existing items if edit mode
                if ($this->editMode) {
                    $order->items()->delete();
                    Log::info('Itens existentes excluídos');
                }
                
                // Save order items
                Log::info('Salvando itens da ordem', ['count' => count($this->orderItems)]);
                foreach ($this->orderItems as $item) {
                    $orderItem = new PurchaseOrderItem();
                    $orderItem->purchase_order_id = $order->id;
                    $orderItem->product_id = $item['product_id'];
                    $orderItem->description = $item['description'];
                    $orderItem->quantity = $item['quantity'];
                    $orderItem->unit_price = $item['unit_price'];
                    $orderItem->unit_of_measure = $item['unit_of_measure'] ?? 'und'; // Definindo um valor padrão
                    $orderItem->line_total = $item['line_total'] ?? ($item['quantity'] * $item['unit_price']);
                    $orderItem->save();
                    Log::info('Item salvo', ['item_id' => $orderItem->id]);
                }
                
                // Criar automaticamente uma ShippingNote para a nova ordem
                if (!$this->editMode) {
                    $this->createShippingNote($order->id, 'order_placed', __('messages.auto_shipping_note_created'));
                    Log::info('Shipping Note criada automaticamente');
                }
                
                DB::commit();
                Log::info('Transação finalizada com sucesso');
                
                $this->dispatch('notify', 
                    type: $this->editMode ? 'warning' : 'success', 
                    message: $this->editMode 
                        ? __('messages.purchase_order_updated') 
                        : __('messages.purchase_order_created')
                );
                
                $this->closeModal();
                $this->dispatch('refreshPurchaseOrders');
                Log::info('Processo de salvamento concluído com sucesso');
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erro ao salvar ordem', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $this->dispatch('notify', type: 'error', message: $e->getMessage());
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Falha na validação', [
                'errors' => $e->errors()
            ]);
            // Livewire já lida com os erros de validação
            throw $e;
        } catch (\Exception $e) {
            Log::error('Erro não tratado', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }
    
    public function confirmDeleteOrder($id)
    {
        $this->deleteOrderId = $id;
        $this->showDeleteModal = true;
    }
    
    public function deleteOrder()
    {
        try {
            $order = PurchaseOrder::findOrFail($this->deleteOrderId);
            
            // Verificar se pode ser excluído - não permitir exclusão se status for 'completed'
            if ($order->status === 'completed') {
                $this->dispatch('notify', 
                    type: 'error', 
                    title: __('messages.error'),
                    message: __('messages.cannot_delete_completed_order')
                );
                $this->closeDeleteModal();
                return;
            }
            
            $order->delete();
            
            $this->dispatch('notify', 
                type: 'success', 
                title: __('messages.success'),
                message: __('messages.purchase_order_deleted')
            );
            
            $this->closeDeleteModal();
        } catch (\Exception $e) {
            $this->dispatch('notify', 
                type: 'error', 
                title: __('messages.error'),
                message: $e->getMessage()
            );
            Log::error('Erro ao excluir ordem de compra', [
                'order_id' => $this->deleteOrderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteOrderId = null;
    }
    
    public function updateOrderStatus($orderId, $status)
    {
        try {
            $order = PurchaseOrder::findOrFail($orderId);
            $order->status = $status;
            $order->save();
            
            // Se o status for 'ordered', criar uma nota de envio automaticamente
            if ($status === 'ordered') {
                $this->createShippingNote($order->id, 'order_placed', __('messages.auto_shipping_note_ordered'));
            }
            
            $this->dispatch('notify', type: 'success', message: __('messages.status_updated'));
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }
    
    public function openShippingNotes($orderId)
    {
        // Em vez de redirecionar para uma rota, definimos o ID e abrimos o modal
        $this->viewingOrderId = $orderId;
        $this->loadShippingNotes();
        $this->showShippingNotesModal = true;
    }

    protected function loadShippingNotes()
    {
        if ($this->viewingOrderId) {
            $this->shippingNotes = ShippingNote::where('purchase_order_id', $this->viewingOrderId)
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get();

            // Log para debug
            Log::info('Shipping notes carregadas', [
                'order_id' => $this->viewingOrderId,
                'count' => $this->shippingNotes->count()
            ]);
        }
    }
    
    protected function createShippingNote($orderId, $status, $note)
    {
        Log::info('Criando Shipping Note automática', [
            'orderId' => $orderId,
            'status' => $status,
            'note' => $note
        ]);
        
        try {
            $shippingNote = new ShippingNote();
            $shippingNote->purchase_order_id = $orderId;
            $shippingNote->status = $status;
            $shippingNote->note = $note;
            $shippingNote->updated_by = Auth::id();
            $saved = $shippingNote->save();
            
            Log::info('Shipping Note automática criada', [
                'success' => $saved,
                'id' => $shippingNote->id ?? 'não gerado'
            ]);
            
            return $shippingNote;
        } catch (\Exception $e) {
            Log::error('Erro ao criar Shipping Note automática', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    public function generatePdf($orderId)
    {
        try {
            $order = PurchaseOrder::with(['supplier', 'items.product', 'createdBy'])->findOrFail($orderId);
            
            $pdf = app('dompdf.wrapper');
            $pdf->loadView('pdf.purchase-order', [
                'order' => $order,
            ]);
            
            $filename = 'purchase_order_' . $order->order_number . '.pdf';
            
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
            $query = PurchaseOrder::query()
                ->with(['supplier', 'createdBy']);
            
            // Filtros
            if ($this->statusFilter) {
                $query->where('status', $this->statusFilter);
            }
            
            if ($this->supplierFilter) {
                $query->where('supplier_id', $this->supplierFilter);
            }
            
            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('order_number', 'like', '%' . $this->search . '%')
                      ->orWhereHas('supplier', function ($sq) {
                          $sq->where('name', 'like', '%' . $this->search . '%');
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
            $orders = $query->limit(100)->get();
            
            // Carregar a view do PDF com os dados
            $pdf = app('dompdf.wrapper');
            $pdf->loadView('pdf.purchase-orders-list', [
                'orders' => $orders,
                'filters' => [
                    'status' => $this->statusFilter,
                    'supplier' => $this->supplierFilter ? Supplier::find($this->supplierFilter)->name : null,
                    'search' => $this->search,
                ],
                'generatedAt' => now()->format('Y-m-d H:i:s'),
            ]);
            
            $filename = 'purchase_orders_list_' . now()->format('Y-m-d') . '.pdf';
            
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
    
    public function closeShippingNotesModal()
    {
        $this->showShippingNotesModal = false;
        $this->viewingOrderId = null;
        $this->resetShippingNote();
    }
    
    public function resetShippingNote()
    {
        $this->shippingNote = [
            'status' => '',
            'note' => '',
            'custom_form_id' => null,
        ];
        $this->shippingAttachment = null;
        $this->selectedCustomForm = null;
        $this->renderCustomForm = false;
        $this->customFormFields = [];
    }
    
    /**
     * Carrega os campos do formulário personalizado selecionado
     */
    public function loadCustomFormFields()
    {
        $this->renderCustomForm = false;
        $this->customFormFields = [];
        $this->formData = [];
        $this->selectedCustomForm = null;
        
        // Verifica se o status selecionado é um formulário personalizado
        if (!empty($this->shippingNote['status']) && strpos($this->shippingNote['status'], 'custom_form_') === 0) {
            // Extrai o ID do formulário do valor do status
            $formId = (int) str_replace('custom_form_', '', $this->shippingNote['status']);
            
            if ($formId > 0) {
                $customForm = \App\Models\SupplyChain\CustomForm::with('fields')->find($formId);
                
                if ($customForm) {
                    $this->selectedCustomForm = $customForm;
                    $this->customFormFields = $customForm->fields->toArray();
                    $this->renderCustomForm = true;
                    $this->shippingNote['custom_form_id'] = $formId;
                    
                    // Verifica se já existem dados salvos para este formulário
                    // Busca nas tabelas de submissão e valores de campos
                    
                    // Primeiro verificamos se há uma shipping note com este formulário personalizado
                    $existingShippingNote = \App\Models\SupplyChain\ShippingNote::where('purchase_order_id', $this->viewingOrderId)
                        ->where('custom_form_id', $formId)
                        ->latest()
                        ->first();
                    
                    if ($existingShippingNote) {
                        // Procuramos por submissões relacionadas a esta nota
                        $submission = \App\Models\SupplyChain\CustomFormSubmission::where('form_id', $formId)
                            ->where('entity_id', $existingShippingNote->id)
                            ->latest()
                            ->with('fieldValues.field')
                            ->first();
                        
                        if ($submission) {
                            // Popula os dados do formulário com os valores encontrados
                            foreach ($submission->fieldValues as $fieldValue) {
                                if ($fieldValue->field) {
                                    $fieldName = $fieldValue->field->name;
                                    $fieldType = $fieldValue->field->type;
                                    $value = $fieldValue->value;
                                    
                                    // Tratamento especial para checkboxes
                                    if ($fieldType === 'checkbox') {
                                        if (!empty($fieldValue->field->options)) {
                                            // Checkbox múltiplo - inicializar como array para wire:model
                                            $checkboxArray = [];
                                            
                                            if (!empty($value) && $value !== '{}' && $value !== '[]') {
                                                if (is_string($value)) {
                                                    $decoded = json_decode($value, true);
                                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                        // Converter apenas valores true para o formato do wire:model
                                                        foreach ($fieldValue->field->options as $option) {
                                                            $optionKey = $option['value'];
                                                            $checkboxArray[$optionKey] = isset($decoded[$optionKey]) && $decoded[$optionKey] === true;
                                                        }
                                                    }
                                                }
                                            } else {
                                                // Inicializar todas as opções como false
                                                foreach ($fieldValue->field->options as $option) {
                                                    $checkboxArray[$option['value']] = false;
                                                }
                                            }
                                            
                                            $this->formData[$fieldName] = $checkboxArray;
                                        } else {
                                            // Checkbox simples - converter string para boolean
                                            $this->formData[$fieldName] = ($value === '1' || $value === true || $value === 'true');
                                        }
                                    } else {
                                        $this->formData[$fieldName] = $value;
                                    }
                                }
                            }
                            // Log para depuração
                            \Illuminate\Support\Facades\Log::info('Carregando dados do formulário personalizado', [
                                'form_id' => $formId,
                                'shipping_note_id' => $existingShippingNote->id,
                                'submission_id' => $submission->id,
                                'data' => $this->formData
                            ]);
                        } else {
                            // Inicializa campos vazios se não encontrar submissão
                            $this->initializeEmptyFormFields();
                        }
                    } else {
                        // Inicializa campos vazios se não encontrar shipping note
                        $this->initializeEmptyFormFields();
                    }
                }
            }
        } else {
            // Não é um formulário personalizado, então limpa os dados relacionados
            $this->shippingNote['custom_form_id'] = null;
        }
    }
    
    /**
     * Inicializa os campos do formulário com valores vazios
     */
    private function initializeEmptyFormFields()
    {
        foreach ($this->customFormFields as $field) {
            // Para campos checkbox, inicializa de acordo com o tipo
            if ($field['type'] === 'checkbox') {
                if (!empty($field['options'])) {
                    // Para checkboxes múltiplos, inicializa array com todas as opções como false
                    $checkboxArray = [];
                    foreach ($field['options'] as $option) {
                        $checkboxArray[$option['value']] = false;
                    }
                    $this->formData[$field['name']] = $checkboxArray;
                } else {
                    // Para checkbox único, inicializa como false
                    $this->formData[$field['name']] = false;
                }
            } else {
                // Para outros tipos, inicializa com valor padrão vazio
                $this->formData[$field['name']] = '';
            }
        }
    }
    
    public function addShippingNote()
    {
        // Validação
        $validationRules = [
            'shippingNote.status' => 'required|string',
        ];
        
        // Adicionar validação para os campos do formulário personalizado se necessário
        if ($this->renderCustomForm && !empty($this->customFormFields)) {
            foreach ($this->customFormFields as $field) {
                if ($field['is_required']) {
                    $validationRules["formData.{$field['name']}"] = 'required';
                }
            }
        }
        
        $this->validate($validationRules, [
            'shippingNote.status.required' => __('messages.shipping_note_status_required'),
        ]);
        
        try {
            DB::beginTransaction();
            
            // Criar a nota de envio
            $note = new ShippingNote();
            $note->purchase_order_id = $this->viewingOrderId;
            
            // Se for um formulário personalizado
            if ($this->renderCustomForm && $this->selectedCustomForm) {
                // Usar um status especial para formulários personalizados
                $note->status = 'custom_form';
                $note->custom_form_id = $this->shippingNote['custom_form_id'];
                
                // Gerar uma nota automática com o nome do formulário
                $note->note = "Formulário: {$this->selectedCustomForm->name}";
                
                // Salvar a shipping note primeiro para obter o ID
                $note->save();
                
                // Criar uma submissão do formulário personalizado
                $submission = new \App\Models\SupplyChain\CustomFormSubmission([
                    'form_id' => $this->selectedCustomForm->id,
                    'entity_id' => $note->id,
                    'created_by' => Auth::id()
                ]);
                $submission->save();
                
                // Salvar os valores dos campos
                foreach ($this->formData as $fieldName => $value) {
                    $field = \App\Models\SupplyChain\CustomFormField::where('form_id', $this->selectedCustomForm->id)
                        ->where('name', $fieldName)
                        ->first();
                    
                    if ($field) {
                        // Verificar se é um campo de arquivo
                        if ($field->type == 'file' && $value) {
                            // Se for um objeto UploadedFile, armazená-lo
                            if (is_object($value) && $value instanceof \Illuminate\Http\UploadedFile) {
                                // Definir o nome do arquivo com timestamp para evitar duplicações
                                $fileName = 'custom_forms/'.time().'_'.$value->getClientOriginalName();
                                
                                // Armazenar o arquivo
                                $path = $value->storeAs('public', $fileName);
                                
                                // Salvar o caminho do arquivo no banco de dados
                                \App\Models\SupplyChain\CustomFormFieldValue::create([
                                    'submission_id' => $submission->id,
                                    'field_id' => $field->id,
                                    'value' => $fileName
                                ]);
                                
                                // Log do upload
                                \Illuminate\Support\Facades\Log::info('Arquivo carregado para formulário personalizado', [
                                    'field' => $fieldName,
                                    'original_name' => $value->getClientOriginalName(),
                                    'stored_path' => $fileName
                                ]);
                            }
                        } else {
                            // Para outros tipos de campo
                            $valueToSave = $value;
                            
                            // Tratamento especial para checkboxes
                            if ($field->type === 'checkbox') {
                                if (!empty($field->options)) {
                                    // Checkbox múltiplo - processar array do wire:model
                                    if (is_array($value)) {
                                        $cleanedValues = [];
                                        foreach ($value as $key => $val) {
                                            // Normalizar valor do checkbox
                                            if ($val === true || $val === 'true' || $val === '1' || $val === 1) {
                                                $cleanedValues[$key] = true;
                                            }
                                        }
                                        $valueToSave = empty($cleanedValues) ? '{}' : json_encode($cleanedValues);
                                    } else {
                                        // Se não for array, manter como está (compatibilidade)
                                        $valueToSave = $value;
                                    }
                                } else {
                                    // Checkbox simples - converter para string booleana
                                    $valueToSave = ($value === true || $value === 'true' || $value === '1' || $value === 1) ? '1' : '0';
                                }
                            }
                            
                            \App\Models\SupplyChain\CustomFormFieldValue::create([
                                'submission_id' => $submission->id,
                                'field_id' => $field->id,
                                'value' => $valueToSave
                            ]);
                            
                            // Log para depuração
                            \Illuminate\Support\Facades\Log::info('Campo salvo (PO):', [
                                'field_name' => $fieldName,
                                'field_type' => $field->type,
                                'original_value' => $value,
                                'saved_value' => $valueToSave
                            ]);
                        }
                    }
                }
                
                // Log para depuração
                \Illuminate\Support\Facades\Log::info('Dados de formulário personalizado salvos', [
                    'shipping_note_id' => $note->id,
                    'form_id' => $this->selectedCustomForm->id,
                    'submission_id' => $submission->id,
                    'data' => $this->formData
                ]);
                
                // Atualizar o status da ordem de compra com o nome do formulário personalizado
                $purchaseOrder = \App\Models\SupplyChain\PurchaseOrder::find($this->viewingOrderId);
                if ($purchaseOrder) {
                    $formName = $this->selectedCustomForm->name;
                    $purchaseOrder->status = $formName;
                    $purchaseOrder->save();
                    
                    // Log da atualização de status
                    \Illuminate\Support\Facades\Log::info('Status da ordem de compra atualizado para nome do formulário', [
                        'order_id' => $purchaseOrder->id,
                        'previous_status' => $purchaseOrder->getOriginal('status'),
                        'new_status' => $formName
                    ]);
                }
            } else {
                // É um status padrão normal
                $note->status = $this->shippingNote['status'];
                
                // Gerar uma nota padrão com o status selecionado
                $statusText = array_key_exists($this->shippingNote['status'], ShippingNote::$statusList) 
                    ? ShippingNote::$statusList[$this->shippingNote['status']] 
                    : $this->shippingNote['status'];
                $note->note = "Status atualizado para: {$statusText}";
                
                // Atualizar status da ordem com base na nota de envio
                $this->updateOrderStatusBasedOnShippingNote($this->viewingOrderId, $this->shippingNote['status']);
            }
            
            // Definir outros campos comuns a todos os tipos de nota
            $note->attachment_url = null; // Campo de anexo não é mais utilizado
            $note->updated_by = Auth::id();
            
            // Para status padrão, salvar agora. Para formulários personalizados, já foi salvo acima
            if (!($this->renderCustomForm && $this->selectedCustomForm)) {
                $note->save();
            }
            
            // Atualizar lista de notas e limpar formulário
            $this->loadShippingNotes();
            $this->resetShippingNote();
            
            DB::commit();
            
            $this->dispatch('notify', type: 'success', message: __('messages.shipping_note_added'));
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error('Erro de validação em ShippingNote', [
                'errors' => $e->errors()
            ]);
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro não tratado em ShippingNote', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }
    
    public function deleteShippingNote($id)
    {
        try {
            $note = ShippingNote::findOrFail($id);
            
            // Verificar se o usuário tem permissão para excluir
            if ($note->purchase_order_id != $this->viewingOrderId) {
                throw new \Exception(__('messages.unauthorized_action'));
            }
            
            // Se houver anexo, excluir o arquivo
            if ($note->attachment_url) {
                try {
                    Storage::disk('public')->delete($note->attachment_url);
                    Log::info('Anexo excluído com sucesso', ['path' => $note->attachment_url]);
                } catch (\Exception $e) {
                    Log::error('Erro ao excluir anexo', [
                        'message' => $e->getMessage(),
                        'path' => $note->attachment_url
                    ]);
                    // Continuar mesmo se não conseguir excluir o arquivo
                }
            }
            
            $note->delete();
            
            $this->dispatch('notify', type: 'success', message: __('messages.shipping_note_deleted'));
            
        } catch (\Exception $e) {
            Log::error('Erro ao excluir shipping note', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }
    
    public function generatePdfList()
    {
        Log::info('=== INÍCIO DO MÉTODO generatePdfList ===');
        
        try {
            // Obter as ordens de compra com os filtros aplicados
            $purchaseOrdersQuery = PurchaseOrder::with(['supplier', 'createdBy', 'items'])
                ->when($this->search, function($query) {
                    return $query->where(function($q) {
                        $q->where('order_number', 'like', '%' . $this->search . '%')
                        ->orWhereHas('supplier', function($subQuery) {
                            $subQuery->where('name', 'like', '%' . $this->search . '%');
                        });
                    });
                })
                ->when($this->statusFilter, function($query) {
                    return $query->where('status', $this->statusFilter);
                })
                ->when($this->supplierFilter, function($query) {
                    return $query->where('supplier_id', $this->supplierFilter);
                })
                ->orderBy($this->sortField, $this->sortDirection);
            
            // Não paginamos aqui para exportar todos os resultados filtrados
            $purchaseOrders = $purchaseOrdersQuery->get();
            
            Log::info('Gerando PDF da listagem de ordens de compra', [
                'quantidade' => $purchaseOrders->count(),
                'filtros' => [
                    'search' => $this->search,
                    'status' => $this->statusFilter,
                    'supplier' => $this->supplierFilter
                ]
            ]);
            
            // Calcular os totais
            $totalOrders = $purchaseOrders->count();
            $totalValue = $purchaseOrders->sum('total_value');
            
            // Obter dados do usuário para incluir no PDF
            $user = Auth::user();
            $currentDate = now()->format('d/m/Y H:i:s');
            
            // Gerar o PDF usando a view
            $pdf = PDF::loadView('pdfs.purchase-orders-list', [
                'purchaseOrders' => $purchaseOrders,
                'totalOrders' => $totalOrders,
                'totalValue' => $totalValue,
                'user' => $user,
                'currentDate' => $currentDate,
                'filters' => [
                    'search' => $this->search,
                    'status' => $this->statusFilter ? __('messages.' . $this->statusFilter) : __('messages.all_statuses'),
                    'supplier' => $this->supplierFilter ? Supplier::find($this->supplierFilter)->name : __('messages.all_suppliers')
                ]
            ]);
            
            // Configurar o PDF
            $pdf->setPaper('a4', 'landscape');
            
            // Gerar um nome de arquivo baseado na data
            $filename = 'purchase_orders_list_' . now()->format('Y-m-d_H-i-s') . '.pdf';
            
            Log::info('PDF da listagem de ordens de compra gerado com sucesso', [
                'filename' => $filename
            ]);
            
            // Mostrar notificação de sucesso
            $this->dispatch('notify', 
                type: 'success', 
                message: __('messages.pdf_generated_successfully')
            );
            
            Log::info('=== FIM DO MÉTODO generatePdfList ===');
            
            // Download do PDF
            return response()->streamDownload(
                fn () => print($pdf->output()),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
            
        } catch (\Exception $e) {
            Log::error('Erro ao gerar PDF da listagem de ordens de compra', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatch('notify', 
                type: 'error', 
                message: __('messages.error_generating_pdf')
            );
            
            Log::info('=== FIM DO MÉTODO generatePdfList (com erro) ===');
        }
    }
    
    protected function updateOrderStatusBasedOnShippingNote($orderId, $shippingStatus)
    {
        Log::info('Iniciando updateOrderStatusBasedOnShippingNote', [
            'orderId' => $orderId,
            'shippingStatus' => $shippingStatus
        ]);
        
        try {
            $order = PurchaseOrder::findOrFail($orderId);
            Log::info('Ordem encontrada', ['orderNumber' => $order->order_number]);
            
            // Lógica para determinar o status da ordem com base no status da ShippingNote
            switch ($shippingStatus) {
                case 'order_placed':
                case 'proforma_invoice_received':
                case 'payment_completed':
                case 'du_in_process':
                case 'goods_acquired':
                case 'shipped_to_port':
                case 'shipping_line_booking_confirmed':
                case 'container_loaded':
                case 'on_board':
                    $order->status = 'ordered';
                    break;
                case 'arrived_at_port':
                case 'customs_clearance':
                    $order->status = 'partially_received';
                    break;
                case 'delivered':
                    $order->status = 'completed';
                    break;
            }
            
            Log::info('Status da ordem atualizado', [
                'oldStatus' => $order->getOriginal('status'),
                'newStatus' => $order->status
            ]);
            
            $saved = $order->save();
            Log::info('Resultado do salvamento da ordem', ['success' => $saved]);
            
            return $saved;
        } catch (\Exception $e) {
            Log::error('Erro em updateOrderStatusBasedOnShippingNote', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
