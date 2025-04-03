<div>
    <div class="p-4 bg-white rounded-lg shadow-md">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold">Work Equipment Management</h2>
        </div>

        <!-- Tabs usando apenas Livewire -->
        <div class="mb-4 border-b border-gray-200">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                <li class="mr-2">
                    <button wire:click="setActiveTab('equipment')" 
                        class="inline-block p-4 rounded-t-lg hover:text-gray-600 hover:border-gray-300 {{ $activeTab === 'equipment' ? 'border-b-2 border-blue-500 text-blue-600' : '' }}">
                        Equipment
                    </button>
                </li>
                <li class="mr-2">
                    <button wire:click="setActiveTab('assignments')" 
                        class="inline-block p-4 rounded-t-lg hover:text-gray-600 hover:border-gray-300 {{ $activeTab === 'assignments' ? 'border-b-2 border-blue-500 text-blue-600' : '' }}">
                        Assignments
                    </button>
                </li>
                <li class="mr-2">
                    <button wire:click="setActiveTab('maintenance')" 
                        class="inline-block p-4 rounded-t-lg hover:text-gray-600 hover:border-gray-300 {{ $activeTab === 'maintenance' ? 'border-b-2 border-blue-500 text-blue-600' : '' }}">
                        Maintenance
                    </button>
                </li>
            </ul>
        </div>

        <!-- Equipment Tab -->
        <div class="{{ $activeTab === 'equipment' ? 'block' : 'hidden' }}">
            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center">
                    <div class="relative">
                        <input type="text" 
                            wire:model.debounce.300ms="searchEquipment" 
                            class="pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Search equipment...">
                        <div class="absolute left-3 top-2.5 text-gray-400">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>
                    <div class="ml-2">
                        <button wire:click="resetFilters" class="px-3 py-2 text-sm text-gray-600 hover:text-blue-600">
                            <i class="fas fa-undo mr-1"></i> Reset
                        </button>
                    </div>
                </div>
                <button wire:click="createEquipment" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    <i class="fas fa-plus-circle mr-2"></i> Add Equipment
                </button>
            </div>

            <!-- Equipment Table -->
            <div class="overflow-x-auto mt-4">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortByEquipment('id')">
                                ID
                                @if($sortFieldEquipment === 'id')
                                    <i class="fas fa-sort-{{ $sortDirectionEquipment === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @else
                                    <i class="fas fa-sort ml-1"></i>
                                @endif
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortByEquipment('name')">
                                Name
                                @if($sortFieldEquipment === 'name')
                                    <i class="fas fa-sort-{{ $sortDirectionEquipment === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @else
                                    <i class="fas fa-sort ml-1"></i>
                                @endif
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortByEquipment('type')">
                                Type
                                @if($sortFieldEquipment === 'type')
                                    <i class="fas fa-sort-{{ $sortDirectionEquipment === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @else
                                    <i class="fas fa-sort ml-1"></i>
                                @endif
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortByEquipment('serial_number')">
                                Serial Number
                                @if($sortFieldEquipment === 'serial_number')
                                    <i class="fas fa-sort-{{ $sortDirectionEquipment === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @else
                                    <i class="fas fa-sort ml-1"></i>
                                @endif
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortByEquipment('status')">
                                Status
                                @if($sortFieldEquipment === 'status')
                                    <i class="fas fa-sort-{{ $sortDirectionEquipment === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @else
                                    <i class="fas fa-sort ml-1"></i>
                                @endif
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($equipment as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->type }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->serial_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $item->status === 'available' ? 'bg-green-100 text-green-800' : 
                                        ($item->status === 'assigned' ? 'bg-blue-100 text-blue-800' : 
                                        ($item->status === 'maintenance' ? 'bg-yellow-100 text-yellow-800' : 
                                        'bg-red-100 text-red-800')) }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <button wire:click="edit({{ $item->id }})" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button wire:click="view({{ $item->id }})" class="text-green-600 hover:text-green-900">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button wire:click="confirmDelete({{ $item->id }})" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    No equipment found.
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

        <!-- Assignments Tab -->
        <div class="{{ $activeTab === 'assignments' ? 'block' : 'hidden' }}">
            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center">
                    <div class="relative">
                        <input type="text" 
                            wire:model.debounce.300ms="searchAssignment" 
                            class="pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Search assignments...">
                        <div class="absolute left-3 top-2.5 text-gray-400">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>
                </div>
                <button wire:click="createAssignment" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    <i class="fas fa-plus-circle mr-2"></i> New Assignment
                </button>
            </div>

            <!-- Assignments Table -->
            <div class="overflow-x-auto mt-4">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Employee
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Equipment
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Assigned Date
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Return Date
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($assignments as $assignment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $assignment->employee->full_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $assignment->equipment->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $assignment->assigned_date ? $assignment->assigned_date->format('d/m/Y') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $assignment->return_date ? $assignment->return_date->format('d/m/Y') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $assignment->status === 'active' ? 'bg-green-100 text-green-800' : 
                                        ($assignment->status === 'returned' ? 'bg-blue-100 text-blue-800' : 
                                        'bg-red-100 text-red-800') }}">
                                        {{ ucfirst($assignment->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <button wire:click="editAssignment({{ $assignment->id }})" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @if($assignment->status === 'active')
                                        <button wire:click="markAsReturned({{ $assignment->id }})" class="text-green-600 hover:text-green-900">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    @endif
                                    <button wire:click="confirmDeleteAssignment({{ $assignment->id }})" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    No assignments found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $assignments->links() }}
            </div>
        </div>

        <!-- Maintenance Tab -->
        <div class="{{ $activeTab === 'maintenance' ? 'block' : 'hidden' }}">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Equipment Maintenance</h3>
                <button wire:click="createMaintenance" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    <i class="fas fa-tools mr-2"></i> Schedule Maintenance
                </button>
            </div>

            <!-- Maintenance Modal -->
            @if($showMaintenanceModal)
            <div class="fixed inset-0 overflow-y-auto z-50" role="dialog">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                                        {{ $isEditing ? 'Edit Maintenance Record' : 'Schedule Maintenance' }}
                                    </h3>
                                    <div class="mt-4 space-y-3">
                                        <div>
                                            <label for="equipment_id_maintenance" class="block text-sm font-medium text-gray-700">Equipment</label>
                                            <select wire:model.lazy="equipment_id_maintenance" id="equipment_id_maintenance" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                <option value="">Select Equipment</option>
                                                @foreach($allEquipment as $item)
                                                    <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->asset_code }})</option>
                                                @endforeach
                                            </select>
                                            @error('equipment_id_maintenance') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        
                                        <div>
                                            <label for="maintenance_type" class="block text-sm font-medium text-gray-700">Maintenance Type</label>
                                            <select wire:model.lazy="maintenance_type" id="maintenance_type" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                <option value="">Select Type</option>
                                                <option value="preventive">Preventive</option>
                                                <option value="corrective">Corrective</option>
                                                <option value="upgrade">Upgrade</option>
                                                <option value="inspection">Inspection</option>
                                            </select>
                                            @error('maintenance_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label for="maintenance_date" class="block text-sm font-medium text-gray-700">Maintenance Date</label>
                                                <input type="date" wire:model.lazy="maintenance_date" id="maintenance_date" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                                @error('maintenance_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                            </div>
                                            
                                            <div>
                                                <label for="next_maintenance_date" class="block text-sm font-medium text-gray-700">Next Maintenance Date</label>
                                                <input type="date" wire:model.lazy="next_maintenance_date" id="next_maintenance_date" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                                @error('next_maintenance_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        
                                        <div>
                                            <label for="cost" class="block text-sm font-medium text-gray-700">Cost</label>
                                            <input type="number" step="0.01" wire:model.lazy="cost" id="cost" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                            @error('cost') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        
                                        <div>
                                            <label for="performed_by" class="block text-sm font-medium text-gray-700">Performed By</label>
                                            <select wire:model.lazy="performed_by" id="performed_by" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                <option value="">Select Employee</option>
                                                @foreach($employees as $employee)
                                                    <option value="{{ $employee->id }}">{{ $employee->full_name }} </option>
                                                @endforeach
                                            </select>
                                            @error('performed_by') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        
                                        <div>
                                            <label for="maintenance_status" class="block text-sm font-medium text-gray-700">Status</label>
                                            <select wire:model.lazy="maintenance_status" id="maintenance_status" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                <option value="">Select Status</option>
                                                <option value="planned">Planned</option>
                                                <option value="in_progress">In Progress</option>
                                                <option value="completed">Completed</option>
                                                <option value="cancelled">Cancelled</option>
                                            </select>
                                            @error('maintenance_status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        
                                        <div>
                                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                            <textarea wire:model.lazy="description" id="description" rows="3" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                                            @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button wire:click="saveMaintenance" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                {{ $isEditing ? 'Update' : 'Save' }}
                            </button>
                            <button wire:click="closeMaintenanceModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Assignment Modal -->
    @if($showAssignmentModal)
    <div class="fixed inset-0 overflow-y-auto z-50" role="dialog">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity ease-out duration-300"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95
                ease-out duration-300 transform transition-opacity transition-transform
                @if($showAssignmentModal) opacity-100 translate-y-0 sm:scale-100 @endif">
                
                <div class="absolute top-0 right-0 pt-4 pr-4">
                    <button wire:click="closeAssignmentModal" type="button" class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <span class="sr-only">Close</span>
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-user-plus text-blue-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            {{ $isEditing ? 'Edit Assignment' : 'Add New Assignment' }}
                        </h3>
                        
                        <div class="mt-4 space-y-4">
                            <div>
                                <label for="equipment_id_assignment" class="block text-sm font-medium text-gray-700">
                                    Equipment
                                </label>
                                <select wire:model.lazy="equipment_id_assignment" id="equipment_id_assignment" 
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    <option value="">Select equipment</option>
                                    @foreach($availableEquipment as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->asset_code }})</option>
                                    @endforeach
                                </select>
                                @error('equipment_id_assignment') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                            </div>
                            
                            <div>
                                <label for="employee_id" class="block text-sm font-medium text-gray-700">
                                    Employee
                                </label>
                                <select wire:model.lazy="employee_id" id="employee_id" 
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    <option value="">Select employee</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                                    @endforeach
                                </select>
                                @error('employee_id') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="issue_date" class="block text-sm font-medium text-gray-700">
                                        Assigned Date
                                    </label>
                                    <input type="date" wire:model.lazy="issue_date" id="issue_date" 
                                        class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    @error('issue_date') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                                </div>
                                
                                <div>
                                    <label for="return_date" class="block text-sm font-medium text-gray-700">
                                        Return Date
                                    </label>
                                    <input type="date" wire:model.lazy="return_date" id="return_date" 
                                        class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    @error('return_date') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                                </div>
                            </div>
                            
                            <div>
                                <label for="assignment_notes" class="block text-sm font-medium text-gray-700">
                                    Purpose
                                </label>
                                <textarea wire:model.lazy="assignment_notes" id="assignment_notes" rows="3" 
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                    placeholder="Enter purpose of assignment"></textarea>
                                @error('assignment_notes') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                            </div>
                            
                            <div>
                                <label for="assignment_status" class="block text-sm font-medium text-gray-700">
                                    Status
                                </label>
                                <select wire:model.lazy="assignment_status" id="assignment_status" 
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    <option value="">Select status</option>
                                    <option value="issued">Issued</option>
                                    <option value="returned">Returned</option>
                                    <option value="damaged">Damaged</option>
                                    <option value="lost">Lost</option>
                                </select>
                                @error('assignment_status') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                            </div>
                            
                            <div>
                                <label for="condition_on_issue" class="block text-sm font-medium text-gray-700">
                                    Condition on Assignment
                                </label>
                                <input type="text" wire:model.lazy="condition_on_issue" id="condition_on_issue" 
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                    placeholder="Enter condition at time of assignment">
                                @error('condition_on_issue') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button wire:click="saveAssignment" type="button" 
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                        <i class="fas fa-save mr-2"></i> {{ $isEditing ? 'Update' : 'Save' }}
                    </button>
                    <button wire:click="closeAssignmentModal" type="button" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Equipment Modal -->
    @if($showEquipmentModal)
    <div class="fixed inset-0 overflow-y-auto z-50" role="dialog">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity ease-out duration-300"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95
                ease-out duration-300 transform transition-opacity transition-transform
                @if($showEquipmentModal) opacity-100 translate-y-0 sm:scale-100 @endif">
                
                <div class="absolute top-0 right-0 pt-4 pr-4">
                    <button wire:click="closeEquipmentModal" type="button" class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <span class="sr-only">Close</span>
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-tools text-blue-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            {{ $isEditing ? 'Edit Equipment' : 'Add New Equipment' }}
                        </h3>
                        
                        <div class="mt-4 space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">
                                    Name
                                </label>
                                <input type="text" wire:model.lazy="name" id="name" 
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                    placeholder="Enter equipment name">
                                @error('name') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                            </div>
                            
                            <div>
                                <label for="equipment_type" class="block text-sm font-medium text-gray-700">
                                    Equipment Type
                                </label>
                                <select wire:model.lazy="equipment_type" id="equipment_type" 
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    <option value="">Select type</option>
                                    <option value="computer">Computer</option>
                                    <option value="phone">Phone</option>
                                    <option value="tool">Tool</option>
                                    <option value="vehicle">Vehicle</option>
                                    <option value="other">Other</option>
                                </select>
                                @error('equipment_type') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="serial_number" class="block text-sm font-medium text-gray-700">
                                        Serial Number
                                    </label>
                                    <input type="text" wire:model.lazy="serial_number" id="serial_number" 
                                        class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                        placeholder="Enter serial number">
                                    @error('serial_number') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                                </div>
                                
                                <div>
                                    <label for="asset_code" class="block text-sm font-medium text-gray-700">
                                        Asset Code
                                    </label>
                                    <input type="text" wire:model.lazy="asset_code" id="asset_code" 
                                        class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                        placeholder="Enter asset code">
                                    @error('asset_code') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="brand" class="block text-sm font-medium text-gray-700">
                                        Brand
                                    </label>
                                    <input type="text" wire:model.lazy="brand" id="brand" 
                                        class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                        placeholder="Enter brand">
                                    @error('brand') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                                </div>
                                
                                <div>
                                    <label for="model" class="block text-sm font-medium text-gray-700">
                                        Model
                                    </label>
                                    <input type="text" wire:model.lazy="model" id="model" 
                                        class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                        placeholder="Enter model">
                                    @error('model') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="purchase_date" class="block text-sm font-medium text-gray-700">
                                        Purchase Date
                                    </label>
                                    <input type="date" wire:model.lazy="purchase_date" id="purchase_date" 
                                        class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    @error('purchase_date') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                                </div>
                                
                                <div>
                                    <label for="purchase_cost" class="block text-sm font-medium text-gray-700">
                                        Purchase Cost
                                    </label>
                                    <input type="number" step="0.01" wire:model.lazy="purchase_cost" id="purchase_cost" 
                                        class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                        placeholder="Enter cost">
                                    @error('purchase_cost') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                                </div>
                            </div>
                            
                            <div>
                                <label for="warranty_expiry" class="block text-sm font-medium text-gray-700">
                                    Warranty Expiry
                                </label>
                                <input type="date" wire:model.lazy="warranty_expiry" id="warranty_expiry" 
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @error('warranty_expiry') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                            </div>
                            
                            <div>
                                <label for="condition" class="block text-sm font-medium text-gray-700">
                                    Condition
                                </label>
                                <input type="text" wire:model.lazy="condition" id="condition" 
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                    placeholder="Enter condition">
                                @error('condition') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                            </div>
                            
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">
                                    Status
                                </label>
                                <select wire:model.lazy="status" id="status" 
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    <option value="">Select status</option>
                                    <option value="available">Available</option>
                                    <option value="assigned">Assigned</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="damaged">Damaged</option>
                                    <option value="disposed">Disposed</option>
                                </select>
                                @error('status') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                            </div>
                            
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700">
                                    Notes
                                </label>
                                <textarea wire:model.lazy="notes" id="notes" rows="3" 
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                    placeholder="Enter notes"></textarea>
                                @error('notes') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button wire:click="saveEquipment" type="button" 
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                        <i class="fas fa-save mr-2"></i> {{ $isEditing ? 'Update' : 'Save' }}
                    </button>
                    <button wire:click="closeEquipmentModal" type="button" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
    <div class="fixed inset-0 overflow-y-auto z-50" role="dialog">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity ease-out duration-300"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95
                ease-out duration-300 transform transition-opacity transition-transform
                @if($showDeleteModal) opacity-100 translate-y-0 sm:scale-100 @endif">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Confirm Deletion
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Are you sure you want to delete this item? This action cannot be undone.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="delete" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                        <i class="fas fa-trash mr-2"></i> Delete
                    </button>
                    <button type="button" wire:click="closeDeleteModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
