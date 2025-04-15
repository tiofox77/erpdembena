<div>
    <div class="container mx-auto px-4 py-6">
        <!-- Cabeçalho Principal -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
            <h1 class="text-2xl font-semibold text-gray-800 flex items-center">
                <i class="fas fa-tachometer-alt text-blue-600 mr-3"></i>
                {{ __('messages.supply_chain_dashboard') }}
            </h1>
            <div class="flex space-x-2">
                <button wire:click="refreshData" 
                    class="inline-flex items-center px-3 py-2 bg-blue-100 border border-transparent rounded-md font-medium text-blue-700 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                    <i class="fas fa-sync-alt mr-2"></i>
                    {{ __('messages.refresh_data') }}
                </button>
                <div class="relative">
                    <select wire:model="timeRange" class="block pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md transition-all duration-200">
                        <option value="today">{{ __('messages.today') }}</option>
                        <option value="week">{{ __('messages.this_week') }}</option>
                        <option value="month">{{ __('messages.this_month') }}</option>
                        <option value="quarter">{{ __('messages.this_quarter') }}</option>
                        <option value="year">{{ __('messages.this_year') }}</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Métricas em Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            @foreach($metrics as $metric)
            <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transform transition-all duration-300 hover:shadow-lg hover:scale-105">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-{{ $metric['color'] ?? 'gray' }}-100 rounded-md p-3">
                            <i class="{{ $metric['icon'] ?? 'fas fa-box' }} text-{{ $metric['color'] ?? 'gray' }}-600 text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    {{ $metric['title'] ?? '---' }}
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-gray-900">
                                        {{ $metric['value'] ?? '---' }}
                                    </div>
                                    
                                    @if(isset($metric['change']) && $metric['change'])
                                    <div class="ml-2 flex items-baseline text-sm font-semibold {{ $metric['change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        <i class="fas {{ $metric['change'] >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-1"></i>
                                        {{ abs($metric['change']) }}%
                                    </div>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                @if(isset($metric['subtitle']) && $metric['subtitle'])
                <div class="bg-gray-50 px-4 py-3">
                    <div class="text-sm text-gray-500">
                        {{ $metric['subtitle'] }}
                    </div>
                </div>
                @endif
            </div>
            @endforeach
        </div>
        
        <!-- Seção de Gráficos e Tendências -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Gráfico de Ordens de Compra por Status -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 hover:shadow-lg">
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-700 flex items-center">
                        <i class="fas fa-chart-pie text-blue-600 mr-2"></i>
                        {{ __('messages.purchase_orders_by_status') }}
                    </h2>
                </div>
                <div class="p-4">
                    <div id="purchaseOrderStatusChart" class="h-72"></div>
                </div>
            </div>
            
            <!-- Gráfico de Tendência de Movimentação de Estoque -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 hover:shadow-lg">
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-700 flex items-center">
                        <i class="fas fa-chart-line text-blue-600 mr-2"></i>
                        {{ __('messages.inventory_movement_trend') }}
                    </h2>
                </div>
                <div class="p-4">
                    <div id="inventoryTrendChart" class="h-72"></div>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Low Stock Items -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 hover:shadow-lg">
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-700 flex items-center">
                        <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                        {{ __('messages.low_stock_alerts') }}
                    </h2>
                </div>
                <div class="p-4">
                    @if(count($lowStockItems) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.product') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.sku') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.current_stock') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.reorder_point') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.status') }}</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($lowStockItems as $item)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if(isset($item['image']) && $item['image'])
                                            <div class="flex-shrink-0 h-10 w-10 mr-3">
                                                <img class="h-10 w-10 rounded-full object-cover" src="{{ Storage::url($item['image']) }}" alt="{{ $item['name'] ?? 'Product' }}">
                                            </div>
                                            @else
                                            <div class="flex-shrink-0 h-10 w-10 mr-3 bg-gray-200 rounded-full flex items-center justify-center">
                                                <i class="fas fa-box text-gray-500"></i>
                                            </div>
                                            @endif
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $item['name'] ?? '---' }}</div>
                                                <div class="text-sm text-gray-500">{{ $item['category'] ?? '---' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item['sku'] ?? '---' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="{{ $item['current_stock'] <= 0 ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                                            {{ $item['current_stock'] ?? '---' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item['reorder_point'] ?? '---' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $item['status'] == 'critical' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $item['status'] == 'critical' ? __('messages.critical') : __('messages.warning') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                       
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="flex flex-col items-center justify-center py-6 space-y-2">
                        <div class="flex-shrink-0 bg-green-100 p-3 rounded-full">
                            <i class="fas fa-check text-green-500 text-xl"></i>
                        </div>
                        <p class="text-gray-500 text-center">{{ __('messages.no_low_stock_items') }}</p>
                        <p class="text-gray-400 text-sm text-center">{{ __('messages.all_stock_levels_normal') }}</p>
                    </div>
                    @endif
                    
                    <div class="mt-4">
                        <a href="{{ route('supply-chain.inventory') }}" class="text-blue-600 hover:text-blue-900 flex items-center justify-end">
                            <span>{{ __('messages.view_all_inventory') }}</span>
                            <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Pending Orders -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 hover:shadow-lg">
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-700 flex items-center">
                        <i class="fas fa-clock text-blue-600 mr-2"></i>
                        {{ __('messages.pending_purchase_orders') }}
                    </h2>
                </div>
                <div class="p-4">
                    @if(count($pendingOrders) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.order_number') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.supplier') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.expected_date') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.status') }}</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.total') }}</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($pendingOrders as $order)
                                <tr class="{{ $order['is_overdue'] ? 'bg-red-50' : ($order['approaching_delivery'] ? 'bg-yellow-50' : 'hover:bg-gray-50') }} transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $order['is_overdue'] ? 'text-red-600' : 'text-gray-900' }}">
                                        {{ $order['order_number'] ?? '---' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order['supplier'] ?? '---' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($order['is_overdue'])
                                            <span class="text-red-600 font-semibold">{{ $order['expected_date'] ?? '---' }}</span>
                                            <span class="block text-xs text-red-600">{{ $order['days_overdue'] ?? '---' }} {{ __('messages.days_overdue') }}</span>
                                        @elseif($order['approaching_delivery'])
                                            <span class="text-yellow-600">{{ $order['expected_date'] ?? '---' }}</span>
                                            <span class="block text-xs text-yellow-600">{{ $order['days_remaining'] ?? '---' }} {{ __('messages.days_remaining') }}</span>
                                        @else
                                            {{ $order['expected_date'] ?? '---' }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $order['status_color'] ?? 'bg-gray-100 text-gray-500' }}">
                                            {{ __('messages.'.$order['status'] ?? 'unknown_status') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">{{ number_format($order['total'] ?? 0, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('supply-chain.purchase-orders', ['id' => $order['id'] ?? 0]) }}" class="text-blue-600 hover:text-blue-900 transition-colors duration-150 transform hover:scale-110 mr-2">
                                            <i class="fas fa-eye"></i>
                                            <span class="sr-only">{{ __('messages.view') }}</span>
                                        </a>
                                        <a href="{{ route('supply-chain.goods-receipts', ['purchase_order_id' => $order['id'] ?? 0]) }}" class="text-green-600 hover:text-green-900 transition-colors duration-150 transform hover:scale-110">
                                            <i class="fas fa-clipboard-check"></i>
                                            <span class="sr-only">{{ __('messages.receive') }}</span>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4 flex flex-wrap gap-3 justify-end text-xs">
                        <div class="flex items-center">
                            <span class="w-3 h-3 inline-block rounded-full bg-red-100 mr-1"></span>
                            <span class="text-gray-600">{{ __('messages.overdue_orders_legend') }}</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-3 h-3 inline-block rounded-full bg-yellow-100 mr-1"></span>
                            <span class="text-gray-600">{{ __('messages.approaching_delivery_legend') }}</span>
                        </div>
                    </div>
                    @else
                    <div class="flex flex-col items-center justify-center py-6 space-y-2">
                        <div class="flex-shrink-0 bg-green-100 p-3 rounded-full">
                            <i class="fas fa-check text-green-500 text-xl"></i>
                        </div>
                        <p class="text-gray-500 text-center">{{ __('messages.no_pending_purchase_orders') }}</p>
                        <p class="text-gray-400 text-sm text-center">{{ __('messages.all_orders_completed') }}</p>
                    </div>
                    @endif
                    
                    <div class="mt-4">
                        <a href="{{ route('supply-chain.purchase-orders') }}" class="text-blue-600 hover:text-blue-900 flex items-center justify-end">
                            <span>{{ __('messages.view_all_purchase_orders') }}</span>
                            <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Transactions -->
        <div class="mt-8 bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 hover:shadow-lg">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-700 flex items-center">
                    <i class="fas fa-exchange-alt text-green-500 mr-2"></i>
                    {{ __('messages.recent_inventory_transactions') }}
                </h2>
            </div>
            <div class="p-4">
                @if(count($recentTransactions) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.date') }}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.reference') }}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.product') }}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.type') }}</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.quantity') }}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.source') }}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.destination') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentTransactions as $transaction)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $transaction['date'] ?? '' }}
                                    @if(isset($transaction['time']) && $transaction['time'])
                                    <span class="block text-xs text-gray-400">{{ $transaction['time'] }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if(isset($transaction['reference_url']) && $transaction['reference_url'])
                                    <a href="{{ $transaction['reference_url'] }}" class="text-blue-600 hover:text-blue-900">
                                        {{ $transaction['reference'] ?? $transaction['number'] ?? '---' }}
                                    </a>
                                    @else
                                    {{ $transaction['reference'] ?? $transaction['number'] ?? '---' }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if(isset($transaction['product_image']) && $transaction['product_image'])
                                        <div class="flex-shrink-0 h-8 w-8 mr-3">
                                            <img class="h-8 w-8 rounded-full object-cover" src="{{ Storage::url($transaction['product_image']) }}" alt="{{ $transaction['product'] ?? 'Product' }}">
                                        </div>
                                        @else
                                        <div class="flex-shrink-0 h-8 w-8 mr-3 bg-gray-200 rounded-full flex items-center justify-center">
                                            <i class="fas fa-box text-gray-500"></i>
                                        </div>
                                        @endif
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $transaction['product'] ?? '---' }}</div>
                                            <div class="text-xs text-gray-500">{{ $transaction['sku'] ?? '---' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $transaction['type_class'] ?? 'bg-gray-100 text-gray-500' }}">
                                        {{ __('messages.'.$transaction['type'] ?? 'unknown_transaction') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right 
                                    {{ $transaction['quantity'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $transaction['quantity'] > 0 ? '+' : '' }}{{ $transaction['quantity'] ?? 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $transaction['source'] ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $transaction['destination'] ?? 'N/A' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="flex flex-col items-center justify-center py-6 space-y-2">
                    <div class="flex-shrink-0 bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-info text-blue-500 text-xl"></i>
                    </div>
                    <p class="text-gray-500 text-center">{{ __('messages.no_recent_transactions') }}</p>
                </div>
                @endif
                
                <div class="mt-4">
                    <a href="{{ route('supply-chain.inventory') }}" class="text-blue-600 hover:text-blue-900 flex items-center justify-end">
                        <span>{{ __('messages.view_all_transactions') }}</span>
                        <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Indicadores de Desempenho -->
        <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Produto mais movimentado -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 hover:shadow-lg">
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-700 flex items-center">
                        <i class="fas fa-medal text-amber-500 mr-2"></i>
                        {{ __('messages.top_moving_products') }}
                    </h2>
                </div>
                <div class="p-4">
                    <div id="topProductsChart" class="h-60"></div>
                </div>
            </div>
            
            <!-- Fornecedores por Volume -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 hover:shadow-lg">
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-700 flex items-center">
                        <i class="fas fa-building text-indigo-500 mr-2"></i>
                        {{ __('messages.top_suppliers_by_volume') }}
                    </h2>
                </div>
                <div class="p-4">
                    <div id="suppliersChart" class="h-60"></div>
                </div>
            </div>
            
            <!-- Previsão de Estoque -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 hover:shadow-lg">
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-700 flex items-center">
                        <i class="fas fa-chart-bar text-purple-500 mr-2"></i>
                        {{ __('messages.stock_forecast') }}
                    </h2>
                </div>
                <div class="p-4">
                    <div id="stockForecastChart" class="h-60"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts para os gráficos -->
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('livewire:initialized', function() {
        // Verificar se os dados dos gráficos estão disponíveis
        if (typeof @this.chartData === 'undefined') {
            console.error('Dados dos gráficos não disponíveis');
            return;
        }
        
        // Acessar dados de forma segura
        var chartData = @this.chartData || {
            purchaseOrderStatus: {series: [], labels: [], colors: []},
            inventoryTrend: {series: [], dates: []},
            topProducts: {data: [], labels: [], colors: []},
            suppliers: {series: [], labels: []},
            stockForecast: {series: [], dates: []}
        };
        
        // Gráfico de Ordens de Compra por Status
        var orderStatusOptions = {
            series: chartData.purchaseOrderStatus.series,
            chart: {
                type: 'donut',
                height: 290,
                animations: {
                    enabled: true,
                    speed: 300
                },
            },
            labels: chartData.purchaseOrderStatus.labels,
            colors: chartData.purchaseOrderStatus.colors,
            plotOptions: {
                pie: {
                    donut: {
                        size: '60%',
                        labels: {
                            show: true,
                            name: {
                                show: true,
                            },
                            value: {
                                show: true,
                                formatter: function(val) {
                                    return val;
                                }
                            },
                            total: {
                                show: true,
                                label: '{{ __("messages.total") }}',
                                formatter: function(w) {
                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                }
                            }
                        }
                    }
                }
            },
            legend: {
                position: 'bottom'
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        height: 250
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }],
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + ' {{ __("messages.orders") }}';
                    }
                }
            }
        };
        
        // Verificar se o elemento existe antes de renderizar
        var purchaseOrderStatusChartElement = document.querySelector("#purchaseOrderStatusChart");
        if (purchaseOrderStatusChartElement) {
            var orderStatusChart = new ApexCharts(purchaseOrderStatusChartElement, orderStatusOptions);
            orderStatusChart.render();
        }
        
        // Gráfico de Tendência de Movimentação de Estoque
        var inventoryTrendOptions = {
            series: chartData.inventoryTrend.series,
            chart: {
                type: 'area',
                height: 290,
                toolbar: {
                    show: false
                },
                animations: {
                    enabled: true,
                    speed: 300
                },
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            xaxis: {
                type: 'datetime',
                categories: chartData.inventoryTrend.dates
            },
            tooltip: {
                x: {
                    format: 'dd MMM yyyy'
                }
            },
            colors: ['#2563EB', '#16A34A', '#DC2626'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.7,
                    opacityTo: 0.2,
                    stops: [0, 90, 100]
                }
            },
            legend: {
                position: 'top'
            }
        };
        
        // Verificar se o elemento existe antes de renderizar
        var inventoryTrendChartElement = document.querySelector("#inventoryTrendChart");
        if (inventoryTrendChartElement) {
            var inventoryTrendChart = new ApexCharts(inventoryTrendChartElement, inventoryTrendOptions);
            inventoryTrendChart.render();
        }
        
        // Gráfico de Produtos mais Movimentados
        var topProductsOptions = {
            series: [{
                data: chartData.topProducts.data
            }],
            chart: {
                type: 'bar',
                height: 240,
                toolbar: {
                    show: false
                },
                animations: {
                    enabled: true,
                    speed: 300
                },
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    distributed: true,
                    barHeight: '70%',
                }
            },
            dataLabels: {
                enabled: false
            },
            xaxis: {
                categories: chartData.topProducts.labels,
            },
            yaxis: {
                labels: {
                    show: false
                }
            },
            colors: chartData.topProducts.colors,
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + ' {{ __("messages.units") }}';
                    }
                }
            }
        };
        
        // Verificar se o elemento existe antes de renderizar
        var topProductsChartElement = document.querySelector("#topProductsChart");
        if (topProductsChartElement) {
            var topProductsChart = new ApexCharts(topProductsChartElement, topProductsOptions);
            topProductsChart.render();
        }
        
        // Gráfico de Fornecedores por Volume
        var suppliersOptions = {
            series: chartData.suppliers.series,
            chart: {
                type: 'pie',
                height: 240,
                toolbar: {
                    show: false
                },
                animations: {
                    enabled: true,
                    speed: 300
                },
            },
            labels: chartData.suppliers.labels,
            colors: ['#3B82F6', '#6366F1', '#8B5CF6', '#EC4899', '#F97316', '#64748B'],
            legend: {
                position: 'bottom',
                fontSize: '12px'
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return '{{ __("messages.currency_symbol") }}' + val.toFixed(2);
                    }
                }
            }
        };
        
        // Verificar se o elemento existe antes de renderizar
        var suppliersChartElement = document.querySelector("#suppliersChart");
        if (suppliersChartElement) {
            var suppliersChart = new ApexCharts(suppliersChartElement, suppliersOptions);
            suppliersChart.render();
        }
        
        // Gráfico de Previsão de Estoque
        var stockForecastOptions = {
            series: chartData.stockForecast.series,
            chart: {
                type: 'line',
                height: 240,
                toolbar: {
                    show: false
                },
                animations: {
                    enabled: true,
                    speed: 300
                },
                dropShadow: {
                    enabled: true,
                    top: 3,
                    left: 2,
                    blur: 4,
                    opacity: 0.1
                }
            },
            colors: ['#4F46E5', '#64748B'],
            dataLabels: {
                enabled: false
            },
            stroke: {
                width: [3, 3],
                curve: 'smooth',
                dashArray: [0, 5]
            },
            grid: {
                borderColor: '#e7e7e7',
                row: {
                    colors: ['#f3f3f3', 'transparent'],
                    opacity: 0.1
                },
            },
            markers: {
                size: 0
            },
            xaxis: {
                categories: chartData.stockForecast.dates,
                labels: {
                    style: {
                        fontSize: '10px'
                    }
                }
            },
            yaxis: {
                labels: {
                    formatter: function(val) {
                        return val.toFixed(0);
                    }
                }
            },
            tooltip: {
                shared: true,
                intersect: false,
            },
            legend: {
                position: 'top',
                fontSize: '12px'
            }
        };

        // Verificar se o elemento existe antes de renderizar
        var stockForecastChartElement = document.querySelector("#stockForecastChart");
        if (stockForecastChartElement) {
            var stockForecastChart = new ApexCharts(stockForecastChartElement, stockForecastOptions);
            stockForecastChart.render();
        }
        
        // Detectar quando componente é atualizado
        Livewire.on('chartDataUpdated', data => {
            console.log('Dados dos gráficos atualizados:', data);
            
            // Verificar se os dados recebidos são válidos
            if (!data) return;
            
            // Atualizar dados dos gráficos quando houver mudanças
            if (typeof orderStatusChart !== 'undefined' && data.purchaseOrderStatus) {
                orderStatusChart.updateOptions({
                    series: data.purchaseOrderStatus.series,
                    labels: data.purchaseOrderStatus.labels,
                    colors: data.purchaseOrderStatus.colors
                });
            }
            
            if (typeof inventoryTrendChart !== 'undefined' && data.inventoryTrend) {
                inventoryTrendChart.updateOptions({
                    series: data.inventoryTrend.series,
                    xaxis: {
                        categories: data.inventoryTrend.dates
                    }
                });
            }
            
            if (typeof topProductsChart !== 'undefined' && data.topProducts) {
                topProductsChart.updateOptions({
                    series: [{
                        data: data.topProducts.data
                    }],
                    xaxis: {
                        categories: data.topProducts.labels
                    },
                    colors: data.topProducts.colors
                });
            }
            
            if (typeof suppliersChart !== 'undefined' && data.suppliers) {
                suppliersChart.updateOptions({
                    series: data.suppliers.series,
                    labels: data.suppliers.labels
                });
            }
            
            if (typeof stockForecastChart !== 'undefined' && data.stockForecast) {
                stockForecastChart.updateOptions({
                    series: data.stockForecast.series,
                    xaxis: {
                        categories: data.stockForecast.dates
                    }
                });
            }
        });
    });
</script>
@endpush
