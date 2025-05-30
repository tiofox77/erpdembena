<div>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold flex items-center">
            <i class="fas fa-tachometer-alt text-indigo-600 mr-2"></i> {{ __('messages.dashboard_overview') }}
        </h2>
        <x-maintenance-guide-link />
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <h3 class="text-lg font-semibold mb-3 flex items-center">
            <i class="fas fa-filter text-indigo-500 mr-2"></i> {{ __('messages.filter_options') }}
        </h3>
        <div class="flex flex-wrap gap-4 items-center">
            <div>
                <label for="filterYear" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.year') }}</label>
                <select id="filterYear" wire:model="filterYear" class="block w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @for ($year = date('Y') - 2; $year <= date('Y') + 1; $year++)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endfor
                </select>
            </div>

            <div>
                <label for="filterMonth" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.month') }}</label>
                <select id="filterMonth" wire:model="filterMonth" class="block w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="all">{{ __('messages.all') }}</option>
                    <option value="01">{{ __('messages.january') }}</option>
                    <option value="02">{{ __('messages.february') }}</option>
                    <option value="03">{{ __('messages.march') }}</option>
                    <option value="04">{{ __('messages.april') }}</option>
                    <option value="05">{{ __('messages.may') }}</option>
                    <option value="06">{{ __('messages.june') }}</option>
                    <option value="07">{{ __('messages.july') }}</option>
                    <option value="08">{{ __('messages.august') }}</option>
                    <option value="09">{{ __('messages.september') }}</option>
                    <option value="10">{{ __('messages.october') }}</option>
                    <option value="11">{{ __('messages.november') }}</option>
                    <option value="12">{{ __('messages.december') }}</option>
                </select>
            </div>

            <div>
                <label for="filterStatus" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.status') }}</label>
                <select id="filterStatus" wire:model="filterStatus" class="block w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="all">{{ __('messages.all') }}</option>
                    <option value="pending">{{ __('messages.pending') }}</option>
                    <option value="in_progress">{{ __('messages.in_progress') }}</option>
                    <option value="completed">{{ __('messages.completed') }}</option>
                    <option value="cancelled">{{ __('messages.cancelled') }}</option>
                    <option value="schedule">{{ __('messages.schedule') }}</option>
                </select>
            </div>

            <div>
                <label for="filterArea" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.area') }}</label>
                <select id="filterArea" wire:model="filterArea" class="block w-40 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="all">All</option>
                    <!-- Area options would be generated dynamically -->
                </select>
            </div>

            <div class="self-end">
                <button type="button" wire:click="refreshDashboard" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    {{ __('messages.update_filters') }}
                </button>
            </div>
        </div>
    </div>

    <!-- KPI Cards Overview -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6 dashboard-card">
        <h3 class="text-xl font-semibold mb-4">{{ __('messages.kpi_overview') }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="bg-blue-50 p-4 rounded-lg text-center border border-blue-100 transition-all hover:shadow-md">
                <div class="flex items-center justify-center mb-2 text-blue-500">
                    <i class="fas fa-calendar-check text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold mb-1">{{ __('messages.planned') }}</h3>
                <p class="text-3xl font-bold text-blue-700">{{ $plannedTasksCount }}</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg text-center border border-green-100 transition-all hover:shadow-md">
                <div class="flex items-center justify-center mb-2 text-green-500">
                    <i class="fas fa-check-circle text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold mb-1">{{ __('messages.actual') }}</h3>
                <p class="text-3xl font-bold text-green-700">{{ $actualTasksCount }}</p>
            </div>
            <div class="bg-indigo-50 p-4 rounded-lg text-center border border-indigo-100 transition-all hover:shadow-md">
                <div class="flex items-center justify-center mb-2 text-indigo-500">
                    <i class="fas fa-chart-line text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold mb-1">{{ __('messages.compliance_percentage') }}</h3>
                <p class="text-3xl font-bold text-indigo-700">{{ $compliancePercentage }}%</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-yellow-50 p-4 rounded-lg text-center border border-yellow-100 transition-all hover:shadow-md">
                <div class="flex items-center justify-center mb-2 text-yellow-500">
                    <i class="fas fa-clock text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold mb-1">{{ __('messages.pending') }}</h3>
                <p class="text-3xl font-bold text-yellow-700">{{ $pendingTasksCount }}</p>
            </div>
            <div class="bg-red-50 p-4 rounded-lg text-center border border-red-100 transition-all hover:shadow-md">
                <div class="flex items-center justify-center mb-2 text-red-500">
                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold mb-1">{{ __('messages.non_compliance_percentage') }}</h3>
                <p class="text-3xl font-bold text-red-700">{{ $nonCompliancePercentage }}%</p>
            </div>
        </div>
    </div>

    <!-- Metrics Overview -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6 dashboard-card">
        <h3 class="text-xl font-semibold mb-4">{{ __('messages.metrics_overview') }}</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <div class="flex flex-col items-center p-4 bg-purple-50 rounded-lg border border-purple-100 transition-all hover:shadow-md">
                <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center mb-3 text-purple-600">
                    <i class="fas fa-calendar-alt text-xl"></i>
                </div>
                <div class="text-2xl font-bold text-purple-700">{{ $scheduledTasks }}</div>
                <div class="text-sm text-gray-600 text-center mt-1">{{ __('messages.total_maintenance_plans') }}</div>
            </div>

            <div class="flex flex-col items-center p-4 bg-red-50 rounded-lg border border-red-100 transition-all hover:shadow-md">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mb-3 text-red-600">
                    <i class="fas fa-exclamation-circle text-xl"></i>
                </div>
                <div class="text-2xl font-bold text-red-700">{{ $overdueTasks }}</div>
                <div class="text-sm text-gray-600 text-center mt-1">{{ __('messages.overdue_tasks') }}</div>
            </div>

            <div class="flex flex-col items-center p-4 bg-yellow-50 rounded-lg border border-yellow-100 transition-all hover:shadow-md">
                <div class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center mb-3 text-yellow-600">
                    <i class="fas fa-tools text-xl"></i>
                </div>
                <div class="text-2xl font-bold text-yellow-700">{{ $equipmentInMaintenance }}</div>
                <div class="text-sm text-gray-600 text-center mt-1">{{ __('messages.equipment_in_maintenance') }}</div>
            </div>

            <div class="flex flex-col items-center p-4 bg-red-50 rounded-lg border border-red-100 transition-all hover:shadow-md">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mb-3 text-red-600">
                    <i class="fas fa-exclamation-triangle text-xl"></i>
                </div>
                <div class="text-2xl font-bold text-red-700">{{ $equipmentOutOfService }}</div>
                <div class="text-sm text-gray-600 text-center mt-1">{{ __('messages.equipment_out_of_service') }}</div>
            </div>

            <div class="flex flex-col items-center p-4 bg-blue-50 rounded-lg border border-blue-100 transition-all hover:shadow-md">
                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center mb-3 text-blue-600">
                    <i class="fas fa-wrench text-xl"></i>
                </div>
                <div class="text-2xl font-bold text-blue-700">{{ $equipmentCount }}</div>
                <div class="text-sm text-gray-600 text-center mt-1">{{ __('messages.total_equipment') }}</div>
            </div>
        </div>
    </div>

    <!-- Widget Planned Dates removido conforme solicitado -->

    <!-- Task Status by Department and Category -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6 dashboard-card">
            <h3 class="text-lg font-semibold mb-4 flex items-center">
                <i class="fas fa-project-diagram text-purple-500 mr-2"></i> {{ __('messages.area_wise_task_status') }}
            </h3>
            <div class="h-80 chart-container">
                <canvas id="deptTaskChart" width="400" height="300"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 dashboard-card">
            <h3 class="text-lg font-semibold mb-4 flex items-center">
                <i class="fas fa-chart-pie text-green-500 mr-2"></i> {{ __('messages.area_wise_compliance') }}
            </h3>
            <div class="h-80 chart-container">
                <canvas id="deptComplianceChart" width="400" height="300"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6 dashboard-card">
            <h3 class="text-lg font-semibold mb-4 flex items-center">
                <i class="fas fa-layer-group text-blue-500 mr-2"></i> {{ __('messages.category_wise_task_status') }}
            </h3>
            <div class="h-80 chart-container">
                <canvas id="categoryTaskChart" width="400" height="300"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 dashboard-card">
            <h3 class="text-lg font-semibold mb-4 flex items-center">
                <i class="fas fa-chart-line text-green-500 mr-2"></i> {{ __('messages.category_wise_compliance') }}
            </h3>
            <div class="h-80 chart-container">
                <canvas id="categoryComplianceChart" width="400" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Original Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Monthly Distribution Chart -->
        <div class="bg-white rounded-lg shadow-sm p-6 dashboard-card">
            <h3 class="text-lg font-semibold mb-4 flex items-center">
                <i class="fas fa-calendar-alt text-blue-500 mr-2"></i> {{ __('messages.monthly_maintenance_distribution') }}
            </h3>
            <div class="h-80 chart-container">
                <canvas id="monthlyChart" width="400" height="300"></canvas>
            </div>
        </div>

        <!-- Status Distribution Chart -->
        <div class="bg-white rounded-lg shadow-sm p-6 dashboard-card">
            <h3 class="text-lg font-semibold mb-4 flex items-center">
                <i class="fas fa-clipboard-list text-indigo-500 mr-2"></i> {{ __('messages.maintenance_plan_status') }}
            </h3>
            <div class="h-80 chart-container">
                <canvas id="statusChart" width="400" height="300"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Planned Maintenance Chart -->
        <div class="bg-white rounded-lg shadow-sm p-6 dashboard-card">
            <h3 class="text-lg font-semibold mb-4 flex items-center">
                <i class="fas fa-clipboard-list text-green-500 mr-2"></i> Planned Maintenance Types
            </h3>
            <div class="h-80 chart-container">
                <canvas id="planningChart" width="400" height="300"></canvas>
            </div>
        </div>

        <!-- Corrective Maintenance Chart -->
        <div class="bg-white rounded-lg shadow-sm p-6 dashboard-card">
            <h3 class="text-lg font-semibold mb-4 flex items-center">
                <i class="fas fa-tools text-red-500 mr-2"></i> Corrective Maintenance by Cause
            </h3>
            <div class="h-80 chart-container">
                <canvas id="correctiveChart" width="400" height="300"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Task Description Status -->
        <div class="bg-white rounded-lg shadow-sm p-6 dashboard-card">
            <h3 class="text-lg font-semibold mb-4 flex items-center">
                <i class="fas fa-tasks text-purple-500 mr-2"></i> {{ __('messages.task_description_wise_status') }}
            </h3>
            <div class="h-80 chart-container">
                <canvas id="taskDescriptionChart" width="400" height="300"></canvas>
            </div>
        </div>

        <!-- Maintenance Alerts -->
        <div class="bg-white rounded-lg shadow-sm p-6 dashboard-card">
            <h3 class="text-lg font-medium mb-4 flex items-center">
                <i class="fas fa-bell mr-2 text-yellow-500"></i> Maintenance Alerts
            </h3>

            @forelse($maintenanceAlerts as $alert)
                <div class="p-3 mb-3 rounded-lg {{ $alert['status'] === 'overdue' ? 'bg-red-50 border border-red-100' : 'bg-blue-50 border border-blue-100' }} flex justify-between items-center alert-item">
                    <div class="flex-1">
                        <div class="font-semibold text-gray-800">{{ $alert['title'] }}</div>
                        <div class="text-sm text-gray-600 flex items-center mt-1">
                            <i class="fas fa-cog mr-1"></i> {{ $alert['equipment'] }}
                        </div>
                        <div class="flex items-center text-xs space-x-2 mt-1">
                            <span class="flex items-center text-gray-600">
                                <i class="far fa-calendar-alt mr-1"></i> {{ $alert['date'] }}
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $alert['status'] === 'overdue' ? 'bg-red-200 text-red-800' : 'bg-blue-200 text-blue-800' }}">
                                {{ $alert['days_until'] }}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center">
                        @if($alert['status'] === 'overdue')
                            <span class="px-2 py-1 rounded bg-red-200 text-red-800 text-xs font-semibold mr-3">Overdue</span>
                        @else
                            <span class="px-2 py-1 rounded bg-blue-200 text-blue-800 text-xs font-semibold mr-3">Upcoming</span>
                        @endif
                        <button wire:click="markAlertAsCompleted({{ $alert['id'] }})" class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 text-green-600 hover:bg-green-200 transition-colors">
                            <i class="fas fa-check"></i>
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-6 bg-gray-50 rounded-lg border border-gray-100">
                    <div class="text-gray-500 flex flex-col items-center">
                        <i class="fas fa-check-circle text-3xl text-green-500 mb-2"></i>
                        <p>No maintenance alerts found</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- New Maintenance Plan Status Section -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6 dashboard-card">
        <h3 class="text-lg font-semibold mb-4">Equipment Maintenance Status</h3>
        <div class="h-96 chart-container">
            <canvas id="planStatusChart" width="400" height="350"></canvas>
        </div>
    </div>

    <!-- Add some custom CSS for dashboard styling -->
    <style>
        .chart-container {
            position: relative;
            height: 80%;
            width: 100%;
            transition: all 0.2s ease-in-out;
        }
        
        .chart-container:hover {
            transform: translateY(-2px);
        }
        
        .dashboard-card {
            transition: all 0.2s ease;
            border: 1px solid #f3f4f6;
        }
        
        .dashboard-card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .alert-item {
            transition: all 0.2s;
        }
        
        .alert-item:hover {
            transform: translateX(2px);
        }
        
        @media (max-width: 640px) {
            .flex.flex-wrap.gap-4 {
                gap: 0.5rem;
            }
        }
    </style>

    <script>
        // Armazenar os dados para os gráficos em variáveis JavaScript
        var monthlyData = @json($monthlyTasksData);
        var statusData = @json($statusDistributionData);
        var planningData = @json($planningChartData);
        var correctiveData = @json($correctiveChartData);

        // Dados para os novos gráficos
        var departmentTaskData = @json($departmentTaskData);
        var departmentComplianceData = @json($departmentComplianceData);
        var categoryTaskData = @json($categoryTaskData);
        var categoryComplianceData = @json($categoryComplianceData);
        var taskDescriptionData = @json($taskDescriptionData);
        var maintenancePlanStatusData = @json($maintenancePlanStatusData);

        // Global chart instances
        let monthlyChart = null;
        let statusChart = null;
        let planningChart = null;
        let correctiveChart = null;
        let deptTaskChart = null;
        let deptComplianceChart = null;
        let categoryTaskChart = null;
        let categoryComplianceChart = null;
        let taskDescriptionChart = null;
        let planStatusChart = null;

        // Main initialization function
        function initializeAllCharts() {
            console.log('Initializing all charts');

            try {
                destroyCharts(); // Destroy existing charts first

                // Small delay to ensure DOM is ready
                setTimeout(function() {
                    // Initialize each chart individually with its own timeout
                    setTimeout(function() { initMonthlyChart(); }, 100);
                    setTimeout(function() { initStatusChart(); }, 200);
                    setTimeout(function() { initPlanningChart(); }, 300);
                    setTimeout(function() { initCorrectiveChart(); }, 400);

                    // Initialize new charts
                    setTimeout(function() { initDeptTaskChart(); }, 500);
                    setTimeout(function() { initDeptComplianceChart(); }, 600);
                    setTimeout(function() { initCategoryTaskChart(); }, 700);
                    setTimeout(function() { initCategoryComplianceChart(); }, 800);
                    setTimeout(function() { initTaskDescriptionChart(); }, 900);
                    setTimeout(function() { initPlanStatusChart(); }, 1000);
                }, 300);
            } catch (error) {
                console.error('Error initializing charts:', error);
            }
        }

        // Clean up previous chart instances
        function destroyCharts() {
            if (monthlyChart) {
                monthlyChart.destroy();
                monthlyChart = null;
            }
            if (statusChart) {
                statusChart.destroy();
                statusChart = null;
            }
            if (planningChart) {
                planningChart.destroy();
                planningChart = null;
            }
            if (correctiveChart) {
                correctiveChart.destroy();
                correctiveChart = null;
            }
            if (deptTaskChart) {
                deptTaskChart.destroy();
                deptTaskChart = null;
            }
            if (deptComplianceChart) {
                deptComplianceChart.destroy();
                deptComplianceChart = null;
            }
            if (categoryTaskChart) {
                categoryTaskChart.destroy();
                categoryTaskChart = null;
            }
            if (categoryComplianceChart) {
                categoryComplianceChart.destroy();
                categoryComplianceChart = null;
            }
            if (taskDescriptionChart) {
                taskDescriptionChart.destroy();
                taskDescriptionChart = null;
            }
            if (planStatusChart) {
                planStatusChart.destroy();
                planStatusChart = null;
            }
        }

        // Funções para os gráficos originais...
        function initMonthlyChart() {
            try {
                const canvas = document.getElementById('monthlyChart');

                if (!canvas) {
                    console.warn('Canvas for monthly chart not found');
                    return;
                }

                const ctx = canvas.getContext('2d');

                if (!ctx) {
                    console.warn('Could not get 2D context for monthly chart');
                    return;
                }

                if (!monthlyData || !monthlyData.datasets || !monthlyData.labels) {
                    console.warn('Invalid data for monthly chart:', monthlyData);
                    return;
                }

                monthlyChart = new Chart(ctx, {
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
                                    text: 'Number of Tasks'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Month'
                                }
                            }
                        }
                    }
                });
                console.log('Monthly chart initialized successfully');
            } catch (error) {
                console.error('Error initializing monthly chart:', error);
            }
        }

        function initStatusChart() {
            try {
                const canvas = document.getElementById('statusChart');

                if (!canvas) {
                    console.warn('Canvas for status chart not found');
                    return;
                }

                const ctx = canvas.getContext('2d');

                if (!ctx) {
                    console.warn('Could not get 2D context for status chart');
                    return;
                }

                if (!statusData || !statusData.datasets || !statusData.labels) {
                    console.warn('Invalid data for status chart:', statusData);
                    return;
                }

                statusChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: statusData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            title: {
                                display: true,
                                text: 'Maintenance Status Distribution'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
                console.log('Status chart initialized successfully');
            } catch (error) {
                console.error('Error initializing status chart:', error);
            }
        }

        function initPlanningChart() {
            try {
                const canvas = document.getElementById('planningChart');

                if (!canvas) {
                    console.warn('Canvas for planning chart not found');
                    return;
                }

                const ctx = canvas.getContext('2d');

                if (!ctx) {
                    console.warn('Could not get 2D context for planning chart');
                    return;
                }

                if (!planningData || !planningData.datasets || !planningData.labels) {
                    console.warn('Invalid data for planning chart:', planningData);
                    return;
                }

                // Get the chart title if it exists, or use default
                const chartTitle = planningData.chartTitle || 'Planned Maintenance Types';

                planningChart = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: planningData.labels,
                        datasets: planningData.datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: chartTitle
                            },
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
                console.log('Planning chart initialized successfully');
            } catch (error) {
                console.error('Error initializing planning chart:', error);
            }
        }

        function initCorrectiveChart() {
            try {
                const canvas = document.getElementById('correctiveChart');

                if (!canvas) {
                    console.warn('Canvas for corrective chart not found');
                    return;
                }

                const ctx = canvas.getContext('2d');

                if (!ctx) {
                    console.warn('Could not get 2D context for corrective chart');
                    return;
                }

                if (!correctiveData || !correctiveData.datasets || !correctiveData.labels) {
                    console.warn('Invalid data for corrective chart:', correctiveData);
                    return;
                }

                correctiveChart = new Chart(ctx, {
                    type: 'bar',
                    data: correctiveData,
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: 'Corrective Maintenance by Cause'
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Number of Occurrences'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Failure Cause'
                                }
                            }
                        }
                    }
                });
                console.log('Corrective chart initialized successfully');
            } catch (error) {
                console.error('Error initializing corrective chart:', error);
            }
        }

        // Funções para os novos gráficos
        function initDeptTaskChart() {
            try {
                const canvas = document.getElementById('deptTaskChart');

                if (!canvas) {
                    console.warn('Canvas para gráfico de tarefas por departamento não encontrado');
                    return;
                }

                const ctx = canvas.getContext('2d');

                if (!ctx) {
                    console.warn('Não foi possível obter contexto 2D para o gráfico de tarefas por departamento');
                    return;
                }

                // Verificar se temos dados ou criar dados padrão
                let chartData = departmentTaskData;
                if (!chartData || !chartData.datasets || !chartData.labels) {
                    console.warn('Dados inválidos para o gráfico de tarefas por departamento. Usando dados padrão.');
                    chartData = {
                        labels: ['Engenharia', 'Produção', 'Qualidade', 'Manutenção'],
                        datasets: [
                            {
                                label: 'Planejado',
                                data: [0.8, 0.9, 0.5, 0.7],
                                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Atual',
                                data: [0.6, 0.8, 0.4, 0.5],
                                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            }
                        ]
                    };
                }

                deptTaskChart = new Chart(ctx, {
                    type: 'bar',
                    data: chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Area Wise Task Status'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Count'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Area'
                                }
                            }
                        }
                    }
                });
                console.log('Area task chart initialized successfully');
            } catch (error) {
                console.error('Erro ao inicializar gráfico de tarefas por departamento:', error);
            }
        }

        function initDeptComplianceChart() {
            try {
                const canvas = document.getElementById('deptComplianceChart');

                if (!canvas) {
                    console.warn('Canvas para gráfico de compliance por departamento não encontrado');
                    return;
                }

                const ctx = canvas.getContext('2d');

                if (!ctx) {
                    console.warn('Não foi possível obter contexto 2D para o gráfico de compliance por departamento');
                    return;
                }

                // Verificar se temos dados ou criar dados padrão
                let chartData = departmentComplianceData;
                if (!chartData || !chartData.datasets || !chartData.labels) {
                    console.warn('Dados inválidos para o gráfico de compliance por departamento. Usando dados padrão.');
                    chartData = {
                        labels: ['Engineering', 'Production', 'Quality', 'Maintenance'],
                        datasets: [
                            {
                                label: 'Compliance %',
                                data: [75, 85, 60, 90],
                                fill: true,
                                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                                pointBorderColor: '#fff',
                                pointHoverBackgroundColor: '#fff',
                                pointHoverBorderColor: 'rgba(54, 162, 235, 1)',
                                tension: 0.1
                            }
                        ]
                    };
                }

                deptComplianceChart = new Chart(ctx, {
                    type: 'line',
                    data: chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Area Wise Task Compliance %'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                suggestedMax: 100,
                                title: {
                                    display: true,
                                    text: 'Compliance %'
                                }
                            }
                        }
                    }
                });
                console.log('Area compliance chart initialized successfully');
            } catch (error) {
                console.error('Error initializing area compliance chart:', error);
            }
        }

        function initCategoryTaskChart() {
            try {
                const canvas = document.getElementById('categoryTaskChart');

                if (!canvas) {
                    console.warn('Canvas para gráfico de tarefas por categoria não encontrado');
                    return;
                }

                const ctx = canvas.getContext('2d');

                if (!ctx) {
                    console.warn('Não foi possível obter contexto 2D para o gráfico de tarefas por categoria');
                    return;
                }

                // Verificar se temos dados ou criar dados padrão
                let chartData = categoryTaskData;
                if (!chartData || !chartData.datasets || !chartData.labels) {
                    console.warn('Dados inválidos para o gráfico de tarefas por categoria. Usando dados padrão.');
                    chartData = {
                        labels: ['Line 1', 'Line 2', 'Line 3', 'Line 4'],
                        datasets: [
                            {
                                label: 'Planned',
                                data: [0.9, 0.5, 0.7, 0.8],
                                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Actual',
                                data: [0.8, 0.4, 0.6, 0.7],
                                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            }
                        ]
                    };
                }

                categoryTaskChart = new Chart(ctx, {
                    type: 'line',
                    data: chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Lines Wise Task Status'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Count'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Line'
                                }
                            }
                        }
                    }
                });
                console.log('Lines task chart initialized successfully');
            } catch (error) {
                console.error('Error initializing lines task chart:', error);
            }
        }

        function initCategoryComplianceChart() {
            try {
                const canvas = document.getElementById('categoryComplianceChart');

                if (!canvas) {
                    console.warn('Canvas para gráfico de compliance por categoria não encontrado');
                    return;
                }

                const ctx = canvas.getContext('2d');

                if (!ctx) {
                    console.warn('Não foi possível obter contexto 2D para o gráfico de compliance por categoria');
                    return;
                }

                // Verificar se temos dados ou criar dados padrão
                let chartData = categoryComplianceData;
                if (!chartData || !chartData.datasets || !chartData.labels) {
                    console.warn('Dados inválidos para o gráfico de compliance por categoria. Usando dados padrão.');
                    chartData = {
                        labels: ['Line 1', 'Line 2', 'Line 3', 'Line 4'],
                        datasets: [
                            {
                                label: 'Compliance %',
                                data: [85, 75, 90, 70],
                                fill: true,
                                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                                pointBorderColor: '#fff',
                                pointHoverBackgroundColor: '#fff',
                                pointHoverBorderColor: 'rgba(54, 162, 235, 1)',
                                tension: 0.1
                            }
                        ]
                    };
                }

                categoryComplianceChart = new Chart(ctx, {
                    type: 'line',
                    data: chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Lines Wise Task Compliance %'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                suggestedMax: 100,
                                title: {
                                    display: true,
                                    text: 'Compliance %'
                                }
                            }
                        }
                    }
                });
                console.log('Lines compliance chart initialized successfully');
            } catch (error) {
                console.error('Error initializing lines compliance chart:', error);
            }
        }

        function initTaskDescriptionChart() {
            try {
                const canvas = document.getElementById('taskDescriptionChart');

                if (!canvas) {
                    console.warn('Canvas for task description chart not found');
                    return;
                }

                const ctx = canvas.getContext('2d');

                if (!ctx) {
                    console.warn('Could not get 2D context for task description chart');
                    return;
                }

                // Check if we have data or create default data
                let chartData = taskDescriptionData;
                if (!chartData || !chartData.datasets || !chartData.labels) {
                    console.warn('Invalid data for task description chart. Using default data.');
                    chartData = {
                        labels: ['Lubrication', 'Electrical Inspection', 'Calibration', 'Cleaning'],
                        datasets: [
                            {
                                label: 'Planned',
                                data: [30, 45, 25, 40],
                                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Completed',
                                data: [25, 40, 20, 35],
                                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Pending',
                                data: [5, 5, 5, 5],
                                backgroundColor: 'rgba(255, 206, 86, 0.5)',
                                borderColor: 'rgba(255, 206, 86, 1)',
                                borderWidth: 1
                            }
                        ]
                    };
                }

                taskDescriptionChart = new Chart(ctx, {
                    type: 'bar',
                    data: chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Task Description Status'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Count'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Description'
                                }
                            }
                        }
                    }
                });
                console.log('Task description chart initialized successfully');
            } catch (error) {
                console.error('Error initializing task description chart:', error);
            }
        }

        function initPlanStatusChart() {
            try {
                const canvas = document.getElementById('planStatusChart');

                if (!canvas) {
                    console.warn('Canvas for maintenance plan status chart not found');
                    return;
                }

                const ctx = canvas.getContext('2d');

                if (!ctx) {
                    console.warn('Could not get 2D context for maintenance plan status chart');
                    return;
                }

                // Check if we have data or create default data
                let chartData = maintenancePlanStatusData;
                if (!chartData || !chartData.datasets || !chartData.labels || chartData.labels.length === 0) {
                    console.warn('Invalid data for maintenance plan status chart. Using default data.');
                    chartData = {
                        labels: ['Equipment 1', 'Equipment 2', 'Equipment 3', 'Equipment 4'],
                        datasets: [
                            {
                                label: 'Total Plans',
                                data: [25, 18, 15, 12],
                                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1,
                                type: 'bar'
                            },
                            {
                                label: 'In Progress',
                                data: [10, 5, 3, 2],
                                backgroundColor: 'rgba(255, 206, 86, 0.5)',
                                borderColor: 'rgba(255, 206, 86, 1)',
                                borderWidth: 1,
                                type: 'bar'
                            },
                            {
                                label: 'Completed',
                                data: [12, 10, 8, 6],
                                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1,
                                type: 'bar'
                            },
                            {
                                label: 'Cancelled',
                                data: [3, 3, 4, 4],
                                backgroundColor: 'rgba(255, 99, 132, 0.5)',
                                borderColor: 'rgba(255, 99, 132, 1)',
                                borderWidth: 1,
                                type: 'bar'
                            }
                        ]
                    };
                }

                planStatusChart = new Chart(ctx, {
                    type: 'bar',
                    data: chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Equipment Maintenance Status'
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false
                            },
                            legend: {
                                position: 'top',
                            }
                        },
                        scales: {
                            x: {
                                stacked: false,
                                title: {
                                    display: true,
                                    text: 'Equipment'
                                }
                            },
                            y: {
                                stacked: false,
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Count'
                                }
                            }
                        }
                    }
                });
                console.log('Maintenance plan status chart initialized successfully');
            } catch (error) {
                console.error('Error initializing maintenance plan status chart:', error);
            }
        }

        // Event listeners for chart initialization
        // Execute initialization only when document is fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOMContentLoaded - Waiting 1 second to initialize charts...');
            setTimeout(initializeAllCharts, 1000);
        });

        // Livewire event listeners
        document.addEventListener('livewire:load', function() {
            console.log('Livewire loaded');
            setTimeout(initializeAllCharts, 1000);
        });

        document.addEventListener('livewire:initialized', function() {
            console.log('Livewire initialized');
            setTimeout(initializeAllCharts, 1000);
        });

        // Custom event for dashboard data loading
        document.addEventListener('dashboardDataLoaded', function() {
            console.log('Dashboard data loaded - initializing charts');
            setTimeout(initializeAllCharts, 800);
        });

        document.addEventListener('livewire:navigated', function() {
            console.log('Livewire navigated - reinitializing charts');
            setTimeout(initializeAllCharts, 800);
        });

        // Window resize handler with debounce to prevent multiple initializations
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                console.log('Window resized - reinitializing charts');
                initializeAllCharts();
            }, 500);
        });

        // Final fallback with reduced timeout for quicker initialization
        setTimeout(function() {
            console.log('Final initialization check');
            if (!monthlyChart && !statusChart && !planningChart && !correctiveChart) {
                initializeAllCharts();
            }
        }, 2000);
    </script>

    <script>
        // Aplicar classe dashboard-card a todos os cards do dashboard
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.bg-white.rounded-lg.shadow-sm');
            cards.forEach(card => {
                card.classList.add('dashboard-card');
            });
            
            const chartContainers = document.querySelectorAll('.h-80');
            chartContainers.forEach(container => {
                container.classList.add('chart-container');
            });
            
            const alertItems = document.querySelectorAll('.p-3.mb-3.rounded-lg');
            alertItems.forEach(item => {
                item.classList.add('alert-item');
            });
        });
    </script>
</div>
