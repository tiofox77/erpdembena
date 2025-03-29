<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\MaintenanceEquipment;
use App\Models\MaintenancePlan;
use App\Models\MaintenanceNote;
use App\Models\MaintenanceCorrective;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class MaintenanceDashboard extends Component
{
    public $equipmentCount;
    public $totalEquipment;
    public $equipmentInMaintenance;
    public $equipmentOutOfService;
    public $scheduledTasks;
    public $overdueTasks;
    public $completedTasks;
    public $maintenanceAlerts = [];

    // Dados para gráficos
    public $planningChartData = [];
    public $correctiveChartData = [];
    public $monthlyTasksData = [];
    public $statusDistributionData = [];

    // Filtros
    public $filterYear;
    public $filterMonth;
    public $filterStatus;
    public $filterDepartment;

    public function mount()
    {
        // Inicializar filtros com valores padrão
        $this->filterYear = date('Y');
        $this->filterMonth = 'all';
        $this->filterStatus = 'all';
        $this->filterDepartment = 'all';

        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        // Carregar contagens de equipamentos
        $this->equipmentCount = MaintenanceEquipment::count();
        $this->totalEquipment = $this->equipmentCount;

        // Verificar se a coluna 'status' existe antes de tentar usá-la
        if (Schema::hasColumn('maintenance_equipment', 'status')) {
            // Equipamento fora de serviço
            $this->equipmentOutOfService = MaintenanceEquipment::where('status', 'out_of_service')->count();

            // Equipamentos em manutenção - buscar em maintenance_plans os equipamentos com planos ativos
            $this->equipmentInMaintenance = MaintenancePlan::whereIn('status', ['pending', 'in_progress'])
                ->whereNotNull('equipment_id')
                ->distinct('equipment_id')
                ->count('equipment_id');
        } else {
            // Se a coluna não existe, definir valores padrão
            $this->equipmentOutOfService = 0;

            // Mesmo sem coluna status, podemos buscar equipamentos com manutenção planejada
            $this->equipmentInMaintenance = MaintenancePlan::whereIn('status', ['pending', 'in_progress'])
                ->whereNotNull('equipment_id')
                ->distinct('equipment_id')
                ->count('equipment_id');
        }

        // Carregar contagens de tarefas
        // Total de planos de manutenção (todos os planos)
        $this->scheduledTasks = MaintenancePlan::count();

        // Tarefas não atualizadas/vencidas (tarefas pendentes com data programada no passado)
        // Verificar primeiro se a coluna scheduled_date existe
        if (Schema::hasColumn('maintenance_plans', 'scheduled_date')) {
            $this->overdueTasks = MaintenancePlan::where('status', 'pending')
                ->where('scheduled_date', '<', now()->format('Y-m-d'))
                ->count();
        } else {
            // Se não tiver data, simplesmente contar as tarefas pendentes
            $this->overdueTasks = MaintenancePlan::where('status', 'pending')->count();
        }

        $this->completedTasks = MaintenancePlan::where('status', 'completed')->count();

        // Carregar alertas de manutenção
        $now = Carbon::now();
        $fiveDaysFromNow = $now->copy()->addDays(5);

        // Buscar tarefas pendentes que estão dentro do prazo de alerta (5 dias) ou atrasadas
        $pendingTasks = MaintenancePlan::whereIn('status', ['pending', 'in_progress'])
            ->whereNotNull('scheduled_date')
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
    }

    /**
     * Carregar dados para os gráficos com base nos filtros atuais
     */
    protected function loadChartsData()
    {
        // Inicializar query base com filtro de ano
        $baseQuery = MaintenancePlan::whereYear('scheduled_date', $this->filterYear);
        $correctiveQuery = MaintenanceCorrective::whereYear('created_at', $this->filterYear);

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

        // Aplicar filtro de departamento se não for 'all'
        if ($this->filterDepartment !== 'all') {
            $baseQuery->whereHas('equipment', function($query) {
                $query->where('department_id', $this->filterDepartment);
            });

            $correctiveQuery->whereHas('equipment', function($query) {
                $query->where('department_id', $this->filterDepartment);
            });
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
                ],
                [
                    'label' => 'Corrective Maintenance',
                    'data' => $correctiveData,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.5)',
                    'borderColor' => 'rgb(239, 68, 68)',
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
        ];

        return [
            'labels' => ['Pending', 'In Progress', 'Completed', 'Cancelled'],
            'datasets' => [
                [
                    'data' => array_values($statusCounts),
                    'backgroundColor' => [
                        'rgba(251, 191, 36, 0.8)',  // pending - amber
                        'rgba(59, 130, 246, 0.8)',  // in progress - blue
                        'rgba(34, 197, 94, 0.8)',   // completed - green
                        'rgba(107, 114, 128, 0.8)', // cancelled - gray
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
        // Distribuição por tipo de manutenção (preventiva, preditiva, etc.)
        $typeDistribution = $query->select('maintenance_type', DB::raw('count(*) as count'))
            ->groupBy('maintenance_type')
            ->pluck('count', 'maintenance_type')
            ->toArray();

        $types = array_keys($typeDistribution);
        $counts = array_values($typeDistribution);

        // Converter tipos vazios para "Other"
        $types = array_map(function($type) {
            return $type ?: 'Other';
        }, $types);

        // Cores para o gráfico
        $colors = [
            'rgba(59, 130, 246, 0.8)',   // blue
            'rgba(16, 185, 129, 0.8)',   // emerald
            'rgba(139, 92, 246, 0.8)',   // purple
            'rgba(249, 115, 22, 0.8)',   // orange
            'rgba(236, 72, 153, 0.8)',   // pink
        ];

        return [
            'labels' => $types,
            'datasets' => [
                [
                    'data' => $counts,
                    'backgroundColor' => array_slice($colors, 0, count($types)),
                    'borderWidth' => 1
                ]
            ]
        ];
    }

    /**
     * Obter dados para o gráfico de manutenção corretiva
     */
    protected function getCorrectiveChartData($query)
    {
        // Distribuição por causa da falha
        $failureDistribution = $query->select('failure_cause_id', DB::raw('count(*) as count'))
            ->groupBy('failure_cause_id')
            ->pluck('count', 'failure_cause_id')
            ->toArray();

        // Buscar nomes das causas
        $causeIds = array_keys($failureDistribution);
        $causeNames = DB::table('failure_causes')
            ->whereIn('id', $causeIds)
            ->pluck('name', 'id')
            ->toArray();

        $labels = [];
        foreach ($causeIds as $id) {
            $labels[] = $causeNames[$id] ?? "Cause #$id";
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => array_values($failureDistribution),
                    'backgroundColor' => [
                        'rgba(239, 68, 68, 0.8)',   // red
                        'rgba(249, 115, 22, 0.8)',  // orange
                        'rgba(234, 179, 8, 0.8)',   // yellow
                        'rgba(16, 185, 129, 0.8)',  // emerald
                        'rgba(99, 102, 241, 0.8)',  // indigo
                    ],
                    'borderWidth' => 1
                ]
            ]
        ];
    }

    /**
     * Atualizar dados ao mudar filtros
     */
    public function updatedFilterYear()
    {
        $this->loadChartsData();
    }

    public function updatedFilterMonth()
    {
        $this->loadChartsData();
    }

    public function updatedFilterStatus()
    {
        $this->loadChartsData();
    }

    public function updatedFilterDepartment()
    {
        $this->loadChartsData();
    }

    public function markAlertAsCompleted($alertId)
    {
        // Encontrar a tarefa no banco de dados
        $task = MaintenancePlan::find($alertId);
        if ($task) {
            // Atualizar status no banco de dados
            $task->update([
                'status' => 'completed',
                'completion_date' => now()
            ]);

            // Criar uma nota de manutenção para registrar a conclusão
            MaintenanceNote::create([
                'maintenance_plan_id' => $alertId,
                'status' => 'completed',
                'notes' => 'Tarefa marcada como concluída pelo dashboard',
                'user_id' => auth()->id(),
            ]);

            // Atualizar os alertas locais
            foreach ($this->maintenanceAlerts as $key => $alert) {
                if ($alert['id'] == $alertId) {
                    $this->maintenanceAlerts[$key]['completed'] = true;
                    break;
                }
            }

            // Atualizar contadores
            $this->overdueTasks--;
            $this->completedTasks++;

            // Atualizar gráficos
            $this->loadChartsData();

            session()->flash('message', 'Tarefa concluída com sucesso!');
        }
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
            'statusDistributionData' => $this->statusDistributionData
        ]);
    }
}
