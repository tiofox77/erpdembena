<?php

namespace App\Livewire\History;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Technician;
use App\Models\MaintenancePlan;
use App\Models\MaintenanceEquipment;
use App\Models\MaintenanceNote;
use App\Models\MaintenanceArea;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TeamPerformance extends Component
{
    use WithPagination;

    // Filters
    public $dateRange = 'last-month';
    public $startDate;
    public $endDate;
    public $userId;
    public $areaId;
    public $taskType;
    public $searchQuery = '';
    public $sortField = 'completed_tasks';
    public $sortDirection = 'desc';

    // Data collections
    public $technicians = [];
    public $areas = [];
    public $taskTypes = [
        'all' => 'All Task Types',
        'preventive' => 'Preventive Maintenance',
        'corrective' => 'Corrective Maintenance',
        'predictive' => 'Predictive Maintenance',
        'inspection' => 'Inspection'
    ];

    // Summary metrics
    public $totalCompletedTasks = 0;
    public $taskCompletionRate = 0;
    public $avgTaskDuration = 0;
    public $topPerformer = null;
    public $improvementAreas = [];

    protected $queryString = [
        'dateRange' => ['except' => 'last-month'],
        'userId' => ['except' => ''],
        'areaId' => ['except' => ''],
        'taskType' => ['except' => ''],
        'searchQuery' => ['except' => ''],
        'sortField' => ['except' => 'completed_tasks'],
        'sortDirection' => ['except' => 'desc']
    ];

    public function mount()
    {
        $this->setDateRange($this->dateRange);
        $this->loadTechniciansList();
        $this->loadAreasList();
        $this->loadPerformanceData();
    }

    public function setDateRange($range)
    {
        $this->dateRange = $range;

        switch ($range) {
            case 'last-week':
                $this->startDate = Carbon::now()->subWeek()->startOfWeek()->format('Y-m-d');
                $this->endDate = Carbon::now()->format('Y-m-d');
                break;
            case 'last-month':
                $this->startDate = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
                $this->endDate = Carbon::now()->format('Y-m-d');
                break;
            case 'last-quarter':
                $this->startDate = Carbon::now()->subMonths(3)->startOfMonth()->format('Y-m-d');
                $this->endDate = Carbon::now()->format('Y-m-d');
                break;
            case 'last-year':
                $this->startDate = Carbon::now()->subYear()->startOfYear()->format('Y-m-d');
                $this->endDate = Carbon::now()->format('Y-m-d');
                break;
            case 'custom':
                // Keep existing custom dates if already set
                if (!$this->startDate) {
                    $this->startDate = Carbon::now()->subMonth()->format('Y-m-d');
                }
                if (!$this->endDate) {
                    $this->endDate = Carbon::now()->format('Y-m-d');
                }
                break;
        }

        $this->resetPage();
    }

    public function updatedDateRange($value)
    {
        $this->setDateRange($value);
        $this->loadPerformanceData();
    }

    public function updatedStartDate($value)
    {
        $this->resetPage();
        $this->loadPerformanceData();
    }

    public function updatedEndDate($value)
    {
        $this->resetPage();
        $this->loadPerformanceData();
    }

    public function updatedUserId($value)
    {
        $this->resetPage();
        $this->loadPerformanceData();
    }

    public function updatedAreaId($value)
    {
        $this->resetPage();
        $this->loadPerformanceData();
    }

    public function updatedTaskType($value)
    {
        $this->resetPage();
        $this->loadPerformanceData();
    }

    public function updatedSearchQuery($value)
    {
        $this->resetPage();
        $this->loadPerformanceData();
    }

    public function sort($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function loadTechniciansList()
    {
        try {
            // Carregamos os técnicos diretamente do modelo Technician
            $this->technicians = Technician::with(['area', 'line'])
                ->orderBy('name')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error loading technicians list: ' . $e->getMessage());
            $this->technicians = [];
        }
    }

    public function loadAreasList()
    {
        try {
            // Obter diretamente da tabela de áreas
            $this->areas = MaintenanceArea::select('id', 'name')
                ->orderBy('name')
                ->get()
                ->pluck('name', 'id')
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error loading areas list: ' . $e->getMessage());
            $this->areas = [];
        }
    }

    public function loadPerformanceData()
    {
        try {
            // Definir datas para análise
            $startDateTime = Carbon::parse($this->startDate)->startOfDay();
            $endDateTime = Carbon::parse($this->endDate)->endOfDay();
            
            // Get technicians performance data with metrics
            $technicianPerformance = $this->getUserPerformanceProperty();
            
            // Calculate overall metrics
            $this->calculateSummaryMetrics($technicianPerformance);
            
            // Identify improvement areas
            $this->identifyImprovementAreas($startDateTime, $endDateTime);
        } catch (\Exception $e) {
            Log::error('Error loading performance data: ' . $e->getMessage());
        }
    }

    /**
     * Calculate summary metrics from technician data
     * 
     * @param \Illuminate\Pagination\LengthAwarePaginator $technicians
     * @return void
     */
    protected function calculateSummaryMetrics($technicians)
    {
        try {
            $totalPlans = 0;
            $completedPlans = 0;
            $totalDuration = 0;
            $durationCount = 0;
            $topPerformerScore = 0;
            $topPerformer = null;

            // Process each technician in the collection
            foreach ($technicians->items() as $technician) {
                // Add to totals
                $totalPlans += $technician->total_plans ?? 0;
                $completedPlans += $technician->completed_plans ?? 0;
                
                // Calculate average duration
                if (isset($technician->avg_duration) && $technician->avg_duration > 0) {
                    $totalDuration += $technician->avg_duration * ($technician->completed_plans ?? 0);
                    $durationCount += $technician->completed_plans ?? 0;
                }
                
                // Find top performer
                if (($technician->completed_plans ?? 0) > $topPerformerScore) {
                    $topPerformerScore = $technician->completed_plans ?? 0;
                    $topPerformer = $technician;
                }
            }
            
            // Set summary metrics
            $this->totalCompletedTasks = $completedPlans;
            $this->taskCompletionRate = $totalPlans > 0 ? round(($completedPlans / $totalPlans) * 100, 1) : 0;
            $this->avgTaskDuration = $durationCount > 0 ? round($totalDuration / $durationCount, 1) : 0;
            $this->topPerformer = $topPerformer;
            
        } catch (\Exception $e) {
            Log::error('Error calculating summary metrics: ' . $e->getMessage());
            $this->totalCompletedTasks = 0;
            $this->taskCompletionRate = 0;
            $this->avgTaskDuration = 0;
            $this->topPerformer = null;
        }
    }

    protected function identifyImprovementAreas($startDateTime, $endDateTime)
    {
        try {
            $this->improvementAreas = [];

            // Identify areas with low completion rates
            $areaCompletionRates = MaintenancePlan::join('maintenance_notes', 'maintenance_plans.id', '=', 'maintenance_notes.maintenance_plan_id')
                ->whereBetween(DB::raw('COALESCE(maintenance_plans.scheduled_date, maintenance_plans.scheduled_date)'), [$startDateTime, $endDateTime])
                ->whereNotNull('maintenance_plans.area_id')
                ->select('maintenance_plans.area_id')
                ->selectRaw('COUNT(*) as total_plans')
                ->selectRaw('SUM(CASE WHEN maintenance_notes.status = "completed" THEN 1 ELSE 0 END) as completed_plans')
                ->groupBy('maintenance_plans.area_id')
                ->having('total_plans', '>', 5) // Only consider areas with enough plans
                ->get();

            foreach ($areaCompletionRates as $areaRate) {
                $completionRate = ($areaRate->completed_plans / $areaRate->total_plans) * 100;

                // Buscar o nome da área, já que agora temos apenas area_id
                $areaName = 'Unknown Area';
                $area = \App\Models\MaintenanceArea::find($areaRate->area_id);
                if ($area) {
                    $areaName = $area->name;
                }

                if ($completionRate < 70) {
                    $this->improvementAreas[] = [
                        'type' => 'area',
                        'name' => $areaName,
                        'metric' => 'completion_rate',
                        'value' => round($completionRate, 1),
                        'recommendation' => 'Review resource allocation for this area'
                    ];
                }
            }

            // Identificar tipos de planos com durações longas
            $planTypeDurations = collect(); // Iniciar com coleção vazia
            
            // Verificar se a tabela maintenance_correctives existe
            if (Schema::hasTable('maintenance_correctives')) {
                try {
                    // Usar a tabela maintenance_correctives se existir
                    // A tabela maintenance_correctives não tem uma coluna maintenance_plan_id
                    // Vamos usar uma abordagem alternativa para obter informações de desempenho
                    
                    // Simplificando a abordagem para evitar colunas inexistentes
                    // Agrupamos apenas por equipment_id para evitar problemas de estrutura
                    $equipmentDurations = DB::table('maintenance_correctives')
                        ->join('maintenance_equipment', 'maintenance_correctives.equipment_id', '=', 'maintenance_equipment.id')
                        ->whereBetween('maintenance_correctives.created_at', [$startDateTime, $endDateTime])
                        ->whereNotNull('maintenance_correctives.end_time')
                        ->select('maintenance_equipment.id', 'maintenance_equipment.name')
                        ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, maintenance_correctives.created_at, maintenance_correctives.end_time)) as avg_duration')
                        ->selectRaw('COUNT(*) as maintenance_count')
                        ->groupBy('maintenance_equipment.id', 'maintenance_equipment.name')
                        ->having('maintenance_count', '>', 2) // Reduzindo ainda mais o limite
                        ->get();
                    
                    $planTypeDurations = collect();
                    
                    // 2. Converter os resultados para um formato compatível com o código existente
                    foreach ($equipmentDurations as $equipment) {
                        // Usar diretamente o nome do equipamento como categoria
                        $equipmentName = $equipment->name ?: 'Equipamento ' . $equipment->id;
                        $categoryName = $equipmentName;
                        
                        // Adicionar à coleção no formato esperado
                        $typeData = (object)[
                            'type' => $categoryName,
                            'avg_duration' => $equipment->avg_duration,
                            'plan_count' => $equipment->maintenance_count
                        ];
                        
                        $planTypeDurations->push($typeData);
                    }
                } catch (\Exception $e) {
                    Log::warning('Erro ao consultar duração de planos por tipo: ' . $e->getMessage());
                }
            } else {
                // Alternativa: usar os tempos de execução das notas de manutenção por tipo de plano
                try {
                    // Usar notas de manutenção como alternativa, se a tabela maintenance_correctives não existir
                    // e se houver um campo de duração nas notas
                    if (Schema::hasColumn('maintenance_notes', 'duration')) {
                        $planTypeDurations = DB::table('maintenance_notes')
                            ->join('maintenance_plans', 'maintenance_notes.maintenance_plan_id', '=', 'maintenance_plans.id')
                            ->where('maintenance_notes.status', 'completed')
                            ->whereBetween('maintenance_notes.created_at', [$startDateTime, $endDateTime])
                            ->select('maintenance_plans.type')
                            ->selectRaw('AVG(maintenance_notes.duration) as avg_duration')
                            ->selectRaw('COUNT(*) as plan_count')
                            ->groupBy('maintenance_plans.type')
                            ->having('plan_count', '>', 5)
                            ->get();
                    }
                } catch (\Exception $e) {
                    Log::warning('Erro ao usar alternativa para duração de planos por tipo: ' . $e->getMessage());
                }
            }
            
            // Processar os resultados (independente de qual fonte foi usada)
            foreach ($planTypeDurations as $planType) {
                $averageDurationHours = round($planType->avg_duration, 1);

                if ($averageDurationHours > 3) {
                    $this->improvementAreas[] = [
                        'type' => 'plan_type',
                        'name' => ucfirst($planType->type),
                        'metric' => 'avg_duration',
                        'value' => $averageDurationHours,
                        'recommendation' => 'Review procedure to optimize time'
                    ];
                }
            }

        } catch (\Exception $e) {
            Log::error('Error identifying improvement areas: ' . $e->getMessage());
            $this->improvementAreas = [];
        }
    }

    public function exportPerformanceData()
    {
        // Placeholder for export functionality
        $this->dispatchBrowserEvent('show-notification', [
            'type' => 'info',
            'message' => 'Export functionality will be implemented soon'
        ]);
    }

    public function getUserPerformanceProperty()
    {
        try {
            // Get date range for queries
            $startDateTime = Carbon::parse($this->startDate)->startOfDay();
            $endDateTime = Carbon::parse($this->endDate)->endOfDay();

            // Base query for technicians
            $query = Technician::with(['area', 'line'])->orderBy('name');

            // Aplicar filtros
            if ($this->userId) {
                $query->where('id', $this->userId);
            }

            if ($this->areaId) {
                $query->where('area_id', $this->areaId);
            }

            if ($this->searchQuery) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->searchQuery . '%')
                      ->orWhereHas('area', function($eq) {
                         $eq->where('name', 'like', '%' . $this->searchQuery . '%');
                      })
                      ->orWhereHas('line', function($eq) {
                         $eq->where('name', 'like', '%' . $this->searchQuery . '%');
                      });
                });
            }
            
            // Obter técnicos com suas métricas de desempenho
            $technicians = $query->get();
            
            // Calcular métricas para cada técnico
            foreach ($technicians as $technician) {
                // Contar total de planos atribuídos
                $technician->total_plans = MaintenancePlan::where('assigned_to', $technician->id)
                    ->where(function($q) use ($startDateTime, $endDateTime) {
                        $q->whereBetween('scheduled_date', [$startDateTime, $endDateTime])
                          ->orWhereHas('notes', function($nq) use ($startDateTime, $endDateTime) {
                              $nq->whereBetween('created_at', [$startDateTime, $endDateTime]);
                          });
                    })
                    ->count();
                
                // Contar planos concluídos
                $technician->completed_plans = MaintenancePlan::where('assigned_to', $technician->id)
                    ->whereHas('notes', function($q) use ($startDateTime, $endDateTime) {
                        $q->where('status', 'completed')
                          ->whereBetween('created_at', [$startDateTime, $endDateTime]);
                    })
                    ->count();
                
                // Contar planos em atraso
                $technician->overdue_plans = MaintenancePlan::where('assigned_to', $technician->id)
                    ->where('scheduled_date', '<', now())
                    ->whereDoesntHave('notes', function($q) {
                        $q->where('status', 'completed');
                    })
                    ->count();
                
                // Calcular taxa de conclusão
                $technician->completion_rate = $technician->total_plans > 0 
                    ? round(($technician->completed_plans / $technician->total_plans) * 100, 1)
                    : 0;
                
                // Verificar se a tabela maintenance_correctives existe antes de consultar
                $tableExists = Schema::hasTable('maintenance_correctives');
                
                // Definir um valor padrão
                $technician->avg_duration = 0;
                
                if ($tableExists) {
                    try {
                        // Calcular duração média usando a tabela maintenance_correctives
                        // Usamos a diferença entre created_at e resolved_at para calcular a duração
                        $correctives = DB::table('maintenance_correctives')
                            ->where('resolved_by', $technician->id)
                            ->whereBetween('created_at', [$startDateTime, $endDateTime])
                            ->whereNotNull('end_time')
                            ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, end_time)) as avg_hours'))
                            ->first();
                        
                        $technician->avg_duration = $correctives && $correctives->avg_hours ? round($correctives->avg_hours, 1) : 0;
                    } catch (\Exception $e) {
                        // Silenciosamente falha e mantém o valor padrão
                        Log::warning('Não foi possível obter a duração média para o técnico ' . $technician->name . ': ' . $e->getMessage());
                    }
                } else {
                    // Usar uma alternativa se a tabela não existir
                    // Podemos usar as notas de manutenção como uma alternativa
                    try {
                        $completedNotes = MaintenanceNote::whereHas('maintenancePlan', function($q) use ($technician, $startDateTime, $endDateTime) {
                            $q->where('assigned_to', $technician->id)
                              ->whereBetween('scheduled_date', [$startDateTime, $endDateTime]);
                        })->where('status', 'completed');
                        
                        // Se houver notas, usamos o valor médio de um campo de duração, se existir
                        if ($completedNotes->count() > 0 && Schema::hasColumn('maintenance_notes', 'duration')) {
                            $avgDuration = $completedNotes->avg('duration');
                            $technician->avg_duration = $avgDuration ? round($avgDuration, 1) : 0;
                        }
                    } catch (\Exception $e) {
                        // Silenciosamente falha e mantém o valor padrão
                        Log::warning('Não foi possível obter a duração alternativa para o técnico ' . $technician->name . ': ' . $e->getMessage());
                    }
                }
            }

            // Ordenar os técnicos pelo campo selecionado
            switch ($this->sortField) {
                case 'name':
                    $technicians = $this->sortDirection === 'asc'
                        ? $technicians->sortBy('name')
                        : $technicians->sortByDesc('name');
                    break;
                case 'completed_tasks':
                case 'completed_plans':
                    $technicians = $this->sortDirection === 'asc'
                        ? $technicians->sortBy('completed_plans')
                        : $technicians->sortByDesc('completed_plans');
                    break;
                case 'completion_rate':
                    $technicians = $this->sortDirection === 'asc'
                        ? $technicians->sortBy('completion_rate')
                        : $technicians->sortByDesc('completion_rate');
                    break;
                case 'avg_duration':
                    $technicians = $this->sortDirection === 'asc'
                        ? $technicians->sortBy('avg_duration')
                        : $technicians->sortByDesc('avg_duration');
                    break;
                case 'overdue_tasks':
                case 'overdue_plans':
                    $technicians = $this->sortDirection === 'asc'
                        ? $technicians->sortBy('overdue_plans')
                        : $technicians->sortByDesc('overdue_plans');
                    break;
                default:
                    $technicians = $technicians->sortByDesc('completed_plans');
            }

            // Implementar paginação manual para a coleção
            $page = request()->get('page', 1);
            $perPage = 10;
            
            $items = $technicians->all();
            $currentPageItems = array_slice($items, ($page - 1) * $perPage, $perPage);
            
            return new \Illuminate\Pagination\LengthAwarePaginator(
                collect($currentPageItems),
                count($items),
                $perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );

        } catch (\Exception $e) {
            Log::error('Error fetching technician performance: ' . $e->getMessage());
            
            return new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]),
                0,
                10,
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        }
    }

    public function render()
    {
        return view('livewire.history.team-performance', [
            'userPerformance' => $this->getUserPerformanceProperty()
        ]);
    }
}
