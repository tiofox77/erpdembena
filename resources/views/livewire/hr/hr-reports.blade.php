<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    {{-- Header --}}
    <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-700 shadow-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-white/20 backdrop-blur rounded-xl">
                        <i class="fas fa-file-chart-line text-2xl text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white">{{ trans('messages.hr_reports') }}</h1>
                        <p class="text-blue-100 text-sm">
                            {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                        </p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <select wire:model.live="period" class="px-4 py-2 rounded-lg bg-white/20 border-white/30 text-white text-sm focus:ring-white focus:border-white [&>option]:text-gray-800">
                        <option value="current_month">{{ trans('messages.current_month') }}</option>
                        <option value="last_month">{{ trans('messages.last_month') }}</option>
                        <option value="current_quarter">{{ trans('messages.current_quarter') }}</option>
                        <option value="current_year">{{ trans('messages.current_year') }}</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Stats Overview --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-sm border p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_employees'] }}</p>
                        <p class="text-xs text-gray-500">{{ trans('messages.active_employees') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-money-bill-wave text-emerald-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['payroll_periods'] }}</p>
                        <p class="text-xs text-gray-500">{{ trans('messages.payroll_periods') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-purple-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['overtime_records'] }}</p>
                        <p class="text-xs text-gray-500">{{ trans('messages.overtime_records') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-times text-orange-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['leave_requests'] }}</p>
                        <p class="text-xs text-gray-500">{{ trans('messages.leave_requests') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Report Categories --}}
        <div class="space-y-6">
            {{-- Payroll Reports --}}
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-money-bill-wave text-emerald-600"></i>
                    {{ trans('messages.payroll_reports') }}
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    {{-- Payroll Report --}}
                    <a href="{{ route('hr.reports.payroll') }}" class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-md transition group">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition">
                                <i class="fas fa-file-invoice-dollar text-emerald-600 text-xl"></i>
                            </div>
                            <span class="px-2 py-1 bg-emerald-100 text-emerald-700 text-xs rounded-full">{{ $stats['payroll_periods'] }}</span>
                        </div>
                        <h3 class="text-base font-semibold text-gray-800 mb-2">{{ trans('messages.payroll_report') }}</h3>
                        <p class="text-sm text-gray-500">{{ trans('messages.payroll_by_period_desc') }}</p>
                        <div class="mt-4 flex items-center text-emerald-600 text-sm font-medium">
                            {{ trans('messages.view_report') }} <i class="fas fa-arrow-right ml-2"></i>
                        </div>
                    </a>
                </div>
            </div>

            {{-- Time & Attendance Reports --}}
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-clock text-purple-600"></i>
                    {{ trans('messages.time_attendance_reports') }}
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    {{-- Overtime Report --}}
                    <a href="{{ route('hr.reports.overtime') }}" class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-md transition group">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition">
                                <i class="fas fa-clock text-purple-600 text-xl"></i>
                            </div>
                            <span class="px-2 py-1 bg-purple-100 text-purple-700 text-xs rounded-full">{{ $stats['overtime_records'] }}</span>
                        </div>
                        <h3 class="text-base font-semibold text-gray-800 mb-2">{{ trans('messages.overtime_report') }}</h3>
                        <p class="text-sm text-gray-500">{{ trans('messages.overtime_report_desc') }}</p>
                        <div class="mt-4 flex items-center text-purple-600 text-sm font-medium">
                            {{ trans('messages.view_report') }} <i class="fas fa-arrow-right ml-2"></i>
                        </div>
                    </a>
                </div>
            </div>

            {{-- Financial Reports --}}
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-hand-holding-usd text-amber-600"></i>
                    {{ trans('messages.financial_reports') }}
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    {{-- Advances Report --}}
                    <a href="{{ route('hr.reports.advances') }}" class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-md transition group">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition">
                                <i class="fas fa-hand-holding-usd text-amber-600 text-xl"></i>
                            </div>
                            <span class="px-2 py-1 bg-amber-100 text-amber-700 text-xs rounded-full">{{ $stats['salary_advances'] }}</span>
                        </div>
                        <h3 class="text-base font-semibold text-gray-800 mb-2">{{ trans('messages.advances_report') }}</h3>
                        <p class="text-sm text-gray-500">{{ trans('messages.advances_report_desc') }}</p>
                        <div class="mt-4 flex items-center text-amber-600 text-sm font-medium">
                            {{ trans('messages.view_report') }} <i class="fas fa-arrow-right ml-2"></i>
                        </div>
                    </a>

                    {{-- Discounts Report --}}
                    <a href="{{ route('hr.reports.discounts') }}" class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-md transition group">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition">
                                <i class="fas fa-minus-circle text-red-600 text-xl"></i>
                            </div>
                            <span class="px-2 py-1 bg-red-100 text-red-700 text-xs rounded-full">{{ $stats['salary_discounts'] }}</span>
                        </div>
                        <h3 class="text-base font-semibold text-gray-800 mb-2">{{ trans('messages.discounts_report') }}</h3>
                        <p class="text-sm text-gray-500">{{ trans('messages.discounts_report_desc') }}</p>
                        <div class="mt-4 flex items-center text-red-600 text-sm font-medium">
                            {{ trans('messages.view_report') }} <i class="fas fa-arrow-right ml-2"></i>
                        </div>
                    </a>
                </div>
            </div>

            {{-- Employee Reports --}}
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-users text-blue-600"></i>
                    {{ trans('messages.employee_reports') }}
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    {{-- Leave Report --}}
                    <a href="{{ route('hr.reports.leave') }}" class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-md transition group">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition">
                                <i class="fas fa-calendar-times text-orange-600 text-xl"></i>
                            </div>
                            <span class="px-2 py-1 bg-orange-100 text-orange-700 text-xs rounded-full">{{ $stats['leave_requests'] }}</span>
                        </div>
                        <h3 class="text-base font-semibold text-gray-800 mb-2">{{ trans('messages.leave_report') }}</h3>
                        <p class="text-sm text-gray-500">{{ trans('messages.leave_report_desc') }}</p>
                        <div class="mt-4 flex items-center text-orange-600 text-sm font-medium">
                            {{ trans('messages.view_report') }} <i class="fas fa-arrow-right ml-2"></i>
                        </div>
                    </a>

                    {{-- Disciplinary Measures --}}
                    <a href="{{ route('hr.disciplinary-measures') }}" class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-md transition group">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 bg-rose-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition">
                                <i class="fas fa-gavel text-rose-600 text-xl"></i>
                            </div>
                            <span class="px-2 py-1 bg-rose-100 text-rose-700 text-xs rounded-full">{{ $stats['disciplinary_measures'] }}</span>
                        </div>
                        <h3 class="text-base font-semibold text-gray-800 mb-2">{{ trans('messages.disciplinary_report') }}</h3>
                        <p class="text-sm text-gray-500">{{ trans('messages.disciplinary_report_desc') }}</p>
                        <div class="mt-4 flex items-center text-rose-600 text-sm font-medium">
                            {{ trans('messages.view_report') }} <i class="fas fa-arrow-right ml-2"></i>
                        </div>
                    </a>

                    {{-- Employee Directory --}}
                    <a href="{{ route('hr.employees') }}" class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-md transition group">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition">
                                <i class="fas fa-address-book text-cyan-600 text-xl"></i>
                            </div>
                            <span class="px-2 py-1 bg-cyan-100 text-cyan-700 text-xs rounded-full">{{ $stats['total_employees'] }}</span>
                        </div>
                        <h3 class="text-base font-semibold text-gray-800 mb-2">{{ trans('messages.employee_directory') }}</h3>
                        <p class="text-sm text-gray-500">{{ trans('messages.employee_directory_desc') }}</p>
                        <div class="mt-4 flex items-center text-cyan-600 text-sm font-medium">
                            {{ trans('messages.view_report') }} <i class="fas fa-arrow-right ml-2"></i>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
