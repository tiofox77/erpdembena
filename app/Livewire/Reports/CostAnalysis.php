<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\MaintenanceEquipment as Equipment;
use App\Models\MaintenanceArea as Area;
use App\Models\MaintenanceLine as Line;
use App\Models\MaintenanceTask;
use App\Models\MaintenancePlan;
use App\Models\MaintenanceTaskLog as TaskLog;
use App\Models\MaintenanceCorrective as Corrective;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CostAnalysis extends Component
{
    // Filters
    public $dateRange = 'month';
    public $startDate;
    public $endDate;
    public $selectedArea = 'all';
    public $selectedLine = 'all';
    public $costType = 'all';
    public $sortField = 'total_cost';
    public $sortDirection = 'desc';

    // Data properties
    public $areas = [];
    public $lines = [];
    public $totalCost = 0;
    public $laborCost = 0;
    public $partsCost = 0;
    public $externalCost = 0;
    public $downtimeCost = 0;
    public $equipmentCosts = [];
    public $maintenanceTypeCosts = [];

    // Chart data
    public $costBreakdownData = [];
    public $monthlyCostTrendData = [];
    public $preventiveVsCorrectiveData = [];
    public $costByAreaData = [];

    public function mount()
    {
        // Load areas and lines for filters
        $this->areas = Area::pluck('name', 'id')->toArray();
        $this->lines = Line::pluck('name', 'id')->toArray();

        // Set default date range
        $this->setDateRange($this->dateRange);

        // Load initial data
        $this->loadCostData();
    }

    public function updatedDateRange()
    {
        $this->setDateRange($this->dateRange);
        $this->loadCostData();
    }

    public function updatedStartDate()
    {
        $this->loadCostData();
    }

    public function updatedEndDate()
    {
        $this->loadCostData();
    }

    public function updatedSelectedArea()
    {
        $this->loadCostData();
    }

    public function updatedSelectedLine()
    {
        $this->loadCostData();
    }

    public function updatedCostType()
    {
        $this->loadCostData();
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

        // Re-sort the equipment costs based on the selected field
        if ($field === 'equipment_name') {
            $this->equipmentCosts = collect($this->equipmentCosts)
                ->sortBy([$field => $this->sortDirection === 'asc' ? 'asc' : 'desc'])
                ->values()
                ->toArray();
        } else {
            $this->equipmentCosts = collect($this->equipmentCosts)
                ->sortBy([$field => $this->sortDirection === 'asc' ? 'asc' : 'desc'])
                ->values()
                ->toArray();
        }
    }

    public function loadCostData()
    {
        try {
            // Get equipment IDs based on filters
            $equipmentQuery = Equipment::query();

            if ($this->selectedArea !== 'all') {
                $equipmentQuery->where('area_id', $this->selectedArea);
            }

            if ($this->selectedLine !== 'all') {
                $equipmentQuery->where('line_id', $this->selectedLine);
            }

            $equipmentIds = $equipmentQuery->pluck('id')->toArray();

            // Calculate cost data
            $this->calculateTaskCosts($equipmentIds);
            $this->calculateCorrectiveCosts($equipmentIds);
            $this->calculateCostBreakdown();
            $this->calculateMonthlyCostTrend($equipmentIds);
            $this->calculatePreventiveVsCorrective();
            $this->calculateCostByArea();

        } catch (\Exception $e) {
            Log::error('Error loading cost analysis data: ' . $e->getMessage());
            $this->setDefaultData();
        }
    }

    protected function calculateTaskCosts($equipmentIds)
    {
        try {
            // Get completed tasks within date range
            $tasks = TaskLog::whereIn('equipment_id', $equipmentIds)
                ->whereBetween('completed_at', [$this->startDate, $this->endDate])
                ->where('status', 'completed')
                ->with(['equipment', 'equipment.area', 'equipment.line'])
                ->get();

            // Apply cost type filter if selected
            if ($this->costType !== 'all') {
                $tasks = $tasks->filter(function($task) {
                    if ($this->costType === 'labor' && $task->labor_cost > 0) return true;
                    if ($this->costType === 'parts' && $task->parts_cost > 0) return true;
                    if ($this->costType === 'external' && $task->external_service_cost > 0) return true;
                    if ($this->costType === 'downtime' && $task->downtime_cost > 0) return true;
                    return false;
                });
            }

            // Calculate totals
            $this->laborCost = $tasks->sum('labor_cost');
            $this->partsCost = $tasks->sum('parts_cost');
            $this->externalCost = $tasks->sum('external_service_cost');
            $this->downtimeCost = $tasks->sum('downtime_cost');

            // Combine with corrective maintenance costs
            // Initialize array to store maintenance type costs
            $this->maintenanceTypeCosts = [
                [
                    'type' => 'Preventive',
                    'count' => $tasks->count(),
                    'labor_cost' => $tasks->sum('labor_cost'),
                    'parts_cost' => $tasks->sum('parts_cost'),
                    'external_cost' => $tasks->sum('external_service_cost'),
                    'downtime_cost' => $tasks->sum('downtime_cost'),
                    'total_cost' => $tasks->sum('labor_cost') + $tasks->sum('parts_cost') +
                                    $tasks->sum('external_service_cost') + $tasks->sum('downtime_cost'),
                    'avg_cost' => $tasks->count() > 0 ?
                        ($tasks->sum('labor_cost') + $tasks->sum('parts_cost') +
                         $tasks->sum('external_service_cost') + $tasks->sum('downtime_cost')) / $tasks->count() : 0
                ]
            ];

            // Group tasks by equipment for equipment costs
            $tasksByEquipment = $tasks->groupBy('equipment_id')->map(function($group) {
                $equipment = $group->first()->equipment;
                return [
                    'equipment_id' => $equipment->id,
                    'equipment_name' => $equipment->name,
                    'area' => $equipment->area->name ?? 'Unknown',
                    'line' => $equipment->line->name ?? 'Unknown',
                    'labor_cost' => $group->sum('labor_cost'),
                    'parts_cost' => $group->sum('parts_cost'),
                    'external_cost' => $group->sum('external_service_cost'),
                    'downtime_cost' => $group->sum('downtime_cost'),
                    'total_cost' => $group->sum('labor_cost') + $group->sum('parts_cost') +
                                    $group->sum('external_service_cost') + $group->sum('downtime_cost'),
                    'task_count' => $group->count()
                ];
            })->values()->toArray();

            $this->equipmentCosts = $tasksByEquipment;

        } catch (\Exception $e) {
            Log::error('Error calculating task costs: ' . $e->getMessage());
        }
    }

    protected function calculateCorrectiveCosts($equipmentIds)
    {
        try {
            // Get corrective maintenance records within date range
            $correctives = Corrective::whereIn('equipment_id', $equipmentIds)
                ->whereBetween('start_time', [$this->startDate." 00:00:00", $this->endDate." 23:59:59"])
                ->where('status', 'resolved')
                ->with(['equipment', 'equipment.area', 'equipment.line'])
                ->get();

            // Apply cost type filter if selected
            if ($this->costType !== 'all') {
                $correctives = $correctives->filter(function($corrective) {
                    if ($this->costType === 'labor' && $corrective->labor_cost > 0) return true;
                    if ($this->costType === 'parts' && $corrective->parts_cost > 0) return true;
                    if ($this->costType === 'external' && $corrective->external_service_cost > 0) return true;
                    if ($this->costType === 'downtime' && $corrective->downtime_cost > 0) return true;
                    return false;
                });
            }

            // Calculate totals and add to existing amounts
            $this->laborCost += $correctives->sum('labor_cost');
            $this->partsCost += $correctives->sum('parts_cost');
            $this->externalCost += $correctives->sum('external_service_cost');
            $this->downtimeCost += $correctives->sum('downtime_cost');
            $this->totalCost = $this->laborCost + $this->partsCost + $this->externalCost + $this->downtimeCost;

            // Add corrective maintenance type to maintenance types array
            $this->maintenanceTypeCosts[] = [
                'type' => 'Corrective',
                'count' => $correctives->count(),
                'labor_cost' => $correctives->sum('labor_cost'),
                'parts_cost' => $correctives->sum('parts_cost'),
                'external_cost' => $correctives->sum('external_service_cost'),
                'downtime_cost' => $correctives->sum('downtime_cost'),
                'total_cost' => $correctives->sum('labor_cost') + $correctives->sum('parts_cost') +
                                $correctives->sum('external_service_cost') + $correctives->sum('downtime_cost'),
                'avg_cost' => $correctives->count() > 0 ?
                    ($correctives->sum('labor_cost') + $correctives->sum('parts_cost') +
                     $correctives->sum('external_service_cost') + $correctives->sum('downtime_cost')) / $correctives->count() : 0
            ];

            // Group correctives by equipment and merge with task costs
            $correctivesByEquipment = $correctives->groupBy('equipment_id')->map(function($group) {
                $equipment = $group->first()->equipment;
                return [
                    'equipment_id' => $equipment->id,
                    'equipment_name' => $equipment->name,
                    'area' => $equipment->area->name ?? 'Unknown',
                    'line' => $equipment->line->name ?? 'Unknown',
                    'labor_cost' => $group->sum('labor_cost'),
                    'parts_cost' => $group->sum('parts_cost'),
                    'external_cost' => $group->sum('external_service_cost'),
                    'downtime_cost' => $group->sum('downtime_cost'),
                    'total_cost' => $group->sum('labor_cost') + $group->sum('parts_cost') +
                                    $group->sum('external_service_cost') + $group->sum('downtime_cost'),
                    'task_count' => $group->count()
                ];
            })->values()->toArray();

            // Merge with existing equipment costs
            $mergedEquipmentCosts = collect($this->equipmentCosts);
            foreach ($correctivesByEquipment as $corrCost) {
                $existingKey = $mergedEquipmentCosts->search(function($item) use ($corrCost) {
                    return $item['equipment_id'] == $corrCost['equipment_id'];
                });

                if ($existingKey !== false) {
                    // Update existing entry
                    $existing = $mergedEquipmentCosts[$existingKey];
                    $mergedEquipmentCosts[$existingKey] = [
                        'equipment_id' => $existing['equipment_id'],
                        'equipment_name' => $existing['equipment_name'],
                        'area' => $existing['area'],
                        'line' => $existing['line'],
                        'labor_cost' => $existing['labor_cost'] + $corrCost['labor_cost'],
                        'parts_cost' => $existing['parts_cost'] + $corrCost['parts_cost'],
                        'external_cost' => $existing['external_cost'] + $corrCost['external_cost'],
                        'downtime_cost' => $existing['downtime_cost'] + $corrCost['downtime_cost'],
                        'total_cost' => $existing['total_cost'] + $corrCost['total_cost'],
                        'task_count' => $existing['task_count'] + $corrCost['task_count']
                    ];
                } else {
                    // Add new entry
                    $mergedEquipmentCosts->push($corrCost);
                }
            }

            // Sort by total cost
            $this->equipmentCosts = $mergedEquipmentCosts->sortByDesc('total_cost')->values()->toArray();

        } catch (\Exception $e) {
            Log::error('Error calculating corrective costs: ' . $e->getMessage());
        }
    }

    protected function calculateCostBreakdown()
    {
        // Calculate cost breakdown for pie chart
        $this->costBreakdownData = [
            'labels' => ['Labor', 'Parts', 'External Services', 'Downtime'],
            'datasets' => [
                [
                    'data' => [
                        $this->laborCost,
                        $this->partsCost,
                        $this->externalCost,
                        $this->downtimeCost
                    ],
                    'backgroundColor' => [
                        'rgba(54, 162, 235, 0.8)',  // Blue
                        'rgba(255, 99, 132, 0.8)',  // Red
                        'rgba(255, 205, 86, 0.8)',  // Yellow
                        'rgba(75, 192, 192, 0.8)'   // Green
                    ],
                    'borderColor' => [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(255, 205, 86, 1)',
                        'rgba(75, 192, 192, 1)'
                    ],
                    'borderWidth' => 1
                ]
            ]
        ];
    }

    protected function calculateMonthlyCostTrend($equipmentIds)
    {
        try {
            // Determine date range
            $startDate = Carbon::parse($this->startDate);
            $endDate = Carbon::parse($this->endDate);
            $diffInMonths = $endDate->diffInMonths($startDate) + 1;

            // Create array to hold monthly data
            $monthlyData = [];

            // Loop through each month in range
            for ($i = 0; $i < $diffInMonths; $i++) {
                $currentMonth = $startDate->copy()->addMonths($i);
                $monthStart = $currentMonth->copy()->startOfMonth()->format('Y-m-d');
                $monthEnd = $currentMonth->copy()->endOfMonth()->format('Y-m-d');
                $monthLabel = $currentMonth->format('M Y');

                // Get task costs for this month
                $taskCosts = TaskLog::whereIn('equipment_id', $equipmentIds)
                    ->whereBetween('completed_at', [$monthStart, $monthEnd])
                    ->where('status', 'completed')
                    ->get();

                // Get corrective costs for this month
                $correctiveCosts = Corrective::whereIn('equipment_id', $equipmentIds)
                    ->whereBetween('start_time', [$monthStart." 00:00:00", $monthEnd." 23:59:59"])
                    ->where('status', 'resolved')
                    ->get();

                // Calculate costs for the month
                $laborCost = $taskCosts->sum('labor_cost') + $correctiveCosts->sum('labor_cost');
                $partsCost = $taskCosts->sum('parts_cost') + $correctiveCosts->sum('parts_cost');
                $externalCost = $taskCosts->sum('external_service_cost') + $correctiveCosts->sum('external_service_cost');
                $downtimeCost = $taskCosts->sum('downtime_cost') + $correctiveCosts->sum('downtime_cost');

                // Add to monthly data array
                $monthlyData[] = [
                    'month' => $monthLabel,
                    'labor' => $laborCost,
                    'parts' => $partsCost,
                    'external' => $externalCost,
                    'downtime' => $downtimeCost
                ];
            }

            // Create chart data from monthly data
            $labels = array_column($monthlyData, 'month');
            $laborData = array_column($monthlyData, 'labor');
            $partsData = array_column($monthlyData, 'parts');
            $externalData = array_column($monthlyData, 'external');
            $downtimeData = array_column($monthlyData, 'downtime');

            $this->monthlyCostTrendData = [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Labor',
                        'data' => $laborData,
                        'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                        'borderColor' => 'rgba(54, 162, 235, 1)',
                        'borderWidth' => 2,
                        'tension' => 0.4
                    ],
                    [
                        'label' => 'Parts',
                        'data' => $partsData,
                        'backgroundColor' => 'rgba(255, 99, 132, 0.5)',
                        'borderColor' => 'rgba(255, 99, 132, 1)',
                        'borderWidth' => 2,
                        'tension' => 0.4
                    ],
                    [
                        'label' => 'External Services',
                        'data' => $externalData,
                        'backgroundColor' => 'rgba(255, 205, 86, 0.5)',
                        'borderColor' => 'rgba(255, 205, 86, 1)',
                        'borderWidth' => 2,
                        'tension' => 0.4
                    ],
                    [
                        'label' => 'Downtime',
                        'data' => $downtimeData,
                        'backgroundColor' => 'rgba(75, 192, 192, 0.5)',
                        'borderColor' => 'rgba(75, 192, 192, 1)',
                        'borderWidth' => 2,
                        'tension' => 0.4
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating monthly cost trend: ' . $e->getMessage());
            $this->monthlyCostTrendData = $this->getDefaultChartData('No monthly cost data available');
        }
    }

    protected function calculatePreventiveVsCorrective()
    {
        try {
            // Use the maintenance type costs data we already calculated
            if (count($this->maintenanceTypeCosts) < 2) {
                $this->preventiveVsCorrectiveData = $this->getDefaultChartData('No maintenance type data available');
                return;
            }

            // Get the data for preventive and corrective
            $preventive = $this->maintenanceTypeCosts[0] ?? ['total_cost' => 0, 'count' => 0];
            $corrective = $this->maintenanceTypeCosts[1] ?? ['total_cost' => 0, 'count' => 0];

            // Format for chart
            $this->preventiveVsCorrectiveData = [
                'labels' => ['Preventive', 'Corrective'],
                'datasets' => [
                    [
                        'label' => 'Total Cost',
                        'data' => [$preventive['total_cost'], $corrective['total_cost']],
                        'backgroundColor' => [
                            'rgba(54, 162, 235, 0.8)',  // Blue for preventive
                            'rgba(255, 99, 132, 0.8)'   // Red for corrective
                        ],
                        'borderColor' => [
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 99, 132, 1)'
                        ],
                        'borderWidth' => 1
                    ],
                    [
                        'label' => 'Count',
                        'data' => [$preventive['count'], $corrective['count']],
                        'type' => 'line',
                        'backgroundColor' => 'rgba(75, 192, 192, 0)',
                        'borderColor' => 'rgba(75, 192, 192, 1)',
                        'borderWidth' => 2,
                        'pointBackgroundColor' => 'rgba(75, 192, 192, 1)',
                        'pointRadius' => 5,
                        'yAxisID' => 'y1'
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating preventive vs corrective data: ' . $e->getMessage());
            $this->preventiveVsCorrectiveData = $this->getDefaultChartData('No maintenance type data available');
        }
    }

    protected function calculateCostByArea()
    {
        try {
            // Group equipment costs by area
            $areaData = [];
            foreach ($this->equipmentCosts as $equipment) {
                $area = $equipment['area'];

                if (!isset($areaData[$area])) {
                    $areaData[$area] = [
                        'labor_cost' => 0,
                        'parts_cost' => 0,
                        'external_cost' => 0,
                        'downtime_cost' => 0,
                        'total_cost' => 0
                    ];
                }

                $areaData[$area]['labor_cost'] += $equipment['labor_cost'];
                $areaData[$area]['parts_cost'] += $equipment['parts_cost'];
                $areaData[$area]['external_cost'] += $equipment['external_cost'];
                $areaData[$area]['downtime_cost'] += $equipment['downtime_cost'];
                $areaData[$area]['total_cost'] += $equipment['total_cost'];
            }

            // Sort by total cost
            uasort($areaData, function($a, $b) {
                return $b['total_cost'] <=> $a['total_cost'];
            });

            // Format for chart
            $labels = array_keys($areaData);
            $laborData = array_column($areaData, 'labor_cost');
            $partsData = array_column($areaData, 'parts_cost');
            $externalData = array_column($areaData, 'external_cost');
            $downtimeData = array_column($areaData, 'downtime_cost');

            $this->costByAreaData = [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Labor',
                        'data' => $laborData,
                        'backgroundColor' => 'rgba(54, 162, 235, 0.8)',
                        'borderColor' => 'rgba(54, 162, 235, 1)',
                        'borderWidth' => 1
                    ],
                    [
                        'label' => 'Parts',
                        'data' => $partsData,
                        'backgroundColor' => 'rgba(255, 99, 132, 0.8)',
                        'borderColor' => 'rgba(255, 99, 132, 1)',
                        'borderWidth' => 1
                    ],
                    [
                        'label' => 'External Services',
                        'data' => $externalData,
                        'backgroundColor' => 'rgba(255, 205, 86, 0.8)',
                        'borderColor' => 'rgba(255, 205, 86, 1)',
                        'borderWidth' => 1
                    ],
                    [
                        'label' => 'Downtime',
                        'data' => $downtimeData,
                        'backgroundColor' => 'rgba(75, 192, 192, 0.8)',
                        'borderColor' => 'rgba(75, 192, 192, 1)',
                        'borderWidth' => 1
                    ]
                ]
            ];

            // If empty, set default
            if (empty($labels)) {
                $this->costByAreaData = $this->getDefaultChartData('No area cost data available');
            }
        } catch (\Exception $e) {
            Log::error('Error calculating cost by area data: ' . $e->getMessage());
            $this->costByAreaData = $this->getDefaultChartData('No area cost data available');
        }
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

    protected function setDefaultData()
    {
        $this->totalCost = 0;
        $this->laborCost = 0;
        $this->partsCost = 0;
        $this->externalCost = 0;
        $this->downtimeCost = 0;
        $this->equipmentCosts = [];
        $this->maintenanceTypeCosts = [];

        $this->costBreakdownData = $this->getDefaultChartData();
        $this->monthlyCostTrendData = $this->getDefaultChartData();
        $this->preventiveVsCorrectiveData = $this->getDefaultChartData();
        $this->costByAreaData = $this->getDefaultChartData();
    }

    public function render()
    {
        return view('livewire.reports.cost-analysis', [
            'areas' => $this->areas,
            'lines' => $this->lines,
            'totalCost' => $this->totalCost,
            'laborCost' => $this->laborCost,
            'partsCost' => $this->partsCost,
            'externalCost' => $this->externalCost,
            'downtimeCost' => $this->downtimeCost,
            'equipmentCosts' => $this->equipmentCosts,
            'maintenanceTypeCosts' => $this->maintenanceTypeCosts,
            'costBreakdownData' => $this->costBreakdownData,
            'monthlyCostTrendData' => $this->monthlyCostTrendData,
            'preventiveVsCorrectiveData' => $this->preventiveVsCorrectiveData,
            'costByAreaData' => $this->costByAreaData
        ]);
    }
}
