<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceSupply extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'maintenance_supplies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'part_number',
        'current_stock',
        'minimum_stock',
        'maximum_stock',
        'unit',
        'location',
        'unit_cost',
        'supplier',
        'supplier_contact',
        'lead_time_days',
        'storage_requirements',
        'handling_instructions',
        'expiration_date',
        'is_active',
        'category_id',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'current_stock' => 'decimal:2',
        'minimum_stock' => 'decimal:2',
        'maximum_stock' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'expiration_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the equipment that uses this supply.
     */
    public function equipment()
    {
        return $this->belongsToMany(MaintenanceEquipment::class, 'maintenance_equipment_supply', 'supply_id', 'equipment_id')
                   ->withPivot('quantity_required', 'notes')
                   ->withTimestamps();
    }

    /**
     * Get the category that owns the supply.
     */
    public function category()
    {
        return $this->belongsTo(MaintenanceCategory::class, 'category_id');
    }

    /**
     * Get the tasks that use this supply.
     */
    public function tasks()
    {
        return $this->belongsToMany(MaintenanceTask::class, 'maintenance_task_supply', 'supply_id', 'task_id')
            ->withPivot('quantity_used', 'unit_cost', 'notes')
            ->withTimestamps();
    }

    /**
     * Get the user who created this supply.
     */
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this supply.
     */
    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope a query to only include active supplies.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include supplies with low stock.
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('current_stock <= minimum_stock')
            ->where('is_active', true);
    }

    /**
     * Get the total value of the current stock.
     */
    public function getTotalValueAttribute()
    {
        return $this->current_stock * $this->unit_cost;
    }

    /**
     * Get the stock status as a bootstrap badge class.
     */
    public function getStockStatusBadgeClassAttribute()
    {
        if ($this->current_stock <= 0) {
            return 'danger';
        } elseif ($this->current_stock <= $this->minimum_stock) {
            return 'warning';
        } elseif ($this->maximum_stock && $this->current_stock >= $this->maximum_stock) {
            return 'info';
        } else {
            return 'success';
        }
    }

    /**
     * Get the stock status text.
     */
    public function getStockStatusTextAttribute()
    {
        if ($this->current_stock <= 0) {
            return 'Sem estoque';
        } elseif ($this->current_stock <= $this->minimum_stock) {
            return 'Estoque baixo';
        } elseif ($this->maximum_stock && $this->current_stock >= $this->maximum_stock) {
            return 'Estoque máximo';
        } else {
            return 'Estoque normal';
        }
    }
}
