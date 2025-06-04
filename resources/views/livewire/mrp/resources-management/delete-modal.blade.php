<!-- Modal de Confirmação de Exclusão -->
<div>
    <div x-data="{ open: @entangle('showDeleteModal') }" 
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
        <div class="relative top-20 mx-auto p-1 w-full max-w-md">
            <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" 
                 x-transition:enter="transition ease-out duration-300" 
                 x-transition:enter-start="transform opacity-0 scale-95" 
                 x-transition:enter-end="transform opacity-100 scale-100" 
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="transform opacity-100 scale-100" 
                 x-transition:leave-end="transform opacity-0 scale-95">
                <!-- Cabeçalho do Modal com gradiente e animação -->
                <div class="bg-gradient-to-r from-red-600 to-red-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2 animate-pulse"></i>
                        {{ trans('messages.confirm_delete') }}
                    </h3>
                    <button type="button" wire:click="$set('showDeleteModal', false)" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

            <div class="p-6">
                <div class="flex items-center justify-center mb-6">
                    <div class="bg-red-100 rounded-full p-3">
                        <i class="fas fa-trash text-red-600 text-3xl"></i>
                    </div>
                </div>
                
                <div class="text-center mb-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-2">{{ trans('messages.confirm_delete_question') }}</h4>
                    <p class="text-sm text-gray-500">
                        {{ trans('messages.delete_resource_warning') }}
                    </p>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4 mb-4 border border-gray-200">
                    <h5 class="text-sm font-medium text-gray-700 mb-2">{{ trans('messages.resource_details') }}</h5>
                    @if(isset($resourceToDelete))
                        <p class="text-sm text-gray-900"><span class="font-medium">{{ trans('messages.name') }}:</span> {{ $resourceToDelete->name }}</p>
                        <p class="text-sm text-gray-900"><span class="font-medium">{{ trans('messages.type') }}:</span> {{ $resourceToDelete->resourceType->name ?? 'N/A' }}</p>
                    @else
                        <p class="text-sm text-gray-500">{{ __('Dados não disponíveis') }}</p>
                    @endif
                </div>
                
                <div class="px-4 py-3 bg-gray-50 rounded-b-lg flex justify-center space-x-4 border-t border-gray-200">
                    <button type="button" wire:click="$set('showDeleteModal', false)" 
                        class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <i class="fas fa-times-circle mr-2"></i>
                        {{ trans('messages.cancel') }}
                    </button>
                    <button type="button" wire:click="delete" wire:loading.attr="disabled"
                        class="inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="delete">
                            <i class="fas fa-trash-alt mr-2"></i>
                            {{ trans('messages.delete') }}
                        </span>
                        <span wire:loading wire:target="delete" class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ trans('messages.deleting') }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão de Tipo de Recurso -->
<div>
    <div x-data="{ open: @entangle('showDeleteTypeModal') }" 
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
        <div class="relative top-20 mx-auto p-1 w-full max-w-md">
            <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" 
                 x-transition:enter="transition ease-out duration-300" 
                 x-transition:enter-start="transform opacity-0 scale-95" 
                 x-transition:enter-end="transform opacity-100 scale-100" 
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="transform opacity-100 scale-100" 
                 x-transition:leave-end="transform opacity-0 scale-95">
                <!-- Cabeçalho do Modal com gradiente e animação -->
                <div class="bg-gradient-to-r from-red-600 to-red-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2 animate-pulse"></i>
                        {{ trans('messages.confirm_delete_type') }}
                    </h3>
                    <button type="button" wire:click="$set('showDeleteTypeModal', false)" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

            <div class="p-6">
                <div class="flex items-center justify-center mb-6">
                    <div class="bg-red-100 rounded-full p-3">
                        <i class="fas fa-trash text-red-600 text-3xl"></i>
                    </div>
                </div>
                
                <div class="text-center mb-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-2">{{ trans('messages.confirm_resource_type_delete_question') }}</h4>
                    <p class="text-sm text-gray-500">
                        {{ trans('messages.delete_resource_type_warning') }}
                    </p>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4 mb-4 border border-gray-200">
                    <h5 class="text-sm font-medium text-gray-700 mb-2">{{ trans('messages.resource_type_details') }}</h5>
                    @if(isset($resourceTypeToDelete))
                        <p class="text-sm text-gray-900"><span class="font-medium">{{ __('Nome') }}:</span> {{ $resourceTypeToDelete->name }}</p>
                    @else
                        <p class="text-sm text-gray-500">{{ __('Dados não disponíveis') }}</p>
                    @endif
                </div>
                
                <div class="px-4 py-3 bg-gray-50 rounded-b-lg flex justify-center space-x-4 border-t border-gray-200">
                    <button type="button" wire:click="$set('showDeleteTypeModal', false)" 
                        class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <i class="fas fa-times-circle mr-2"></i>
                        {{ trans('messages.cancel') }}
                    </button>
                    <button type="button" wire:click="deleteResourceType" wire:loading.attr="disabled"
                        class="inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="deleteResourceType">
                            <i class="fas fa-trash-alt mr-2"></i>
                            {{ trans('messages.delete') }}
                        </span>
                        <span wire:loading wire:target="deleteResourceType" class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ trans('messages.deleting') }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
