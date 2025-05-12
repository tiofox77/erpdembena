<?php

namespace App\Livewire\Mrp;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Mrp\ProductionSchedule;
use App\Models\Mrp\ProductionOrder;
use App\Models\Mrp\BomHeader;
use App\Models\Mrp\BomDetail;
use App\Models\SupplyChain\Product;
use App\Models\SupplyChain\InventoryLocation as Location;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Livewire\Mrp\CompleteProductionTrait;

class ProductionScheduling extends Component
{
    use WithPagination;
    use CompleteProductionTrait;
    
    // Propriedades do componente
    public $search = '';
    public $sortField = 'start_date';
    public $sortDirection = 'asc';
    public $perPage = 10;
    public $currentTab = 'list';
    public $viewType = 'table'; // Tipo de visualização (tabela ou calendário)
    
    // Verificação de componentes
    public $componentAvailability = [];
    public $showComponentWarning = false;
    public $insufficientComponents = [];
    public $maxQuantityPossible = 0;
    
    // Propriedades para modal
    public $showModal = false;
    public $showDeleteModal = false;
    public $showOrdersModal = false;
    public $showViewModal = false; // Modal de visualização
    public $editMode = false;
    public $scheduleId = null;
    public $scheduleToDelete = null; // Programacao a ser excluída
    public $confirmDelete = false; // Confirmação de exclusão
    
    // Nova ordem de produção
    public $newOrder = [
        'quantity' => '',
        'due_date' => '',
        'description' => '',
        'status' => 'pending'
    ];
    
    protected $listeners = [
        'startProduction' => 'startProduction',
        'completeProduction' => 'completeProduction',
        'updateWipInventory' => 'updateWipInventory',
        'updated:schedule.product_id' => 'checkComponentAvailability',
        'updated:schedule.planned_quantity' => 'checkComponentAvailability'
    ];
    
    /**
     * Método para verificar a disponibilidade de componentes com base na BOM do produto
     * É chamado automaticamente quando o produto ou a quantidade planejada é alterada
     */
    public function checkComponentAvailability()
    {
        // Inicializar variáveis com valores padrão
        $this->showComponentWarning = false;
        $this->insufficientComponents = [];
        $this->maxQuantityPossible = 0;
        
        // Define um tempo limite para prevenir loops infinitos
        $startTime = microtime(true);
        $timeLimit = 2.0; // limite de 2 segundos para execução
        
        // Registrar o início da verificação para debug
        \Illuminate\Support\Facades\Log::info('Iniciando verificação de componentes', [
            'product_id' => $this->schedule['product_id'] ?? 'nenhum',
            'planned_quantity' => $this->schedule['planned_quantity'] ?? 'nenhuma'
        ]);
        
        // Se não há produto selecionado, não há o que verificar
        if (empty($this->schedule['product_id'])) {
            return;
        }
        
        // Garantir que a quantidade seja numérica, mesmo que seja zero
        $planned_quantity = isset($this->schedule['planned_quantity']) && is_numeric($this->schedule['planned_quantity']) 
            ? (float)$this->schedule['planned_quantity'] 
            : 0;
            
        // Verifica se a quantidade é muito grande (acima de 1 milhão)
        if ($planned_quantity > 1000000) {
            $this->showComponentWarning = true;
            $this->insufficientComponents = [[
                'name' => __('messages.excessive_quantity'),
                'sku' => 'N/A',
                'required' => $planned_quantity,
                'available' => 'N/A',
                'missing' => 'N/A'
            ]];
            \Illuminate\Support\Facades\Log::warning('Verificação de componentes cancelada: quantidade muito grande', [
                'planned_quantity' => $planned_quantity
            ]);
            return;
        }
        
        // Primeiro, verificar se existe alguma BOM para o produto, independente do status
        $anyBom = BomHeader::where('product_id', $this->schedule['product_id'])->first();
        
        if (!$anyBom) {
            // Produto não tem BOM cadastrada, não há como verificar componentes
            return;
        }
        
        // Verificar se a BOM encontrada está com status ativo
        if ($anyBom->status !== 'active') {
            // BOM existe mas não está ativa
            $this->showComponentWarning = true;
            $this->insufficientComponents = [[
                'name' => __('messages.invalid_bom_status'),
                'sku' => $anyBom->bom_number,
                'required' => __('messages.bom_status_must_be_active'),
                'available' => $anyBom->status,
                'missing' => __('messages.activate_bom_to_continue')
            ]];
            return;
        }
        
        // Usar a BOM ativa encontrada
        $bomHeader = $anyBom;
        
        // Buscar todos os componentes da BOM
        $components = BomDetail::where('bom_header_id', $bomHeader->id)
            ->with(['component' => function($query) {
                $query->with('inventoryItems');
            }])
            ->get();
            
        if ($components->isEmpty()) {
            // BOM existe mas não tem componentes cadastrados
            return;
        }
        
        // Usar a variável planned_quantity já definida acima
        $this->componentAvailability = [];
        $insufficientFound = false;
        $maxPossible = PHP_INT_MAX;
        
        // Registrar a quantidade para debug
        \Illuminate\Support\Facades\Log::info('Verificando quantidade:', [
            'planned_quantity' => $planned_quantity,
            'product_id' => $this->schedule['product_id']
        ]);
        
        foreach ($components as $bomComponent) {
        // Verificar se o tempo limite foi atingido
        if ((microtime(true) - $startTime) > $timeLimit) {
            \Illuminate\Support\Facades\Log::warning('Verificação de componentes interrompida: tempo limite excedido', [
                'product_id' => $this->schedule['product_id'],
                'planned_quantity' => $planned_quantity,
                'processed_components' => count($this->componentAvailability)
            ]);
            
            $this->showComponentWarning = true;
            $this->insufficientComponents[] = [
                'name' => __('messages.verification_time_exceeded'),
                'sku' => 'N/A',
                'required' => 'N/A',
                'available' => 'N/A',
                'missing' => __('messages.too_many_components_or_quantity')
            ];
            
            // Atualizar flags e valores para usar na UI
            return;
        }
        
        // Pular componentes inválidos
        if (!$bomComponent->component) {
            continue;
        }
        
        // Calcular quantidade necessária do componente
        $required_quantity = $bomComponent->quantity * $planned_quantity;
        
        // Calcular quantidade disponível (somar todos os locais de inventário)
        $available_quantity = 0;
        if ($bomComponent->component->inventoryItems) {
            $available_quantity = $bomComponent->component->inventoryItems->sum('quantity_on_hand');
        }
        
        // Verificar se há quantidade suficiente
        $sufficient = $available_quantity >= $required_quantity;
        
        // Calcular quantas unidades do produto final podem ser produzidas com esse componente
        $maxProducible = $bomComponent->quantity > 0 
            ? floor($available_quantity / $bomComponent->quantity) 
            : 0;
            
        // Atualizar a quantidade máxima possível (o mínimo entre todos os componentes)
        if ($maxProducible < $maxPossible) {
            $maxPossible = $maxProducible;
        }
        
        // Armazenar informações sobre esse componente
        $this->componentAvailability[] = [
            'component_id' => $bomComponent->component_id,
            'name' => $bomComponent->component->name,
            'sku' => $bomComponent->component->sku,
            'required_quantity' => $required_quantity,
            'available_quantity' => $available_quantity,
            'sufficient' => $sufficient,
            'max_producible' => $maxProducible
        ];
        
        // Se não há quantidade suficiente, registrar para alerta
        if (!$sufficient) {
            $insufficientFound = true;
            $this->insufficientComponents[] = [
                'name' => $bomComponent->component->name,
                'sku' => $bomComponent->component->sku,
                'required' => $required_quantity,
                'available' => $available_quantity,
                'missing' => $required_quantity - $available_quantity
            ];
        }
    }
        
        // Atualizar flags e valores para usar na UI
        $this->showComponentWarning = $insufficientFound;
        $this->maxQuantityPossible = $maxPossible;
    }
    
    // Propriedades do formulário
    public $schedule = [
        'product_id' => '',
        'schedule_number' => '',
        'start_date' => '',
        'start_time' => '08:00',
        'end_date' => '',
        'end_time' => '17:00',
        'planned_quantity' => '',
        'actual_quantity' => 0,
        'actual_start_time' => null,
        'actual_end_time' => null,
        'is_delayed' => false,
        'delay_reason' => '',
        'status' => 'draft',
        'priority' => 'medium',
        'responsible' => '',
        'location_id' => '',  // ID da localização de inventário da supply chain
        'working_hours_per_day' => 8, // Horas de trabalho por dia
        'hourly_production_rate' => 0, // Taxa de produção por hora
        'working_days' => [ // Dias de trabalho na semana
            'mon' => true,
            'tue' => true,
            'wed' => true,
            'thu' => true,
            'fri' => true,
            'sat' => false,
            'sun' => false
        ],
        'setup_time' => 30, // Tempo de setup em minutos
        'cleanup_time' => 15, // Tempo de limpeza em minutos
        'notes' => ''
    ];
    
    // Planos diários de produção
    public $dailyPlans = [];
    public $showDailyPlansModal = false;
    public $editingDailyPlan = null;
    public $viewingDailyPlans = false;
    
    // Propriedades de filtro
    public $statusFilter = null;
    public $priorityFilter = null;
    public $dateFilter = null;
    public $productFilter = null;
    
    // Propriedades do calendário
    public $calendarView = 'month';
    public $calendarDate = null;
    public $calendarEvents = [];
    public $calendarTitle = '';
    public $calendarDayNames = [];
    public $calendarWeeks = [];
    public $calendarDays = [];
    
    /**
     * Mount component
     */
    public function mount()
    {
        $this->calendarDate = date('Y-m-d');
        $this->updateCalendarTitle();
        $this->setupCalendarDayNames();
        $this->loadCalendarEvents();
    }
    
    /**
     * Regras de validação
     */
    protected function rules()
    {
        return [
            'schedule.product_id' => 'required|exists:sc_products,id',
            'schedule.schedule_number' => [
                'required',
                'string',
                'max:50',
                $this->editMode
                    ? Rule::unique('mrp_production_schedules', 'schedule_number')->ignore($this->scheduleId)
                    : Rule::unique('mrp_production_schedules', 'schedule_number'),
            ],
            'schedule.start_date' => 'required|date',
            'schedule.start_time' => 'required',
            'schedule.end_date' => 'required|date|after_or_equal:schedule.start_date',
            'schedule.end_time' => 'required',
            'schedule.planned_quantity' => 'required|numeric|min:0.001',
            'schedule.actual_quantity' => 'nullable|numeric|min:0',
            'schedule.actual_start_time' => 'nullable|date',
            'schedule.actual_end_time' => 'nullable|date',
            'schedule.is_delayed' => 'boolean',
            'schedule.delay_reason' => 'nullable|string|max:1000',
            'schedule.status' => ['required', Rule::in(['draft', 'confirmed', 'in_progress', 'completed', 'cancelled'])],
            'schedule.priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'schedule.responsible' => 'nullable|string|max:100',
            'schedule.working_hours_per_day' => 'required|numeric|min:0.5|max:24',
            'schedule.hourly_production_rate' => 'required|numeric|min:0.01',
            'schedule.working_days' => 'required|array',
            'schedule.working_days.mon' => 'boolean',
            'schedule.working_days.tue' => 'boolean',
            'schedule.working_days.wed' => 'boolean',
            'schedule.working_days.thu' => 'boolean',
            'schedule.working_days.fri' => 'boolean',
            'schedule.working_days.sat' => 'boolean',
            'schedule.working_days.sun' => 'boolean',
            'schedule.setup_time' => 'nullable|numeric|min:0',
            'schedule.cleanup_time' => 'nullable|numeric|min:0',
            'schedule.location_id' => 'required|exists:sc_inventory_locations,id',
            'schedule.notes' => 'nullable|string|max:1000',
        ];
    }
    
    /**
     * Resetar paginação quando a busca mudar
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    /**
     * Ordenar por coluna
     */
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        
        $this->sortField = $field;
    }
    
    /**
     * Resetar filtros
     */
    public function resetFilters()
    {
        $this->search = '';
        $this->statusFilter = null;
        $this->priorityFilter = null;
        $this->dateFilter = null;
        $this->productFilter = null;
        $this->resetPage();
    }
    
    /**
     * Alterar a visualização da aba
     */
    public function setTab($tab)
    {
        $this->currentTab = $tab;
        
        if ($tab === 'calendar') {
            // Garantir que os dias da semana estão configurados
            $this->setupCalendarDayNames();
            
            // Verificar se a data do calendário está definida
            if (!$this->calendarDate) {
                $this->calendarDate = date('Y-m-d');
            }
            
            // Atualizar título do calendário
            $this->updateCalendarTitle();
            
            // Inicializar o calendário
            $this->loadCalendarEvents();
        }
    }
    
    /**
     * Alterar a visualização do calendário
     */
    public function setCalendarView($view)
    {
        $this->calendarView = $view;
        $this->loadCalendarEvents();
    }
    
    /**
     * Avançar para o próximo mês no calendário
     */
    public function nextMonth()
    {
        $date = date('Y-m-d', strtotime($this->calendarDate . ' +1 month'));
        $this->calendarDate = $date;
        $this->updateCalendarTitle();
        $this->loadCalendarEvents();
    }
    
    /**
     * Voltar para o mês anterior no calendário
     */
    public function previousMonth()
    {
        $date = date('Y-m-d', strtotime($this->calendarDate . ' -1 month'));
        $this->calendarDate = $date;
        $this->updateCalendarTitle();
        $this->loadCalendarEvents();
    }
    
    /**
     * Atualizar o título do calendário baseado na data atual
     */
    private function updateCalendarTitle()
    {
        setlocale(LC_TIME, 'pt_BR.utf8', 'Portuguese_Brazil');
        $month = ucfirst(date('F Y', strtotime($this->calendarDate)));
        $this->calendarTitle = $month;
    }
    
    /**
     * Configurar os nomes dos dias da semana para o calendário
     */
    private function setupCalendarDayNames()
    {
        $this->calendarDayNames = [
            __('messages.sunday_short'),
            __('messages.monday_short'),
            __('messages.tuesday_short'),
            __('messages.wednesday_short'),
            __('messages.thursday_short'),
            __('messages.friday_short'),
            __('messages.saturday_short')
        ];
    }
    
    /**
     * Carregar eventos para o calendário
     */
    public function loadCalendarEvents()
    {
        // Atualizar o título do calendário
        $this->updateCalendarTitle();
        
        // Garantir que os dias da semana estão configurados
        if (empty($this->calendarDayNames)) {
            $this->setupCalendarDayNames();
        }
        
        // Definir datas para o calendário
        $year = date('Y', strtotime($this->calendarDate));
        $month = date('m', strtotime($this->calendarDate));
        
        // Primeiro dia do mês
        $firstDayOfMonth = $year . '-' . $month . '-01';
        // Último dia do mês
        $lastDayOfMonth = date('Y-m-t', strtotime($firstDayOfMonth));
        
        // Dia da semana do primeiro dia (0 = domingo, 6 = sábado)
        $firstDayOfWeek = (int)date('w', strtotime($firstDayOfMonth));
        
        // Gerar dias para o calendário
        $this->calendarDays = [];
        $this->calendarWeeks = [];
        
        // Montar a visualização do calendário
        $currentMonth = date('Y-m', strtotime($this->calendarDate));
        $firstDayOfMonth = date('Y-m-01', strtotime($currentMonth));
        $lastDayOfMonth = date('Y-m-t', strtotime($currentMonth));
        
        // Adicionar dias do mês anterior para completar a primeira semana
        for ($i = 0; $i < $firstDayOfWeek; $i++) {
            $date = date('Y-m-d', strtotime($firstDayOfMonth . ' -' . ($firstDayOfWeek - $i) . ' days'));
            $day = date('j', strtotime($date));
            
            $this->calendarDays[] = [
                'date' => $date,
                'day' => $day,
                'isCurrentMonth' => false,
                'isToday' => $date === date('Y-m-d'),
                'events' => []
            ];
        }
        
        // Adicionar dias do mês atual
        $daysInMonth = (int)date('t', strtotime($firstDayOfMonth));
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $year . '-' . $month . '-' . sprintf('%02d', $day);
            
            $this->calendarDays[] = [
                'date' => $date,
                'day' => $day,
                'isCurrentMonth' => true,
                'isToday' => $date === date('Y-m-d'),
                'events' => []
            ];
        }
        
        // Adicionar dias do próximo mês para completar a última semana (6 semanas)
        $remainingDays = 42 - count($this->calendarDays);
        
        for ($day = 1; $day <= $remainingDays; $day++) {
            $date = date('Y-m-d', strtotime($lastDayOfMonth . ' +' . $day . ' days'));
            
            $this->calendarDays[] = [
                'date' => $date,
                'day' => $day,
                'isCurrentMonth' => false,
                'isToday' => $date === date('Y-m-d'),
                'events' => []
            ];
        }
        
        // Organizar dias em semanas
        $this->calendarWeeks = array_chunk($this->calendarDays, 7);
        
        // Buscar eventos entre as datas do calendário
        $start = $this->calendarDays[0]['date'];
        $end = end($this->calendarDays)['date'];
        
        try {
            // Log detalhado de diagnóstico para o intervalo de datas
            \Illuminate\Support\Facades\Log::info('Buscando agendamentos para o calendário', [
                'data_inicio' => $start,
                'data_fim' => $end,
                'mes_atual' => date('Y-m', strtotime($this->calendarDate))
            ]);
            
            // Buscar TODOS os agendamentos para diagnosticar
            $allSchedules = ProductionSchedule::count();
            \Illuminate\Support\Facades\Log::info('Total de agendamentos no banco de dados', [
                'total' => $allSchedules
            ]);
            
            // Verificar estrutura das tabelas envolvidas
            try {
                $tableInfo = \DB::select('DESCRIBE mrp_production_schedules');
                \Illuminate\Support\Facades\Log::info('Estrutura da tabela mrp_production_schedules', [
                    'colunas' => collect($tableInfo)->pluck('Field')->toArray()
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Erro ao verificar estrutura da tabela', [
                    'erro' => $e->getMessage()
                ]);
            }
            
            // Consulta normal refinada para mais clareza
            $schedules = ProductionSchedule::with(['product'])
                ->where(function($query) use ($start, $end) {
                    $query->where(function($q) use ($start, $end) {
                        // Eventos que começam no período
                        $q->where('start_date', '>=', $start)
                          ->where('start_date', '<=', $end);
                    })->orWhere(function($q) use ($start, $end) {
                        // Eventos que terminam no período
                        $q->where('end_date', '>=', $start)
                          ->where('end_date', '<=', $end);
                    })->orWhere(function($q) use ($start, $end) {
                        // Eventos que cruzam o período
                        $q->where('start_date', '<=', $start)
                          ->where('end_date', '>=', $end);
                    });
                })
                ->get();
                
            // Log do resultado da consulta
            \Illuminate\Support\Facades\Log::info('Agendamentos encontrados para o calendário', [
                'quantidade' => $schedules->count(),
                'agendamentos' => $schedules->map(function($schedule) {
                    return [
                        'id' => $schedule->id,
                        'produto' => $schedule->product ? $schedule->product->name : 'Produto não definido',
                        'start_date' => $schedule->start_date,
                        'end_date' => $schedule->end_date,
                        'status' => $schedule->status
                    ];
                })
            ]);
                
            // Associar eventos aos dias do calendário com log detalhado
            \Illuminate\Support\Facades\Log::info('Iniciando associação de eventos aos dias');
            
            foreach ($schedules as $schedule) {
                // Garantir que as datas estejam no formato correto
                $scheduleStartDate = is_string($schedule->start_date) ? $schedule->start_date : $schedule->start_date->format('Y-m-d');
                $scheduleEndDate = is_string($schedule->end_date) ? $schedule->end_date : $schedule->end_date->format('Y-m-d');
                
                // Normalizar as datas removendo o componente de hora
                $normalizedStartDate = date('Y-m-d', strtotime($scheduleStartDate));
                $normalizedEndDate = date('Y-m-d', strtotime($scheduleEndDate));
                $normalizedStart = date('Y-m-d', strtotime($start));
                $normalizedEnd = date('Y-m-d', strtotime($end));
                
                \Illuminate\Support\Facades\Log::info('Processando agendamento', [
                    'id' => $schedule->id,
                    'produto' => $schedule->product ? $schedule->product->name : 'Produto não definido',
                    'data_inicio_original' => $schedule->start_date,
                    'data_fim_original' => $schedule->end_date,
                    'data_inicio_normalizada' => $normalizedStartDate,
                    'data_fim_normalizada' => $normalizedEndDate,
                    'intervalo_calendário' => [$normalizedStart, $normalizedEnd]
                ]);
                
                $startDate = max(strtotime($normalizedStartDate), strtotime($normalizedStart));
                $endDate = min(strtotime($normalizedEndDate), strtotime($normalizedEnd));
                $currentDate = $startDate;
                
                while ($currentDate <= $endDate) {
                    $formattedDate = date('Y-m-d', $currentDate);
                    
                    $event = [
                        'id' => $schedule->id,
                        'title' => $schedule->product ? $schedule->product->name : 'Produto não definido',
                        'status' => $schedule->status,
                        'priority' => $schedule->priority ?? 'medium'
                    ];
                    
                    $found = false;
                    // Adicionar evento à data correspondente no calendário
                    foreach ($this->calendarDays as &$day) {
                        if ($day['date'] === $formattedDate) {
                            if (!isset($day['events'])) {
                                $day['events'] = [];
                            }
                            $day['events'][] = $event;
                            $found = true;
                            \Illuminate\Support\Facades\Log::info('Evento adicionado ao dia', [
                                'dia' => $formattedDate,
                                'evento_id' => $event['id'],
                                'titulo' => $event['title']
                            ]);
                            break;
                        }
                    }
                    
                    if (!$found) {
                        \Illuminate\Support\Facades\Log::warning('Dia não encontrado no calendário', [
                            'data_procurada' => $formattedDate,
                            'dias_disponíveis' => collect($this->calendarDays)->pluck('date')->toArray()
                        ]);
                    }
                    
                    $currentDate = strtotime('+1 day', $currentDate);
                }
            }
            
            // IMPORTANTE: Transferir eventos de calendarDays para calendarWeeks
            // que é a estrutura usada no template
            \Illuminate\Support\Facades\Log::info('Transferindo eventos para calendarWeeks');
            
            // Para cada semana no calendarWeeks
            foreach ($this->calendarWeeks as $weekIndex => &$week) {
                // Para cada dia na semana
                foreach ($week as $dayIndex => &$day) {
                    $dateToMatch = $day['date'];
                    
                    // Encontrar o dia correspondente em calendarDays
                    foreach ($this->calendarDays as $calendarDay) {
                        if ($calendarDay['date'] === $dateToMatch) {
                            // Transferir os eventos encontrados
                            if (isset($calendarDay['events'])) {
                                $day['events'] = $calendarDay['events'];
                                \Illuminate\Support\Facades\Log::info('Eventos transferidos para calendarWeeks', [
                                    'data' => $dateToMatch,
                                    'quantidade' => count($calendarDay['events']),
                                    'semana' => $weekIndex,
                                    'dia' => $dayIndex
                                ]);
                            }
                            break;
                        }
                    }
                }
            }
            
            // Ordenar eventos por prioridade
            foreach ($this->calendarDays as &$day) {
                if (isset($day['events']) && !empty($day['events'])) {
                    usort($day['events'], function($a, $b) {
                        $priorityOrder = ['urgent' => 1, 'high' => 2, 'medium' => 3, 'low' => 4];
                        return ($priorityOrder[$a['priority']] ?? 5) <=> ($priorityOrder[$b['priority']] ?? 5);
                    });
                }
            }
        } catch (\Exception $e) {
            // Em caso de erro, pelo menos garantir que o calendário seja exibido
            $this->dispatch('notify',
                type: 'error',
                title: __('messages.calendar_load_error'),
                message: __('messages.calendar_events_load_error', ['error' => $e->getMessage()])
            );
        }
    }
    
    /**
     * Gerar número de programação
     */
    public function generateScheduleNumber()
    {
        if (empty($this->schedule['product_id'])) {
            return;
        }
        
        $product = Product::find($this->schedule['product_id']);
        if (!$product) {
            return;
        }
        
        // Gerar número sequencial
        $lastSchedule = ProductionSchedule::orderBy('id', 'desc')->first();
        $lastId = $lastSchedule ? $lastSchedule->id + 1 : 1;
        
        // Formato: SCH-AAAAMMDD-XXXX (XXXX = número sequencial)
        $this->schedule['schedule_number'] = 'SCH-' . date('Ymd') . '-' . str_pad($lastId, 4, '0', STR_PAD_LEFT);
        
        // Definir datas padrão
        if (empty($this->schedule['start_date'])) {
            $this->schedule['start_date'] = date('Y-m-d');
        }
        
        if (empty($this->schedule['end_date'])) {
            $this->schedule['end_date'] = date('Y-m-d', strtotime('+7 days'));
        }
    }
    
    /**
     * Abrir modal para criar nova programação
     */
    public function create()
    {
        try {
            \Illuminate\Support\Facades\Log::info('create method called');
            $this->resetValidation();
            $this->reset('schedule');
            $schedule = [
                'product_id' => '',
                'schedule_number' => $this->generateScheduleNumber(),
                'start_date' => date('Y-m-d'),
                'end_date' => date('Y-m-d', strtotime('+7 days')),
                'planned_quantity' => '',
                'status' => 'draft',
                'priority' => 'medium',
                'responsible' => '',
                'location_id' => '',
                'notes' => ''
            ];
            $this->schedule = $schedule;
            $this->editMode = false;
            $this->showModal = true;
            
            \Illuminate\Support\Facades\Log::info('Modal should be visible now', [
                'showModal' => $this->showModal,
                'schedule' => $this->schedule
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in create method', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Alias para o método create - usado pelos botões na interface
     */
    public function openCreateModal()
    {
        // Add debug log
        \Illuminate\Support\Facades\Log::info('openCreateModal called');
        
        try {
            // Verificar se existem produtos e localizações
            $firstProduct = \App\Models\SupplyChain\Product::first();
            $firstLocation = \App\Models\SupplyChain\InventoryLocation::first();
            
            \Illuminate\Support\Facades\Log::info('Valores para inicialização', [
                'produto' => $firstProduct ? $firstProduct->id : 'Nenhum produto encontrado',
                'localização' => $firstLocation ? $firstLocation->id : 'Nenhuma localização encontrada'
            ]);
            
            $this->resetValidation();
            $this->reset('schedule');
            
            // Inicializa primeiro com o product_id para que o generateScheduleNumber funcione
            $this->schedule = [
                'product_id' => $firstProduct ? $firstProduct->id : '',
            ];
            
            // Agora geramos o número de programação após definir o product_id
            $this->generateScheduleNumber();
            \Illuminate\Support\Facades\Log::info('Número de programação gerado', ['numero' => $this->schedule['schedule_number'] ?? 'Não gerado']);
            
            // Completa a inicialização com os demais valores
            $this->schedule = array_merge($this->schedule, [
                'start_date' => date('Y-m-d'),
                'end_date' => date('Y-m-d', strtotime('+7 days')),
                'planned_quantity' => '1', // Valor padrão
                'status' => 'draft',
                'priority' => 'medium',
                'location_id' => $firstLocation ? $firstLocation->id : '',
                'responsible' => '',
                'notes' => ''
            ]);
            
            // Garantir que o schedule_number existe
            if (empty($this->schedule['schedule_number'])) {
                // Gerar manualmente se o método normal falhou
                $lastSchedule = \App\Models\Mrp\ProductionSchedule::orderBy('id', 'desc')->first();
                $lastId = $lastSchedule ? $lastSchedule->id + 1 : 1;
                $this->schedule['schedule_number'] = 'SCH-' . date('Ymd') . '-' . str_pad($lastId, 4, '0', STR_PAD_LEFT);
                
                \Illuminate\Support\Facades\Log::info('Número de programação gerado manualmente', 
                    ['numero' => $this->schedule['schedule_number']]);
            }
            
            $this->editMode = false;
            $this->showModal = true;
            
            \Illuminate\Support\Facades\Log::info('Modal iniciado com dados', [
                'showModal' => $this->showModal,
                'editMode' => $this->editMode,
                'schedule' => $this->schedule
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in openCreateModal', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

/**
 * Método para tratar erros do calendário
 */
public function handleCalendarError(\Exception $e)
{
    // Em caso de erro, pelo menos garantir que o calendário seja exibido
    $this->dispatch('notify',
        type: 'error',
        title: __('messages.calendar_load_error'),
        message: __('messages.calendar_events_load_error', ['error' => $e->getMessage()])
    );
}

// Método removido - duplicado

// Método removido - duplicado

// Método removido - duplicado

/**
 * Abrir modal para criar agendamento em uma data específica
 * 
 * @param string $date Data no formato Y-m-d
 */
public function openCreateModalForDate($date)
{
    // Add debug log
    \Illuminate\Support\Facades\Log::info('openCreateModalForDate called', ['date' => $date]);
        
    try {
        // Verificar se existem produtos e localizações
        $firstProduct = \App\Models\SupplyChain\Product::first();
        $firstLocation = \App\Models\SupplyChain\InventoryLocation::first();
            
        \Illuminate\Support\Facades\Log::info('Valores para inicialização no calendário', [
            'produto' => $firstProduct ? $firstProduct->id : 'Nenhum produto encontrado',
            'localização' => $firstLocation ? $firstLocation->id : 'Nenhuma localização encontrada'
        ]);
            
        $this->resetValidation();
        $this->reset('schedule');
            
        // Inicializa primeiro com o product_id para que o generateScheduleNumber funcione
        $this->schedule = [
            'product_id' => $firstProduct ? $firstProduct->id : '',
        ];
            
        // Agora geramos o número de programação após definir o product_id
        $this->generateScheduleNumber();
        \Illuminate\Support\Facades\Log::info('Número de programação gerado para calendário', ['numero' => $this->schedule['schedule_number'] ?? 'Não gerado']);
            
        // Completa a inicialização com os demais valores
        $this->schedule = array_merge($this->schedule, [
            'start_date' => $date,
            'end_date' => date('Y-m-d', strtotime($date . ' +7 days')),
            'planned_quantity' => '1', // Valor padrão
            'status' => 'draft',
            'priority' => 'medium',
            'location_id' => $firstLocation ? $firstLocation->id : '',
            'responsible' => '',
            'notes' => ''
        ]);
            
        // Garantir que o schedule_number existe
        if (empty($this->schedule['schedule_number'])) {
            // Gerar manualmente se o método normal falhou
            $lastSchedule = \App\Models\Mrp\ProductionSchedule::orderBy('id', 'desc')->first();
            $lastId = $lastSchedule ? $lastSchedule->id + 1 : 1;
            $this->schedule['schedule_number'] = 'SCH-' . date('Ymd') . '-' . str_pad($lastId, 4, '0', STR_PAD_LEFT);
                
            \Illuminate\Support\Facades\Log::info('Número de programação gerado manualmente para calendário', 
                ['numero' => $this->schedule['schedule_number']]);
        }
            
        $this->editMode = false;
        $this->showModal = true;
            
        \Illuminate\Support\Facades\Log::info('Modal do calendário iniciado com dados', [
            'showModal' => $this->showModal,
            'editMode' => $this->editMode,
            'schedule' => $this->schedule
        ]);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Error in openCreateModalForDate', [
            'exception' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
}

/**
 * Carregar e abrir modal para editar programação
 */
public function edit($id)
{
    try {
        \Illuminate\Support\Facades\Log::info('Iniciando edição de programação', ['id' => $id]);
        
        $this->scheduleId = $id;
        // Garantindo que estamos usando os relacionamentos corretos
        $schedule = ProductionSchedule::with(['product', 'location'])->findOrFail($id);
        
        \Illuminate\Support\Facades\Log::info('Agendamento encontrado para edição', [
            'id' => $id,
            'número' => $schedule->schedule_number,
            'produto_id' => $schedule->product_id,
            'produto' => $schedule->product ? $schedule->product->name : 'Não definido'
        ]);
            
        $this->schedule = [
            'product_id' => $schedule->product_id,
            'schedule_number' => $schedule->schedule_number,
            'start_date' => $schedule->start_date->format('Y-m-d'),
            'start_time' => $schedule->start_time,
            'end_date' => $schedule->end_date->format('Y-m-d'),
            'end_time' => $schedule->end_time,
            'planned_quantity' => $schedule->planned_quantity,
            'status' => $schedule->status,
            'priority' => $schedule->priority,
            'responsible' => $schedule->responsible,
            'location_id' => $schedule->location_id,
            'working_hours_per_day' => $schedule->working_hours_per_day,
            'hourly_production_rate' => $schedule->hourly_production_rate,
            'working_days' => $schedule->working_days ?? [
                'mon' => true,
                'tue' => true,
                'wed' => true,
                'thu' => true,
                'fri' => true,
                'sat' => false,
                'sun' => false
            ],
            'setup_time' => $schedule->setup_time,
            'cleanup_time' => $schedule->cleanup_time,
            'notes' => $schedule->notes
        ];
            
        $this->editMode = true;
        $this->showModal = true;
        
        \Illuminate\Support\Facades\Log::info('Modal de edição aberto com sucesso', [
            'editMode' => $this->editMode,
            'showModal' => $this->showModal,
            'scheduleId' => $this->scheduleId
        ]);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        \Illuminate\Support\Facades\Log::error('Agendamento não encontrado para edição', [
            'id' => $id,
            'error' => $e->getMessage()
        ]);
        
        $this->dispatch('notify',
            type: 'error',
            title: __('messages.error'),
            message: __('messages.schedule_not_found')
        );
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Erro ao editar programação', [
            'id' => $id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        $this->dispatch('notify',
            type: 'error',
            title: __('messages.error'),
            message: __('messages.error_loading_schedule', ['error' => $e->getMessage()])
        );
    }
} 
/**
 * Visualizar e gerenciar ordens de produção relacionadas a um agendamento
 * Permite visualizar ordens associadas e adicionar novas ordens
 */
public function viewOrders($id)
{
    \Illuminate\Support\Facades\Log::info('Visualizando ordens para o agendamento', ['id' => $id]);
    $this->scheduleId = $id;
    $this->showOrdersModal = true;
    
    // Carregamos o agendamento com seus detalhes
    $schedule = ProductionSchedule::with(['product'])->find($id);
    if (!$schedule) {
        $this->showOrdersModal = false;
        $this->dispatch('notify', 
            type: 'error',
            title: __('messages.error'),
            message: __('messages.schedule_not_found')
        );
        return;
    }
    
    // Verificamos se há ordens relacionadas e carregamos suas informações
    $relatedOrders = ProductionOrder::where('schedule_id', $id)
        ->with(['product'])
        ->get();
    
    \Illuminate\Support\Facades\Log::info('Ordens carregadas com sucesso', [
        'agendamento_id' => $id,
        'quantidade_ordens' => $relatedOrders->count()
    ]);
}
    
/**
 * Visualizar produção detalhada
 */
public function view($id)
{
    try {
        \Illuminate\Support\Facades\Log::info('Visualizando agendamento', ['id' => $id]);
        
        // Definir ID e carregar o agendamento completo com seus relacionamentos
        $this->scheduleId = $id;
        $this->selectedSchedule = ProductionSchedule::with(['product', 'location'])->find($id);
        
        if (!$this->selectedSchedule) {
            \Illuminate\Support\Facades\Log::warning('Agendamento não encontrado', ['id' => $id]);
            $this->dispatch('notify',
                type: 'error',
                title: __('messages.error'),
                message: __('messages.schedule_not_found')
            );
            return;
        }
        
        \Illuminate\Support\Facades\Log::info('Agendamento carregado com sucesso', [
            'id' => $id,
            'número' => $this->selectedSchedule->schedule_number,
            'produto' => $this->selectedSchedule->product ? $this->selectedSchedule->product->name : 'Produto não definido'
        ]);
        
        // Abrir o modal
        $this->showViewModal = true;
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Erro ao visualizar agendamento', [
            'id' => $id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        $this->dispatch('notify',
            type: 'error',
            title: __('messages.error'),
            message: __('messages.error_viewing_schedule', ['error' => $e->getMessage()])
        );
    }
}

/**
 * Confirmar exclusão de programação
 */
public function confirmDelete($id)
{
    $this->scheduleId = $id;
    $this->scheduleToDelete = ProductionSchedule::with(['product'])->findOrFail($id);
    $this->confirmDelete = false; // Resetar confirmação a cada nova solicitação de exclusão
    $this->showDeleteModal = true;
}
    
    /**
     * Excluir programação
     */
    public function delete()
    {
        // Validar confirmação
        if (!$this->confirmDelete) {
            $this->dispatch('notify',
                type: 'error',
                title: __('messages.confirmation_required'),
                message: __('messages.please_confirm_deletion')
            );
            return;
        }
        
        if (!$this->scheduleToDelete) {
            $this->scheduleToDelete = ProductionSchedule::findOrFail($this->scheduleId);
        }
        
        try {
            // Verificar se existem ordens relacionadas
            $relatedOrdersCount = ProductionOrder::where('schedule_id', $this->scheduleId)->count();
            
            if ($relatedOrdersCount > 0) {
                $this->dispatch('notify',
                    type: 'error',
                    title: __('messages.delete_error'),
                    message: __('messages.cannot_delete_schedule_with_orders', ['count' => $relatedOrdersCount])
                );
                
                $this->closeCreateEditModal();
                return;
            }
            
            // Excluir programação
            $this->scheduleToDelete->delete();
            
            $this->dispatch('notify',
                type: 'success',
                title: __('messages.success'),
                message: __('messages.production_schedule_deleted')
            );
            
            $this->scheduleToDelete = null;
            $this->closeCreateEditModal();
            
            // Recarregar dados do calendário se estiver na visualização de calendário
            if ($this->currentTab === 'calendar') {
                $this->loadCalendarEvents();
            }
        } catch (\Exception $e) {
            $this->dispatch('notify',
                type: 'error',
                title: __('messages.error'),
                message: __('messages.error_deleting_schedule', ['error' => $e->getMessage()])
            );
        }
    }

    /**
     * Fechar modal de criação/edição
     */
    public function closeCreateEditModal()
    {
        \Illuminate\Support\Facades\Log::info('closeCreateEditModal called');
        
        $this->showModal = false;
        $this->editMode = false;
        
        \Illuminate\Support\Facades\Log::info('Create/Edit modal closed', [
            'showModal' => $this->showModal,
            'editMode' => $this->editMode
        ]);
    }

    /**
     * Fechar modal de exclusão
     */
    public function closeDeleteModal()
    {
        \Illuminate\Support\Facades\Log::info('closeDeleteModal called');
        
        $this->showDeleteModal = false;
        $this->scheduleToDelete = null;
        $this->confirmDelete = false;
        
        \Illuminate\Support\Facades\Log::info('Delete modal closed');
    }

    /**
     * Criar uma nova ordem de produção associada ao agendamento atual
     */
    public function createOrder()
    {
        \Illuminate\Support\Facades\Log::info('Iniciando criação de ordem para o agendamento', ['id' => $this->scheduleId]);
        
        // Validar os dados da nova ordem
        $this->validate([
            'newOrder.quantity' => 'required|numeric|min:0.01',
            'newOrder.due_date' => 'required|date',
            'newOrder.description' => 'nullable|string|max:500',
        ], [], [
            'newOrder.quantity' => __('messages.quantity'),
            'newOrder.due_date' => __('messages.due_date'),
            'newOrder.description' => __('messages.description'),
        ]);
        
        try {
            // Buscar o agendamento atual
            $schedule = ProductionSchedule::with(['product'])->findOrFail($this->scheduleId);
            
            // Criar a nova ordem de produção
            $order = new ProductionOrder();
            $order->schedule_id = $this->scheduleId;
            $order->product_id = $schedule->product_id;
            $order->order_number = 'ORD-' . date('Ymd') . '-' . sprintf('%04d', ProductionOrder::count() + 1);
            $order->quantity = $this->newOrder['quantity'];
            $order->due_date = $this->newOrder['due_date'];
            $order->description = $this->newOrder['description'];
            $order->status = 'pending';
            $order->created_by = auth()->id();
            $order->save();
            
            // Resetar o formulário
            $this->reset('newOrder');
            $this->newOrder = [
                'quantity' => '',
                'due_date' => '',
                'description' => '',
                'status' => 'pending'
            ];
            
            // Notificar o usuário
            $this->dispatch('notify', 
                type: 'success',
                title: __('messages.success'),
                message: __('messages.order_created_successfully')
            );
            
            \Illuminate\Support\Facades\Log::info('Ordem criada com sucesso', [
                'agendamento_id' => $this->scheduleId,
                'ordem_id' => $order->id
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao criar ordem', [
                'erro' => $e->getMessage(),
                'agendamento_id' => $this->scheduleId
            ]);
            
            $this->dispatch('notify', 
                type: 'error',
                title: __('messages.error'),
                message: __('messages.order_creation_error', ['error' => $e->getMessage()])
            );
        }
    }

    /**
     * Fechar modal de ordens
     */
    public function closeOrdersModal()
    {
        \Illuminate\Support\Facades\Log::info('closeOrdersModal called');
        $this->showOrdersModal = false;
        $this->scheduleId = null;
        
        // Resetar o formulário de nova ordem
        $this->reset('newOrder');
        $this->newOrder = [
            'quantity' => '',
            'due_date' => '',
            'description' => '',
            'status' => 'pending'
        ];
        
        \Illuminate\Support\Facades\Log::info('Orders modal closed');
    }

    /**
     * Fechar modal de visualização
     */
    public function closeViewModal()
    {
        \Illuminate\Support\Facades\Log::info('closeViewModal called');
        
        $this->showViewModal = false;
        
        \Illuminate\Support\Facades\Log::info('View modal closed');
    }

    /**
     * Fechar modal de planos diários
     */
    public function closeDailyPlansModal()
    {
        \Illuminate\Support\Facades\Log::info('closeDailyPlansModal called');
        
        $this->showDailyPlansModal = false;
        $this->viewingDailyPlans = false;
        $this->dailyPlans = [];
        
        \Illuminate\Support\Facades\Log::info('Daily plans modal closed');
    }

    /**
     * Salvar programação (criar ou atualizar)
     */
    public function save()
    {
        // Chamada para método específico
        if ($this->editMode) {
            return $this->update();
        } else {
            return $this->store();
        }
    }
    
    /**
     * Método para registrar o início da produção
     */
    public function startProduction()
    {
        try {
            \Illuminate\Support\Facades\Log::info('=== INÍCIO DO MÉTODO START PRODUCTION ===');
            
            $schedule = ProductionSchedule::findOrFail($this->scheduleId);
            
            // Atualizar o status e registrar a data/hora de início
            $schedule->status = 'in_progress';
            $schedule->actual_start_time = now();
            $schedule->save();
            
            // Atualizar o primeiro plano diário para 'in_progress'
            $firstDailyPlan = $schedule->dailyPlans()
                ->where('status', 'pending')
                ->orderBy('production_date')
                ->first();
                
            if ($firstDailyPlan) {
                $firstDailyPlan->status = 'in_progress';
                $firstDailyPlan->save();
            }
            
            // Carregar dados atualizados
            $this->view($this->scheduleId);
            
            // Notificar usuário
            $this->dispatch('notify',
                type: 'success',
                title: __('messages.success'),
                message: __('messages.production_started')
            );
            
            \Illuminate\Support\Facades\Log::info('=== FIM DO MÉTODO START PRODUCTION ===');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao iniciar produção: ' . $e->getMessage());
            
            $this->dispatch('notify',
                type: 'error',
                title: __('messages.error'),
                message: __('messages.production_start_error', ['error' => $e->getMessage()])
            );
        }
    }

    /**
     * Visualizar planos diários
     */
    public function viewDailyPlans($id)
    {
        try {
            \Illuminate\Support\Facades\Log::info('Visualizando planos diários', ['id' => $id]);
            
            $this->scheduleId = $id;
            $schedule = ProductionSchedule::with(['product', 'dailyPlans' => function($query) {
                $query->orderBy('production_date');
            }])->findOrFail($id);
            
            // Carregar planos diários para a programação
            $this->dailyPlans = $schedule->dailyPlans->toArray();
            $this->viewingSchedule = $schedule;
            $this->viewingDailyPlans = true;
            $this->showDailyPlansModal = true;
            
            // Verificar disponibilidade de componentes para esta programação
            // Salvar o produto_id e a quantidade planejada atual
            $savedProductId = $this->schedule['product_id'] ?? null;
            $savedQuantity = $this->schedule['planned_quantity'] ?? null;
            
            // Temporariamente definir os valores da programação que estamos visualizando
            $this->schedule['product_id'] = $schedule->product_id;
            $this->schedule['planned_quantity'] = $schedule->planned_quantity;
            
            // Executar a verificação de componentes
            $this->checkComponentAvailability();
            
            // Restaurar os valores originais se necessário
            if ($savedProductId) {
                $this->schedule['product_id'] = $savedProductId;
            }
            if ($savedQuantity) {
                $this->schedule['planned_quantity'] = $savedQuantity;
            }
            
            \Illuminate\Support\Facades\Log::info('Planos diários carregados com sucesso', 
                ['id' => $id, 'número' => $schedule->schedule_number, 'produto' => $schedule->product->name]
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao carregar planos diários', [
                'id' => $id,
                'erro' => $e->getMessage(),
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatch('toast', 
                type: 'error',
                title: __('messages.error'),
                message: __('messages.load_daily_plans_error', ['error' => $e->getMessage()])
            );
        }
    }
    
    /**
     * Método para atualizar um plano diário de produção
     */
    public function updateDailyPlan($index)
    {
        try {
            $planData = $this->dailyPlans[$index];
            $plan = \App\Models\Mrp\ProductionDailyPlan::findOrFail($planData['id']);
            
            \Illuminate\Support\Facades\Log::info('Atualizando plano diário de produção', [
                'id' => $planData['id'],
                'data_producao' => $planData['production_date'] ?? 'N/A',
                'agendamento_id' => $this->scheduleId
            ]);
            
            // Atualizar dados do plano
            $plan->planned_quantity = $planData['planned_quantity'];
            $plan->actual_quantity = $planData['actual_quantity'];
            $plan->start_time = $planData['start_time'];
            $plan->end_time = $planData['end_time'];
            $plan->status = $planData['status'];
            $plan->notes = $planData['notes'];
            $plan->updated_by = auth()->id();
            $plan->save();
            
            // Recalcular totais do agendamento principal
            $schedule = $plan->schedule;
            $schedule->actual_quantity = $schedule->dailyPlans()->sum('actual_quantity');
            $schedule->save();
            
            // Atualizar dados na interface
            $this->dailyPlans = $schedule->dailyPlans()->orderBy('production_date')->get()->toArray();
            
            \Illuminate\Support\Facades\Log::info('Plano diário atualizado com sucesso', [
                'id' => $planData['id'],
                'agendamento_id' => $this->scheduleId,
                'usuario' => auth()->user()->name
            ]);
            
            $this->dispatch('toast', 
                type: 'success',
                title: __('messages.success'),
                message: __('messages.daily_plan_updated')
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao atualizar plano diário', [
                'id' => $planData['id'] ?? 'N/A',
                'index' => $index,
                'erro' => $e->getMessage(),
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatch('toast', 
                type: 'error',
                title: __('messages.error'),
                message: __('messages.daily_plan_update_error', ['error' => $e->getMessage()])
            );
        }
    }

/**
 * Atualizar programação existente
 */
public function update()
{
    try {
        \Illuminate\Support\Facades\Log::info('=== INÍCIO DO MÉTODO UPDATE ===');
        \Illuminate\Support\Facades\Log::info('Tentando atualizar programação', ['scheduleId' => $this->scheduleId]);
        
        $this->validate();
        \Illuminate\Support\Facades\Log::info('Dados validados com sucesso para atualização');
        
        // Localizar o registro existente
        $schedule = ProductionSchedule::findOrFail($this->scheduleId);
        
        // Verificar se datas ou quantidade foram alteradas
        $datesChanged = $schedule->start_date != $this->schedule['start_date'] || 
                      $schedule->end_date != $this->schedule['end_date'] ||
                      $schedule->planned_quantity != $this->schedule['planned_quantity'];
        
        // Preparar dados para atualização
        $data = $this->schedule;
        // Não use updated_by pois a coluna não existe na tabela
        
        // Tratar status 'in_progress'
        if ($data['status'] == 'in_progress' && $schedule->status != 'in_progress') {
            $data['actual_start_time'] = now();
        }
        
        // Tratar status 'completed'
        if ($data['status'] == 'completed' && $schedule->status != 'completed') {
            $data['actual_end_time'] = now();
            
            // Verificar se houve atraso
            $endDateTime = new \DateTime($data['end_date'] . ' ' . $data['end_time']);
            $now = new \DateTime();
            
            if ($now > $endDateTime) {
                $data['is_delayed'] = true;
                if (empty($data['delay_reason'])) {
                    $data['delay_reason'] = 'Produção concluída após a data/hora final prevista';
                }
            }
        }
        
        \Illuminate\Support\Facades\Log::info('Tentando salvar as alterações do agendamento', [
            'id' => $this->scheduleId,
            'productId' => $this->schedule['product_id'],
            'scheduleNumber' => $this->schedule['schedule_number']
        ]);
        
        // Atualizar o registro
        $schedule->update($data);
        
        // Se datas ou quantidade foram alteradas, redistribuir os planos diários
        if ($datesChanged) {
            $schedule->distributePlannedQuantity();
        }
        
        \Illuminate\Support\Facades\Log::info('Agendamento atualizado com sucesso', ['id' => $this->scheduleId]);
        
        // Fechar o modal após salvar
        $this->closeCreateEditModal();
        
        // Notificar usuário
        $this->dispatch('notify',
            type: 'success',
            title: __('messages.success'),
            message: __('messages.schedule_updated')
        );
        
        \Illuminate\Support\Facades\Log::info('=== FIM DO MÉTODO UPDATE ===');
        return true;
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Erro ao atualizar programação: ' . $e->getMessage());
        
        $this->dispatch('notify',
            type: 'error',
            title: __('messages.error'),
            message: __('messages.schedule_update_error', ['error' => $e->getMessage()])
        );
        
        return false;
    }
}

/**
 * Store new production schedule
 */
public function store()
{
    try {
        \Illuminate\Support\Facades\Log::info('=== INÍCIO DO MÉTODO STORE ===');
        
        $this->validate();
        
        // Preparar dados para salvar
        $data = $this->schedule;
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();
        
        // Tratar status 'in_progress'
        if ($data['status'] == 'in_progress') {
            $data['actual_start_time'] = now();
        }
        
        // Salvar o registro
        $schedule = ProductionSchedule::create($data);
        
        \Illuminate\Support\Facades\Log::info('Programação criada com sucesso', ['id' => $schedule->id]);
        
        // Criar planos diários
        $schedule->distributePlannedQuantity();
        
        \Illuminate\Support\Facades\Log::info('Planos diários criados com sucesso');
        
        // Fechar o modal após salvar
        $this->closeCreateEditModal();
        
        // Notificar usuário
        $this->dispatch('notify',
            type: 'success',
            title: __('messages.success'),
            message: __('messages.schedule_created')
        );
        
        \Illuminate\Support\Facades\Log::info('=== FIM DO MÉTODO STORE ===');
        return true;
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Erro ao criar programação: ' . $e->getMessage());
        
        $this->dispatch('notify',
            type: 'error',
            title: __('messages.error'),
            message: __('messages.schedule_creation_error', ['error' => $e->getMessage()])
        );
        
        \Illuminate\Support\Facades\Log::info('=== FIM DO MÉTODO STORE - ERRO ===');
        return false;
    }
}
    
    /**
     * Carregar dados para a view
     */




    public function render()
    {
        // Se estiver na aba de calendário, carregar eventos
        if ($this->currentTab === 'calendar' && empty($this->calendarEvents)) {
            $this->loadCalendarEvents();
        }
        
        // Construir a consulta para a listagem
        $query = ProductionSchedule::with(['product', 'location'])
            ->when($this->search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('schedule_number', 'like', "%{$search}%")
                      ->orWhere('responsible', 'like', "%{$search}%")
                      ->orWhereHas('product', function ($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%");
                      });
                });
            })
            ->when($this->statusFilter, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($this->priorityFilter, function ($query, $priority) {
                $query->where('priority', $priority);
            })
            ->when($this->dateFilter, function ($query, $date) {
                $query->whereDate('start_date', '<=', $date)
                      ->whereDate('end_date', '>=', $date);
            })
            ->when($this->productFilter, function ($query, $productId) {
                $query->where('product_id', $productId);
            })
            ->orderBy($this->sortField, $this->sortDirection);
        
        $schedules = $query->paginate($this->perPage);
        
        // Carregar dados para selects - apenas produtos do tipo finished_product
        $products = Product::where('product_type', 'finished_product')->orderBy('name')->get();
        
        // Carregar localizações de inventário da supply chain
        $locations = Location::orderBy('name')->get();
        
        // Carregar ordens de produção se estiver visualizando
        $relatedOrders = [];
        $selectedSchedule = null;
        
        if ($this->showOrdersModal && $this->scheduleId) {
            $relatedOrders = ProductionOrder::where('schedule_id', $this->scheduleId)
                ->with(['product'])
                ->get();
                
            $selectedSchedule = ProductionSchedule::with(['product', 'location'])
                ->find($this->scheduleId);
        }
        
        // Visualizar detalhes da programação selecionada
        $viewingSchedule = null;
        if ($this->showViewModal && $this->scheduleId) {
            $viewingSchedule = ProductionSchedule::with(['product', 'location'])
                ->find($this->scheduleId);
        }
        
        // Definições para os selects
        $statuses = [
            'draft' => 'Rascunho',
            'confirmed' => 'Confirmada',
            'in_progress' => 'Em Andamento',
            'completed' => 'Concluída',
            'cancelled' => 'Cancelada'
        ];
        
        $priorities = [
            'low' => 'Baixa',
            'medium' => 'Média',
            'high' => 'Alta',
            'urgent' => 'Urgente'
        ];
        
        return view('livewire.mrp.production-scheduling', [
            'schedules' => $schedules,
            'products' => $products,
            'locations' => $locations,
            'statuses' => $statuses,
            'priorities' => $priorities,
            'relatedOrders' => $relatedOrders,
            'selectedSchedule' => $selectedSchedule,
            'viewingSchedule' => $viewingSchedule
        ])->layout('layouts.livewire', [
            'title' => 'Programação de Produção'
        ]);
    }
}
