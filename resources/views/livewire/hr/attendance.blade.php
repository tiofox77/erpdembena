<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex items-center space-x-4">
                            <h2 class="text-xl font-semibold text-gray-800">
                                <i class="fas fa-clock mr-2 text-gray-600"></i>
                                {{ __('attendance.attendance_management') }}
                            </h2>
                            <x-hr-guide-link />
                        </div>
                        <div class="flex items-center space-x-3">
                            <!-- View Toggle -->
                            <div class="bg-gray-100 p-1 rounded-lg flex">
                                <button
                                    wire:click="switchView('list')"
                                    class="{{ $viewMode === 'list' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-600 hover:text-gray-800' }} px-3 py-1 rounded-md text-sm font-medium transition-all duration-200 ease-in-out"
                                >
                                    <i class="fas fa-list mr-1"></i>
                                    {{ __('attendance.list_view') }}
                                </button>
                                <button
                                    wire:click="switchView('calendar')"
                                    class="{{ $viewMode === 'calendar' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-600 hover:text-gray-800' }} px-3 py-1 rounded-md text-sm font-medium transition-all duration-200 ease-in-out"
                                >
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    {{ __('attendance.calendar_view') }}
                                </button>
                            </div>
                            <button
                                wire:click="create"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 ease-in-out transform hover:scale-105"
                            >
                                <i class="fas fa-plus mr-1"></i>
                                {{ __('attendance.register_attendance') }}
                            </button>
                        </div>
                    </div>

                    <!-- Filters and Search -->
                    <div class="mb-6 bg-white p-4 rounded-lg shadow-sm">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
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
                                        placeholder="{{ __('attendance.search_employee') }}"
                                        class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 pr-3 py-2 sm:text-sm border-gray-300 rounded-md"
                                    >
                                </div>
                            </div>

                            <div>
                                <label for="filterDepartment" class="sr-only">{{ __('attendance.department') }}</label>
                                <select
                                    id="filterDepartment"
                                    wire:model.live="filters.department_id"
                                    class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                                    <option value="">{{ __('attendance.all_departments') }}</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="filterStatus" class="sr-only">{{ __('attendance.status') }}</label>
                                <select
                                    id="filterStatus"
                                    wire:model.live="filters.status"
                                    class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                                    <option value="">{{ __('attendance.all_status') }}</option>
                                    <option value="present">{{ __('attendance.present') }}</option>
                                    <option value="absent">{{ __('attendance.absent') }}</option>
                                    <option value="late">{{ __('attendance.late') }}</option>
                                    <option value="half_day">{{ __('attendance.half_day') }}</option>
                                </select>
                            </div>

                            <div>
                                <label for="dateFrom" class="sr-only">{{ __('attendance.date_from') }}</label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-calendar text-gray-400"></i>
                                    </div>
                                    <input
                                        type="date"
                                        id="dateFrom"
                                        wire:model.live="filters.start_date"
                                        placeholder="{{ __('attendance.date_from') }}"
                                        class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 pr-3 py-2 sm:text-sm border-gray-300 rounded-md"
                                    >
                                </div>
                            </div>

                            <div>
                                <label for="dateTo" class="sr-only">{{ __('attendance.date_to') }}</label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-calendar text-gray-400"></i>
                                    </div>
                                    <input
                                        type="date"
                                        id="dateTo"
                                        wire:model.live="filters.end_date"
                                        placeholder="{{ __('attendance.date_to') }}"
                                        class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 pr-3 py-2 sm:text-sm border-gray-300 rounded-md"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Calendar View -->
                    @if($viewMode === 'calendar')
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3 flex justify-between items-center">
                                <h3 class="text-lg font-medium text-white flex items-center">
                                    <i class="fas fa-calendar-alt mr-2 animate-pulse"></i>
                                    {{ __('attendance.attendance_calendar') }}
                                </h3>
                                <div class="flex items-center space-x-3">
                                    <button wire:click="previousMonth" class="text-white hover:text-gray-200 p-1 rounded transition-all duration-200 hover:bg-blue-800">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <span class="text-white font-medium px-3">{{ $currentMonthName }}</span>
                                    <button wire:click="nextMonth" class="text-white hover:text-gray-200 p-1 rounded transition-all duration-200 hover:bg-blue-800">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="p-4">
                                <div class="grid grid-cols-7 gap-2">
                                    <!-- Days of Week Header -->
                                    <div class="text-center text-xs font-medium text-gray-500 py-2">Dom</div>
                                    <div class="text-center text-xs font-medium text-gray-500 py-2">Seg</div>
                                    <div class="text-center text-xs font-medium text-gray-500 py-2">Ter</div>
                                    <div class="text-center text-xs font-medium text-gray-500 py-2">Qua</div>
                                    <div class="text-center text-xs font-medium text-gray-500 py-2">Qui</div>
                                    <div class="text-center text-xs font-medium text-gray-500 py-2">Sex</div>
                                    <div class="text-center text-xs font-medium text-gray-500 py-2">Sáb</div>
                                    
                                    <!-- Empty cells for days before month start -->
                                    @for($i = 0; $i < $startDayOfWeek; $i++)
                                        <div class="h-16 bg-gray-50 rounded-lg"></div>
                                    @endfor
                                    
                                    <!-- Days of the month -->
                                    @for($day = 1; $day <= $daysInMonth; $day++)
                                        @php
                                            $currentDate = \Carbon\Carbon::createFromDate($currentYear, $currentMonth, $day)->format('Y-m-d');
                                            $dayStats = $calendarData->get($currentDate) ?? null;
                                            $isToday = $currentDate === \Carbon\Carbon::today()->format('Y-m-d');
                                            $isPast = $currentDate < \Carbon\Carbon::today()->format('Y-m-d');
                                        @endphp
                                        <div 
                                            wire:click="selectDate('{{ $currentDate }}')"
                                            class="h-32 rounded-lg border-2 cursor-pointer transition-all duration-200 ease-in-out transform hover:scale-105 hover:shadow-md 
                                                {{ $isToday ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300' }}
                                                {{ $isPast && $dayStats && $dayStats['total_attendances'] > 0 ? 'bg-green-50' : '' }}
                                                {{ $isPast && (!$dayStats || $dayStats['total_attendances'] === 0) ? 'bg-red-50' : '' }}"
                                        >
                                            <div class="p-2 h-full flex flex-col">
                                                <!-- Cabeçalho do dia -->
                                                <div class="flex justify-between items-start mb-1">
                                                    <span class="text-sm font-bold {{ $isToday ? 'text-blue-600' : 'text-gray-900' }}">
                                                        {{ $day }}
                                                    </span>
                                                    @if($dayStats && $dayStats['total_attendances'] > 0)
                                                        <span class="text-xs bg-blue-100 text-blue-800 px-1 rounded-full font-medium">
                                                            {{ $dayStats['total_attendances'] }}
                                                        </span>
                                                    @endif
                                                </div>
                                                
                                                <!-- Estatísticas detalhadas -->
                                                @if($dayStats && $dayStats['total_attendances'] > 0)
                                                    <div class="flex-1 space-y-1">
                                                        <!-- Presentes -->
                                                        @if($dayStats['present'] > 0)
                                                            <div class="flex items-center justify-between text-xs">
                                                                <div class="flex items-center">
                                                                    <div class="w-2 h-2 bg-green-400 rounded-full mr-1"></div>
                                                                    <span class="text-green-700">{{ __('attendance.present') }}</span>
                                                                </div>
                                                                <span class="font-medium text-green-700">{{ $dayStats['present'] }}</span>
                                                            </div>
                                                        @endif
                                                        
                                                        <!-- Ausentes -->
                                                        @if($dayStats['absent'] > 0)
                                                            <div class="flex items-center justify-between text-xs">
                                                                <div class="flex items-center">
                                                                    <div class="w-2 h-2 bg-red-400 rounded-full mr-1"></div>
                                                                    <span class="text-red-700">{{ __('attendance.absent') }}</span>
                                                                </div>
                                                                <span class="font-medium text-red-700">{{ $dayStats['absent'] }}</span>
                                                            </div>
                                                        @endif
                                                        
                                                        <!-- Atrasados -->
                                                        @if($dayStats['late'] > 0)
                                                            <div class="flex items-center justify-between text-xs">
                                                                <div class="flex items-center">
                                                                    <div class="w-2 h-2 bg-yellow-400 rounded-full mr-1"></div>
                                                                    <span class="text-yellow-700">{{ __('attendance.late') }}</span>
                                                                </div>
                                                                <span class="font-medium text-yellow-700">{{ $dayStats['late'] }}</span>
                                                            </div>
                                                        @endif
                                                        
                                                        <!-- Licenças -->
                                                        @if($dayStats['leave'] > 0)
                                                            <div class="flex items-center justify-between text-xs">
                                                                <div class="flex items-center">
                                                                    <div class="w-2 h-2 bg-purple-400 rounded-full mr-1"></div>
                                                                    <span class="text-purple-700">{{ __('attendance.leave') }}</span>
                                                                </div>
                                                                <span class="font-medium text-purple-700">{{ $dayStats['leave'] }}</span>
                                                            </div>
                                                        @endif
                                                        
                                                        <!-- Taxa de presença -->
                                                        <div class="mt-1 pt-1 border-t border-gray-200">
                                                            <div class="flex items-center justify-between text-xs">
                                                                <span class="text-gray-600">Taxa:</span>
                                                                <span class="font-medium 
                                                                    {{ $dayStats['attendance_rate'] >= 90 ? 'text-green-600' : '' }}
                                                                    {{ $dayStats['attendance_rate'] >= 70 && $dayStats['attendance_rate'] < 90 ? 'text-yellow-600' : '' }}
                                                                    {{ $dayStats['attendance_rate'] < 70 ? 'text-red-600' : '' }}
                                                                ">{{ $dayStats['attendance_rate'] }}%</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="flex-1 flex items-center justify-center">
                                                        <span class="text-xs text-gray-400">{{ __('attendance.no_data') }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                                
                                <!-- Legend -->
                                <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">{{ __('attendance.legend') }}:</h4>
                                    <div class="flex flex-wrap gap-4 text-xs">
                                        <div class="flex items-center space-x-1">
                                            <div class="w-3 h-3 rounded-full bg-green-400"></div>
                                            <span>{{ __('attendance.present') }}</span>
                                        </div>
                                        <div class="flex items-center space-x-1">
                                            <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                            <span>{{ __('attendance.absent') }}</span>
                                        </div>
                                        <div class="flex items-center space-x-1">
                                            <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                                            <span>{{ __('attendance.late') }}</span>
                                        </div>
                                        <div class="flex items-center space-x-1">
                                            <div class="w-3 h-3 rounded-full bg-orange-400"></div>
                                            <span>{{ __('attendance.half_day') }}</span>
                                        </div>
                                        <div class="flex items-center space-x-1">
                                            <div class="w-3 h-3 rounded-full bg-purple-400"></div>
                                            <span>{{ __('attendance.leave') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Attendance Records Table -->
                    @if($viewMode === 'list')
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <div class="flex items-center space-x-1 cursor-pointer" wire:click="sortBy('date')">
                                                    <i class="fas fa-calendar-alt text-gray-400"></i>
                                                    <span>Date</span>
                                                    @if($sortField === 'date')
                                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-gray-400"></i>
                                                    @endif
                                                </div>
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <div class="flex items-center space-x-1 cursor-pointer" wire:click="sortBy('employee_id')">
                                                    <i class="fas fa-user text-gray-400"></i>
                                                    <span>Employee</span>
                                                    @if($sortField === 'employee_id')
                                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-gray-400"></i>
                                                    @endif
                                                </div>
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <div class="flex items-center space-x-1">
                                                    <i class="fas fa-clock text-gray-400"></i>
                                                    <span>Times</span>
                                                </div>
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <div class="flex items-center space-x-1 cursor-pointer" wire:click="sortBy('status')">
                                                    <i class="fas fa-info-circle text-gray-400"></i>
                                                    <span>Status</span>
                                                    @if($sortField === 'status')
                                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-gray-400"></i>
                                                    @endif
                                                </div>
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <div class="flex items-center space-x-1">
                                                    <i class="fas fa-check-circle text-gray-400"></i>
                                                    <span>Approved</span>
                                                </div>
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <div class="flex items-center space-x-1">
                                                    <i class="fas fa-sticky-note text-gray-400"></i>
                                                    <span>Remarks</span>
                                                </div>
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <div class="flex items-center space-x-1">
                                                    <i class="fas fa-cog text-gray-400"></i>
                                                    <span>Actions</span>
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($attendances as $attendance)
                                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">{{ $attendance->date->format('M d, Y') }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        @if($attendance->employee->photo)
                                                            <img src="{{ asset('storage/' . $attendance->employee->photo) }}" alt="{{ $attendance->employee->full_name }}" class="h-8 w-8 rounded-full mr-2">
                                                        @else
                                                            <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center mr-2">
                                                                <i class="fas fa-user text-gray-400"></i>
                                                            </div>
                                                        @endif
                                                        <div class="text-sm text-gray-900">{{ $attendance->employee->full_name }}</div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        <div>In: {{ $attendance->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('H:i') : 'N/A' }}</div>
                                                        <div>Out: {{ $attendance->time_out ? \Carbon\Carbon::parse($attendance->time_out)->format('H:i') : 'N/A' }}</div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                        {{ $attendance->status === 'present' ? 'bg-green-100 text-green-800' : 
                                                        ($attendance->status === 'absent' ? 'bg-red-100 text-red-800' : 
                                                        ($attendance->status === 'late' ? 'bg-yellow-100 text-yellow-800' : 
                                                        ($attendance->status === 'half_day' ? 'bg-orange-100 text-orange-800' : 
                                                        'bg-purple-100 text-purple-800'))) }}">
                                                        {{ ucfirst($attendance->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    @if($attendance->is_approved)
                                                        <span class="text-green-600">
                                                            <i class="fas fa-check-circle mr-1"></i>
                                                            Approved
                                                        </span>
                                                    @else
                                                        <span class="text-yellow-600">
                                                            <i class="fas fa-clock mr-1"></i>
                                                            Pending
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $attendance->remarks ?: '-' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <button
                                                        wire:click="edit({{ $attendance->id }})"
                                                        class="text-indigo-600 hover:text-indigo-900 mr-3 transition-colors duration-200"
                                                        title="Edit"
                                                    >
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button
                                                        wire:click="confirmDelete({{ $attendance->id }})"
                                                        class="text-red-600 hover:text-red-900 transition-colors duration-200"
                                                        title="Delete"
                                                    >
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="px-6 py-12 text-center">
                                                    <div class="flex flex-col items-center justify-center text-gray-500">
                                                        <i class="fas fa-clock text-gray-400 text-4xl mb-4"></i>
                                                        <span class="text-lg font-medium">No attendance records found</span>
                                                        <p class="text-sm mt-2">
                                                            @if($search || !empty($filters['employee_id']) || !empty($filters['status']) || !empty($filters['start_date']) || !empty($filters['end_date']))
                                                                No records match your search criteria. Try adjusting your filters.
                                                                <button
                                                                    wire:click="resetFilters"
                                                                    class="text-blue-500 hover:text-blue-700 underline ml-1"
                                                                >
                                                                    Clear all filters
                                                                </button>
                                                            @else
                                                                There are no attendance records in the system yet. Click "Record Attendance" to add one.
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
                                @if(method_exists($attendances, 'links'))
                                    {{ $attendances->links() }}
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Modal for Batch Attendance -->
    @if($showCalendarModal)
        <div x-data="{ open: @entangle('showCalendarModal') }" 
             x-show="open" 
             x-cloak 
             class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50" 
             role="dialog" 
             aria-modal="true"
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0">
            <div class="relative top-20 mx-auto p-1 w-full max-w-4xl">
                <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" 
                     x-transition:enter="transition ease-out duration-300" 
                     x-transition:enter-start="transform opacity-0 scale-95" 
                     x-transition:enter-end="transform opacity-100 scale-100" 
                     x-transition:leave="transition ease-in duration-200" 
                     x-transition:leave-start="transform opacity-100 scale-100" 
                     x-transition:leave-end="transform opacity-0 scale-95">
                    
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <i class="fas fa-users mr-2 animate-pulse"></i>
                            {{ __('attendance.batch_attendance_for') }} {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}
                        </h3>
                        <button type="button" wire:click="closeCalendarModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <!-- Content -->
                    <div class="p-6">
                        <form wire:submit.prevent="saveBatchAttendance">
                            <!-- Etapa 1: Seleção de Shift -->
                            <div class="mb-6 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
                                <div class="flex items-center bg-gradient-to-r from-purple-50 to-purple-100 px-4 py-3 border-b border-gray-200">
                                    <i class="fas fa-business-time text-purple-600 mr-2"></i>
                                    <h4 class="text-base font-medium text-gray-700">{{ __('attendance.step_1_select_shift') }}</h4>
                                    @if($selectedShift)
                                        <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check mr-1"></i>
                                            {{ __('attendance.selected') }}
                                        </span>
                                    @endif
                                </div>
                                <div class="p-4">
                                    @if(count($availableShifts) > 0)
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            @foreach($availableShifts as $shift)
                                                <div class="relative">
                                                    <input type="radio" wire:click="selectShift({{ $shift->id }})" 
                                                           name="shift_selection" 
                                                           value="{{ $shift->id }}" 
                                                           {{ $selectedShift == $shift->id ? 'checked' : '' }}
                                                           class="sr-only" 
                                                           id="shift_{{ $shift->id }}">
                                                    <label for="shift_{{ $shift->id }}" 
                                                           class="block p-4 border-2 rounded-lg cursor-pointer transition-all duration-200 hover:border-blue-300 {{ $selectedShift == $shift->id ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:bg-gray-50' }}">
                                                        <div class="flex items-center justify-between">
                                                            <div class="flex items-center">
                                                                <div class="flex-shrink-0">
                                                                    <i class="fas fa-clock text-blue-600 text-lg"></i>
                                                                </div>
                                                                <div class="ml-3">
                                                                    <h5 class="text-sm font-medium text-gray-900">{{ $shift->name }}</h5>
                                                                    <p class="text-sm text-gray-500">
                                                                        {{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }}
                                                                    </p>
                                                                    @if($shift->description)
                                                                        <p class="text-xs text-gray-400 mt-1">{{ $shift->description }}</p>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            @if($selectedShift == $shift->id)
                                                                <div class="flex-shrink-0">
                                                                    <i class="fas fa-check-circle text-blue-600 text-lg"></i>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center py-8">
                                            <i class="fas fa-business-time text-gray-400 text-4xl mb-4"></i>
                                            <p class="text-gray-500">{{ __('attendance.no_shifts_available') }}</p>
                                            <p class="text-sm text-gray-400 mt-2">{{ __('attendance.no_shifts_available_description') }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Etapa 2: Configurações do Lote (só aparece se shift selecionado) -->
                            @if($selectedShift)
                                <div class="mb-6 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
                                    <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                                        <i class="fas fa-cog text-blue-600 mr-2"></i>
                                        <h4 class="text-base font-medium text-gray-700">{{ __('attendance.step_2_batch_settings') }}</h4>
                                    </div>
                                    <div class="p-4">
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <label for="batchStatus" class="block text-sm font-medium text-gray-700 mb-1">{{ __('attendance.attendance_status') }}</label>
                                                <select wire:model="batchStatus" id="batchStatus" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white">
                                                    <option value="present">{{ __('attendance.present') }}</option>
                                                    <option value="absent">{{ __('attendance.absent') }}</option>
                                                    <option value="late">{{ __('attendance.late') }}</option>
                                                    <option value="half_day">{{ __('attendance.half_day') }}</option>
                                                    <option value="leave">{{ __('attendance.sick_leave') }}</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label for="batchTimeIn" class="block text-sm font-medium text-gray-700 mb-1">{{ __('attendance.time_in') }}</label>
                                                <input type="time" wire:model="batchTimeIn" id="batchTimeIn" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white">
                                                <p class="text-xs text-gray-500 mt-1">
                                                    <i class="fas fa-info-circle mr-1"></i>
                                                    {{ __('attendance.auto_filled_from_shift') }}
                                                </p>
                                            </div>
                                            <div>
                                                <label for="batchTimeOut" class="block text-sm font-medium text-gray-700 mb-1">{{ __('attendance.time_out') }}</label>
                                                <input type="time" wire:model="batchTimeOut" id="batchTimeOut" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white">
                                                <p class="text-xs text-gray-500 mt-1">
                                                    <i class="fas fa-info-circle mr-1"></i>
                                                    {{ __('attendance.auto_filled_from_shift') }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="mt-4">
                                            <label for="batchRemarks" class="block text-sm font-medium text-gray-700 mb-1">{{ __('attendance.observations') }}</label>
                                            <textarea wire:model="batchRemarks" id="batchRemarks" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white" placeholder="{{ __('attendance.enter_observations') }}"></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Etapa 3: Seleção de Funcionários -->
                                <div class="mb-6 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
                                    <div class="flex items-center justify-between bg-gradient-to-r from-green-50 to-green-100 px-4 py-3 border-b border-gray-200">
                                        <div class="flex items-center">
                                            <i class="fas fa-users text-green-600 mr-2"></i>
                                            <h4 class="text-base font-medium text-gray-700">{{ __('attendance.step_3_select_employees') }}</h4>
                                            <span class="ml-2 text-sm text-gray-500">({{ count($shiftEmployees) }} {{ __('attendance.employees_count') }})</span>
                                        </div>
                                        @if(count($shiftEmployees) > 0)
                                            <div class="flex items-center space-x-2">
                                                <button type="button" wire:click="toggleSelectAll" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                                    <i class="fas fa-check-square mr-1"></i>
                                                    {{ $selectAllEmployees ? __('attendance.deselect_all') : __('attendance.select_all') }}
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="p-4">
                                        @if(count($shiftEmployees) > 0)
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                @foreach($shiftEmployees as $employee)
                                                    <div class="flex items-center p-3 border rounded-lg {{ $employee['already_marked'] ? 'bg-gray-50 border-gray-300' : 'bg-white border-gray-200 hover:border-blue-300' }} transition-all duration-200">
                                                        <div class="flex items-center w-full">
                                                            <input type="checkbox" wire:model="selectedEmployees" value="{{ $employee['id'] }}" 
                                                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" 
                                                                   @if($employee['already_marked']) disabled @endif>
                                                            <div class="ml-3 flex-1">
                                                                <div class="flex items-center justify-between">
                                                                    <div>
                                                                        <label class="text-sm font-medium text-gray-700">{{ $employee['name'] }}</label>
                                                                        <div class="text-xs text-gray-500">{{ $employee['department'] }}</div>
                                                                    </div>
                                                                    @if($employee['has_rotation'])
                                                                        <div class="flex items-center">
                                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                                                <i class="fas fa-sync-alt mr-1"></i>
                                                                                {{ __('shifts.rotation') }}
                                                                            </span>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                
                                                                <!-- Informações de Rotação -->
                                                                @if($employee['has_rotation'])
                                                                    <div class="mt-2 p-2 bg-purple-50 rounded-lg border border-purple-200">
                                                                        <div class="flex items-center justify-between mb-1">
                                                                            <span class="text-xs font-medium text-purple-700">
                                                                                <i class="fas fa-info-circle mr-1"></i>
                                                                                {{ __('shifts.rotation_info') }}
                                                                            </span>
                                                                        </div>
                                                                        <div class="text-xs text-purple-600 space-y-1">
                                                                            <div class="flex items-center">
                                                                                <i class="fas fa-clock mr-1"></i>
                                                                                <span class="font-medium">{{ __('shifts.current_shift') }}:</span>
                                                                                <span class="ml-1">
                                                                                    @php
                                                                                        $currentShift = $availableShifts->firstWhere('id', $employee['current_shift_id']);
                                                                                    @endphp
                                                                                    {{ $currentShift ? $currentShift['name'] : 'N/A' }}
                                                                                </span>
                                                                            </div>
                                                                            <div class="flex items-center">
                                                                                <i class="fas fa-sync-alt mr-1"></i>
                                                                                <span class="font-medium">{{ __('shifts.rotation_type') }}:</span>
                                                                                <span class="ml-1">
                                                                                    @switch($employee['rotation_type'])
                                                                                        @case('daily')
                                                                                            {{ __('shifts.daily_rotation') }}
                                                                                            @break
                                                                                        @case('weekly')
                                                                                            {{ __('shifts.weekly_rotation') }}
                                                                                            @break
                                                                                        @case('monthly')
                                                                                            {{ __('shifts.monthly_rotation') }}
                                                                                            @break
                                                                                        @case('yearly')
                                                                                            {{ __('shifts.yearly_rotation') }}
                                                                                            @break
                                                                                        @default
                                                                                            {{ __('shifts.custom_rotation') }}
                                                                                    @endswitch
                                                                                    @if($employee['rotation_interval'] > 1)
                                                                                        ({{ $employee['rotation_interval'] }}x)
                                                                                    @endif
                                                                                </span>
                                                                            </div>
                                                                            @if($employee['next_rotation'])
                                                                                <div class="flex items-center">
                                                                                    <i class="fas fa-calendar-alt mr-1"></i>
                                                                                    <span class="font-medium">{{ __('shifts.next_rotation') }}:</span>
                                                                                    <span class="ml-1">
                                                                                        {{ \Carbon\Carbon::parse($employee['next_rotation'])->format('d/m/Y') }}
                                                                                    </span>
                                                                                </div>
                                                                            @endif
                                                                            <div class="flex items-center">
                                                                                <i class="fas fa-calendar-plus mr-1"></i>
                                                                                <span class="font-medium">{{ __('shifts.rotation_started') }}:</span>
                                                                                <span class="ml-1">
                                                                                    {{ \Carbon\Carbon::parse($employee['assignment_start'])->format('d/m/Y') }}
                                                                                </span>
                                                                            </div>
                                                                            @if($employee['assignment_end'])
                                                                                <div class="flex items-center">
                                                                                    <i class="fas fa-calendar-minus mr-1"></i>
                                                                                    <span class="font-medium">{{ __('shifts.rotation_ends') }}:</span>
                                                                                    <span class="ml-1">
                                                                                        {{ \Carbon\Carbon::parse($employee['assignment_end'])->format('d/m/Y') }}
                                                                                    </span>
                                                                                </div>
                                                                            @else
                                                                                @if($employee['is_permanent'])
                                                                                    <div class="flex items-center">
                                                                                        <i class="fas fa-infinity mr-1"></i>
                                                                                        <span class="font-medium">{{ __('shifts.permanent') }}</span>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                @else
                                                                    <!-- Informações de Turno Fixo -->
                                                                    <div class="mt-2 p-2 bg-blue-50 rounded-lg border border-blue-200">
                                                                        <div class="text-xs text-blue-600 space-y-1">
                                                                            <div class="flex items-center">
                                                                                <i class="fas fa-clock mr-1"></i>
                                                                                <span class="font-medium">{{ __('shifts.single_shift') }}</span>
                                                                            </div>
                                                                            <div class="flex items-center">
                                                                                <i class="fas fa-calendar-plus mr-1"></i>
                                                                                <span class="font-medium">{{ __('shifts.assigned_since') }}:</span>
                                                                                <span class="ml-1">
                                                                                    {{ \Carbon\Carbon::parse($employee['assignment_start'])->format('d/m/Y') }}
                                                                                </span>
                                                                            </div>
                                                                            @if($employee['assignment_end'])
                                                                                <div class="flex items-center">
                                                                                    <i class="fas fa-calendar-minus mr-1"></i>
                                                                                    <span class="font-medium">{{ __('shifts.assignment_ends') }}:</span>
                                                                                    <span class="ml-1">
                                                                                        {{ \Carbon\Carbon::parse($employee['assignment_end'])->format('d/m/Y') }}
                                                                                    </span>
                                                                                </div>
                                                                            @else
                                                                                @if($employee['is_permanent'])
                                                                                    <div class="flex items-center">
                                                                                        <i class="fas fa-infinity mr-1"></i>
                                                                                        <span class="font-medium">{{ __('shifts.permanent') }}</span>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                                
                                                                @if($employee['already_marked'])
                                                                    <div class="mt-2 text-xs text-orange-600 font-medium">
                                                                        <i class="fas fa-check-circle mr-1"></i>
                                                                        {{ __('attendance.already_recorded') }}
                                                                        @if($employee['existing_status'])
                                                                            <span class="ml-1">({{ ucfirst($employee['existing_status']) }})</span>
                                                                        @endif
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-center py-8">
                                                <i class="fas fa-users text-gray-400 text-4xl mb-4"></i>
                                                <p class="text-gray-500">{{ __('attendance.no_employees_found') }}</p>
                                                <p class="text-sm text-gray-400 mt-2">{{ __('attendance.no_employees_in_shift') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <!-- Mensagem para selecionar shift primeiro -->
                                <div class="mb-6 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
                                    <div class="p-8 text-center">
                                        <i class="fas fa-arrow-up text-blue-500 text-4xl mb-4"></i>
                                        <h4 class="text-lg font-medium text-gray-900 mb-2">{{ __('attendance.select_shift_first') }}</h4>
                                        <p class="text-gray-500">{{ __('attendance.select_shift_first_description') }}</p>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Actions -->
                            <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                                <button type="button" wire:click="closeCalendarModal" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                    <i class="fas fa-times mr-2"></i>
                                    {{ __('attendance.cancel') }}
                                </button>
                                <button type="submit" 
                                        wire:loading.attr="disabled" 
                                        @if(!$selectedShift) disabled @endif
                                        class="inline-flex justify-center items-center px-4 py-2 {{ $selectedShift ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed' }} border border-transparent rounded-md font-semibold text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed">
                                    <span wire:loading.remove wire:target="saveBatchAttendance">
                                        <i class="fas fa-save mr-2"></i>
                                        {{ __('attendance.save_attendance') }}
                                    </span>
                                    <span wire:loading wire:target="saveBatchAttendance" class="inline-flex items-center">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {{ __('attendance.processing') }}
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Create/Edit Attendance Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">
                        <i class="fas {{ $isEditing ? 'fa-edit' : 'fa-plus-circle' }} mr-2"></i>
                        {{ $isEditing ? 'Edit' : 'Record' }} Attendance
                    </h3>
                    <button type="button" class="text-gray-500 hover:text-gray-700 text-xl" wire:click="closeModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
                        <p class="font-bold flex items-center">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
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
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Employee -->
                        <div class="md:col-span-2">
                            <label for="employee_id" class="block text-sm font-medium text-gray-700">Employee</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <select id="employee_id"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('employee_id') border-red-300 text-red-900 @enderror"
                                    wire:model.live="employee_id">
                                    <option value="">Select Employee</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                                    @endforeach
                                </select>
                                @error('employee_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Date -->
                        <div class="md:col-span-2">
                            <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="date" id="date"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('date') border-red-300 text-red-900 @enderror"
                                    wire:model.live="date">
                                @error('date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Time In -->
                        <div>
                            <label for="time_in" class="block text-sm font-medium text-gray-700">Time In</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="time" id="time_in"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('time_in') border-red-300 text-red-900 @enderror"
                                    wire:model.live="time_in">
                                @error('time_in')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Time Out -->
                        <div>
                            <label for="time_out" class="block text-sm font-medium text-gray-700">Time Out</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="time" id="time_out"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('time_out') border-red-300 text-red-900 @enderror"
                                    wire:model.live="time_out">
                                @error('time_out')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="md:col-span-2">
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <select id="status"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('status') border-red-300 text-red-900 @enderror"
                                    wire:model.live="status">
                                    <option value="">Select Status</option>
                                    <option value="present">Present</option>
                                    <option value="absent">Absent</option>
                                    <option value="late">Late</option>
                                    <option value="half_day">Half Day</option>
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Remarks -->
                        <div class="md:col-span-2">
                            <label for="remarks" class="block text-sm font-medium text-gray-700">Remarks</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <textarea id="remarks" rows="3"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('remarks') border-red-300 text-red-900 @enderror"
                                    wire:model.live="remarks"></textarea>
                                @error('remarks')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Campos de Pagamento -->
                        <div class="border-t border-gray-200 pt-4 mb-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Informações de Pagamento</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="hourly_rate" class="block text-sm font-medium text-gray-700 mb-1">Taxa Horária (AOA)</label>
                                    <input type="number" min="0" step="0.01" id="hourly_rate"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                        wire:model.live="hourly_rate">
                                    @error('hourly_rate')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="overtime_hours" class="block text-sm font-medium text-gray-700 mb-1">Horas Extra</label>
                                    <input type="number" min="0" step="0.5" id="overtime_hours"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                        wire:model.live="overtime_hours">
                                    @error('overtime_hours')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="overtime_rate" class="block text-sm font-medium text-gray-700 mb-1">Taxa Hora Extra (AOA)</label>
                                    <input type="number" min="0" step="0.01" id="overtime_rate"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                        wire:model.live="overtime_rate">
                                    @error('overtime_rate')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="flex items-start mt-4">
                                <div class="flex items-center h-5">
                                    <input id="affects_payroll" type="checkbox"
                                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                                        wire:model.live="affects_payroll">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="affects_payroll" class="font-medium text-gray-700">Afeta Folha de Pagamento</label>
                                    <p class="text-gray-500">Se selecionado, este registo será considerado no cálculo da folha de pagamento</p>
                                </div>
                            </div>
                            @error('affects_payroll')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Campos específicos para género -->
                        <div class="border-t border-gray-200 pt-4 mb-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Informações Específicas (Género)</h3>
                            
                            <div class="flex items-start mb-4">
                                <div class="flex items-center h-5">
                                    <input id="is_maternity_related" type="checkbox"
                                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                                        wire:model.live="is_maternity_related">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="is_maternity_related" class="font-medium text-gray-700">Relacionado com Maternidade</label>
                                    <p class="text-gray-500">Ausência, ajuste de horário ou dispensa por motivos de maternidade</p>
                                </div>
                            </div>
                            @error('is_maternity_related')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            
                            @if($is_maternity_related)
                            <div>
                                <label for="maternity_type" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Ausência por Maternidade</label>
                                <select id="maternity_type"
                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                    wire:model.live="maternity_type">
                                    <option value="">Selecione o tipo</option>
                                    <option value="pre_natal_care">Consulta Pré-natal</option>
                                    <option value="maternity_leave">Licença Maternidade</option>
                                    <option value="nursing_break">Pausa para Amamentação</option>
                                    <option value="child_care">Cuidados com Criança</option>
                                </select>
                                @error('maternity_type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            @endif
                        </div>
                        
                        <div class="flex items-start mb-6">
                            <div class="flex items-center h-5">
                                <input id="is_approved" type="checkbox"
                                    class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                                    wire:model.live="is_approved">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="is_approved" class="font-medium text-gray-700">Aprovado</label>
                                <p class="text-gray-500">Marcar este registo de presença como aprovado</p>
                            </div>
                        </div>
                        @error('is_approved')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button"
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            wire:click="closeModal">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ $isEditing ? 'Atualizar' : 'Salvar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-red-600">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Eliminar Registo de Presença
                    </h3>
                    <button type="button" class="text-gray-500 hover:text-gray-700 text-xl" wire:click="closeDeleteModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="mb-4">
                    <p class="text-gray-700">Tem certeza que deseja eliminar este registo de presença? Esta ação não pode ser desfeita.</p>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        wire:click="closeDeleteModal">
                        Cancel
                    </button>
                    <button type="button"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        wire:click="delete">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Flash Message -->
    @if (session()->has('message'))
        <div x-data="{ show: true }"
             x-init="setTimeout(() => show = false, 3000)"
             x-show="show"
             class="fixed bottom-4 right-4 bg-green-500 text-white py-2 px-4 rounded-md shadow-md">
            {{ session('message') }}
        </div>
    @endif
</div>
