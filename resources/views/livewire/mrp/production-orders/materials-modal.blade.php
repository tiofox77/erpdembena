<!-- Modal de Visualização de Materiais da Ordem de Produção -->
<div x-data="{ show: @entangle('showMaterialsModal') }"
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

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            @if($selectedOrder)
                <!-- Cabeçalho do Modal com gradiente -->
                <div class="bg-gradient-to-r from-green-600 to-green-700 px-4 py-3">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-boxes mr-2"></i>
                        {{ __('messages.production_materials_for', ['order' => $selectedOrder->order_number]) }}
                    </h3>
                </div>

                <!-- Conteúdo do Modal -->
                <div class="p-6">
                    <!-- Informações da Ordem -->
                    <div class="mb-4">
                        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-2 mb-2">
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('messages.product') }}:</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $selectedOrder->product->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('messages.quantity_to_produce') }}:</p>
                                <p class="text-sm font-semibold text-gray-900">{{ number_format($selectedOrder->planned_quantity, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('messages.bom') }}:</p>
                                <p class="text-sm font-semibold text-gray-900">
                                    @if($selectedOrder->bomHeader)
                                        {{ $selectedOrder->bomHeader->description }} (V{{ $selectedOrder->bomHeader->version }})
                                    @else
                                        <span class="text-red-500">{{ __('messages.bom_not_defined') }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    @if(!$selectedOrder->bomHeader)
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        {{ __('messages.bom_missing_warning') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @elseif(count($orderMaterials) === 0)
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        {{ __('messages.bom_empty_warning') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Resumo de Disponibilidade de Materiais -->
                        <div class="mb-6">
                            <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-2 rounded-lg mb-4">
                                <h4 class="text-base font-medium text-gray-700 flex items-center">
                                    <i class="fas fa-chart-pie text-blue-500 mr-2"></i>
                                    {{ __('messages.availability_summary') }}
                                </h4>
                            </div>
                            
                            @php
                                $totalComponents = count($orderMaterials);
                                $shortageComponents = 0;
                                $criticalShortageComponents = 0;
                                
                                foreach($orderMaterials as $material) {
                                    if($material['shortage'] > 0) {
                                        $shortageComponents++;
                                        if($material['is_critical']) {
                                            $criticalShortageComponents++;
                                        }
                                    }
                                }
                                
                                $fullyAvailable = $shortageComponents === 0;
                                $partiallyAvailable = $shortageComponents > 0 && $shortageComponents < $totalComponents;
                                $unavailable = $shortageComponents === $totalComponents;
                                $hasCriticalShortage = $criticalShortageComponents > 0;
                            @endphp
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Status Geral -->
                                <div class="bg-white rounded-lg shadow p-4 border-l-4 
                                    @if($fullyAvailable) border-green-500
                                    @elseif($partiallyAvailable && !$hasCriticalShortage) border-yellow-500
                                    @else border-red-500 @endif">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 mr-3">
                                            <div class="w-10 h-10 rounded-full flex items-center justify-center
                                                @if($fullyAvailable) bg-green-100 text-green-500
                                                @elseif($partiallyAvailable && !$hasCriticalShortage) bg-yellow-100 text-yellow-500
                                                @else bg-red-100 text-red-500 @endif">
                                                <i class="fas 
                                                    @if($fullyAvailable) fa-check
                                                    @elseif($partiallyAvailable && !$hasCriticalShortage) fa-exclamation
                                                    @else fa-times @endif text-lg"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-500">{{ __('messages.status') }}</div>
                                            <div class="text-lg font-semibold
                                                @if($fullyAvailable) text-green-600
                                                @elseif($partiallyAvailable && !$hasCriticalShortage) text-yellow-600
                                                @else text-red-600 @endif">
                                                @if($fullyAvailable) {{ __('messages.ready_for_production') }}
                                                @elseif($partiallyAvailable && !$hasCriticalShortage) {{ __('messages.partially_available') }}
                                                @else {{ __('messages.unavailable') }} @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Materiais em Falta -->
                                <div class="bg-white rounded-lg shadow p-4 border-l-4 
                                    @if($shortageComponents === 0) border-green-500
                                    @elseif($shortageComponents < $totalComponents/2) border-yellow-500
                                    @else border-red-500 @endif">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 mr-3">
                                            <div class="w-10 h-10 rounded-full flex items-center justify-center
                                                @if($shortageComponents === 0) bg-green-100 text-green-500
                                                @elseif($shortageComponents < $totalComponents/2) bg-yellow-100 text-yellow-500
                                                @else bg-red-100 text-red-500 @endif">
                                                <i class="fas fa-boxes text-lg"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-500">{{ __('messages.missing_components') }}</div>
                                            <div class="text-lg font-semibold">
                                                {{ $shortageComponents }} de {{ $totalComponents }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Componentes Críticos -->
                                <div class="bg-white rounded-lg shadow p-4 border-l-4 
                                    @if($criticalShortageComponents === 0) border-green-500
                                    @else border-red-500 @endif">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 mr-3">
                                            <div class="w-10 h-10 rounded-full flex items-center justify-center
                                                @if($criticalShortageComponents === 0) bg-green-100 text-green-500
                                                @else bg-red-100 text-red-500 @endif">
                                                <i class="fas
                                                    @if($criticalShortageComponents === 0) fa-shield-alt
                                                    @else fa-exclamation-triangle @if($criticalShortageComponents > 0) animate-pulse @endif @endif text-lg"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-500">{{ __('messages.critical_components') }}</div>
                                            <div class="text-lg font-semibold
                                                @if($criticalShortageComponents === 0) text-green-600
                                                @else text-red-600 @endif">
                                                {{ $criticalShortageComponents }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tabela de Materiais Necessários -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('messages.component') }}
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('messages.required_per_unit') }}
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('messages.total_required') }}
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('messages.available_stock') }}
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('messages.status') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($orderMaterials as $material)
                                        <tr class="hover:bg-gray-50 transition-colors duration-150 
                                            @if($material['is_critical'] && $material['shortage'] > 0) bg-red-50 @endif">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    @if($material['is_critical'])
                                                        <span class="mr-2 text-red-500" title="{{ __('messages.critical_components') }}">
                                                            <i class="fas fa-exclamation-circle"></i>
                                                        </span>
                                                    @endif
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $material['component']->name ?? 'N/A' }}
                                                    </div>
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $material['component']->sku ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <div class="text-sm text-gray-900">
                                                    {{ number_format($material['quantity_per_unit'], 4) }} 
                                                    {{ $material['uom'] ?? 'UN' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ number_format($material['required_quantity'], 2) }}
                                                    {{ $material['uom'] ?? 'UN' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <div class="text-sm @if($material['available_quantity'] < $material['required_quantity']) text-red-600 @else text-gray-900 @endif">
                                                    {{ number_format($material['available_quantity'], 2) }}
                                                    {{ $material['uom'] ?? 'UN' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                @if($material['shortage'] > 0)
                                                    <span class="px-2 py-1 text-xs rounded-full 
                                                        @if($material['is_critical']) bg-red-100 text-red-800 @else bg-yellow-100 text-yellow-800 @endif">
                                                        {{ __('messages.shortage') }}: {{ number_format($material['shortage'], 2) }} {{ $material['uom'] ?? 'UN' }}
                                                    </span>
                                                @else
                                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                                        {{ __('messages.available') }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
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
                </div>
            @else
                <div class="p-6 text-center">
                    <p class="text-gray-500">{{ __('messages.loading_information') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
