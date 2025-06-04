<?php

namespace App\Livewire\Maintenance;

use App\Models\FailureCause;
use App\Models\FailureCauseCategory;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

class FailureCauses extends Component
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
    public $cause = [
        'category_id' => '',
        'name' => '',
        'description' => '',
        'is_active' => true
    ];

    // Regras de validação
    protected function rules()
    {
        return [
            'cause.category_id' => 'required|exists:failure_cause_categories,id',
            'cause.name' => 'required|string|max:255',
            'cause.description' => 'nullable|string',
            'cause.is_active' => 'boolean'
        ];
    }

    // Mensagens de validação personalizadas
    protected function messages()
    {
        return [
            'cause.category_id.required' => 'The category is required.',
            'cause.category_id.exists' => 'The selected category does not exist.',
            'cause.name.required' => 'The cause name is required.',
            'cause.name.max' => 'The cause name must not exceed 255 characters.',
        ];
    }

    // Propriedade computada para dados com paginação
    #[Computed]
    public function causes()
    {
        return FailureCause::with('category')
            ->when($this->search, function ($query) {
                return $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%')
                    ->orWhereHas('category', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->orderBy('name')
            ->paginate($this->perPage);
    }

    // Propriedade computada para todas as categorias (para o dropdown)
    #[Computed]
    public function allCategories()
    {
        return FailureCauseCategory::active()->orderBy('name')->get();
    }

    // Método para abrir o modal de criação
    public function openCreateModal()
    {
        $this->isEditing = false;
        $this->reset('cause');
        $this->cause['is_active'] = true;
        $this->showModal = true;
    }

    // Método para abrir o modal de edição
    public function edit($id)
    {
        $this->isEditing = true;
        $cause = FailureCause::findOrFail($id);
        $this->cause = $cause->toArray();
        $this->showModal = true;
    }

    // Método para salvar (criar ou atualizar)
    public function save()
    {
        $validatedData = $this->validate();

        try {
            if ($this->isEditing) {
                // Atualizar registro existente
                $cause = FailureCause::findOrFail($this->cause['id']);
                $cause->update([
                    'category_id' => $this->cause['category_id'],
                    'name' => $this->cause['name'],
                    'description' => $this->cause['description'],
                    'is_active' => $this->cause['is_active']
                ]);
                $message = 'Failure cause updated successfully';
                $type = 'info';
            } else {
                // Criar novo registro
                FailureCause::create([
                    'category_id' => $this->cause['category_id'],
                    'name' => $this->cause['name'],
                    'description' => $this->cause['description'],
                    'is_active' => $this->cause['is_active']
                ]);
                $message = 'Failure cause created successfully';
                $type = 'success';
            }

            // Enviar notificação
            $this->dispatch('notify', type: $type, message: $message);

            // Limpar e fechar o modal
            $this->showModal = false;
            $this->reset('cause');
        } catch (\Exception $e) {
            // Registrar e notificar erros
            Log::error('Error saving failure cause: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Error saving failure cause: ' . $e->getMessage());
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
            $cause = FailureCause::findOrFail($this->deleteId);
            $cause->delete();

            // Notificar sucesso
            $this->dispatch('notify', type: 'warning', message: 'Failure cause deleted successfully');

            // Fechar modal e limpar dados
            $this->showDeleteModal = false;
            $this->reset('deleteId');
        } catch (\Exception $e) {
            Log::error('Error deleting failure cause: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Error deleting failure cause: ' . $e->getMessage());
        }
    }

    // Método para fechar modal
    public function closeModal()
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->reset(['cause', 'deleteId']);
    }

    // Método para validação em tempo real
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    // Renderização do componente
    public function render()
    {
        return view('livewire.maintenance.failure-causes');
    }
}