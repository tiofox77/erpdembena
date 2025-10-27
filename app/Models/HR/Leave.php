<?php

declare(strict_types=1);

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Leave extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'status',
        'approved_by',
        'approved_date',
        'rejection_reason',
        'attachment',
        'is_gender_specific',
        'gender_leave_type',
        'medical_certificate_details',
        'is_paid_leave',
        'payment_percentage',
        'payment_notes',
        'affects_payroll',
        'payroll_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_date' => 'date',
        'is_gender_specific' => 'boolean',
        'is_paid_leave' => 'boolean',
        'payment_percentage' => 'decimal:2',
        'affects_payroll' => 'boolean',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';
    
    /**
     * Gender leave type constants
     */
    const GENDER_LEAVE_MATERNITY = 'maternity';
    const GENDER_LEAVE_MENSTRUATION = 'menstruation';
    const GENDER_LEAVE_PRENATAL = 'prenatal';
    const GENDER_LEAVE_POSTNATAL = 'postnatal';
    const GENDER_LEAVE_BREASTFEEDING = 'breastfeeding';

    /**
     * Get the employee that the leave belongs to
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the leave type
     */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * Get the approver
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }
    
    /**
     * Get the payroll record for this leave
     */
    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }
    
    /**
     * Calculate the payment amount for this leave period
     */
    public function getPaymentAmountAttribute(): float
    {
        if (!$this->is_paid_leave || !$this->employee || $this->status !== self::STATUS_APPROVED) {
            return 0.0;
        }
        
        // Calcula o valor diário com base no salário base do funcionário
        $dailyRate = $this->employee->base_salary / 30; // Assume 30 dias por mês
        
        // Aplica a percentagem de pagamento definida para este tipo de licença
        $amount = $dailyRate * $this->total_days * ($this->payment_percentage / 100);
        
        return round($amount, 2);
    }
    
    /**
     * Determine if this leave is eligible for gender-specific benefits
     */
    public function getIsEligibleForGenderBenefitsAttribute(): bool
    {
        return $this->is_gender_specific && 
               $this->employee && 
               $this->employee->gender === 'female' && 
               $this->status === self::STATUS_APPROVED;
    }
    
    /**
     * Generate a summary of the leave for payroll processing
     */
    public function getPayrollSummaryAttribute(): array
    {
        return [
            'id' => $this->id,
            'employee_name' => $this->employee ? $this->employee->full_name : 'N/A',
            'employee_id' => $this->employee_id,
            'start_date' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date->format('Y-m-d'),
            'total_days' => $this->total_days,
            'type' => $this->leaveType ? $this->leaveType->name : 'N/A',
            'is_paid' => $this->is_paid_leave,
            'payment_percentage' => $this->payment_percentage,
            'payment_amount' => $this->payment_amount,
            'is_gender_specific' => $this->is_gender_specific,
            'gender_leave_type' => $this->gender_leave_type,
        ];
    }
}
