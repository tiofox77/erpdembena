<!-- Modal para Criar ou Editar Plano de Compra com Múltiplos Produtos -->
<div x-data="{ 
        calculateItemTotal(index) {
            if (this.$wire.planItems[index] && this.$wire.planItems[index].quantity && this.$wire.planItems[index].unit_price) {
                return (parseFloat(this.$wire.planItems[index].quantity) * parseFloat(this.$wire.planItems[index].unit_price)).toFixed(2);
            }
            return 0;
        },
        calculateGrandTotal() {
            let total = 0;
            this.$wire.planItems.forEach((item, index) => {
                if (item.quantity && item.unit_price) {
                    total += parseFloat(item.quantity) * parseFloat(item.unit_price);
                }
            });
            return total.toFixed(2);
        }
    }" 
    x-cloak
    class="fixed inset-0 z-30 flex items-center justify-center overflow-auto bg-gray-800 bg-opacity-75 transition-all duration-300 ease-in-out"
    x-show="$wire.showMultiProductModal"
    @keydown.escape.window="$wire.closeMultiProductModal()">
    <div class="relative w-full max-w-5xl mx-auto my-8 px-4 sm:px-0"
        x-show="$wire.showMultiProductModal"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        @click.away="$wire.closeMultiProductModal()">
        <div class="relative bg-white rounded-lg shadow-xl overflow-hidden" @click.stop>
            <!-- Cabeçalho do Modal -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-4 sm:px-6 flex justify-between items-center">
                <h3 class="text-lg font-medium text-white">
                    <i class="fas {{ $editMode ? 'fa-edit' : 'fa-plus-circle' }} mr-2"></i>
                    {{ $editMode ? __('messages.edit_purchase_plan') : __('messages.add_purchase_plan') }}
                </h3>
                <button @click="$wire.closeMultiProductModal()" class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Formulário -->
            <form wire:submit.prevent="saveMultiProducts">
                <div class="px-4 py-5 sm:p-6 space-y-6">
                    <!-- Informações Gerais do Plano de Compra -->
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
                        <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                            {{ __('Informações Gerais') }}
                        </h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Título do Plano -->
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('Título do Plano') }}
                                </label>
                                <div class="relative">
                                    <input type="text" wire:model="plan.title" id="title"
                                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('plan.title') border-red-500 @enderror"
                                        placeholder="Ex: Compra mensal de insumos">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-heading text-gray-400"></i>
                                    </div>
                                </div>
                                @error('plan.title')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Fornecedor -->
                            <div>
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
                            <div>
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
                            
                            <!-- Prioridade -->
                            <div>
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
                    
                    <!-- Lista de Produtos -->
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-boxes text-blue-600 mr-2"></i>
                                {{ __('Lista de Produtos') }}
                            </h4>
                            <button type="button" wire:click="addPlanItem" 
                                class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-plus mr-1"></i> Adicionar Produto
                            </button>
                        </div>
                        
                        <!-- Cabeçalho da tabela -->
                        <div class="bg-gray-50 p-2 grid grid-cols-12 gap-2 font-medium text-xs text-gray-700 border-b border-gray-200">
                            <div class="col-span-3">PRODUTO</div>
                            <div class="col-span-2">QUANTIDADE</div>
                            <div class="col-span-2">UNIDADE</div>
                            <div class="col-span-2">PREÇO UNIT.</div>
                            <div class="col-span-2">TOTAL</div>
                            <div class="col-span-1">AÇÕES</div>
                        </div>
                        
                        @foreach($planItems as $index => $item)
                            <div class="grid grid-cols-12 gap-2 p-2 border-b border-gray-200 items-center">
                                <!-- Produto -->
                                <div class="col-span-3">
                                    <select wire:model="planItems.{{ $index }}.product_id" 
                                        class="block w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('planItems.'.$index.'.product_id') border-red-500 @enderror">
                                        <option value="">{{ __('messages.select_product') }}</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                        @endforeach
                                    </select>
                                    @error('planItems.'.$index.'.product_id')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <!-- Quantidade -->
                                <div class="col-span-2">
                                    <input type="number" step="0.001" min="0.001" 
                                        wire:model="planItems.{{ $index }}.quantity"
                                        wire:change="updateItemTotal({{ $index }})"
                                        class="block w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('planItems.'.$index.'.quantity') border-red-500 @enderror">
                                    @error('planItems.'.$index.'.quantity')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <!-- Unidade -->
                                <div class="col-span-2">
                                    <input type="text"
                                        wire:model="planItems.{{ $index }}.unit_of_measure"
                                        class="block w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('planItems.'.$index.'.unit_of_measure') border-red-500 @enderror"
                                        placeholder="ex: KG, UN, CX">
                                    @error('planItems.'.$index.'.unit_of_measure')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <!-- Preço Unitário -->
                                <div class="col-span-2">
                                    <input type="number" step="0.01" min="0"
                                        wire:model="planItems.{{ $index }}.unit_price"
                                        wire:change="updateItemTotal({{ $index }})"
                                        class="block w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('planItems.'.$index.'.unit_price') border-red-500 @enderror">
                                    @error('planItems.'.$index.'.unit_price')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <!-- Preço Total -->
                                <div class="col-span-2">
                                    <div class="flex items-center">
                                        <span class="font-semibold">{{ number_format($planItems[$index]['total_price'] ?? 0, 2, ',', '.') }}</span>
                                    </div>
                                </div>
                                
                                <!-- Ações -->
                                <div class="col-span-1 text-center">
                                    <button type="button" wire:click="removePlanItem({{ $index }})" class="text-red-500 hover:text-red-700">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                        
                        @if(count($planItems) == 0)
                            <div class="py-4 text-center text-gray-500 italic">
                                {{ __('Nenhum produto adicionado. Clique em "Adicionar Produto" para começar.') }}
                            </div>
                        @endif
                        
                        <!-- Total do Plano -->
                        <div class="flex justify-end mt-4 bg-gray-50 p-3 rounded-md border border-gray-200">
                            <div class="text-right">
                                <span class="block text-sm text-gray-700">Total do Plano:</span>
                                <span class="block text-xl font-bold text-blue-700" x-text="calculateGrandTotal() + ' €'"></span>
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
                    <button type="button" wire:click="closeMultiProductModal" 
                        class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out">
                        <i class="fas fa-times mr-2"></i>
                        {{ __('messages.cancel') }}
                    </button>
                    <button type="submit" 
                        class="inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out">
                        <i class="fas {{ $editMode ? 'fa-save' : 'fa-plus-circle' }} mr-2"></i>
                        {{ $editMode ? __('messages.update') : __('messages.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
