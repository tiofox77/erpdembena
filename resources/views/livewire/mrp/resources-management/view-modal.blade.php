<!-- Modal para Visualizar Recurso -->
<div>
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
        <div class="relative top-20 mx-auto p-1 w-full max-w-2xl">
            <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" 
                 x-transition:enter="transition ease-out duration-300" 
                 x-transition:enter-start="transform opacity-0 scale-95" 
                 x-transition:enter-end="transform opacity-100 scale-100" 
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="transform opacity-100 scale-100" 
                 x-transition:leave-end="transform opacity-0 scale-95">
                <!-- Cabeçalho do Modal com gradiente e animação -->
                <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-eye mr-2 animate-pulse"></i>
                        {{ trans('messages.resource_details') }}
                    </h3>
                    <button type="button" wire:click="closeViewModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

            @if($selectedResource)
                <div class="p-6">
                    <!-- Status do Recurso -->
                    <div class="mb-6 flex justify-center">
                        @if($selectedResource->active)
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1.5"></i>
                                {{ trans('messages.resource_active') }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                <i class="fas fa-times-circle mr-1.5"></i>
                                {{ trans('messages.resource_inactive') }}
                            </span>
                        @endif
                    </div>
                    
                    <!-- Grade de Informações -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Coluna 1: Informações Gerais -->
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                {{ trans('messages.general_information') }}
                            </h4>
                            
                            <dl class="space-y-3">
                                <!-- Nome -->
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ trans('messages.resource_name') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedResource->name }}</dd>
                                </div>
                                
                                <!-- Tipo de Recurso -->
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ trans('messages.resource_type') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedResource->resourceType->name ?? 'N/A' }}</dd>
                                </div>
                                
                                <!-- Descrição -->
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ trans('messages.description') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900 bg-gray-100 p-2 rounded">
                                        {{ $selectedResource->description ?: trans('messages.no_description') }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                        
                        <!-- Coluna 2: Localização e Departamento -->
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-map-marker-alt text-blue-600 mr-2"></i>
                                {{ trans('messages.location_and_department') }}
                            </h4>
                            
                            <dl class="space-y-3">
                                <!-- Departamento -->
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ trans('messages.department') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedResource->department->name ?? 'N/A' }}</dd>
                                </div>
                                
                                <!-- Localização -->
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ trans('messages.location') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedResource->location->name ?? 'N/A' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                    
                    <!-- Capacidade e Eficiência -->
                    <div class="mt-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-tachometer-alt text-blue-600 mr-2"></i>
                            {{ trans('messages.capacity_and_efficiency') }}
                        </h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Capacidade Nominal -->
                            <div class="bg-white p-3 rounded-lg border border-gray-200 text-center">
                                <div class="text-sm font-medium text-gray-500 mb-1">{{ trans('messages.nominal_capacity') }}</div>
                                <div class="text-lg font-semibold text-gray-900">
                                    {{ number_format($selectedResource->capacity, 2) }} 
                                    <span class="text-sm font-normal text-gray-500">{{ $selectedResource->capacity_uom }}</span>
                                </div>
                            </div>
                            
                            <!-- Fator de Eficiência -->
                            <div class="bg-white p-3 rounded-lg border border-gray-200 text-center">
                                <div class="text-sm font-medium text-gray-500 mb-1">{{ trans('messages.efficiency_factor') }}</div>
                                <div class="text-lg font-semibold text-gray-900">
                                    {{ $selectedResource->efficiency_factor }}%
                                </div>
                            </div>
                            
                            <!-- Capacidade Efetiva -->
                            <div class="bg-white p-3 rounded-lg border border-gray-200 text-center">
                                <div class="text-sm font-medium text-gray-500 mb-1">{{ trans('messages.effective_capacity') }}</div>
                                <div class="text-lg font-semibold text-blue-600">
                                    {{ number_format($selectedResource->capacity * $selectedResource->efficiency_factor / 100, 2) }} 
                                    <span class="text-sm font-normal text-gray-500">{{ $selectedResource->capacity_uom }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Informações do Sistema -->
                    <div class="mt-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-clipboard-list text-blue-600 mr-2"></i>
                            {{ trans('messages.system_information') }}
                        </h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <span class="text-sm text-gray-500">{{ trans('messages.created_by') }}:</span>
                                <span class="text-sm text-gray-900 ml-1">{{ $selectedResource->createdBy->name ?? 'N/A' }}</span>
                                <span class="text-xs text-gray-500 ml-1">
                                    {{ $selectedResource->created_at ? $selectedResource->created_at->format('d/m/Y H:i') : '' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">{{ trans('messages.updated_by') }}:</span>
                                <span class="text-sm text-gray-900 ml-1">{{ $selectedResource->updatedBy->name ?? 'N/A' }}</span>
                                <span class="text-xs text-gray-500 ml-1">
                                    {{ $selectedResource->updated_at ? $selectedResource->updated_at->format('d/m/Y H:i') : '' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botões de Ação -->
                    <div class="mt-6 flex flex-wrap justify-between items-center">
                        <div class="flex space-x-2 mb-2 sm:mb-0">
                            <button type="button" wire:click="edit({{ $selectedResource->id }})" 
                                class="inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                <i class="fas fa-edit mr-2"></i>
                                {{ trans('messages.edit') }}
                            </button>
                            
                            @if($selectedResource->active)
                                <button type="button" wire:click="toggleStatus({{ $selectedResource->id }})" wire:loading.attr="disabled"
                                    class="inline-flex justify-center items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed">
                                    <span wire:loading.remove wire:target="toggleStatus">
                                        <i class="fas fa-ban mr-2"></i>
                                        {{ trans('messages.deactivate') }}
                                    </span>
                                    <span wire:loading wire:target="toggleStatus" class="inline-flex items-center">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {{ trans('messages.deactivating') }}
                                    </span>
                                </button>
                            @else
                                <button type="button" wire:click="toggleStatus({{ $selectedResource->id }})" wire:loading.attr="disabled"
                                    class="inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed">
                                    <span wire:loading.remove wire:target="toggleStatus">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        {{ trans('messages.activate') }}
                                    </span>
                                    <span wire:loading wire:target="toggleStatus" class="inline-flex items-center">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {{ trans('messages.activating') }}
                                    </span>
                                </button>
                            @endif
                        </div>
                        
                        <div class="flex space-x-2">
                            <button type="button" wire:click="closeViewModal()" 
                                class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                <i class="fas fa-times-circle mr-2"></i>
                                {{ trans('messages.close') }}
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <div class="p-6 flex justify-center items-center">
                    <div class="flex flex-col items-center space-y-2">
                        <div class="flex-shrink-0 bg-gray-100 p-3 rounded-full">
                            <i class="fas fa-exclamation-circle text-gray-400 text-2xl"></i>
                        </div>
                        <p class="text-gray-500">{{ trans('messages.resource_not_found') }}</p>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end">
                    <button type="button" wire:click="closeViewModal()" 
                        class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <i class="fas fa-times-circle mr-2"></i>
                        {{ trans('messages.close') }}
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
