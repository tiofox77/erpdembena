<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Maintenance Team Performance</h1>

    <!-- Filters Section -->
    <div class="bg-white p-4 mb-6 rounded-lg shadow">
        <div class="flex flex-wrap -mx-2">
            <!-- Date Range Filter -->
            <div class="w-full md:w-1/4 px-2 mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                <select wire:model.live="dateRange" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="last-week">Last Week</option>
                    <option value="last-month">Last Month</option>
                    <option value="last-quarter">Last Quarter</option>
                    <option value="last-year">Last Year</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>

            <!-- Custom Date Range -->
            @if($dateRange === 'custom')
                <div class="w-full md:w-1/4 px-2 mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" wire:model.live="startDate" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                </div>
                <div class="w-full md:w-1/4 px-2 mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" wire:model.live="endDate" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                </div>
            @endif

            <!-- User Filter -->
            <div class="w-full md:w-1/4 px-2 mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Team Member</label>
                <select wire:model.live="userId" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="">All Team Members</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Area Filter -->
            <div class="w-full md:w-1/4 px-2 mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Area</label>
                <select wire:model.live="areaId" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="">All Areas</option>
                    @foreach($areas as $area)
                        <option value="{{ $area }}">{{ $area }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Task Type Filter -->
            <div class="w-full md:w-1/4 px-2 mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Task Type</label>
                <select wire:model.live="taskType" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="all">All Task Types</option>
                    @foreach($taskTypes as $value => $label)
                        @if($value !== 'all')
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <!-- Search Input -->
            <div class="w-full md:w-1/4 px-2 mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Search team members or tasks..." class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 pl-10">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Completed Tasks -->
        <div class="bg-white p-4 rounded-lg shadow flex items-center">
            <div class="rounded-full p-3 bg-blue-100 mr-4">
                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Completed Tasks</p>
                <p class="text-xl font-semibold">{{ number_format($totalCompletedTasks) }}</p>
            </div>
        </div>

        <!-- Task Completion Rate -->
        <div class="bg-white p-4 rounded-lg shadow flex items-center">
            <div class="rounded-full p-3 bg-green-100 mr-4">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Completion Rate</p>
                <p class="text-xl font-semibold">{{ $taskCompletionRate }}%</p>
            </div>
        </div>

        <!-- Average Task Duration -->
        <div class="bg-white p-4 rounded-lg shadow flex items-center">
            <div class="rounded-full p-3 bg-yellow-100 mr-4">
                <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Avg Task Duration</p>
                <p class="text-xl font-semibold">{{ $avgTaskDuration }} hrs</p>
            </div>
        </div>

        <!-- Top Performer -->
        <div class="bg-white p-4 rounded-lg shadow flex items-center">
            <div class="rounded-full p-3 bg-purple-100 mr-4">
                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Top Performer</p>
                <p class="text-xl font-semibold">{{ $topPerformer ? $topPerformer->name : 'N/A' }}</p>
            </div>
        </div>
    </div>

    <!-- Team Performance Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="flex justify-between items-center p-4 border-b">
            <h2 class="text-lg font-semibold text-gray-800">Team Performance Metrics</h2>
            <button wire:click="exportPerformanceData" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 flex items-center">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Export
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sort('name')">
                            Team Member
                            @if($sortField === 'name')
                                @if($sortDirection === 'asc')
                                    <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                @else
                                    <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                @endif
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sort('completed_tasks')">
                            Completed Tasks
                            @if($sortField === 'completed_tasks')
                                @if($sortDirection === 'asc')
                                    <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                @else
                                    <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                @endif
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sort('completion_rate')">
                            Completion Rate
                            @if($sortField === 'completion_rate')
                                @if($sortDirection === 'asc')
                                    <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                @else
                                    <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                @endif
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sort('avg_duration')">
                            Avg Duration (hrs)
                            @if($sortField === 'avg_duration')
                                @if($sortDirection === 'asc')
                                    <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                @else
                                    <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                @endif
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sort('overdue_tasks')">
                            Overdue Tasks
                            @if($sortField === 'overdue_tasks')
                                @if($sortDirection === 'asc')
                                    <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                @else
                                    <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                @endif
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Efficiency Rating</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($userPerformance as $member)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                            <span class="text-gray-600 font-medium">{{ substr($member->name, 0, 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $member->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $member->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $member->completed_tasks }} / {{ $member->total_tasks }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="relative pt-1">
                                    <div class="overflow-hidden h-2 text-xs flex rounded bg-gray-200">
                                        <div style="width: {{ $member->completion_rate }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center
                                            @if($member->completion_rate >= 90)
                                                bg-green-500
                                            @elseif($member->completion_rate >= 70)
                                                bg-blue-500
                                            @elseif($member->completion_rate >= 50)
                                                bg-yellow-500
                                            @else
                                                bg-red-500
                                            @endif
                                        "></div>
                                    </div>
                                    <div class="text-xs text-right mt-1">{{ $member->completion_rate }}%</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $member->avg_duration }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $member->overdue_tasks > 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $member->overdue_tasks }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @php
                                    // Calculate efficiency rating based on completion rate, average duration, and overdue tasks
                                    $efficiencyScore = 0;
                                    if ($member->total_tasks > 0) {
                                        $completionFactor = $member->completion_rate / 100;
                                        $overdueRatio = $member->total_tasks > 0 ? $member->overdue_tasks / $member->total_tasks : 0;
                                        $durationFactor = $member->avg_duration > 0 ? min(1, 3 / $member->avg_duration) : 1;

                                        $efficiencyScore = (($completionFactor * 0.5) + ($durationFactor * 0.3) - ($overdueRatio * 0.2)) * 100;
                                        $efficiencyScore = max(0, min(100, $efficiencyScore));
                                    }
                                @endphp

                                @if($efficiencyScore >= 90)
                                    <span class="px-2 py-1 text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Excellent ({{ round($efficiencyScore) }}%)</span>
                                @elseif($efficiencyScore >= 70)
                                    <span class="px-2 py-1 text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Good ({{ round($efficiencyScore) }}%)</span>
                                @elseif($efficiencyScore >= 50)
                                    <span class="px-2 py-1 text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Average ({{ round($efficiencyScore) }}%)</span>
                                @else
                                    <span class="px-2 py-1 text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Needs Improvement ({{ round($efficiencyScore) }}%)</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                No team performance data found for the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t">
            {{ $userPerformance->links() }}
        </div>
    </div>

    <!-- Improvement Areas -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="p-4 border-b">
            <h2 class="text-lg font-semibold text-gray-800">Areas for Improvement</h2>
        </div>

        <div class="p-4">
            @if(count($improvementAreas) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($improvementAreas as $area)
                        <div class="border rounded-lg p-4 bg-gray-50">
                            <div class="flex items-center mb-2">
                                <svg class="h-5 w-5 text-yellow-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <h3 class="text-md font-semibold">
                                    {{ $area['type'] === 'area' ? 'Area: ' : 'Task Type: ' }}
                                    <span class="text-gray-700">{{ ucfirst($area['name']) }}</span>
                                </h3>
                            </div>
                            <p class="text-sm text-gray-600 mb-2">
                                <span class="font-medium">Issue:</span>
                                @if($area['metric'] === 'completion_rate')
                                    Low completion rate ({{ $area['value'] }}%)
                                @else
                                    Long average duration ({{ $area['value'] }} hours)
                                @endif
                            </p>
                            <p class="text-sm text-gray-600">
                                <span class="font-medium">Recommendation:</span> {{ $area['recommendation'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-6 bg-gray-50 rounded text-center">
                    <p class="text-gray-500">No improvement areas identified for the current period. The team is performing well!</p>
                </div>
            @endif
        </div>
    </div>
</div>
