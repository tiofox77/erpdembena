<?php

declare(strict_types=1);

namespace App\Models\HR;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DisciplinaryMeasure extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'disciplinary_measures';

    protected $fillable = [
        'employee_id',
        'measure_type',
        'reason',
        'description',
        'applied_date',
        'effective_date',
        'status',
        'notes',
        'attachments',
        'applied_by',
    ];

    protected $casts = [
        'applied_date' => 'date',
        'effective_date' => 'date',
        'attachments' => 'array',
    ];

    // Measure types
    public const TYPE_VERBAL_WARNING = 'verbal_warning';
    public const TYPE_WRITTEN_WARNING = 'written_warning';
    public const TYPE_SUSPENSION = 'suspension';
    public const TYPE_TERMINATION = 'termination';
    public const TYPE_FINE = 'fine';
    public const TYPE_OTHER = 'other';

    // Status
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function appliedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by');
    }

    public function getMeasureTypeNameAttribute(): string
    {
        return match($this->measure_type) {
            self::TYPE_VERBAL_WARNING => 'Advertência Verbal',
            self::TYPE_WRITTEN_WARNING => 'Advertência Escrita',
            self::TYPE_SUSPENSION => 'Suspensão',
            self::TYPE_TERMINATION => 'Demissão',
            self::TYPE_FINE => 'Multa',
            self::TYPE_OTHER => 'Outro',
            default => 'Desconhecido',
        };
    }

    public function getStatusNameAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'Activo',
            self::STATUS_COMPLETED => 'Concluído',
            self::STATUS_CANCELLED => 'Cancelado',
            default => 'Desconhecido',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'text-orange-600 bg-orange-100',
            self::STATUS_COMPLETED => 'text-green-600 bg-green-100',
            self::STATUS_CANCELLED => 'text-red-600 bg-red-100',
            default => 'text-gray-600 bg-gray-100',
        };
    }

    public function getMeasureTypeColorAttribute(): string
    {
        return match($this->measure_type) {
            self::TYPE_VERBAL_WARNING => 'text-yellow-600 bg-yellow-100',
            self::TYPE_WRITTEN_WARNING => 'text-orange-600 bg-orange-100',
            self::TYPE_SUSPENSION => 'text-red-600 bg-red-100',
            self::TYPE_TERMINATION => 'text-gray-900 bg-gray-100',
            self::TYPE_FINE => 'text-purple-600 bg-purple-100',
            self::TYPE_OTHER => 'text-blue-600 bg-blue-100',
            default => 'text-gray-600 bg-gray-100',
        };
    }
}
