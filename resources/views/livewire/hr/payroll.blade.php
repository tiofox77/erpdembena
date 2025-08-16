{{-- Modern Full Width Payroll Management Interface --}}
<div class="min-h-screen bg-gray-50">
    <div class="w-full h-full">
        <div class="flex flex-col min-h-screen">
            
            {{-- Header Section with Gradient - Full Width --}}
            <div class="bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 px-6 py-8 text-white flex-shrink-0">
                <div class="w-full flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="bg-white/20 backdrop-blur-sm rounded-lg p-3">
                            <i class="fas fa-money-bill-wave text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold">{{ __('messages.payroll_management') }}</h1>
                            <p class="text-blue-100 mt-1">{{ __('messages.payroll_management_description') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button
                            wire:click="openEmployeeSearch"
                            class="bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white px-6 py-3 rounded-lg font-medium transition-all duration-300 hover:scale-105 flex items-center space-x-2 border border-white/20"
                        >
                            <i class="fas fa-search text-lg"></i>
                            <span>{{ __('messages.process_payroll') }}</span>
                        </button>
                        <button
                            wire:click="exportPayroll"
                            class="bg-green-500/90 hover:bg-green-400 text-white px-6 py-3 rounded-lg font-medium transition-all duration-300 hover:scale-105 flex items-center space-x-2"
                        >
                            <i class="fas fa-file-export text-lg"></i>
                            <span>{{ __('messages.export') }}</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Stats Cards - Full Width --}}
            <div class="bg-white border-b border-gray-200 px-6 py-6 flex-shrink-0">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-gradient-to-br from-green-50 to-emerald-100 p-6 rounded-xl border border-green-200 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-600 font-medium text-sm">{{ __('messages.total_processed') }}</p>
                                <p class="text-3xl font-bold text-green-700">{{ number_format($payrolls->count()) }}</p>
                            </div>
                            <div class="bg-green-500 p-3 rounded-full">
                                <i class="fas fa-check text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-xl border border-blue-200 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-600 font-medium text-sm">{{ __('messages.pending_approval') }}</p>
                                <p class="text-3xl font-bold text-blue-700">{{ $payrolls->where('status', 'draft')->count() }}</p>
                            </div>
                            <div class="bg-blue-500 p-3 rounded-full">
                                <i class="fas fa-clock text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-6 rounded-xl border border-purple-200 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-600 font-medium text-sm">{{ __('messages.approved') }}</p>
                                <p class="text-3xl font-bold text-purple-700">{{ $payrolls->where('status', 'approved')->count() }}</p>
                            </div>
                            <div class="bg-purple-500 p-3 rounded-full">
                                <i class="fas fa-user-check text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-amber-50 to-yellow-100 p-6 rounded-xl border border-amber-200 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-amber-600 font-medium text-sm">{{ __('messages.paid') }}</p>
                                <p class="text-3xl font-bold text-amber-700">{{ $payrolls->where('status', 'paid')->count() }}</p>
                            </div>
                            <div class="bg-amber-500 p-3 rounded-full">
                                <i class="fas fa-money-bill text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filters Section - Full Width --}}
            <div class="bg-white border-b border-gray-200 px-6 py-6 flex-shrink-0">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-6 border border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-700 flex items-center">
                            <i class="fas fa-filter text-blue-500 mr-2"></i>
                            {{ __('messages.filters_and_search') }}
                        </h3>
                        <button
                            wire:click="resetFilters"
                            class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-colors flex items-center space-x-2"
                        >
                            <i class="fas fa-undo text-sm"></i>
                            <span class="text-sm font-medium">{{ __('messages.reset') }}</span>
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-search text-gray-400 mr-1"></i>
                                {{ __('messages.search_employee') }}
                            </label>
                            <div class="relative">
                                <input
                                    type="text"
                                    id="search"
                                    wire:model.live.debounce.300ms="search"
                                    placeholder="{{ __('messages.search_employee_placeholder') }}"
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                >
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="department_filter" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-building text-gray-400 mr-1"></i>
                                {{ __('messages.department') }}
                            </label>
                            <select
                                id="department_filter"
                                wire:model.live="filters.department_id"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                            >
                                <option value="">{{ __('messages.all_departments') }}</option>
                                @foreach(\App\Models\HR\Department::all() as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="month_filter" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar text-gray-400 mr-1"></i>
                                {{ __('messages.month') }}
                            </label>
                            <select
                                id="month_filter"
                                wire:model.live="filters.month"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                            >
                                <option value="">{{ __('messages.all_months') }}</option>
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">
                                        {{ \Carbon\Carbon::createFromDate(null, $i, 1)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label for="status_filter" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-flag text-gray-400 mr-1"></i>
                                {{ __('messages.status') }}
                            </label>
                            <select
                                id="status_filter"
                                wire:model.live="filters.status"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                            >
                                <option value="">{{ __('messages.all_status') }}</option>
                                <option value="draft">{{ __('messages.draft') }}</option>
                                <option value="approved">{{ __('messages.approved') }}</option>
                                <option value="paid">{{ __('messages.paid') }}</option>
                                <option value="cancelled">{{ __('messages.cancelled') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main Content Area - Payroll Table --}}
            <div class="flex-1 bg-white px-6 py-6">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-6 border border-gray-200 mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 flex items-center mb-4">
                        <i class="fas fa-table text-blue-500 mr-2"></i>
                        {{ __('messages.payroll_records') }}
                    </h3>
                    
                    {{-- Modern Payroll Cards --}}
                    <div class="space-y-4">
                        @forelse($payrolls as $payroll)
                            <div class="bg-white border border-gray-200 rounded-xl p-6 hover:shadow-lg transition-all duration-300 hover:border-blue-300">
                                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-center">
                                    
                                    <!-- Employee Info Column -->
                                    <div class="lg:col-span-2">
                                        <div class="flex items-center space-x-3">
                                            @if($payroll->employee->photo)
                                                <img src="{{ asset('storage/' . $payroll->employee->photo) }}" alt="{{ $payroll->employee->full_name }}" class="h-12 w-12 rounded-full border-2 border-gray-200">
                                            @else
                                                <div class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center border-2 border-blue-200">
                                                    <i class="fas fa-user text-blue-600"></i>
                                                </div>
                                            @endif
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $payroll->employee->full_name }}</p>
                                                <p class="text-xs text-gray-500 truncate">{{ $payroll->employee->department->name ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Period Info Column -->
                                    <div class="lg:col-span-2">
                                        <div class="space-y-1">
                                            <div class="flex items-center space-x-2">
                                                <div class="w-2 h-2 bg-blue-400 rounded-full"></div>
                                                <span class="text-sm font-medium text-gray-900">{{ $payroll->payrollPeriod->name ?? 'N/A' }}</span>
                                            </div>
                                            @if($payroll->payrollPeriod && $payroll->payrollPeriod->start_date && $payroll->payrollPeriod->end_date)
                                                <p class="text-xs text-gray-500 ml-4">{{ $payroll->payrollPeriod->start_date->format('d/m') }} - {{ $payroll->payrollPeriod->end_date->format('d/m/Y') }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Attendance & Leave Column -->
                                    <div class="lg:col-span-2">
                                        <div class="space-y-2">
                                            <div class="flex items-center space-x-2 text-sm">
                                                <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                                                <i class="fas fa-clock text-green-500 text-xs"></i>
                                                <span class="text-gray-700 font-medium">{{ number_format($payroll->attendance_hours ?? 0, 1) }}{{ __('payroll.hours') }}</span>
                                            </div>
                                            <div class="flex items-center space-x-2 text-sm">
                                                <div class="w-2 h-2 bg-yellow-400 rounded-full"></div>
                                                <i class="fas fa-calendar-alt text-yellow-500 text-xs"></i>
                                                <span class="text-gray-700">{{ number_format($payroll->leave_days ?? 0, 1) }} {{ __('payroll.days') }}</span>
                                            </div>
                                            @if(($payroll->maternity_days ?? 0) > 0)
                                                <div class="flex items-center space-x-2 text-xs">
                                                    <i class="fas fa-baby text-pink-500"></i>
                                                    <span class="text-pink-600 font-medium">{{ number_format($payroll->maternity_days ?? 0, 1) }} {{ __('payroll.maternity') }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Salary Info & Advanced Discounts Column -->
                                    <div class="lg:col-span-4">
                                        <div class="space-y-3">
                                            <!-- Salary Summary -->
                                            <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg p-3 border border-green-200">
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div class="text-center">
                                                        <p class="text-xs text-green-600 font-medium">{{ __('payroll.gross') }}</p>
                                                        <p class="text-sm font-bold text-green-700">{{ number_format($payroll->gross_salary, 0) }}</p>
                                                    </div>
                                                    <div class="text-center">
                                                        <p class="text-xs text-green-600 font-medium">{{ __('payroll.net') }}</p>
                                                        <p class="text-sm font-bold text-green-800">{{ number_format($payroll->net_salary, 0) }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Advanced Discounts Details -->
                                            <div class="bg-gradient-to-r from-red-50 to-pink-50 rounded-lg p-3 border border-red-200">
                                                <div class="flex items-center justify-between mb-2">
                                                    <div class="flex items-center space-x-2">
                                                        <i class="fas fa-minus-circle text-red-500 text-xs"></i>
                                                        <p class="text-xs text-red-700 font-semibold">{{ __('payroll.detailed_discounts') }}</p>
                                                    </div>
                                                    <p class="text-xs font-bold text-red-800">{{ number_format($payroll->total_deductions, 0) }}</p>
                                                </div>
                                                
                                                <div class="space-y-1.5">
                                                    <!-- Salary Advances -->
                                                    @if($payroll->advance_deduction > 0)
                                                        <div class="flex justify-between items-center text-xs">
                                                            <div class="flex items-center space-x-1.5">
                                                                <i class="fas fa-hand-holding-usd text-orange-500"></i>
                                                                <span class="text-gray-700">{{ __('payroll.salary_advances') }}</span>
                                                            </div>
                                                            <span class="font-medium text-orange-700">-{{ number_format($payroll->advance_deduction, 0) }}</span>
                                                        </div>
                                                    @endif
                                                    
                                                    <!-- Salary Discounts -->
                                                    @if($payroll->total_salary_discounts > 0)
                                                        <div class="flex justify-between items-center text-xs">
                                                            <div class="flex items-center space-x-1.5">
                                                                <i class="fas fa-percentage text-purple-500"></i>
                                                                <span class="text-gray-700">{{ __('payroll.salary_discounts') }}</span>
                                                            </div>
                                                            <span class="font-medium text-purple-700">-{{ number_format($payroll->total_salary_discounts, 0) }}</span>
                                                        </div>
                                                    @endif
                                                    
                                                    <!-- Income Tax -->
                                                    @if($payroll->income_tax > 0)
                                                        <div class="flex justify-between items-center text-xs">
                                                            <div class="flex items-center space-x-1.5">
                                                                <i class="fas fa-receipt text-blue-500"></i>
                                                                <span class="text-gray-700">{{ __('payroll.income_tax') }}</span>
                                                            </div>
                                                            <span class="font-medium text-blue-700">-{{ number_format($payroll->income_tax, 0) }}</span>
                                                        </div>
                                                    @endif
                                                    
                                                    <!-- Social Security -->
                                                    @if($payroll->social_security > 0)
                                                        <div class="flex justify-between items-center text-xs">
                                                            <div class="flex items-center space-x-1.5">
                                                                <i class="fas fa-shield-alt text-indigo-500"></i>
                                                                <span class="text-gray-700">{{ __('payroll.social_security') }}</span>
                                                            </div>
                                                            <span class="font-medium text-indigo-700">-{{ number_format($payroll->social_security, 0) }}</span>
                                                        </div>
                                                    @endif
                                                    
                                                    <!-- Attendance Deductions -->
                                                    @if(($payroll->late_deduction ?? 0) > 0 || ($payroll->absence_deduction ?? 0) > 0)
                                                        <div class="flex justify-between items-center text-xs">
                                                            <div class="flex items-center space-x-1.5">
                                                                <i class="fas fa-clock text-yellow-500"></i>
                                                                <span class="text-gray-700">{{ __('payroll.attendance_deductions') }}</span>
                                                            </div>
                                                            <span class="font-medium text-yellow-700">-{{ number_format(($payroll->late_deduction ?? 0) + ($payroll->absence_deduction ?? 0), 0) }}</span>
                                                        </div>
                                                    @endif
                                                    
                                                    <!-- Other Deductions -->
                                                    @if($payroll->other_deductions > 0)
                                                        <div class="flex justify-between items-center text-xs">
                                                            <div class="flex items-center space-x-1.5">
                                                                <i class="fas fa-ellipsis-h text-gray-500"></i>
                                                                <span class="text-gray-700">{{ __('payroll.other_deductions') }}</span>
                                                            </div>
                                                            <span class="font-medium text-gray-700">-{{ number_format($payroll->other_deductions, 0) }}</span>
                                                        </div>
                                                    @endif
                                                    
                                                    <!-- Show message if no deductions -->
                                                    @if($payroll->total_deductions == 0)
                                                        <div class="text-center py-1">
                                                            <p class="text-xs text-gray-500">{{ __('payroll.no_discounts') }}</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Status Column -->
                                    <div class="lg:col-span-1">
                                        <div class="flex justify-center">
                                            @if($payroll->status === 'draft')
                                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 border border-yellow-200">
                                                    <div class="w-2 h-2 bg-yellow-500 rounded-full mr-2 animate-pulse"></div>
                                                    {{ __('payroll.draft') }}
                                                </span>
                                            @elseif($payroll->status === 'approved')
                                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                                                    <div class="w-2 h-2 bg-blue-500 rounded-full mr-2"></div>
                                                    {{ __('payroll.approved') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-200">
                                                    <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                                    {{ __('payroll.paid') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Actions Column -->
                                    <div class="lg:col-span-1">
                                        <div class="flex items-center justify-end space-x-2">
                                            <div class="relative group">
                                                <button wire:click="view({{ $payroll->id }})" 
                                                        class="flex items-center justify-center w-8 h-8 text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-full transition-all duration-200 hover:scale-110">
                                                    <i class="fas fa-eye text-sm"></i>
                                                </button>
                                                <div class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                                                    {{ __('payroll.view_details') }}
                                                </div>
                                            </div>
                                            
                                            @if($payroll->status === 'draft')
                                                <div class="relative group">
                                                    <button wire:click="edit({{ $payroll->id }})" 
                                                            class="flex items-center justify-center w-8 h-8 text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-full transition-all duration-200 hover:scale-110">
                                                        <i class="fas fa-edit text-sm"></i>
                                                    </button>
                                                    <div class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                                                        {{ __('payroll.edit') }}
                                                    </div>
                                                </div>
                                                <div class="relative group">
                                                    <button wire:click="approve({{ $payroll->id }})" 
                                                            class="flex items-center justify-center w-8 h-8 text-green-600 bg-green-50 hover:bg-green-100 rounded-full transition-all duration-200 hover:scale-110">
                                                        <i class="fas fa-check text-sm"></i>
                                                    </button>
                                                    <div class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                                                        {{ __('payroll.approve') }}
                                                    </div>
                                                </div>
                                            @endif
                                            
                                            @if($payroll->status === 'approved')
                                                <div class="relative group">
                                                    <button wire:click="markAsPaid({{ $payroll->id }})" 
                                                            class="flex items-center justify-center w-8 h-8 text-green-600 bg-green-50 hover:bg-green-100 rounded-full transition-all duration-200 hover:scale-110">
                                                        <i class="fas fa-money-bill-wave text-sm"></i>
                                                    </button>
                                                    <div class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                                                        {{ __('payroll.mark_as_paid') }}
                                                    </div>
                                                </div>
                                            @endif
                                            
                                            <div class="relative group">
                                                <button wire:click="downloadPayslip({{ $payroll->id }})" 
                                                        class="flex items-center justify-center w-8 h-8 text-red-600 bg-red-50 hover:bg-red-100 rounded-full transition-all duration-200 hover:scale-110">
                                                    <i class="fas fa-file-pdf text-sm"></i>
                                                </button>
                                                <div class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                                                    {{ __('payroll.download_payslip') }}
                                                </div>
                                            </div>
                                            
                                            @php
                                                $isAdmin = auth()->user()->hasRole('admin') || auth()->user()->hasRole('super-admin');
                                                $canDelete = $isAdmin || in_array($payroll->status, ['draft', 'rejected']);
                                            @endphp
                                            
                                            @if($canDelete)
                                                <div class="relative group">
                                                    <button wire:click="delete({{ $payroll->id }})" 
                                                            class="flex items-center justify-center w-8 h-8 text-red-700 bg-red-100 hover:bg-red-200 rounded-full transition-all duration-200 hover:scale-110">
                                                        <i class="fas fa-trash text-sm"></i>
                                                    </button>
                                                    <div class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                                                        @if($isAdmin && !in_array($payroll->status, ['draft', 'rejected']))
                                                            {{ __('messages.admin_delete') }}
                                                        @else
                                                            {{ __('messages.delete') }}
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-16">
                                <div class="bg-gradient-to-br from-gray-100 to-gray-200 w-24 h-24 mx-auto mb-6 rounded-full flex items-center justify-center">
                                    <i class="fas fa-money-bill-wave text-4xl text-gray-400"></i>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 mb-3">
                                    Nenhum registo de folha de pagamento encontrado
                                </h3>
                                <div class="max-w-md mx-auto">
                                    @if($search || !empty($filters['period_id']) || !empty($filters['department_id']) || !empty($filters['status']))
                                        <p class="text-gray-600 mb-4">
                                            Nenhum registo corresponde aos critérios de pesquisa. Tente ajustar os filtros.
                                        </p>
                                        <button wire:click="resetFilters" 
                                                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-300">
                                            <i class="fas fa-filter mr-2"></i>
                                            Limpar filtros
                                        </button>
                                    @else
                                        <p class="text-gray-600 mb-4">
                                            Ainda não existem registos de folha de pagamento no sistema.
                                        </p>
                                        <button wire:click="openEmployeeSearch" 
                                                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white font-semibold rounded-lg hover:from-green-700 hover:to-green-800 transition-all duration-300">
                                            <i class="fas fa-plus mr-2"></i>
                                            Processar Primeiro Salário
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforelse
                    </div>
                    
                    {{-- Pagination --}}
                    @if($payrolls->hasPages())
                        <div class="mt-6 bg-white rounded-xl border border-gray-200 px-6 py-4">
                            {{ $payrolls->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Processamento de Payroll -->
    @include('livewire.hr.payroll-process-modal')
    
    <!-- Modal de Seleção de Período de Payroll -->
    @include('livewire.hr.payroll-period-selection-modal')
    
    <!-- Modal de Visualização Moderna do Payroll -->
    @if($showViewModal && $currentPayroll)
        <div class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl max-h-[95vh] flex flex-col">
                
                <!-- Header com Gradiente -->
                <div class="bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-800 px-4 sm:px-6 lg:px-8 py-4 sm:py-6 text-white rounded-t-2xl">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-3 sm:space-x-4 flex-1 min-w-0">
                            <div class="bg-white/20 backdrop-blur-sm rounded-xl p-2 sm:p-3 flex-shrink-0">
                                <i class="fas fa-file-invoice-dollar text-lg sm:text-2xl"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="text-lg sm:text-xl lg:text-2xl font-bold truncate">{{ __('messages.payroll_details') }}</h3>
                                <p class="text-blue-100 text-xs sm:text-sm mt-1 truncate">{{ $currentPayroll->employee->full_name }} • {{ $currentPayroll->payrollPeriod->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <button type="button" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg p-2 transition-all flex-shrink-0 ml-2" wire:click="closeViewModal">
                            <i class="fas fa-times text-lg sm:text-xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Conteúdo Principal -->
                <div class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto">
                    
                    <!-- Cards de Informações Principais -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                        
                        <!-- Card {{ __('messages.employee') }} -->
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-100 rounded-xl p-6 border border-blue-200">
                            <div class="flex items-center mb-4">
                                <div class="bg-blue-500 rounded-lg p-2 mr-3">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                                <h4 class="font-semibold text-gray-800">{{ __('messages.employee') }}</h4>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">{{ __('messages.name') }}:</span>
                                    <span class="text-sm font-medium text-gray-800">{{ $currentPayroll->employee->full_name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">{{ __('messages.id') }}:</span>
                                    <span class="text-sm font-medium text-gray-800">{{ $currentPayroll->employee->id }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">{{ __('messages.department') }}:</span>
                                    <span class="text-sm font-medium text-gray-800">{{ $currentPayroll->employee->department->name ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">{{ __('messages.position') }}:</span>
                                    <span class="text-sm font-medium text-gray-800">{{ $currentPayroll->employee->position->name ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Período -->
                        <div class="bg-gradient-to-br from-green-50 to-emerald-100 rounded-xl p-6 border border-green-200">
                            <div class="flex items-center mb-4">
                                <div class="bg-green-500 rounded-lg p-2 mr-3">
                                    <i class="fas fa-calendar-alt text-white"></i>
                                </div>
                                <h4 class="font-semibold text-gray-800">{{ __('messages.period') }}</h4>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">{{ __('messages.period') }}:</span>
                                    <span class="text-sm font-medium text-gray-800">{{ $currentPayroll->payrollPeriod->name ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">{{ __('messages.start_date') }}:</span>
                                    <span class="text-sm font-medium text-gray-800">{{ $currentPayroll->payrollPeriod?->start_date?->format('d/m/Y') ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">{{ __('messages.end_date') }}:</span>
                                    <span class="text-sm font-medium text-gray-800">{{ $currentPayroll->payrollPeriod?->end_date?->format('d/m/Y') ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">{{ __('messages.hours') }}:</span>
                                    <span class="text-sm font-medium text-gray-800">{{ number_format((float)$currentPayroll->attendance_hours, 1) }}h</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Status -->
                        <div class="bg-gradient-to-br from-purple-50 to-violet-100 rounded-xl p-6 border border-purple-200">
                            <div class="flex items-center mb-4">
                                <div class="bg-purple-500 rounded-lg p-2 mr-3">
                                    <i class="fas fa-info-circle text-white"></i>
                                </div>
                                <h4 class="font-semibold text-gray-800">{{ __('messages.status') }}</h4>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">{{ __('messages.status') }}:</span>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                        {{ $currentPayroll->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : 
                                        ($currentPayroll->status === 'approved' ? 'bg-blue-100 text-blue-800' : 
                                        'bg-green-100 text-green-800') }}">
                                        <i class="fas fa-circle text-xs mr-1"></i>
                                        {{ ucfirst($currentPayroll->status) }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">{{ __('messages.created_at') }}:</span>
                                    <span class="text-sm font-medium text-gray-800">{{ $currentPayroll->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">{{ __('messages.payment') }}:</span>
                                    <span class="text-sm font-medium text-gray-800">{{ $currentPayroll->payment_method ? ucfirst(str_replace('_', ' ', $currentPayroll->payment_method)) : 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">{{ __('messages.account') }}:</span>
                                    <span class="text-sm font-medium text-gray-800">{{ $currentPayroll->bank_account ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Breakdown Detalhado dos Componentes -->
                    @if($currentPayroll->payrollItems && $currentPayroll->payrollItems->count() > 0)
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-800 mb-6 flex items-center">
                            <i class="fas fa-list-ul text-blue-500 mr-2"></i>
                            {{ __('payroll.detailed_breakdown_components') }}
                        </h4>
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            
                            <!-- Rendimentos Detalhados -->
                            <div class="bg-green-50 rounded-xl p-6 border border-green-200">
                                <h5 class="font-semibold text-green-800 mb-4 flex items-center">
                                    <i class="fas fa-plus-circle text-green-600 mr-2"></i>
                                    {{ __('payroll.earnings') }}
                                </h5>
                                <div class="space-y-3">
                                    @php $totalEarnings = 0; @endphp
                                    @foreach($currentPayroll->payrollItems->whereIn('type', ['earning', 'allowance', 'bonus']) as $item)
                                        @php $totalEarnings += (float)$item->amount; @endphp
                                        <div class="flex justify-between items-center py-2 border-b border-green-200 last:border-b-0">
                                            <div>
                                                <span class="text-sm font-medium text-gray-800">{{ \App\Helpers\PayrollTranslationHelper::getTranslatedName($item->name) }}</span>
                                                @if($item->description)
                                                    <p class="text-xs text-gray-600 mt-1">{{ \App\Helpers\PayrollTranslationHelper::getTranslatedDescription($item->name, $item->description) }}</p>
                                                @endif
                                                <div class="flex items-center mt-1">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                        {{ $item->type === 'earning' ? 'bg-blue-100 text-blue-800' : 
                                                        ($item->type === 'allowance' ? 'bg-purple-100 text-purple-800' : 
                                                        'bg-orange-100 text-orange-800') }}">
                                                        {{ __('payroll.' . $item->type) }}
                                                    </span>
                                                    @if($item->is_taxable)
                                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                             {{ __('payroll.taxable') }}
                                                        </span>
                                                    @else
                                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                             {{ __('payroll.exempt') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <span class="text-sm font-bold text-green-700">{{ number_format((float)$item->amount, 2, ',', '.') }} {{ __('payroll.currency') }}</span>
                                        </div>
                                    @endforeach
                                    <div class="flex justify-between items-center pt-3 border-t-2 border-green-300">
                                        <span class="font-semibold text-green-800">{{ __('messages.total_earnings') }}:</span>
                                        <span class="text-lg font-bold text-green-700">{{ number_format($totalEarnings, 2, ',', '.') }} {{ __('payroll.currency') }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Deduções Detalhadas -->
                            <div class="bg-red-50 rounded-xl p-6 border border-red-200">
                                <h5 class="font-semibold text-red-800 mb-4 flex items-center">
                                    <i class="fas fa-minus-circle text-red-600 mr-2"></i>
                                    {{ __('payroll.deductions') }}
                                </h5>
                                <div class="space-y-3">
                                    @php $totalDeductions = 0; @endphp
                                    @foreach($currentPayroll->payrollItems->whereIn('type', ['deduction', 'tax']) as $item)
                                        @php $totalDeductions += abs((float)$item->amount); @endphp
                                        <div class="flex justify-between items-center py-2 border-b border-red-200 last:border-b-0">
                                            <div>
                                                <span class="text-sm font-medium text-gray-800">{{ \App\Helpers\PayrollTranslationHelper::getTranslatedName($item->name) }}</span>
                                                @if($item->description)
                                                    <p class="text-xs text-gray-600 mt-1">{{ \App\Helpers\PayrollTranslationHelper::getTranslatedDescription($item->name, $item->description) }}</p>
                                                @endif
                                                <div class="flex items-center mt-1">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                        {{ $item->type === 'tax' ? 'bg-red-100 text-red-800' : 'bg-orange-100 text-orange-800' }}">
                                                        {{ $item->type === 'tax' ? __('messages.tax') : __('messages.deduction') }}
                                                    </span>
                                                </div>
                                            </div>
                                            <span class="text-sm font-bold text-red-700">-{{ number_format(abs((float)$item->amount), 2, ',', '.') }} {{ __('payroll.currency') }}</span>
                                        </div>
                                    @endforeach
                                    <div class="flex justify-between items-center pt-3 border-t-2 border-red-300">
                                        <span class="font-semibold text-red-800">{{ __('messages.total_deductions') }}:</span>
                                        <span class="text-lg font-bold text-red-700">-{{ number_format($totalDeductions, 2, ',', '.') }} {{ __('payroll.currency') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Resumo Final -->
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-6 border border-gray-200 mb-6">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-calculator text-blue-500 mr-2"></i>
                            {{ __('payroll.final_summary') }}
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="text-center">
                                <div class="bg-green-100 rounded-lg p-4">
                                    <i class="fas fa-arrow-up text-green-600 text-2xl mb-2"></i>
                                    <p class="text-sm text-gray-600 mb-1">{{ __('payroll.gross_total') }}</p>
                                     <p class="text-xl font-bold text-green-700">{{ number_format((float)$currentPayroll->basic_salary + (float)$currentPayroll->allowances + (float)$currentPayroll->overtime + (float)$currentPayroll->bonuses, 2, ',', '.') }} {{ __('payroll.currency') }}</p>
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="bg-red-100 rounded-lg p-4">
                                    <i class="fas fa-arrow-down text-red-600 text-2xl mb-2"></i>
                                    <p class="text-sm text-gray-600 mb-1">{{ __('payroll.total_deductions') }}</p>
                                     <p class="text-xl font-bold text-red-700">{{ number_format((float)$currentPayroll->tax + (float)$currentPayroll->social_security + (float)$currentPayroll->deductions, 2, ',', '.') }} {{ __('payroll.currency') }}</p>
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="bg-blue-100 rounded-lg p-4">
                                    <i class="fas fa-wallet text-blue-600 text-2xl mb-2"></i>
                                    <p class="text-sm text-gray-600 mb-1">{{ __('payroll.net_salary') }}</p>
                                     <p class="text-2xl font-bold text-blue-700">{{ number_format((float)$currentPayroll->net_salary, 2, ',', '.') }} {{ __('payroll.currency') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Observações -->
                    @if($currentPayroll->remarks)
                    <div class="bg-yellow-50 rounded-xl p-6 border border-yellow-200">
                        <h4 class="font-semibold text-yellow-800 mb-3 flex items-center">
                            <i class="fas fa-sticky-note text-yellow-600 mr-2"></i>
                            {{ __('payroll.observations') }}
                        </h4>
                        <p class="text-sm text-gray-700 leading-relaxed">{{ $currentPayroll->remarks }}</p>
                    </div>
                    @endif
                </div>

                <!-- Footer com Botões -->
                <div class="bg-gray-50 px-4 sm:px-8 py-4 sm:py-6 border-t border-gray-200 rounded-b-2xl flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="text-xs sm:text-sm text-gray-500 text-center sm:text-left">
                        <i class="fas fa-clock mr-1"></i>
                        {{ __('payroll.generated_on') }} {{ $currentPayroll->created_at->format('d/m/Y às H:i') }}
                    </div>
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3 w-full sm:w-auto">
                        <button type="button"
                            class="w-full sm:w-auto px-4 sm:px-6 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all"
                            wire:click="closeViewModal">
                            <i class="fas fa-times mr-2"></i>
                            {{ __('messages.close') }}
                        </button>
                        <button type="button"
                            wire:click="downloadPayslip({{ $currentPayroll->id }})"
                            class="w-full sm:w-auto px-4 sm:px-6 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all">
                            <i class="fas fa-file-pdf mr-2"></i>
                            {{ __('payroll.download_payslip') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de Confirmação de Pagamento -->
    @if($showPayModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">
                        <i class="fas fa-money-bill-wave mr-2"></i>
                        Confirmar Pagamento
                    </h3>
                    <button type="button" class="text-gray-500 hover:text-gray-700 text-xl" wire:click="$set('showPayModal', false)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <p class="mb-4 text-gray-600">Você está confirmando que este pagamento foi realizado. Esta ação atualizará o status da folha de pagamento para "Pago".</p>

                <form wire:submit.prevent="pay">
                    <div class="mb-4">
                        <label for="payment_date" class="block text-sm font-medium text-gray-700">{{ __('messages.payment_date') }}</label>
                        <input type="date" id="payment_date"
                            class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('payment_date') border-red-300 text-red-900 @enderror"
                            wire:model.live="payment_date">
                        @error('payment_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button"
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            wire:click="$set('showPayModal', false)">
                            {{ __('messages.cancel') }}
                        </button>
                        <button type="submit"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            {{ __('messages.confirm_payment') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Adicionar botão de download na tabela de listagem -->
    <script>
        document.addEventListener('livewire:initialized', function () {
            Livewire.hook('element.initialized', ({ component, el }) => {
                if (component.name === 'hr.payroll') {
                    // Adicionar botões de download após renderizar
                    document.querySelectorAll('[data-payroll-id]').forEach(function(button) {
                        if (!button.dataset.initialized) {
                            button.dataset.initialized = true;
                            button.addEventListener('click', function(e) {
                                e.preventDefault();
                                Livewire.dispatch('downloadPayslip', { payrollId: button.dataset.payrollId });
                            });
                        }
                    });
                }
            });
        });
    </script>

    <!-- Flash Message -->
    @if (session()->has('message'))
        <div x-data="{ show: true }"
             x-init="setTimeout(() => show = false, 3000)"
             x-show="show"
             class="fixed bottom-4 right-4 bg-green-500 text-white py-2 px-4 rounded-md shadow-md">
            {{ session('message') }}
        </div>
    @endif


    {{-- Include Employee Search Modal --}}
    @include('livewire.hr.payroll-employee-search-modal')
    
    {{-- Include Delete Confirmation Modal --}}
    @include('livewire.hr.payroll-delete-modal')
</div>
