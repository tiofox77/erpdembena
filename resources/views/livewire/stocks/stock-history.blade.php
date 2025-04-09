<div>
    <div class="py-4">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-history text-blue-600 mr-3"></i> Inventory Transaction History
                </h1>
                <div class="flex space-x-2">
                    <button 
                        wire:click="generatePdf"
                        class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2 px-4 rounded flex items-center transition-colors duration-150"
                    >
                        <i class="fas fa-file-pdf mr-2"></i>
                        Export PDF
                    </button>
                    <a href="{{ route('stocks.stockin') }}" class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2 px-4 rounded flex items-center transition-colors duration-150">
                        <i class="fas fa-arrow-circle-up mr-2"></i>
                        Stock In
                    </a>
                    <a href="{{ route('stocks.stockout') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded flex items-center transition-colors duration-150">
                        <i class="fas fa-arrow-circle-down mr-2"></i>
                        Stock Out
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Transactions</p>
                            <p class="text-xl font-bold text-gray-800">{{ $this->inventoryStats['total_transactions'] }}</p>
                        </div>
                        <div class="rounded-full bg-blue-100 p-3">
                            <i class="fas fa-exchange-alt text-blue-500"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Stock In Transactions</p>
                            <p class="text-xl font-bold text-gray-800">{{ $this->inventoryStats['stock_in_count'] }}</p>
                        </div>
                        <div class="rounded-full bg-green-100 p-3">
                            <i class="fas fa-arrow-circle-up text-green-500"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-red-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Stock Out Transactions</p>
                            <p class="text-xl font-bold text-gray-800">{{ $this->inventoryStats['stock_out_count'] }}</p>
                        </div>
                        <div class="rounded-full bg-red-100 p-3">
                            <i class="fas fa-arrow-circle-down text-red-500"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-indigo-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Inventory Value</p>
                            <p class="text-xl font-bold text-gray-800">
                                ${{ number_format($this->inventoryStats['total_value'], 2) }}
                            </p>
                        </div>
                        <div class="rounded-full bg-indigo-100 p-3">
                            <i class="fas fa-dollar-sign text-indigo-500"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="mb-6 bg-white overflow-hidden shadow-sm rounded-lg p-6 border border-gray-200">
                <h4 class="text-sm font-medium text-gray-700 mb-4 flex items-center">
                    <i class="fas fa-filter mr-2 text-blue-500"></i> Filters and Search
                </h4>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Search Input -->
                    <div>
                        <label for="search" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                            <i class="fas fa-search mr-1 text-gray-500"></i> Search
                        </label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input
                                wire:model.live.debounce.300ms="search"
                                type="text"
                                id="search"
                                placeholder="Search by part name, supplier..."
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            >
                        </div>
                    </div>
                    
                    <!-- Equipment Dropdown -->
                    <div>
                        <label for="equipmentId" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                            <i class="fas fa-tools mr-1 text-gray-500"></i> Equipment
                        </label>
                        <div class="relative rounded-md shadow-sm">
                            <select
                                id="equipmentId"
                                wire:model.live="equipmentId"
                                class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            >
                                <option value="">All Equipment</option>
                                @foreach($this->equipmentList as $equipment)
                                    <option value="{{ $equipment->id }}">{{ $equipment->name }} - {{ $equipment->serial_number }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Part Dropdown -->
                    <div>
                        <label for="partId" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                            <i class="fas fa-cog mr-1 text-gray-500"></i> Part
                        </label>
                        <div class="relative rounded-md shadow-sm">
                            <select
                                id="partId"
                                wire:model.live="partId"
                                class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            >
                                <option value="">All Parts</option>
                                @foreach($this->partsList as $part)
                                    <option value="{{ $part->id }}">{{ $part->name }} ({{ $part->part_number ?: 'No P/N' }})</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <!-- Transaction Type -->
                    <div>
                        <label for="transactionType" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                            <i class="fas fa-exchange-alt mr-1 text-gray-500"></i> Transaction Type
                        </label>
                        <select 
                            wire:model.live="transactionType" 
                            id="transactionType" 
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >
                            <option value="">All Types</option>
                            <option value="stock_in">Stock In</option>
                            <option value="stock_out">Stock Out</option>
                        </select>
                    </div>
                    
                    <!-- Date Range -->
                    <div>
                        <label for="dateFrom" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                            <i class="fas fa-calendar-alt mr-1 text-gray-500"></i> From Date
                        </label>
                        <input 
                            wire:model.live="dateFrom" 
                            type="date" 
                            id="dateFrom" 
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >
                    </div>
                    
                    <div>
                        <label for="dateTo" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                            <i class="fas fa-calendar-alt mr-1 text-gray-500"></i> To Date
                        </label>
                        <input 
                            wire:model.live="dateTo" 
                            type="date" 
                            id="dateTo" 
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >
                    </div>
                </div>
                
                <div class="flex justify-end mt-4">
                    <button
                        wire:click="clearFilters"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <i class="fas fa-eraser mr-2"></i>
                        Clear Filters
                    </button>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('transaction_date')">
                                    <div class="flex items-center space-x-1">
                                        <span>Date</span>
                                        @if ($sortField === 'transaction_date')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-gray-400"></i>
                                        @else
                                            <i class="fas fa-sort text-gray-400"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center">
                                        <span>Part</span>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('type')">
                                    <div class="flex items-center space-x-1">
                                        <span>Type</span>
                                        @if ($sortField === 'type')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-gray-400"></i>
                                        @else
                                            <i class="fas fa-sort text-gray-400"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('quantity')">
                                    <div class="flex items-center space-x-1">
                                        <span>Quantity</span>
                                        @if ($sortField === 'quantity')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-gray-400"></i>
                                        @else
                                            <i class="fas fa-sort text-gray-400"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('unit_cost')">
                                    <div class="flex items-center space-x-1">
                                        <span>Unit Cost</span>
                                        @if ($sortField === 'unit_cost')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-gray-400"></i>
                                        @else
                                            <i class="fas fa-sort text-gray-400"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center">
                                        <span>Supplier/Ref</span>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center">
                                        <span>Notes</span>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center">
                                        <span>Created By</span>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center">
                                        <i class="fas fa-cog text-gray-400 mr-1"></i>
                                        <span>Actions</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($this->stockTransactions as $transaction)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="flex flex-col">
                                            <span>{{ $transaction->transaction_date->format('d M Y') }}</span>
                                            <span class="text-xs text-gray-500">{{ $transaction->transaction_date->format('H:i') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="flex flex-col">
                                            <span class="font-medium">{{ $transaction->part->name }}</span>
                                            <span class="text-xs text-gray-500">{{ $transaction->part->part_number ?: 'No P/N' }}</span>
                                            <span class="text-xs text-gray-500">{{ $transaction->part->equipment->name ?? 'No equipment' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($transaction->type === 'stock_in')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-arrow-circle-up mr-1"></i> Stock In
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-arrow-circle-down mr-1"></i> Stock Out
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="font-medium">{{ number_format($transaction->quantity) }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($transaction->unit_cost)
                                            <span>${{ number_format($transaction->unit_cost, 2) }}</span>
                                        @else
                                            <span class="text-gray-500">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="flex flex-col">
                                            @if($transaction->supplier)
                                                <span>{{ $transaction->supplier }}</span>
                                            @endif
                                            
                                            @if($transaction->invoice_number)
                                                <span class="text-xs text-gray-500">Ref: {{ $transaction->invoice_number }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 max-w-xs">
                                        <div class="truncate">
                                            {{ $transaction->notes ?: '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ optional($transaction->createdBy)->name ?? 'System' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <button 
                                            wire:click="generatePdf({{ $transaction->id }})"
                                            class="inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-red-500"
                                            title="Generate PDF Receipt"
                                        >
                                            <i class="fas fa-file-pdf text-red-600"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-4 text-sm text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center py-6">
                                            <i class="fas fa-box-open text-gray-400 text-4xl mb-2"></i>
                                            <p>No inventory transactions found matching your criteria.</p>
                                            <button 
                                                wire:click="clearFilters"
                                                class="mt-2 inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                            >
                                                <i class="fas fa-eraser mr-1"></i> Clear Filters
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
                    {{ $this->stockTransactions->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
