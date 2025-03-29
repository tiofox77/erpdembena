<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\MaintenanceEquipment;
use App\Models\MaintenanceTask;
use App\Models\MaintenanceSupply;
use App\Models\MaintenanceCategory;
use App\Models\MaintenanceDepartment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MaintenanceDashboard extends Component
{
    public $filter = 'week';
    public $taskChartData = [];
    public $equipmentStatusData = [];
    public $taskCompletionRate = 0;
    public $lowStockSupplies = [];

    // Valores para métricas básicas
    public $equipmentCount = 0;
    public $pendingTasksCount = 0;
    public $overdueTasksCount = 0;
    public $completedTasksCount = 0;

    // Listas para tabelas
    public $recentTasks = [];
    public $upcomingTasks = [];
    public $equipmentRequiringMaintenance = [];

    protected $listeners = ['refresh' => '$refresh'];

    public function mount()
    {
        $this->loadMetrics();
        $this->updateChartData();
        $this->calculateTaskCompletionRate();
        $this->loadEquipmentStatusData();
        $this->loadLowStockSupplies();
        $this->loadTaskLists();
    }

    public function loadMetrics()
    {
        $this->equipmentCount = MaintenanceEquipment::count();
        $this->pendingTasksCount = MaintenanceTask::where('status', 'pending')->count();
        $this->overdueTasksCount = MaintenanceTask::where('status', 'pending')
            ->where('scheduled_date', '<', now())
            ->count();
        $this->completedTasksCount = MaintenanceTask::where('status', 'completed')->count();
    }

    public function loadTaskLists()
    {
        // Tarefas recentes (últimas 5 adicionadas)
        $this->recentTasks = MaintenanceTask::with(['equipment', 'category'])
            ->latest()
            ->limit(5)
            ->get();

        // Próximas tarefas (5 próximas pendentes ordenadas por data)
        $this->upcomingTasks = MaintenanceTask::with(['equipment', 'assignedToUser', 'category'])
            ->where('status', 'pending')
            ->where('scheduled_date', '>=', now())
            ->orderBy('scheduled_date')
            ->limit(5)
            ->get();

        // Equipamentos que precisam de manutenção
        $this->equipmentRequiringMaintenance = MaintenanceEquipment::with(['category', 'department', 'pendingTasks'])
            ->whereHas('tasks', function($query) {
                $query->where('status', 'pending');
            })
            ->limit(5)
            ->get();
    }

    public function updateFilter($filter)
    {
        $this->filter = $filter;
        $this->updateChartData();
        $this->calculateTaskCompletionRate();
    }

    public function updateChartData()
    {
        $end = Carbon::now();
        $start = $this->getStartDate();

        // Format dates based on filter
        $format = $this->getDateFormat();

        // Initialize data
        $dates = [];
        $pending = [];
        $completed = [];
        $current = clone $start;

        // Generate dates array
        while ($current <= $end) {
            $dateKey = $current->format($format);
            $dates[] = $dateKey;

            $currentEnd = $this->getEndOfPeriod(clone $current);

            // Count tasks for this period
            $pendingCount = MaintenanceTask::whereBetween('scheduled_date', [$current, $currentEnd])
                ->where('status', 'pending')
                ->count();

            $completedCount = MaintenanceTask::whereBetween('completion_date', [$current, $currentEnd])
                ->where('status', 'completed')
                ->count();

            $pending[] = $pendingCount;
            $completed[] = $completedCount;

            // Move to next period
            $current = $this->moveToNextPeriod($current);
        }

        // Prepare chart data
        $this->taskChartData = [
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Tarefas Pendentes',
                    'data' => $pending,
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                ],
                [
                    'label' => 'Tarefas Concluídas',
                    'data' => $completed,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                ],
            ],
        ];

        $this->emit('chartDataUpdated', [
            'taskChartData' => $this->taskChartData,
            'equipmentStatusData' => $this->equipmentStatusData
        ]);
    }

    private function getStartDate()
    {
        switch ($this->filter) {
            case 'week':
                return Carbon::now()->subDays(7)->startOfDay();
            case 'month':
                return Carbon::now()->subMonth()->startOfDay();
            case 'quarter':
                return Carbon::now()->subMonths(3)->startOfDay();
            case 'year':
                return Carbon::now()->subYear()->startOfDay();
            default:
                return Carbon::now()->subDays(7)->startOfDay();
        }
    }

    private function getDateFormat()
    {
        switch ($this->filter) {
            case 'week':
                return 'd/m';
            case 'month':
                return 'd/m';
            case 'quarter':
                return 'd/m';
            case 'year':
                return 'm/Y';
            default:
                return 'd/m';
        }
    }

    private function getEndOfPeriod($date)
    {
        switch ($this->filter) {
            case 'week':
                return $date->endOfDay();
            case 'month':
                return $date->endOfDay();
            case 'quarter':
                return $date->endOfDay();
            case 'year':
                return $date->endOfMonth();
            default:
                return $date->endOfDay();
        }
    }

    private function moveToNextPeriod($date)
    {
        switch ($this->filter) {
            case 'week':
                return $date->addDay();
            case 'month':
                return $date->addDay();
            case 'quarter':
                return $date->addDays(7);
            case 'year':
                return $date->addMonth();
            default:
                return $date->addDay();
        }
    }

    private function calculateTaskCompletionRate()
    {
        $start = $this->getStartDate();
        $end = Carbon::now();

        $totalTasks = MaintenanceTask::whereBetween('scheduled_date', [$start, $end])->count();

        if ($totalTasks > 0) {
            $completedTasks = MaintenanceTask::whereBetween('scheduled_date', [$start, $end])
                ->where('status', 'completed')
                ->count();

            $this->taskCompletionRate = round(($completedTasks / $totalTasks) * 100);
        } else {
            $this->taskCompletionRate = 0;
        }
    }

    private function loadEquipmentStatusData()
    {
        $active = MaintenanceEquipment::where('status', 'active')->count();
        $inMaintenance = MaintenanceEquipment::where('status', 'in_maintenance')->count();
        $inactive = MaintenanceEquipment::where('status', 'inactive')->count();
        $obsolete = MaintenanceEquipment::where('status', 'obsolete')->count();

        $this->equipmentStatusData = [
            'labels' => ['Ativo', 'Em Manutenção', 'Inativo', 'Obsoleto'],
            'datasets' => [
                [
                    'data' => [$active, $inMaintenance, $inactive, $obsolete],
                    'backgroundColor' => [
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 99, 132, 0.2)',
                    ],
                    'borderColor' => [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)',
                    ],
                ],
            ],
        ];
    }

    private function loadLowStockSupplies()
    {
        $this->lowStockSupplies = MaintenanceSupply::where('current_stock', '<=', DB::raw('minimum_stock'))
            ->where('is_active', true)
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.maintenance-dashboard');
    }
}
