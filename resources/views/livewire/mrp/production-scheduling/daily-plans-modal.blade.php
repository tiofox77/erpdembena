<!-- Modal para Planos Diários de Produção -->
<div x-cloak
    class="fixed inset-0 z-30 flex items-center justify-center overflow-auto bg-gray-800 bg-opacity-75 transition-opacity"
    style="width: 100vw; height: 100vh;"
    x-show="$wire.showDailyPlansModal"
    @keydown.escape.window="$wire.closeDailyPlansModal()">
    <div class="relative w-[95%] max-h-[90vh] mx-auto my-4"
        x-show="$wire.showDailyPlansModal"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        @click.away="$wire.closeDailyPlansModal()">

        <div class="relative bg-white rounded-lg shadow-xl overflow-hidden w-full flex flex-col max-h-[90vh]" @click.stop>
            
            <!-- Cabeçalho do Modal -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-3 py-3 sm:px-6 sm:py-4 flex justify-between items-center shadow-lg sticky top-0 z-10">
                <h3 class="text-sm sm:text-base md:text-lg font-medium text-white flex items-center animate__animated animate__fadeIn truncate max-w-[75%]">
                    <i class="fas fa-calendar-alt mr-2 text-yellow-300"></i>
                    {{ __('messages.daily_production_plans') }}
                    @if($viewingSchedule)
                        - {{ $viewingSchedule->schedule_number }}
                    @endif
                </h3>
                <button @click="$wire.closeDailyPlansModal()" class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Content -->
            <div class="bg-white p-3 sm:p-6 overflow-y-auto flex-grow">
                @if($viewingSchedule)
                
                <!-- Seleção de Turno -->
                <div class="mb-6 bg-blue-50 p-4 rounded-lg shadow-sm border border-blue-200 animate__animated animate__fadeIn">
                    <h4 class="text-base font-semibold text-blue-800 mb-3 flex items-center">
                        <i class="fas fa-user-clock text-blue-600 mr-2"></i>
                        {{ __('messages.select_shift_first') }}
                    </h4>
                    <p class="text-sm text-blue-600 mb-4">{{ __('messages.select_shift_instruction') }}</p>
                    
                    <div class="relative rounded-md shadow-sm max-w-lg">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user-clock text-purple-500"></i>
                        </div>
                        <select wire:model.live="selectedShiftId" 
                                class="pl-10 pr-10 py-3 block w-full text-base border-blue-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-md transition-all duration-200 font-medium">
                            <option value="">{{ __('messages.select_shift') }}</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}" class="py-2">{{ $shift->name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="fas fa-chevron-down text-blue-400"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Alerta de componentes insuficientes (apenas aviso, não bloqueante) -->
                @if($showComponentWarning)
                <div class="mb-4 p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded-md shadow-md animate__animated animate__fadeIn">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 pt-0.5">
                            <i class="fas fa-exclamation-triangle text-yellow-500 text-xl animate__animated animate__pulse animate__infinite"></i>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-yellow-800">
                                @if(count($insufficientComponents) == 1 && isset($insufficientComponents[0]['name']) && $insufficientComponents[0]['name'] == __('messages.invalid_bom_status'))
                                    <span class="font-bold">{{ __('messages.bom_inactive_warning') }}</span>
                                    <span class="block text-xs mt-1">{{ __('messages.activate_bom_instruction') }}</span>
                                    <span class="block text-xs mt-1 text-blue-700">{{ __('messages.continue_despite_warning') }}</span>
                                @else
                                    {{ __('messages.insufficient_components_warning') }} 
                                    <span class="font-bold">({{ __('messages.maximum_possible') }}: {{ $maxQuantityPossible }})</span>
                                    <span class="block text-xs mt-1 text-blue-700">{{ __('messages.continue_despite_warning') }}</span>
                                @endif
                            </p>
                            <div x-data="{showDetails: false}" class="mt-2">
                                <button @click="showDetails = !showDetails" type="button" 
                                    class="text-xs px-2 py-1 bg-blue-100 text-blue-700 hover:bg-blue-200 rounded-md font-medium flex items-center transition-all duration-200 hover:scale-105">
                                    <i class="fas" :class="showDetails ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                                    <span class="ml-1">{{ __('messages.view_details') }}</span>
                                </button>
                                <div x-show="showDetails" x-transition:enter="transition ease-out duration-300" 
                                    x-transition:enter-start="opacity-0 transform -translate-y-4" 
                                    x-transition:enter-end="opacity-100 transform translate-y-0"
                                    class="mt-3 text-xs text-gray-700 bg-white p-3 rounded-md border border-gray-200 shadow-sm">
                                    <ul class="space-y-2 divide-y divide-gray-100">
                                        @foreach($insufficientComponents as $component)
                                            <li class="border-l-2 border-red-400 pl-3 py-2 hover:bg-yellow-50 transition-all duration-200 rounded-r-md">
                                                <div class="font-medium flex items-center">
                                                    <i class="fas fa-box-open text-gray-700 mr-1"></i>
                                                    <span>{{ $component['name'] }}</span>
                                                    <span class="text-xs text-gray-500 ml-1">({{ $component['sku'] }})</span>
                                                </div>
                                                <div class="text-xs flex flex-wrap justify-between mt-2 gap-2">
                                                    <span class="text-gray-700 bg-green-50 px-2 py-1 rounded-md">
                                                        <i class="fas fa-cubes text-green-600 mr-1"></i> {{ __('messages.available') }}: <span class="font-bold">{{ is_numeric($component['available']) ? number_format((float)$component['available'], 2) : $component['available'] }}</span>
                                                    </span>
                                                    <span class="text-gray-700 bg-blue-50 px-2 py-1 rounded-md">
                                                        <i class="fas fa-clipboard-list text-blue-600 mr-1"></i> {{ __('messages.required') }}: <span class="font-bold">{{ is_numeric($component['required']) ? number_format((float)$component['required'], 2) : $component['required'] }}</span>
                                                    </span>
                                                    <span class="text-red-600 font-bold bg-red-50 px-2 py-1 rounded-md">
                                                        <i class="fas fa-exclamation-circle mr-1"></i> {{ __('messages.missing') }}: {{ is_numeric($component['missing']) ? number_format((float)$component['missing'], 2) : $component['missing'] }}
                                                    </span>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 animate__animated animate__fadeIn">
                    <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 transform hover:scale-[1.02]">
                        <span class="text-sm font-medium text-gray-500 flex items-center">
                            <i class="fas fa-hashtag text-blue-500 mr-2"></i> {{ __('messages.schedule_number') }}:
                        </span>
                        <p class="text-sm text-gray-900 mt-1 font-semibold">{{ $viewingSchedule->schedule_number }}</p>
                    </div>
                    <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 transform hover:scale-[1.02]">
                        <span class="text-sm font-medium text-gray-500 flex items-center">
                            <i class="fas fa-box-open text-green-500 mr-2"></i> {{ __('messages.product') }}:
                        </span>
                        <p class="text-sm text-gray-900 mt-1 font-semibold">{{ $viewingSchedule->product->name }}</p>
                        <p class="text-xs text-gray-500 italic">{{ $viewingSchedule->product->sku }}</p>
                    </div>
                    <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 transform hover:scale-[1.02]">
                        <span class="text-sm font-medium text-gray-500 flex items-center">
                            <i class="fas fa-cubes text-amber-500 mr-2"></i> {{ __('messages.total_quantity') }}:
                        </span>
                        <p class="text-sm text-gray-900 mt-1 font-semibold">{{ number_format($viewingSchedule->planned_quantity, 2) }}</p>
                    </div>
                    <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 transform hover:scale-[1.02]">
                        <span class="text-sm font-medium text-gray-500 flex items-center">
                            <i class="fas fa-calendar-range text-purple-500 mr-2"></i> {{ __('messages.period') }}:
                        </span>
                        <p class="text-sm text-gray-900 mt-1 font-semibold flex items-center">
                            <i class="fas fa-calendar-day text-blue-400 mr-1"></i>
                            @if($viewingSchedule->start_date && $viewingSchedule->end_date)
                                {{ $viewingSchedule->start_date->format('d/m/Y') }} 
                                <span class="mx-1 text-xs text-gray-500"><i class="fas fa-clock mx-1"></i>{{ $viewingSchedule->start_time }}</span>
                                <i class="fas fa-arrow-right mx-2 text-gray-400"></i>
                                <i class="fas fa-calendar-day text-blue-400 mr-1"></i> {{ $viewingSchedule->end_date->format('d/m/Y') }}
                                <span class="mx-1 text-xs text-gray-500"><i class="fas fa-clock mx-1"></i>{{ $viewingSchedule->end_time }}</span>
                            @else
                                {{ $viewingSchedule->start_date ?? '' }} {{ $viewingSchedule->start_time }} - 
                                {{ $viewingSchedule->end_date ?? '' }} {{ $viewingSchedule->end_time }}
                            @endif
                        </p>
                    </div>
                    <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 transform hover:scale-[1.02]">
                        <span class="text-sm font-medium text-gray-500 flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i> {{ __('messages.actual_quantity') }}:
                        </span>
                        @php
                            $totalActualQuantity = $viewingSchedule->dailyPlans->sum('actual_quantity');
                            $percentage = $viewingSchedule->planned_quantity > 0 ? min(100, round(($totalActualQuantity / $viewingSchedule->planned_quantity) * 100)) : 0;
                        @endphp
                        <p class="text-sm text-gray-900 mt-1 font-semibold">{{ number_format($totalActualQuantity, 2) }}</p>
                        <div class="mt-1 w-full bg-gray-200 rounded-full h-1.5">
                            <div class="bg-green-500 h-1.5 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                        <p class="text-xs text-right text-gray-500">{{ $percentage }}%</p>
                    </div>
                    <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 transform hover:scale-[1.02]">
                        <span class="text-sm font-medium text-gray-500 flex items-center">
                            <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i> {{ __('messages.defect_quantity') }}:
                        </span>
                        @php
                            $totalDefectQuantity = $viewingSchedule->dailyPlans->sum('defect_quantity');
                            $defectPercentage = $viewingSchedule->planned_quantity > 0 ? min(100, round(($totalDefectQuantity / $viewingSchedule->planned_quantity) * 100)) : 0;
                        @endphp
                        <p class="text-sm text-gray-900 mt-1 font-semibold">{{ number_format($totalDefectQuantity, 2) }}</p>
                        <div class="mt-1 w-full bg-gray-200 rounded-full h-1.5">
                            <div class="bg-red-500 h-1.5 rounded-full" style="width: {{ $defectPercentage }}%"></div>
                        </div>
                        <p class="text-xs text-right text-gray-500">{{ $defectPercentage }}%</p>
                    </div>
                    <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 transform hover:scale-[1.02]">
                        <span class="text-sm font-medium text-gray-500 flex items-center">
                            <i class="fas fa-info-circle text-blue-500 mr-2"></i> {{ __('messages.status') }}:
                        </span>
                        <p class="text-sm text-gray-900 mt-2">
                            <span class="px-3 py-1 inline-flex items-center text-xs leading-5 font-semibold rounded-full animate__animated animate__fadeIn
                            @if($viewingSchedule->status == 'draft') bg-gray-100 text-gray-800
                            @elseif($viewingSchedule->status == 'confirmed') bg-blue-100 text-blue-800
                            @elseif($viewingSchedule->status == 'in_progress') bg-yellow-100 text-yellow-800
                            @elseif($viewingSchedule->status == 'completed') bg-green-100 text-green-800
                            @elseif($viewingSchedule->status == 'cancelled') bg-red-100 text-red-800
                            @endif">
                                <i class="mr-1 fas 
                                @if($viewingSchedule->status == 'draft') fa-pencil-alt
                                @elseif($viewingSchedule->status == 'confirmed') fa-check
                                @elseif($viewingSchedule->status == 'in_progress') fa-spinner fa-spin
                                @elseif($viewingSchedule->status == 'completed') fa-check-double
                                @elseif($viewingSchedule->status == 'cancelled') fa-ban
                                @endif"></i>
                                {{ __('messages.status_' . $viewingSchedule->status) }}
                            </span>
                        </p>
                    </div>
                    
                    <!-- Seção de impacto das falhas na produção -->
                    @if(isset($viewingSchedule) && $viewingSchedule)
                    <!-- Breakdown impact section removed as requested -->
                    @endif
                @else
                <div class="p-4 rounded-md bg-yellow-50 border border-yellow-100">
                    <p class="text-yellow-700">{{ __('messages.no_schedule_selected') }}</p>
                </div>
                @endif
                </div>
                
                @if($viewingSchedule)
                <div class="mt-4">
                    <h4 class="text-lg font-semibold text-gray-900 mb-2 flex items-center">
                        <i class="fas fa-calendar-day text-blue-600 mr-2 animate__animated animate__fadeIn"></i>
                        {{ __('messages.daily_plans') }}
                        @if($selectedShiftId)
                            <span class="ml-2 bg-purple-100 text-purple-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded-full">{{ $selectedShiftName }}</span>
                        @endif
                    </h4>
                    
                    <div class="mt-2 overflow-x-auto -mx-3 sm:mx-0">
                    @if($selectedShiftId)
                        <table class="min-w-full divide-y divide-gray-200 shadow-sm rounded-lg overflow-hidden table-auto md:table-fixed">
                            <thead class="bg-gradient-to-r from-blue-50 to-blue-100">
                                <tr>
                                    <th scope="col" class="px-4 py-3">
                                        <div class="flex items-center">
                                            <i class="fas fa-calendar-day text-blue-500 mr-2"></i>
                                            {{ __('messages.date') }}
                                        </div>
                                    </th>
                                    <th scope="col" class="px-4 py-3 md:table-cell">
                                        <div class="flex items-center">
                                            <i class="fas fa-clock text-blue-500 mr-2"></i>
                                            {{ __('messages.time_period') }}
                                        </div>
                                    </th>

                                    <th scope="col" class="px-4 py-3 hidden md:table-cell">
                                        <div class="flex items-center">
                                            <i class="fas fa-clipboard-list text-blue-500 mr-2"></i>
                                            {{ __('messages.planned_quantity') }}
                                        </div>
                                    </th>
                                    <th scope="col" class="px-4 py-3 hidden md:table-cell">
                                        <div class="flex items-center">
                                            <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                                            {{ __('messages.defect_quantity') }}
                                        </div>
                                    </th>
                                    <th scope="col" class="px-4 py-3 hidden md:table-cell">
                                        <div class="flex items-center">
                                            <i class="fas fa-check-square text-blue-500 mr-2"></i>
                                            {{ __('messages.actual_quantity') }}
                                        </div>
                                    </th>
                                    <th scope="col" class="px-4 py-3 hidden md:table-cell">
                                        <div class="flex items-center">
                                            <i class="fas fa-flag text-blue-500 mr-2"></i>
                                            {{ __('messages.status') }}
                                        </div>
                                    </th>
                                    <th scope="col" class="px-4 py-3 hidden md:table-cell">
                                        <div class="flex items-center">
                                            <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                                            {{ __('messages.breakdown') }}
                                        </div>
                                    </th>
                                    <th scope="col" class="px-4 py-3 hidden md:table-cell">
                                        <div class="flex items-center">
                                            <i class="fas fa-tag text-orange-500 mr-2"></i>
                                            {{ __('messages.failure_details') }}
                                        </div>
                                    </th>
                                    <th scope="col" class="px-4 py-3 hidden md:table-cell">
                                        <div class="flex items-center">
                                            <i class="fas fa-cogs text-blue-500 mr-2"></i>
                                            {{ __('messages.actions') }}
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @if(isset($filteredDailyPlans) && count($filteredDailyPlans) > 0)
                                @foreach($filteredDailyPlans as $index => $plan)
                                <tr class="hover:bg-blue-50 transition-colors duration-150 animate__animated animate__fadeIn block md:table-row mb-4 md:mb-0 border rounded-lg md:border-none shadow-md md:shadow-none bg-white">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        <div class="flex items-center">
                                            <i class="fas fa-calendar-day text-blue-400 mr-2"></i>
                                            <span class="font-medium">{{ \Carbon\Carbon::parse($plan['production_date'])->format('d/m/Y') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        <div class="flex items-center space-x-1">
                                            @if(isset($selectedShiftId) && $selectedShiftId)
                                                @php
                                                    $shift = $shifts->firstWhere('id', $selectedShiftId);
                                                    $startTime = $shift ? $shift->start_time : '00:00';
                                                    $endTime = $shift ? $shift->end_time : '00:00';
                                                @endphp
                                                <span class="text-gray-600"><i class="fas fa-hourglass-start text-green-500 mr-1"></i>{{ $startTime }}</span>
                                                <i class="fas fa-arrow-right text-gray-400"></i>
                                                <span class="text-gray-600"><i class="fas fa-hourglass-end text-amber-500 mr-1"></i>{{ $endTime }}</span>
                                            @else
                                                <span class="text-gray-500 italic">{{ __('messages.select_shift_first') }}</span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        <div class="relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-cubes text-blue-400"></i>
                                            </div>
                                            <input type="number" step="0.01" min="0" 
                                                class="pl-9 pr-12 border-gray-300 focus:ring-blue-500 focus:border-blue-500 block w-full border rounded-lg transition-all duration-200 text-sm" 
                                                style="-moz-appearance: auto; -webkit-appearance: auto; appearance: auto; padding-right: 20px;"
                                                wire:model.defer="filteredDailyPlans.{{ $index }}.planned_quantity"
                                                placeholder="0.00">
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        <div class="relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-red-400"></i>
                                            </div>
                                            <input type="number" step="0.01" min="0" 
                                                class="pl-9 pr-12 border-gray-300 focus:ring-red-500 focus:border-red-500 block w-full border rounded-lg transition-all duration-200 text-sm" 
                                                style="-moz-appearance: auto; -webkit-appearance: auto; appearance: auto; padding-right: 20px;"
                                                wire:model.defer="filteredDailyPlans.{{ $index }}.defect_quantity"
                                                placeholder="0.00">
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        <div class="relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-check-circle text-green-400"></i>
                                            </div>
                                            <input type="number" step="0.01" min="0" 
                                                class="pl-9 pr-12 border-gray-300 focus:ring-green-500 focus:border-green-500 block w-full border rounded-lg transition-all duration-200 text-sm" 
                                                style="-moz-appearance: auto; -webkit-appearance: auto; appearance: auto; padding-right: 20px;"
                                                wire:model.defer="filteredDailyPlans.{{ $index }}.actual_quantity"
                                                placeholder="0.00">
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm block md:table-cell before:content-['Status:'] md:before:content-none before:font-medium before:text-gray-700 before:block md:before:hidden">
                                        <div class="relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-flag text-blue-400"></i>
                                            </div>
                                            <select wire:model.defer="filteredDailyPlans.{{ $index }}.status" 
                                                class="pl-9 block w-full py-2 text-sm border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-md transition-all duration-200">
                                                <option value="pending">{{ __('messages.status_pending') }}</option>
                                                <option value="in_progress">{{ __('messages.status_in_progress') }}</option>
                                                <option value="completed">{{ __('messages.status_completed') }}</option>
                                                <option value="cancelled">{{ __('messages.status_cancelled') }}</option>
                                            </select>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm block md:table-cell before:content-['Breakdown:'] md:before:content-none before:font-medium before:text-gray-700 before:block md:before:hidden">
                                        <div class="flex items-center space-x-2">
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" wire:model.defer="filteredDailyPlans.{{ $index }}.has_breakdown" class="form-checkbox h-5 w-5 text-red-600 transition duration-150 ease-in-out rounded">
                                                <span class="ml-2 text-sm text-gray-700">{{ __('messages.yes') }}</span>
                                            </label>
                                            <div x-data="{ show: false }" x-show.transition.opacity="$wire.filteredDailyPlans && $wire.filteredDailyPlans[{{ $index }}] && $wire.filteredDailyPlans[{{ $index }}].has_breakdown" class="relative rounded-md shadow-sm flex-1 ml-2">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-clock text-gray-400"></i>
                                                </div>
                                                <input type="number" wire:model.defer="filteredDailyPlans.{{ $index }}.breakdown_minutes" 
                                                    class="pl-9 block w-full py-2 text-sm border-gray-300 focus:ring-red-500 focus:border-red-500 rounded-md transition-all duration-200" 
                                                    placeholder="{{ __('messages.minutes') }}">
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm block md:table-cell before:content-['Failure_Details:'] md:before:content-none before:font-medium before:text-gray-700 before:block md:before:hidden">
                                        <div x-data="{ show: false }" x-show.transition.opacity="$wire.filteredDailyPlans && $wire.filteredDailyPlans[{{ $index }}] && $wire.filteredDailyPlans[{{ $index }}].has_breakdown">
                                            <div class="mb-2 relative rounded-md shadow-sm">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-tag text-orange-400"></i>
                                                </div>
                                                <select wire:model.defer="filteredDailyPlans.{{ $index }}.failure_category_id" 
                                                    class="pl-8 pr-2 block w-full py-1.5 text-xs sm:text-sm border-gray-300 focus:outline-none focus:ring-orange-500 focus:border-orange-500 rounded-md transition-all duration-200 truncate">
                                                    <option value="">{{ __('messages.select_failure_category') }}</option>
                                                    @foreach($failureCategories as $category)
                                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="relative rounded-md shadow-sm">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-list text-blue-400"></i>
                                                </div>
                                                <select wire:model.defer="filteredDailyPlans.{{ $index }}.failure_root_causes" 
                                                    class="pl-8 pr-2 block w-full py-1 text-xs sm:text-sm border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-md transition-all duration-200 truncate"
                                                    multiple size="3">
                                                    @foreach($failureRootCauses as $rootCause)
                                                        <option value="{{ $rootCause->id }}">{{ $rootCause->name }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="text-xs text-gray-500 mt-1 italic">{{ __('messages.select_multiple') }}</div>
                                            </div>
                                        </div>
                                        <div x-data="{ show: true }" x-show.transition.opacity="!($wire.filteredDailyPlans && $wire.filteredDailyPlans[{{ $index }}] && $wire.filteredDailyPlans[{{ $index }}].has_breakdown)" class="text-sm text-gray-500 italic">
                                            {{ __('messages.no_breakdown_reported') }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        <div class="flex space-x-2">
                                            <button type="button" wire:click.prevent="saveDailyPlan({{ $index }})" 
                                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105 shadow-sm"
                                                    wire:loading.attr="disabled">
                                                <i class="fas fa-save mr-1 text-blue-200"></i>
                                                <span wire:loading.remove wire:target="saveDailyPlan({{ $index }})">{{ __('messages.save') }}</span>
                                                <span wire:loading wire:target="saveDailyPlan({{ $index }})" class="inline-flex items-center">
                                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    {{ __('messages.saving') }}...
                                                </span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 block w-full">
                                        <div class="flex flex-col items-center justify-center space-y-3 animate__animated animate__fadeIn">
                                            <i class="fas fa-calendar-times text-4xl text-gray-300"></i>
                                            <p>{{ __('messages.no_daily_plans_found') }}</p>
                                            <p class="text-xs text-gray-400">{{ __('messages.empty_daily_plans_message') }}</p>
                                        </div>
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    @else
                        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-100 text-center">
                            <i class="fas fa-arrow-up text-yellow-400 text-2xl mb-2 animate__animated animate__bounce"></i>
                            <p class="text-yellow-700">{{ __('messages.please_select_shift_first') }}</p>
                        </div>
                    @endif
                    </div>
                </div>
                @endif
            </div>
            
            <!-- Rodapé do Modal -->
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-3 py-3 sm:px-6 flex flex-col sm:flex-row justify-between items-center gap-2 shadow-inner border-t border-gray-200 sticky bottom-0 z-10">
                <div class="text-xs text-gray-500 italic flex items-center text-center sm:text-left">
                    <i class="fas fa-info-circle mr-1 text-blue-400"></i>
                    <span>{{ __('messages.daily_plans_info') }}</span>
                </div>
                <div class="flex">
                    <button type="button" wire:click="closeDailyPlansModal" 
                        class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <i class="fas fa-times mr-2"></i>
                        {{ __('messages.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:initialized', function () {
        Livewire.on('dailyPlansModalOpened', function() {
            setTimeout(function() {
                initBreakdownCharts();
            }, 200);
        });
        
        // Reinitialize charts whenever data changes
        Livewire.on('breakdownDataUpdated', function() {
            initBreakdownCharts();
        });
    });
    
    function initBreakdownCharts() {
        // Clear previous charts if they exist
        if (window.breakdownEfficiencyChart) {
            window.breakdownEfficiencyChart.destroy();
        }
        if (window.productionComparisonChart) {
            window.productionComparisonChart.destroy();
        }
        
        const breakdownCtx = document.getElementById('breakdownEfficiencyChart');
        const productionCtx = document.getElementById('productionComparisonChart');
        
        // Only proceed if chart elements exist
        if (!breakdownCtx || !productionCtx) return;
        
        // Get data from blade variables
        @if(isset($viewingSchedule) && $viewingSchedule && isset($viewingSchedule->breakdown_impact))
            const breakdownMinutes = {{ $viewingSchedule->breakdown_impact['total_breakdown_minutes'] ?? 0 }};
            const efficiencyPercentage = {{ $viewingSchedule->breakdown_impact['efficiency_percentage'] ?? 0 }};
            const plannedProduction = {{ $viewingSchedule->breakdown_impact['total_planned_production'] ?? 0 }};
            const actualProduction = {{ $viewingSchedule->breakdown_impact['total_actual_production'] ?? 0 }};
            const efficiencyLoss = {{ $viewingSchedule->breakdown_impact['total_efficiency_loss'] ?? 0 }};
            
            // Create efficiency vs breakdown chart
            window.breakdownEfficiencyChart = new Chart(breakdownCtx, {
                type: 'bar',
                data: {
                    labels: ['{{ __('messages.breakdown_and_efficiency') }}'],
                    datasets: [
                        {
                            label: '{{ __('messages.breakdown_minutes') }}',
                            data: [breakdownMinutes],
                            backgroundColor: 'rgba(239, 68, 68, 0.7)',
                            borderColor: 'rgba(239, 68, 68, 1)',
                            borderWidth: 1,
                            yAxisID: 'y-axis-1'
                        },
                        {
                            label: '{{ __('messages.efficiency') }}',
                            data: [efficiencyPercentage],
                            backgroundColor: 'rgba(59, 130, 246, 0.7)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 1,
                            yAxisID: 'y-axis-2'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            ticks: {
                                autoSkip: false,
                                maxRotation: 0
                            }
                        },
                        'y-axis-1': {
                            type: 'linear',
                            position: 'left',
                            title: {
                                display: true,
                                text: '{{ __('messages.minutes') }}'
                            },
                            grid: {
                                display: false
                            },
                            beginAtZero: true
                        },
                        'y-axis-2': {
                            type: 'linear',
                            position: 'right',
                            title: {
                                display: true,
                                text: '%'
                            },
                            grid: {
                                display: false
                            },
                            min: 0,
                            max: 100
                        }
                    },
                    plugins: {
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        },
                        legend: {
                            position: 'top',
                        }
                    }
                }
            });
            
            // Create production comparison chart
            window.productionComparisonChart = new Chart(productionCtx, {
                type: 'bar',
                data: {
                    labels: ['{{ __('messages.production_quantities') }}'],
                    datasets: [
                        {
                            label: '{{ __('messages.planned') }}',
                            data: [plannedProduction],
                            backgroundColor: 'rgba(59, 130, 246, 0.7)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 1
                        },
                        {
                            label: '{{ __('messages.actual') }}',
                            data: [actualProduction],
                            backgroundColor: 'rgba(34, 197, 94, 0.7)',
                            borderColor: 'rgba(34, 197, 94, 1)',
                            borderWidth: 1
                        },
                        {
                            label: '{{ __('messages.efficiency_loss') }}',
                            data: [efficiencyLoss],
                            backgroundColor: 'rgba(239, 68, 68, 0.7)',
                            borderColor: 'rgba(239, 68, 68, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            ticks: {
                                autoSkip: false,
                                maxRotation: 0
                            }
                        },
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: '{{ __('messages.units') }}'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        },
                        legend: {
                            position: 'top',
                        }
                    }
                }
            });
        @endif
    }
</script>
