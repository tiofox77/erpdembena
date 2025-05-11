<div>
    <!-- Cabeçalho da Página -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 inline-flex items-center">
                <i class="fas fa-chart-line text-blue-600 mr-2"></i>
                {{ __('messages.financial_reporting') }}
            </h1>
            <p class="mt-1 text-sm text-gray-500">{{ __('messages.financial_reporting_description') }}</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-2">
            <button type="button" wire:click="generateReport" 
                class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                <i class="fas fa-chart-bar mr-2"></i>
                {{ __('messages.generate_report') }}
            </button>
            
            <button type="button" wire:click="exportReport" 
                class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                <i class="fas fa-download mr-2"></i>
                {{ __('messages.export_report') }}
            </button>
        </div>
    </div>
    
    <!-- Cartão de Filtros -->
    <div class="bg-white rounded-lg shadow-md mb-6 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-4 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center">
                <i class="fas fa-filter mr-2"></i>
                {{ __('messages.report_filters') }}
            </h2>
        </div>
        
        <div class="p-5">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Tipo de Relatório -->
                <div>
                    <label for="reportType" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('messages.report_type') }}
                    </label>
                    <select id="reportType" wire:model.live="reportType" 
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md transition-all duration-200 ease-in-out">
                        @foreach($reportTypes as $value => $label)
                            <option value="{{ $value }}">{{ __('messages.' . $value) }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Período -->
                <div>
                    <label for="timeframe" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('messages.timeframe') }}
                    </label>
                    <select id="timeframe" wire:model.live="timeframe" 
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md transition-all duration-200 ease-in-out">
                        @foreach($timeframes as $value => $label)
                            <option value="{{ $value }}">{{ __('messages.' . $value . '_timeframe') }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Data Inicial (visível apenas quando timeframe = custom) -->
                <div x-data="{}" x-show="$wire.timeframe === 'custom'">
                    <label for="startDate" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('messages.start_date') }}
                    </label>
                    <input type="date" id="startDate" wire:model.live="dateRange.start" 
                        class="mt-1 block w-full pl-3 pr-3 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md transition-all duration-200 ease-in-out">
                </div>
                
                <!-- Data Final (visível apenas quando timeframe = custom) -->
                <div x-data="{}" x-show="$wire.timeframe === 'custom'">
                    <label for="endDate" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('messages.end_date') }}
                    </label>
                    <input type="date" id="endDate" wire:model.live="dateRange.end" 
                        class="mt-1 block w-full pl-3 pr-3 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md transition-all duration-200 ease-in-out">
                </div>
                
                <!-- Filtro de Produto -->
                <div class="md:col-span-2">
                    <label for="productFilter" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('messages.product_filter') }}
                    </label>
                    <select id="productFilter" wire:model.live="productFilter" 
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md transition-all duration-200 ease-in-out">
                        <option value="">{{ __('messages.all_products') }}</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="md:col-span-2 flex items-end">
                    <!-- Botão para gerar relatório -->
                    <button type="button" wire:click="generateReport" 
                        class="inline-flex justify-center items-center px-4 py-2 w-full border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        <i class="fas fa-chart-bar mr-2"></i>
                        {{ __('messages.generate_report') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cartão de Visão Geral do MRP -->
    <div class="bg-white rounded-lg shadow-md mb-6 overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 px-4 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center">
                <i class="fas fa-tachometer-alt mr-2"></i>
                {{ __('messages.mrp_financial_overview') }}
            </h2>
        </div>
        
        <div class="p-4">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                <!-- Card: Custo de Produção -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200 flex flex-col">
                    <div class="font-medium text-blue-800 text-sm mb-1">{{ __('messages.production_costs') }}</div>
                    <div class="flex justify-between items-center mt-2">
                        <div class="text-2xl font-bold text-blue-700">
                            <!-- Simulação de dados, em um sistema real seria dinâmico -->
                            {{ number_format(rand(50000, 150000), 2) }} {{ __('messages.currency') }}
                        </div>
                        <div class="rounded-full p-2 bg-blue-200">
                            <i class="fas fa-industry text-blue-600"></i>
                        </div>
                    </div>
                    <div class="text-xs text-blue-600 mt-3">
                        <i class="fas fa-arrow-{{ rand(0, 1) ? 'up' : 'down' }} mr-1"></i>
                        {{ rand(1, 15) }}% {{ __('messages.from_last_period') }}
                    </div>
                </div>
                
                <!-- Card: Custo de Compras -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200 flex flex-col">
                    <div class="font-medium text-green-800 text-sm mb-1">{{ __('messages.purchase_costs') }}</div>
                    <div class="flex justify-between items-center mt-2">
                        <div class="text-2xl font-bold text-green-700">
                            {{ number_format(rand(20000, 80000), 2) }} {{ __('messages.currency') }}
                        </div>
                        <div class="rounded-full p-2 bg-green-200">
                            <i class="fas fa-shopping-cart text-green-600"></i>
                        </div>
                    </div>
                    <div class="text-xs text-green-600 mt-3">
                        <i class="fas fa-arrow-{{ rand(0, 1) ? 'up' : 'down' }} mr-1"></i>
                        {{ rand(1, 15) }}% {{ __('messages.from_last_period') }}
                    </div>
                </div>
                
                <!-- Card: Lucratividade -->
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200 flex flex-col">
                    <div class="font-medium text-purple-800 text-sm mb-1">{{ __('messages.profitability') }}</div>
                    <div class="flex justify-between items-center mt-2">
                        <div class="text-2xl font-bold text-purple-700">
                            {{ rand(10, 35) }}%
                        </div>
                        <div class="rounded-full p-2 bg-purple-200">
                            <i class="fas fa-chart-line text-purple-600"></i>
                        </div>
                    </div>
                    <div class="text-xs text-purple-600 mt-3">
                        <i class="fas fa-arrow-{{ rand(0, 1) ? 'up' : 'down' }} mr-1"></i>
                        {{ rand(1, 5) }}% {{ __('messages.from_last_period') }}
                    </div>
                </div>
                
                <!-- Card: Ordens Completas -->
                <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-lg p-4 border border-amber-200 flex flex-col">
                    <div class="font-medium text-amber-800 text-sm mb-1">{{ __('messages.completed_orders') }}</div>
                    <div class="flex justify-between items-center mt-2">
                        <div class="text-2xl font-bold text-amber-700">
                            {{ rand(10, 100) }}
                        </div>
                        <div class="rounded-full p-2 bg-amber-200">
                            <i class="fas fa-clipboard-check text-amber-600"></i>
                        </div>
                    </div>
                    <div class="text-xs text-amber-600 mt-3">
                        <i class="fas fa-arrow-{{ rand(0, 1) ? 'up' : 'down' }} mr-1"></i>
                        {{ rand(1, 20) }}% {{ __('messages.from_last_period') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Área para Dicas de Relatório -->
    <div class="bg-white rounded-lg shadow-md mb-6 overflow-hidden">
        <div class="bg-gradient-to-r from-gray-700 to-gray-800 px-4 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center">
                <i class="fas fa-lightbulb mr-2"></i>
                {{ __('messages.report_recommendations') }}
            </h2>
        </div>
        
        <div class="p-5">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Dica 1 -->
                <div class="p-4 border border-gray-200 rounded-lg hover:shadow-md transition-all duration-200">
                    <div class="font-medium text-gray-800 mb-2 flex items-center">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-2">
                            <i class="fas fa-chart-pie text-blue-600"></i>
                        </div>
                        {{ __('messages.cost_analysis') }}
                    </div>
                    <p class="text-sm text-gray-600">
                        {{ __('messages.cost_analysis_tip') }}
                    </p>
                    <button type="button" wire:click="$set('reportType', 'production-costs')" 
                        class="mt-3 inline-flex items-center text-xs font-medium text-blue-600 hover:text-blue-800">
                        {{ __('messages.try_this_report') }}
                        <i class="fas fa-arrow-right ml-1"></i>
                    </button>
                </div>
                
                <!-- Dica 2 -->
                <div class="p-4 border border-gray-200 rounded-lg hover:shadow-md transition-all duration-200">
                    <div class="font-medium text-gray-800 mb-2 flex items-center">
                        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center mr-2">
                            <i class="fas fa-balance-scale text-green-600"></i>
                        </div>
                        {{ __('messages.period_comparison') }}
                    </div>
                    <p class="text-sm text-gray-600">
                        {{ __('messages.period_comparison_tip') }}
                    </p>
                    <button type="button" wire:click="$set('reportType', 'cost-comparison')" 
                        class="mt-3 inline-flex items-center text-xs font-medium text-green-600 hover:text-green-800">
                        {{ __('messages.try_this_report') }}
                        <i class="fas fa-arrow-right ml-1"></i>
                    </button>
                </div>
                
                <!-- Dica 3 -->
                <div class="p-4 border border-gray-200 rounded-lg hover:shadow-md transition-all duration-200">
                    <div class="font-medium text-gray-800 mb-2 flex items-center">
                        <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center mr-2">
                            <i class="fas fa-percentage text-purple-600"></i>
                        </div>
                        {{ __('messages.product_profitability') }}
                    </div>
                    <p class="text-sm text-gray-600">
                        {{ __('messages.product_profitability_tip') }}
                    </p>
                    <button type="button" wire:click="$set('reportType', 'product-profitability')" 
                        class="mt-3 inline-flex items-center text-xs font-medium text-purple-600 hover:text-purple-800">
                        {{ __('messages.try_this_report') }}
                        <i class="fas fa-arrow-right ml-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal para exibir relatório -->
    <div x-cloak
        class="fixed inset-0 z-30 flex items-center justify-center overflow-auto bg-gray-800 bg-opacity-75 transition-opacity"
        x-show="$wire.showReportModal"
        @keydown.escape.window="$wire.closeReportModal()">
        
        <div class="relative w-full max-w-6xl mx-auto my-8 px-4 sm:px-0"
            x-show="$wire.showReportModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95"
            @click.away="$wire.closeReportModal()">
            
            <div class="relative bg-white rounded-lg shadow-xl overflow-hidden" @click.stop>
                <!-- Cabeçalho do Modal -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-4 py-4 sm:px-6 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-white">
                        <i class="fas fa-chart-bar mr-2"></i>
                        {{ $reportTitle }}
                    </h3>
                    <button @click="$wire.closeReportModal()" class="text-white hover:text-gray-200 focus:outline-none">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="p-6">
                    <!-- Descrição do relatório -->
                    <p class="text-sm text-gray-600 mb-6">{{ $reportDescription }}</p>
                    
                    <!-- Área do gráfico -->
                    <div class="mb-6 bg-gray-50 p-4 rounded-lg shadow-inner">
                        <div class="h-64 md:h-80">
                            <!-- O gráfico será renderizado pelo ChartJS -->
                            <canvas id="reportChart" class="w-full h-full"></canvas>
                        </div>
                    </div>
                    
                    <!-- Tabela de dados do relatório -->
                    <div class="overflow-x-auto">
                        @if($reportType === 'production-costs')
                            @include('livewire.mrp.financial-reporting.production-costs-table')
                        @elseif($reportType === 'purchase-costs')
                            @include('livewire.mrp.financial-reporting.purchase-costs-table')
                        @elseif($reportType === 'cost-comparison')
                            @include('livewire.mrp.financial-reporting.cost-comparison-table')
                        @elseif($reportType === 'product-profitability')
                            @include('livewire.mrp.financial-reporting.product-profitability-table')
                        @endif
                    </div>
                </div>
                
                <!-- Rodapé do Modal -->
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-col sm:flex-row-reverse space-y-2 sm:space-y-0 sm:space-x-2 sm:space-x-reverse">
                    <button wire:click="exportReport('pdf')" 
                        class="inline-flex justify-center items-center w-full sm:w-auto px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out">
                        <i class="fas fa-file-pdf mr-2"></i>
                        {{ __('messages.export_as_pdf') }}
                    </button>
                    <button wire:click="exportReport('csv')" 
                        class="inline-flex justify-center items-center w-full sm:w-auto px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 ease-in-out">
                        <i class="fas fa-file-csv mr-2"></i>
                        {{ __('messages.export_as_csv') }}
                    </button>
                    <button wire:click="closeReportModal" 
                        class="inline-flex justify-center items-center w-full sm:w-auto px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out">
                        <i class="fas fa-times mr-2"></i>
                        {{ __('messages.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts para ChartJS -->
    @if($showReportModal)
        <script>
            document.addEventListener('livewire:initialized', function () {
                const ctx = document.getElementById('reportChart').getContext('2d');
                const chartData = @js($chartData);
                const chartOptions = @js($chartOptions);
                
                if (chartOptions.type === 'bar') {
                    new Chart(ctx, {
                        type: chartOptions.type,
                        data: chartData,
                        options: chartOptions.options
                    });
                } else if (chartOptions.type === 'pie') {
                    new Chart(ctx, {
                        type: chartOptions.type,
                        data: chartData,
                        options: chartOptions.options
                    });
                } else if (chartOptions.type === 'line') {
                    new Chart(ctx, {
                        type: chartOptions.type,
                        data: chartData,
                        options: chartOptions.options
                    });
                }
            });
        </script>
    @endif
</div>
