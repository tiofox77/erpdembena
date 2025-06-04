<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex items-center space-x-4">
                            <h2 class="text-xl font-semibold text-gray-800">
                                <i class="fas fa-chart-line mr-2 text-gray-600"></i>
                                HR Reports Dashboard
                            </h2>
                            <x-hr-guide-link />
                        </div>
                        
                        <!-- Date Range Filter -->
                        <div class="flex space-x-4">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">From</label>
                                <input 
                                    type="date" 
                                    id="start_date" 
                                    wire:model.live="filters.start_date"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                >
                            </div>
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700">To</label>
                                <input 
                                    type="date" 
                                    id="end_date" 
                                    wire:model.live="filters.end_date"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                >
                            </div>
                            <div class="flex items-end">
                                <button
                                    wire:click="resetFilters"
                                    class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150"
                                >
                                    <i class="fas fa-redo mr-2"></i>
                                    Reset
                                </button>
                            </div>
                            <div class="flex items-end">
                                <button
                                    wire:click="exportData"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150"
                                >
                                    <i class="fas fa-download mr-2"></i>
                                    Export
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Key Metrics -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                        <!-- Employee Count -->
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                                    <i class="fas fa-users text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-sm font-medium text-gray-600">Total Employees</h3>
                                    <div class="flex items-baseline">
                                        <p class="text-2xl font-semibold text-gray-800">{{ $totalEmployees }}</p>
                                        <span class="ml-2 text-xs font-medium {{ $employeeGrowth >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            <i class="fas fa-{{ $employeeGrowth >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                            {{ abs($employeeGrowth) }}% 
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Attendance Rate -->
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-green-100 text-green-500">
                                    <i class="fas fa-check-circle text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-sm font-medium text-gray-600">Attendance Rate</h3>
                                    <div class="flex items-baseline">
                                        <p class="text-2xl font-semibold text-gray-800">{{ $attendanceRate }}%</p>
                                        <span class="ml-2 text-xs font-medium {{ $attendanceGrowth >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            <i class="fas fa-{{ $attendanceGrowth >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                            {{ abs($attendanceGrowth) }}% 
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Leave Rate -->
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-orange-100 text-orange-500">
                                    <i class="fas fa-calendar-alt text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-sm font-medium text-gray-600">Leave Utilization</h3>
                                    <div class="flex items-baseline">
                                        <p class="text-2xl font-semibold text-gray-800">{{ $leaveRate }}%</p>
                                        <span class="ml-2 text-xs font-medium {{ $leaveGrowth <= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            <i class="fas fa-{{ $leaveGrowth <= 0 ? 'arrow-down' : 'arrow-up' }}"></i>
                                            {{ abs($leaveGrowth) }}% 
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Payroll Total -->
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-purple-100 text-purple-500">
                                    <i class="fas fa-money-bill-wave text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-sm font-medium text-gray-600">Monthly Payroll</h3>
                                    <div class="flex items-baseline">
                                        <p class="text-2xl font-semibold text-gray-800">${{ number_format($totalPayroll, 2) }}</p>
                                        <span class="ml-2 text-xs font-medium {{ $payrollGrowth <= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            <i class="fas fa-{{ $payrollGrowth >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                            {{ abs($payrollGrowth) }}% 
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Charts Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Employee Distribution Chart -->
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-800 mb-4">Distribuição de Funcionários</h3>
                            <div class="h-80">
                                <canvas id="departmentChart"></canvas>
                            </div>
                        </div>
                        
                        <!-- Attendance Chart -->
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-800 mb-4">Tendência de Presença (Últimos 7 dias)</h3>
                            <div class="h-80">
                                <canvas id="attendanceChart"></canvas>
                            </div>
                        </div>
                        
                        <!-- Leave Distribution Chart -->
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-800 mb-4">Licenças por Tipo</h3>
                            <div class="h-80">
                                <canvas id="leaveChart"></canvas>
                            </div>
                        </div>
                        
                        <!-- Payroll Trend Chart -->
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-800 mb-4">Tendência de Folha de Pagamento</h3>
                            <div class="h-80">
                                <canvas id="payrollChart"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Top Employees Table -->
                    <div class="mt-6">
                        <h3 class="text-lg font-medium text-gray-800 mb-4">Top Performing Employees</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Employee
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Department
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Position
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Attendance Rate
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Performance Score
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($topEmployees as $employee)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                @if($employee->photo)
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <img class="h-10 w-10 rounded-full" src="{{ asset('storage/' . $employee->photo) }}" alt="{{ $employee->full_name }}">
                                                </div>
                                                @else
                                                <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-user text-gray-500"></i>
                                                </div>
                                                @endif
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $employee->full_name }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $employee->email }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $employee->department->name ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $employee->position->title ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $employee->attendance_rate }}%</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="relative pt-1">
                                                <div class="overflow-hidden h-2 text-xs flex rounded bg-gray-200">
                                                    <div style="width: {{ $employee->performance_score }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500"></div>
                                                </div>
                                                <div class="text-xs text-gray-500 mt-1">{{ $employee->performance_score }}/100</div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:load', function() {
            // Department Chart
            var deptCtx = document.getElementById('departmentChart').getContext('2d');
            var deptChart = new Chart(deptCtx, {
                type: 'pie',
                data: {
                    labels: @json($departmentChartData['labels']),
                    datasets: [{
                        label: 'Employees by Department',
                        data: @json($departmentChartData['data']),
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(255, 206, 86, 0.8)',
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(153, 102, 255, 0.8)',
                            'rgba(255, 159, 64, 0.8)',
                            'rgba(199, 199, 199, 0.8)',
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        }
                    }
                }
            });

            // Attendance Chart
            var attCtx = document.getElementById('attendanceChart').getContext('2d');
            var attChart = new Chart(attCtx, {
                type: 'line',
                data: {
                    labels: @json($attendanceChartData['labels']),
                    datasets: @json($attendanceChartData['datasets'])
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Leave Chart
            var leaveCtx = document.getElementById('leaveChart').getContext('2d');
            var leaveChart = new Chart(leaveCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($leaveChartData['labels']),
                    datasets: [{
                        label: 'Licenças por Tipo',
                        data: @json($leaveChartData['data']),
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 206, 86, 0.8)',
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(153, 102, 255, 0.8)',
                            'rgba(255, 159, 64, 0.8)',
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        }
                    }
                }
            });

            // Payroll Chart
            var payrollCtx = document.getElementById('payrollChart').getContext('2d');
            var payrollChart = new Chart(payrollCtx, {
                type: 'bar',
                data: {
                    labels: @json($payrollChartData['labels']),
                    datasets: [{
                        label: 'Valor da Folha (R$)',
                        data: @json($payrollChartData['data']),
                        backgroundColor: 'rgba(153, 102, 255, 0.6)',
                        borderColor: 'rgba(153, 102, 255, 1)',
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
                                callback: function(value) {
                                    return 'R$ ' + value.toLocaleString('pt-BR');
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'R$ ' + context.raw.toLocaleString('pt-BR');
                                }
                            }
                        }
                    }
                }
            });

            // Listen for filter changes
            Livewire.on('chartDataUpdated', function(data) {
                // Update all charts with new data
                deptChart.data.labels = data.departmentChartData.labels;
                deptChart.data.datasets[0].data = data.departmentChartData.data;
                deptChart.update();

                attChart.data.labels = data.attendanceChartData.labels;
                attChart.data.datasets = data.attendanceChartData.datasets;
                attChart.update();

                leaveChart.data.labels = data.leaveChartData.labels;
                leaveChart.data.datasets[0].data = data.leaveChartData.data;
                leaveChart.update();

                payrollChart.data.labels = data.payrollChartData.labels;
                payrollChart.data.datasets[0].data = data.payrollChartData.data;
                payrollChart.update();
            });

            // Register event listener for chart refreshes
            window.addEventListener('refreshCharts', function(e) {
                const chartData = e.detail;
                
                // Update department chart
                if (chartData.departmentChartData) {
                    deptChart.data.labels = chartData.departmentChartData.labels;
                    deptChart.data.datasets[0].data = chartData.departmentChartData.data;
                    deptChart.update();
                }
                
                // Update attendance chart
                if (chartData.attendanceChartData) {
                    attChart.data.labels = chartData.attendanceChartData.labels;
                    attChart.data.datasets = chartData.attendanceChartData.datasets;
                    attChart.update();
                }
                
                // Update leave chart
                if (chartData.leaveChartData) {
                    leaveChart.data.labels = chartData.leaveChartData.labels;
                    leaveChart.data.datasets[0].data = chartData.leaveChartData.data;
                    leaveChart.update();
                }
                
                // Update payroll chart
                if (chartData.payrollChartData) {
                    payrollChart.data.labels = chartData.payrollChartData.labels;
                    payrollChart.data.datasets[0].data = chartData.payrollChartData.data;
                    payrollChart.update();
                }
            });
        });
    </script>
    @endpush
</div>
