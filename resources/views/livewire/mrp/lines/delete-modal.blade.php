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
                
                <!-- Cabeçalho com gradiente vermelho -->
                <div class="bg-gradient-to-r from-red-600 to-red-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-trash mr-2 animate-pulse"></i>
                        {{ __('messages.delete_line') }}
                    </h3>
                    <button type="button" wire:click="closeDeleteModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <!-- Corpo do modal de exclusão -->
                <div class="p-6">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                            <i class="fas fa-exclamation-triangle text-red-600 text-lg"></i>
                        </div>
                        
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            {{ __('messages.confirm_delete_line') }}
                        </h3>
                        
                        @if($lineToDelete)
                            <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-lg font-medium text-gray-700">{{ $lineToDelete->name }}</span>
                                    <span class="text-sm text-gray-500">{{ $lineToDelete->code }}</span>
                                </div>
                                <p class="text-sm text-gray-500">
                                    {{ $lineToDelete->location ? $lineToDelete->location->name : __('messages.not_assigned') }}
                                </p>
                                @if($lineToDelete->capacity_per_hour)
                                    <p class="text-sm text-gray-500 mt-1">
                                        {{ __('messages.capacity') }}: {{ number_format($lineToDelete->capacity_per_hour, 1) }} {{ __('messages.units_per_hour') }}
                                    </p>
                                @endif
                            </div>
                        @endif
                        
                        <p class="text-sm text-gray-500 mt-4">
                            {{ __('messages.delete_line_warning') }}
                        </p>
                    </div>
                    
                    <!-- Botões de Ação -->
                    <div class="mt-6 flex justify-center gap-3">
                        <button type="button" wire:click="closeDeleteModal" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                            <i class="fas fa-times mr-2"></i>
                            {{ __('messages.cancel') }}
                        </button>
                        
                        @if(!$confirmDelete)
                            <button type="button" wire:click="delete" 
                                class="inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                <i class="fas fa-trash mr-2"></i>
                                {{ __('messages.delete') }}
                            </button>
                        @else
                            <button type="button" wire:click="delete" wire:loading.attr="disabled"
                                class="inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                <i class="fas fa-check mr-2"></i>
                                {{ __('messages.confirm_delete') }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
