<div>
    <div class="container mx-auto py-6 px-4">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Stock Out Management</h1>
            <p class="text-gray-500 mt-1">Manage parts and materials removed from inventory</p>
        </div>

        <!-- Filters and Search Section -->
        <div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
            <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                <i class="fas fa-filter mr-2 text-blue-500"></i> Filters and Search
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <!-- Search Box -->
                <div class="md:col-span-2">
                    <label for="search" class="block text-xs font-medium text-gray-700 mb-1">Search</label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input
                            wire:model.live.debounce.300ms="search"
                            type="text"
                            id="search"
                            class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md"
                            placeholder="Search by part name, part number..."
                        >
                    </div>
                </div>

                <!-- Part Filter -->
                <div class="md:col-span-2">
                    <label for="partFilter" class="block text-xs font-medium text-gray-700 mb-1">Filter by Part</label>
                    <select
                        wire:model.live="equipmentPartId"
                        id="partFilter"
                        class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                    >
                        <option value="">All Parts</option>
                        @foreach($this->partsList as $part)
                            <option value="{{ $part->id }}">{{ $part->name }} ({{ $part->part_number ?: 'No P/N' }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Per Page Selection -->
                <div>
                    <label for="perPage" class="block text-xs font-medium text-gray-700 mb-1">Items Per Page</label>
                    <select
                        wire:model.live="perPage"
                        id="perPage"
                        class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                    >
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <!-- Clear Filters Button -->
                <div class="flex items-end md:col-span-1">
                    <button
                        wire:click="clearFilters"
                        class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <i class="fas fa-times mr-1.5"></i> Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Actions Bar -->
        <div class="mb-4 flex justify-between items-center">
            <div>
                <span class="text-sm text-gray-600">
                    Showing <span class="font-medium">{{ $this->stockOuts->firstItem() ?? 0 }}</span> to 
                    <span class="font-medium">{{ $this->stockOuts->lastItem() ?? 0 }}</span> of 
                    <span class="font-medium">{{ $this->stockOuts->total() }}</span> stock out records
                </span>
            </div>
            <div class="flex space-x-2">
                <button
                    wire:click="generatePdf"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    <i class="fas fa-file-pdf mr-2 text-red-500"></i> Export PDF
                </button>
                <button
                    wire:click="openCreateModal"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    <i class="fas fa-plus mr-2"></i> New Stock Out
                </button>
            </div>
        </div>

        <!-- Stock Out Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('date')">
                            <div class="flex items-center space-x-1">
                                <span>Date</span>
                                @if ($sortField === 'date')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                @else
                                    <i class="fas fa-sort text-gray-400"></i>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ref Number
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Parts
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Reason
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            User
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($this->stockOuts as $stockOut)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ date('d M Y', strtotime($stockOut->date)) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $stockOut->reference_number ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $stockOut->items->count() }} part(s)
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $stockOut->items->sum('quantity') }} total items
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ \Illuminate\Support\Str::limit($stockOut->reason, 30) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $stockOut->user->first_name ?? '' }} {{ $stockOut->user->last_name ?? '' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <button wire:click="viewStockOut({{ $stockOut->id }})" 
                                    class="text-blue-600 hover:text-blue-900 w-8 h-8 rounded-full flex items-center justify-center hover:bg-blue-100"
                                    title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button wire:click="generatePdf({{ $stockOut->id }})" 
                                    class="text-red-600 hover:text-red-900 w-8 h-8 rounded-full flex items-center justify-center hover:bg-red-100"
                                    title="Generate PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </button>
                                <button wire:click="editStockOut({{ $stockOut->id }})" 
                                    class="text-indigo-600 hover:text-indigo-900 w-8 h-8 rounded-full flex items-center justify-center hover:bg-indigo-100"
                                    title="Edit Stock Out">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button wire:click="confirmDelete({{ $stockOut->id }})" 
                                    class="text-red-600 hover:text-red-900 w-8 h-8 rounded-full flex items-center justify-center hover:bg-red-100"
                                    title="Delete Stock Out">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            No stock out records found. <button wire:click="openCreateModal" class="text-blue-600 hover:text-blue-900">Create one now</button>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $this->stockOuts->links() }}
        </div>

        <!-- Create/Edit Modal -->
        @if($showModal)
        <div
            class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50"
            x-data="{}"
        >
            <div class="bg-white rounded-lg shadow-lg w-full max-w-5xl p-6 overflow-y-auto max-h-[90vh]">
                <!-- Modal Header -->
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">
                        {{ $isEditing ? 'Edit Stock Out' : 'New Stock Out' }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Form -->
                <form wire:submit.prevent="saveStockOut">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Column - Transaction Details -->
                        <div class="space-y-4">
                            <h4 class="font-medium text-gray-700 border-b pb-2">Transaction Details</h4>

                            <!-- Reference Number -->
                            <div>
                                <label for="reference" class="block text-sm font-medium text-gray-700">Reference Number</label>
                                <input 
                                    wire:model="stockOut.reference_number" 
                                    type="text" 
                                    id="reference" 
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" 
                                    placeholder="Enter reference number"
                                    {{ !$isEditing ? 'readonly' : '' }}
                                >
                                @error('stockOut.reference_number') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                            </div>

                            <!-- Date -->
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700">Date <span class="text-red-500">*</span></label>
                                <input 
                                    wire:model="stockOut.date" 
                                    type="date" 
                                    id="date" 
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                >
                                @error('stockOut.date') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                            </div>

                            <!-- Reason -->
                            <div>
                                <label for="reason" class="block text-sm font-medium text-gray-700">Reason <span class="text-red-500">*</span></label>
                                <input 
                                    wire:model="stockOut.reason" 
                                    type="text" 
                                    id="reason" 
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" 
                                    placeholder="Enter reason for stock out"
                                >
                                @error('stockOut.reason') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                            </div>

                            <!-- Notes -->
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea 
                                    wire:model="stockOut.notes" 
                                    id="notes" 
                                    rows="3" 
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" 
                                    placeholder="Enter additional notes"
                                ></textarea>
                                @error('stockOut.notes') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Right Column - Parts Selection -->
                        <div class="space-y-4">
                            <h4 class="font-medium text-gray-700 border-b pb-2">Part Selection <span class="text-red-500">*</span></h4>
                            
                            @error('selectedParts') <div class="text-sm text-red-500 mb-2">{{ $message }}</div> @enderror
                            
                            <!-- Add New Part Form -->
                            <div class="bg-gray-50 p-3 rounded-md border border-gray-200">
                                <h5 class="text-sm font-medium text-gray-700 mb-2">Add Part</h5>
                                <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                                    <div class="md:col-span-3">
                                        <select 
                                            wire:model="newPart.equipment_part_id" 
                                            class="block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                        >
                                            <option value="">Select Part</option>
                                            @foreach($this->partsList as $part)
                                                <option value="{{ $part->id }}" {{ $part->stock_quantity <= 0 ? 'disabled' : '' }}>
                                                    {{ $part->name }} ({{ $part->part_number ?: 'No P/N' }}) - Stock: {{ $part->stock_quantity }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('newPart.equipment_part_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <input 
                                            wire:model="newPart.quantity" 
                                            type="number" 
                                            min="1" 
                                            class="block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" 
                                            placeholder="Qty"
                                        >
                                        @error('newPart.quantity') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <button 
                                            type="button"
                                            wire:click="addPart"
                                            class="inline-flex justify-center w-full px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                        >
                                            <i class="fas fa-plus mr-1"></i> Add
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Selected Parts List -->
                            <div class="mt-3 overflow-y-auto" style="max-height: 300px;">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Part</th>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID/BAC</th>
                                            <th scope="col" class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                            <th scope="col" class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($selectedParts as $index => $part)
                                        <tr>
                                            <td class="px-3 py-2 text-sm">
                                                {{ $part['part_name'] }}
                                            </td>
                                            <td class="px-3 py-2 text-xs text-gray-500">
                                                {{ $part['part_number'] ?: 'No P/N' }}<br>
                                                {{ $part['bac_code'] ? 'BAC: '.$part['bac_code'] : '' }}
                                            </td>
                                            <td class="px-3 py-2 text-sm text-center">
                                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                                    {{ $part['quantity'] }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                <button 
                                                    type="button" 
                                                    wire:click="removePart({{ $index }})" 
                                                    class="text-red-600 hover:text-red-900"
                                                >
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="px-3 py-4 text-sm text-gray-500 text-center">
                                                No parts selected yet. Please add at least one part above.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer with Actions -->
                    <div class="mt-6 flex justify-end space-x-3 border-t pt-4">
                        <button 
                            type="button" 
                            wire:click="closeModal" 
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            {{ $isEditing ? 'Update Record' : 'Save Record' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        <!-- Delete Confirmation Modal -->
        @if($showDeleteModal)
        <div
            class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50"
        >
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-red-600">Confirm Deletion</h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <p class="mb-4 text-sm text-gray-500">
                    Are you sure you want to delete this stock out record? This action will return the items to stock and cannot be undone.
                </p>
                <div class="flex justify-end space-x-3">
                    <button 
                        wire:click="closeModal" 
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Cancel
                    </button>
                    <button 
                        wire:click="deleteStockOut" 
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
        @endif

        <!-- View Modal -->
        @if($showViewModal && $viewingStockOut)
        <div
            class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50"
        >
            <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 overflow-y-auto max-h-[90vh]">
                <!-- Modal Header -->
                <div class="flex justify-between items-center mb-4 border-b pb-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-arrow-circle-down text-red-500 mr-2"></i>
                        Stock Out Details
                    </h3>
                    <button wire:click="closeViewModal" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Transaction Information -->
                    <div class="space-y-4">
                        <h4 class="font-medium text-gray-700 border-b pb-2">Transaction Information</h4>
                        
                        <div>
                            <p class="text-sm text-gray-500">Reference Number</p>
                            <p class="font-medium">{{ $viewingStockOut->reference_number ?: 'N/A' }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500">Date</p>
                            <p class="font-medium">{{ date('d M Y', strtotime($viewingStockOut->date)) }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500">User</p>
                            <p class="font-medium">{{ $viewingStockOut->user->first_name ?? '' }} {{ $viewingStockOut->user->last_name ?? '' }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500">Reason</p>
                            <p class="font-medium">{{ $viewingStockOut->reason }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500">Notes</p>
                            <p class="font-medium">{{ $viewingStockOut->notes ?: 'No additional notes' }}</p>
                        </div>
                    </div>

                    <!-- Parts Information -->
                    <div>
                        <h4 class="font-medium text-gray-700 border-b pb-2 mb-3">Parts Information</h4>
                        
                        <div class="overflow-y-auto max-h-52">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Part</th>
                                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID/BAC</th>
                                        <th scope="col" class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($viewingStockOut->items as $item)
                                    <tr>
                                        <td class="px-3 py-2 text-sm font-medium">
                                            {{ $item->equipmentPart->name ?? 'Part Removed' }}
                                        </td>
                                        <td class="px-3 py-2 text-xs text-gray-500">
                                            {{ $item->equipmentPart->part_number ?: 'No P/N' }}<br>
                                            {{ $item->equipmentPart->bac_code ? 'BAC: '.$item->equipmentPart->bac_code : '' }}
                                        </td>
                                        <td class="px-3 py-2 text-sm text-center">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                                {{ $item->quantity }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td class="px-3 py-2 text-sm font-medium" colspan="2">Total Items</td>
                                        <td class="px-3 py-2 text-sm font-medium text-center">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                                {{ $viewingStockOut->items->sum('quantity') }}
                                            </span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-6 flex justify-end space-x-3 border-t pt-4">
                    <button 
                        wire:click="editStockOut({{ $viewingStockOut->id }})" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <i class="fas fa-edit mr-2"></i>
                        Edit Record
                    </button>
                    <button 
                        wire:click="generatePdf({{ $viewingStockOut->id }})" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <i class="fas fa-file-pdf mr-2 text-red-500"></i>
                        Download PDF
                    </button>
                    <button 
                        wire:click="closeViewModal" 
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
        @endif

        <!-- JavaScript For Toastr Notifications -->
        <script>
            document.addEventListener("livewire:initialized", () => {
                @this.on('notify', (data) => {
                    toastr[data.type](data.message, data.type === 'error' ? 'Error' : (data.type === 'success' ? 'Success' : 'Information'));
                });
            });
        </script>
    </div>
</div>
