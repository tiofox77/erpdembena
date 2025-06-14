<div>
    <!-- Cabeçalho da Página -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 inline-flex items-center">
                <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>
                {{ __('messages.production_scheduling') }}
            </h1>
            <p class="mt-1 text-sm text-gray-500">{{ __('messages.production_scheduling_description') }}</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-2">
            @can('production_scheduling.create')
                <button type="button" wire:click="openCreateModal" 
                    class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                    <i class="fas fa-plus mr-2"></i>
                    {{ __('messages.add_production_schedule') }}
                </button>
            @else
                <button type="button" disabled
                    class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gray-400 cursor-not-allowed opacity-75"
                    title="{{ __('messages.no_permission') }}">
                    <i class="fas fa-ban mr-2"></i>
                    {{ __('messages.add_production_schedule') }}
                </button>
            @endcan
        </div>
    </div>
    
    <!-- Cartão de Filtros -->
    <div class="bg-white rounded-lg shadow-md mb-6 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-4 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center">
                <i class="fas fa-filter mr-2"></i>
                {{ __('messages.filter_schedules') }}
            </h2>
        </div>
        
        <div class="p-5">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Campo de Busca -->
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('messages.search') }}
                    </label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" wire:model.live.debounce.300ms="search" id="search" 
                            class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md"
                            placeholder="{{ __('messages.search_schedules_placeholder') }}">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            @if($search)
                                <button wire:click="$set('search', '')" class="text-gray-400 hover:text-gray-500">
                                    <i class="fas fa-times"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">{{ __('messages.search_schedules_help') }}</p>
                </div>
                
                <!-- Filtros de data removidos -->
                
                <!-- Filtro por Status -->
                <div>
                    <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('messages.status') }}
                    </label>
                    <select id="statusFilter" wire:model.live="statusFilter" 
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="">{{ __('messages.all_statuses') }}</option>
                        <option value="draft">{{ __('messages.draft') }}</option>
                        <option value="confirmed">{{ __('messages.confirmed') }}</option>
                        <option value="in_progress">{{ __('messages.in_progress') }}</option>
                        <option value="completed">{{ __('messages.completed') }}</option>
                        <option value="cancelled">{{ __('messages.cancelled') }}</option>
                    </select>
                </div>
                
                <!-- Filtro por Produto -->
                <div>
                    <label for="productFilter" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('messages.product') }}
                    </label>
                    <select id="productFilter" wire:model.live="productFilter" 
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="">{{ __('messages.all_products') }}</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Visualizar como -->
                <div>
                    <label for="viewType" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('messages.view_as') }}
                    </label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <button type="button" wire:click="$set('viewType', 'table')" 
                            class="inline-flex items-center py-2 px-4 border border-r-0 border-gray-300 
                            {{ $viewType === 'table' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700' }} 
                            text-sm font-medium rounded-l-md hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                            <i class="fas fa-list mr-2"></i>
                            {{ __('messages.table') }}
                        </button>
                        <button type="button" wire:click="$set('viewType', 'calendar')" 
                            class="inline-flex items-center py-2 px-4 border border-gray-300 
                            {{ $viewType === 'calendar' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700' }} 
                            text-sm font-medium rounded-r-md hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            {{ __('messages.calendar') }}
                        </button>
                    </div>
                </div>
                
                <!-- Botões de Ação -->
                <div class="flex items-end gap-2">
                    <!-- Botão Gerar PDF -->
                    @can('production_scheduling.export_pdf')
                        <button type="button" wire:click="generatePdfList" 
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <i class="fas fa-file-pdf mr-2"></i>
                            {{ __('messages.generate_list_pdf') }}
                        </button>
                    @else
                        <button type="button" disabled
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gray-400 cursor-not-allowed opacity-75"
                            title="{{ __('messages.no_permission') }}">
                            <i class="fas fa-ban mr-2"></i>
                            {{ __('messages.generate_list_pdf') }}
                        </button>
                    @endcan
                    
                    <!-- Limpar Filtros -->
                    <button type="button" wire:click="resetFilters" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-undo mr-2"></i>
                        {{ __('messages.reset_filters') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Visualização em Tabela -->
    @if($viewType === 'table')
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-4 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-white flex items-center">
                    <i class="fas fa-list mr-2"></i>
                    {{ __('messages.production_schedules_list') }}
                </h2>
                <div class="flex items-center space-x-2">
                    <select wire:model.live="perPage" class="border-0 bg-blue-600 text-white text-sm rounded-md focus:outline-none focus:ring-0">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th wire:click="sortBy('schedule_number')" class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors duration-200">
                                <div class="flex items-center">
                                    {{ __('messages.schedule_number') }}
                                    @if($sortField === 'schedule_number')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-2 text-blue-500"></i>
                                    @else
                                        <i class="fas fa-sort ml-2 text-gray-400"></i>
                                    @endif
                                </div>
                            </th>
                            <th wire:click="sortBy('product_id')" class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors duration-200">
                                <div class="flex items-center">
                                    {{ __('messages.product') }}
                                    @if($sortField === 'product_id')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-2 text-blue-500"></i>
                                    @else
                                        <i class="fas fa-sort ml-2 text-gray-400"></i>
                                    @endif
                                </div>
                            </th>
                            <th wire:click="sortBy('responsible_id')" class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors duration-200">
                                <div class="flex items-center">
                                    {{ __('messages.responsible') }}
                                    @if($sortField === 'responsible_id')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-2 text-blue-500"></i>
                                    @else
                                        <i class="fas fa-sort ml-2 text-gray-400"></i>
                                    @endif
                                </div>
                            </th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    {{ __('messages.shifts') }}
                                </div>
                            </th>
                            <th wire:click="sortBy('quantity')" class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors duration-200">
                                <div class="flex items-center justify-end">
                                    {{ __('messages.quantity') }}
                                    @if($sortField === 'quantity')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-2 text-blue-500"></i>
                                    @else
                                        <i class="fas fa-sort ml-2 text-gray-400"></i>
                                    @endif
                                </div>
                            </th>
                            <th wire:click="sortBy('status')" class="px-6 py-3 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors duration-200">
                                <div class="flex items-center justify-center">
                                    {{ __('messages.status') }}
                                    @if($sortField === 'status')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-2 text-blue-500"></i>
                                    @else
                                        <i class="fas fa-sort ml-2 text-gray-400"></i>
                                    @endif
                                </div>
                            </th>
                            <th class="px-6 py-3 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('messages.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($schedules as $schedule)
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                                    {{ $schedule->schedule_number }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-box text-gray-500"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $schedule->product->name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $schedule->product->sku }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($schedule->responsible)
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-user text-blue-500"></i>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">{{ $schedule->responsible->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $schedule->responsible->position }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-gray-400 italic">{{ __('messages.not_assigned') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($schedule->shifts as $shift)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                                <i class="fas fa-clock mr-1"></i> {{ $shift->name }}
                                            </span>
                                        @empty
                                            <span class="text-gray-400 italic">{{ __('messages.no_shifts_assigned') }}</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                    @php
                                        // Calcular a soma das quantidades reais dos planos diários
                                        $actualQuantity = $schedule->dailyPlans->sum('actual_quantity');
                                    @endphp
                                    {{ number_format($actualQuantity, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium transition-all duration-300
                                        {{ $schedule->status === 'draft' ? 'bg-gray-100 text-gray-800 hover:bg-gray-200' : 
                                           ($schedule->status === 'confirmed' ? 'bg-blue-100 text-blue-800 hover:bg-blue-200 hover:shadow' : 
                                           ($schedule->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200 shadow-md hover:shadow-lg' : 
                                           ($schedule->status === 'completed' ? 'bg-green-100 text-green-800 hover:bg-green-200 hover:shadow' : 'bg-red-100 text-red-800 hover:bg-red-200'))) }}">
                                        <i class="fas 
                                            {{ $schedule->status === 'draft' ? 'fa-pencil-alt' : 
                                               ($schedule->status === 'confirmed' ? 'fa-check-circle fa-beat-fade' : 
                                               ($schedule->status === 'in_progress' ? 'fa-hourglass-half fa-spin-pulse' : 
                                               ($schedule->status === 'completed' ? 'fa-flag-checkered fa-bounce' : 'fa-ban fa-shake'))) }} mr-1"></i>
                                        <span class="{{ $schedule->status === 'in_progress' ? 'animate-pulse font-bold' : '' }}">
                                            {{ __('messages.' . $schedule->status) }}
                                        </span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @can('production_scheduling.view_orders')
                                        <button wire:click="viewOrders({{ $schedule->id }})" class="text-green-600 hover:text-green-900 mr-3 transition-colors duration-200" title="{{ __('messages.view_orders') }}">
                                            <i class="fas fa-list-alt"></i>
                                        </button>
                                    @else
                                        <button disabled class="text-gray-400 mr-3 cursor-not-allowed" title="{{ __('messages.no_permission') }}">
                                            <i class="fas fa-list-alt"></i>
                                        </button>
                                    @endcan
                                    
                                    @can('production_scheduling.view')
                                        <button wire:click="view({{ $schedule->id }})" class="text-blue-600 hover:text-blue-900 mr-3 transition-colors duration-200" title="{{ __('messages.view_details') }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @else
                                        <button disabled class="text-gray-400 mr-3 cursor-not-allowed" title="{{ __('messages.no_permission') }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @endcan
                                    
                                    @can('production_scheduling.view_daily_plans')
                                        <button wire:click="viewDailyPlans({{ $schedule->id }})" class="text-teal-600 hover:text-teal-900 mr-3 transition-colors duration-200" title="{{ __('messages.view_daily_plans') }}">
                                            <i class="fas fa-calendar-check"></i>
                                        </button>
                                    @else
                                        <button disabled class="text-gray-400 mr-3 cursor-not-allowed" title="{{ __('messages.no_permission') }}">
                                            <i class="fas fa-calendar-check"></i>
                                        </button>
                                    @endcan
                                    
                                    @can('production_scheduling.edit')
                                        <button wire:click="edit({{ $schedule->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3 transition-colors duration-200" title="{{ __('messages.edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @else
                                        <button disabled class="text-gray-400 mr-3 cursor-not-allowed" title="{{ __('messages.no_permission') }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @endcan
                                    
                                    @can('production_scheduling.export_pdf')
                                        <button wire:click="generateSchedulePdf({{ $schedule->id }})" class="text-orange-600 hover:text-orange-900 mr-3 transition-colors duration-200 transform hover:scale-110" title="{{ __('messages.generate_pdf') }}">
                                            <i class="fas fa-file-pdf"></i>
                                        </button>
                                    @else
                                        <button disabled class="text-gray-400 mr-3 cursor-not-allowed" title="{{ __('messages.no_permission') }}">
                                            <i class="fas fa-file-pdf"></i>
                                        </button>
                                    @endcan
                                    
                                    @can('production_scheduling.delete')
                                        <button wire:click="openDeleteModal({{ $schedule->id }})" class="text-red-600 hover:text-red-900 transition-colors duration-200 transform hover:scale-110" title="{{ __('messages.delete') }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    @else
                                        <button disabled class="text-gray-400 cursor-not-allowed" title="{{ __('messages.no_permission') }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center py-6">
                                        <i class="fas fa-calendar-times text-4xl text-gray-300 mb-2"></i>
                                        <p>{{ __('messages.no_schedules_found') }}</p>
                                        @can('production_scheduling.create')
                                        <button wire:click="openCreateModal" class="mt-3 inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                            <i class="fas fa-plus mr-1"></i>
                                            {{ __('messages.add_production_schedule') }}
                                        </button>
                                        @else
                                        <button disabled class="mt-3 inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-gray-400 cursor-not-allowed opacity-75">
                                            <i class="fas fa-ban mr-1"></i>
                                            {{ __('messages.add_production_schedule') }}
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6" wire:key="pagination-container-{{ now() }}">
                <div class="pagination-wrapper">
                    {{ $schedules->links() }}
                </div>
            </div>
        </div>
    
    <!-- Visualização em Calendário -->
    @else
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-4 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-white flex items-center">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    {{ __('messages.production_calendar') }}
                </h2>
                <div class="flex items-center space-x-2">
                    <button type="button" wire:click="previousMonth" class="text-white hover:text-gray-200 focus:outline-none">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <span class="text-white font-medium">{{ $calendarTitle }}</span>
                    <button type="button" wire:click="nextMonth" class="text-white hover:text-gray-200 focus:outline-none">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    <button type="button" wire:click="loadCalendarEvents" class="ml-4 bg-white text-blue-600 hover:bg-blue-50 px-2 py-1 rounded-md text-xs font-medium focus:outline-none" title="Atualizar calendário">
                        <i class="fas fa-sync-alt mr-1"></i> Atualizar
                    </button>
                </div>
            </div>
            
            <div class="p-4">
                <!-- Grid do Calendário -->
                <div class="grid grid-cols-7 gap-px bg-gray-200">
                    <!-- Cabeçalho dos Dias da Semana -->
                    @foreach($calendarDayNames as $dayName)
                        <div class="bg-gray-100 px-2 py-3 text-center text-sm font-medium text-gray-500">
                            {{ $dayName }}
                        </div>
                    @endforeach
                    
                    <!-- Dias do Calendário por semana -->
                    @foreach($calendarWeeks as $week)
                        @foreach($week as $day)
                        <div class="bg-white min-h-[120px] p-2
                            {{ !$day['isCurrentMonth'] ? 'bg-gray-50 text-gray-400' : '' }}
                            {{ $day['isToday'] ? 'bg-blue-50 border border-blue-200' : '' }}">
                            <!-- Número do Dia -->
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium {{ $day['isToday'] ? 'text-blue-600' : 'text-gray-700' }}">
                                    {{ $day['day'] }}
                                </span>
                                @if($day['isCurrentMonth'])
                                    @can('production_scheduling.create')
                                        <button wire:click="openCreateModalForDate('{{ $day['date'] }}')" class="text-blue-600 hover:text-blue-800" title="{{ __('messages.add_schedule') }}">
                                            <i class="fas fa-plus-circle text-xs"></i>
                                        </button>
                                    @else
                                        <button disabled class="text-gray-400 cursor-not-allowed" title="{{ __('messages.no_permission') }}">
                                            <i class="fas fa-plus-circle text-xs"></i>
                                        </button>
                                    @endcan
                                @endif
                            </div>
                            
                            <!-- Eventos do Dia -->
                            <div class="mt-1 space-y-1 max-h-24 overflow-y-auto">
                                @foreach($day['events'] ?? [] as $event)
                                    <div wire:click="view({{ $event['id'] }})" 
                                        class="p-1 text-xs rounded truncate cursor-pointer
                                        {{ $event['status'] === 'draft' ? 'bg-gray-100 text-gray-800' : 
                                           ($event['status'] === 'confirmed' ? 'bg-blue-100 text-blue-800' : 
                                           ($event['status'] === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 
                                           ($event['status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'))) }}">
                                        {{ $event['title'] }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    @endforeach
                </div>
                
                <!-- Legenda do Calendário -->
                <div class="mt-4 flex flex-wrap gap-3">
                    <span class="inline-flex items-center text-xs">
                        <span class="w-3 h-3 inline-block bg-gray-100 mr-1"></span>
                        {{ __('messages.draft') }}
                    </span>
                    <span class="inline-flex items-center text-xs">
                        <span class="w-3 h-3 inline-block bg-blue-100 mr-1"></span>
                        {{ __('messages.confirmed') }}
                    </span>
                    <span class="inline-flex items-center text-xs">
                        <span class="w-3 h-3 inline-block bg-yellow-100 mr-1"></span>
                        {{ __('messages.in_progress') }}
                    </span>
                    <span class="inline-flex items-center text-xs">
                        <span class="w-3 h-3 inline-block bg-green-100 mr-1"></span>
                        {{ __('messages.completed') }}
                    </span>
                    <span class="inline-flex items-center text-xs">
                        <span class="w-3 h-3 inline-block bg-red-100 mr-1"></span>
                        {{ __('messages.cancelled') }}
                    </span>
                </div>
            </div>
        </div>
    @endif
    
    <!-- Modais -->
    <div>
        @include('livewire.mrp.production-scheduling.create-edit-modal')
        @include('livewire.mrp.production-scheduling.delete-modal')
        @include('livewire.mrp.production-scheduling.view-modal')
        @include('livewire.mrp.production-scheduling.daily-plans-modal')
        @include('livewire.mrp.production-scheduling.orders-modal')
    </div>
</div>
