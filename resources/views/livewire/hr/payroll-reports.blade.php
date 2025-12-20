{{-- Modern Full Width Payroll Reports Interface --}}
<div class="min-h-screen bg-gray-50">
    <div class="w-full h-full">
        <div class="flex flex-col min-h-screen">
            
            {{-- Header Section with Gradient - Full Width --}}
            <div class="bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 px-6 py-8 text-white flex-shrink-0">
                <div class="w-full flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="bg-white/20 backdrop-blur-sm rounded-lg p-3">
                            <i class="fas fa-chart-line text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold">{{ __("messages.payroll_reports") }}</h1>
                            <p class="text-sm text-gray-200 mt-1">
                                {{ __("messages.consult_processed_payments_period") }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filters Section - Full Width --}}
            <div class="bg-white border-b border-gray-200 px-6 py-6 flex-shrink-0">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-6 border border-gray-200">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-blue-50">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-filter text-blue-600 text-lg"></i>
                            <span class="text-lg font-semibold text-gray-800">{{ __("messages.filters_and_search") }}</span>
                        </div>
                        <button
                            wire:click="clearFilters"
                            class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-colors flex items-center space-x-2"
                        >
                            <i class="fas fa-undo text-sm"></i>
                            <span class="text-sm font-medium">{{ __("messages.reset") }}</span>
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {{-- Year Filter --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt text-gray-400 mr-1"></i>
                                {{ __("messages.year") }}
                            </label>
                            <select 
                                wire:model.live="selectedYear"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                            >
                                @foreach($availableYears as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
            
                        {{-- Search --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-search text-gray-400 mr-1"></i>
                                {{ __("messages.search_period") }}
                            </label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    wire:model.live.debounce.300ms="search"
                                    placeholder="{{ __("messages.period_name") }}"
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                >
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Department Filter --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-building text-gray-400 mr-1"></i>
                                {{ __("messages.department") }}
                            </label>
                            <select 
                                wire:model.live="selectedDepartment"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                            >
                                <option value="">{{ __("messages.all_departments") }}</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Listagem de Períodos com Pagamentos --}}
            @if(count($periodsWithTotals) > 0)
            <div class="flex-1 bg-gray-50 px-6 py-6 overflow-y-auto">
                <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200">
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                        <div class="flex items-center space-x-2 mb-4">
                            <i class="fas fa-calendar-check text-blue-600 text-xl"></i>
                            <span class="text-xl font-bold text-gray-900">{{ __("messages.payments_by_period") }}</span>
                            <span class="ml-2 px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold">{{ $periodsWithTotals->count() }} {{ $periodsWithTotals->count() === 1 ? __("messages.period") : __("messages.periods") }}</span>
                        </div>
                    </div>
                
                <div class="p-6 grid grid-cols-1 gap-4">
                    @foreach($periodsWithTotals as $periodData)
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border-2 border-blue-200 hover:border-blue-400 hover:shadow-lg transition-all">
                                <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                                    <div>
                                        <div class="flex items-center mb-2">
                                            <i class="fas fa-calendar text-blue-600 mr-2 text-xl"></i>
                                            <span class="text-gray-900 font-bold text-lg">{{ $periodData['period']->name }}</span>
                                        </div>
                                        <div class="text-gray-600 text-sm font-medium">
                                            {{ $periodData['period']->start_date->format('d/m/Y') }} - {{ $periodData['period']->end_date->format('d/m/Y') }}
                                        </div>
                                        <div class="mt-3 flex items-center space-x-2">
                                            <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">
                                                <i class="fas fa-users mr-1"></i>
                                                {{ $periodData['total_employees'] }} {{ __("messages.employees") }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="text-center bg-white rounded-lg p-4 shadow-sm">
                                        <div class="text-gray-500 text-xs font-medium uppercase mb-1">{{ __("messages.salary_gross") }}</div>
                                        <div class="text-2xl font-bold text-green-600">
                                            {{ number_format($periodData['gross_total'], 2, ',', '.') }}
                                        </div>
                                        <div class="text-gray-400 text-xs mt-1">AOA</div>
                                    </div>
                                    
                                    <div class="text-center bg-white rounded-lg p-4 shadow-sm">
                                        <div class="text-gray-500 text-xs font-medium uppercase mb-1">{{ __("messages.deductions") }}</div>
                                        <div class="text-2xl font-bold text-red-600">
                                            {{ number_format($periodData['deductions_total'], 2, ',', '.') }}
                                        </div>
                                        <div class="text-gray-400 text-xs mt-1">AOA</div>
                                    </div>
                                    
                                    <div class="text-center bg-white rounded-lg p-4 shadow-sm">
                                        <div class="text-gray-500 text-xs font-medium uppercase mb-1">{{ __("messages.salary_liquid") }}</div>
                                        <div class="text-2xl font-bold text-blue-700">
                                            {{ number_format($periodData['net_total'], 2, ',', '.') }}
                                        </div>
                                        <div class="text-gray-400 text-xs mt-1">AOA</div>
                                    </div>
                                    
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        @php
                                            $hasPayments = $periodData['total_employees'] > 0;
                                            $isClosed = $periodData['period']->status === 'closed';
                                        @endphp
                                        <span class="px-4 py-2 {{ $hasPayments ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }} rounded-lg text-sm font-semibold">
                                            @if($isClosed)
                                                ✓ {{ __('messages.status_closed') }}
                                            @elseif($hasPayments)
                                                ✓ {{ __('messages.status_processed') }}
                                            @else
                                                ○ {{ __('messages.status_open') }}
                                            @endif
                                        </span>
                                        
                                        {{-- Relatório Consolidado do Período --}}
                                        <button 
                                            wire:click="generatePeriodReport({{ $periodData['period']->id }})"
                                            class="w-full px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white text-sm font-medium rounded-lg shadow-sm transition-all duration-300 hover:scale-105 hover:shadow-md flex items-center justify-center"
                                        >
                                            <i class="fas fa-chart-bar mr-2"></i>
                                            {{ __('messages.consolidated_report_button') }}
                                        </button>
                                        
                                        {{-- Relatório Detalhado do Batch --}}
                                        <button 
                                            wire:click="generateBatchReportForPeriod({{ $periodData['period']->id }})"
                                            class="w-full px-4 py-2 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white text-sm font-medium rounded-lg shadow-sm transition-all duration-300 hover:scale-105 hover:shadow-md flex items-center justify-center"
                                        >
                                            <i class="fas fa-file-invoice-dollar mr-2"></i>
                                            {{ __('messages.detailed_report_button') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                    @endforeach
                </div>
                </div>
            </div>
            @else
                <div class="flex-1 bg-gray-50 px-6 py-6 overflow-y-auto">
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200">
                        <div class="p-16 text-center">
                            <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full mb-6">
                                <i class="fas fa-inbox text-4xl text-gray-400"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-3">{{ __("messages.no_payments_found") }}</h3>
                            <p class="text-gray-600 mb-2">{{ __("messages.no_payments_for_year") }}</p>
                            <p class="text-gray-500 text-sm">{{ __("messages.process_payrolls_first", ['section' => '<span class="font-semibold text-blue-600">' . __("messages.payroll_section") . '</span>']) }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Flash Messages --}}
            @if (session()->has('error'))
                <div class="fixed bottom-4 right-4 bg-red-100 border-l-4 border-red-500 p-4 rounded-lg shadow-lg z-50 animate-slide-up">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                        <p class="text-red-700 font-medium">{{ session('error') }}</p>
                    </div>
                </div>
            @endif
            
            @if (session()->has('message'))
                <div class="fixed bottom-4 right-4 bg-green-100 border-l-4 border-green-500 p-4 rounded-lg shadow-lg z-50 animate-slide-up">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-3"></i>
                        <p class="text-green-700 font-medium">{{ session('message') }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
