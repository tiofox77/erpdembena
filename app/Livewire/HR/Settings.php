<?php

declare(strict_types=1);

namespace App\Livewire\HR;

use App\Models\HR\HRSetting;
use Livewire\Component;
use Livewire\WithPagination;

class Settings extends Component
{
    use WithPagination;
    
    // Propriedades do formulário
    public ?int $setting_id = null;
    public string $key = '';
    public string $value = '';
    public string $group = 'general';
    public ?string $description = null;
    
    // Filtros e paginação
    public string $activeGroup = 'all';
    public string $search = '';
    public int $perPage = 10;
    
    // Controlo de modais
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public bool $isEditing = false;
    public bool $isSystemSetting = false;
    
    // Lifecycle hooks
    public function hydrate(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
    }
    
    // Listeners
    protected $listeners = ['refreshSettings' => '$refresh'];
    
    // Regras de validação
    protected function rules(): array
    {
        $rules = [
            'key' => 'required|string|min:3|max:255',
            'value' => 'required|string',
            'group' => 'required|string|in:' . implode(',', array_keys(HRSetting::getGroups())),
            'description' => 'nullable|string|max:500',
        ];
        
        if (!$this->isEditing) {
            $rules['key'] .= '|unique:hr_settings,key';
        } else {
            $rules['key'] .= '|unique:hr_settings,key,' . $this->setting_id;
        }
        
        return $rules;
    }
    
    /**
     * Filtro por grupo
     */
    public function filterByGroup(string $group): void
    {
        $this->activeGroup = $group;
        $this->resetPage();
    }
    
    /**
     * Atualização da pesquisa
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }
    
    /**
     * Criar nova configuração
     */
    public function create(): void
    {
        $this->reset(['setting_id', 'key', 'value', 'description']);
        $this->group = 'general';
        $this->isEditing = false;
        $this->isSystemSetting = false;
        $this->showModal = true;
    }
    
    /**
     * Editar configuração existente
     */
    public function edit(HRSetting $setting): void
    {
        $this->setting_id = $setting->id;
        $this->key = $setting->key;
        $this->value = $setting->value ?? '';
        $this->group = $setting->group;
        $this->description = $setting->description ?? '';
        $this->isSystemSetting = $setting->is_system;
        
        $this->isEditing = true;
        $this->showModal = true;
    }
    
    /**
     * Confirmar exclusão
     */
    public function confirmDelete(HRSetting $setting): void
    {
        // Não permitir exclusão de configurações do sistema
        if ($setting->is_system) {
            $this->dispatch('notify', 
                type: 'error',
                title: __('messages.error'),
                message: __('messages.cannot_delete_system_setting')
            );
            return;
        }
        
        $this->setting_id = $setting->id;
        $this->key = $setting->key;
        $this->showDeleteModal = true;
    }
    
    /**
     * Salvar configuração
     */
    public function save(): void
    {
        $validatedData = $this->validate();
        
        if ($this->isEditing) {
            $setting = HRSetting::find($this->setting_id);
            
            if ($setting && $setting->is_system) {
                // Para configurações do sistema, só permitir alterar o valor
                $setting->update([
                    'value' => $validatedData['value']
                ]);
            } elseif ($setting) {
                // Para configurações normais, permitir alterar tudo
                $setting->update($validatedData);
            }
            
            $this->dispatch('notify', 
                type: 'warning',
                title: __('messages.success'),
                message: __('messages.hr_setting_updated')
            );
        } else {
            // Criar nova configuração
            HRSetting::create($validatedData);
            
            $this->dispatch('notify', 
                type: 'success',
                title: __('messages.success'),
                message: __('messages.hr_setting_created')
            );
        }
        
        $this->showModal = false;
        $this->reset(['setting_id', 'key', 'value', 'description']);
        $this->group = 'general';
    }
    
    /**
     * Excluir configuração
     */
    public function delete(): void
    {
        $setting = HRSetting::find($this->setting_id);
        
        if ($setting) {
            if ($setting->is_system) {
                $this->dispatch('notify', 
                    type: 'error',
                    title: __('messages.error'),
                    message: __('messages.cannot_delete_system_setting')
                );
                $this->showDeleteModal = false;
                return;
            }
            
            $setting->delete();
            $this->dispatch('notify', 
                type: 'error',
                title: __('messages.success'),
                message: __('messages.hr_setting_deleted')
            );
        }
        
        $this->showDeleteModal = false;
    }
    
    /**
     * Fechar modal
     */
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
    }
    
    /**
     * Fechar modal de exclusão
     */
    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
    }
    
    /**
     * Reiniciar filtros
     */
    public function resetFilters(): void
    {
        $this->search = '';
        $this->activeGroup = 'all';
        $this->resetPage();
    }
    
    /**
     * Renderizar view
     */
    public function render()
    {
        $query = HRSetting::query();
        
        if ($this->activeGroup !== 'all') {
            $query->where('group', $this->activeGroup);
        }
        
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('key', 'like', "%{$this->search}%")
                  ->orWhere('value', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%");
            });
        }
        
        $settings = $query->paginate($this->perPage);
        $groups = HRSetting::getGroups();
        
        return view('livewire.hr.settings', [
            'settings' => $settings,
            'groups' => $groups
        ]);
    }
}
