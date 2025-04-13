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
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Filtro de localização -->
                        <div>
                            <label for="locationFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-warehouse text-gray-500 mr-1"></i>
                                {{ __('messages.location') }}
                            </label>
                            <select wire:model="locationFilter" id="locationFilter" 
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
                            <select wire:model="categoryFilter" id="categoryFilter" 
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
                            <select wire:model="stockFilter" id="stockFilter" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="">{{ __('messages.all_stock_levels') }}</option>
                                <option value="low">{{ __('messages.low_stock') }}</option>
                                <option value="out">{{ __('messages.out_of_stock') }}</option>
                                <option value="in">{{ __('messages.in_stock') }}</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Botões de ação -->
                    <div class="flex justify-end">
                        <button wire:click="resetFilters" 
                            class="flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
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
                                <div class="text-sm">
                                    @if($item->is_out_of_stock)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        {{ __('messages.out_of_stock') }} (0)
                                    </span>
                                    @elseif($item->is_low_stock)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        {{ __('messages.low_stock') }} ({{ $item->quantity }})
                                    </span>
                                    @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ $item->quantity }}
                                    </span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">{{ __('messages.reorder_point') }}: {{ $item->product->reorder_point }}</div>
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
                                    @if(!empty($search) || $locationFilter || $stockFilter)
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
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $transaction->created_at->format('d/m/Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $transaction->created_at->format('H:i') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $transaction->transaction_number }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $transaction->product->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $transaction->product->sku }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $transaction->transaction_type == 'purchase_receipt' ? 'bg-green-100 text-green-800' : 
                                          ($transaction->transaction_type == 'transfer' ? 'bg-blue-100 text-blue-800' : 
                                          ($transaction->transaction_type == 'adjustment' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) }}">
                                        {{ __(str_replace('_', ' ', ucfirst($transaction->transaction_type))) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium {{ $transaction->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $transaction->quantity > 0 ? '+' : '' }}{{ $transaction->quantity }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($transaction->source_location_id && $transaction->destination_location_id)
                                    <div class="text-sm text-gray-900">
                                        {{ $transaction->sourceLocation->name }} → {{ $transaction->destinationLocation->name }}
                                    </div>
                                    @elseif($transaction->source_location_id)
                                    <div class="text-sm text-gray-900">{{ $transaction->sourceLocation->name }}</div>
                                    @elseif($transaction->destination_location_id)
                                    <div class="text-sm text-gray-900">{{ $transaction->destinationLocation->name }}</div>
                                    @else
                                    <div class="text-sm text-gray-900">-</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $transaction->user->name }}</div>
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
    </div>

    <!-- Modal de Ajuste de Estoque -->
    <div>
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
            <div class="relative top-20 mx-auto p-1 w-full max-w-2xl">
                <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" 
                     x-transition:enter="transition ease-out duration-300" 
                     x-transition:enter-start="transform opacity-0 scale-95" 
                     x-transition:enter-end="transform opacity-100 scale-100" 
                     x-transition:leave="transition ease-in duration-200" 
                     x-transition:leave-start="transform opacity-100 scale-100" 
                     x-transition:leave-end="transform opacity-0 scale-95">
                    <!-- Cabeçalho com gradiente -->
                    <div class="bg-gradient-to-r from-yellow-600 to-yellow-700 rounded-t-lg px-4 py-3 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <i class="fas fa-balance-scale mr-2 animate-pulse"></i>
                            {{ __('messages.adjust_stock') }}
                        </h3>
                        <button type="button" wire:click="closeAdjustmentModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <!-- Corpo da modal -->
                    <form wire:submit.prevent="saveAdjustment">
                        <div class="p-6 space-y-6">
                            <!-- Informações do produto -->
                            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                                <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                                    <i class="fas fa-box text-blue-600 mr-2"></i>
                                    <h3 class="text-base font-medium text-gray-700">{{ __('messages.product_information') }}</h3>
                                </div>
                                <div class="p-4">
                                    @if($transferItem)
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0 bg-blue-100 p-2 rounded-full">
                                            <i class="fas fa-box text-blue-600 text-xl"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-lg font-medium text-gray-900">{{ $transferItem->product->name }}</h4>
                                            <div class="text-sm text-gray-500 mt-1">{{ __('messages.sku') }}: {{ $transferItem->product->sku }}</div>
                                            <div class="text-sm text-gray-500">{{ __('messages.current_stock') }}: {{ $transferItem->quantity }} @ {{ $transferItem->location->name }}</div>
                                        </div>
                                    </div>
                                    @else
                                    <div class="text-gray-500 text-center">{{ __('messages.no_product_selected') }}</div>
                                    @endif
                                </div>
                            </div>

                            <!-- Opções de ajuste -->
                            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                                <div class="flex items-center bg-gradient-to-r from-yellow-50 to-yellow-100 px-4 py-3 border-b border-gray-200">
                                    <i class="fas fa-sliders-h text-yellow-600 mr-2"></i>
                                    <h3 class="text-base font-medium text-gray-700">{{ __('messages.adjustment_options') }}</h3>
                                </div>
                                <div class="p-4 space-y-4">
                                    <!-- Tipo de ajuste -->
                                    <div>
                                        <label for="adjustmentType" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.adjustment_type') }}</label>
                                        <select wire:model="adjustmentType" id="adjustmentType" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring focus:ring-yellow-500 focus:ring-opacity-50 sm:text-sm bg-white">
                                            <option value="add">{{ __('messages.add_stock') }}</option>
                                            <option value="remove">{{ __('messages.remove_stock') }}</option>
                                            <option value="set">{{ __('messages.set_stock_level') }}</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Quantidade -->
                                    <div>
                                        <label for="adjustmentQuantity" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.quantity') }}</label>
                                        <input type="number" wire:model="adjustmentQuantity" id="adjustmentQuantity" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring focus:ring-yellow-500 focus:ring-opacity-50 sm:text-sm bg-white" placeholder="{{ __('messages.enter_adjustment_quantity') }}">
                                        @error('adjustmentQuantity') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <!-- Motivo -->
                                    <div>
                                        <label for="adjustmentReason" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.reason') }}</label>
                                        <select wire:model="adjustmentReason" id="adjustmentReason" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring focus:ring-yellow-500 focus:ring-opacity-50 sm:text-sm bg-white">
                                            <option value="">{{ __('messages.select_reason') }}</option>
                                            <option value="count">{{ __('messages.physical_count') }}</option>
                                            <option value="damaged">{{ __('messages.damaged_items') }}</option>
                                            <option value="expired">{{ __('messages.expired_items') }}</option>
                                            <option value="returned">{{ __('messages.customer_return') }}</option>
                                            <option value="correction">{{ __('messages.system_correction') }}</option>
                                            <option value="other">{{ __('messages.other_reason') }}</option>
                                        </select>
                                        @error('adjustmentReason') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <!-- Notes -->
                                    <div>
                                        <label for="adjustmentNotes" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.notes') }}</label>
                                        <textarea wire:model="adjustmentNotes" id="adjustmentNotes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring focus:ring-yellow-500 focus:ring-opacity-50 sm:text-sm bg-white" placeholder="{{ __('messages.enter_adjustment_notes') }}"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Rodapé com botões de ação -->
                        <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                            <button type="button" wire:click="closeAdjustmentModal" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                <i class="fas fa-times mr-2"></i>
                                {{ __('messages.cancel') }}
                            </button>
                            <button type="submit" wire:loading.attr="disabled" class="inline-flex justify-center items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="saveAdjustment">
                                    <i class="fas fa-save mr-2"></i>
                                    {{ __('messages.save_adjustment') }}
                                </span>
                                <span wire:loading wire:target="saveAdjustment" class="inline-flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ __('messages.processing') }}
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Transferência de Estoque -->
    <div>
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
            <div class="relative top-20 mx-auto p-1 w-full max-w-2xl">
                <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" 
                     x-transition:enter="transition ease-out duration-300" 
                     x-transition:enter-start="transform opacity-0 scale-95" 
                     x-transition:enter-end="transform opacity-100 scale-100" 
                     x-transition:leave="transition ease-in duration-200" 
                     x-transition:leave-start="transform opacity-100 scale-100" 
                     x-transition:leave-end="transform opacity-0 scale-95">
                    <!-- Cabeçalho com gradiente -->
                    <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <i class="fas fa-exchange-alt mr-2 animate-pulse"></i>
                            {{ __('messages.transfer_stock') }}
                        </h3>
                        <button type="button" wire:click="closeTransferModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <!-- Corpo da modal -->
                    <form wire:submit.prevent="saveTransfer">
                        <div class="p-6 space-y-6">
                            <!-- Informações do produto -->
                            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                                <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                                    <i class="fas fa-box text-blue-600 mr-2"></i>
                                    <h3 class="text-base font-medium text-gray-700">{{ __('messages.product_information') }}</h3>
                                </div>
                                <div class="p-4">
                                    @if($transferItem)
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0 bg-blue-100 p-2 rounded-full">
                                            <i class="fas fa-box text-blue-600 text-xl"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-lg font-medium text-gray-900">{{ $transferItem->product->name }}</h4>
                                            <div class="text-sm text-gray-500 mt-1">{{ __('messages.sku') }}: {{ $transferItem->product->sku }}</div>
                                            <div class="text-sm text-gray-500">{{ __('messages.current_stock') }}: {{ $transferItem->quantity }} @ {{ $transferItem->location->name }}</div>
                                        </div>
                                    </div>
                                    @else
                                    <div class="text-gray-500 text-center py-4">
                                        <i class="fas fa-box-open text-gray-400 text-4xl mb-2"></i>
                                        <p>{{ __('messages.no_product_selected') }}</p>
                                        <p class="text-sm text-gray-400 mt-1">{{ __('messages.please_select_product_first') }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Opções de transferência -->
                            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                                <div class="flex items-center bg-gradient-to-r from-indigo-50 to-indigo-100 px-4 py-3 border-b border-gray-200">
                                    <i class="fas fa-exchange-alt text-indigo-600 mr-2"></i>
                                    <h3 class="text-base font-medium text-gray-700">{{ __('messages.transfer_options') }}</h3>
                                </div>
                                <div class="p-4 space-y-4">
                                    @if($transferItem)
                                    <!-- Quantidade a transferir -->
                                    <div>
                                        <label for="transferQuantity" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.transfer_quantity') }}</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">{{ __('messages.units') }}</span>
                                            </div>
                                            <input type="number" wire:model="transferQuantity" id="transferQuantity" min="1" 
                                                class="pl-12 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 sm:text-sm bg-white"
                                                placeholder="{{ __('messages.enter_transfer_quantity') }}">
                                        </div>
                                        @error('transferQuantity') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Local de destino -->
                                    <div>
                                        <label for="transferDestinationId" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.destination_location') }}</label>
                                        <select wire:model="transferDestinationId" id="transferDestinationId" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 sm:text-sm bg-white">
                                            <option value="">{{ __('messages.select_destination') }}</option>
                                            @foreach($availableLocations as $location)
                                            <option value="{{ $location->id }}">{{ $location->name }} ({{ $location->location_code }})</option>
                                            @endforeach
                                        </select>
                                        @error('transferDestinationId') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Motivo da transferência -->
                                    <div>
                                        <label for="transferReason" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.transfer_reason') }}</label>
                                        <select wire:model="transferReason" id="transferReason" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 sm:text-sm bg-white">
                                            <option value="">{{ __('messages.select_reason') }}</option>
                                            <option value="inventory_reorganization">{{ __('messages.inventory_reorganization') }}</option>
                                            <option value="production">{{ __('messages.production') }}</option>
                                            <option value="customer_order">{{ __('messages.customer_order') }}</option>
                                            <option value="return">{{ __('messages.return') }}</option>
                                            <option value="other">{{ __('messages.other_reason') }}</option>
                                        </select>
                                        @error('transferReason') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Notas -->
                                    <div>
                                        <label for="transferNotes" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.notes') }}</label>
                                        <textarea wire:model="transferNotes" id="transferNotes" rows="3" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 sm:text-sm bg-white"
                                            placeholder="{{ __('messages.enter_transfer_notes') }}"></textarea>
                                    </div>
                                    @else
                                    <div class="text-gray-500 text-center py-4">
                                        <p class="text-sm">{{ __('messages.select_product_first') }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Rodapé com botões de ação -->
                        <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                            <button type="button" wire:click="closeTransferModal" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                <i class="fas fa-times mr-2"></i>
                                {{ __('messages.cancel') }}
                            </button>
                            <button type="submit" wire:loading.attr="disabled" class="inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="saveTransfer">
                                    <i class="fas fa-exchange-alt mr-2"></i>
                                    {{ __('messages.transfer_stock') }}
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
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Histórico de Transações -->
    <div>
        <div x-data="{ open: @entangle('showHistoryModal') }" 
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
                            <i class="fas fa-history mr-2"></i>
                            {{ __('messages.transaction_history') }}
                        </h3>
                        <button type="button" wire:click="closeHistoryModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <!-- Corpo da modal -->
                    <div class="p-6 space-y-6">
                        <!-- Informações do produto -->
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                            <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-box text-blue-600 mr-2"></i>
                                <h3 class="text-base font-medium text-gray-700">{{ __('messages.product_information') }}</h3>
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
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                            <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-history text-blue-600 mr-2"></i>
                                <h3 class="text-base font-medium text-gray-700">{{ __('messages.transaction_records') }}</h3>
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
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    {{ $transaction->transaction_type == 'purchase_receipt' ? 'bg-green-100 text-green-800' : 
                                                    ($transaction->transaction_type == 'transfer' ? 'bg-blue-100 text-blue-800' : 
                                                    ($transaction->transaction_type == 'adjustment' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) }}">
                                                    {{ __(str_replace('_', ' ', ucfirst($transaction->transaction_type))) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium {{ $transaction->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $transaction->quantity > 0 ? '+' : '' }}{{ $transaction->quantity }}
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
                                                <div class="text-sm text-gray-900">{{ $transaction->user->name }}</div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-4 text-center">
                                                <div class="flex flex-col items-center justify-center py-4 space-y-2">
                                                    <div class="flex-shrink-0 bg-gray-100 p-2 rounded-full">
                                                        <i class="fas fa-history text-gray-400 text-xl"></i>
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
    </div>
</div>
