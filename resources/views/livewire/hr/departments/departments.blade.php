<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="w-full px-6 py-6">
        
        {{-- Messages --}}
        @if (session()->has('message'))
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-init="setTimeout(() => show = false, 5000)"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg shadow-md">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-green-800">
                            {{ session('message') }}
                        </p>
                    </div>
                    <div class="ml-auto pl-3">
                        <button @click="show = false" class="inline-flex text-green-600 hover:text-green-800 focus:outline-none transition-colors duration-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endif
        
        {{-- Header --}}
        <div class="bg-gradient-to-r from-purple-600 via-indigo-600 to-blue-600 rounded-2xl shadow-xl mb-6 overflow-hidden">
            <div class="px-6 py-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div class="flex items-center mb-4 md:mb-0">
                        <div class="p-3 bg-white bg-opacity-20 backdrop-blur-sm rounded-xl mr-4 shadow-lg">
                            <i class="fas fa-building text-white text-3xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-white flex items-center">
                                {{ __('hr.departments.departments_management') }}
                            </h1>
                            <p class="text-purple-100 text-sm mt-1">
                                <i class="fas fa-info-circle mr-1"></i>
                                {{ __('hr.departments.manage_departments_description') }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button 
                            wire:click="create"
                            class="inline-flex items-center px-6 py-3 bg-white text-purple-600 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                            <i class="fas fa-plus-circle mr-2"></i>
                            {{ __('hr.departments.add_department') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            {{-- Total --}}
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-200">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">{{ __('hr.departments.total_departments') }}</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $departments->total() }}</p>
                        </div>
                        <div class="p-4 bg-gradient-to-br from-purple-100 to-indigo-100 rounded-xl">
                            <i class="fas fa-building text-purple-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-purple-50 to-indigo-50 px-6 py-2">
                    <p class="text-xs text-purple-700 font-medium">
                        <i class="fas fa-chart-line mr-1"></i>
                        {{ __('hr.departments.all_departments') }}
                    </p>
                </div>
            </div>

            {{-- Active --}}
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-200">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">{{ __('hr.departments.active') }}</p>
                            <p class="text-3xl font-bold text-green-600">
                                {{ $departments->where('is_active', true)->count() }}
                            </p>
                        </div>
                        <div class="p-4 bg-gradient-to-br from-green-100 to-emerald-100 rounded-xl">
                            <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-2">
                    <p class="text-xs text-green-700 font-medium">
                        <i class="fas fa-toggle-on mr-1"></i>
                        {{ __('hr.departments.active_departments') }}
                    </p>
                </div>
            </div>

            {{-- Inactive --}}
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-200">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">{{ __('hr.departments.inactive') }}</p>
                            <p class="text-3xl font-bold text-red-600">
                                {{ $departments->where('is_active', false)->count() }}
                            </p>
                        </div>
                        <div class="p-4 bg-gradient-to-br from-red-100 to-rose-100 rounded-xl">
                            <i class="fas fa-times-circle text-red-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-red-50 to-rose-50 px-6 py-2">
                    <p class="text-xs text-red-700 font-medium">
                        <i class="fas fa-toggle-off mr-1"></i>
                        {{ __('hr.departments.inactive_departments') }}
                    </p>
                </div>
            </div>

            {{-- With Manager --}}
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-200">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">{{ __('hr.departments.with_manager') }}</p>
                            <p class="text-3xl font-bold text-blue-600">
                                {{ $departments->whereNotNull('manager_id')->count() }}
                            </p>
                        </div>
                        <div class="p-4 bg-gradient-to-br from-blue-100 to-cyan-100 rounded-xl">
                            <i class="fas fa-user-tie text-blue-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-blue-50 to-cyan-50 px-6 py-2">
                    <p class="text-xs text-blue-700 font-medium">
                        <i class="fas fa-users mr-1"></i>
                        {{ __('hr.departments.managed_departments') }}
                    </p>
                </div>
            </div>
        </div>
        
        {{-- Filters --}}
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden mb-6">
            <div class="flex items-center bg-gradient-to-r from-purple-50 to-indigo-50 px-5 py-4 border-b border-gray-200">
                <div class="p-2 bg-white rounded-lg shadow-sm mr-3">
                    <i class="fas fa-filter text-purple-600"></i>
                </div>
                <h3 class="text-base font-semibold text-gray-800">{{ __('hr.departments.filters') }}</h3>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-5">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-search text-gray-400 mr-1"></i>
                            {{ __('hr.departments.search') }}
                        </label>
                        <div class="relative">
                            <input 
                                type="text" 
                                id="search" 
                                wire:model.live.debounce.300ms="search"
                                class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200"
                                placeholder="{{ __('hr.departments.search_departments') }}">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    <div class="md:col-span-3">
                        <label for="status_filter" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-toggle-on text-gray-400 mr-1"></i>
                            {{ __('hr.departments.status') }}
                        </label>
                        <select 
                            id="status_filter" 
                            wire:model.live="status_filter"
                            class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200">
                            <option value="all">{{ __('hr.departments.all') }}</option>
                            <option value="active">{{ __('hr.departments.active') }}</option>
                            <option value="inactive">{{ __('hr.departments.inactive') }}</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label for="perPage" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-list text-gray-400 mr-1"></i>
                            {{ __('hr.departments.per_page') }}
                        </label>
                        <select 
                            id="perPage" 
                            wire:model.live="perPage"
                            class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    <div class="md:col-span-2 flex items-end">
                        <button 
                            wire:click="resetFilters"
                            class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-gray-100 to-gray-200 border border-gray-300 rounded-lg font-semibold text-sm text-gray-700 hover:from-gray-200 hover:to-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                            <i class="fas fa-redo mr-2"></i>
                            {{ __('hr.departments.reset') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Table --}}
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="flex items-center bg-gradient-to-r from-purple-50 to-indigo-50 px-5 py-4 border-b border-gray-200">
                <div class="p-2 bg-white rounded-lg shadow-sm mr-3">
                    <i class="fas fa-table text-purple-600"></i>
                </div>
                <h3 class="text-base font-semibold text-gray-800">
                    {{ __('hr.departments.departments_list') }}
                </h3>
                <span class="ml-auto px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm font-semibold">
                    {{ $departments->total() }} {{ __('hr.departments.total') }}
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" 
                                wire:click="sortBy('name')"
                                class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors duration-200">
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-building text-gray-400"></i>
                                    <span>{{ __('hr.departments.department_name') }}</span>
                                    @if($sortField === 'name')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-purple-600"></i>
                                    @else
                                        <i class="fas fa-sort text-gray-300"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-align-left text-gray-400"></i>
                                    <span>{{ __('hr.departments.description') }}</span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-user-tie text-gray-400"></i>
                                    <span>{{ __('hr.departments.responsible') }}</span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-users text-gray-400"></i>
                                    <span>{{ __('hr.departments.employees') }}</span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center justify-center space-x-1">
                                    <i class="fas fa-sitemap text-gray-400"></i>
                                    <span>{{ __('hr.departments.org_chart') }}</span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center justify-center space-x-1">
                                    <i class="fas fa-toggle-on text-gray-400"></i>
                                    <span>{{ __('hr.departments.status') }}</span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center justify-center space-x-1">
                                    <i class="fas fa-cog text-gray-400"></i>
                                    <span>{{ __('hr.departments.actions') }}</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($departments as $department)
                        <tr class="hover:bg-purple-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-purple-100 to-indigo-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-building text-purple-600"></i>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-bold text-gray-900">{{ $department->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-600 max-w-xs truncate">
                                    {{ $department->description ?? __('hr.departments.not_available') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($department->manager)
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 bg-gradient-to-br from-blue-100 to-cyan-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-user text-blue-600 text-xs"></i>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">{{ $department->manager->full_name }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400 italic">{{ __('hr.departments.not_assigned') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="text-sm font-medium text-gray-900">{{ $department->employees->count() }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($department->org_chart)
                                    <button 
                                        wire:click="viewOrgChart({{ $department->id }})"
                                        class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-lg hover:from-amber-600 hover:to-orange-600 transition-all duration-200 transform hover:scale-105 shadow-sm"
                                        title="{{ __('hr.departments.view_org_chart') }}">
                                        <i class="fas fa-sitemap mr-1.5"></i>
                                        <span class="text-xs font-medium">Ver</span>
                                    </button>
                                @else
                                    <span class="text-xs text-gray-400 italic">{{ __('hr.departments.no_org_chart') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($department->is_active)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        {{ __('hr.departments.active') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        {{ __('hr.departments.inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button 
                                        wire:click="edit({{ $department->id }})"
                                        class="inline-flex items-center px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-200 transform hover:scale-105 shadow-sm"
                                        title="{{ __('hr.departments.edit') }}">
                                        <i class="fas fa-edit text-sm"></i>
                                    </button>
                                    <button 
                                        wire:click="confirmDelete({{ $department->id }})"
                                        class="inline-flex items-center px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-all duration-200 transform hover:scale-105 shadow-sm"
                                        title="{{ __('hr.departments.delete') }}">
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-20 h-20 bg-gradient-to-br from-purple-100 to-indigo-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-building text-purple-400 text-3xl"></i>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('hr.departments.no_departments_found') }}</h3>
                                    <p class="text-gray-500 mb-4">{{ __('hr.departments.start_by_adding_department') }}</p>
                                    <button 
                                        wire:click="create"
                                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl font-semibold hover:from-purple-700 hover:to-indigo-700 transition-all duration-200 transform hover:scale-105 shadow-lg">
                                        <i class="fas fa-plus-circle mr-2"></i>
                                        {{ __('hr.departments.add_department') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($departments->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                {{ $departments->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- Modals --}}
    @include('livewire.hr.departments.modals.create-edit-modal')
    @include('livewire.hr.departments.modals.delete-modal')
    @include('livewire.hr.departments.modals.view-org-chart-modal')
</div>
