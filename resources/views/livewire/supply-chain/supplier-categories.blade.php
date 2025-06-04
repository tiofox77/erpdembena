<div>
    <div class="container mx-auto px-4 py-6">
        <!-- Header with title and add button -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
            <h1 class="text-2xl font-semibold text-gray-800 flex items-center">
                <i class="fas fa-tags text-blue-600 mr-3"></i>
                {{ __('supplier.categories_management') }}
            </h1>
            <button wire:click="openAddModal" 
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                <i class="fas fa-plus-circle mr-2"></i>
                {{ __('supplier.add_category') }}
            </button>
        </div>

        <!-- Search and filters -->
        <div class="mb-6 bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 ease-in-out hover:shadow-lg transform hover:scale-[1.01]">
            <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                <i class="fas fa-filter text-blue-600 mr-2"></i>
                <h2 class="text-base font-medium text-gray-700">{{ __('supplier.filters_and_search') }}</h2>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Search input -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-search text-gray-500 mr-1"></i>
                            {{ __('supplier.search') }}
                        </label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" wire:model.live.debounce.300ms="search" id="search" 
                                class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 pr-12 sm:text-sm border-gray-300 rounded-md" 
                                placeholder="{{ __('supplier.search_placeholder') }}">
                        </div>
                    </div>
                    
                    <!-- Status Filter -->
                    <div>
                        <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-filter text-gray-500 mr-1"></i>
                            {{ __('supplier.status') }}
                        </label>
                        <select id="statusFilter" wire:model.live="statusFilter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            <option value="">{{ __('supplier.all') }} ({{ $supplierCounts['all'] }})</option>
                            <option value="active">{{ __('supplier.active') }} ({{ $supplierCounts['active'] }})</option>
                            <option value="inactive">{{ __('supplier.inactive') }} ({{ $supplierCounts['inactive'] }})</option>
                        </select>
                    </div>
                    
                    <!-- Items Per Page -->
                    <div>
                        <label for="perPage" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-list-ol text-gray-500 mr-1"></i>
                            {{ __('Items per page') }}
                        </label>
                        <select id="perPage" wire:model.live="perPage" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end mt-4">
                    <button wire:click="resetFilters" 
                        class="inline-flex items-center px-3 py-2 bg-gray-200 hover:bg-gray-300 border border-transparent rounded-md text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <span wire:loading.remove wire:target="resetFilters">
                            <i class="fas fa-redo-alt mr-2"></i>
                            {{ __('livewire/layout.reset') }}
                        </span>
                        <span wire:loading wire:target="resetFilters">
                            <i class="fas fa-spinner fa-spin mr-1"></i> {{ __('supplier.clearing') }}...
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        @if (session()->has('message'))
            <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">
                            {{ session('message') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">
                            {{ session('error') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Categories Table -->
        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
            <!-- Table Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
                <h2 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-tags mr-2"></i>
                    {{ __('supplier.categories_list') }}
                </h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('code')">
                                    {{ __('supplier.code') }}
                                    @if($sortField === 'code')
                                        @if($sortDirection === 'asc')
                                            <i class="fas fa-sort-up ml-1"></i>
                                        @else
                                            <i class="fas fa-sort-down ml-1"></i>
                                        @endif
                                    @else
                                        <i class="fas fa-sort text-gray-400 ml-1"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('name')">
                                    {{ __('supplier.name') }}
                                    @if($sortField === 'name')
                                        @if($sortDirection === 'asc')
                                            <i class="fas fa-sort-up ml-1"></i>
                                        @else
                                            <i class="fas fa-sort-down ml-1"></i>
                                        @endif
                                    @else
                                        <i class="fas fa-sort text-gray-400 ml-1"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    {{ __('Suppliers') }}
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('supplier.status') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('supplier.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($categories as $category)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $category->code }}
                                        @if(!$category->is_active)
                                            <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                {{ __('supplier.inactive') }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $category->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $category->suppliers_count > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $category->suppliers_count }} {{ Str::plural('supplier', $category->suppliers_count) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-500 truncate max-w-xs">{{ $category->description ?? '--' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        <i class="fas {{ $category->is_active ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                                        {{ $category->is_active ? __('supplier.active') : __('supplier.inactive') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <button wire:click="edit({{ $category->id }})" 
                                            class="text-indigo-600 hover:text-indigo-900 transition-colors duration-150 transform hover:scale-110"
                                            title="{{ __('supplier.edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button wire:click="confirmDelete({{ $category->id }})" 
                                            class="text-red-600 hover:text-red-900 transition-colors duration-150 transform hover:scale-110"
                                            title="{{ __('supplier.delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                    {{ __('supplier.no_categories_found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="px-4 py-3 bg-white border-t border-gray-200">
                {{ $categories->links() }}
            </div>
        </div>
    </div>

    <!-- Modal de Adicionar/Editar Categoria -->
    <div x-data="{ show: @entangle('showModal') }" x-show="show" x-cloak
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto" 
        aria-labelledby="modal-title" 
        role="dialog" 
        aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="show"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                aria-hidden="true">
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="show"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row justify-between">
                    <h3 class="text-lg leading-6 font-medium text-white" id="modal-title">
                        <i class="fas {{ $editMode ? 'fa-edit' : 'fa-plus-circle' }} mr-2"></i>
                        {{ $editMode ? __('supplier.edit_category') : __('supplier.add_category') }}
                    </h3>
                    <button type="button" wire:click="closeModal" class="text-white hover:text-gray-200 transition-colors duration-150">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Modal Content -->
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                    @if ($errors->any())
                        <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-red-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700">
                                        {{ __('supplier.form_errors') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form wire:submit.prevent="save">
                        <div class="space-y-4">
                            <!-- Code -->
                            <div>
                                <label for="code" class="block text-sm font-medium text-gray-700">
                                    <i class="fas fa-barcode text-gray-500 mr-1"></i>
                                    {{ __('supplier.code') }} *
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input type="text" wire:model="code" id="code" 
                                        class="focus:ring-blue-500 focus:border-blue-500 block w-full pr-10 sm:text-sm border-gray-300 rounded-md {{ $errors->has('code') ? 'border-red-300 text-red-900 placeholder-red-300' : '' }}"
                                        placeholder="{{ __('supplier.code_placeholder') }}"
                                        {{ !$editMode ? 'readonly' : '' }}
                                        aria-invalid="true"
                                        aria-describedby="code-error">
                                </div>
                                @error('code')
                                    <p class="mt-2 text-sm text-red-600" id="code-error">
                                        <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">
                                    <i class="fas fa-tag text-gray-500 mr-1"></i>
                                    {{ __('supplier.name') }} *
                                </label>
                                <div class="mt-1">
                                    <input type="text" wire:model="name" id="name" 
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md {{ $errors->has('name') ? 'border-red-300 text-red-900 placeholder-red-300' : '' }}"
                                        placeholder="{{ __('supplier.name_placeholder') }}">
                                </div>
                                @error('name')
                                    <p class="mt-2 text-sm text-red-600">
                                        <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">
                                    <i class="fas fa-align-left text-gray-500 mr-1"></i>
                                    {{ __('supplier.description') }}
                                </label>
                                <div class="mt-1">
                                    <textarea wire:model="description" id="description" rows="3" 
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border border-gray-300 rounded-md"></textarea>
                                </div>
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="is_active" class="block text-sm font-medium text-gray-700">
                                    <i class="fas fa-toggle-on text-gray-500 mr-1"></i>
                                    {{ __('supplier.status') }}
                                </label>
                                <div class="mt-2">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" wire:model="is_active" class="sr-only peer">
                                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                        <span class="ml-3 text-sm font-medium text-gray-700">
                                            {{ $is_active ? __('supplier.active') : __('supplier.inactive') }}
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="save"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                        <i class="fas fa-save mr-2"></i>
                        {{ __('supplier.save') }}
                    </button>
                    <button type="button" wire:click="closeModal"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                        <i class="fas fa-times mr-2"></i>
                        {{ __('supplier.cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        @this.on('confirm-delete', (data) => {
            Swal.fire({
                title: data.title,
                text: data.text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: data.confirmButtonText,
                cancelButtonText: data.cancelButtonText,
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call(data.onConfirmed);
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    @this.call(data.onCancelled);
                }
            });
        });
    });
</script>
@endpush
