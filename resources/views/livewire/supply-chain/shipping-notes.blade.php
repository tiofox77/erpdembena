<div>
    <div class="container mx-auto px-4 py-6">
        <!-- Cabeçalho Principal -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
            <h1 class="text-2xl font-semibold text-gray-800 flex items-center">
                <i class="fas fa-shipping-fast text-blue-600 mr-3"></i>
                {{ __('messages.shipping_notes') }}
            </h1>
            <div class="flex space-x-3">
                <a href="{{ route('supply-chain.custom-forms') }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 ease-in-out transform hover:scale-105 hover:shadow-lg">
                    <i class="fas fa-file-alt mr-2"></i>
                    {{ __('messages.custom_forms') }}
                </a>
                <button wire:click="openAddModal" 
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 hover:shadow-lg">
                    <i class="fas fa-plus-circle mr-2 animate-pulse"></i>
                    {{ __('messages.add_shipping_note') }}
                </button>
            </div>
        </div>

        <!-- Cartão de Busca e Filtros -->
        <div class="mb-6 bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden transition-all duration-300 ease-in-out hover:shadow-lg">
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
                                placeholder="{{ __('messages.search_shipping_notes_placeholder') }}" 
                                type="search">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">{{ __('messages.search_shipping_notes_help') }}</p>
                    </div>
                    
                    <!-- Linha de filtros -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Filtro de Status -->
                        <div>
                            <label for="status_filter" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-tag text-gray-500 mr-1"></i>
                                {{ __('messages.status') }}
                            </label>
                            <select wire:model="statusFilter" id="status_filter" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="">{{ __('messages.all_statuses') }}</option>
                                <option value="preparing">{{ __('messages.shipping_status_preparing') }}</option>
                                <option value="ready_for_pickup">{{ __('messages.shipping_status_ready_for_pickup') }}</option>
                                <option value="shipped">{{ __('messages.shipping_status_shipped') }}</option>
                                <option value="in_transit">{{ __('messages.shipping_status_in_transit') }}</option>
                                <option value="customs">{{ __('messages.shipping_status_customs') }}</option>
                                <option value="delivery">{{ __('messages.shipping_status_delivery') }}</option>
                                <option value="delivered">{{ __('messages.shipping_status_delivered') }}</option>
                                <option value="returned">{{ __('messages.shipping_status_returned') }}</option>
                                <option value="issue">{{ __('messages.shipping_status_issue') }}</option>
                            </select>
                        </div>
                        
                        <!-- Filtro de Pedido -->
                        <div>
                            <label for="order_filter" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-shopping-cart text-gray-500 mr-1"></i>
                                {{ __('messages.purchase_order') }}
                            </label>
                            <select wire:model="orderFilter" id="order_filter" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="">{{ __('messages.all_orders') }}</option>
                                @foreach($purchaseOrders ?? [] as $order)
                                    <option value="{{ $order->id }}">{{ $order->order_number }}</option>
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

        <!-- Alertas (se necessário) -->
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

        <!-- Tabela de Dados -->
        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden transition-all duration-300 ease-in-out hover:shadow-lg">
            <!-- Cabeçalho da Tabela -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
                <h2 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-clipboard-list mr-2"></i>
                    {{ __('messages.shipping_notes_list') }}
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('created_at')">
                                    {{ __('messages.date') }}
                                    @if($sortField === 'created_at')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 opacity-50"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('purchase_order_id')">
                                    {{ __('messages.order') }}
                                    @if($sortField === 'purchase_order_id')
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
                                {{ __('messages.note') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('messages.created_by') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('messages.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($shippingNotes ?? [] as $note)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $note->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($note->purchaseOrder)
                                        <a href="#" wire:click.prevent="viewOrder({{ $note->purchase_order_id }})" class="text-blue-600 hover:text-blue-900">
                                            {{ $note->purchaseOrder->order_number ?? __('messages.order') . ' #' . $note->purchase_order_id }}
                                        </a>
                                    @else
                                        {{ __('messages.order') . ' #' . $note->purchase_order_id }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($note->status == 'preparing') bg-gray-100 text-gray-800
                                        @elseif($note->status == 'ready_for_pickup') bg-blue-100 text-blue-800
                                        @elseif($note->status == 'shipped') bg-indigo-100 text-indigo-800
                                        @elseif($note->status == 'in_transit') bg-purple-100 text-purple-800
                                        @elseif($note->status == 'customs') bg-yellow-100 text-yellow-800
                                        @elseif($note->status == 'delivery') bg-green-100 text-green-800
                                        @elseif($note->status == 'delivered') bg-emerald-100 text-emerald-800
                                        @elseif($note->status == 'returned') bg-red-100 text-red-800
                                        @elseif($note->status == 'issue') bg-orange-100 text-orange-800
                                        @elseif($note->status == 'custom_form') bg-blue-100 text-blue-800
                                        @endif border border-opacity-50
                                        @if($note->status == 'preparing') border-gray-300
                                        @elseif($note->status == 'ready_for_pickup') border-blue-300
                                        @elseif($note->status == 'shipped') border-indigo-300
                                        @elseif($note->status == 'in_transit') border-purple-300
                                        @elseif($note->status == 'customs') border-yellow-300
                                        @elseif($note->status == 'delivery') border-green-300
                                        @elseif($note->status == 'delivered') border-emerald-300
                                        @elseif($note->status == 'returned') border-red-300
                                        @elseif($note->status == 'issue') border-orange-300
                                        @elseif($note->status == 'custom_form') border-blue-300
                                        @endif">
                                        <i class="
                                            @if($note->status == 'preparing') fas fa-cog
                                            @elseif($note->status == 'ready_for_pickup') fas fa-box
                                            @elseif($note->status == 'shipped') fas fa-truck-loading
                                            @elseif($note->status == 'in_transit') fas fa-shipping-fast
                                            @elseif($note->status == 'customs') fas fa-passport
                                            @elseif($note->status == 'delivery') fas fa-truck-moving
                                            @elseif($note->status == 'delivered') fas fa-check-circle
                                            @elseif($note->status == 'returned') fas fa-undo-alt
                                            @elseif($note->status == 'issue') fas fa-exclamation-circle
                                            @elseif($note->status == 'custom_form') fas fa-clipboard-list
                                            @endif mr-1 text-xs"></i>
                                        @if($note->status == 'custom_form' && $note->customForm)
                                            {{ $note->customForm->name }}
                                        @else
                                            {{ __('messages.shipping_status_'.$note->status) }}
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                    {{ $note->note }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ optional(\App\Models\User::find($note->created_by))->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <button wire:click="viewNote({{ $note->id }})" 
                                            class="text-blue-600 hover:text-blue-900 transition-colors duration-150 transform hover:scale-110"
                                            title="{{ __('messages.view_note') }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button wire:click="editNote({{ $note->id }})" 
                                            class="text-indigo-600 hover:text-indigo-900 transition-colors duration-150 transform hover:scale-110"
                                            title="{{ __('messages.edit_note') }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <!-- Botão para abrir formulário personalizado -->
                                        <button wire:click="openCustomForm({{ $note->id }})" 
                                            class="text-green-600 hover:text-green-900 transition-colors duration-150 transform hover:scale-110"
                                            title="{{ __('messages.fill_custom_form') }}">
                                            <i class="fas fa-file-alt"></i>
                                        </button>
                                        <!-- Botão para visualizar submissões de formulários -->
                                        <button wire:click="viewFormSubmissions({{ $note->id }})" 
                                            class="text-purple-600 hover:text-purple-900 transition-colors duration-150 transform hover:scale-110"
                                            title="{{ __('messages.view_form_submissions') }}">
                                            <i class="fas fa-clipboard-check"></i>
                                        </button>
                                        <button wire:click="confirmDeleteNote({{ $note->id }})" 
                                            class="text-red-600 hover:text-red-900 transition-colors duration-150 transform hover:scale-110"
                                            title="{{ __('messages.delete_note') }}">
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
                                            <i class="fas fa-shipping-fast text-gray-400 text-2xl"></i>
                                        </div>
                                        <p class="text-gray-500 text-sm">{{ __('messages.no_shipping_notes_found') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <div class="px-4 py-3 bg-white border-t border-gray-200">
                @if(isset($shippingNotes) && method_exists($shippingNotes, 'links'))
                    {{ $shippingNotes->links() }}
                @endif
            </div>
        </div>
    </div>

    <!-- Modais -->
    @include('livewire.supply-chain.shipping-notes-modals')
    @include('livewire.supply-chain.shipping-notes-view-modal')
</div>
