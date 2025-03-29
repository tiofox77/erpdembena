<?php

namespace App\Livewire\Components;

use App\Models\MaintenanceLine as Line;
use App\Models\MaintenanceArea as Area;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

class MaintenanceLineArea extends Component
{
    use WithPagination;

    #[Url]
    public $activeTab = 'areas';

    #[Url(history: true)]
    public $search = '';

    public $perPage = 10;

    public $showAreaModal = false;
    public $showLineModal = false;
    public $showDeleteModal = false;

    public $isEditing = false;
    public $deleteType = '';
    public $deleteId = null;

    public $area = [
        'name' => '',
        'description' => ''
    ];

    public $line = [
        'name' => '',
        'description' => ''
    ];

    protected $listeners = [
        'refreshComponent' => '$refresh'
    ];

    protected function rules()
    {
        return [
            'area.name' => 'required|string|max:255',
            'area.description' => 'nullable|string',
            'line.name' => 'required|string|max:255',
            'line.description' => 'nullable|string',
        ];
    }

    protected function messages()
    {
        return [
            'area.name.required' => 'The area name is required.',
            'area.name.max' => 'The area name must not exceed 255 characters.',
            'line.name.required' => 'The line name is required.',
            'line.name.max' => 'The line name must not exceed 255 characters.',
        ];
    }

    public function mount()
    {
        $this->resetPage();
    }

    public function updatedActiveTab()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function getAreasProperty()
    {
        return Area::when($this->search, function ($query) {
                return $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate($this->perPage);
    }

    public function getLinesProperty()
    {
        return Line::when($this->search, function ($query) {
                return $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate($this->perPage);
    }

    public function getAllAreasProperty()
    {
        return Area::orderBy('name')->get();
    }

    // Area CRUD operations
    public function openCreateAreaModal()
    {
        $this->resetValidation();
        $this->isEditing = false;
        $this->area = [
            'name' => '',
            'description' => ''
        ];
        $this->showAreaModal = true;
    }

    public function editArea($id)
    {
        $this->resetValidation();
        $this->isEditing = true;
        $area = Area::findOrFail($id);
        $this->area = [
            'id' => $area->id,
            'name' => $area->name,
            'description' => $area->description
        ];
        $this->showAreaModal = true;
    }

    public function saveArea()
    {
        $validatedData = $this->validate([
            'area.name' => 'required|string|max:255',
            'area.description' => 'nullable|string'
        ]);

        try {
            if ($this->isEditing) {
                $area = Area::findOrFail($this->area['id']);
                $area->update([
                    'name' => $this->area['name'],
                    'description' => $this->area['description']
                ]);
                $message = 'Area updated successfully';
            } else {
                Area::create([
                    'name' => $this->area['name'],
                    'description' => $this->area['description']
                ]);
                $message = 'Area created successfully';
            }

            // Enviar notificação de sucesso com tipo específico (create ou update)
            $notificationType = $this->isEditing ? 'info' : 'success';
            $this->dispatch('notify', type: $notificationType, message: $message);

            $this->showAreaModal = false;
            $this->reset(['area']);
        } catch (\Exception $e) {
            Log::error('Error saving area: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Error saving area: ' . $e->getMessage());
        }
    }

    public function confirmDeleteArea($id)
    {
        $this->deleteType = 'area';
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    // Line CRUD operations
    public function openCreateLineModal()
    {
        $this->resetValidation();
        $this->isEditing = false;
        $this->line = [
            'name' => '',
            'description' => ''
        ];
        $this->showLineModal = true;
    }

    public function editLine($id)
    {
        $this->resetValidation();
        $this->isEditing = true;
        $line = Line::findOrFail($id);
        $this->line = [
            'id' => $line->id,
            'name' => $line->name,
            'description' => $line->description
        ];
        $this->showLineModal = true;
    }

    public function saveLine()
    {
        $validatedData = $this->validate([
            'line.name' => 'required|string|max:255',
            'line.description' => 'nullable|string'
        ]);

        try {
            if ($this->isEditing) {
                $line = Line::findOrFail($this->line['id']);
                $line->update([
                    'name' => $this->line['name'],
                    'description' => $this->line['description']
                ]);
                $message = 'Line updated successfully';

            } else {
                Line::create([
                    'name' => $this->line['name'],
                    'description' => $this->line['description']
                ]);
                $message = 'Line created successfully';
            }
            // Enviar notificação de sucesso com tipo específico (create ou update)
            $notificationType = $this->isEditing ? 'info' : 'success';
            $this->dispatch('notify', type: $notificationType, message: $message);

            $this->showLineModal = false;
            $this->reset(['line']);
        } catch (\Exception $e) {
            Log::error('Error saving line: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Error saving line: ' . $e->getMessage());
        }
    }

    public function confirmDeleteLine($id)
    {
        $this->deleteType = 'line';
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    // Common modal actions
    public function closeModal()
    {
        $this->showAreaModal = false;
        $this->showLineModal = false;
        $this->showDeleteModal = false;
        $this->reset(['area', 'line', 'deleteId', 'deleteType']);
        $this->resetValidation();
    }

    public function deleteConfirmed()
    {
        try {
            if ($this->deleteType === 'area') {
                $area = Area::findOrFail($this->deleteId);
                $area->delete();
                $message = 'Area deleted successfully';
            } else if ($this->deleteType === 'line') {
                $line = Line::findOrFail($this->deleteId);
                $line->delete();
                $message = 'Line deleted successfully';
            }

            // Usar warning para notificações de exclusão
            $this->dispatch('notify', type: 'warning', message: $message);

            $this->showDeleteModal = false;
            $this->reset(['deleteId', 'deleteType']);
        } catch (\Exception $e) {
            Log::error('Error deleting ' . $this->deleteType . ': ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Error deleting ' . $this->deleteType . ': ' . $e->getMessage());
        }
    }

    public function updated($propertyName)
    {
        // Validação em tempo real
        $this->validateOnly($propertyName);
    }

    public function render()
    {
        return view('livewire.components.maintenance-line-area');
    }
}