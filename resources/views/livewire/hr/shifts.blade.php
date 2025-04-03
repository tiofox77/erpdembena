<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold text-gray-800">
                            <i class="fas fa-clock mr-2 text-gray-600"></i>
                            Shift Management
                        </h2>
                        <button
                            wire:click="create"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                            <i class="fas fa-plus mr-1"></i>
                            Add Shift
                        </button>
                    </div>

                    <!-- Filters and Search -->
                    <div class="mb-6 bg-white p-4 rounded-lg shadow-sm">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="md:col-span-2">
                                <label for="search" class="sr-only">Search</label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                    <input
                                        type="text"
                                        id="search"
                                        wire:model.live.debounce.300ms="search"
                                        placeholder="Search shifts..."
                                        class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 pr-3 py-2 sm:text-sm border-gray-300 rounded-md"
                                    >
                                </div>
                            </div>

                            <div>
                                <label for="filterType" class="sr-only">Shift Type</label>
                                <select
                                    id="filterType"
                                    wire:model.live="filters.type"
                                    class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                                    <option value="">All Types</option>
                                    <option value="morning">Morning</option>
                                    <option value="afternoon">Afternoon</option>
                                    <option value="night">Night</option>
                                    <option value="custom">Custom</option>
                                </select>
                            </div>

                            <div>
                                <label for="filterDepartment" class="sr-only">Department</label>
                                <select
                                    id="filterDepartment"
                                    wire:model.live="filters.department_id"
                                    class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                                    <option value="">All Departments</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- View Options -->
                    <div class="flex justify-end mb-4">
                        <div class="flex border border-gray-300 rounded-md overflow-hidden">
                            <button
                                wire:click="setView('list')"
                                class="px-4 py-2 {{ $currentView === 'list' ? 'bg-blue-50 text-blue-700' : 'bg-white text-gray-700' }}"
                            >
                                <i class="fas fa-list mr-1"></i> List
                            </button>
                            <button
                                wire:click="setView('calendar')"
                                class="px-4 py-2 {{ $currentView === 'calendar' ? 'bg-blue-50 text-blue-700' : 'bg-white text-gray-700' }}"
                            >
                                <i class="fas fa-calendar-alt mr-1"></i> Calendar
                            </button>
                        </div>
                    </div>

                    <!-- List View -->
                    @if($currentView === 'list')
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1 cursor-pointer" wire:click="sortBy('name')">
                                                <span>Shift Name</span>
                                                @if($sortField === 'name')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                                @else
                                                    <i class="fas fa-sort"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1 cursor-pointer" wire:click="sortBy('type')">
                                                <span>Type</span>
                                                @if($sortField === 'type')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                                @else
                                                    <i class="fas fa-sort"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1">
                                                <span>Time</span>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1">
                                                <span>Department</span>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1">
                                                <span>Employees</span>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($shifts as $shift)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $shift->name }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    {{ $shift->type === 'morning' ? 'bg-yellow-100 text-yellow-800' : 
                                                    ($shift->type === 'afternoon' ? 'bg-blue-100 text-blue-800' : 
                                                    ($shift->type === 'night' ? 'bg-indigo-100 text-indigo-800' : 
                                                    'bg-gray-100 text-gray-800')) }}">
                                                    {{ ucfirst($shift->type) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $shift->department->name ?? 'All Departments' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center text-sm text-gray-500">
                                                    <div class="flex -space-x-1">
                                                        @foreach($shift->employees->take(3) as $employee)
                                                            @if($employee->photo)
                                                                <img class="h-6 w-6 rounded-full ring-2 ring-white" src="{{ asset('storage/' . $employee->photo) }}" alt="{{ $employee->full_name }}">
                                                            @else
                                                                <div class="h-6 w-6 rounded-full bg-gray-200 flex items-center justify-center ring-2 ring-white">
                                                                    <i class="fas fa-user text-gray-400 text-xs"></i>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                    @if($shift->employees->count() > 3)
                                                        <span class="ml-1">+{{ $shift->employees->count() - 3 }} more</span>
                                                    @endif
                                                    @if($shift->employees->count() === 0)
                                                        <span>No employees assigned</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button
                                                    wire:click="edit({{ $shift->id }})"
                                                    class="text-indigo-600 hover:text-indigo-900 mr-3"
                                                >
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button
                                                    wire:click="confirmDelete({{ $shift->id }})"
                                                    class="text-red-600 hover:text-red-900"
                                                >
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-12 text-center">
                                                <div class="flex flex-col items-center justify-center text-gray-500">
                                                    <i class="fas fa-clock text-gray-400 text-4xl mb-4"></i>
                                                    <span class="text-lg font-medium">No shifts found</span>
                                                    <p class="text-sm mt-2">
                                                        @if($search || !empty($filters['type']) || !empty($filters['department_id']))
                                                            No shifts match your search criteria. Try adjusting your filters.
                                                            <button
                                                                wire:click="resetFilters"
                                                                class="text-blue-500 hover:text-blue-700 underline ml-1"
                                                            >
                                                                Clear all filters
                                                            </button>
                                                        @else
                                                            There are no shifts in the system yet. Click "Add Shift" to create one.
                                                        @endif
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="px-4 py-3 bg-white border-t border-gray-200 sm:px-6">
                            @if(method_exists($shifts, 'links'))
                                {{ $shifts->links() }}
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Calendar View (Simplified) -->
                    @if($currentView === 'calendar')
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="flex items-center justify-between px-6 py-4 border-b">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">
                                    {{ $currentMonth }} {{ $currentYear }}
                                </h3>
                            </div>
                            <div class="flex space-x-2">
                                <button
                                    wire:click="previousMonth"
                                    class="p-2 rounded-full hover:bg-gray-100"
                                >
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button
                                    wire:click="currentMonth"
                                    class="px-3 py-1 rounded bg-blue-100 text-blue-700 hover:bg-blue-200"
                                >
                                    Today
                                </button>
                                <button
                                    wire:click="nextMonth"
                                    class="p-2 rounded-full hover:bg-gray-100"
                                >
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                        <div class="grid grid-cols-7 gap-px bg-gray-200">
                            <div class="text-center py-2 bg-gray-50 text-xs font-medium text-gray-500 uppercase">Sun</div>
                            <div class="text-center py-2 bg-gray-50 text-xs font-medium text-gray-500 uppercase">Mon</div>
                            <div class="text-center py-2 bg-gray-50 text-xs font-medium text-gray-500 uppercase">Tue</div>
                            <div class="text-center py-2 bg-gray-50 text-xs font-medium text-gray-500 uppercase">Wed</div>
                            <div class="text-center py-2 bg-gray-50 text-xs font-medium text-gray-500 uppercase">Thu</div>
                            <div class="text-center py-2 bg-gray-50 text-xs font-medium text-gray-500 uppercase">Fri</div>
                            <div class="text-center py-2 bg-gray-50 text-xs font-medium text-gray-500 uppercase">Sat</div>
                        </div>
                        <div class="grid grid-cols-7 gap-px bg-gray-200">
                            @foreach($calendarDays as $day)
                                <div class="min-h-[100px] bg-white p-1">
                                    <div class="flex justify-between">
                                        <span class="text-sm {{ $day['isToday'] ? 'bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center' : ($day['isCurrentMonth'] ? 'text-gray-900' : 'text-gray-400') }}">
                                            {{ $day['dayNumber'] }}
                                        </span>
                                        @if($day['isCurrentMonth'])
                                            <button
                                                wire:click="createShiftForDate('{{ $day['date'] }}')"
                                                class="text-xs text-blue-600 hover:text-blue-800"
                                                title="Add shift for this day"
                                            >
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        @endif
                                    </div>
                                    <div class="mt-1 space-y-1 max-h-[80px] overflow-y-auto">
                                        @foreach($day['shifts'] as $shift)
                                            <div 
                                                class="text-xs p-1 rounded truncate cursor-pointer hover:bg-gray-50
                                                {{ $shift['type'] === 'morning' ? 'bg-yellow-50 text-yellow-800 border-l-2 border-yellow-500' : 
                                                ($shift['type'] === 'afternoon' ? 'bg-blue-50 text-blue-800 border-l-2 border-blue-500' : 
                                                ($shift['type'] === 'night' ? 'bg-indigo-50 text-indigo-800 border-l-2 border-indigo-500' : 
                                                'bg-gray-50 text-gray-800 border-l-2 border-gray-500')) }}"
                                                wire:click="viewShift({{ $shift['id'] }})"
                                            >
                                                {{ $shift['time'] }} - {{ $shift['name'] }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Shift Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">
                        <i class="fas {{ $isEditing ? 'fa-edit' : 'fa-plus-circle' }} mr-2"></i>
                        {{ $isEditing ? 'Edit' : 'Add' }} Shift
                    </h3>
                    <button type="button" class="text-gray-500 hover:text-gray-700 text-xl" wire:click="closeModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
                        <p class="font-bold flex items-center">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
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
                    <div class="grid grid-cols-1 gap-4">
                        <!-- Shift Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Shift Name</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="text" id="name"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-300 text-red-900 @enderror"
                                    wire:model.live="name">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Shift Type -->
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700">Shift Type</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <select id="type"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('type') border-red-300 text-red-900 @enderror"
                                    wire:model.live="type">
                                    <option value="">Select Type</option>
                                    <option value="morning">Morning</option>
                                    <option value="afternoon">Afternoon</option>
                                    <option value="night">Night</option>
                                    <option value="custom">Custom</option>
                                </select>
                                @error('type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Time Range -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input type="time" id="start_time"
                                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('start_time') border-red-300 text-red-900 @enderror"
                                        wire:model.live="start_time">
                                    @error('start_time')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div>
                                <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input type="time" id="end_time"
                                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('end_time') border-red-300 text-red-900 @enderror"
                                        wire:model.live="end_time">
                                    @error('end_time')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Department -->
                        <div>
                            <label for="department_id" class="block text-sm font-medium text-gray-700">Department</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <select id="department_id"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('department_id') border-red-300 text-red-900 @enderror"
                                    wire:model.live="department_id">
                                    <option value="">Select Department</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Employees Assignment -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Assign Employees</label>
                            <div class="border border-gray-300 rounded-md p-2 max-h-60 overflow-y-auto">
                                @foreach($availableEmployees as $employee)
                                    <div class="flex items-center py-1">
                                        <input
                                            type="checkbox"
                                            id="employee-{{ $employee->id }}"
                                            value="{{ $employee->id }}"
                                            wire:model.live="selectedEmployees"
                                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                        >
                                        <label for="employee-{{ $employee->id }}" class="ml-2 text-sm text-gray-700">
                                            {{ $employee->full_name }}
                                            <span class="text-xs text-gray-500">
                                                ({{ $employee->position->title ?? 'No Position' }})
                                            </span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <textarea id="description" rows="2"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('description') border-red-300 text-red-900 @enderror"
                                    wire:model.live="description"></textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Recurrence Options (for future implementation) -->
                        <div>
                            <div class="relative flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="is_recurring" type="checkbox"
                                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                                        wire:model.live="is_recurring">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="is_recurring" class="font-medium text-gray-700">Recurring Shift</label>
                                    <p class="text-gray-500">Make this a recurring shift pattern</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button"
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            wire:click="closeModal">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ $isEditing ? 'Update' : 'Save' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-red-600">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Delete Shift
                    </h3>
                    <button type="button" class="text-gray-500 hover:text-gray-700 text-xl" wire:click="closeDeleteModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="mb-4">
                    <p class="text-gray-700">Are you sure you want to delete this shift? This action cannot be undone.</p>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        wire:click="closeDeleteModal">
                        Cancel
                    </button>
                    <button type="button"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        wire:click="delete">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Flash Message -->
    @if (session()->has('message'))
        <div x-data="{ show: true }"
             x-init="setTimeout(() => show = false, 3000)"
             x-show="show"
             class="fixed bottom-4 right-4 bg-green-500 text-white py-2 px-4 rounded-md shadow-md">
            {{ session('message') }}
        </div>
    @endif
</div>
