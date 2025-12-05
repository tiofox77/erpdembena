<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    {{-- Header --}}
    <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-700 shadow-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                {{-- Title --}}
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-white/20 backdrop-blur rounded-xl">
                        <i class="fas fa-chart-line text-2xl text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white">Dashboard RH</h1>
                        <p class="text-blue-100 text-sm">
                            {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                        </p>
                    </div>
                </div>
                
                {{-- Filters --}}
                <div class="flex flex-wrap items-center gap-3">
                    <select wire:model.live="period" class="px-4 py-2 rounded-lg bg-white/20 border-white/30 text-white text-sm focus:ring-white focus:border-white [&>option]:text-gray-800">
                        <option value="current_month">Mês Atual</option>
                        <option value="last_month">Mês Anterior</option>
                        <option value="current_quarter">Trimestre</option>
                        <option value="current_year">Ano</option>
                    </select>
                    
                    <select wire:model.live="departmentId" class="px-4 py-2 rounded-lg bg-white/20 border-white/30 text-white text-sm focus:ring-white focus:border-white [&>option]:text-gray-800">
                        <option value="">Todos Departamentos</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    
                    <select wire:model.live="payrollPeriodId" class="px-4 py-2 rounded-lg bg-white/20 border-white/30 text-white text-sm focus:ring-white focus:border-white [&>option]:text-gray-800">
                        <option value="">Período Salarial</option>
                        @foreach($payrollPeriods as $pp)
                            <option value="{{ $pp->id }}">{{ $pp->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Quick Links --}}
    <div class="bg-white border-b shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-1 py-2 overflow-x-auto">
                <a href="{{ route('hr.employees') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition whitespace-nowrap">
                    <i class="fas fa-users"></i> Funcionários
                </a>
                <a href="{{ route('hr.attendance') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:text-green-600 hover:bg-green-50 rounded-lg transition whitespace-nowrap">
                    <i class="fas fa-fingerprint"></i> Presenças
                </a>
                <a href="{{ route('hr.payroll') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition whitespace-nowrap">
                    <i class="fas fa-money-bill-wave"></i> Folha Salarial
                </a>
                <a href="{{ route('hr.payroll-batch') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition whitespace-nowrap">
                    <i class="fas fa-layer-group"></i> Processamento
                </a>
                <a href="{{ route('hr.leave') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition whitespace-nowrap">
                    <i class="fas fa-calendar-times"></i> Licenças
                </a>
                <a href="{{ route('hr.overtime-records') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition whitespace-nowrap">
                    <i class="fas fa-clock"></i> Horas Extra
                </a>
                <a href="{{ route('hr.salary-advances') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition whitespace-nowrap">
                    <i class="fas fa-hand-holding-usd"></i> Adiantamentos
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{-- KPI Cards Row 1 --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
            {{-- Total Employees --}}
            <div class="bg-white rounded-xl shadow-sm border p-4 hover:shadow-md transition">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-blue-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($kpis['totalEmployees'] ?? 0) }}</p>
                <p class="text-xs text-gray-500">Total Funcionários</p>
            </div>
            
            {{-- Active --}}
            <div class="bg-white rounded-xl shadow-sm border p-4 hover:shadow-md transition">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-check text-green-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($kpis['activeEmployees'] ?? 0) }}</p>
                <p class="text-xs text-gray-500">Activos</p>
            </div>
            
            {{-- Attendance Rate --}}
            <div class="bg-white rounded-xl shadow-sm border p-4 hover:shadow-md transition">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-pie text-emerald-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $kpis['attendanceRate'] ?? 0 }}%</p>
                <p class="text-xs text-gray-500">Taxa Presença</p>
            </div>
            
            {{-- Overtime Hours --}}
            <div class="bg-white rounded-xl shadow-sm border p-4 hover:shadow-md transition">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-purple-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($kpis['totalOvertimeHours'] ?? 0, 1) }}h</p>
                <p class="text-xs text-gray-500">Horas Extra</p>
            </div>
            
            {{-- Pending Leaves --}}
            <div class="bg-white rounded-xl shadow-sm border p-4 hover:shadow-md transition">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-hourglass-half text-orange-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $kpis['pendingLeaves'] ?? 0 }}</p>
                <p class="text-xs text-gray-500">Licenças Pendentes</p>
            </div>
            
            {{-- New Hires --}}
            <div class="bg-white rounded-xl shadow-sm border p-4 hover:shadow-md transition">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-10 h-10 bg-cyan-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-plus text-cyan-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $kpis['newHires'] ?? 0 }}</p>
                <p class="text-xs text-gray-500">Novas Contratações</p>
            </div>
        </div>
        
        {{-- Financial Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            {{-- Total Payroll --}}
            <div class="bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl shadow-lg p-5 text-white">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-money-bill-wave text-xl"></i>
                    </div>
                    <div>
                        <p class="text-emerald-100 text-sm">Folha Salarial</p>
                        <p class="text-xs text-emerald-200">{{ $kpis['payrollCount'] ?? 0 }} registros</p>
                    </div>
                </div>
                <p class="text-3xl font-bold">{{ number_format($kpis['totalPayroll'] ?? 0, 0, ',', '.') }}</p>
                <p class="text-emerald-100 text-sm">AOA</p>
            </div>
            
            {{-- Advances --}}
            <div class="bg-gradient-to-br from-amber-500 to-yellow-600 rounded-xl shadow-lg p-5 text-white">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-hand-holding-usd text-xl"></i>
                    </div>
                    <div>
                        <p class="text-amber-100 text-sm">Adiantamentos</p>
                    </div>
                </div>
                <p class="text-3xl font-bold">{{ number_format($kpis['totalAdvances'] ?? 0, 0, ',', '.') }}</p>
                <p class="text-amber-100 text-sm">AOA</p>
            </div>
            
            {{-- Discounts --}}
            <div class="bg-gradient-to-br from-red-500 to-rose-600 rounded-xl shadow-lg p-5 text-white">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-minus-circle text-xl"></i>
                    </div>
                    <div>
                        <p class="text-red-100 text-sm">Descontos</p>
                    </div>
                </div>
                <p class="text-3xl font-bold">{{ number_format($kpis['totalDiscounts'] ?? 0, 0, ',', '.') }}</p>
                <p class="text-red-100 text-sm">AOA</p>
            </div>
            
            {{-- Overtime Cost --}}
            <div class="bg-gradient-to-br from-purple-500 to-violet-600 rounded-xl shadow-lg p-5 text-white">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-coins text-xl"></i>
                    </div>
                    <div>
                        <p class="text-purple-100 text-sm">Custo Horas Extra</p>
                    </div>
                </div>
                <p class="text-3xl font-bold">{{ number_format($kpis['totalOvertimeCost'] ?? 0, 0, ',', '.') }}</p>
                <p class="text-purple-100 text-sm">AOA</p>
            </div>
        </div>
        
        {{-- Charts Row 1 --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Department Distribution --}}
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-building text-blue-500"></i>
                    Funcionários por Departamento
                </h3>
                <div class="h-64">
                    <canvas id="departmentChart"></canvas>
                </div>
            </div>
            
            {{-- Attendance Summary --}}
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-fingerprint text-green-500"></i>
                    Resumo de Presenças
                </h3>
                <div class="h-64">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>
        </div>
        
        {{-- Charts Row 2 --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            {{-- Leave Status --}}
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-calendar-times text-orange-500"></i>
                    Status das Licenças
                </h3>
                <div class="h-48">
                    <canvas id="leavesChart"></canvas>
                </div>
            </div>
            
            {{-- Overtime by Department --}}
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-clock text-purple-500"></i>
                    Horas Extra por Dept.
                </h3>
                <div class="h-48">
                    <canvas id="overtimeChart"></canvas>
                </div>
            </div>
            
            {{-- Advances vs Discounts --}}
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-balance-scale text-amber-500"></i>
                    Adiant. vs Descontos
                </h3>
                <div class="h-48">
                    <canvas id="advDiscChart"></canvas>
                </div>
            </div>
        </div>
        
        {{-- Payroll Trend --}}
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-chart-line text-emerald-500"></i>
                Evolução da Folha Salarial
            </h3>
            <div class="h-64">
                <canvas id="payrollTrendChart"></canvas>
            </div>
        </div>
    </div>
    
    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let charts = {};
        
        const colors = {
            blue: '#3B82F6',
            green: '#10B981',
            emerald: '#059669',
            purple: '#8B5CF6',
            orange: '#F59E0B',
            red: '#EF4444',
            cyan: '#06B6D4',
            pink: '#EC4899',
            indigo: '#6366F1',
            gray: '#6B7280'
        };
        
        const palette = [colors.blue, colors.green, colors.purple, colors.orange, colors.red, colors.cyan, colors.pink, colors.indigo];
        
        function destroyCharts() {
            Object.values(charts).forEach(c => { if(c) c.destroy(); });
            charts = {};
        }
        
        function initCharts(data) {
            destroyCharts();
            
            // Department Chart
            const deptCtx = document.getElementById('departmentChart');
            if (deptCtx && data.department) {
                charts.dept = new Chart(deptCtx, {
                    type: 'doughnut',
                    data: {
                        labels: data.department.labels,
                        datasets: [{ data: data.department.data, backgroundColor: palette }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'right' } }
                    }
                });
            }
            
            // Attendance Chart
            const attCtx = document.getElementById('attendanceChart');
            if (attCtx && data.attendance) {
                charts.att = new Chart(attCtx, {
                    type: 'doughnut',
                    data: {
                        labels: data.attendance.labels,
                        datasets: [{ data: data.attendance.data, backgroundColor: [colors.green, colors.orange, colors.red] }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'right' } }
                    }
                });
            }
            
            // Leaves Chart
            const lvCtx = document.getElementById('leavesChart');
            if (lvCtx && data.leaves) {
                charts.leaves = new Chart(lvCtx, {
                    type: 'pie',
                    data: {
                        labels: data.leaves.labels,
                        datasets: [{ data: data.leaves.data, backgroundColor: [colors.orange, colors.green, colors.red] }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }
            
            // Overtime by Dept Chart
            const otCtx = document.getElementById('overtimeChart');
            if (otCtx && data.overtimeByDept) {
                charts.ot = new Chart(otCtx, {
                    type: 'bar',
                    data: {
                        labels: data.overtimeByDept.labels,
                        datasets: [{ label: 'Horas', data: data.overtimeByDept.data, backgroundColor: colors.purple, borderRadius: 4 }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }
            
            // Advances vs Discounts
            const adCtx = document.getElementById('advDiscChart');
            if (adCtx && data.advancesDiscounts) {
                charts.ad = new Chart(adCtx, {
                    type: 'doughnut',
                    data: {
                        labels: data.advancesDiscounts.labels,
                        datasets: [{ data: data.advancesDiscounts.data, backgroundColor: [colors.orange, colors.red] }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }
            
            // Payroll Trend
            const ptCtx = document.getElementById('payrollTrendChart');
            if (ptCtx && data.payrollTrend) {
                charts.pt = new Chart(ptCtx, {
                    type: 'line',
                    data: {
                        labels: data.payrollTrend.labels,
                        datasets: [{
                            label: 'Folha Salarial',
                            data: data.payrollTrend.data,
                            borderColor: colors.emerald,
                            backgroundColor: colors.emerald + '33',
                            fill: true,
                            tension: 0.3
                        }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false,
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }
        }
        
        // Initial load
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => initCharts(@json($charts)), 300);
        });
        
        // Livewire update
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('charts-updated', (event) => {
                setTimeout(() => initCharts(event.charts), 200);
            });
        });
    </script>
</div>
