{{-- Modal de Adicionar/Editar Fornecedor --}}
@if($showModal)
<div 
    x-data="{ show: @entangle('showModal') }"
    x-show="show"
    x-cloak
    x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in duration-200"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 overflow-y-auto" 
    aria-labelledby="modal-title" 
    role="dialog" 
    aria-modal="true"
>
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div 
            x-show="show"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
            aria-hidden="true"
        ></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div 
            x-show="show"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full"
        >
            {{-- Cabeçalho do Modal --}}
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row justify-between">
                <h3 class="text-lg leading-6 font-medium text-white" id="modal-title">
                    <i class="fas {{ $supplier_id ? 'fa-edit' : 'fa-plus-circle' }} mr-2"></i>
                    {{ $supplier_id ? __('livewire/suppliers.edit_supplier') : __('livewire/suppliers.add_supplier') }}
                </h3>
                <button type="button" wire:click="closeModal" class="text-white hover:text-gray-200 transition-colors duration-150">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 max-h-[70vh] overflow-y-auto">
                @if ($errors->any())
                <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                {{ __('livewire/suppliers.form_errors', ['count' => $errors->count()]) }}
                            </h3>
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
                    {{-- Informações Básicas --}}
                    <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                            <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                            <h2 class="text-base font-medium text-gray-700">{{ __('livewire/suppliers.basic_info') }}</h2>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Nome do Fornecedor --}}
                                <div class="col-span-2">
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                        <i class="fas fa-building text-gray-500 mr-1"></i>
                                        {{ __('livewire/suppliers.supplier_name') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="name" wire:model="name"
                                        class="block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors duration-200"
                                        placeholder="{{ __('livewire/suppliers.supplier_name_placeholder') }}">
                                    @error('name') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                    @enderror
                                </div>
                                
                                {{-- Código do Fornecedor --}}
                                <div>
                                    <label for="supplier_code" class="block text-sm font-medium text-gray-700 mb-1">
                                        <i class="fas fa-barcode text-gray-500 mr-1"></i>
                                        {{ __('livewire/suppliers.supplier_code') }}
                                    </label>
                                    <input type="text" id="code" wire:model="code"
                                        class="block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors duration-200"
                                        placeholder="{{ __('livewire/suppliers.supplier_code_placeholder') }}">
                                    @error('code') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                    @enderror
                                </div>
                                
                                {{-- ID Fiscal --}}
                                <div>
                                    <label for="tax_id" class="block text-sm font-medium text-gray-700 mb-1">
                                        <i class="fas fa-id-card text-gray-500 mr-1"></i>
                                        {{ __('livewire/suppliers.tax_id') }}
                                    </label>
                                    <input type="text" id="tax_id" wire:model="tax_id"
                                        class="block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors duration-200"
                                        placeholder="{{ __('livewire/suppliers.tax_id_placeholder') }}">
                                    @error('tax_id') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                    @enderror
                                </div>
                                
                                {{-- Status --}}
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                        <i class="fas fa-toggle-on text-gray-500 mr-1"></i>
                                        {{ __('livewire/suppliers.status') }}
                                    </label>
                                    <select id="status" wire:model="status"
                                        class="block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors duration-200">
                                        <option value="active">{{ __('livewire/suppliers.active') }}</option>
                                        <option value="inactive">{{ __('livewire/suppliers.inactive') }}</option>
                                    </select>
                                    @error('status') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                    @enderror
                                </div>
                                
                                {{-- Category --}}
                                <div>
                                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">
                                        <i class="fas fa-tags text-gray-500 mr-1"></i>
                                        {{ __('supplier.category') }}
                                    </label>
                                    <select id="category_id" wire:model="category_id"
                                        class="block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors duration-200">
                                        <option value="">{{ __('livewire/suppliers.select_category') }}</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_id') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Informações de Contato --}}
                    <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                            <i class="fas fa-address-card text-blue-600 mr-2"></i>
                            <h2 class="text-base font-medium text-gray-700">{{ __('livewire/suppliers.contact_information') }}</h2>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Contato --}}
                                <div>
                                    <label for="contact_person" class="block text-sm font-medium text-gray-700 mb-1">
                                        <i class="fas fa-user text-gray-500 mr-1"></i>
                                        {{ __('livewire/suppliers.contact_person') }}
                                    </label>
                                    <input type="text" id="contact_person" wire:model="contact_person"
                                        class="block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors duration-200"
                                        placeholder="{{ __('livewire/suppliers.contact_person_placeholder') }}">
                                    @error('contact_person') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                    @enderror
                                </div>
                                
                                {{-- Cargo --}}
                                <div>
                                    <label for="position" class="block text-sm font-medium text-gray-700 mb-1">
                                        <i class="fas fa-id-badge text-gray-500 mr-1"></i>
                                        {{ __('livewire/suppliers.position') }}
                                    </label>
                                    <input type="text" id="position" wire:model="position"
                                        class="block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors duration-200"
                                        placeholder="{{ __('livewire/suppliers.position_placeholder') }}">
                                    @error('position') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                    @enderror
                                </div>
                                
                                {{-- Email --}}
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                        <i class="fas fa-envelope text-gray-500 mr-1"></i>
                                        {{ __('livewire/suppliers.email') }}
                                    </label>
                                    <input type="email" id="email" wire:model="email"
                                        class="block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors duration-200"
                                        placeholder="{{ __('livewire/suppliers.email_placeholder') }}">
                                    @error('email') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                    @enderror
                                </div>
                                
                                {{-- Telefone --}}
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                                        <i class="fas fa-phone text-gray-500 mr-1"></i>
                                        {{ __('livewire/suppliers.phone') }}
                                    </label>
                                    <input type="text" id="phone" wire:model="phone"
                                        class="block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors duration-200"
                                        placeholder="{{ __('livewire/suppliers.phone_placeholder') }}">
                                    @error('phone') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                    @enderror
                                </div>
                                
                                {{-- Site --}}
                                <div class="col-span-2">
                                    <label for="website" class="block text-sm font-medium text-gray-700 mb-1">
                                        <i class="fas fa-globe text-gray-500 mr-1"></i>
                                        {{ __('livewire/suppliers.website') }}
                                    </label>
                                    <input type="url" id="website" wire:model="website"
                                        class="block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors duration-200"
                                        placeholder="{{ __('livewire/suppliers.website_placeholder') }}">
                                    @error('website') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Informações de Endereço --}}
                    <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                            <i class="fas fa-map-marker-alt text-blue-600 mr-2"></i>
                            <h2 class="text-base font-medium text-gray-700">{{ __('livewire/suppliers.address_info') }}</h2>
                        </div>
                        <div class="p-4">
                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-map text-gray-500 mr-1"></i>
                                    {{ __('livewire/suppliers.address') }}
                                </label>
                                <textarea id="address" wire:model="address" rows="3"
                                    class="block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors duration-200"
                                    placeholder="{{ __('livewire/suppliers.address_placeholder') }}"></textarea>
                                @error('address') 
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Notas e Descrição --}}
                    <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                            <i class="fas fa-sticky-note text-blue-600 mr-2"></i>
                            <h2 class="text-base font-medium text-gray-700">{{ __('livewire/suppliers.notes_and_description') }}</h2>
                        </div>
                        <div class="p-4">
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-comment text-gray-500 mr-1"></i>
                                    {{ __('livewire/suppliers.notes') }}
                                </label>
                                <textarea id="notes" wire:model="notes" rows="3"
                                    class="block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors duration-200"
                                    placeholder="{{ __('livewire/suppliers.notes_placeholder') }}"></textarea>
                                @error('notes') 
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                @enderror
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" wire:click="save"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200 transform hover:scale-105">
                    <i class="fas fa-save mr-2"></i>
                    <span wire:loading.remove wire:target="save">{{ __('livewire/layout.save') }}</span>
                    <span wire:loading wire:target="save">{{ __('livewire/layout.saving') }}...</span>
                </button>
                <button type="button" wire:click="closeModal"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                    <i class="fas fa-times mr-2"></i>
                    {{ __('livewire/layout.cancel') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endif
