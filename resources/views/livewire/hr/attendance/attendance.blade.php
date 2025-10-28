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
                                <button
                                    wire:click="create"
                                    class="inline-flex items-center px-4 py-2 bg-white border border-transparent rounded-md font-semibold text-blue-700 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105"
                                >
                                    <i class="fas fa-plus mr-2"></i>
                                    {{ __('attendance.register_attendance') }}
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
                                                             @php
                                                                 $employeeShift = $attendance->employee->shiftAssignments()
                                                                     ->whereDate('start_date', '<=', $attendance->date)
                                                                     ->where(function($q) use ($attendance) {
                                                                         $q->whereNull('end_date')
                                                                           ->orWhereDate('end_date', '>=', $attendance->date);
                                                                     })
                                                                     ->with('shift')
                                                                     ->first();
                                                                 
                                                                 $shiftInfo = $employeeShift && $employeeShift->shift 
                                                                     ? $employeeShift->shift->name . ' (' . $employeeShift->shift->start_time->format('H:i') . ' - ' . $employeeShift->shift->end_time->format('H:i') . ')'
                                                                     : 'Sem turno';
                                                             @endphp
                                                             <div class="text-xs text-gray-500 flex items-center mt-1">
                                                                 <i class="fas fa-clock mr-1 text-indigo-400"></i>
                                                                 {{ $shiftInfo }}
                                                             </div>
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
    @include('livewire.hr.attendance.modals._delete-modal')
    @include('livewire.hr.attendance.modals._import-modal')
    @include('livewire.hr.attendance.modals._shift-mismatch-modal')
    @include('livewire.hr.attendance.modals._incomplete-records-modal')
    @include('livewire.hr.attendance.modals._time-conflicts-modal')
    @include('livewire.hr.attendance.modals._calendar-batch-modal')

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
