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
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
            <div class="bg-white rounded-lg shadow-xl w-full max-w-5xl p-6 overflow-y-auto max-h-[90vh]">
                <!-- Modal Header -->
                <div class="flex justify-between items-center mb-6 border-b border-gray-200 pb-4">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas {{ $isEditing ? 'fa-edit' : 'fa-arrow-circle-down' }} text-blue-600 mr-2"></i>
                        {{ $isEditing ? 'Edit Stock Out' : 'New Stock Out' }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-500 text-xl">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Error Summary -->
                @if($errors->any())
                <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
                    <p class="font-bold flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        Please correct the following errors:
                    </p>
                    <ul class="mt-2 list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Form -->
                <form wire:submit.prevent="saveStockOut">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Column - Transaction Details -->
                        <div class="space-y-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <h4 class="font-medium text-gray-700 border-b pb-2 flex items-center">
                                <i class="fas fa-info-circle text-blue-500 mr-2"></i> Transaction Details
                            </h4>

                            <!-- Reference Number -->
                            <div>
                                <label for="reference" class="block text-sm font-medium text-gray-700 flex items-center">
                                    <i class="fas fa-hashtag text-gray-500 mr-1"></i> Reference Number
                                </label>
                                <input 
                                    wire:model="stockOut.reference_number" 
                                    type="text" 
                                    id="reference" 
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-gray-50" 
                                    readonly
                                >
                                @error('stockOut.reference_number') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                            </div>

                            <!-- Date -->
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700 flex items-center">
                                    <i class="fas fa-calendar-alt text-gray-500 mr-1"></i> Date <span class="text-red-500">*</span>
                                </label>
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
                                <label for="reason" class="block text-sm font-medium text-gray-700 flex items-center">
                                    <i class="fas fa-comment-alt text-gray-500 mr-1"></i> Reason <span class="text-red-500">*</span>
                                </label>
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
                                <label for="notes" class="block text-sm font-medium text-gray-700 flex items-center">
                                    <i class="fas fa-sticky-note text-gray-500 mr-1"></i> Notes
                                </label>
                                <textarea 
                                    wire:model="stockOut.notes" 
                                    id="notes" 
                                    rows="3" 
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" 
                                    placeholder="Enter additional notes about this transaction..."
                                ></textarea>
                                @error('stockOut.notes') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Right Column - Parts Selection -->
                        <div class="space-y-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <h4 class="font-medium text-gray-700 border-b pb-2 flex items-center">
                                <i class="fas fa-tools text-blue-500 mr-2"></i> Part Selection <span class="text-red-500">*</span>
                            </h4>
                            
                            @error('selectedParts') <div class="text-sm text-red-500 mb-2">{{ $message }}</div> @enderror
                            
                            <!-- Add New Part Form -->
                            <div class="bg-white p-3 rounded-md border border-gray-200 shadow-sm">
                                <h5 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                                    <i class="fas fa-plus-circle text-green-500 mr-1"></i> Add Part
                                </h5>
                                <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                                    <div class="md:col-span-3">
                                        <div class="relative">
                                            <select 
                                                wire:model.live="newPart.equipment_part_id" 
                                                class="block w-full shadow-sm sm:text-sm border-gray-300 rounded-md pr-10"
                                            >
                                                <option value="">Select Part</option>
                                                @foreach($this->partsList as $part)
                                                    <option value="{{ $part->id }}" {{ $part->stock_quantity <= 0 ? 'disabled' : '' }} class="{{ $part->stock_quantity <= 3 ? 'text-orange-600 font-medium' : '' }}">
                                                        {{ $part->name }} ({{ $part->part_number ?: 'No P/N' }}) - Stock: {{ $part->stock_quantity }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                            </div>
                                        </div>
                                        @error('newPart.equipment_part_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <div class="relative">
                                            <input 
                                                wire:model="newPart.quantity" 
                                                type="number" 
                                                min="1" 
                                                class="block w-full shadow-sm sm:text-sm border-gray-300 rounded-md pl-8" 
                                                placeholder="Qty"
                                            >
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-hashtag text-gray-400 text-xs"></i>
                                            </div>
                                        </div>
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
                            <div class="mt-3 overflow-y-auto border border-gray-200 rounded-md shadow-sm" style="max-height: 300px;">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50 sticky top-0">
                                        <tr>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <span class="flex items-center">
                                                    <i class="fas fa-tools text-gray-400 mr-1"></i> Part
                                                </span>
                                            </th>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <span class="flex items-center">
                                                    <i class="fas fa-barcode text-gray-400 mr-1"></i> ID/BAC
                                                </span>
                                            </th>
                                            <th scope="col" class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <span class="flex items-center justify-center">
                                                    <i class="fas fa-sort-amount-down text-gray-400 mr-1"></i> Qty
                                                </span>
                                            </th>
                                            <th scope="col" class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <span class="flex items-center justify-center">
                                                    <i class="fas fa-cog text-gray-400 mr-1"></i> Actions
                                                </span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($selectedParts as $index => $part)
                                        <tr class="hover:bg-gray-50">
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
                                                    class="text-red-600 hover:text-red-900 w-8 h-8 rounded-full flex items-center justify-center hover:bg-red-50"
                                                    title="Remove Part"
                                                >
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="px-3 py-4 text-sm text-gray-500 text-center">
                                                <div class="flex flex-col items-center justify-center py-6">
                                                    <i class="fas fa-box-open text-gray-400 text-3xl mb-2"></i>
                                                    No parts selected yet. Please add at least one part above.
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer with Actions -->
                    <div class="mt-6 flex justify-end space-x-3 border-t border-gray-200 pt-4">
                        <button 
                            type="button" 
                            wire:click="closeModal" 
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center"
                        >
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button 
                            type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center"
                        >
                            <i class="fas {{ $isEditing ? 'fa-save' : 'fa-plus-circle' }} mr-2"></i>
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
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
                <div class="flex justify-between items-center mb-4 border-b border-gray-200 pb-4">
                    <h3 class="text-lg font-medium text-red-600 flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Confirm Deletion
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-500 text-xl">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-6">
                    <p class="mb-4 text-sm text-gray-500">
                        Are you sure you want to delete this stock out record? This action will return the items to stock and cannot be undone.
                    </p>
                    <div class="bg-yellow-50 p-4 rounded-md border-l-4 border-yellow-400">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-yellow-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    This will permanently delete the record and return inventory items to stock.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end space-x-3">
                    <button
                        wire:click="closeModal"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 flex items-center"
                    >
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>
                    <button
                        wire:click="deleteStockOut"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 flex items-center"
                    >
                        <i class="fas fa-trash-alt mr-2"></i> Delete
                    </button>
                </div>
            </div>
        </div>
        @endif

        <!-- View Modal -->
        @if($showViewModal && $viewingStockOut)
        <div
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
            <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl p-6 overflow-y-auto max-h-[90vh]">
                <!-- Modal Header -->
                <div class="flex justify-between items-center mb-6 border-b border-gray-200 pb-4">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-arrow-circle-down text-blue-600 mr-2"></i>
                        Stock Out Details
                    </h3>
                    <button wire:click="closeViewModal" class="text-gray-400 hover:text-gray-500 text-xl">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Transaction Information -->
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                        <div class="bg-blue-50 px-4 py-2 rounded-t-lg flex items-center">
                            <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                            <h4 class="font-medium text-gray-700">Transaction Information</h4>
                        </div>
                        <div class="p-4 space-y-3">
                            <div>
                                <p class="text-sm text-gray-500 flex items-center">
                                    <i class="fas fa-hashtag text-gray-400 mr-1"></i> Reference Number
                                </p>
                                <p class="font-medium">{{ $viewingStockOut->reference_number ?: 'N/A' }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-500 flex items-center">
                                    <i class="fas fa-calendar-alt text-gray-400 mr-1"></i> Date
                                </p>
                                <p class="font-medium">{{ date('d M Y', strtotime($viewingStockOut->date)) }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-500 flex items-center">
                                    <i class="fas fa-comment-alt text-gray-400 mr-1"></i> Reason
                                </p>
                                <p class="font-medium">{{ $viewingStockOut->reason }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-500 flex items-center">
                                    <i class="fas fa-sticky-note text-gray-400 mr-1"></i> Notes
                                </p>
                                <p class="font-medium">{{ $viewingStockOut->notes ?: 'No notes provided' }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-500 flex items-center">
                                    <i class="fas fa-user text-gray-400 mr-1"></i> Created By
                                </p>
                                <p class="font-medium">{{ $viewingStockOut->user ? $viewingStockOut->user->name : 'System' }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-500 flex items-center">
                                    <i class="fas fa-clock text-gray-400 mr-1"></i> Created At
                                </p>
                                <p class="font-medium">{{ $viewingStockOut->created_at->format('d M Y, h:i A') }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Items Information -->
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                        <div class="bg-blue-50 px-4 py-2 rounded-t-lg flex items-center">
                            <i class="fas fa-tools text-blue-500 mr-2"></i>
                            <h4 class="font-medium text-gray-700">Items Information</h4>
                        </div>
                        <div class="p-4">
                            <div class="overflow-y-auto max-h-[300px] border border-gray-200 rounded-md">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50 sticky top-0">
                                        <tr>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Part</th>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID/BAC</th>
                                            <th scope="col" class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($viewingStockOut->items as $item)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-2 text-sm">
                                                {{ $item->equipmentPart->name }}
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
                                        @empty
                                        <tr>
                                            <td colspan="3" class="px-3 py-4 text-sm text-gray-500 text-center">
                                                No items found for this stock out record.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Summary -->
                            <div class="mt-4 p-3 bg-gray-50 rounded-md border border-gray-200">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-700">Total Items:</span>
                                    <span class="text-sm font-bold">{{ $viewingStockOut->items->count() }}</span>
                                </div>
                                <div class="flex justify-between items-center mt-1">
                                    <span class="text-sm font-medium text-gray-700">Total Quantity:</span>
                                    <span class="text-sm font-bold">{{ $viewingStockOut->items->sum('quantity') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3 border-t border-gray-200 pt-4">
                    <button 
                        wire:click="closeViewModal" 
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150 flex items-center"
                    >
                        <i class="fas fa-times mr-2"></i> Close
                    </button>
                    <button 
                        wire:click="editStockOut({{ $viewingStockOut->id }})" 
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150 flex items-center"
                    >
                        <i class="fas fa-edit mr-2"></i> Edit
                    </button>
                    <button 
                        wire:click="confirmDelete({{ $viewingStockOut->id }})" 
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-150 flex items-center"
                    >
                        <i class="fas fa-trash-alt mr-2"></i> Delete
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
