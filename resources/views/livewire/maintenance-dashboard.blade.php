<div>
    <h2 class="text-2xl font-semibold mb-6">Dashboard Overview</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <!-- Metrics Overview -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <h3 class="text-lg font-medium mb-4">Metrics Overview</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <div class="metrics-card">
                <div class="icon purple">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="number">{{ $scheduledTasks }}</div>
                <div class="text-sm text-gray-500">Total Maintenance Plans</div>
            </div>

            <div class="metrics-card">
                <div class="icon red">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="number">{{ $overdueTasks }}</div>
                <div class="text-sm text-gray-500">Overdue Tasks</div>
            </div>

            <div class="metrics-card">
                <div class="icon yellow">
                    <i class="fas fa-tools"></i>
                </div>
                <div class="number">{{ $equipmentInMaintenance }}</div>
                <div class="text-sm text-gray-500">Equipment in Maintenance</div>
            </div>



            <div class="metrics-card">
                <div class="icon red">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="number">{{ $equipmentOutOfService }}</div>
                <div class="text-sm text-gray-500">Equipment Out of Service</div>
            </div>


            <div class="metrics-card">
                <div class="icon blue">
                    <i class="fas fa-wrench"></i>
                </div>
                <div class="number">{{ $equipmentCount }}</div>
                <div class="text-sm text-gray-500">Total Equipment</div>
            </div>




        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Maintenance Alerts -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-medium mb-4">Maintenance Alerts</h3>

            @forelse($maintenanceAlerts as $alert)
                <div class="alert-item flex justify-between items-center">
                    <div>
                        <div class="font-medium">{{ $alert['title'] }}</div>
                        <div class="text-sm text-gray-500">{{ $alert['description'] }}</div>
                        <div class="text-xs text-gray-400 mt-1">•</div>
                    </div>
                    <div class="flex items-center">
                        <span class="overdue-badge mr-3">Overdue</span>
                        <button class="text-green-500 hover:text-green-700">
                            <i class="fas fa-check-circle"></i>
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center text-gray-500 py-4">
                    No maintenance alerts found
                </div>
            @endforelse
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-medium mb-4">Quick Actions</h3>

            <div class="grid grid-cols-2 gap-4">
                <a href="#" class="action-card">
                    <div class="icon blue">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="title">Schedule Maintenance</div>
                    <div class="subtitle">Create a new maintenance task</div>
                </a>

                <a href="#" class="action-card">
                    <div class="icon green">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div class="title">Add Equipment</div>
                    <div class="subtitle">Register new equipment</div>
                </a>

                <a href="#" class="action-card">
                    <div class="icon purple">
                        <i class="fas fa-tag"></i>
                    </div>
                    <div class="title">Add Category</div>
                    <div class="subtitle">Create a new category</div>
                </a>

                <a href="#" class="action-card">
                    <div class="icon orange">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="title">Add Department</div>
                    <div class="subtitle">Create a new department</div>
                </a>
            </div>
        </div>
    </div>
</div>
