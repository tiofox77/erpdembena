<div>
    <div class="container mx-auto py-6 px-4">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Equipment Parts Management</h1>
            <p class="text-gray-500 mt-1">Manage and track all equipment parts in the inventory</p>
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
                            placeholder="Search by name, part number, BAC code..."
                        >
                    </div>
                </div>

                <!-- Equipment Filter -->
                <div class="md:col-span-2">
                    <label for="equipmentFilter" class="block text-xs font-medium text-gray-700 mb-1">Filter by Equipment</label>
                    <select
                        wire:model.live="equipmentId"
                        id="equipmentFilter"
                        class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                    >
                        <option value="">All Equipment</option>
                        @foreach($this->equipmentList as $equipment)
                            <option value="{{ $equipment->id }}">{{ $equipment->name }}</option>
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
                    Showing <span class="font-medium">{{ $this->parts->firstItem() ?? 0 }}</span> to 
                    <span class="font-medium">{{ $this->parts->lastItem() ?? 0 }}</span> of 
                    <span class="font-medium">{{ $this->parts->total() }}</span> parts
                </span>
            </div>
            <div>
                <button
                    wire:click="openCreateModal"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    <i class="fas fa-plus mr-2"></i> Add New Part
                </button>
            </div>
        </div>

        <!-- Parts Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('name')">
                            <div class="flex items-center space-x-1">
                                <span>Name</span>
                                @if ($sortField === 'name')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                @else
                                    <i class="fas fa-sort text-gray-400"></i>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('part_number')">
                            <div class="flex items-center space-x-1">
                                <span>Part Number</span>
                                @if ($sortField === 'part_number')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                @else
                                    <i class="fas fa-sort text-gray-400"></i>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('bac_code')">
                            <div class="flex items-center space-x-1">
                                <span>BAC Code</span>
                                @if ($sortField === 'bac_code')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                @else
                                    <i class="fas fa-sort text-gray-400"></i>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('stock_quantity')">
                            <div class="flex items-center space-x-1">
                                <span>Stock</span>
                                @if ($sortField === 'stock_quantity')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                @else
                                    <i class="fas fa-sort text-gray-400"></i>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Equipment</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($this->parts as $part)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $part->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $part->part_number ?: 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $part->bac_code ?: 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <!-- Stock level indicator -->
                            <div class="flex items-center space-x-2">
                                @if($part->stock_quantity > $part->minimum_stock_level)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ $part->stock_quantity }}
                                    </span>
                                @elseif($part->stock_quantity > 0)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        {{ $part->stock_quantity }}
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Out of Stock
                                    </span>
                                @endif
                                
                                <!-- Stock adjustment buttons -->
                                <button wire:click="updateStock({{ $part->id }}, 1)" title="Add 1 to stock"
                                    class="text-blue-600 hover:text-blue-900 text-xs">
                                    <i class="fas fa-plus-circle"></i>
                                </button>
                                
                                <button wire:click="updateStock({{ $part->id }}, -1)" title="Remove 1 from stock"
                                    class="text-red-600 hover:text-red-900 text-xs" {{ $part->stock_quantity < 1 ? 'disabled' : '' }}>
                                    <i class="fas fa-minus-circle"></i>
                                </button>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $part->equipment->name ?? 'Not assigned' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <button wire:click="viewPart({{ $part->id }})" 
                                    class="text-blue-600 hover:text-blue-900 w-8 h-8 rounded-full flex items-center justify-center hover:bg-blue-100"
                                    title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button wire:click="editPart({{ $part->id }})" 
                                    class="text-indigo-600 hover:text-indigo-900 w-8 h-8 rounded-full flex items-center justify-center hover:bg-indigo-100"
                                    title="Edit Part">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button wire:click="confirmDelete({{ $part->id }})" 
                                    class="text-red-600 hover:text-red-900 w-8 h-8 rounded-full flex items-center justify-center hover:bg-red-100"
                                    title="Delete Part">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            No parts found. <button wire:click="openCreateModal" class="text-blue-600 hover:text-blue-900">Add one now</button>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $this->parts->links() }}
        </div>

        <!-- Create/Edit Modal -->
        @if($showModal)
        <div
            class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50"
            x-data="{}"
        >
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 overflow-y-auto max-h-[90vh]">
                <!-- Modal Header -->
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">
                        {{ $isEditing ? 'Edit Part' : 'Add New Part' }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Form -->
                <form wire:submit.prevent="savePart">
                    <div class="space-y-4">
                        <!-- Part Name -->
                        <div>
                            <label for="part-name" class="block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
                            <input 
                                wire:model="part.name" 
                                type="text" 
                                id="part-name" 
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" 
                                placeholder="Enter part name"
                            >
                            @error('part.name') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- Part Number -->
                        <div>
                            <label for="part-number" class="block text-sm font-medium text-gray-700">Part Number</label>
                            <input 
                                wire:model="part.part_number" 
                                type="text" 
                                id="part-number" 
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" 
                                placeholder="Enter part number"
                            >
                            @error('part.part_number') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- BAC Code -->
                        <div>
                            <label for="bac-code" class="block text-sm font-medium text-gray-700">BAC Code</label>
                            <input 
                                wire:model="part.bac_code" 
                                type="text" 
                                id="bac-code" 
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" 
                                placeholder="Enter BAC code"
                            >
                            @error('part.bac_code') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea 
                                wire:model="part.description" 
                                id="description" 
                                rows="3" 
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" 
                                placeholder="Enter part description"
                            ></textarea>
                            @error('part.description') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- Equipment Selection -->
                        <div>
                            <label for="equipment" class="block text-sm font-medium text-gray-700">Equipment <span class="text-red-500">*</span></label>
                            <select 
                                wire:model="part.maintenance_equipment_id" 
                                id="equipment" 
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                            >
                                <option value="">Select Equipment</option>
                                @foreach($this->equipmentList as $equipment)
                                    <option value="{{ $equipment->id }}">{{ $equipment->name }}</option>
                                @endforeach
                            </select>
                            @error('part.maintenance_equipment_id') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- Stock Quantity -->
                        <div>
                            <label for="stock-quantity" class="block text-sm font-medium text-gray-700">Stock Quantity <span class="text-red-500">*</span></label>
                            <input 
                                wire:model="part.stock_quantity" 
                                type="number" 
                                id="stock-quantity" 
                                min="0" 
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" 
                                placeholder="Enter stock quantity"
                            >
                            @error('part.stock_quantity') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- Minimum Stock Level -->
                        <div>
                            <label for="min-stock" class="block text-sm font-medium text-gray-700">Minimum Stock Level <span class="text-red-500">*</span></label>
                            <input 
                                wire:model="part.minimum_stock_level" 
                                type="number" 
                                id="min-stock" 
                                min="0" 
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" 
                                placeholder="Enter minimum stock level"
                            >
                            @error('part.minimum_stock_level') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- Unit Cost -->
                        <div>
                            <label for="unit-cost" class="block text-sm font-medium text-gray-700">Unit Cost</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input 
                                    wire:model="part.unit_cost" 
                                    type="number" 
                                    id="unit-cost" 
                                    step="0.01" 
                                    min="0" 
                                    class="pl-8 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" 
                                    placeholder="0.00"
                                >
                            </div>
                            @error('part.unit_cost') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Modal Footer with Actions -->
                    <div class="mt-6 flex justify-end space-x-3">
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
                            {{ $isEditing ? 'Update Part' : 'Add Part' }}
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
                    Are you sure you want to delete this part? This action cannot be undone.
                </p>
                <div class="flex justify-end space-x-3">
                    <button 
                        wire:click="closeModal" 
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Cancel
                    </button>
                    <button 
                        wire:click="deletePart" 
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
        @endif

        <!-- View Modal -->
        @if($showViewModal && $viewingPart)
        <div
            class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50"
        >
            <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 overflow-y-auto max-h-[90vh]">
                <!-- Modal Header -->
                <div class="flex justify-between items-center mb-4 border-b pb-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-cogs text-blue-500 mr-2"></i>
                        Part Details
                    </h3>
                    <button wire:click="closeViewModal" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Basic Information -->
                    <div class="space-y-4">
                        <h4 class="font-medium text-gray-700">Basic Information</h4>
                        
                        <div>
                            <p class="text-sm text-gray-500">Name</p>
                            <p class="font-medium">{{ $viewingPart->name }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500">Part Number</p>
                            <p class="font-medium">{{ $viewingPart->part_number ?: 'Not specified' }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500">BAC Code</p>
                            <p class="font-medium">{{ $viewingPart->bac_code ?: 'Not specified' }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500">Description</p>
                            <p class="font-medium">{{ $viewingPart->description ?: 'No description provided' }}</p>
                        </div>
                    </div>

                    <!-- Stock Information -->
                    <div class="space-y-4">
                        <h4 class="font-medium text-gray-700">Stock Information</h4>
                        
                        <div>
                            <p class="text-sm text-gray-500">Current Stock</p>
                            <div class="flex items-center">
                                @if($viewingPart->stock_quantity > $viewingPart->minimum_stock_level)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 mr-2">
                                        {{ $viewingPart->stock_quantity }}
                                    </span>
                                    <span class="text-green-600 text-sm">Sufficient</span>
                                @elseif($viewingPart->stock_quantity > 0)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 mr-2">
                                        {{ $viewingPart->stock_quantity }}
                                    </span>
                                    <span class="text-yellow-600 text-sm">Low Stock</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 mr-2">
                                        0
                                    </span>
                                    <span class="text-red-600 text-sm">Out of Stock</span>
                                @endif
                            </div>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500">Minimum Stock Level</p>
                            <p class="font-medium">{{ $viewingPart->minimum_stock_level }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500">Unit Cost</p>
                            <p class="font-medium">{{ $viewingPart->unit_cost ? '$' . number_format($viewingPart->unit_cost, 2) : 'Not specified' }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500">Last Restocked</p>
                            <p class="font-medium">{{ $viewingPart->last_restock_date ? date('M d, Y', strtotime($viewingPart->last_restock_date)) : 'Never restocked' }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500">Associated Equipment</p>
                            <p class="font-medium">{{ $viewingPart->equipment->name ?? 'Not assigned' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-6 flex justify-end space-x-3 border-t pt-4">
                    <button 
                        wire:click="editPart({{ $viewingPart->id }})" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <i class="fas fa-edit mr-2"></i>
                        Edit Part
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
