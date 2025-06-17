<!-- ============================================
     TAB 2: PRODUCTS SELECTION
     ============================================ -->

<div class="space-y-8" x-data="{ 
    searchQuery: @entangle('productSearch'),
    selectedProducts: @entangle('items'),
    showDropdown: false,
    selectedProductId: null
}">
    <!-- Page Title -->
    <div class="text-center">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ __('Product Selection') }}</h2>
        <p class="text-gray-600">{{ __('Search and add products to your transfer request') }}</p>
    </div>

    <!-- Product Search Section -->
    <div class="bg-gradient-to-br from-green-50 to-emerald-100 rounded-xl border border-green-200 shadow-sm relative">
        <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-4">
            <h3 class="text-lg font-semibold text-white flex items-center">
                <i class="fas fa-search mr-3"></i>
                {{ __('Find Products') }}
                @if($this->hasSourceWarehouse)
                    <span class="ml-2 text-green-200 text-sm">
                        ({{ __('Available in selected warehouse') }})
                    </span>
                @endif
            </h3>
        </div>
        <div class="p-6">
            @if($this->hasSourceWarehouse)
                <div class="relative" x-data="{ isSearching: false }" style="z-index: 1000;">
                    <!-- Search Input -->
                    <div class="relative">
                        <input type="text" 
                               wire:model.debounce.300ms="productSearch"
                               x-model="searchQuery"
                               @input="showDropdown = searchQuery.length > 0"
                               @focus="showDropdown = searchQuery.length > 0"
                               @click.away="showDropdown = false"
                               placeholder="{{ __('Search products by name or SKU...') }}"
                               class="block w-full pl-12 pr-16 py-4 text-lg border-2 border-gray-300 rounded-xl shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50 bg-white transition-all duration-200">
                        <div class="absolute left-4 top-1/2 transform -translate-y-1/2">
                            <i class="fas fa-search text-gray-400 text-lg"></i>
                        </div>
                        <div class="absolute right-4 top-1/2 transform -translate-y-1/2" x-show="searchQuery.length > 0">
                            <button @click="searchQuery = ''; showDropdown = false; $wire.set('productSearch', '')" 
                                    class="text-gray-400 hover:text-gray-600 transition-colors">
                                <i class="fas fa-times-circle text-lg"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Search Results Dropdown -->
                    <div x-show="showDropdown && searchQuery.length > 0" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform scale-95"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 transform scale-100"
                         x-transition:leave-end="opacity-0 transform scale-95"
                         class="absolute z-[9999] w-full mt-2 bg-white border-2 border-gray-200 rounded-xl shadow-2xl max-h-80 overflow-y-auto"
                         style="z-index: 9999;">
                        
                        @if(count($filteredProducts) > 0)
                            <div class="py-2">
                                @foreach($filteredProducts as $product)
                                    @php
                                        $stockQty = $this->getProductStock($product['id']);
                                    @endphp
                                    <div wire:click="selectProduct({{ $product['id'] }})"
                                         @click="showDropdown = false; searchQuery = ''"
                                         class="px-4 py-3 hover:bg-green-50 cursor-pointer border-b border-gray-100 last:border-b-0 transition-all duration-150">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-3">
                                                    <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-blue-500 rounded-lg flex items-center justify-center">
                                                        <i class="fas fa-box text-white text-sm"></i>
                                                    </div>
                                                    <div>
                                                        <h4 class="font-semibold text-gray-900">{{ $product['name'] }}</h4>
                                                        <p class="text-sm text-gray-600">SKU: {{ $product['sku'] }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                    {{ $stockQty > 10 ? 'bg-green-100 text-green-800' : ($stockQty > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                    <i class="fas fa-cube mr-1"></i>{{ $stockQty }} {{ __('available') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-search text-3xl mb-3"></i>
                                <p class="text-lg">{{ __('No products found') }}</p>
                                <p class="text-sm">{{ __('Try adjusting your search terms or check if products have stock in the selected warehouse') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <!-- Warning when no source warehouse selected -->
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>{{ __('Select Source Warehouse:') }}</strong> 
                                {{ __('Please select a source warehouse in the General tab to see available products.') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Selected Products Section -->
    <div class="bg-gradient-to-br from-blue-50 to-indigo-100 rounded-xl border border-blue-200 overflow-hidden shadow-sm">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white flex items-center">
                    <i class="fas fa-shopping-cart mr-3"></i>
                    {{ __('Selected Products') }}
                </h3>
                <span class="bg-white bg-opacity-20 text-white px-3 py-1 rounded-full text-sm font-medium">
                    {{ count($items) }} {{ __('items') }}
                </span>
            </div>
        </div>
        <div class="p-6">
            @if(count($items) > 0)
                <div class="space-y-4">
                    @foreach($items as $index => $item)
                        <div class="bg-white rounded-xl border-2 border-gray-200 p-6 hover:border-blue-300 transition-all duration-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4 flex-1">
                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-purple-500 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-box text-white text-lg"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-bold text-gray-900 text-lg">
                                            @php
                                                $product = $products->find($item['product_id']);
                                            @endphp
                                            {{ $product ? $product->name : 'Product not found' }}
                                        </h4>
                                        <p class="text-gray-600">
                                            SKU: <span class="font-mono font-semibold">{{ $product ? $product->sku : 'N/A' }}</span>
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Quantity Input -->
                                <div class="flex items-center space-x-4">
                                    <div class="flex items-center space-x-2">
                                        <label class="text-sm font-semibold text-gray-700">{{ __('Quantity:') }}</label>
                                        <div class="flex items-center border-2 border-gray-300 rounded-lg bg-white">
                                            <button type="button" 
                                                    wire:click="decrementQuantity({{ $index }})"
                                                    class="px-3 py-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" 
                                                   wire:model.defer="items.{{ $index }}.quantity_requested"
                                                   min="0.01" 
                                                   step="0.01"
                                                   class="w-20 px-3 py-2 text-center border-0 focus:ring-0 focus:outline-none font-semibold">
                                            <button type="button" 
                                                    wire:click="incrementQuantity({{ $index }})"
                                                    class="px-3 py-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Remove Button -->
                                    <button type="button" 
                                            wire:click="removeItem({{ $index }})"
                                            class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-all duration-200 transform hover:scale-110">
                                        <i class="fas fa-trash text-lg"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Notes for this item -->
                            <div class="mt-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-comment mr-1"></i>{{ __('Item Notes') }}
                                </label>
                                <textarea wire:model.defer="items.{{ $index }}.notes"
                                          placeholder="{{ __('Optional notes for this item...') }}"
                                          rows="2"
                                          class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 bg-white transition-all duration-200 resize-none"></textarea>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-12">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-shopping-cart text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('No products selected') }}</h3>
                    <p class="text-gray-600 mb-6">{{ __('Use the search above to find and add products to your transfer request') }}</p>
                    
                    <!-- Quick Add Buttons for Popular Products -->
                    @if(count($products) > 0 && $this->hasSourceWarehouse)
                        <div class="mt-8">
                            <h4 class="text-lg font-semibold text-gray-700 mb-4">{{ __('Quick Add Popular Items') }}</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($products->take(6) as $popularProduct)
                                    @php
                                        $stockQty = $this->getProductStock($popularProduct->id);
                                    @endphp
                                    <button type="button"
                                            wire:click="quickAddProduct({{ $popularProduct->id }})"
                                            class="p-4 bg-white border-2 border-gray-200 rounded-xl hover:border-blue-300 hover:shadow-md transition-all duration-200 transform hover:scale-105">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-8 h-8 bg-gradient-to-br from-green-400 to-blue-500 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-box text-white text-sm"></i>
                                                </div>
                                                <div class="text-left">
                                                    <h5 class="font-semibold text-gray-900 text-sm">{{ $popularProduct->name }}</h5>
                                                    <p class="text-xs text-gray-600">{{ $popularProduct->sku }}</p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                                    {{ $stockQty > 10 ? 'bg-green-100 text-green-800' : ($stockQty > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                    <i class="fas fa-cube mr-1"></i>{{ $stockQty }}
                                                </span>
                                            </div>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @elseif(!$this->hasSourceWarehouse)
                        <div class="mt-8 p-6 bg-blue-50 border border-blue-200 rounded-xl">
                            <div class="text-center">
                                <i class="fas fa-warehouse text-blue-400 text-3xl mb-3"></i>
                                <h4 class="text-lg font-semibold text-blue-700 mb-2">{{ __('Select Source Warehouse') }}</h4>
                                <p class="text-blue-600">{{ __('Choose a source warehouse in the General tab to see quick add options') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Validation Messages -->
    @if(empty($items))
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <strong>{{ __('Required:') }}</strong> {{ __('Please add at least one product to continue.') }}
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>
