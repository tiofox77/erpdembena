{{-- Modal: CreateShift (DO NOT RENAME) --}}
@if($showShiftModal)
<div id="modal-create-shift" role="dialog" aria-modal="true"
     aria-labelledby="modal-create-shift-title"
     x-data="{ open: @entangle('showShiftModal') }" 
     x-show="open" 
     x-cloak 
     class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50" 
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
            
            <!-- Cabeçalho com gradiente -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                <h3 id="modal-create-shift-title" class="text-lg font-medium text-white flex items-center">
                    <i class="fas {{ $isEditing ? 'fa-edit' : 'fa-plus-circle' }} mr-2 animate-pulse"></i>
                    {{ $isEditing ? __('shifts.edit_shift') : __('shifts.add_shift') }}
                </h3>
                <button type="button" wire:click="closeShiftModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Corpo da modal com cartões temáticos -->
            <div class="p-6 space-y-6">
                <!-- Informações Gerais -->
                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                    <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                        <h3 class="text-base font-medium text-gray-700">{{ __('shifts.general_settings') }}</h3>
                    </div>
                    <div class="p-4 space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-tag text-blue-500 mr-1"></i>
                                {{ __('shifts.name') }}
                            </label>
                            <input type="text" wire:model.defer="name" id="name" 
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white"
                                placeholder="{{ __('shifts.enter_shift_name') }}">
                            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-file-alt text-blue-500 mr-1"></i>
                                {{ __('shifts.description') }}
                            </label>
                            <textarea wire:model.defer="description" id="description" rows="3" 
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white"
                                placeholder="{{ __('shifts.enter_description') }}"></textarea>
                            @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Configurações de Horário -->
                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                    <div class="flex items-center bg-gradient-to-r from-green-50 to-green-100 px-4 py-3 border-b border-gray-200">
                        <i class="fas fa-clock text-green-600 mr-2"></i>
                        <h3 class="text-base font-medium text-gray-700">{{ __('shifts.time_settings') }}</h3>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-play text-green-500 mr-1"></i>
                                    {{ __('shifts.start_time') }}
                                </label>
                                <input type="time" wire:model.defer="start_time" id="start_time" 
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white">
                                @error('start_time') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label for="end_time" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-stop text-red-500 mr-1"></i>
                                    {{ __('shifts.end_time') }}
                                </label>
                                <input type="time" wire:model.defer="end_time" id="end_time" 
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white">
                                @error('end_time') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label for="break_duration" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-pause text-yellow-500 mr-1"></i>
                                    {{ __('shifts.break_duration') }}
                                </label>
                                <div class="relative">
                                    <input type="number" wire:model.defer="break_duration" id="break_duration" 
                                        class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white pr-12"
                                        placeholder="0">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">min</span>
                                    </div>
                                </div>
                                @error('break_duration') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Configurações Avançadas -->
                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                    <div class="flex items-center bg-gradient-to-r from-purple-50 to-purple-100 px-4 py-3 border-b border-gray-200">
                        <i class="fas fa-cogs text-purple-600 mr-2"></i>
                        <h3 class="text-base font-medium text-gray-700">{{ __('shifts.additional_settings') }}</h3>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex items-center">
                                <div>
                                    <label for="is_night_shift" class="flex items-center cursor-pointer">
                                        <div class="relative">
                                            <input type="checkbox" wire:model.defer="is_night_shift" id="is_night_shift" class="sr-only">
                                            <div class="w-12 h-6 bg-gray-300 rounded-full shadow-inner transition-all duration-300 ease-in-out"
                                                 :class="{ 'bg-indigo-500': $wire.is_night_shift, 'bg-gray-300': !$wire.is_night_shift }"></div>
                                            <div class="dot absolute w-6 h-6 bg-white rounded-full shadow left-0 top-0 transition-transform duration-300 ease-in-out transform" 
                                                 :class="{ 'translate-x-6': $wire.is_night_shift, 'translate-x-0': !$wire.is_night_shift }"></div>
                                        </div>
                                    </label>
                                </div>
                                <div class="ml-3">
                                    <label for="is_night_shift" class="text-sm font-medium text-gray-700 cursor-pointer flex items-center">
                                        <i class="fas fa-moon text-indigo-500 mr-2"></i>
                                        {{ __('shifts.night_shift') }}
                                    </label>
                                    <p class="text-xs text-gray-500 mt-1">{{ __('shifts.night_shift_info') }}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center">
                                <div>
                                    <label for="is_active" class="flex items-center cursor-pointer">
                                        <div class="relative">
                                            <input type="checkbox" wire:model.defer="is_active" id="is_active" class="sr-only">
                                            <div class="w-12 h-6 bg-gray-300 rounded-full shadow-inner transition-all duration-300 ease-in-out"
                                                 :class="{ 'bg-green-500': $wire.is_active, 'bg-gray-300': !$wire.is_active }"></div>
                                            <div class="dot absolute w-6 h-6 bg-white rounded-full shadow left-0 top-0 transition-transform duration-300 ease-in-out transform" 
                                                 :class="{ 'translate-x-6': $wire.is_active, 'translate-x-0': !$wire.is_active }"></div>
                                        </div>
                                    </label>
                                </div>
                                <div class="ml-3">
                                    <label for="is_active" class="text-sm font-medium text-gray-700 cursor-pointer flex items-center">
                                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                        {{ __('shifts.active') }}
                                    </label>
                                    <p class="text-xs text-gray-500 mt-1">{{ __('shifts.active_info') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Rodapé com botões de ação -->
            <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                <button type="button" wire:click="closeShiftModal" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                    <i class="fas fa-times mr-2"></i>
                    {{ __('shifts.cancel') }}
                </button>
                <button type="button" wire:click="saveShift" wire:loading.attr="disabled" class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="saveShift">
                        <i class="fas {{ $isEditing ? 'fa-save' : 'fa-plus-circle' }} mr-2"></i>
                        {{ $isEditing ? __('shifts.update') : __('shifts.save') }}
                    </span>
                    <span wire:loading wire:target="saveShift" class="inline-flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('shifts.processing') }}
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
@endif
