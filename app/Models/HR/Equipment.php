<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'equipment_type',
        'serial_number',
        'asset_code',
        'brand',
        'model',
        'purchase_date',
        'purchase_cost',
        'warranty_expiry',
        'condition',
        'status',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'purchase_cost' => 'decimal:2',
    ];

    /**
     * Status constants
     */
    const STATUS_AVAILABLE = 'available';
    const STATUS_ASSIGNED = 'assigned';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_DAMAGED = 'damaged';
    const STATUS_DISPOSED = 'disposed';

    /**
     * Equipment type constants
     */
    const TYPE_COMPUTER = 'computer';
    const TYPE_PHONE = 'phone';
    const TYPE_TOOL = 'tool';
    const TYPE_VEHICLE = 'vehicle';
    const TYPE_OTHER = 'other';

    /**
     * Get the equipment assignments
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(EmployeeEquipment::class);
    }

    /**
     * Get the maintenance records
     */
    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(EquipmentMaintenance::class);
    }
}
