<div class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center space-x-4">
                    <div class="p-3 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ __('dashboard.hr_dashboard') }}</h1>
                        <p class="text-sm text-gray-600">{{ __('dashboard.hr_dashboard_subtitle') }}</p>
                    </div>
                </div>
                
                <!-- Controls -->
                <div class="flex items-center space-x-4">
                    <!-- Period Selector -->
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">{{ __('dashboard.period') }}:</label>
                        <select wire:model.live="selectedPeriod" 
                                wire:change="$refresh"
                                x-data="{ selectedValue: @entangle('selectedPeriod') }"
                                x-on:change="selectedValue = $event.target.value"
                                class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="current_month" {{ $selectedPeriod === 'current_month' ? 'selected' : '' }}>{{ __('dashboard.current_month') }}</option>
                            <option value="last_month" {{ $selectedPeriod === 'last_month' ? 'selected' : '' }}>{{ __('dashboard.last_month') }}</option>
                            <option value="current_quarter" {{ $selectedPeriod === 'current_quarter' ? 'selected' : '' }}>{{ __('dashboard.current_quarter') }}</option>
                            <option value="current_year" {{ $selectedPeriod === 'current_year' ? 'selected' : '' }}>{{ __('dashboard.current_year') }}</option>
                        </select>
                    </div>
                    
                    <!-- Department Filter -->
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">{{ __('dashboard.department') }}:</label>
                        <select wire:model.live="selectedDepartment" 
                                class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">{{ __('dashboard.all_departments') }}</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Employees -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('dashboard.total_employees') }}</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($totalEmployees) }}</p>
                        <div class="flex items-center mt-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                {{ $employeeGrowth >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    @if($employeeGrowth >= 0)
                                        <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    @else
                                        <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    @endif
                                </svg>
                                {{ abs($employeeGrowth) }}%
                            </span>
                        </div>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Attendance Rate -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('dashboard.attendance_rate') }}</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $attendanceRate }}{{ __('dashboard.percentage') }}</p>
                        <div class="flex items-center mt-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                {{ $attendanceGrowth >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    @if($attendanceGrowth >= 0)
                                        <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    @else
                                        <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    @endif
                                </svg>
                                {{ abs($attendanceGrowth) }}p.p.
                            </span>
                        </div>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Overtime Hours -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('dashboard.average_overtime') }}</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $avgOvertimeHours }}{{ __('dashboard.hours') }}</p>
                        <div class="flex items-center mt-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                {{ $overtimeGrowth >= 0 ? 'bg-orange-100 text-orange-800' : 'bg-green-100 text-green-800' }}">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    @if($overtimeGrowth >= 0)
                                        <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    @else
                                        <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    @endif
                                </svg>
                                {{ abs($overtimeGrowth) }}%
                            </span>
                        </div>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-full">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Salary Advances -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div class="ml-3">
                        <p class="text-sm text-gray-600">{{ __('dashboard.salary_advances') }}</p>
                        <p class="text-xl font-semibold text-gray-900">{{ number_format($totalSalaryAdvances, 0, ',', '.') }}</p>
                    </div>
                    <div class="flex items-center mt-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                {{ $advancesGrowth >= 0 ? 'bg-orange-100 text-orange-800' : 'bg-green-100 text-green-800' }}">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    @if($advancesGrowth >= 0)
                                        <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    @else
                                        <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    @endif
                                </svg>
                                {{ abs($advancesGrowth) }}%
                            </span>
                        </div>
                    </div>
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Department Distribution -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('dashboard.department_distribution') }}</h3>
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="h-64" wire:ignore>
                    <canvas id="departmentChart"></canvas>
                </div>
            </div>

            <!-- Attendance Trends -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('dashboard.attendance_trends') }}</h3>
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Additional Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Overtime Trends -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('dashboard.overtime_hours') }}</h3>
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="h-48">
                    <canvas id="overtimeChart"></canvas>
                </div>
            </div>

            <!-- Leaves Status -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('dashboard.leaves_status') }}</h3>
                    <div class="p-2 bg-orange-100 rounded-lg">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="h-48" wire:ignore>
                    <canvas id="leavesChart"></canvas>
                </div>
            </div>

            <!-- Salary Trends -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('dashboard.salary_trends') }}</h3>
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="h-48">
                    <canvas id="salaryTrendsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Additional Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Overtime by Department -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('dashboard.overtime_by_department') }}</h3>
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="overtimeByDepartmentChart"></canvas>
                </div>
            </div>

            <!-- Advances vs Discounts -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('dashboard.advances_vs_discounts') }}</h3>
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="advancesVsDiscountsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Full-width Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Monthly Payroll -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('dashboard.monthly_payroll') }}</h3>
                    <div class="p-2 bg-emerald-100 rounded-lg">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="monthlyPayrollChart"></canvas>
                </div>
            </div>

            <!-- Payroll Timeline -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('dashboard.payroll_timeline') }}</h3>
                    <div class="p-2 bg-indigo-100 rounded-lg">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="payrollTimelineChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Initialization Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let chartInstances = {};
        
        function initializeCharts() {
            // Destroy existing charts
            Object.values(chartInstances).forEach(chart => {
                if (chart) chart.destroy();
            });
            chartInstances = {};
            
            // Department Chart
            const deptCtx = document.getElementById('departmentChart');
            if (deptCtx) {
                chartInstances.department = new Chart(deptCtx, {
                    type: 'doughnut',
                    data: {
                        labels: @json($departmentData['labels'] ?? []),
                        datasets: [{
                            data: @json($departmentData['data'] ?? []),
                            backgroundColor: @json($departmentData['backgroundColor'] ?? []),
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true
                                }
                            }
                        }
                    }
                });
            }

            // Attendance Chart
            const attendanceCtx = document.getElementById('attendanceChart');
            if (attendanceCtx) {
                chartInstances.attendance = new Chart(attendanceCtx, {
                    type: 'line',
                    data: {
                        labels: @json($attendanceChartData['labels'] ?? []),
                        datasets: @json($attendanceChartData['datasets'] ?? [])
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
            }

            // Overtime Chart
            const overtimeCtx = document.getElementById('overtimeChart');
            if (overtimeCtx) {
                chartInstances.overtime = new Chart(overtimeCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($overtimeChartData['labels'] ?? []),
                        datasets: [{
                            label: 'Horas Extra',
                            data: @json($overtimeChartData['data'] ?? []),
                            backgroundColor: '@json($overtimeChartData['backgroundColor'] ?? '#8B5CF6')',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }

            // Leaves Chart
            const leavesCtx = document.getElementById('leavesChart');
            if (leavesCtx) {
                chartInstances.leaves = new Chart(leavesCtx, {
                    type: 'pie',
                    data: {
                        labels: @json($leavesChartData['labels'] ?? []),
                        datasets: [{
                            data: @json($leavesChartData['data'] ?? []),
                            backgroundColor: @json($leavesChartData['backgroundColor'] ?? [])
                        }]
                    },
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
            }

            // Salary Trends Chart
            const salaryCtx = document.getElementById('salaryTrendsChart');
            if (salaryCtx) {
                chartInstances.salary = new Chart(salaryCtx, {
                    type: 'line',
                    data: {
                        labels: @json($salaryTrendsData['labels'] ?? []),
                        datasets: @json($salaryTrendsData['datasets'] ?? [])
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
            }

            // Overtime by Department Chart
            const overtimeDeptCtx = document.getElementById('overtimeByDepartmentChart');
            if (overtimeDeptCtx) {
                chartInstances.overtimeDept = new Chart(overtimeDeptCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($overtimeByDepartmentData['labels'] ?? []),
                        datasets: [{
                            data: @json($overtimeByDepartmentData['data'] ?? []),
                            backgroundColor: @json($overtimeByDepartmentData['backgroundColor'] ?? [])
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // Monthly Payroll Chart
            const payrollCtx = document.getElementById('monthlyPayrollChart');
            if (payrollCtx) {
                chartInstances.payroll = new Chart(payrollCtx, {
                    type: 'line',
                    data: {
                        labels: @json($monthlyPayrollData['labels'] ?? []),
                        datasets: [{
                            label: '{{ __('dashboard.payroll') }}',
                            data: @json($monthlyPayrollData['data'] ?? []),
                            backgroundColor: @json($monthlyPayrollData['backgroundColor'] ?? '#10B981'),
                            borderColor: @json($monthlyPayrollData['borderColor'] ?? '#10B981'),
                            fill: false
                        }]
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
            }

            // Advances vs Discounts Chart
            const advDiscCtx = document.getElementById('advancesVsDiscountsChart');
            if (advDiscCtx) {
                chartInstances.advancesDiscounts = new Chart(advDiscCtx, {
                    type: 'doughnut',
                    data: {
                        labels: @json($advancesVsDiscountsData['labels'] ?? []),
                        datasets: [{
                            data: @json($advancesVsDiscountsData['data'] ?? []),
                            backgroundColor: @json($advancesVsDiscountsData['backgroundColor'] ?? [])
                        }]
                    },
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
            }

            // Payroll Timeline Chart
            const timelineCtx = document.getElementById('payrollTimelineChart');
            if (timelineCtx) {
                chartInstances.timeline = new Chart(timelineCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($payrollTimelineData['labels'] ?? []),
                        datasets: [{
                            label: '{{ __('dashboard.estimated_payroll') }}',
                            data: @json($payrollTimelineData['data'] ?? []),
                            backgroundColor: @json($payrollTimelineData['backgroundColor'] ?? '#8B5CF6'),
                            borderColor: @json($payrollTimelineData['borderColor'] ?? '#8B5CF6')
                        }]
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
            }
        }
        
        // Initialize charts on load
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initializeCharts, 500);
        });
        
        // Listen for custom chart update event
        window.addEventListener('charts-update', function() {
            setTimeout(initializeCharts, 200);
        });
        
        // Listen for Livewire updates (when period or department changes)
        document.addEventListener('livewire:morph-updated', function() {
            setTimeout(initializeCharts, 300);
        });
        
        // Listen for Livewire component updates
        Livewire.on('periodsUpdated', () => {
            setTimeout(initializeCharts, 200);
        });
        
        // Force charts update on any Livewire update
        document.addEventListener('livewire:updated', function() {
            console.log('Livewire updated - refreshing charts');
            setTimeout(initializeCharts, 100);
        });
        
        // Listen for dynamic chart data refresh
        Livewire.on('refresh-charts', (event) => {
            console.log('Refreshing charts with new data:', event);
            
            // Destroy existing charts
            Object.values(chartInstances).forEach(chart => {
                if (chart) chart.destroy();
            });
            chartInstances = {};
            
            // Recreate charts with new data
            setTimeout(() => {
                initializeChartsWithData(event[0]);
            }, 200);
        });
        
        function initializeChartsWithData(newData) {
            // Update Leave Chart with new data
            const leavesCtx = document.getElementById('leavesChart');
            if (leavesCtx && newData.leavesChartData) {
                chartInstances.leaves = new Chart(leavesCtx, {
                    type: 'pie',
                    data: {
                        labels: newData.leavesChartData.labels || [],
                        datasets: [{
                            data: newData.leavesChartData.data || [],
                            backgroundColor: newData.leavesChartData.backgroundColor || []
                        }]
                    },
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
            }
            
            // Update other charts with fallback to page load data
            setTimeout(initializeCharts, 100);
        }
    </script>
</div>
