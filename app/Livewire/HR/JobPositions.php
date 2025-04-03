<?php

namespace App\Livewire\HR;

use App\Models\HR\Department;
use App\Models\HR\JobCategory;
use App\Models\HR\JobPosition;
use Livewire\Component;
use Livewire\WithPagination;

class JobPositions extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'title';
    public $sortDirection = 'asc';
    public $filters = [
        'department_id' => '',
        'category_id' => '',
        'is_active' => '',
    ];

    // Form properties
    public $position_id;
    public $title;
    public $description;
    public $responsibilities;
    public $requirements;
    public $salary_range_min;
    public $salary_range_max;
    public $department_id;
    public $category_id;
    public $is_active = true;

    // Modal flags
    public $showModal = false;
    public $showDeleteModal = false;
    public $isEditing = false;

    // Listeners
    protected $listeners = ['refreshPositions' => '$refresh'];

    // Rules
    protected function rules()
    {
        return [
            'title' => 'required|min:3|max:255',
            'description' => 'nullable',
            'responsibilities' => 'nullable',
            'requirements' => 'nullable',
            'salary_range_min' => 'nullable|numeric|min:0',
            'salary_range_max' => 'nullable|numeric|min:0|gte:salary_range_min',
            'department_id' => 'required|exists:departments,id',
            'category_id' => 'nullable|exists:job_categories,id',
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

    public function updatingFilters()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->reset([
            'position_id', 'title', 'description', 'responsibilities', 'requirements',
            'salary_range_min', 'salary_range_max', 'department_id', 'category_id'
        ]);
        $this->is_active = true;
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function edit(JobPosition $position)
    {
        $this->position_id = $position->id;
        $this->title = $position->title;
        $this->description = $position->description;
        $this->responsibilities = $position->responsibilities;
        $this->requirements = $position->requirements;
        $this->salary_range_min = $position->salary_range_min;
        $this->salary_range_max = $position->salary_range_max;
        $this->department_id = $position->department_id;
        $this->category_id = $position->category_id;
        $this->is_active = $position->is_active;

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function confirmDelete(JobPosition $position)
    {
        $this->position_id = $position->id;
        $this->showDeleteModal = true;
    }

    public function save()
    {
        $validatedData = $this->validate();

        if ($this->isEditing) {
            $position = JobPosition::find($this->position_id);
            $position->update($validatedData);
            session()->flash('message', 'Position updated successfully.');
        } else {
            JobPosition::create($validatedData);
            session()->flash('message', 'Position created successfully.');
        }

        $this->showModal = false;
        $this->reset([
            'position_id', 'title', 'description', 'responsibilities', 'requirements',
            'salary_range_min', 'salary_range_max', 'department_id', 'category_id'
        ]);
        $this->is_active = true;
    }

    public function delete()
    {
        $position = JobPosition::find($this->position_id);
        $position->delete();
        $this->showDeleteModal = false;
        session()->flash('message', 'Position deleted successfully.');
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
        $this->reset('filters');
        $this->search = '';
        $this->resetPage();
    }

    public function render()
    {
        $positions = JobPosition::where('title', 'like', "%{$this->search}%")
            ->when($this->filters['department_id'], function ($query) {
                return $query->where('department_id', $this->filters['department_id']);
            })
            ->when($this->filters['category_id'], function ($query) {
                return $query->where('category_id', $this->filters['category_id']);
            })
            ->when($this->filters['is_active'] !== '', function ($query) {
                return $query->where('is_active', $this->filters['is_active']);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $departments = Department::where('is_active', true)->get();
        $categories = JobCategory::where('is_active', true)->get();

        return view('livewire.hr.job-positions', [
            'positions' => $positions,
            'departments' => $departments,
            'categories' => $categories,
        ]);
    }
}
