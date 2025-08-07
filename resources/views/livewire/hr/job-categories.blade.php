{{-- Modern Full Width Job Categories Management Interface --}}
<div class="min-h-screen bg-gray-50">
    <div class="w-full h-full">
        <div class="flex flex-col min-h-screen">
            
            {{-- Header Section with Gradient - Full Width --}}
            <div class="bg-gradient-to-r from-purple-600 via-purple-700 to-indigo-800 px-6 py-8 text-white rounded-t-2xl">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-white bg-opacity-20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                            <i class="fas fa-briefcase text-2xl text-white"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold">{{ __('livewire/hr/job-categories.job_categories_management') }}</h1>
                            <p class="text-purple-100 mt-1">{{ __('livewire/hr/job-categories.manage_categories_description') }}</p>
                        </div>
                    </div>
                    <button wire:click="create" 
                            class="bg-white bg-opacity-20 backdrop-blur-sm hover:bg-opacity-30 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 border border-white border-opacity-20">
                        <i class="fas fa-plus mr-2"></i>
                        {{ __('livewire/hr/job-categories.add_category') }}
                    </button>
                </div>
            </div>

            {{-- Stats Cards Section --}}
            <div class="bg-white border-b border-gray-200 px-6 py-6 flex-shrink-0">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-gradient-to-br from-purple-50 to-indigo-100 p-6 rounded-xl border border-purple-200 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-purple-600 mb-1">{{ __('livewire/hr/job-categories.total_categories') }}</p>
                                <p class="text-2xl font-bold text-purple-800">{{ $categories->total() }}</p>
                            </div>
                            <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                                <i class="fas fa-tags text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-green-50 to-emerald-100 p-6 rounded-xl border border-green-200 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-green-600 mb-1">{{ __('livewire/hr/job-categories.active_categories') }}</p>
                                <p class="text-2xl font-bold text-green-800">{{ $categories->where('is_active', true)->count() }}</p>
                            </div>
                            <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                                <i class="fas fa-check-circle text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-yellow-50 to-orange-100 p-6 rounded-xl border border-yellow-200 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-yellow-600 mb-1">{{ __('livewire/hr/job-categories.inactive_categories') }}</p>
                                <p class="text-2xl font-bold text-yellow-800">{{ $categories->where('is_active', false)->count() }}</p>
                            </div>
                            <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center">
                                <i class="fas fa-pause-circle text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-blue-50 to-cyan-100 p-6 rounded-xl border border-blue-200 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-blue-600 mb-1">{{ __('livewire/hr/job-categories.recent_updates') }}</p>
                                <p class="text-2xl font-bold text-blue-800">{{ $categories->where('updated_at', '>=', now()->subWeek())->count() }}</p>
                            </div>
                            <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                                <i class="fas fa-clock text-white text-xl"></i>
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
                            <i class="fas fa-filter text-purple-500 mr-2"></i>
                            {{ __('livewire/hr/job-categories.filters_and_search') }}
                        </h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="md:col-span-2">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-search text-gray-400 mr-1"></i>
                                {{ __('livewire/hr/job-categories.search_categories') }}
                            </label>
                            <div class="relative">
                                <input type="text" 
                                       id="search"
                                       wire:model.live.debounce.300ms="search" 
                                       placeholder="{{ __('livewire/hr/job-categories.search_placeholder') }}" 
                                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 pl-10">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-eye text-gray-400 mr-1"></i>
                                {{ __('livewire/hr/job-categories.per_page') }}
                            </label>
                            <select wire:model.live="perPage" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                        
                        <div class="flex items-end">
                            <button wire:click="resetFilters" 
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 text-white font-medium rounded-lg hover:from-gray-700 hover:to-gray-800 transition-all duration-300">
                                <i class="fas fa-undo mr-2"></i>
                                {{ __('livewire/hr/job-categories.reset_filters') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main Content Area - Categories Cards --}}
            <div class="flex-1 bg-white px-6 py-6">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-6 border border-gray-200 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-700 flex items-center">
                            <i class="fas fa-list text-purple-500 mr-2"></i>
                            {{ __('livewire/hr/job-categories.categories_list') }}
                        </h3>
                        <div class="flex items-center space-x-4">
                            <button wire:click="sortBy('name')" 
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-sort-alpha-{{ $sortDirection === 'asc' ? 'down' : 'up' }} mr-1.5"></i>
                                {{ __('livewire/hr/job-categories.sort_by_name') }}
                            </button>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse($categories as $category)
                            <div class="bg-white border border-gray-200 rounded-xl p-6 hover:shadow-lg transition-all duration-300 hover:border-purple-300 group">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-12 h-12 rounded-lg {{ $category->is_active ? 'bg-gradient-to-br from-purple-100 to-indigo-100 border-purple-200' : 'bg-gradient-to-br from-gray-100 to-gray-200 border-gray-200' }} border flex items-center justify-center">
                                            <i class="fas fa-tag {{ $category->is_active ? 'text-purple-600' : 'text-gray-500' }} text-lg"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h4 class="text-lg font-semibold text-gray-900 truncate group-hover:text-purple-700 transition-colors duration-200">
                                                {{ $category->name }}
                                            </h4>
                                            <div class="flex items-center mt-1">
                                                @if($category->is_active)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                                        <div class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></div>
                                                        {{ __('livewire/hr/job-categories.active') }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                                        <div class="w-1.5 h-1.5 bg-gray-500 rounded-full mr-1.5"></div>
                                                        {{ __('livewire/hr/job-categories.inactive') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    @if($category->description)
                                        <p class="text-sm text-gray-600 leading-relaxed line-clamp-3">
                                            {{ $category->description }}
                                        </p>
                                    @else
                                        <p class="text-sm text-gray-400 italic">
                                            {{ __('livewire/hr/job-categories.no_description') }}
                                        </p>
                                    @endif
                                </div>
                                
                                <div class="border-t border-gray-100 pt-4">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs text-gray-500">
                                            <i class="fas fa-clock mr-1"></i>
                                            {{ __('livewire/hr/job-categories.created') }}: {{ $category->created_at->format('d/m/Y') }}
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <div class="relative group/tooltip">
                                                <button wire:click="edit({{ $category->id }})" 
                                                        class="flex items-center justify-center w-8 h-8 text-purple-600 bg-purple-50 hover:bg-purple-100 rounded-full transition-all duration-200 hover:scale-110">
                                                    <i class="fas fa-edit text-sm"></i>
                                                </button>
                                                <div class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                                                    {{ __('livewire/hr/job-categories.edit') }}
                                                </div>
                                            </div>
                                            
                                            <div class="relative group/tooltip">
                                                <button wire:click="confirmDelete({{ $category->id }})" 
                                                        class="flex items-center justify-center w-8 h-8 text-red-600 bg-red-50 hover:bg-red-100 rounded-full transition-all duration-200 hover:scale-110">
                                                    <i class="fas fa-trash text-sm"></i>
                                                </button>
                                                <div class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                                                    {{ __('livewire/hr/job-categories.delete') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-16">
                                <div class="bg-gradient-to-br from-gray-100 to-gray-200 w-24 h-24 mx-auto mb-6 rounded-full flex items-center justify-center">
                                    <i class="fas fa-tags text-4xl text-gray-400"></i>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 mb-3">
                                    {{ __('livewire/hr/job-categories.no_categories_found') }}
                                </h3>
                                <div class="max-w-md mx-auto">
                                    @if($search)
                                        <p class="text-gray-600 mb-4">
                                            {{ __('livewire/hr/job-categories.no_results_for_search') }}
                                        </p>
                                        <button wire:click="resetFilters" 
                                                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-semibold rounded-lg hover:from-purple-700 hover:to-purple-800 transition-all duration-300">
                                            <i class="fas fa-filter mr-2"></i>
                                            {{ __('livewire/hr/job-categories.clear_search') }}
                                        </button>
                                    @else
                                        <p class="text-gray-600 mb-4">
                                            {{ __('livewire/hr/job-categories.get_started_description') }}
                                        </p>
                                        <button wire:click="create" 
                                                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-semibold rounded-lg hover:from-purple-700 hover:to-purple-800 transition-all duration-300">
                                            <i class="fas fa-plus mr-2"></i>
                                            {{ __('livewire/hr/job-categories.create_first_category') }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforelse
                    </div>
                    
                    {{-- Pagination --}}
                    @if($categories->hasPages())
                        <div class="mt-6 bg-white rounded-xl border border-gray-200 px-6 py-4">
                            {{ $categories->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Category Modal -->
    @if($showModal)
    <div class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg flex flex-col max-h-[95vh]">
            
            <!-- Header with Gradient -->
            <div class="bg-gradient-to-r from-purple-600 via-purple-700 to-indigo-800 px-6 py-4 text-white rounded-t-2xl">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white bg-opacity-20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                            <i class="fas {{ $isEditing ? 'fa-edit' : 'fa-plus-circle' }} text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold">
                                {{ $isEditing ? __('livewire/hr/job-categories.edit_category') : __('livewire/hr/job-categories.add_new_category') }}
                            </h3>
                            <p class="text-purple-100 text-sm">{{ __('livewire/hr/job-categories.category_form_subtitle') }}</p>
                        </div>
                    </div>
                    <button wire:click="closeModal" 
                            class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-white hover:bg-opacity-20 transition-all duration-200">
                        <i class="fas fa-times text-white"></i>
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="flex-1 p-6 overflow-y-auto">
                <form wire:submit.prevent="save" class="space-y-6">
                    
                    <!-- Category Name Field -->
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-4 border border-gray-200">
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-tag text-purple-500 mr-2"></i>
                            {{ __('livewire/hr/job-categories.category_name') }}
                        </label>
                        <input type="text" 
                               id="name" 
                               wire:model="name" 
                               placeholder="{{ __('livewire/hr/job-categories.category_name_placeholder') }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 @error('name') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                        @error('name') 
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p> 
                        @enderror
                    </div>

                    <!-- Description Field -->
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-4 border border-gray-200">
                        <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-align-left text-purple-500 mr-2"></i>
                            {{ __('livewire/hr/job-categories.category_description') }}
                        </label>
                        <textarea id="description" 
                                  wire:model="description" 
                                  rows="4"
                                  placeholder="{{ __('livewire/hr/job-categories.category_description_placeholder') }}"
                                  class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 @error('description') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror resize-none"></textarea>
                        @error('description') 
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p> 
                        @enderror
                    </div>

                    <!-- Status Toggle -->
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-4 border border-gray-200">
                        <div class="flex items-start space-x-4">
                            <div class="flex items-center">
                                <input id="is_active" 
                                       wire:model="is_active" 
                                       type="checkbox" 
                                       class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                            </div>
                            <div class="flex-1">
                                <label for="is_active" class="block text-sm font-semibold text-gray-700">
                                    <i class="fas fa-toggle-on text-purple-500 mr-2"></i>
                                    {{ __('livewire/hr/job-categories.is_active') }}
                                </label>
                                <p class="text-sm text-gray-600 mt-1">{{ __('livewire/hr/job-categories.status_description') }}</p>
                            </div>
                        </div>
                    </div>
                    
                </form>
            </div>

            <!-- Footer with Actions -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 rounded-b-2xl flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    {{ __('livewire/hr/job-categories.form_help_text') }}
                </div>
                <div class="flex space-x-3">
                    <button type="button"
                            wire:click="closeModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all duration-200">
                        <i class="fas fa-times mr-2"></i>
                        {{ __('livewire/hr/job-categories.cancel') }}
                    </button>
                    <button type="submit"
                            wire:click="save"
                            class="px-6 py-2 text-sm font-medium text-white bg-gradient-to-r from-purple-600 to-purple-700 rounded-lg hover:from-purple-700 hover:to-purple-800 focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all duration-200">
                        <i class="fas {{ $isEditing ? 'fa-save' : 'fa-plus' }} mr-2"></i>
                        {{ $isEditing ? __('livewire/hr/job-categories.update') : __('livewire/hr/job-categories.create') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Modern Delete Confirmation Modal -->
    @if($showDeleteModal)
    <div class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
            
            <!-- Header with Gradient -->
            <div class="bg-gradient-to-r from-red-500 via-red-600 to-red-700 px-6 py-4 text-white rounded-t-2xl">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white bg-opacity-20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">{{ __('livewire/hr/job-categories.delete_category') }}</h3>
                        <p class="text-red-100 text-sm">{{ __('livewire/hr/job-categories.delete_warning') }}</p>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6">
                <div class="bg-gradient-to-r from-red-50 to-red-100 rounded-xl p-4 border border-red-200 mb-6">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-red-500 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-red-800 font-medium">{{ __('livewire/hr/job-categories.delete_confirmation_title') }}</p>
                            <p class="text-sm text-red-700 mt-1">{{ __('livewire/hr/job-categories.delete_confirmation_message') }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-yellow-800 font-medium">{{ __('livewire/hr/job-categories.impact_warning') }}</p>
                            <p class="text-sm text-yellow-700 mt-1">{{ __('livewire/hr/job-categories.impact_description') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer with Actions -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 rounded-b-2xl flex justify-end space-x-3">
                <button type="button"
                        wire:click="closeDeleteModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-200">
                    <i class="fas fa-times mr-2"></i>
                    {{ __('livewire/hr/job-categories.cancel') }}
                </button>
                <button type="button"
                        wire:click="delete"
                        class="px-6 py-2 text-sm font-medium text-white bg-gradient-to-r from-red-600 to-red-700 rounded-lg hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 transition-all duration-200">
                    <i class="fas fa-trash-alt mr-2"></i>
                    {{ __('livewire/hr/job-categories.delete') }}
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
