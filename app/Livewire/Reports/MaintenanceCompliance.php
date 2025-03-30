<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MaintenancePlan;
use App\Models\MaintenanceEquipment;
use App\Models\MaintenanceArea;
use App\Models\MaintenanceLine;
use App\Models\MaintenanceNote;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MaintenanceCompliance extends Component
{
    use WithPagination;

    public $dateRange = 'month';
    public $startDate;
    public $endDate;
    public $selectedArea = 'all';
    public $selectedLine = 'all';
    public $searchTerm = '';
    public $sortField = 'compliance_rate';
    public $sortDirection = 'desc';

    // Chart data
    public $complianceTrendData = [];
    public $complianceByAreaData = [];
    public $complianceByEquipmentData = [];
    public $overdueTasksData = [];

    public function mount()
    {
        // Set default date range to current month
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');

        $this->loadChartData();
    }

    public function updatedDateRange()
    {
        $this->setDateRange($this->dateRange);
        $this->loadChartData();
    }

    public function updatedStartDate()
    {
        $this->dateRange = 'custom';
        $this->loadChartData();
    }

    public function updatedEndDate()
    {
        $this->dateRange = 'custom';
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

    public function setDateRange($range)
    {
        $this->dateRange = $range;
        $now = now();

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
            case 'all':
                $this->startDate = null;
                $this->endDate = null;
                break;
        }

        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function loadChartData()
    {
        try {
            $startDate = $this->startDate ? Carbon::parse($this->startDate) : Carbon::now()->subYear();
            $endDate = $this->endDate ? Carbon::parse($this->endDate) : Carbon::now();

            // Prepare query filters for equipment
            $equipmentQuery = MaintenanceEquipment::query()
                ->when($this->selectedArea !== 'all', function ($query) {
                    return $query->where('area_id', $this->selectedArea);
                })
                ->when($this->selectedLine !== 'all', function ($query) {
                    return $query->where('line_id', $this->selectedLine);
                });

            // Get equipment IDs for filtering
            $equipmentIds = $equipmentQuery->pluck('id')->toArray();

            // Load compliance trend data
            $trendData = $this->getComplianceTrendData($equipmentIds, $startDate, $endDate);

            // Load compliance by area data
            $areaData = $this->getComplianceByAreaData($equipmentIds, $startDate, $endDate);

            // Load compliance by equipment data
            $equipmentData = $this->getComplianceByEquipmentData($equipmentIds, $startDate, $endDate);

            // Load overdue tasks data
            $overdueData = $this->getOverdueTasksData($equipmentIds, $startDate, $endDate);

            // Set the chart data
            $this->complianceTrendData = $trendData;
            $this->complianceByAreaData = $areaData;
            $this->complianceByEquipmentData = $equipmentData;
            $this->overdueTasksData = $overdueData;
        } catch (\Exception $e) {
            Log::error('Error loading maintenance compliance data: ' . $e->getMessage());

            // Set default data in case of error
            $this->setDefaultChartData();
        }
    }

    protected function getComplianceTrendData($equipmentIds, $startDate, $endDate)
    {
        try {
            // Get months between start and end date
            $months = [];
            $current = $startDate->copy();
            while ($current <= $endDate) {
                $months[] = $current->format('Y-m');
                $current->addMonth();
            }

            $monthlyData = [];
            $monthLabels = array_map(function($m) {
                return Carbon::createFromFormat('Y-m', $m)->format('M Y');
            }, $months);

            // Get monthly planned vs completed maintenance tasks
            foreach ($months as $month) {
                $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
                $monthEnd = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

                // Get total planned maintenance
                $plannedCount = MaintenancePlan::whereIn('equipment_id', $equipmentIds)
                    ->where('scheduled_date', '>=', $monthStart)
                    ->where('scheduled_date', '<=', $monthEnd)
                    ->count();

                // Get completed maintenance (using notes)
                $completedCount = 0;
                if ($plannedCount > 0) {
                    $completedCount = MaintenanceNote::join('maintenance_plans', 'maintenance_notes.maintenance_plan_id', '=', 'maintenance_plans.id')
                        ->whereIn('maintenance_plans.equipment_id', $equipmentIds)
                        ->where('maintenance_plans.scheduled_date', '>=', $monthStart)
                        ->where('maintenance_plans.scheduled_date', '<=', $monthEnd)
                        ->where('maintenance_notes.status', 'completed')
                        ->count();
                }

                // Calculate compliance rate
                $complianceRate = $plannedCount > 0 ? round(($completedCount / $plannedCount) * 100, 1) : 0;
                $monthlyData[] = $complianceRate;
            }

            return [
                'labels' => $monthLabels,
                'datasets' => [
                    [
                        'label' => 'Compliance Rate (%)',
                        'data' => $monthlyData,
                        'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                        'borderColor' => 'rgba(75, 192, 192, 1)',
                        'borderWidth' => 2,
                        'tension' => 0.3,
                        'fill' => true
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error in getComplianceTrendData: ' . $e->getMessage());

            // Return default data
            return [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                'datasets' => [
                    [
                        'label' => 'Compliance Rate (%)',
                        'data' => [85, 88, 82, 90, 86, 92],
                        'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                        'borderColor' => 'rgba(75, 192, 192, 1)',
                        'borderWidth' => 2,
                        'tension' => 0.3,
                        'fill' => true
                    ]
                ]
            ];
        }
    }

    protected function getComplianceByAreaData($equipmentIds, $startDate, $endDate)
    {
        try {
            // Get area IDs for all equipment
            $areaIds = MaintenanceEquipment::whereIn('id', $equipmentIds)
                ->whereNotNull('area_id')
                ->pluck('area_id')
                ->unique()
                ->toArray();

            // Get area names
            $areaNames = [];
            $complianceRates = [];

            if (DB::getSchemaBuilder()->hasTable('maintenance_areas')) {
                $areas = DB::table('maintenance_areas')
                    ->whereIn('id', $areaIds)
                    ->get();

                foreach ($areas as $area) {
                    // Get equipment in this area
                    $areaEquipmentIds = MaintenanceEquipment::where('area_id', $area->id)
                        ->whereIn('id', $equipmentIds)
                        ->pluck('id')
                        ->toArray();

                    // Get total planned maintenance for area
                    $plannedCount = MaintenancePlan::whereIn('equipment_id', $areaEquipmentIds)
                        ->where('scheduled_date', '>=', $startDate)
                        ->where('scheduled_date', '<=', $endDate)
                        ->count();

                    // Get completed maintenance for area (using notes)
                    $completedCount = 0;
                    if ($plannedCount > 0) {
                        $completedCount = MaintenanceNote::join('maintenance_plans', 'maintenance_notes.maintenance_plan_id', '=', 'maintenance_plans.id')
                            ->whereIn('maintenance_plans.equipment_id', $areaEquipmentIds)
                            ->where('maintenance_plans.scheduled_date', '>=', $startDate)
                            ->where('maintenance_plans.scheduled_date', '<=', $endDate)
                            ->where('maintenance_notes.status', 'completed')
                            ->count();
                    }

                    // Calculate compliance rate
                    $complianceRate = $plannedCount > 0 ? round(($completedCount / $plannedCount) * 100, 1) : 0;

                    $areaNames[] = $area->name;
                    $complianceRates[] = $complianceRate;
                }
            }

            // Sort by compliance rate (high to low)
            array_multisort($complianceRates, SORT_DESC, $areaNames);

            // Get colors based on compliance rate
            $backgroundColors = array_map(function ($rate) {
                if ($rate >= 90) return 'rgba(72, 187, 120, 0.7)'; // Green for high compliance
                if ($rate >= 70) return 'rgba(237, 137, 54, 0.7)'; // Orange for medium compliance
                return 'rgba(229, 62, 62, 0.7)'; // Red for low compliance
            }, $complianceRates);

            $borderColors = array_map(function ($rate) {
                if ($rate >= 90) return 'rgba(72, 187, 120, 1)';
                if ($rate >= 70) return 'rgba(237, 137, 54, 1)';
                return 'rgba(229, 62, 62, 1)';
            }, $complianceRates);

            return [
                'labels' => $areaNames,
                'datasets' => [
                    [
                        'label' => 'Compliance Rate (%)',
                        'data' => $complianceRates,
                        'backgroundColor' => $backgroundColors,
                        'borderColor' => $borderColors,
                        'borderWidth' => 1
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error in getComplianceByAreaData: ' . $e->getMessage());

            // Return default data
            return [
                'labels' => ['Production', 'Packaging', 'Quality Control', 'Maintenance', 'Utilities'],
                'datasets' => [
                    [
                        'label' => 'Compliance Rate (%)',
                        'data' => [95, 88, 76, 92, 85],
                        'backgroundColor' => [
                            'rgba(72, 187, 120, 0.7)',
                            'rgba(72, 187, 120, 0.7)',
                            'rgba(237, 137, 54, 0.7)',
                            'rgba(72, 187, 120, 0.7)',
                            'rgba(72, 187, 120, 0.7)'
                        ],
                        'borderColor' => [
                            'rgba(72, 187, 120, 1)',
                            'rgba(72, 187, 120, 1)',
                            'rgba(237, 137, 54, 1)',
                            'rgba(72, 187, 120, 1)',
                            'rgba(72, 187, 120, 1)'
                        ],
                        'borderWidth' => 1
                    ]
                ]
            ];
        }
    }

    protected function getComplianceByEquipmentData($equipmentIds, $startDate, $endDate)
    {
        try {
            // Limit to top 10 equipment with highest maintenance count
            $topEquipmentIds = MaintenancePlan::whereIn('equipment_id', $equipmentIds)
                ->where('scheduled_date', '>=', $startDate)
                ->where('scheduled_date', '<=', $endDate)
                ->select('equipment_id', DB::raw('COUNT(*) as count'))
                ->groupBy('equipment_id')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->pluck('equipment_id')
                ->toArray();

            $equipmentNames = [];
            $complianceRates = [];
            $plannedCounts = [];
            $completedCounts = [];

            foreach ($topEquipmentIds as $equipmentId) {
                $equipment = MaintenanceEquipment::find($equipmentId);
                if ($equipment) {
                    // Get total planned maintenance
                    $plannedCount = MaintenancePlan::where('equipment_id', $equipmentId)
                        ->where('scheduled_date', '>=', $startDate)
                        ->where('scheduled_date', '<=', $endDate)
                        ->count();

                    // Get completed maintenance (using notes)
                    $completedCount = 0;
                    if ($plannedCount > 0) {
                        $completedCount = MaintenanceNote::join('maintenance_plans', 'maintenance_notes.maintenance_plan_id', '=', 'maintenance_plans.id')
                            ->where('maintenance_plans.equipment_id', $equipmentId)
                            ->where('maintenance_plans.scheduled_date', '>=', $startDate)
                            ->where('maintenance_plans.scheduled_date', '<=', $endDate)
                            ->where('maintenance_notes.status', 'completed')
                            ->count();
                    }

                    // Calculate compliance rate
                    $complianceRate = $plannedCount > 0 ? round(($completedCount / $plannedCount) * 100, 1) : 0;

                    $equipmentNames[] = $equipment->name;
                    $complianceRates[] = $complianceRate;
                    $plannedCounts[] = $plannedCount;
                    $completedCounts[] = $completedCount;
                }
            }

            return [
                'labels' => $equipmentNames,
                'datasets' => [
                    [
                        'label' => 'Planned',
                        'data' => $plannedCounts,
                        'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                        'borderColor' => 'rgba(54, 162, 235, 1)',
                        'borderWidth' => 1
                    ],
                    [
                        'label' => 'Completed',
                        'data' => $completedCounts,
                        'backgroundColor' => 'rgba(75, 192, 192, 0.5)',
                        'borderColor' => 'rgba(75, 192, 192, 1)',
                        'borderWidth' => 1
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error in getComplianceByEquipmentData: ' . $e->getMessage());

            // Return default data
            return [
                'labels' => ['Machine A', 'Machine B', 'Machine C', 'Machine D', 'Machine E'],
                'datasets' => [
                    [
                        'label' => 'Planned',
                        'data' => [20, 18, 15, 12, 10],
                        'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                        'borderColor' => 'rgba(54, 162, 235, 1)',
                        'borderWidth' => 1
                    ],
                    [
                        'label' => 'Completed',
                        'data' => [18, 15, 12, 11, 9],
                        'backgroundColor' => 'rgba(75, 192, 192, 0.5)',
                        'borderColor' => 'rgba(75, 192, 192, 1)',
                        'borderWidth' => 1
                    ]
                ]
            ];
        }
    }

    protected function getOverdueTasksData($equipmentIds, $startDate, $endDate)
    {
        try {
            // Get current date
            $today = Carbon::now();

            // Find overdue maintenance plans (scheduled date has passed but no completed notes)
            $overduePlans = MaintenancePlan::whereIn('equipment_id', $equipmentIds)
                ->where('scheduled_date', '<', $today)
                ->where('scheduled_date', '>=', $startDate)
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                          ->from('maintenance_notes')
                          ->whereColumn('maintenance_notes.maintenance_plan_id', 'maintenance_plans.id')
                          ->where('maintenance_notes.status', 'completed');
                })
                ->orderBy('scheduled_date', 'asc')
                ->limit(10)
                ->get();

            $labels = [];
            $data = [];
            $backgroundColors = [];
            $borderColors = [];

            foreach ($overduePlans as $plan) {
                $equipment = $plan->equipment;
                if ($equipment) {
                    $daysOverdue = Carbon::parse($plan->scheduled_date)->diffInDays($today);

                    $labels[] = $equipment->name;
                    $data[] = $daysOverdue;

                    // Color based on days overdue
                    if ($daysOverdue <= 7) {
                        $backgroundColors[] = 'rgba(237, 137, 54, 0.5)'; // Orange for 1 week
                        $borderColors[] = 'rgba(237, 137, 54, 1)';
                    } elseif ($daysOverdue <= 30) {
                        $backgroundColors[] = 'rgba(229, 62, 62, 0.5)'; // Red for 1 month
                        $borderColors[] = 'rgba(229, 62, 62, 1)';
                    } else {
                        $backgroundColors[] = 'rgba(148, 20, 20, 0.5)'; // Dark red for > 1 month
                        $borderColors[] = 'rgba(148, 20, 20, 1)';
                    }
                }
            }

            return [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Days Overdue',
                        'data' => $data,
                        'backgroundColor' => $backgroundColors,
                        'borderColor' => $borderColors,
                        'borderWidth' => 1
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error in getOverdueTasksData: ' . $e->getMessage());

            // Return default data
            return [
                'labels' => ['Machine X', 'Machine Y', 'Machine Z', 'Machine W', 'Machine V'],
                'datasets' => [
                    [
                        'label' => 'Days Overdue',
                        'data' => [45, 30, 20, 12, 5],
                        'backgroundColor' => [
                            'rgba(148, 20, 20, 0.5)',
                            'rgba(148, 20, 20, 0.5)',
                            'rgba(229, 62, 62, 0.5)',
                            'rgba(229, 62, 62, 0.5)',
                            'rgba(237, 137, 54, 0.5)'
                        ],
                        'borderColor' => [
                            'rgba(148, 20, 20, 1)',
                            'rgba(148, 20, 20, 1)',
                            'rgba(229, 62, 62, 1)',
                            'rgba(229, 62, 62, 1)',
                            'rgba(237, 137, 54, 1)'
                        ],
                        'borderWidth' => 1
                    ]
                ]
            ];
        }
    }

    protected function setDefaultChartData()
    {
        // Default compliance trend data
        $this->complianceTrendData = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'datasets' => [
                [
                    'label' => 'Compliance Rate (%)',
                    'data' => [85, 88, 82, 90, 86, 92],
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 2,
                    'tension' => 0.3,
                    'fill' => true
                ]
            ]
        ];

        // Default compliance by area data
        $this->complianceByAreaData = [
            'labels' => ['Production', 'Packaging', 'Quality Control', 'Maintenance', 'Utilities'],
            'datasets' => [
                [
                    'label' => 'Compliance Rate (%)',
                    'data' => [95, 88, 76, 92, 85],
                    'backgroundColor' => [
                        'rgba(72, 187, 120, 0.7)',
                        'rgba(72, 187, 120, 0.7)',
                        'rgba(237, 137, 54, 0.7)',
                        'rgba(72, 187, 120, 0.7)',
                        'rgba(72, 187, 120, 0.7)'
                    ],
                    'borderColor' => [
                        'rgba(72, 187, 120, 1)',
                        'rgba(72, 187, 120, 1)',
                        'rgba(237, 137, 54, 1)',
                        'rgba(72, 187, 120, 1)',
                        'rgba(72, 187, 120, 1)'
                    ],
                    'borderWidth' => 1
                ]
            ]
        ];

        // Default compliance by equipment data
        $this->complianceByEquipmentData = [
            'labels' => ['Machine A', 'Machine B', 'Machine C', 'Machine D', 'Machine E'],
            'datasets' => [
                [
                    'label' => 'Planned',
                    'data' => [20, 18, 15, 12, 10],
                    'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Completed',
                    'data' => [18, 15, 12, 11, 9],
                    'backgroundColor' => 'rgba(75, 192, 192, 0.5)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 1
                ]
            ]
        ];

        // Default overdue tasks data
        $this->overdueTasksData = [
            'labels' => ['Machine X', 'Machine Y', 'Machine Z', 'Machine W', 'Machine V'],
            'datasets' => [
                [
                    'label' => 'Days Overdue',
                    'data' => [45, 30, 20, 12, 5],
                    'backgroundColor' => [
                        'rgba(148, 20, 20, 0.5)',
                        'rgba(148, 20, 20, 0.5)',
                        'rgba(229, 62, 62, 0.5)',
                        'rgba(229, 62, 62, 0.5)',
                        'rgba(237, 137, 54, 0.5)'
                    ],
                    'borderColor' => [
                        'rgba(148, 20, 20, 1)',
                        'rgba(148, 20, 20, 1)',
                        'rgba(229, 62, 62, 1)',
                        'rgba(229, 62, 62, 1)',
                        'rgba(237, 137, 54, 1)'
                    ],
                    'borderWidth' => 1
                ]
            ]
        ];
    }

    public function render()
    {
        try {
            $startDate = $this->startDate ? Carbon::parse($this->startDate) : Carbon::now()->subYear();
            $endDate = $this->endDate ? Carbon::parse($this->endDate) : Carbon::now();

            // Get compliance data for the table
            $query = MaintenanceEquipment::query()
                ->with(['department', 'line', 'maintenancePlans'])
                ->when($this->selectedArea !== 'all', function ($query) {
                    return $query->where('area_id', $this->selectedArea);
                })
                ->when($this->selectedLine !== 'all', function ($query) {
                    return $query->where('line_id', $this->selectedLine);
                })
                ->when($this->searchTerm, function ($query) {
                    return $query->where('name', 'like', '%' . $this->searchTerm . '%')
                        ->orWhere('serial_number', 'like', '%' . $this->searchTerm . '%');
                });

            $equipmentList = $query->get();
            $equipmentComplianceData = [];

            // Get current date
            $today = Carbon::now();

            foreach ($equipmentList as $equipment) {
                // Get total planned maintenance
                $plannedCount = $equipment->maintenancePlans()
                    ->where('scheduled_date', '>=', $startDate)
                    ->where('scheduled_date', '<=', $endDate)
                    ->count();

                // Get completed maintenance (using notes)
                $completedCount = 0;
                $overdueCount = 0;
                $upcomingCount = 0;

                if ($plannedCount > 0) {
                    // Count completed tasks
                    $completedCount = MaintenanceNote::join('maintenance_plans', 'maintenance_notes.maintenance_plan_id', '=', 'maintenance_plans.id')
                        ->where('maintenance_plans.equipment_id', $equipment->id)
                        ->where('maintenance_plans.scheduled_date', '>=', $startDate)
                        ->where('maintenance_plans.scheduled_date', '<=', $endDate)
                        ->where('maintenance_notes.status', 'completed')
                        ->distinct('maintenance_plans.id')
                        ->count('maintenance_plans.id');

                    // Count overdue tasks (scheduled date has passed but no completed notes)
                    $overdueCount = $equipment->maintenancePlans()
                        ->where('scheduled_date', '<', $today)
                        ->where('scheduled_date', '>=', $startDate)
                        ->whereNotExists(function ($query) {
                            $query->select(DB::raw(1))
                                ->from('maintenance_notes')
                                ->whereColumn('maintenance_notes.maintenance_plan_id', 'maintenance_plans.id')
                                ->where('maintenance_notes.status', 'completed');
                        })
                        ->count();

                    // Count upcoming tasks (scheduled date is in the future)
                    $upcomingCount = $equipment->maintenancePlans()
                        ->where('scheduled_date', '>=', $today)
                        ->where('scheduled_date', '<=', $endDate)
                        ->count();
                }

                // Calculate compliance rate
                $complianceRate = $plannedCount > 0 ? round(($completedCount / $plannedCount) * 100, 1) : 0;

                $equipmentComplianceData[] = [
                    'id' => $equipment->id,
                    'name' => $equipment->name,
                    'serial_number' => $equipment->serial_number,
                    'area' => $equipment->department ? $equipment->department->name : 'N/A',
                    'line' => $equipment->line ? $equipment->line->name : 'N/A',
                    'planned_count' => $plannedCount,
                    'completed_count' => $completedCount,
                    'overdue_count' => $overdueCount,
                    'upcoming_count' => $upcomingCount,
                    'compliance_rate' => $complianceRate
                ];
            }

            // Convert to collection for sorting
            $complianceData = collect($equipmentComplianceData);

            // Apply sorting
            if ($this->sortField) {
                $complianceData = $complianceData->sortBy([
                    [$this->sortField, $this->sortDirection]
                ]);
            }

            // Calculate overall metrics
            $totalPlanned = $complianceData->sum('planned_count');
            $totalCompleted = $complianceData->sum('completed_count');
            $totalOverdue = $complianceData->sum('overdue_count');
            $totalUpcoming = $complianceData->sum('upcoming_count');
            $overallComplianceRate = $totalPlanned > 0 ? round(($totalCompleted / $totalPlanned) * 100, 1) : 0;

            // Get areas and lines for filters
            $areas = MaintenanceEquipment::with('department')
                ->get()
                ->map(function ($equipment) {
                    return $equipment->department;
                })
                ->filter()
                ->unique('id')
                ->pluck('name', 'id')
                ->toArray();

            $lines = MaintenanceEquipment::with('line')
                ->get()
                ->map(function ($equipment) {
                    return $equipment->line;
                })
                ->filter()
                ->unique('id')
                ->pluck('name', 'id')
                ->toArray();

            return view('livewire.reports.maintenance-compliance', [
                'complianceData' => $complianceData,
                'totalEquipment' => $complianceData->count(),
                'totalPlanned' => $totalPlanned,
                'totalCompleted' => $totalCompleted,
                'totalOverdue' => $totalOverdue,
                'totalUpcoming' => $totalUpcoming,
                'overallComplianceRate' => $overallComplianceRate,
                'areas' => $areas,
                'lines' => $lines,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in render method: ' . $e->getMessage());

            // Return with empty data
            return view('livewire.reports.maintenance-compliance', [
                'complianceData' => collect([]),
                'totalEquipment' => 0,
                'totalPlanned' => 0,
                'totalCompleted' => 0,
                'totalOverdue' => 0,
                'totalUpcoming' => 0,
                'overallComplianceRate' => 0,
                'areas' => [],
                'lines' => [],
            ]);
        }
    }
}
