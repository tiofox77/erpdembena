<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\MaintenanceEquipment as Equipment;
use App\Models\MaintenanceArea as Area;
use App\Models\MaintenanceLine as Line;
use App\Models\MaintenanceTask;
use App\Models\MaintenancePlan;
use App\Models\MaintenanceTaskLog as TaskLog;
use App\Models\MaintenanceCorrective as DowntimeRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DowntimeImpact extends Component
{
    // Filters
    public $dateRange = 'month';
    public $startDate;
    public $endDate;
    public $selectedArea = 'all';
    public $selectedLine = 'all';
    public $downtimeType = 'all';
    public $sortField = 'duration';
    public $sortDirection = 'desc';

    // Data properties
    public $areas = [];
    public $lines = [];
    public $downtimeRecords = [];
    public $totalDowntime = 0;
    public $productionLoss = 0;
    public $financialImpact = 0;
    public $availabilityRate = 0;
    public $mostCriticalEquipment = '';
    public $mostCriticalDowntime = 0;
    public $recommendations = [];

    // Chart data
    public $downtimeByEquipmentData = [];
    public $downtimeTypesData = [];
    public $downtimeTrendData = [];
    public $financialImpactData = [];

    public function mount()
    {
        // Load areas and lines for filters
        $this->areas = Area::pluck('name', 'id')->toArray();
        $this->lines = Line::pluck('name', 'id')->toArray();

        // Set default date range
        $this->setDateRange($this->dateRange);

        // Load initial data
        $this->loadChartData();
    }

    public function updatedDateRange()
    {
        $this->setDateRange($this->dateRange);
        $this->loadChartData();
    }

    public function updatedStartDate()
    {
        $this->loadChartData();
    }

    public function updatedEndDate()
    {
        $this->loadChartData();
    }

    public function updatedSelectedArea()
    {
        $this->loadChartData();
    }

    public function updatedSelectedLine()
    {
        $this->loadChartData();
    }

    public function updatedDowntimeType()
    {
        $this->loadChartData();
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

        // Re-sort the downtime records
        $this->downtimeRecords = collect($this->downtimeRecords)
            ->sortBy([$field => $this->sortDirection === 'asc' ? 'asc' : 'desc'])
            ->values()
            ->toArray();
    }

    public function loadChartData()
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

            // Generate chart data
            $this->getDowntimeRecords($equipmentIds, $this->startDate, $this->endDate);
            $this->getDowntimeByEquipmentData($equipmentIds, $this->startDate, $this->endDate);
            $this->getDowntimeTypesData($equipmentIds, $this->startDate, $this->endDate);
            $this->getDowntimeTrendData($equipmentIds, $this->startDate, $this->endDate);
            $this->getFinancialImpactData($equipmentIds, $this->startDate, $this->endDate);
            $this->generateRecommendations();

        } catch (\Exception $e) {
            Log::error('Error loading downtime impact data: ' . $e->getMessage());
            $this->setDefaultChartData();
        }
    }

    protected function getDowntimeRecords($equipmentIds, $startDate, $endDate)
    {
        // Query downtime records
        $query = DowntimeRecord::whereIn('equipment_id', $equipmentIds)
            ->whereBetween('start_date', [$startDate, $endDate]);

        if ($this->downtimeType !== 'all') {
            $query->where('type', strtolower($this->downtimeType) === 'planned' ? 'Planned' :
                (strtolower($this->downtimeType) === 'unplanned' ? 'Unplanned' :
                (strtolower($this->downtimeType) === 'operational' ? 'Operational' : $this->downtimeType)));
        }

        $records = $query->with(['equipment', 'equipment.area', 'equipment.line'])->get();

        // Process records for display
        $this->downtimeRecords = $records->map(function($record) {
            return [
                'id' => $record->id,
                'start_date' => Carbon::parse($record->start_date)->format('Y-m-d H:i'),
                'equipment' => $record->equipment->name ?? 'Unknown',
                'serial_number' => $record->equipment->serial_number ?? '',
                'area' => $record->equipment->area->name ?? 'Unknown',
                'line' => $record->equipment->line->name ?? 'Unknown',
                'type' => $record->type,
                'reason' => $record->reason,
                'duration' => $record->duration_hours,
                'production_loss' => $record->production_loss_units,
                'financial_impact' => $record->financial_impact
            ];
        })->sortByDesc('duration')->values()->toArray();

        // Calculate summary metrics
        $this->totalDowntime = $records->sum('duration_hours');
        $this->productionLoss = $records->sum('production_loss_units');
        $this->financialImpact = $records->sum('financial_impact');

        // Calculate availability rate
        $totalPeriodHours = Carbon::parse($startDate)->diffInHours(Carbon::parse($endDate)) * count($equipmentIds);
        $this->availabilityRate = $totalPeriodHours > 0
            ? round(100 - (($this->totalDowntime / $totalPeriodHours) * 100), 1)
            : 100;

        // Find most critical equipment
        if ($records->isNotEmpty()) {
            $criticalEquipment = $records->groupBy('equipment_id')
                ->map(function($group) {
                    return [
                        'equipment_id' => $group->first()->equipment_id,
                        'equipment_name' => $group->first()->equipment->name,
                        'total_downtime' => $group->sum('duration_hours')
                    ];
                })
                ->sortByDesc('total_downtime')
                ->first();

            $this->mostCriticalEquipment = $criticalEquipment['equipment_name'] ?? 'None';
            $this->mostCriticalDowntime = $criticalEquipment['total_downtime'] ?? 0;
        } else {
            $this->mostCriticalEquipment = 'None';
            $this->mostCriticalDowntime = 0;
        }
    }

    protected function getDowntimeByEquipmentData($equipmentIds, $startDate, $endDate)
    {
        // Get top 10 equipment by downtime
        $query = DowntimeRecord::whereIn('equipment_id', $equipmentIds)
            ->whereBetween('start_date', [$startDate, $endDate]);

        if ($this->downtimeType !== 'all') {
            $query->where('type', strtolower($this->downtimeType) === 'planned' ? 'Planned' :
                (strtolower($this->downtimeType) === 'unplanned' ? 'Unplanned' :
                (strtolower($this->downtimeType) === 'operational' ? 'Operational' : $this->downtimeType)));
        }

        $topEquipment = $query->select('equipment_id', DB::raw('SUM(duration_hours) as total_downtime'))
            ->groupBy('equipment_id')
            ->orderByDesc('total_downtime')
            ->limit(10)
            ->with('equipment')
            ->get();

        $labels = $topEquipment->map(function($item) {
            return $item->equipment->name ?? 'Unknown Equipment';
        })->toArray();

        $data = $topEquipment->pluck('total_downtime')->toArray();

        $this->downtimeByEquipmentData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Downtime Hours',
                    'data' => $data,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.8)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1
                ]
            ]
        ];

        // Set default data if empty
        if (empty($labels)) {
            $this->downtimeByEquipmentData = [
                'labels' => ['No data available'],
                'datasets' => [
                    [
                        'label' => 'Downtime Hours',
                        'data' => [0],
                        'backgroundColor' => 'rgba(54, 162, 235, 0.8)',
                        'borderColor' => 'rgba(54, 162, 235, 1)',
                        'borderWidth' => 1
                    ]
                ]
            ];
        }
    }

    protected function getDowntimeTypesData($equipmentIds, $startDate, $endDate)
    {
        // Group downtime by type
        $query = DowntimeRecord::whereIn('equipment_id', $equipmentIds)
            ->whereBetween('start_date', [$startDate, $endDate]);

        if ($this->downtimeType !== 'all') {
            $query->where('type', strtolower($this->downtimeType) === 'planned' ? 'Planned' :
                (strtolower($this->downtimeType) === 'unplanned' ? 'Unplanned' :
                (strtolower($this->downtimeType) === 'operational' ? 'Operational' : $this->downtimeType)));
        }

        $downtimeByType = $query->select('type', DB::raw('SUM(duration_hours) as total_downtime'))
            ->groupBy('type')
            ->orderByDesc('total_downtime')
            ->get();

        $labels = $downtimeByType->pluck('type')->toArray();
        $data = $downtimeByType->pluck('total_downtime')->toArray();

        // Create color array
        $backgroundColors = [
            'Planned' => 'rgba(54, 162, 235, 0.8)',
            'Unplanned' => 'rgba(255, 99, 132, 0.8)',
            'Operational' => 'rgba(255, 205, 86, 0.8)',
            'Other' => 'rgba(75, 192, 192, 0.8)'
        ];

        $colors = array_map(function($type) use ($backgroundColors) {
            return $backgroundColors[$type] ?? 'rgba(201, 203, 207, 0.8)';
        }, $labels);

        $this->downtimeTypesData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => array_map(function($color) {
                        return str_replace('0.8', '1', $color);
                    }, $colors),
                    'borderWidth' => 1
                ]
            ]
        ];

        // Set default data if empty
        if (empty($labels)) {
            $this->downtimeTypesData = [
                'labels' => ['No data available'],
                'datasets' => [
                    [
                        'data' => [0],
                        'backgroundColor' => ['rgba(201, 203, 207, 0.8)'],
                        'borderColor' => ['rgba(201, 203, 207, 1)'],
                        'borderWidth' => 1
                    ]
                ]
            ];
        }
    }

    protected function getDowntimeTrendData($equipmentIds, $startDate, $endDate)
    {
        // Get downtime trend by day/week
        $startCarbon = Carbon::parse($startDate);
        $endCarbon = Carbon::parse($endDate);
        $daysRange = $startCarbon->diffInDays($endCarbon);

        // Choose grouping (daily for <30 days, weekly for 31-90 days, monthly for >90)
        $grouping = 'daily';
        if ($daysRange > 90) {
            $grouping = 'monthly';
        } elseif ($daysRange > 30) {
            $grouping = 'weekly';
        }

        $query = DowntimeRecord::whereIn('equipment_id', $equipmentIds)
            ->whereBetween('start_date', [$startDate, $endDate]);

        if ($this->downtimeType !== 'all') {
            $query->where('type', strtolower($this->downtimeType) === 'planned' ? 'Planned' :
                (strtolower($this->downtimeType) === 'unplanned' ? 'Unplanned' :
                (strtolower($this->downtimeType) === 'operational' ? 'Operational' : $this->downtimeType)));
        }

        // Format date based on grouping
        $dateFormat = $grouping === 'daily' ? 'Y-m-d' : ($grouping === 'weekly' ? 'Y-W' : 'Y-m');
        $dateFormatLabel = $grouping === 'daily' ? 'M d' : ($grouping === 'weekly' ? 'W w' : 'M Y');

        // SQL date format function based on DB type
        $dbDateFormat = $grouping === 'daily' ? 'Y-m-d' : ($grouping === 'weekly' ? 'Y-W' : 'Y-m');

        // Get downtime by date and type
        $records = $query->get();

        // Group and format data
        $groupedData = [];
        $types = ['Planned', 'Unplanned', 'Operational'];

        foreach ($records as $record) {
            $date = Carbon::parse($record->start_date)->format($dateFormat);
            $type = $record->type;

            if (!isset($groupedData[$date])) {
                $groupedData[$date] = [
                    'label' => Carbon::parse($record->start_date)->format($dateFormatLabel),
                    'Planned' => 0,
                    'Unplanned' => 0,
                    'Operational' => 0
                ];
            }

            if (in_array($type, $types)) {
                $groupedData[$date][$type] += $record->duration_hours;
            } else {
                // If type doesn't match predefined types, categorize as "Other"
                if (!isset($groupedData[$date]['Other'])) {
                    $groupedData[$date]['Other'] = 0;
                    if (!in_array('Other', $types)) {
                        $types[] = 'Other';
                    }
                }
                $groupedData[$date]['Other'] += $record->duration_hours;
            }
        }

        // Sort by date
        ksort($groupedData);

        // Prepare chart data
        $labels = array_map(function($item) {
            return $item['label'];
        }, $groupedData);

        $datasets = [];
        $colors = [
            'Planned' => ['rgba(54, 162, 235, 0.8)', 'rgba(54, 162, 235, 1)'],
            'Unplanned' => ['rgba(255, 99, 132, 0.8)', 'rgba(255, 99, 132, 1)'],
            'Operational' => ['rgba(255, 205, 86, 0.8)', 'rgba(255, 205, 86, 1)'],
            'Other' => ['rgba(75, 192, 192, 0.8)', 'rgba(75, 192, 192, 1)']
        ];

        foreach ($types as $type) {
            $data = array_map(function($item) use ($type) {
                return $item[$type] ?? 0;
            }, $groupedData);

            $datasets[] = [
                'label' => $type,
                'data' => $data,
                'backgroundColor' => $colors[$type][0] ?? 'rgba(201, 203, 207, 0.8)',
                'borderColor' => $colors[$type][1] ?? 'rgba(201, 203, 207, 1)',
                'borderWidth' => 2,
                'tension' => 0.4
            ];
        }

        $this->downtimeTrendData = [
            'labels' => $labels,
            'datasets' => $datasets
        ];

        // Set default data if empty
        if (empty($labels)) {
            $this->downtimeTrendData = [
                'labels' => ['No data available'],
                'datasets' => [
                    [
                        'label' => 'No Data',
                        'data' => [0],
                        'backgroundColor' => 'rgba(201, 203, 207, 0.8)',
                        'borderColor' => 'rgba(201, 203, 207, 1)',
                        'borderWidth' => 2,
                        'tension' => 0.4
                    ]
                ]
            ];
        }
    }

    protected function getFinancialImpactData($equipmentIds, $startDate, $endDate)
    {
        // Get financial impact by area
        $query = DowntimeRecord::whereIn('equipment_id', $equipmentIds)
            ->whereBetween('start_date', [$startDate, $endDate]);

        if ($this->downtimeType !== 'all') {
            $query->where('type', strtolower($this->downtimeType) === 'planned' ? 'Planned' :
                (strtolower($this->downtimeType) === 'unplanned' ? 'Unplanned' :
                (strtolower($this->downtimeType) === 'operational' ? 'Operational' : $this->downtimeType)));
        }

        $records = $query->with(['equipment.area'])->get();

        // Group by area
        $areaData = [];
        foreach ($records as $record) {
            $areaName = $record->equipment->area->name ?? 'Unknown';

            if (!isset($areaData[$areaName])) {
                $areaData[$areaName] = [
                    'financial_impact' => 0,
                    'planned' => 0,
                    'unplanned' => 0,
                    'operational' => 0
                ];
            }

            $areaData[$areaName]['financial_impact'] += $record->financial_impact;

            if ($record->type === 'Planned') {
                $areaData[$areaName]['planned'] += $record->financial_impact;
            } elseif ($record->type === 'Unplanned') {
                $areaData[$areaName]['unplanned'] += $record->financial_impact;
            } elseif ($record->type === 'Operational') {
                $areaData[$areaName]['operational'] += $record->financial_impact;
            }
        }

        // Sort by total financial impact
        uasort($areaData, function($a, $b) {
            return $b['financial_impact'] <=> $a['financial_impact'];
        });

        // Prepare chart data
        $labels = array_keys($areaData);

        $datasets = [
            [
                'label' => 'Planned',
                'data' => array_map(function($item) { return $item['planned']; }, $areaData),
                'backgroundColor' => 'rgba(54, 162, 235, 0.8)',
                'borderColor' => 'rgba(54, 162, 235, 1)',
                'borderWidth' => 1
            ],
            [
                'label' => 'Unplanned',
                'data' => array_map(function($item) { return $item['unplanned']; }, $areaData),
                'backgroundColor' => 'rgba(255, 99, 132, 0.8)',
                'borderColor' => 'rgba(255, 99, 132, 1)',
                'borderWidth' => 1
            ],
            [
                'label' => 'Operational',
                'data' => array_map(function($item) { return $item['operational']; }, $areaData),
                'backgroundColor' => 'rgba(255, 205, 86, 0.8)',
                'borderColor' => 'rgba(255, 205, 86, 1)',
                'borderWidth' => 1
            ]
        ];

        $this->financialImpactData = [
            'labels' => $labels,
            'datasets' => $datasets
        ];

        // Set default data if empty
        if (empty($labels)) {
            $this->financialImpactData = [
                'labels' => ['No data available'],
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
    }

    protected function generateRecommendations()
    {
        // Generate improvement recommendations based on downtime data
        $this->recommendations = [];

        try {
            // Check if we have downtime records to analyze
            if (empty($this->downtimeRecords)) {
                return;
            }

            // 1. Most frequent failure equipment - recommend preventive maintenance
            $equipmentIssues = collect($this->downtimeRecords)
                ->where('type', 'Unplanned')
                ->groupBy('equipment')
                ->map(function($group) {
                    return [
                        'equipment' => $group->first()['equipment'],
                        'count' => $group->count(),
                        'total_downtime' => $group->sum('duration'),
                        'financial_impact' => $group->sum('financial_impact'),
                        'most_common_reason' => $group->groupBy('reason')
                            ->map(function($reasonGroup) { return $reasonGroup->count(); })
                            ->sortDesc()
                            ->keys()
                            ->first()
                    ];
                })
                ->sortByDesc('count')
                ->take(3)
                ->values()
                ->toArray();

            foreach ($equipmentIssues as $issue) {
                if ($issue['count'] >= 2) {
                    $this->recommendations[] = [
                        'title' => 'Increase Preventive Maintenance for ' . $issue['equipment'],
                        'target' => $issue['equipment'],
                        'description' => 'This equipment has experienced ' . $issue['count'] . ' unplanned failures, with ' .
                            number_format($issue['total_downtime'], 1) . ' hours of downtime and $' .
                            number_format($issue['financial_impact']) . ' financial impact. Most common reason: ' .
                            ($issue['most_common_reason'] ?? 'Various issues'),
                        'priority' => $issue['financial_impact'] > 5000 ? 'High' : ($issue['financial_impact'] > 2000 ? 'Medium' : 'Low'),
                        'potential_hours_saved' => round($issue['total_downtime'] * 0.7, 1),
                        'estimated_roi' => round($issue['financial_impact'] * 0.6),
                        'implementation_steps' => [
                            'Perform complete inspection of ' . $issue['equipment'],
                            'Increase PM frequency by 50%',
                            'Focus on ' . ($issue['most_common_reason'] ?? 'critical components'),
                            'Train operators on early warning signs',
                            'Implement condition monitoring if applicable'
                        ]
                    ];
                }
            }

            // 2. Check for areas with high operational downtime - process improvements
            $areaOperationalIssues = collect($this->downtimeRecords)
                ->where('type', 'Operational')
                ->groupBy('area')
                ->map(function($group) {
                    return [
                        'area' => $group->first()['area'],
                        'total_downtime' => $group->sum('duration'),
                        'financial_impact' => $group->sum('financial_impact'),
                        'most_common_reason' => $group->groupBy('reason')
                            ->map(function($reasonGroup) { return $reasonGroup->count(); })
                            ->sortDesc()
                            ->keys()
                            ->first()
                    ];
                })
                ->sortByDesc('total_downtime')
                ->take(2)
                ->values()
                ->toArray();

            foreach ($areaOperationalIssues as $issue) {
                if ($issue['total_downtime'] >= 8) {
                    $this->recommendations[] = [
                        'title' => 'Process Optimization for ' . $issue['area'],
                        'target' => $issue['area'] . ' Area',
                        'description' => 'This area has ' . number_format($issue['total_downtime'], 1) .
                            ' hours of operational downtime with $' . number_format($issue['financial_impact']) .
                            ' financial impact. Most common reason: ' . ($issue['most_common_reason'] ?? 'Various issues'),
                        'priority' => $issue['financial_impact'] > 10000 ? 'High' : ($issue['financial_impact'] > 5000 ? 'Medium' : 'Low'),
                        'potential_hours_saved' => round($issue['total_downtime'] * 0.5, 1),
                        'estimated_roi' => round($issue['financial_impact'] * 0.4),
                        'implementation_steps' => [
                            'Conduct workflow analysis in ' . $issue['area'],
                            'Identify bottlenecks related to ' . ($issue['most_common_reason'] ?? 'operational processes'),
                            'Implement standard work procedures',
                            'Train staff on optimized processes',
                            'Monitor and adjust based on performance metrics'
                        ]
                    ];
                }
            }

            // 3. Identify planned maintenance optimization opportunities
            $longPlannedDowntime = collect($this->downtimeRecords)
                ->where('type', 'Planned')
                ->filter(function($record) {
                    return $record['duration'] > 4; // Focus on longer planned maintenance
                })
                ->groupBy('equipment')
                ->map(function($group) {
                    return [
                        'equipment' => $group->first()['equipment'],
                        'area' => $group->first()['area'],
                        'total_downtime' => $group->sum('duration'),
                        'average_duration' => $group->avg('duration'),
                        'financial_impact' => $group->sum('financial_impact'),
                        'count' => $group->count()
                    ];
                })
                ->sortByDesc('total_downtime')
                ->take(2)
                ->values()
                ->toArray();

            foreach ($longPlannedDowntime as $issue) {
                $this->recommendations[] = [
                    'title' => 'Optimize Planned Maintenance for ' . $issue['equipment'],
                    'target' => $issue['equipment'],
                    'description' => 'This equipment requires ' . number_format($issue['total_downtime'], 1) .
                        ' hours of planned maintenance with $' . number_format($issue['financial_impact']) .
                        ' impact. Average duration: ' . number_format($issue['average_duration'], 1) . ' hours per maintenance.',
                    'priority' => $issue['average_duration'] > 8 ? 'High' : ($issue['average_duration'] > 4 ? 'Medium' : 'Low'),
                    'potential_hours_saved' => round($issue['total_downtime'] * 0.3, 1),
                    'estimated_roi' => round($issue['financial_impact'] * 0.25),
                    'implementation_steps' => [
                        'Review maintenance procedures for ' . $issue['equipment'],
                        'Identify tasks that can be performed in parallel',
                        'Pre-stage parts and tools before maintenance',
                        'Train maintenance technicians on optimized procedures',
                        'Consider predictive maintenance technologies'
                    ]
                ];
            }

            // 4. Generic recommendation if none of the above apply
            if (count($this->recommendations) == 0 && $this->totalDowntime > 0) {
                $this->recommendations[] = [
                    'title' => 'Implement Downtime Tracking System',
                    'target' => 'All Equipment',
                    'description' => 'Implement a detailed downtime tracking system to better categorize and analyze downtime causes.',
                    'priority' => 'Medium',
                    'potential_hours_saved' => round($this->totalDowntime * 0.15, 1),
                    'estimated_roi' => round($this->financialImpact * 0.1),
                    'implementation_steps' => [
                        'Define downtime categories and reasons',
                        'Train operators on proper downtime reporting',
                        'Implement automated downtime tracking where possible',
                        'Create regular review process for downtime data',
                        'Establish continuous improvement team to address findings'
                    ]
                ];
            }

        } catch (\Exception $e) {
            Log::error('Error generating downtime recommendations: ' . $e->getMessage());
        }
    }

    protected function setDefaultChartData()
    {
        // Set default chart data if there's an error loading from database
        $this->downtimeByEquipmentData = [
            'labels' => ['No data available'],
            'datasets' => [
                [
                    'label' => 'Downtime Hours',
                    'data' => [0],
                    'backgroundColor' => 'rgba(54, 162, 235, 0.8)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1
                ]
            ]
        ];

        $this->downtimeTypesData = [
            'labels' => ['No data available'],
            'datasets' => [
                [
                    'data' => [0],
                    'backgroundColor' => ['rgba(201, 203, 207, 0.8)'],
                    'borderColor' => ['rgba(201, 203, 207, 1)'],
                    'borderWidth' => 1
                ]
            ]
        ];

        $this->downtimeTrendData = [
            'labels' => ['No data available'],
            'datasets' => [
                [
                    'label' => 'No Data',
                    'data' => [0],
                    'backgroundColor' => 'rgba(201, 203, 207, 0.8)',
                    'borderColor' => 'rgba(201, 203, 207, 1)',
                    'borderWidth' => 2,
                    'tension' => 0.4
                ]
            ]
        ];

        $this->financialImpactData = [
            'labels' => ['No data available'],
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

        $this->downtimeRecords = [];
        $this->totalDowntime = 0;
        $this->productionLoss = 0;
        $this->financialImpact = 0;
        $this->availabilityRate = 100;
        $this->mostCriticalEquipment = 'None';
        $this->mostCriticalDowntime = 0;
        $this->recommendations = [];
    }

    public function render()
    {
        return view('livewire.reports.downtime-impact', [
            'areas' => $this->areas,
            'lines' => $this->lines,
            'downtimeRecords' => $this->downtimeRecords,
            'totalDowntime' => $this->totalDowntime,
            'productionLoss' => $this->productionLoss,
            'financialImpact' => $this->financialImpact,
            'availabilityRate' => $this->availabilityRate,
            'mostCriticalEquipment' => $this->mostCriticalEquipment,
            'mostCriticalDowntime' => $this->mostCriticalDowntime,
            'downtimeByEquipmentData' => $this->downtimeByEquipmentData,
            'downtimeTypesData' => $this->downtimeTypesData,
            'downtimeTrendData' => $this->downtimeTrendData,
            'financialImpactData' => $this->financialImpactData,
            'recommendations' => $this->recommendations
        ]);
    }
}
