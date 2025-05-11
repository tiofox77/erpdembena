<!-- Modal para visualizar detalhes completos da BOM -->
<div x-data="{ showBomDetailsModal: @entangle('showBomDetailsModal').defer }" class="fixed inset-0 overflow-y-auto" x-show="showBomDetailsModal" x-cloak>
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" x-show="showBomDetailsModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full"
            x-show="showBomDetailsModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            
            <!-- Cabeçalho do Modal -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-4 py-3 sm:px-6 flex justify-between items-center">
                <h3 class="text-lg leading-6 font-medium text-white">
                    <i class="fas fa-project-diagram mr-2"></i>
                    {{ __('messages.bom_details') }} - {{ $selectedBomForDetails->bom_number ?? '' }}
                </h3>
                <button @click="showBomDetailsModal = false" class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Conteúdo do Modal -->
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                @if ($selectedBomForDetails)
                    <!-- Informações do Cabeçalho da BOM -->
                    <div class="mb-6 bg-blue-50 p-4 rounded-lg border border-blue-200">
                        <h4 class="text-lg font-semibold text-blue-800 mb-4 flex items-center">
                            <i class="fas fa-info-circle mr-2"></i>
                            {{ __('messages.bom_information') }}
                        </h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Coluna 1 - Informações Básicas -->
                            <div>
                                <div class="mb-3">
                                    <span class="block text-sm font-medium text-gray-500">{{ __('messages.product') }}:</span>
                                    <span class="text-gray-800 font-semibold">{{ $selectedBomForDetails->product->name ?? 'N/A' }}</span>
                                    <span class="block text-xs text-gray-500">{{ $selectedBomForDetails->product->sku ?? '' }}</span>
                                </div>
                                
                                <div class="mb-3">
                                    <span class="block text-sm font-medium text-gray-500">{{ __('messages.bom_number') }}:</span>
                                    <span class="text-gray-800">{{ $selectedBomForDetails->bom_number }}</span>
                                </div>
                                
                                <div class="mb-3">
                                    <span class="block text-sm font-medium text-gray-500">{{ __('messages.version') }}:</span>
                                    <span class="text-gray-800">{{ $selectedBomForDetails->version }}</span>
                                </div>
                            </div>
                            
                            <!-- Coluna 2 - Datas e Status -->
                            <div>
                                <div class="mb-3">
                                    <span class="block text-sm font-medium text-gray-500">{{ __('messages.status') }}:</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        @if($selectedBomForDetails->status === 'draft') bg-gray-100 text-gray-800
                                        @elseif($selectedBomForDetails->status === 'active') bg-green-100 text-green-800
                                        @elseif($selectedBomForDetails->status === 'obsolete') bg-red-100 text-red-800
                                        @endif">
                                        <i class="mr-1
                                            @if($selectedBomForDetails->status === 'draft') fas fa-pencil-alt
                                            @elseif($selectedBomForDetails->status === 'active') fas fa-check-circle
                                            @elseif($selectedBomForDetails->status === 'obsolete') fas fa-ban
                                            @endif"></i>
                                        @if($selectedBomForDetails->status === 'draft') {{ __('messages.status_draft') }}
                                        @elseif($selectedBomForDetails->status === 'active') {{ __('messages.status_active') }}
                                        @elseif($selectedBomForDetails->status === 'obsolete') {{ __('messages.status_obsolete') }}
                                        @else {{ $selectedBomForDetails->status }}
                                        @endif
                                    </span>
                                </div>
                                
                                <div class="mb-3">
                                    <span class="block text-sm font-medium text-gray-500">{{ __('messages.effective_date') }}:</span>
                                    <span class="text-gray-800">{{ $selectedBomForDetails->effective_date ? $selectedBomForDetails->effective_date->format('d/m/Y') : 'N/A' }}</span>
                                </div>
                                
                                <div class="mb-3">
                                    <span class="block text-sm font-medium text-gray-500">{{ __('messages.expiration_date') }}:</span>
                                    <span class="text-gray-800">{{ $selectedBomForDetails->expiration_date ? $selectedBomForDetails->expiration_date->format('d/m/Y') : __('messages.not_applicable') }}</span>
                                </div>
                            </div>
                            
                            <!-- Coluna 3 - Descrição e Notas -->
                            <div>
                                <div class="mb-3">
                                    <span class="block text-sm font-medium text-gray-500">{{ __('messages.description') }}:</span>
                                    <span class="text-gray-800">{{ $selectedBomForDetails->description }}</span>
                                </div>
                                
                                <div class="mb-3">
                                    <span class="block text-sm font-medium text-gray-500">{{ __('messages.notes') }}:</span>
                                    <span class="text-gray-800">{{ $selectedBomForDetails->notes ?: __('messages.no_notes') }}</span>
                                </div>
                                
                                <div class="mb-3">
                                    <span class="block text-sm font-medium text-gray-500">{{ __('messages.created_at') }}:</span>
                                    <span class="text-gray-800">{{ $selectedBomForDetails->created_at ? $selectedBomForDetails->created_at->format('d/m/Y H:i:s') : 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Componentes da BOM -->
                    <div>
                        <h4 class="text-lg font-semibold text-blue-800 mb-4 flex items-center">
                            <i class="fas fa-puzzle-piece mr-2"></i>
                            {{ __('messages.components') }}
                            <span class="ml-2 bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                                {{ count($bomDetailsComponents) }} {{ __('messages.items') }}
                            </span>
                        </h4>
                        
                        @if(count($bomDetailsComponents) > 0)
                            <div class="overflow-x-auto bg-white rounded-lg shadow-md">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('messages.component') }}
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('messages.quantity') }}
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('messages.level') }}
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('messages.position') }}
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('messages.critical') }}
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('messages.scrap') }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($bomDetailsComponents as $component)
                                            <tr class="hover:bg-gray-50 transition-colors duration-150 {{ (isset($component['is_critical']) && ($component['is_critical'] === true || $component['is_critical'] === 1 || $component['is_critical'] === 'Sim')) ? 'bg-red-50' : '' }}">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        @if(isset($component['component']) && isset($component['component']['name']))
                                                            {{ $component['component']['name'] }}
                                                        @else
                                                            Componente #{{ $component['id'] ?? '?' }}
                                                        @endif
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        @if(isset($component['component']) && isset($component['component']['sku']))
                                                            {{ $component['component']['sku'] }}
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ number_format($component['quantity'], 4) }} {{ $component['uom'] }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $component['level'] ?? '1' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $component['position'] ?: '-' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    @if(isset($component['is_critical']) && ($component['is_critical'] === true || $component['is_critical'] === 1 || $component['is_critical'] === 'Sim'))
                                                        <span class="text-red-600" title="{{ __('messages.critical_component') }}">
                                                            <i class="fas fa-exclamation-circle text-lg"></i>
                                                        </span>
                                                    @else
                                                        <span class="text-gray-400" title="{{ __('messages.not_critical_component') }}">
                                                            <i class="fas fa-minus"></i>
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    @if(isset($component['scrap_percentage']) && $component['scrap_percentage'] > 0)
                                                        {{ number_format($component['scrap_percentage'], 2) }}%
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-box-open text-gray-300 text-5xl mb-3"></i>
                                    <h3 class="text-gray-500 text-lg font-medium mb-2">{{ __('messages.no_components_found') }}</h3>
                                    <p class="text-gray-400 text-sm max-w-md">{{ __('messages.no_components_message') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-gray-300 text-5xl mb-3"></i>
                            <h3 class="text-gray-500 text-lg font-medium mb-2">{{ __('messages.bom_not_found') }}</h3>
                            <p class="text-gray-400 text-sm max-w-md">{{ __('messages.bom_not_found_message') }}</p>
                        </div>
                    </div>
                @endif
            </div>
            
            <!-- Rodapé do Modal -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button @click="showBomDetailsModal = false" type="button" class="inline-flex justify-center px-4 py-2 bg-gray-600 text-white rounded-md shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:ml-3 sm:w-auto sm:text-sm">
                    {{ __('messages.close') }}
                </button>
                
                @if($selectedBomForDetails && $selectedBomForDetails->id)
                <button wire:click="edit({{ $selectedBomForDetails->id }})" @click="showBomDetailsModal = false" type="button" class="mt-3 sm:mt-0 inline-flex justify-center px-4 py-2 bg-blue-600 text-white rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                    <i class="fas fa-edit mr-2"></i>
                    {{ __('messages.edit') }}
                </button>
                @endif
            </div>
        </div>
    </div>
</div>
