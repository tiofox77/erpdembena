<!-- Modal de Registro de Pagamento -->
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
            
            <!-- Cabeçalho -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-dollar-sign mr-2"></i>
                    {{ __('messages.register_payment') }}
                </h3>
                <button wire:click="closePaymentModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Conteúdo -->
            <div class="p-6">
                @if($paymentDiscount)
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                            <div>
                                <h4 class="font-medium text-blue-900">{{ __('messages.discount_information') }}</h4>
                                <p class="text-sm text-blue-800">
                                    {{ __('messages.employee') }}: {{ $paymentDiscount->employee->full_name ?? 'N/A' }}
                                </p>
                                <p class="text-sm text-blue-800">
                                    {{ __('messages.remaining_amount') }}: {{ number_format($paymentDiscount->remaining_amount ?? 0, 2) }} Kz
                                </p>
                                <p class="text-sm text-blue-800">
                                    {{ __('messages.remaining_installments') }}: {{ $paymentDiscount->remaining_installments ?? 0 }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
                
                <form wire:submit.prevent="processPayment" class="space-y-6">
                    <!-- Data de Pagamento -->
                    <div>
                        <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-day mr-1 text-gray-400"></i>
                            {{ __('messages.payment_date') }} *
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-calendar-day text-gray-400"></i>
                            </div>
                            <input type="date" id="payment_date" wire:model="payment_date" 
                                class="pl-10 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                        </div>
                        @error('payment_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <!-- Tipo de Pagamento -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">{{ __('messages.payment_type') }}</label>
                        <div class="space-y-3">
                            <!-- Parcela Regular -->
                            <div class="relative">
                                <input id="payment_type_installment" wire:model="payment_type" value="installment" type="radio" 
                                    class="sr-only peer" />
                                <label for="payment_type_installment" class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all duration-200">
                                    <div class="flex items-center">
                                        <div class="w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-blue-500 peer-checked:bg-blue-500 flex items-center justify-center">
                                            <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100"></div>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">{{ __('messages.regular_installment') }}</div>
                                            <div class="text-sm text-gray-500">{{ number_format($paymentDiscount?->installment_amount ?? 0, 2) }} Kz</div>
                                        </div>
                                    </div>
                                    <div class="ml-auto">
                                        <i class="fas fa-calendar-check text-blue-500"></i>
                                    </div>
                                </label>
                            </div>
                            
                            <!-- Valor Personalizado -->
                            <div class="relative">
                                <input id="payment_type_custom" wire:model="payment_type" value="custom" type="radio" 
                                    class="sr-only peer" />
                                <label for="payment_type_custom" class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 peer-checked:border-yellow-500 peer-checked:bg-yellow-50 transition-all duration-200">
                                    <div class="flex items-center">
                                        <div class="w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-yellow-500 peer-checked:bg-yellow-500 flex items-center justify-center">
                                            <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100"></div>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">{{ __('messages.custom_amount') }}</div>
                                            <div class="text-sm text-gray-500">{{ __('messages.enter_custom_amount') }}</div>
                                        </div>
                                    </div>
                                    <div class="ml-auto">
                                        <i class="fas fa-edit text-yellow-500"></i>
                                    </div>
                                </label>
                                
                                <!-- Campo de Valor Personalizado -->
                                <div x-show="$wire.payment_type === 'custom'" 
                                     x-transition:enter="transition ease-out duration-300" 
                                     x-transition:enter-start="opacity-0 scale-95" 
                                     x-transition:enter-end="opacity-100 scale-100" 
                                     x-transition:leave="transition ease-in duration-200" 
                                     x-transition:leave-start="opacity-100 scale-100" 
                                     x-transition:leave-end="opacity-0 scale-95"
                                     x-cloak
                                     class="mt-3 ml-7 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <label for="payment_amount" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-coins text-yellow-600 mr-2"></i>
                                        {{ __('messages.payment_amount') }} *
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 text-sm font-medium">Kz</span>
                                        </div>
                                        <input type="number" step="0.01" id="payment_amount" wire:model="payment_amount" 
                                            placeholder="0.00" max="{{ $paymentDiscount?->remaining_amount ?? 0 }}"
                                            class="pl-12 block w-full border-yellow-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 text-sm bg-white">
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <span class="text-xs text-gray-400">máx: {{ number_format($paymentDiscount?->remaining_amount ?? 0, 2) }}</span>
                                        </div>
                                    </div>
                                    @error('payment_amount') 
                                        <p class="mt-2 text-sm text-red-600 flex items-center">
                                            <i class="fas fa-exclamation-triangle mr-2"></i>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Pagamento Total -->
                            <div class="relative">
                                <input id="payment_type_full" wire:model="payment_type" value="full" type="radio" 
                                    class="sr-only peer" />
                                <label for="payment_type_full" class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 peer-checked:border-green-500 peer-checked:bg-green-50 transition-all duration-200">
                                    <div class="flex items-center">
                                        <div class="w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-green-500 peer-checked:bg-green-500 flex items-center justify-center">
                                            <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100"></div>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">{{ __('messages.full_payment') }}</div>
                                            <div class="text-sm text-gray-500">{{ number_format($paymentDiscount?->remaining_amount ?? 0, 2) }} Kz</div>
                                        </div>
                                    </div>
                                    <div class="ml-auto">
                                        <i class="fas fa-check-circle text-green-500"></i>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Notas do Pagamento -->
                    <div>
                        <label for="payment_notes" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-sticky-note mr-1 text-gray-400"></i>
                            {{ __('messages.payment_notes') }}
                        </label>
                        <textarea id="payment_notes" wire:model="payment_notes" rows="3" 
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm" 
                            placeholder="{{ __('messages.payment_notes_placeholder') }}"></textarea>
                        @error('payment_notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <!-- Resumo do Pagamento -->
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <h4 class="font-medium text-gray-900 mb-2 flex items-center">
                            <i class="fas fa-calculator mr-2 text-gray-600"></i>
                            {{ __('messages.payment_summary') }}
                        </h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">{{ __('messages.payment_amount') }}:</span>
                                <span class="font-medium">
                                    @if($payment_type === 'installment')
                                        {{ number_format($paymentDiscount?->installment_amount ?? 0, 2) }} Kz
                                    @elseif($payment_type === 'full')
                                        {{ number_format($paymentDiscount?->remaining_amount ?? 0, 2) }} Kz
                                    @else
                                        {{ number_format($payment_amount ?? 0, 2) }} Kz
                                    @endif
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-600">{{ __('messages.remaining_after_payment') }}:</span>
                                <span class="font-medium">
                                    @if($payment_type === 'installment')
                                        {{ number_format(($paymentDiscount?->remaining_amount ?? 0) - ($paymentDiscount?->installment_amount ?? 0), 2) }} Kz
                                    @elseif($payment_type === 'full')
                                        0.00 Kz
                                    @else
                                        {{ number_format(($paymentDiscount?->remaining_amount ?? 0) - ($payment_amount ?? 0), 2) }} Kz
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Rodapé -->
            <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                <button wire:click="closePaymentModal" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out">
                    <i class="fas fa-times mr-2"></i>
                    {{ __('messages.cancel') }}
                </button>
                <button wire:click="processPayment" class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out">
                    <i class="fas fa-save mr-2"></i>
                    {{ __('messages.process_payment') }}
                </button>
            </div>
        </div>
    </div>
</div>
