{{-- Include Employee Search Modal --}}
@include('livewire.hr.payroll.Modals._SearchEmployeeModal')

{{-- Payroll Processing Modal --}}
<style>
    /* Desabilitar overlay escuro padrão do Livewire nesta modal */
    [wire\:loading\.delay\.none\.grid] {
        opacity: 0 !important;
        pointer-events: none !important;
    }
</style>

@if($selectedEmployee)
<div x-data="{ open: @entangle('showProcessModal') }" 
     x-show="open" 
     x-cloak 
     class="fixed inset-0 z-50 overflow-hidden" 
     role="dialog" 
     aria-modal="true" 
     aria-labelledby="modal-title"
     x-transition:enter="transition ease-out duration-300" 
     x-transition:enter-start="opacity-0" 
     x-transition:enter-end="opacity-100" 
     x-transition:leave="transition ease-in duration-200" 
     x-transition:leave-start="opacity-100" 
     x-transition:leave-end="opacity-0">

    {{-- Background Overlay --}}
    <div class="absolute inset-0 bg-gradient-to-br from-gray-900/80 via-blue-900/70 to-gray-900/90 backdrop-blur-sm"></div>

    {{-- Modal Container --}}
    <div class="relative flex items-start justify-center min-h-screen p-2 sm:p-4 lg:p-6">
        <div class="w-full max-w-7xl bg-white rounded-2xl shadow-2xl transform transition-all duration-300 ease-in-out my-2 sm:my-4 lg:my-8 flex flex-col h-[90vh] max-h-screen" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="transform opacity-0 scale-95" 
             x-transition:enter-end="transform opacity-100 scale-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="transform opacity-100 scale-100" 
             x-transition:leave-end="transform opacity-0 scale-95">
            
            {{-- Header --}}
            <div class="bg-gradient-to-r from-green-600 via-emerald-600 to-teal-700 p-4 lg:p-6 text-white flex-shrink-0 rounded-t-2xl">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center space-x-3 lg:space-x-4">
                        <div class="bg-white/20 p-2 lg:p-3 rounded-lg">
                            <i class="fas fa-calculator text-lg lg:text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-lg lg:text-2xl font-bold">{{ __('messages.process_payroll') }}</h2>
                            <p class="text-green-100 text-sm lg:text-base">{{ $selectedEmployee->full_name }} - {{ $selected_month }}/{{ $selected_year }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 lg:space-x-3">
                        <button
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {{-- Search Input --}}
                        <div class="lg:col-span-2">
                            <div class="relative">
                                <input
                                    type="text"
                                    wire:model.live.debounce.300ms="employeeSearch"
                                    placeholder="{{ __('messages.search_employee_by_name_id_email_phone') }}"
                                    class="w-full pl-12 pr-4 py-4 text-lg border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                    autofocus
                                >
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400 text-lg"></i>
                                </div>
                                @if($employeeSearch)
                                    <button 
                                        wire:click="$set('employeeSearch', '')"
                                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600"
                                    >
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                        
                        {{-- Reset Filters Button --}}
                        <div class="flex items-end">
                            <button
                                wire:click="resetEmployeeSearchFilters"
                                class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-4 rounded-xl transition-all duration-200 flex items-center justify-center space-x-2"
                            >
                                <i class="fas fa-undo text-gray-500"></i>
                                <span>{{ __('messages.reset_filters') }}</span>
                            </button>
                        </div>
                    </div>
                    
                    {{-- Advanced Filters Row --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
                        {{-- Department Filter --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-building text-blue-500 mr-1"></i>
                                {{ __('messages.department') }}
                            </label>
                            <select 
                                wire:model.live="departmentFilter"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="">{{ __('messages.all_departments') }}</option>
                                @foreach(\App\Models\HR\Department::all() as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        {{-- Status Filter --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-user-check text-green-500 mr-1"></i>
                                {{ __('messages.status') }}
                            </label>
                            <select 
                                wire:model.live="statusFilter"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="">{{ __('messages.all_statuses') }}</option>
                                <option value="active">{{ __('messages.active') }}</option>
                                <option value="inactive">{{ __('messages.inactive') }}</option>
                                <option value="terminated">{{ __('messages.terminated') }}</option>
                                <option value="suspended">{{ __('messages.suspended') }}</option>
                            </select>
                        </div>
                        
                        {{-- Sort By --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-sort text-purple-500 mr-1"></i>
                                {{ __('messages.sort_by') }}
                            </label>
                            <select 
                                wire:model.live="employeeSortField"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="full_name">{{ __('messages.name') }}</option>
                                <option value="id_card">{{ __('messages.id_card') }}</option>
                                <option value="hire_date">{{ __('messages.hire_date') }}</option>
                                @if($this->canViewSalaryDetails())
                                <option value="base_salary">{{ __('messages.base_salary') }}</option>
                                @endif
                            </select>
                        </div>
                        
                        {{-- Sort Order --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-arrow-up-down text-orange-500 mr-1"></i>
                                {{ __('messages.order') }}
                            </label>
                            <select 
                                wire:model.live="employeeSortDirection"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="asc">{{ __('messages.ascending') }}</option>
                                <option value="desc">{{ __('messages.descending') }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Selected Period Information --}}
                @if($selectedPayrollPeriod)
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4 mb-8">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-calendar-check text-blue-600"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900">{{ __('payroll.selected_period') }}</h4>
                                <div class="flex items-center space-x-4 text-sm text-gray-600 mt-1">
                                    <span class="font-medium text-blue-700">{{ $selectedPayrollPeriod->name }}</span>
                                    <span class="text-gray-400">•</span>
                                    <span>{{ \Carbon\Carbon::parse($selectedPayrollPeriod->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($selectedPayrollPeriod->end_date)->format('d/m/Y') }}</span>
                                    @if($selectedPayrollPeriod->payment_date)
                                        <span class="text-gray-400">•</span>
                                        <span>{{ __('payroll.payment') }}: {{ \Carbon\Carbon::parse($selectedPayrollPeriod->payment_date)->format('d/m/Y') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                @if($selectedPayrollPeriod->status === 'open')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <div class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></div>
                                        {{ __('payroll.status_open') }}
                                    </span>
                                @elseif($selectedPayrollPeriod->status === 'processing')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <div class="w-1.5 h-1.5 bg-yellow-500 rounded-full mr-1.5"></div>
                                        {{ __('payroll.status_processing') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <i class="fas fa-lock text-xs mr-1.5"></i>
                                        {{ __('payroll.status_closed') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Search Results --}}
                @if(count($searchResults) > 0)
                    <div class="space-y-6">
                        {{-- Results Header with Pagination Info --}}
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-users text-blue-500 mr-2"></i>
                                {{ __('messages.employees') }} 
                                <span class="ml-2 px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                                    {{ $totalResults }} {{ __('messages.total') }}
                                </span>
                            </h3>
                            
                            {{-- Results per page selector --}}
                            <div class="flex items-center space-x-3">
                                <label class="text-sm text-gray-600">{{ __('messages.per_page') }}:</label>
                                <select 
                                    wire:model.live="resultsPerPage"
                                    class="px-3 py-1 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>
                        
                        {{-- Employee Grid with Enhanced Cards --}}
                        <div class="grid gap-4 max-h-96 overflow-y-auto pr-2" style="scrollbar-width: thin; scrollbar-color: #3B82F6 #E5E7EB;">
                            @foreach($searchResults as $employee)
                                <div 
                                    wire:click="selectEmployee({{ $employee['id'] }})"
                                    class="bg-white border border-gray-200 hover:border-blue-300 hover:shadow-lg p-6 rounded-xl cursor-pointer transition-all duration-300 hover:scale-[1.01]"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            {{-- Employee Avatar --}}
                                            <div class="relative">
                                                <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white w-16 h-16 rounded-full flex items-center justify-center font-bold text-xl">
                                                    {{ substr($employee['full_name'], 0, 1) }}
                                                </div>
                                                {{-- Status indicator --}}
                                                <div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full border-2 border-white
                                                    {{ $employee['employment_status'] === 'active' ? 'bg-green-500' : 
                                                       ($employee['employment_status'] === 'inactive' ? 'bg-yellow-500' : 'bg-red-500') }}">
                                                </div>
                                            </div>
                                            
                                            {{-- Employee Info --}}
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2 mb-1">
                                                    <h4 class="text-lg font-semibold text-gray-900">{{ $employee['full_name'] }}</h4>
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                                                        {{ $employee['employment_status'] === 'active' ? 'bg-green-100 text-green-800' : 
                                                           ($employee['employment_status'] === 'inactive' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                        {{ __("messages.{$employee['employment_status']}") }}
                                                    </span>
                                                </div>
                                                
                                                {{-- Employee Details Grid --}}
                                                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 text-sm text-gray-600">
                                                    <span class="flex items-center">
                                                        <i class="fas fa-id-badge text-blue-500 mr-1.5 w-4"></i>
                                                        {{ $employee['id_card'] }}
                                                    </span>
                                                    <span class="flex items-center">
                                                        <i class="fas fa-building text-green-500 mr-1.5 w-4"></i>
                                                        {{ Str::limit($employee['department_name'], 15) ?? 'N/A' }}
                                                    </span>
                                                    <span class="flex items-center">
                                                        <i class="fas fa-briefcase text-purple-500 mr-1.5 w-4"></i>
                                                        {{ Str::limit($employee['position_name'], 15) ?? 'N/A' }}
                                                    </span>
                                                    <span class="flex items-center">
                                                        <i class="fas fa-calendar text-orange-500 mr-1.5 w-4"></i>
                                                        {{ $employee['hire_date'] }}
                                                    </span>
                                                    <span class="flex items-center">
                                                        <i class="fas fa-envelope text-blue-500 mr-1.5 w-4"></i>
                                                        {{ Str::limit($employee['email'], 20) ?? 'N/A' }}
                                                    </span>
                                                    <span class="flex items-center">
                                                        <i class="fas fa-phone text-green-500 mr-1.5 w-4"></i>
                                                        {{ $employee['phone'] ?? 'N/A' }}
                                                    </span>
                                                    @if($this->canViewSalaryDetails())
                                                    <span class="flex items-center font-medium">
                                                        <i class="fas fa-money-bill text-green-600 mr-1.5 w-4"></i>
                                                        {{ number_format($employee['base_salary'], 0, ',', '.') }} AOA
                                                    </span>
                                                    @else
                                                    <span class="flex items-center font-medium">
                                                        <i class="fas fa-eye-slash text-gray-400 mr-1.5 w-4"></i>
                                                        <span class="text-gray-500">{{ __('payroll.salary_hidden') }}</span>
                                                    </span>
                                                    @endif
                                                    <span class="flex items-center justify-end">
                                                        <i class="fas fa-chevron-right text-blue-500 text-lg"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        {{-- Pagination --}}
                        @if($totalPages > 1)
                            <div class="flex items-center justify-between border-t border-gray-200 pt-6">
                                <div class="flex items-center text-sm text-gray-600">
                                    {{ __('messages.showing') }} 
                                    <span class="font-medium mx-1">
                                        {{ (($currentPage - 1) * $resultsPerPage) + 1 }}
                                    </span>
                                    {{ __('messages.to') }}
                                    <span class="font-medium mx-1">
                                        {{ min($currentPage * $resultsPerPage, $totalResults) }}
                                    </span>
                                    {{ __('messages.of') }}
                                    <span class="font-medium mx-1">{{ $totalResults }}</span>
                                    {{ __('messages.results') }}
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    {{-- Previous Page --}}
                                    <button 
                                        wire:click="previousPage"
                                        @if($currentPage <= 1) disabled @endif
                                        class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                    >
                                        <i class="fas fa-chevron-left mr-1"></i>
                                        {{ __('messages.previous') }}
                                    </button>
                                    
                                    {{-- Page Numbers --}}
                                    @for($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++)
                                        <button 
                                            wire:click="goToPage({{ $i }})"
                                            class="px-3 py-2 text-sm font-medium border rounded-lg transition-colors
                                                {{ $i === $currentPage 
                                                    ? 'bg-blue-500 text-white border-blue-500' 
                                                    : 'text-gray-500 bg-white border-gray-300 hover:bg-gray-50 hover:text-gray-700' }}"
                                        >
                                            {{ $i }}
                                        </button>
                                    @endfor
                                    
                                    {{-- Next Page --}}
                                    <button 
                                        wire:click="nextPage"
                                        @if($currentPage >= $totalPages) disabled @endif
                                        class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                    >
                                        {{ __('messages.next') }}
                                        <i class="fas fa-chevron-right ml-1"></i>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                @elseif(strlen($employeeSearch) >= 2)
                    <div class="text-center py-12">
                        <div class="text-gray-400 mb-4">
                            <i class="fas fa-search text-4xl"></i>
                        </div>
                        <p class="text-gray-600 text-lg">{{ __('messages.no_employees_found') }}</p>
                        <p class="text-gray-500 text-sm">{{ __('messages.try_different_search_terms') }}</p>
                        <div class="mt-4">
                            <button 
                                wire:click="resetEmployeeSearchFilters"
                                class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-4 py-2 rounded-lg transition-colors"
                            >
                                <i class="fas fa-undo mr-1"></i>
                                {{ __('messages.reset_and_show_all') }}
                            </button>
                        </div>
                    </div>
                @elseif($totalResults === 0 && empty($employeeSearch))
                    <div class="text-center py-16">
                        <div class="text-gray-400 mb-4">
                            <i class="fas fa-users text-5xl"></i>
                        </div>
                        <h4 class="text-xl font-semibold text-gray-700 mb-2">{{ __('messages.no_employees_in_filters') }}</h4>
                        <p class="text-gray-500 mb-6">{{ __('messages.try_adjusting_filters') }}</p>
                        <button 
                            wire:click="resetEmployeeSearchFilters"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg transition-colors flex items-center mx-auto space-x-2"
                        >
                            <i class="fas fa-undo"></i>
                            <span>{{ __('messages.reset_all_filters') }}</span>
                        </button>
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="text-blue-400 mb-4">
                            <i class="fas fa-search text-4xl"></i>
                        </div>
                        <p class="text-gray-600 text-lg">{{ __('messages.search_or_browse_employees') }}</p>
                        <p class="text-gray-500 text-sm">{{ __('messages.use_filters_or_search_above') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif

{{-- Payroll Processing Modal --}}
@if($selectedEmployee)
<div x-data="{ open: @entangle('showProcessModal') }" 
                 x-show="open" 
                 x-cloak 
                 class="fixed inset-0 z-50 overflow-hidden" 
                 role="dialog" 
                 aria-modal="true" 
                 aria-labelledby="modal-title"
                 x-transition:enter="transition ease-out duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0">
        
                {{-- Background Overlay --}}
                <div class="absolute inset-0 bg-gradient-to-br from-gray-900/80 via-blue-900/70 to-gray-900/90 backdrop-blur-sm"></div>

                {{-- Modal Container --}}
                <div class="relative flex items-start justify-center min-h-screen p-2 sm:p-4 lg:p-6">
                    <div class="w-full max-w-7xl bg-white rounded-2xl shadow-2xl transform transition-all duration-300 ease-in-out my-2 sm:my-4 lg:my-8 flex flex-col h-[90vh] max-h-screen" 
                         x-transition:enter="transition ease-out duration-300" 
                         x-transition:enter-start="transform opacity-0 scale-95" 
                         x-transition:enter-end="transform opacity-100 scale-100" 
                         x-transition:leave="transition ease-in duration-200" 
                         x-transition:leave-start="transform opacity-100 scale-100" 
                         x-transition:leave-end="transform opacity-0 scale-95">
                        
                        {{-- Header --}}
                        <div class="bg-gradient-to-r from-green-600 via-emerald-600 to-teal-700 p-4 lg:p-6 text-white flex-shrink-0 rounded-t-2xl">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                <div class="flex items-center space-x-3 lg:space-x-4">
                                    <div class="bg-white/20 p-2 lg:p-3 rounded-lg">
                                        <i class="fas fa-calculator text-lg lg:text-2xl"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-lg lg:text-2xl font-bold">{{ __('messages.process_payroll') }}</h2>
                                        <p class="text-green-100 text-sm lg:text-base">{{ $selectedEmployee->full_name }} - {{ $selected_month }}/{{ $selected_year }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2 lg:space-x-3">
                                    <button 
                                        wire:click="goBackToEmployeeSelection"
                                        class="bg-white/10 hover:bg-white/20 px-3 py-2 lg:px-4 lg:py-2 rounded-lg transition-colors flex items-center space-x-2 text-sm lg:text-base"
                                        title="{{ __('messages.back_to_employee_selection') }}"
                                    >
                                        <i class="fas fa-arrow-left"></i>
                                        <span class="hidden sm:inline">{{ __('messages.back') }}</span>
                                    </button>
                                    <button 
                                        wire:click="calculatePayrollComponents"
                                        class="bg-white/10 hover:bg-white/20 px-3 py-2 lg:px-4 lg:py-2 rounded-lg transition-colors flex items-center space-x-2 text-sm lg:text-base"
                                    >
                                        <i class="fas fa-sync-alt"></i>
                                        <span class="hidden sm:inline">{{ __('messages.recalculate') }}</span>
                                    </button>
                                    <button 
                                        wire:click="closeProcessModal" 
                                        class="text-white/80 hover:text-white p-2"
                                    >
                                        <i class="fas fa-times text-lg lg:text-xl"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                {{-- Content --}}
                <div class="flex-1 overflow-hidden min-h-0">
                    <div class="h-full flex flex-col xl:flex-row">
                        {{-- Left Panel - Employee Info & Components --}}
                        <div class="w-full xl:w-1/2 border-b xl:border-b-0 xl:border-r border-gray-200 overflow-y-auto flex-1">
                            <div class="p-4 lg:p-6 space-y-4 lg:space-y-6">
                                {{-- Employee Summary Card --}}
                                <div class="bg-gradient-to-br from-blue-50 to-indigo-100 p-4 lg:p-6 rounded-2xl border border-blue-200">
                                    <h3 class="text-base lg:text-lg font-bold text-blue-800 mb-4 flex items-center">
                                        <i class="fas fa-user-circle mr-2"></i>
                                        {{ __('messages.employee_information') }}
                                    </h3>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 lg:gap-4">
                                        <div class="bg-white/60 p-3 rounded-lg">
                                            <p class="text-xs lg:text-sm text-blue-600 font-medium mb-1">{{ __('messages.employee_id') }}</p>
                                            <p class="text-sm lg:text-base text-blue-800 font-semibold">BI: {{ $selectedEmployee->id_card ?? 'N/A' }}</p>
                                        </div>
                                        <div class="bg-white/60 p-3 rounded-lg">
                                            <p class="text-xs lg:text-sm text-blue-600 font-medium mb-1">{{ __('messages.department') }}</p>
                                            <p class="text-sm lg:text-base text-blue-800 font-semibold">{{ $selectedEmployee->department->name ?? 'N/A' }}</p>
                                        </div>
                                        @if($this->canViewSalaryDetails())
                                        <div class="bg-white/60 p-3 rounded-lg">
                                            <p class="text-xs lg:text-sm text-blue-600 font-medium mb-1">{{ __('messages.basic_salary') }}</p>
                                            <p class="text-sm lg:text-base text-blue-800 font-semibold">{{ number_format($basic_salary ?? 0, 2) }} {{ __('messages.currency_aoa') }}</p>
                                        </div>
                                        <div class="bg-white/60 p-3 rounded-lg">
                                            <p class="text-xs lg:text-sm text-blue-600 font-medium mb-1">{{ __('messages.hourly_rate') }}</p>
                                            <p class="text-sm lg:text-base text-blue-800 font-semibold">{{ number_format($hourly_rate ?? 0, 2) }} {{ __('messages.currency_aoa') }}{{ __('messages.per_hour') }}</p>
                                        </div>
                                        @else
                                        <div class="bg-gray-100/60 p-3 rounded-lg col-span-2">
                                            <p class="text-xs lg:text-sm text-gray-500 font-medium mb-1">{{ __('payroll.salary_information') }}</p>
                                            <p class="text-sm lg:text-base text-gray-600 font-semibold">{{ __('payroll.salary_hidden') }}</p>
                                        </div>
                                        @endif
                                        <div class="bg-white/60 p-3 rounded-lg">
                                            <p class="text-xs lg:text-sm text-blue-600 font-medium mb-1">IRT ({{ __('messages.income_tax') }})</p>
                                            <p class="text-sm lg:text-base text-blue-800 font-semibold">{{ number_format($this->irtCalculationDetails['total_irt'] ?? 0, 2) }} {{ __('messages.currency_aoa') }}</p>
                                        </div>
                                        <div class="bg-white/60 p-3 rounded-lg">
                                            <p class="text-xs lg:text-sm text-blue-600 font-medium mb-1">INSS (3%)</p>
                                            <p class="text-sm lg:text-base text-blue-800 font-semibold">{{ number_format($inss_3_percent, 2) }} {{ __('messages.currency_aoa') }}</p>
                                        </div>
                                        <div class="bg-orange-50/60 p-3 rounded-lg border border-orange-200">
                                            <p class="text-xs lg:text-sm text-orange-600 font-medium mb-1">INSS (8%) - {{ __('payroll.illustrative_only') }}</p>
                                            <p class="text-sm lg:text-base text-orange-700 font-semibold">{{ number_format($inss_8_percent, 2) }} {{ __('messages.currency_aoa') }}</p>
                                            <p class="text-xs text-orange-500 mt-1">{{ __('payroll.calculated_from_main_salary') }}</p>
                                        </div>
                                        @if(isset($bonus_amount) && $bonus_amount > 0)
                                            <div class="bg-white/60 p-3 rounded-lg">
                                                <p class="text-xs lg:text-sm text-blue-600 font-medium mb-1">{{ __('messages.bonus_amount') }}</p>
                                                <p class="text-sm lg:text-base text-blue-800 font-semibold">{{ number_format($bonus_amount, 2) }} {{ __('messages.currency_aoa') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Subsidies and Additional Payments --}}
                                <div class="bg-gradient-to-br from-teal-50 to-emerald-50 p-6 rounded-xl border border-teal-200 shadow-sm">
                                    <h3 class="text-lg font-bold text-teal-800 mb-6 flex items-center">
                                        <i class="fas fa-gift mr-3 text-teal-600"></i>
                                        Subsidies and Additional Payments
                                    </h3>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                        {{-- Christmas Subsidy --}}
                                        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                                            <div class="flex items-start space-x-3 mb-3">
                                                <div class="flex-shrink-0 mt-1">
                                                    <input 
                                                        type="checkbox" 
                                                        wire:model.live="christmas_subsidy"
                                                        id="christmas_subsidy"
                                                        class="h-5 w-5 text-teal-600 focus:ring-teal-500 border-gray-300 rounded cursor-pointer"
                                                    >
                                                </div>
                                                <div class="flex-1">
                                                    <label for="christmas_subsidy" class="text-base font-semibold text-gray-800 block mb-1 cursor-pointer">
                                                        Christmas Subsidy
                                                    </label>
                                                    <p class="text-sm text-teal-600 mb-3">Additional Christmas payment: 50% do salário base</p>
                                                    <div class="text-2xl font-bold text-teal-700" wire:loading.class="opacity-50" wire:target="christmas_subsidy">
                                                        {{ number_format($this->christmasSubsidyAmount, 2) }} AOA
                                                    </div>
                                                    <div class="mt-2" wire:loading wire:target="christmas_subsidy">
                                                        <span class="text-xs text-gray-500">
                                                            <i class="fas fa-spinner fa-spin mr-1"></i>
                                                            Recalculando...
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Vacation Subsidy --}}
                                        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                                            <div class="flex items-start space-x-3 mb-3">
                                                <div class="flex-shrink-0 mt-1">
                                                    <input 
                                                        type="checkbox" 
                                                        wire:model.live="vacation_subsidy"
                                                        id="vacation_subsidy"
                                                        class="h-5 w-5 text-teal-600 focus:ring-teal-500 border-gray-300 rounded cursor-pointer"
                                                    >
                                                </div>
                                                <div class="flex-1">
                                                    <label for="vacation_subsidy" class="text-base font-semibold text-gray-800 block mb-1 cursor-pointer">
                                                        Vacation Subsidy
                                                    </label>
                                                    <p class="text-sm text-teal-600 mb-3">Additional vacation payment: 50% do salário base</p>
                                                    <div class="text-2xl font-bold text-teal-700" wire:loading.class="opacity-50" wire:target="vacation_subsidy">
                                                        {{ number_format($this->vacationSubsidyAmount, 2) }} AOA
                                                    </div>
                                                    <div class="mt-2" wire:loading wire:target="vacation_subsidy">
                                                        <span class="text-xs text-gray-500">
                                                            <i class="fas fa-spinner fa-spin mr-1"></i>
                                                            Recalculando...
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Additional Bonus --}}
                                    <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                                        <div class="flex items-center space-x-3 mb-4">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-teal-100 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-plus-circle text-teal-600 text-sm"></i>
                                                </div>
                                            </div>
                                            <div class="flex-1">
                                                <label class="text-base font-semibold text-gray-800 block">Additional Bonus</label>
                                            </div>
                                        </div>
                                        <div class="relative">
                                            <input 
                                                type="number" 
                                                step="0.01"
                                                min="0"
                                                wire:model.live="additional_bonus_amount"
                                                class="w-full px-4 py-3 text-lg border border-gray-200 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-gray-50"
                                                placeholder="AOA 0"
                                                value="{{ $additional_bonus_amount ?? 0 }}"
                                                x-on:blur="if($event.target.value === '') { $wire.set('additional_bonus_amount', 0) }"
                                            >
                                        </div>
                                    </div>
                                </div>

                                {{-- Attendance Summary Card --}}
                                <div class="bg-gradient-to-br from-green-50 to-emerald-100 p-6 rounded-2xl border border-green-200">
                                    <h3 class="text-lg font-bold text-green-800 mb-4 flex items-center">
                                        <i class="fas fa-clock mr-2"></i>
                                        {{ __('messages.attendance_summary') }} - {{ $selected_month }}/{{ $selected_year }}
                                    </h3>
                                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                                        <div class="bg-white/60 p-3 rounded-lg text-center">
                                            <p class="text-xs text-green-600 font-medium mb-1">{{ __('messages.total_working_days') }}</p>
                                            <p class="text-lg text-green-800 font-bold">{{ $total_working_days ?? 0 }}</p>
                                        </div>
                                        <div class="bg-white/60 p-3 rounded-lg text-center">
                                            <p class="text-xs text-green-600 font-medium mb-1">{{ __('messages.present_days') }}</p>
                                            <p class="text-lg text-green-800 font-bold">{{ $present_days ?? 0 }}</p>
                                        </div>
                                        <div class="bg-white/60 p-3 rounded-lg text-center">
                                            <p class="text-xs text-green-600 font-medium mb-1">{{ __('messages.absent_days') }}</p>
                                            <p class="text-lg text-orange-600 font-bold">{{ $absent_days ?? 0 }}</p>
                                        </div>
                                        <div class="bg-white/60 p-3 rounded-lg text-center">
                                            <p class="text-xs text-green-600 font-medium mb-1">{{ __('messages.late_arrivals') }}</p>
                                            <p class="text-lg text-yellow-600 font-bold">{{ $late_arrivals ?? 0 }}</p>
                                        </div>
                                    </div>
                                    
                                    {{-- Warning if no attendance records --}}
                                    @if(($present_days ?? 0) == 0)
                                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm text-yellow-700">
                                                    <strong>Atenção:</strong> Não foram encontrados registros de presença para este funcionário no período selecionado.
                                                </p>
                                                <p class="text-xs text-yellow-600 mt-1">
                                                    Verifique se:
                                                    <br>• As datas do período estão corretas
                                                    <br>• Os registros de ponto foram importados
                                                    <br>• O funcionário estava ativo no período
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    
                                    {{-- Total Hours Summary --}}
                                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                                        <div class="bg-white/60 p-3 rounded-lg text-center">
                                            <p class="text-xs text-green-600 font-medium mb-1">{{ __('messages.total_hours_worked') }}</p>
                                            <p class="text-lg text-green-800 font-bold">{{ number_format($total_attendance_hours ?? 0, 1) }}h</p>
                                        </div>
                                        @if($this->canViewSalaryDetails())
                                        <div class="bg-white/60 p-3 rounded-lg text-center">
                                            <p class="text-xs text-green-600 font-medium mb-1">{{ __('messages.regular_hours_pay') }}</p>
                                            <p class="text-lg text-green-800 font-bold">{{ number_format($regular_hours_pay ?? 0, 2) }} {{ __('messages.currency_aoa') }}</p>
                                        </div>
                                        <div class="bg-white/60 p-3 rounded-lg text-center">
                                            <p class="text-xs text-blue-600 font-medium mb-1">{{ __('messages.hourly_rate') }}</p>
                                            <p class="text-lg text-blue-800 font-bold">{{ number_format($hourly_rate ?? 0, 2) }} {{ __('messages.currency_aoa') }}/h</p>
                                        </div>
                                        <div class="bg-white/60 p-3 rounded-lg text-center">
                                            <p class="text-xs text-purple-600 font-medium mb-1">{{ __('messages.daily_rate') }}</p>
                                            <p class="text-lg text-purple-800 font-bold">{{ number_format($daily_rate ?? 0, 2) }} {{ __('messages.currency_aoa') }}/dia</p>
                                        </div>
                                        @else
                                        <div class="bg-gray-100/60 p-3 rounded-lg text-center col-span-3">
                                            <p class="text-xs text-gray-500 font-medium mb-1">{{ __('payroll.salary_rates') }}</p>
                                            <p class="text-lg text-gray-600 font-bold">{{ __('payroll.salary_hidden') }}</p>
                                        </div>
                                        @endif
                                    </div>
                                    
                                    {{-- Individual Attendance Records --}}
                                    @if(isset($attendanceData) && count($attendanceData) > 0)
                                        <div class="mt-4">
                                            <div class="flex items-center justify-between mb-2">
                                                <p class="text-sm text-green-700 font-medium">{{ __('messages.attendance_records') }} ({{ count($attendanceData) }})</p>
                                                <span class="text-xs text-green-600">{{ __('messages.click_to_expand') }}</span>
                                            </div>
                                            <div class="bg-white/40 rounded-lg max-h-32 overflow-y-auto">
                                                <div class="space-y-1 p-2">
                                                    @foreach($attendanceData as $attendance)
                                                        <div class="flex items-center justify-between text-xs bg-white/60 px-3 py-2 rounded border-l-4 
                                                                @if($attendance['status'] === 'present') border-green-500
                                                                @elseif($attendance['status'] === 'late') border-yellow-500
                                                                @elseif($attendance['status'] === 'half_day') border-blue-500
                                                                @else border-red-500
                                                                @endif">
                                                            <div>
                                                                <span class="font-medium text-gray-800">{{ $attendance['date'] ?? 'N/A' }}</span>
                                                                <span class="ml-2 px-2 py-1 rounded text-xs
                                                                        @if($attendance['status'] === 'present') bg-green-100 text-green-800
                                                                        @elseif($attendance['status'] === 'late') bg-yellow-100 text-yellow-800
                                                                        @elseif($attendance['status'] === 'half_day') bg-blue-100 text-blue-800
                                                                        @else bg-red-100 text-red-800
                                                                        @endif">
                                                                    {{ ucfirst($attendance['status']) }}
                                                                </span>
                                                                <span class="text-xs text-gray-500">({{ __('payroll.taxable') }})</span>
                                                            </div>
                                                            <div class="text-gray-600">
                                                                @if($attendance['time_in'] && $attendance['time_out'])
                                                                    {{ \Carbon\Carbon::parse($attendance['time_in'])->format('H:i') }} - 
                                                                    {{ \Carbon\Carbon::parse($attendance['time_out'])->format('H:i') }}
                                                                @else
                                                                    <span class="text-yellow-600">{{ __('messages.no_times_recorded') }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                                            <i class="fas fa-exclamation-triangle text-yellow-500 text-2xl mb-2"></i>
                                            <p class="text-yellow-700 font-medium">{{ __('messages.no_attendance_records_found') }}</p>
                                            <p class="text-yellow-600 text-sm mt-1">{{ __('messages.period') }}: {{ $selected_month }}/{{ $selected_year }}</p>
                                        </div>
                                    @endif
                                </div>

                                {{-- Overtime Records Card --}}
                                <div class="bg-gradient-to-br from-purple-50 to-violet-100 p-6 rounded-2xl border border-purple-200">
                                    <h3 class="text-lg font-bold text-purple-800 mb-4 flex items-center">
                                        <i class="fas fa-clock mr-2"></i>
                                        {{ __('messages.overtime_records') }}
                                    </h3>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-purple-600 font-medium">{{ __('messages.total_overtime_hours') }}</p>
                                            <p class="text-purple-800 font-semibold">{{ number_format($total_overtime_hours ?? 0, 2) }}h</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-purple-600 font-medium">{{ __('messages.overtime_amount') }}</p>
                                            <p class="text-purple-800 font-semibold">{{ number_format($total_overtime_amount ?? 0, 2) }} {{ __('messages.currency_aoa') }}</p>
                                        </div>
                                    </div>
                                    @if(isset($overtimeRecords) && count($overtimeRecords) > 0)
                                        <div class="mt-4">
                                            <p class="text-sm text-purple-600 font-medium mb-2">{{ __('messages.overtime_details') }}</p>
                                            <div class="max-h-32 overflow-y-auto space-y-1">
                                                @foreach($overtimeRecords as $overtime)
                                                    <div class="text-xs bg-purple-100 px-2 py-1 rounded flex justify-between">
                                                        <span>{{ $overtime['date'] ?? 'N/A' }}</span>
                                                        <span>{{ $overtime['hours'] ?? 0 }}h - {{ number_format($overtime['amount'] ?? 0, 2) }} {{ __('messages.currency_aoa') }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- Salary Advances Card --}}
                                <div class="bg-gradient-to-br from-orange-50 to-amber-100 p-6 rounded-2xl border border-orange-200">
                                    <h3 class="text-lg font-bold text-orange-800 mb-4 flex items-center">
                                        <i class="fas fa-hand-holding-usd mr-2"></i>
                                        {{ __('messages.salary_advances') }}
                                    </h3>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-orange-600 font-medium">{{ __('messages.total_advances') }}</p>
                                            <p class="text-orange-800 font-semibold">{{ number_format($total_salary_advances ?? 0, 2) }} {{ __('messages.currency_aoa') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-orange-600 font-medium">{{ __('messages.deduction_amount') }}</p>
                                            <p class="text-orange-800 font-semibold">{{ number_format($advance_deduction ?? 0, 2) }} {{ __('messages.currency_aoa') }}</p>
                                        </div>
                                    </div>
                                    @if(isset($salaryAdvances) && count($salaryAdvances) > 0)
                                        <div class="mt-4">
                                            <p class="text-sm text-orange-600 font-medium mb-2">{{ __('messages.advance_details') }}</p>
                                            <div class="max-h-40 overflow-y-auto space-y-2">
                                                @foreach($salaryAdvances as $advance)
                                                    <div class="bg-orange-100 p-3 rounded-lg">
                                                        <div class="flex justify-between items-start mb-1">
                                                            <span class="text-xs">({{ __('payroll.up_to_30k_non_taxable') }})</span><span class="text-xs font-medium text-orange-700">{{ $advance['request_date'] ?? 'N/A' }}</span>
                                                            <span class="text-xs font-bold text-orange-800">{{ number_format($advance['amount'] ?? 0, 2) }} {{ __('messages.currency_aoa') }}</span>
                                                        </div>
                                                        <div class="flex justify-between text-xs text-orange-600">
                                                            <span>{{ $advance['remaining_installments'] ?? 0 }}/{{ $advance['installments'] ?? 0 }} parcelas</span>
                                                            <span class="font-medium">{{ number_format($advance['installment_amount'] ?? 0, 2) }} {{ __('messages.currency_aoa') }}{{ __('messages.per_month') }}</span>
                                                        </div>
                                                        @if(isset($advance['reason']))
                                                            <span class="text-xs text-gray-500">({{ __('payroll.taxable_excess') }})</span><p class="text-xs text-orange-500 mt-1 truncate">{{ $advance['reason'] }}</p>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- Salary Discounts Card --}}
                                <div class="bg-gradient-to-br from-red-50 to-rose-100 p-6 rounded-2xl border border-red-200">
                                    <h3 class="text-lg font-bold text-red-800 mb-4 flex items-center">
                                        <i class="fas fa-minus-circle mr-2"></i>
                                        {{ __('messages.salary_discounts') }}
                                    </h3>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-red-600 font-medium">{{ __('messages.total_discounts') }}</p>
                                            <p class="text-red-800 font-semibold">{{ number_format($total_salary_discounts ?? 0, 2) }} {{ __('messages.currency_aoa') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-red-600 font-medium">{{ __('messages.active_discounts') }}</p>
                                            <p class="text-red-800 font-semibold">{{ isset($salaryDiscounts) ? count($salaryDiscounts) : 0 }}</p>
                                        </div>
                                    </div>
                                    @if(isset($salaryDiscounts) && count($salaryDiscounts) > 0)
                                        <div class="mt-4">
                                            <p class="text-sm text-red-600 font-medium mb-2">{{ __('messages.discount_details') }}</p>
                                            <div class="max-h-40 overflow-y-auto space-y-2">
                                                @foreach($salaryDiscounts as $discount)
                                                    <div class="bg-red-100 p-3 rounded-lg">
                                                        <div class="flex justify-between items-start mb-1">
                                                            <span class="text-xs font-medium text-red-700">{{ $discount['request_date'] ?? 'N/A' }}</span>
                                                            <span class="text-xs font-bold text-red-800">{{ number_format($discount['amount'] ?? 0, 2) }} {{ __('messages.currency_aoa') }}</span>
                                                        </div>
                                                        <div class="flex justify-between text-xs text-red-600">
                                                            <span>{{ $discount['remaining_installments'] ?? 0 }}/{{ $discount['installments'] ?? 0 }} parcelas</span>
                                                            <span class="font-medium">{{ number_format($discount['installment_amount'] ?? 0, 2) }} {{ __('messages.currency_aoa') }}{{ __('messages.per_month') }}</span>
                                                        </div>
                                                        <div class="flex justify-between text-xs text-red-500 mt-1">
                                                            <span class="capitalize">{{ $discount['discount_type'] ?? 'outros' }}</span>
                                                            @if(isset($discount['reason']))
                                                                <span class="truncate ml-2">{{ $discount['reason'] }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- Benefits & Allowances Card --}}
                                <div class="bg-gradient-to-br from-teal-50 to-cyan-100 p-6 rounded-2xl border border-teal-200">
                                    <h3 class="text-lg font-bold text-teal-800 mb-4 flex items-center">
                                        <i class="fas fa-gift mr-2"></i>
                                        {{ __('messages.benefits_allowances') }}
                                    </h3>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-teal-600 font-medium">{{ __('messages.food_benefit') }} 
                                                <span class="text-xs bg-gray-200 text-gray-600 px-2 py-1 rounded-full ml-1">{{ __('payroll.non_taxable') }}</span>
                                            </p>
                                            <p class="text-teal-800 font-semibold">{{ number_format($selectedEmployee->food_benefit ?? 0, 2) }} {{ __('messages.currency_aoa') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-teal-600 font-medium">{{ __('messages.transport_benefit') }}
                                                <span class="text-xs bg-green-200 text-green-700 px-2 py-1 rounded-full ml-1">{{ __('payroll.taxable') }}</span>
                                            </p>
                                            <p class="text-teal-800 font-semibold">{{ number_format($transport_allowance ?? 0, 2) }} {{ __('messages.currency_aoa') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-teal-600 font-medium">{{ __('messages.bonus_amount') }}
                                                <span class="text-xs bg-green-200 text-green-700 px-2 py-1 rounded-full ml-1">{{ __('payroll.taxable') }}</span>
                                            </p>
                                            <p class="text-teal-800 font-semibold">{{ number_format($bonus_amount ?? 0, 2) }} AOA</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-teal-600 font-medium">{{ __('messages.taxable_benefits') }}</p>
                                            <p class="text-teal-800 font-semibold">{{ number_format(($transport_allowance ?? 0) + ($bonus_amount ?? 0), 2) }} AOA</p>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- Right Panel - Payroll Summary --}}
                        <div class="w-full xl:w-1/2 bg-gradient-to-br from-gray-50 to-gray-100 overflow-y-auto flex-1">
                            <div class="p-4 lg:p-6">
                                @if($this->canViewSalaryDetails())
                                {{-- Payroll Summary --}}
                                <div class="bg-white rounded-2xl border border-gray-200 p-4 lg:p-6 shadow-sm">
                                    <h3 class="text-lg lg:text-xl font-bold text-gray-800 mb-4 lg:mb-6 flex items-center">
                                        <i class="fas fa-chart-pie text-green-500 mr-2"></i>
                                        {{ __('messages.payroll_summary') }}
                                    </h3>
                                @else
                                {{-- Limited View for Payroll Processors --}}
                                <div class="bg-white rounded-2xl border border-gray-200 p-4 lg:p-6 shadow-sm">
                                    <h3 class="text-lg lg:text-xl font-bold text-gray-800 mb-4 lg:mb-6 flex items-center">
                                        <i class="fas fa-eye-slash text-gray-500 mr-2"></i>
                                        {{ __('payroll.processing_summary') }}
                                    </h3>
                                    
                                    <div class="text-center py-8">
                                        <div class="bg-gray-100 rounded-lg p-6">
                                            <i class="fas fa-lock text-4xl text-gray-400 mb-4"></i>
                                            <h4 class="text-lg font-semibold text-gray-600 mb-2">{{ __('payroll.salary_hidden') }}</h4>
                                            <p class="text-gray-500 text-sm">{{ __('payroll.processing_only_description') }}</p>
                                            <div class="mt-4 text-xs text-gray-400">
                                                <p>{{ __('payroll.can_process_based_on_attendance') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                    
                                    <div class="space-y-3 lg:space-y-4">
                                        {{-- Base Salary --}}
                                        <div class="flex justify-between items-center p-3 lg:p-4 bg-blue-50 rounded-xl">
                                            <span class="font-medium text-blue-700 text-sm lg:text-base">{{ __('messages.basic_salary') }}</span>
                                            <span class="font-bold text-blue-800 text-sm lg:text-lg">{{ number_format($basic_salary ?? 0, 2) }} AOA</span>
                                        </div>

                                        {{-- Christmas Subsidy --}}
                                        <div class="flex justify-between items-center p-3 lg:p-4 rounded-xl transition-all duration-200" 
                                             :class="{'bg-emerald-50 opacity-100': $wire.christmas_subsidy, 'bg-gray-50 opacity-60': !$wire.christmas_subsidy}">
                                            <span class="font-medium text-sm lg:text-base" 
                                                  :class="{'text-emerald-700': $wire.christmas_subsidy, 'text-gray-500': !$wire.christmas_subsidy}">
                                                <i class="fas fa-gift mr-1" :class="{'text-emerald-600': $wire.christmas_subsidy, 'text-gray-400': !$wire.christmas_subsidy}"></i>
                                                {{ __('messages.christmas_subsidy') }}
                                            </span>
                                            <span class="font-bold text-sm lg:text-lg" 
                                                  :class="{'text-emerald-800': $wire.christmas_subsidy, 'text-gray-500': !$wire.christmas_subsidy}">
                                                <span x-show="$wire.christmas_subsidy" x-transition>
                                                    +{{ number_format($this->christmasSubsidyAmount, 2) }} {{ __('messages.currency_aoa') }}
                                                </span>
                                                <span x-show="!$wire.christmas_subsidy" x-transition>
                                                    0.00 {{ __('messages.currency_aoa') }}
                                                </span>
                                            </span>
                                        </div>

                                        {{-- Vacation Subsidy --}}
                                        <div class="flex justify-between items-center p-3 lg:p-4 rounded-xl transition-all duration-200" 
                                             :class="{'bg-emerald-50 opacity-100': $wire.vacation_subsidy, 'bg-gray-50 opacity-60': !$wire.vacation_subsidy}">
                                            <span class="font-medium text-sm lg:text-base" 
                                                  :class="{'text-emerald-700': $wire.vacation_subsidy, 'text-gray-500': !$wire.vacation_subsidy}">
                                                <i class="fas fa-umbrella-beach mr-1" :class="{'text-emerald-600': $wire.vacation_subsidy, 'text-gray-400': !$wire.vacation_subsidy}"></i>
                                                {{ __('messages.vacation_subsidy') }}
                                            </span>
                                            <span class="font-bold text-sm lg:text-lg" 
                                                  :class="{'text-emerald-800': $wire.vacation_subsidy, 'text-gray-500': !$wire.vacation_subsidy}">
                                                <span x-show="$wire.vacation_subsidy" x-transition>
                                                    +{{ number_format($this->vacationSubsidyAmount, 2) }} {{ __('messages.currency_aoa') }}
                                                </span>
                                                <span x-show="!$wire.vacation_subsidy" x-transition>
                                                    0.00 {{ __('messages.currency_aoa') }}
                                                </span>
                                            </span>
                                        </div>

                                        {{-- Transport Allowance --}}
                                        @if($transport_allowance > 0 || ($this->selectedEmployee && $this->selectedEmployee->transport_benefit > 0))
                                            <div class="p-2 lg:p-3 bg-gradient-to-r from-green-50 to-yellow-50 rounded-lg border border-green-200">
                                                <div class="flex justify-between items-center mb-2">
                                                    <div class="flex items-center space-x-2">
                                                        <span class="font-medium text-gray-700 text-xs lg:text-sm">{{ __('messages.transport_allowance') }}</span>
                                                        <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded-full font-medium">{{ __('payroll.proportional') }}</span>
                                                    </div>
                                                    <span class="font-bold text-gray-800 text-xs lg:text-sm">{{ number_format($transport_allowance, 2) }} AOA</span>
                                                </div>
                                                
                                                
                                                <div class="space-y-1 text-xs bg-white/50 p-2 rounded">
                                                    <div class="flex justify-between">
                                                        <span class="text-blue-600 font-medium">• {{ __('payroll.total_allowance') }}:</span>
                                                        <span class="text-blue-700 font-semibold">{{ number_format($this->getFullTransportBenefit(), 2) }} AOA</span>
                                                    </div>
                                                    <div class="flex justify-between">
                                                        <span class="text-gray-600">• {{ __('payroll.present_days') }}:</span>
                                                        <span class="text-gray-700">{{ $this->present_days }}/{{ \App\Models\HR\HRSetting::get('monthly_working_days', 22) }} {{ __('payroll.days') }}</span>
                                                    </div>
                                                    @if($this->getTransportDiscountAmount() > 0)
                                                    <div class="flex justify-between border-t pt-1">
                                                        <span class="text-red-600">• {{ __('payroll.discount_for_absences') }}:</span>
                                                        <span class="text-red-700 font-medium">-{{ number_format($this->getTransportDiscountAmount(), 2) }} AOA</span>
                                                    </div>
                                                    @endif
                                                    <div class="flex justify-between border-t pt-1 font-semibold">
                                                        <span class="text-green-600">• {{ __('payroll.amount_to_pay') }}:</span>
                                                        <span class="text-green-700">{{ number_format($this->getProportionalTransportOnly(), 2) }} AOA</span>
                                                    </div>
                                                </div>
                                                
                                                <div class="space-y-1 text-xs mt-2">
                                                    <div class="flex justify-between">
                                                        <span class="text-gray-600">• {{ __('payroll.exempt_up_to') }}:</span>
                                                        <span class="text-gray-700 font-medium">{{ number_format($this->getExemptTransportAllowance(), 2) }} AOA</span>
                                                    </div>
                                                    <div class="flex justify-between">
                                                        <span class="text-green-600">• {{ __('payroll.taxable') }}:</span>
                                                        <span class="text-green-700 font-medium">{{ number_format($this->getTaxableTransportAllowance(), 2) }} AOA</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Employee Profile Bonus --}}
                                        @if($bonus_amount > 0)
                                        <div class="flex justify-between items-center p-3 lg:p-4 bg-blue-50 rounded-xl">
                                            <span class="font-medium text-blue-700 text-sm lg:text-base">
                                                <i class="fas fa-user-tag text-blue-600 mr-1"></i>
                                                {{ __('payroll.employee_profile_bonus') }}
                                            </span>
                                            <span class="font-bold text-blue-800 text-sm lg:text-lg">+{{ number_format($bonus_amount, 2) }} AOA</span>
                                        </div>
                                        @endif

                                        {{-- Position Subsidy --}}
                                        @if($selectedEmployee && $selectedEmployee->position_subsidy > 0)
                                        <div class="flex justify-between items-center p-3 lg:p-4 bg-indigo-50 rounded-xl">
                                            <span class="font-medium text-indigo-700 text-sm lg:text-base">
                                                <i class="fas fa-briefcase text-indigo-600 mr-1"></i>
                                                {{ __('messages.position_subsidy') }}
                                            </span>
                                            <span class="font-bold text-indigo-800 text-sm lg:text-lg">+{{ number_format($selectedEmployee->position_subsidy, 2) }} AOA</span>
                                        </div>
                                        @endif

                                        {{-- Performance Subsidy --}}
                                        @if($selectedEmployee && $selectedEmployee->performance_subsidy > 0)
                                        <div class="flex justify-between items-center p-3 lg:p-4 bg-green-50 rounded-xl">
                                            <span class="font-medium text-green-700 text-sm lg:text-base">
                                                <i class="fas fa-chart-line text-green-600 mr-1"></i>
                                                {{ __('messages.performance_subsidy') }}
                                            </span>
                                            <span class="font-bold text-green-800 text-sm lg:text-lg">+{{ number_format($selectedEmployee->performance_subsidy, 2) }} AOA</span>
                                        </div>
                                        @endif

                                        {{-- Additional Payroll Bonus --}}
                                        @if($additional_bonus_amount > 0)
                                        <div class="flex justify-between items-center p-3 lg:p-4 bg-purple-50 rounded-xl">
                                            <span class="font-medium text-purple-700 text-sm lg:text-base">
                                                <i class="fas fa-plus-circle text-purple-600 mr-1"></i>
                                                {{ __('payroll.additional_payroll_bonus') }}
                                            </span>
                                            <span class="font-bold text-purple-800 text-sm lg:text-lg">+{{ number_format($additional_bonus_amount, 2) }} AOA</span>
                                        </div>
                                        @endif

                                        {{-- Overtime --}}
                                        @if($total_overtime_amount > 0)
                                        <div class="flex justify-between items-center p-3 lg:p-4 bg-orange-50 rounded-xl">
                                            <span class="font-medium text-orange-700 text-sm lg:text-base">
                                                <i class="fas fa-clock text-orange-600 mr-1"></i>
                                                {{ __('messages.overtime_amount') }}
                                            </span>
                                            <span class="font-bold text-orange-800 text-sm lg:text-lg">+{{ number_format($total_overtime_amount, 2) }} AOA</span>
                                        </div>
                                        @endif

                                        {{-- Divider --}}
                                        <hr class="border-gray-200">

                                        {{-- Main Salary (Base Salary + All Benefits including Food & Transport) --}}
                                        <div class="bg-blue-50 rounded-xl border border-blue-200 p-2 lg:p-3" x-data="{ showMainSalaryDetails: false }">
                                            <div class="flex justify-between items-center">
                                                <div class="flex items-center space-x-2">
                                                    <span class="font-semibold text-blue-700 text-sm lg:text-base">{{ __('messages.gross_salary') }}</span>
                                                    <!-- Help Icon for Main Salary Details -->
                                                    <button @click="showMainSalaryDetails = !showMainSalaryDetails" 
                                                            class="w-4 h-4 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-blue-600 transition-colors"
                                                            type="button"
                                                            title="{{ __('payroll.view_main_salary_details') }}">
                                                        ?
                                                    </button>
                                                </div>
                                                <span class="text-lg lg:text-xl font-bold text-blue-800">
                                                    {{ number_format($gross_salary, 2) }} AOA
                                                </span>
                                            </div>
                                            
                                            <!-- Main Salary Details Breakdown -->
                                            <div x-show="showMainSalaryDetails" x-transition class="mt-3 p-3 bg-blue-100/50 rounded-lg border border-blue-200">
                                                <h5 class="text-xs font-semibold text-blue-800 mb-2">{{ __('messages.main_salary_breakdown') }}:</h5>
                                                <div class="space-y-1 text-xs">
                                                    <div class="flex justify-between">
                                                        <span class="text-blue-700">{{ __('messages.basic_salary') }}:</span>
                                                        <span class="font-medium text-blue-800">{{ number_format($basic_salary ?? 0, 2) }} AOA</span>
                                                    </div>
                                                    <div class="flex justify-between">
                                                        <span class="text-blue-700">{{ __('messages.food_benefit') }}:</span>
                                                        <span class="font-medium text-blue-800">{{ number_format($selectedEmployee->food_benefit ?? 0, 2) }} AOA</span>
                                                    </div>
                                                    <div class="flex justify-between">
                                                        <span class="text-blue-700">{{ __('messages.transport_benefit') }}:</span>
                                                        <span class="font-medium text-blue-800">{{ number_format($transport_allowance ?? 0, 2) }} AOA</span>
                                                    </div>
                                                    @if($total_overtime_amount > 0)
                                                    <div class="flex justify-between">
                                                        <span class="text-blue-700">{{ __('messages.overtime') }}:</span>
                                                        <span class="font-medium text-blue-800">{{ number_format($total_overtime_amount ?? 0, 2) }} AOA</span>
                                                    </div>
                                                    @endif
                                                    @if($bonus_amount > 0)
                                                    <div class="flex justify-between">
                                                        <span class="text-blue-700">{{ __('messages.bonus_amount') }}:</span>
                                                        <span class="font-medium text-blue-800">{{ number_format($bonus_amount ?? 0, 2) }} AOA</span>
                                                    </div>
                                                    @endif
                                                    @if($additional_bonus_amount > 0)
                                                    <div class="flex justify-between">
                                                        <span class="text-blue-700">{{ __('messages.additional_bonus') }}:</span>
                                                        <span class="font-medium text-blue-800">{{ number_format($additional_bonus_amount ?? 0, 2) }} AOA</span>
                                                    </div>
                                                    @endif
                                                    @if($this->christmasSubsidyAmount > 0)
                                                    <div class="flex justify-between">
                                                        <span class="text-blue-700">{{ __('messages.christmas_subsidy') }}:</span>
                                                        <span class="font-medium text-blue-800">{{ number_format($this->christmasSubsidyAmount, 2) }} AOA</span>
                                                    </div>
                                                    @endif
                                                    @if($this->vacationSubsidyAmount > 0)
                                                    <div class="flex justify-between">
                                                        <span class="text-blue-700">{{ __('messages.vacation_subsidy') }}:</span>
                                                        <span class="font-medium text-blue-800">{{ number_format($this->vacationSubsidyAmount, 2) }} AOA</span>
                                                    </div>
                                                    @endif
                                                    <div class="border-t border-blue-300 pt-1 mt-1">
                                                        <div class="flex justify-between font-semibold">
                                                            <span class="text-blue-800">{{ __('messages.total') }}:</span>
                                                            <span class="text-blue-900">{{ number_format($gross_salary, 2) }} AOA</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Gross Salary --}}
                                        <div class="bg-green-50 rounded-xl border border-green-200 p-2 lg:p-3" x-data="{ showGrossSalaryDetails: false }">
                                            <div class="flex justify-between items-center">
                                                <div class="flex items-center space-x-2">
                                                    <span class="font-semibold text-green-700 text-sm lg:text-base">{{ __('messages.baseIRT_taxable_amount') }}</span>
                                                    <!-- Help Icon for Gross Salary Details -->
                                                    <button @click="showGrossSalaryDetails = !showGrossSalaryDetails" 
                                                            class="w-4 h-4 bg-green-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-green-600 transition-colors"
                                                            type="button"
                                                            title="{{ __('payroll.view_gross_salary_details') }}">
                                                        ?
                                                    </button>
                                                </div>
                                                <span class="text-lg lg:text-xl font-bold text-green-800">{{ number_format($base_irt_taxable_amount, 2) }} AOA</span>
                                            </div>
                                            
                                            <!-- Gross Salary Details Breakdown -->
                                            <div x-show="showGrossSalaryDetails" x-transition class="mt-3 p-3 bg-green-100/50 rounded-lg border border-green-200">
                                                <h5 class="text-xs font-semibold text-green-800 mb-2">{{ __('messages.gross_salary_breakdown') }}:</h5>
                                                <div class="space-y-1 text-xs">
                                                    <div class="flex justify-between">
                                                        <span class="text-green-700">{{ __('messages.basic_salary') }}:</span>
                                                        <span class="font-medium text-green-800">{{ number_format($basic_salary ?? 0, 2) }} AOA</span>
                                                    </div>
                                                    @if(isset($taxable_transport) && $taxable_transport > 0)
                                                    <div class="flex justify-between">
                                                        <span class="text-green-700">{{ __('messages.transport_benefit') }} <span class="text-xs text-gray-500">(excesso tributável)</span>:</span>
                                                        <span class="font-medium text-green-800">{{ number_format($taxable_transport, 2) }} AOA</span>
                                                    </div>
                                                    @endif
                                                    @if($bonus_amount > 0)
                                                    <div class="flex justify-between">
                                                        <span class="text-green-700">{{ __('messages.bonus_amount') }} <span class="text-xs text-gray-500">(tributável)</span>:</span>
                                                        <span class="font-medium text-green-800">{{ number_format($bonus_amount ?? 0, 2) }} AOA</span>
                                                    </div>
                                                    @endif
                                                    @if($additional_bonus_amount > 0)
                                                    <div class="flex justify-between">
                                                        <span class="text-green-700">{{ __('messages.additional_bonus') }} <span class="text-xs text-gray-500">(tributável)</span>:</span>
                                                        <span class="font-medium text-green-800">{{ number_format($additional_bonus_amount ?? 0, 2) }} AOA</span>
                                                    </div>
                                                    @endif
                                                    @if($this->christmasSubsidyAmount > 0)
                                                    <div class="flex justify-between">
                                                        <span class="text-green-700">{{ __('messages.christmas_subsidy') }} <span class="text-xs text-gray-500">(tributável)</span>:</span>
                                                        <span class="font-medium text-green-800">{{ number_format($this->christmasSubsidyAmount, 2) }} AOA</span>
                                                    </div>
                                                    @endif
                                                    @if($this->vacationSubsidyAmount > 0)
                                                    <div class="flex justify-between">
                                                        <span class="text-green-700">{{ __('messages.vacation_subsidy') }} <span class="text-xs text-gray-500">(tributável)</span>:</span>
                                                        <span class="font-medium text-green-800">{{ number_format($this->vacationSubsidyAmount, 2) }} AOA</span>
                                                    </div>
                                                    @endif
                                                    @if($total_overtime_amount > 0)
                                                    <div class="flex justify-between">
                                                        <span class="text-green-700">{{ __('messages.overtime_amount') }} <span class="text-xs text-gray-500">(tributável)</span>:</span>
                                                        <span class="font-medium text-green-800">{{ number_format($total_overtime_amount ?? 0, 2) }} AOA</span>
                                                    </div>
                                                    @endif
                                                    @if(isset($taxable_food) && $taxable_food > 0)
                                                    <div class="flex justify-between">
                                                        <span class="text-green-700">{{ __('messages.food_benefit') }} <span class="text-xs text-gray-500">(excesso tributável)</span>:</span>
                                                        <span class="font-medium text-green-800">{{ number_format($taxable_food, 2) }} AOA</span>
                                                    </div>
                                                    @endif
                                                    <div class="mt-2 pt-1 border-t border-green-300">
                                                        <div class="text-xs text-green-600 mb-1">
                                                            <span class="font-medium">{{ __('messages.excluded_from_gross') }}:</span>
                                                        </div>
                                                        <div class="flex justify-between text-gray-600">
                                                            <span class="text-xs">{{ __('messages.food_benefit') }} <span class="text-xs">(até 30k não tributável)</span>:</span>
                                                            <span class="text-xs line-through">{{ number_format($exempt_food ?? 0, 2) }} AOA</span>
                                                        </div>
                                                        <div class="flex justify-between text-gray-600">
                                                            <span class="text-xs">{{ __('messages.transport_benefit') }} <span class="text-xs">(até 30k não tributável)</span>:</span>
                                                            <span class="text-xs line-through">{{ number_format($exempt_transport ?? 0, 2) }} AOA</span>
                                                        </div>
                                                    </div>
                                                    <div class="border-t border-green-300 pt-1 mt-1">
                                                        <div class="flex justify-between font-semibold">
                                                            <span class="text-green-800">{{ __('messages.total') }} (Gross Salary):</span>
                                                            <span class="text-green-900">{{ number_format($base_irt_taxable_amount, 2) }} AOA</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Deductions Section --}}
                                        <div class="space-y-2">
                                            <h4 class="text-sm font-semibold text-gray-700 mb-2">{{ __('messages.deductions') }}:</h4>
                                            
                                            {{-- IRT --}}
                                            <div class="bg-red-50 rounded-lg p-2 lg:p-3" x-data="{ showIrtDetails: false }">
                                                <div class="flex justify-between items-center">
                                                    <div class="flex items-center space-x-2">
                                                        <div>
                                                            <span class="font-medium text-red-700 text-xs lg:text-sm">IRT</span>
                                                            @if($this->irtBracketDescription)
                                                                <div class="text-xs text-red-600 mt-1">{{ $this->irtBracketDescription }}</div>
                                                            @endif
                                                        </div>
                                                        <!-- Help Icon for IRT Details -->
                                                        <button @click="showIrtDetails = !showIrtDetails" 
                                                                class="w-4 h-4 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-blue-600 transition-colors"
                                                                type="button"
                                                                title="Ver detalhes do cálculo de IRT">
                                                            ?
                                                        </button>
                                                    </div>
                                                    <span class="font-bold text-red-800 text-xs lg:text-sm">-{{ number_format($this->irtCalculationDetails['total_irt'] ?? 0, 2) }} AOA</span>
                                                </div>
                                                
                                                <!-- IRT Calculation Details Popup -->
                                                <div x-show="showIrtDetails" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="mt-3 p-3 bg-white rounded-lg border border-red-200 shadow-sm text-xs">
                                                    <h5 class="font-semibold text-gray-800 mb-2 flex items-center">
                                                        <svg class="w-4 h-4 mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M12 3C7.03 3 3 7.03 3 12s4.03 9 9 9c2.93 0 5.67-1.33 7.5-3.5C21.67 15.67 23 13.93 23 11c0-4.97-4.03-9-9-9z"></path>
                                                        </svg>
                                                        {{ __('payroll.irt_calculation_details') }}
                                                    </h5>
                                                    
                                                    
                                                    <div class="space-y-2">
                                                     
                                                        <div class="flex justify-between border-t pt-2">
                                                            <span class="text-gray-700 font-medium">{{ __('payroll.mc_taxable_matter') }}:</span>
                                                            <span class="font-bold">{{ number_format($this->irtCalculationDetails['mc'] ?? 0, 2) }} AOA</span>
                                                        </div>
                                                        
                                                        @if(isset($this->irtCalculationDetails['bracket']) && $this->irtCalculationDetails['bracket'])
                                                            <div class="border-t pt-2 space-y-1">
                                                                <div class="flex justify-between">
                                                                    <span class="text-blue-600">{{ __('payroll.bracket') }} {{ $this->irtCalculationDetails['bracket']->bracket_number }}:</span>
                                                                    <span class="text-sm">{{ number_format($this->irtCalculationDetails['bracket']->min) }} - {{ $this->irtCalculationDetails['bracket']->max > 0 ? number_format($this->irtCalculationDetails['bracket']->max) : '+∞' }} AOA</span>
                                                                </div>
                                                                <div class="flex justify-between">
                                                                    <span class="text-gray-600">{{ __('payroll.fixed_amount') }}:</span>
                                                                    <span class="font-medium">{{ number_format($this->irtCalculationDetails['fixed_amount'], 2) }} AOA</span>
                                                                </div>
                                                                <div class="flex justify-between">
                                                                    <span class="text-gray-600">{{ __('payroll.excess') }} ({{ number_format($this->irtCalculationDetails['excess'], 2) }} × {{ $this->irtCalculationDetails['bracket']->tax_rate }}%):</span>
                                                                    <span class="font-medium">{{ number_format($this->irtCalculationDetails['tax_on_excess'], 2) }} AOA</span>
                                                                </div>
                                                                <div class="flex justify-between border-t pt-1 font-bold">
                                                                    <span class="text-red-700">{{ __('payroll.total_irt') }}:</span>
                                                                    <span class="text-red-800">{{ number_format($this->irtCalculationDetails['total_irt'], 2) }} AOA</span>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- INSS --}}
                                            <div class="flex justify-between items-center p-2 lg:p-3 bg-red-50 rounded-lg">
                                                <span class="font-medium text-red-700 text-xs lg:text-sm">INSS (3%)</span>
                                                <span class="font-bold text-red-800 text-xs lg:text-sm">-{{ number_format($inss_3_percent, 2) }} AOA</span>
                                            </div>
                                            
                                            {{-- INSS 8% Illustrative --}}
                                            <div class="flex justify-between items-center p-2 lg:p-3 bg-orange-50 rounded-lg border border-orange-200">
                                                <div>
                                                    <span class="font-medium text-orange-700 text-xs lg:text-sm">INSS (8%) - {{ __('payroll.illustrative_only') }}</span>
                                                    <div class="text-xs text-orange-500 mt-1">{{ __('payroll.calculated_gross_salary') }}</div>
                                                </div>
                                                <span class="font-bold text-orange-800 text-xs lg:text-sm">{{ number_format($inss_8_percent, 2) }} AOA</span>
                                            </div>

                                            {{-- Salary Advances --}}
                                            @if($advance_deduction > 0)
                                            <div class="flex justify-between items-center p-2 lg:p-3 bg-red-50 rounded-lg">
                                                <span class="font-medium text-red-700 text-xs lg:text-sm">{{ __('messages.salary_advances') }}</span>
                                                <span class="font-bold text-red-800 text-xs lg:text-sm">-{{ number_format($advance_deduction ?? 0, 2) }} AOA</span>
                                            </div>
                                            @endif

                                            {{-- Salary Discounts --}}
                                            @if($total_salary_discounts > 0)
                                            <div class="flex justify-between items-center p-2 lg:p-3 bg-red-50 rounded-lg">
                                                <span class="font-medium text-red-700 text-xs lg:text-sm">{{ __('messages.salary_discounts') }}</span>
                                                <span class="font-bold text-red-800 text-xs lg:text-sm">-{{ number_format($total_salary_discounts ?? 0, 2) }} AOA</span>
                                            </div>
                                            @endif

                                            {{-- Absence Deduction --}}
                                            @if($absence_deduction > 0)
                                            <div class="flex justify-between items-center p-2 lg:p-3 bg-red-50 rounded-lg">
                                                <span class="font-medium text-red-700 text-xs lg:text-sm">{{ __('payroll.absence_deductions') }} ({{ $absent_days ?? 0 }} {{ __('payroll.days') }})</span>
                                                <span class="font-bold text-red-800 text-xs lg:text-sm">-{{ number_format($absence_deduction ?? 0, 2) }} AOA</span>
                                            </div>
                                            @endif

                                            {{-- Late Arrival Deductions --}}
                                            @if($late_deduction > 0)
                                            <div class="flex justify-between items-center p-2 lg:p-3 bg-yellow-50 rounded-lg">
                                                <span class="font-medium text-yellow-700 text-xs lg:text-sm">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    {{ __('payroll.late_arrival_discount') }} ({{ $late_arrivals ?? 0 }} {{ __('payroll.days') }})
                                                </span>
                                                <span class="font-bold text-yellow-800 text-xs lg:text-sm">-{{ number_format($late_deduction, 2) }} AOA</span>
                                            </div>
                                            @endif
                                            
                                            {{-- Absence Deductions --}}
                                            @if($absence_deduction > 0)
                                            <div class="flex justify-between items-center p-2 lg:p-3 bg-orange-50 rounded-lg">
                                                <span class="font-medium text-orange-700 text-xs lg:text-sm">
                                                    <i class="fas fa-calendar-times mr-1"></i>
                                                    {{ __('payroll.absence_discount') }} ({{ $absent_days ?? 0 }} {{ __('payroll.days') }})
                                                </span>
                                                <span class="font-bold text-orange-800 text-xs lg:text-sm">-{{ number_format($absence_deduction, 2) }} AOA</span>
                                            </div>
                                            @endif

                                            {{-- Main Salary --}}
                                        <div class="bg-yellow-50 rounded-xl border border-yellow-200 p-2 lg:p-3">
                                            <div class="flex justify-between items-center">
                                                <span class="font-semibold text-yellow-700 text-sm lg:text-base">{{ __('messages.main_salary') }}</span>
                                                <span class="text-lg lg:text-xl font-bold text-yellow-800">{{ number_format($main_salary ?? 0, 2) }} AOA</span>
                                            </div>
                                        </div>

                                        {{-- Total Deductions --}}
                                        <div class="flex justify-between items-center p-3 lg:p-4 bg-red-50 rounded-xl border border-red-200">
                                            <span class="font-semibold text-red-700 text-sm lg:text-base">{{ __('messages.total_deductions') }}</span>
                                            <span class="text-lg lg:text-xl font-bold text-red-800">{{ number_format($total_deductions ?? 0, 2) }} AOA</span>
                                        </div>

                                        {{-- Net Salary --}}
                                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border-2 border-blue-200 shadow-sm p-2 lg:p-3" x-data="{ showNetSalaryDetails: false }">
                                            <div class="flex justify-between items-center">
                                                <div class="flex items-center space-x-2">
                                                    <span class="font-bold text-blue-700 text-base lg:text-lg flex items-center">
                                                        <i class="fas fa-wallet text-blue-600 mr-2"></i>
                                                        {{ __('messages.net_salary') }}
                                                    </span>
                                                    <!-- Help Icon for Net Salary Details -->
                                                    <button @click="showNetSalaryDetails = !showNetSalaryDetails" 
                                                            class="w-4 h-4 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-blue-600 transition-colors"
                                                            type="button"
                                                            title="{{ __('payroll.view_net_salary_details') }}">
                                                        ?
                                                    </button>
                                                </div>
                                                <span class="text-xl lg:text-2xl font-bold text-blue-800">{{ number_format($net_salary ?? 0, 2) }} AOA</span>
                                            </div>
                                            
                                            <!-- Net Salary Details Breakdown -->
                                            <div x-show="showNetSalaryDetails" x-transition class="mt-3 p-3 bg-blue-100/50 rounded-lg border border-blue-200">
                                                <h5 class="text-xs font-semibold text-blue-800 mb-2">{{ __('messages.net_salary_breakdown') }}:</h5>
                                                <div class="space-y-1 text-xs">
                                                    <div class="flex justify-between">
                                                        <span class="text-blue-700">{{ __('messages.gross_salary') }}:</span>
                                                        <span class="font-medium text-blue-800">{{ number_format($gross_salary ?? 0, 2) }} AOA</span>
                                                    </div>
                                                    <div class="flex justify-between text-red-700">
                                                        <span>IRT ({{ __('messages.income_tax') }}):</span>
                                                        <span class="font-medium">-{{ number_format($income_tax ?? 0, 2) }} AOA</span>
                                                    </div>
                                                    <div class="flex justify-between text-red-700">
                                                        <span>INSS (3%):</span>
                                                        <span class="font-medium">-{{ number_format($inss_3_percent ?? 0, 2) }} AOA</span>
                                                    </div>
                                                    <div class="flex justify-between text-orange-600 bg-orange-50 p-2 rounded border border-orange-200 mt-1">
                                                        <div>
                                                            <span>INSS (8%) - {{ __('payroll.illustrative_only') }}</span>
                                                            <div class="text-xs text-orange-500">{{ __('payroll.calculated_gross_salary') }}</div>
                                                        </div>
                                                        <span class="font-medium">{{ number_format($inss_8_percent ?? 0, 2) }} AOA</span>
                                                    </div>
                                                    @if($this->advance_deduction > 0)
                                                    <div class="flex justify-between text-red-700">
                                                        <span>{{ __('messages.salary_advances') }}:</span>
                                                        <span class="font-medium">-{{ number_format($this->advance_deduction, 2) }} AOA</span>
                                                    </div>
                                                    @endif
                                                    @if($this->total_salary_discounts > 0)
                                                    <div class="flex justify-between text-red-700">
                                                        <span>{{ __('messages.salary_discounts') }}:</span>
                                                        <span class="font-medium">-{{ number_format($this->total_salary_discounts, 2) }} AOA</span>
                                                    </div>
                                                    @endif
                                                    @if($this->union_deduction > 0)
                                                    <div class="flex justify-between text-red-700">
                                                        <span>{{ __('messages.union_deduction') }}:</span>
                                                        <span class="font-medium">-{{ number_format($this->union_deduction, 2) }} AOA</span>
                                                    </div>
                                                    @endif
                                                    @if($this->u_fund_ded > 0)
                                                    <div class="flex justify-between text-red-700">
                                                        <span>{{ __('messages.u_fund_deduction') }}:</span>
                                                        <span class="font-medium">-{{ number_format($this->u_fund_ded, 2) }} AOA</span>
                                                    </div>
                                                    @endif
                                                    @if($this->loan_installments > 0)
                                                    <div class="flex justify-between text-red-700">
                                                        <span>{{ __('messages.loan_installments') }}:</span>
                                                        <span class="font-medium">-{{ number_format($this->loan_installments, 2) }} AOA</span>
                                                    </div>
                                                    @endif
                                                    @if($this->selectedEmployee && $this->selectedEmployee->food_benefit > 0 && $this->is_food_in_kind)
                                                    <div class="flex justify-between text-red-700">
                                                        <span>{{ __('messages.food_in_kind') }}:</span>
                                                        <span class="font-medium">-{{ number_format($this->selectedEmployee->food_benefit, 2) }} AOA</span>
                                                    </div>
                                                    @endif
                                                    @if($this->absence_deduction > 0)
                                                    <div class="flex justify-between text-red-700">
                                                        <span>{{ __('messages.absence_deductions') }} ({{ $this->absent_days ?? 0 }} dias):</span>
                                                        <span class="font-medium">{{ __('messages.already_deducted_from_main') }}</span>
                                                    </div>
                                                    @endif
                                                    <!-- Breakdown detalhado dos componentes do Main Salary -->
                                                    <div class="bg-green-50 p-2 rounded border border-green-200 mt-2">
                                                        <h6 class="text-xs font-semibold text-green-800 mb-1">{{ __('messages.gross_salary_components') }}:</h6>
                                                        <div class="space-y-1 text-xs">
                                                            <div class="flex justify-between">
                                                                <span class="text-green-700">{{ __('messages.basic_salary') }}:</span>
                                                                <span class="font-medium">{{ number_format($this->basic_salary, 2) }} AOA</span>
                                                            </div>
                                                            @if($this->transport_allowance > 0)
                                                            <div class="flex justify-between">
                                                                <span class="text-green-700">{{ __('messages.transport_allowance') }}:</span>
                                                                <span class="font-medium">{{ number_format($this->transport_allowance, 2) }} AOA</span>
                                                            </div>
                                                            @endif
                                                            @if($this->selectedEmployee && $this->selectedEmployee->food_benefit > 0 && !$this->is_food_in_kind)
                                                            <div class="flex justify-between">
                                                                <span class="text-green-700">{{ __('messages.food_allowance_cash') }}:</span>
                                                                <span class="font-medium">{{ number_format($this->selectedEmployee->food_benefit, 2) }} AOA</span>
                                                            </div>
                                                            @endif
                                                            @if($this->total_overtime_amount > 0)
                                                            <div class="flex justify-between">
                                                                <span class="text-green-700">{{ __('messages.overtime') }}:</span>
                                                                <span class="font-medium">{{ number_format($this->total_overtime_amount, 2) }} AOA</span>
                                                            </div>
                                                            @endif
                                                            @if($this->night_shift_bonus > 0)
                                                            <div class="flex justify-between">
                                                                <span class="text-green-700">{{ __('messages.night_shift') }}:</span>
                                                                <span class="font-medium">{{ number_format($this->night_shift_bonus, 2) }} AOA</span>
                                                            </div>
                                                            @endif
                                                            @if($this->other_allowances > 0)
                                                            <div class="flex justify-between">
                                                                <span class="text-green-700">{{ __('messages.other_allowances') }}:</span>
                                                                <span class="font-medium">{{ number_format($this->other_allowances, 2) }} AOA</span>
                                                            </div>
                                                            @endif
                                                            @if($this->absence_deduction > 0)
                                                            <div class="flex justify-between text-red-600">
                                                                <span>{{ __('messages.absence_deductions') }}:</span>
                                                                <span class="font-medium">-{{ number_format($this->absence_deduction, 2) }} AOA</span>
                                                            </div>
                                                            @endif
                                                            <div class="flex justify-between font-semibold border-t border-green-300 pt-1 mt-1">
                                                                <span class="text-green-800">{{ __('messages.main_salary_total') }}:</span>
                                                                <span class="text-green-900">{{ number_format($main_salary ?? 0, 2) }} AOA</span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Subsídios adicionais -->
                                                    @if($this->vacation_subsidy || $this->christmas_subsidy || ($this->bonus_amount + $this->additional_bonus_amount) > 0)
                                                    <div class="bg-purple-50 p-2 rounded border border-purple-200 mt-2">
                                                        <h6 class="text-xs font-semibold text-purple-800 mb-1">{{ __('messages.additional_benefits') }}:</h6>
                                                        <div class="space-y-1 text-xs">
                                                            @if($this->vacation_subsidy_amount > 0)
                                                            <div class="flex justify-between">
                                                                <span class="text-purple-700">{{ __('messages.vacation_subsidy') }}:</span>
                                                                <span class="font-medium">{{ number_format($this->vacation_subsidy_amount, 2) }} AOA</span>
                                                            </div>
                                                            @endif
                                                            @if($this->christmas_subsidy_amount > 0)
                                                            <div class="flex justify-between">
                                                                <span class="text-purple-700">{{ __('messages.christmas_subsidy') }}:</span>
                                                                <span class="font-medium">{{ number_format($this->christmas_subsidy_amount, 2) }} AOA</span>
                                                            </div>
                                                            @endif
                                                            @if(($this->bonus_amount + $this->additional_bonus_amount) > 0)
                                                            <div class="flex justify-between">
                                                                <span class="text-purple-700">{{ __('messages.bonus_total') }}:</span>
                                                                <span class="font-medium">{{ number_format($this->bonus_amount + $this->additional_bonus_amount, 2) }} AOA</span>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <div class="flex justify-between text-gray-600 text-xs mt-2 pt-1 border-t border-gray-200">
                                                        <span>{{ __('messages.total_deductions') }}:</span>
                                                        <span class="font-medium">-{{ number_format($total_deductions ?? 0, 2) }} AOA</span>
                                                    </div>
                                                    @if($this->selectedEmployee && $this->selectedEmployee->food_benefit > 0 && !$this->is_food_in_kind)
                                                    <div class="flex justify-between text-red-700 text-xs">
                                                        <span>{{ __('messages.food_allowance_cash') }}:</span>
                                                        <span class="font-medium">-{{ number_format($this->selectedEmployee->food_benefit, 2) }} AOA</span>
                                                    </div>
                                                    @endif
                                                    <div class="border-t border-blue-300 pt-1 mt-1">
                                                        <div class="flex justify-between font-semibold">
                                                            <span class="text-blue-800">{{ __('messages.net_salary_final') }}:</span>
                                                            <span class="text-blue-900">{{ number_format($net_salary ?? 0, 2) }} AOA</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Action Buttons --}}
                                <div class="mt-6 space-y-3">
                                    <button 
                                        wire:click="save"
                                        wire:loading.attr="disabled"
                                        wire:target="save"
                                        type="button"
                                        class="w-full bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold py-4 px-6 rounded-xl transition-all duration-300 hover:scale-105 hover:shadow-lg flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                        onclick="console.log('Botão Salvar clicado')"
                                    >
                                        <span wire:loading.remove wire:target="save">
                                            <i class="fas fa-save text-lg"></i>
                                            <span class="ml-2">{{ __('messages.save_payroll') }}</span>
                                        </span>
                                        <span wire:loading wire:target="save">
                                            <i class="fas fa-spinner fa-spin text-lg"></i>
                                            <span class="ml-2">Salvando...</span>
                                        </span>
                                    </button>
                                    
                                    <button 
                                        wire:click="closeProcessModal"
                                        type="button"
                                        class="w-full bg-gray-500 hover:bg-gray-600 text-white font-medium py-3 px-6 rounded-xl transition-colors flex items-center justify-center space-x-2"
                                    >
                                        <i class="fas fa-times"></i>
                                        <span>{{ __('messages.cancel') }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
