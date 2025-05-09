<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\MaintenanceEquipment;
use App\Models\MaintenancePlan;
use App\Models\MaintenanceNote;
use App\Models\MaintenanceCorrective;
use App\Models\FailureCause;  // Adicionado para solucionar problema de namespace
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MaintenanceDashboard extends Component
{
    /**
     * Método utilitário para garantir que todas as queries filtrem registros com deleted_at
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function ensureNotDeleted($query)
    {
        return $query->whereNull('deleted_at');
    }
    
    /**
     * Método utilitário para filtrar registros excluídos em relações de equipment
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $areaId
     * @return void
     */
    protected function filterEquipment($query, $areaId = null)
    {
        $query->whereHas('equipment', function($equipmentQuery) use ($areaId) {
            $equipmentQuery->whereNull('deleted_at');
            
            if ($areaId) {
                $equipmentQuery->where('area_id', $areaId);
            }
        });
    }
    public $equipmentCount;
    public $totalEquipment;
    public $equipmentInMaintenance;
    public $equipmentOutOfService;
    public $scheduledTasks;
    public $overdueTasks;
    public $completedTasks;
    public $maintenanceAlerts = [];
    
    // Novos dados para widgets
    public $pendingCorrectiveCount;
    public $partRequestsCount;
    public $upcomingMaintenance = [];
    public $recentTasks = [];
    public $availableTechnicians = [];

    // Dados para gráficos
    public $planningChartData = [];
    public $correctiveChartData = [];
    public $monthlyTasksData = [];
    public $statusDistributionData = [];
    public $maintenanceTypeData = [];

    // Dados adicionais para KPIs gerais
    public $plannedTasksCount = 0;
    public $actualTasksCount = 0;
    public $pendingTasksCount = 0;
    public $compliancePercentage = 0;
    public $nonCompliancePercentage = 0;

    // Dados para gráficos adicionais
    public $areaTaskData = [];
    public $areaComplianceData = [];
    public $lineTaskData = [];
    public $lineComplianceData = [];
    public $taskDescriptionData = [];
    public $plannedDates = [];
    public $maintenancePlanStatusData = []; // New property for maintenance plan status

    // Filtros
    public $filterYear;
    public $filterMonth;
    public $filterStatus;
    public $filterArea;

    protected $listeners = ['refresh' => 'loadDashboardData'];

    public function mount()
    {
        // Inicializar filtros com valores padrão
        $this->filterYear = date('Y');
        $this->filterMonth = 'all';
        $this->filterStatus = 'all';
        $this->filterArea = 'all';

        $this->loadDashboardData();
    }

    /**
     * Método para atualizar o dashboard quando os filtros são alterados
     */
    public function refreshDashboard()
    {
        // Registrar os valores de filtro no log para debug
        Log::info('Updating dashboard with filters:', [
            'year' => $this->filterYear,
            'month' => $this->filterMonth,
            'status' => $this->filterStatus,
            'area' => $this->filterArea
        ]);

        // Limpar dados anteriores
        $this->resetExcept(['filterYear', 'filterMonth', 'filterStatus', 'filterArea']);
        
        // Recarregar dados com novos filtros
        $this->loadDashboardData();
        
        // Notificar o frontend
        $this->dispatch('dashboardDataLoaded');
        $this->dispatch('refreshCharts');
        
        session()->flash('message', 'Dashboard updated successfully!');
    }

    public function loadDashboardData()
    {
        // Carregar contagens de equipamentos
        $this->equipmentCount = MaintenanceEquipment::whereNull('deleted_at')->count();
        $this->totalEquipment = $this->equipmentCount;

        // Verificar se a coluna 'status' existe antes de tentar usá-la
        if (Schema::hasColumn('maintenance_equipment', 'status')) {
            // Equipamento fora de serviço
        $this->equipmentOutOfService = MaintenanceEquipment::where('status', 'out_of_service')->whereNull('deleted_at')->count();

            // Equipamentos em manutenção - buscar em maintenance_plans os equipamentos com planos ativos
            // Consideramos também as notas de manutenção para determinar equipamentos realmente em manutenção
            $equipmentIdsWithActivePlans = MaintenancePlan::whereIn('status', ['pending', 'in_progress', 'schedule'])
                ->whereNotNull('equipment_id')
                ->whereNull('deleted_at')
                ->pluck('equipment_id')
                ->unique();
            
            // Verificar quais equipamentos têm notas de manutenção em progresso
            $equipmentIdsWithActiveNotes = MaintenanceNote::whereIn('maintenance_plan_id', function($query) {
                $query->select('id')
                    ->from('maintenance_plans')
                    ->whereIn('status', ['in_progress'])
                    ->whereNotNull('equipment_id')
                    ->whereNull('deleted_at');
            })
            ->whereIn('maintenance_notes.status', ['in_progress', 'pending'])
            ->whereNull('deleted_at')
            ->join('maintenance_plans', 'maintenance_notes.maintenance_plan_id', '=', 'maintenance_plans.id')
            ->pluck('maintenance_plans.equipment_id')
            ->unique();
            
            // Combinar os equipamentos de ambas as consultas e contar o total único
            $allEquipmentIds = $equipmentIdsWithActivePlans->merge($equipmentIdsWithActiveNotes)->unique();
            $this->equipmentInMaintenance = $allEquipmentIds->count();
        } else {
            // Se a coluna não existe, definir valores padrão
            $this->equipmentOutOfService = 0;

            // Mesmo sem coluna status, podemos buscar equipamentos com manutenção planejada
            // Usando a mesma lógica melhorada considerando as notas de manutenção
            $equipmentIdsWithActivePlans = MaintenancePlan::whereIn('status', ['pending', 'in_progress', 'schedule'])
                ->whereNotNull('equipment_id')
                ->whereNull('deleted_at')
                ->pluck('equipment_id')
                ->unique();
            
            // Verificar quais equipamentos têm notas de manutenção em progresso
            $equipmentIdsWithActiveNotes = MaintenanceNote::whereIn('maintenance_plan_id', function($query) {
                $query->select('id')
                    ->from('maintenance_plans')
                    ->whereIn('status', ['in_progress'])
                    ->whereNotNull('equipment_id')
                    ->whereNull('deleted_at');
            })
            ->whereIn('maintenance_notes.status', ['in_progress', 'pending'])
            ->whereNull('deleted_at')
            ->join('maintenance_plans', 'maintenance_notes.maintenance_plan_id', '=', 'maintenance_plans.id')
            ->pluck('maintenance_plans.equipment_id')
            ->unique();
            
            // Combinar os equipamentos de ambas as consultas e contar o total único
            $allEquipmentIds = $equipmentIdsWithActivePlans->merge($equipmentIdsWithActiveNotes)->unique();
            $this->equipmentInMaintenance = $allEquipmentIds->count();
        }

        // Carregar contagens de tarefas
        // Total de planos de manutenção (todos os planos)
        $this->scheduledTasks = MaintenancePlan::whereNull('deleted_at')->count();

        // Tarefas não atualizadas/vencidas (tarefas pendentes com data programada no passado)
        // Verificar primeiro se a coluna scheduled_date existe
        if (Schema::hasColumn('maintenance_plans', 'scheduled_date')) {
            $this->overdueTasks = MaintenancePlan::whereIn('status', ['pending', 'schedule'])
                ->where('scheduled_date', '<', now()->format('Y-m-d'))
                ->whereNull('deleted_at')
                ->count();
        } else {
            // Se não tiver data, simplesmente contar as tarefas pendentes e agendadas
            $this->overdueTasks = MaintenancePlan::whereIn('status', ['pending', 'schedule'])->whereNull('deleted_at')->count();
        }

        $this->completedTasks = MaintenancePlan::where('status', 'completed')->whereNull('deleted_at')->count();

        // Carregar dados para os KPIs adicionais
        $this->loadKpiData();

        // Carregar alertas de manutenção
        $now = Carbon::now();
        $fiveDaysFromNow = $now->copy()->addDays(5);

        // Buscar tarefas pendentes que estão dentro do prazo de alerta (5 dias) ou atrasadas
        $pendingTasks = MaintenancePlan::whereIn('status', ['pending', 'in_progress', 'schedule'])
            ->whereNotNull('scheduled_date')
            ->whereNull('deleted_at')
            ->where(function($query) use ($now, $fiveDaysFromNow) {
                $query->where('scheduled_date', '<=', $fiveDaysFromNow->format('Y-m-d'))
                      ->orWhere('scheduled_date', '<', $now->format('Y-m-d'));
            })
            ->get();

        // Filtrar tarefas que já possuem notas de manutenção com status "completed"
        $completedTaskIds = MaintenanceNote::where('status', 'completed')
            ->pluck('maintenance_plan_id')
            ->toArray();

        $filteredTasks = $pendingTasks->filter(function($task) use ($completedTaskIds) {
            return !in_array($task->id, $completedTaskIds);
        })->take(5);

        $this->maintenanceAlerts = $filteredTasks->map(function ($task) use ($now) {
            $scheduledDate = Carbon::parse($task->scheduled_date);
            $daysUntil = $scheduledDate->diffInDays($now, false);
            $isOverdue = $daysUntil < 0;

            return [
                'id' => $task->id,
                'title' => $task->description ?? ('Task #' . $task->id),
                'description' => $task->notes ?? 'No description available',
                'date' => $scheduledDate->format('d/m/Y'),
                'days_until' => $isOverdue ? abs($daysUntil) . ' days overdue' : $daysUntil . ' days left',
                'status' => $isOverdue ? 'overdue' : 'upcoming',
                'status_color' => $isOverdue ? 'red' : ($daysUntil <= 2 ? 'yellow' : 'blue'),
                'equipment' => $task->equipment ? $task->equipment->name : 'N/A',
                'completed' => false
            ];
        })->toArray();

        // Carregar dados para os gráficos com base nos filtros
        $this->loadChartsData();

        // Carregar dados para os novos gráficos
        $this->loadAreaData();
        $this->loadLineData();
        $this->loadTaskDescriptionData();
        $this->loadMaintenancePlanStatusData(); // Load maintenance plan status data

        // Dispara um evento para o navegador saber que pode renderizar os gráficos
        $this->dispatch('dashboardDataLoaded');
    }

    /**
     * Carregar dados para os KPIs adicionais
     */
    protected function loadKpiData()
    {
        // Inicializar query base com os filtros atuais
        $query = $this->ensureNotDeleted(MaintenancePlan::query());
        
        // Aplicar filtro de ano se não for 'all'
        if ($this->filterYear !== 'all') {
            $query->whereYear('scheduled_date', $this->filterYear);
        }
        
        // Aplicar filtro de mês se não for 'all'
        if ($this->filterMonth !== 'all') {
            $query->whereMonth('scheduled_date', $this->filterMonth);
        }
        
        // Aplicar filtro de status se não for 'all'
        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }
        
        // Aplicar filtro de área se não for 'all'
        if ($this->filterArea !== 'all') {
            $query->whereHas('equipment', function($q) {
                $q->where('area_id', $this->filterArea);
            });
        }

        // Contagem de tarefas planejadas
        $this->plannedTasksCount = $query->count();

        // Clone a query para não perder as condições
        $completedQuery = clone $query;
        $pendingQuery = clone $query;

        // Contagem de tarefas realizadas
        $this->actualTasksCount = $completedQuery->where('status', 'completed')->count();

        // Contagem de tarefas pendentes
        $this->pendingTasksCount = $pendingQuery->where('status', 'pending')->count();

        // Cálculo da taxa de conformidade
        if ($this->plannedTasksCount > 0) {
            $this->compliancePercentage = round(($this->actualTasksCount / $this->plannedTasksCount) * 100);
            $this->nonCompliancePercentage = 100 - $this->compliancePercentage;
        } else {
            $this->compliancePercentage = 0;
            $this->nonCompliancePercentage = 0;
        }
    }

    /**
     * Carregar datas planejadas
     */
    protected function loadPlannedDates()
    {
        // Inicializar query com os filtros atuais
        $query = $this->ensureNotDeleted(MaintenancePlan::whereNotNull('scheduled_date'));
        
        // Aplicar filtro de ano se não for 'all'
        if ($this->filterYear !== 'all') {
            $query->whereYear('scheduled_date', $this->filterYear);
        }
        
        // Aplicar filtro de mês se não for 'all'
        if ($this->filterMonth !== 'all') {
            $query->whereMonth('scheduled_date', $this->filterMonth);
        }
        
        // Aplicar filtro de status se não for 'all'
        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }
        
        // Aplicar filtro de área se não for 'all'
        if ($this->filterArea !== 'all') {
            $query->whereHas('equipment', function($q) {
                $q->where('area_id', $this->filterArea);
            });
        }
        
        $dates = $query->where('scheduled_date', '>=', now()->subDays(30))
            ->where('scheduled_date', '<=', now()->addDays(30))
            ->orderBy('scheduled_date')
            ->pluck('scheduled_date')
            ->toArray();

        $this->plannedDates = array_map(function($date) {
            return Carbon::parse($date)->format(\App\Models\Setting::getSystemDateFormat());
        }, $dates);
    }

    /**
     * Carregar dados para os gráficos com base nos filtros
     */
    protected function loadChartsData()
    {
        Log::info('Loading chart data with filters:', [
            'year' => $this->filterYear,
            'month' => $this->filterMonth,
            'status' => $this->filterStatus,
            'area' => $this->filterArea
        ]);
        
        // Inicializar query base com filtro de ano
        $baseQuery = $this->ensureNotDeleted(MaintenancePlan::whereYear('scheduled_date', $this->filterYear));
        $correctiveQuery = $this->ensureNotDeleted(MaintenanceCorrective::whereYear('created_at', $this->filterYear));

        // Aplicar filtro de mês se não for 'all'
        if ($this->filterMonth !== 'all') {
            $baseQuery->whereMonth('scheduled_date', $this->filterMonth);
            $correctiveQuery->whereMonth('created_at', $this->filterMonth);
        }

        // Aplicar filtro de status se não for 'all'
        if ($this->filterStatus !== 'all') {
            $baseQuery->where('status', $this->filterStatus);
            $correctiveQuery->where('status', $this->filterStatus);
        }

        // Aplicar filtro de área se não for 'all'
        if ($this->filterArea !== 'all') {
            $this->filterEquipment($baseQuery, $this->filterArea);
            $this->filterEquipment($correctiveQuery, $this->filterArea);
        } else {
            // Mesmo sem filtro de área, garantir que apenas equipamentos não excluídos sejam considerados
            $this->filterEquipment($baseQuery);
            $this->filterEquipment($correctiveQuery);
        }

        // Dados para gráfico de distribuição por mês (planejado vs. corretivo)
        $this->monthlyTasksData = $this->getMonthlyDistributionData($baseQuery, $correctiveQuery);

        // Dados para gráfico de status
        $this->statusDistributionData = $this->getStatusDistributionData($baseQuery);

        // Dados para gráfico de manutenção planejada
        $this->planningChartData = $this->getPlanningChartData($baseQuery);

        // Dados para gráfico de manutenção corretiva
        $this->correctiveChartData = $this->getCorrectiveChartData($correctiveQuery);
    }

    /**
     * Coletar dados agrupados por área (maintenance_areas)
     */
    protected function loadAreaData()
    {
        try {
            // Initialize empty arrays for our datasets
            $areas = [];
            $areaIds = [];
            $plannedData = [];
            $actualData = [];
            $complianceData = [];

            // First try to get data from maintenance_areas table
            if (Schema::hasTable('maintenance_areas')) {
                $areasData = DB::table('maintenance_areas')
                    ->whereNotNull('name')
                    ->whereNull('deleted_at')
                    ->select('id', 'name')
                    ->get();

                foreach ($areasData as $area) {
                    $areas[] = $area->name;
                    $areaIds[] = $area->id;
                }
            } else {
                // If no area tables, extract unique area IDs from equipment table
                if (Schema::hasColumn('maintenance_equipment', 'area_id')) {
                    $areaData = DB::table('maintenance_equipment')
                        ->whereNotNull('area_id')
                        ->whereNull('deleted_at')
                        ->select('area_id')
                        ->distinct()
                        ->get();

                    foreach ($areaData as $area) {
                        // Try to get area name if available
                        $areaInfo = null;
                        if (Schema::hasTable('maintenance_areas')) {
                            $areaInfo = DB::table('maintenance_areas')
                                ->where('id', $area->area_id)
                                ->whereNull('deleted_at')
                                ->first();
                        }

                        if ($areaInfo) {
                            $areas[] = $areaInfo->name;
                        } else {
                            $areas[] = 'Area ' . $area->area_id;
                        }

                        $areaIds[] = $area->area_id;
                    }
                }
            }

            // Check for direct area field in equipment
            if (empty($areas)) {
                if (Schema::hasColumn('maintenance_equipment', 'area')) {
                    $areaNames = DB::table('maintenance_equipment')
                        ->whereNotNull('area')
                        ->whereNull('deleted_at')
                        ->select('area')
                        ->distinct()
                        ->pluck('area')
                        ->toArray();

                    foreach ($areaNames as $index => $areaName) {
                        $areas[] = $areaName;
                        $areaIds[] = $index; // Use index as placeholder ID
                    }
                }
            }

            // If we still have no areas, create an empty chart structure
            if (empty($areas)) {
                $this->areaTaskData = [
                    'labels' => [],
                    'datasets' => [
                        [
                            'label' => 'Planned',
                            'data' => [],
                            'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                            'borderColor' => 'rgba(54, 162, 235, 1)',
                            'borderWidth' => 1
                        ],
                        [
                            'label' => 'Actual',
                            'data' => [],
                            'backgroundColor' => 'rgba(75, 192, 192, 0.5)',
                            'borderColor' => 'rgba(75, 192, 192, 1)',
                            'borderWidth' => 1
                        ]
                    ]
                ];

                $this->areaComplianceData = [
                    'labels' => [],
                    'datasets' => [
                        [
                            'type' => 'line',
                            'label' => 'Compliance %',
                            'data' => [],
                            'backgroundColor' => 'rgba(153, 102, 255, 0.2)',
                            'borderColor' => 'rgba(153, 102, 255, 1)',
                            'borderWidth' => 2,
                            'fill' => true,
                        ]
                    ]
                ];

                return;
            }

            // Now that we have areas, gather the actual metrics for each area
            for ($i = 0; $i < count($areas); $i++) {
                $areaName = $areas[$i];
                $areaId = $areaIds[$i];

                // Get planned maintenance tasks for this area
                // First, try with area_id from MaintenancePlan directly
                $plannedQuery = null;
                if (Schema::hasColumn('maintenance_plans', 'area_id')) {
                    $plannedQuery = MaintenancePlan::where('area_id', $areaId)->whereNull('deleted_at');
                } else {
                    // Try with equipment relationship
                    $plannedQuery = MaintenancePlan::whereNull('deleted_at')->whereHas('equipment', function ($query) use ($areaId) {
                        // Check which column to use
                        if (Schema::hasColumn('maintenance_equipment', 'area_id')) {
                            $query->where('area_id', $areaId)->whereNull('deleted_at');
                        }
                    });
                }

                // Apply any active filters
                if ($this->filterYear !== 'all') {
                    $plannedQuery->whereYear('scheduled_date', $this->filterYear);
                }

                if ($this->filterMonth !== 'all') {
                    $plannedQuery->whereMonth('scheduled_date', $this->filterMonth);
                }

                $planned = $plannedQuery->count();

                // Get completed maintenance tasks for this area
                $actualQuery = clone $plannedQuery;
                $actual = $actualQuery->where('status', 'completed')->count();

                // Also get corrective maintenance count
                $correctiveQuery = MaintenanceCorrective::whereNull('deleted_at')->whereHas('equipment', function ($query) use ($areaId) {
                    // Check which column to use
                    if (Schema::hasColumn('maintenance_equipment', 'area_id')) {
                        $query->where('area_id', $areaId)->whereNull('deleted_at');
                    }
                });

                // Apply filters to corrective data too
                if ($this->filterYear !== 'all') {
                    $correctiveQuery->whereYear('created_at', $this->filterYear);
                }

                if ($this->filterMonth !== 'all') {
                    $correctiveQuery->whereMonth('created_at', $this->filterMonth);
                }

                $corrective = $correctiveQuery->count();

                // Calculate compliance percentage (ensure it doesn't exceed 100%)
                $compliance = $planned > 0 ? min(100, round(($actual / $planned) * 100)) : 0;

                $plannedData[] = $planned;
                $actualData[] = $actual;
                $complianceData[] = $compliance;
            }

            // Create chart data for area tasks
            $this->areaTaskData = [
                'labels' => $areas,
                'datasets' => [
                    [
                        'label' => 'Planned',
                        'data' => $plannedData,
                        'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                        'borderColor' => 'rgba(54, 162, 235, 1)',
                        'borderWidth' => 1
                    ],
                    [
                        'label' => 'Actual',
                        'data' => $actualData,
                        'backgroundColor' => 'rgba(75, 192, 192, 0.5)',
                        'borderColor' => 'rgba(75, 192, 192, 1)',
                        'borderWidth' => 1
                    ]
                ]
            ];

            // Create chart data for area compliance
            $this->areaComplianceData = [
                'labels' => $areas,
                'datasets' => [
                    [
                        'type' => 'line',
                        'label' => 'Compliance %',
                        'data' => $complianceData,
                        'backgroundColor' => 'rgba(153, 102, 255, 0.2)',
                        'borderColor' => 'rgba(153, 102, 255, 1)',
                        'borderWidth' => 2,
                        'fill' => true,
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error("Error loading area data: " . $e->getMessage());

            // Even in case of error, return empty structure rather than default data
            $this->areaTaskData = [
                'labels' => [],
                'datasets' => [
                    [
                        'label' => 'Planned',
                        'data' => [],
                        'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                        'borderColor' => 'rgba(54, 162, 235, 1)',
                        'borderWidth' => 1
                    ],
                    [
                        'label' => 'Actual',
                        'data' => [],
                        'backgroundColor' => 'rgba(75, 192, 192, 0.5)',
                        'borderColor' => 'rgba(75, 192, 192, 1)',
                        'borderWidth' => 1
                    ]
                ]
            ];

            $this->areaComplianceData = [
                'labels' => [],
                'datasets' => [
                    [
                        'type' => 'line',
                        'label' => 'Compliance %',
                        'data' => [],
                        'backgroundColor' => 'rgba(153, 102, 255, 0.2)',
                        'borderColor' => 'rgba(153, 102, 255, 1)',
                        'borderWidth' => 2,
                        'fill' => true,
                    ]
                ]
            ];
        }
    }

    /**
     * Load data grouped by lines (maintenance_lines)
     */
    protected function loadLineData()
    {
        try {
            // Initialize empty arrays for our datasets
            $lines = [];
            $lineIds = [];
            $plannedData = [];
            $actualData = [];
            $complianceData = [];

            // First try to get lines from maintenance_lines table
            if (Schema::hasTable('maintenance_lines')) {
                $linesData = DB::table('maintenance_lines')
                    ->whereNotNull('name')
                    ->select('id', 'name')
                    ->get();

                foreach ($linesData as $line) {
                    $lines[] = $line->name;
                    $lineIds[] = $line->id;
                }
            } else {
                // Check if line_id exists in the equipment table
                if (Schema::hasColumn('maintenance_equipment', 'line_id')) {
                    // Get line IDs from equipment table
                    $lineIdValues = DB::table('maintenance_equipment')
                        ->whereNotNull('line_id')
                        ->select('line_id')
                        ->distinct()
                        ->pluck('line_id')
                        ->toArray();

                    // For each line ID, try to get its name
                    foreach ($lineIdValues as $lineId) {
                        $lineIds[] = $lineId;

                        // Try to get the name from maintenance_lines
                        $lineName = null;
                        if (Schema::hasTable('maintenance_lines')) {
                            $lineInfo = DB::table('maintenance_lines')
                                ->where('id', $lineId)
                                ->first();
                            if ($lineInfo) {
                                $lineName = $lineInfo->name;
                            }
                        }

                        // If we couldn't find a name, create a placeholder
                        if (!$lineName) {
                            $lineName = 'Line ' . $lineId;
                        }

                        $lines[] = $lineName;
                    }
                }
            }

            // Check for direct line field in equipment
            if (empty($lines) && Schema::hasColumn('maintenance_equipment', 'line')) {
                // Get distinct line values directly from equipment table
                $lineValues = DB::table('maintenance_equipment')
                    ->whereNotNull('line')
                    ->select('line')
                    ->distinct()
                    ->get();

                foreach ($lineValues as $index => $lineObj) {
                    $lines[] = $lineObj->line;
                    $lineIds[] = $index; // Use index as placeholder ID
                }
            }

            // If we still have no lines, create an empty chart structure
            if (empty($lines)) {
                $this->lineTaskData = [
                    'labels' => [],
                    'datasets' => [
                        [
                            'type' => 'line',
                            'label' => 'Planned',
                            'data' => [],
                            'fill' => false,
                            'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                            'borderColor' => 'rgba(54, 162, 235, 1)',
                            'borderWidth' => 2,
                            'tension' => 0.1
                        ],
                        [
                            'type' => 'line',
                            'label' => 'Actual',
                            'data' => [],
                            'fill' => false,
                            'backgroundColor' => 'rgba(75, 192, 192, 0.5)',
                            'borderColor' => 'rgba(75, 192, 192, 1)',
                            'borderWidth' => 2,
                            'tension' => 0.1
                        ]
                    ]
                ];

                $this->lineComplianceData = [
                    'labels' => [],
                    'datasets' => [
                        [
                            'type' => 'line',
                            'label' => 'Compliance %',
                            'data' => [],
                            'backgroundColor' => 'rgba(255, 159, 64, 0.2)',
                            'borderColor' => 'rgba(255, 159, 64, 1)',
                            'borderWidth' => 2,
                            'fill' => true,
                            'tension' => 0.1
                        ]
                    ]
                ];

                return;
            }

            // Now that we have lines, gather the actual metrics for each line
            for ($i = 0; $i < count($lines); $i++) {
                $lineName = $lines[$i];
                $lineId = $lineIds[$i];

                // Get planned maintenance tasks for this line
                $plannedQuery = null;

                // Try first with line_id from MaintenancePlan directly
                if (Schema::hasColumn('maintenance_plans', 'line_id')) {
                    $plannedQuery = MaintenancePlan::where('line_id', $lineId)->whereNull('deleted_at');
                }
                // If that doesn't work, try through equipment relationship
                else {
                    $plannedQuery = MaintenancePlan::whereNull('deleted_at')->whereHas('equipment', function ($query) use ($lineId, $lineName) {
                        if (Schema::hasColumn('maintenance_equipment', 'line_id')) {
                            $query->where('line_id', $lineId)->whereNull('deleted_at');
                        } elseif (Schema::hasColumn('maintenance_equipment', 'line')) {
                            $query->where('line', $lineName)->whereNull('deleted_at');
                        }
                    });
                }

                // Apply active filters
                if ($this->filterYear !== 'all') {
                    $plannedQuery->whereYear('scheduled_date', $this->filterYear);
                }

                if ($this->filterMonth !== 'all') {
                    $plannedQuery->whereMonth('scheduled_date', $this->filterMonth);
                }

                if ($this->filterStatus !== 'all') {
                    $plannedQuery->where('status', $this->filterStatus);
                }

                $planned = $plannedQuery->count();

                // Get completed maintenance tasks for this line
                $actualQuery = null;

                // Clone the original query, but limit to completed status
                if (Schema::hasColumn('maintenance_plans', 'line_id')) {
                    $actualQuery = MaintenancePlan::where('line_id', $lineId);
                } else {
                    $actualQuery = MaintenancePlan::whereHas('equipment', function ($query) use ($lineId, $lineName) {
                        if (Schema::hasColumn('maintenance_equipment', 'line_id')) {
                            $query->where('line_id', $lineId);
                        } elseif (Schema::hasColumn('maintenance_equipment', 'line')) {
                            $query->where('line', $lineName);
                        }
                    });
                }

                // Apply active filters (except status)
                if ($this->filterYear !== 'all') {
                    $actualQuery->whereYear('scheduled_date', $this->filterYear);
                }

                if ($this->filterMonth !== 'all') {
                    $actualQuery->whereMonth('scheduled_date', $this->filterMonth);
                }

                // Always filter for completed status
                $actual = $actualQuery->where('status', 'completed')->count();

                // Get corrective maintenance data for this line
                $correctiveQuery = MaintenanceCorrective::whereNull('deleted_at')->whereHas('equipment', function ($query) use ($lineId, $lineName) {
                    if (Schema::hasColumn('maintenance_equipment', 'line_id')) {
                        $query->where('line_id', $lineId)->whereNull('deleted_at');
                    } elseif (Schema::hasColumn('maintenance_equipment', 'line')) {
                        $query->where('line', $lineName)->whereNull('deleted_at');
                    }
                });

                // Apply filters to corrective data too
                if ($this->filterYear !== 'all') {
                    $correctiveQuery->whereYear('created_at', $this->filterYear);
                }

                if ($this->filterMonth !== 'all') {
                    $correctiveQuery->whereMonth('created_at', $this->filterMonth);
                }

                if ($this->filterStatus !== 'all') {
                    $correctiveQuery->where('status', $this->filterStatus);
                }

                $corrective = $correctiveQuery->count();

                // Calculate compliance percentage (ensure it doesn't exceed 100%)
                $compliance = $planned > 0 ? min(100, round(($actual / $planned) * 100)) : 0;

                $plannedData[] = $planned;
                $actualData[] = $actual;
                $complianceData[] = $compliance;
            }

            // Create chart data for line tasks
            $this->lineTaskData = [
                'labels' => $lines,
                'datasets' => [
                    [
                        'type' => 'line',
                        'label' => 'Planned',
                        'data' => $plannedData,
                        'fill' => false,
                        'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                        'borderColor' => 'rgba(54, 162, 235, 1)',
                        'borderWidth' => 2,
                        'tension' => 0.1
                    ],
                    [
                        'type' => 'line',
                        'label' => 'Actual',
                        'data' => $actualData,
                        'fill' => false,
                        'backgroundColor' => 'rgba(75, 192, 192, 0.5)',
                        'borderColor' => 'rgba(75, 192, 192, 1)',
                        'borderWidth' => 2,
                        'tension' => 0.1
                    ]
                ]
            ];

            // Create chart data for line compliance
            $this->lineComplianceData = [
                'labels' => $lines,
                'datasets' => [
                    [
                        'type' => 'line',
                        'label' => 'Compliance %',
                        'data' => $complianceData,
                        'backgroundColor' => 'rgba(255, 159, 64, 0.2)',
                        'borderColor' => 'rgba(255, 159, 64, 1)',
                        'borderWidth' => 2,
                        'fill' => true,
                        'tension' => 0.1
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error("Error loading line data: " . $e->getMessage());

            // Even in case of error, return empty structure rather than default data
            $this->lineTaskData = [
                'labels' => [],
                'datasets' => [
                    [
                        'type' => 'line',
                        'label' => 'Planned',
                        'data' => [],
                        'fill' => false,
                        'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                        'borderColor' => 'rgba(54, 162, 235, 1)',
                        'borderWidth' => 2,
                        'tension' => 0.1
                    ],
                    [
                        'type' => 'line',
                        'label' => 'Actual',
                        'data' => [],
                        'fill' => false,
                        'backgroundColor' => 'rgba(75, 192, 192, 0.5)',
                        'borderColor' => 'rgba(75, 192, 192, 1)',
                        'borderWidth' => 2,
                        'tension' => 0.1
                    ]
                ]
            ];

            $this->lineComplianceData = [
                'labels' => [],
                'datasets' => [
                    [
                        'type' => 'line',
                        'label' => 'Compliance %',
                        'data' => [],
                        'backgroundColor' => 'rgba(255, 159, 64, 0.2)',
                        'borderColor' => 'rgba(255, 159, 64, 1)',
                        'borderWidth' => 2,
                        'fill' => true,
                        'tension' => 0.1
                    ]
                ]
            ];
        }
    }

    /**
     * Collect data grouped by task description
     */
    protected function loadTaskDescriptionData()
    {
        try {
            // Get the most common task descriptions directly from the database
            // Use a cleaner approach than just LEFT(description, 30)
            $descriptions = MaintenancePlan::selectRaw('description')
                ->whereNotNull('description')
                ->whereNull('deleted_at')
                ->groupBy('description')
                ->orderByRaw('COUNT(*) DESC')
            ->limit(5)
                ->pluck('description')
                ->toArray();

            // Process descriptions to keep them readable but not too long
            $processedDescriptions = [];
            foreach ($descriptions as $desc) {
                // Intelligently truncate: cut at word boundary if possible
                if (strlen($desc) > 30) {
                    $shortDesc = substr($desc, 0, 30);
                    // Try to cut at last space to avoid cutting words
                    $spacePos = strrpos($shortDesc, ' ');
                    if ($spacePos !== false) {
                        $shortDesc = substr($shortDesc, 0, $spacePos);
                    }
                    $processedDescriptions[] = $shortDesc . '...';
                } else {
                    $processedDescriptions[] = $desc;
                }
            }

            // If we don't have enough descriptions, use default categories
            if (count($processedDescriptions) < 3) {
                $processedDescriptions = ['Lubrication', 'Electrical Inspection', 'Calibration', 'Cleaning', 'Replacement'];
            }

            $plannedData = [];
            $actualData = [];
            $pendingData = [];

            foreach ($descriptions as $index => $originalDesc) {
                $displayDesc = $processedDescriptions[$index];

                // Get exact matches for more accurate counts
                $planned = MaintenancePlan::where('description', $originalDesc)
                    ->whereNull('deleted_at')
                    ->count();

                // Get completed tasks with this description
                $actual = MaintenancePlan::where('description', $originalDesc)
                    ->whereNull('deleted_at')
                    ->where('status', 'completed')
                    ->count();

                // Get pending tasks with this description (including scheduled status)
                $pending = MaintenancePlan::where('description', $originalDesc)
                    ->whereNull('deleted_at')
                    ->whereIn('status', ['pending', 'schedule'])
                    ->count();

                $plannedData[] = $planned;
                $actualData[] = $actual;
                $pendingData[] = $pending;
            }

            // Build chart data for task description
            $this->taskDescriptionData = [
                'labels' => $processedDescriptions,
                'datasets' => [
                    [
                        'label' => 'Planned',
                        'data' => $plannedData,
                        'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                        'borderColor' => 'rgba(54, 162, 235, 1)',
                        'borderWidth' => 1
                    ],
                    [
                        'label' => 'Completed',
                        'data' => $actualData,
                        'backgroundColor' => 'rgba(75, 192, 192, 0.5)',
                        'borderColor' => 'rgba(75, 192, 192, 1)',
                        'borderWidth' => 1
                    ],
                    [
                        'label' => 'Pending',
                        'data' => $pendingData,
                        'backgroundColor' => 'rgba(255, 206, 86, 0.5)',
                        'borderColor' => 'rgba(255, 206, 86, 1)',
                        'borderWidth' => 1
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error("Error loading task description data: " . $e->getMessage());
            // Default data
            $this->taskDescriptionData = [
                'labels' => ['Lubrication', 'Electrical Inspection', 'Calibration', 'Cleaning', 'Replacement'],
                'datasets' => [
                    [
                        'label' => 'Planned',
                        'data' => [30, 45, 25, 40, 35],
                        'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                        'borderColor' => 'rgba(54, 162, 235, 1)',
                        'borderWidth' => 1
                    ],
                    [
                        'label' => 'Completed',
                        'data' => [25, 40, 20, 35, 30],
                        'backgroundColor' => 'rgba(75, 192, 192, 0.5)',
                        'borderColor' => 'rgba(75, 192, 192, 1)',
                        'borderWidth' => 1
                    ],
                    [
                        'label' => 'Pending',
                        'data' => [5, 5, 5, 5, 5],
                        'backgroundColor' => 'rgba(255, 206, 86, 0.5)',
                        'borderColor' => 'rgba(255, 206, 86, 1)',
                        'borderWidth' => 1
                    ]
                ]
            ];
        }
    }

    /**
     * Load maintenance plan data with recurrence info and status from notes
     */
    protected function loadMaintenancePlanStatusData()
    {
        try {
            // Start with equipment that has maintenance plans
            $equipment = MaintenanceEquipment::with(['maintenancePlans' => function($query) {
                $query->whereNull('deleted_at');
            }])
                ->whereNull('deleted_at')
                ->select('id', 'name')
                ->limit(10)
                ->get();

            $labels = [];
            $totalData = [];
            $inProgressData = [];
            $completedData = [];
            $cancelledData = [];
            $scheduledData = []; // Novo array para status 'schedule'

            foreach ($equipment as $equip) {
                // Skip equipment with no name
                if (empty($equip->name)) continue;

                // Truncate long names
                $shortName = strlen($equip->name) > 30
                    ? substr($equip->name, 0, 30) . '...'
                    : $equip->name;

                $labels[] = $shortName;

                // Get maintenance plans for this equipment
                $plans = $equip->maintenancePlans;
                $totalData[] = $plans->count();

                // Get plan IDs for this equipment
                $planIds = $plans->pluck('id')->toArray();

                // If there are no plans, continue to the next equipment
                if (empty($planIds)) {
                    $inProgressData[] = 0;
                    $completedData[] = 0;
                    $cancelledData[] = 0;
                    $scheduledData[] = 0; // Adicionando zero para scheduled
                    continue;
                }

                // Count notes with different statuses for these plans
                $inProgressCount = MaintenanceNote::whereIn('maintenance_plan_id', $planIds)
                    ->whereNull('deleted_at')
                    ->where('status', 'in_progress')
                    ->count();

                $completedCount = MaintenanceNote::whereIn('maintenance_plan_id', $planIds)
                    ->whereNull('deleted_at')
                    ->where('status', 'completed')
                    ->count();

                $cancelledCount = MaintenanceNote::whereIn('maintenance_plan_id', $planIds)
                    ->whereNull('deleted_at')
                    ->where('status', 'cancelled')
                    ->count();
                
                $scheduledCount = MaintenanceNote::whereIn('maintenance_plan_id', $planIds)
                    ->whereNull('deleted_at')
                    ->where('status', 'schedule')
                    ->count();

                $inProgressData[] = $inProgressCount;
                $completedData[] = $completedCount;
                $cancelledData[] = $cancelledCount;
                $scheduledData[] = $scheduledCount;
            }

            // Prepare chart data
            $this->maintenancePlanStatusData = [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Total Plans',
                        'data' => $totalData,
                        'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                        'borderColor' => 'rgba(54, 162, 235, 1)',
                        'borderWidth' => 1,
                        'type' => 'bar'
                    ],
                    [
                        'label' => 'In Progress',
                        'data' => $inProgressData,
                        'backgroundColor' => 'rgba(255, 206, 86, 0.5)',
                        'borderColor' => 'rgba(255, 206, 86, 1)',
                        'borderWidth' => 1,
                        'type' => 'bar'
                    ],
                    [
                        'label' => 'Completed',
                        'data' => $completedData,
                        'backgroundColor' => 'rgba(75, 192, 192, 0.5)',
                        'borderColor' => 'rgba(75, 192, 192, 1)',
                        'borderWidth' => 1,
                        'type' => 'bar'
                    ],
                    [
                        'label' => 'Cancelled',
                        'data' => $cancelledData,
                        'backgroundColor' => 'rgba(255, 99, 132, 0.5)',
                        'borderColor' => 'rgba(255, 99, 132, 1)',
                        'borderWidth' => 1,
                        'type' => 'bar'
                    ],
                    [
                        'label' => 'Scheduled',
                        'data' => $scheduledData,
                        'backgroundColor' => 'rgba(147, 51, 234, 0.5)',
                        'borderColor' => 'rgba(147, 51, 234, 1)',
                        'borderWidth' => 1,
                        'type' => 'bar'
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error("Error loading maintenance plan status data: " . $e->getMessage());
            // Empty chart structure in case of error
            $this->maintenancePlanStatusData = [
                'labels' => [],
                'datasets' => [
                    [
                        'label' => 'Total Plans',
                        'data' => [],
                        'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                        'borderColor' => 'rgba(54, 162, 235, 1)',
                        'borderWidth' => 1
                    ]
                ]
            ];
        }
    }

    /**
     * Obter distribuição de tarefas por mês (planejado vs. corretivo)
     */
    protected function getMonthlyDistributionData($baseQuery, $correctiveQuery)
    {
        $months = [
            '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
            '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Aug',
            '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec'
        ];

        $plannedData = [];
        $correctiveData = [];

        foreach ($months as $monthNum => $monthName) {
            $plannedCount = (clone $baseQuery)
                ->whereMonth('scheduled_date', $monthNum)
                ->count();

            $correctiveCount = (clone $correctiveQuery)
                ->whereMonth('created_at', $monthNum)
                ->count();

            $plannedData[] = $plannedCount;
            $correctiveData[] = $correctiveCount;
        }

        return [
            'labels' => array_values($months),
            'datasets' => [
                [
                    'label' => 'Planned Maintenance',
                    'data' => $plannedData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Corrective Maintenance',
                    'data' => $correctiveData,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.5)',
                    'borderColor' => 'rgb(239, 68, 68)',
                    'borderWidth' => 1
                ]
            ]
        ];
    }

    /**
     * Obter distribuição por status
     */
    protected function getStatusDistributionData($query)
    {
        $statusCounts = [
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
            'schedule' => (clone $query)->where('status', 'schedule')->count(),
        ];

        // Garantir que existam dados, caso contrário, o Chart.js pode falhar
        $hasData = array_sum(array_values($statusCounts)) > 0;

        if (!$hasData) {
            $statusCounts = [
                'pending' => 0,
                'in_progress' => 0,
                'completed' => 1, // Valor mínimo para mostrar o gráfico
                'cancelled' => 0,
                'schedule' => 0,
            ];
        }

        return [
            'labels' => ['Pending', 'In Progress', 'Completed', 'Cancelled', 'Scheduled'],
            'datasets' => [
                [
                    'data' => array_values($statusCounts),
                    'backgroundColor' => [
                        'rgba(251, 191, 36, 0.8)',  // pending - amber
                        'rgba(59, 130, 246, 0.8)',  // in progress - blue
                        'rgba(34, 197, 94, 0.8)',   // completed - green
                        'rgba(107, 114, 128, 0.8)', // cancelled - gray
                        'rgba(147, 51, 234, 0.8)',  // schedule - purple
                    ],
                    'borderWidth' => 1
                ]
            ]
        ];
    }

    /**
     * Obter dados para o gráfico de manutenção planejada
     */
    protected function getPlanningChartData($query)
    {
        // Initialize variables
        $types = [];
        $counts = [];
        $chartTitle = 'Maintenance Types';

        try {
            // Verificar se a coluna maintenance_type existe na tabela maintenance_plans
            if (Schema::hasColumn('maintenance_plans', 'maintenance_type')) {
                // Distribuição por tipo de manutenção (preventiva, preditiva, etc.)
                $typeDistribution = $query->select('maintenance_type', DB::raw('count(*) as count'))
                    ->whereNotNull('maintenance_type')
                    ->groupBy('maintenance_type')
                    ->pluck('count', 'maintenance_type')
                    ->toArray();

                // If no data exists with maintenance_type, try to get other meaningful data
                if (empty($typeDistribution)) {
                    // Try to categorize by frequency instead
                    if (Schema::hasColumn('maintenance_plans', 'frequency')) {
                        $typeDistribution = $query->select('frequency', DB::raw('count(*) as count'))
                            ->whereNotNull('frequency')
                            ->groupBy('frequency')
                            ->pluck('count', 'frequency')
                            ->toArray();

                        $chartTitle = 'Maintenance Frequency';
                    }
                }

                $types = array_keys($typeDistribution);
                $counts = array_values($typeDistribution);

                // Convert empty types to "Other"
                $types = array_map(function($type) {
                    return $type ?: 'Other';
                }, $types);
            } else {
                // If maintenance_type doesn't exist, use status as an alternative dimension
                $statusDistribution = $query->select('status', DB::raw('count(*) as count'))
                    ->whereNotNull('status')
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray();

                $chartTitle = 'Maintenance Status';

                // Map status codes to readable labels
                $statusLabels = [
                    'pending' => 'Pending',
                    'in_progress' => 'In Progress',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                    'schedule' => 'Scheduled'
                ];

                // Create arrays for chart
                $tempTypes = [];
                $tempCounts = [];

                foreach ($statusDistribution as $status => $count) {
                    $tempTypes[] = $statusLabels[$status] ?? ucfirst($status);
                    $tempCounts[] = $count;
                }

                $types = $tempTypes;
                $counts = $tempCounts;
            }

            // If still no data, provide default
            if (empty($types)) {
                $types = ['No data available'];
                $counts = [1];
            }
        } catch (\Exception $e) {
            Log::error("Error in getPlanningChartData: " . $e->getMessage());
            $types = ['Error loading data'];
            $counts = [1];
            $chartTitle = 'Data Error';
        }

        // Colors for the chart
        $colors = [
            'rgba(59, 130, 246, 0.8)',   // blue
            'rgba(16, 185, 129, 0.8)',   // emerald
            'rgba(139, 92, 246, 0.8)',   // purple
            'rgba(249, 115, 22, 0.8)',   // orange
            'rgba(236, 72, 153, 0.8)',   // pink
        ];

        // Fill with additional colors if needed
        while (count($colors) < count($types)) {
            $colors = array_merge($colors, $colors);
        }

        return [
            'labels' => $types,
            'datasets' => [
                [
                    'data' => $counts,
                    'backgroundColor' => array_slice($colors, 0, count($types)),
                    'borderWidth' => 1
                ]
            ],
            'chartTitle' => $chartTitle
        ];
    }

    /**
     * Obter dados para o gráfico de manutenção corretiva
     */
    protected function getCorrectiveChartData($query)
    {
        try {
            // Distribuição por causa da falha
            $failureDistribution = $query->select('failure_cause_id', DB::raw('count(*) as count'))
                ->groupBy('failure_cause_id')
                ->pluck('count', 'failure_cause_id')
                ->toArray();

            // Se não existirem dados, criar dados fake para o gráfico não quebrar
            if (empty($failureDistribution)) {
                return [
                    'labels' => ['No data available'],
                    'datasets' => [
                        [
                            'data' => [1],
                            'backgroundColor' => ['rgba(107, 114, 128, 0.8)'],
                            'borderWidth' => 1
                        ]
                    ]
                ];
            }

            // Buscar nomes das causas
            $causeIds = array_keys($failureDistribution);

            // Usar o model FailureCause para obter os nomes
            $causeNames = FailureCause::whereIn('id', $causeIds)
                ->pluck('name', 'id')
                ->toArray();

            $labels = [];
            foreach ($causeIds as $id) {
                $labels[] = $causeNames[$id] ?? "Cause #$id";
            }

            // Cores para o gráfico
            $colors = [
                'rgba(239, 68, 68, 0.8)',   // red
                'rgba(249, 115, 22, 0.8)',  // orange
                'rgba(234, 179, 8, 0.8)',   // yellow
                'rgba(16, 185, 129, 0.8)',  // emerald
                'rgba(99, 102, 241, 0.8)',  // indigo
            ];

            // Preencher com cores adicionais se necessário
            while (count($colors) < count($labels)) {
                $colors = array_merge($colors, $colors);
            }

            return [
                'labels' => $labels,
                'datasets' => [
                    [
                        'data' => array_values($failureDistribution),
                        'backgroundColor' => array_slice($colors, 0, count($labels)),
                        'borderWidth' => 1
                    ]
                ]
            ];
        } catch (\Exception $e) {
            // Log do erro e retornar dados vazios para não quebrar o frontend
            Log::error("Error loading corrective chart data: " . $e->getMessage());
            return [
                'labels' => ['Error loading data'],
                'datasets' => [
                    [
                        'data' => [1],
                        'backgroundColor' => ['rgba(239, 68, 68, 0.8)'],
                        'borderWidth' => 1
                    ]
                ]
            ];
        }
    }

    /**
     * Marcar um alerta como concluído
     */
    public function markAlertAsCompleted($alertId)
    {
        // Encontrar a tarefa no banco de dados
        $task = MaintenancePlan::find($alertId);
        if ($task) {
            // Atualizar status no banco de dados
            $task->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Adicionar nota de manutenção
            MaintenanceNote::create([
                'maintenance_plan_id' => $task->id,
                'notes' => 'Marked as completed from dashboard',
                'status' => 'completed',
                'user_id' => auth()->id(),
            ]);

            // Recarregar dados
            $this->loadDashboardData();

            session()->flash('message', 'Task marked as completed successfully!');
        }
    }

    public function hydrate()
    {
        // Disparar evento após a hidratação para garantir que os gráficos sejam renderizados
        $this->dispatch('dashboardDataLoaded');
    }

    public function render()
    {
        return view('livewire.maintenance-dashboard', [
            'equipmentCount' => $this->equipmentCount,
            'totalEquipment' => $this->totalEquipment,
            'equipmentInMaintenance' => $this->equipmentInMaintenance,
            'equipmentOutOfService' => $this->equipmentOutOfService,
            'scheduledTasks' => $this->scheduledTasks,
            'overdueTasks' => $this->overdueTasks,
            'completedTasks' => $this->completedTasks,
            'maintenanceAlerts' => $this->maintenanceAlerts,
            'planningChartData' => $this->planningChartData,
            'correctiveChartData' => $this->correctiveChartData,
            'monthlyTasksData' => $this->monthlyTasksData,
            'statusDistributionData' => $this->statusDistributionData,

            // Dados adicionais
            'plannedTasksCount' => $this->plannedTasksCount,
            'actualTasksCount' => $this->actualTasksCount,
            'pendingTasksCount' => $this->pendingTasksCount,
            'compliancePercentage' => $this->compliancePercentage,
            'nonCompliancePercentage' => $this->nonCompliancePercentage,
            'departmentTaskData' => $this->areaTaskData,
            'departmentComplianceData' => $this->areaComplianceData,
            'categoryTaskData' => $this->lineTaskData,
            'categoryComplianceData' => $this->lineComplianceData,
            'taskDescriptionData' => $this->taskDescriptionData,
            'plannedDates' => $this->plannedDates,
            'maintenancePlanStatusData' => $this->maintenancePlanStatusData
        ]);
    }
}
