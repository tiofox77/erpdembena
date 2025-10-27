<?php

namespace App\Livewire;

use App\Models\MaintenanceArea as Area;
use App\Models\MaintenanceEquipment as Equipment;
use App\Models\MaintenanceLine as Line;
use Livewire\Component;
use Livewire\WithPagination;

class MaintenanceEquipmentController extends Component
{
    use WithPagination;

    // Form properties
    public $equipmentId;
    public $name;
    public $serial_number;
    public $line_id;
    public $area_id;
    public $status = 'operational';
    public $purchase_date;
    public $last_maintenance;
    public $next_maintenance;
    public $notes;

    // Modal state (nova propriedade para controlar a visibilidade da modal)
    public $isModalOpen = false;
    public $showViewModal = false;
    public $viewingEquipment = null;

    // Filter and sort properties
    public $search = '';
    public $lineFilter = '';
    public $areaFilter = '';
    public $statusFilter = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;

    // Listen for events
    protected $listeners = ['delete'];

    // Reset pagination when filters change
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingLineFilter()
    {
        $this->resetPage();
    }

    public function updatingAreaFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    // Sort by column
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
    }

    // Reset form fields
    private function resetInputFields()
    {
        $this->reset(['equipmentId', 'name', 'serial_number', 'line_id', 'area_id', 'purchase_date', 'last_maintenance', 'next_maintenance', 'notes']);
        $this->status = 'operational';
    }

    // Open modal for new equipment
    public function openNewEquipmentModal()
    {
        $this->resetInputFields();
        $this->isModalOpen = true;
    }

    // Close modal
    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->showViewModal = false;
        $this->viewingEquipment = null;
        $this->resetInputFields();
    }

    // View equipment details
    public function viewEquipment($id)
    {
        try {
            $this->viewingEquipment = Equipment::with(['line', 'area'])->findOrFail($id);
            $this->showViewModal = true;
        } catch (\Exception $e) {
            $notificationType = 'error';
            $message = 'The equipment you are looking for could not be found.';
            $this->dispatch('notify', type: $notificationType, message: $message);
        }
    }

    // Close view modal
    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewingEquipment = null;
    }

    // Clear all filters
    public function clearFilters()
    {
        $this->search = '';
        $this->lineFilter = '';
        $this->areaFilter = '';
        $this->statusFilter = '';
        $this->perPage = 10;
        $this->resetPage();

        $this->dispatch('filters-cleared');

        $notificationType = 'info';
        $message = 'All search filters have been reset.';
        $this->dispatch('notify', type: $notificationType, message: $message);
    }

    // Load equipment data for editing
    public function edit($id)
    {
        try {
            $equipment = Equipment::findOrFail($id);
            $this->equipmentId = $id;
            $this->name = $equipment->name;
            $this->serial_number = $equipment->serial_number;
            $this->line_id = $equipment->line_id;
            $this->area_id = $equipment->area_id;
            $this->status = $equipment->status;
            $this->purchase_date = $equipment->purchase_date;
            $this->last_maintenance = $equipment->last_maintenance;
            $this->next_maintenance = $equipment->next_maintenance;
            $this->notes = $equipment->notes;

            $this->isModalOpen = true;
        } catch (\Exception $e) {
            $notificationType = 'error';
            $message = 'The equipment you are trying to edit could not be found.';
            $this->dispatch('notify', type: $notificationType, message: $message);
        }
    }

    // Save equipment data
    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'serial_number' => 'required|string|max:100',
            'line_id' => 'nullable|exists:maintenance_lines,id',
            'area_id' => 'nullable|exists:maintenance_areas,id',
            'status' => 'required|in:operational,maintenance,out_of_service',
        ]);

        try {
            $equipment = $this->equipmentId
                ? Equipment::findOrFail($this->equipmentId)
                : new Equipment();

            $equipment->name = $this->name;
            $equipment->serial_number = $this->serial_number;
            $equipment->line_id = $this->line_id;
            $equipment->area_id = $this->area_id;
            $equipment->status = $this->status;
            $equipment->purchase_date = $this->purchase_date;

            // Auto-set maintenance dates
            // TODO: This is a temporary solution. Will be replaced with a proper maintenance scheduling system in the future.
            $today = now()->format('Y-m-d');
            $equipment->last_maintenance = $this->last_maintenance ?? $today; // Default to today if not provided
            $equipment->next_maintenance = $this->next_maintenance ?? now()->addMonths(6)->format('Y-m-d'); // Default to 6 months from now

            $equipment->notes = $this->notes;

            $equipment->save();

            $this->closeModal();

            // Send notification
            $notificationType = 'info';
            $message = $this->equipmentId
                ? "Equipment '{$equipment->name}' has been updated successfully."
                : "Equipment '{$equipment->name}' has been created successfully.";
            $this->dispatch('notify', type: $notificationType, message: $message);

        } catch (\Exception $e) {
            // Send notification
            $notificationType = 'error';
            $message = 'An error occurred while saving the equipment. Please try again.';
            $this->dispatch('notify', type: $notificationType, message: $message);
        }
    }

    // Delete equipment
    public function delete($id)
    {
        try {
            $equipment = Equipment::findOrFail($id);
            $name = $equipment->name;
            $equipment->delete();

            $notificationType = 'success';
            $message = "Equipment '{$name}' has been deleted successfully.";
            $this->dispatch('notify', type: $notificationType, message: $message);
        } catch (\Exception $e) {
            $notificationType = 'error';
            $message = 'An error occurred while deleting the equipment. Please try again.';
            $this->dispatch('notify', type: $notificationType, message: $message);
        }
    }

    // Main render method
    public function render()
    {
        $lines = Line::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();

        $equipment = Equipment::query()
            ->when($this->search, function ($query) {
                return $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('serial_number', 'like', '%' . $this->search . '%')
                      ->orWhere('notes', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->lineFilter, function ($query) {
                return $query->where('line_id', $this->lineFilter);
            })
            ->when($this->areaFilter, function ($query) {
                return $query->where('area_id', $this->areaFilter);
            })
            ->when($this->statusFilter, function ($query) {
                return $query->where('status', $this->statusFilter);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.maintenance-equipment', [
            'equipment' => $equipment,
            'lines' => $lines,
            'areas' => $areas
        ]);
    }
}
