<div>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Maintenance Types Report</h1>
        <p class="text-gray-600">Analyze the types of maintenance performed by equipment in the selected period.</p>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Date Range Filter -->
            <div>
                <label for="dateRange" class="block text-sm font-medium text-gray-700 mb-1">Period</label>
                <select id="dateRange" wire:model.live="dateRange" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="week">Current Week</option>
                    <option value="month">Current Month</option>
                    <option value="quarter">Current Quarter</option>
                    <option value="year">Current Year</option>
                    <option value="custom">Custom Period</option>
                </select>
            </div>

            <!-- Custom Date Range -->
            @if($dateRange === 'custom')
                <div>
                    <label for="startDate" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" id="startDate" wire:model.live="startDate" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="endDate" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" id="endDate" wire:model.live="endDate" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            @endif

            <!-- Area Filter -->
            <div>
                <label for="area" class="block text-sm font-medium text-gray-700 mb-1">Area</label>
                <select id="area" wire:model.live="selectedArea" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="all">All Areas</option>
                    @foreach($areas as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Line Filter -->
            <div>
                <label for="line" class="block text-sm font-medium text-gray-700 mb-1">Line</label>
                <select id="line" wire:model.live="selectedLine" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="all">All Lines</option>
                    @foreach($lines as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Search -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Equipment</label>
                <div class="relative rounded-md shadow-sm">
                    <input type="text" id="search" wire:model.live.debounce.300ms="searchTerm" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 pl-10" placeholder="Search by name or number...">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <!-- Total Maintenance Actions -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-indigo-100 p-3 mr-4">
                    <i class="fas fa-tools text-indigo-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Total Maintenance Actions</h3>
                    <p class="text-2xl font-bold">{{ $totalAll }}</p>
                </div>
            </div>
        </div>

        <!-- Preventive Maintenance -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-blue-100 p-3 mr-4">
                    <i class="fas fa-calendar-check text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Preventive Maintenance</h3>
                    <p class="text-2xl font-bold">{{ $totalPreventive }} <span class="text-sm font-normal text-gray-500">({{ $preventivePercentage }}%)</span></p>
                </div>
            </div>
        </div>

        <!-- Corrective Maintenance -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-red-100 p-3 mr-4">
                    <i class="fas fa-wrench text-red-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Corrective Maintenance</h3>
                    <p class="text-2xl font-bold">{{ $totalCorrective }} <span class="text-sm font-normal text-gray-500">({{ $correctivePercentage }}%)</span></p>
                </div>
            </div>
        </div>

        <!-- Predictive Maintenance -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-green-100 p-3 mr-4">
                    <i class="fas fa-chart-line text-green-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Predictive Maintenance</h3>
                    <p class="text-2xl font-bold">{{ $totalPredictive }} <span class="text-sm font-normal text-gray-500">({{ $predictivePercentage }}%)</span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Maintenance Type Distribution Chart -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <h3 class="text-md font-medium text-gray-700 mb-4">Maintenance Type Distribution</h3>
            <div class="h-80">
                <canvas id="maintenanceDistributionChart" wire:ignore></canvas>
            </div>
        </div>

        <!-- Monthly Maintenance by Type Chart -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <h3 class="text-md font-medium text-gray-700 mb-4">Monthly Maintenance by Type</h3>
            <div class="h-80">
                <canvas id="monthlyMaintenanceChart" wire:ignore></canvas>
            </div>
        </div>

        <!-- Preventive Maintenance Compliance Chart -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <h3 class="text-md font-medium text-gray-700 mb-4">Preventive Maintenance Compliance</h3>
            <div class="h-80">
                <canvas id="preventiveComplianceChart" wire:ignore></canvas>
            </div>
        </div>

        <!-- Corrective Maintenance by Area Chart -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <h3 class="text-md font-medium text-gray-700 mb-4">Corrective Maintenance by Area</h3>
            <div class="h-80">
                <canvas id="correctiveByAreaChart" wire:ignore></canvas>
            </div>
        </div>
    </div>

    <!-- Equipment Maintenance Details Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
        <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Maintenance Details by Equipment</h3>
            <p class="mt-1 text-sm text-gray-500">Total equipment: {{ $totalEquipment }}</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('equipment_name')">
                            Equipment
                            @if($sortField === 'equipment_name')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Area
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Line
                        </th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('preventive_count')">
                            Preventive
                            @if($sortField === 'preventive_count')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('corrective_count')">
                            Corrective
                            @if($sortField === 'corrective_count')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('predictive_count')">
                            Predictive
                            @if($sortField === 'predictive_count')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('total_actions')">
                            Total
                            @if($sortField === 'total_actions')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            DISTRIBUTION
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($maintenanceData as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $item['equipment_name'] }}</div>
                                <div class="text-xs text-gray-500">{{ $item['serial_number'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item['area'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item['line'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                {{ $item['preventive_count'] }}
                                <div class="text-xs">({{ $item['preventive_percentage'] }}%)</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                {{ $item['corrective_count'] }}
                                <div class="text-xs">({{ $item['corrective_percentage'] }}%)</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                {{ $item['predictive_count'] }}
                                <div class="text-xs">({{ $item['predictive_percentage'] }}%)</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                {{ $item['total_actions'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="w-full h-4 flex rounded-full overflow-hidden">
                                    @if ($item['preventive_count'] > 0)
                                        <div class="h-full bg-blue-500" style="width: {{ $item['preventive_percentage'] }}%"></div>
                                    @endif
                                    @if ($item['corrective_count'] > 0)
                                        <div class="h-full bg-red-500" style="width: {{ $item['corrective_percentage'] }}%"></div>
                                    @endif
                                    @if ($item['predictive_count'] > 0)
                                        <div class="h-full bg-green-500" style="width: {{ $item['predictive_percentage'] }}%"></div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                No maintenance data found for the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', function() {
        initCharts();

        Livewire.hook('morph.updated', () => {
            initCharts();
        });
    });

    function initCharts() {
        // Initialize the maintenance type distribution chart
        const distributionCtx = document.getElementById('maintenanceDistributionChart');
        if (distributionCtx) {
            // Check if chart already exists and destroy it
            if (window.distributionChart instanceof Chart) {
                window.distributionChart.destroy();
            }

            window.distributionChart = new Chart(distributionCtx, {
                type: 'pie',
                data: @json($maintenanceDistributionData),
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const dataset = context.dataset;
                                    const total = dataset.data.reduce((acc, data) => acc + data, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Initialize the monthly maintenance by type chart
        const monthlyCtx = document.getElementById('monthlyMaintenanceChart');
        if (monthlyCtx) {
            // Check if chart already exists and destroy it
            if (window.monthlyChart instanceof Chart) {
                window.monthlyChart.destroy();
            }

            window.monthlyChart = new Chart(monthlyCtx, {
                type: 'bar',
                data: @json($monthlyMaintenanceData),
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            title: {
                                display: true,
                                text: 'Month'
                            }
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Quantity'
                            },
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }

        // Initialize the preventive maintenance compliance chart
        const complianceCtx = document.getElementById('preventiveComplianceChart');
        if (complianceCtx) {
            // Check if chart already exists and destroy it
            if (window.complianceChart instanceof Chart) {
                window.complianceChart.destroy();
            }

            window.complianceChart = new Chart(complianceCtx, {
                type: 'doughnut',
                data: @json($preventiveComplianceData),
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    return `${label}: ${value}%`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Initialize the corrective maintenance by area chart
        const areaCtx = document.getElementById('correctiveByAreaChart');
        if (areaCtx) {
            // Check if chart already exists and destroy it
            if (window.areaChart instanceof Chart) {
                window.areaChart.destroy();
            }

            window.areaChart = new Chart(areaCtx, {
                type: 'bar',
                data: @json($correctiveByAreaData),
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Corrective Actions'
                            },
                            ticks: {
                                precision: 0
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Area'
                            }
                        }
                    }
                }
            });
        }
    }
</script>
@endpush
