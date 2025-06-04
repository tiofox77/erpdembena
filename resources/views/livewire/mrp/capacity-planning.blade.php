<div>
    <div class="container mx-auto px-4 py-6">
        <!-- Cabeçalho Principal -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
            <h1 class="text-2xl font-semibold text-gray-800 flex items-center">
                <i class="fas fa-calendar-alt text-blue-600 mr-3"></i>
                {{ __('messages.capacity_planning') }}
            </h1>
            <button wire:click="create" 
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 hover:shadow-lg">
                <i class="fas fa-plus-circle mr-2 animate-pulse"></i>
                {{ __('messages.add_capacity_plan') }}
            </button>
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
                                placeholder="{{ __('messages.search_capacity_placeholder') }}" 
                                type="search">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">{{ __('messages.search_capacity_help') }}</p>
                    </div>
                    
                    <!-- Linha de filtros -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Filtro de Status -->
                        <div>
                            <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-tasks text-gray-500 mr-1"></i>
                                {{ __('messages.status') }}
                            </label>
                            <select wire:model="statusFilter" id="statusFilter" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="">{{ __('messages.all_statuses') }}</option>
                                @foreach($statuses as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Filtro de Tipo de Recurso -->
                        <div>
                            <label for="resourceTypeFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-cogs text-gray-500 mr-1"></i>
                                {{ __('messages.resource_type') }}
                            </label>
                            <select wire:model="resourceTypeFilter" id="resourceTypeFilter" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="">{{ __('messages.all_resource_types') }}</option>
                                @foreach($resourceTypes as $resourceType)
                                    <option value="{{ $resourceType->id }}">{{ $resourceType->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Filtro de Departamento -->
                        <div>
                            <label for="departmentFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-building text-gray-500 mr-1"></i>
                                {{ __('messages.department') }}
                            </label>
                            <select wire:model="departmentFilter" id="departmentFilter" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="">{{ __('messages.all_departments') }}</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Filtro de Localização -->
                        <div>
                            <label for="locationFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-map-marker-alt text-gray-500 mr-1"></i>
                                {{ __('messages.location') }}
                            </label>
                            <select wire:model="locationFilter" id="locationFilter" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="">{{ __('messages.all_locations') }}</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Filtro de Data -->
                        <div>
                            <label for="dateFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-calendar-day text-gray-500 mr-1"></i>
                                {{ __('messages.plan_date') }}
                            </label>
                            <input wire:model="dateFilter" id="dateFilter" type="date" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
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

        <!-- Tabela de Planos de Capacidade -->
        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden transition-all duration-300 ease-in-out hover:shadow-lg">
            <!-- Cabeçalho da Tabela -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
                <h2 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    {{ __('messages.capacity_plans_list') }}
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('resource_id')">
                                    {{ __('messages.resource') }}
                                    @if($sortField === 'resource_id')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 opacity-50"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('start_date')">
                                    {{ __('messages.plan_period') }}
                                    @if($sortField === 'start_date')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 opacity-50"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('available_capacity')">
                                    {{ __('messages.capacity') }}
                                    @if($sortField === 'available_capacity')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 opacity-50"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('department_id')">
                                    {{ __('messages.department') }}
                                    @if($sortField === 'department_id')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 opacity-50"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('status')">
                                    {{ __('messages.status') }}
                                    @if($sortField === 'status')
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
                        @forelse ($capacityPlans as $plan)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $plan->resource->name ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ $plan->resourceType->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ optional($plan->start_date)->format('d/m/Y') }} - 
                                        {{ optional($plan->end_date)->format('d/m/Y') }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        @php
                                            $days = optional($plan->start_date)->diffInDays($plan->end_date) + 1;
                                        @endphp
                                        {{ $days }} {{ __('messages.days') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ number_format($plan->available_capacity, 2) }} 
                                        {{ $capacityUnits[$plan->capacity_uom] ?? $plan->capacity_uom }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ __('messages.efficiency') }}: {{ $plan->efficiency_factor }}%
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $plan->department->name ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ $plan->location->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($plan->status === 'draft') bg-gray-100 text-gray-800
                                        @elseif($plan->status === 'confirmed') bg-blue-100 text-blue-800
                                        @elseif($plan->status === 'in_progress') bg-yellow-100 text-yellow-800
                                        @elseif($plan->status === 'completed') bg-green-100 text-green-800
                                        @elseif($plan->status === 'cancelled') bg-red-100 text-red-800
                                        @endif">
                                        <i class="mr-1
                                            @if($plan->status === 'draft') fas fa-pencil-alt
                                            @elseif($plan->status === 'confirmed') fas fa-clipboard-check
                                            @elseif($plan->status === 'in_progress') fas fa-spinner fa-spin
                                            @elseif($plan->status === 'completed') fas fa-check-circle
                                            @elseif($plan->status === 'cancelled') fas fa-ban
                                            @endif"></i>
                                        {{ $statuses[$plan->status] ?? $plan->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <button wire:click="view({{ $plan->id }})" 
                                            class="text-blue-600 hover:text-blue-900 transition-colors duration-150 transform hover:scale-110" title="{{ __('messages.view_details') }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button wire:click="edit({{ $plan->id }})" 
                                            class="text-indigo-600 hover:text-indigo-900 transition-colors duration-150 transform hover:scale-110" title="{{ __('messages.edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button wire:click="confirmDelete({{ $plan->id }})" 
                                            class="text-red-600 hover:text-red-900 transition-colors duration-150 transform hover:scale-110" title="{{ __('messages.delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        
                                        <!-- Dropdown de Ações para Status -->
                                        <div x-data="{ open: false }" class="relative text-left">
                                            <button @click="open = !open" type="button" class="text-gray-600 hover:text-gray-900 transition-colors duration-150 transform hover:scale-110">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            
                                            <div x-show="open" @click.away="open = false" 
                                                class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none z-10"
                                                x-transition:enter="transition ease-out duration-100"
                                                x-transition:enter-start="transform opacity-0 scale-95"
                                                x-transition:enter-end="transform opacity-100 scale-100"
                                                x-transition:leave="transition ease-in duration-75"
                                                x-transition:leave-start="transform opacity-100 scale-100"
                                                x-transition:leave-end="transform opacity-0 scale-95"
                                                style="display: none">
                                                <div class="py-1">
                                                    @if($plan->status === 'draft')
                                                        <button wire:click="updateStatus({{ $plan->id }}, 'confirmed')" class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-900 w-full text-left">
                                                            <i class="fas fa-clipboard-check mr-3 text-blue-500"></i>
                                                            {{ __('messages.confirm_plan') }}
                                                        </button>
                                                    @elseif($plan->status === 'confirmed')
                                                        <button wire:click="updateStatus({{ $plan->id }}, 'in_progress')" class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-yellow-50 hover:text-yellow-900 w-full text-left">
                                                            <i class="fas fa-play-circle mr-3 text-yellow-500"></i>
                                                            {{ __('messages.start_plan') }}
                                                        </button>
                                                    @elseif($plan->status === 'in_progress')
                                                        <button wire:click="updateStatus({{ $plan->id }}, 'completed')" class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-green-50 hover:text-green-900 w-full text-left">
                                                            <i class="fas fa-check-circle mr-3 text-green-500"></i>
                                                            {{ __('messages.complete_plan') }}
                                                        </button>
                                                    @endif
                                                    
                                                    @if(!in_array($plan->status, ['completed', 'cancelled']))
                                                        <button wire:click="updateStatus({{ $plan->id }}, 'cancelled')" class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-900 w-full text-left">
                                                            <i class="fas fa-ban mr-3 text-red-500"></i>
                                                            {{ __('messages.cancel_plan') }}
                                                        </button>
                                                    @endif
                                                    
                                                    @if($plan->status === 'cancelled')
                                                        <button wire:click="updateStatus({{ $plan->id }}, 'draft')" class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-900 w-full text-left">
                                                            <i class="fas fa-redo-alt mr-3 text-gray-500"></i>
                                                            {{ __('messages.reopen_as_draft') }}
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col items-center justify-center py-6 space-y-2">
                                        <div class="flex-shrink-0 bg-gray-100 p-3 rounded-full">
                                            <i class="fas fa-calendar-times text-gray-400 text-2xl"></i>
                                        </div>
                                        <p class="text-gray-500 text-sm">{{ __('messages.no_capacity_plans_found') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <div class="px-4 py-3 bg-white border-t border-gray-200">
                {{ $capacityPlans->links() }}
            </div>
        </div>
    </div>

    <!-- Modais (incluindo os arquivos separados) -->
    @include('livewire.mrp.capacity-planning.create-edit-modal')
    @include('livewire.mrp.capacity-planning.view-modal')
    @include('livewire.mrp.capacity-planning.delete-modal')
</div>
