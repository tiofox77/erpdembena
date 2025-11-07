{{-- Payroll Summary - Replica estrutura da modal individual usando dados do helper --}}
@if(!empty($calculatedData))
{{-- Left Panel - Payroll Summary (LADO ESQUERDO) --}}
<div class="w-full xl:w-1/2 bg-gradient-to-br from-gray-50 to-gray-100 overflow-y-auto flex-1 p-4 lg:p-6 order-1">
    
    {{-- Payroll Summary Card --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-4 lg:p-6 shadow-sm">
        <h3 class="text-lg lg:text-xl font-bold text-gray-800 mb-4 lg:mb-6 flex items-center">
            <i class="fas fa-chart-pie text-green-500 mr-2"></i>
            Resumo da Folha de Pagamento
        </h3>
        
        <div class="space-y-3 lg:space-y-4">
            {{-- Base Salary --}}
            <div class="flex justify-between items-center p-3 lg:p-4 bg-blue-50 rounded-xl">
                <span class="font-medium text-blue-700 text-sm lg:text-base">Salário Base</span>
                <span class="font-bold text-blue-800 text-sm lg:text-lg">{{ number_format($calculatedData['basic_salary'], 2) }} AOA</span>
            </div>

            {{-- Food Allowance - SEMPRE MOSTRAR --}}
            <div class="flex justify-between items-center p-3 lg:p-4 bg-teal-50 rounded-xl">
                <div>
                    <span class="font-medium text-teal-700 text-sm lg:text-base">
                        <i class="fas fa-utensils mr-1 text-teal-600"></i>
                        Subsídio de Alimentação
                    </span>
                    @if(!($calculatedData['is_food_in_kind'] ?? false))
                    <div class="text-xs text-teal-600 mt-1">
                        <span class="bg-gray-200 text-gray-600 px-2 py-0.5 rounded">Não tributável</span>
                    </div>
                    @endif
                </div>
                <span class="font-bold text-teal-800 text-sm lg:text-lg">{{ number_format($calculatedData['food_benefit'] ?? 0, 2) }} AOA</span>
            </div>

            {{-- Christmas Subsidy --}}
            @if($calculatedData['christmas_subsidy_amount'] > 0)
            <div class="flex justify-between items-center p-3 lg:p-4 bg-emerald-50 rounded-xl">
                <span class="font-medium text-emerald-700 text-sm lg:text-base">
                    <i class="fas fa-gift mr-1 text-emerald-600"></i>
                    Subsídio de Natal
                </span>
                <span class="font-bold text-emerald-800 text-sm lg:text-lg">+{{ number_format($calculatedData['christmas_subsidy_amount'], 2) }} AOA</span>
            </div>
            @endif

            {{-- Vacation Subsidy --}}
            @if($calculatedData['vacation_subsidy_amount'] > 0)
            <div class="flex justify-between items-center p-3 lg:p-4 bg-emerald-50 rounded-xl">
                <span class="font-medium text-emerald-700 text-sm lg:text-base">
                    <i class="fas fa-umbrella-beach mr-1 text-emerald-600"></i>
                    Subsídio de Férias
                </span>
                <span class="font-bold text-emerald-800 text-sm lg:text-lg">+{{ number_format($calculatedData['vacation_subsidy_amount'], 2) }} AOA</span>
            </div>
            @endif

            {{-- Transport Allowance - SEMPRE MOSTRAR --}}
            <div class="p-2 lg:p-3 bg-gradient-to-r from-green-50 to-yellow-50 rounded-lg border border-green-200">
                <div class="flex justify-between items-center mb-2">
                    <div class="flex items-center space-x-2">
                        <span class="font-medium text-gray-700 text-xs lg:text-sm">Subsídio de Transporte</span>
                        <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded-full font-medium">Proporcional</span>
                    </div>
                    <span class="font-bold text-gray-800 text-xs lg:text-sm">{{ number_format($calculatedData['transport_allowance'], 2) }} AOA</span>
                </div>
                
                <div class="space-y-1 text-xs bg-white/50 p-2 rounded">
                    <div class="flex justify-between">
                        <span class="text-blue-600 font-medium">• Total do Benefício:</span>
                        <span class="text-blue-700 font-semibold">{{ number_format($calculatedData['transport_benefit_full'], 2) }} AOA</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">• Dias Presentes:</span>
                        <span class="text-gray-700">{{ $calculatedData['present_days'] }}/{{ $calculatedData['total_working_days'] }} dias</span>
                    </div>
                    @if($calculatedData['transport_discount'] > 0)
                    <div class="flex justify-between border-t pt-1">
                        <span class="text-red-600">• Desconto por Faltas:</span>
                        <span class="text-red-700 font-medium">-{{ number_format($calculatedData['transport_discount'], 2) }} AOA</span>
                    </div>
                    @endif
                    <div class="flex justify-between border-t pt-1 font-semibold">
                        <span class="text-green-600">• Valor a Pagar:</span>
                        <span class="text-green-700">{{ number_format($calculatedData['transport_allowance'], 2) }} AOA</span>
                    </div>
                </div>
                
                <div class="space-y-1 text-xs mt-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">• Isento (até 30k):</span>
                        <span class="text-gray-700 font-medium">{{ number_format($calculatedData['exempt_transport'], 2) }} AOA</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-green-600">• Tributável:</span>
                        <span class="text-green-700 font-medium">{{ number_format($calculatedData['taxable_transport'], 2) }} AOA</span>
                    </div>
                </div>
            </div>

            {{-- Employee Profile Bonus --}}
            @if($calculatedData['bonus_amount'] > 0)
            <div class="flex justify-between items-center p-3 lg:p-4 bg-blue-50 rounded-xl">
                <span class="font-medium text-blue-700 text-sm lg:text-base">
                    <i class="fas fa-user-tag text-blue-600 mr-1"></i>
                    Bónus do Perfil do Funcionário
                </span>
                <span class="font-bold text-blue-800 text-sm lg:text-lg">+{{ number_format($calculatedData['bonus_amount'], 2) }} AOA</span>
            </div>
            @endif

            {{-- Position Subsidy --}}
            @if(($calculatedData['position_subsidy'] ?? 0) > 0)
            <div class="flex justify-between items-center p-3 lg:p-4 bg-indigo-50 rounded-xl">
                <span class="font-medium text-indigo-700 text-sm lg:text-base">
                    <i class="fas fa-briefcase text-indigo-600 mr-1"></i>
                    {{ __('messages.position_subsidy') }}
                </span>
                <span class="font-bold text-indigo-800 text-sm lg:text-lg">+{{ number_format($calculatedData['position_subsidy'], 2) }} AOA</span>
            </div>
            @endif

            {{-- Performance Subsidy --}}
            @if(($calculatedData['performance_subsidy'] ?? 0) > 0)
            <div class="flex justify-between items-center p-3 lg:p-4 bg-green-50 rounded-xl">
                <span class="font-medium text-green-700 text-sm lg:text-base">
                    <i class="fas fa-chart-line text-green-600 mr-1"></i>
                    {{ __('messages.performance_subsidy') }}
                </span>
                <span class="font-bold text-green-800 text-sm lg:text-lg">+{{ number_format($calculatedData['performance_subsidy'], 2) }} AOA</span>
            </div>
            @endif

            {{-- Additional Payroll Bonus --}}
            @if($calculatedData['additional_bonus_amount'] > 0)
            <div class="flex justify-between items-center p-3 lg:p-4 bg-purple-50 rounded-xl">
                <span class="font-medium text-purple-700 text-sm lg:text-base">
                    <i class="fas fa-plus-circle text-purple-600 mr-1"></i>
                    Bónus Adicional do Payroll
                </span>
                <span class="font-bold text-purple-800 text-sm lg:text-lg">+{{ number_format($calculatedData['additional_bonus_amount'], 2) }} AOA</span>
            </div>
            @endif

            {{-- Overtime --}}
            @if($calculatedData['total_overtime_amount'] > 0)
            <div class="flex justify-between items-center p-3 lg:p-4 bg-orange-50 rounded-xl">
                <span class="font-medium text-orange-700 text-sm lg:text-base">
                    <i class="fas fa-clock text-orange-600 mr-1"></i>
                    Horas Extras
                </span>
                <span class="font-bold text-orange-800 text-sm lg:text-lg">+{{ number_format($calculatedData['total_overtime_amount'], 2) }} AOA</span>
            </div>
            @endif

            {{-- Divider --}}
            <hr class="border-gray-200">

            {{-- Main Salary (Base Salary + All Benefits including Food & Transport) - CÓPIA EXATA DA MODAL INDIVIDUAL --}}
            <div class="bg-blue-50 rounded-xl border border-blue-200 p-2 lg:p-3" x-data="{ showMainSalaryDetails: false }">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-2">
                        <span class="font-semibold text-blue-700 text-sm lg:text-base">{{ __('messages.gross_salary') }}</span>
                        <!-- Help Icon for Main Salary Details -->
                        <button @click="showMainSalaryDetails = !showMainSalaryDetails" 
                                class="w-4 h-4 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-blue-600 transition-colors"
                                type="button"
                                title="{{ __('payroll.view_main_salary_details') }}">
                            ?
                        </button>
                    </div>
                    <span class="text-lg lg:text-xl font-bold text-blue-800">
                        {{ number_format($calculatedData['gross_salary'], 2) }} AOA
                    </span>
                </div>
                
                <!-- Main Salary Details Breakdown -->
                <div x-show="showMainSalaryDetails" x-transition class="mt-3 p-3 bg-blue-100/50 rounded-lg border border-blue-200">
                    <h5 class="text-xs font-semibold text-blue-800 mb-2">{{ __('messages.main_salary_breakdown') }}:</h5>
                    <div class="space-y-1 text-xs">
                        <div class="flex justify-between">
                            <span class="text-blue-700">{{ __('messages.basic_salary') }}:</span>
                            <span class="font-medium text-blue-800">{{ number_format($calculatedData['basic_salary'] ?? 0, 2) }} AOA</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-blue-700">{{ __('messages.food_benefit') }}:</span>
                            <span class="font-medium text-blue-800">{{ number_format($calculatedData['food_benefit'] ?? 0, 2) }} AOA</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-blue-700">{{ __('messages.transport_benefit') }}:</span>
                            <span class="font-medium text-blue-800">{{ number_format($calculatedData['transport_allowance'] ?? 0, 2) }} AOA</span>
                        </div>
                        @if($calculatedData['total_overtime_amount'] > 0)
                        <div class="flex justify-between">
                            <span class="text-blue-700">{{ __('messages.overtime') }}:</span>
                            <span class="font-medium text-blue-800">{{ number_format($calculatedData['total_overtime_amount'] ?? 0, 2) }} AOA</span>
                        </div>
                        @endif
                        @if($calculatedData['bonus_amount'] > 0)
                        <div class="flex justify-between">
                            <span class="text-blue-700">{{ __('messages.bonus_amount') }}:</span>
                            <span class="font-medium text-blue-800">{{ number_format($calculatedData['bonus_amount'] ?? 0, 2) }} AOA</span>
                        </div>
                        @endif
                        @if(($calculatedData['position_subsidy'] ?? 0) > 0)
                        <div class="flex justify-between">
                            <span class="text-blue-700">{{ __('messages.position_subsidy') }}:</span>
                            <span class="font-medium text-blue-800">{{ number_format($calculatedData['position_subsidy'] ?? 0, 2) }} AOA</span>
                        </div>
                        @endif
                        @if(($calculatedData['performance_subsidy'] ?? 0) > 0)
                        <div class="flex justify-between">
                            <span class="text-blue-700">{{ __('messages.performance_subsidy') }}:</span>
                            <span class="font-medium text-blue-800">{{ number_format($calculatedData['performance_subsidy'] ?? 0, 2) }} AOA</span>
                        </div>
                        @endif
                        @if($calculatedData['additional_bonus_amount'] > 0)
                        <div class="flex justify-between">
                            <span class="text-blue-700">{{ __('messages.additional_bonus') }}:</span>
                            <span class="font-medium text-blue-800">{{ number_format($calculatedData['additional_bonus_amount'] ?? 0, 2) }} AOA</span>
                        </div>
                        @endif
                        @if($calculatedData['christmas_subsidy_amount'] > 0)
                        <div class="flex justify-between">
                            <span class="text-blue-700">{{ __('messages.christmas_subsidy') }}:</span>
                            <span class="font-medium text-blue-800">{{ number_format($calculatedData['christmas_subsidy_amount'] ?? 0, 2) }} AOA</span>
                        </div>
                        @endif
                        @if($calculatedData['vacation_subsidy_amount'] > 0)
                        <div class="flex justify-between">
                            <span class="text-blue-700">{{ __('messages.vacation_subsidy') }}:</span>
                            <span class="font-medium text-blue-800">{{ number_format($calculatedData['vacation_subsidy_amount'] ?? 0, 2) }} AOA</span>
                        </div>
                        @endif
                        <div class="border-t border-blue-300 pt-1 mt-1">
                            <div class="flex justify-between font-semibold">
                                <span class="text-blue-800">{{ __('messages.total') }}:</span>
                                <span class="text-blue-900">{{ number_format($calculatedData['gross_salary'], 2) }} AOA</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Base IRT Taxable Amount - CÓPIA EXATA DA MODAL INDIVIDUAL --}}
            <div class="bg-green-50 rounded-xl border border-green-200 p-2 lg:p-3" x-data="{ showGrossSalaryDetails: false }">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-2">
                        <span class="font-semibold text-green-700 text-sm lg:text-base">{{ __('messages.baseIRT_taxable_amount') }}</span>
                        <!-- Help Icon for Gross Salary Details -->
                        <button @click="showGrossSalaryDetails = !showGrossSalaryDetails" 
                                class="w-4 h-4 bg-green-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-green-600 transition-colors"
                                type="button"
                                title="{{ __('payroll.view_gross_salary_details') }}">
                            ?
                        </button>
                    </div>
                    <span class="text-lg lg:text-xl font-bold text-green-800">{{ number_format($calculatedData['irt_base'], 2) }} AOA</span>
                </div>
                
                <!-- Gross Salary Details Breakdown -->
                <div x-show="showGrossSalaryDetails" x-transition class="mt-3 p-3 bg-green-100/50 rounded-lg border border-green-200">
                    <h5 class="text-xs font-semibold text-green-800 mb-2">{{ __('messages.gross_salary_breakdown') }}:</h5>
                    <div class="space-y-1 text-xs">
                        <div class="flex justify-between">
                            <span class="text-green-700">{{ __('messages.basic_salary') }}:</span>
                            <span class="font-medium text-green-800">{{ number_format($calculatedData['basic_salary'] ?? 0, 2) }} AOA</span>
                        </div>
                        @if($calculatedData['taxable_transport'] > 0)
                        <div class="flex justify-between">
                            <span class="text-green-700">{{ __('messages.transport_benefit') }} <span class="text-xs text-gray-500">(excesso tributável)</span>:</span>
                            <span class="font-medium text-green-800">{{ number_format($calculatedData['taxable_transport'], 2) }} AOA</span>
                        </div>
                        @endif
                        @if($calculatedData['bonus_amount'] > 0)
                        <div class="flex justify-between">
                            <span class="text-green-700">{{ __('messages.bonus_amount') }} <span class="text-xs text-gray-500">(tributável)</span>:</span>
                            <span class="font-medium text-green-800">{{ number_format($calculatedData['bonus_amount'] ?? 0, 2) }} AOA</span>
                        </div>
                        @endif
                        @if($calculatedData['additional_bonus_amount'] > 0)
                        <div class="flex justify-between">
                            <span class="text-green-700">{{ __('messages.additional_bonus') }} <span class="text-xs text-gray-500">(tributável)</span>:</span>
                            <span class="font-medium text-green-800">{{ number_format($calculatedData['additional_bonus_amount'] ?? 0, 2) }} AOA</span>
                        </div>
                        @endif
                        @if($calculatedData['christmas_subsidy_amount'] > 0)
                        <div class="flex justify-between">
                            <span class="text-green-700">{{ __('messages.christmas_subsidy') }} <span class="text-xs text-gray-500">(tributável)</span>:</span>
                            <span class="font-medium text-green-800">{{ number_format($calculatedData['christmas_subsidy_amount'] ?? 0, 2) }} AOA</span>
                        </div>
                        @endif
                        @if($calculatedData['vacation_subsidy_amount'] > 0)
                        <div class="flex justify-between">
                            <span class="text-green-700">{{ __('messages.vacation_subsidy') }} <span class="text-xs text-gray-500">(tributável)</span>:</span>
                            <span class="font-medium text-green-800">{{ number_format($calculatedData['vacation_subsidy_amount'] ?? 0, 2) }} AOA</span>
                        </div>
                        @endif
                        @if($calculatedData['total_overtime_amount'] > 0)
                        <div class="flex justify-between">
                            <span class="text-green-700">{{ __('messages.overtime_amount') }} <span class="text-xs text-gray-500">(tributável)</span>:</span>
                            <span class="font-medium text-green-800">{{ number_format($calculatedData['total_overtime_amount'] ?? 0, 2) }} AOA</span>
                        </div>
                        @endif
                        @if($calculatedData['taxable_food'] > 0)
                        <div class="flex justify-between">
                            <span class="text-green-700">{{ __('messages.food_benefit') }} <span class="text-xs text-gray-500">(excesso tributável)</span>:</span>
                            <span class="font-medium text-green-800">{{ number_format($calculatedData['taxable_food'], 2) }} AOA</span>
                        </div>
                        @endif
                        <div class="flex justify-between border-t border-green-300 pt-1 mt-1">
                            <span class="text-green-700 font-medium">INSS (3%):</span>
                            <span class="font-medium text-red-600">-{{ number_format($calculatedData['inss_3_percent'], 2) }} AOA</span>
                        </div>
                        <div class="border-t border-green-300 pt-1 mt-1">
                            <div class="flex justify-between font-semibold">
                                <span class="text-green-800">{{ __('messages.total') }}:</span>
                                <span class="text-green-900">{{ number_format($calculatedData['irt_base'], 2) }} AOA</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Deductions Section - CÓPIA EXATA DA MODAL INDIVIDUAL --}}
            <div class="space-y-2">
                <h4 class="text-sm font-semibold text-gray-700 mb-2">{{ __('messages.deductions') }}:</h4>
                
                {{-- IRT --}}
                <div class="flex justify-between items-center p-2 lg:p-3 bg-red-50 rounded-lg">
                    <div>
                        <span class="font-medium text-red-700 text-xs lg:text-sm">IRT ({{ __('messages.income_tax') }})</span>
                        <div class="text-xs text-red-600 mt-1">{{ __('payroll.calculated_from') }}: {{ number_format($calculatedData['irt_base'] ?? 0, 2) }} AOA</div>
                    </div>
                    <span class="font-bold text-red-800 text-xs lg:text-sm">-{{ number_format($calculatedData['irt'] ?? 0, 2) }} AOA</span>
                </div>

                {{-- INSS 3% --}}
                <div class="flex justify-between items-center p-2 lg:p-3 bg-red-50 rounded-lg">
                    <span class="font-medium text-red-700 text-xs lg:text-sm">INSS (3%)</span>
                    <span class="font-bold text-red-800 text-xs lg:text-sm">-{{ number_format($calculatedData['inss_3_percent'] ?? 0, 2) }} AOA</span>
                </div>

                {{-- INSS 8% Illustrative --}}
                <div class="flex justify-between items-center p-2 lg:p-3 bg-orange-50 rounded-lg border border-orange-200">
                    <div>
                        <span class="font-medium text-orange-700 text-xs lg:text-sm">INSS (8%) - {{ __('payroll.illustrative_only') }}</span>
                        <div class="text-xs text-orange-500 mt-1">{{ __('payroll.calculated_gross_salary') }}</div>
                    </div>
                    <span class="font-bold text-orange-800 text-xs lg:text-sm">{{ number_format($calculatedData['inss_8_percent'] ?? 0, 2) }} AOA</span>
                </div>

                {{-- Salary Advances --}}
                @if(($calculatedData['advance_deduction'] ?? 0) > 0)
                <div class="flex justify-between items-center p-2 lg:p-3 bg-red-50 rounded-lg">
                    <span class="font-medium text-red-700 text-xs lg:text-sm">{{ __('messages.salary_advances') }}</span>
                    <span class="font-bold text-red-800 text-xs lg:text-sm">-{{ number_format($calculatedData['advance_deduction'] ?? 0, 2) }} AOA</span>
                </div>
                @endif

                {{-- Salary Discounts --}}
                @if(($calculatedData['total_salary_discounts'] ?? 0) > 0)
                <div class="flex justify-between items-center p-2 lg:p-3 bg-red-50 rounded-lg">
                    <span class="font-medium text-red-700 text-xs lg:text-sm">{{ __('messages.salary_discounts') }}</span>
                    <span class="font-bold text-red-800 text-xs lg:text-sm">-{{ number_format($calculatedData['total_salary_discounts'] ?? 0, 2) }} AOA</span>
                </div>
                @endif

                {{-- Late Arrival Deductions --}}
                @if(($calculatedData['late_deduction'] ?? 0) > 0)
                <div class="flex justify-between items-center p-2 lg:p-3 bg-yellow-50 rounded-lg">
                    <span class="font-medium text-yellow-700 text-xs lg:text-sm">
                        <i class="fas fa-clock mr-1"></i>
                        {{ __('payroll.late_arrival_discount') }} ({{ $calculatedData['late_arrivals'] ?? 0 }} {{ __('payroll.days') }})
                    </span>
                    <span class="font-bold text-yellow-800 text-xs lg:text-sm">-{{ number_format($calculatedData['late_deduction'], 2) }} AOA</span>
                </div>
                @endif
                
                {{-- Absence Deductions --}}
                <div class="flex justify-between items-center p-2 lg:p-3 bg-orange-50 rounded-lg">
                    <span class="font-medium text-orange-700 text-xs lg:text-sm">
                        <i class="fas fa-calendar-times mr-1"></i>
                        {{ __('payroll.absence_discount') }} ({{ $calculatedData['absent_days'] ?? 0 }} {{ __('payroll.days') }})
                    </span>
                    <span class="font-bold text-orange-800 text-xs lg:text-sm">-{{ number_format($calculatedData['absence_deduction'] ?? 0, 2) }} AOA</span>
                </div>

                {{-- Main Salary - CÓPIA EXATA DA MODAL INDIVIDUAL --}}
                <div class="bg-yellow-50 rounded-xl border border-yellow-200 p-2 lg:p-3 mt-4">
                    <div class="flex justify-between items-center">
                        <span class="font-semibold text-yellow-700 text-sm lg:text-base">{{ __('messages.main_salary') }}</span>
                        <span class="text-lg lg:text-xl font-bold text-yellow-800">{{ number_format($calculatedData['main_salary'], 2) }} AOA</span>
                    </div>
                </div>

                {{-- Total Deductions --}}
                <div class="flex justify-between items-center p-3 lg:p-4 bg-red-50 rounded-xl border border-red-200">
                    <span class="font-semibold text-red-700 text-sm lg:text-base">{{ __('messages.total_deductions') }}</span>
                    <span class="text-lg lg:text-xl font-bold text-red-800">-{{ number_format($calculatedData['total_deductions'], 2) }} AOA</span>
                </div>

                {{-- Net Salary --}}
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border-2 border-blue-200 shadow-sm p-2 lg:p-3">
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-blue-700 text-base lg:text-lg flex items-center">
                            <i class="fas fa-wallet text-blue-600 mr-2"></i>
                            {{ __('messages.net_salary') }}
                        </span>
                        <span class="text-xl lg:text-2xl font-bold text-blue-800">{{ number_format($calculatedData['net_salary'], 2) }} AOA</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Right Panel - Employee Data (LADO DIREITO) --}}
<div class="w-full xl:w-1/2 bg-white overflow-y-auto flex-1 p-4 lg:p-6 order-2">
    
    {{-- Employee Basic Info Card --}}
    <div class="bg-white rounded-xl border border-blue-200 p-5 shadow-sm">
        <h3 class="text-lg font-bold text-blue-800 mb-4 flex items-center">
            <i class="fas fa-id-card mr-2"></i>
            Informações do Funcionário
        </h3>
        <div class="grid grid-cols-2 gap-3 text-sm">
            <div>
                <span class="text-gray-600">Salário Base:</span>
                <span class="font-bold text-gray-900 ml-2">{{ number_format($calculatedData['basic_salary'], 2) }} AOA</span>
            </div>
            <div>
                <span class="text-gray-600">Taxa Horária:</span>
                <span class="font-bold text-gray-900 ml-2">{{ number_format($calculatedData['hourly_rate'], 2) }} AOA/h</span>
            </div>
            <div>
                <span class="text-gray-600">Taxa Diária:</span>
                <span class="font-bold text-gray-900 ml-2">{{ number_format($calculatedData['daily_rate'], 2) }} AOA/dia</span>
            </div>
            <div>
                <span class="text-gray-600">Dias Úteis:</span>
                <span class="font-bold text-gray-900 ml-2">{{ $calculatedData['monthly_working_days'] }} dias</span>
            </div>
        </div>
    </div>

    {{-- Attendance Summary Card --}}
    <div class="bg-gradient-to-br from-green-50 to-emerald-100 p-6 rounded-2xl border border-green-200 mt-6">
        <h3 class="text-lg font-bold text-green-800 mb-4 flex items-center">
            <i class="fas fa-clock mr-2"></i>
            Resumo de Presença
        </h3>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white/60 p-3 rounded-lg text-center">
                <p class="text-xs text-green-600 font-medium mb-1">Horas Trabalhadas</p>
                <p class="text-lg text-green-800 font-bold">{{ number_format($calculatedData['attendance_hours'], 1) }}h</p>
            </div>
            <div class="bg-white/60 p-3 rounded-lg text-center">
                <p class="text-xs text-green-600 font-medium mb-1">Dias Presentes</p>
                <p class="text-lg text-green-800 font-bold">{{ $calculatedData['present_days'] }}</p>
            </div>
            <div class="bg-white/60 p-3 rounded-lg text-center">
                <p class="text-xs text-orange-600 font-medium mb-1">Faltas</p>
                <p class="text-lg text-orange-600 font-bold">{{ $calculatedData['absent_days'] }}</p>
            </div>
            <div class="bg-white/60 p-3 rounded-lg text-center">
                <p class="text-xs text-yellow-600 font-medium mb-1">Atrasos</p>
                <p class="text-lg text-yellow-600 font-bold">{{ $calculatedData['late_days'] }}</p>
            </div>
        </div>
    </div>

    {{-- Overtime Card --}}
    @if($calculatedData['total_overtime_hours'] > 0)
    <div class="bg-gradient-to-br from-orange-50 to-amber-100 p-6 rounded-2xl border border-orange-200 mt-6">
        <h3 class="text-lg font-bold text-orange-800 mb-4 flex items-center">
            <i class="fas fa-clock mr-2"></i>
            Horas Extras
        </h3>
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white/60 p-3 rounded-lg text-center">
                <p class="text-xs text-orange-600 font-medium mb-1">Total de Horas</p>
                <p class="text-lg text-orange-800 font-bold">{{ number_format($calculatedData['total_overtime_hours'], 1) }}h</p>
            </div>
            <div class="bg-white/60 p-3 rounded-lg text-center">
                <p class="text-xs text-orange-600 font-medium mb-1">Valor Total</p>
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
