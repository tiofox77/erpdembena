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
use App\Models\Mrp\Responsible;
use App\Models\Mrp\ProductionScheduleShift;
use Carbon\Carbon;

class ProductionSchedule extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        // Quando uma programação é atualizada, registrar alterações mas NÃO recalcular planos diários automaticamente
        static::updated(function($schedule) {
            // Verificar se houve mudança em campos relevantes para o cálculo
            if ($schedule->wasChanged(['planned_quantity', 'working_hours_per_day', 'hourly_production_rate',
                                      'setup_time', 'cleanup_time', 'start_date', 'end_date', 'working_days'])) {
                \Illuminate\Support\Facades\Log::info('Detectada alteração em campos relevantes para distribuição', [
                    'schedule_id' => $schedule->id,
                    'schedule_number' => $schedule->schedule_number,
                    'campos_alterados' => $schedule->getChanges()
                ]);
                
                // NÃO recalcular planos diários automaticamente - isso será feito manualmente na interface de planos diários
                // $schedule->recalculateDailyPlans(false);
            }
        });
    }

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
        'end_date',
        'planned_quantity',
        'actual_quantity',
        'is_delayed',
        'delay_reason',
        'status',
        'priority',
        'responsible_id', // Novo campo com relação ao responsável
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
     * Get the responsible person for this schedule.
     */
    public function responsible()
    {
        return $this->belongsTo(Responsible::class);
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
            ->select('mrp_shifts.*'); // Especifica a tabela para evitar ambiguidade de colunas
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
                0 => 'sun',
                1 => 'mon',
                2 => 'tue',
                3 => 'wed',
                4 => 'thu',
                5 => 'fri',
                6 => 'sat'
            ];
        
            // Dias de trabalho definidos no agendamento
            $workingDays = $this->working_days ?? [
                'mon' => true,
                'tue' => true,
                'wed' => true,
                'thu' => true,
                'fri' => true,
                'sat' => false,
                'sun' => false
            ];
            
            // Debug dos dias de trabalho definidos
            \Illuminate\Support\Facades\Log::debug('Dias de trabalho definidos na programação:', [
                'working_days' => $workingDays,
                'schedule_id' => $this->id
            ]);    
            
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
                // Converter minutos para horas
                $nonProductiveHours = $totalNonProductiveMinutes / 60;
                
                if ($hoursPerDay > 0) { // Evitar divisão por zero
                    // Calcular a proporção de tempo produtivo (tempo total menos setup/cleanup)
                    $productiveRatio = max(0, ($hoursPerDay - $nonProductiveHours) / $hoursPerDay);
                    $dailyCapacity = $dailyCapacity * $productiveRatio;
                    
                    if ($debug) {
                        \Illuminate\Support\Facades\Log::info('Capacidade ajustada para setup/cleanup (método corrigido)', [
                            'setup_time' => $setupTime, 
                            'cleanup_time' => $cleanupTime,
                            'total_non_productive_minutes' => $totalNonProductiveMinutes,
                            'non_productive_hours' => $nonProductiveHours,
                            'hours_per_day' => $hoursPerDay,
                            'productive_ratio' => $productiveRatio,
                            'daily_capacity_before' => $hoursPerDay * $hourlyRate,
                            'daily_capacity_after' => $dailyCapacity
                        ]);
                    }
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
                    // Distribuir a quantidade planejada entre os turnos com base na duração de cada turno
                    $shiftsCount = $shifts->count();
                    $totalShiftHours = 0;
                    $shiftHours = [];
                    
                    foreach ($shifts as $shift) {
                        $duration = $shift->duration; // Usa o accessor que calcula a duração do turno
                        $shiftHours[$shift->id] = $duration;
                        $totalShiftHours += $duration;
                    }
                    
                    // Debug para investigar o problema da distribuição
                    if ($debug) {
                        \Illuminate\Support\Facades\Log::info('Detalhes dos turnos e horas', [
                            'date' => $date,
                            'shifts_count' => $shiftsCount,
                            'total_shift_hours' => $totalShiftHours,
                            'shift_details' => $shifts->map(function($shift) {
                                return [
                                    'id' => $shift->id,
                                    'name' => $shift->name,
                                    'start_time' => $shift->start_time,
                                    'end_time' => $shift->end_time,
                                    'duration' => $shift->duration,
                                ];
                            })->toArray(),
                        ]);
                    }
                    
                    // Verificar se a duração total dos turnos é válida
                    if ($totalShiftHours <= 0) {
                        $quantityPerShift = $quantityForDay / $shiftsCount; // Fallback para divisão igual
                    } else {
                        // Inicializar array para armazenar quantidade por turno
                        $shiftQuantities = [];
                        
                        // Distribuir proporcionalmente à duração do turno
                        foreach ($shifts as $shift) {
                            $ratio = $shiftHours[$shift->id] / $totalShiftHours;
                            $shiftQuantities[$shift->id] = $quantityForDay * $ratio;
                        }
                    }
                    
                    if ($debug) {
                        \Illuminate\Support\Facades\Log::info('Distribuição proporcional de quantidade por turnos', [
                            'date' => $date,
                            'daily_quantity' => $quantityForDay,
                            'shifts_count' => $shiftsCount,
                            'shift_quantities' => isset($shiftQuantities) ? $shiftQuantities : [],
                            'total_shift_hours' => $totalShiftHours,
                            'working_hours_per_day' => $this->working_hours_per_day,
                            'hourly_production_rate' => $this->hourly_production_rate,
                            'schedule_id' => $this->id,
                            'schedule_number' => $this->schedule_number,
                            'planned_quantity' => $this->planned_quantity
                        ]);
                    }
                    
                    // Criar planos diários com base na quantidade calculada para cada turno
                    foreach ($shifts as $shift) {
                        // Determinar a quantidade para este turno específico
                        $shiftQuantity = 0;
                        
                        if (isset($shiftQuantities) && isset($shiftQuantities[$shift->id])) {
                            // Usar a quantidade proporcional à duração do turno
                            $shiftQuantity = $shiftQuantities[$shift->id];
                        } else {
                            // Fallback para divisão igual
                            $shiftQuantity = $quantityForDay / $shiftsCount;
                        }
                        
                        // Verificar se é o último turno no último dia para ajustar o total
                        if ($isLastDay && $shift->id === $shifts->last()->id) {
                            // Calcular o total já atribuído antes deste último turno
                            $totalAssigned = 0;
                            foreach ($plans as $existingPlan) {
                                $totalAssigned += $existingPlan['planned_quantity']; 
                            }
                            $difference = $this->planned_quantity - $totalAssigned;
                            
                            if ($debug) {
                                \Illuminate\Support\Facades\Log::info('Ajuste do último turno para garantir total exato', [
                                    'total_assigned_before' => $totalAssigned,
                                    'planned_quantity' => $this->planned_quantity,
                                    'difference' => $difference,
                                    'original_shift_quantity' => $shiftQuantity,
                                    'new_shift_quantity' => $shiftQuantity + $difference
                                ]);
                            }
                            
                            // Ajustar a quantidade para garantir o total exato
                            $shiftQuantity = max(0, $shiftQuantity + $difference);
                        }
                        
                        $planData = [
                            'schedule_id' => $this->id,
                            'production_date' => $date,
                            'planned_quantity' => $shiftQuantity,
                            'status' => 'pending',
                            'created_by' => $userId,
                            'updated_by' => $userId,
                            'shift_id' => $shift->id
                        ];
                        $plans[] = $planData;
                        
                        if ($debug) {
                            $logMessage = $index === 0 ? 'Dados de plano diário com turno (primeiro dia)' : 
                                         ($isLastDay ? 'Dados de plano diário com turno (último dia)' : 'Dados de plano diário com turno');
                            
                            \Illuminate\Support\Facades\Log::debug($logMessage, array_merge($planData, [
                                'shift_name' => $shift->name,
                                'shift_duration' => isset($shiftHours[$shift->id]) ? $shiftHours[$shift->id] : 'N/A',
                                'total_shift_hours' => $totalShiftHours,
                                'ratio_of_day' => isset($shiftHours[$shift->id]) && $totalShiftHours > 0 ? ($shiftHours[$shift->id] / $totalShiftHours) : 'N/A'
                            ]));
                        }
                    }
                } else {
                    // Se não houver turnos, criar um plano diário sem associação de turno
                    $planData = [
                        'schedule_id' => $this->id,
                        'production_date' => $date,
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
    
    /**
     * Recalcula e atualiza todos os planos diários associados a esta programação
     * Este método é chamado automaticamente quando a programação é atualizada
     * para garantir que os planos diários reflitam as configurações atualizadas
     * 
     * @param bool $forceDelete Se true, exclui todos os planos atuais antes de recalcular
     * @return array Detalhes da operação realizada
     */
    public function recalculateDailyPlans($forceDelete = false)
    {
        try {
            \Illuminate\Support\Facades\Log::info('Iniciando recálculo de planos diários', [
                'schedule_id' => $this->id,
                'schedule_number' => $this->schedule_number,
                'force_delete' => $forceDelete
            ]);
            
            // Obter os planos diários atuais
            $currentPlans = ProductionDailyPlan::where('schedule_id', $this->id)->get();
            $planCount = $currentPlans->count();
            
            // Se não existem planos ou forceDelete é true, excluir e recalcular tudo
            if ($planCount === 0 || $forceDelete) {
                // Se houver planos existentes, excluí-los
                if ($planCount > 0) {
                    ProductionDailyPlan::where('schedule_id', $this->id)->delete();
                    
                    \Illuminate\Support\Facades\Log::info('Planos diários excluídos para recálculo', [
                        'schedule_id' => $this->id,
                        'planos_excluidos' => $planCount
                    ]);
                }
                
                // Calcular novos planos diários com base nas configurações atuais
                $result = $this->calculatePlannedDistribution(true);
                
                if (isset($result['error'])) {
                    throw new \Exception($result['error']);
                }
                
                // Criar os novos planos diários
                $newPlans = [];
                foreach ($result['plans'] as $plan) {
                    $newPlans[] = ProductionDailyPlan::create($plan);
                }
                
                \Illuminate\Support\Facades\Log::info('Planos diários recalculados com sucesso', [
                    'schedule_id' => $this->id,
                    'novos_planos' => count($newPlans)
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Planos diários recalculados com sucesso',
                    'deleted_plans' => $planCount,
                    'new_plans' => count($newPlans)
                ];
            } else {
                // Se existem planos e forceDelete é false, atualizar os planos existentes
                // sem excluí-los (preserva dados já registrados pelo usuário)
                
                // Calcular novas distribuições
                $result = $this->calculatePlannedDistribution(true);
                
                if (isset($result['error'])) {
                    throw new \Exception($result['error']);
                }
                
                // Criar um mapa dos planos calculados indexado por data+turno
                $plannedMap = [];
                foreach ($result['plans'] as $plan) {
                    $key = $plan['production_date'] . '-' . ($plan['shift_id'] ?? 'noshift');
                    $plannedMap[$key] = $plan;
                }
                
                // Atualizar apenas as quantidades planejadas dos planos existentes
                $updatedCount = 0;
                foreach ($currentPlans as $currentPlan) {
                    $key = $currentPlan->production_date->format('Y-m-d') . '-' . ($currentPlan->shift_id ?? 'noshift');
                    
                    if (isset($plannedMap[$key])) {
                        // Atualizar apenas a quantidade planejada (preservar outros dados)
                        $currentPlan->planned_quantity = $plannedMap[$key]['planned_quantity'];
                        $currentPlan->save();
                        $updatedCount++;
                    }
                }
                
                \Illuminate\Support\Facades\Log::info('Planos diários atualizados (apenas quantidades)', [
                    'schedule_id' => $this->id,
                    'total_planos' => $planCount,
                    'planos_atualizados' => $updatedCount
                ]);
                
                return [
                    'success' => true, 
                    'message' => 'Quantidades planejadas atualizadas',
                    'updated_plans' => $updatedCount,
                    'total_plans' => $planCount
                ];
            }
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao recalcular planos diários', [
                'schedule_id' => $this->id,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => 'Erro ao recalcular planos diários: ' . $e->getMessage()
            ];
        }
    }
}
