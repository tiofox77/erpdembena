<?php

namespace App\Livewire\Mrp;

use App\Models\Mrp\FailureRootCause;
use App\Models\Mrp\FailureCategory;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class FailureRootCauses extends Component
{
    use WithPagination;
    use AuthorizesRequests;

    // Propriedades para filtragem e pesquisa
    public $search = '';
    public $isActive = '';
    public $categoryFilter = '';
    public $perPage = 10;
    
    // Propriedades para ordenação
    public $sortField = 'name';
    public $sortDirection = 'asc';

    // Propriedades para o modal
    public $showModal = false;
    public $editMode = false;
    public $rootCause = [];
    public $rootCauseId = null;
    public $confirmingDeletion = false;
    
    // Lista de categorias para o select
    public $categories = [];

    // Propriedades para validação
    public $rules = [
        'rootCause.code' => 'required|string|max:20|unique:mrp_failure_root_causes,code',
        'rootCause.name' => 'required|string|max:255',
        'rootCause.description' => 'nullable|string',
        'rootCause.category_id' => 'required|exists:mrp_failure_categories,id',
        'rootCause.is_active' => 'boolean'
    ];

    public function mount()
    {
        $this->loadCategories();
        $this->rootCause = [
            'code' => '',
            'name' => '',
            'description' => '',
            'category_id' => '',
            'is_active' => true
        ];
    }

    /**
     * Generate a unique failure root cause code
     *
     * @return string
     */
    private function generateUniqueCode()
    {
        $prefix = 'RC'; // Root Cause prefix
        $latestRootCause = FailureRootCause::orderBy('id', 'desc')->first();
        
        if ($latestRootCause) {
            // Extract the numeric part of the latest code
            $numericPart = intval(preg_replace('/[^0-9]/', '', $latestRootCause->code));
            $nextNumeric = $numericPart + 1;
        } else {
            $nextNumeric = 1;
        }
        
        // Format the new code with leading zeros
        return $prefix . str_pad($nextNumeric, 4, '0', STR_PAD_LEFT);
    }

    public function loadCategories()
    {
        $this->categories = FailureCategory::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->toArray();
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
        $query = FailureRootCause::with('category');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }

        if ($this->isActive !== '') {
            $query->where('is_active', $this->isActive === 'active');
        }
        
        // Handle special case for failure_category_id since it needs to be sorted from the relationship
        if ($this->sortField === 'failure_category_id') {
            $query->join('mrp_failure_categories', 'mrp_failure_root_causes.category_id', '=', 'mrp_failure_categories.id')
                  ->select('mrp_failure_root_causes.*')
                  ->orderBy('mrp_failure_categories.name', $this->sortDirection);
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }
        
        $rootCauses = $query->paginate($this->perPage);

        return view('livewire.mrp.failure-root-causes', [
            'rootCauses' => $rootCauses
        ]);
    }

    public function createRootCause()
    {
        $this->reset('rootCause', 'rootCauseId');
        $this->rootCause = [
            'code' => $this->generateUniqueCode(),
            'name' => '',
            'description' => '',
            'category_id' => '',
            'is_active' => true
        ];
        $this->loadCategories();
        $this->resetValidation();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function editRootCause($id)
    {
        $this->resetValidation();
        $this->rootCauseId = $id;
        $rootCause = FailureRootCause::findOrFail($id);
        $this->rootCause = $rootCause->toArray();
        $this->loadCategories();
        $this->editMode = true;
        $this->showModal = true;
    }

    public function saveRootCause()
    {
        // Modificar regras para atualização
        if ($this->editMode) {
            $this->rules['rootCause.code'] = 'required|string|max:20|unique:mrp_failure_root_causes,code,' . $this->rootCauseId;
        }

        $this->validate();

        if ($this->editMode) {
            $rootCause = FailureRootCause::findOrFail($this->rootCauseId);
        } else {
            $rootCause = new FailureRootCause();
        }

        $rootCause->code = $this->rootCause['code'];
        $rootCause->name = $this->rootCause['name'];
        $rootCause->description = $this->rootCause['description'];
        $rootCause->category_id = $this->rootCause['category_id'];
        $rootCause->is_active = $this->rootCause['is_active'];
        $rootCause->created_by = $this->editMode ? $rootCause->created_by : Auth::id();
        $rootCause->updated_by = Auth::id();

        $rootCause->save();

        $this->showModal = false;
        $this->dispatch('toast', [
            'type' => 'success',
            'message' => $this->editMode 
                ? __('messages.failure_root_cause_updated') 
                : __('messages.failure_root_cause_created')
        ]);
    }

    public function confirmDelete($id)
    {
        $this->rootCauseId = $id;
        $this->confirmingDeletion = true;
    }

    public function deleteRootCause()
    {
        $rootCause = FailureRootCause::findOrFail($this->rootCauseId);
        $rootCause->delete();
        $this->confirmingDeletion = false;
        $this->dispatch('toast', [
            'type' => 'success',
            'message' => __('messages.failure_root_cause_deleted')
        ]);
    }

    public function toggleActive($id)
    {
        $rootCause = FailureRootCause::findOrFail($id);
        $rootCause->is_active = !$rootCause->is_active;
        $rootCause->updated_by = Auth::id();
        $rootCause->save();

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => $rootCause->is_active 
                ? __('messages.failure_root_cause_activated') 
                : __('messages.failure_root_cause_deactivated')
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

    public function updatingCategoryFilter()
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
