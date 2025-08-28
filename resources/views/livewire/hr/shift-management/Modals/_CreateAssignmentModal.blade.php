{{-- Modal: CreateAssignment (DO NOT RENAME) --}}
<div id="modal-create-assignment" role="dialog" aria-modal="true"
     aria-labelledby="modal-create-assignment-title"
     x-data="{ open: @entangle('showAssignmentModal') }" 
     x-show="open" 
     x-cloak 
     class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50" 
     x-transition:enter="transition ease-out duration-300" 
     x-transition:enter-start="opacity-0" 
     x-transition:enter-end="opacity-100" 
     x-transition:leave="transition ease-in duration-200" 
     x-transition:leave-start="opacity-100" 
     x-transition:leave-end="opacity-0">
    <div class="relative top-4 mx-auto p-2 w-full max-w-5xl min-h-screen flex items-center justify-center">
        <div class="relative bg-white rounded-2xl shadow-2xl transform transition-all duration-300 ease-in-out w-full max-h-[90vh] overflow-hidden" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="transform opacity-0 scale-95" 
             x-transition:enter-end="transform opacity-100 scale-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="transform opacity-100 scale-100" 
             x-transition:leave-end="transform opacity-0 scale-95">
            
            <!-- Cabeçalho com gradiente melhorado -->
            <div class="bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 rounded-t-2xl px-6 py-4 flex justify-between items-center sticky top-0 z-10">
                <div class="flex items-center">
                    <div class="bg-white bg-opacity-20 rounded-full p-2 mr-3">
                        <i class="fas fa-user-plus text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 id="modal-create-assignment-title" class="text-lg font-semibold text-white">
                            {{ $assignment_id ? __('shifts.edit_assignment') : __('shifts.new_assignment') }}
                        </h3>
                        <p class="text-blue-100 text-sm opacity-90">
                            {{ __('shifts.assignment_details') }}
                        </p>
                    </div>
                </div>
                <button type="button" wire:click="closeAssignmentModal" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white p-2 rounded-full transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <!-- Corpo da modal -->
            <div class="max-h-[75vh] overflow-y-auto">
                <form wire:submit.prevent="saveAssignment">
                    <div class="p-6 space-y-6">
                        <!-- Informações do funcionário e turno -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="flex items-center bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-100">
                                <div class="bg-blue-100 rounded-full p-2 mr-3">
                                    <i class="fas fa-user-clock text-blue-600"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-800">{{ __('shifts.employee_shift_selection') }}</h3>
                                    <p class="text-sm text-gray-600">{{ __('shifts.employee_selection_description') }}</p>
                                </div>
                            </div>
                            <div class="p-6 space-y-6">
                                <!-- Employee selection -->
                                <div>
                                    <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-user text-blue-500 mr-2"></i>
                                        {{ __('shifts.employee') }} *
                                    </label>
                                    <select wire:model="employee_id" wire:key="employee-select-{{ $assignment_id ?? 'new' }}" id="employee_id" 
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 text-sm bg-white transition-all duration-200">
                                        <option value="">{{ __('shifts.select_employee') }}</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}" @if(strval($employee_id) === strval($employee->id)) selected @endif>
                                                {{ $employee->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('employee_id') 
                                        <p class="mt-2 text-sm text-red-600 flex items-center">
                                            <i class="fas fa-exclamation-triangle mr-2"></i>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                                
                                <!-- Rotation setting -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-3">
                                        <i class="fas fa-sync-alt text-purple-500 mr-2"></i>
                                        {{ __('shifts.shift_configuration') }}
                                    </label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div class="relative">
                                            <input type="radio" wire:model="has_rotation" value="0" id="fixed_shift" class="sr-only">
                                            <label for="fixed_shift" 
                                                   class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition-all duration-200"
                                                   :class="$wire.has_rotation == 0 ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
                                                <div class="flex items-center">
                                                    <div class="w-4 h-4 border-2 rounded-full flex items-center justify-center transition-all duration-200"
                                                         :class="$wire.has_rotation == 0 ? 'border-blue-500 bg-blue-500' : 'border-gray-300'">
                                                        <div class="w-2 h-2 bg-white rounded-full transition-opacity duration-200"
                                                             :class="$wire.has_rotation == 0 ? 'opacity-100' : 'opacity-0'"></div>
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-medium text-gray-900">{{ __('shifts.single_shift') }}</div>
                                                        <div class="text-xs text-gray-500">{{ __('shifts.single_shift_description') }}</div>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="relative">
                                            <input type="radio" wire:model="has_rotation" value="1" id="rotation_shift" class="sr-only">
                                            <label for="rotation_shift" 
                                                   class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition-all duration-200"
                                                   :class="$wire.has_rotation == 1 ? 'border-purple-500 bg-purple-50' : 'border-gray-200'">
                                                <div class="flex items-center">
                                                    <div class="w-4 h-4 border-2 rounded-full flex items-center justify-center transition-all duration-200"
                                                         :class="$wire.has_rotation == 1 ? 'border-purple-500 bg-purple-500' : 'border-gray-300'">
                                                        <div class="w-2 h-2 bg-white rounded-full transition-opacity duration-200"
                                                             :class="$wire.has_rotation == 1 ? 'opacity-100' : 'opacity-0'"></div>
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-medium text-gray-900">{{ __('shifts.shift_rotation') }}</div>
                                                        <div class="text-xs text-gray-500">{{ __('shifts.rotation_description') }}</div>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Rotation details (when enabled) -->
                                <div x-show="$wire.has_rotation" 
                                     x-transition:enter="transition ease-out duration-300" 
                                     x-transition:enter-start="opacity-0 scale-95" 
                                     x-transition:enter-end="opacity-100 scale-100" 
                                     x-transition:leave="transition ease-in duration-200" 
                                     x-transition:leave-start="opacity-100 scale-100" 
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label for="rotation_type" class="block text-sm font-medium text-gray-700 mb-2">
                                                <i class="fas fa-calendar-alt text-purple-500 mr-2"></i>
                                                {{ __('shifts.rotation_type') }}
                                            </label>
                                            <select wire:model="rotation_type" id="rotation_type" 
                                                class="mt-1 block w-full rounded-lg border-purple-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-500 focus:ring-opacity-50 text-sm bg-white transition-all duration-200">
                                                <option value="">{{ __('shifts.select_rotation_type') }}</option>
                                                <option value="weekly">{{ __('shifts.weekly') }}</option>
                                                <option value="biweekly">{{ __('shifts.biweekly') }}</option>
                                                <option value="monthly">{{ __('shifts.monthly') }}</option>
                                            </select>
                                            @error('rotation_type') 
                                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label for="rotation_frequency" class="block text-sm font-medium text-gray-700 mb-2">
                                                <i class="fas fa-hashtag text-purple-500 mr-2"></i>
                                                {{ __('shifts.frequency_days') }}
                                            </label>
                                            <input type="number" wire:model="rotation_frequency" id="rotation_frequency" min="1" max="30" 
                                                class="mt-1 block w-full rounded-lg border-purple-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-500 focus:ring-opacity-50 text-sm bg-white transition-all duration-200">
                                            @error('rotation_frequency') 
                                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label for="rotation_start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                                <i class="fas fa-play text-purple-500 mr-2"></i>
                                                {{ __('shifts.rotation_start_date') }}
                                            </label>
                                            <input type="date" wire:model="rotation_start_date" id="rotation_start_date" 
                                                class="mt-1 block w-full rounded-lg border-purple-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-500 focus:ring-opacity-50 text-sm bg-white transition-all duration-200">
                                            @error('rotation_start_date') 
                                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Single shift selection (when not rotating) -->
                                <div x-show="!$wire.has_rotation" 
                                     x-transition:enter="transition ease-out duration-300" 
                                     x-transition:enter-start="opacity-0 scale-95" 
                                     x-transition:enter-end="opacity-100 scale-100" 
                                     x-transition:leave="transition ease-in duration-200" 
                                     x-transition:leave-start="opacity-100 scale-100" 
                                     x-transition:leave-end="opacity-0 scale-95">
                                    <label for="shift_id_assignment" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-clock text-blue-500 mr-2"></i>
                                        {{ __('shifts.shift') }} *
                                    </label>
                                    <select wire:model="shift_id_assignment" wire:key="shift-select-{{ $assignment_id ?? 'new' }}" id="shift_id_assignment" 
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 text-sm bg-white transition-all duration-200">
                                        <option value="">{{ __('shifts.select_shift') }}</option>
                                        @foreach($shiftsForSelect as $shift)
                                            <option value="{{ $shift->id }}" @if($shift_id_assignment == $shift->id) selected @endif>
                                                {{ $shift->name }} ({{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('shift_id_assignment') 
                                        <p class="mt-2 text-sm text-red-600 flex items-center">
                                            <i class="fas fa-exclamation-triangle mr-2"></i>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                                
                                <!-- Multiple Shifts selection (when rotation is enabled) -->
                                <div x-show="$wire.has_rotation" 
                                     x-transition:enter="transition ease-out duration-300" 
                                     x-transition:enter-start="opacity-0 scale-95" 
                                     x-transition:enter-end="opacity-100 scale-100" 
                                     x-transition:leave="transition ease-in duration-200" 
                                     x-transition:leave-start="opacity-100 scale-100" 
                                     x-transition:leave-end="opacity-0 scale-95">
                                    <label class="block text-sm font-medium text-gray-700 mb-3">
                                        <i class="fas fa-layer-group text-purple-500 mr-2"></i>
                                        {{ __('shifts.select_shifts') }} *
                                    </label>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-64 overflow-y-auto p-3 border-2 border-purple-200 rounded-lg bg-purple-50">
                                        @foreach($shiftsForSelect as $shift)
                                            <div class="relative" x-data="{ 
                                                get isSelected() { 
                                                    return $wire.selected_shifts && $wire.selected_shifts.includes('{{ $shift->id }}') 
                                                } 
                                            }">
                                                <input type="checkbox" wire:model.live="selected_shifts" value="{{ $shift->id }}" id="shift_{{ $shift->id }}" class="sr-only">
                                                <label for="shift_{{ $shift->id }}" 
                                                       class="flex items-center p-3 border-2 rounded-lg cursor-pointer hover:bg-white transition-all duration-200"
                                                       :class="isSelected ? 'border-purple-500 bg-white' : 'border-gray-200'">
                                                    <div class="flex items-center">
                                                        <div class="w-4 h-4 border-2 rounded flex items-center justify-center transition-all duration-200"
                                                             :class="isSelected ? 'border-purple-500 bg-purple-500' : 'border-gray-300'">
                                                            <i class="fas fa-check text-white text-xs transition-opacity duration-200"
                                                               :class="isSelected ? 'opacity-100' : 'opacity-0'"></i>
                                                        </div>
                                                        <div class="ml-3 flex-1">
                                                            <div class="text-sm font-medium text-gray-900">{{ $shift->name }}</div>
                                                            <div class="text-xs text-gray-500">{{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }}</div>
                                                            @if($shift->is_night_shift)
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 mt-1">
                                                                    <i class="fas fa-moon mr-1"></i> {{ __('shifts.night') }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('selected_shifts') 
                                        <p class="mt-2 text-sm text-red-600 flex items-center">
                                            <i class="fas fa-exclamation-triangle mr-2"></i>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                    @error('shifts') 
                                        <p class="mt-2 text-sm text-red-600 flex items-center">
                                            <i class="fas fa-exclamation-triangle mr-2"></i>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                
                        <!-- Datas -->
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                            <div class="flex items-center bg-gradient-to-r from-green-50 to-green-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-calendar text-green-600 mr-2"></i>
                                <h3 class="text-base font-medium text-gray-700">{{ __('shifts.assignment_period') }}</h3>
                            </div>
                            <div class="p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Data de início -->
                                    <div>
                                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ __('shifts.start_date') }} *
                                        </label>
                                        <input type="date" wire:model.defer="start_date" id="start_date" 
                                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white">
                                        @error('start_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <!-- Data de término -->
                                    <div>
                                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ __('shifts.end_date') }}
                                        </label>
                                        <input type="date" wire:model.defer="end_date" id="end_date" 
                                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white">
                                        @error('end_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Observações -->
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                            <div class="flex items-center bg-gradient-to-r from-green-50 to-green-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-sticky-note text-green-600 mr-2"></i>
                                <h3 class="text-base font-medium text-gray-700">{{ __('shifts.notes') }}</h3>
                            </div>
                            <div class="p-4">
                                <!-- Notas -->
                                <div>
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-comment text-green-500 mr-2"></i>
                                        {{ __('shifts.assignment_notes') }}
                                    </label>
                                    <textarea wire:model.defer="notes" id="notes" rows="3" 
                                        placeholder="{{ __('shifts.enter_notes') }}"
                                        class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white transition-all duration-200"></textarea>
                                    @error('notes') 
                                        <p class="mt-2 text-sm text-red-600 flex items-center">
                                            <i class="fas fa-exclamation-triangle mr-2"></i>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Botões de ação com estado de Loading e Animações -->
                        <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                            <button type="button" wire:click="closeAssignmentModal" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                <i class="fas fa-times mr-2"></i>
                                {{ __('shifts.cancel') }}
                            </button>
                            <button type="submit" wire:loading.attr="disabled" class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="saveAssignment">
                                    <i class="fas {{ $assignment_id ? 'fa-save' : 'fa-plus-circle' }} mr-2"></i>
                                    {{ $assignment_id ? __('shifts.update') : __('shifts.save') }}
                                </span>
                                <span wire:loading wire:target="saveAssignment" class="inline-flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ __('shifts.processing') }}
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
