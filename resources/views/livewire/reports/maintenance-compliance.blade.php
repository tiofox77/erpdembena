<div>
    <h2 class="text-lg font-medium text-gray-900 mb-4">Maintenance Compliance Report</h2>

    <!-- Filters Section -->
    <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Date Range Filter -->
            <div>
                <label for="dateRange" class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                <select id="dateRange" wire:model.live="dateRange" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="week">Current Week</option>
                    <option value="month">Current Month</option>
                    <option value="quarter">Current Quarter</option>
                    <option value="year">Current Year</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>

            <!-- Custom Date Range -->
            @if ($dateRange === 'custom')
            <div>
                <label for="startDate" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date" id="startDate" wire:model.live="startDate" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="endDate" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input type="date" id="endDate" wire:model.live="endDate" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            @endif

            <!-- Area Filter -->
            <div>
                <label for="selectedArea" class="block text-sm font-medium text-gray-700 mb-1">Area</label>
                <select id="selectedArea" wire:model.live="selectedArea" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="all">All Areas</option>
                    @foreach ($areas as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Line Filter -->
            <div>
                <label for="selectedLine" class="block text-sm font-medium text-gray-700 mb-1">Line</label>
                <select id="selectedLine" wire:model.live="selectedLine" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="all">All Lines</option>
                    @foreach ($lines as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Search -->
            <div>
                <label for="searchTerm" class="block text-sm font-medium text-gray-700 mb-1">Search Equipment</label>
                <input type="text" id="searchTerm" wire:model.live.debounce.300ms="searchTerm" placeholder="Search by name or serial..." class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <!-- Overall Compliance Rate -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-indigo-100 p-3 mr-4">
                    <i class="fas fa-chart-pie text-indigo-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Overall Compliance</h3>
                    <p class="text-2xl font-bold">{{ $overallComplianceRate }}%</p>
                </div>
            </div>
        </div>

        <!-- Total Planned -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-blue-100 p-3 mr-4">
                    <i class="fas fa-calendar-alt text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Total Planned</h3>
                    <p class="text-2xl font-bold">{{ $totalPlanned }}</p>
                </div>
            </div>
        </div>

        <!-- Completed -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-green-100 p-3 mr-4">
                    <i class="fas fa-check text-green-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Completed</h3>
                    <p class="text-2xl font-bold">{{ $totalCompleted }}</p>
                </div>
            </div>
        </div>

        <!-- Overdue -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-red-100 p-3 mr-4">
                    <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Overdue</h3>
                    <p class="text-2xl font-bold">{{ $totalOverdue }}</p>
                </div>
            </div>
        </div>

        <!-- Upcoming -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-yellow-100 p-3 mr-4">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Upcoming</h3>
                    <p class="text-2xl font-bold">{{ $totalUpcoming }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Compliance Trend Chart -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <h3 class="text-md font-medium text-gray-700 mb-4">Compliance Trend</h3>
            <div class="h-80">
                <canvas id="complianceTrendChart" wire:ignore></canvas>
            </div>
        </div>

        <!-- Compliance by Area Chart -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <h3 class="text-md font-medium text-gray-700 mb-4">Compliance by Area</h3>
            <div class="h-80">
                <canvas id="complianceByAreaChart" wire:ignore></canvas>
            </div>
        </div>

        <!-- Compliance by Equipment Chart -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <h3 class="text-md font-medium text-gray-700 mb-4">Planned vs Completed by Equipment</h3>
            <div class="h-80">
                <canvas id="complianceByEquipmentChart" wire:ignore></canvas>
            </div>
        </div>

        <!-- Overdue Tasks Chart -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <h3 class="text-md font-medium text-gray-700 mb-4">Top Overdue Tasks</h3>
            <div class="h-80">
                <canvas id="overdueTasksChart" wire:ignore></canvas>
            </div>
        </div>
    </div>

    <!-- Equipment Compliance Data Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h3 class="text-md font-medium text-gray-700">Equipment Compliance Details</h3>
            <p class="text-sm text-gray-500">Total equipment: {{ $totalEquipment }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('name')" class="flex items-center">
                                Equipment
                                @if ($sortField === 'name')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('area')" class="flex items-center">
                                Area
                                @if ($sortField === 'area')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('line')" class="flex items-center">
                                Line
                                @if ($sortField === 'line')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('planned_count')" class="flex items-center">
                                Planned
                                @if ($sortField === 'planned_count')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('completed_count')" class="flex items-center">
                                Completed
                                @if ($sortField === 'completed_count')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('overdue_count')" class="flex items-center">
                                Overdue
                                @if ($sortField === 'overdue_count')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('upcoming_count')" class="flex items-center">
                                Upcoming
                                @if ($sortField === 'upcoming_count')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('compliance_rate')" class="flex items-center">
                                Compliance
                                @if ($sortField === 'compliance_rate')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($complianceData as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $item['name'] }}</div>
                                <div class="text-xs text-gray-500">{{ $item['serial_number'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item['area'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item['line'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item['planned_count'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item['completed_count'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if ($item['overdue_count'] > 0)
                                    <span class="px-2 py-1 text-xs font-medium text-red-700 bg-red-100 rounded-full">
                                        {{ $item['overdue_count'] }}
                                    </span>
                                @else
                                    {{ $item['overdue_count'] }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item['upcoming_count'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="relative w-24 h-2 bg-gray-200 rounded mr-2">
                                        <div class="absolute left-0 top-0 h-2 rounded"
                                             style="width: {{ $item['compliance_rate'] }}%; background-color: {{ $item['compliance_rate'] >= 90 ? '#10B981' : ($item['compliance_rate'] >= 70 ? '#F59E0B' : '#EF4444') }}"></div>
                                    </div>
                                    <span class="text-sm text-gray-700">{{ $item['compliance_rate'] }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                No compliance data found.
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
        // Initialize Compliance Trend Chart
        const trendCtx = document.getElementById('complianceTrendChart');
        if (trendCtx) {
            // Check if chart already exists and destroy it
            if (window.complianceTrendChart instanceof Chart) {
                window.complianceTrendChart.destroy();
            }

            window.complianceTrendChart = new Chart(trendCtx, {
                type: 'line',
                data: @json($complianceTrendData),
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            title: {
                                display: true,
                                text: 'Compliance Rate (%)'
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
        }

        // Initialize Compliance by Area Chart
        const areaCtx = document.getElementById('complianceByAreaChart');
        if (areaCtx) {
            // Check if chart already exists and destroy it
            if (window.complianceByAreaChart instanceof Chart) {
                window.complianceByAreaChart.destroy();
            }

            window.complianceByAreaChart = new Chart(areaCtx, {
                type: 'bar',
                data: @json($complianceByAreaData),
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            title: {
                                display: true,
                                text: 'Compliance Rate (%)'
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
        }

        // Initialize Compliance by Equipment Chart
        const equipmentCtx = document.getElementById('complianceByEquipmentChart');
        if (equipmentCtx) {
            // Check if chart already exists and destroy it
            if (window.complianceByEquipmentChart instanceof Chart) {
                window.complianceByEquipmentChart.destroy();
            }

            window.complianceByEquipmentChart = new Chart(equipmentCtx, {
                type: 'bar',
                data: @json($complianceByEquipmentData),
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                    },
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
                                text: 'Equipment'
                            }
                        }
                    }
                }
            });
        }

        // Initialize Overdue Tasks Chart
        const overdueCtx = document.getElementById('overdueTasksChart');
        if (overdueCtx) {
            // Check if chart already exists and destroy it
            if (window.overdueTasksChart instanceof Chart) {
                window.overdueTasksChart.destroy();
            }

            window.overdueTasksChart = new Chart(overdueCtx, {
                type: 'bar',
                data: @json($overdueTasksData),
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            display: false,
                        },
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Days Overdue'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Equipment'
                            }
                        }
                    }
                }
            });
        }
    }
</script>
@endpush
