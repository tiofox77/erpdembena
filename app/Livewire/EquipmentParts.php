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
    public $isEditing = false;
    public $deleteId = null;

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
            ->with('equipment')
            ->orderBy('name')
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
            $this->dispatch('notify', type: 'error', message: 'Part not found.');
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
                $message = 'Part updated successfully';
                $notificationType = 'info';
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

                $message = 'Part created successfully';
                $notificationType = 'success';
            }

            $this->dispatch('notify', type: $notificationType, message: $message);
            $this->showModal = false;
            $this->reset('part');
        } catch (\Exception $e) {
            Log::error('Error saving part: ' . $e->getMessage());
            Log::error('Part data: ' . json_encode($this->part));
            $this->dispatch('notify', type: 'error', message: 'Error saving part: ' . $e->getMessage());
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
            $part->delete();

            $this->dispatch('notify', type: 'warning', message: 'Part deleted successfully');
            $this->showDeleteModal = false;
            $this->deleteId = null;
        } catch (\Exception $e) {
            Log::error('Error deleting part: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Error deleting part: ' . $e->getMessage());
        }
    }

    /**
     * Close any modal
     */
    public function closeModal()
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
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
                $this->dispatch('notify', type: 'error', message: 'Stock quantity cannot be negative');
                return;
            }

            $part->update([
                'stock_quantity' => $newQuantity,
                'last_restock_date' => $amount > 0 ? now() : $part->last_restock_date
            ]);

            $action = $amount > 0 ? 'added to' : 'removed from';
            $this->dispatch('notify', type: 'info', message: abs($amount) . ' items ' . $action . ' stock for ' . $part->name);
        } catch (\Exception $e) {
            Log::error('Error updating stock: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Error updating stock: ' . $e->getMessage());
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
     * Render the component
     */
    public function render()
    {
        return view('livewire.equipment-parts')
            ->layout('layouts.livewire');
    }
}
