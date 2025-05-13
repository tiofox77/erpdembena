<?php

namespace App\Models\Mrp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\SupplyChain\Product;
use App\Models\SupplyChain\InventoryLocation as Location;
use App\Models\Mrp\Line;
use App\Models\Mrp\Shift;
use Carbon\Carbon;

class ProductionSchedule extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'mrp_production_schedules';

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'schedule_number',
        'start_date',
        'start_time',
        'end_date',
        'end_time',
        'planned_quantity',
        'actual_quantity',
        'actual_start_time',
        'actual_end_time',
        'is_delayed',
        'delay_reason',
        'status',
        'priority',
        'responsible',
        'location_id',
        'working_hours_per_day',
        'hourly_production_rate',
        'working_days',
        'setup_time',
        'cleanup_time',
        'notes',
        'created_by',
        'updated_by',
    ];

    /**
     * Os atributos que devem ser convertidos.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'planned_quantity' => 'decimal:3',
        'actual_quantity' => 'decimal:3',
        'actual_start_time' => 'datetime',
        'actual_end_time' => 'datetime',
        'is_delayed' => 'boolean',
        'working_hours_per_day' => 'decimal:2',
        'hourly_production_rate' => 'decimal:2',
        'working_days' => 'array',
        'setup_time' => 'integer',
        'cleanup_time' => 'integer',
    ];

    /**
     * Get the product that is being scheduled.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the production orders for this schedule.
     */
    public function productionOrders()
    {
        return $this->hasMany(ProductionOrder::class, 'schedule_id');
    }

    /**
     * Get the location for this production schedule.
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the user that created the production schedule.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the production schedule.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    
    /**
     * Get the production line for this schedule.
     */
    public function line()
    {
        return $this->belongsTo(Line::class);
    }
    
    /**
     * Get the shift for this production schedule.
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
    
    /**
     * Get the daily production plans for this schedule.
     */
    public function dailyPlans()
    {
        return $this->hasMany(ProductionDailyPlan::class, 'schedule_id');
    }
    
    /**
     * Get percentage of completion based on actual vs planned quantity
     */
    public function getCompletionPercentageAttribute()
    {
        if ($this->planned_quantity <= 0) return 0;
        return min(round(($this->actual_quantity / $this->planned_quantity) * 100, 2), 100);
    }
    
    /**
     * Calcula a estimativa de tempo restante para conclusão da produção
     * com base na taxa de produção linear e tempo decorrido
     */
    public function getEstimatedTimeRemainingAttribute()
    {
        // Se já completou ou não está em progresso, retorna zero
        if ($this->status === 'completed' || $this->status !== 'in_progress' || !$this->actual_start_time) {
            return [
                'hours' => 0,
                'minutes' => 0,
                'percentage' => 100,
                'is_delayed' => false,
                'expected_production' => 0,
                'actual_production' => 0
            ];
        }
        
        // Configuração de datas e horas
        $startDateTime = Carbon::parse($this->getRawOriginal('start_date'))->setTimeFromTimeString($this->start_time);
        $endDateTime = Carbon::parse($this->getRawOriginal('end_date'))->setTimeFromTimeString($this->end_time);
        $now = Carbon::now();
        
        // Verificar se está atrasado
        $isDelayed = $now->gt($endDateTime);
        
        // Tempo total planejado em segundos
        $totalPlannedTime = $startDateTime->diffInSeconds($endDateTime);
        
        // Tempo decorrido desde o início real até agora (em segundos)
        $actualStartTime = Carbon::parse($this->actual_start_time);
        $elapsedTime = $actualStartTime->diffInSeconds($now);
        
        // Calcula a produção esperada para o momento atual (cálculo linear)
        $expectedProduction = 0;
        if ($totalPlannedTime > 0) {
            $timeProgressPercentage = min(($elapsedTime / $totalPlannedTime) * 100, 100);
            $expectedProduction = ($this->planned_quantity * $timeProgressPercentage) / 100;
        }
        
        // Obtém a produção real acumulada até agora
        $actualProduction = $this->dailyPlans->sum('actual_quantity') ?: 0;
        if ($actualProduction == 0) {
            $actualProduction = $this->actual_quantity ?: 0;
        }
        
        // Se toda a produção planejada já foi concluída
        if ($actualProduction >= $this->planned_quantity) {
            return [
                'hours' => 0,
                'minutes' => 0,
                'percentage' => 100,
                'is_delayed' => $isDelayed,
                'expected_production' => round($expectedProduction, 2),
                'actual_production' => $actualProduction
            ];
        }
        
        // Cálculo do tempo restante baseado na taxa real de produção
        $remainingSeconds = 0;
        
        // Calcula a taxa de produção real (unidades por segundo)
        if ($elapsedTime > 0 && $actualProduction > 0) {
            $actualProductionRate = $actualProduction / $elapsedTime;
            
            // Quanto ainda falta produzir
            $remainingProduction = $this->planned_quantity - $actualProduction;
            
            // Quanto tempo vai demorar com a taxa atual
            $remainingSeconds = ($actualProductionRate > 0) ? $remainingProduction / $actualProductionRate : 0;
        } else {
            // Se não tiver produção real ou tempo decorrido ainda, usa a taxa planejada
            $plannedProductionRate = $totalPlannedTime > 0 ? $this->planned_quantity / $totalPlannedTime : 0;
            $remainingProduction = $this->planned_quantity - $actualProduction;
            $remainingSeconds = ($plannedProductionRate > 0) ? $remainingProduction / $plannedProductionRate : 0;
        }
        
        // Converter segundos restantes em horas e minutos
        $remainingHours = floor($remainingSeconds / 3600);
        $remainingMinutes = floor(($remainingSeconds % 3600) / 60);
        
        // Calcular a porcentagem do tempo que já passou
        $percentageElapsed = 0;
        if ($totalPlannedTime > 0) {
            $percentageElapsed = min(round(($elapsedTime / $totalPlannedTime) * 100), 100);
        }
        
        return [
            'hours' => $remainingHours,
            'minutes' => $remainingMinutes,
            'percentage' => $percentageElapsed,
            'is_delayed' => $isDelayed,
            'expected_production' => round($expectedProduction, 2),
            'actual_production' => $actualProduction
        ];
    }
    
    /**
     * Distribute the planned quantity across days
     */
    public function distributePlannedQuantity()
    {
        // Delete existing daily plans first
        $this->dailyPlans()->delete();
        
        // Calculate number of days between start and end dates
        $startDate = new \DateTime($this->start_date);
        $endDate = new \DateTime($this->end_date);
        $interval = $startDate->diff($endDate);
        $totalDays = $interval->days + 1; // Include both start and end days
        
        if ($totalDays <= 0) return;
        
        // Inicializar dias de trabalho se não definidos
        $workingDays = $this->working_days ?? [
            'mon' => true,
            'tue' => true,
            'wed' => true,
            'thu' => true,
            'fri' => true,
            'sat' => false,
            'sun' => false
        ];
        
        // Mapear dias da semana para números (0=domingo, 1=segunda, etc)
        $dayMapping = [
            'sun' => 0,
            'mon' => 1,
            'tue' => 2,
            'wed' => 3,
            'thu' => 4,
            'fri' => 5,
            'sat' => 6
        ];
        
        // Inverter o mapeamento para facilitar a verificação
        $dayNameMapping = [0 => 'sun', 1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat'];
        
        // Contar dias úteis para produção
        $currentDate = clone $startDate;
        $workingDaysCount = 0;
        $workingDatesArray = [];
        
        for ($i = 0; $i < $totalDays; $i++) {
            $dayOfWeek = (int)$currentDate->format('w'); // 0=domingo, 6=sábado
            $dayName = $dayNameMapping[$dayOfWeek];
            
            if (isset($workingDays[$dayName]) && $workingDays[$dayName]) {
                $workingDaysCount++;
                $workingDatesArray[] = $currentDate->format('Y-m-d');
            }
            
            $currentDate->modify('+1 day');
        }
        
        // Se não houver dias úteis, usar todos os dias
        if ($workingDaysCount === 0) {
            $workingDaysCount = $totalDays;
            $currentDate = clone $startDate;
            $workingDatesArray = [];
            
            for ($i = 0; $i < $totalDays; $i++) {
                $workingDatesArray[] = $currentDate->format('Y-m-d');
                $currentDate->modify('+1 day');
            }
        }
        
        // Calcular capacidade diária baseada nas horas de trabalho e taxa de produção
        $hoursPerDay = $this->working_hours_per_day ?? 8;
        $hourlyRate = $this->hourly_production_rate ?? 0;
        
        // Se a taxa horária estiver definida, usar para calcular a capacidade diária
        $dailyCapacity = ($hourlyRate > 0) ? $hourlyRate * $hoursPerDay : 0;
        
        // Ajustar para tempo de setup e limpeza
        $setupTime = $this->setup_time ?? 30;
        $cleanupTime = $this->cleanup_time ?? 15;
        $totalNonProductiveMinutes = $setupTime + $cleanupTime;
        $productiveMinutesPerDay = ($hoursPerDay * 60) - $totalNonProductiveMinutes;
        
        // Redução proporcional na capacidade devido ao setup e limpeza
        if ($hoursPerDay > 0 && $hourlyRate > 0 && $productiveMinutesPerDay > 0) {
            $capacityReduction = $totalNonProductiveMinutes / ($hoursPerDay * 60);
            $dailyCapacity = $dailyCapacity * (1 - $capacityReduction);
        }
        
        // Verificar se a quantidade total é possível nos dias úteis disponíveis
        $totalCapacity = $dailyCapacity * $workingDaysCount;
        
        // Calcular a quantidade por dia
        $dailyQuantity = 0;
        
        if ($dailyCapacity > 0 && $totalCapacity >= $this->planned_quantity) {
            // Se a capacidade for suficiente, distribuir igualmente
            $dailyQuantity = $this->planned_quantity / $workingDaysCount;
        } elseif ($dailyCapacity > 0) {
            // Se a capacidade não for suficiente, usar a capacidade máxima diária
            $dailyQuantity = $dailyCapacity;
        } else {
            // Se não houver dados de capacidade, distribuir igualmente
            $dailyQuantity = $this->planned_quantity / $workingDaysCount;
        }
        
        // Criar planos diários apenas para os dias úteis
        $userId = auth()->id() ?? 1;
        
        foreach ($workingDatesArray as $index => $date) {
            // Para o último dia, ajustar a quantidade para garantir que o total seja correto
            $isLastDay = ($index == count($workingDatesArray) - 1);
            $quantityForDay = $dailyQuantity;
            
            if ($isLastDay) {
                $totalPlannedSoFar = $dailyQuantity * $index;
                $quantityForDay = $this->planned_quantity - $totalPlannedSoFar;
                
                // Garantir que não seja negativo
                if ($quantityForDay < 0) $quantityForDay = 0;
            }
            
            $this->dailyPlans()->create([
                'production_date' => $date,
                'start_time' => $this->start_time,
                'end_time' => $this->end_time,
                'planned_quantity' => $quantityForDay,
                'status' => 'pending',
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
        }
    }
}
