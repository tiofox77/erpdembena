<div>
    <!-- Cabeçalho da Página -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 inline-flex items-center">
                <i class="fas fa-boxes text-blue-600 mr-2"></i>
                {{ __('messages.inventory_levels') }}
            </h1>
            <p class="mt-1 text-sm text-gray-500">{{ __('messages.inventory_levels_description') }}</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-2">
            <button type="button" wire:click="create" 
                class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                <i class="fas fa-plus mr-2"></i>
                {{ __('messages.add_inventory_level') }}
            </button>
        </div>
    </div>
    
    <!-- Cartão de Filtros -->
    <div class="bg-white rounded-lg shadow-md mb-6 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-4 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center">
                <i class="fas fa-filter mr-2"></i>
                {{ __('messages.filter_inventory') }}
            </h2>
        </div>
        
        <div class="p-5">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Campo de Busca -->
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('messages.search') }}
                    </label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" wire:model.live.debounce.300ms="search" id="search" 
                            class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md"
                            placeholder="{{ __('messages.search_inventory_placeholder') }}">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            @if($search)
                                <button wire:click="$set('search', '')" class="text-gray-400 hover:text-gray-500">
                                    <i class="fas fa-times"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">{{ __('messages.search_inventory_help') }}</p>
                </div>
                
                <!-- Filtro por Status -->
                <div>
                    <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('messages.filter_by_status') }}
                    </label>
                    <select id="statusFilter" wire:model.live="statusFilter" 
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="">{{ __('messages.all_statuses') }}</option>
                        <option value="normal">{{ __('messages.normal_stock') }}</option>
                        <option value="low">{{ __('messages.low_stock') }}</option>
                        <option value="overstock">{{ __('messages.overstock') }}</option>
                        <option value="critical">{{ __('messages.critical_stock') }}</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabela de Níveis de Estoque -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-4 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-white flex items-center">
                <i class="fas fa-layer-group mr-2"></i>
                {{ __('messages.inventory_levels_list') }}
            </h2>
            <div class="flex items-center space-x-2">
                <select wire:model.live="perPage" class="border-0 bg-blue-600 text-white text-sm rounded-md focus:outline-none focus:ring-0">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th wire:click="sortBy('product_id')" class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors duration-200">
                            <div class="flex items-center">
                                {{ __('messages.product') }}
                                @if($sortField === 'product_id')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-2 text-blue-500"></i>
                                @else
                                    <i class="fas fa-sort ml-2 text-gray-400"></i>
                                @endif
                            </div>
                        </th>
                        <th wire:click="sortBy('safety_stock')" class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors duration-200">
                            <div class="flex items-center justify-end">
                                {{ __('messages.minimum_stock') }}
                                <i class="fas fa-shield-alt ml-1 text-blue-500 cursor-help" title="{{ __('messages.safety_level_info') }}"></i>
                                @if($sortField === 'safety_stock')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-2 text-blue-500"></i>
                                @else
                                    <i class="fas fa-sort ml-2 text-gray-400"></i>
                                @endif
                            </div>
                        </th>
                        <th wire:click="sortBy('maximum_stock')" class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors duration-200">
                            <div class="flex items-center justify-end">
                                {{ __('messages.maximum_stock') }}
                                <i class="fas fa-warehouse ml-1 text-blue-500 cursor-help" title="{{ __('messages.maximum_stock_info') }}"></i>
                                @if($sortField === 'maximum_stock')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-2 text-blue-500"></i>
                                @else
                                    <i class="fas fa-sort ml-2 text-gray-400"></i>
                                @endif
                            </div>
                        </th>
                        <th wire:click="sortBy('reorder_point')" class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors duration-200">
                            <div class="flex items-center justify-end">
                                {{ __('messages.reorder_point') }}
                                <i class="fas fa-sync ml-1 text-blue-500 cursor-help" title="{{ __('messages.reorder_point_info') }}"></i>
                                @if($sortField === 'reorder_point')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-2 text-blue-500"></i>
                                @else
                                    <i class="fas fa-sort ml-2 text-gray-400"></i>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('messages.status') }}
                            <i class="fas fa-info-circle ml-1 text-blue-500 cursor-help" title="{{ __('messages.stock_status_info') }}"></i>
                        </th>
                        <th wire:click="sortBy('current_stock')" class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors duration-200">
                            <div class="flex items-center justify-end">
                                {{ __('messages.current_stock') }}
                                <i class="fas fa-info-circle ml-1 text-blue-500 cursor-help" title="{{ __('messages.stock_from_supply_chain') }}"></i>
                                @if($sortField === 'current_stock')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-2"></i>
                                @else
                                    <i class="fas fa-sort ml-2 text-gray-400"></i>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('messages.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($inventoryLevels as $level)
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <!-- PRODUCT -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-box text-gray-500"></i>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $level->product->name }}
                                        </div>
                                        <div class="flex items-center text-xs text-gray-500 mt-1">
                                            <span class="mr-2"><i class="fas fa-barcode mr-1"></i>{{ $level->product->sku }}</span>
                                            @if($level->product->category)
                                                <span><i class="fas fa-tag mr-1"></i>{{ $level->product->category->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <!-- SAFETY STOCK -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                <div class="flex flex-col items-end">
                                    <span class="font-medium text-gray-800">
                                        {{ number_format($level->safety_stock, 2) }} {{ $level->uom }}
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        <i class="fas fa-shield-alt mr-1"></i> {{ __('messages.safety_level') }}
                                    </span>
                                    @if($level->current_stock <= $level->safety_stock)
                                        <span class="text-xs text-red-600 mt-1 flex items-center">
                                            <i class="fas fa-exclamation-circle mr-1 animate-pulse"></i>
                                            {{ __('messages.below_safety_stock') }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <!-- MAXIMUM STOCK -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                <div class="flex flex-col items-end">
                                    <span class="font-medium text-gray-800">
                                        {{ $level->maximum_stock ? number_format($level->maximum_stock, 2) . ' ' . $level->uom : '-' }}
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        <i class="fas fa-warehouse mr-1"></i> {{ __('messages.maximum_stock') }}
                                    </span>
                                    @if($level->maximum_stock > 0 && $level->current_stock > $level->maximum_stock)
                                        <span class="text-xs text-blue-600 mt-1 flex items-center">
                                            <i class="fas fa-arrow-up mr-1"></i>
                                            {{ __('messages.above_maximum') }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <!-- REORDER POINT -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                <div class="flex flex-col items-end">
                                    <span class="font-medium text-gray-800">
                                        {{ number_format($level->reorder_point, 2) }} {{ $level->uom }}
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        <i class="fas fa-sync mr-1"></i> {{ __('messages.reorder_point') }}
                                    </span>
                                    @if($level->current_stock <= $level->reorder_point && $level->current_stock > $level->safety_stock)
                                        <span class="text-xs text-amber-600 mt-1 flex items-center">
                                            <i class="fas fa-shopping-cart mr-1"></i>
                                            {{ __('messages.reorder_needed') }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <!-- STATUS -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                    {{ $level->getStockStatus() === 'normal' ? 'bg-green-100 text-green-800' : 
                                       ($level->getStockStatus() === 'low' ? 'bg-amber-100 text-amber-800' : 
                                       ($level->getStockStatus() === 'overstock' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800')) }}">
                                    <i class="fas 
                                        {{ $level->getStockStatus() === 'normal' ? 'fa-check-circle' : 
                                           ($level->getStockStatus() === 'low' ? 'fa-exclamation-triangle' : 
                                           ($level->getStockStatus() === 'overstock' ? 'fa-arrow-up' : 'fa-times-circle')) }} mr-1 
                                        {{ $level->getStockStatus() === 'critical' ? 'animate-pulse' : '' }}"></i>
                                    {{ __('messages.' . $level->getStockStatus() . '_stock') }}
                                </span>
                                <div class="mt-2 text-xs text-gray-500 flex items-center justify-center">
                                    <i class="fas fa-link mr-1"></i>
                                    <span>{{ __('messages.from') }} <span class="font-medium text-blue-600">{{ __('messages.supply_chain') }}</span></span>
                                </div>
                            </td>
                            <!-- CURRENT STOCK -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                <div class="flex flex-col items-end">
                                    <span class="font-medium {{ $level->current_stock <= $level->safety_stock ? 'text-red-600' : ($level->current_stock <= $level->reorder_point ? 'text-amber-600' : 'text-gray-800') }}">
                                        {{ number_format($level->current_stock, 2) }} {{ $level->uom }}
                                    </span>
                                    <span class="text-xs text-gray-500 flex items-center">
                                        <i class="fas fa-box-open mr-1"></i> {{ __('messages.available') }}: {{ number_format($level->available_stock, 2) }}
                                    </span>
                                    <div class="mt-1 w-24 bg-gray-200 rounded-full h-2 overflow-hidden">
                                        @php
                                            $maxValue = max($level->maximum_stock, $level->current_stock);
                                            $percentage = $maxValue > 0 ? ($level->current_stock / $maxValue) * 100 : 0;
                                            $barColor = $level->current_stock <= $level->safety_stock ? 'bg-red-600' : 
                                                       ($level->current_stock <= $level->reorder_point ? 'bg-amber-500' : 
                                                       ($level->current_stock > $level->maximum_stock && $level->maximum_stock > 0 ? 'bg-blue-600' : 'bg-green-600'));
                                        @endphp
                                        <div class="{{ $barColor }} h-2" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <!-- ACTIONS -->
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <button wire:click="view({{ $level->id }})" class="text-blue-600 hover:text-blue-900 mr-3 transition-colors duration-200" title="{{ __('messages.view_details') }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button wire:click="edit({{ $level->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3 transition-colors duration-200" title="{{ __('messages.edit') }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button wire:click="confirmDelete({{ $level->id }})" class="text-red-600 hover:text-red-900 transition-colors duration-200" title="{{ __('messages.delete') }}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center py-6">
                                    <i class="fas fa-box-open text-4xl text-gray-300 mb-2"></i>
                                    <p>{{ __('messages.no_inventory_levels_found') }}</p>
                                    <button wire:click="create" class="mt-3 inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                        <i class="fas fa-plus mr-1"></i>
                                        {{ __('messages.add_inventory_level') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Paginação -->
        <div class="bg-gray-50 px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="flex-1 flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-700">
                        {{ __('messages.showing') }} 
                        <span class="font-medium">{{ $inventoryLevels->firstItem() ?: 0 }}</span> 
                        {{ __('messages.to') }} 
                        <span class="font-medium">{{ $inventoryLevels->lastItem() ?: 0 }}</span> 
                        {{ __('messages.of') }} 
                        <span class="font-medium">{{ $inventoryLevels->total() }}</span> 
                        {{ __('messages.results') }}
                    </p>
                </div>
                <div>
                    {{ $inventoryLevels->links() }}
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal para Criar/Editar -->
    @include('livewire.mrp.inventory-levels.create-edit-modal')

    <!-- Modal para Excluir -->
    @include('livewire.mrp.inventory-levels.delete-modal')

    <!-- Modal para Visualizar -->
    @include('livewire.mrp.inventory-levels.view-modal')
</div>
