@extends('layouts.livewire')

@section('title', __('messages.dashboard_title'))

@section('content')
<div class="py-6 container mx-auto px-4">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-bold text-gray-900 flex items-center">
            <i class="fas fa-tachometer-alt text-blue-600 mr-3"></i> {{ __('messages.maintenance_dashboard') }}
        </h1>
        <x-maintenance-guide-link />
    </div>

    <!-- Painéis de métricas principais -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">{{ __('messages.total_equipment') }}</h3>
                    <p class="text-3xl font-bold text-blue-600 mt-2">
                        {{ App\Models\MaintenanceEquipment::count() }}
                    </p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-tools text-blue-500 text-xl"></i>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-4">
                <a href="{{ route('maintenance.equipment') }}" class="text-blue-600 hover:underline flex items-center">
                    <span>{{ __('messages.view_all_equipment') }}</span>
                    <i class="fas fa-arrow-right ml-1 text-xs"></i>
                </a>
            </p>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">{{ __('messages.scheduled_maintenance') }}</h3>
                    <p class="text-3xl font-bold text-green-600 mt-2">
                        {{ App\Models\MaintenancePlan::whereIn('status', ['schedule', 'pending'])->count() }}
                    </p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-calendar-check text-green-500 text-xl"></i>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-4">
                <a href="{{ route('maintenance.plan') }}" class="text-green-600 hover:underline flex items-center">
                    <span>{{ __('messages.view_maintenance_plan') }}</span>
                    <i class="fas fa-arrow-right ml-1 text-xs"></i>
                </a>
            </p>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">{{ __('messages.pending_corrective') }}</h3>
                    <p class="text-3xl font-bold text-yellow-600 mt-2">
                        {{ App\Models\MaintenanceCorrective::where('status', 'pending')->count() }}
                    </p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <i class="fas fa-wrench text-yellow-500 text-xl"></i>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-4">
                <a href="{{ route('maintenance.corrective') }}" class="text-yellow-600 hover:underline flex items-center">
                    <span>{{ __('messages.view_corrective_maintenance') }}</span>
                    <i class="fas fa-arrow-right ml-1 text-xs"></i>
                </a>
            </p>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">{{ __('messages.part_requests') }}</h3>
                    <p class="text-3xl font-bold text-red-600 mt-2">
                        {{ App\Models\EquipmentPartRequest::where('status', 'pending')->count() }}
                    </p>
                </div>
                <div class="bg-red-100 p-3 rounded-full">
                    <i class="fas fa-box-open text-red-500 text-xl"></i>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-4">
                <a href="{{ route('equipment.part-requests') }}" class="text-red-600 hover:underline flex items-center">
                    <span>{{ __('messages.view_part_requests') }}</span>
                    <i class="fas fa-arrow-right ml-1 text-xs"></i>
                </a>
            </p>
        </div>
    </div>

    <!-- Painéis de informações e estatísticas -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Widget Calendário -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    {{ __('messages.maintenance_schedule') }}
                </h3>
            </div>
            <div class="p-4 max-h-80 overflow-y-auto">
                @php
                    $startDate = \Carbon\Carbon::today();
                    $endDate = \Carbon\Carbon::today()->addDays(7);
                    
                    $upcomingMaintenance = App\Models\MaintenancePlan::where(function($query) use ($startDate, $endDate) {
                            $query->whereDate('next_date', '>=', $startDate)
                                  ->whereDate('next_date', '<=', $endDate);
                        })
                        ->whereNotNull('equipment_id')
                        ->whereIn('status', ['schedule', 'pending'])
                        ->with(['equipment', 'assignedTo'])
                        ->orderBy('next_date')
                        ->take(5)
                        ->get();
                @endphp
                
                @if($upcomingMaintenance->count() > 0)
                    <div class="space-y-3">
                        @foreach($upcomingMaintenance as $plan)
                            <div class="border border-gray-200 rounded-md p-3 bg-white hover:bg-blue-50 transition-colors hover:border-blue-200">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-start space-x-3">
                                        <div class="bg-blue-100 text-blue-600 p-2 rounded-full flex-shrink-0">
                                            <i class="fas fa-calendar-day"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-800">{{ $plan->title }}</h4>
                                            <p class="text-sm text-gray-600">{{ $plan->equipment->name ?? __('messages.no_equipment_specified') }}</p>
                                            <div class="flex items-center mt-1 text-xs text-gray-500">
                                                <i class="fas fa-user-hard-hat mr-1"></i>
                                                <span>{{ optional($plan->assignedTo)->name ?? __('messages.unassigned') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs bg-{{ $plan->status == 'schedule' ? 'blue' : 'yellow' }}-100 text-{{ $plan->status == 'schedule' ? 'blue' : 'yellow' }}-700 px-2 py-1 rounded-full inline-block">
                                            {{ __('messages.'.$plan->status) }}
                                        </div>
                                        <div class="mt-1 text-sm font-medium text-gray-700">
                                            {{ \Carbon\Carbon::parse($plan->next_date)->format('d/m/Y') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 flex flex-col items-center">
                        <div class="bg-blue-50 p-3 rounded-full mb-2">
                            <i class="fas fa-calendar-times text-blue-400 text-xl"></i>
                        </div>
                        <p class="text-gray-500">{{ __('messages.no_scheduled_maintenance') }}</p>
                    </div>
                @endif
            </div>
            <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 text-right">
                <a href="{{ route('maintenance.schedule-calendar') }}" class="text-blue-600 hover:underline text-sm flex items-center justify-end">
                    <span>{{ __('messages.view_full_calendar') }}</span>
                    <i class="fas fa-arrow-right ml-1 text-xs"></i>
                </a>
            </div>
        </div>

        <!-- Widget de Tarefas Recentes -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-green-600 to-green-700 px-4 py-3">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-tasks mr-2"></i>
                    {{ __('messages.recent_tasks') }}
                </h3>
            </div>
            <div class="p-4 max-h-80 overflow-y-auto">
                @php
                    $recentTasks = App\Models\MaintenanceTaskLog::with(['equipment', 'user'])
                        ->whereNotNull('equipment_id')
                        ->orderBy('completed_at', 'desc')
                        ->take(5)
                        ->get();
                @endphp
                
                @if($recentTasks->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentTasks as $task)
                            <div class="border border-gray-200 rounded-md p-3 bg-white hover:bg-blue-50 transition-colors hover:border-blue-200">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-start space-x-3">
                                        <div class="bg-green-100 text-green-600 p-2 rounded-full flex-shrink-0">
                                            <i class="fas fa-tasks"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-800">{{ $task->title }}</h4>
                                            <p class="text-sm text-gray-600">{{ $task->equipment->name ?? __('messages.no_equipment_specified') }}</p>
                                            <div class="flex items-center mt-1 text-xs text-gray-500">
                                                <i class="fas fa-user mr-1"></i>
                                                <span>{{ $task->user->name ?? __('messages.unknown_user') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full inline-block">
                                            {{ __('messages.'.$task->status) }}
                                        </div>
                                        <div class="mt-1 text-sm font-medium text-gray-700">
                                            {{ \Carbon\Carbon::parse($task->completed_at)->format('d/m/Y') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 flex flex-col items-center">
                        <div class="bg-green-50 p-3 rounded-full mb-2">
                            <i class="fas fa-clipboard-list text-green-400 text-xl"></i>
                        </div>
                        <p class="text-gray-500">{{ __('messages.no_recent_tasks') }}</p>
                    </div>
                @endif
            </div>
            <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 text-right">
                <a href="{{ route('maintenance.tasks') }}" class="text-green-600 hover:underline text-sm flex items-center justify-end">
                    <span>{{ __('messages.view_all_tasks') }}</span>
                    <i class="fas fa-arrow-right ml-1 text-xs"></i>
                </a>
            </div>
        </div>

        <!-- Widget de Técnicos Disponíveis -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-4 py-3">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-user-hard-hat mr-2"></i>
                    {{ __('messages.available_technicians') }}
                </h3>
            </div>
            <div class="p-4 max-h-80 overflow-y-auto">
                @php
                    $technicians = App\Models\User::whereHas('roles', function($query) {
                            $query->where('name', 'technician');
                        })
                        ->withCount(['maintenanceTasks' => function($query) {
                            $query->where('status', 'in_progress');
                        }])
                        ->orderBy('maintenance_tasks_count', 'desc')
                        ->take(5)
                        ->get();
                @endphp
                
                @if($technicians->count() > 0)
                    <div class="space-y-3">
                        @foreach($technicians as $tech)
                            @php
                                $availabilityStatus = $tech->maintenance_tasks_count > 2 ? 'busy' : ($tech->maintenance_tasks_count > 0 ? 'limited' : 'available');
                                $statusColor = [
                                    'available' => 'green',
                                    'limited' => 'yellow',
                                    'busy' => 'red'
                                ][$availabilityStatus];
                            @endphp
                            <div class="border border-gray-200 rounded-md p-3 bg-white hover:bg-blue-50 transition-colors hover:border-blue-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="bg-purple-100 text-purple-600 p-2 rounded-full flex-shrink-0">
                                            <i class="fas fa-user-hard-hat"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-800">{{ $tech->name }}</h4>
                                            <div class="text-sm text-gray-600">
                                                {{ __('messages.active_tasks') }}: {{ $tech->maintenance_tasks_count }}
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-xs bg-{{ $statusColor }}-100 text-{{ $statusColor }}-700 px-2 py-1 rounded-full inline-block">
                                            {{ __('messages.' . $availabilityStatus) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 flex flex-col items-center">
                        <div class="bg-purple-50 p-3 rounded-full mb-2">
                            <i class="fas fa-user-hard-hat text-purple-400 text-xl"></i>
                        </div>
                        <p class="text-gray-500">{{ __('messages.no_technicians_found') }}</p>
                    </div>
                @endif
            </div>
            <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 text-right">
                <a href="{{ route('maintenance.technicians') }}" class="text-purple-600 hover:underline text-sm flex items-center justify-end">
                    <span>{{ __('messages.manage_technicians') }}</span>
                    <i class="fas fa-arrow-right ml-1 text-xs"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Gráficos e Análises -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Gráfico de Status de Manutenção -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-700 flex items-center">
                    <i class="fas fa-chart-pie text-blue-500 mr-2"></i>
                    {{ __('messages.maintenance_status_distribution') }}
                </h3>
            </div>
            <div class="p-6">
                @php
                    $statuses = ['pending', 'in_progress', 'completed', 'cancelled'];
                    $statusCounts = [];
                    $statusColors = [
                        'pending' => '#FCD34D',    // yellow-300
                        'in_progress' => '#60A5FA', // blue-400
                        'completed' => '#34D399',   // green-400
                        'cancelled' => '#F87171'    // red-400
                    ];
                    
                    // Consulta para obter tarefas de manutenção relacionadas a equipamentos
                    foreach ($statuses as $status) {
                        $statusCounts[$status] = App\Models\MaintenanceTaskLog::whereNotNull('equipment_id')
                            ->where('status', $status)
                            ->count();
                    }
                    
                    $chartId = 'maintenance-status-chart-' . rand(1000, 9999);
                @endphp
                
                <div class="relative" style="height: 300px">
                    <canvas id="{{ $chartId }}"></canvas>
                </div>
                
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const ctx = document.getElementById('{{ $chartId }}').getContext('2d');
                        
                        new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: [  
                                    @foreach($statuses as $status)
                                        '{{ __('messages.' . $status) }}',
                                    @endforeach
                                ],
                                datasets: [{
                                    data: [
                                        @foreach($statuses as $status)
                                            {{ $statusCounts[$status] }},
                                        @endforeach
                                    ],
                                    backgroundColor: [
                                        @foreach($statuses as $status)
                                            '{{ $statusColors[$status] }}',
                                        @endforeach
                                    ],
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                const value = context.raw;
                                                const total = context.dataset.data.reduce((acc, val) => acc + val, 0);
                                                const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                                return `${context.label}: ${value} (${percentage}%)`;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    });
                </script>
            </div>
        </div>

        <!-- Gráfico de Manutenção por Tipo -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-700 flex items-center">
                    <i class="fas fa-chart-bar text-green-500 mr-2"></i>
                    {{ __('messages.maintenance_by_type') }}
                </h3>
            </div>
            <div class="p-6">
                @php
                    $types = ['preventive', 'corrective', 'predictive', 'conditional', 'other'];
                    $typeCounts = [];
                    $typeColors = [
                        'preventive' => '#3B82F6',  // blue-500
                        'corrective' => '#EF4444',  // red-500
                        'predictive' => '#8B5CF6',  // violet-500
                        'conditional' => '#F97316', // orange-500
                        'other' => '#6B7280'        // gray-500
                    ];
                    
                    // Consulta para obter tipos de manutenção específicos para equipamentos
                    foreach ($types as $type) {
                        $typeCounts[$type] = App\Models\MaintenanceTaskLog::whereNotNull('equipment_id')
                            ->where('type', $type)
                            ->count();
                    }
                    
                    $chartId = 'maintenance-type-chart-' . rand(1000, 9999);
                @endphp
                
                <div class="relative" style="height: 300px">
                    <canvas id="{{ $chartId }}"></canvas>
                </div>
                
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const ctx = document.getElementById('{{ $chartId }}').getContext('2d');
                        
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: [  
                                    @foreach($types as $type)
                                        '{{ __('messages.' . $type) }}',
                                    @endforeach
                                ],
                                datasets: [{
                                    label: '{{ __('messages.number_of_tasks') }}',
                                    data: [
                                        @foreach($types as $type)
                                            {{ $typeCounts[$type] }},
                                        @endforeach
                                    ],
                                    backgroundColor: [
                                        @foreach($types as $type)
                                            '{{ $typeColors[$type] }}',
                                        @endforeach
                                    ],
                                    borderColor: [
                                        @foreach($types as $type)
                                            '{{ $typeColors[$type] }}',
                                        @endforeach
                                    ],
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            precision: 0
                                        }
                                    }
                                }
                            }
                        });
                    });
                </script>
            </div>
        </div>
    </div>

    <!-- Gráficos de Desempenho de Manutenção -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Task Description Wise Status -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-700 flex items-center">
                    <i class="fas fa-tasks text-indigo-500 mr-2"></i>
                    {{ __('messages.task_description_wise_status') }}
                </h3>
            </div>
            <div class="p-6">
                @php
                    // Obter descrições de tarefas distintas (limitando a 5 para o gráfico não ficar sobrecarregado)
                    $taskDescriptions = App\Models\MaintenanceTaskLog::select('description')
                        ->whereNotNull('equipment_id')
                        ->whereNotNull('description')
                        ->distinct('description')
                        ->limit(5)
                        ->pluck('description')
                        ->toArray();
                    
                    $statuses = ['planned', 'completed', 'pending'];
                    $statusColors = [
                        'planned' => '#93C5FD',   // blue-300
                        'completed' => '#86EFAC', // green-300
                        'pending' => '#FDE68A'    // amber-200
                    ];
                    
                    $descriptionData = [];
                    foreach ($taskDescriptions as $description) {
                        $descriptionData[$description] = [
                            'planned' => App\Models\MaintenanceTaskLog::where('description', $description)
                                ->where('status', 'planned')
                                ->whereNotNull('equipment_id')
                                ->count(),
                            'completed' => App\Models\MaintenanceTaskLog::where('description', $description)
                                ->where('status', 'completed')
                                ->whereNotNull('equipment_id')
                                ->count(),
                            'pending' => App\Models\MaintenanceTaskLog::where('description', $description)
                                ->where('status', 'pending')
                                ->whereNotNull('equipment_id')
                                ->count()
                        ];
                    }
                    
                    $taskDescChartId = 'task-description-chart-' . rand(1000, 9999);
                @endphp
                
                <div class="relative" style="height: 300px">
                    <canvas id="{{ $taskDescChartId }}"></canvas>
                </div>
                
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const ctx = document.getElementById('{{ $taskDescChartId }}').getContext('2d');
                        
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: [  
                                    @foreach($taskDescriptions as $description)
                                        '{{ Str::limit($description, 20) }}',
                                    @endforeach
                                ],
                                datasets: [
                                    @foreach($statuses as $status)
                                    {
                                        label: '{{ __("messages.{$status}") }}',
                                        data: [
                                            @foreach($taskDescriptions as $description)
                                                {{ $descriptionData[$description][$status] }},
                                            @endforeach
                                        ],
                                        backgroundColor: '{{ $statusColors[$status] }}',
                                        borderColor: '{{ $statusColors[$status] }}',
                                        borderWidth: 1
                                    },
                                    @endforeach
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    x: {
                                        stacked: true,
                                    },
                                    y: {
                                        stacked: true,
                                        beginAtZero: true,
                                        ticks: {
                                            precision: 0
                                        }
                                    }
                                }
                            }
                        });
                    });
                </script>
            </div>
        </div>

        <!-- Equipment Maintenance Status -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-700 flex items-center">
                    <i class="fas fa-tools text-orange-500 mr-2"></i>
                    {{ __('messages.equipment_maintenance_status') }}
                </h3>
            </div>
            <div class="p-6">
                @php
                    // Obter os equipamentos com mais atividades de manutenção (limitando a 4)
                    $topEquipments = App\Models\MaintenanceTaskLog::select('equipment_id', DB::raw('count(*) as total'))
                        ->whereNotNull('equipment_id')
                        ->groupBy('equipment_id')
                        ->orderBy('total', 'desc')
                        ->limit(4)
                        ->get();
                    
                    $equipmentIds = $topEquipments->pluck('equipment_id')->toArray();
                    $equipmentNames = [];
                    $equipmentStatusCounts = [];
                    $maintenanceStatuses = ['total_plans', 'in_progress', 'completed', 'cancelled'];
                    $statusColors = [
                        'total_plans' => '#93C5FD',  // blue-300
                        'in_progress' => '#FCD34D', // yellow-300
                        'completed' => '#34D399',   // green-400
                        'cancelled' => '#F87171'    // red-400
                    ];
                    
                    foreach ($equipmentIds as $equipmentId) {
                        $equipment = App\Models\MaintenanceEquipment::find($equipmentId);
                        if ($equipment) {
                            $equipmentNames[] = $equipment->name;
                            
                            $equipmentStatusCounts[] = [
                                'total_plans' => App\Models\MaintenanceTaskLog::where('equipment_id', $equipmentId)->count(),
                                'in_progress' => App\Models\MaintenanceTaskLog::where('equipment_id', $equipmentId)
                                    ->where('status', 'in_progress')->count(),
                                'completed' => App\Models\MaintenanceTaskLog::where('equipment_id', $equipmentId)
                                    ->where('status', 'completed')->count(),
                                'cancelled' => App\Models\MaintenanceTaskLog::where('equipment_id', $equipmentId)
                                    ->where('status', 'cancelled')->count()
                            ];
                        }
                    }
                    
                    $equipmentChartId = 'equipment-status-chart-' . rand(1000, 9999);
                @endphp
                
                <div class="relative" style="height: 300px">
                    <canvas id="{{ $equipmentChartId }}"></canvas>
                </div>
                
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const ctx = document.getElementById('{{ $equipmentChartId }}').getContext('2d');
                        
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: [  
                                    @foreach($equipmentNames as $name)
                                        '{{ Str::limit($name, 20) }}',
                                    @endforeach
                                ],
                                datasets: [
                                    @foreach($maintenanceStatuses as $index => $status)
                                    {
                                        label: '{{ __("messages.{$status}") }}',
                                        data: [
                                            @foreach($equipmentStatusCounts as $counts)
                                                {{ $counts[$status] }},
                                            @endforeach
                                        ],
                                        backgroundColor: '{{ $statusColors[$status] }}',
                                        borderColor: '{{ $statusColors[$status] }}',
                                        borderWidth: 1
                                    },
                                    @endforeach
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            precision: 0
                                        }
                                    }
                                }
                            }
                        });
                    });
                </script>
            </div>
        </div>
    </div>
    
    <!-- Seção de Acesso Rápido -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
        <div class="bg-gradient-to-r from-gray-700 to-gray-800 px-4 py-3">
            <h3 class="text-lg font-medium text-white flex items-center">
                <i class="fas fa-th-large mr-2"></i>
                {{ __('messages.quick_access') }}
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <a href="{{ route('maintenance.plan') }}" class="bg-gray-50 hover:bg-blue-50 p-4 rounded-lg text-center transition-colors border border-gray-200 hover:border-blue-200 group">
                    <div class="bg-blue-100 text-blue-500 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-2 group-hover:bg-blue-500 group-hover:text-white transition-colors">
                        <i class="fas fa-calendar-alt text-xl"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700">{{ __('messages.maintenance_planning') }}</span>
                </a>

                <a href="{{ route('maintenance.corrective') }}" class="bg-gray-50 hover:bg-yellow-50 p-4 rounded-lg text-center transition-colors border border-gray-200 hover:border-yellow-200 group">
                    <div class="bg-yellow-100 text-yellow-500 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-2 group-hover:bg-yellow-500 group-hover:text-white transition-colors">
                        <i class="fas fa-wrench text-xl"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-yellow-700">{{ __('messages.corrective_maintenance') }}</span>
                </a>

                <a href="{{ route('maintenance.equipment') }}" class="bg-gray-50 hover:bg-green-50 p-4 rounded-lg text-center transition-colors border border-gray-200 hover:border-green-200 group">
                    <div class="bg-green-100 text-green-500 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-2 group-hover:bg-green-500 group-hover:text-white transition-colors">
                        <i class="fas fa-tools text-xl"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-green-700">{{ __('messages.equipment_management') }}</span>
                </a>

                <a href="{{ route('maintenance.parts') }}" class="bg-gray-50 hover:bg-red-50 p-4 rounded-lg text-center transition-colors border border-gray-200 hover:border-red-200 group">
                    <div class="bg-red-100 text-red-500 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-2 group-hover:bg-red-500 group-hover:text-white transition-colors">
                        <i class="fas fa-cogs text-xl"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-red-700">{{ __('messages.spare_parts') }}</span>
                </a>

                <a href="{{ route('maintenance.areas-lines') }}" class="bg-gray-50 hover:bg-purple-50 p-4 rounded-lg text-center transition-colors border border-gray-200 hover:border-purple-200 group">
                    <div class="bg-purple-100 text-purple-500 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-2 group-hover:bg-purple-500 group-hover:text-white transition-colors">
                        <i class="fas fa-project-diagram text-xl"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-purple-700">{{ __('messages.areas_and_lines') }}</span>
                </a>

                <a href="{{ route('maintenance.reports') }}" class="bg-gray-50 hover:bg-indigo-50 p-4 rounded-lg text-center transition-colors border border-gray-200 hover:border-indigo-200 group">
                    <div class="bg-indigo-100 text-indigo-500 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-2 group-hover:bg-indigo-500 group-hover:text-white transition-colors">
                        <i class="fas fa-chart-line text-xl"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-700">{{ __('messages.reports_and_analytics') }}</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Seção Dashboard Completo -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
            <h3 class="text-lg font-medium text-white flex items-center">
                <i class="fas fa-analytics mr-2"></i>
                {{ __('messages.detailed_dashboard') }}
            </h3>
        </div>
        <div class="p-6">
            <livewire:maintenance-dashboard />
        </div>
    </div>
</div>
@endsection
