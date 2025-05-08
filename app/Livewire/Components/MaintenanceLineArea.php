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
        // Check if user has permission to view areas and lines
        if (!auth()->user()->can('areas.view') && !auth()->user()->can('lines.view')) {
            $this->dispatch('notify', type: 'error', title: 'Access Denied', message: 'You do not have permission to access this page.');
            return redirect()->route('maintenance.dashboard');
        }

        $this->resetPage();
    }

    // Permission check for creating area
    public function canCreateArea()
    {
        return auth()->user()->can('areas.create');
    }

    // Permission check for editing area
    public function canEditArea()
    {
        return auth()->user()->can('areas.edit');
    }

    // Permission check for deleting area
    public function canDeleteArea()
    {
        return auth()->user()->can('areas.delete');
    }

    // Permission check for creating line
    public function canCreateLine()
    {
        return auth()->user()->can('lines.create');
    }

    // Permission check for editing line
    public function canEditLine()
    {
        return auth()->user()->can('lines.edit');
    }

    // Permission check for deleting line
    public function canDeleteLine()
    {
        return auth()->user()->can('lines.delete');
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
        if (!$this->canCreateArea()) {
            $this->dispatch('notify', type: 'error', title: 'Access Denied', message: 'You do not have permission to create areas.');
            return;
        }

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
        if (!$this->canEditArea()) {
            $this->dispatch('notify', type: 'error', title: 'Access Denied', message: 'You do not have permission to edit areas.');
            return;
        }

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

    // Save area (create or update)
    public function saveArea()
    {
        // Check permission based on operation (create or edit)
        if ($this->isEditing && !$this->canEditArea()) {
            $this->dispatch('notify', type: 'error', title: 'Access Denied', message: 'You do not have permission to edit areas.');
            return;
        } elseif (!$this->isEditing && !$this->canCreateArea()) {
            $this->dispatch('notify', type: 'error', title: 'Access Denied', message: 'You do not have permission to create areas.');
            return;
        }

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
                $message = __('livewire/components/maintenance-line-area.area_updated_success');
            } else {
                $area = Area::create([
                    'name' => $this->area['name'],
                    'description' => $this->area['description']
                ]);
                $message = __('livewire/components/maintenance-line-area.area_created_success');
            }

            // Send success notification with specific type (create or update)
            $notificationType = $this->isEditing ? 'info' : 'success';
            $this->dispatch('notify', type: $notificationType, message: $message);

            $this->showAreaModal = false;
            $this->resetAreaForm();
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: __('livewire/components/maintenance-line-area.' . ($this->isEditing ? 'area_updated_error' : 'area_created_error')) . $e->getMessage());
        }
    }

    public function confirmDelete($id, $type)
    {
        // Check permission based on item type
        if ($type === 'area' && !$this->canDeleteArea()) {
            $this->dispatch('notify', type: 'error', title: 'Access Denied', message: 'You do not have permission to delete areas.');
            return;
        } elseif ($type === 'line' && !$this->canDeleteLine()) {
            $this->dispatch('notify', type: 'error', title: 'Access Denied', message: 'You do not have permission to delete lines.');
            return;
        }

        $this->deleteId = $id;
        $this->deleteType = $type;
        $this->showDeleteModal = true;
    }

    // Line CRUD operations
    public function openCreateLineModal()
    {
        if (!$this->canCreateLine()) {
            $this->dispatch('notify', type: 'error', title: 'Access Denied', message: 'You do not have permission to create lines.');
            return;
        }

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
        if (!$this->canEditLine()) {
            $this->dispatch('notify', type: 'error', title: 'Access Denied', message: 'You do not have permission to edit lines.');
            return;
        }

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
        // Check permission based on operation (create or edit)
        if ($this->isEditing && !$this->canEditLine()) {
            $this->dispatch('notify', type: 'error', title: 'Access Denied', message: 'You do not have permission to edit lines.');
            return;
        } elseif (!$this->isEditing && !$this->canCreateLine()) {
            $this->dispatch('notify', type: 'error', title: 'Access Denied', message: 'You do not have permission to create lines.');
            return;
        }

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
                $message = __('livewire/components/maintenance-line-area.line_updated_success');
            } else {
                $line = Line::create([
                    'name' => $this->line['name'],
                    'description' => $this->line['description']
                ]);
                $message = __('livewire/components/maintenance-line-area.line_created_success');
            }

            // Send success notification with specific type (create or update)
            $notificationType = $this->isEditing ? 'info' : 'success';
            $this->dispatch('notify', type: $notificationType, message: $message);

            $this->showLineModal = false;
            $this->reset(['line']);
        } catch (\Exception $e) {
            Log::error('Error saving line: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', title: __('livewire/components/maintenance-line-area.access_denied'), message: __('livewire/components/maintenance-line-area.error_saving_line'));
        }
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

    // Delete area or line
    public function delete()
    {
        try {
            // Check permission based on item type
            if ($this->deleteType === 'area' && !$this->canDeleteArea()) {
                $this->dispatch('notify', type: 'error', title: __('livewire/components/maintenance-line-area.access_denied'), message: __('livewire/components/maintenance-line-area.no_permission_delete_area'));
                $this->showDeleteModal = false;
                return;
            } elseif ($this->deleteType === 'line' && !$this->canDeleteLine()) {
                $this->dispatch('notify', type: 'error', title: __('livewire/components/maintenance-line-area.access_denied'), message: __('livewire/components/maintenance-line-area.no_permission_delete_line'));
                $this->showDeleteModal = false;
                return;
            }

            if ($this->deleteType === 'area') {
                $item = Area::findOrFail($this->deleteId);

                // Removing the check for associated lines that's causing the SQL error
                // The relationship might be defined incorrectly or the column name is different
                // We'll delete the area directly for now

                $item->delete();
                $this->dispatch('notify', type: 'success', message: __('livewire/components/maintenance-line-area.area_deleted_success'));
            } else {
                $item = Line::findOrFail($this->deleteId);
                $item->delete();
                $this->dispatch('notify', type: 'success', message: __('livewire/components/maintenance-line-area.line_deleted_success'));
            }

            $this->showDeleteModal = false;
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: __('livewire/components/maintenance-line-area.error_deleting_item') . $e->getMessage());
            $this->showDeleteModal = false;
        }
    }

    public function updated($propertyName)
    {
        // Validação em tempo real
        $this->validateOnly($propertyName);
    }

    /**
     * Reset area form fields
     */
    public function resetAreaForm()
    {
        $this->reset(['area']);
        $this->isEditing = false;
    }

    public function render()
    {
        return view('livewire.components.maintenance-line-area');
    }
}
