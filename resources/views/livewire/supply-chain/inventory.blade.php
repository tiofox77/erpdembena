<div>
    <div class="container mx-auto px-4 py-6">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
            <h1 class="text-2xl font-semibold text-gray-800 flex items-center">
                <i class="fas fa-boxes text-blue-600 mr-3"></i>
                {{ __('messages.inventory_management') }}
            </h1>
            <div class="flex flex-wrap sm:flex-nowrap gap-2">
                <button wire:click="openAdjustmentModal" 
                    class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-all duration-200 ease-in-out transform hover:scale-105 hover:shadow-lg">
                    <i class="fas fa-balance-scale mr-2"></i>
                    {{ __('messages.adjust_stock') }}
                </button>
                <button wire:click="openTransferModal" 
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 hover:shadow-lg">
                    <i class="fas fa-exchange-alt mr-2"></i>
                    {{ __('messages.transfer_stock') }}
                </button>
            </div>
        </div>

        <!-- Seção de Filtros -->
        <div class="mb-20">
            <!-- Cartão de Busca e Filtros -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 ease-in-out hover:shadow-lg transform hover:scale-[1.01]">
                <!-- Cabeçalho do cartão com gradiente -->
                <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                    <i class="fas fa-filter text-blue-600 mr-2"></i>
                    <h2 class="text-base font-medium text-gray-700">{{ __('messages.filters_and_search') }}</h2>
                </div>
                <!-- Conteúdo do cartão -->
                <div class="p-4">
                    <div class="flex flex-col gap-4">
                    <!-- Campo de busca -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-search text-gray-500 mr-1"></i>
                            {{ __('messages.search') }}
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input wire:model.debounce.300ms="search" id="search" 
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out" 
                                placeholder="{{ __('messages.search_inventory') }}" 
                                type="search">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">{{ __('messages.search_by_product_name_or_sku') }}</p>
                    </div>
                    
                    <!-- Linha de filtros -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Filtro de localização -->
                        <div>
                            <label for="locationFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-warehouse text-gray-500 mr-1"></i>
                                {{ __('messages.location') }}
                            </label>
                            <select wire:model.live="location_filter" id="locationFilter" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="">{{ __('messages.all_locations') }}</option>
                                @foreach($locations as $location)
                                <option value="{{ $location->id }}">{{ $location->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Filtro de categoria -->
                        <div>
                            <label for="categoryFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-tags text-gray-500 mr-1"></i>
                                {{ __('messages.category') }}
                            </label>
                            <select wire:model.live="category_filter" id="categoryFilter" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="">{{ __('messages.all_categories') }}</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Filtro de nível de estoque -->
                        <div>
                            <label for="stockFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-cubes text-gray-500 mr-1"></i>
                                {{ __('messages.stock_level') }}
                            </label>
                            <select wire:model.live="stock_filter" id="stockFilter" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="">{{ __('messages.all_stock_levels') }}</option>
                                <option value="low">{{ __('messages.low_stock') }}</option>
                                <option value="out">{{ __('messages.out_of_stock') }}</option>
                                <option value="in">{{ __('messages.in_stock') }}</option>
                            </select>
                        </div>
                        
                        <!-- Filtro de tipo de produto -->
                        <div>
                            <label for="productTypeFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-box-open text-gray-500 mr-1"></i>
                                {{ __('messages.product_type') }}
                            </label>
                            <select wire:model.live="product_type_filter" id="productTypeFilter" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="">{{ __('messages.all_types') }}</option>
                                <option value="finished_product">{{ __('messages.finished_product') }}</option>
                                <option value="raw_material">{{ __('messages.raw_material') }}</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Botões de ação -->
                    <div class="flex justify-end">
                        <button wire:click="resetFilters" 
                            class="flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded bg-white text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                            <i class="fas fa-redo-alt mr-2"></i>
                            {{ __('messages.reset_filters') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Seção de Listagem de Inventário -->
        <div class="mb-20">
            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden transition-all duration-300 ease-in-out hover:shadow-lg transform hover:scale-[1.01]">

            <!-- Cabeçalho da Tabela -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3 border-b border-gray-200">
                <h2 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-cubes mr-2"></i>
                    {{ __('messages.inventory_items') }}
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-box text-gray-400 mr-2"></i>
                                    {{ __('messages.product') }}
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-warehouse text-gray-400 mr-2"></i>
                                    {{ __('messages.location') }}
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-layer-group text-gray-400 mr-2"></i>
                                    {{ __('messages.stock_level') }}
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-dollar-sign text-gray-400 mr-2"></i>
                                    {{ __('messages.value') }}
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-clock text-gray-400 mr-2"></i>
                                    {{ __('messages.last_updated') }}
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center justify-end">
                                    <i class="fas fa-cogs text-gray-400 mr-2"></i>
                                    {{ __('messages.actions') }}
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($inventoryItems as $item)
                        <tr class="{{ $item->is_low_stock ? 'bg-yellow-50' : ($item->is_out_of_stock ? 'bg-red-50' : '') }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                    <div class="text-sm text-gray-500 ml-1">({{ $item->product->sku }})</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $item->location->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold">
                                    @if($item->quantity_on_hand < 0)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 shadow-sm transform transition-all duration-300 hover:scale-105">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        {{ __('messages.out_of_stock') }}
                                    </span>
                                    <span class="ml-1 text-red-600 text-base font-bold">{{ $item->quantity_on_hand }}</span>
                                    <i class="fas fa-exclamation-circle text-red-600 ml-1 animate-pulse transform transition-all duration-500 hover:rotate-12 hover:scale-110"></i>
                                    @elseif($item->is_out_of_stock)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 shadow-sm transform transition-all duration-300 hover:scale-105">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        {{ __('messages.out_of_stock') }}
                                    </span>
                                    <span class="ml-1 text-red-600 text-base font-bold">0</span>
                                    <i class="fas fa-exclamation-circle text-red-600 ml-1 animate-pulse transform transition-all duration-500 hover:rotate-12 hover:scale-110"></i>
                                    @elseif($item->is_low_stock)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 shadow-sm transform transition-all duration-300 hover:scale-105">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        {{ __('messages.low_stock') }}
                                    </span>
                                    <span class="ml-1 text-amber-600 text-base font-bold">{{ $item->quantity_on_hand }}</span>
                                    <i class="fas fa-exclamation-triangle text-amber-600 ml-1 animate-pulse transform transition-all duration-500 hover:rotate-12 hover:scale-110"></i>
                                    @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 shadow-sm transform transition-all duration-300 hover:scale-105">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        {{ __('messages.in_stock') }}
                                    </span>
                                    <span class="ml-1 text-green-600 text-base font-bold">{{ $item->quantity_on_hand }}</span>
                                    <i class="fas fa-check-circle text-green-600 ml-1 transform transition-all duration-300 hover:rotate-12 hover:scale-110"></i>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 mt-1 flex items-center">
                                    <i class="fas fa-arrow-alt-circle-down text-gray-400 mr-1"></i>
                                    {{ __('messages.reorder_point') }}: <span class="font-medium">{{ $item->product->reorder_point }}</span>
                                </div>
                                <div class="text-xs text-gray-500 mt-1 flex items-center">
                                    <i class="fas fa-boxes text-gray-400 mr-1"></i>
                                    {{ __('messages.available') }}: <span class="font-medium">{{ $item->quantity_available ?? $item->quantity_on_hand }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ number_format($item->total_value, 2) }}</div>
                                <div class="text-xs text-gray-500">@ {{ number_format($item->unit_cost, 2) }} {{ __('messages.per_unit') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $item->updated_at->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $item->updated_at->format('H:i') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <button wire:click="openHistoryModal({{ $item->id }})" 
                                        class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded bg-blue-100 text-blue-700 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                        <i class="fas fa-history mr-1"></i>
                                        {{ __('messages.history') }}
                                    </button>
                                    <button wire:click="openAdjustmentModal({{ $item->id }})" 
                                        class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded bg-yellow-100 text-yellow-700 hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                        <i class="fas fa-balance-scale mr-1"></i>
                                        {{ __('messages.adjust') }}
                                    </button>
                                    <button wire:click="openTransferModal({{ $item->id }})" 
                                        class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded bg-indigo-100 text-indigo-700 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                        <i class="fas fa-exchange-alt mr-1"></i>
                                        {{ __('messages.transfer') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center">
                                <div class="flex flex-col items-center justify-center space-y-3">
                                    <div class="bg-gray-100 rounded-full p-3">
                                        <i class="fas fa-box-open text-gray-400 text-2xl"></i>
                                    </div>
                                    <p class="text-gray-500 font-medium text-base">
                                        {{ __('messages.no_inventory_items_found') }}
                                    </p>
                                    <p class="text-gray-400 text-sm max-w-md">
                                        {{ __('messages.try_adjusting_filters') }}
                                    </p>
                                    @if(!empty($search) || $location_filter || $category_filter || $stock_filter || $product_type_filter)
                                    <button wire:click="resetFilters" class="mt-2 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                        <i class="fas fa-redo-alt mr-2"></i>
                                        {{ __('messages.clear_filters') }}
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="px-4 py-3 bg-white border-t border-gray-200 sm:px-6">
                <div class="pagination-container">
                    {{ $inventoryItems->links() }}
                </div>
            </div>
        </div>

        <!-- Seção de Transações Recentes -->
        <div class="mb-20">
            <!-- Card de Transações Recentes -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden transition-all duration-300 ease-in-out hover:shadow-lg transform hover:scale-[1.01]">
                <!-- Cabeçalho da Seção -->
                <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-4 py-3 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-history mr-2"></i>
                        {{ __('messages.recent_transactions') }}
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar-alt text-gray-400 mr-2"></i>
                                        {{ __('messages.date') }}
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center">
                                        <i class="fas fa-hashtag text-gray-400 mr-2"></i>
                                        {{ __('messages.transaction_number') }}
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center">
                                        <i class="fas fa-box text-gray-400 mr-2"></i>
                                        {{ __('messages.product') }}
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center">
                                        <i class="fas fa-tag text-gray-400 mr-2"></i>
                                        {{ __('messages.type') }}
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center">
                                        <i class="fas fa-sort-numeric-up text-gray-400 mr-2"></i>
                                        {{ __('messages.quantity') }}
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center">
                                        <i class="fas fa-warehouse text-gray-400 mr-2"></i>
                                        {{ __('messages.locations') }}
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center">
                                        <i class="fas fa-user text-gray-400 mr-2"></i>
                                        {{ __('messages.user') }}
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($recentTransactions as $transaction)
                            <tr class="{{ $transaction->getBackgroundColorClass() }} transition-all duration-300 ease-in-out transform hover:scale-[1.01]">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 font-medium">{{ $transaction->created_at->format('d/m/Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $transaction->created_at->format('H:i') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-mono font-medium text-gray-900">{{ $transaction->transaction_number }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 font-medium flex items-center">
                                        <i class="fas {{ $transaction->getIcon() }} {{ $transaction->getIconColorClass() }} mr-2"></i>
                                        {{ $transaction->product->name }}
                                    </div>
                                    <div class="text-xs text-gray-500">{{ $transaction->product->sku }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="inline-flex items-center">
                                        @if($transaction->transaction_type === 'adjustment')
                                            @if($transaction->quantity > 0)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 shadow transform transition-all duration-300 hover:scale-105 hover:bg-green-200">
                                                    <i class="fas fa-plus-circle mr-1 animate-pulse"></i>
                                                    {{ __('messages.stock_added') }}
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800 shadow transform transition-all duration-300 hover:scale-105 hover:bg-orange-200">
                                                    <i class="fas fa-minus-circle mr-1 animate-pulse"></i>
                                                    {{ __('messages.stock_removed') }}
                                                </span>
                                            @endif
                                        @elseif($transaction->transaction_type === 'purchase_receipt')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 shadow transform transition-all duration-300 hover:scale-105 hover:bg-green-200">
                                                <i class="fas fa-truck-loading mr-1"></i>
                                                {{ __('messages.purchase_receipt') }}
                                            </span>
                                        @elseif($transaction->transaction_type === 'sales_issue')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800 shadow transform transition-all duration-300 hover:scale-105 hover:bg-orange-200">
                                                <i class="fas fa-shopping-cart mr-1"></i>
                                                {{ __('messages.sales_issue') }}
                                            </span>
                                        @elseif($transaction->transaction_type === 'production')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800 shadow transform transition-all duration-300 hover:scale-105 hover:bg-purple-200">
                                                <i class="fas fa-industry mr-1"></i>
                                                {{ __('messages.production') }}
                                            </span>
                                        @elseif($transaction->transaction_type === 'raw_production' || $transaction->transaction_type === 'daily_production')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 shadow transform transition-all duration-300 hover:scale-105 hover:bg-red-200">
                                                <i class="fas fa-minus-circle mr-1"></i>
                                                {{ __('messages.raw_material') }}
                                            </span>
                                        @elseif($transaction->transaction_type === 'production_order' || $transaction->transaction_type === 'daily_production_fg')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 shadow transform transition-all duration-300 hover:scale-105 hover:bg-green-200">
                                                <i class="fas fa-plus-circle mr-1"></i>
                                                {{ __('messages.production_order') }}
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 shadow transform transition-all duration-300 hover:scale-105 hover:bg-blue-200">
                                                <i class="fas fa-exchange-alt mr-1"></i>
                                                {{ __('messages.stock_transfer') }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($transaction->transaction_type === 'raw_production' || $transaction->transaction_type === 'daily_production')
                                        <div class="text-sm font-bold text-red-600 flex items-center animate-fadeIn transition-all duration-300 hover:scale-110">
                                            <span class="bg-red-100 text-red-800 rounded-full w-6 h-6 flex items-center justify-center mr-1">-</span>
                                            {{ number_format(abs($transaction->quantity), 2) }}
                                        </div>
                                    @elseif($transaction->quantity > 0)                                        
                                        <div class="text-sm font-bold text-green-600 flex items-center animate-fadeIn transition-all duration-300 hover:scale-110">
                                            <span class="bg-green-100 text-green-800 rounded-full w-6 h-6 flex items-center justify-center mr-1">+</span>
                                            {{ number_format($transaction->quantity, 2) }}
                                        </div>
                                    @elseif($transaction->quantity < 0)
                                        <div class="text-sm font-bold text-red-600 flex items-center animate-fadeIn transition-all duration-300 hover:scale-110">
                                            <span class="bg-red-100 text-red-800 rounded-full w-6 h-6 flex items-center justify-center mr-1">-</span>
                                            {{ number_format(abs($transaction->quantity), 2) }}
                                        </div>
                                    @else
                                        <div class="text-sm font-medium text-gray-600 flex items-center">
                                            <span class="bg-gray-100 text-gray-800 rounded-full w-6 h-6 flex items-center justify-center mr-1">0</span>
                                            {{ number_format($transaction->quantity, 2) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($transaction->source_location_id && $transaction->destination_location_id)
                                    <div class="text-sm text-indigo-700 font-medium flex items-center">
                                        <span class="flex items-center">
                                            <i class="fas fa-warehouse text-gray-600 mr-1"></i>
                                            {{ $transaction->sourceLocation->name }}
                                        </span>
                                        <span class="mx-2 text-indigo-500 transform transition-all duration-500 hover:translate-x-1">
                                            <i class="fas fa-long-arrow-alt-right animate-pulse"></i>
                                        </span>
                                        <span class="flex items-center">
                                            <i class="fas fa-warehouse text-gray-600 mr-1"></i>
                                            {{ $transaction->destinationLocation->name }}
                                        </span>
                                    </div>
                                    @elseif($transaction->source_location_id)
                                    <div class="text-sm text-red-600 font-medium flex items-center">
                                        <i class="fas fa-warehouse text-red-500 mr-1"></i>
                                        {{ $transaction->sourceLocation->name }}
                                        <span class="ml-2 text-red-400">
                                            <i class="fas fa-arrow-alt-circle-down"></i>
                                        </span>
                                    </div>
                                    @elseif($transaction->destination_location_id)
                                    <div class="text-sm text-green-600 font-medium flex items-center">
                                        <i class="fas fa-warehouse text-green-500 mr-1"></i>
                                        {{ $transaction->destinationLocation->name }}
                                        <span class="ml-2 text-green-400">
                                            <i class="fas fa-arrow-alt-circle-up"></i>
                                        </span>
                                    </div>
                                    @else
                                    <div class="text-sm text-gray-500 italic flex items-center">
                                        <i class="fas fa-question-circle text-gray-400 mr-1"></i>
                                        {{ __('messages.no_location') }}
                                    </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $transaction->creator->name }}</div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-3">
                                        <div class="bg-gray-100 rounded-full p-3">
                                            <i class="fas fa-history text-gray-400 text-2xl"></i>
                                        </div>
                                        <p class="text-gray-500 font-medium text-base">
                                            {{ __('messages.no_recent_transactions') }}
                                        </p>
                                        <p class="text-gray-400 text-sm max-w-md">
                                            {{ __('messages.transactions_will_appear_here') }}
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Modal de Ajuste de Estoque -->
        <div x-data="{ open: @entangle('showAdjustmentModal') }" 
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
                
                <!-- Cabeçalho -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-boxes mr-2 animate-pulse"></i>
                        {{ __('messages.adjust_stock') }}
                    </h3>
                    <button type="button" wire:click="closeAdjustmentModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form wire:submit.prevent="saveAdjustment">
                    <!-- Product Information -->
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 m-4">
                        <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                            <i class="fas fa-box text-blue-600 mr-2"></i>
                            <h2 class="text-base font-medium text-gray-700">{{ __('messages.product_information') }}</h2>
                        </div>
                        <div class="p-4 space-y-4">
                            <!-- Product Search -->
                            <div>
                                <label for="productSearch" class="block text-sm font-medium text-gray-700 flex items-center">
                                    <i class="fas fa-search mr-1 text-blue-500"></i>
                                    {{ __('messages.search_products') }}
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                    <input type="text" wire:model.live.debounce.300ms="productSearchQuery" id="productSearch"
                                        class="pl-10 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                        placeholder="{{ __('messages.type_product_name_or_sku') }}">
                                </div>
                            </div>
                            
                            <!-- Select Products -->
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2 flex justify-between">
                                    <div>
                                        <i class="fas fa-box-open mr-1 text-blue-500"></i>
                                        {{ __('messages.select_products') }} <span class="text-red-500">*</span>
                                    </div>
                                    @if(count($selectedProducts) > 0)
                                        <span class="text-blue-600 text-xs font-semibold bg-blue-100 px-2 py-1 rounded-full">
                                            {{ count($selectedProducts) }} {{ __('messages.selected') }}
                                        </span>
                                    @endif
                                </label>
                                <div class="mt-1 overflow-y-auto max-h-48 border border-gray-200 rounded-md">
                                    @if(count($this->filteredProducts) === 0)
                                        <div class="p-4 text-center text-gray-500">
                                            <i class="fas fa-box-open text-gray-300 text-2xl mb-2"></i>
                                            <p>{{ __('messages.no_products_found') }}</p>
                                        </div>
                                    @else
                                        <div class="grid grid-cols-1 divide-y divide-gray-200">
                                            @foreach($this->filteredProducts as $product)
                                                <label class="flex items-center py-2 px-3 hover:bg-gray-50 cursor-pointer transition-colors duration-150 @if(in_array($product->id, $selectedProducts)) bg-blue-50 @endif">
                                                    <input type="checkbox" 
                                                        wire:model.live="selectedProducts" 
                                                        value="{{ $product->id }}" 
                                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                                    <div class="ml-3 flex-1">
                                                        <div class="text-sm font-medium text-gray-900 flex items-center">
                                                            {{ $product->name }}
                                                            <span class="ml-2 text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ $product->sku }}</span>
                                                        </div>
                                                        @php
                                                            $stockInfo = $product->inventoryItems->firstWhere('location_id', $selectedLocationId);
                                                            $stockQty = $stockInfo ? $stockInfo->quantity_on_hand : 0;
                                                        @endphp
                                                        <div class="text-xs text-gray-500 mt-1">
                                                            @if($selectedLocationId)
                                                                <span class="@if($stockQty <= 0) text-red-500 @elseif($stockQty < 10) text-yellow-500 @else text-green-600 @endif">
                                                                    <i class="fas fa-cubes mr-1"></i> {{ __('messages.current_stock') }}: {{ $stockQty }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                @error('selectedProducts') 
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Select Location -->
                            <div>
                                <label for="selectedLocationId" class="block text-sm font-medium text-gray-700">
                                    {{ __('messages.select_location') }} <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1">
                                    <select wire:model.live="selectedLocationId" id="selectedLocationId"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white">
                                        <option value="">{{ __('messages.select_option') }}</option>
                                        @foreach($availableLocations as $location)
                                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('selectedLocationId') 
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Adjustment Options -->
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 m-4">
                        <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                            <i class="fas fa-sliders-h text-blue-600 mr-2"></i>
                            <h2 class="text-base font-medium text-gray-700">{{ __('messages.adjustment_options') }}</h2>
                        </div>
                        <div class="p-4 space-y-4">
                            <!-- Adjustment Type -->
                            <div>
                                <label for="adjustmentType" class="block text-sm font-medium text-gray-700">
                                    {{ __('messages.adjustment_type') }} <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1">
                                    <select wire:model.live="adjustmentType" id="adjustmentType"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white">
                                        <option value="">{{ __('messages.select_option') }}</option>
                                        <option value="add">{{ __('messages.add_stock') }}</option>
                                        <option value="remove">{{ __('messages.remove_stock') }}</option>
                                        <option value="set">{{ __('messages.set_stock') }}</option>
                                    </select>
                                </div>
                                @error('adjustmentType') 
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Quantities -->
                            @if(count($selectedProducts) > 0 && $selectedLocationId && $adjustmentType)
                                @foreach($selectedProducts as $productId)
                                    <div>
                                        @php
                                            $product = $availableProducts->firstWhere('id', $productId);
                                            $currentStock = $product ? 
                                                \App\Models\SupplyChain\InventoryItem::where('product_id', $productId)
                                                    ->where('location_id', $selectedLocationId)
                                                    ->value('quantity_on_hand') ?? 0 : 0;
                                        @endphp
                                        <label for="quantity_{{ $productId }}" class="block text-sm font-medium text-gray-700">
                                            {{ __('messages.quantity_for') }} {{ $product->name }} <span class="text-red-500">*</span>
                                        </label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <input type="number" 
                                                id="quantity_{{ $productId }}"
                                                wire:model.live="adjustmentQuantities.{{ $productId }}"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white"
                                                placeholder="0"
                                                min="0"
                                                step="1">
                                            <div class="mt-1 text-sm text-gray-500">
                                                {{ __('messages.current_stock') }}: {{ $currentStock }}
                                            </div>
                                        </div>
                                        @error('adjustmentQuantities.'.$productId) 
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endforeach
                            @endif

                            <!-- Adjustment Reason -->
                            <div>
                                <label for="adjustmentReason" class="block text-sm font-medium text-gray-700">
                                    {{ __('messages.adjustment_reason') }} <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1">
                                    <input type="text" id="adjustmentReason" wire:model.live="adjustmentReason"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white"
                                        placeholder="{{ __('messages.enter_adjustment_reason') }}">
                                </div>
                                @error('adjustmentReason') 
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Notes -->
                            <div>
                                <label for="adjustmentNotes" class="block text-sm font-medium text-gray-700">
                                    {{ __('messages.notes') }}
                                </label>
                                <div class="mt-1">
                                    <textarea id="adjustmentNotes" wire:model.live="adjustmentNotes" rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white"
                                        placeholder="{{ __('messages.enter_notes') }}"></textarea>
                                </div>
                                @error('adjustmentNotes') 
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Footer com botões -->
                    <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                        <button type="button" wire:click="closeAdjustmentModal" 
                            class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                            <i class="fas fa-times mr-2"></i>
                            {{ __('messages.cancel') }}
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="saveAdjustment">
                                <i class="fas fa-save mr-2"></i>
                                {{ __('messages.save') }}
                            </span>
                            <span wire:loading wire:target="saveAdjustment" class="inline-flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ __('messages.saving') }}
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Transferência de Estoque -->
    <div x-data="{ open: @entangle('showTransferModal') }" 
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
                
                <!-- Cabeçalho -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-exchange-alt mr-2"></i>
                        {{ __('messages.transfer_stock') }}
                    </h3>
                    <button type="button" wire:click="closeTransferModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Conteúdo -->
                <div class="p-6 space-y-6">
                    <!-- Product Information -->
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
                        <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                            <i class="fas fa-box text-blue-600 mr-2"></i>
                            <h2 class="text-base font-medium text-gray-700">{{ __('messages.product_information') }}</h2>
                        </div>
                        <div class="p-4 space-y-4">
                            <!-- Filtros e Busca -->
                            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-4 transition-all duration-300 ease-in-out hover:shadow-md">
                                <h5 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                                    <i class="fas fa-filter mr-2 text-blue-500"></i> {{ __('messages.filters_and_search') }}
                                </h5>
                                
                                <!-- Busca -->
                                <div class="mb-3">
                                    <label for="transferProductSearch" class="block text-sm font-medium text-gray-700 flex items-center">
                                        <i class="fas fa-search mr-1 text-blue-500"></i>
                                        {{ __('messages.search_products') }}
                                    </label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-search text-gray-400"></i>
                                        </div>
                                        <input type="text" wire:model.live.debounce.300ms="transferProductSearchQuery" id="transferProductSearch"
                                            class="pl-10 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                            placeholder="{{ __('messages.type_product_name_or_sku') }}">
                                    </div>
                                </div>
                                
                                <!-- Filtros -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Filtro de Tipo de Produto -->
                                    <div>
                                        <label for="transferProductTypeFilter" class="block text-sm font-medium text-gray-700 flex items-center">
                                            <i class="fas fa-box mr-1 text-blue-500"></i>
                                            {{ __('messages.product_type') }}
                                        </label>
                                        <select id="transferProductTypeFilter" wire:model.live="transferProductTypeFilter"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white">
                                            <option value="">{{ __('messages.all_types') }}</option>
                                            <option value="finished_product">{{ __('messages.finished_product') }}</option>
                                            <option value="raw_material">{{ __('messages.raw_material') }}</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Checkbox para Produtos com Estoque -->
                                    <div class="flex items-center">
                                        <div class="flex h-5 items-center mt-4">
                                            <input id="showOnlyWithStock" type="checkbox" wire:model.live="showOnlyWithStock"
                                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                {{ empty($transferSourceId) ? 'disabled' : '' }}>
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="showOnlyWithStock" class="font-medium text-gray-700 {{ empty($transferSourceId) ? 'opacity-50' : '' }}">
                                                {{ __('messages.only_products_with_stock') }}
                                            </label>
                                            @if(empty($transferSourceId))
                                                <p class="text-xs text-amber-600">
                                                    {{ __('messages.select_source_to_see_stock') }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Select Products for Transfer -->
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2 flex justify-between">
                                    <div>
                                        <i class="fas fa-box-open mr-1 text-blue-500"></i>
                                        {{ __('messages.select_products') }} <span class="text-red-500">*</span>
                                    </div>
                                    @if(count($selectedTransferProducts) > 0)
                                        <span class="text-blue-600 text-xs font-semibold bg-blue-100 px-2 py-1 rounded-full">
                                            {{ count($selectedTransferProducts) }} {{ __('messages.selected') }}
                                        </span>
                                    @endif
                                </label>
                                
                                <div class="mt-1 overflow-y-auto max-h-48 border border-gray-200 rounded-md">
                                    @if(count($this->filteredTransferProducts) === 0)
                                        <div class="p-4 text-center text-gray-500">
                                            <i class="fas fa-box-open text-gray-300 text-2xl mb-2"></i>
                                            <p>{{ __('messages.no_products_found') }}</p>
                                        </div>
                                    @else
                                        <div class="grid grid-cols-1 divide-y divide-gray-200">
                                            @foreach($this->filteredTransferProducts as $product)
                                                @php
                                                    $sourceStock = $transferSourceId ? 
                                                        $product->inventoryItems->where('location_id', $transferSourceId)->first()?->quantity_on_hand ?? 0 : 0;
                                                    
                                                    $stockClass = $sourceStock <= 0 ? 'bg-red-100 text-red-800' :
                                                                 ($sourceStock <= 3 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800');
                                                    
                                                    $stockIcon = $sourceStock <= 0 ? 'fa-times-circle' :
                                                                ($sourceStock <= 3 ? 'fa-exclamation-triangle' : 'fa-check-circle');
                                                @endphp
                                                <label class="flex items-center py-3 px-4 hover:bg-blue-50 @if($sourceStock <= 0) opacity-75 @endif cursor-pointer transition-colors duration-200 ease-in-out @if(in_array($product->id, $selectedTransferProducts)) bg-blue-50 border-l-4 border-blue-500 @else border-l-4 border-transparent @endif rounded-md mb-1 shadow-sm hover:shadow-md">
                                                    <input type="checkbox" 
                                                        wire:model.live="selectedTransferProducts" 
                                                        value="{{ $product->id }}" 
                                                        @if($sourceStock <= 0) disabled @endif
                                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                                    <div class="ml-3 flex-1">
                                                        <div class="text-sm font-medium text-gray-900 flex items-center">
                                                            {{ $product->name }}
                                                            <span class="ml-2 text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ $product->sku }}</span>
                                                            @if($product->product_type === 'finished_product')
                                                                <span class="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full">
                                                                    <i class="fas fa-box-open mr-1"></i>{{ __('messages.finished_product') }}
                                                                </span>
                                                            @elseif($product->product_type === 'raw_material')
                                                                <span class="ml-2 text-xs bg-purple-100 text-purple-800 px-2 py-0.5 rounded-full">
                                                                    <i class="fas fa-cubes mr-1"></i>{{ __('messages.raw_material') }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                        
                                                        <div class="mt-2 flex items-center">
                                                            <span class="{{ $stockClass }} text-xs font-medium px-2.5 py-0.5 rounded-full flex items-center">
                                                                <i class="fas {{ $stockIcon }} mr-1"></i>
                                                                @if($sourceStock <= 0)
                                                                    {{ __('messages.out_of_stock') }}
                                                                @elseif($sourceStock <= 3)
                                                                    {{ __('messages.low_stock') }}
                                                                @else
                                                                    {{ __('messages.in_stock') }}
                                                                @endif
                                                            </span>
                                                            
                                                            <span class="ml-2 text-xs text-gray-600">
                                                                <i class="fas fa-boxes mr-1"></i>
                                                                {{ __('messages.available') }}: <span class="font-semibold">{{ $sourceStock }}</span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    
                                                    @if(in_array($product->id, $selectedTransferProducts))
                                                        <div class="ml-2 text-blue-600">
                                                            <i class="fas fa-check-circle text-xl"></i>
                                                        </div>
                                                    @elseif($sourceStock > 0)
                                                        <div class="ml-2 text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                            <i class="fas fa-plus-circle"></i>
                                                        </div>
                                                    @endif
                                                </label>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                @error('selectedTransferProducts') 
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Transfer Options -->
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
                        <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                            <i class="fas fa-map-marker-alt text-blue-600 mr-2"></i>
                            <h2 class="text-base font-medium text-gray-700">{{ __('messages.transfer_options') }}</h2>
                        </div>
                        <div class="p-4 space-y-4">
                            <!-- Source Location -->
                            <div>
                                <label for="transferSourceId" class="block text-sm font-medium text-gray-700">
                                    {{ __('messages.source_location') }} <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1">
                                    <select wire:model.live="transferSourceId" id="transferSourceId"
                                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md bg-white">
                                        <option value="">{{ __('messages.select_option') }}</option>
                                        @foreach($availableLocations as $location)
                                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('transferSourceId') 
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Destination Location -->
                            <div>
                                <label for="transferDestinationId" class="block text-sm font-medium text-gray-700">
                                    {{ __('messages.destination_location') }} <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1">
                                    <select wire:model="transferDestinationId" id="transferDestinationId"
                                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md bg-white">
                                        <option value="">{{ __('messages.select_option') }}</option>
                                        @foreach($availableLocations as $location)
                                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('transferDestinationId') 
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Quantities -->
                            @foreach($selectedTransferProducts as $productId)
                                <div>
                                    @php
                                        $product = $availableProducts->firstWhere('id', $productId);
                                        $currentStock = $product && $transferSourceId ? 
                                            $product->inventoryItems->where('location_id', $transferSourceId)->first()?->quantity ?? 0 : 0;
                                    @endphp
                                    <label class="block text-sm font-medium text-gray-700">
                                        {{ $product ? $product->name : '' }} - {{ __('messages.quantity') }} 
                                        <span class="text-red-500">*</span>
                                        <span class="text-gray-500 text-sm">({{ __('messages.available') }}: {{ $currentStock }})</span>
                                    </label>
                                    <div class="mt-1">
                                        <input type="number" 
                                               wire:model="transferQuantities.{{ $productId }}"
                                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white"
                                               placeholder="0"
                                               max="{{ $currentStock }}">
                                    </div>
                                    @error('transferQuantities.'.$productId) 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endforeach

                            <!-- Transfer Reason -->
                            <div>
                                <label for="transferReason" class="block text-sm font-medium text-gray-700">
                                    {{ __('messages.transfer_reason') }} <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1">
                                    <input type="text" 
                                           wire:model="transferReason" 
                                           id="transferReason"
                                           class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white"
                                           placeholder="{{ __('messages.enter_transfer_reason') }}">
                                </div>
                                @error('transferReason') 
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Notes -->
                            <div>
                                <label for="transferNotes" class="block text-sm font-medium text-gray-700">
                                    {{ __('messages.notes') }}
                                </label>
                                <div class="mt-1">
                                    <textarea wire:model="transferNotes" 
                                              id="transferNotes" 
                                              rows="3"
                                              class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white"
                                              placeholder="{{ __('messages.enter_notes') }}"></textarea>
                                </div>
                                @error('transferNotes') 
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                    <button type="button" wire:click="closeTransferModal" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <i class="fas fa-times mr-2"></i>
                        {{ __('messages.cancel') }}
                    </button>
                    <button type="button" wire:click="saveTransfer" wire:loading.attr="disabled" class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="saveTransfer">
                            <i class="fas fa-exchange-alt mr-2"></i>
                            {{ __('messages.transfer') }}
                        </span>
                        <span wire:loading wire:target="saveTransfer" class="inline-flex items-center">
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

    <!-- Modal de Histórico de Transações -->
    <div x-data="{ open: @entangle('showHistoryModal') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-1 w-full max-w-4xl">
            <div class="relative bg-white rounded-lg shadow-xl">
                <!-- Cabeçalho -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-lg px-4 py-3 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-history mr-2"></i>
                        {{ __('messages.transaction_history') }}
                    </h3>
                    <button type="button" wire:click="closeHistoryModal" class="text-white hover:text-gray-200 focus:outline-none">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Corpo da modal -->
                <div class="p-6 space-y-6">
                    <!-- Informações do produto -->
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
                        <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                            <i class="fas fa-box text-blue-600 mr-2"></i>
                            <h2 class="text-base font-medium text-gray-700">{{ __('messages.product_information') }}</h2>
                        </div>
                        <div class="p-4">
                            @if($historyItem)
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0 bg-blue-100 p-2 rounded-full">
                                    <i class="fas fa-box text-blue-600 text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-medium text-gray-900">{{ $historyItem->product->name }}</h4>
                                    <div class="text-sm text-gray-500 mt-1">{{ __('messages.sku') }}: {{ $historyItem->product->sku }}</div>
                                    <div class="text-sm text-gray-500">{{ __('messages.current_stock') }}: {{ $historyItem->quantity }} @ {{ $historyItem->location->name }}</div>
                                </div>
                            </div>
                            @else
                            <div class="text-gray-500 text-center">{{ __('messages.no_product_selected') }}</div>
                            @endif
                        </div>
                    </div>

                    <!-- Tabela de histórico -->
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
                        <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                            <i class="fas fa-history text-blue-600 mr-2"></i>
                            <h2 class="text-base font-medium text-gray-700">{{ __('messages.transaction_records') }}</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center">
                                                <i class="fas fa-calendar-alt text-gray-400 mr-2"></i>
                                                {{ __('messages.date') }}
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center">
                                                <i class="fas fa-tag text-gray-400 mr-2"></i>
                                                {{ __('messages.type') }}
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center">
                                                <i class="fas fa-sort-numeric-up text-gray-400 mr-2"></i>
                                                {{ __('messages.quantity') }}
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center">
                                                <i class="fas fa-info-circle text-gray-400 mr-2"></i>
                                                {{ __('messages.details') }}
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center">
                                                <i class="fas fa-user text-gray-400 mr-2"></i>
                                                {{ __('messages.user') }}
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($inventoryItemHistory as $transaction)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $transaction->created_at->format('d/m/Y') }}</div>
                                            <div class="text-xs text-gray-500">{{ $transaction->created_at->format('H:i') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="inline-flex items-center">
                                                @if($transaction->transaction_type === 'adjustment')
                                                    @if($transaction->quantity > 0)
                                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                                            <i class="fas fa-plus-circle mr-1"></i>
                                                            {{ __('messages.stock_added') }}
                                                        </span>
                                                    @else
                                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                                            <i class="fas fa-minus-circle mr-1"></i>
                                                            {{ __('messages.stock_removed') }}
                                                        </span>
                                                    @endif
                                                @elseif($transaction->transaction_type === 'transfer')
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                                        <i class="fas fa-exchange-alt mr-1"></i>
                                                        {{ __('messages.stock_transfer') }}
                                                    </span>
                                                @elseif($transaction->transaction_type === 'raw_production')
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800">
                                                        <i class="fas fa-industry mr-1"></i>
                                                        {{ __('messages.raw_material') }}
                                                    </span>
                                                @elseif($transaction->transaction_type === 'production_order')
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-800">
                                                        <i class="fas fa-cogs mr-1"></i>
                                                        {{ __('messages.production_order') }}
                                                    </span>
                                                @elseif($transaction->transaction_type === 'purchase_receipt')
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                                                        <i class="fas fa-truck-loading mr-1"></i>
                                                        {{ __('messages.purchase_receipt') }}
                                                    </span>
                                                @elseif($transaction->transaction_type === 'sales_issue')
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                                        <i class="fas fa-shopping-cart mr-1"></i>
                                                        {{ __('messages.sales_issue') }}
                                                    </span>
                                                @elseif($transaction->transaction_type === 'production')
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                                        <i class="fas fa-industry mr-1"></i>
                                                        {{ __('messages.production') }}
                                                    </span>
                                                @elseif($transaction->transaction_type === 'production_receipt')
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                                        <i class="fas fa-box-open mr-1"></i>
                                                        {{ __('messages.production_receipt') }}
                                                    </span>
                                                @elseif($transaction->transaction_type === 'production_issue')
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-orange-100 text-orange-800">
                                                        <i class="fas fa-box mr-1"></i>
                                                        {{ __('messages.production_issue') }}
                                                    </span>
                                                @elseif($transaction->transaction_type === 'daily_production')
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                                        <i class="fas fa-arrow-down mr-1"></i>
                                                        {{ __('messages.raw_material_consumption') ?? 'Raw Material Consumption' }}
                                                    </span>
                                                @elseif($transaction->transaction_type === 'daily_production_fg')
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                                        <i class="fas fa-arrow-up mr-1"></i>
                                                        {{ __('messages.finished_goods_addition') ?? 'Finished Goods Addition' }}
                                                    </span>
                                                @else
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                                                        <i class="fas fa-question-circle mr-1"></i>
                                                        {{ $transaction->transaction_type }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium {{ $transaction->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $transaction->quantity > 0 ? '+' : '' }}{{ number_format($transaction->quantity, 2) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($transaction->source_location_id && $transaction->destination_location_id)
                                            <div class="text-sm text-gray-900 flex items-center">
                                                <span class="mr-1">{{ $transaction->sourceLocation->name }}</span>
                                                <i class="fas fa-long-arrow-alt-right text-gray-400 mx-1"></i>
                                                <span class="ml-1">{{ $transaction->destinationLocation->name }}</span>
                                            </div>
                                            @endif
                                            <div class="text-sm text-gray-500 mt-1">
                                                {{ $transaction->notes ?? '-' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $transaction->creator->name }}</div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center">
                                            <div class="flex flex-col items-center justify-center py-4 space-y-2">
                                                <div class="flex-shrink-0 bg-gray-100 p-2 rounded-full">
                                                    <i class="fas fa-history text-gray-400 text-2xl"></i>
                                                </div>
                                                <p class="text-gray-500 text-sm">{{ __('messages.no_transaction_history') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Rodapé com botões de ação -->
                <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                    <button type="button" wire:click="closeHistoryModal" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <i class="fas fa-check mr-2"></i>
                        {{ __('messages.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Alerts -->
    <div x-data="{ show: false, message: '', type: '' }"
         x-on:notify.window="show = true; message = $event.detail.message; type = $event.detail.type; setTimeout(() => { show = false }, 3000)"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-90"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-90"
         class="fixed top-4 right-4 z-50"
         x-cloak>
        <div x-show="type === 'success'" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm" x-text="message"></p>
                </div>
            </div>
        </div>
        <div x-show="type === 'error'" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm" x-text="message"></p>
                </div>
            </div>
        </div>
    </div>
</div>
