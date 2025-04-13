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

    public function mount()
    {
        $this->loadDashboardData();
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
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'supplier' => $order->supplier->name,
                    'status' => $order->status,
                    'date' => $order->order_date->format('Y-m-d'),
                    'expected_delivery' => $order->expected_delivery_date ? $order->expected_delivery_date->format('Y-m-d') : 'Not specified',
                    'total' => $order->total_amount,
                    'is_overdue' => $order->expected_delivery_date && $order->expected_delivery_date->isPast(),
                    'receipt_percentage' => $order->receipt_percentage
                ];
            })
            ->toArray();
    }

    protected function loadRecentTransactions()
    {
        // Get recent inventory transactions
        $this->recentTransactions = InventoryTransaction::with(['product', 'sourceLocation', 'destinationLocation', 'creator'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'number' => $transaction->transaction_number,
                    'date' => $transaction->created_at->format('Y-m-d H:i'),
                    'product' => $transaction->product->name,
                    'type' => $transaction->transaction_type,
                    'quantity' => $transaction->quantity,
                    'source' => $transaction->sourceLocation->name ?? 'N/A',
                    'destination' => $transaction->destinationLocation->name ?? 'N/A',
                    'created_by' => $transaction->creator->name
                ];
            })
            ->toArray();
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
            'totalStockItems' => $this->totalStockItems
        ]);
    }
}
