<div>
    <div class="container mx-auto px-4 py-6">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
            <h1 class="text-2xl font-semibold text-gray-800 flex items-center">
                <i class="fas fa-shopping-cart text-blue-600 mr-3"></i>
                {{ __('messages.purchase_orders_management') }}
            </h1>
            <button wire:click="openCreateModal" 
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 hover:shadow-lg">
                <i class="fas fa-plus-circle mr-2 animate-pulse"></i>
                {{ __('messages.create_order') }}
            </button>
        </div>

        <!-- Cartão de Busca e Filtros -->
        <div class="mb-6 bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 ease-in-out hover:shadow-lg transform hover:scale-[1.01]">
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
                                placeholder="{{ __('messages.search_orders') }}" 
                                type="search">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">{{ __('messages.search_by_order_number_or_supplier') }}</p>
                    </div>
                    
                    <!-- Linha de filtros -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Filtro de Status -->
                        <div>
                            <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-tag text-gray-500 mr-1"></i>
                                {{ __('messages.status') }}
                            </label>
                            <select wire:model="statusFilter" id="statusFilter" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="">{{ __('messages.all_statuses') }}</option>
                                <option value="draft">{{ __('messages.draft') }}</option>
                                <option value="pending_approval">{{ __('messages.pending_approval') }}</option>
                                <option value="approved">{{ __('messages.approved') }}</option>
                                <option value="ordered">{{ __('messages.ordered') }}</option>
                                <option value="partially_received">{{ __('messages.partially_received') }}</option>
                                <option value="completed">{{ __('messages.completed') }}</option>
                                <option value="cancelled">{{ __('messages.cancelled') }}</option>
                            </select>
                        </div>
                        
                        <!-- Filtro de Fornecedor -->
                        <div>
                            <label for="supplierFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-building text-gray-500 mr-1"></i>
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
                        
                        <!-- Registros por página -->
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
                    
                    <!-- Botões de ação -->
                    <div class="flex justify-end space-x-3">
                        <button wire:click="generateListPdf" 
                            class="inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                            <i class="fas fa-file-pdf mr-2"></i>
                            {{ __('messages.generate_list_pdf') }}
                        </button>
                        
                        <button wire:click="resetFilters" 
                            class="inline-flex justify-center items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-gray-700 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                            <i class="fas fa-redo-alt mr-2"></i>
                            {{ __('messages.reset_filters') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alertas -->
        @if (session()->has('message'))
            <div class="mb-4 flex w-full overflow-hidden bg-white rounded-lg shadow-md">
                <div class="flex items-center justify-center w-12 bg-green-500">
                    <i class="fas fa-check text-white"></i>
                </div>
                <div class="px-4 py-2 -mx-3">
                    <div class="mx-3">
                        <p class="text-sm text-gray-600">{{ session('message') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-4 flex w-full overflow-hidden bg-white rounded-lg shadow-md">
                <div class="flex items-center justify-center w-12 bg-red-500">
                    <i class="fas fa-exclamation-circle text-white"></i>
                </div>
                <div class="px-4 py-2 -mx-3">
                    <div class="mx-3">
                        <p class="text-sm text-gray-600">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Tabela de Ordens de Compra -->
        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden transition-all duration-300 ease-in-out hover:shadow-lg">
            <!-- Cabeçalho da Tabela -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
                <h2 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-shopping-cart mr-2"></i>
                    {{ __('messages.purchase_orders_list') }}
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('order_number')">
                                    {{ __('messages.order_number') }}
                                    @if($sortField === 'order_number')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 opacity-50"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('messages.supplier') }}
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
                                {{ __('messages.delivery_date') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('messages.total') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('messages.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($purchaseOrders as $order)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $order->order_number }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $order->supplier->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($order->status)
                                        <div class="flex items-center">
                                            <span wire:click="openShippingNotes({{ $order->id }})" 
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer hover:shadow-md transition-all duration-200 ease-in-out transform hover:scale-105 
                                                @php
                                                    // Verificar se o status é um dos valores padrão do sistema
                                                    $standardStatuses = [
                                                        'draft', 'pending_approval', 'approved', 'ordered', 
                                                        'partially_received', 'completed', 'cancelled',
                                                        'order_placed', 'proforma_invoice_received', 'payment_completed',
                                                        'du_in_process', 'goods_acquired', 'shipped_to_port',
                                                        'shipping_line_booking_confirmed', 'container_loaded',
                                                        'on_board', 'arrived_at_port', 'customs_clearance', 'delivered'
                                                    ];
                                                    
                                                    $isStandardStatus = in_array($order->status, $standardStatuses);
                                                @endphp
                                                
                                                @if(!$isStandardStatus)
                                                    bg-blue-100 text-blue-800 border border-blue-200
                                                @elseif($order->status == 'draft') bg-gray-100 text-gray-800
                                                @elseif($order->status == 'pending_approval') bg-yellow-100 text-yellow-800
                                                @elseif($order->status == 'approved') bg-blue-100 text-blue-800
                                                @elseif($order->status == 'ordered') bg-indigo-100 text-indigo-800
                                                @elseif($order->status == 'partially_received') bg-purple-100 text-purple-800
                                                @elseif($order->status == 'completed') bg-green-100 text-green-800
                                                @elseif($order->status == 'cancelled') bg-red-100 text-red-800
                                                @elseif($order->status == 'order_placed') bg-gray-100 text-gray-800
                                                @elseif($order->status == 'proforma_invoice_received') bg-blue-100 text-blue-800
                                                @elseif($order->status == 'payment_completed') bg-indigo-100 text-indigo-800
                                                @elseif($order->status == 'du_in_process') bg-purple-100 text-purple-800
                                                @elseif($order->status == 'goods_acquired') bg-green-100 text-green-800
                                                @elseif($order->status == 'shipped_to_port') bg-emerald-100 text-emerald-800
                                                @elseif($order->status == 'shipping_line_booking_confirmed') bg-yellow-100 text-yellow-800
                                                @elseif($order->status == 'container_loaded') bg-orange-100 text-orange-800
                                                @elseif($order->status == 'on_board') bg-red-100 text-red-800
                                                @elseif($order->status == 'arrived_at_port') bg-pink-100 text-pink-800
                                                @elseif($order->status == 'customs_clearance') bg-teal-100 text-teal-800
                                                @elseif($order->status == 'delivered') bg-cyan-100 text-cyan-800
                                                @endif">
                                                
                                                @if(!$isStandardStatus)
                                                    <!-- Ícone para formulários personalizados -->
                                                    <i class="fas fa-clipboard-list mr-1 text-xs"></i>
                                                    {{ $order->status }}
                                                @else
                                                    <!-- Ícones para status padrão -->
                                                    <i class="fas 
                                                    @if($order->status == 'draft') fa-pencil-alt
                                                    @elseif($order->status == 'pending_approval') fa-clock
                                                    @elseif($order->status == 'approved') fa-check
                                                    @elseif($order->status == 'ordered') fa-shopping-cart
                                                    @elseif($order->status == 'partially_received') fa-truck
                                                    @elseif($order->status == 'completed') fa-check-circle
                                                    @elseif($order->status == 'cancelled') fa-times-circle
                                                    @elseif($order->status == 'order_placed') fa-shopping-cart 
                                                    @elseif($order->status == 'proforma_invoice_received') fa-file-invoice-dollar
                                                    @elseif($order->status == 'payment_completed') fa-money-bill-wave
                                                    @elseif($order->status == 'du_in_process') fa-file-alt
                                                    @elseif($order->status == 'goods_acquired') fa-boxes
                                                    @elseif($order->status == 'shipped_to_port') fa-dolly
                                                    @elseif($order->status == 'shipping_line_booking_confirmed') fa-calendar-check
                                                    @elseif($order->status == 'container_loaded') fa-box
                                                    @elseif($order->status == 'on_board') fa-ship 
                                                    @elseif($order->status == 'arrived_at_port') fa-anchor
                                                    @elseif($order->status == 'customs_clearance') fa-clipboard-check
                                                    @elseif($order->status == 'delivered') fa-check-circle
                                                    @endif mr-1 text-xs"></i>
                                                    
                                                    @php
                                                        // Verificar se existe uma tradução para este status
                                                        $translationKey = 'messages.shipping_status_'.$order->status;
                                                        $translationExists = \Illuminate\Support\Facades\Lang::has($translationKey);
                                                    @endphp
                                                    
                                                    @if($translationExists)
                                                        {{ __($translationKey) }}
                                                    @else
                                                        {{ __("messages.status_{$order->status}") }}
                                                    @endif
                                                @endif
                                            </span>
                                            @if($order->shipping_status_date)
                                                <span class="text-xs text-gray-500 ml-2">
                                                    {{ $order->shipping_status_date->format('d/m/Y') }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span wire:click="openShippingNotes({{ $order->id }})" 
                                              class="cursor-pointer text-blue-600 hover:text-blue-800 transition-colors duration-200 ease-in-out text-sm">
                                            <i class="fas fa-plus-circle mr-1"></i>
                                            {{ __('messages.add_shipping_status') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="block text-sm font-medium 
                                        {{ $order->is_overdue ? 'text-red-600' : 
                                          (strtotime($order->expected_delivery_date) <= strtotime('+15 days') && 
                                           strtotime($order->expected_delivery_date) >= strtotime('now') ? 
                                           'text-amber-600' : 'text-gray-900') }}">
                                        {{ $order->expected_delivery_date ? date('d/m/Y', strtotime($order->expected_delivery_date)) : '-' }}
                                        @if($order->is_overdue)
                                            <i class="fas fa-exclamation-circle text-red-500 ml-1 animate-pulse" 
                                               title="{{ __('messages.overdue_order') }}"></i>
                                        @elseif(strtotime($order->expected_delivery_date) <= strtotime('+15 days') && 
                                               strtotime($order->expected_delivery_date) >= strtotime('now'))
                                            <i class="fas fa-exclamation-triangle text-amber-500 ml-1" 
                                               title="{{ __('messages.delivery_approaching') }}" 
                                               x-data="{}" 
                                               x-tooltip.raw="{{ __('messages.delivery_within_15_days') }}"></i>
                                        @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium {{ $order->total_amount > 1000 ? 'text-green-600' : 'text-gray-900' }}">
                                        {{ number_format($order->total_amount, 2) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <button wire:click="viewOrder({{ $order->id }})" 
                                            class="text-blue-600 hover:text-blue-900 transition-colors duration-150 transform hover:scale-110"
                                            title="{{ __('messages.view_order') }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <button wire:click="editOrder({{ $order->id }})" 
                                            class="text-indigo-600 hover:text-indigo-900 transition-colors duration-150 transform hover:scale-110"
                                            title="{{ __('messages.edit_order') }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <button wire:click="openShippingNotes({{ $order->id }})"
                                            class="text-amber-600 hover:text-amber-800 transition-colors duration-150 transform hover:scale-110"
                                            title="{{ __('messages.shipping_notes') }}">
                                            <i class="fas fa-shipping-fast"></i>
                                        </button>
                                        
                                        <button wire:click="generatePdf({{ $order->id }})" 
                                            class="text-green-600 hover:text-green-900 transition-colors duration-150 transform hover:scale-110"
                                            title="{{ __('messages.generate_pdf') }}">
                                            <i class="fas fa-file-pdf"></i>
                                        </button>
                                        
                                        <button wire:click="confirmDeleteOrder({{ $order->id }})" 
                                            class="text-red-600 hover:text-red-900 transition-colors duration-150 transform hover:scale-110"
                                            title="{{ __('messages.delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col items-center justify-center py-6 space-y-2">
                                        <div class="flex-shrink-0 bg-gray-100 p-3 rounded-full">
                                            <i class="fas fa-shopping-cart text-gray-400 text-2xl"></i>
                                        </div>
                                        <p class="text-gray-500 text-sm">{{ __('messages.no_purchase_orders_found') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <div class="px-4 py-3 bg-white border-t border-gray-200">
                {{ $purchaseOrders->links() }}
            </div>
        </div>
    </div>

    <!-- Modals -->
    @include('livewire.supply-chain.purchase-orders-modals')
    @include('livewire.supply-chain.purchase-orders-modal-view')
    @include('livewire.supply-chain.purchase-orders-modal-shipping')
</div>
