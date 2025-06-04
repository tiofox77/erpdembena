<?php

namespace App\Livewire\Reports;

use App\Models\MaintenanceArea;
use App\Models\MaintenanceCorrective;
use App\Models\MaintenanceEquipment;
use App\Models\MaintenanceLine;
use App\Models\MaintenancePlan;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class MaintenanceTypes extends Component
{
    public $dateRange = 'month';
    public $startDate;
    public $endDate;
    public $selectedArea = 'all';
    public $selectedLine = 'all';
    public $searchTerm = '';
    public $sortField = 'equipment_name';
    public $sortDirection = 'asc';

    public $areas = [];
    public $lines = [];

    public $totalAll = 0;
    public $totalPreventive = 0;
    public $totalCorrective = 0;
    public $totalPredictive = 0;
    public $preventivePercentage = 0;
    public $correctivePercentage = 0;
    public $predictivePercentage = 0;
    public $totalEquipment = 0;

    public $maintenanceData = [];
    public $maintenanceDistributionData = [];
    public $monthlyMaintenanceData = [];
    public $preventiveComplianceData = [];
    public $correctiveByAreaData = [];

    protected $listeners = ['refresh' => '$refresh'];

    public function mount()
    {
        // Log debug message
        Log::info('MaintenanceTypes component mounting');

        // Set default dates (last year)
        $this->endDate = Carbon::now()->format('Y-m-d');
        $this->startDate = Carbon::now()->subYear()->format('Y-m-d');

        // Load areas and lines
        $this->loadAreasAndLines();

        // Load initial data
        $this->loadChartData();
    }

    private function loadAreasAndLines()
    {
        // Check if equipment is registered and log
        $equipmentCount = MaintenanceEquipment::count();
        Log::info("Registered equipment: {$equipmentCount}");

        if ($equipmentCount == 0) {
            Log::warning("No equipment registered in the system");
        }

        // Check if areas are registered and log
        $areasCount = MaintenanceArea::count();
        Log::info("Registered areas: {$areasCount}");

        if ($areasCount == 0) {
            Log::warning("No areas registered in the system");
        }

        // Load areas
        $areas = MaintenanceArea::orderBy('name')->get();
        $this->areas = $areas->pluck('name', 'id')->toArray();

        // Check if lines are registered and log
        $linesCount = MaintenanceLine::count();
        Log::info("Registered lines: {$linesCount}");

        if ($linesCount == 0) {
            Log::warning("No lines registered in the system");
        }

        // Load lines
        $lines = MaintenanceLine::orderBy('name')->get();
        $this->lines = $lines->pluck('name', 'id')->toArray();

        // Check if maintenance plans are registered and log
        $plansCount = MaintenancePlan::count();
        Log::info("Registered maintenance plans: {$plansCount}");

        if ($plansCount == 0) {
            Log::warning("No maintenance plans registered in the system");
        }

        // Check if corrective maintenance is registered and log
        $correctivesCount = MaintenanceCorrective::count();
        Log::info("Registered corrective maintenance: {$correctivesCount}");

        if ($correctivesCount == 0) {
            Log::warning("No corrective maintenance registered in the system");
        }
    }

    public function updatedDateRange()
    {
        // Adjust dates based on the selected period
        $now = Carbon::now();

        switch ($this->dateRange) {
            case 'week':
                $this->startDate = $now->copy()->startOfWeek()->format('Y-m-d');
                $this->endDate = $now->copy()->endOfWeek()->format('Y-m-d');
                break;
            case 'month':
                $this->startDate = $now->copy()->startOfMonth()->format('Y-m-d');
                $this->endDate = $now->copy()->endOfMonth()->format('Y-m-d');
                break;
            case 'quarter':
                $this->startDate = $now->copy()->startOfQuarter()->format('Y-m-d');
                $this->endDate = $now->copy()->endOfQuarter()->format('Y-m-d');
                break;
            case 'year':
                $this->startDate = $now->copy()->startOfYear()->format('Y-m-d');
                $this->endDate = $now->copy()->endOfYear()->format('Y-m-d');
                break;
        }

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

    public function updatedSearchTerm()
    {
        $this->loadChartData();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        // Reorder the data
        $this->maintenanceData = collect($this->maintenanceData)
            ->sortBy([$this->sortField => $this->sortDirection === 'asc' ? 'asc' : 'desc'])
            ->values()
            ->toArray();
    }

    private function loadChartData()
    {
        Log::info('Loading data for maintenance types report', [
            'dateRange' => $this->dateRange,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'selectedArea' => $this->selectedArea,
            'selectedLine' => $this->selectedLine,
            'searchTerm' => $this->searchTerm
        ]);

        // First, verify data exists in the database
        $plansCount = MaintenancePlan::count();
        $correctivesCount = MaintenanceCorrective::count();

        Log::info("Database counts: Plans: {$plansCount}, Correctives: {$correctivesCount}");

        // Get available plan types
        $availableTypes = DB::table('maintenance_plans')
            ->select('type')
            ->whereNotNull('type')
            ->distinct()
            ->get()
            ->pluck('type')
            ->toArray();

        Log::info("Available maintenance plan types: " . implode(', ', $availableTypes ?: ['none']));

        // Filter equipment based on selected filters
        $equipmentQuery = MaintenanceEquipment::query();

        // Apply area filter
        if ($this->selectedArea !== 'all') {
            $equipmentQuery->where('area_id', $this->selectedArea);
        }

        // Apply line filter
        if ($this->selectedLine !== 'all') {
            $equipmentQuery->where('line_id', $this->selectedLine);
        }

        // Apply search filter
        if (!empty($this->searchTerm)) {
            $equipmentQuery->where(function ($query) {
                $query->where('name', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('serial_number', 'like', '%' . $this->searchTerm . '%');
            });
        }

        // Get equipment with relationships
        $equipment = $equipmentQuery->with(['area', 'line'])->get();

        // Log the number of equipment found
        Log::info("Equipment found after filters: " . $equipment->count());

        // If no equipment, initialize empty data and return
        if ($equipment->isEmpty()) {
            Log::warning("No equipment found with applied filters");
            $this->initializeEmptyData();
            return;
        }

        // Equipment IDs to filter maintenance
        $equipmentIds = $equipment->pluck('id')->toArray();

        // Start and end dates to filter maintenance
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate);

        // Get maintenance plans (preventive and predictive)
        try {
            // First get ALL plans to see what we have
            $allPlans = MaintenancePlan::whereIn('equipment_id', $equipmentIds)
                ->whereBetween('scheduled_date', [$startDate, $endDate])
                ->get();

            Log::info("All maintenance plans found: " . $allPlans->count());

            // Collect types actually present in the data
            $typesInData = $allPlans->pluck('type')->unique()->filter()->toArray();
            Log::info("Types found in data: " . implode(', ', $typesInData ?: ['none']));

            // Adjust filter conditions based on what's in the data
            // Set default types to check if none are found
            $preventiveTypes = ['preventive', 'preventiva', 'Preventive', 'Preventiva'];
            $predictiveTypes = ['predictive', 'preditiva', 'Predictive', 'Preditiva'];

            $plans = $allPlans;
        } catch (\Exception $e) {
            Log::error("Error getting maintenance plans: " . $e->getMessage());
            $plans = collect();
        }

        // Get corrective maintenance
        try {
            $correctives = MaintenanceCorrective::whereIn('equipment_id', $equipmentIds)
                ->whereBetween('start_time', [$startDate, $endDate])
                ->get();

            Log::info("Corrective maintenance found: " . $correctives->count());
        } catch (\Exception $e) {
            Log::error("Error getting corrective maintenance: " . $e->getMessage());
            $correctives = collect();
        }

        // Prepare data for visualization
        $this->prepareMaintenanceData($equipment, $plans, $correctives, $preventiveTypes, $predictiveTypes);

        // Prepare data for charts
        $this->maintenanceDistributionData = $this->getMaintenanceDistributionData();
        $this->monthlyMaintenanceData = $this->getMonthlyMaintenanceData($startDate, $endDate, $equipmentIds, $preventiveTypes, $predictiveTypes);
        $this->preventiveComplianceData = $this->getPreventiveComplianceData($preventiveTypes);
        $this->correctiveByAreaData = $this->getCorrectiveByAreaData();
    }

    private function initializeEmptyData()
    {
        // Initialize counters
        $this->totalAll = 0;
        $this->totalPreventive = 0;
        $this->totalCorrective = 0;
        $this->totalPredictive = 0;
        $this->preventivePercentage = 0;
        $this->correctivePercentage = 0;
        $this->predictivePercentage = 0;
        $this->totalEquipment = 0;

        // Initialize maintenance data
        $this->maintenanceData = [];

        // Initialize chart data
        $this->maintenanceDistributionData = [
            'labels' => ['Preventive', 'Corrective', 'Predictive'],
            'datasets' => [
                [
                    'data' => [0, 0, 0],
                    'backgroundColor' => ['#3B82F6', '#EF4444', '#10B981'],
                ]
            ]
        ];

        // Initialize monthly data (last 3 months)
        $months = [];
        $now = Carbon::now();
        for ($i = 2; $i >= 0; $i--) {
            $months[] = $now->copy()->subMonths($i)->format('M Y');
        }

        $this->monthlyMaintenanceData = [
            'labels' => $months,
            'datasets' => [
                [
                    'label' => 'Preventive',
                    'data' => array_fill(0, count($months), 0),
                    'backgroundColor' => '#3B82F6',
                ],
                [
                    'label' => 'Corrective',
                    'data' => array_fill(0, count($months), 0),
                    'backgroundColor' => '#EF4444',
                ],
                [
                    'label' => 'Predictive',
                    'data' => array_fill(0, count($months), 0),
                    'backgroundColor' => '#10B981',
                ]
            ]
        ];

        // Initialize preventive compliance data
        $this->preventiveComplianceData = [
            'labels' => ['Completed', 'Not Completed'],
            'datasets' => [
                [
                    'data' => [0, 100],
                    'backgroundColor' => ['#3B82F6', '#E5E7EB'],
                ]
            ]
        ];

        // Initialize corrective by area data
        $this->correctiveByAreaData = [
            'labels' => array_values($this->areas),
            'datasets' => [
                [
                    'data' => array_fill(0, count($this->areas), 0),
                    'backgroundColor' => '#EF4444',
                ]
            ]
        ];
    }

    private function prepareMaintenanceData($equipment, $plans, $correctives, $preventiveTypes, $predictiveTypes)
    {
        // Initialize counters
        $this->totalAll = 0;
        $this->totalPreventive = 0;
        $this->totalCorrective = 0;
        $this->totalPredictive = 0;
        $this->totalEquipment = $equipment->count();

        // Prepare data by equipment
        $maintenanceData = [];

        foreach ($equipment as $eq) {
            // Count maintenance plans by type
            $preventiveCount = $plans->where('equipment_id', $eq->id)
                ->filter(function($plan) use ($preventiveTypes) {
                    return in_array($plan->type, $preventiveTypes) ||
                           preg_match('/prevent/i', $plan->type);
                })
                ->count();

            $predictiveCount = $plans->where('equipment_id', $eq->id)
                ->filter(function($plan) use ($predictiveTypes) {
                    return in_array($plan->type, $predictiveTypes) ||
                           preg_match('/predict/i', $plan->type);
                })
                ->count();

            // Count corrective maintenance
            $correctiveCount = $correctives->where('equipment_id', $eq->id)->count();

            // Calculate total by equipment
            $totalActions = $preventiveCount + $correctiveCount + $predictiveCount;

            // Calculate percentages
            $preventivePercentage = $totalActions > 0 ? round(($preventiveCount / $totalActions) * 100) : 0;
            $correctivePercentage = $totalActions > 0 ? round(($correctiveCount / $totalActions) * 100) : 0;
            $predictivePercentage = $totalActions > 0 ? round(($predictiveCount / $totalActions) * 100) : 0;

            // Add to general totals
            $this->totalPreventive += $preventiveCount;
            $this->totalCorrective += $correctiveCount;
            $this->totalPredictive += $predictiveCount;
            $this->totalAll += $totalActions;

            // If there is at least one maintenance action, add to the data array
            if ($totalActions > 0) {
                $maintenanceData[] = [
                    'equipment_id' => $eq->id,
                    'equipment_name' => $eq->name,
                    'serial_number' => $eq->serial_number,
                    'area' => $eq->area ? $eq->area->name : 'Area not defined',
                    'area_id' => $eq->area_id,
                    'line' => $eq->line ? $eq->line->name : 'Line not defined',
                    'line_id' => $eq->line_id,
                    'preventive_count' => $preventiveCount,
                    'corrective_count' => $correctiveCount,
                    'predictive_count' => $predictiveCount,
                    'total_actions' => $totalActions,
                    'preventive_percentage' => $preventivePercentage,
                    'corrective_percentage' => $correctivePercentage,
                    'predictive_percentage' => $predictivePercentage,
                ];
            }
        }

        // Calculate general percentages
        $this->preventivePercentage = $this->totalAll > 0 ? round(($this->totalPreventive / $this->totalAll) * 100) : 0;
        $this->correctivePercentage = $this->totalAll > 0 ? round(($this->totalCorrective / $this->totalAll) * 100) : 0;
        $this->predictivePercentage = $this->totalAll > 0 ? round(($this->totalPredictive / $this->totalAll) * 100) : 0;

        // Sort the data
        $this->maintenanceData = collect($maintenanceData)
            ->sortBy([$this->sortField => $this->sortDirection === 'asc' ? 'asc' : 'desc'])
            ->values()
            ->toArray();
    }

    private function getMaintenanceDistributionData()
    {
        // Initialize data for the distribution chart
        return [
            'labels' => ['Preventive', 'Corrective', 'Predictive'],
            'datasets' => [
                [
                    'data' => [$this->totalPreventive, $this->totalCorrective, $this->totalPredictive],
                    'backgroundColor' => ['#3B82F6', '#EF4444', '#10B981'],
                ]
            ]
        ];
    }

    private function getMonthlyMaintenanceData($startDate, $endDate, $equipmentIds, $preventiveTypes, $predictiveTypes)
    {
        // Minimum period of 3 months for the chart
        $periodStart = Carbon::parse($startDate);
        $periodEnd = Carbon::parse($endDate);

        // If the period is less than 3 months, adjust to show at least 3 months
        if ($periodStart->diffInMonths($periodEnd) < 2) {
            $periodStart = $periodEnd->copy()->subMonths(2)->startOfMonth();
        }

        // Create array of months in the period
        $months = [];
        $currentDate = $periodStart->copy()->startOfMonth();
        while ($currentDate->lte($periodEnd)) {
            $months[] = $currentDate->format('M Y');
            $currentDate->addMonth();
        }

        // Initialize data
        $preventiveData = array_fill(0, count($months), 0);
        $correctiveData = array_fill(0, count($months), 0);
        $predictiveData = array_fill(0, count($months), 0);

        // If there is no equipment, return empty data
        if (empty($equipmentIds)) {
            return [
                'labels' => $months,
                'datasets' => [
                    [
                        'label' => 'Preventive',
                        'data' => $preventiveData,
                        'backgroundColor' => '#3B82F6',
                    ],
                    [
                        'label' => 'Corrective',
                        'data' => $correctiveData,
                        'backgroundColor' => '#EF4444',
                    ],
                    [
                        'label' => 'Predictive',
                        'data' => $predictiveData,
                        'backgroundColor' => '#10B981',
                    ]
                ]
            ];
        }

        // Get all plans for the period
        try {
            $allPlans = MaintenancePlan::whereIn('equipment_id', $equipmentIds)
                ->whereBetween('scheduled_date', [$periodStart, $periodEnd])
                ->get();

            // Process plan data by month
            foreach ($allPlans as $plan) {
                $planDate = Carbon::parse($plan->scheduled_date);
                $monthIndex = $planDate->format('M Y');
                $index = array_search($monthIndex, $months);

                if ($index !== false) {
                    if (in_array($plan->type, $preventiveTypes) || preg_match('/prevent/i', $plan->type)) {
                        $preventiveData[$index]++;
                    } elseif (in_array($plan->type, $predictiveTypes) || preg_match('/predict/i', $plan->type)) {
                        $predictiveData[$index]++;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Error processing monthly plans: " . $e->getMessage());
        }

        // Get correctives by month
        try {
            $monthlyCorrectives = MaintenanceCorrective::whereIn('equipment_id', $equipmentIds)
                ->whereBetween('start_time', [$periodStart, $periodEnd])
                ->get();

            // Process corrective data by month
            foreach ($monthlyCorrectives as $corrective) {
                $correctiveDate = Carbon::parse($corrective->start_time);
                $monthIndex = $correctiveDate->format('M Y');
                $index = array_search($monthIndex, $months);

                if ($index !== false) {
                    $correctiveData[$index]++;
                }
            }
        } catch (\Exception $e) {
            Log::error("Error processing monthly correctives: " . $e->getMessage());
        }

        // Return formatted data
        return [
            'labels' => $months,
            'datasets' => [
                [
                    'label' => 'Preventive',
                    'data' => $preventiveData,
                    'backgroundColor' => '#3B82F6',
                ],
                [
                    'label' => 'Corrective',
                    'data' => $correctiveData,
                    'backgroundColor' => '#EF4444',
                ],
                [
                    'label' => 'Predictive',
                    'data' => $predictiveData,
                    'backgroundColor' => '#10B981',
                ]
            ]
        ];
    }

    private function getPreventiveComplianceData($preventiveTypes)
    {
        // Initialize values
        $completedCount = 0;
        $pendingCount = 0;
        $totalExpected = 0;

        // Try to get real data
        try {
            $startDate = Carbon::parse($this->startDate);
            $endDate = Carbon::parse($this->endDate);

            // Filter equipment
            $equipmentQuery = MaintenanceEquipment::query();

            if ($this->selectedArea !== 'all') {
                $equipmentQuery->where('area_id', $this->selectedArea);
            }

            if ($this->selectedLine !== 'all') {
                $equipmentQuery->where('line_id', $this->selectedLine);
            }

            if (!empty($this->searchTerm)) {
                $equipmentQuery->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->searchTerm . '%')
                        ->orWhere('serial_number', 'like', '%' . $this->searchTerm . '%');
                });
            }

            $equipmentIds = $equipmentQuery->pluck('id')->toArray();

            if (empty($equipmentIds)) {
                Log::info("No equipment found for compliance calculation");
                return $this->getMinimalComplianceData();
            }

            // Get preventive maintenance plans for these equipment
            $plans = MaintenancePlan::whereIn('equipment_id', $equipmentIds)
                ->where(function($query) use ($preventiveTypes) {
                    $query->whereIn('type', $preventiveTypes)
                        ->orWhere(function($q) {
                            $q->whereNotNull('type')
                              ->where('type', 'like', '%prevent%');
                        });
                })
                ->whereBetween('scheduled_date', [$startDate, $endDate])
                ->with('notes') // Eager load notes for completion status
                ->get();

            Log::info("Found " . $plans->count() . " preventive plans for compliance calculation");

            // Calculate frequency-based expected and completed counts
            foreach ($plans as $plan) {
                // Calculate how many maintenance instances should exist in the date range based on frequency
                if ($plan->frequency_type == 'once') {
                    // If frequency is 'once', there should be just one instance
                    $totalExpected++;

                    // Check if completed using notes
                    $isCompleted = $this->isPlanCompleted($plan);
                    if ($isCompleted) {
                        $completedCount++;
                    } else {
                        $pendingCount++;
                    }
                } else {
                    // For recurring frequencies (daily, weekly, monthly, etc.)
                    $frequency = $this->getFrequencyInDays($plan);

                    if ($frequency > 0) {
                        // Calculate how many times maintenance should occur in the date range
                        $planStart = max($startDate, Carbon::parse($plan->scheduled_date));
                        $daysInRange = min($endDate, Carbon::now())->diffInDays($planStart) + 1;
                        $expectedInstances = ceil($daysInRange / $frequency);

                        $totalExpected += $expectedInstances;

                        // Count actual completed instances from notes
                        $completedInstances = $plan->notes()
                            ->where('status', 'completed')
                            ->whereBetween('created_at', [$startDate, $endDate])
                            ->distinct('created_at', 'DATE(created_at)') // Prevent duplicates on the same day
                            ->count();

                        $completedCount += $completedInstances;
                        $pendingCount += ($expectedInstances - $completedInstances);
                    }
                }
            }

            Log::info("Preventive compliance calculation: Expected: $totalExpected, Completed: $completedCount, Pending: $pendingCount");
        } catch (\Exception $e) {
            Log::error("Error calculating preventive compliance: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return $this->getMinimalComplianceData();
        }

        // Calculate percentages
        $total = $completedCount + $pendingCount;
        $completedPercentage = $total > 0 ? round(($completedCount / $total) * 100) : 0;
        $pendingPercentage = $total > 0 ? 100 - $completedPercentage : 0;

        // Return formatted data
        return [
            'labels' => ['Completed', 'Not Completed'],
            'datasets' => [
                [
                    'data' => [$completedPercentage, $pendingPercentage],
                    'backgroundColor' => ['#3B82F6', '#E5E7EB'],
                ]
            ]
        ];
    }

    /**
     * Check if a maintenance plan is completed
     */
    private function isPlanCompleted($plan)
    {
        // First check the plan's own status
        if ($plan->status === 'completed') {
            return true;
        }

        // Then check if there are any notes marking it as completed
        if ($plan->relationLoaded('notes')) {
            return $plan->notes->where('status', 'completed')->count() > 0;
        }

        // If notes aren't loaded, query directly
        return $plan->notes()->where('status', 'completed')->exists();
    }

    /**
     * Get frequency in days for a maintenance plan
     */
    private function getFrequencyInDays($plan)
    {
        switch ($plan->frequency_type) {
            case 'daily':
                return 1;
            case 'weekly':
                return 7;
            case 'monthly':
                return 30; // Approximation
            case 'quarterly':
                return 90; // Approximation
            case 'yearly':
                return 365; // Approximation
            case 'custom':
                return $plan->custom_days ?? 0;
            default:
                return 0;
        }
    }

    /**
     * Get minimal compliance data for the chart
     */
    private function getMinimalComplianceData()
    {
        return [
            'labels' => ['Completed', 'Not Completed'],
            'datasets' => [
                [
                    'data' => [0, 100],
                    'backgroundColor' => ['#3B82F6', '#E5E7EB'],
                ]
            ]
        ];
    }

    private function getCorrectiveByAreaData()
    {
        // Initialize array for counting
        $areaLabels = [];
        $areaCounts = [];

        try {
            // Start and end dates
            $startDate = Carbon::parse($this->startDate);
            $endDate = Carbon::parse($this->endDate);

            // Prepare base query for correctives
            $correctiveQuery = MaintenanceCorrective::query()
                ->join('maintenance_equipment', 'maintenance_correctives.equipment_id', '=', 'maintenance_equipment.id')
                ->join('maintenance_areas', 'maintenance_equipment.area_id', '=', 'maintenance_areas.id')
                ->whereBetween('maintenance_correctives.start_time', [$startDate, $endDate]);

            // Apply line filter if necessary
            if ($this->selectedLine !== 'all') {
                $correctiveQuery->where('maintenance_equipment.line_id', $this->selectedLine);
            }

            // Apply search filter if necessary
            if (!empty($this->searchTerm)) {
                $correctiveQuery->where(function ($query) {
                    $query->where('maintenance_equipment.name', 'like', '%' . $this->searchTerm . '%')
                        ->orWhere('maintenance_equipment.serial_number', 'like', '%' . $this->searchTerm . '%');
                });
            }

            // Group by area and count
            $areaCorrecives = $correctiveQuery
                ->select('maintenance_areas.id', 'maintenance_areas.name', DB::raw('count(*) as count'))
                ->groupBy('maintenance_areas.id', 'maintenance_areas.name')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get();

            // Prepare data for the chart
            foreach ($areaCorrecives as $item) {
                $areaLabels[] = $item->name;
                $areaCounts[] = $item->count;
            }
        } catch (\Exception $e) {
            Log::error("Error getting correctives by area: " . $e->getMessage());
        }

        // If there is no data, use existing areas
        if (empty($areaLabels)) {
            // Limit to 10 areas for the chart
            $areas = array_slice($this->areas, 0, 10);
            $areaLabels = array_values($areas);
            $areaCounts = array_fill(0, count($areaLabels), 0);
        }

        // Return formatted data
        return [
            'labels' => $areaLabels,
            'datasets' => [
                [
                    'data' => $areaCounts,
                    'backgroundColor' => '#EF4444',
                ]
            ]
        ];
    }

    public function render()
    {
        return view('livewire.reports.maintenance-types');
    }
}

