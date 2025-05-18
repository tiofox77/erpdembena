<?php

namespace App\Livewire\SupplyChain;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SupplyChain\SupplierCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SupplierCategories extends Component
{
    use WithPagination;

    public $category_id;
    public $name;
    public $code;
    public $description;
    public $is_active = true;

    public $search = '';
    public $statusFilter = '';
    public $perPage = 10;
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $showModal = false;
    public $showDeleteModal = false;
    public $editMode = false;
    public $itemToDelete = null;
    
    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc']
    ];

    protected $paginationTheme = 'tailwind';

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'code' => [
                'required', 
                'string', 
                'max:50',
                Rule::unique('sc_supplier_categories', 'code')->ignore($this->category_id)
            ],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        
        $this->sortField = $field;
    }

    public function openAddModal()
    {
        $this->create();
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->editMode = false;
        $this->generateCategoryCode();
    }

    public function edit($id)
    {
        $this->resetForm();
        $category = SupplierCategory::findOrFail($id);
        
        $this->category_id = $category->id;
        $this->name = $category->name;
        $this->code = $category->code;
        $this->description = $category->description;
        $this->is_active = $category->is_active;
        
        $this->showModal = true;
        $this->editMode = true;
    }

    public function save()
    {
        $this->validate();
        
        DB::beginTransaction();
        try {
            $data = [
                'name' => $this->name,
                'code' => $this->code,
                'description' => $this->description,
                'is_active' => $this->is_active,
                'updated_by' => auth()->id(),
            ];
            
            if (!$this->editMode) {
                $data['created_by'] = auth()->id();
                SupplierCategory::create($data);
                $message = __('supplier.category_created');
            } else {
                $category = SupplierCategory::findOrFail($this->category_id);
                $category->update($data);
                $message = __('supplier.category_updated');
            }
            
            DB::commit();
            
            $this->resetForm();
            $this->showModal = false;
            
            $this->dispatch('notify', 
                type: 'success', 
                title: __('supplier.success'),
                message: $message
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', 
                type: 'error', 
                title: __('supplier.error'),
                message: $e->getMessage()
            );
        }
    }

    public function confirmDelete($id)
    {
        $this->itemToDelete = $id;
        $category = SupplierCategory::findOrFail($id);
        $this->dispatch('confirm-delete', 
            title: __('supplier.delete_category_title'),
            text: __('supplier.delete_category_text', ['name' => $category->name]),
            confirmButtonText: __('supplier.delete_confirm_button'),
            cancelButtonText: __('supplier.cancel_button'),
            onConfirmed: 'delete',
            onCancelled: 'cancelDelete'
        );
    }

    public function delete()
    {
        try {
            $category = SupplierCategory::findOrFail($this->itemToDelete);
            
            // Check if category has suppliers
            if ($category->suppliers()->count() > 0) {
                throw new \Exception(__('supplier.category_has_suppliers'));
            }
            
            $category->delete();
            
            $this->dispatch('notify', 
                type: 'success', 
                title: __('supplier.success'),
                message: __('supplier.category_deleted')
            );
            
        } catch (\Exception $e) {
            $this->dispatch('notify', 
                type: 'error', 
                title: __('supplier.error'),
                message: $e->getMessage()
            );
        }
        
        $this->itemToDelete = null;
        $this->showDeleteModal = false;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'category_id',
            'name',
            'code',
            'description',
            'is_active',
        ]);
        
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function generateCategoryCode()
    {
        if (empty($this->code)) {
            $latest = SupplierCategory::orderBy('id', 'desc')->first();
            $number = $latest ? (int) substr($latest->code, 2) + 1 : 1;
            $this->code = 'CT' . str_pad($number, 4, '0', STR_PAD_LEFT);
        }
    }

    public function clearFilters()
    {
        $this->reset(['search', 'statusFilter', 'sortField', 'sortDirection', 'perPage']);
        $this->sortField = 'name';
        $this->sortDirection = 'asc';
        $this->perPage = 10;
        $this->resetPage();
    }

    public function render()
    {
        $query = SupplierCategory::query()
            ->withCount('suppliers')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('is_active', $this->statusFilter === 'active');
            });
            
        $categories = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.supply-chain.supplier-categories', [
            'categories' => $categories,
            'supplierCounts' => $this->getSupplierCounts(),
        ]);
    }
    
    protected function getSupplierCounts()
    {
        return [
            'active' => SupplierCategory::where('is_active', true)->count(),
            'inactive' => SupplierCategory::where('is_active', false)->count(),
            'all' => SupplierCategory::count(),
        ];
    }
}
