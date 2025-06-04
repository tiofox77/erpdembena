<?php

namespace App\Livewire\Mrp;

use App\Models\Mrp\FailureCategory;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class FailureCategories extends Component
{
    use WithPagination;
    use AuthorizesRequests;

    // Propriedades para filtragem e pesquisa
    public $search = '';
    public $isActive = '';
    public $perPage = 10;
    
    // Propriedades para ordenação
    public $sortField = 'name';
    public $sortDirection = 'asc';

    // Propriedades para o modal
    public $showModal = false;
    public $editMode = false;
    public $failureCategory = [];
    public $categoryId = null;
    public $confirmingDeletion = false;

    // Propriedades para validação
    public $rules = [
        'failureCategory.code' => 'required|string|max:20|unique:mrp_failure_categories,code',
        'failureCategory.name' => 'required|string|max:255',
        'failureCategory.description' => 'nullable|string',
        'failureCategory.color' => 'required|string|max:20',
        'failureCategory.is_active' => 'boolean'
    ];

    public function mount()
    {
        $this->failureCategory = [
            'code' => '',
            'name' => '',
            'description' => '',
            'color' => '#3498db',
            'is_active' => true
        ];
    }

    /**
     * Sort by the given field
     *
     * @param string $field
     * @return void
     */
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        
        $this->sortField = $field;
    }
    
    public function render()
    {
        $query = FailureCategory::query();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->isActive !== '') {
            $query->where('is_active', $this->isActive === 'active');
        }

        $categories = $query->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);

        return view('livewire.mrp.failure-categories', [
            'categories' => $categories
        ]);
    }

    /**
     * Generate a unique failure category code
     *
     * @return string
     */
    private function generateUniqueCode()
    {
        $prefix = 'FC'; // Failure Category prefix
        $latestCategory = FailureCategory::orderBy('id', 'desc')->first();
        
        if ($latestCategory) {
            // Extract the numeric part of the latest code
            $numericPart = intval(preg_replace('/[^0-9]/', '', $latestCategory->code));
            $nextNumeric = $numericPart + 1;
        } else {
            $nextNumeric = 1;
        }
        
        // Format the new code with leading zeros
        return $prefix . str_pad($nextNumeric, 4, '0', STR_PAD_LEFT);
    }

    public function createCategory()
    {
        $this->reset('failureCategory', 'categoryId');
        $this->failureCategory = [
            'code' => $this->generateUniqueCode(),
            'name' => '',
            'description' => '',
            'color' => '#3498db',
            'is_active' => true
        ];
        $this->resetValidation();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function editCategory($id)
    {
        $this->resetValidation();
        $this->categoryId = $id;
        $category = FailureCategory::findOrFail($id);
        $this->failureCategory = $category->toArray();
        $this->failureCategory['color'] = $category->color;
        $this->editMode = true;
        $this->showModal = true;
    }

    public function saveCategory()
    {
        // Modificar regras para atualização
        if ($this->editMode) {
            $this->rules['failureCategory.code'] = 'required|string|max:20|unique:mrp_failure_categories,code,' . $this->categoryId;
        }

        $this->validate();

        if ($this->editMode) {
            $category = FailureCategory::findOrFail($this->categoryId);
        } else {
            $category = new FailureCategory();
        }

        $category->code = $this->failureCategory['code'];
        $category->name = $this->failureCategory['name'];
        $category->description = $this->failureCategory['description'];
        $category->color = $this->failureCategory['color'];
        $category->is_active = $this->failureCategory['is_active'];
        $category->created_by = $this->editMode ? $category->created_by : Auth::id();
        $category->updated_by = Auth::id();

        $category->save();

        $this->showModal = false;
        $this->dispatch('toast', [
            'type' => 'success',
            'message' => $this->editMode 
                ? __('messages.failure_category_updated') 
                : __('messages.failure_category_created')
        ]);
    }

    public function confirmDelete($id)
    {
        $this->categoryId = $id;
        $this->confirmingDeletion = true;
    }

    public function deleteCategory()
    {
        $category = FailureCategory::findOrFail($this->categoryId);
        
        // Verificar se a categoria tem causas raiz associadas
        if ($category->rootCauses()->count() > 0) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => __('messages.failure_category_has_root_causes')
            ]);
            $this->confirmingDeletion = false;
            return;
        }
        
        $category->delete();
        $this->confirmingDeletion = false;
        $this->dispatch('toast', [
            'type' => 'success',
            'message' => __('messages.failure_category_deleted')
        ]);
    }

    public function toggleActive($id)
    {
        $category = FailureCategory::findOrFail($id);
        $category->is_active = !$category->is_active;
        $category->updated_by = Auth::id();
        $category->save();

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => $category->is_active 
                ? __('messages.failure_category_activated') 
                : __('messages.failure_category_deactivated')
        ]);
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->confirmingDeletion = false;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingIsActive()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }
}
