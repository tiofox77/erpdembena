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
        'area_id' => '',
        'function' => ''
    ];
    
    // Validation rules
    protected function rules()
    {
        return [
            'technician.name' => 'required|string|max:255',
            'technician.phone_number' => 'nullable|string|max:20',
            'technician.address' => 'nullable|string|max:255',
            'technician.gender' => 'nullable|string|in:male,female,other',
            'technician.age' => 'nullable|integer',
            'technician.line_id' => 'nullable|exists:maintenance_lines,id',
            'technician.area_id' => 'nullable|exists:maintenance_areas,id',
            'technician.function' => 'nullable|string|max:255',
        ];
    }
    
    // Custom validation messages
    protected function messages()
    {
        return [
            'technician.name.required' => 'The technician name is required.',
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
            // Tratar campos vazios convertendo-os para NULL
            $technicianData = $this->technician;
            
            // Converter strings vazias em NULL para campos específicos
            foreach(['gender', 'line_id', 'area_id', 'phone_number', 'address', 'function'] as $field) {
                if (isset($technicianData[$field]) && $technicianData[$field] === '') {
                    $technicianData[$field] = null;
                }
            }
            
            // Age deve ser um número ou null
            if (isset($technicianData['age']) && ($technicianData['age'] === '' || $technicianData['age'] === 0)) {
                $technicianData['age'] = null;
            }
            
            if ($this->isEditing) {
                $technician = Technician::findOrFail($technicianData['id']);
                $technician->update($technicianData);
                $message = __('messages.technician_updated');
            } else {
                Technician::create($technicianData);
                $message = __('messages.technician_created');
            }
            
            $this->reset('technician');
            $this->showModal = false;
            $this->dispatch('notify', type: 'success', message: $message);
        } catch (\Exception $e) {
            Log::error('Error saving technician: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: __('messages.error_saving_technician'));
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
