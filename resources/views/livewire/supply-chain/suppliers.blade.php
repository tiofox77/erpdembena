<div>
    <div class="py-2 sm:py-4">
        <div class="max-w-full mx-auto px-2 sm:px-4 lg:px-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4">
                <div class="flex items-center space-x-4">
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2 sm:mb-0 flex items-center">
                        <i class="fas fa-truck-loading mr-3 text-gray-700"></i> {{ __('livewire/suppliers.suppliers_management') }}
                    </h1>
                </div>
                <button
                    type="button"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-1.5 px-3 sm:py-2 sm:px-4 rounded flex items-center"
                    wire:click="openAddModal"
                >
                    <i class="fas fa-plus-circle mr-2"></i> {{ __('livewire/suppliers.add_supplier') }}
                </button>
            </div>

            <!-- Filters and Table Section -->
            <div class="bg-white rounded-lg shadow mb-4 sm:mb-6">
                <div class="p-2 sm:p-4">
                    <!-- Enhanced Filter Section -->
                    <div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                        <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                            <i class="fas fa-filter mr-2 text-blue-500"></i> {{ __('livewire/suppliers.filters_and_search') }}
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label for="search" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-search mr-1 text-gray-500"></i> {{ __('livewire/layout.search') }}
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                    <input
                                        type="text"
                                        wire:model.live.debounce.300ms="search"
                                        id="search"
                                        placeholder="{{ __('livewire/suppliers.search_suppliers') }}"
                                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                </div>
                            </div>
                            <div>
                                <label for="status" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-toggle-on mr-1 text-gray-500"></i> {{ __('livewire/suppliers.status') }}
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="status"
                                        wire:model.live="status"
                                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                        <option value="">{{ __('livewire/suppliers.all_statuses') }}</option>
                                        <option value="active">{{ __('livewire/suppliers.active') }}</option>
                                        <option value="inactive">{{ __('livewire/suppliers.inactive') }}</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label for="perPage" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-list-ol mr-1 text-gray-500"></i> {{ __('livewire/layout.per_page') }}
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        id="perPage"
                                        wire:model.live="perPage"
                                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button
                                wire:click="clearFilters"
                                class="px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md shadow-sm hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 flex items-center transition-colors duration-150"
                            >
                                <i class="fas fa-eraser mr-2"></i>
                                <span wire:loading.remove wire:target="clearFilters">{{ __('livewire/layout.clear_filters') }}</span>
                                <span wire:loading wire:target="clearFilters">{{ __('livewire/layout.clearing') }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Alert Messages -->
                    @if (session()->has('message'))
                        <div class="mb-4 flex w-full overflow-hidden bg-white rounded-lg shadow-md">
                            <div class="flex items-center justify-center w-12 bg-green-500">
                                <i class="fas fa-check text-white"></i>
                            </div>
                            <div class="px-4 py-2 -mx-3">
                                <div class="mx-3">
                                    <span class="font-semibold text-green-500">{{ __('livewire/layout.success') }}</span>
                                    <p class="text-sm text-gray-600">{{ session('message') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="mb-4 flex w-full overflow-hidden bg-white rounded-lg shadow-md">
                            <div class="flex items-center justify-center w-12 bg-red-500">
                                <i class="fas fa-exclamation-triangle text-white"></i>
                            </div>
                            <div class="px-4 py-2 -mx-3">
                                <div class="mx-3">
                                    <span class="font-semibold text-red-500">{{ __('livewire/layout.error') }}</span>
                                    <p class="text-sm text-gray-600">{{ session('error') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Enhanced Table -->
                    <div class="overflow-x-auto bg-white rounded-lg shadow">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th scope="col" class="group px-6 py-3 text-left">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-medium tracking-wider text-gray-500 uppercase">{{ __('livewire/suppliers.supplier_name') }}</span>
                                            <button wire:click="sortBy('name')" class="text-gray-400 focus:outline-none">
                                                @if ($sortField === 'name')
                                                    @if ($sortDirection === 'asc')
                                                        <i class="fas fa-sort-up text-blue-500"></i>
                                                    @else
                                                        <i class="fas fa-sort-down text-blue-500"></i>
                                                    @endif
                                                @else
                                                    <i class="fas fa-sort text-gray-300 group-hover:text-gray-400"></i>
                                                @endif
                                            </button>
                                        </div>
                                    </th>
                                    <th scope="col" class="group px-6 py-3 text-left">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-medium tracking-wider text-gray-500 uppercase">{{ __('livewire/suppliers.contact_person') }}</span>
                                            <button wire:click="sortBy('contact_person')" class="text-gray-400 focus:outline-none">
                                                @if ($sortField === 'contact_person')
                                                    @if ($sortDirection === 'asc')
                                                        <i class="fas fa-sort-up text-blue-500"></i>
                                                    @else
                                                        <i class="fas fa-sort-down text-blue-500"></i>
                                                    @endif
                                                @else
                                                    <i class="fas fa-sort text-gray-300 group-hover:text-gray-400"></i>
                                                @endif
                                            </button>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left">
                                        <span class="text-xs font-medium tracking-wider text-gray-500 uppercase">{{ __('livewire/suppliers.contact_info') }}</span>
                                    </th>
                                    <th scope="col" class="group px-6 py-3 text-left">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-medium tracking-wider text-gray-500 uppercase">{{ __('livewire/suppliers.status') }}</span>
                                            <button wire:click="sortBy('is_active')" class="text-gray-400 focus:outline-none">
                                                @if ($sortField === 'is_active')
                                                    @if ($sortDirection === 'asc')
                                                        <i class="fas fa-sort-up text-blue-500"></i>
                                                    @else
                                                        <i class="fas fa-sort-down text-blue-500"></i>
                                                    @endif
                                                @else
                                                    <i class="fas fa-sort text-gray-300 group-hover:text-gray-400"></i>
                                                @endif
                                            </button>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center">
                                        <span class="text-xs font-medium tracking-wider text-gray-500 uppercase">{{ __('livewire/layout.actions') }}</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($suppliers as $supplier)
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 bg-blue-100 text-blue-500 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-building"></i>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $supplier->name }}</div>
                                                    <div class="text-sm text-gray-500">{{ $supplier->supplier_code }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">{{ $supplier->contact_person }}</div>
                                            <div class="text-sm text-gray-500">{{ $supplier->position }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col">
                                                <span class="text-sm text-gray-900 flex items-center">
                                                    <i class="fas fa-envelope text-gray-400 mr-2"></i> {{ $supplier->email }}
                                                </span>
                                                <span class="text-sm text-gray-500 flex items-center mt-1">
                                                    <i class="fas fa-phone text-gray-400 mr-2"></i> {{ $supplier->phone }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $supplier->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                @if($supplier->is_active)
                                                    <i class="fas fa-check-circle mr-1"></i> {{ __('livewire/suppliers.active') }}
                                                @else
                                                    <i class="fas fa-times-circle mr-1"></i> {{ __('livewire/suppliers.inactive') }}
                                                @endif
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex items-center justify-center space-x-3">
                                                <button 
                                                    wire:click="openViewModal({{ $supplier->id }})" 
                                                    class="text-blue-600 hover:text-blue-900 focus:outline-none"
                                                    title="{{ __('livewire/layout.view') }}"
                                                >
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button 
                                                    wire:click="edit({{ $supplier->id }})" 
                                                    class="text-indigo-600 hover:text-indigo-900 focus:outline-none"
                                                    title="{{ __('livewire/layout.edit') }}"
                                                >
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button 
                                                    wire:click="confirmDelete({{ $supplier->id }})" 
                                                    class="text-red-600 hover:text-red-900 focus:outline-none"
                                                    title="{{ __('livewire/layout.delete') }}"
                                                >
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-500">
                                                <i class="fas fa-folder-open text-4xl mb-4"></i>
                                                <p class="text-lg font-medium">{{ __('livewire/suppliers.no_suppliers_found') }}</p>
                                                <p class="text-sm mt-2">{{ __('livewire/suppliers.add_first_supplier') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                <div class="mt-4">
                    {{ $suppliers->links() }}
                </div>



                <!-- Modal de Adicionar/Editar Fornecedor -->
                @if($showModal)
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center overflow-y-auto p-2 sm:p-4">
                    <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all w-full max-w-4xl max-h-[90vh] overflow-y-auto">
                        <!-- Enhanced Modal Header -->
                        <div class="bg-gray-50 px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 flex justify-between items-center sticky top-0 z-10">
                            <h3 class="text-base sm:text-lg font-medium text-gray-900 flex items-center">
                                <span class="bg-blue-100 text-blue-600 p-2 rounded-full mr-3">
                                    <i class="fas {{ $supplier_id ? 'fa-edit' : 'fa-plus-circle' }} text-lg"></i>
                                </span>
                                {{ $supplier_id ? __('livewire/suppliers.edit_supplier') : __('livewire/suppliers.add_supplier') }}
                            </h3>
                            <button wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        
                        <!-- Modal Body -->
                        <div class="px-4 sm:px-6 py-4 sm:py-5">
                            <p class="text-gray-600 text-xs sm:text-sm mb-3 sm:mb-4">{{ __('livewire/suppliers.fill_details') }}</p>
                            
                            <!-- Mensagens de erro gerais -->
                            @if ($errors->any())
                            <div class="rounded-md bg-red-50 p-4 mb-4 border border-red-200">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800">{{ __('livewire/suppliers.form_errors', ['count' => $errors->count()]) }}</h3>
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
                
                            <form wire:submit.prevent="save">
                                <!-- Informações Básicas -->
                                <div class="bg-gray-50 p-4 rounded-md mb-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                                        <i class="fas fa-info-circle mr-2 text-blue-500"></i> {{ __('livewire/suppliers.basic_info') }}
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <!-- Nome do Fornecedor -->
                                        <div class="md:col-span-2">
                                            <label for="name" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                                <i class="fas fa-building mr-1 text-gray-500"></i> {{ __('livewire/suppliers.supplier_name') }} <span class="text-red-500">*</span>
                                            </label>
                                            <div class="relative rounded-md shadow-sm">
                                                <input 
                                                    type="text" 
                                                    id="name" 
                                                    wire:model="name" 
                                                    class="block w-full py-2 px-3 border @error('name') border-red-300 focus:ring-red-500 focus:border-red-500 @else border-gray-300 focus:ring-blue-500 focus:border-blue-500 @enderror bg-white rounded-md shadow-sm sm:text-sm"
                                                >
                                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    @error('name')
                                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                                    @enderror
                                                </div>
                                            </div>
                                            @error('name') 
                                                <p class="mt-1 text-xs text-red-600 flex items-center">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                                    {{ $message }}
                                                </p> 
                                            @enderror
                                        </div>
                    
                                        <!-- Código do Fornecedor -->
                                        <div>
                                            <label for="supplier_code" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                                <i class="fas fa-barcode mr-1 text-gray-500"></i> {{ __('livewire/suppliers.supplier_code') }}
                                            </label>
                                            <div class="relative rounded-md shadow-sm">
                                                <input 
                                                    type="text" 
                                                    id="supplier_code" 
                                                    wire:model="supplier_code" 
                                                    class="block w-full py-2 px-3 border @error('supplier_code') border-red-300 focus:ring-red-500 focus:border-red-500 @else border-gray-300 focus:ring-blue-500 focus:border-blue-500 @enderror bg-white rounded-md shadow-sm sm:text-sm"
                                                >
                                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    @error('supplier_code')
                                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                                    @enderror
                                                </div>
                                            </div>
                                            @error('supplier_code') 
                                                <p class="mt-1 text-xs text-red-600 flex items-center">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                                    {{ $message }}
                                                </p> 
                                            @enderror
                                        </div>
                    
                                        <!-- ID Fiscal -->
                                        <div>
                                            <label for="tax_id" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                                <i class="fas fa-id-card mr-1 text-gray-500"></i> {{ __('livewire/suppliers.tax_id') }}
                                            </label>
                                            <div class="relative rounded-md shadow-sm">
                                                <input 
                                                    type="text" 
                                                    id="tax_id" 
                                                    wire:model="tax_id" 
                                                    class="block w-full py-2 px-3 border @error('tax_id') border-red-300 focus:ring-red-500 focus:border-red-500 @else border-gray-300 focus:ring-blue-500 focus:border-blue-500 @enderror bg-white rounded-md shadow-sm sm:text-sm"
                                                >
                                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    @error('tax_id')
                                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                                    @enderror
                                                </div>
                                            </div>
                                            @error('tax_id') 
                                                <p class="mt-1 text-xs text-red-600 flex items-center">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                                    {{ $message }}
                                                </p> 
                                            @enderror
                                        </div>

                                        <!-- Status -->
                                        <div>
                                            <label for="is_active" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                                <i class="fas fa-toggle-on mr-1 text-gray-500"></i> {{ __('livewire/suppliers.status') }}
                                            </label>
                                            <div class="relative rounded-md shadow-sm">
                                                <select
                                                    id="is_active"
                                                    wire:model="is_active"
                                                    class="block w-full py-2 px-3 border @error('is_active') border-red-300 focus:ring-red-500 focus:border-red-500 @else border-gray-300 focus:ring-blue-500 focus:border-blue-500 @enderror bg-white rounded-md shadow-sm sm:text-sm"
                                                >
                                                    <option value="1">{{ __('livewire/suppliers.active') }}</option>
                                                    <option value="0">{{ __('livewire/suppliers.inactive') }}</option>
                                                </select>
                                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    @error('is_active')
                                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                                    @else
                                                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                                    @enderror
                                                </div>
                                            </div>
                                            @error('is_active') 
                                                <p class="mt-1 text-xs text-red-600 flex items-center">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                                    {{ $message }}
                                                </p> 
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                        
                                <!-- Informações de Contato -->
                                <div class="bg-gray-50 p-4 rounded-md mb-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                                        <i class="fas fa-address-card mr-2 text-blue-500"></i> {{ __('livewire/suppliers.contact_information') }}
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <!-- Contato -->
                                        <div>
                                            <label for="contact_person" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                                <i class="fas fa-user mr-1 text-gray-500"></i> {{ __('livewire/suppliers.contact_person') }}
                                            </label>
                                            <div class="relative rounded-md shadow-sm">
                                                <input 
                                                    type="text" 
                                                    id="contact_person" 
                                                    wire:model="contact_person" 
                                                    class="block w-full py-2 px-3 border @error('contact_person') border-red-300 focus:ring-red-500 focus:border-red-500 @else border-gray-300 focus:ring-blue-500 focus:border-blue-500 @enderror bg-white rounded-md shadow-sm sm:text-sm"
                                                >
                                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    @error('contact_person')
                                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                                    @enderror
                                                </div>
                                            </div>
                                            @error('contact_person') 
                                                <p class="mt-1 text-xs text-red-600 flex items-center">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                                    {{ $message }}
                                                </p> 
                                            @enderror
                                        </div>
                    
                                        <!-- Cargo -->
                                        <div>
                                            <label for="position" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                                <i class="fas fa-id-badge mr-1 text-gray-500"></i> {{ __('livewire/suppliers.position') }}
                                            </label>
                                            <div class="relative rounded-md shadow-sm">
                                                <input 
                                                    type="text" 
                                                    id="position" 
                                                    wire:model="position" 
                                                    class="block w-full py-2 px-3 border @error('position') border-red-300 focus:ring-red-500 focus:border-red-500 @else border-gray-300 focus:ring-blue-500 focus:border-blue-500 @enderror bg-white rounded-md shadow-sm sm:text-sm"
                                                >
                                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    @error('position')
                                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                                    @enderror
                                                </div>
                                            </div>
                                            @error('position') 
                                                <p class="mt-1 text-xs text-red-600 flex items-center">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                                    {{ $message }}
                                                </p> 
                                            @enderror
                                        </div>
                    
                                        <!-- Email -->
                                        <div>
                                            <label for="email" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                                <i class="fas fa-envelope mr-1 text-gray-500"></i> {{ __('livewire/suppliers.email') }}
                                            </label>
                                            <div class="relative rounded-md shadow-sm">
                                                <input 
                                                    type="email" 
                                                    id="email" 
                                                    wire:model="email" 
                                                    class="block w-full py-2 px-3 border @error('email') border-red-300 focus:ring-red-500 focus:border-red-500 @else border-gray-300 focus:ring-blue-500 focus:border-blue-500 @enderror bg-white rounded-md shadow-sm sm:text-sm"
                                                >
                                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    @error('email')
                                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                                    @enderror
                                                </div>
                                            </div>
                                            @error('email') 
                                                <p class="mt-1 text-xs text-red-600 flex items-center">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                                    {{ $message }}
                                                </p> 
                                            @enderror
                                        </div>
                    
                                        <!-- Telefone -->
                                        <div>
                                            <label for="phone" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                                <i class="fas fa-phone mr-1 text-gray-500"></i> {{ __('livewire/suppliers.phone') }}
                                            </label>
                                            <div class="relative rounded-md shadow-sm">
                                                <input 
                                                    type="text" 
                                                    id="phone" 
                                                    wire:model="phone" 
                                                    class="block w-full py-2 px-3 border @error('phone') border-red-300 focus:ring-red-500 focus:border-red-500 @else border-gray-300 focus:ring-blue-500 focus:border-blue-500 @enderror bg-white rounded-md shadow-sm sm:text-sm"
                                                >
                                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    @error('phone')
                                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                                    @enderror
                                                </div>
                                            </div>
                                            @error('phone') 
                                                <p class="mt-1 text-xs text-red-600 flex items-center">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                                    {{ $message }}
                                                </p> 
                                            @enderror
                                        </div>
                    
                                        <!-- Site -->
                                        <div class="md:col-span-4">
                                            <label for="website" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                                <i class="fas fa-globe mr-1 text-gray-500"></i> {{ __('livewire/suppliers.website') }}
                                            </label>
                                            <div class="relative rounded-md shadow-sm">
                                                <input 
                                                    type="url" 
                                                    id="website" 
                                                    wire:model="website" 
                                                    class="block w-full py-2 px-3 border @error('website') border-red-300 focus:ring-red-500 focus:border-red-500 @else border-gray-300 focus:ring-blue-500 focus:border-blue-500 @enderror bg-white rounded-md shadow-sm sm:text-sm"
                                                >
                                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    @error('website')
                                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                                    @enderror
                                                </div>
                                            </div>
                                            @error('website') 
                                                <p class="mt-1 text-xs text-red-600 flex items-center">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                                    {{ $message }}
                                                </p> 
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                        
                                <!-- Informações de Endereço -->
                                <div class="bg-gray-50 p-4 rounded-md mb-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                                        <i class="fas fa-map-marker-alt mr-2 text-blue-500"></i> {{ __('livewire/suppliers.address_info') }}
                                    </h4>
                                    <div class="grid grid-cols-1 gap-4">
                                        <!-- Endereço -->
                                        <div>
                                            <label for="address" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                                <i class="fas fa-map mr-1 text-gray-500"></i> {{ __('livewire/suppliers.address') }}
                                            </label>
                                            <div class="relative rounded-md shadow-sm">
                                                <textarea 
                                                    id="address" 
                                                    wire:model="address" 
                                                    rows="3" 
                                                    class="block w-full py-2 px-3 border @error('address') border-red-300 focus:ring-red-500 focus:border-red-500 @else border-gray-300 focus:ring-blue-500 focus:border-blue-500 @enderror bg-white rounded-md shadow-sm sm:text-sm"
                                                ></textarea>
                                            </div>
                                            @error('address') 
                                                <p class="mt-1 text-xs text-red-600 flex items-center">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                                    {{ $message }}
                                                </p> 
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                        
                                <!-- Notas e Descrição -->
                                <div class="bg-gray-50 p-4 rounded-md mb-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                                        <i class="fas fa-sticky-note mr-2 text-blue-500"></i> {{ __('livewire/suppliers.notes_and_description') }}
                                    </h4>
                                    <div class="grid grid-cols-1 gap-4">
                                        <!-- Observações -->
                                        <div>
                                            <label for="notes" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                                <i class="fas fa-comment mr-1 text-gray-500"></i> {{ __('livewire/suppliers.notes') }}
                                            </label>
                                            <div class="relative rounded-md shadow-sm">
                                                <textarea 
                                                    id="notes" 
                                                    wire:model="notes" 
                                                    rows="3" 
                                                    class="block w-full py-2 px-3 border @error('notes') border-red-300 focus:ring-red-500 focus:border-red-500 @else border-gray-300 focus:ring-blue-500 focus:border-blue-500 @enderror bg-white rounded-md shadow-sm sm:text-sm"
                                                ></textarea>
                                            </div>
                                            @error('notes') 
                                                <p class="mt-1 text-xs text-red-600 flex items-center">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                                    {{ $message }}
                                                </p> 
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Botões de ação -->
                                <div class="py-3 bg-gray-50 text-right flex justify-end space-x-3 rounded-md px-3 sticky bottom-0">
                                    <button 
                                        type="button" 
                                        wire:click="closeModal"
                                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150 flex items-center"
                                    >
                                        <i class="fas fa-times mr-2"></i> {{ __('livewire/layout.cancel') }}
                                    </button>
                                    <button 
                                        type="submit"
                                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150 flex items-center"
                                    >
                                        <i class="fas fa-save mr-2"></i>
                                        <span wire:loading.remove wire:target="save">{{ __('livewire/layout.save') }}</span>
                                        <span wire:loading wire:target="save">{{ __('livewire/layout.saving') }}...</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif


                <!-- Modal de Visualização de Fornecedor -->
                @if($showViewModal)
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center overflow-y-auto p-2 sm:p-4">
                    <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all w-full max-w-4xl max-h-[90vh] overflow-y-auto">
                        <!-- Enhanced Modal Header -->
                        <div class="bg-gray-50 px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 flex justify-between items-center sticky top-0 z-10">
                            <h3 class="text-base sm:text-lg font-medium text-gray-900 flex items-center">
                                <span class="bg-blue-100 text-blue-600 p-2 rounded-full mr-3">
                                    <i class="fas fa-building text-lg"></i>
                                </span>
                                {{ __('livewire/suppliers.supplier_details') }}
                            </h3>
                            <button wire:click="closeViewModal" class="text-gray-400 hover:text-gray-500">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        
                        <!-- Modal Body -->
                        <div class="px-4 sm:px-6 py-4 sm:py-5">
                            <!-- Informações Básicas -->
                            <div class="bg-gray-50 p-4 rounded-md mb-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                                    <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                                    {{ __('livewire/suppliers.basic_info') }}
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-xs font-medium text-gray-500">{{ __('livewire/suppliers.supplier_name') }}</p>
                                        <p class="text-sm font-medium text-gray-800">{{ $viewSupplier->name ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500">{{ __('livewire/suppliers.supplier_code') }}</p>
                                        <p class="text-sm font-medium text-gray-800">{{ $viewSupplier->supplier_code ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500">{{ __('livewire/suppliers.tax_id') }}</p>
                                        <p class="text-sm font-medium text-gray-800">{{ $viewSupplier->tax_id ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500">{{ __('livewire/suppliers.status') }}</p>
                                        <p class="text-sm font-medium">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ isset($viewSupplier) && $viewSupplier->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                @if(isset($viewSupplier) && $viewSupplier->is_active)
                                                    <i class="fas fa-check-circle mr-1"></i> {{ __('livewire/suppliers.active') }}
                                                @else
                                                    <i class="fas fa-times-circle mr-1"></i> {{ __('livewire/suppliers.inactive') }}
                                                @endif
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                
                            <!-- Informações de Contato -->
                            <div class="bg-gray-50 p-4 rounded-md mb-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                                    <i class="fas fa-address-card mr-2 text-blue-500"></i>
                                    {{ __('livewire/suppliers.contact_information') }}
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-xs font-medium text-gray-500">{{ __('livewire/suppliers.contact_person') }}</p>
                                        <p class="text-sm font-medium text-gray-800">{{ $viewSupplier->contact_person ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500">{{ __('livewire/suppliers.position') }}</p>
                                        <p class="text-sm font-medium text-gray-800">{{ $viewSupplier->position ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500">{{ __('livewire/suppliers.email') }}</p>
                                        <p class="text-sm font-medium text-gray-800">
                                            @if(isset($viewSupplier) && $viewSupplier->email)
                                                <a href="mailto:{{ $viewSupplier->email }}" class="text-blue-600 hover:text-blue-800">
                                                    <i class="fas fa-envelope mr-1"></i> {{ $viewSupplier->email }}
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500">{{ __('livewire/suppliers.phone') }}</p>
                                        <p class="text-sm font-medium text-gray-800">
                                            @if(isset($viewSupplier) && $viewSupplier->phone)
                                                <a href="tel:{{ $viewSupplier->phone }}" class="text-blue-600 hover:text-blue-800">
                                                    <i class="fas fa-phone mr-1"></i> {{ $viewSupplier->phone }}
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </p>
                                    </div>
                                    <div class="col-span-2">
                                        <p class="text-xs font-medium text-gray-500">{{ __('livewire/suppliers.website') }}</p>
                                        <p class="text-sm font-medium text-gray-800">
                                            @if(isset($viewSupplier) && $viewSupplier->website)
                                                <a href="{{ $viewSupplier->website }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                                    <i class="fas fa-globe mr-1"></i> {{ $viewSupplier->website }}
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                
                            <!-- Informações de Endereço -->
                            <div class="bg-gray-50 p-4 rounded-md mb-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                                    <i class="fas fa-map-marker-alt mr-2 text-blue-500"></i>
                                    {{ __('livewire/suppliers.address_info') }}
                                </h4>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 mb-1">{{ __('livewire/suppliers.address') }}</p>
                                    <p class="text-sm font-medium text-gray-800">{{ $viewSupplier->address ?? '-' }}</p>
                                </div>
                            </div>
                
                            <!-- Informações Adicionais -->
                            <div class="bg-gray-50 p-4 rounded-md mb-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                                    <i class="fas fa-sticky-note mr-2 text-blue-500"></i>
                                    {{ __('livewire/suppliers.additional_info') }}
                                </h4>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 mb-1">{{ __('livewire/suppliers.notes') }}</p>
                                    <p class="text-sm font-medium text-gray-800">{{ $viewSupplier->notes ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                
                        <!-- Botões de ação -->
                        <div class="py-3 bg-gray-50 text-right flex justify-end space-x-3 rounded-md px-3 sticky bottom-0">
                            <button 
                                type="button" 
                                wire:click="closeViewModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150 flex items-center"
                            >
                                <i class="fas fa-times mr-2"></i> {{ __('livewire/layout.close') }}
                            </button>
                            <button 
                                type="button"
                                wire:click="edit({{ isset($viewSupplier) ? $viewSupplier->id : 0 }})"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150 flex items-center"
                            >
                                <i class="fas fa-edit mr-2"></i> {{ __('livewire/layout.edit') }}
                            </button>
                        </div>
                    </div>
                </div>
                @endif
                        </div>
                    </div>
                </div>


                <!-- Modal de Confirmação de Exclusão -->
                @if($showDeleteModal)
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center overflow-y-auto p-2 sm:p-4">
                    <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all w-full max-w-lg">
                        <!-- Enhanced Modal Header -->
                        <div class="bg-red-50 px-4 sm:px-6 py-3 sm:py-4 border-b border-red-100 flex justify-between items-center">
                            <h3 class="text-base sm:text-lg font-medium text-red-700 flex items-center">
                                <span class="bg-red-100 text-red-600 p-2 rounded-full mr-3">
                                    <i class="fas fa-exclamation-triangle text-lg"></i>
                                </span>
                                {{ __('livewire/suppliers.confirm_deletion') }}
                            </h3>
                            <button wire:click="closeDeleteModal" class="text-gray-400 hover:text-gray-500">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                
                        <!-- Modal Body -->
                        <div class="px-4 sm:px-6 py-4 sm:py-5">
                            <div class="flex items-start mb-4">
                                <div class="flex-shrink-0 mt-0.5">
                                    <i class="fas fa-info-circle text-yellow-500 text-lg"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-gray-600">{{ __('livewire/suppliers.delete_supplier_confirmation') }}</p>
                                    <p class="mt-1 text-sm font-semibold text-gray-900">{{ $deleteSupplierName }}</p>
                                </div>
                            </div>
                            
                            <!-- Informações do Fornecedor -->
                            <div class="bg-gray-50 p-4 rounded-md mt-3">
                                <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                                    <i class="fas fa-building mr-2 text-blue-500"></i>
                                    {{ __('livewire/suppliers.supplier_details') }}
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <p class="text-xs font-medium text-gray-500">{{ __('livewire/suppliers.supplier_code') }}</p>
                                        <p class="text-sm font-medium text-gray-800">{{ $deleteSupplier->supplier_code ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500">{{ __('livewire/suppliers.tax_id') }}</p>
                                        <p class="text-sm font-medium text-gray-800">{{ $deleteSupplier->tax_id ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Aviso -->
                            <div class="mt-4 bg-yellow-50 p-3 rounded-md border border-yellow-100">
                                <p class="text-xs text-yellow-700 flex items-start">
                                    <i class="fas fa-exclamation-circle mr-2 mt-0.5"></i>
                                    {{ __('livewire/suppliers.delete_warning') }}
                                </p>
                            </div>
                        </div>
                
                        <!-- Botões de ação -->
                        <div class="py-3 bg-gray-50 flex justify-between sm:justify-end space-x-3 px-4 sm:px-6 border-t border-gray-200">
                            <button 
                                type="button" 
                                wire:click="closeDeleteModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150 flex items-center"
                            >
                                <i class="fas fa-times mr-2"></i> {{ __('livewire/layout.cancel') }}
                            </button>
                            <button 
                                type="button"
                                wire:click="delete"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-150 flex items-center"
                            >
                                <i class="fas fa-trash-alt mr-2"></i>
                                <span wire:loading.remove wire:target="delete">{{ __('livewire/layout.delete') }}</span>
                                <span wire:loading wire:target="delete">{{ __('livewire/layout.deleting') }}...</span>
                            </button>
                        </div>
                    </div>
                </div>
                @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>