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
                        {{ $editMode ? __('messages.edit_shift') : __('messages.add_shift') }}
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
                                <!-- Nome do Turno -->
                                <div class="md:col-span-2">
                                    <label for="shift.name" class="block text-sm font-medium text-gray-700">
                                        {{ __('messages.shift_name') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="shift.name" wire:model="shift.name" 
                                        placeholder="{{ __('messages.enter_shift_name') }}"
                                        class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white">
                                    @error('shift.name') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <!-- Hora de Início -->
                                <div>
                                    <label for="shift.start_time" class="block text-sm font-medium text-gray-700">
                                        {{ __('messages.start_time') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="time" id="shift.start_time" wire:model="shift.start_time" 
                                        class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white">
                                    @error('shift.start_time') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <!-- Hora de Término -->
                                <div>
                                    <label for="shift.end_time" class="block text-sm font-medium text-gray-700">
                                        {{ __('messages.end_time') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="time" id="shift.end_time" wire:model="shift.end_time" 
                                        class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white">
                                    @error('shift.end_time') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Cartão de Intervalo -->
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                            <div class="flex items-center bg-gradient-to-r from-green-50 to-green-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-coffee text-green-600 mr-2"></i>
                                <h3 class="text-base font-medium text-gray-700">{{ __('messages.break_times') }}</h3>
                            </div>
                            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Início do Intervalo -->
                                <div>
                                    <label for="shift.break_start" class="block text-sm font-medium text-gray-700">
                                        {{ __('messages.break_start') }}
                                    </label>
                                    <input type="time" id="shift.break_start" wire:model="shift.break_start" 
                                        class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white">
                                </div>
                                
                                <!-- Término do Intervalo -->
                                <div>
                                    <label for="shift.break_end" class="block text-sm font-medium text-gray-700">
                                        {{ __('messages.break_end') }}
                                    </label>
                                    <input type="time" id="shift.break_end" wire:model="shift.break_end" 
                                        class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Cartão de Dias de Trabalho -->
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                            <div class="flex items-center bg-gradient-to-r from-purple-50 to-purple-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-calendar-alt text-purple-600 mr-2"></i>
                                <h3 class="text-base font-medium text-gray-700">{{ __('messages.working_days') }}</h3>
                            </div>
                            <div class="p-4 grid grid-cols-1 gap-4">
                                <div class="flex flex-wrap gap-3">
                                    @foreach($weekDays as $day)
                                        <label class="inline-flex items-center bg-gray-50 hover:bg-gray-100 border border-gray-300 rounded-md px-3 py-2 cursor-pointer transition-all duration-200">
                                            <input type="checkbox" wire:model="shift.working_days" value="{{ $day['value'] }}" 
                                                class="form-checkbox h-4 w-4 text-blue-600 rounded focus:ring-blue-500 mr-2">
                                            {{ $day['label'] }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        
                        <!-- Cartão de Configurações Adicionais -->
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                            <div class="flex items-center bg-gradient-to-r from-yellow-50 to-yellow-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-cog text-yellow-600 mr-2"></i>
                                <h3 class="text-base font-medium text-gray-700">{{ __('messages.additional_settings') }}</h3>
                            </div>
                            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Cor do Turno -->
                                <div>
                                    <label for="shift.color_code" class="block text-sm font-medium text-gray-700">
                                        {{ __('messages.color') }} <span class="text-red-500">*</span>
                                    </label>
                                    <div class="mt-1 flex items-center">
                                        <input type="color" id="shift.color_code" wire:model="shift.color_code" 
                                            class="h-8 w-8 border border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        <span class="ml-2 text-sm text-gray-500">{{ $shift['color_code'] ?? '#3B82F6' }}</span>
                                    </div>
                                    @error('shift.color_code') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <!-- Status Ativo -->
                                <div class="flex items-center">
                                    <div>
                                        <label for="shift.is_active" class="flex items-center cursor-pointer">
                                            <div class="relative">
                                                <!-- Input escondido -->
                                                <input type="checkbox" wire:model="shift.is_active" id="shift.is_active" class="sr-only">
                                                <!-- Track (fundo do toggle) -->
                                                <div class="w-12 h-6 bg-gray-300 rounded-full shadow-inner transition-all duration-300 ease-in-out"></div>
                                                <!-- Dot (bolinha do toggle) -->
                                                <div class="dot absolute w-6 h-6 bg-white rounded-full shadow left-0 top-0 transition-transform duration-300 ease-in-out transform" 
                                                    :class="{'translate-x-6 bg-green-500': $wire.shift.is_active, 'bg-white': !$wire.shift.is_active}"></div>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="ml-3">
                                        <label for="shift.is_active" class="text-sm font-medium text-gray-700 cursor-pointer">
                                            {{ __('messages.is_active') }}
                                        </label>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ __('messages.active_info') }}
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Descrição -->
                                <div class="md:col-span-2">
                                    <label for="shift.description" class="block text-sm font-medium text-gray-700">
                                        {{ __('messages.description') }}
                                    </label>
                                    <textarea id="shift.description" wire:model="shift.description" rows="3"
                                        placeholder="{{ __('messages.enter_shift_description') }}"
                                        class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white"></textarea>
                                    @error('shift.description') 
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
