<div>
    <!-- JavaScript for Notifications -->
    <script>
        function showNotification(message, type = 'success') {
            if (window.toastr) {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-top-right',
                    timeOut: 5000
                };

                toastr[type](message);
            } else {
                alert(message);
            }
        }

        document.addEventListener('livewire:initialized', () => {
            Livewire.on('notify', (params) => {
                console.log('Notification event received:', params);
                showNotification(params.message, params.type);
            });
        });
    </script>

    <div class="py-6 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg w-full">
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-wrench mr-2 text-gray-600"></i> Equipment/Machine Downtime Reporting
                        </h2>
                        <button
                            wire:click="openModal"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                            <i class="fas fa-plus-circle mr-1"></i> Report Downtime
                        </button>
                    </div>

                    <!-- Filters and Search -->
                    <div class="mb-6 bg-white p-4 rounded-lg shadow-sm">
                        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                            <div class="md:col-span-2">
                                <label for="search" class="sr-only">Search</label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                    <input
                                        type="text"
                                        id="search"
                                        wire:model.live.debounce.300ms="search"
                                        placeholder="Search for equipment, failure modes..."
                                        class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 pr-3 py-2 sm:text-sm border-gray-300 rounded-md"
                                    >
                                </div>
                            </div>

                            <div>
                                <label for="filterYear" class="sr-only">Year</label>
                                <select
                                    id="filterYear"
                                    wire:model.live="filterYear"
                                    class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                                    <option value="">All Years</option>
                                    @foreach($years as $yearKey => $yearValue)
                                        <option value="{{ $yearKey }}">{{ $yearValue }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="filterMonth" class="sr-only">Month</label>
                                <select
                                    id="filterMonth"
                                    wire:model.live="filterMonth"
                                    class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                                    <option value="">All Months</option>
                                    @foreach($months as $monthKey => $monthValue)
                                        <option value="{{ $monthKey }}">{{ $monthValue }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="filterEquipment" class="sr-only">Equipment</label>
                                <select
                                    id="filterEquipment"
                                    wire:model.live="filterEquipment"
                                    class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                                    <option value="">All Equipment</option>
                                    @foreach($equipment as $equipItem)
                                        <option value="{{ $equipItem->id }}">{{ $equipItem->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="filterStatus" class="sr-only">Status</label>
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
                            </div>
                        </div>
                    </div>

                    <!-- Corrective Maintenance Records Table -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg w-full">
                        <div class="overflow-x-auto w-full">
                            <table class="min-w-full divide-y divide-gray-200 w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('year')">
                                            <div class="flex items-center space-x-1">
                                                <span>Year</span>
                                                @if($sortField === 'year')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-gray-400"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('month')">
                                            <div class="flex items-center space-x-1">
                                                <span>Month</span>
                                                @if($sortField === 'month')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-gray-400"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('week')">
                                            <div class="flex items-center space-x-1">
                                                <span>Week#</span>
                                                @if($sortField === 'week')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-gray-400"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('system_process')">
                                            <div class="flex items-center space-x-1">
                                                <span>System/Process</span>
                                                @if($sortField === 'system_process')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-gray-400"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1">
                                                <span>Equipment ID</span>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('failure_mode')">
                                            <div class="flex items-center space-x-1">
                                                <span>Failure Modes</span>
                                                @if($sortField === 'failure_mode')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-gray-400"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1">
                                                <span>Failure Modes Categories</span>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('start_time')">
                                            <div class="flex items-center space-x-1">
                                                <span>Start Time</span>
                                                @if($sortField === 'start_time')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-gray-400"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('end_time')">
                                            <div class="flex items-center space-x-1">
                                                <span>End Time</span>
                                                @if($sortField === 'end_time')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-gray-400"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('downtime_length')">
                                            <div class="flex items-center space-x-1">
                                                <span>Downtime Length</span>
                                                @if($sortField === 'downtime_length')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-gray-400"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('failure_cause')">
                                            <div class="flex items-center space-x-1">
                                                <span>Failure Causes</span>
                                                @if($sortField === 'failure_cause')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-gray-400"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1">
                                                <span>Failures Causes Categories</span>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1">
                                                <span>Status</span>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($correctiveRecords as $record)
                                        <tr>
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
                                                {{ $record->equipment->name ?? 'N/A' }}
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
                                                {{ $record->formatted_downtime }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $record->failure_cause_name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $record->failure_cause_category ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    {{ $record->status === 'open' ? 'bg-red-100 text-red-800' :
                                                       ($record->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' :
                                                       ($record->status === 'resolved' ? 'bg-green-100 text-green-800' :
                                                       'bg-gray-100 text-gray-800')) }}">
                                                    {{ $statuses[$record->status] }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button
                                                    wire:click.prevent="view({{ $record->id }})"
                                                    class="text-blue-600 hover:text-blue-900 mx-1"
                                                    title="View Details"
                                                >
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button
                                                    wire:click="edit({{ $record->id }})"
                                                    class="text-indigo-600 hover:text-indigo-900 mx-1"
                                                    title="Edit"
                                                >
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button
                                                    wire:click="confirmDelete({{ $record->id }})"
                                                    class="text-red-600 hover:text-red-900 mx-1"
                                                    title="Delete"
                                                >
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="13" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                                <div class="flex flex-col items-center justify-center py-8">
                                                    <i class="fas fa-clipboard-list text-gray-400 text-4xl mb-4"></i>
                                                    <p class="text-lg font-medium">No downtime records found</p>
                                                    <p class="text-sm text-gray-500 mt-1">
                                                        @if($search || $filterStatus || $filterEquipment || $filterYear || $filterMonth)
                                                            Try adjusting your search filters or
                                                            <button
                                                                wire:click="$set('search', ''), $set('filterStatus', ''), $set('filterEquipment', ''), $set('filterYear', ''), $set('filterMonth', '')"
                                                                class="text-blue-600 hover:text-blue-800 underline"
                                                            >
                                                                clear all filters
                                                            </button>
                                                        @else
                                                            Click "Report Downtime" to record a new equipment failure
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
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-6xl p-6 overflow-y-auto max-h-[90vh]">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas {{ $isEditing ? 'fa-edit' : 'fa-plus-circle' }} mr-2"></i>
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
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <!-- Year, Month, Week -->
                        <div>
                            <label for="year" class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                            <div class="relative rounded-md shadow-sm">
                                <input
                                    type="number"
                                    id="year"
                                    wire:model.live="corrective.year"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm
                                    @error('corrective.year') border-red-300 text-red-900 @enderror"
                                >
                                @error('corrective.year')
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                    </div>
                                @enderror
                            </div>
                            @error('corrective.year')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                            <div class="relative rounded-md shadow-sm">
                                <select
                                    id="month"
                                    wire:model.live="corrective.month"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm
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
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="week" class="block text-sm font-medium text-gray-700 mb-1">Week #</label>
                            <div class="relative rounded-md shadow-sm">
                                <input
                                    type="number"
                                    id="week"
                                    wire:model.live="corrective.week"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm
                                    @error('corrective.week') border-red-300 text-red-900 @enderror"
                                >
                                @error('corrective.week')
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                    </div>
                                @enderror
                            </div>
                            @error('corrective.week')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- System Process, Equipment ID -->
                        <div>
                            <label for="system_process" class="block text-sm font-medium text-gray-700 mb-1">System/Process</label>
                            <div class="relative rounded-md shadow-sm">
                                <input
                                    type="text"
                                    id="system_process"
                                    wire:model.live="corrective.system_process"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm
                                    @error('corrective.system_process') border-red-300 text-red-900 @enderror"
                                >
                                @error('corrective.system_process')
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                    </div>
                                @enderror
                            </div>
                            @error('corrective.system_process')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="equipment_id" class="block text-sm font-medium text-gray-700 mb-1">Equipment</label>
                            <div class="relative rounded-md shadow-sm">
                                <select
                                    id="equipment_id"
                                    wire:model.live="corrective.equipment_id"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm
                                    @error('corrective.equipment_id') border-red-300 text-red-900 @enderror"
                                >
                                    <option value="">Select Equipment</option>
                                    @foreach($equipment as $equipItem)
                                        <option value="{{ $equipItem->id }}">{{ $equipItem->name }}</option>
                                    @endforeach
                                </select>
                                @error('corrective.equipment_id')
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                    </div>
                                @enderror
                            </div>
                            @error('corrective.equipment_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <div class="relative rounded-md shadow-sm">
                                <select
                                    id="status"
                                    wire:model.live="corrective.status"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm
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
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Failure Mode Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <!-- Failure Mode -->
                            <div>
                                <label for="failure_mode_id" class="block text-sm font-medium text-gray-700 mb-1">Failure Mode</label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="failure_mode_id"
                                        wire:model.live="corrective.failure_mode_id"
                                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm
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
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Failure Mode Category -->
                            <div>
                                <label for="failure_mode_category_id" class="block text-sm font-medium text-gray-700 mb-1">Failure Mode Category</label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="failure_mode_category_id"
                                        wire:model.live="corrective.failure_mode_category_id"
                                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    >
                                        <option value="">Select Failure Mode Category</option>
                                        @foreach($modeCategories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Failure Cause -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <!-- Failure Cause -->
                            <div>
                                <label for="failure_cause_id" class="block text-sm font-medium text-gray-700 mb-1">Failure Cause</label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="failure_cause_id"
                                        wire:model.live="corrective.failure_cause_id"
                                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm
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
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Failure Cause Category -->
                            <div>
                                <label for="failure_cause_category_id" class="block text-sm font-medium text-gray-700 mb-1">Failure Cause Category</label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="failure_cause_category_id"
                                        wire:model.live="corrective.failure_cause_category_id"
                                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    >
                                        <option value="">Select Failure Cause Category</option>
                                        @foreach($causeCategories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Time Info -->
                        <div>
                            <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                            <div class="relative rounded-md shadow-sm">
                                <input
                                    type="datetime-local"
                                    id="start_time"
                                    wire:model.live="corrective.start_time"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm
                                    @error('corrective.start_time') border-red-300 text-red-900 @enderror"
                                >
                                @error('corrective.start_time')
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                    </div>
                                @enderror
                            </div>
                            @error('corrective.start_time')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="end_time" class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                            <div class="relative rounded-md shadow-sm">
                                <input
                                    type="datetime-local"
                                    id="end_time"
                                    wire:model.live="corrective.end_time"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm
                                    @error('corrective.end_time') border-red-300 text-red-900 @enderror"
                                >
                                @error('corrective.end_time')
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                    </div>
                                @enderror
                            </div>
                            @error('corrective.end_time')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="downtime_length" class="block text-sm font-medium text-gray-700 mb-1">Downtime Length (hours)</label>
                            <div class="relative rounded-md shadow-sm">
                                <input
                                    type="text"
                                    id="downtime_length"
                                    wire:model.live="corrective.downtime_length"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm
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
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @else
                                <p class="mt-1 text-xs text-gray-500">Calculated automatically when start and end times are set</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Description and Actions -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <div class="relative rounded-md shadow-sm">
                                <textarea
                                    id="description"
                                    wire:model.live="corrective.description"
                                    rows="4"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm
                                    @error('corrective.description') border-red-300 text-red-900 @enderror"
                                ></textarea>
                                @error('corrective.description')
                                    <div class="absolute top-0 right-0 pr-3 pt-3 flex items-center pointer-events-none">
                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                    </div>
                                @enderror
                            </div>
                            @error('corrective.description')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="actions_taken" class="block text-sm font-medium text-gray-700 mb-1">Actions Taken</label>
                            <div class="relative rounded-md shadow-sm">
                                <textarea
                                    id="actions_taken"
                                    wire:model.live="corrective.actions_taken"
                                    rows="4"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm
                                    @error('corrective.actions_taken') border-red-300 text-red-900 @enderror"
                                ></textarea>
                                @error('corrective.actions_taken')
                                    <div class="absolute top-0 right-0 pr-3 pt-3 flex items-center pointer-events-none">
                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                    </div>
                                @enderror
                            </div>
                            @error('corrective.actions_taken')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Personnel -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="reported_by" class="block text-sm font-medium text-gray-700 mb-1">Reported By</label>
                            <div class="relative rounded-md shadow-sm">
                                <select
                                    id="reported_by"
                                    wire:model.live="corrective.reported_by"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm
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
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="resolved_by" class="block text-sm font-medium text-gray-700 mb-1">Resolved By</label>
                            <div class="relative rounded-md shadow-sm">
                                <select
                                    id="resolved_by"
                                    wire:model.live="corrective.resolved_by"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm
                                    @error('corrective.resolved_by') border-red-300 text-red-900 @enderror"
                                    {{ $corrective['status'] !== 'resolved' && $corrective['status'] !== 'closed' ? 'disabled' : '' }}
                                >
                                    <option value="">Select User</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                @error('corrective.resolved_by')
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                    </div>
                                @enderror
                            </div>
                            @error('corrective.resolved_by')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @else
                                <p class="mt-1 text-xs text-gray-500">Only applicable for resolved or closed status</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button
                            type="button"
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            wire:click="closeModal"
                        >
                            <i class="fas fa-times mr-1"></i> Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
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
            <div class="bg-white rounded-lg shadow-lg w-full max-w-6xl p-6 overflow-y-auto max-h-[90vh]">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-clipboard-list mr-2"></i>
                        Downtime Record Details
                    </h3>
                    <button type="button" class="text-gray-500 hover:text-gray-700 text-xl" wire:click="closeViewModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                    <div class="flex justify-between mb-2">
                        <div>
                            <span class="text-xs text-gray-500">Status:</span>
                            <span class="ml-1 px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ $viewingCorrective->status === 'open' ? 'bg-red-100 text-red-800' :
                                   ($viewingCorrective->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' :
                                   ($viewingCorrective->status === 'resolved' ? 'bg-green-100 text-green-800' :
                                   'bg-gray-100 text-gray-800')) }}">
                                {{ $statuses[$viewingCorrective->status] }}
                            </span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500">Record ID:</span>
                            <span class="text-sm font-medium ml-1">{{ $viewingCorrective->id }}</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="text-xs text-gray-500">Equipment:</span>
                            <p class="text-sm font-medium">{{ $viewingCorrective->equipment->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500">System/Process:</span>
                            <p class="text-sm">{{ $viewingCorrective->system_process ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500">Date:</span>
                            <p class="text-sm">{{ $viewingCorrective->year }}/{{ $months[$viewingCorrective->month] ?? $viewingCorrective->month }} (Week {{ $viewingCorrective->week }})</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Downtime Information</h4>
                        <div class="bg-white p-3 border border-gray-200 rounded-md">
                            <div class="mb-3">
                                <span class="text-xs text-gray-500">Start Time:</span>
                                <p class="text-sm">{{ $viewingCorrective->start_time ? $viewingCorrective->start_time->format('Y/m/d H:i:s') : 'N/A' }}</p>
                            </div>
                            <div class="mb-3">
                                <span class="text-xs text-gray-500">End Time:</span>
                                <p class="text-sm">{{ $viewingCorrective->end_time ? $viewingCorrective->end_time->format('Y/m/d H:i:s') : 'N/A' }}</p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500">Downtime Length:</span>
                                <p class="text-sm font-medium">{{ $viewingCorrective->formatted_downtime }}</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Failure Information</h4>
                        <div class="bg-white p-3 border border-gray-200 rounded-md">
                            <div class="mb-3">
                                <span class="text-xs text-gray-500">Failure Mode:</span>
                                <p class="text-sm">{{ $viewingCorrective->failure_mode_name ?? 'N/A' }}</p>
                            </div>
                            <div class="mb-3">
                                <span class="text-xs text-gray-500">Failure Mode Category:</span>
                                <p class="text-sm">{{ $viewingCorrective->failure_mode_category ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500">Failure Cause:</span>
                                <p class="text-sm">{{ $viewingCorrective->failure_cause_name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500">Failure Cause Category:</span>
                                <p class="text-sm">{{ $viewingCorrective->failure_cause_category ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Description</h4>
                        <div class="bg-white p-3 border border-gray-200 rounded-md h-32 overflow-y-auto">
                            <p class="text-sm">{{ $viewingCorrective->description ?? 'No description provided.' }}</p>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Actions Taken</h4>
                        <div class="bg-white p-3 border border-gray-200 rounded-md h-32 overflow-y-auto">
                            <p class="text-sm">{{ $viewingCorrective->actions_taken ?? 'No actions recorded.' }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Reported By</h4>
                        <div class="bg-white p-3 border border-gray-200 rounded-md">
                            <p class="text-sm">{{ $viewingCorrective->reporter->name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Resolved By</h4>
                        <div class="bg-white p-3 border border-gray-200 rounded-md">
                            <p class="text-sm">{{ $viewingCorrective->resolver->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button
                        type="button"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        wire:click="closeViewModal"
                    >
                        <i class="fas fa-times mr-1"></i> Close
                    </button>
                    <button
                        type="button"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        wire:click="edit({{ $viewingCorrective->id }})"
                    >
                        <i class="fas fa-edit mr-1"></i> Edit
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
                        Delete Downtime Record
                    </h3>
                    <button type="button" class="text-gray-500 hover:text-gray-700 text-xl" wire:click="closeModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="bg-red-50 p-4 rounded-md mb-4">
                    <p class="text-sm text-red-700">
                        Are you sure you want to delete this downtime record? This action cannot be undone, and all associated data will be permanently removed.
                    </p>
                </div>

                <div class="flex justify-end space-x-3">
                    <button
                        type="button"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        wire:click="closeModal"
                    >
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                    <button
                        type="button"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        wire:click="delete"
                    >
                        <i class="fas fa-trash-alt mr-1"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
