<div>
    <h2 class="text-2xl font-semibold mb-6">Dashboard Overview</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-wrap gap-4 items-center">
            <div>
                <label for="filterYear" class="block text-sm font-medium text-gray-700 mb-1">Ano</label>
                <select id="filterYear" wire:model.live="filterYear" class="block w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @for ($year = date('Y') - 2; $year <= date('Y') + 1; $year++)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endfor
                </select>
            </div>

            <div>
                <label for="filterMonth" class="block text-sm font-medium text-gray-700 mb-1">Mês</label>
                <select id="filterMonth" wire:model.live="filterMonth" class="block w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="all">Todos</option>
                    <option value="01">Janeiro</option>
                    <option value="02">Fevereiro</option>
                    <option value="03">Março</option>
                    <option value="04">Abril</option>
                    <option value="05">Maio</option>
                    <option value="06">Junho</option>
                    <option value="07">Julho</option>
                    <option value="08">Agosto</option>
                    <option value="09">Setembro</option>
                    <option value="10">Outubro</option>
                    <option value="11">Novembro</option>
                    <option value="12">Dezembro</option>
                </select>
            </div>

            <div>
                <label for="filterStatus" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="filterStatus" wire:model.live="filterStatus" class="block w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="all">Todos</option>
                    <option value="pending">Pendentes</option>
                    <option value="in_progress">Em andamento</option>
                    <option value="completed">Concluídos</option>
                    <option value="cancelled">Cancelados</option>
                </select>
            </div>

            <div>
                <label for="filterDepartment" class="block text-sm font-medium text-gray-700 mb-1">Departamento</label>
                <select id="filterDepartment" wire:model.live="filterDepartment" class="block w-40 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="all">Todos</option>
                    <!-- Opções de departamento seriam geradas dinamicamente -->
                </select>
            </div>

            <div class="self-end">
                <button type="button" wire:click="loadDashboardData" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Atualizar
                </button>
            </div>
        </div>
    </div>

    <!-- Metrics Overview -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <h3 class="text-lg font-medium mb-4">Metrics Overview</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <div class="metrics-card">
                <div class="icon purple">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="number">{{ $scheduledTasks }}</div>
                <div class="text-sm text-gray-500">Total Maintenance Plans</div>
            </div>

            <div class="metrics-card">
                <div class="icon red">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="number">{{ $overdueTasks }}</div>
                <div class="text-sm text-gray-500">Overdue Tasks</div>
            </div>

            <div class="metrics-card">
                <div class="icon yellow">
                    <i class="fas fa-tools"></i>
                </div>
                <div class="number">{{ $equipmentInMaintenance }}</div>
                <div class="text-sm text-gray-500">Equipment in Maintenance</div>
            </div>

            <div class="metrics-card">
                <div class="icon red">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="number">{{ $equipmentOutOfService }}</div>
                <div class="text-sm text-gray-500">Equipment Out of Service</div>
            </div>

            <div class="metrics-card">
                <div class="icon blue">
                    <i class="fas fa-wrench"></i>
                </div>
                <div class="number">{{ $equipmentCount }}</div>
                <div class="text-sm text-gray-500">Total Equipment</div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Monthly Distribution Chart -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-medium mb-4">Monthly Maintenance Distribution</h3>
            <div class="h-80" id="monthlyChart"></div>
        </div>

        <!-- Status Distribution Chart -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-medium mb-4">Maintenance Status</h3>
            <div class="h-80" id="statusChart"></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Planned Maintenance Chart -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-medium mb-4">Planned Maintenance Types</h3>
            <div class="h-80" id="planningChart"></div>
        </div>

        <!-- Corrective Maintenance Chart -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-medium mb-4">Corrective Maintenance by Cause</h3>
            <div class="h-80" id="correctiveChart"></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Maintenance Alerts -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-medium mb-4">Maintenance Alerts</h3>

            @forelse($maintenanceAlerts as $alert)
                <div class="alert-item flex justify-between items-center mb-3">
                    <div class="flex-1">
                        <div class="font-medium">{{ $alert['title'] }}</div>
                        <div class="text-sm text-gray-500">{{ $alert['equipment'] }}</div>
                        <div class="flex items-center text-xs space-x-2 mt-1">
                            <span class="text-gray-600">{{ $alert['date'] }}</span>
                            <span class="px-2 py-0.5 rounded-full text-xs
                                {{ $alert['status'] === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $alert['days_until'] }}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center">
                        @if($alert['status'] === 'overdue')
                            <span class="overdue-badge mr-3">Overdue</span>
                        @endif
                        <button wire:click="markAlertAsCompleted({{ $alert['id'] }})" class="text-green-500 hover:text-green-700">
                            <i class="fas fa-check-circle"></i>
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center text-gray-500 py-4">
                    No maintenance alerts found
                </div>
            @endforelse
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-medium mb-4">Quick Actions</h3>

            <div class="grid grid-cols-2 gap-4">
                <a href="{{ route('maintenance.plan') }}" class="action-card">
                    <div class="icon blue">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="title">Schedule Maintenance</div>
                    <div class="subtitle">Create a new maintenance task</div>
                </a>

                <a href="{{ route('maintenance.equipment') }}" class="action-card">
                    <div class="icon green">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div class="title">Add Equipment</div>
                    <div class="subtitle">Register new equipment</div>
                </a>

                <a href="{{ route('maintenance.corrective') }}" class="action-card">
                    <div class="icon purple">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="title">Corrective Maintenance</div>
                    <div class="subtitle">Report a maintenance issue</div>
                </a>

                <a href="{{ route('maintenance.reports') }}" class="action-card">
                    <div class="icon orange">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="title">View Reports</div>
                    <div class="subtitle">Analyze maintenance data</div>
                </a>
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('livewire:initialized', function () {
            initCharts(@json($monthlyTasksData), @json($statusDistributionData), @json($planningChartData), @json($correctiveChartData));

            Livewire.hook('message.processed', (message, component) => {
                if (component.name === 'maintenance-dashboard') {
                    initCharts(@json($monthlyTasksData), @json($statusDistributionData), @json($planningChartData), @json($correctiveChartData));
                }
            });
        });

        let charts = {};

        function initCharts(monthlyData, statusData, planningData, correctiveData) {
            // Destruir gráficos existentes para evitar duplicação
            Object.values(charts).forEach(chart => chart.destroy());
            charts = {};

            // Gráfico de distribuição mensal
            const monthlyCtx = document.getElementById('monthlyChart');
            charts.monthlyChart = new Chart(monthlyCtx, {
                type: 'bar',
                data: monthlyData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Número de Tarefas'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Mês'
                            }
                        }
                    }
                }
            });

            // Gráfico de status
            const statusCtx = document.getElementById('statusChart');
            charts.statusChart = new Chart(statusCtx, {
                type: 'doughnut',
                data: statusData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Gráfico de manutenção planejada
            const planningCtx = document.getElementById('planningChart');
            charts.planningChart = new Chart(planningCtx, {
                type: 'pie',
                data: planningData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Gráfico de manutenção corretiva
            const correctiveCtx = document.getElementById('correctiveChart');
            charts.correctiveChart = new Chart(correctiveCtx, {
                type: 'bar',
                data: correctiveData,
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Número de Ocorrências'
                            }
                        }
                    }
                }
            });
        }
    </script>
</div>
