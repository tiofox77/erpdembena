<div>
    <div class="container mx-auto px-4 py-6">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
            <h1 class="text-2xl font-semibold text-gray-800 flex items-center">
                <i class="fas fa-truck-loading text-blue-600 mr-3"></i>
                {{ __('livewire/suppliers.suppliers_management') }}
            </h1>
            <button wire:click="openAddModal" 
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 hover:shadow-lg">
                <i class="fas fa-plus-circle mr-2 animate-pulse"></i>
                {{ __('livewire/suppliers.add_supplier') }}
            </button>
        </div>

        <!-- Cartão de Busca e Filtros -->
        <div class="mb-6 bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 ease-in-out hover:shadow-lg transform hover:scale-[1.01]">
            <!-- Cabeçalho do cartão com gradiente -->
            <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                <i class="fas fa-filter text-blue-600 mr-2"></i>
                <h2 class="text-base font-medium text-gray-700">{{ __('livewire/suppliers.filters_and_search') }}</h2>
            </div>
            <!-- Conteúdo do cartão -->
            <div class="p-4">
                <div class="flex flex-col gap-4">
                    <!-- Campo de busca -->
                    <div>
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
                                placeholder="{{ __('livewire/suppliers.search_suppliers') }}" 
                                type="search">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">{{ __('livewire/suppliers.search_by_name_code_tax_id') }}</p>
                    </div>
                    
                    <!-- Linha de filtros -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Filtro de Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-toggle-on text-gray-500 mr-1"></i>
                                {{ __('livewire/suppliers.status') }}
                            </label>
                            <select wire:model.live="status" id="status" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="">{{ __('livewire/products.all_statuses') }}</option>
                                <option value="active">{{ __('livewire/suppliers.active') }}</option>
                                <option value="inactive">{{ __('livewire/suppliers.inactive') }}</option>
                            </select>
                        </div>
                        
                        <!-- Category Filter -->
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-tags text-gray-500 mr-1"></i>
                                {{ __('supplier.category') }}
                            </label>
                            <select wire:model.live="category_id" id="category_id" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="">{{ __('livewire/products.all_types') }}</option>
                                @if(isset($categories) && count($categories) > 0)
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        
                        <!-- Registros por página -->
                        <div>
                            <label for="perPage" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-list-ol text-gray-500 mr-1"></i>
                                {{ __('livewire/layout.items_per_page') }}
                            </label>
                            <select wire:model.live="perPage" id="perPage" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Botões de ação -->
                    <div class="flex justify-end">
                        <button wire:click="resetFilters" 
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <span wire:loading.remove wire:target="resetFilters">
                                <i class="fas fa-filter-circle-xmark mr-2"></i>
                                {{ __('livewire/layout.clear_filters') }}
                            </span>
                            <span wire:loading wire:target="resetFilters">{{ __('livewire/layout.clearing') }}...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alertas -->
        @if (session()->has('message'))
            <div class="mb-4 flex w-full overflow-hidden bg-white rounded-lg shadow-md">
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

        @if (session()->has('error'))
            <div class="mb-4 flex w-full overflow-hidden bg-white rounded-lg shadow-md">
                <div class="flex items-center justify-center w-12 bg-red-500">
                    <i class="fas fa-exclamation-circle text-white"></i>
                </div>
                <div class="px-4 py-2 -mx-3">
                    <div class="mx-3">
                        <p class="text-sm text-gray-600">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Tabela de Fornecedores -->
        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden transition-all duration-300 ease-in-out hover:shadow-lg">
            <!-- Cabeçalho da Tabela -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
                <h2 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-truck-loading mr-2"></i>
                    {{ __('livewire/suppliers.suppliers_list') }}
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('name')">
                                    {{ __('livewire/suppliers.name') }}
                                    @if($sortField === 'name')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 text-gray-400"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('category_id')">
                                    {{ __('supplier.category') }}
                                    @if($sortField === 'category_id')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 text-gray-400"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('supplier_code')">
                                    {{ __('livewire/suppliers.supplier_code') }}
                                    @if($sortField === 'supplier_code')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 text-gray-400"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('livewire/suppliers.contact_email') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('livewire/suppliers.phone') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('status')">
                                    {{ __('livewire/suppliers.status') }}
                                    @if($sortField === 'status')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 text-gray-400"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('livewire/layout.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($suppliers as $supplier)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $supplier->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        @if($supplier->category)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ $supplier->category->name }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">--</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">{{ $supplier->supplier_code }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">{{ $supplier->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">{{ $supplier->phone }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $supplier->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        <i class="fas {{ $supplier->status === 'active' ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                                        {{ $supplier->status === 'active' ? __('livewire/suppliers.active') : __('livewire/suppliers.inactive') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <button wire:click="view({{ $supplier->id }})" 
                                            class="text-blue-600 hover:text-blue-900 transition-colors duration-150 transform hover:scale-110">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button wire:click="edit({{ $supplier->id }})" 
                                            class="text-indigo-600 hover:text-indigo-900 transition-colors duration-150 transform hover:scale-110">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button wire:click="confirmDelete({{ $supplier->id }})" 
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
                                            <i class="fas fa-truck-loading text-gray-400 text-2xl"></i>
                                        </div>
                                        <p class="text-gray-500 text-sm">{{ __('livewire/suppliers.no_suppliers_found') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <div class="px-4 py-3 bg-white border-t border-gray-200">
                {{ $suppliers->links() }}
            </div>
        </div>
    </div>

    <!-- Modais -->
    @include('livewire.supply-chain.suppliers-modals')
    @include('livewire.supply-chain.delete-supplier-modal')
    
    <!-- Modal de Visualização -->
    <div x-data="{ show: @entangle('showViewModal') }" x-show="show" x-cloak
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="show"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="show"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                
                <!-- Cabeçalho do Modal -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row justify-between">
                    <h3 class="text-lg leading-6 font-medium text-white" id="view-modal-title">
                        <i class="fas fa-eye mr-2"></i>
                        {{ __('livewire/suppliers.view_supplier') }}
                    </h3>
                    <button type="button" wire:click="closeViewModal" class="text-white hover:text-gray-200 transition-colors duration-150">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Conteúdo do Modal -->
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 max-h-[80vh] overflow-y-auto">
                    @if($viewingSupplier)
                        <!-- Informações Básicas -->
                        <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                            <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                <h2 class="text-base font-medium text-gray-700">{{ __('livewire/suppliers.basic_info') }}</h2>
                            </div>
                            <div class="p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Nome do Fornecedor -->
                                    <div class="col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-building text-gray-500 mr-1"></i>
                                            {{ __('livewire/suppliers.supplier_name') }}
                                        </label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $viewingSupplier['name'] ?? 'N/A' }}</p>
                                    </div>
                                    
                                    <!-- Código do Fornecedor -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-barcode text-gray-500 mr-1"></i>
                                            {{ __('livewire/suppliers.supplier_code') }}
                                        </label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $viewingSupplier['supplier_code'] ?? 'N/A' }}</p>
                                    </div>
                                    
                                    <!-- ID Fiscal -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-id-card text-gray-500 mr-1"></i>
                                            {{ __('livewire/suppliers.tax_id') }}
                                        </label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $viewingSupplier['tax_id'] ?? 'N/A' }}</p>
                                    </div>
                                    
                                    <!-- Status -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-toggle-on text-gray-500 mr-1"></i>
                                            {{ __('livewire/suppliers.status') }}
                                        </label>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $viewingSupplier['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            <i class="fas {{ $viewingSupplier['status'] === 'active' ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                                            {{ $viewingSupplier['status'] === 'active' ? __('livewire/suppliers.active') : __('livewire/suppliers.inactive') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informações de Contato -->
                        <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                            <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-address-card text-blue-600 mr-2"></i>
                                <h2 class="text-base font-medium text-gray-700">{{ __('livewire/suppliers.contact_information') }}</h2>
                            </div>
                            <div class="p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Contato -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-user text-gray-500 mr-1"></i>
                                            {{ __('livewire/suppliers.contact_person') }}
                                        </label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $viewingSupplier['contact_person'] ?? 'N/A' }}</p>
                                    </div>
                                    
                                    <!-- Cargo -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-id-badge text-gray-500 mr-1"></i>
                                            {{ __('livewire/suppliers.position') }}
                                        </label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $viewingSupplier['position'] ?? 'N/A' }}</p>
                                    </div>
                                    
                                    <!-- Email -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-envelope text-gray-500 mr-1"></i>
                                            {{ __('livewire/suppliers.email') }}
                                        </label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            @if(!empty($viewingSupplier['email']))
                                                <a href="mailto:{{ $viewingSupplier['email'] }}" class="text-blue-600 hover:text-blue-800">
                                                    {{ $viewingSupplier['email'] }}
                                                </a>
                                            @else
                                                N/A
                                            @endif
                                        </p>
                                    </div>
                                    
                                    <!-- Telefone -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-phone-alt text-gray-500 mr-1"></i>
                                            {{ __('livewire/suppliers.phone') }}
                                        </label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            @if(!empty($viewingSupplier['phone']))
                                                <a href="tel:{{ $viewingSupplier['phone'] }}" class="text-blue-600 hover:text-blue-800">
                                                    {{ $viewingSupplier['phone'] }}
                                                </a>
                                            @else
                                                N/A
                                            @endif
                                        </p>
                                    </div>
                                    
                                    <!-- Website -->
                                    <div class="col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-globe text-gray-500 mr-1"></i>
                                            {{ __('livewire/suppliers.website') }}
                                        </label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            @if(!empty($viewingSupplier['website']))
                                                <a href="{{ $viewingSupplier['website'] }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                                    {{ $viewingSupplier['website'] }}
                                                </a>
                                            @else
                                                N/A
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informações de Endereço -->
                        <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                            <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-map-marker-alt text-blue-600 mr-2"></i>
                                <h2 class="text-base font-medium text-gray-700">{{ __('livewire/suppliers.address_information') }}</h2>
                            </div>
                            <div class="p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Endereço -->
                                    <div class="col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-map-marked-alt text-gray-500 mr-1"></i>
                                            {{ __('livewire/suppliers.address') }}
                                        </label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $viewingSupplier['address'] ?? 'N/A' }}</p>
                                    </div>
                                    
                                    <!-- Cidade -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-city text-gray-500 mr-1"></i>
                                            {{ __('livewire/suppliers.city') }}
                                        </label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $viewingSupplier['city'] ?? 'N/A' }}</p>
                                    </div>
                                    
                                    <!-- Estado -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-map text-gray-500 mr-1"></i>
                                            {{ __('livewire/suppliers.state') }}
                                        </label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $viewingSupplier['state'] ?? 'N/A' }}</p>
                                    </div>
                                    
                                    <!-- País -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-flag text-gray-500 mr-1"></i>
                                            {{ __('livewire/suppliers.country') }}
                                        </label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $viewingSupplier['country'] ?? 'N/A' }}</p>
                                    </div>
                                    
                                    <!-- CEP -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-mail-bulk text-gray-500 mr-1"></i>
                                            {{ __('livewire/suppliers.postal_code') }}
                                        </label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $viewingSupplier['postal_code'] ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informações Adicionais -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                            <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                <h2 class="text-base font-medium text-gray-700">{{ __('livewire/suppliers.additional_information') }}</h2>
                            </div>
                            <div class="p-4">
                                <!-- Notas -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        <i class="fas fa-sticky-note text-gray-500 mr-1"></i>
                                        {{ __('livewire/suppliers.notes') }}
                                    </label>
                                    <div class="mt-1 bg-gray-50 p-3 rounded border border-gray-200">
                                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $viewingSupplier['notes'] ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Termos de Pagamento -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-file-invoice-dollar text-gray-500 mr-1"></i>
                                            {{ __('livewire/suppliers.payment_terms') }}
                                        </label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{ $viewingSupplier['payment_terms'] ? $viewingSupplier['payment_terms'] . ' ' . __('livewire/suppliers.days') : 'N/A' }}
                                        </p>
                                    </div>
                                    
                                    <!-- Limite de Crédito -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-credit-card text-gray-500 mr-1"></i>
                                            {{ __('livewire/suppliers.credit_limit') }}
                                        </label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{ isset($viewingSupplier['credit_limit']) ? number_format($viewingSupplier['credit_limit'], 2, ',', '.') . ' €' : 'N/A' }}
                                        </p>
                                    </div>
                                    
                                    <!-- Banco -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-university text-gray-500 mr-1"></i>
                                            {{ __('livewire/suppliers.bank_name') }}
                                        </label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $viewingSupplier['bank_name'] ?? 'N/A' }}</p>
                                    </div>
                                    
                                    <!-- Conta Bancária -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-credit-card text-gray-500 mr-1"></i>
                                            {{ __('livewire/suppliers.bank_account') }}
                                        </label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $viewingSupplier['bank_account'] ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-exclamation-circle text-gray-400 text-5xl mb-4"></i>
                            <p class="text-gray-500">{{ __('livewire/suppliers.no_supplier_data') }}</p>
                        </div>
                    @endif
                </div>

                <!-- Rodapé do Modal -->
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="closeViewModal"
                        class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                        {{ __('livewire/layout.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>