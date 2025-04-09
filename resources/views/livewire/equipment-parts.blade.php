<div>
    <div class="py-4">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-cogs mr-3 text-gray-700"></i> Equipment Parts Management
                </h1>
                <button
                    wire:click="openCreateModal"
                    type="button"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded flex items-center transition-colors duration-150"
                >
                    <i class="fas fa-plus-circle mr-2"></i>
                    Add Part
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
                                    placeholder="Search parts..."
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
                                    id="perPage"
                                    wire:model.live="perPage"
                                    class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                >
                                    <option value="10">10 per page</option>
                                    <option value="25">25 per page</option>
                                    <option value="50">50 per page</option>
                                    <option value="100">100 per page</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end mt-3">
                        <button
                            wire:click="clearFilters"
                            class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            <i class="fas fa-times-circle mr-1"></i> Clear Filters
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('name')">
                                    <div class="flex items-center space-x-1">
                                        <i class="fas fa-tag text-gray-400 mr-1"></i>
                                        <span>Name</span>
                                        @if($sortField === 'name')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                        @else
                                            <i class="fas fa-sort text-gray-300"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('part_number')">
                                    <div class="flex items-center space-x-1">
                                        <i class="fas fa-hashtag text-gray-400 mr-1"></i>
                                        <span>Part Number</span>
                                        @if($sortField === 'part_number')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                        @else
                                            <i class="fas fa-sort text-gray-300"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('equipment.name')">
                                    <div class="flex items-center space-x-1">
                                        <i class="fas fa-tools text-gray-400 mr-1"></i>
                                        <span>Equipment</span>
                                        @if($sortField === 'equipment.name')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                        @else
                                            <i class="fas fa-sort text-gray-300"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('stock_quantity')">
                                    <div class="flex items-center space-x-1">
                                        <i class="fas fa-boxes text-gray-400 mr-1"></i>
                                        <span>Stock</span>
                                        @if($sortField === 'stock_quantity')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                        @else
                                            <i class="fas fa-sort text-gray-300"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('minimum_stock_level')">
                                    <div class="flex items-center space-x-1">
                                        <i class="fas fa-level-down-alt text-gray-400 mr-1"></i>
                                        <span>Min. Level</span>
                                        @if($sortField === 'minimum_stock_level')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                        @else
                                            <i class="fas fa-sort text-gray-300"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('last_restock_date')">
                                    <div class="flex items-center space-x-1">
                                        <i class="fas fa-calendar-check text-gray-400 mr-1"></i>
                                        <span>Last Restock</span>
                                        @if($sortField === 'last_restock_date')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                        @else
                                            <i class="fas fa-sort text-gray-300"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center justify-end space-x-1">
                                        <i class="fas fa-cog text-gray-400 mr-1"></i>
                                        <span>Actions</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($this->parts as $part)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $part->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $part->part_number ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $part->equipment->name ?? 'Not assigned' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if($part->stock_quantity <= $part->minimum_stock_level)
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    <i class="fas fa-exclamation-circle mr-1"></i> {{ $part->stock_quantity }}
                                                </span>
                                            @else
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    <i class="fas fa-check-circle mr-1"></i> {{ $part->stock_quantity }}
                                                </span>
                                            @endif
                                           <!-- <div class="ml-2">
                                                <button
                                                    wire:click="updateStock({{ $part->id }}, 1)"
                                                    class="w-7 h-7 text-green-600 hover:text-green-900 hover:bg-green-100 rounded-full flex items-center justify-center transition-colors duration-150"
                                                    title="Increase Stock"
                                                >
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                                <button
                                                    wire:click="updateStock({{ $part->id }}, -1)"
                                                    class="w-7 h-7 text-red-600 hover:text-red-900 hover:bg-red-100 rounded-full flex items-center justify-center transition-colors duration-150"
                                                    title="Decrease Stock"
                                                >
                                                    <i class="fas fa-minus"></i>
                                                </button> -->
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $part->minimum_stock_level }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                        <div class="flex items-center">
                                            <i class="far fa-calendar-alt mr-2 text-gray-400"></i>
                                            {{ $part->last_restock_date ? date('M d, Y', strtotime($part->last_restock_date)) : 'Never' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <div class="flex justify-end space-x-2">
                                            <button
                                                wire:click="viewPart({{ $part->id }})"
                                                class="text-blue-600 hover:text-blue-900 w-8 h-8 rounded-full flex items-center justify-center hover:bg-blue-100"
                                                title="View Details"
                                            >
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button
                                                wire:click="editPart({{ $part->id }})"
                                                class="text-indigo-600 hover:text-indigo-900 w-8 h-8 rounded-full flex items-center justify-center hover:bg-indigo-100"
                                                title="Edit"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button
                                                wire:click="confirmDelete({{ $part->id }})"
                                                class="text-red-600 hover:text-red-900 w-8 h-8 rounded-full flex items-center justify-center hover:bg-red-100"
                                                title="Delete"
                                            >
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center py-8">
                                            <div class="bg-gray-100 rounded-full p-3 mb-4">
                                                <i class="fas fa-tools text-gray-400 text-4xl"></i>
                                            </div>
                                            <p class="text-lg font-medium">No parts found</p>
                                            <p class="text-sm text-gray-500 mt-1 flex items-center">
                                                @if($search || $equipmentId)
                                                    <i class="fas fa-filter mr-1"></i> Try adjusting your search filters or
                                                    <button
                                                        wire:click="clearFilters"
                                                        class="ml-1 text-blue-600 hover:text-blue-800 underline flex items-center"
                                                    >
                                                        <i class="fas fa-times-circle mr-1"></i> clear all filters
                                                    </button>
                                                @else
                                                    <i class="fas fa-info-circle mr-1"></i> Click "Add Part" to create your first part
                                                @endif
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $this->parts->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- View Part Modal -->
    @if($showViewModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl p-6 overflow-y-auto max-h-[90vh]">
            <!-- Enhanced Header with Icon -->
            <div class="flex justify-between items-center mb-6 border-b border-gray-200 pb-4">
                <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                    <span class="bg-blue-100 text-blue-600 p-2 rounded-full mr-3">
                        <i class="fas fa-cog text-lg"></i>
                    </span>
                    Part Details
                </h3>
                <div class="flex items-center space-x-2">
                    <button
                        type="button"
                        class="bg-indigo-100 text-indigo-600 hover:bg-indigo-200 transition-colors duration-150 p-2 rounded-full"
                        wire:click="editPart({{ $viewingPart->id }})"
                        title="Edit Part"
                    >
                        <i class="fas fa-edit"></i>
                    </button>
                    <button
                        type="button"
                        class="bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors duration-150 p-2 rounded-full"
                        wire:click="closeViewModal"
                        title="Close"
                    >
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Part Summary Card -->
            <div class="bg-gray-50 rounded-lg mb-6 shadow-sm">
                <div class="p-4 border-b border-gray-200">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-center">
                        <div class="flex items-center mb-3 md:mb-0">
                            <span class="bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-sm flex items-center">
                                <i class="fas fa-hashtag mr-1"></i>
                                {{ $viewingPart->part_number ?? 'No Part Number' }}
                            </span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="far fa-calendar-alt mr-2"></i>
                            <span>Last Updated: {{ $viewingPart->updated_at->format('Y-m-d g:i A') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Stock Status -->
                <div class="p-4">
                    <div class="flex items-start mb-4">
                        <span class="bg-blue-50 p-2 rounded-full text-blue-600 mr-3">
                            <i class="fas fa-tag"></i>
                        </span>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Part Name</p>
                            <p class="text-sm font-medium">{{ $viewingPart->name }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="flex items-start">
                            <span class="bg-blue-50 p-2 rounded-full text-blue-600 mr-3">
                                <i class="fas fa-tools"></i>
                            </span>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Equipment</p>
                                <p class="text-sm font-medium">{{ $viewingPart->equipment->name ?? 'Not assigned' }}</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <span class="bg-{{ $viewingPart->stock_quantity <= $viewingPart->minimum_stock_level ? 'red' : 'green' }}-50 p-2 rounded-full text-{{ $viewingPart->stock_quantity <= $viewingPart->minimum_stock_level ? 'red' : 'green' }}-600 mr-3">
                                <i class="fas fa-boxes"></i>
                            </span>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Stock Quantity</p>
                                <p class="text-sm font-medium flex items-center">
                                    {{ $viewingPart->stock_quantity }}
                                    @if($viewingPart->stock_quantity <= $viewingPart->minimum_stock_level)
                                        <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-800">Low Stock</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <span class="bg-blue-50 p-2 rounded-full text-blue-600 mr-3">
                                <i class="fas fa-level-down-alt"></i>
                            </span>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Minimum Level</p>
                                <p class="text-sm font-medium">{{ $viewingPart->minimum_stock_level }}</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <span class="bg-blue-50 p-2 rounded-full text-blue-600 mr-3">
                                <i class="fas fa-dollar-sign"></i>
                            </span>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Unit Cost</p>
                                <p class="text-sm font-medium">${{ number_format($viewingPart->unit_cost, 2) }}</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <span class="bg-blue-50 p-2 rounded-full text-blue-600 mr-3">
                                <i class="fas fa-calendar-check"></i>
                            </span>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Last Restock</p>
                                <p class="text-sm font-medium">
                                    {{ $viewingPart->last_restock_date ? date('M d, Y', strtotime($viewingPart->last_restock_date)) : 'Never' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description Section -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm mb-6">
                <div class="bg-gray-50 px-4 py-2 rounded-t-lg flex items-center">
                    <i class="fas fa-align-left text-gray-600 mr-2"></i>
                    <h4 class="text-sm font-semibold text-gray-700">Description</h4>
                </div>
                <div class="p-4">
                    @if($viewingPart->description)
                        <p class="text-sm leading-relaxed">{{ $viewingPart->description }}</p>
                    @else
                        <div class="flex items-center justify-center h-12 text-gray-400">
                            <i class="fas fa-file-alt mr-2"></i>
                            <span>No description provided</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Footer Action Buttons -->
            <div class="flex justify-end space-x-3 border-t border-gray-200 pt-4">
                <button
                    type="button"
                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150 flex items-center"
                    wire:click="closeViewModal"
                >
                    <i class="fas fa-times mr-2"></i> Close
                </button>
                <button
                    type="button"
                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150 flex items-center"
                    wire:click="editPart({{ $viewingPart->id }})"
                >
                    <i class="fas fa-edit mr-2"></i> Edit Part
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center">
            <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all w-full max-w-2xl">
                <!-- Modal header -->
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <span class="bg-indigo-100 text-indigo-600 p-2 rounded-full mr-3">
                            <i class="fas {{ $isEditing ? 'fa-edit' : 'fa-plus-circle' }} text-lg"></i>
                        </span>
                        {{ $isEditing ? 'Edit Part' : 'Add New Part' }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Modal body -->
                <div class="px-6 py-4">
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

                    <form wire:submit.prevent="savePart">
                        <div class="bg-gray-50 p-4 rounded-md mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                                <i class="fas fa-info-circle mr-2 text-blue-500"></i> Part Information
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="part-name" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                        <i class="fas fa-tag mr-1 text-gray-500"></i> Part Name <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative rounded-md shadow-sm">
                                        <input
                                            type="text"
                                            id="part-name"
                                            wire:model.live="part.name"
                                            placeholder="Enter part name"
                                            class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150 ease-in-out @error('part.name') border-red-300 text-red-900 placeholder-red-300 @enderror"
                                        >
                                        @error('part.name')
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                            </div>
                                        @enderror
                                    </div>
                                    @error('part.name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="part-number" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                        <i class="fas fa-hashtag mr-1 text-gray-500"></i> Part Number
                                    </label>
                                    <input
                                        type="text"
                                        id="part-number"
                                        wire:model.live="part.part_number"
                                        placeholder="Enter part number (optional)"
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    >
                                </div>

                                <div>
                                    <label for="barcode" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                        <i class="fas fa-barcode mr-1 text-gray-500"></i> Barcode
                                    </label>
                                    <input
                                        type="text"
                                        id="barcode"
                                        wire:model.live="part.bar_code"
                                        placeholder="Enter barcode (optional)"
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    >
                                </div>

                                <div>
                                    <label for="equipment-id" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                        <i class="fas fa-tools mr-1 text-gray-500"></i> Equipment <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative rounded-md shadow-sm">
                                        <select
                                            id="equipment-id"
                                            wire:model.live="part.maintenance_equipment_id"
                                            class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('part.maintenance_equipment_id') border-red-300 text-red-900 @enderror"
                                        >
                                            <option value="">Select Equipment</option>
                                            @foreach($this->equipmentList as $equipment)
                                                <option value="{{ $equipment->id }}">{{ $equipment->name }} - {{ $equipment->serial_number }}</option>
                                            @endforeach
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                        </div>
                                        
                                        @error('part.maintenance_equipment_id')
                                            <div class="absolute inset-y-0 right-8 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                            </div>
                                        @enderror
                                    </div>
                                    @error('part.maintenance_equipment_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-md mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                                <i class="fas fa-boxes mr-2 text-blue-500"></i> Stock Information
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="stock-quantity" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                        <i class="fas fa-boxes mr-1 text-gray-500"></i> Stock Quantity <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative rounded-md shadow-sm">
                                        <input
                                            type="number"
                                            id="stock-quantity"
                                            wire:model.live="part.stock_quantity"
                                            min="0"
                                            placeholder="Enter quantity"
                                            class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('part.stock_quantity') border-red-300 text-red-900 placeholder-red-300 @enderror"
                                        >
                                        @error('part.stock_quantity')
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                            </div>
                                        @enderror
                                    </div>
                                    @error('part.stock_quantity') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="minimum-stock-level" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                        <i class="fas fa-level-down-alt mr-1 text-gray-500"></i> Minimum Stock Level <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative rounded-md shadow-sm">
                                        <input
                                            type="number"
                                            id="minimum-stock-level"
                                            wire:model.live="part.minimum_stock_level"
                                            min="0"
                                            placeholder="Enter min level"
                                            class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('part.minimum_stock_level') border-red-300 text-red-900 placeholder-red-300 @enderror"
                                        >
                                        @error('part.minimum_stock_level')
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                            </div>
                                        @enderror
                                    </div>
                                    @error('part.minimum_stock_level') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
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
                                            type="number"
                                            id="unit-cost"
                                            wire:model.live="part.unit_cost"
                                            min="0"
                                            step="0.01"
                                            placeholder="0.00"
                                            class="pl-7 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-md mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                                <i class="fas fa-align-left mr-2 text-blue-500"></i> Description
                            </h4>
                            <div>
                                <label for="description" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-align-left mr-1 text-gray-500"></i> Description
                                </label>
                                <textarea
                                    id="description"
                                    wire:model.live="part.description"
                                    rows="3"
                                    placeholder="Enter additional details about this part (optional)"
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                ></textarea>
                            </div>
                        </div>

                        <!-- Modal footer -->
                        <div class="mt-5 flex justify-end space-x-3 border-t border-gray-200 pt-4">
                            <button
                                type="button"
                                wire:click="closeModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <i class="fas fa-times mr-2"></i> Cancel
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <i class="fas {{ $isEditing ? 'fa-save' : 'fa-plus' }} mr-2"></i>
                                <span>{{ $isEditing ? 'Update' : 'Save' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center">
            <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all w-full max-w-md">
                <div class="bg-red-50 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-red-100 rounded-full p-3">
                            <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                        </div>
                        <h3 class="ml-3 text-lg font-medium text-gray-900">Confirm Deletion</h3>
                    </div>
                </div>

                <div class="bg-white px-6 py-4">
                    <p class="text-sm text-gray-500">
                        Are you sure you want to delete this part? This action cannot be undone and may affect maintenance records.
                    </p>

                    <div class="mt-4 bg-gray-50 p-3 rounded-md border border-gray-200">
                        <div class="flex items-center text-sm">
                            <i class="fas fa-exclamation-circle text-amber-500 mr-2"></i>
                            <span class="font-medium">Warning:</span>
                            <span class="ml-1">Deleting this part will remove all associated stock history.</span>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-3 flex justify-end border-t border-gray-200">
                    <button
                        type="button"
                        wire:click="closeModal"
                        class="mr-3 inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>
                    <button
                        type="button"
                        wire:click="deletePart"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                    >
                        <i class="fas fa-trash-alt mr-2"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
