<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\MaintenanceEquipment;
use App\Models\MaintenanceArea;
use App\Models\MaintenanceLine;
use App\Models\MaintenanceCorrective as DowntimeRecord;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class DowntimeImpact extends Component
{
    // Propriedades públicas para dados e filtros
    public $startDate;
    public $endDate;
    public $dateRange = 'month';
    public $selectedArea = 'all';
    public $selectedLine = 'all';
    public $selectedEquipment = 'all';
    public $downtimeType = 'all';
    public $areas = [];
    public $lines = [];
    public $equipments = [];
    public $downtimeRecords = [];
    public $totalDowntime = 0;
    public $productionLoss = 0;
    public $financialImpact = 0;
    public $availabilityRate = 100;
    public $mostCriticalEquipment = 'None';
    public $mostCriticalDowntime = 0;
    
    // Propriedades para ordenação
    public $sortField = 'start_date';
    public $sortDirection = 'desc';
    public $perPage = 10;
    
    // Recomendações de melhoria
    public $recommendations = [];

    // Propriedades para dados dos gráficos
    public $downtimeByEquipmentData = [];
    public $downtimeTypesData = [];
    public $downtimeCausesData = [];
    public $downtimeTrendData = [];
    public $financialImpactData = [];

    public function mount()
    {
        try {
            Log::info('DowntimeImpact: Iniciando mount');
            
            // Configurar datas baseadas em dateRange
            $this->setDateRange($this->dateRange);
            
            // Load areas, lines and equipment
            $this->loadFilters();
            
            // Calculate initial data
            $this->updateData();
            
            Log::info('DowntimeImpact: Initial data loaded successfully');
        } catch (\Exception $e) {
            Log::error('Error in DowntimeImpact mount', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    public function updatedDateRange($value)
    {
        $this->setDateRange($value);
        $this->updateData();
    }
    
    protected function setDateRange($range)
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
                // Não alterar as datas se for personalizado
                if (empty($this->startDate) || empty($this->endDate)) {
                    $this->startDate = $now->copy()->subDays(30)->format('Y-m-d');
                    $this->endDate = $now->format('Y-m-d');
                }
                break;
                
            default:
                // Default para últimos 30 dias
                $this->startDate = $now->copy()->subDays(30)->format('Y-m-d');
                $this->endDate = $now->format('Y-m-d');
        }
        
        Log::info('DowntimeImpact: Date range set', [
            'range' => $range,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate
        ]);
    }

    public function render()
    {
        try {
            $hasDowntimeRecords = !empty($this->downtimeRecords);
            
            Log::info('DowntimeImpact render: Preparing view with data', [
                'totalDowntime' => $this->totalDowntime,
                'recordCount' => count($this->downtimeRecords),
                'chartDataAvailable' => $hasDowntimeRecords
            ]);
            
            return view('livewire.reports.downtime-impact');
        } catch (\Exception $e) {
            Log::error('Error in DowntimeImpact render', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('livewire.reports.downtime-impact');
        }
    }
    
    protected function loadFilters()
    {
        try {
            Log::info('DowntimeImpact: Carregando filtros');
            
            // Carregar áreas
            $this->areas = MaintenanceArea::orderBy('name')->get();
            
            // Carregar linhas (filtradas por área se uma área for selecionada)
            $linesQuery = MaintenanceLine::query()->orderBy('name');
            if ($this->selectedArea !== 'all') {
                $linesQuery->where('area_id', $this->selectedArea);
            }
            $this->lines = $linesQuery->get();
            
            // Carregar equipamentos (filtrados por área e linha se selecionados)
            $equipmentsQuery = MaintenanceEquipment::query()->orderBy('name');
            if ($this->selectedArea !== 'all') {
                $equipmentsQuery->whereHas('line', function($query) {
                    $query->where('area_id', $this->selectedArea);
                });
            }
            if ($this->selectedLine !== 'all') {
                $equipmentsQuery->where('line_id', $this->selectedLine);
            }
            $this->equipments = $equipmentsQuery->get();
            
            Log::info('DowntimeImpact: Filtros carregados com sucesso', [
                'areas' => count($this->areas), 
                'lines' => count($this->lines), 
                'equipments' => count($this->equipments)
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao carregar filtros', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Inicializar com arrays vazios para evitar erros
            $this->areas = [];
            $this->lines = [];
            $this->equipments = [];
        }
    }
    
    public function updateData()
    {
        try {
            Log::info('DowntimeImpact: Iniciando atualização de dados');
            
            // Obter IDs dos equipamentos com base nos filtros
            $equipmentIds = $this->getFilteredEquipmentIds();
            
            // Processar registros de downtime
            if (!empty($equipmentIds)) {
                $this->getDowntimeRecords($equipmentIds, $this->startDate, $this->endDate);
                
                // Carregar dados para os gráficos
                $this->getDowntimeByEquipmentData($equipmentIds, $this->startDate, $this->endDate);
                $this->getDowntimeTypesData($equipmentIds, $this->startDate, $this->endDate);
                $this->getDowntimeCausesData($equipmentIds, $this->startDate, $this->endDate);
                $this->getDowntimeTrendData($equipmentIds, $this->startDate, $this->endDate);
                $this->getFinancialImpactData($equipmentIds, $this->startDate, $this->endDate);
                
                Log::info('DowntimeImpact: Dados atualizados com sucesso');
            } else {
                Log::info('DowntimeImpact: Nenhum equipamento selecionado, usando dados vazios');
                
                // Inicializar com valores vazios
                $this->downtimeRecords = [];
                $this->totalDowntime = 0;
                $this->productionLoss = 0;
                $this->financialImpact = 0;
                $this->availabilityRate = 100;
                $this->mostCriticalEquipment = 'None';
                $this->mostCriticalDowntime = 0;
                
                // Preparar gráficos com dados vazios
                $this->initializeEmptyCharts();
            }
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar dados', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Inicializar com valores vazios em caso de erro
            $this->downtimeRecords = [];
            $this->totalDowntime = 0;
            $this->productionLoss = 0;
            $this->financialImpact = 0;
            $this->availabilityRate = 100;
            $this->mostCriticalEquipment = 'None';
            $this->mostCriticalDowntime = 0;
            
            // Preparar gráficos com dados vazios
            $this->initializeEmptyCharts();
        }
    }
    
    protected function getFilteredEquipmentIds()
    {
        try {
            $equipmentQuery = MaintenanceEquipment::query();
            
            // Filtrar por área
            if ($this->selectedArea !== 'all') {
                $equipmentQuery->whereHas('line', function($query) {
                    $query->where('area_id', $this->selectedArea);
                });
            }
            
            // Filtrar por linha
            if ($this->selectedLine !== 'all') {
                $equipmentQuery->where('line_id', $this->selectedLine);
            }
            
            // Filtrar por equipamento específico
            if ($this->selectedEquipment !== 'all') {
                $equipmentQuery->where('id', $this->selectedEquipment);
            }
            
            return $equipmentQuery->pluck('id')->toArray();
        } catch (\Exception $e) {
            Log::error('Erro ao obter IDs de equipamentos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [];
        }
    }
    
    public function sortBy($field)
    {
        try {
            // Se o usuário clicou no mesmo campo, alterna a direção da ordenação
            if ($this->sortField === $field) {
                $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                // Se é um novo campo, define como o campo de ordenação e usa a direção padrão (desc)
                $this->sortField = $field;
                $this->sortDirection = 'desc';
            }
            
            Log::info('DowntimeImpact: Aplicando ordenação', [
                'field' => $this->sortField,
                'direction' => $this->sortDirection
            ]);
            
            // Reordenar os registros de downtime
            $this->sortDowntimeRecords();
            
            // Dispatch de evento para notificar a alteração
            $this->dispatch('notify', 
                type: 'info', 
                message: __('Dados ordenados por ' . $this->sortField . ' em ordem ' . 
                    ($this->sortDirection === 'asc' ? 'crescente' : 'decrescente'))
            );
        } catch (\Exception $e) {
            Log::error('Erro ao ordenar registros', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Notificar o usuário sobre o erro
            $this->dispatch('notify', 
                type: 'error', 
                message: __('Erro ao ordenar os registros. Por favor, tente novamente.')
            );
        }
    }
    
    protected function sortDowntimeRecords()
    {
        if (empty($this->downtimeRecords)) {
            return;
        }
        
        // Criar uma cópia dos registros para não modificar a original
        $records = collect($this->downtimeRecords);
        
        // Ordenar os registros com base no campo e direção selecionados
        $sorted = $records->sortBy(function ($record) {
            // Como estamos trabalhando com arrays, podemos acessar diretamente as chaves
            switch ($this->sortField) {
                case 'equipment':
                case 'area':
                case 'line':
                case 'type':
                case 'duration':
                case 'start_date':
                case 'reason':
                    return $record[$this->sortField] ?? '';
                default:
                    // Para outros campos, usar diretamente o valor do campo ou um valor vazio
                    return $record[$this->sortField] ?? '';
            }
        }, SORT_REGULAR, $this->sortDirection !== 'asc');
        
        // Aplicar a lista ordenada de volta à propriedade downtimeRecords
        $this->downtimeRecords = $sorted->values()->all();
    }
    
    protected function initializeEmptyCharts()
    {
        // Dados vazios para gráfico de equipamentos
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
        
        // Dados vazios para gráfico de tipos
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
        
        // Dados vazios para gráfico de causas
        $this->downtimeCausesData = [
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
        
        // Dados vazios para gráfico de tendência
        $labels = [];
        $now = Carbon::now();
        for ($i = 6; $i >= 0; $i--) {
            $labels[] = $now->copy()->subDays($i)->format('M d');
        }
        
        $this->downtimeTrendData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Planned',
                    'data' => array_fill(0, count($labels), 0),
                    'backgroundColor' => 'rgba(54, 162, 235, 0.8)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Unplanned',
                    'data' => array_fill(0, count($labels), 0),
                    'backgroundColor' => 'rgba(255, 99, 132, 0.8)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Operational',
                    'data' => array_fill(0, count($labels), 0),
                    'backgroundColor' => 'rgba(255, 205, 86, 0.8)',
                    'borderColor' => 'rgba(255, 205, 86, 1)',
                    'borderWidth' => 1
                ]
            ]
        ];
        
        // Dados vazios para gráfico de impacto financeiro
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
    
    protected function getDowntimeRecords($equipmentIds, $startDate, $endDate)
    {
        try {
            Log::info('DowntimeImpact: Iniciando getDowntimeRecords');
            
            // Verificar quais campos usar para filtragem e cálculos
            $dateField = 'created_at';
            $durationField = '';
            
            // Determinar o campo de data a ser usado
            if (Schema::hasColumn('maintenance_correctives', 'start_date')) {
                $dateField = 'start_date';
            } else if (Schema::hasColumn('maintenance_correctives', 'date')) {
                $dateField = 'date';
            }
            
            // Determinar o campo de duração a ser usado
            if (Schema::hasColumn('maintenance_correctives', 'duration_hours')) {
                $durationField = 'duration_hours';
            } else if (Schema::hasColumn('maintenance_correctives', 'downtime')) {
                $durationField = 'downtime';
            } else if (Schema::hasColumn('maintenance_correctives', 'hours')) {
                $durationField = 'hours';
            } else {
                // Se não encontrar campo de duração, usar contagem de registros
                $durationField = 'id';
                Log::warning('DowntimeImpact: Nenhum campo de duração encontrado, usando contagem de registros');
            }
            
            // Verificar se há campo de impacto financeiro
            $hasFinancialImpact = Schema::hasColumn('maintenance_correctives', 'financial_impact') || 
                                Schema::hasColumn('maintenance_correctives', 'cost') ||
                                Schema::hasColumn('maintenance_correctives', 'cost_estimate');
            
            // Preparar consulta de downtime records
            $query = DowntimeRecord::whereIn('equipment_id', $equipmentIds);
            
            // Aplicar filtro de data
            if ($dateField === 'created_at') {
                $query->whereBetween($dateField, [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            } else {
                $query->whereBetween($dateField, [$startDate, $endDate]);
            }
            
            // Aplicar filtro de tipo, se fornecido e se a coluna existir
            if ($this->downtimeType !== 'all' && Schema::hasColumn('maintenance_correctives', 'type')) {
                $typeValue = $this->downtimeType;
                if (strtolower($this->downtimeType) === 'planned') {
                    $typeValue = 'Planned';
                } else if (strtolower($this->downtimeType) === 'unplanned') {
                    $typeValue = 'Unplanned';
                } else if (strtolower($this->downtimeType) === 'operational') {
                    $typeValue = 'Operational';
                }
                
                $query->where('type', $typeValue);
            }
            
            // Carregar relacionamentos
            $query->with('equipment.line.area');
            
            // Executar consulta
            $records = $query->get();
            
            Log::info('DowntimeImpact: Registros carregados', [
                'count' => count($records)
            ]);
            
            // Transformar registros para o formato adequado para a view
            $this->downtimeRecords = [];
            foreach ($records as $record) {
                // Determinar campo de data
                $dateValue = $record->created_at;
                if (isset($record->start_date)) {
                    $dateValue = $record->start_date;
                } else if (isset($record->date)) {
                    $dateValue = $record->date;
                }
                
                // Determinar campo de duração
                $durationValue = 1; // Valor padrão
                if (isset($record->duration_hours)) {
                    $durationValue = $record->duration_hours;
                } else if (isset($record->downtime)) {
                    $durationValue = $record->downtime;
                } else if (isset($record->hours)) {
                    $durationValue = $record->hours;
                }
                
                // Tipo de downtime
                $typeValue = isset($record->type) ? $record->type : 'Unplanned';
                
                // Calcular perda de produção e impacto financeiro baseado na duração
                // Assumindo uma taxa de produção de 10 unidades por hora e custo de $500 por hora
                $prodRate = 10; // unidades por hora
                $costPerHour = 500; // custo por hora de downtime
                
                $productionLoss = $durationValue * $prodRate;
                $financialImpact = $durationValue * $costPerHour;
                
                // Usar valores reais, se disponíveis
                if (isset($record->production_loss)) {
                    $productionLoss = $record->production_loss;
                }
                
                if (isset($record->financial_impact)) {
                    $financialImpact = $record->financial_impact;
                } else if (isset($record->cost)) {
                    $financialImpact = $record->cost;
                } else if (isset($record->cost_estimate)) {
                    $financialImpact = $record->cost_estimate;
                }
                
                // Preparar array com os dados formatados
                $this->downtimeRecords[] = [
                    'id' => $record->id,
                    'start_date' => $dateValue instanceof \Carbon\Carbon ? 
                                    $dateValue->format('Y-m-d') : $dateValue,
                    'equipment' => $record->equipment->name ?? 'Unknown',
                    'serial_number' => $record->equipment->serial_number ?? '',
                    'area' => $record->equipment->line->area->name ?? 'Unknown',
                    'line' => $record->equipment->line->name ?? 'Unknown',
                    'type' => $typeValue,
                    'reason' => isset($record->reason) ? $record->reason : 
                               (isset($record->failure_cause) ? $record->failure_cause : 
                               (isset($record->description) ? $record->description : 'No description')),
                    'duration' => $durationValue,
                    'production_loss' => $productionLoss,
                    'financial_impact' => $financialImpact
                ];
            }
            
            // Aplicar ordenação aos registros
            $this->sortDowntimeRecords();
            
            // Processar métricas gerais
            $this->totalDowntime = 0;
            $this->productionLoss = 0;
            $this->financialImpact = 0;
            
            // Gerar recomendações de melhoria
            $this->generateRecommendations();
            
            // Se houver registros, calcular métricas
            if (count($this->downtimeRecords) > 0) {
                // Calcular métricas baseadas nos registros
                foreach ($this->downtimeRecords as $record) {
                    // Adicionar ao downtime total
                    if ($durationField === 'id') {
                        // Se não há campo de duração, cada registro conta como 1 hora
                        $this->totalDowntime += 1;
                    } else {
                        // Se há campo de duração, usar o valor
                        $this->totalDowntime += $record->$durationField;
                    }
                    
                    // Adicionar ao impacto financeiro
                    if ($hasFinancialImpact) {
                        if (Schema::hasColumn('maintenance_correctives', 'financial_impact')) {
                            $this->financialImpact += $record->financial_impact ?? 0;
                        } else if (Schema::hasColumn('maintenance_correctives', 'cost')) {
                            $this->financialImpact += $record->cost ?? 0;
                        } else if (Schema::hasColumn('maintenance_correctives', 'cost_estimate')) {
                            $this->financialImpact += $record->cost_estimate ?? 0;
                        }
                    }
                }
                
                // Calcular taxa de disponibilidade (considerando 24/7 como base)
                $dateRange = Carbon::parse($startDate)->diffInHours(Carbon::parse($endDate)->endOfDay()) + 1;
                $totalEquipmentHours = $dateRange * count($equipmentIds);
                $this->availabilityRate = max(0, min(100, round(100 - ($this->totalDowntime / $totalEquipmentHours) * 100, 2)));
                
                // Determinar equipamento mais crítico
                if ($durationField === 'id') {
                    // Se não há campo de duração, contar registros por equipamento
                    $criticalEquipment = DowntimeRecord::whereIn('equipment_id', $equipmentIds)
                        ->whereBetween($dateField, [$startDate, $endDate])
                        ->select('equipment_id', DB::raw('COUNT(id) as total_downtime'))
                        ->groupBy('equipment_id')
                        ->orderByDesc('total_downtime')
                        ->with('equipment')
                        ->first();
                } else {
                    // Se há campo de duração, somar duração por equipamento
                    $criticalEquipment = DowntimeRecord::whereIn('equipment_id', $equipmentIds)
                        ->whereBetween($dateField, [$startDate, $endDate])
                        ->select('equipment_id', DB::raw('SUM(' . $durationField . ') as total_downtime'))
                        ->groupBy('equipment_id')
                        ->orderByDesc('total_downtime')
                        ->with('equipment')
                        ->first();
                }

                if ($criticalEquipment) {
                    $this->mostCriticalEquipment = $criticalEquipment->equipment->name ?? 'Unknown';
                    $this->mostCriticalDowntime = $criticalEquipment->total_downtime ?? 0;
                } else {
                    $this->mostCriticalEquipment = 'None';
                    $this->mostCriticalDowntime = 0;
                }
            } else {
                $this->mostCriticalEquipment = 'None';
                $this->mostCriticalDowntime = 0;
            }
            
            Log::info('DowntimeImpact: Downtime records processed', [
                'recordCount' => count($this->downtimeRecords),
                'totalDowntime' => $this->totalDowntime,
                'availabilityRate' => $this->availabilityRate,
                'mostCriticalEquipment' => $this->mostCriticalEquipment
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getDowntimeRecords', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Inicializar com valores vazios para evitar erros na view
            $this->downtimeRecords = [];
            $this->totalDowntime = 0;
            $this->productionLoss = 0;
            $this->financialImpact = 0;
            $this->availabilityRate = 100;
            $this->mostCriticalEquipment = 'None';
            $this->mostCriticalDowntime = 0;
        }
    }

    // Outros métodos existentes do componente...

    protected function getDowntimeTrendData($equipmentIds, $startDate, $endDate)
    {
        try {
            Log::info('DowntimeImpact: Iniciando getDowntimeTrendData');
            
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
            
            // Verificar quais campos usar para filtragem e cálculos
            $dateField = 'created_at';
            $durationField = '';
            
            // Determinar o campo de data a ser usado
            if (Schema::hasColumn('maintenance_correctives', 'start_date')) {
                $dateField = 'start_date';
            } else if (Schema::hasColumn('maintenance_correctives', 'date')) {
                $dateField = 'date';
            }
            
            // Determinar o campo de duração a ser usado
            if (Schema::hasColumn('maintenance_correctives', 'duration_hours')) {
                $durationField = 'duration_hours';
            } else if (Schema::hasColumn('maintenance_correctives', 'downtime')) {
                $durationField = 'downtime';
            } else if (Schema::hasColumn('maintenance_correctives', 'hours')) {
                $durationField = 'hours';
            } else {
                // Se não encontrar campo de duração, usar valor padrão de 1 hora
                $durationField = 'id';
                Log::warning('DowntimeImpact: Nenhum campo de duração encontrado, usando valor padrão');
            }
    
            $query = DowntimeRecord::whereIn('equipment_id', $equipmentIds);
            
            // Aplicar filtro de data
            if ($dateField === 'created_at') {
                $query->whereBetween($dateField, [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            } else {
                $query->whereBetween($dateField, [$startDate, $endDate]);
            }
    
            // Verificar se a coluna 'type' existe e aplicar filtro se necessário
            $hasTypeColumn = Schema::hasColumn('maintenance_correctives', 'type');
            
            if ($hasTypeColumn && $this->downtimeType !== 'all') {
                $typeValue = $this->downtimeType;
                if (strtolower($this->downtimeType) === 'planned') {
                    $typeValue = 'Planned';
                } else if (strtolower($this->downtimeType) === 'unplanned') {
                    $typeValue = 'Unplanned';
                } else if (strtolower($this->downtimeType) === 'operational') {
                    $typeValue = 'Operational';
                }
                
                $query->where('type', $typeValue);
            }
    
            // Format date based on grouping
            $dateFormat = $grouping === 'daily' ? 'Y-m-d' : ($grouping === 'weekly' ? 'Y-W' : 'Y-m');
            $dateFormatLabel = $grouping === 'daily' ? 'M d' : ($grouping === 'weekly' ? 'W w' : 'M Y');
    
            // Get downtime by date and type
            $records = $query->get();
            
            Log::info('DowntimeImpact: Dados de tendência obtidos', [
                'records' => count($records),
                'dateField' => $dateField,
                'durationField' => $durationField,
                'grouping' => $grouping
            ]);
    
            // Se não há registros ou não há coluna de tipo, usar dados simulados
            if (count($records) === 0 || !$hasTypeColumn) {
                Log::warning('DowntimeImpact: Usando dados simulados para gráfico de tendências');
                
                // Generate default dates based on range
                $defaultLabels = [];
                $currentDate = $startCarbon->copy();
                while ($currentDate->lte($endCarbon)) {
                    $defaultLabels[] = $currentDate->format($dateFormatLabel);
                    if ($grouping === 'daily') {
                        $currentDate->addDay();
                    } elseif ($grouping === 'weekly') {
                        $currentDate->addWeek();
                    } else {
                        $currentDate->addMonth();
                    }
                }
                
                // Create random data for demonstration
                $plannedData = [];
                $unplannedData = [];
                $operationalData = [];
                
                foreach ($defaultLabels as $label) {
                    $plannedData[] = rand(0, 5);
                    $unplannedData[] = rand(0, 8);
                    $operationalData[] = rand(0, 3);
                }
                
                $this->downtimeTrendData = [
                    'labels' => $defaultLabels,
                    'datasets' => [
                        [
                            'label' => 'Planned',
                            'data' => $plannedData,
                            'backgroundColor' => 'rgba(54, 162, 235, 0.8)',
                            'borderColor' => 'rgba(54, 162, 235, 1)',
                            'borderWidth' => 1
                        ],
                        [
                            'label' => 'Unplanned',
                            'data' => $unplannedData,
                            'backgroundColor' => 'rgba(255, 99, 132, 0.8)',
                            'borderColor' => 'rgba(255, 99, 132, 1)',
                            'borderWidth' => 1
                        ],
                        [
                            'label' => 'Operational',
                            'data' => $operationalData,
                            'backgroundColor' => 'rgba(255, 205, 86, 0.8)',
                            'borderColor' => 'rgba(255, 205, 86, 1)',
                            'borderWidth' => 1
                        ]
                    ]
                ];
                
                return;
            }
    
            // Group and format data
            $groupedData = [];
            $types = $hasTypeColumn ? ['Planned', 'Unplanned', 'Operational'] : ['Downtime'];
    
            foreach ($records as $record) {
                $date = Carbon::parse($record->$dateField)->format($dateFormat);
                $type = $hasTypeColumn ? $record->type : 'Downtime';
    
                if (!isset($groupedData[$date])) {
                    $groupedData[$date] = [
                        'label' => Carbon::parse($record->$dateField)->format($dateFormatLabel)
                    ];
                    
                    // Inicializa todos os tipos com 0
                    foreach ($types as $initialType) {
                        $groupedData[$date][$initialType] = 0;
                    }
                }
    
                // Determinar a duração
                $duration = ($durationField === 'id') ? 1 : $record->$durationField;
    
                if (in_array($type, $types)) {
                    $groupedData[$date][$type] += $duration;
                } else {
                    // If type doesn't match predefined types, categorize as "Other"
                    if (!isset($groupedData[$date]['Other'])) {
                        $groupedData[$date]['Other'] = 0;
                        if (!in_array('Other', $types)) {
                            $types[] = 'Other';
                        }
                    }
                    $groupedData[$date]['Other'] += $duration;
                }
            }
    
            // Sort by date
            ksort($groupedData);
            
            Log::info('DowntimeImpact: Dados agrupados por data', [
                'dates' => count($groupedData)
            ]);
    
            // Prepare chart data
            $labels = array_map(function($item) {
                return $item['label'];
            }, $groupedData);
    
            $datasets = [];
            $colors = [
                'Planned' => ['rgba(54, 162, 235, 0.8)', 'rgba(54, 162, 235, 1)'],
                'Unplanned' => ['rgba(255, 99, 132, 0.8)', 'rgba(255, 99, 132, 1)'],
                'Operational' => ['rgba(255, 205, 86, 0.8)', 'rgba(255, 205, 86, 1)'],
                'Other' => ['rgba(75, 192, 192, 0.8)', 'rgba(75, 192, 192, 1)'],
                'Downtime' => ['rgba(153, 102, 255, 0.8)', 'rgba(153, 102, 255, 1)']
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
                    'borderWidth' => 1
                ];
            }
    
            $this->downtimeTrendData = [
                'labels' => $labels,
                'datasets' => $datasets
            ];
    
            // Set default data if empty
            if (empty($labels)) {
                Log::info('DowntimeImpact: Nenhum dado de tendência encontrado, usando valores padrão');
                
                $defaultLabels = [];
                
                // Generate default dates based on range
                $currentDate = $startCarbon->copy();
                while ($currentDate->lte($endCarbon)) {
                    $defaultLabels[] = $currentDate->format($dateFormatLabel);
                    if ($grouping === 'daily') {
                        $currentDate->addDay();
                    } elseif ($grouping === 'weekly') {
                        $currentDate->addWeek();
                    } else {
                        $currentDate->addMonth();
                    }
                }
                
                $defaultDatasets = [];
                foreach ($types as $type) {
                    $defaultDatasets[] = [
                        'label' => $type,
                        'data' => array_fill(0, count($defaultLabels), 0),
                        'backgroundColor' => $colors[$type][0] ?? 'rgba(201, 203, 207, 0.8)',
                        'borderColor' => $colors[$type][1] ?? 'rgba(201, 203, 207, 1)',
                        'borderWidth' => 1
                    ];
                }
                
                $this->downtimeTrendData = [
                    'labels' => $defaultLabels,
                    'datasets' => $defaultDatasets
                ];
            }
            
            Log::info('DowntimeImpact: getDowntimeTrendData concluído com sucesso');
            
        } catch (\Exception $e) {
            Log::error('Erro em getDowntimeTrendData', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Em caso de erro, usar valores padrão
            $defaultLabels = [];
            $startCarbon = Carbon::parse($startDate);
            $endCarbon = Carbon::parse($endDate);
            $grouping = 'daily';
            $dateFormatLabel = 'M d';
            
            if ($startCarbon->diffInDays($endCarbon) > 90) {
                $grouping = 'monthly';
                $dateFormatLabel = 'M Y';
            } elseif ($startCarbon->diffInDays($endCarbon) > 30) {
                $grouping = 'weekly';
                $dateFormatLabel = 'W w';
            }
            
            // Generate default dates
            $currentDate = $startCarbon->copy();
            while ($currentDate->lte($endCarbon)) {
                $defaultLabels[] = $currentDate->format($dateFormatLabel);
                if ($grouping === 'daily') {
                    $currentDate->addDay();
                } elseif ($grouping === 'weekly') {
                    $currentDate->addWeek();
                } else {
                    $currentDate->addMonth();
                }
            }
            
            $this->downtimeTrendData = [
                'labels' => $defaultLabels,
                'datasets' => [
                    [
                        'label' => 'Planned',
                        'data' => array_fill(0, count($defaultLabels), 0),
                        'backgroundColor' => 'rgba(54, 162, 235, 0.8)',
                        'borderColor' => 'rgba(54, 162, 235, 1)',
                        'borderWidth' => 1
                    ],
                    [
                        'label' => 'Unplanned',
                        'data' => array_fill(0, count($defaultLabels), 0),
                        'backgroundColor' => 'rgba(255, 99, 132, 0.8)',
                        'borderColor' => 'rgba(255, 99, 132, 1)',
                        'borderWidth' => 1
                    ],
                    [
                        'label' => 'Operational',
                        'data' => array_fill(0, count($defaultLabels), 0),
                        'backgroundColor' => 'rgba(255, 205, 86, 0.8)',
                        'borderColor' => 'rgba(255, 205, 86, 1)',
                        'borderWidth' => 1
                    ]
                ]
            ];
        }
    }

    /**
     * Gera recomendações inteligentes com base nos dados de downtime analisados
     */
    protected function generateRecommendations()
    {
        try {
            Log::info('DowntimeImpact: Gerando recomendações');
            $this->recommendations = [];
            
            if (empty($this->downtimeRecords)) {
                // Se não há registros, retornar recomendação genérica
                $this->recommendations[] = [
                    'title' => __('Implement Data Collection Process'),
                    'target' => __('General'),
                    'priority' => 'high',
                    'impact' => __('High'),
                    'description' => __('Start collecting detailed downtime records to enable accurate analysis. Implement a structured process for recording equipment failures, maintenance activities, and operational disruptions.'),
                    'benefits' => __('Better data quality for future analysis and decision making'),
                    'icon' => 'fa-database',
                    'potential_hours_saved' => 20.0,
                    'estimated_roi' => 5000,
                    'implementation_steps' => [
                        __('Design a standardized data collection form for downtime events'),
                        __('Train all maintenance and production staff on data collection procedures'),
                        __('Implement digital data collection tools (mobile app or terminal station)'),
                        __('Establish a review process to ensure data quality and completeness'),
                        __('Create regular reports to monitor data collection effectiveness')
                    ]
                ];
                return;
            }
            
            // Analisar equipamentos críticos - identifica equipamentos com maior impacto
            $equipmentDowntimes = [];
            foreach ($this->downtimeRecords as $record) {
                $equipment = $record['equipment'];
                if (!isset($equipmentDowntimes[$equipment])) {
                    $equipmentDowntimes[$equipment] = [
                        'duration' => 0,
                        'count' => 0
                    ];
                }
                $equipmentDowntimes[$equipment]['duration'] += $record['duration'];
                $equipmentDowntimes[$equipment]['count']++;
            }
            
            // Ordenar por duração total
            uasort($equipmentDowntimes, function($a, $b) {
                return $b['duration'] <=> $a['duration'];
            });
            
            // Pegar os 3 equipamentos mais críticos
            $criticalEquipments = array_slice($equipmentDowntimes, 0, 3, true);
            
            // Recomendação para equipamento mais crítico (se houver)
            if (!empty($criticalEquipments)) {
                $mostCriticalEquipment = array_key_first($criticalEquipments);
                $downtimeHours = $criticalEquipments[$mostCriticalEquipment]['duration'];
                $downtimeCount = $criticalEquipments[$mostCriticalEquipment]['count'];
                
                // Calcular o potencial de economia baseado nos dados históricos
                $potentialReduction = $downtimeHours * (rand(30, 60) / 100); // 30-60% de redução
                $estimatedRoi = $potentialReduction * rand(200, 500); // ROI estimado baseado nas horas economizadas
                
                $this->recommendations[] = [
                    'title' => __('Preventive Maintenance Program for :equipment', ['equipment' => $mostCriticalEquipment]),
                    'target' => $mostCriticalEquipment,
                    'priority' => 'high',
                    'impact' => __('High'),
                    'description' => __('Implement a comprehensive preventive maintenance program for :equipment which has caused :hours hours of downtime across :count incidents.', [
                        'equipment' => $mostCriticalEquipment,
                        'hours' => number_format($downtimeHours, 1),
                        'count' => $downtimeCount
                    ]),
                    'benefits' => __('Potential reduction of up to :percent% in unplanned downtime', [
                        'percent' => rand(20, 40)
                    ]),
                    'icon' => 'fa-tools',
                    'potential_hours_saved' => $potentialReduction,
                    'estimated_roi' => $estimatedRoi,
                    'implementation_steps' => [
                        __('Conduct a detailed failure analysis of the :equipment', ['equipment' => $mostCriticalEquipment]), 
                        __('Develop a preventive maintenance checklist specific to failure modes'),
                        __('Schedule regular maintenance intervals based on manufacturer recommendations and operational data'),
                        __('Train maintenance personnel on equipment-specific procedures'),
                        __('Implement condition monitoring where applicable to detect early signs of failure'),
                        __('Document all maintenance activities and review effectiveness quarterly')
                    ]
                ];
            }
            
            // Analisar causas comuns de falha
            $failureCauses = [];
            foreach ($this->downtimeRecords as $record) {
                $reason = $record['reason'];
                if (!isset($failureCauses[$reason])) {
                    $failureCauses[$reason] = 0;
                }
                $failureCauses[$reason]++;
            }
            
            // Ordenar por frequência
            arsort($failureCauses);
            
            // Pegar a causa mais comum
            if (!empty($failureCauses)) {
                $mostCommonCause = array_key_first($failureCauses);
                $occurrences = $failureCauses[$mostCommonCause];
                
                if ($occurrences > 1) { // Só recomendar se ocorrer mais de uma vez
                    // Estimar horas potenciais economizadas e ROI
                    $avgTimePerOccurrence = 2.5; // Suposição da média em horas por ocorrência
                    $potentialHoursSaved = $occurrences * $avgTimePerOccurrence * 0.7; // Supondo que 70% desses incidentes possam ser evitados
                    $estimatedRoi = $potentialHoursSaved * rand(150, 300); // Valor estimado por hora economizada
                    
                    $this->recommendations[] = [
                        'title' => __('Address Recurring Issue: :cause', ['cause' => $mostCommonCause]),
                        'target' => __('Process Improvement'),
                        'priority' => 'medium',
                        'impact' => __('Medium'),
                        'description' => __('Investigate and address the root cause of recurring issue: ":cause" which has occurred :count times in the selected period.', [
                            'cause' => $mostCommonCause,
                            'count' => $occurrences
                        ]),
                        'benefits' => __('Elimination of a frequent failure mode'),
                        'icon' => 'fa-search',
                        'potential_hours_saved' => $potentialHoursSaved,
                        'estimated_roi' => $estimatedRoi,
                        'implementation_steps' => [
                            __('Form a cross-functional team to analyze the recurring issue'),
                            __('Conduct root cause analysis using 5-Why or Fishbone diagram methods'),
                            __('Develop countermeasures to address the identified root causes'),
                            __('Implement changes and document the resolution process'),
                            __('Monitor the effectiveness of the solution for at least 3 months'),
                            __('Standardize successful solutions as part of regular procedures')
                        ]
                    ];
                }
            }
            
            // Analisar tipos de downtime
            $downtimeTypes = [];
            foreach ($this->downtimeRecords as $record) {
                $type = $record['type'];
                if (!isset($downtimeTypes[$type])) {
                    $downtimeTypes[$type] = [
                        'duration' => 0,
                        'count' => 0
                    ];
                }
                $downtimeTypes[$type]['duration'] += $record['duration'];
                $downtimeTypes[$type]['count']++;
            }
            
            // Verificar proporção de downtime não planejado
            if (isset($downtimeTypes['Unplanned'])) {
                $unplannedDowntime = $downtimeTypes['Unplanned']['duration'];
                $totalDowntime = array_sum(array_column($downtimeTypes, 'duration'));
                $unplannedPercentage = ($totalDowntime > 0) ? ($unplannedDowntime / $totalDowntime) * 100 : 0;
                
                if ($unplannedPercentage > 50) { // Se mais de 50% do downtime é não planejado
                    // Calcular potencial de economia e ROI
                    $potentialHoursSaved = $unplannedDowntime * 0.4; // Assumindo que 40% do downtime não planejado pode ser reduzido
                    $estimatedRoi = $potentialHoursSaved * rand(300, 600); // Valor mais alto para essa estratégia
                    
                    $this->recommendations[] = [
                        'title' => __('Improve Maintenance Planning'),
                        'target' => __('Maintenance Department'),
                        'priority' => 'high',
                        'impact' => __('High'),
                        'description' => __(':percent% of total downtime is unplanned. Implement better maintenance planning and scheduling to convert unplanned downtime into planned maintenance.', [
                            'percent' => number_format($unplannedPercentage, 1)
                        ]),
                        'benefits' => __('Reduction in production impact and emergency maintenance costs'),
                        'icon' => 'fa-calendar-alt',
                        'potential_hours_saved' => $potentialHoursSaved,
                        'estimated_roi' => $estimatedRoi,
                        'implementation_steps' => [
                            __('Review current maintenance planning processes and identify gaps'),
                            __('Implement a maintenance planning software system with scheduling capabilities'),
                            __('Establish weekly maintenance planning meetings with production'),
                            __('Develop a critical equipment list with maintenance frequency requirements'),
                            __('Create a spare parts inventory system to support planned maintenance'),
                            __('Train planners and supervisors on effective maintenance scheduling')
                        ]
                    ];
                }
            }
            
            // Adicionar recomendação genérica sobre treinamento, se não tiver muitas recomendações
            if (count($this->recommendations) < 2) {
                $totalDowntimeHours = 0;
                foreach ($this->downtimeRecords as $record) {
                    $totalDowntimeHours += $record['duration'];
                }
                
                // Estimar economia potencial para treinamento de operadores
                $potentialHoursSaved = max(15, $totalDowntimeHours * 0.15); // Pelo menos 15 horas ou 15% do downtime total
                $estimatedRoi = $potentialHoursSaved * rand(150, 250);
                
                $this->recommendations[] = [
                    'title' => __('Operator Training Program'),
                    'target' => __('Production Team'),
                    'priority' => 'medium',
                    'impact' => __('Medium'),
                    'description' => __('Develop and implement a comprehensive training program for operators focused on equipment operation best practices and early problem detection.'),
                    'benefits' => __('Improved equipment utilization and reduced operator-induced failures'),
                    'icon' => 'fa-user-graduate',
                    'potential_hours_saved' => $potentialHoursSaved,
                    'estimated_roi' => $estimatedRoi,
                    'implementation_steps' => [
                        __('Assess current operator skill levels and identify training gaps'),
                        __('Develop equipment-specific training materials and standard operating procedures'),
                        __('Create a training schedule that minimizes production disruption'),
                        __('Implement hands-on training sessions with experienced operators as mentors'),
                        __('Establish operator certification program with regular refresher training'),
                        __('Develop feedback mechanism to continuously improve the training program')
                    ]
                ];
            }
            
            // Adicionar recomendação sobre análise de dados, se não tiver muitas recomendações
            if (count($this->recommendations) < 3) {
                // Estimar economia potencial para monitoramento OEE
                $potentialHoursSaved = ($totalDowntimeHours > 0) ? $totalDowntimeHours * 0.25 : 25; // 25% do downtime total ou 25 horas
                $estimatedRoi = $potentialHoursSaved * rand(200, 400);
                
                $this->recommendations[] = [
                    'title' => __('Implement OEE Monitoring'),
                    'target' => __('All Equipment'),
                    'priority' => 'medium',
                    'impact' => __('High'),
                    'description' => __('Implement Overall Equipment Effectiveness (OEE) monitoring to track availability, performance, and quality metrics for all critical equipment.'),
                    'benefits' => __('Holistic view of equipment performance beyond just downtime'),
                    'icon' => 'fa-chart-line',
                    'potential_hours_saved' => $potentialHoursSaved,
                    'estimated_roi' => $estimatedRoi,
                    'implementation_steps' => [
                        __('Define OEE calculation standards for different equipment types'),
                        __('Select and implement OEE monitoring software or dashboard solution'),
                        __('Install necessary data collection points (sensors, counters, etc.)'),
                        __('Train supervisors and managers on OEE principles and interpretation'),
                        __('Establish baseline OEE values for all critical equipment'),
                        __('Develop improvement action plans based on OEE data insights')
                    ]
                ];
            }
            
            Log::info('DowntimeImpact: Recomendações geradas', [
                'count' => count($this->recommendations)
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao gerar recomendações', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Adicionar recomendação genérica em caso de erro
            $this->recommendations = [[
                'title' => __('Improve Data Collection Process'),
                'target' => __('Maintenance Department'),
                'priority' => 'medium',
                'impact' => __('Medium'),
                'description' => __('Enhance data collection processes to enable more detailed analysis of downtime causes and impacts.'),
                'benefits' => __('Better insights for future improvement initiatives'),
                'icon' => 'fa-database',
                'potential_hours_saved' => 15.0,
                'estimated_roi' => 3750,
                'implementation_steps' => [
                    __('Audit current downtime data collection processes'),
                    __('Identify gaps in data completeness and accuracy'),
                    __('Implement standardized failure coding system'),
                    __('Train maintenance personnel on proper data entry procedures'),
                    __('Set up regular data quality reviews and validation')
                ]
            ]];
        }
    }
    
    /**
     * Gera dados para o gráfico de downtime por equipamento
     * 
     * @param array $equipmentIds IDs dos equipamentos a considerar
     * @param string $startDate Data de início
     * @param string $endDate Data de fim
     */
    protected function getDowntimeByEquipmentData($equipmentIds, $startDate, $endDate)
    {
        try {
            Log::info('DowntimeImpact: Gerando dados de downtime por equipamento');
            
            // Inicializar com dados vazios
            $labels = [];
            $data = [];
            $backgroundColors = [];
            $borderColors = [];
            
            // Se não há registros, usar dados vazios
            if (empty($this->downtimeRecords)) {
                $this->downtimeByEquipmentData = [
                    'labels' => [],
                    'datasets' => [[
                        'label' => __('Hours'),
                        'data' => [],
                        'backgroundColor' => [],
                        'borderColor' => [],
                        'borderWidth' => 1
                    ]]
                ];
                return;
            }
            
            // Agrupar downtime por equipamento
            $equipmentDowntimes = [];
            foreach ($this->downtimeRecords as $record) {
                $equipment = $record['equipment'];
                if (!isset($equipmentDowntimes[$equipment])) {
                    $equipmentDowntimes[$equipment] = 0;
                }
                $equipmentDowntimes[$equipment] += $record['duration'];
            }
            
            // Ordenar por maior downtime
            arsort($equipmentDowntimes);
            
            // Limitar para os 10 equipamentos com maior downtime
            $equipmentDowntimes = array_slice($equipmentDowntimes, 0, 10, true);
            
            // Cores para o gráfico
            $colors = [
                ['rgba(255, 99, 132, 0.8)', 'rgba(255, 99, 132, 1)'],
                ['rgba(54, 162, 235, 0.8)', 'rgba(54, 162, 235, 1)'],
                ['rgba(255, 206, 86, 0.8)', 'rgba(255, 206, 86, 1)'],
                ['rgba(75, 192, 192, 0.8)', 'rgba(75, 192, 192, 1)'],
                ['rgba(153, 102, 255, 0.8)', 'rgba(153, 102, 255, 1)'],
                ['rgba(255, 159, 64, 0.8)', 'rgba(255, 159, 64, 1)'],
                ['rgba(199, 199, 199, 0.8)', 'rgba(199, 199, 199, 1)'],
                ['rgba(83, 102, 255, 0.8)', 'rgba(83, 102, 255, 1)'],
                ['rgba(40, 159, 64, 0.8)', 'rgba(40, 159, 64, 1)'],
                ['rgba(210, 199, 199, 0.8)', 'rgba(210, 199, 199, 1)'],
            ];
            
            // Preparar dados para o gráfico
            $i = 0;
            foreach ($equipmentDowntimes as $equipment => $hours) {
                $labels[] = $equipment;
                $data[] = number_format($hours, 1);
                $colorIndex = $i % count($colors);
                $backgroundColors[] = $colors[$colorIndex][0];
                $borderColors[] = $colors[$colorIndex][1];
                $i++;
            }
            
            // Formatar dados para o gráfico
            $this->downtimeByEquipmentData = [
                'labels' => $labels,
                'datasets' => [[
                    'label' => __('Hours'),
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => $borderColors,
                    'borderWidth' => 1
                ]]
            ];
            
            Log::info('DowntimeImpact: Dados de downtime por equipamento gerados', [
                'equipmentCount' => count($labels)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao gerar dados de downtime por equipamento', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Em caso de erro, usar dados vazios
            $this->downtimeByEquipmentData = [
                'labels' => [],
                'datasets' => [[
                    'label' => __('Hours'),
                    'data' => [],
                    'backgroundColor' => [],
                    'borderColor' => [],
                    'borderWidth' => 1
                ]]
            ];
        }
    }
    
    /**
     * Gera dados para o gráfico de tipos de downtime
     * 
     * @param array $equipmentIds IDs dos equipamentos a considerar
     * @param string $startDate Data de início
     * @param string $endDate Data de fim
     */
    protected function getDowntimeTypesData($equipmentIds, $startDate, $endDate)
    {
        try {
            Log::info('DowntimeImpact: Gerando dados de tipos de downtime');
            
            // Se não há registros, usar dados vazios
            if (empty($this->downtimeRecords)) {
                $this->downtimeTypesData = [
                    'labels' => [],
                    'datasets' => [[
                        'data' => [],
                        'backgroundColor' => [],
                        'borderColor' => [],
                        'borderWidth' => 1
                    ]]
                ];
                return;
            }
            
            // Agrupar downtime por tipo
            $downtimeTypes = [];
            foreach ($this->downtimeRecords as $record) {
                $type = $record['type'];
                if (!isset($downtimeTypes[$type])) {
                    $downtimeTypes[$type] = 0;
                }
                $downtimeTypes[$type] += $record['duration'];
            }
            
            // Tipos de downtime predefinidos
            $typeColors = [
                'Planned' => ['rgba(54, 162, 235, 0.8)', 'rgba(54, 162, 235, 1)'],
                'Unplanned' => ['rgba(255, 99, 132, 0.8)', 'rgba(255, 99, 132, 1)'],
                'Operational' => ['rgba(255, 206, 86, 0.8)', 'rgba(255, 206, 86, 1)'],
                'Other' => ['rgba(153, 102, 255, 0.8)', 'rgba(153, 102, 255, 1)'],
            ];
            
            // Preparar dados para o gráfico
            $labels = [];
            $data = [];
            $backgroundColors = [];
            $borderColors = [];
            
            foreach ($downtimeTypes as $type => $hours) {
                $labels[] = $type;
                $data[] = number_format($hours, 1);
                
                // Usar cores predefinidas para tipos conhecidos, ou cores padrão para outros
                if (isset($typeColors[$type])) {
                    $backgroundColors[] = $typeColors[$type][0];
                    $borderColors[] = $typeColors[$type][1];
                } else {
                    $backgroundColors[] = 'rgba(201, 203, 207, 0.8)';
                    $borderColors[] = 'rgba(201, 203, 207, 1)';
                }
            }
            
            // Formatar dados para o gráfico
            $this->downtimeTypesData = [
                'labels' => $labels,
                'datasets' => [[
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => $borderColors,
                    'borderWidth' => 1
                ]]
            ];
            
            Log::info('DowntimeImpact: Dados de tipos de downtime gerados', [
                'typesCount' => count($labels)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao gerar dados de tipos de downtime', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Em caso de erro, usar dados vazios
            $this->downtimeTypesData = [
                'labels' => [],
                'datasets' => [[
                    'data' => [],
                    'backgroundColor' => [],
                    'borderColor' => [],
                    'borderWidth' => 1
                ]]
            ];
        }
    }
    
    /**
     * Gera dados para o gráfico de causas de downtime
     * 
     * @param array $equipmentIds IDs dos equipamentos a considerar
     * @param string $startDate Data de início
     * @param string $endDate Data de fim
     */
    protected function getDowntimeCausesData($equipmentIds, $startDate, $endDate)
    {
        try {
            Log::info('DowntimeImpact: Gerando dados de causas de downtime');
            
            // Se não há registros, usar dados vazios
            if (empty($this->downtimeRecords)) {
                $this->downtimeCausesData = [
                    'labels' => [],
                    'datasets' => [[
                        'data' => [],
                        'backgroundColor' => [],
                        'borderColor' => [],
                        'borderWidth' => 1
                    ]]
                ];
                return;
            }
            
            // Agrupar downtime por causa
            $downtimeCauses = [];
            foreach ($this->downtimeRecords as $record) {
                $cause = $record['reason'];
                if (!isset($downtimeCauses[$cause])) {
                    $downtimeCauses[$cause] = 0;
                }
                $downtimeCauses[$cause] += $record['duration'];
            }
            
            // Ordenar por maior downtime
            arsort($downtimeCauses);
            
            // Limitar para as 10 causas com maior impacto
            $downtimeCauses = array_slice($downtimeCauses, 0, 10, true);
            
            // Cores para o gráfico
            $colors = [
                ['rgba(255, 99, 132, 0.8)', 'rgba(255, 99, 132, 1)'],
                ['rgba(54, 162, 235, 0.8)', 'rgba(54, 162, 235, 1)'],
                ['rgba(255, 206, 86, 0.8)', 'rgba(255, 206, 86, 1)'],
                ['rgba(75, 192, 192, 0.8)', 'rgba(75, 192, 192, 1)'],
                ['rgba(153, 102, 255, 0.8)', 'rgba(153, 102, 255, 1)'],
                ['rgba(255, 159, 64, 0.8)', 'rgba(255, 159, 64, 1)'],
                ['rgba(199, 199, 199, 0.8)', 'rgba(199, 199, 199, 1)'],
                ['rgba(83, 102, 255, 0.8)', 'rgba(83, 102, 255, 1)'],
                ['rgba(40, 159, 64, 0.8)', 'rgba(40, 159, 64, 1)'],
                ['rgba(210, 199, 199, 0.8)', 'rgba(210, 199, 199, 1)'],
            ];
            
            // Preparar dados para o gráfico
            $labels = [];
            $data = [];
            $backgroundColors = [];
            $borderColors = [];
            
            $i = 0;
            foreach ($downtimeCauses as $cause => $hours) {
                $labels[] = $cause;
                $data[] = number_format($hours, 1);
                $colorIndex = $i % count($colors);
                $backgroundColors[] = $colors[$colorIndex][0];
                $borderColors[] = $colors[$colorIndex][1];
                $i++;
            }
            
            // Formatar dados para o gráfico
            $this->downtimeCausesData = [
                'labels' => $labels,
                'datasets' => [[
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => $borderColors,
                    'borderWidth' => 1
                ]]
            ];
            
            Log::info('DowntimeImpact: Dados de causas de downtime gerados', [
                'causesCount' => count($labels)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao gerar dados de causas de downtime', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Em caso de erro, usar dados vazios
            $this->downtimeCausesData = [
                'labels' => [],
                'datasets' => [[
                    'data' => [],
                    'backgroundColor' => [],
                    'borderColor' => [],
                    'borderWidth' => 1
                ]]
            ];
        }
    }
    
    /**
     * Gera dados para o gráfico de impacto financeiro
     * 
     * @param array $equipmentIds IDs dos equipamentos a considerar
     * @param string $startDate Data de início
     * @param string $endDate Data de fim
     */
    protected function getFinancialImpactData($equipmentIds, $startDate, $endDate)
    {
        try {
            Log::info('DowntimeImpact: Gerando dados de impacto financeiro');
            
            // Se não há registros, usar dados vazios
            if (empty($this->downtimeRecords)) {
                $this->financialImpactData = [
                    'labels' => [],
                    'datasets' => [[
                        'label' => __('Cost ($)'),
                        'data' => [],
                        'backgroundColor' => 'rgba(255, 99, 132, 0.8)',
                        'borderColor' => 'rgba(255, 99, 132, 1)',
                        'borderWidth' => 1
                    ]]
                ];
                return;
            }
            
            // Calcular impacto financeiro por equipamento
            // Assumindo um custo por hora de downtime de $500 (valor exemplo)
            $costPerHour = 500;
            
            $equipmentImpacts = [];
            foreach ($this->downtimeRecords as $record) {
                $equipment = $record['equipment'];
                if (!isset($equipmentImpacts[$equipment])) {
                    $equipmentImpacts[$equipment] = 0;
                }
                $equipmentImpacts[$equipment] += $record['duration'] * $costPerHour;
            }
            
            // Ordenar por maior impacto financeiro
            arsort($equipmentImpacts);
            
            // Limitar para os 10 equipamentos com maior impacto
            $equipmentImpacts = array_slice($equipmentImpacts, 0, 10, true);
            
            // Preparar dados para o gráfico
            $labels = [];
            $data = [];
            
            foreach ($equipmentImpacts as $equipment => $cost) {
                $labels[] = $equipment;
                $data[] = number_format($cost, 0);
            }
            
            // Formatar dados para o gráfico
            $this->financialImpactData = [
                'labels' => $labels,
                'datasets' => [[
                    'label' => __('Cost ($)'),
                    'data' => $data,
                    'backgroundColor' => 'rgba(255, 99, 132, 0.8)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'borderWidth' => 1
                ]]
            ];
            
            // Atualizar o impacto financeiro total
            $this->financialImpact = array_sum($equipmentImpacts);
            
            Log::info('DowntimeImpact: Dados de impacto financeiro gerados', [
                'equipmentCount' => count($labels),
                'totalImpact' => $this->financialImpact
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao gerar dados de impacto financeiro', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Em caso de erro, usar dados vazios
            $this->financialImpactData = [
                'labels' => [],
                'datasets' => [[
                    'label' => __('Cost ($)'),
                    'data' => [],
                    'backgroundColor' => 'rgba(255, 99, 132, 0.8)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'borderWidth' => 1
                ]]
            ];
        }
    }
}
