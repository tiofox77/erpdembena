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
                        {{ $editMode ? __('messages.edit_line') : __('messages.add_line') }}
                    </h3>
                    <button type="button" wire:click="closeModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <!-- Corpo do modal -->
                <form wire:submit.prevent="save">
                    <div class="p-6 space-y-6">
                        <!-- Cartão de Informações Básicas -->
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                            <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                <h3 class="text-base font-medium text-gray-700">{{ __('messages.basic_information') }}</h3>
                            </div>
                            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Código da Linha -->
                                <div>
                                    <label for="line.code" class="block text-sm font-medium text-gray-700">
                                        {{ __('messages.code') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="line.code" wire:model="line.code" 
                                        placeholder="{{ __('messages.enter_line_code') }}"
                                        class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white">
                                    @error('line.code') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <!-- Nome da Linha -->
                                <div>
                                    <label for="line.name" class="block text-sm font-medium text-gray-700">
                                        {{ __('messages.name') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="line.name" wire:model="line.name" 
                                        placeholder="{{ __('messages.enter_line_name') }}"
                                        class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white">
                                    @error('line.name') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <!-- Capacidade por Hora -->
                                <div>
                                    <label for="line.capacity_per_hour" class="block text-sm font-medium text-gray-700">
                                        {{ __('messages.capacity_per_hour') }} <span class="text-red-500">*</span>
                                    </label>
                                    <div class="mt-1 flex rounded-md shadow-sm">
                                        <input type="number" step="0.1" min="0" id="line.capacity_per_hour" wire:model="line.capacity_per_hour" 
                                            placeholder="0"
                                            class="focus:ring-blue-500 focus:border-blue-500 flex-1 block w-full rounded-none rounded-l-md sm:text-sm border-gray-300 bg-white">
                                        <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                            {{ __('messages.units_per_hour') }}
                                        </span>
                                    </div>
                                    @error('line.capacity_per_hour') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <!-- Status Ativo -->
                                <div class="flex items-center">
                                    <div>
                                        <label for="line.is_active" class="flex items-center cursor-pointer">
                                            <div class="relative">
                                                <!-- Input escondido -->
                                                <input type="checkbox" wire:model="line.is_active" id="line.is_active" class="sr-only">
                                                <!-- Track (fundo do toggle) -->
                                                <div class="w-12 h-6 bg-gray-300 rounded-full shadow-inner transition-all duration-300 ease-in-out"></div>
                                                <!-- Dot (bolinha do toggle) -->
                                                <div class="dot absolute w-6 h-6 bg-white rounded-full shadow left-0 top-0 transition-transform duration-300 ease-in-out transform" 
                                                    :class="{'translate-x-6 bg-green-500': $wire.line.is_active, 'bg-white': !$wire.line.is_active}"></div>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="ml-3">
                                        <label for="line.is_active" class="text-sm font-medium text-gray-700 cursor-pointer">
                                            {{ __('messages.is_active') }}
                                        </label>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ __('messages.active_info') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Cartão de Localização e Departamento -->
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                            <div class="flex items-center bg-gradient-to-r from-green-50 to-green-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-map-marker-alt text-green-600 mr-2"></i>
                                <h3 class="text-base font-medium text-gray-700">{{ __('messages.location_and_department') }}</h3>
                            </div>
                            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Localização -->
                                <div>
                                    <label for="line.location_id" class="block text-sm font-medium text-gray-700">
                                        {{ __('messages.location') }}
                                    </label>
                                    <select id="line.location_id" wire:model="line.location_id" 
                                        class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white">
                                        <option value="">{{ __('messages.select_location') }}</option>
                                        @foreach($locations as $location)
                                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('line.location_id') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <!-- Departamento -->
                                <div>
                                    <label for="line.department_id" class="block text-sm font-medium text-gray-700">
                                        {{ __('messages.department') }}
                                    </label>
                                    <select id="line.department_id" wire:model="line.department_id" 
                                        class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white">
                                        <option value="">{{ __('messages.select_department') }}</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('line.department_id') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <!-- Gerente Responsável -->
                                <div class="md:col-span-2">
                                    <label for="line.manager_id" class="block text-sm font-medium text-gray-700">
                                        {{ __('messages.manager') }}
                                    </label>
                                    <select id="line.manager_id" wire:model="line.manager_id" 
                                        class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white">
                                        <option value="">{{ __('messages.select_manager') }}</option>
                                        @foreach($managers as $manager)
                                            <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('line.manager_id') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Cartão de Turnos -->
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                            <div class="flex items-center bg-gradient-to-r from-purple-50 to-purple-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-clock text-purple-600 mr-2"></i>
                                <h3 class="text-base font-medium text-gray-700">{{ __('messages.associated_shifts') }}</h3>
                            </div>
                            <div class="p-4 grid grid-cols-1 gap-4">
                                @if(count($availableShifts) > 0)
                                    <div class="flex flex-wrap gap-3">
                                        @foreach($availableShifts as $shift)
                                            <label class="inline-flex items-center bg-gray-50 hover:bg-gray-100 border border-gray-300 rounded-md px-3 py-2 cursor-pointer transition-all duration-200">
                                                <input type="checkbox" wire:model="selectedShifts" value="{{ $shift['id'] }}" 
                                                    class="form-checkbox h-4 w-4 text-blue-600 rounded focus:ring-blue-500 mr-2">
                                                <div class="flex items-center">
                                                    <div class="w-3 h-3 rounded-full mr-2" style="background-color: {{ $shift['color_code'] }}"></div>
                                                    <span>{{ $shift['name'] }}</span>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-4 text-gray-500">
                                        {{ __('messages.no_shifts_available') }}
                                    </div>
                                @endif
                                @error('selectedShifts') 
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Cartão de Descrição e Observações -->
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                            <div class="flex items-center bg-gradient-to-r from-yellow-50 to-yellow-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-sticky-note text-yellow-600 mr-2"></i>
                                <h3 class="text-base font-medium text-gray-700">{{ __('messages.description_and_notes') }}</h3>
                            </div>
                            <div class="p-4 grid grid-cols-1 gap-4">
                                <!-- Descrição -->
                                <div>
                                    <label for="line.description" class="block text-sm font-medium text-gray-700">
                                        {{ __('messages.description') }}
                                    </label>
                                    <textarea id="line.description" wire:model="line.description" rows="3"
                                        placeholder="{{ __('messages.enter_line_description') }}"
                                        class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white"></textarea>
                                    @error('line.description') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <!-- Observações -->
                                <div>
                                    <label for="line.notes" class="block text-sm font-medium text-gray-700">
                                        {{ __('messages.notes') }}
                                    </label>
                                    <textarea id="line.notes" wire:model="line.notes" rows="3"
                                        placeholder="{{ __('messages.enter_line_notes') }}"
                                        class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white"></textarea>
                                    @error('line.notes') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botões de Ação -->
                    <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                        <button type="button" wire:click="closeModal" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                            <i class="fas fa-times mr-2"></i>
                            {{ __('messages.cancel') }}
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="save">
                                <i class="fas {{ $editMode ? 'fa-save' : 'fa-plus-circle' }} mr-2"></i>
                                {{ $editMode ? __('messages.update') : __('messages.save') }}
                            </span>
                            <span wire:loading wire:target="save" class="inline-flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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
</div>
