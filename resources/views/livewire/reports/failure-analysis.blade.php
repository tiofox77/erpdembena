<div>
    <h2 class="text-lg font-medium text-gray-900 mb-4">Failure Analysis Report</h2>

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

            <!-- Equipment Filter -->
            <div>
                <label for="selectedEquipment" class="block text-sm font-medium text-gray-700 mb-1">Equipment</label>
                <select id="selectedEquipment" wire:model.live="selectedEquipment" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="all">All Equipment</option>
                    @foreach ($equipment as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
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

        <!-- Top Failure Cause -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-yellow-100 p-3 mr-4">
                    <i class="fas fa-exclamation-circle text-yellow-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Top Failure Cause</h3>
                    <p class="text-2xl font-bold">{{ $topFailureCause }}</p>
                    <p class="text-xs text-gray-500">{{ $topFailureCauseCount }} occurrences</p>
                </div>
            </div>
        </div>

        <!-- Most Failing Equipment -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-blue-100 p-3 mr-4">
                    <i class="fas fa-tools text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Most Failing Equipment</h3>
                    <p class="text-2xl font-bold">{{ $mostFailingEquipment }}</p>
                    <p class="text-xs text-gray-500">{{ $mostFailingEquipmentCount }} failures</p>
                </div>
            </div>
        </div>

        <!-- Average Downtime -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-purple-100 p-3 mr-4">
                    <i class="fas fa-clock text-purple-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Avg. Downtime</h3>
                    <p class="text-2xl font-bold">{{ number_format($averageDowntime, 1) }} hrs</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Failure Causes Chart -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <h3 class="text-md font-medium text-gray-700 mb-4">Failure Causes Distribution</h3>
            <div class="h-80">
                <canvas id="failureCausesChart" wire:ignore></canvas>
            </div>
        </div>

        <!-- Failures by Equipment Chart -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <h3 class="text-md font-medium text-gray-700 mb-4">Failures by Equipment</h3>
            <div class="h-80">
                <canvas id="failuresByEquipmentChart" wire:ignore></canvas>
            </div>
        </div>

        <!-- Failures Over Time Chart -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <h3 class="text-md font-medium text-gray-700 mb-4">Failures Over Time</h3>
            <div class="h-80">
                <canvas id="failuresOverTimeChart" wire:ignore></canvas>
            </div>
        </div>

        <!-- Failure Impact Chart -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <h3 class="text-md font-medium text-gray-700 mb-4">Failure Impact (Downtime)</h3>
            <div class="h-80">
                <canvas id="failureImpactChart" wire:ignore></canvas>
            </div>
        </div>
    </div>

    <!-- Detailed Failure Analysis Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
        <div class="p-4 border-b border-gray-200">
            <h3 class="text-md font-medium text-gray-700">Detailed Failure Records</h3>
            <p class="text-sm text-gray-500">Complete failure analysis data</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('date')" class="flex items-center">
                                Date
                                @if ($sortField === 'date')
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
                                Area
                                @if ($sortField === 'area')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('root_cause')" class="flex items-center">
                                Root Cause
                                @if ($sortField === 'root_cause')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('failed_component')" class="flex items-center">
                                Failed Component
                                @if ($sortField === 'failed_component')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('downtime')" class="flex items-center">
                                Downtime (hrs)
                                @if ($sortField === 'downtime')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($failureRecords as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item['date'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $item['equipment'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item['area'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item['root_cause'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item['failed_component'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($item['downtime'], 1) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <button wire:click="showFailureDetails({{ $item['id'] }})" class="text-indigo-600 hover:text-indigo-900">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                No failure records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Failure Patterns Analysis -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h3 class="text-md font-medium text-gray-700">Failure Patterns & Recommendations</h3>
            <p class="text-sm text-gray-500">Identified patterns and suggested actions</p>
        </div>

        <div class="p-4">
            @forelse ($identifiedPatterns as $pattern)
                <div class="mb-6 last:mb-0 border-b pb-4 last:border-0">
                    <h4 class="text-md font-medium text-gray-700 mb-2">{{ $pattern['type'] }}</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <h5 class="text-sm font-medium text-gray-600 mb-1">Severity</h5>
                            <p class="text-sm">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    {{ $pattern['severity'] === 'High' ? 'text-red-700 bg-red-100' :
                                    ($pattern['severity'] === 'Medium' ? 'text-yellow-700 bg-yellow-100' : 'text-green-700 bg-green-100') }}">
                                    {{ $pattern['severity'] }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="mb-2">
                        <h5 class="text-sm font-medium text-gray-600 mb-1">Description</h5>
                        <p class="text-sm text-gray-500">{{ $pattern['description'] }}</p>
                    </div>
                    <div>
                        <h5 class="text-sm font-medium text-gray-600 mb-1">Suggested Action</h5>
                        <p class="text-sm text-gray-500">{{ $pattern['suggested_action'] }}</p>
                    </div>
                </div>
            @empty
                <div class="text-center text-sm text-gray-500 py-4">
                    No failure patterns identified.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Detail Modal -->
    @if($showDetailModal)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50" wire:click="closeModal">
        <div class="bg-white rounded-lg overflow-hidden shadow-xl max-w-4xl w-full mx-4 transform" wire:click.stop="">
            <div class="px-6 py-4 bg-gray-100 border-b">
                <h3 class="text-lg font-medium text-gray-900">Failure Detail - {{ $selectedFailure['equipment'] }}</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h4 class="text-md font-medium text-gray-700 mb-4">Basic Information</h4>
                        <div class="space-y-3">
                            <div class="flex">
                                <span class="w-1/3 text-sm font-medium text-gray-500">Date:</span>
                                <span class="w-2/3 text-sm text-gray-900">{{ $selectedFailure['date'] }}</span>
                            </div>
                            <div class="flex">
                                <span class="w-1/3 text-sm font-medium text-gray-500">Equipment:</span>
                                <span class="w-2/3 text-sm text-gray-900">{{ $selectedFailure['equipment'] }}</span>
                            </div>
                            <div class="flex">
                                <span class="w-1/3 text-sm font-medium text-gray-500">Serial Number:</span>
                                <span class="w-2/3 text-sm text-gray-900">{{ $selectedFailure['serial_number'] }}</span>
                            </div>
                            <div class="flex">
                                <span class="w-1/3 text-sm font-medium text-gray-500">Location:</span>
                                <span class="w-2/3 text-sm text-gray-900">{{ $selectedFailure['area'] }} - {{ $selectedFailure['line'] }}</span>
                            </div>
                            <div class="flex">
                                <span class="w-1/3 text-sm font-medium text-gray-500">Failed Component:</span>
                                <span class="w-2/3 text-sm text-gray-900">{{ $selectedFailure['failure_mode'] }}</span>
                            </div>
                            <div class="flex">
                                <span class="w-1/3 text-sm font-medium text-gray-500">Reported By:</span>
                                <span class="w-2/3 text-sm text-gray-900">{{ $selectedFailure['reported_by'] }}</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-md font-medium text-gray-700 mb-4">Impact Assessment</h4>
                        <div class="space-y-3">
                            <div class="flex">
                                <span class="w-1/3 text-sm font-medium text-gray-500">Downtime:</span>
                                <span class="w-2/3 text-sm text-gray-900">{{ $selectedFailure['downtime_hours'] }} hours</span>
                            </div>
                            <div class="flex">
                                <span class="w-1/3 text-sm font-medium text-gray-500">Status:</span>
                                <span class="w-2/3 text-sm text-gray-900">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        {{ $selectedFailure['status'] === 'resolved' ? 'text-green-700 bg-green-100' :
                                          ($selectedFailure['status'] === 'in_progress' ? 'text-yellow-700 bg-yellow-100' : 'text-red-700 bg-red-100') }}">
                                        {{ ucfirst($selectedFailure['status']) }}
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <h4 class="text-md font-medium text-gray-700 mb-4">Root Cause</h4>
                    <div class="space-y-3">
                        <div class="flex">
                            <span class="w-1/4 text-sm font-medium text-gray-500">Failure Mode:</span>
                            <span class="w-3/4 text-sm text-gray-900">{{ $selectedFailure['failure_mode'] }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-1/4 text-sm font-medium text-gray-500">Failure Cause:</span>
                            <span class="w-3/4 text-sm text-gray-900">{{ $selectedFailure['failure_cause'] }}</span>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <h4 class="text-md font-medium text-gray-700 mb-4">Description</h4>
                    <p class="text-sm text-gray-700">{{ $selectedFailure['description'] }}</p>
                </div>

                <div>
                    <h4 class="text-md font-medium text-gray-700 mb-4">Actions Taken</h4>
                    <p class="text-sm text-gray-700">{{ $selectedFailure['actions_taken'] }}</p>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-100 border-t flex justify-end">
                <button type="button" wire:click="closeModal" class="px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Close
                </button>
            </div>
        </div>
    </div>
    @endif
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
        // Initialize Failure Causes Chart
        const causesCtx = document.getElementById('failureCausesChart');
        if (causesCtx) {
            // Check if chart already exists and destroy it
            if (window.failureCausesChart instanceof Chart) {
                window.failureCausesChart.destroy();
            }

            window.failureCausesChart = new Chart(causesCtx, {
                type: 'pie',
                data: @json($failureCausesData),
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

        // Initialize Failures by Equipment Chart
        const equipmentCtx = document.getElementById('failuresByEquipmentChart');
        if (equipmentCtx) {
            // Check if chart already exists and destroy it
            if (window.failuresByEquipmentChart instanceof Chart) {
                window.failuresByEquipmentChart.destroy();
            }

            window.failuresByEquipmentChart = new Chart(equipmentCtx, {
                type: 'bar',
                data: @json($failuresByEquipmentData),
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

        // Initialize Failures Over Time Chart
        const timeCtx = document.getElementById('failuresOverTimeChart');
        if (timeCtx) {
            // Check if chart already exists and destroy it
            if (window.failuresOverTimeChart instanceof Chart) {
                window.failuresOverTimeChart.destroy();
            }

            window.failuresOverTimeChart = new Chart(timeCtx, {
                type: 'line',
                data: @json($failuresOverTimeData),
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
                                text: 'Number of Failures'
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

        // Initialize Failure Impact Chart
        const impactCtx = document.getElementById('failureImpactChart');
        if (impactCtx) {
            // Check if chart already exists and destroy it
            if (window.failureImpactChart instanceof Chart) {
                window.failureImpactChart.destroy();
            }

            window.failureImpactChart = new Chart(impactCtx, {
                type: 'bar',
                data: @json($failureImpactData),
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
                            title: {
                                display: true,
                                text: 'Downtime (Hours)'
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
    }
</script>
@endpush

