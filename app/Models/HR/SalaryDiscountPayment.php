<?php

declare(strict_types=1);

namespace App\Models\HR;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryDiscountPayment extends Model
{
    use HasFactory;
    
    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'salary_discount_id',
        'amount',
        'payment_date',
        'installment_number',
        'processed_by',
        'notes',
    ];
    
    /**
     * Os atributos que devem ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];
    
    /**
     * Relacionamento com SalaryDiscount
     */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(SalaryDiscount::class, 'salary_discount_id');
    }
    
    /**
     * Relacionamento com User (processador)
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
