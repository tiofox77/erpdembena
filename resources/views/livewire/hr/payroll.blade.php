<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold text-gray-800">
                            <i class="fas fa-money-bill-wave mr-2 text-gray-600"></i>
                            Payroll Management
                        </h2>
                        <div class="flex space-x-2">
                            <a href="{{ route('hr.payroll-guide') }}" 
                                class="px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 flex items-center">
                                <i class="fas fa-info-circle mr-1"></i>
                                Guia de Cálculos
                            </a>
                            <button
                                wire:click="create"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                            >
                                <i class="fas fa-plus mr-1"></i>
                                Create Payroll
                            </button>
                            <button
                                wire:click="exportPayroll"
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                            >
                                <i class="fas fa-file-export mr-1"></i>
                                Export
                            </button>
                        </div>
                    </div>

                    <!-- Filters and Search -->
                    <div class="mb-6 bg-white p-4 rounded-lg shadow-sm">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="md:col-span-1">
                                <label for="search" class="sr-only">Search</label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                    <input
                                        type="text"
                                        id="search"
                                        wire:model.live.debounce.300ms="search"
                                        placeholder="Search employee..."
                                        class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 pr-3 py-2 sm:text-sm border-gray-300 rounded-md"
                                    >
                                </div>
                            </div>

                            <div>
                                <label for="filterPeriod" class="sr-only">Period</label>
                                <select
                                    id="filterPeriod"
                                    wire:model.live="filters.period_id"
                                    class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                                    <option value="">All Periods</option>
                                    @foreach($payrollPeriods as $period)
                                        <option value="{{ $period->id }}">{{ $period->name }}</option>
                                    @endforeach
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

                            <div>
                                <label for="filterStatus" class="sr-only">Status</label>
                                <select
                                    id="filterStatus"
                                    wire:model.live="filters.status"
                                    class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                                    <option value="">All Status</option>
                                    <option value="draft">Draft</option>
                                    <option value="approved">Approved</option>
                                    <option value="paid">Paid</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Payroll Table -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1 cursor-pointer" wire:click="sortBy('period_id')">
                                                <span>Period</span>
                                                @if($sortField === 'period_id')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                                @else
                                                    <i class="fas fa-sort"></i>
                                                @endif
                                            </div>
                                        </th>
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
                                                <span>Department</span>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1">
                                                <span>Gross Salary</span>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1">
                                                <span>Deductions</span>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1">
                                                <span>Net Salary</span>
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
                                    @forelse($payrolls as $payroll)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $payroll->period->name ?? 'N/A' }}</div>
                                                <div class="text-sm text-gray-500">
                                                    @if($payroll->period && $payroll->period->start_date && $payroll->period->end_date)
                                                        {{ $payroll->period->start_date->format('M d') }} - {{ $payroll->period->end_date->format('M d, Y') }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    @if($payroll->employee->photo)
                                                        <img src="{{ asset('storage/' . $payroll->employee->photo) }}" alt="{{ $payroll->employee->full_name }}" class="h-8 w-8 rounded-full mr-2">
                                                    @else
                                                        <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center mr-2">
                                                            <i class="fas fa-user text-gray-400"></i>
                                                        </div>
                                                    @endif
                                                    <div class="text-sm text-gray-900">{{ $payroll->employee->full_name }}</div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $payroll->employee->department->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ number_format($payroll->gross_salary, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ number_format($payroll->total_deductions, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ number_format($payroll->net_salary, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    {{ $payroll->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : 
                                                    ($payroll->status === 'approved' ? 'bg-blue-100 text-blue-800' : 
                                                    'bg-green-100 text-green-800') }}">
                                                    {{ ucfirst($payroll->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button
                                                    wire:click="view({{ $payroll->id }})"
                                                    class="text-blue-600 hover:text-blue-900 mr-2"
                                                    title="View Details"
                                                >
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                @if($payroll->status === 'draft')
                                                    <button
                                                        wire:click="edit({{ $payroll->id }})"
                                                        class="text-indigo-600 hover:text-indigo-900 mr-2"
                                                        title="Edit"
                                                    >
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button
                                                        wire:click="approve({{ $payroll->id }})"
                                                        class="text-green-600 hover:text-green-900 mr-2"
                                                        title="Approve"
                                                    >
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                @endif
                                                @if($payroll->status === 'approved')
                                                    <button
                                                        wire:click="markAsPaid({{ $payroll->id }})"
                                                        class="text-green-600 hover:text-green-900 mr-2"
                                                        title="Mark as Paid"
                                                    >
                                                        <i class="fas fa-money-bill-wave"></i>
                                                    </button>
                                                @endif
                                                <button
                                                    wire:click="downloadPayslip({{ $payroll->id }})"
                                                    class="text-red-600 hover:text-red-900 mr-2"
                                                    title="Download Payslip"
                                                >
                                                    <i class="fas fa-file-pdf"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="px-6 py-12 text-center">
                                                <div class="flex flex-col items-center justify-center text-gray-500">
                                                    <i class="fas fa-money-bill-wave text-gray-400 text-4xl mb-4"></i>
                                                    <span class="text-lg font-medium">No payroll records found</span>
                                                    <p class="text-sm mt-2">
                                                        @if($search || !empty($filters['period_id']) || !empty($filters['department_id']) || !empty($filters['status']))
                                                            No records match your search criteria. Try adjusting your filters.
                                                            <button
                                                                wire:click="resetFilters"
                                                                class="text-blue-500 hover:text-blue-700 underline ml-1"
                                                            >
                                                                Clear all filters
                                                            </button>
                                                        @else
                                                            There are no payroll records in the system yet. Click "Create Payroll" to add one.
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
                            @if(method_exists($payrolls, 'links'))
                                {{ $payrolls->links() }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Forms and other components go here -->
    @if($showModal)
        <!-- Simplified payroll form modal for brevity -->
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl p-6 max-h-screen overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">
                        <i class="fas {{ $isEditing ? 'fa-edit' : 'fa-plus-circle' }} mr-2"></i>
                        {{ $isEditing ? 'Edit' : 'Create' }} Payroll
                    </h3>
                    <button type="button" class="text-gray-500 hover:text-gray-700 text-xl" wire:click="closeModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
                        <p class="font-bold">Please correct the following errors:</p>
                        <ul class="mt-2 list-disc list-inside text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form wire:submit.prevent="save">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Basic information -->
                        <div class="md:col-span-2 border-b border-gray-200 pb-4 mb-4">
                            <h4 class="text-md font-medium mb-2">Basic Information</h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Period -->
                                <div>
                                    <label for="payroll_period_id" class="block text-sm font-medium text-gray-700">Payroll Period</label>
                                    <select id="payroll_period_id"
                                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('payroll_period_id') border-red-300 text-red-900 @enderror"
                                        wire:model.live="payroll_period_id">
                                        <option value="">Select Period</option>
                                        @foreach($payrollPeriods as $period)
                                            <option value="{{ $period->id }}">{{ $period->name }} ({{ $period->start_date->format('M d') }} - {{ $period->end_date->format('M d, Y') }})</option>
                                        @endforeach
                                    </select>
                                    @error('payroll_period_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Employee -->
                                <div>
                                    <label for="employee_id" class="block text-sm font-medium text-gray-700">Employee</label>
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
                        </div>

                        <!-- Earnings -->
                        <div class="border-b border-gray-200 pb-4 mb-4">
                            <h4 class="text-md font-medium mb-2">Earnings</h4>
                            
                            <div class="space-y-3">
                                <div>
                                    <label for="basic_salary" class="block text-sm font-medium text-gray-700">Basic Salary</label>
                                    <input type="number" id="basic_salary" step="0.01" min="0"
                                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('basic_salary') border-red-300 text-red-900 @enderror"
                                        wire:model.live="basic_salary">
                                    @error('basic_salary')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="overtime" class="block text-sm font-medium text-gray-700">Overtime</label>
                                    <input type="number" id="overtime" step="0.01" min="0"
                                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('overtime') border-red-300 text-red-900 @enderror"
                                        wire:model.live="overtime">
                                    @error('overtime')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="bonuses" class="block text-sm font-medium text-gray-700">Bonus</label>
                                    <input type="number" id="bonuses" step="0.01" min="0"
                                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('bonuses') border-red-300 text-red-900 @enderror"
                                        wire:model.live="bonuses">
                                    @error('bonuses')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="allowances" class="block text-sm font-medium text-gray-700">Allowances</label>
                                    <input type="number" id="allowances" step="0.01" min="0"
                                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('allowances') border-red-300 text-red-900 @enderror"
                                        wire:model.live="allowances">
                                    @error('allowances')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Deductions -->
                        <div class="border-b border-gray-200 pb-4 mb-4">
                            <h4 class="text-md font-medium mb-2">Deductions</h4>
                            
                            <div class="space-y-3">
                                <div>
                                    <label for="tax" class="block text-sm font-medium text-gray-700">Tax</label>
                                    <input type="number" id="tax" step="0.01" min="0"
                                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('tax') border-red-300 text-red-900 @enderror"
                                        wire:model="tax" readonly>
                                    @error('tax')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="social_security" class="block text-sm font-medium text-gray-700">Social Security</label>
                                    <input type="number" id="social_security" step="0.01" min="0"
                                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('social_security') border-red-300 text-red-900 @enderror"
                                        wire:model="social_security" readonly>
                                    @error('social_security')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="deductions" class="block text-sm font-medium text-gray-700">Other Deductions</label>
                                    <input type="number" id="deductions" step="0.01" min="0"
                                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('deductions') border-red-300 text-red-900 @enderror"
                                        wire:model.live="deductions">
                                    @error('deductions')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Summary -->
                        <div class="md:col-span-2 border-b border-gray-200 pb-4 mb-4">
                            <h4 class="text-md font-medium mb-2">Payroll Summary</h4>
                            
                            <div class="bg-gray-50 p-4 rounded-md">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Basic Salary</p>
                                        <p class="font-medium">{{ number_format($basic_salary ?? 0, 2) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Allowances</p>
                                        <p class="font-medium">{{ number_format($allowances ?? 0, 2) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Overtime</p>
                                        <p class="font-medium">{{ number_format($overtime ?? 0, 2) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Bonuses</p>
                                        <p class="font-medium">{{ number_format($bonuses ?? 0, 2) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Gross Salary</p>
                                        <p class="font-medium text-green-600">{{ number_format(($basic_salary + $allowances + $overtime + $bonuses) ?? 0, 2) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Total Deductions</p>
                                        <p class="font-medium text-red-600">{{ number_format(($deductions + $tax + $social_security) ?? 0, 2) }}</p>
                                    </div>
                                    <div class="col-span-2 pt-2 border-t">
                                        <p class="text-sm text-gray-500">Net Salary</p>
                                        <p class="font-medium text-lg text-blue-700">{{ number_format($net_salary ?? 0, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="md:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea id="notes" rows="3"
                                class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('notes') border-red-300 text-red-900 @enderror"
                                wire:model.live="notes"></textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
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

    <!-- Modal de Visualização do Payroll -->
    @if($showViewModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl p-6 max-h-screen overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">
                        <i class="fas fa-file-invoice-dollar mr-2"></i>
                        Detalhes da Folha de Pagamento
                    </h3>
                    <button type="button" class="text-gray-500 hover:text-gray-700 text-xl" wire:click="closeViewModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                @if($currentPayroll)
                <div class="space-y-6">
                    <!-- Informações básicas -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-50 rounded-lg">
                        <div>
                            <h4 class="font-medium text-gray-700 mb-2">Informações do Funcionário</h4>
                            <div class="space-y-1">
                                <p class="text-sm"><span class="font-medium">Nome:</span> {{ $currentPayroll->employee->full_name }}</p>
                                <p class="text-sm"><span class="font-medium">ID:</span> {{ $currentPayroll->employee->employee_id }}</p>
                                <p class="text-sm"><span class="font-medium">Departamento:</span> {{ $currentPayroll->employee->department->name ?? 'N/A' }}</p>
                                <p class="text-sm"><span class="font-medium">Cargo:</span> {{ $currentPayroll->employee->position->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-700 mb-2">Informações do Pagamento</h4>
                            <div class="space-y-1">
                                <p class="text-sm"><span class="font-medium">Período:</span> 
                                    @if($currentPayroll->payrollPeriod && $currentPayroll->payrollPeriod->start_date && $currentPayroll->payrollPeriod->end_date)
                                        {{ $currentPayroll->payrollPeriod->name }} ({{ $currentPayroll->payrollPeriod->start_date->format('d/m/Y') }} - {{ $currentPayroll->payrollPeriod->end_date->format('d/m/Y') }})
                                    @else
                                        N/A
                                    @endif
                                </p>
                                <p class="text-sm"><span class="font-medium">Status:</span> <span class="px-2 py-1 text-xs rounded-full 
                                    {{ $currentPayroll->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : 
                                    ($currentPayroll->status === 'approved' ? 'bg-blue-100 text-blue-800' : 
                                    'bg-green-100 text-green-800') }}">{{ ucfirst($currentPayroll->status) }}</span></p>
                                <p class="text-sm"><span class="font-medium">Data de Pagamento:</span> {{ $currentPayroll->payment_date ? date('d/m/Y', strtotime($currentPayroll->payment_date)) : 'N/A' }}</p>
                                <p class="text-sm"><span class="font-medium">Método de Pagamento:</span> {{ ucfirst(str_replace('_', ' ', $currentPayroll->payment_method)) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Rendimentos e Deduções -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Rendimentos -->
                        <div class="border rounded-lg p-4">
                            <h4 class="font-medium text-gray-700 mb-3 border-b pb-2">Rendimentos</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm">Salário Base</span>
                                    <span class="text-sm font-medium">{{ number_format($currentPayroll->basic_salary, 2, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm">Subsídios</span>
                                    <span class="text-sm font-medium">{{ number_format($currentPayroll->allowances, 2, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm">Horas Extras</span>
                                    <span class="text-sm font-medium">{{ number_format($currentPayroll->overtime, 2, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm">Bônus</span>
                                    <span class="text-sm font-medium">{{ number_format($currentPayroll->bonuses, 2, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between items-center border-t pt-2 mt-2">
                                    <span class="text-sm font-medium">Total Rendimentos</span>
                                    <span class="text-sm font-bold">{{ number_format($currentPayroll->basic_salary + $currentPayroll->allowances + $currentPayroll->overtime + $currentPayroll->bonuses, 2, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Deduções -->
                        <div class="border rounded-lg p-4">
                            <h4 class="font-medium text-gray-700 mb-3 border-b pb-2">Deduções</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm">Imposto de Renda (IRT)</span>
                                    <span class="text-sm font-medium">{{ number_format($currentPayroll->tax, 2, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm">Segurança Social (INSS)</span>
                                    <span class="text-sm font-medium">{{ number_format($currentPayroll->social_security, 2, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm">Outras Deduções</span>
                                    <span class="text-sm font-medium">{{ number_format($currentPayroll->deductions, 2, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between items-center border-t pt-2 mt-2">
                                    <span class="text-sm font-medium">Total Deduções</span>
                                    <span class="text-sm font-bold">{{ number_format($currentPayroll->tax + $currentPayroll->social_security + $currentPayroll->deductions, 2, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Salário Líquido -->
                    <div class="bg-gray-100 p-4 rounded-lg">
                        <div class="flex justify-between items-center">
                            <h4 class="font-medium text-gray-700">Salário Líquido</h4>
                            <span class="text-lg font-bold">{{ number_format($currentPayroll->net_salary, 2, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Observações -->
                    @if($currentPayroll->remarks)
                    <div class="border-t pt-4">
                        <h4 class="font-medium text-gray-700 mb-2">Observações</h4>
                        <p class="text-sm text-gray-600">{{ $currentPayroll->remarks }}</p>
                    </div>
                    @endif
                </div>

                <!-- Botões de Ações -->
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        wire:click="closeViewModal">
                        Fechar
                    </button>
                    <button type="button"
                        wire:click="downloadPayslip({{ $currentPayroll->id }})"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <i class="fas fa-file-pdf mr-1"></i>
                        Baixar Contracheque
                    </button>
                </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Modal de Confirmação de Pagamento -->
    @if($showPayModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">
                        <i class="fas fa-money-bill-wave mr-2"></i>
                        Confirmar Pagamento
                    </h3>
                    <button type="button" class="text-gray-500 hover:text-gray-700 text-xl" wire:click="$set('showPayModal', false)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <p class="mb-4 text-gray-600">Você está confirmando que este pagamento foi realizado. Esta ação atualizará o status da folha de pagamento para "Pago".</p>

                <form wire:submit.prevent="pay">
                    <div class="mb-4">
                        <label for="payment_date" class="block text-sm font-medium text-gray-700">Data de Pagamento</label>
                        <input type="date" id="payment_date"
                            class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('payment_date') border-red-300 text-red-900 @enderror"
                            wire:model.live="payment_date">
                        @error('payment_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button"
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            wire:click="$set('showPayModal', false)">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Confirmar Pagamento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Adicionar botão de download na tabela de listagem -->
    <script>
        document.addEventListener('livewire:initialized', function () {
            Livewire.hook('element.initialized', ({ component, el }) => {
                if (component.name === 'hr.payroll') {
                    // Adicionar botões de download após renderizar
                    document.querySelectorAll('[data-payroll-id]').forEach(function(button) {
                        if (!button.dataset.initialized) {
                            button.dataset.initialized = true;
                            button.addEventListener('click', function(e) {
                                e.preventDefault();
                                Livewire.dispatch('downloadPayslip', { payrollId: button.dataset.payrollId });
                            });
                        }
                    });
                }
            });
        });
    </script>

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
