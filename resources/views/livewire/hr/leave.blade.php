<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold text-gray-800">
                            <i class="fas fa-calendar-alt mr-2 text-gray-600"></i>
                            Leave Management
                        </h2>
                        <button
                            wire:click="create"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                            <i class="fas fa-plus mr-1"></i>
                            Request Leave
                        </button>
                    </div>

                    <!-- Filters and Search -->
                    <div class="mb-6 bg-white p-4 rounded-lg shadow-sm">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
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
                                        placeholder="Search employee or reason..."
                                        class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 pr-3 py-2 sm:text-sm border-gray-300 rounded-md"
                                    >
                                </div>
                            </div>

                            <div>
                                <label for="filterEmployee" class="sr-only">Employee</label>
                                <select
                                    id="filterEmployee"
                                    wire:model.live="filters.employee_id"
                                    class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                                    <option value="">All Employees</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="filterLeaveType" class="sr-only">Leave Type</label>
                                <select
                                    id="filterLeaveType"
                                    wire:model.live="filters.leave_type_id"
                                    class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                                    <option value="">All Leave Types</option>
                                    @foreach($leaveTypes as $leaveType)
                                        <option value="{{ $leaveType->id }}">{{ $leaveType->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="filterStatus" class="sr-only">Status</label>
                                <select
                                    id="filterStatus"
                                    wire:model.live="filters.status"
                                    class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Leave Requests Table -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1 cursor-pointer" wire:click="sortBy('employee_id')">
                                                <span>Employee</span>
                                                @if($sortField === 'employee_id')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                                @else
                                                    <i class="fas fa-sort"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1">
                                                <span>Leave Type</span>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1 cursor-pointer" wire:click="sortBy('start_date')">
                                                <span>Period</span>
                                                @if($sortField === 'start_date')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                                @else
                                                    <i class="fas fa-sort"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1">
                                                <span>Days</span>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1 cursor-pointer" wire:click="sortBy('status')">
                                                <span>Status</span>
                                                @if($sortField === 'status')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                                @else
                                                    <i class="fas fa-sort"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($leaves as $leave)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    @if($leave->employee->photo)
                                                        <img src="{{ asset('storage/' . $leave->employee->photo) }}" alt="{{ $leave->employee->full_name }}" class="h-8 w-8 rounded-full mr-2">
                                                    @else
                                                        <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center mr-2">
                                                            <i class="fas fa-user text-gray-400"></i>
                                                        </div>
                                                    @endif
                                                    <div class="text-sm text-gray-900">{{ $leave->employee->full_name }}</div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $leave->leaveType->name ?? 'Unknown' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $leave->start_date->format('M d, Y') }} to {{ $leave->end_date->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $leave->total_days }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    {{ $leave->status === 'approved' ? 'bg-green-100 text-green-800' : 
                                                    ($leave->status === 'rejected' ? 'bg-red-100 text-red-800' : 
                                                    ($leave->status === 'cancelled' ? 'bg-gray-100 text-gray-800' : 
                                                    'bg-yellow-100 text-yellow-800')) }}">
                                                    {{ ucfirst($leave->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button
                                                    wire:click="view({{ $leave->id }})"
                                                    class="text-blue-600 hover:text-blue-900 mr-3"
                                                    title="View Details"
                                                >
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                @if($leave->status === 'pending')
                                                <button
                                                    wire:click="edit({{ $leave->id }})"
                                                    class="text-indigo-600 hover:text-indigo-900 mr-3"
                                                    title="Edit"
                                                >
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button
                                                    wire:click="approve({{ $leave->id }})"
                                                    class="text-green-600 hover:text-green-900 mr-3"
                                                    title="Approve"
                                                >
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button
                                                    wire:click="reject({{ $leave->id }})"
                                                    class="text-red-600 hover:text-red-900 mr-3"
                                                    title="Reject"
                                                >
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                @endif
                                                @if(in_array($leave->status, ['pending', 'approved']))
                                                <button
                                                    wire:click="confirmCancel({{ $leave->id }})"
                                                    class="text-gray-600 hover:text-gray-900"
                                                    title="Cancel"
                                                >
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-12 text-center">
                                                <div class="flex flex-col items-center justify-center text-gray-500">
                                                    <i class="fas fa-calendar-alt text-gray-400 text-4xl mb-4"></i>
                                                    <span class="text-lg font-medium">No leave requests found</span>
                                                    <p class="text-sm mt-2">
                                                        @if($search || !empty($filters['employee_id']) || !empty($filters['leave_type_id']) || !empty($filters['status']))
                                                            No requests match your search criteria. Try adjusting your filters.
                                                            <button
                                                                wire:click="resetFilters"
                                                                class="text-blue-500 hover:text-blue-700 underline ml-1"
                                                            >
                                                                Clear all filters
                                                            </button>
                                                        @else
                                                            There are no leave requests in the system yet. Click "Request Leave" to add one.
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
                            @if(method_exists($leaves, 'links'))
                                {{ $leaves->links() }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Leave Request Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">
                        <i class="fas {{ $isEditing ? 'fa-edit' : 'fa-plus-circle' }} mr-2"></i>
                        {{ $isEditing ? 'Edit' : 'Request' }} Leave
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
                        <!-- Employee -->
                        <div>
                            <label for="employee_id" class="block text-sm font-medium text-gray-700">Employee</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <select id="employee_id"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('employee_id') border-red-300 text-red-900 @enderror"
                                    wire:model.live="employee_id">
                                    <option value="">Select Employee</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                                    @endforeach
                                </select>
                                @error('employee_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Leave Type -->
                        <div>
                            <label for="leave_type_id" class="block text-sm font-medium text-gray-700">Leave Type</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <select id="leave_type_id"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('leave_type_id') border-red-300 text-red-900 @enderror"
                                    wire:model.live="leave_type_id">
                                    <option value="">Select Leave Type</option>
                                    @foreach($leaveTypes as $leaveType)
                                        <option value="{{ $leaveType->id }}">{{ $leaveType->name }}</option>
                                    @endforeach
                                </select>
                                @error('leave_type_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Start Date -->
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="date" id="start_date"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('start_date') border-red-300 text-red-900 @enderror"
                                    wire:model.live="start_date">
                                @error('start_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- End Date -->
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="date" id="end_date"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('end_date') border-red-300 text-red-900 @enderror"
                                    wire:model.live="end_date">
                                @error('end_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Total Days (Calculated automatically) -->
                        <div>
                            <label for="total_days" class="block text-sm font-medium text-gray-700">Total Days</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number" id="total_days" step="0.5" min="0.5"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('total_days') border-red-300 text-red-900 @enderror"
                                    wire:model.live="total_days" readonly>
                                @error('total_days')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Reason -->
                        <div>
                            <label for="reason" class="block text-sm font-medium text-gray-700">Reason</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <textarea id="reason" rows="3"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('reason') border-red-300 text-red-900 @enderror"
                                    wire:model.live="reason"></textarea>
                                @error('reason')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Attachment -->
                        <div>
                            <label for="attachment" class="block text-sm font-medium text-gray-700">Attachment (Optional)</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="file" id="attachment"
                                    class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('attachment') border-red-300 text-red-900 @enderror"
                                    wire:model.live="attachment">
                                @error('attachment')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            @if($attachmentPreview)
                                <div class="mt-2">
                                    <a href="{{ asset('storage/' . $attachmentPreview) }}" target="_blank" class="text-blue-600 hover:underline flex items-center">
                                        <i class="fas fa-file-alt mr-1"></i> View current attachment
                                    </a>
                                </div>
                            @endif
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
                            {{ $isEditing ? 'Update' : 'Submit' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- View Leave Details Modal -->
    @if($showViewModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">
                        <i class="fas fa-info-circle mr-2"></i>
                        Leave Request Details
                    </h3>
                    <button type="button" class="text-gray-500 hover:text-gray-700 text-xl" wire:click="closeViewModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                @if($currentLeave)
                <div class="space-y-4">
                    <div class="flex items-center mb-4">
                        @if($currentLeave->employee->photo)
                            <img src="{{ asset('storage/' . $currentLeave->employee->photo) }}" alt="{{ $currentLeave->employee->full_name }}" class="h-16 w-16 rounded-full mr-4">
                        @else
                            <div class="h-16 w-16 rounded-full bg-gray-200 flex items-center justify-center mr-4">
                                <i class="fas fa-user text-gray-400 text-xl"></i>
                            </div>
                        @endif
                        <div>
                            <h4 class="text-lg font-medium">{{ $currentLeave->employee->full_name }}</h4>
                            <p class="text-gray-500">{{ $currentLeave->employee->position->title ?? 'No Position' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Leave Type</p>
                            <p class="font-medium">{{ $currentLeave->leaveType->name ?? 'Unknown' }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">Status</p>
                            <p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $currentLeave->status === 'approved' ? 'bg-green-100 text-green-800' : 
                                    ($currentLeave->status === 'rejected' ? 'bg-red-100 text-red-800' : 
                                    ($currentLeave->status === 'cancelled' ? 'bg-gray-100 text-gray-800' : 
                                    'bg-yellow-100 text-yellow-800')) }}">
                                    {{ ucfirst($currentLeave->status) }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">Start Date</p>
                            <p class="font-medium">{{ $currentLeave->start_date->format('M d, Y') }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">End Date</p>
                            <p class="font-medium">{{ $currentLeave->end_date->format('M d, Y') }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">Total Days</p>
                            <p class="font-medium">{{ $currentLeave->total_days }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">Request Date</p>
                            <p class="font-medium">{{ $currentLeave->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Reason</p>
                        <p class="font-medium">{{ $currentLeave->reason }}</p>
                    </div>

                    @if($currentLeave->approved_by)
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Approved By</p>
                            <p class="font-medium">{{ $currentLeave->approver->name ?? 'Unknown' }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">Approved Date</p>
                            <p class="font-medium">{{ $currentLeave->approved_date ? $currentLeave->approved_date->format('M d, Y') : 'N/A' }}</p>
                        </div>
                    </div>
                    @endif

                    @if($currentLeave->rejection_reason)
                    <div>
                        <p class="text-sm text-gray-500">Rejection Reason</p>
                        <p class="font-medium">{{ $currentLeave->rejection_reason }}</p>
                    </div>
                    @endif

                    @if($currentLeave->attachment)
                    <div>
                        <p class="text-sm text-gray-500">Attachment</p>
                        <a href="{{ asset('storage/' . $currentLeave->attachment) }}" target="_blank" class="text-blue-600 hover:underline inline-flex items-center mt-1">
                            <i class="fas fa-file-alt mr-1"></i> View Attachment
                        </a>
                    </div>
                    @endif

                    <div class="grid grid-cols-3 gap-4 pt-4 border-t border-gray-200">
                        <div>
                            <p class="text-sm text-gray-500">{{ __('messages.created_by') }}</p>
                            <p class="font-medium">{{ $currentLeave->creator->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">{{ __('messages.created_at') }}</p>
                            <p class="font-medium">{{ $currentLeave->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">{{ __('messages.updated_at') }}</p>
                            <p class="font-medium">{{ $currentLeave->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        wire:click="closeViewModal">
                        Close
                    </button>
                    @if($currentLeave->status === 'pending')
                    <button type="button"
                        class="px-4 py-2 border border-green-500 rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                        wire:click="approve({{ $currentLeave->id }})">
                        Approve
                    </button>
                    <button type="button"
                        class="px-4 py-2 border border-red-500 rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        wire:click="reject({{ $currentLeave->id }})">
                        Reject
                    </button>
                    @endif
                </div>
                @endif
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
