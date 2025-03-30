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
            $this->loadResourceUtilization();
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

    protected function loadResourceUtilization()
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

            $plans = $planQuery->with(['assignedTo', 'equipment', 'equipment.area'])->get();

            // Get completed maintenance notes
            $notesQuery = MaintenanceNote::whereBetween('created_at', [$this->startDate, $this->endDate])
                ->where('status', 'completed')
                ->whereHas('maintenancePlan');

            if ($this->selectedArea !== 'all') {
                $notesQuery->whereHas('maintenancePlan.equipment', function($query) {
                    $query->where('area_id', $this->selectedArea);
                });
            }

            $notes = $notesQuery->with(['user', 'maintenancePlan.equipment', 'maintenancePlan.equipment.area'])->get();

            // Get corrective maintenance within date range
            $correctiveQuery = Corrective::whereBetween('start_time', [$this->startDate." 00:00:00", $this->endDate." 23:59:59"])
                ->whereIn('status', ['resolved', 'closed']);

            // Apply area filter if selected
            if ($this->selectedArea !== 'all') {
                $correctiveQuery->whereHas('equipment', function($query) {
                    $query->where('area_id', $this->selectedArea);
                });
            }

            $correctives = $correctiveQuery->with(['resolver', 'equipment', 'equipment.area'])->get();

            // Process resource utilization data
            $resourceData = [];

            // Process maintenance plans
            foreach ($plans as $plan) {
                if (!$plan->assignedTo) continue;

                $resourceType = 'technician';
                $resourceId = $plan->assignedTo->id;
                $resourceName = $plan->assignedTo->name;
                $areaName = $plan->equipment->area->name ?? 'Unknown';
                // Estimate hours based on task type or use a default
                $hoursWorked = $this->estimateHoursForMaintenancePlan($plan);

                if (!isset($resourceData[$resourceType][$resourceId])) {
                    $resourceData[$resourceType][$resourceId] = [
                        'id' => $resourceId,
                        'name' => $resourceName,
                        'type' => $resourceType,
                        'primary_area' => $areaName,
                        'hours_worked' => 0,
                        'tasks_completed' => 0,
                        'areas' => []
                    ];
                }

                $resourceData[$resourceType][$resourceId]['hours_worked'] += $hoursWorked;
                $resourceData[$resourceType][$resourceId]['tasks_completed'] += 1;

                if (!in_array($areaName, $resourceData[$resourceType][$resourceId]['areas'])) {
                    $resourceData[$resourceType][$resourceId]['areas'][] = $areaName;
                }
            }

            // Process maintenance notes
            foreach ($notes as $note) {
                if (!$note->user) continue;

                $resourceType = 'technician';
                $resourceId = $note->user->id;
                $resourceName = $note->user->name;
                $plan = $note->maintenancePlan;
                if (!$plan || !$plan->equipment || !$plan->equipment->area) continue;

                $areaName = $plan->equipment->area->name ?? 'Unknown';
                // Default time spent on notes is 1 hour unless specified
                $hoursWorked = 1;

                if (!isset($resourceData[$resourceType][$resourceId])) {
                    $resourceData[$resourceType][$resourceId] = [
                        'id' => $resourceId,
                        'name' => $resourceName,
                        'type' => $resourceType,
                        'primary_area' => $areaName,
                        'hours_worked' => 0,
                        'tasks_completed' => 0,
                        'areas' => []
                    ];
                }

                $resourceData[$resourceType][$resourceId]['hours_worked'] += $hoursWorked;
                $resourceData[$resourceType][$resourceId]['tasks_completed'] += 1;

                if (!in_array($areaName, $resourceData[$resourceType][$resourceId]['areas'])) {
                    $resourceData[$resourceType][$resourceId]['areas'][] = $areaName;
                }
            }

            // Process correctives
            foreach ($correctives as $corrective) {
                if (!$corrective->resolver) continue;

                $resourceType = 'technician';
                $resourceId = $corrective->resolver->id;
                $resourceName = $corrective->resolver->name;
                $areaName = $corrective->equipment->area->name ?? 'Unknown';

                // Calculate hours from downtime or if not available, use 2 hours as default
                $hoursWorked = $corrective->downtime_length ?? 2;
                if (is_string($hoursWorked) && strpos($hoursWorked, ':') !== false) {
                    // Parse HH:MM:SS format
                    list($hours, $minutes, $seconds) = array_pad(explode(':', $hoursWorked), 3, 0);
                    $hoursWorked = $hours + ($minutes / 60) + ($seconds / 3600);
                }

                if (!isset($resourceData[$resourceType][$resourceId])) {
                    $resourceData[$resourceType][$resourceId] = [
                        'id' => $resourceId,
                        'name' => $resourceName,
                        'type' => $resourceType,
                        'primary_area' => $areaName,
                        'hours_worked' => 0,
                        'tasks_completed' => 0,
                        'areas' => []
                    ];
                }

                $resourceData[$resourceType][$resourceId]['hours_worked'] += $hoursWorked;
                $resourceData[$resourceType][$resourceId]['tasks_completed'] += 1;

                if (!in_array($areaName, $resourceData[$resourceType][$resourceId]['areas'])) {
                    $resourceData[$resourceType][$resourceId]['areas'][] = $areaName;
                }
            }

            // Filter by resource type if selected
            if ($this->resourceType !== 'all') {
                foreach ($resourceData as $type => $resources) {
                    if ($type !== $this->resourceType) {
                        unset($resourceData[$type]);
                    }
                }
            }

            // Filter by search term if provided
            if (!empty($this->searchTerm)) {
                foreach ($resourceData as $type => $resources) {
                    foreach ($resources as $id => $resource) {
                        if (stripos($resource['name'], $this->searchTerm) === false) {
                            unset($resourceData[$type][$id]);
                        }
                    }
                }
            }

            // Flatten and transform data for display
            $flattenedData = [];
            foreach ($resourceData as $type => $resources) {
                foreach ($resources as $resource) {
                    $flattenedData[] = [
                        'id' => $resource['id'],
                        'name' => $resource['name'],
                        'type' => $resource['type'],
                        'primary_area' => $resource['primary_area'],
                        'areas' => implode(', ', $resource['areas']),
                        'hours_worked' => $resource['hours_worked'],
                        'tasks_completed' => $resource['tasks_completed'],
                        'utilization_rate' => 0, // Will be calculated later
                        'status' => '' // Will be set later
                    ];
                }
            }

            // Calculate totals
            $this->totalHoursWorked = array_sum(array_column($flattenedData, 'hours_worked'));
            $this->availableResources = count($flattenedData);

            // Store data
            $this->resourceDetails = $flattenedData;

            // Log success
            Log::info('Resource utilization data loaded successfully', [
                'resource_count' => $this->availableResources,
                'total_hours' => $this->totalHoursWorked
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading resource utilization: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->setDefaultResourceData();
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

    protected function setDefaultResourceData()
    {
        $this->resourceDetails = [];
        $this->averageUtilization = 0;
        $this->totalHoursWorked = 0;
        $this->availableResources = 0;
        $this->topResource = 'None';
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
