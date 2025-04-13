<?php

namespace App\Models\SupplyChain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SupplyChain\Product;
use App\Models\SupplyChain\InventoryLocation;

class InventoryItem extends Model
{
    use HasFactory;
    
    protected $table = 'sc_inventory_items';

    protected $fillable = [
        'product_id',
        'location_id',
        'quantity_on_hand',
        'quantity_allocated',
        'quantity_available',
        'bin_location',
        'batch_number',
        'expiry_date',
        'serial_number',
        'status',
        'unit_cost'
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'quantity_on_hand' => 'decimal:2',
        'quantity_allocated' => 'decimal:2',
        'quantity_available' => 'decimal:2',
        'unit_cost' => 'decimal:2'
    ];

    /**
     * Get the product that owns the inventory item
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the location that owns the inventory item
     */
    public function location()
    {
        return $this->belongsTo(InventoryLocation::class);
    }

    /**
     * Get only available items
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')
                     ->where('quantity_available', '>', 0);
    }

    /**
     * Get items that are expired or expiring within days
     */
    public function scopeExpiring($query, $days = 30)
    {
        return $query->whereNotNull('expiry_date')
                     ->where('expiry_date', '<=', now()->addDays($days))
                     ->where('quantity_on_hand', '>', 0);
    }

    /**
     * Get items that are below critical level (min_stock_level)
     */
    public function scopeBelowMinimumLevel($query)
    {
        return $query->whereHas('product', function ($q) {
            $q->whereRaw('inventory_items.quantity_on_hand <= products.min_stock_level');
        });
    }

    /**
     * Calculate the total value of this inventory item
     */
    public function getTotalValueAttribute()
    {
        $cost = $this->unit_cost ?? $this->product->cost_price ?? 0;
        return $this->quantity_on_hand * $cost;
    }

    /**
     * Automatically calculate available quantity
     */
    protected static function booted()
    {
        static::saving(function ($inventoryItem) {
            $inventoryItem->quantity_available = max(0, $inventoryItem->quantity_on_hand - $inventoryItem->quantity_allocated);
        });
    }
}
