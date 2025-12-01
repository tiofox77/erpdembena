{{-- Include Employee Search Modal --}}
@include('livewire.hr.payroll.Modals._SearchEmployeeModal')

{{-- Payroll Processing Modal --}}
<style>
    /* Desabilitar overlay escuro padrão do Livewire nesta modal */
    [wire\:loading\.delay\.none\.grid] {
        opacity: 0 !important;
        pointer-events: none !important;
    }
</style>

{{-- Payroll Processing Modal --}}
@if($selectedEmployee)
<div x-data="{ open: @entangle('showProcessModal') }" 
                 x-show="open" 
                 x-cloak 
                 class="fixed inset-0 z-50 overflow-hidden" 
                 role="dialog" 
                 aria-modal="true" 
                 aria-labelledby="modal-title"
                 x-transition:enter="transition ease-out duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0">
        
                {{-- Background Overlay --}}
                <div class="absolute inset-0 bg-gradient-to-br from-gray-900/80 via-blue-900/70 to-gray-900/90 backdrop-blur-sm"></div>

                {{-- Modal Container --}}
                <div class="relative flex items-start justify-center min-h-screen p-2 sm:p-4 lg:p-6">
                    <div class="w-full max-w-7xl bg-white rounded-2xl shadow-2xl transform transition-all duration-300 ease-in-out my-2 sm:my-4 lg:my-8 flex flex-col h-[90vh] max-h-screen" 
                         x-transition:enter="transition ease-out duration-300" 
                         x-transition:enter-start="transform opacity-0 scale-95" 
                         x-transition:enter-end="transform opacity-100 scale-100" 
                         x-transition:leave="transition ease-in duration-200" 
                         x-transition:leave-start="transform opacity-100 scale-100" 
                         x-transition:leave-end="transform opacity-0 scale-95">
                        
                        {{-- Header --}}
                        <div class="bg-gradient-to-r from-green-600 via-emerald-600 to-teal-700 p-4 lg:p-6 text-white flex-shrink-0 rounded-t-2xl">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                <div class="flex items-center space-x-3 lg:space-x-4">
                                    <div class="bg-white/20 p-2 lg:p-3 rounded-lg">
                                        <i class="fas fa-calculator text-lg lg:text-2xl"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-lg lg:text-2xl font-bold">{{ __('messages.process_payroll') }}</h2>
                                        <p class="text-green-100 text-sm lg:text-base">{{ $selectedEmployee->full_name }} - {{ $selected_month }}/{{ $selected_year }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2 lg:space-x-3">
                                    <button 
                                        wire:click="goBackToEmployeeSelection"
                                        class="bg-white/10 hover:bg-white/20 px-3 py-2 lg:px-4 lg:py-2 rounded-lg transition-colors flex items-center space-x-2 text-sm lg:text-base"
                                        title="{{ __('messages.back_to_employee_selection') }}"
                                    >
                                        <i class="fas fa-arrow-left"></i>
                                        <span class="hidden sm:inline">{{ __('messages.back') }}</span>
                                    </button>
                                    <button 
                                        wire:click="calculatePayrollComponents"
                                        class="bg-white/10 hover:bg-white/20 px-3 py-2 lg:px-4 lg:py-2 rounded-lg transition-colors flex items-center space-x-2 text-sm lg:text-base"
                                    >
                                        <i class="fas fa-sync-alt"></i>
                                        <span class="hidden sm:inline">{{ __('messages.recalculate') }}</span>
                                    </button>
                                    <button 
                                        wire:click="closeProcessModal" 
                                        class="text-white/80 hover:text-white p-2"
                                    >
                                        <i class="fas fa-times text-lg lg:text-xl"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                {{-- Content --}}
                <div class="flex-1 overflow-hidden min-h-0">
                    <div class="h-full flex flex-col xl:flex-row">
                        {{-- Left Panel - Employee Info & Components --}}
                        <div class="w-full xl:w-1/2 border-b xl:border-b-0 xl:border-r border-gray-200 overflow-y-auto flex-1">
                            <div class="p-4 lg:p-6 space-y-4 lg:space-y-6">
                                {{-- Employee Summary Card --}}
                                <div class="bg-gradient-to-br from-blue-50 to-indigo-100 p-4 lg:p-6 rounded-2xl border border-blue-200">
                                    <h3 class="text-base lg:text-lg font-bold text-blue-800 mb-4 flex items-center">
                                        <i class="fas fa-user-circle mr-2"></i>
                                        {{ __('messages.employee_information') }}
                                    </h3>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 lg:gap-4">
                                        <div class="bg-white/60 p-3 rounded-lg">
                                            <p class="text-xs lg:text-sm text-blue-600 font-medium mb-1">{{ __('messages.employee_id') }}</p>
                                            <p class="text-sm lg:text-base text-blue-800 font-semibold">BI: {{ $selectedEmployee->id_card ?? 'N/A' }}</p>
                                        </div>
                                        <div class="bg-white/60 p-3 rounded-lg">
                                            <p class="text-xs lg:text-sm text-blue-600 font-medium mb-1">{{ __('messages.department') }}</p>
                                            <p class="text-sm lg:text-base text-blue-800 font-semibold">{{ $selectedEmployee->department->name ?? 'N/A' }}</p>
                                        </div>
                                        @if($this->canViewSalaryDetails())
                                        <div class="bg-white/60 p-3 rounded-lg">
                                            <p class="text-xs lg:text-sm text-blue-600 font-medium mb-1">{{ __('messages.basic_salary') }}</p>
                                            <p class="text-sm lg:text-base text-blue-800 font-semibold">{{ number_format($basic_salary ?? 0, 2) }} {{ __('messages.currency_aoa') }}</p>
                                        </div>
                                        <div class="bg-white/60 p-3 rounded-lg">
                                            <p class="text-xs lg:text-sm text-blue-600 font-medium mb-1">{{ __('messages.hourly_rate') }}</p>
                                            <p class="text-sm lg:text-base text-blue-800 font-semibold">{{ number_format($hourly_rate ?? 0, 2) }} {{ __('messages.currency_aoa') }}{{ __('messages.per_hour') }}</p>
                                        </div>
                                        @else
                                        <div class="bg-gray-100/60 p-3 rounded-lg col-span-2">
                                            <p class="text-xs lg:text-sm text-gray-500 font-medium mb-1">{{ __('payroll.salary_information') }}</p>
                                            <p class="text-sm lg:text-base text-gray-600 font-semibold">{{ __('payroll.salary_hidden') }}</p>
                                        </div>
                                        @endif
                                        <div class="bg-white/60 p-3 rounded-lg">
                                            <p class="text-xs lg:text-sm text-blue-600 font-medium mb-1">IRT ({{ __('messages.income_tax') }})</p>
                                            <p class="text-sm lg:text-base text-blue-800 font-semibold">{{ number_format($this->irtCalculationDetails['total_irt'] ?? 0, 2) }} {{ __('messages.currency_aoa') }}</p>
                                        </div>
                                        <div class="bg-white/60 p-3 rounded-lg">
                                            <p class="text-xs lg:text-sm text-blue-600 font-medium mb-1">INSS (3%)</p>
                                            <p class="text-sm lg:text-base text-blue-800 font-semibold">{{ number_format($inss_3_percent, 2) }} {{ __('messages.currency_aoa') }}</p>
                                        </div>
                                        <div class="bg-orange-50/60 p-3 rounded-lg border border-orange-200">
                                            <p class="text-xs lg:text-sm text-orange-600 font-medium mb-1">INSS (8%) - {{ __('payroll.illustrative_only') }}</p>
                                            <p class="text-sm lg:text-base text-orange-700 font-semibold">{{ number_format($inss_8_percent, 2) }} {{ __('messages.currency_aoa') }}</p>
                                            <p class="text-xs text-orange-500 mt-1">{{ __('payroll.calculated_from_main_salary') }}</p>
                                        </div>
                                        @if(isset($family_allowance) && $family_allowance > 0)
                                            <div class="bg-white/60 p-3 rounded-lg">
                                                <p class="text-xs lg:text-sm text-blue-600 font-medium mb-1">{{ __('messages.family_allowance') }}</p>
                                                <p class="text-sm lg:text-base text-blue-800 font-semibold">{{ number_format($family_allowance, 2) }} {{ __('messages.currency_aoa') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Subsidies and Additional Payments --}}
                                <div class="bg-gradient-to-br from-teal-50 to-emerald-50 p-6 rounded-xl border border-teal-200 shadow-sm">
                                    <h3 class="text-lg font-bold text-teal-800 mb-6 flex items-center">
                                        <i class="fas fa-gift mr-3 text-teal-600"></i>
                                        Subsidies and Additional Payments
                                    </h3>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                        {{-- Christmas Subsidy --}}
                                        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                                            <div class="flex items-start space-x-3 mb-3">
                                                <div class="flex-shrink-0 mt-1">
                                                    <input 
                                                        type="checkbox" 
                                                        wire:model.live="christmas_subsidy"
                                                        id="christmas_subsidy"
                                                        class="h-5 w-5 text-teal-600 focus:ring-teal-500 border-gray-300 rounded cursor-pointer"
                                                    >
                                                </div>
                                                <div class="flex-1">
                                                    <label for="christmas_subsidy" class="text-base font-semibold text-gray-800 block mb-1 cursor-pointer">
                                                        Christmas Subsidy
                                                    </label>
                                                    <p class="text-sm text-teal-600 mb-3">Additional Christmas payment: 50% do salário base</p>
                                                    <div class="text-2xl font-bold text-teal-700" wire:loading.class="opacity-50" wire:target="christmas_subsidy">
                                                        {{ number_format($this->christmasSubsidyAmount, 2) }} AOA
                                                    </div>
                                                    <div class="mt-2" wire:loading wire:target="christmas_subsidy">
                                                        <span class="text-xs text-gray-500">
                                                            <i class="fas fa-spinner fa-spin mr-1"></i>
                                                            Recalculando...
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Vacation Subsidy --}}
                                        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                                            <div class="flex items-start space-x-3 mb-3">
                                                <div class="flex-shrink-0 mt-1">
                                                    <input 
                                                        type="checkbox" 
                                                        wire:model.live="vacation_subsidy"
                                                        id="vacation_subsidy"
                                                        class="h-5 w-5 text-teal-600 focus:ring-teal-500 border-gray-300 rounded cursor-pointer"
                                                    >
                                                </div>
                                                <div class="flex-1">
                                                    <label for="vacation_subsidy" class="text-base font-semibold text-gray-800 block mb-1 cursor-pointer">
                                                        Vacation Subsidy
                                                    </label>
                                                    <p class="text-sm text-teal-600 mb-3">Additional vacation payment: 50% do salário base</p>
                                                    <div class="text-2xl font-bold text-teal-700" wire:loading.class="opacity-50" wire:target="vacation_subsidy">
                                                        {{ number_format($this->vacationSubsidyAmount, 2) }} AOA
                                                    </div>
                                                    <div class="mt-2" wire:loading wire:target="vacation_subsidy">
                                                        <span class="text-xs text-gray-500">
                                                            <i class="fas fa-spinner fa-spin mr-1"></i>
                                                            Recalculando...
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Additional Bonus --}}
                                    <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                                        <div class="flex items-center space-x-3 mb-4">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-teal-100 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-plus-circle text-teal-600 text-sm"></i>
                                                </div>
                                            </div>
                                            <div class="flex-1">
                                                <label class="text-base font-semibold text-gray-800 block">Additional Bonus</label>
                                            </div>
                                        </div>
                                        <div class="relative">
                                            <input 
                                                type="number" 
                                                step="0.01"
                                                min="0"
                                                wire:model.live="additional_bonus_amount"
                                                class="w-full px-4 py-3 text-lg border border-gray-200 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-gray-50"
                                                placeholder="AOA 0"
                                                value="{{ $additional_bonus_amount ?? 0 }}"
                                                x-on:blur="if($event.target.value === '') { $wire.set('additional_bonus_amount', 0) }"
                                            >
                                        </div>
                                    </div>
                                </div>

                                {{-- Attendance Summary Card --}}
                                <div class="bg-gradient-to-br from-green-50 to-emerald-100 p-6 rounded-2xl border border-green-200">
                                    <h3 class="text-lg font-bold text-green-800 mb-4 flex items-center">
                                        <i class="fas fa-clock mr-2"></i>
                                        {{ __('messages.attendance_summary') }} - {{ $selected_month }}/{{ $selected_year }}
                                    </h3>
                                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                                        <div class="bg-white/60 p-3 rounded-lg text-center">
                                            <p class="text-xs text-green-600 font-medium mb-1">{{ __('messages.total_working_days') }}</p>
                                            <p class="text-lg text-green-800 font-bold">{{ $total_working_days ?? 0 }}</p>
                                        </div>
                                        <div class="bg-white/60 p-3 rounded-lg text-center">
                                            <p class="text-xs text-green-600 font-medium mb-1">{{ __('messages.present_days') }}</p>
                                            <p class="text-lg text-green-800 font-bold">{{ $present_days ?? 0 }}</p>
                                        </div>
                                        <div class="bg-white/60 p-3 rounded-lg text-center">
                                            <p class="text-xs text-green-600 font-medium mb-1">{{ __('messages.absent_days') }}</p>
                                            <p class="text-lg text-orange-600 font-bold">{{ $absent_days ?? 0 }}</p>
                                        </div>
                                        <div class="bg-white/60 p-3 rounded-lg text-center">
                                            <p class="text-xs text-green-600 font-medium mb-1">{{ __('messages.late_arrivals') }}</p>
                                            <p class="text-lg text-yellow-600 font-bold">{{ $late_arrivals ?? 0 }}</p>
                                        </div>
                                    </div>
                                    
                                    {{-- Warning if no attendance records --}}
                                    @if(($present_days ?? 0) == 0)
                                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm text-yellow-700">
                                                    <strong>Atenção:</strong> Não foram encontrados registros de presença para este funcionário no período selecionado.
                                                </p>
                                                <p class="text-xs text-yellow-600 mt-1">
                                                    Verifique se:
                                                    <br>• As datas do período estão corretas
                                                    <br>• Os registros de ponto foram importados
                                                    <br>• O funcionário estava ativo no período
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    
                                    {{-- Total Hours Summary --}}
                                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                                        <div class="bg-white/60 p-3 rounded-lg text-center">
                                            <p class="text-xs text-green-600 font-medium mb-1">{{ __('messages.total_hours_worked') }}</p>
                                            <p class="text-lg text-green-800 font-bold">{{ number_format($total_attendance_hours ?? 0, 1) }}h</p>
                                        </div>
                                        @if($this->canViewSalaryDetails())
                                        <div class="bg-white/60 p-3 rounded-lg text-center">
                                            <p class="text-xs text-green-600 font-medium mb-1">{{ __('messages.regular_hours_pay') }}</p>
                                            <p class="text-lg text-green-800 font-bold">{{ number_format($regular_hours_pay ?? 0, 2) }} {{ __('messages.currency_aoa') }}</p>
                                        </div>
                                        <div class="bg-white/60 p-3 rounded-lg text-center">
                                            <p class="text-xs text-blue-600 font-medium mb-1">{{ __('messages.hourly_rate') }}</p>
                                            <p class="text-lg text-blue-800 font-bold">{{ number_format($hourly_rate ?? 0, 2) }} {{ __('messages.currency_aoa') }}/h</p>
                                        </div>
                                        <div class="bg-white/60 p-3 rounded-lg text-center">
                                            <p class="text-xs text-purple-600 font-medium mb-1">{{ __('messages.daily_rate') }}</p>
                                            <p class="text-lg text-purple-800 font-bold">{{ number_format($daily_rate ?? 0, 2) }} {{ __('messages.currency_aoa') }}/dia</p>
                                        </div>
                                        @else
                                        <div class="bg-gray-100/60 p-3 rounded-lg text-center col-span-3">
                                            <p class="text-xs text-gray-500 font-medium mb-1">{{ __('payroll.salary_rates') }}</p>
                                            <p class="text-lg text-gray-600 font-bold">{{ __('payroll.salary_hidden') }}</p>
                                        </div>
                                        @endif
                                    </div>
                                    
                                    {{-- Individual Attendance Records --}}
                                    @if(isset($attendanceData) && count($attendanceData) > 0)
                                        <div class="mt-4">
                                            <div class="flex items-center justify-between mb-2">
                                                <p class="text-sm text-green-700 font-medium">{{ __('messages.attendance_records') }} ({{ count($attendanceData) }})</p>
                                                <span class="text-xs text-green-600">{{ __('messages.click_to_expand') }}</span>
                                            </div>
                                            <div class="bg-white/40 rounded-lg max-h-32 overflow-y-auto">
                                                <div class="space-y-1 p-2">
                                                    @foreach($attendanceData as $attendance)
                                                        <div class="flex items-center justify-between text-xs bg-white/60 px-3 py-2 rounded border-l-4 
                                                                @if($attendance['status'] === 'present') border-green-500
                                                                @elseif($attendance['status'] === 'late') border-yellow-500
                                                                @elseif($attendance['status'] === 'half_day') border-blue-500
                                                                @else border-red-500
                                                                @endif">
                                                            <div>
                                                                <span class="font-medium text-gray-800">{{ $attendance['date'] ?? 'N/A' }}</span>
                                                                <span class="ml-2 px-2 py-1 rounded text-xs
                                                                        @if($attendance['status'] === 'present') bg-green-100 text-green-800
                                                                        @elseif($attendance['status'] === 'late') bg-yellow-100 text-yellow-800
                                                                        @elseif($attendance['status'] === 'half_day') bg-blue-100 text-blue-800
                                                                        @else bg-red-100 text-red-800
                                                                        @endif">
                                                                    {{ ucfirst($attendance['status']) }}
                                                                </span>
                                                                <span class="text-xs text-gray-500">({{ __('payroll.taxable') }})</span>
                                                            </div>
                                                            <div class="text-gray-600">
                                                                @if($attendance['time_in'] && $attendance['time_out'])
                                                                    {{ \Carbon\Carbon::parse($attendance['time_in'])->format('H:i') }} - 
                                                                    {{ \Carbon\Carbon::parse($attendance['time_out'])->format('H:i') }}
                                                                @else
                                                                    <span class="text-yellow-600">{{ __('messages.no_times_recorded') }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                                            <i class="fas fa-exclamation-triangle text-yellow-500 text-2xl mb-2"></i>
                                            <p class="text-yellow-700 font-medium">{{ __('messages.no_attendance_records_found') }}</p>
                                            <p class="text-yellow-600 text-sm mt-1">{{ __('messages.period') }}: {{ $selected_month }}/{{ $selected_year }}</p>
                                        </div>
                                    @endif
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
                                            <p class="text-purple-800 font-semibold">{{ number_format($total_overtime_amount ?? 0, 2) }} {{ __('messages.currency_aoa') }}</p>
                                        </div>
                                    </div>
                                    @if(isset($overtimeRecords) && count($overtimeRecords) > 0)
                                        <div class="mt-4">
                                            <p class="text-sm text-purple-600 font-medium mb-2">{{ __('messages.overtime_details') }}</p>
                                            <div class="max-h-32 overflow-y-auto space-y-1">
                                                @foreach($overtimeRecords as $overtime)
                                                    <div class="text-xs bg-purple-100 px-2 py-1 rounded flex justify-between">
                                                        <span>{{ $overtime['date'] ?? 'N/A' }}</span>
                                                        <span>{{ $overtime['hours'] ?? 0 }}h - {{ number_format($overtime['amount'] ?? 0, 2) }} {{ __('messages.currency_aoa') }}</span>
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
                                            <p class="text-orange-800 font-semibold">{{ number_format($total_salary_advances ?? 0, 2) }} {{ __('messages.currency_aoa') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-orange-600 font-medium">{{ __('messages.deduction_amount') }}</p>
                                            <p class="text-orange-800 font-semibold">{{ number_format($advance_deduction ?? 0, 2) }} {{ __('messages.currency_aoa') }}</p>
                                        </div>
                                    </div>
                                    @if(isset($salaryAdvances) && count($salaryAdvances) > 0)
                                        <div class="mt-4">
                                            <p class="text-sm text-orange-600 font-medium mb-2">{{ __('messages.advance_details') }}</p>
                                            <div class="max-h-40 overflow-y-auto space-y-2">
                                                @foreach($salaryAdvances as $advance)
                                                    <div class="bg-orange-100 p-3 rounded-lg">
                                                        <div class="flex justify-between items-start mb-1">
                                                            <span class="text-xs">({{ __('payroll.up_to_30k_non_taxable') }})</span><span class="text-xs font-medium text-orange-700">{{ $advance['request_date'] ?? 'N/A' }}</span>
                                                            <span class="text-xs font-bold text-orange-800">{{ number_format($advance['amount'] ?? 0, 2) }} {{ __('messages.currency_aoa') }}</span>
                                                        </div>
                                                        <div class="flex justify-between text-xs text-orange-600">
                                                            <span>{{ $advance['remaining_installments'] ?? 0 }}/{{ $advance['installments'] ?? 0 }} parcelas</span>
                                                            <span class="font-medium">{{ number_format($advance['installment_amount'] ?? 0, 2) }} {{ __('messages.currency_aoa') }}{{ __('messages.per_month') }}</span>
                                                        </div>
                                                        @if(isset($advance['reason']))
                                                            <span class="text-xs text-gray-500">({{ __('payroll.taxable_excess') }})</span><p class="text-xs text-orange-500 mt-1 truncate">{{ $advance['reason'] }}</p>
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
                                            <p class="text-red-800 font-semibold">{{ number_format($total_salary_discounts ?? 0, 2) }} {{ __('messages.currency_aoa') }}</p>
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
                                                            <span class="text-xs font-bold text-red-800">{{ number_format($discount['amount'] ?? 0, 2) }} {{ __('messages.currency_aoa') }}</span>
                                                        </div>
                                                        <div class="flex justify-between text-xs text-red-600">
                                                            <span>{{ $discount['remaining_installments'] ?? 0 }}/{{ $discount['installments'] ?? 0 }} parcelas</span>
                                                            <span class="font-medium">{{ number_format($discount['installment_amount'] ?? 0, 2) }} {{ __('messages.currency_aoa') }}{{ __('messages.per_month') }}</span>
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
                                            <p class="text-sm text-teal-600 font-medium">{{ __('messages.food_benefit') }} 
                                                <span class="text-xs bg-gray-200 text-gray-600 px-2 py-1 rounded-full ml-1">{{ __('payroll.non_taxable') }}</span>
                                            </p>
                                            <p class="text-teal-800 font-semibold">{{ number_format($selectedEmployee->food_benefit ?? 0, 2) }} {{ __('messages.currency_aoa') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-teal-600 font-medium">{{ __('messages.transport_benefit') }}
                                                <span class="text-xs bg-green-200 text-green-700 px-2 py-1 rounded-full ml-1">{{ __('payroll.taxable') }}</span>
                                            </p>
                                            <p class="text-teal-800 font-semibold">{{ number_format($transport_allowance ?? 0, 2) }} {{ __('messages.currency_aoa') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-teal-600 font-medium">{{ __('messages.family_allowance') }}
                                                <span class="text-xs bg-green-200 text-green-700 px-2 py-1 rounded-full ml-1">{{ __('payroll.taxable') }}</span>
                                            </p>
                                            <p class="text-teal-800 font-semibold">{{ number_format($family_allowance ?? 0, 2) }} AOA</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-teal-600 font-medium">{{ __('messages.taxable_benefits') }}</p>
                                            <p class="text-teal-800 font-semibold">{{ number_format(($transport_allowance ?? 0) + ($family_allowance ?? 0), 2) }} AOA</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Action Buttons --}}
                                <div class="mt-6 space-y-3">
                                    <button 
                                        wire:click="save"
                                        wire:loading.attr="disabled"
                                        wire:target="save"
                                        type="button"
                                        class="w-full bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold py-4 px-6 rounded-xl transition-all duration-300 hover:scale-105 hover:shadow-lg flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                        onclick="console.log('Botão Salvar clicado')"
                                    >
                                        <span wire:loading.remove wire:target="save">
                                            <i class="fas fa-save text-lg"></i>
                                            <span class="ml-2">{{ __('messages.save_payroll') }}</span>
                                        </span>
                                        <span wire:loading wire:target="save">
                                            <i class="fas fa-spinner fa-spin text-lg"></i>
                                            <span class="ml-2">Salvando...</span>
                                        </span>
                                    </button>
                                    
                                    <button 
                                        wire:click="closeProcessModal"
                                        type="button"
                                        class="w-full bg-gray-500 hover:bg-gray-600 text-white font-medium py-3 px-6 rounded-xl transition-colors flex items-center justify-center space-x-2"
                                    >
                                        <i class="fas fa-times"></i>
                                        <span>{{ __('messages.cancel') }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Right Panel - Payroll Summary --}}
                        <div class="w-full xl:w-1/2 bg-gradient-to-br from-gray-50 to-gray-100 overflow-y-auto flex-1">
                            <div class="p-4 lg:p-6">
                                
                                <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                                    <i class="fas fa-file-invoice-dollar text-blue-600 mr-3"></i>
                                    Folha de Salário - Resumo Detalhado
                                </h3>

                                {{-- GRID 3 COLUNAS --}}
                                <div class="grid grid-cols-1 gap-4">
                                    
                                    {{-- COLUNA 1 - ENTRADAS (AOA) --}}
                                    <section class="bg-white rounded-xl shadow-md border border-gray-200 p-5">
                                        <h4 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b-2 border-blue-500">
                                            <i class="fas fa-plus-circle text-blue-600 mr-2"></i>
                                            {{ __('messages.salary_gross') }}
                                        </h4>
                                        <div class="text-sm text-gray-600 mb-4">{{ __('messages.gross_salary_components') }}</div>

                                        <div class="space-y-2.5">
                                            {{-- Basic Salary --}}
                                            <div class="flex justify-between items-center p-2.5 bg-blue-50 rounded-lg">
                                                <span class="text-gray-700 font-medium">Basic Salary</span>
                                                <span class="text-blue-800 font-bold tabular-nums">{{ number_format($basic_salary, 2) }}</span>
                                            </div>

                                            {{-- Transport --}}
                                            @if($transport_allowance > 0)
                                            <div class="flex justify-between items-center p-2.5 bg-gray-50 rounded-lg">
                                                <span class="text-gray-700 font-medium">Transport</span>
                                                <span class="text-gray-800 font-bold tabular-nums">{{ number_format($transport_allowance, 2) }}</span>
                                            </div>
                                            @endif

                                            {{-- Food allowance --}}
                                            @if($selectedEmployee && $selectedEmployee->food_benefit > 0)
                                            <div class="flex justify-between items-center p-2.5 bg-gray-50 rounded-lg">
                                                <span class="text-gray-700 font-medium">Food allowance</span>
                                                <span class="text-gray-800 font-bold tabular-nums">{{ number_format($selectedEmployee->food_benefit, 2) }}</span>
                                            </div>
                                            @endif

                                            {{-- Total Over Time --}}
                                            @if($total_overtime_amount > 0)
                                            <div class="flex justify-between items-center p-2.5 bg-gray-50 rounded-lg">
                                                <span class="text-gray-700 font-medium">Total Over Time</span>
                                                <span class="text-gray-800 font-bold tabular-nums">{{ number_format($total_overtime_amount, 2) }}</span>
                                            </div>
                                            @endif

                                            {{-- Natal Allowance --}}
                                            @if($this->christmasSubsidyAmount > 0)
                                            <div class="flex justify-between items-center p-2.5 bg-gray-50 rounded-lg">
                                                <span class="text-gray-700 font-medium">Natal Allowance</span>
                                                <span class="text-gray-800 font-bold tabular-nums">{{ number_format($this->christmasSubsidyAmount, 2) }}</span>
                                            </div>
                                            @endif

                                            {{-- Leave Allowance --}}
                                            @if($this->vacationSubsidyAmount > 0)
                                            <div class="flex justify-between items-center p-2.5 bg-gray-50 rounded-lg">
                                                <span class="text-gray-700 font-medium">Leave Allowance</span>
                                                <span class="text-gray-800 font-bold tabular-nums">{{ number_format($this->vacationSubsidyAmount, 2) }}</span>
                                            </div>
                                            @endif

                                            {{-- Additional Bonus --}}
                                            @if($additional_bonus_amount > 0)
                                            <div class="flex justify-between items-center p-2.5 bg-gray-50 rounded-lg">
                                                <span class="text-gray-700 font-medium">Additional Bonus</span>
                                                <span class="text-gray-800 font-bold tabular-nums">{{ number_format($additional_bonus_amount, 2) }}</span>
                                            </div>
                                            @endif

                                            {{-- Ajuda Familiar --}}
                                            @if($family_allowance > 0)
                                            <div class="flex justify-between items-center p-2.5 bg-gray-50 rounded-lg">
                                                <span class="text-gray-700 font-medium">Ajuda Familiar</span>
                                                <span class="text-gray-800 font-bold tabular-nums">{{ number_format($family_allowance, 2) }}</span>
                                            </div>
                                            @endif

                                            {{-- Subsídio de cargo --}}
                                            @if($selectedEmployee && $selectedEmployee->position_subsidy > 0)
                                            <div class="flex justify-between items-center p-2.5 bg-gray-50 rounded-lg">
                                                <span class="text-gray-700 font-medium">Subsídio de cargo</span>
                                                <span class="text-gray-800 font-bold tabular-nums">{{ number_format($selectedEmployee->position_subsidy, 2) }}</span>
                                            </div>
                                            @endif

                                            {{-- Subsídio de desempenho --}}
                                            @if($selectedEmployee && $selectedEmployee->performance_subsidy > 0)
                                            <div class="flex justify-between items-center p-2.5 bg-gray-50 rounded-lg">
                                                <span class="text-gray-700 font-medium">{{ __('messages.performance_subsidy') }}</span>
                                                <span class="text-gray-800 font-bold tabular-nums">{{ number_format($selectedEmployee->performance_subsidy, 2) }}</span>
                                            </div>
                                            @endif

                                            {{-- Absence (dedução) com detalhes - sempre mostrar --}}
                                            <div class="p-3 {{ ($absence_deduction ?? 0) > 0 ? 'bg-red-50 border-red-200' : 'bg-gray-50 border-gray-200' }} rounded-lg border">
                                                <div class="flex justify-between items-center mb-2">
                                                    <span class="{{ ($absence_deduction ?? 0) > 0 ? 'text-red-700' : 'text-gray-600' }} font-bold flex items-center">
                                                        <i class="fas fa-calendar-times mr-2"></i>
                                                        {{ __('messages.absence_deductions') }}
                                                    </span>
                                                    <span class="{{ ($absence_deduction ?? 0) > 0 ? 'text-red-800' : 'text-gray-700' }} font-bold text-lg tabular-nums">
                                                        {{ ($absence_deduction ?? 0) > 0 ? '-' : '' }}{{ number_format($absence_deduction ?? 0, 2) }}
                                                    </span>
                                                </div>
                                                <div class="text-xs {{ ($absence_deduction ?? 0) > 0 ? 'text-red-600' : 'text-gray-600' }} space-y-1 pl-6">
                                                    <div class="flex justify-between">
                                                        <span>• {{ __('messages.absent_days') }}:</span>
                                                        <span class="font-semibold">{{ $absent_days ?? 0 }} {{ __('messages.days') }}</span>
                                                    </div>
                                                    <div class="flex justify-between">
                                                        <span>• {{ __('messages.daily_rate') }}:</span>
                                                        <span class="font-semibold">{{ number_format(($daily_rate ?? 0), 2) }} AOA</span>
                                                    </div>
                                                    @if(($absent_days ?? 0) > 0)
                                                    <div class="flex justify-between pt-1 border-t {{ ($absence_deduction ?? 0) > 0 ? 'border-red-200' : 'border-gray-200' }}">
                                                        <span>• {{ __('messages.calculation') }}:</span>
                                                        <span class="font-semibold">{{ $absent_days }} × {{ number_format(($daily_rate ?? 0), 2) }}</span>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-4 pt-3 border-t-2 border-gray-300">
                                            <div class="text-xs text-gray-600 mb-2">
                                                Alimentação e Transporte: isentos até <strong>30.000 AOA</strong> cada (tributa apenas o excesso).
                                            </div>
                                        </div>
                                    </section>

                                    {{-- COLUNA 2 - BASE IRT & DEDUÇÕES --}}
                                    <section class="bg-white rounded-xl shadow-md border border-gray-200 p-5">
                                        <h4 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b-2 border-green-500">
                                            <i class="fas fa-calculator text-green-600 mr-2"></i>
                                            Base IRT & Deduções
                                        </h4>

                                        <table class="w-full text-sm">
                                            <tbody class="space-y-1.5">
                                                <tr class="border-b border-gray-200">
                                                    <td class="py-2 text-gray-700">Gross Salary</td>
                                                    <td class="py-2 text-right font-bold tabular-nums text-gray-900">{{ number_format($gross_salary, 2) }}</td>
                                                </tr>
                                                <tr class="border-b border-gray-100">
                                                    <td class="py-2 text-gray-600 text-xs">Food Payment &gt; 30.000 (excesso)</td>
                                                    <td class="py-2 text-right tabular-nums text-gray-700">{{ number_format($taxable_food ?? 0, 2) }}</td>
                                                </tr>
                                                <tr class="border-b border-gray-100">
                                                    <td class="py-2 text-gray-600 text-xs">Transport &gt; 30.000 (excesso)</td>
                                                    <td class="py-2 text-right tabular-nums text-gray-700">{{ number_format($taxable_transport ?? 0, 2) }}</td>
                                                </tr>
                                                <tr class="border-b border-gray-100">
                                                    <td class="py-2 text-gray-600 text-xs">Food exemption (até 30k)</td>
                                                    <td class="py-2 text-right tabular-nums text-green-700 font-medium">-{{ number_format($exempt_food ?? 0, 2) }}</td>
                                                </tr>
                                                <tr class="border-b border-gray-100">
                                                    <td class="py-2 text-gray-600 text-xs">Transport exemption (até 30k)</td>
                                                    <td class="py-2 text-right tabular-nums text-green-700 font-medium">-{{ number_format($exempt_transport ?? 0, 2) }}</td>
                                                </tr>
                                                <tr class="border-b border-gray-200">
                                                    <td class="py-2 text-gray-700">INSS rate</td>
                                                    <td class="py-2 text-right tabular-nums text-gray-900">3.00%</td>
                                                </tr>
                                                <tr class="border-b border-gray-200">
                                                    <td class="py-2 text-gray-700">INSS amount</td>
                                                    <td class="py-2 text-right font-medium tabular-nums text-red-700">-{{ number_format($inss_3_percent, 2) }}</td>
                                                </tr>
                                                <tr class="border-b border-gray-200 bg-blue-50">
                                                    <td class="py-2 text-blue-800 font-bold">Base IRT antes do INSS</td>
                                                    <td class="py-2 text-right font-bold tabular-nums text-blue-900">{{ number_format(($gross_salary - ($exempt_food ?? 0) - ($exempt_transport ?? 0)), 2) }}</td>
                                                </tr>
                                                <tr class="bg-green-50">
                                                    <td class="py-3 text-green-800 font-bold">Base IRT (após INSS)</td>
                                                    <td class="py-3 text-right font-bold text-lg tabular-nums text-green-900">{{ number_format($irt_base ?? $base_irt_taxable_amount, 2) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </section>

                                    {{-- COLUNA 3 - IRT & LÍQUIDO --}}
                                    <section class="bg-white rounded-xl shadow-md border border-gray-200 p-5">
                                        <h4 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b-2 border-purple-500">
                                            <i class="fas fa-coins text-purple-600 mr-2"></i>
                                            IRT &amp; Líquido
                                        </h4>

                                        <table class="w-full text-sm">
                                            <tbody>
                                                <tr class="border-b border-gray-200">
                                                    <td class="py-2 text-gray-700">Base IRT</td>
                                                    <td class="py-2 text-right font-medium tabular-nums text-gray-900">{{ number_format($irt_base ?? $base_irt_taxable_amount, 2) }}</td>
                                                </tr>
                                                <tr class="border-b border-gray-100">
                                                    <td class="py-2 text-gray-600">Gross Salary</td>
                                                    <td class="py-2 text-right tabular-nums text-gray-800">{{ number_format($gross_salary, 2) }}</td>
                                                </tr>
                                                <tr class="border-b border-gray-100">
                                                    <td class="py-2 text-gray-600">INSS 3%</td>
                                                    <td class="py-2 text-right tabular-nums text-red-700">-{{ number_format($inss_3_percent ?? 0, 2) }}</td>
                                                </tr>
                                                <tr class="border-b border-gray-200 bg-red-50">
                                                    <td class="py-2 text-red-800 font-bold">IRT (PF + taxa × excesso)</td>
                                                    <td class="py-2 text-right font-bold tabular-nums text-red-900">-{{ number_format($income_tax ?? 0, 2) }}</td>
                                                </tr>
                                                {{-- Salary Advances --}}
                                                @if(!empty($salaryAdvances))
                                                    @foreach($salaryAdvances as $advance)
                                                    <tr class="border-b border-gray-100">
                                                        <td class="py-2 text-xs">
                                                            <span class="text-orange-600"><i class="fas fa-hand-holding-usd mr-1"></i>Salary Advance</span>
                                                            <span class="text-gray-500">({{ $advance['reason'] ?? 'Adiantamento' }})</span>
                                                        </td>
                                                        <td class="py-2 text-right tabular-nums text-orange-700">-{{ number_format($advance['installment_amount'] ?? 0, 2) }}</td>
                                                    </tr>
                                                    @endforeach
                                                @endif
                                                
                                                {{-- Salary Discounts --}}
                                                @if(!empty($salaryDiscounts))
                                                    @foreach($salaryDiscounts as $discount)
                                                    <tr class="border-b border-gray-100">
                                                        <td class="py-2 text-xs">
                                                            @if($discount['discount_type'] === 'union')
                                                                <span class="text-blue-600"><i class="fas fa-users mr-1"></i>Union Discount</span>
                                                            @elseif($discount['discount_type'] === 'quixiquila')
                                                                <span class="text-purple-600"><i class="fas fa-hand-holding-usd mr-1"></i>Quixiquila</span>
                                                            @else
                                                                <span class="text-gray-600"><i class="fas fa-minus-circle mr-1"></i>Other Discount</span>
                                                            @endif
                                                            <span class="text-gray-500">({{ $discount['reason'] ?? '' }})</span>
                                                        </td>
                                                        <td class="py-2 text-right tabular-nums text-gray-700">-{{ number_format($discount['installment_amount'] ?? 0, 2) }}</td>
                                                    </tr>
                                                    @endforeach
                                                @endif
                                                @if($selectedEmployee && $selectedEmployee->food_benefit > 0)
                                                <tr class="border-b border-gray-100">
                                                    <td class="py-2 text-red-600">Food allowance (não pago)</td>
                                                    <td class="py-2 text-right tabular-nums text-red-700">-{{ number_format($selectedEmployee->food_benefit, 2) }}</td>
                                                </tr>
                                                @endif
                                                <tr class="bg-green-100 border-t-2 border-green-500">
                                                    <td class="py-3 text-green-900 font-bold text-base">NET TOTAL</td>
                                                    <td class="py-3 text-right font-bold text-xl tabular-nums text-green-900">{{ number_format($calculatedData['net_salary'] ?? $net_salary ?? 0, 2) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        @if(isset($irtCalculationDetails['bracket']))
                                        <div class="mt-4 p-3 bg-gray-50 rounded-lg text-xs text-gray-700">
                                            <strong>Cálculo do IRT:</strong>
                                            <div class="mt-1 font-mono">
                                                PF {{ number_format($irtCalculationDetails['fixed_amount'] ?? 0, 2) }} 
                                                + {{ number_format(($irtCalculationDetails['bracket']->tax_rate ?? 0) * 100, 2) }}% 
                                                × (Base {{ number_format($irt_base ?? $base_irt_taxable_amount, 2) }} 
                                                − Excess over {{ number_format($irtCalculationDetails['bracket']->min_income ?? 0, 0) }})
                                            </div>
                                        </div>
                                        @endif
                                    </section>

                                </div>

                                {{-- RESUMO DE PRESENÇA --}}
                                <div class="mt-6 bg-white rounded-xl shadow-md border border-gray-200 p-5">
                                    <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                                        <i class="fas fa-calendar-check text-blue-600 mr-2"></i>
                                        Resumo de Presença
                                    </h4>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <div class="bg-green-50 p-4 rounded-lg border border-green-200 text-center">
                                            <div class="text-2xl font-bold text-green-700">{{ $present_days ?? 0 }}</div>
                                            <div class="text-xs text-gray-600 mt-1">Dias Presentes</div>
                                        </div>
                                        <div class="bg-red-50 p-4 rounded-lg border border-red-200 text-center">
                                            <div class="text-2xl font-bold text-red-700">{{ $absent_days ?? 0 }}</div>
                                            <div class="text-xs text-gray-600 mt-1">Faltas</div>
                                        </div>
                                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200 text-center">
                                            <div class="text-2xl font-bold text-blue-700">{{ $total_working_days ?? 0 }}</div>
                                            <div class="text-xs text-gray-600 mt-1">Dias Úteis</div>
                                        </div>
                                        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200 text-center">
                                            <div class="text-2xl font-bold text-purple-700">{{ number_format($total_overtime_hours ?? 0, 1) }}h</div>
                                            <div class="text-xs text-gray-600 mt-1">Horas Extra</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 text-sm text-gray-600">
                                    ⚠️ <strong>Notas:</strong> Ausência subtrai; INSS incide no bruto; isenções de 30k p/ alimentação e transporte antes do IRT.
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
