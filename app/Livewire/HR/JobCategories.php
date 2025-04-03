<?php

namespace App\Livewire\HR;

use App\Models\HR\JobCategory;
use Livewire\Component;
use Livewire\WithPagination;

class JobCategories extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'name';
    public $sortDirection = 'asc';

    // Form properties
    public $category_id;
    public $name;
    public $description;
    public $is_active = true;

    // Modal flags
    public $showModal = false;
    public $showDeleteModal = false;
    public $isEditing = false;

    // Lifecycle hooks
    public function hydrate()
    {
        $this->resetErrorBag();
        $this->resetValidation();
    }

    // Listeners
    protected $listeners = ['refreshJobCategories' => '$refresh'];

    // Rules
    protected function rules()
    {
        return [
            'name' => 'required|min:3|max:255',
            'description' => 'nullable',
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
            'category_id', 'name', 'description'
        ]);
        $this->is_active = true;
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function edit(JobCategory $category)
    {
        $this->category_id = $category->id;
        $this->name = $category->name;
        $this->description = $category->description;
        $this->is_active = $category->is_active;

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function confirmDelete(JobCategory $category)
    {
        $this->category_id = $category->id;
        $this->showDeleteModal = true;
    }

    public function save()
    {
        $validatedData = $this->validate();

        if ($this->isEditing) {
            $category = JobCategory::find($this->category_id);
            $category->update($validatedData);
            session()->flash('message', 'Job category updated successfully.');
        } else {
            JobCategory::create($validatedData);
            session()->flash('message', 'Job category created successfully.');
        }

        $this->showModal = false;
        $this->reset([
            'category_id', 'name', 'description'
        ]);
        $this->is_active = true;
    }

    public function delete()
    {
        $category = JobCategory::find($this->category_id);
        $category->delete();
        $this->showDeleteModal = false;
        session()->flash('message', 'Job category deleted successfully.');
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

    public function render()
    {
        $categories = JobCategory::where('name', 'like', "%{$this->search}%")
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.hr.job-categories', [
            'categories' => $categories,
        ]);
    }
}
