<div>
    <h2 class="text-lg font-medium text-gray-900 mb-4">Equipment Availability Report</h2>

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
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <!-- Availability -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-blue-100 p-3 mr-4">
                    <i class="fas fa-chart-line text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Average Availability</h3>
                    <p class="text-2xl font-bold">{{ number_format($averageAvailability, 2) }}%</p>
                </div>
            </div>
        </div>

        <!-- MTBF -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-green-100 p-3 mr-4">
                    <i class="fas fa-clock text-green-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Average MTBF</h3>
                    <p class="text-2xl font-bold">{{ number_format($averageMtbf, 1) }} hrs</p>
                </div>
            </div>
        </div>

        <!-- MTTR -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="rounded-full bg-red-100 p-3 mr-4">
                    <i class="fas fa-wrench text-red-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Average MTTR</h3>
                    <p class="text-2xl font-bold">{{ number_format($averageMttr, 1) }} hrs</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Availability Chart -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <h3 class="text-md font-medium text-gray-700 mb-4">Equipment Availability</h3>
            <div class="h-80">
                <canvas id="availabilityChart" wire:ignore></canvas>
            </div>
        </div>

        <!-- MTBF & MTTR Chart -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <h3 class="text-md font-medium text-gray-700 mb-4">MTBF & MTTR by Equipment</h3>
            <div class="h-80">
                <canvas id="mtbfMttrChart" wire:ignore></canvas>
            </div>
        </div>
    </div>

    <!-- Equipment Data Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h3 class="text-md font-medium text-gray-700">Equipment Details</h3>
            <p class="text-sm text-gray-500">Total equipment: {{ $totalCount }}</p>
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
                            <button wire:click="sortBy('availability')" class="flex items-center">
                                Availability
                                @if ($sortField === 'availability')
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
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="relative w-full h-2 bg-gray-200 rounded">
                                        <div class="absolute left-0 top-0 h-2 rounded"
                                             style="width: {{ $item['availability'] }}%; background-color: {{ $item['availability'] > 95 ? '#10B981' : ($item['availability'] > 90 ? '#FBBF24' : '#EF4444') }}"></div>
                                    </div>
                                    <span class="ml-2 text-sm text-gray-700">{{ number_format($item['availability'], 2) }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($item['mtbf'], 1) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($item['mttr'], 1) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                No equipment data found.
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
        // Initialize Availability Chart
        const availabilityCtx = document.getElementById('availabilityChart');
        if (availabilityCtx) {
            // Check if chart already exists and destroy it
            if (window.availabilityChart instanceof Chart) {
                window.availabilityChart.destroy();
            }

            window.availabilityChart = new Chart(availabilityCtx, {
                type: 'bar',
                data: @json($availabilityData),
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
                                text: 'Availability (%)'
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

        // Initialize MTBF & MTTR Chart
        const mtbfMttrCtx = document.getElementById('mtbfMttrChart');
        if (mtbfMttrCtx) {
            // Check if chart already exists and destroy it
            if (window.mtbfMttrChart instanceof Chart) {
                window.mtbfMttrChart.destroy();
            }

            // Combine MTBF and MTTR data
            const mtbfMttrData = {
                labels: @json($mtbfData['labels']),
                datasets: [
                    {
                        label: 'MTBF (Hours)',
                        data: @json($mtbfData['datasets'][0]['data']),
                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1,
                        yAxisID: 'y'
                    },
                    {
                        label: 'MTTR (Hours)',
                        data: @json($mttrData['datasets'][0]['data']),
                        backgroundColor: 'rgba(255, 99, 132, 0.5)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1,
                        yAxisID: 'y1'
                    }
                ]
            };

            window.mtbfMttrChart = new Chart(mtbfMttrCtx, {
                type: 'bar',
                data: mtbfMttrData,
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
                            position: 'left',
                            title: {
                                display: true,
                                text: 'MTBF (Hours)'
                            }
                        },
                        y1: {
                            beginAtZero: true,
                            position: 'right',
                            grid: {
                                drawOnChartArea: false
                            },
                            title: {
                                display: true,
                                text: 'MTTR (Hours)'
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
