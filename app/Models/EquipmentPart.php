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
        'maintenance_equipment_id'
    ];

    /**
     * Get the equipment that this part belongs to
     */
    public function equipment()
    {
        return $this->belongsTo(MaintenanceEquipment::class, 'maintenance_equipment_id');
    }
}
