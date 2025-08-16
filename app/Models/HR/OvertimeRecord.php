<?php

declare(strict_types=1);

namespace App\Models\HR;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OvertimeRecord extends Model
{
    use HasFactory;
    
    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',
        'date',
        'start_time',
        'end_time',
        'hours',
        'rate',
        'amount',
        'description',
        'status',
        'approved_by',
        'approved_at',
        'notes',
        'input_type',
        'period_type',
        'is_night_shift',
    ];
    
    /**
     * Os atributos que devem ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'approved_at' => 'date',
        'hours' => 'decimal:2',
        'rate' => 'decimal:2',
        'amount' => 'decimal:2',
    ];
    
    /**
     * Obtém o funcionário associado a este registo de hora extra.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
    
    /**
     * Obtém o utilizador que aprovou a hora extra.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    /**
     * Calcula automaticamente as horas e o valor total.
     */
    public function calculateHoursAndAmount(): void
    {
        if ($this->start_time && $this->end_time) {
            $start = new \DateTime($this->start_time);
            $end = new \DateTime($this->end_time);
            
            // Calcula a diferença em horas
            $interval = $start->diff($end);
            $hours = $interval->h + ($interval->i / 60);
            
            $this->hours = $hours;
            $this->amount = $hours * $this->rate;
        }
    }
}
