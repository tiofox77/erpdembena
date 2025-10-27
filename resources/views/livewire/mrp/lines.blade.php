<div>
    <!-- Cabeçalho da Página -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 inline-flex items-center">
                <i class="fas fa-industry text-blue-600 mr-2"></i>
                {{ __('messages.lines_management') }}
            </h1>
            <p class="mt-1 text-sm text-gray-500">{{ __('messages.lines_management_description') }}</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-2">
            @can('lines.create')
                <button type="button" wire:click="openCreateModal" 
                    class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                    <i class="fas fa-plus mr-2 animate-pulse"></i>
                    {{ __('messages.add_line') }}
                </button>
            @else
                <button type="button" disabled
                    class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gray-400 cursor-not-allowed opacity-75"
                    title="{{ __('messages.no_permission') }}">
                    <i class="fas fa-ban mr-2"></i>
                    {{ __('messages.add_line') }}
                </button>
            @endcan
        </div>
    </div>
    
    <!-- Cartão de Filtros -->
    <div class="mb-6 bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 ease-in-out hover:shadow-lg">
        <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
            <i class="fas fa-filter text-blue-600 mr-2"></i>
            <h2 class="text-base font-medium text-gray-700">{{ __('messages.filter_lines') }}</h2>
        </div>
        
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Campo de Busca -->
                <div class="md:col-span-3">
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
                            placeholder="{{ __('messages.search_lines_placeholder') }}" 
                            type="search">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">{{ __('messages.search_lines_help') }}</p>
                </div>
                
                <!-- Filtro por Status -->
                <div>
                    <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-toggle-on text-gray-500 mr-1"></i>
                        {{ __('messages.status') }}
                    </label>
                    <select wire:model.live="statusFilter" id="statusFilter" 
                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                        <option value="">{{ __('messages.all_statuses') }}</option>
                        <option value="active">{{ __('messages.active') }}</option>
                        <option value="inactive">{{ __('messages.inactive') }}</option>
                    </select>
                </div>
                
                <!-- Filtro por Departamento -->
                <div>
                    <label for="departmentFilter" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-building text-gray-500 mr-1"></i>
                        {{ __('messages.department') }}
                    </label>
                    <select wire:model.live="departmentFilter" id="departmentFilter" 
                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                        <option value="">{{ __('messages.all_departments') }}</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Filtro por Localização -->
                <div>
                    <label for="locationFilter" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-map-marker-alt text-gray-500 mr-1"></i>
                        {{ __('messages.location') }}
                    </label>
                    <select wire:model.live="locationFilter" id="locationFilter" 
                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                        <option value="">{{ __('messages.all_locations') }}</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Registros por página -->
                <div>
                    <label for="perPage" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-list-ol text-gray-500 mr-1"></i>
                        {{ __('messages.items_per_page') }}
                    </label>
                    <select wire:model.live="perPage" id="perPage" 
                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
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

    <!-- Alertas (se necessário) -->
    @if (session()->has('message'))
        <div class="mb-4 flex w-full overflow-hidden bg-white rounded-lg shadow-md">
            <div class="flex items-center justify-center w-12 bg-green-500">
                <i class="fas fa-check text-white"></i>
            </div>
            <div class="px-4 py-2 -mx-3">
                <div class="mx-3">
                    <p class="text-sm text-gray-600">{{ session('message') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Tabela de Linhas de Produção -->
    <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden transition-all duration-300 ease-in-out hover:shadow-lg">
        <!-- Cabeçalho da Tabela -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
            <h2 class="text-lg font-medium text-white flex items-center">
                <i class="fas fa-industry mr-2"></i>
                {{ __('messages.production_lines_list') }}
            </h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center cursor-pointer" wire:click="sortBy('code')">
                                {{ __('messages.code') }}
                                @if($sortField === 'code')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @else
                                    <i class="fas fa-sort ml-1 opacity-50"></i>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center cursor-pointer" wire:click="sortBy('name')">
                                {{ __('messages.name') }}
                                @if($sortField === 'name')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @else
                                    <i class="fas fa-sort ml-1 opacity-50"></i>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('messages.location') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('messages.capacity') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center cursor-pointer" wire:click="sortBy('is_active')">
                                {{ __('messages.status') }}
                                @if($sortField === 'is_active')
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
                    @forelse ($lines as $line)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $line->code }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $line->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $line->location ? $line->location->name : __('messages.not_assigned') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($line->capacity_per_hour, 1) }} {{ __('messages.units_per_hour') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($line->is_active)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ __('messages.active') }}
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        {{ __('messages.inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    @can('lines.view')
                                        <button wire:click="view({{ $line->id }})" 
                                            class="text-blue-600 hover:text-blue-900 transition-colors duration-150 transform hover:scale-110"
                                            title="{{ __('messages.view') }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @else
                                        <button disabled
                                            class="text-gray-400 cursor-not-allowed opacity-75"
                                            title="{{ __('messages.no_permission') }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @endcan
                                    
                                    @can('lines.edit')
                                        <button wire:click="edit({{ $line->id }})" 
                                            class="text-indigo-600 hover:text-indigo-900 transition-colors duration-150 transform hover:scale-110"
                                            title="{{ __('messages.edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @else
                                        <button disabled
                                            class="text-gray-400 cursor-not-allowed opacity-75"
                                            title="{{ __('messages.no_permission') }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @endcan
                                    
                                    @can('lines.delete')
                                        <button wire:click="openDeleteModal({{ $line->id }})" 
                                            class="text-red-600 hover:text-red-900 transition-colors duration-150 transform hover:scale-110"
                                            title="{{ __('messages.delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @else
                                        <button disabled
                                            class="text-gray-400 cursor-not-allowed opacity-75"
                                            title="{{ __('messages.no_permission') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col items-center justify-center py-6 space-y-2">
                                    <div class="flex-shrink-0 bg-gray-100 p-3 rounded-full">
                                        <i class="fas fa-industry text-gray-400 text-2xl"></i>
                                    </div>
                                    <p class="text-gray-500 text-sm">{{ __('messages.no_lines_found') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Paginação -->
        <div class="px-4 py-3 bg-white border-t border-gray-200">
            {{ $lines->links() }}
        </div>
    </div>

    <!-- Modais -->
    <div>
        @include('livewire.mrp.lines.create-edit-modal')
        @include('livewire.mrp.lines.delete-modal')
        @include('livewire.mrp.lines.view-modal')
    </div>
</div>
