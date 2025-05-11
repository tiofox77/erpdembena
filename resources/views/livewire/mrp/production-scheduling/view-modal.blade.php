<!-- Modal para Visualizar Programação de Produção -->
<div x-cloak
    class="fixed inset-0 z-30 flex items-center justify-center bg-gray-800 bg-opacity-75 transition-opacity"
    x-show="$wire.showViewModal"
    @keydown.escape.window="$wire.closeViewModal()">
    <div class="relative w-full max-w-[90%] sm:max-w-[85%] lg:max-w-[80%] mx-auto h-[90vh] flex flex-col px-2 sm:px-4"
        x-show="$wire.showViewModal"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        @click.away="$wire.closeViewModal()">
        <div class="relative bg-white rounded-lg shadow-xl flex flex-col h-full" @click.stop>
            <!-- Cabeçalho do Modal -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3 sm:px-6 flex justify-between items-center flex-shrink-0">
                <h3 class="text-lg font-medium text-white">
                    <i class="fas fa-eye mr-2"></i>
                    {{ __('messages.production_schedule_details') }}
                </h3>
                <button @click="$wire.closeViewModal()" class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            @if($viewingSchedule)
                <div class="flex-1 p-4 sm:p-5 overflow-y-auto">
                    <!-- Badges de Status e Prioridade -->
                    <div class="mb-6 flex justify-center space-x-4">
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium
                            @if($viewingSchedule->status === 'draft') bg-gray-100 text-gray-800
                            @elseif($viewingSchedule->status === 'confirmed') bg-blue-100 text-blue-800
                            @elseif($viewingSchedule->status === 'in_progress') bg-yellow-100 text-yellow-800
                            @elseif($viewingSchedule->status === 'completed') bg-green-100 text-green-800
                            @else bg-red-100 text-red-800
                            @endif">
                            <i class="mr-1.5
                                @if($viewingSchedule->status === 'draft') fas fa-pencil-alt
                                @elseif($viewingSchedule->status === 'confirmed') fas fa-check-circle
                                @elseif($viewingSchedule->status === 'in_progress') fas fa-hourglass-half
                                @elseif($viewingSchedule->status === 'completed') fas fa-flag-checkered
                                @else fas fa-ban
                                @endif"></i>
                            {{ __('messages.' . $viewingSchedule->status) }}
                        </span>
                        
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium
                            @if($viewingSchedule->priority === 'low') bg-blue-100 text-blue-800
                            @elseif($viewingSchedule->priority === 'medium') bg-green-100 text-green-800
                            @elseif($viewingSchedule->priority === 'high') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800
                            @endif">
                            <i class="mr-1.5
                                @if($viewingSchedule->priority === 'low') fas fa-arrow-down
                                @elseif($viewingSchedule->priority === 'medium') fas fa-equals
                                @elseif($viewingSchedule->priority === 'high') fas fa-arrow-up
                                @else fas fa-exclamation-circle
                                @endif"></i>
                            {{ __('messages.priority_' . $viewingSchedule->priority) }}
                        </span>
                    </div>
                    
                    <!-- Grade de Informações -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
                        <!-- Coluna 1: Informações da Programação -->
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                {{ __('messages.schedule_information') }}
                            </h4>
                            
                            <dl class="grid grid-cols-1 gap-x-3 gap-y-3 sm:grid-cols-2 lg:grid-cols-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.product') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $viewingSchedule->product->name }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.schedule_number') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $viewingSchedule->schedule_number }}
                                    </dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.status') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $viewingSchedule->status == 'completed' ? 'bg-green-100 text-green-800' : 
                                               ($viewingSchedule->status == 'in_progress' ? 'bg-blue-100 text-blue-800' : 
                                               ($viewingSchedule->status == 'confirmed' ? 'bg-indigo-100 text-indigo-800' : 
                                               ($viewingSchedule->status == 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'))) }}">
                                            {{ __('messages.status_' . $viewingSchedule->status) }}
                                        </span>
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.period') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $viewingSchedule->start_date->format('d/m/Y') }} {{ $viewingSchedule->start_time ?? '00:00' }} - 
                                        {{ $viewingSchedule->end_date->format('d/m/Y') }} {{ $viewingSchedule->end_time ?? '23:59' }}
                                    </dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.completion') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $viewingSchedule->completionPercentage }}%"></div>
                                        </div>
                                        <span class="text-xs">{{ $viewingSchedule->completionPercentage }}%</span>
                                    </dd>
                                </div>
                                
                                @if($viewingSchedule->status === 'completed')
                                <div class="sm:col-span-3 border border-green-100 bg-green-50 p-3 rounded-md">
                                    <dt class="text-sm font-medium text-green-700">{{ __('messages.production_results') }}:</dt>
                                    <dd class="mt-2">
                                        <div class="grid grid-cols-2 gap-3">
                                            <!-- Quantidade Planejada vs Real -->
                                            <div class="bg-white rounded-md p-2 border border-green-200">
                                                <div class="flex justify-between items-center mb-1">
                                                    <span class="text-xs font-medium text-gray-500">{{ __('messages.planned_vs_actual') }}</span>
                                                    @php
                                                        $completionPercent = $viewingSchedule->planned_quantity > 0 
                                                            ? round(($viewingSchedule->actual_quantity / $viewingSchedule->planned_quantity) * 100, 1)
                                                            : 0;
                                                    @endphp
                                                    <span class="text-xs font-semibold @if($completionPercent >= 100) text-green-600 @elseif($completionPercent >= 80) text-yellow-600 @else text-red-600 @endif">
                                                        {{ $completionPercent }}%
                                                    </span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-sm font-semibold">
                                                        <i class="fas fa-tasks text-blue-600"></i> {{ number_format($viewingSchedule->planned_quantity, 2) }}
                                                    </span>
                                                    <span class="text-sm font-semibold">
                                                        <i class="fas fa-flag-checkered text-green-600"></i> {{ number_format($viewingSchedule->actual_quantity, 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <!-- Status de Atraso -->
                                            <div class="bg-white rounded-md p-2 border border-green-200">
                                                <div class="text-xs font-medium text-gray-500 mb-1">{{ __('messages.completion_status') }}</div>
                                                <div class="flex items-center justify-center h-6">
                                                    @if($viewingSchedule->is_delayed)
                                                        <span class="text-red-600 font-semibold flex items-center">
                                                            <i class="fas fa-exclamation-circle mr-1"></i> {{ __('messages.delayed') }}
                                                        </span>
                                                    @else
                                                        <span class="text-green-600 font-semibold flex items-center">
                                                            <i class="fas fa-check-circle mr-1"></i> {{ __('messages.on_time') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </dd>
                                </div>
                                @elseif($viewingSchedule->status === 'in_progress')
                                <div class="sm:col-span-3 border border-blue-100 bg-blue-50 p-3 rounded-md">
                                    <dt class="text-sm font-medium text-blue-700">{{ __('messages.production_estimate') }}:</dt>
                                    <dd class="mt-2">
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                            <!-- Tempo decorrido -->
                                            <div class="bg-white p-2 rounded border border-blue-200">
                                                <span class="text-xs font-medium text-gray-500 block">{{ __('messages.time_progress') }}</span>
                                                <div class="w-full bg-gray-200 rounded-full h-2.5 mt-1">
                                                    <div class="bg-green-600 h-2.5 rounded-full" 
                                                         style="width: {{ $viewingSchedule->estimatedTimeRemaining['percentage'] }}%"></div>
                                                </div>
                                                <span class="text-xs block mt-1">{{ $viewingSchedule->estimatedTimeRemaining['percentage'] }}% {{ __('messages.elapsed') }}</span>
                                            </div>
                                            
                                            <!-- Produção esperada vs real -->
                                            <div class="bg-white p-2 rounded border border-blue-200">
                                                <span class="text-xs font-medium text-gray-500 block">{{ __('messages.expected_vs_actual') }}</span>
                                                <div class="flex justify-between items-center mt-1">
                                                    <span class="text-sm font-medium">{{ number_format($viewingSchedule->estimatedTimeRemaining['expected_production'], 2) }}</span>
                                                    <span class="text-xs text-gray-500">vs</span>
                                                    <span class="text-sm font-medium {{ $viewingSchedule->estimatedTimeRemaining['actual_production'] >= $viewingSchedule->estimatedTimeRemaining['expected_production'] ? 'text-green-600' : 'text-yellow-600' }}">
                                                        {{ number_format($viewingSchedule->estimatedTimeRemaining['actual_production'], 2) }}
                                                    </span>
                                                </div>
                                                <div class="text-xs mt-1">
                                                    @if($viewingSchedule->estimatedTimeRemaining['actual_production'] >= $viewingSchedule->estimatedTimeRemaining['expected_production'])
                                                        <span class="text-green-600">{{ __('messages.production_on_track') }}</span>
                                                    @else
                                                        <span class="text-yellow-600">{{ __('messages.production_behind') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <!-- Tempo estimado para conclusão -->
                                            <div class="bg-white p-2 rounded border border-blue-200 {{ $viewingSchedule->estimatedTimeRemaining['is_delayed'] ? 'border-red-300 bg-red-50' : '' }}">
                                                <span class="text-xs font-medium text-gray-500 block">{{ __('messages.estimated_completion') }}</span>
                                                <div class="text-center mt-1">
                                                    <span class="text-lg font-bold {{ $viewingSchedule->estimatedTimeRemaining['is_delayed'] ? 'text-red-600' : 'text-blue-600' }}">
                                                        {{ $viewingSchedule->estimatedTimeRemaining['hours'] }}h {{ $viewingSchedule->estimatedTimeRemaining['minutes'] }}m
                                                    </span>
                                                </div>
                                                <div class="text-xs mt-1 text-center">
                                                    @if($viewingSchedule->estimatedTimeRemaining['is_delayed'])
                                                        <span class="text-red-600">{{ __('messages.deadline_passed') }}</span>
                                                    @else
                                                        <span>{{ __('messages.time_remaining') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </dd>
                                </div>
                                @endif

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.priority') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $viewingSchedule->priority == 'urgent' ? 'bg-red-100 text-red-800' : 
                                               ($viewingSchedule->priority == 'high' ? 'bg-orange-100 text-orange-800' : 
                                               ($viewingSchedule->priority == 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800')) }}">
                                            {{ __('messages.priority_' . $viewingSchedule->priority) }}
                                        </span>
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.planned_quantity') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ number_format($viewingSchedule->planned_quantity, 2) }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.actual_quantity') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ number_format($viewingSchedule->actual_quantity, 2) }}
                                    </dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.actual_start_time') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $viewingSchedule->actual_start_time ? $viewingSchedule->actual_start_time->format('d/m/Y H:i') : __('messages.not_started') }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.actual_end_time') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $viewingSchedule->actual_end_time ? $viewingSchedule->actual_end_time->format('d/m/Y H:i') : __('messages.not_completed') }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.responsible') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $viewingSchedule->responsible ?: __('messages.not_specified') }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.location') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ is_object($viewingSchedule->location) ? $viewingSchedule->location->name : ($viewingSchedule->location_name ?: __('messages.not_specified')) }}
                                    </dd>
                                </div>
                                
                                @if($viewingSchedule->is_delayed)
                                <div class="sm:col-span-3">
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.delay_reason') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900 bg-red-50 p-2 rounded border border-red-200">
                                        {{ $viewingSchedule->delay_reason ?: __('messages.no_reason_provided') }}
                                    </dd>
                                </div>
                                @endif
                            </dl>    
                                <!-- Tempo Restante (apenas para status em andamento) -->
                                @if($viewingSchedule->status === 'in_progress')
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('messages.remaining_time') }}:</dt>
                                        <dd class="mt-1 text-sm 
                                            @if($viewingSchedule->end_date->isPast()) text-red-600 font-medium
                                            @elseif($viewingSchedule->end_date->diffInDays(now()) <= 2) text-yellow-600
                                            @else text-gray-900
                                            @endif">
                                            @if($viewingSchedule->end_date->isPast())
                                                {{ $viewingSchedule->end_date->diffInDays(now()) }} {{ __('messages.days_overdue') }}
                                            @else
                                                {{ now()->diffInDays($viewingSchedule->end_date) }} {{ __('messages.days_remaining') }}
                                            @endif
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                        
                        <!-- Coluna 2: Informações de Datas -->
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>
                                {{ __('messages.dates_information') }}
                            </h4>
                            
                            <dl class="space-y-3">
                                <!-- Data Inicial -->
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.start_date') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $viewingSchedule->start_date->format('d/m/Y') }}
                                    </dd>
                                </div>
                                
                                <!-- Data Final -->
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.end_date') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $viewingSchedule->end_date->format('d/m/Y') }}
                                    </dd>
                                </div>
                                
                                <!-- Duração -->
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.duration') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $viewingSchedule->start_date->diffInDays($viewingSchedule->end_date) + 1 }} {{ __('messages.days') }}
                                    </dd>
                                </div>
                                
                                <!-- Tempo Restante (apenas para status em andamento) -->
                                @if($viewingSchedule->status === 'in_progress')
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('messages.remaining_time') }}:</dt>
                                        <dd class="mt-1 text-sm 
                                            @if($viewingSchedule->end_date->isPast()) text-red-600 font-medium
                                            @elseif($viewingSchedule->end_date->diffInDays(now()) <= 2) text-yellow-600
                                            @else text-gray-900
                                            @endif">
                                            @if($viewingSchedule->end_date->isPast())
                                                {{ $viewingSchedule->end_date->diffInDays(now()) }} {{ __('messages.days_overdue') }}
                                            @else
                                                {{ now()->diffInDays($viewingSchedule->end_date) }} {{ __('messages.days_remaining') }}
                                            @endif
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                    
                    <!-- Notas -->
                    <div class="mt-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-sticky-note text-blue-600 mr-2"></i>
                            {{ __('messages.notes') }}
                        </h4>
                        
                        <div class="p-3 bg-white border border-gray-200 rounded-md">
                            <p class="text-sm text-gray-700 whitespace-pre-line">
                                {{ $viewingSchedule->notes ?: __('messages.no_notes_available') }}
                            </p>
                        </div>
                    </div>
                    
                    <!-- Ordens de Produção Relacionadas (se houver) -->
                    @if(isset($relatedOrders) && count($relatedOrders) > 0)
                        <div class="mt-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-clipboard-list text-blue-600 mr-2"></i>
                                {{ __('messages.related_production_orders') }}
                            </h4>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('messages.order_number') }}
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('messages.planned_quantity') }}
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('messages.status') }}
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('messages.actions') }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($relatedOrders as $order)
                                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                                                    {{ $order->order_number }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ number_format($order->planned_quantity, 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                        @if($order->status === 'draft') bg-gray-100 text-gray-800
                                                        @elseif($order->status === 'released') bg-blue-100 text-blue-800
                                                        @elseif($order->status === 'in_progress') bg-yellow-100 text-yellow-800
                                                        @elseif($order->status === 'completed') bg-green-100 text-green-800
                                                        @else bg-red-100 text-red-800
                                                        @endif">
                                                        {{ __('messages.' . $order->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                                    <button type="button" wire:click="viewOrder({{ $order->id }})" 
                                                        class="text-blue-600 hover:text-blue-800 transition-colors duration-150">
                                                        <i class="fas fa-external-link-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Metadados -->
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-gray-500">
                        <!-- Criado Por / Em -->
                        <div class="flex items-center">
                            <span class="mr-1">{{ __('messages.created_by') }}:</span>
                            <span class="font-medium text-gray-700">{{ $viewingSchedule->createdBy->name ?? 'N/A' }}</span>
                            <span class="mx-1">•</span>
                            <span>{{ $viewingSchedule->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        
                        <!-- Atualizado Por / Em -->
                        <div class="flex items-center justify-end">
                            <span class="mr-1">{{ __('messages.updated_by') }}:</span>
                            <span class="font-medium text-gray-700">{{ $viewingSchedule->updatedBy->name ?? 'N/A' }}</span>
                            <span class="mx-1">•</span>
                            <span>{{ $viewingSchedule->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Production Actions -->
            <div class="bg-white px-4 py-3 border-t border-gray-200">
                <div class="flex flex-wrap gap-2 justify-center">
                    <!-- Start Production Button - apenas se status for 'confirmed' -->
                    @if($viewingSchedule->status == 'confirmed')
                    <button type="button" wire:click="startProduction" 
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ __('messages.start_production') }}
                    </button>
                    @endif
                    
                    <!-- Complete Production Button - apenas se status for 'in_progress' -->
                    @if($viewingSchedule->status == 'in_progress')
                    <div x-data="{ showActualQuantity: false }">
                        <button type="button" 
                                @click="showActualQuantity = true"
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ __('messages.complete_production') }}
                        </button>
                        
                        <!-- Modal para informar a quantidade produzida -->
                        <div x-show="showActualQuantity" 
                             class="fixed z-10 inset-0 overflow-y-auto" 
                             style="display: none;"
                             x-transition:enter="ease-out duration-300"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="ease-in duration-200"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0">
                            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                                </div>

                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                        <div class="sm:flex sm:items-start">
                                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                                    {{ __('messages.enter_produced_quantity') }}
                                                </h3>
                                                <div class="mt-4">
                                                    <label for="actual_quantity" class="block text-sm font-medium text-gray-700">{{ __('messages.actual_quantity') }}</label>
                                                    <input type="number" wire:model.defer="schedule.actual_quantity" step="0.001" min="0"
                                                           class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                                    
                                                    <div class="mt-4">
                                                        <label for="delay_reason" class="block text-sm font-medium text-gray-700">{{ __('messages.delay_reason') }} ({{ __('messages.if_applicable') }})</label>
                                                        <textarea wire:model.defer="schedule.delay_reason" rows="3"
                                                                  class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                        <button type="button" wire:click="completeProduction" @click="showActualQuantity = false"
                                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                                            {{ __('messages.save') }}
                                        </button>
                                        <button type="button" @click="showActualQuantity = false"
                                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                            {{ __('messages.cancel') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Botão de Movimentação Manual de Estoque - apenas se status for 'completed' e não tiver sido movimentado -->                    
                    @if($viewingSchedule->status == 'completed' && !$viewingSchedule->stock_moved && $viewingSchedule->actual_quantity > 0)
                    <button type="button" wire:click="moveProductionToStock" 
                            wire:loading.attr="disabled"
                            wire:target="moveProductionToStock"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition ease-in-out duration-150">
                        <svg wire:loading.remove wire:target="moveProductionToStock" class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                        <svg wire:loading wire:target="moveProductionToStock" class="-ml-1 mr-2 h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="moveProductionToStock">{{ __('messages.move_to_stock') }}</span>
                        <span wire:loading wire:target="moveProductionToStock">{{ __('messages.processing') }}...</span>
                    </button>
                    @endif
                    
                    <!-- Indicador de Movimentação de Estoque - mostra se o estoque já foi movimentado -->
                    @if($viewingSchedule->status == 'completed' && $viewingSchedule->stock_moved)
                    <div class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-green-700 bg-green-100">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ __('messages.stock_updated') }}
                    </div>
                    @endif
                    
                    <!-- Ver Planos Diários -->
                    <button type="button" wire:click="viewDailyPlans({{ $viewingSchedule->id }})" 
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        {{ __('messages.view_daily_plans') }}
                    </button>
                    
                    <!-- Ver Ordens de Produção -->
                    <button type="button" wire:click="viewOrders({{ $viewingSchedule->id }})" 
                            class="mt-2 sm:mt-0 sm:ml-2 inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        {{ __('messages.view_orders') }}
                    </button>
                    
                    <!-- Editar Button -->
                    <button type="button" wire:click="edit({{ $viewingSchedule->id }})" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        {{ __('messages.edit') }}
                    </button>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" wire:click="closeViewModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    {{ __('messages.close') }}
                </button>
            </div>
            @else
                <div class="p-6 flex justify-center items-center">
                    <div class="flex flex-col items-center space-y-2">
                        <div class="flex-shrink-0 bg-gray-100 p-3 rounded-full">
                            <i class="fas fa-exclamation-circle text-gray-400 text-2xl"></i>
                        </div>
                        <p class="text-gray-500">{{ __('messages.schedule_not_found') }}</p>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end">
                    <button type="button" wire:click="closeViewModal" 
                        class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-times mr-2"></i>
                        {{ __('messages.close') }}
                    </button>
                </div>
            @endif
        </div>

<!-- Scripts para Toastr -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Livewire.on('showToast', function(data) {
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };
            
            switch(data.type) {
                case 'success':
                    toastr.success(data.message, data.title);
                    break;
                case 'error':
                    toastr.error(data.message, data.title);
                    break;
                case 'warning':
                    toastr.warning(data.message, data.title);
                    break;
                case 'info':
                    toastr.info(data.message, data.title);
                    break;
                default:
                    toastr.info(data.message, data.title);
            }
        });
    });
</script>

</div>
</div>
