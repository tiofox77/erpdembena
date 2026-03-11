<div class="min-h-screen bg-gradient-to-br from-slate-50 via-green-50 to-emerald-50">
    {{-- Header --}}
    <div class="bg-gradient-to-r from-green-600 via-emerald-600 to-teal-700 shadow-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <a href="{{ route('hr.reports') }}" class="p-2 bg-white/20 backdrop-blur rounded-lg hover:bg-white/30 transition">
                        <i class="fas fa-arrow-left text-white"></i>
                    </a>
                    <div class="p-3 bg-white/20 backdrop-blur rounded-xl">
                        <i class="fas fa-clipboard-check text-2xl text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white">{{ trans('messages.attendance_report') }}</h1>
                        <p class="text-green-100 text-sm">{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <select wire:model.live="period" class="px-4 py-2 rounded-lg bg-white/20 border-white/30 text-white text-sm focus:ring-white focus:border-white [&>option]:text-gray-800">
                        <option value="current_month">{{ trans('messages.current_month') }}</option>
                        <option value="last_month">{{ trans('messages.last_month') }}</option>
                        <option value="current_quarter">{{ trans('messages.current_quarter') }}</option>
                        <option value="current_year">{{ trans('messages.current_year') }}</option>
                        <option value="custom">{{ trans('messages.custom_period') }}</option>
                    </select>
                </div>
            </div>

            {{-- Custom date range --}}
            @if($period === 'custom')
            <div class="flex items-center gap-3 mt-4">
                <input type="date" wire:model.live="customStartDate" class="px-3 py-2 rounded-lg bg-white/20 border-white/30 text-white text-sm focus:ring-white focus:border-white placeholder-white/60" />
                <span class="text-white/70">—</span>
                <input type="date" wire:model.live="customEndDate" class="px-3 py-2 rounded-lg bg-white/20 border-white/30 text-white text-sm focus:ring-white focus:border-white placeholder-white/60" />
            </div>
            @endif
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border p-4">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs text-gray-500">{{ trans('messages.active_employees') }}</p>
                    <i class="fas fa-users text-blue-400 text-sm"></i>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['active_employees']) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border p-4">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs text-gray-500">{{ trans('messages.present') }}</p>
                    <i class="fas fa-check-circle text-green-400 text-sm"></i>
                </div>
                <p class="text-2xl font-bold text-green-600">{{ number_format($summary['present_count']) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border p-4">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs text-gray-500">{{ trans('messages.absent') }}</p>
                    <i class="fas fa-times-circle text-red-400 text-sm"></i>
                </div>
                <p class="text-2xl font-bold text-red-600">{{ number_format($summary['absent_count']) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border p-4">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs text-gray-500">{{ trans('messages.late') }}</p>
                    <i class="fas fa-clock text-yellow-400 text-sm"></i>
                </div>
                <p class="text-2xl font-bold text-yellow-600">{{ number_format($summary['late_count']) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border p-4">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs text-gray-500">{{ trans('messages.attendance_rate') }}</p>
                    <i class="fas fa-chart-line text-emerald-400 text-sm"></i>
                </div>
                <p class="text-2xl font-bold text-emerald-600">{{ $summary['attendance_rate'] }}%</p>
            </div>
        </div>

        {{-- Secondary Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border p-4">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs text-gray-500">{{ trans('messages.half_day') }}</p>
                    <i class="fas fa-adjust text-orange-400 text-sm"></i>
                </div>
                <p class="text-xl font-bold text-orange-600">{{ number_format($summary['half_day_count']) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border p-4">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs text-gray-500">{{ trans('messages.on_leave') }}</p>
                    <i class="fas fa-calendar-check text-teal-400 text-sm"></i>
                </div>
                <p class="text-xl font-bold text-teal-600">{{ number_format($summary['leave_count']) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border p-4">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs text-gray-500">{{ trans('messages.approved_leaves') }}</p>
                    <i class="fas fa-calendar-alt text-purple-400 text-sm"></i>
                </div>
                <p class="text-xl font-bold text-purple-600">{{ number_format($summary['approved_leaves']) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border p-4">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs text-gray-500">{{ trans('messages.total_records') }}</p>
                    <i class="fas fa-database text-gray-400 text-sm"></i>
                </div>
                <p class="text-xl font-bold text-gray-700">{{ number_format($summary['total_records']) }}</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ trans('messages.department') }}</label>
                    <select wire:model.live="departmentFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">{{ trans('messages.all_departments') }}</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ trans('messages.employee') }}</label>
                    <input type="text" wire:model.live.debounce.300ms="employeeFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm" placeholder="{{ trans('messages.search_employee') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ trans('messages.status') }}</label>
                    <select wire:model.live="statusFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">{{ trans('messages.all') }}</option>
                        <option value="present">{{ trans('messages.present') }}</option>
                        <option value="absent">{{ trans('messages.absent') }}</option>
                        <option value="late">{{ trans('messages.late') }}</option>
                        <option value="half_day">{{ trans('messages.half_day') }}</option>
                        <option value="leave">{{ trans('messages.on_leave') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ trans('messages.view') }}</label>
                    <select wire:model.live="viewMode" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="detailed">{{ trans('messages.detailed_view') }}</option>
                        <option value="summary">{{ trans('messages.summary_view') }}</option>
                        <option value="calendar">{{ trans('messages.calendar_view') }}</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button wire:click="$set('departmentFilter', ''); $set('employeeFilter', ''); $set('statusFilter', '')" class="w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm transition">
                        <i class="fas fa-undo mr-1"></i> {{ trans('messages.clear_filters') }}
                    </button>
                </div>
            </div>
        </div>

        {{-- Department Breakdown --}}
        <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">
                <i class="fas fa-building text-emerald-500 mr-2"></i>{{ trans('messages.department_breakdown') }}
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ trans('messages.department') }}</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ trans('messages.employees') }}</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-green-600 uppercase">{{ trans('messages.present') }}</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-red-600 uppercase">{{ trans('messages.absent') }}</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-yellow-600 uppercase">{{ trans('messages.late') }}</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-teal-600 uppercase">{{ trans('messages.on_leave') }}</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ trans('messages.attendance_rate') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($departmentBreakdown as $dept)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ $dept->name }}</td>
                            <td class="px-4 py-2 text-center text-sm text-gray-600">{{ $dept->active_employees_count }}</td>
                            <td class="px-4 py-2 text-center text-sm font-semibold text-green-600">{{ $dept->stats['present'] }}</td>
                            <td class="px-4 py-2 text-center text-sm font-semibold text-red-600">{{ $dept->stats['absent'] }}</td>
                            <td class="px-4 py-2 text-center text-sm font-semibold text-yellow-600">{{ $dept->stats['late'] }}</td>
                            <td class="px-4 py-2 text-center text-sm font-semibold text-teal-600">{{ $dept->stats['leave'] }}</td>
                            <td class="px-4 py-2 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full {{ $dept->stats['rate'] >= 80 ? 'bg-green-500' : ($dept->stats['rate'] >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ $dept->stats['rate'] }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold {{ $dept->stats['rate'] >= 80 ? 'text-green-600' : ($dept->stats['rate'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">{{ $dept->stats['rate'] }}%</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Main Content: Detailed, Calendar, or Summary --}}
        @if($viewMode === 'calendar')
            {{-- Calendar View --}}
            <div class="bg-white rounded-xl shadow-sm border overflow-hidden mb-6">
                {{-- Calendar Header --}}
                <div class="px-6 py-4 border-b bg-gray-50 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <h3 class="text-sm font-semibold text-gray-700">
                        <i class="fas fa-calendar text-emerald-500 mr-2"></i>{{ trans('messages.calendar_view') }}
                    </h3>
                    <div class="flex items-center gap-3">
                        <input type="text" wire:model.live.debounce.300ms="calendarEmployeeFilter" class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm w-48" placeholder="{{ trans('messages.search_employee') }}">
                        <div class="flex items-center gap-1 bg-white border rounded-lg px-1">
                            <button wire:click="previousMonth" class="p-1.5 hover:bg-gray-100 rounded transition" title="Previous month">
                                <i class="fas fa-chevron-left text-gray-600 text-xs"></i>
                            </button>
                            <span class="px-3 py-1 text-sm font-semibold text-gray-700 min-w-[140px] text-center">{{ $calendarData['month_label'] }}</span>
                            <button wire:click="nextMonth" class="p-1.5 hover:bg-gray-100 rounded transition" title="Next month">
                                <i class="fas fa-chevron-right text-gray-600 text-xs"></i>
                            </button>
                        </div>
                        <button wire:click="goToCurrentMonth" class="px-3 py-1.5 bg-emerald-100 text-emerald-700 rounded-lg text-xs font-medium hover:bg-emerald-200 transition">
                            {{ trans('messages.today') }}
                        </button>
                    </div>
                </div>

                {{-- Legend --}}
                <div class="px-6 py-3 border-b bg-gray-50/50 flex flex-wrap gap-4 text-xs">
                    <span class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-green-500 inline-block"></span> {{ trans('messages.present') }}</span>
                    <span class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-red-500 inline-block"></span> {{ trans('messages.absent') }}</span>
                    <span class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-yellow-400 inline-block"></span> {{ trans('messages.late') }}</span>
                    <span class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-orange-400 inline-block"></span> {{ trans('messages.half_day') }}</span>
                    <span class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-teal-500 inline-block"></span> {{ trans('messages.on_leave') }} (Attendance)</span>
                    <span class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-purple-500 inline-block"></span> {{ trans('messages.on_leave') }} (Leave Mgmt)</span>
                    <span class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-gray-200 inline-block"></span> {{ trans('messages.no_record') }}</span>
                    <span class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-slate-100 border border-slate-300 inline-block"></span> Weekend</span>
                </div>

                {{-- Calendar Grid --}}
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-xs">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-3 py-2 text-left font-semibold text-gray-700 border-b sticky left-0 bg-gray-50 z-10 min-w-[180px]">{{ trans('messages.employee') }}</th>
                                @foreach($calendarData['days'] as $day)
                                    <th class="px-0 py-2 text-center font-medium border-b min-w-[28px] {{ $day['is_weekend'] ? 'bg-slate-100 text-gray-400' : 'text-gray-600' }}">
                                        <div class="text-[10px] leading-tight">{{ $day['label'] }}</div>
                                        <div class="font-bold">{{ $day['day'] }}</div>
                                    </th>
                                @endforeach
                                <th class="px-2 py-2 text-center font-semibold text-green-700 border-b bg-green-50 min-w-[28px]" title="Present">P</th>
                                <th class="px-2 py-2 text-center font-semibold text-red-700 border-b bg-red-50 min-w-[28px]" title="Absent">A</th>
                                <th class="px-2 py-2 text-center font-semibold text-yellow-700 border-b bg-yellow-50 min-w-[28px]" title="Late">L</th>
                                <th class="px-2 py-2 text-center font-semibold text-purple-700 border-b bg-purple-50 min-w-[28px]" title="Leave Mgmt">F</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($calendarData['grid'] as $row)
                            <tr class="hover:bg-gray-50/50 border-b border-gray-100">
                                <td class="px-3 py-1.5 sticky left-0 bg-white z-10 border-r">
                                    <div class="text-xs font-medium text-gray-900 truncate max-w-[170px]" title="{{ $row['employee']->full_name }}">{{ $row['employee']->full_name }}</div>
                                    <div class="text-[10px] text-gray-400 truncate">{{ $row['employee']->department->name ?? '' }}</div>
                                </td>
                                @foreach($row['days'] as $dayCell)
                                    <td class="px-0 py-1 text-center {{ $dayCell['is_weekend'] ? 'bg-slate-50' : '' }}">
                                        @if($dayCell['is_weekend'] && !$dayCell['status'])
                                            <span class="inline-block w-5 h-5 rounded bg-slate-100"></span>
                                        @elseif($dayCell['status'] === 'present')
                                            <span class="inline-block w-5 h-5 rounded bg-green-500 cursor-default" title="{{ $dayCell['tooltip'] }}"></span>
                                        @elseif($dayCell['status'] === 'absent')
                                            <span class="inline-block w-5 h-5 rounded bg-red-500 cursor-default" title="{{ $dayCell['tooltip'] }}"></span>
                                        @elseif($dayCell['status'] === 'late')
                                            <span class="inline-block w-5 h-5 rounded bg-yellow-400 cursor-default" title="{{ $dayCell['tooltip'] }}"></span>
                                        @elseif($dayCell['status'] === 'half_day')
                                            <span class="inline-block w-5 h-5 rounded bg-orange-400 cursor-default" title="{{ $dayCell['tooltip'] }}"></span>
                                        @elseif($dayCell['status'] === 'leave')
                                            <span class="inline-block w-5 h-5 rounded bg-teal-500 cursor-default" title="{{ $dayCell['tooltip'] }}"></span>
                                        @elseif($dayCell['status'] === 'leave_mgmt')
                                            <span class="inline-block w-5 h-5 rounded bg-purple-500 cursor-default" title="{{ $dayCell['tooltip'] }}"></span>
                                        @elseif($dayCell['status'] === 'no_record')
                                            <span class="inline-block w-5 h-5 rounded bg-gray-200 cursor-default" title="{{ $dayCell['tooltip'] }}"></span>
                                        @else
                                            <span class="inline-block w-5 h-5"></span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="px-1 py-1 text-center bg-green-50 font-bold text-green-700">{{ $row['stats']['present'] }}</td>
                                <td class="px-1 py-1 text-center bg-red-50 font-bold {{ $row['stats']['absent'] > 0 ? 'text-red-700' : 'text-gray-300' }}">{{ $row['stats']['absent'] }}</td>
                                <td class="px-1 py-1 text-center bg-yellow-50 font-bold {{ $row['stats']['late'] > 0 ? 'text-yellow-700' : 'text-gray-300' }}">{{ $row['stats']['late'] }}</td>
                                <td class="px-1 py-1 text-center bg-purple-50 font-bold {{ $row['stats']['leave_mgmt'] > 0 ? 'text-purple-700' : 'text-gray-300' }}">{{ $row['stats']['leave_mgmt'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ count($calendarData['days']) + 5 }}" class="px-6 py-12 text-center">
                                    <i class="fas fa-inbox text-4xl text-gray-300"></i>
                                    <p class="text-gray-500 mt-2">{{ trans('messages.no_records_found') }}</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(count($calendarData['grid']) >= 50)
                <div class="px-6 py-3 border-t bg-yellow-50 text-xs text-yellow-700">
                    <i class="fas fa-info-circle mr-1"></i> {{ trans('messages.showing_max_employees') }}
                </div>
                @endif
            </div>
        @elseif($viewMode === 'detailed')
            {{-- Detailed View --}}
            <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                <div class="px-6 py-4 border-b bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-700">
                        <i class="fas fa-list text-emerald-500 mr-2"></i>{{ trans('messages.detailed_records') }}
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ trans('messages.date') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ trans('messages.employee') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ trans('messages.department') }}</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ trans('messages.time_in') }}</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ trans('messages.time_out') }}</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ trans('messages.status') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ trans('messages.remarks') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($records as $record)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-900">
                                    {{ \Carbon\Carbon::parse($record->date)->format('d/m/Y') }}
                                    <span class="text-xs text-gray-400 ml-1">{{ \Carbon\Carbon::parse($record->date)->translatedFormat('D') }}</span>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $record->employee->full_name ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">{{ $record->employee->department->name ?? '-' }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-center text-sm">
                                    @if($record->time_in)
                                        <span class="text-gray-700">{{ \Carbon\Carbon::parse($record->time_in)->format('H:i') }}</span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-center text-sm">
                                    @if($record->time_out)
                                        <span class="text-gray-700">{{ \Carbon\Carbon::parse($record->time_out)->format('H:i') }}</span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-center">
                                    @switch($record->status)
                                        @case('present')
                                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800"><i class="fas fa-check mr-1"></i>{{ trans('messages.present') }}</span>
                                            @break
                                        @case('absent')
                                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800"><i class="fas fa-times mr-1"></i>{{ trans('messages.absent') }}</span>
                                            @break
                                        @case('late')
                                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800"><i class="fas fa-clock mr-1"></i>{{ trans('messages.late') }}</span>
                                            @break
                                        @case('half_day')
                                            <span class="px-2 py-1 text-xs rounded-full bg-orange-100 text-orange-800"><i class="fas fa-adjust mr-1"></i>{{ trans('messages.half_day') }}</span>
                                            @break
                                        @case('leave')
                                            <span class="px-2 py-1 text-xs rounded-full bg-teal-100 text-teal-800"><i class="fas fa-calendar-check mr-1"></i>{{ trans('messages.on_leave') }}</span>
                                            @break
                                    @endswitch
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-500 max-w-xs truncate">{{ $record->remarks ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <i class="fas fa-inbox text-4xl text-gray-300"></i>
                                    <p class="text-gray-500 mt-2">{{ trans('messages.no_records_found') }}</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t">{{ $records->links() }}</div>
            </div>
        @else
            {{-- Summary View per Employee --}}
            <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                <div class="px-6 py-4 border-b bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-700">
                        <i class="fas fa-users text-emerald-500 mr-2"></i>{{ trans('messages.employee_summary') }}
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ trans('messages.employee') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ trans('messages.department') }}</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-green-600 uppercase">{{ trans('messages.present') }}</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-red-600 uppercase">{{ trans('messages.absent') }}</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-yellow-600 uppercase">{{ trans('messages.late') }}</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-orange-600 uppercase">{{ trans('messages.half_day') }}</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-teal-600 uppercase">{{ trans('messages.on_leave') }}</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-purple-600 uppercase">{{ trans('messages.leave_management') }}</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ trans('messages.total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($employeeSummary as $employee)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $employee->full_name }}</div>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">{{ $employee->department->name ?? '-' }}</td>
                                <td class="px-6 py-3 text-center">
                                    <span class="px-2 py-1 text-sm font-bold text-green-600">{{ $employee->attendance_stats['present'] }}</span>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <span class="px-2 py-1 text-sm font-bold {{ $employee->attendance_stats['absent'] > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $employee->attendance_stats['absent'] }}</span>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <span class="px-2 py-1 text-sm font-bold {{ $employee->attendance_stats['late'] > 0 ? 'text-yellow-600' : 'text-gray-400' }}">{{ $employee->attendance_stats['late'] }}</span>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <span class="px-2 py-1 text-sm font-bold {{ $employee->attendance_stats['half_day'] > 0 ? 'text-orange-600' : 'text-gray-400' }}">{{ $employee->attendance_stats['half_day'] }}</span>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <span class="px-2 py-1 text-sm font-bold {{ $employee->attendance_stats['leave_attendance'] > 0 ? 'text-teal-600' : 'text-gray-400' }}">{{ $employee->attendance_stats['leave_attendance'] }}</span>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    @if($employee->attendance_stats['leave_management'] > 0)
                                        <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-700 font-semibold">{{ $employee->attendance_stats['leave_management'] }} {{ trans('messages.days') }}</span>
                                    @else
                                        <span class="text-gray-400 text-sm">0</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <span class="px-2 py-1 text-sm font-bold text-gray-700">{{ $employee->attendance_stats['total_records'] }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center">
                                    <i class="fas fa-inbox text-4xl text-gray-300"></i>
                                    <p class="text-gray-500 mt-2">{{ trans('messages.no_records_found') }}</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t">{{ $employeeSummary->links() }}</div>
            </div>
        @endif
    </div>
</div>
