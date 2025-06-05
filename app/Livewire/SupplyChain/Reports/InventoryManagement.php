<?php

namespace App\Livewire\SupplyChain\Reports;

use Livewire\Component;
use App\Models\SupplyChain\InventoryItem;
use App\Models\SupplyChain\InventoryLocation;
use App\Models\SupplyChain\Product;
use App\Models\SupplyChain\ProductCategory;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InventoryManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'sc_products.name';
    public $sortDirection = 'asc';
    public $filters = [
        'location' => '',
        'category' => '',
        'product_type' => '',  // Tipos de produtos: raw_material, finished_product, service, etc
        'stock_status' => '',  // 'low', 'out', 'normal', 'all'
        'date_range' => '',    // Para análise de movimento
        'warehouse_type' => '', // 'all', 'raw_material', 'finished_product'
        'expiry_date' => '',    // Para produtos com data de validade
    ];
    
    public $period = 'monthly';  // 'weekly', 'monthly', 'quarterly', 'yearly'
    public $chartType = 'inventory_value';  // 'inventory_value', 'turnover', 'abc_analysis'
    
    public $dateStart;
    public $dateEnd;
    
    // Dados para gráficos e análises
    public $chartData = [];
    
    // Modos de visualização
    public $viewMode = 'table';  // 'table', 'chart', 'summary'
    
    public function mount()
    {
        // Definir datas padrão (útimo mês)
        $this->dateEnd = Carbon::now()->format('Y-m-d');
        $this->dateStart = Carbon::now()->subMonth()->format('Y-m-d');
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
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingFilters()
    {
        $this->resetPage();
        $this->loadChartData();
    }
    
    public function updatedPeriod()
    {
        $this->loadChartData();
    }
    
    public function updatedChartType()
    {
        $this->loadChartData();
        $this->dispatch('chartDataUpdated');
    }
    
    /**
     * Change the chart type and load new data
     *
     * @param string $type The chart type to switch to
     * @return void
     */
    public function changeChartType($type)
    {
        // Log para debug
        Log::info('Mudando tipo de gráfico para: ' . $type);
        
        // Atualiza o tipo de gráfico
        $this->chartType = $type;
        
        // Carrega os dados para o novo tipo
        $this->loadChartData();
        
        // Notifica que os dados foram atualizados
        $this->dispatch('chartDataUpdated');
    }
    
    public function updatedDateStart()
    {
        $this->loadChartData();
    }
    
    public function updatedDateEnd()
    {
        $this->loadChartData();
    }
    
    public function updatedViewMode()
    {
        if ($this->viewMode === 'chart' || $this->viewMode === 'summary') {
            $this->loadChartData();
            // Adicione um log para debug
            logger()->info('ViewMode atualizado: ' . $this->viewMode);
            logger()->info('ChartType atual: ' . $this->chartType);
            logger()->info('ChartData: ' . json_encode($this->chartData));
        }
    }
    
    public function getChartData()
    {
        // Método para ser chamado via wire:click ou AJAX
        return $this->chartData;
    }
    
    /**
     * Load chart data based on current chart type
     *
     * @return void
     */
    public function loadChartData()
    {
        // Emitir evento antes de carregar os dados
        $this->dispatch('chartLoading');
        
        // Carregar dados com base no tipo de gráfico
        switch($this->chartType) {
            case 'inventory_value':
                $this->loadInventoryValueData();
                break;
            case 'turnover':
                $this->loadTurnoverData();
                break;
            case 'abc_analysis':
                $this->loadABCAnalysisData();
                break;
            default:
                $this->loadInventoryValueData();
        }
        
        // Emitir evento informando que os dados foram atualizados, enviando os dados no evento
        $this->dispatch('chartDataUpdated', [
            'chartData' => $this->chartData,
            'chartType' => $this->chartType
        ]);
        
        // Tentar registar os dados e tipos para debug
        try {
            Log::info('InventoryManagement: Chart data loaded', [
                'type' => $this->chartType, 
                'data' => $this->chartData
            ]);
        } catch (\Exception $e) {
            Log::error('Error logging chart data: ' . $e->getMessage());
        }
    }
    
    protected function loadInventoryValueData()
    {
        try {
            // Obter dados de valor de inventário ao longo do tempo
            $query = InventoryItem::select(
                DB::raw('DATE(sc_inventory_items.created_at) as date'),
                DB::raw('SUM(sc_inventory_items.quantity_on_hand * sc_products.unit_price) as total_value')
            )
            ->join('sc_products', 'sc_inventory_items.product_id', '=', 'sc_products.id')
            ->whereBetween('sc_inventory_items.created_at', [$this->dateStart.' 00:00:00', $this->dateEnd.' 23:59:59'])
            ->groupBy(DB::raw('DATE(sc_inventory_items.created_at)'))
            ->orderBy('date');
            
            // Aplicar filtros adicionais
            if (!empty($this->filters['location'])) {
                $query->where('sc_inventory_items.location_id', $this->filters['location']);
            }
            
            if (!empty($this->filters['category'])) {
                $query->join('sc_product_categories', 'sc_products.category_id', '=', 'sc_product_categories.id')
                      ->where('sc_products.category_id', $this->filters['category']);
            }
            
            $data = $query->get();
            
            $labels = [];
            $values = [];
            
            foreach ($data as $item) {
                $labels[] = Carbon::parse($item->date)->format('d/m/Y');
                $values[] = $item->total_value;
            }
            
            $this->chartData = [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Valor do Inventário',
                        'data' => $values,
                        'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                        'borderColor' => 'rgba(75, 192, 192, 1)',
                        'fill' => true
                    ]
                ],
                'title' => 'Valor do Inventário ao Longo do Tempo',
                'type' => 'line'
            ];
        } catch (\Exception $e) {
            // Em caso de erro, carregar dados de fallback para o gráfico
            $this->chartData = [
                'labels' => ['Sem dados suficientes'],
                'datasets' => [
                    [
                        'label' => 'Valor do Inventário',
                        'data' => [0],
                        'backgroundColor' => 'rgba(200, 200, 200, 0.2)',
                        'borderColor' => 'rgba(200, 200, 200, 1)',
                        'fill' => true
                    ]
                ],
                'title' => 'Dados insuficientes para gerar o gráfico',
                'type' => 'line'
            ];
        }
    }
    
    protected function loadTurnoverData()
    {
        try {
            // Obter dados de rotatividade do inventário
            $query = DB::table('sc_inventory_items')
                    ->select(
                        'sc_products.name as product_name',
                        DB::raw('COUNT(DISTINCT sc_inventory_items.id) as movement_count'),
                        DB::raw('SUM(CASE WHEN sc_inventory_items.quantity_on_hand > 0 THEN sc_inventory_items.quantity_on_hand ELSE 0 END) as total_in'),
                        DB::raw('SUM(CASE WHEN sc_inventory_items.quantity_on_hand < 0 THEN ABS(sc_inventory_items.quantity_on_hand) ELSE 0 END) as total_out')
                    )
                    ->join('sc_products', 'sc_inventory_items.product_id', '=', 'sc_products.id')
                    ->whereBetween('sc_inventory_items.created_at', [$this->dateStart.' 00:00:00', $this->dateEnd.' 23:59:59'])
                    ->groupBy('sc_products.name')
                    ->orderBy('movement_count', 'desc')
                    ->limit(10);
                    
            // Aplicar filtros adicionais
            if (!empty($this->filters['location'])) {
                $query->where('sc_inventory_items.location_id', $this->filters['location']);
            }
            
            if (!empty($this->filters['category'])) {
                $query->join('sc_product_categories', 'sc_products.category_id', '=', 'sc_product_categories.id')
                      ->where('sc_products.category_id', $this->filters['category']);
            }
            
            $data = $query->get();
            
            $labels = [];
            $inValues = [];
            $outValues = [];
            
            foreach ($data as $item) {
                $labels[] = $item->product_name;
                $inValues[] = $item->total_in;
                $outValues[] = $item->total_out;
            }
            
            $this->chartData = [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Entrada',
                        'data' => $inValues,
                        'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                        'borderColor' => 'rgba(75, 192, 192, 1)',
                    ],
                    [
                        'label' => 'Saída',
                        'data' => $outValues,
                        'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                        'borderColor' => 'rgba(255, 99, 132, 1)',
                    ]
                ],
                'title' => 'Top 10 Produtos por Movimentação',
                'type' => 'bar'
            ];
        } catch (\Exception $e) {
            // Tratar exceções e fornecer dados de fallback
            $this->chartData = [
                'labels' => ['Sem dados suficientes'],
                'datasets' => [
                    [
                        'label' => 'Entrada',
                        'data' => [0],
                        'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                        'borderColor' => 'rgba(75, 192, 192, 1)',
                    ],
                    [
                        'label' => 'Saída',
                        'data' => [0],
                        'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                        'borderColor' => 'rgba(255, 99, 132, 1)',
                    ]
                ],
                'title' => 'Dados insuficientes para gerar o gráfico',
                'type' => 'bar'
            ];
        }
    }
    
    /**
     * Carrega os dados para análise ABC de inventário
     *
     * @return void
     */
    protected function loadABCAnalysisData()
    {
        try {
            // Análise ABC baseada no valor do inventário
            $query = InventoryItem::select(
                'sc_products.name as product_name',
                DB::raw('SUM(sc_inventory_items.quantity_on_hand) as total_quantity'),
                'sc_products.unit_price',
                DB::raw('SUM(sc_inventory_items.quantity_on_hand * sc_products.unit_price) as total_value')
            )
            ->join('sc_products', 'sc_inventory_items.product_id', '=', 'sc_products.id')
            ->groupBy('sc_products.name', 'sc_products.unit_price')
            ->orderBy('total_value', 'desc');
            
            // Aplicar filtros adicionais
            if (!empty($this->filters['location'])) {
                $query->where('sc_inventory_items.location_id', $this->filters['location']);
            }
            
            if (!empty($this->filters['category'])) {
                $query->join('sc_product_categories', 'sc_products.category_id', '=', 'sc_product_categories.id')
                      ->where('sc_products.category_id', $this->filters['category']);
            }
            
            $products = $query->get();
            
            // Calcular valor total do inventário
            $totalValue = $products->sum('total_value');
            
            $accumulatedValue = 0;
            $classA = [];
            $classB = [];
            $classC = [];
            
            // Verificar se há produtos e se o valor total é maior que zero
            if ($products->count() > 0 && $totalValue > 0) {
                foreach ($products as $product) {
                    $percentage = ($product->total_value / $totalValue) * 100;
                    $accumulatedValue += $percentage;
                    
                    $item = [
                        'name' => $product->product_name,
                        'quantity' => $product->total_quantity,
                        'value' => $product->total_value,
                        'percentage' => $percentage,
                        'accumulated' => $accumulatedValue
                    ];
                    
                    if ($accumulatedValue <= 80) {
                        $classA[] = $item;
                    } elseif ($accumulatedValue <= 95) {
                        $classB[] = $item;
                    } else {
                        $classC[] = $item;
                    }
                }
            } else {
                // Se não houver produtos ou valor total for zero, criar dados de exemplo para evitar erros
                $classA = [['name' => 'Sem dados', 'quantity' => 0, 'value' => 0, 'percentage' => 0, 'accumulated' => 0]];
                $classB = [];
                $classC = [];
            }
            
            // Dados para o gráfico de pizza de classes ABC
            $this->chartData = [
                'labels' => ['Classe A (80% do valor)', 'Classe B (15% do valor)', 'Classe C (5% do valor)'],
                'datasets' => [
                    [
                        'label' => 'Número de itens',
                        'data' => [
                            count($classA), 
                            count($classB), 
                            count($classC)
                        ],
                        'backgroundColor' => [
                            'rgba(255, 99, 132, 0.6)',
                            'rgba(54, 162, 235, 0.6)',
                            'rgba(255, 206, 86, 0.6)'
                        ],
                        'borderWidth' => 1
                    ]
                ],
                'detailed' => [
                    'classA' => $classA,
                    'classB' => $classB,
                    'classC' => $classC
                ],
                'title' => 'Análise ABC de Inventário',
                'type' => 'pie'
            ];
        } catch (\Exception $e) {
            // Tratar exceções e fornecer dados de fallback
            $this->chartData = [
                'labels' => ['Classe A', 'Classe B', 'Classe C'],
                'datasets' => [
                    [
                        'data' => [0, 0, 0],
                        'backgroundColor' => [
                            'rgba(255, 99, 132, 0.6)',
                            'rgba(54, 162, 235, 0.6)',
                            'rgba(255, 206, 86, 0.6)'
                        ]
                    ]
                ],
                'detailed' => [
                    'classA' => [['name' => 'Sem dados', 'quantity' => 0, 'value' => 0, 'percentage' => 0, 'accumulated' => 0]],
                    'classB' => [],
                    'classC' => []
                ],
                'title' => 'Análise ABC de Inventário (Sem dados)',
                'type' => 'pie'
            ];
        }
    }
    
    public function getInventoryLocationsProperty()
    {
        return InventoryLocation::orderBy('name')->get();
    }
    
    public function getProductTypesProperty()
    {
        // Obter tipos de produto distintos do banco de dados
        return Product::select('product_type')
            ->distinct()
            ->whereNotNull('product_type')
            ->orderBy('product_type')
            ->pluck('product_type');
    }
    
    public function getProductCategoriesProperty()
    {
        return ProductCategory::orderBy('name')->get();
    }
    
    public function getInventoryQuery()
    {
        $query = InventoryItem::select(
                'sc_inventory_items.*', 
                'sc_products.name as product_name',
                'sc_products.sku as product_sku',
                'sc_products.unit_price',
                'sc_products.product_type',
                'sc_products.unit_of_measure',
                'sc_products.min_stock_level',
                'sc_products.reorder_point',
                'sc_products.lead_time_days',
                'sc_inventory_locations.name as location_name',
                'sc_inventory_locations.is_raw_material_warehouse',
                'sc_product_categories.name as category_name',
                DB::raw('(sc_inventory_items.quantity_on_hand * sc_products.unit_price) as item_value'),
                DB::raw('(CASE WHEN sc_inventory_items.quantity_on_hand <= 0 THEN "out_of_stock"
                      WHEN sc_inventory_items.quantity_on_hand <= sc_products.min_stock_level THEN "low_stock"
                      WHEN sc_inventory_items.quantity_on_hand <= sc_products.reorder_point THEN "reorder"
                      ELSE "normal" END) as stock_status')
            )
            ->join('sc_products', 'sc_inventory_items.product_id', '=', 'sc_products.id')
            ->join('sc_inventory_locations', 'sc_inventory_items.location_id', '=', 'sc_inventory_locations.id')
            ->leftJoin('sc_product_categories', 'sc_products.category_id', '=', 'sc_product_categories.id');
        
        // Aplicar pesquisa
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('sc_products.name', 'like', '%'.$this->search.'%')
                  ->orWhere('sc_products.sku', 'like', '%'.$this->search.'%')
                  ->orWhere('sc_inventory_locations.name', 'like', '%'.$this->search.'%')
                  ->orWhere('sc_inventory_items.batch_number', 'like', '%'.$this->search.'%');
            });
        }
        
        // Aplicar filtros
        if (!empty($this->filters['location'])) {
            $query->where('sc_inventory_items.location_id', $this->filters['location']);
        }
        
        if (!empty($this->filters['category'])) {
            $query->where('sc_products.category_id', $this->filters['category']);
        }
        
        // Filtro por tipo de produto
        if (!empty($this->filters['product_type'])) {
            $query->where('sc_products.product_type', $this->filters['product_type']);
        }
        
        // Filtro por tipo de armazém
        if (!empty($this->filters['warehouse_type'])) {
            switch ($this->filters['warehouse_type']) {
                case 'raw_material':
                    $query->where('sc_inventory_locations.is_raw_material_warehouse', true);
                    break;
                case 'finished_product':
                    $query->where('sc_inventory_locations.is_raw_material_warehouse', false);
                    break;
                // 'all' não aplica filtro
            }
        }
        
        // Filtro por data de validade
        if (!empty($this->filters['expiry_date'])) {
            $daysToExpire = (int)$this->filters['expiry_date'];
            $futureDate = Carbon::now()->addDays($daysToExpire)->format('Y-m-d');
            $query->whereNotNull('sc_inventory_items.expiry_date')
                  ->whereDate('sc_inventory_items.expiry_date', '<=', $futureDate);
        }
        
        // Filtro por status de estoque
        if (!empty($this->filters['stock_status'])) {
            switch ($this->filters['stock_status']) {
                case 'low':
                    $query->whereRaw('sc_inventory_items.quantity_on_hand <= sc_products.min_stock_level AND sc_inventory_items.quantity_on_hand > 0');
                    break;
                case 'out':
                    $query->where('sc_inventory_items.quantity_on_hand', '<=', 0);
                    break;
                case 'reorder':
                    $query->whereRaw('sc_inventory_items.quantity_on_hand <= sc_products.reorder_point AND sc_inventory_items.quantity_on_hand > sc_products.min_stock_level');
                    break;
                // 'all' não aplica filtro
            }
        }
        
        return $query;
    }
    
    public function getSummaryStatistics()
    {
        $baseQuery = $this->getInventoryQuery();
        
        $totalValue = $baseQuery->sum(DB::raw('sc_inventory_items.quantity_on_hand * sc_products.unit_price'));
        $totalItems = $baseQuery->count();
        
        // Itens por status de estoque
        $lowStockItems = $this->getInventoryQuery()
            ->whereRaw('sc_inventory_items.quantity_on_hand <= sc_products.min_stock_level AND sc_inventory_items.quantity_on_hand > 0')
            ->count();
        $outOfStockItems = $this->getInventoryQuery()
            ->where('sc_inventory_items.quantity_on_hand', '<=', 0)
            ->count();
        $reorderItems = $this->getInventoryQuery()
            ->whereRaw('sc_inventory_items.quantity_on_hand <= sc_products.reorder_point AND sc_inventory_items.quantity_on_hand > sc_products.min_stock_level')
            ->count();
        $normalItems = $totalItems - $lowStockItems - $outOfStockItems - $reorderItems;
        
        // Valor por tipo de armazém
        $rawMaterialValue = $this->getInventoryQuery()
            ->where('sc_inventory_locations.is_raw_material_warehouse', true)
            ->sum(DB::raw('sc_inventory_items.quantity_on_hand * sc_products.unit_price'));
        $finishedProductValue = $this->getInventoryQuery()
            ->where('sc_inventory_locations.is_raw_material_warehouse', false)
            ->sum(DB::raw('sc_inventory_items.quantity_on_hand * sc_products.unit_price'));
            
        // Próximos a vencer
        $expiryIn30Days = $this->getInventoryQuery()
            ->whereNotNull('sc_inventory_items.expiry_date')
            ->whereDate('sc_inventory_items.expiry_date', '<=', Carbon::now()->addDays(30))
            ->count();
        
        return [
            'total_value' => $totalValue,
            'total_items' => $totalItems,
            'low_stock_items' => $lowStockItems,
            'reorder_items' => $reorderItems,
            'out_of_stock_items' => $outOfStockItems,
            'normal_stock_items' => $normalItems,
            'raw_material_value' => $rawMaterialValue,
            'finished_product_value' => $finishedProductValue,
            'expiring_soon' => $expiryIn30Days,
            'stock_health_percentage' => $totalItems > 0 
                ? (($normalItems) / $totalItems) * 100 
                : 0
        ];
    }
    
    public function render()
    {
        $inventory = $this->getInventoryQuery()
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
        
        $summaryStats = $this->getSummaryStatistics();
        
        return view('livewire.supply-chain.reports.inventory-management', [
            'inventory' => $inventory,
            'inventoryLocations' => $this->inventoryLocations,
            'productCategories' => $this->productCategories,
            'productTypes' => $this->productTypes,
            'summaryStats' => $summaryStats
        ]);
    }
}
