<!-- Modal para Criar/Editar Nível de Estoque -->
<div x-data="{ open: @entangle('showModal') }"
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
    <div class="relative w-full max-w-2xl mx-auto my-8 px-4 sm:px-0"
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
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-4 sm:px-6 flex justify-between items-center">
                <h3 class="text-lg font-medium text-white">
                    <i class="fas {{ $editMode ? 'fa-edit' : 'fa-plus-circle' }} mr-2"></i>
                    {{ $editMode ? __('messages.edit_inventory_level') : __('messages.add_inventory_level') }}
                </h3>
                <button @click="$wire.closeModal()" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form wire:submit.prevent="save">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Produto -->
                        <div class="col-span-2">
                            <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.product') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select id="product_id" wire:model.live="inventoryLevel.product_id" {{ $editMode ? 'disabled' : '' }}
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md bg-white
                                    {{ $editMode ? 'bg-gray-100' : '' }}">
                                    <option value="">{{ __('messages.select_product') }}</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                    @endforeach
                                </select>
                                @if($editMode)
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-lock text-gray-400"></i>
                                    </div>
                                @endif
                            </div>
                            @error('inventoryLevel.product_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Estoque Atual -->
                        <div>
                            <label for="current_stock" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.current_stock') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number" id="current_stock" wire:model.live="inventoryLevel.current_stock" step="0.01" min="0"
                                    class="focus:ring-blue-500 focus:border-blue-500 block w-full pr-12 sm:text-sm border-gray-300 rounded-md" 
                                    placeholder="0.00">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">{{ $uom }}</span>
                                </div>
                            </div>
                            @error('inventoryLevel.current_stock')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Unidade de Medida -->
                        <div>
                            <label for="uom" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.uom') }} <span class="text-red-500">*</span>
                            </label>
                            <select id="uom" wire:model.live="inventoryLevel.uom"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                <option value="">{{ __('messages.select_uom') }}</option>
                                @foreach($unitTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('inventoryLevel.uom')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Estoque Mínimo -->
                        <div>
                            <label for="min_stock" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.min_stock') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number" id="min_stock" wire:model.live="inventoryLevel.min_stock" step="0.01" min="0"
                                    class="focus:ring-blue-500 focus:border-blue-500 block w-full pr-12 sm:text-sm border-gray-300 rounded-md" 
                                    placeholder="0.00">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">{{ $uom }}</span>
                                </div>
                            </div>
                            @error('inventoryLevel.min_stock')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Ponto de Reposição -->
                        <div>
                            <label for="reorder_point" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.reorder_point') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number" id="reorder_point" wire:model.live="inventoryLevel.reorder_point" step="0.01" min="0"
                                    class="focus:ring-blue-500 focus:border-blue-500 block w-full pr-12 sm:text-sm border-gray-300 rounded-md" 
                                    placeholder="0.00">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">{{ $uom }}</span>
                                </div>
                            </div>
                            @error('inventoryLevel.reorder_point')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Estoque Máximo -->
                        <div>
                            <label for="max_stock" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.max_stock') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number" id="max_stock" wire:model.live="inventoryLevel.max_stock" step="0.01" min="0"
                                    class="focus:ring-blue-500 focus:border-blue-500 block w-full pr-12 sm:text-sm border-gray-300 rounded-md" 
                                    placeholder="0.00">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">{{ $uom }}</span>
                                </div>
                            </div>
                            @error('inventoryLevel.max_stock')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Lead Time (dias) -->
                        <div>
                            <label for="lead_time" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.lead_time') }}
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number" id="lead_time" wire:model.live="inventoryLevel.lead_time_days" step="1" min="0"
                                    class="focus:ring-blue-500 focus:border-blue-500 block w-full pr-12 sm:text-sm border-gray-300 rounded-md" 
                                    placeholder="0">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">{{ __('messages.days') }}</span>
                                </div>
                            </div>
                            @error('inventoryLevel.lead_time_days')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Localização -->
                        <div class="col-span-2">
                            <label for="location_id" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.location') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select id="location_id" wire:model.live="inventoryLevel.location_id" {{ $editMode ? 'disabled' : '' }}
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md bg-white
                                    {{ $editMode ? 'bg-gray-100' : '' }}">
                                    <option value="">{{ __('messages.select_location') }}</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                                    @endforeach
                                </select>
                                @if($editMode)
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-lock text-gray-400"></i>
                                    </div>
                                @endif
                            </div>
                            @error('inventoryLevel.location_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notas -->
                        <div class="col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.notes') }}
                            </label>
                            <textarea id="notes" wire:model.live="inventoryLevel.notes" rows="3"
                                class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md bg-white" 
                                placeholder="{{ __('messages.inventory_notes_placeholder') }}"></textarea>
                            @error('inventoryLevel.notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Rodapé do Modal -->
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-col sm:flex-row-reverse space-y-2 sm:space-y-0 sm:space-x-2 sm:space-x-reverse">
                    <button type="submit" wire:loading.attr="disabled" 
                        class="inline-flex justify-center items-center w-full sm:w-auto px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="save">
                            <i class="fas {{ $editMode ? 'fa-save' : 'fa-plus-circle' }} mr-2"></i>
                            {{ $editMode ? __('messages.save_changes') : __('messages.create_inventory_level') }}
                        </span>
                        <span wire:loading wire:target="save">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            {{ __('messages.saving') }}...
                        </span>
                    </button>
                    <button type="button" wire:click="closeModal" wire:loading.attr="disabled" 
                        class="inline-flex justify-center items-center w-full sm:w-auto px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <i class="fas fa-times mr-2"></i>
                        {{ __('messages.cancel') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
