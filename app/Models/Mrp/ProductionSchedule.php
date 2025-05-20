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
use App\Models\Mrp\ProductionScheduleShift;
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
        'line_id',
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
    
    // The shift() relationship was removed since it's replaced by the many-to-many shifts() relationship
    
    /**
     * Get the shifts for this production schedule.
     */
    public function shifts()
    {
        return $this->belongsToMany(Shift::class, 'mrp_production_schedule_shift', 'production_schedule_id', 'shift_id')
            ->withTimestamps()
            ->using(ProductionScheduleShift::class)
            ->withoutTrashed()->select('mrp_shifts.*'); // Especifica a tabela para evitar ambiguidade de colunas
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
        // Verificar se tem dados básicos necessários
        if (empty($this->start_date) || empty($this->end_date) || empty($this->planned_quantity)) {
            \Illuminate\Support\Facades\Log::warning('Dados básicos insuficientes para distribuir quantidade', [
                'schedule_id' => $this->id,
                'start_date' => $this->start_date ?? 'vazio',
                'end_date' => $this->end_date ?? 'vazio',
                'planned_quantity' => $this->planned_quantity ?? 'vazio'
            ]);
            return false;
        }
        
        try {
            \Illuminate\Support\Facades\Log::info('Iniciando distribuição de quantidade planejada', [
                'schedule_id' => $this->id,
                'schedule_number' => $this->schedule_number
            ]);
            
            // Se já existem planos diários, remover todos para criar novos
            $this->dailyPlans()->delete();
            
            // Mapear dias da semana para códigos numéricos
            $dayNameMapping = [
                0 => 'sunday',
                1 => 'monday',
                2 => 'tuesday',
                3 => 'wednesday',
                4 => 'thursday',
                5 => 'friday',
                6 => 'saturday'
            ];
            
            // Dias de trabalho definidos no agendamento
            $workingDays = $this->working_days ?? [
                'monday' => true,
                'tuesday' => true,
                'wednesday' => true,
                'thursday' => true,
                'friday' => true,
                'saturday' => false,
                'sunday' => false
            ];
            
            // Calcular o número total de dias entre as datas de início e fim
            $startDate = \Carbon\Carbon::parse($this->start_date);
            $endDate = \Carbon\Carbon::parse($this->end_date);
            $totalDays = $startDate->diffInDays($endDate) + 1; // +1 para incluir o último dia
            
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
                
                $currentDate->addDay();
            }
            
            // Se não houver dias úteis, usar todos os dias
            if ($workingDaysCount === 0) {
                $workingDaysCount = $totalDays;
                $currentDate = clone $startDate;
                $workingDatesArray = [];
                
                for ($i = 0; $i < $totalDays; $i++) {
                    $workingDatesArray[] = $currentDate->format('Y-m-d');
                    $currentDate->addDay();
                }
            }
            
            // Calcular capacidade diária baseada nas horas de trabalho e taxa de produção
            $hoursPerDay = $this->working_hours_per_day ?? 8;
            $hourlyRate = $this->hourly_production_rate ?? 0;
            
            $dailyCapacity = $hoursPerDay * $hourlyRate;
            
            // Se houver tempo de setup e limpeza, reduzir a capacidade diária
            $setupTime = $this->setup_time ?? 0; // Em minutos
            $cleanupTime = $this->cleanup_time ?? 0; // Em minutos
            
            if ($setupTime > 0 || $cleanupTime > 0) {
                $totalNonProductiveMinutes = $setupTime + $cleanupTime;
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
            
            // Obter ID do usuário atual
            $userId = auth()->id() ?? 1;
            
            // Obter os turnos associados a este agendamento
            $shifts = $this->shifts;
            
            // Verificar se há turnos definidos
            if ($shifts->isEmpty()) {
                \Illuminate\Support\Facades\Log::warning('Nenhum turno associado ao criar planos diários', [
                    'schedule_id' => $this->id,
                    'schedule_number' => $this->schedule_number
                ]);
            }
            
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
                
                // Se houver turnos associados, criar um plano para cada turno
                if ($shifts->isNotEmpty()) {
                    // Distribuir a quantidade planejada igualmente entre os turnos
                    $shiftsCount = $shifts->count();
                    $quantityPerShift = $quantityForDay / $shiftsCount;
                    
                    foreach ($shifts as $shift) {
                        $dailyPlan = new \App\Models\Mrp\ProductionDailyPlan([
                            'schedule_id' => $this->id,
                            'production_date' => $date,
                            'start_time' => $this->start_time,
                            'end_time' => $this->end_time,
                            'planned_quantity' => $quantityPerShift,
                            'status' => 'pending',
                            'created_by' => $userId,
                            'updated_by' => $userId,
                            'shift_id' => $shift->id
                        ]);
                        $dailyPlan->save();
                        
                        \Illuminate\Support\Facades\Log::info('Plano diário criado com turno', [
                            'plan_id' => $dailyPlan->id,
                            'date' => $date,
                            'shift_id' => $shift->id,
                            'quantity' => $quantityPerShift
                        ]);
                    }
                } else {
                    // Se não houver turnos, criar um plano diário sem associação de turno
                    $dailyPlan = new \App\Models\Mrp\ProductionDailyPlan([
                        'schedule_id' => $this->id,
                        'production_date' => $date,
                        'start_time' => $this->start_time,
                        'end_time' => $this->end_time,
                        'planned_quantity' => $quantityForDay,
                        'status' => 'pending',
                        'created_by' => $userId,
                        'updated_by' => $userId,
                        'shift_id' => null // Sem turno associado
                    ]);
                    $dailyPlan->save();
                    
                    \Illuminate\Support\Facades\Log::info('Plano diário criado sem turno', [
                        'plan_id' => $dailyPlan->id,
                        'date' => $date,
                        'quantity' => $quantityForDay
                    ]);
                }
            }
            
            // Registrar log de sucesso
            \Illuminate\Support\Facades\Log::info('Planos diários distribuídos com sucesso', [
                'schedule_id' => $this->id,
                'schedule_number' => $this->schedule_number,
                'shifts_count' => $shifts->count(),
                'working_days_count' => $workingDaysCount
            ]);
            
            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao distribuir quantidade planejada: ' . $e->getMessage(), [
                'schedule_id' => $this->id,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return false;
        }
    }
    
    /**
     * Calcula a distribuição da quantidade planejada entre dias e turnos sem salvar
     * Este método é usado para preparar dados antes de salvar e inclui logs detalhados
     * 
     * @param bool $debug Se true, gera logs adicionais de depuração
     * @return array Dados para criar planos diários de produção
     */
    public function calculatePlannedDistribution($debug = true)
    {
        try {
            // Iniciar logs de depuração
            if ($debug) {
                \Illuminate\Support\Facades\Log::channel('daily')->info('Iniciando cálculo de distribuição planejada', [
                    'schedule_id' => $this->id,
                    'schedule_number' => $this->schedule_number,
                    'planned_quantity' => $this->planned_quantity
                ]);
            }
            
            // Verificar se o modelo tem os dados necessários
            if (empty($this->start_date) || empty($this->end_date)) {
                $errorMsg = 'Datas de início ou fim não definidas para calcular distribuição';
                \Illuminate\Support\Facades\Log::error($errorMsg, [
                    'schedule_id' => $this->id,
                    'schedule_number' => $this->schedule_number
                ]);
                return ['error' => $errorMsg, 'plans' => []];
            }
            
            // Definir configurações básicas
            $startDate = \Carbon\Carbon::parse($this->start_date);
            $endDate = \Carbon\Carbon::parse($this->end_date);
            
            // Verificar se o período é válido
            if ($endDate->lt($startDate)) {
                $errorMsg = 'Data de fim anterior à data de início';
                \Illuminate\Support\Facades\Log::error($errorMsg, [
                    'schedule_id' => $this->id,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d')
                ]);
                return ['error' => $errorMsg, 'plans' => []];
            }
            
            // Definir dias de trabalho
            $workingDays = is_array($this->working_days) ? $this->working_days : json_decode($this->working_days, true);
            
            // Se não houver dias de trabalho definidos, usar dias úteis padrão (seg-sex)
            if (empty($workingDays)) {
                $workingDays = [1, 2, 3, 4, 5]; // Segunda a Sexta
                if ($debug) {
                    \Illuminate\Support\Facades\Log::warning('Dias de trabalho não definidos, usando padrão (seg-sex)', [
                        'schedule_id' => $this->id
                    ]);
                }
            }
            
            // Calcular dias úteis dentro do período
            $workingDatesArray = [];
            $currentDate = $startDate->copy();
            
            while ($currentDate->lte($endDate)) {
                // Verificar se o dia da semana atual está nos dias de trabalho (0=domingo, 6=sábado)
                $dayOfWeek = $currentDate->dayOfWeek;
                if (in_array($dayOfWeek, $workingDays)) {
                    $workingDatesArray[] = $currentDate->format('Y-m-d');
                }
                $currentDate->addDay();
            }
            
            $workingDaysCount = count($workingDatesArray);
            
            if ($workingDaysCount == 0) {
                $errorMsg = 'Nenhum dia útil encontrado no período selecionado';
                \Illuminate\Support\Facades\Log::error($errorMsg, [
                    'schedule_id' => $this->id,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'working_days' => $workingDays
                ]);
                return ['error' => $errorMsg, 'plans' => []];
            }
            
            if ($debug) {
                \Illuminate\Support\Facades\Log::info('Dias úteis encontrados', [
                    'schedule_id' => $this->id,
                    'working_days_count' => $workingDaysCount,
                    'first_day' => reset($workingDatesArray),
                    'last_day' => end($workingDatesArray)
                ]);
            }
            
            // Calcular capacidade diária com base em taxas de produção
            $hoursPerDay = $this->working_hours_per_day ?? 8; // Padrão: 8 horas por dia
            $hourlyRate = $this->hourly_production_rate ?? 0; // Unidades produzidas por hora
            
            $dailyCapacity = $hoursPerDay * $hourlyRate;
            
            // Se houver tempo de setup e limpeza, reduzir a capacidade diária
            $setupTime = $this->setup_time ?? 0; // Em minutos
            $cleanupTime = $this->cleanup_time ?? 0; // Em minutos
            
            if ($setupTime > 0 || $cleanupTime > 0) {
                $totalNonProductiveMinutes = $setupTime + $cleanupTime;
                $capacityReduction = $totalNonProductiveMinutes / ($hoursPerDay * 60);
                $dailyCapacity = $dailyCapacity * (1 - $capacityReduction);
                
                if ($debug) {
                    \Illuminate\Support\Facades\Log::info('Capacidade ajustada para setup/cleanup', [
                        'setup_time' => $setupTime, 
                        'cleanup_time' => $cleanupTime,
                        'daily_capacity_before' => $hoursPerDay * $hourlyRate,
                        'daily_capacity_after' => $dailyCapacity
                    ]);
                }
            }
            
            // Verificar se a quantidade total é possível nos dias úteis disponíveis
            $totalCapacity = $dailyCapacity * $workingDaysCount;
            
            // Calcular a quantidade por dia
            $dailyQuantity = 0;
            
            if ($dailyCapacity > 0 && $totalCapacity >= $this->planned_quantity) {
                // Se a capacidade for suficiente, distribuir igualmente
                $dailyQuantity = $this->planned_quantity / $workingDaysCount;
                if ($debug) {
                    \Illuminate\Support\Facades\Log::info('Capacidade suficiente para distribuição igual', [
                        'daily_quantity' => $dailyQuantity,
                        'total_capacity' => $totalCapacity,
                        'planned_quantity' => $this->planned_quantity
                    ]);
                }
            } elseif ($dailyCapacity > 0) {
                // Se a capacidade não for suficiente, usar a capacidade máxima diária
                $dailyQuantity = $dailyCapacity;
                \Illuminate\Support\Facades\Log::warning('Capacidade insuficiente para quantidade planejada', [
                    'daily_capacity' => $dailyCapacity,
                    'total_capacity' => $totalCapacity,
                    'planned_quantity' => $this->planned_quantity,
                    'scheduling_days_needed' => ceil($this->planned_quantity / $dailyCapacity)
                ]);
            } else {
                // Se não houver dados de capacidade, distribuir igualmente
                $dailyQuantity = $this->planned_quantity / $workingDaysCount;
                \Illuminate\Support\Facades\Log::warning('Sem dados de capacidade, distribuindo igualmente', [
                    'daily_quantity' => $dailyQuantity
                ]);
            }
            
            // Obter ID do usuário atual
            $userId = auth()->id() ?? 1;
            
            // Obter os turnos associados a este agendamento
            $shifts = $this->shifts;
            
            // Verificar se há turnos definidos
            if ($shifts->isEmpty()) {
                \Illuminate\Support\Facades\Log::warning('Nenhum turno associado ao calcular planos diários', [
                    'schedule_id' => $this->id,
                    'schedule_number' => $this->schedule_number
                ]);
            } else if ($debug) {
                \Illuminate\Support\Facades\Log::info('Turnos encontrados para distribuição', [
                    'schedule_id' => $this->id,
                    'shifts_count' => $shifts->count(),
                    'shift_ids' => $shifts->pluck('id')->toArray(),
                    'shift_names' => $shifts->pluck('name')->toArray()
                ]);
            }
            
            // Preparar array de planos diários
            $plans = [];
            
            foreach ($workingDatesArray as $index => $date) {
                // Para o último dia, ajustar a quantidade para garantir que o total seja correto
                $isLastDay = ($index == count($workingDatesArray) - 1);
                $quantityForDay = $dailyQuantity;
                
                if ($isLastDay) {
                    $totalPlannedSoFar = $dailyQuantity * $index;
                    $quantityForDay = $this->planned_quantity - $totalPlannedSoFar;
                    
                    // Garantir que não seja negativo
                    if ($quantityForDay < 0) {
                        $quantityForDay = 0;
                        if ($debug) {
                            \Illuminate\Support\Facades\Log::warning('Quantidade ajustada para zero no último dia', [
                                'day_index' => $index,
                                'date' => $date
                            ]);
                        }
                    }
                }
                
                // Se houver turnos associados, criar um plano para cada turno
                if ($shifts->isNotEmpty()) {
                    // Distribuir a quantidade planejada igualmente entre os turnos
                    $shiftsCount = $shifts->count();
                    $quantityPerShift = $quantityForDay / $shiftsCount;
                    
                    foreach ($shifts as $shift) {
                        $planData = [
                            'schedule_id' => $this->id,
                            'production_date' => $date,
                            'start_time' => $this->start_time,
                            'end_time' => $this->end_time,
                            'planned_quantity' => $quantityPerShift,
                            'status' => 'pending',
                            'created_by' => $userId,
                            'updated_by' => $userId,
                            'shift_id' => $shift->id
                        ];
                        $plans[] = $planData;
                        
                        if ($debug && $index === 0) { // Logar apenas para o primeiro dia como exemplo
                            \Illuminate\Support\Facades\Log::debug('Dados de plano diário com turno', $planData);
                        }
                    }
                } else {
                    // Se não houver turnos, criar um plano diário sem associação de turno
                    $planData = [
                        'schedule_id' => $this->id,
                        'production_date' => $date,
                        'start_time' => $this->start_time,
                        'end_time' => $this->end_time,
                        'planned_quantity' => $quantityForDay,
                        'status' => 'pending',
                        'created_by' => $userId,
                        'updated_by' => $userId,
                        'shift_id' => null // Sem turno associado
                    ];
                    $plans[] = $planData;
                    
                    if ($debug && $index === 0) { // Logar apenas para o primeiro dia como exemplo
                        \Illuminate\Support\Facades\Log::debug('Dados de plano diário sem turno', $planData);
                    }
                }
            }
            
            // Registrar sucesso no log
            if ($debug) {
                \Illuminate\Support\Facades\Log::info('Cálculo de distribuição concluído com sucesso', [
                    'schedule_id' => $this->id,
                    'schedule_number' => $this->schedule_number,
                    'total_plans' => count($plans),
                    'shifts_count' => $shifts->count(),
                    'working_days_count' => $workingDaysCount
                ]);
            }
            
            return [
                'success' => true,
                'plans' => $plans,
                'stats' => [
                    'working_days_count' => $workingDaysCount,
                    'shifts_count' => $shifts->count(),
                    'daily_capacity' => $dailyCapacity,
                    'total_capacity' => $totalCapacity,
                    'daily_quantity' => $dailyQuantity
                ]
            ];
            
        } catch (\Exception $e) {
            // Registrar erro no log
            $errorMsg = "Erro ao calcular distribuição: {$e->getMessage()} em {$e->getFile()}:{$e->getLine()}";
            \Illuminate\Support\Facades\Log::error($errorMsg, [
                'schedule_id' => $this->id,
                'schedule_number' => $this->schedule_number,
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Retornar erro para exibição na interface
            return ['error' => $errorMsg, 'exception' => get_class($e), 'plans' => []];
        }
    }
}
