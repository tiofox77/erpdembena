<?php

namespace App\Livewire\Mrp;

use App\Livewire\Mrp\CompleteProductionTrait;
use App\Livewire\Mrp\DailyProductionStockTrait;
use Illuminate\Support\Facades\Log;
use App\Models\Mrp\BomDetail;
use App\Models\Mrp\BomHeader;
use App\Models\Mrp\FailureCategory;
use App\Models\Mrp\FailureRootCause;
use App\Models\Mrp\Line;
use App\Models\Mrp\ProductionDailyPlan;
use App\Models\Mrp\ProductionOrder;
use App\Models\Mrp\ProductionSchedule;
use App\Models\Mrp\Responsible;
use App\Models\Mrp\Shift;
use App\Models\SupplyChain\InventoryLocation as Location;
use App\Models\SupplyChain\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class ProductionScheduling extends Component
{
    use DailyProductionStockTrait;
    protected $messages = [
        'newOrder.quantity.required' => 'A quantidade é obrigatória',
        'newOrder.quantity.numeric' => 'A quantidade deve ser um número',
        'newOrder.quantity.min' => 'A quantidade deve ser maior que zero',
        'newOrder.due_date.required' => 'A data de entrega é obrigatória',
        'newOrder.due_date.date' => 'A data de entrega deve ser uma data válida'
    ];

    use WithPagination;
    use CompleteProductionTrait;

    // Propriedades do componente
    public $search = '';
    public $sortField = 'start_date';
    public $sortDirection = 'asc';
    public $perPage = 10;
    public $currentTab = 'list';
    public $viewType = 'table';  // Tipo de visualização (tabela ou calendário)
    public $selectedShifts = [];  // Array de IDs de turnos selecionados
    // Verificação de componentes
    public $componentAvailability = [];
    public $showComponentWarning = false;
    public $insufficientComponents = [];
    public $maxQuantityPossible = 0;
    public $rawMaterialWarehouses = null;
    
    // Estas propriedades já existem no componente, foram mantidas aqui apenas como comentário
    // public $showDeleteModal = false;
    // public $scheduleToDelete = null;
    // public $confirmDelete = false;

    /**
     * Computed property to check if raw material warehouses exist
     */
    public function getHasRawMaterialWarehousesProperty()
    {
        return $this->rawMaterialWarehouses && $this->rawMaterialWarehouses->count() > 0;
    }

    /**
     * Método auxiliar para resetar o formulário de nova ordem
     *
     * @return void
     */
    private function resetOrderForm()
    {
        $this->newOrder = [
            'quantity' => '',
            'due_date' => '',
            'description' => '',
            'status' => 'pending'
        ];
        $this->resetErrorBag(['newOrder.quantity', 'newOrder.due_date', 'newOrder.description']);
    }

    /**
     * Criar nova ordem de produção para a programação selecionada
     *
     * @return void
     */
    public function createOrder()
    {
        // Validar os dados da nova ordem
        $this->validate($this->orderRules);

        try {
            // Verificar se existe uma programação selecionada
            if (!$this->scheduleId) {
                session()->flash('error', 'Nenhuma programação selecionada');
                return;
            }

            // Buscar a programação
            $schedule = ProductionSchedule::with('product')->find($this->scheduleId);
            if (!$schedule) {
                session()->flash('error', 'Programação não encontrada');
                return;
            }

            // Gerar número da ordem
            $lastOrder = ProductionOrder::orderBy('id', 'desc')->first();
            $orderNumber = 'ORD' . str_pad(($lastOrder ? $lastOrder->id + 1 : 1), 6, '0', STR_PAD_LEFT);

            // Criar a nova ordem
            $order = new ProductionOrder();
            $order->schedule_id = $this->scheduleId;
            $order->product_id = $schedule->product_id;
            $order->order_number = $orderNumber;
            $order->planned_quantity = $this->newOrder['quantity'];
            $order->due_date = $this->newOrder['due_date'];
            $order->description = $this->newOrder['description'] ?: null;
            $order->status = 'pending';
            $order->created_by = auth()->id();
            $order->created_at = Carbon::now();
            $order->updated_at = Carbon::now();
            $order->save();

            // Resetar o formulário após criar com sucesso
            $this->resetOrderForm();

            // Notificar o usuário
            session()->flash('success', 'Ordem de produção criada com sucesso!');

            // Log para debug
            \Illuminate\Support\Facades\Log::info('Ordem de produção criada', [
                'order_id' => $order->id,
                'order_number' => $orderNumber,
                'schedule_id' => $this->scheduleId
            ]);
        } catch (\Exception $e) {
            // Log do erro
            \Illuminate\Support\Facades\Log::error('Erro ao criar ordem de produção', [
                'error' => $e->getMessage(),
                'schedule_id' => $this->scheduleId,
                'data' => $this->newOrder
            ]);

            // Notificar o usuário
            session()->flash('error', 'Erro ao criar ordem de produção: ' . $e->getMessage());
        }
    }

    // Propriedades para modal
    public $showModal = false;
    public $showDeleteModal = false;
    public $showOrdersModal = false;
    public $showViewModal = false;  // Modal de visualização
    public $editMode = false;
    public $scheduleId = null;
    public $viewingSchedule = null;  // Programação sendo visualizada
    public $scheduleToDelete = null;  // Programacao a ser excluída
    public $confirmDelete = false;  // Confirmação de exclusão
    public $deleting = false;  // Flag para controlar o estado de exclusão
    // Análise de impacto de paradas
    public $impactAnalysis = [];
    public $breakdownImpact = [];
    public $chartHistory = [];  // Propriedade pública específica para o histórico usado nos gráficos

    // Nova ordem de produção
    public $newOrder = [
        'quantity' => '',
        'due_date' => '',
        'description' => '',
        'status' => 'pending'
    ];

    // Validação para ordens de produção
    protected $orderRules = [
        'newOrder.quantity' => 'required|numeric|min:0.01',
        'newOrder.due_date' => 'required|date',
        'newOrder.description' => 'nullable|string|max:255'
    ];

    protected $listeners = [
        'startProduction' => 'startProduction',
        'completeProduction' => 'completeProduction',
        'updateWipInventory' => 'updateWipInventory',
        'updated:schedule.product_id' => 'checkComponentAvailability',
        'updated:schedule.planned_quantity' => 'checkComponentAvailability',
        'openDeleteModal' => 'openDeleteModal',  // Renomeado para evitar conflitos
        'viewDailyPlans' => 'viewDailyPlans',
        'updateDailyPlan' => 'updateDailyPlan',
        'closeDailyPlansModal' => 'closeDailyPlansModal',
        'view' => 'viewSchedule',
        'selectShift' => 'selectShift',
        'closeCreateEditModal' => 'closeCreateEditModal',
        'toggleShift' => 'toggleShift',
        'viewOrders' => 'viewOrders',
        'closeOrdersModal' => 'closeOrdersModal'
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
        $this->rawMaterialWarehouses = null;

        // Define um tempo limite para prevenir loops infinitos
        $startTime = microtime(true);
        $timeLimit = 2.0;  // limite de 2 segundos para execução

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
            ? (float) $this->schedule['planned_quantity']
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
            ->with(['component' => function ($query) {
                $query->with('inventoryItems');
            }])
            ->get();

        if ($components->isEmpty()) {
            // BOM existe mas não tem componentes cadastrados
            return;
        }

        // Buscar todos os locais marcados como armazém de matéria-prima (fora do loop para eficiência)
        // Importante: certifique-se de usar a tabela correta sc_inventory_locations
        $this->rawMaterialWarehouses = Location::where('is_raw_material_warehouse', 1)  // Usando 1 em vez de true para garantir compatibilidade com MySQL
            ->where('is_active', 1)  // Usando 1 em vez de true para garantir compatibilidade com MySQL
            ->select('id', 'name', 'code', 'city')
            ->orderBy('name')
            ->get();

        // Log para debug - verificar o SQL gerado e os resultados
        \Illuminate\Support\Facades\Log::info('SQL para buscar armazéns de matéria-prima', [
            'query' => Location::where('is_raw_material_warehouse', 1)->where('is_active', 1)->toSql(),
            'bindings' => Location::where('is_raw_material_warehouse', 1)->where('is_active', 1)->getBindings(),
            'count' => $this->rawMaterialWarehouses->count(),
            'warehouses' => $this->rawMaterialWarehouses->toArray()
        ]);

        // Extrair apenas os IDs para o filtro de disponibilidade
        $rawMaterialLocationIds = $this->rawMaterialWarehouses->pluck('id')->toArray();

        // Registrar informações sobre os locais de matéria-prima para debug
        \Illuminate\Support\Facades\Log::info('Locais de armazém de matéria-prima', [
            'total_locations' => count($rawMaterialLocationIds),
            'location_ids' => $rawMaterialLocationIds
        ]);

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

            // Calcular quantidade disponível (somar apenas os locais de inventário marcados como armazém de matéria-prima)
            $available_quantity = 0;
            if ($bomComponent->component->inventoryItems) {
                // Filtrar apenas os itens em armazéns de matéria-prima
                // Importante: usar $rawMaterialLocationIds que foi definido fora do loop
                \Illuminate\Support\Facades\Log::info('Filtrando itens de inventário para componente', [
                    'component_name' => $bomComponent->component->name ?? 'Desconhecido',
                    'total_inventory_items' => $bomComponent->component->inventoryItems->count(),
                    'raw_material_location_ids' => $rawMaterialLocationIds
                ]);

                $rawMaterialItems = $bomComponent->component->inventoryItems->filter(function ($item) use ($rawMaterialLocationIds) {
                    \Illuminate\Support\Facades\Log::info('Verificando item de inventário', [
                        'item_id' => $item->id,
                        'location_id' => $item->location_id,
                        'is_in_raw_material_warehouse' => in_array($item->location_id, $rawMaterialLocationIds)
                    ]);
                    return in_array($item->location_id, $rawMaterialLocationIds);
                });

                // Somar as quantidades disponíveis apenas nos armazéns de matéria-prima
                $available_quantity = $rawMaterialItems->sum('quantity_on_hand');

                // Para debug, também calcular a quantidade total em todos os armazéns
                $total_available = $bomComponent->component->inventoryItems->sum('quantity_on_hand');

                // Se a quantidade disponível for diferente da total, registrar para debug
                if ($available_quantity != $total_available) {
                    \Illuminate\Support\Facades\Log::info('Diferença de quantidade entre armazéns de matéria-prima e total', [
                        'component' => $bomComponent->component->name,
                        'raw_material_warehouses_only' => $available_quantity,
                        'all_warehouses' => $total_available,
                        'difference' => $total_available - $available_quantity
                    ]);
                }
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

        // Atualizar flags e valores para usar na UI
        $this->showComponentWarning = $insufficientFound;
        $this->maxQuantityPossible = $maxPossible;
    }

    // Propriedades do formulário
    public $schedule = [
        'product_id' => '',
        'schedule_number' => '',
        'start_date' => '',
        'end_date' => '',
        'planned_quantity' => '',
        'actual_quantity' => 0,
        'actual_start_time' => null,
        'end_date' => null,  // Substituído actual_end_time por end_date
        'is_delayed' => false,
        'delay_reason' => '',
        'status' => 'draft',
        'priority' => 'medium',
        'responsible' => '',
        'location_id' => '',  // ID da localização de inventário da supply chain
        'working_hours_per_day' => 8,  // Horas de trabalho por dia
        'hourly_production_rate' => 0,  // Taxa de produção por hora
        'working_days' => [  // Dias de trabalho na semana
            'mon' => true,
            'tue' => true,
            'wed' => true,
            'thu' => true,
            'fri' => true,
            'sat' => false,
            'sun' => false
        ],
        'setup_time' => 30,  // Tempo de setup em minutos
        'cleanup_time' => 15,  // Tempo de limpeza em minutos
        'notes' => ''
    ];

    // Planos diários de produção
    public $dailyPlans = [];
    public $showDailyPlansModal = false;
    public $shifts = [];  // Turnos associados ao planejamento atual
    public $selectedShiftId = null;
    public $selectedShiftName = null;
    public $filteredDailyPlans = [];
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
        // Inicializar datas do calendário
        $this->calendarDate = date('Y-m-d');
        $this->updateCalendarTitle();
        $this->setupCalendarDayNames();
        $this->loadCalendarEvents();

        // Carregar todos os turnos disponíveis no sistema
        $allShifts = Shift::orderBy('name')->get();
        \Illuminate\Support\Facades\Log::info('Turnos carregados no mount', [
            'total' => $allShifts->count(),
            'ids' => $allShifts->pluck('id')->toArray(),
            'names' => $allShifts->pluck('name')->toArray()
        ]);

        // Disponibilizar os turnos como propriedade pública para todas as views
        $this->shifts = $allShifts;
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
            'schedule.end_date' => 'required|date|after_or_equal:schedule.start_date',
            'schedule.planned_quantity' => 'required|numeric|min:0.001',
            'schedule.location_id' => 'required|exists:sc_inventory_locations,id',
            'schedule.status' => 'required|in:draft,confirmed,in_progress,completed,cancelled',
            'schedule.priority' => 'required|in:low,medium,high,urgent',
            'schedule.line_id' => 'nullable|exists:mrp_lines,id',
            'schedule.responsible_id' => 'nullable|exists:mrp_responsibles,id',
            'schedule.working_hours_per_day' => 'required|numeric|min:0.5|max:24',
            'schedule.hourly_production_rate' => 'required|numeric|min:0.1',
            'schedule.setup_time' => 'nullable|integer|min:0',
            'schedule.cleanup_time' => 'nullable|integer|min:0',
        ];
    }

    /**
     * Resetar paginação quando a busca ou os filtros mudarem
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    /**
     * Resetar paginação quando o número de itens por página mudar
     */
    public function updatingPerPage()
    {
        $this->resetPage();
    }
    
    /**
     * Resetar paginação quando o tipo de visualização mudar
     */
    public function updatingViewType()
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
        $this->reset(['search', 'statusFilter', 'priorityFilter', 'dateFilter', 'productFilter']);
        $this->resetPage();
    }
    
    /**
     * Gerar PDF de uma programação de produção individual
     *
     * @param int $scheduleId ID da programação de produção
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|null
     */
    public function generateSchedulePdf($scheduleId)
    {
        \Illuminate\Support\Facades\Log::info('=== INÍCIO DO MÉTODO generateSchedulePdf ===', [
            'schedule_id' => $scheduleId
        ]);
        
        try {
            // Buscar a programação com os relacionamentos principais (sem breakdowns)
            $schedule = ProductionSchedule::with([
                'product',
                'location',
                'line',
                'shifts',
                'dailyPlans' => function($query) {
                    $query->orderBy('production_date');
                },
                'dailyPlans.shift',
                'dailyPlans.responsible',
                'productionOrders'
            ])->findOrFail($scheduleId);
            
            \Illuminate\Support\Facades\Log::info('Dados da programação carregados', [
                'schedule_number' => $schedule->schedule_number,
                'product' => $schedule->product->name,
                'status' => $schedule->status,
                'daily_plans_count' => $schedule->dailyPlans->count()
            ]);
            
            // Calcular informações adicionais para o PDF
            $totalProducedQuantity = $schedule->dailyPlans->sum('actual_quantity') ?: 0;
            $totalRejectedQuantity = $schedule->dailyPlans->sum('rejected_quantity') ?: 0;
            $totalEfficiency = $schedule->dailyPlans->avg('efficiency') ?: 0;
            
            // Calcular porcentagem de conclusão
            $completionPercentage = 0;
            if ($schedule->planned_quantity > 0) {
                $completionPercentage = min(100, round(($totalProducedQuantity / $schedule->planned_quantity) * 100));
            }
            
            // Obter dados do usuário para incluir no PDF
            $user = \Illuminate\Support\Facades\Auth::user();
            $currentDate = now()->format('d/m/Y H:i:s');
            
            // Definir os status para exibição no PDF
            $statuses = [
                'draft' => __('messages.draft'),
                'confirmed' => __('messages.confirmed'),
                'in_progress' => __('messages.in_progress'),
                'completed' => __('messages.completed'),
                'cancelled' => __('messages.cancelled')
            ];
            
            // Definir as prioridades para exibição no PDF
            $priorities = [
                'low' => __('messages.low'),
                'medium' => __('messages.medium'),
                'high' => __('messages.high'),
                'urgent' => __('messages.urgent')
            ];
            
            // Gerar o PDF usando a view
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.production-schedule-detail', [
                'schedule' => $schedule,
                'totalProducedQuantity' => $totalProducedQuantity,
                'totalRejectedQuantity' => $totalRejectedQuantity,
                'totalEfficiency' => $totalEfficiency,
                'completionPercentage' => $completionPercentage,
                'user' => $user,
                'currentDate' => $currentDate,
                'statuses' => $statuses,
                'priorities' => $priorities
            ]);
            
            // Configurar o PDF
            $pdf->setPaper('a4', 'portrait');
            
            // Gerar um nome de arquivo baseado no número da programação e data atual
            $filename = 'production_schedule_' . $schedule->schedule_number . '_' . now()->format('Y-m-d_H-i-s') . '.pdf';
            
            \Illuminate\Support\Facades\Log::info('PDF da programação de produção individual gerado com sucesso', [
                'filename' => $filename
            ]);
            
            // Mostrar notificação de sucesso
            $this->dispatch('notify', 
                type: 'success', 
                message: __('messages.pdf_generated_successfully')
            );
            
            \Illuminate\Support\Facades\Log::info('=== FIM DO MÉTODO generateSchedulePdf ===');
            
            // Download do PDF
            return response()->streamDownload(
                fn () => print($pdf->output()),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao gerar PDF da programação de produção individual', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'schedule_id' => $scheduleId
            ]);
            
            $this->dispatch('notify', 
                type: 'error', 
                message: __('messages.error_generating_pdf')
            );
            
            \Illuminate\Support\Facades\Log::info('=== FIM DO MÉTODO generateSchedulePdf (com erro) ===');
            return null;
        }
    }
    
    /**
     * Gerar PDF da listagem de programações de produção com os filtros aplicados
     * 
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function generatePdfList()
    {
        \Illuminate\Support\Facades\Log::info('=== INÍCIO DO MÉTODO generatePdfList ===');
        
        try {
            // Obter as programações de produção com os filtros aplicados
            $schedulesQuery = ProductionSchedule::with(['product', 'line', 'location', 'shifts', 'dailyPlans'])
                ->when($this->search, function($query) {
                    return $query->where(function($q) {
                        $q->where('schedule_number', 'like', '%' . $this->search . '%')
                          ->orWhereHas('product', function($subQuery) {
                              $subQuery->where('name', 'like', '%' . $this->search . '%')
                                      ->orWhere('sku', 'like', '%' . $this->search . '%');
                          });
                    });
                })
                ->when($this->statusFilter, function($query) {
                    return $query->where('status', $this->statusFilter);
                })
                ->when($this->productFilter, function($query) {
                    return $query->where('product_id', $this->productFilter);
                })
                ->orderBy($this->sortField, $this->sortDirection);
            
            // Não paginamos aqui para exportar todos os resultados filtrados
            $schedules = $schedulesQuery->get();
            
            \Illuminate\Support\Facades\Log::info('Gerando PDF da listagem de programações de produção', [
                'quantidade' => $schedules->count(),
                'filtros' => [
                    'search' => $this->search,
                    'status' => $this->statusFilter,
                    'product' => $this->productFilter
                ]
            ]);
            
            // Calcular os totais
            $totalSchedules = $schedules->count();
            $totalPlannedQuantity = $schedules->sum('planned_quantity');
            
            // Obter dados do usuário para incluir no PDF
            $user = \Illuminate\Support\Facades\Auth::user();
            $currentDate = now()->format('d/m/Y H:i:s');
            
            // Carregar dados complementares para a listagem
            $products = Product::orderBy('name')->get();
            $statuses = [
                'draft' => __('messages.draft'),
                'confirmed' => __('messages.confirmed'),
                'in_progress' => __('messages.in_progress'),
                'completed' => __('messages.completed'),
                'cancelled' => __('messages.cancelled')
            ];
            
            // Gerar o PDF usando a view
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.production-schedules-list', [
                'schedules' => $schedules,
                'totalSchedules' => $totalSchedules,
                'totalPlannedQuantity' => $totalPlannedQuantity,
                'user' => $user,
                'currentDate' => $currentDate,
                'statuses' => $statuses,
                'filters' => [
                    'search' => $this->search,
                    'status' => $this->statusFilter ? __('messages.' . $this->statusFilter) : __('messages.all_statuses'),
                    'product' => $this->productFilter ? $products->firstWhere('id', $this->productFilter)->name : __('messages.all_products')
                ]
            ]);
            
            // Configurar o PDF
            $pdf->setPaper('a4', 'landscape');
            
            // Gerar um nome de arquivo baseado na data
            $filename = 'production_schedules_list_' . now()->format('Y-m-d_H-i-s') . '.pdf';
            
            \Illuminate\Support\Facades\Log::info('PDF da listagem de programações de produção gerado com sucesso', [
                'filename' => $filename
            ]);
            
            // Mostrar notificação de sucesso
            $this->dispatch('notify', 
                type: 'success', 
                message: __('messages.pdf_generated_successfully')
            );
            
            \Illuminate\Support\Facades\Log::info('=== FIM DO MÉTODO generatePdfList ===');
            
            // Download do PDF
            return response()->streamDownload(
                fn () => print($pdf->output()),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao gerar PDF da listagem de programações de produção', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatch('notify', 
                type: 'error', 
                message: __('messages.error_generating_pdf')
            );
            
            \Illuminate\Support\Facades\Log::info('=== FIM DO MÉTODO generatePdfList (com erro) ===');
        }
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
        $firstDayOfWeek = (int) date('w', strtotime($firstDayOfMonth));

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
        $daysInMonth = (int) date('t', strtotime($firstDayOfMonth));

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
                ->where(function ($query) use ($start, $end) {
                    $query->where(function ($q) use ($start, $end) {
                        // Eventos que começam no período
                        $q
                            ->where('start_date', '>=', $start)
                            ->where('start_date', '<=', $end);
                    })->orWhere(function ($q) use ($start, $end) {
                        // Eventos que terminam no período
                        $q
                            ->where('end_date', '>=', $start)
                            ->where('end_date', '<=', $end);
                    })->orWhere(function ($q) use ($start, $end) {
                        // Eventos que cruzam o período
                        $q
                            ->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                    });
                })
                ->get();

            // Log do resultado da consulta
            \Illuminate\Support\Facades\Log::info('Agendamentos encontrados para o calendário', [
                'quantidade' => $schedules->count(),
                'agendamentos' => $schedules->map(function ($schedule) {
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
                    usort($day['events'], function ($a, $b) {
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
                message: __('messages.calendar_events_load_error', ['error' => $e->getMessage()]));
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
            $this->reset(['schedule', 'selectedShifts']);
            $schedule = [
                'product_id' => '',
                'schedule_number' => $this->generateScheduleNumber(),
                'start_date' => date('Y-m-d'),
                'end_date' => date('Y-m-d', strtotime('+7 days')),
                'planned_quantity' => '',
                'actual_quantity' => 0,
                'is_delayed' => false,
                'delay_reason' => '',
                'status' => 'draft',
                'priority' => 'medium',
                'responsible' => '',
                'location_id' => '',
                'working_hours_per_day' => 8,
                'hourly_production_rate' => 10,
                'setup_time' => 0,
                'cleanup_time' => 0,
                'line_id' => '',
                'notes' => '',
                'working_days' => [
                    'mon' => true,
                    'tue' => true,
                    'wed' => true,
                    'thu' => true,
                    'fri' => true,
                    'sat' => false,
                    'sun' => false
                ]
            ];

            // Pré-selecionar todos os turnos (sem filtro de ativos)
            $allShifts = Shift::orderBy('name')->get();

            // Debug - registrar os turnos encontrados
            \Illuminate\Support\Facades\Log::info('Todos os turnos encontrados', [
                'count' => $allShifts->count(),
                'shifts' => $allShifts->pluck('name')->toArray(),
                'shifts_ids' => $allShifts->pluck('id')->toArray()
            ]);

            // Inicializar o array de turnos selecionados
            $this->selectedShifts = $allShifts->pluck('id')->toArray();

            // Calcular as horas de trabalho e taxa de produção com base nos turnos selecionados
            $this->calculateWorkingHoursAndRate();

            // Debug - verificar o array de turnos após inicialização
            \Illuminate\Support\Facades\Log::info('Array de turnos inicializado', [
                'shifts_array' => $schedule['shifts'],
                'count' => count($schedule['shifts']),
                'tipos' => array_map(function ($value) {
                    return gettype($value);
                }, $schedule['shifts'])
            ]);

            // Forçando inclusão explícita de shifts na propriedade publica
            $this->shifts = $allShifts;

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
            // Verificar se existem produtos, localizações, linhas de produção e turnos
            $firstProduct = \App\Models\SupplyChain\Product::first();
            $firstLocation = \App\Models\SupplyChain\InventoryLocation::first();
            $firstProductionLine = Line::first();

            // Carregar todos os turnos para exibição na view
            $allShifts = Shift::orderBy('name')->get();
            $firstShiftId = $allShifts->isNotEmpty() ? $allShifts->first()->id : null;

            // Definir a propriedade $shifts usada pela view para exibir os turnos no dropdown
            $this->shifts = $allShifts;

            \Illuminate\Support\Facades\Log::info('Valores para inicialização', [
                'produto' => $firstProduct ? $firstProduct->id : 'Nenhum produto encontrado',
                'localização' => $firstLocation ? $firstLocation->id : 'Nenhuma localização encontrada',
                'linha_producao' => $firstProductionLine ? $firstProductionLine->id : 'Nenhuma linha encontrada',
                'total_turnos' => $allShifts->count(),
                'primeiro_turno_id' => $firstShiftId
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
                'planned_quantity' => '1',  // Valor padrão
                'status' => 'draft',
                'priority' => 'medium',
                'location_id' => $firstLocation ? $firstLocation->id : '',
                'line_id' => $firstProductionLine ? $firstProductionLine->id : '',
                'shift_id' => $firstShiftId,  // Inicializar com o primeiro turno encontrado
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
                'schedule' => $this->schedule,
                'shifts_count' => $this->shifts->count()
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
            message: __('messages.calendar_events_load_error', ['error' => $e->getMessage()]));
    }

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
            $this->reset(['schedule', 'selectedShifts']);

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
                'planned_quantity' => '1',  // Valor padrão
                'status' => 'draft',
                'priority' => 'medium',
                'location_id' => $firstLocation ? $firstLocation->id : '',
                'responsible' => '',
                'notes' => '',
                'working_hours_per_day' => 8,
                'hourly_production_rate' => 10,
                'setup_time' => 30,
                'cleanup_time' => 15,
                'working_days' => [
                    'mon' => true,
                    'tue' => true,
                    'wed' => true,
                    'thu' => true,
                    'fri' => true,
                    'sat' => false,
                    'sun' => false
                ]
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
            $this->resetValidation();
            $this->scheduleId = $id;
            // Garantindo que estamos usando os relacionamentos corretos
            $schedule = ProductionSchedule::with(['product', 'location', 'shifts', 'responsible'])->findOrFail($id);

            // Preencher array schedule com todos os campos do modelo
            $this->schedule = [
                'product_id' => $schedule->product_id,
                'schedule_number' => $schedule->schedule_number,
                'start_date' => $schedule->start_date->format('Y-m-d'),
                'end_date' => $schedule->end_date->format('Y-m-d'),
                'planned_quantity' => $schedule->planned_quantity,
                'actual_quantity' => $schedule->actual_quantity,
                'is_delayed' => $schedule->is_delayed,
                'delay_reason' => $schedule->delay_reason,
                'status' => $schedule->status,
                'priority' => $schedule->priority,
                'responsible_id' => $schedule->responsible_id,
                'location_id' => $schedule->location_id,
                'working_hours_per_day' => $schedule->working_hours_per_day,
                'hourly_production_rate' => $schedule->hourly_production_rate,
                'setup_time' => $schedule->setup_time,
                'cleanup_time' => $schedule->cleanup_time,
                'notes' => $schedule->notes,
                'line_id' => $schedule->line_id,
                'working_days' => $schedule->working_days
            ];

            \Illuminate\Support\Facades\Log::info('Agendamento encontrado para edição', [
                'id' => $id,
                'produto' => $schedule->product_id,
                'linha' => $schedule->line_id,
                'status' => $schedule->status
            ]);

            // Resetar os turnos selecionados para evitar resíduos de edições anteriores
            $this->selectedShifts = [];

            // Carregar turnos associados a esta programação a partir da tabela pivot mrp_production_schedule_shift
            $this->selectedShifts = $schedule->shifts()->pluck('id')->toArray();

            // Calcular as horas de trabalho e taxa de produção com base nos turnos selecionados
            $this->calculateWorkingHoursAndRate();

            // Log detalhado dos turnos encontrados no banco de dados
            \Illuminate\Support\Facades\Log::debug('Turnos encontrados no banco para esta programação:', [
                'schedule_id' => $schedule->id,
                'associated_shifts_count' => count($this->selectedShifts),
                'associated_shifts_ids' => $this->selectedShifts,
                'pivot_table' => 'mrp_production_schedule_shift'
            ]);

            // Forçar a atualização dos valores dos turnos no formulário
            // Adicionar um pequeno delay para garantir que o DOM seja atualizado
            $this->dispatch('refresh-shifts');

            // Re-renderizar o componente para garantir que os valores sejam atualizados
            $this->dispatch('$refresh');

            // Registrar em log os valores que foram atribuídos ao formulário
            \Illuminate\Support\Facades\Log::info('Dados carregados para o formulário de edição', [
                'total_shifts' => count($this->selectedShifts),
                'shift_ids' => $this->selectedShifts,
                'line_id' => $schedule->line_id
            ]);

            // Verificar se os dados foram carregados corretamente
            $this->validateOnly('schedule.shifts');
            $this->validateOnly('schedule.line_id');

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
                message: __('messages.schedule_not_found'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao editar programação', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatch('notify',
                type: 'error',
                title: __('messages.error'),
                message: __('messages.error_loading_schedule', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Fechar o modal de criação/edição de programação
     * Este método é chamado pelo botão Cancelar e pelo evento @click.away no modal
     */
    public function closeCreateEditModal()
    {
        \Illuminate\Support\Facades\Log::info('Fechando modal de criação/edição de programação');
        $this->showModal = false;
        $this->editMode = false;
        $this->scheduleId = null;
        $this->reset(['schedule', 'selectedShifts']);
        $this->resetValidation();
        $this->componentAvailability = [];
        $this->showComponentWarning = false;
        $this->insufficientComponents = [];
        $this->maxQuantityPossible = 0;

        // Disparar evento de fechamento do modal para quem estiver escutando
        $this->dispatch('createEditModalClosed');
    }

    /**
     * Método para alternar a seleção de um turno
     * Método para alternar a seleção de um turno
     * @param int $shiftId ID do turno a ser alternado
     */
    public function toggleShift($shiftId)
    {
        $shiftId = intval($shiftId);

        // Registrar a chamada para debug
        \Illuminate\Support\Facades\Log::debug('Toggle shift chamado', [
            'shift_id' => $shiftId,
            'current_selected' => $this->selectedShifts
        ]);

        // O input checkbox com wire:model já vai atualizar o array selectedShifts
        // Este método é chamado pelo wire:change e podemos realizar ações adicionais aqui se necessário

        // Calcular as horas de trabalho e taxa de produção com base nos turnos selecionados
        $this->calculateWorkingHoursAndRate();

        // Forçar nova renderização do componente
        $this->dispatch('$refresh');
    }

    /**
     * Calcular as horas de trabalho por dia com base nos turnos selecionados,
     * estimar a taxa de produção horária, definir dias de trabalho e sugerir tempos de setup/cleanup
     */
    public function calculateWorkingHoursAndRate()
    {
        // Se não houver turnos selecionados, definir valores padrão
        if (empty($this->selectedShifts)) {
            $this->schedule['working_hours_per_day'] = 8;  // valor padrão
            $this->schedule['hourly_production_rate'] = 10;  // valor padrão
            $this->schedule['setup_time'] = 30;  // valor padrão em minutos
            $this->schedule['cleanup_time'] = 15;  // valor padrão em minutos

            // Dias úteis padrão (segunda a sexta)
            $this->schedule['working_days'] = [
                'mon' => true,
                'tue' => true,
                'wed' => true,
                'thu' => true,
                'fri' => true,
                'sat' => false,
                'sun' => false
            ];

            return;
        }

        // Buscar os turnos selecionados do banco de dados
        $shifts = Shift::whereIn('id', $this->selectedShifts)->get();

        // Calcular as horas de trabalho totais por dia com base nos turnos selecionados
        $totalHours = 0;

        // Para cada turno, calcular as horas totais
        foreach ($shifts as $shift) {
            // Converter os horários de início e fim para objetos Carbon
            $startTime = \Carbon\Carbon::parse($shift->start_time);
            $endTime = \Carbon\Carbon::parse($shift->end_time);

            // Se o horário de término for anterior ao horário de início, assumir que cruza a meia-noite
            if ($endTime < $startTime) {
                $endTime->addDay();
            }

            // Calcular a duração do turno em horas
            $shiftHours = $endTime->diffInMinutes($startTime) / 60;
            $totalHours += $shiftHours;
        }

        // Não modificamos mais os dias de trabalho baseado nos turnos
        // Os dias de trabalho são definidos pelo usuário de forma independente

        // Arredondar para o 0.5 mais próximo
        $totalHours = round($totalHours * 2) / 2;

        // Definir as horas de trabalho por dia
        $this->schedule['working_hours_per_day'] = $totalHours;

        // Não definimos mais os dias de trabalho automaticamente
        // Apenas inicializamos caso ainda não estejam definidos
        if (empty($this->schedule['working_days'])) {
            $this->schedule['working_days'] = [
                'mon' => true,
                'tue' => true,
                'wed' => true,
                'thu' => true,
                'fri' => true,
                'sat' => false,
                'sun' => false
            ];
        }

        // Estimar a taxa de produção horária
        // Se a quantidade planejada estiver definida, calcular uma estimativa baseada nos dias
        if (isset($this->schedule['planned_quantity']) &&
                is_numeric($this->schedule['planned_quantity']) &&
                $this->schedule['planned_quantity'] > 0 &&
                isset($this->schedule['start_date']) &&
                isset($this->schedule['end_date'])) {
            $startDate = \Carbon\Carbon::parse($this->schedule['start_date']);
            $endDate = \Carbon\Carbon::parse($this->schedule['end_date']);
            $workingDaysCount = $endDate->diffInDays($startDate) + 1;  // +1 para incluir o próprio dia de início

            if ($workingDaysCount > 0 && $totalHours > 0) {
                // Calcular taxa horária: quantidade planejada / (dias úteis * horas por dia)
                $hourlyRate = $this->schedule['planned_quantity'] / ($workingDaysCount * $totalHours);
                $this->schedule['hourly_production_rate'] = round($hourlyRate, 2);
            }

            // Calcular tempos de setup e cleanup baseados na quantidade e complexidade
            $complexityFactor = min(max($this->schedule['planned_quantity'] / 1000, 1), 5);  // Fator entre 1 e 5

            // Setup time: entre 15 e 45 minutos dependendo da complexidade e quantidade
            $this->schedule['setup_time'] = round(15 + ($complexityFactor * 6));

            // Cleanup time: entre 10 e 30 minutos dependendo da complexidade e quantidade
            $this->schedule['cleanup_time'] = round(10 + ($complexityFactor * 4));
        } else {
            // Valores padrão se não for possível calcular
            $this->schedule['hourly_production_rate'] = 10;
            $this->schedule['setup_time'] = 30;
            $this->schedule['cleanup_time'] = 15;
        }

        \Illuminate\Support\Facades\Log::debug('Cálculo de horas de trabalho, taxa de produção e dias de trabalho', [
            'turnos_selecionados' => count($this->selectedShifts),
            'total_horas' => $totalHours,
            'taxa_producao' => $this->schedule['hourly_production_rate'],
            'dias_trabalho' => $this->schedule['working_days'],
            'setup_time' => $this->schedule['setup_time'],
            'cleanup_time' => $this->schedule['cleanup_time']
        ]);
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
                message: __('messages.schedule_not_found'));
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
     * Fechar o modal de ordens de produção
     */
    public function closeOrdersModal()
    {
        \Illuminate\Support\Facades\Log::info('Fechando modal de ordens');
        $this->showOrdersModal = false;
        $this->resetOrderForm();
    }


    /**
     * Visualizar planos diários de produção
     */
    public function viewDailyPlans($id)
    {
        try {
            \Illuminate\Support\Facades\Log::info('Visualizando planos diários com informações de falhas', ['id' => $id]);

            $this->viewingSchedule = ProductionSchedule::with(['product', 'location', 'shifts'])
                ->findOrFail($id);

            // Resetar a seleção de turno
            $this->selectedShiftId = null;
            $this->selectedShiftName = null;
            $this->filteredDailyPlans = [];

            // Carregar SOMENTE os turnos associados a esta programação específica
            // O select de shifts no daily plan deve mostrar apenas os shifts associados ao planejamento
            $this->shifts = $this->viewingSchedule->shifts;

            // Log para debug dos turnos carregados
            \Illuminate\Support\Facades\Log::info('Turnos específicos do planejamento', [
                'schedule_id' => $id,
                'total_shifts' => $this->shifts->count(),
                'shifts_ids' => $this->shifts->pluck('id')->toArray(),
                'shifts_names' => $this->shifts->pluck('name')->toArray()
            ]);

            // Não usamos mais o fallback para todos os turnos ativos
            // Queremos mostrar apenas os turnos associados a este planejamento específico

            \Illuminate\Support\Facades\Log::info('Turnos disponíveis carregados', [
                'total_turnos' => $this->shifts->count(),
                'turnos' => $this->shifts->pluck('name')->toArray()
            ]);

            // Carregar os planos diários existentes
            $this->dailyPlans = [];
            $this->filteredDailyPlans = [];

            // Obter todos os planos existentes para este agendamento
            $existingPlans = ProductionDailyPlan::where('schedule_id', $id)
                ->orderBy('production_date')
                ->orderBy('start_time')
                ->get();

            // Gerar um array com TODOS os dias entre a data inicial e final do planejamento
            $startDate = \Carbon\Carbon::parse($this->viewingSchedule->start_date);
            $endDate = \Carbon\Carbon::parse($this->viewingSchedule->end_date);
            $period = \Carbon\CarbonPeriod::create($startDate, $endDate);

            \Illuminate\Support\Facades\Log::info('Gerando planos para todo o período do planejamento', [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'total_dias' => $period->count(),
                'planos_existentes' => $existingPlans->count()
            ]);

            // Agrupar os planos existentes por data para referência rápida
            $existingPlansByDate = [];
            foreach ($existingPlans as $plan) {
                $dateStr = $plan->production_date->format('Y-m-d');
                if (!isset($existingPlansByDate[$dateStr])) {
                    $existingPlansByDate[$dateStr] = [];
                }
                $existingPlansByDate[$dateStr][] = $plan;
            }

            \Illuminate\Support\Facades\Log::info('Planos existentes agrupados por data', [
                'total_datas' => count($existingPlansByDate),
                'datas' => array_keys($existingPlansByDate)
            ]);

            // Criar entradas para todos os dias, usando dados existentes quando disponíveis
            $index = 0;
            foreach ($period as $date) {
                $dateKey = $date->format('Y-m-d');

                // Verificar se existem planos para esta data
                if (isset($existingPlansByDate[$dateKey])) {
                    // Processar todos os planos existentes para esta data
                    foreach ($existingPlansByDate[$dateKey] as $plan) {
                        $this->dailyPlans[$index] = [
                            'id' => $plan->id,
                            'production_date' => $dateKey,
                            'start_time' => $plan->start_time,
                            'end_time' => $plan->end_time,
                            'planned_quantity' => $plan->planned_quantity,
                            'actual_quantity' => $plan->actual_quantity,
                            'defect_quantity' => $plan->defect_quantity,
                            'has_breakdown' => $plan->has_breakdown,
                            'breakdown_minutes' => $plan->breakdown_minutes,
                            'failure_category_id' => $plan->failure_category_id,
                            'failure_root_causes' => $plan->failure_root_causes,
                            'status' => $plan->status,
                            'notes' => $plan->notes,
                            'shift_id' => $plan->shift_id,
                        ];
                        $index++;
                    }
                } else {
                    // Criar nova entrada para este dia
                    $this->dailyPlans[$index] = [
                        'id' => null,
                        'production_date' => $dateKey,
                        'start_time' => null,
                        'end_time' => null,
                        'planned_quantity' => isset($this->viewingSchedule) ? $this->calculateDefaultQuantity() : 0,
                        'actual_quantity' => 0,
                        'defect_quantity' => 0,
                        'has_breakdown' => false,
                        'breakdown_minutes' => 0,
                        'failure_category_id' => null,
                        'failure_root_causes' => null,
                        'status' => 'pending',
                        'notes' => '',
                        'shift_id' => null,
                    ];
                    $index++;
                }
            }

            // Calcular o impacto das falhas na produção
            $this->calculateFailureImpact();

            // Garantir que os daily plans estejam ordenados por data
            usort($this->dailyPlans, function ($a, $b) {
                return strcmp($a['production_date'], $b['production_date']);
            });

            // Verificar disponibilidade de componentes (se aplicável)
            if (method_exists($this, 'checkComponentAvailability')) {
                $this->checkComponentAvailability();
            }

            // Ativar o modal após toda a preparação estar concluída
            $this->showDailyPlansModal = true;

            // Disparar evento para inicializar os gráficos
            $this->dispatch('dailyPlansModalOpened');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao carregar planos diários', [
                'id' => $id,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatch('notify',
                type: 'error',
                title: __('messages.error'),
                message: __('messages.failed_to_load_daily_plans') . ": {$e->getMessage()}");
        }
    }

    /**
     * Criar planos diários para a programação com base nos dias de trabalho marcados e turnos
     */
    public function createDailyPlansForWorkingDays($scheduleId)
    {
        try {
            \Illuminate\Support\Facades\Log::info('Criando planos diários para programação', ['id' => $scheduleId]);

            // Buscar a programação com seus turnos associados
            $schedule = ProductionSchedule::with(['shifts'])->findOrFail($scheduleId);

            // Map dos dias da semana
            $dayMap = [
                0 => 'sun',
                1 => 'mon',
                2 => 'tue',
                3 => 'wed',
                4 => 'thu',
                5 => 'fri',
                6 => 'sat',
            ];

            // Data de início e fim
            $startDate = \Carbon\Carbon::parse($schedule->start_date);
            $endDate = \Carbon\Carbon::parse($schedule->end_date);

            // Dias de trabalho do agendamento
            $workingDays = $schedule->working_days ?? [
                'mon' => true,
                'tue' => true,
                'wed' => true,
                'thu' => true,
                'fri' => true,
                'sat' => false,
                'sun' => false,
            ];

            // Calcular quantidade total de dias úteis
            $currentDate = $startDate->copy();
            $totalWorkingDays = 0;
            $workingDates = [];

            while ($currentDate->lte($endDate)) {
                $dayOfWeek = $currentDate->dayOfWeek;  // 0 = Dom, 6 = Sáb
                $dayKey = $dayMap[$dayOfWeek];

                if (isset($workingDays[$dayKey]) && $workingDays[$dayKey]) {
                    $totalWorkingDays++;
                    $workingDates[] = $currentDate->format('Y-m-d');
                }

                $currentDate->addDay();
            }

            // Se não houver dias úteis, usar todos os dias
            if ($totalWorkingDays === 0) {
                $totalWorkingDays = $startDate->diffInDays($endDate) + 1;
                $workingDates = [];
                $currentDate = $startDate->copy();

                while ($currentDate->lte($endDate)) {
                    $workingDates[] = $currentDate->format('Y-m-d');
                    $currentDate->addDay();
                }
            }

            // Verificar se o agendamento tem turnos associados
            $shifts = $schedule->shifts;

            // Inicializar planos diários
            $this->dailyPlans = [];

            // Calcular capacidade diária com base em horas de trabalho e taxa de produção
            $workingHoursPerDay = $schedule->working_hours_per_day ?? 8;  // Padrão: 8 horas/dia
            $hourlyProductionRate = $schedule->hourly_production_rate ?? 0;  // Unidades por hora
            $setupTime = $schedule->setup_time ?? 0;  // Em minutos
            $cleanupTime = $schedule->cleanup_time ?? 0;  // Em minutos

            // Calcular capacidade base diária
            $baseCapacity = $workingHoursPerDay * $hourlyProductionRate;

            // Ajustar capacidade considerando tempo de setup e cleanup
            $dailyCapacity = $baseCapacity;
            if ($setupTime > 0 || $cleanupTime > 0) {
                // Convertendo minutos de setup/cleanup para horas e ajustando a capacidade
                $totalNonProductiveMinutes = $setupTime + $cleanupTime;
                $nonProductiveHours = $totalNonProductiveMinutes / 60;

                if ($workingHoursPerDay > 0) {  // Evitar divisão por zero
                    $productiveRatio = max(0, ($workingHoursPerDay - $nonProductiveHours) / $workingHoursPerDay);
                    $dailyCapacity = $baseCapacity * $productiveRatio;
                }
            }

            // Verificar se a capacidade total pode cobrir a quantidade planejada
            $totalCapacity = $dailyCapacity * $totalWorkingDays;

            // Log dos cálculos de capacidade para debug
            \Illuminate\Support\Facades\Log::info('Cálculos de capacidade diária', [
                'working_hours_per_day' => $workingHoursPerDay,
                'hourly_production_rate' => $hourlyProductionRate,
                'setup_time' => $setupTime,
                'cleanup_time' => $cleanupTime,
                'base_capacity' => $baseCapacity,
                'daily_capacity' => $dailyCapacity,
                'total_capacity' => $totalCapacity,
                'planned_quantity' => $schedule->planned_quantity
            ]);

            // Calcular quantidade diária com base na capacidade
            $dailyQuantity = 0;

            if ($dailyCapacity > 0 && $totalCapacity >= $schedule->planned_quantity) {
                // Capacidade suficiente - distribuir igualmente
                $dailyQuantity = $schedule->planned_quantity / $totalWorkingDays;
            } elseif ($dailyCapacity > 0) {
                // Capacidade insuficiente - usar capacidade máxima diária
                $dailyQuantity = $dailyCapacity;

                \Illuminate\Support\Facades\Log::warning('Capacidade insuficiente para quantidade planejada', [
                    'daily_capacity' => $dailyCapacity,
                    'total_capacity' => $totalCapacity,
                    'planned_quantity' => $schedule->planned_quantity,
                    'scheduling_days_needed' => ceil($schedule->planned_quantity / $dailyCapacity)
                ]);
            } else {
                // Fallback se dados de capacidade não forem válidos
                $dailyQuantity = $schedule->planned_quantity / $totalWorkingDays;

                \Illuminate\Support\Facades\Log::warning('Dados de capacidade inválidos, usando divisão igual', [
                    'daily_quantity' => $dailyQuantity
                ]);
            }

            // Se não houver turnos associados, criar um plano por dia sem turno específico
            if ($shifts->isEmpty()) {
                \Illuminate\Support\Facades\Log::info('Sem turnos associados, criando planos diários genéricos');

                foreach ($workingDates as $index => $date) {
                    $this->dailyPlans[] = [
                        'id' => null,
                        'production_date' => $date,
                        'start_time' => $schedule->start_time,
                        'end_time' => $schedule->end_time,
                        'planned_quantity' => $dailyQuantity,
                        'actual_quantity' => 0,
                        'defect_quantity' => 0,
                        'has_breakdown' => false,
                        'breakdown_minutes' => 0,
                        'failure_category_id' => null,
                        'failure_root_causes' => null,
                        'status' => 'pending',
                        'notes' => '',
                        'shift_id' => null,
                    ];

                    // Log detalhado da criação do plano para monitoramento
                    \Illuminate\Support\Facades\Log::info('Criando plano diário com quantidade planejada', [
                        'date' => $date,
                        'original_calculated_quantity' => $schedule->planned_quantity / $totalWorkingDays,
                        'rounded_quantity' => $dailyQuantity
                    ]);
                }
            } else {
                // Se houver turnos, criar um plano para cada dia e turno
                \Illuminate\Support\Facades\Log::info('Criando planos diários por turno', [
                    'total_turnos' => $shifts->count(),
                    'turnos' => $shifts->pluck('name')->toArray()
                ]);

                // Calcular quantidade por turno (distribuída igualmente entre os turnos para cada dia)
                $shiftsCount = $shifts->count();
                $quantityPerShift = $dailyQuantity / $shiftsCount;

                foreach ($workingDates as $dateIndex => $date) {
                    foreach ($shifts as $shiftIndex => $shift) {
                        // Ajustar o horário de início e fim de acordo com o turno
                        $startTime = $shift->start_time ?? $schedule->start_time;
                        $endTime = $shift->end_time ?? $schedule->end_time;

                        // O último turno no último dia pode receber a diferença para completar o total exato
                        $plannedQuantity = $quantityPerShift;
                        if ($dateIndex == count($workingDates) - 1 && $shiftIndex == $shifts->count() - 1) {
                            // Calcular a soma de todas as quantidades já atribuídas
                            $totalAssigned = count($this->dailyPlans) * $quantityPerShift;
                            $difference = $schedule->planned_quantity - $totalAssigned;
                            $plannedQuantity = $quantityPerShift + $difference;
                        }

                        $this->dailyPlans[] = [
                            'id' => null,
                            'production_date' => $date,
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                            'planned_quantity' => $plannedQuantity,
                            'actual_quantity' => 0,
                            'defect_quantity' => 0,
                            'has_breakdown' => false,
                            'breakdown_minutes' => 0,
                            'failure_category_id' => null,
                            'failure_root_causes' => null,
                            'status' => 'pending',
                            'notes' => '',
                            'shift_id' => $shift->id,
                            'shift_name' => $shift->name,  // Armazenar o nome para exibição
                        ];
                    }
                }
            }

            \Illuminate\Support\Facades\Log::info('Planos diários criados com sucesso', [
                'total_dias' => count($workingDates),
                'total_planos' => count($this->dailyPlans),
                'quantidade_diaria' => $dailyQuantity
            ]);

            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao criar planos diários', [
                'id' => $scheduleId,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }

    /**
     * Fechar o modal de planos diários
     */
    public function closeDailyPlansModal()
    {
        \Illuminate\Support\Facades\Log::info('Fechando modal de planos diários');
        $this->showDailyPlansModal = false;
        $this->viewingDailyPlans = false;
        $this->dailyPlans = [];
        $this->filteredDailyPlans = [];
        $this->selectedShiftId = null;
        $this->selectedShiftName = null;
        $this->editingDailyPlan = null;
    }

    /**
     * Método para selecionar e filtrar planos diários por turno
     *
     * @param string $shift_id ID do turno selecionado
     * @return void
     */
    public function selectShift($shift_id)
    {
        try {
            \Illuminate\Support\Facades\Log::info('Turno selecionado', ['shift_id' => $shift_id]);

            // Se não há turnos carregados, não podemos filtrar
            if (!property_exists($this, 'shifts') || !isset($this->shifts) || $this->shifts->isEmpty()) {
                \Illuminate\Support\Facades\Log::error('Erro ao selecionar turno', [
                    'shift_id' => $shift_id,
                    'erro' => 'Property [$shifts] not found on component: [mrp.production-scheduling]'
                ]);
                return;
            }

            $this->selectedShiftId = $shift_id;

            // Log adicional para rastrear o valor do shift_id
            \Illuminate\Support\Facades\Log::info('Shift ID definido explicitamente', [
                'shift_id_definido' => $this->selectedShiftId,
                'valor_recebido' => $shift_id
            ]);

            // Encontrar o nome do turno para exibição
            $selectedShift = $this->shifts->firstWhere('id', $shift_id);
            if ($selectedShift) {
                $this->selectedShiftName = $selectedShift->name;

                // Filtrar os planos diários pelo turno selecionado
                if (isset($this->dailyPlans) && count($this->dailyPlans) > 0) {
                    $this->filteredDailyPlans = collect($this->dailyPlans)
                        ->filter(function ($plan) use ($shift_id) {
                            return $plan['shift_id'] == $shift_id;
                        })
                        ->toArray();
                }
            } else {
                $this->selectedShiftName = null;
                $this->filteredDailyPlans = [];
                \Illuminate\Support\Facades\Log::error('Erro ao selecionar turno', [
                    'shift_id' => $shift_id,
                    'erro' => 'Turno não encontrado na lista de turnos disponíveis'
                ]);
            }

            // Recarregar dados do planejamento selecionado para atualizar o modal
            if ($this->scheduleId) {
                \Illuminate\Support\Facades\Log::info('Carregando dados para modal', [
                    'schedule_id' => $this->scheduleId,
                    'schedule_loaded' => true
                ]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao selecionar turno', [
                'shift_id' => $shift_id,
                'erro' => $e->getMessage()
            ]);
        }
    }

    /**
     * Método chamado quando o turno é selecionado no dropdown
     */
    public function updatedSelectedShiftId($value)
    {
        try {
            if (empty($value)) {
                $this->selectedShiftId = null;
                $this->selectedShiftName = null;
                $this->filteredDailyPlans = [];
                return;
            }

            \Illuminate\Support\Facades\Log::info('Turno selecionado', [
                'shift_id' => $value,
                'from_livewire_binding' => true
            ]);

            // Obter o nome do turno selecionado
            $selectedShift = $this->shifts->firstWhere('id', $value);
            $this->selectedShiftName = $selectedShift ? $selectedShift->name : '';

            // Limpar planos filtrados para criar novos
            $this->filteredDailyPlans = [];

            // Criar planos diários para este turno
            $this->createDailyPlansForSelectedShift();

            // Filtrar os planos diários pelo turno selecionado
            $this->filterDailyPlansByShift();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao selecionar turno', [
                'shift_id' => $value,
                'erro' => $e->getMessage()
            ]);

            $this->dispatch('notify',
                type: 'error',
                title: __('messages.error'),
                message: __('messages.error_selecting_shift'));
        }
    }

    /**
     * Atualizar o array principal de planos diários com base no plano filtrado
     */
    private function updateMainDailyPlansArray($planData)
    {
        // Se o plano não tem ID ainda, não há o que atualizar
        if (!isset($planData['id']) || !$planData['id']) {
            return;
        }

        // Procurar o índice deste plano no array principal
        $mainIndex = null;
        foreach ($this->dailyPlans as $index => $plan) {
            if (isset($plan['id']) && $plan['id'] == $planData['id']) {
                $mainIndex = $index;
                break;
            }
        }

        // Se encontrado, atualizar
        if ($mainIndex !== null) {
            $this->dailyPlans[$mainIndex] = $planData;
        } else {
            // Se não encontrado, adicionar ao final
            $this->dailyPlans[] = $planData;
        }
    }

    /**
     * Filtrar os planos diários pelo turno selecionado
     */
    public function filterDailyPlansByShift()
    {
        if (!$this->selectedShiftId) {
            $this->filteredDailyPlans = [];
            return;
        }

        // Log para rastrear os planos antes da filtragem
        \Illuminate\Support\Facades\Log::info('Planos diários antes da filtragem', [
            'total_planos' => count($this->dailyPlans),
            'planos_com_shift_id' => count(array_filter($this->dailyPlans, function ($p) {
                return isset($p['shift_id']) && $p['shift_id'];
            })),
            'planos_com_shift_id_selecionado' => count(array_filter($this->dailyPlans, function ($p) {
                return isset($p['shift_id']) && $p['shift_id'] == $this->selectedShiftId;
            }))
        ]);

        // Modificado para mostrar todos os planos (com ou sem shift_id) quando um turno é selecionado
        $this->filteredDailyPlans = array_values(array_filter($this->dailyPlans, function ($plan) {
            // Se o plano já tem um shift_id definido, verificar se corresponde ao selecionado
            if (isset($plan['shift_id']) && $plan['shift_id']) {
                $matches = $plan['shift_id'] == $this->selectedShiftId;
                if ($matches) {
                    \Illuminate\Support\Facades\Log::debug('Plano encontrado para o turno selecionado', [
                        'data' => $plan['production_date'] ?? 'desconhecida',
                        'shift_id' => $plan['shift_id']
                    ]);
                }
                return $matches;
            }

            // Para planos sem shift_id, incluir todos (removendo o filtro)
            \Illuminate\Support\Facades\Log::debug('Plano sem shift_id incluído no resultado', [
                'data' => $plan['production_date'] ?? 'desconhecida'
            ]);
            return true;  // Incluir todos os planos sem shift_id no resultado
        }));

        // Se não houver planos para este turno, criar novos para cada dia
        if (empty($this->filteredDailyPlans) && $this->viewingSchedule) {
            \Illuminate\Support\Facades\Log::info('Criando planos diários para o turno selecionado', [
                'shift_id' => $this->selectedShiftId,
                'schedule_id' => $this->viewingSchedule->id
            ]);

            // Obter os dias de trabalho da programação
            $this->createDailyPlansForSelectedShift();
        }

        \Illuminate\Support\Facades\Log::info('Planos diários filtrados pelo turno', [
            'shift_id' => $this->selectedShiftId,
            'total_planos' => count($this->filteredDailyPlans)
        ]);
    }

    /**
     * Criar planos diários para o turno selecionado
     */
    private function createDailyPlansForSelectedShift()
    {
        // Log detalhado para diagnóstico do problema de shift_id
        \Illuminate\Support\Facades\Log::info('Iniciando criação de planos diários para o turno selecionado', [
            'selectedShiftId' => $this->selectedShiftId,
            'viewingSchedule' => $this->viewingSchedule ? $this->viewingSchedule->id : null
        ]);

        if (!$this->selectedShiftId || !$this->viewingSchedule) {
            \Illuminate\Support\Facades\Log::warning('Não foi possível criar planos: turno ou programação não definidos', [
                'selectedShiftId' => $this->selectedShiftId,
                'viewingSchedule' => $this->viewingSchedule ? true : false
            ]);
            return;
        }

        try {
            // Map dos dias da semana
            $dayMap = [
                0 => 'sun',
                1 => 'mon',
                2 => 'tue',
                3 => 'wed',
                4 => 'thu',
                5 => 'fri',
                6 => 'sat',
            ];

            // Data de início e fim
            $startDate = \Carbon\Carbon::parse($this->viewingSchedule->start_date);
            $endDate = \Carbon\Carbon::parse($this->viewingSchedule->end_date);

            // Dias de trabalho do agendamento
            $workingDays = $this->viewingSchedule->working_days ?? [
                'mon' => true,
                'tue' => true,
                'wed' => true,
                'thu' => true,
                'fri' => true,
                'sat' => false,
                'sun' => false,
            ];

            // Calcular quantidade total de dias úteis
            $currentDate = $startDate->copy();
            $totalWorkingDays = 0;
            $workingDates = [];

            while ($currentDate->lte($endDate)) {
                $dayOfWeek = $currentDate->dayOfWeek;  // 0 = Dom, 6 = Sáb
                $dayKey = $dayMap[$dayOfWeek];

                if (isset($workingDays[$dayKey]) && $workingDays[$dayKey]) {
                    $totalWorkingDays++;
                    $workingDates[] = $currentDate->format('Y-m-d');
                }

                $currentDate->addDay();
            }

            // Se não houver dias úteis, usar todos os dias
            if ($totalWorkingDays === 0) {
                $totalWorkingDays = $startDate->diffInDays($endDate) + 1;
                $workingDates = [];
                $currentDate = $startDate->copy();

                while ($currentDate->lte($endDate)) {
                    $workingDates[] = $currentDate->format('Y-m-d');
                    $currentDate->addDay();
                }
            }

            // Obter o turno selecionado
            $selectedShift = $this->shifts->firstWhere('id', $this->selectedShiftId);

            if (!$selectedShift) {
                throw new \Exception('Turno não encontrado');
            }

            // Calcular capacidade diária com base em horas de trabalho e taxa de produção
            $workingHoursPerDay = $this->viewingSchedule->working_hours_per_day ?? 8;  // Padrão: 8 horas/dia
            $hourlyProductionRate = $this->viewingSchedule->hourly_production_rate ?? 0;  // Unidades por hora
            $setupTime = $this->viewingSchedule->setup_time ?? 0;  // Em minutos
            $cleanupTime = $this->viewingSchedule->cleanup_time ?? 0;  // Em minutos

            // Calcular capacidade base diária
            $baseCapacity = $workingHoursPerDay * $hourlyProductionRate;

            // Ajustar capacidade considerando tempo de setup e cleanup
            $dailyCapacity = $baseCapacity;
            if ($setupTime > 0 || $cleanupTime > 0) {
                // Convertendo minutos para horas
                $totalNonProductiveMinutes = $setupTime + $cleanupTime;
                $nonProductiveHours = $totalNonProductiveMinutes / 60;

                if ($workingHoursPerDay > 0) {  // Evitar divisão por zero
                    $productiveRatio = max(0, ($workingHoursPerDay - $nonProductiveHours) / $workingHoursPerDay);
                    $dailyCapacity = $baseCapacity * $productiveRatio;
                }
            }

            // Verificar se a capacidade total pode cobrir a quantidade planejada
            $totalCapacity = $dailyCapacity * $totalWorkingDays;

            // Log dos cálculos de capacidade para debug
            \Illuminate\Support\Facades\Log::info('Cálculos de capacidade diária no modal de planos diários', [
                'working_hours_per_day' => $workingHoursPerDay,
                'hourly_production_rate' => $hourlyProductionRate,
                'setup_time' => $setupTime,
                'cleanup_time' => $cleanupTime,
                'base_capacity' => $baseCapacity,
                'daily_capacity' => $dailyCapacity,
                'total_capacity' => $totalCapacity,
                'planned_quantity' => $this->viewingSchedule->planned_quantity
            ]);

            // Algoritmo simplificado para calcular a quantidade diária
            $totalPlannedQuantity = $this->viewingSchedule->planned_quantity;
            
            // Obter o número total de turnos
            $totalShifts = $this->shifts->count();
            
            // Log do número total de turnos e quantidade total planejada
            \Illuminate\Support\Facades\Log::info('Informações do cálculo de quantidade planejada', [
                'schedule_number' => $this->viewingSchedule->schedule_number,
                'total_planned_quantity' => $totalPlannedQuantity,
                'total_working_days' => $totalWorkingDays,
                'total_shifts' => $totalShifts
            ]);
            
            // Calcular a quantidade total de operações (dias * turnos)
            $totalOperations = $totalWorkingDays * $totalShifts;
            
            // Calcular primeiro a quantidade por dia
            $quantityPerDay = 0;
            if ($totalWorkingDays > 0) {
                $quantityPerDay = $totalPlannedQuantity / $totalWorkingDays;
            }
            
            // Depois, dividir a quantidade diária pelo número de turnos
            $quantityPerOperation = 0;
            if ($totalShifts > 0) {
                $quantityPerOperation = $quantityPerDay / $totalShifts;
            }
            
            \Illuminate\Support\Facades\Log::info('Detalhamento da distribuição de quantidade por turnos', [
                'schedule_number' => $this->viewingSchedule->schedule_number,
                'quantidade_total' => $totalPlannedQuantity,
                'dias_uteis' => $totalWorkingDays,
                'quantidade_por_dia' => $quantityPerDay,
                'numero_turnos' => $totalShifts,
                'quantidade_por_turno' => $quantityPerOperation
            ]);
            
            // Garantir que temos apenas 2 casas decimais, arredondando para cima
            // IMPORTANTE: Usar a quantidade por turno, não por dia!
            $dailyQuantity = ceil($quantityPerOperation * 100) / 100;
            
            \Illuminate\Support\Facades\Log::info('Cálculo de quantidade por turno simplificado', [
                'schedule_number' => $this->viewingSchedule->schedule_number,
                'total_operations' => $totalOperations,
                'quantity_per_operation' => $quantityPerOperation,
                'daily_quantity_rounded' => $dailyQuantity,
                'selected_shift' => $selectedShift->name,
                'selected_shift_id' => $selectedShift->id
            ]);
            
            // Verificar se a soma total não vai exceder a quantidade planejada
            $totalDistributed = $dailyQuantity * $totalOperations;
            if ($totalDistributed > $totalPlannedQuantity) {
                // Se exceder, ajustar o valor ligeiramente para baixo
                $adjustmentFactor = $totalPlannedQuantity / $totalDistributed;
                $dailyQuantity = floor($dailyQuantity * $adjustmentFactor * 100) / 100;
                
                \Illuminate\Support\Facades\Log::info('Ajuste de quantidade para não exceder o total', [
                    'total_distributed_before' => $totalDistributed,
                    'adjustment_factor' => $adjustmentFactor,
                    'daily_quantity_adjusted' => $dailyQuantity,
                    'total_distributed_after' => $dailyQuantity * $totalOperations
                ]);
            }
            
            // Verificar se a quantidade não excede a capacidade diária do turno
            if ($dailyCapacity > 0 && $dailyQuantity > $dailyCapacity) {
                \Illuminate\Support\Facades\Log::warning('Quantidade calculada excede capacidade diária do turno, ajustando para capacidade máxima', [
                    'calculated_quantity' => $dailyQuantity,
                    'max_capacity' => $dailyCapacity
                ]);
                $dailyQuantity = $dailyCapacity;
            }

            // Verificar se já existem planos no banco de dados para esta programação e turno
            $existingDbPlans = ProductionDailyPlan::where('schedule_id', $this->viewingSchedule->id)
                ->where('shift_id', $this->selectedShiftId)
                ->get()
                ->keyBy(function ($plan) {
                    return $plan->production_date->format('Y-m-d');
                });

            \Illuminate\Support\Facades\Log::info('Planos existentes no banco de dados para o turno selecionado', [
                'shift_id' => $this->selectedShiftId,
                'total_planos_db' => $existingDbPlans->count(),
                'schedule_id' => $this->viewingSchedule->id
            ]);

            // Criar um plano para cada dia com o turno selecionado
            foreach ($workingDates as $date) {
                // Verificar se já existe um plano no array local para este dia e turno
                $existingLocalPlan = array_filter($this->dailyPlans, function ($plan) use ($date) {
                    return $plan['production_date'] == $date &&
                        $plan['shift_id'] == $this->selectedShiftId;
                });

                if (!empty($existingLocalPlan)) {
                    // Se já existe no array local, adicionar aos planos filtrados
                    $this->filteredDailyPlans[] = reset($existingLocalPlan);
                    continue;
                }

                // Verificar se já existe no banco de dados
                if (isset($existingDbPlans[$date])) {
                    // Se existe no banco, usar os dados do banco
                    $dbPlan = $existingDbPlans[$date];
                    $this->filteredDailyPlans[] = [
                        'id' => $dbPlan->id,
                        'production_date' => $date,
                        'planned_quantity' => $dbPlan->planned_quantity,
                        'actual_quantity' => $dbPlan->actual_quantity,
                        'defect_quantity' => $dbPlan->defect_quantity,
                        'has_breakdown' => $dbPlan->has_breakdown,
                        'breakdown_minutes' => $dbPlan->breakdown_minutes,
                        'failure_category_id' => $dbPlan->failure_category_id,
                        'failure_root_causes' => $dbPlan->failure_root_causes,
                        'status' => $dbPlan->status,
                        'notes' => $dbPlan->notes,
                        'shift_id' => $dbPlan->shift_id,
                    ];
                    continue;
                }

                // Definir horários com base no turno selecionado
                $selectedShift = $this->shifts->firstWhere('id', $this->selectedShiftId);
                $startTime = $selectedShift ? $selectedShift->start_time : null;
                $endTime = $selectedShift ? $selectedShift->end_time : null;

                // Criar um novo plano virtual (não salvo no banco) para este dia e turno
                // IMPORTANTE: Dividir explicitamente a quantidade diária pelo número de turnos
                // A quantidade diária total para este dia é:
                $totalDailyQuantity = $totalPlannedQuantity / $totalWorkingDays;
                
                // A quantidade para este turno específico é:
                $quantityForThisShift = $totalDailyQuantity / $totalShifts;
                
                // Arredondar para 2 casas decimais
                $plannedQuantityRounded = round($quantityForThisShift, 2);
                
                // Log detalhado para monitorar os valores de quantidade planejada
                \Illuminate\Support\Facades\Log::info('VALORES DETALHADOS DO CÁLCULO DE QUANTIDADE PLANEJADA', [
                    'data' => $date,
                    'turno_id' => $this->selectedShiftId,
                    'turno_nome' => $selectedShift ? $selectedShift->name : 'N/A',
                    'quantidade_total_programada' => $totalPlannedQuantity,
                    'dias_trabalho_total' => $totalWorkingDays,
                    'quantidade_diaria_total' => $totalDailyQuantity,
                    'total_turnos' => $totalShifts,
                    'quantidade_por_turno_sem_arredondamento' => $quantityForThisShift,
                    'quantidade_por_turno_arredondada' => $plannedQuantityRounded,
                    'soma_total_todos_turnos' => $plannedQuantityRounded * $totalShifts * $totalWorkingDays,
                    'calculo_explicado' => "$totalPlannedQuantity ÷ $totalWorkingDays = $totalDailyQuantity por dia, depois $totalDailyQuantity ÷ $totalShifts = $quantityForThisShift por turno"
                ]);
                
                $this->filteredDailyPlans[] = [
                    'id' => null,
                    'production_date' => $date,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'planned_quantity' => $plannedQuantityRounded,
                    'actual_quantity' => 0,
                    'defect_quantity' => 0,
                    'has_breakdown' => false,
                    'breakdown_minutes' => 0,
                    'failure_category_id' => null,
                    'failure_root_causes' => null,
                    'status' => 'pending',
                    'notes' => '',
                    'shift_id' => $this->selectedShiftId,
                ];
            }

            \Illuminate\Support\Facades\Log::info('Planos diários criados para o turno selecionado', [
                'shift_id' => $this->selectedShiftId,
                'total_dias' => count($workingDates),
                'total_planos' => count($this->filteredDailyPlans)
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao criar planos diários para o turno', [
                'shift_id' => $this->selectedShiftId,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Recalcular planos diários com base nos parâmetros atuais da programação
     */
    public function recalculatePlans()
    {
        try {
            if (!$this->viewingSchedule || !$this->selectedShiftId) {
                $this->dispatch('notify',
                    type: 'error',
                    title: __('messages.error'),
                    message: __('messages.select_shift_first'));
                return;
            }

            \Illuminate\Support\Facades\Log::info('Recalculando planos diários', [
                'schedule_id' => $this->viewingSchedule->id,
                'shift_id' => $this->selectedShiftId
            ]);

            // Forçar um recálculo de todos os planos com base na Configuration Schedule original
            // Isso utiliza o modelo ProductionSchedule que implementamos antes
            $schedule = ProductionSchedule::find($this->viewingSchedule->id);

            if (!$schedule) {
                throw new \Exception('Programação não encontrada');
            }

            // Recalcular apenas os planos deste turno
            $result = $schedule->recalculateDailyPlans(false);

            // Recarregar os dados atualizados
            $this->viewDailyPlans($this->viewingSchedule->id);
            $this->updatedSelectedShiftId($this->selectedShiftId);

            // Notificar o usuário
            if (isset($result['success']) && $result['success']) {
                $this->dispatch('notify',
                    type: 'success',
                    title: __('messages.success'),
                    message: __('messages.plans_recalculated_successfully'));
            } else {
                $this->dispatch('notify',
                    type: 'warning',
                    title: __('messages.warning'),
                    message: $result['error'] ?? __('messages.error_recalculating_plans'));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao recalcular planos diários', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatch('notify',
                type: 'error',
                title: __('messages.error'),
                message: __('messages.failed_to_recalculate_plans') . ": {$e->getMessage()}");
        }
    }

    /**
     * Salvar um plano diário específico - recebe o índice do plano a ser salvo
     *
     * @param int $index Índice do plano a ser salvo
     * @return bool Retorna true se o salvamento foi bem sucedido
     */
    public function saveDailyPlan($index)
    {
        try {
            // Verificar se temos os dados básicos necessários
            if (!$this->viewingSchedule || !$this->selectedShiftId) {
                $this->dispatch('notify',
                    type: 'error',
                    title: __('messages.error'),
                    message: __('messages.select_shift_first'));
                return false;
            }

            // Verificar se o plano no índice especificado existe
            if (!isset($this->filteredDailyPlans[$index])) {
                $this->dispatch('notify',
                    type: 'error',
                    title: __('messages.error'),
                    message: __('messages.plan_not_found'));
                return false;
            }

            // Obter os dados do plano específico
            $plan = $this->filteredDailyPlans[$index];
            $dateKey = $plan['production_date'];

            \Illuminate\Support\Facades\Log::info('Salvando plano diário específico', [
                'index' => $index,
                'date' => $dateKey,
                'schedule_id' => $this->viewingSchedule->id,
                'shift_id' => $this->selectedShiftId
            ]);

            // Verificar se já existe um plano para esta data e turno
            $existingPlan = ProductionDailyPlan::where('schedule_id', $this->viewingSchedule->id)
                ->where('shift_id', $this->selectedShiftId)
                ->where('production_date', $dateKey)
                ->first();

            \Illuminate\Support\Facades\Log::info('Verificando plano existente para salvar', [
                'shift_id_selecionado' => $this->selectedShiftId,
                'plano_existente' => $existingPlan ? true : false
            ]);
            if (isset($plan['id']) && $plan['id']) {
                // Atualizar plano existente
                $dailyPlan = ProductionDailyPlan::find($plan['id']);
                if ($dailyPlan) {
                    $dailyPlan->update([
                        'planned_quantity' => $plan['planned_quantity'],
                        'actual_quantity' => $plan['actual_quantity'],
                        'defect_quantity' => $plan['defect_quantity'],
                        'has_breakdown' => $plan['has_breakdown'],
                        'breakdown_minutes' => $plan['breakdown_minutes'] ?? 0,
                        'failure_category_id' => $plan['failure_category_id'] ?? null,
                        'failure_root_causes' => $plan['failure_root_causes'] ?? null,
                        'status' => $this->validatePlanStatus($plan['status'] ?? 'pending'),
                        'notes' => $plan['notes'] ?? '',
                        'shift_id' => $plan['shift_id'] ?? $this->selectedShiftId  // Garantir que o shift_id seja atualizado
                    ]);

                    \Illuminate\Support\Facades\Log::info('Plano atualizado com shift_id', [
                        'id' => $dailyPlan->id,
                        'shift_id_plano' => $plan['shift_id'] ?? 'não definido',
                        'shift_id_usado' => $plan['shift_id'] ?? $this->selectedShiftId,
                        'shift_id_salvo' => $dailyPlan->fresh()->shift_id
                    ]);

                    \Illuminate\Support\Facades\Log::info('Plano atualizado', [
                        'id' => $dailyPlan->id,
                        'date' => $dailyPlan->production_date->format('Y-m-d'),
                        'shift_id' => $dailyPlan->shift_id,
                        'quantity' => $dailyPlan->planned_quantity
                    ]);

                    // Verificar se o status é 'completed' e processar o estoque
                    $dailyPlan->refresh(); // Recarregar o modelo para garantir dados atualizados
                    
                    \Illuminate\Support\Facades\Log::alert('VERIFICANDO NECESSIDADE DE PROCESSAMENTO DE ESTOQUE NO SAVE DAILY PLAN', [
                        'plan_id' => $dailyPlan->id,
                        'status' => $dailyPlan->status,
                        'status_is_completed' => ($dailyPlan->status === 'completed') ? 'SIM' : 'NÃO',
                        'actual_quantity' => $dailyPlan->actual_quantity
                    ]);
                    
                    if ($dailyPlan->status === 'completed') {
                        \Illuminate\Support\Facades\Log::alert('INICIANDO PROCESSAMENTO DE ESTOQUE NO SAVE DAILY PLAN', [
                            'plan_id' => $dailyPlan->id,
                            'time' => date('Y-m-d H:i:s')
                        ]);
                        
                        // Processar o consumo de matérias-primas
                        $materialResult = $this->processMaterialConsumption($dailyPlan, 0, '');
                        
                        // Adicionar o produto acabado ao estoque
                        $stockResult = $this->addFinishedProductToStock($dailyPlan, 0, '');
                        
                        \Illuminate\Support\Facades\Log::alert('RESULTADO DO PROCESSAMENTO DE ESTOQUE NO SAVE DAILY PLAN', [
                            'plan_id' => $dailyPlan->id,
                            'material_success' => $materialResult['success'] ?? false,
                            'stock_success' => $stockResult['success'] ?? false,
                            'time' => date('Y-m-d H:i:s')
                        ]);
                        
                        // Adicionar mensagem de sucesso se o processamento ocorreu
                        if (($materialResult['success'] ?? false) || ($stockResult['success'] ?? false)) {
                            $this->dispatch('notify',
                                type: 'info',
                                title: __('messages.inventory_update'),
                                message: __('messages.inventory_updated_for_daily_plan'));
                        }
                    }
                    
                    $this->dispatch('notify',
                        type: 'success',
                        title: __('messages.success'),
                        message: __('messages.daily_plan_updated'));

                    return true;
                }
            }
            // Se o plano já existe para esta data e turno, mas não está relacionado ao array na interface
            else if ($existingPlan) {
                $existingPlan->update([
                    'planned_quantity' => $plan['planned_quantity'],
                    'actual_quantity' => $plan['actual_quantity'] ?? $existingPlan->actual_quantity,
                    'defect_quantity' => $plan['defect_quantity'] ?? $existingPlan->defect_quantity,
                    'has_breakdown' => $plan['has_breakdown'] ?? $existingPlan->has_breakdown,
                    'breakdown_minutes' => $plan['breakdown_minutes'] ?? $existingPlan->breakdown_minutes,
                    'failure_category_id' => $plan['failure_category_id'] ?? $existingPlan->failure_category_id,
                    'failure_root_causes' => $plan['failure_root_causes'] ?? $existingPlan->failure_root_causes,
                    'status' => $this->validatePlanStatus($plan['status'] ?? $existingPlan->status),
                    'notes' => $plan['notes'] ?? $existingPlan->notes,
                    'updated_by' => auth()->id()
                ]);

                // Atualizar ID no array para futuras atualizações usarem o mesmo registro
                $this->filteredDailyPlans[$index]['id'] = $existingPlan->id;

                \Illuminate\Support\Facades\Log::info('Plano existente atualizado', [
                    'id' => $existingPlan->id,
                    'date' => $existingPlan->production_date->format('Y-m-d'),
                    'shift_id' => $existingPlan->shift_id
                ]);
                
                // Verificar se o status é 'completed' e processar o estoque
                $existingPlan->refresh(); // Recarregar o modelo para garantir dados atualizados
                
                \Illuminate\Support\Facades\Log::alert('VERIFICANDO NECESSIDADE DE PROCESSAMENTO DE ESTOQUE NO SAVE DAILY PLAN (PLANO EXISTENTE)', [
                    'plan_id' => $existingPlan->id,
                    'status' => $existingPlan->status,
                    'status_is_completed' => ($existingPlan->status === 'completed') ? 'SIM' : 'NÃO',
                    'actual_quantity' => $existingPlan->actual_quantity
                ]);
                
                if ($existingPlan->status === 'completed') {
                    \Illuminate\Support\Facades\Log::alert('INICIANDO PROCESSAMENTO DE ESTOQUE NO SAVE DAILY PLAN (PLANO EXISTENTE)', [
                        'plan_id' => $existingPlan->id,
                        'time' => date('Y-m-d H:i:s')
                    ]);
                    
                    // Processar o consumo de matérias-primas
                    $materialResult = $this->processMaterialConsumption($existingPlan, 0, '');
                    
                    // Adicionar o produto acabado ao estoque
                    $stockResult = $this->addFinishedProductToStock($existingPlan, 0, '');
                    
                    \Illuminate\Support\Facades\Log::alert('RESULTADO DO PROCESSAMENTO DE ESTOQUE NO SAVE DAILY PLAN (PLANO EXISTENTE)', [
                        'plan_id' => $existingPlan->id,
                        'material_success' => $materialResult['success'] ?? false,
                        'stock_success' => $stockResult['success'] ?? false,
                        'time' => date('Y-m-d H:i:s')
                    ]);
                    
                    // Adicionar mensagem de sucesso se o processamento ocorreu
                    if (($materialResult['success'] ?? false) || ($stockResult['success'] ?? false)) {
                        $this->dispatch('notify',
                            type: 'info',
                            title: __('messages.inventory_update'),
                            message: __('messages.inventory_updated_for_daily_plan'));
                    }
                }

                $this->dispatch('notify',
                    type: 'success',
                    title: __('messages.success'),
                    message: __('messages.daily_plan_updated'));

                return true;
            }
            // Caso não exista, criamos um novo plano
            else {
                $newPlan = ProductionDailyPlan::create([
                    'schedule_id' => $this->viewingSchedule->id,
                    'production_date' => $dateKey,
                    'planned_quantity' => $plan['planned_quantity'],
                    'actual_quantity' => $plan['actual_quantity'] ?? 0,
                    'defect_quantity' => $plan['defect_quantity'] ?? 0,
                    'has_breakdown' => $plan['has_breakdown'] ?? false,
                    'breakdown_minutes' => $plan['breakdown_minutes'] ?? 0,
                    'failure_category_id' => $plan['failure_category_id'] ?? null,
                    'failure_root_causes' => $plan['failure_root_causes'] ?? null,
                    'status' => $this->validatePlanStatus($plan['status'] ?? 'pending'),
                    'notes' => $plan['notes'] ?? '',
                    'shift_id' => $this->selectedShiftId,  // Garantir que shift_id seja passado corretamente
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id()
                ]);

                \Illuminate\Support\Facades\Log::info('Criando novo plano com shift_id explícito (saveDailyPlan)', [
                    'novo_plano_id' => $newPlan->id,
                    'shift_id_usado' => $this->selectedShiftId,
                    'shift_id_salvo' => $newPlan->shift_id
                ]);

                // Atualizar ID no array para futuras atualizações usarem o mesmo registro
                $this->filteredDailyPlans[$index]['id'] = $newPlan->id;

                \Illuminate\Support\Facades\Log::info('Novo plano criado', [
                    'id' => $newPlan->id,
                    'date' => $newPlan->production_date->format('Y-m-d'),
                    'shift_id' => $newPlan->shift_id
                ]);

                // Verificar se o status é 'completed' e processar o estoque para o novo plano também
                $newPlan->refresh(); // Recarregar o modelo para garantir dados atualizados
                
                \Illuminate\Support\Facades\Log::alert('VERIFICANDO NECESSIDADE DE PROCESSAMENTO DE ESTOQUE NO SAVE DAILY PLAN (NOVO PLANO)', [
                    'plan_id' => $newPlan->id,
                    'status' => $newPlan->status,
                    'status_is_completed' => ($newPlan->status === 'completed') ? 'SIM' : 'NÃO',
                    'actual_quantity' => $newPlan->actual_quantity
                ]);
                
                if ($newPlan->status === 'completed') {
                    \Illuminate\Support\Facades\Log::alert('INICIANDO PROCESSAMENTO DE ESTOQUE NO SAVE DAILY PLAN (NOVO PLANO)', [
                        'plan_id' => $newPlan->id,
                        'time' => date('Y-m-d H:i:s')
                    ]);
                    
                    // Processar o consumo de matérias-primas
                    $materialResult = $this->processMaterialConsumption($newPlan, 0, '');
                    
                    // Adicionar o produto acabado ao estoque
                    $stockResult = $this->addFinishedProductToStock($newPlan, 0, '');
                    
                    \Illuminate\Support\Facades\Log::alert('RESULTADO DO PROCESSAMENTO DE ESTOQUE NO SAVE DAILY PLAN (NOVO PLANO)', [
                        'plan_id' => $newPlan->id,
                        'material_success' => $materialResult['success'] ?? false,
                        'stock_success' => $stockResult['success'] ?? false,
                        'time' => date('Y-m-d H:i:s')
                    ]);
                    
                    // Adicionar mensagem de sucesso se o processamento ocorreu
                    if (($materialResult['success'] ?? false) || ($stockResult['success'] ?? false)) {
                        $this->dispatch('notify',
                            type: 'info',
                            title: __('messages.inventory_update'),
                            message: __('messages.inventory_updated_for_daily_plan'));
                    }
                }

                $this->dispatch('notify',
                    type: 'success',
                    title: __('messages.success'),
                    message: __('messages.daily_plan_created'));
                    
                // Verificar se todos os planos diários estão completos e atualizar o status da programação
                if (isset($this->viewingSchedule) && $this->viewingSchedule->id) {
                    \Illuminate\Support\Facades\Log::info('Verificando status da programação após salvar plano diário individual', [
                        'schedule_id' => $this->viewingSchedule->id,
                        'plano_status' => $dailyPlan->status ?? null
                    ]);
                    
                    $this->checkAndUpdateScheduleStatus($this->viewingSchedule->id);
                }

                return true;
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao salvar plano diário', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatch('notify',
                type: 'error',
                title: __('messages.error'),
                message: __('messages.error_saving_daily_plan') . ': ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Valida o status para garantir que seja um valor permitido no banco
     * @param string|null $status
     * @return string
     */
    private function validatePlanStatus($status)
    {
        $allowedStatuses = ['pending', 'in_progress', 'completed', 'cancelled'];

        if (in_array($status, $allowedStatuses)) {
            return $status;
        }

        // Se for 'scheduled' ou qualquer outro valor não permitido, usar 'pending'
        return 'pending';
    }

    /**
     * Salvar planos diários de produção
     */
    public function saveDailyPlans()
    {
        try {
            if (!$this->viewingSchedule || !$this->selectedShiftId) {
                $this->dispatch('notify',
                    type: 'error',
                    title: __('messages.error'),
                    message: __('messages.select_shift_first'));
                return;
            }

            \Illuminate\Support\Facades\Log::info('Salvando planos diários', [
                'schedule_id' => $this->viewingSchedule->id,
                'shift_id' => $this->selectedShiftId,
                'total_planos' => count($this->filteredDailyPlans)
            ]);

            // Verificar quantos planos já existem para este turno
            $existingPlans = ProductionDailyPlan::where('schedule_id', $this->viewingSchedule->id)
                ->where('shift_id', $this->selectedShiftId)
                ->get()
                ->keyBy(function ($plan) {
                    return $plan->production_date->format('Y-m-d');
                });

            \Illuminate\Support\Facades\Log::info('Verificação de planos existentes', [
                'total_planos_existentes' => $existingPlans->count(),
                'turno_selecionado' => $this->selectedShiftId
            ]);

            // Registrar os dados iniciais para debug
            \Illuminate\Support\Facades\Log::info('Iniciando o processamento para salvar planos', [
                'total_planos' => count($this->filteredDailyPlans),
                'shift_id_selecionado' => $this->selectedShiftId,
                'todos_planos_tem_shift' => collect($this->filteredDailyPlans)->every(function ($p) {
                    return isset($p['shift_id']) && $p['shift_id'];
                })
            ]);

            // Processar cada plano diário
            foreach ($this->filteredDailyPlans as $index => $plan) {
                $dateKey = $plan['production_date'];

                // Garantir que o shift_id esteja definido para este plano
                if (!isset($plan['shift_id']) || !$plan['shift_id']) {
                    $plan['shift_id'] = $this->selectedShiftId;
                }

                \Illuminate\Support\Facades\Log::info('Processando plano', [
                    'index' => $index,
                    'date' => $dateKey,
                    'plan_id' => $plan['id'] ?? null,
                    'shift_id' => $plan['shift_id']
                ]);

                if (isset($plan['id']) && $plan['id']) {
                    // Atualizar plano existente
                    $dailyPlan = ProductionDailyPlan::find($plan['id']);
                    if ($dailyPlan) {
                        $dailyPlan->update([
                            'planned_quantity' => $plan['planned_quantity'],
                            'actual_quantity' => $plan['actual_quantity'],
                            'defect_quantity' => $plan['defect_quantity'],
                            'has_breakdown' => $plan['has_breakdown'],
                            'breakdown_minutes' => $plan['breakdown_minutes'] ?? 0,
                            'failure_category_id' => $plan['failure_category_id'] ?? null,
                            'failure_root_causes' => $plan['failure_root_causes'] ?? null,
                            'status' => $this->validatePlanStatus($plan['status'] ?? 'pending'),
                            'notes' => $plan['notes'] ?? '',
                            'shift_id' => $plan['shift_id'] ?? $this->selectedShiftId  // Garantir que o shift_id seja atualizado
                        ]);

                        \Illuminate\Support\Facades\Log::info('Plano atualizado com shift_id', [
                            'id' => $dailyPlan->id,
                            'shift_id_plano' => $plan['shift_id'] ?? 'não definido',
                            'shift_id_usado' => $plan['shift_id'] ?? $this->selectedShiftId,
                            'shift_id_salvo' => $dailyPlan->fresh()->shift_id
                        ]);

                        \Illuminate\Support\Facades\Log::info('Plano atualizado', [
                            'id' => $dailyPlan->id,
                            'date' => $dailyPlan->production_date->format('Y-m-d'),
                            'shift_id' => $dailyPlan->shift_id,
                            'quantity' => $dailyPlan->planned_quantity
                        ]);
                    }
                } else if (!isset($existingPlans[$dateKey])) {
                    // Criar novo plano apenas se não existir um para esta data e turno
                    $newPlan = ProductionDailyPlan::create([
                        'schedule_id' => $this->viewingSchedule->id,
                        'production_date' => $plan['production_date'],
                        'start_time' => $plan['start_time'] ?? null,
                        'end_time' => $plan['end_time'] ?? null,
                        'planned_quantity' => $plan['planned_quantity'],
                        'actual_quantity' => $plan['actual_quantity'] ?? 0,
                        'defect_quantity' => $plan['defect_quantity'] ?? 0,
                        'has_breakdown' => $plan['has_breakdown'] ?? false,
                        'breakdown_minutes' => $plan['breakdown_minutes'] ?? 0,
                        'failure_category_id' => $plan['failure_category_id'] ?? null,
                        'failure_root_causes' => $plan['failure_root_causes'] ?? null,
                        'status' => $this->validatePlanStatus($plan['status'] ?? 'pending'),
                        'notes' => $plan['notes'] ?? '',
                        'shift_id' => $this->selectedShiftId,  // Garantir que shift_id seja passado corretamente
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                    ]);

                    \Illuminate\Support\Facades\Log::info('Criando novo plano com shift_id explícito', [
                        'novo_plano_id' => $newPlan->id,
                        'shift_id_usado' => $this->selectedShiftId,
                        'shift_id_salvo' => $newPlan->shift_id
                    ]);

                    \Illuminate\Support\Facades\Log::info('Novo plano criado', [
                        'id' => $newPlan->id,
                        'date' => $newPlan->production_date->format('Y-m-d'),
                        'shift_id' => $newPlan->shift_id,
                        'quantity' => $newPlan->planned_quantity
                    ]);
                } else {
                    // Se o plano já existe mas não foi carregado na interface, atualizar a quantidade e o shift_id
                    $existingPlan = $existingPlans[$dateKey];
                    $existingPlan->update([
                        'planned_quantity' => $plan['planned_quantity'],
                        'shift_id' => $plan['shift_id'] ?? $this->selectedShiftId  // Garantir que o shift_id esteja definido
                    ]);

                    \Illuminate\Support\Facades\Log::info('Plano existente atualizado com shift_id explícito', [
                        'id' => $existingPlan->id,
                        'shift_id_usado' => $plan['shift_id'] ?? $this->selectedShiftId,
                        'shift_id_salvo' => $existingPlan->fresh()->shift_id
                    ]);

                    \Illuminate\Support\Facades\Log::info('Plano existente atualizado', [
                        'id' => $existingPlan->id,
                        'date' => $existingPlan->production_date->format('Y-m-d'),
                        'shift_id' => $existingPlan->shift_id,
                        'quantity' => $existingPlan->planned_quantity
                    ]);
                }
            }

            // Notificar sucesso
            $this->dispatch('notify',
                type: 'success',
                title: __('messages.success'),
                message: __('messages.daily_plans_saved_successfully'));

            // Verificar se todos os planos diários estão completos e atualizar o status da programação se necessário
            $this->checkAndUpdateScheduleStatus($this->viewingSchedule->id);
            
            // Recarregar dados
            $this->viewDailyPlans($this->viewingSchedule->id);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao salvar planos diários', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatch('notify',
                type: 'error',
                title: __('messages.error'),
                message: __('messages.failed_to_save_daily_plans'));
        }
    }
    
    /**
     * Verifica se todos os planos diários de todos os turnos estão completos 
     * e atualiza o status da programação automaticamente
     * 
     * @param int $scheduleId ID da programação a ser verificada
     * @return void
     */
    public function checkAndUpdateScheduleStatus($scheduleId)
    {
        try {
            // Buscar a programação
            $schedule = ProductionSchedule::with('shifts')->findOrFail($scheduleId);
            
            // Obter todos os turnos associados a esta programação
            $shifts = $schedule->shifts;
            
            // Se não houver turnos definidos, não faz nada
            if ($shifts->isEmpty()) {
                Log::info('Nenhum turno encontrado para a programação', [
                    'schedule_id' => $scheduleId
                ]);
                return;
            }
            
            // Verificar se todos os turnos têm todos os seus planos completos
            $allShiftsCompleted = true;
            $shiftStatus = [];
            
            foreach ($shifts as $shift) {
                // Buscar todos os planos diários para este turno
                $shiftPlans = ProductionDailyPlan::where('schedule_id', $scheduleId)
                    ->where('shift_id', $shift->id)
                    ->get();
                
                // Se não houver planos para este turno, considerar como não completo
                if ($shiftPlans->isEmpty()) {
                    $allShiftsCompleted = false;
                    $shiftStatus[$shift->id] = 'no_plans';
                    continue;
                }
                
                // Verificar se todos os planos deste turno estão completos
                $shiftCompleted = $shiftPlans->every(function ($plan) {
                    return $plan->status === 'completed';
                });
                
                $shiftStatus[$shift->id] = [
                    'name' => $shift->name,
                    'total_plans' => $shiftPlans->count(),
                    'completed_plans' => $shiftPlans->where('status', 'completed')->count(),
                    'is_completed' => $shiftCompleted
                ];
                
                if (!$shiftCompleted) {
                    $allShiftsCompleted = false;
                }
            }
            
            Log::info('Verificando status dos planos diários por turno', [
                'schedule_id' => $scheduleId,
                'total_shifts' => $shifts->count(),
                'shifts_status' => $shiftStatus,
                'all_shifts_completed' => $allShiftsCompleted ? 'Sim' : 'Não'
            ]);
            
            // Se todos os turnos tiverem todos os seus planos completos, atualizar o status da programação
            if ($allShiftsCompleted) {
                // Verificar se o status atual não é já 'completed'
                if ($schedule->status !== 'completed') {
                    // Atualizar o status da programação para 'completed'
                    $schedule->status = 'completed';
                    $schedule->save();
                    
                    Log::info('Status da programação atualizado automaticamente para COMPLETED', [
                        'schedule_id' => $scheduleId,
                        'schedule_number' => $schedule->schedule_number
                    ]);
                    
                    // Notificar o usuário sobre a atualização
                    $this->dispatch('notify',
                        type: 'success',
                        title: __('messages.success'),
                        message: __('messages.schedule_auto_completed'));
                }
            }
        } catch (\Exception $e) {
            Log::error('Erro ao verificar status dos planos diários', [
                'schedule_id' => $scheduleId,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Atualizar um plano diário de produção
     *
     * @param int $index Índice do plano a ser atualizado
     * @param array $data Dados atualizados
     */
  /**
 * Atualizar um plano diário de produção
 *
 * @param int $index Índice do plano a ser atualizado
 * @param array $data Dados atualizados
 */
public function updateDailyPlan($index, $data = null)
{
    try {
        // Verificar se o plano existe
        if (!isset($this->filteredDailyPlans[$index])) {
            throw new \Exception(__('messages.daily_plan_not_found'));
        }

        // Se não foram passados dados específicos, usar os dados do formulário
        if (!$data) {
            $data = $this->filteredDailyPlans[$index];
        }

        // Assegurar que o shift_id está definido
        if (!isset($data['shift_id']) && $this->selectedShiftId) {
            $data['shift_id'] = $this->selectedShiftId;
        }

        \Illuminate\Support\Facades\Log::info('Atualizando plano diário', [
            'index' => $index,
            'data' => $data,
            'shift_id' => $data['shift_id'] ?? $this->selectedShiftId ?? null
        ]);

        // Se o plano já existe no banco, atualizar
        if (isset($this->filteredDailyPlans[$index]['id']) && $this->filteredDailyPlans[$index]['id']) {
            $plan = ProductionDailyPlan::findOrFail($this->filteredDailyPlans[$index]['id']);
            
            // Salvar a quantidade atual produzida e status antes da atualização
            $previousActualQuantity = $plan->actual_quantity ?? 0;
            $previousStatus = $plan->status ?? '';
            
            // Atualizar os campos do plano
            $plan->production_date = $data['production_date'] ?? $this->filteredDailyPlans[$index]['production_date'];
            $plan->start_time = $data['start_time'] ?? $this->filteredDailyPlans[$index]['start_time'];
            $plan->end_time = $data['end_time'] ?? $this->filteredDailyPlans[$index]['end_time'];
            $plan->planned_quantity = $data['planned_quantity'] ?? $this->filteredDailyPlans[$index]['planned_quantity'];
            $plan->actual_quantity = $data['actual_quantity'] ?? $this->filteredDailyPlans[$index]['actual_quantity'] ?? 0;
            $plan->defect_quantity = $data['defect_quantity'] ?? $this->filteredDailyPlans[$index]['defect_quantity'] ?? 0;
            $plan->has_breakdown = $data['has_breakdown'] ?? $this->filteredDailyPlans[$index]['has_breakdown'] ?? false;
            $plan->breakdown_minutes = $data['breakdown_minutes'] ?? $this->filteredDailyPlans[$index]['breakdown_minutes'] ?? 0;
            $plan->failure_category_id = $data['failure_category_id'] ?? $this->filteredDailyPlans[$index]['failure_category_id'] ?? null;
            $plan->failure_root_causes = $data['failure_root_causes'] ?? $this->filteredDailyPlans[$index]['failure_root_causes'] ?? null;
            $plan->status = $data['status'] ?? $this->filteredDailyPlans[$index]['status'] ?? 'pending';
            $plan->notes = $data['notes'] ?? $this->filteredDailyPlans[$index]['notes'] ?? '';

            // Garantir que o turno selecionado seja salvo
            $plan->shift_id = $data['shift_id'] ?? $this->selectedShiftId ?? $this->filteredDailyPlans[$index]['shift_id'] ?? null;

            $plan->save();
            
            // Processar o consumo de materiais e adição de produto final ao estoque
            // quando o status é 'completed' ou quando muda para 'completed'
            $statusChanged = $previousStatus !== $plan->status;
            
            \Illuminate\Support\Facades\Log::info('Verificando condição de processamento de estoque', [
                'plan_id' => $plan->id,
                'status_atual' => $plan->status,
                'status_anterior' => $previousStatus,
                'status_mudou' => $statusChanged ? 'Sim' : 'Não',
                'completado_agora' => ($statusChanged && $plan->status === 'completed') ? 'Sim' : 'Não'
            ]);
            
            // DEPURAÇÃO: Verificar explicitamente se o status é 'completed'
            \Illuminate\Support\Facades\Log::alert('VERIFICAÇÃO DE PROCESSAMENTO DE ESTOQUE PARA PLANO DIÁRIO', [
                'plan_id' => $plan->id,
                'status_atual' => $plan->status,
                'status_texto_exato' => $plan->status,  // Mostrar literalmente o texto para verificar espaços
                'status_completed' => ($plan->status === 'completed') ? 'SIM - CORRESPONDE EXATAMENTE' : 'NÃO - NÃO CORRESPONDE',
                'status_trim' => trim($plan->status),
                'status_length' => strlen($plan->status),
                'actual_quantity' => $plan->actual_quantity,
                'actual_quantity_formatted' => number_format($plan->actual_quantity, 2),
                'planned_quantity' => $plan->planned_quantity,
                'time' => date('Y-m-d H:i:s')
            ]);
            
            // Verificar a condição explícita do status
            if (trim($plan->status) === 'completed') {
                \Illuminate\Support\Facades\Log::alert('INICIANDO PROCESSAMENTO DE ESTOQUE PARA PLANO DIÁRIO', [
                    'plan_id' => $plan->id,
                    'actual_quantity' => $plan->actual_quantity,
                    'time' => date('Y-m-d H:i:s')
                ]);
                
                // Forçar o processamento do estoque quando o status é completed
                try {
                    // Processar o consumo de matérias-primas
                    $materialResult = $this->processMaterialConsumption($plan, $previousActualQuantity, $previousStatus);
                    \Illuminate\Support\Facades\Log::alert('RESULTADO DO PROCESSAMENTO DE MATÉRIA-PRIMA', [
                        'plan_id' => $plan->id,
                        'material_success' => $materialResult['success'] ?? false,
                        'components_processed' => $materialResult['components_processed'] ?? 0,
                        'time' => date('Y-m-d H:i:s')
                    ]);
                    
                    // Adicionar o produto acabado ao estoque
                    $stockResult = $this->addFinishedProductToStock($plan, $previousActualQuantity, $previousStatus);
                    \Illuminate\Support\Facades\Log::alert('RESULTADO DO PROCESSAMENTO DE PRODUTO ACABADO', [
                        'plan_id' => $plan->id,
                        'stock_success' => $stockResult['success'] ?? false,
                        'quantity_added' => $stockResult['quantity_added'] ?? 0,
                        'time' => date('Y-m-d H:i:s')
                    ]);
                    
                    \Illuminate\Support\Facades\Log::info('Processamento de estoque para plano diário concluído', [
                        'plan_id' => $plan->id,
                        'material_success' => $materialResult['success'] ?? false,
                        'stock_success' => $stockResult['success'] ?? false,
                        'current_status' => $plan->status,
                        'previous_status' => $previousStatus
                    ]);
                    
                    // Adicionar mensagem de sucesso se o processamento ocorreu
                    if (($materialResult['success'] ?? false) || ($stockResult['success'] ?? false)) {
                        $this->dispatch('notify',
                            type: 'info',
                            title: __('messages.inventory_update'),
                            message: __('messages.inventory_updated_for_daily_plan'));
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Erro ao processar estoque para plano diário', [
                        'plan_id' => $plan->id,
                        'erro' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            // Atualizar o objeto na memória
            $this->filteredDailyPlans[$index] = array_merge($this->filteredDailyPlans[$index], $data);
            $this->filteredDailyPlans[$index]['id'] = $plan->id;
            $this->filteredDailyPlans[$index]['shift_id'] = $plan->shift_id;

            // Se o status foi alterado para 'completed', verificar se todos os planos estão completos
            if ($plan->status === 'completed' && $previousStatus !== 'completed') {
                $this->checkAndUpdateScheduleStatus($plan->schedule_id);
            }

            \Illuminate\Support\Facades\Log::info('Plano diário atualizado com sucesso', [
                'id' => $plan->id,
                'shift_id' => $plan->shift_id,
                'status' => $plan->status,
                'previous_status' => $previousStatus
            ]);
        } else {
            // Plano novo, criar no banco de dados
            $schedule = ProductionSchedule::findOrFail($this->viewingSchedule->id);

            $plan = new ProductionDailyPlan();
            $plan->schedule_id = $schedule->id;
            $plan->production_date = $data['production_date'] ?? $this->filteredDailyPlans[$index]['production_date'];
            $plan->start_time = $data['start_time'] ?? $this->filteredDailyPlans[$index]['start_time'];
            $plan->end_time = $data['end_time'] ?? $this->filteredDailyPlans[$index]['end_time'];
            $plan->planned_quantity = $data['planned_quantity'] ?? $this->filteredDailyPlans[$index]['planned_quantity'];
            $plan->actual_quantity = $data['actual_quantity'] ?? $this->filteredDailyPlans[$index]['actual_quantity'] ?? 0;
            $plan->defect_quantity = $data['defect_quantity'] ?? $this->filteredDailyPlans[$index]['defect_quantity'] ?? 0;
            $plan->has_breakdown = $data['has_breakdown'] ?? $this->filteredDailyPlans[$index]['has_breakdown'] ?? false;
            $plan->breakdown_minutes = $data['breakdown_minutes'] ?? $this->filteredDailyPlans[$index]['breakdown_minutes'] ?? 0;
            $plan->failure_category_id = $data['failure_category_id'] ?? $this->filteredDailyPlans[$index]['failure_category_id'] ?? null;
            $plan->failure_root_causes = $data['failure_root_causes'] ?? $this->filteredDailyPlans[$index]['failure_root_causes'] ?? null;
            $plan->status = $data['status'] ?? $this->filteredDailyPlans[$index]['status'] ?? 'pending';
            $plan->notes = $data['notes'] ?? $this->filteredDailyPlans[$index]['notes'] ?? '';

            // Garantir que o turno selecionado seja salvo
            $plan->shift_id = $data['shift_id'] ?? $this->selectedShiftId ?? null;

            $plan->save();
            
            // Para planos novos, processar o estoque se o status for 'completed'
            if ($plan->status === 'completed') {
                // Processar o consumo de matérias-primas
                $materialResult = $this->processMaterialConsumption($plan, 0, '');
                
                // Adicionar o produto acabado ao estoque
                $stockResult = $this->addFinishedProductToStock($plan, 0, '');
                
                \Illuminate\Support\Facades\Log::info('Processamento de estoque para novo plano diário concluído', [
                    'plan_id' => $plan->id,
                    'material_success' => $materialResult['success'],
                    'stock_success' => $stockResult['success'],
                    'status' => $plan->status
                ]);
            }

            // Atualizar o objeto na memória
            $this->dailyPlans[$index] = array_merge($this->dailyPlans[$index], $data);
            $this->dailyPlans[$index]['id'] = $plan->id;

            \Illuminate\Support\Facades\Log::info('Novo plano diário criado', [
                'id' => $plan->id
            ]);
        }

        // Recalcular o impacto das falhas
        $this->calculateFailureImpact();

        // Após atualizar um plano, devemos recarregar todos os planos do banco de dados
        // para garantir que estamos com os dados atualizados
        $reloadedPlans = ProductionDailyPlan::where('schedule_id', $this->scheduleId)
            ->orderBy('production_date')
            ->orderBy('start_time')
            ->get();

        // Resetar os planos diários em memória
        $this->dailyPlans = [];

        // Reconstruir o array com os dados atualizados
        foreach ($reloadedPlans as $i => $plan) {
            $this->dailyPlans[$i] = [
                'id' => $plan->id,
                'production_date' => $plan->production_date->format('Y-m-d'),
                'start_time' => $plan->start_time,
                'end_time' => $plan->end_time,
                'planned_quantity' => $plan->planned_quantity,
                'actual_quantity' => $plan->actual_quantity,
                'defect_quantity' => $plan->defect_quantity,
                'has_breakdown' => $plan->has_breakdown,
                'breakdown_minutes' => $plan->breakdown_minutes,
                'failure_category_id' => $plan->failure_category_id,
                'failure_root_causes' => $plan->failure_root_causes,
                'status' => $plan->status,
                'notes' => $plan->notes,
                'shift_id' => $plan->shift_id,
            ];
        }

        \Illuminate\Support\Facades\Log::info('Planos diários recarregados do banco de dados', [
            'total_plans' => count($this->dailyPlans)
        ]);

        // Re-aplicar o filtro de turno se existir um turno selecionado
        if (!empty($this->selectedShiftId)) {
            // Re-filtrar os planos diários pelo turno selecionado
            if (isset($this->dailyPlans) && count($this->dailyPlans) > 0) {
                $this->filteredDailyPlans = collect($this->dailyPlans)
                    ->filter(function ($plan) {
                        // Logar para debug o turno de cada plano
                        \Illuminate\Support\Facades\Log::debug('Verificando turno do plano', [
                            'plan_shift_id' => $plan['shift_id'] ?? 'null',
                            'selected_shift_id' => $this->selectedShiftId
                        ]);

                        // Incluir planos que ainda não têm turno definido E planos do turno selecionado
                        // Isso garante que não vamos esconder planos que ainda precisam ser configurados
                        return empty($plan['shift_id']) || $plan['shift_id'] == $this->selectedShiftId;
                    })
                    ->toArray();

                \Illuminate\Support\Facades\Log::info('Planos diários re-filtrados após atualização', [
                    'shift_id' => $this->selectedShiftId,
                    'filtered_count' => count($this->filteredDailyPlans),
                    'total_plans' => count($this->dailyPlans)
                ]);
            }
        } else {
            // Se não há turno selecionado, mostrar todos os planos
            $this->filteredDailyPlans = $this->dailyPlans;
        }

        $this->dispatch('notify',
            type: 'success',
            title: __('messages.success'),
            message: __('messages.daily_plan_updated'));

        // Disparar evento para atualizar os gráficos
        $this->dispatch('dailyPlansUpdated');
        
        // Verificar se todos os planos diários estão completos e atualizar o status da programação se necessário
        if (isset($this->viewingSchedule) && $this->viewingSchedule->id) {
            \Illuminate\Support\Facades\Log::info('Verificando status da programação após atualizar plano diário', [
                'schedule_id' => $this->viewingSchedule->id,
                'plano_status' => $data['status'] ?? null
            ]);
            
            $this->checkAndUpdateScheduleStatus($this->viewingSchedule->id);
        }

        return true;
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Erro ao atualizar plano diário', [
            'index' => $index,
            'erro' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        $this->dispatch('notify',
            type: 'error',
            title: __('messages.error'),
            message: __('messages.failed_to_update_daily_plan') . ": {$e->getMessage()}");

        return false;
    }
}

    /**
     * Calcula a quantidade padrão para novos planos diários
     * Considera o número total de dias de trabalho e turnos para uma distribuição mais precisa
     *
     * @return float A quantidade planejada recomendada por dia para o turno atual
     */
    private function calculateDefaultQuantity()
    {
        if (!$this->viewingSchedule) {
            return 0;
        }

        try {
            // Calcular com base no período e quantidade total planejada
            $startDate = \Carbon\Carbon::parse($this->viewingSchedule->start_date);
            $endDate = \Carbon\Carbon::parse($this->viewingSchedule->end_date);
            
            // Obter dias de trabalho configurados
            $workingDays = $this->viewingSchedule->working_days ?? [
                'mon' => true,
                'tue' => true,
                'wed' => true,
                'thu' => true,
                'fri' => true,
                'sat' => false,
                'sun' => false,
            ];
            
            // Mapeamento de dias da semana
            $dayMap = [
                0 => 'sun',
                1 => 'mon',
                2 => 'tue',
                3 => 'wed',
                4 => 'thu',
                5 => 'fri',
                6 => 'sat',
            ];
            
            // Calcular total de dias úteis efetivos
            $currentDate = $startDate->copy();
            $totalWorkingDays = 0;
            
            while ($currentDate->lte($endDate)) {
                $dayOfWeek = $currentDate->dayOfWeek;  // 0 = Dom, 6 = Sáb
                $dayKey = $dayMap[$dayOfWeek];
                
                if (isset($workingDays[$dayKey]) && $workingDays[$dayKey]) {
                    $totalWorkingDays++;
                }
                
                $currentDate->addDay();
            }
            
            // Se não houver dias úteis, usar todos os dias
            if ($totalWorkingDays <= 0) {
                $totalWorkingDays = $startDate->diffInDays($endDate) + 1;  // Incluir o próprio dia
            }
            
            if ($totalWorkingDays <= 0) {
                return 0;
            }
            
            // Calcular a quantidade total por dia considerando todos os turnos
            $totalQuantityPerDay = 0;
            if ($this->viewingSchedule->planned_quantity > 0) {
                $totalQuantityPerDay = round($this->viewingSchedule->planned_quantity / $totalWorkingDays, 2); // Arredondar para 2 casas decimais
            }
            
            // IMPORTANTE: Log para debug do cálculo inicial
            \Illuminate\Support\Facades\Log::info('Cálculo da quantidade diária', [
                'quantidade_total' => $this->viewingSchedule->planned_quantity,
                'dias_trabalho' => $totalWorkingDays,
                'quantidade_por_dia' => $totalQuantityPerDay
            ]);
            
            // Se temos um turno selecionado e há mais de um turno, distribuir proporcionalmente
            $totalShifts = $this->shifts ? $this->shifts->count() : 1;
            
            if ($totalShifts > 1 && $this->selectedShiftId) {
                // Calcular proporção do turno atual
                $totalShiftHours = 0;
                $selectedShiftDuration = 0;
                
                foreach ($this->shifts as $shift) {
                    $duration = $shift->duration ?? 0;
                    $totalShiftHours += $duration;
                    
                    if ($shift->id == $this->selectedShiftId) {
                        $selectedShiftDuration = $duration;
                    }
                }
                
                if ($totalShiftHours > 0 && $selectedShiftDuration > 0) {
                    $shiftRatio = $selectedShiftDuration / $totalShiftHours;
                    return round($totalQuantityPerDay * $shiftRatio, 2); // Arredondar para 2 casas decimais
                }
                
                // Se não temos informações de duração, dividir igualmente
                return round($totalQuantityPerDay / $totalShifts, 2); // Arredondar para 2 casas decimais
            }
            
            // SEMPRE dividir pelo número de turnos, independente de condições anteriores
            // Esse é o caso padrão e deve sempre considerar a divisão por turnos
            $perShiftQuantity = $totalQuantityPerDay / $totalShifts;
            
            \Illuminate\Support\Facades\Log::info('Cálculo final de quantidade por turno', [
                'quantidade_diaria_total' => $totalQuantityPerDay,
                'numero_turnos' => $totalShifts,
                'quantidade_por_turno' => $perShiftQuantity,
                'quantidade_por_turno_arredondada' => round($perShiftQuantity, 2)
            ]);
            
            return round($perShiftQuantity, 2);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao calcular quantidade padrão', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 0;
        }
    }

    /**
     * Calcular o impacto das falhas na produção
     */
    public function calculateFailureImpact()
    {
        // Inicializar estatísticas de impacto
        $this->failureImpact = [
            'total_planned' => 0,
            'total_produced' => 0,
            'total_defects' => 0,
            'total_breakdown_minutes' => 0,
            'breakdown_count' => 0,
            'efficiency_percentage' => 100,
            'quality_percentage' => 100,
            'estimated_loss' => 0,
            'categories' => [],
            'root_causes' => [],
        ];

        // Se não há planos diários, não há o que calcular
        if (empty($this->dailyPlans)) {
            return;
        }

        // Calcular totais
        $totalPlanned = 0;
        $totalProduced = 0;
        $totalDefects = 0;
        $totalBreakdownMinutes = 0;
        $breakdownCount = 0;

        // Arrays para armazenar categorias e causas de falhas
        $categories = [];
        $rootCauses = [];

        foreach ($this->dailyPlans as $plan) {
            // Somar valores
            $totalPlanned += floatval($plan['planned_quantity'] ?? 0);
            $totalProduced += floatval($plan['actual_quantity'] ?? 0);
            $totalDefects += floatval($plan['defect_quantity'] ?? 0);

            if ($plan['has_breakdown']) {
                $breakdownCount++;
                $totalBreakdownMinutes += intval($plan['breakdown_minutes'] ?? 0);

                // Contar categorias de falhas
                if (!empty($plan['failure_category_id'])) {
                    $categoryId = $plan['failure_category_id'];
                    if (!isset($categories[$categoryId])) {
                        $categories[$categoryId] = [
                            'id' => $categoryId,
                            'count' => 0,
                            'minutes' => 0,
                        ];
                    }

                    $categories[$categoryId]['count']++;
                    $categories[$categoryId]['minutes'] += intval($plan['breakdown_minutes'] ?? 0);
                }

                // Contar causas raiz de falhas
                if (!empty($plan['failure_root_causes'])) {
                    $causesArray = is_array($plan['failure_root_causes'])
                        ? $plan['failure_root_causes']
                        : json_decode($plan['failure_root_causes'], true);

                    if (is_array($causesArray)) {
                        foreach ($causesArray as $causeId) {
                            if (!isset($rootCauses[$causeId])) {
                                $rootCauses[$causeId] = [
                                    'id' => $causeId,
                                    'count' => 0,
                                ];
                            }

                            $rootCauses[$causeId]['count']++;
                        }
                    }
                }
            }
        }

        // Calcular percentuais
        $efficiencyPercentage = ($totalPlanned > 0) ? (($totalProduced / $totalPlanned) * 100) : 100;
        $qualityPercentage = ($totalProduced > 0) ? (($totalProduced - $totalDefects) / $totalProduced * 100) : 100;

        // Estimar perda de produção devido a paradas
        $estimatedLoss = $totalPlanned - $totalProduced;

        // Armazenar resultados
        $this->failureImpact = [
            'total_planned' => $totalPlanned,
            'total_produced' => $totalProduced,
            'total_defects' => $totalDefects,
            'total_breakdown_minutes' => $totalBreakdownMinutes,
            'breakdown_count' => $breakdownCount,
            'efficiency_percentage' => round($efficiencyPercentage, 2),
            'quality_percentage' => round($qualityPercentage, 2),
            'estimated_loss' => $estimatedLoss,
            'categories' => array_values($categories),
            'root_causes' => array_values($rootCauses),
        ];

        // Log para debug
        \Illuminate\Support\Facades\Log::info('Cálculo de impacto de falhas concluído', [
            'total_planned' => $totalPlanned,
            'total_produced' => $totalProduced,
            'total_defects' => $totalDefects,
            'breakdown_minutes' => $totalBreakdownMinutes,
            'efficiency' => round($efficiencyPercentage, 2) . '%',
            'quality' => round($qualityPercentage, 2) . '%',
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
            $this->selectedSchedule = ProductionSchedule::with(['product', 'location', 'line', 'shifts'])->find($id);

            if (!$this->selectedSchedule) {
                \Illuminate\Support\Facades\Log::warning('Agendamento não encontrado', ['id' => $id]);
                $this->dispatch('notify',
                    type: 'error',
                    title: __('messages.error'),
                    message: __('messages.schedule_not_found'));
                return;
            }

            // Calcular o impacto de breakdown para a visualização detalhada
            $this->calculateBreakdownImpact($this->selectedSchedule);

            // Atribuir os dados da análise tanto para o objeto viewingSchedule quanto para a propriedade pública do componente
            // Isso garante que os dados estarão disponíveis entre requisições do Livewire

            // Garantir que os dados da análise estão em formato de array
            // e registrar para debug antes de atribuir
            \Illuminate\Support\Facades\Log::debug('Atribuindo dados de impacto para as propriedades', $this->impactAnalysis);

            // Converter para array e garantir que está em formato adequado para serialização no Livewire
            $this->breakdownImpact = json_decode(json_encode($this->impactAnalysis), true);

            // Garantir que o campo history também está serializado corretamente
            // e armazenar numa propriedade pública dedicada para os gráficos
            if (isset($this->breakdownImpact['history']) && is_array($this->breakdownImpact['history'])) {
                foreach ($this->breakdownImpact['history'] as $key => $item) {
                    $this->breakdownImpact['history'][$key] = (array) $item;
                }

                // Armazenar histórico em uma propriedade pública separada para garantir serialização
                $this->chartHistory = $this->breakdownImpact['history'];
                \Illuminate\Support\Facades\Log::debug('chartHistory configurado com sucesso:', $this->chartHistory);
            }

            // Verificar se os dados estão disponíveis para debug
            \Illuminate\Support\Facades\Log::debug('Dados finais de breakdownImpact:', $this->breakdownImpact);

            // Também atribuir ao objeto selectedSchedule como antes
            $this->selectedSchedule->breakdownImpact = $this->breakdownImpact;

            \Illuminate\Support\Facades\Log::info('Agendamento carregado com sucesso', [
                'id' => $id,
                'número' => $this->selectedSchedule->schedule_number,
                'produto' => $this->selectedSchedule->product ? $this->selectedSchedule->product->name : 'Produto não definido'
            ]);

            // Abrir o modal
            $this->showViewModal = true;

            // Verificar dados de histórico para debug
            if (!empty($this->chartHistory)) {
                \Illuminate\Support\Facades\Log::debug('Histórico de dados disponível para gráficos', [
                    'count' => count($this->chartHistory),
                    'primeiro_item' => $this->chartHistory[0] ?? 'nenhum',
                    'chartHistory' => $this->chartHistory
                ]);
            } else {
                \Illuminate\Support\Facades\Log::warning('Não há dados de histórico para gráficos');

                // Garantir que temos dados para teste se não houver dados reais
                if (empty($this->chartHistory) && !empty($this->breakdownImpact['history'])) {
                    $this->chartHistory = $this->breakdownImpact['history'];
                    \Illuminate\Support\Facades\Log::debug('chartHistory configurado a partir de breakdownImpact');
                }
            }

            // Disparar evento para inicializar os gráficos após a abertura do modal
            // Usar dispatch com atraso para garantir que o modal está completamente renderizado
            $this->dispatch('viewModalReady');

            // Passamos o historyData diretamente para o JavaScript para garantir que os gráficos tenham acesso aos dados
            $historyJson = json_encode($this->chartHistory);
            $this->js("console.log('Modal aberto, dados de histórico carregados:', " . $historyJson . ');');
            $this->js('window.chartHistoryData = ' . $historyJson . ';');
            $this->js("if (typeof window.initBreakdownCharts === 'function') setTimeout(window.initBreakdownCharts, 800);");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao visualizar agendamento', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Calcula a análise de impacto de paradas (breakdown) para uma programação de produção
     *
     * @param ProductionSchedule $schedule A programação a ser analisada
     * @return void
     */
    public function calculateBreakdownImpact($schedule)
    {
        // Estrutura padrão para dados da análise de impacto
        $impactAnalysis = [
            // Breakdown Impact Analysis
            'production_loss' => 0,
            'revenue_loss' => 0,
            'recovery_hours' => 0,
            'total_breakdown_minutes' => 0,
            'efficiency_percentage' => 100,
            // Quality & Production
            'total_planned_production' => 0,
            'total_actual_production' => 0,
            'total_defect_quantity' => 0,
            'good_units' => 0,
            'defect_rate' => 0,
            'quality_rate' => 100,
            // Histórico para gráficos
            'history' => []
        ];

        try {
            // Iniciar com logs detalhados para debug
            \Illuminate\Support\Facades\Log::info('Iniciando cálculo de impacto para programação', [
                'schedule_id' => $schedule->id,
                'schedule_number' => $schedule->schedule_number,
                'status' => $schedule->status,
                'has_product' => isset($schedule->product),
                'planned_quantity' => $schedule->planned_quantity,
                'actual_quantity' => $schedule->actual_quantity,
                'defect_quantity' => $schedule->defect_quantity
            ]);

            // Passo 1: Buscar todos os planos diários relacionados
            $allDailyPlans = ProductionDailyPlan::where('schedule_id', $schedule->id)->get();

            if ($allDailyPlans->isEmpty()) {
                \Illuminate\Support\Facades\Log::warning('Nenhum plano diário encontrado para o agendamento', [
                    'schedule_id' => $schedule->id
                ]);
            } else {
                \Illuminate\Support\Facades\Log::info('Planos diários encontrados', [
                    'count' => $allDailyPlans->count(),
                    'schedule_id' => $schedule->id
                ]);

                // Log detalhado dos dados dos planos diários para depuração
                if ($allDailyPlans->count() > 0) {
                    $samplePlans = $allDailyPlans->take(min(3, $allDailyPlans->count()));
                    foreach ($samplePlans as $index => $plan) {
                        \Illuminate\Support\Facades\Log::debug('Exemplo de plano diário #' . ($index + 1), [
                            'id' => $plan->id,
                            'date' => $plan->production_date,
                            'planned_quantity' => $plan->planned_quantity,
                            'actual_quantity' => $plan->actual_quantity,
                            'defect_quantity' => $plan->defect_quantity,
                            'has_breakdown' => $plan->has_breakdown,
                            'breakdown_minutes' => $plan->breakdown_minutes
                        ]);
                    }
                }
            }

            // Passo 2: Calcular totais de produção
            $totalPlannedProduction = 0;
            $totalActualProduction = 0;
            $totalDefectQuantity = 0;

            // Usar operador null coalescing para evitar erros se a soma retornar null
            $totalPlannedProduction = $allDailyPlans->sum('planned_quantity') ?? 0;
            $totalActualProduction = $allDailyPlans->sum('actual_quantity') ?? 0;
            $totalDefectQuantity = $allDailyPlans->sum('defect_quantity') ?? 0;

            \Illuminate\Support\Facades\Log::info('Totais calculados dos planos diários', [
                'total_planned' => $totalPlannedProduction,
                'total_actual' => $totalActualProduction,
                'total_defect' => $totalDefectQuantity
            ]);

            // Passo 3: Calcular unidades boas e taxas
            $goodUnits = max(0, $totalActualProduction - $totalDefectQuantity);

            // Calcular taxas de defeitos e qualidade
            $defectRate = 0;
            $qualityRate = 100;  // Default 100%

            if ($totalActualProduction > 0) {
                // Se a quantidade com defeito for maior que a produção total, limitar a 100%
                if ($totalDefectQuantity >= $totalActualProduction) {
                    $defectRate = 100;
                    $qualityRate = 0;
                    $goodUnits = 0;
                } else {
                    $defectRate = ($totalDefectQuantity / $totalActualProduction) * 100;
                    $defectRate = min(100, max(0, $defectRate));  // Limitar entre 0 e 100%

                    $qualityRate = 100 - $defectRate;  // Alternativa mais precisa
                    $qualityRate = min(100, max(0, $qualityRate));  // Limitar entre 0 e 100%
                }
            }

            // Passo 4: Filtrar planos com breakdown para cálculos adicionais
            $plansWithBreakdown = $allDailyPlans->where('has_breakdown', true);
            $totalBreakdownMinutes = $plansWithBreakdown->sum('breakdown_minutes') ?? 0;
            $recoveryHours = $totalBreakdownMinutes / 60;  // Converter minutos para horas

            // Passo 5: Calcular perdas de produção e eficiência
            $plannedQuantity = (float) $schedule->planned_quantity ?: 0;
            $productionLoss = max(0, $plannedQuantity - $goodUnits);

            // Calcular eficiência baseada nas unidades boas vs planejadas
            $efficiencyPercentage = 100;  // Default 100%
            if ($plannedQuantity > 0) {
                $efficiencyPercentage = ($goodUnits / $plannedQuantity) * 100;
                $efficiencyPercentage = min(100, max(0, $efficiencyPercentage));  // Limitar entre 0 e 100%
            }

            // Passo 6: Calcular perda de receita (se o produto tiver preço de custo definido)
            $revenueLoss = 0;
            if ($schedule->product && isset($schedule->product->cost_price)) {
                $unitPrice = (float) $schedule->product->cost_price;
                $revenueLoss = $productionLoss * $unitPrice;

                \Illuminate\Support\Facades\Log::info('Preço de custo do produto encontrado', [
                    'product_id' => $schedule->product->id,
                    'cost_price' => $unitPrice,
                    'revenue_loss' => $revenueLoss
                ]);
            } else {
                \Illuminate\Support\Facades\Log::warning('Preço de custo do produto não disponível para cálculo de perda de receita', [
                    'schedule_id' => $schedule->id,
                    'has_product' => isset($schedule->product)
                ]);
            }

            // Passo 7: Preparar dados para gráficos históricos
            $history = [];
            if ($plansWithBreakdown->isNotEmpty()) {
                foreach ($plansWithBreakdown->sortByDesc('production_date')->take(5) as $plan) {
                    try {
                        // Converter minutos para horas para exibição
                        $hoursValue = ($plan->breakdown_minutes > 0) ? ($plan->breakdown_minutes / 60) : 0;

                        // Calcular unidades boas para este plano
                        $planGoodUnits = max(0, $plan->actual_quantity - $plan->defect_quantity);
                        $planQualityRate = 100;  // Valor padrão

                        if ($plan->actual_quantity > 0) {
                            // Se a quantidade com defeito for maior que a produção, limitar adequadamente
                            if ($plan->defect_quantity >= $plan->actual_quantity) {
                                $planGoodUnits = 0;
                                $planQualityRate = 0;
                            } else {
                                $planDefectRate = ($plan->defect_quantity / $plan->actual_quantity) * 100;
                                $planDefectRate = min(100, max(0, $planDefectRate));

                                $planQualityRate = 100 - $planDefectRate;  // Cálculo mais preciso
                                $planQualityRate = min(100, max(0, $planQualityRate));  // Limitar entre 0 e 100%
                            }
                        }

                        $history[] = [
                            'date' => $plan->production_date->format('Y-m-d'),
                            'hours' => round($hoursValue, 2),
                            'loss' => (float) ($plan->planned_quantity - $planGoodUnits),
                            'defects' => (float) ($plan->defect_quantity),
                            'quality_rate' => round($planQualityRate, 2)
                        ];
                    } catch (\Exception $historyError) {
                        \Illuminate\Support\Facades\Log::error('Erro ao processar dados históricos de plano diário', [
                            'plan_id' => $plan->id,
                            'error' => $historyError->getMessage()
                        ]);
                    }
                }
            }

            // Passo 8: Compilar todos os dados na estrutura de análise
            $impactAnalysis = [
                // Breakdown Impact Analysis
                'production_loss' => $productionLoss,
                'revenue_loss' => $revenueLoss,
                'recovery_hours' => $recoveryHours,
                'total_breakdown_minutes' => $totalBreakdownMinutes,
                'efficiency_percentage' => round($efficiencyPercentage, 2),
                // Quality & Production
                'total_planned_production' => $totalPlannedProduction,
                'total_actual_production' => $totalActualProduction,
                'total_defect_quantity' => $totalDefectQuantity,
                'good_units' => $goodUnits,
                'defect_rate' => round($defectRate, 2),
                'quality_rate' => round($qualityRate, 2),
                // Histórico para gráficos
                'history' => $history
            ];

            // Atribuir à propriedade pública para acessar na view
            $this->impactAnalysis = $impactAnalysis;

            \Illuminate\Support\Facades\Log::info('Análise de impacto calculada com sucesso', $impactAnalysis);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro crítico ao calcular análise de impacto', [
                'schedule_id' => $schedule->id ?? 'unknown',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        // Atualizar o objeto de agendamento com os dados da análise
        // Mantemos as duas propriedades (camelCase e snake_case) para compatibilidade
        $schedule->breakdownImpact = $impactAnalysis;
        $schedule->breakdown_impact = $impactAnalysis;

        // Atualizar também as propriedades do componente se existirem
        if (isset($this->viewingSchedule) && $this->viewingSchedule && $this->viewingSchedule->id === $schedule->id) {
            $this->viewingSchedule->breakdownImpact = $impactAnalysis;
            $this->viewingSchedule->breakdown_impact = $impactAnalysis;
        }

        if (isset($this->selectedSchedule) && $this->selectedSchedule && $this->selectedSchedule->id === $schedule->id) {
            $this->selectedSchedule->breakdownImpact = $impactAnalysis;
            $this->selectedSchedule->breakdown_impact = $impactAnalysis;
        }
    }

    /**
     * Abrir modal de confirmação de exclusão de programação
     *
     * @param int $id ID da programação a excluir
     */
    public function openDeleteModal($id)
    {
        try {
            \Illuminate\Support\Facades\Log::info('=== INÍCIO DO MÉTODO openDeleteModal() ===', ['id' => $id]);
            
            // Carregar programação com informações do produto
            $this->scheduleToDelete = ProductionSchedule::with(['product', 'responsible', 'location'])->find($id);
            
            if (!$this->scheduleToDelete) {
                \Illuminate\Support\Facades\Log::warning('Programação não encontrada para exclusão', ['id' => $id]);
                $this->dispatch('notify',
                    type: 'error',
                    title: __('messages.error'),
                    message: __('messages.schedule_not_found'));
                return;
            }
            
            // Verificar se há ordens de produção associadas
            $relatedOrders = ProductionOrder::where('schedule_id', $id)->get();
            $this->relatedOrders = $relatedOrders;
            
            \Illuminate\Support\Facades\Log::info('Preparando modal de exclusão', [
                'schedule_id' => $id,
                'schedule_number' => $this->scheduleToDelete->schedule_number,
                'related_orders_count' => $relatedOrders->count()
            ]);
            
            // Mostrar modal de confirmação
            $this->showDeleteModal = true;
            $this->confirmDelete = false;
            
            \Illuminate\Support\Facades\Log::info('=== FIM DO MÉTODO openDeleteModal() ===');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao abrir modal de exclusão', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatch('notify',
                type: 'error',
                title: __('messages.error'),
                message: __('messages.error_loading_schedule', ['error' => $e->getMessage()]));
        }
    }
    
    /**
     * Fechar o modal de exclusão
     */
    public function closeDeleteModal()
    {
        \Illuminate\Support\Facades\Log::info('Fechando modal de exclusão');
        $this->showDeleteModal = false;
        $this->scheduleToDelete = null;
        $this->confirmDelete = false;
        if (isset($this->relatedOrders)) {
            $this->relatedOrders = [];
        }
    }
    
    /**
     * @param int $id ID da programação a visualizar
     */
    public function viewSchedule($id)
    {
        try {
            // Registrar tentativa de visualização
            \Illuminate\Support\Facades\Log::info('Tentando visualizar programação de produção', ['id' => $id]);

            // Buscar programação com seus relacionamentos
            $this->viewingSchedule = ProductionSchedule::with([
                'product',
                'location',
                'line',
                'shifts',  // Corrigido para plural conforme relacionamento no model
                'dailyPlans',
                'productionOrders',
                'responsible'
            ])->find($id);

            // Verificar se a propriedade viewingSchedule foi preenchida no render()
            if (!$this->viewingSchedule) {
                // Tentar carregar novamente direto pelo modelo
                $this->viewingSchedule = ProductionSchedule::with([
                    'product',
                    'location',
                    'line',
                    'shifts',
                    'dailyPlans',
                    'productionOrders',
                    'responsible'
                ])->find($id);
            }

            // Verificar se a programação existe
            if (!$this->viewingSchedule) {
                \Illuminate\Support\Facades\Log::warning('Programação não encontrada', ['id' => $id]);
                $this->dispatch('notify',
                    type: 'error',
                    title: __('messages.schedule_not_found'),
                    message: __('messages.schedule_may_have_been_deleted'));
                return;
            }

            // Abrir modal de visualização
            $this->scheduleId = $id;
            $this->showViewModal = true;

            \Illuminate\Support\Facades\Log::info('Programação carregada com sucesso', [
                'id' => $id,
                'schedule_number' => $this->viewingSchedule->schedule_number
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao visualizar programação', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatch('notify',
                type: 'error',
                title: __('messages.error'),
                message: $e->getMessage());
        }
    }

    /**
     * Fechar o modal de visualização
     */
    public function closeViewModal()
    {
        \Illuminate\Support\Facades\Log::info('Fechando modal de visualização');
        $this->showViewModal = false;
        $this->viewingSchedule = null;
        $this->scheduleId = null;
        $this->breakdownData = null;
    }

    /**
     * Inicia a produção mudando o status de confirmed para in_progress
     *
     * @return void
     */
    public function startProduction()
    {
        try {
            if (!$this->scheduleId) {
                $this->dispatch('notify', type: 'error', message: __('messages.no_schedule_selected'));
                return;
            }
            
            $schedule = ProductionSchedule::find($this->scheduleId);
            
            if (!$schedule) {
                $this->dispatch('notify', type: 'error', message: __('messages.schedule_not_found'));
                return;
            }
            
            if ($schedule->status !== 'confirmed') {
                $this->dispatch('notify', type: 'error', message: __('messages.only_confirmed_schedules_can_start'));
                return;
            }
            
            $schedule->status = 'in_progress';
            $schedule->save();
            
            // Atualizar a propriedade viewingSchedule para refletir a mudança de status
            if ($this->viewingSchedule && $this->viewingSchedule->id == $schedule->id) {
                $this->viewingSchedule->status = 'in_progress';
            }
            
            $this->dispatch('notify', type: 'success', message: __('messages.production_started'));
            
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
            Log::error('Erro ao iniciar produção: ' . $e->getMessage());
        }
    }

    /**
     * Excluir programação
     */
    public function delete()
    {
        try {
            // Verificar se temos um ID válido para exclusão
            if (!$this->scheduleId) {
                $this->dispatch('notify',
                    type: 'error',
                    title: __('messages.error'),
                    message: __('messages.invalid_schedule_id'));
                $this->closeDeleteModal();
                return;
            }

            // Carregar programação se ainda não estiver carregada
            if (!$this->scheduleToDelete) {
                $this->scheduleToDelete = ProductionSchedule::findOrFail($this->scheduleId);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify',
                type: 'error',
                title: __('messages.error'),
                message: __('messages.schedule_not_found'));
            $this->closeDeleteModal();
            return;
        }

        try {
            // Verificar se existem ordens relacionadas
            $relatedOrdersCount = ProductionOrder::where('schedule_id', $this->scheduleId)->count();

            if ($relatedOrdersCount > 0) {
                $this->dispatch('notify',
                    type: 'error',
                    title: __('messages.delete_error'),
                    message: __('messages.cannot_delete_schedule_with_orders', ['count' => $relatedOrdersCount]));

                return;  // Não feche o modal para que o usuário possa ver o alerta e ter a opção de cancelar
            }

            // Confirmar exclusão com logs
            \Illuminate\Support\Facades\Log::info('Excluindo programação de produção', [
                'id' => $this->scheduleId,
                'schedule_number' => $this->scheduleToDelete->schedule_number
            ]);

            // Excluir programação
            $this->scheduleToDelete->delete();

            $this->dispatch('notify',
                type: 'success',
                title: __('messages.success'),
                message: __('messages.production_schedule_deleted'));

            $this->closeDeleteModal();

            // Recarregar dados do calendário se estiver na visualização de calendário
            if ($this->currentTab === 'calendar') {
                $this->loadCalendarEvents();
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao excluir programação', [
                'id' => $this->scheduleId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatch('notify',
                type: 'error',
                title: __('messages.error'),
                message: __('messages.schedule_delete_error', ['error' => $e->getMessage()]));
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
            $datesChanged =
                $schedule->start_date != $this->schedule['start_date'] ||
                $schedule->end_date != $this->schedule['end_date'] ||
                $schedule->planned_quantity != $this->schedule['planned_quantity'];

            // Log adicional para debug
            \Illuminate\Support\Facades\Log::info('Verificação de alteração de datas ou quantidade', [
                'datas_alteradas' => $datesChanged,
                'data_inicio_antiga' => $schedule->start_date,
                'data_inicio_nova' => $this->schedule['start_date'],
                'data_fim_antiga' => $schedule->end_date,
                'data_fim_nova' => $this->schedule['end_date'],
                'quantidade_antiga' => $schedule->planned_quantity,
                'quantidade_nova' => $this->schedule['planned_quantity']
            ]);

            // Atualizar o registro
            $schedule->update($this->schedule);

            // Processar os turnos selecionados para a tabela pivot mrp_production_schedule_shift
            $selectedShiftIds = [];

            // Debug do formato dos dados de turnos na atualização
            \Illuminate\Support\Facades\Log::debug('Formato dos dados de turnos na atualização:', [
                'selectedShifts' => $this->selectedShifts,
                'schedule_id' => $schedule->id,
                'pivot_table' => 'mrp_production_schedule_shift'
            ]);

            if (!empty($this->selectedShifts)) {
                // Converter todos os IDs para inteiros
                foreach ($this->selectedShifts as $shiftId) {
                    $selectedShiftIds[] = intval($shiftId);

                    // Registrar cada turno que está sendo adicionado/mantido
                    $shiftName = Shift::find($shiftId)?->name ?? 'desconhecido';
                    \Illuminate\Support\Facades\Log::debug("Mantendo/adicionando turno ID: {$shiftId} ({$shiftName}) na atualização");
                }

                // Garantir que os IDs sejam inteiros válidos
                $selectedShiftIds = array_filter($selectedShiftIds, function ($id) {
                    return is_numeric($id) && $id > 0;
                });

                // Buscar os turnos atuais antes da sincronização para comparação
                $currentShifts = $schedule->shifts()->pluck('id')->toArray();

                // Sincronizar os turnos com a programação na tabela pivot
                \Illuminate\Support\Facades\Log::info('Tentando sincronizar turnos na atualização', [
                    'shift_ids_novos' => $selectedShiftIds,
                    'shift_ids_atuais' => $currentShifts,
                    'schedule_id' => $schedule->id,
                    'alteracoes' => array_diff($selectedShiftIds, $currentShifts)
                ]);

                try {
                    // Usar withTimestamps para garantir que created_at e updated_at sejam preenchidos na pivot
                    $schedule->shifts()->sync($selectedShiftIds);

                    // Verificar se os turnos foram realmente associados - usar fresh() para pegar dados atualizados
                    $associatedShifts = $schedule->fresh()->shifts()->get();

                    \Illuminate\Support\Facades\Log::info('Turnos atualizados com sucesso na tabela pivot', [
                        'turnos_ids_enviados' => $selectedShiftIds,
                        'turnos_associados_count' => $associatedShifts->count(),
                        'turnos_nomes' => $associatedShifts->pluck('name')->toArray()
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Erro ao atualizar turnos na tabela pivot:', [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            } else {
                \Illuminate\Support\Facades\Log::warning('Nenhum turno selecionado ou formato inválido na atualização');
                // Se não tiver turnos selecionados, limpar todos os turnos existentes da tabela pivot
                $schedule->shifts()->sync([]);
                \Illuminate\Support\Facades\Log::info('Todos os turnos foram removidos da programação ID: ' . $schedule->id);
            }

            // Não precisamos chamar distributePlannedQuantity() aqui porque o hook booted() já faz isso automaticamente
            // quando os campos relevantes são atualizados
            if ($datesChanged) {
                \Illuminate\Support\Facades\Log::info('Datas ou quantidade alteradas - os planos diários serão atualizados automaticamente pelo hook booted()', [
                    'schedule_id' => $schedule->id
                ]);
            }

            \Illuminate\Support\Facades\Log::info('Agendamento atualizado com sucesso', [
                'id' => $schedule->id
            ]);

            // Fechar o modal
            $this->closeCreateEditModal();

            // Notificar usuário
            $this->dispatch('notify',
                type: 'success',
                title: __('messages.success'),
                message: __('messages.schedule_updated'));

            \Illuminate\Support\Facades\Log::info('=== FIM DO MÉTODO UPDATE ===');
            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao atualizar programação: ' . $e->getMessage());

            $this->dispatch('notify',
                type: 'error',
                title: __('messages.error'),
                message: __('messages.schedule_update_error', ['error' => $e->getMessage()]));

            return false;
        }
    }

    // Método syncShifts removido para desabilitar a funcionalidade de turnos

    /**
     * Store new production schedule
     */
    public function store()
    {
        \Illuminate\Support\Facades\Log::info('=== INÍCIO DO MÉTODO STORE ===');

        // Remover try-catch para permitir que os erros de validação
        // sejam processados automaticamente pelo Livewire
        $this->validate($this->rules());

        try {
            // Preparar dados para salvar
            $data = $this->schedule;
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            // Se status for in_progress, definir data/hora de início real
            if ($data['status'] == 'in_progress') {
                $data['actual_start_time'] = now();
            }

            // Salvar o registro
            $schedule = ProductionSchedule::create($data);

            \Illuminate\Support\Facades\Log::info('Programação criada com sucesso', ['id' => $schedule->id]);

            // Processar os turnos selecionados para a tabela pivot mrp_production_schedule_shift
            $selectedShiftIds = [];

            // Debug do formato dos dados de turnos recebidos
            \Illuminate\Support\Facades\Log::debug('Formato dos dados de turnos recebidos:', [
                'selectedShifts' => $this->selectedShifts,
                'pivot_table' => 'mrp_production_schedule_shift'
            ]);

            if (!empty($this->selectedShifts)) {
                // Converter todos os IDs para inteiros
                foreach ($this->selectedShifts as $shiftId) {
                    $selectedShiftIds[] = intval($shiftId);

                    // Registrar cada turno que está sendo adicionado
                    $shiftName = Shift::find($shiftId)?->name ?? 'desconhecido';
                    \Illuminate\Support\Facades\Log::debug("Adicionando turno ID: {$shiftId} ({$shiftName}) à programação");
                }

                // Garantir que os IDs sejam inteiros válidos
                $selectedShiftIds = array_filter($selectedShiftIds, function ($id) {
                    return is_numeric($id) && $id > 0;
                });

                // Sincronizar os turnos com a programação na tabela pivot
                \Illuminate\Support\Facades\Log::info('Tentando sincronizar turnos na tabela pivot', [
                    'shift_ids' => $selectedShiftIds,
                    'schedule_id' => $schedule->id,
                    'tabela_pivot' => 'mrp_production_schedule_shift'
                ]);

                try {
                    // Usar withTimestamps para garantir que created_at e updated_at sejam preenchidos na pivot
                    $schedule->shifts()->sync($selectedShiftIds);

                    // Verificar se os turnos foram realmente associados
                    $associatedShifts = $schedule->fresh()->shifts()->get();

                    \Illuminate\Support\Facades\Log::info('Turnos associados com sucesso na tabela pivot', [
                        'turnos_ids_enviados' => $selectedShiftIds,
                        'turnos_associados_count' => $associatedShifts->count(),
                        'turnos_nomes' => $associatedShifts->pluck('name')->toArray()
                    ]);

                    // Recarregar o modelo após sincronizar os turnos para garantir que a relação esteja atualizada
                    $schedule = $schedule->fresh();

                    \Illuminate\Support\Facades\Log::info('Turnos associados com sucesso ao agendamento');
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Erro ao salvar turnos na tabela pivot:', [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);

                    // Não criar planos diários automaticamente
                    \Illuminate\Support\Facades\Log::info('Erro ao associar turnos, mas o agendamento foi criado');
                }
            } else {
                \Illuminate\Support\Facades\Log::warning('Nenhum turno selecionado ou formato inválido na criação da programação');

                // Não criar planos diários automaticamente
                \Illuminate\Support\Facades\Log::info('Agendamento criado sem turnos associados');
            }

            // Fechar o modal após salvar
            $this->closeCreateEditModal();

            // Notificar usuário
            $this->dispatch('notify',
                type: 'success',
                title: __('messages.success'),
                message: __('messages.schedule_created'));

            \Illuminate\Support\Facades\Log::info('=== FIM DO MÉTODO STORE ===');
            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao criar programação: ' . $e->getMessage());

            $this->dispatch('notify',
                type: 'error',
                title: __('messages.error'),
                message: __('messages.schedule_creation_error', ['error' => $e->getMessage()]));

            \Illuminate\Support\Facades\Log::info('=== FIM DO MÉTODO STORE - ERRO ===');
            return false;
        }
    }

    // Note: Removed duplicate method definitions for openDeleteModal, closeDeleteModal, and delete

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
                $query->where(function ($q) use ($search) {
                    $q
                        ->where('schedule_number', 'like', "%{$search}%")
                        ->orWhere('responsible', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($q) use ($search) {
                            $q
                                ->where('name', 'like', "%{$search}%")
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
                $query
                    ->whereDate('start_date', '<=', $date)
                    ->whereDate('end_date', '>=', $date);
            })
            ->when($this->productFilter, function ($query, $productId) {
                $query->where('product_id', $productId);
            })
            ->orderBy($this->sortField, $this->sortDirection);

        $schedules = $query->paginate($this->perPage);

        // Carregar dados para selects - apenas produtos do tipo finished_product que têm componentes no BOM
        $products = Product::where('product_type', 'finished_product')
            ->whereHas('bomHeaders', function ($query) {
                $query
                    ->where('status', 'active')
                    ->whereHas('details');  // Garante que o produto tem componentes cadastrados
            })
            ->orderBy('name')
            ->get();

        // Carregar localizações de inventário da supply chain
        $locations = Location::orderBy('name')->get();

        // Carregar linhas de produção e turnos
        $productionLines = Line::where('is_active', true)->orderBy('name')->get();
        
        // Carregar responsáveis ativos
        $responsibles = Responsible::where('is_active', true)->orderBy('name')->get();

        // Carregar turnos - para o modal de daily plan, usamos apenas os turnos específicos do planejamento
        // definidos na propriedade $this->shifts, mas para outras funcionalidades, carregamos todos os turnos
        if ($this->showDailyPlansModal && isset($this->shifts)) {
            // Usar turnos específicos para o daily plan que foram carregados em viewDailyPlans()
            $shifts = $this->shifts;

            // Registro para debug
            \Illuminate\Support\Facades\Log::info('Usando turnos específicos do planejamento para o daily plan', [
                'total_shifts' => $shifts->count(),
                'shifts_ids' => $shifts->pluck('id')->toArray()
            ]);
        } else {
            // Para outras funcionalidades, carregamos todos os turnos (sem filtro de ativos)
            $shifts = Shift::orderBy('name')->get();
        }

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
            $viewingSchedule = ProductionSchedule::with([
                'product',
                'location',
                'line',
                'shifts',
                'productionOrders',
                'dailyPlans'
            ])->find($this->scheduleId);

            // Definir a propriedade de visualização para garantir acesso no template
            $this->viewingSchedule = $viewingSchedule;

            // Log para debug
            \Illuminate\Support\Facades\Log::info('Carregando dados para modal', [
                'schedule_id' => $this->scheduleId,
                'schedule_loaded' => $viewingSchedule ? true : false
            ]);
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

        // Carregar categorias de falha e causas raiz para o modal de planos diários
        $failureCategories = FailureCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        $failureRootCauses = FailureRootCause::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.mrp.production-scheduling', [
            'schedules' => $schedules,
            'products' => $products,
            'locations' => $locations,
            'productionLines' => $productionLines,
            'shifts' => $shifts,
            'responsibles' => $responsibles,
            'statuses' => $statuses,
            'priorities' => $priorities,
            'relatedOrders' => $relatedOrders,
            'selectedSchedule' => $selectedSchedule,
            'viewingSchedule' => $viewingSchedule,
            'failureCategories' => $failureCategories,
            'failureRootCauses' => $failureRootCauses
        ])->layout('layouts.livewire', [
            'title' => 'Programação de Produção'
        ]);
    }
}
