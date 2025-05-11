<?php

namespace App\Livewire\Mrp;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Mrp\PurchasePlan;
use App\Models\Mrp\PurchasePlanHeader;
use App\Models\Mrp\PurchasePlanItem;
use App\Models\SupplyChain\Product;
use App\Models\SupplyChain\Supplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PurchasePlanning extends Component
{
    use WithPagination;
    
    // Propriedades do componente
    public $search = '';
    public $sortField = 'required_date';
    public $sortDirection = 'asc';
    public $perPage = 10;
    
    // Propriedades para modal
    public $showModal = false;
    public $showDeleteModal = false;
    public $showViewModal = false;
    public $showMultiProductModal = false;
    public $editMode = false;
    public $planId = null;
    
    // Propriedades do formulário (modo antigo)
    public $plan = [
        'product_id' => '',
        'supplier_id' => '',
        'required_date' => '',
        'required_quantity' => '',
        'unit_price' => '',
        'total_price' => 0,
        'priority' => 'medium',
        'status' => 'planned',
        'lead_time' => '',
        'notes' => ''
    ];
    
    // Propriedades para o novo modelo multi-produtos
    public $planHeader = [
        'title' => '',
        'supplier_id' => '',
        'planned_date' => '',
        'required_date' => '',
        'priority' => 'medium',
        'status' => 'planned',
        'notes' => '',
        'total_value' => 0
    ];
    
    public $planItems = [];
    
    // Propriedades de filtro
    public $dateFilter = null;
    public $productFilter = null;
    public $supplierFilter = null;
    public $statusFilter = null;
    public $priorityFilter = null;
    
    /**
     * Mount component
     */
    public function mount()
    {
        $this->plan['required_date'] = date('Y-m-d', strtotime('+7 days'));
        $this->planHeader['required_date'] = date('Y-m-d', strtotime('+7 days'));
    }
    
    /**
     * Regras de validação
     */
    protected function rules()
    {
        return [
            'plan.product_id' => 'required|exists:sc_products,id',
            'plan.supplier_id' => 'required|exists:sc_suppliers,id',
            'plan.required_date' => 'required|date|after_or_equal:today',
            'plan.required_quantity' => 'required|numeric|min:0.01',
            'plan.unit_price' => 'required|numeric|min:0',
            'plan.priority' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'plan.status' => ['required', Rule::in(['planned', 'approved', 'ordered', 'cancelled'])],
            'plan.lead_time' => 'nullable|integer|min:0',
            'plan.notes' => 'nullable|string|max:1000',
        ];
    }
    
    /**
     * Resetar paginação quando a busca mudar
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    /**
     * Atualizar preço total quando quantidade ou preço unitário mudar
     */
    public function updatedPlanRequiredQuantity()
    {
        $this->calculateTotalPrice();
    }
    
    /**
     * Atualizar preço total quando preço unitário mudar
     */
    public function updatedPlanUnitPrice()
    {
        $this->calculateTotalPrice();
    }
    
    /**
     * Calcular preço total
     */
    private function calculateTotalPrice()
    {
        if (!empty($this->plan['required_quantity']) && !empty($this->plan['unit_price'])) {
            $this->plan['total_price'] = $this->plan['required_quantity'] * $this->plan['unit_price'];
        } else {
            $this->plan['total_price'] = 0;
        }
    }
    
    /**
     * Ordenar por coluna
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
     * Resetar filtros
     */
    public function resetFilters()
    {
        $this->search = '';
        $this->dateFilter = null;
        $this->productFilter = null;
        $this->supplierFilter = null;
        $this->statusFilter = null;
        $this->priorityFilter = null;
        $this->resetPage();
    }
    
    /**
     * Redirecionamento para a nova funcionalidade (desativada a versão antiga)
     */
    public function create()
    {
        // Redirecionar para a nova funcionalidade de múltiplos produtos
        $this->createMultiProduct();
    }
    
    /**
     * Abrir modal para criar novo plano de compra com múltiplos produtos
     */
    public function createMultiProduct()
    {
        $this->resetValidation();
        $this->reset(['planHeader', 'planItems']);
        $this->planHeader = [
            'title' => 'Plano de Compra - ' . date('d/m/Y', strtotime('+7 days')),
            'planned_date' => date('Y-m-d'),
            'required_date' => date('Y-m-d', strtotime('+7 days')),
            'status' => 'planned',
            'priority' => 'medium',
            'total_value' => 0
        ];
        
        // Iniciar com um item vazio
        $this->addItem();
        
        $this->multiEditMode = false;
        $this->showMultiProductModal = true;
    }
    
    /**
     * Editar plano de compra com múltiplos produtos
     */
    public function editMultiProduct($id)
    {
        $this->resetValidation();
        $this->reset(['planHeader', 'planItems']);
        
        // Carregar o plano de compra com seus itens
        $planHeader = PurchasePlanHeader::with(['items'])->findOrFail($id);
        
        // Preencher dados do cabeçalho
        $this->planHeader = [
            'id' => $planHeader->id,
            'title' => $planHeader->title,
            'supplier_id' => $planHeader->supplier_id,
            'planned_date' => $planHeader->planned_date,
            'required_date' => $planHeader->required_date,
            'status' => $planHeader->status,
            'priority' => $planHeader->priority,
            'notes' => $planHeader->notes,
            'total_value' => $planHeader->total_value
        ];
        
        // Preencher itens do plano
        foreach ($planHeader->items as $item) {
            $this->planItems[] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_of_measure' => $item->unit_of_measure,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
                'notes' => $item->notes
            ];
        }
        
        // Se não houver itens, adicionar um item vazio
        if (empty($this->planItems)) {
            $this->addItem();
        }
        
        $this->multiEditMode = true;
        $this->showMultiProductModal = true;
    }
    
    /**
     * Carregar e abrir modal para editar plano de compra
     */
    public function edit($id)
    {
        $this->resetValidation();
        $this->planId = $id;
        $plan = PurchasePlan::findOrFail($id);
        
        $this->plan = [
            'product_id' => $plan->product_id,
            'supplier_id' => $plan->supplier_id,
            'required_date' => $plan->required_date->format('Y-m-d'),
            'required_quantity' => $plan->required_quantity,
            'unit_price' => $plan->unit_price,
            'total_price' => $plan->total_price,
            'priority' => $plan->priority,
            'status' => $plan->status,
            'lead_time' => $plan->lead_time,
            'notes' => $plan->notes
        ];
        
        $this->editMode = true;
        $this->showModal = true;
    }
    
    /**
     * Visualizar detalhes do plano de compra
     */
    public function view($id)
    {
        $this->planId = $id;
        $this->showViewModal = true;
    }
    
    /**
     * Confirmar exclusão de plano de compra
     */
    public function confirmDelete($id)
    {
        $this->planId = $id;
        $this->showDeleteModal = true;
    }
    
    /**
     * Excluir plano de compra
     */
    public function delete()
    {
        try {
            DB::beginTransaction();
            
            // Buscar o plano na nova tabela de cabeçalhos
            $plan = PurchasePlanHeader::findOrFail($this->planId);
            
            // Só permitir excluir se estiver planejado ou cancelado
            if (!in_array($plan->status, ['planned', 'cancelled'])) {
                $this->dispatch('notify', 
                    type: 'error', 
                    title: __('messages.error'), 
                    message: __('messages.delete_purchase_plan_not_allowed')
                );
                
                $this->showDeleteModal = false;
                return;
            }
            
            // Excluir primeiro os itens relacionados
            PurchasePlanItem::where('purchase_plan_id', $plan->id)->delete();
            
            // Depois excluir o cabeçalho
            $plan->delete();
            
            DB::commit();
            
            $this->dispatch('notify', 
                type: 'success', 
                title: __('messages.success'), 
                message: __('messages.purchase_plan_deleted')
            );
        } catch (\Exception $e) {
            DB::rollback();
            
            $this->dispatch('notify', 
                type: 'error', 
                title: __('messages.error'), 
                message: __('messages.purchase_plan_delete_error') . ': ' . $e->getMessage()
            );
        }
        
        $this->showDeleteModal = false;
        $this->planId = null;
    }
    
    /**
     * Fechar modais
     */
    public function closeModal()
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->showViewModal = false;
        $this->showMultiProductModal = false;
        $this->editMode = false;
        $this->planId = null;
    }
    
    /**
     * Fechar modal de múltiplos produtos
     */
    public function closeMultiProductModal()
    {
        $this->showMultiProductModal = false;
    }
    
    /**
     * Adicionar um item ao plano de compra
     */
    public function addPlanItem()
    {
        $this->planItems[] = [
            'product_id' => '',
            'quantity' => 1,
            'unit_of_measure' => '',
            'unit_price' => 0,
            'total_price' => 0,
            'notes' => ''
        ];
    }
    
    /**
     * Remover um item do plano de compra
     */
    public function removePlanItem($index)
    {
        if (isset($this->planItems[$index])) {
            unset($this->planItems[$index]);
            $this->planItems = array_values($this->planItems);
        }
    }
    
    /**
     * Atualizar o preço total de um item
     */
    public function updateItemTotal($index)
    {
        if (isset($this->planItems[$index])) {
            $quantity = floatval($this->planItems[$index]['quantity'] ?? 0);
            $unitPrice = floatval($this->planItems[$index]['unit_price'] ?? 0);
            $this->planItems[$index]['total_price'] = $quantity * $unitPrice;
        }
    }
    
    /**
     * Atualizar o total do plano
     */
    public function updatePlanTotal()
    {
        $total = 0;
        foreach ($this->planItems as $item) {
            $total += floatval($item['total_price'] ?? 0);
        }
        $this->planHeader['total_value'] = $total;
    }
    
    /**
     * Atualizar status do plano de compra com múltiplos produtos
     */
    public function updateStatus($id, $status)
    {
        try {
            $plan = PurchasePlanHeader::findOrFail($id);
            
            // Verificar transições válidas de status
            $validTransitions = [
                'planned' => ['approved', 'cancelled'],
                'approved' => ['ordered', 'cancelled'],
                'ordered' => ['cancelled'],
                'cancelled' => ['planned']
            ];
            
            if (!in_array($status, $validTransitions[$plan->status] ?? [])) {
                $this->dispatch('notify', 
                    type: 'error', 
                    title: __('messages.error'), 
                    message: __('messages.purchase_plan_status_transition_invalid', ['from' => $plan->status, 'to' => $status])
                );
                return;
            }
            
            $plan->status = $status;
            $plan->updated_by = Auth::id();
            $plan->save();
            
            $this->dispatch('notify', 
                type: 'success', 
                title: __('messages.success'), 
                message: __('messages.purchase_plan_status_updated')
            );
            
            // Fechar modal de visualização se estiver aberto
            if ($this->showViewModal) {
                $this->closeModal();
            }
            
        } catch (\Exception $e) {
            $this->dispatch('notify', 
                type: 'error', 
                title: __('messages.error'), 
                message: __('Falha ao atualizar status: ') . $e->getMessage()
            );
        }
    }
    
    /**
     * Salvar plano de compra (criar ou atualizar)
     */
    public function save()
    {
        $this->validate();
        
        DB::beginTransaction();
        
        try {
            // Certificar que o total seja calculado
            $this->calculateTotalPrice();
            
            if ($this->editMode) {
                $plan = PurchasePlan::findOrFail($this->planId);
                $plan->fill($this->plan);
                $plan->updated_by = Auth::id();
                $plan->save();
                
                $this->dispatch('notify', 
                    type: 'warning', 
                    title: __('messages.success'), 
                    message: __('messages.purchase_plan_updated')
                );
            } else {
                $plan = new PurchasePlan($this->plan);
                $plan->created_by = Auth::id();
                $plan->updated_by = Auth::id();
                $plan->save();
                
                $this->dispatch('notify', 
                    type: 'success', 
                    title: __('messages.success'), 
                    message: __('messages.purchase_plan_created')
                );
            }
            
            DB::commit();
            $this->closeModal();
        } catch (\Exception $e) {
            DB::rollback();
            
            $this->dispatch('notify', 
                type: 'error', 
                title: __('messages.error'), 
                message: __('messages.purchase_plan_save_error') . ': ' . $e->getMessage()
            );
        }
    }
    
    /**
     * Validar e salvar plano de compra com múltiplos produtos
     */
    public function saveMultiProducts()
    {
        // Verificar se é uma edição ou uma criação
        $isEdit = isset($this->planHeader['id']);
        
        // Validar cabeçalho do plano
        $this->validate([
            'planHeader.title' => 'required|string|max:255',
            'planHeader.supplier_id' => 'nullable|exists:sc_suppliers,id',
            'planHeader.required_date' => 'required|date|after_or_equal:today',
            'planHeader.priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'planHeader.status' => ['required', Rule::in(['planned', 'approved', 'ordered', 'cancelled'])],
            'planHeader.notes' => 'nullable|string|max:1000',
        ]);
        
        // Validar itens do plano
        foreach ($this->planItems as $index => $item) {
            $this->validate([
                "planItems.{$index}.product_id" => 'required|exists:sc_products,id',
                "planItems.{$index}.quantity" => 'required|numeric|min:0.001',
                "planItems.{$index}.unit_price" => 'required|numeric|min:0',
                "planItems.{$index}.unit_of_measure" => 'nullable|string|max:50',
                "planItems.{$index}.notes" => 'nullable|string|max:500',
            ], [
                "planItems.{$index}.product_id.required" => 'O produto é obrigatório',
                "planItems.{$index}.quantity.required" => 'A quantidade é obrigatória',
                "planItems.{$index}.quantity.min" => 'A quantidade deve ser maior que zero',
                "planItems.{$index}.unit_price.required" => 'O preço unitário é obrigatório',
            ]);
        }
        
        try {
            DB::beginTransaction();
            
            // Calcular o total do plano
            $this->updatePlanTotal();
            
            if ($isEdit) {
                // Atualizar cabeçalho existente
                $planHeader = PurchasePlanHeader::findOrFail($this->planHeader['id']);
                $planHeader->title = $this->planHeader['title'];
                $planHeader->supplier_id = !empty($this->planHeader['supplier_id']) ? $this->planHeader['supplier_id'] : null;
                $planHeader->planned_date = $this->planHeader['planned_date'] ?? date('Y-m-d');
                $planHeader->required_date = $this->planHeader['required_date'];
                $planHeader->status = $this->planHeader['status'];
                $planHeader->priority = $this->planHeader['priority'];
                $planHeader->notes = $this->planHeader['notes'];
                $planHeader->total_value = $this->planHeader['total_value'];
                $planHeader->updated_by = Auth::id();
                $planHeader->save();
                
                // Excluir todos os itens anteriores para recriar
                PurchasePlanItem::where('purchase_plan_id', $planHeader->id)->delete();
            } else {
                // Criar novo cabeçalho
                $planHeader = new PurchasePlanHeader();
                $planHeader->plan_number = 'PO-' . date('Ymd') . '-' . strtoupper(Str::random(5));
                $planHeader->title = $this->planHeader['title'];
                $planHeader->supplier_id = !empty($this->planHeader['supplier_id']) ? $this->planHeader['supplier_id'] : null;
                $planHeader->planned_date = $this->planHeader['planned_date'] ?? date('Y-m-d');
                $planHeader->required_date = $this->planHeader['required_date'];
                $planHeader->status = $this->planHeader['status'];
                $planHeader->priority = $this->planHeader['priority'];
                $planHeader->notes = $this->planHeader['notes'];
                $planHeader->total_value = $this->planHeader['total_value'];
                $planHeader->created_by = Auth::id();
                $planHeader->updated_by = Auth::id();
                $planHeader->save();
            }
            
            // Salvar os itens do plano
            foreach ($this->planItems as $item) {
                $planItem = new PurchasePlanItem();
                $planItem->purchase_plan_id = $planHeader->id;
                $planItem->product_id = $item['product_id'];
                $planItem->quantity = $item['quantity'];
                $planItem->unit_of_measure = $item['unit_of_measure'];
                $planItem->unit_price = $item['unit_price'];
                $planItem->total_price = $item['quantity'] * $item['unit_price'];
                $planItem->notes = $item['notes'] ?? null;
                $planItem->save();
            }
            
            DB::commit();
            
            $this->dispatch('notify', 
                type: 'success', 
                title: __('messages.success'), 
                message: $isEdit ? __('Plano de compra atualizado com sucesso.') : __('Plano de compra criado com múltiplos produtos.')
            );
            
            $this->closeMultiProductModal();
            
        } catch (\Exception $e) {
            DB::rollback();
            
            $this->dispatch('notify', 
                type: 'error', 
                title: __('messages.error'), 
                message: __('messages.purchase_plan_save_error') . ': ' . $e->getMessage()
            );
        }
    }

    /**
     * Carregar dados para a view
     */
    public function render()
    {
        // Construir a consulta para a listagem com os novos planos de múltiplos produtos
        $query = PurchasePlanHeader::with(['supplier', 'items', 'items.product', 'createdBy', 'updatedBy'])
            ->when($this->search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('plan_number', 'like', "%{$search}%")
                      ->orWhereHas('supplier', function ($sq) use ($search) {
                          $sq->where('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('items.product', function ($sq) use ($search) {
                          $sq->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%");
                      });
                });
            })
            ->when($this->dateFilter, function ($query, $date) {
                $query->whereDate('required_date', $date);
            })
            ->when($this->productFilter, function ($query, $productId) {
                $query->whereHas('items', function ($q) use ($productId) {
                    $q->where('product_id', $productId);
                });
            })
            ->when($this->supplierFilter, function ($query, $supplierId) {
                $query->where('supplier_id', $supplierId);
            })
            ->when($this->statusFilter, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($this->priorityFilter, function ($query, $priority) {
                $query->where('priority', $priority);
            })
            ->orderBy($this->sortField, $this->sortDirection);
        
        $purchasePlans = $query->paginate($this->perPage);
        
        // Carregar dados para selects
        $products = Product::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        
        // Selecionar plano para visualização detalhada
        $selectedPlan = null;
        
        if ($this->planId && $this->showViewModal) {
            $selectedPlan = PurchasePlanHeader::with(['supplier', 'items', 'items.product', 'createdBy', 'updatedBy'])->find($this->planId);
        }
        
        // Definições para os selects
        $priorities = [
            'low' => __('messages.priority_low'),
            'medium' => __('messages.priority_medium'),
            'high' => __('messages.priority_high'),
            'urgent' => __('messages.priority_critical')
        ];
        
        $statuses = [
            'planned' => __('messages.status_planned'),
            'approved' => __('messages.status_approved'),
            'ordered' => __('messages.status_ordered'),
            'cancelled' => __('messages.status_cancelled')
        ];
        
        return view('livewire.mrp.purchase-planning', [
            'purchasePlans' => $purchasePlans,
            'products' => $products,
            'suppliers' => $suppliers,
            'priorities' => $priorities,
            'statuses' => $statuses,
            'selectedPlan' => $selectedPlan
        ])->layout('layouts.livewire', [
            'title' => __('messages.purchase_planning')
        ]);
    }
}
