<?php

namespace App\Livewire\Maintenance;

use App\Models\FailureMode;
use App\Models\FailureModeCategory;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

class FailureModes extends Component
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
    public $mode = [
        'category_id' => '',
        'name' => '',
        'description' => '',
        'is_active' => true
    ];

    // Regras de validação
    protected function rules()
    {
        return [
            'mode.category_id' => 'required|exists:failure_mode_categories,id',
            'mode.name' => 'required|string|max:255',
            'mode.description' => 'nullable|string',
            'mode.is_active' => 'boolean'
        ];
    }

    // Mensagens de validação personalizadas
    protected function messages()
    {
        return [
            'mode.category_id.required' => 'The category is required.',
            'mode.category_id.exists' => 'The selected category does not exist.',
            'mode.name.required' => 'The mode name is required.',
            'mode.name.max' => 'The mode name must not exceed 255 characters.',
        ];
    }

    // Propriedade computada para dados com paginação
    #[Computed]
    public function modes()
    {
        return FailureMode::with('category')
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
        return FailureModeCategory::active()->orderBy('name')->get();
    }

    // Método para abrir o modal de criação
    public function openCreateModal()
    {
        $this->isEditing = false;
        $this->reset('mode');
        $this->mode['is_active'] = true;
        $this->showModal = true;
    }

    // Método para abrir o modal de edição
    public function edit($id)
    {
        $this->isEditing = true;
        $mode = FailureMode::findOrFail($id);
        $this->mode = $mode->toArray();
        $this->showModal = true;
    }

    // Método para salvar (criar ou atualizar)
    public function save()
    {
        $validatedData = $this->validate();

        try {
            if ($this->isEditing) {
                // Atualizar registro existente
                $mode = FailureMode::findOrFail($this->mode['id']);
                $mode->update([
                    'category_id' => $this->mode['category_id'],
                    'name' => $this->mode['name'],
                    'description' => $this->mode['description'],
                    'is_active' => $this->mode['is_active']
                ]);
                $message = 'Failure mode updated successfully';
                $type = 'info';
            } else {
                // Criar novo registro
                FailureMode::create([
                    'category_id' => $this->mode['category_id'],
                    'name' => $this->mode['name'],
                    'description' => $this->mode['description'],
                    'is_active' => $this->mode['is_active']
                ]);
                $message = 'Failure mode created successfully';
                $type = 'success';
            }

            // Enviar notificação
            $this->dispatch('notify', type: $type, message: $message);

            // Limpar e fechar o modal
            $this->showModal = false;
            $this->reset('mode');
        } catch (\Exception $e) {
            // Registrar e notificar erros
            Log::error('Error saving failure mode: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Error saving failure mode: ' . $e->getMessage());
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
            $mode = FailureMode::findOrFail($this->deleteId);
            $mode->delete();

            // Notificar sucesso
            $this->dispatch('notify', type: 'warning', message: 'Failure mode deleted successfully');

            // Fechar modal e limpar dados
            $this->showDeleteModal = false;
            $this->reset('deleteId');
        } catch (\Exception $e) {
            Log::error('Error deleting failure mode: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Error deleting failure mode: ' . $e->getMessage());
        }
    }

    // Método para fechar modal
    public function closeModal()
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->reset(['mode', 'deleteId']);
    }

    // Método para validação em tempo real
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    // Renderização do componente
    public function render()
    {
        return view('livewire.maintenance.failure-modes');
    }
}