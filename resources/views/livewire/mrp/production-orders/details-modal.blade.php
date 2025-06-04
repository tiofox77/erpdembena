<!-- Modal de Detalhes da Ordem de Produção -->
<div x-data="{ show: @entangle('showDetailsModal') }"
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

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
            @if($selectedOrder)
                <!-- Cabeçalho do Modal com gradiente -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-clipboard-list mr-2"></i>
                        {{ __('messages.production_order_details') }}
                    </h3>
                </div>

                <!-- Conteúdo do Modal -->
                <div class="p-6 space-y-6">
                    <!-- Status e Prioridade -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                        <div class="flex items-center">
                            <div class="text-xl font-bold text-gray-800">{{ $selectedOrder->order_number }}</div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                @if($selectedOrder->status === 'draft') bg-gray-100 text-gray-800
                                @elseif($selectedOrder->status === 'released') bg-blue-100 text-blue-800
                                @elseif($selectedOrder->status === 'in_progress') bg-yellow-100 text-yellow-800
                                @elseif($selectedOrder->status === 'completed') bg-green-100 text-green-800
                                @elseif($selectedOrder->status === 'cancelled') bg-red-100 text-red-800
                                @endif">
                                <i class="mr-1
                                    @if($selectedOrder->status === 'draft') fas fa-pencil-alt
                                    @elseif($selectedOrder->status === 'released') fas fa-paper-plane
                                    @elseif($selectedOrder->status === 'in_progress') fas fa-spinner fa-spin
                                    @elseif($selectedOrder->status === 'completed') fas fa-check-circle
                                    @elseif($selectedOrder->status === 'cancelled') fas fa-ban
                                    @endif"></i>
                                {{ $statuses[$selectedOrder->status] ?? $selectedOrder->status }}
                            </span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                @if($selectedOrder->priority === 'low') bg-blue-100 text-blue-800
                                @elseif($selectedOrder->priority === 'medium') bg-gray-100 text-gray-800
                                @elseif($selectedOrder->priority === 'high') bg-orange-100 text-orange-800
                                @elseif($selectedOrder->priority === 'urgent') bg-red-100 text-red-800
                                @endif">
                                <i class="mr-1
                                    @if($selectedOrder->priority === 'low') fas fa-arrow-down
                                    @elseif($selectedOrder->priority === 'medium') fas fa-minus
                                    @elseif($selectedOrder->priority === 'high') fas fa-arrow-up
                                    @elseif($selectedOrder->priority === 'urgent') fas fa-exclamation-circle
                                    @endif"></i>
                                {{ $priorities[$selectedOrder->priority] ?? $selectedOrder->priority }}
                            </span>
                        </div>
                    </div>

                    <!-- Informações Principais -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-4">
                        <h4 class="text-base font-medium text-gray-700 flex items-center mb-3">
                            <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                            {{ __('messages.product_information') }}
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('messages.product') }}:</p>
                                <p class="text-sm text-gray-800">{{ $selectedOrder->product->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('messages.sku') }}:</p>
                                <p class="text-sm text-gray-800">{{ $selectedOrder->product->sku ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('messages.bom') }}:</p>
                                <p class="text-sm text-gray-800">{{ $selectedOrder->bomHeader->description ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('messages.bom_version') }}:</p>
                                <p class="text-sm text-gray-800">{{ $selectedOrder->bomHeader->version ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Planejamento e Datas -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-4">
                        <h4 class="text-base font-medium text-gray-700 flex items-center mb-3">
                            <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>
                            {{ __('messages.planning_and_dates') }}
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('messages.production_schedule') }}:</p>
                                <p class="text-sm text-gray-800">{{ $selectedOrder->schedule->description ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('messages.location') }}:</p>
                                <p class="text-sm text-gray-800">{{ $selectedOrder->location->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('messages.planned_start_date') }}:</p>
                                <p class="text-sm text-gray-800">{{ $selectedOrder->planned_start_date ? $selectedOrder->planned_start_date->format('d/m/Y') : 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('messages.planned_end_date') }}:</p>
                                <p class="text-sm text-gray-800">{{ $selectedOrder->planned_end_date ? $selectedOrder->planned_end_date->format('d/m/Y') : 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('messages.actual_start_date') }}:</p>
                                <p class="text-sm text-gray-800">{{ $selectedOrder->actual_start_date ? $selectedOrder->actual_start_date->format('d/m/Y') : __('messages.not_started') }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('messages.actual_end_date') }}:</p>
                                <p class="text-sm text-gray-800">{{ $selectedOrder->actual_end_date ? $selectedOrder->actual_end_date->format('d/m/Y') : __('messages.not_completed') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quantidades -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-4">
                        <h4 class="text-base font-medium text-gray-700 flex items-center mb-3">
                            <i class="fas fa-clipboard-check text-blue-500 mr-2"></i>
                            {{ __('messages.quantities') }}
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('messages.planned_quantity') }}:</p>
                                <p class="text-sm text-gray-800">{{ number_format($selectedOrder->planned_quantity, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('messages.produced_quantity') }}:</p>
                                <p class="text-sm text-gray-800">{{ number_format($selectedOrder->produced_quantity, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('messages.rejected_quantity') }}:</p>
                                <p class="text-sm text-gray-800">{{ number_format($selectedOrder->rejected_quantity, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('messages.remaining_quantity') }}:</p>
                                <p class="text-sm text-gray-800">{{ number_format(max(0, $selectedOrder->planned_quantity - $selectedOrder->produced_quantity), 2) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('messages.completion_percentage') }}:</p>
                                <p class="text-sm text-gray-800">
                                    @if($selectedOrder->planned_quantity > 0)
                                        {{ number_format(($selectedOrder->produced_quantity / $selectedOrder->planned_quantity) * 100, 1) }}%
                                    @else
                                        0%
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('messages.rejection_percentage') }}:</p>
                                <p class="text-sm text-gray-800">
                                    @if($selectedOrder->produced_quantity + $selectedOrder->rejected_quantity > 0)
                                        {{ number_format(($selectedOrder->rejected_quantity / ($selectedOrder->produced_quantity + $selectedOrder->rejected_quantity)) * 100, 1) }}%
                                    @else
                                        0%
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Observações -->
                    @if($selectedOrder->notes)
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-base font-medium text-gray-700 flex items-center mb-3">
                                <i class="fas fa-sticky-note text-blue-500 mr-2"></i>
                                {{ __('messages.observations') }}
                            </h4>
                            <p class="text-sm text-gray-800 whitespace-pre-line">{{ $selectedOrder->notes }}</p>
                        </div>
                    @endif

                    <!-- Botões de Ação para Alteração de Status -->
                    @if(!in_array($selectedOrder->status, ['completed', 'cancelled']))
                        <div class="mt-6 pt-4 border-t border-gray-200">
                            <h4 class="text-base font-medium text-gray-700 flex items-center mb-3">
                                <i class="fas fa-tasks text-blue-500 mr-2"></i>
                                {{ __('messages.available_actions') }}
                            </h4>
                            <div class="flex flex-wrap gap-2">
                                @if($selectedOrder->status === 'draft')
                                    <button wire:click="updateStatus({{ $selectedOrder->id }}, 'released')" 
                                        class="inline-flex items-center px-3 py-1 bg-blue-100 border border-blue-200 rounded-md text-sm font-medium text-blue-700 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out">
                                        <i class="fas fa-paper-plane mr-1"></i> {{ __('messages.release') }}
                                    </button>
                                    <button wire:click="updateStatus({{ $selectedOrder->id }}, 'cancelled')" 
                                        class="inline-flex items-center px-3 py-1 bg-red-100 border border-red-200 rounded-md text-sm font-medium text-red-700 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out">
                                        <i class="fas fa-ban mr-1"></i> {{ __('messages.cancel') }}
                                    </button>
                                @elseif($selectedOrder->status === 'released')
                                    <button wire:click="updateStatus({{ $selectedOrder->id }}, 'in_progress')" 
                                        class="inline-flex items-center px-3 py-1 bg-yellow-100 border border-yellow-200 rounded-md text-sm font-medium text-yellow-700 hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-all duration-200 ease-in-out">
                                        <i class="fas fa-play mr-1"></i> {{ __('messages.start') }}
                                    </button>
                                    <button wire:click="updateStatus({{ $selectedOrder->id }}, 'cancelled')" 
                                        class="inline-flex items-center px-3 py-1 bg-red-100 border border-red-200 rounded-md text-sm font-medium text-red-700 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out">
                                        <i class="fas fa-ban mr-1"></i> {{ __('messages.cancel') }}
                                    </button>
                                @elseif($selectedOrder->status === 'in_progress')
                                    <button wire:click="updateStatus({{ $selectedOrder->id }}, 'completed')" 
                                        class="inline-flex items-center px-3 py-1 bg-green-100 border border-green-200 rounded-md text-sm font-medium text-green-700 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 ease-in-out">
                                        <i class="fas fa-check-circle mr-1"></i> {{ __('messages.complete') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Botões de Ação -->
                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6 flex justify-end space-x-2">
                    <button type="button" wire:click="closeModal" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <i class="fas fa-times-circle mr-2"></i>
                        {{ __('messages.close') }}
                    </button>
                    <button type="button" wire:click="edit({{ $selectedOrder->id }})" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <i class="fas fa-edit mr-2"></i>
                        {{ __('messages.edit') }}
                    </button>
                </div>
            @else
                <div class="p-6 text-center">
                    <p class="text-gray-500">Carregando informações...</p>
                </div>
            @endif
        </div>
    </div>
</div>
