<div>
    <h2 class="text-lg font-medium text-gray-900 mb-4">Downtime Impact Analysis</h2>

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

            <!-- Downtime Type Filter -->
            <div>
                <label for="downtimeType" class="block text-sm font-medium text-gray-700 mb-1">Downtime Type</label>
                <select id="downtimeType" wire:model.live="downtimeType" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="all">All Downtime Types</option>
                    <option value="planned">Planned Maintenance</option>
                    <option value="unplanned">Unplanned Failures</option>
                    <option value="operational">Operational Delays</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <!-- Total Downtime -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-red-100 p-3 mr-4">
                    <i class="fas fa-clock text-red-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Total Downtime</h3>
                    <p class="text-2xl font-bold">{{ number_format($totalDowntime, 1) }} hrs</p>
                </div>
            </div>
        </div>

        <!-- Production Loss -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-yellow-100 p-3 mr-4">
                    <i class="fas fa-industry text-yellow-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Production Loss</h3>
                    <p class="text-2xl font-bold">{{ number_format($productionLoss) }} units</p>
                </div>
            </div>
        </div>

        <!-- Financial Impact -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-green-100 p-3 mr-4">
                    <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Financial Impact</h3>
                    <p class="text-2xl font-bold">${{ number_format($financialImpact) }}</p>
                </div>
            </div>
        </div>

        <!-- Availability Rate -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-blue-100 p-3 mr-4">
                    <i class="fas fa-chart-pie text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Availability Rate</h3>
                    <p class="text-2xl font-bold">{{ $availabilityRate }}%</p>
                </div>
            </div>
        </div>

        <!-- Most Critical Equipment -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-purple-100 p-3 mr-4">
                    <i class="fas fa-exclamation-triangle text-purple-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Most Critical Equipment</h3>
                    <p class="text-2xl font-bold">{{ $mostCriticalEquipment }}</p>
                    <p class="text-xs text-gray-500">{{ number_format($mostCriticalDowntime, 1) }} hrs downtime</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Downtime by Equipment Chart -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <h3 class="text-md font-medium text-gray-700 mb-4">Downtime by Equipment</h3>
            <div class="h-80">
                <canvas id="downtimeByEquipmentChart" wire:ignore></canvas>
            </div>
        </div>

        <!-- Downtime Types Chart -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <h3 class="text-md font-medium text-gray-700 mb-4">Downtime by Type</h3>
            <div class="h-80">
                <canvas id="downtimeTypesChart" wire:ignore></canvas>
            </div>
        </div>

        <!-- Downtime Trend Chart -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <h3 class="text-md font-medium text-gray-700 mb-4">Downtime Trend</h3>
            <div class="h-80">
                <canvas id="downtimeTrendChart" wire:ignore></canvas>
            </div>
        </div>

        <!-- Financial Impact by Area Chart -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <h3 class="text-md font-medium text-gray-700 mb-4">Financial Impact by Area</h3>
            <div class="h-80">
                <canvas id="financialImpactChart" wire:ignore></canvas>
            </div>
        </div>
    </div>

    <!-- Downtime Records Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
        <div class="p-4 border-b border-gray-200">
            <h3 class="text-md font-medium text-gray-700">Detailed Downtime Records</h3>
            <p class="text-sm text-gray-500">Total records: {{ count($downtimeRecords) }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('start_date')" class="flex items-center">
                                Date
                                @if ($sortField === 'start_date')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('equipment')" class="flex items-center">
                                Equipment
                                @if ($sortField === 'equipment')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('area')" class="flex items-center">
                                Area / Line
                                @if ($sortField === 'area')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('type')" class="flex items-center">
                                Type
                                @if ($sortField === 'type')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('reason')" class="flex items-center">
                                Reason
                                @if ($sortField === 'reason')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('duration')" class="flex items-center">
                                Duration (hrs)
                                @if ($sortField === 'duration')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('production_loss')" class="flex items-center">
                                Production Loss
                                @if ($sortField === 'production_loss')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('financial_impact')" class="flex items-center">
                                Financial Impact
                                @if ($sortField === 'financial_impact')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($downtimeRecords as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item['start_date'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $item['equipment'] }}</div>
                                <div class="text-xs text-gray-500">{{ $item['serial_number'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item['area'] }} / {{ $item['line'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    {{ $item['type'] === 'Planned' ? 'text-blue-700 bg-blue-100' :
                                      ($item['type'] === 'Unplanned' ? 'text-red-700 bg-red-100' : 'text-yellow-700 bg-yellow-100') }}">
                                    {{ $item['type'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item['reason'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($item['duration'], 1) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($item['production_loss']) }} units
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${{ number_format($item['financial_impact']) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                No downtime records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Improvement Recommendations -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h3 class="text-md font-medium text-gray-700">Improvement Recommendations</h3>
            <p class="text-sm text-gray-500">Suggested actions to reduce downtime</p>
        </div>

        <div class="p-4">
            @forelse ($recommendations as $recommendation)
                <div class="mb-4 last:mb-0 p-4 bg-gray-50 rounded-md">
                    <h4 class="text-md font-medium text-gray-700 mb-2">{{ $recommendation['title'] }}</h4>
                    <div class="flex flex-wrap gap-4 mb-2">
                        <div class="flex items-center text-sm text-gray-500">
                            <i class="fas fa-tag text-indigo-500 mr-2"></i>
                            {{ $recommendation['target'] }}
                        </div>
                        <div class="flex items-center text-sm text-gray-500">
                            <i class="fas fa-clock text-indigo-500 mr-2"></i>
                            Potential savings: {{ number_format($recommendation['potential_hours_saved'], 1) }} hrs
                        </div>
                        <div class="flex items-center text-sm text-gray-500">
                            <i class="fas fa-dollar-sign text-indigo-500 mr-2"></i>
                            Estimated ROI: ${{ number_format($recommendation['estimated_roi']) }}
                        </div>
                        <div class="flex items-center text-sm text-gray-500">
                            <i class="fas fa-chart-line text-indigo-500 mr-2"></i>
                            Priority:
                            <span class="ml-1 px-2 py-0.5 text-xs font-medium rounded-full
                                {{ $recommendation['priority'] === 'High' ? 'text-red-700 bg-red-100' :
                                  ($recommendation['priority'] === 'Medium' ? 'text-yellow-700 bg-yellow-100' : 'text-green-700 bg-green-100') }}">
                                {{ $recommendation['priority'] }}
                            </span>
                        </div>
                    </div>
                    <p class="text-sm text-gray-700 mb-2">{{ $recommendation['description'] }}</p>
                    <div>
                        <h5 class="text-sm font-medium text-gray-600 mb-1">Implementation Steps</h5>
                        <ol class="list-decimal list-inside text-sm text-gray-600">
                            @foreach ($recommendation['implementation_steps'] as $step)
                                <li class="mb-1">{{ $step }}</li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            @empty
                <div class="text-center text-sm text-gray-500 py-4">
                    No recommendations available.
                </div>
            @endforelse
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
        // Initialize Downtime by Equipment Chart
        const equipmentCtx = document.getElementById('downtimeByEquipmentChart');
        if (equipmentCtx) {
            // Check if chart already exists and destroy it
            if (window.downtimeByEquipmentChart instanceof Chart) {
                window.downtimeByEquipmentChart.destroy();
            }

            window.downtimeByEquipmentChart = new Chart(equipmentCtx, {
                type: 'bar',
                data: @json($downtimeByEquipmentData),
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
                                text: 'Hours'
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

        // Initialize Downtime Types Chart
        const typesCtx = document.getElementById('downtimeTypesChart');
        if (typesCtx) {
            // Check if chart already exists and destroy it
            if (window.downtimeTypesChart instanceof Chart) {
                window.downtimeTypesChart.destroy();
            }

            window.downtimeTypesChart = new Chart(typesCtx, {
                type: 'doughnut',
                data: @json($downtimeTypesData),
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
                                    return `${label}: ${value.toFixed(1)} hrs (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Initialize Downtime Trend Chart
        const trendCtx = document.getElementById('downtimeTrendChart');
        if (trendCtx) {
            // Check if chart already exists and destroy it
            if (window.downtimeTrendChart instanceof Chart) {
                window.downtimeTrendChart.destroy();
            }

            window.downtimeTrendChart = new Chart(trendCtx, {
                type: 'line',
                data: @json($downtimeTrendData),
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
                                text: 'Hours'
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

        // Initialize Financial Impact Chart
        const impactCtx = document.getElementById('financialImpactChart');
        if (impactCtx) {
            // Check if chart already exists and destroy it
            if (window.financialImpactChart instanceof Chart) {
                window.financialImpactChart.destroy();
            }

            window.financialImpactChart = new Chart(impactCtx, {
                type: 'bar',
                data: @json($financialImpactData),
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
                                text: 'Financial Impact ($)'
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
    }
</script>
@endpush
