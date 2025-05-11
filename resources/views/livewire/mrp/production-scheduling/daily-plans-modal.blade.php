<!-- Modal para Planos Diários de Produção -->
<div x-cloak
    class="fixed inset-0 z-30 flex items-center justify-center overflow-auto bg-gray-800 bg-opacity-75 transition-opacity"
    x-show="$wire.showDailyPlansModal"
    @keydown.escape.window="$wire.closeDailyPlansModal()">
    <div class="relative w-full max-w-4xl mx-auto my-8 px-4 sm:px-0"
        x-show="$wire.showDailyPlansModal"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        @click.away="$wire.closeDailyPlansModal()">

        <div class="relative bg-white rounded-lg shadow-xl overflow-hidden" @click.stop>
            
            <!-- Cabeçalho do Modal -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-4 sm:px-6 flex justify-between items-center">
                <h3 class="text-lg font-medium text-white">
                    <i class="fas fa-calendar-alt mr-2"></i>
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
                <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <span class="text-sm font-medium text-gray-500">{{ __('messages.schedule_number') }}:</span>
                        <p class="text-sm text-gray-900">{{ $viewingSchedule->schedule_number }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">{{ __('messages.product') }}:</span>
                        <p class="text-sm text-gray-900">{{ $viewingSchedule->product->name }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">{{ __('messages.total_quantity') }}:</span>
                        <p class="text-sm text-gray-900">{{ number_format($viewingSchedule->planned_quantity, 2) }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">{{ __('messages.period') }}:</span>
                        <p class="text-sm text-gray-900">
                            @if($viewingSchedule->start_date && $viewingSchedule->end_date)
                                {{ $viewingSchedule->start_date->format('d/m/Y') }} {{ $viewingSchedule->start_time }} - 
                                {{ $viewingSchedule->end_date->format('d/m/Y') }} {{ $viewingSchedule->end_time }}
                            @else
                                {{ $viewingSchedule->start_date ?? '' }} {{ $viewingSchedule->start_time }} - 
                                {{ $viewingSchedule->end_date ?? '' }} {{ $viewingSchedule->end_time }}
                            @endif
                        </p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">{{ __('messages.actual_quantity') }}:</span>
                        <p class="text-sm text-gray-900">{{ number_format($viewingSchedule->actual_quantity, 2) }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">{{ __('messages.status') }}:</span>
                        <p class="text-sm text-gray-900">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ $viewingSchedule->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $viewingSchedule->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $viewingSchedule->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $viewingSchedule->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $viewingSchedule->status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                                {{ $viewingSchedule->status === 'confirmed' ? 'bg-indigo-100 text-indigo-800' : '' }}">
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
                    <h4 class="text-lg font-medium text-gray-900 mb-2">{{ __('messages.daily_plans') }}</h4>
                    
                    <div class="mt-2 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('messages.date') }}
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('messages.hours') }}
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('messages.planned_quantity') }}
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('messages.actual_quantity') }}
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('messages.status') }}
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('messages.actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @if(isset($dailyPlans) && count($dailyPlans) > 0)
                                @foreach($dailyPlans as $index => $plan)
                                <tr>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($plan['production_date'])->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                        {{ $plan['start_time'] }} - {{ $plan['end_time'] }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                        <input type="number" step="0.001" min="0" 
                                               class="border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border rounded-md" 
                                               wire:model.defer="dailyPlans.{{ $index }}.planned_quantity">
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                        <input type="number" step="0.001" min="0" 
                                               class="border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border rounded-md" 
                                               wire:model.defer="dailyPlans.{{ $index }}.actual_quantity">
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                        <select wire:model.defer="dailyPlans.{{ $index }}.status" 
                                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                            <option value="pending">{{ __('messages.status_pending') }}</option>
                                            <option value="in_progress">{{ __('messages.status_in_progress') }}</option>
                                            <option value="completed">{{ __('messages.status_completed') }}</option>
                                            <option value="cancelled">{{ __('messages.status_cancelled') }}</option>
                                        </select>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                        <button type="button" wire:click="updateDailyPlan({{ $index }})" 
                                                class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            {{ __('messages.save') }}
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="6" class="px-4 py-4 text-center text-sm text-gray-500">
                                        {{ __('messages.no_daily_plans_found') }}
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
            <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end">
                <button type="button" wire:click="closeDailyPlansModal" 
                    class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out">
                    <i class="fas fa-times mr-2"></i>
                    {{ __('messages.close') }}
                </button>
            </div>
        </div>
    </div>
</div>
