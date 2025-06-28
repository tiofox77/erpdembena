<div>
    <div class="container mx-auto px-4 py-6">
        <!-- Cabeçalho do relatório -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
            <h1 class="text-2xl font-semibold text-gray-800 flex items-center">
                <i class="fas fa-warehouse text-blue-600 mr-3"></i>
                {{ __('messages.inventory_management_report') }}
            </h1>
            
            <!-- Seletores de visualização -->
            <div class="flex space-x-2">
                <button wire:click="$set('viewMode', 'table')" class="inline-flex items-center px-3 py-1 rounded-md {{ $viewMode == 'table' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }} transition-all duration-200 ease-in-out transform hover:scale-105">
                    <i class="fas fa-table mr-1"></i>{{ __('messages.table') }}
                </button>
                <button wire:click="$set('viewMode', 'chart')" class="inline-flex items-center px-3 py-1 rounded-md {{ $viewMode == 'chart' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }} transition-all duration-200 ease-in-out transform hover:scale-105">
                    <i class="fas fa-chart-line mr-1"></i>{{ __('messages.charts') }}
                </button>
                <button wire:click="$set('viewMode', 'summary')" class="inline-flex items-center px-3 py-1 rounded-md {{ $viewMode == 'summary' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }} transition-all duration-200 ease-in-out transform hover:scale-105">
                    <i class="fas fa-clipboard-list mr-1"></i>{{ __('messages.summary') }}
                </button>
            </div>
        </div>

        <!-- Dashboard de estatísticas de inventário -->
        <div class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                <!-- Total de Itens -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow p-4 border border-blue-200 transition-all duration-300 hover:shadow-lg transform hover:scale-[1.01]">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-blue-800 mb-1">{{ __('messages.total_items') }}</p>
                            <p class="text-2xl font-bold text-blue-900">{{ $summaryStats['total_items'] }}</p>
                        </div>
                        <div class="rounded-full bg-blue-500 p-3 text-white">
                            <i class="fas fa-boxes fa-lg"></i>
                        </div>
                    </div>
                    <p class="text-xs text-blue-700 mt-2">{{ __('messages.total_inventory_items') }}</p>
                </div>

                <!-- Quantidade Total em Estoque -->
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg shadow p-4 border border-purple-200 transition-all duration-300 hover:shadow-lg transform hover:scale-[1.01]">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-purple-800 mb-1">{{ __('messages.total_quantity') }}</p>
                            @php
                                $totalQuantity = DB::table('sc_inventory_items')->sum('quantity_on_hand');
                            @endphp
                            <p class="text-2xl font-bold text-purple-900">{{ number_format($totalQuantity) }}</p>
                        </div>
                        <div class="rounded-full bg-purple-500 p-3 text-white">
                            <i class="fas fa-cubes fa-lg"></i>
                        </div>
                    </div>
                    <p class="text-xs text-purple-700 mt-2">{{ __('messages.total_stock_quantity') }}</p>
                </div>
                
                <!-- Valor Total do Estoque -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg shadow p-4 border border-green-200 transition-all duration-300 hover:shadow-lg transform hover:scale-[1.01]">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-green-800 mb-1">{{ __('messages.total_value') }}</p>
                            <p class="text-2xl font-bold text-green-900">{{ number_format($summaryStats['total_value'], 2) }}</p>
                        </div>
                        <div class="rounded-full bg-green-500 p-3 text-white">
                            <i class="fas fa-dollar-sign fa-lg"></i>
                        </div>
                    </div>
                    <p class="text-xs text-green-700 mt-2">{{ __('messages.total_inventory_value') }}</p>
                </div>

                <!-- Itens Baixo Estoque -->
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg shadow p-4 border border-yellow-200 transition-all duration-300 hover:shadow-lg transform hover:scale-[1.01]">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-yellow-800 mb-1">{{ __('messages.low_stock') }}</p>
                            <p class="text-2xl font-bold text-yellow-900">{{ $summaryStats['low_stock_items'] }}</p>
                        </div>
                        <div class="rounded-full bg-yellow-500 p-3 text-white">
                            <i class="fas fa-exclamation-triangle fa-lg"></i>
                        </div>
                    </div>
                    <p class="text-xs text-yellow-700 mt-2">{{ __('messages.items_below_min_level') }}</p>
                </div>

                <!-- Itens Sem Estoque -->
                <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg shadow p-4 border border-red-200 transition-all duration-300 hover:shadow-lg transform hover:scale-[1.01]">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-red-800 mb-1">{{ __('messages.out_of_stock') }}</p>
                            <p class="text-2xl font-bold text-red-900">{{ $summaryStats['out_of_stock_items'] }}</p>
                        </div>
                        <div class="rounded-full bg-red-500 p-3 text-white">
                            <i class="fas fa-times-circle fa-lg"></i>
                        </div>
                    </div>
                    <p class="text-xs text-red-700 mt-2">{{ __('messages.items_with_zero_stock') }}</p>
                </div>
            </div>
        </div>

        <!-- Cartão de Busca e Filtros -->
        <div class="mb-6 bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 ease-in-out hover:shadow-lg transform hover:scale-[1.01]">
            <!-- Cabeçalho do cartão com gradiente -->
            <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                <i class="fas fa-filter text-blue-600 mr-2"></i>
                <h2 class="text-base font-medium text-gray-700">{{ __('messages.advanced_filters') }}</h2>
            </div>
            
            <!-- Conteúdo do cartão -->
            <div class="p-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-medium text-gray-700">{{ __('messages.filter_instructions') }}</h3>
                    <button wire:click="resetFilters" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <i class="fas fa-times-circle mr-2"></i>
                        <span>{{ __('messages.reset_filters') }}</span>
                        <svg wire:loading wire:target="resetFilters" class="animate-spin ml-2 h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <!-- Pesquisa -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.search') }}</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-all duration-200 ease-in-out hover:border-blue-400" placeholder="{{ __('messages.search_products_sku_batch') }}">
                    </div>
                </div>

                <!-- Filtro por Localização/Armazém -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.location') }}</label>
                    <select wire:model.live="filters.location" class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md transition-all duration-200 ease-in-out hover:border-blue-400">
                        <option value="">{{ __('messages.all_locations') }}</option>
                        @foreach($inventoryLocations as $location)
                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Filtro por Tipo de Armazém -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.warehouse_type') }}</label>
                    <select wire:model.live="filters.warehouse_type" class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md transition-all duration-200 ease-in-out hover:border-blue-400">
                        <option value="">{{ __('messages.all_warehouses') }}</option>
                        <option value="raw_material">{{ __('messages.raw_material_warehouse') }}</option>
                        <option value="finished_product">{{ __('messages.finished_product_warehouse') }}</option>
                    </select>
                </div>

                <!-- Filtro por Categoria -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.category') }}</label>
                    <select wire:model.live="filters.category" class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md transition-all duration-200 ease-in-out hover:border-blue-400">
                        <option value="">{{ __('messages.all_categories') }}</option>
                        @foreach($productCategories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Filtro por Tipo de Produto -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.product_type') }}</label>
                    <select wire:model.live="filters.product_type" class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md transition-all duration-200 ease-in-out hover:border-blue-400">
                        <option value="">{{ __('messages.all_types') }}</option>
                        @foreach($productTypes as $type)
                            <option value="{{ $type }}">{{ __("messages.$type") }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro por Status de Estoque -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.stock_status') }}</label>
                    <select wire:model.live="filters.stock_status" class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md transition-all duration-200 ease-in-out hover:border-blue-400">
                        <option value="">{{ __('messages.all_stock') }}</option>
                        <option value="normal">{{ __('messages.normal_stock') }}</option>
                        <option value="reorder">{{ __('messages.reorder_level') }}</option>
                        <option value="low">{{ __('messages.low_stock') }}</option>
                        <option value="out">{{ __('messages.out_of_stock') }}</option>
                    </select>
                </div>
            </div>
            
            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                <!-- Filtro por Data de Validade -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.expiry_date_filter') }}</label>
                    <select wire:model.live="filters.expiry_date" class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md transition-all duration-200 ease-in-out hover:border-blue-400">
                        <option value="">{{ __('messages.all_expiry_dates') }}</option>
                        <option value="30">{{ __('messages.expires_in_30_days') }}</option>
                        <option value="60">{{ __('messages.expires_in_60_days') }}</option>
                        <option value="90">{{ __('messages.expires_in_90_days') }}</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Conteúdo principal do relatório -->
    <div class="mt-6">
        @if ($viewMode == 'table')
            <!-- Modo de visualização em tabela -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden transition-all duration-300 ease-in-out hover:shadow-lg">
                <!-- Cabeçalho da Tabela -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-boxes mr-2"></i>
                        {{ __('messages.inventory_items') }}
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('products.name')">
                                    {{ __('messages.product') }}
                                    @if ($sortField == 'products.name')
                                        <i class="fas fa-sort-{{ $sortDirection == 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('messages.sku') }}
                                </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('messages.category') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('messages.location') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('messages.quantity') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('messages.unit_price') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('messages.total_value') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('messages.status') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($inventory as $item)
                            <tr class="hover:bg-gray-50 transition-colors duration-200 ease-in-out">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                                            <i class="fas fa-box text-blue-500"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $item->product_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $item->unit_of_measure }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $item->product_sku }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-700">{{ $item->category_name ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-700">
                                        <i class="fas fa-warehouse text-blue-500 mr-1"></i>
                                        {{ $item->location_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $item->quantity_on_hand }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ number_format($item->unit_price, 2) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ number_format($item->item_value, 2) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($item->quantity_on_hand <= 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-exclamation-circle mr-1 animate-pulse"></i>
                                            {{ __('messages.out_of_stock') }}
                                        </span>
                                    @elseif($item->quantity_on_hand <= $item->min_stock_level)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            {{ __('messages.low_stock') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            {{ __('messages.in_stock') }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    {{ __('messages.no_records_found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <div class="mt-4">
                {{ $inventory->links() }}
            </div>
            
            <!-- Seletor de itens por página -->
            <div class="mt-4 flex items-center justify-end">
                <span class="text-sm text-gray-700 mr-2">{{ __('messages.items_per_page') }}</span>
                <select wire:model="perPage" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" style="max-width: 80px;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        @elseif ($viewMode == 'chart')
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('messages.chart_view') }}</h3>
                    <div class="flex space-x-4 mb-4">
                        <button wire:click="changeChartType('inventory_value')" 
                            class="px-4 py-2 font-semibold rounded-lg shadow-md transition duration-200 ease-in-out transform hover:scale-105"
                            :class="{'bg-blue-500 text-white hover:bg-blue-600': $wire.chartType === 'inventory_value', 'bg-gray-200 text-gray-700 hover:bg-gray-300': $wire.chartType !== 'inventory_value'}">
                            Valor de Inventário
                        </button>
                        <button wire:click="changeChartType('turnover')" 
                            class="px-4 py-2 font-semibold rounded-lg shadow-md transition duration-200 ease-in-out transform hover:scale-105"
                            :class="{'bg-blue-500 text-white hover:bg-blue-600': $wire.chartType === 'turnover', 'bg-gray-200 text-gray-700 hover:bg-gray-300': $wire.chartType !== 'turnover'}">
                            Giro de Estoque
                        </button>
                        <button wire:click="changeChartType('abc_analysis')" 
                            class="px-4 py-2 font-semibold rounded-lg shadow-md transition duration-200 ease-in-out transform hover:scale-105"
                            :class="{'bg-blue-500 text-white hover:bg-blue-600': $wire.chartType === 'abc_analysis', 'bg-gray-200 text-gray-700 hover:bg-gray-300': $wire.chartType !== 'abc_analysis'}">
                            Análise ABC
                        </button>
                    </div>
                </div>
                
                <!-- Área para o gráfico -->
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-4">
                    <!-- Debug info (hidden) -->
                    <pre id="debug-info" class="hidden">{{ json_encode($chartData) }}</pre>
                    
                    <script>
                        document.addEventListener('livewire:initialized', function () {
                            // Debug dos dados do gráfico
                            console.log('Componente inicializado com chartData:', @json($chartData));
                            document.getElementById('debug-info').textContent = JSON.stringify(@json($chartData), null, 2);
                            // Carregar os dados do gráfico quando a página for carregada
                            document.addEventListener('livewire:initialized', function() {
                                if (@this.viewMode == 'chart') {
                                    @this.loadChartData();
                                }
                            });
                            
                            // Adicionando spinners de carregamento aos botões
                            document.addEventListener('livewire:chartDataLoading', () => {
                                document.getElementById('chart-loading').classList.remove('hidden');
                            });
                            
                            document.addEventListener('livewire:chartDataUpdated', () => {
                                document.getElementById('chart-loading').classList.add('hidden');
                            });
                        });
                    </script>
                    
                    <!-- Indicador de carregamento global para operações do Livewire -->
                    <div class="flex justify-center items-center my-4 loading-indicator" 
                         wire:loading
                         wire:target="loadChartData, changeChartType">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-700"></div>
                        <span class="ml-2 text-gray-700">{{ __('messages.loading_chart_data') }}</span>
                    </div>
                    
                    <!-- Indicador de carregamento específico para o gráfico -->
                    <div id="chart-loading" class="flex justify-center items-center my-4 hidden">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-700"></div>
                        <span class="ml-2 text-gray-700">Renderizando gráfico...</span>
                    </div>
                    
                    <!-- Notificador de erros -->
                    <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded relative mb-4 hidden" 
                         id="error-message">
                        <span class="block sm:inline" id="error-text">Ocorreu um problema ao carregar os dados do gráfico.</span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.classList.add('hidden')">
                            <svg class="fill-current h-6 w-6 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                            </svg>
                        </span>
                    </div>
                    
                    <!-- Container para o gráfico com altura fixa para evitar problemas de renderização -->
                    <div id="chart-container" class="relative bg-white p-4 rounded-lg border border-gray-200" style="height: 400px;">
                        <!-- Canvas será criado dinamicamente pelo Alpine.js -->
                    </div>
                    <div x-data="{
                        chart: null,
                        renderPending: false,
                        
                        init() {
                            console.log('Inicializando componente Alpine');
                            
                            // Escutar eventos Livewire para atualização de dados
                            Livewire.on('chartDataUpdated', () => {
                                console.log('Evento chartDataUpdated recebido');
                                setTimeout(() => this.renderChart(), 100);
                            });
                            
                            // Escutar evento de atualização de filtros
                            Livewire.on('filtersUpdated', () => {
                                console.log('Filtros atualizados, recarregando dados');
                                const loadingEl = document.getElementById('chart-loading');
                                if (loadingEl) loadingEl.classList.remove('hidden');
                                
                                // Precisamos recarregar os dados do gráfico
                                setTimeout(() => this.renderChart(), 300);
                            });
                            
                            // Carrega dados iniciais
                            $wire.loadChartData();
                            
                            // Renderizar gráfico inicial após atraso para garantir DOM pronto
                            setTimeout(() => this.renderChart(), 500);
                        },
                        
                        renderChart() {
                            console.log('Renderizando gráfico');
                            this.renderPending = true;
                            
                            // Mostrar carregamento
                            const loadingEl = document.getElementById('chart-loading');
                            if (loadingEl) loadingEl.classList.remove('hidden');
                            
                            // Esconder mensagem de erro se estiver visível
                            const errorEl = document.getElementById('error-message');
                            if (errorEl) errorEl.classList.add('hidden');
                            
                            // Destruir gráfico anterior se existir
                            if (this.chart) {
                                try {
                                    this.chart.destroy();
                                } catch (e) {
                                    console.warn('Erro ao destruir gráfico anterior:', e);
                                }
                                this.chart = null;
                            }
                            
                            // Buscar o container onde o canvas será adicionado
                            const container = document.getElementById('chart-container');
                            if (!container) {
                                console.error('Container do gráfico não encontrado');
                                if (loadingEl) loadingEl.classList.add('hidden');
                                if (errorEl) {
                                    errorEl.classList.remove('hidden');
                                    const errorTextEl = document.getElementById('error-text');
                                    if (errorTextEl) errorTextEl.textContent = 'Container do gráfico não encontrado';
                                }
                                return;
                            }
                            
                            // Limpar o container
                            container.innerHTML = '';
                            
                            // Criar um novo canvas
                            const canvas = document.createElement('canvas');
                            canvas.id = 'chart-canvas';
                            canvas.className = 'w-full h-full';
                            container.appendChild(canvas);
                            
                            // Buscar dados do Livewire
                            const chartType = $wire.chartType || 'inventory_value';
                            const chartData = $wire.chartData || null;
                            
                            // Verificar dados
                            if (!chartData || !chartData.labels || !chartData.datasets) {
                                console.warn('Dados de gráfico inválidos ou ausentes, tentando recarregar');
                                $wire.loadChartData();
                                if (loadingEl) loadingEl.classList.add('hidden');
                                if (errorEl) {
                                    errorEl.classList.remove('hidden');
                                    const errorTextEl = document.getElementById('error-text');
                                    if (errorTextEl) errorTextEl.textContent = 'Dados de gráfico inválidos ou ausentes';
                                }
                                return;
                            }
                            
                            // Dar tempo para o DOM processar a criação do canvas
                            setTimeout(() => {
                                try {
                                    // Agora que o canvas foi criado e adicionado ao DOM, podemos obter seu contexto
                                    const ctx = canvas.getContext('2d');
                                    
                                    // Determinar tipo de gráfico com base no chartType
                                    let chartJsType = 'line';
                                    if (chartType === 'abc_analysis') chartJsType = 'pie';
                                    else if (chartType === 'turnover') chartJsType = 'bar';
                                    
                                    // Configurações específicas para cada tipo
                                    let options = {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        animation: {
                                            duration: 500 // Animação mais rápida para melhor desempenho
                                        }
                                    };
                                    
                                    if (chartJsType === 'pie') {
                                        options.plugins = {
                                            legend: { position: 'top' },
                                            title: { display: true, text: chartData.title || 'Análise de Inventário' }
                                        };
                                    } else {
                                        options.scales = {
                                            y: { beginAtZero: true }
                                        };
                                    }
                                    
                                    console.log(`Criando gráfico ${chartJsType} com dados:`, chartData);
                                    
                                    // Criar gráfico
                                    this.chart = new Chart(ctx, {
                                        type: chartJsType,
                                        data: chartData,
                                        options: options
                                    });
                                    
                                    this.renderPending = false;
                                    if (loadingEl) loadingEl.classList.add('hidden');
                                } catch (error) {
                                    console.error('Erro ao renderizar gráfico:', error);
                                    if (loadingEl) loadingEl.classList.add('hidden');
                                    if (errorEl) {
                                        errorEl.classList.remove('hidden');
                                        const errorTextEl = document.getElementById('error-text');
                                        if (errorTextEl) errorTextEl.textContent = `Erro na renderização: ${error.message}`;
                                    }
                                }
                            }, 100); // Pequeno atraso para garantir que o DOM processou a criação do canvas
                        },
                    }">                        
                        <!-- Área do Gráfico com Alpine.js -->
                        <div class="flex flex-col items-center">
                            <!-- Já temos o canvas e indicadores de carregamento acima, não precisamos duplicar aqui -->
                        </div>
                    </div>
                </div>
                
                <p class="text-xs text-gray-500 mt-2">* Dados baseados no estoque atual e histórico recente</p>
            </div>
        @elseif ($viewMode == 'summary')
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('messages.summary_view') }}</h3>
                
                <!-- Estatísticas Gerais -->
                <div class="mb-8">
                    <h4 class="font-medium text-gray-800 mb-3 border-b pb-2">{{ __('messages.general_statistics') }}</h4>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Coluna 1: Estatísticas de Itens -->
                        <div>
                            <div class="mb-4">
                                <h5 class="text-sm font-medium text-gray-600 mb-2">{{ __('messages.inventory_stats') }}</h5>
                                <ul class="divide-y divide-gray-200">
                                    <li class="py-2 flex justify-between">
                                        <span class="text-gray-600">{{ __('messages.total_items') }}:</span>
                                        <span class="font-medium">{{ $summaryStats['total_items'] }}</span>
                                    </li>
                                    <li class="py-2 flex justify-between">
                                        <span class="text-gray-600">{{ __('messages.normal_stock') }}:</span>
                                        <span class="font-medium text-green-600">{{ $summaryStats['normal_stock_items'] }} 
                                            <span class="text-gray-500 text-xs">({{ intval(($summaryStats['normal_stock_items']/$summaryStats['total_items'])*100) }}%)</span>
                                        </span>
                                    </li>
                                    <li class="py-2 flex justify-between">
                                        <span class="text-gray-600">{{ __('messages.reorder_level') }}:</span>
                                        <span class="font-medium text-blue-600">{{ $summaryStats['reorder_items'] }} 
                                            <span class="text-gray-500 text-xs">({{ intval(($summaryStats['reorder_items']/$summaryStats['total_items'])*100) }}%)</span>
                                        </span>
                                    </li>
                                    <li class="py-2 flex justify-between">
                                        <span class="text-gray-600">{{ __('messages.low_stock') }}:</span>
                                        <span class="font-medium text-yellow-600">{{ $summaryStats['low_stock_items'] }} 
                                            <span class="text-gray-500 text-xs">({{ intval(($summaryStats['low_stock_items']/$summaryStats['total_items'])*100) }}%)</span>
                                        </span>
                                    </li>
                                    <li class="py-2 flex justify-between">
                                        <span class="text-gray-600">{{ __('messages.out_of_stock') }}:</span>
                                        <span class="font-medium text-red-600">{{ $summaryStats['out_of_stock_items'] }} 
                                            <span class="text-gray-500 text-xs">({{ intval(($summaryStats['out_of_stock_items']/$summaryStats['total_items'])*100) }}%)</span>
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <!-- Coluna 2: Estatísticas de Valor -->
                        <div>
                            <div class="mb-4">
                                <h5 class="text-sm font-medium text-gray-600 mb-2">{{ __('messages.value_statistics') }}</h5>
                                <ul class="divide-y divide-gray-200">
                                    <li class="py-2 flex justify-between">
                                        <span class="text-gray-600">{{ __('messages.total_value') }}:</span>
                                        <span class="font-medium">{{ number_format($summaryStats['total_value'], 2) }}</span>
                                    </li>
                                    <li class="py-2 flex justify-between">
                                        <span class="text-gray-600">{{ __('messages.raw_material_value') }}:</span>
                                        <span class="font-medium text-blue-600">{{ number_format($summaryStats['raw_material_value'], 2) }} 
                                            <span class="text-gray-500 text-xs">({{ $summaryStats['total_value'] > 0 ? intval(($summaryStats['raw_material_value']/$summaryStats['total_value'])*100) : 0 }}%)</span>
                                        </span>
                                    </li>
                                    <li class="py-2 flex justify-between">
                                        <span class="text-gray-600">{{ __('messages.finished_product_value') }}:</span>
                                        <span class="font-medium text-green-600">{{ number_format($summaryStats['finished_product_value'], 2) }} 
                                            <span class="text-gray-500 text-xs">({{ $summaryStats['total_value'] > 0 ? intval(($summaryStats['finished_product_value']/$summaryStats['total_value'])*100) : 0 }}%)</span>
                                        </span>
                                    </li>
                                    <li class="py-2 flex justify-between">
                                        <span class="text-gray-600">{{ __('messages.stock_health') }}:</span>
                                        <div class="w-32">
                                            <div class="flex items-center">
                                                <div class="w-full bg-gray-200 rounded-full h-2">
                                                    <div class="h-2 rounded-full {{ $summaryStats['stock_health_percentage'] > 80 ? 'bg-green-500' : ($summaryStats['stock_health_percentage'] > 50 ? 'bg-yellow-500' : 'bg-red-500') }}" 
                                                        style="width: {{ $summaryStats['stock_health_percentage'] }}%">
                                                    </div>
                                                </div>
                                                <span class="ml-2 text-xs font-medium text-gray-700">{{ intval($summaryStats['stock_health_percentage']) }}%</span>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="py-2 flex justify-between">
                                        <span class="text-gray-600">{{ __('messages.expiring_soon') }}:</span>
                                        <span class="font-medium {{ $summaryStats['expiring_soon'] > 10 ? 'text-yellow-600' : 'text-gray-600' }}">{{ $summaryStats['expiring_soon'] }} {{ __('messages.items') }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Top Produtos por Valor -->
                <div class="mb-6">
                    <h4 class="font-medium text-gray-800 mb-3 border-b pb-2">{{ __('messages.top_value_products') }}</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.product') }}</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.quantity') }}</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.unit_price') }}</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.total_value') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @php
                                $topProducts = DB::table('sc_inventory_items')
                                    ->join('sc_products', 'sc_inventory_items.product_id', '=', 'sc_products.id')
                                    ->select(
                                        'sc_products.name as product_name',
                                        DB::raw('SUM(sc_inventory_items.quantity_on_hand) as total_quantity'),
                                        'sc_products.unit_price',
                                        DB::raw('SUM(sc_inventory_items.quantity_on_hand * sc_products.unit_price) as total_value')
                                    )
                                    ->groupBy('sc_products.name', 'sc_products.unit_price')
                                    ->orderBy('total_value', 'desc')
                                    ->limit(5)
                                    ->get();
                                @endphp
                                
                                @foreach($topProducts as $product)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">{{ $product->product_name }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-600">{{ number_format($product->total_quantity) }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-600">{{ number_format($product->unit_price, 2) }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 font-medium">{{ number_format($product->total_value, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
