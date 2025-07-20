{{-- Advanced Payroll Processing Modal --}}
@if($showEmployeeSearch || $showProcessModal)
<div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50" x-data="{ loading: false }">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-7xl mx-4 max-h-[95vh] overflow-hidden">
        
        @if($showEmployeeSearch)
            {{-- Employee Search Modal --}}
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-6 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="bg-white/20 p-2 rounded-lg">
                            <i class="fas fa-search text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold">{{ __('messages.search_employee_for_payroll') }}</h2>
                            <p class="text-blue-100">{{ __('messages.search_employee_payroll_description') }}</p>
                        </div>
                    </div>
                    <button wire:click="showEmployeeSearch = false" class="text-white/80 hover:text-white p-2">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <div class="p-8">
                {{-- Search Input --}}
                <div class="mb-8">
                    <div class="relative">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="employeeSearch"
                            placeholder="{{ __('messages.search_employee_by_name_id_email') }}"
                            class="w-full pl-12 pr-4 py-4 text-lg border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                            autofocus
                        >
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400 text-lg"></i>
                        </div>
                    </div>
                </div>

                {{-- Period Selection --}}
                <div class="grid grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-calendar text-blue-500 mr-1"></i>
                            {{ __('messages.month') }}
                        </label>
                        <select 
                            wire:model.live="selected_month" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">
                                    {{ DateTime::createFromFormat('!m', $i)->format('F Y') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt text-purple-500 mr-1"></i>
                            {{ __('messages.year') }}
                        </label>
                        <select 
                            wire:model.live="selected_year" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            @for($year = now()->year - 2; $year <= now()->year + 1; $year++)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                {{-- Search Results --}}
                @if(count($searchResults) > 0)
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-users text-blue-500 mr-2"></i>
                            {{ __('messages.search_results') }} ({{ count($searchResults) }})
                        </h3>
                        <div class="grid gap-4">
                            @foreach($searchResults as $employee)
                                <div 
                                    wire:click="selectEmployee({{ $employee['id'] }})"
                                    class="bg-gradient-to-r from-gray-50 to-gray-100 hover:from-blue-50 hover:to-blue-100 p-6 rounded-xl border border-gray-200 hover:border-blue-300 cursor-pointer transition-all duration-300 hover:scale-[1.02] hover:shadow-md"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white w-12 h-12 rounded-full flex items-center justify-center font-semibold text-lg">
                                                {{ substr($employee['full_name'], 0, 1) }}
                                            </div>
                                            <div>
                                                <h4 class="text-lg font-semibold text-gray-800">{{ $employee['full_name'] }}</h4>
                                                <div class="flex items-center space-x-4 text-sm text-gray-600">
                                                    <span class="flex items-center">
                                                        <i class="fas fa-id-badge text-blue-500 mr-1"></i>
                                                        BI: {{ $employee['id_card'] }}
                                                    </span>
                                                    <span class="flex items-center">
                                                        <i class="fas fa-building text-green-500 mr-1"></i>
                                                        {{ $employee['department_name'] ?? 'N/A' }}
                                                    </span>
                                                    <span class="flex items-center">
                                                        <i class="fas fa-envelope text-blue-500 mr-1"></i>
                                                        {{ $employee['email'] ?? 'N/A' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-blue-500 hover:text-blue-600">
                                            <i class="fas fa-chevron-right text-lg"></i>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @elseif(strlen($employeeSearch) >= 2)
                    <div class="text-center py-12">
                        <div class="text-gray-400 mb-4">
                            <i class="fas fa-search text-4xl"></i>
                        </div>
                        <p class="text-gray-600 text-lg">{{ __('messages.no_employees_found') }}</p>
                        <p class="text-gray-500 text-sm">{{ __('messages.try_different_search_terms') }}</p>
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="text-blue-400 mb-4">
                            <i class="fas fa-search text-4xl"></i>
                        </div>
                        <p class="text-gray-600 text-lg">{{ __('messages.start_typing_to_search') }}</p>
                        <p class="text-gray-500 text-sm">{{ __('messages.minimum_2_characters') }}</p>
                    </div>
                @endif
            </div>
        @endif

        @if($showProcessModal && $selectedEmployee)
            {{-- Advanced Payroll Processing Modal --}}
            <div class="h-full flex flex-col">
                {{-- Header --}}
                <div class="bg-gradient-to-r from-green-600 via-emerald-600 to-teal-700 p-6 text-white flex-shrink-0">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="bg-white/20 p-3 rounded-lg">
                                <i class="fas fa-calculator text-2xl"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold">{{ __('messages.process_payroll') }}</h2>
                                <p class="text-green-100">{{ $selectedEmployee->full_name }} - {{ $selected_month }}/{{ $selected_year }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <button 
                                wire:click="calculatePayrollComponents"
                                class="bg-white/10 hover:bg-white/20 px-4 py-2 rounded-lg transition-colors flex items-center space-x-2"
                            >
                                <i class="fas fa-sync-alt"></i>
                                <span>{{ __('messages.recalculate') }}</span>
                            </button>
                            <button 
                                wire:click="showProcessModal = false" 
                                class="text-white/80 hover:text-white p-2"
                            >
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Content --}}
                <div class="flex-1 overflow-hidden">
                    <div class="h-full flex flex-col lg:flex-row">
                        {{-- Left Panel - Employee Info & Components --}}
                        <div class="w-full lg:w-1/2 border-b lg:border-b-0 lg:border-r border-gray-200 overflow-y-auto max-h-96 lg:max-h-none">
                            <div class="p-6 space-y-6">
                                {{-- Employee Summary Card --}}
                                <div class="bg-gradient-to-br from-blue-50 to-indigo-100 p-6 rounded-2xl border border-blue-200">
                                    <h3 class="text-lg font-bold text-blue-800 mb-4 flex items-center">
                                        <i class="fas fa-user-circle mr-2"></i>
                                        {{ __('messages.employee_information') }}
                                    </h3>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-blue-600 font-medium">{{ __('messages.employee_id') }}</p>
                                            <p class="text-blue-800 font-semibold">{{ $selectedEmployee->employee_id }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-blue-600 font-medium">{{ __('messages.department') }}</p>
                                            <p class="text-blue-800 font-semibold">{{ $selectedEmployee->department->name ?? 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-blue-600 font-medium">{{ __('messages.basic_salary') }}</p>
                                            <p class="text-blue-800 font-semibold">{{ number_format($basic_salary, 2) }} AOA</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-blue-600 font-medium">{{ __('messages.hourly_rate') }}</p>
                                            <p class="text-blue-800 font-semibold">{{ number_format($hourly_rate, 2) }} AOA/h</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Attendance Summary Card --}}
                                <div class="bg-gradient-to-br from-green-50 to-emerald-100 p-6 rounded-2xl border border-green-200">
                                    <h3 class="text-lg font-bold text-green-800 mb-4 flex items-center">
                                        <i class="fas fa-clock mr-2"></i>
                                        {{ __('messages.attendance_summary') }}
                                    </h3>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-green-600 font-medium">{{ __('messages.total_working_days') }}</p>
                                            <p class="text-green-800 font-semibold">{{ $total_working_days ?? 0 }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-green-600 font-medium">{{ __('messages.present_days') }}</p>
                                            <p class="text-green-800 font-semibold">{{ $present_days ?? 0 }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-green-600 font-medium">{{ __('messages.absent_days') }}</p>
                                            <p class="text-green-800 font-semibold">{{ $absent_days ?? 0 }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-green-600 font-medium">{{ __('messages.late_arrivals') }}</p>
                                            <p class="text-green-800 font-semibold">{{ $late_arrivals ?? 0 }}</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Overtime Records Card --}}
                                <div class="bg-gradient-to-br from-purple-50 to-violet-100 p-6 rounded-2xl border border-purple-200">
                                    <h3 class="text-lg font-bold text-purple-800 mb-4 flex items-center">
                                        <i class="fas fa-clock mr-2"></i>
                                        {{ __('messages.overtime_records') }}
                                    </h3>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-purple-600 font-medium">{{ __('messages.total_overtime_hours') }}</p>
                                            <p class="text-purple-800 font-semibold">{{ number_format($total_overtime_hours ?? 0, 2) }}h</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-purple-600 font-medium">{{ __('messages.overtime_amount') }}</p>
                                            <p class="text-purple-800 font-semibold">{{ number_format($total_overtime_amount ?? 0, 2) }} AOA</p>
                                        </div>
                                    </div>
                                    @if(isset($overtimeRecords) && count($overtimeRecords) > 0)
                                        <div class="mt-4">
                                            <p class="text-sm text-purple-600 font-medium mb-2">{{ __('messages.overtime_details') }}</p>
                                            <div class="max-h-32 overflow-y-auto space-y-1">
                                                @foreach($overtimeRecords as $overtime)
                                                    <div class="text-xs bg-purple-100 px-2 py-1 rounded flex justify-between">
                                                        <span>{{ $overtime['date'] ?? 'N/A' }}</span>
                                                        <span>{{ $overtime['hours'] ?? 0 }}h - {{ number_format($overtime['amount'] ?? 0, 2) }} AOA</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- Salary Advances Card --}}
                                <div class="bg-gradient-to-br from-orange-50 to-amber-100 p-6 rounded-2xl border border-orange-200">
                                    <h3 class="text-lg font-bold text-orange-800 mb-4 flex items-center">
                                        <i class="fas fa-hand-holding-usd mr-2"></i>
                                        {{ __('messages.salary_advances') }}
                                    </h3>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-orange-600 font-medium">{{ __('messages.total_advances') }}</p>
                                            <p class="text-orange-800 font-semibold">{{ number_format($total_salary_advances ?? 0, 2) }} AOA</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-orange-600 font-medium">{{ __('messages.deduction_amount') }}</p>
                                            <p class="text-orange-800 font-semibold">{{ number_format($advance_deduction ?? 0, 2) }} AOA</p>
                                        </div>
                                    </div>
                                    @if(isset($salaryAdvances) && count($salaryAdvances) > 0)
                                        <div class="mt-4">
                                            <p class="text-sm text-orange-600 font-medium mb-2">{{ __('messages.advance_details') }}</p>
                                            <div class="max-h-40 overflow-y-auto space-y-2">
                                                @foreach($salaryAdvances as $advance)
                                                    <div class="bg-orange-100 p-3 rounded-lg">
                                                        <div class="flex justify-between items-start mb-1">
                                                            <span class="text-xs font-medium text-orange-700">{{ $advance['request_date'] ?? 'N/A' }}</span>
                                                            <span class="text-xs font-bold text-orange-800">{{ number_format($advance['amount'] ?? 0, 2) }} AOA</span>
                                                        </div>
                                                        <div class="flex justify-between text-xs text-orange-600">
                                                            <span>{{ $advance['remaining_installments'] ?? 0 }}/{{ $advance['installments'] ?? 0 }} parcelas</span>
                                                            <span class="font-medium">{{ number_format($advance['installment_amount'] ?? 0, 2) }} AOA/mês</span>
                                                        </div>
                                                        @if(isset($advance['reason']))
                                                            <p class="text-xs text-orange-500 mt-1 truncate">{{ $advance['reason'] }}</p>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- Salary Discounts Card --}}
                                <div class="bg-gradient-to-br from-red-50 to-rose-100 p-6 rounded-2xl border border-red-200">
                                    <h3 class="text-lg font-bold text-red-800 mb-4 flex items-center">
                                        <i class="fas fa-minus-circle mr-2"></i>
                                        {{ __('messages.salary_discounts') }}
                                    </h3>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-red-600 font-medium">{{ __('messages.total_discounts') }}</p>
                                            <p class="text-red-800 font-semibold">{{ number_format($total_salary_discounts ?? 0, 2) }} AOA</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-red-600 font-medium">{{ __('messages.active_discounts') }}</p>
                                            <p class="text-red-800 font-semibold">{{ isset($salaryDiscounts) ? count($salaryDiscounts) : 0 }}</p>
                                        </div>
                                    </div>
                                    @if(isset($salaryDiscounts) && count($salaryDiscounts) > 0)
                                        <div class="mt-4">
                                            <p class="text-sm text-red-600 font-medium mb-2">{{ __('messages.discount_details') }}</p>
                                            <div class="max-h-40 overflow-y-auto space-y-2">
                                                @foreach($salaryDiscounts as $discount)
                                                    <div class="bg-red-100 p-3 rounded-lg">
                                                        <div class="flex justify-between items-start mb-1">
                                                            <span class="text-xs font-medium text-red-700">{{ $discount['request_date'] ?? 'N/A' }}</span>
                                                            <span class="text-xs font-bold text-red-800">{{ number_format($discount['amount'] ?? 0, 2) }} AOA</span>
                                                        </div>
                                                        <div class="flex justify-between text-xs text-red-600">
                                                            <span>{{ $discount['remaining_installments'] ?? 0 }}/{{ $discount['installments'] ?? 0 }} parcelas</span>
                                                            <span class="font-medium">{{ number_format($discount['installment_amount'] ?? 0, 2) }} AOA/mês</span>
                                                        </div>
                                                        <div class="flex justify-between text-xs text-red-500 mt-1">
                                                            <span class="capitalize">{{ $discount['discount_type'] ?? 'outros' }}</span>
                                                            @if(isset($discount['reason']))
                                                                <span class="truncate ml-2">{{ $discount['reason'] }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- Benefits & Allowances Card --}}
                                <div class="bg-gradient-to-br from-teal-50 to-cyan-100 p-6 rounded-2xl border border-teal-200">
                                    <h3 class="text-lg font-bold text-teal-800 mb-4 flex items-center">
                                        <i class="fas fa-gift mr-2"></i>
                                        {{ __('messages.benefits_allowances') }}
                                    </h3>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-teal-600 font-medium">{{ __('messages.food_benefit') }}</p>
                                            <p class="text-teal-800 font-semibold">{{ number_format($selectedEmployee->food_benefit ?? 0, 2) }} AOA</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-teal-600 font-medium">{{ __('messages.transport_benefit') }}</p>
                                            <p class="text-teal-800 font-semibold">{{ number_format($selectedEmployee->transport_benefit ?? 0, 2) }} AOA</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-teal-600 font-medium">{{ __('messages.bonus_amount') }}</p>
                                            <p class="text-teal-800 font-semibold">{{ number_format($bonus_amount ?? 0, 2) }} AOA</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-teal-600 font-medium">{{ __('messages.total_benefits') }}</p>
                                            <p class="text-teal-800 font-semibold">{{ number_format(($selectedEmployee->food_benefit ?? 0) + ($selectedEmployee->transport_benefit ?? 0) + ($bonus_amount ?? 0), 2) }} AOA</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Additional Input for Bonus --}}
                                <div class="bg-gradient-to-br from-indigo-50 to-blue-100 p-6 rounded-2xl border border-indigo-200">
                                    <h3 class="text-lg font-bold text-indigo-800 mb-4 flex items-center">
                                        <i class="fas fa-plus-circle mr-2"></i>
                                        {{ __('messages.additional_payments') }}
                                    </h3>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-indigo-700 mb-2">{{ __('messages.bonus_amount') }}</label>
                                            <input 
                                                type="number" 
                                                step="0.01" 
                                                wire:model.live="bonus_amount"
                                                class="w-full px-3 py-2 border border-indigo-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                                placeholder="0.00"
                                            >
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <input 
                                                type="checkbox" 
                                                wire:model.live="holiday_allowance"
                                                id="holiday_allowance"
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                            >
                                            <label for="holiday_allowance" class="text-sm font-medium text-indigo-700">
                                                {{ __('messages.holiday_allowance') }}
                                            </label>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <input 
                                                type="checkbox" 
                                                wire:model.live="christmas_bonus"
                                                id="christmas_bonus"
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                            >
                                            <label for="christmas_bonus" class="text-sm font-medium text-indigo-700">
                                                {{ __('messages.christmas_bonus') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Right Panel - Calculations & Summary --}}
                        <div class="w-full lg:w-1/2 overflow-y-auto bg-gray-50">
                            <div class="p-6">
                                {{-- Payroll Summary --}}
                                <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                                    <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                                        <i class="fas fa-chart-pie text-green-500 mr-2"></i>
                                        {{ __('messages.payroll_summary') }}
                                    </h3>
                                    
                                    <div class="space-y-4">
                                        <div class="flex justify-between items-center p-4 bg-green-50 rounded-xl">
                                            <span class="font-semibold text-green-700">{{ __('messages.gross_salary') }}</span>
                                            <span class="text-xl font-bold text-green-800">{{ number_format($gross_salary, 2) }} AOA</span>
                                        </div>
                                        <div class="flex justify-between items-center p-4 bg-red-50 rounded-xl">
                                            <span class="font-semibold text-red-700">{{ __('messages.total_deductions') }}</span>
                                            <span class="text-xl font-bold text-red-800">{{ number_format($total_deductions, 2) }} AOA</span>
                                        </div>
                                        <div class="flex justify-between items-center p-4 bg-blue-50 rounded-xl border-2 border-blue-200">
                                            <span class="font-bold text-blue-700 text-lg">{{ __('messages.net_salary') }}</span>
                                            <span class="text-2xl font-bold text-blue-800">{{ number_format($net_salary, 2) }} AOA</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Action Buttons --}}
                                <div class="mt-6 space-y-3">
                                    <button 
                                        wire:click="save"
                                        class="w-full bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold py-4 px-6 rounded-xl transition-all duration-300 hover:scale-105 hover:shadow-lg flex items-center justify-center space-x-2"
                                    >
                                        <i class="fas fa-save text-lg"></i>
                                        <span>{{ __('messages.save_payroll') }}</span>
                                    </button>
                                    
                                    <button 
                                        wire:click="showProcessModal = false"
                                        class="w-full bg-gray-500 hover:bg-gray-600 text-white font-medium py-3 px-6 rounded-xl transition-colors flex items-center justify-center space-x-2"
                                    >
                                        <i class="fas fa-times"></i>
                                        <span>{{ __('messages.cancel') }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endif
