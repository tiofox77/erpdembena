<!-- Modal para Visualizar Programação de Produção -->
<div x-cloak
    class="fixed inset-0 z-30 flex items-center justify-center bg-gray-800 bg-opacity-75 transition-opacity"
    x-show="$wire.showViewModal"
    x-init="$nextTick(() => { if (typeof window.initBreakdownCharts === 'function') setTimeout(window.initBreakdownCharts, 500); })"
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
                <div class="flex items-center space-x-3">
                    @if($viewingSchedule)
                    <button 
                        wire:click="generateSchedulePdf({{ $viewingSchedule->id }})"
                        type="button"
                        class="inline-flex items-center px-3 py-1.5 text-white text-sm font-medium hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300 rounded-md transition-all duration-200 ease-in-out transform hover:scale-105"
                        title="{{ __('messages.generate_pdf') }}">
                        <i class="fas fa-file-pdf mr-2"></i>
                        {{ __('messages.generate_pdf') }}
                    </button>
                    @endif
                    <button @click="$wire.closeViewModal()" class="text-white hover:text-gray-200 focus:outline-none">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
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
                                        @php
                                            // Calcular a soma das quantidades reais dos planos diários
                                            $totalActualQuantity = $viewingSchedule->dailyPlans->sum('actual_quantity');
                                            // Calcular o percentual de conclusão
                                            $completionPercentage = $viewingSchedule->planned_quantity > 0 
                                                ? min(100, round(($totalActualQuantity / $viewingSchedule->planned_quantity) * 100, 1))
                                                : 0;
                                        @endphp
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $completionPercentage }}%"></div>
                                        </div>
                                        <span class="text-xs">{{ $completionPercentage }}%</span>
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
                                                        // Calcular a soma das quantidades reais dos planos diários
                                                        $totalActualQuantity = $viewingSchedule->dailyPlans->sum('actual_quantity');
                                                        $completionPercent = $viewingSchedule->planned_quantity > 0 
                                                            ? round(($totalActualQuantity / $viewingSchedule->planned_quantity) * 100, 1)
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
                                                        <i class="fas fa-flag-checkered text-green-600"></i> {{ number_format($totalActualQuantity, 2) }}
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
                                                @php
                                                    // Calcular a soma das quantidades reais dos planos diários
                                                    $totalActualProduction = $viewingSchedule->dailyPlans->sum('actual_quantity');
                                                    
                                                    // Calcular a produção esperada baseada no tempo decorrido
                                                    $startDate = $viewingSchedule->actual_start_date ?? $viewingSchedule->start_date;
                                                    $startTime = $viewingSchedule->actual_start_time ?? $viewingSchedule->start_time ?? '00:00';
                                                    $endDate = $viewingSchedule->end_date;
                                                    $endTime = $viewingSchedule->end_time ?? '23:59';
                                                    
                                                    $startDateTime = \Carbon\Carbon::parse($startDate->format('Y-m-d') . ' ' . $startTime);
                                                    $endDateTime = \Carbon\Carbon::parse($endDate->format('Y-m-d') . ' ' . $endTime);
                                                    $now = \Carbon\Carbon::now();
                                                    
                                                    // Total de minutos planejados para a produção
                                                    $totalPlannedMinutes = $startDateTime->diffInMinutes($endDateTime);
                                                    $minutesElapsed = $startDateTime->diffInMinutes($now);
                                                    
                                                    // Percentual do tempo decorrido
                                                    $timePercentage = ($totalPlannedMinutes > 0) ? min(100, max(0, ($minutesElapsed / $totalPlannedMinutes) * 100)) : 0;
                                                    
                                                    // Produção esperada baseada no percentual de tempo decorrido
                                                    $expectedProduction = ($viewingSchedule->planned_quantity * $timePercentage) / 100;
                                                @endphp
                                                <div class="flex justify-between items-center mt-1">
                                                    <span class="text-sm font-medium">{{ number_format($expectedProduction, 2) }}</span>
                                                    <span class="text-xs text-gray-500">vs</span>
                                                    <span class="text-sm font-medium {{ $totalActualProduction >= $expectedProduction ? 'text-green-600' : 'text-yellow-600' }}">
                                                        {{ number_format($totalActualProduction, 2) }}
                                                    </span>
                                                </div>
                                                <div class="text-xs mt-1">
                                                    @if($totalActualProduction >= $expectedProduction)
                                                        <span class="text-green-600">{{ __('messages.production_on_track') }}</span>
                                                    @else
                                                        <span class="text-yellow-600">{{ __('messages.production_behind') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <!-- Tempo estimado para conclusão -->
                                            @php
                                                // Recalcular se está atrasado com base na produção real dos planos diários
                                                $isDelayed = false;
                                                $hours = 0;
                                                $minutes = 0;
                                                
                                                // Calcular tempo decorrido em horas desde o início
                                                $startDate = $viewingSchedule->actual_start_date ?? $viewingSchedule->start_date;
                                                $startTime = $viewingSchedule->actual_start_time ?? $viewingSchedule->start_time ?? '00:00';
                                                $startDateTime = \Carbon\Carbon::parse($startDate->format('Y-m-d') . ' ' . $startTime);
                                                $now = \Carbon\Carbon::now();
                                                $elapsedHours = max(1, $startDateTime->diffInMinutes($now) / 60); // Em horas, mínimo 1h
                                                
                                                if ($viewingSchedule->planned_quantity > 0 && $totalActualProduction > 0) {
                                                    // Taxa de produção atual por hora
                                                    $productionRate = $totalActualProduction / $elapsedHours;
                                                    
                                                    // Tempo restante estimado
                                                    $remainingQuantity = $viewingSchedule->planned_quantity - $totalActualProduction;
                                                    if ($remainingQuantity > 0 && $productionRate > 0) {
                                                        $remainingTime = $remainingQuantity / $productionRate;
                                                        $hours = floor($remainingTime);
                                                        $minutes = round(($remainingTime - $hours) * 60);
                                                    }
                                                    
                                                    // Verificar se ultrapassa o prazo
                                                    $endDate = $viewingSchedule->end_date;
                                                    $endTime = $viewingSchedule->end_time ?? '23:59';
                                                    $endDateTime = \Carbon\Carbon::parse($endDate->format('Y-m-d') . ' ' . $endTime);
                                                    $hoursUntilDeadline = max(0, $now->diffInMinutes($endDateTime) / 60);
                                                    
                                                    $isDelayed = $hours > $hoursUntilDeadline;
                                                } else {
                                                    // Usar valores padrão do objeto se disponíveis, ou calcular baseado em datas
                                                    if (isset($viewingSchedule->estimatedTimeRemaining['hours'])) {
                                                        $hours = $viewingSchedule->estimatedTimeRemaining['hours'];
                                                        $minutes = $viewingSchedule->estimatedTimeRemaining['minutes'] ?? 0;
                                                        $isDelayed = $viewingSchedule->estimatedTimeRemaining['is_delayed'] ?? false;
                                                    } else {
                                                        // Se não há dados estimados, calcular baseado no prazo
                                                        $endDate = $viewingSchedule->end_date;
                                                        $endTime = $viewingSchedule->end_time ?? '23:59';
                                                        $endDateTime = \Carbon\Carbon::parse($endDate->format('Y-m-d') . ' ' . $endTime);
                                                        $diffInHours = $now->diffInHours($endDateTime);
                                                        $hours = $diffInHours;
                                                        $minutes = $now->diffInMinutes($endDateTime) % 60;
                                                        $isDelayed = $now > $endDateTime;
                                                    }
                                                }
                                            @endphp
                                            <div class="bg-white p-2 rounded border border-blue-200 {{ $isDelayed ? 'border-red-300 bg-red-50' : '' }}">
                                                <span class="text-xs font-medium text-gray-500 block">{{ __('messages.estimated_completion') }}</span>
                                                <div class="text-center mt-1">
                                                    <span class="text-lg font-bold {{ $isDelayed ? 'text-red-600' : 'text-blue-600' }}">
                                                        {{ $hours }}h {{ $minutes }}m
                                                    </span>
                                                </div>
                                                <div class="text-xs mt-1 text-center">
                                                    @if($isDelayed)
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
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.planned_quantity') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <span class="font-medium text-blue-700">{{ number_format($viewingSchedule->planned_quantity, 2) }}</span>
                                        <span class="text-xs text-gray-500">{{ __('messages.units') }}</span>
                                        @if(!empty($breakdownImpact) && isset($breakdownImpact['efficiency_percentage']))
                                            <div class="mt-1 text-xs text-blue-600">
                                                <i class="fas fa-info-circle mr-1"></i> {{ __('messages.efficiency') }}: {{ number_format($breakdownImpact['efficiency_percentage'] ?? 0, 2) }}%
                                            </div>
                                        @endif
                                    </dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.actual_quantity') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        @if($viewingSchedule->actual_quantity)
                                            <span class="font-medium text-green-700">{{ number_format($viewingSchedule->actual_quantity, 2) }}</span>
                                            <span class="text-xs text-gray-500">{{ __('messages.units') }}</span>
                                            @if(!empty($breakdownImpact) && isset($breakdownImpact['total_defect_quantity']))
                                                <div class="mt-1 flex flex-col">
                                                    <span class="text-xs text-green-600">
                                                        <i class="fas fa-check-circle mr-1"></i> {{ __('messages.good_units') }}: {{ number_format($breakdownImpact['good_units'] ?? 0, 2) }}
                                                    </span>
                                                    <span class="text-xs text-red-600">
                                                        <i class="fas fa-exclamation-circle mr-1"></i> {{ __('messages.defect_quantity') }}: {{ number_format($breakdownImpact['total_defect_quantity'] ?? 0, 2) }}
                                                    </span>
                                                </div>
                                            @endif
                                        @else
                                            <span>-</span>
                                        @endif
                                    </dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.actual_start_time') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $viewingSchedule->start_date ? $viewingSchedule->start_date->format('d/m/Y') : __('messages.not_started') }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.actual_end_time') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $viewingSchedule->end_date ? $viewingSchedule->end_date->format('d/m/Y') : __('messages.not_completed') }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.responsible') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        @if($viewingSchedule->responsible)
                                            <div class="flex items-center">
                                                <span class="font-medium text-blue-700">{{ $viewingSchedule->responsible->name }}</span>
                                                @if($viewingSchedule->responsible->position)
                                                    <span class="text-xs text-gray-500 ml-2">{{ $viewingSchedule->responsible->position }}</span>
                                                @endif
                                            </div>
                                            @if($viewingSchedule->responsible->department || $viewingSchedule->responsible->email)
                                                <div class="mt-1 text-xs text-gray-600">
                                                    @if($viewingSchedule->responsible->department)
                                                        <span class="inline-block"><i class="fas fa-building mr-1"></i> {{ $viewingSchedule->responsible->department }}</span>
                                                    @endif
                                                    @if($viewingSchedule->responsible->email)
                                                        <span class="inline-block ml-2"><i class="fas fa-envelope mr-1"></i> {{ $viewingSchedule->responsible->email }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                            @if(!$viewingSchedule->responsible->is_active)
                                                <div class="mt-1 text-xs text-yellow-600">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i> {{ __('messages.inactive') }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-gray-500 italic">{{ __('messages.not_specified') }}</span>
                                        @endif
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.location') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ is_object($viewingSchedule->location) ? $viewingSchedule->location->name : ($viewingSchedule->location_name ?: __('messages.not_specified')) }}
                                    </dd>
                                </div>
                                
                                <!-- Warehouse Raw Materials -->
                                <div class="sm:col-span-3 mt-4 border border-blue-100 bg-blue-50 p-3 rounded-md">
                                    <dt class="text-sm font-medium text-blue-700 flex items-center">
                                        <i class="fas fa-warehouse text-blue-600 mr-2"></i>
                                        {{ __('messages.warehouse_raw_materials') }}
                                    </dt>
                                    <dd class="mt-2">
                                        @if($viewingSchedule->product && $viewingSchedule->product->bomItems && $viewingSchedule->product->bomItems->count() > 0)
                                            <div class="overflow-hidden shadow-sm border border-blue-200 rounded-lg bg-white">
                                                <table class="min-w-full divide-y divide-gray-200">
                                                    <thead class="bg-blue-50">
                                                        <tr>
                                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-blue-700 tracking-wider">{{ __('messages.material_name') }}</th>
                                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-blue-700 tracking-wider">{{ __('messages.sku') }}</th>
                                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-blue-700 tracking-wider">{{ __('messages.required_qty') }}</th>
                                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-blue-700 tracking-wider">{{ __('messages.stock_qty') }}</th>
                                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-blue-700 tracking-wider">{{ __('messages.status') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white divide-y divide-gray-200">
                                                        @foreach($viewingSchedule->product->bomItems as $bomItem)
                                                            @php
                                                                // Calcular a quantidade necessária baseada na quantidade total planejada
                                                                $requiredQty = $bomItem->quantity * $viewingSchedule->planned_quantity;
                                                                
                                                                // Obter estoque atual do componente (matéria-prima)
                                                                $stockQty = $bomItem->component->getStockQuantity() ?? 0;
                                                                
                                                                // Verificar se há estoque suficiente
                                                                $hasEnoughStock = $stockQty >= $requiredQty;
                                                            @endphp
                                                            <tr class="hover:bg-gray-50">
                                                                <td class="px-3 py-2 whitespace-nowrap text-xs">
                                                                    <div class="font-medium text-gray-900">{{ $bomItem->component->name }}</div>
                                                                    @if($bomItem->component->description)
                                                                        <div class="text-xs text-gray-500 truncate max-w-xs">{{ $bomItem->component->description }}</div>
                                                                    @endif
                                                                </td>
                                                                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-500">{{ $bomItem->component->sku }}</td>
                                                                <td class="px-3 py-2 whitespace-nowrap text-xs font-medium">{{ number_format($requiredQty, 2) }}</td>
                                                                <td class="px-3 py-2 whitespace-nowrap text-xs font-medium {{ $hasEnoughStock ? 'text-green-600' : 'text-red-600' }}">
                                                                    {{ number_format($stockQty, 2) }}
                                                                </td>
                                                                <td class="px-3 py-2 whitespace-nowrap text-xs">
                                                                    @if($hasEnoughStock)
                                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                                            <i class="fas fa-check-circle mr-1"></i>
                                                                            {{ __('messages.available') }}
                                                                        </span>
                                                                    @else
                                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                                                            {{ __('messages.insufficient') }}
                                                                        </span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="text-center py-3 text-sm text-gray-500 bg-white rounded-md border border-gray-200">
                                                <i class="fas fa-info-circle text-blue-400 mr-1"></i>
                                                {{ __('messages.no_bom_items_found') }}
                                            </div>
                                        @endif
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
                        
                        <!-- Breakdown Impact Analysis -->
                        <div class="bg-red-50 rounded-lg p-4 border border-red-200 mt-4">
                            <h4 class="font-semibold text-gray-800 mb-4 flex items-center justify-between">
                                <div>
                                    <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                                    {{ __('messages.breakdown_impact') }}
                                </div>
                                
                                <!-- Resumo da eficiência -->
                                <div class="text-xs text-gray-600 bg-gray-100 p-1.5 rounded-lg">
                                    <span class="font-medium">
                                        {{ __('messages.planned_vs_actual_with_breakdown') }}:
                                    </span>
                                    <span class="text-blue-600 font-medium mx-1">{{ number_format($viewingSchedule->planned_quantity, 0) }}</span> →
                                    <span class="text-green-600 font-medium mx-1">{{ number_format($breakdownImpact['good_units'] ?? 0, 0) }}</span>
                                    <span class="text-xs">(-{{ number_format($breakdownImpact['production_loss'] ?? 0, 0) }})</span>
                                </div>
                            </h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-3">
                                <!-- Impact on Production -->
                                <div class="bg-white rounded-lg p-3 shadow-sm border border-red-100 md:col-span-1 lg:col-span-2">
                                    <h5 class="text-sm font-medium text-red-700 mb-2">{{ __('messages.production_impact') }}</h5>
                                    <div class="flex items-center">
                                        <div class="mr-2">
                                            <i class="fas fa-industry text-red-500 text-lg"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold">{{ number_format($breakdownImpact['production_loss'] ?? 0, 2) }} {{ __('messages.units') }}</p>
                                            <p class="text-xs text-gray-500">{{ __('messages.estimated_production_loss') }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Impact on Revenue -->
                                <div class="bg-white rounded-lg p-3 shadow-sm border border-red-100 md:col-span-1 lg:col-span-2">
                                    <h5 class="text-sm font-medium text-red-700 mb-2">{{ __('messages.revenue_impact') }}</h5>
                                    <div class="flex items-center">
                                        <div class="mr-2">
                                            <i class="fas fa-dollar-sign text-red-500 text-lg"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold">${{ number_format($breakdownImpact['revenue_loss'] ?? 0, 2) }}</p>
                                            <p class="text-xs text-gray-500">{{ __('messages.estimated_revenue_loss') }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Recovery Time -->
                                <div class="bg-white rounded-lg p-3 shadow-sm border border-red-100 md:col-span-1 lg:col-span-2">
                                    <h5 class="text-sm font-medium text-red-700 mb-2">{{ __('messages.recovery_time') }}</h5>
                                    <div class="flex items-center">
                                        <div class="mr-2">
                                            <i class="fas fa-hourglass-half text-red-500 text-lg"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold">{{ number_format($breakdownImpact['recovery_hours'] ?? 0, 2) }} {{ __('messages.hours') }}</p>
                                            <p class="text-xs text-gray-500">{{ __('messages.estimated_recovery_time') }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Defect Quantity -->
                                <div class="bg-white rounded-lg p-3 shadow-sm border border-red-100 md:col-span-1 lg:col-span-2">
                                    <h5 class="text-sm font-medium text-red-700 mb-2">{{ __('messages.defect_quantity') }}</h5>
                                    <div class="flex items-center">
                                        <div class="mr-2">
                                            <i class="fas fa-exclamation-circle text-red-500 text-lg"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold">{{ number_format($breakdownImpact['total_defect_quantity'] ?? 0, 2) }} {{ __('messages.units') }}</p>
                                            <p class="text-xs text-gray-500">{{ number_format($breakdownImpact['defect_rate'] ?? 0, 2) }}% {{ __('messages.defect_rate') }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Good Units -->
                                <div class="bg-white rounded-lg p-3 shadow-sm border border-green-100 md:col-span-1 lg:col-span-2">
                                    <h5 class="text-sm font-medium text-green-700 mb-2">{{ __('messages.good_units') }}</h5>
                                    <div class="flex items-center">
                                        <div class="mr-2">
                                            <i class="fas fa-check-circle text-green-500 text-lg"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold">{{ number_format($breakdownImpact['good_units'] ?? 0, 2) }} {{ __('messages.units') }}</p>
                                            <p class="text-xs text-gray-500">{{ number_format($breakdownImpact['quality_rate'] ?? 0, 2) }}% {{ __('messages.quality_rate') }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Efficiency Percentage -->
                                <div class="bg-white rounded-lg p-3 shadow-sm border border-blue-100 md:col-span-1 lg:col-span-2">
                                    <h5 class="text-sm font-medium text-blue-700 mb-2">{{ __('messages.efficiency') }}</h5>
                                    <div class="flex items-center">
                                        <div class="mr-2">
                                            <i class="fas fa-tachometer-alt text-blue-500 text-lg"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold">{{ number_format($breakdownImpact['efficiency_percentage'] ?? 0, 2) }}%</p>
                                            <p class="text-xs text-gray-500">{{ __('messages.production_efficiency') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Impact Trend and Quality Charts -->
                            @if(!empty($chartHistory))
                            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3">
                                <!-- Impact Trend Chart -->
                                <div class="bg-white p-3 rounded-lg border border-red-100">
                                    <h5 class="text-sm font-medium text-red-700 mb-2">{{ __('messages.impact_trend') }}</h5>
                                    <div class="h-48" style="position: relative;">
                                        <canvas id="impactTrendCanvas"></canvas>
                                    </div>
                                </div>
                                
                                <!-- Quality Analysis Chart -->
                                <div class="bg-white p-3 rounded-lg border border-green-100">
                                    <h5 class="text-sm font-medium text-green-700 mb-2">{{ __('messages.quality_analysis') }}</h5>
                                    <div class="h-48" style="position: relative;">
                                        <canvas id="qualityAnalysisCanvas"></canvas>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Direct initialization scripts for charts -->
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    // Wait for the canvas elements to be in the DOM
                                    setTimeout(function() {
                                        try {
                                            const historyData = @json($chartHistory);
                                            
                                            if (historyData && historyData.length > 0) {
                                                // Initialize Impact Trend Chart
                                                const impactCanvas = document.getElementById('impactTrendCanvas');
                                                if (impactCanvas) {
                                                    const impactCtx = impactCanvas.getContext('2d');
                                                    new Chart(impactCtx, {
                                                        type: 'bar',
                                                        data: {
                                                            labels: historyData.map(item => item.date),
                                                            datasets: [
                                                                {
                                                                    label: '{{ __('messages.breakdown_hours') }}',
                                                                    data: historyData.map(item => item.hours),
                                                                    backgroundColor: 'rgba(234, 88, 12, 0.6)',
                                                                    borderColor: 'rgba(234, 88, 12, 1)',
                                                                    borderWidth: 1
                                                                },
                                                                {
                                                                    label: '{{ __('messages.production_loss') }}',
                                                                    data: historyData.map(item => item.loss),
                                                                    backgroundColor: 'rgba(220, 38, 38, 0.6)',
                                                                    borderColor: 'rgba(220, 38, 38, 1)',
                                                                    borderWidth: 1
                                                                }
                                                            ]
                                                        },
                                                        options: {
                                                            responsive: true,
                                                            maintainAspectRatio: false,
                                                            scales: {
                                                                y: {
                                                                    beginAtZero: true,
                                                                    title: {
                                                                        display: true,
                                                                        text: '{{ __('messages.value') }}'
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    });
                                                    console.log('{{ __('messages.impact_trend_chart_initialized') }}');
                                                }
                                                
                                                // Initialize Quality Analysis Chart
                                                const qualityCanvas = document.getElementById('qualityAnalysisCanvas');
                                                if (qualityCanvas) {
                                                    const qualityCtx = qualityCanvas.getContext('2d');
                                                    new Chart(qualityCtx, {
                                                        type: 'bar',
                                                        data: {
                                                            labels: historyData.map(item => item.date),
                                                            datasets: [
                                                                {
                                                                    label: '{{ __('messages.defect_quantity') }}',
                                                                    data: historyData.map(item => item.defects),
                                                                    backgroundColor: 'rgba(239, 68, 68, 0.6)',
                                                                    borderColor: 'rgba(239, 68, 68, 1)',
                                                                    borderWidth: 1,
                                                                    type: 'bar'
                                                                },
                                                                {
                                                                    label: '{{ __('messages.quality_rate') }}',
                                                                    data: historyData.map(item => item.quality_rate),
                                                                    borderColor: 'rgba(16, 185, 129, 1)',
                                                                    backgroundColor: 'rgba(16, 185, 129, 0.2)',
                                                                    borderWidth: 2,
                                                                    type: 'line',
                                                                    yAxisID: 'y1'
                                                                }
                                                            ]
                                                        },
                                                        options: {
                                                            responsive: true,
                                                            maintainAspectRatio: false,
                                                            scales: {
                                                                y: {
                                                                    beginAtZero: true,
                                                                    title: {
                                                                        display: true,
                                                                        text: '{{ __('messages.units') }}'
                                                                    }
                                                                },
                                                                y1: {
                                                                    position: 'right',
                                                                    beginAtZero: true,
                                                                    max: 100,
                                                                    title: {
                                                                        display: true,
                                                                        text: '%'
                                                                    },
                                                                    grid: {
                                                                        drawOnChartArea: false
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    });
                                                    console.log('{{ __('messages.quality_analysis_chart_initialized') }}');
                                                }
                                            }
                                        } catch (error) {
                                            console.error('{{ __('messages.error_initializing_charts') }}:', error);
                                        }
                                    }, 500);
                                });
                            </script>
                            
                            <div class="mt-2 text-center">
                                <button type="button" onclick="location.reload()" class="text-xs text-blue-600 underline">
                                    {{ __('messages.reload_charts') }}
                                </button>
                            </div>
                            @endif
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
                    
                    <!-- Botão e modal Complete Production removidos para automatização do processo - 
                    A programação agora é marcada como completa automaticamente quando todos os planos diários são completados -->
                    @if($viewingSchedule->status == 'in_progress')
                    <!-- Botão removido - Agora o processo é automático -->
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

<!-- Scripts para Toastr e Charts -->
<script>
    // Embed breakdown data directly to avoid serialization issues
    const breakdownData = @json($breakdownImpact ?? []);
    
    // Make history data available globally using the dedicated chartHistory property
    window.chartHistoryData = @json($chartHistory ?? []);
    console.log('Chart history data (from dedicated property):', window.chartHistoryData);
    
    document.addEventListener('DOMContentLoaded', function() {
        // Log for debugging
        console.log('DOM Content Loaded, breakdownData:', breakdownData);
        
        // Make sure Chart.js is available
        if (typeof Chart === 'undefined') {
            console.error('Chart.js not loaded!');
            // Dynamically load Chart.js if not available
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js';
            script.onload = function() {
                console.log('Chart.js loaded dynamically');
                setTimeout(initializeBreakdownCharts, 300);
            };
            document.head.appendChild(script);
        } else {
            console.log('Chart.js is available');
        }
        // Set up toast notification listener for Livewire v3
        document.addEventListener('livewire:initialized', () => {
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
        
        // Initialize Breakdown Impact Analysis Charts when modal opens
        document.addEventListener('livewire:initialized', () => {
            // Set up listeners for Livewire v3 events
            Livewire.on('viewModalReady', () => {
                console.log('viewModalReady event fired');
                setTimeout(initializeBreakdownCharts, 100); // Short delay to ensure DOM elements are ready
            });
        });
        
        // Also initialize when component is updated
        document.addEventListener('livewire:update', () => {
            console.log('Livewire component updated');
            if (document.getElementById('impactTrendChart')) {
                setTimeout(initializeBreakdownCharts, 100);
            }
        });
        
        function initializeBreakdownCharts() {
            console.log('Initializing breakdown charts');
            
            // Check if canvases exist
            const impactCanvas = document.getElementById('impactTrendCanvas');
            const qualityCanvas = document.getElementById('qualityAnalysisCanvas');
            
            if (!impactCanvas || !qualityCanvas) {
                console.warn('Chart canvases not found in the DOM');
                return;
            }
            
            console.log('Chart canvases found');
            
            // Hard-coded static data if needed for testing
            // let testData = [
            //     {date: '2025-05-20', hours: 11.67, loss: 14.5, defects: 100.0, quality_rate: 100.0},
            //     {date: '2025-05-15', hours: 6.67, loss: 14.5, defects: 100.0, quality_rate: 100.0},
            //     {date: '2025-05-14', hours: 3.33, loss: 80.0, defects: 40.0, quality_rate: 33.33}
            // ];
            
            // Use the globally available history data
            let historyData = window.chartHistoryData || [];
            
            console.log('History data for charts:', historyData);
            
            if (historyData.length === 0) {
                console.warn('No history data available for charts');
                return;
            }
            
            // Check if charts already exist and destroy them safely
            try {
                if (window.impactTrendChart instanceof Chart) {
                    window.impactTrendChart.destroy();
                }
                
                if (window.qualityAnalysisChart instanceof Chart) {
                    window.qualityAnalysisChart.destroy();
                }
            } catch (e) {
                console.warn('Error destroying existing charts:', e);
                // Continue even if destroy fails
            }
            
            // Get labels and datasets
            const dates = historyData.map(item => item.date);
            const hours = historyData.map(item => item.hours);
            const losses = historyData.map(item => item.loss);
            const defects = historyData.map(item => item.defects);
            const qualityRates = historyData.map(item => item.quality_rate);
            
            // Initialize Impact Trend Chart
            const impactCtx = impactCanvas.getContext('2d');
            window.impactTrendChart = new Chart(impactCtx, {
                type: 'bar',
                data: {
                    labels: dates,
                    datasets: [
                        {
                            label: 'Horas de Parada',
                            data: hours,
                            backgroundColor: 'rgba(234, 88, 12, 0.6)',
                            borderColor: 'rgba(234, 88, 12, 1)',
                            borderWidth: 1,
                            yAxisID: 'y-axis-1'
                        },
                        {
                            label: 'Perda de Produção',
                            data: losses,
                            backgroundColor: 'rgba(220, 38, 38, 0.6)',
                            borderColor: 'rgba(220, 38, 38, 1)',
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
                            grid: {
                                display: false
                            }
                        },
                        'y-axis-1': {
                            type: 'linear',
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Horas'
                            },
                            beginAtZero: true,
                            grid: {
                                display: false
                            }
                        },
                        'y-axis-2': {
                            type: 'linear',
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Unidades'
                            },
                            beginAtZero: true,
                            grid: {
                                display: false
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
                            labels: {
                                usePointStyle: true,
                                boxWidth: 6
                            }
                        }
                    }
                }
            });
            
            // Initialize Quality Analysis Chart
            const qualityCtx = qualityCanvas.getContext('2d');
            window.qualityAnalysisChart = new Chart(qualityCtx, {
                type: 'bar',
                data: {
                    labels: dates,
                    datasets: [
                        {
                            label: 'Quantidade de Defeitos',
                            data: defects,
                            backgroundColor: 'rgba(239, 68, 68, 0.6)',
                            borderColor: 'rgba(239, 68, 68, 1)',
                            borderWidth: 1,
                            yAxisID: 'y-axis-1'
                        },
                        {
                            label: 'Taxa de Qualidade',
                            data: qualityRates,
                            type: 'line',
                            backgroundColor: 'rgba(16, 185, 129, 0.2)',
                            borderColor: 'rgba(16, 185, 129, 1)',
                            borderWidth: 2,
                            pointRadius: 4,
                            pointBackgroundColor: 'rgba(16, 185, 129, 1)',
                            fill: false,
                            yAxisID: 'y-axis-2'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        'y-axis-1': {
                            type: 'linear',
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Unidades'
                            },
                            beginAtZero: true,
                            grid: {
                                display: false
                            }
                        },
                        'y-axis-2': {
                            type: 'linear',
                            position: 'right',
                            title: {
                                display: true,
                                text: '%'
                            },
                            min: 0,
                            max: 100,
                            grid: {
                                display: false
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
                            labels: {
                                usePointStyle: true,
                                boxWidth: 6
                            }
                        }
                    }
                }
            });
            
            console.log('Charts initialized successfully');
        }
        
        // Setup a global function to initialize charts that can be called from Alpine.js
        window.initBreakdownCharts = function() {
            console.log('Manual chart initialization triggered');
            setTimeout(initializeBreakdownCharts, 500);
        }
        
        // Initialize charts when view is loaded with a longer delay to ensure DOM is fully rendered
        setTimeout(initializeBreakdownCharts, 1000);
    });
    });
</script>

</div>
</div>