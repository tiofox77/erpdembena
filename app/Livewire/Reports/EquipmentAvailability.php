<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MaintenanceEquipment;
use App\Models\MaintenanceCorrective;
use App\Models\MaintenanceArea;
use App\Models\MaintenanceLine;
use App\Models\MaintenancePlan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EquipmentAvailability extends Component
{
    use WithPagination;

    public $dateRange = 'month';
    public $startDate;
    public $endDate;
    public $selectedArea = 'all';
    public $selectedLine = 'all';
    public $searchTerm = '';
    public $sortField = 'availability';
    public $sortDirection = 'desc';

    // Chart data
    public $availabilityData = [];
    public $mtbfData = [];
    public $mttrData = [];

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
            // Get equipment filtered by area and line
            $query = MaintenanceEquipment::query()
                ->when($this->selectedArea !== 'all', function ($query) {
                    return $query->where('area_id', $this->selectedArea);
                })
                ->when($this->selectedLine !== 'all', function ($query) {
                    return $query->where('line_id', $this->selectedLine);
                });

            // Get top 5 equipment for charts
            $equipmentIds = $query->pluck('id')->toArray();

            // Get downtime data for each equipment
            $startDate = Carbon::parse($this->startDate);
            $endDate = Carbon::parse($this->endDate)->endOfDay();

            // Get equipment with most downtime for charts (top 5)
            $topEquipment = MaintenanceCorrective::whereIn('equipment_id', $equipmentIds)
                ->whereBetween('start_time', [$startDate, $endDate])
                ->select('equipment_id', DB::raw('SUM(downtime_length) as total_downtime'))
                ->groupBy('equipment_id')
                ->orderByDesc('total_downtime')
                ->limit(5)
                ->get();

            if ($topEquipment->isEmpty()) {
                $this->setDefaultChartData();
                return;
            }

            $topEquipmentIds = $topEquipment->pluck('equipment_id')->toArray();

            // Get equipment names for the chart labels
            $equipmentNames = MaintenanceEquipment::whereIn('id', $topEquipmentIds)
                ->pluck('name', 'id')
                ->toArray();

            // Initialize chart data arrays
            $availabilityValues = [];
            $mtbfValues = [];
            $mttrValues = [];
            $chartLabels = [];

            // For each top equipment, calculate metrics
            foreach ($topEquipmentIds as $equipmentId) {
                $equipmentName = $equipmentNames[$equipmentId] ?? "Equipment #$equipmentId";
                $chartLabels[] = $equipmentName;

                // Get failures for this equipment in the date range
                $failures = MaintenanceCorrective::where('equipment_id', $equipmentId)
                    ->whereBetween('start_time', [$startDate, $endDate])
                    ->get();

                $failureCount = $failures->count();
                $totalDowntime = $failures->sum('downtime_length');

                // Calculate total period in hours
                $totalPeriodHours = $startDate->diffInHours($endDate);

                // Calculate availability (uptime / total time)
                $availability = $totalPeriodHours > 0
                    ? (($totalPeriodHours - $totalDowntime) / $totalPeriodHours) * 100
                    : 100;

                // Calculate MTBF (Mean Time Between Failures)
                $mtbf = $failureCount > 0
                    ? ($totalPeriodHours - $totalDowntime) / $failureCount
                    : $totalPeriodHours; // If no failures, MTBF is the entire period

                // Calculate MTTR (Mean Time To Repair)
                $mttr = $failureCount > 0
                    ? $totalDowntime / $failureCount
                    : 0; // If no failures, MTTR is 0

                $availabilityValues[] = round($availability, 2);
                $mtbfValues[] = round($mtbf, 1);
                $mttrValues[] = round($mttr, 1);
            }

            // Format chart data
            $this->availabilityData = [
                'labels' => $chartLabels,
                'datasets' => [
                    [
                        'label' => 'Availability (%)',
                        'data' => $availabilityValues,
                        'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                        'borderColor' => 'rgba(54, 162, 235, 1)',
                        'borderWidth' => 1
                    ]
                ]
            ];

            $this->mtbfData = [
                'labels' => $chartLabels,
                'datasets' => [
                    [
                        'label' => 'MTBF (Hours)',
                        'data' => $mtbfValues,
                        'backgroundColor' => 'rgba(75, 192, 192, 0.5)',
                        'borderColor' => 'rgba(75, 192, 192, 1)',
                        'borderWidth' => 1
                    ]
                ]
            ];

            $this->mttrData = [
                'labels' => $chartLabels,
                'datasets' => [
                    [
                        'label' => 'MTTR (Hours)',
                        'data' => $mttrValues,
                        'backgroundColor' => 'rgba(255, 99, 132, 0.5)',
                        'borderColor' => 'rgba(255, 99, 132, 1)',
                        'borderWidth' => 1
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error loading chart data: ' . $e->getMessage());
            $this->setDefaultChartData();
        }
    }

    protected function setDefaultChartData()
    {
        $defaultLabels = ['No Data Available'];
        $defaultValues = [0];

        $this->availabilityData = [
            'labels' => $defaultLabels,
            'datasets' => [
                [
                    'label' => 'Availability (%)',
                    'data' => $defaultValues,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1
                ]
            ]
        ];

        $this->mtbfData = [
            'labels' => $defaultLabels,
            'datasets' => [
                [
                    'label' => 'MTBF (Hours)',
                    'data' => $defaultValues,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.5)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 1
                ]
            ]
        ];

        $this->mttrData = [
            'labels' => $defaultLabels,
            'datasets' => [
                [
                    'label' => 'MTTR (Hours)',
                    'data' => $defaultValues,
                    'backgroundColor' => 'rgba(255, 99, 132, 0.5)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'borderWidth' => 1
                ]
            ]
        ];
    }

    public function render()
    {
        try {
            // Parse date strings to Carbon instances for calculations
            $startDate = Carbon::parse($this->startDate);
            $endDate = Carbon::parse($this->endDate)->endOfDay();

            // Filter equipment by search term and area/line if selected
            $query = MaintenanceEquipment::query()
                ->when($this->selectedArea !== 'all', function ($query) {
                    return $query->where('area_id', $this->selectedArea);
                })
                ->when($this->selectedLine !== 'all', function ($query) {
                    return $query->where('line_id', $this->selectedLine);
                })
                ->when($this->searchTerm, function ($query) {
                    return $query->where(function($q) {
                        $q->where('name', 'like', '%' . $this->searchTerm . '%')
                          ->orWhere('serial_number', 'like', '%' . $this->searchTerm . '%');
                    });
                });

            // Get the total equipment count
            $totalCount = $query->count();

            // Array to hold equipment metrics
            $equipmentMetrics = [];

            // Calculate total period in hours
            $totalPeriodHours = $startDate->diffInHours($endDate);

            // Calculate metrics for each equipment
            foreach ($query->get() as $equipment) {
                // Count failures in date range
                $failures = MaintenanceCorrective::where('equipment_id', $equipment->id)
                    ->whereBetween('start_time', [$startDate, $endDate])
                    ->get();

                $failureCount = $failures->count();

                // Get total downtime in hours
                $totalDowntime = $failures->sum('downtime_length');

                // Calculate availability as percentage of uptime
                $availability = $totalPeriodHours > 0
                    ? (($totalPeriodHours - $totalDowntime) / $totalPeriodHours) * 100
                    : 100;

                // Calculate MTBF (Mean Time Between Failures)
                $mtbf = $failureCount > 0
                    ? ($totalPeriodHours - $totalDowntime) / $failureCount
                    : $totalPeriodHours; // If no failures, MTBF is the entire period

                // Calculate MTTR (Mean Time To Repair)
                $mttr = $failureCount > 0
                    ? $totalDowntime / $failureCount
                    : 0; // If no failures, MTTR is 0

                $equipmentMetrics[] = [
                    'id' => $equipment->id,
                    'name' => $equipment->name,
                    'serial_number' => $equipment->serial_number,
                    'area' => $equipment->department ? $equipment->department->name : 'N/A',
                    'line' => $equipment->line ? $equipment->line->name : 'N/A',
                    'availability' => round($availability, 2),
                    'mtbf' => round($mtbf, 1),
                    'mttr' => round($mttr, 1),
                    'failure_count' => $failureCount,
                    'downtime' => round($totalDowntime, 1)
                ];
            }

            // Convert to collection for sorting
            $equipmentData = collect($equipmentMetrics);

            // Sort data
            if ($this->sortField) {
                $equipmentData = $equipmentData->sortBy([
                    [$this->sortField, $this->sortDirection]
                ]);
            }

            // Calculate overall averages
            $averageAvailability = $equipmentData->avg('availability');
            $averageMtbf = $equipmentData->avg('mtbf');
            $averageMttr = $equipmentData->avg('mttr');

            // Get all areas and lines for filters - directly from their models
            $areas = MaintenanceArea::orderBy('name')
                ->pluck('name', 'id')
                ->toArray();

            $lines = MaintenanceLine::orderBy('name')
                ->pluck('name', 'id')
                ->toArray();

            return view('livewire.reports.equipment-availability', [
                'equipmentData' => $equipmentData,
                'totalCount' => $totalCount,
                'averageAvailability' => $averageAvailability,
                'averageMtbf' => $averageMtbf,
                'averageMttr' => $averageMttr,
                'areas' => $areas,
                'lines' => $lines,
            ]);
        } catch (\Exception $e) {
            Log::error('Error rendering equipment availability: ' . $e->getMessage());

            // Return view with default values if calculation fails
            return view('livewire.reports.equipment-availability', [
                'equipmentData' => collect([]),
                'totalCount' => 0,
                'averageAvailability' => 0,
                'averageMtbf' => 0,
                'averageMttr' => 0,
                'areas' => [],
                'lines' => [],
            ]);
        }
    }
}
