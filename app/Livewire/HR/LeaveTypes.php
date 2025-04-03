<?php

namespace App\Livewire\HR;

use App\Models\HR\LeaveType;
use Livewire\Component;
use Livewire\WithPagination;

class LeaveTypes extends Component
{
    use WithPagination;
    
    public $search = '';
    public $perPage = 10;
    public $sortField = 'name';
    public $sortDirection = 'asc';
    
    // Form properties
    public $leave_type_id;
    public $name;
    public $description;
    public $days_allowed;
    public $requires_approval = true;
    public $is_paid = true;
    public $is_active = true;
    
    // Modal flags
    public $showModal = false;
    public $showDeleteModal = false;
    public $isEditing = false;
    
    // Validation rules
    protected function rules()
    {
        return [
            'name' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
            'days_allowed' => 'required|numeric|min:0',
            'requires_approval' => 'boolean',
            'is_paid' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
    
    public function updatingSearch()
    {
        $this->resetPage();
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
    
    public function create()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }
    
    public function edit(LeaveType $leaveType)
    {
        $this->resetForm();
        
        $this->leave_type_id = $leaveType->id;
        $this->name = $leaveType->name;
        $this->description = $leaveType->description;
        $this->days_allowed = $leaveType->days_allowed;
        $this->requires_approval = $leaveType->requires_approval;
        $this->is_paid = $leaveType->is_paid;
        $this->is_active = $leaveType->is_active;
        
        $this->isEditing = true;
        $this->showModal = true;
    }
    
    public function save()
    {
        $validatedData = $this->validate();
        
        if ($this->isEditing) {
            $leaveType = LeaveType::find($this->leave_type_id);
            $leaveType->update($validatedData);
            session()->flash('message', 'Leave type updated successfully.');
        } else {
            LeaveType::create($validatedData);
            session()->flash('message', 'Leave type created successfully.');
        }
        
        $this->resetForm();
        $this->showModal = false;
    }
    
    public function confirmDelete(LeaveType $leaveType)
    {
        $this->leave_type_id = $leaveType->id;
        $this->showDeleteModal = true;
    }
    
    public function delete()
    {
        $leaveType = LeaveType::find($this->leave_type_id);
        
        // Check if there are any leaves associated with this type
        if ($leaveType->leaves()->count() > 0) {
            session()->flash('error', 'Cannot delete this leave type as it has leaves associated with it.');
        } else {
            $leaveType->delete();
            session()->flash('message', 'Leave type deleted successfully.');
        }
        
        $this->showDeleteModal = false;
    }
    
    public function toggleActive(LeaveType $leaveType)
    {
        $leaveType->is_active = !$leaveType->is_active;
        $leaveType->save();
        
        $status = $leaveType->is_active ? 'activated' : 'deactivated';
        session()->flash('message', "Leave type {$status} successfully.");
    }
    
    public function resetForm()
    {
        $this->reset([
            'leave_type_id', 'name', 'description', 'days_allowed', 
            'requires_approval', 'is_paid', 'is_active'
        ]);
        
        $this->requires_approval = true;
        $this->is_paid = true;
        $this->is_active = true;
        
        $this->resetErrorBag();
    }
    
    public function closeModal()
    {
        $this->showModal = false;
        $this->resetErrorBag();
    }
    
    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
    }
    
    public function render()
    {
        $leaveTypes = LeaveType::when($this->search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
            
        return view('livewire.hr.leave-types', [
            'leaveTypes' => $leaveTypes
        ]);
    }
}
