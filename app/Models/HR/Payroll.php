<?php

declare(strict_types=1);

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'payroll_period_id',
        'basic_salary',
        'allowances',
        'overtime',
        'bonuses',
        'deductions',
        'tax',
        'social_security',
        'net_salary',
        'payment_method',
        'bank_account',
        'payment_date',
        'status',
        'remarks',
        'generated_by',
        'approved_by',
        'attendance_hours',
        'leave_days',
        'maternity_days',
        'special_leave_days',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'basic_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'overtime' => 'decimal:2',
        'bonuses' => 'decimal:2',
        'deductions' => 'decimal:2',
        'tax' => 'decimal:2',
        'social_security' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'attendance_hours' => 'decimal:2',
        'leave_days' => 'decimal:2',
        'maternity_days' => 'decimal:2',
        'special_leave_days' => 'decimal:2',
    ];

    /**
     * Status constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_APPROVED = 'approved';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the employee that the payroll belongs to
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the payroll period
     */
    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    /**
     * Get the payroll items
     */
    public function payrollItems(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    /**
     * Get the generator
     */
    public function generator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'generated_by');
    }

    /**
     * Get the approver
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    /**
     * Get the attendance records for this payroll
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the leave records for this payroll
     */
    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }
    
    /**
     * Calcular pagamentos relacionados a presença
     */
    public function calculateAttendancePayments(): float
    {
        $regularPay = $this->attendances()
            ->whereNull('overtime_hours')
            ->get()
            ->sum(function ($attendance) {
                return $attendance->calculateNormalPay();
            });

        $overtimePay = $this->attendances()
            ->whereNotNull('overtime_hours')
            ->get()
            ->sum(function ($attendance) {
                return $attendance->calculateOvertimePay();
            });

        return $regularPay + $overtimePay;
    }

    /**
     * Calcular deduções relacionadas a licenças
     */
    public function calculateLeaveDeductions(): float
    {
        return $this->leaves()
            ->where('is_paid_leave', false)
            ->get()
            ->sum(function ($leave) {
                return $leave->calculatePaymentAmount();
            });
    }

    /**
     * Processar os registos de presença e licença para esta folha de pagamento
     */
    public function processAttendanceAndLeave(): void
    {
        $period = $this->payrollPeriod;
        $employee = $this->employee;

        if (!$period || !$employee) {
            return;
        }

        $startDate = Carbon::parse($period->start_date);
        $endDate = Carbon::parse($period->end_date);

        // Associar registos de presença a esta folha de pagamento
        Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('affects_payroll', true)
            ->update(['payroll_id' => $this->id]);

        // Associar registos de licença a esta folha de pagamento
        Leave::where('employee_id', $employee->id)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate]);
            })
            ->where('affects_payroll', true)
            ->update(['payroll_id' => $this->id]);

        // Calcular horas trabalhadas
        $this->attendance_hours = $this->attendances()->sum('hours_worked');
        
        // Calcular dias de licença
        $this->leave_days = $this->leaves()->sum(function ($leave) {
            return $leave->end_date->diffInDays($leave->start_date) + 1;
        });
        
        // Calcular dias de maternidade
        $this->maternity_days = $this->leaves()
            ->where('is_gender_specific', true)
            ->where('gender_leave_type', Leave::GENDER_LEAVE_TYPE_MATERNITY)
            ->sum(function ($leave) {
                return $leave->end_date->diffInDays($leave->start_date) + 1;
            });

        // Calcular dias de licença especial
        $this->special_leave_days = $this->leaves()
            ->where('is_gender_specific', true)
            ->whereNot('gender_leave_type', Leave::GENDER_LEAVE_TYPE_MATERNITY)
            ->sum(function ($leave) {
                return $leave->end_date->diffInDays($leave->start_date) + 1;
            });

        $this->save();
    }
}
