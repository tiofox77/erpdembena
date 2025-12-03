<?php

declare(strict_types=1);

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\HR\Employee;
use App\Models\HR\Payroll;

class PayrollBatchItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_batch_id',
        'employee_id',
        'payroll_id',
        'gross_salary',
        'net_salary',
        'total_deductions',
        'status',
        'processing_order',
        'processed_at',
        'error_message',
        'notes',
        // Editable subsidies
        'christmas_subsidy',
        'vacation_subsidy',
        'additional_bonus',
        // Calculated amounts
        'christmas_subsidy_amount',
        'vacation_subsidy_amount',
        // Detailed breakdown
        'basic_salary',
        'transport_allowance',
        'food_allowance',
        'family_allowance',
        'position_subsidy',
        'performance_subsidy',
        'overtime_amount',
        'night_shift_allowance',
        'night_shift_days',
        // Deductions
        'inss_deduction',
        'irt_deduction',
        'advance_deduction',
        'discount_deduction',
        'late_deduction',
        'absence_deduction',
        // Attendance
        'present_days',
        'absent_days',
        'late_days',
        'total_working_days',
    ];

    protected $casts = [
        'gross_salary' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'processing_order' => 'integer',
        'processed_at' => 'datetime',
        // Subsidies
        'christmas_subsidy' => 'boolean',
        'vacation_subsidy' => 'boolean',
        'additional_bonus' => 'decimal:2',
        'christmas_subsidy_amount' => 'decimal:2',
        'vacation_subsidy_amount' => 'decimal:2',
        // Breakdown
        'basic_salary' => 'decimal:2',
        'transport_allowance' => 'decimal:2',
        'food_allowance' => 'decimal:2',
        'family_allowance' => 'decimal:2',
        'position_subsidy' => 'decimal:2',
        'performance_subsidy' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'night_shift_allowance' => 'decimal:2',
        'night_shift_days' => 'integer',
        // Deductions
        'inss_deduction' => 'decimal:2',
        'irt_deduction' => 'decimal:2',
        'advance_deduction' => 'decimal:2',
        'discount_deduction' => 'decimal:2',
        'late_deduction' => 'decimal:2',
        'absence_deduction' => 'decimal:2',
        // Attendance
        'present_days' => 'integer',
        'absent_days' => 'integer',
        'late_days' => 'integer',
        'total_working_days' => 'integer',
    ];

    protected $dates = [
        'processed_at',
        'created_at',
        'updated_at'
    ];

    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SKIPPED = 'skipped';

    /**
     * Get the batch that owns the item
     */
    public function payrollBatch(): BelongsTo
    {
        return $this->belongsTo(PayrollBatch::class);
    }

    /**
     * Alias for payrollBatch relationship
     */
    public function batch(): BelongsTo
    {
        return $this->payrollBatch();
    }

    /**
     * Get the employee for this item
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the payroll record for this item
     */
    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    /**
     * Scope to get items by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get completed items
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope to get failed items
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Check if item is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if item failed
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Get status label in Portuguese
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            self::STATUS_PENDING => 'Pendente',
            self::STATUS_PROCESSING => 'Processando',
            self::STATUS_COMPLETED => 'Concluído',
            self::STATUS_FAILED => 'Falhado',
            self::STATUS_SKIPPED => 'Ignorado',
        ];

        return $labels[$this->status] ?? 'Desconhecido';
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        $colors = [
            self::STATUS_PENDING => 'gray',
            self::STATUS_PROCESSING => 'yellow',
            self::STATUS_COMPLETED => 'green',
            self::STATUS_FAILED => 'red',
            self::STATUS_SKIPPED => 'orange',
        ];

        return $colors[$this->status] ?? 'gray';
    }

    /**
     * Get all available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pendente',
            self::STATUS_PROCESSING => 'Processando',
            self::STATUS_COMPLETED => 'Concluído',
            self::STATUS_FAILED => 'Falhado',
            self::STATUS_SKIPPED => 'Ignorado',
        ];
    }
}
