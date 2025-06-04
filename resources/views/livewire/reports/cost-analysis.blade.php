<div>
    <h2 class="text-lg font-medium text-gray-900 mb-4">Maintenance Cost Analysis</h2>

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

            <!-- Cost Type Filter -->
            <div>
                <label for="costType" class="block text-sm font-medium text-gray-700 mb-1">Cost Type</label>
                <select id="costType" wire:model.live="costType" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="all">All Cost Types</option>
                    <option value="labor">Labor</option>
                    <option value="parts">Parts/Materials</option>
                    <option value="external">External Services</option>
                    <option value="downtime">Downtime</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <!-- Total Maintenance Cost -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-green-100 p-3 mr-4">
                    <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Total Cost</h3>
                    <p class="text-2xl font-bold">${{ number_format($totalCost, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Labor Cost -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-blue-100 p-3 mr-4">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Labor Cost</h3>
                    <p class="text-2xl font-bold">${{ number_format($laborCost, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Parts/Materials Cost -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-purple-100 p-3 mr-4">
                    <i class="fas fa-cogs text-purple-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Parts Cost</h3>
                    <p class="text-2xl font-bold">${{ number_format($partsCost, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- External Services Cost -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-yellow-100 p-3 mr-4">
                    <i class="fas fa-handshake text-yellow-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">External Services</h3>
                    <p class="text-2xl font-bold">${{ number_format($externalCost, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Downtime Cost -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-red-100 p-3 mr-4">
                    <i class="fas fa-clock text-red-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Downtime Cost</h3>
                    <p class="text-2xl font-bold">${{ number_format($downtimeCost, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Cost Breakdown Chart -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <h3 class="text-md font-medium text-gray-700 mb-4">Cost Breakdown</h3>
            <div class="h-80">
                <canvas id="costBreakdownChart" wire:ignore></canvas>
            </div>
        </div>

        <!-- Monthly Cost Trend Chart -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <h3 class="text-md font-medium text-gray-700 mb-4">Monthly Cost Trend</h3>
            <div class="h-80">
                <canvas id="monthlyCostTrendChart" wire:ignore></canvas>
            </div>
        </div>

        <!-- Preventive vs Corrective Cost Chart -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <h3 class="text-md font-medium text-gray-700 mb-4">Preventive vs Corrective Cost</h3>
            <div class="h-80">
                <canvas id="preventiveVsCorrective" wire:ignore></canvas>
            </div>
        </div>

        <!-- Cost by Area Chart -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <h3 class="text-md font-medium text-gray-700 mb-4">Cost by Area</h3>
            <div class="h-80">
                <canvas id="costByAreaChart" wire:ignore></canvas>
            </div>
        </div>
    </div>

    <!-- Top Cost Equipment Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
        <div class="p-4 border-b border-gray-200">
            <h3 class="text-md font-medium text-gray-700">Top Cost Equipment</h3>
            <p class="text-sm text-gray-500">Equipment with highest maintenance costs</p>
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
                            <button wire:click="sortBy('labor_cost')" class="flex items-center">
                                Labor Cost
                                @if ($sortField === 'labor_cost')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('parts_cost')" class="flex items-center">
                                Parts Cost
                                @if ($sortField === 'parts_cost')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('external_cost')" class="flex items-center">
                                External Cost
                                @if ($sortField === 'external_cost')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('downtime_cost')" class="flex items-center">
                                Downtime Cost
                                @if ($sortField === 'downtime_cost')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('total_cost')" class="flex items-center">
                                Total Cost
                                @if ($sortField === 'total_cost')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($equipmentCosts as $item)
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
                                ${{ number_format($item['labor_cost'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${{ number_format($item['parts_cost'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${{ number_format($item['external_cost'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${{ number_format($item['downtime_cost'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                ${{ number_format($item['total_cost'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                No cost data found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Cost Analysis by Maintenance Type -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h3 class="text-md font-medium text-gray-700">Cost Analysis by Maintenance Type</h3>
            <p class="text-sm text-gray-500">Cost breakdown by maintenance type and category</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortByType('type')" class="flex items-center">
                                Maintenance Type
                                @if ($sortTypeField === 'type')
                                    <i class="ml-1 fas fa-sort-{{ $sortTypeDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortByType('count')" class="flex items-center">
                                Count
                                @if ($sortTypeField === 'count')
                                    <i class="ml-1 fas fa-sort-{{ $sortTypeDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortByType('labor_cost')" class="flex items-center">
                                Labor Cost
                                @if ($sortTypeField === 'labor_cost')
                                    <i class="ml-1 fas fa-sort-{{ $sortTypeDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortByType('parts_cost')" class="flex items-center">
                                Parts Cost
                                @if ($sortTypeField === 'parts_cost')
                                    <i class="ml-1 fas fa-sort-{{ $sortTypeDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortByType('external_cost')" class="flex items-center">
                                External Cost
                                @if ($sortTypeField === 'external_cost')
                                    <i class="ml-1 fas fa-sort-{{ $sortTypeDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortByType('downtime_cost')" class="flex items-center">
                                Downtime Cost
                                @if ($sortTypeField === 'downtime_cost')
                                    <i class="ml-1 fas fa-sort-{{ $sortTypeDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortByType('total_cost')" class="flex items-center">
                                Total Cost
                                @if ($sortTypeField === 'total_cost')
                                    <i class="ml-1 fas fa-sort-{{ $sortTypeDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortByType('avg_cost')" class="flex items-center">
                                Avg. Cost
                                @if ($sortTypeField === 'avg_cost')
                                    <i class="ml-1 fas fa-sort-{{ $sortTypeDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($typeCosts as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $item['type'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item['count'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${{ number_format($item['labor_cost'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${{ number_format($item['parts_cost'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${{ number_format($item['external_cost'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${{ number_format($item['downtime_cost'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                ${{ number_format($item['total_cost'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${{ number_format($item['avg_cost'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                No cost by type data found.
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
        // Initialize Cost Breakdown Chart
        const breakdownCtx = document.getElementById('costBreakdownChart');
        if (breakdownCtx) {
            // Check if chart already exists and destroy it
            if (window.costBreakdownChart instanceof Chart) {
                window.costBreakdownChart.destroy();
            }

            window.costBreakdownChart = new Chart(breakdownCtx, {
                type: 'doughnut',
                data: @json($costBreakdownData),
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
                                    let label = context.label || '';
                                    let value = context.raw || 0;
                                    let total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                    let percentage = Math.round((value / total * 100) * 10) / 10;
                                    return `${label}: $${value.toLocaleString()} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Initialize Monthly Cost Trend Chart
        const trendCtx = document.getElementById('monthlyCostTrendChart');
        if (trendCtx) {
            // Check if chart already exists and destroy it
            if (window.monthlyCostTrendChart instanceof Chart) {
                window.monthlyCostTrendChart.destroy();
            }

            window.monthlyCostTrendChart = new Chart(trendCtx, {
                type: 'line',
                data: @json($monthlyCostTrendData),
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
                                text: 'Cost ($)'
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

        // Initialize Preventive vs Corrective Cost Chart
        const preventiveVsCorrectiveCtx = document.getElementById('preventiveVsCorrective');
        if (preventiveVsCorrectiveCtx) {
            // Check if chart already exists and destroy it
            if (window.preventiveVsCorrectiveChart instanceof Chart) {
                window.preventiveVsCorrectiveChart.destroy();
            }

            window.preventiveVsCorrectiveChart = new Chart(preventiveVsCorrectiveCtx, {
                type: 'bar',
                data: @json($preventiveVsCorrectiveData),
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
                            stacked: true,
                            title: {
                                display: true,
                                text: 'Cost ($)'
                            }
                        },
                        x: {
                            stacked: true,
                            title: {
                                display: true,
                                text: 'Month'
                            }
                        }
                    }
                }
            });
        }

        // Initialize Cost by Area Chart
        const areaCtx = document.getElementById('costByAreaChart');
        if (areaCtx) {
            // Check if chart already exists and destroy it
            if (window.costByAreaChart instanceof Chart) {
                window.costByAreaChart.destroy();
            }

            window.costByAreaChart = new Chart(areaCtx, {
                type: 'bar',
                data: @json($costByAreaData),
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
                            stacked: true,
                            title: {
                                display: true,
                                text: 'Cost ($)'
                            }
                        },
                        x: {
                            stacked: true,
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
