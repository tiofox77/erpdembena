<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_id',
        'type',
        'description',
        'amount',
        'is_taxable',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_taxable' => 'boolean',
    ];

    /**
     * Item type constants
     */
    const TYPE_ALLOWANCE = 'allowance';
    const TYPE_BONUS = 'bonus';
    const TYPE_OVERTIME = 'overtime';
    const TYPE_DEDUCTION = 'deduction';
    const TYPE_TAX = 'tax';
    const TYPE_SOCIAL_SECURITY = 'social_security';

    /**
     * Get the payroll that the item belongs to
     */
    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }
}
