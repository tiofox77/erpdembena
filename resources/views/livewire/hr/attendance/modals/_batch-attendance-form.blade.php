{{-- Etapa 1: Seleção de Shift --}}
<div class="mb-6 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
    <div class="flex items-center bg-gradient-to-r from-purple-50 to-purple-100 px-4 py-3 border-b border-gray-200">
        <i class="fas fa-business-time text-purple-600 mr-2"></i>
        <h4 class="text-base font-medium text-gray-700">{{ __('attendance.step_1_select_shift') }}</h4>
        @if($selectedShift)
            <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                <i class="fas fa-check mr-1"></i>
                {{ __('attendance.selected') }}
            </span>
        @endif
    </div>
    <div class="p-4">
        @if(count($availableShifts) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($availableShifts as $shift)
                    <div class="relative">
                        <input type="radio" wire:click="selectShift({{ $shift['id'] }})" 
                               name="shift_selection" 
                               value="{{ $shift['id'] }}" 
                               {{ $selectedShift == $shift['id'] ? 'checked' : '' }}
                               class="sr-only" 
                               id="shift_{{ $shift['id'] }}">
                        <label for="shift_{{ $shift['id'] }}" 
                               class="block p-4 border-2 rounded-lg cursor-pointer transition-all duration-200 hover:border-blue-300 {{ $selectedShift == $shift['id'] ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:bg-gray-50' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-clock text-blue-600 text-lg"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h5 class="text-sm font-medium text-gray-900">{{ $shift['name'] }}</h5>
                                        <p class="text-sm text-gray-500">
                                            {{ $shift['start_time'] }} - {{ $shift['end_time'] }}
                                        </p>
                                        @if($shift['description'] ?? false)
                                            <p class="text-xs text-gray-400 mt-1">{{ $shift['description'] }}</p>
                                        @endif
                                    </div>
                                </div>
                                @if($selectedShift == $shift['id'])
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-check-circle text-blue-600 text-lg"></i>
                                    </div>
                                @endif
                            </div>
                        </label>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <i class="fas fa-business-time text-gray-400 text-4xl mb-4"></i>
                <p class="text-gray-500">{{ __('attendance.no_shifts_available') }}</p>
                <p class="text-sm text-gray-400 mt-2">{{ __('attendance.no_shifts_available_description') }}</p>
            </div>
        @endif
    </div>
</div>

{{-- Etapa 2: Configurações do Lote (só aparece se shift selecionado) --}}
@if($selectedShift)
    <div class="mb-6 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
            <i class="fas fa-cog text-blue-600 mr-2"></i>
            <h4 class="text-base font-medium text-gray-700">{{ __('attendance.step_2_batch_settings') }}</h4>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="batchStatus" class="block text-sm font-medium text-gray-700 mb-1">{{ __('attendance.attendance_status') }}</label>
                    <select wire:model="batchStatus" id="batchStatus" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white">
                        <option value="present">{{ __('attendance.present') }}</option>
                        <option value="absent">{{ __('attendance.absent') }}</option>
                        <option value="late">{{ __('attendance.late') }}</option>
                        <option value="half_day">{{ __('attendance.half_day') }}</option>
                        <option value="leave">{{ __('attendance.sick_leave') }}</option>
                    </select>
                </div>
                <div>
                    <label for="batchTimeIn" class="block text-sm font-medium text-gray-700 mb-1">{{ __('attendance.time_in') }}</label>
                    <input type="time" wire:model="batchTimeIn" id="batchTimeIn" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white">
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        {{ __('attendance.auto_filled_from_shift') }}
                    </p>
                </div>
                <div>
                    <label for="batchTimeOut" class="block text-sm font-medium text-gray-700 mb-1">{{ __('attendance.time_out') }}</label>
                    <input type="time" wire:model="batchTimeOut" id="batchTimeOut" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white">
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        {{ __('attendance.auto_filled_from_shift') }}
                    </p>
                </div>
            </div>
            <div class="mt-4">
                <label for="batchRemarks" class="block text-sm font-medium text-gray-700 mb-1">{{ __('attendance.observations') }}</label>
                <textarea wire:model="batchRemarks" id="batchRemarks" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white" placeholder="{{ __('attendance.enter_observations') }}"></textarea>
            </div>
        </div>
    </div>
    
    {{-- Etapa 3: Seleção de Funcionários --}}
    <div class="mb-6 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="flex items-center justify-between bg-gradient-to-r from-green-50 to-green-100 px-4 py-3 border-b border-gray-200">
            <div class="flex items-center">
                <i class="fas fa-users text-green-600 mr-2"></i>
                <h4 class="text-base font-medium text-gray-700">{{ __('attendance.step_3_select_employees') }}</h4>
                <span class="ml-2 text-sm text-gray-500">({{ count($shiftEmployees) }} {{ __('attendance.employees_count') }})</span>
            </div>
            @if(count($shiftEmployees) > 0)
                <div class="flex items-center space-x-2">
                    <button type="button" wire:click="toggleSelectAll" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        <i class="fas fa-check-square mr-1"></i>
                        {{ $selectAllEmployees ? __('attendance.deselect_all') : __('attendance.select_all') }}
                    </button>
                </div>
            @endif
        </div>
        <div class="p-4">
            @if(count($shiftEmployees) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($shiftEmployees as $employee)
                        <div class="flex items-center p-3 border rounded-lg {{ isset($employee['already_marked']) && $employee['already_marked'] ? 'bg-gray-50 border-gray-300' : 'bg-white border-gray-200 hover:border-blue-300' }} transition-all duration-200">
                            <div class="flex items-center w-full">
                                <input type="checkbox" wire:model="selectedEmployees" value="{{ $employee['id'] }}" 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" 
                                       @if(isset($employee['already_marked']) && $employee['already_marked']) disabled @endif>
                                <div class="ml-3 flex-1">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label class="text-sm font-medium text-gray-700">{{ $employee['name'] }}</label>
                                            <div class="text-xs text-gray-500">{{ $employee['department'] }}</div>
                                        </div>
                                        @if(isset($employee['has_rotation']) && $employee['has_rotation'])
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                <i class="fas fa-sync-alt mr-1"></i>
                                                {{ __('shifts.rotation') }}
                                            </span>
                                        @endif
                                    </div>
                                    
                                    @if(isset($employee['already_marked']) && $employee['already_marked'])
                                        <div class="mt-2 text-xs text-orange-600 font-medium">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            {{ __('attendance.already_recorded') }}
                                            @if(isset($employee['existing_status']) && $employee['existing_status'])
                                                <span class="ml-1">({{ ucfirst($employee['existing_status']) }})</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-users text-gray-400 text-4xl mb-4"></i>
                    <p class="text-gray-500">{{ __('attendance.no_employees_found') }}</p>
                    <p class="text-sm text-gray-400 mt-2">{{ __('attendance.no_employees_in_shift') }}</p>
                </div>
            @endif
        </div>
    </div>
@else
    {{-- Mensagem para selecionar shift primeiro --}}
    <div class="mb-6 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="p-8 text-center">
            <i class="fas fa-arrow-up text-blue-500 text-4xl mb-4"></i>
            <h4 class="text-lg font-medium text-gray-900 mb-2">{{ __('attendance.select_shift_first') }}</h4>
            <p class="text-gray-500">{{ __('attendance.select_shift_first_description') }}</p>
        </div>
    </div>
@endif
