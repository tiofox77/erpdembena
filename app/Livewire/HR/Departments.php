<?php

namespace App\Livewire\HR;

use App\Models\HR\Department;
use Livewire\Component;
use Livewire\WithPagination;

class Departments extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'name';
    public $sortDirection = 'asc';

    // Form properties
    public $department_id;
    public $name;
    public $description;
    public $manager_id;
    public $is_active = true;

    // Modal flags
    public $showModal = false;
    public $showDeleteModal = false;
    public $isEditing = false;

    // Listeners
    protected $listeners = ['refreshDepartments' => '$refresh'];

    // Rules
    protected function rules()
    {
        return [
            'name' => 'required|min:3|max:255',
            'description' => 'nullable',
            'manager_id' => 'nullable|exists:employees,id',
            'is_active' => 'boolean',
        ];
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

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->reset([
            'department_id', 'name', 'description', 'manager_id'
        ]);
        $this->is_active = true;
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function edit(Department $department)
    {
        $this->department_id = $department->id;
        $this->name = $department->name;
        $this->description = $department->description;
        $this->manager_id = $department->manager_id;
        $this->is_active = $department->is_active;

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function confirmDelete(Department $department)
    {
        $this->department_id = $department->id;
        $this->showDeleteModal = true;
    }

    public function save()
    {
        $validatedData = $this->validate();

        if ($this->isEditing) {
            $department = Department::find($this->department_id);
            $department->update($validatedData);
            session()->flash('message', 'Department updated successfully.');
        } else {
            Department::create($validatedData);
            session()->flash('message', 'Department created successfully.');
        }

        $this->showModal = false;
        $this->reset([
            'department_id', 'name', 'description', 'manager_id'
        ]);
        $this->is_active = true;
    }

    public function delete()
    {
        $department = Department::find($this->department_id);
        $department->delete();
        $this->showDeleteModal = false;
        session()->flash('message', 'Department deleted successfully.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
    }
    
    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
    }
    
    public function resetFilters()
    {
        $this->search = '';
        $this->resetPage();
    }

    public function hydrate()
    {
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        $departments = Department::where('name', 'like', "%{$this->search}%")
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.hr.departments', [
            'departments' => $departments,
        ]);
    }
}
