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
                            <input wire:model.debounce.300ms="search" id="search" 
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
                            <select wire:model="status" id="status" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="">{{ __('livewire/suppliers.all_statuses') }}</option>
                                <option value="active">{{ __('livewire/suppliers.active') }}</option>
                                <option value="inactive">{{ __('livewire/suppliers.inactive') }}</option>
                            </select>
                        </div>
                        
                        <!-- Filtro de Categoria (se existir) -->
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-tags text-gray-500 mr-1"></i>
                                {{ __('livewire/suppliers.category') }}
                            </label>
                            <select wire:model="category" id="category" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="">{{ __('livewire/suppliers.all_categories') }}</option>
                                @if(isset($categories) && count($categories) > 0)
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
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
                            <select wire:model="perPage" id="perPage" 
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
                        <button wire:click="clearFilters" 
                            class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md text-sm font-medium text-gray-700 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                            <i class="fas fa-redo-alt mr-2"></i>
                            <span wire:loading.remove wire:target="clearFilters">{{ __('livewire/layout.reset_filters') }}</span>
                            <span wire:loading wire:target="clearFilters">{{ __('livewire/layout.clearing') }}...</span>
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
                                        <i class="fas fa-sort ml-1 opacity-50"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('supplier_code')">
                                    {{ __('livewire/suppliers.supplier_code') }}
                                    @if($sortField === 'supplier_code')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 opacity-50"></i>
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
                                        <i class="fas fa-sort ml-1 opacity-50"></i>
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
</div>