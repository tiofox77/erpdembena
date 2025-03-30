<?php

namespace App\Livewire;

use App\Models\EquipmentPart;
use App\Models\MaintenanceEquipment;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EquipmentParts extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public $search = '';

    #[Url]
    public $equipmentId = null;

    public $perPage = 10;
    public $showModal = false;
    public $showDeleteModal = false;
    public $showViewModal = false;
    public $viewingPart = null;
    public $isEditing = false;
    public $deleteId = null;
    public $sortField = 'name';
    public $sortDirection = 'asc';

    // Form data
    public $part = [
        'name' => '',
        'part_number' => '',
        'description' => '',
        'stock_quantity' => 0,
        'unit_cost' => null,
        'minimum_stock_level' => 1,
        'maintenance_equipment_id' => null
    ];

    /**
     * Validation rules
     */
    protected function rules()
    {
        return [
            'part.name' => 'required|string|max:255',
            'part.part_number' => 'nullable|string|max:255',
            'part.description' => 'nullable|string',
            'part.stock_quantity' => 'required|integer|min:0',
            'part.unit_cost' => 'nullable|numeric|min:0',
            'part.minimum_stock_level' => 'required|integer|min:0',
            'part.maintenance_equipment_id' => 'required|exists:maintenance_equipment,id',
        ];
    }

    /**
     * Custom validation messages
     */
    protected function messages()
    {
        return [
            'part.name.required' => 'The part name is required.',
            'part.stock_quantity.required' => 'The stock quantity is required.',
            'part.stock_quantity.min' => 'The stock quantity cannot be negative.',
            'part.minimum_stock_level.required' => 'The minimum stock level is required.',
            'part.maintenance_equipment_id.required' => 'Please select an equipment.',
        ];
    }

    /**
     * Lifecycle hook that runs once on component initialization
     */
    public function mount($equipmentId = null)
    {
        if ($equipmentId) {
            $this->equipmentId = $equipmentId;
            $this->part['maintenance_equipment_id'] = $equipmentId;
        }
        $this->sortField = 'name';
        $this->sortDirection = 'asc';
    }

    /**
     * Get all equipment for dropdown selection
     */
    #[Computed]
    public function getEquipmentListProperty()
    {
        return MaintenanceEquipment::orderBy('name')->get();
    }

    /**
     * Get parts based on search and equipment filter
     */
    #[Computed]
    public function getPartsProperty()
    {
        return EquipmentPart::when($this->search, function ($query) {
                return $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('part_number', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->equipmentId, function ($query) {
                return $query->where('maintenance_equipment_id', $this->equipmentId);
            })
            ->when($this->sortField, function ($query) {
                $query->orderBy($this->sortField, $this->sortDirection);
            })
            ->with('equipment')
            ->paginate($this->perPage);
    }

    /**
     * Open the modal for creating a new part
     */
    public function openCreateModal()
    {
        $this->reset('part');

        if ($this->equipmentId) {
            $this->part['maintenance_equipment_id'] = $this->equipmentId;
        }

        $this->isEditing = false;
        $this->showModal = true;
    }

    /**
     * Open the modal for editing a part
     */
    public function editPart($id)
    {
        try {
            $part = EquipmentPart::findOrFail($id);
            $this->part = $part->toArray();
            $this->isEditing = true;
            $this->showModal = true;
        } catch (\Exception $e) {
            Log::error('Error editing part: ' . $e->getMessage());
            $notificationType = 'error';
            $message = 'Part not found.';
            $this->dispatch('notify', type: $notificationType, message: $message);
        }
    }

    /**
     * Save the part (create or update)
     */
    public function savePart()
    {
        $this->validate();

        try {
            if ($this->isEditing) {
                $part = EquipmentPart::findOrFail($this->part['id']);
                $part->update([
                    'name' => $this->part['name'],
                    'part_number' => $this->part['part_number'],
                    'description' => $this->part['description'],
                    'stock_quantity' => $this->part['stock_quantity'],
                    'unit_cost' => $this->part['unit_cost'],
                    'minimum_stock_level' => $this->part['minimum_stock_level'],
                    'maintenance_equipment_id' => $this->part['maintenance_equipment_id'],
                ]);
                $notificationType = 'info';
                $message = "Part '{$this->part['name']}' has been updated successfully.";
            } else {
                // Add a direct database insert as a fallback
                $result = DB::table('equipment_parts')->insert([
                    'name' => $this->part['name'],
                    'part_number' => $this->part['part_number'],
                    'description' => $this->part['description'],
                    'stock_quantity' => $this->part['stock_quantity'],
                    'unit_cost' => $this->part['unit_cost'],
                    'minimum_stock_level' => $this->part['minimum_stock_level'],
                    'maintenance_equipment_id' => $this->part['maintenance_equipment_id'],
                    'last_restock_date' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                if (!$result) {
                    throw new \Exception('Failed to insert record directly');
                }

                $notificationType = 'success';
                $message = "Part '{$this->part['name']}' has been created successfully.";
            }

            $this->dispatch('notify', type: $notificationType, message: $message);
            $this->showModal = false;
            $this->reset('part');
        } catch (\Exception $e) {
            Log::error('Error saving part: ' . $e->getMessage());
            Log::error('Part data: ' . json_encode($this->part));
            $notificationType = 'error';
            $message = 'Error saving part: ' . $e->getMessage();
            $this->dispatch('notify', type: $notificationType, message: $message);
        }
    }

    /**
     * Open the confirm delete modal
     */
    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    /**
     * Process the delete confirmation
     */
    public function deletePart()
    {
        try {
            $part = EquipmentPart::findOrFail($this->deleteId);
            $name = $part->name;
            $part->delete();

            $notificationType = 'success';
            $message = "Part '{$name}' has been deleted successfully.";
            $this->dispatch('notify', type: $notificationType, message: $message);
            $this->showDeleteModal = false;
            $this->deleteId = null;
        } catch (\Exception $e) {
            Log::error('Error deleting part: ' . $e->getMessage());
            $notificationType = 'error';
            $message = 'Error deleting part: ' . $e->getMessage();
            $this->dispatch('notify', type: $notificationType, message: $message);
        }
    }

    /**
     * Open modal to view part details
     */
    public function viewPart($id)
    {
        try {
            $this->viewingPart = EquipmentPart::with('equipment')->findOrFail($id);
            $this->showViewModal = true;
        } catch (\Exception $e) {
            Log::error('Error viewing part: ' . $e->getMessage());
            $notificationType = 'error';
            $message = 'Part not found.';
            $this->dispatch('notify', type: $notificationType, message: $message);
        }
    }

    /**
     * Close the view modal
     */
    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewingPart = null;
    }

    /**
     * Close any modal
     */
    public function closeModal()
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->showViewModal = false;
        $this->viewingPart = null;
        $this->reset('part');
    }

    /**
     * Update stock quantity
     */
    public function updateStock($id, $amount)
    {
        try {
            $part = EquipmentPart::findOrFail($id);
            $newQuantity = $part->stock_quantity + $amount;

            if ($newQuantity < 0) {
                $notificationType = 'error';
                $message = 'Stock quantity cannot be negative';
                $this->dispatch('notify', type: $notificationType, message: $message);
                return;
            }

            $part->update([
                'stock_quantity' => $newQuantity,
                'last_restock_date' => $amount > 0 ? now() : $part->last_restock_date
            ]);

            $action = $amount > 0 ? 'added to' : 'removed from';
            $notificationType = 'info';
            $message = abs($amount) . ' items ' . $action . ' stock for ' . $part->name;
            $this->dispatch('notify', type: $notificationType, message: $message);
        } catch (\Exception $e) {
            Log::error('Error updating stock: ' . $e->getMessage());
            $notificationType = 'error';
            $message = 'Error updating stock: ' . $e->getMessage();
            $this->dispatch('notify', type: $notificationType, message: $message);
        }
    }

    /**
     * Real-time validation
     */
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    /**
     * Sort parts by a specific field
     */
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        // Reinicia para a primeira página quando ordenar
        $this->resetPage();
    }

    /**
     * Clear filters
     */
    public function clearFilters()
    {
        $this->search = '';
        $this->equipmentId = '';
        $this->resetPage();

        // Emite evento para feedback visual
        $this->dispatch('filters-cleared');

        $notificationType = 'info';
        $message = 'All search filters have been reset.';
        $this->dispatch('notify', type: $notificationType, message: $message);
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.equipment-parts');
    }
}
