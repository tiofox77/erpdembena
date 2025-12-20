<div>
    <div class="py-4">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-4 sm:p-6">
                    {{-- Header --}}
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg px-6 py-8 mb-6 shadow-lg">
                        <div class="flex justify-between items-center">
                            <div>
                                <h1 class="text-2xl font-bold text-white flex items-center">
                                    <i class="fas fa-tools mr-3 text-blue-200 animate-pulse"></i>
                                    {{ __('messages.work_equipment_management') }}
                                </h1>
                                <p class="text-blue-100 mt-2">{{ __('messages.manage_equipment_assignments_maintenance') }}</p>
                            </div>
                            <div class="flex space-x-3">
                                @if($activeTab === 'equipment')
                                    <button wire:click="createEquipment" class="inline-flex items-center px-4 py-2 bg-white border border-transparent rounded-md font-semibold text-blue-600 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                        <i class="fas fa-plus mr-2"></i>
                                        {{ __('messages.add_equipment') }}
                                    </button>
                                @else
                                    <button wire:click="createAssignment" class="inline-flex items-center px-4 py-2 bg-white border border-transparent rounded-md font-semibold text-blue-600 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                        <i class="fas fa-plus mr-2"></i>
                                        {{ __('messages.new_assignment') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Tabs --}}
                    <div class="mb-6 border-b border-gray-200">
                        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                            <li class="mr-2">
                                <button wire:click="setActiveTab('equipment')" 
                                    class="inline-block p-4 rounded-t-lg hover:text-gray-600 hover:border-gray-300 transition-all {{ $activeTab === 'equipment' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-gray-500' }}">
                                    <i class="fas fa-tools mr-2"></i>
                                    {{ __('messages.equipment') }}
                                </button>
                            </li>
                            <li class="mr-2">
                                <button wire:click="setActiveTab('assignments')" 
                                    class="inline-block p-4 rounded-t-lg hover:text-gray-600 hover:border-gray-300 transition-all {{ $activeTab === 'assignments' ? 'border-b-2 border-green-600 text-green-600 font-semibold' : 'text-gray-500' }}">
                                    <i class="fas fa-user-tag mr-2"></i>
                                    {{ __('messages.assignments') }}
                                </button>
                            </li>
                        </ul>
                    </div>

                    {{-- Equipment Tab --}}
                    <div class="{{ $activeTab === 'equipment' ? 'block' : 'hidden' }}">
                        {{-- Filters and Search --}}
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 overflow-hidden">
                            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                                <div class="flex items-center">
                                    <i class="fas fa-filter text-gray-600 mr-2"></i>
                                    <h3 class="text-lg font-medium text-gray-700">{{ __('messages.search_and_filters') }}</h3>
                                </div>
                            </div>
                            
                            <div class="p-6">
                                {{-- Search Bar --}}
                                <div class="mb-6">
                                    <div class="relative max-w-md">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-search text-gray-400"></i>
                                        </div>
                                        <input
                                            type="text"
                                            wire:model.live.debounce.300ms="searchEquipment"
                                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                            placeholder="{{ __('messages.search') }}..."
                                        >
                                    </div>
                                </div>

                                {{-- Advanced Filters --}}
                                <div class="space-y-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        {{-- Category Filter --}}
                                        <div class="space-y-2">
                                            <label class="flex items-center text-sm font-medium text-gray-700">
                                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-2">
                                                    <i class="fas fa-folder text-blue-600 text-xs"></i>
                                                </div>
                                                {{ __('messages.category') }}
                                            </label>
                                            <div class="relative">
                                                <select wire:model.live="filters.equipment_type" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 bg-white pr-8 transition-all duration-200 appearance-none">
                                                    <option value="">{{ __('messages.all_categories') }}</option>
                                                    @foreach($workEquipmentCategories as $category)
                                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                    <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                                </div>
                                            </div>
                                            @if($filters['equipment_type'])
                                                <div class="flex items-center text-xs text-blue-600">
                                                    <i class="fas fa-filter mr-1"></i>
                                                    {{ __('messages.filtered') }}
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Status Filter --}}
                                        <div class="space-y-2">
                                            <label class="flex items-center text-sm font-medium text-gray-700">
                                                <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center mr-2">
                                                    <i class="fas fa-info-circle text-purple-600 text-xs"></i>
                                                </div>
                                                {{ __('messages.status') }}
                                            </label>
                                            <div class="relative">
                                                <select wire:model.live="filters.status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-500 focus:ring-opacity-50 bg-white pr-8 transition-all duration-200 appearance-none">
                                                    <option value="">{{ __('messages.all_statuses') }}</option>
                                                    <option value="available">{{ __('messages.available') }}</option>
                                                    <option value="assigned">{{ __('messages.assigned') }}</option>
                                                    <option value="maintenance">{{ __('messages.maintenance') }}</option>
                                                    <option value="damaged">{{ __('messages.damaged') }}</option>
                                                    <option value="disposed">{{ __('messages.disposed') }}</option>
                                                </select>
                                                <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                    <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                                </div>
                                            </div>
                                            @if($filters['status'])
                                                <div class="flex items-center text-xs text-purple-600">
                                                    <i class="fas fa-filter mr-1"></i>
                                                    {{ __('messages.filtered') }}
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Per Page --}}
                                        <div class="space-y-2">
                                            <label class="flex items-center text-sm font-medium text-gray-700">
                                                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center mr-2">
                                                    <i class="fas fa-list text-gray-600 text-xs"></i>
                                                </div>
                                                {{ __('messages.per_page') }}
                                            </label>
                                            <div class="relative">
                                                <select wire:model.live="perPage" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-500 focus:ring-opacity-50 bg-white pr-8 transition-all duration-200">
                                                    <option value="10">10</option>
                                                    <option value="25">25</option>
                                                    <option value="50">50</option>
                                                    <option value="100">100</option>
                                                </select>
                                                <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                    <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Filter Actions --}}
                                <div class="mt-6 flex items-center justify-between">
                                    <div class="text-sm text-gray-600">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        {{ __('messages.showing') }} {{ $equipment->firstItem() ?? 0 }} {{ __('messages.to') }} {{ $equipment->lastItem() ?? 0 }} {{ __('messages.of') }} {{ $equipment->total() }} {{ __('messages.results') }}
                                    </div>
                                    <button wire:click="resetFilters" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                        <i class="fas fa-undo mr-2"></i>
                                        {{ __('messages.reset_filters') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Equipment Table --}}
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-table text-gray-600 mr-2"></i>
                                        <h3 class="text-lg font-medium text-gray-700">{{ __('messages.equipment_list') }}</h3>
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        <i class="fas fa-tools mr-1"></i>
                                        {{ $equipment->total() }} {{ __('messages.total') }}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <div class="flex items-center space-x-1 cursor-pointer transition-colors duration-200 hover:text-gray-700" wire:click="sortByEquipment('name')">
                                                    <i class="fas fa-tools text-gray-400 mr-1"></i>
                                                    <span>{{ __('messages.name') }}</span>
                                                    @if($sortFieldEquipment === 'name')
                                                        <i class="fas fa-sort-{{ $sortDirectionEquipment === 'asc' ? 'up' : 'down' }} ml-1 text-blue-600"></i>
                                                    @else
                                                        <i class="fas fa-sort ml-1 text-gray-400"></i>
                                                    @endif
                                                </div>
                                            </th>
                                            <th scope="col" class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <div class="flex items-center">
                                                    <i class="fas fa-folder text-gray-400 mr-1"></i>
                                                    <span>{{ __('messages.category') }}</span>
                                                </div>
                                            </th>
                                            <th scope="col" class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <div class="flex items-center">
                                                    <i class="fas fa-barcode text-gray-400 mr-1"></i>
                                                    <span>{{ __('messages.serial_number') }}</span>
                                                </div>
                                            </th>
                                            <th scope="col" class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <div class="flex items-center">
                                                    <i class="fas fa-info-circle text-gray-400 mr-1"></i>
                                                    <span>{{ __('messages.status') }}</span>
                                                </div>
                                            </th>
                                            <th scope="col" class="px-4 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <i class="fas fa-cog text-gray-400 mr-1"></i>
                                                <span>{{ __('messages.actions') }}</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($equipment as $item)
                                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                                <td class="px-4 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                            <i class="fas fa-tools text-blue-600"></i>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">{{ $item->name }}</div>
                                                            @if($item->asset_code)
                                                                <div class="text-sm text-gray-500">{{ $item->asset_code }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $item->category ? $item->category->name : '--' }}</div>
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-500">{{ $item->serial_number ?? '--' }}</div>
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap">
                                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        {{ $item->status === 'available' ? 'bg-green-100 text-green-800' : 
                                                        ($item->status === 'assigned' ? 'bg-blue-100 text-blue-800' : 
                                                        ($item->status === 'maintenance' ? 'bg-yellow-100 text-yellow-800' : 
                                                        'bg-red-100 text-red-800')) }}">
                                                        {{ __('messages.' . $item->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <div class="flex items-center justify-end space-x-3">
                                                        <button wire:click="editEquipment({{ $item->id }})" 
                                                            class="text-blue-600 hover:text-blue-900 transition-colors duration-200"
                                                            title="{{ __('messages.edit') }}">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button wire:click="confirmDelete({{ $item->id }}, 'equipment')" 
                                                            class="text-red-600 hover:text-red-900 transition-colors duration-200"
                                                            title="{{ __('messages.delete') }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-4 py-12 text-center text-gray-500">
                                                    <div class="flex flex-col items-center">
                                                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                                            <i class="fas fa-tools text-4xl text-gray-300"></i>
                                                        </div>
                                                        <p class="text-lg font-medium text-gray-900 mb-1">{{ __('messages.no_equipment_found') }}</p>
                                                        <p class="text-sm text-gray-500">{{ __('messages.add_equipment_to_get_started') }}</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-4">
                            {{ $equipment->links() }}
                        </div>
                    </div>

                    {{-- Assignments Tab --}}
                    <div class="{{ $activeTab === 'assignments' ? 'block' : 'hidden' }}">
                        {{-- Filters and Search --}}
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 overflow-hidden">
                            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                                <div class="flex items-center">
                                    <i class="fas fa-filter text-gray-600 mr-2"></i>
                                    <h3 class="text-lg font-medium text-gray-700">{{ __('messages.search_and_filters') }}</h3>
                                </div>
                            </div>
                            
                            <div class="p-6">
                                {{-- Search Bar --}}
                                <div class="mb-6">
                                    <div class="relative max-w-md">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-search text-gray-400"></i>
                                        </div>
                                        <input
                                            type="text"
                                            wire:model.live.debounce.300ms="searchAssignment"
                                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200"
                                            placeholder="{{ __('messages.search') }}..."
                                        >
                                    </div>
                                </div>

                                {{-- Filter Actions --}}
                                <div class="mt-6 flex items-center justify-between">
                                    <div class="text-sm text-gray-600">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        {{ __('messages.showing') }} {{ $assignments->firstItem() ?? 0 }} {{ __('messages.to') }} {{ $assignments->lastItem() ?? 0 }} {{ __('messages.of') }} {{ $assignments->total() }} {{ __('messages.results') }}
                                    </div>
                                    <button wire:click="resetFilters" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
                                        <i class="fas fa-undo mr-2"></i>
                                        {{ __('messages.reset_filters') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Assignments Table --}}
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-table text-gray-600 mr-2"></i>
                                        <h3 class="text-lg font-medium text-gray-700">{{ __('messages.assignments_list') }}</h3>
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        <i class="fas fa-user-tag mr-1"></i>
                                        {{ $assignments->total() }} {{ __('messages.total') }}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <div class="flex items-center">
                                                    <i class="fas fa-user text-gray-400 mr-1"></i>
                                                    <span>{{ __('messages.employee') }}</span>
                                                </div>
                                            </th>
                                            <th scope="col" class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <div class="flex items-center">
                                                    <i class="fas fa-tools text-gray-400 mr-1"></i>
                                                    <span>{{ __('messages.equipment') }}</span>
                                                </div>
                                            </th>
                                            <th scope="col" class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <div class="flex items-center">
                                                    <i class="fas fa-calendar text-gray-400 mr-1"></i>
                                                    <span>{{ __('messages.issue_date') }}</span>
                                                </div>
                                            </th>
                                            <th scope="col" class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <div class="flex items-center">
                                                    <i class="fas fa-calendar-check text-gray-400 mr-1"></i>
                                                    <span>{{ __('messages.return_date') }}</span>
                                                </div>
                                            </th>
                                            <th scope="col" class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <div class="flex items-center">
                                                    <i class="fas fa-info-circle text-gray-400 mr-1"></i>
                                                    <span>{{ __('messages.status') }}</span>
                                                </div>
                                            </th>
                                            <th scope="col" class="px-4 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <i class="fas fa-cog text-gray-400 mr-1"></i>
                                                <span>{{ __('messages.actions') }}</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($assignments as $assignment)
                                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                                <td class="px-4 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-lg flex items-center justify-center">
                                                            <i class="fas fa-user text-green-600"></i>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">
                                                                {{ $assignment->employee->full_name }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $assignment->equipment->name }}</div>
                                                    @if($assignment->equipment->asset_code)
                                                        <div class="text-sm text-gray-500">{{ $assignment->equipment->asset_code }}</div>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-500">
                                                        {{ $assignment->issue_date ? $assignment->issue_date->format('d/m/Y') : '--' }}
                                                    </div>
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-500">
                                                        {{ $assignment->return_date ? $assignment->return_date->format('d/m/Y') : '--' }}
                                                    </div>
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap">
                                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        {{ $assignment->status === 'issued' ? 'bg-green-100 text-green-800' : 
                                                        ($assignment->status === 'returned' ? 'bg-blue-100 text-blue-800' : 
                                                        ($assignment->status === 'damaged' ? 'bg-yellow-100 text-yellow-800' : 
                                                        'bg-red-100 text-red-800')) }}">
                                                        {{ __('messages.' . $assignment->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <div class="flex items-center justify-end space-x-3">
                                                        <button wire:click="editAssignment({{ $assignment->id }})" 
                                                            class="text-blue-600 hover:text-blue-900 transition-colors duration-200"
                                                            title="{{ __('messages.edit') }}">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        @if($assignment->status === 'issued')
                                                            <button wire:click="markAsReturned({{ $assignment->id }})" 
                                                                class="text-green-600 hover:text-green-900 transition-colors duration-200"
                                                                title="{{ __('messages.mark_as_returned') }}">
                                                                <i class="fas fa-check-circle"></i>
                                                            </button>
                                                        @endif
                                                        <button wire:click="confirmDeleteAssignment({{ $assignment->id }})" 
                                                            class="text-red-600 hover:text-red-900 transition-colors duration-200"
                                                            title="{{ __('messages.delete') }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-4 py-12 text-center text-gray-500">
                                                    <div class="flex flex-col items-center">
                                                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                                            <i class="fas fa-user-tag text-4xl text-gray-300"></i>
                                                        </div>
                                                        <p class="text-lg font-medium text-gray-900 mb-1">{{ __('messages.no_assignments_found') }}</p>
                                                        <p class="text-sm text-gray-500">{{ __('messages.create_assignment_to_get_started') }}</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-4">
                            {{ $assignments->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modals --}}
    @include('livewire.hr.work-equipment.modals.equipment-modal')
    @include('livewire.hr.work-equipment.modals.assignment-modal')
    @include('livewire.hr.work-equipment.modals.delete-modal')
</div>

@push('scripts')
<script>
    window.addEventListener('success', event => {
        toastr.success(event.detail);
    });
    
    window.addEventListener('error', event => {
        toastr.error(event.detail);
    });
</script>
@endpush
