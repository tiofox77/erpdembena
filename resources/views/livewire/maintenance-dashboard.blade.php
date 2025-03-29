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
                <label for="filterYear" class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                <select id="filterYear" wire:model.live="filterYear" class="block w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @for ($year = date('Y') - 2; $year <= date('Y') + 1; $year++)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endfor
                </select>
            </div>

            <div>
                <label for="filterMonth" class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                <select id="filterMonth" wire:model.live="filterMonth" class="block w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="all">All</option>
                    <option value="01">January</option>
                    <option value="02">February</option>
                    <option value="03">March</option>
                    <option value="04">April</option>
                    <option value="05">May</option>
                    <option value="06">June</option>
                    <option value="07">July</option>
                    <option value="08">August</option>
                    <option value="09">September</option>
                    <option value="10">October</option>
                    <option value="11">November</option>
                    <option value="12">December</option>
                </select>
            </div>

            <div>
                <label for="filterStatus" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="filterStatus" wire:model.live="filterStatus" class="block w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="all">All</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <div>
                <label for="filterArea" class="block text-sm font-medium text-gray-700 mb-1">Area</label>
                <select id="filterArea" wire:model.live="filterArea" class="block w-40 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="all">All</option>
                    <!-- Area options would be generated dynamically -->
                </select>
            </div>

            <div class="self-end">
                <button type="button" wire:click="loadDashboardData" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Update
                </button>
            </div>
        </div>
    </div>

    <!-- KPI Cards Overview -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="grid grid-cols-3 gap-4 mb-4">
            <div class="bg-blue-100 p-4 rounded-lg text-center">
                <h3 class="text-lg font-semibold mb-2">Planned</h3>
                <p class="text-3xl font-bold">{{ $plannedTasksCount }}</p>
            </div>
            <div class="bg-green-100 p-4 rounded-lg text-center">
                <h3 class="text-lg font-semibold mb-2">Actual</h3>
                <p class="text-3xl font-bold">{{ $actualTasksCount }}</p>
            </div>
            <div class="bg-blue-100 p-4 rounded-lg text-center">
                <h3 class="text-lg font-semibold mb-2">Compliance %</h3>
                <p class="text-3xl font-bold">{{ $compliancePercentage }}%</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="bg-blue-100 p-4 rounded-lg text-center">
                <h3 class="text-lg font-semibold mb-2">Pending</h3>
                <p class="text-3xl font-bold">{{ $pendingTasksCount }}</p>
            </div>
            <div class="bg-red-100 p-4 rounded-lg text-center">
                <h3 class="text-lg font-semibold mb-2">Non-Compliance %</h3>
                <p class="text-3xl font-bold">{{ $nonCompliancePercentage }}%</p>
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

    <!-- Task Status by Department and Category -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-medium mb-4">Area Wise Task Status</h3>
            <div class="h-80">
                <canvas id="deptTaskChart" width="400" height="300"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-medium mb-4">Area Wise Task Compliance %</h3>
            <div class="h-80">
                <canvas id="deptComplianceChart" width="400" height="300"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-medium mb-4">Lines Wise Task Status</h3>
            <div class="h-80">
                <canvas id="categoryTaskChart" width="400" height="300"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-medium mb-4">Lines Wise Task Compliance %</h3>
            <div class="h-80">
                <canvas id="categoryComplianceChart" width="400" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Planned Dates Timeline -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <h3 class="text-lg font-medium mb-4">Planned Dates</h3>
        <div class="overflow-x-auto">
            <div class="flex space-x-2 pb-3">
                @foreach($plannedDates as $date)
                <div class="px-3 py-2 bg-blue-100 text-blue-800 rounded-md text-sm whitespace-nowrap">
                    {{ $date }}
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Original Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Monthly Distribution Chart -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-medium mb-4">Monthly Maintenance Distribution</h3>
            <div class="h-80">
                <canvas id="monthlyChart" width="400" height="300"></canvas>
            </div>
        </div>

        <!-- Status Distribution Chart -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-medium mb-4">Maintenance Status</h3>
            <div class="h-80">
                <canvas id="statusChart" width="400" height="300"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Planned Maintenance Chart -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-medium mb-4">Planned Maintenance Types</h3>
            <div class="h-80">
                <canvas id="planningChart" width="400" height="300"></canvas>
            </div>
        </div>

        <!-- Corrective Maintenance Chart -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-medium mb-4">Corrective Maintenance by Cause</h3>
            <div class="h-80">
                <canvas id="correctiveChart" width="400" height="300"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Task Description Status -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-medium mb-4">Task Description Wise Status</h3>
            <div class="h-80">
                <canvas id="taskDescriptionChart" width="400" height="300"></canvas>
            </div>
        </div>

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
    </div>

    <!-- New Maintenance Plan Status Section -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <h3 class="text-lg font-medium mb-4">Equipment Maintenance Status</h3>
        <div class="h-96">
            <canvas id="planStatusChart" width="400" height="350"></canvas>
        </div>
    </div>

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
</div>
