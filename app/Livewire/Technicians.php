<?php

namespace App\Livewire;

use App\Models\Technician;
use App\Models\MaintenanceLine;
use App\Models\MaintenanceArea;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Url;

class Technicians extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public $search = '';
    
    public $perPage = 10;
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $showModal = false;
    public $showDeleteModal = false;
    public $isEditing = false;
    public $deleteId = null;
    
    // Form data
    public $technician = [
        'name' => '',
        'phone_number' => '',
        'address' => '',
        'gender' => '',
        'age' => null,
        'line_id' => '',
        'area_id' => ''
    ];
    
    // Validation rules
    protected function rules()
    {
        $technicianId = isset($this->technician['id']) ? $this->technician['id'] : '';
        
        return [
            'technician.name' => 'required|string|max:255',
            'technician.phone_number' => 'required|string|max:20',
            'technician.address' => 'nullable|string|max:255',
            'technician.gender' => 'nullable|string|in:male,female,other',
            'technician.age' => 'nullable|integer|min:18|max:100',
            'technician.line_id' => 'nullable|exists:maintenance_lines,id',
            'technician.area_id' => 'nullable|exists:maintenance_areas,id',
        ];
    }
    
    // Custom validation messages
    protected function messages()
    {
        return [
            'technician.name.required' => 'The technician name is required.',
            'technician.phone_number.required' => 'The phone number is required.',
            'technician.age.min' => 'The minimum age is 18 years.',
            'technician.age.max' => 'The maximum age is 100 years.',
            'technician.line_id.exists' => 'The selected line does not exist.',
            'technician.area_id.exists' => 'The selected area does not exist.',
        ];
    }
    
    // Get all available lines for dropdown
    public function getLinesProperty()
    {
        return MaintenanceLine::orderBy('name')->get();
    }
    
    // Get all available areas for dropdown
    public function getAreasProperty()
    {
        return MaintenanceArea::orderBy('name')->get();
    }
    
    // Get technicians based on search and sorting
    public function getTechniciansProperty()
    {
        return Technician::when($this->search, function ($query) {
                return $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('phone_number', 'like', '%' . $this->search . '%')
                      ->orWhere('address', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->with(['line', 'area'])
            ->paginate($this->perPage);
    }
    
    // Open modal to create new technician
    public function openCreateModal()
    {
        $this->resetValidation();
        $this->reset('technician');
        $this->isEditing = false;
        $this->showModal = true;
    }
    
    // Open modal to edit existing technician
    public function edit($id)
    {
        try {
            $this->resetValidation();
            $technician = Technician::findOrFail($id);
            $this->technician = $technician->toArray();
            $this->isEditing = true;
            $this->showModal = true;
        } catch (\Exception $e) {
            Log::error('Error editing technician: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Error loading technician data.');
        }
    }
    
    // Save technician (create or update)
    public function save()
    {
        $this->validate();
        
        try {
            if ($this->isEditing) {
                $technician = Technician::findOrFail($this->technician['id']);
                $technician->update($this->technician);
                $message = 'Technician updated successfully!';
            } else {
                Technician::create($this->technician);
                $message = 'Technician created successfully!';
            }
            
            $this->reset('technician');
            $this->showModal = false;
            $this->dispatch('notify', type: 'success', message: $message);
        } catch (\Exception $e) {
            Log::error('Error saving technician: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Error saving technician.');
        }
    }
    
    // Confirm deletion
    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }
    
    // Delete technician
    public function delete()
    {
        try {
            $technician = Technician::findOrFail($this->deleteId);
            $technician->delete();
            
            $this->deleteId = null;
            $this->showDeleteModal = false;
            $this->dispatch('notify', type: 'success', message: 'Technician deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Error deleting technician: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Error deleting technician.');
        }
    }
    
    // Close modal
    public function closeModal()
    {
        $this->reset('technician');
        $this->resetValidation();
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->isEditing = false;
    }
    
    // Sort by field
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }
    
    // Clear filters
    public function clearFilters()
    {
        $this->reset('search');
        $this->resetPage();
    }
    
    // Updated perPage
    public function updatedPerPage()
    {
        $this->resetPage();
    }
    
    // Real-time validation
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }
    
    // Render the component
    public function render()
    {
        return view('livewire.technicians', [
            'technicians' => $this->technicians,
            'lines' => $this->lines,
            'areas' => $this->areas
        ]);
    }
}
