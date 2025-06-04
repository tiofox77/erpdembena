<?php

namespace App\Livewire\Mrp;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Mrp\ProductionOrder;
use App\Models\Mrp\PurchasePlan;
use App\Models\SupplyChain\Product;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;

class FinancialReporting extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'date';
    public $sortDirection = 'desc';
    public $timeframe = 'month';
    public $reportType = 'production-costs';
    public $dateRange = [
        'start' => null,
        'end' => null
    ];
    public $productFilter = null;
    public $showReportModal = false;
    public $reportData = [];
    public $reportTitle = '';
    public $reportDescription = '';
    public $chartOptions = [];
    public $chartData = [];
    
    public $reportTypes = [
        'production-costs' => 'Production Costs',
        'purchase-costs' => 'Purchase Costs',
        'cost-comparison' => 'Cost Comparison',
        'product-profitability' => 'Product Profitability'
    ];
    
    public $timeframes = [
        'week' => 'Weekly',
        'month' => 'Monthly',
        'quarter' => 'Quarterly',
        'year' => 'Yearly',
        'custom' => 'Custom Range'
    ];

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function mount()
    {
        // Inicializa o período como o mês atual
        $this->dateRange['start'] = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateRange['end'] = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTimeframe($value)
    {
        $this->timeframe = $value;
        $this->updateDateRange();
    }

    public function updateDateRange()
    {
        $now = Carbon::now();
        
        switch ($this->timeframe) {
            case 'week':
                $this->dateRange['start'] = $now->copy()->startOfWeek()->format('Y-m-d');
                $this->dateRange['end'] = $now->copy()->endOfWeek()->format('Y-m-d');
                break;
            case 'month':
                $this->dateRange['start'] = $now->copy()->startOfMonth()->format('Y-m-d');
                $this->dateRange['end'] = $now->copy()->endOfMonth()->format('Y-m-d');
                break;
            case 'quarter':
                $this->dateRange['start'] = $now->copy()->startOfQuarter()->format('Y-m-d');
                $this->dateRange['end'] = $now->copy()->endOfQuarter()->format('Y-m-d');
                break;
            case 'year':
                $this->dateRange['start'] = $now->copy()->startOfYear()->format('Y-m-d');
                $this->dateRange['end'] = $now->copy()->endOfYear()->format('Y-m-d');
                break;
            // Caso 'custom', mantém as datas existentes
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
    }

    public function generateReport()
    {
        $this->validate([
            'reportType' => ['required', Rule::in(array_keys($this->reportTypes))],
            'dateRange.start' => 'required|date',
            'dateRange.end' => 'required|date|after_or_equal:dateRange.start',
        ]);

        // Executar a função correspondente ao tipo de relatório
        switch ($this->reportType) {
            case 'production-costs':
                $this->generateProductionCostsReport();
                break;
            case 'purchase-costs':
                $this->generatePurchaseCostsReport();
                break;
            case 'cost-comparison':
                $this->generateCostComparisonReport();
                break;
            case 'product-profitability':
                $this->generateProductProfitabilityReport();
                break;
        }

        $this->showReportModal = true;
    }

    private function generateProductionCostsReport()
    {
        $this->reportTitle = __('messages.production_costs_report');
        $this->reportDescription = __('messages.production_costs_report_description', [
            'start' => Carbon::parse($this->dateRange['start'])->format('d/m/Y'),
            'end' => Carbon::parse($this->dateRange['end'])->format('d/m/Y')
        ]);

        $query = ProductionOrder::whereBetween('planned_start_date', [$this->dateRange['start'], $this->dateRange['end']])
            ->where('status', '!=', 'cancelled');

        if ($this->productFilter) {
            $query->where('product_id', $this->productFilter);
        }

        // Agrupando por produto e calculando custos
        $productionCosts = $query->select(
            'product_id',
            DB::raw('SUM(planned_quantity) as total_planned'),
            DB::raw('SUM(produced_quantity) as total_produced'),
            DB::raw('SUM(rejected_quantity) as total_rejected'),
            DB::raw('AVG(unit_cost) as avg_unit_cost'),
            DB::raw('SUM(unit_cost * planned_quantity) as total_planned_cost'),
            DB::raw('SUM(unit_cost * produced_quantity) as total_actual_cost')
        )
        ->with('product')
        ->groupBy('product_id')
        ->get();

        // Preparação dos dados do relatório
        $this->reportData = $productionCosts->map(function ($item) {
            $efficiency = $item->total_planned > 0 ? 
                round(($item->total_produced / $item->total_planned) * 100, 2) : 0;
            
            $variance = $item->total_planned_cost > 0 ? 
                round((($item->total_actual_cost - $item->total_planned_cost) / $item->total_planned_cost) * 100, 2) : 0;
            
            return [
                'product_name' => $item->product->name,
                'product_sku' => $item->product->sku,
                'planned_qty' => $item->total_planned,
                'produced_qty' => $item->total_produced,
                'rejected_qty' => $item->total_rejected,
                'avg_unit_cost' => $item->avg_unit_cost,
                'total_planned_cost' => $item->total_planned_cost,
                'total_actual_cost' => $item->total_actual_cost,
                'efficiency' => $efficiency,
                'cost_variance' => $variance
            ];
        })->toArray();

        // Preparação dos dados para o gráfico
        $labels = $productionCosts->pluck('product.name')->toArray();
        $plannedCosts = $productionCosts->pluck('total_planned_cost')->toArray();
        $actualCosts = $productionCosts->pluck('total_actual_cost')->toArray();
        
        $this->chartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => __('messages.planned_cost'),
                    'data' => $plannedCosts,
                    'backgroundColor' => '#4299e1', // azul
                    'borderColor' => '#3182ce',
                    'borderWidth' => 1
                ],
                [
                    'label' => __('messages.actual_cost'),
                    'data' => $actualCosts,
                    'backgroundColor' => '#48bb78', // verde
                    'borderColor' => '#38a169',
                    'borderWidth' => 1
                ]
            ]
        ];
        
        $this->chartOptions = [
            'type' => 'bar',
            'options' => [
                'responsive' => true,
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'title' => [
                            'display' => true,
                            'text' => __('messages.cost_in_currency')
                        ]
                    ],
                    'x' => [
                        'title' => [
                            'display' => true,
                            'text' => __('messages.products')
                        ]
                    ]
                ]
            ]
        ];
    }

    private function generatePurchaseCostsReport()
    {
        $this->reportTitle = __('messages.purchase_costs_report');
        $this->reportDescription = __('messages.purchase_costs_report_description', [
            'start' => Carbon::parse($this->dateRange['start'])->format('d/m/Y'),
            'end' => Carbon::parse($this->dateRange['end'])->format('d/m/Y')
        ]);

        $query = PurchasePlan::whereBetween('required_date', [$this->dateRange['start'], $this->dateRange['end']])
            ->where('status', '!=', 'cancelled');

        if ($this->productFilter) {
            $query->where('product_id', $this->productFilter);
        }

        // Agrupando por produto
        $purchaseCosts = $query->select(
            'product_id',
            DB::raw('SUM(required_quantity) as total_quantity'),
            DB::raw('AVG(unit_price) as avg_unit_price'),
            DB::raw('SUM(required_quantity * unit_price) as total_cost')
        )
        ->with('product', 'supplier')
        ->groupBy('product_id')
        ->get();

        // Preparação dos dados do relatório
        $this->reportData = $purchaseCosts->map(function ($item) {
            return [
                'product_name' => $item->product->name,
                'product_sku' => $item->product->sku,
                'total_quantity' => $item->total_quantity,
                'avg_unit_price' => $item->avg_unit_price,
                'total_cost' => $item->total_cost
            ];
        })->toArray();

        // Preparação dos dados para o gráfico de pizza
        $labels = $purchaseCosts->pluck('product.name')->toArray();
        $costs = $purchaseCosts->pluck('total_cost')->toArray();
        
        // Gerar 10 cores diferentes para o gráfico
        $backgroundColors = [
            '#4299e1', '#48bb78', '#ed8936', '#ecc94b', '#9f7aea', 
            '#ed64a6', '#38b2ac', '#667eea', '#f56565', '#a0aec0'
        ];
        
        $this->chartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $costs,
                    'backgroundColor' => array_slice($backgroundColors, 0, count($costs)),
                    'borderWidth' => 1
                ]
            ]
        ];
        
        $this->chartOptions = [
            'type' => 'pie',
            'options' => [
                'responsive' => true,
                'plugins' => [
                    'legend' => [
                        'position' => 'bottom',
                    ],
                    'title' => [
                        'display' => true,
                        'text' => __('messages.purchase_cost_distribution')
                    ]
                ]
            ]
        ];
    }

    private function generateCostComparisonReport()
    {
        $this->reportTitle = __('messages.cost_comparison_report');
        $this->reportDescription = __('messages.cost_comparison_report_description', [
            'start' => Carbon::parse($this->dateRange['start'])->format('d/m/Y'),
            'end' => Carbon::parse($this->dateRange['end'])->format('d/m/Y')
        ]);

        // Períodos para comparação
        $currentPeriodStart = Carbon::parse($this->dateRange['start']);
        $currentPeriodEnd = Carbon::parse($this->dateRange['end']);
        $periodLength = $currentPeriodStart->diffInDays($currentPeriodEnd);
        
        // Período anterior de mesmo tamanho
        $previousPeriodEnd = $currentPeriodStart->copy()->subDay();
        $previousPeriodStart = $previousPeriodEnd->copy()->subDays($periodLength);

        // Dados de produção - período atual
        $currentProductionCosts = ProductionOrder::whereBetween('planned_start_date', [$currentPeriodStart, $currentPeriodEnd])
            ->where('status', '!=', 'cancelled')
            ->select(DB::raw('SUM(unit_cost * produced_quantity) as total_cost'))
            ->first()
            ->total_cost ?? 0;
            
        // Dados de produção - período anterior
        $previousProductionCosts = ProductionOrder::whereBetween('planned_start_date', [$previousPeriodStart, $previousPeriodEnd])
            ->where('status', '!=', 'cancelled')
            ->select(DB::raw('SUM(unit_cost * produced_quantity) as total_cost'))
            ->first()
            ->total_cost ?? 0;
            
        // Dados de compras - período atual
        $currentPurchaseCosts = PurchasePlan::whereBetween('required_date', [$currentPeriodStart, $currentPeriodEnd])
            ->whereIn('status', ['approved', 'ordered'])
            ->select(DB::raw('SUM(unit_price * required_quantity) as total_cost'))
            ->first()
            ->total_cost ?? 0;
            
        // Dados de compras - período anterior
        $previousPurchaseCosts = PurchasePlan::whereBetween('required_date', [$previousPeriodStart, $previousPeriodEnd])
            ->whereIn('status', ['approved', 'ordered'])
            ->select(DB::raw('SUM(unit_price * required_quantity) as total_cost'))
            ->first()
            ->total_cost ?? 0;
            
        // Calcular variações
        $productionVariation = $previousProductionCosts > 0 ? 
            round((($currentProductionCosts - $previousProductionCosts) / $previousProductionCosts) * 100, 2) : 0;
            
        $purchaseVariation = $previousPurchaseCosts > 0 ? 
            round((($currentPurchaseCosts - $previousPurchaseCosts) / $previousPurchaseCosts) * 100, 2) : 0;
            
        // Total custos
        $currentTotalCosts = $currentProductionCosts + $currentPurchaseCosts;
        $previousTotalCosts = $previousProductionCosts + $previousPurchaseCosts;
        
        $totalVariation = $previousTotalCosts > 0 ? 
            round((($currentTotalCosts - $previousTotalCosts) / $previousTotalCosts) * 100, 2) : 0;
            
        // Preparação dos dados do relatório
        $this->reportData = [
            'current_period' => [
                'start' => $currentPeriodStart->format('d/m/Y'),
                'end' => $currentPeriodEnd->format('d/m/Y'),
                'production_costs' => $currentProductionCosts,
                'purchase_costs' => $currentPurchaseCosts,
                'total_costs' => $currentTotalCosts
            ],
            'previous_period' => [
                'start' => $previousPeriodStart->format('d/m/Y'),
                'end' => $previousPeriodEnd->format('d/m/Y'),
                'production_costs' => $previousProductionCosts,
                'purchase_costs' => $previousPurchaseCosts,
                'total_costs' => $previousTotalCosts
            ],
            'variations' => [
                'production' => $productionVariation,
                'purchase' => $purchaseVariation,
                'total' => $totalVariation
            ]
        ];

        // Preparação dos dados para o gráfico
        $labels = [__('messages.production_costs'), __('messages.purchase_costs'), __('messages.total_costs')];
        $currentData = [$currentProductionCosts, $currentPurchaseCosts, $currentTotalCosts];
        $previousData = [$previousProductionCosts, $previousPurchaseCosts, $previousTotalCosts];
        
        $this->chartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => __('messages.current_period'),
                    'data' => $currentData,
                    'backgroundColor' => '#4299e1',
                    'borderColor' => '#3182ce',
                    'borderWidth' => 1
                ],
                [
                    'label' => __('messages.previous_period'),
                    'data' => $previousData,
                    'backgroundColor' => '#a0aec0',
                    'borderColor' => '#718096',
                    'borderWidth' => 1
                ]
            ]
        ];
        
        $this->chartOptions = [
            'type' => 'bar',
            'options' => [
                'responsive' => true,
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'title' => [
                            'display' => true,
                            'text' => __('messages.cost_in_currency')
                        ]
                    ]
                ]
            ]
        ];
    }

    private function generateProductProfitabilityReport()
    {
        $this->reportTitle = __('messages.product_profitability_report');
        $this->reportDescription = __('messages.product_profitability_report_description', [
            'start' => Carbon::parse($this->dateRange['start'])->format('d/m/Y'),
            'end' => Carbon::parse($this->dateRange['end'])->format('d/m/Y')
        ]);

        // Simulação de dados para relatório de rentabilidade de produtos
        // Em um cenário real, esses dados viriam de tabelas de vendas, custos, etc.
        
        $query = ProductionOrder::whereBetween('planned_start_date', [$this->dateRange['start'], $this->dateRange['end']])
            ->where('status', '=', 'completed')
            ->with('product');
            
        if ($this->productFilter) {
            $query->where('product_id', $this->productFilter);
        }
        
        $completedOrders = $query->get();
        
        // Preparação dos dados do relatório com valores simulados de vendas
        $this->reportData = $completedOrders->map(function ($order) {
            // Simulação de preço de venda (em um sistema real, viria do sistema de vendas)
            $salesPrice = $order->unit_cost * (1 + (rand(15, 45) / 100)); // markup entre 15% e 45%
            $totalSales = $order->produced_quantity * $salesPrice;
            $totalCost = $order->produced_quantity * $order->unit_cost;
            $profit = $totalSales - $totalCost;
            $profitMargin = $totalSales > 0 ? ($profit / $totalSales) * 100 : 0;
            
            return [
                'product_name' => $order->product->name,
                'product_sku' => $order->product->sku,
                'order_number' => $order->order_number,
                'quantity' => $order->produced_quantity,
                'unit_cost' => $order->unit_cost,
                'total_cost' => $totalCost,
                'unit_price' => $salesPrice,
                'total_sales' => $totalSales,
                'profit' => $profit,
                'profit_margin' => round($profitMargin, 2)
            ];
        })->toArray();

        // Agrupar por produto para o gráfico
        $productSummary = collect($this->reportData)
            ->groupBy('product_name')
            ->map(function ($group) {
                return [
                    'total_sales' => $group->sum('total_sales'),
                    'total_cost' => $group->sum('total_cost'),
                    'profit' => $group->sum('profit'),
                    'profit_margin' => $group->sum('total_sales') > 0 ? 
                        ($group->sum('profit') / $group->sum('total_sales')) * 100 : 0
                ];
            });
            
        // Preparação dos dados para o gráfico
        $labels = $productSummary->keys()->toArray();
        $salesData = $productSummary->pluck('total_sales')->toArray();
        $costData = $productSummary->pluck('total_cost')->toArray();
        $profitData = $productSummary->pluck('profit')->toArray();
        
        $this->chartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'type' => 'bar',
                    'label' => __('messages.sales'),
                    'data' => $salesData,
                    'backgroundColor' => '#4299e1',
                    'borderColor' => '#3182ce',
                    'borderWidth' => 1
                ],
                [
                    'type' => 'bar',
                    'label' => __('messages.costs'),
                    'data' => $costData,
                    'backgroundColor' => '#f56565',
                    'borderColor' => '#e53e3e',
                    'borderWidth' => 1
                ],
                [
                    'type' => 'line',
                    'label' => __('messages.profit'),
                    'data' => $profitData,
                    'backgroundColor' => 'transparent',
                    'borderColor' => '#48bb78',
                    'borderWidth' => 2,
                    'tension' => 0.1
                ]
            ]
        ];
        
        $this->chartOptions = [
            'type' => 'bar',
            'options' => [
                'responsive' => true,
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'title' => [
                            'display' => true,
                            'text' => __('messages.amount_in_currency')
                        ]
                    ]
                ]
            ]
        ];
    }

    public function closeReportModal()
    {
        $this->showReportModal = false;
        $this->resetExcept(['search', 'perPage', 'sortField', 'sortDirection', 'timeframe', 'reportType', 'dateRange', 'productFilter']);
    }

    public function exportReport($format = 'csv')
    {
        // Implementação da exportação do relatório (PDF, CSV, etc.)
        $this->dispatch('notify', [
            'type' => 'info',
            'title' => __('messages.export_started'),
            'message' => __('messages.generating_report_file')
        ]);
    }

    public function getProductsProperty()
    {
        return Product::where('is_active', true)
            ->when($this->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('sku', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.mrp.financial-reporting', [
            'products' => $this->products
        ]);
    }
}
