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
                                    <i class="fas fa-university mr-3 text-indigo-200 animate-pulse"></i>
                                    @lang('hr.banks.title')
                                </h1>
                                <p class="text-indigo-100 mt-2">@lang('hr.banks.subtitle')</p>
                            </div>
                            <div class="flex space-x-3">
                                <button wire:click="create" class="inline-flex items-center px-4 py-2 bg-white border border-transparent rounded-md font-semibold text-indigo-700 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                    <i class="fas fa-plus mr-2"></i>
                                    @lang('hr.banks.add_bank')
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Overview -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <!-- Total Banks Card -->
                        <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 border border-indigo-200 rounded-lg p-6 shadow-sm hover:shadow-md transition-all duration-200">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-indigo-500 rounded-xl flex items-center justify-center shadow-lg">
                                        <i class="fas fa-university text-white text-lg"></i>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="text-sm font-medium text-indigo-700 uppercase tracking-wide">@lang('hr.banks.total_banks')</div>
                                    <div class="text-2xl font-bold text-indigo-900">{{ $totalBanks }}</div>
                                    <div class="text-xs text-indigo-600 mt-1">{{ __('messages.banks_in_system') }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Active Banks Card -->
                        <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 border border-emerald-200 rounded-lg p-6 shadow-sm hover:shadow-md transition-all duration-200">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-emerald-500 rounded-xl flex items-center justify-center shadow-lg">
                                        <i class="fas fa-check-circle text-white text-lg"></i>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="text-sm font-medium text-emerald-700 uppercase tracking-wide">@lang('hr.banks.active_banks')</div>
                                    <div class="text-2xl font-bold text-emerald-900">{{ $activeBanks }}</div>
                                    <div class="text-xs text-emerald-600 mt-1">{{ __('messages.available_for_payroll') }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Inactive Banks Card -->
                        <div class="bg-gradient-to-br from-red-50 to-red-100 border border-red-200 rounded-lg p-6 shadow-sm hover:shadow-md transition-all duration-200">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-red-500 rounded-xl flex items-center justify-center shadow-lg">
                                        <i class="fas fa-times-circle text-white text-lg"></i>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="text-sm font-medium text-red-700 uppercase tracking-wide">@lang('hr.banks.inactive_banks')</div>
                                    <div class="text-2xl font-bold text-red-900">{{ $inactiveBanks }}</div>
                                    <div class="text-xs text-red-600 mt-1">{{ __('messages.not_available') }}</div>
                                </div>
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
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200"
                                        placeholder="@lang('hr.banks.search_placeholder')"
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
                                            {{ __('common.status') }}
                                        </label>
                                        <div class="relative">
                                            <select wire:model.live="statusFilter" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 bg-white pr-8 transition-all duration-200">
                                                <option value="all">{{ __('common.all') }}</option>
                                                <option value="active">{{ __('common.active') }}</option>
                                                <option value="inactive">{{ __('common.inactive') }}</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                            </div>
                                        </div>
                                        @if($statusFilter !== 'all')
                                            <div class="flex items-center text-xs text-indigo-600">
                                                <i class="fas fa-filter mr-1"></i>
                                                {{ __('messages.filtered') }}
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Per Page Filter -->
                                    <div class="space-y-2">
                                        <label class="flex items-center text-sm font-medium text-gray-700">
                                            <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center mr-2">
                                                <i class="fas fa-list text-emerald-600 text-xs"></i>
                                            </div>
                                            {{ __('common.per_page') }}
                                        </label>
                                        <div class="relative">
                                            <select wire:model.live="perPage" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-500 focus:ring-opacity-50 bg-white pr-8 transition-all duration-200">
                                                <option value="10">10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Clear Filters -->
                                    <div class="flex items-end">
                                        <button type="button" 
                                                wire:click="$set('search', '')"
                                                class="w-full px-4 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 flex items-center justify-center">
                                            <i class="fas fa-times mr-2"></i>
                                            {{ __('common.clear_filters') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Banks Data Table -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <!-- Table Header -->
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fas fa-table text-gray-600 mr-2"></i>
                                    <h3 class="text-lg font-medium text-gray-700">{{ __('hr.banks.bank_list') }}</h3>
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $banks->firstItem() ?? 0 }} - {{ $banks->lastItem() ?? 0 }} {{ __('common.of') }} {{ $banks->total() }}
                                </div>
                            </div>
                        </div>

                        <!-- Modern Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gradient-to-r from-indigo-50 to-indigo-100">
                                    <tr>
                                        <!-- Bank Name Column -->
                                        <th wire:click="sortBy('name')" 
                                            class="group px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider cursor-pointer hover:bg-indigo-200 transition-all duration-200">
                                            <div class="flex items-center space-x-2">
                                                <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center group-hover:bg-indigo-200">
                                                    <i class="fas fa-university text-indigo-600 text-xs"></i>
                                                </div>
                                                <span>@lang('hr.banks.name')</span>
                                                @if($sortField === 'name')
                                                    @if($sortDirection === 'asc')
                                                        <i class="fas fa-sort-alpha-up text-indigo-600"></i>
                                                    @else
                                                        <i class="fas fa-sort-alpha-down text-indigo-600"></i>
                                                    @endif
                                                @else
                                                    <i class="fas fa-sort text-gray-400 group-hover:text-indigo-600"></i>
                                                @endif
                                            </div>
                                        </th>
                                        
                                        <!-- Short Name Column -->
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                            <div class="flex items-center space-x-2">
                                                <div class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center">
                                                    <i class="fas fa-tag text-emerald-600 text-xs"></i>
                                                </div>
                                                <span>@lang('hr.banks.short_name')</span>
                                            </div>
                                        </th>
                                        
                                        <!-- Code Column -->
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                            <div class="flex items-center space-x-2">
                                                <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <i class="fas fa-code text-blue-600 text-xs"></i>
                                                </div>
                                                <span>@lang('hr.banks.code')</span>
                                            </div>
                                        </th>
                                        
                                        <!-- Country Column -->
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                            <div class="flex items-center space-x-2">
                                                <div class="w-6 h-6 rounded-full bg-purple-100 flex items-center justify-center">
                                                    <i class="fas fa-globe text-purple-600 text-xs"></i>
                                                </div>
                                                <span>@lang('hr.banks.country')</span>
                                            </div>
                                        </th>
                                        
                                        <!-- Status Column -->
                                        <th wire:click="sortBy('is_active')" 
                                            class="group px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider cursor-pointer hover:bg-indigo-200 transition-all duration-200">
                                            <div class="flex items-center space-x-2">
                                                <div class="w-6 h-6 rounded-full bg-yellow-100 flex items-center justify-center group-hover:bg-yellow-200">
                                                    <i class="fas fa-toggle-on text-yellow-600 text-xs"></i>
                                                </div>
                                                <span>@lang('common.status')</span>
                                                @if($sortField === 'is_active')
                                                    @if($sortDirection === 'asc')
                                                        <i class="fas fa-sort-up text-indigo-600"></i>
                                                    @else
                                                        <i class="fas fa-sort-down text-indigo-600"></i>
                                                    @endif
                                                @else
                                                    <i class="fas fa-sort text-gray-400 group-hover:text-indigo-600"></i>
                                                @endif
                                            </div>
                                        </th>
                                        
                                        <!-- Actions Column -->
                                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                            <div class="flex items-center justify-center space-x-2">
                                                <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center">
                                                    <i class="fas fa-cog text-gray-600 text-xs"></i>
                                                </div>
                                                <span>@lang('common.actions')</span>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($banks as $bank)
                                        <tr class="hover:bg-gradient-to-r hover:from-indigo-50 hover:to-white transition-all duration-200">
                                            <!-- Bank Name -->
                                            <td class="px-6 py-4">
                                                <div class="flex items-center space-x-4">
                                                    @if($bank->logo)
                                                        <div class="flex-shrink-0">
                                                            <img class="h-12 w-12 rounded-xl object-cover border-2 border-indigo-100 shadow-sm" 
                                                                 src="{{ Storage::url($bank->logo) }}" 
                                                                 alt="{{ $bank->name }}">
                                                        </div>
                                                    @else
                                                        <div class="flex-shrink-0">
                                                            <div class="h-12 w-12 bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-xl flex items-center justify-center shadow-md">
                                                                <i class="fas fa-university text-white text-lg"></i>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    <div class="min-w-0 flex-1">
                                                        <div class="text-sm font-semibold text-gray-900 truncate">
                                                            {{ $bank->name }}
                                                        </div>
                                                        @if($bank->website)
                                                            <div class="text-xs text-gray-500 mt-1 flex items-center">
                                                                <i class="fas fa-external-link-alt mr-1"></i>
                                                                <a href="{{ $bank->website }}" target="_blank" class="hover:text-indigo-600 transition-colors duration-200">
                                                                    {{ $bank->website }}
                                                                </a>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <!-- Short Name -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    @if($bank->short_name)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-medium bg-emerald-100 text-emerald-800">
                                                            {{ $bank->short_name }}
                                                        </span>
                                                    @else
                                                        <span class="text-gray-400">—</span>
                                                    @endif
                                                </div>
                                            </td>
                                            
                                            <!-- Code -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    @if($bank->code)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-mono bg-blue-100 text-blue-800 border border-blue-200">
                                                            {{ $bank->code }}
                                                        </span>
                                                    @else
                                                        <span class="text-gray-400">—</span>
                                                    @endif
                                                </div>
                                            </td>
                                            
                                            <!-- Country -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900 flex items-center">
                                                    <i class="fas fa-globe text-purple-500 mr-2"></i>
                                                    {{ $bank->country }}
                                                </div>
                                            </td>
                                            
                                            <!-- Status -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <button wire:click="toggleStatus({{ $bank->id }})"
                                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2
                                                               {{ $bank->is_active 
                                                                  ? 'bg-gradient-to-r from-emerald-100 to-emerald-200 text-emerald-800 hover:from-emerald-200 hover:to-emerald-300 focus:ring-emerald-500' 
                                                                  : 'bg-gradient-to-r from-red-100 to-red-200 text-red-800 hover:from-red-200 hover:to-red-300 focus:ring-red-500' }}">
                                                    <i class="fas {{ $bank->is_active ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                                                    {{ $bank->is_active ? __('common.active') : __('common.inactive') }}
                                                </button>
                                            </td>
                                            
                                            <!-- Actions -->
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <div class="flex items-center justify-center space-x-3">
                                                    <button wire:click="edit({{ $bank->id }})"
                                                            class="inline-flex items-center justify-center w-8 h-8 bg-indigo-100 text-indigo-600 rounded-lg hover:bg-indigo-200 transition-all duration-200 transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                                        <i class="fas fa-edit text-sm"></i>
                                                    </button>
                                                    <button wire:click="confirmDelete({{ $bank->id }})"
                                                            class="inline-flex items-center justify-center w-8 h-8 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-all duration-200 transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                                        <i class="fas fa-trash-alt text-sm"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-12 text-center">
                                                <div class="flex flex-col items-center justify-center text-gray-400">
                                                    <i class="fas fa-university text-4xl mb-4 opacity-50"></i>
                                                    <h3 class="text-lg font-medium text-gray-900 mb-1">@lang('hr.banks.no_banks_found')</h3>
                                                    <p class="text-sm text-gray-500">{{ __('hr.banks.no_banks_description') }}</p>
                                                    <button wire:click="create" 
                                                            class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                                                        <i class="fas fa-plus mr-2"></i>
                                                        @lang('hr.banks.add_first_bank')
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($banks->hasPages())
                            <div class="bg-white border-t border-gray-200 px-6 py-3">
                                {{ $banks->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    @if($showModal)
        <div x-data="{ open: @entangle('showModal') }" 
             x-show="open" 
             x-cloak 
             class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50" 
             role="dialog" 
             aria-modal="true"
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0">
            <div class="relative top-20 mx-auto p-1 w-full max-w-4xl">
                <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" 
                     x-transition:enter="transition ease-out duration-300" 
                     x-transition:enter-start="transform opacity-0 scale-95" 
                     x-transition:enter-end="transform opacity-100 scale-100" 
                     x-transition:leave="transition ease-in duration-200" 
                     x-transition:leave-start="transform opacity-100 scale-100" 
                     x-transition:leave-end="transform opacity-0 scale-95">
                    
                    <!-- Cabeçalho com gradiente -->
                    <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <i class="fas {{ $isEditing ? 'fa-edit' : 'fa-plus-circle' }} mr-2 animate-pulse"></i>
                            {{ $isEditing ? __('hr.banks.edit_bank') : __('hr.banks.add_bank') }}
                        </h3>
                        <button type="button" wire:click="closeModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <form wire:submit.prevent="save">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <!-- Bank Information Section -->
                            <div class="mb-6 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 transition-all duration-200 ease-in-out hover:shadow-md">
                                <div class="flex items-center bg-gradient-to-r from-indigo-50 to-indigo-100 px-4 py-3 border-b border-gray-200">
                                    <i class="fas fa-university text-indigo-600 mr-2"></i>
                                    <h2 class="text-base font-medium text-gray-700">{{ __('hr.banks.bank_information') }}</h2>
                                </div>
                                
                                <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Bank Name -->
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700">{{ __('hr.banks.name') }} *</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-university text-gray-400"></i>
                                            </div>
                                            <input type="text" 
                                                   wire:model.live="name" 
                                                   id="name"
                                                   placeholder="{{ __('hr.banks.enter_bank_name') }}"
                                                   class="pl-10 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm bg-white transition-all duration-200">
                                        </div>
                                        @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Short Name -->
                                    <div>
                                        <label for="short_name" class="block text-sm font-medium text-gray-700">{{ __('hr.banks.short_name') }}</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-tag text-gray-400"></i>
                                            </div>
                                            <input type="text" 
                                                   wire:model.live="short_name" 
                                                   id="short_name"
                                                   placeholder="{{ __('hr.banks.enter_short_name') }}"
                                                   class="pl-10 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm bg-white transition-all duration-200">
                                        </div>
                                        @error('short_name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Bank Code (Auto-generated) -->
                                    <div>
                                        <label for="code" class="block text-sm font-medium text-gray-700 flex items-center">
                                            {{ __('hr.banks.code') }}
                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                {{ __('common.auto_generated') }}
                                            </span>
                                        </label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-code text-gray-400"></i>
                                            </div>
                                            <input type="text" 
                                                   wire:model="code" 
                                                   id="code"
                                                   readonly
                                                   placeholder="{{ __('hr.banks.auto_generated_code') }}"
                                                   class="pl-10 block w-full border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-500 sm:text-sm transition-all duration-200">
                                        </div>
                                        @error('code') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- SWIFT Code -->
                                    <div>
                                        <label for="swift_code" class="block text-sm font-medium text-gray-700">{{ __('hr.banks.swift_code') }}</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-globe text-gray-400"></i>
                                            </div>
                                            <input type="text" 
                                                   wire:model="swift_code" 
                                                   id="swift_code"
                                                   placeholder="{{ __('hr.banks.enter_swift_code') }}"
                                                   class="pl-10 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm bg-white transition-all duration-200">
                                        </div>
                                        @error('swift_code') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Country -->
                                    <div>
                                        <label for="country" class="block text-sm font-medium text-gray-700">{{ __('hr.banks.country') }} *</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-flag text-gray-400"></i>
                                            </div>
                                            <input type="text" 
                                                   wire:model="country" 
                                                   id="country"
                                                   class="pl-10 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm bg-white transition-all duration-200">
                                        </div>
                                        @error('country') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Website -->
                                    <div>
                                        <label for="website" class="block text-sm font-medium text-gray-700">{{ __('hr.banks.website') }}</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-external-link-alt text-gray-400"></i>
                                            </div>
                                            <input type="url" 
                                                   wire:model="website" 
                                                   id="website"
                                                   placeholder="https://www.example.com"
                                                   class="pl-10 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm bg-white transition-all duration-200">
                                        </div>
                                        @error('website') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Status Section -->
                            <div class="mb-6 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 transition-all duration-200 ease-in-out hover:shadow-md">
                                <div class="flex items-center bg-gradient-to-r from-emerald-50 to-emerald-100 px-4 py-3 border-b border-gray-200">
                                    <i class="fas fa-toggle-on text-emerald-600 mr-2"></i>
                                    <h2 class="text-base font-medium text-gray-700">{{ __('hr.banks.bank_status') }}</h2>
                                </div>
                                
                                <div class="p-4">
                                    <div class="flex items-center space-x-3">
                                        <input type="checkbox" 
                                               wire:model="is_active" 
                                               id="is_active"
                                               class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 transition-all duration-200">
                                        <label for="is_active" class="flex items-center text-sm font-medium text-gray-700">
                                            <i class="fas fa-check-circle text-emerald-500 mr-2"></i>
                                            {{ __('hr.banks.is_active') }}
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2 ml-7">{{ __('hr.banks.active_bank_description') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" 
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-6 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 text-base font-semibold text-white hover:from-indigo-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm transition-all duration-200 ease-in-out transform hover:scale-105">
                                <i class="fas {{ $isEditing ? 'fa-save' : 'fa-plus' }} mr-2"></i>
                                {{ $isEditing ? __('common.update') : __('common.create') }}
                            </button>
                            <button type="button" 
                                    wire:click="closeModal"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-6 py-3 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all duration-200 ease-in-out transform hover:scale-105">
                                <i class="fas fa-times mr-2"></i>
                                {{ __('common.cancel') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                @lang('hr.banks.delete_bank')
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    @lang('hr.banks.delete_confirmation')
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <button type="button" 
                                wire:click="delete"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            @lang('common.delete')
                        </button>
                        <button type="button" 
                                wire:click="closeModal"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">
                            @lang('common.cancel')
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
