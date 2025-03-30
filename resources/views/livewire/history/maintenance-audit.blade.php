<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Maintenance Audit Log</h1>

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
                <label class="block text-sm font-medium text-gray-700 mb-1">User</label>
                <select wire:model.live="userId" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Equipment Filter -->
            <div class="w-full md:w-1/4 px-2 mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Equipment</label>
                <select wire:model.live="equipmentId" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="">All Equipment</option>
                    @foreach($equipment as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Action Type Filter -->
            <div class="w-full md:w-1/4 px-2 mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Action Type</label>
                <select wire:model.live="actionType" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="all">All Actions</option>
                    @foreach($actionTypes as $value => $label)
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
                    <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Search logs..." class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 pl-10">
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
        <!-- Total Actions -->
        <div class="bg-white p-4 rounded-lg shadow flex items-center">
            <div class="rounded-full p-3 bg-blue-100 mr-4">
                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Actions</p>
                <p class="text-xl font-semibold">{{ number_format($totalActions) }}</p>
            </div>
        </div>

        <!-- Critical Actions -->
        <div class="bg-white p-4 rounded-lg shadow flex items-center">
            <div class="rounded-full p-3 bg-red-100 mr-4">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Critical Actions</p>
                <p class="text-xl font-semibold">{{ number_format($criticalActions) }}</p>
            </div>
        </div>

        <!-- Most Active User -->
        <div class="bg-white p-4 rounded-lg shadow flex items-center">
            <div class="rounded-full p-3 bg-green-100 mr-4">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Most Active User</p>
                <p class="text-xl font-semibold">{{ $mostActiveUser ? $mostActiveUser->name : 'N/A' }}</p>
            </div>
        </div>

        <!-- Most Serviced Equipment -->
        <div class="bg-white p-4 rounded-lg shadow flex items-center">
            <div class="rounded-full p-3 bg-purple-100 mr-4">
                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Most Serviced Equipment</p>
                <p class="text-xl font-semibold">{{ $mostServicedEquipment ? $mostServicedEquipment->name : 'N/A' }}</p>
            </div>
        </div>
    </div>

    <!-- Audit Log Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="flex justify-between items-center p-4 border-b">
            <h2 class="text-lg font-semibold text-gray-800">Audit Log Entries</h2>
            <button wire:click="exportAuditLogs" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 flex items-center">
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
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Equipment</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($auditLogs as $log)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $log->created_at->format('M d, Y H:i:s') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                            <span class="text-gray-600 font-medium">{{ $log->user ? substr($log->user->name, 0, 2) : 'NA' }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $log->user ? $log->user->name : 'Unknown User' }}</div>
                                        <div class="text-sm text-gray-500">{{ $log->user ? $log->user->email : '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if(in_array($log->action_type, ['equipment_status_changed', 'downtime_recorded', 'part_replaced']))
                                        bg-red-100 text-red-800
                                    @elseif(in_array($log->action_type, ['task_completed', 'checklist_completed']))
                                        bg-green-100 text-green-800
                                    @else
                                        bg-blue-100 text-blue-800
                                    @endif
                                ">
                                    {{ $actionTypes[$log->action_type] ?? $log->action_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $log->equipment ? $log->equipment->name : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-md truncate">
                                {{ $log->description }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button type="button" class="text-blue-600 hover:text-blue-900" onclick="showLogDetails('{{ $log->id }}')">
                                    View
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                No audit logs found with the current filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t">
            {{ $auditLogs->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showLogDetails(logId) {
        // This would be implemented to show a modal with full log details
        alert('Log details for ID: ' + logId + ' will be shown in a modal in the future implementation.');
    }
</script>
@endpush
