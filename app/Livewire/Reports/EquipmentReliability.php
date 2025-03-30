<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MaintenanceEquipment;
use App\Models\MaintenanceCorrective;
use App\Models\MaintenancePlan;
use App\Models\MaintenanceArea;
use App\Models\MaintenanceLine;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EquipmentReliability extends Component
{
    use WithPagination;

    public $dateRange = 'month';
    public $startDate;
    public $endDate;
    public $selectedArea = 'all';
    public $selectedLine = 'all';
    public $searchTerm = '';
    public $sortField = 'reliability';
    public $sortDirection = 'desc';

    // Chart data
    public $reliabilityTrendData = [];
    public $mtbfTrendData = [];
    public $failureRatesData = [];
    public $topFailureModesData = [];

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

            // Query base
            $equipmentQuery = MaintenanceEquipment::query()
                ->with(['department', 'line'])
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

            // Get equipment IDs for filtering corrective maintenance
            $equipmentIds = $equipmentQuery->pluck('id')->toArray();

            // Load reliability trend data (monthly)
            $reliabilityTrend = $this->getReliabilityTrendData($equipmentIds, $startDate, $endDate);

            // Load MTBF trend data (monthly)
            $mtbfTrend = $this->getMtbfTrendData($equipmentIds, $startDate, $endDate);

            // Load failure rates data by equipment
            $failureRates = $this->getFailureRatesData($equipmentIds, $startDate, $endDate);

            // Load top failure modes
            $topFailureModes = $this->getTopFailureModesData($equipmentIds, $startDate, $endDate);

            // Set chart data
            $this->reliabilityTrendData = $reliabilityTrend;
            $this->mtbfTrendData = $mtbfTrend;
            $this->failureRatesData = $failureRates;
            $this->topFailureModesData = $topFailureModes;
        } catch (\Exception $e) {
            Log::error('Error loading reliability data: ' . $e->getMessage());

            // Set default data in case of error
            $this->setDefaultChartData();
        }
    }

    protected function getReliabilityTrendData($equipmentIds, $startDate, $endDate)
    {
        try {
            // Check if we have equipment IDs
            if (empty($equipmentIds)) {
                return $this->getDefaultReliabilityTrend();
            }

            // Get months between start and end date
            $months = [];
            $current = $startDate->copy();
            while ($current <= $endDate) {
                $months[] = $current->format('Y-m');
                $current->addMonth();
            }

            if (empty($months)) {
                return $this->getDefaultReliabilityTrend();
            }

            // Calcular dados de confiabilidade por mês
            $data = [];
            $labels = [];
            $plannedDowntime = [];
            $unplannedDowntime = [];

            foreach ($months as $month) {
                $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
                $monthEnd = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

                // Total horas disponíveis no mês para todos os equipamentos
                $totalHoursInMonth = $monthEnd->diffInHours($monthStart) * count($equipmentIds);

                // 1. Obter tempo de inatividade NÃO PLANEJADO (manutenção corretiva)
                $correctiveDowntime = MaintenanceCorrective::whereIn('equipment_id', $equipmentIds)
                    ->whereBetween('start_time', [$monthStart, $monthEnd])
                    ->sum('downtime_length');

                // 2. Obter tempo de inatividade PLANEJADO (manutenção preventiva)
                // Vamos considerar manutenções preventivas concluídas no mês
                $preventiveMaintenance = MaintenancePlan::whereIn('equipment_id', $equipmentIds)
                    ->where('status', 'completed')
                    ->whereBetween('scheduled_date', [$monthStart, $monthEnd])
                    ->get();

                // Estimativa do tempo de inatividade planejada (usando estimated_duration_minutes ou um valor padrão)
                $preventiveDowntime = 0;
                foreach ($preventiveMaintenance as $plan) {
                    // Converter minutos para horas
                    $preventiveDowntime += ($plan->estimated_duration_minutes ?? 60) / 60;
                }

                // Cálculo da confiabilidade:
                // Reliability = (Total Time - Unplanned Downtime) / Total Time * 100
                // Nota: Tempo de inatividade planejado NÃO afeta a confiabilidade
                $reliability = $totalHoursInMonth > 0
                    ? (($totalHoursInMonth - $correctiveDowntime) / $totalHoursInMonth) * 100
                    : 100;

                // Garantir que a confiabilidade esteja entre 0 e 100
                $reliability = max(0, min(100, $reliability));

                $data[] = round($reliability, 2);
                $labels[] = Carbon::createFromFormat('Y-m', $month)->format('M Y');

                // Armazenar dados de tempo de inatividade para potencial uso futuro
                $plannedDowntime[] = round($preventiveDowntime, 2);
                $unplannedDowntime[] = round($correctiveDowntime, 2);
            }

            return [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Reliability (%)',
                        'data' => $data,
                        'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                        'borderColor' => 'rgba(54, 162, 235, 1)',
                        'borderWidth' => 1,
                        'tension' => 0.4
                    ]
                    // Poderíamos adicionar datasets adicionais para mostrar downtime planejado vs não planejado
                    // se for necessário no futuro
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error in getReliabilityTrendData: ' . $e->getMessage());
            return $this->getDefaultReliabilityTrend();
        }
    }

    protected function getDefaultReliabilityTrend()
    {
        return [
            'labels' => ['No Data Available'],
            'datasets' => [
                [
                    'label' => 'Reliability (%)',
                    'data' => [0],
                    'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1,
                    'tension' => 0.4
                ]
            ]
        ];
    }

    protected function getMtbfTrendData($equipmentIds, $startDate, $endDate)
    {
        try {
            // Check if we have equipment IDs
            if (empty($equipmentIds)) {
                return $this->getDefaultMtbfTrend();
            }

            // Get months between start and end date
            $months = [];
            $current = $startDate->copy();
            while ($current <= $endDate) {
                $months[] = $current->format('Y-m');
                $current->addMonth();
            }

            if (empty($months)) {
                return $this->getDefaultMtbfTrend();
            }

            // Calcular MTBF e MTTR por mês usando dados reais
            $mtbfData = [];
            $mttrData = [];
            $labels = [];

            foreach ($months as $month) {
                $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
                $monthEnd = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

                // Total horas disponíveis no mês para todos os equipamentos
                $totalHoursInMonth = $monthEnd->diffInHours($monthStart) * count($equipmentIds);

                // Obter falhas no mês - manutenções corretivas
                $failureInstances = MaintenanceCorrective::whereIn('equipment_id', $equipmentIds)
                    ->whereBetween('start_time', [$monthStart, $monthEnd])
                    ->get();

                $failureCount = $failureInstances->count();

                // Calcular tempo total de reparo (correção)
                $totalRepairTime = $failureInstances->sum('downtime_length');

                // Calcular tempo total entre falhas (excluindo tempo de reparo)
                $operatingTime = $totalHoursInMonth - $totalRepairTime;

                // Calcular MTBF (Mean Time Between Failures)
                // MTBF = Tempo de Operação Total / Número de Falhas
                if ($failureCount > 0) {
                    $mtbf = $operatingTime / $failureCount;
                    $mttr = $totalRepairTime / $failureCount; // MTTR = Tempo Total de Reparo / Número de Falhas
                } else {
                    // Se não houver falhas, MTBF é todo o período e MTTR é zero
                    $mtbf = $operatingTime;
                    $mttr = 0;
                }

                // Ajustar valores muito altos para melhor visualização nos gráficos
                if ($mtbf > 1000) $mtbf = 1000; // Limitar MTBF para melhor visualização

                $mtbfData[] = round($mtbf, 2);
                $mttrData[] = round($mttr, 2);
                $labels[] = Carbon::createFromFormat('Y-m', $month)->format('M Y');
            }

            return [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'MTBF (Hours)',
                        'data' => $mtbfData,
                        'backgroundColor' => 'rgba(75, 192, 192, 0.5)',
                        'borderColor' => 'rgba(75, 192, 192, 1)',
                        'borderWidth' => 1,
                        'tension' => 0.1,
                        'yAxisID' => 'y'
                    ],
                    [
                        'label' => 'MTTR (Hours)',
                        'data' => $mttrData,
                        'backgroundColor' => 'rgba(255, 99, 132, 0.5)',
                        'borderColor' => 'rgba(255, 99, 132, 1)',
                        'borderWidth' => 1,
                        'tension' => 0.1,
                        'yAxisID' => 'y1'
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error in getMtbfTrendData: ' . $e->getMessage());
            return $this->getDefaultMtbfTrend();
        }
    }

    protected function getDefaultMtbfTrend()
    {
        return [
            'labels' => ['No Data Available'],
            'datasets' => [
                [
                    'label' => 'MTBF (Hours)',
                    'data' => [0],
                    'backgroundColor' => 'rgba(75, 192, 192, 0.5)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 1,
                    'tension' => 0.4,
                    'yAxisID' => 'y'
                ],
                [
                    'label' => 'MTTR (Hours)',
                    'data' => [0],
                    'backgroundColor' => 'rgba(255, 99, 132, 0.5)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'borderWidth' => 1,
                    'tension' => 0.4,
                    'yAxisID' => 'y1'
                ]
            ]
        ];
    }

    protected function getFailureRatesData($equipmentIds, $startDate, $endDate)
    {
        try {
            // Query failure counts by equipment
            $equipmentFailures = MaintenanceCorrective::whereIn('equipment_id', $equipmentIds)
                ->where('start_time', '>=', $startDate)
                ->where('start_time', '<=', $endDate)
                ->select('equipment_id', DB::raw('COUNT(*) as failure_count'))
                ->groupBy('equipment_id')
                ->get();

            if ($equipmentFailures->isEmpty()) {
                // Return default data if no failures
                return [
                    'labels' => ['No Failure Data Available'],
                    'datasets' => [
                        [
                            'label' => 'Number of Failures',
                            'data' => [0],
                            'backgroundColor' => 'rgba(255, 159, 64, 0.5)',
                            'borderColor' => 'rgba(255, 159, 64, 1)',
                            'borderWidth' => 1
                        ]
                    ]
                ];
            }

            // Get equipment names
            $equipmentNames = MaintenanceEquipment::whereIn('id', $equipmentIds)
                ->pluck('name', 'id')
                ->toArray();

            // Prepare data for chart
            $labels = [];
            $data = [];

            foreach ($equipmentFailures as $failure) {
                if (isset($equipmentNames[$failure->equipment_id])) {
                    $labels[] = $equipmentNames[$failure->equipment_id];
                    $data[] = $failure->failure_count;
                }
            }

            // Sort by failure count (highest first)
            array_multisort($data, SORT_DESC, $labels);

            // Limit to top 10
            $labels = array_slice($labels, 0, 10);
            $data = array_slice($data, 0, 10);

            return [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Number of Failures',
                        'data' => $data,
                        'backgroundColor' => 'rgba(255, 159, 64, 0.5)',
                        'borderColor' => 'rgba(255, 159, 64, 1)',
                        'borderWidth' => 1
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error in getFailureRatesData: ' . $e->getMessage());

            // Return default data
            return [
                'labels' => ['No Data Available'],
                'datasets' => [
                    [
                        'label' => 'Number of Failures',
                        'data' => [0],
                        'backgroundColor' => 'rgba(255, 159, 64, 0.5)',
                        'borderColor' => 'rgba(255, 159, 64, 1)',
                        'borderWidth' => 1
                    ]
                ]
            ];
        }
    }

    protected function getTopFailureModesData($equipmentIds, $startDate, $endDate)
    {
        try {
            // Query failure types from actual data in the database
            $failureTypes = MaintenanceCorrective::whereIn('equipment_id', $equipmentIds)
                ->where('start_time', '>=', $startDate)
                ->where('start_time', '<=', $endDate)
                ->whereNotNull('failure_mode_id')
                ->join('failure_modes', 'maintenance_correctives.failure_mode_id', '=', 'failure_modes.id')
                ->leftJoin('failure_mode_categories', 'failure_modes.category_id', '=', 'failure_mode_categories.id')
                ->select(
                    'failure_mode_categories.name as category_name',
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('failure_mode_categories.name')
                ->orderBy('count', 'desc')
                ->get();

            if ($failureTypes->isEmpty()) {
                // Return default data if no failure types found
                return [
                    'labels' => ['No Failure Type Data'],
                    'datasets' => [
                        [
                            'label' => 'Failure Occurrences',
                            'data' => [0],
                            'backgroundColor' => ['rgba(200, 200, 200, 0.5)'],
                            'borderColor' => ['rgba(200, 200, 200, 1)'],
                            'borderWidth' => 1
                        ]
                    ]
                ];
            }

            // Prepare data for chart
            $labels = [];
            $data = [];
            $backgroundColor = [];
            $borderColor = [];

            $colors = [
                ['rgba(255, 99, 132, 0.5)', 'rgba(255, 99, 132, 1)'],
                ['rgba(54, 162, 235, 0.5)', 'rgba(54, 162, 235, 1)'],
                ['rgba(255, 206, 86, 0.5)', 'rgba(255, 206, 86, 1)'],
                ['rgba(75, 192, 192, 0.5)', 'rgba(75, 192, 192, 1)'],
                ['rgba(153, 102, 255, 0.5)', 'rgba(153, 102, 255, 1)'],
                ['rgba(255, 159, 64, 0.5)', 'rgba(255, 159, 64, 1)'],
                ['rgba(199, 199, 199, 0.5)', 'rgba(199, 199, 199, 1)'],
                ['rgba(83, 102, 255, 0.5)', 'rgba(83, 102, 255, 1)'],
                ['rgba(255, 99, 255, 0.5)', 'rgba(255, 99, 255, 1)'],
                ['rgba(159, 159, 64, 0.5)', 'rgba(159, 159, 64, 1)'],
            ];

            foreach ($failureTypes as $i => $type) {
                $name = $type->category_name ?? "Uncategorized";
                $labels[] = $name;
                $data[] = $type->count;
                $backgroundColor[] = $colors[$i % count($colors)][0];
                $borderColor[] = $colors[$i % count($colors)][1];
            }

            return [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Failure Occurrences',
                        'data' => $data,
                        'backgroundColor' => $backgroundColor,
                        'borderColor' => $borderColor,
                        'borderWidth' => 1
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error in getTopFailureModesData: ' . $e->getMessage());

            // Return default data
            return [
                'labels' => ['No Data Available'],
                'datasets' => [
                    [
                        'label' => 'Failure Occurrences',
                        'data' => [0],
                        'backgroundColor' => ['rgba(200, 200, 200, 0.5)'],
                        'borderColor' => ['rgba(200, 200, 200, 1)'],
                        'borderWidth' => 1
                    ]
                ]
            ];
        }
    }

    protected function setDefaultChartData()
    {
        // Default reliability trend data
        $this->reliabilityTrendData = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'datasets' => [
                [
                    'label' => 'Reliability (%)',
                    'data' => [98, 97, 98.5, 96, 97.5, 99],
                    'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1,
                    'tension' => 0.4
                ]
            ]
        ];

        // Default MTBF trend data
        $this->mtbfTrendData = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'datasets' => [
                [
                    'label' => 'MTBF (Hours)',
                    'data' => [720, 680, 700, 650, 690, 710],
                    'backgroundColor' => 'rgba(75, 192, 192, 0.5)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 1,
                    'tension' => 0.4,
                    'yAxisID' => 'y'
                ],
                [
                    'label' => 'MTTR (Hours)',
                    'data' => [2.5, 3.1, 2.8, 3.5, 3.0, 2.7],
                    'backgroundColor' => 'rgba(255, 99, 132, 0.5)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'borderWidth' => 1,
                    'tension' => 0.4,
                    'yAxisID' => 'y1'
                ]
            ]
        ];

        // Default failure rates data
        $this->failureRatesData = [
            'labels' => ['Equipment 1', 'Equipment 2', 'Equipment 3', 'Equipment 4', 'Equipment 5'],
            'datasets' => [
                [
                    'label' => 'Number of Failures',
                    'data' => [5, 4, 3, 2, 1],
                    'backgroundColor' => 'rgba(255, 159, 64, 0.5)',
                    'borderColor' => 'rgba(255, 159, 64, 1)',
                    'borderWidth' => 1
                ]
            ]
        ];

        // Default top failure modes data
        $this->topFailureModesData = [
            'labels' => ['Mechanical Failure', 'Electrical Failure', 'Software Issue', 'Operator Error', 'Routine Wear'],
            'datasets' => [
                [
                    'label' => 'Failure Occurrences',
                    'data' => [8, 6, 4, 3, 2],
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.5)',
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(255, 206, 86, 0.5)',
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(153, 102, 255, 0.5)'
                    ],
                    'borderColor' => [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    'borderWidth' => 1
                ]
            ]
        ];
    }

    public function render()
    {
        $query = MaintenanceEquipment::query()
            ->with(['department', 'line'])
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

        // Get equipment IDs
        $equipmentIds = $query->pluck('id')->toArray();
        $totalEquipment = count($equipmentIds);

        try {
            // Date ranges for filtering
            $startDate = $this->startDate ? Carbon::parse($this->startDate) : Carbon::now()->subYear();
            $endDate = $this->endDate ? Carbon::parse($this->endDate) : Carbon::now();

            // Calculate total time period in hours
            $totalHours = $startDate->diffInHours($endDate);

            // Get reliability metrics from corrective maintenance data
            $equipmentMetrics = [];

            foreach ($query->get() as $equipment) {
                // Count failures in date range
                $failureCount = MaintenanceCorrective::where('equipment_id', $equipment->id)
                    ->where('start_time', '>=', $startDate)
                    ->where('start_time', '<=', $endDate)
                    ->count();

                // Get total downtime in hours
                $totalDowntime = MaintenanceCorrective::where('equipment_id', $equipment->id)
                    ->where('start_time', '>=', $startDate)
                    ->where('start_time', '<=', $endDate)
                    ->sum('downtime_length');

                // Get last failure date
                $lastFailure = MaintenanceCorrective::where('equipment_id', $equipment->id)
                    ->where('start_time', '<=', $endDate)
                    ->orderByDesc('start_time')
                    ->first();

                $lastFailureDate = $lastFailure ? $lastFailure->start_time->format('Y-m-d') : 'None';

                // Calculate metrics
                $reliability = $totalHours > 0 ? (($totalHours - $totalDowntime) / $totalHours) * 100 : 100;
                $mtbf = $failureCount > 0 ? ($totalHours - $totalDowntime) / $failureCount : $totalHours;
                $mttr = $failureCount > 0 ? $totalDowntime / $failureCount : 0;

                $equipmentMetrics[] = [
                    'id' => $equipment->id,
                    'name' => $equipment->name,
                    'serial_number' => $equipment->serial_number,
                    'area' => $equipment->department ? $equipment->department->name : 'N/A',
                    'line' => $equipment->line ? $equipment->line->name : 'N/A',
                    'reliability' => round($reliability, 2),
                    'mtbf' => round($mtbf, 1),
                    'mttr' => round($mttr, 1),
                    'failure_count' => $failureCount,
                    'downtime' => round($totalDowntime, 1),
                    'last_failure' => $lastFailureDate
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
            $averageReliability = $equipmentData->avg('reliability');
            $averageMtbf = $equipmentData->avg('mtbf');
            $averageMttr = $equipmentData->avg('mttr');
            $totalFailures = $equipmentData->sum('failure_count');
            $totalDowntime = $equipmentData->sum('downtime');

            // Calculate failure rate (failures per operational hour, as percentage)
            $totalOperationalHours = $totalHours * $totalEquipment;
            $failureRate = $totalOperationalHours > 0
                ? round(($totalFailures / $totalOperationalHours) * 100, 2)
                : 0;
        } catch (\Exception $e) {
            Log::error('Error calculating equipment metrics: ' . $e->getMessage());

            // Provide default values if calculation fails
            $equipmentData = collect([]);
            $averageReliability = 95;
            $averageMtbf = 500;
            $averageMttr = 3;
            $totalFailures = 0;
            $totalDowntime = 0;
            $failureRate = 0;
        }

        // Get all areas and lines for filters - directly from their models
        $areas = MaintenanceArea::orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $lines = MaintenanceLine::orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        return view('livewire.reports.equipment-reliability', [
            'equipmentData' => $equipmentData,
            'totalCount' => count($equipmentData),
            'averageReliability' => $averageReliability,
            'averageMtbf' => $averageMtbf,
            'averageMttr' => $averageMttr,
            'totalFailures' => $totalFailures,
            'totalDowntime' => $totalDowntime,
            'failureRate' => $failureRate,
            'areas' => $areas,
            'lines' => $lines,
            'totalEquipment' => $totalEquipment,
            'totalHours' => $totalHours,
        ]);
    }
}
