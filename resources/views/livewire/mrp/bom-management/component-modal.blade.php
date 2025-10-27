<!-- Modal para Criação/Edição de Componente -->
<div x-data="{ show: @entangle('showComponentModal') }"
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

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
            <!-- Cabeçalho do Modal com gradiente -->
            <div class="bg-gradient-to-r from-green-600 to-green-700 px-4 py-3">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-{{ $editMode ? 'edit' : 'plus-circle' }} mr-2"></i>
                    {{ $editMode ? __('messages.edit_component_in_bom') : __('messages.add_component_to_bom') }}
                </h3>
            </div>

            <!-- Formulário -->
            <form wire:submit.prevent="saveComponent">
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Componente -->
                        <div>
                            <label for="component_id" class="block text-sm font-medium text-gray-700">{{ __('messages.component') }} <span class="text-red-500">*</span></label>
                            <select id="component_id" wire:model.live="bomDetail.component_id" 
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                {{ $editMode ? 'disabled' : '' }}>
                                <option value="">{{ __('messages.select_component') }}</option>
                                @foreach($components as $component)
                                    <option value="{{ $component->id }}">
                                        {{ $component->name }} ({{ $component->sku }}) - 
                                        <span class="font-semibold {{ $component->total_quantity > 0 ? 'text-green-600' : 'text-red-600' }}">Stock: {{ number_format($component->total_quantity ?? 0, 2) }} {{ $component->unit_of_measure }}</span>
                                    </option>
                                @endforeach
                            </select>
                            @error('bomDetail.component_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Quantidade -->
                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700">{{ __('messages.component_quantity') }} <span class="text-red-500">*</span></label>
                            <input type="number" id="quantity" wire:model.live="bomDetail.quantity" min="0.0001" step="0.0001"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            @error('bomDetail.quantity') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Removed fields: level, position, scrap_percentage, uom -->

                        <!-- É Crítico -->
                        <div class="flex items-center space-x-2 md:col-span-2">
                            <input type="checkbox" id="is_critical" wire:model.live="bomDetail.is_critical"
                                class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <label for="is_critical" class="font-medium text-gray-700">
                                <span class="text-red-600 mr-1"><i class="fas fa-exclamation-circle"></i></span>
                                {{ __('messages.component_critical') }}
                            </label>
                            <span class="text-xs text-gray-500">{{ __('messages.component_critical_description') }}</span>
                        </div>

                        <!-- Observações -->
                        <div class="md:col-span-2">
                            <label for="component_notes" class="block text-sm font-medium text-gray-700">{{ __('messages.component_notes') }}</label>
                            <textarea id="component_notes" wire:model.live="bomDetail.notes" rows="3"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                            @error('bomDetail.notes') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6 flex justify-end space-x-2">
                    <button type="button" wire:click="closeModal" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <i class="fas fa-times-circle mr-2"></i>
                        {{ __('messages.cancel') }}
                    </button>
                    <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 ease-in-out transform hover:scale-105 relative"
                        wire:loading.attr="disabled"
                        wire:loading.class="bg-green-500 cursor-wait"
                        wire:target="saveComponent">
                        <span wire:loading.remove wire:target="saveComponent">
                            <i class="fas fa-save mr-2"></i>
                            {{ __('messages.save') }}
                        </span>
                        <span wire:loading wire:target="saveComponent" class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ __('messages.saving') }}...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
