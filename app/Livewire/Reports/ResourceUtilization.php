<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\MaintenanceEquipment as Equipment;
use App\Models\MaintenanceArea as Area;
use App\Models\MaintenanceLine as Line;
use App\Models\MaintenancePlan;
use App\Models\MaintenanceNote;
use App\Models\MaintenanceCorrective as Corrective;
use App\Models\User;
use App\Models\EquipmentPart;
use App\Models\EquipmentPartRequest;
use App\Models\EquipmentPartRequestItem;
use App\Models\MaintenanceSupply;
use App\Models\MaintenanceTask;
use App\Models\MaintenanceTaskLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResourceUtilization extends Component
{
    // Filters
    public $dateRange = 'month';
    public $startDate;
    public $endDate;
    public $resourceType = 'all';
    public $selectedArea = 'all';
    public $searchTerm = '';
    public $sortField = 'utilization_rate';
    public $sortDirection = 'desc';

    // Data properties
    public $areas = [];
    public $averageUtilization = 0;
    public $technicianUtilization = [];
    public $partsUtilization = [];
    public $suppliesUtilization = [];
    public $totalHoursWorked = 0;
    public $availableResources = 0;
    public $topResource = '';
    public $topUtilization = 0;
    public $resourceDetails = [];
    public $currentAllocations = [];

    // Chart data
    public $utilizationRatesData = [];
    public $utilizationTrendData = [];
    public $utilizationByAreaData = [];
    public $taskTypeDistributionData = [];

    public function mount()
    {
        // Load areas for filters
        $this->areas = Area::pluck('name', 'id')->toArray();

        // Set default date range
        $this->setDateRange($this->dateRange);

        // Load initial data
        $this->loadUtilizationData();
    }

    public function updatedDateRange()
    {
        $this->setDateRange($this->dateRange);
        $this->loadUtilizationData();
    }

    public function updatedStartDate()
    {
        $this->loadUtilizationData();
    }

    public function updatedEndDate()
    {
        $this->loadUtilizationData();
    }

    public function updatedResourceType()
    {
        $this->loadUtilizationData();
    }

    public function updatedSelectedArea()
    {
        $this->loadUtilizationData();
    }

    public function updatedSearchTerm()
    {
        $this->loadUtilizationData();
    }

    public function setDateRange($range)
    {
        $now = Carbon::now();

        switch ($range) {
            case 'week':
                $this->startDate = $now->startOfWeek()->format('Y-m-d');
                $this->endDate = $now->endOfWeek()->format('Y-m-d');
                break;
            case 'month':
                $this->startDate = $now->startOfMonth()->format('Y-m-d');
                $this->endDate = $now->endOfMonth()->format('Y-m-d');
                break;
            case 'quarter':
                $this->startDate = $now->startOfQuarter()->format('Y-m-d');
                $this->endDate = $now->endOfQuarter()->format('Y-m-d');
                break;
            case 'year':
                $this->startDate = $now->startOfYear()->format('Y-m-d');
                $this->endDate = $now->endOfYear()->format('Y-m-d');
                break;
            case 'custom':
                // Keep the existing custom dates or set defaults if empty
                if (empty($this->startDate)) {
                    $this->startDate = $now->subDays(30)->format('Y-m-d');
                }
                if (empty($this->endDate)) {
                    $this->endDate = $now->format('Y-m-d');
                }
                break;
        }
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        // Re-sort the resource details
        $this->resourceDetails = collect($this->resourceDetails)
            ->sortBy([$field => $this->sortDirection === 'asc' ? 'asc' : 'desc'])
            ->values()
            ->toArray();
    }

    public function loadUtilizationData()
    {
        try {
            // Carregar dados de diferentes tipos de recursos
            $this->loadTechnicianUtilization();  // Técnicos
            $this->loadPartsUtilization();       // Peças
            $this->loadSuppliesUtilization();    // Suprimentos
            
            // Combinar e processar todos os dados de recursos
            $this->combineResourceData();
            
            // Carregar alocações atuais e calcular métricas
            $this->loadCurrentAllocations();
            $this->calculateUtilizationRates();
            $this->calculateUtilizationTrend();
            $this->calculateUtilizationByArea();
            $this->calculateTaskTypeDistribution();
        } catch (\Exception $e) {
            Log::error('Error loading resource utilization data: ' . $e->getMessage());
            $this->setDefaultData();
        }
    }

    protected function loadTechnicianUtilization()
    {
        try {
            // Estrutura para armazenar dados de técnicos
            $technicianData = [];
            
            // 1. Obter planos de manutenção (incluindo status 'in_progress', não apenas 'completed')
            $planQuery = MaintenancePlan::whereBetween('scheduled_date', [$this->startDate, $this->endDate])
                ->whereIn('status', ['completed', 'in_progress', 'pending', 'schedule']);

            // Aplicar filtro de área se selecionado
            if ($this->selectedArea !== 'all') {
                $planQuery->whereHas('equipment', function($query) {
                    $query->where('area_id', $this->selectedArea);
                });
            }

            $plans = $planQuery->with(['assignedTo', 'equipment', 'equipment.area'])->get();

            // 2. Obter notas de manutenção (incluindo todas as notas importantes)
            $notesQuery = MaintenanceNote::whereBetween('created_at', [$this->startDate, $this->endDate])
                ->whereIn('status', ['completed', 'in_progress', 'pending'])
                ->whereHas('maintenancePlan');

            if ($this->selectedArea !== 'all') {
                $notesQuery->whereHas('maintenancePlan.equipment', function($query) {
                    $query->where('area_id', $this->selectedArea);
                });
            }

            $notes = $notesQuery->with(['user', 'maintenancePlan.equipment', 'maintenancePlan.equipment.area'])->get();

            // 3. Obter manutenções corretivas (incluindo mais status para melhor cobertura)
            $correctiveQuery = Corrective::whereBetween('start_time', [$this->startDate." 00:00:00", $this->endDate." 23:59:59"])
                ->whereIn('status', ['resolved', 'closed', 'in_progress']);

            // Aplicar filtro de área se selecionado
            if ($this->selectedArea !== 'all') {
                $correctiveQuery->whereHas('equipment', function($query) {
                    $query->where('area_id', $this->selectedArea);
                });
            }

            $correctives = $correctiveQuery->with(['resolver', 'equipment', 'equipment.area'])->get();
            
            // 4. NOVO: Buscar dados dos técnicos na tabela Technician
            $technicians = [];
            if (class_exists('\App\Models\Technician')) {
                $techQuery = \App\Models\Technician::query();
                if ($this->selectedArea !== 'all') {
                    $techQuery->where('area_id', $this->selectedArea);
                }
                $technicians = $techQuery->get();
                
                // Inicializar dados para todos os técnicos, mesmo sem tarefas atribuídas
                foreach ($technicians as $tech) {
                    $resourceType = 'technician';
                    $resourceId = $tech->id;
                    $resourceName = $tech->name;
                    $areaName = $tech->area ? $tech->area->name : 'General';
                    
                    $technicianData[$resourceType][$resourceId] = [
                        'id' => $resourceId,
                        'name' => $resourceName,
                        'type' => $resourceType,
                        'primary_area' => $areaName,
                        'hours_worked' => 0,
                        'tasks_completed' => 0,
                        'areas' => [$areaName]
                    ];
                }
            }

            // 5. Processar dados de planos de manutenção
            foreach ($plans as $plan) {
                if (!$plan->assignedTo) continue;

                $resourceType = 'technician';
                $resourceId = $plan->assignedTo->id;
                $resourceName = $plan->assignedTo->name;
                $areaName = $plan->equipment && $plan->equipment->area ? $plan->equipment->area->name : 'Unknown';
                
                // Estimar horas trabalhadas com base no tipo de tarefa
                $hoursWorked = $this->estimateHoursForMaintenancePlan($plan);
                
                // Ajustar horas com base no status (tarefas em andamento contam parcialmente)
                if ($plan->status == 'in_progress') {
                    $hoursWorked *= 0.5; // 50% do tempo estimado já foi utilizado
                } else if ($plan->status == 'pending' || $plan->status == 'schedule') {
                    $hoursWorked *= 0.25; // 25% do tempo estimado em preparação
                }

                if (!isset($technicianData[$resourceType][$resourceId])) {
                    $technicianData[$resourceType][$resourceId] = [
                        'id' => $resourceId,
                        'name' => $resourceName,
                        'type' => $resourceType,
                        'primary_area' => $areaName,
                        'hours_worked' => 0,
                        'tasks_completed' => 0,
                        'areas' => []
                    ];
                }

                $technicianData[$resourceType][$resourceId]['hours_worked'] += $hoursWorked;
                
                // Contar apenas tarefas concluídas para o contador de tarefas
                if ($plan->status == 'completed') {
                    $technicianData[$resourceType][$resourceId]['tasks_completed'] += 1;
                }

                if (!in_array($areaName, $technicianData[$resourceType][$resourceId]['areas'])) {
                    $technicianData[$resourceType][$resourceId]['areas'][] = $areaName;
                }
            }

            // 6. Processar notas de manutenção
            foreach ($notes as $note) {
                if (!$note->user) continue;

                $resourceType = 'technician';
                $resourceId = $note->user->id;
                $resourceName = $note->user->name;
                $plan = $note->maintenancePlan;
                if (!$plan || !$plan->equipment || !$plan->equipment->area) continue;

                $areaName = $plan->equipment->area->name ?? 'Unknown';
                
                // Usar duração registrada na nota ou estimativa padrão
                $hoursWorked = $note->duration_minutes ? ($note->duration_minutes / 60) : 1;

                if (!isset($technicianData[$resourceType][$resourceId])) {
                    $technicianData[$resourceType][$resourceId] = [
                        'id' => $resourceId,
                        'name' => $resourceName,
                        'type' => $resourceType,
                        'primary_area' => $areaName,
                        'hours_worked' => 0,
                        'tasks_completed' => 0,
                        'areas' => []
                    ];
                }

                $technicianData[$resourceType][$resourceId]['hours_worked'] += $hoursWorked;
                
                // Contar apenas notas concluídas para tarefas completas
                if ($note->status == 'completed') {
                    $technicianData[$resourceType][$resourceId]['tasks_completed'] += 1;
                }

                if (!in_array($areaName, $technicianData[$resourceType][$resourceId]['areas'])) {
                    $technicianData[$resourceType][$resourceId]['areas'][] = $areaName;
                }
            }

            // 7. Processar manutenções corretivas
            foreach ($correctives as $corrective) {
                if (!$corrective->resolver) continue;

                $resourceType = 'technician';
                $resourceId = $corrective->resolver->id;
                $resourceName = $corrective->resolver->name;
                $areaName = $corrective->equipment && $corrective->equipment->area ? $corrective->equipment->area->name : 'Unknown';

                // Calcular horas a partir do tempo de inatividade ou usar valor padrão
                $hoursWorked = $corrective->downtime_length ?? 2;
                if (is_string($hoursWorked) && strpos($hoursWorked, ':') !== false) {
                    // Analisar formato HH:MM:SS
                    list($hours, $minutes, $seconds) = array_pad(explode(':', $hoursWorked), 3, 0);
                    $hoursWorked = $hours + ($minutes / 60) + ($seconds / 3600);
                }
                
                // Ajustar horas com base no status
                if ($corrective->status == 'in_progress') {
                    $hoursWorked *= 0.6; // 60% do tempo já foi utilizado em tarefas em andamento
                }

                if (!isset($technicianData[$resourceType][$resourceId])) {
                    $technicianData[$resourceType][$resourceId] = [
                        'id' => $resourceId,
                        'name' => $resourceName,
                        'type' => $resourceType,
                        'primary_area' => $areaName,
                        'hours_worked' => 0,
                        'tasks_completed' => 0,
                        'areas' => []
                    ];
                }

                $technicianData[$resourceType][$resourceId]['hours_worked'] += $hoursWorked;
                
                // Contar apenas corretivas fechadas/resolvidas para o contador de tarefas
                if (in_array($corrective->status, ['resolved', 'closed'])) {
                    $technicianData[$resourceType][$resourceId]['tasks_completed'] += 1;
                }

                if (!in_array($areaName, $technicianData[$resourceType][$resourceId]['areas'])) {
                    $technicianData[$resourceType][$resourceId]['areas'][] = $areaName;
                }
            }

            // 8. Armazenar dados para combinação posterior
            $this->technicianUtilization = $technicianData;
            
            // Log de sucesso
            Log::info('Technician utilization data loaded successfully', [
                'technician_count' => isset($technicianData['technician']) ? count($technicianData['technician']) : 0
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error loading technician utilization: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->technicianUtilization = [];
        }
    }

    /**
     * Estimate hours worked for a maintenance plan based on type and priority
     */
    protected function estimateHoursForMaintenancePlan($plan)
    {
        // Use duration field if available
        if (!empty($plan->estimated_duration_minutes)) {
            return $plan->estimated_duration_minutes / 60;
        }

        // Estimate based on type and priority
        $type = strtolower($plan->type ?? '');
        $priority = strtolower($plan->priority ?? '');

        // Base estimates by type
        if (strpos($type, 'prevent') !== false) {
            $baseHours = 2; // Preventive maintenance
        } elseif (strpos($type, 'predict') !== false) {
            $baseHours = 1.5; // Predictive maintenance
        } else {
            $baseHours = 3; // Other types
        }

        // Adjust for priority
        if ($priority === 'high' || $priority === 'critical') {
            $baseHours *= 1.5;
        } elseif ($priority === 'low') {
            $baseHours *= 0.75;
        }

        return $baseHours;
    }

    protected function loadCurrentAllocations()
    {
        try {
            // Get active tasks with assigned resources
            $now = Carbon::now();

            // Get currently assigned maintenance plans
            $activeTasksQuery = MaintenancePlan::where('status', 'in_progress')
                ->whereNotNull('assigned_to');

            // Apply area filter if selected
            if ($this->selectedArea !== 'all') {
                $activeTasksQuery->whereHas('equipment', function($query) {
                    $query->where('area_id', $this->selectedArea);
                });
            }

            $activeTasks = $activeTasksQuery->with(['assignedTo', 'equipment', 'equipment.area'])->get();

            // Get currently assigned correctives
            $activeCorrectivesQuery = Corrective::where('status', 'in_progress')
                ->whereNotNull('resolved_by');

            // Apply area filter if selected
            if ($this->selectedArea !== 'all') {
                $activeCorrectivesQuery->whereHas('equipment', function($query) {
                    $query->where('area_id', $this->selectedArea);
                });
            }

            $activeCorrectives = $activeCorrectivesQuery->with(['resolver', 'equipment', 'equipment.area'])->get();

            // Process current allocations
            $allocations = [];

            // Process maintenance plans
            foreach ($activeTasks as $task) {
                if (!$task->assignedTo) continue;

                // Filter by resource type if selected
                if ($this->resourceType !== 'all' && $this->resourceType !== 'technician') {
                    continue;
                }

                // Filter by search term if provided
                if (!empty($this->searchTerm) && stripos($task->assignedTo->name, $this->searchTerm) === false) {
                    continue;
                }

                $allocations[] = [
                    'task_id' => $task->id,
                    'task_name' => $task->description ?? 'Maintenance Plan',
                    'equipment' => $task->equipment->name ?? 'Unknown',
                    'area' => $task->equipment->area->name ?? 'Unknown',
                    'priority' => $task->priority ?? 'Normal',
                    'resources' => $task->assignedTo->name,
                    'start_date' => $task->scheduled_date ? Carbon::parse($task->scheduled_date)->format('Y-m-d') : 'N/A',
                    'estimated_completion' => $task->next_maintenance_date ? Carbon::parse($task->next_maintenance_date)->format('Y-m-d') : 'N/A',
                    'status' => 'In Progress'
                ];
            }

            // Process correctives
            foreach ($activeCorrectives as $corrective) {
                if (!$corrective->resolver) continue;

                // Filter by resource type if selected
                if ($this->resourceType !== 'all' && $this->resourceType !== 'technician') {
                    continue;
                }

                // Filter by search term if provided
                if (!empty($this->searchTerm) && stripos($corrective->resolver->name, $this->searchTerm) === false) {
                    continue;
                }

                $allocations[] = [
                    'task_id' => 'C-' . $corrective->id,
                    'task_name' => 'Corrective: ' . substr($corrective->description, 0, 30) . (strlen($corrective->description) > 30 ? '...' : ''),
                    'equipment' => $corrective->equipment->name ?? 'Unknown',
                    'area' => $corrective->equipment->area->name ?? 'Unknown',
                    'priority' => $corrective->priority ?? 'High',
                    'resources' => $corrective->resolver->name,
                    'start_date' => $corrective->start_time ? Carbon::parse($corrective->start_time)->format('Y-m-d') : 'N/A',
                    'estimated_completion' => 'ASAP',
                    'status' => 'In Progress'
                ];
            }

            // Sort by priority
            $this->currentAllocations = collect($allocations)
                ->sortBy(function($item) {
                    $priority = $item['priority'];
                    if ($priority === 'High' || $priority === 'Critical') return 1;
                    if ($priority === 'Medium') return 2;
                    return 3;
                })
                ->values()
                ->toArray();

        } catch (\Exception $e) {
            Log::error('Error loading current allocations: ' . $e->getMessage());
            $this->currentAllocations = [];
        }
    }

    protected function calculateUtilizationRates()
    {
        try {
            if (empty($this->resourceDetails)) {
                $this->utilizationRatesData = $this->getDefaultChartData('No resource data available');
                return;
            }

            // Get top 10 most utilized resources
            $topResources = collect($this->resourceDetails)
                ->sortByDesc('utilization_rate')
                ->take(10)
                ->values()
                ->toArray();

            $labels = array_map(function($resource) {
                return $resource['name'];
            }, $topResources);

            $data = array_map(function($resource) {
                return $resource['utilization_rate'];
            }, $topResources);

            $colors = array_map(function($resource) {
                switch ($resource['status']) {
                    case 'Underutilized':
                        return 'rgba(255, 205, 86, 0.8)'; // Yellow
                    case 'Optimal':
                        return 'rgba(75, 192, 192, 0.8)'; // Green
                    case 'Overutilized':
                        return 'rgba(255, 99, 132, 0.8)'; // Red
                    default:
                        return 'rgba(201, 203, 207, 0.8)'; // Grey
                }
            }, $topResources);

            $borderColors = array_map(function($color) {
                return str_replace('0.8', '1', $color);
            }, $colors);

            $this->utilizationRatesData = [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Utilization Rate (%)',
                        'data' => $data,
                        'backgroundColor' => $colors,
                        'borderColor' => $borderColors,
                        'borderWidth' => 1
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating utilization rates chart: ' . $e->getMessage());
            $this->utilizationRatesData = $this->getDefaultChartData('No utilization data available');
        }
    }

    protected function calculateUtilizationTrend()
    {
        try {
            // Get date range
            $startDate = Carbon::parse($this->startDate);
            $endDate = Carbon::parse($this->endDate);
            $diffInWeeks = $endDate->diffInWeeks($startDate) + 1;

            // Decide on time grouping based on date range
            $grouping = 'weekly';
            if ($diffInWeeks <= 2) {
                $grouping = 'daily';
            } elseif ($diffInWeeks > 13) {
                $grouping = 'monthly';
            }

            // Get all completed maintenance plans
            $planQuery = MaintenancePlan::whereBetween('scheduled_date', [$this->startDate, $this->endDate])
                ->where('status', 'completed');

            // Apply area filter if selected
            if ($this->selectedArea !== 'all') {
                $planQuery->whereHas('equipment', function($query) {
                    $query->where('area_id', $this->selectedArea);
                });
            }

            $plans = $planQuery->with(['assignedTo', 'equipment', 'equipment.area'])->get();

            // Get completed corrective maintenance
            $correctiveQuery = Corrective::whereBetween('start_time', [$this->startDate." 00:00:00", $this->endDate." 23:59:59"])
                ->whereIn('status', ['resolved', 'closed']);

            // Apply area filter if selected
            if ($this->selectedArea !== 'all') {
                $correctiveQuery->whereHas('equipment', function($query) {
                    $query->where('area_id', $this->selectedArea);
                });
            }

            $correctives = $correctiveQuery->with(['resolver', 'equipment', 'equipment.area'])->get();

            // Format for period grouping
            $periods = [];
            $current = $startDate->copy();

            while ($current->lte($endDate)) {
                $periodKey = '';
                $periodEnd = null;

                if ($grouping === 'daily') {
                    $periodKey = $current->format('Y-m-d');
                    $periodLabel = $current->format('M d');
                    $periodEnd = $current->copy()->endOfDay();
                } elseif ($grouping === 'weekly') {
                    $weekStart = $current->copy()->startOfWeek();
                    $weekEnd = $current->copy()->endOfWeek();
                    if ($weekEnd->gt($endDate)) $weekEnd = $endDate->copy();

                    $periodKey = $weekStart->format('Y-W');
                    $periodLabel = 'Week ' . $weekStart->format('W');
                    $periodEnd = $weekEnd;
                    $current = $weekEnd->copy();
                } elseif ($grouping === 'monthly') {
                    $monthStart = $current->copy()->startOfMonth();
                    $monthEnd = $current->copy()->endOfMonth();
                    if ($monthEnd->gt($endDate)) $monthEnd = $endDate->copy();

                    $periodKey = $monthStart->format('Y-m');
                    $periodLabel = $monthStart->format('M Y');
                    $periodEnd = $monthEnd;
                    $current = $monthEnd->copy();
                }

                $periods[$periodKey] = [
                    'label' => $periodLabel,
                    'start' => $current->copy(),
                    'end' => $periodEnd,
                    'hours_worked' => 0,
                    'available_hours' => 0,
                ];

                $current->addDay();
            }

            // Process maintenance plans into periods
            foreach ($plans as $plan) {
                if (!$plan->assignedTo) continue;

                // Skip if resource type filter doesn't match
                if ($this->resourceType !== 'all' && $this->resourceType !== 'technician') {
                    continue;
                }

                // Filter by search term if provided
                if (!empty($this->searchTerm) && stripos($plan->assignedTo->name, $this->searchTerm) === false) {
                    continue;
                }

                $completedDate = Carbon::parse($plan->scheduled_date);
                // Estimate hours based on task type or use a default
                $hoursWorked = $this->estimateHoursForMaintenancePlan($plan);

                // Find the right period
                foreach ($periods as $key => &$period) {
                    if ($completedDate->between($period['start'], $period['end'])) {
                        $period['hours_worked'] += $hoursWorked;
                        break;
                    }
                }
            }

            // Process correctives into periods
            foreach ($correctives as $corrective) {
                if (!$corrective->resolver) continue;
                if (!$corrective->start_time) continue;

                // Skip if resource type filter doesn't match
                if ($this->resourceType !== 'all' && $this->resourceType !== 'technician') {
                    continue;
                }

                // Filter by search term if provided
                if (!empty($this->searchTerm) && stripos($corrective->resolver->name, $this->searchTerm) === false) {
                    continue;
                }

                $startTime = Carbon::parse($corrective->start_time);

                // Calculate hours from downtime or if not available, use 2 hours as default
                $hoursWorked = $corrective->downtime_length ?? 2;
                if (is_string($hoursWorked) && strpos($hoursWorked, ':') !== false) {
                    // Parse HH:MM:SS format
                    list($hours, $minutes, $seconds) = array_pad(explode(':', $hoursWorked), 3, 0);
                    $hoursWorked = $hours + ($minutes / 60) + ($seconds / 3600);
                }

                // Find the right period
                foreach ($periods as $key => &$period) {
                    if ($startTime->between($period['start'], $period['end'])) {
                        $period['hours_worked'] += $hoursWorked;
                        break;
                    }
                }
            }

            // Calculate available hours per period
            foreach ($periods as $key => &$period) {
                $workingDays = $this->getWorkingDaysBetweenDates($period['start'], $period['end']);
                $standardHoursPerDay = 8; // Assuming 8-hour workday
                $technicianCount = User::where('role', 'technician')->count();
                $technicianCount = max($technicianCount, 1); // Ensure at least 1

                $period['available_hours'] = $workingDays * $standardHoursPerDay * $technicianCount;
            }

            // Format chart data
            $labels = array_column($periods, 'label');
            $utilizationRates = [];

            foreach ($periods as $period) {
                $rate = $period['available_hours'] > 0 ?
                    min(($period['hours_worked'] / $period['available_hours']) * 100, 100) : 0;
                $utilizationRates[] = round($rate, 1);
            }

            $this->utilizationTrendData = [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Utilization Rate (%)',
                        'data' => $utilizationRates,
                        'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                        'borderColor' => 'rgba(54, 162, 235, 1)',
                        'borderWidth' => 2,
                        'tension' => 0.4,
                        'fill' => true
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating utilization trend: ' . $e->getMessage());
            $this->utilizationTrendData = $this->getDefaultChartData('No trend data available');
        }
    }

    protected function calculateUtilizationByArea()
    {
        try {
            if (empty($this->resourceDetails)) {
                $this->utilizationByAreaData = $this->getDefaultChartData('No area data available');
                return;
            }

            // Group resources by primary area
            $areaUtilization = [];

            foreach ($this->resourceDetails as $resource) {
                $area = $resource['primary_area'];

                if (!isset($areaUtilization[$area])) {
                    $areaUtilization[$area] = [
                        'hours_worked' => 0,
                        'available_hours' => 0,
                        'resources' => 0
                    ];
                }

                $areaUtilization[$area]['hours_worked'] += $resource['hours_worked'];
                $areaUtilization[$area]['available_hours'] += $resource['available_hours'];
                $areaUtilization[$area]['resources']++;
            }

            // Calculate utilization rate per area
            $areas = [];
            $rates = [];

            foreach ($areaUtilization as $area => $data) {
                $areas[] = $area;
                $rate = $data['available_hours'] > 0 ?
                    min(($data['hours_worked'] / $data['available_hours']) * 100, 100) : 0;
                $rates[] = round($rate, 1);
            }

            $this->utilizationByAreaData = [
                'labels' => $areas,
                'datasets' => [
                    [
                        'label' => 'Utilization Rate (%)',
                        'data' => $rates,
                        'backgroundColor' => 'rgba(255, 99, 132, 0.8)',
                        'borderColor' => 'rgba(255, 99, 132, 1)',
                        'borderWidth' => 1
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating utilization by area: ' . $e->getMessage());
            $this->utilizationByAreaData = $this->getDefaultChartData('No area data available');
        }
    }

    protected function calculateTaskTypeDistribution()
    {
        try {
            // Get completed maintenance plans within date range
            $planQuery = MaintenancePlan::whereBetween('scheduled_date', [$this->startDate, $this->endDate])
                ->where('status', 'completed');

            // Apply area filter if selected
            if ($this->selectedArea !== 'all') {
                $planQuery->whereHas('equipment', function($query) {
                    $query->where('area_id', $this->selectedArea);
                });
            }

            $plans = $planQuery->with(['assignedTo'])->get();

            // Get corrective maintenance within date range
            $correctiveQuery = Corrective::whereBetween('start_time', [$this->startDate." 00:00:00", $this->endDate." 23:59:59"])
                ->whereIn('status', ['resolved', 'closed']);

            // Apply area filter if selected
            if ($this->selectedArea !== 'all') {
                $correctiveQuery->whereHas('equipment', function($query) {
                    $query->where('area_id', $this->selectedArea);
                });
            }

            $correctives = $correctiveQuery->with(['resolver'])->get();

            // Apply resource type and search filters
            if ($this->resourceType !== 'all' || !empty($this->searchTerm)) {
                $plans = $plans->filter(function($plan) {
                    if (!$plan->assignedTo) return false;

                    if ($this->resourceType !== 'all' && $this->resourceType !== 'technician') {
                        return false;
                    }

                    if (!empty($this->searchTerm) && stripos($plan->assignedTo->name, $this->searchTerm) === false) {
                        return false;
                    }

                    return true;
                });

                $correctives = $correctives->filter(function($corrective) {
                    if (!$corrective->resolver) return false;

                    if ($this->resourceType !== 'all' && $this->resourceType !== 'technician') {
                        return false;
                    }

                    if (!empty($this->searchTerm) && stripos($corrective->resolver->name, $this->searchTerm) === false) {
                        return false;
                    }

                    return true;
                });
            }

            // Count tasks by type
            $preventivePlans = $plans->filter(function($plan) {
                return $plan->type && (strcasecmp($plan->type, 'preventive') === 0 ||
                    stripos($plan->type, 'prevent') !== false);
            })->count();

            $predictivePlans = $plans->filter(function($plan) {
                return $plan->type && (strcasecmp($plan->type, 'predictive') === 0 ||
                    stripos($plan->type, 'predict') !== false);
            })->count();

            $inspectionPlans = $plans->filter(function($plan) {
                return $plan->type && strcasecmp($plan->type, 'inspection') === 0;
            })->count();

            $otherPlans = $plans->filter(function($plan) {
                return !$plan->type ||
                    (stripos($plan->type, 'prevent') === false &&
                     stripos($plan->type, 'predict') === false &&
                     strcasecmp($plan->type, 'inspection') !== 0);
            })->count();

            $taskTypeCounts = [
                'Preventive' => $preventivePlans,
                'Corrective' => $correctives->count(),
                'Predictive' => $predictivePlans,
                'Inspection' => $inspectionPlans,
                'Other' => $otherPlans
            ];

            // Format chart data
            $colors = [
                'Preventive' => 'rgba(54, 162, 235, 0.8)',
                'Corrective' => 'rgba(255, 99, 132, 0.8)',
                'Predictive' => 'rgba(75, 192, 192, 0.8)',
                'Inspection' => 'rgba(255, 205, 86, 0.8)',
                'Other' => 'rgba(201, 203, 207, 0.8)'
            ];

            $borderColors = array_map(function($color) {
                return str_replace('0.8', '1', $color);
            }, $colors);

            $this->taskTypeDistributionData = [
                'labels' => array_keys($taskTypeCounts),
                'datasets' => [
                    [
                        'data' => array_values($taskTypeCounts),
                        'backgroundColor' => array_values($colors),
                        'borderColor' => array_values($borderColors),
                        'borderWidth' => 1
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating task type distribution: ' . $e->getMessage());
            $this->taskTypeDistributionData = $this->getDefaultChartData('No task type data available');
        }
    }

    protected function getUtilizationStatus($rate)
    {
        if ($rate < 40) {
            return 'Underutilized';
        } elseif ($rate <= 85) {
            return 'Optimal';
        } else {
            return 'Overutilized';
        }
    }

    protected function getWorkingDaysBetweenDates($startDate, $endDate)
    {
        $days = 0;
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            // Skip weekends (assuming 6 = Saturday, 0 = Sunday)
            if ($current->dayOfWeek !== 0 && $current->dayOfWeek !== 6) {
                $days++;
            }
            $current->addDay();
        }

        return $days;
    }

    protected function getDefaultChartData($label = 'No data available')
    {
        return [
            'labels' => [$label],
            'datasets' => [
                [
                    'label' => 'No Data',
                    'data' => [0],
                    'backgroundColor' => 'rgba(201, 203, 207, 0.8)',
                    'borderColor' => 'rgba(201, 203, 207, 1)',
                    'borderWidth' => 1
                ]
            ]
        ];
    }

    /**
     * Load part utilization data from EquipmentPart and related models
     * @return void
     */
    protected function loadPartsUtilization()
    {
        try {
            // Estrutura para armazenar dados de peças
            $partsData = [];

            // 1. Buscar requisições de peças no período
            $requestsQuery = EquipmentPartRequest::whereBetween('created_at', [$this->startDate, $this->endDate])
                ->whereIn('status', ['approved', 'delivered', 'partial']);
                
            if ($this->selectedArea !== 'all') {
                $requestsQuery->whereHas('equipment', function($query) {
                    $query->where('area_id', $this->selectedArea);
                });
            }
            
            $requests = $requestsQuery->with(['items.part', 'equipment', 'equipment.area'])->get();
            
            // 2. Processar itens de requisição para calcular utilização de peças
            foreach ($requests as $request) {
                if (!$request->items || $request->items->isEmpty()) continue;
                
                foreach ($request->items as $item) {
                    if (!$item->part) continue;
                    
                    $partId = $item->part_id;
                    $partName = $item->part->name ?? 'Part #' . $partId;
                    $quantity = $item->quantity;
                    $areaName = $request->equipment && $request->equipment->area ? $request->equipment->area->name : 'Unknown';
                    
                    // Considerar apenas peças aprovadas/entregues
                    if (in_array($request->status, ['approved', 'delivered', 'partial'])) {
                        if (!isset($partsData['part'][$partId])) {
                            $partsData['part'][$partId] = [
                                'id' => $partId,
                                'name' => $partName,
                                'type' => 'part',
                                'primary_area' => $areaName,
                                'quantity_used' => 0,
                                'requests_count' => 0,
                                'cost_total' => 0,
                                'areas' => []
                            ];
                        }
                        
                        $partsData['part'][$partId]['quantity_used'] += $quantity;
                        $partsData['part'][$partId]['requests_count'] += 1;
                        
                        // Calcular custo se disponível
                        if ($item->cost_per_unit) {
                            $partsData['part'][$partId]['cost_total'] += ($item->cost_per_unit * $quantity);
                        } else if ($item->part && $item->part->cost) {
                            $partsData['part'][$partId]['cost_total'] += ($item->part->cost * $quantity);
                        }
                        
                        // Registrar áreas onde a peça foi utilizada
                        if (!in_array($areaName, $partsData['part'][$partId]['areas'])) {
                            $partsData['part'][$partId]['areas'][] = $areaName;
                        }
                    }
                }
            }
            
            // 3. Buscar peças mais utilizadas em planos de manutenção
            $tasksWithPartsQuery = MaintenanceTask::whereBetween('created_at', [$this->startDate, $this->endDate])
                ->where('type', 'maintenance')
                ->where('parts_required', true);
                
            if ($this->selectedArea !== 'all') {
                $tasksWithPartsQuery->whereHas('maintenancePlan.equipment', function($query) {
                    $query->where('area_id', $this->selectedArea);
                });
            }
            
            $tasksWithParts = $tasksWithPartsQuery->with(['maintenancePlan.equipment.area'])->get();
            
            foreach ($tasksWithParts as $task) {
                // Se a tarefa tem dados de peças no formato JSON
                if ($task->parts_data && is_string($task->parts_data)) {
                    $taskPartsData = json_decode($task->parts_data, true);
                    
                    if (is_array($taskPartsData)) {
                        foreach ($taskPartsData as $partData) {
                            $partId = $partData['id'] ?? 0;
                            $partName = $partData['name'] ?? 'Unknown Part';
                            $quantity = $partData['quantity'] ?? 1;
                            
                            if ($partId > 0) {
                                $areaName = $task->maintenancePlan && $task->maintenancePlan->equipment && 
                                           $task->maintenancePlan->equipment->area ? 
                                           $task->maintenancePlan->equipment->area->name : 'Unknown';
                                           
                                if (!isset($partsData['part'][$partId])) {
                                    $partsData['part'][$partId] = [
                                        'id' => $partId,
                                        'name' => $partName,
                                        'type' => 'part',
                                        'primary_area' => $areaName,
                                        'quantity_used' => 0,
                                        'requests_count' => 0,
                                        'cost_total' => 0,
                                        'areas' => []
                                    ];
                                }
                                
                                $partsData['part'][$partId]['quantity_used'] += $quantity;
                                $partsData['part'][$partId]['requests_count'] += 1;
                                
                                if (!in_array($areaName, $partsData['part'][$partId]['areas'])) {
                                    $partsData['part'][$partId]['areas'][] = $areaName;
                                }
                            }
                        }
                    }
                }
            }
            
            // 4. Adicionar peças cadastradas, mesmo que não tenham sido utilizadas
            if ($this->partsUtilization === null || empty($this->partsUtilization)) {
                $partsQuery = EquipmentPart::query();
                
                if ($this->selectedArea !== 'all') {
                    $partsQuery->whereHas('equipment', function($query) {
                        $query->where('area_id', $this->selectedArea);
                    });
                }
                
                $parts = $partsQuery->with(['equipment.area'])->limit(50)->get();
                
                foreach ($parts as $part) {
                    $partId = $part->id;
                    
                    if (!isset($partsData['part'][$partId])) {
                        $areaName = $part->equipment && $part->equipment->area ? 
                                     $part->equipment->area->name : 'General';
                                     
                        $partsData['part'][$partId] = [
                            'id' => $partId,
                            'name' => $part->name,
                            'type' => 'part',
                            'primary_area' => $areaName,
                            'quantity_used' => 0,
                            'requests_count' => 0,
                            'cost_total' => 0,
                            'areas' => [$areaName]
                        ];
                    }
                }
            }
            
            // 5. Armazenar dados de peças
            $this->partsUtilization = $partsData;
            
            // Log de sucesso
            Log::info('Parts utilization data loaded successfully', [
                'parts_count' => isset($partsData['part']) ? count($partsData['part']) : 0
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error loading parts utilization: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->partsUtilization = [];
        }
    }
    
    /**
     * Load supplies utilization data
     * @return void
     */
    protected function loadSuppliesUtilization()
    {
        try {
            // Estrutura para armazenar dados de suprimentos
            $suppliesData = [];
            
            // 1. Buscar registros de utilização de suprimentos em manutenções
            $suppliesQuery = MaintenanceSupply::whereBetween('date_used', [$this->startDate, $this->endDate]);
                
            if ($this->selectedArea !== 'all') {
                $suppliesQuery->whereHas('maintenancePlan.equipment', function($query) {
                    $query->where('area_id', $this->selectedArea);
                });
            }
            
            $supplies = $suppliesQuery->with(['maintenancePlan.equipment.area'])->get();
            
            // 2. Processar dados de suprimentos
            foreach ($supplies as $supply) {
                $supplyId = $supply->id;
                $supplyName = $supply->name;
                $quantity = $supply->quantity;
                $areaName = $supply->maintenancePlan && $supply->maintenancePlan->equipment && 
                           $supply->maintenancePlan->equipment->area ? 
                           $supply->maintenancePlan->equipment->area->name : 'Unknown';
                
                // Agrupar por tipo de suprimento para análise
                $supplyTypeId = $supply->supply_type_id ?? 0;
                $supplyType = $supply->supplyType ? $supply->supplyType->name : 'Geral';
                
                if (!isset($suppliesData['supply'][$supplyTypeId])) {
                    $suppliesData['supply'][$supplyTypeId] = [
                        'id' => $supplyTypeId,
                        'name' => $supplyType,
                        'type' => 'supply',
                        'primary_area' => $areaName,
                        'quantity_used' => 0,
                        'tasks_supported' => 0,
                        'cost_total' => 0,
                        'areas' => []
                    ];
                }
                
                $suppliesData['supply'][$supplyTypeId]['quantity_used'] += $quantity;
                $suppliesData['supply'][$supplyTypeId]['tasks_supported'] += 1;
                
                // Calcular custo se disponível
                if ($supply->cost) {
                    $suppliesData['supply'][$supplyTypeId]['cost_total'] += ($supply->cost * $quantity);
                }
                
                // Registrar áreas onde o suprimento foi utilizado
                if (!in_array($areaName, $suppliesData['supply'][$supplyTypeId]['areas'])) {
                    $suppliesData['supply'][$supplyTypeId]['areas'][] = $areaName;
                }
            }
            
            // 3. Incluir suprimentos utilizados em tarefas
            $tasksQuery = MaintenanceTaskLog::whereBetween('created_at', [$this->startDate, $this->endDate])
                ->where('supplies_used', true);
                
            if ($this->selectedArea !== 'all') {
                $tasksQuery->whereHas('task.maintenancePlan.equipment', function($query) {
                    $query->where('area_id', $this->selectedArea);
                });
            }
            
            $tasks = $tasksQuery->with(['task.maintenancePlan.equipment.area'])->get();
            
            foreach ($tasks as $taskLog) {
                // Se o log tem dados de suprimentos em formato JSON
                if ($taskLog->supplies_data && is_string($taskLog->supplies_data)) {
                    $taskSupplies = json_decode($taskLog->supplies_data, true);
                    
                    if (is_array($taskSupplies)) {
                        foreach ($taskSupplies as $supplyData) {
                            $supplyTypeId = $supplyData['type_id'] ?? 0;
                            $supplyName = $supplyData['name'] ?? 'Unknown Supply';
                            $quantity = $supplyData['quantity'] ?? 1;
                            
                            $areaName = $taskLog->task && $taskLog->task->maintenancePlan && 
                                       $taskLog->task->maintenancePlan->equipment && 
                                       $taskLog->task->maintenancePlan->equipment->area ? 
                                       $taskLog->task->maintenancePlan->equipment->area->name : 'Unknown';
                            
                            if ($supplyTypeId > 0) {
                                if (!isset($suppliesData['supply'][$supplyTypeId])) {
                                    $suppliesData['supply'][$supplyTypeId] = [
                                        'id' => $supplyTypeId,
                                        'name' => $supplyName,
                                        'type' => 'supply',
                                        'primary_area' => $areaName,
                                        'quantity_used' => 0,
                                        'tasks_supported' => 0,
                                        'cost_total' => 0,
                                        'areas' => []
                                    ];
                                }
                                
                                $suppliesData['supply'][$supplyTypeId]['quantity_used'] += $quantity;
                                $suppliesData['supply'][$supplyTypeId]['tasks_supported'] += 1;
                                
                                // Calcular custo se disponível
                                if (isset($supplyData['cost']) && $supplyData['cost'] > 0) {
                                    $suppliesData['supply'][$supplyTypeId]['cost_total'] += ($supplyData['cost'] * $quantity);
                                }
                                
                                // Registrar áreas
                                if (!in_array($areaName, $suppliesData['supply'][$supplyTypeId]['areas'])) {
                                    $suppliesData['supply'][$supplyTypeId]['areas'][] = $areaName;
                                }
                            }
                        }
                    }
                }
            }
            
            // 4. Armazenar dados de suprimentos
            $this->suppliesUtilization = $suppliesData;
            
            // Log de sucesso
            Log::info('Supplies utilization data loaded successfully', [
                'supplies_count' => isset($suppliesData['supply']) ? count($suppliesData['supply']) : 0
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error loading supplies utilization: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->suppliesUtilization = [];
        }
    }

    /**
     * Combine all resource data for display and analysis
     * @return void
     */
    protected function combineResourceData()
    {
        try {
            // Estrutura para armazenar todos os dados de recursos combinados
            $combinedData = [];
            
            // 1. Adicionar dados de técnicos se disponíveis
            if (!empty($this->technicianUtilization)) {
                foreach ($this->technicianUtilization as $type => $resources) {
                    foreach ($resources as $resource) {
                        // Pular se o tipo de recurso estiver filtrado
                        if ($this->resourceType !== 'all' && $type !== $this->resourceType) {
                            continue;
                        }
                        
                        // Pular se o termo de busca não corresponder
                        if (!empty($this->searchTerm) && stripos($resource['name'], $this->searchTerm) === false) {
                            continue;
                        }
                        
                        $combinedData[] = [
                            'id' => $resource['id'],
                            'name' => $resource['name'],
                            'type' => $resource['type'],
                            'primary_area' => $resource['primary_area'],
                            'areas' => is_array($resource['areas']) ? implode(', ', $resource['areas']) : $resource['areas'],
                            'hours_worked' => $resource['hours_worked'],
                            'tasks_completed' => $resource['tasks_completed'],
                            'utilization_rate' => 0, // Será calculado posteriormente
                            'status' => '' // Será definido posteriormente
                        ];
                    }
                }
            }
            
            // 2. Adicionar dados de peças se disponíveis e o tipo não estiver filtrado
            if (!empty($this->partsUtilization) && ($this->resourceType === 'all' || $this->resourceType === 'part')) {
                foreach ($this->partsUtilization as $type => $resources) {
                    foreach ($resources as $resource) {
                        // Pular se o termo de busca não corresponder
                        if (!empty($this->searchTerm) && stripos($resource['name'], $this->searchTerm) === false) {
                            continue;
                        }
                        
                        $combinedData[] = [
                            'id' => $resource['id'],
                            'name' => $resource['name'],
                            'type' => $resource['type'],
                            'primary_area' => $resource['primary_area'],
                            'areas' => is_array($resource['areas']) ? implode(', ', $resource['areas']) : $resource['areas'],
                            'quantity_used' => $resource['quantity_used'],
                            'requests_count' => $resource['requests_count'],
                            'cost_total' => $resource['cost_total'],
                            'utilization_rate' => 0, // Será calculado de forma diferente para peças
                            'status' => '' // Será definido posteriormente
                        ];
                    }
                }
            }
            
            // 3. Adicionar dados de suprimentos se disponíveis e o tipo não estiver filtrado
            if (!empty($this->suppliesUtilization) && ($this->resourceType === 'all' || $this->resourceType === 'supply')) {
                foreach ($this->suppliesUtilization as $type => $resources) {
                    foreach ($resources as $resource) {
                        // Pular se o termo de busca não corresponder
                        if (!empty($this->searchTerm) && stripos($resource['name'], $this->searchTerm) === false) {
                            continue;
                        }
                        
                        $combinedData[] = [
                            'id' => $resource['id'],
                            'name' => $resource['name'],
                            'type' => $resource['type'],
                            'primary_area' => $resource['primary_area'],
                            'areas' => is_array($resource['areas']) ? implode(', ', $resource['areas']) : $resource['areas'],
                            'quantity_used' => $resource['quantity_used'],
                            'tasks_supported' => $resource['tasks_supported'],
                            'cost_total' => $resource['cost_total'],
                            'utilization_rate' => 0, // Será calculado posteriormente
                            'status' => '' // Será definido posteriormente
                        ];
                    }
                }
            }
            
            // 4. Ordenar dados combinados conforme configuração
            if (!empty($combinedData)) {
                // Função de comparação para ordenação
                $direction = $this->sortDirection === 'asc' ? 1 : -1;
                
                usort($combinedData, function($a, $b) use ($direction) {
                    $field = $this->sortField;
                    
                    // Verificar se ambos os itens possuem o campo
                    if (!isset($a[$field]) || !isset($b[$field])) {
                        // Tentar campos alternativos com base no tipo
                        if ($a['type'] === 'technician' && $b['type'] === 'technician') {
                            $field = 'hours_worked';
                        } elseif (($a['type'] === 'part' || $a['type'] === 'supply') && 
                                 ($b['type'] === 'part' || $b['type'] === 'supply')) {
                            $field = 'quantity_used';
                        } else {
                            // Tipos diferentes, ordenar por tipo e nome
                            if ($a['type'] !== $b['type']) {
                                return strcmp($a['type'], $b['type']) * $direction;
                            } else {
                                return strcmp($a['name'], $b['name']) * $direction;
                            }
                        }
                    }
                    
                    // Ordenar por campo numérico
                    if (is_numeric($a[$field]) && is_numeric($b[$field])) {
                        return ($a[$field] - $b[$field]) * $direction;
                    }
                    
                    // Ordenar por string
                    return strcmp($a[$field], $b[$field]) * $direction;
                });
            }
            
            // 5. Calcular totais
            $totalHoursWorked = 0;
            $totalQuantityUsed = 0;
            $totalCost = 0;
            
            foreach ($combinedData as $resource) {
                if ($resource['type'] === 'technician' && isset($resource['hours_worked'])) {
                    $totalHoursWorked += $resource['hours_worked'];
                }
                
                if (($resource['type'] === 'part' || $resource['type'] === 'supply') && isset($resource['quantity_used'])) {
                    $totalQuantityUsed += $resource['quantity_used'];
                }
                
                if (($resource['type'] === 'part' || $resource['type'] === 'supply') && isset($resource['cost_total'])) {
                    $totalCost += $resource['cost_total'];
                }
            }
            
            // 6. Armazenar dados e métricas
            $this->resourceDetails = $combinedData;
            $this->totalHoursWorked = $totalHoursWorked;
            $this->availableResources = count($combinedData);
            
            // Identificar recurso mais utilizado
            if (!empty($combinedData)) {
                $this->topResource = $combinedData[0]['name'];
            } else {
                $this->topResource = __('messages.none');
            }
            
            // Log de sucesso
            Log::info('Combined resource data prepared successfully', [
                'resource_count' => $this->availableResources,
                'total_hours' => $this->totalHoursWorked,
                'total_quantity' => $totalQuantityUsed,
                'total_cost' => $totalCost
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error combining resource data: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->setDefaultResourceData();
        }
    }

    /**
     * Set default data when there's an error loading resources
     */
    protected function setDefaultResourceData()
    {
        $this->resourceDetails = [];
        $this->totalHoursWorked = 0;
        $this->availableResources = 0;
        $this->topResource = __('messages.none');
        $this->topUtilization = 0;
    }

    protected function setDefaultData()
    {
        $this->setDefaultResourceData();
        $this->currentAllocations = [];
        $this->utilizationRatesData = $this->getDefaultChartData();
        $this->utilizationTrendData = $this->getDefaultChartData();
        $this->utilizationByAreaData = $this->getDefaultChartData();
        $this->taskTypeDistributionData = $this->getDefaultChartData();
    }

    public function render()
    {
        return view('livewire.reports.resource-utilization', [
            'areas' => $this->areas,
            'averageUtilization' => $this->averageUtilization,
            'totalHoursWorked' => $this->totalHoursWorked,
            'availableResources' => $this->availableResources,
            'topResource' => $this->topResource,
            'topUtilization' => $this->topUtilization,
            'resourceDetails' => $this->resourceDetails,
            'currentAllocations' => $this->currentAllocations,
            'utilizationRatesData' => $this->utilizationRatesData,
            'utilizationTrendData' => $this->utilizationTrendData,
            'utilizationByAreaData' => $this->utilizationByAreaData,
            'taskTypeDistributionData' => $this->taskTypeDistributionData
        ]);
    }
}
