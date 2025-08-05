<div>
    <div class="py-4">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-4 sm:p-6">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg px-6 py-8 mb-6 shadow-lg">
                        <div class="flex justify-between items-center">
                            <div>
                                <h1 class="text-2xl font-bold text-white flex items-center">
                                    <i class="fas fa-users mr-3 text-blue-200 animate-pulse"></i>
                                    {{ __('messages.employees_management') }}
                                </h1>
                                <p class="text-blue-100 mt-2">{{ __('messages.manage_employees_description') }}</p>
                            </div>
                            <div class="flex space-x-3">
                                <button wire:click="exportToExcel" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                    <i class="fas fa-file-excel mr-2"></i>
                                    {{ __('messages.export_excel') }}
                                </button>
                                <button wire:click="create" class="inline-flex items-center px-4 py-2 bg-white border border-transparent rounded-md font-semibold text-blue-700 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                    <i class="fas fa-plus mr-2"></i>
                                    {{ __('messages.new_employee') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Filters and Search -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center">
                                <i class="fas fa-filter text-gray-600 mr-2"></i>
                                <h3 class="text-lg font-medium text-gray-700">{{ __('messages.search_and_filters') }}</h3>
                            </div>
                        </div>
                        
                        <div class="p-6">
                            <!-- Search Bar -->
                            <div class="mb-6">
                                <div class="relative max-w-md">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                    <input
                                        type="text"
                                        wire:model.debounce.300ms="search"
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                        placeholder="{{ __('messages.search_employees_placeholder') }}"
                                    >
                                </div>
                            </div>

                            <!-- Advanced Filters -->
                            <div class="space-y-6">
                                <!-- Primary Filters Row -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <!-- Department Filter -->
                                    <div class="space-y-2">
                                        <label class="flex items-center text-sm font-medium text-gray-700">
                                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-2">
                                                <i class="fas fa-building text-blue-600 text-xs"></i>
                                            </div>
                                            {{ __('messages.department') }}
                                        </label>
                                        <div class="relative">
                                            <select wire:model.live="filters.department_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 bg-white pr-8 transition-all duration-200">
                                                <option value="">{{ __('messages.all_departments') }}</option>
                                                @foreach($departments as $department)
                                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                            </div>
                                        </div>
                                        @if($filters['department_id'])
                                            <div class="flex items-center text-xs text-blue-600">
                                                <i class="fas fa-filter mr-1"></i>
                                                {{ __('messages.filtered') }}
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Position Filter -->
                                    <div class="space-y-2">
                                        <label class="flex items-center text-sm font-medium text-gray-700">
                                            <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center mr-2">
                                                <i class="fas fa-briefcase text-green-600 text-xs"></i>
                                            </div>
                                            {{ __('messages.position') }}
                                        </label>
                                        <div class="relative">
                                            <select wire:model.live="filters.position_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50 bg-white pr-8 transition-all duration-200">
                                                <option value="">{{ __('messages.all_positions') }}</option>
                                                @foreach($positions as $position)
                                                    <option value="{{ $position->id }}">{{ $position->title }}</option>
                                                @endforeach
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                            </div>
                                        </div>
                                        @if($filters['position_id'])
                                            <div class="flex items-center text-xs text-green-600">
                                                <i class="fas fa-filter mr-1"></i>
                                                {{ __('messages.filtered') }}
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Employment Status Filter -->
                                    <div class="space-y-2">
                                        <label class="flex items-center text-sm font-medium text-gray-700">
                                            <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center mr-2">
                                                <i class="fas fa-user-check text-purple-600 text-xs"></i>
                                            </div>
                                            {{ __('messages.employment_status') }}
                                        </label>
                                        <div class="relative">
                                            <select wire:model.live="filters.employment_status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-500 focus:ring-opacity-50 bg-white pr-8 transition-all duration-200">
                                                <option value="">{{ __('messages.all_statuses') }}</option>
                                                <option value="active">{{ __('messages.active') }}</option>
                                                <option value="on_leave">{{ __('messages.on_leave') }}</option>
                                                <option value="terminated">{{ __('messages.terminated') }}</option>
                                                <option value="suspended">{{ __('messages.suspended') }}</option>
                                                <option value="retired">{{ __('messages.retired') }}</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                            </div>
                                        </div>
                                        @if($filters['employment_status'])
                                            <div class="flex items-center text-xs text-purple-600">
                                                <i class="fas fa-filter mr-1"></i>
                                                {{ __('messages.filtered') }}
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Gender Filter -->
                                    <div class="space-y-2">
                                        <label class="flex items-center text-sm font-medium text-gray-700">
                                            <div class="w-8 h-8 rounded-full bg-pink-100 flex items-center justify-center mr-2">
                                                <i class="fas fa-venus-mars text-pink-600 text-xs"></i>
                                            </div>
                                            {{ __('messages.gender') }}
                                        </label>
                                        <div class="relative">
                                            <select wire:model.live="filters.gender" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50 bg-white pr-8 transition-all duration-200">
                                                <option value="">{{ __('messages.all_genders') }}</option>
                                                <option value="male">{{ __('messages.male') }}</option>
                                                <option value="female">{{ __('messages.female') }}</option>
                                                <option value="other">{{ __('messages.other') }}</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                            </div>
                                        </div>
                                        @if($filters['gender'])
                                            <div class="flex items-center text-xs text-pink-600">
                                                <i class="fas fa-filter mr-1"></i>
                                                {{ __('messages.filtered') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Secondary Filters Row -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <!-- Hire Date From -->
                                    <div class="space-y-2">
                                        <label class="flex items-center text-sm font-medium text-gray-700">
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center mr-2">
                                                <i class="fas fa-calendar text-indigo-600 text-xs"></i>
                                            </div>
                                            {{ __('messages.hired_after') }}
                                        </label>
                                        <input type="date" wire:model.live="filters.hire_date_from" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 bg-white transition-all duration-200">
                                        @if($filters['hire_date_from'])
                                            <div class="flex items-center text-xs text-indigo-600">
                                                <i class="fas fa-filter mr-1"></i>
                                                {{ __('messages.filtered') }}
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Salary Range Filter -->
                                    <div class="space-y-2">
                                        <label class="flex items-center text-sm font-medium text-gray-700">
                                            <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center mr-2">
                                                <i class="fas fa-dollar-sign text-emerald-600 text-xs"></i>
                                            </div>
                                            {{ __('messages.salary_range') }}
                                        </label>
                                        <div class="relative">
                                            <select wire:model.live="filters.salary_range" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-500 focus:ring-opacity-50 bg-white pr-8 transition-all duration-200">
                                                <option value="">{{ __('messages.all_salaries') }}</option>
                                                <option value="0-50000">0 - 50.000 AOA</option>
                                                <option value="50000-100000">50.000 - 100.000 AOA</option>
                                                <option value="100000-200000">100.000 - 200.000 AOA</option>
                                                <option value="200000+">200.000+ AOA</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                            </div>
                                        </div>
                                        @if($filters['salary_range'])
                                            <div class="flex items-center text-xs text-emerald-600">
                                                <i class="fas fa-filter mr-1"></i>
                                                {{ __('messages.filtered') }}
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Salary Advances Filter -->
                                    <div class="space-y-2">
                                        <label class="flex items-center text-sm font-medium text-gray-700">
                                            <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center mr-2">
                                                <i class="fas fa-hand-holding-usd text-orange-600 text-xs"></i>
                                            </div>
                                            {{ __('messages.salary_advances') }}
                                        </label>
                                        <div class="relative">
                                            <select wire:model.live="filters.has_advances" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 bg-white pr-8 transition-all duration-200">
                                                <option value="">{{ __('messages.all_employees') }}</option>
                                                <option value="with_advances">{{ __('messages.with_advances') }}</option>
                                                <option value="without_advances">{{ __('messages.without_advances') }}</option>
                                                <option value="pending_advances">{{ __('messages.pending_advances') }}</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                            </div>
                                        </div>
                                        @if($filters['has_advances'])
                                            <div class="flex items-center text-xs text-orange-600">
                                                <i class="fas fa-filter mr-1"></i>
                                                {{ __('messages.filtered') }}
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Per Page -->
                                    <div class="space-y-2">
                                        <label class="flex items-center text-sm font-medium text-gray-700">
                                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center mr-2">
                                                <i class="fas fa-list text-gray-600 text-xs"></i>
                                            </div>
                                            {{ __('messages.per_page') }}
                                        </label>
                                        <div class="relative">
                                            <select wire:model.live="perPage" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-500 focus:ring-opacity-50 bg-white pr-8 transition-all duration-200">
                                                <option value="10">10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Filter Actions -->
                            <div class="mt-6 flex items-center justify-between">
                                <div class="text-sm text-gray-600">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    {{ __('messages.showing') }} {{ $employees->firstItem() ?? 0 }} {{ __('messages.to') }} {{ $employees->lastItem() ?? 0 }} {{ __('messages.of') }} {{ $employees->total() }} {{ __('messages.results') }}
                                </div>
                                <button wire:click="resetFilters" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                    <i class="fas fa-undo mr-2"></i>
                                    {{ __('messages.reset_filters') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Employees Table -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fas fa-table text-gray-600 mr-2"></i>
                                    <h3 class="text-lg font-medium text-gray-700">{{ __('messages.employees_list') }}</h3>
                                </div>
                                <div class="text-sm text-gray-600">
                                    <i class="fas fa-users mr-1"></i>
                                    {{ $employees->total() }} {{ __('messages.total_employees') }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1 cursor-pointer transition-colors duration-200 hover:text-gray-700" wire:click="sortBy('full_name')">
                                                <i class="fas fa-user text-gray-400 mr-1"></i>
                                                <span>{{ __('messages.employee') }}</span>
                                                @if($sortField === 'full_name')
                                                    @if($sortDirection === 'asc')
                                                        <i class="fas fa-sort-up text-blue-500"></i>
                                                    @else
                                                        <i class="fas fa-sort-down text-blue-500"></i>
                                                    @endif
                                                @else
                                                    <i class="fas fa-sort text-gray-400 hover:text-gray-600"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-3 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1">
                                                <i class="fas fa-venus-mars text-gray-400 mr-1"></i>
                                                <span>{{ __('messages.gender') }}</span>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-3 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1">
                                                <i class="fas fa-phone text-gray-400 mr-1"></i>
                                                <span>{{ __('messages.contact') }}</span>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-3 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1 cursor-pointer transition-colors duration-200 hover:text-gray-700" wire:click="sortBy('department_id')">
                                                <i class="fas fa-building text-gray-400 mr-1"></i>
                                                <span>{{ __('messages.department') }}</span>
                                                @if($sortField === 'department_id')
                                                    @if($sortDirection === 'asc')
                                                        <i class="fas fa-sort-up text-blue-500"></i>
                                                    @else
                                                        <i class="fas fa-sort-down text-blue-500"></i>
                                                    @endif
                                                @else
                                                    <i class="fas fa-sort text-gray-400 hover:text-gray-600"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-3 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1 cursor-pointer transition-colors duration-200 hover:text-gray-700" wire:click="sortBy('position_id')">
                                                <i class="fas fa-briefcase text-gray-400 mr-1"></i>
                                                <span>{{ __('messages.position') }}</span>
                                                @if($sortField === 'position_id')
                                                    @if($sortDirection === 'asc')
                                                        <i class="fas fa-sort-up text-blue-500"></i>
                                                    @else
                                                        <i class="fas fa-sort-down text-blue-500"></i>
                                                    @endif
                                                @else
                                                    <i class="fas fa-sort text-gray-400 hover:text-gray-600"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-3 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1 cursor-pointer transition-colors duration-200 hover:text-gray-700" wire:click="sortBy('hire_date')">
                                                <i class="fas fa-calendar text-gray-400 mr-1"></i>
                                                <span>{{ __('messages.hire_date') }}</span>
                                                @if($sortField === 'hire_date')
                                                    @if($sortDirection === 'asc')
                                                        <i class="fas fa-sort-up text-blue-500"></i>
                                                    @else
                                                        <i class="fas fa-sort-down text-blue-500"></i>
                                                    @endif
                                                @else
                                                    <i class="fas fa-sort text-gray-400 hover:text-gray-600"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-3 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1">
                                                <i class="fas fa-money-bill text-gray-400 mr-1"></i>
                                                <span>{{ __('messages.salary_info') }}</span>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-3 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1">
                                                <i class="fas fa-hand-holding-usd text-gray-400 mr-1"></i>
                                                <span>{{ __('messages.advances_info') }}</span>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-3 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1 cursor-pointer transition-colors duration-200 hover:text-gray-700" wire:click="sortBy('employment_status')">
                                                <i class="fas fa-user-check text-gray-400 mr-1"></i>
                                                <span>{{ __('messages.status') }}</span>
                                                @if($sortField === 'employment_status')
                                                    @if($sortDirection === 'asc')
                                                        <i class="fas fa-sort-up text-blue-500"></i>
                                                    @else
                                                        <i class="fas fa-sort-down text-blue-500"></i>
                                                    @endif
                                                @else
                                                    <i class="fas fa-sort text-gray-400 hover:text-gray-600"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-4 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <i class="fas fa-cogs text-gray-400 mr-1"></i>
                                            {{ __('messages.actions') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($employees as $employee)
                                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        @if($employee->photo)
                                                            <img class="h-10 w-10 rounded-full object-cover" src="{{ Storage::url($employee->photo) }}" alt="{{ $employee->full_name }}">
                                                        @else
                                                            <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center">
                                                                <span class="text-white font-medium text-sm">{{ substr($employee->full_name, 0, 2) }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-medium text-gray-900">{{ $employee->full_name }}</div>
                                                        <div class="text-sm text-gray-500">
                                                            @if($employee->id_card)
                                                                <i class="fas fa-id-card mr-1"></i>{{ $employee->id_card }}
                                                            @else
                                                                {{ $employee->email }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-3 py-4 whitespace-nowrap">
                                                @if($employee->gender)
                                                    @switch($employee->gender)
                                                        @case('male')
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                                <i class="fas fa-mars mr-1"></i>
                                                                {{ __('messages.male') }}
                                                            </span>
                                                            @break
                                                        @case('female')
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-pink-100 text-pink-800">
                                                                <i class="fas fa-venus mr-1"></i>
                                                                {{ __('messages.female') }}
                                                            </span>
                                                            @break
                                                        @default
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                                <i class="fas fa-transgender mr-1"></i>
                                                                {{ __('messages.other') }}
                                                            </span>
                                                    @endswitch
                                                @else
                                                    <span class="text-xs text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-4 whitespace-nowrap">
                                                <div class="space-y-1">
                                                    @if($employee->phone)
                                                        <div class="flex items-center text-sm text-gray-900">
                                                            <i class="fas fa-phone text-green-500 mr-2"></i>
                                                            {{ $employee->phone }}
                                                        </div>
                                                    @endif
                                                    @if($employee->email)
                                                        <div class="flex items-center text-sm text-gray-600">
                                                            <i class="fas fa-envelope text-blue-500 mr-2"></i>
                                                            {{ Str::limit($employee->email, 20) }}
                                                        </div>
                                                    @endif
                                                    @if(!$employee->phone && !$employee->email)
                                                        <span class="text-sm text-gray-400 italic">{{ __('messages.no_contact_info') }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-3 py-4 whitespace-nowrap">
                                                @if($employee->department)
                                                    <div class="flex items-center">
                                                        <i class="fas fa-building text-blue-500 mr-2"></i>
                                                        <span class="text-sm text-gray-900">{{ Str::limit($employee->department->name, 15) }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-sm text-gray-400 italic">{{ __('messages.not_assigned') }}</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-4 whitespace-nowrap">
                                                @if($employee->position)
                                                    <div class="flex items-center">
                                                        <i class="fas fa-briefcase text-green-500 mr-2"></i>
                                                        <span class="text-sm text-gray-900">{{ Str::limit($employee->position->title, 15) }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-sm text-gray-400 italic">{{ __('messages.not_assigned') }}</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <i class="fas fa-calendar text-purple-500 mr-2"></i>
                                                    <span class="text-sm text-gray-900">{{ $employee->hire_date ? \Carbon\Carbon::parse($employee->hire_date)->format('d/m/Y') : '-' }}</span>
                                                </div>
                                            </td>
                                            <td class="px-3 py-4 whitespace-nowrap">
                                                <div class="space-y-1">
                                                    @if($employee->base_salary)
                                                        <div class="flex items-center text-sm font-medium text-gray-900">
                                                            <i class="fas fa-dollar-sign text-green-500 mr-1"></i>
                                                            {{ number_format($employee->base_salary, 0, ',', '.') }} AOA
                                                        </div>
                                                    @endif
                                                    @php
                                                        $totalBenefits = ($employee->food_benefit ?? 0) + ($employee->transport_benefit ?? 0) + ($employee->bonus_amount ?? 0);
                                                    @endphp
                                                    @if($totalBenefits > 0)
                                                        <div class="flex items-center text-xs text-gray-600">
                                                            <i class="fas fa-gift text-blue-500 mr-1"></i>
                                                            +{{ number_format($totalBenefits, 0, ',', '.') }} AOA
                                                        </div>
                                                    @endif
                                                    @if(!$employee->base_salary && !$totalBenefits)
                                                        <span class="text-sm text-gray-400 italic">{{ __('messages.not_defined') }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-3 py-4 whitespace-nowrap">
                                                @php
                                                    $salaryAdvances = $employee->salaryAdvances;
                                                    $totalAdvances = $salaryAdvances->sum('amount');
                                                    $pendingAdvances = $salaryAdvances->where('status', 'pending')->sum('amount');
                                                    $activeAdvances = $salaryAdvances->where('status', 'approved')->sum('remaining_amount');
                                                @endphp
                                                <div class="space-y-1">
                                                    @if($totalAdvances > 0)
                                                        <div class="flex items-center text-sm font-medium text-gray-900">
                                                            <i class="fas fa-hand-holding-usd text-orange-500 mr-1"></i>
                                                            {{ number_format($totalAdvances, 0, ',', '.') }} AOA
                                                        </div>
                                                        @if($pendingAdvances > 0)
                                                            <div class="flex items-center text-xs text-yellow-600">
                                                                <i class="fas fa-clock mr-1"></i>
                                                                {{ __('messages.pending_amount') }}: {{ number_format($pendingAdvances, 0, ',', '.') }} AOA
                                                            </div>
                                                        @endif
                                                        @if($activeAdvances > 0)
                                                            <div class="flex items-center text-xs text-red-600">
                                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                                {{ __('messages.pending_amount') }}: {{ number_format($activeAdvances, 0, ',', '.') }} AOA
                                                            </div>
                                                        @endif
                                                    @else
                                                        <span class="text-sm text-gray-400 italic">{{ __('messages.no_advances') }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @switch($employee->employment_status)
                                                    @case('active')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            <i class="fas fa-check-circle mr-1"></i>
                                                            {{ __('messages.active') }}
                                                        </span>
                                                        @break
                                                    @case('on_leave')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                            <i class="fas fa-pause-circle mr-1"></i>
                                                            {{ __('messages.on_leave') }}
                                                        </span>
                                                        @break
                                                    @case('terminated')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                            <i class="fas fa-times-circle mr-1"></i>
                                                            {{ __('messages.terminated') }}
                                                        </span>
                                                        @break
                                                    @case('suspended')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                                            {{ __('messages.suspended') }}
                                                        </span>
                                                        @break
                                                    @case('retired')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            <i class="fas fa-user-clock mr-1"></i>
                                                            {{ __('messages.retired') }}
                                                        </span>
                                                        @break
                                                    @default
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                            {{ ucfirst($employee->employment_status) }}
                                                        </span>
                                                @endswitch
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-center text-sm font-medium">
                                                <div class="flex items-center justify-center space-x-2">
                                                    <button
                                                        wire:click="view({{ $employee->id }})"
                                                        class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105"
                                                        title="{{ __('messages.view') }}"
                                                    >
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button
                                                        wire:click="edit({{ $employee->id }})"
                                                        class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 transform hover:scale-105"
                                                        title="{{ __('messages.edit') }}"
                                                    >
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button
                                                        wire:click="manageDocuments({{ $employee->id }})"
                                                        class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-purple-700 bg-purple-100 hover:bg-purple-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200 transform hover:scale-105"
                                                        title="{{ __('messages.documents') }}"
                                                    >
                                                        <i class="fas fa-folder"></i>
                                                    </button>
                                                    <button
                                                        wire:click="confirmDelete({{ $employee->id }})"
                                                        class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 transform hover:scale-105"
                                                        title="{{ __('messages.delete') }}"
                                                    >
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="px-6 py-20">
                                                <div class="text-center">
                                                    <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-gray-100 mb-6">
                                                        <i class="fas fa-users text-gray-400 text-3xl"></i>
                                                    </div>
                                                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('messages.no_employees_found') }}</h3>
                                                    @if($search || array_filter($filters))
                                                        <p class="text-gray-500 mb-4">{{ __('messages.no_employees_description') }}</p>
                                                        <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
                                                            <button
                                                                wire:click="resetFilters"
                                                                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200"
                                                            >
                                                                <i class="fas fa-undo mr-2"></i>
                                                                {{ __('messages.reset_filters') }}
                                                            </button>
                                                            <button
                                                                wire:click="create"
                                                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200"
                                                            >
                                                                <i class="fas fa-plus mr-2"></i>
                                                                {{ __('messages.new_employee') }}
                                                            </button>
                                                        </div>
                                                    @else
                                                        <p class="text-gray-500 mb-6">{{ __('messages.no_employees_description') }}</p>
                                                        <button
                                                            wire:click="create"
                                                            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105"
                                                        >
                                                            <i class="fas fa-plus mr-2"></i>
                                                            {{ __('messages.add_first_employee') }}
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="px-4 py-3 bg-white border-t border-gray-200 sm:px-6">
                            @if(method_exists($employees, 'links'))
                                {{ $employees->links() }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Employee Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto backdrop-blur-sm">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-6xl max-h-[95vh] overflow-hidden m-4 transform transition-all duration-300">
                <!-- Modern Header with Gradient -->
                <div class="bg-gradient-to-r {{ $isEditing ? 'from-green-600 to-green-700' : 'from-blue-600 to-blue-700' }} px-6 py-6 sticky top-0 z-20">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                <i class="fas {{ $isEditing ? 'fa-user-edit' : 'fa-user-plus' }} text-white text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-white">
                                    {{ $isEditing ? __('messages.edit') : __('messages.create') }} {{ __('messages.employee') }}
                                </h3>
                                <p class="text-blue-100 text-sm">
                                    {{ $isEditing ? __('messages.update_employee_information') : __('messages.add_new_employee_information') }}
                                </p>
                            </div>
                        </div>
                        <button type="button" 
                            class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center text-white hover:bg-opacity-30 transition-all duration-200 transform hover:scale-105" 
                            wire:click="closeModal">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>
                </div>

                <!-- Scrollable Content Area -->
                <div class="overflow-y-auto max-h-[calc(95vh-120px)]">
                    <!-- Error Messages -->
                    @if($errors->any())
                        <div class="mx-6 mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-exclamation-triangle text-red-600 text-sm"></i>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">{{ __('messages.please_correct_errors') }}</h3>
                                    <ul class="mt-2 text-sm text-red-700 space-y-1">
                                        @foreach($errors->all() as $error)
                                            <li class="flex items-start">
                                                <i class="fas fa-chevron-right text-red-500 text-xs mt-1 mr-2"></i>
                                                {{ $error }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Form Container -->
                    <form wire:submit.prevent="save" class="px-6 py-6">
                        <!-- Personal Information Section -->
                        <div class="mb-8">
                            <div class="flex items-center mb-6">
                                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900">{{ __('messages.personal_information') }}</h4>
                                    <p class="text-sm text-gray-600">{{ __('messages.personal_info_description') }}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 bg-gray-50 p-6 rounded-xl">
                                <!-- Full Name -->
                                <div class="space-y-2">
                                    <label for="full_name" class="flex items-center text-sm font-medium text-gray-700">
                                        <i class="fas fa-user text-blue-500 mr-2"></i>
                                        {{ __('messages.full_name') }} <span class="text-red-500 ml-1">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="text" id="full_name"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 @error('full_name') border-red-500 bg-red-50 @enderror"
                                            wire:model.live="full_name" 
                                            placeholder="{{ __('messages.enter_full_name') }}">
                                        @error('full_name')
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                            </div>
                                        @enderror
                                    </div>
                                    @error('full_name')
                                        <p class="flex items-center text-sm text-red-600">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Date of Birth -->
                                <div class="space-y-2">
                                    <label for="date_of_birth" class="flex items-center text-sm font-medium text-gray-700">
                                        <i class="fas fa-birthday-cake text-purple-500 mr-2"></i>
                                        {{ __('messages.date_of_birth') }}
                                    </label>
                                    <div class="relative">
                                        <input type="date" id="date_of_birth"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 @error('date_of_birth') border-red-500 bg-red-50 @enderror"
                                            wire:model.live="date_of_birth">
                                        @error('date_of_birth')
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                            </div>
                                        @enderror
                                    </div>
                                    @error('date_of_birth')
                                        <p class="flex items-center text-sm text-red-600">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Gender -->
                                <div class="space-y-2">
                                    <label for="gender" class="flex items-center text-sm font-medium text-gray-700">
                                        <i class="fas fa-venus-mars text-pink-500 mr-2"></i>
                                        {{ __('messages.gender') }}
                                    </label>
                                    <div class="relative">
                                        <select id="gender"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-all duration-200 appearance-none bg-white @error('gender') border-red-500 bg-red-50 @enderror"
                                            wire:model.live="gender">
                                            <option value="">{{ __('messages.select_gender') }}</option>
                                            <option value="male">{{ __('messages.male') }}</option>
                                            <option value="female">{{ __('messages.female') }}</option>
                                            <option value="other">{{ __('messages.other') }}</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            @error('gender')
                                                <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                                            @enderror
                                            <i class="fas fa-chevron-down text-gray-400"></i>
                                        </div>
                                    </div>
                                    @error('gender')
                                        <p class="flex items-center text-sm text-red-600">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- ID Card -->
                                <div class="space-y-2">
                                    <label for="id_card" class="flex items-center text-sm font-medium text-gray-700">
                                        <i class="fas fa-id-card text-indigo-500 mr-2"></i>
                                        {{ __('messages.id_card') }}
                                    </label>
                                    <div class="relative">
                                        <input type="text" id="id_card"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 @error('id_card') border-red-500 bg-red-50 @enderror"
                                            wire:model.live="id_card" 
                                            placeholder="{{ __('messages.id_card_number') }}">
                                        @error('id_card')
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                            </div>
                                        @enderror
                                    </div>
                                    @error('id_card')
                                        <p class="flex items-center text-sm text-red-600">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Tax Number -->
                                <div class="space-y-2">
                                    <label for="tax_number" class="flex items-center text-sm font-medium text-gray-700">
                                        <i class="fas fa-receipt text-green-500 mr-2"></i>
                                        {{ __('messages.tax_number') }}
                                    </label>
                                    <div class="relative">
                                        <input type="text" id="tax_number"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 @error('tax_number') border-red-500 bg-red-50 @enderror"
                                            wire:model.live="tax_number" 
                                            placeholder="{{ __('messages.tax_identification_number') }}">
                                        @error('tax_number')
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                            </div>
                                        @enderror
                                    </div>
                                    @error('tax_number')
                                        <p class="flex items-center text-sm text-red-600">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Marital Status -->
                                <div class="space-y-2">
                                    <label for="marital_status" class="flex items-center text-sm font-medium text-gray-700">
                                        <i class="fas fa-heart text-red-500 mr-2"></i>
                                        {{ __('messages.marital_status') }}
                                    </label>
                                    <div class="relative">
                                        <select id="marital_status"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all duration-200 appearance-none bg-white @error('marital_status') border-red-500 bg-red-50 @enderror"
                                            wire:model.live="marital_status">
                                            <option value="">{{ __('messages.select_status') }}</option>
                                            <option value="single">{{ __('messages.single') }}</option>
                                            <option value="married">{{ __('messages.married') }}</option>
                                            <option value="divorced">{{ __('messages.divorced') }}</option>
                                            <option value="widowed">{{ __('messages.widowed') }}</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            @error('marital_status')
                                                <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                                            @enderror
                                            <i class="fas fa-chevron-down text-gray-400"></i>
                                        </div>
                                    </div>
                                    @error('marital_status')
                                        <p class="flex items-center text-sm text-red-600">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Dependents -->
                                <div class="space-y-2">
                                    <label for="dependents" class="flex items-center text-sm font-medium text-gray-700">
                                        <i class="fas fa-users text-orange-500 mr-2"></i>
                                        {{ __('messages.dependents') }}
                                    </label>
                                    <div class="relative">
                                        <input type="number" min="0" id="dependents"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all duration-200 @error('dependents') border-red-500 bg-red-50 @enderror"
                                            wire:model.live="dependents" 
                                            placeholder="{{ __('messages.number_of_dependents') }}">
                                        @error('dependents')
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                            </div>
                                        @enderror
                                    </div>
                                    @error('dependents')
                                        <p class="flex items-center text-sm text-red-600">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Photo (File Upload) -->
                                <div class="md:col-span-3 space-y-2">
                                    <label for="photo" class="flex items-center text-sm font-medium text-gray-700">
                                        <i class="fas fa-camera text-purple-500 mr-2"></i>
                                        {{ __('messages.photo') }}
                                    </label>
                                    <div class="flex items-center space-x-4">
                                        @if($photo && !is_string($photo))
                                            <div class="relative">
                                                <img src="{{ $photo->temporaryUrl() }}" class="h-20 w-20 object-cover rounded-full border-4 border-purple-200 shadow-md">
                                                <div class="absolute -top-1 -right-1 bg-green-500 rounded-full p-1">
                                                    <i class="fas fa-check text-white text-xs"></i>
                                                </div>
                                            </div>
                                        @elseif($photo && is_string($photo))
                                            <div class="relative">
                                                <img src="{{ asset('storage/' . $photo) }}" class="h-20 w-20 object-cover rounded-full border-4 border-purple-200 shadow-md">
                                                <div class="absolute -top-1 -right-1 bg-blue-500 rounded-full p-1">
                                                    <i class="fas fa-image text-white text-xs"></i>
                                                </div>
                                            </div>
                                        @else
                                            <div class="h-20 w-20 bg-gray-100 rounded-full border-4 border-dashed border-gray-300 flex items-center justify-center">
                                                <i class="fas fa-user text-gray-400 text-xl"></i>
                                            </div>
                                        @endif
                                        <div class="flex-1">
                                            <input type="file" id="photo" accept="image/*"
                                                class="block w-full text-sm text-gray-600 file:mr-4 file:py-3 file:px-6 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 file:transition-all file:duration-200 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                                wire:model.live="photo">
                                            <p class="mt-1 text-xs text-gray-500">{{ __('messages.photo_upload_hint') }}</p>
                                        </div>
                                    </div>
                                    @error('photo')
                                        <p class="flex items-center text-sm text-red-600">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                        </div>
                    </div>

                    <!-- Contact Information Section -->
                    <div class="border-t border-gray-200">
                        <div class="px-6 py-5">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-2 rounded-lg">
                                        <i class="fas fa-address-book text-white"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h4 class="text-lg font-semibold text-gray-900">{{ __('messages.contact_information') }}</h4>
                                        <p class="text-sm text-gray-500">{{ __('messages.contact_info_description') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Email -->
                                <div class="space-y-2">
                                    <label for="email" class="flex items-center text-sm font-medium text-gray-700">
                                        <i class="fas fa-envelope text-blue-500 mr-2"></i>
                                    </label>
                                    <div class="relative">
                                        <input type="email" id="email"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 @error('email') border-red-500 bg-red-50 @enderror"
                                            wire:model.live="email" 
                                            placeholder="{{ __('messages.email_address') }}">
                                        @error('email')
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                            </div>
                                        @enderror
                                    </div>
                                    @error('email')
                                        <p class="flex items-center text-sm text-red-600">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Phone -->
                                <div class="space-y-2">
                                    <label for="phone" class="flex items-center text-sm font-medium text-gray-700">
                                        <i class="fas fa-phone text-green-500 mr-2"></i>
                                        {{ __('messages.phone') }}
                                    </label>
                                    <div class="relative">
                                        <input type="text" id="phone"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 @error('phone') border-red-500 bg-red-50 @enderror"
                                            wire:model.live="phone" 
                                            placeholder="{{ __('messages.phone_number') }}">
                                        @error('phone')
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                            </div>
                                        @enderror
                                    </div>
                                    @error('phone')
                                        <p class="flex items-center text-sm text-red-600">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Address -->
                                <div class="md:col-span-2 space-y-2">
                                    <label for="address" class="flex items-center text-sm font-medium text-gray-700">
                                        <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>
                                        {{ __('messages.address') }}
                                    </label>
                                    <div class="relative">
                                        <textarea id="address" rows="3"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all duration-200 resize-none @error('address') border-red-500 bg-red-50 @enderror"
                                            wire:model.live="address" 
                                            placeholder="{{ __('messages.full_address') }}"></textarea>
                                        @error('address')
                                            <div class="absolute top-3 right-3 pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                            </div>
                                        @enderror
                                    </div>
                                    @error('address')
                                        <p class="flex items-center text-sm text-red-600">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bank Information Section -->
                    <div class="border-t border-gray-200">
                        <div class="px-6 py-5">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="bg-gradient-to-r from-green-500 to-blue-600 p-2 rounded-lg">
                                        <i class="fas fa-university text-white"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h4 class="text-lg font-semibold text-gray-900">{{ __('messages.bank_information') }}</h4>
                                        <p class="text-sm text-gray-500">{{ __('messages.bank_info_description') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Bank Name -->
                                <div class="space-y-2">
                                    <label for="bank_name" class="flex items-center text-sm font-medium text-gray-700">
                                        <i class="fas fa-building text-green-500 mr-2"></i>
                                        {{ __('messages.bank_name') }}
                                    </label>
                                    <div class="relative">
                                        <select id="bank_name"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 appearance-none bg-white @error('bank_name') border-red-500 bg-red-50 @enderror"
                                            wire:model.live="bank_name">
                                            <option value="">{{ __('messages.select_bank') }}</option>
                                            <option value="Banco Nacional de Angola (BNA)">Banco Nacional de Angola (BNA)</option>
                                            <option value="Banco Angolano de Investimentos (BAI)">Banco Angolano de Investimentos (BAI)</option>
                                            <option value="Banco de Fomento Angola (BFA)">Banco de Fomento Angola (BFA)</option>
                                            <option value="Banco Económico">Banco Económico</option>
                                            <option value="Banco de Poupança e Crédito (BPC)">Banco de Poupança e Crédito (BPC)</option>
                                            <option value="Standard Bank Angola">Standard Bank Angola</option>
                                            <option value="Banco Millennium Atlântico">Banco Millennium Atlântico</option>
                                            <option value="Banco BIC">Banco BIC</option>
                                            <option value="Banco Sol">Banco Sol</option>
                                            <option value="Banco Keve">Banco Keve</option>
                                            <option value="Banco BAI Micro Finanças">Banco BAI Micro Finanças</option>
                                            <option value="Banco Comercial do Huambo">Banco Comercial do Huambo</option>
                                            <option value="Banco de Negócios Internacional (BNI)">Banco de Negócios Internacional (BNI)</option>
                                            <option value="Banco de Desenvolvimento de Angola (BDA)">Banco de Desenvolvimento de Angola (BDA)</option>
                                            <option value="Banco Prestígio">Banco Prestígio</option>
                                            <option value="Banco VTB África">Banco VTB África</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            @error('bank_name')
                                                <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                                            @enderror
                                            <i class="fas fa-chevron-down text-gray-400"></i>
                                        </div>
                                    </div>
                                    @error('bank_name')
                                        <p class="flex items-center text-sm text-red-600">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Bank Account -->
                                <div class="space-y-2">
                                    <label for="bank_account" class="flex items-center text-sm font-medium text-gray-700">
                                        <i class="fas fa-credit-card text-blue-500 mr-2"></i>
                                        {{ __('messages.bank_account') }}
                                    </label>
                                    <div class="relative">
                                        <input type="text" id="bank_account"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 @error('bank_account') border-red-500 bg-red-50 @enderror"
                                            wire:model.live="bank_account" 
                                            placeholder="{{ __('messages.account_number') }}">
                                        @error('bank_account')
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                            </div>
                                        @enderror
                                    </div>
                                    @error('bank_account')
                                        <p class="flex items-center text-sm text-red-600">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Employment Information Section -->
                    <div class="border-t border-gray-200">
                        <div class="px-6 py-5">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="bg-gradient-to-r from-orange-500 to-red-600 p-2 rounded-lg">
                                        <i class="fas fa-briefcase text-white"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h4 class="text-lg font-semibold text-gray-900">{{ __('messages.employment_information') }}</h4>
                                        <p class="text-sm text-gray-500">{{ __('messages.employment_info_description') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Department -->
                                <div class="space-y-2">
                                    <label for="department_id" class="flex items-center text-sm font-medium text-gray-700">
                                        <i class="fas fa-building text-blue-500 mr-2"></i>
                                        {{ __('messages.department') }} <span class="text-red-500 ml-1">*</span>
                                    </label>
                                    <div class="relative">
                                        <select id="department_id"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 appearance-none bg-white @error('department_id') border-red-500 bg-red-50 @enderror"
                                            wire:model.live="department_id">
                                            <option value="">{{ __('messages.select_department') }}</option>
                                            @foreach($departments as $department)
                                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            @error('department_id')
                                                <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                                            @enderror
                                            <i class="fas fa-chevron-down text-gray-400"></i>
                                        </div>
                                    </div>
                                    @error('department_id')
                                        <p class="flex items-center text-sm text-red-600">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Position -->
                                <div class="space-y-2">
                                    <label for="position_id" class="flex items-center text-sm font-medium text-gray-700">
                                        <i class="fas fa-user-tie text-purple-500 mr-2"></i>
                                        {{ __('messages.position') }} <span class="text-red-500 ml-1">*</span>
                                    </label>
                                    <div class="relative">
                                        <select id="position_id"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 appearance-none bg-white @error('position_id') border-red-500 bg-red-50 @enderror"
                                            wire:model.live="position_id">
                                            <option value="">{{ __('messages.select_position') }}</option>
                                            @foreach($positions as $position)
                                                <option value="{{ $position->id }}">{{ $position->title }}</option>
                                            @endforeach
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            @error('position_id')
                                                <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                                            @enderror
                                            <i class="fas fa-chevron-down text-gray-400"></i>
                                        </div>
                                    </div>
                                    @error('position_id')
                                        <p class="flex items-center text-sm text-red-600">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Hire Date -->
                                <div class="space-y-2">
                                    <label for="hire_date" class="flex items-center text-sm font-medium text-gray-700">
                                        <i class="fas fa-calendar text-green-500 mr-2"></i>
                                        {{ __('messages.hire_date') }} <span class="text-red-500 ml-1">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="date" id="hire_date"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 @error('hire_date') border-red-500 bg-red-50 @enderror"
                                            wire:model.live="hire_date">
                                        @error('hire_date')
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                            </div>
                                        @enderror
                                    </div>
                                    @error('hire_date')
                                        <p class="flex items-center text-sm text-red-600">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Employment Status -->
                                <div class="space-y-2">
                                    <label for="employment_status" class="flex items-center text-sm font-medium text-gray-700">
                                        <i class="fas fa-check-circle text-emerald-500 mr-2"></i>
                                        {{ __('messages.employment_status') }} <span class="text-red-500 ml-1">*</span>
                                    </label>
                                    <div class="relative">
                                        <select id="employment_status"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-200 appearance-none bg-white @error('employment_status') border-red-500 bg-red-50 @enderror"
                                            wire:model.live="employment_status">
                                            <option value="">{{ __('messages.select_status_employment') }}</option>
                                            <option value="active">{{ __('messages.active') }}</option>
                                            <option value="on_leave">{{ __('messages.on_leave') }}</option>
                                            <option value="terminated">{{ __('messages.terminated') }}</option>
                                            <option value="suspended">{{ __('messages.suspended') }}</option>
                                            <option value="retired">{{ __('messages.retired') }}</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            @error('employment_status')
                                                <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                                            @enderror
                                            <i class="fas fa-chevron-down text-gray-400"></i>
                                        </div>
                                    </div>
                                    @error('employment_status')
                                        <p class="flex items-center text-sm text-red-600">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- INSS Number -->
                                <div class="space-y-2">
                                    <label for="inss_number" class="flex items-center text-sm font-medium text-gray-700">
                                        <i class="fas fa-shield-alt text-cyan-500 mr-2"></i>
                                        {{ __('messages.inss_number') }}
                                    </label>
                                    <div class="relative">
                                        <input type="text" id="inss_number"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-all duration-200 @error('inss_number') border-red-500 bg-red-50 @enderror"
                                            wire:model.live="inss_number" 
                                            placeholder="{{ __('messages.inss_number_placeholder') }}">
                                        @error('inss_number')
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                            </div>
                                        @enderror
                                    </div>
                                    @error('inss_number')
                                        <p class="flex items-center text-sm text-red-600">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Base Salary -->
                                <div class="space-y-2">
                                    <label for="base_salary" class="flex items-center text-sm font-medium text-gray-700">
                                        <i class="fas fa-money-bill text-yellow-500 mr-2"></i>
                                        {{ __('messages.base_salary') }}
                                    </label>
                                    <div class="relative">
                                        <input type="number" step="0.01" min="0" id="base_salary"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-all duration-200 @error('base_salary') border-red-500 bg-red-50 @enderror"
                                            wire:model.live="base_salary" 
                                            placeholder="{{ __('messages.base_salary_placeholder') }}">
                                        @error('base_salary')
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                            </div>
                                        @enderror
                                    </div>
                                    @error('base_salary')
                                        <p class="flex items-center text-sm text-red-600">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Benefits Section -->
                    <div class="border-t border-gray-200">
                        <div class="px-6 py-5">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="bg-gradient-to-r from-emerald-500 to-teal-600 p-2 rounded-lg">
                                        <i class="fas fa-hand-holding-usd text-white"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h4 class="text-lg font-semibold text-gray-900">{{ __('messages.benefits') }}</h4>
                                        <p class="text-sm text-gray-500">{{ __('messages.salary_info_description') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Food Benefit -->
                                <div class="space-y-2">
                                    <label for="food_benefit" class="flex items-center text-sm font-medium text-gray-700">
                                        <i class="fas fa-utensils text-orange-500 mr-2"></i>
                                        {{ __('messages.food_benefit') }}
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <span class="text-gray-500 text-sm font-medium">AOA</span>
                                        </div>
                                        <input type="number" step="0.01" min="0" id="food_benefit"
                                            class="w-full pl-16 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all duration-200 @error('food_benefit') border-red-500 bg-red-50 @enderror"
                                            wire:model.live="food_benefit" 
                                            placeholder="0.00">
                                        @error('food_benefit')
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                            </div>
                                        @enderror
                                    </div>
                                    @error('food_benefit')
                                        <p class="flex items-center text-sm text-red-600">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            {{ $message }}
                                        </p>
                                    @else
                                        <p class="text-xs text-gray-500">{{ __('messages.optional_benefit') }}</p>
                                    @enderror
                                </div>

                                <!-- Transport Benefit -->
                                <div class="space-y-2">
                                    <label for="transport_benefit" class="flex items-center text-sm font-medium text-gray-700">
                                        <i class="fas fa-bus text-blue-500 mr-2"></i>
                                        {{ __('messages.transport_benefit') }}
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <span class="text-gray-500 text-sm font-medium">AOA</span>
                                        </div>
                                        <input type="number" step="0.01" min="0" id="transport_benefit"
                                            class="w-full pl-16 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 @error('transport_benefit') border-red-500 bg-red-50 @enderror"
                                            wire:model.live="transport_benefit" 
                                            placeholder="0.00">
                                        @error('transport_benefit')
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                            </div>
                                        @enderror
                                    </div>
                                    @error('transport_benefit')
                                        <p class="flex items-center text-sm text-red-600">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            {{ $message }}
                                        </p>
                                    @else
                                        <p class="text-xs text-gray-500">{{ __('messages.optional_benefit') }}</p>
                                    @enderror
                                </div>

                                <!-- Bonus Amount -->
                                <div class="space-y-2">
                                    <label for="bonus_amount" class="flex items-center text-sm font-medium text-gray-700">
                                        <i class="fas fa-gift text-purple-500 mr-2"></i>
                                        {{ __('messages.bonus_amount') }}
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <span class="text-gray-500 text-sm font-medium">AOA</span>
                                        </div>
                                        <input type="number" step="0.01" min="0" id="bonus_amount"
                                            class="w-full pl-16 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 @error('bonus_amount') border-red-500 bg-red-50 @enderror"
                                            wire:model.live="bonus_amount" 
                                            placeholder="0.00">
                                        @error('bonus_amount')
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                            </div>
                                        @enderror
                                    </div>
                                    @error('bonus_amount')
                                        <p class="flex items-center text-sm text-red-600">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            {{ $message }}
                                        </p>
                                    @else
                                        <p class="text-xs text-gray-500">{{ __('messages.optional_benefit') }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($isEditing)
                    <div class="px-6 py-4">
                        <h4 class="text-md font-medium text-gray-700 mb-2 border-b pb-1">{{ __('messages.documents') }}</h4>
                        <div class="mt-2 bg-gray-50 p-3 rounded">
                            <div class="flex flex-col">
                                <div class="flex justify-between items-center mb-3">
                                    <h5 class="text-sm font-medium text-gray-700">{{ __('messages.employee_documents') }}</h5>
                                    <button type="button" wire:click="showDocumentUploadModal"
                                        class="px-3 py-1 bg-blue-500 text-white rounded-md text-sm hover:bg-blue-600 transition">
                                        <i class="fas fa-plus mr-1"></i> {{ __('messages.add_document') }}
                                    </button>
                                </div>
                                
                                @if(count($employeeDocuments) > 0)
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full bg-white border border-gray-200 text-sm">
                                            <thead>
                                                <tr class="bg-gray-100">
                                                    <th class="py-2 px-3 text-left border-b">{{ __('messages.type') }}</th>
                                                    <th class="py-2 px-3 text-left border-b">{{ __('messages.title') }}</th>
                                                    <th class="py-2 px-3 text-left border-b">{{ __('messages.expiry_date') }}</th>
                                                    <th class="py-2 px-3 text-left border-b">{{ __('messages.status') }}</th>
                                                    <th class="py-2 px-3 text-left border-b">{{ __('messages.actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($employeeDocuments as $document)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="py-2 px-3 border-b">
                                                        @switch($document->document_type)
                                                            @case('id_card')
                                                                <span class="inline-flex items-center bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                                                                    <i class="fas fa-id-card mr-1"></i> {{ __('messages.id_card_doc') }}
                                                                </span>
                                                                @break
                                                            @case('certificate')
                                                                <span class="inline-flex items-center bg-green-100 text-green-800 text-xs px-2 py-1 rounded">
                                                                    <i class="fas fa-certificate mr-1"></i> {{ __('messages.certificate') }}
                                                                </span>
                                                                @break
                                                            @case('professional_card')
                                                                <span class="inline-flex items-center bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded">
                                                                    <i class="fas fa-id-badge mr-1"></i> {{ __('messages.professional_card') }}
                                                                </span>
                                                                @break
                                                            @case('contract')
                                                                <span class="inline-flex items-center bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded">
                                                                    <i class="fas fa-file-contract mr-1"></i> {{ __('messages.contract') }}
                                                                </span>
                                                                @break
                                                            @default
                                                                <span class="inline-flex items-center bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded">
                                                                    <i class="fas fa-file mr-1"></i> {{ __('messages.other_doc') }}
                                                                </span>
                                                        @endswitch
                                                    </td>
                                                    <td class="py-2 px-3 border-b">{{ $document->title }}</td>
                                                    <td class="py-2 px-3 border-b">
                                                        @if($document->expiry_date)
                                                            {{ \Carbon\Carbon::parse($document->expiry_date)->format('d/m/Y') }}
                                                        @else
                                                            <span class="text-gray-400">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-2 px-3 border-b">
                                                        @if($document->is_verified)
                                                            <span class="inline-flex items-center bg-green-100 text-green-800 text-xs px-2 py-1 rounded">
                                                                <i class="fas fa-check-circle mr-1"></i> {{ __('messages.verified') }}
                                                                @if($document->verification_date)
                                                                    <span class="mx-1 text-xs text-gray-500">{{ \Carbon\Carbon::parse($document->verification_date)->format('d/m/Y') }}</span>
                                                                @endif
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded">
                                                                <i class="fas fa-clock mr-1"></i> {{ __('messages.pending') }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="py-2 px-3 border-b">
                                                        <div class="flex space-x-2">
                                                            <button type="button" wire:click="downloadDocument({{ $document->id }})"
                                                                class="text-blue-600 hover:text-blue-800">
                                                                <i class="fas fa-download"></i>
                                                            </button>
                                                            <button type="button" wire:click="toggleDocumentVerification({{ $document->id }})"
                                                                class="{{ $document->is_verified ? 'text-yellow-600 hover:text-yellow-800' : 'text-green-600 hover:text-green-800' }}">
                                                                <i class="fas {{ $document->is_verified ? 'fa-times-circle' : 'fa-check-circle' }}"></i>
                                                            </button>
                                                            <button type="button" 
                                                                wire:click="confirmDeleteDocument({{ $document->id }})"
                                                                onclick="confirm('Are you sure you want to delete this document?') || event.stopImmediatePropagation()"
                                                                class="text-red-600 hover:text-red-800">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="bg-gray-100 p-4 rounded text-center text-gray-600">
                                        <p><i class="fas fa-file-alt text-xl mb-2"></i></p>
                                        <p>No documents found. Click "Add Document" to upload.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Modal Action Buttons -->
                    <div class="border-t border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 rounded-b-xl">
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-gray-500">
                                @if($isEditing)
                                    <i class="fas fa-info-circle mr-1"></i>
                                    {{ __('messages.update_employee_information') }}
                                @else
                                    <i class="fas fa-info-circle mr-1"></i>
                                    {{ __('messages.add_new_employee_information') }}
                                @endif
                            </div>
                            <div class="flex items-center space-x-3">
                                <button type="button"
                                    class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200"
                                    wire:click="closeModal">
                                    <i class="fas fa-times mr-2"></i>
                                    {{ __('messages.cancel') }}
                                </button>
                                <button type="submit"
                                    class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r @if($isEditing) from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 @else from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 @endif hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 @if($isEditing) focus:ring-blue-500 @else focus:ring-green-500 @endif transform transition-all duration-200 hover:scale-105">
                                    @if($isEditing)
                                        <i class="fas fa-save mr-2"></i>
                                        {{ __('messages.update_employee') }}
                                    @else
                                        <i class="fas fa-plus mr-2"></i>
                                        {{ __('messages.create_employee') }}
                                    @endif
                                </button>
                            </div>
                        </div>
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
                        Delete Employee
                    </h3>
                    <button type="button" class="text-gray-500 hover:text-gray-700 text-xl" wire:click="closeDeleteModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="mb-4">
                    <p class="text-gray-700">Are you sure you want to delete this employee? This action cannot be undone.</p>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
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

    <!-- View Employee Modal -->
    @if($showViewModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto m-4">
            <div class="flex justify-between items-center p-6 pb-3 border-b sticky top-0 bg-white z-10">
                <h3 class="text-xl font-semibold flex items-center text-blue-700">
                    <i class="fas fa-user-circle text-blue-500 mr-2 text-2xl"></i>
                    Employee Details
                </h3>
                <button type="button" class="text-gray-500 hover:text-gray-700 text-xl" wire:click="closeViewModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6">
                <!-- Employee Photo and Basic Info -->
                <div class="md:col-span-1 flex flex-col items-center bg-gray-50 p-6 rounded-lg shadow-sm">
                    <div class="w-32 h-32 bg-gray-200 rounded-full overflow-hidden mb-4 border-4 border-blue-100 shadow-md">
                        @if($photo)
                            <img src="{{ asset('storage/' . $photo) }}" alt="{{ $full_name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-400 bg-blue-50">
                                <i class="fas fa-user text-5xl text-blue-300"></i>
                            </div>
                        @endif
                    </div>
                    <h4 class="font-bold text-xl text-center text-gray-800">{{ $full_name }}</h4>
                    @if(isset($position_id) && $positions->contains('id', $position_id))
                        <p class="text-sm text-gray-600 text-center flex items-center mt-1">
                            <i class="fas fa-briefcase text-blue-500 mr-1"></i>
                            {{ $positions->firstWhere('id', $position_id)->title }}
                        </p>
                    @endif
                    @if(isset($department_id) && $departments->contains('id', $department_id))
                        <p class="text-sm text-gray-600 text-center flex items-center mt-1">
                            <i class="fas fa-building text-blue-500 mr-1"></i>
                            {{ $departments->firstWhere('id', $department_id)->name }} Department
                        </p>
                    @endif
                    
                    <div class="mt-5 w-full">
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-100 shadow-sm">
                            <h5 class="font-semibold text-blue-800 mb-3 flex items-center">
                                <i class="fas fa-id-badge mr-2"></i>Employment Information
                            </h5>
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div class="text-gray-600 flex items-center">
                                    <i class="fas fa-dot-circle text-blue-400 mr-1 text-xs"></i>Status:
                                </div>
                                <div class="font-medium">
                                    @switch($employment_status)
                                        @case('active')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i> Active
                                            </span>
                                            @break
                                        @case('on_leave')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-hourglass-half mr-1"></i> On Leave
                                            </span>
                                            @break
                                        @case('terminated')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-times-circle mr-1"></i> Terminated
                                            </span>
                                            @break
                                        @case('suspended')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                <i class="fas fa-pause-circle mr-1"></i> Suspended
                                            </span>
                                            @break
                                        @case('retired')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <i class="fas fa-user-clock mr-1"></i> Retired
                                            </span>
                                            @break
                                        @default
                                            {{ $employment_status }}
                                    @endswitch
                                </div>
                                <div class="text-gray-600 flex items-center">
                                    <i class="fas fa-dot-circle text-blue-400 mr-1 text-xs"></i>Hire Date:
                                </div>
                                <div class="font-medium">
                                    <span class="flex items-center">
                                        <i class="far fa-calendar-alt text-blue-500 mr-1"></i>
                                        {{ $hire_date ? date('d/m/Y', strtotime($hire_date)) : 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Personal and Contact Information -->
                <div class="md:col-span-2">
                    <div class="mb-6 bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                        <h4 class="text-md font-semibold text-blue-700 mb-3 border-b pb-2 flex items-center">
                            <i class="fas fa-user mr-2 text-blue-500"></i>Personal Information
                        </h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-gray-600 flex items-center mb-1">
                                    <i class="fas fa-birthday-cake text-blue-400 mr-2"></i>Date of Birth:
                                </p>
                                <p class="font-medium pl-6">{{ $date_of_birth ? date('d/m/Y', strtotime($date_of_birth)) : 'N/A' }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-gray-600 flex items-center mb-1">
                                    <i class="fas fa-venus-mars text-blue-400 mr-2"></i>Gender:
                                </p>
                                <p class="font-medium pl-6">{{ $gender ? ucfirst($gender) : 'N/A' }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-gray-600 flex items-center mb-1">
                                    <i class="fas fa-id-card text-blue-400 mr-2"></i>ID Card:
                                </p>
                                <p class="font-medium pl-6">{{ $id_card ?: 'N/A' }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-gray-600 flex items-center mb-1">
                                    <i class="fas fa-receipt text-blue-400 mr-2"></i>Tax Number:
                                </p>
                                <p class="font-medium pl-6">{{ $tax_number ?: 'N/A' }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-gray-600 flex items-center mb-1">
                                    <i class="fas fa-ring text-blue-400 mr-2"></i>Marital Status:
                                </p>
                                <p class="font-medium pl-6">{{ $marital_status ? ucfirst($marital_status) : 'N/A' }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-gray-600 flex items-center mb-1">
                                    <i class="fas fa-users text-blue-400 mr-2"></i>Dependents:
                                </p>
                                <p class="font-medium pl-6">{{ $dependents ?: '0' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-6 bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                        <h4 class="text-md font-semibold text-blue-700 mb-3 border-b pb-2 flex items-center">
                            <i class="fas fa-address-card mr-2 text-blue-500"></i>Contact Information
                        </h4>
                        <div class="grid grid-cols-1 gap-4 text-sm">
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-gray-600 flex items-center mb-1">
                                    <i class="fas fa-envelope text-blue-400 mr-2"></i>Email:
                                </p>
                                <p class="font-medium pl-6">{{ $email ?: 'N/A' }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-gray-600 flex items-center mb-1">
                                    <i class="fas fa-phone text-blue-400 mr-2"></i>Phone:
                                </p>
                                <p class="font-medium pl-6">{{ $phone ?: 'N/A' }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-gray-600 flex items-center mb-1">
                                    <i class="fas fa-map-marker-alt text-blue-400 mr-2"></i>Address:
                                </p>
                                <p class="font-medium pl-6">{{ $address ?: 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4 bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                        <h4 class="text-md font-semibold text-blue-700 mb-3 border-b pb-2 flex items-center">
                            <i class="fas fa-university mr-2 text-blue-500"></i>Bank Information
                        </h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-gray-600 flex items-center mb-1">
                                    <i class="fas fa-landmark text-blue-400 mr-2"></i>Bank Name:
                                </p>
                                <p class="font-medium pl-6">{{ $bank_name ?: 'N/A' }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-gray-600 flex items-center mb-1">
                                    <i class="fas fa-credit-card text-blue-400 mr-2"></i>Bank Account:
                                </p>
                                <p class="font-medium pl-6">{{ $bank_account ?: 'N/A' }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-gray-600 flex items-center mb-1">
                                    <i class="fas fa-id-badge text-blue-400 mr-2"></i>INSS Number:
                                </p>
                                <p class="font-medium pl-6">{{ $inss_number ?: 'N/A' }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-gray-600 flex items-center mb-1">
                                    <i class="fas fa-money-bill-wave text-blue-400 mr-2"></i>Base Salary:
                                </p>
                                <p class="font-medium pl-6">{{ $base_salary ? number_format($base_salary, 2, ',', '.') : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employee Benefits -->
            <div class="px-6 pb-4">
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                    <h4 class="text-md font-semibold text-green-700 mb-3 border-b pb-2 flex items-center">
                        <i class="fas fa-hand-holding-usd mr-2 text-green-500"></i>
                        {{ __('messages.benefits') }}
                    </h4>
                    <div class="grid grid-cols-3 gap-4 text-sm">
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-gray-600 flex items-center mb-1">
                                <i class="fas fa-utensils text-green-400 mr-2"></i>{{ __('messages.food_benefit') }}:
                            </p>
                            <p class="font-medium pl-6">
                                @if($food_benefit)
                                    AOA {{ number_format($food_benefit, 2, ',', '.') }}
                                @else
                                    <span class="text-gray-500 italic">{{ __('messages.not_applicable') }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-gray-600 flex items-center mb-1">
                                <i class="fas fa-bus text-green-400 mr-2"></i>{{ __('messages.transport_benefit') }}:
                            </p>
                            <p class="font-medium pl-6">
                                @if($transport_benefit)
                                    AOA {{ number_format($transport_benefit, 2, ',', '.') }}
                                @else
                                    <span class="text-gray-500 italic">{{ __('messages.not_applicable') }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-gray-600 flex items-center mb-1">
                                <i class="fas fa-gift text-green-400 mr-2"></i>{{ __('messages.bonus_amount') }}:
                            </p>
                            <p class="font-medium pl-6">
                                @if($bonus_amount)
                                    AOA {{ number_format($bonus_amount, 2, ',', '.') }}
                                @else
                                    <span class="text-gray-500 italic">{{ __('messages.not_applicable') }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employee Salary Advances -->
            <div class="px-6 pb-4">
                @php
                    $employeeSalaryAdvances = collect();
                    if ($employee_id) {
                        $employee = \App\Models\HR\Employee::with('salaryAdvances')->find($employee_id);
                        $employeeSalaryAdvances = $employee ? $employee->salaryAdvances : collect();
                    }
                @endphp
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                    <h4 class="text-md font-semibold text-orange-700 mb-3 border-b pb-2 flex items-center">
                        <i class="fas fa-hand-holding-usd mr-2 text-orange-500"></i>
                        {{ __('messages.salary_advances') }}
                    </h4>
                    @if($employeeSalaryAdvances->count() > 0)
                        <div class="space-y-3">
                            @foreach($employeeSalaryAdvances->take(5) as $advance)
                                <div class="bg-gray-50 p-3 rounded-lg border-l-4 {{ $advance->status === 'approved' ? 'border-green-500' : ($advance->status === 'pending' ? 'border-yellow-500' : 'border-red-500') }}">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2 mb-1">
                                                <span class="text-sm font-medium text-gray-900">
                                                    AOA {{ number_format($advance->amount, 0, ',', '.') }}
                                                </span>
                                                @switch($advance->status)
                                                    @case('approved')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            <i class="fas fa-check mr-1"></i>{{ __('messages.approved') }}
                                                        </span>
                                                        @break
                                                    @case('pending')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                            <i class="fas fa-clock mr-1"></i>{{ __('messages.pending') }}
                                                        </span>
                                                        @break
                                                    @case('rejected')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                            <i class="fas fa-times mr-1"></i>{{ __('messages.rejected') }}
                                                        </span>
                                                        @break
                                                @endswitch
                                            </div>
                                            <div class="text-xs text-gray-600 space-y-1">
                                                <div class="flex items-center">
                                                    <i class="fas fa-calendar-alt mr-1"></i>
                                                    {{ __('messages.requested') }}: {{ $advance->created_at->format('d/m/Y') }}
                                                </div>
                                                @if($advance->status === 'approved' && $advance->remaining_amount > 0)
                                                    <div class="flex items-center text-orange-600">
                                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                                        {{ __('messages.pending_amount') }}: AOA {{ number_format($advance->remaining_amount, 0, ',', '.') }}
                                                    </div>
                                                @endif
                                                @if($advance->reason)
                                                    <div class="flex items-start">
                                                        <i class="fas fa-comment-alt mr-1 mt-0.5"></i>
                                                        <span class="italic">{{ Str::limit($advance->reason, 50) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        @if($advance->status === 'approved')
                                            <div class="text-right text-xs">
                                                <div class="text-green-600 font-medium">
                                                    {{ __('messages.approved') }}
                                                </div>
                                                @if($advance->approved_at)
                                                    <div class="text-gray-500">
                                                        {{ $advance->approved_at->format('d/m/Y') }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            @if($employeeSalaryAdvances->count() > 5)
                                <div class="text-center py-2">
                                    <span class="text-sm text-gray-500">
                                        {{ __('messages.showing') }} 5 {{ __('messages.of') }} {{ $employeeSalaryAdvances->count() }} {{ __('messages.advances') }}
                                    </span>
                                </div>
                            @endif
                            <!-- Summary Statistics -->
                            <div class="grid grid-cols-3 gap-3 mt-4 pt-3 border-t border-gray-200">
                                <div class="text-center">
                                    <div class="text-lg font-semibold text-gray-900">
                                        AOA {{ number_format($employeeSalaryAdvances->sum('amount'), 0, ',', '.') }}
                                    </div>
                                    <div class="text-xs text-gray-600">{{ __('messages.total_advances') }}</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-lg font-semibold text-yellow-600">
                                        AOA {{ number_format($employeeSalaryAdvances->where('status', 'pending')->sum('amount'), 0, ',', '.') }}
                                    </div>
                                    <div class="text-xs text-gray-600">{{ __('messages.pending_advances') }}</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-lg font-semibold text-orange-600">
                                        AOA {{ number_format($employeeSalaryAdvances->where('status', 'approved')->sum('remaining_amount'), 0, ',', '.') }}
                                    </div>
                                    <div class="text-xs text-gray-600">{{ __('messages.pending_amount') }}</div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-orange-100 mb-4">
                                <i class="fas fa-hand-holding-usd text-orange-400 text-2xl"></i>
                            </div>
                            <h3 class="text-sm font-medium text-gray-900 mb-1">{{ __('messages.no_advances') }}</h3>
                            <p class="text-xs text-gray-500">{{ __('messages.no_salary_advances_description') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Employee Documents -->
            <div class="px-6 pb-6">
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                    <h4 class="text-md font-semibold text-blue-700 mb-4 border-b pb-2 flex items-center">
                        <i class="fas fa-file-alt text-blue-500 mr-2"></i>
                        Documents
                    </h4>
                    
                    @if(count($employeeDocuments ?? []) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-200 text-sm rounded-lg overflow-hidden">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="py-3 px-4 text-left border-b font-semibold text-gray-700">Type</th>
                                        <th class="py-3 px-4 text-left border-b font-semibold text-gray-700">Title</th>
                                        <th class="py-3 px-4 text-left border-b font-semibold text-gray-700">Upload Date</th>
                                        <th class="py-3 px-4 text-left border-b font-semibold text-gray-700">Expiry Date</th>
                                        <th class="py-3 px-4 text-left border-b font-semibold text-gray-700">Status</th>
                                        <th class="py-3 px-4 text-left border-b font-semibold text-gray-700">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($employeeDocuments as $document)
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-3 px-4 border-b">
                                            @switch($document->document_type)
                                                @case('id_card')
                                                    <span class="inline-flex items-center bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                                                        <i class="fas fa-id-card mr-1"></i> ID Card
                                                    </span>
                                                    @break
                                                @case('certificate')
                                                    <span class="inline-flex items-center bg-green-100 text-green-800 text-xs px-2 py-1 rounded">
                                                        <i class="fas fa-certificate mr-1"></i> Certificate
                                                    </span>
                                                    @break
                                                @case('professional_card')
                                                    <span class="inline-flex items-center bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded">
                                                        <i class="fas fa-id-badge mr-1"></i> Professional
                                                    </span>
                                                    @break
                                                @case('contract')
                                                    <span class="inline-flex items-center bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded">
                                                        <i class="fas fa-file-contract mr-1"></i> Contract
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="inline-flex items-center bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded">
                                                        <i class="fas fa-file mr-1"></i> Other
                                                    </span>
                                            @endswitch
                                        </td>
                                        <td class="py-3 px-4 border-b">{{ $document->title }}</td>
                                        <td class="py-3 px-4 border-b">
                                            <span class="flex items-center">
                                                <i class="far fa-calendar-plus text-blue-500 mr-1"></i>
                                                {{ $document->created_at->format('d/m/Y') }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 border-b">
                                            @if($document->expiry_date)
                                                <span class="flex items-center">
                                                    <i class="far fa-calendar-times text-red-500 mr-1"></i>
                                                    {{ \Carbon\Carbon::parse($document->expiry_date)->format('d/m/Y') }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">N/A</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 border-b">
                                            @if($document->is_verified)
                                                <span class="inline-flex items-center bg-green-100 text-green-800 text-xs px-2 py-1 rounded">
                                                    <i class="fas fa-check-circle mr-1"></i> Verified
                                                </span>
                                            @else
                                                <span class="inline-flex items-center bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded">
                                                    <i class="fas fa-clock mr-1"></i> Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 border-b">
                                            <button type="button" wire:click="downloadDocument({{ $document->id }})"
                                                class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 px-2 py-1 rounded flex items-center transition-colors">
                                                <i class="fas fa-download mr-1"></i> Download
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="bg-gray-50 p-6 rounded-lg text-center text-gray-600 border border-gray-200">
                            <p><i class="fas fa-file-alt text-2xl mb-2 text-blue-300"></i></p>
                            <p>No documents available for this employee.</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="bg-gray-50 p-6 border-t flex justify-end space-x-3">
                <button type="button" wire:click="closeViewModal" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                    <i class="fas fa-times mr-2"></i>
                    Close
                </button>
                <button type="button" wire:click="edit({{ $employee_id }})" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <i class="fas fa-edit mr-2"></i> Edit
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Document Upload Modal -->
    @if($showDocumentModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden m-4 transform transition-all duration-300 ease-out">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-4 rounded-t-xl">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="bg-white/20 p-2 rounded-lg mr-3">
                            <i class="fas fa-file-upload text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-white">{{ __('messages.upload_document') }}</h3>
                            <p class="text-blue-100 text-sm">{{ __('messages.upload_document_description') }}</p>
                        </div>
                    </div>
                    <button type="button" 
                        class="text-white/80 hover:text-white hover:bg-white/20 rounded-lg p-2 transition-all duration-200" 
                        wire:click="closeDocumentModal">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Content -->
            <div class="max-h-[calc(90vh-120px)] overflow-y-auto">
                <!-- Error Messages -->
                @if($errors->any())
                    <div class="mx-6 mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                            <p class="font-semibold text-red-700">{{ __('messages.please_correct_errors') }}</p>
                        </div>
                        <ul class="mt-2 text-sm text-red-600 space-y-1">
                            @foreach($errors->all() as $error)
                                <li class="flex items-center">
                                    <i class="fas fa-dot-circle text-xs mr-2"></i>
                                    {{ $error }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form wire:submit.prevent="uploadDocument" class="p-6" id="uploadDocument">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Document Type -->
                        <div class="md:col-span-2 space-y-2">
                            <label for="newDocumentType" class="flex items-center text-sm font-medium text-gray-700">
                                <i class="fas fa-file-alt text-indigo-500 mr-2"></i>
                                {{ __('messages.document_type') }} <span class="text-red-500 ml-1">*</span>
                            </label>
                            <div class="relative">
                                <select id="newDocumentType" wire:model="newDocumentType" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 appearance-none bg-white @error('newDocumentType') border-red-500 bg-red-50 @enderror">
                                    <option value="">{{ __('messages.select_document_type') }}</option>
                                    <option value="id_card">🆔 {{ __('messages.id_card') }}</option>
                                    <option value="passport">🛂 {{ __('messages.passport') }}</option>
                                    <option value="visa">✈️ {{ __('messages.visa') }}</option>
                                    <option value="driving_license">🚗 {{ __('messages.driving_license') }}</option>
                                    <option value="certificate">📜 {{ __('messages.certificate') }}</option>
                                    <option value="diploma">🎓 {{ __('messages.diploma') }}</option>
                                    <option value="professional_card">💼 {{ __('messages.professional_card') }}</option>
                                    <option value="work_permit">📋 {{ __('messages.work_permit') }}</option>
                                    <option value="contract">📄 {{ __('messages.contract') }}</option>
                                    <option value="health_certificate">🏥 {{ __('messages.health_certificate') }}</option>
                                    <option value="insurance">🛡️ {{ __('messages.insurance') }}</option>
                                    <option value="tax_clearance">💰 {{ __('messages.tax_clearance') }}</option>
                                    <option value="criminal_record">⚖️ {{ __('messages.criminal_record') }}</option>
                                    <option value="other">📁 {{ __('messages.other') }}</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    @error('newDocumentType')
                                        <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                                    @enderror
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                            @error('newDocumentType')
                                <p class="flex items-center text-sm text-red-600">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Document Title -->
                        <div class="space-y-2">
                            <label for="newDocumentTitle" class="flex items-center text-sm font-medium text-gray-700">
                                <i class="fas fa-tag text-green-500 mr-2"></i>
                                {{ __('messages.document_title') }} <span class="text-red-500 ml-1">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" id="newDocumentTitle" wire:model="newDocumentTitle" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 @error('newDocumentTitle') border-red-500 bg-red-50 @enderror"
                                    placeholder="{{ __('messages.document_title_placeholder') }}">
                                @error('newDocumentTitle')
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                    </div>
                                @enderror
                            </div>
                            @error('newDocumentTitle')
                                <p class="flex items-center text-sm text-red-600">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Expiry Date -->
                        <div class="space-y-2">
                            <label for="newDocumentExpiryDate" class="flex items-center text-sm font-medium text-gray-700">
                                <i class="fas fa-calendar-alt text-yellow-500 mr-2"></i>
                                {{ __('messages.expiry_date_optional') }}
                            </label>
                            <div class="relative">
                                <input type="date" id="newDocumentExpiryDate" wire:model="newDocumentExpiryDate" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-all duration-200 @error('newDocumentExpiryDate') border-red-500 bg-red-50 @enderror">
                                @error('newDocumentExpiryDate')
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                    </div>
                                @enderror
                            </div>
                            @error('newDocumentExpiryDate')
                                <p class="flex items-center text-sm text-red-600">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Document File -->
                        <div class="md:col-span-2 space-y-2">
                            <label for="newDocumentFile" class="flex items-center text-sm font-medium text-gray-700">
                                <i class="fas fa-cloud-upload-alt text-purple-500 mr-2"></i>
                                {{ __('messages.upload_file') }} <span class="text-red-500 ml-1">*</span>
                            </label>
                            <div class="relative">
                                <input type="file" id="newDocumentFile" 
                                    accept="image/*,.pdf,.doc,.docx"
                                    class="block w-full text-sm text-gray-600 file:mr-4 file:py-3 file:px-6 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 file:transition-all file:duration-200 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 @error('newDocumentFile') border-red-500 bg-red-50 @enderror"
                                    wire:model="newDocumentFile">
                            </div>
                            <p class="flex items-center text-xs text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>
                                {{ __('messages.file_size_restrictions') }}
                            </p>
                            @error('newDocumentFile')
                                <p class="flex items-center text-sm text-red-600">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Remarks -->
                        <div class="md:col-span-2 space-y-2">
                            <label for="newDocumentRemarks" class="flex items-center text-sm font-medium text-gray-700">
                                <i class="fas fa-comment-alt text-cyan-500 mr-2"></i>
                                {{ __('messages.remarks_optional') }}
                            </label>
                            <div class="relative">
                                <textarea id="newDocumentRemarks" wire:model="newDocumentRemarks" rows="3" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-all duration-200 resize-none @error('newDocumentRemarks') border-red-500 bg-red-50 @enderror"
                                    placeholder="{{ __('messages.document_remarks_placeholder') }}"></textarea>
                                @error('newDocumentRemarks')
                                    <div class="absolute top-3 right-3 pointer-events-none">
                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                    </div>
                                @enderror
                            </div>
                            @error('newDocumentRemarks')
                                <p class="flex items-center text-sm text-red-600">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="border-t border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 rounded-b-xl">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        {{ __('messages.upload_document_footer_info') }}
                    </div>
                    <div class="flex items-center space-x-3">
                        <button type="button" wire:click="closeDocumentModal"
                            class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                            <i class="fas fa-times mr-2"></i>
                            {{ __('messages.cancel') }}
                        </button>
                        <button type="submit" form="uploadDocument"
                            class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transform transition-all duration-200 hover:scale-105">
                            <i class="fas fa-cloud-upload-alt mr-2"></i>
                            {{ __('messages.upload_document') }}
                        </button>
                    </div>
                </div>
            </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- View Employee Modal -->
    @if($showViewModal && $viewEmployee)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden m-4 transform transition-all duration-300 ease-out">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-700 px-6 py-4 rounded-t-xl">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="bg-white/20 p-2 rounded-lg mr-3">
                            <i class="fas fa-user text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-white">{{ __('messages.employee_details') }}</h3>
                            <p class="text-indigo-100 text-sm">{{ $viewEmployee->full_name }}</p>
                        </div>
                    </div>
                    <button type="button" 
                        class="text-white/80 hover:text-white hover:bg-white/20 rounded-lg p-2 transition-all duration-200" 
                        wire:click="closeViewModal">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Expiring Documents Alert -->
            @if($expiringDocuments->count() > 0)
            <div class="mx-6 mt-4 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-amber-500 mr-3 animate-pulse"></i>
                    <div>
                        <h4 class="font-semibold text-amber-800">{{ __('messages.documents_expiring_soon') }}</h4>
                        <p class="text-sm text-amber-700">{{ __('messages.documents_expire_within_30_days', ['count' => $expiringDocuments->count()]) }}</p>
                    </div>
                </div>
                <div class="mt-3 space-y-2">
                    @foreach($expiringDocuments as $document)
                    <div class="flex items-center justify-between bg-white rounded-lg p-3 border border-amber-200">
                        <div class="flex items-center">
                            <i class="fas fa-file-alt text-amber-600 mr-2"></i>
                            <span class="font-medium text-gray-800">{{ $document->title }}</span>
                        </div>
                        <span class="text-sm font-medium text-amber-700">
                            {{ __('messages.expires_on') }}: {{ \Carbon\Carbon::parse($document->expiry_date)->format('d/m/Y') }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Modal Content -->
            <div class="max-h-[calc(90vh-150px)] overflow-y-auto p-4 sm:p-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">
                    <!-- Employee Information -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Personal Information -->
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6">
                            <div class="flex items-center mb-4">
                                <div class="bg-blue-100 p-2 rounded-lg mr-3">
                                    <i class="fas fa-user text-blue-600"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-800">{{ __('messages.personal_information') }}</h4>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">{{ __('messages.full_name') }}</label>
                                    <p class="text-gray-800 font-medium">{{ $viewEmployee->full_name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">{{ __('messages.date_of_birth') }}</label>
                                    <p class="text-gray-800">{{ $viewEmployee->date_of_birth ? \Carbon\Carbon::parse($viewEmployee->date_of_birth)->format('d/m/Y') : '-' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">{{ __('messages.gender') }}</label>
                                    <p class="text-gray-800">{{ $viewEmployee->gender ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">{{ __('messages.id_card') }}</label>
                                    <p class="text-gray-800">{{ $viewEmployee->id_card ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">{{ __('messages.phone') }}</label>
                                    <p class="text-gray-800">{{ $viewEmployee->phone ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">{{ __('messages.email') }}</label>
                                    <p class="text-gray-800">{{ $viewEmployee->email ?? '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Employment Information -->
                        <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-6">
                            <div class="flex items-center mb-4">
                                <div class="bg-green-100 p-2 rounded-lg mr-3">
                                    <i class="fas fa-briefcase text-green-600"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-800">{{ __('messages.employment_information') }}</h4>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">{{ __('messages.department') }}</label>
                                    <p class="text-gray-800 font-medium">{{ $viewEmployee->department->name ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">{{ __('messages.position') }}</label>
                                    <p class="text-gray-800 font-medium">{{ $viewEmployee->position->name ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">{{ __('messages.hire_date') }}</label>
                                    <p class="text-gray-800">{{ $viewEmployee->hire_date ? \Carbon\Carbon::parse($viewEmployee->hire_date)->format('d/m/Y') : '-' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">{{ __('messages.employment_status') }}</label>
                                    <p class="text-gray-800">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($viewEmployee->employment_status === 'active') bg-green-100 text-green-800
                                            @elseif($viewEmployee->employment_status === 'inactive') bg-red-100 text-red-800
                                            @else bg-yellow-100 text-yellow-800 @endif">
                                            {{ ucfirst($viewEmployee->employment_status) }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Employee Photo -->
                    <div class="space-y-6">
                        <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-6">
                            <div class="flex items-center mb-4">
                                <div class="bg-purple-100 p-2 rounded-lg mr-3">
                                    <i class="fas fa-camera text-purple-600"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-800">{{ __('messages.photo') }}</h4>
                            </div>
                            <div class="flex justify-center">
                                @if($viewEmployee->photo)
                                    <img src="{{ Storage::url($viewEmployee->photo) }}" 
                                         alt="{{ $viewEmployee->full_name }}" 
                                         class="w-32 h-32 object-cover rounded-full border-4 border-white shadow-lg">
                                @else
                                    <div class="w-32 h-32 bg-gray-300 rounded-full flex items-center justify-center border-4 border-white shadow-lg">
                                        <i class="fas fa-user text-gray-500 text-4xl"></i>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documents Section -->
                <div class="mt-6 bg-gradient-to-r from-gray-50 to-slate-50 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="bg-gray-100 p-2 rounded-lg mr-3">
                                <i class="fas fa-folder text-gray-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">{{ __('messages.documents') }}</h4>
                        </div>
                        <span class="text-sm text-gray-500">{{ $viewEmployee->documents->count() }} {{ __('messages.documents_total') }}</span>
                    </div>
                    
                    @if($viewEmployee->documents->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                            @foreach($viewEmployee->documents as $document)
                            @php
                                $extension = pathinfo($document->file_path, PATHINFO_EXTENSION);
                                $canPreview = in_array(strtolower($extension), ['pdf', 'jpg', 'jpeg', 'png', 'gif']);
                                $fileUrl = Storage::url($document->file_path);
                            @endphp
                            <div class="bg-white rounded-lg p-4 border border-gray-200 hover:shadow-lg hover:border-blue-300 transition-all duration-200 group cursor-pointer"
                                 onclick="window.open('{{ $fileUrl }}', '_blank')"
                                 title="{{ $canPreview ? __('messages.click_to_view') : __('messages.click_to_download') }}">
                                <div class="space-y-3">
                                    <!-- Document Header -->
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center mb-1">
                                                @php
                                                    $iconClass = match(strtolower($extension)) {
                                                        'pdf' => 'fas fa-file-pdf text-red-500',
                                                        'doc', 'docx' => 'fas fa-file-word text-blue-600',
                                                        'xls', 'xlsx' => 'fas fa-file-excel text-green-600',
                                                        'jpg', 'jpeg', 'png', 'gif' => 'fas fa-file-image text-purple-500',
                                                        default => 'fas fa-file-alt text-gray-500'
                                                    };
                                                @endphp
                                                <i class="{{ $iconClass }} mr-2 text-lg group-hover:scale-110 transition-transform duration-200"></i>
                                                <h5 class="font-medium text-gray-800 text-sm truncate" title="{{ $document->title }}">{{ $document->title }}</h5>
                                            </div>
                                            <p class="text-xs text-gray-600 mb-2">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-700">
                                                    {{ ucfirst(str_replace('_', ' ', $document->type)) }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Document Info -->
                                    <div class="space-y-2">
                                        @if($document->expiry_date)
                                            <div class="flex items-center text-xs">
                                                <i class="fas fa-calendar-alt text-gray-400 mr-2"></i>
                                                <span class="text-gray-600">{{ \Carbon\Carbon::parse($document->expiry_date)->format('d/m/Y') }}</span>
                                                @if(\Carbon\Carbon::parse($document->expiry_date)->isPast())
                                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                                        {{ __('messages.expired') }}
                                                    </span>
                                                @elseif(\Carbon\Carbon::parse($document->expiry_date)->diffInDays() <= 30)
                                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">
                                                        <i class="fas fa-clock mr-1"></i>
                                                        {{ __('messages.expiring_soon') }}
                                                    </span>
                                                @else
                                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                        <i class="fas fa-check mr-1"></i>
                                                        {{ __('messages.valid') }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                        
                                        @if($document->created_at)
                                            <div class="flex items-center text-xs text-gray-500">
                                                <i class="fas fa-clock mr-2"></i>
                                                <span>{{ __('messages.uploaded_on') }}: {{ \Carbon\Carbon::parse($document->created_at)->format('d/m/Y H:i') }}</span>
                                            </div>
                                        @endif
                                        
                                        @if($document->remarks)
                                            <div class="text-xs text-gray-600 bg-gray-50 p-2 rounded">
                                                <i class="fas fa-comment-alt mr-1"></i>
                                                {{ Str::limit($document->remarks, 60) }}
                                            </div>
                                        @endif
                                    </div>

                                     <!-- Document Actions -->
                                    <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                                        <div class="flex items-center space-x-2">
                                            <!-- Download Button -->
                                            <a href="{{ $fileUrl }}" 
                                               target="_blank"
                                               class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded transition-all duration-200"
                                               title="{{ __('messages.download') }}"
                                               onclick="event.stopPropagation();">
                                                <i class="fas fa-download mr-1"></i>
                                                {{ __('messages.download') }}
                                            </a>
                                            
                                            <!-- View Button (for images and PDFs) -->
                                            @if($canPreview)
                                                <button type="button"
                                                    onclick="event.stopPropagation(); window.open('{{ $fileUrl }}', '_blank')"
                                                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-600 hover:text-green-800 hover:bg-green-50 rounded transition-all duration-200"
                                                    title="{{ __('messages.view') }}">
                                                    <i class="fas fa-eye mr-1"></i>
                                                    {{ __('messages.view') }}
                                                </button>
                                            @endif
                                            
                                            <!-- Quick Action Indicator -->
                                            <span class="text-xs text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                @if($canPreview)
                                                    <i class="fas fa-mouse-pointer mr-1"></i>{{ __('messages.click_to_view') }}
                                                @else
                                                    <i class="fas fa-mouse-pointer mr-1"></i>{{ __('messages.click_to_download') }}
                                                @endif
                                            </span>
                                        </div>
                                        
                                        <!-- File Size -->
                                        @if(Storage::exists('public/' . $document->file_path))
                                            <span class="text-xs text-gray-400">
                                                {{ number_format(Storage::size('public/' . $document->file_path) / 1024, 1) }} KB
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-folder-open text-gray-400 text-3xl mb-2"></i>
                            <p class="text-gray-500">{{ __('messages.no_documents_found') }}</p>
                        </div>
                    @endif
                </div>

                <!-- Salary Information Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                    <!-- Salary Advances Section -->
                    <div class="bg-gradient-to-r from-amber-50 to-yellow-50 rounded-xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="bg-amber-100 p-2 rounded-lg mr-3">
                                    <i class="fas fa-hand-holding-usd text-amber-600"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-800">{{ __('messages.salary_advances') }}</h4>
                            </div>
                            <span class="text-sm text-gray-500">{{ $viewEmployee->salaryAdvances?->count() ?? 0 }} {{ __('messages.total') }}</span>
                        </div>
                        
                        @if($viewEmployee->salaryAdvances?->count() > 0)
                            <div class="space-y-3 max-h-40 overflow-y-auto">
                                @foreach($viewEmployee->salaryAdvances()->latest()->take(5)->get() as $advance)
                                <div class="bg-white rounded-lg p-3 border border-amber-200">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center mb-1">
                                                <span class="font-medium text-gray-800">{{ number_format($advance->amount, 2) }} AOA</span>
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                    @if($advance->status === 'approved') bg-green-100 text-green-800
                                                    @elseif($advance->status === 'pending') bg-yellow-100 text-yellow-800
                                                    @else bg-red-100 text-red-800 @endif">
                                                    {{ ucfirst($advance->status) }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-600">{{ __('messages.requested_on') }}: {{ \Carbon\Carbon::parse($advance->request_date)->format('d/m/Y') }}</p>
                                            @if($advance->reason)
                                                <p class="text-xs text-gray-500 mt-1">{{ Str::limit($advance->reason, 50) }}</p>
                                            @endif
                                            @if($advance->status === 'approved' && $advance->remaining_amount > 0)
                                                <div class="mt-2">
                                                    <div class="flex justify-between text-xs text-gray-600">
                                                        <span>{{ __('messages.remaining') }}</span>
                                                        <span>{{ number_format($advance->remaining_amount, 2) }} AOA</span>
                                                    </div>
                                                    <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                                        <div class="bg-amber-500 h-1.5 rounded-full" style="width: {{ (($advance->amount - $advance->remaining_amount) / $advance->amount) * 100 }}%"></div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-6">
                                <i class="fas fa-hand-holding text-amber-300 text-2xl mb-2"></i>
                                <p class="text-gray-500 text-sm">{{ __('messages.no_salary_advances') }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Salary Discounts Section -->
                    <div class="bg-gradient-to-r from-red-50 to-pink-50 rounded-xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="bg-red-100 p-2 rounded-lg mr-3">
                                    <i class="fas fa-minus-circle text-red-600"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-800">{{ __('messages.salary_discounts') }}</h4>
                            </div>
                            <span class="text-sm text-gray-500">{{ $viewEmployee->salaryDiscounts?->count() ?? 0 }} {{ __('messages.total') }}</span>
                        </div>
                        
                        @if($viewEmployee->salaryDiscounts?->count() > 0)
                            <div class="space-y-3 max-h-40 overflow-y-auto">
                                @foreach($viewEmployee->salaryDiscounts()->latest()->take(5)->get() as $discount)
                                <div class="bg-white rounded-lg p-3 border border-red-200">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center mb-1">
                                                <span class="font-medium text-gray-800">{{ number_format($discount->amount, 2) }} AOA</span>
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                    @if($discount->status === 'active') bg-red-100 text-red-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ ucfirst($discount->status) }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-600">{{ __('messages.type') }}: {{ ucfirst($discount->type) }}</p>
                                            @if($discount->reason)
                                                <p class="text-xs text-gray-500 mt-1">{{ Str::limit($discount->reason, 50) }}</p>
                                            @endif
                                            @if($discount->start_date)
                                                <p class="text-xs text-gray-600 mt-1">
                                                    {{ __('messages.period') }}: {{ \Carbon\Carbon::parse($discount->start_date)->format('d/m/Y') }}
                                                    @if($discount->end_date)
                                                        - {{ \Carbon\Carbon::parse($discount->end_date)->format('d/m/Y') }}
                                                    @endif
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-6">
                                <i class="fas fa-minus text-red-300 text-2xl mb-2"></i>
                                <p class="text-gray-500 text-sm">{{ __('messages.no_salary_discounts') }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Attendance & Overtime Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                    <!-- Recent Attendance Section -->
                    <div class="bg-gradient-to-r from-blue-50 to-cyan-50 rounded-xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="bg-blue-100 p-2 rounded-lg mr-3">
                                    <i class="fas fa-clock text-blue-600"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-800">{{ __('messages.recent_attendance') }}</h4>
                            </div>
                            <span class="text-sm text-gray-500">{{ __('messages.last_30_days') }}</span>
                        </div>
                        
                        @php
                            $recentAttendance = $viewEmployee->attendances()
                                ->where('date', '>=', now()->subDays(30))
                                ->orderBy('date', 'desc')
                                ->take(5)
                                ->get();
                        @endphp
                        
                        @if($recentAttendance->count() > 0)
                            <div class="space-y-3 max-h-40 overflow-y-auto">
                                @foreach($recentAttendance as $attendance)
                                <div class="bg-white rounded-lg p-3 border border-blue-200">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center mb-1">
                                                <span class="font-medium text-gray-800">{{ \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') }}</span>
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                    @if($attendance->status === 'present') bg-green-100 text-green-800
                                                    @elseif($attendance->status === 'absent') bg-red-100 text-red-800
                                                    @elseif($attendance->status === 'late') bg-yellow-100 text-yellow-800
                                                    @else bg-blue-100 text-blue-800 @endif">
                                                    {{ ucfirst($attendance->status) }}
                                                </span>
                                            </div>
                                            @if($attendance->time_in || $attendance->time_out)
                                                <div class="flex items-center text-xs text-gray-600 space-x-4">
                                                    @if($attendance->time_in)
                                                        <span><i class="fas fa-sign-in-alt mr-1"></i>{{ \Carbon\Carbon::parse($attendance->time_in)->format('H:i') }}</span>
                                                    @endif
                                                    @if($attendance->time_out)
                                                        <span><i class="fas fa-sign-out-alt mr-1"></i>{{ \Carbon\Carbon::parse($attendance->time_out)->format('H:i') }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                            @if($attendance->overtime_hours > 0)
                                                <div class="text-xs text-orange-600 mt-1">
                                                    <i class="fas fa-clock mr-1"></i>{{ __('messages.overtime') }}: {{ $attendance->overtime_hours }}h
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            
                            <!-- Attendance Summary -->
                            @php
                                $monthlyAttendance = $viewEmployee->attendances()
                                    ->where('date', '>=', now()->subDays(30))
                                    ->get();
                                $presentDays = $monthlyAttendance->where('status', 'present')->count();
                                $absentDays = $monthlyAttendance->where('status', 'absent')->count();
                                $lateDays = $monthlyAttendance->where('status', 'late')->count();
                                $totalDays = $monthlyAttendance->count();
                            @endphp
                            
                            @if($totalDays > 0)
                                <div class="mt-4 pt-4 border-t border-blue-200">
                                    <div class="grid grid-cols-3 gap-4 text-center">
                                        <div>
                                            <div class="text-lg font-bold text-green-600">{{ $presentDays }}</div>
                                            <div class="text-xs text-gray-600">{{ __('messages.present') }}</div>
                                        </div>
                                        <div>
                                            <div class="text-lg font-bold text-yellow-600">{{ $lateDays }}</div>
                                            <div class="text-xs text-gray-600">{{ __('messages.late') }}</div>
                                        </div>
                                        <div>
                                            <div class="text-lg font-bold text-red-600">{{ $absentDays }}</div>
                                            <div class="text-xs text-gray-600">{{ __('messages.absent') }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-6">
                                <i class="fas fa-calendar-times text-blue-300 text-2xl mb-2"></i>
                                <p class="text-gray-500 text-sm">{{ __('messages.no_recent_attendance') }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Overtime Records Section -->
                    <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="bg-purple-100 p-2 rounded-lg mr-3">
                                    <i class="fas fa-clock text-purple-600"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-800">{{ __('messages.overtime_records') }}</h4>
                            </div>
                            <span class="text-sm text-gray-500">{{ __('messages.last_30_days') }}</span>
                        </div>
                        
                        @php
                            $overtimeRecords = $viewEmployee->overtimeRecords()
                                ->where('date', '>=', now()->subDays(30))
                                ->where('hours', '>', 0)
                                ->orderBy('date', 'desc')
                                ->take(5)
                                ->get();
                            $totalOvertimeHours = $overtimeRecords->sum('hours');
                        @endphp
                        
                        @if($overtimeRecords->count() > 0)
                            <div class="space-y-3 max-h-40 overflow-y-auto">
                                @foreach($overtimeRecords as $overtime)
                                <div class="bg-white rounded-lg p-3 border border-purple-200">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center mb-1">
                                                <span class="font-medium text-gray-800">{{ \Carbon\Carbon::parse($overtime->date)->format('d/m/Y') }}</span>
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                                    {{ $overtime->hours }}h
                                                </span>
                                            </div>
                                            <div class="flex items-center text-xs text-gray-600 space-x-4">
                                                @if($overtime->start_time)
                                                    <span><i class="fas fa-sign-in-alt mr-1"></i>{{ \Carbon\Carbon::parse($overtime->start_time)->format('H:i') }}</span>
                                                @endif
                                                @if($overtime->end_time)
                                                    <span><i class="fas fa-sign-out-alt mr-1"></i>{{ \Carbon\Carbon::parse($overtime->end_time)->format('H:i') }}</span>
                                                @endif
                                            </div>
                                            @if($overtime->description)
                                                <p class="text-xs text-gray-500 mt-1">{{ Str::limit($overtime->description, 50) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            
                            <!-- Overtime Summary -->
                            <div class="mt-4 pt-4 border-t border-purple-200">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-purple-600">{{ $totalOvertimeHours }}h</div>
                                    <div class="text-xs text-gray-600">{{ __('messages.total_overtime_hours') }}</div>
                                    @if($totalOvertimeHours > 0)
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ __('messages.average_per_day') }}: {{ number_format($totalOvertimeHours / max($overtimeRecords->count(), 1), 1) }}h
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="text-center py-6">
                                <i class="fas fa-clock text-purple-300 text-2xl mb-2"></i>
                                <p class="text-gray-500 text-sm">{{ __('messages.no_overtime_records') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="border-t border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 rounded-b-xl">
                <div class="flex justify-end">
                    <button type="button" wire:click="closeViewModal"
                        class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                        <i class="fas fa-times mr-2"></i>
                        {{ __('messages.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Duplicate Document Confirmation Modal -->
    @if($showDuplicateConfirmation)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center z-50" x-data="{}"
         x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full mx-4"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-amber-500 to-orange-600 text-white px-6 py-4 rounded-t-xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="bg-white bg-opacity-20 p-2 rounded-lg mr-3">
                            <i class="fas fa-exclamation-triangle text-white animate-pulse"></i>
                        </div>
                        <h3 class="text-lg font-semibold">{{ __('messages.document_exists_warning', ['type' => ucfirst(str_replace('_', ' ', $newDocumentType))]) }}</h3>
                    </div>
                </div>
            </div>

            <!-- Modal Content -->
            <div class="p-6">
                <div class="mb-6">
                    <div class="flex items-center mb-4">
                        <div class="bg-amber-100 p-3 rounded-full mr-4">
                            <i class="fas fa-file-alt text-amber-600 text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900">{{ __('messages.existing_document') }}</h4>
                            <p class="text-sm text-gray-600">{{ $existingDocument->title ?? '' }}</p>
                            @if($existingDocument && $existingDocument->created_at)
                                <p class="text-xs text-gray-500">{{ __('messages.uploaded_on') }}: {{ \Carbon\Carbon::parse($existingDocument->created_at)->format('d/m/Y H:i') }}</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-orange-50 to-amber-50 border border-orange-200 rounded-lg p-4">
                        <p class="text-sm text-gray-700 mb-2">
                            <i class="fas fa-info-circle text-orange-500 mr-2"></i>
                            {{ __('messages.replace_document_confirm') }}
                        </p>
                        <ul class="text-xs text-gray-600 space-y-1 ml-6">
                            <li>• {{ __('messages.old_document_will_be_deleted') }}</li>
                            <li>• {{ __('messages.new_document_will_be_saved') }}</li>
                            <li>• {{ __('messages.action_cannot_be_undone') }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="border-t border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 rounded-b-xl">
                <div class="flex justify-end space-x-3">
                    <button type="button" wire:click="cancelDocumentReplacement"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                        <i class="fas fa-times mr-2"></i>
                        {{ __('messages.no_cancel') }}
                    </button>
                    <button type="button" wire:click="confirmDocumentReplacement"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-orange-500 to-amber-600 hover:from-orange-600 hover:to-amber-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200">
                        <i class="fas fa-check mr-2"></i>
                        {{ __('messages.yes_replace') }}
                    </button>
                </div>
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