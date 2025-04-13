<div>
    <div class="container mx-auto px-4 py-6">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
            <h1 class="text-2xl font-semibold text-gray-800 flex items-center">
                <i class="fas fa-boxes text-blue-600 mr-3"></i>
                {{ __('livewire/products.products_management') }}
            </h1>
            <button wire:click="openAddModal" 
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 hover:shadow-lg">
                <i class="fas fa-plus-circle mr-2 animate-pulse"></i>
                {{ __('livewire/products.add_product') }}
            </button>
        </div>

        <!-- Cartão de Busca e Filtros -->
        <div class="mb-6 bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 ease-in-out hover:shadow-lg transform hover:scale-[1.01]">
            <!-- Cabeçalho do cartão com gradiente -->
            <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                <i class="fas fa-filter text-blue-600 mr-2"></i>
                <h2 class="text-base font-medium text-gray-700">{{ __('livewire/products.search_and_filters') }}</h2>
            </div>
            <!-- Conteúdo do cartão -->
            <div class="p-4">
                <div class="flex flex-col gap-4">
                    <!-- Campo de busca -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-search text-gray-500 mr-1"></i>
                            {{ __('livewire/layout.search') }}
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input wire:model.debounce.300ms="search" id="search" 
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out" 
                                placeholder="{{ __('livewire/products.search_products') }}" 
                                type="search">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">{{ __('livewire/products.search_by_name_sku_description') }}</p>
                    </div>
                    
                    <!-- Linha de filtros -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Filtro de categoria -->
                        <div>
                            <label for="categoryFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-tags text-gray-500 mr-1"></i>
                                {{ __('livewire/products.category') }}
                            </label>
                            <select wire:model="categoryFilter" id="categoryFilter" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="">{{ __('livewire/products.all_categories') }}</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Filtro de fornecedor (se existir na classe Livewire) -->
                        <div>
                            <label for="supplierFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-truck-loading text-gray-500 mr-1"></i>
                                {{ __('livewire/products.supplier') }}
                            </label>
                            <select wire:model="supplier_filter" id="supplierFilter" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="">{{ __('livewire/products.all_suppliers') }}</option>
                                @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Filtro de status (se existir na classe Livewire) -->
                        <div>
                            <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-toggle-on text-gray-500 mr-1"></i>
                                {{ __('livewire/products.status') }}
                            </label>
                            <select wire:model="status_filter" id="statusFilter" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="">{{ __('livewire/products.all_statuses') }}</option>
                                <option value="active">{{ __('livewire/products.active') }}</option>
                                <option value="inactive">{{ __('livewire/products.inactive') }}</option>
                            </select>
                        </div>
                        
                        <!-- Registros por página -->
                        <div>
                            <label for="perPage" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-list-ol text-gray-500 mr-1"></i>
                                {{ __('livewire/layout.per_page') }}
                            </label>
                            <select wire:model="perPage" id="perPage" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Botões de ação -->
                    <div class="flex justify-end">
                        <button wire:click="resetFilters" 
                            class="flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                            <i class="fas fa-redo-alt mr-2"></i>
                            {{ __('livewire/layout.reset_filters') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela de Produtos -->
        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden transition-all duration-300 ease-in-out hover:shadow-lg">
            <!-- Cabeçalho da Tabela -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3 border-b border-gray-200">
                <h2 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-boxes mr-2"></i>
                    {{ __('livewire/products.product_list') }}
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('livewire/products.product_info') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('livewire/products.category') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('livewire/products.inventory_info') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('livewire/products.pricing') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('livewire/layout.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($products as $product)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($product->image_url)
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img class="h-10 w-10 rounded-full object-cover" src="{{ $product->image_url }}" alt="{{ $product->name }}">
                                    </div>
                                    @else
                                    <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                                        <i class="fas fa-box text-gray-500"></i>
                                    </div>
                                    @endif
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $product->sku }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $product->category->name ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ __('livewire/products.in_stock') }}: {{ $product->current_stock }}</div>
                                <div class="text-sm text-gray-500">{{ __('livewire/products.reorder_point') }}: {{ $product->reorder_point }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ __('livewire/products.cost_price') }}: {{ number_format($product->cost_price, 2) }}</div>
                                <div class="text-sm text-gray-500">{{ __('livewire/products.unit_price') }}: {{ number_format($product->selling_price, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <button wire:click="openViewModal({{ $product->id }})" 
                                        class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded bg-blue-100 text-blue-700 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                        <i class="fas fa-eye mr-1"></i>
                                        {{ __('livewire/layout.view') }}
                                    </button>
                                    <button wire:click="openEditModal({{ $product->id }})" 
                                        class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded bg-indigo-100 text-indigo-700 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                        <i class="fas fa-edit mr-1"></i>
                                        {{ __('livewire/layout.edit') }}
                                    </button>
                                    <button wire:click="confirmDelete({{ $product->id }})" 
                                        class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded bg-red-100 text-red-700 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                        <i class="fas fa-trash mr-1"></i>
                                        {{ __('livewire/layout.delete') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-box-open text-4xl text-gray-300 mb-3"></i>
                                    <h3 class="text-lg font-medium text-gray-900 mb-1">{{ __('livewire/products.no_products_found') }}</h3>
                                    <p class="text-sm text-gray-500 mb-4">{{ __('livewire/products.try_different_filters') }}</p>
                                    <button wire:click="resetFilters" 
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                        <i class="fas fa-redo-alt mr-2"></i>
                                        {{ __('livewire/layout.reset_filters') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-sm text-gray-600 bg-blue-50 px-3 py-2 rounded-md border border-blue-100 flex items-center">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                        {{ __('livewire/layout.showing') }} <span class="font-medium mx-1">{{ $products->firstItem() ?? 0 }}</span> {{ __('livewire/layout.to') }} <span class="font-medium mx-1">{{ $products->lastItem() ?? 0 }}</span> {{ __('livewire/layout.of') }} <span class="font-medium mx-1">{{ $products->total() }}</span> {{ __('livewire/layout.results') }}
                    </div>
                    <div class="pagination-container">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Product Modal -->
    @include('livewire.supply-chain.add-edit-procut-modal')

    <!-- View Product Modal -->
    <div>
        <div x-data="{ open: @entangle('showViewModal') }" x-show="open" x-cloak class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50" role="dialog" aria-modal="true" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="relative top-20 mx-auto p-1 w-full max-w-4xl">
                <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95">
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-eye mr-2 animate-pulse"></i>
                        {{ __('livewire/products.product_details') }}
                    </h3>
                    <button type="button" wire:click="closeViewModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="overflow-y-auto p-6 max-h-[calc(100vh-200px)]">
                    <!-- Navegação por abas -->
                    <div class="mb-6 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 transition-all duration-200 ease-in-out hover:shadow-md">
                        <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                            <i class="fas fa-layer-group text-blue-600 mr-2"></i>
                            <h2 class="text-base font-medium text-gray-700">{{ __('livewire/products.product_sections') }}</h2>
                        </div>
                        <div class="p-3 flex flex-wrap gap-2">
                            <button type="button" 
                                wire:click="setTab('general')" 
                                class="{{ $currentTab == 'general' ? 'bg-blue-100 border-blue-500 text-blue-700' : 'bg-gray-50 hover:bg-gray-100 text-gray-700' }} px-4 py-2 rounded-md border focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center transition-all duration-200 ease-in-out transform hover:scale-105">
                                <i class="fas fa-info-circle mr-2 transition-all duration-200 {{ $currentTab == 'general' ? 'text-blue-600 animate-pulse' : '' }}"></i>{{ __('livewire/products.general_info') }}
                            </button>
                            <button type="button" 
                                wire:click="setTab('inventory')" 
                                class="{{ $currentTab == 'inventory' ? 'bg-blue-100 border-blue-500 text-blue-700' : 'bg-gray-50 hover:bg-gray-100 text-gray-700' }} px-4 py-2 rounded-md border focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center transition-all duration-200 ease-in-out transform hover:scale-105">
                                <i class="fas fa-warehouse mr-2 transition-all duration-200 {{ $currentTab == 'inventory' ? 'text-blue-600 animate-pulse' : '' }}"></i>{{ __('livewire/products.inventory') }}
                            </button>
                            <button type="button" 
                                wire:click="setTab('dimensions')" 
                                class="{{ $currentTab == 'dimensions' ? 'bg-purple-100 border-purple-500 text-purple-700' : 'bg-gray-50 hover:bg-gray-100 text-gray-700' }} px-4 py-2 rounded-md border focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 flex items-center transition-all duration-200 ease-in-out transform hover:scale-105">
                                <i class="fas fa-ruler-combined mr-2 transition-all duration-200 {{ $currentTab == 'dimensions' ? 'text-purple-600 animate-pulse' : '' }}"></i>{{ __('livewire/products.dimensions') }}
                            </button>
                            <button type="button" 
                                wire:click="setTab('suppliers')" 
                                class="{{ $currentTab == 'suppliers' ? 'bg-green-100 border-green-500 text-green-700' : 'bg-gray-50 hover:bg-gray-100 text-gray-700' }} px-4 py-2 rounded-md border focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 flex items-center transition-all duration-200 ease-in-out transform hover:scale-105">
                                <i class="fas fa-truck-loading mr-2 transition-all duration-200 {{ $currentTab == 'suppliers' ? 'text-green-600 animate-pulse' : '' }}"></i>{{ __('livewire/products.suppliers') }}
                            </button>
                        </div>
                    </div>

                    <!-- Conteúdo das abas -->
                    <div class="space-y-6" x-data="{ activeTab: @entangle('currentTab') }" x-cloak>
                        <!-- Aba de Informações Gerais -->
                        <div x-show="activeTab == 'general'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">{{ __('livewire/products.product_name') }}</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $name }}</p>
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-gray-500">{{ __('livewire/products.sku') }}</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $sku }}</p>
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-gray-500">{{ __('livewire/products.category') }}</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $category_id ? $categories->firstWhere('id', $category_id)->name : '-' }}</p>
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-gray-500">{{ __('livewire/products.barcode') }}</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $barcode ?: '-' }}</p>
                                </div>

                                <div class="md:col-span-2">
                                    <p class="text-sm font-medium text-gray-500">{{ __('livewire/products.description') }}</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $description ?: '-' }}</p>
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-gray-500">{{ __('livewire/products.unit_price') }}</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ number_format($unit_price, 2) }}</p>
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-gray-500">{{ __('livewire/products.cost_price') }}</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ number_format($cost_price, 2) }}</p>
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-gray-500">{{ __('livewire/products.unit_of_measure') }}</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $unit_of_measure }}</p>
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-gray-500">{{ __('livewire/products.product_image') }}</p>
                                    @if($image)
                                        <div class="mt-1">
                                            <img src="{{ asset('storage/' . $image) }}" class="h-24 w-24 object-cover rounded-md">
                                        </div>
                                    @else
                                        <p class="mt-1 text-sm text-gray-900">-</p>
                                    @endif
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-gray-500">{{ __('livewire/products.status') }}</p>
                                    <p class="mt-1 text-sm text-gray-900">
                                        @if($is_active)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                {{ __('livewire/products.active') }}
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                {{ __('livewire/products.inactive') }}
                                            </span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Aba de Inventário -->
                        <div x-show="activeTab == 'inventory'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95">
                            <!-- Cartão de Controle de Estoque -->
                            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden mb-6">
                                <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                                    <i class="fas fa-warehouse text-blue-600 mr-2"></i>
                                    <h3 class="text-base font-medium text-gray-700">{{ __('livewire/products.inventory_details') }}</h3>
                                </div>
                                <div class="p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">{{ __('livewire/products.is_stockable') }}</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $is_stockable ? __('livewire/layout.yes') : __('livewire/layout.no') }}</p>
                                </div>

                                <div></div> <!-- Empty div for grid alignment -->

                                <div>
                                    <p class="text-sm font-medium text-gray-500">{{ __('livewire/products.min_stock_level') }}</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $min_stock_level }}</p>
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-gray-500">{{ __('livewire/products.reorder_point') }}</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $reorder_point }}</p>
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-gray-500">{{ __('livewire/products.lead_time_days') }}</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $lead_time_days }}</p>
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-gray-500">{{ __('livewire/products.storage_location') }}</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $location ?: '-' }}</p>
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-gray-500">{{ __('livewire/products.tax_type') }}</p>
                                    <p class="mt-1 text-sm text-gray-900">
                                        @if($tax_type == 'standard')
                                            {{ __('livewire/products.tax_standard') }}
                                        @elseif($tax_type == 'reduced')
                                            {{ __('livewire/products.tax_reduced') }}
                                        @else
                                            {{ __('livewire/products.tax_exempt') }}
                                        @endif
                                    </p>
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-gray-500">{{ __('livewire/products.tax_rate') }}</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $tax_rate }}%</p>
                                </div>
                            </div>
                                </div>
                            </div>
                        </div>

                        <!-- Aba de Dimensões -->
                        <div x-show="activeTab == 'dimensions'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95">
                            <!-- Cartão de Dimensões -->
                            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden mb-6">
                                <div class="flex items-center bg-gradient-to-r from-purple-50 to-purple-100 px-4 py-3 border-b border-gray-200">
                                    <i class="fas fa-ruler-combined text-purple-600 mr-2"></i>
                                    <h3 class="text-base font-medium text-gray-700">{{ __('livewire/products.dimensions_details') }}</h3>
                                </div>
                                <div class="p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">{{ __('livewire/products.weight') }}</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $weight ?: '-' }}</p>
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-gray-500">{{ __('livewire/products.width') }}</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $width ?: '-' }}</p>
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-gray-500">{{ __('livewire/products.height') }}</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $height ?: '-' }}</p>
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-gray-500">{{ __('livewire/products.depth') }}</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $depth ?: '-' }}</p>
                                </div>
                            </div>
                                </div>
                            </div>
                        </div>

                        <!-- Aba de Fornecedores -->
                        <div x-show="activeTab == 'suppliers'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95">
                            <!-- Cartão de Fornecedores -->
                            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden mb-6">
                                <div class="flex items-center bg-gradient-to-r from-green-50 to-green-100 px-4 py-3 border-b border-gray-200">
                                    <i class="fas fa-truck-loading text-green-600 mr-2"></i>
                                    <h3 class="text-base font-medium text-gray-700">{{ __('livewire/products.supplier_details') }}</h3>
                                </div>
                                <div class="p-4">
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">{{ __('livewire/products.primary_supplier') }}</p>
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{ $primary_supplier_id ? $suppliers->firstWhere('id', $primary_supplier_id)->name : '-' }}
                                    </p>
                                </div>
                            </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                    <button type="button" wire:click="closeViewModal" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <i class="fas fa-times mr-2"></i>
                        {{ __('livewire/layout.close') }}
                    </button>
                    <button type="button" wire:click="openEditModal({{ $product_id }})" class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <i class="fas fa-edit mr-2"></i>
                        {{ __('livewire/layout.edit') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="@if($showConfirmDelete) fixed @else hidden @endif inset-0 overflow-y-auto z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <!-- Modal Header -->
                <div class="bg-red-500 px-4 py-3 text-white flex justify-between items-center">
                    <h3 class="text-lg font-medium flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        {{ __('livewire/products.delete_product') }}
                    </h3>
                    <button type="button" wire:click="cancelDelete" class="text-white hover:text-gray-200 focus:outline-none">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <!-- Modal Body -->
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="mb-4 text-sm text-gray-600 bg-red-50 p-3 rounded-md border-l-4 border-red-400">
                        <p>{{ __('livewire/products.confirm_delete_product') }}</p>
                    </div>
                    
                    <!-- Exibição das informações do produto a ser excluído -->
                    <div class="bg-gray-50 rounded-md p-4 mb-4">
                        <div class="grid grid-cols-1 gap-3">
                            <div class="flex items-center">
                                <i class="fas fa-tag text-gray-400 mr-2 w-5"></i>
                                <div class="flex-1">
                                    <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('livewire/products.product_name') }}</h4>
                                    <p class="text-sm font-medium text-gray-800">{{ $name }}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center">
                                <i class="fas fa-barcode text-gray-400 mr-2 w-5"></i>
                                <div class="flex-1">
                                    <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('livewire/products.sku') }}</h4>
                                    <p class="text-sm font-medium text-gray-800">{{ $sku }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                    <button type="button" wire:click="delete" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition duration-150">
                        <i class="fas fa-trash mr-2"></i>{{ __('livewire/layout.delete') }}
                    </button>
                    <button type="button" wire:click="cancelDelete" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition duration-150">
                        <i class="fas fa-ban mr-2"></i>{{ __('livewire/layout.cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
