<div>
    <h2 class="text-lg font-medium text-gray-900 mb-4">Equipment Reliability Report</h2>

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
        <!-- Overall Reliability -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-purple-100 p-3 mr-4">
                    <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Overall Reliability</h3>
                    <p class="text-2xl font-bold">{{ $averageReliability }}%</p>
                </div>
            </div>
        </div>

        <!-- Average MTBF -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-blue-100 p-3 mr-4">
                    <i class="fas fa-clock text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Avg. MTBF</h3>
                    <p class="text-2xl font-bold">{{ $averageMtbf }} hrs</p>
                </div>
            </div>
        </div>

        <!-- Average MTTR -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-green-100 p-3 mr-4">
                    <i class="fas fa-tools text-green-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Avg. MTTR</h3>
                    <p class="text-2xl font-bold">{{ $averageMttr }} hrs</p>
                </div>
            </div>
        </div>

        <!-- Total Failures -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-red-100 p-3 mr-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Total Failures</h3>
                    <p class="text-2xl font-bold">{{ $totalFailures }}</p>
                </div>
            </div>
        </div>

        <!-- Failure Frequency -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-yellow-100 p-3 mr-4">
                    <i class="fas fa-tachometer-alt text-yellow-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Failure Rate</h3>
                    <p class="text-2xl font-bold">{{ $failureRate }}%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Reliability Trend Chart -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <h3 class="text-md font-medium text-gray-700 mb-4">Reliability Trend</h3>
            <div class="h-80">
                <canvas id="reliabilityTrendChart" wire:ignore></canvas>
            </div>
        </div>

        <!-- MTBF vs MTTR Chart -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <h3 class="text-md font-medium text-gray-700 mb-4">MTBF vs MTTR by Area</h3>
            <div class="h-80">
                <canvas id="mtbfMttrChart" wire:ignore></canvas>
            </div>
        </div>

        <!-- Top Failures by Equipment Chart -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <h3 class="text-md font-medium text-gray-700 mb-4">Most Frequent Failures by Equipment</h3>
            <div class="h-80">
                <canvas id="failuresByEquipmentChart" wire:ignore></canvas>
            </div>
        </div>

        <!-- Failure Categories Chart -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <h3 class="text-md font-medium text-gray-700 mb-4">Failure Types Distribution</h3>
            <div class="h-80">
                <canvas id="failureCategoriesChart" wire:ignore></canvas>
            </div>
        </div>
    </div>

    <!-- Equipment Reliability Data Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h3 class="text-md font-medium text-gray-700">Equipment Reliability Details</h3>
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
                            <button wire:click="sortBy('mtbf')" class="flex items-center">
                                MTBF (hrs)
                                @if ($sortField === 'mtbf')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('mttr')" class="flex items-center">
                                MTTR (hrs)
                                @if ($sortField === 'mttr')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('failure_count')" class="flex items-center">
                                Failures
                                @if ($sortField === 'failure_count')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('reliability')" class="flex items-center">
                                Reliability
                                @if ($sortField === 'reliability')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Most Recent Failure
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($equipmentData as $item)
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
                                {{ $item['mtbf'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item['mttr'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if ($item['failure_count'] > 3)
                                    <span class="px-2 py-1 text-xs font-medium text-red-700 bg-red-100 rounded-full">
                                        {{ $item['failure_count'] }}
                                    </span>
                                @else
                                    {{ $item['failure_count'] }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="relative w-24 h-2 bg-gray-200 rounded mr-2">
                                        <div class="absolute left-0 top-0 h-2 rounded"
                                             style="width: {{ $item['reliability'] }}%; background-color: {{ $item['reliability'] >= 90 ? '#10B981' : ($item['reliability'] >= 70 ? '#F59E0B' : '#EF4444') }}"></div>
                                    </div>
                                    <span class="text-sm text-gray-700">{{ $item['reliability'] }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item['last_failure'] }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                No reliability data found.
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
        // Initialize Reliability Trend Chart
        const trendCtx = document.getElementById('reliabilityTrendChart');
        if (trendCtx) {
            // Check if chart already exists and destroy it
            if (window.reliabilityTrendChart instanceof Chart) {
                window.reliabilityTrendChart.destroy();
            }

            window.reliabilityTrendChart = new Chart(trendCtx, {
                type: 'line',
                data: @json($reliabilityTrendData),
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
                                text: 'Reliability Rate (%)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        }
                    }
                }
            });
        }

        // Initialize MTBF/MTTR Chart
        const mtbfMttrCtx = document.getElementById('mtbfMttrChart');
        if (mtbfMttrCtx) {
            // Check if chart already exists and destroy it
            if (window.mtbfMttrChart instanceof Chart) {
                window.mtbfMttrChart.destroy();
            }

            window.mtbfMttrChart = new Chart(mtbfMttrCtx, {
                type: 'bar',
                data: @json($mtbfTrendData),
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.raw.toFixed(2) + ' hrs';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'MTBF (Hours)'
                            },
                            grid: {
                                drawOnChartArea: false
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'MTTR (Hours)'
                            },
                            // Não duplicar as linhas de grade
                            grid: {
                                drawOnChartArea: false
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Period'
                            }
                        }
                    }
                }
            });
        }

        // Initialize Failures by Equipment Chart
        const failuresEquipmentCtx = document.getElementById('failuresByEquipmentChart');
        if (failuresEquipmentCtx) {
            // Check if chart already exists and destroy it
            if (window.failuresByEquipmentChart instanceof Chart) {
                window.failuresByEquipmentChart.destroy();
            }

            window.failuresByEquipmentChart = new Chart(failuresEquipmentCtx, {
                type: 'bar',
                data: @json($failureRatesData),
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
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
                                text: 'Number of Failures'
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

        // Initialize Failure Categories Chart
        const categoriesCtx = document.getElementById('failureCategoriesChart');
        if (categoriesCtx) {
            // Check if chart already exists and destroy it
            if (window.failureCategoriesChart instanceof Chart) {
                window.failureCategoriesChart.destroy();
            }

            window.failureCategoriesChart = new Chart(categoriesCtx, {
                type: 'doughnut',
                data: @json($topFailureModesData),
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                    }
                }
            });
        }
    }
</script>
@endpush
