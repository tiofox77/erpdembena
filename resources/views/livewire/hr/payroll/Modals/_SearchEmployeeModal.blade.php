{{-- Employee Search Modal --}}
@if($showEmployeeSearch)
<div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50" x-data="{ loading: false }">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-7xl mx-4 max-h-[95vh] overflow-hidden">
        
        {{-- Header --}}
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-white/20 p-2 rounded-lg">
                        <i class="fas fa-search text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold">{{ __('messages.search_employee_for_payroll') }}</h2>
                        <p class="text-blue-100">{{ __('messages.search_employee_payroll_description') }}</p>
                    </div>
                </div>
                <button wire:click="closeEmployeeSearch" class="text-white/80 hover:text-white p-2">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <div class="p-8">
            {{-- Advanced Search and Filters --}}
            <div class="mb-8">
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
