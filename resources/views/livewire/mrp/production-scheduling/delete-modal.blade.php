<!-- Modal para Confirmar Exclusão de Programação de Produção -->
<div x-cloak
    class="fixed inset-0 z-30 flex items-center justify-center overflow-auto bg-gray-800 bg-opacity-75 transition-opacity"
    x-show="$wire.showDeleteModal"
    @keydown.escape.window="$wire.closeDeleteModal()">
    <div class="relative w-full max-w-md mx-auto my-8 px-4 sm:px-0"
        x-show="$wire.showDeleteModal"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        @click.away="$wire.closeDeleteModal()">
        <div class="relative bg-white rounded-lg shadow-xl overflow-hidden" @click.stop>
            <!-- Cabeçalho do Modal -->
            <div class="bg-gradient-to-r from-red-600 to-red-700 px-4 py-4 sm:px-6 flex justify-between items-center">
                <h3 class="text-lg font-medium text-white">
                    <i class="fas fa-trash-alt mr-2 animate-pulse"></i>
                    {{ __('messages.confirm_delete') }}
                </h3>
                <button @click="$wire.closeDeleteModal()" class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="p-6">
                <div class="text-center mb-6">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">{{ __('messages.delete_schedule_confirmation') }}</h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            {{ __('messages.delete_schedule_warning') }}
                        </p>
                    </div>
                </div>

                @if($scheduleToDelete)
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-4">
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

                <!-- Checkbox de confirmação -->
                <div class="mt-4">
                    <div class="flex items-center">
                        <input id="confirm-delete" wire:model="confirmDelete" type="checkbox" 
                            class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                        <label for="confirm-delete" class="ml-2 block text-sm text-gray-900">
                            {{ __('messages.confirm_permanent_delete') }}
                        </label>
                    </div>
                    @error('confirmDelete')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <!-- Rodapé do Modal -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-col sm:flex-row-reverse gap-2">
                <button type="button" wire:click="delete" wire:loading.attr="disabled" 
                    class="inline-flex justify-center w-full sm:w-auto items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                    {{ $confirmDelete ? '' : 'disabled' }}>
                    <span wire:loading.remove wire:target="delete">
                        <i class="fas fa-trash-alt mr-2"></i>
                        {{ __('messages.delete_permanently') }}
                    </span>
                    <span wire:loading wire:target="delete">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        {{ __('messages.deleting') }}...
                    </span>
                </button>
                <button type="button" wire:click="closeDeleteModal" wire:loading.attr="disabled" 
                    class="inline-flex justify-center w-full sm:w-auto items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out">
                    <i class="fas fa-times mr-2"></i>
                    {{ __('messages.cancel') }}
                </button>
            </div>
        </div>
    </div>
</div>
