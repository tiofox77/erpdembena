<?php

namespace App\Livewire\SupplyChain;

use Livewire\Component;
use App\Models\SupplyChain\Product;
use App\Models\SupplyChain\PurchaseOrder;
use App\Models\SupplyChain\InventoryItem;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\InventoryTransaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Dashboard extends Component
{
    public $stockAlerts = [];
    public $pendingOrders = [];
    public $recentTransactions = [];
    public $inventoryValueByCategory = [];
    public $inventoryTurnover = [];
    public $supplierPerformance = [];
    public $orderStatusSummary = [];
    public $inventoryValue = 0;
    public $avgLeadTime = 0;
    public $pendingOrdersValue = 0;
    public $totalStockItems = 0;
    public $dateRange = '30days'; // Default 30 days
    public $timeRange = 'month'; // Padrão para período de tempo nos gráficos
    public $chartData = [];

    public function mount()
    {
        // Carregar dados iniciais
        $this->loadDashboardData();
        $this->prepareChartData();
    }

    public function loadDashboardData()
    {
        $this->loadStockAlerts();
        $this->loadPendingOrders();
        $this->loadRecentTransactions();
        $this->loadInventoryValueByCategory();
        $this->loadInventoryTurnover();
        $this->loadSupplierPerformance();
        $this->loadOrderStatusSummary();
        $this->loadKPIs();
    }

    protected function loadStockAlerts()
    {
        // Get products that are below their reorder point
        $this->stockAlerts = Product::with(['inventoryItems', 'category'])
            ->whereHas('inventoryItems', function ($q) {
                $q->whereRaw('quantity_on_hand <= sc_products.reorder_point');
            })
            ->limit(10)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'category' => $product->category->name ?? 'Uncategorized',
                    'current_stock' => $product->inventoryItems->sum('quantity_on_hand'),
                    'reorder_point' => $product->reorder_point,
                    'min_stock_level' => $product->min_stock_level,
                    'status' => $product->inventoryItems->sum('quantity_on_hand') <= $product->min_stock_level ? 'critical' : 'warning'
                ];
            })
            ->toArray();
    }

    protected function loadPendingOrders()
    {
        // Get pending purchase orders
        $this->pendingOrders = PurchaseOrder::with(['supplier'])
            ->whereIn('status', ['approved', 'ordered', 'partially_received'])
            ->orderBy('expected_delivery_date')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                $isOverdue = $order->expected_delivery_date && $order->expected_delivery_date->isPast();
                $approachingDelivery = false;
                $daysRemaining = 0;
                $daysOverdue = 0;
                
                if ($order->expected_delivery_date) {
                    if ($isOverdue) {
                        $daysOverdue = now()->diffInDays($order->expected_delivery_date);
                    } else {
                        $daysRemaining = now()->diffInDays($order->expected_delivery_date);
                        $approachingDelivery = $daysRemaining <= 15;
                    }
                }
                
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'supplier' => $order->supplier->name,
                    'status' => $order->status,
                    'status_color' => $this->getStatusColorClass($order->status),
                    'date' => $order->order_date->format('Y-m-d'),
                    'expected_date' => $order->expected_delivery_date ? $order->expected_delivery_date->format('Y-m-d') : 'Not specified',
                    'total' => $order->total_amount,
                    'is_overdue' => $isOverdue,
                    'approaching_delivery' => $approachingDelivery,
                    'days_remaining' => $daysRemaining,
                    'days_overdue' => $daysOverdue,
                    'receipt_percentage' => $order->receipt_percentage
                ];
            })
            ->toArray();
    }

    protected function getStatusColorClass($status)
    {
        return match ($status) {
            'draft' => 'bg-gray-100 text-gray-800',
            'pending_approval' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-blue-100 text-blue-800',
            'ordered' => 'bg-indigo-100 text-indigo-800',
            'partially_received' => 'bg-sky-100 text-sky-800',
            'completed' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    protected function loadRecentTransactions()
    {
        // Get recent inventory transactions
        $this->recentTransactions = InventoryTransaction::with(['product', 'sourceLocation', 'destinationLocation', 'creator'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($transaction) {
                $referenceInfo = $this->getTransactionReference($transaction);
                
                return [
                    'id' => $transaction->id,
                    'number' => $transaction->transaction_number,
                    'date' => $transaction->created_at->format('Y-m-d'),
                    'time' => $transaction->created_at->format('H:i'),
                    'product' => $transaction->product->name,
                    'product_image' => $transaction->product->image ?? null,
                    'sku' => $transaction->product->sku ?? null,
                    'type' => $transaction->transaction_type,
                    'type_class' => $this->getTransactionTypeClass($transaction->transaction_type),
                    'quantity' => $transaction->quantity,
                    'source' => $transaction->sourceLocation->name ?? 'N/A',
                    'destination' => $transaction->destinationLocation->name ?? 'N/A',
                    'created_by' => $transaction->creator->name,
                    'reference' => $referenceInfo['label'] ?? $transaction->transaction_number,
                    'reference_url' => $referenceInfo['url'] ?? null,
                ];
            })
            ->toArray();
    }
    
    protected function getTransactionTypeClass($type)
    {
        switch ($type) {
            case 'purchase_receipt':
            case 'inventory_adjustment_increase':
            case 'daily_production_fg': // Adição de produto acabado
                return 'bg-green-100 text-green-800';
                
            case 'sales_issue':
            case 'inventory_adjustment_decrease':
            case 'daily_production': // Consumo de matéria-prima
                return 'bg-red-100 text-red-800';
                
            case 'transfer':
                return 'bg-blue-100 text-blue-800';
                
            case 'production':
            case 'production_receipt':
            case 'production_issue':
            case 'production_order':
            case 'raw_production':
                return 'bg-purple-100 text-purple-800';
                
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }
    
    protected function getTransactionReference($transaction)
    {
        $result = [
            'label' => $transaction->transaction_number,
            'url' => null
        ];
        
        // Determinar referência com base no tipo de transação
        switch ($transaction->transaction_type) {
            case 'purchase_order_receipt':
                if ($transaction->purchase_order_id) {
                    $result['label'] = 'PO #' . $transaction->purchase_order_id;
                    $result['url'] = route('supply-chain.purchase-orders', ['id' => $transaction->purchase_order_id]);
                }
                break;
                
            case 'internal_transfer':
                $result['label'] = 'Transfer #' . $transaction->id;
                break;
                
            case 'inventory_adjustment':
                $result['label'] = 'Adjustment #' . $transaction->id;
                break;
                
            case 'sales_order':
                if ($transaction->sales_order_id) {
                    $result['label'] = 'SO #' . $transaction->sales_order_id;
                    // $result['url'] = route('sales.orders', ['id' => $transaction->sales_order_id]);
                }
                break;
                
            default:
                $result['label'] = 'Tx #' . $transaction->transaction_number;
                break;
        }
        
        return $result;
    }

    protected function loadInventoryValueByCategory()
    {
        // Calculate inventory value by product category
        $this->inventoryValueByCategory = DB::table('sc_products')
            ->join('sc_inventory_items', 'sc_products.id', '=', 'sc_inventory_items.product_id')
            ->leftJoin('sc_product_categories', 'sc_products.category_id', '=', 'sc_product_categories.id')
            ->select(
                'sc_product_categories.name as category',
                DB::raw('COALESCE(sc_product_categories.name, "Uncategorized") as category_name'),
                DB::raw('SUM(sc_inventory_items.quantity_on_hand * sc_products.cost_price) as value')
            )
            ->groupBy('category')
            ->orderByDesc('value')
            ->get()
            ->toArray();
    }

    protected function loadInventoryTurnover()
    {
        // Calculate inventory turnover for the last 6 months
        $sixMonthsAgo = Carbon::now()->subMonths(6)->startOfMonth();
        $dates = [];
        $turnover = [];

        for ($i = 0; $i < 6; $i++) {
            $month = Carbon::now()->subMonths(5 - $i)->startOfMonth();
            $dates[] = $month->format('M Y');

            // This is a simplified calculation, would normally involve COGS
            $monthTurnover = InventoryTransaction::where('transaction_type', 'sales_issue')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('total_cost');

            $avgInventory = DB::table('sc_inventory_items')
                ->join('sc_products', 'sc_inventory_items.product_id', '=', 'sc_products.id')
                ->whereYear('sc_inventory_items.created_at', '<=', $month->year)
                ->whereMonth('sc_inventory_items.created_at', '<=', $month->month)
                ->sum(DB::raw('sc_inventory_items.quantity_on_hand * sc_products.cost_price')) / 2;

            $turnover[] = $avgInventory > 0 ? round($monthTurnover / $avgInventory, 2) : 0;
        }

        $this->inventoryTurnover = [
            'dates' => $dates,
            'values' => $turnover
        ];
    }

    protected function loadSupplierPerformance()
    {
        // Calculate supplier performance based on delivery times and quality
        $this->supplierPerformance = Supplier::withCount(['purchaseOrders'])
            ->with(['purchaseOrders' => function ($query) {
                $query->with('goodsReceipts')
                    ->whereNotNull('delivery_date')
                    ->whereNotNull('expected_delivery_date');
            }])
            ->having('purchase_orders_count', '>', 0)
            ->limit(10)
            ->get()
            ->map(function ($supplier) {
                $onTimeDeliveries = 0;
                $totalDeliveries = 0;
                $leadTimeDays = [];

                foreach ($supplier->purchaseOrders as $order) {
                    if ($order->delivery_date && $order->expected_delivery_date) {
                        $totalDeliveries++;
                        
                        if (!$order->delivery_date->isAfter($order->expected_delivery_date)) {
                            $onTimeDeliveries++;
                        }
                        
                        $leadTimeDays[] = $order->delivery_date->diffInDays($order->order_date);
                    }
                }

                $onTimePercentage = $totalDeliveries > 0 ? round(($onTimeDeliveries / $totalDeliveries) * 100) : 0;
                $avgLeadTime = count($leadTimeDays) > 0 ? round(array_sum($leadTimeDays) / count($leadTimeDays)) : 0;

                return [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'on_time_delivery' => $onTimePercentage,
                    'avg_lead_time' => $avgLeadTime,
                    'total_orders' => $supplier->purchase_orders_count,
                    'performance_rating' => $this->calculatePerformanceRating($onTimePercentage, $avgLeadTime)
                ];
            })
            ->toArray();
    }

    protected function calculatePerformanceRating($onTimePercentage, $leadTime)
    {
        // Simple scoring model
        if ($onTimePercentage >= 95) {
            return 'Excellent';
        } elseif ($onTimePercentage >= 80) {
            return 'Good';
        } elseif ($onTimePercentage >= 60) {
            return 'Average';
        } else {
            return 'Poor';
        }
    }

    protected function loadOrderStatusSummary()
    {
        // Summarize purchase orders by status
        $counts = PurchaseOrder::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        $this->orderStatusSummary = [
            'draft' => $counts['draft'] ?? 0,
            'pending_approval' => $counts['pending_approval'] ?? 0,
            'approved' => $counts['approved'] ?? 0,
            'ordered' => $counts['ordered'] ?? 0,
            'partially_received' => $counts['partially_received'] ?? 0,
            'completed' => $counts['completed'] ?? 0,
            'cancelled' => $counts['cancelled'] ?? 0
        ];
    }

    protected function loadKPIs()
    {
        // Calculate total inventory value
        $this->inventoryValue = DB::table('sc_inventory_items')
            ->join('sc_products', 'sc_inventory_items.product_id', '=', 'sc_products.id')
            ->sum(DB::raw('sc_inventory_items.quantity_on_hand * COALESCE(sc_inventory_items.unit_cost, sc_products.cost_price, 0)'));
        
        // Calculate average lead time
        $this->avgLeadTime = PurchaseOrder::whereNotNull('delivery_date')
            ->whereNotNull('order_date')
            ->get()
            ->avg(function ($order) {
                return $order->delivery_date->diffInDays($order->order_date);
            }) ?? 0;
        
        // Calculate value of pending orders
        $this->pendingOrdersValue = PurchaseOrder::whereIn('status', ['approved', 'ordered', 'partially_received'])
            ->sum('total_amount');
        
        // Count total stock items
        $this->totalStockItems = Product::where('is_stockable', true)->count();
    }

    public function refreshData()
    {
        $this->loadDashboardData();
        $this->prepareChartData();
        $this->dispatch('notify', 
            type: 'info', 
            message: __('messages.data_refreshed_successfully')
        );
    }

    public function prepareChartData()
    {
        // Preparar dados para os gráficos
        $this->chartData = [
            'purchaseOrderStatus' => $this->preparePurchaseOrderStatusChart(),
            'inventoryTrend' => $this->prepareInventoryTrendChart(),
            'topProducts' => $this->prepareTopProductsChart(),
            'suppliers' => $this->prepareSuppliersChart(),
            'stockForecast' => $this->prepareStockForecastChart()
        ];
        
        // Disparar evento para atualizar gráficos via JavaScript
        $this->dispatch('chartDataUpdated', $this->chartData);
    }

    protected function preparePurchaseOrderStatusChart()
    {
        // Gráfico de pizza/donut para status das ordens de compra
        $statuses = [
            'draft' => __('messages.draft'),
            'pending_approval' => __('messages.pending_approval'),
            'approved' => __('messages.approved'),
            'ordered' => __('messages.ordered'),
            'partially_received' => __('messages.partially_received'),
            'completed' => __('messages.completed'),
            'cancelled' => __('messages.cancelled')
        ];
        
        $series = [];
        $labels = [];
        $colors = [
            '#CBD5E1', // draft - slate-300
            '#FDE68A', // pending_approval - amber-200
            '#93C5FD', // approved - blue-300
            '#A78BFA', // ordered - violet-400
            '#7DD3FC', // partially_received - sky-300
            '#86EFAC', // completed - green-300
            '#FCA5A5'  // cancelled - red-300
        ];
        
        foreach ($statuses as $status => $label) {
            $count = $this->orderStatusSummary[$status] ?? 0;
            if ($count > 0) {
                $series[] = $count;
                $labels[] = $label;
            }
        }
        
        return [
            'series' => $series,
            'labels' => $labels,
            'colors' => $colors
        ];
    }
    
    protected function prepareInventoryTrendChart()
    {
        // Definir o período baseado no timeRange selecionado
        $daysToLookBack = match($this->timeRange) {
            'today' => 1,
            'week' => 7,
            'month' => 30,
            'quarter' => 90,
            'year' => 365,
            default => 30
        };
        
        $startDate = Carbon::now()->subDays($daysToLookBack)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        
        // Preparar datas para o eixo X
        $dates = [];
        $currentDate = clone $startDate;
        $interval = $daysToLookBack > 60 ? 7 : 1; // Agrupar por semana se for mais de 60 dias
        
        while ($currentDate <= $endDate) {
            $dates[] = $currentDate->format('Y-m-d');
            $currentDate->addDays($interval);
        }
        
        // Obter transações agrupadas por tipo
        $receipts = InventoryTransaction::where('transaction_type', 'purchase_receipt')
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->selectRaw('DATE(created_at) as date, SUM(quantity) as total')
            ->groupBy('date')
            ->pluck('total', 'date')
            ->toArray();
            
        $transfers = InventoryTransaction::where('transaction_type', 'transfer')
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->selectRaw('DATE(created_at) as date, SUM(quantity) as total')
            ->groupBy('date')
            ->pluck('total', 'date')
            ->toArray();
            
        $adjustments = InventoryTransaction::whereIn('transaction_type', ['adjustment', 'consumption'])
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->selectRaw('DATE(created_at) as date, SUM(quantity) as total')
            ->groupBy('date')
            ->pluck('total', 'date')
            ->toArray();
        
        // Preparar séries para o gráfico
        $receiptsSeries = [];
        $transfersSeries = [];
        $adjustmentsSeries = [];
        
        foreach ($dates as $date) {
            $receiptsSeries[] = $receipts[$date] ?? 0;
            $transfersSeries[] = $transfers[$date] ?? 0;
            $adjustmentsSeries[] = $adjustments[$date] ?? 0;
        }
        
        return [
            'dates' => $dates,
            'series' => [
                [
                    'name' => __('messages.receipts'),
                    'data' => $receiptsSeries
                ],
                [
                    'name' => __('messages.transfers'),
                    'data' => $transfersSeries
                ],
                [
                    'name' => __('messages.adjustments'),
                    'data' => $adjustmentsSeries
                ]
            ]
        ];
    }
    
    protected function prepareTopProductsChart()
    {
        // Obter os produtos mais movimentados com base nas transações
        $topProducts = DB::table('sc_inventory_transactions')
            ->join('sc_products', 'sc_inventory_transactions.product_id', '=', 'sc_products.id')
            ->selectRaw('sc_products.id, sc_products.name, SUM(ABS(sc_inventory_transactions.quantity)) as total_movement')
            ->groupBy('sc_products.id', 'sc_products.name')
            ->orderByDesc('total_movement')
            ->limit(5)
            ->get();
        
        $labels = [];
        $data = [];
        $colors = ['#4F46E5', '#2563EB', '#3B82F6', '#60A5FA', '#93C5FD']; // Tons de azul
        
        foreach ($topProducts as $product) {
            $labels[] = $product->name;
            $data[] = $product->total_movement;
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => $colors
        ];
    }
    
    protected function prepareSuppliersChart()
    {
        // Obter os principais fornecedores por volume de ordens de compra
        $topSuppliers = DB::table('sc_purchase_orders')
            ->join('sc_suppliers', 'sc_purchase_orders.supplier_id', '=', 'sc_suppliers.id')
            ->selectRaw('sc_suppliers.id, sc_suppliers.name, COUNT(sc_purchase_orders.id) as order_count, SUM(sc_purchase_orders.total_amount) as total_amount')
            ->where('sc_purchase_orders.status', '!=', 'draft')
            ->where('sc_purchase_orders.status', '!=', 'cancelled')
            ->groupBy('sc_suppliers.id', 'sc_suppliers.name')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();
        
        $series = [];
        $labels = [];
        
        foreach ($topSuppliers as $supplier) {
            $series[] = round($supplier->total_amount, 2);
            $labels[] = $supplier->name;
        }
        
        return [
            'series' => $series,
            'labels' => $labels
        ];
    }
    
    protected function prepareStockForecastChart()
    {
        // Simulação de previsão de estoque para os próximos 6 meses
        // Em um ambiente real, isso seria baseado em um modelo de previsão mais complexo
        
        $dates = [];
        $currentDate = Carbon::now()->startOfMonth();
        
        for ($i = 0; $i < 6; $i++) {
            $dates[] = $currentDate->format('M Y');
            $currentDate->addMonth();
        }
        
        // Calcular tendência de consumo baseado nos últimos 3 meses
        $consumptionRate = DB::table('sc_inventory_transactions')
            ->where('transaction_type', 'consumption')
            ->where('created_at', '>=', Carbon::now()->subMonths(3))
            ->avg('quantity') ?? 0;
        
        $consumptionRate = abs($consumptionRate) * 30; // Consumo mensal estimado
        
        // Estoque atual
        $currentStock = InventoryItem::sum('quantity_on_hand');
        
        // Ordens de compra pendentes nos próximos meses
        $pendingOrders = PurchaseOrder::whereIn('status', ['approved', 'ordered', 'partially_received'])
            ->where('expected_delivery_date', '>=', Carbon::now())
            ->where('expected_delivery_date', '<=', Carbon::now()->addMonths(6))
            ->get()
            ->groupBy(function ($order) {
                return $order->expected_delivery_date->format('Y-m');
            })
            ->map(function ($orders) {
                return $orders->sum('total_amount');
            })
            ->toArray();
        
        // Simular estoque projetado para cada mês
        $projectedStockLevels = [];
        $projectedPurchases = [];
        $stockLevel = $currentStock;
        
        foreach ($dates as $index => $date) {
            $ym = Carbon::createFromFormat('M Y', $date)->format('Y-m');
            $incomingOrder = $pendingOrders[$ym] ?? 0;
            
            // Convertendo valor para quantidade estimada (simplificação)
            $incomingQuantity = round($incomingOrder / 100); // Estimativa simples
            
            // Atualizar estoque projetado
            $stockLevel = max(0, $stockLevel - $consumptionRate + $incomingQuantity);
            
            $projectedStockLevels[] = round($stockLevel);
            $projectedPurchases[] = $incomingQuantity;
        }
        
        return [
            'dates' => $dates,
            'series' => [
                [
                    'name' => __('messages.projected_stock'),
                    'data' => $projectedStockLevels
                ],
                [
                    'name' => __('messages.projected_purchases'),
                    'data' => $projectedPurchases
                ]
            ]
        ];
    }
    
    public function updatedTimeRange()
    {
        // Quando mudar o período, atualizar os dados dos gráficos
        $this->prepareChartData();
        $this->dispatch('notify', 
            type: 'info', 
            message: __('messages.dashboard_time_range_updated')
        );
    }

    public function render()
    {
        // Define metrics for display cards
        $metrics = [
            [
                'title' => __('messages.total_inventory_value'),
                'value' => number_format($this->inventoryValue, 2) . ' ' . config('app.currency', '$'),
                'icon' => 'fas fa-warehouse',
                'color' => 'border-blue-500'
            ],
            [
                'title' => __('messages.avg_lead_time'),
                'value' => round($this->avgLeadTime) . ' ' . __('messages.days'),
                'icon' => 'fas fa-truck',
                'color' => 'border-green-500'
            ],
            [
                'title' => __('messages.pending_orders_value'),
                'value' => number_format($this->pendingOrdersValue, 2) . ' ' . config('app.currency', '$'),
                'icon' => 'fas fa-shopping-cart',
                'color' => 'border-purple-500'
            ],
            [
                'title' => __('messages.stock_items'),
                'value' => $this->totalStockItems,
                'icon' => 'fas fa-boxes',
                'color' => 'border-yellow-500'
            ]
        ];

        return view('livewire.supply-chain.dashboard', [
            'metrics' => $metrics,
            'lowStockItems' => $this->stockAlerts,
            'pendingOrders' => $this->pendingOrders,
            'recentTransactions' => $this->recentTransactions,
            'inventoryValueByCategory' => $this->inventoryValueByCategory,
            'inventoryTurnover' => $this->inventoryTurnover,
            'supplierPerformance' => $this->supplierPerformance,
            'orderStatusSummary' => $this->orderStatusSummary,
            'inventoryValue' => $this->inventoryValue,
            'avgLeadTime' => $this->avgLeadTime,
            'pendingOrdersValue' => $this->pendingOrdersValue,
            'totalStockItems' => $this->totalStockItems,
            'chartData' => $this->chartData
        ]);
    }
}
