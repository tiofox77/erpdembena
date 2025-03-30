<div>
    <div class="py-2 sm:py-4">
        <div class="max-w-full mx-auto px-2 sm:px-4 lg:px-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2 sm:mb-0">Maintenance Scheduling</h1>
                <button
                    type="button"
                    class="bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-1.5 px-3 sm:py-2 sm:px-4 rounded flex items-center"
                    wire:click="openModal"
                >
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Schedule
                </button>
            </div>

            <!-- Filters and Table Section -->
            <div class="bg-white rounded-lg shadow mb-4 sm:mb-6">
                <div class="p-2 sm:p-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-4 mb-4">
                        <div>
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="search"
                                placeholder="Search equipment..."
                                class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm py-1.5 px-2"
                            >
                        </div>
                        <div>
                            <select wire:model.live="statusFilter" class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm py-1.5 px-2">
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <select wire:model.live="frequencyFilter" class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm py-1.5 px-2">
                                <option value="">All Frequencies</option>
                                <option value="once">Once</option>
                                <option value="daily">Daily</option>
                                <option value="custom">Custom</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th scope="col" class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Task
                                    </th>
                                    <th scope="col" class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Equipment
                                    </th>
                                    <th scope="col" class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">
                                        Frequency
                                    </th>
                                    <th scope="col" class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">
                                        Last Maint.
                                    </th>
                                    <th scope="col" class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">
                                        Next Maint.
                                    </th>
                                    <th scope="col" class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col" class="px-2 sm:px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
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
                                                    In Progress
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
                                                <button wire:click="edit({{ $schedule->id }})" class="text-indigo-600 hover:text-indigo-900">
                                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </button>
                                                <button wire:click="openHistory({{ $schedule->id }})" class="text-blue-600 hover:text-blue-900" title="View History">
                                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </button>
                                                <button wire:click="delete({{ $schedule->id }})" wire:confirm="Are you sure you want to delete this schedule?" class="text-red-600 hover:text-red-900">
                                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
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
        <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="bg-gray-50 px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 flex justify-between items-center sticky top-0 z-10">
                <h3 class="text-base sm:text-lg font-medium text-gray-900">{{ $isEditing ? 'Edit Maintenance Plan' : 'New Maintenance Plan' }}</h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="px-4 sm:px-6 py-3 sm:py-4">
                <p class="text-gray-600 text-xs sm:text-sm mb-3 sm:mb-4">Fill in the details to schedule a new maintenance task.</p>

                <form wire:submit.prevent="save">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 mb-3 sm:mb-4">
                        <!-- Task & Equipment -->
                        <div>
                            <label for="task_id" class="block text-xs sm:text-sm font-medium text-gray-700">Task *</label>
                            <select id="task_id" wire:model="task_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm py-1.5 px-2">
                                <option value="">Select task</option>
                                @foreach($tasks as $task)
                                    <option value="{{ $task->id }}">{{ $task->title }}</option>
                                @endforeach
                            </select>
                            @error('task_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="equipment_id" class="block text-xs sm:text-sm font-medium text-gray-700">Equipment *</label>
                            <select id="equipment_id" wire:model="equipment_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm py-1.5 px-2">
                                <option value="">Select equipment</option>
                                @foreach($equipment as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                            @error('equipment_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <!-- Line & Area -->
                        <div>
                            <label for="line_id" class="block text-xs sm:text-sm font-medium text-gray-700">Line</label>
                            <select id="line_id" wire:model="line_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm py-1.5 px-2">
                                <option value="">Select line</option>
                                @foreach($lines as $line)
                                    <option value="{{ $line->id }}">{{ $line->name }}</option>
                                @endforeach
                            </select>
                            @error('line_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="area_id" class="block text-xs sm:text-sm font-medium text-gray-700">Area</label>
                            <select id="area_id" wire:model="area_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm py-1.5 px-2">
                                <option value="">Select area</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                                @endforeach
                            </select>
                            @error('area_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <!-- Scheduled Date & Frequency -->
                        <div>
                            <label for="scheduled_date" class="block text-xs sm:text-sm font-medium text-gray-700">Start Date *</label>
                            <input type="date" id="scheduled_date" wire:model="scheduled_date" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm py-1.5 px-2">
                            @error('scheduled_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="frequency_type" class="block text-xs sm:text-sm font-medium text-gray-700">Frequency</label>
                            <div class="flex gap-2">
                                <select id="frequency_type" wire:model="frequency_type" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm py-1.5 px-2">
                                    @foreach($frequencies as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <input
                                    type="number"
                                    id="custom_days"
                                    wire:model="custom_days"
                                    class="mt-1 w-20 sm:w-24 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm py-1.5 px-2"
                                    placeholder="Days"
                                    style="display: none;">
                            </div>
                            @error('frequency_type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            @error('custom_days') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Frequency Option Fields -->
                    @if($frequency_type === 'weekly')
                    <div class="mb-3 sm:mb-4">
                        <label for="day_of_week" class="block text-xs sm:text-sm font-medium text-gray-700">Day of Week *</label>
                        <select id="day_of_week" wire:model="day_of_week" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm py-1.5 px-2">
                            <option value="0">Sunday</option>
                            <option value="1">Monday</option>
                            <option value="2">Tuesday</option>
                            <option value="3">Wednesday</option>
                            <option value="4">Thursday</option>
                            <option value="5">Friday</option>
                            <option value="6">Saturday</option>
                        </select>
                        @error('day_of_week') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    @endif

                    @if($frequency_type === 'monthly')
                    <div class="mb-3 sm:mb-4">
                        <label for="day_of_month" class="block text-xs sm:text-sm font-medium text-gray-700">Day of Month *</label>
                        <select id="day_of_month" wire:model="day_of_month" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm py-1.5 px-2">
                            @for($i = 1; $i <= 31; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                        @error('day_of_month') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    @endif

                    @if($frequency_type === 'yearly')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 mb-3 sm:mb-4">
                        <div>
                            <label for="month" class="block text-xs sm:text-sm font-medium text-gray-700">Month *</label>
                            <select id="month" wire:model="month" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm py-1.5 px-2">
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
                            @error('month') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="month_day" class="block text-xs sm:text-sm font-medium text-gray-700">Day *</label>
                            <select id="month_day" wire:model="month_day" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm py-1.5 px-2">
                                @for($i = 1; $i <= 31; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                            @error('month_day') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 mb-3 sm:mb-4">
                        <!-- Priority & Type -->
                        <div>
                            <label for="priority" class="block text-xs sm:text-sm font-medium text-gray-700">Priority</label>
                            <select id="priority" wire:model="priority" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm py-1.5 px-2">
                                @foreach($priorities as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('priority') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="type" class="block text-xs sm:text-sm font-medium text-gray-700">Type *</label>
                            <select id="type" wire:model="type" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm py-1.5 px-2">
                                <option value="preventive">Preventive</option>
                                <option value="predictive">Predictive</option>
                                <option value="other">Other</option>
                            </select>
                            @error('type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <!-- Status & Assignee -->
                        <div>
                            <label for="status" class="block text-xs sm:text-sm font-medium text-gray-700">Status</label>
                            <select id="status" wire:model="status" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm py-1.5 px-2">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="Scheduled">Scheduled</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            @error('status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="assigned_to" class="block text-xs sm:text-sm font-medium text-gray-700">Assigned To</label>
                            <div class="relative">
                                <!-- Combined select and search -->
                                <select
                                    id="assigned_to"
                                    wire:model="assigned_to"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm py-1.5 px-2"
                                >
                                    <option value="">Select technician</option>
                                    @foreach($technicians as $technician)
                                        <option value="{{ $technician->id }}">{{ $technician->full_name ?? $technician->name }}</option>
                                    @endforeach
                                </select>

                                <!-- Search overlay input -->
                                <div class="mt-2">
                                    <input
                                        type="text"
                                        wire:model.live.debounce.300ms="technicianSearch"
                                        placeholder="Search for a technician..."
                                        class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm py-1.5 px-2"
                                    >

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

                    <!-- Description & Notes -->
                    <div class="mb-3 sm:mb-4">
                        <label for="description" class="block text-xs sm:text-sm font-medium text-gray-700">Description</label>
                        <textarea id="description" wire:model="description" rows="2" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm py-1.5 px-2" placeholder="Enter task description"></textarea>
                        @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-3 sm:mb-4">
                        <label for="notes" class="block text-xs sm:text-sm font-medium text-gray-700">Notes</label>
                        <textarea id="notes" wire:model="notes" rows="2" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm py-1.5 px-2" placeholder="Enter any additional notes"></textarea>
                        @error('notes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <p class="text-xs text-gray-500 mb-3 sm:mb-4">* Required fields</p>

                    <!-- Modal Footer -->
                    <div class="flex justify-end space-x-2 sm:space-x-3">
                        <button type="button" wire:click="closeModal" class="px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700">
                            {{ $isEditing ? 'Update' : 'Schedule' }} Task
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Holiday Warning Modal -->
    @if($showHolidayWarning)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-[60] flex items-center justify-center p-2 sm:p-4" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center;">
        <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all w-full max-w-lg">
            <!-- Modal Header -->
            <div class="bg-yellow-50 px-4 sm:px-6 py-3 sm:py-4 border-b border-yellow-200 flex justify-between items-center">
                <h3 class="text-base sm:text-lg font-medium text-yellow-800">
                    <svg class="inline-block w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    Aviso: {{ $holidayTitle }}
                </h3>
                <button wire:click="keepOriginalDate" class="text-yellow-600 hover:text-yellow-800">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="px-4 sm:px-6 py-4 sm:py-5 bg-white">
                <div class="text-sm text-gray-700 mb-4">
                    <p>A data que você selecionou <span class="font-semibold">({{ \Carbon\Carbon::parse($originalScheduledDate)->format('d/m/Y') }})</span> é um <span class="text-yellow-700 font-bold">{{ $holidayTitle }}</span>.</p>
                    <p class="mt-2">Planos de manutenção normalmente não são agendados nessas datas.</p>
                    <p class="mt-2">Deseja agendar para a próxima data disponível <span class="font-semibold">({{ \Carbon\Carbon::parse($suggestedDate)->format('d/m/Y') }})</span> ou manter a data original?</p>
                </div>

                <!-- Modal Footer -->
                <div class="flex justify-end space-x-3 mt-4">
                    <button
                        type="button"
                        wire:click="keepOriginalDate"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50"
                    >
                        Manter Data Original
                    </button>
                    <button
                        type="button"
                        wire:click="acceptSuggestedDate"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700"
                    >
                        Usar Data Sugerida
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

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
    });
</script>

    <!-- Modal de Notas de Manutenção -->
    <livewire:maintenance-note-modal />
</div>
