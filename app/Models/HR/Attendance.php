<?php

declare(strict_types=1);

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
        'hourly_rate',
        'affects_payroll',
    ];

    protected $casts = [
        'date' => 'date',
        'time_in' => 'datetime',
        'time_out' => 'datetime',
        'hourly_rate' => 'decimal:2',
        'affects_payroll' => 'boolean',
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
     * Maternity type constants
     */
    const MATERNITY_PRENATAL = 'prenatal';
    const MATERNITY_POSTNATAL = 'postnatal';
    const MATERNITY_BREASTFEEDING = 'breastfeeding';

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
    public function getHoursWorkedAttribute(): float
    {
        if ($this->time_in && $this->time_out) {
            return (float) $this->time_out->diffInHours($this->time_in);
        }
        return 0.0;
    }
    
    /**
     * Get the payroll record for this attendance
     */
    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }
    
    /**
     * Calculate normal pay amount based on hours worked and hourly rate
     */
    public function getNormalPayAttribute(): float
    {
        if ($this->status === self::STATUS_ABSENT) {
            return 0.0;
        }
        
        if ($this->status === self::STATUS_HALFDAY) {
            return $this->hourly_rate * ($this->hours_worked / 2);
        }
        
        return $this->hourly_rate * $this->hours_worked;
    }
    
    /**
     * Calculate overtime pay amount based on overtime hours and overtime rate
     */
    public function getOvertimePayAttribute(): float
    {
        if (!$this->overtime_hours || !$this->overtime_rate) {
            return 0.0;
        }
        
        return $this->overtime_hours * $this->overtime_rate;
    }
    
    /**
     * Calculate total pay (normal + overtime)
     */
    public function getTotalPayAttribute(): float
    {
        return $this->normal_pay + $this->overtime_pay;
    }
    
    /**
     * Determine if this attendance is eligible for maternity benefits
     */
    public function getIsEligibleForMaternityBenefitsAttribute(): bool
    {
        return $this->is_maternity_related && 
               $this->employee && 
               $this->employee->gender === 'female' && 
               $this->is_approved;
    }
}
