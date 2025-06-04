<!-- Modal para Confirmação de Exclusão de Componente -->
<div x-data="{ show: @entangle('showDeleteComponentModal') }"
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

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <!-- Cabeçalho do Modal com gradiente -->
            <div class="bg-gradient-to-r from-red-600 to-red-700 px-4 py-3">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    {{ __('messages.confirm_component_deletion') }}
                </h3>
            </div>

            <!-- Conteúdo do Modal -->
            <div class="p-6">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <i class="fas fa-puzzle-piece text-red-600 text-xl animate-pulse"></i>
                </div>
                <div class="text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-headline">
                        {{ __('messages.remove_component') }}?
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            {{ __('messages.delete_component_warning') }}
                        </p>
                        
                        @if($bomDetailId)
                            @php
                                $component = App\Models\Mrp\BomDetail::with('component')->find($bomDetailId);
                            @endphp
                            @if($component && $component->component)
                                <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                                    <div class="text-sm text-left">
                                        <p class="font-medium">{{ __('messages.component_to_be_removed') }}:</p>
                                        <p class="mt-1"><span class="font-medium">{{ __('messages.name') }}:</span> {{ $component->component->name }}</p>
                                        <p><span class="font-medium">{{ __('messages.sku') }}:</span> {{ $component->component->sku }}</p>
                                        <p><span class="font-medium">{{ __('messages.quantity') }}:</span> {{ number_format($component->quantity, 4) }} {{ $component->uom }}</p>
                                        @if($component->is_critical)
                                            <p class="mt-2 text-red-600 flex items-center">
                                                <i class="fas fa-exclamation-circle mr-1"></i>
                                                {{ __('messages.this_is_critical_component') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="px-4 py-3 bg-gray-50 sm:px-6 flex justify-end space-x-2">
                <button type="button" wire:click="closeModal" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                    <i class="fas fa-times-circle mr-2"></i>
                    {{ __('messages.cancel') }}
                </button>
                <button type="button" wire:click="deleteComponent" 
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                    <i class="fas fa-trash-alt mr-2"></i>
                    {{ __('messages.remove') }}
                </button>
            </div>
        </div>
    </div>
</div>
