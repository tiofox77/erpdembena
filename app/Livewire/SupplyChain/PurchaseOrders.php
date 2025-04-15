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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PurchaseOrders extends Component
{
    use WithPagination;
    use WithFileUploads;
    
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
    ];
    public $shippingAttachment = null;
    public $shippingNotes = [];

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
    
    public function render()
    {
        $suppliers = Supplier::orderBy('name')->get();
        
        $purchaseOrdersQuery = PurchaseOrder::with(['supplier', 'createdBy', 'items'])
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
    
    public function updatedProductSearch($value)
    {
        if (strlen($value) >= 2) {
            $this->products = Product::where('name', 'like', "%{$value}%")
                ->orWhere('product_code', 'like', "%{$value}%")
                ->orWhere('description', 'like', "%{$value}%")
                ->get();
        } else {
            $this->products = [];
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
            $this->dispatch('notify', type: 'error', title: __('messages.access_denied'), message: __('messages.no_permission'));
            
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
            'order_date' => $order->order_date,
            'expected_delivery_date' => $order->expected_delivery_date,
            'status' => $order->status,
            'notes' => $order->notes,
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
        $product = Product::findOrFail($productId);
        
        $this->orderItems[] = [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'description' => $product->description,
            'quantity' => 1,
            'unit_price' => $product->price,
            'unit_of_measure' => $product->unit_of_measure ?? 'und', // Adicionando o campo unit_of_measure
            'line_total' => $product->price
        ];
        
        $this->calculateOrderTotal();
        $this->closeProductSelector();
    }
    
    public function openProductSelector()
    {
        $this->productSearch = '';
        $this->products = Product::orderBy('name')->limit(10)->get();
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
        $total = 0;
        foreach ($this->orderItems as $item) {
            $total += floatval($item['line_total'] ?? 0);
        }
        $this->orderTotal = $total;
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
                
                // Removendo campos que não existem na tabela
                // $order->shipping_address = $this->purchaseOrder['shipping_address'] ?? null;
                $order->notes = $this->purchaseOrder['notes'] ?? null;
                // $order->terms_conditions = $this->purchaseOrder['terms_conditions'] ?? null;
                
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
            
            // Verificar se pode ser excluído
            if (!in_array($order->status, ['draft', 'pending_approval', 'ordered'])) {
                $this->dispatch('notify', type: 'error', message: __('messages.cannot_delete_order_in_status', ['status' => $order->status]));
                $this->closeDeleteModal();
                return;
            }
            
            $order->delete();
            
            $this->dispatch('notify', type: 'success', message: __('messages.purchase_order_deleted'));
            
            $this->closeDeleteModal();
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }
    
    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
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
        ];
        $this->shippingAttachment = null;
    }
    
    public function addShippingNote()
    {
        Log::info('Iniciando addShippingNote', [
            'shippingNote' => $this->shippingNote,
            'viewingOrderId' => $this->viewingOrderId,
            'hasAttachment' => !is_null($this->shippingAttachment)
        ]);
        
        try {
            $this->validate([
                'shippingNote.status' => 'required|string',
                'shippingNote.note' => 'required|string|min:5',
                'shippingAttachment' => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png',
            ], [
                'shippingNote.status.required' => __('messages.shipping_note_status_required'),
                'shippingNote.note.required' => __('messages.shipping_note_note_required'),
                'shippingNote.note.min' => __('messages.shipping_note_note_min'),
                'shippingAttachment.max' => __('messages.shipping_note_attachment_max'),
                'shippingAttachment.mimes' => __('messages.shipping_note_attachment_mimes')
            ]);
            
            Log::info('Validação de ShippingNote passou');
            
            DB::beginTransaction();
            Log::info('Iniciou transação para ShippingNote');
            
            $attachmentPath = null;
            if ($this->shippingAttachment) {
                Log::info('Preparando para salvar o anexo', [
                    'originalName' => $this->shippingAttachment->getClientOriginalName(),
                    'size' => $this->shippingAttachment->getSize(),
                    'extension' => $this->shippingAttachment->getClientOriginalExtension()
                ]);
                try {
                    // Obter a ordem de compra para utilizar o OrderNumber no caminho do arquivo
                    $purchaseOrder = PurchaseOrder::findOrFail($this->viewingOrderId);
                    $orderNumber = $purchaseOrder->order_number;
                    
                    // Criar diretório específico para esta ordem
                    $storageDirectory = "shipping-notes/{$orderNumber}";
                    
                    // Obter o nome original do arquivo e anexar timestamp para evitar duplicidades
                    $originalName = $this->shippingAttachment->getClientOriginalName();
                    $extension = $this->shippingAttachment->getClientOriginalExtension();
                    $timestamp = now()->format('YmdHis');
                    $fileName = pathinfo($originalName, PATHINFO_FILENAME) . "_{$timestamp}." . $extension;
                    
                    // Salvar o arquivo na pasta específica da ordem
                    $attachmentPath = $this->shippingAttachment->storeAs($storageDirectory, $fileName, 'public');
                    
                    Log::info('Anexo salvo com sucesso', [
                        'path' => $attachmentPath,
                        'orderNumber' => $orderNumber
                    ]);
                } catch (\Exception $e) {
                    Log::error('Erro ao salvar anexo', [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw new \Exception(__('messages.error_saving_attachment') . ': ' . $e->getMessage());
                }
            }
            
            // Verificar se o modelo ShippingNote existe
            if (!class_exists(\App\Models\SupplyChain\ShippingNote::class)) {
                Log::error('Modelo ShippingNote não encontrado.');
                throw new \Exception(__('messages.shipping_note_model_not_found'));
            }
            
            Log::info('Criando nova ShippingNote', [
                'purchaseOrderId' => $this->viewingOrderId,
                'status' => $this->shippingNote['status'],
                'noteLength' => strlen($this->shippingNote['note'])
            ]);
            
            $shippingNote = new ShippingNote();
            $shippingNote->purchase_order_id = $this->viewingOrderId;
            $shippingNote->status = $this->shippingNote['status'];
            $shippingNote->note = $this->shippingNote['note'];
            $shippingNote->attachment_url = $attachmentPath;
            $shippingNote->updated_by = Auth::id();
            
            try {
                $saved = $shippingNote->save();
                Log::info('Resultado do salvamento da ShippingNote', [
                    'success' => $saved,
                    'shippingNoteId' => $shippingNote->id ?? 'não gerado'
                ]);
                
                if (!$saved) {
                    throw new \Exception(__('messages.error_saving_shipping_note'));
                }
            } catch (\Exception $e) {
                Log::error('Erro ao salvar ShippingNote', [
                    'message' => $e->getMessage(), 
                    'sql' => DB::getQueryLog(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
            
            // Atualizar o status da ordem com base no status da ShippingNote
            try {
                Log::info('Atualizando status da ordem baseado na ShippingNote', [
                    'orderId' => $this->viewingOrderId,
                    'shippingStatus' => $this->shippingNote['status']
                ]);
                
                $this->updateOrderStatusBasedOnShippingNote($this->viewingOrderId, $this->shippingNote['status']);
                Log::info('Status da ordem atualizado com sucesso');
            } catch (\Exception $e) {
                Log::error('Erro ao atualizar status da ordem', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
            
            DB::commit();
            Log::info('Transação de ShippingNote finalizada com sucesso');
            
            $this->resetShippingNote();
            
            $this->dispatch('notify', type: $this->shippingNote['id'] ? 'warning' : 'success', message: __('messages.shipping_note_added'));
            
            Log::info('ShippingNote criada com sucesso');
            
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
