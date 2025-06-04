<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold text-gray-800">
                            <i class="fas fa-tools mr-2 text-gray-600"></i>
                            Work Equipment Management
                        </h2>
                        
                        <!-- Tabs -->
                        <div class="flex space-x-4">
                            <button 
                                wire:click="setActiveTab('equipment')" 
                                class="px-4 py-2 {{$activeTab === 'equipment' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'}} rounded-md hover:bg-opacity-90 focus:outline-none"
                            >
                                <i class="fas fa-laptop mr-1"></i>
                                Equipment
                            </button>
                            <button 
                                wire:click="setActiveTab('assignments')" 
                                class="px-4 py-2 {{$activeTab === 'assignments' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'}} rounded-md hover:bg-opacity-90 focus:outline-none"
                            >
                                <i class="fas fa-user-tag mr-1"></i>
                                Assignments
                            </button>
                            <button 
                                wire:click="setActiveTab('maintenance')" 
                                class="px-4 py-2 {{$activeTab === 'maintenance' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'}} rounded-md hover:bg-opacity-90 focus:outline-none"
                            >
                                <i class="fas fa-wrench mr-1"></i>
                                Maintenance
                            </button>
                        </div>
                    </div>

                    <!-- Equipment Tab -->
                    @if($activeTab === 'equipment')
                    <div>
                        <!-- Actions button -->
                        <div class="mb-4">
                            <button
                                wire:click="createEquipment"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                            >
                                <i class="fas fa-plus mr-1"></i>
                                Add Equipment
                            </button>
                        </div>

                        <!-- Filters and Search -->
                        <div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="md:col-span-2">
                                    <label for="searchEquipment" class="sr-only">Search</label>
                                    <div class="relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-search text-gray-400"></i>
                                        </div>
                                        <input
                                            type="text"
                                            id="searchEquipment"
                                            wire:model.live.debounce.300ms="searchEquipment"
                                            placeholder="Search equipment..."
                                            class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 pr-3 py-2 sm:text-sm border-gray-300 rounded-md"
                                        >
                                    </div>
                                </div>

                                <div>
                                    <label for="filterType" class="sr-only">Type</label>
                                    <select
                                        id="filterType"
                                        wire:model.live="filters.equipment_type"
                                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                        <option value="">All Types</option>
                                        <option value="computer">Computer</option>
                                        <option value="phone">Phone</option>
                                        <option value="tool">Tool</option>
                                        <option value="vehicle">Vehicle</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="filterStatus" class="sr-only">Status</label>
                                    <select
                                        id="filterStatus"
                                        wire:model.live="filters.status"
                                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                        <option value="">All Statuses</option>
                                        <option value="available">Available</option>
                                        <option value="assigned">Assigned</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="damaged">Damaged</option>
                                        <option value="disposed">Disposed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-4 flex justify-end">
                                <button
                                    wire:click="resetFilters"
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                                >
                                    <i class="fas fa-redo mr-1"></i>
                                    Reset Filters
                                </button>
                            </div>
                        </div>

                        <!-- Equipment Table -->
                        <div class="overflow-x-auto bg-white rounded-lg shadow overflow-y-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortByEquipment('name')">
                                            <div class="flex items-center">
                                                Name
                                                @if($sortFieldEquipment === 'name')
                                                <i class="fas fa-sort-{{ $sortDirectionEquipment === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                                @else
                                                <i class="fas fa-sort ml-1 text-gray-400"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Type
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Serial Number
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Assigned To
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($equipment as $item)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $item->name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $item->asset_code }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ ucfirst($item->equipment_type) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ $item->serial_number ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $item->status === 'available' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $item->status === 'assigned' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $item->status === 'maintenance' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $item->status === 'damaged' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $item->status === 'disposed' ? 'bg-gray-100 text-gray-800' : '' }}">
                                                {{ ucfirst($item->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($item->status === 'assigned' && $item->currentAssignment)
                                                <div class="text-sm text-gray-900">
                                                    {{ $item->currentAssignment->employee->full_name ?? 'Unknown' }}
                                                </div>
                                            @else
                                                <div class="text-sm text-gray-500">
                                                    Not assigned
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button wire:click="editEquipment({{ $item->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button wire:click="confirmDeleteEquipment({{ $item->id }})" class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                            <div class="flex flex-col items-center justify-center py-6">
                                                <i class="fas fa-tools text-3xl text-gray-400 mb-3"></i>
                                                <p>No equipment found.</p>
                                                <button 
                                                    wire:click="createEquipment"
                                                    class="mt-3 px-4 py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                                >
                                                    Add Equipment
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4">
                            {{ $equipment->links() }}
                        </div>
                    </div>
                    @endif

                    <!-- Equipment Assignment Tab -->
                    @if($activeTab === 'assignments')
                    <div>
                        <!-- Add actions, filters, and table for assignments -->
                        <div class="mb-4">
                            <button
                                wire:click="createAssignment"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                            >
                                <i class="fas fa-user-tag mr-1"></i>
                                New Assignment
                            </button>
                        </div>

                        <!-- Assignments Table -->
                        <!-- Table implementation similar to equipment tab -->
                    </div>
                    @endif

                    <!-- Equipment Maintenance Tab -->
                    @if($activeTab === 'maintenance')
                    <div>
                        <!-- Add actions, filters, and table for maintenance -->
                        <div class="mb-4">
                            <button
                                wire:click="createMaintenance"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                            >
                                <i class="fas fa-wrench mr-1"></i>
                                New Maintenance Record
                            </button>
                        </div>

                        <!-- Maintenance Table -->
                        <!-- Table implementation similar to equipment tab -->
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Equipment Modal -->
    <div x-data="{ show: @entangle('showEquipmentModal') }" x-show="show" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        {{ $isEditing ? 'Edit Equipment' : 'Add New Equipment' }}
                    </h3>
                    <form wire:submit.prevent="saveEquipment">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Equipment Form Fields -->
                            <div class="col-span-2">
                                <label for="name" class="block text-sm font-medium text-gray-700">Equipment Name</label>
                                <input type="text" id="name" wire:model="name" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="equipment_type" class="block text-sm font-medium text-gray-700">Type</label>
                                <select id="equipment_type" wire:model="equipment_type" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="">Select Type</option>
                                    <option value="computer">Computer</option>
                                    <option value="phone">Phone</option>
                                    <option value="tool">Tool</option>
                                    <option value="vehicle">Vehicle</option>
                                    <option value="other">Other</option>
                                </select>
                                @error('equipment_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="asset_code" class="block text-sm font-medium text-gray-700">Asset Code</label>
                                <input type="text" id="asset_code" wire:model="asset_code" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @error('asset_code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="serial_number" class="block text-sm font-medium text-gray-700">Serial Number</label>
                                <input type="text" id="serial_number" wire:model="serial_number" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @error('serial_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="purchase_date" class="block text-sm font-medium text-gray-700">Purchase Date</label>
                                <input type="date" id="purchase_date" wire:model="purchase_date" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @error('purchase_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select id="status" wire:model="status" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="available">Available</option>
                                    <option value="assigned">Assigned</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="damaged">Damaged</option>
                                    <option value="disposed">Disposed</option>
                                </select>
                                @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-span-2">
                                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea id="notes" wire:model="notes" rows="3" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                                @error('notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </form>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="saveEquipment" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Save
                    </button>
                    <button wire:click="closeEquipmentModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-data="{ show: @entangle('showDeleteModal') }" x-show="show" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Delete Confirmation
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you sure you want to delete this {{ $deleteType }} record? This action cannot be undone.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="delete" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Delete
                    </button>
                    <button wire:click="closeDeleteModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
