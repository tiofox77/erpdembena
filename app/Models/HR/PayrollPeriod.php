<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'payment_date',
        'status',
        'remarks',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'payment_date' => 'date',
    ];

    /**
     * Status constants
     */
    const STATUS_OPEN = 'open';
    const STATUS_PROCESSING = 'processing';
    const STATUS_CLOSED = 'closed';

    /**
     * Get the payrolls for this period
     */
    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }
}
