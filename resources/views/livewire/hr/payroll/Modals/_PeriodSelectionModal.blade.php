{{-- Modal: Payroll Period Selection (DO NOT RENAME) --}}
@if($showPeriodSelection)
<div class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-50 p-4"
     x-data="{ show: @entangle('showPeriodSelection').live }"
     x-show="show"
     style="display: none;"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
     
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col"
         @click.stop>
        
        <!-- Header com Gradiente -->
        <div class="bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-800 px-6 py-6 text-white rounded-t-2xl">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4 flex-1 min-w-0">
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3 flex-shrink-0">
                        <i class="fas fa-calendar-alt text-2xl"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-xl lg:text-2xl font-bold truncate">
                            {{ __('payroll.select_period_title') }}
                        </h3>
                        <p class="text-blue-100 text-sm mt-1 truncate">
                            {{ __('payroll.select_period_subtitle') }}
                        </p>
                    </div>
                </div>
                <button wire:click="closePeriodSelection" 
                        class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg p-2 transition-all flex-shrink-0 ml-2">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Conteúdo Principal -->
        <div class="flex-1 p-6 overflow-y-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                @forelse($payrollPeriods as $period)
                    <div class="group relative bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-xl p-6 hover:shadow-lg hover:border-blue-300 cursor-pointer transition-all duration-300 transform hover:-translate-y-1"
                         wire:click="selectPayrollPeriod({{ $period->id }})">
                        
                        <!-- Period Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                                        <i class="fas fa-calendar-alt text-blue-600"></i>
                                    </div>
                                    <h4 class="font-bold text-lg text-gray-900 group-hover:text-blue-700 transition-colors">{{ $period->name }}</h4>
                                </div>
                                
                                <!-- Period Dates -->
                                <div class="space-y-2">
                                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                                        <div class="w-2 h-2 bg-blue-400 rounded-full"></div>
                                        <i class="fas fa-calendar-day text-xs"></i>
                                        <span class="font-medium">{{ \Carbon\Carbon::parse($period->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($period->end_date)->format('d/m/Y') }}</span>
                                    </div>
                                    @if($period->payment_date)
                                        <div class="flex items-center space-x-2 text-sm text-gray-600">
                                            <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                                            <i class="fas fa-money-bill-wave text-xs"></i>
                                            <span>{{ __('payroll.payment') }}: <span class="font-medium">{{ \Carbon\Carbon::parse($period->payment_date)->format('d/m/Y') }}</span></span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Status Badge -->
                            <div class="flex-shrink-0">
                                @if($period->status === 'open')
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-200">
                                        <div class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></div>
                                        {{ __('payroll.status_open') }}
                                    </span>
                                @elseif($period->status === 'processing')
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 border border-yellow-200">
                                        <div class="w-2 h-2 bg-yellow-500 rounded-full mr-2 animate-pulse"></div>
                                        {{ __('payroll.status_processing') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-200">
                                        <i class="fas fa-lock text-xs mr-2"></i>
                                        {{ __('payroll.status_closed') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        @if($period->remarks)
                            <div class="bg-blue-50 border-l-4 border-blue-400 p-3 rounded-r-lg mt-4">
                                <div class="flex items-start space-x-2">
                                    <i class="fas fa-info-circle text-blue-500 text-sm mt-0.5"></i>
                                    <p class="text-sm text-blue-700 font-medium">{{ $period->remarks }}</p>
                                </div>
                            </div>
                        @endif
                        
                        <!-- Hover Effect Indicator -->
                        <div class="absolute inset-0 border-2 border-transparent group-hover:border-blue-400 rounded-xl transition-all duration-300 pointer-events-none"></div>
                        <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <i class="fas fa-arrow-right text-blue-500"></i>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full">
                        <div class="text-center py-12">
                            <div class="bg-gradient-to-br from-gray-100 to-gray-200 w-24 h-24 mx-auto mb-6 rounded-full flex items-center justify-center">
                                <i class="fas fa-calendar-times text-4xl text-gray-400"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-3">
                                {{ __('payroll.no_periods_available') }}
                            </h3>
                            <p class="text-gray-600 mb-6 max-w-md mx-auto leading-relaxed">
                                {{ __('payroll.create_period_first') }}
                            </p>
                            <a href="{{ route('hr.payroll-periods') }}" 
                               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-blue-800 transform hover:scale-105 transition-all duration-300 shadow-lg">
                                <i class="fas fa-plus mr-2"></i>
                                {{ __('payroll.create_period') }}
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Footer Moderno -->
        @if($payrollPeriods->count() > 0)
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-5 border-t border-gray-200 rounded-b-2xl">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                        <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                        <i class="fas fa-info-circle"></i>
                        <span class="font-medium">{{ __('payroll.select_period_help') }}</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button wire:click="closePeriodSelection" 
                                class="px-6 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 hover:border-gray-400 transition-all duration-300 font-medium">
                            <i class="fas fa-times mr-2"></i>
                            {{ __('common.cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endif
