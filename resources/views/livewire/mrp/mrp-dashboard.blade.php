<div>
    <!-- Cabeçalho da Página -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 inline-flex items-center">
                <i class="fas fa-tachometer-alt text-blue-600 mr-2"></i>
                {{ __('messages.mrp_dashboard') }}
            </h1>
            <p class="mt-1 text-sm text-gray-500">{{ __('messages.mrp_dashboard_description') }}</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-2">
            @can('mrp_dashboard.refresh')
                <button type="button" wire:click="refreshData" 
                    class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                    <i class="fas fa-sync-alt mr-2"></i>
                    {{ __('messages.refresh_data') }}
                </button>
            @else
                <button type="button" disabled
                    class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gray-400 cursor-not-allowed opacity-75"
                    title="{{ __('messages.no_permission') }}">
                    <i class="fas fa-ban mr-2"></i>
                    {{ __('messages.refresh_data') }}
                </button>
            @endcan
        </div>
    </div>
    
    <!-- Cards de Visão Geral MRP -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <!-- Card - Ordens de Produção -->
        <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-blue-500">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">{{ __('messages.production_orders') }}</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalProductionOrders }}</p>
                </div>
                <div class="h-12 w-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-clipboard-list text-blue-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-center space-x-2">
                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                        {{ $completedProductionOrders }} {{ __('messages.completed') }}
                    </span>
                    <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                        {{ $inProgressProductionOrders }} {{ __('messages.in_progress') }}
                    </span>
                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                        {{ $plannedProductionOrders }} {{ __('messages.planned') }}
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Card - Previsões de Demanda -->
        <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-purple-500">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">{{ __('messages.demand_forecasts') }}</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalDemandForecasts }}</p>
                </div>
                <div class="h-12 w-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-sm text-gray-500">
                    {{ __('messages.last_update') }}: {{ $lastForecastUpdate }}
                </div>
            </div>
        </div>
        
        <!-- Card - Planos de Compra -->
        <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-green-500">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">{{ __('messages.purchase_plans') }}</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalPurchasePlans }}</p>
                </div>
                <div class="h-12 w-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-green-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-center space-x-2">
                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                        {{ $plannedPurchases }} {{ __('messages.planned') }}
                    </span>
                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                        {{ $orderedPurchases }} {{ __('messages.ordered') }}
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Card - Status de Estoque -->
        <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-amber-500">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">{{ __('messages.inventory_status') }}</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalInventoryItems }}</p>
                </div>
                <div class="h-12 w-12 bg-amber-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-boxes text-amber-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-center space-x-2">
                    <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                        {{ $lowStockCount }} {{ __('messages.low_stock') }}
                    </span>
                    <span class="bg-amber-100 text-amber-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                        {{ $mediumStockCount }} {{ __('messages.medium_stock') }}
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Gráficos e Informações Detalhadas -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Gráfico de Ordens de Produção -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-4">
                <h2 class="text-lg font-semibold text-white flex items-center">
                    <i class="fas fa-chart-bar mr-2"></i>
                    {{ __('messages.production_by_status') }}
                </h2>
            </div>
            <div class="p-4">
                <div class="h-64">
                    <canvas id="productionChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Gráfico de Demanda vs. Capacidade -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-4 py-4">
                <h2 class="text-lg font-semibold text-white flex items-center">
                    <i class="fas fa-balance-scale mr-2"></i>
                    {{ __('messages.demand_vs_capacity') }}
                </h2>
            </div>
            <div class="p-4">
                <div class="h-64">
                    <canvas id="demandVsCapacityChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Lista de Alertas e Atividades Recentes -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Alertas Ativos -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-gradient-to-r from-red-600 to-red-700 px-4 py-4">
                <h2 class="text-lg font-semibold text-white flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    {{ __('messages.active_alerts') }}
                </h2>
            </div>
            <div class="p-4">
                <ul class="divide-y divide-gray-200">
                    @forelse($alerts as $alert)
                        <li class="py-3 flex items-start">
                            <div class="flex-shrink-0 text-center mr-3">
                                <span class="inline-flex items-center justify-center h-8 w-8 rounded-full
                                    {{ $alert['severity'] === 'critical' ? 'bg-red-100 text-red-600' : 
                                       ($alert['severity'] === 'high' ? 'bg-amber-100 text-amber-600' : 'bg-blue-100 text-blue-600') }}">
                                    <i class="fas {{ $alert['icon'] }}"></i>
                                </span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $alert['title'] }}</p>
                                <p class="text-sm text-gray-500">{{ $alert['description'] }}</p>
                            </div>
                            <div class="flex-shrink-0 self-center">
                                @can('alerts.view')
                                    <button type="button" wire:click="viewAlert({{ $alert['id'] }})"
                                        class="rounded-full p-1 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                        title="{{ __('messages.view_alert') }}">
                                        <i class="fas fa-arrow-right"></i>
                                    </button>
                                @else
                                    <button type="button" disabled
                                        class="rounded-full p-1 text-gray-300 cursor-not-allowed"
                                        title="{{ __('messages.no_permission') }}">
                                        <i class="fas fa-arrow-right"></i>
                                    </button>
                                @endcan
                            </div>
                        </li>
                    @empty
                        <li class="py-4 text-center text-gray-500">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            {{ __('messages.no_active_alerts') }}
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
        
        <!-- Atividades Recentes -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-gradient-to-r from-gray-700 to-gray-800 px-4 py-4">
                <h2 class="text-lg font-semibold text-white flex items-center">
                    <i class="fas fa-history mr-2"></i>
                    {{ __('messages.recent_activities') }}
                </h2>
            </div>
            <div class="p-4">
                <ul class="divide-y divide-gray-200">
                    @forelse($recentActivities as $activity)
                        <li class="py-3">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-gray-100">
                                        <i class="fas {{ $activity['icon'] }} text-gray-600"></i>
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $activity['title'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $activity['time'] }}</p>
                                </div>
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $activity['type'] === 'production' ? 'bg-blue-100 text-blue-800' : 
                                           ($activity['type'] === 'purchase' ? 'bg-green-100 text-green-800' : 
                                           ($activity['type'] === 'inventory' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-800')) }}">
                                        {{ $activity['typeText'] }}
                                    </span>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="py-4 text-center text-gray-500">
                            {{ __('messages.no_recent_activities') }}
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Scripts para os gráficos -->
    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', function () {
            // Gráfico de Ordens de Produção
            const productionCtx = document.getElementById('productionChart').getContext('2d');
            const productionData = @json($productionChartData);
            
            new Chart(productionCtx, {
                type: 'doughnut',
                data: {
                    labels: productionData.labels,
                    datasets: [{
                        data: productionData.data,
                        backgroundColor: productionData.colors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
            
            // Gráfico de Demanda vs. Capacidade
            const demandCapacityCtx = document.getElementById('demandVsCapacityChart').getContext('2d');
            const demandCapacityData = @json($demandCapacityChartData);
            
            new Chart(demandCapacityCtx, {
                type: 'bar',
                data: {
                    labels: demandCapacityData.labels,
                    datasets: [
                        {
                            label: '{{ __("messages.demand") }}',
                            data: demandCapacityData.demandData,
                            backgroundColor: '#93c5fd',
                            borderColor: '#3b82f6',
                            borderWidth: 1
                        },
                        {
                            label: '{{ __("messages.capacity") }}',
                            data: demandCapacityData.capacityData,
                            backgroundColor: '#c4b5fd',
                            borderColor: '#8b5cf6',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</div>
