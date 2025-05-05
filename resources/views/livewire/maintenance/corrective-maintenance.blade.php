<div>


    <div class="py-6 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg w-full">
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex items-center space-x-4">
                            <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-wrench mr-2 text-gray-600"></i> {{ __('messages.equipment_downtime_reporting') }}
                            </h2>
                            <x-maintenance-guide-link />
                        </div>
                        <button
                            wire:click="openModal"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                            <i class="fas fa-plus-circle mr-1"></i> {{ __('messages.report_downtime') }}
                        </button>
                    </div>

                    <!-- Filters and Search -->
                    <div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                        <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                            <i class="fas fa-filter mr-2 text-blue-500"></i> {{ __('messages.filters_and_search') }}
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                            <div class="md:col-span-2">
                                <label for="search" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-search mr-1 text-gray-500"></i> {{ __('messages.search') }}
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                    <input
                                        type="text"
                                        id="search"
                                        wire:model.live.debounce.300ms="search"
                                        placeholder="{{ __('messages.search_for_equipment') }}"
                                        class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 pr-3 py-2 sm:text-sm border-gray-300 rounded-md"
                                    >
                                </div>
                            </div>

                            <div>
                                <label for="filterYear" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="far fa-calendar-alt mr-1 text-gray-500"></i> {{ __('messages.year') }}
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="filterYear"
                                        wire:model.live="filterYear"
                                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                        <option value="">{{ __('messages.all_years') }}</option>
                                        @foreach($years as $yearKey => $yearValue)
                                            <option value="{{ $yearKey }}">{{ $yearValue }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="filterMonth" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-calendar-day mr-1 text-gray-500"></i> Month
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="filterMonth"
                                        wire:model.live="filterMonth"
                                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                        <option value="">{{ __('messages.all_months') }}</option>
                                        @foreach($months as $monthKey => $monthValue)
                                            <option value="{{ $monthKey }}">{{ $monthValue }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="filterEquipment" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-wrench mr-1 text-gray-500"></i> Equipment
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="filterEquipment"
                                        wire:model.live="filterEquipment"
                                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                        <option value="">All Equipment</option>
                                        @foreach($equipment as $equipItem)
                                            <option value="{{ $equipItem->id }}">{{ $equipItem->name }} - {{ $equipItem->serial_number }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="filterStatus" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-toggle-on mr-1 text-gray-500"></i> Status
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="filterStatus"
                                        wire:model.live="filterStatus"
                                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                        <option value="">All Statuses</option>
                                        @foreach($statuses as $statusKey => $statusValue)
                                            <option value="{{ $statusKey }}">{{ $statusValue }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="perPage" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-list-ol mr-1 text-gray-500"></i> Items Per Page
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="perPage"
                                        wire:model.live="perPage"
                                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                        <option value="10">10 per page</option>
                                        <option value="25">25 per page</option>
                                        <option value="50">50 per page</option>
                                        <option value="100">100 per page</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end mt-3">
                            <button
                                wire:click="clearFilters"
                                class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                <i class="fas fa-times-circle mr-1"></i> Clear Filters
                            </button>
                        </div>
                    </div>

                    <!-- Corrective Maintenance Records Table -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg w-full border border-gray-200">
                        <div class="overflow-x-auto w-full">
                            <table class="min-w-full divide-y divide-gray-200 w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('year')">
                                            <div class="flex items-center space-x-1">
                                                <i class="far fa-calendar-alt text-gray-400 mr-1"></i>
                                                <span>Year</span>
                                                @if($sortField === 'year')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                                @else
                                                    <i class="fas fa-sort text-gray-300"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('month')">
                                            <div class="flex items-center space-x-1">
                                                <i class="fas fa-calendar-day text-gray-400 mr-1"></i>
                                                <span>Month</span>
                                                @if($sortField === 'month')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                                @else
                                                    <i class="fas fa-sort text-gray-300"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('week')">
                                            <div class="flex items-center space-x-1">
                                                <i class="fas fa-calendar-week text-gray-400 mr-1"></i>
                                                <span>Week#</span>
                                                @if($sortField === 'week')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                                @else
                                                    <i class="fas fa-sort text-gray-300"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('system_process')">
                                            <div class="flex items-center space-x-1">
                                                <i class="fas fa-sitemap text-gray-400 mr-1"></i>
                                                <span>System/Process</span>
                                                @if($sortField === 'system_process')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                                @else
                                                    <i class="fas fa-sort text-gray-300"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1">
                                                <i class="fas fa-wrench text-gray-400 mr-1"></i>
                                                <span>Equipment ID</span>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('failure_mode')">
                                            <div class="flex items-center space-x-1">
                                                <i class="fas fa-times-circle text-gray-400 mr-1"></i>
                                                <span>Failure Modes</span>
                                                @if($sortField === 'failure_mode')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                                @else
                                                    <i class="fas fa-sort text-gray-300"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1">
                                                <i class="fas fa-tags text-gray-400 mr-1"></i>
                                                <span>Mode Categories</span>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('start_time')">
                                            <div class="flex items-center space-x-1">
                                                <i class="fas fa-play-circle text-gray-400 mr-1"></i>
                                                <span>Start Time</span>
                                                @if($sortField === 'start_time')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                                @else
                                                    <i class="fas fa-sort text-gray-300"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('end_time')">
                                            <div class="flex items-center space-x-1">
                                                <i class="fas fa-stop-circle text-gray-400 mr-1"></i>
                                                <span>End Time</span>
                                                @if($sortField === 'end_time')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                                @else
                                                    <i class="fas fa-sort text-gray-300"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('downtime_length')">
                                            <div class="flex items-center space-x-1">
                                                <i class="fas fa-hourglass-half text-gray-400 mr-1"></i>
                                                <span>Downtime</span>
                                                @if($sortField === 'downtime_length')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                                @else
                                                    <i class="fas fa-sort text-gray-300"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('failure_cause')">
                                            <div class="flex items-center space-x-1">
                                                <i class="fas fa-search text-gray-400 mr-1"></i>
                                                <span>Failure Causes</span>
                                                @if($sortField === 'failure_cause')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                                @else
                                                    <i class="fas fa-sort text-gray-300"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1">
                                                <i class="fas fa-tag text-gray-400 mr-1"></i>
                                                <span>Cause Categories</span>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1">
                                                <i class="fas fa-toggle-on text-gray-400 mr-1"></i>
                                                <span>Status</span>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <i class="fas fa-cogs text-gray-400 mr-1"></i> Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($correctiveRecords as $record)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $record->year }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $months[$record->month] ?? $record->month }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $record->week }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $record->system_process }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <div class="flex items-center">
                                                    <i class="fas fa-tools text-gray-400 mr-1"></i>
                                                    {{ $record->equipment->name ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $record->failure_mode_name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $record->failure_mode_category ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $record->start_time ? $record->start_time->format('Y/m/d H:i:s') : 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $record->end_time ? $record->end_time->format('Y/m/d H:i:s') : 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <div class="flex items-center">
                                                    <i class="fas fa-clock text-gray-400 mr-1"></i>
                                                    {{ $record->formatted_downtime }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $record->failure_cause_name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $record->failure_cause_category ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full items-center
                                                    {{ $record->status === 'open' ? 'bg-red-100 text-red-800' :
                                                       ($record->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' :
                                                       ($record->status === 'resolved' ? 'bg-green-100 text-green-800' :
                                                       'bg-gray-100 text-gray-800')) }}">
                                                    <i class="fas
                                                        {{ $record->status === 'open' ? 'fa-exclamation-circle' :
                                                           ($record->status === 'in_progress' ? 'fa-spinner fa-spin' :
                                                           ($record->status === 'resolved' ? 'fa-check-circle' :
                                                           'fa-question-circle')) }} mr-1"></i>
                                                    {{ $statuses[$record->status] }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end space-x-2">
                                                    <button
                                                        wire:click.prevent="view({{ $record->id }})"
                                                        class="text-blue-600 hover:text-blue-900 w-8 h-8 rounded-full flex items-center justify-center hover:bg-blue-100"
                                                        title="View Details"
                                                    >
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button
                                                        wire:click="edit({{ $record->id }})"
                                                        class="text-indigo-600 hover:text-indigo-900 w-8 h-8 rounded-full flex items-center justify-center hover:bg-indigo-100"
                                                        title="Edit"
                                                    >
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button
                                                        wire:click="confirmDelete({{ $record->id }})"
                                                        class="text-red-600 hover:text-red-900 w-8 h-8 rounded-full flex items-center justify-center hover:bg-red-100"
                                                        title="Delete"
                                                    >
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="13" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                                <div class="flex flex-col items-center justify-center py-8">
                                                    <div class="bg-gray-100 rounded-full p-3 mb-4">
                                                        <i class="fas fa-clipboard-list text-gray-400 text-4xl"></i>
                                                    </div>
                                                    <p class="text-lg font-medium">No downtime records found</p>
                                                    <p class="text-sm text-gray-500 mt-1 flex items-center">
                                                        @if($search || $filterStatus || $filterEquipment || $filterYear || $filterMonth)
                                                            <i class="fas fa-filter mr-1"></i> Try adjusting your search filters or
                                                            <button
                                                                wire:click="clearFilters"
                                                                class="ml-1 text-blue-600 hover:text-blue-800 underline flex items-center"
                                                            >
                                                                <i class="fas fa-times-circle mr-1"></i> clear all filters
                                                            </button>
                                                        @else
                                                            <i class="fas fa-info-circle mr-1"></i> Click "Report Downtime" to record a new equipment failure
                                                        @endif
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="px-4 py-3 bg-white border-t border-gray-200 sm:px-6">
                            {{ $correctiveRecords->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-6xl p-6 overflow-y-auto max-h-[90vh]">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas {{ $isEditing ? 'fa-edit' : 'fa-toolbox' }} mr-2"></i>
                        {{ $isEditing ? 'Edit' : 'Report' }} Equipment Downtime
                    </h3>
                    <button type="button" class="text-gray-500 hover:text-gray-700 text-xl" wire:click="closeModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
                        <p class="font-bold flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            Please correct the following errors:
                        </p>
                        <ul class="mt-2 list-disc list-inside text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form wire:submit.prevent="save">
                    <!-- Basic Information Section -->
                    <div class="bg-gray-50 p-3 rounded-md mb-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-info-circle mr-2 text-blue-500"></i> Basic Information
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4 mb-3 sm:mb-4">
                            <!-- Year, Month, Week -->
                            <div>
                                <label for="year" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="far fa-calendar-alt mr-1 text-gray-500"></i> Year
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <input
                                        type="number"
                                        id="year"
                                        wire:model.live="corrective.year"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs sm:text-sm py-1.5 px-2
                                        @error('corrective.year') border-red-300 text-red-900 @enderror"
                                    >
                                    @error('corrective.year')
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i class="fas fa-exclamation-circle text-red-500"></i>
                                        </div>
                                    @enderror
                                </div>
                                @error('corrective.year')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="month" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-calendar-day mr-1 text-gray-500"></i> Month
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="month"
                                        wire:model.live="corrective.month"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs sm:text-sm py-1.5 px-2
                                        @error('corrective.month') border-red-300 text-red-900 @enderror"
                                    >
                                        <option value="">Select Month</option>
                                        @foreach($months as $monthKey => $monthValue)
                                            <option value="{{ $monthKey }}">{{ $monthValue }}</option>
                                        @endforeach
                                    </select>
                                    @error('corrective.month')
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i class="fas fa-exclamation-circle text-red-500"></i>
                                        </div>
                                    @enderror
                                </div>
                                @error('corrective.month')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="week" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-calendar-week mr-1 text-gray-500"></i> Week #
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <input
                                        type="number"
                                        id="week"
                                        wire:model.live="corrective.week"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs sm:text-sm py-1.5 px-2
                                        @error('corrective.week') border-red-300 text-red-900 @enderror"
                                    >
                                    @error('corrective.week')
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i class="fas fa-exclamation-circle text-red-500"></i>
                                        </div>
                                    @enderror
                                </div>
                                @error('corrective.week')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Equipment Section -->
                    <div class="bg-gray-50 p-3 rounded-md mb-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-cogs mr-2 text-blue-500"></i> Equipment Information
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4 mb-3 sm:mb-4">
                            <!-- System Process, Equipment ID -->
                            <div>
                                <label for="system_process" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-sitemap mr-1 text-gray-500"></i> System/Process
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <input
                                        type="text"
                                        id="system_process"
                                        wire:model.live="corrective.system_process"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs sm:text-sm py-1.5 px-2
                                        @error('corrective.system_process') border-red-300 text-red-900 @enderror"
                                    >
                                    @error('corrective.system_process')
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i class="fas fa-exclamation-circle text-red-500"></i>
                                        </div>
                                    @enderror
                                </div>
                                @error('corrective.system_process')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="equipment_id" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-wrench mr-1 text-gray-500"></i> Equipment
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="equipment_id"
                                        wire:model.live="corrective.equipment_id"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs sm:text-sm py-1.5 px-2
                                        @error('corrective.equipment_id') border-red-300 text-red-900 @enderror"
                                    >
                                        <option value="">Select Equipment</option>
                                        @foreach($equipment as $equipItem)
                                            <option value="{{ $equipItem->id }}">{{ $equipItem->name }} - {{ $equipItem->serial_number }}</option>
                                        @endforeach
                                    </select>
                                    @error('corrective.equipment_id')
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i class="fas fa-exclamation-circle text-red-500"></i>
                                        </div>
                                    @enderror
                                </div>
                                @error('corrective.equipment_id')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="status" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-toggle-on mr-1 text-gray-500"></i> Status
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="status"
                                        wire:model.live="corrective.status"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs sm:text-sm py-1.5 px-2
                                        @error('corrective.status') border-red-300 text-red-900 @enderror"
                                    >
                                        @foreach($statuses as $statusKey => $statusValue)
                                            <option value="{{ $statusKey }}">{{ $statusValue }}</option>
                                        @endforeach
                                    </select>
                                    @error('corrective.status')
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i class="fas fa-exclamation-circle text-red-500"></i>
                                        </div>
                                    @enderror
                                </div>
                                @error('corrective.status')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Failure Information Section -->
                    <div class="bg-gray-50 p-3 rounded-md mb-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-exclamation-triangle mr-2 text-orange-500"></i> Failure Information
                        </h4>

                        <!-- Failure Mode Info -->
                        <div class="md:col-span-3">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 mt-2">
                                <!-- Failure Mode -->
                                <div>
                                    <label for="failure_mode_id" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 flex items-center">
                                        <i class="fas fa-times-circle mr-1 text-gray-500"></i> Failure Mode
                                    </label>
                                    <div class="relative rounded-md shadow-sm">
                                        <select
                                            id="failure_mode_id"
                                            wire:model.live="corrective.failure_mode_id"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs sm:text-sm py-1.5 px-2
                                            @error('corrective.failure_mode_id') border-red-300 text-red-900 @enderror"
                                        >
                                            <option value="">Select Failure Mode</option>
                                            @foreach($failureModes as $mode)
                                                <option value="{{ $mode->id }}">{{ $mode->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('corrective.failure_mode_id')
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                            </div>
                                        @enderror
                                    </div>
                                    @error('corrective.failure_mode_id')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Failure Mode Category -->
                                <div>
                                    <label for="failure_mode_category_id" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 flex items-center">
                                        <i class="fas fa-tags mr-1 text-gray-500"></i> Failure Mode Category
                                    </label>
                                    <div class="relative rounded-md shadow-sm">
                                        <select
                                            id="failure_mode_category_id"
                                            wire:model.live="corrective.failure_mode_category_id"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs sm:text-sm py-1.5 px-2
                                            @error('corrective.failure_mode_category_id') border-red-300 text-red-900 @enderror"
                                        >
                                            <option value="">Select Failure Mode Category</option>
                                            @foreach($modeCategories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('corrective.failure_mode_category_id')
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                            </div>
                                        @enderror
                                    </div>
                                    @error('corrective.failure_mode_category_id')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Failure Cause -->
                        <div class="md:col-span-3 mt-3">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 mt-2">
                                <!-- Failure Cause -->
                                <div>
                                    <label for="failure_cause_id" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 flex items-center">
                                        <i class="fas fa-search mr-1 text-gray-500"></i> Failure Cause
                                    </label>
                                    <div class="relative rounded-md shadow-sm">
                                        <select
                                            id="failure_cause_id"
                                            wire:model.live="corrective.failure_cause_id"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs sm:text-sm py-1.5 px-2
                                            @error('corrective.failure_cause_id') border-red-300 text-red-900 @enderror"
                                        >
                                            <option value="">Select Failure Cause</option>
                                            @foreach($failureCauses as $cause)
                                                <option value="{{ $cause->id }}">{{ $cause->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('corrective.failure_cause_id')
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                            </div>
                                        @enderror
                                    </div>
                                    @error('corrective.failure_cause_id')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Failure Cause Category -->
                                <div>
                                    <label for="failure_cause_category_id" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 flex items-center">
                                        <i class="fas fa-tag mr-1 text-gray-500"></i> Failure Cause Category
                                    </label>
                                    <div class="relative rounded-md shadow-sm">
                                        <select
                                            id="failure_cause_category_id"
                                            wire:model.live="corrective.failure_cause_category_id"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs sm:text-sm py-1.5 px-2
                                            @error('corrective.failure_cause_category_id') border-red-300 text-red-900 @enderror"
                                        >
                                            <option value="">Select Failure Cause Category</option>
                                            @foreach($causeCategories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('corrective.failure_cause_category_id')
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                            </div>
                                        @enderror
                                    </div>
                                    @error('corrective.failure_cause_category_id')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Time Information Section -->
                    <div class="bg-gray-50 p-3 rounded-md mb-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-clock mr-2 text-blue-500"></i> Time Information
                        </h4>
                        <div class="md:col-span-3">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4">
                                <div>
                                    <label for="start_time" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 flex items-center">
                                        <i class="fas fa-play-circle mr-1 text-gray-500"></i> Start Time
                                    </label>
                                    <div class="relative rounded-md shadow-sm">
                                        <input
                                            type="datetime-local"
                                            id="start_time"
                                            wire:model.live="corrective.start_time"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs sm:text-sm py-1.5 px-2
                                            @error('corrective.start_time') border-red-300 text-red-900 @enderror"
                                        >
                                        @error('corrective.start_time')
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                            </div>
                                        @enderror
                                    </div>
                                    @error('corrective.start_time')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="end_time" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 flex items-center">
                                        <i class="fas fa-stop-circle mr-1 text-gray-500"></i> End Time
                                    </label>
                                    <div class="relative rounded-md shadow-sm">
                                        <input
                                            type="datetime-local"
                                            id="end_time"
                                            wire:model.live="corrective.end_time"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs sm:text-sm py-1.5 px-2
                                            @error('corrective.end_time') border-red-300 text-red-900 @enderror"
                                        >
                                        @error('corrective.end_time')
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                            </div>
                                        @enderror
                                    </div>
                                    @error('corrective.end_time')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="downtime_length" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 flex items-center">
                                        <i class="fas fa-hourglass-half mr-1 text-gray-500"></i> Downtime Length (hours)
                                    </label>
                                    <div class="relative rounded-md shadow-sm">
                                        <input
                                            type="text"
                                            id="downtime_length"
                                            wire:model.live="corrective.downtime_length"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs sm:text-sm py-1.5 px-2
                                            @error('corrective.downtime_length') border-red-300 text-red-900 @enderror"
                                            {{ !empty($corrective['start_time']) && !empty($corrective['end_time']) ? 'readonly' : '' }}
                                        >
                                        @error('corrective.downtime_length')
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                            </div>
                                        @enderror
                                    </div>
                                    @error('corrective.downtime_length')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @else
                                        <p class="mt-1 text-xs text-gray-500 flex items-center">
                                            <i class="fas fa-calculator mr-1"></i> Calculated automatically when start and end times are set
                                        </p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description and Actions Section -->
                    <div class="bg-gray-50 p-3 rounded-md mb-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-file-alt mr-2 text-blue-500"></i> Details
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                            <div>
                                <label for="description" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-align-left mr-1 text-gray-500"></i> Description
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <textarea
                                        id="description"
                                        wire:model.live="corrective.description"
                                        rows="4"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs sm:text-sm py-1.5 px-2
                                        @error('corrective.description') border-red-300 text-red-900 @enderror"
                                        placeholder="Describe the issue in detail..."
                                    ></textarea>
                                    @error('corrective.description')
                                        <div class="absolute top-0 right-0 pr-3 pt-3 flex items-center pointer-events-none">
                                            <i class="fas fa-exclamation-circle text-red-500"></i>
                                        </div>
                                    @enderror
                                </div>
                                @error('corrective.description')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="actions_taken" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-tasks mr-1 text-gray-500"></i> Actions Taken
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <textarea
                                        id="actions_taken"
                                        wire:model.live="corrective.actions_taken"
                                        rows="4"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs sm:text-sm py-1.5 px-2
                                        @error('corrective.actions_taken') border-red-300 text-red-900 @enderror"
                                        placeholder="Describe the actions taken to resolve the issue..."
                                    ></textarea>
                                    @error('corrective.actions_taken')
                                        <div class="absolute top-0 right-0 pr-3 pt-3 flex items-center pointer-events-none">
                                            <i class="fas fa-exclamation-circle text-red-500"></i>
                                        </div>
                                    @enderror
                                </div>
                                @error('corrective.actions_taken')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Personnel Section -->
                    <div class="bg-gray-50 p-3 rounded-md mb-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-users mr-2 text-blue-500"></i> Personnel
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                            <div>
                                <label for="reported_by" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-user-edit mr-1 text-gray-500"></i> Reported By
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="reported_by"
                                        wire:model.live="corrective.reported_by"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs sm:text-sm py-1.5 px-2
                                        @error('corrective.reported_by') border-red-300 text-red-900 @enderror"
                                    >
                                        <option value="">Select User</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('corrective.reported_by')
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i class="fas fa-exclamation-circle text-red-500"></i>
                                        </div>
                                    @enderror
                                </div>
                                @error('corrective.reported_by')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="resolved_by" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-user-check mr-1 text-gray-500"></i> Resolved By
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="resolved_by"
                                        wire:model.live="corrective.resolved_by"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs sm:text-sm py-1.5 px-2
                                        @error('corrective.resolved_by') border-red-300 text-red-900 @enderror"
                                        {{ $corrective['status'] !== 'resolved' && $corrective['status'] !== 'closed' ? 'disabled' : '' }}
                                    >
                                        <option value="">Select Technician</option>
                                        @foreach($technicians as $technician)
                                            <option value="{{ $technician->id }}">{{ $technician->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('corrective.resolved_by')
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i class="fas fa-exclamation-circle text-red-500"></i>
                                        </div>
                                    @enderror
                                </div>
                                @error('corrective.resolved_by')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @else
                                    <p class="mt-1 text-xs text-gray-500 flex items-center">
                                        <i class="fas fa-info-circle mr-1"></i> Only applicable for resolved or closed status
                                    </p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-4">
                        <button
                            type="button"
                            class="px-3 py-1.5 sm:px-4 sm:py-2 border border-gray-300 rounded-md shadow-sm text-xs sm:text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            wire:click="closeModal"
                        >
                            <i class="fas fa-times mr-1"></i> Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-3 py-1.5 sm:px-4 sm:py-2 border border-transparent rounded-md shadow-sm text-xs sm:text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            <i class="fas {{ $isEditing ? 'fa-save' : 'fa-plus' }} mr-1"></i>
                            {{ $isEditing ? 'Update' : 'Save' }} Record
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- View Modal -->
    @if($showViewModal && $viewingCorrective)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-6xl p-6 overflow-y-auto max-h-[90vh]">
                <!-- Enhanced Header with Icon -->
                <div class="flex justify-between items-center mb-6 border-b border-gray-200 pb-4">
                    <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                        <span class="bg-blue-100 text-blue-600 p-2 rounded-full mr-3">
                            <i class="fas fa-clipboard-check text-lg"></i>
                        </span>
                        Downtime Record Details
                    </h3>
                    <div class="flex items-center space-x-2">
                        <button
                            type="button"
                            class="bg-indigo-100 text-indigo-600 hover:bg-indigo-200 transition-colors duration-150 p-2 rounded-full"
                            wire:click="edit({{ $viewingCorrective->id }})"
                            title="Edit Record"
                        >
                            <i class="fas fa-edit"></i>
                        </button>
                        <button
                            type="button"
                            class="bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors duration-150 p-2 rounded-full"
                            wire:click="closeViewModal"
                            title="Close"
                        >
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Status Summary Card -->
                <div class="bg-gray-50 rounded-lg mb-6 shadow-sm">
                    <div class="p-4 border-b border-gray-200">
                        <div class="flex flex-col md:flex-row md:justify-between md:items-center">
                            <div class="flex items-center mb-3 md:mb-0">
                                <span class="px-3 py-1.5 inline-flex items-center rounded-full text-sm font-medium
                                    {{ $viewingCorrective->status === 'open' ? 'bg-red-100 text-red-800' :
                                       ($viewingCorrective->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' :
                                       ($viewingCorrective->status === 'resolved' ? 'bg-green-100 text-green-800' :
                                       'bg-gray-100 text-gray-800')) }}">
                                    <i class="fas
                                        {{ $viewingCorrective->status === 'open' ? 'fa-exclamation-circle' :
                                           ($viewingCorrective->status === 'in_progress' ? 'fa-spinner fa-spin' :
                                           ($viewingCorrective->status === 'resolved' ? 'fa-check-circle' :
                                           'fa-question-circle')) }} mr-2"></i>
                                    {{ $statuses[$viewingCorrective->status] }}
                                </span>
                                <span class="ml-4 bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-sm flex items-center">
                                    <i class="fas fa-hashtag mr-1"></i>
                                    ID: {{ $viewingCorrective->id }}
                                </span>
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="far fa-calendar-alt mr-2"></i>
                                <span>{{ $viewingCorrective->year }}/{{ $months[$viewingCorrective->month] ?? $viewingCorrective->month }} (Week {{ $viewingCorrective->week }})</span>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4">
                        <div class="flex items-start">
                            <span class="bg-blue-50 p-2 rounded-full text-blue-600 mr-3">
                                <i class="fas fa-tools"></i>
                            </span>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Equipment</p>
                                <p class="text-sm font-medium">{{ $viewingCorrective->equipment->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <span class="bg-blue-50 p-2 rounded-full text-blue-600 mr-3">
                                <i class="fas fa-sitemap"></i>
                            </span>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">System/Process</p>
                                <p class="text-sm font-medium">{{ $viewingCorrective->system_process ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Sections -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Downtime Information -->
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                        <div class="bg-blue-50 px-4 py-2 rounded-t-lg flex items-center">
                            <i class="fas fa-clock text-blue-600 mr-2"></i>
                            <h4 class="text-sm font-semibold text-blue-800">Downtime Information</h4>
                        </div>
                        <div class="p-4">
                            <div class="mb-4 flex">
                                <div class="w-8 text-center mr-2">
                                    <i class="fas fa-play-circle text-green-500"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">Start Time</p>
                                    <p class="text-sm">{{ $viewingCorrective->start_time ? $viewingCorrective->start_time->format('Y/m/d H:i:s') : 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="mb-4 flex">
                                <div class="w-8 text-center mr-2">
                                    <i class="fas fa-stop-circle text-red-500"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">End Time</p>
                                    <p class="text-sm">{{ $viewingCorrective->end_time ? $viewingCorrective->end_time->format('Y/m/d H:i:s') : 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="flex">
                                <div class="w-8 text-center mr-2">
                                    <i class="fas fa-hourglass-half text-blue-500"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">Downtime Length</p>
                                    <p class="text-sm font-semibold">{{ $viewingCorrective->formatted_downtime }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Failure Information -->
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                        <div class="bg-orange-50 px-4 py-2 rounded-t-lg flex items-center">
                            <i class="fas fa-exclamation-triangle text-orange-500 mr-2"></i>
                            <h4 class="text-sm font-semibold text-orange-800">Failure Information</h4>
                        </div>
                        <div class="p-4">
                            <div class="mb-4 flex">
                                <div class="w-8 text-center mr-2">
                                    <i class="fas fa-times-circle text-red-500"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">Failure Mode</p>
                                    <p class="text-sm">{{ $viewingCorrective->failure_mode_name ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="mb-4 flex">
                                <div class="w-8 text-center mr-2">
                                    <i class="fas fa-tags text-blue-500"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">Failure Mode Category</p>
                                    <p class="text-sm">{{ $viewingCorrective->failure_mode_category ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="mb-4 flex">
                                <div class="w-8 text-center mr-2">
                                    <i class="fas fa-search text-indigo-500"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">Failure Cause</p>
                                    <p class="text-sm">{{ $viewingCorrective->failure_cause_name ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="flex">
                                <div class="w-8 text-center mr-2">
                                    <i class="fas fa-tag text-purple-500"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">Failure Cause Category</p>
                                    <p class="text-sm">{{ $viewingCorrective->failure_cause_category ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Description -->
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                        <div class="bg-gray-50 px-4 py-2 rounded-t-lg flex items-center">
                            <i class="fas fa-align-left text-gray-600 mr-2"></i>
                            <h4 class="text-sm font-semibold text-gray-700">Description</h4>
                        </div>
                        <div class="p-4 h-40 overflow-y-auto">
                            @if($viewingCorrective->description)
                                <p class="text-sm leading-relaxed">{{ $viewingCorrective->description }}</p>
                            @else
                                <div class="flex items-center justify-center h-full text-gray-400">
                                    <i class="fas fa-file-alt mr-2"></i>
                                    <span>No description provided</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions Taken -->
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                        <div class="bg-gray-50 px-4 py-2 rounded-t-lg flex items-center">
                            <i class="fas fa-tasks text-gray-600 mr-2"></i>
                            <h4 class="text-sm font-semibold text-gray-700">Actions Taken</h4>
                        </div>
                        <div class="p-4 h-40 overflow-y-auto">
                            @if($viewingCorrective->actions_taken)
                                <p class="text-sm leading-relaxed">{{ $viewingCorrective->actions_taken }}</p>
                            @else
                                <div class="flex items-center justify-center h-full text-gray-400">
                                    <i class="fas fa-clipboard-check mr-2"></i>
                                    <span>No actions recorded</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Personnel Information -->
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm mb-6">
                    <div class="bg-indigo-50 px-4 py-2 rounded-t-lg flex items-center">
                        <i class="fas fa-users text-indigo-600 mr-2"></i>
                        <h4 class="text-sm font-semibold text-indigo-800">Personnel Information</h4>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex">
                                <div class="w-8 text-center mr-2">
                                    <i class="fas fa-user-edit text-blue-500"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">Reported By</p>
                                    <p class="text-sm font-medium">{{ $viewingCorrective->reporter->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="flex">
                                <div class="w-8 text-center mr-2">
                                    <i class="fas fa-user-check text-green-500"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">Resolved By</p>
                                    <p class="text-sm font-medium">{{ $viewingCorrective->resolver->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div class="flex justify-end space-x-3 border-t border-gray-200 pt-4">
                    <button
                        type="button"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150 flex items-center"
                        wire:click="closeViewModal"
                    >
                        <i class="fas fa-times mr-2"></i> {{ __('messages.close') }}
                    </button>
                    <button
                        type="button"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150 flex items-center"
                        wire:click="edit({{ $viewingCorrective->id }})"
                    >
                        <i class="fas fa-edit mr-2"></i> {{ __('messages.edit_record') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-red-600 flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        {{ __('messages.delete_downtime_record') }}
                    </h3>
                    <button type="button" class="text-gray-500 hover:text-gray-700 text-xl" wire:click="closeModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="bg-red-50 p-4 rounded-md mb-4">
                    <p class="text-sm text-red-700">
                        {{ __('messages.delete_downtime_confirm') }}
                    </p>
                </div>

                <div class="flex justify-end space-x-3">
                    <button
                        type="button"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        wire:click="closeModal"
                    >
                        <i class="fas fa-times mr-1"></i> {{ __('messages.cancel') }}
                    </button>
                    <button
                        type="button"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        wire:click="delete"
                    >
                        <i class="fas fa-trash-alt mr-1"></i> {{ __('messages.delete') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
    
    <!-- Calendar Section -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg w-full mt-8">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="far fa-calendar-alt mr-2 text-gray-600"></i> {{ __('messages.corrective_maintenance_calendar') }}
                </h2>
                <div class="text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i> {{ __('messages.calendar_shows_corrective_events') }}
                </div>
            </div>
            
            <!-- Corrective Maintenance Calendar Component -->
            <livewire:maintenance.corrective-calendar />
        </div>
    </div>
</div>
