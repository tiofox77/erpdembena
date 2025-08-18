<!-- Modal de Registro de Pagamento -->
<div>
    <div x-data="{ open: @entangle('showPaymentModal') }" 
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
        <div class="relative top-20 mx-auto p-1 w-full max-w-2xl">
            <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" 
                 x-transition:enter="transition ease-out duration-300" 
                 x-transition:enter-start="transform opacity-0 scale-95" 
                 x-transition:enter-end="transform opacity-100 scale-100" 
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="transform opacity-100 scale-100" 
                 x-transition:leave-end="transform opacity-0 scale-95">
                
                <!-- Cabeçalho com gradiente -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-hand-holding-usd mr-2 animate-pulse"></i>
                        {{ __('messages.register_payment') }}
                    </h3>
                    <button type="button" wire:click="closePaymentModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form wire:submit.prevent="savePayment">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="mb-6 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 transition-all duration-200 ease-in-out hover:shadow-md">
                            <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                <h2 class="text-base font-medium text-gray-700">{{ __('messages.advance_summary') }}</h2>
                            </div>
                            
                            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="text-xs font-medium text-gray-500">{{ __('messages.employee') }}</h4>
                                    <p class="text-sm font-medium text-gray-800">{{ $paymentAdvance?->employee?->full_name ?? '-' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-xs font-medium text-gray-500">{{ __('messages.total_amount') }}</h4>
                                    <p class="text-sm font-medium text-gray-800">{{ number_format($paymentAdvance?->amount ?? 0, 2) }} Kz</p>
                                </div>
                                <div>
                                    <h4 class="text-xs font-medium text-gray-500">{{ __('messages.remaining_amount') }}</h4>
                                    <p class="text-sm font-medium text-gray-800">{{ number_format($paymentAdvance?->remaining_amount ?? 0, 2) }} Kz</p>
                                </div>
                                <div>
                                    <h4 class="text-xs font-medium text-gray-500">{{ __('messages.remaining_installments') }}</h4>
                                    <p class="text-sm font-medium text-gray-800">{{ $paymentAdvance?->remaining_installments ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-6 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 transition-all duration-200 ease-in-out hover:shadow-md">
                            <div class="flex items-center bg-gradient-to-r from-green-50 to-green-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-money-bill-wave text-green-600 mr-2"></i>
                                <h2 class="text-base font-medium text-gray-700">{{ __('messages.payment_details') }}</h2>
                            </div>
                            
                            <div class="p-4 grid grid-cols-1 gap-4">
                                <!-- Data de Pagamento -->
                                <div>
                                    <label for="payment_date" class="block text-sm font-medium text-gray-700">{{ __('messages.payment_date') }} *</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-calendar-alt text-gray-400"></i>
                                        </div>
                                        <input type="date" id="payment_date" wire:model="payment_date" 
                                            placeholder="{{ __('messages.select_date') }}"
                                            class="pl-10 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white">
                                    </div>
                                    @error('payment_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <!-- Tipo de Pagamento -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-3">{{ __('messages.payment_type') }}</label>
                                    <div class="space-y-3">
                                        <!-- Parcela Regular -->
                                        <div class="relative">
                                            <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition-all duration-200"
                                                :class="$wire.payment_type === 'installment' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
                                                <input wire:model.live="payment_type" value="installment" type="radio" 
                                                    class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500 mr-3" 
                                                    name="payment_type" />
                                                <div class="flex items-center w-full">
                                                    <div class="flex-1">
                                                        <div class="text-sm font-medium text-gray-900">{{ __('messages.regular_installment') }}</div>
                                                        <div class="text-sm text-gray-500">{{ number_format($paymentAdvance?->installment_amount ?? 0, 2) }} Kz</div>
                                                    </div>
                                                    <div class="ml-auto">
                                                        <i class="fas fa-calendar-check text-blue-500"></i>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                        
                                        
                                        <!-- Pagamento Completo -->
                                        <div class="relative">
                                            <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition-all duration-200"
                                                :class="$wire.payment_type === 'full' ? 'border-green-500 bg-green-50' : 'border-gray-200'">
                                                <input wire:model.live="payment_type" value="full" type="radio" 
                                                    class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500 mr-3" 
                                                    name="payment_type" />
                                                <div class="flex items-center w-full">
                                                    <div class="flex-1">
                                                        <div class="text-sm font-medium text-gray-900">{{ __('messages.pay_full_remaining') }}</div>
                                                        <div class="text-sm text-gray-500">{{ number_format($paymentAdvance?->remaining_amount ?? 0, 2) }} Kz</div>
                                                    </div>
                                                    <div class="ml-auto">
                                                        <i class="fas fa-money-check-alt text-green-500"></i>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    @error('payment_type') 
                                        <p class="mt-2 text-sm text-red-600 flex items-center">
                                            <i class="fas fa-exclamation-triangle mr-2"></i>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                                
                                <!-- Observações -->
                                <div>
                                    <label for="payment_notes" class="block text-sm font-medium text-gray-700">{{ __('messages.notes') }}</label>
                                    <textarea id="payment_notes" wire:model="payment_notes" rows="2" 
                                        placeholder="{{ __('messages.enter_payment_notes') }}"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white"></textarea>
                                    @error('payment_notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <!-- Debug Info -->
                                <div class="border border-blue-200 rounded-md p-3 bg-blue-50 mt-2">
                                    <h4 class="text-sm font-medium text-blue-700 mb-2">Debug Info</h4>
                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        <div class="text-blue-600">Payment Type:</div>
                                        <div class="text-blue-900 font-medium">{{ $payment_type ?? 'null' }}</div>
                                        
                                        <div class="text-blue-600">Payment Amount:</div>
                                        <div class="text-blue-900 font-medium">{{ $payment_amount ?? 'null' }} ({{ gettype($payment_amount) }})</div>
                                        
                                        <div class="text-blue-600">Payment Amount Raw:</div>
                                        <div class="text-blue-900 font-medium">{{ var_export($payment_amount, true) }}</div>
                                        
                                        <div class="text-blue-600">Modal Open:</div>
                                        <div class="text-blue-900 font-medium">{{ $showPaymentModal ? 'true' : 'false' }}</div>
                                    </div>
                                </div>
                                
                                <!-- Resumo do Pagamento -->
                                <div class="border border-gray-200 rounded-md p-3 bg-gray-50 mt-2">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">{{ __('messages.payment_summary') }}</h4>
                                    <div class="grid grid-cols-2 gap-2 text-sm">
                                        <div class="text-gray-600">{{ __('messages.payment_amount') }}:</div>
                                        <div class="text-gray-900 font-medium">{{ number_format($payment_amount ?? 0, 2) }} Kz</div>
                                        
                                        <div class="text-gray-600">{{ __('messages.remaining_after_payment') }}:</div>
                                        <div class="text-gray-900 font-medium">{{ number_format(($paymentAdvance?->remaining_amount ?? 0) - ($payment_amount ?? 0), 2) }} Kz</div>
                                        
                                        <div class="text-gray-600">{{ __('messages.status_after_payment') }}:</div>
                                        <div class="text-gray-900 font-medium">
                                            @if(($paymentAdvance?->remaining_amount ?? 0) - ($payment_amount ?? 0) <= 0)
                                                <span class="text-green-600">{{ __('messages.completed') }}</span>
                                            @else
                                                <span class="text-blue-600">{{ __('messages.in_progress') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                        <button type="button" wire:click="closePaymentModal" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                            <i class="fas fa-times mr-2"></i>
                            {{ __('messages.cancel') }}
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="savePayment">
                                <i class="fas fa-save mr-2"></i>
                                {{ __('messages.register_payment') }}
                            </span>
                            <span wire:loading wire:target="savePayment" class="inline-flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ __('messages.processing') }}
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
