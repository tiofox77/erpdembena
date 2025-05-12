<!-- Modal para Planos Diários de Produção -->
<div x-cloak
    class="fixed inset-0 z-30 flex items-center justify-center overflow-auto bg-gray-800 bg-opacity-75 transition-opacity"
    style="width: 100vw; height: 100vh;"
    x-show="$wire.showDailyPlansModal"
    @keydown.escape.window="$wire.closeDailyPlansModal()">
    <div class="relative w-[95%] mx-auto my-4"
        x-show="$wire.showDailyPlansModal"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        @click.away="$wire.closeDailyPlansModal()">

        <div class="relative bg-white rounded-lg shadow-xl overflow-hidden w-full" @click.stop>
            
            <!-- Cabeçalho do Modal -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-4 py-4 sm:px-6 flex justify-between items-center shadow-lg">
                <h3 class="text-lg font-medium text-white flex items-center animate__animated animate__fadeIn">
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
            <div class="bg-white p-4 sm:p-6">
                @if($viewingSchedule)
                
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
                <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4 animate__animated animate__fadeIn">
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
                        <p class="text-sm text-gray-900 mt-1 font-semibold">{{ number_format($viewingSchedule->actual_quantity, 2) }}</p>
                        <div class="mt-1 w-full bg-gray-200 rounded-full h-1.5">
                            @php
                                $percentage = $viewingSchedule->planned_quantity > 0 ? min(100, round(($viewingSchedule->actual_quantity / $viewingSchedule->planned_quantity) * 100)) : 0;
                            @endphp
                            <div class="bg-green-500 h-1.5 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                        <p class="text-xs text-right text-gray-500">{{ $percentage }}%</p>
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
                    </h4>
                    
                    <div class="mt-2 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 shadow-sm rounded-lg overflow-hidden">
                            <thead class="bg-gradient-to-r from-blue-50 to-blue-100">
                                <tr>
                                    <th scope="col" class="px-4 py-3">
                                        <div class="flex items-center">
                                            <i class="fas fa-calendar-day text-blue-500 mr-2"></i>
                                            {{ __('messages.date') }}
                                        </div>
                                    </th>
                                    <th scope="col" class="px-4 py-3">
                                        <div class="flex items-center">
                                            <i class="fas fa-clock text-blue-500 mr-2"></i>
                                            {{ __('messages.time_period') }}
                                        </div>
                                    </th>
                                    <th scope="col" class="px-4 py-3">
                                        <div class="flex items-center">
                                            <i class="fas fa-clipboard-list text-blue-500 mr-2"></i>
                                            {{ __('messages.planned_quantity') }}
                                        </div>
                                    </th>
                                    <th scope="col" class="px-4 py-3">
                                        <div class="flex items-center">
                                            <i class="fas fa-check-square text-blue-500 mr-2"></i>
                                            {{ __('messages.actual_quantity') }}
                                        </div>
                                    </th>
                                    <th scope="col" class="px-4 py-3">
                                        <div class="flex items-center">
                                            <i class="fas fa-flag text-blue-500 mr-2"></i>
                                            {{ __('messages.status') }}
                                        </div>
                                    </th>
                                    <th scope="col" class="px-4 py-3">
                                        <div class="flex items-center">
                                            <i class="fas fa-cogs text-blue-500 mr-2"></i>
                                            {{ __('messages.actions') }}
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @if(isset($dailyPlans) && count($dailyPlans) > 0)
                                @foreach($dailyPlans as $index => $plan)
                                <tr class="hover:bg-blue-50 transition-colors duration-150 animate__animated animate__fadeIn">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        <div class="flex items-center">
                                            <i class="fas fa-calendar-day text-blue-400 mr-2"></i>
                                            <span class="font-medium">{{ \Carbon\Carbon::parse($plan['production_date'])->format('d/m/Y') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        <div class="flex items-center space-x-1">
                                            <span class="text-gray-600"><i class="fas fa-hourglass-start text-green-500 mr-1"></i>{{ $plan['start_time'] }}</span>
                                            <i class="fas fa-arrow-right text-gray-400"></i>
                                            <span class="text-gray-600"><i class="fas fa-hourglass-end text-amber-500 mr-1"></i>{{ $plan['end_time'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        <div class="relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-cubes text-blue-400"></i>
                                            </div>
                                            <input type="number" step="0.001" min="0" 
                                                class="pl-9 pr-8 border-gray-300 focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border rounded-lg transition-all duration-200" 
                                                wire:model.defer="dailyPlans.{{ $index }}.planned_quantity"
                                                placeholder="0.000">
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        <div class="relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-check-circle text-green-400"></i>
                                            </div>
                                            <input type="number" step="0.001" min="0" 
                                                class="pl-9 pr-8 border-gray-300 focus:ring-green-500 focus:border-green-500 block w-full sm:text-sm border rounded-lg transition-all duration-200" 
                                                wire:model.defer="dailyPlans.{{ $index }}.actual_quantity"
                                                placeholder="0.000">
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        <div class="relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-flag text-blue-400"></i>
                                            </div>
                                            <select wire:model.defer="dailyPlans.{{ $index }}.status" 
                                                class="pl-9 block w-full py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg transition-all duration-200">
                                                <option value="pending">{{ __('messages.status_pending') }}</option>
                                                <option value="in_progress">{{ __('messages.status_in_progress') }}</option>
                                                <option value="completed">{{ __('messages.status_completed') }}</option>
                                                <option value="cancelled">{{ __('messages.status_cancelled') }}</option>
                                            </select>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        <button type="button" wire:click="updateDailyPlan({{ $index }})" 
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105 shadow-sm">
                                            <i class="fas fa-save mr-1 text-blue-200"></i>
                                            {{ __('messages.save') }}
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">
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
                    </div>
                </div>
                @endif
            </div>
            
            <!-- Rodapé do Modal -->
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 py-3 sm:px-6 flex justify-between items-center shadow-inner border-t border-gray-200">
                <div class="text-xs text-gray-500 italic flex items-center">
                    <i class="fas fa-info-circle mr-1 text-blue-400"></i>
                    <span>{{ __('messages.daily_plans_info') }}</span>
                </div>
                <button type="button" wire:click="closeDailyPlansModal" 
                    class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                    <i class="fas fa-times mr-2"></i>
                    {{ __('messages.close') }}
                </button>
            </div>
        </div>
    </div>
</div>
