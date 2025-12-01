{{-- Payroll Summary - Estrutura baseada em payr.html (3 colunas organizadas) --}}
@if(!empty($calculatedData))
{{-- Left Panel - Payroll Summary (LADO ESQUERDO) --}}
<div class="w-full xl:w-1/2 bg-gradient-to-br from-gray-50 to-gray-100 overflow-y-auto flex-1 p-4 lg:p-6 order-1">
    
    <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
        <i class="fas fa-file-invoice-dollar text-blue-600 mr-3"></i>
        {{ __('messages.payroll_summary') }}
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
                    <span class="text-blue-800 font-bold tabular-nums">{{ number_format($calculatedData['basic_salary'], 2) }}</span>
                </div>

                {{-- Transport --}}
                <div class="flex justify-between items-center p-2.5 bg-gray-50 rounded-lg">
                    <span class="text-gray-700 font-medium">Transport</span>
                    <span class="text-gray-800 font-bold tabular-nums">{{ number_format($calculatedData['transport_allowance'], 2) }}</span>
                </div>

                {{-- Food allowance --}}
                <div class="flex justify-between items-center p-2.5 bg-gray-50 rounded-lg">
                    <span class="text-gray-700 font-medium">Food allowance</span>
                    <span class="text-gray-800 font-bold tabular-nums">{{ number_format($calculatedData['food_benefit'] ?? 0, 2) }}</span>
                </div>

                {{-- Night Allowance --}}
                @if(($calculatedData['night_allowance'] ?? 0) > 0)
                <div class="flex justify-between items-center p-2.5 bg-gray-50 rounded-lg">
                    <span class="text-gray-700 font-medium">Night Allowance</span>
                    <span class="text-gray-800 font-bold tabular-nums">{{ number_format($calculatedData['night_allowance'] ?? 0, 2) }}</span>
                </div>
                @endif

                {{-- Total Over Time --}}
                @if(($calculatedData['total_overtime_amount'] ?? 0) > 0)
                <div class="flex justify-between items-center p-2.5 bg-gray-50 rounded-lg">
                    <span class="text-gray-700 font-medium">Total Over Time</span>
                    <span class="text-gray-800 font-bold tabular-nums">{{ number_format($calculatedData['total_overtime_amount'], 2) }}</span>
                </div>
                @endif

                {{-- Natal Allowance --}}
                @if(($calculatedData['christmas_subsidy_amount'] ?? 0) > 0)
                <div class="flex justify-between items-center p-2.5 bg-gray-50 rounded-lg">
                    <span class="text-gray-700 font-medium">Natal Allowance</span>
                    <span class="text-gray-800 font-bold tabular-nums">{{ number_format($calculatedData['christmas_subsidy_amount'], 2) }}</span>
                </div>
                @endif

                {{-- Leave Allowance --}}
                @if(($calculatedData['vacation_subsidy_amount'] ?? 0) > 0)
                <div class="flex justify-between items-center p-2.5 bg-gray-50 rounded-lg">
                    <span class="text-gray-700 font-medium">Leave Allowance</span>
                    <span class="text-gray-800 font-bold tabular-nums">{{ number_format($calculatedData['vacation_subsidy_amount'], 2) }}</span>
                </div>
                @endif

                {{-- Additional Bonus (batch) --}}
                @if(($calculatedData['additional_bonus_amount'] ?? 0) > 0)
                <div class="flex justify-between items-center p-2.5 bg-gray-50 rounded-lg">
                    <span class="text-gray-700 font-medium">Additional Bonus</span>
                    <span class="text-gray-800 font-bold tabular-nums">{{ number_format($calculatedData['additional_bonus_amount'], 2) }}</span>
                </div>
                @endif

                {{-- Ajuda Familiar --}}
                @if(($calculatedData['family_allowance'] ?? 0) > 0)
                <div class="flex justify-between items-center p-2.5 bg-gray-50 rounded-lg">
                    <span class="text-gray-700 font-medium">Ajuda Familiar</span>
                    <span class="text-gray-800 font-bold tabular-nums">{{ number_format($calculatedData['family_allowance'] ?? 0, 2) }}</span>
                </div>
                @endif

                {{-- Subsídio de cargo --}}
                @if(($calculatedData['position_subsidy'] ?? 0) > 0)
                <div class="flex justify-between items-center p-2.5 bg-gray-50 rounded-lg">
                    <span class="text-gray-700 font-medium">{{ __('messages.position_subsidy') }}</span>
                    <span class="text-gray-800 font-bold tabular-nums">{{ number_format($calculatedData['position_subsidy'], 2) }}</span>
                </div>
                @endif

                {{-- Subsídio de desempenho --}}
                @if(($calculatedData['performance_subsidy'] ?? 0) > 0)
                <div class="flex justify-between items-center p-2.5 bg-gray-50 rounded-lg">
                    <span class="text-gray-700 font-medium">{{ __('messages.performance_subsidy') }}</span>
                    <span class="text-gray-800 font-bold tabular-nums">{{ number_format($calculatedData['performance_subsidy'], 2) }}</span>
                </div>
                @endif

                {{-- Absence (dedução) com detalhes - sempre mostrar --}}
                <div class="p-3 {{ ($calculatedData['absence_deduction'] ?? 0) > 0 ? 'bg-red-50 border-red-200' : 'bg-gray-50 border-gray-200' }} rounded-lg border">
                    <div class="flex justify-between items-center mb-2">
                        <span class="{{ ($calculatedData['absence_deduction'] ?? 0) > 0 ? 'text-red-700' : 'text-gray-600' }} font-bold flex items-center">
                            <i class="fas fa-calendar-times mr-2"></i>
                            {{ __('messages.absence_deductions') }}
                        </span>
                        <span class="{{ ($calculatedData['absence_deduction'] ?? 0) > 0 ? 'text-red-800' : 'text-gray-700' }} font-bold text-lg tabular-nums">
                            {{ ($calculatedData['absence_deduction'] ?? 0) > 0 ? '-' : '' }}{{ number_format($calculatedData['absence_deduction'] ?? 0, 2) }}
                        </span>
                    </div>
                    <div class="text-xs {{ ($calculatedData['absence_deduction'] ?? 0) > 0 ? 'text-red-600' : 'text-gray-600' }} space-y-1 pl-6">
                        <div class="flex justify-between">
                            <span>• {{ __('messages.absent_days') }}:</span>
                            <span class="font-semibold">{{ $calculatedData['absent_days'] ?? 0 }} {{ __('messages.days') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>• {{ __('messages.daily_rate') }}:</span>
                            <span class="font-semibold">{{ number_format(($calculatedData['daily_rate'] ?? 0), 2) }} AOA</span>
                        </div>
                        @if(($calculatedData['absent_days'] ?? 0) > 0)
                        <div class="flex justify-between pt-1 border-t {{ ($calculatedData['absence_deduction'] ?? 0) > 0 ? 'border-red-200' : 'border-gray-200' }}">
                            <span>• {{ __('messages.calculation') }}:</span>
                            <span class="font-semibold">{{ $calculatedData['absent_days'] }} × {{ number_format(($calculatedData['daily_rate'] ?? 0), 2) }}</span>
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
                {{ __('messages.irt_base_and_deductions') }}
            </h4>

            <table class="w-full text-sm">
                <tbody class="space-y-1.5">
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <td class="py-2 text-gray-700 font-bold">{{ __('messages.gross_salary') }}</td>
                        <td class="py-2 text-right font-bold tabular-nums text-gray-900">{{ number_format($calculatedData['gross_salary'], 2) }}</td>
                    </tr>
                    @if(($calculatedData['absence_deduction'] ?? 0) > 0 || ($calculatedData['late_deduction'] ?? 0) > 0)
                    <tr class="border-b border-gray-100">
                        <td class="py-1 text-xs text-gray-500 italic pl-4">
                            ↳ {{ __('messages.includes_attendance_deductions') }}
                            @if(($calculatedData['absence_deduction'] ?? 0) > 0)
                                ({{ __('messages.absences') }}: -{{ number_format($calculatedData['absence_deduction'], 2) }})
                            @endif
                            @if(($calculatedData['late_deduction'] ?? 0) > 0)
                                ({{ __('messages.late_arrivals_short') }}: -{{ number_format($calculatedData['late_deduction'], 2) }})
                            @endif
                        </td>
                        <td class="py-1"></td>
                    </tr>
                    @endif
                    <tr class="border-b border-gray-100">
                        <td class="py-2 text-gray-600 text-xs">{{ __('messages.food_payment_excess') }}</td>
                        <td class="py-2 text-right tabular-nums text-gray-700">{{ number_format($calculatedData['taxable_food'] ?? 0, 2) }}</td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-2 text-gray-600 text-xs">{{ __('messages.transport_excess') }}</td>
                        <td class="py-2 text-right tabular-nums text-gray-700">{{ number_format($calculatedData['taxable_transport'] ?? 0, 2) }}</td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-2 text-gray-600 text-xs">{{ __('messages.food_exemption_up_to_30k') }}</td>
                        <td class="py-2 text-right tabular-nums text-green-700 font-medium">-{{ number_format($calculatedData['exempt_food'] ?? 0, 2) }}</td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-2 text-gray-600 text-xs">{{ __('messages.transport_exemption_up_to_30k') }}</td>
                        <td class="py-2 text-right tabular-nums text-green-700 font-medium">-{{ number_format($calculatedData['exempt_transport'] ?? 0, 2) }}</td>
                    </tr>
                    <tr class="border-b border-gray-200">
                        <td class="py-2 text-gray-700">{{ __('messages.inss_rate') }}</td>
                        <td class="py-2 text-right tabular-nums text-gray-900">3.00%</td>
                    </tr>
                    <tr class="border-b border-gray-200">
                        <td class="py-2 text-gray-700">{{ __('messages.inss_amount') }}</td>
                        <td class="py-2 text-right font-medium tabular-nums text-red-700">-{{ number_format($calculatedData['inss_3_percent'], 2) }}</td>
                    </tr>
                    <tr class="border-b border-gray-200 bg-blue-50">
                        <td class="py-2 text-blue-800 font-bold">{{ __('messages.irt_base_before_inss') }}</td>
                        <td class="py-2 text-right font-bold tabular-nums text-blue-900">{{ number_format(($calculatedData['gross_salary'] - ($calculatedData['exempt_food'] ?? 0) - ($calculatedData['exempt_transport'] ?? 0)), 2) }}</td>
                    </tr>
                    <tr class="bg-green-50">
                        <td class="py-3 text-green-800 font-bold">{{ __('messages.irt_base_after_inss') }}</td>
                        <td class="py-3 text-right font-bold text-lg tabular-nums text-green-900">{{ number_format($calculatedData['irt_base'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </section>

        {{-- COLUNA 3 - IRT & LÍQUIDO --}}
        <section class="bg-white rounded-xl shadow-md border border-gray-200 p-5">
            <h4 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b-2 border-purple-500">
                <i class="fas fa-coins text-purple-600 mr-2"></i>
                {{ __('messages.irt_and_net') }}
            </h4>

            <table class="w-full text-sm">
                <tbody>
                    <tr class="border-b border-gray-200">
                        <td class="py-2 text-gray-700">{{ __('messages.irt_base') }}</td>
                        <td class="py-2 text-right font-medium tabular-nums text-gray-900">{{ number_format($calculatedData['irt_base'], 2) }}</td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-2 text-gray-600">{{ __('messages.gross_salary') }}</td>
                        <td class="py-2 text-right tabular-nums text-gray-800">{{ number_format($calculatedData['gross_salary'], 2) }}</td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-2 text-gray-600">INSS 3%</td>
                        <td class="py-2 text-right tabular-nums text-red-700">-{{ number_format($calculatedData['inss_3_percent'], 2) }}</td>
                    </tr>
                    <tr class="border-b border-gray-200 bg-red-50">
                        <td class="py-2 text-red-800 font-bold">{{ __('messages.irt_calculation_formula') }}</td>
                        <td class="py-2 text-right font-bold tabular-nums text-red-900">-{{ number_format($calculatedData['irt'], 2) }}</td>
                    </tr>
                    {{-- Salary Advances --}}
                    @if(!empty($salaryAdvances))
                        @foreach($salaryAdvances as $advance)
                        <tr class="border-b border-gray-100">
                            <td class="py-2 text-xs">
                                <span class="text-orange-600"><i class="fas fa-hand-holding-usd mr-1"></i>{{ __('messages.salary_advance') }}</span>
                                <span class="text-gray-500">({{ $advance['reason'] ?? __('messages.advance_payment') }})</span>
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
                                    <span class="text-blue-600"><i class="fas fa-users mr-1"></i>{{ __('messages.union_discount') }}</span>
                                @elseif($discount['discount_type'] === 'quixiquila')
                                    <span class="text-purple-600"><i class="fas fa-hand-holding-usd mr-1"></i>{{ __('messages.quixiquila') }}</span>
                                @else
                                    <span class="text-gray-600"><i class="fas fa-minus-circle mr-1"></i>{{ __('messages.other_discount') }}</span>
                                @endif
                                <span class="text-gray-500">({{ $discount['reason'] ?? '' }})</span>
                            </td>
                            <td class="py-2 text-right tabular-nums text-gray-700">-{{ number_format($discount['installment_amount'] ?? 0, 2) }}</td>
                        </tr>
                        @endforeach
                    @endif
                    {{-- Absence Deduction --}}
                    @if(($calculatedData['absence_deduction'] ?? 0) > 0)
                    <tr class="border-b border-gray-100">
                        <td class="py-2 text-xs">
                            <span class="text-red-600"><i class="fas fa-calendar-times mr-1"></i>{{ __('messages.absence_deductions') }}</span>
                            <span class="text-gray-500">({{ __('messages.absence_deductions') }})</span>
                        </td>
                        <td class="py-2 text-right tabular-nums text-red-700">-{{ number_format($calculatedData['absence_deduction'], 2) }}</td>
                    </tr>
                    @endif
                    {{-- Late Deduction --}}
                    @if(($calculatedData['late_deduction'] ?? 0) > 0)
                    <tr class="border-b border-gray-100">
                        <td class="py-2 text-xs">
                            <span class="text-orange-600"><i class="fas fa-clock mr-1"></i>{{ __('messages.late_deduction') }}</span>
                            <span class="text-gray-500">({{ __('messages.late_deductions_desc') }})</span>
                        </td>
                        <td class="py-2 text-right tabular-nums text-orange-700">-{{ number_format($calculatedData['late_deduction'], 2) }}</td>
                    </tr>
                    @endif
                    @if(($calculatedData['food_benefit'] ?? 0) > 0)
                    <tr class="border-b border-gray-100">
                        <td class="py-2 text-red-600">{{ __('messages.food_allowance_not_paid') }}</td>
                        <td class="py-2 text-right tabular-nums text-red-700">-{{ number_format($calculatedData['food_benefit'], 2) }}</td>
                    </tr>
                    @endif
                    <tr class="bg-green-100 border-t-2 border-green-500">
                        <td class="py-3 text-green-900 font-bold text-base">{{ __('messages.net_total') }}</td>
                        <td class="py-3 text-right font-bold text-xl tabular-nums text-green-900">{{ number_format($calculatedData['net_salary'], 2) }}</td>
                    </tr>
                </tbody>
            </table>

            @if(isset($calculatedData['irt_calculation_details']['bracket']))
            <div class="mt-4 p-3 bg-gray-50 rounded-lg text-xs text-gray-700">
                <strong>{{ __('messages.irt_calculation_title') }}:</strong>
                <div class="mt-1 font-mono">
                    PF {{ number_format($calculatedData['irt_calculation_details']['fixed_amount'], 2) }} 
                    + {{ number_format($calculatedData['irt_calculation_details']['bracket']->tax_rate * 100, 2) }}% 
                    × (Base {{ number_format($calculatedData['irt_base'], 2) }} 
                    − {{ __('messages.excess_over') }} {{ number_format($calculatedData['irt_calculation_details']['bracket']->min_income, 0) }})
                </div>
            </div>
            @endif
        </section>

    </div>

</div>

{{-- Right Panel - Employee Data (LADO DIREITO) - mantido do original --}}
<div class="w-full xl:w-1/2 bg-white overflow-y-auto flex-1 p-4 lg:p-6 order-2">
    
    {{-- Employee Basic Info Card --}}
    <div class="bg-white rounded-xl border border-blue-200 p-5 shadow-sm">
        <h3 class="text-lg font-bold text-blue-800 mb-4 flex items-center">
            <i class="fas fa-id-card mr-2"></i>
            {{ __('messages.employee_information') }}
        </h3>
        <div class="grid grid-cols-2 gap-3 text-sm">
            <div>
                <span class="text-gray-600">{{ __('messages.base_salary') }}:</span>
                <span class="font-bold text-gray-900 ml-2">{{ number_format($calculatedData['basic_salary'], 2) }} AOA</span>
            </div>
            <div>
                <span class="text-gray-600">{{ __('messages.hourly_rate') }}:</span>
                <span class="font-bold text-gray-900 ml-2">{{ number_format($calculatedData['hourly_rate'], 2) }} AOA/h</span>
            </div>
            <div>
                <span class="text-gray-600">{{ __('messages.daily_rate_label') }}:</span>
                <span class="font-bold text-gray-900 ml-2">{{ number_format($calculatedData['daily_rate'], 2) }} AOA/dia</span>
            </div>
            <div>
                <span class="text-gray-600">{{ __('messages.working_days_label') }}:</span>
                <span class="font-bold text-gray-900 ml-2">{{ $calculatedData['monthly_working_days'] }} dias</span>
            </div>
        </div>
    </div>

    {{-- Attendance Summary Card --}}
    <div class="bg-gradient-to-br from-green-50 to-emerald-100 p-6 rounded-2xl border border-green-200 mt-6">
        <h3 class="text-lg font-bold text-green-800 mb-4 flex items-center">
            <i class="fas fa-clock mr-2"></i>
            {{ __('messages.attendance_summary') }}
        </h3>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white/60 p-3 rounded-lg text-center">
                <p class="text-xs text-green-600 font-medium mb-1">{{ __('messages.hours_worked') }}</p>
                <p class="text-lg text-green-800 font-bold">{{ number_format($calculatedData['attendance_hours'], 1) }}h</p>
            </div>
            <div class="bg-white/60 p-3 rounded-lg text-center">
                <p class="text-xs text-green-600 font-medium mb-1">{{ __('messages.present_days') }}</p>
                <p class="text-lg text-green-800 font-bold">{{ $calculatedData['present_days'] }}</p>
            </div>
            <div class="bg-white/60 p-3 rounded-lg text-center">
                <p class="text-xs text-orange-600 font-medium mb-1">{{ __('messages.absences_short') }}</p>
                <p class="text-lg text-orange-600 font-bold">{{ $calculatedData['absent_days'] }}</p>
            </div>
            <div class="bg-white/60 p-3 rounded-lg text-center">
                <p class="text-xs text-yellow-600 font-medium mb-1">{{ __('messages.late_arrivals') }}</p>
                <p class="text-lg text-yellow-600 font-bold">{{ $calculatedData['late_days'] }}</p>
            </div>
        </div>
    </div>

    {{-- Overtime Card --}}
    @if($calculatedData['total_overtime_hours'] > 0)
    <div class="bg-gradient-to-br from-orange-50 to-amber-100 p-6 rounded-2xl border border-orange-200 mt-6">
        <h3 class="text-lg font-bold text-orange-800 mb-4 flex items-center">
            <i class="fas fa-clock mr-2"></i>
            {{ __('messages.overtime_hours') }}
        </h3>
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white/60 p-3 rounded-lg text-center">
                <p class="text-xs text-orange-600 font-medium mb-1">{{ __('messages.total_hours') }}</p>
                <p class="text-lg text-orange-800 font-bold">{{ number_format($calculatedData['total_overtime_hours'], 1) }}h</p>
            </div>
            <div class="bg-white/60 p-3 rounded-lg text-center">
                <p class="text-xs text-orange-600 font-medium mb-1">{{ __('messages.total_value') }}</p>
                <p class="text-lg text-orange-800 font-bold">{{ number_format($calculatedData['total_overtime_amount'], 2) }} AOA</p>
            </div>
        </div>
    </div>
    @endif

</div>

@else
{{-- Loading State --}}
<div class="w-full flex items-center justify-center p-12">
    <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-orange-600 mb-4"></div>
        <p class="text-gray-600">Calculando...</p>
    </div>
</div>
@endif
