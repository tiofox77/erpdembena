<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\MaintenanceEquipment as Equipment;
use App\Models\MaintenanceArea as Area;
use App\Models\MaintenanceLine as Line;
use App\Models\MaintenanceCorrective as Corrective;
use App\Models\FailureMode;
use App\Models\FailureCause;
use App\Models\FailureModeCategory;
use App\Models\FailureCauseCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FailureAnalysis extends Component
{
    // Filters
    public $dateRange = 'month';
    public $startDate;
    public $endDate;
    public $selectedArea = 'all';
    public $selectedLine = 'all';
    public $selectedEquipment = 'all';
    public $sortField = 'date';
    public $sortDirection = 'desc';

    // Modal control
    public $showDetailModal = false;
    public $selectedFailure = null;

    // Data properties
    public $areas = [];
    public $lines = [];
    public $equipment = [];
    public $failureRecords = [];
    public $totalFailures = 0;
    public $topFailureCause = '';
    public $topFailureCauseCount = 0;
    public $mostFailingEquipment = '';
    public $mostFailingEquipmentCount = 0;
    public $averageDowntime = 0;
    public $identifiedPatterns = [];

    // Chart data
    public $failureCausesData = [];
    public $failuresByEquipmentData = [];
    public $failuresOverTimeData = [];
    public $failureImpactData = [];
    public $causeCategoriesData = [];
    public $modeCategoriesData = [];

    public function mount()
    {
        try {
            // Load areas, lines and equipment for filters
            $this->areas = Area::orderBy('name')->pluck('name', 'id')->toArray();
            $this->lines = Line::orderBy('name')->pluck('name', 'id')->toArray();
            $this->loadEquipmentOptions();

            // Set default date range
            $this->setDateRange($this->dateRange);

            // Load initial data
            $this->loadFailureData();
        } catch (\Exception $e) {
            Log::error('Error in FailureAnalysis mount: ' . $e->getMessage());
            $this->setEmptyState();
        }
    }

    public function updatedDateRange()
    {
        $this->setDateRange($this->dateRange);
        $this->loadFailureData();
    }

    public function updatedStartDate()
    {
        $this->loadFailureData();
    }

    public function updatedEndDate()
    {
        $this->loadFailureData();
    }

    public function updatedSelectedArea()
    {
        // Reset the line and equipment selection when area changes
        $this->selectedLine = 'all';
        $this->selectedEquipment = 'all';

        $this->loadEquipmentOptions();
        $this->loadFailureData();
    }

    public function updatedSelectedLine()
    {
        // Reset equipment selection when line changes
        $this->selectedEquipment = 'all';

        $this->loadEquipmentOptions();
        $this->loadFailureData();
    }

    public function updatedSelectedEquipment()
    {
        $this->loadFailureData();
    }

    public function setDateRange($range)
    {
        $now = Carbon::now();

        switch ($range) {
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
            case 'custom':
                // Keep the existing custom dates or set defaults if empty
                if (empty($this->startDate)) {
                    $this->startDate = $now->copy()->subDays(30)->format('Y-m-d');
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

        // Re-sort the failure records
        $this->failureRecords = collect($this->failureRecords)
            ->sortBy([$field => $this->sortDirection === 'asc' ? 'asc' : 'desc'])
            ->values()
            ->toArray();
    }

    public function loadEquipmentOptions()
    {
        try {
            $query = Equipment::query()->orderBy('name');

            if ($this->selectedArea !== 'all') {
                $query->where('area_id', $this->selectedArea);
            }

            if ($this->selectedLine !== 'all') {
                $query->where('line_id', $this->selectedLine);
            }

            $this->equipment = $query->pluck('name', 'id')->toArray();
        } catch (\Exception $e) {
            Log::error('Error loading equipment options: ' . $e->getMessage());
            $this->equipment = [];
        }
    }

    public function showFailureDetails($failureId)
    {
        try {
            $failure = Corrective::with([
                'equipment',
                'equipment.area',
                'equipment.line',
                'failureMode',
                'failureCause',
                'reporter',
                'resolver'
            ])->findOrFail($failureId);

            // Format data for the modal
            $this->selectedFailure = [
                'id' => $failure->id,
                'date' => Carbon::parse($failure->start_time)->format('Y-m-d H:i'),
                'equipment' => $failure->equipment->name ?? 'Unknown',
                'serial_number' => $failure->equipment->serial_number ?? '',
                'area' => $failure->equipment->area->name ?? 'Unknown',
                'line' => $failure->equipment->line->name ?? 'Unknown',
                'failure_mode' => $failure->failureMode ? $failure->failureMode->name : 'Unknown',
                'failure_cause' => $failure->failureCause ? $failure->failureCause->name : 'Unknown',
                'description' => $failure->description ?? '',
                'actions_taken' => $failure->actions_taken ?? '',
                'reported_by' => $failure->reporter ? $failure->reporter->name : 'Unknown',
                'resolved_by' => $failure->resolver ? $failure->resolver->name : 'Unknown',
                'status' => $failure->status ?? 'Unknown',
                'downtime' => $failure->downtime_length ?? '0:00:00',
                'downtime_hours' => $this->formatDowntimeHours($failure->downtime_length),
                'financial_impact' => $failure->financial_impact ?? 0,
                'production_loss' => $failure->production_loss_units ?? 0,
            ];

            $this->showDetailModal = true;
        } catch (\Exception $e) {
            Log::error('Error loading failure details: ' . $e->getMessage());
        }
    }

    public function closeModal()
    {
        $this->showDetailModal = false;
        $this->selectedFailure = null;
    }

    public function loadFailureData()
    {
        try {
            Log::info('FailureAnalysis: Loading failure data with filters', [
                'dateRange' => $this->dateRange,
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
                'selectedArea' => $this->selectedArea,
                'selectedLine' => $this->selectedLine,
                'selectedEquipment' => $this->selectedEquipment
            ]);

            // Build query based on filters
            $query = Corrective::query();

            // Eagerly load all necessary relationships
            $query->with([
                'equipment',
                'equipment.area',
                'equipment.line',
                'failureMode',
                'failureMode.category',
                'failureCause',
                'failureCause.category',
                'reporter',
                'resolver'
            ]);

            // Date range filter - ensure correct format and validate dates
            if (!empty($this->startDate) && !empty($this->endDate)) {
                $startDateTime = $this->startDate . " 00:00:00";
                $endDateTime = $this->endDate . " 23:59:59";
                $query->whereBetween('start_time', [$startDateTime, $endDateTime]);

                Log::info('FailureAnalysis: Date filter applied', [
                    'startDateTime' => $startDateTime,
                    'endDateTime' => $endDateTime
                ]);
            } else {
                Log::warning('FailureAnalysis: Missing date range, using default');
                // If dates are missing, set a reasonable default (last 30 days)
                $endDate = Carbon::now();
                $startDate = Carbon::now()->subDays(30);
                $query->whereBetween('start_time', [$startDate, $endDate]);
            }

            // Apply equipment filter
            if ($this->selectedEquipment !== 'all') {
                $query->where('equipment_id', $this->selectedEquipment);
                Log::info('FailureAnalysis: Equipment filter applied', ['equipment_id' => $this->selectedEquipment]);
            } elseif ($this->selectedArea !== 'all' || $this->selectedLine !== 'all') {
                // Apply area and line filters if equipment is not selected
                $query->whereHas('equipment', function($q) {
                    if ($this->selectedArea !== 'all') {
                        $q->where('area_id', $this->selectedArea);
                        Log::info('FailureAnalysis: Area filter applied', ['area_id' => $this->selectedArea]);
                    }

                    if ($this->selectedLine !== 'all') {
                        $q->where('line_id', $this->selectedLine);
                        Log::info('FailureAnalysis: Line filter applied', ['line_id' => $this->selectedLine]);
                    }
                });
            }

            // Get corrective maintenance records - make sure to apply no soft deletes
            $failures = $query->get();

            // Debug information
            Log::info('FailureAnalysis: Query returned ' . $failures->count() . ' records');

            // Reset all data properties if no failures found
            if ($failures->isEmpty()) {
                Log::info('FailureAnalysis: No records found, applying empty state');
                $this->setEmptyState();
                return;
            }

            // Process failure records for display
            $this->processFailureRecords($failures);

            // Generate chart data
            $this->calculateFailureCauses($failures);
            $this->calculateFailuresByEquipment($failures);
            $this->calculateFailuresOverTime($failures);
            $this->calculateFailureImpact($failures);
            $this->calculateCategoriesDistribution($failures);

            // Identify patterns in failures
            $this->identifyPatterns($failures);

        } catch (\Exception $e) {
            Log::error('Error loading failure analysis data: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            $this->setEmptyState();
        }
    }

    protected function processFailureRecords($failures)
    {
        try {
            // Process records for the data table
            $this->failureRecords = $failures->map(function($failure) {
                return [
                    'id' => $failure->id,
                    'date' => Carbon::parse($failure->start_time)->format('Y-m-d H:i'),
                    'equipment' => $failure->equipment ? $failure->equipment->name : 'Unknown',
                    'area' => $failure->equipment && $failure->equipment->area ? $failure->equipment->area->name : 'Unknown',
                    'root_cause' => $failure->failureCause ? $failure->failureCause->name : 'Unknown',
                    'failed_component' => $failure->failureMode ? $failure->failureMode->name : 'Unknown',
                    'downtime' => $this->formatDowntimeHours($failure->downtime_length)
                ];
            })->sortByDesc('date')->values()->toArray();

            // Calculate summary metrics
            $this->totalFailures = count($this->failureRecords);

            // Most common failure cause
            if ($failures->isNotEmpty()) {
                $causeCounts = $failures->groupBy(function($failure) {
                    return $failure->failureCause ? $failure->failureCause->name : 'Unknown';
                })->map->count();

                $this->topFailureCause = $causeCounts->sortDesc()->keys()->first() ?? 'None';
                $this->topFailureCauseCount = $causeCounts->sortDesc()->first() ?? 0;

                // Most failing equipment
                $equipmentCounts = $failures->groupBy(function($failure) {
                    return $failure->equipment ? $failure->equipment->name : 'Unknown';
                })->map->count();

                $this->mostFailingEquipment = $equipmentCounts->sortDesc()->keys()->first() ?? 'None';
                $this->mostFailingEquipmentCount = $equipmentCounts->sortDesc()->first() ?? 0;

                // Average downtime
                $totalDowntimeHours = 0;

                foreach ($failures as $failure) {
                    $totalDowntimeHours += $this->convertDowntimeToHours($failure->downtime_length);
                }

                $this->averageDowntime = $this->totalFailures > 0 ?
                    round($totalDowntimeHours / $this->totalFailures, 1) : 0;
            } else {
                $this->topFailureCause = 'None';
                $this->topFailureCauseCount = 0;
                $this->mostFailingEquipment = 'None';
                $this->mostFailingEquipmentCount = 0;
                $this->averageDowntime = 0;
            }
        } catch (\Exception $e) {
            Log::error('Error processing failure records: ' . $e->getMessage());
            $this->failureRecords = [];
            $this->totalFailures = 0;
            $this->topFailureCause = 'None';
            $this->topFailureCauseCount = 0;
            $this->mostFailingEquipment = 'None';
            $this->mostFailingEquipmentCount = 0;
            $this->averageDowntime = 0;
        }
    }

    protected function calculateFailureCauses($failures)
    {
        try {
            if ($failures->isEmpty()) {
                $this->failureCausesData = $this->getEmptyChartData('No failure causes found');
                return;
            }

            // Group by failure cause
            $causeGroups = $failures->groupBy(function($failure) {
                return $failure->failureCause ? $failure->failureCause->name : 'Unknown';
            });

            $labels = $causeGroups->keys()->toArray();
            $data = $causeGroups->map->count()->values()->toArray();

            // Generate colors
            $colors = $this->generateChartColors(count($labels));

            $this->failureCausesData = [
                'labels' => $labels,
                'datasets' => [
                    [
                        'data' => $data,
                        'backgroundColor' => $colors,
                        'borderColor' => array_map(function($color) {
                            return str_replace('0.7', '1', $color);
                        }, $colors),
                        'borderWidth' => 1
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating failure causes: ' . $e->getMessage());
            $this->failureCausesData = $this->getEmptyChartData('Error loading failure causes');
        }
    }

    protected function calculateFailuresByEquipment($failures)
    {
        try {
            if ($failures->isEmpty()) {
                $this->failuresByEquipmentData = $this->getEmptyChartData('No equipment failures found');
                return;
            }

            // Group by equipment and count failures
            $equipmentGroups = $failures->groupBy(function($failure) {
                return $failure->equipment ? $failure->equipment->name : 'Unknown';
            });

            // Sort by count and take top 10
            $equipmentCounts = $equipmentGroups->map->count()->sortDesc()->take(10);

            $labels = $equipmentCounts->keys()->toArray();
            $data = $equipmentCounts->values()->toArray();

            $this->failuresByEquipmentData = [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Number of Failures',
                        'data' => $data,
                        'backgroundColor' => 'rgba(255, 99, 132, 0.8)',
                        'borderColor' => 'rgba(255, 99, 132, 1)',
                        'borderWidth' => 1
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating failures by equipment: ' . $e->getMessage());
            $this->failuresByEquipmentData = $this->getEmptyChartData('Error loading equipment data');
        }
    }

    protected function calculateFailuresOverTime($failures)
    {
        try {
            if ($failures->isEmpty()) {
                $this->failuresOverTimeData = $this->getEmptyChartData('No failure trend data found');
                return;
            }

            // Determine date grouping based on range
            $startDate = Carbon::parse($this->startDate);
            $endDate = Carbon::parse($this->endDate);
            $diffInDays = $endDate->diffInDays($startDate);

            $grouping = 'daily';
            if ($diffInDays > 60) {
                $grouping = 'monthly';
            } elseif ($diffInDays > 14) {
                $grouping = 'weekly';
            }

            // Group failures by date
            $failuresByDate = [];

            foreach ($failures as $failure) {
                $date = Carbon::parse($failure->start_time);

                if ($grouping == 'daily') {
                    $key = $date->format('Y-m-d');
                    $label = $date->format('M d');
                } elseif ($grouping == 'weekly') {
                    $key = $date->format('Y-W');
                    $label = 'Week ' . $date->format('W');
                } else {
                    $key = $date->format('Y-m');
                    $label = $date->format('M Y');
                }

                if (!isset($failuresByDate[$key])) {
                    $failuresByDate[$key] = [
                        'label' => $label,
                        'count' => 0,
                        'by_cause' => []
                    ];
                }

                $failuresByDate[$key]['count']++;

                $cause = $failure->failureCause ? $failure->failureCause->name : 'Unknown';
                if (!isset($failuresByDate[$key]['by_cause'][$cause])) {
                    $failuresByDate[$key]['by_cause'][$cause] = 0;
                }
                $failuresByDate[$key]['by_cause'][$cause]++;
            }

            // Sort by date
            ksort($failuresByDate);

            // Get all unique causes
            $allCauses = [];
            foreach ($failuresByDate as $data) {
                foreach (array_keys($data['by_cause']) as $cause) {
                    if (!in_array($cause, $allCauses)) {
                        $allCauses[] = $cause;
                    }
                }
            }

            // If no failures, just return empty chart data
            if (empty($failuresByDate) || empty($allCauses)) {
                $this->failuresOverTimeData = $this->getEmptyChartData('No failure trend data found');
                return;
            }

            // Prepare chart data
            $labels = array_column($failuresByDate, 'label');

            $datasets = [];
            $colors = $this->generateChartColors(count($allCauses));

            foreach ($allCauses as $index => $cause) {
                $data = [];
                foreach ($failuresByDate as $dateData) {
                    $data[] = $dateData['by_cause'][$cause] ?? 0;
                }

                $datasets[] = [
                    'label' => $cause,
                    'data' => $data,
                    'backgroundColor' => $colors[$index],
                    'borderColor' => str_replace('0.7', '1', $colors[$index]),
                    'borderWidth' => 1
                ];
            }

            $this->failuresOverTimeData = [
                'labels' => $labels,
                'datasets' => $datasets
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating failures over time: ' . $e->getMessage());
            $this->failuresOverTimeData = $this->getEmptyChartData('Error loading trend data');
        }
    }

    protected function calculateFailureImpact($failures)
    {
        try {
            if ($failures->isEmpty()) {
                $this->failureImpactData = $this->getEmptyChartData('No impact data found');
                return;
            }

            // Calculate impact by equipment (downtime only)
            $equipmentImpact = [];

            foreach ($failures as $failure) {
                $equipmentName = $failure->equipment ? $failure->equipment->name : 'Unknown';

                if (!isset($equipmentImpact[$equipmentName])) {
                    $equipmentImpact[$equipmentName] = 0;
                }

                $equipmentImpact[$equipmentName] += $this->convertDowntimeToHours($failure->downtime_length);
            }

            if (empty($equipmentImpact)) {
                $this->failureImpactData = $this->getEmptyChartData('No downtime data available');
                return;
            }

            // Sort by downtime and take top 10
            arsort($equipmentImpact);
            $equipmentImpact = array_slice($equipmentImpact, 0, 10, true);

            $labels = array_keys($equipmentImpact);
            $downtimeData = array_values($equipmentImpact);

            $this->failureImpactData = [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Downtime (Hours)',
                        'data' => $downtimeData,
                        'backgroundColor' => 'rgba(54, 162, 235, 0.7)',
                        'borderColor' => 'rgba(54, 162, 235, 1)',
                        'borderWidth' => 1
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating failure impact: ' . $e->getMessage());
            $this->failureImpactData = $this->getEmptyChartData('Error loading impact data');
        }
    }

    protected function calculateCategoriesDistribution($failures)
    {
        try {
            if ($failures->isEmpty()) {
                $this->causeCategoriesData = $this->getEmptyChartData('No cause categories found');
                $this->modeCategoriesData = $this->getEmptyChartData('No mode categories found');
                return;
            }

            // Calculate cause categories distribution
            $causeCategoryGroups = $failures->groupBy(function($failure) {
                if ($failure->failureCause && $failure->failureCause->category) {
                    return $failure->failureCause->category->name;
                }
                return 'Unknown';
            });

            $causeLabels = $causeCategoryGroups->keys()->toArray();
            $causeData = $causeCategoryGroups->map->count()->values()->toArray();
            $causeColors = $this->generateChartColors(count($causeLabels));

            $this->causeCategoriesData = [
                'labels' => $causeLabels,
                'datasets' => [
                    [
                        'data' => $causeData,
                        'backgroundColor' => $causeColors,
                        'borderColor' => array_map(function($color) {
                            return str_replace('0.7', '1', $color);
                        }, $causeColors),
                        'borderWidth' => 1
                    ]
                ]
            ];

            // Calculate mode categories distribution
            $modeCategoryGroups = $failures->groupBy(function($failure) {
                if ($failure->failureMode && $failure->failureMode->category) {
                    return $failure->failureMode->category->name;
                }
                return 'Unknown';
            });

            $modeLabels = $modeCategoryGroups->keys()->toArray();
            $modeData = $modeCategoryGroups->map->count()->values()->toArray();
            $modeColors = $this->generateChartColors(count($modeLabels));

            $this->modeCategoriesData = [
                'labels' => $modeLabels,
                'datasets' => [
                    [
                        'data' => $modeData,
                        'backgroundColor' => $modeColors,
                        'borderColor' => array_map(function($color) {
                            return str_replace('0.7', '1', $color);
                        }, $modeColors),
                        'borderWidth' => 1
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating categories distribution: ' . $e->getMessage());
            $this->causeCategoriesData = $this->getEmptyChartData('Error loading cause categories');
            $this->modeCategoriesData = $this->getEmptyChartData('Error loading mode categories');
        }
    }

    protected function identifyPatterns($failures)
    {
        try {
            // Always clear existing patterns first
            $this->identifiedPatterns = [];

            // If no failures to analyze, exit early
            if ($failures->isEmpty()) {
                Log::info('FailureAnalysis: No failures found for pattern identification');
                return;
            }

            // Validate the collection is not empty and log count
            Log::info('FailureAnalysis: Identifying patterns for ' . $failures->count() . ' failures');

            // 1. Recurring failures on same equipment
            $equipmentFailures = $failures->groupBy('equipment_id');
            foreach ($equipmentFailures as $equipmentId => $equipmentGroup) {
                if (count($equipmentGroup) >= 3) {
                    $equipment = $equipmentGroup->first()->equipment;
                    $equipmentName = $equipment ? $equipment->name : 'Unknown Equipment';

                    // Check for recurring failure modes
                    $failureModes = $equipmentGroup->groupBy('failure_mode_id');
                    foreach ($failureModes as $modeId => $modeGroup) {
                        if (count($modeGroup) >= 2) {
                            $mode = $modeGroup->first()->failureMode;
                            $modeName = $mode ? $mode->name : 'Unknown Mode';

                            $this->identifiedPatterns[] = [
                                'type' => 'Recurring Failure Mode',
                                'description' => $equipmentName . ' has experienced ' . count($modeGroup) . ' failures with mode "' . $modeName . '"',
                                'suggested_action' => 'Review maintenance plan and investigate potential root causes for this specific failure mode',
                                'severity' => 'High'
                            ];
                        }
                    }

                    // Check for recurring failure causes
                    $failureCauses = $equipmentGroup->groupBy('failure_cause_id');
                    foreach ($failureCauses as $causeId => $causeGroup) {
                        if (count($causeGroup) >= 2) {
                            $cause = $causeGroup->first()->failureCause;
                            $causeName = $cause ? $cause->name : 'Unknown Cause';

                            $this->identifiedPatterns[] = [
                                'type' => 'Recurring Failure Cause',
                                'description' => $equipmentName . ' has experienced ' . count($causeGroup) . ' failures with cause "' . $causeName . '"',
                                'suggested_action' => 'Investigate specific prevention measures for this failure cause',
                                'severity' => 'High'
                            ];
                        }
                    }
                }
            }

            // 2. Common failure causes across equipment
            $causeCounts = $failures->groupBy('failure_cause_id');
            foreach ($causeCounts as $causeId => $causeGroup) {
                if (count($causeGroup) >= 3) {
                    $cause = $causeGroup->first()->failureCause;
                    $causeName = $cause ? $cause->name : 'Unknown Cause';
                    $categoryName = ($cause && $cause->category) ? $cause->category->name : 'Unknown Category';

                    // Get affected equipment
                    $affectedEquipment = $causeGroup->groupBy('equipment_id')->count();

                    if ($affectedEquipment >= 2) {
                        $this->identifiedPatterns[] = [
                            'type' => 'Common Failure Cause',
                            'description' => 'Cause "' . $causeName . '" (Category: ' . $categoryName . ') is affecting ' . $affectedEquipment . ' different equipment with ' . count($causeGroup) . ' total failures',
                            'suggested_action' => 'Implement a system-wide solution to address this failure cause',
                            'severity' => 'Medium'
                        ];
                    }
                }
            }

            // 3. Seasonal or time-based patterns
            $monthlyFailures = $failures->groupBy(function($failure) {
                return Carbon::parse($failure->start_time)->format('m');
            });

            $maxMonthlyCount = $monthlyFailures->map->count()->max();
            $avgMonthlyCount = $failures->count() / max(1, $monthlyFailures->count());

            foreach ($monthlyFailures as $month => $monthGroup) {
                if (count($monthGroup) > ($avgMonthlyCount * 1.5) && count($monthGroup) >= 3) {
                    $monthName = Carbon::createFromFormat('m', $month)->format('F');

                    // Check for common causes in this month
                    $monthlyCauses = $monthGroup->groupBy('failure_cause_id')->map->count()->sortDesc()->take(2);
                    $topCauses = [];

                    foreach ($monthlyCauses as $causeId => $count) {
                        $cause = $failures->first(function($failure) use ($causeId) {
                            return $failure->failure_cause_id == $causeId && $failure->failureCause;
                        })->failureCause;

                        if ($cause) {
                            $topCauses[] = $cause->name . ' (' . $count . ')';
                        }
                    }

                    $topCauseText = !empty($topCauses) ? ' Top causes: ' . implode(', ', $topCauses) : '';

                    $this->identifiedPatterns[] = [
                        'type' => 'Seasonal Pattern',
                        'description' => 'Increased failures in ' . $monthName . ' (' . count($monthGroup) . ' failures, ' .
                            round((count($monthGroup) / count($failures)) * 100) . '% of total).' . $topCauseText,
                        'suggested_action' => 'Investigate environmental factors in ' . $monthName . ' and implement preventive measures',
                        'severity' => 'Medium'
                    ];
                }
            }

            // 4. Failure cascade (multiple failures close together)
            $failuresByDate = $failures->groupBy(function($failure) {
                return Carbon::parse($failure->start_time)->format('Y-m-d');
            });

            foreach ($failuresByDate as $date => $dateGroup) {
                if (count($dateGroup) >= 3) {
                    // Check for patterns in the cascade
                    $cascadeCauses = $dateGroup->groupBy('failure_cause_id');
                    $cascadeModes = $dateGroup->groupBy('failure_mode_id');

                    $commonCause = $cascadeCauses->count() == 1 ? 'with common cause' : 'with different causes';
                    $commonMode = $cascadeModes->count() == 1 ? 'showing the same failure mode' : 'showing various failure modes';

                    $this->identifiedPatterns[] = [
                        'type' => 'Failure Cascade',
                        'description' => count($dateGroup) . ' failures occurred on ' . Carbon::parse($date)->format('M d, Y') . ' ' . $commonCause . ' and ' . $commonMode,
                        'suggested_action' => 'Investigate possible common triggers or systemic issues that affected multiple equipment on this date',
                        'severity' => 'High'
                    ];
                }
            }

            // 5. Analyze failures by category
            $this->analyzeCauseCategories($failures);
            $this->analyzeModeCategories($failures);

            // Sort patterns by severity
            $severityOrder = ['High' => 0, 'Medium' => 1, 'Low' => 2];
            usort($this->identifiedPatterns, function($a, $b) use ($severityOrder) {
                return $severityOrder[$a['severity']] <=> $severityOrder[$b['severity']];
            });

        } catch (\Exception $e) {
            Log::error('Error identifying failure patterns: ' . $e->getMessage());
            $this->identifiedPatterns = [];
        }
    }

    protected function analyzeCauseCategories($failures)
    {
        try {
            // Group failures by cause category
            $categoryFailures = $failures->groupBy(function($failure) {
                if ($failure->failureCause && $failure->failureCause->category) {
                    return $failure->failureCause->category->name;
                }
                return 'Unknown Category';
            });

            // If more than 60% of failures belong to a single category, that's a pattern
            $totalFailures = $failures->count();
            foreach ($categoryFailures as $category => $categoryGroup) {
                $percentage = ($categoryGroup->count() / $totalFailures) * 100;

                if ($percentage >= 60 && $categoryGroup->count() >= 3) {
                    // Find the most common causes in this category
                    $commonCauses = $categoryGroup->groupBy(function($failure) {
                        return $failure->failureCause ? $failure->failureCause->name : 'Unknown';
                    })->map->count()->sortDesc()->take(3);

                    $causesList = [];
                    foreach ($commonCauses as $cause => $count) {
                        $causesList[] = $cause . ' (' . $count . ')';
                    }

                    $this->identifiedPatterns[] = [
                        'type' => 'Dominant Failure Category',
                        'description' => 'Category "' . $category . '" represents ' . round($percentage) . '% of all failures. Most common causes: ' . implode(', ', $causesList),
                        'suggested_action' => 'Focus improvement efforts on addressing this category of failures',
                        'severity' => 'Medium'
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Error analyzing cause categories: ' . $e->getMessage());
        }
    }

    protected function analyzeModeCategories($failures)
    {
        try {
            // Group failures by mode category
            $categoryFailures = $failures->groupBy(function($failure) {
                if ($failure->failureMode && $failure->failureMode->category) {
                    return $failure->failureMode->category->name;
                }
                return 'Unknown Category';
            });

            // If more than 60% of failures belong to a single category, that's a pattern
            $totalFailures = $failures->count();
            foreach ($categoryFailures as $category => $categoryGroup) {
                $percentage = ($categoryGroup->count() / $totalFailures) * 100;

                if ($percentage >= 60 && $categoryGroup->count() >= 3) {
                    // Find the most common modes in this category
                    $commonModes = $categoryGroup->groupBy(function($failure) {
                        return $failure->failureMode ? $failure->failureMode->name : 'Unknown';
                    })->map->count()->sortDesc()->take(3);

                    $modesList = [];
                    foreach ($commonModes as $mode => $count) {
                        $modesList[] = $mode . ' (' . $count . ')';
                    }

                    $this->identifiedPatterns[] = [
                        'type' => 'Dominant Failure Mode Category',
                        'description' => 'Failure mode category "' . $category . '" represents ' . round($percentage) . '% of all failures. Most common modes: ' . implode(', ', $modesList),
                        'suggested_action' => 'Review equipment design or operating procedures to address this category of failure modes',
                        'severity' => 'Medium'
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Error analyzing mode categories: ' . $e->getMessage());
        }
    }

    protected function getEmptyChartData($message = 'No data available')
    {
        return [
            'labels' => [$message],
            'datasets' => [
                [
                    'label' => 'No Data',
                    'data' => [0],
                    'backgroundColor' => 'rgba(201, 203, 207, 0.7)',
                    'borderColor' => 'rgba(201, 203, 207, 1)',
                    'borderWidth' => 1
                ]
            ]
        ];
    }

    protected function setEmptyState()
    {
        // Reset all data properties to empty/default values
        $this->failureRecords = [];
        $this->totalFailures = 0;
        $this->topFailureCause = 'None';
        $this->topFailureCauseCount = 0;
        $this->mostFailingEquipment = 'None';
        $this->mostFailingEquipmentCount = 0;
        $this->averageDowntime = 0;

        // CRITICAL: Ensure identified patterns are cleared
        $this->identifiedPatterns = [];

        // Reset chart data
        $this->failureCausesData = $this->getEmptyChartData('No failure causes found');
        $this->failuresByEquipmentData = $this->getEmptyChartData('No equipment failures found');
        $this->failuresOverTimeData = $this->getEmptyChartData('No failure trend data found');
        $this->failureImpactData = $this->getEmptyChartData('No impact data found');
        $this->causeCategoriesData = $this->getEmptyChartData('No cause categories found');
        $this->modeCategoriesData = $this->getEmptyChartData('No mode categories found');

        // Log empty state for debugging
        Log::info('FailureAnalysis: Empty state applied - no data found for current filters');
    }

    protected function generateChartColors($count)
    {
        $baseColors = [
            'rgba(255, 99, 132, 0.7)',    // Red
            'rgba(54, 162, 235, 0.7)',    // Blue
            'rgba(255, 206, 86, 0.7)',    // Yellow
            'rgba(75, 192, 192, 0.7)',    // Green
            'rgba(153, 102, 255, 0.7)',   // Purple
            'rgba(255, 159, 64, 0.7)',    // Orange
            'rgba(199, 199, 199, 0.7)',   // Gray
            'rgba(83, 102, 255, 0.7)',    // Indigo
            'rgba(255, 99, 255, 0.7)',    // Pink
            'rgba(0, 162, 150, 0.7)',     // Teal
        ];

        $colors = [];
        for ($i = 0; $i < $count; $i++) {
            $colors[] = $baseColors[$i % count($baseColors)];
        }

        return $colors;
    }

    protected function formatDowntimeHours($downtime)
    {
        if (!$downtime) return 0;

        // If already in hours (numeric)
        if (is_numeric($downtime)) {
            return round($downtime, 1);
        }

        // If in HH:MM:SS format
        if (is_string($downtime) && strpos($downtime, ':') !== false) {
            return $this->convertDowntimeToHours($downtime);
        }

        return 0;
    }

    protected function convertDowntimeToHours($downtime)
    {
        if (!$downtime) return 0;

        // If already in hours (numeric)
        if (is_numeric($downtime)) {
            return (float)$downtime;
        }

        // If in HH:MM:SS format
        if (is_string($downtime) && strpos($downtime, ':') !== false) {
            $parts = array_map('intval', explode(':', $downtime));
            $hours = $parts[0];
            $minutes = isset($parts[1]) ? $parts[1] : 0;
            $seconds = isset($parts[2]) ? $parts[2] : 0;

            return $hours + ($minutes / 60) + ($seconds / 3600);
        }

        return 0;
    }

    public function render()
    {
        return view('livewire.reports.failure-analysis', [
            'areas' => $this->areas,
            'lines' => $this->lines,
            'equipment' => $this->equipment,
            'failureRecords' => $this->failureRecords,
            'totalFailures' => $this->totalFailures,
            'topFailureCause' => $this->topFailureCause,
            'topFailureCauseCount' => $this->topFailureCauseCount,
            'mostFailingEquipment' => $this->mostFailingEquipment,
            'mostFailingEquipmentCount' => $this->mostFailingEquipmentCount,
            'averageDowntime' => $this->averageDowntime,
            'identifiedPatterns' => $this->identifiedPatterns,
            'failureCausesData' => $this->failureCausesData,
            'failuresByEquipmentData' => $this->failuresByEquipmentData,
            'failuresOverTimeData' => $this->failuresOverTimeData,
            'failureImpactData' => $this->failureImpactData,
            'causeCategoriesData' => $this->causeCategoriesData,
            'modeCategoriesData' => $this->modeCategoriesData
        ]);
    }
}