<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeEquipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'equipment_id',
        'issue_date',
        'return_date',
        'condition_on_issue',
        'condition_on_return',
        'issued_by',
        'received_by',
        'status',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'return_date' => 'date',
    ];

    /**
     * Status constants
     */
    const STATUS_ISSUED = 'issued';
    const STATUS_RETURNED = 'returned';
    const STATUS_DAMAGED = 'damaged';
    const STATUS_LOST = 'lost';

    /**
     * Get the employee that the equipment is assigned to
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the equipment
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /**
     * Get the issuer
     */
    public function issuer(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'issued_by');
    }

    /**
     * Get the receiver
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'received_by');
    }
}
