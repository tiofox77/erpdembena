<div>
    <div x-data="{ open: @entangle('showModal') }" x-show="open" x-cloak class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50" role="dialog" aria-modal="true" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="relative top-20 mx-auto p-1 w-full max-w-4xl">
            <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95">
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <i class="fas {{ $editMode ? 'fa-edit' : 'fa-plus-circle' }} mr-2 animate-pulse"></i>
                        {{ $editMode ? __('livewire/products.edit_product') : __('livewire/products.add_product') }}
                    </h3>
                    <button type="button" wire:click="closeModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form wire:submit.prevent="save">
                    <!-- Modal Content -->
                    <div class="overflow-y-auto p-6 max-h-[calc(100vh-200px)]">
                        <!-- Mensagens de erro de validação -->
                        @if ($errors->any())
                        <div class="mb-4 rounded-md bg-red-50 p-4 border-l-4 border-red-400">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-red-500 text-lg"></i>
                                </div>
                                <div class="ml-3 w-full">
                                    <h3 class="text-sm font-medium text-red-800">{{ __('messages.validation_errors') }}</h3>
                                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                <button type="button" onclick="this.parentElement.parentElement.style.display='none'" class="ml-auto flex-shrink-0 text-red-500 hover:text-red-700">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Navegação por abas -->
                        <div class="mb-6 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 transition-all duration-200 ease-in-out hover:shadow-md">
                            <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-layer-group text-blue-600 mr-2"></i>
                                <h2 class="text-base font-medium text-gray-700">{{ __('livewire/products.product_sections') }}</h2>
                            </div>
                            <div class="p-3 flex flex-wrap gap-2">
                                <button type="button" 
                                    wire:click="setTab('general')" 
                                    class="{{ $currentTab == 'general' ? 'bg-blue-100 border-blue-500 text-blue-700' : 'bg-gray-50 hover:bg-gray-100 text-gray-700' }} px-4 py-2 rounded-md border focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center transition-all duration-200 ease-in-out transform hover:scale-105">
                                    <i class="fas fa-info-circle mr-2 transition-all duration-200 {{ $currentTab == 'general' ? 'text-blue-600 animate-pulse' : '' }}"></i>{{ __('livewire/products.general_info') }}
                                </button>
                                <button type="button" 
                                    wire:click="setTab('inventory')" 
                                    class="{{ $currentTab == 'inventory' ? 'bg-blue-100 border-blue-500 text-blue-700' : 'bg-gray-50 hover:bg-gray-100 text-gray-700' }} px-4 py-2 rounded-md border focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center transition-all duration-200 ease-in-out transform hover:scale-105">
                                    <i class="fas fa-warehouse mr-2 transition-all duration-200 {{ $currentTab == 'inventory' ? 'text-blue-600 animate-pulse' : '' }}"></i>{{ __('livewire/products.inventory_info') }}
                                </button>
                                <button type="button" 
                                    wire:click="setTab('dimensions')" 
                                    class="{{ $currentTab == 'dimensions' ? 'bg-blue-100 border-blue-500 text-blue-700' : 'bg-gray-50 hover:bg-gray-100 text-gray-700' }} px-4 py-2 rounded-md border focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center transition-all duration-200 ease-in-out transform hover:scale-105">
                                    <i class="fas fa-ruler-combined mr-2 transition-all duration-200 {{ $currentTab == 'dimensions' ? 'text-blue-600 animate-pulse' : '' }}"></i>{{ __('livewire/products.dimensions_info') }}
                                </button>
                                <button type="button" 
                                    wire:click="setTab('suppliers')" 
                                    class="{{ $currentTab == 'suppliers' ? 'bg-blue-100 border-blue-500 text-blue-700' : 'bg-gray-50 hover:bg-gray-100 text-gray-700' }} px-4 py-2 rounded-md border focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center transition-all duration-200 ease-in-out transform hover:scale-105">
                                    <i class="fas fa-truck-loading mr-2 transition-all duration-200 {{ $currentTab == 'suppliers' ? 'text-blue-600 animate-pulse' : '' }}"></i>{{ __('livewire/products.suppliers_info') }}
                                </button>
                            </div>
                        </div>

                        <!-- Conteúdo das abas -->
                        <div class="space-y-6" x-data="{ activeTab: @entangle('currentTab') }" x-cloak>
                            <!-- Aba de Informações Gerais -->
                            <div x-show="activeTab == 'general'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95">
                                <!-- Cartão de Informações Básicas -->
                                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                                    <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                        <h3 class="text-base font-medium text-gray-700">{{ __('livewire/products.basic_information') }}</h3>
                                    </div>
                                    <div class="p-4">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                                    <i class="fas fa-tag text-gray-500 mr-1"></i>
                                                    {{ __('livewire/products.product_name') }} <span class="text-red-500">*</span>
                                                </label>
                                                <input type="text" wire:model="name" id="name" 
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm"
                                                    placeholder="{{ __('livewire/products.enter_product_name') }}">
                                                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                            </div>
                                            
                                            <div>
                                                <label for="sku" class="block text-sm font-medium text-gray-700 mb-1">
                                                    <i class="fas fa-barcode text-gray-500 mr-1"></i>
                                                    {{ __('livewire/products.sku') }} <span class="text-red-500">*</span>
                                                </label>
                                                <input type="text" wire:model="sku" id="sku" 
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm"
                                                    placeholder="{{ __('livewire/products.enter_sku') }}">
                                                @error('sku') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                            </div>
                                            
                                            <div>
                                                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">
                                                    {{ __('livewire/products.category') }}
                                                </label>
                                                <select id="category_id" wire:model="category_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                    <option value="">{{ __('livewire/layout.select_option') }}</option>
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('category_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                            </div>
                                            
                                            <div>
                                                <label for="product_type" class="block text-sm font-medium text-gray-700 mb-1">
                                                    {{ __('livewire/products.product_type') }}
                                                </label>
                                                <select wire:model="product_type" id="product_type" 
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm">
                                                    <option value="finished_product">{{ __('livewire/products.finished_product') }}</option>
                                                    <option value="raw_material">{{ __('livewire/products.raw_material') }}</option>
                                                </select>
                                                @error('product_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                            </div>
                                            
                                            <div>
                                                <label for="barcode" class="block text-sm font-medium text-gray-700 mb-1">
                                                    <i class="fas fa-qrcode text-gray-500 mr-1"></i>
                                                    {{ __('livewire/products.barcode') }}
                                                </label>
                                                <input type="text" wire:model="barcode" id="barcode" 
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm"
                                                    placeholder="{{ __('livewire/products.enter_barcode') }}">
                                                @error('barcode') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Cartão de Descrição -->
                                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden mt-6">
                                    <div class="flex items-center bg-gradient-to-r from-green-50 to-green-100 px-4 py-3 border-b border-gray-200">
                                        <i class="fas fa-align-left text-green-600 mr-2"></i>
                                        <h3 class="text-base font-medium text-gray-700">{{ __('livewire/products.description_details') }}</h3>
                                    </div>
                                    <div class="p-4">
                                        <div>
                                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                                <i class="fas fa-file-alt text-gray-500 mr-1"></i>
                                                {{ __('livewire/products.description') }}
                                            </label>
                                            <textarea wire:model="description" id="description" rows="3" 
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm"
                                                placeholder="{{ __('livewire/products.enter_description') }}"></textarea>
                                            @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Cartão de Preços -->
                                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden mt-6">
                                    <div class="flex items-center bg-gradient-to-r from-purple-50 to-purple-100 px-4 py-3 border-b border-gray-200">
                                        <i class="fas fa-money-bill-wave text-purple-600 mr-2"></i>
                                        <h3 class="text-base font-medium text-gray-700">{{ __('livewire/products.pricing_information') }}</h3>
                                    </div>
                                    <div class="p-4">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label for="unit_price" class="block text-sm font-medium text-gray-700 mb-1">
                                                    <i class="fas fa-tag text-gray-500 mr-1"></i>
                                                    {{ __('livewire/products.unit_price') }} <span class="text-red-500">*</span>
                                                </label>
                                                <div class="mt-1 relative rounded-md shadow-sm">
                                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <span class="text-gray-500 sm:text-sm">$</span>
                                                    </div>
                                                    <input type="number" step="0.01" wire:model="unit_price" id="unit_price" 
                                                        class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm"
                                                        placeholder="0.00">
                                                </div>
                                                @error('unit_price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                            </div>
                                            
                                            <div>
                                                <label for="cost_price" class="block text-sm font-medium text-gray-700 mb-1">
                                                    <i class="fas fa-dollar-sign text-gray-500 mr-1"></i>
                                                    {{ __('livewire/products.cost_price') }} <span class="text-red-500">*</span>
                                                </label>
                                                <div class="mt-1 relative rounded-md shadow-sm">
                                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <span class="text-gray-500 sm:text-sm">$</span>
                                                    </div>
                                                    <input type="number" step="0.01" wire:model="cost_price" id="cost_price" 
                                                        class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm"
                                                        placeholder="0.00">
                                                </div>
                                                @error('cost_price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                            </div>
                                            
                                            <div>
                                                <label for="unit_of_measure" class="block text-sm font-medium text-gray-700 mb-1">
                                                    <i class="fas fa-ruler text-gray-500 mr-1"></i>
                                                    {{ __('livewire/products.unit_of_measure') }} <span class="text-red-500">*</span>
                                                </label>
                                                <select wire:model="unit_of_measure" id="unit_of_measure" 
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm">
                                                    <option value="">{{ __('livewire/products.select_unit') }}</option>
                                                    @foreach($unitTypes as $unitType)
                                                        <option value="{{ $unitType->symbol }}">{{ $unitType->formattedName }}</option>
                                                    @endforeach
                                                </select>
                                                @error('unit_of_measure') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Cartão de Imagem e Status -->
                                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden mt-6">
                                    <div class="flex items-center bg-gradient-to-r from-yellow-50 to-yellow-100 px-4 py-3 border-b border-gray-200">
                                        <i class="fas fa-image text-yellow-600 mr-2"></i>
                                        <h3 class="text-base font-medium text-gray-700">{{ __('livewire/products.media_and_status') }}</h3>
                                    </div>
                                    <div class="p-4">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div>
                                                <label for="temp_image" class="block text-sm font-medium text-gray-700 mb-2">
                                                    <i class="fas fa-camera text-gray-500 mr-1"></i>
                                                    {{ __('livewire/products.product_image') }}
                                                </label>
                                                <div class="flex items-center">
                                                    <div class="space-y-2">
                                                        <input type="file" wire:model="temp_image" id="temp_image" accept="image/*"
                                                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                                        @error('temp_image') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                                                        
                                                        <div class="mt-2">
                                                            @if ($temp_image)
                                                                <div class="relative group w-32 h-32">
                                                                    <img src="{{ $temp_image->temporaryUrl() }}" class="w-full h-full object-cover rounded-md border border-gray-300">
                                                                    <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity duration-200 rounded-md flex items-center justify-center">
                                                                        <button type="button" wire:click="$set('temp_image', null)" class="text-white hover:text-red-300 focus:outline-none">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            @elseif($image)
                                                                <div class="relative group w-32 h-32">
                                                                    <img src="{{ asset('storage/' . $image) }}" class="w-full h-full object-cover rounded-md border border-gray-300">
                                                                    <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity duration-200 rounded-md flex items-center justify-center">
                                                                        <button type="button" wire:click="removeImage" class="text-white hover:text-red-300 focus:outline-none">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <div class="w-32 h-32 border-2 border-dashed border-gray-300 rounded-md flex items-center justify-center">
                                                                    <i class="fas fa-image text-gray-400 text-3xl"></i>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                    <i class="fas fa-toggle-on text-gray-500 mr-1"></i>
                                                    {{ __('livewire/products.status') }}
                                                </label>
                                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                                    <div class="flex items-center">
                                                        <div>
                                                            <label for="is_active" class="flex items-center cursor-pointer">
                                                                <div class="relative">
                                                                    <!-- Hidden checkbox -->
                                                                    <input type="checkbox" id="is_active" 
                                                                        {{ $is_active ? 'checked' : '' }}
                                                                        wire:click="$toggle('is_active')"
                                                                        class="sr-only">
                                                                    <!-- Track (toggle background) -->
                                                                    <div class="w-12 h-6 bg-gray-300 rounded-full shadow-inner transition-all duration-300 ease-in-out"></div>
                                                                    <!-- Dot (toggle handle) -->
                                                                    <div class="dot absolute w-6 h-6 bg-white rounded-full shadow left-0 top-0 transition-transform duration-300 ease-in-out transform {{ $is_active ? 'translate-x-6 bg-green-500' : 'bg-white' }}"></div>
                                                                </div>
                                                            </label>
                                                        </div>
                                                        <div class="ml-3">
                                                            <label for="is_active" class="text-sm font-medium text-gray-700 cursor-pointer">
                                                                {{ __('livewire/products.is_active') }}
                                                            </label>
                                                            <p class="text-xs text-gray-500 mt-1">
                                                                {{ __('livewire/products.active_product_info') }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Aba de Inventário -->
                            <div x-show="activeTab == 'inventory'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95">
                                <!-- Cartão de Controle de Estoque -->
                                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                                    <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                                        <i class="fas fa-warehouse text-blue-600 mr-2"></i>
                                        <h3 class="text-base font-medium text-gray-700">{{ __('livewire/products.stock_control') }}</h3>
                                    </div>
                                    <div class="p-4">
                                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-4">
                                            <div class="flex items-center">
                                                <div>
                                                    <label for="is_stockable" class="flex items-center cursor-pointer">
                                                        <div class="relative">
                                                            <!-- Input escondido -->
                                                            <input type="checkbox" wire:model="is_stockable" id="is_stockable" class="sr-only">
                                                            <!-- Track (fundo do toggle) -->
                                                            <div class="w-12 h-6 bg-gray-300 rounded-full shadow-inner transition-all duration-300 ease-in-out"></div>
                                                            <!-- Dot (bolinha do toggle) -->
                                                            <div class="dot absolute w-6 h-6 bg-white rounded-full shadow left-0 top-0 transition-transform duration-300 ease-in-out transform" 
                                                                :class="{'translate-x-6 bg-blue-500': $wire.is_stockable, 'bg-white': !$wire.is_stockable}"></div>
                                                        </div>
                                                    </label>
                                                </div>
                                                <div class="ml-3">
                                                    <label for="is_stockable" class="text-sm font-medium text-gray-700 cursor-pointer">
                                                        {{ __('livewire/products.is_stockable') }}
                                                    </label>
                                                    <p class="text-xs text-gray-500 mt-1">
                                                        {{ __('livewire/products.stockable_info') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label for="min_stock_level" class="block text-sm font-medium text-gray-700 mb-1">
                                                    <i class="fas fa-battery-quarter text-gray-500 mr-1"></i>
                                                    {{ __('livewire/products.min_stock_level') }}
                                                </label>
                                                <input type="number" wire:model="min_stock_level" id="min_stock_level" min="0" 
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm"
                                                    placeholder="0">
                                                @error('min_stock_level') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                            </div>
                                            
                                            <div>
                                                <label for="reorder_point" class="block text-sm font-medium text-gray-700 mb-1">
                                                    <i class="fas fa-shopping-cart text-gray-500 mr-1"></i>
                                                    {{ __('livewire/products.reorder_point') }}
                                                </label>
                                                <input type="number" wire:model="reorder_point" id="reorder_point" min="0"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm"
                                                    placeholder="0">
                                                @error('reorder_point') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Cartão de Tempo de Entrega e Armazenamento -->
                                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden mt-6">
                                    <div class="flex items-center bg-gradient-to-r from-green-50 to-green-100 px-4 py-3 border-b border-gray-200">
                                        <i class="fas fa-clock text-green-600 mr-2"></i>
                                        <h3 class="text-base font-medium text-gray-700">{{ __('livewire/products.supply_information') }}</h3>
                                    </div>
                                    <div class="p-4">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label for="lead_time_days" class="block text-sm font-medium text-gray-700 mb-1">
                                                    <i class="fas fa-truck-loading text-gray-500 mr-1"></i>
                                                    {{ __('livewire/products.lead_time_days') }}
                                                </label>
                                                <div class="mt-1 relative rounded-md shadow-sm">
                                                    <input type="number" wire:model="lead_time_days" id="lead_time_days" min="0"
                                                        class="block w-full pr-12 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm"
                                                        placeholder="0">
                                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                        <span class="text-gray-500 sm:text-sm">{{ __('livewire/products.days') }}</span>
                                                    </div>
                                                </div>
                                                @error('lead_time_days') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                            </div>
                                            
                                            <div>
                                                <label for="location" class="block text-sm font-medium text-gray-700 mb-1">
                                                    <i class="fas fa-map-marker-alt text-gray-500 mr-1"></i>
                                                    {{ __('livewire/products.storage_location') }}
                                                </label>
                                                <input type="text" wire:model="location" id="location" 
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm"
                                                    placeholder="{{ __('livewire/products.enter_storage_location') }}">
                                                @error('location') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Cartão de Impostos -->
                                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden mt-6">
                                    <div class="flex items-center bg-gradient-to-r from-yellow-50 to-yellow-100 px-4 py-3 border-b border-gray-200">
                                        <i class="fas fa-file-invoice-dollar text-yellow-600 mr-2"></i>
                                        <h3 class="text-base font-medium text-gray-700">{{ __('livewire/products.tax_information') }}</h3>
                                    </div>
                                    <div class="p-4">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label for="tax_type" class="block text-sm font-medium text-gray-700 mb-1">
                                                    <i class="fas fa-receipt text-gray-500 mr-1"></i>
                                                    {{ __('livewire/products.tax_type') }}
                                                </label>
                                                <select wire:model="tax_type" id="tax_type" 
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm">
                                                    <option value="standard">{{ __('livewire/products.tax_standard') }}</option>
                                                    <option value="reduced">{{ __('livewire/products.tax_reduced') }}</option>
                                                    <option value="exempt">{{ __('livewire/products.tax_exempt') }}</option>
                                                </select>
                                                @error('tax_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                            </div>
                                            
                                            <div>
                                                <label for="tax_rate" class="block text-sm font-medium text-gray-700 mb-1">
                                                    <i class="fas fa-percent text-gray-500 mr-1"></i>
                                                    {{ __('livewire/products.tax_rate') }}
                                                </label>
                                                <div class="mt-1 relative rounded-md shadow-sm">
                                                    <input type="number" step="0.01" wire:model="tax_rate" id="tax_rate" min="0"
                                                        class="block w-full pr-12 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm"
                                                        placeholder="0.00">
                                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                        <span class="text-gray-500 sm:text-sm">%</span>
                                                    </div>
                                                </div>
                                                @error('tax_rate') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Aba de Dimensões -->
                            <div x-show="activeTab == 'dimensions'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95">
                                <!-- Cartão de Dimensões Físicas -->
                                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                                    <div class="flex items-center bg-gradient-to-r from-purple-50 to-purple-100 px-4 py-3 border-b border-gray-200">
                                        <i class="fas fa-ruler-combined text-purple-600 mr-2"></i>
                                        <h3 class="text-base font-medium text-gray-700">{{ __('livewire/products.physical_dimensions') }}</h3>
                                    </div>
                                    <div class="p-4">
                                        <div class="bg-blue-50 p-4 rounded-md mb-4 border-l-4 border-blue-400">
                                            <div class="flex">
                                                <div class="flex-shrink-0">
                                                    <i class="fas fa-info-circle text-blue-600"></i>
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-sm text-blue-700">
                                                        {{ __('livewire/products.dimensions_info') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                            <div>
                                                <label for="weight" class="block text-sm font-medium text-gray-700 mb-1">
                                                    <i class="fas fa-weight-hanging text-gray-500 mr-1"></i>
                                                    {{ __('livewire/products.weight') }}
                                                </label>
                                                <div class="mt-1 relative rounded-md shadow-sm">
                                                    <input type="number" step="0.01" wire:model="weight" id="weight" min="0"
                                                        class="block w-full pr-12 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm"
                                                        placeholder="0.00">
                                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                        <span class="text-gray-500 sm:text-sm">kg</span>
                                                    </div>
                                                </div>
                                                @error('weight') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                            </div>
                                            
                                            <div>
                                                <label for="width" class="block text-sm font-medium text-gray-700 mb-1">
                                                    <i class="fas fa-arrows-alt-h text-gray-500 mr-1"></i>
                                                    {{ __('livewire/products.width') }}
                                                </label>
                                                <div class="mt-1 relative rounded-md shadow-sm">
                                                    <input type="number" step="0.01" wire:model="width" id="width" min="0"
                                                        class="block w-full pr-12 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm"
                                                        placeholder="0.00">
                                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                        <span class="text-gray-500 sm:text-sm">cm</span>
                                                    </div>
                                                </div>
                                                @error('width') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                            </div>
                                            
                                            <div>
                                                <label for="height" class="block text-sm font-medium text-gray-700 mb-1">
                                                    <i class="fas fa-arrows-alt-v text-gray-500 mr-1"></i>
                                                    {{ __('livewire/products.height') }}
                                                </label>
                                                <div class="mt-1 relative rounded-md shadow-sm">
                                                    <input type="number" step="0.01" wire:model="height" id="height" min="0"
                                                        class="block w-full pr-12 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm"
                                                        placeholder="0.00">
                                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                        <span class="text-gray-500 sm:text-sm">cm</span>
                                                    </div>
                                                </div>
                                                @error('height') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                            </div>
                                            
                                            <div>
                                                <label for="depth" class="block text-sm font-medium text-gray-700 mb-1">
                                                    <i class="fas fa-cube text-gray-500 mr-1"></i>
                                                    {{ __('livewire/products.depth') }}
                                                </label>
                                                <div class="mt-1 relative rounded-md shadow-sm">
                                                    <input type="number" step="0.01" wire:model="depth" id="depth" min="0"
                                                        class="block w-full pr-12 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm"
                                                        placeholder="0.00">
                                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                        <span class="text-gray-500 sm:text-sm">cm</span>
                                                    </div>
                                                </div>
                                                @error('depth') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Aba de Fornecedores -->
                            <div x-show="activeTab == 'suppliers'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95">
                                <!-- Cartão de Informações de Fornecedor -->
                                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                                    <div class="flex items-center bg-gradient-to-r from-green-50 to-green-100 px-4 py-3 border-b border-gray-200">
                                        <i class="fas fa-truck-loading text-green-600 mr-2"></i>
                                        <h3 class="text-base font-medium text-gray-700">{{ __('livewire/products.supplier_information') }}</h3>
                                    </div>
                                    <div class="p-4">
                                        <div class="bg-gray-50 p-4 rounded-md mb-6 border-l-4 border-blue-400">
                                            <div class="flex">
                                                <div class="flex-shrink-0">
                                                    <i class="fas fa-info-circle text-blue-600"></i>
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-sm text-gray-700">
                                                        {{ __('livewire/products.supplier_selection_info') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="space-y-4">
                                            <div>
                                                <label for="primary_supplier_id" class="block text-sm font-medium text-gray-700 mb-1">
                                                    <i class="fas fa-building text-gray-500 mr-1"></i>
                                                    {{ __('livewire/products.primary_supplier') }}
                                                </label>
                                                <select wire:model="primary_supplier_id" id="primary_supplier_id" 
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm">
                                                    <option value="">{{ __('livewire/products.select_supplier') }}</option>
                                                    @foreach($suppliers as $supplier)
                                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('primary_supplier_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                            </div>
                                            
                                            <div class="border-t border-gray-200 pt-4 mt-6">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 text-yellow-500">
                                                        <i class="fas fa-lightbulb"></i>
                                                    </div>
                                                    <div class="ml-3">
                                                        <p class="text-sm text-gray-600 italic">
                                                            {{ __('livewire/products.additional_suppliers_future') }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                        <button type="button" wire:click="closeModal" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                            <i class="fas fa-times mr-2"></i>
                            {{ __('livewire/layout.cancel') }}
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed">
                            <span wire:loading.remove>
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
</div>