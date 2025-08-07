<div>
    <div class="py-4">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-4 sm:p-6">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 rounded-lg px-6 py-8 mb-6 shadow-lg">
                        <div class="flex justify-between items-center">
                            <div>
                                <h1 class="text-2xl font-bold text-white flex items-center">
                                    <i class="fas fa-calendar-alt mr-3 text-indigo-200 animate-pulse"></i>
                                    {{ __('payroll.payroll_periods_management') }}
                                </h1>
                                <p class="text-indigo-100 mt-2">{{ __('payroll.manage_payroll_periods_description') }}</p>
                            </div>
                            <div class="flex space-x-3">
                                <button wire:click="exportToExcel" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                    <i class="fas fa-file-excel mr-2"></i>
                                    {{ __('common.export_excel') }}
                                </button>
                                <button wire:click="openPeriodModal" class="inline-flex items-center px-4 py-2 bg-white border border-transparent rounded-md font-semibold text-indigo-700 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                    <i class="fas fa-plus mr-2"></i>
                                    {{ __('payroll.new_period') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Filters and Search -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center">
                                <i class="fas fa-filter text-gray-600 mr-2"></i>
                                <h3 class="text-lg font-medium text-gray-700">{{ __('common.search_and_filters') }}</h3>
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
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200"
                                        placeholder="{{ __('payroll.search_periods_placeholder') }}"
                                    >
                                </div>
                            </div>

                            <!-- Advanced Filters -->
                            <div class="space-y-6">
                                <!-- Primary Filters Row -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <!-- Status Filter -->
                                    <div class="space-y-2">
                                        <label class="flex items-center text-sm font-medium text-gray-700">
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center mr-2">
                                                <i class="fas fa-toggle-on text-indigo-600 text-xs"></i>
                                            </div>
                                            {{ __('payroll.status') }}
                                        </label>
                                        <div class="relative">
                                            <select wire:model.live="filters.status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 bg-white pr-8 transition-all duration-200">
                                                <option value="">{{ __('payroll.all_status') }}</option>
                                                <option value="open">{{ __('payroll.open') }}</option>
                                                <option value="processing">{{ __('payroll.processing') }}</option>
                                                <option value="closed">{{ __('payroll.closed') }}</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                            </div>
                                        </div>
                                        @if($filters['status'] ?? false)
                                            <div class="flex items-center text-xs text-indigo-600">
                                                <i class="fas fa-filter mr-1"></i>
                                                {{ __('common.filtered') }}
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Date Range Filter -->
                                    <div class="space-y-2">
                                        <label class="flex items-center text-sm font-medium text-gray-700">
                                            <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center mr-2">
                                                <i class="fas fa-calendar-alt text-green-600 text-xs"></i>
                                            </div>
                                            {{ __('payroll.date_range') }}
                                        </label>
                                        <input
                                            type="text"
                                            wire:model.live.debounce.300ms="filters.date_range"
                                            placeholder="{{ __('payroll.date_range_placeholder') }}"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 transition-all duration-200"
                                        />
                                        @if($filters['date_range'] ?? false)
                                            <div class="flex items-center text-xs text-green-600">
                                                <i class="fas fa-filter mr-1"></i>
                                                {{ __('common.filtered') }}
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Reset Filters -->
                                    <div class="space-y-2">
                                        <label class="flex items-center text-sm font-medium text-gray-700">
                                            <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center mr-2">
                                                <i class="fas fa-times-circle text-red-600 text-xs"></i>
                                            </div>
                                            {{ __('common.actions') }}
                                        </label>
                                        <button 
                                            wire:click="resetFilters"
                                            class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200"
                                        >
                                            <i class="fas fa-times-circle mr-2"></i>
                                            {{ __('common.reset_filters') }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Filter Summary -->
                            <div class="mt-6 flex items-center justify-between">
                                <div class="text-sm text-gray-600">
                                    @php
                                        $activeFilters = collect($filters)->filter()->count();
                                    @endphp
                                    @if($activeFilters > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            <i class="fas fa-filter mr-1"></i>
                                            {{ $activeFilters }} {{ __('common.active_filters') }}
                                        </span>
                                    @else
                                        <span class="text-gray-500">{{ __('common.no_filters_applied') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
        
        <!-- Hidden Search for backward compatibility -->
        <div class="hidden mb-4 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input
                wire:model.live.debounce.300ms="searchQuery"
                type="text"
                placeholder="Search periods..."
                class="pl-10 focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
            />
        </div>
        
        <!-- Session Messages -->
        @if (session()->has('message'))
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-md">
                {{ session('message') }}
            </div>
        @endif
        
        @if (session()->has('error'))
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-md">
                {{ session('error') }}
            </div>
        @endif
        
                    <!-- Payroll Periods Table -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fas fa-table text-gray-600 mr-2"></i>
                                    <h3 class="text-lg font-medium text-gray-700">{{ __('payroll.periods_list') }}</h3>
                                </div>
                                <div class="text-sm text-gray-600">
                                    {{ __('common.showing_records', ['count' => $periods->count(), 'total' => $periods->total()]) }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1 cursor-pointer transition-colors duration-200 hover:text-gray-700" wire:click="sortBy('id')">
                                                <i class="fas fa-hashtag text-gray-400 mr-1"></i>
                                                <span>{{ __('payroll.id') }}</span>
                                                @if ($sortField === 'id')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-indigo-500"></i>
                                                @else
                                                    <i class="fas fa-sort text-gray-300 group-hover:text-gray-500"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1 cursor-pointer transition-colors duration-200 hover:text-gray-700" wire:click="sortBy('name')">
                                                <i class="fas fa-tag text-gray-400 mr-1"></i>
                                                <span>{{ __('payroll.name') }}</span>
                                                @if ($sortField === 'name')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-indigo-500"></i>
                                                @else
                                                    <i class="fas fa-sort text-gray-300 group-hover:text-gray-500"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1 cursor-pointer transition-colors duration-200 hover:text-gray-700" wire:click="sortBy('start_date')">
                                                <i class="fas fa-calendar-alt text-gray-400 mr-1"></i>
                                                <span>{{ __('payroll.period') }}</span>
                                                @if ($sortField === 'start_date')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-indigo-500"></i>
                                                @else
                                                    <i class="fas fa-sort text-gray-300 group-hover:text-gray-500"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1 cursor-pointer transition-colors duration-200 hover:text-gray-700" wire:click="sortBy('payment_date')">
                                                <i class="fas fa-dollar-sign text-gray-400 mr-1"></i>
                                                <span>{{ __('payroll.payment_date') }}</span>
                                                @if ($sortField === 'payment_date')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-indigo-500"></i>
                                                @else
                                                    <i class="fas fa-sort text-gray-300 group-hover:text-gray-500"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1 cursor-pointer transition-colors duration-200 hover:text-gray-700" wire:click="sortBy('status')">
                                                <i class="fas fa-toggle-on text-gray-400 mr-1"></i>
                                                <span>{{ __('payroll.status') }}</span>
                                                @if ($sortField === 'status')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-indigo-500"></i>
                                                @else
                                                    <i class="fas fa-sort text-gray-300 group-hover:text-gray-500"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-4 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center justify-center">
                                                <i class="fas fa-cogs text-gray-400 mr-1"></i>
                                                <span>{{ __('common.actions') }}</span>
                                            </div>
                                        </th>
                    </tr>
                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($periods as $period)
                                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center mr-3">
                                                        <i class="fas fa-hashtag text-indigo-600 text-xs"></i>
                                                    </div>
                                                    <span class="text-sm font-medium text-gray-900">{{ $period->id }}</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                                                        <i class="fas fa-calendar-alt text-blue-600 text-xs"></i>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">{{ $period->name }}</div>
                                                        <div class="text-xs text-gray-500">{{ __('payroll.period_name') }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center mr-3">
                                                        <i class="fas fa-clock text-green-600 text-xs"></i>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm text-gray-900">{{ $period->start_date->format('d/m/Y') }} - {{ $period->end_date->format('d/m/Y') }}</div>
                                                        <div class="text-xs text-gray-500">{{ $period->start_date->diffInDays($period->end_date) + 1 }} {{ __('payroll.days') }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center mr-3">
                                                        <i class="fas fa-dollar-sign text-yellow-600 text-xs"></i>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm text-gray-900">{{ $period->payment_date->format('d/m/Y') }}</div>
                                                        <div class="text-xs text-gray-500">{{ $period->payment_date->format('l') }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                @switch($period->status)
                                                    @case('open')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            <i class="fas fa-circle text-green-500 mr-1" style="font-size: 6px;"></i>
                                                            {{ __('payroll.open') }}
                                                        </span>
                                                        @break
                                                    @case('processing')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                            <i class="fas fa-spinner fa-spin text-yellow-500 mr-1" style="font-size: 8px;"></i>
                                                            {{ __('payroll.processing') }}
                                                        </span>
                                                        @break
                                                    @case('closed')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                            <i class="fas fa-lock text-gray-500 mr-1" style="font-size: 8px;"></i>
                                                            {{ __('payroll.closed') }}
                                                        </span>
                                                        @break
                                                    @default
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                            <i class="fas fa-question text-gray-500 mr-1" style="font-size: 8px;"></i>
                                                            {{ ucfirst($period->status) }}
                                                        </span>
                                                @endswitch
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-center text-sm font-medium">
                                                <div class="flex items-center justify-center space-x-2">
                                                    <button
                                                        wire:click="editPeriod({{ $period->id }})"
                                                        class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:scale-105"
                                                        title="{{ __('common.edit') }} {{ $period->name }}"
                                                    >
                                                        <i class="fas fa-edit mr-1"></i>
                                                        {{ __('common.edit') }}
                                                    </button>
                                                    <button
                                                        wire:click="confirmDelete({{ $period->id }})"
                                                        class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 transform hover:scale-105"
                                                        title="{{ __('common.delete') }} {{ $period->name }}"
                                                    >
                                                        <i class="fas fa-trash mr-1"></i>
                                                        {{ __('common.delete') }}
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-20">
                                                <div class="text-center">
                                                    <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-gray-100 mb-6">
                                                        <i class="fas fa-calendar-times text-gray-400 text-2xl"></i>
                                                    </div>
                                                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('payroll.no_periods_found') }}</h3>
                                                    <p class="text-gray-500 mb-6">{{ __('payroll.no_periods_description') }}</p>
                                                    <button
                                                        wire:click="openPeriodModal"
                                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                    >
                                                        <i class="fas fa-plus mr-2"></i>
                                                        {{ __('payroll.create_first_period') }}
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="px-4 py-3 bg-white border-t border-gray-200 sm:px-6">
                            @if(method_exists($periods, 'links'))
                                {{ $periods->links() }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Period Modal -->
    @if($showPeriodModal)
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            {{ $isEditing ? 'Edit Payroll Period' : 'New Payroll Period' }}
                        </h3>
                        <div class="mt-4 space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                                <input type="text" wire:model="name" id="name" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="e.g. April 2025">
                                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                                    <input type="date" wire:model="start_date" id="start_date" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('start_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                                    <input type="date" wire:model="end_date" id="end_date" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('end_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                            <div>
                                <label for="payment_date" class="block text-sm font-medium text-gray-700">Payment Date</label>
                                <input type="date" wire:model="payment_date" id="payment_date" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                @error('payment_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select wire:model="status" id="status" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="">Select Status</option>
                                    <option value="open">Open</option>
                                    <option value="processing">Processing</option>
                                    <option value="closed">Closed</option>
                                </select>
                                @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label for="remarks" class="block text-sm font-medium text-gray-700">Remarks</label>
                                <textarea wire:model="remarks" id="remarks" rows="3" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                                @error('remarks') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="savePeriod" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Save
                        </button>
                        <button wire:click="closeModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-exclamation-triangle text-red-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Delete Payroll Period
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Are you sure you want to delete this payroll period? This action cannot be undone.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="deletePeriod" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Delete
                        </button>
                        <button wire:click="closeModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
