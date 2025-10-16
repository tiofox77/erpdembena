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
                
                <!-- Cabeçalho com gradiente -->
                <div class="bg-gradient-to-r from-red-600 to-red-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2 animate-pulse"></i>
                        {{ __('messages.confirm_deletion') }}
                    </h3>
                    <button type="button" wire:click="closeDeleteModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-trash text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">{{ __('messages.delete_advance') }}</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    {{ __('messages.delete_advance_confirm_message') }}
                                </p>
                            </div>
                            
                            @if($advanceToDelete)
                            <div class="mt-3 bg-gray-50 p-3 border border-gray-200 rounded-md">
                                <div class="grid grid-cols-1 gap-2">
                                    <div>
                                        <h4 class="text-xs font-medium text-gray-500">{{ __('messages.employee') }}</h4>
                                        <p class="text-sm font-medium text-gray-800">{{ $advanceToDelete->employee?->full_name ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-medium text-gray-500">{{ __('messages.amount') }}</h4>
                                        <p class="text-sm font-medium text-gray-800">{{ number_format($advanceToDelete->amount ?? 0, 2) }} Kz</p>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-medium text-gray-500">{{ __('messages.request_date') }}</h4>
                                        <p class="text-sm font-medium text-gray-800">{{ $advanceToDelete->request_date ? $advanceToDelete->request_date->format('d/m/Y') : '-' }}</p>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-medium text-gray-500">{{ __('messages.status') }}</h4>
                                        <p class="text-sm font-medium text-gray-800">
                                            @if($advanceToDelete->status === 'pending')
                                                {{ __('messages.pending') }}
                                            @elseif($advanceToDelete->status === 'approved')
                                                {{ __('messages.approved') }}
                                            @elseif($advanceToDelete->status === 'completed')
                                                {{ __('messages.completed') }}
                                            @else
                                                {{ __('messages.rejected') }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                    <button type="button" wire:click="closeDeleteModal" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <i class="fas fa-times mr-2"></i>
                        {{ __('messages.cancel') }}
                    </button>
                    <button type="button" wire:click="delete" wire:loading.attr="disabled" class="inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="delete">
                            <i class="fas fa-trash mr-2"></i>
                            {{ __('messages.confirm_delete') }}
                        </span>
                        <span wire:loading wire:target="delete" class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ __('messages.processing') }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
