<?php

namespace App\Livewire;

use App\Models\MaintenanceTask;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Log;

class MaintenanceTaskComponent extends Component
{
    use WithPagination;

    // Propriedades para o formulário
    public $showModal = false;
    public $taskId = null;

    #[Validate('required|min:3')]
    public $title = '';

    #[Validate('nullable')]
    public $description = '';

    // Propriedades para o modal de visualização
    public $showViewModal = false;
    public $viewingTask = null;

    // Propriedades para listagem/filtro
    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;

    // Livewire Event Listeners
    protected function getListeners()
    {
        return [
            'escape-pressed' => 'closeAllModals'
        ];
    }

    // Métodos para controle da modal
    public function openModal()
    {
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    // Método para fechar todos os modais
    public function closeAllModals()
    {
        $this->closeModal();
        $this->closeViewModal();
    }

    // Métodos para o modal de visualização
    public function viewTask($id)
    {
        $this->viewingTask = MaintenanceTask::findOrFail($id);
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewingTask = null;
    }

    public function createTask()
    {
        $this->resetForm();
        $this->openModal();
    }

    public function editTask($id)
    {
        // Fechar o modal de visualização se estiver aberto
        if ($this->showViewModal) {
            $this->closeViewModal();
        }

        $task = MaintenanceTask::findOrFail($id);
        $this->taskId = $task->id;
        $this->title = $task->title;
        $this->description = $task->description;
        $this->openModal();
    }

    public function resetForm()
    {
        $this->reset(['taskId', 'title', 'description']);
        $this->resetValidation();
    }

    // Método para limpar os filtros
    public function clearFilters()
    {
        $this->reset(['search']);
        $this->resetPage();

        // Dispatch an event to force UI refresh
        $this->dispatch('filters-cleared');
    }

    // Método para salvar os dados
    public function save()
    {
        $this->validate();

        try {
            if ($this->taskId) {
                $task = MaintenanceTask::findOrFail($this->taskId);
                $task->update([
                    'title' => $this->title,
                    'description' => $this->description
                ]);
                $message = 'Task updated successfully.';
            } else {
                MaintenanceTask::create([
                    'title' => $this->title,
                    'description' => $this->description
                ]);
                $message = 'Task created successfully.';
            }

            $this->closeModal();
            $this->dispatch('notify', type: 'success', message: $message);
        } catch (\Exception $e) {
            Log::error('Task save error: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'An error occurred while saving the task.');
        }
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

    public function delete($id)
    {
        try {
            $task = MaintenanceTask::findOrFail($id);
            $task->delete();

            $this->dispatch('notify', type: 'success', message: 'Task deleted successfully.');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'An error occurred while deleting the task.');
        }
    }

    public function render()
    {
        $tasks = MaintenanceTask::query()
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.maintenance-task', [
            'tasks' => $tasks
        ]);
    }
}
