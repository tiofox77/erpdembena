<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceEquipment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'maintenance_equipment';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'serial_number',
        'line_id',
        'area_id',
        'status',
        'purchase_date',
        'last_maintenance',
        'next_maintenance',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */


    /**
     * Get the line that the equipment belongs to.
     */
    public function line()
    {
        return $this->belongsTo(MaintenanceLine::class, 'line_id');
    }

    /**
     * Get the area that the equipment belongs to.
     */
    public function area()
    {
        return $this->belongsTo(MaintenanceArea::class, 'area_id');
    }

    /**
     * Get the department that the equipment belongs to.
     * @deprecated Use area() instead
     */
    public function department()
    {
        return $this->belongsTo(MaintenanceArea::class, 'area_id');
    }

    /**
     * Get the tasks associated with the equipment.
     */
    public function tasks()
    {
        return $this->hasMany(MaintenanceTask::class, 'equipment_id');
    }

    /**
     * Get the maintenance plans associated with the equipment.
     */
    public function maintenancePlans()
    {
        return $this->hasMany(MaintenancePlan::class, 'equipment_id');
    }

    /**
     * Get the corrective maintenance records associated with the equipment.
     */
    public function correctives()
    {
        return $this->hasMany(MaintenanceCorrective::class, 'equipment_id');
    }

    /**
     * Get the files associated with the equipment.
     */

    /**
     * Get the parts associated with the equipment.
     */
    public function parts()
    {
        return $this->hasMany(EquipmentPart::class, 'maintenance_equipment_id');
    }


}
