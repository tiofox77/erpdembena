<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentMaintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipment_id',
        'maintenance_type',
        'maintenance_date',
        'cost',
        'performed_by',
        'status',
        'description',
        'next_maintenance_date',
    ];

    protected $casts = [
        'maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'cost' => 'decimal:2',
    ];

    /**
     * Status constants
     */
    const STATUS_PLANNED = 'planned';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Maintenance type constants
     */
    const TYPE_PREVENTIVE = 'preventive';
    const TYPE_CORRECTIVE = 'corrective';
    const TYPE_UPGRADE = 'upgrade';
    const TYPE_INSPECTION = 'inspection';

    /**
     * Get the equipment that the maintenance is for
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /**
     * Get the employee who performed the maintenance
     */
    public function performer(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'performed_by');
    }
}
