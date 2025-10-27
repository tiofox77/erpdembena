<div>
    <div class="py-2 sm:py-4">
        <div class="max-w-full mx-auto px-2 sm:px-4 lg:px-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-warehouse text-blue-600 mr-3"></i>
                    {{ __('livewire/supply-chain/locations.inventory_locations_management') }}
                </h1>
                <button wire:click="openAddModal" 
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 hover:shadow-lg">
                    <i class="fas fa-plus-circle mr-2 animate-pulse"></i>
                    {{ __('livewire/supply-chain/locations.add_location') }}
                </button>
            </div>

            <!-- Cartão de Busca e Filtros -->
            <div class="mb-6 bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 ease-in-out hover:shadow-lg transform hover:scale-[1.01]">
                <!-- Cabeçalho do cartão com gradiente -->
                <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                    <i class="fas fa-filter text-blue-600 mr-2"></i>
                    <h2 class="text-base font-medium text-gray-700">{{ __('messages.filters_and_search') }}</h2>
                </div>
                <!-- Conteúdo do cartão -->
                <div class="p-4">
                    <div class="flex flex-col gap-4">
                        <!-- Campo de busca -->
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-search text-gray-500 mr-1"></i>
                                {{ __('messages.search') }}
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input wire:model.debounce.300ms="search" id="search" 
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out" 
                                    placeholder="{{ __('messages.search_locations') }}" 
                                    type="search">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">{{ __('messages.search_locations') }}</p>
                        </div>
                        
                        <!-- Linha de filtros -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Status -->
                            <div>
                                <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-toggle-on text-gray-500 mr-1"></i>
                                    {{ __('messages.status') }}
                                </label>
                                <select wire:model.live="statusFilter" id="statusFilter" 
                                    class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                    <option value="">{{ __('messages.all') }}</option>
                                    <option value="active">{{ __('messages.active') }}</option>
                                    <option value="inactive">{{ __('messages.inactive') }}</option>
                                </select>
                            </div>
                            
                            <!-- Tipo -->
                            <div>
                                <label for="typeFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-tag text-gray-500 mr-1"></i>
                                    {{ __('messages.type') }}
                                </label>
                                <select wire:model.live="typeFilter" id="typeFilter" 
                                    class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                    <option value="">{{ __('messages.all') }}</option>
                                    <option value="raw_material">{{ __('messages.raw_material_warehouse') }}</option>
                                    <option value="normal">{{ __('messages.normal_warehouse') }}</option>
                                </select>
                            </div>
                            
                            <!-- Registros por página -->
                            <div>
                                <label for="perPage" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-list-ol text-gray-500 mr-1"></i>
                                    {{ __('messages.records_per_page') }}
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
                                class="flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                <i class="fas fa-undo mr-2"></i>
                                <span wire:loading.remove wire:target="resetFilters">{{ __('messages.reset_filters') }}</span>
                                <span wire:loading wire:target="resetFilters" class="inline-flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ __('messages.resetting') }}...
                                </span>
                            </button>
                        </div>
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
                                    <p class="text-sm text-red-600">{{ session('error') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Tabela de Localizações -->
                    <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden transition-all duration-300 ease-in-out hover:shadow-lg">
                        <!-- Cabeçalho da Tabela -->
                        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-white flex items-center">
                                <i class="fas fa-warehouse mr-2"></i>
                                {{ __('livewire/supply-chain/locations.locations_list') }}
                            </h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('name')">
                                            {{ __('livewire/supply-chain/locations.name') }}
                                            <i class="fas fa-sort{{ $sortField === 'name' ? '-' . ($sortDirection === 'asc' ? 'up' : 'down') : '' }} ml-1"></i>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('code')">
                                            {{ __('livewire/supply-chain/locations.code') }}
                                            <i class="fas fa-sort{{ $sortField === 'code' ? '-' . ($sortDirection === 'asc' ? 'up' : 'down') : '' }} ml-1"></i>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('city')">
                                            {{ __('livewire/supply-chain/locations.city') }}
                                            <i class="fas fa-sort{{ $sortField === 'city' ? '-' . ($sortDirection === 'asc' ? 'up' : 'down') : '' }} ml-1"></i>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('livewire/supply-chain/locations.items') }}
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('is_active')">
                                            {{ __('livewire/supply-chain/locations.status') }}
                                            <i class="fas fa-sort{{ $sortField === 'is_active' ? '-' . ($sortDirection === 'asc' ? 'up' : 'down') : '' }} ml-1"></i>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('is_raw_material_warehouse')">
                                            {{ __('livewire/supply-chain/locations.is_raw_material_warehouse') }}
                                            <i class="fas fa-sort{{ $sortField === 'is_raw_material_warehouse' ? '-' . ($sortDirection === 'asc' ? 'up' : 'down') : '' }} ml-1"></i>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('livewire/supply-chain/locations.actions') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($locations as $location)
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $location->name }}</div>
                                                    @if($location->description)
                                                        <div class="text-sm text-gray-500">{{ $location->description }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $location->code }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $location->city }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ $location->inventory_items_count }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $location->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $location->is_active ? __('messages.active') : __('messages.inactive') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $location->is_raw_material_warehouse ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ $location->is_raw_material_warehouse ? __('messages.yes') : __('messages.no') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <button wire:click="view({{ $location->id }})" 
                                                    class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded bg-blue-100 text-blue-700 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                                    <i class="fas fa-eye mr-1"></i>
                                                    {{ __('messages.view') }}
                                                </button>
                                                <button wire:click="openEditModal({{ $location->id }})" 
                                                    class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded bg-indigo-100 text-indigo-700 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                                    <i class="fas fa-edit mr-1"></i>
                                                    {{ __('messages.edit') }}
                                                </button>
                                                <button wire:click="confirmDelete({{ $location->id }})" 
                                                    class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded bg-red-100 text-red-700 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                                    <i class="fas fa-trash mr-1"></i>
                                                    {{ __('messages.delete') }}
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-10 text-center">
                                            <div class="flex flex-col items-center justify-center">
                                                <i class="fas fa-warehouse text-5xl text-gray-300 mb-3"></i>
                                                <h3 class="text-lg font-medium text-gray-900 mb-1">{{ __('messages.no_locations_found') }}</h3>
                                                <p class="text-sm text-gray-500 mb-4">{{ __('messages.try_different_search') }}</p>
                                                <button wire:click="resetFilters" 
                                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                                    <i class="fas fa-redo-alt mr-2"></i>
                                                    {{ __('messages.reset_filters') }}
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                                <div class="text-sm text-gray-600 bg-blue-50 px-3 py-2 rounded-md border border-blue-100 flex items-center">
                                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                    {{ __('messages.showing') }} <span class="font-medium mx-1">{{ $locations->firstItem() ?? 0 }}</span> {{ __('messages.to') }} <span class="font-medium mx-1">{{ $locations->lastItem() ?? 0 }}</span> {{ __('messages.of') }} <span class="font-medium mx-1">{{ $locations->total() }}</span> {{ __('messages.results') }}
                                </div>
                                <div class="pagination-container">
                                    {{ $locations->links() }}
                                </div>
                            </div>
                        </div>
        </div>
    </div>

    <!-- Modal de Adição/Edição de Localização -->
    <div>
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
                <form wire:submit.prevent="save" class="overflow-hidden">
                    <!-- Cabeçalho com gradiente -->
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <i class="fas {{ $editMode ? 'fa-edit' : 'fa-plus-circle' }} mr-2 animate-pulse"></i>
                            {{ $editMode ? __('livewire/supply-chain/locations.edit_location') : __('livewire/supply-chain/locations.add_location') }}
                        </h3>
                        <button type="button" wire:click="closeModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
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
                                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                <h2 class="text-md font-medium text-gray-700">{{ __('messages.basic_information') }}</h2>
                            </div>
                            <div class="p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="col-span-1 md:col-span-2">
                                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-warehouse text-gray-500 mr-1"></i>
                                            {{ __('livewire/supply-chain/locations.location_name') }}*
                                        </label>
                                        <input type="text" id="name" wire:model.defer="location.name" placeholder="{{ __('livewire/supply-chain/locations.enter_location_name') }}" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white">
                                        @error('location.name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label for="location_code" class="block text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-barcode text-gray-500 mr-1"></i>
                                            {{ __('livewire/supply-chain/locations.location_code') }}*
                                        </label>
                                        <input type="text" id="location_code" wire:model.defer="location.location_code" placeholder="{{ __('livewire/supply-chain/locations.enter_location_code') }}" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white">
                                        @error('location.location_code') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Contact Information Section -->
                        <div class="bg-white rounded-lg shadow-sm mb-6 overflow-hidden border border-gray-200">
                            <div class="flex items-center bg-gray-50 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-address-card text-green-500 mr-2"></i>
                                <h2 class="text-md font-medium text-gray-700">{{ __('messages.contact_information') }}</h2>
                            </div>
                            <div class="p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="contact_person" class="block text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-user text-gray-500 mr-1"></i>
                                            {{ __('livewire/supply-chain/locations.contact_person') }}
                                        </label>
                                        <input type="text" id="contact_person" wire:model.defer="location.contact_person" placeholder="{{ __('livewire/supply-chain/locations.enter_contact_person') }}" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white">
                                        @error('location.contact_person') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-phone text-gray-500 mr-1"></i>
                                            {{ __('livewire/supply-chain/locations.phone') }}
                                        </label>
                                        <input type="text" id="phone" wire:model.defer="location.phone" placeholder="{{ __('livewire/supply-chain/locations.enter_phone') }}" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white">
                                        @error('location.phone') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-span-1 md:col-span-2">
                                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-map-marker-alt text-gray-500 mr-1"></i>
                                            {{ __('livewire/supply-chain/locations.address') }}
                                        </label>
                                        <textarea id="address" wire:model.defer="location.address" rows="2" placeholder="{{ __('livewire/supply-chain/locations.enter_address') }}" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white"></textarea>
                                        @error('location.address') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Additional Information Section -->
                        <div class="bg-white rounded-lg shadow-sm mb-6 overflow-hidden border border-gray-200">
                            <div class="flex items-center bg-gray-50 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-sticky-note text-purple-500 mr-2"></i>
                                <h2 class="text-md font-medium text-gray-700">{{ __('messages.additional_information') }}</h2>
                            </div>
                            <div class="p-4">
                                <div class="mb-4">
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                                        <i class="fas fa-comment text-gray-500 mr-1"></i>
                                        {{ __('livewire/supply-chain/locations.notes') }}
                                    </label>
                                    <textarea id="notes" wire:model.defer="location.notes" rows="3" placeholder="{{ __('livewire/supply-chain/locations.enter_notes') }}" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white"></textarea>
                                    @error('location.notes') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="flex items-center mt-4">
                                    <div>
                                        <label for="is_active" class="flex items-center cursor-pointer">
                                            <div class="relative">
                                                <!-- Input escondido -->
                                                <input type="checkbox" wire:model.defer="location.is_active" id="is_active" class="sr-only">
                                                <!-- Track (fundo do toggle) -->
                                                <div class="w-12 h-6 bg-gray-300 rounded-full shadow-inner transition-all duration-300 ease-in-out"></div>
                                                <!-- Dot (bolinha do toggle) -->
                                                <div class="dot absolute w-6 h-6 bg-white rounded-full shadow left-0 top-0 transition-transform duration-300 ease-in-out transform" 
                                                    :class="{'translate-x-6 bg-green-500': $wire.location.is_active, 'bg-white': !$wire.location.is_active}"></div>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="ml-3">
                                        <label for="is_active" class="text-sm font-medium text-gray-700 cursor-pointer">
                                            {{ __('livewire/supply-chain/locations.is_active') }}
                                        </label>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ __('livewire/supply-chain/locations.active_info') }}
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Armazém de Matéria-Prima -->
                                <div class="flex items-center space-x-3 mt-4">
                                    <div>
                                        <label for="is_raw_material_warehouse" class="flex items-center cursor-pointer">
                                            <div class="relative">
                                                <!-- Input escondido -->
                                                <input type="checkbox" wire:model.defer="location.is_raw_material_warehouse" id="is_raw_material_warehouse" class="sr-only">
                                                <!-- Track (fundo do toggle) -->
                                                <div class="w-12 h-6 bg-gray-300 rounded-full shadow-inner transition-all duration-300 ease-in-out"></div>
                                                <!-- Dot (bolinha do toggle) -->
                                                <div class="dot absolute w-6 h-6 bg-white rounded-full shadow left-0 top-0 transition-transform duration-300 ease-in-out transform"
                                                    :class="{'translate-x-6 bg-blue-500': $wire.location.is_raw_material_warehouse, 'bg-white': !$wire.location.is_raw_material_warehouse}"></div>
                                            </div>
                                        </label>
                                    </div>
                                    <div>
                                        <label for="is_raw_material_warehouse" class="text-sm font-medium text-gray-700 cursor-pointer">
                                            {{ __('livewire/supply-chain/locations.is_raw_material_warehouse') }}
                                        </label>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ __('livewire/supply-chain/locations.is_raw_material_warehouse_help') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                        <button type="button" wire:click="closeModal" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                            <i class="fas fa-times mr-2"></i>
                            {{ __('livewire/layout.cancel') }}
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="save">
                                <i class="fas {{ $editMode ? 'fa-save' : 'fa-plus-circle' }} mr-2"></i>
                                {{ $editMode ? __('livewire/layout.update') : __('livewire/layout.save') }}
                            </span>
                            <span wire:loading wire:target="save" class="inline-flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ __('livewire/layout.processing') }}
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <div>
        <div x-data="{ open: @entangle('showConfirmDelete') }" 
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
            <div class="relative top-20 mx-auto p-1 w-full max-w-lg">
                <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" 
                     x-transition:enter="transition ease-out duration-300" 
                     x-transition:enter-start="transform opacity-0 scale-95" 
                     x-transition:enter-end="transform opacity-100 scale-100" 
                     x-transition:leave="transition ease-in duration-200" 
                     x-transition:leave-start="transform opacity-100 scale-100" 
                     x-transition:leave-end="transform opacity-0 scale-95">
                <!-- Cabeçalho com gradiente -->
                <div class="bg-gradient-to-r from-red-600 to-red-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-trash-alt mr-2 animate-pulse"></i>
                        {{ __('livewire/supply-chain/locations.delete_location') }}
                    </h3>
                    <button type="button" wire:click="cancelDelete" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
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
                                {{ __('livewire/supply-chain/locations.delete_location_confirmation') }}
                            </p>
                            
                            @if(isset($deleteLocationName) && $deleteLocationName)
                            <div class="mt-3 p-3 bg-gray-50 rounded-md border border-gray-200">
                                <h4 class="text-sm font-medium text-gray-700 mb-1">{{ __('livewire/supply-chain/locations.location_name') }}:</h4>
                                <p class="text-sm font-semibold text-gray-900">{{ $deleteLocationName }}</p>
                            </div>
                            @endif
                            
                            @if($deleteHasItems)
                            <div class="mt-3 p-3 bg-red-50 rounded-md border border-red-200">
                                <p class="text-sm text-red-600 flex items-start">
                                    <i class="fas fa-exclamation-circle mr-2 mt-0.5 text-red-500"></i>
                                    <span>{{ __('livewire/supply-chain/locations.delete_location_has_items_warning') }}</span>
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
                        {{ __('messages.delete') }}
                    </button>
                    <button 
                        type="button" 
                        wire:click="cancelDelete" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-150"
                    >
                        <i class="fas fa-times mr-2"></i>
                        {{ __('messages.cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de Visualização de Localização -->
    <div>
        <div x-data="{ open: @entangle('showViewModal') }" 
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
                        <i class="fas fa-eye mr-2 animate-pulse"></i>
                        {{ __('livewire/supply-chain/locations.view_location_details') }}
                    </h3>
                    <button type="button" wire:click="closeViewModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 max-h-[calc(100vh-200px)] overflow-y-auto">
                    @if(isset($viewLocation) && $viewLocation)
                        <!-- Basic Information Section -->
                        <div class="bg-white rounded-lg shadow-sm mb-6 overflow-hidden border border-gray-200">
                            <div class="flex items-center bg-gray-50 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                <h2 class="text-md font-medium text-gray-700">{{ __('messages.basic_information') }}</h2>
                            </div>
                            <div class="p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">{{ __('livewire/supply-chain/locations.location_name') }}</h4>
                                        <p class="text-sm font-medium text-gray-900">{{ $viewLocation->name }}</p>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">{{ __('messages.location_code') }}</h4>
                                        <p class="text-sm font-medium text-gray-900">{{ $viewLocation->location_code }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Contact Information Section -->
                        <div class="bg-white rounded-lg shadow-sm mb-6 overflow-hidden border border-gray-200">
                            <div class="flex items-center bg-gray-50 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-address-card text-green-500 mr-2"></i>
                                <h2 class="text-md font-medium text-gray-700">{{ __('messages.contact_information') }}</h2>
                            </div>
                            <div class="p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">{{ __('livewire/supply-chain/locations.contact_person') }}</h4>
                                        <p class="text-sm font-medium text-gray-900">{{ $viewLocation->contact_person ?: '--' }}</p>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">{{ __('livewire/supply-chain/locations.phone') }}</h4>
                                        <p class="text-sm font-medium text-gray-900">{{ $viewLocation->phone ?: '--' }}</p>
                                    </div>
                                    <div class="col-span-1 md:col-span-2">
                                        <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">{{ __('livewire/supply-chain/locations.address') }}</h4>
                                        <p class="text-sm font-medium text-gray-900">{{ $viewLocation->address ?: '--' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Inventory Information Section -->
                        <div class="bg-white rounded-lg shadow-sm mb-6 overflow-hidden border border-gray-200">
                            <div class="flex items-center bg-gray-50 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-boxes text-amber-500 mr-2"></i>
                                <h2 class="text-md font-medium text-gray-700">{{ __('messages.inventory_information') }}</h2>
                            </div>
                            <div class="p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">{{ __('messages.inventory_items') }}</h4>
                                        <p class="text-sm font-medium text-gray-900">{{ $viewLocation->inventory_count ?: '0' }}</p>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">{{ __('messages.inventory_value') }}</h4>
                                        <p class="text-sm font-medium text-gray-900">{{ number_format($viewLocation->inventory_value ?: 0, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Additional Information Section -->
                        <div class="bg-white rounded-lg shadow-sm mb-6 overflow-hidden border border-gray-200">
                            <div class="flex items-center bg-gray-50 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-sticky-note text-purple-500 mr-2"></i>
                                <h2 class="text-md font-medium text-gray-700">{{ __('messages.additional_information') }}</h2>
                            </div>
                            <div class="p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">{{ __('livewire/supply-chain/locations.notes') }}</h4>
                                        <p class="text-sm font-medium text-gray-900">{{ $viewLocation->notes ?: '--' }}</p>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">{{ __('livewire/supply-chain/locations.raw_material_warehouse') }}</h4>
                                        <div>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $viewLocation->is_raw_material_warehouse ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ $viewLocation->is_raw_material_warehouse ? __('messages.yes') : __('messages.no') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-6">
                            <i class="fas fa-exclamation-circle text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-500">{{ __('messages.location_not_found') }}</p>
                        </div>
                    @endif
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                    @if(isset($viewLocation) && $viewLocation)
                    <button 
                        type="button" 
                        wire:click="openEditModal({{ $viewLocation->id }})" 
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-150"
                    >
                        <i class="fas fa-edit mr-2"></i>
                        {{ __('messages.edit') }}
                    </button>
                    @endif
                    <button 
                        type="button" 
                        wire:click="closeViewModal" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-150"
                    >
                        <i class="fas fa-times mr-2"></i>
                        {{ __('messages.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
