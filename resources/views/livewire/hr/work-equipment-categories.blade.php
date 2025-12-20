<div>
    <div class="py-4">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-4 sm:p-6">
                    {{-- Header --}}
                    <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 rounded-lg px-6 py-8 mb-6 shadow-lg">
                        <div class="flex justify-between items-center">
                            <div>
                                <h1 class="text-2xl font-bold text-white flex items-center">
                                    <i class="fas fa-folder mr-3 text-indigo-200 animate-pulse"></i>
                                    {{ __('messages.work_equipment_categories_management') }}
                                </h1>
                                <p class="text-indigo-100 mt-2">{{ __('messages.manage_equipment_categories_description') }}</p>
                            </div>
                            <div>
                                <button wire:click="create"
                                    class="inline-flex items-center px-4 py-2 bg-white border border-transparent rounded-md font-semibold text-indigo-600 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                    <i class="fas fa-plus mr-2"></i>
                                    {{ __('messages.add_work_equipment_category') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Filters and Search --}}
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center">
                                <i class="fas fa-filter text-gray-600 mr-2"></i>
                                <h3 class="text-lg font-medium text-gray-700">{{ __('messages.search_and_filters') }}</h3>
                            </div>
                        </div>
                        
                        <div class="p-6">
                            {{-- Search Bar --}}
                            <div class="mb-6">
                                <div class="relative max-w-md">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                    <input
                                        type="text"
                                        wire:model.live.debounce.300ms="search"
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200"
                                        placeholder="{{ __('messages.search_work_equipment_categories') }}"
                                    >
                                </div>
                            </div>

                            {{-- Filter Actions --}}
                            <div class="mt-6 flex items-center justify-between">
                                <div class="text-sm text-gray-600">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    {{ __('messages.showing') }} {{ $categories->firstItem() ?? 0 }} {{ __('messages.to') }} {{ $categories->lastItem() ?? 0 }} {{ __('messages.of') }} {{ $categories->total() }} {{ __('messages.results') }}
                                </div>
                                <button wire:click="resetFilters" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                                    <i class="fas fa-undo mr-2"></i>
                                    {{ __('messages.reset_filters') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Categories Table --}}
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fas fa-table text-gray-600 mr-2"></i>
                                    <h3 class="text-lg font-medium text-gray-700">{{ __('messages.categories_list') }}</h3>
                                </div>
                                <div class="text-sm text-gray-600">
                                    <i class="fas fa-folder text-gray-600 mr-1"></i>
                                    {{ $categories->total() }} {{ __('messages.total') }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1 cursor-pointer transition-colors duration-200 hover:text-gray-700" wire:click="sortBy('name')">
                                                <i class="fas fa-folder text-gray-400 mr-1"></i>
                                                <span>{{ __('messages.category_name') }}</span>
                                                @if($sortField === 'name')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-indigo-600"></i>
                                                @else
                                                    <i class="fas fa-sort ml-1 text-gray-400"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center">
                                                <i class="fas fa-align-left text-gray-400 mr-1"></i>
                                                <span>{{ __('messages.description') }}</span>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center">
                                                <i class="fas fa-palette text-gray-400 mr-1"></i>
                                                <span>{{ __('messages.color') }}</span>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center">
                                                <i class="fas fa-info-circle text-gray-400 mr-1"></i>
                                                <span>{{ __('messages.status') }}</span>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-4 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <i class="fas fa-cog text-gray-400 mr-1"></i>
                                            <span>{{ __('messages.actions') }}</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($categories as $category)
                                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10 rounded-lg flex items-center justify-center" style="background-color: {{ $category->color }}20;">
                                                        <i class="fas fa-folder" style="color: {{ $category->color }};"></i>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $category->name }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4">
                                                <div class="text-sm text-gray-500 truncate max-w-xs">
                                                    {{ $category->description ?? '--' }}
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium" style="background-color: {{ $category->color }}; color: {{ $this->getContrastColor($category->color) }};">
                                                    {{ $category->color }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                    {{ $category->is_active ? __('messages.active') : __('messages.inactive') }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex items-center justify-end space-x-3">
                                                    <button wire:click="edit({{ $category->id }})" 
                                                        class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200"
                                                        title="{{ __('messages.edit') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button wire:click="confirmDelete({{ $category->id }})" 
                                                        class="text-red-600 hover:text-red-900 transition-colors duration-200"
                                                        title="{{ __('messages.delete') }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-12 text-center text-gray-500">
                                                <div class="flex flex-col items-center">
                                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                                        <i class="fas fa-folder-open text-4xl text-gray-300"></i>
                                                    </div>
                                                    <p class="text-lg font-medium text-gray-900 mb-1">{{ __('messages.no_categories_found') }}</p>
                                                    <button
                                                        wire:click="create"
                                                        class="mt-3 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200"
                                                    >
                                                        <i class="fas fa-plus mr-1"></i>
                                                        {{ __('messages.add_work_equipment_category') }}
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4">
                        {{ $categories->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modals --}}
    @include('livewire.hr.work-equipment-categories.modals.category-modal')
    @include('livewire.hr.work-equipment-categories.modals.delete-modal')
</div>
