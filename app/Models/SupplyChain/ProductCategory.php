<?php

namespace App\Models\SupplyChain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\SupplyChain\Product;

class ProductCategory extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'sc_product_categories';

    protected $fillable = [
        'name',
        'code',
        'description',
        'color',
        'parent_id'
    ];

    /**
     * Get all products in this category
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    /**
     * Get the parent category
     */
    public function parent()
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    /**
     * Get all subcategories
     */
    public function children()
    {
        return $this->hasMany(ProductCategory::class, 'parent_id');
    }

    /**
     * Get all recursive subcategories
     */
    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    /**
     * Get only root categories (with no parent)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get all products in this category and subcategories
     */
    public function allProducts()
    {
        $categoryIds = $this->getAllChildrenIds();
        $categoryIds[] = $this->id;
        
        return Product::whereIn('category_id', $categoryIds);
    }

    /**
     * Get IDs of all children categories recursively
     */
    protected function getAllChildrenIds()
    {
        $ids = [];
        
        foreach ($this->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $child->getAllChildrenIds());
        }
        
        return $ids;
    }
}
