<div>
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold">{{ __('livewire/product-categories.categories_management') }}</h1>
            <button wire:click="openAddModal" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-plus mr-2"></i>{{ __('livewire/product-categories.add_category') }}
            </button>
        </div>

        <!-- Search and Filter Card -->
        <div class="mb-6 bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 ease-in-out hover:shadow-lg transform hover:scale-[1.01]">
            <!-- Card header with gradient -->
            <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                <i class="fas fa-filter text-blue-600 mr-2"></i>
                <h2 class="text-base font-medium text-gray-700">{{ __('livewire/product-categories.search_and_filters') }}</h2>
            </div>
            <!-- Card content -->
            <div class="p-4">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-search text-gray-500 mr-1"></i>
                            {{ __('livewire/layout.search') }}
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input wire:model.live.debounce.300ms="search" id="search" 
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out" 
                                placeholder="{{ __('livewire/product-categories.search_categories') }}" 
                                type="search">
                        </div>
                    </div>
                    <div class="flex-initial flex space-x-2">
                        <div>
                            <label for="perPage" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-list-ol text-gray-500 mr-1"></i>
                                {{ __('livewire/layout.per_page') }}
                            </label>
                            <select wire:model.live="perPage" id="perPage" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                        <div class="self-end">
                            <button wire:click="resetFilters" class="flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                <i class="fas fa-redo-alt mr-2"></i>
                                {{ __('livewire/layout.reset') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela de Categorias -->
        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden transition-all duration-300 ease-in-out hover:shadow-lg">
            <!-- Cabeçalho da Tabela -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3 border-b border-gray-200">
                <h2 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-tags mr-2"></i>
                    {{ __('livewire/product-categories.category_list') }}
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('name')">
                                    {{ __('livewire/product-categories.category_name') }}
                                    @if ($sortField === 'name')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('livewire/product-categories.parent_category') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('livewire/product-categories.category_description') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('livewire/product-categories.products_count') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('livewire/layout.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($categories as $category)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-4 w-4 rounded-full mr-2" style="background-color: {{ $category->color }}"></div>
                                    <div class="text-sm font-medium text-gray-900">{{ $category->name }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $category->parent ? $category->parent->name : '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ \Illuminate\Support\Str::limit($category->description, 50) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $category->products_count }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <button wire:click="edit({{ $category->id }})" 
                                        class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded bg-indigo-100 text-indigo-700 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                        <i class="fas fa-edit mr-1"></i>
                                        {{ __('livewire/layout.edit') }}
                                    </button>
                                    <button wire:click="confirmDelete({{ $category->id }})" 
                                        class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded bg-red-100 text-red-700 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                        <i class="fas fa-trash mr-1"></i>
                                        {{ __('livewire/layout.delete') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center">
                                <div class="flex flex-col items-center justify-center space-y-3 py-4">
                                    <div class="bg-gray-100 rounded-full p-3 text-gray-400">
                                        <i class="fas fa-folder-open text-2xl"></i>
                                    </div>
                                    <p class="text-gray-500 font-medium">{{ __('livewire/product-categories.no_categories_found') }}</p>
                                    <p class="text-gray-400 text-sm">{{ __('livewire/product-categories.try_different_search') }}</p>
                                    <button wire:click="openAddModal" class="mt-2 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                        <i class="fas fa-plus mr-2"></i>
                                        {{ __('livewire/product-categories.add_category') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="px-5 py-4 bg-gray-50 border-t border-gray-200">
                <div class="flex flex-col sm:flex-row justify-between items-center">
                    <div class="text-sm text-gray-500 mb-3 sm:mb-0">
                        {!! __('livewire/layout.showing_results', ['first' => $categories->firstItem() ?: 0, 'last' => $categories->lastItem() ?: 0, 'total' => $categories->total()]) !!}
                    </div>
                    <div>
                        {{ $categories->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Category Modal -->
    <div id="categoryModal" class="@if($showModal) fixed @else hidden @endif inset-0 overflow-y-auto z-50">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                <form wire:submit.prevent="save" class="overflow-hidden">
                    <div class="bg-blue-600 px-4 py-3 sm:px-6 flex justify-between items-center">
                        <h3 class="text-lg leading-6 font-medium text-white" id="modal-title">
                            <i class="fas {{ $editMode ? 'fa-edit' : 'fa-plus-circle' }} mr-2"></i>
                            {{ $editMode ? __('livewire/product-categories.edit_category') : __('livewire/product-categories.add_category') }}
                        </h3>
                        <button type="button" wire:click="closeModal" class="text-white hover:text-gray-200">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <div class="px-4 pt-5 pb-4 sm:p-6 max-h-[calc(100vh-200px)] overflow-y-auto">
                        @if ($errors->any())
                        <div class="rounded-md bg-red-50 p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-red-400 text-lg"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">{{ __('messages.validation_errors') }}</h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <ul class="list-disc pl-5 space-y-1">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Basic Information Section -->
                        <div class="bg-white rounded-lg shadow-sm mb-6 overflow-hidden border border-gray-200">
                            <div class="flex items-center bg-gray-50 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-tag text-blue-500 mr-2"></i>
                                <h2 class="text-md font-medium text-gray-700">{{ __('messages.basic_information') }}</h2>
                            </div>
                            <div class="p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="col-span-1 md:col-span-2">
                                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-signature text-gray-500 mr-1"></i>
                                            {{ __('livewire/product-categories.category_name') }}*
                                        </label>
                                        <input type="text" id="name" wire:model.defer="name" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-barcode text-gray-500 mr-1"></i>
                                            {{ __('livewire/product-categories.category_code') }}*
                                        </label>
                                        <input type="text" id="code" wire:model.defer="code" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        @error('code') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-sitemap text-gray-500 mr-1"></i>
                                            {{ __('livewire/product-categories.parent_category') }}
                                        </label>
                                        <select id="parent_id" wire:model.defer="parent_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="">{{ __('livewire/layout.none') }}</option>
                                            @foreach($parentCategories as $parentCategory)
                                                @if(!$editMode || ($editMode && $parentCategory['id'] != $category_id))
                                                <option value="{{ $parentCategory['id'] }}">{{ $parentCategory['name'] }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        @error('parent_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Visual Information Section -->
                        <div class="bg-white rounded-lg shadow-sm mb-6 overflow-hidden border border-gray-200">
                            <div class="flex items-center bg-gray-50 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-palette text-purple-500 mr-2"></i>
                                <h2 class="text-md font-medium text-gray-700">{{ __('livewire/product-categories.visual_settings') }}</h2>
                            </div>
                            <div class="p-4">
                                <div>
                                    <label for="color" class="block text-sm font-medium text-gray-700 mb-1">
                                        <i class="fas fa-fill-drip text-gray-500 mr-1"></i>
                                        {{ __('livewire/product-categories.category_color') }}*
                                    </label>
                                    <div class="mt-1 flex items-center">
                                        <input type="color" id="color" wire:model.defer="color" class="h-10 w-10 border-gray-300 rounded cursor-pointer">
                                        <span class="ml-3 px-3 py-1 rounded" style="background-color: {{ $color }}; color: {{ $this->getContrastColor($color) }};">{{ $color }}</span>
                                    </div>
                                    @error('color') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Description Section -->
                        <div class="bg-white rounded-lg shadow-sm mb-6 overflow-hidden border border-gray-200">
                            <div class="flex items-center bg-gray-50 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-align-left text-green-500 mr-2"></i>
                                <h2 class="text-md font-medium text-gray-700">{{ __('messages.additional_information') }}</h2>
                            </div>
                            <div class="p-4">
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                        <i class="fas fa-info-circle text-gray-500 mr-1"></i>
                                        {{ __('livewire/product-categories.category_description') }}
                                    </label>
                                    <textarea id="description" wire:model.defer="description" rows="3" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                                    @error('description') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-150">
                            <i class="fas {{ $editMode ? 'fa-save' : 'fa-plus-circle' }} mr-2"></i>
                            {{ $editMode ? __('messages.update') : __('messages.save') }}
                        </button>
                        <button type="button" wire:click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-150">
                            <i class="fas fa-times mr-2"></i>
                            {{ __('messages.cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmModal" class="@if($showConfirmDelete) fixed @else hidden @endif inset-0 overflow-y-auto z-50">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-red-600 px-4 py-3 sm:px-6 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-medium text-white" id="modal-title">
                        <i class="fas fa-trash-alt mr-2"></i>
                        {{ __('livewire/product-categories.delete_category') }}
                    </h3>
                    <button type="button" wire:click="cancelDelete" class="text-white hover:text-gray-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mx-0">
                            <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">
                                {{ __('livewire/product-categories.confirm_delete_category') }}
                            </p>
                            
                            @if(isset($deleteCategoryName) && $deleteCategoryName)
                            <div class="mt-3 p-3 bg-gray-50 rounded-md border border-gray-200">
                                <h4 class="text-sm font-medium text-gray-700 mb-1">{{ __('livewire/product-categories.category_name') }}:</h4>
                                <p class="text-sm font-semibold text-gray-900">{{ $deleteCategoryName }}</p>
                            </div>
                            @endif
                            
                            @if($deleteHasProducts)
                            <div class="mt-3 p-3 bg-red-50 rounded-md border border-red-200">
                                <p class="text-sm text-red-600 flex items-start">
                                    <i class="fas fa-exclamation-circle mr-2 mt-0.5 text-red-500"></i>
                                    <span>{{ __('livewire/product-categories.cannot_delete_category_with_products') }}</span>
                                </p>
                            </div>
                            @endif
                            
                            @if($deleteHasChildren)
                            <div class="mt-3 p-3 bg-red-50 rounded-md border border-red-200">
                                <p class="text-sm text-red-600 flex items-start">
                                    <i class="fas fa-exclamation-circle mr-2 mt-0.5 text-red-500"></i>
                                    <span>{{ __('livewire/product-categories.cannot_delete_category_with_children') }}</span>
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                    <button 
                        type="button" 
                        wire:click="delete" 
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-150"
                    >
                        <i class="fas fa-trash-alt mr-2"></i>
                        {{ __('livewire/layout.delete') }}
                    </button>
                    <button 
                        type="button" 
                        wire:click="cancelDelete" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-150"
                    >
                        <i class="fas fa-times mr-2"></i>
                        {{ __('livewire/layout.cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
