<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Parts/Supply Lifecycle History</h1>

    <!-- Filters Section -->
    <div class="bg-white p-4 mb-6 rounded-lg shadow">
        <div class="flex flex-wrap -mx-2">
            <!-- Category Filter -->
            <div class="w-full md:w-1/4 px-2 mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Part Category</label>
                <select wire:model.live="partCategory" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}">{{ $category }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Part Filter -->
            <div class="w-full md:w-1/4 px-2 mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Part</label>
                <select wire:model.live="partId" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="">All Parts</option>
                    @foreach($parts as $part)
                        <option value="{{ $part->id }}">{{ $part->name }} ({{ $part->sku }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Supplier Filter -->
            <div class="w-full md:w-1/4 px-2 mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                <select wire:model.live="supplierId" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Transaction Type Filter -->
            <div class="w-full md:w-1/4 px-2 mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Type</label>
                <select wire:model.live="transactionType" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="all">All Transactions</option>
                    @foreach($transactionTypes as $value => $label)
                        @if($value !== 'all')
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <!-- Date Range Filter -->
            <div class="w-full md:w-1/4 px-2 mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                <select wire:model.live="dateRange" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="last-week">Last Week</option>
                    <option value="last-month">Last Month</option>
                    <option value="last-quarter">Last Quarter</option>
                    <option value="last-year">Last Year</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>

            <!-- Custom Date Range -->
            @if($dateRange === 'custom')
                <div class="w-full md:w-1/4 px-2 mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" wire:model.live="startDate" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                </div>
                <div class="w-full md:w-1/4 px-2 mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" wire:model.live="endDate" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                </div>
            @endif

            <!-- Search Input -->
            <div class="w-full md:w-1/4 px-2 mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Search parts or transactions..." class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 pl-10">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <!-- Total Parts -->
        <div class="bg-white p-4 rounded-lg shadow flex items-center">
            <div class="rounded-full p-3 bg-blue-100 mr-4">
                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Parts</p>
                <p class="text-xl font-semibold">{{ number_format($totalParts) }}</p>
            </div>
        </div>

        <!-- Total Inventory Value -->
        <div class="bg-white p-4 rounded-lg shadow flex items-center">
            <div class="rounded-full p-3 bg-green-100 mr-4">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Inventory Value</p>
                <p class="text-xl font-semibold">${{ number_format($totalValue, 2) }}</p>
            </div>
        </div>

        <!-- Total Transactions -->
        <div class="bg-white p-4 rounded-lg shadow flex items-center">
            <div class="rounded-full p-3 bg-purple-100 mr-4">
                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Transactions</p>
                <p class="text-xl font-semibold">{{ number_format($totalTransactions) }}</p>
            </div>
        </div>

        <!-- Most Used Part -->
        <div class="bg-white p-4 rounded-lg shadow flex items-center">
            <div class="rounded-full p-3 bg-yellow-100 mr-4">
                <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Most Used Part</p>
                <p class="text-xl font-semibold">{{ $mostUsedPart ? $mostUsedPart->name : 'N/A' }}</p>
            </div>
        </div>

        <!-- Top Supplier -->
        <div class="bg-white p-4 rounded-lg shadow flex items-center">
            <div class="rounded-full p-3 bg-red-100 mr-4">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Top Supplier</p>
                <p class="text-xl font-semibold">{{ $topSupplier ? $topSupplier->name : 'N/A' }}</p>
            </div>
        </div>
    </div>

    <!-- Part Lifecycle & Transactions Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="flex justify-between items-center p-4 border-b">
            <h2 class="text-lg font-semibold text-gray-800">Parts Transaction History</h2>
            <button wire:click="exportTransactionHistory" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 flex items-center">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Export
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sort('created_at')">
                            Date/Time
                            @if($sortField === 'created_at')
                                @if($sortDirection === 'asc')
                                    <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                @else
                                    <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                @endif
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Part</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sort('quantity')">
                            Quantity
                            @if($sortField === 'quantity')
                                @if($sortDirection === 'asc')
                                    <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                @else
                                    <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                @endif
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sort('unit_cost')">
                            Unit Cost
                            @if($sortField === 'unit_cost')
                                @if($sortDirection === 'asc')
                                    <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                @else
                                    <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                @endif
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier/Related Work</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($partTransactions as $transaction)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $transaction->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $transaction->part ? $transaction->part->name : 'Unknown Part' }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $transaction->part ? $transaction->part->sku : '' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if($transaction->transaction_type === 'purchase')
                                        bg-green-100 text-green-800
                                    @elseif($transaction->transaction_type === 'usage')
                                        bg-blue-100 text-blue-800
                                    @elseif($transaction->transaction_type === 'return')
                                        bg-yellow-100 text-yellow-800
                                    @elseif($transaction->transaction_type === 'adjustment')
                                        bg-purple-100 text-purple-800
                                    @else
                                        bg-gray-100 text-gray-800
                                    @endif
                                ">
                                    {{ $transactionTypes[$transaction->transaction_type] ?? $transaction->transaction_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="{{ $transaction->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $transaction->quantity > 0 ? '+' : '' }}{{ $transaction->quantity }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${{ number_format($transaction->unit_cost, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($transaction->supplier)
                                    {{ $transaction->supplier->name }}
                                @elseif($transaction->maintenance)
                                    Work Order: {{ $transaction->maintenance->work_order_number }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $transaction->reference_number ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button type="button" class="text-blue-600 hover:text-blue-900" onclick="showTransactionDetails('{{ $transaction->id }}')">
                                    View
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                No transaction records found with the current filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t">
            {{ $partTransactions->links() }}
        </div>
    </div>

    <!-- Part Lifecycle Visualization (to be implemented later) -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="p-4 border-b">
            <h2 class="text-lg font-semibold text-gray-800">Parts Lifecycle Visualization</h2>
        </div>
        <div class="p-4">
            <p class="text-gray-600 mb-4">Select a specific part above to view its complete lifecycle visualization.</p>
            @if($partId)
                <div class="h-64 bg-gray-100 rounded flex items-center justify-center">
                    <p class="text-gray-500">Part lifecycle visualization will be shown here in future updates.</p>
                </div>
            @else
                <div class="p-6 bg-gray-50 rounded text-center">
                    <p class="text-gray-500">Please select a specific part to view its lifecycle visualization.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showTransactionDetails(transactionId) {
        // This would be implemented to show a modal with full transaction details
        alert('Transaction details for ID: ' + transactionId + ' will be shown in a modal in the future implementation.');
    }
</script>
@endpush
