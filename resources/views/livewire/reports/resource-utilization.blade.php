<div>
    <h2 class="text-lg font-medium text-gray-900 mb-4">Resource Utilization Report</h2>

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

            <!-- Resource Type Filter -->
            <div>
                <label for="resourceType" class="block text-sm font-medium text-gray-700 mb-1">Resource Type</label>
                <select id="resourceType" wire:model.live="resourceType" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="all">All Resources</option>
                    <option value="technician">Technicians</option>
                    <option value="tool">Tools/Equipment</option>
                    <option value="part">Parts/Materials</option>
                </select>
            </div>

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

            <!-- Search -->
            <div>
                <label for="searchTerm" class="block text-sm font-medium text-gray-700 mb-1">Search Resource</label>
                <input type="text" id="searchTerm" wire:model.live.debounce.300ms="searchTerm" placeholder="Search by name..." class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <!-- Average Utilization Rate -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-indigo-100 p-3 mr-4">
                    <i class="fas fa-percentage text-indigo-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Avg. Utilization Rate</h3>
                    <p class="text-2xl font-bold">{{ $averageUtilization }}%</p>
                </div>
            </div>
        </div>

        <!-- Total Hours Worked -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-blue-100 p-3 mr-4">
                    <i class="fas fa-clock text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Total Hours Worked</h3>
                    <p class="text-2xl font-bold">{{ number_format($totalHoursWorked, 1) }}</p>
                </div>
            </div>
        </div>

        <!-- Available Resources -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-green-100 p-3 mr-4">
                    <i class="fas fa-users text-green-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Available Resources</h3>
                    <p class="text-2xl font-bold">{{ $availableResources }}</p>
                </div>
            </div>
        </div>

        <!-- Top Utilized Resource -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-purple-100 p-3 mr-4">
                    <i class="fas fa-user-check text-purple-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Top Utilized Resource</h3>
                    <p class="text-2xl font-bold">{{ $topResource }}</p>
                    <p class="text-sm text-gray-500">{{ $topUtilization }}% Utilization</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Resource Utilization Chart -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <h3 class="text-md font-medium text-gray-700 mb-4">Resource Utilization Rates</h3>
            <div class="h-80">
                <canvas id="resourceUtilizationChart" wire:ignore></canvas>
            </div>
        </div>

        <!-- Utilization Trend Chart -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <h3 class="text-md font-medium text-gray-700 mb-4">Utilization Trend</h3>
            <div class="h-80">
                <canvas id="utilizationTrendChart" wire:ignore></canvas>
            </div>
        </div>

        <!-- Utilization by Area Chart -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <h3 class="text-md font-medium text-gray-700 mb-4">Utilization by Area</h3>
            <div class="h-80">
                <canvas id="utilizationByAreaChart" wire:ignore></canvas>
            </div>
        </div>

        <!-- Task Distribution Chart -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <h3 class="text-md font-medium text-gray-700 mb-4">Task Type Distribution</h3>
            <div class="h-80">
                <canvas id="taskDistributionChart" wire:ignore></canvas>
            </div>
        </div>
    </div>

    <!-- Resource Utilization Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
        <div class="p-4 border-b border-gray-200">
            <h3 class="text-md font-medium text-gray-700">Resource Utilization Details</h3>
            <p class="text-sm text-gray-500">Total resources: {{ $availableResources }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('name')" class="flex items-center">
                                Resource Name
                                @if ($sortField === 'name')
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
                            <button wire:click="sortBy('area')" class="flex items-center">
                                Primary Area
                                @if ($sortField === 'area')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('total_hours')" class="flex items-center">
                                Total Hours
                                @if ($sortField === 'total_hours')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('available_hours')" class="flex items-center">
                                Available Hours
                                @if ($sortField === 'available_hours')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('utilization_rate')" class="flex items-center">
                                Utilization Rate
                                @if ($sortField === 'utilization_rate')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('tasks_completed')" class="flex items-center">
                                Tasks Completed
                                @if ($sortField === 'tasks_completed')
                                    <i class="ml-1 fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($resourceDetails as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $item['name'] }}</div>
                                <div class="text-xs text-gray-500">ID: {{ $item['id'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item['type'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item['primary_area'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($item['hours_worked'], 1) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($item['available_hours'] ?? 0, 1) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="relative w-24 h-2 bg-gray-200 rounded mr-2">
                                        <div class="absolute left-0 top-0 h-2 rounded"
                                             style="width: {{ $item['utilization_rate'] }}%; background-color: {{ $item['utilization_rate'] >= 90 ? '#EF4444' : ($item['utilization_rate'] >= 70 ? '#F59E0B' : '#10B981') }}"></div>
                                    </div>
                                    <span class="text-sm text-gray-700">{{ $item['utilization_rate'] }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item['tasks_completed'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if ($item['utilization_rate'] >= 90)
                                    <span class="px-2 py-1 text-xs font-medium text-red-700 bg-red-100 rounded-full">
                                        Overutilized
                                    </span>
                                @elseif ($item['utilization_rate'] >= 70)
                                    <span class="px-2 py-1 text-xs font-medium text-yellow-700 bg-yellow-100 rounded-full">
                                        Optimal
                                    </span>
                                @elseif ($item['utilization_rate'] >= 40)
                                    <span class="px-2 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full">
                                        Good
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-full">
                                        Underutilized
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                No resource utilization data found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Current Resource Allocation -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h3 class="text-md font-medium text-gray-700">Current Resource Allocation</h3>
            <p class="text-sm text-gray-500">Active tasks and assigned resources</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Task
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Equipment
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Area
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Priority
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Assigned Resources
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Start Date
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estimated Completion
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($currentAllocations as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $item['task_name'] }}</div>
                                <div class="text-xs text-gray-500">Task ID: {{ $item['task_id'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item['equipment'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item['area'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    {{ $item['priority'] === 'High' ? 'text-red-700 bg-red-100' :
                                      ($item['priority'] === 'Medium' ? 'text-yellow-700 bg-yellow-100' : 'text-green-700 bg-green-100') }}">
                                    {{ $item['priority'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="h-8 w-8 rounded-full bg-gray-200 border border-white flex items-center justify-center text-xs font-medium" title="{{ $item['resources'] }}">
                                    {{ substr($item['resources'], 0, 2) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item['start_date'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item['estimated_completion'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    {{ $item['status'] === 'In Progress' ? 'text-blue-700 bg-blue-100' :
                                      ($item['status'] === 'On Hold' ? 'text-yellow-700 bg-yellow-100' : 'text-purple-700 bg-purple-100') }}">
                                    {{ $item['status'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                No current resource allocations found.
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
        // Initialize Resource Utilization Chart
        const resourceCtx = document.getElementById('resourceUtilizationChart');
        if (resourceCtx) {
            // Check if chart already exists and destroy it
            if (window.resourceUtilizationChart instanceof Chart) {
                window.resourceUtilizationChart.destroy();
            }

            window.resourceUtilizationChart = new Chart(resourceCtx, {
                type: 'bar',
                data: @json($utilizationRatesData),
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
                            max: 100,
                            title: {
                                display: true,
                                text: 'Utilization Rate (%)'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Resource'
                            }
                        }
                    }
                }
            });
        }

        // Initialize Utilization Trend Chart
        const trendCtx = document.getElementById('utilizationTrendChart');
        if (trendCtx) {
            // Check if chart already exists and destroy it
            if (window.utilizationTrendChart instanceof Chart) {
                window.utilizationTrendChart.destroy();
            }

            window.utilizationTrendChart = new Chart(trendCtx, {
                type: 'line',
                data: @json($utilizationTrendData),
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
                                text: 'Utilization Rate (%)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Week'
                            }
                        }
                    }
                }
            });
        }

        // Initialize Utilization by Area Chart
        const areaCtx = document.getElementById('utilizationByAreaChart');
        if (areaCtx) {
            // Check if chart already exists and destroy it
            if (window.utilizationByAreaChart instanceof Chart) {
                window.utilizationByAreaChart.destroy();
            }

            window.utilizationByAreaChart = new Chart(areaCtx, {
                type: 'bar',
                data: @json($utilizationByAreaData),
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
                                text: 'Utilization Rate (%)'
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

        // Initialize Task Distribution Chart
        const taskCtx = document.getElementById('taskDistributionChart');
        if (taskCtx) {
            // Check if chart already exists and destroy it
            if (window.taskDistributionChart instanceof Chart) {
                window.taskDistributionChart.destroy();
            }

            window.taskDistributionChart = new Chart(taskCtx, {
                type: 'doughnut',
                data: @json($taskTypeDistributionData),
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
