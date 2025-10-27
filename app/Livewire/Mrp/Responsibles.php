<?php

namespace App\Livewire\Mrp;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Mrp\Responsible;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Responsibles extends Component
{
    use WithPagination;
    
    public $search = '';
    public $perPage = 10;
    public $sortField = 'name';
    public $sortDirection = 'asc';
    
    // Form properties
    public $showModal = false;
    public $editMode = false;
    public $responsible = [];
    public $responsibleId = null;
    
    // Delete modal
    public $showDeleteModal = false;
    public $deleteResponsibleId = null;
    
    protected $rules = [
        'responsible.name' => 'required|string|max:255',
        'responsible.position' => 'nullable|string|max:255',
        'responsible.department' => 'nullable|string|max:255',
        'responsible.email' => 'nullable|email|max:255',
        'responsible.phone' => 'nullable|string|max:50',
        'responsible.notes' => 'nullable|string',
        'responsible.is_active' => 'boolean',
    ];
    
    public function mount()
    {
        $this->resetForm();
    }
    
    public function render()
    {
        $responsibles = Responsible::query()
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('position', 'like', '%' . $this->search . '%')
                      ->orWhere('department', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
            
        return view('livewire.mrp.responsibles', [
            'responsibles' => $responsibles
        ]);
    }
    
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }
    
    public function openCreateModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }
    
    public function openEditModal($id)
    {
        $this->resetForm();
        $this->responsibleId = $id;
        $this->editMode = true;
        
        $responsible = Responsible::findOrFail($id);
        $this->responsible = $responsible->toArray();
        
        $this->showModal = true;
    }
    
    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }
    
    public function resetForm()
    {
        $this->responsible = [
            'name' => '',
            'position' => '',
            'department' => '',
            'email' => '',
            'phone' => '',
            'notes' => '',
            'is_active' => true,
        ];
        $this->responsibleId = null;
    }
    
    public function save()
    {
        $this->validate();
        
        try {
            if ($this->editMode) {
                $responsible = Responsible::findOrFail($this->responsibleId);
                $responsible->update(array_merge(
                    $this->responsible,
                    ['updated_by' => Auth::id()]
                ));
                
                $this->dispatch('notify', 
                    type: 'success', 
                    title: __('messages.success'),
                    message: __('messages.responsible_updated', ['name' => $responsible->name])
                );
            } else {
                $responsible = Responsible::create(array_merge(
                    $this->responsible,
                    ['created_by' => Auth::id(), 'updated_by' => Auth::id()]
                ));
                
                $this->dispatch('notify', 
                    type: 'success', 
                    title: __('messages.success'),
                    message: __('messages.responsible_created', ['name' => $responsible->name])
                );
            }
            
            $this->closeModal();
        } catch (\Exception $e) {
            $this->dispatch('notify', 
                type: 'error', 
                title: __('messages.error'),
                message: $e->getMessage()
            );
            
            Log::error('Erro ao salvar responsável', [
                'error' => $e->getMessage(),
                'responsible' => $this->responsible
            ]);
        }
    }
    
    public function confirmDeleteResponsible($id)
    {
        $this->deleteResponsibleId = $id;
        $this->showDeleteModal = true;
    }
    
    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteResponsibleId = null;
    }
    
    public function deleteResponsible()
    {
        try {
            $responsible = Responsible::findOrFail($this->deleteResponsibleId);
            $name = $responsible->name;
            
            $responsible->delete();
            
            $this->dispatch('notify', 
                type: 'success', 
                title: __('messages.success'),
                message: __('messages.responsible_deleted', ['name' => $name])
            );
            
            $this->closeDeleteModal();
        } catch (\Exception $e) {
            $this->dispatch('notify', 
                type: 'error', 
                title: __('messages.error'),
                message: $e->getMessage()
            );
            
            Log::error('Erro ao excluir responsável', [
                'id' => $this->deleteResponsibleId,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    public function toggleActive($id)
    {
        try {
            $responsible = Responsible::findOrFail($id);
            $responsible->is_active = !$responsible->is_active;
            $responsible->updated_by = Auth::id();
            $responsible->save();
            
            $statusText = $responsible->is_active ? 'activated' : 'deactivated';
            
            $this->dispatch('notify', 
                type: 'success', 
                title: __('messages.success'),
                message: __('messages.responsible_' . $statusText, ['name' => $responsible->name])
            );
        } catch (\Exception $e) {
            $this->dispatch('notify', 
                type: 'error', 
                title: __('messages.error'),
                message: $e->getMessage()
            );
            
            Log::error('Erro ao alterar status do responsável', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
