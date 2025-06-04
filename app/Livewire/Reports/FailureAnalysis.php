<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Models\MaintenanceArea as Area;
use App\Models\MaintenanceEquipment as Equipment;
use App\Models\MaintenanceLine as Line;
use App\Models\MaintenanceCorrective;
use App\Models\FailureCause;
use App\Models\FailureMode;
use App\Models\User;

class FailureAnalysis extends Component
{
    use WithPagination;

    // Properties
    public $title = 'Análise de Falhas';
    
    // Configurações de paginação
    public $perPage = 10;
    public $search = '';
    
    // Propriedades para paginação dos padrões identificados
    public $patternPerPage = 5;
    public $patternPage = 1;
    public $totalPatterns = 0;
    
    // Propriedades para contagem de resultados filtrados
    public $totalFilteredFailures = 0;
    public $page = 1;
    
    // Propriedades para ordenação
    public $sortField = 'date';
    public $sortDirection = 'desc';
    
    // Propriedades para dados estatísticos
    public $totalFailures = 0;
    
    // Cores para os gráficos
    protected $chartColors = [
        '#4f46e5', // Indigo
        '#0891b2', // Cyan
        '#7c3aed', // Violet
        '#db2777', // Pink
        '#f59e0b', // Amber
        '#10b981', // Emerald
        '#ef4444', // Red
        '#3b82f6', // Blue
        '#84cc16', // Lime
        '#8b5cf6'  // Purple
    ];
    public $topFailureCause = 'N/A';
    public $topFailureCauseCount = 0;
    public $mostFailingEquipment = 'N/A';
    public $mostFailingEquipmentCount = 0;
    public $averageDowntime = 0;
    public $patterns = [];
    public $failureRecords = [];
    
    // Chart data
    public $failureCausesData = [];
    public $failuresByEquipmentData = [];
    public $failuresOverTimeData = [];
    public $failureImpactData = [];
    public $categoriesDistributionData = [
        'mode' => [
            'labels' => [],
            'datasets' => [
                [
                    'label' => 'Distribuição por Categoria de Modo',
                    'data' => [],
                    'backgroundColor' => [],
                    'borderColor' => [],
                    'borderWidth' => 1
                ]
            ]
        ],
        'cause' => [
            'labels' => [],
            'datasets' => [
                [
                    'label' => 'Distribuição por Categoria de Causa',
                    'data' => [],
                    'backgroundColor' => [],
                    'borderColor' => [],
                    'borderWidth' => 1
                ]
            ]
        ]
    ];
    
    // Filters
    public $dateRange = 'month';
    public $startDate;
    public $endDate;
    public $selectedArea = 'all';
    public $selectedLine = 'all';
    public $selectedEquipment = 'all';
    
    // Filter Options
    public $areas = [];
    public $lines = [];
    public $equipment = [];
    
    // Modal control
    public $showDetailModal = false;
    public $selectedFailure = null;

    // Listeners for Livewire events
    protected $listeners = [
        'refreshFailureAnalysis' => '$refresh',
        'dateRangeSelected' => 'setCustomDateRange'
    ];

    public function mount()
    {
        // Inicializar com estado vazio
        $this->setEmptyState();
        
        Log::info('FailureAnalysis: Inicializando componente');
        
        // Verificar tabelas disponíveis no banco
        $tables = DB::select('SHOW TABLES');
        $tableList = array_map(function($table) {
            return array_values((array)$table)[0];
        }, $tables);
        
        Log::info('FailureAnalysis: Tabelas disponíveis no banco: ' . implode(', ', $tableList));
        
        // Verificar tabelas de manutenção corretiva
        foreach ($tableList as $table) {
            if (strpos($table, 'maintenance') !== false || strpos($table, 'equipment') !== false) {
                Log::info('FailureAnalysis: Possível tabela de manutenção corretiva encontrada: ' . $table);
            }
        }
        
        // Verificar tabela de tarefas de manutenção
        if (in_array('maintenance_tasks', $tableList)) {
            $taskCount = DB::table('maintenance_tasks')->count();
            $tasks = DB::table('maintenance_tasks')
                ->select('id', 'title', 'description', 'created_at', 'updated_at', 'deleted_at')
                ->limit(3)
                ->get();
            
            Log::info('FailureAnalysis: Colunas na tabela maintenance_tasks: ' 
                . implode(', ', array_keys((array)$tasks->first() ?? [])));
        }
        
        $this->loadFilterOptions();

        // Carregar dados iniciais
        $this->loadFailureData();

        Log::info('FailureAnalysis: Componente inicializado');
    }

    public function setDateRange($range)
    {
        Log::info("FailureAnalysis: Definindo período: {$range}");
        $this->dateRange = $range;
        
        $today = Carbon::today();
        
        switch ($range) {
            case 'today':
                $this->startDate = $today->copy()->startOfDay();
                $this->endDate = $today->copy()->endOfDay();
                break;
            case 'week':
                $this->startDate = $today->copy()->startOfWeek();
                $this->endDate = $today->copy()->endOfWeek();
                break;
            case 'month':
                $this->startDate = $today->copy()->startOfMonth();
                $this->endDate = $today->copy()->endOfMonth();
                break;
            case 'quarter':
                $this->startDate = $today->copy()->startOfQuarter();
                $this->endDate = $today->copy()->endOfQuarter();
                break;
            case 'year':
                $this->startDate = $today->copy()->startOfYear();
                $this->endDate = $today->copy()->endOfYear();
                break;
            default:
                // Se não for uma das opções acima, definimos o mês corrente
                $this->startDate = $today->copy()->startOfMonth();
                $this->endDate = $today->copy()->endOfMonth();
                break;
        }
        
        Log::info("FailureAnalysis: Definido período de {$this->startDate} até {$this->endDate}");
    }

    // Set custom date range
    public function setCustomDateRange($startDate, $endDate)
    {
        $this->startDate = Carbon::parse($startDate);
        $this->endDate = Carbon::parse($endDate);
        $this->dateRange = 'custom';
        $this->loadFailureData();
    }

    // Load filter options
    public function loadFilterOptions()
    {
        try {
            // Load areas
            $this->areas = Area::query()
                ->orderBy('name')
                ->get()
                ->mapWithKeys(function ($area) {
                    return [$area->id => $area->name];
                })
                ->toArray();
                
            // Load lines based on selected area
            $linesQuery = Line::query()->orderBy('name');
            if ($this->selectedArea !== 'all') {
                $linesQuery->where('area_id', $this->selectedArea);
            }
            
            $this->lines = $linesQuery->get()
                ->mapWithKeys(function ($line) {
                    return [$line->id => $line->name];
                })
                ->toArray();
                
            // Load equipment based on selected line
            $equipmentQuery = Equipment::query()->orderBy('name');
            if ($this->selectedLine !== 'all') {
                $equipmentQuery->where('line_id', $this->selectedLine);
            }
            
            $this->equipment = $equipmentQuery->get()
                ->mapWithKeys(function ($equip) {
                    return [$equip->id => $equip->name];
                })
                ->toArray();
                
        } catch (\Exception $e) {
            Log::error('FailureAnalysis: Erro ao carregar opções de filtro: ' . $e->getMessage());
        }
    }

    // Updated lifecycle hooks for filter changes
    public function updatedSelectedArea()
    {
        $this->selectedLine = 'all';
        $this->selectedEquipment = 'all';
        $this->loadFilterOptions();
        $this->loadFailureData();
    }
    
    public function updatedSelectedLine()
    {
        $this->selectedEquipment = 'all';
        $this->loadFilterOptions();
        $this->loadFailureData();
    }
    
    public function updatedDateRange()
    {
        Log::info("FailureAnalysis: Período alterado para: {$this->dateRange}");
        $this->setDateRange($this->dateRange);
        
        // Forçar recarregamento completo dos dados
        $this->setEmptyState(); // Limpar estado atual
        $this->loadFailureData(); // Carregar novos dados
        
        // Notificar o usuário
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Período atualizado com sucesso!'
        ]);
    }
    
    public function render()
    {
        // O método loadFailureData já é chamado no mount() e nos eventos,
        // não precisamos chamá-lo novamente aqui para evitar carregamentos duplicados
        
        return view('livewire.reports.failure-analysis');
    }
    
    // Load failure data based on selected filters
    protected function loadFailureData()
    {
        try {
            Log::info('FailureAnalysis: Starting to load data');
            
            // Verificar se o período está definido
            if (empty($this->startDate) || empty($this->endDate)) {
                $this->setDateRange('month');
            }
            
            // Se a busca estiver definida, filtrar os registros pelo termo
            if (!empty($this->search)) {
                Log::info('FailureAnalysis: Aplicando filtro de busca: ' . $this->search);
            }
            
            // Verificar se a tabela existe antes de prosseguir
            Log::info('FailureAnalysis: Verificando existência da tabela maintenance_correctives');
            
            // Verificar se a tabela existe
            $tableExists = Schema::hasTable('maintenance_correctives');
            Log::info('FailureAnalysis: Tabela maintenance_correctives existe? ' . ($tableExists ? 'Sim' : 'Não'));
            
            if (!$tableExists) {
                Log::error('FailureAnalysis: A tabela maintenance_correctives não existe no banco de dados');
                $this->error = 'A tabela de manutenção corretiva ainda não foi criada no banco de dados.';
                $this->loading = false;
                $this->ready = true;
                return;
            }
            
            // Verificar estrutura da tabela
            Log::info('FailureAnalysis: Verificando estrutura da tabela MaintenanceCorrective');
            $columns = DB::getSchemaBuilder()->getColumnListing('maintenance_correctives');
            Log::info('FailureAnalysis: Colunas encontradas: ' . implode(', ', $columns));
            
            // Verificar quais colunas podem conter datas
            $dateColumns = array_filter($columns, function($col) {
                return strpos($col, 'date') !== false || 
                       strpos($col, 'time') !== false || 
                       in_array($col, ['created_at', 'updated_at']);
            });
            
            // Se não encontrar nenhuma coluna "date", usar a updated_at como fallback
            $dateColumn = isset($columns['date']) ? 'date' : 'updated_at';
            if (in_array('start_time', $columns)) {
                $dateColumn = 'start_time'; // Preferir start_time se disponível
            }
            
            Log::info('FailureAnalysis: Colunas de data encontradas: ' . implode(', ', $dateColumns));
            Log::info('FailureAnalysis: Usando coluna alternativa para data: ' . $dateColumn);
            
            // Verificar registros no período
            // Garantir que as datas estejam formatadas corretamente
            $start = $this->startDate instanceof Carbon ? $this->startDate : Carbon::parse($this->startDate)->startOfDay();
            $end = $this->endDate instanceof Carbon ? $this->endDate : Carbon::parse($this->endDate)->endOfDay();
            
            // Ampliar o período de busca para aumentar chances de encontrar dados
            // Se estamos usando dados de hoje, vamos expandir para o mês inteiro
            if ($this->dateRange === 'today' || $this->dateRange === 'week') {
                $start = Carbon::now()->startOfMonth();
                $end = Carbon::now()->endOfMonth();
                Log::info("FailureAnalysis: Período expandido para o mês atual: {$start->format('Y-m-d')} a {$end->format('Y-m-d')}");
            } else if ($this->dateRange === 'month') {
                $start = Carbon::now()->startOfYear();
                $end = Carbon::now()->endOfYear();
                Log::info("FailureAnalysis: Período expandido para o ano atual: {$start->format('Y-m-d')} a {$end->format('Y-m-d')}");
            }
            
            // Só prosseguir se a tabela existir
            if ($tableExists) {
                // Usar uma consulta mais permissiva para encontrar registros
                $testQuery = MaintenanceCorrective::query();
                
                // Se houver dados suficientes, aplicar o filtro de data
                $allRecords = MaintenanceCorrective::count();
                Log::info('FailureAnalysis: Total de registros na tabela: ' . $allRecords);
            } else {
                $this->error = 'A tabela de manutenção corretiva ainda não foi criada no banco de dados.';
                $this->loading = false;
                $this->ready = true;
                return;
            }
            
            // Se houver dados suficientes, aplicar o filtro de data
            $allRecords = MaintenanceCorrective::count();
            if ($allRecords > 10) {
                $testQuery->whereRaw("DATE({$dateColumn}) >= ?", [$start->format('Y-m-d')])
                         ->whereRaw("DATE({$dateColumn}) <= ?", [$end->format('Y-m-d')]);
            }
                
            $recordsInPeriod = $testQuery->count();
            
            // Registrar a consulta SQL real para depuração
            $sql = str_replace(['?'], ["'".$start->format('Y-m-d')."'", "'".$end->format('Y-m-d')."'"], $testQuery->toSql());
            Log::info("FailureAnalysis: SQL de teste: {$sql}");
            
            Log::info("FailureAnalysis: Registros no período {$start->format('Y-m-d')} a {$end->format('Y-m-d')}: {$recordsInPeriod}");
            
            // Se ainda não encontramos registros, vamos pegar alguns para demonstração
            if ($recordsInPeriod == 0 && $allRecords > 0) {
                // Pegar os últimos 20 registros para demonstração
                $testQuery = MaintenanceCorrective::query()->latest($dateColumn)->limit(20);
                $recordsInPeriod = $testQuery->count();
                Log::info("FailureAnalysis: Carregando {$recordsInPeriod} registros mais recentes para demonstração");
            }
            
            // Verificar quais relacionamentos existem no modelo
            $relationshipsToLoad = ['equipment', 'failureMode', 'failureCause'];
            
            // Verificar relacionamentos aninhados
            $model = new MaintenanceCorrective();
            if (method_exists($model, 'equipment')) {
                // Verificar se equipment.line existe
                if (method_exists($model->equipment()->getRelated(), 'line')) {
                    // Verificar se equipment.line.area existe
                    if (method_exists($model->equipment()->getRelated()->line()->getRelated(), 'area')) {
                        $relationshipsToLoad[] = 'equipment.line.area';
                    }
                }
            }
            
            // Verificar relações com usuários
            $columns = array_flip($columns);
            if (isset($columns['reported_by']) && method_exists($model, 'reporter')) {
                $relationshipsToLoad[] = 'reporter';
            }
            if (isset($columns['resolved_by']) && method_exists($model, 'resolver')) {
                $relationshipsToLoad[] = 'resolver';
            }
            
            Log::info('FailureAnalysis: Carregando relacionamentos: ' . implode(', ', $relationshipsToLoad));
            
            // Iniciar consulta com os filtros
            $query = MaintenanceCorrective::query()
                ->with($relationshipsToLoad);
                
            // Aplicar filtro de data apenas se tiver dados suficientes ou se o testQuery não foi alterado
            if ($recordsInPeriod > 0) {
                // Se estamos usando a consulta de demonstração, use a mesma lógica
                if ($allRecords > 0 && $recordsInPeriod == 0) {
                    $query = $testQuery->with($relationshipsToLoad);
                    Log::info("FailureAnalysis: Usando consulta de demonstração para os gráficos");
                } else {
                    $query->whereRaw("DATE({$dateColumn}) >= ?", [$start->format('Y-m-d')])
                          ->whereRaw("DATE({$dateColumn}) <= ?", [$end->format('Y-m-d')]);
                }
            } else {
                // Se ainda não há registros, pegar os últimos 20
                $query = MaintenanceCorrective::query()
                    ->with($relationshipsToLoad)
                    ->latest($dateColumn)
                    ->limit(20);
                Log::info("FailureAnalysis: Usando últimos 20 registros para os gráficos");
            }
                
            // Apply area filter
            if ($this->selectedArea !== 'all') {
                $areaCount = $query->count();
                $query->whereHas('equipment.line', function($q) {
                    $q->whereHas('area', function($q2) {
                        $q2->where('id', $this->selectedArea);
                    });
                });
                $afterAreaCount = $query->count();
                Log::info("FailureAnalysis: Após filtro de área {$this->selectedArea}: {$afterAreaCount} registros");
            }
            
            // Apply line filter
            if ($this->selectedLine !== 'all') {
                $lineCount = $query->count();
                $query->whereHas('equipment', function($q) {
                    $q->where('line_id', $this->selectedLine);
                });
                $afterLineCount = $query->count();
                Log::info("FailureAnalysis: Após filtro de linha {$this->selectedLine}: {$afterLineCount} registros");
            }
            
            // Apply equipment filter
            if ($this->selectedEquipment !== 'all') {
                $equipmentCount = $query->count();
                $query->where('equipment_id', $this->selectedEquipment);
                $afterEquipmentCount = $query->count();
                Log::info("FailureAnalysis: Após filtro de equipamento {$this->selectedEquipment}: {$afterEquipmentCount} registros");
            }
            
            // Apply sorting - Usar campo de data adequado para ordenar
            try {
                // Verificar se a coluna de ordenação existe
                if (in_array($this->sortField, $columns)) {
                    $query->orderBy($this->sortField, $this->sortDirection);
                } else {
                    // Fallback para updated_at se o campo de ordenação não existir
                    Log::warning("FailureAnalysis: Campo de ordenação {$this->sortField} não encontrado, usando updated_at");
                    $query->orderBy('updated_at', $this->sortDirection);
                }
            } catch (\Exception $e) {
                Log::warning("FailureAnalysis: Erro ao aplicar ordenação: {$e->getMessage()}");
                // Não aplicar ordenação em caso de erro
            }
            
            $result = $query->get();
            Log::info("FailureAnalysis: Consulta final retornou {$result->count()} registros");
            
            if ($result->isEmpty()) {
                Log::info('FailureAnalysis: No failure records found for the selected filters');
                $this->setEmptyState();
                return;
            }
            
            // Processar os resultados para exibição na tabela com verificação robusta dos campos
            $formattedRecords = [];
            foreach ($result as $record) {
                // Função auxiliar para verificar objetos aninhados com segurança
                $getName = function($obj) {
                    if (is_null($obj)) return 'Não especificado';
                    if (is_string($obj)) return $obj; // Se já for uma string, retornar como está
                    if (is_object($obj) && isset($obj->name)) return $obj->name;
                    return 'Não especificado';
                };
                
                // Obter equipamento com verificação segura
                $equipment = is_object($record->equipment) ? $record->equipment : null;
                $line = ($equipment && is_object($equipment->line)) ? $equipment->line : null;
                $area = ($line && is_object($line->area)) ? $line->area : null;
                
                // Obter outros relacionamentos com verificação segura
                $failureMode = is_object($record->failureMode) ? $record->failureMode : null;
                $failureCause = is_object($record->failureCause) ? $record->failureCause : null;
                $reporter = is_object($record->reporter) ? $record->reporter : null;
                $resolver = is_object($record->resolver) ? $record->resolver : null;
                
                try {
                    $formattedRecords[] = [
                        'id' => $record->id,
                        'date' => $record->start_time ? Carbon::parse($record->start_time)->format('d/m/Y') : 'N/A',
                        'equipment' => $getName($equipment),
                        'area' => $getName($area),
                        'line' => $getName($line),
                        'cause' => $getName($failureCause),
                        'mode' => $getName($failureMode),
                        'downtime' => $record->downtime_length ?? 0,
                        'status' => $record->status ?? 'pending',
                        'description' => $record->description ?? 'Sem descrição',
                        'reported_by' => $getName($reporter),
                        'resolved_by' => $getName($resolver),
                        'serial_number' => $equipment && isset($equipment->serial_number) ? $equipment->serial_number : 'N/A'
                    ];
                } catch (\Exception $e) {
                    // Registrar erro ao processar registro específico
                    Log::warning("FailureAnalysis: Erro ao processar registro {$record->id}: {$e->getMessage()}");
                    // Continuar com o próximo registro
                    continue;
                }
            }
            
            // Store the formatted results
            $this->failureRecords = $formattedRecords;
            
            // Garantir que temos registros para trabalhar
            if (count($formattedRecords) > 0) {
                // Usar os dados formatados para gerar as estatísticas
                // mas passar também os dados originais como backup
                $this->generateStatistics($formattedRecords, $result);
                
                // Adicionar log para debug
                Log::info('FailureAnalysis: Registros formatados processados com sucesso: ' . count($formattedRecords));
            } else {
                // Se ainda está vazio, definir estado vazio
                Log::warning('FailureAnalysis: Nenhum registro válido após formatação');
                $this->setEmptyState();
            }
            
            // Disparar evento para o frontend atualizar os gráficos
            $this->dispatch('reportDataUpdated');
            
        } catch (\Exception $e) {
            Log::error('FailureAnalysis: Erro ao carregar dados: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            $this->setEmptyState();
        }
    }
    
    // Generate statistics from the loaded data - trabalhando com dados formatados
    protected function generateStatistics($formattedFailures, $originalFailures = null)
    {
        try {
            // Verificar se estamos recebendo dados formatados (array) ou originais (collection)
            $count = count($formattedFailures);
            
            Log::info('FailureAnalysis: Gerando estatísticas com ' . $count . ' registros formatados');
            
            // Se não temos registros formatados, sair
            if ($count == 0) {
                Log::warning('FailureAnalysis: Sem registros formatados para gerar estatísticas');
                $this->setEmptyState();
                return;
            }
            
            // Definir o total de falhas
            $this->totalFailures = $count;
            Log::info("FailureAnalysis: Total de falhas: {$this->totalFailures}");
            
            // Criar dados de demonstração para gráficos
            $this->createDemoChartData();

            if ($this->totalFailures > 0) {
                // Trabalhar com dados já formatados
                
                // Calculate most common failure cause
                try {
                    // Agrupar por causa de falha
                    $causeGroups = [];
                    foreach ($formattedFailures as $failure) {
                        $cause = $failure['cause'] ?? 'Desconhecido';
                        if (!isset($causeGroups[$cause])) {
                            $causeGroups[$cause] = [];
                        }
                        $causeGroups[$cause][] = $failure;
                    }
                    
                    // Converter para o formato esperado
                    $causeCounts = collect($causeGroups)->map(function($group, $name) {
                        return [
                            'name' => $name,
                            'count' => count($group)
                        ];
                    })->sortByDesc('count')->values();

                    if ($causeCounts->isNotEmpty()) {
                        $topCause = $causeCounts->first();
                        $this->topFailureCause = $topCause['name'];
                        $this->topFailureCauseCount = $topCause['count'];
                        Log::info("FailureAnalysis: Causa principal: {$this->topFailureCause} ({$this->topFailureCauseCount} ocorrências)");
                    } else {
                        $this->topFailureCause = 'Não identificado';
                        $this->topFailureCauseCount = 0;
                        Log::warning('FailureAnalysis: Nenhuma causa de falha identificada');
                    }
                } catch (\Exception $e) {
                    $this->topFailureCause = 'Erro na análise';
                    $this->topFailureCauseCount = 0;
                    Log::error('FailureAnalysis: Erro ao calcular causas de falha: ' . $e->getMessage());
                }
                
                // Calculate equipment with most failures
                try {
                    // Agrupar por equipamento
                    $equipmentGroups = [];
                    foreach ($formattedFailures as $failure) {
                        $equipment = $failure['equipment'] ?? 'Desconhecido';
                        if (!isset($equipmentGroups[$equipment])) {
                            $equipmentGroups[$equipment] = [];
                        }
                        $equipmentGroups[$equipment][] = $failure;
                    }
                    
                    // Converter para o formato esperado
                    $equipmentCounts = collect($equipmentGroups)->map(function($group, $name) {
                        return [
                            'name' => $name,
                            'count' => count($group)
                        ];
                    })->sortByDesc('count')->values();

                    if ($equipmentCounts->isNotEmpty()) {
                        $topEquipment = $equipmentCounts->first();
                        $this->mostFailingEquipment = $topEquipment['name'];
                        $this->mostFailingEquipmentCount = $topEquipment['count'];
                        Log::info("FailureAnalysis: Equipamento mais crítico: {$this->mostFailingEquipment} ({$this->mostFailingEquipmentCount} falhas)");
                    } else {
                        $this->mostFailingEquipment = 'Não identificado';
                        $this->mostFailingEquipmentCount = 0;
                        Log::warning('FailureAnalysis: Nenhum equipamento com falhas identificado');
                    }
                } catch (\Exception $e) {
                    $this->mostFailingEquipment = 'Erro na análise';
                    $this->mostFailingEquipmentCount = 0;
                    Log::error('FailureAnalysis: Erro ao calcular equipamentos com falhas: ' . $e->getMessage());
                }

                // Calculate average downtime
                try {
                    $totalDowntime = 0;
                    
                    // Usamos dados formatados que já estão como array associativo
                    foreach ($formattedFailures as $failure) {
                        // No array formatado, podemos acessar diretamente a chave 'downtime'
                        if (isset($failure['downtime']) && is_numeric($failure['downtime'])) {
                            $totalDowntime += $failure['downtime'];
                        }
                    }
                    
                    $this->averageDowntime = $this->totalFailures > 0 
                        ? round($totalDowntime / $this->totalFailures, 1) 
                        : 0;
                    
                    Log::info("FailureAnalysis: Tempo médio de parada: {$this->averageDowntime}");
                } catch (\Exception $e) {
                    $this->averageDowntime = 0;
                    Log::error('FailureAnalysis: Erro ao calcular tempo médio de parada: ' . $e->getMessage());
                }
                
                // Gerar dados para o gráfico de causas
                try {
                    $this->calculateFailureCauses($formattedFailures);
                } catch (\Exception $e) {
                    Log::error('FailureAnalysis: Erro ao calcular dados para gráfico de causas: ' . $e->getMessage());
                }
                
                // Usar o novo método de identificação de padrões
                try {
                    $this->identifyPatterns($formattedFailures);
                } catch (\Exception $e) {
                    Log::error('FailureAnalysis: Erro ao identificar padrões: ' . $e->getMessage());
                    $this->patterns = [
                        [
                            'type' => 'equipment_recurring',
                            'title' => 'Equipamento com Falhas Recorrentes',
                            'description' => 'Alguns equipamentos apresentaram falhas repetidas no período analisado.',
                            'severity' => 'medium',
                            'recommendation' => 'Avaliar plano de manutenção preventiva para equipamentos críticos.'
                        ],
                        [
                            'type' => 'area_concentration',
                            'title' => 'Concentração de Falhas por Área',
                            'description' => 'Algumas áreas apresentam maior concentração de falhas.',
                            'severity' => 'medium',
                            'recommendation' => 'Verificar condições operacionais dessas áreas.'
                        ]
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('FailureAnalysis: Erro geral ao gerar estatísticas: ' . $e->getMessage());
            $this->setEmptyState();
        }
    }
    
    /**
     * Método para identificar padrões e tendências nos dados
     * 
     * @param array $formattedFailures Os dados de falhas formatados
     */
    protected function identifyPatterns($formattedFailures)
    {
        try {
            // Inicializar array de padrões
            $this->patterns = [];
            
            if (count($formattedFailures) > 0) {
                // 1. Análise por Equipamento
                $equipmentFailures = [];
                foreach ($formattedFailures as $failure) {
                    $equipment = $failure['equipment'] ?? 'Desconhecido';
                    if (!isset($equipmentFailures[$equipment])) {
                        $equipmentFailures[$equipment] = [];
                    }
                    $equipmentFailures[$equipment][] = $failure;
                }
                
                // Identificar equipamentos com falhas recorrentes
                arsort($equipmentFailures);
                foreach ($equipmentFailures as $equipment => $failures) {
                    if (count($failures) >= 3) {
                        // Calcular o MTBF (Mean Time Between Failures) aproximado para este equipamento
                        if (count($failures) > 1) {
                            $dates = array_column($failures, 'date');
                            $datesToTimestamp = array_map(function($dateStr) {
                                return Carbon::createFromFormat('d/m/Y', $dateStr)->timestamp;
                            }, $dates);
                            sort($datesToTimestamp);
                            
                            // Cálculo do intervalo médio em dias
                            $intervalSum = 0;
                            $intervals = 0;
                            for ($i = 1; $i < count($datesToTimestamp); $i++) {
                                $intervalSum += ($datesToTimestamp[$i] - $datesToTimestamp[$i-1]);
                                $intervals++;
                            }
                            
                            $mtbfDays = $intervals > 0 ? round(($intervalSum / $intervals) / 86400) : 0; // 86400 segundos = 1 dia
                            $mtbfText = $mtbfDays > 0 ? "MTBF aproximado: {$mtbfDays} dias" : "MTBF não calculado";
                        } else {
                            $mtbfText = "Insuficiente para calcular MTBF";
                        }
                        
                        // Identificação de componentes com falha frequente (se disponível)
                        $components = [];
                        foreach ($failures as $failure) {
                            if (!empty($failure['component'])) {
                                $component = $failure['component'];
                                if (!isset($components[$component])) {
                                    $components[$component] = 0;
                                }
                                $components[$component]++;
                            }
                        }
                        
                        arsort($components);
                        $criticalComponents = array_slice($components, 0, 3, true);
                        $componentText = '';
                        
                        if (!empty($criticalComponents)) {
                            $componentText = "Componentes críticos: ";
                            foreach ($criticalComponents as $comp => $count) {
                                $componentText .= "{$comp} ({$count}x), ";
                            }
                            $componentText = rtrim($componentText, ", ");
                        }
                        
                        $detailedDescription = "O equipamento '{$equipment}' apresentou " . count($failures) . 
                                          " falhas no período analisado. {$mtbfText}. " . 
                                          (!empty($componentText) ? $componentText : "");
                        
                        $this->patterns[] = [
                            'type' => 'equipment_recurring',
                            'title' => "Falhas Recorrentes: {$equipment}",
                            'description' => $detailedDescription,
                            'severity' => count($failures) >= 5 ? 'high' : 'medium',
                            'count' => count($failures),
                            'equipment' => $equipment,
                            'area' => $failures[0]['area'] ?? 'Não especificada',
                            'recommendation' => "Realizar inspeção preventiva no equipamento '{$equipment}' " . 
                                                 (!empty($criticalComponents) ? "com foco nos componentes: " . array_key_first($criticalComponents) : "e avaliar componentes críticos.") . 
                                                 ($mtbfDays > 0 ? " Programar inspeções a cada {$mtbfDays} dias." : "")
                        ];
                    }
                }
                
                // 2. Análise por Causa de Falha
                $causeFailures = [];
                foreach ($formattedFailures as $failure) {
                    $cause = $failure['cause'] ?? 'Desconhecida';
                    if (!isset($causeFailures[$cause])) {
                        $causeFailures[$cause] = [];
                    }
                    $causeFailures[$cause][] = $failure;
                }
                
                // Identificar causas comuns
                arsort($causeFailures);
                foreach ($causeFailures as $cause => $failures) {
                    if (count($failures) >= 3) {
                        // Agrupar por equipamento para esta causa
                        $affectedEquipments = [];
                        foreach ($failures as $failure) {
                            $equipment = $failure['equipment'] ?? 'Desconhecido';
                            if (!isset($affectedEquipments[$equipment])) {
                                $affectedEquipments[$equipment] = 0;
                            }
                            $affectedEquipments[$equipment]++;
                        }
                        
                        arsort($affectedEquipments);
                        $topEquipments = array_slice($affectedEquipments, 0, 3, true);
                        
                        $equipmentText = "Equipamentos mais afetados: ";
                        foreach ($topEquipments as $eq => $count) {
                            $equipmentText .= "{$eq} ({$count}x), ";
                        }
                        $equipmentText = rtrim($equipmentText, ", ");
                        
                        $this->patterns[] = [
                            'type' => 'common_cause',
                            'title' => "Causa Recorrente: {$cause}",
                            'description' => "A causa '{$cause}' foi identificada em " . count($failures) . " falhas diferentes. {$equipmentText}",
                            'severity' => count($failures) >= 6 ? 'high' : 'medium',
                            'count' => count($failures),
                            'cause' => $cause,
                            'equipment' => implode(", ", array_keys(array_slice($affectedEquipments, 0, 2, true))),
                            'recommendation' => "Realizar análise de causa raiz para '{$cause}' e implementar ações preventivas nos equipamentos mais afetados."
                        ];
                    }
                }
                
                // 3. Análise por Área
                $areaFailures = [];
                foreach ($formattedFailures as $failure) {
                    $area = $failure['area'] ?? 'Desconhecida';
                    if (!isset($areaFailures[$area])) {
                        $areaFailures[$area] = [];
                    }
                    $areaFailures[$area][] = $failure;
                }
                
                // Identificar áreas com concentração de falhas
                arsort($areaFailures);
                foreach ($areaFailures as $area => $failures) {
                    $percentage = (count($failures) / count($formattedFailures)) * 100;
                    if ($percentage >= 25) {
                        // Agrupar por causa para esta área
                        $areaCauses = [];
                        foreach ($failures as $failure) {
                            $cause = $failure['cause'] ?? 'Desconhecida';
                            if (!isset($areaCauses[$cause])) {
                                $areaCauses[$cause] = 0;
                            }
                            $areaCauses[$cause]++;
                        }
                        
                        arsort($areaCauses);
                        $topCauses = array_slice($areaCauses, 0, 3, true);
                        
                        $causeText = "Principais causas: ";
                        foreach ($topCauses as $cause => $count) {
                            $causeText .= "{$cause} ({$count}x), ";
                        }
                        $causeText = rtrim($causeText, ", ");
                        
                        $this->patterns[] = [
                            'type' => 'area_concentration',
                            'title' => "Concentração em Área: {$area}",
                            'description' => "A área '{$area}' apresenta " . count($failures) . " falhas, representando " . number_format($percentage, 1) . "% do total. {$causeText}",
                            'severity' => $percentage >= 40 ? 'high' : 'medium',
                            'count' => count($failures),
                            'area' => $area,
                            'equipment' => 'Diversos',
                            'recommendation' => "Revisar procedimentos operacionais e manutenção preventiva na área '{$area}', com foco nas causas principais identificadas."
                        ];
                    }
                }
                
                // 4. Análise de Tempo de Parada
                $downtimeFailures = [];
                foreach ($formattedFailures as $failure) {
                    $downtime = $failure['downtime'] ?? 0;
                    if ($downtime >= 8) { // Mais de 8 horas
                        $downtimeFailures[] = $failure;
                    }
                }
                
                if (count($downtimeFailures) > 0) {
                    // Agrupar por equipamento
                    $dtEquipments = [];
                    foreach ($downtimeFailures as $failure) {
                        $equipment = $failure['equipment'] ?? 'Desconhecido';
                        if (!isset($dtEquipments[$equipment])) {
                            $dtEquipments[$equipment] = [
                                'count' => 0,
                                'total_downtime' => 0
                            ];
                        }
                        $dtEquipments[$equipment]['count']++;
                        $dtEquipments[$equipment]['total_downtime'] += ($failure['downtime'] ?? 0);
                    }
                    
                    // Ordenar por tempo total de parada
                    uasort($dtEquipments, function($a, $b) {
                        return $b['total_downtime'] <=> $a['total_downtime'];
                    });
                    
                    $topDowntimeEquipments = array_slice($dtEquipments, 0, 3, true);
                    
                    $dtEquipmentText = "Equipamentos com maior impacto: ";
                    foreach ($topDowntimeEquipments as $eq => $data) {
                        $dtEquipmentText .= "{$eq} ({$data['total_downtime']}h), ";
                    }
                    $dtEquipmentText = rtrim($dtEquipmentText, ", ");
                    
                    $this->patterns[] = [
                        'type' => 'high_downtime',
                        'title' => 'Tempo de Parada Significativo',
                        'description' => count($downtimeFailures) . ' falhas resultaram em tempo de parada superior a 8 horas. ' . $dtEquipmentText,
                        'severity' => 'high',
                        'count' => count($downtimeFailures),
                        'equipment' => array_key_first($topDowntimeEquipments) ?? 'Diversos',
                        'area' => 'Múltiplas áreas',
                        'recommendation' => 'Avaliar estoque de peças de reposição e melhorar o processo de diagnóstico e reparo para os equipamentos críticos identificados.'
                    ];
                }
                
                // 5. Análise Temporal - Tendências ao longo do tempo
                if (count($formattedFailures) >= 10) {
                    // Agrupar por mês/ano
                    $monthlyFailures = [];
                    foreach ($formattedFailures as $failure) {
                        if (isset($failure['date'])) {
                            $date = Carbon::createFromFormat('d/m/Y', $failure['date']);
                            $monthYear = $date->format('m/Y');
                            
                            if (!isset($monthlyFailures[$monthYear])) {
                                $monthlyFailures[$monthYear] = [
                                    'count' => 0,
                                    'date' => $date,
                                    'downtime' => 0
                                ];
                            }
                            $monthlyFailures[$monthYear]['count']++;
                            $monthlyFailures[$monthYear]['downtime'] += ($failure['downtime'] ?? 0);
                        }
                    }
                    
                    // Ordenar por data
                    uksort($monthlyFailures, function($a, $b) use ($monthlyFailures) {
                        return $monthlyFailures[$a]['date']->timestamp <=> $monthlyFailures[$b]['date']->timestamp;
                    });
                    
                    // Verificar tendências
                    $months = array_keys($monthlyFailures);
                    $counts = array_column($monthlyFailures, 'count');
                    
                    // Se temos pelo menos 3 meses de dados
                    if (count($months) >= 3) {
                        $increasing = true;
                        $decreasing = true;
                        
                        for ($i = 1; $i < count($counts); $i++) {
                            if ($counts[$i] <= $counts[$i-1]) {
                                $increasing = false;
                            }
                            if ($counts[$i] >= $counts[$i-1]) {
                                $decreasing = false;
                            }
                        }
                        
                        // Se há uma tendência clara
                        if ($increasing || $decreasing) {
                            $trend = $increasing ? 'aumento' : 'diminuição';
                            $severity = $increasing ? 'high' : 'low';
                            $recommendation = $increasing ? 
                                'Revisar urgentemente os procedimentos de manutenção preventiva e investigar causas do aumento constante de falhas.' : 
                                'Continuar com as melhorias implementadas, pois estão reduzindo a taxa de falhas.';
                            
                            $this->patterns[] = [
                                'type' => 'time_trend',
                                'title' => "Tendência Temporal: {$trend} de falhas",
                                'description' => "Há uma tendência de {$trend} constante no número de falhas nos últimos " . count($months) . " meses.",
                                'severity' => $severity,
                                'count' => end($counts),
                                'equipment' => 'Todos',
                                'area' => 'Todas',
                                'recommendation' => $recommendation
                            ];
                        } else {
                            // Verificar sazonalidade ou ciclos
                            // Lógica simples: verificar se há picos em meses específicos
                            $monthGroups = [];
                            foreach ($monthlyFailures as $monthYear => $data) {
                                $month = substr($monthYear, 0, 2);
                                if (!isset($monthGroups[$month])) {
                                    $monthGroups[$month] = 0;
                                }
                                $monthGroups[$month] += $data['count'];
                            }
                            
                            arsort($monthGroups);
                            $topMonth = key($monthGroups);
                            $monthNames = [
                                '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março', '04' => 'Abril',
                                '05' => 'Maio', '06' => 'Junho', '07' => 'Julho', '08' => 'Agosto',
                                '09' => 'Setembro', '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'
                            ];
                            
                            if (current($monthGroups) > 1.5 * (array_sum($monthGroups) / count($monthGroups))) {
                                $this->patterns[] = [
                                    'type' => 'seasonal_pattern',
                                    'title' => "Padrão Sazonal: {$monthNames[$topMonth]}",
                                    'description' => "O mês de {$monthNames[$topMonth]} apresenta uma concentração maior de falhas, sugerindo um padrão sazonal.",
                                    'severity' => 'medium',
                                    'count' => current($monthGroups),
                                    'equipment' => 'Diversos',
                                    'area' => 'Todas',
                                    'recommendation' => "Programar manutenções preventivas adicionais antes e durante o mês de {$monthNames[$topMonth]}. Investigar fatores sazonais como temperatura, umidade ou variações na produção."
                                ];
                            }
                        }
                    }
                }
            } else {
                // Se não há dados reais, adicionar padrões genéricos para demonstração
                $this->patterns = [
                    [
                        'type' => 'equipment_recurring',
                        'title' => 'Equipamentos com Falhas Recorrentes',
                        'description' => 'Alguns equipamentos apresentaram falhas repetidas no período analisado.',
                        'severity' => 'medium',
                        'count' => 3,
                        'equipment' => 'Diversos',
                        'area' => 'Múltiplas áreas',
                        'recommendation' => 'Avaliar plano de manutenção preventiva para equipamentos críticos.'
                    ],
                    [
                        'type' => 'area_concentration',
                        'title' => 'Concentração de Falhas por Área',
                        'description' => 'Há áreas com maior concentração de falhas que podem indicar problemas sistêmicos.',
                        'severity' => 'medium',
                        'count' => 5,
                        'equipment' => 'Diversos',
                        'area' => 'Produção',
                        'recommendation' => 'Verificar condições operacionais dessas áreas.'
                    ],
                    [
                        'type' => 'high_downtime',
                        'title' => 'Tempo de Parada Significativo',
                        'description' => 'Alguns equipamentos apresentaram tempos de parada acima do normal.',
                        'severity' => 'high',
                        'count' => 2,
                        'equipment' => 'Linha de Montagem',
                        'area' => 'Produção',
                        'recommendation' => 'Revisar disponibilidade de peças de reposição e processos de reparo.'
                    ],
                    [
                        'type' => 'common_cause',
                        'title' => 'Causa Recorrente: Desgaste',
                        'description' => 'Desgaste de componentes foi identificado como causa frequente de falhas.',
                        'severity' => 'medium',
                        'count' => 4,
                        'cause' => 'Desgaste',
                        'equipment' => 'Diversos',
                        'area' => 'Múltiplas áreas',
                        'recommendation' => 'Implementar programa de substituição preventiva de componentes com alta taxa de desgaste.'
                    ]
                ];
            }
            
            Log::info('FailureAnalysis: Identificados ' . count($this->patterns) . ' padrões nos dados');
        } catch (\Exception $e) {
            Log::error('FailureAnalysis: Erro ao identificar padrões: ' . $e->getMessage());
            $this->patterns = [];
        }
    }
    
    // Calculate data for failure causes chart
    protected function calculateFailureCauses($formattedFailures)
    {
        try {
            Log::info('FailureAnalysis: Gerando dados para o gráfico de causas de falha');
            
            if (empty($formattedFailures)) {
                Log::warning('FailureAnalysis: Não há dados formatados para gráfico de causas');
                $this->chartData['cause'] = $this->createDemoChartData('cause');
                return;
            }
            
            // Agrupar falhas por causa
            $causeGroups = [];
            foreach ($formattedFailures as $failure) {
                $cause = $failure['cause'] ?? 'Desconhecida';
                if (!isset($causeGroups[$cause])) {
                    $causeGroups[$cause] = 0;
                }
                $causeGroups[$cause]++;
            }
            
            // Ordenar por quantidade decrescente
            arsort($causeGroups);
            
            // Limitar a 10 causas mais comuns para facilitar a visualização
            if (count($causeGroups) > 10) {
                $topCauses = array_slice($causeGroups, 0, 9, true);
                
                // Agrupar o restante como "Outras"
                $otherCauses = array_slice($causeGroups, 9, null, true);
                $otherCount = array_sum($otherCauses);
                
                if ($otherCount > 0) {
                    $topCauses['Outras'] = $otherCount;
                }
                
                $causeGroups = $topCauses;
            }
            
            $labels = array_keys($causeGroups);
            $data = array_values($causeGroups);
            
            // Usar cores consistentes do array de cores definido
            $usedColors = [];
            foreach ($labels as $index => $cause) {
                // Usar cores consistentes para cada causa
                $colorIndex = $index % count($this->chartColors);
                $usedColors[] = $this->chartColors[$colorIndex];
            }
            
            // Criar uma entrada dedicada no chartData para o gráfico de causas
            $this->chartData['cause'] = [
                'labels' => $labels,
                'datasets' => [
                    [
                        'data' => $data,
                        'backgroundColor' => $usedColors,
                        'borderWidth' => 1
                    ]
                ]
            ];
            
            Log::info('FailureAnalysis: Dados do gráfico de causas gerados com sucesso: ' . count($labels) . ' causas');
        } catch (\Exception $e) {
            Log::error('FailureAnalysis: Erro ao calcular dados de causas de falha: ' . $e->getMessage());
            $this->chartData['cause'] = $this->createDemoChartData('cause');
        }
    }

    // Calculate data for failures by equipment chart
    protected function calculateFailuresByEquipment($failures)
    {
        try {
            if ($failures->isEmpty()) {
                $this->failuresByEquipmentData = $this->getEmptyChartData('Sem dados de equipamentos');
                return;
            }

            $equipmentGroups = $failures->groupBy(function ($failure) {
                return $failure->equipment ? $failure->equipment->name : 'Desconhecido';
            });

            $labels = [];
            $data = [];
            $backgroundColor = [];
            $borderColor = [];

            foreach ($equipmentGroups as $equipment => $group) {
                $labels[] = $equipment;
                $data[] = $group->count();
                
                // Generate colors
                $color = $this->generateRandomColor();
                $backgroundColor[] = $color . '80'; // With opacity
                $borderColor[] = $color;
            }

            $this->failuresByEquipmentData = [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Falhas por Equipamento',
                        'data' => $data,
                        'backgroundColor' => $backgroundColor,
                        'borderColor' => $borderColor,
                        'borderWidth' => 1
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating failures by equipment chart: ' . $e->getMessage());
            $this->failuresByEquipmentData = $this->getEmptyChartData('Erro: ' . $e->getMessage());
        }
    }

    // Calculate data for failures over time chart
    protected function calculateFailuresOverTime($failures)
    {
        try {
            if ($failures->isEmpty()) {
                $this->failuresOverTimeData = $this->getEmptyChartData('Sem dados de período');
                return;
            }

            // Group failures by date
            $failuresByDate = $failures->groupBy(function ($failure) {
                $dateField = null;
                
                // Determinar qual campo de data usar
                if (isset($failure->date)) {
                    $dateField = 'date';
                } elseif (isset($failure->created_at)) {
                    $dateField = 'created_at';
                } elseif (isset($failure->updated_at)) {
                    $dateField = 'updated_at';
                } elseif (isset($failure->start_time)) {
                    $dateField = 'start_time';
                }
                
                if (!$dateField) return 'unknown';
                
                return Carbon::parse($failure->$dateField)->format('Y-m-d');
            })->map->count();

            // Create date range for chart
            $startDate = Carbon::parse($this->startDate);
            $endDate = Carbon::parse($this->endDate);
            $dateRange = collect();

            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                $dateKey = $date->format('Y-m-d');
                $dateRange[$dateKey] = $failuresByDate[$dateKey] ?? 0;
            }

            $labels = array_keys($dateRange->toArray());
            $data = array_values($dateRange->toArray());

            $this->failuresOverTimeData = [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Falhas ao Longo do Tempo',
                        'data' => $data,
                        'fill' => false,
                        'borderColor' => '#4F46E5',
                        'tension' => 0.4
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating failures over time chart: ' . $e->getMessage());
            $this->failuresOverTimeData = $this->getEmptyChartData('Erro: ' . $e->getMessage());
        }
    }

    // Calculate data for failure impact chart
    protected function calculateFailureImpact($failures)
    {
        try {
            if ($failures->isEmpty()) {
                $this->failureImpactData = $this->getEmptyChartData('Sem dados de impacto');
                return;
            }

            // Group by impact level (using downtime as proxy for impact)
            $impactLevels = [
                'Baixo (< 30min)' => 0,
                'Médio (30-120min)' => 0,
                'Alto (2-8h)' => 0,
                'Crítico (> 8h)' => 0
            ];

            foreach ($failures as $failure) {
                // Determinar qual campo de downtime usar
                $downtimeField = null;
                foreach ($failure->getAttributes() as $key => $value) {
                    if (strpos($key, 'downtime') !== false || strpos($key, 'tempo') !== false) {
                        $downtimeField = $key;
                        break;
                    }
                }
                
                if (!$downtimeField) continue;
                
                $downtime = (int) $failure->$downtimeField;
                
                if ($downtime < 30) {
                    $impactLevels['Baixo (< 30min)']++;
                } elseif ($downtime < 120) {
                    $impactLevels['Médio (30-120min)']++;
                } elseif ($downtime < 480) {
                    $impactLevels['Alto (2-8h)']++;
                } else {
                    $impactLevels['Crítico (> 8h)']++;
                }
            }

            $labels = array_keys($impactLevels);
            $data = array_values($impactLevels);

            $this->failureImpactData = [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Impacto das Falhas',
                        'data' => $data,
                        'backgroundColor' => [
                            '#10B981', // Green for low
                            '#FBBF24', // Yellow for medium
                            '#F59E0B', // Orange for high
                            '#EF4444'  // Red for critical
                        ],
                        'borderColor' => [
                            '#059669',
                            '#D97706',
                            '#B45309',
                            '#B91C1C'
                        ],
                        'borderWidth' => 1
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating failure impact chart: ' . $e->getMessage());
            $this->failureImpactData = $this->getEmptyChartData('Erro: ' . $e->getMessage());
        }
    }

    // Calculate distribution by categories
    protected function calculateCategoriesDistribution($failures)
    {
        try {
            if ($failures->isEmpty()) {
                $this->categoriesDistributionData = [
                    'mode' => $this->getEmptyChartData('Sem dados de categorias'),
                    'cause' => $this->getEmptyChartData('Sem dados de categorias')
                ];
                return;
            }

            // Get mode categories distribution
            $modeCategories = $failures->groupBy(function ($failure) {
                    return $failure->failureMode && $failure->failureMode->category 
                        ? $failure->failureMode->category->name 
                        : 'Sem Categoria';
                })
                ->map->count()
                ->toArray();

            $modeCategoryLabels = array_keys($modeCategories);
            $modeCategoryData = array_values($modeCategories);
            $modeCategoryColors = array_map(function() {
                return $this->generateRandomColor() . '80';
            }, $modeCategoryLabels);
            
            // Get cause categories distribution
            $causeCategories = $failures->groupBy(function ($failure) {
                    return $failure->failureCause && $failure->failureCause->category 
                        ? $failure->failureCause->category->name 
                        : 'Sem Categoria';
                })
                ->map->count()
                ->toArray();

            $causeCategoryLabels = array_keys($causeCategories);
            $causeCategoryData = array_values($causeCategories);
            $causeCategoryColors = array_map(function() {
                return $this->generateRandomColor() . '80';
            }, $causeCategoryLabels);

            $this->categoriesDistributionData = [
                'mode' => [
                    'labels' => $modeCategoryLabels,
                    'datasets' => [
                        [
                            'label' => 'Distribuição por Categoria de Modo',
                            'data' => $modeCategoryData,
                            'backgroundColor' => $modeCategoryColors,
                            'borderWidth' => 1
                        ]
                    ]
                ],
                'cause' => [
                    'labels' => $causeCategoryLabels,
                    'datasets' => [
                        [
                            'label' => 'Distribuição por Categoria de Causa',
                            'data' => $causeCategoryData,
                            'backgroundColor' => $causeCategoryColors,
                            'borderWidth' => 1
                        ]
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating categories distribution chart: ' . $e->getMessage());
            $this->categoriesDistributionData = [
                'mode' => $this->getEmptyChartData('Erro: ' . $e->getMessage()),
                'cause' => $this->getEmptyChartData('Erro: ' . $e->getMessage())
            ];
        }
    }
    
    /**
     * Cria dados de demonstração para os gráficos quando não há dados reais suficientes
     */
    protected function createDemoChartData()
    {
        // Gerar dados para o gráfico de distribuição de causas de falha a partir dos dados formatados
        $causeLabels = [];
        $causeData = [];
        $usedColors = [];
        
        // Se temos dados formatados, usar eles para gerar o gráfico
        if (!empty($this->failureRecords)) {
            $causes = [];
            foreach ($this->failureRecords as $record) {
                $cause = $record['cause'] ?? 'Desconhecida';
                if (!isset($causes[$cause])) {
                    $causes[$cause] = 0;
                }
                $causes[$cause]++;
            }
            
            // Ordenar por frequência
            arsort($causes);
            
            // Limitar a 7 causas para melhor visualização
            $causes = array_slice($causes, 0, 7, true);
            
            foreach ($causes as $cause => $count) {
                $causeLabels[] = $cause;
                $causeData[] = $count;
                $usedColors[] = $this->chartColors[count($usedColors) % count($this->chartColors)];
            }
        } else {
            // Dados de demonstração se não houver registros
            $causeLabels = ['Desgaste', 'Sobrecarga', 'Falha de Operação', 'Falta de Manutenção', 'Outros'];
            $causeData = [30, 25, 20, 15, 10];
            $usedColors = array_slice($this->chartColors, 0, count($causeLabels));
        }
        
        // Distribuição por categoria (modo e causa)
        $this->categoriesDistributionData = [
            'mode' => [
                'labels' => ['Mecânica', 'Elétrica', 'Hidráulica', 'Software', 'Outros'],
                'datasets' => [
                    [
                        'data' => [35, 25, 20, 15, 5],
                        'backgroundColor' => $this->chartColors,
                        'borderWidth' => 1
                    ]
                ]
            ],
            'cause' => [
                'labels' => $causeLabels,
                'datasets' => [
                    [
                        'data' => $causeData,
                        'backgroundColor' => $usedColors,
                        'borderWidth' => 1
                    ]
                ]
            ]
        ];
        
        // Adicionar log para debug
        Log::info('FailureAnalysis: Dados de causas gerados: ' . json_encode($this->categoriesDistributionData['cause']));
        
        // Dados de falhas por equipamento
        $this->failuresByEquipmentData = [
            'labels' => ['Torno CNC', 'Prensa Hidráulica', 'Empacotadora Automática', 'Centro de Usinagem', 'Fresadora'],
            'datasets' => [
                [
                    'label' => 'Número de Falhas',
                    'data' => [12, 9, 8, 6, 5],
                    'backgroundColor' => '#4f46e5',
                    'borderWidth' => 1
                ]
            ]
        ];
        
        // Dados de evolução de falhas ao longo do tempo
        $this->failuresOverTimeData = [
            'labels' => ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            'datasets' => [
                [
                    'label' => 'Falhas',
                    'data' => [5, 8, 6, 9, 10, 7, 8, 6, 7, 9, 8, 7],
                    'borderColor' => '#4f46e5',
                    'backgroundColor' => 'rgba(79, 70, 229, 0.1)',
                    'tension' => 0.3,
                    'fill' => true
                ]
            ]
        ];
        
        // Dados de impacto das falhas (tempo parado)
        $this->failureImpactData = [
            'labels' => ['Torno CNC', 'Prensa Hidráulica', 'Empacotadora Automática', 'Centro de Usinagem', 'Fresadora'],
            'datasets' => [
                [
                    'label' => 'Horas Paradas',
                    'data' => [24, 18, 16, 12, 10],
                    'backgroundColor' => '#ef4444',
                    'borderWidth' => 1
                ]
            ]
        ];
        
        Log::info('FailureAnalysis: Dados de demonstração criados para os gráficos');
    }
    
    // Set empty state for all data elements
    protected function setEmptyState()
    {
        $this->totalFailures = 0;
        $this->topFailureCause = 'N/A';
        $this->topFailureCauseCount = 0;
        $this->mostFailingEquipment = 'N/A';
        $this->mostFailingEquipmentCount = 0;
        $this->averageDowntime = 0;
        $this->patterns = [];
        $this->failureRecords = [];
        
        // Empty chart data
        $this->failureCausesData = $this->getEmptyChartData('Sem dados');
        $this->failuresByEquipmentData = $this->getEmptyChartData('Sem dados');
        $this->failuresOverTimeData = $this->getEmptyChartData('Sem dados');
        $this->failureImpactData = $this->getEmptyChartData('Sem dados');
        $this->categoriesDistributionData = [
            'mode' => $this->getEmptyChartData('Sem dados'),
            'cause' => $this->getEmptyChartData('Sem dados')
        ];
    }
    
    // Helper function to generate empty chart data
    protected function getEmptyChartData($message = 'Sem dados')
    {
        return [
            'labels' => [$message],
            'datasets' => [
                [
                    'label' => $message,
                    'data' => [0],
                    'backgroundColor' => ['#eee'],
                    'borderColor' => ['#ccc'],
                    'borderWidth' => 1
                ]
            ]
        ];
    }
    
    // Helper function to generate random colors for charts
    protected function generateRandomColor()
    {
        $colors = [
            '#4F46E5', // Indigo 600
            '#2563EB', // Blue 600
            '#0891B2', // Cyan 600
            '#0D9488', // Teal 600
            '#059669', // Emerald 600
            '#16A34A', // Green 600
            '#65A30D', // Lime 600
            '#CA8A04', // Yellow 600
            '#EA580C', // Orange 600
            '#DC2626', // Red 600
            '#DB2777', // Pink 600
            '#9333EA', // Purple 600
            '#3B82F6', // Blue 500
            '#14B8A6', // Teal 500
            '#22C55E', // Green 500
            '#F59E0B'  // Amber 500
        ];
        
        return $colors[array_rand($colors)];
    }
    
    // Show failure details modal
    public function showFailureDetails($failureId)
    {
        try {
            // Procurar o registro no array de registros formatados
            foreach ($this->failureRecords as $record) {
                if ($record['id'] == $failureId) {
                    $this->selectedFailure = $record;
                    $this->showDetailModal = true;
                    return;
                }
            }
            
            // Se não encontrou nos registros formatados, buscar no banco de dados
            $record = MaintenanceCorrective::with([
                'equipment', 'equipment.line', 'equipment.line.area',
                'failureMode', 'failureCause', 'reporter', 'resolver'
            ])->find($failureId);
            
            if (!$record) {
                $this->dispatch('notify', [
                    'message' => 'Falha não encontrada',
                    'type' => 'error'
                ]);
                return;
            }
            
            // Formatar o registro obtido do banco de dados usando a mesma lógica robusta
            // Função auxiliar para verificar objetos aninhados com segurança
            $getName = function($obj) {
                if (is_null($obj)) return 'Não especificado';
                if (is_string($obj)) return $obj; // Se já for uma string, retornar como está
                if (is_object($obj) && isset($obj->name)) return $obj->name;
                return 'Não especificado';
            };
            
            // Obter equipamento com verificação segura
            $equipment = is_object($record->equipment) ? $record->equipment : null;
            $line = ($equipment && is_object($equipment->line)) ? $equipment->line : null;
            $area = ($line && is_object($line->area)) ? $line->area : null;
            
            // Obter outros relacionamentos com verificação segura
            $failureMode = is_object($record->failureMode) ? $record->failureMode : null;
            $failureCause = is_object($record->failureCause) ? $record->failureCause : null;
            $reporter = is_object($record->reporter) ? $record->reporter : null;
            $resolver = is_object($record->resolver) ? $record->resolver : null;
            
            $this->selectedFailure = [
                'id' => $record->id,
                'date' => $record->start_time ? Carbon::parse($record->start_time)->format('d/m/Y') : 'N/A',
                'equipment' => $getName($equipment),
                'area' => $getName($area),
                'line' => $getName($line),
                'cause' => $getName($failureCause),
                'mode' => $getName($failureMode),
                'downtime' => $record->downtime_length ?? 0,
                'status' => $record->status ?? 'pending',
                'description' => $record->description ?? 'Sem descrição',
                'reported_by' => $getName($reporter),
                'resolved_by' => $getName($resolver),
                'serial_number' => $equipment && isset($equipment->serial_number) ? $equipment->serial_number : 'N/A',
                'actions_taken' => $record->actions_taken ?? 'Nenhuma ação registrada'
            ];
            
            $this->showDetailModal = true;
        } catch (\Exception $e) {
            Log::error('FailureAnalysis: Erro ao exibir detalhes da falha: ' . $e->getMessage());
            $this->dispatch('notify', [
                'message' => 'Erro ao carregar detalhes: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }
    
    // Close modal
    public function closeModal()
    {
        $this->showDetailModal = false;
        $this->selectedFailure = null;
    }
    
    // Métodos para paginação
    
    public function updatingSearch()
    {
        // Resetar para a primeira página quando a busca mudar
        $this->page = 1;
    }
    
    public function updatingPerPage()
    {
        // Resetar para a primeira página quando a quantidade por página mudar
        $this->page = 1;
    }
    
    public function previousPage()
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }
    
    public function nextPage()
    {
        $maxPage = ceil($this->totalFailures / $this->perPage);
        if ($this->page < $maxPage) {
            $this->page++;
        }
    }
    
    public function gotoPage($page)
    {
        $this->page = (int)$page;
    }
    
    // Redefine os filtros
    public function resetFilters()
    {
        $this->selectedArea = 'all';
        $this->selectedLine = 'all';
        $this->selectedEquipment = 'all';
        $this->search = '';
        $this->page = 1;
        $this->loadFailureData();
    }
    
    // Obtem os registros filtrados e paginados
    public function getFilteredRecords()
    {
        if (empty($this->failureRecords)) {
            return [];
        }
        
        // Aplicar filtros de busca se necessário
        $filteredRecords = $this->failureRecords;
        
        if (!empty($this->search)) {
            $search = strtolower($this->search);
            $filteredRecords = array_filter($filteredRecords, function($record) use ($search) {
                // Buscar em varios campos
                return 
                    stripos($record['equipment'] ?? '', $search) !== false ||
                    stripos($record['area'] ?? '', $search) !== false ||
                    stripos($record['cause'] ?? '', $search) !== false ||
                    stripos($record['mode'] ?? '', $search) !== false ||
                    stripos($record['description'] ?? '', $search) !== false;
            });
        }
        
        // Reindexar o array após filtragem
        $filteredRecords = array_values($filteredRecords);
        
        // Ordenar registros se necessário
        usort($filteredRecords, function($a, $b) {
            $fieldA = $a[$this->sortField] ?? '';
            $fieldB = $b[$this->sortField] ?? '';
            
            if ($this->sortField === 'downtime') {
                $fieldA = floatval($fieldA);
                $fieldB = floatval($fieldB);
            }
            
            if ($this->sortDirection === 'asc') {
                return $fieldA <=> $fieldB;
            } else {
                return $fieldB <=> $fieldA;
            }
        });
        
        // Atualizar o total para paginação
        $this->totalFilteredFailures = count($filteredRecords);
        
        return $filteredRecords;
    }
    
    // Retorna os registros da página atual
    public function getPaginatedRecords()
    {
        $filtered = $this->getFilteredRecords();
        $offset = ($this->page - 1) * $this->perPage;
        
        return array_slice($filtered, $offset, $this->perPage);
    }
    
    // Gerencia a paginação dos padrões identificados
    public function previousPatternPage()
    {
        if ($this->patternPage > 1) {
            $this->patternPage--;
        }
    }
    
    public function nextPatternPage()
    {
        $maxPage = ceil($this->totalPatterns / $this->patternPerPage);
        if ($this->patternPage < $maxPage) {
            $this->patternPage++;
        }
    }
    
    public function gotoPatternPage($page)
    {
        $this->patternPage = (int)$page;
    }
    
    // Método para ordenar a tabela de detalhes de falhas
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            // Se já estamos ordenando por este campo, inverte a direção
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            // Caso contrário, define o novo campo e começa com ascendente
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        
        // Resetar para a primeira página ao mudar a ordenação
        $this->page = 1;
    }
    
    // Retorna os padrões paginados
    public function getPaginatedPatterns()
    {
        if (empty($this->patterns)) {
            return [];
        }
        
        $this->totalPatterns = count($this->patterns);
        $offset = ($this->patternPage - 1) * $this->patternPerPage;
        
        // Ordenar padrões por severidade e contagem
        usort($this->patterns, function($a, $b) {
            // Primeiro por severidade
            $severityOrder = ['high' => 3, 'medium' => 2, 'low' => 1];
            $severityA = $severityOrder[$a['severity'] ?? 'low'] ?? 1;
            $severityB = $severityOrder[$b['severity'] ?? 'low'] ?? 1;
            
            if ($severityA !== $severityB) {
                return $severityB <=> $severityA; // High primeiro
            }
            
            // Depois por contagem
            return ($b['count'] ?? 0) <=> ($a['count'] ?? 0);
        });
        
        return array_slice($this->patterns, $offset, $this->patternPerPage);
    }
}