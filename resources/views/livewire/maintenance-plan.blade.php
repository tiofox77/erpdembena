<div>
    <div class="py-2 sm:py-4">
        <div class="max-w-full mx-auto px-2 sm:px-4 lg:px-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2 sm:mb-0 flex items-center">
                    <i class="fas fa-calendar-alt mr-3 text-gray-700"></i> Maintenance Scheduling
                </h1>
                <button
                    type="button"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-1.5 px-3 sm:py-2 sm:px-4 rounded flex items-center"
                    wire:click="openModal"
                >
                    <i class="fas fa-plus-circle mr-2"></i> Add Schedule
                </button>
            </div>

            <!-- Filters and Table Section -->
            <div class="bg-white rounded-lg shadow mb-4 sm:mb-6">
                <div class="p-2 sm:p-4">
                    <!-- Enhanced Filter Section -->
                    <div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                        <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                            <i class="fas fa-filter mr-2 text-blue-500"></i> Filters and Search
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label for="search" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-search mr-1 text-gray-500"></i> Search
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                    <input
                                        type="text"
                                        wire:model.live.debounce.300ms="search"
                                        id="search"
                                        placeholder="Search equipment..."
                                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                </div>
                            </div>
                            <div>
                                <label for="statusFilter" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-tasks mr-1 text-gray-500"></i> Status
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="statusFilter"
                                        wire:model.live="statusFilter"
                                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                        <option value="">All Statuses</option>
                                        <option value="pending">Pending</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label for="frequencyFilter" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-sync-alt mr-1 text-gray-500"></i> Frequency
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="frequencyFilter"
                                        wire:model.live="frequencyFilter"
                                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                        <option value="">All Frequencies</option>
                                        <option value="once">Once</option>
                                        <option value="daily">Daily</option>
                                        <option value="custom">Custom</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                        <option value="yearly">Yearly</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button
                                wire:click="clearFilters"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150 flex items-center"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-75 cursor-wait"
                            >
                                <i class="fas fa-eraser mr-2" wire:loading.class="hidden" wire:target="clearFilters"></i>
                                <i class="fas fa-spinner fa-spin mr-2 hidden" wire:loading.class.remove="hidden" wire:target="clearFilters"></i>
                                <span wire:loading.remove wire:target="clearFilters">Clear Filters</span>
                                <span wire:loading wire:target="clearFilters">Clearing...</span>
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <!-- Enhanced Table Headers with Icons -->
                        <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th scope="col" class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <div class="flex items-center space-x-1">
                                            <i class="fas fa-clipboard-list text-gray-400 mr-1"></i>
                                            <span>Task</span>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <div class="flex items-center space-x-1">
                                            <i class="fas fa-tools text-gray-400 mr-1"></i>
                                            <span>Equipment</span>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">
                                        <div class="flex items-center space-x-1">
                                            <i class="fas fa-sync-alt text-gray-400 mr-1"></i>
                                            <span>Frequency</span>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">
                                        <div class="flex items-center space-x-1">
                                            <i class="fas fa-history text-gray-400 mr-1"></i>
                                            <span>Last Maint.</span>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">
                                        <div class="flex items-center space-x-1">
                                            <i class="fas fa-calendar-day text-gray-400 mr-1"></i>
                                            <span>Next Maint.</span>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <div class="flex items-center space-x-1">
                                            <i class="fas fa-tasks text-gray-400 mr-1"></i>
                                            <span>Status</span>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-2 sm:px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <div class="flex items-center justify-end space-x-1">
                                            <i class="fas fa-cog text-gray-400 mr-1"></i>
                                            <span>Actions</span>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($schedules as $schedule)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-2 sm:px-4 py-2 whitespace-nowrap">
                                            <div class="text-xs sm:text-sm font-medium text-gray-900">{{ $schedule->task->title ?? 'No Task' }}</div>
                                        </td>
                                        <td class="px-2 sm:px-4 py-2 whitespace-nowrap">
                                            <div class="text-xs sm:text-sm font-medium text-gray-900">{{ $schedule->equipment->name ?? 'No Equipment' }}</div>
                                            <div class="text-xs text-gray-500 hidden sm:block">{{ $schedule->equipment->serial_number ?? 'No S/N' }}</div>
                                        </td>
                                        <td class="px-2 sm:px-4 py-2 whitespace-nowrap text-xs sm:text-sm text-gray-500 hidden sm:table-cell">
                                            @switch($schedule->frequency_type)
                                                @case('once')
                                                    Once
                                                    @break
                                                @case('daily')
                                                    Daily
                                                    @break
                                                @case('custom')
                                                    Every {{ $schedule->custom_days }} days
                                                    @break
                                                @case('weekly')
                                                    Weekly {{ isset($schedule->day_of_week) ? '(' . ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][$schedule->day_of_week] . ')' : '' }}
                                                    @break
                                                @case('monthly')
                                                    Monthly {{ isset($schedule->day_of_month) ? '(day ' . $schedule->day_of_month . ')' : '' }}
                                                    @break
                                                @case('yearly')
                                                    Yearly {{ isset($schedule->month) && isset($schedule->month_day) ? '(' . ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'][$schedule->month] . ' ' . $schedule->month_day . ')' : '' }}
                                                    @break
                                                @default
                                                    Unknown frequency
                                            @endswitch
                                        </td>
                                        <td class="px-2 sm:px-4 py-2 whitespace-nowrap text-xs sm:text-sm text-gray-500 hidden md:table-cell">
                                            {{ $schedule->last_maintenance_date ? $schedule->last_maintenance_date->format('M d, Y') : 'Not set' }}
                                        </td>
                                        <td class="px-2 sm:px-4 py-2 whitespace-nowrap text-xs sm:text-sm text-gray-500 hidden md:table-cell">
                                            {{ $schedule->next_maintenance_date ? $schedule->next_maintenance_date->format('M d, Y') : 'Not scheduled' }}
                                        </td>
                                        <td class="px-2 sm:px-4 py-2 whitespace-nowrap">
                                            @if($schedule->status === 'pending')
                                                <span class="px-2 py-0.5 sm:px-3 sm:py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Pending
                                                </span>
                                            @elseif($schedule->status === 'in_progress')
                                                <span class="px-2 py-0.5 sm:px-3 sm:py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    <i class="fas fa-spinner fa-spin mr-1"></i> In Progress
                                                </span>
                                            @elseif($schedule->status === 'completed')
                                                <span class="px-2 py-0.5 sm:px-3 sm:py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Completed
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 sm:px-3 sm:py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    Cancelled
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-2 sm:px-4 py-2 whitespace-nowrap text-right text-xs sm:text-sm">
                                            <div class="flex justify-end space-x-1 sm:space-x-2">
                                                <button wire:click="viewSchedule({{ $schedule->id }})" class="text-blue-600 hover:text-blue-900" title="View Details" wire:loading.attr="disabled">
                                                    <i class="fas fa-eye w-4 h-4 sm:w-5 sm:h-5" wire:loading.class="hidden" wire:target="viewSchedule({{ $schedule->id }})"></i>
                                                    <i class="fas fa-spinner fa-spin w-4 h-4 sm:w-5 sm:h-5 hidden" wire:loading.class.remove="hidden" wire:target="viewSchedule({{ $schedule->id }})"></i>
                                                </button>
                                                <button wire:click="edit({{ $schedule->id }})" class="text-indigo-600 hover:text-indigo-900" title="Edit" wire:loading.attr="disabled">
                                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" wire:loading.class="hidden" wire:target="edit({{ $schedule->id }})" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                    <i class="fas fa-spinner fa-spin w-4 h-4 sm:w-5 sm:h-5 hidden" wire:loading.class.remove="hidden" wire:target="edit({{ $schedule->id }})"></i>
                                                </button>
                                                <button wire:click="openHistory({{ $schedule->id }})" class="text-blue-600 hover:text-blue-900" title="View History" wire:loading.attr="disabled">
                                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" wire:loading.class="hidden" wire:target="openHistory({{ $schedule->id }})" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <i class="fas fa-spinner fa-spin w-4 h-4 sm:w-5 sm:h-5 hidden" wire:loading.class.remove="hidden" wire:target="openHistory({{ $schedule->id }})"></i>
                                                </button>
                                                <button wire:click="delete({{ $schedule->id }})" wire:confirm="Are you sure you want to delete this schedule?" class="text-red-600 hover:text-red-900" title="Delete" wire:loading.attr="disabled">
                                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" wire:loading.class="hidden" wire:target="delete({{ $schedule->id }})" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                    <i class="fas fa-spinner fa-spin w-4 h-4 sm:w-5 sm:h-5 hidden" wire:loading.class.remove="hidden" wire:target="delete({{ $schedule->id }})"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-2 sm:px-4 py-2 whitespace-nowrap text-center text-gray-500">
                                            No maintenance schedules found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $schedules->links() }}
                    </div>
                </div>
            </div>

            <!-- Calendar Section -->
            <div class="bg-white rounded-lg shadow">
                <livewire:maintenance-schedule-calendar />
            </div>
        </div>
    </div>

    <!-- Livewire v3 Modal -->
    @if($showModal)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center overflow-y-auto p-2 sm:p-4">
        <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all w-full max-w-7xl max-h-[90vh] overflow-y-auto">
            <!-- Enhanced Modal Header -->
            <div class="bg-gray-50 px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 flex justify-between items-center sticky top-0 z-10">
                <h3 class="text-base sm:text-lg font-medium text-gray-900 flex items-center">
                    <span class="bg-indigo-100 text-indigo-600 p-2 rounded-full mr-3">
                        <i class="fas {{ $isEditing ? 'fa-edit' : 'fa-calendar-plus' }} text-lg"></i>
                    </span>
                    {{ $isEditing ? 'Edit Maintenance Plan' : 'New Maintenance Plan' }}
                </h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="px-4 sm:px-6 py-3 sm:py-4">
                <p class="text-gray-600 text-xs sm:text-sm mb-3 sm:mb-4">Fill in the details to schedule a new maintenance task.</p>

                <form wire:submit.prevent="save">
                    <div class="bg-gray-50 p-4 rounded-md mb-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                            <i class="fas fa-info-circle mr-2 text-blue-500"></i> Basic Information
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Task & Equipment -->
                            <div>
                                <label for="task_id" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-clipboard-list mr-1 text-gray-500"></i> Task <span class="text-red-500">*</span>
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="task_id"
                                        wire:model="task_id"
                                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('task_id') border-red-300 text-red-900 @enderror"
                                    >
                                        <option value="">Select task</option>
                                        @foreach($tasks as $task)
                                            <option value="{{ $task->id }}">{{ $task->title }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                    </div>
                                    @error('task_id')
                                        <div class="absolute inset-y-0 right-8 pr-3 flex items-center pointer-events-none">
                                            <i class="fas fa-exclamation-circle text-red-500"></i>
                                        </div>
                                    @enderror
                                </div>
                                @error('task_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="equipment_id" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-tools mr-1 text-gray-500"></i> Equipment <span class="text-red-500">*</span>
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="equipment_id"
                                        wire:model="equipment_id"
                                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('equipment_id') border-red-300 text-red-900 @enderror"
                                    >
                                        <option value="">Select equipment</option>
                                        @foreach($equipment as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                    </div>
                                    @error('equipment_id')
                                        <div class="absolute inset-y-0 right-8 pr-3 flex items-center pointer-events-none">
                                            <i class="fas fa-exclamation-circle text-red-500"></i>
                                        </div>
                                    @enderror
                                </div>
                                @error('equipment_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Line & Area -->
                            <div>
                                <label for="line_id" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-project-diagram mr-1 text-gray-500"></i> Line
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="line_id"
                                        wire:model="line_id"
                                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                        <option value="">Select line</option>
                                        @foreach($lines as $line)
                                            <option value="{{ $line->id }}">{{ $line->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                    </div>
                                </div>
                                @error('line_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="area_id" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-map-marker-alt mr-1 text-gray-500"></i> Area
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="area_id"
                                        wire:model="area_id"
                                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                        <option value="">Select area</option>
                                        @foreach($areas as $area)
                                            <option value="{{ $area->id }}">{{ $area->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                    </div>
                                </div>
                                @error('area_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-md mb-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                            <i class="fas fa-calendar-alt mr-2 text-blue-500"></i> Schedule Information
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Scheduled Date & Frequency -->
                            <div>
                                <label for="scheduled_date" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-calendar-day mr-1 text-gray-500"></i> Start Date <span class="text-red-500">*</span>
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <input
                                        type="date"
                                        id="scheduled_date"
                                        wire:model="scheduled_date"
                                        class="block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('scheduled_date') border-red-300 text-red-900 @enderror"
                                    >
                                    @error('scheduled_date')
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i class="fas fa-exclamation-circle text-red-500"></i>
                                        </div>
                                    @enderror
                                </div>
                                @error('scheduled_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="frequency_type" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-sync-alt mr-1 text-gray-500"></i> Frequency <span class="text-red-500">*</span>
                                </label>
                                <div class="flex gap-2">
                                    <div class="relative rounded-md shadow-sm w-full">
                                        <select
                                            id="frequency_type"
                                            wire:model="frequency_type"
                                            class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('frequency_type') border-red-300 text-red-900 @enderror"
                                        >
                                            @foreach($frequencies as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                        </div>
                                    </div>
                                    <input
                                        type="number"
                                        id="custom_days"
                                        wire:model="custom_days"
                                        class="w-24 py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('custom_days') border-red-300 text-red-900 @enderror"
                                        placeholder="Days"
                                        style="display: none;">
                                </div>
                                @error('frequency_type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                @error('custom_days') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Status & Priority -->
                            <div>
                                <label for="status" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-tasks mr-1 text-gray-500"></i> Status
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="status"
                                        wire:model="status"
                                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                        <option value="pending">Pending</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="Scheduled">Scheduled</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                    </div>
                                </div>
                                @error('status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="priority" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-flag mr-1 text-gray-500"></i> Priority
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="priority"
                                        wire:model="priority"
                                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                        @foreach($priorities as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                    </div>
                                </div>
                                @error('priority') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Frequency Option Fields -->
                    @if($frequency_type === 'weekly' || $frequency_type === 'monthly' || $frequency_type === 'yearly')
                    <div class="bg-gray-50 p-4 rounded-md mb-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                            <i class="fas fa-calendar-week mr-2 text-blue-500"></i> {{ ucfirst($frequency_type) }} Schedule Options
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            @if($frequency_type === 'weekly')
                            <div>
                                <label for="day_of_week" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-calendar-day mr-1 text-gray-500"></i> Day of Week <span class="text-red-500">*</span>
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="day_of_week"
                                        wire:model="day_of_week"
                                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('day_of_week') border-red-300 text-red-900 @enderror"
                                    >
                                        <option value="0">Sunday</option>
                                        <option value="1">Monday</option>
                                        <option value="2">Tuesday</option>
                                        <option value="3">Wednesday</option>
                                        <option value="4">Thursday</option>
                                        <option value="5">Friday</option>
                                        <option value="6">Saturday</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                    </div>
                                </div>
                                @error('day_of_week') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            @endif

                            @if($frequency_type === 'monthly')
                            <div>
                                <label for="day_of_month" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-calendar-day mr-1 text-gray-500"></i> Day of Month <span class="text-red-500">*</span>
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="day_of_month"
                                        wire:model="day_of_month"
                                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('day_of_month') border-red-300 text-red-900 @enderror"
                                    >
                                        @for($i = 1; $i <= 31; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                    </div>
                                </div>
                                @error('day_of_month') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            @endif

                            @if($frequency_type === 'yearly')
                            <div>
                                <label for="month" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-calendar-alt mr-1 text-gray-500"></i> Month <span class="text-red-500">*</span>
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="month"
                                        wire:model="month"
                                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('month') border-red-300 text-red-900 @enderror"
                                    >
                                        <option value="1">January</option>
                                        <option value="2">February</option>
                                        <option value="3">March</option>
                                        <option value="4">April</option>
                                        <option value="5">May</option>
                                        <option value="6">June</option>
                                        <option value="7">July</option>
                                        <option value="8">August</option>
                                        <option value="9">September</option>
                                        <option value="10">October</option>
                                        <option value="11">November</option>
                                        <option value="12">December</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                    </div>
                                </div>
                                @error('month') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="month_day" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-calendar-day mr-1 text-gray-500"></i> Day <span class="text-red-500">*</span>
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="month_day"
                                        wire:model="month_day"
                                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('month_day') border-red-300 text-red-900 @enderror"
                                    >
                                        @for($i = 1; $i <= 31; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                    </div>
                                </div>
                                @error('month_day') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <div class="bg-gray-50 p-4 rounded-md mb-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                            <i class="fas fa-sliders-h mr-2 text-blue-500"></i> Additional Settings
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label for="type" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-tags mr-1 text-gray-500"></i> Type <span class="text-red-500">*</span>
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="type"
                                        wire:model="type"
                                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('type') border-red-300 text-red-900 @enderror"
                                    >
                                        <option value="preventive">Preventive</option>
                                        <option value="predictive">Predictive</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                    </div>
                                </div>
                                @error('type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="md:col-span-3">
                                <label for="assigned_to" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-user-cog mr-1 text-gray-500"></i> Assigned To
                                </label>
                                <div class="relative">
                                    <!-- Combined select and search -->
                                    <div class="relative rounded-md shadow-sm">
                                        <select
                                            id="assigned_to"
                                            wire:model="assigned_to"
                                            class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        >
                                            <option value="">Select technician</option>
                                            @foreach($technicians as $technician)
                                                <option value="{{ $technician->id }}">{{ $technician->full_name ?? $technician->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                        </div>
                                    </div>

                                    <!-- Search overlay input -->
                                    <div class="mt-2">
                                        <div class="relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-search text-gray-400"></i>
                                            </div>
                                            <input
                                                type="text"
                                                wire:model.live.debounce.300ms="technicianSearch"
                                                placeholder="Search for a technician..."
                                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            >
                                        </div>

                                        @if(!empty($filteredTechnicians))
                                            <div class="absolute z-10 w-full mt-1 bg-white shadow-lg rounded-md max-h-60 overflow-auto">
                                                @forelse($filteredTechnicians as $tech)
                                                    <div
                                                        wire:key="tech-{{ $tech['id'] }}"
                                                        wire:click="selectTechnician({{ $tech['id'] }})"
                                                        class="px-4 py-2 text-sm cursor-pointer hover:bg-gray-100"
                                                    >
                                                        {{ $tech['full_name'] ?? $tech['name'] }}
                                                        @if(!empty($tech['email']))
                                                            <span class="text-xs text-gray-500 block">{{ $tech['email'] }}</span>
                                                        @endif
                                                    </div>
                                                @empty
                                                    <div class="px-4 py-2 text-sm text-gray-700">No technicians found</div>
                                                @endforelse
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @error('assigned_to') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-md mb-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                            <i class="fas fa-file-alt mr-2 text-blue-500"></i> Notes & Description
                        </h4>
                        <!-- Description & Notes -->
                        <div class="mb-3">
                            <label for="description" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                <i class="fas fa-align-left mr-1 text-gray-500"></i> Description
                            </label>
                            <textarea
                                id="description"
                                wire:model="description"
                                rows="2"
                                class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Enter task description"
                            ></textarea>
                            @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="notes" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                <i class="fas fa-sticky-note mr-1 text-gray-500"></i> Notes
                            </label>
                            <textarea
                                id="notes"
                                wire:model="notes"
                                rows="2"
                                class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Enter any additional notes"
                            ></textarea>
                            @error('notes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <p class="text-xs text-gray-500 mb-4 flex items-center">
                        <i class="fas fa-info-circle mr-1 text-blue-500"></i> Fields marked with <span class="text-red-500">*</span> are required
                    </p>

                    <!-- Modal Footer -->
                    <div class="flex justify-end space-x-3 border-t border-gray-200 pt-4">
                        <button
                            type="button"
                            wire:click="closeModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150 flex items-center"
                            wire:loading.attr="disabled"
                        >
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150 flex items-center"
                            wire:loading.class="opacity-75 cursor-wait"
                            wire:loading.attr="disabled"
                        >
                            <i class="fas {{ $isEditing ? 'fa-save' : 'fa-calendar-check' }} mr-2" wire:loading.class="hidden" wire:target="save"></i>
                            <i class="fas fa-spinner fa-spin mr-2 hidden" wire:loading.class.remove="hidden" wire:target="save"></i>
                            <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Update' : 'Schedule' }} Task</span>
                            <span wire:loading wire:target="save">Processing...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Holiday Warning Modal -->
    @if($showHolidayWarning)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-[60] flex items-center justify-center p-2 sm:p-4">
        <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all w-full max-w-lg">
            <!-- Modal Header -->
            <div class="bg-yellow-50 px-4 sm:px-6 py-3 sm:py-4 border-b border-yellow-200 flex justify-between items-center">
                <h3 class="text-base sm:text-lg font-medium text-yellow-800 flex items-center">
                    <span class="bg-yellow-100 text-yellow-700 p-2 rounded-full mr-3">
                        <i class="fas fa-exclamation-triangle text-lg"></i>
                    </span>
                    Aviso: {{ $holidayTitle }}
                </h3>
                <button wire:click="keepOriginalDate" class="text-yellow-600 hover:text-yellow-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="px-4 sm:px-6 py-4 sm:py-5 bg-white">
                <div class="bg-yellow-50 p-4 rounded-md border border-yellow-200 mb-4">
                    <div class="flex items-start">
                        <i class="fas fa-calendar-times text-yellow-500 mt-1 mr-3"></i>
                        <div>
                            <p class="text-sm text-gray-700">
                                A data que você selecionou <span class="font-semibold">({{ \Carbon\Carbon::parse($originalScheduledDate)->format('d/m/Y') }})</span> é um <span class="text-yellow-700 font-bold">{{ $holidayTitle }}</span>.
                            </p>
                            <p class="mt-2 text-sm text-gray-700">
                                Planos de manutenção normalmente não são agendados nessas datas.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 p-4 rounded-md border border-blue-200">
                    <div class="flex items-start">
                        <i class="fas fa-calendar-check text-blue-500 mt-1 mr-3"></i>
                        <div>
                            <p class="text-sm text-gray-700">
                                Deseja agendar para a próxima data disponível <span class="font-semibold">({{ \Carbon\Carbon::parse($suggestedDate)->format('d/m/Y') }})</span> ou manter a data original?
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex justify-end space-x-3 mt-4 pt-4 border-t border-gray-200">
                    <button
                        type="button"
                        wire:click="keepOriginalDate"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150 flex items-center"
                    >
                        <i class="fas fa-calendar-times mr-2"></i> Manter Data Original
                    </button>
                    <button
                        type="button"
                        wire:click="acceptSuggestedDate"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150 flex items-center"
                    >
                        <i class="fas fa-calendar-check mr-2"></i> Usar Data Sugerida
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- View Detail Modal -->
    @if($showViewModal)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center overflow-y-auto p-2 sm:p-4">
        <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all w-full max-w-7xl max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="bg-gray-50 px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 flex justify-between items-center sticky top-0 z-10">
                <h3 class="text-base sm:text-lg font-medium text-gray-900 flex items-center">
                    <span class="bg-blue-100 text-blue-600 p-2 rounded-full mr-3">
                        <i class="fas fa-eye text-lg"></i>
                    </span>
                    View Maintenance Plan Details
                </h3>
                <button wire:click="closeViewModal" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="px-4 sm:px-6 py-4 sm:py-5">
                <div class="divide-y divide-gray-200">
                    <!-- Basic Information -->
                    <div class="pb-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                            <i class="fas fa-info-circle mr-2 text-blue-500"></i> Basic Information
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs font-medium text-gray-500">Task</p>
                                <p class="text-sm text-gray-900">{{ $tasks->where('id', $task_id)->first()->title ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Equipment</p>
                                <p class="text-sm text-gray-900">{{ $equipment->where('id', $equipment_id)->first()->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Line</p>
                                <p class="text-sm text-gray-900">{{ $lines->where('id', $line_id)->first()->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Area</p>
                                <p class="text-sm text-gray-900">{{ $areas->where('id', $area_id)->first()->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Schedule Information -->
                    <div class="py-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                            <i class="fas fa-calendar-alt mr-2 text-blue-500"></i> Schedule Information
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs font-medium text-gray-500">Start Date</p>
                                <p class="text-sm text-gray-900">{{ $scheduled_date ? \Carbon\Carbon::parse($scheduled_date)->format('m/d/Y') : 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Frequency</p>
                                <p class="text-sm text-gray-900">
                                    @switch($frequency_type)
                                        @case('once')
                                            Once
                                            @break
                                        @case('daily')
                                            Daily
                                            @break
                                        @case('custom')
                                            Every {{ $custom_days }} days
                                            @break
                                        @case('weekly')
                                            Weekly {{ isset($day_of_week) ? '(' . ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][$day_of_week] . ')' : '' }}
                                            @break
                                        @case('monthly')
                                            Monthly {{ isset($day_of_month) ? '(day ' . $day_of_month . ')' : '' }}
                                            @break
                                        @case('yearly')
                                            Yearly {{ isset($month) && isset($month_day) ? '(' . ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'][$month] . ' ' . $month_day . ')' : '' }}
                                            @break
                                        @default
                                            Unknown frequency
                                    @endswitch
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Settings -->
                    <div class="py-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                            <i class="fas fa-sliders-h mr-2 text-blue-500"></i> Additional Settings
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs font-medium text-gray-500">Priority</p>
                                <p class="text-sm">
                                    @switch($priority)
                                        @case('low')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-arrow-down mr-1"></i> Low
                                            </span>
                                            @break
                                        @case('medium')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-equals mr-1"></i> Medium
                                            </span>
                                            @break
                                        @case('high')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-arrow-up mr-1"></i> High
                                            </span>
                                            @break
                                        @default
                                            {{ ucfirst($priority) }}
                                    @endswitch
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Type</p>
                                <p class="text-sm">
                                    @switch($type)
                                        @case('preventive')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <i class="fas fa-shield-alt mr-1"></i> Preventive
                                            </span>
                                            @break
                                        @case('predictive')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                <i class="fas fa-chart-line mr-1"></i> Predictive
                                            </span>
                                            @break
                                        @default
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <i class="fas fa-cog mr-1"></i> {{ ucfirst($type) }}
                                            </span>
                                    @endswitch
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Status</p>
                                <p class="text-sm">
                                    @switch($status)
                                        @case('pending')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-clock mr-1"></i> Pending
                                            </span>
                                            @break
                                        @case('in_progress')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <i class="fas fa-spinner fa-spin mr-1"></i> In Progress
                                            </span>
                                            @break
                                        @case('completed')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check mr-1"></i> Completed
                                            </span>
                                            @break
                                        @case('cancelled')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <i class="fas fa-ban mr-1"></i> Cancelled
                                            </span>
                                            @break
                                        @default
                                            {{ ucfirst($status) }}
                                    @endswitch
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Assigned To</p>
                                <p class="text-sm text-gray-900">{{ $technicians->where('id', $assigned_to)->first()->full_name ?? $technicians->where('id', $assigned_to)->first()->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Notes & Description -->
                    <div class="py-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                            <i class="fas fa-file-alt mr-2 text-blue-500"></i> Notes & Description
                        </h4>
                        <div class="mb-4">
                            <p class="text-xs font-medium text-gray-500 mb-1">Description</p>
                            <div class="bg-gray-50 p-3 rounded-md text-sm">
                                {{ $description ?: 'No description provided.' }}
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 mb-1">Notes</p>
                            <div class="bg-gray-50 p-3 rounded-md text-sm">
                                {{ $notes ?: 'No notes available.' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="mt-6 flex justify-end space-x-3 border-t border-gray-200 pt-4">
                    <button
                        type="button"
                        wire:click="closeViewModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150 flex items-center"
                    >
                        <i class="fas fa-times mr-2"></i> Close
                    </button>
                    <button
                        type="button"
                        wire:click="edit({{ $scheduleId }})"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150 flex items-center"
                    >
                        <i class="fas fa-edit mr-2"></i> Edit
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal de Notas de Manutenção -->
    <livewire:maintenance-note-modal />

    <style>
        @keyframes pulse-blue {
            0%, 100% {
                background-color: rgba(191, 219, 254, 0.7); /* lighter blue */
            }
            50% {
                background-color: rgba(147, 197, 253, 0.9); /* darker blue */
            }
        }

        .in-progress-pulse {
            animation: pulse-blue 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>

    <script>
        document.addEventListener('livewire:initialized', function() {
            function setupCustomDaysToggle() {
                // Função para controlar a visibilidade do campo custom_days
                function toggleCustomDaysField() {
                    const frequencyType = document.getElementById('frequency_type');
                    const customDaysInput = document.getElementById('custom_days');

                    if (frequencyType && customDaysInput) {
                        if (frequencyType.value === 'custom') {
                            customDaysInput.style.display = 'block';
                        } else {
                            customDaysInput.style.display = 'none';
                        }
                    }
                }

                // Executa a função no carregamento inicial
                toggleCustomDaysField();

                // Adiciona listener para quando o valor de frequency_type mudar
                const frequencyType = document.getElementById('frequency_type');
                if (frequencyType) {
                    frequencyType.addEventListener('change', toggleCustomDaysField);
                }
            }

            // Configuração inicial
            setupCustomDaysToggle();

            // Configurar sempre que o modal for aberto/atualizado
            Livewire.on('showModalUpdated', () => {
                setTimeout(setupCustomDaysToggle, 100); // Pequeno atraso para garantir que os elementos estejam renderizados
            });

            // Para compatibilidade com Livewire, também escutar atualizações de componente
            document.addEventListener('livewire:load', setupCustomDaysToggle);

            // Add animation to "In Progress" status items
            function setupStatusAnimations() {
                document.querySelectorAll('.bg-blue-100').forEach(function(element) {
                    if (element.textContent.trim().includes('In Progress')) {
                        element.classList.add('in-progress-pulse');
                    }
                });
            }

            // Run on initial load
            setupStatusAnimations();

            // Add observer for dynamic content updates
            const observer = new MutationObserver(function(mutations) {
                setupStatusAnimations();
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        });
    </script>
</div>
