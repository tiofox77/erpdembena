<?php

namespace App\Livewire\SupplyChain;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SupplyChain\ProductCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductCategories extends Component
{
    use WithPagination;

    public $category_id;
    public $name;
    public $code;
    public $description;
    public $color = '#3B82F6'; // Default color - blue
    public $parent_id;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $showModal = false;
    public $showConfirmDelete = false;
    public $itemToDelete = null;
    public $editMode = false;
    public $deleteCategoryName = null;
    public $deleteHasProducts = false;
    public $deleteHasChildren = false;

    protected $paginationTheme = 'tailwind';

    protected $listeners = ['refresh' => '$refresh', 'closeModal' => 'closeModal'];

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:50', 
                Rule::unique('sc_product_categories', 'code')->ignore($this->category_id)],
            'description' => 'nullable|string',
            'color' => 'required|string|size:7|starts_with:#',
            'parent_id' => 'nullable|exists:sc_product_categories,id',
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function openAddModal()
    {
        $this->create();
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->generateCategoryCode();
    }

    public function edit($id)
    {
        $this->resetForm();
        $this->category_id = $id;
        $category = ProductCategory::findOrFail($id);
        
        $this->name = $category->name;
        $this->code = $category->code;
        $this->description = $category->description;
        $this->color = $category->color;
        $this->parent_id = $category->parent_id;
        
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();
        
        // Check for circular reference in parent-child relationship
        if ($this->parent_id && $this->category_id) {
            if ($this->parent_id == $this->category_id) {
                $this->addError('parent_id', __('Category cannot be its own parent'));
                return;
            }
            
            // Check if selected parent is one of the category's children
            $category = ProductCategory::findOrFail($this->category_id);
            $childrenIds = $this->getAllChildrenIds($category);
            
            if (in_array($this->parent_id, $childrenIds)) {
                $this->addError('parent_id', __('Cannot select a child category as parent'));
                return;
            }
        }
        
        DB::beginTransaction();
        try {
            $category = $this->category_id ? 
                ProductCategory::findOrFail($this->category_id) : 
                new ProductCategory();
            
            $category->name = $this->name;
            $category->code = $this->code;
            $category->description = $this->description;
            $category->color = $this->color;
            $category->parent_id = $this->parent_id ?: null;
            
            $category->save();
            
            DB::commit();
            
            $this->resetForm();
            $this->showModal = false;
            
            $this->dispatch('notify', 
                type: 'success', 
                title: __('livewire/product-categories.success'),
                message: $this->category_id ? 
                    __('livewire/product-categories.category_updated') : 
                    __('livewire/product-categories.category_created')
            );

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', 
                type: 'error', 
                title: __('livewire/product-categories.error'),
                message: $e->getMessage()
            );
        }
    }

    public function confirmDelete($id)
    {
        $category = ProductCategory::findOrFail($id);
        $this->deleteCategoryName = $category->name;
        $this->deleteHasProducts = ($category->products()->count() > 0);
        $this->deleteHasChildren = ($category->children()->count() > 0);
        $this->itemToDelete = $id;
        $this->showConfirmDelete = true;
    }

    public function delete()
    {
        try {
            $category = ProductCategory::findOrFail($this->itemToDelete);
            
            // Check if category has any products
            if ($category->products()->count() > 0) {
                throw new \Exception(__('Cannot delete category with associated products'));
            }
            
            // Check if category has child categories
            if ($category->children()->count() > 0) {
                throw new \Exception(__('Cannot delete category with child categories'));
            }
            
            $category->delete();
            
            $this->dispatch('notify', 
                type: 'success', 
                title: __('livewire/product-categories.success'),
                message: __('livewire/product-categories.category_deleted')
            );
            
        } catch (\Exception $e) {
            $this->dispatch('notify', 
                type: 'error', 
                title: __('livewire/product-categories.error'),
                message: $e->getMessage()
            );
        }
        
        $this->showConfirmDelete = false;
        $this->itemToDelete = null;
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

    public function resetForm()
    {
        $this->reset([
            'category_id', 'name', 'code', 'description', 'parent_id'
        ]);
        
        $this->color = '#3B82F6';
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }
    
    /**
     * Get the contrasting color (black or white) for text based on the background color
     * 
     * @param string $hexColor The background color in hex format (e.g. #ffffff)
     * @return string The contrasting color (#ffffff for dark backgrounds, #000000 for light backgrounds)
     */
    public function getContrastColor($hexColor)
    {
        // Remove the # if present
        $hex = ltrim($hexColor, '#');
        
        // Convert hex to RGB
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        // Calculate the brightness (using the formula from W3C standards)
        $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
        
        // Return white for dark colors and black for light colors
        return ($brightness > 128) ? '#000000' : '#ffffff';
    }

    public function cancelDelete()
    {
        $this->showConfirmDelete = false;
        $this->itemToDelete = null;
        $this->deleteCategoryName = null;
        $this->deleteHasProducts = false;
        $this->deleteHasChildren = false;
    }

    protected function generateCategoryCode()
    {
        $prefix = 'CAT';
        $lastCategory = ProductCategory::orderBy('id', 'desc')->first();
        
        if ($lastCategory) {
            $lastCode = $lastCategory->code;
            if (strpos($lastCode, $prefix) === 0) {
                $number = intval(substr($lastCode, strlen($prefix)));
                $newNumber = $number + 1;
            } else {
                $newNumber = 1;
            }
        } else {
            $newNumber = 1;
        }
        
        $this->code = $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    protected function getAllChildrenIds($category)
    {
        $ids = [];
        
        foreach ($category->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getAllChildrenIds($child));
        }
        
        return $ids;
    }

    public function render()
    {
        $query = ProductCategory::query();
        
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }
        
        $categories = $query->with('parent')
                            ->orderBy($this->sortField, $this->sortDirection)
                            ->paginate($this->perPage);
        
        $parentCategories = ProductCategory::orderBy('name')
                                          ->get()
                                          ->map(function ($category) {
                                              return [
                                                  'id' => $category->id,
                                                  'name' => $category->name
                                              ];
                                          });
        
        return view('livewire.supply-chain.product-categories', [
            'categories' => $categories,
            'parentCategories' => $parentCategories
        ]);
    }
}
