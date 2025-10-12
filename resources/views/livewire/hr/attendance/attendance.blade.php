<div>
    <div class="py-4">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-4 sm:p-6">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg px-6 py-8 mb-6 shadow-lg">
                        <div class="flex justify-between items-center">
                            <div>
                                <h1 class="text-2xl font-bold text-white flex items-center">
                                    <i class="fas fa-clock mr-3 text-blue-200 animate-pulse"></i>
                                    {{ __('attendance.attendance_management') }}
                                </h1>
                                <p class="text-blue-100 mt-2">{{ __('attendance.manage_attendance_description') }}</p>
                            </div>
                            <div class="flex space-x-3">
                                <!-- View Toggle -->
                                <div class="bg-white bg-opacity-20 p-1 rounded-lg flex">
                                    <button
                                        wire:click="switchView('list')"
                                        class="{{ $viewMode === 'list' ? 'bg-white text-blue-700 shadow-sm' : 'text-blue-100 hover:text-white' }} px-3 py-2 rounded-md text-sm font-medium transition-all duration-200 ease-in-out"
                                    >
                                        <i class="fas fa-list mr-1"></i>
                                        {{ __('attendance.list_view') }}
                                    </button>
                                    <button
                                        wire:click="switchView('calendar')"
                                        class="{{ $viewMode === 'calendar' ? 'bg-white text-blue-700 shadow-sm' : 'text-blue-100 hover:text-white' }} px-3 py-2 rounded-md text-sm font-medium transition-all duration-200 ease-in-out"
                                    >
                                        <i class="fas fa-calendar-alt mr-1"></i>
                                        {{ __('attendance.calendar_view') }}
                                    </button>
                                </div>
                                <!-- Import Button -->
                                <button
                                    wire:click="openImportModal"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 ease-in-out transform hover:scale-105"
                                >
                                    <i class="fas fa-file-import mr-2"></i>
                                    {{ __('messages.import') }}
                                </button>
                                <button
                                    wire:click="openCalendar"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 ease-in-out transform hover:scale-105"
                                >
                                    <i class="fas fa-users mr-2"></i>
                                    {{ __('attendance.batch_attendance') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Filters and Search -->
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 mb-6 overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-600 to-purple-700 px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full bg-white bg-opacity-20 flex items-center justify-center mr-3">
                                        <i class="fas fa-filter text-white text-lg animate-pulse"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-white">{{ __('attendance.search_and_filters') }}</h3>
                                        <p class="text-indigo-100 text-sm">{{ __('attendance.filter_description') }}</p>
                                    </div>
                                </div>
                                <div class="text-white text-sm">
                                    <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full">
                                        <i class="fas fa-database mr-1"></i>
                                        <span class="font-medium">{{ $attendances->total() ?? 0 }}</span> {{ __('attendance.total_records') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-6">
                            <!-- Search Bar -->
                            <div class="mb-6">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400 text-lg"></i>
                                    </div>
                                    <input
                                        type="text"
                                        wire:model.live.debounce.300ms="search"
                                        class="block w-full pl-12 pr-4 py-4 text-lg border-2 border-gray-300 rounded-xl leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 shadow-sm hover:shadow-md"
                                        placeholder="{{ __('attendance.search_employee_placeholder') }}"
                                    >
                                    @if($search)
                                        <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-4 flex items-center">
                                            <i class="fas fa-times text-gray-400 hover:text-gray-600 transition-colors"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <!-- Advanced Filters -->
                            <div class="bg-gray-50 rounded-xl p-6 mb-6">
                                <div class="flex items-center mb-4">
                                    <i class="fas fa-sliders-h text-gray-600 mr-2"></i>
                                    <h4 class="text-md font-semibold text-gray-700">{{ __('attendance.advanced_filters') }}</h4>
                                </div>
                                
                                <!-- Primary Filters Row -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                                    <!-- Department Filter -->
                                    <div class="space-y-3">
                                        <label class="flex items-center text-sm font-semibold text-gray-700">
                                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center mr-3 shadow-sm">
                                                <i class="fas fa-building text-blue-600 text-sm"></i>
                                            </div>
                                            {{ __('attendance.department') }}
                                        </label>
                                        <div class="relative">
                                            <select wire:model.live="filters.department_id" class="w-full rounded-lg border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-25 bg-white py-3 pl-3 pr-10 text-sm transition-all duration-200 hover:border-blue-400 appearance-none">
                                                <option value="">{{ __('attendance.all_departments') }}</option>
                                                @foreach($departments as $department)
                                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400"></i>
                                            </div>
                                        </div>
                                        @if($filters['department_id'])
                                            <div class="flex items-center text-xs text-blue-600 bg-blue-50 px-2 py-1 rounded-md">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                {{ __('attendance.filtered') }}
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Status Filter -->
                                    <div class="space-y-3">
                                        <label class="flex items-center text-sm font-semibold text-gray-700">
                                            <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center mr-3 shadow-sm">
                                                <i class="fas fa-user-check text-green-600 text-sm"></i>
                                            </div>
                                            {{ __('attendance.status') }}
                                        </label>
                                        <div class="relative">
                                            <select wire:model.live="filters.status" class="w-full rounded-lg border-2 border-gray-300 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500 focus:ring-opacity-25 bg-white py-3 pl-3 pr-10 text-sm transition-all duration-200 hover:border-green-400 appearance-none">
                                                <option value="">{{ __('attendance.all_status') }}</option>
                                                <option value="present">{{ __('attendance.present') }}</option>
                                                <option value="absent">{{ __('attendance.absent') }}</option>
                                                <option value="late">{{ __('attendance.late') }}</option>
                                                <option value="half_day">{{ __('attendance.half_day') }}</option>
                                                <option value="leave">{{ __('attendance.leave') }}</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400"></i>
                                            </div>
                                        </div>
                                        @if($filters['status'])
                                            <div class="flex items-center text-xs text-green-600 bg-green-50 px-2 py-1 rounded-md">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                {{ __('attendance.filtered') }}
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Date From Filter -->
                                    <div class="space-y-3">
                                        <label class="flex items-center text-sm font-semibold text-gray-700">
                                            <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center mr-3 shadow-sm">
                                                <i class="fas fa-calendar-plus text-indigo-600 text-sm"></i>
                                            </div>
                                            {{ __('attendance.date_from') }}
                                        </label>
                                        <div class="relative">
                                            <input type="date" wire:model.live="filters.start_date" class="w-full rounded-lg border-2 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-25 py-3 px-3 text-sm transition-all duration-200 hover:border-indigo-400">
                                        </div>
                                        @if($filters['start_date'])
                                            <div class="flex items-center text-xs text-indigo-600 bg-indigo-50 px-2 py-1 rounded-md">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                {{ __('attendance.filtered') }}
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Date To Filter -->
                                    <div class="space-y-3">
                                        <label class="flex items-center text-sm font-semibold text-gray-700">
                                            <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center mr-3 shadow-sm">
                                                <i class="fas fa-calendar-minus text-purple-600 text-sm"></i>
                                            </div>
                                            {{ __('attendance.date_to') }}
                                        </label>
                                        <div class="relative">
                                            <input type="date" wire:model.live="filters.end_date" class="w-full rounded-lg border-2 border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500 focus:ring-opacity-25 py-3 px-3 text-sm transition-all duration-200 hover:border-purple-400">
                                        </div>
                                        @if($filters['end_date'])
                                            <div class="flex items-center text-xs text-purple-600 bg-purple-50 px-2 py-1 rounded-md">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                {{ __('attendance.filtered') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Filter Actions -->
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                <div class="flex items-center space-x-4">
                                    <div class="text-sm text-gray-600">
                                        @if(array_filter($filters))
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <i class="fas fa-filter mr-1"></i>
                                                {{ count(array_filter($filters)) }} {{ __('attendance.active_filters') }}
                                            </span>
                                        @else
                                            <span class="text-gray-500">{{ __('attendance.no_filters_applied') }}</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-3">
                                    @if(array_filter($filters))
                                        <button wire:click="resetFilters" class="inline-flex items-center px-4 py-2 border-2 border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                                            <i class="fas fa-times mr-2"></i>
                                            {{ __('attendance.clear_filters') }}
                                        </button>
                                    @endif
                                    
                                    <div class="text-sm text-gray-600 bg-gray-100 px-4 py-2 rounded-lg">
                                        <i class="fas fa-list mr-1"></i>
                                        {{ __('attendance.showing') }} 
                                        <span class="font-semibold">{{ $attendances->firstItem() ?? 0 }}-{{ $attendances->lastItem() ?? 0 }}</span> 
                                        {{ __('attendance.of') }} 
                                        <span class="font-semibold">{{ $attendances->total() ?? 0 }}</span>
                                    </div>
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
                                    <div class="text-center text-xs font-medium text-gray-500 py-2">{{ __('attendance.sunday') }}</div>
                                    <div class="text-center text-xs font-medium text-gray-500 py-2">{{ __('attendance.monday') }}</div>
                                    <div class="text-center text-xs font-medium text-gray-500 py-2">{{ __('attendance.tuesday') }}</div>
                                    <div class="text-center text-xs font-medium text-gray-500 py-2">{{ __('attendance.wednesday') }}</div>
                                    <div class="text-center text-xs font-medium text-gray-500 py-2">{{ __('attendance.thursday') }}</div>
                                    <div class="text-center text-xs font-medium text-gray-500 py-2">{{ __('attendance.friday') }}</div>
                                    <div class="text-center text-xs font-medium text-gray-500 py-2">{{ __('attendance.saturday') }}</div>
                                    
                                    <!-- Empty cells for days before month start -->
                                    @for($i = 0; $i < $startDayOfWeek; $i++)
                                        <div class="h-16 bg-gray-50 rounded-lg"></div>
                                    @endfor
                                    
                                    <!-- Days of the month -->
                                    @for($day = 1; $day <= $daysInMonth; $day++)
                                         @php
                                             $currentDate = \Carbon\Carbon::createFromDate($currentYear, $currentMonth, $day)->format('Y-m-d');
                                             $dayStats = $calendarData->get($currentDate);
                                             $isToday = $currentDate === \Carbon\Carbon::today()->format('Y-m-d');
                                             $isPast = $currentDate < \Carbon\Carbon::today()->format('Y-m-d');
                                        @endphp
                                        <div 
                                            wire:click="selectDate('{{ $currentDate }}')"
                                            class="h-32 rounded-lg border-2 cursor-pointer transition-all duration-200 ease-in-out transform hover:scale-105 hover:shadow-md 
                                                {{ $isToday ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300' }}
                                                {{ $isPast && $dayStats && $dayStats['attendance_rate'] >= 90 ? 'bg-green-50' : '' }}
                                                {{ $isPast && $dayStats && $dayStats['attendance_rate'] >= 70 && $dayStats['attendance_rate'] < 90 ? 'bg-yellow-50' : '' }}
                                                {{ $isPast && $dayStats && $dayStats['attendance_rate'] < 70 ? 'bg-red-50' : '' }}
                                                {{ $isPast && (!$dayStats || $dayStats['total_scheduled'] === 0) ? 'bg-gray-50' : '' }}"
                                        >
                                            <div class="p-2 h-full flex flex-col">
                                                <!-- Cabeçalho do dia -->
                                                <div class="flex justify-between items-start mb-1">
                                                    <span class="text-sm font-bold {{ $isToday ? 'text-blue-600' : 'text-gray-900' }}">
                                                        {{ $day }}
                                                    </span>
                                                     @if($dayStats && $dayStats['total_scheduled'] > 0)
                                                         <span class="text-xs bg-blue-100 text-blue-800 px-1 rounded-full font-medium">
                                                             {{ $dayStats['total_attendances'] }}/{{ $dayStats['total_scheduled'] }}
                                                         </span>
                                                     @endif
                                                </div>
                                                
                                                <!-- Estatísticas detalhadas -->
                                                @if($dayStats && $dayStats['total_scheduled'] > 0)
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
                                                        
                                                        <!-- Não marcados -->
                                                        @if($dayStats['not_marked'] > 0)
                                                            <div class="flex items-center justify-between text-xs">
                                                                <div class="flex items-center">
                                                                    <div class="w-2 h-2 bg-gray-400 rounded-full mr-1"></div>
                                                                    <span class="text-gray-700">{{ __('attendance.not_marked') }}</span>
                                                                </div>
                                                                <span class="font-medium text-gray-700">{{ $dayStats['not_marked'] }}</span>
                                                            </div>
                                                        @endif
                                                        
                                                        <!-- Taxa de presença -->
                                                        <div class="mt-1 pt-1 border-t border-gray-200">
                                                            <div class="flex items-center justify-between text-xs">
                                                                        <span class="text-gray-600">{{ __('attendance.rate') }}:</span>
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
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-table text-gray-600 mr-2"></i>
                                        <h3 class="text-lg font-medium text-gray-700">{{ __('attendance.attendance_records') }}</h3>
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $attendances->total() ?? 0 }} {{ __('attendance.records') }}
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ __('attendance.showing_records') }}
                                    </div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <div class="flex items-center space-x-1 cursor-pointer transition-colors duration-200 hover:text-gray-700" wire:click="sortBy('date')">
                                                    <i class="fas fa-calendar text-gray-400 mr-1"></i>
                                                    <span>{{ __('attendance.date') }}</span>
                                                    @if($sortField === 'date')
                                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500 ml-1"></i>
                                                    @else
                                                        <i class="fas fa-sort text-gray-400 ml-1 opacity-0 group-hover:opacity-100"></i>
                                                    @endif
                                                </div>
                                            </th>
                                            <th scope="col" class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <div class="flex items-center space-x-1 cursor-pointer transition-colors duration-200 hover:text-gray-700" wire:click="sortBy('employee_id')">
                                                    <i class="fas fa-user text-gray-400 mr-1"></i>
                                                    <span>{{ __('attendance.employee') }}</span>
                                                    @if($sortField === 'employee_id')
                                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500 ml-1"></i>
                                                    @else
                                                        <i class="fas fa-sort text-gray-400 ml-1 opacity-0 group-hover:opacity-100"></i>
                                                    @endif
                                                </div>
                                            </th>
                                            <th scope="col" class="px-3 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <div class="flex items-center">
                                                    <i class="fas fa-clock text-gray-400 mr-1"></i>
                                                    <span>{{ __('attendance.times') }}</span>
                                                </div>
                                            </th>
                                            <th scope="col" class="px-3 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <div class="flex items-center space-x-1 cursor-pointer transition-colors duration-200 hover:text-gray-700" wire:click="sortBy('status')">
                                                    <i class="fas fa-user-check text-gray-400 mr-1"></i>
                                                    <span>{{ __('attendance.status') }}</span>
                                                    @if($sortField === 'status')
                                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500 ml-1"></i>
                                                    @else
                                                        <i class="fas fa-sort text-gray-400 ml-1 opacity-0 group-hover:opacity-100"></i>
                                                    @endif
                                                </div>
                                            </th>
                                            <th scope="col" class="px-3 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <div class="flex items-center">
                                                    <i class="fas fa-comment text-gray-400 mr-1"></i>
                                                    <span>{{ __('attendance.remarks') }}</span>
                                                </div>
                                            </th>
                                            <th scope="col" class="px-4 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <div class="flex items-center justify-center">
                                                    <i class="fas fa-cog text-gray-400 mr-1"></i>
                                                    <span>{{ __('attendance.actions') }}</span>
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($attendances as $attendance)
                                             <tr class="hover:bg-gray-50 transition-colors duration-200 group">
                                                 <td class="px-4 py-4">
                                                     <div class="flex items-center">
                                                         <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center mr-3">
                                                             <i class="fas fa-calendar text-blue-600 text-sm"></i>
                                                         </div>
                                                         <div>
                                                             <div class="text-sm font-medium text-gray-900">{{ $attendance->date->format('M d, Y') }}</div>
                                                             <div class="text-sm text-gray-500">{{ $attendance->date->format('l') }}</div>
                                                         </div>
                                                     </div>
                                                 </td>
                                                 <td class="px-4 py-4">
                                                     <div class="flex items-center">
                                                         @if($attendance->employee->photo)
                                                             <img src="{{ asset('storage/' . $attendance->employee->photo) }}" alt="{{ $attendance->employee->full_name }}" class="w-10 h-10 rounded-full mr-3 border-2 border-gray-200">
                                                         @else
                                                             <div class="w-10 h-10 rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center mr-3">
                                                                 <span class="text-white font-medium text-sm">{{ strtoupper(substr($attendance->employee->full_name, 0, 1)) }}</span>
                                                             </div>
                                                         @endif
                                                         <div>
                                                             <div class="text-sm font-medium text-gray-900">{{ $attendance->employee->full_name }}</div>
                                                             <div class="text-sm text-gray-500">{{ $attendance->employee->employee_id ?? 'N/A' }}</div>
                                                         </div>
                                                     </div>
                                                 </td>
                                                 <td class="px-3 py-4">
                                                     <div class="space-y-1">
                                                         <div class="flex items-center text-sm text-gray-700">
                                                             <i class="fas fa-sign-in-alt text-green-500 mr-2 text-xs"></i>
                                                             <span class="font-medium">{{ $attendance->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('H:i') : 'N/A' }}</span>
                                                         </div>
                                                         <div class="flex items-center text-sm text-gray-700">
                                                             <i class="fas fa-sign-out-alt text-red-500 mr-2 text-xs"></i>
                                                             <span class="font-medium">{{ $attendance->time_out ? \Carbon\Carbon::parse($attendance->time_out)->format('H:i') : 'N/A' }}</span>
                                                         </div>
                                                     </div>
                                                 </td>
                                                <td class="px-3 py-4">
                                                     <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                                         {{ $attendance->status === 'present' ? 'bg-green-100 text-green-800 border border-green-200' : 
                                                         ($attendance->status === 'absent' ? 'bg-red-100 text-red-800 border border-red-200' : 
                                                         ($attendance->status === 'late' ? 'bg-yellow-100 text-yellow-800 border border-yellow-200' : 
                                                         ($attendance->status === 'half_day' ? 'bg-orange-100 text-orange-800 border border-orange-200' : 
                                                         'bg-purple-100 text-purple-800 border border-purple-200'))) }}">
                                                         @if($attendance->status === 'present')
                                                             <i class="fas fa-check-circle mr-1"></i>
                                                         @elseif($attendance->status === 'absent')
                                                             <i class="fas fa-times-circle mr-1"></i>
                                                         @elseif($attendance->status === 'late')
                                                             <i class="fas fa-clock mr-1"></i>
                                                         @elseif($attendance->status === 'half_day')
                                                             <i class="fas fa-adjust mr-1"></i>
                                                         @else
                                                             <i class="fas fa-calendar-check mr-1"></i>
                                                         @endif
                                                         {{ __("attendance.status_" . $attendance->status) ?? ucfirst($attendance->status) }}
                                                     </span>
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
                            
                            <!-- Enhanced Pagination -->
                            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-t border-gray-200">
                                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                                    <!-- Results Info -->
                                    <div class="flex items-center space-x-4">
                                        <div class="text-sm text-gray-600">
                                            <span class="font-medium">{{ __('attendance.showing') }}</span>
                                            <span class="font-bold text-indigo-600">{{ $attendances->firstItem() ?? 0 }}</span>
                                            <span>{{ __('attendance.to') }}</span>
                                            <span class="font-bold text-indigo-600">{{ $attendances->lastItem() ?? 0 }}</span>
                                            <span>{{ __('attendance.of') }}</span>
                                            <span class="font-bold text-indigo-600">{{ $attendances->total() ?? 0 }}</span>
                                            <span>{{ __('attendance.results') }}</span>
                                        </div>
                                        
                                        <!-- Per Page Selector -->
                                        <div class="flex items-center space-x-2">
                                            <label class="text-sm text-gray-600">{{ __('attendance.per_page') }}:</label>
                                            <div class="relative">
                                                <select wire:model.live="perPage" class="text-sm border-2 border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-25 bg-white py-2 pl-3 pr-8 appearance-none transition-all duration-200 hover:border-indigo-400">
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
                                    
                                    <!-- Pagination Links -->
                                    <div class="flex items-center">
                                        @if(method_exists($attendances, 'links'))
                                            <div class="pagination-wrapper">
                                                {{ $attendances->links('pagination::tailwind') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modals --}}
    @include('livewire.hr.attendance.modals._create-edit-modal')
    @include('livewire.hr.attendance.modals._batch-attendance-modal')
    @include('livewire.hr.attendance.modals._delete-modal')
    @include('livewire.hr.attendance.modals._import-modal')
    @include('livewire.hr.attendance.modals._time-conflicts-modal')

    {{-- Notificações são tratadas pelo layout global (livewire.blade.php) --}}
    {{-- Usa eventos: $this->dispatch('toast', ['type' => 'success', 'message' => '...']) --}}
</div>

{{-- BACKUP - OLD MODALS (KEEP FOR REFERENCE) --}}
@if(false)
    <!-- BACKUP: Calendar Modal for Batch Attendance -->
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
                                                    <input type="radio" wire:click="selectShift({{ $shift['id'] }})" 
                                                           name="shift_selection" 
                                                           value="{{ $shift['id'] }}" 
                                                           {{ $selectedShift == $shift['id'] ? 'checked' : '' }}
                                                           class="sr-only" 
                                                           id="shift_{{ $shift['id'] }}">
                                                    <label for="shift_{{ $shift['id'] }}" 
                                                           class="block p-4 border-2 rounded-lg cursor-pointer transition-all duration-200 hover:border-blue-300 {{ $selectedShift == $shift['id'] ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:bg-gray-50' }}">
                                                        <div class="flex items-center justify-between">
                                                            <div class="flex items-center">
                                                                <div class="flex-shrink-0">
                                                                    <i class="fas fa-clock text-blue-600 text-lg"></i>
                                                                </div>
                                                                <div class="ml-3">
                                                                    <h5 class="text-sm font-medium text-gray-900">{{ $shift['name'] }}</h5>
                                                                    <p class="text-sm text-gray-500">
                                                                        {{ $shift['start_time'] }} - {{ $shift['end_time'] }}
                                                                    </p>
                                                                    @if($shift['description'] ?? false)
                                                                        <p class="text-xs text-gray-400 mt-1">{{ $shift['description'] }}</p>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            @if($selectedShift == $shift['id'])
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
                                                    <div class="flex items-center p-3 border rounded-lg {{ isset($employee['already_marked']) && $employee['already_marked'] ? 'bg-gray-50 border-gray-300' : 'bg-white border-gray-200 hover:border-blue-300' }} transition-all duration-200">
                                                        <div class="flex items-center w-full">
                                                            <input type="checkbox" wire:model="selectedEmployees" value="{{ $employee['id'] }}" 
                                                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" 
                                                                   @if(isset($employee['already_marked']) && $employee['already_marked']) disabled @endif>
                                                            <div class="ml-3 flex-1">
                                                                <div class="flex items-center justify-between">
                                                                    <div>
                                                                        <label class="text-sm font-medium text-gray-700">{{ $employee['name'] }}</label>
                                                                        <div class="text-xs text-gray-500">{{ $employee['department'] }}</div>
                                                                    </div>
                                                                    @if(isset($employee['has_rotation']) && $employee['has_rotation'])
                                                                        <div class="flex items-center">
                                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                                                <i class="fas fa-sync-alt mr-1"></i>
                                                                                {{ __('shifts.rotation') }}
                                                                            </span>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                
                                                                <!-- Informações de Rotação -->
                                                                @if(isset($employee['has_rotation']) && $employee['has_rotation'])
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
                                                                                    @if(isset($employee['rotation_interval']) && $employee['rotation_interval'] > 1)
                                                                                        ({{ $employee['rotation_interval'] }}x)
                                                                                    @endif
                                                                                </span>
                                                                            </div>
                                                                            @if(isset($employee['next_rotation']) && $employee['next_rotation'])
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
                                                                            @if(isset($employee['assignment_end']) && $employee['assignment_end'])
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
                                                                            @if(isset($employee['assignment_end']) && $employee['assignment_end'])
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
                                                                
                                                                @if(isset($employee['already_marked']) && $employee['already_marked'])
                                                                    <div class="mt-2 text-xs text-orange-600 font-medium">
                                                                        <i class="fas fa-check-circle mr-1"></i>
                                                                        {{ __('attendance.already_recorded') }}
                                                                        @if(isset($employee['existing_status']) && $employee['existing_status'])
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

    <!-- Import Modal -->
    @if($showImportModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden m-4 transform transition-all duration-300">
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-indigo-600 to-purple-700 px-6 py-4 rounded-t-xl">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-file-import text-white text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-white">{{ __('messages.import_attendance') }}</h3>
                                <p class="text-indigo-100 text-sm">{{ __('messages.import_attendance_description') }}</p>
                            </div>
                        </div>
                        <button type="button" 
                            class="text-white/80 hover:text-white hover:bg-white/20 rounded-lg p-2 transition-all duration-200" 
                            wire:click="closeImportModal">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="p-6">
                    <!-- Instructions -->
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-500 text-lg"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-semibold text-blue-800 mb-2">{{ __('messages.import_instructions') }}</h4>
                                <ul class="text-sm text-blue-700 space-y-1 list-disc list-inside">
                                    <li>{{ __('messages.attendance_import_instruction_1') }}</li>
                                    <li>{{ __('messages.attendance_import_instruction_2') }}</li>
                                    <li>{{ __('messages.attendance_import_instruction_3') }}</li>
                                    <li>{{ __('messages.attendance_import_instruction_4') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- File Upload -->
                    <div class="space-y-4">
                        <label class="flex items-center text-sm font-medium text-gray-700">
                            <i class="fas fa-file-excel text-green-600 mr-2"></i>
                            {{ __('messages.select_file') }}
                        </label>
                        
                        <div class="relative">
                            <input type="file" 
                                   wire:model="importFile" 
                                   accept=".xlsx,.xls,.csv"
                                   class="block w-full text-sm text-gray-600 
                                          file:mr-4 file:py-3 file:px-6 
                                          file:rounded-lg file:border-0 
                                          file:text-sm file:font-medium 
                                          file:bg-indigo-50 file:text-indigo-700 
                                          hover:file:bg-indigo-100 
                                          file:transition-all file:duration-200 
                                          border border-gray-300 rounded-lg 
                                          focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                                          cursor-pointer">
                        </div>

                        @error('importFile')
                            <p class="flex items-center text-sm text-red-600">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror

                        @if($importFile)
                            <div class="bg-green-50 border border-green-200 rounded-lg p-3 flex items-center">
                                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                <span class="text-sm text-green-700">
                                    {{ __('messages.file_selected') }}: <strong>{{ $importFile->getClientOriginalName() }}</strong>
                                </span>
                            </div>
                        @endif

                        <!-- Expected Format Info -->
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <h5 class="text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                <i class="fas fa-table text-gray-600 mr-2"></i>
                                {{ __('messages.expected_excel_format') }}
                            </h5>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-xs">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="px-3 py-2 text-left font-medium text-gray-700">Emp ID</th>
                                            <th class="px-3 py-2 text-left font-medium text-gray-700">Name</th>
                                            <th class="px-3 py-2 text-left font-medium text-gray-700">Date</th>
                                            <th class="px-3 py-2 text-left font-medium text-gray-700">Absence</th>
                                            <th class="px-3 py-2 text-left font-medium text-gray-700">Check-In</th>
                                            <th class="px-3 py-2 text-left font-medium text-gray-700">Check-Out</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white">
                                        <tr class="border-t border-gray-200">
                                            <td class="px-3 py-2 text-gray-600">10</td>
                                            <td class="px-3 py-2 text-gray-600">Abel Francisco</td>
                                            <td class="px-3 py-2 text-gray-600">11/09/2025</td>
                                            <td class="px-3 py-2 text-gray-600">0:00</td>
                                            <td class="px-3 py-2 text-gray-600">06:57</td>
                                            <td class="px-3 py-2 text-gray-600">18:24</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-xs text-gray-600 mt-2">
                                <i class="fas fa-fingerprint text-indigo-500 mr-1"></i>
                                <strong>Emp ID</strong> {{ __('messages.must_match_biometric_id') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="border-t border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 rounded-b-xl">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-600">
                            <i class="fas fa-info-circle mr-1"></i>
                            {{ __('messages.max_file_size') }}: <strong>10MB</strong>
                        </div>
                        <div class="flex items-center space-x-3">
                            <button type="button"
                                class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200"
                                wire:click="closeImportModal">
                                <i class="fas fa-times mr-2"></i>
                                {{ __('messages.cancel') }}
                            </button>
                            <button type="button"
                                wire:click="importFromExcel"
                                wire:loading.attr="disabled"
                                :disabled="!$wire.importFile"
                                class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-upload mr-2" wire:loading.remove wire:target="importFromExcel"></i>
                                <i class="fas fa-spinner fa-spin mr-2" wire:loading wire:target="importFromExcel"></i>
                                <span wire:loading.remove wire:target="importFromExcel">{{ __('messages.import') }}</span>
                                <span wire:loading wire:target="importFromExcel">{{ __('messages.importing') }}...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Time Conflicts Modal -->
    @if($showTimeConflictsModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden m-4 transform transition-all duration-300">
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-yellow-600 to-orange-600 px-6 py-4 rounded-t-xl">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-white">⚠️ Horas Duplicadas Detectadas</h3>
                                <p class="text-yellow-100 text-sm">Por favor, selecione as horas corretas para cada funcionário</p>
                            </div>
                        </div>
                        <button type="button" 
                            class="text-white/80 hover:text-white hover:bg-white/20 rounded-lg p-2 transition-all duration-200" 
                            wire:click="closeTimeConflictsModal">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="p-6 overflow-y-auto max-h-[calc(90vh-200px)]">
                    <div class="space-y-4">
                        @foreach($timeConflicts as $index => $conflict)
                            <div class="border border-orange-200 rounded-lg p-4 bg-orange-50">
                                <div class="flex items-start justify-between mb-3">
                                    <div>
                                        <h4 class="font-semibold text-gray-900">{{ $conflict['employee_name'] }}</h4>
                                        <p class="text-sm text-gray-600">
                                            <i class="fas fa-calendar text-orange-500 mr-1"></i>
                                            {{ \Carbon\Carbon::parse($conflict['date'])->format('d/m/Y') }}
                                            <span class="mx-2">•</span>
                                            <i class="fas fa-id-badge text-orange-500 mr-1"></i>
                                            Emp ID: {{ $conflict['emp_id'] }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Check-In -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fas fa-sign-in-alt text-green-500 mr-1"></i>
                                            Hora de Entrada
                                        </label>
                                        @if(count($conflict['check_in_options']) > 0)
                                            <div class="space-y-2">
                                                @foreach($conflict['check_in_options'] as $timeOption)
                                                    <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                                        <input type="radio" 
                                                               wire:model="selectedTimes.{{ $index }}.check_in" 
                                                               value="{{ $timeOption }}"
                                                               class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                                        <span class="ml-3 text-lg font-mono font-semibold text-gray-900">{{ $timeOption }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-gray-500 italic">Sem hora de entrada</p>
                                        @endif
                                    </div>

                                    <!-- Check-Out -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fas fa-sign-out-alt text-red-500 mr-1"></i>
                                            Hora de Saída
                                        </label>
                                        @if(count($conflict['check_out_options']) > 0)
                                            <div class="space-y-2">
                                                @foreach($conflict['check_out_options'] as $timeOption)
                                                    <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                                        <input type="radio" 
                                                               wire:model="selectedTimes.{{ $index }}.check_out" 
                                                               value="{{ $timeOption }}"
                                                               class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                                        <span class="ml-3 text-lg font-mono font-semibold text-gray-900">{{ $timeOption }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-gray-500 italic">Sem hora de saída</p>
                                        @endif
                                    </div>
                                </div>

                                <!-- Original Raw Data -->
                                <div class="mt-3 p-2 bg-gray-100 rounded text-xs text-gray-600">
                                    <strong>Dados originais:</strong> 
                                    Check-In: <code>{{ $conflict['check_in_raw'] ?? 'N/A' }}</code> | 
                                    Check-Out: <code>{{ $conflict['check_out_raw'] ?? 'N/A' }}</code>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-between items-center">
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                        Total de conflitos: <strong>{{ count($timeConflicts) }}</strong>
                    </p>
                    <div class="flex space-x-3">
                        <button type="button"
                            wire:click="closeTimeConflictsModal"
                            class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </button>
                        <button type="button"
                            wire:click="processConflictResolution"
                            class="px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all shadow-md">
                            <i class="fas fa-check mr-2"></i>
                            Confirmar e Importar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Flash Message Backup --}}
    @if (false && session()->has('message'))
        <div x-data="{ show: true }"
             x-init="setTimeout(() => show = false, 3000)"
             x-show="show"
             class="fixed bottom-4 right-4 bg-green-500 text-white py-2 px-4 rounded-md shadow-md">
            {{ session('message') }}
        </div>
    @endif
@endif
