<?php

namespace App\Livewire\Mrp;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Mrp\CapacityPlan;
use App\Models\Mrp\Resource;
use App\Models\Mrp\ResourceType;
use App\Models\MaintenanceArea;
use App\Models\SupplyChain\InventoryLocation as Location;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CapacityPlanning extends Component
{
    use WithPagination;
    
    // Propriedades do componente
    public $search = '';
    public $sortField = 'start_date';
    public $sortDirection = 'asc';
    public $perPage = 10;
    
    // Propriedades para modal
    public $showModal = false;
    public $showDeleteModal = false;
    public $showViewModal = false;
    public $editMode = false;
    public $planId = null;
    
    // Propriedades do formulário
    public $plan = [
        'name' => '',
        'description' => '',
        'resource_id' => '',
        'resource_type_id' => '',
        'department_id' => '',
        'location_id' => '',
        'start_date' => '',
        'end_date' => '',
        'planned_capacity' => 0,
        'actual_capacity' => 0,
        'capacity_uom' => 'hours',
        'status' => 'draft'
    ];
    
    // Propriedades de filtro
    public $statusFilter = null;
    public $resourceTypeFilter = null;
    public $departmentFilter = null;
    public $locationFilter = null;
    public $dateFilter = null;
    
    /**
     * Mount component
     */
    public function mount()
    {
        $this->plan['start_date'] = date('Y-m-d');
        $this->plan['end_date'] = date('Y-m-d', strtotime('+30 days'));
    }
    
    /**
     * Regras de validação
     */
    protected function rules()
    {
        return [
            'plan.name' => 'required|string|max:255',
            'plan.description' => 'nullable|string|max:1000',
            'plan.resource_id' => 'required|exists:resources,id',
            'plan.resource_type_id' => 'required|exists:resource_types,id',
            'plan.department_id' => 'nullable|exists:maintenance_areas,id',
            'plan.location_id' => 'nullable|exists:sc_inventory_locations,id',     
            'plan.start_date' => 'required|date',
            'plan.end_date' => 'required|date|after_or_equal:plan.start_date',
            'plan.planned_capacity' => 'required|numeric|min:0',
            'plan.actual_capacity' => 'nullable|numeric|min:0',
            'plan.capacity_uom' => ['required', Rule::in(['hours', 'units', 'volume', 'weight'])],
            'plan.status' => ['required', Rule::in(['draft', 'active', 'completed', 'cancelled'])],
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
     * Atualização quando o tipo de recurso é selecionado
     */
    public function updatedPlanResourceTypeId()
    {
        $this->plan['resource_id'] = '';
        
        // Se um tipo de recurso for selecionado, atualiza o departamento automaticamente
        if (!empty($this->plan['resource_type_id'])) {
            // Buscar o primeiro recurso do tipo selecionado para obter seus valores padrão
            $resourceSample = Resource::where('resource_type_id', $this->plan['resource_type_id'])
                ->where('active', true)
                ->first();
                
            if ($resourceSample) {
                $this->plan['department_id'] = $resourceSample->department_id;
                $this->plan['location_id'] = $resourceSample->location_id;
            }
        }
    }
    
    /**
     * Calcular capacidade disponível efetiva
     */
    public function calculateEffectiveCapacity()
    {
        if (!empty($this->plan['available_capacity']) && !empty($this->plan['efficiency_factor'])) {
            return round(($this->plan['available_capacity'] * $this->plan['efficiency_factor']) / 100, 2);
        }
        
        return 0;
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
        $this->statusFilter = null;
        $this->resourceTypeFilter = null;
        $this->departmentFilter = null;
        $this->locationFilter = null;
        $this->dateFilter = null;
        $this->resetPage();
    }
    
    /**
     * Abrir modal para criar novo plano de capacidade
     */
    public function create()
    {
        $this->resetValidation();
        $this->reset('plan');
        
        // Inicializar com valores padrão conforme o schema do banco de dados
        $this->plan = [
            'name' => '',
            'description' => '',
            'resource_id' => '',
            'resource_type_id' => '',
            'department_id' => '',
            'location_id' => '',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+30 days')),
            'planned_capacity' => 0,
            'actual_capacity' => 0,
            'capacity_uom' => 'hours',
            'status' => 'draft'
        ];
        
        $this->editMode = false;
        $this->showModal = true;
        
        Log::info('Modal de criação de plano de capacidade aberto com valores iniciais', [
            'initial_values' => $this->plan
        ]);
    }
    
    /**
     * Carregar e abrir modal para editar plano de capacidade
     */
    public function edit($id)
    {
        $this->resetValidation();
        $this->planId = $id;
        $plan = CapacityPlan::findOrFail($id);
        
        $this->plan = [
            'resource_id' => $plan->resource_id,
            'resource_type_id' => $plan->resource_type_id,
            'department_id' => $plan->department_id,
            'location_id' => $plan->location_id,
            'start_date' => $plan->start_date->format('Y-m-d'),
            'end_date' => $plan->end_date->format('Y-m-d'),
            'available_capacity' => $plan->available_capacity,
            'planned_capacity' => $plan->planned_capacity,
            'capacity_uom' => $plan->capacity_uom,
            'efficiency_factor' => $plan->efficiency_factor,
            'status' => $plan->status,
            'notes' => $plan->notes
        ];
        
        $this->editMode = true;
        $this->showModal = true;
    }
    
    /**
     * Visualizar detalhes do plano de capacidade
     */
    public function view($id)
    {
        $this->planId = $id;
        $this->showViewModal = true;
    }
    
    /**
     * Confirmar exclusão de plano de capacidade
     */
    public function confirmDelete($id)
    {
        $this->planId = $id;
        $this->showDeleteModal = true;
    }
    
    /**
     * Excluir plano de capacidade
     */
    public function delete()
    {
        try {
            $plan = CapacityPlan::findOrFail($this->planId);
            
            // Só permitir excluir se estiver em rascunho ou cancelado
            if (!in_array($plan->status, ['draft', 'cancelled'])) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'title' => __('messages.error'),
                    'message' => __('messages.capacity_delete_not_allowed')
                ]);
                
                $this->showDeleteModal = false;
                return;
            }
            
            $plan->delete();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'title' => __('messages.success'),
                'message' => __('messages.capacity_deleted')
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => __('messages.capacity_delete_error') . ': ' . $e->getMessage()
            ]);
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
    }
    
    /**
     * Atualizar status do plano de capacidade
     */
    public function updateStatus($id, $status)
    {
        $plan = CapacityPlan::findOrFail($id);
        
        // Verificar transições válidas de status
        $validTransitions = [
            'draft' => ['confirmed', 'cancelled'],
            'confirmed' => ['in_progress', 'cancelled'],
            'in_progress' => ['completed', 'cancelled'],
            'completed' => [],
            'cancelled' => ['draft']
        ];
        
        if (!in_array($status, $validTransitions[$plan->status] ?? [])) {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => __('messages.capacity_status_transition_invalid', ['from' => $plan->status, 'to' => $status])
            ]);
            return;
        }
        
        $plan->status = $status;
        $plan->updated_by = Auth::id();
        $plan->save();
        
        $this->dispatch('notify', [
            'type' => 'success',
            'title' => __('messages.success'),
            'message' => __('messages.capacity_status_updated')
        ]);
    }
    
    /**
     * Salvar plano de capacidade (criar ou atualizar)
     */
    public function save()
    {
        try {
            // Log para diagnóstico - início da operação de salvar
            Log::info('=== INÍCIO DO MÉTODO save EM CapacityPlanning ===', [
                'editMode' => $this->editMode,
                'planId' => $this->planId,
                'plan_data' => $this->plan
            ]);
            
            // Validar os dados do formulário
            $this->validate();
            Log::info('=== Validação Concluída ===');
            
            DB::beginTransaction();
            
            try {
                if ($this->editMode) {
                    // Editar plano existente
                    Log::info('Editando plano existente: ' . $this->planId);
                    $plan = CapacityPlan::findOrFail($this->planId);
                    $plan->fill($this->plan);
                    $plan->updated_by = Auth::id();
                    $plan->save();
                    Log::info('Plano atualizado com sucesso', ['capacity_plan_id' => $plan->id]);
                    
                    $this->dispatch('notify', [
                        'type' => 'warning',
                        'title' => __('messages.success'),
                        'message' => __('messages.capacity_updated')
                    ]);
                } else {
                    // Criar novo plano
                    Log::info('Criando novo plano de capacidade');
                    $plan = new CapacityPlan($this->plan);
                    $plan->created_by = Auth::id();
                    $plan->updated_by = Auth::id();
                    
                    // Verificar os dados do objeto antes de salvar
                    Log::info('Dados do plano antes de salvar:', [
                        'attributes' => $plan->getAttributes(),
                        'fillable' => $plan->getFillable()
                    ]);
                    
                    $plan->save();
                    Log::info('Novo plano criado com sucesso', ['capacity_plan_id' => $plan->id]);
                    
                    $this->dispatch('notify', [
                        'type' => 'success',
                        'title' => __('messages.success'),
                        'message' => __('messages.capacity_created')
                    ]);
                }
                
                DB::commit();
                Log::info('Transação concluída com sucesso');
                $this->closeModal();
            } catch (\Exception $e) {
                DB::rollback();
                Log::error('Erro ao salvar plano de capacidade', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'plan_data' => $this->plan
                ]);
                
                $this->dispatch('notify', [
                    'type' => 'error',
                    'title' => __('messages.error'),
                    'message' => __('messages.capacity_save_error') . ': ' . $e->getMessage()
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Capturando erros de validação para logar detalhes
            Log::warning('Erro de validação ao salvar plano de capacidade', [
                'errors' => $e->errors(),
                'plan_data' => $this->plan
            ]);
            // Não precisamos fazer nada aqui pois o Livewire já trata os erros de validação
            throw $e;
        } catch (\Exception $e) {
            // Capturando qualquer outro erro no processo
            Log::error('Erro não tratado ao salvar plano de capacidade', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'plan_data' => $this->plan
            ]);
            
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => __('messages.unexpected_error') . ': ' . $e->getMessage()
            ]);
        }
        
        Log::info('=== FIM DO MÉTODO save EM CapacityPlanning ===');
    }
    
    /**
     * Carregar dados para a view
     */
    public function render()
    {
        // Construir a consulta para a listagem (simplificada para evitar modelos inexistentes)
        $query = CapacityPlan::with(['department'])
            ->when($this->search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->whereHas('department', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
                });
            })
            ->when($this->statusFilter, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($this->departmentFilter, function ($query, $departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->when($this->dateFilter, function ($query, $date) {
                $query->whereDate('start_date', '<=', $date)
                      ->whereDate('end_date', '>=', $date);
            })
            ->orderBy($this->sortField, $this->sortDirection);
        
        $capacityPlans = $query->paginate($this->perPage);
        
        // Carregando dados dos modelos
        $resourceTypes = ResourceType::where('active', true)->orderBy('name')->get();
        
        // Carregando todos os recursos, sem filtro por tipo (para permitir seleção direta)
        // Agora busca todos os recursos ativos, independente do tipo selecionado
        $resources = Resource::where('active', true)
            ->orderBy('name')
            ->get();
        
        $departments = MaintenanceArea::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();
        
        // Selecionar plano para visualização detalhada
        $selectedPlan = null;
        
        if ($this->planId && $this->showViewModal) {
            $selectedPlan = CapacityPlan::with(['department', 'createdBy', 'updatedBy'])->find($this->planId);
        }
        
        // Definições para os selects
        $statuses = [
            'draft' => __('messages.status_draft'),
            'confirmed' => __('messages.status_confirmed'),
            'in_progress' => __('messages.status_in_progress'),
            'completed' => __('messages.status_completed'),
            'cancelled' => __('messages.status_cancelled')
        ];
        
        $capacityUnits = [
            'hours' => __('messages.capacity_uom_hours'),
            'units' => __('messages.capacity_uom_units'),
            'volume' => __('messages.capacity_uom_volume'),
            'weight' => __('messages.capacity_uom_weight')
        ];
        
        return view('livewire.mrp.capacity-planning', [
            'capacityPlans' => $capacityPlans,
            'resourceTypes' => $resourceTypes,
            'resources' => $resources,
            'departments' => $departments,
            'locations' => $locations,
            'statuses' => $statuses,
            'capacityUnits' => $capacityUnits,
            'selectedPlan' => $selectedPlan
        ])->layout('layouts.livewire', [
            'title' => __('messages.capacity_planning')
        ]);
    }
}
