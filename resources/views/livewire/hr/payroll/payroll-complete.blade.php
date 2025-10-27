{{-- Modern Full Width Payroll Management Interface --}}
<div class="min-h-screen bg-gray-50">
    <div class="w-full h-full">
        <div class="flex flex-col min-h-screen">
            
            {{-- Header Section with Gradient - Full Width --}}
            <div class="bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 px-6 py-8 text-white flex-shrink-0">
                <div class="w-full flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="bg-white/20 backdrop-blur-sm rounded-lg p-3">
                            <i class="fas fa-money-bill-wave text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold">{{ __('messages.payroll_management') }}</h1>
                            <p class="text-blue-100 mt-1">{{ __('messages.payroll_management_description') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button
                            wire:click="openEmployeeSearch"
                            class="bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white px-6 py-3 rounded-lg font-medium transition-all duration-300 hover:scale-105 flex items-center space-x-2 border border-white/20"
                        >
                            <i class="fas fa-search text-lg"></i>
                            <span>{{ __('messages.process_payroll') }}</span>
                        </button>
                        <button
                            wire:click="exportPayroll"
                            class="bg-green-500/90 hover:bg-green-400 text-white px-6 py-3 rounded-lg font-medium transition-all duration-300 hover:scale-105 flex items-center space-x-2"
                        >
                            <i class="fas fa-file-export text-lg"></i>
                            <span>{{ __('messages.export') }}</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Stats Cards - Full Width --}}
            <div class="bg-white border-b border-gray-200 px-6 py-6 flex-shrink-0">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-gradient-to-br from-green-50 to-emerald-100 p-6 rounded-xl border border-green-200 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-600 font-medium text-sm">{{ __('messages.total_processed') }}</p>
                                <p class="text-3xl font-bold text-green-700">{{ number_format($payrolls->count()) }}</p>
                            </div>
                            <div class="bg-green-500 p-3 rounded-full">
                                <i class="fas fa-check text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-xl border border-blue-200 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-600 font-medium text-sm">{{ __('messages.pending_approval') }}</p>
                                <p class="text-3xl font-bold text-blue-700">{{ $payrolls->where('status', 'draft')->count() }}</p>
                            </div>
                            <div class="bg-blue-500 p-3 rounded-full">
                                <i class="fas fa-clock text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-6 rounded-xl border border-purple-200 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-600 font-medium text-sm">{{ __('messages.approved') }}</p>
                                <p class="text-3xl font-bold text-purple-700">{{ $payrolls->where('status', 'approved')->count() }}</p>
                            </div>
                            <div class="bg-purple-500 p-3 rounded-full">
                                <i class="fas fa-user-check text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-amber-50 to-yellow-100 p-6 rounded-xl border border-amber-200 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-amber-600 font-medium text-sm">{{ __('messages.paid') }}</p>
                                <p class="text-3xl font-bold text-amber-700">{{ $payrolls->where('status', 'paid')->count() }}</p>
                            </div>
                            <div class="bg-amber-500 p-3 rounded-full">
                                <i class="fas fa-money-bill text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filters Section - Full Width --}}
            <div class="bg-white border-b border-gray-200 px-6 py-6 flex-shrink-0">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-6 border border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-700 flex items-center">
                            <i class="fas fa-filter text-blue-500 mr-2"></i>
                            {{ __('messages.filters_and_search') }}
                        </h3>
                        <button
                            wire:click="resetFilters"
                            class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-colors flex items-center space-x-2"
                        >
                            <i class="fas fa-undo text-sm"></i>
                            <span class="text-sm font-medium">{{ __('messages.reset') }}</span>
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-search text-gray-400 mr-1"></i>
                                {{ __('messages.search_employee') }}
                            </label>
                            <div class="relative">
                                <input
                                    type="text"
                                    id="search"
                                    wire:model.live.debounce.300ms="search"
                                    placeholder="{{ __('messages.search_employee_placeholder') }}"
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                >
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="department_filter" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-building text-gray-400 mr-1"></i>
                                {{ __('messages.department') }}
                            </label>
                            <select
                                id="department_filter"
                                wire:model.live="filters.department_id"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                            >
                                <option value="">{{ __('messages.all_departments') }}</option>
                                @foreach(\App\Models\HR\Department::all() as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="month_filter" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar text-gray-400 mr-1"></i>
                                {{ __('messages.month') }}
                            </label>
                            <select
                                id="month_filter"
                                wire:model.live="filters.month"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                            >
                                <option value="">{{ __('messages.all_months') }}</option>
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">
                                        {{ \Carbon\Carbon::createFromDate(null, $i, 1)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label for="status_filter" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-flag text-gray-400 mr-1"></i>
                                {{ __('messages.status') }}
                            </label>
                            <select
                                id="status_filter"
                                wire:model.live="filters.status"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                            >
                                <option value="">{{ __('messages.all_status') }}</option>
                                <option value="draft">{{ __('messages.draft') }}</option>
                                <option value="approved">{{ __('messages.approved') }}</option>
                                <option value="paid">{{ __('messages.paid') }}</option>
                                <option value="cancelled">{{ __('messages.cancelled') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Table Section - Full Width --}}
            <div class="flex-1 bg-white px-6 py-6">
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2 cursor-pointer hover:text-blue-600 transition-colors" wire:click="sortBy('employee_id')">
                                            <i class="fas fa-user"></i>
                                            <span>{{ __('messages.employee') }}</span>
                                            @if($sortField === 'employee_id')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                            @else
                                                <i class="fas fa-sort text-gray-300"></i>
                                            @endif
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2 cursor-pointer hover:text-blue-600 transition-colors" wire:click="sortBy('created_at')">
                                            <i class="fas fa-calendar-alt"></i>
                                            <span>{{ __('messages.period') }}</span>
                                            @if($sortField === 'created_at')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                            @else
                                                <i class="fas fa-sort text-gray-300"></i>
                                            @endif
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2 cursor-pointer hover:text-blue-600 transition-colors" wire:click="sortBy('gross_salary')">
                                            <i class="fas fa-dollar-sign"></i>
                                            <span>{{ __('messages.gross_salary') }}</span>
                                            @if($sortField === 'gross_salary')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                            @else
                                                <i class="fas fa-sort text-gray-300"></i>
                                            @endif
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2 cursor-pointer hover:text-blue-600 transition-colors" wire:click="sortBy('total_deductions')">
                                            <i class="fas fa-minus-circle"></i>
                                            <span>{{ __('messages.deductions') }}</span>
                                            @if($sortField === 'total_deductions')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                            @else
                                                <i class="fas fa-sort text-gray-300"></i>
                                            @endif
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2 cursor-pointer hover:text-blue-600 transition-colors" wire:click="sortBy('net_salary')">
                                            <i class="fas fa-hand-holding-usd"></i>
                                            <span>{{ __('messages.net_salary') }}</span>
                                            @if($sortField === 'net_salary')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                            @else
                                                <i class="fas fa-sort text-gray-300"></i>
                                            @endif
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2 cursor-pointer hover:text-blue-600 transition-colors" wire:click="sortBy('status')">
                                            <i class="fas fa-flag"></i>
                                            <span>{{ __('messages.status') }}</span>
                                            @if($sortField === 'status')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                            @else
                                                <i class="fas fa-sort text-gray-300"></i>
                                            @endif
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-cogs"></i>
                                        <span>{{ __('messages.actions') }}</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($payrolls as $payroll)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                                                        <span class="text-white font-semibold text-sm">
                                                            {{ substr($payroll->employee->full_name ?? 'N/A', 0, 1) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $payroll->employee->full_name ?? 'N/A' }}</div>
                                                    <div class="text-sm text-gray-500">{{ $payroll->employee->employee_id ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $payroll->created_at->format('M Y') }}</div>
                                            <div class="text-sm text-gray-500">{{ $payroll->created_at->format('d/m/Y') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-green-600">{{ number_format($payroll->gross_salary ?? 0, 2) }} AOA</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-red-600">{{ number_format($payroll->total_deductions ?? 0, 2) }} AOA</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-blue-600">{{ number_format($payroll->net_salary ?? 0, 2) }} AOA</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                                {{ $payroll->status === 'draft' ? 'bg-yellow-100 text-yellow-800 border border-yellow-200' : 
                                                   ($payroll->status === 'approved' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 
                                                   ($payroll->status === 'paid' ? 'bg-green-100 text-green-800 border border-green-200' :
                                                   'bg-red-100 text-red-800 border border-red-200')) }}">
                                                <i class="fas {{ $payroll->status === 'draft' ? 'fa-clock' : 
                                                               ($payroll->status === 'approved' ? 'fa-check' : 
                                                               ($payroll->status === 'paid' ? 'fa-money-bill' : 'fa-times')) }} mr-1"></i>
                                                {{ ucfirst($payroll->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                            <div class="flex items-center justify-center space-x-2">
                                                <button 
                                                    wire:click="view({{ $payroll->id }})"
                                                    class="text-blue-600 hover:text-blue-900 p-2 rounded-lg hover:bg-blue-50 transition-colors"
                                                    title="{{ __('messages.view') }}"
                                                >
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                
                                                @if($payroll->status === 'draft')
                                                    <button 
                                                        wire:click="edit({{ $payroll->id }})"
                                                        class="text-green-600 hover:text-green-900 p-2 rounded-lg hover:bg-green-50 transition-colors"
                                                        title="{{ __('messages.edit') }}"
                                                    >
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                @endif
                                                
                                                @if($payroll->status === 'approved')
                                                    <button 
                                                        wire:click="markAsPaid({{ $payroll->id }})"
                                                        class="text-purple-600 hover:text-purple-900 p-2 rounded-lg hover:bg-purple-50 transition-colors"
                                                        title="{{ __('messages.mark_as_paid') }}"
                                                    >
                                                        <i class="fas fa-money-bill"></i>
                                                    </button>
                                                @endif
                                                
                                                <button 
                                                    wire:click="downloadPayslip({{ $payroll->id }})"
                                                    class="text-indigo-600 hover:text-indigo-900 p-2 rounded-lg hover:bg-indigo-50 transition-colors"
                                                    title="{{ __('messages.download_payslip') }}"
                                                >
                                                    <i class="fas fa-file-pdf"></i>
                                                </button>
                                                
                                                @if($payroll->status === 'draft')
                                                    <button 
                                                        wire:click="confirmDelete({{ $payroll->id }})"
                                                        class="text-red-600 hover:text-red-900 p-2 rounded-lg hover:bg-red-50 transition-colors"
                                                        title="{{ __('messages.delete') }}"
                                                    >
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-500">
                                                <i class="fas fa-money-bill-wave text-gray-300 text-6xl mb-4"></i>
                                                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('messages.no_payrolls_found') }}</h3>
                                                <p class="text-gray-500">{{ __('messages.start_by_processing_payroll') }}</p>
                                                <button
                                                    wire:click="openEmployeeSearch"
                                                    class="mt-4 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2"
                                                >
                                                    <i class="fas fa-plus"></i>
                                                    <span>{{ __('messages.process_payroll') }}</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if(method_exists($payrolls, 'links'))
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                            {{ $payrolls->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Include Advanced Payroll Processing Modal --}}
    @include('livewire.hr.payroll-process-modal')

    {{-- Success/Error Messages --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" 
             class="fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-50">
            {{ session('message') }}
        </div>
    @endif
</div>
