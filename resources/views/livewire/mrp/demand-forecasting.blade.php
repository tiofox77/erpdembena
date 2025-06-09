<div>
    <div class="container mx-auto px-4 py-6">
        <!-- Cabeçalho Principal -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
            <h1 class="text-2xl font-semibold text-gray-800 flex items-center">
                <i class="fas fa-chart-line text-blue-600 mr-3"></i>
                {{ __('messages.demand_forecasting') }}
            </h1>
            @can('demand_forecast.create')
            <button wire:click="create" 
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 hover:shadow-lg"
                title="{{ __('messages.create_forecast') }}">
                <i class="fas fa-plus-circle mr-2 animate-pulse"></i>
                {{ __('messages.add_forecast') }}
            </button>
            @else
            <button class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-gray-500 cursor-not-allowed opacity-70"
                title="{{ __('messages.no_permission_to_add_forecast') }}">
                <i class="fas fa-plus-circle mr-2"></i>
                {{ __('messages.add_forecast') }}
            </button>
            @endcan
        </div>

        <!-- Cartão de Busca e Filtros -->
        <div class="mb-6 bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 ease-in-out hover:shadow-lg transform hover:scale-[1.01]">
            <!-- Cabeçalho do cartão com gradiente -->
            <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                <i class="fas fa-filter text-blue-600 mr-2"></i>
                <h2 class="text-base font-medium text-gray-700">{{ __('messages.filters_and_search') }}</h2>
            </div>
            <!-- Conteúdo do cartão -->
            <div class="p-4">
                <div class="flex flex-col gap-4">
                    <!-- Campo de busca -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-search text-gray-500 mr-1"></i>
                            {{ __('messages.search') }}
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input wire:model.debounce.300ms="search" id="search" 
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out" 
                                placeholder="{{ __('messages.search_forecast_placeholder') }}" 
                                type="search">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">{{ __('messages.search_forecast_help') }}</p>
                    </div>
                    
                    <!-- Linha de filtros -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Filtro de Produto -->
                        <div>
                            <label for="productFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-box text-gray-500 mr-1"></i>
                                {{ __('messages.product') }}
                            </label>
                            <select wire:model="productFilter" id="productFilter" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="">{{ __('messages.all_products') }}</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Filtro de Data -->
                        <div>
                            <label for="dateFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-calendar-day text-gray-500 mr-1"></i>
                                {{ __('messages.forecast_date') }}
                            </label>
                            <input wire:model="dateFilter" id="dateFilter" type="date" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                        </div>
                        
                        <!-- Filtro de Tipo -->
                        <div>
                            <label for="typeFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-tag text-gray-500 mr-1"></i>
                                {{ __('messages.forecast_type') }}
                            </label>
                            <select wire:model="typeFilter" id="typeFilter" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="">{{ __('messages.all_types') }}</option>
                                @foreach($forecastTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Registros por página -->
                        <div>
                            <label for="perPage" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-list-ol text-gray-500 mr-1"></i>
                                {{ __('messages.items_per_page') }}
                            </label>
                            <select wire:model="perPage" id="perPage" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Botão de reset -->
                    <div class="flex justify-end">
                        <button wire:click="resetFilters" 
                            class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md text-sm font-medium text-gray-700 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                            <i class="fas fa-redo-alt mr-2"></i>
                            {{ __('messages.reset_filters') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela de Previsões de Demanda -->
        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden transition-all duration-300 ease-in-out hover:shadow-lg">
            <!-- Cabeçalho da Tabela -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
                <h2 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-chart-line mr-2"></i>
                    {{ __('messages.forecasts_list') }}
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('product_id')">
                                    {{ __('messages.product') }}
                                    @if($sortField === 'product_id')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 opacity-50"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('forecast_date')">
                                    {{ __('messages.forecast_date') }}
                                    @if($sortField === 'forecast_date')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 opacity-50"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('forecast_quantity')">
                                    {{ __('messages.forecast_quantity') }}
                                    @if($sortField === 'forecast_quantity')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 opacity-50"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('confidence_level')">
                                    {{ __('messages.confidence_level') }}
                                    @if($sortField === 'confidence_level')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 opacity-50"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('forecast_type')">
                                    {{ __('messages.forecast_type') }}
                                    @if($sortField === 'forecast_type')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 opacity-50"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('messages.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($forecasts as $forecast)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $forecast->product->name ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ $forecast->product->sku ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ optional($forecast->forecast_date)->format('d/m/Y') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ number_format($forecast->forecast_quantity, 2) }}
                                    </div>
                                    @if($forecast->actual_quantity)
                                        <div class="text-xs text-gray-500">
                                            {{ __('messages.actual') }}: {{ number_format($forecast->actual_quantity, 2) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($forecast->confidence_level)
                                        <div class="relative">
                                            <div class="flex items-center">
                                                <div class="w-full bg-gray-200 rounded-full h-2">
                                                    <div class="h-2 rounded-full 
                                                        @if($forecast->confidence_level < 50) bg-red-500
                                                        @elseif($forecast->confidence_level < 75) bg-yellow-500
                                                        @else bg-green-500
                                                        @endif" 
                                                        style="width: {{ $forecast->confidence_level }}%">
                                                    </div>
                                                </div>
                                                <span class="ml-2 text-xs font-medium text-gray-700">{{ $forecast->confidence_level }}%</span>
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-sm text-gray-500">--</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($forecast->forecast_type === 'manual') bg-blue-100 text-blue-800
                                        @elseif($forecast->forecast_type === 'automatic') bg-green-100 text-green-800
                                        @else bg-purple-100 text-purple-800
                                        @endif">
                                        <i class="mr-1
                                            @if($forecast->forecast_type === 'manual') fas fa-user
                                            @elseif($forecast->forecast_type === 'automatic') fas fa-robot
                                            @else fas fa-pen-fancy
                                            @endif"></i>
                                        {{ $forecastTypes[$forecast->forecast_type] ?? $forecast->forecast_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <!-- View Button -->
                                        @can('demand_forecast.view', $forecast)
                                        <button wire:click="view({{ $forecast->id }})" 
                                            class="text-blue-600 hover:text-blue-900 transition-colors duration-150 transform hover:scale-110" 
                                            title="{{ __('messages.view_details') }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @else
                                        <span class="text-gray-400 cursor-not-allowed" 
                                            title="{{ __('messages.no_view_permission') }}">
                                            <i class="fas fa-eye"></i>
                                        </span>
                                        @endcan

                                        <!-- Edit Button -->
                                        @can('demand_forecast.edit', $forecast)
                                        <button wire:click="edit({{ $forecast->id }})" 
                                            class="text-indigo-600 hover:text-indigo-900 transition-colors duration-150 transform hover:scale-110" 
                                            title="{{ __('messages.edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @else
                                        <span class="text-gray-400 cursor-not-allowed" 
                                            title="{{ __('messages.no_edit_permission') }}">
                                            <i class="fas fa-edit"></i>
                                        </span>
                                        @endcan

                                        <!-- Delete Button -->
                                        @can('demand_forecast.delete', $forecast)
                                        <button wire:click="confirmDelete({{ $forecast->id }})" 
                                            class="text-red-600 hover:text-red-900 transition-colors duration-150 transform hover:scale-110" 
                                            title="{{ __('messages.delete') }}"
                                            onclick="return confirm('{{ __('messages.confirm_delete_forecast') }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @else
                                        <span class="text-gray-400 cursor-not-allowed" 
                                            title="{{ __('messages.no_delete_permission') }}">
                                            <i class="fas fa-trash"></i>
                                        </span>
                                        @endcan
                                        
                                        <!-- Export Button -->
                                        @can('demand_forecast.export', $forecast)
                                        <div x-data="{ open: false }" class="relative">
                                            <button @click="open = !open" 
                                                class="text-green-600 hover:text-green-900 transition-colors duration-150 transform hover:scale-110"
                                                :class="{ 'text-green-700': open }"
                                                title="{{ __('messages.export') }}">
                                                <i class="fas fa-download"></i>
                                            </button>
                                            <div x-show="open" @click.away="open = false" 
                                                class="origin-top-right absolute right-0 mt-2 w-40 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10"
                                                x-transition:enter="transition ease-out duration-100"
                                                x-transition:enter-start="transform opacity-0 scale-95"
                                                x-transition:enter-end="transform opacity-100 scale-100"
                                                x-transition:leave="transition ease-in duration-75"
                                                x-transition:leave-start="transform opacity-100 scale-100"
                                                x-transition:leave-end="transform opacity-0 scale-95">
                                                <div class="py-1">
                                                    <button wire:click="exportToPdf({{ $forecast->id }})" 
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                        title="{{ __('messages.export_to_pdf') }}">
                                                        <i class="fas fa-file-pdf mr-2 text-red-500"></i> {{ __('messages.export_pdf') }}
                                                    </button>
                                                    <button wire:click="exportToExcel({{ $forecast->id }})" 
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                        title="{{ __('messages.export_to_excel') }}">
                                                        <i class="fas fa-file-excel mr-2 text-green-600"></i> {{ __('messages.export_excel') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col items-center justify-center py-6 space-y-2">
                                        <div class="flex-shrink-0 bg-gray-100 p-3 rounded-full">
                                            <i class="fas fa-chart-area text-gray-400 text-2xl"></i>
                                        </div>
                                        <p class="text-gray-500 text-sm">{{ __('messages.no_forecasts_found') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <div class="px-4 py-3 bg-white border-t border-gray-200">
                {{ $forecasts->links() }}
            </div>
        </div>
    </div>

    <!-- Modais (incluindo os arquivos separados) -->
    @include('livewire.mrp.demand-forecasting.create-edit-modal')
    @include('livewire.mrp.demand-forecasting.delete-modal')
</div>
