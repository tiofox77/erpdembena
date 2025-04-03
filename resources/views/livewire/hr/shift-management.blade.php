<div>
    <div class="p-4 bg-white rounded-lg shadow-md">
        <!-- Tabs Selector apenas com Livewire -->
        <div>
            <div class="border-b border-gray-200 mb-4">
                <ul class="flex flex-wrap -mb-px">
                    <li class="mr-2">
                        <button wire:click="setActiveTab('shifts')" 
                            class="inline-block py-2 px-4 border-b-2 font-medium text-sm {{ $activeTab === 'shifts' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            <i class="fas fa-clock mr-2"></i> Shifts
                        </button>
                    </li>
                    <li class="mr-2">
                        <button wire:click="setActiveTab('assignments')" 
                            class="inline-block py-2 px-4 border-b-2 font-medium text-sm {{ $activeTab === 'assignments' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            <i class="fas fa-user-clock mr-2"></i> Assignments
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Conteúdo da tab Shifts -->
            <div class="{{ $activeTab === 'shifts' ? 'block' : 'hidden' }} mt-4">
                <div class="flex flex-col md:flex-row justify-between items-center mb-4 space-y-2 md:space-y-0">
                    <div class="flex items-center space-x-2 w-full md:w-auto">
                        <input type="text" wire:model.debounce.300ms="searchShift" placeholder="Search shifts..." 
                            class="px-3 py-2 placeholder-gray-500 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        
                        <select wire:model="filters.is_active" class="block pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            <option value="">All Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        
                        <button wire:click="resetFilters" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-sync-alt mr-2"></i> Reset
                        </button>
                    </div>
                    
                    <div class="flex space-x-2 w-full md:w-auto justify-end">
                        <button wire:click="exportShiftsPDF" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-file-pdf mr-2 text-red-500"></i> Export PDF
                        </button>
                        
                        <button wire:click="createShift" class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-plus mr-2"></i> Add Shift
                        </button>
                    </div>
                </div>
                
                <!-- Tabela de Turnos -->
                <div class="overflow-x-auto bg-white rounded-lg border">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortByShift('name')">
                                    Name
                                    @if($sortFieldShift === 'name')
                                        <i class="fas fa-sort-{{ $sortDirectionShift === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortByShift('start_time')">
                                    Start Time
                                    @if($sortFieldShift === 'start_time')
                                        <i class="fas fa-sort-{{ $sortDirectionShift === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortByShift('end_time')">
                                    End Time
                                    @if($sortFieldShift === 'end_time')
                                        <i class="fas fa-sort-{{ $sortDirectionShift === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Interval
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($shifts as $shift)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $shift->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">{{ $shift->start_time->format('H:i') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">{{ $shift->end_time->format('H:i') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">{{ $shift->break_duration ?? 0 }} min</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $shift->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $shift->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $shift->is_night_shift ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $shift->is_night_shift ? 'Night Shift' : 'Day Shift' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button wire:click="editShift({{ $shift->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button wire:click="confirmDeleteShift({{ $shift->id }})" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        No shifts found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginação -->
                <div class="mt-4">
                    {{ $shifts->links() }}
                </div>
            </div>

            <!-- Conteúdo da tab Assignments -->
            <div class="{{ $activeTab === 'assignments' ? 'block' : 'hidden' }} mt-4">
                <div class="flex flex-col md:flex-row justify-between items-center mb-4 space-y-2 md:space-y-0">
                    <div class="flex items-center space-x-2 w-full md:w-auto">
                        <input type="text" wire:model.debounce.300ms="searchAssignment" placeholder="Search assignments..." 
                            class="px-3 py-2 placeholder-gray-500 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        
                        <select wire:model="filters.department_id" class="block pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            <option value="">All Departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                        
                        <select wire:model="filters.shift_id" class="block pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            <option value="">All Shifts</option>
                            @foreach($shiftsForSelect as $shift)
                                <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                            @endforeach
                        </select>
                        
                        <button wire:click="resetFilters" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-sync-alt mr-2"></i> Reset
                        </button>
                    </div>
                    
                    <div class="flex space-x-2 w-full md:w-auto justify-end">
                        <button wire:click="exportAssignmentsPDF" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-file-pdf mr-2 text-red-500"></i> Export PDF
                        </button>
                        
                        <button wire:click="createAssignment" class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-plus mr-2"></i> Add Assignment
                        </button>
                    </div>
                </div>
                
                <!-- Search and Filters -->
                <div class="mb-4 flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="flex-1">
                        <div class="relative">
                            <input wire:model.debounce.300ms="searchAssignment" type="text" placeholder="Search assignments..." 
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div class="absolute left-3 top-2 text-gray-400">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <select wire:model="filters.department_id" class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                        <select wire:model="filters.shift_id" class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Shifts</option>
                            @foreach($shiftsForSelect as $shiftOption)
                                <option value="{{ $shiftOption->id }}">{{ $shiftOption->name }}</option>
                            @endforeach
                        </select>
                        <select wire:model="perPage" class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                        </select>
                    </div>
                </div>
                
                <!-- Assignments Table -->
                <div class="overflow-x-auto bg-white rounded-lg border">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortByAssignment('employee_id')">
                                    Employee
                                    @if($sortFieldAssignment === 'employee_id')
                                        <i class="fas fa-sort-{{ $sortDirectionAssignment === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Department
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortByAssignment('shift_id')">
                                    Shift
                                    @if($sortFieldAssignment === 'shift_id')
                                        <i class="fas fa-sort-{{ $sortDirectionAssignment === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortByAssignment('start_date')">
                                    Start Date
                                    @if($sortFieldAssignment === 'start_date')
                                        <i class="fas fa-sort-{{ $sortDirectionAssignment === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortByAssignment('end_date')">
                                    End Date
                                    @if($sortFieldAssignment === 'end_date')
                                        <i class="fas fa-sort-{{ $sortDirectionAssignment === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($shiftAssignments as $assignment)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $assignment->employee->full_name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">{{ $assignment->employee->department->name ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">{{ $assignment->shift->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">{{ $assignment->start_date->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">{{ $assignment->end_date ? $assignment->end_date->format('d/m/Y') : 'Continuous' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $assignment->is_permanent ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $assignment->is_permanent ? 'Permanent' : 'Temporary' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button wire:click="editAssignment({{ $assignment->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button wire:click="confirmDeleteAssignment({{ $assignment->id }})" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        No assignments found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginação -->
                <div class="mt-4">
                    {{ $shiftAssignments->links() }}
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
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-user-clock text-indigo-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    {{ $isEditing ? 'Edit Assignment' : 'New Shift Assignment' }}
                                </h3>
                                
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label for="employee_id" class="block text-sm font-medium text-gray-700">
                                            <i class="fas fa-user mr-1 text-gray-500"></i> Employee
                                        </label>
                                        <select wire:model.lazy="employee_id" id="employee_id" 
                                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                            <option value="">Select Employee</option>
                                            @foreach($employees as $employee)
                                                <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('employee_id') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                                    </div>
                                    
                                    <div>
                                        <label for="shift_id" class="block text-sm font-medium text-gray-700">
                                            <i class="fas fa-clock mr-1 text-gray-500"></i> Shift
                                        </label>
                                        <select wire:model.lazy="shift_id" id="shift_id" 
                                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                            <option value="">Select Shift</option>
                                            @foreach($shifts as $shift)
                                                <option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->start_time }} - {{ $shift->end_time }})</option>
                                            @endforeach
                                        </select>
                                        @error('shift_id') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="start_date" class="block text-sm font-medium text-gray-700">
                                                <i class="fas fa-calendar-plus mr-1 text-gray-500"></i> Start Date
                                            </label>
                                            <input type="date" wire:model.lazy="start_date" id="start_date" 
                                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                            @error('start_date') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                                        </div>
                                        
                                        <div>
                                            <label for="end_date" class="block text-sm font-medium text-gray-700">
                                                <i class="fas fa-calendar-minus mr-1 text-gray-500"></i> End Date
                                            </label>
                                            <input type="date" wire:model.lazy="end_date" id="end_date" 
                                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                            @error('end_date') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="flex flex-wrap space-x-4">
                                        <div class="flex items-center">
                                            <input type="checkbox" wire:model.lazy="is_permanent" id="is_permanent" 
                                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                            <label for="is_permanent" class="ml-2 block text-sm text-gray-700">
                                                <i class="fas fa-thumbtack mr-1 text-gray-500"></i> Permanent Assignment
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label for="rotation_pattern" class="block text-sm font-medium text-gray-700">
                                            <i class="fas fa-sync-alt mr-1 text-gray-500"></i> Rotation Pattern
                                        </label>
                                        <select wire:model.lazy="rotation_pattern" id="rotation_pattern" 
                                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                            <option value="">Select Rotation Pattern</option>
                                            <option value="daily">Daily</option>
                                            <option value="weekly">Weekly</option>
                                            <option value="biweekly">Bi-weekly</option>
                                            <option value="monthly">Monthly</option>
                                            <option value="none">None</option>
                                        </select>
                                        @error('rotation_pattern') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                                    </div>
                                    
                                    <div>
                                        <label for="notes" class="block text-sm font-medium text-gray-700">
                                            <i class="fas fa-sticky-note mr-1 text-gray-500"></i> Notes
                                        </label>
                                        <textarea wire:model.lazy="notes" id="notes" rows="3" 
                                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                                        @error('notes') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                            <button wire:click="saveAssignment" type="button" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
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

            <!-- Shift Modal -->
            @if($showShiftModal)
            <div class="fixed inset-0 overflow-y-auto z-50" role="dialog">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity ease-out duration-300"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95
                        ease-out duration-300 transform transition-opacity transition-transform
                        @if($showShiftModal) opacity-100 translate-y-0 sm:scale-100 @endif">
                        
                        <div class="absolute top-0 right-0 pt-4 pr-4">
                            <button wire:click="closeShiftModal" type="button" class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <span class="sr-only">Close</span>
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                        
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-clock text-blue-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    {{ $isEditing ? 'Edit Shift' : 'Add New Shift' }}
                                </h3>
                                
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700">
                                            Name
                                        </label>
                                        <input type="text" wire:model.lazy="name" id="name" 
                                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                            placeholder="Enter category name">
                                        @error('name') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="start_time" class="block text-sm font-medium text-gray-700">
                                                Start Time
                                            </label>
                                            <input type="time" wire:model.lazy="start_time" id="start_time" 
                                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                            @error('start_time') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                                        </div>
                                        
                                        <div>
                                            <label for="end_time" class="block text-sm font-medium text-gray-700">
                                                End Time
                                            </label>
                                            <input type="time" wire:model.lazy="end_time" id="end_time" 
                                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                            @error('end_time') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label for="break_duration" class="block text-sm font-medium text-gray-700">
                                            Break Duration (minutes)
                                        </label>
                                        <input type="number" wire:model.lazy="break_duration" id="break_duration" 
                                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                            placeholder="Enter break duration in minutes">
                                        @error('break_duration') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                                    </div>
                                    
                                    <div>
                                        <label for="description" class="block text-sm font-medium text-gray-700">
                                            Description
                                        </label>
                                        <textarea wire:model.lazy="description" id="description" rows="3" 
                                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                            placeholder="Enter category description"></textarea>
                                        @error('description') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                                    </div>
                                    
                                    <div class="flex items-center space-x-6">
                                        <div class="flex items-center">
                                            <input type="checkbox" wire:model.lazy="is_night_shift" id="is_night_shift" 
                                                class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                            <label for="is_night_shift" class="ml-2 block text-sm text-gray-700">
                                                Night Shift
                                            </label>
                                        </div>
                                        
                                        <div class="flex items-center">
                                            <input type="checkbox" wire:model.lazy="is_active" id="is_active" 
                                                class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                            <label for="is_active" class="ml-2 block text-sm text-gray-700">
                                                Active
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                            <button wire:click="saveShift" type="button" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                                <i class="fas fa-save mr-2"></i> {{ $isEditing ? 'Update' : 'Save' }}
                            </button>
                            <button wire:click="closeShiftModal" type="button" 
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
    </div>
</div>
