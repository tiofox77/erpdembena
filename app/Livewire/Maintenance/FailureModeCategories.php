<?php

namespace App\Livewire\Maintenance;

use App\Models\FailureModeCategory;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

class FailureModeCategories extends Component
{
    use WithPagination;

    // Propriedades com suporte a URL
    #[Url]
    public $search = '';

    // Propriedades de estado
    public $perPage = 10;
    public $showModal = false;
    public $showDeleteModal = false;
    public $isEditing = false;
    public $deleteId = null;

    // Dados do formulário
    public $category = [
        'name' => '',
        'description' => '',
        'is_active' => true
    ];

    // Regras de validação
    protected function rules()
    {
        return [
            'category.name' => 'required|string|max:255',
            'category.description' => 'nullable|string',
            'category.is_active' => 'boolean'
        ];
    }

    // Mensagens de validação personalizadas
    protected function messages()
    {
        return [
            'category.name.required' => 'The category name is required.',
            'category.name.max' => 'The category name must not exceed 255 characters.',
        ];
    }

    // Propriedade computada para dados com paginação
    #[Computed]
    public function categories()
    {
        return FailureModeCategory::when($this->search, function ($query) {
                return $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate($this->perPage);
    }

    // Método para abrir o modal de criação
    public function openCreateModal()
    {
        $this->isEditing = false;
        $this->reset('category');
        $this->category['is_active'] = true;
        $this->showModal = true;
    }

    // Método para abrir o modal de edição
    public function edit($id)
    {
        $this->isEditing = true;
        $category = FailureModeCategory::findOrFail($id);
        $this->category = $category->toArray();
        $this->showModal = true;
    }

    // Método para salvar (criar ou atualizar)
    public function save()
    {
        $validatedData = $this->validate();

        try {
            if ($this->isEditing) {
                // Atualizar registro existente
                $category = FailureModeCategory::findOrFail($this->category['id']);
                $category->update([
                    'name' => $this->category['name'],
                    'description' => $this->category['description'],
                    'is_active' => $this->category['is_active']
                ]);
                $message = 'Failure mode category updated successfully';
                $type = 'info';
            } else {
                // Criar novo registro
                FailureModeCategory::create([
                    'name' => $this->category['name'],
                    'description' => $this->category['description'],
                    'is_active' => $this->category['is_active']
                ]);
                $message = 'Failure mode category created successfully';
                $type = 'success';
            }

            // Enviar notificação
            $this->dispatch('notify', type: $type, message: $message);

            // Limpar e fechar o modal
            $this->showModal = false;
            $this->reset('category');
        } catch (\Exception $e) {
            // Registrar e notificar erros
            Log::error('Error saving failure mode category: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Error saving failure mode category: ' . $e->getMessage());
        }
    }

    // Método para confirmar a exclusão
    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    // Método para deletar após confirmação
    public function deleteConfirmed()
    {
        try {
            $category = FailureModeCategory::findOrFail($this->deleteId);
            $category->delete();

            // Notificar sucesso
            $this->dispatch('notify', type: 'warning', message: 'Failure mode category deleted successfully');

            // Fechar modal e limpar dados
            $this->showDeleteModal = false;
            $this->reset('deleteId');
        } catch (\Exception $e) {
            Log::error('Error deleting failure mode category: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Error deleting failure mode category: ' . $e->getMessage());
        }
    }

    // Método para fechar modal
    public function closeModal()
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->reset(['category', 'deleteId']);
    }

    // Método para validação em tempo real
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    // Renderização do componente
    public function render()
    {
        return view('livewire.maintenance.failure-mode-categories');
    }
}