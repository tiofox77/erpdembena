<div>
    <div class="py-4">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-arrow-circle-down text-green-600 mr-3"></i> Stock In Management
                </h1>
                <button
                    wire:click="openCreateModal"
                    type="button"
                    class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2 px-4 rounded flex items-center transition-colors duration-150"
                >
                    <i class="fas fa-plus-circle mr-2"></i>
                    New Stock In
                </button>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                <!-- Filter Section -->
                <div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                    <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-filter mr-2 text-blue-500"></i> Filters and Search
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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

                        <div>
                            <label for="perPage" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                <i class="fas fa-list-ol mr-1 text-gray-500"></i> Items Per Page
                            </label>
                            <div class="relative rounded-md shadow-sm">
                                <select
                                    wire:model.live="perPage"
                                    id="perPage"
                                    class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                                >
                                    <option value="10">10 per page</option>
                                    <option value="25">25 per page</option>
                                    <option value="50">50 per page</option>
                                    <option value="100">100 per page</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end mt-3">
                        <button
                            wire:click="clearFilters"
                            class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            <i class="fas fa-eraser mr-1"></i>
                            Clear Filters
                        </button>
                        <button 
                            wire:click="generatePdf"
                            class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            <i class="fas fa-file-pdf text-red-600 mr-1"></i>
                            Generate PDF Report
                        </button>
                    </div>
                </div>

                <!-- Stock In Records Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('transaction_date')">
                                    <div class="flex items-center space-x-1">
                                        <i class="fas fa-calendar-alt text-gray-400 mr-1"></i>
                                        <span>Date</span>
                                        @if($sortField === 'transaction_date')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500 ml-1"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-1">
                                        <i class="fas fa-tag text-gray-400 mr-1"></i>
                                        <span>Part</span>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-1">
                                        <i class="fas fa-tools text-gray-400 mr-1"></i>
                                        <span>Equipment</span>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('quantity')">
                                    <div class="flex items-center space-x-1">
                                        <i class="fas fa-sort-amount-up-alt text-gray-400 mr-1"></i>
                                        <span>Quantity</span>
                                        @if($sortField === 'quantity')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500 ml-1"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('supplier')">
                                    <div class="flex items-center space-x-1">
                                        <i class="fas fa-building text-gray-400 mr-1"></i>
                                        <span>Supplier</span>
                                        @if($sortField === 'supplier')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500 ml-1"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-1">
                                        <i class="fas fa-file-invoice text-gray-400 mr-1"></i>
                                        <span>Invoice</span>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-1">
                                        <i class="fas fa-sticky-note text-gray-400 mr-1"></i>
                                        <span>Notes</span>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-1">
                                        <i class="fas fa-cog text-gray-400 mr-1"></i>
                                        <span>Actions</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($this->stockTransactions as $transaction)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                                        <div class="flex items-center">
                                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full mr-2">
                                                <i class="fas fa-arrow-circle-down"></i>
                                            </span>
                                            {{ $transaction->transaction_date->format('d/m/Y') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $transaction->part->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $transaction->part->equipment->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                            +{{ $transaction->quantity }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $transaction->supplier ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $transaction->invoice_number ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                        <div class="max-w-xs truncate">
                                            {{ $transaction->notes ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                        <div class="flex space-x-1">
                                            <button 
                                                wire:click="editStockIn({{ $transaction->id }})"
                                                class="inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            >
                                                <i class="fas fa-edit text-blue-600"></i>
                                            </button>
                                            <button 
                                                wire:click="generatePdf({{ $transaction->id }})"
                                                class="inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-red-500"
                                                title="Generate PDF Receipt"
                                            >
                                                <i class="fas fa-file-pdf text-red-600"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center py-8">
                                            <div class="bg-gray-100 rounded-full p-3 mb-4">
                                                <i class="fas fa-inbox text-gray-400 text-2xl"></i>
                                            </div>
                                            <h3 class="text-lg font-medium text-gray-900 mb-1">No stock in records found</h3>
                                            <p class="text-gray-500 mb-4">There are no stock in transactions matching your criteria.</p>
                                            <button
                                                wire:click="openCreateModal"
                                                class="mt-2 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                            >
                                                <i class="fas fa-plus-circle mr-2"></i>
                                                Add Stock In
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $this->stockTransactions->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Stock In Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center">
            <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all w-full max-w-2xl">
                <!-- Modal header -->
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <span class="bg-green-100 text-green-600 p-2 rounded-full mr-3">
                            <i class="fas fa-arrow-circle-down"></i>
                        </span>
                        Add Stock In
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="px-6 py-4">
                    @if($errors->any())
                        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
                            <p class="font-bold flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                Please fix the following errors:
                            </p>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form wire:submit.prevent="processStockIn">
                        <div class="bg-gray-50 p-4 rounded-md mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                                <i class="fas fa-tag mr-2 text-blue-500"></i> Part Selection
                            </h4>
                            <div>
                                <label for="part-id" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-tools mr-1 text-gray-500"></i> Select Part <span class="text-red-500">*</span>
                                </label>
                                <select 
                                    wire:model="stockIn.equipment_part_id"
                                    id="part-id"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                                >
                                    <option value="">-- Select a part --</option>
                                    @foreach($this->partsForEquipment as $part)
                                        <option value="{{ $part->id }}">{{ $part->name }} ({{ $part->part_number ?? 'No P/N' }}) - Current Stock: {{ $part->stock_quantity }}</option>
                                    @endforeach
                                </select>
                                @error('stockIn.equipment_part_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-md mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                                <i class="fas fa-boxes mr-2 text-blue-500"></i> Stock Information
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="quantity" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                        <i class="fas fa-sort-amount-up-alt mr-1 text-gray-500"></i> Quantity <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative rounded-md shadow-sm">
                                        <input
                                            wire:model="stockIn.quantity"
                                            type="number"
                                            min="1"
                                            id="quantity"
                                            class="block w-full pr-10 border-gray-300 focus:ring-blue-500 focus:border-blue-500 rounded-md sm:text-sm"
                                            placeholder="Enter quantity"
                                        >
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i class="fas fa-boxes text-gray-400"></i>
                                        </div>
                                    </div>
                                    @error('stockIn.quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="unit-cost" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                        <i class="fas fa-dollar-sign mr-1 text-gray-500"></i> Unit Cost
                                    </label>
                                    <div class="relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">$</span>
                                        </div>
                                        <input
                                            wire:model="stockIn.unit_cost"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            id="unit-cost"
                                            class="block w-full pl-7 pr-10 border-gray-300 focus:ring-blue-500 focus:border-blue-500 rounded-md sm:text-sm"
                                            placeholder="0.00"
                                        >
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i class="fas fa-tag text-gray-400"></i>
                                        </div>
                                    </div>
                                    @error('stockIn.unit_cost') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-md mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                                <i class="fas fa-info-circle mr-2 text-blue-500"></i> Additional Information
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="supplier" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                        <i class="fas fa-building mr-1 text-gray-500"></i> Supplier
                                    </label>
                                    <div class="relative rounded-md shadow-sm">
                                        <input
                                            wire:model="stockIn.supplier"
                                            type="text"
                                            id="supplier"
                                            class="block w-full pr-10 border-gray-300 focus:ring-blue-500 focus:border-blue-500 rounded-md sm:text-sm"
                                            placeholder="Enter supplier name"
                                        >
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i class="fas fa-building text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label for="invoice-number" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                        <i class="fas fa-file-invoice mr-1 text-gray-500"></i> Invoice Number
                                    </label>
                                    <div class="relative rounded-md shadow-sm">
                                        <input
                                            wire:model="stockIn.invoice_number"
                                            type="text"
                                            id="invoice-number"
                                            class="block w-full pr-10 border-gray-300 focus:ring-blue-500 focus:border-blue-500 rounded-md sm:text-sm"
                                            placeholder="Enter invoice number"
                                        >
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i class="fas fa-file-invoice text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label for="received-date" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                        <i class="fas fa-calendar-alt mr-1 text-gray-500"></i> Received Date
                                    </label>
                                    <div class="relative rounded-md shadow-sm">
                                        <input
                                            wire:model="stockIn.received_date"
                                            type="date"
                                            id="received-date"
                                            class="block w-full pr-10 border-gray-300 focus:ring-blue-500 focus:border-blue-500 rounded-md sm:text-sm"
                                        >
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i class="fas fa-calendar-alt text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-md mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                                <i class="fas fa-sticky-note mr-2 text-blue-500"></i> Notes
                            </h4>
                            <div>
                                <textarea
                                    wire:model="stockIn.notes"
                                    id="notes"
                                    rows="3"
                                    class="shadow-sm block w-full focus:ring-blue-500 focus:border-blue-500 border-gray-300 rounded-md"
                                    placeholder="Enter any additional notes..."
                                ></textarea>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 flex justify-end">
                            <button
                                type="button"
                                wire:click="closeModal"
                                class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 mr-3"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                            >
                                <i class="fas fa-save mr-2"></i>
                                Process Stock In
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
