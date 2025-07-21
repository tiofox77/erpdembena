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

            {{-- Main Content Area - Payroll Table --}}
            <div class="flex-1 bg-white px-6 py-6">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-6 border border-gray-200 mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 flex items-center mb-4">
                        <i class="fas fa-table text-blue-500 mr-2"></i>
                        {{ __('messages.payroll_records') }}
                    </h3>
                    
                    {{-- Payroll Table --}}
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
                                                <span>Departamento</span>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1">
                                                <span><i class="fas fa-clock text-gray-400 mr-1"></i>Presenças</span>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1">
                                                <span><i class="fas fa-calendar-alt text-gray-400 mr-1"></i>Licenças</span>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div wire:click="sortBy('gross_salary')" class="cursor-pointer flex items-center space-x-1">
                                                <span>Bruto</span>
                                                @if($sortField === 'gross_salary')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                                @else
                                                    <i class="fas fa-sort"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Deduções
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div wire:click="sortBy('net_salary')" class="cursor-pointer flex items-center space-x-1">
                                                <span>Líquido</span>
                                                @if($sortField === 'net_salary')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                                @else
                                                    <i class="fas fa-sort"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div wire:click="sortBy('status')" class="cursor-pointer flex items-center space-x-1">
                                                <span>Status</span>
                                                @if($sortField === 'status')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                                @else
                                                    <i class="fas fa-sort"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium">
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
                                                <div class="flex items-center">
                                                    <i class="fas fa-clock text-blue-500 mr-1"></i>
                                                    <span>{{ number_format($payroll->attendance_hours ?? 0, 1) }} h</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <div class="flex flex-col">
                                                    <span>{{ number_format($payroll->leave_days ?? 0, 1) }} dias</span>
                                                    @if(($payroll->maternity_days ?? 0) > 0)
                                                        <span class="text-xs text-pink-600 font-medium">{{ number_format($payroll->maternity_days ?? 0, 1) }} maternidade</span>
                                                    @endif
                                                </div>
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
                                             <td colspan="9" class="px-6 py-12 text-center">
                                                <div class="flex flex-col items-center justify-center text-gray-500">
                                                    <i class="fas fa-money-bill-wave text-gray-400 text-4xl mb-4"></i>
                                                     <span class="text-lg font-medium">Nenhum registo de folha de pagamento encontrado</span>
                                                    <p class="text-sm mt-2">
                                                        @if($search || !empty($filters['period_id']) || !empty($filters['department_id']) || !empty($filters['status']))
                                                            Nenhum registo corresponde aos critérios de pesquisa. Tente ajustar os filtros.
                                                            <button
                                                                wire:click="resetFilters"
                                                                class="text-blue-500 hover:text-blue-700 underline ml-1"
                                                            >
                                                                Limpar filtros
                                                            </button>
                                                        @else
                                                            Ainda não existem registos de folha de pagamento no sistema. Clique em "Criar Folha" para adicionar.
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

    <!-- Modal de Processamento de Payroll -->
    @include('livewire.hr.payroll-process-modal')
    
    <!-- Modal de Visualização Moderna do Payroll -->
    @if($showViewModal && $currentPayroll)
        <div class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl max-h-[95vh] flex flex-col">
                
                <!-- Header com Gradiente -->
                <div class="bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-800 px-4 sm:px-6 lg:px-8 py-4 sm:py-6 text-white rounded-t-2xl">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-3 sm:space-x-4 flex-1 min-w-0">
                            <div class="bg-white/20 backdrop-blur-sm rounded-xl p-2 sm:p-3 flex-shrink-0">
                                <i class="fas fa-file-invoice-dollar text-lg sm:text-2xl"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="text-lg sm:text-xl lg:text-2xl font-bold truncate">Detalhes da Folha de Pagamento</h3>
                                <p class="text-blue-100 text-xs sm:text-sm mt-1 truncate">{{ $currentPayroll->employee->full_name }} • {{ $currentPayroll->payrollPeriod->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <button type="button" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg p-2 transition-all flex-shrink-0 ml-2" wire:click="closeViewModal">
                            <i class="fas fa-times text-lg sm:text-xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Conteúdo Principal -->
                <div class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto">
                    
                    <!-- Cards de Informações Principais -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                        
                        <!-- Card Funcionário -->
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-100 rounded-xl p-6 border border-blue-200">
                            <div class="flex items-center mb-4">
                                <div class="bg-blue-500 rounded-lg p-2 mr-3">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                                <h4 class="font-semibold text-gray-800">Funcionário</h4>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Nome:</span>
                                    <span class="text-sm font-medium text-gray-800">{{ $currentPayroll->employee->full_name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">ID:</span>
                                    <span class="text-sm font-medium text-gray-800">{{ $currentPayroll->employee->id }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Departamento:</span>
                                    <span class="text-sm font-medium text-gray-800">{{ $currentPayroll->employee->department->name ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Cargo:</span>
                                    <span class="text-sm font-medium text-gray-800">{{ $currentPayroll->employee->position->name ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Período -->
                        <div class="bg-gradient-to-br from-green-50 to-emerald-100 rounded-xl p-6 border border-green-200">
                            <div class="flex items-center mb-4">
                                <div class="bg-green-500 rounded-lg p-2 mr-3">
                                    <i class="fas fa-calendar-alt text-white"></i>
                                </div>
                                <h4 class="font-semibold text-gray-800">Período</h4>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Período:</span>
                                    <span class="text-sm font-medium text-gray-800">{{ $currentPayroll->payrollPeriod->name ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Início:</span>
                                    <span class="text-sm font-medium text-gray-800">{{ $currentPayroll->payrollPeriod?->start_date?->format('d/m/Y') ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Fim:</span>
                                    <span class="text-sm font-medium text-gray-800">{{ $currentPayroll->payrollPeriod?->end_date?->format('d/m/Y') ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Horas:</span>
                                    <span class="text-sm font-medium text-gray-800">{{ number_format((float)$currentPayroll->attendance_hours, 1) }}h</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Status -->
                        <div class="bg-gradient-to-br from-purple-50 to-violet-100 rounded-xl p-6 border border-purple-200">
                            <div class="flex items-center mb-4">
                                <div class="bg-purple-500 rounded-lg p-2 mr-3">
                                    <i class="fas fa-info-circle text-white"></i>
                                </div>
                                <h4 class="font-semibold text-gray-800">Status</h4>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Status:</span>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                        {{ $currentPayroll->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : 
                                        ($currentPayroll->status === 'approved' ? 'bg-blue-100 text-blue-800' : 
                                        'bg-green-100 text-green-800') }}">
                                        <i class="fas fa-circle text-xs mr-1"></i>
                                        {{ ucfirst($currentPayroll->status) }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Gerado em:</span>
                                    <span class="text-sm font-medium text-gray-800">{{ $currentPayroll->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Pagamento:</span>
                                    <span class="text-sm font-medium text-gray-800">{{ $currentPayroll->payment_method ? ucfirst(str_replace('_', ' ', $currentPayroll->payment_method)) : 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Conta:</span>
                                    <span class="text-sm font-medium text-gray-800">{{ $currentPayroll->bank_account ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Breakdown Detalhado dos Componentes -->
                    @if($currentPayroll->payrollItems && $currentPayroll->payrollItems->count() > 0)
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-800 mb-6 flex items-center">
                            <i class="fas fa-list-ul text-blue-500 mr-2"></i>
                            Breakdown Detalhado dos Componentes
                        </h4>
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            
                            <!-- Rendimentos Detalhados -->
                            <div class="bg-green-50 rounded-xl p-6 border border-green-200">
                                <h5 class="font-semibold text-green-800 mb-4 flex items-center">
                                    <i class="fas fa-plus-circle text-green-600 mr-2"></i>
                                    Rendimentos
                                </h5>
                                <div class="space-y-3">
                                    @php $totalEarnings = 0; @endphp
                                    @foreach($currentPayroll->payrollItems->whereIn('type', ['earning', 'allowance', 'bonus']) as $item)
                                        @php $totalEarnings += (float)$item->amount; @endphp
                                        <div class="flex justify-between items-center py-2 border-b border-green-200 last:border-b-0">
                                            <div>
                                                <span class="text-sm font-medium text-gray-800">{{ $item->name }}</span>
                                                @if($item->description)
                                                    <p class="text-xs text-gray-600 mt-1">{{ $item->description }}</p>
                                                @endif
                                                <div class="flex items-center mt-1">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                        {{ $item->type === 'earning' ? 'bg-blue-100 text-blue-800' : 
                                                        ($item->type === 'allowance' ? 'bg-purple-100 text-purple-800' : 
                                                        'bg-orange-100 text-orange-800') }}">
                                                        {{ ucfirst($item->type) }}
                                                    </span>
                                                    @if($item->is_taxable)
                                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                            Tributável
                                                        </span>
                                                    @else
                                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                            Isento
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <span class="text-sm font-bold text-green-700">{{ number_format((float)$item->amount, 2, ',', '.') }} AOA</span>
                                        </div>
                                    @endforeach
                                    <div class="flex justify-between items-center pt-3 border-t-2 border-green-300">
                                        <span class="font-semibold text-green-800">Total Rendimentos:</span>
                                        <span class="text-lg font-bold text-green-700">{{ number_format($totalEarnings, 2, ',', '.') }} AOA</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Deduções Detalhadas -->
                            <div class="bg-red-50 rounded-xl p-6 border border-red-200">
                                <h5 class="font-semibold text-red-800 mb-4 flex items-center">
                                    <i class="fas fa-minus-circle text-red-600 mr-2"></i>
                                    Deduções
                                </h5>
                                <div class="space-y-3">
                                    @php $totalDeductions = 0; @endphp
                                    @foreach($currentPayroll->payrollItems->whereIn('type', ['deduction', 'tax']) as $item)
                                        @php $totalDeductions += abs((float)$item->amount); @endphp
                                        <div class="flex justify-between items-center py-2 border-b border-red-200 last:border-b-0">
                                            <div>
                                                <span class="text-sm font-medium text-gray-800">{{ $item->name }}</span>
                                                @if($item->description)
                                                    <p class="text-xs text-gray-600 mt-1">{{ $item->description }}</p>
                                                @endif
                                                <div class="flex items-center mt-1">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                        {{ $item->type === 'tax' ? 'bg-red-100 text-red-800' : 'bg-orange-100 text-orange-800' }}">
                                                        {{ $item->type === 'tax' ? 'Imposto' : 'Dedução' }}
                                                    </span>
                                                </div>
                                            </div>
                                            <span class="text-sm font-bold text-red-700">-{{ number_format(abs((float)$item->amount), 2, ',', '.') }} AOA</span>
                                        </div>
                                    @endforeach
                                    <div class="flex justify-between items-center pt-3 border-t-2 border-red-300">
                                        <span class="font-semibold text-red-800">Total Deduções:</span>
                                        <span class="text-lg font-bold text-red-700">-{{ number_format($totalDeductions, 2, ',', '.') }} AOA</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Resumo Final -->
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-6 border border-gray-200 mb-6">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-calculator text-blue-500 mr-2"></i>
                            Resumo Final
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="text-center">
                                <div class="bg-green-100 rounded-lg p-4">
                                    <i class="fas fa-arrow-up text-green-600 text-2xl mb-2"></i>
                                    <p class="text-sm text-gray-600 mb-1">Total Bruto</p>
                                    <p class="text-xl font-bold text-green-700">{{ number_format((float)$currentPayroll->basic_salary + (float)$currentPayroll->allowances + (float)$currentPayroll->overtime + (float)$currentPayroll->bonuses, 2, ',', '.') }} AOA</p>
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="bg-red-100 rounded-lg p-4">
                                    <i class="fas fa-arrow-down text-red-600 text-2xl mb-2"></i>
                                    <p class="text-sm text-gray-600 mb-1">Total Deduções</p>
                                    <p class="text-xl font-bold text-red-700">{{ number_format((float)$currentPayroll->tax + (float)$currentPayroll->social_security + (float)$currentPayroll->deductions, 2, ',', '.') }} AOA</p>
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="bg-blue-100 rounded-lg p-4">
                                    <i class="fas fa-wallet text-blue-600 text-2xl mb-2"></i>
                                    <p class="text-sm text-gray-600 mb-1">Salário Líquido</p>
                                    <p class="text-2xl font-bold text-blue-700">{{ number_format((float)$currentPayroll->net_salary, 2, ',', '.') }} AOA</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Observações -->
                    @if($currentPayroll->remarks)
                    <div class="bg-yellow-50 rounded-xl p-6 border border-yellow-200">
                        <h4 class="font-semibold text-yellow-800 mb-3 flex items-center">
                            <i class="fas fa-sticky-note text-yellow-600 mr-2"></i>
                            Observações
                        </h4>
                        <p class="text-sm text-gray-700 leading-relaxed">{{ $currentPayroll->remarks }}</p>
                    </div>
                    @endif
                </div>

                <!-- Footer com Botões -->
                <div class="bg-gray-50 px-4 sm:px-8 py-4 sm:py-6 border-t border-gray-200 rounded-b-2xl flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="text-xs sm:text-sm text-gray-500 text-center sm:text-left">
                        <i class="fas fa-clock mr-1"></i>
                        Gerado em {{ $currentPayroll->created_at->format('d/m/Y às H:i') }}
                    </div>
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3 w-full sm:w-auto">
                        <button type="button"
                            class="w-full sm:w-auto px-4 sm:px-6 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all"
                            wire:click="closeViewModal">
                            <i class="fas fa-times mr-2"></i>
                            Fechar
                        </button>
                        <button type="button"
                            wire:click="downloadPayslip({{ $currentPayroll->id }})"
                            class="w-full sm:w-auto px-4 sm:px-6 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all">
                            <i class="fas fa-file-pdf mr-2"></i>
                            Baixar Recibo
                        </button>
                    </div>
                </div>
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


    {{-- Include Advanced Payroll Processing Modal --}}
    @include('livewire.hr.payroll-process-modal')
</div>
