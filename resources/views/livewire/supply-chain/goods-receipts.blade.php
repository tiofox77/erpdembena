<div>
    <div class="py-2 sm:py-4">
        <div class="max-w-full mx-auto px-2 sm:px-4 lg:px-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4">
                <div class="flex items-center space-x-4">
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2 sm:mb-0 flex items-center">
                        <i class="fas fa-dolly-flatbed mr-3 text-gray-700"></i> {{ __('messages.goods_receipts_management') }}
                    </h1>
                </div>
                <button
                    type="button"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-1.5 px-3 sm:py-2 sm:px-4 rounded flex items-center"
                    wire:click="openCreateModal"
                >
                    <i class="fas fa-plus-circle mr-2"></i> {{ __('messages.create_receipt') }}
                </button>
            </div>

            <!-- Filters and Table Section -->
            <div class="bg-white rounded-lg shadow mb-4 sm:mb-6">
                <div class="p-2 sm:p-4">
                    <!-- Enhanced Filter Section -->
                    <div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                        <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                            <i class="fas fa-filter mr-2 text-blue-500"></i> {{ __('messages.filters_and_search') }}
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label for="search" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-search mr-1 text-gray-500"></i> {{ __('messages.search') }}
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                    <input
                                        type="text"
                                        wire:model.debounce.300ms="search"
                                        id="search"
                                        placeholder="{{ __('messages.search_receipts') }}"
                                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                </div>
                            </div>
                            <div>
                                <label for="statusFilter" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-tag mr-1 text-gray-500"></i> {{ __('messages.status') }}
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="statusFilter"
                                        wire:model="statusFilter"
                                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                        <option value="">{{ __('messages.all_statuses') }}</option>
                                        <option value="pending">{{ __('messages.pending') }}</option>
                                        <option value="processing">{{ __('messages.processing') }}</option>
                                        <option value="completed">{{ __('messages.completed') }}</option>
                                        <option value="cancelled">{{ __('messages.cancelled') }}</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label for="supplierFilter" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-truck mr-1 text-gray-500"></i> {{ __('messages.supplier') }}
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="supplierFilter"
                                        wire:model="supplierFilter"
                                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                        <option value="">{{ __('messages.all_suppliers') }}</option>
                                        @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label for="locationFilter" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-warehouse mr-1 text-gray-500"></i> {{ __('messages.location') }}
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="locationFilter"
                                        wire:model="locationFilter"
                                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                        <option value="">{{ __('messages.all_locations') }}</option>
                                        @foreach($locations as $location)
                                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label for="perPage" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-list-ol mr-1 text-gray-500"></i> {{ __('messages.per_page') }}
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="perPage"
                                        wire:model="perPage"
                                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button
                                wire:click="resetFilters"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150 flex items-center"
                            >
                                <i class="fas fa-undo mr-2"></i>
                                <span wire:loading.remove wire:target="resetFilters">{{ __('messages.reset_filters') }}</span>
                                <span wire:loading wire:target="resetFilters">{{ __('messages.resetting') }}...</span>
                            </button>
                        </div>
                    </div>

                    <!-- Alert Messages -->
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
                                <i class="fas fa-exclamation-triangle text-white"></i>
                            </div>
                            <div class="px-4 py-2 -mx-3">
                                <div class="mx-3">
                                    <p class="text-sm text-red-600">{{ session('error') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Enhanced Table -->
                    <div class="overflow-x-auto bg-white rounded-lg shadow">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('receipt_number')">
                                    {{ __('messages.receipt_number') }}
                                    @if ($sortField === 'receipt_number')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('messages.purchase_order') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('messages.supplier') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('messages.location') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('status')">
                                    {{ __('messages.status') }}
                                    @if ($sortField === 'status')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('receipt_date')">
                                    {{ __('messages.receipt_date') }}
                                    @if ($sortField === 'receipt_date')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('messages.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($goodsReceipts as $receipt)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $receipt->receipt_number }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    @if($receipt->purchase_order)
                                        <a href="#" wire:click.prevent="viewPurchaseOrder({{ $receipt->purchase_order_id }})" class="text-blue-600 hover:underline">
                                            {{ $receipt->purchase_order->order_number }}
                                        </a>
                                    @else
                                        <span class="text-gray-500">{{ __('messages.direct_receipt') }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $receipt->supplier->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $receipt->location->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $receipt->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                      ($receipt->status == 'processing' ? 'bg-blue-100 text-blue-800' : 
                                      ($receipt->status == 'completed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')) }}">
                                    {{ __(ucfirst($receipt->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ date('d/m/Y', strtotime($receipt->receipt_date)) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="viewReceipt({{ $receipt->id }})" class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @if($receipt->status == 'pending' || $receipt->status == 'processing')
                                <button wire:click="editReceipt({{ $receipt->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endif
                                @if($receipt->status == 'pending')
                                <button wire:click="confirmDelete({{ $receipt->id }})" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                {{ __('messages.no_goods_receipts_found') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="px-4 py-3 bg-white border-t border-gray-200 sm:px-6">
                {{ $goodsReceipts->links() }}
            </div>
        </div>
    </div>

    <!-- View/Create/Edit Goods Receipt Modal -->
    <!-- Modal content will be added here -->

    <!-- Delete Confirmation Modal -->
    <!-- Modal content will be added here -->
</div>
