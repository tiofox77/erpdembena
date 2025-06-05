<?php

namespace App\Models\SupplyChain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Inventory extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sc_inventory_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
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
        'unit_cost',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'last_count_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Get the product associated with the inventory.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the location associated with the inventory.
     */
    public function location()
    {
        return $this->belongsTo(InventoryLocation::class, 'location_id');
    }

    /**
     * Get the user who created the inventory record.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the inventory record.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Check if the inventory item is considered low stock.
     *
     * @return bool
     */
    public function isLowStock()
    {
        return $this->quantity <= $this->min_stock_level && $this->quantity > 0;
    }

    /**
     * Check if the inventory item is out of stock.
     *
     * @return bool
     */
    public function isOutOfStock()
    {
        return $this->quantity <= 0;
    }

    /**
     * Get the total value of the inventory item.
     *
     * @return float
     */
    public function getTotalValue()
    {
        return $this->quantity * ($this->product->unit_price ?? 0);
    }

    /**
     * Scope a query to only include active inventory items.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include low stock inventory items.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'min_stock_level')
                     ->where('quantity', '>', 0);
    }

    /**
     * Scope a query to only include out of stock inventory items.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', '<=', 0);
    }
}
