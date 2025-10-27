<div>
    <div class="container mx-auto px-4 py-6">
        <!-- Cabeçalho Principal -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
            <h1 class="text-2xl font-semibold text-gray-800 flex items-center">
                <i class="fas fa-chart-line text-blue-600 mr-3"></i>
                {{ __('reports/failure-analysis.title') }}
            </h1>
        </div>

        <!-- Cartão de Busca e Filtros -->
        <div class="mb-6 bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 ease-in-out hover:shadow-lg transform hover:scale-[1.01]">
            <!-- Cabeçalho do cartão com gradiente -->
            <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                <i class="fas fa-filter text-blue-600 mr-2"></i>
                <h2 class="text-base font-medium text-gray-700">{{ __('reports/failure-analysis.filters_and_period') }}</h2>
            </div>
            <!-- Conteúdo do cartão -->
            <div class="p-4">
                <div class="flex flex-col gap-4">
                    <!-- Grid de filtros -->
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <!-- Date Range Filter -->
                        <div>
                            <label for="dateRange" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-calendar-alt text-gray-500 mr-1"></i>
                                {{ __('reports/failure-analysis.period') }}
                            </label>
                            <select id="dateRange" wire:model.live="dateRange" class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="week">{{ __('reports/failure-analysis.current_week') }}</option>
                                <option value="month">{{ __('reports/failure-analysis.current_month') }}</option>
                                <option value="quarter">{{ __('reports/failure-analysis.current_quarter') }}</option>
                                <option value="year">{{ __('reports/failure-analysis.current_year') }}</option>
                                <option value="custom">{{ __('reports/failure-analysis.custom_period') }}</option>
                            </select>
                        </div>

                        <!-- Custom Date Range -->
                        @if ($dateRange === 'custom')
                        <div>
                            <label for="startDate" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-calendar-day text-gray-500 mr-1"></i>
                                {{ __('reports/failure-analysis.start_date') }}
                            </label>
                            <input type="date" id="startDate" wire:model.live="startDate" class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white">
                        </div>
                        <div>
                            <label for="endDate" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-calendar-day text-gray-500 mr-1"></i>
                                {{ __('reports/failure-analysis.end_date') }}
                            </label>
                            <input type="date" id="endDate" wire:model.live="endDate" class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white">
                        </div>
                        @endif

                        <!-- Area Filter -->
                        <div>
                            <label for="selectedArea" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-map-marker-alt text-gray-500 mr-1"></i>
                                {{ __('reports/failure-analysis.area') }}
                            </label>
                            <select id="selectedArea" wire:model.live="selectedArea" class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="all">{{ __('reports/failure-analysis.all_areas') }}</option>
                                @foreach ($areas as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Line Filter -->
                        <div>
                            <label for="selectedLine" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-stream text-gray-500 mr-1"></i>
                                {{ __('reports/failure-analysis.line') }}
                            </label>
                            <select id="selectedLine" wire:model.live="selectedLine" class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="all">{{ __('reports/failure-analysis.all_lines') }}</option>
                                @foreach ($lines as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Equipment Filter -->
                        <div>
                            <label for="selectedEquipment" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-cogs text-gray-500 mr-1"></i>
                                {{ __('reports/failure-analysis.equipment') }}
                            </label>
                            <select id="selectedEquipment" wire:model.live="selectedEquipment" class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="all">{{ __('reports/failure-analysis.all_equipment') }}</option>
                                @foreach ($equipment as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <!-- Botão de reset -->
                    <div class="flex justify-end">
                        <button wire:click="resetFilters" 
                            class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md text-sm font-medium text-gray-700 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                            <i class="fas fa-redo-alt mr-2"></i>
                            {{ __('reports/failure-analysis.reset_filters') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <!-- Total Failures -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 ease-in-out hover:shadow-lg transform hover:scale-[1.01]">
            <div class="flex items-center bg-gradient-to-r from-red-50 to-red-100 px-4 py-2 border-b border-gray-200">
                <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                <h3 class="text-sm font-medium text-gray-700">{{ __('reports/failure-analysis.total_failures') }}</h3>
            </div>
            <div class="p-4">
                <div class="flex items-center">
                    <div class="rounded-full bg-red-100 p-3 mr-4 shadow-sm">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-gray-800">{{ $totalFailures }}</p>
                        <p class="text-xs text-gray-500">{{ __('reports/failure-analysis.in_selected_period') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Failure Cause -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 ease-in-out hover:shadow-lg transform hover:scale-[1.01]">
            <div class="flex items-center bg-gradient-to-r from-yellow-50 to-yellow-100 px-4 py-2 border-b border-gray-200">
                <i class="fas fa-exclamation-circle text-yellow-600 mr-2"></i>
                <h3 class="text-sm font-medium text-gray-700">{{ __('reports/failure-analysis.main_cause') }}</h3>
            </div>
            <div class="p-4">
                <div class="flex items-center">
                    <div class="rounded-full bg-yellow-100 p-3 mr-4 shadow-sm">
                        <i class="fas fa-exclamation-circle text-yellow-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gray-800 truncate">{{ $topFailureCause }}</p>
                        <p class="text-xs text-gray-500">{{ $topFailureCauseCount }} {{ __('reports/failure-analysis.occurrences') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Most Failing Equipment -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 ease-in-out hover:shadow-lg transform hover:scale-[1.01]">
            <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-2 border-b border-gray-200">
                <i class="fas fa-tools text-blue-600 mr-2"></i>
                <h3 class="text-sm font-medium text-gray-700">{{ __('reports/failure-analysis.most_failing_equipment') }}</h3>
            </div>
            <div class="p-4">
                <div class="flex items-center">
                    <div class="rounded-full bg-blue-100 p-3 mr-4 shadow-sm">
                        <i class="fas fa-tools text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gray-800 truncate">{{ $mostFailingEquipment }}</p>
                        <p class="text-xs text-gray-500">{{ $mostFailingEquipmentCount }} {{ __('reports/failure-analysis.failures') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Average Downtime -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 ease-in-out hover:shadow-lg transform hover:scale-[1.01]">
            <div class="flex items-center bg-gradient-to-r from-green-50 to-green-100 px-4 py-2 border-b border-gray-200">
                <i class="fas fa-clock text-green-600 mr-2"></i>
                <h3 class="text-sm font-medium text-gray-700">{{ __('reports/failure-analysis.average_downtime') }}</h3>
            </div>
            <div class="p-4">
                <div class="flex items-center">
                    <div class="rounded-full bg-green-100 p-3 mr-4 shadow-sm">
                        <i class="fas fa-clock text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-gray-800">{{ $averageDowntime }}</p>
                        <p class="text-xs text-gray-500">{{ __('reports/failure-analysis.hours_per_failure') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
        <!-- Chart 1: Failure Distribution by Category -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 ease-in-out hover:shadow-lg col-span-1">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
                <h2 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-chart-pie mr-2"></i>
                    {{ __('reports/failure-analysis.failure_causes_distribution') }}
                </h2>
            </div>
            <div class="p-3">
                <div style="height: 260px;">
                    <canvas id="causeCategoriesChart" wire:ignore></canvas>
                </div>
            </div>
        </div>

        <!-- Chart 2: Failures by Equipment -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 ease-in-out hover:shadow-lg col-span-1">
            <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-4 py-3">
                <h2 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-chart-bar mr-2"></i>
                    {{ __('reports/failure-analysis.failures_by_equipment') }}
                </h2>
            </div>
            <div class="p-3">
                <div style="height: 260px;">
                    <canvas id="failuresByEquipmentChart" wire:ignore></canvas>
                </div>
            </div>
        </div>

        <!-- Chart 3: Downtime Impact -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 ease-in-out hover:shadow-lg col-span-1">
            <div class="bg-gradient-to-r from-red-600 to-red-700 px-4 py-3">
                <h2 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-clock mr-2"></i>
                    {{ __('reports/failure-analysis.downtime_impact') }}
                </h2>
            </div>
            <div class="p-3">
                <div style="height: 260px;">
                    <canvas id="failureImpactChart" wire:ignore></canvas>
                </div>
            </div>
        </div>

        <!-- Chart 4: Failure Trend -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 ease-in-out hover:shadow-lg col-span-3">
            <div class="bg-gradient-to-r from-green-600 to-green-700 px-4 py-3">
                <h2 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-chart-line mr-2"></i>
                    {{ __('reports/failure-analysis.failure_trend_over_time') }}
                </h2>
            </div>
            <div class="p-3">
                <div style="height: 260px;">
                    <canvas id="failuresOverTimeChart" wire:ignore></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Failure Analysis Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 ease-in-out hover:shadow-lg mb-6">
        <!-- Cabeçalho da Tabela -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
            <h2 class="text-lg font-medium text-white flex items-center">
                <i class="fas fa-table mr-2"></i>
                {{ __('reports/failure-analysis.detailed_failure_records') }}
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center cursor-pointer" wire:click="sortBy('date')">
                                <i class="fas fa-calendar-day text-gray-400 mr-1"></i>
                                {{ __('reports/failure-analysis.date') }}
                                @if ($sortField === 'date')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-blue-500"></i>
                                @else
                                    <i class="fas fa-sort ml-1 opacity-50"></i>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center cursor-pointer" wire:click="sortBy('equipment')">
                                <i class="fas fa-cogs text-gray-400 mr-1"></i>
                                {{ __('reports/failure-analysis.equipment') }}
                                @if ($sortField === 'equipment')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-blue-500"></i>
                                @else
                                    <i class="fas fa-sort ml-1 opacity-50"></i>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center cursor-pointer" wire:click="sortBy('area')">
                                <i class="fas fa-map-marker-alt text-gray-400 mr-1"></i>
                                {{ __('reports/failure-analysis.area') }}
                                @if ($sortField === 'area')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-blue-500"></i>
                                @else
                                    <i class="fas fa-sort ml-1 opacity-50"></i>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center cursor-pointer" wire:click="sortBy('cause')">
                                <i class="fas fa-exclamation-circle text-gray-400 mr-1"></i>
                                {{ __('reports/failure-analysis.cause') }}
                                @if ($sortField === 'cause')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-blue-500"></i>
                                @else
                                    <i class="fas fa-sort ml-1 opacity-50"></i>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center cursor-pointer" wire:click="sortBy('mode')">
                                <i class="fas fa-wrench text-gray-400 mr-1"></i>
                                {{ __('reports/failure-analysis.mode') }}
                                @if ($sortField === 'mode')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-blue-500"></i>
                                @else
                                    <i class="fas fa-sort ml-1 opacity-50"></i>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center cursor-pointer" wire:click="sortBy('downtime')">
                                <i class="fas fa-clock text-gray-400 mr-1"></i>
                                {{ __('reports/failure-analysis.downtime') }}
                                @if ($sortField === 'downtime')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-blue-500"></i>
                                @else
                                    <i class="fas fa-sort ml-1 opacity-50"></i>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('reports/failure-analysis.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($this->getPaginatedRecords() as $item)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item['date'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <div class="font-medium">{{ $item['equipment'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item['area'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item['cause'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item['mode'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-semibold">
                                    {{ number_format($item['downtime'], 1) }} {{ __('reports/failure-analysis.hours') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="showFailureDetails({{ $item['id'] }})" 
                                    class="text-blue-600 hover:text-blue-900 transition-colors duration-150 transform hover:scale-110">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col items-center justify-center py-6 space-y-2">
                                    <div class="flex-shrink-0 bg-gray-100 p-3 rounded-full">
                                        <i class="fas fa-search text-gray-400 text-2xl"></i>
                                    </div>
                                    <p class="text-gray-500 text-sm">{{ __('reports/failure-analysis.no_records_found') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Paginação -->
        <div class="mt-4 px-6 py-3 flex items-center justify-between border-t border-gray-200">
            <div class="flex-1 flex justify-between sm:hidden">
                <button wire:click="previousPage" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    {{ __('reports/failure-analysis.previous') }}
                </button>
                <button wire:click="nextPage" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    {{ __('reports/failure-analysis.next') }}
                </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        {{ __('reports/failure-analysis.showing') }} <span class="font-medium">{{ count($this->getPaginatedRecords()) }}</span> {{ __('reports/failure-analysis.of') }} <span class="font-medium">{{ $totalFilteredFailures ?? count($this->getFilteredRecords()) }}</span> {{ __('reports/failure-analysis.results') }}
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="{{ __('reports/failure-analysis.pagination') }}">
                        <button wire:click="previousPage" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">{{ __('reports/failure-analysis.previous') }}</span>
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        
                        @php
                            $maxPage = ceil(($totalFilteredFailures ?? count($this->getFilteredRecords())) / $perPage);
                        @endphp
                        
                        @for ($i = 1; $i <= $maxPage; $i++)
                            <button wire:click="gotoPage({{ $i }})" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium {{ $i == $page ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:bg-gray-50' }}">
                                {{ $i }}
                            </button>
                        @endfor
                        
                        <button wire:click="nextPage" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">{{ __('reports/failure-analysis.next') }}</span>
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Failure Patterns Analysis -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 ease-in-out hover:shadow-lg mb-6">
        <!-- Cabeçalho da Seção -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-4 py-3">
            <h2 class="text-lg font-medium text-white flex items-center">
                <i class="fas fa-brain mr-2"></i>
                {{ __('reports/failure-analysis.identified_patterns') }}
            </h2>
        </div>

        <div class="p-6">
            <!-- Filtros para padrões -->
            <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between space-y-3 md:space-y-0 md:space-x-4 bg-gray-50 p-4 rounded-lg">
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-gray-700">{{ __('reports/failure-analysis.total_patterns_identified') }}: {{ $totalPatterns }}</h3>
                </div>
                <div class="flex space-x-2">
                    <select wire:model="patternPerPage" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="3">3 {{ __('reports/failure-analysis.per_page') }}</option>
                        <option value="5">5 {{ __('reports/failure-analysis.per_page') }}</option>
                        <option value="10">10 {{ __('reports/failure-analysis.per_page') }}</option>
                        <option value="20">20 {{ __('reports/failure-analysis.per_page') }}</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @forelse ($this->getPaginatedPatterns() as $pattern)
                <div class="mb-6 last:mb-0 border-b border-gray-200 pb-6 last:border-0">
                    <div class="flex items-center mb-3">
                        @if ($pattern['type'] === 'recurring_cause')
                            <i class="fas fa-repeat text-red-500 mr-2 text-lg"></i>
                        @elseif ($pattern['type'] === 'increasing_frequency')
                            <i class="fas fa-chart-line text-orange-500 mr-2 text-lg"></i>
                        @elseif ($pattern['type'] === 'area_wide_issue')
                            <i class="fas fa-map-marked-alt text-purple-500 mr-2 text-lg"></i>
                        @else
                            <i class="fas fa-exclamation-triangle text-yellow-500 mr-2 text-lg"></i>
                        @endif
                        <h4 class="text-lg font-semibold text-gray-800">
                            {{ $pattern['description'] }}
                        </h4>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-4 mb-4 border border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <h5 class="text-sm font-medium text-gray-600 mb-1 flex items-center">
                                    <i class="fas fa-exclamation-circle text-gray-400 mr-1"></i>
                                    Severidade
                                </h5>
                                <p class="text-sm">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full inline-flex items-center
                                        {{ $pattern['severity'] === 'high' ? 'text-white bg-red-500' :
                                        ($pattern['severity'] === 'medium' ? 'text-yellow-800 bg-yellow-100' : 'text-green-800 bg-green-100') }}">
                                        @if ($pattern['severity'] === 'high')
                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                            Alta
                                        @elseif ($pattern['severity'] === 'medium')
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Média
                                        @else
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Baixa
                                        @endif
                                    </span>
                                </p>
                            </div>
                            <div>
                                <h5 class="text-sm font-medium text-gray-600 mb-1 flex items-center">
                                    <i class="fas fa-map-marker-alt text-gray-400 mr-1"></i>
                                    Área
                                </h5>
                                <p class="text-sm text-gray-800">{{ $pattern['area'] ?? 'Não especificada' }}</p>
                            </div>
                            <div>
                                <h5 class="text-sm font-medium text-gray-600 mb-1 flex items-center">
                                    <i class="fas fa-cogs text-gray-400 mr-1"></i>
                                    Equipamento
                                </h5>
                                <p class="text-sm text-gray-800">{{ $pattern['equipment'] ?? 'Não especificado' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                        <h5 class="text-sm font-medium text-blue-700 mb-2 flex items-center">
                            <i class="fas fa-info-circle mr-1"></i>
                            Detalhes
                        </h5>
                        <p class="text-sm text-gray-700">{{ $pattern['details'] ?? $pattern['description'] }}</p>
                    </div>
                    
                    <div class="mt-4 flex justify-end">
                        <span class="text-xs text-gray-500 flex items-center">
                            <i class="fas fa-clock mr-1"></i>
                            {{ $pattern['count'] ?? 'Várias' }} ocorrências detectadas
                        </span>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-10 space-y-3 col-span-2">
                    <div class="flex-shrink-0 bg-gray-100 p-4 rounded-full">
                        <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                    </div>
                    <p class="text-gray-600">{{ __('reports/failure-analysis.no_patterns_found') }}</p>
                    <p class="text-sm text-gray-500">{{ __('reports/failure-analysis.try_expanding_period') }}</p>
                </div>
            @endforelse
        </div>
        
        <!-- Paginação para Padrões -->
        @if($totalPatterns > $patternPerPage)
            <div class="mt-6 bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 rounded-lg">
                <div class="flex-1 flex justify-between sm:hidden">
                    <button wire:click="previousPatternPage" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        {{ __('reports/failure-analysis.previous') }}
                    </button>
                    <button wire:click="nextPatternPage" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        {{ __('reports/failure-analysis.next') }}
                    </button>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            {{ __('reports/failure-analysis.showing') }} <span class="font-medium">{{ count($this->getPaginatedPatterns()) }}</span> {{ __('reports/failure-analysis.of') }} <span class="font-medium">{{ $totalPatterns }}</span> {{ __('reports/failure-analysis.patterns') }}
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Paginação">
                            <button wire:click="previousPatternPage" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Anterior</span>
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            
                            @php
                                $maxPage = ceil($totalPatterns / $patternPerPage);
                            @endphp
                            
                            @for ($i = 1; $i <= $maxPage; $i++)
                                <button wire:click="gotoPatternPage({{ $i }})" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium {{ $i == $patternPage ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:bg-gray-50' }}">
                                    {{ $i }}
                                </button>
                            @endfor
                            
                            <button wire:click="nextPatternPage" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Próximo</span>
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </nav>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Detail Modal -->
    @if($showDetailModal)
    <div x-data="{ open: true }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50" 
         role="dialog" 
         aria-modal="true"
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0"
         wire:click="closeModal">
        <div class="relative top-20 mx-auto p-1 w-full max-w-4xl">
            <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" 
                 x-transition:enter="transition ease-out duration-300" 
                 x-transition:enter-start="transform opacity-0 scale-95" 
                 x-transition:enter-end="transform opacity-100 scale-100" 
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="transform opacity-100 scale-100" 
                 x-transition:leave-end="transform opacity-0 scale-95"
                 wire:click.stop="">
                
                <!-- Cabeçalho com gradiente -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-cogs mr-2"></i>
                        Detalhes da Falha - {{ $selectedFailure['equipment'] }}
                    </h3>
                    <button type="button" wire:click="closeModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <!-- Corpo do modal com abas e cartões temáticos -->
                <div class="p-6">
                    <!-- Informações básicas -->
                    <div class="mb-6 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 transition-all duration-200 ease-in-out hover:shadow-md">
                        <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                            <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                            <h2 class="text-base font-medium text-gray-700">Informações Básicas</h2>
                     <!-- Filtros e Busca para Tabela -->
                    <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between space-y-3 md:space-y-0 md:space-x-4 bg-gray-50 p-4 rounded-lg">
                        <div class="flex-1">
                            <label for="search" class="sr-only">Buscar</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input type="text" wire:model.debounce.300ms="search" id="search" placeholder="Buscar por equipamento, causa ou área..." class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <select wire:model="perPage" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="10">10 por página</option>
                                <option value="25">25 por página</option>
                                <option value="50">50 por página</option>
                                <option value="100">100 por página</option>
                            </select>
                            <select wire:model="sortField" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="date">Ordenar por Data</option>
                                <option value="equipment">Ordenar por Equipamento</option>
                                <option value="area">Ordenar por Área</option>
                                <option value="downtime">Ordenar por Tempo</option>
                                <option value="cause">Ordenar por Causa</option>
                            </select>
                            <button wire:click="$set('sortDirection', sortDirection === 'asc' ? 'desc' : 'asc')" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fas fa-sort mr-1"></i>
                                {{ $sortDirection === 'asc' ? 'Asc' : 'Desc' }}
                            </button>
                        </div>
                    </div>
                    
                    <!-- Tabela de Registros -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200 divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Equipamento</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Área</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Causa</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modo</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tempo (h)</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($this->getPaginatedRecords() as $record)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $record['date'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $record['equipment'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $record['area'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $record['cause'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $record['mode'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $record['downtime'] }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full inline-flex items-center
                                                {{ $record['status'] === 'completed' ? 'text-green-700 bg-green-100' :
                                                   ($record['status'] === 'in_progress' ? 'text-yellow-700 bg-yellow-100' : 'text-red-700 bg-red-100') }}">
                                                {{ $record['status'] === 'completed' ? 'Concluído' : 
                                                   ($record['status'] === 'in_progress' ? 'Em Progresso' : 'Pendente') }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-indigo-600 hover:text-indigo-900">
                                            <button wire:click="showFailureDetails({{ $record['id'] }})" class="text-indigo-600 hover:text-indigo-900">
                                                <i class="fas fa-eye"></i> Detalhes
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-3 text-sm text-gray-500 text-center">Nenhum registro encontrado</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginação -->
                    <div class="mt-4 bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 rounded-lg">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <button wire:click="previousPage" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Anterior
                            </button>
                            <button wire:click="nextPage" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Próximo
                            </button>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Mostrando <span class="font-medium">{{ count($this->getPaginatedRecords()) }}</span> de <span class="font-medium">{{ $totalFilteredFailures ?? $totalFailures }}</span> resultados
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Paginação">
                                    <button wire:click="previousPage" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                        <span class="sr-only">Anterior</span>
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    
                                    <!-- Renderizar links de página com base no total de páginas -->
                                    @for ($i = 1; $i <= ceil($totalFailures / $perPage); $i++)
                                        <button wire:click="gotoPage({{ $i }})" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium {{ $i == $page ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:bg-gray-50' }}">
                                            {{ $i }}
                                        </button>
                                    @endfor
                                    
                                    <button wire:click="nextPage" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                        <span class="sr-only">Próximo</span>
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </nav>
                            </div>
                        </div>
                    </div>span class="w-1/3 text-sm font-medium text-gray-500 flex items-center">
                                            <i class="fas fa-user text-gray-400 mr-1"></i>
                                            Reportado por:
                                        </span>
                                        <span class="w-2/3 text-sm text-gray-900">{{ $selectedFailure['reported_by'] }}</span>
                                    </div>
{{ ... }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Avaliação de Impacto -->
                    <div class="mb-6 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 transition-all duration-200 ease-in-out hover:shadow-md">
                        <div class="flex items-center bg-gradient-to-r from-red-50 to-red-100 px-4 py-3 border-b border-gray-200">
                            <i class="fas fa-exclamation-circle text-red-600 mr-2"></i>
                            <h2 class="text-base font-medium text-gray-700">Avaliação de Impacto</h2>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex items-center">
                                    <span class="w-1/3 text-sm font-medium text-gray-500 flex items-center">
                                        <i class="fas fa-clock text-gray-400 mr-1"></i>
                                        Tempo Parado:
                                    </span>
                                    <span class="w-2/3 text-sm text-gray-900">
                                        <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-semibold">
                                            {{ $selectedFailure['downtime'] }} horas
                                        </span>
                                    </span>
                                </div>
                                <div class="flex items-center">
                                    <span class="w-1/3 text-sm font-medium text-gray-500 flex items-center">
                                        <i class="fas fa-tasks text-gray-400 mr-1"></i>
                                        Status:
                                    </span>
                                    <span class="w-2/3 text-sm text-gray-900">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full inline-flex items-center
                                            {{ $selectedFailure['status'] === 'resolved' ? 'text-green-700 bg-green-100' :
                                              ($selectedFailure['status'] === 'in_progress' ? 'text-yellow-700 bg-yellow-100' : 'text-red-700 bg-red-100') }}">
                                            @if($selectedFailure['status'] === 'resolved')
                                                <i class="fas fa-check-circle mr-1"></i>
                                            @elseif($selectedFailure['status'] === 'in_progress')
                                                <i class="fas fa-spinner mr-1"></i>
                                            @else
                                                <i class="fas fa-exclamation-circle mr-1"></i>
                                            @endif
                                            {{ $selectedFailure['status'] === 'resolved' ? 'Resolvido' : 
                                              ($selectedFailure['status'] === 'in_progress' ? 'Em Andamento' : 'Pendente') }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Causa Raiz -->
                    <div class="mb-6 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 transition-all duration-200 ease-in-out hover:shadow-md">
                        <div class="flex items-center bg-gradient-to-r from-yellow-50 to-yellow-100 px-4 py-3 border-b border-gray-200">
                            <i class="fas fa-search text-yellow-600 mr-2"></i>
                            <h2 class="text-base font-medium text-gray-700">Causa Raiz</h2>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex items-center">
                                    <span class="w-1/3 text-sm font-medium text-gray-500 flex items-center">
                                        <i class="fas fa-wrench text-gray-400 mr-1"></i>
                                        Modo de Falha:
                                    </span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $selectedFailure['mode'] }}</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="w-1/3 text-sm font-medium text-gray-500 flex items-center">
                                        <i class="fas fa-exclamation-triangle text-gray-400 mr-1"></i>
                                        Causa da Falha:
                                    </span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $selectedFailure['cause'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Descrição -->
                    <div class="mb-6 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 transition-all duration-200 ease-in-out hover:shadow-md">
                        <div class="flex items-center bg-gradient-to-r from-green-50 to-green-100 px-4 py-3 border-b border-gray-200">
                            <i class="fas fa-file-alt text-green-600 mr-2"></i>
                            <h2 class="text-base font-medium text-gray-700">Descrição</h2>
                        </div>
                        <div class="p-4">
                            <p class="text-sm text-gray-700">{{ $selectedFailure['description'] }}</p>
                        </div>
                    </div>

                    <!-- Ações Tomadas -->
                    <div class="mb-6 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 transition-all duration-200 ease-in-out hover:shadow-md">
                        <div class="flex items-center bg-gradient-to-r from-purple-50 to-purple-100 px-4 py-3 border-b border-gray-200">
                            <i class="fas fa-tools text-purple-600 mr-2"></i>
                            <h2 class="text-base font-medium text-gray-700">Ações Tomadas</h2>
                        </div>
                        <div class="p-4">
                            <p class="text-sm text-gray-700">{{ $selectedFailure['actions_taken'] ?? 'Nenhuma ação registrada' }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Botões de Ação -->
                <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                    <button type="button" wire:click="closeModal" class="inline-flex justify-center items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <i class="fas fa-times mr-2"></i>
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    // Initialize charts on page load and when Livewire updates
    document.addEventListener('livewire:initialized', initCharts);
    document.addEventListener('livewire:update', initCharts);
    document.addEventListener('DOMContentLoaded', initCharts); // Redundante mas garante a inicialização

    // Adicionar listener para atualização específica de dados do relatório
    window.addEventListener('reportDataUpdated', initCharts);

    function initCharts() {
        console.log('Inicializando gráficos de análise de falhas...');
        
        // Initialize Failure Causes Chart
        const causesCtx = document.getElementById('failureCausesChart');
        if (causesCtx) {
            // Check if chart already exists and destroy it
            if (window.failureCausesChart instanceof Chart) {
                window.failureCausesChart.destroy();
            }

            window.failureCausesChart = new Chart(causesCtx, {
                type: 'pie',
                data: @json($failureCausesData),
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                    }
                }
            });
        }

        // Initialize Failures by Equipment Chart
        const equipmentCtx = document.getElementById('failuresByEquipmentChart');
        if (equipmentCtx) {
            // Check if chart already exists and destroy it
            if (window.failuresByEquipmentChart instanceof Chart) {
                window.failuresByEquipmentChart.destroy();
            }

            window.failuresByEquipmentChart = new Chart(equipmentCtx, {
                type: 'bar',
                data: @json($failuresByEquipmentData),
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Failures'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Equipment'
                            }
                        }
                    }
                }
            });
        }

        // Initialize Failures Over Time Chart
        const timeCtx = document.getElementById('failuresOverTimeChart');
        if (timeCtx) {
            // Check if chart already exists and destroy it
            if (window.failuresOverTimeChart instanceof Chart) {
                window.failuresOverTimeChart.destroy();
            }

            window.failuresOverTimeChart = new Chart(timeCtx, {
                type: 'line',
                data: @json($failuresOverTimeData),
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Failures'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        }
                    }
                }
            });
        }

        // Initialize Failure Impact Chart
        const impactCtx = document.getElementById('failureImpactChart');
        if (impactCtx) {
            // Check if chart already exists and destroy it
            if (window.failureImpactChart instanceof Chart) {
                window.failureImpactChart.destroy();
            }

            window.failureImpactChart = new Chart(impactCtx, {
                type: 'bar',
                data: @json($failureImpactData),
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Downtime (Hours)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Equipment'
                            }
                        }
                    }
                }
            });
        }
    }

    // Initialize Category Distribution Charts
    const modeCategoryCtx = document.getElementById('modeCategoriesChart');
    if (modeCategoryCtx && @json($categoriesDistributionData) && typeof @json($categoriesDistributionData) === 'object' && @json($categoriesDistributionData)['mode']) {
        // Check if chart already exists and destroy it
        if (window.modeCategoriesChart instanceof Chart) {
            window.modeCategoriesChart.destroy();
        }

        window.modeCategoriesChart = new Chart(modeCategoryCtx, {
            type: 'doughnut',
            data: @json($categoriesDistributionData['mode']),
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                }
            }
        });
    }

    const causeCategoryCtx = document.getElementById('causeCategoriesChart');
    if (causeCategoryCtx && @json($categoriesDistributionData) && typeof @json($categoriesDistributionData) === 'object' && @json($categoriesDistributionData)['cause']) {
        // Check if chart already exists and destroy it
        if (window.causeCategoriesChart instanceof Chart) {
            window.causeCategoriesChart.destroy();
        }

        window.causeCategoriesChart = new Chart(causeCategoryCtx, {
            type: 'doughnut',
            data: @json($categoriesDistributionData['cause']),
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                }
            }
        });
    }
</script>
@endpush

