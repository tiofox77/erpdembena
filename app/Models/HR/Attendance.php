<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'time_in',
        'time_out',
        'status',
        'remarks',
        'is_approved',
        'approved_by',
    ];

    protected $casts = [
        'date' => 'date',
        'time_in' => 'datetime',
        'time_out' => 'datetime',
        'is_approved' => 'boolean',
    ];

    /**
     * Status constants
     */
    const STATUS_PRESENT = 'present';
    const STATUS_ABSENT = 'absent';
    const STATUS_LATE = 'late';
    const STATUS_HALFDAY = 'half_day';
    const STATUS_LEAVE = 'leave';

    /**
     * Get the employee that the attendance belongs to
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the approver
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    /**
     * Calculate total hours worked
     */
    public function getHoursWorkedAttribute()
    {
        if ($this->time_in && $this->time_out) {
            return $this->time_out->diffInHours($this->time_in);
        }
        return 0;
    }
}
