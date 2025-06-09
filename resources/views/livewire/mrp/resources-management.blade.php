<div>
    <!-- Cabeçalho do componente -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-tools text-blue-600 mr-3"></i>
            {{ trans('messages.resources_management') }}
        </h1>
        <div class="flex gap-2">
            @can('resource_types.create')
                <button wire:click="createResourceType" 
                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 ease-in-out transform hover:scale-105 hover:shadow-lg"
                    title="{{ trans('messages.add_resource_type') }}">
                    <i class="fas fa-layer-group mr-2 animate-pulse"></i>
                    {{ trans('messages.add_resource_type') }}
                </button>
            @else
                <button type="button" disabled
                    class="inline-flex items-center px-4 py-2 bg-gray-400 border border-transparent rounded-md font-semibold text-white cursor-not-allowed opacity-75"
                    title="{{ trans('messages.no_permission') }}">
                    <i class="fas fa-ban mr-2"></i>
                    {{ trans('messages.add_resource_type') }}
                </button>
            @endcan
            
            @can('resources.create')
                <button wire:click="create" 
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 hover:shadow-lg"
                    title="{{ trans('messages.add_resource') }}">
                    <i class="fas fa-plus-circle mr-2 animate-pulse"></i>
                    {{ trans('messages.add_resource') }}
                </button>
            @else
                <button type="button" disabled
                    class="inline-flex items-center px-4 py-2 bg-gray-400 border border-transparent rounded-md font-semibold text-white cursor-not-allowed opacity-75"
                    title="{{ trans('messages.no_permission') }}">
                    <i class="fas fa-ban mr-2"></i>
                    {{ trans('messages.add_resource') }}
                </button>
            @endcan
        </div>
    </div>

    <!-- Cartão de Busca e Filtros -->
    <div class="bg-white rounded-lg shadow-md border border-gray-200 p-4 mb-6 transition-all duration-300 ease-in-out hover:shadow-lg">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <!-- Campo de Busca -->
            <div class="relative flex-grow max-w-md">
                <input type="text" wire:model.live.debounce.300ms="search" 
                    class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 ease-in-out"
                    placeholder="{{ trans('messages.search_resources') }}">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                @if($search)
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <button wire:click="$set('search', '')" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif
            </div>

            <!-- Filtros -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Filtro de Tipo de Recurso -->
                <div class="w-full sm:w-auto">
                    <select wire:model.live="resourceTypeFilter" 
                        class="w-full rounded-lg border border-gray-300 py-2 pl-3 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 ease-in-out">
                        <option value="">{{ trans('messages.all_types') }}</option>
                        @foreach($resourceTypesFilter as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro de Departamento -->
                <div class="w-full sm:w-auto">
                    <select wire:model.live="departmentFilter" 
                        class="w-full rounded-lg border border-gray-300 py-2 pl-3 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 ease-in-out">
                        <option value="">{{ trans('messages.all_departments') }}</option>
                        @foreach($departmentsFilter as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro de Localização -->
                <div class="w-full sm:w-auto">
                    <select wire:model.live="locationFilter" 
                        class="w-full rounded-lg border border-gray-300 py-2 pl-3 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 ease-in-out">
                        <option value="">{{ trans('messages.all_locations') }}</option>
                        @foreach($locationsFilter as $location)
                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro de Status -->
                <div class="w-full sm:w-auto">
                    <select wire:model.live="statusFilter" 
                        class="w-full rounded-lg border border-gray-300 py-2 pl-3 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 ease-in-out">
                        <option value="">{{ trans('messages.all_status') }}</option>
                        <option value="1">{{ trans('messages.active') }}</option>
                        <option value="0">{{ trans('messages.inactive') }}</option>
                    </select>
                </div>

                <!-- Botão de Resetar Filtros -->
                <button wire:click="resetFilters" 
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out">
                    <i class="fas fa-undo mr-2"></i>
                    {{ trans('messages.reset_filters') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Tabela de Recursos -->
    <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden transition-all duration-300 ease-in-out hover:shadow-lg">
        <!-- Cabeçalho da Tabela -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
            <h2 class="text-lg font-medium text-white flex items-center">
                <i class="fas fa-tools mr-2"></i>
                {{ trans('messages.resources_list') }}
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center cursor-pointer" wire:click="sortBy('name')">
                                {{ trans('messages.resource') }}
                                @if(isset($sortField) && $sortField === 'name')
                                    <i class="fas fa-sort-{{ isset($sortDirection) && $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @else
                                    <i class="fas fa-sort ml-1 opacity-50"></i>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center cursor-pointer" wire:click="sortBy('resource_type_id')">
                                {{ trans('messages.type') }}
                                @if(isset($sortField) && $sortField === 'resource_type_id')
                                    <i class="fas fa-sort-{{ isset($sortDirection) && $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @else
                                    <i class="fas fa-sort ml-1 opacity-50"></i>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center cursor-pointer" wire:click="sortBy('department_id')">
                                {{ trans('messages.department') }}
                                @if(isset($sortField) && $sortField === 'department_id')
                                    <i class="fas fa-sort-{{ isset($sortDirection) && $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @else
                                    <i class="fas fa-sort ml-1 opacity-50"></i>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center cursor-pointer" wire:click="sortBy('location_id')">
                                {{ trans('messages.location') }}
                                @if(isset($sortField) && $sortField === 'location_id')
                                    <i class="fas fa-sort-{{ isset($sortDirection) && $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @else
                                    <i class="fas fa-sort ml-1 opacity-50"></i>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center cursor-pointer" wire:click="sortBy('capacity')">
                                {{ trans('messages.capacity') }}
                                @if(isset($sortField) && $sortField === 'capacity')
                                    <i class="fas fa-sort-{{ isset($sortDirection) && $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @else
                                    <i class="fas fa-sort ml-1 opacity-50"></i>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ trans('messages.status') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ trans('messages.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($resources as $resource)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $resource->name }}</div>
                                <div class="text-xs text-gray-500">{{ Str::limit($resource->description, 30) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $resource->resourceType->name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $resource->department->name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $resource->location->name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ number_format($resource->capacity, 2) }} {{ $resource->capacity_uom }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ trans('messages.efficiency') }}: {{ $resource->efficiency_factor }}%
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($resource->active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i> {{ trans('messages.active') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1"></i> {{ trans('messages.inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    @can('resources.view')
                                        <button wire:click="view({{ $resource->id }})" 
                                            class="text-blue-600 hover:text-blue-900 transition-colors duration-150 transform hover:scale-110" 
                                            title="{{ trans('messages.view_details') }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @else
                                        <button disabled
                                            class="text-gray-400 cursor-not-allowed"
                                            title="{{ trans('messages.no_permission') }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @endcan
                                    
                                    @can('resources.edit')
                                        <button wire:click="edit({{ $resource->id }})" 
                                            class="text-indigo-600 hover:text-indigo-900 transition-colors duration-150 transform hover:scale-110" 
                                            title="{{ trans('messages.edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @else
                                        <button disabled
                                            class="text-gray-400 cursor-not-allowed"
                                            title="{{ trans('messages.no_permission') }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @endcan
                                    
                                    @can('resources.delete')
                                        <button wire:click="confirmDelete({{ $resource->id }})" 
                                            class="text-red-600 hover:text-red-900 transition-colors duration-150 transform hover:scale-110" 
                                            title="{{ trans('messages.delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @else
                                        <button disabled
                                            class="text-gray-400 cursor-not-allowed"
                                            title="{{ trans('messages.no_permission') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endcan
                                    
                                    <!-- Toggle Status -->
                                    @can('resources.edit')
                                        <button wire:click="toggleStatus({{ $resource->id }})" 
                                            class="{{ $resource->active ? 'text-orange-600 hover:text-orange-900' : 'text-green-600 hover:text-green-900' }} transition-colors duration-150 transform hover:scale-110" 
                                            title="{{ $resource->active ? trans('messages.deactivate') : trans('messages.activate') }}">
                                            <i class="fas {{ $resource->active ? 'fa-ban' : 'fa-check-circle' }}"></i>
                                        </button>
                                    @else
                                        <button disabled
                                            class="text-gray-400 cursor-not-allowed"
                                            title="{{ trans('messages.no_permission') }}">
                                            <i class="fas {{ $resource->active ? 'fa-ban' : 'fa-check-circle' }}"></i>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center py-6">
                                    <i class="fas fa-search text-gray-400 text-3xl mb-3"></i>
                                    <p class="text-gray-500 mb-1">{{ trans('messages.no_resources_found') }}</p>
                                    @can('resources.create')
                                        <p class="text-gray-400 text-sm">{{ trans('messages.try_adjusting_filters_or_create') }}</p>
                                    @else
                                        <p class="text-gray-400 text-sm">{{ trans('messages.try_adjusting_filters') }}</p>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Paginação -->
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $resources->links() }}
        </div>
    </div>

    <!-- Modal para Ver/Criar/Editar Recursos -->
    @include('livewire.mrp.resources-management.create-edit-modal')
    
    <!-- Modal para Ver/Criar/Editar Tipos de Recursos -->
    @include('livewire.mrp.resources-management.resource-type-modal')
    
    <!-- Modal para Visualização Detalhada -->
    @include('livewire.mrp.resources-management.view-modal')
    
    <!-- Modal para Confirmação de Exclusão -->
    @include('livewire.mrp.resources-management.delete-modal')
</div>
