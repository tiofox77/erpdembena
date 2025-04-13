<?php

namespace App\Models\SupplyChain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\SupplyChain\InventoryItem;
use App\Models\SupplyChain\GoodsReceipt;
use App\Models\SupplyChain\InventoryTransaction;

class InventoryLocation extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'sc_inventory_locations';

    protected $fillable = [
        'name',
        'code',
        'description',
        'address',
        'city',
        'postal_code',
        'phone',
        'manager',
        'is_active'
    ];

    /**
     * Get all inventory items at this location
     */
    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class, 'location_id');
    }

    /**
     * Get goods receipts for this location
     */
    public function goodsReceipts()
    {
        return $this->hasMany(GoodsReceipt::class, 'location_id');
    }

    /**
     * Get inventory transactions where this is the source location
     */
    public function sourceTransactions()
    {
        return $this->hasMany(InventoryTransaction::class, 'source_location_id');
    }

    /**
     * Get inventory transactions where this is the destination location
     */
    public function destinationTransactions()
    {
        return $this->hasMany(InventoryTransaction::class, 'destination_location_id');
    }

    /**
     * Get only active locations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Calculate total inventory value at this location
     */
    public function getTotalInventoryValueAttribute()
    {
        return $this->inventoryItems->sum(function ($item) {
            return $item->quantity_on_hand * ($item->unit_cost ?? $item->product->cost_price ?? 0);
        });
    }

    /**
     * Count number of unique products at this location
     */
    public function getUniqueProductCountAttribute()
    {
        return $this->inventoryItems()->count();
    }
}
