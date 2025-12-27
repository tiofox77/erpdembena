<!-- Modal de Visualização de Overtime Night Shift -->
<div x-data="{ open: @entangle('showViewModal') }" 
     x-show="open" 
     x-cloak 
     class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50" 
     role="dialog" 
     aria-modal="true"
     x-transition:enter="transition ease-out duration-300" 
     x-transition:enter-start="opacity-0" 
     x-transition:enter-end="opacity-100" 
     x-transition:leave="transition ease-in duration-200" 
     x-transition:leave-start="opacity-100" 
     x-transition:leave-end="opacity-0">
    
    <div class="flex items-center justify-center min-h-screen px-4">
        <div x-show="open" 
             class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-4xl sm:w-full"
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="transform opacity-0 scale-95" 
             x-transition:enter-end="transform opacity-100 scale-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="transform opacity-100 scale-100" 
             x-transition:leave-end="transform opacity-0 scale-95">
            
            <!-- Cabeçalho da Modal -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-700 rounded-t-lg px-6 py-4 flex justify-between items-center">
                <h3 class="text-xl font-medium text-white flex items-center">
                    <i class="fas fa-moon mr-3"></i>
                    {{ __('messages.view') }} {{ __('messages.night_allowance') }}
                </h3>
                <button type="button" wire:click="closeViewModal" 
                        class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Corpo da Modal -->
            <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
                
                <!-- Informações do Funcionário -->
                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                    <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                        <i class="fas fa-user text-blue-600 mr-2"></i>
                        <h3 class="text-base font-medium text-gray-700">{{ __('messages.employee_information') }}</h3>
                    </div>
                    <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h4 class="text-xs font-medium text-gray-500">{{ __('messages.employee') }}</h4>
                            <p class="text-sm font-medium text-gray-800">{{ $employee_name }}</p>
                        </div>
                        <div>
                            <h4 class="text-xs font-medium text-gray-500">{{ __('messages.date') }}</h4>
                            <p class="text-sm font-medium text-gray-800">{{ $date ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Detalhes do Overtime Night Shift -->
                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                    <div class="flex items-center bg-gradient-to-r from-purple-50 to-purple-100 px-4 py-3 border-b border-gray-200">
                        <i class="fas fa-moon text-purple-600 mr-2"></i>
                        <h3 class="text-base font-medium text-gray-700">{{ __('messages.night_allowance') }} {{ __('messages.details') }}</h3>
                    </div>
                    <div class="p-4 space-y-4">
                        <!-- Calculation Breakdown -->
                        <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-xl p-4 border border-purple-200">
                            <div class="flex items-center mb-3">
                                <div class="bg-purple-200 rounded-full p-2 mr-2">
                                    <i class="fas fa-chart-line text-purple-700"></i>
                                </div>
                                <h4 class="text-sm font-bold text-purple-900">{{ __('messages.calculation_breakdown') }}</h4>
                            </div>
                            
                            <div class="space-y-2">
                                <!-- Dias Trabalhados -->
                                <div class="flex items-center justify-between bg-white bg-opacity-60 rounded-lg p-2">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar-day text-blue-600 mr-2"></i>
                                        <span class="text-xs text-gray-700">{{ __('messages.days_worked') }}:</span>
                                    </div>
                                    <span class="text-sm font-bold text-gray-900">{{ number_format($direct_hours ?? 0, 1) }} {{ __('messages.days') }}</span>
                                </div>

                                <!-- Taxa Diária -->
                                <div class="flex items-center justify-between bg-white bg-opacity-60 rounded-lg p-2">
                                    <div class="flex items-center">
                                        <i class="fas fa-coins text-yellow-600 mr-2"></i>
                                        <span class="text-xs text-gray-700">{{ __('messages.daily_rate') }}:</span>
                                    </div>
                                    <span class="text-sm font-bold text-gray-900">{{ number_format($rate ?? 0, 2) }} KZ/dia</span>
                                </div>

                                <!-- Subtotal -->
                                <div class="flex items-center justify-between bg-blue-100 rounded-lg p-2">
                                    <div class="flex items-center">
                                        <i class="fas fa-calculator text-blue-600 mr-2"></i>
                                        <span class="text-xs font-semibold text-blue-900">{{ __('messages.subtotal') }}:</span>
                                    </div>
                                    <span class="text-sm font-bold text-blue-900">
                                        {{ number_format(($direct_hours ?? 0) * ($rate ?? 0), 2) }} KZ
                                        <span class="text-xs text-blue-600 ml-1">({{ number_format($direct_hours ?? 0, 1) }} × {{ number_format($rate ?? 0, 2) }})</span>
                                    </span>
                                </div>

                                <!-- Night Allowance (20%) -->
                                <div class="flex items-center justify-between bg-purple-100 rounded-lg p-2">
                                    <div class="flex items-center">
                                        <i class="fas fa-moon text-purple-600 mr-2"></i>
                                        <span class="text-xs font-semibold text-purple-900">{{ __('messages.night_shift_bonus') }} (20%):</span>
                                    </div>
                                    <span class="text-sm font-bold text-purple-900">{{ number_format($amount ?? 0, 2) }} KZ</span>
                                </div>

                                <!-- Info -->
                                <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-2">
                                    <div class="flex items-center text-xs text-indigo-700">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        <span>{{ __('messages.night_shift_payment_info') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($description)
                            <div>
                                <h4 class="text-xs font-medium text-gray-500">{{ __('messages.description') }}</h4>
                                <p class="text-sm text-gray-800">{{ $description }}</p>
                            </div>
                        @endif

                        <div>
                            <h4 class="text-xs font-medium text-gray-500">{{ __('messages.status') }}</h4>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($status === 'approved') bg-green-100 text-green-800
                                @elseif($status === 'rejected') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ __(ucfirst($status)) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Informações de Auditoria -->
                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                    <div class="flex items-center bg-gradient-to-r from-gray-50 to-gray-100 px-4 py-3 border-b border-gray-200">
                        <i class="fas fa-info-circle text-gray-600 mr-2"></i>
                        <h3 class="text-base font-medium text-gray-700">{{ __('messages.audit_information') }}</h3>
                    </div>
                    <div class="p-4 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <h4 class="text-xs font-medium text-gray-500">{{ __('messages.created_by') }}</h4>
                                <p class="text-sm font-medium text-gray-800">{{ $creator_name ?? '-' }}</p>
                            </div>
                            <div>
                                <h4 class="text-xs font-medium text-gray-500">{{ __('messages.created_at') }}</h4>
                                <p class="text-sm font-medium text-gray-800">{{ $created_at ? \Carbon\Carbon::parse($created_at)->format('d/m/Y H:i') : '-' }}</p>
                            </div>
                            <div>
                                <h4 class="text-xs font-medium text-gray-500">{{ __('messages.updated_at') }}</h4>
                                <p class="text-sm font-medium text-gray-800">{{ $updated_at ? \Carbon\Carbon::parse($updated_at)->format('d/m/Y H:i') : '-' }}</p>
                            </div>
                        </div>

                        @if($status !== 'pending' && $approver_name)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t">
                                <div>
                                    <h4 class="text-xs font-medium text-gray-500">{{ __('messages.approved_by') }}</h4>
                                    <p class="text-sm font-medium text-gray-800">{{ $approver_name }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            <!-- Rodapé da Modal -->
            <div class="bg-gray-50 px-6 py-4 rounded-b-lg flex justify-end border-t border-gray-200">
                <button type="button" wire:click="closeViewModal" 
                    class="inline-flex justify-center items-center px-6 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200">
                    <i class="fas fa-times mr-2"></i>
                    {{ __('messages.close') }}
                </button>
            </div>

        </div>
    </div>
</div>
