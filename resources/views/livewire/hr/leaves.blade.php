{{-- Modern Full Width Leave Management Interface --}}
<div class="min-h-screen bg-gray-50">
    <div class="w-full h-full">
        <div class="flex flex-col min-h-screen">
            
            {{-- Header Section with Gradient - Full Width --}}
            <div class="bg-gradient-to-r from-emerald-600 via-teal-700 to-cyan-800 px-6 py-8 text-white rounded-t-2xl">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-white bg-opacity-20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-2xl text-white"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold">{{ __('livewire/hr/leaves.leave_management') }}</h1>
                            <p class="text-emerald-100 mt-1">{{ __('livewire/hr/leaves.manage_leaves_description') }}</p>
                        </div>
                    </div>
                    <button wire:click="openLeaveModal" 
                            class="bg-white bg-opacity-20 backdrop-blur-sm hover:bg-opacity-30 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 border border-white border-opacity-20">
                        <i class="fas fa-plus mr-2"></i>
                        {{ __('livewire/hr/leaves.new_leave_request') }}
                    </button>
                </div>
            </div>

            {{-- Stats Cards Section --}}
            <div class="bg-white border-b border-gray-200 px-6 py-6 flex-shrink-0">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-gradient-to-br from-emerald-50 to-teal-100 p-6 rounded-xl border border-emerald-200 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-emerald-600 mb-1">{{ __('livewire/hr/leaves.total_requests') }}</p>
                                <p class="text-2xl font-bold text-emerald-800">{{ $leaves->total() }}</p>
                            </div>
                            <div class="w-12 h-12 bg-emerald-500 rounded-lg flex items-center justify-center">
                                <i class="fas fa-calendar-check text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-100 p-6 rounded-xl border border-blue-200 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-blue-600 mb-1">{{ __('livewire/hr/leaves.pending_requests') }}</p>
                                <p class="text-2xl font-bold text-blue-800">{{ $leaves->where('status', 'pending')->count() }}</p>
                            </div>
                            <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                                <i class="fas fa-clock text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-green-50 to-emerald-100 p-6 rounded-xl border border-green-200 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-green-600 mb-1">{{ __('livewire/hr/leaves.approved_requests') }}</p>
                                <p class="text-2xl font-bold text-green-800">{{ $leaves->where('status', 'approved')->count() }}</p>
                            </div>
                            <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                                <i class="fas fa-check-circle text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-red-50 to-pink-100 p-6 rounded-xl border border-red-200 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-red-600 mb-1">{{ __('livewire/hr/leaves.rejected_requests') }}</p>
                                <p class="text-2xl font-bold text-red-800">{{ $leaves->where('status', 'rejected')->count() }}</p>
                            </div>
                            <div class="w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center">
                                <i class="fas fa-times-circle text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filters Section --}}
            <div class="bg-white border-b border-gray-200 px-6 py-6 flex-shrink-0">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-6 border border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-700 flex items-center">
                            <i class="fas fa-filter text-emerald-500 mr-2"></i>
                            {{ __('livewire/hr/leaves.filters_and_search') }}
                        </h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <!-- Search Field -->
                        <div class="md:col-span-2">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-search text-gray-400 mr-1"></i>
                                {{ __('livewire/hr/leaves.search_leaves') }}
                            </label>
                            <div class="relative">
                                <input type="text" 
                                       wire:model.live.debounce.300ms="searchQuery" 
                                       placeholder="{{ __('livewire/hr/leaves.search_placeholder') }}" 
                                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 pl-10">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Employee Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-user text-gray-400 mr-1"></i>
                                {{ __('livewire/hr/leaves.employee') }}
                            </label>
                            <select wire:model.live="filters.employee_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">{{ __('livewire/hr/leaves.all_employees') }}</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Department Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-building text-gray-400 mr-1"></i>
                                {{ __('livewire/hr/leaves.department') }}
                            </label>
                            <select wire:model.live="filters.department_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">{{ __('livewire/hr/leaves.all_departments') }}</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Leave Type Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-tags text-gray-400 mr-1"></i>
                                {{ __('livewire/hr/leaves.leave_type') }}
                            </label>
                            <select wire:model.live="filters.leave_type_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">{{ __('livewire/hr/leaves.all_leave_types') }}</option>
                                @foreach($leaveTypes as $leaveType)
                                    <option value="{{ $leaveType->id }}">{{ $leaveType->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Status Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-flag text-gray-400 mr-1"></i>
                                {{ __('livewire/hr/leaves.status') }}
                            </label>
                            <select wire:model.live="filters.status" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">{{ __('livewire/hr/leaves.all_statuses') }}</option>
                                <option value="pending">{{ __('livewire/hr/leaves.pending') }}</option>
                                <option value="approved">{{ __('livewire/hr/leaves.approved') }}</option>
                                <option value="rejected">{{ __('livewire/hr/leaves.rejected') }}</option>
                                <option value="cancelled">{{ __('livewire/hr/leaves.cancelled') }}</option>
                            </select>
                        </div>
                        
                        <!-- Per Page -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-eye text-gray-400 mr-1"></i>
                                {{ __('livewire/hr/leaves.per_page') }}
                            </label>
                            <select wire:model.live="perPage" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                        
                        <!-- Reset Button -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 text-transparent">Reset</label>
                            <button wire:click="resetFilters" 
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 text-white font-medium rounded-lg hover:from-gray-700 hover:to-gray-800 transition-all duration-300">
                                <i class="fas fa-undo mr-2"></i>
                                {{ __('livewire/hr/leaves.reset_filters') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Content Section --}}
            <div class="flex-1 bg-white px-6 py-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 flex items-center">
                        <i class="fas fa-list text-emerald-500 mr-2"></i>
                        {{ __('livewire/hr/leaves.leaves_list') }}
                    </h3>
                    <div class="flex items-center space-x-4">
                        <!-- View Toggle Button -->
                        <div class="flex items-center bg-gray-100 rounded-lg p-1">
                            <button wire:click="toggleViewMode" 
                                    class="flex items-center px-3 py-1.5 text-sm font-medium rounded-md transition-all duration-200 {{ $viewMode === 'grid' ? 'bg-white text-emerald-600 shadow-sm' : 'text-gray-600 hover:text-gray-800' }}">
                                <i class="fas fa-th mr-1.5"></i>
                                {{ __('livewire/hr/leaves.view_mode_grid') }}
                            </button>
                            <button wire:click="toggleViewMode" 
                                    class="flex items-center px-3 py-1.5 text-sm font-medium rounded-md transition-all duration-200 {{ $viewMode === 'list' ? 'bg-white text-emerald-600 shadow-sm' : 'text-gray-600 hover:text-gray-800' }}">
                                <i class="fas fa-list mr-1.5"></i>
                                {{ __('livewire/hr/leaves.view_mode_list') }}
                            </button>
                        </div>
                        
                        <!-- Sort Button -->
                        <button wire:click="sortBy('created_at')" 
                                class="text-sm text-emerald-600 hover:text-emerald-800 font-medium transition-colors duration-200">
                            <i class="fas fa-sort-alpha-{{ $sortDirection === 'asc' ? 'down' : 'up' }} mr-1.5"></i>
                            {{ __('livewire/hr/leaves.sort_by_date') }}
                        </button>
                    </div>
                </div>

                @if($leaves->count() > 0)
                    @if($viewMode === 'grid')
                        <!-- Modern Cards Layout -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach($leaves as $leave)
                            <div class="bg-white border border-gray-200 rounded-xl p-6 hover:shadow-lg transition-all duration-300 hover:border-emerald-300">
                                <!-- Header with Employee and Status -->
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-user text-emerald-600"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-900 text-sm">{{ $leave->employee->full_name }}</h4>
                                            <p class="text-xs text-gray-500">{{ $leave->employee->department->name ?? '' }}</p>
                                        </div>
                                    </div>
                                    <div>
                                        @if($leave->status === 'pending')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">
                                                <div class="w-1.5 h-1.5 bg-yellow-500 rounded-full mr-1.5"></div>
                                                {{ __('livewire/hr/leaves.pending') }}
                                            </span>
                                        @elseif($leave->status === 'approved')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                                <div class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></div>
                                                {{ __('livewire/hr/leaves.approved') }}
                                            </span>
                                        @elseif($leave->status === 'rejected')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                                <div class="w-1.5 h-1.5 bg-red-500 rounded-full mr-1.5"></div>
                                                {{ __('livewire/hr/leaves.rejected') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                                <div class="w-1.5 h-1.5 bg-gray-500 rounded-full mr-1.5"></div>
                                                {{ __('livewire/hr/leaves.cancelled') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Leave Details -->
                                <div class="space-y-3 mb-4">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-tag text-emerald-500 text-sm"></i>
                                        <span class="text-sm font-medium text-gray-700">{{ $leave->leaveType->name }}</span>
                                    </div>
                                    
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-calendar text-emerald-500 text-sm"></i>
                                        <span class="text-sm text-gray-600">
                                            {{ \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y') }} - 
                                            {{ \Carbon\Carbon::parse($leave->end_date)->format('d/m/Y') }}
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-clock text-emerald-500 text-sm"></i>
                                        <span class="text-sm text-gray-600">
                                            @php
                                                $days = \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1;
                                            @endphp
                                            {{ $days }} {{ $days === 1 ? __('livewire/hr/leaves.day') : __('livewire/hr/leaves.days') }}
                                        </span>
                                    </div>
                                    
                                    @if($leave->reason)
                                        <div class="bg-gray-50 rounded-lg p-3 mt-3">
                                            <p class="text-sm text-gray-700 line-clamp-2">{{ $leave->reason }}</p>
                                        </div>
                                    @else
                                        <div class="bg-gray-50 rounded-lg p-3 mt-3">
                                            <p class="text-sm text-gray-400 italic">{{ __('livewire/hr/leaves.no_description') }}</p>
                                        </div>
                                    @endif
                                </div>

                                <!-- Footer with Actions -->
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                    <div class="text-xs text-gray-500">
                                        <i class="fas fa-calendar-plus mr-1"></i>
                                        {{ \Carbon\Carbon::parse($leave->created_at)->format('d/m/Y') }}
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <!-- View Button -->
                                        <div class="relative group/tooltip">
                                            <button wire:click="viewLeave({{ $leave->id }})" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-blue-100 text-blue-600 hover:text-blue-700 transition-all duration-200">
                                                <i class="fas fa-eye text-sm"></i>
                                            </button>
                                            <div class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                                                {{ __('livewire/hr/leaves.view') }}
                                            </div>
                                        </div>
                                        
                                        <!-- Edit Button -->
                                        <div class="relative group/tooltip">
                                            <button wire:click="editLeave({{ $leave->id }})" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-emerald-100 text-emerald-600 hover:text-emerald-700 transition-all duration-200">
                                                <i class="fas fa-edit text-sm"></i>
                                            </button>
                                            <div class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                                                {{ __('livewire/hr/leaves.edit') }}
                                            </div>
                                        </div>
                                        
                                        @if($leave->status === 'pending')
                                            <div class="relative group/tooltip">
                                                <button wire:click="openApprovalModal({{ $leave->id }})" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-green-100 text-green-600 hover:text-green-700 transition-all duration-200">
                                                    <i class="fas fa-check text-sm"></i>
                                                </button>
                                                <div class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                                                    {{ __('livewire/hr/leaves.approve') }}
                                                </div>
                                            </div>
                                        @endif
                                        
                                        <div class="relative group/tooltip">
                                            <button wire:click="confirmDelete({{ $leave->id }})" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-red-100 text-red-600 hover:text-red-700 transition-all duration-200">
                                                <i class="fas fa-trash text-sm"></i>
                                            </button>
                                            <div class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                                                {{ __('livewire/hr/leaves.delete') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <!-- Modern Table Layout -->
                        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gradient-to-r from-emerald-50 to-emerald-100">
                                        <tr>
                                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-emerald-700 uppercase tracking-wider">
                                                <button wire:click="sortBy('employee_id')" class="flex items-center space-x-1 hover:text-emerald-900 transition-colors">
                                                    <span>{{ __('livewire/hr/leaves.employee') }}</span>
                                                    @if($sortField === 'employee_id')
                                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                                    @else
                                                        <i class="fas fa-sort text-gray-400"></i>
                                                    @endif
                                                </button>
                                            </th>
                                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-emerald-700 uppercase tracking-wider">
                                                {{ __('livewire/hr/leaves.leave_type') }}
                                            </th>
                                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-emerald-700 uppercase tracking-wider">
                                                <button wire:click="sortBy('start_date')" class="flex items-center space-x-1 hover:text-emerald-900 transition-colors">
                                                    <span>{{ __('livewire/hr/leaves.date_range') }}</span>
                                                    @if($sortField === 'start_date')
                                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                                    @else
                                                        <i class="fas fa-sort text-gray-400"></i>
                                                    @endif
                                                </button>
                                            </th>
                                            <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-emerald-700 uppercase tracking-wider">
                                                {{ __('livewire/hr/leaves.duration') }}
                                            </th>
                                            <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-emerald-700 uppercase tracking-wider">
                                                <div class="flex items-center justify-center">
                                                    <button wire:click="sortBy('status')" class="flex items-center space-x-1 hover:text-emerald-900 transition-colors">
                                                        <span>{{ __('livewire/hr/leaves.status') }}</span>
                                                        @if($sortField === 'status')
                                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                                        @else
                                                            <i class="fas fa-sort text-gray-400"></i>
                                                        @endif
                                                    </button>
                                                </div>
                                            </th>
                                            <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-emerald-700 uppercase tracking-wider">
                                                {{ __('livewire/hr/leaves.actions') }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($leaves as $leave)
                                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center mr-3">
                                                            <i class="fas fa-user text-emerald-600"></i>
                                                        </div>
                                                        <div>
                                                            <div class="text-sm font-medium text-gray-900">{{ $leave->employee->full_name }}</div>
                                                            <div class="text-sm text-gray-500">{{ $leave->employee->department->name ?? '' }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <i class="fas fa-tag text-emerald-500 mr-2"></i>
                                                        <span class="text-sm text-gray-900 font-medium">{{ $leave->leaveType->name }}</span>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center text-sm text-gray-900">
                                                        <i class="fas fa-calendar text-emerald-500 mr-2"></i>
                                                        <span>{{ \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y') }}</span>
                                                        <span class="mx-2 text-gray-400">-</span>
                                                        <span>{{ \Carbon\Carbon::parse($leave->end_date)->format('d/m/Y') }}</span>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    @php
                                                        $days = \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1;
                                                    @endphp
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                                        <i class="fas fa-clock mr-1"></i>
                                                        {{ $days }} {{ $days === 1 ? __('livewire/hr/leaves.day') : __('livewire/hr/leaves.days') }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    @if($leave->status === 'pending')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">
                                                            <div class="w-1.5 h-1.5 bg-yellow-500 rounded-full mr-1.5"></div>
                                                            {{ __('livewire/hr/leaves.pending') }}
                                                        </span>
                                                    @elseif($leave->status === 'approved')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                                            <div class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></div>
                                                            {{ __('livewire/hr/leaves.approved') }}
                                                        </span>
                                                    @elseif($leave->status === 'rejected')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                                            <div class="w-1.5 h-1.5 bg-red-500 rounded-full mr-1.5"></div>
                                                            {{ __('livewire/hr/leaves.rejected') }}
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                                            <div class="w-1.5 h-1.5 bg-gray-500 rounded-full mr-1.5"></div>
                                                            {{ __('livewire/hr/leaves.cancelled') }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <div class="flex items-center justify-center space-x-2">
                                                        <!-- View Button -->
                                                        <div class="relative group/tooltip">
                                                            <button wire:click="viewLeave({{ $leave->id }})" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-blue-100 text-blue-600 hover:text-blue-700 transition-all duration-200">
                                                                <i class="fas fa-eye text-sm"></i>
                                                            </button>
                                                            <div class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                                                                {{ __('livewire/hr/leaves.view') }}
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Edit Button -->
                                                        <div class="relative group/tooltip">
                                                            <button wire:click="editLeave({{ $leave->id }})" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-emerald-100 text-emerald-600 hover:text-emerald-700 transition-all duration-200">
                                                                <i class="fas fa-edit text-sm"></i>
                                                            </button>
                                                            <div class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                                                                {{ __('livewire/hr/leaves.edit') }}
                                                            </div>
                                                        </div>
                                                        
                                                        @if($leave->status === 'pending')
                                                            <div class="relative group/tooltip">
                                                                <button wire:click="openApprovalModal({{ $leave->id }})" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-green-100 text-green-600 hover:text-green-700 transition-all duration-200">
                                                                    <i class="fas fa-check text-sm"></i>
                                                                </button>
                                                                <div class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                                                                    {{ __('livewire/hr/leaves.approve') }}
                                                                </div>
                                                            </div>
                                                        @endif
                                                        
                                                        <div class="relative group/tooltip">
                                                            <button wire:click="confirmDelete({{ $leave->id }})" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-red-100 text-red-600 hover:text-red-700 transition-all duration-200">
                                                                <i class="fas fa-trash text-sm"></i>
                                                            </button>
                                                            <div class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                                                                {{ __('livewire/hr/leaves.delete') }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                @else
                    <!-- Empty State -->
                    <div class="text-center py-16">
                        <div class="mx-auto w-24 h-24 bg-emerald-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-calendar-alt text-4xl text-emerald-400"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">
                            {{ __('livewire/hr/leaves.no_leaves_found') }}
                        </h3>
                        <div class="max-w-md mx-auto">
                            @if($searchQuery || $filters['employee_id'] || $filters['department_id'] || $filters['leave_type_id'] || $filters['status'])
                                <p class="text-gray-600 mb-4">
                                    {{ __('livewire/hr/leaves.no_results_for_search') }}
                                </p>
                                <button wire:click="resetFilters" 
                                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white font-semibold rounded-lg hover:from-emerald-700 hover:to-emerald-800 transition-all duration-300">
                                    <i class="fas fa-undo mr-2"></i>
                                    {{ __('livewire/hr/leaves.clear_search') }}
                                </button>
                            @else
                                <p class="text-gray-600 mb-4">
                                    {{ __('livewire/hr/leaves.get_started_description') }}
                                </p>
                                <button wire:click="openLeaveModal" 
                                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white font-semibold rounded-lg hover:from-emerald-700 hover:to-emerald-800 transition-all duration-300">
                                    <i class="fas fa-plus mr-2"></i>
                                    {{ __('livewire/hr/leaves.create_first_request') }}
                                </button>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Pagination -->
                @if($leaves->hasPages())
                    <div class="mt-8">
                        {{ $leaves->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
        
        <!-- Session Messages -->
        @if (session()->has('message'))
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-md">
                {{ session('message') }}
            </div>
        @endif

    
    <!-- Leave Modal -->
    @if($showLeaveModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeModal"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <!-- Modal Header -->
                    <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center">
                                        <i class="fas fa-calendar-plus text-emerald-600 text-sm"></i>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-white" id="modal-title">
                                        {{ $isEditing ? __('livewire/hr/leaves.edit_leave_request') : __('livewire/hr/leaves.new_leave_request') }}
                                    </h3>
                                </div>
                            </div>
                            <button wire:click="closeModal" class="text-white hover:text-gray-200 transition-colors">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Modal Body -->
                    <div class="px-6 py-6">
                        <div class="grid grid-cols-1 gap-6">
                            <!-- Employee Selection -->
                            <div class="space-y-2">
                                <label for="leave_employee_id" class="flex items-center text-sm font-medium text-gray-700">
                                    <i class="fas fa-user text-emerald-500 mr-2"></i>
                                    {{ __('livewire/hr/leaves.employee') }}
                                </label>
                                <select wire:model.live="leave_employee_id" id="leave_employee_id" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors @error('leave_employee_id') border-red-500 @enderror" required>
                                    <option value="">{{ __('livewire/hr/leaves.select_employee') }}</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                                    @endforeach
                                </select>
                                @error('leave_employee_id') 
                                    <p class="mt-1 text-sm text-red-600 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            
                            <!-- Leave Type -->
                            <div class="space-y-2">
                                <label for="leave_type_id" class="flex items-center text-sm font-medium text-gray-700">
                                    <i class="fas fa-tag text-emerald-500 mr-2"></i>
                                    {{ __('livewire/hr/leaves.leave_type') }}
                                </label>
                                <select wire:model.live="leave_type_id" id="leave_type_id" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors @error('leave_type_id') border-red-500 @enderror" required>
                                    <option value="">{{ __('livewire/hr/leaves.select_leave_type') }}</option>
                                    @foreach($leaveTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                @error('leave_type_id') 
                                    <p class="mt-1 text-sm text-red-600 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            
                            <!-- Date Range -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label for="start_date" class="flex items-center text-sm font-medium text-gray-700">
                                        <i class="fas fa-calendar-alt text-emerald-500 mr-2"></i>
                                        {{ __('livewire/hr/leaves.start_date') }}
                                    </label>
                                    <input type="date" wire:model.live="start_date" id="start_date" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors @error('start_date') border-red-500 @enderror" required>
                                    @error('start_date') 
                                        <p class="mt-1 text-sm text-red-600 flex items-center">
                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                                
                                <div class="space-y-2">
                                    <label for="end_date" class="flex items-center text-sm font-medium text-gray-700">
                                        <i class="fas fa-calendar-check text-emerald-500 mr-2"></i>
                                        {{ __('livewire/hr/leaves.end_date') }}
                                    </label>
                                    <input type="date" wire:model.live="end_date" id="end_date" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors @error('end_date') border-red-500 @enderror" required>
                                    @error('end_date') 
                                        <p class="mt-1 text-sm text-red-600 flex items-center">
                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Reason -->
                            <div class="space-y-2">
                                <label for="reason" class="flex items-center text-sm font-medium text-gray-700">
                                    <i class="fas fa-comment-alt text-emerald-500 mr-2"></i>
                                    {{ __('livewire/hr/leaves.reason') }} <span class="text-red-500">*</span>
                                </label>
                                <textarea wire:model.live="reason" id="reason" rows="3" 
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors resize-none @error('reason') border-red-500 @enderror"
                                          placeholder="{{ __('livewire/hr/leaves.reason_placeholder') }}" required></textarea>
                                @error('reason') 
                                    <p class="mt-1 text-sm text-red-600 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            
                            <!-- Attachment -->
                            <div class="space-y-2">
                                <label for="temp_attachment" class="flex items-center text-sm font-medium text-gray-700">
                                    <i class="fas fa-paperclip text-emerald-500 mr-2"></i>
                                    {{ __('livewire/hr/leaves.attachment') }}
                                </label>
                                <input type="file" wire:model="temp_attachment" id="temp_attachment" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                                @error('temp_attachment') 
                                    <p class="mt-1 text-sm text-red-600 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                                
                                @if ($attachment && !$temp_attachment)
                                    <div class="mt-2 p-3 bg-gray-50 rounded-lg">
                                        <p class="text-xs text-gray-600 flex items-center">
                                            <i class="fas fa-file text-gray-400 mr-2"></i>
                                            {{ __('livewire/hr/leaves.current_file') }}: <span class="font-medium ml-1">{{ basename($attachment) }}</span>
                                        </p>
                                    </div>
                                @endif
                                
                                @if ($temp_attachment)
                                    <div class="mt-2 p-3 bg-emerald-50 rounded-lg">
                                        <p class="text-xs text-emerald-700 flex items-center">
                                            <i class="fas fa-file-upload text-emerald-500 mr-2"></i>
                                            {{ __('livewire/hr/leaves.new_file') }}: <span class="font-medium ml-1">{{ $temp_attachment->getClientOriginalName() }}</span>
                                        </p>
                                    </div>
                                @endif
                            </div>
                            
                            @if($isEditing)
                                <!-- Status -->
                                <div class="space-y-2">
                                    <label for="status" class="flex items-center text-sm font-medium text-gray-700">
                                        <i class="fas fa-info-circle text-emerald-500 mr-2"></i>
                                        {{ __('livewire/hr/leaves.status') }}
                                    </label>
                                    <select wire:model="status" id="status" 
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                                        <option value="pending">{{ __('livewire/hr/leaves.pending') }}</option>
                                        <option value="approved">{{ __('livewire/hr/leaves.approved') }}</option>
                                        <option value="rejected">{{ __('livewire/hr/leaves.rejected') }}</option>
                                        <option value="cancelled">{{ __('livewire/hr/leaves.cancelled') }}</option>
                                    </select>
                                    @error('status') 
                                        <p class="mt-1 text-sm text-red-600 flex items-center">
                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                                
                                @if($status === 'rejected')
                                    <!-- Rejection Reason -->
                                    <div class="space-y-2">
                                        <label for="rejection_reason" class="flex items-center text-sm font-medium text-gray-700">
                                            <i class="fas fa-times-circle text-red-500 mr-2"></i>
                                            {{ __('livewire/hr/leaves.rejection_reason') }}
                                        </label>
                                        <textarea wire:model="rejection_reason" id="rejection_reason" rows="2" 
                                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors resize-none"
                                                  placeholder="{{ __('livewire/hr/leaves.rejection_reason_placeholder') }}"></textarea>
                                        @error('rejection_reason') 
                                            <p class="mt-1 text-sm text-red-600 flex items-center">
                                                <i class="fas fa-exclamation-circle mr-1"></i>
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="bg-gray-50 px-6 py-4 flex flex-col sm:flex-row sm:justify-end space-y-2 sm:space-y-0 sm:space-x-3">
                        <button wire:click="closeModal" type="button" 
                                class="w-full sm:w-auto px-6 py-3 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors font-medium">
                            <i class="fas fa-times mr-2"></i>
                            {{ __('livewire/hr/leaves.cancel') }}
                        </button>
                        <button wire:click="saveLeave" type="button" 
                                onclick="console.log('Save button clicked');"
                                class="w-full sm:w-auto px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                                wire:loading.attr="disabled"
                                wire:target="saveLeave">
                            <span wire:loading.remove wire:target="saveLeave">
                                <i class="fas fa-save mr-2"></i>
                                {{ __('livewire/hr/leaves.save') }}
                            </span>
                            <span wire:loading wire:target="saveLeave" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ __('livewire/hr/leaves.saving') }}
                            </span>
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
                                    Delete Leave Request
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Are you sure you want to delete this leave request? This action cannot be undone.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="deleteLeave" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
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
    
    <!-- Approval Modal -->
    @if($showApprovalModal)
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-clipboard-check text-blue-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Update Leave Status
                                </h3>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label for="approval_status" class="block text-sm font-medium text-gray-700">Status</label>
                                        <select wire:model="status" id="approval_status" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="pending">Pending</option>
                                            <option value="approved">Approved</option>
                                            <option value="rejected">Rejected</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                    </div>
                                    
                                    @if($status === 'rejected')
                                        <div>
                                            <label for="approval_rejection_reason" class="block text-sm font-medium text-gray-700">Rejection Reason</label>
                                            <textarea wire:model="rejection_reason" id="approval_rejection_reason" rows="3" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                                            @error('rejection_reason') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="updateLeaveStatus" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Update
                        </button>
                        <button wire:click="closeModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    <!-- View Leave Modal -->
    @if($showViewModal && $viewLeaveData)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="view-modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeModal"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <!-- Modal Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center">
                                        <i class="fas fa-eye text-blue-600 text-sm"></i>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-white" id="view-modal-title">
                                        {{ __('livewire/hr/leaves.view_leave_details') }}
                                    </h3>
                                    <p class="text-blue-100 text-sm">
                                        {{ $viewLeaveData->employee->full_name }} - {{ $viewLeaveData->leaveType->name ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>
                            <button wire:click="closeModal" class="text-white hover:text-gray-200 transition-colors">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Modal Body -->
                    <div class="px-6 py-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Left Column: Basic Information -->
                            <div class="space-y-6">
                                <!-- Employee Information -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                                        <i class="fas fa-user text-blue-500 mr-2"></i>
                                        {{ __('livewire/hr/leaves.employee_info') }}
                                    </h4>
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">{{ __('livewire/hr/leaves.employee') }}:</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $viewLeaveData->employee->full_name }}</span>
                                        </div>
                                        @if($viewLeaveData->employee->department)
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-600">{{ __('livewire/hr/leaves.department') }}:</span>
                                                <span class="text-sm font-medium text-gray-900">{{ $viewLeaveData->employee->department->name ?? 'N/A' }}</span>
                                            </div>
                                        @endif
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">{{ __('livewire/hr/leaves.employee_id') }}:</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $viewLeaveData->employee->id_card ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Leave Details -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                        {{ __('livewire/hr/leaves.leave_details') }}
                                    </h4>
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">{{ __('livewire/hr/leaves.leave_type') }}:</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $viewLeaveData->leaveType->name ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">{{ __('livewire/hr/leaves.start_date') }}:</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $viewLeaveData->start_date->format('d/m/Y') }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">{{ __('livewire/hr/leaves.end_date') }}:</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $viewLeaveData->end_date->format('d/m/Y') }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">{{ __('livewire/hr/leaves.duration') }}:</span>
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ $viewLeaveData->total_days }} {{ $viewLeaveData->total_days == 1 ? __('livewire/hr/leaves.day') : __('livewire/hr/leaves.days') }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600">{{ __('livewire/hr/leaves.status') }}:</span>
                                            <span class="inline-flex items-center">
                                                @if($viewLeaveData->status === 'pending')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">
                                                        <div class="w-1.5 h-1.5 bg-yellow-500 rounded-full mr-1.5"></div>
                                                        {{ __('livewire/hr/leaves.pending') }}
                                                    </span>
                                                @elseif($viewLeaveData->status === 'approved')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                                        <div class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></div>
                                                        {{ __('livewire/hr/leaves.approved') }}
                                                    </span>
                                                @elseif($viewLeaveData->status === 'rejected')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                                        <div class="w-1.5 h-1.5 bg-red-500 rounded-full mr-1.5"></div>
                                                        {{ __('livewire/hr/leaves.rejected') }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                                        <div class="w-1.5 h-1.5 bg-gray-500 rounded-full mr-1.5"></div>
                                                        {{ __('livewire/hr/leaves.cancelled') }}
                                                    </span>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Right Column: Additional Information -->
                            <div class="space-y-6">
                                <!-- Reason -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                                        <i class="fas fa-comment-alt text-blue-500 mr-2"></i>
                                        {{ __('livewire/hr/leaves.reason') }}
                                    </h4>
                                    <div class="text-sm text-gray-700 leading-relaxed">
                                        {{ $viewLeaveData->reason ?: __('livewire/hr/leaves.no_description') }}
                                    </div>
                                </div>
                                
                                <!-- Approval Information -->
                                @if($viewLeaveData->status !== 'pending')
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                                            <i class="fas fa-user-check text-blue-500 mr-2"></i>
                                            {{ __('livewire/hr/leaves.approval_info') }}
                                        </h4>
                                        <div class="space-y-2">
                                            @if($viewLeaveData->approver)
                                                <div class="flex justify-between">
                                                    <span class="text-sm text-gray-600">{{ __('livewire/hr/leaves.approved_by') }}:</span>
                                                    <span class="text-sm font-medium text-gray-900">{{ $viewLeaveData->approver->full_name }}</span>
                                                </div>
                                            @endif
                                            @if($viewLeaveData->approved_date)
                                                <div class="flex justify-between">
                                                    <span class="text-sm text-gray-600">{{ __('livewire/hr/leaves.approved_date') }}:</span>
                                                    <span class="text-sm font-medium text-gray-900">{{ $viewLeaveData->approved_date->format('d/m/Y H:i') }}</span>
                                                </div>
                                            @endif
                                            @if($viewLeaveData->status === 'rejected' && $viewLeaveData->rejection_reason)
                                                <div class="mt-3">
                                                    <span class="text-sm text-gray-600">{{ __('livewire/hr/leaves.rejection_reason') }}:</span>
                                                    <div class="mt-1 text-sm text-red-700 bg-red-50 p-2 rounded border border-red-200">
                                                        {{ $viewLeaveData->rejection_reason }}
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Attachment -->
                                @if($viewLeaveData->attachment)
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                                            <i class="fas fa-paperclip text-blue-500 mr-2"></i>
                                            {{ __('livewire/hr/leaves.attachment') }}
                                        </h4>
                                        <div class="flex items-center justify-between p-3 bg-white rounded border border-gray-200">
                                            <div class="flex items-center">
                                                <i class="fas fa-file text-gray-400 mr-2"></i>
                                                <span class="text-sm text-gray-700">{{ basename($viewLeaveData->attachment) }}</span>
                                            </div>
                                            <a href="{{ asset('storage/' . $viewLeaveData->attachment) }}" target="_blank" 
                                               class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                                <i class="fas fa-download mr-1"></i>
                                                {{ __('livewire/hr/leaves.download') }}
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Audit Trail -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                                        <i class="fas fa-history text-blue-500 mr-2"></i>
                                        {{ __('livewire/hr/leaves.audit_trail') }}
                                    </h4>
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">{{ __('livewire/hr/leaves.created_at') }}:</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $viewLeaveData->created_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                        @if($viewLeaveData->updated_at && $viewLeaveData->updated_at != $viewLeaveData->created_at)
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-600">{{ __('livewire/hr/leaves.updated_at') }}:</span>
                                                <span class="text-sm font-medium text-gray-900">{{ $viewLeaveData->updated_at->format('d/m/Y H:i') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="bg-gray-50 px-6 py-4 flex flex-col sm:flex-row sm:justify-end space-y-2 sm:space-y-0 sm:space-x-3">
                        <button wire:click="closeModal" type="button" 
                                class="w-full sm:w-auto px-6 py-3 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors font-medium">
                            <i class="fas fa-times mr-2"></i>
                            {{ __('livewire/hr/leaves.close') }}
                        </button>
                        <button wire:click="editLeave({{ $viewLeaveData->id }})" type="button" 
                                class="w-full sm:w-auto px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors font-medium">
                            <i class="fas fa-edit mr-2"></i>
                            {{ __('livewire/hr/leaves.edit') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
