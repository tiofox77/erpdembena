<?php

namespace App\Livewire\Maintenance;

use App\Models\Maintenance\EquipmentType;
use App\Models\EquipmentPart;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class EquipmentTypes extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'tailwind';
    
    // Propriedades para o formulário
    public $equipmentType = [];
    public $search = '';
    public $showModal = false;
    public $showDeleteModal = false;
    public $editMode = false;
    public $typeToDelete = null;
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;
    
    // Regras de validação
    protected function rules()
    {
        return [
            'equipmentType.name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('equipment_types', 'name')
                    ->ignore(isset($this->equipmentType['id']) ? $this->equipmentType['id'] : null)
            ],
            'equipmentType.description' => 'nullable|string',
            'equipmentType.is_active' => 'boolean',
            'equipmentType.color_code' => 'nullable|string|max:7|regex:/#[0-9A-Fa-f]{6}/'
        ];
    }
    
    protected $messages = [
        'equipmentType.name.required' => 'O nome do tipo de equipamento é obrigatório.',
        'equipmentType.name.unique' => 'Este nome de tipo de equipamento já está em uso.',
        'equipmentType.color_code.regex' => 'O código de cor deve ser um valor hexadecimal válido (ex: #FF5500).'
    ];
    
    public function mount()
    {
        $this->resetEquipmentType();
    }
    
    public function render()
    {
        $types = EquipmentType::query()
            ->when($this->search, function ($query) {
                return $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
            
        return view('livewire.maintenance.equipment-types', [
            'types' => $types
        ]);
    }
    
    public function resetEquipmentType()
    {
        $this->equipmentType = [
            'name' => '',
            'description' => '',
            'is_active' => true,
            'color_code' => '#' . substr(md5(mt_rand()), 0, 6) // Cor aleatória
        ];
        $this->resetValidation();
    }
    
    /**
     * Método para iniciar a criação de um novo tipo de equipamento
     */
    public function create()
    {
        Log::info('Iniciando criação de novo tipo de equipamento');
        
        // Debugar o estado atual
        Log::info('Estado antes do create:', [
            'showModal' => $this->showModal,
            'editMode' => $this->editMode
        ]);
        
        $this->resetEquipmentType();
        $this->editMode = false;
        $this->showModal = true;
        
        // Notificação visual para o usuário
        $this->dispatch('notify', 
            type: 'info', 
            message: 'Debug: Tentativa de abrir modal (showModal = ' . ($this->showModal ? 'true' : 'false') . ')'
        );
        
        Log::info('Modal de criação de tipo de equipamento aberto. Estado final:', [
            'showModal' => $this->showModal,
            'editMode' => $this->editMode
        ]);
    }
    
    /**
     * Método para editar um tipo de equipamento existente
     */
    public function edit($id)
    {
        Log::info('Iniciando edição de tipo de equipamento', ['id' => $id]);
        
        try {
            $this->resetValidation();
            $type = EquipmentType::findOrFail($id);
            $this->equipmentType = $type->toArray();
            $this->editMode = true;
            $this->showModal = true;
            
            Log::info('Tipo de equipamento carregado para edição', [
                'id' => $id,
                'nome' => $type->name
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao editar tipo de equipamento', [
                'id' => $id,
                'erro' => $e->getMessage()
            ]);
            
            $this->dispatch('notify', 
                type: 'error', 
                message: __('messages.equipment_type_not_found')
            );
        }
    }
    
    /**
     * Método para salvar ou atualizar um tipo de equipamento
     */
    public function save()
    {
        Log::info('=== INÍCIO DO MÉTODO SAVE EM EQUIPMENT TYPES ===');
        
        $this->validate();
        
        try {
            if ($this->editMode) {
                Log::info('Atualizando tipo de equipamento', [
                    'id' => $this->equipmentType['id'],
                    'nome' => $this->equipmentType['name']
                ]);
                
                $type = EquipmentType::findOrFail($this->equipmentType['id']);
                $type->update($this->equipmentType);
                
                $this->dispatch('notify', 
                    type: 'warning', 
                    message: __('messages.equipment_type_updated', ['name' => $type->name])
                );
                
                Log::info('Tipo de equipamento atualizado com sucesso', [
                    'id' => $type->id, 
                    'nome' => $type->name
                ]);
            } else {
                Log::info('Criando novo tipo de equipamento', [
                    'nome' => $this->equipmentType['name']
                ]);
                
                $type = EquipmentType::create($this->equipmentType);
                
                $this->dispatch('notify', 
                    type: 'success', 
                    message: __('messages.equipment_type_created', ['name' => $type->name])
                );
                
                Log::info('Tipo de equipamento criado com sucesso', [
                    'id' => $type->id, 
                    'nome' => $type->name
                ]);
            }
            
            // Fechar o modal e resetar as propriedades
            $this->showModal = false;
            $this->resetEquipmentType();
        } catch (\Exception $e) {
            Log::error('Erro ao salvar tipo de equipamento', [
                'erro' => $e->getMessage(),
                'equipmentType' => $this->equipmentType
            ]);
            
            $this->dispatch('notify', 
                type: 'error', 
                message: __('messages.error_saving_equipment_type')
            );
        }
        
        Log::info('=== FIM DO MÉTODO SAVE EM EQUIPMENT TYPES ===');
    }
    
    /**
     * Método para confirmar a exclusão de um tipo de equipamento
     */
    public function confirmDelete($id)
    {
        Log::info('Confirmando exclusão de tipo de equipamento', ['id' => $id]);
        
        try {
            $this->typeToDelete = EquipmentType::findOrFail($id);
            $this->showDeleteModal = true;
            
            Log::info('Modal de confirmação de exclusão aberto');
        } catch (\Exception $e) {
            Log::error('Erro ao confirmar exclusão de tipo de equipamento', [
                'id' => $id,
                'erro' => $e->getMessage()
            ]);
            
            $this->dispatch('notify', 
                type: 'error', 
                message: __('messages.equipment_type_not_found')
            );
        }
    }
    
    /**
     * Método para excluir um tipo de equipamento
     */
    public function delete()
    {
        Log::info('Excluindo tipo de equipamento', ['id' => $this->typeToDelete->id]);
        
        try {
            // Verificar se existem peças associadas a este tipo
            $hasAssociatedParts = EquipmentPart::where('equipment_type_id', $this->typeToDelete->id)->exists();
            
            if ($hasAssociatedParts) {
                $this->dispatch('notify', 
                    type: 'error', 
                    message: __('messages.equipment_type_has_parts', ['name' => $this->typeToDelete->name])
                );
                
                $this->showDeleteModal = false;
                $this->typeToDelete = null;
                return;
            }
            
            $typeName = $this->typeToDelete->name;
            $this->typeToDelete->delete();
            
            $this->dispatch('notify', 
                type: 'success', 
                message: __('messages.equipment_type_deleted', ['name' => $typeName])
            );
            
            Log::info('Tipo de equipamento excluído com sucesso', ['nome' => $typeName]);
            
            $this->showDeleteModal = false;
            $this->typeToDelete = null;
        } catch (\Exception $e) {
            Log::error('Erro ao excluir tipo de equipamento', [
                'erro' => $e->getMessage(),
                'id' => $this->typeToDelete ? $this->typeToDelete->id : null
            ]);
            
            $this->dispatch('notify', 
                type: 'error', 
                message: __('messages.error_deleting_equipment_type')
            );
        }
    }
    
    /**
     * Método para fechar o modal de exclusão
     */
    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->typeToDelete = null;
    }
    
    /**
     * Método para fechar o modal de criação/edição
     */
    public function closeModal()
    {
        $this->showModal = false;
        $this->resetEquipmentType();
    }
    
    /**
     * Método para ordenar a tabela
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
     * Método chamado quando a pesquisa é atualizada
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    /**
     * Método chamado quando o número de itens por página é atualizado
     */
    public function updatingPerPage()
    {
        $this->resetPage();
    }
}