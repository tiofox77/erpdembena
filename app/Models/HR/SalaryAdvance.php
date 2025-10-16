<?php

declare(strict_types=1);

namespace App\Models\HR;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryAdvance extends Model
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
        'status',
        'approved_by',
        'approved_at',
        'notes',
        'signed_document',
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
     * Atributos calculados que devem ser anexados ao modelo.
     *
     * @var array<int, string>
     */
    protected $appends = ['remaining_amount'];
    
    /**
     * Calcula o valor restante baseado nas parcelas restantes.
     *
     * @return float
     */
    public function getRemainingAmountAttribute(): float
    {
        return $this->remaining_installments * $this->installment_amount;
    }
    
    /**
     * Obtém o funcionário associado a este adiantamento salarial.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
    
    /**
     * Obtém o utilizador que aprovou o adiantamento salarial.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    /**
     * Obtém o utilizador que criou o adiantamento salarial.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Obtém os pagamentos de parcelas deste adiantamento.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(SalaryAdvancePayment::class);
    }
    
    /**
     * Calcula automaticamente o valor da parcela baseado no montante total e número de parcelas.
     */
    public function calculateInstallmentAmount(): void
    {
        if ($this->amount > 0 && $this->installments > 0) {
            $this->installment_amount = $this->amount / $this->installments;
            $this->remaining_installments = $this->installments;
        }
    }
    
    /**
     * Registra um pagamento de parcela e atualiza o número de parcelas restantes.
     * 
     * @param float $amount Valor do pagamento
     * @param \DateTime|string $paymentDate Data do pagamento
     * @param int $installmentNumber Número da parcela (0 = pagamento completo)
     * @param int|null $processedBy ID do utilizador que processou o pagamento
     * @param string|null $notes Notas do pagamento
     * @return SalaryAdvancePayment
     */
    public function registerPayment(float $amount, $paymentDate, int $installmentNumber, ?int $processedBy = null, ?string $notes = null): SalaryAdvancePayment
    {
        $payment = $this->payments()->create([
            'payment_date' => $paymentDate,
            'amount' => $amount,
            'installment_number' => $installmentNumber,
            'processed_by' => $processedBy,
            'notes' => $notes,
        ]);
        
        // Se for pagamento completo (installment_number = 0), zera as parcelas restantes
        if ($installmentNumber === 0) {
            $this->remaining_installments = 0;
        } else {
            // Atualiza o número de parcelas restantes
            $this->remaining_installments = max(0, $this->remaining_installments - 1);
        }
        
        // Se não há mais parcelas, marca como completo
        if ($this->remaining_installments === 0) {
            $this->status = 'completed';
        }
        
        $this->save();
        
        return $payment;
    }
}
