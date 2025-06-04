<!-- Modal para Visualizar Nível de Estoque -->
<div x-data="{ open: @entangle('showViewModal') }"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center overflow-auto bg-gray-600 bg-opacity-75 transition-opacity"
    role="dialog"
    aria-modal="true"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @keydown.escape.window="$wire.closeViewModal()">
    <div class="relative w-full max-w-2xl mx-auto my-8 px-4 sm:px-0"
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        @click.away="$wire.closeViewModal()">
        <div class="relative bg-white rounded-lg shadow-xl overflow-hidden" @click.stop>
            <!-- Cabeçalho do Modal -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-4 sm:px-6 flex justify-between items-center">
                <h3 class="text-lg font-medium text-white">
                    <i class="fas fa-eye mr-2"></i>
                    {{ __('messages.view_inventory_level_details') }}
                </h3>
                <button @click="$wire.closeViewModal()" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Informações do Produto -->
                    <div class="col-span-2 bg-gray-50 p-4 rounded-lg">
                        <div class="flex items-center mb-2">
                            <div class="flex-shrink-0 h-12 w-12 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-box text-blue-500 text-lg"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-semibold text-gray-900">
                                    {{ $viewingLevel?->product?->name }}
                                </h4>
                                <div class="flex items-center text-sm text-gray-500 mt-1">
                                    <span class="mr-2"><i class="fas fa-barcode mr-1"></i>{{ $viewingLevel?->product?->sku }}</span>
                                    @if($viewingLevel?->product?->category)
                                        <span><i class="fas fa-tag mr-1"></i>{{ $viewingLevel?->product?->category?->name }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Local -->
                    <div class="bg-gray-50 p-3 rounded-md">
                        <h5 class="text-sm font-medium text-gray-500 mb-1">{{ __('messages.location') }}</h5>
                        <p class="text-base font-medium text-gray-800">{{ $viewingLevel?->location?->name }}</p>
                    </div>

                    <!-- Unidade de Medida -->
                    <div class="bg-gray-50 p-3 rounded-md">
                        <h5 class="text-sm font-medium text-gray-500 mb-1">{{ __('messages.uom') }}</h5>
                        <p class="text-base font-medium text-gray-800">{{ $viewingLevel?->uom }}</p>
                    </div>

                    <!-- Integração com Supply Chain -->
                    <div class="col-span-2 border-t border-b border-gray-200 py-4 my-2">
                        <h5 class="text-sm font-semibold text-gray-600 mb-3">
                            <i class="fas fa-link mr-1"></i> 
                            {{ __('messages.stock_from_supply_chain') }}
                        </h5>
                        
                        <!-- Estoque Atual com Barra de Progresso -->
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center mr-2">
                                    <i class="fas fa-boxes text-blue-600"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-700">{{ __('messages.current_stock') }}</span>
                            </div>
                            <div>
                                <span class="text-lg font-bold {{ $viewingLevel?->current_stock <= $viewingLevel?->safety_stock ? 'text-red-600' : ($viewingLevel?->current_stock <= $viewingLevel?->reorder_point ? 'text-amber-600' : 'text-gray-800') }}">
                                    {{ number_format($viewingLevel?->current_stock ?? 0, 2) }} {{ $viewingLevel?->uom }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="w-full bg-gray-200 rounded-full h-3 mb-4">
                            @php
                                if ($viewingLevel) {
                                    $maxValue = max($viewingLevel->maximum_stock, $viewingLevel->current_stock);
                                    $percentage = $maxValue > 0 ? ($viewingLevel->current_stock / $maxValue) * 100 : 0;
                                    $barColor = $viewingLevel->current_stock <= $viewingLevel->safety_stock ? 'bg-red-600' : 
                                               ($viewingLevel->current_stock <= $viewingLevel->reorder_point ? 'bg-amber-500' : 
                                               ($viewingLevel->current_stock > $viewingLevel->maximum_stock && $viewingLevel->maximum_stock > 0 ? 'bg-blue-600' : 'bg-green-600'));
                                } else {
                                    $percentage = 0;
                                    $barColor = 'bg-gray-400';
                                }
                            @endphp
                            <div class="{{ $barColor }} h-3 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                        
                        <!-- Status do Estoque -->
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-medium text-gray-700">{{ __('messages.status') }}</span>
                            @if($viewingLevel)
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                                    {{ $viewingLevel->getStockStatus() === 'normal' ? 'bg-green-100 text-green-800' : 
                                       ($viewingLevel->getStockStatus() === 'low' ? 'bg-amber-100 text-amber-800' : 
                                       ($viewingLevel->getStockStatus() === 'overstock' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800')) }}">
                                    <i class="fas 
                                        {{ $viewingLevel->getStockStatus() === 'normal' ? 'fa-check-circle' : 
                                           ($viewingLevel->getStockStatus() === 'low' ? 'fa-exclamation-triangle' : 
                                           ($viewingLevel->getStockStatus() === 'overstock' ? 'fa-arrow-up' : 'fa-times-circle')) }} mr-1 
                                        {{ $viewingLevel->getStockStatus() === 'critical' ? 'animate-pulse' : '' }}"></i>
                                    {{ __('messages.' . $viewingLevel->getStockStatus() . '_stock') }}
                                </span>
                            @else
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    <i class="fas fa-question-circle mr-1"></i>
                                    {{ __('messages.unavailable') }}
                                </span>
                            @endif
                        </div>
                        
                        <!-- Estoque Disponível -->
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">{{ __('messages.available') }}</span>
                            <span class="font-medium text-gray-800">
                                {{ number_format($viewingLevel?->available_stock ?? 0, 2) }} {{ $viewingLevel?->uom }}
                            </span>
                        </div>
                    </div>

                    <!-- Níveis de Estoque Configurados -->
                    <div class="col-span-2">
                        <h5 class="text-sm font-semibold text-gray-600 mb-3">{{ __('messages.configured_levels') }}</h5>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Estoque Mínimo (Safety Stock) -->
                            <div class="bg-red-50 p-3 rounded-md border border-red-100">
                                <div class="flex justify-between items-center">
                                    <h6 class="text-xs font-medium text-red-700 mb-1 flex items-center">
                                        <i class="fas fa-shield-alt mr-1"></i> {{ __('messages.minimum_stock') }}
                                    </h6>
                                    <i class="fas fa-info-circle text-red-400 cursor-help" title="{{ __('messages.safety_level_info') }}"></i>
                                </div>
                                <p class="text-lg font-bold text-red-800">{{ number_format($viewingLevel?->safety_stock ?? 0, 2) }} {{ $viewingLevel?->uom }}</p>
                            </div>

                            <!-- Ponto de Reposição -->
                            <div class="bg-amber-50 p-3 rounded-md border border-amber-100">
                                <div class="flex justify-between items-center">
                                    <h6 class="text-xs font-medium text-amber-700 mb-1 flex items-center">
                                        <i class="fas fa-sync mr-1"></i> {{ __('messages.reorder_point') }}
                                    </h6>
                                    <i class="fas fa-info-circle text-amber-400 cursor-help" title="{{ __('messages.reorder_point_info') }}"></i>
                                </div>
                                <p class="text-lg font-bold text-amber-800">{{ number_format($viewingLevel?->reorder_point ?? 0, 2) }} {{ $viewingLevel?->uom }}</p>
                            </div>

                            <!-- Estoque Máximo -->
                            <div class="bg-blue-50 p-3 rounded-md border border-blue-100">
                                <div class="flex justify-between items-center">
                                    <h6 class="text-xs font-medium text-blue-700 mb-1 flex items-center">
                                        <i class="fas fa-warehouse mr-1"></i> {{ __('messages.maximum_stock') }}
                                    </h6>
                                    <i class="fas fa-info-circle text-blue-400 cursor-help" title="{{ __('messages.maximum_stock_info') }}"></i>
                                </div>
                                <p class="text-lg font-bold text-blue-800">{{ number_format($viewingLevel?->maximum_stock ?? 0, 2) }} {{ $viewingLevel?->uom }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Notas -->
                    @if($viewingLevel?->notes)
                    <div class="col-span-2 mt-2">
                        <h5 class="text-sm font-semibold text-gray-600 mb-2">{{ __('messages.notes') }}</h5>
                        <div class="bg-yellow-50 p-3 rounded-md border border-yellow-100">
                            <p class="text-sm text-gray-700">{{ $viewingLevel->notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
                
            <!-- Rodapé do Modal -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end space-x-2">
                <button type="button" wire:click="closeViewModal" 
                    class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                    <i class="fas fa-times mr-2"></i>
                    {{ __('messages.close') }}
                </button>
                <button type="button" wire:click="edit({{ $viewingLevel?->id }})" 
                    class="inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                    <i class="fas fa-edit mr-2"></i>
                    {{ __('messages.edit') }}
                </button>
            </div>
        </div>
    </div>
</div>
