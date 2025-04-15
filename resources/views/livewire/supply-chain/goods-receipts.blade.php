<div>
    <div class="container mx-auto px-4 py-6">
        <!-- Cabeçalho Principal -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
            <h1 class="text-2xl font-semibold text-gray-800 flex items-center">
                <i class="fas fa-dolly-flatbed text-blue-600 mr-3"></i>
                {{ __('messages.goods_receipts_management') }}
            </h1>
            <button wire:click="openCreateModal" 
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 hover:shadow-lg">
                <i class="fas fa-plus-circle mr-2 animate-pulse"></i>
                {{ __('messages.create_receipt') }}
            </button>
        </div>

        <!-- Cartão de Busca e Filtros -->
        <div class="mb-6 bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 ease-in-out hover:shadow-lg transform hover:scale-[1.01]"
            x-data="{}" 
            x-init="setTimeout(() => { $el.classList.add('animate-zoom-in') }, 100)"
            @class="['animate-zoom-in']">
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
                                placeholder="{{ __('messages.search_receipts') }}" 
                                type="search">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">{{ __('messages.search_by_receipt_number_or_supplier') }}</p>
                    </div>
                    
                    <!-- Linha de filtros -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Status Filter -->
                        <div>
                            <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-tag text-gray-500 mr-1"></i>
                                {{ __('messages.status') }}
                            </label>
                            <select wire:model="statusFilter" id="statusFilter" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="">{{ __('messages.all_statuses') }}</option>
                                <option value="pending">{{ __('messages.pending') }}</option>
                                <option value="processing">{{ __('messages.processing') }}</option>
                                <option value="completed">{{ __('messages.completed') }}</option>
                                <option value="cancelled">{{ __('messages.cancelled') }}</option>
                            </select>
                        </div>
                        
                        <!-- Supplier Filter -->
                        <div>
                            <label for="supplierFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-truck text-gray-500 mr-1"></i>
                                {{ __('messages.supplier') }}
                            </label>
                            <select wire:model="supplierFilter" id="supplierFilter" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="">{{ __('messages.all_suppliers') }}</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Location Filter -->
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
                        
                        <!-- Items per page -->
                        <div>
                            <label for="perPage" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-list-ol text-gray-500 mr-1"></i>
                                {{ __('messages.items_per_page') }}
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
                    
                    <!-- Botão de reset -->
                    <div class="flex justify-end">
                        <button wire:click="resetFilters" 
                            class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md text-sm font-medium text-gray-700 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                            <i class="fas fa-redo-alt mr-2"></i>
                            {{ __('messages.reset_filters') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela de Dados -->
        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden transition-all duration-300 ease-in-out hover:shadow-lg transform hover:scale-[1.01]"
            x-data="{}" 
            x-init="setTimeout(() => { $el.classList.add('animate-zoom-in') }, 200)"
            @class="['animate-zoom-in']">
            <!-- Cabeçalho da Tabela -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
                <h2 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-clipboard-list mr-2"></i>
                    {{ __('messages.goods_receipts_list') }}
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('receipt_number')">
                                    {{ __('messages.receipt_number') }}
                                    @if($sortField === 'receipt_number')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 opacity-50"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('messages.purchase_order') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('supplier_id')">
                                    {{ __('messages.supplier') }}
                                    @if($sortField === 'supplier_id')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 opacity-50"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('location_id')">
                                    {{ __('messages.location') }}
                                    @if($sortField === 'location_id')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 opacity-50"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('status')">
                                    {{ __('messages.status') }}
                                    @if($sortField === 'status')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 opacity-50"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('receipt_date')">
                                    {{ __('messages.receipt_date') }}
                                    @if($sortField === 'receipt_date')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 opacity-50"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('messages.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($goodsReceipts as $receipt)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $receipt->receipt_number }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    @if($receipt->purchase_order)
                                        <a href="#" wire:click.prevent="viewPurchaseOrder({{ $receipt->purchase_order_id }})" class="text-blue-600 hover:text-blue-900 hover:underline flex items-center">
                                            <i class="fas fa-file-invoice mr-1"></i> {{ $receipt->purchase_order->order_number }}
                                        </a>
                                    @else
                                        <span class="text-gray-500 flex items-center"><i class="fas fa-box mr-1"></i> {{ __('messages.direct_receipt') }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 flex items-center">
                                    <i class="fas fa-truck text-gray-500 mr-1"></i> {{ $receipt->supplier->name }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 flex items-center">
                                    <i class="fas fa-warehouse text-gray-500 mr-1"></i> {{ $receipt->location->name }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $receipt->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                    ($receipt->status == 'processing' ? 'bg-blue-100 text-blue-800' : 
                                    ($receipt->status == 'completed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')) }}">
                                    <i class="fas 
                                        {{ $receipt->status == 'pending' ? 'fa-clock' : 
                                        ($receipt->status == 'processing' ? 'fa-spinner fa-spin' : 
                                        ($receipt->status == 'completed' ? 'fa-check-circle' : 'fa-times-circle')) }} mr-1.5"></i>
                                    {{ __('messages.status_'.$receipt->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 flex items-center">
                                    <i class="fas fa-calendar-alt text-gray-500 mr-1"></i> {{ date('d/m/Y', strtotime($receipt->receipt_date)) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <button wire:click="viewReceipt({{ $receipt->id }})" 
                                        class="text-blue-600 hover:text-blue-900 transition-colors duration-150 transform hover:scale-110" title="{{ __('messages.view') }}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    @if($receipt->status == 'pending' || $receipt->status == 'processing')
                                    <button wire:click="editReceipt({{ $receipt->id }})" 
                                        class="text-indigo-600 hover:text-indigo-900 transition-colors duration-150 transform hover:scale-110" title="{{ __('messages.edit') }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @endif
                                    
                                    @if($receipt->status == 'pending')
                                    <button wire:click="confirmDelete({{ $receipt->id }})" 
                                        class="text-red-600 hover:text-red-900 transition-colors duration-150 transform hover:scale-110" title="{{ __('messages.delete') }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col items-center justify-center py-6 space-y-2">
                                    <div class="flex-shrink-0 bg-gray-100 p-3 rounded-full">
                                        <i class="fas fa-dolly-flatbed text-gray-400 text-2xl"></i>
                                    </div>
                                    <p class="text-gray-500 text-sm">{{ __('messages.no_goods_receipts_found') }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <div class="px-4 py-3 bg-white border-t border-gray-200">
                {{ $goodsReceipts->links() }}
            </div>
        </div>
    </div>

    <!-- Modais (serão mantidos em um arquivo separado) -->
    @include('livewire.supply-chain.goods-receipts-modals')

    <!-- Notificações Toast -->
    <div x-data="{ show: false, message: '', type: '' }"
         x-on:toast.window="show = true; message = $event.detail.message; type = $event.detail.type; setTimeout(() => { show = false }, 3000)"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-90"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-90"
         class="fixed top-4 right-4 z-50"
         x-cloak>
        <div x-show="type === 'success'" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-md flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-500"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm" x-text="message"></p>
            </div>
        </div>
        <div x-show="type === 'error'" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-md flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-circle text-red-500"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm" x-text="message"></p>
            </div>
        </div>
    </div>
</div>
