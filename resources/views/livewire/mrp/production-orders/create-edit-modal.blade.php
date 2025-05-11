<!-- Modal de Criação/Edição de Ordem de Produção -->
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

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <!-- Cabeçalho do Modal com gradiente -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-{{ $editMode ? 'edit' : 'plus-circle' }} mr-2"></i>
                    {{ $editMode ? __('messages.edit_production_order') : __('messages.new_production_order') }}
                </h3>
            </div>

            <!-- Formulário -->
            <form wire:submit.prevent="save">
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Seção Informações Básicas -->
                        <div class="md:col-span-3">
                            <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-2 rounded-lg mb-4">
                                <h4 class="text-base font-medium text-gray-700 flex items-center">
                                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                    {{ __('messages.basic_information') }}
                                </h4>
                            </div>
                        </div>

                        <!-- Número da Ordem -->
                        <div>
                            <label for="order_number" class="block text-sm font-medium text-gray-700">{{ __('messages.order_number') }} <span class="text-red-500">*</span></label>
                            <input type="text" id="order_number" wire:model="order.order_number" 
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                {{ $editMode ? 'readonly' : '' }}>
                            @error('order.order_number') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Produto -->
                        <div>
                            <label for="product_id" class="block text-sm font-medium text-gray-700">{{ __('messages.product') }} <span class="text-red-500">*</span></label>
                            <select id="product_id" wire:model="order.product_id" 
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">{{ __('messages.select_product') }}</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                @endforeach
                            </select>
                            @error('order.product_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- BOM -->
                        <div>
                            <label for="bom_header_id" class="block text-sm font-medium text-gray-700">{{ __('messages.bill_of_materials') }}</label>
                            <select id="bom_header_id" wire:model="order.bom_header_id" 
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">{{ __('messages.select_bom') }}</option>
                                @foreach($availableBoms as $bom)
                                    <option value="{{ $bom['id'] }}">V{{ $bom['version'] }} - {{ $bom['description'] }}</option>
                                @endforeach
                            </select>
                            @error('order.bom_header_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Seção de Planejamento -->
                        <div class="md:col-span-3">
                            <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-2 rounded-lg mb-4">
                                <h4 class="text-base font-medium text-gray-700 flex items-center">
                                    <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>
                                    {{ __('messages.planning') }}
                                </h4>
                            </div>
                        </div>

                        <!-- Programação -->
                        <div>
                            <label for="schedule_id" class="block text-sm font-medium text-gray-700">{{ __('messages.production_schedule') }}</label>
                            <select id="schedule_id" wire:model="order.schedule_id" 
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">{{ __('messages.select_schedule') }}</option>
                                @foreach($availableSchedules as $schedule)
                                    <option value="{{ $schedule['id'] }}">{{ $schedule['description'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Data Início Planejada -->
                        <div>
                            <label for="planned_start_date" class="block text-sm font-medium text-gray-700">{{ __('messages.planned_start_date') }} <span class="text-red-500">*</span></label>
                            <input type="date" id="planned_start_date" wire:model="order.planned_start_date" 
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            @error('order.planned_start_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Data Fim Planejada -->
                        <div>
                            <label for="planned_end_date" class="block text-sm font-medium text-gray-700">{{ __('messages.planned_end_date') }} <span class="text-red-500">*</span></label>
                            <input type="date" id="planned_end_date" wire:model="order.planned_end_date" 
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            @error('order.planned_end_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Data Início Real -->
                        <div>
                            <label for="actual_start_date" class="block text-sm font-medium text-gray-700">{{ __('messages.actual_start_date') }}</label>
                            <input type="date" id="actual_start_date" wire:model="order.actual_start_date" 
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            @error('order.actual_start_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Data Fim Real -->
                        <div>
                            <label for="actual_end_date" class="block text-sm font-medium text-gray-700">{{ __('messages.actual_end_date') }}</label>
                            <input type="date" id="actual_end_date" wire:model="order.actual_end_date" 
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            @error('order.actual_end_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Localização -->
                        <div>
                            <label for="location_id" class="block text-sm font-medium text-gray-700">{{ __('messages.location') }}</label>
                            <select id="location_id" wire:model="order.location_id" 
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">{{ __('messages.select_location') }}</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Seção de Quantidades e Status -->
                        <div class="md:col-span-3">
                            <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-2 rounded-lg mb-4">
                                <h4 class="text-base font-medium text-gray-700 flex items-center">
                                    <i class="fas fa-clipboard-check text-blue-500 mr-2"></i>
                                    {{ __('messages.quantities_and_status') }}
                                </h4>
                            </div>
                        </div>

                        <!-- Quantidade Planejada -->
                        <div>
                            <label for="planned_quantity" class="block text-sm font-medium text-gray-700">{{ __('messages.planned_quantity') }} <span class="text-red-500">*</span></label>
                            <input type="number" id="planned_quantity" wire:model="order.planned_quantity" step="0.01" min="0.01"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            @error('order.planned_quantity') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Quantidade Produzida -->
                        <div>
                            <label for="produced_quantity" class="block text-sm font-medium text-gray-700">{{ __('messages.produced_quantity') }}</label>
                            <input type="number" id="produced_quantity" wire:model="order.produced_quantity" step="0.01" min="0"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            @error('order.produced_quantity') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Quantidade Rejeitada -->
                        <div>
                            <label for="rejected_quantity" class="block text-sm font-medium text-gray-700">{{ __('messages.rejected_quantity') }}</label>
                            <input type="number" id="rejected_quantity" wire:model="order.rejected_quantity" step="0.01" min="0"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            @error('order.rejected_quantity') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">{{ __('messages.status') }} <span class="text-red-500">*</span></label>
                            <select id="status" wire:model="order.status" 
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                @foreach($statuses as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('order.status') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Prioridade -->
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700">{{ __('messages.priority') }} <span class="text-red-500">*</span></label>
                            <select id="priority" wire:model="order.priority" 
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                @foreach($priorities as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('order.priority') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Observações -->
                        <div class="md:col-span-3">
                            <label for="notes" class="block text-sm font-medium text-gray-700">{{ __('messages.notes') }}</label>
                            <textarea id="notes" wire:model="order.notes" rows="3"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                            @error('order.notes') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
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
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <i class="fas fa-save mr-2"></i>
                        {{ __('messages.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
