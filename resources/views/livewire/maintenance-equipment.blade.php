<div>
    <div class="py-4">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-tools mr-3 text-gray-700"></i> Equipment Management
                </h1>
                <button
                    wire:click="openNewEquipmentModal"
                    type="button"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded flex items-center transition-colors duration-150"
                >
                    <i class="fas fa-plus-circle mr-2"></i>
                    Add Equipment
                </button>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                <!-- Filter Section -->
                <div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                    <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-filter mr-2 text-blue-500"></i> Filters and Search
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="search" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                <i class="fas fa-search mr-1 text-gray-500"></i> Search
                            </label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input
                                    wire:model.live="search"
                                    id="search"
                                    type="text"
                                    placeholder="Search equipment..."
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                            </div>
                        </div>

                        <div>
                            <label for="lineFilter" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                <i class="fas fa-project-diagram mr-1 text-gray-500"></i> Line
                            </label>
                            <div class="relative rounded-md shadow-sm">
                                <select
                                    id="lineFilter"
                                    wire:model.live="lineFilter"
                                    class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                                    <option value="">All Lines</option>
                                    @foreach($lines as $line)
                                        <option value="{{ $line->id }}">{{ $line->name }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="areaFilter" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                <i class="fas fa-map-marker-alt mr-1 text-gray-500"></i> Area
                            </label>
                            <div class="relative rounded-md shadow-sm">
                                <select
                                    id="areaFilter"
                                    wire:model.live="areaFilter"
                                    class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                                    <option value="">All Areas</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="statusFilter" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                <i class="fas fa-clipboard-check mr-1 text-gray-500"></i> Status
                            </label>
                            <div class="relative rounded-md shadow-sm">
                                <select
                                    id="statusFilter"
                                    wire:model.live="statusFilter"
                                    class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                                    <option value="">All Statuses</option>
                                    <option value="operational">Operational</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="out_of_service">Out of Service</option>
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
                    <table class="min-w-full table-fixed divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4 cursor-pointer" wire:click="sortBy('name')">
                                    <div class="flex items-center space-x-1">
                                        <i class="fas fa-wrench text-gray-400 mr-1"></i>
                                        <span>Name</span>
                                        @if ($sortField === 'name')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                        @else
                                            <i class="fas fa-sort text-gray-300"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6 cursor-pointer" wire:click="sortBy('serial_number')">
                                    <div class="flex items-center space-x-1">
                                        <i class="fas fa-hashtag text-gray-400 mr-1"></i>
                                        <span>Serial Number</span>
                                        @if ($sortField === 'serial_number')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                        @else
                                            <i class="fas fa-sort text-gray-300"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">
                                    <div class="flex items-center space-x-1">
                                        <i class="fas fa-project-diagram text-gray-400 mr-1"></i>
                                        <span>Line</span>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">
                                    <div class="flex items-center space-x-1">
                                        <i class="fas fa-map-marker-alt text-gray-400 mr-1"></i>
                                        <span>Area</span>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12 cursor-pointer" wire:click="sortBy('status')">
                                    <div class="flex items-center space-x-1">
                                        <i class="fas fa-clipboard-check text-gray-400 mr-1"></i>
                                        <span>Status</span>
                                        @if ($sortField === 'status')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                        @else
                                            <i class="fas fa-sort text-gray-300"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6 cursor-pointer" wire:click="sortBy('next_maintenance')">
                                    <div class="flex items-center space-x-1">
                                        <i class="fas fa-calendar-alt text-gray-400 mr-1"></i>
                                        <span>Next Maintenance</span>
                                        @if ($sortField === 'next_maintenance')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                        @else
                                            <i class="fas fa-sort text-gray-300"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">
                                    <div class="flex items-center justify-end space-x-1">
                                        <i class="fas fa-cog text-gray-400 mr-1"></i>
                                        <span>Actions</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($equipment as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                        {{ $item->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                        {{ $item->serial_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                        {{ $item->line->name ?? 'Not assigned' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                        {{ $item->area->name ?? 'Not assigned' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($item->status === 'operational')
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i> Operational
                                            </span>
                                        @elseif($item->status === 'maintenance')
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-tools mr-1"></i> Maintenance
                                            </span>
                                        @else
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                <i class="fas fa-exclamation-circle mr-1"></i> Out of Service
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                        <div class="flex items-center">
                                            <i class="far fa-calendar-alt mr-2 text-gray-400"></i>
                                            {{ $item->next_maintenance ? date('M d, Y', strtotime($item->next_maintenance)) : 'Not scheduled' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <div class="flex justify-end space-x-2">
                                            <button
                                                wire:click="viewEquipment({{ $item->id }})"
                                                class="text-blue-600 hover:text-blue-900 w-8 h-8 rounded-full flex items-center justify-center hover:bg-blue-100"
                                                title="View Details"
                                            >
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button
                                                wire:click="edit({{ $item->id }})"
                                                class="text-indigo-600 hover:text-indigo-900 w-8 h-8 rounded-full flex items-center justify-center hover:bg-indigo-100"
                                                title="Edit"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button
                                                wire:click="delete({{ $item->id }})"
                                                wire:confirm="Are you sure you want to delete this equipment?"
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
                                            <p class="text-lg font-medium">No equipment found</p>
                                            <p class="text-sm text-gray-500 mt-1 flex items-center">
                                                @if($search || $lineFilter || $areaFilter || $statusFilter)
                                                    <i class="fas fa-filter mr-1"></i> Try adjusting your search filters or
                                                    <button
                                                        wire:click="clearFilters"
                                                        class="ml-1 text-blue-600 hover:text-blue-800 underline flex items-center"
                                                    >
                                                        <i class="fas fa-times-circle mr-1"></i> clear all filters
                                                    </button>
                                                @else
                                                    <i class="fas fa-info-circle mr-1"></i> Click "Add Equipment" to create your first equipment
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
                    {{ $equipment->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- View Equipment Modal -->
    @if($showViewModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl p-6 overflow-y-auto max-h-[90vh]">
            <!-- Enhanced Header with Icon -->
            <div class="flex justify-between items-center mb-6 border-b border-gray-200 pb-4">
                <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                    <span class="bg-blue-100 text-blue-600 p-2 rounded-full mr-3">
                        <i class="fas fa-tools text-lg"></i>
                    </span>
                    Equipment Details
                </h3>
                <div class="flex items-center space-x-2">
                    <button
                        type="button"
                        class="bg-indigo-100 text-indigo-600 hover:bg-indigo-200 transition-colors duration-150 p-2 rounded-full"
                        wire:click="edit({{ $viewingEquipment->id }})"
                        title="Edit Equipment"
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

            <!-- Equipment Summary Card -->
            <div class="bg-gray-50 rounded-lg mb-6 shadow-sm">
                <div class="p-4 border-b border-gray-200">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-center">
                        <div class="flex items-center mb-3 md:mb-0">
                            <span class="bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-sm flex items-center">
                                <i class="fas fa-hashtag mr-1"></i>
                                S/N: {{ $viewingEquipment->serial_number }}
                            </span>
                        </div>
                        <div class="flex items-center">
                            @if($viewingEquipment->status === 'operational')
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i> Operational
                                </span>
                            @elseif($viewingEquipment->status === 'maintenance')
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-tools mr-1"></i> Maintenance
                                </span>
                            @else
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    <i class="fas fa-exclamation-circle mr-1"></i> Out of Service
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Equipment Details -->
                <div class="p-4">
                    <div class="flex items-start mb-4">
                        <span class="bg-blue-50 p-2 rounded-full text-blue-600 mr-3">
                            <i class="fas fa-wrench"></i>
                        </span>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Equipment Name</p>
                            <p class="text-sm font-medium">{{ $viewingEquipment->name }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-start">
                            <span class="bg-blue-50 p-2 rounded-full text-blue-600 mr-3">
                                <i class="fas fa-project-diagram"></i>
                            </span>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Line</p>
                                <p class="text-sm font-medium">{{ $viewingEquipment->line->name ?? 'Not assigned' }}</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <span class="bg-blue-50 p-2 rounded-full text-blue-600 mr-3">
                                <i class="fas fa-map-marker-alt"></i>
                            </span>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Area</p>
                                <p class="text-sm font-medium">{{ $viewingEquipment->area->name ?? 'Not assigned' }}</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <span class="bg-blue-50 p-2 rounded-full text-blue-600 mr-3">
                                <i class="fas fa-calendar-plus"></i>
                            </span>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Purchase Date</p>
                                <p class="text-sm font-medium">
                                    {{ $viewingEquipment->purchase_date ? date('M d, Y', strtotime($viewingEquipment->purchase_date)) : 'Not available' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <span class="bg-blue-50 p-2 rounded-full text-blue-600 mr-3">
                                <i class="fas fa-calendar-check"></i>
                            </span>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Last Maintenance</p>
                                <p class="text-sm font-medium">
                                    {{ $viewingEquipment->last_maintenance ? date('M d, Y', strtotime($viewingEquipment->last_maintenance)) : 'Never' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <span class="bg-blue-50 p-2 rounded-full text-blue-600 mr-3">
                                <i class="fas fa-calendar-alt"></i>
                            </span>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Next Maintenance</p>
                                <p class="text-sm font-medium">
                                    {{ $viewingEquipment->next_maintenance ? date('M d, Y', strtotime($viewingEquipment->next_maintenance)) : 'Not scheduled' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes Section -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm mb-6">
                <div class="bg-gray-50 px-4 py-2 rounded-t-lg flex items-center">
                    <i class="fas fa-sticky-note text-gray-600 mr-2"></i>
                    <h4 class="text-sm font-semibold text-gray-700">Notes</h4>
                </div>
                <div class="p-4">
                    @if($viewingEquipment->notes)
                        <p class="text-sm leading-relaxed">{{ $viewingEquipment->notes }}</p>
                    @else
                        <div class="flex items-center justify-center h-12 text-gray-400">
                            <i class="fas fa-file-alt mr-2"></i>
                            <span>No notes available</span>
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
                    wire:click="edit({{ $viewingEquipment->id }})"
                >
                    <i class="fas fa-edit mr-2"></i> Edit Equipment
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal for Creating/Editing -->
    @if($isModalOpen)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center">
            <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all w-full max-w-2xl">
                <!-- Modal header -->
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <span class="bg-indigo-100 text-indigo-600 p-2 rounded-full mr-3">
                            <i class="fas {{ $equipmentId ? 'fa-edit' : 'fa-plus-circle' }} text-lg"></i>
                        </span>
                        {{ $equipmentId ? 'Edit Equipment' : 'Add New Equipment' }}
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

                    <form wire:submit.prevent="save">
                        <div class="bg-gray-50 p-4 rounded-md mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                                <i class="fas fa-info-circle mr-2 text-blue-500"></i> Equipment Information
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="name" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                        <i class="fas fa-wrench mr-1 text-gray-500"></i> Equipment Name <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative rounded-md shadow-sm">
                                        <input
                                            type="text"
                                            id="name"
                                            wire:model="name"
                                            placeholder="Enter equipment name"
                                            class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150 ease-in-out @error('name') border-red-300 text-red-900 placeholder-red-300 @enderror"
                                        >
                                        @error('name')
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                            </div>
                                        @enderror
                                    </div>
                                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="serial_number" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                        <i class="fas fa-hashtag mr-1 text-gray-500"></i> Serial Number <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative rounded-md shadow-sm">
                                        <input
                                            type="text"
                                            id="serial_number"
                                            wire:model="serial_number"
                                            placeholder="Enter serial number"
                                            class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150 ease-in-out @error('serial_number') border-red-300 text-red-900 placeholder-red-300 @enderror"
                                        >
                                        @error('serial_number')
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                            </div>
                                        @enderror
                                    </div>
                                    @error('serial_number') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="line_id" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                        <i class="fas fa-project-diagram mr-1 text-gray-500"></i> Line
                                    </label>
                                    <div class="relative rounded-md shadow-sm">
                                        <select
                                            id="line_id"
                                            wire:model="line_id"
                                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150 ease-in-out @error('line_id') border-red-300 text-red-900 @enderror"
                                        >
                                            <option value="">Select Line</option>
                                            @foreach($lines as $line)
                                                <option value="{{ $line->id }}">{{ $line->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                        </div>
                                        @error('line_id')
                                            <div class="absolute inset-y-0 right-8 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                            </div>
                                        @enderror
                                    </div>
                                    @error('line_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="area_id" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                        <i class="fas fa-map-marker-alt mr-1 text-gray-500"></i> Area
                                    </label>
                                    <div class="relative rounded-md shadow-sm">
                                        <select
                                            id="area_id"
                                            wire:model="area_id"
                                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150 ease-in-out @error('area_id') border-red-300 text-red-900 @enderror"
                                        >
                                            <option value="">Select Area</option>
                                            @foreach($areas as $area)
                                                <option value="{{ $area->id }}">{{ $area->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                        </div>
                                        @error('area_id')
                                            <div class="absolute inset-y-0 right-8 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                            </div>
                                        @enderror
                                    </div>
                                    @error('area_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-md mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                                <i class="fas fa-clipboard-check mr-2 text-blue-500"></i> Status and Maintenance
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="status" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                        <i class="fas fa-clipboard-check mr-1 text-gray-500"></i> Status <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative rounded-md shadow-sm">
                                        <select
                                            id="status"
                                            wire:model="status"
                                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150 ease-in-out @error('status') border-red-300 text-red-900 @enderror"
                                        >
                                            <option value="operational">Operational</option>
                                            <option value="maintenance">Maintenance</option>
                                            <option value="out_of_service">Out of Service</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                        </div>
                                        @error('status')
                                            <div class="absolute inset-y-0 right-8 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                            </div>
                                        @enderror
                                    </div>
                                    @error('status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="purchase_date" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                        <i class="fas fa-calendar-plus mr-1 text-gray-500"></i> Purchase Date
                                    </label>
                                    <div class="relative rounded-md shadow-sm">
                                        <input
                                            type="date"
                                            id="purchase_date"
                                            wire:model="purchase_date"
                                            class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150 ease-in-out"
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
                                <i class="fas fa-sticky-note mr-2 text-blue-500"></i> Additional Information
                            </h4>
                            <div>
                                <label for="notes" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-sticky-note mr-1 text-gray-500"></i> Notes
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <textarea
                                        id="notes"
                                        wire:model="notes"
                                        rows="3"
                                        placeholder="Enter additional notes or information about this equipment"
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150 ease-in-out"
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Modal footer -->
                        <div class="mt-5 flex justify-end space-x-3 border-t border-gray-200 pt-4">
                            <button
                                type="button"
                                wire:click="closeModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150 flex items-center"
                            >
                                <i class="fas fa-times mr-2"></i> Cancel
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150 flex items-center"
                                wire:loading.class="opacity-75 cursor-wait"
                            >
                                <i class="fas {{ $equipmentId ? 'fa-save' : 'fa-plus' }} mr-2"></i>
                                <span wire:loading.remove>{{ $equipmentId ? 'Update' : 'Save' }}</span>
                                <span wire:loading><i class="fas fa-spinner fa-spin mr-1"></i> Processing...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif


</div>
