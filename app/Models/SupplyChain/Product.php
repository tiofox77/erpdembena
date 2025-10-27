<?php

namespace App\Models\SupplyChain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\SupplyChain\ProductCategory;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\InventoryItem;
use App\Models\SupplyChain\PurchaseOrderItem;
use App\Models\SupplyChain\InventoryTransaction;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'sc_products';

    protected $fillable = [
        'name',
        'sku',
        'category_id',
        'description',
        'unit_price',
        'cost_price',
        'unit_of_measure',
        'barcode',
        'image',
        'min_stock_level',
        'reorder_point',
        'lead_time_days',
        'is_stockable',
        'is_active',
        'product_type',
        'primary_supplier_id',
        'tax_type',
        'tax_rate',
        'location',
        'weight',
        'width',
        'height',
        'depth'
    ];

    /**
     * Get the category that owns the product
     */
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /**
     * Get the primary supplier of the product
     */
    public function primarySupplier()
    {
        return $this->belongsTo(Supplier::class, 'primary_supplier_id');
    }

    /**
     * Get all inventory items for this product
     */
    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class);
    }

    /**
     * Get purchase order items for this product
     */
    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Get inventory transactions for this product
     */
    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    /**
     * Get only active products
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get only stockable products
     */
    public function scopeStockable($query)
    {
        return $query->where('is_stockable', true);
    }

    /**
     * Get products below reorder point
     */
    public function scopeBelowReorderPoint($query)
    {
        return $query->whereHas('inventoryItems', function ($q) {
            $q->whereRaw('quantity_on_hand <= products.reorder_point');
        });
    }

    /**
     * Calculate total inventory value
     */
    public function getInventoryValueAttribute()
    {
        return $this->inventoryItems->sum(function ($item) {
            return $item->quantity_on_hand * ($this->cost_price ?? 0);
        });
    }

    /**
     * Get total quantity on hand across all locations
     */
    public function getTotalQuantityAttribute()
    {
        return $this->inventoryItems->sum('quantity_on_hand');
    }
    
    /**
     * Get all BOM headers for this product
     */
    public function bomHeaders()
    {
        return $this->hasMany(\App\Models\Mrp\BomHeader::class);
    }
}
