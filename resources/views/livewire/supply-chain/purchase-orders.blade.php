<div>
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold">{{ __('messages.purchase_orders_management') }}</h1>
            <button wire:click="openCreateModal" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-plus mr-2"></i>{{ __('messages.create_order') }}
            </button>
        </div>

        <!-- Search and Filter -->
        <div class="bg-white rounded-lg shadow mb-6 p-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex-1">
                    <label for="search" class="sr-only">{{ __('messages.search') }}</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input wire:model.debounce.300ms="search" id="search" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="{{ __('messages.search_orders') }}" type="search">
                    </div>
                </div>
                <div class="flex-initial flex space-x-2">
                    <select wire:model="statusFilter" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="">{{ __('messages.all_statuses') }}</option>
                        <option value="draft">{{ __('messages.draft') }}</option>
                        <option value="pending_approval">{{ __('messages.pending_approval') }}</option>
                        <option value="approved">{{ __('messages.approved') }}</option>
                        <option value="ordered">{{ __('messages.ordered') }}</option>
                        <option value="partially_received">{{ __('messages.partially_received') }}</option>
                        <option value="completed">{{ __('messages.completed') }}</option>
                        <option value="cancelled">{{ __('messages.cancelled') }}</option>
                    </select>
                    <select wire:model="supplierFilter" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="">{{ __('messages.all_suppliers') }}</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    <select wire:model="perPage" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="10">10 {{ __('messages.per_page') }}</option>
                        <option value="25">25 {{ __('messages.per_page') }}</option>
                        <option value="50">50 {{ __('messages.per_page') }}</option>
                        <option value="100">100 {{ __('messages.per_page') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Purchase Orders Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('order_number')">
                                    {{ __('messages.order_number') }}
                                    @if ($sortField === 'order_number')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('messages.supplier') }}
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
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('order_date')">
                                    {{ __('messages.order_date') }}
                                    @if ($sortField === 'order_date')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1"></i>
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
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $order->order_number }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $order->supplier->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $order->status == 'draft' ? 'bg-gray-100 text-gray-800' : 
                                      ($order->status == 'pending_approval' ? 'bg-yellow-100 text-yellow-800' : 
                                      ($order->status == 'approved' ? 'bg-blue-100 text-blue-800' : 
                                      ($order->status == 'ordered' ? 'bg-purple-100 text-purple-800' : 
                                      ($order->status == 'partially_received' ? 'bg-indigo-100 text-indigo-800' :
                                      ($order->status == 'completed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'))))) }}">
                                    {{ __(str_replace('_', ' ', ucfirst($order->status))) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ date('d/m/Y', strtotime($order->order_date)) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 {{ $order->is_overdue ? 'text-red-600 font-medium' : '' }}">
                                    @if($order->expected_delivery)
                                        {{ date('d/m/Y', strtotime($order->expected_delivery)) }}
                                        @if($order->is_overdue)
                                            <i class="fas fa-exclamation-circle ml-1"></i>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ number_format($order->total_amount, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="viewOrder({{ $order->id }})" class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @if($order->status != 'cancelled' && $order->status != 'completed')
                                <button wire:click="editOrder({{ $order->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endif
                                @if($order->status == 'draft' || $order->status == 'pending_approval')
                                <button wire:click="confirmDelete({{ $order->id }})" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                {{ __('messages.no_purchase_orders_found') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="px-4 py-3 bg-white border-t border-gray-200 sm:px-6">
                {{ $purchaseOrders->links() }}
            </div>
        </div>
    </div>

    <!-- View/Create/Edit Purchase Order Modal -->
    <!-- Modal content will be added here -->

    <!-- Delete Confirmation Modal -->
    <!-- Modal content will be added here -->
</div>
