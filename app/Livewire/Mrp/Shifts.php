<?php

namespace App\Livewire\Mrp;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Mrp\Shift;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class Shifts extends Component
{
    use WithPagination;
    
    // Propriedades de listagem e filtros
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;
    public $statusFilter = '';
    
    // Propriedades dos modais
    public $showModal = false;
    public $showDeleteModal = false;
    public $showViewModal = false;
    public $editMode = false;
    public $shiftId = null;
    public $shiftToDelete = null;
    public $confirmDelete = false;
    
    // Propriedades do objeto shift
    public $shift = [
        'name' => '',
        'start_time' => '',
        'end_time' => '',
        'description' => '',
        'color_code' => '#3B82F6', // Azul padrão
        'is_active' => true,
        'break_start' => null,
        'break_end' => null,
        'working_days' => []
    ];
    
    // Dias da semana para seleção múltipla
    public $weekDays = [];
    
    /**
     * Regras de validação
     */
    protected function rules()
    {
        $shiftId = $this->shift['id'] ?? '';
        
        return [
            'shift.name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('mrp_shifts', 'name')->ignore($shiftId)
            ],
            'shift.start_time' => 'required|string',
            'shift.end_time' => 'required|string',
            'shift.description' => 'nullable|string|max:255',
            'shift.color_code' => 'required|string|max:20',
            'shift.is_active' => 'boolean',
            'shift.break_start' => 'nullable|string',
            'shift.break_end' => 'nullable|string',
            'shift.working_days' => 'array'
        ];
    }
    
    /**
     * Mensagens de validação
     */
    protected function messages()
    {
        return [
            'shift.name.required' => __('messages.shift_name_required'),
            'shift.name.unique' => __('messages.shift_name_unique'),
            'shift.start_time.required' => __('messages.shift_start_time_required'),
            'shift.end_time.required' => __('messages.shift_end_time_required'),
            'shift.color_code.required' => __('messages.shift_color_required'),
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
        // Inicializar dias da semana com as traduções corretas
        $this->weekDays = [
            ['value' => 'monday', 'label' => __('messages.monday')],
            ['value' => 'tuesday', 'label' => __('messages.tuesday')],
            ['value' => 'wednesday', 'label' => __('messages.wednesday')],
            ['value' => 'thursday', 'label' => __('messages.thursday')],
            ['value' => 'friday', 'label' => __('messages.friday')],
            ['value' => 'saturday', 'label' => __('messages.saturday')],
            ['value' => 'sunday', 'label' => __('messages.sunday')]
        ];
        
        $this->resetFilters();
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
        $this->resetPage();
    }
    
    /**
     * Abrir modal para criar novo turno
     */
    public function create()
    {
        $this->resetValidation();
        $this->editMode = false;
        $this->shift = [
            'name' => '',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'description' => '',
            'color_code' => '#3B82F6',
            'is_active' => true,
            'break_start' => '12:00',
            'break_end' => '13:00',
            'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']
        ];
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
     * Abrir modal para editar turno
     */
    public function edit($id)
    {
        $this->resetValidation();
        $this->editMode = true;
        $this->shiftId = $id;
        
        $shift = Shift::findOrFail($id);
        $this->shift = $shift->toArray();
        
        // Converter working_days de string JSON para array PHP se necessário
        if (isset($this->shift['working_days']) && is_string($this->shift['working_days'])) {
            $this->shift['working_days'] = json_decode($this->shift['working_days'], true);
        }
        
        $this->showModal = true;
    }
    
    /**
     * Ver detalhes do turno
     */
    public function view($id)
    {
        $this->shiftId = $id;
        $shift = Shift::findOrFail($id);
        $this->shift = $shift->toArray();
        
        // Converter working_days de string JSON para array PHP se necessário
        if (isset($this->shift['working_days']) && is_string($this->shift['working_days'])) {
            $this->shift['working_days'] = json_decode($this->shift['working_days'], true);
        }
        
        $this->showViewModal = true;
    }
    
    /**
     * Abrir modal de exclusão
     */
    public function openDeleteModal($id)
    {
        $this->shiftToDelete = Shift::findOrFail($id);
        $this->showDeleteModal = true;
    }
    
    /**
     * Fechar modal de exclusão
     */
    public function closeDeleteModal()
    {
        $this->shiftToDelete = null;
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
     * Salvar turno
     */
    public function save()
    {
        $this->validate();
        
        try {
            DB::beginTransaction();
            
            // Converter array para JSON antes de salvar
            if (isset($this->shift['working_days']) && is_array($this->shift['working_days'])) {
                $this->shift['working_days'] = json_encode($this->shift['working_days']);
            }
            
            if ($this->editMode) {
                $shift = Shift::findOrFail($this->shiftId);
                $shift->update($this->shift);
                
                $this->dispatch('notify', 
                    type: 'warning', 
                    title: __('messages.shift_updated_title'),
                    message: __('messages.shift_updated_message')
                );
            } else {
                $this->shift['created_by'] = Auth::id();
                Shift::create($this->shift);
                
                $this->dispatch('notify', 
                    type: 'success', 
                    title: __('messages.shift_created_title'),
                    message: __('messages.shift_created_message')
                );
            }
            
            DB::commit();
            $this->showModal = false;
            $this->reset(['shift']);
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->dispatch('notify', 
                type: 'error', 
                title: __('messages.error'),
                message: __('messages.shift_save_error', ['error' => $e->getMessage()])
            );
            
            return false;
        }
    }
    
    /**
     * Excluir turno
     */
    public function delete()
    {
        if (!$this->confirmDelete) {
            $this->confirmDelete = true;
            return;
        }
        
        try {
            $shift = Shift::findOrFail($this->shiftToDelete->id);
            $shift->delete();
            
            $this->dispatch('notify', 
                type: 'error', 
                title: __('messages.shift_deleted_title'),
                message: __('messages.shift_deleted_message')
            );
            
            $this->closeDeleteModal();
            
            return true;
        } catch (\Exception $e) {
            $this->dispatch('notify', 
                type: 'error', 
                title: __('messages.error'),
                message: __('messages.shift_delete_error', ['error' => $e->getMessage()])
            );
            
            return false;
        }
    }
    
    /**
     * Renderizar o componente
     */
    public function render()
    {
        $query = Shift::query()
            ->when($this->search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($this->statusFilter, function ($query, $status) {
                $query->where('is_active', $status === 'active');
            })
            ->orderBy($this->sortField, $this->sortDirection);
        
        $shifts = $query->paginate($this->perPage);
        
        return view('livewire.mrp.shifts', [
            'shifts' => $shifts
        ])->layout('layouts.livewire', [
            'title' => __('messages.shifts_management')
        ]);
    }
}
