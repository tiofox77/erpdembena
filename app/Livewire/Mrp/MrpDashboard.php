<?php

namespace App\Livewire\Mrp;

use Livewire\Component;
use App\Models\Mrp\DemandForecast;
use App\Models\Mrp\ProductionOrder;
use App\Models\Mrp\PurchasePlan;
use App\Models\Mrp\InventoryLevel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MrpDashboard extends Component
{
    public $itemsToOrder = [];
    public $upcomingProduction = [];
    public $inventorySummary = [];
    public $demandForecastSummary = [];
    public $alerts = [];
    public $recentActivities = [];
    public $isLoadingStats = false;
    
    // Variáveis para cards do dashboard
    public $totalProductionOrders = 0;
    public $completedProductionOrders = 0;
    public $inProgressProductionOrders = 0;
    public $plannedProductionOrders = 0;
    public $totalDemandForecasts = 0;
    public $lastForecastUpdate = '-';
    public $totalPurchasePlans = 0;
    public $plannedPurchases = 0;
    public $orderedPurchases = 0;
    public $totalInventoryItems = 0;
    public $lowStockCount = 0;
    public $mediumStockCount = 0;
    
    // Dados para gráficos
    public $productionChartData = [];
    public $demandCapacityChartData = [];
    
    /**
     * Mount the component
     */
    public function mount()
    {
        $this->loadDashboardData();
    }
    
    /**
     * Load all dashboard data
     */
    public function loadDashboardData()
    {
        $this->isLoadingStats = true;
        
        // Carregar itens que precisam ser pedidos (abaixo do ponto de reposição)
        $this->loadItemsToOrder();
        
        // Carregar ordens de produção próximas
        $this->loadUpcomingProduction();
        
        // Carregar resumo de estoque
        $this->loadInventorySummary();
        
        // Carregar resumo das previsões de demanda
        $this->loadDemandForecastSummary();
        
        // Carregar estatísticas para os cards do dashboard
        $this->loadProductionOrderStats();
        $this->loadDemandForecastStats();
        $this->loadPurchasePlanStats();
        $this->loadInventoryStats();
        
        // Carregar dados para gráficos
        $this->loadChartData();
        $this->loadDemandCapacityChartData();
        
        // Carregar alertas
        $this->loadAlerts();
        
        // Carregar atividades recentes
        $this->loadRecentActivities();
        
        $this->isLoadingStats = false;
    }
    
    /**
     * Carrega itens que precisam ser pedidos
     */
    private function loadItemsToOrder()
    {
        // Check if the required tables exist before querying
        if (Schema::hasTable('mrp_inventory_levels') && Schema::hasTable('sc_products')) {
            try {
                // Esta consulta encontra produtos que estão abaixo do ponto de reposição
                $this->itemsToOrder = InventoryLevel::join('sc_products', 'mrp_inventory_levels.product_id', '=', 'sc_products.id')
                    ->select([
                        'sc_products.id',
                        'sc_products.name',
                        'sc_products.sku',
                        'mrp_inventory_levels.reorder_point',
                        'mrp_inventory_levels.safety_stock',
                        DB::raw('sc_products.stock_quantity as current_stock'),
                        DB::raw('mrp_inventory_levels.reorder_point - sc_products.stock_quantity as missing_quantity')
                    ])
                    ->whereRaw('sc_products.stock_quantity < mrp_inventory_levels.reorder_point')
                    ->orderByRaw('(mrp_inventory_levels.reorder_point - sc_products.stock_quantity) DESC')
                    ->limit(10)
                    ->get()
                    ->toArray();
            } catch (\Exception $e) {
                // Log the error but don't crash the dashboard
                \Log::error('Error loading items to order: ' . $e->getMessage());
                $this->itemsToOrder = [];
            }
        } else {
            // Tables don't exist yet, return empty array
            $this->itemsToOrder = [];
        }
    }
    
    /**
     * Carrega ordens de produção próximas
     */
    private function loadUpcomingProduction()
    {
        // Check if the required tables exist before querying
        if (Schema::hasTable('mrp_production_orders')) {
            try {
                // Ordens de produção que começam nos próximos dias
                $this->upcomingProduction = ProductionOrder::with(['product'])
                    ->whereIn('status', ['draft', 'released'])
                    ->where('planned_start_date', '>=', now())
                    ->where('planned_start_date', '<=', now()->addDays(30))
                    ->orderBy('planned_start_date')
                    ->limit(5)
                    ->get()
                    ->toArray();
            } catch (\Exception $e) {
                // Log the error but don't crash the dashboard
                \Log::error('Error loading upcoming production: ' . $e->getMessage());
                $this->upcomingProduction = [];
            }
        } else {
            // Table doesn't exist yet, return empty array
            $this->upcomingProduction = [];
        }
    }
    
    /**
     * Carrega resumo de estoque
     */
    private function loadInventorySummary()
    {
        // Check if the required tables exist before querying
        if (Schema::hasTable('mrp_inventory_levels')) {
            try {
                // Resumo dos níveis de estoque
                $summary = InventoryLevel::select([
                        DB::raw('COUNT(DISTINCT product_id) as total_products'),
                        DB::raw('SUM(CASE WHEN safety_stock > 0 THEN 1 ELSE 0 END) as with_safety_stock'),
                        DB::raw('AVG(reorder_point) as avg_reorder_point'),
                        DB::raw('AVG(lead_time_days) as avg_lead_time')
                    ])
                    ->first();
                    
                $this->inventorySummary = $summary ? $summary->toArray() : [
                    'total_products' => 0,
                    'with_safety_stock' => 0,
                    'avg_reorder_point' => 0,
                    'avg_lead_time' => 0
                ];
            } catch (\Exception $e) {
                // Log the error but don't crash the dashboard
                \Log::error('Error loading inventory summary: ' . $e->getMessage());
                $this->inventorySummary = [
                    'total_products' => 0,
                    'with_safety_stock' => 0,
                    'avg_reorder_point' => 0,
                    'avg_lead_time' => 0
                ];
            }
        } else {
            // Table doesn't exist yet, return empty summary
            $this->inventorySummary = [
                'total_products' => 0,
                'with_safety_stock' => 0,
                'avg_reorder_point' => 0,
                'avg_lead_time' => 0
            ];
        }
    }
    
    /**
     * Carrega resumo das previsões de demanda
     */
    private function loadDemandForecastSummary()
    {
        // Check if the required tables exist before querying
        if (Schema::hasTable('mrp_demand_forecasts')) {
            try {
                // Resumo das previsões de demanda
                $summary = DemandForecast::select([
                        DB::raw('COUNT(DISTINCT product_id) as products_with_forecast'),
                        DB::raw('COUNT(*) as total_forecasts'),
                        DB::raw('AVG(confidence_level) as avg_confidence'),
                        DB::raw('SUM(CASE WHEN forecast_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as forecasts_next_30days')
                    ])
                    ->first();
                    
                $this->demandForecastSummary = $summary ? $summary->toArray() : [
                    'products_with_forecast' => 0,
                    'total_forecasts' => 0,
                    'avg_confidence' => 0,
                    'forecasts_next_30days' => 0
                ];
            } catch (\Exception $e) {
                // Log the error but don't crash the dashboard
                \Log::error('Error loading demand forecast summary: ' . $e->getMessage());
                $this->demandForecastSummary = [
                    'products_with_forecast' => 0,
                    'total_forecasts' => 0,
                    'avg_confidence' => 0,
                    'forecasts_next_30days' => 0
                ];
            }
        } else {
            // Table doesn't exist yet, return empty summary
            $this->demandForecastSummary = [
                'products_with_forecast' => 0,
                'total_forecasts' => 0,
                'avg_confidence' => 0,
                'forecasts_next_30days' => 0
            ];
        }
    }
    
    /**
     * Carrega estatísticas de ordens de produção para o dashboard
     */
    private function loadProductionOrderStats()
    {
        if (Schema::hasTable('mrp_production_orders')) {
            try {
                // Total de ordens de produção
                $this->totalProductionOrders = ProductionOrder::count();
                
                // Ordens completadas
                $this->completedProductionOrders = ProductionOrder::where('status', 'completed')->count();
                
                // Ordens em progresso
                $this->inProgressProductionOrders = ProductionOrder::where('status', 'in_progress')->count();
                
                // Ordens planejadas (draft + released)
                $this->plannedProductionOrders = ProductionOrder::whereIn('status', ['draft', 'released'])->count();
            } catch (\Exception $e) {
                // Log the error but don't crash the dashboard
                \Log::error('Error loading production order stats: ' . $e->getMessage());
                // Os valores padrão já estão definidos como 0
            }
        }
    }
    
    /**
     * Carrega estatísticas de previsões de demanda para o dashboard
     */
    private function loadDemandForecastStats()
    {
        if (Schema::hasTable('mrp_demand_forecasts')) {
            try {
                // Total de previsões de demanda
                $this->totalDemandForecasts = DemandForecast::count();
                
                // Última atualização
                $lastForecast = DemandForecast::latest('updated_at')->first();
                if ($lastForecast) {
                    $this->lastForecastUpdate = $lastForecast->updated_at->format('d/m/Y H:i');
                }
            } catch (\Exception $e) {
                // Log the error but don't crash the dashboard
                \Log::error('Error loading demand forecast stats: ' . $e->getMessage());
                // Os valores padrão já estão definidos
            }
        }
    }
    
    /**
     * Carrega estatísticas de planos de compra para o dashboard
     */
    private function loadPurchasePlanStats()
    {
        if (Schema::hasTable('mrp_purchase_plans')) {
            try {
                // Total de planos de compra
                $this->totalPurchasePlans = PurchasePlan::count();
                
                // Planos planejados (draft + approved)
                $this->plannedPurchases = PurchasePlan::whereIn('status', ['draft', 'approved'])->count();
                
                // Planos ordenados
                $this->orderedPurchases = PurchasePlan::where('status', 'ordered')->count();
            } catch (\Exception $e) {
                // Log the error but don't crash the dashboard
                \Log::error('Error loading purchase plan stats: ' . $e->getMessage());
                // Os valores padrão já estão definidos como 0
            }
        }
    }
    
    /**
     * Carrega estatísticas de inventário para o dashboard
     */
    private function loadInventoryStats()
    {
        if (Schema::hasTable('mrp_inventory_levels') && Schema::hasTable('sc_products')) {
            try {
                // Total de itens de inventário monitorados
                $this->totalInventoryItems = InventoryLevel::count();
                
                // Calcular itens com estoque baixo e médio
                // Estoque baixo: abaixo do safety_stock
                $this->lowStockCount = InventoryLevel::join('sc_products', 'mrp_inventory_levels.product_id', '=', 'sc_products.id')
                    ->whereRaw('sc_products.stock_quantity < mrp_inventory_levels.safety_stock')
                    ->count();
                    
                // Estoque médio: entre safety_stock e reorder_point
                $this->mediumStockCount = InventoryLevel::join('sc_products', 'mrp_inventory_levels.product_id', '=', 'sc_products.id')
                    ->whereRaw('sc_products.stock_quantity >= mrp_inventory_levels.safety_stock')
                    ->whereRaw('sc_products.stock_quantity <= mrp_inventory_levels.reorder_point')
                    ->count();
            } catch (\Exception $e) {
                // Log the error but don't crash the dashboard
                \Log::error('Error loading inventory stats: ' . $e->getMessage());
                // Os valores padrão já estão definidos como 0
            }
        }
    }
    
    /**
     * Carrega dados para o gráfico de ordens de produção
     */
    private function loadChartData()
    {
        if (Schema::hasTable('mrp_production_orders')) {
            try {
                // Dados para o gráfico de ordens de produção (simplificado)
                $this->productionChartData = [
                    'labels' => ['Planejadas', 'Em Progresso', 'Concluídas', 'Canceladas'],
                    'datasets' => [
                        [
                            'data' => [
                                $this->plannedProductionOrders,
                                $this->inProgressProductionOrders,
                                $this->completedProductionOrders,
                                ProductionOrder::where('status', 'cancelled')->count()
                            ],
                            'backgroundColor' => ['#3b82f6', '#eab308', '#16a34a', '#ef4444']
                        ]
                    ]
                ];
            } catch (\Exception $e) {
                // Log the error but don't crash the dashboard
                \Log::error('Error loading chart data: ' . $e->getMessage());
                $this->productionChartData = []; 
            }
        } else {
            $this->productionChartData = [
                'labels' => ['Planejadas', 'Em Progresso', 'Concluídas', 'Canceladas'],
                'datasets' => [
                    [
                        'data' => [0, 0, 0, 0],
                        'backgroundColor' => ['#3b82f6', '#eab308', '#16a34a', '#ef4444']
                    ]
                ]
            ];
        }
    }
    
    /**
     * Carrega alertas ativos para o dashboard
     */
    private function loadAlerts()
    {
        $this->alerts = [];
        $alertId = 1;
        
        // Verificar se existem as tabelas necessárias
        if (!Schema::hasTable('mrp_inventory_levels') || !Schema::hasTable('sc_products')) {
            // Adiciona alerta sobre tabelas ausentes
            $this->alerts[] = [
                'id' => $alertId++,
                'title' => 'Tabelas MRP não encontradas',
                'message' => 'Algumas tabelas necessárias para o MRP não foram encontradas no banco de dados.',
                'description' => 'Algumas tabelas necessárias para o MRP não foram encontradas no banco de dados.',
                'severity' => 'critical',
                'icon' => 'fa-database',
                'date' => now()->format('d/m/Y H:i')
            ];
            return;
        }
        
        // Alerta para itens com estoque abaixo do nível de segurança (crítico)
        if ($this->lowStockCount > 0) {
            $this->alerts[] = [
                'id' => $alertId++,
                'title' => 'Estoque crítico',
                'message' => "$this->lowStockCount produtos com estoque abaixo do nível de segurança",
                'description' => "$this->lowStockCount produtos estão com nível de estoque abaixo do mínimo de segurança definido. É recomendável repor o estoque urgentemente.",
                'severity' => 'critical',
                'icon' => 'fa-box-open',
                'date' => now()->format('d/m/Y H:i')
            ];
        }
        
        // Alerta para itens com estoque entre segurança e ponto de reposição (alta prioridade)
        if ($this->mediumStockCount > 0) {
            $this->alerts[] = [
                'id' => $alertId++,
                'title' => 'Estoque baixo',
                'message' => "$this->mediumStockCount produtos com estoque abaixo do ponto de reposição",
                'description' => "$this->mediumStockCount produtos estão com nível de estoque abaixo do ponto de reposição. É recomendável planejar a reposição destes itens.",
                'severity' => 'high',
                'icon' => 'fa-boxes',
                'date' => now()->format('d/m/Y H:i')
            ];
        }
        
        // Verificar ordens de produção com atraso
        if (Schema::hasTable('mrp_production_orders')) {
            try {
                // Ordens de produção que deveriam ter começado mas não começaram
                $lateStartCount = ProductionOrder::whereIn('status', ['draft', 'released'])
                    ->where('planned_start_date', '<', now())
                    ->count();
                    
                if ($lateStartCount > 0) {
                    $this->alerts[] = [
                        'id' => $alertId++,
                        'title' => 'Início de produção atrasado',
                        'message' => "$lateStartCount ordens de produção com início atrasado",
                        'description' => "$lateStartCount ordens de produção já deveriam ter sido iniciadas, mas ainda estão em status de rascunho ou liberadas. Verifique possíveis atrasos na produção.",
                        'severity' => 'high',
                        'icon' => 'fa-clock',
                        'date' => now()->format('d/m/Y H:i')
                    ];
                }
                
                // Ordens em progresso com data de término excedida
                $lateFinishCount = ProductionOrder::where('status', 'in_progress')
                    ->where('planned_end_date', '<', now())
                    ->count();
                    
                if ($lateFinishCount > 0) {
                    $this->alerts[] = [
                        'id' => $alertId++,
                        'title' => 'Conclusão de produção atrasada',
                        'message' => "$lateFinishCount ordens de produção em atraso para conclusão",
                        'description' => "$lateFinishCount ordens de produção em andamento já ultrapassaram a data planejada de término. É necessário verificar o que está causando os atrasos.",
                        'severity' => 'critical',
                        'icon' => 'fa-exclamation-circle',
                        'date' => now()->format('d/m/Y H:i')
                    ];
                }
            } catch (\Exception $e) {
                \Log::error('Error loading production alerts: ' . $e->getMessage());
            }
        }
        
        // Verificar planos de compra atrasados
        if (Schema::hasTable('mrp_purchase_plans')) {
            try {
                // Compras aprovadas mas ainda não ordenadas que estão atrasadas
                $latePurchaseCount = PurchasePlan::where('status', 'approved')
                    ->where('planned_order_date', '<', now())
                    ->count();
                    
                if ($latePurchaseCount > 0) {
                    $this->alerts[] = [
                        'id' => $alertId++,
                        'title' => 'Pedidos de compra atrasados',
                        'message' => "$latePurchaseCount planos de compra aprovados mas ainda não ordenados",
                        'description' => "$latePurchaseCount planos de compra foram aprovados mas ainda não foram convertidos em pedidos de compra efetivos. Isso pode causar atrasos na produção.",
                        'severity' => 'high',
                        'icon' => 'fa-shopping-cart',
                        'date' => now()->format('d/m/Y H:i')
                    ];
                }
            } catch (\Exception $e) {
                \Log::error('Error loading purchase alerts: ' . $e->getMessage());
            }
        }
        
        // Se não há alertas, colocar um alerta neutro
        if (empty($this->alerts)) {
            $this->alerts[] = [
                'id' => $alertId++,
                'title' => 'Sistema operando normalmente',
                'message' => 'Não há alertas ativos no momento',
                'description' => 'Todos os processos do sistema MRP estão funcionando corretamente. Não há alertas ou notificações que requeiram atenção no momento.',
                'severity' => 'info',
                'icon' => 'fa-check-circle',
                'date' => now()->format('d/m/Y H:i')
            ];
        }
    }
    
    /**
     * Carrega dados para o gráfico de demanda vs capacidade
     */
    private function loadDemandCapacityChartData()
    {
        // Inicialização dos dados do gráfico
        $this->demandCapacityChartData = [
            'labels' => [],
            'datasets' => [
                [
                    'label' => 'Demanda',
                    'data' => [],
                    'backgroundColor' => 'rgba(79, 70, 229, 0.6)',
                    'borderColor' => 'rgb(79, 70, 229)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Capacidade',
                    'data' => [],
                    'backgroundColor' => 'rgba(16, 185, 129, 0.6)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 1
                ]
            ]
        ];
        
        // Verifica se as tabelas necessárias existem
        if (!Schema::hasTable('mrp_demand_forecasts') || !Schema::hasTable('mrp_production_orders')) {
            // Se as tabelas não existirem, use dados de exemplo
            $this->loadDemandCapacityChartDataExample();
            return;
        }
        
        try {
            // Próximos 6 meses para o gráfico
            $labels = [];
            $demandData = [];
            $capacityData = [];
            
            // Obter os próximos 6 meses
            $currentDate = now();
            for ($i = 0; $i < 6; $i++) {
                $month = $currentDate->copy()->addMonths($i);
                $monthLabel = $month->format('M Y'); // Ex: Jan 2025
                $labels[] = $monthLabel;
                
                $startOfMonth = $month->copy()->startOfMonth();
                $endOfMonth = $month->copy()->endOfMonth();
                
                // Calcular demanda prevista para o mês
                $demand = DemandForecast::whereBetween('forecast_date', [$startOfMonth, $endOfMonth])
                    ->sum('forecast_quantity');
                $demandData[] = $demand ?: 0;
                
                // Calcular capacidade de produção para o mês (simplificado)
                // Em um sistema real, isso viria de uma tabela de capacidade de produção
                // Por enquanto, usará um valor fixo ou baseado em histórico de produção
                $capacity = ProductionOrder::whereBetween('planned_end_date', [$startOfMonth, $endOfMonth])
                    ->where('status', 'completed')
                    ->sum('quantity');
                    
                // Se não houver histórico, usar um valor estimado baseado na demanda
                if ($capacity == 0) {
                    $capacity = $demand * 1.2; // Capacidade 20% maior que a demanda como exemplo
                }
                
                $capacityData[] = $capacity ?: 0;
            }
            
            // Atualizar os dados do gráfico
            $this->demandCapacityChartData['labels'] = $labels;
            $this->demandCapacityChartData['datasets'][0]['data'] = $demandData;
            $this->demandCapacityChartData['datasets'][1]['data'] = $capacityData;
        } catch (\Exception $e) {
            \Log::error('Error loading demand vs capacity chart data: ' . $e->getMessage());
            $this->loadDemandCapacityChartDataExample();
        }
    }
    
    /**
     * Carrega dados de exemplo para o gráfico de demanda vs capacidade
     */
    private function loadDemandCapacityChartDataExample()
    {
        // Dados de exemplo para o gráfico quando não há dados reais disponíveis
        $this->demandCapacityChartData = [
            'labels' => [
                'Jun 2025', 'Jul 2025', 'Ago 2025', 'Set 2025', 'Out 2025', 'Nov 2025'
            ],
            'datasets' => [
                [
                    'label' => 'Demanda',
                    'data' => [150, 180, 210, 200, 250, 280],
                    'backgroundColor' => 'rgba(79, 70, 229, 0.6)',
                    'borderColor' => 'rgb(79, 70, 229)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Capacidade',
                    'data' => [200, 200, 200, 250, 250, 300],
                    'backgroundColor' => 'rgba(16, 185, 129, 0.6)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 1
                ]
            ]
        ];
    }
    
    /**
     * Carrega as atividades recentes do módulo MRP
     */
    private function loadRecentActivities()
    {
        $this->recentActivities = [];
        $activityId = 1;
        
        // As atividades recentes podem vir de várias tabelas
        // Vamos verificar cada uma delas e adicionar as mais recentes
        
        // Verificação de ordens de produção recentes
        if (Schema::hasTable('mrp_production_orders')) {
            try {
                $recentProductions = ProductionOrder::latest('updated_at')
                    ->limit(3)
                    ->get();
                    
                foreach ($recentProductions as $production) {
                    $statusText = 'Atualizada';
                    $icon = 'fa-clipboard-list';
                    
                    if ($production->status === 'completed') {
                        $statusText = 'Concluída';
                        $icon = 'fa-check-circle';
                    } elseif ($production->status === 'in_progress') {
                        $statusText = 'Em progresso';
                        $icon = 'fa-cogs';
                    } elseif ($production->status === 'draft') {
                        $statusText = 'Criada como rascunho';
                        $icon = 'fa-pencil-alt';
                    } elseif ($production->status === 'released') {
                        $statusText = 'Liberada para produção';
                        $icon = 'fa-play';
                    } elseif ($production->status === 'cancelled') {
                        $statusText = 'Cancelada';
                        $icon = 'fa-times-circle';
                    }
                    
                    $this->recentActivities[] = [
                        'id' => $activityId++,
                        'icon' => $icon,
                        'title' => "Ordem de Produção #{$production->order_number} $statusText",
                        'time' => $production->updated_at->diffForHumans(),
                        'type' => 'production',
                        'typeText' => 'Produção'
                    ];
                }
            } catch (\Exception $e) {
                \Log::error('Error loading recent production orders: ' . $e->getMessage());
            }
        }
        
        // Verificação de planos de compra recentes
        if (Schema::hasTable('mrp_purchase_plans')) {
            try {
                $recentPurchases = PurchasePlan::latest('updated_at')
                    ->limit(3)
                    ->get();
                    
                foreach ($recentPurchases as $purchase) {
                    $statusText = 'Atualizado';
                    $icon = 'fa-shopping-cart';
                    
                    if ($purchase->status === 'ordered') {
                        $statusText = 'Pedido enviado';
                        $icon = 'fa-truck';
                    } elseif ($purchase->status === 'approved') {
                        $statusText = 'Aprovado';
                        $icon = 'fa-check';
                    } elseif ($purchase->status === 'draft') {
                        $statusText = 'Criado como rascunho';
                        $icon = 'fa-pencil-alt';
                    }
                    
                    $this->recentActivities[] = [
                        'id' => $activityId++,
                        'icon' => $icon,
                        'title' => "Plano de Compra #{$purchase->id} $statusText",
                        'time' => $purchase->updated_at->diffForHumans(),
                        'type' => 'purchase',
                        'typeText' => 'Compra'
                    ];
                }
            } catch (\Exception $e) {
                \Log::error('Error loading recent purchase plans: ' . $e->getMessage());
            }
        }
        
        // Verificação de atualizações de inventário
        if (Schema::hasTable('mrp_inventory_levels')) {
            try {
                $recentInventory = InventoryLevel::latest('updated_at')
                    ->limit(3)
                    ->get();
                    
                foreach ($recentInventory as $inventory) {
                    $this->recentActivities[] = [
                        'id' => $activityId++,
                        'icon' => 'fa-boxes',
                        'title' => "Nível de inventário atualizado para o produto #{$inventory->product_id}",
                        'time' => $inventory->updated_at->diffForHumans(),
                        'type' => 'inventory',
                        'typeText' => 'Inventário'
                    ];
                }
            } catch (\Exception $e) {
                \Log::error('Error loading recent inventory updates: ' . $e->getMessage());
            }
        }
        
        // Se não houver atividades recentes, adicionar uma mensagem de exemplo
        if (empty($this->recentActivities)) {
            $this->recentActivities[] = [
                'id' => $activityId++,
                'icon' => 'fa-info-circle',
                'title' => 'Sem atividades recentes',
                'time' => 'agora',
                'type' => 'info',
                'typeText' => 'Info'
            ];
        }
        
        // Ordenar atividades pela data mais recente (assumindo que o campo time já está formatado)
        // Em um ambiente de produção, seria melhor ordenar por um campo de data antes de formatar
        usort($this->recentActivities, function($a, $b) {
            // Aqui estamos apenas mantendo a ordem já definida
            return 0;
        });
    }
    
    /**
     * Visualizar detalhes de um alerta
     */
    public function viewAlert($alertId)
    {
        // Implemente a lógica para visualizar detalhes do alerta
        // Por enquanto, apenas registramos que o alerta foi visualizado
        \Log::info("Alerta #$alertId visualizado");
        
        // Futuro: redirecionar para a página relevante com base no tipo do alerta
        // Por exemplo, se for um alerta de estoque baixo, redirecionar para a página de estoque
    }
    
    public function refreshData()
    {
        $this->loadDashboardData();
    }
    
    public function render()
    {
        return view('livewire.mrp.mrp-dashboard');
    }
}
