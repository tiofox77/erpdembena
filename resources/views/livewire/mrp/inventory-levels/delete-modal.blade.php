<!-- Modal para Confirmar Exclusão de Nível de Estoque -->
<div x-data="{ open: @entangle('showDeleteModal') }"
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
    @keydown.escape.window="$wire.closeModal()">
    <div class="relative w-full max-w-md mx-auto my-8 px-4 sm:px-0"
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        @click.away="$wire.closeModal()">
        <div class="relative bg-white rounded-lg shadow-xl overflow-hidden" @click.stop>
            <!-- Cabeçalho do Modal -->
            <div class="bg-gradient-to-r from-red-600 to-red-700 px-4 py-4 sm:px-6 flex justify-between items-center">
                <h3 class="text-lg font-medium text-white">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    {{ __('messages.confirm_deletion') }}
                </h3>
                <button @click="$wire.closeModal()" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="p-6">
                <div class="flex flex-col items-center justify-center text-center space-y-4">
                    <div class="flex-shrink-0 bg-red-100 p-3 rounded-full animate-pulse">
                        <i class="fas fa-trash text-red-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">{{ __('messages.delete_inventory_level') }}</h3>
                    <p class="text-sm text-gray-500">
                        {{ __('messages.delete_inventory_level_confirmation') }}
                    </p>
                    <p class="text-sm text-red-600 font-medium">
                        {{ __('messages.action_cannot_be_undone') }}
                    </p>
                </div>
                
                @if($selectedLevel)
                    <div class="mt-6 bg-gray-50 p-4 rounded-md">
                        <div class="text-sm text-gray-700">
                            <p class="font-medium">{{ __('messages.product') }}: <span class="font-normal">{{ $selectedLevel->product->name ?? 'N/A' }}</span></p>
                            <p class="font-medium mt-1">{{ __('messages.current_stock') }}: <span class="font-normal">{{ number_format($selectedLevel->current_stock, 2) }} {{ $selectedLevel->uom }}</span></p>
                        </div>
                    </div>
                    
                    <!-- Aviso sobre dados associados -->
                    <div class="mt-4 bg-yellow-50 p-4 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-yellow-600"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">{{ __('messages.important_note') }}</h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>
                                        {{ __('messages.delete_inventory_level_warning') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            
            <!-- Rodapé do Modal -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-col sm:flex-row-reverse space-y-2 sm:space-y-0 sm:space-x-2 sm:space-x-reverse">
                <button wire:click="delete" 
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-75 cursor-wait" 
                    class="inline-flex justify-center items-center w-full sm:w-auto px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="delete">
                        <i class="fas fa-trash-alt mr-2"></i>
                        {{ __('messages.delete') }}
                    </span>
                    <span wire:loading wire:target="delete">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        {{ __('messages.deleting') }}...
                    </span>
                </button>
                <button wire:click="closeModal" 
                    wire:loading.attr="disabled" 
                    wire:loading.class="opacity-75 cursor-wait"
                    class="inline-flex justify-center items-center w-full sm:w-auto px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                    <i class="fas fa-times mr-2"></i>
                    {{ __('messages.cancel') }}
                </button>
            </div>
        </div>
    </div>
</div>
