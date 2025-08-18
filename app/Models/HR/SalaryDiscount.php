<?php

declare(strict_types=1);

namespace App\Models\HR;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryDiscount extends Model
{
    use HasFactory;
    
    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',
        'request_date',
        'amount',
        'installments',
        'installment_amount',
        'first_deduction_date',
        'remaining_installments',
        'reason',
        'discount_type', // 'union' or 'others'
        'status',
        'approved_by',
        'approved_at',
        'notes',
    ];
    
    /**
     * Os atributos que devem ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'request_date' => 'date',
        'first_deduction_date' => 'date',
        'approved_at' => 'date',
        'amount' => 'decimal:2',
        'installment_amount' => 'decimal:2',
    ];
    
    /**
     * Atributos que devem ser anexados ao modelo.
     *
     * @var array<string>
     */
    protected $appends = [
        'remaining_amount',
    ];
    
    /**
     * Constantes para tipos de desconto
     */
    public const TYPE_UNION = 'union';
    public const TYPE_OTHERS = 'others';
    public const TYPE_QUIXIQUILA = 'quixiquila';
    
    /**
     * Constantes para status
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_COMPLETED = 'completed';
    
    /**
     * Relacionamento com Employee
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
    
    /**
     * Relacionamento com User (aprovador)
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    /**
     * Relacionamento com pagamentos
     */
    public function payments(): HasMany
    {
        return $this->hasMany(SalaryDiscountPayment::class);
    }
    
    /**
     * Calcula o valor restante baseado nas parcelas restantes
     */
    public function getRemainingAmountAttribute(): float
    {
        if ($this->remaining_installments <= 0) {
            return 0.0;
        }
        
        return $this->remaining_installments * $this->installment_amount;
    }
    
    /**
     * Registra um pagamento de parcela
     */
    public function registerPayment(
        float $amount,
        string $payment_date,
        int $installment_number,
        int $processed_by,
        ?string $notes = null
    ): SalaryDiscountPayment {
        $payment = $this->payments()->create([
            'amount' => $amount,
            'payment_date' => $payment_date,
            'installment_number' => $installment_number,
            'processed_by' => $processed_by,
            'notes' => $notes,
        ]);
        
        // Se for pagamento completo (installment_number = 0), zera as parcelas restantes
        if ($installment_number === 0) {
            $this->remaining_installments = 0;
        } else {
            // Atualiza o número de parcelas restantes
            $this->remaining_installments = max(0, $this->remaining_installments - 1);
        }
        
        // Se não há mais parcelas restantes, marca como completo
        if ($this->remaining_installments <= 0) {
            $this->status = self::STATUS_COMPLETED;
        }
        
        $this->save();
        
        return $payment;
    }
    
    /**
     * Verifica se o desconto pode ser aprovado
     */
    public function canBeApproved(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
    
    /**
     * Verifica se o desconto pode ser rejeitado
     */
    public function canBeRejected(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_APPROVED], true);
    }
    
    /**
     * Scope para filtrar por tipo de desconto
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('discount_type', $type);
    }
    
    /**
     * Scope para filtrar por status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
    
    /**
     * Obtém o nome amigável do tipo de desconto
     */
    public function getDiscountTypeNameAttribute(): string
    {
        return match ($this->discount_type) {
            self::TYPE_UNION => __('messages.union_discount'),
            self::TYPE_OTHERS => __('messages.other_discount'),
            self::TYPE_QUIXIQUILA => 'Quixiquila',
            default => $this->discount_type,
        };
    }
    
    /**
     * Obtém o nome amigável do status
     */
    public function getStatusNameAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => __('messages.pending'),
            self::STATUS_APPROVED => __('messages.approved'),
            self::STATUS_REJECTED => __('messages.rejected'),
            self::STATUS_COMPLETED => __('messages.completed'),
            default => $this->status,
        };
    }
}
