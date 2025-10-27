<!-- Campos condicionais para entrada de horas extras baseados no tipo de entrada -->
<div class="mb-6 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 transition-all duration-200 ease-in-out hover:shadow-md">
    <div class="flex items-center bg-gradient-to-r from-green-50 to-green-100 px-4 py-3 border-b border-gray-200">
        <i class="fas fa-clock text-green-600 mr-2"></i>
        <h2 class="text-base font-medium text-gray-700">{{ __('messages.hours_input') }}</h2>
    </div>
    <div class="p-4" x-data="{ inputType: @entangle('input_type') }">
        <!-- Horas por Intervalo de Tempo -->
        <div x-show="inputType === 'time_range'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
            <div class="bg-blue-50 p-3 rounded-md border border-blue-100 mb-4">
                <div class="flex items-center text-sm">
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                    <span class="text-gray-600">{{ __('messages.time_range_info') }}</span>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="group transition-all duration-300 ease-in-out hover:scale-[1.01] hover:shadow-sm p-3 rounded-md border border-gray-100">
                    <label for="start_time" class="block text-sm font-medium text-gray-700 flex items-center">
                        <i class="fas fa-hourglass-start text-blue-600 mr-2"></i>
                        {{ __('messages.start_time') }} <span class="text-red-500 ml-1">*</span>
                    </label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="far fa-clock text-gray-400"></i>
                        </div>
                        <input type="time" id="start_time" wire:model.debounce.500ms="start_time" 
                            class="pl-10 block w-full shadow-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white transition-all duration-200 ease-in-out
                            {{ $errors->has('start_time') ? 'border-red-300 text-red-900 placeholder-red-300 focus:ring-red-500 focus:border-red-500' : '' }}">
                        <div wire:loading wire:target="start_time" class="absolute right-10 inset-y-0 flex items-center">
                            <svg class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>
                    @error('start_time') 
                        <p class="mt-1 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="group transition-all duration-300 ease-in-out hover:scale-[1.01] hover:shadow-sm p-3 rounded-md border border-gray-100">
                    <label for="end_time" class="block text-sm font-medium text-gray-700 flex items-center">
                        <i class="fas fa-hourglass-end text-blue-600 mr-2"></i>
                        {{ __('messages.end_time') }} <span class="text-red-500 ml-1">*</span>
                    </label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="far fa-clock text-gray-400"></i>
                        </div>
                        <input type="time" id="end_time" wire:model.debounce.500ms="end_time" 
                            class="pl-10 block w-full shadow-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white transition-all duration-200 ease-in-out
                            {{ $errors->has('end_time') ? 'border-red-300 text-red-900 placeholder-red-300 focus:ring-red-500 focus:border-red-500' : '' }}">
                        <div wire:loading wire:target="end_time" class="absolute right-10 inset-y-0 flex items-center">
                            <svg class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>
                    @error('end_time') 
                        <p class="mt-1 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            @error('time_diff') <p class="mt-2 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
            @error('time_error') <p class="mt-2 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
        </div>
        
        <!-- Horas Diretas (Diária/Mensal) -->
        <div x-show="inputType === 'daily' || inputType === 'monthly'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
            <!-- Informação contextual baseada no tipo de entrada -->
            <div class="bg-green-50 p-3 rounded-md border border-green-100 mb-4">
                <div class="flex items-center text-sm">
                    <i class="fas fa-info-circle text-green-600 mr-2"></i>
                    <span class="text-gray-600" x-text="inputType === 'daily' ? '{{ __('messages.daily_hours_help') }}' : '{{ __('messages.monthly_hours_help') }}'"></span>
                </div>
            </div>
            
            <div class="group transition-all duration-300 ease-in-out hover:scale-[1.01] hover:shadow-sm p-4 rounded-md border border-gray-100">
                <label for="direct_hours" class="block text-sm font-medium text-gray-700 flex items-center">
                    <i class="fas fa-clock text-blue-600 mr-2"></i>
                    {{ __('messages.total_hours') }} <span class="text-red-500 ml-1">*</span>
                </label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-hourglass-half text-gray-400"></i>
                    </div>
                    <input type="number" step="0.01" id="direct_hours" wire:model.debounce.500ms="direct_hours" 
                        placeholder="{{ __('messages.enter_worked_hours') }}" 
                        class="pl-10 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white transition-all duration-200 ease-in-out
                        {{ $errors->has('direct_hours') ? 'border-red-300 text-red-900 placeholder-red-300 focus:ring-red-500 focus:border-red-500' : '' }}">
                    <div wire:loading wire:target="direct_hours" class="absolute right-10 inset-y-0 flex items-center">
                        <svg class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <span class="text-gray-500 sm:text-sm font-medium">h</span>
                    </div>
                </div>
                @error('direct_hours') 
                    <p class="mt-1 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                @enderror
                <div class="mt-1 text-xs text-gray-500 flex items-center">
                    <i class="fas fa-info-circle mr-1 text-blue-500"></i>
                    <span x-show="inputType === 'daily'">{{ __('messages.daily_hours_help') }}</span>
                    <span x-show="inputType === 'monthly'">{{ __('messages.monthly_hours_help') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Taxa horária -->
<div class="mb-6 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 transition-all duration-200 ease-in-out hover:shadow-md">
    <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
        <i class="fas fa-money-bill-wave text-blue-600 mr-2"></i>
        <h2 class="text-base font-medium text-gray-700">{{ __('messages.rate_and_calculation') }}</h2>
    </div>
    <div class="p-4 space-y-4">
        <div class="group transition-all duration-300 ease-in-out hover:scale-[1.01] hover:shadow-sm p-3 rounded-md border border-gray-100">
            <label for="rate" class="block text-sm font-medium text-gray-700 flex items-center">
                <i class="fas fa-dollar-sign text-blue-600 mr-2"></i>
                {{ __('messages.hourly_rate') }} <span class="text-red-500 ml-1">*</span>
            </label>
            <div class="mt-1 relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 sm:text-sm">$</span>
                </div>
                <input type="number" step="0.01" id="rate" wire:model.debounce.500ms="rate" 
                    placeholder="{{ __('messages.enter_hourly_rate') }}" 
                    class="pl-7 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white transition-all duration-200 ease-in-out
                    {{ $errors->has('rate') ? 'border-red-300 text-red-900 placeholder-red-300 focus:ring-red-500 focus:border-red-500' : '' }}">
                <div wire:loading wire:target="rate" class="absolute right-0 inset-y-0 flex items-center pr-3">
                    <svg class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
            @error('rate') 
                <p class="mt-1 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
            @enderror
        </div>
        
        <!-- Resultados dos cálculos -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-100 shadow-md overflow-hidden transition-all duration-300 ease-in-out hover:shadow-lg"
                x-data="{ updated: false }" 
                x-effect="
                    if ($wire.hours !== null || $wire.amount !== null) {
                        updated = true;
                        setTimeout(() => { updated = false }, 1000);
                    }">
                
            <div class="flex items-center bg-gradient-to-r from-indigo-500 to-blue-600 px-4 py-2 border-b border-blue-200">
                <i class="fas fa-calculator text-white mr-2"></i>
                <h3 class="text-sm font-medium text-white">{{ __('messages.calculation_results') }}</h3>
            </div>
            <div class="p-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <!-- Horas Calculadas -->
                    <div class="bg-white p-3 rounded-md shadow-sm border border-blue-100 transition-all duration-300 ease-in-out"
                         :class="{'transform scale-[1.02]': updated}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-clock text-blue-600 mr-2"></i>
                                <span class="text-sm font-medium text-gray-700">{{ __('messages.calculated_hours') }}:</span>
                            </div>
                            <div class="flex items-center">
                                <span wire:loading wire:target="calculateHoursAndAmount, direct_hours, start_time, end_time" class="mr-2">
                                    <svg class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                                <span class="text-lg font-bold text-blue-700 transition-all duration-300 ease-in-out" :class="{'animate-pulse': updated}">
                                    {{ $hours ?? '0.00' }} h
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Valor Total -->
                    <div class="bg-white p-3 rounded-md shadow-sm border border-green-100 transition-all duration-300 ease-in-out"
                         :class="{'transform scale-[1.02]': updated}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-dollar-sign text-green-600 mr-2"></i>
                                <span class="text-sm font-medium text-gray-700">{{ __('messages.total_amount') }}:</span>
                            </div>
                            <div class="flex items-center">
                                <span wire:loading wire:target="calculateHoursAndAmount, direct_hours, rate, start_time, end_time" class="mr-2">
                                    <svg class="animate-spin h-4 w-4 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                                <span class="text-lg font-bold text-green-700 transition-all duration-300 ease-in-out" :class="{'animate-pulse': updated}">
                                    $ {{ $amount ?? '0.00' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3 text-xs text-gray-500 flex items-center bg-blue-50 p-2 rounded-md">
                    <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                    <span>{{ __('messages.auto_calculation_info') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Descrição -->
<div class="mb-6 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 transition-all duration-200 ease-in-out hover:shadow-md">
    <div class="flex items-center bg-gradient-to-r from-purple-50 to-purple-100 px-4 py-3 border-b border-gray-200">
        <i class="fas fa-align-left text-purple-600 mr-2"></i>
        <h2 class="text-base font-medium text-gray-700">{{ __('messages.additional_info') }}</h2>
    </div>
    <div class="p-4">
        <div class="group transition-all duration-300 ease-in-out hover:scale-[1.01] hover:shadow-sm p-3 rounded-md border border-gray-100">
            <label for="description" class="block text-sm font-medium text-gray-700 flex items-center">
                <i class="fas fa-comment-alt text-purple-600 mr-2"></i>
                {{ __('messages.description') }}
            </label>
            <div class="mt-1">
                <textarea id="description" wire:model.defer="description" rows="3" 
                    placeholder="{{ __('messages.enter_description') }}"
                    class="shadow-sm block w-full focus:ring-blue-500 focus:border-blue-500 sm:text-sm border-gray-300 rounded-md bg-white"></textarea>
            </div>
        </div>
    </div>
</div>

<!-- Botões de ação -->
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
