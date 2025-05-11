<!-- Modal para Criação/Edição de BOM -->
<div x-data="{ show: @entangle('showModal') }"
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
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-{{ $editMode ? 'edit' : 'plus-circle' }} mr-2"></i>
                    {{ $editMode ? __('messages.edit') : __('messages.new') }} {{ __('messages.bill_of_materials') }}
                </h3>
            </div>

            <!-- Formulário -->
            <form wire:submit.prevent="saveBomHeader">
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Seção Informações Básicas -->
                        <div class="md:col-span-2">
                            <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-2 rounded-lg mb-4">
                                <h4 class="text-base font-medium text-gray-700 flex items-center">
                                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                    {{ __('messages.basic_information') }}
                                </h4>
                            </div>
                        </div>

                        <!-- Produto -->
                        <div>
                            <label for="product_id" class="block text-sm font-medium text-gray-700">{{ __('messages.product') }} <span class="text-red-500">*</span></label>
                            <select id="product_id" wire:model.live="bomHeader.product_id" wire:change="generateBomNumber"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                {{ $editMode ? 'disabled' : '' }}>
                                <option value="">{{ __('messages.select_product') }}</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                @endforeach
                            </select>
                            @error('bomHeader.product_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Número da BOM -->
                        <div>
                            <label for="bom_number" class="block text-sm font-medium text-gray-700">{{ __('messages.bom_number') }} <span class="text-red-500">*</span></label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="text" id="bom_number" wire:model.live="bomHeader.bom_number" 
                                    class="block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    {{ $editMode ? 'readonly' : '' }}>
                                @if(!$editMode)
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                        <button type="button" wire:click="generateBomNumber" class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                            @error('bomHeader.bom_number') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Descrição -->
                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700">{{ __('messages.description') }} <span class="text-red-500">*</span></label>
                            <input type="text" id="description" wire:model.live="bomHeader.description" 
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            @error('bomHeader.description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Versão -->
                        <div>
                            <label for="version" class="block text-sm font-medium text-gray-700">{{ __('messages.version') }} <span class="text-red-500">*</span></label>
                            <input type="number" id="version" wire:model.live="bomHeader.version" min="1" step="1"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            @error('bomHeader.version') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- UOM (Unidade de Medida) -->
                        <div>
                            <label for="uom" class="block text-sm font-medium text-gray-700">{{ __('messages.uom') }} <span class="text-red-500">*</span></label>
                            <select id="uom" wire:model.live="bomHeader.uom" 
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                @foreach($unitTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('bomHeader.uom') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Seção Status e Datas -->
                        <div class="md:col-span-2">
                            <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-2 rounded-lg mb-4">
                                <h4 class="text-base font-medium text-gray-700 flex items-center">
                                    <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>
                                    {{ __('messages.status_and_dates') }}
                                </h4>
                            </div>
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">{{ __('messages.status') }} <span class="text-red-500">*</span></label>
                            <select id="status" wire:model.live="bomHeader.status" 
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="draft">{{ __('messages.draft') }}</option>
                                <option value="active">{{ __('messages.active') }}</option>
                                <option value="obsolete">{{ __('messages.obsolete') }}</option>
                            </select>
                            @error('bomHeader.status') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Separador para manter o layout -->
                        <div></div>

                        <!-- Data de Efetividade -->
                        <div>
                            <label for="effective_date" class="block text-sm font-medium text-gray-700">{{ __('messages.effective_date') }} <span class="text-red-500">*</span></label>
                            <input type="date" id="effective_date" wire:model.live="bomHeader.effective_date" 
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            @error('bomHeader.effective_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Data de Expiração -->
                        <div>
                            <label for="expiration_date" class="block text-sm font-medium text-gray-700">{{ __('messages.expiration_date') }}</label>
                            <input type="date" id="expiration_date" wire:model.live="bomHeader.expiration_date" 
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            @error('bomHeader.expiration_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Observações -->
                        <div class="md:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700">{{ __('messages.notes') }}</label>
                            <textarea id="notes" wire:model.live="bomHeader.notes" rows="3"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                            @error('bomHeader.notes') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
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
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 relative"
                        wire:loading.attr="disabled"
                        wire:loading.class="bg-blue-500 cursor-wait"
                        wire:target="saveBomHeader">
                        <span wire:loading.remove wire:target="saveBomHeader">
                            <i class="fas fa-save mr-2"></i>
                            {{ __('messages.save') }}
                        </span>
                        <span wire:loading wire:target="saveBomHeader" class="inline-flex items-center">
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
