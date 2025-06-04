<div>
    <!-- Cabeçalho da Página -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 inline-flex items-center">
                <i class="fas fa-tag text-blue-600 mr-2"></i>
                {{ __('messages.failure_categories') }}
            </h1>
            <p class="mt-1 text-sm text-gray-500">{{ __('messages.failure_categories_description') }}</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-2">
            <button type="button" wire:click="createCategory" 
                class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                <i class="fas fa-plus mr-2 animate-pulse"></i>
                {{ __('messages.add_failure_category') }}
            </button>
        </div>
    </div>

    <!-- Cartão de Filtros -->
    <div class="mb-6 bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 ease-in-out hover:shadow-lg">
        <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
            <i class="fas fa-filter text-blue-600 mr-2"></i>
            <h2 class="text-base font-medium text-gray-700">{{ __('messages.filter_categories') }}</h2>
        </div>
        
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Campo de Busca -->
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-search text-gray-500 mr-1"></i>
                        {{ __('messages.search') }}
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input wire:model.live.debounce.300ms="search" id="search" 
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out" 
                            placeholder="{{ __('messages.search_failure_categories_placeholder') }}" 
                            type="search">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">{{ __('messages.search_failure_categories_help') }}</p>
                </div>
                
                <!-- Filtro por Status -->
                <div>
                    <label for="isActive" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-toggle-on text-gray-500 mr-1"></i>
                        {{ __('messages.status') }}
                    </label>
                    <select wire:model.live="isActive" id="isActive" 
                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                        <option value="">{{ __('messages.all_statuses') }}</option>
                        <option value="active">{{ __('messages.active') }}</option>
                        <option value="inactive">{{ __('messages.inactive') }}</option>
                    </select>
                </div>
                
                <!-- Registros por página -->
                <div>
                    <label for="perPage" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-list-ol text-gray-500 mr-1"></i>
                        {{ __('messages.items_per_page') }}
                    </label>
                    <select wire:model.live="perPage" id="perPage" 
                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                
                <!-- Botão de reset -->
                <div class="flex justify-end md:col-span-3">
                    <button wire:click="$set('search', ''); $set('isActive', '');" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <i class="fas fa-redo-alt mr-2"></i>
                        {{ __('messages.reset_filters') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas (se necessário) -->
    @if (session()->has('message'))
        <div class="mb-6 flex w-full overflow-hidden bg-white rounded-lg shadow-md">
            <div class="flex items-center justify-center w-12 bg-green-500">
                <i class="fas fa-check text-white"></i>
            </div>
            
            <div class="px-4 py-2 -mx-3">
                <div class="mx-3">
                    <p class="text-sm text-gray-600">{{ session('message') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Tabela de Categorias de Falha -->
    <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden transition-all duration-300 ease-in-out hover:shadow-lg mt-6">
        <!-- Cabeçalho da Tabela -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
            <h2 class="text-lg font-medium text-white flex items-center">
                <i class="fas fa-table mr-2"></i>
                {{ __('messages.failure_categories_list') }}
            </h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center cursor-pointer" wire:click="sortBy('code')">
                                {{ __('messages.code') }}
                                @if($sortField === 'code')
                                    @if($sortDirection === 'asc')
                                        <i class="fas fa-sort-up ml-1"></i>
                                    @else
                                        <i class="fas fa-sort-down ml-1"></i>
                                    @endif
                                @else
                                    <i class="fas fa-sort ml-1 opacity-50"></i>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center cursor-pointer" wire:click="sortBy('name')">
                                {{ __('messages.name') }}
                                @if($sortField === 'name')
                                    @if($sortDirection === 'asc')
                                        <i class="fas fa-sort-up ml-1"></i>
                                    @else
                                        <i class="fas fa-sort-down ml-1"></i>
                                    @endif
                                @else
                                    <i class="fas fa-sort ml-1 opacity-50"></i>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center cursor-pointer" wire:click="sortBy('description')">
                                {{ __('messages.description') }}
                                @if($sortField === 'description')
                                    @if($sortDirection === 'asc')
                                        <i class="fas fa-sort-up ml-1"></i>
                                    @else
                                        <i class="fas fa-sort-down ml-1"></i>
                                    @endif
                                @else
                                    <i class="fas fa-sort ml-1 opacity-50"></i>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.color') }}</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.status') }}</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($categories as $category)
                        <tr class="hover:bg-gray-50 transition-colors duration-150 ease-in-out">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $category->code }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $category->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <div class="max-w-xs truncate">{{ $category->description ?: __('messages.no_description') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="flex items-center">
                                    <div class="w-6 h-6 mr-2 rounded-full border border-gray-300 shadow-sm" style="background-color: {{ $category->color }};"></div>
                                    <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">{{ $category->color }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $category->is_active ? __('messages.active') : __('messages.inactive') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <button wire:click="editCategory({{ $category->id }})" 
                                        class="text-indigo-600 hover:text-indigo-900 transition-colors duration-150 transform hover:scale-110">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button wire:click="toggleActive({{ $category->id }})" 
                                        class="{{ $category->is_active ? 'text-amber-600 hover:text-amber-900' : 'text-green-600 hover:text-green-900' }} transition-colors duration-150 transform hover:scale-110">
                                        <i class="fas {{ $category->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                    </button>
                                    <button wire:click="confirmDelete({{ $category->id }})" 
                                        class="text-red-600 hover:text-red-900 transition-colors duration-150 transform hover:scale-110">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col items-center justify-center py-6 space-y-2">
                                    <div class="flex-shrink-0 bg-gray-100 p-3 rounded-full">
                                        <i class="fas fa-tag text-gray-400 text-2xl"></i>
                                    </div>
                                    <p class="text-gray-500 text-sm">{{ __('messages.no_failure_categories_found') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Paginação -->
        <div class="px-4 py-3 bg-white border-t border-gray-200">
            {{ $categories->links() }}
        </div>
    </div>

    <!-- Modal para adicionar/editar categoria -->
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
        <div class="relative top-20 mx-auto p-1 w-full max-w-3xl">
            <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" 
                 x-transition:enter="transition ease-out duration-300" 
                 x-transition:enter-start="transform opacity-0 scale-95" 
                 x-transition:enter-end="transform opacity-100 scale-100" 
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="transform opacity-100 scale-100" 
                 x-transition:leave-end="transform opacity-0 scale-95">
                
                <!-- Cabeçalho com gradiente -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <i class="fas {{ $editMode ? 'fa-edit' : 'fa-plus-circle' }} mr-2 animate-pulse"></i>
                        {{ $editMode ? __('messages.edit_failure_category') : __('messages.add_failure_category') }}
                    </h3>
                    <button type="button" wire:click="closeModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form wire:submit="saveCategory">
                    <div class="p-6 space-y-6">
                        @if ($errors->any())
                            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-exclamation-circle text-red-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-red-700">{{ __('messages.please_fix_errors') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Cartão para informações básicas -->
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                            <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                <h3 class="text-base font-medium text-gray-700">{{ __('messages.basic_information') }}</h3>
                            </div>
                            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="code" class="block text-sm font-medium text-gray-700 flex items-center">
                                        {{ __('messages.code') }} <span class="text-red-500">*</span>
                                        <span class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-800 text-xs rounded-full">{{ __('messages.auto_generated') }}</span>
                                    </label>
                                    <div class="mt-1 flex rounded-md shadow-sm">
                                        <div class="relative flex items-stretch flex-grow focus-within:z-10">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-hashtag text-gray-400"></i>
                                            </div>
                                            <input type="text" wire:model="failureCategory.code" id="code" 
                                                readonly
                                                class="pl-10 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-gray-50 cursor-not-allowed">
                                        </div>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">{{ __('messages.auto_generated_code_info') }}</p>
                                    @error('failureCategory.code') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">{{ __('messages.name') }} <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="failureCategory.name" id="name" 
                                        placeholder="{{ __('messages.enter_category_name') }}"
                                        class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white">
                                    @error('failureCategory.name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="md:col-span-2">
                                    <label for="description" class="block text-sm font-medium text-gray-700">{{ __('messages.description') }}</label>
                                    <textarea wire:model="failureCategory.description" id="description" rows="3" 
                                        placeholder="{{ __('messages.enter_category_description') }}"
                                        class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white"></textarea>
                                    @error('failureCategory.description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Cartão para propriedades visuais -->
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                            <div class="flex items-center bg-gradient-to-r from-purple-50 to-purple-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-palette text-purple-600 mr-2"></i>
                                <h3 class="text-base font-medium text-gray-700">{{ __('messages.visual_properties') }}</h3>
                            </div>
                            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="color" class="block text-sm font-medium text-gray-700">{{ __('messages.color_code') }} <span class="text-red-500">*</span></label>
                                    <div class="mt-1 flex items-center space-x-3">
                                        <div class="relative overflow-hidden rounded border border-gray-300 shadow-sm">
                                            <input type="color" wire:model="failureCategory.color" id="color" 
                                                class="h-10 p-0 cursor-pointer w-full" 
                                                placeholder="{{ __('messages.enter_hex_color') }}">
                                            <div class="absolute inset-0 pointer-events-none opacity-0 hover:opacity-10 bg-black"></div>
                                        </div>
                                        <div class="w-10 h-10 rounded-full border-2 border-gray-300 shadow-sm" style="background-color: {{ $failureCategory['color'] ?? '#3498db' }};"></div>
                                        <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">{{ $failureCategory['color'] ?? '#3498db' }}</span>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">{{ __('messages.color_code_help') }}</p>
                                    @error('failureCategory.color') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="flex items-center bg-gray-50 p-3 rounded-lg border border-gray-200 mt-2">
                                    <div>
                                        <label for="is_active" class="flex items-center cursor-pointer">
                                            <div class="relative">
                                                <input type="checkbox" wire:model="failureCategory.is_active" id="is_active" class="sr-only">
                                                <div class="w-12 h-6 bg-gray-300 rounded-full shadow-inner transition-all duration-300 ease-in-out"></div>
                                                <div class="dot absolute w-6 h-6 bg-white rounded-full shadow left-0 top-0 transition-transform duration-300 ease-in-out transform" 
                                                    :class="{'translate-x-6 bg-green-500': $wire.failureCategory.is_active, 'bg-white': !$wire.failureCategory.is_active}"></div>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="ml-3">
                                        <label for="is_active" class="text-sm font-medium text-gray-700 cursor-pointer">
                                            {{ __('messages.is_active') }}
                                        </label>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ __('messages.active_info') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                        <button type="button" wire:click="closeModal" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                            <i class="fas fa-times mr-2"></i>
                            {{ __('messages.cancel') }}
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="saveCategory">
                                <i class="fas {{ $editMode ? 'fa-save' : 'fa-plus-circle' }} mr-2"></i>
                                {{ $editMode ? __('messages.update') : __('messages.save') }}
                            </span>
                            <span wire:loading wire:target="saveCategory" class="inline-flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="[http://www.w3.org/2000/svg"](http://www.w3.org/2000/svg") fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ __('messages.processing') }}
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para confirmar exclusão -->
    <div x-data="{ open: @entangle('confirmingDeletion') }" 
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
        <div class="relative top-20 mx-auto p-1 w-full max-w-md">
            <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out">
                <div class="bg-gradient-to-r from-red-600 to-red-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2 animate-pulse"></i>
                        {{ __('messages.delete_failure_category') }}
                    </h3>
                    <button type="button" wire:click="closeModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="p-6">
                    <div class="mb-4 text-sm text-gray-700">
                        <p class="mb-2">{{ __('messages.delete_failure_category_confirmation') }}</p>
                        <p class="font-bold text-red-600">{{ __('messages.delete_failure_category_warning') }}</p>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" wire:click="closeModal" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                            <i class="fas fa-times mr-2"></i>
                            {{ __('messages.cancel') }}
                        </button>
                        <button type="button" wire:click="deleteCategory" wire:loading.attr="disabled" class="inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="deleteCategory">
                                <i class="fas fa-trash-alt mr-2"></i>
                                {{ __('messages.delete') }}
                            </span>
                            <span wire:loading wire:target="deleteCategory" class="inline-flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="[http://www.w3.org/2000/svg"](http://www.w3.org/2000/svg") fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{