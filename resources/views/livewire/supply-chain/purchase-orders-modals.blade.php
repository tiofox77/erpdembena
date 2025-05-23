<!-- Modal de Criação/Edição -->
<div x-data="{ open: @entangle('showModal').live }" 
     x-show="open" 
     x-cloak 
     class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50" 
     role="dialog" 
     aria-modal="true"
     x-transition:enter="transition ease-out duration-300" 
     x-transition:enter-start="opacity-0" 
     x-transition:enter-end="opacity-100" 
     x-transition:leave="transition ease-in duration-200" 
     x-transition:leave-start="opacity-100" 
     x-transition:leave-end="opacity-0">
    <div class="relative top-20 mx-auto p-1 w-full max-w-4xl">
        <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="transform opacity-0 scale-95" 
             x-transition:enter-end="transform opacity-100 scale-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="transform opacity-100 scale-100" 
             x-transition:leave-end="transform opacity-0 scale-95">
            <!-- Cabeçalho com gradiente -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas {{ $editMode ? 'fa-edit' : 'fa-plus-circle' }} mr-2 animate-pulse"></i>
                    {{ $editMode ? __('messages.edit_purchase_order') : __('messages.create_purchase_order') }}
                </h3>
                <button type="button" wire:click="closeModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Conteúdo da Modal -->
            <div class="p-6 space-y-6">
                <!-- Informações Gerais -->
                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                    <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                        <h3 class="text-base font-medium text-gray-700">{{ __('messages.general_information') }}</h3>
                    </div>
                    <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Código da Ordem -->
                        <div>
                            <label for="order_number" class="block text-sm font-medium text-gray-700">{{ __('messages.order_number') }}</label>
                            <input type="text" id="order_number" wire:model.defer="purchaseOrder.order_number" readonly 
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-gray-100"
                                placeholder="{{ __('messages.auto_generated') }}">
                        </div>
                        
                        <!-- Fornecedor -->
                        <div>
                            <label for="supplier_id" class="block text-sm font-medium text-gray-700">{{ __('messages.supplier') }}</label>
                            <select wire:model.defer="purchaseOrder.supplier_id" id="supplier_id" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white">
                                <option value="">{{ __('messages.select_supplier') }}</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            @error('purchaseOrder.supplier_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Data da Ordem -->
                        <div>
                            <label for="order_date" class="block text-sm font-medium text-gray-700">{{ __('messages.order_date') }}</label>
                            <input type="date" 
                                wire:model.defer="purchaseOrder.order_date" 
                                id="order_date" 
                                value="{{ $purchaseOrder['order_date'] ?? '' }}"
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            @error('purchaseOrder.order_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Data de Entrega Prevista -->
                        <div>
                            <label for="expected_delivery_date" class="block text-sm font-medium text-gray-700">{{ __('messages.expected_delivery') }}</label>
                            <input type="date" 
                                wire:model.defer="purchaseOrder.expected_delivery_date" 
                                id="expected_delivery_date" 
                                value="{{ $purchaseOrder['expected_delivery_date'] ?? '' }}"
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            @error('purchaseOrder.expected_delivery_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Status da Ordem -->
                        @if($editMode)
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">{{ __('messages.status') }}</label>
                            <select wire:model.defer="purchaseOrder.status" id="status" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm">
                                <option value="draft">{{ __('messages.draft') }}</option>
                                <option value="pending_approval">{{ __('messages.pending_approval') }}</option>
                                <option value="approved">{{ __('messages.approved') }}</option>
                                <option value="ordered">{{ __('messages.ordered') }}</option>
                                <option value="partially_received">{{ __('messages.partially_received') }}</option>
                                <option value="completed">{{ __('messages.completed') }}</option>
                                <option value="cancelled">{{ __('messages.cancelled') }}</option>
                            </select>
                            @error('purchaseOrder.status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        @endif
                    </div>
                </div>
                
                <!-- Itens da Ordem -->
                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                    <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                        <i class="fas fa-list text-blue-600 mr-2"></i>
                        <h3 class="text-base font-medium text-gray-700">{{ __('messages.order_items') }}</h3>
                    </div>
                    <div class="p-4">
                        <!-- Tabela de Itens -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('messages.product') }}
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('messages.description') }}
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('messages.quantity') }}
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('messages.unit_price') }}
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('messages.total') }}
                                        </th>
                                        <th scope="col" class="relative px-4 py-3">
                                            <span class="sr-only">{{ __('messages.actions') }}</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($orderItems as $index => $item)
                                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $item['product_name'] }}</div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="text" wire:model.defer="orderItems.{{ $index }}.description" 
                                                    class="block w-full py-1 px-2 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                                <input type="number" min="1" required
                                                    wire:model.defer="orderItems.{{ $index }}.quantity" 
                                                    wire:change="calculateLineTotal({{ $index }})"
                                                    class="block w-20 py-1 px-2 text-sm text-right border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 bg-white"
                                                    placeholder="{{ __('messages.quantity') }}"
                                                    title="{{ __('messages.quantity_required') }}">
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                                <input type="number" step="0.01" min="0" required
                                                    wire:model.defer="orderItems.{{ $index }}.unit_price" 
                                                    wire:change="calculateLineTotal({{ $index }})"
                                                    class="block w-24 py-1 px-2 text-sm text-right border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 bg-white"
                                                    placeholder="{{ __('messages.unit_price') }}"
                                                    title="{{ __('messages.unit_price_required') }}">
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                                <div class="text-sm font-medium text-gray-900">{{ number_format($item['line_total'] ?? 0, 2) }}</div>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                                <button wire:click="removeItem({{ $index }})" class="text-red-600 hover:text-red-900 transition-colors duration-150 transform hover:scale-110">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-3 text-center text-sm text-gray-500">
                                                {{ __('messages.no_items_added') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <!-- Rodapé com total -->
                                <tfoot class="bg-gray-50">
                                    <!-- Subtotal -->
                                    <tr>
                                        <td colspan="4" class="px-4 py-2 text-right font-medium">
                                            {{ __('messages.subtotal') }}:
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            {{ number_format(collect($orderItems)->sum('line_total'), 2) }}
                                        </td>
                                        <td></td>
                                    </tr>
                                    <!-- Shipping Cost -->
                                    <tr>
                                        <td colspan="4" class="px-4 py-2 text-right font-medium">
                                            {{ __('messages.shipping_cost') }}:
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-500 sm:text-sm">{{ config('default.currency_symbol', '$') }}</span>
                                                </div>
                                                <input type="number" 
                                                    wire:model.defer="purchaseOrder.shipping_amount" 
                                                    wire:change="calculateOrderTotal"
                                                    step="0.01" 
                                                    min="0"
                                                    class="pl-7 block w-full text-right border-0 bg-transparent focus:ring-0 focus:border-blue-500 p-0 text-sm"
                                                    placeholder="0.00">
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <!-- Order Total -->
                                    <tr class="border-t border-gray-200">
                                        <td colspan="4" class="px-4 py-3 text-right font-bold">
                                            {{ __('messages.order_total') }}:
                                        </td>
                                        <td class="px-4 py-3 text-right font-bold text-gray-900">
                                            {{ number_format($orderTotal, 2) }}
                                        </td>
                                        <td></td>
                                    </tr>
                                    <!-- Add Item Button -->
                                    <tr>
                                        <td colspan="6" class="px-4 py-3">
                                            <button type="button" wire:click="openProductSelector" 
                                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150 transform hover:scale-105">
                                                <i class="fas fa-plus-circle mr-1.5"></i>
                                                {{ __('messages.add_item') }}
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Observações -->
                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                    <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                        <i class="fas fa-sticky-note text-blue-600 mr-2"></i>
                        <h3 class="text-base font-medium text-gray-700">{{ __('messages.notes') }}</h3>
                    </div>
                    <div class="p-4">
                        <label for="notes" class="block text-sm font-medium text-gray-700">{{ __('messages.notes') }}</label>
                        <textarea id="notes" wire:model.defer="purchaseOrder.notes" rows="4"
                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                            placeholder="{{ __('messages.enter_order_notes') }}"></textarea>
                    </div>
                </div>
            </div>

            <!-- Rodapé com botões de ação -->
            <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                <button type="button" wire:click="closeModal" 
                    class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                    <i class="fas fa-times mr-2"></i>
                    {{ __('messages.cancel') }}
                </button>
                <button type="submit" wire:click="savePurchaseOrder" wire:loading.attr="disabled"
                    class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="savePurchaseOrder">
                        <i class="fas {{ $editMode ? 'fa-save' : 'fa-plus-circle' }} mr-2"></i>
                        {{ $editMode ? __('messages.update') : __('messages.create') }}
                    </span>
                    <span wire:loading wire:target="savePurchaseOrder" class="inline-flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('messages.processing') }}
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Seleção de Produtos -->
<div
    x-data="{
        open: false,
        observer: null,
        searchTimeout: null,
        init() {
            // Initialize Intersection Observer for infinite scroll
            this.observer = new IntersectionObserver(
                (entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting && !@this.isLoadingProducts && @this.hasMoreProducts) {
                            @this.call('loadMoreProducts');
                        }
                    });
                },
                {
                    root: this.$refs.productsContainer,
                    rootMargin: '100px',
                    threshold: 0.1
                }
            );
            
            // Listen for modal open event
            this.$wire.on('openProductSelectorModal', () => { 
                this.open = true;
                // Reset scroll position when modal opens
                this.$nextTick(() => {
                    if (this.$refs.productsContainer) {
                        this.$refs.productsContainer.scrollTop = 0;
                    }
                    this.setupObserver();
                });
            });
            
            // Listen for products loaded event
            this.$wire.on('productsLoaded', () => {
                this.setupObserver();
            });
            
            // Clean up observer when component is destroyed
            this.$watch('open', (value) => {
                if (!value && this.observer) {
                    this.observer.disconnect();
                }
            });
        },
        setupObserver() {
            // Clean up previous observer
            if (this.observer) {
                this.observer.disconnect();
            }
            
            // Set up observer on the last product row
            this.$nextTick(() => {
                const lastRow = this.$refs.lastProductRow;
                if (lastRow) {
                    this.observer.observe(lastRow);
                }
            });
        },
        closeModal() {
            this.open = false;
            @this.set('productSearch', '', false);
        },
        handleSearch(value) {
            // Clear previous timeout
            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout);
            }
            
            // Set new timeout
            this.searchTimeout = setTimeout(() => {
                @this.set('productSearch', value);
                @this.call('loadProducts');
            }, 500);
        }
    }"
    x-show="open"
    x-cloak
    class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50"
    role="dialog"
    aria-modal="true"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0">
    <div class="relative top-20 mx-auto p-1 w-full max-w-4xl">
        <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="transform opacity-0 scale-95"
             x-transition:enter-end="transform opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="transform opacity-100 scale-100"
             x-transition:leave-end="transform opacity-0 scale-95">
            <!-- Cabeçalho com gradiente -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                <h3 class="text-lg font-medium text-white">
                    <i class="fas fa-boxes mr-2"></i>
                    {{ __('messages.select_products') }}
                </h3>
                <button type="button" @click="closeModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Conteúdo da Modal -->
            <div class="p-6">
                <!-- Busca de produtos -->
                <div class="mb-4">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" 
                                   x-model="searchTerm"
                                   x-init="searchTerm = '{{ $productSearch }}'"
                                   x-on:input.debounce.500ms="
                                       if (searchTerm.trim() !== '{{ $productSearch }}') {
                                           $wire.set('productSearch', searchTerm, false)
                                               .then(() => $wire.call('loadProducts'));
                                       }
                                   "
                                   class="block w-full pl-10 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                   placeholder="{{ __('messages.search_products_placeholder') }}">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <template x-if="$wire.productSearch">
                                    <button type="button" 
                                            @click="
                                                searchTerm = '';
                                                $wire.set('productSearch', '', false)
                                                    .then(() => $wire.call('loadProducts'));
                                            " 
                                            class="text-gray-400 hover:text-gray-500">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </template>
                                <div x-show="$wire.isLoadingProducts" class="ml-2">
                                    <i class="fas fa-circle-notch fa-spin text-blue-500"></i>
                                </div>
                            </div>
                    </div>
                </div>
                
                <!-- Lista de Produtos -->
                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto max-h-96" x-ref="productsContainer">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0 z-10">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('messages.product') }}
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('messages.code') }}
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('messages.price') }}
                                    </th>
                                    <th scope="col" class="relative px-4 py-3 w-16">
                                        <span class="sr-only">{{ __('messages.actions') }}</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($products as $index => $product)
                                    <tr class="hover:bg-gray-50 transition-colors duration-150" 
                                        @if($loop->last) x-ref="lastProductRow" @endif>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $product->name }}
                                                @if($product->unit_of_measure)
                                                    <span class="text-xs text-gray-500 ml-1">({{ $product->unit_of_measure }})</span>
                                                @endif
                                            </div>
                                            @if($product->description)
                                                <div class="text-xs text-gray-500 mt-1">
                                                    {{ Str::limit($product->description, 60) }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                            {{ $product->product_code }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                                            {{ number_format($product->price, 2) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                            <button wire:click="addProduct({{ $product->id }})" 
                                                class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:text-blue-900 transition-colors duration-150 transform hover:scale-110"
                                                title="{{ __('messages.add_to_order') }}">
                                                <i class="fas fa-plus-circle"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-12 text-center text-gray-500">
                                            <div class="flex flex-col items-center justify-center">
                                                <i class="fas fa-box-open text-4xl text-gray-300 mb-3"></i>
                                                @if($productSearch)
                                                    <p class="text-gray-600">{{ __('messages.no_products_found_for') }} "{{ $productSearch }}"</p>
                                                    <p class="text-sm text-gray-500 mt-1">{{ __('messages.try_different_search') }}</p>
                                                @else
                                                    <p class="text-gray-600">{{ __('messages.no_products_available') }}</p>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse

                                <!-- Loading Animation -->
                                <tr x-show="$wire.isLoadingProducts" x-transition.opacity.duration.300ms>
                                    <td colspan="4" class="px-4 py-6 text-center">
                                        <div class="flex flex-col items-center justify-center space-y-2">
                                            <div class="relative">
                                                <div class="w-10 h-10 border-4 border-indigo-200 rounded-full"></div>
                                                <div class="absolute top-0 left-0 w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                                            </div>
                                            <p class="text-sm font-medium text-gray-600">{{ __('messages.loading_products') }}</p>
                                        </div>
                                    </td>
                                </tr>

                                <!-- End of list message -->
                                <tr x-show="!$wire.isLoadingProducts && $wire.products.length > 0 && !$wire.hasMoreProducts" x-transition.opacity.duration.300ms>
                                    <td colspan="4" class="px-4 py-4 text-center">
                                        <div class="flex items-center justify-center space-x-2 text-gray-500">
                                            <i class="fas fa-check-circle text-green-500"></i>
                                            <span>{{ __('messages.all_products_loaded') }}</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Selected products counter -->
                @if(count($orderItems) > 0)
                    <div class="mt-3 text-sm text-gray-600">
                        <i class="fas fa-check-circle text-green-500 mr-1"></i>
                        {{ trans_choice('messages.products_selected', count($orderItems), ['count' => count($orderItems)]) }}
                    </div>
                @endif
            </div>
            
            <!-- Rodapé -->
            <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-between items-center border-t border-gray-200">
                <div class="text-sm text-gray-600">
                    @if(count($products) > 0)
                        {{ trans_choice('messages.showing_products', min(count($products), $productPerPage), [
                            'from' => ($productPage - 1) * $productPerPage + 1,
                            'to' => min($productPage * $productPerPage, $totalProducts),
                            'total' => $totalProducts
                        ]) }}
                    @endif
                </div>
                <div class="flex space-x-3">
                    <button type="button" @click="closeModal" 
                        class="inline-flex justify-center items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-gray-700 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200 ease-in-out">
                        {{ __('messages.cancel') }}
                    </button>
                    <button type="button" @click="closeModal" 
                        class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out">
                        {{ __('messages.done') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Exclusão de Ordem -->
<div x-data="{ show: @entangle('showDeleteModal') }"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform scale-90"
    x-transition:enter-end="opacity-100 transform scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform scale-100"
    x-transition:leave-end="opacity-0 transform scale-90"
    class="fixed inset-0 z-50 overflow-y-auto" 
    style="display: none;">
    
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-900 opacity-75"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <!-- Cabeçalho do Modal -->
            <div class="bg-gradient-to-r from-red-500 to-red-700 px-4 py-3 sm:px-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg leading-6 font-medium text-white flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2 animate-pulse"></i>
                        {{ __('messages.confirm_deletion') }}
                    </h3>
                    <button @click="show = false" class="text-white hover:text-gray-200 focus:outline-none">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <!-- Conteúdo do Modal -->
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-trash text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            {{ __('messages.delete_purchase_order') }}
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                {{ __('messages.delete_purchase_order_confirmation') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Botões de Ação -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="deleteOrder" 
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                    {{ __('messages.delete') }}
                </button>
                <button @click="show = false"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    {{ __('messages.cancel') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div x-data="{ open: @entangle('showConfirmDelete') }" 
     x-show="open" 
     x-cloak 
     class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50" 
     role="dialog" 
     aria-modal="true"
     x-transition:enter="transition ease-out duration-300" 
     x-transition:enter-start="opacity-0" 
     x-transition:enter-end="opacity-100" 
     x-transition:leave="transition ease-in duration-200" 
     x-transition:leave-start="opacity-100" 
     x-transition:leave-end="opacity-0">
    <div class="relative top-20 mx-auto p-1 w-full max-w-md">
        <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="transform opacity-0 scale-95" 
             x-transition:enter-end="transform opacity-100 scale-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="transform opacity-100 scale-100" 
             x-transition:leave-end="transform opacity-0 scale-95">
            <!-- Cabeçalho com gradiente -->
            <div class="bg-gradient-to-r from-red-600 to-red-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2 animate-pulse"></i>
                    {{ __('messages.confirm_deletion') }}
                </h3>
                <button type="button" wire:click="cancelDelete" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Conteúdo da Modal -->
            <div class="p-6">
                <div class="flex items-start mb-4">
                    <div class="flex-shrink-0 bg-red-100 rounded-full p-2">
                        <i class="fas fa-trash-alt text-red-600 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-gray-600">{{ __('messages.confirm_delete_purchase_order') }}</p>
                        <div class="mt-2">
                            <p class="text-sm font-medium text-gray-900">
                                {{ __('messages.order_number') }}: <span id="deleteOrderNumber"></span>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Aviso -->
                <div class="mt-4 bg-yellow-50 p-3 rounded-md border border-yellow-100">
                    <p class="text-xs text-yellow-700 flex items-start">
                        <i class="fas fa-exclamation-circle mr-2 mt-0.5"></i>
                        {{ __('messages.delete_purchase_order_warning') }}
                    </p>
                </div>
            </div>

            <!-- Rodapé com botões de ação -->
            <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                <button type="button" wire:click="cancelDelete" 
                    class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                    <i class="fas fa-times mr-2"></i>
                    {{ __('messages.cancel') }}
                </button>
                <button type="button" wire:click="deleteOrder" wire:loading.attr="disabled" 
                    class="inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="deleteOrder">
                        <i class="fas fa-trash-alt mr-2"></i>
                        {{ __('messages.delete') }}
                    </span>
                    <span wire:loading wire:target="deleteOrder" class="inline-flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('messages.processing') }}
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
