<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentPart extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'equipment_parts';

    protected $fillable = [
        'name',
        'part_number',
        'description',
        'stock_quantity',
        'unit_cost',
        'last_restock_date',
        'minimum_stock_level',
        'maintenance_equipment_id',
        'bar_code',
        'category'
    ];

    /**
     * Get the equipment that this part belongs to
     */
    public function equipment()
    {
        return $this->belongsTo(MaintenanceEquipment::class, 'maintenance_equipment_id');
    }

    /**
     * Get stock transactions associated with this part
     */
    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class, 'equipment_part_id');
    }

    /**
     * Get stock in transactions only
     */
    public function stockIns()
    {
        return $this->stockTransactions()->where('type', 'stock_in');
    }

    /**
     * Get stock out transactions only
     */
    public function stockOuts()
    {
        return $this->stockTransactions()->where('type', 'stock_out');
    }
}
