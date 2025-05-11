<!-- Modal para Confirmar Exclusão de Previsão de Demanda -->
<div x-cloak
    class="fixed inset-0 z-30 flex items-center justify-center overflow-auto bg-gray-800 bg-opacity-75 transition-opacity"
    x-show="$wire.showDeleteModal"
    @keydown.escape.window="$wire.closeModal()">
    <div class="relative w-full max-w-md mx-auto my-8 px-4 sm:px-0"
        x-show="$wire.showDeleteModal"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="ease-in duration-200"
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
                <button @click="$wire.closeModal()" class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="p-6">
                <div class="flex flex-col items-center justify-center text-center space-y-4">
                    <div class="flex-shrink-0 bg-red-100 p-3 rounded-full">
                        <i class="fas fa-trash text-red-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">{{ __('messages.delete_forecast') }}</h3>
                    <p class="text-sm text-gray-500">
                        {{ __('messages.delete_forecast_confirmation') }}
                    </p>
                    <p class="text-sm text-red-600 font-medium">
                        {{ __('messages.action_cannot_be_undone') }}
                    </p>
                </div>
            </div>
            
            <!-- Rodapé do Modal -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-col sm:flex-row-reverse space-y-2 sm:space-y-0 sm:space-x-2 sm:space-x-reverse">
                <button wire:click="delete" 
                    class="inline-flex justify-center items-center w-full sm:w-auto px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out">
                    <i class="fas fa-trash-alt mr-2"></i>
                    {{ __('messages.delete') }}
                </button>
                <button wire:click="closeModal" 
                    class="inline-flex justify-center items-center w-full sm:w-auto px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out">
                    <i class="fas fa-times mr-2"></i>
                    {{ __('messages.cancel') }}
                </button>
            </div>
        </div>
    </div>
</div>
