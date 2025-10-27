<!-- Modal para Criar ou Editar Previsão de Demanda -->
<div x-cloak
    class="fixed inset-0 z-30 flex items-center justify-center overflow-auto bg-gray-800 bg-opacity-75 transition-all duration-300 ease-in-out"
    x-show="$wire.showModal"
    @keydown.escape.window="$wire.closeModal()">
    <div class="relative w-full max-w-2xl mx-auto my-8 px-4 sm:px-0"
        x-show="$wire.showModal"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        @click.away="$wire.closeModal()">
        <div class="relative bg-white rounded-lg shadow-xl overflow-hidden" @click.stop>
            <!-- Cabeçalho do Modal -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-4 sm:px-6 flex justify-between items-center">
                <h3 class="text-lg font-medium text-white">
                    <i class="fas {{ $editMode ? 'fa-edit' : 'fa-plus-circle' }} mr-2"></i>
                    {{ $editMode ? __('messages.edit_forecast') : __('messages.add_forecast') }}
                </h3>
                <button @click="$wire.closeModal()" class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Formulário -->
            <form wire:submit.prevent="save">
                <div class="px-4 py-5 sm:p-6 space-y-6">
                    <!-- Seção Dados Básicos -->
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
                        <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-chart-line text-blue-600 mr-2"></i>
                            {{ __('messages.forecast_information') }}
                        </h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Produto -->
                            <div>
                                <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.product') }} <span class="text-red-600">*</span>
                                </label>
                                <div class="relative">
                                    <select wire:model.live="forecast.product_id" id="product_id" 
                                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('forecast.product_id') border-red-500 @enderror">
                                        <option value="">{{ __('messages.select_product') }}</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-box text-gray-400"></i>
                                    </div>
                                </div>
                                @error('forecast.product_id')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Data da Previsão -->
                            <div>
                                <label for="forecast_date" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.forecast_date') }} <span class="text-red-600">*</span>
                                </label>
                                <div class="relative">
                                    <input type="date" wire:model.live="forecast.forecast_date" id="forecast_date" 
                                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('forecast.forecast_date') border-red-500 @enderror">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-calendar-alt text-gray-400"></i>
                                    </div>
                                </div>
                                @error('forecast.forecast_date')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Quantidade Prevista -->
                            <div>
                                <label for="forecast_quantity" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.forecast_quantity') }} <span class="text-red-600">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number" wire:model.live="forecast.forecast_quantity" id="forecast_quantity" min="0" step="1"
                                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('forecast.forecast_quantity') border-red-500 @enderror"
                                        placeholder="0">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-hashtag text-gray-400"></i>
                                    </div>
                                </div>
                                @error('forecast.forecast_quantity')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Nível de Confiança -->
                            <div>
                                <label for="confidence_level" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.confidence_level') }} (%)
                                </label>
                                <div class="relative">
                                    <input type="number" wire:model.live="forecast.confidence_level" id="confidence_level" min="0" max="100" step="0.01"
                                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('forecast.confidence_level') border-red-500 @enderror"
                                        placeholder="0-100">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-percentage text-gray-400"></i>
                                    </div>
                                </div>
                                @error('forecast.confidence_level')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">{{ __('messages.confidence_level_help') }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Seção Adicional -->
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
                        <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-clipboard-list text-blue-600 mr-2"></i>
                            {{ __('messages.additional_information') }}
                        </h4>
                        
                        <div class="grid grid-cols-1 gap-4">
                            <!-- Tipo de Previsão -->
                            <div>
                                <label for="forecast_type" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.forecast_type') }} <span class="text-red-600">*</span>
                                </label>
                                <div class="relative">
                                    <select wire:model.live="forecast.forecast_type" id="forecast_type" 
                                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('forecast.forecast_type') border-red-500 @enderror">
                                        @foreach($forecastTypes as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-tag text-gray-400"></i>
                                    </div>
                                </div>
                                @error('forecast.forecast_type')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Notas -->
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.notes') }}
                                </label>
                                <div class="relative">
                                    <textarea wire:model.live="forecast.notes" id="notes" rows="3" 
                                        class="block w-full pl-3 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('forecast.notes') border-red-500 @enderror"
                                        placeholder="{{ __('messages.forecast_notes_placeholder') }}"></textarea>
                                </div>
                                @error('forecast.notes')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Rodapé do Modal -->
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-col sm:flex-row justify-end gap-2">
                    <button type="button" wire:click="closeModal" 
                        class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-70 cursor-wait">
                        <i class="fas fa-times mr-2"></i>
                        {{ __('messages.cancel') }}
                    </button>
                    <button type="submit" 
                        class="inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out"
                        wire:loading.attr="disabled"
                        wire:target="save"
                        wire:loading.class="opacity-70 cursor-wait">
                        <span wire:loading.remove wire:target="save">
                            <i class="fas {{ $editMode ? 'fa-save' : 'fa-plus-circle' }} mr-2"></i>
                            {{ $editMode ? __('messages.update') : __('messages.save') }}
                        </span>
                        <span wire:loading wire:target="save" class="inline-flex items-center">
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
