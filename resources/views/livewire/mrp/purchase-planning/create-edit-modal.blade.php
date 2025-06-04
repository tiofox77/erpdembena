<!-- Modal para Criar ou Editar Plano de Compra -->
<div x-data="{ 
        calculateTotal() {
            if (this.$wire.plan.required_quantity && this.$wire.plan.unit_price) {
                return (parseFloat(this.$wire.plan.required_quantity) * parseFloat(this.$wire.plan.unit_price)).toFixed(2);
            }
            return 0;
        }
    }" 
    x-cloak
    class="fixed inset-0 z-30 flex items-center justify-center overflow-auto bg-gray-800 bg-opacity-75 transition-all duration-300 ease-in-out"
    x-show="$wire.showModal"
    @keydown.escape.window="$wire.closeModal()">
    <div class="relative w-full max-w-4xl mx-auto my-8 px-4 sm:px-0"
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
                    {{ $editMode ? __('messages.edit_purchase_plan') : __('messages.add_purchase_plan') }}
                </h3>
                <button @click="$wire.closeModal()" class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Formulário -->
            <form wire:submit.prevent="save">
                <div class="px-4 py-5 sm:p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Seção de Produto e Fornecedor -->
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
                            <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-box text-blue-600 mr-2"></i>
                                {{ __('messages.product_and_supplier') }}
                            </h4>
                            
                            <!-- Produto -->
                            <div class="mb-4">
                                <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.product') }} <span class="text-red-600">*</span>
                                </label>
                                <div class="relative">
                                    <select wire:model="plan.product_id" id="product_id" 
                                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('plan.product_id') border-red-500 @enderror">
                                        <option value="">{{ __('messages.select_product') }}</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-box text-gray-400"></i>
                                    </div>
                                </div>
                                @error('plan.product_id')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Fornecedor -->
                            <div class="mb-4">
                                <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.supplier') }}
                                </label>
                                <div class="relative">
                                    <select wire:model="plan.supplier_id" id="supplier_id" 
                                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('plan.supplier_id') border-red-500 @enderror">
                                        <option value="">{{ __('messages.select_supplier') }}</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-truck text-gray-400"></i>
                                    </div>
                                </div>
                                @error('plan.supplier_id')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Data Necessária -->
                            <div class="mb-4">
                                <label for="required_date" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.required_date') }} <span class="text-red-600">*</span>
                                </label>
                                <div class="relative">
                                    <input type="date" wire:model="plan.required_date" id="required_date"
                                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('plan.required_date') border-red-500 @enderror">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-calendar-alt text-gray-400"></i>
                                    </div>
                                </div>
                                @error('plan.required_date')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Lead Time -->
                            <div>
                                <label for="lead_time" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.lead_time') }}
                                </label>
                                <div class="relative">
                                    <input type="number" wire:model="plan.lead_time" id="lead_time" min="0"
                                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('plan.lead_time') border-red-500 @enderror"
                                        placeholder="Ex: 7">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-clock text-gray-400"></i>
                                    </div>
                                </div>
                                @error('plan.lead_time')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">{{ __('messages.lead_time_help') }}</p>
                            </div>
                        </div>
                        
                        <!-- Seção de Quantidade e Preço -->
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
                            <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-calculator text-blue-600 mr-2"></i>
                                {{ __('messages.quantity_and_price') }}
                            </h4>
                            
                            <!-- Quantidade Necessária -->
                            <div class="mb-4">
                                <label for="required_quantity" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.required_quantity') }} <span class="text-red-600">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number" wire:model="plan.required_quantity" id="required_quantity" step="0.001" min="0.001"
                                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('plan.required_quantity') border-red-500 @enderror"
                                        placeholder="Ex: 10.5">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-weight text-gray-400"></i>
                                    </div>
                                </div>
                                @error('plan.required_quantity')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Preço Unitário -->
                            <div class="mb-4">
                                <label for="unit_price" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.unit_price') }} <span class="text-red-600">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number" wire:model="plan.unit_price" id="unit_price" step="0.01" min="0"
                                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('plan.unit_price') border-red-500 @enderror"
                                        placeholder="Ex: 25.50">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-euro-sign text-gray-400"></i>
                                    </div>
                                </div>
                                @error('plan.unit_price')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Preço Total -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.total_price') }}
                                </label>
                                <div class="relative bg-gray-50 p-3 rounded-md border border-gray-200">
                                    <span class="text-lg font-semibold text-blue-700" x-text="calculateTotal() + ' €'"></span>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">{{ __('messages.total_price_calculation') }}</p>
                            </div>
                            
                            <!-- Prioridade -->
                            <div class="mb-4">
                                <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.priority') }} <span class="text-red-600">*</span>
                                </label>
                                <div class="relative">
                                    <select wire:model="plan.priority" id="priority" 
                                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('plan.priority') border-red-500 @enderror">
                                        @foreach($priorities as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-flag text-gray-400"></i>
                                    </div>
                                </div>
                                @error('plan.priority')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.status') }} <span class="text-red-600">*</span>
                                </label>
                                <div class="relative">
                                    <select wire:model="plan.status" id="status" 
                                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('plan.status') border-red-500 @enderror">
                                        @foreach($statuses as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-tasks text-gray-400"></i>
                                    </div>
                                </div>
                                @error('plan.status')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Seção de Notas -->
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
                        <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-sticky-note text-blue-600 mr-2"></i>
                            {{ __('messages.notes') }}
                        </h4>
                        
                        <div>
                            <div class="relative">
                                <textarea wire:model="plan.notes" id="notes" rows="3" 
                                    class="block w-full pl-3 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('plan.notes') border-red-500 @enderror"
                                    placeholder="{{ __('messages.purchase_plan_notes_placeholder') }}"></textarea>
                            </div>
                            @error('plan.notes')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">{{ __('messages.notes_help') }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Rodapé do Modal -->
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-col sm:flex-row justify-end gap-2">
                    <button type="button" wire:click="closeModal()" class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:ring-blue-500">
                        <i class="fas fa-times mr-2"></i>
                        {{ __('messages.cancel') }}
                    </button>
                    <button type="submit" class="inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:ring-blue-500">
                        <i class="fas {{ $editMode ? 'fa-save' : 'fa-plus-circle' }} mr-2"></i>
                        {{ $editMode ? __('messages.update') : __('messages.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
