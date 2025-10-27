<?php

namespace App\Livewire\Mrp;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Mrp\Line;
use App\Models\Mrp\Shift;
use App\Models\SupplyChain\InventoryLocation as Location;
use App\Models\HR\Department;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class Lines extends Component
{
    use WithPagination;
    
    // Propriedades de listagem e filtros
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;
    public $statusFilter = '';
    public $departmentFilter = '';
    public $locationFilter = '';
    
    // Propriedades dos modais
    public $showModal = false;
    public $showDeleteModal = false;
    public $showViewModal = false;
    public $editMode = false;
    public $lineId = null;
    public $lineToDelete = null;
    public $confirmDelete = false;
    
    // Propriedades do objeto line
    public $line = [
        'name' => '',
        'code' => '',
        'description' => '',
        'capacity_per_hour' => 0,
        'is_active' => true,
        'location_id' => null,
        'department_id' => null,
        'manager_id' => null,
        'notes' => '',
        'shifts' => []
    ];
    
    // Propriedades de dados para selects
    public $availableShifts = [];
    public $selectedShifts = [];
    
    /**
     * Regras de validação
     */
    protected function rules()
    {
        $lineId = $this->line['id'] ?? '';
        
        return [
            'line.name' => 'required|string|max:100',
            'line.code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('mrp_lines', 'code')->ignore($lineId)
            ],
            'line.description' => 'nullable|string',
            'line.capacity_per_hour' => 'required|numeric|min:0',
            'line.is_active' => 'boolean',
            'line.location_id' => 'nullable|exists:sc_inventory_locations,id',
            'line.department_id' => 'nullable|exists:departments,id',
            'line.manager_id' => 'nullable|exists:users,id',
            'line.notes' => 'nullable|string',
            'selectedShifts' => 'array'
        ];
    }
    
    /**
     * Mensagens de validação
     */
    protected function messages()
    {
        return [
            'line.name.required' => __('messages.line_name_required'),
            'line.code.required' => __('messages.line_code_required'),
            'line.code.unique' => __('messages.line_code_unique'),
            'line.capacity_per_hour.required' => __('messages.line_capacity_required'),
            'line.capacity_per_hour.numeric' => __('messages.line_capacity_numeric'),
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
     * Montar o componente
     */
    public function mount()
    {
        $this->resetFilters();
        $this->loadShifts();
    }
    
    /**
     * Carrega os turnos disponíveis
     */
    public function loadShifts()
    {
        $this->availableShifts = Shift::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->toArray();
    }
    
    /**
     * Ordenar por coluna
     */
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }
    
    /**
     * Resetar filtros
     */
    public function resetFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->departmentFilter = '';
        $this->locationFilter = '';
        $this->resetPage();
    }
    
    /**
     * Gerar código único para linha
     */
    public function generateLineCode()
    {
        $prefix = 'L-';
        $randomPart = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ'), 0, 2));
        $numericPart = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        $code = $prefix . $randomPart . $numericPart;
        
        // Verificar se o código já existe
        while (Line::where('code', $code)->exists()) {
            $randomPart = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ'), 0, 2));
            $numericPart = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $code = $prefix . $randomPart . $numericPart;
        }
        
        return $code;
    }
    
    /**
     * Abrir modal para criar nova linha
     */
    public function create()
    {
        $this->resetValidation();
        $this->editMode = false;
        $this->line = [
            'name' => '',
            'code' => $this->generateLineCode(),
            'description' => '',
            'capacity_per_hour' => 0,
            'is_active' => true,
            'location_id' => null,
            'department_id' => null,
            'manager_id' => null,
            'notes' => ''
        ];
        $this->selectedShifts = [];
        $this->showModal = true;
    }
    
    /**
     * Alias para o método create - usado pelos botões na interface
     */
    public function openCreateModal()
    {
        $this->create();
    }
    
    /**
     * Abrir modal para editar linha
     */
    public function edit($id)
    {
        $this->resetValidation();
        $this->editMode = true;
        $this->lineId = $id;
        
        $line = Line::with('shifts')->findOrFail($id);
        $this->line = $line->toArray();
        
        // Preparar os turnos selecionados
        $this->selectedShifts = $line->shifts->pluck('id')->toArray();
        
        $this->showModal = true;
    }
    
    /**
     * Ver detalhes da linha
     */
    public function view($id)
    {
        $this->lineId = $id;
        $line = Line::with(['location', 'department', 'manager', 'shifts', 'createdBy'])->findOrFail($id);
        $this->line = $line->toArray();
        
        $this->showViewModal = true;
    }
    
    /**
     * Abrir modal de exclusão
     */
    public function openDeleteModal($id)
    {
        $this->lineToDelete = Line::findOrFail($id);
        $this->showDeleteModal = true;
    }
    
    /**
     * Fechar modal de exclusão
     */
    public function closeDeleteModal()
    {
        $this->lineToDelete = null;
        $this->showDeleteModal = false;
        $this->confirmDelete = false;
    }
    
    /**
     * Fechar modal principal
     */
    public function closeModal()
    {
        $this->showModal = false;
    }
    
    /**
     * Fechar modal de visualização
     */
    public function closeViewModal()
    {
        $this->showViewModal = false;
    }
    
    /**
     * Salvar linha
     */
    public function save()
    {
        $this->validate();
        
        try {
            DB::beginTransaction();
            
            if ($this->editMode) {
                $line = Line::findOrFail($this->lineId);
                $line->update($this->line);
                
                // Sincronizar os turnos
                $line->shifts()->sync($this->selectedShifts);
                
                $this->dispatch('notify', 
                    type: 'warning', 
                    title: __('messages.line_updated_title'),
                    message: __('messages.line_updated_message')
                );
            } else {
                $this->line['created_by'] = Auth::id();
                $line = Line::create($this->line);
                
                // Sincronizar os turnos
                $line->shifts()->sync($this->selectedShifts);
                
                $this->dispatch('notify', 
                    type: 'success', 
                    title: __('messages.line_created_title'),
                    message: __('messages.line_created_message')
                );
            }
            
            DB::commit();
            $this->showModal = false;
            $this->reset(['line']);
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->dispatch('notify', 
                type: 'error', 
                title: __('messages.error'),
                message: __('messages.line_save_error', ['error' => $e->getMessage()])
            );
            
            return false;
        }
    }
    
    /**
     * Excluir linha
     */
    public function delete()
    {
        if (!$this->confirmDelete) {
            $this->confirmDelete = true;
            return;
        }
        
        try {
            $line = Line::findOrFail($this->lineToDelete->id);
            
            // Desvincular os turnos antes de excluir
            $line->shifts()->detach();
            $line->delete();
            
            $this->dispatch('notify', 
                type: 'error', 
                title: __('messages.line_deleted_title'),
                message: __('messages.line_deleted_message')
            );
            
            $this->closeDeleteModal();
            
            return true;
        } catch (\Exception $e) {
            $this->dispatch('notify', 
                type: 'error', 
                title: __('messages.error'),
                message: __('messages.line_delete_error', ['error' => $e->getMessage()])
            );
            
            return false;
        }
    }
    
    /**
     * Renderizar o componente
     */
    public function render()
    {
        $query = Line::with(['location', 'department', 'manager'])
            ->when($this->search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($this->statusFilter, function ($query, $status) {
                $query->where('is_active', $status === 'active');
            })
            ->when($this->departmentFilter, function ($query, $departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->when($this->locationFilter, function ($query, $locationId) {
                $query->where('location_id', $locationId);
            })
            ->orderBy($this->sortField, $this->sortDirection);
        
        $lines = $query->paginate($this->perPage);
        
        // Carregar dados para selects
        $locations = Location::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $managers = User::orderBy('name')->get();
        
        return view('livewire.mrp.lines', [
            'lines' => $lines,
            'locations' => $locations,
            'departments' => $departments,
            'managers' => $managers
        ])->layout('layouts.livewire', [
            'title' => __('messages.lines_management')
        ]);
    }
}
