<!-- Modal para Confirmar Exclusão de Programação de Produção -->
<div x-data="{ show: @entangle('showDeleteModal') }"
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-90"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 transform scale-100"
     x-transition:leave-end="opacity-0 transform scale-90"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display:none">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <!-- Cabeçalho do Modal com gradiente -->
            <div class="bg-gradient-to-r from-red-600 to-red-700 px-4 py-3">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    {{ __('messages.confirm_deletion') }}
                </h3>
            </div>

            <div class="p-6">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <i class="fas fa-trash text-red-600 text-xl animate-pulse"></i>
                </div>
                <div class="text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-headline">
                        {{ __('messages.are_you_sure') }}
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            {{ __('messages.delete_schedule_warning') }}
                        </p>
                    </div>
                </div>
                
                @if($scheduleToDelete)
                    <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <dl class="space-y-2">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
                                <dt class="text-sm font-medium text-gray-500">{{ __('messages.schedule_number') }}:</dt>
                                <dd class="text-sm text-gray-900 md:text-right">{{ $scheduleToDelete->schedule_number }}</dd>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
                                <dt class="text-sm font-medium text-gray-500">{{ __('messages.product') }}:</dt>
                                <dd class="text-sm text-gray-900 md:text-right">
                                    {{ $scheduleToDelete->product->name ?? 'N/A' }}
                                    @if($scheduleToDelete->product && $scheduleToDelete->product->sku)
                                        <span class="text-xs text-gray-500 ml-1">({{ $scheduleToDelete->product->sku }})</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
                                <dt class="text-sm font-medium text-gray-500">{{ __('messages.quantity') }}:</dt>
                                <dd class="text-sm text-gray-900 md:text-right">{{ number_format($scheduleToDelete->quantity, 2) }}</dd>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
                                <dt class="text-sm font-medium text-gray-500">{{ __('messages.date_range') }}:</dt>
                                <dd class="text-sm text-gray-900 md:text-right">
                                    {{ $scheduleToDelete->start_date->format('d/m/Y') }} - {{ $scheduleToDelete->end_date->format('d/m/Y') }}
                                </dd>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
                                <dt class="text-sm font-medium text-gray-500">{{ __('messages.status') }}:</dt>
                                <dd class="text-sm text-gray-900 md:text-right">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($scheduleToDelete->status === 'draft') bg-gray-100 text-gray-800
                                        @elseif($scheduleToDelete->status === 'confirmed') bg-blue-100 text-blue-800
                                        @elseif($scheduleToDelete->status === 'in_progress') bg-yellow-100 text-yellow-800
                                        @elseif($scheduleToDelete->status === 'completed') bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        {{ __('messages.' . $scheduleToDelete->status) }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Alerta de ordens relacionadas -->
                    @if(isset($relatedOrders) && count($relatedOrders) > 0)
                        <div class="rounded-md bg-yellow-50 p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-yellow-400"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">{{ __('messages.attention_required') }}</h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <p>
                                            {{ __('messages.related_orders_warning', ['count' => count($relatedOrders)]) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
                
                <!-- Aviso importante sobre exclusão -->
                <div class="mt-4 p-3 bg-yellow-50 border-l-4 border-yellow-400 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700 text-left">
                                {{ __('messages.delete_warning_permanent') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Botões de Ação -->
            <div class="px-4 py-3 bg-gray-50 sm:px-6 flex justify-end space-x-2">
                <button type="button" wire:click="closeDeleteModal" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                    <i class="fas fa-times-circle mr-2"></i>
                    {{ __('messages.cancel') }}
                </button>
                <button type="button" wire:click="delete" 
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                    <i class="fas fa-trash-alt mr-2"></i>
                    {{ __('messages.delete') }}
                </button>
            </div>
        </div>
    </div>
</div>