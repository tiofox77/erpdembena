<?php

namespace App\Livewire\SupplyChain;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\SupplyChain\Product;
use App\Models\SupplyChain\ProductCategory;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\InventoryItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class Products extends Component
{
    use WithPagination, WithFileUploads;

    public $product_id;
    public $name;
    public $sku;
    public $category_id;
    public $description;
    public $unit_price = 0;
    public $cost_price = 0;
    public $unit_of_measure = 'unit';
    public $barcode;
    public $image;
    public $temp_image;
    public $min_stock_level = 0;
    public $reorder_point = 0;
    public $lead_time_days = 0;
    public $is_stockable = true;
    public $is_active = true;
    public $primary_supplier_id;
    public $tax_type = 'standard';
    public $tax_rate = 0;
    public $location;
    public $weight;
    public $width;
    public $height;
    public $depth;

    public $search = '';
    public $category_filter = '';
    public $supplier_filter = '';
    public $status_filter = '';
    public $stock_filter = '';
    
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $showModal = false;
    public $showViewModal = false;
    public $showConfirmDelete = false;
    public $itemToDelete = null;
    public $editMode = false;

    public $currentTab = 'general';
    public $tabList = ['general', 'inventory', 'dimensions', 'suppliers'];

    protected $paginationTheme = 'tailwind';

    protected $listeners = ['refresh' => '$refresh'];

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'sku' => ['required', 'string', 'max:50', 
                Rule::unique('sc_products', 'sku')->ignore($this->product_id)],
            'category_id' => 'nullable|exists:sc_product_categories,id',
            'description' => 'nullable|string',
            'unit_price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'unit_of_measure' => 'required|string|max:50',
            'barcode' => 'nullable|string|max:50',
            'temp_image' => 'nullable|image|max:2048', // 2MB Max
            'min_stock_level' => 'required|integer|min:0',
            'reorder_point' => 'required|integer|min:0',
            'lead_time_days' => 'required|integer|min:0',
            'is_stockable' => 'boolean',
            'is_active' => 'boolean',
            'primary_supplier_id' => 'nullable|exists:sc_suppliers,id',
            'tax_type' => 'required|in:standard,reduced,exempt',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'location' => 'nullable|string|max:100',
            'weight' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'depth' => 'nullable|numeric|min:0',
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function updatedTempImage()
    {
        $this->validate([
            'temp_image' => 'image|max:2048',
        ]);
    }

    public function resetFilters()
    {
        $this->reset(['search', 'category_filter', 'supplier_filter', 'status_filter', 'stock_filter']);
    }

    public function setTab($tab)
    {
        if (in_array($tab, $this->tabList)) {
            $this->currentTab = $tab;
        }
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->generateSku();
    }
    
    public function openAddModal()
    {
        $this->editMode = false;
        $this->resetForm();
        $this->showModal = true;
        $this->generateSku();
    }
    
    public function openEditModal($id)
    {
        $this->editMode = true;
        $this->resetForm();
        $this->edit($id);
    }
    
    public function openViewModal($id)
    {
        $this->view($id);
        $this->showViewModal = true;
    }

    public function edit($id)
    {
        $this->resetForm();
        $this->product_id = $id;
        $product = Product::findOrFail($id);
        
        $this->name = $product->name;
        $this->sku = $product->sku;
        $this->category_id = $product->category_id;
        $this->description = $product->description;
        $this->unit_price = $product->unit_price;
        $this->cost_price = $product->cost_price;
        $this->unit_of_measure = $product->unit_of_measure;
        $this->barcode = $product->barcode;
        $this->image = $product->image;
        $this->min_stock_level = $product->min_stock_level;
        $this->reorder_point = $product->reorder_point;
        $this->lead_time_days = $product->lead_time_days;
        $this->is_stockable = $product->is_stockable;
        $this->is_active = $product->is_active;
        $this->primary_supplier_id = $product->primary_supplier_id;
        $this->tax_type = $product->tax_type;
        $this->tax_rate = $product->tax_rate;
        $this->location = $product->location;
        $this->weight = $product->weight;
        $this->width = $product->width;
        $this->height = $product->height;
        $this->depth = $product->depth;
        
        $this->showModal = true;
    }

    public function view($id)
    {
        $this->product_id = $id;
        $product = Product::with(['category', 'primarySupplier', 'inventoryItems'])->findOrFail($id);
        
        $this->name = $product->name;
        $this->sku = $product->sku;
        $this->category_id = $product->category_id;
        $this->description = $product->description;
        $this->unit_price = $product->unit_price;
        $this->cost_price = $product->cost_price;
        $this->unit_of_measure = $product->unit_of_measure;
        $this->barcode = $product->barcode;
        $this->image = $product->image;
        $this->min_stock_level = $product->min_stock_level;
        $this->reorder_point = $product->reorder_point;
        $this->lead_time_days = $product->lead_time_days;
        $this->is_stockable = $product->is_stockable;
        $this->is_active = $product->is_active;
        $this->primary_supplier_id = $product->primary_supplier_id;
        $this->tax_type = $product->tax_type;
        $this->tax_rate = $product->tax_rate;
        $this->location = $product->location;
        $this->weight = $product->weight;
        $this->width = $product->width;
        $this->height = $product->height;
        $this->depth = $product->depth;
        
        $this->showViewModal = true;
    }

    public function save()
    {
        $this->validate();
        
        DB::beginTransaction();
        try {
            $product = $this->product_id ? 
                Product::findOrFail($this->product_id) : 
                new Product();
            
            $product->name = $this->name;
            $product->sku = $this->sku;
            $product->category_id = $this->category_id;
            $product->description = $this->description;
            $product->unit_price = $this->unit_price;
            $product->cost_price = $this->cost_price;
            $product->unit_of_measure = $this->unit_of_measure;
            $product->barcode = $this->barcode;
            $product->min_stock_level = $this->min_stock_level;
            $product->reorder_point = $this->reorder_point;
            $product->lead_time_days = $this->lead_time_days;
            $product->is_stockable = $this->is_stockable;
            $product->is_active = $this->is_active;
            $product->primary_supplier_id = $this->primary_supplier_id;
            $product->tax_type = $this->tax_type;
            $product->tax_rate = $this->tax_rate;
            $product->location = $this->location;
            $product->weight = $this->weight;
            $product->width = $this->width;
            $product->height = $this->height;
            $product->depth = $this->depth;
            
            // Handle image upload
            if ($this->temp_image) {
                // Remove old image if exists
                if ($product->image && Storage::exists('public/' . $product->image)) {
                    Storage::delete('public/' . $product->image);
                }
                
                $imagePath = $this->temp_image->store('products', 'public');
                $product->image = str_replace('public/', '', $imagePath);
            }
            
            $product->save();
            
            DB::commit();
            
            $this->resetForm();
            $this->showModal = false;
            
            $this->dispatch('notify', [
                'type' => 'success',
                'title' => __('livewire/products.success'),
                'message' => $this->product_id ? 
                    __('livewire/products.product_updated') : 
                    __('livewire/products.product_created')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => __('livewire/products.error'),
                'message' => $e->getMessage()
            ]);
        }
    }

    public function confirmDelete($id)
    {
        $this->showConfirmDelete = true;
        $this->itemToDelete = $id;
    }

    public function delete()
    {
        try {
            $product = Product::findOrFail($this->itemToDelete);
            
            // Check if product has inventory transactions, purchase orders, etc.
            if ($product->inventoryItems()->count() > 0) {
                throw new \Exception(__('livewire/products.cannot_delete_product_with_inventory'));
            }
            
            if ($product->purchaseOrderItems()->count() > 0) {
                throw new \Exception(__('livewire/products.cannot_delete_product_with_orders'));
            }
            
            // Delete product image if exists
            if ($product->image && Storage::exists('public/' . $product->image)) {
                Storage::delete('public/' . $product->image);
            }
            
            $product->delete();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'title' => __('livewire/products.success'),
                'message' => __('livewire/products.product_deleted')
            ]);
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => __('livewire/products.error'),
                'message' => $e->getMessage()
            ]);
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
            'product_id', 'name', 'sku', 'category_id', 'description',
            'barcode', 'temp_image', 'location',
            'primary_supplier_id', 'weight', 'width', 'height', 'depth',
            'currentTab'
        ]);
        
        $this->unit_price = 0;
        $this->cost_price = 0;
        $this->unit_of_measure = 'unit';
        $this->min_stock_level = 0;
        $this->reorder_point = 0;
        $this->lead_time_days = 0;
        $this->is_stockable = true;
        $this->is_active = true;
        $this->tax_type = 'standard';
        $this->tax_rate = 0;
        $this->currentTab = 'general';
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->showViewModal = false;
        $this->resetForm();
    }
    
    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->resetForm();
    }

    public function cancelDelete()
    {
        $this->showConfirmDelete = false;
        $this->itemToDelete = null;
    }

    protected function generateSku()
    {
        $prefix = 'PRD';
        $lastProduct = Product::orderBy('id', 'desc')->first();
        
        if ($lastProduct) {
            $lastSku = $lastProduct->sku;
            if (strpos($lastSku, $prefix) === 0) {
                $number = intval(substr($lastSku, strlen($prefix)));
                $newNumber = $number + 1;
            } else {
                $newNumber = 1;
            }
        } else {
            $newNumber = 1;
        }
        
        $this->sku = $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    public function render()
    {
        $query = Product::query();
        
        // Apply search filter
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhere('barcode', 'like', '%' . $this->search . '%');
            });
        }
        
        // Apply category filter
        if (!empty($this->category_filter)) {
            $query->where('category_id', $this->category_filter);
        }
        
        // Apply supplier filter
        if (!empty($this->supplier_filter)) {
            $query->where('primary_supplier_id', $this->supplier_filter);
        }
        
        // Apply status filter
        if ($this->status_filter !== '') {
            $query->where('is_active', $this->status_filter === 'active');
        }
        
        // Apply stock filter
        if ($this->stock_filter === 'low') {
            $query->whereHas('inventoryItems', function ($q) {
                $q->whereRaw('quantity_on_hand <= products.reorder_point');
            });
        } elseif ($this->stock_filter === 'out') {
            $query->whereHas('inventoryItems', function ($q) {
                $q->where('quantity_on_hand', '<=', 0);
            });
        }
        
        $products = $query->with(['category', 'primarySupplier'])
                           ->withCount('inventoryItems')
                           ->orderBy($this->sortField, $this->sortDirection)
                           ->paginate(10);
        
        // Get inventory summary for each product
        foreach ($products as $product) {
            $product->total_stock = InventoryItem::where('product_id', $product->id)->sum('quantity_on_hand');
        }
        
        // Get categories for dropdown
        $categories = ProductCategory::orderBy('name')->get();
        
        // Get suppliers for dropdown
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        
        return view('livewire.supply-chain.products', [
            'products' => $products,
            'categories' => $categories,
            'suppliers' => $suppliers
        ]);
    }
}
