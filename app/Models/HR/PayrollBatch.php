<?php

declare(strict_types=1);

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Carbon\Carbon;
use App\Models\HR\PayrollPeriod;
use App\Models\HR\Department;
use App\Models\HR\Payroll;

class PayrollBatch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'payroll_period_id',
        'department_id',
        'status',
        'total_employees',
        'processed_employees',
        'total_gross_amount',
        'total_net_amount',
        'total_deductions',
        'batch_date',
        'processing_started_at',
        'processing_completed_at',
        'created_by',
        'approved_by',
        'payment_method',
        'bank_transfer_reference',
        'notes'
    ];

    protected $casts = [
        'batch_date' => 'date',
        'processing_started_at' => 'datetime',
        'processing_completed_at' => 'datetime',
        'total_gross_amount' => 'decimal:2',
        'total_net_amount' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'total_employees' => 'integer',
        'processed_employees' => 'integer',
    ];

    protected $dates = [
        'batch_date',
        'processing_started_at', 
        'processing_completed_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Status constants
    public const STATUS_DRAFT = 'draft';
    public const STATUS_READY_TO_PROCESS = 'ready_to_process';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PAID = 'paid';

    // Payment method constants
    public const PAYMENT_CASH = 'cash';
    public const PAYMENT_BANK_TRANSFER = 'bank_transfer';
    public const PAYMENT_CHECK = 'check';

    /**
     * Get the payroll period that owns the batch
     */
    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    /**
     * Get the department that owns the batch
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the user who created the batch
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved the batch
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the payroll records for this batch
     */
    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class, 'payroll_batch_id');
    }

    /**
     * Get the batch items (employees included in the batch)
     */
    public function batchItems(): HasMany
    {
        return $this->hasMany(PayrollBatchItem::class);
    }

    /**
     * Scope to get batches by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get batches for a specific period
     */
    public function scopeForPeriod($query, int $periodId)
    {
        return $query->where('payroll_period_id', $periodId);
    }

    /**
     * Scope to get batches for a specific department
     */
    public function scopeForDepartment($query, int $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Check if batch is editable
     */
    public function isEditable(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_READY_TO_PROCESS]);
    }

    /**
     * Check if batch can be processed
     */
    public function canBeProcessed(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_READY_TO_PROCESS]) && $this->total_employees > 0;
    }

    /**
     * Check if batch is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if batch can be approved
     */
    public function canBeApproved(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Get status label in Portuguese
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            self::STATUS_DRAFT => 'Rascunho',
            self::STATUS_READY_TO_PROCESS => 'Pronto para Processar',
            self::STATUS_PROCESSING => 'Processando',
            self::STATUS_COMPLETED => 'Concluído',
            self::STATUS_FAILED => 'Falhado',
            self::STATUS_APPROVED => 'Aprovado',
            self::STATUS_PAID => 'Pago',
        ];

        return $labels[$this->status] ?? 'Desconhecido';
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        $colors = [
            self::STATUS_DRAFT => 'gray',
            self::STATUS_READY_TO_PROCESS => 'blue',
            self::STATUS_PROCESSING => 'yellow',
            self::STATUS_COMPLETED => 'green',
            self::STATUS_FAILED => 'red',
            self::STATUS_APPROVED => 'purple',
            self::STATUS_PAID => 'emerald',
        ];

        return $colors[$this->status] ?? 'gray';
    }

    /**
     * Get processing progress percentage
     */
    public function getProgressPercentageAttribute(): float
    {
        if ($this->total_employees === 0) {
            return 0;
        }

        return round(($this->processed_employees / $this->total_employees) * 100, 2);
    }

    /**
     * Get processing duration in minutes
     */
    public function getProcessingDurationAttribute(): ?int
    {
        if (!$this->processing_started_at) {
            return null;
        }

        $endTime = $this->processing_completed_at ?? now();
        return $this->processing_started_at->diffInMinutes($endTime);
    }

    /**
     * Get formatted batch date
     */
    public function getFormattedBatchDateAttribute(): string
    {
        return $this->batch_date ? $this->batch_date->format('d/m/Y') : '';
    }

    /**
     * Get all available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Rascunho',
            self::STATUS_READY_TO_PROCESS => 'Pronto para Processar',
            self::STATUS_PROCESSING => 'Processando',
            self::STATUS_COMPLETED => 'Concluído',
            self::STATUS_FAILED => 'Falhado',
            self::STATUS_APPROVED => 'Aprovado',
            self::STATUS_PAID => 'Pago',
        ];
    }

    /**
     * Get all payment methods
     */
    public static function getPaymentMethods(): array
    {
        return [
            self::PAYMENT_CASH => 'Dinheiro',
            self::PAYMENT_BANK_TRANSFER => 'Transferência Bancária',
            self::PAYMENT_CHECK => 'Cheque',
        ];
    }
}
