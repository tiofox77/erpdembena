<div>
    <h2 class="text-lg font-medium text-gray-900 mb-4">Equipment Maintenance Timeline</h2>

    <!-- Equipment Selection -->
    <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="col-span-2">
                <label for="equipmentId" class="block text-sm font-medium text-gray-700 mb-1">Select Equipment</label>
                <select id="equipmentId" wire:model.live="equipmentId" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">-- Select Equipment --</option>
                    @foreach ($equipmentList as $item)
                        <option value="{{ $item['id'] }}">{{ $item['display_name'] }} ({{ $item['area'] }} / {{ $item['line'] }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="timelinePeriod" class="block text-sm font-medium text-gray-700 mb-1">Time Period</label>
                <select id="timelinePeriod" wire:model.live="timelinePeriod" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="month">Last Month</option>
                    <option value="quarter">Last Quarter</option>
                    <option value="year">Last Year</option>
                    <option value="all">All Time</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>

            @if ($timelinePeriod === 'custom')
            <div>
                <label for="startDate" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date" id="startDate" wire:model.live="startDate" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="endDate" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input type="date" id="endDate" wire:model.live="endDate" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            @endif
        </div>
    </div>

    @if (!empty($equipment))
    <!-- Equipment Details -->
    <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6">
            <div>
                <h3 class="text-md font-medium text-gray-700 mb-2">Equipment Details</h3>
                <div class="space-y-2">
                    <div class="flex justify-between border-b border-gray-100 pb-1">
                        <span class="text-sm font-medium text-gray-500">Name:</span>
                        <span class="text-sm text-gray-900">{{ $equipment['name'] }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-1">
                        <span class="text-sm font-medium text-gray-500">Serial Number:</span>
                        <span class="text-sm text-gray-900">{{ $equipment['serial_number'] ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-1">
                        <span class="text-sm font-medium text-gray-500">Location:</span>
                        <span class="text-sm text-gray-900">{{ $equipment['area'] }} / {{ $equipment['line'] }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-1">
                        <span class="text-sm font-medium text-gray-500">Purchase Date:</span>
                        <span class="text-sm text-gray-900">{{ $equipment['purchase_date'] }}</span>
                    </div>
                </div>
            </div>
            <div>
                <h3 class="text-md font-medium text-gray-700 mb-2">Maintenance Status</h3>
                <div class="space-y-2">
                    <div class="flex justify-between border-b border-gray-100 pb-1">
                        <span class="text-sm font-medium text-gray-500">Current Status:</span>
                        <span class="text-sm px-2 py-1 rounded-full {{ $equipment['status'] === 'operational' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ ucfirst($equipment['status'] ?? 'Unknown') }}
                        </span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-1">
                        <span class="text-sm font-medium text-gray-500">Last Maintenance:</span>
                        <span class="text-sm text-gray-900">{{ $equipment['last_maintenance'] }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-1">
                        <span class="text-sm font-medium text-gray-500">Next Scheduled:</span>
                        <span class="text-sm text-gray-900">{{ $equipment['next_maintenance'] }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-1">
                        <span class="text-sm font-medium text-gray-500">Notes:</span>
                        <span class="text-sm text-gray-900">{{ Str::limit($equipment['notes'], 50) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline -->
    <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
        <h3 class="text-md font-medium text-gray-700 mb-4">Maintenance Timeline</h3>

        @if (count($timelineEvents) > 0)
            <div class="relative">
                <!-- Timeline Line -->
                <div class="absolute h-full w-0.5 bg-gray-200 left-6 md:left-8 top-0"></div>

                <!-- Timeline Events -->
                <div class="space-y-8 relative">
                    @foreach ($timelineEvents as $event)
                        <div class="relative pl-10 md:pl-12">
                            <!-- Timeline Dot -->
                            <div class="absolute left-5 md:left-7 -translate-x-1/2 w-3 h-3 rounded-full bg-{{ $event['color_class'] }}-500 border-2 border-white shadow"></div>

                            <!-- Event Card -->
                            <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm hover:shadow transition-shadow">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="text-sm px-2 py-0.5 rounded-full bg-{{ $event['color_class'] }}-100 text-{{ $event['color_class'] }}-800">
                                        <i class="fas fa-{{ $event['icon'] }} mr-1"></i>
                                        {{ $event['type'] }}
                                    </span>
                                    <span class="text-sm text-gray-600">{{ $event['date'] }}</span>
                                </div>

                                <h4 class="text-md font-medium text-gray-900 mb-1">{{ $event['title'] }}</h4>
                                <p class="text-sm text-gray-700 mb-3">{{ Str::limit($event['description'], 100) }}</p>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2 text-sm">
                                    <div class="flex items-center">
                                        <span class="font-medium text-gray-600 mr-2">Status:</span>
                                        <span class="text-{{ $event['color_class'] }}-600">{{ $event['status'] }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="font-medium text-gray-600 mr-2">Technician:</span>
                                        <span>{{ $event['technician'] }}</span>
                                    </div>

                                    @foreach ($event['details'] as $label => $value)
                                        @if (!empty($value) && $value !== 'N/A' && $value !== 'None')
                                            <div class="col-span-1 md:col-span-2 mt-1">
                                                <span class="font-medium text-gray-600 mr-2">{{ $label }}:</span>
                                                <span>{{ $value }}</span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="text-center py-10 text-gray-500">
                <i class="fas fa-calendar-times text-3xl mb-2"></i>
                <p>No maintenance events found for this time period.</p>
                <p class="text-sm mt-2">Try selecting a different time range or equipment.</p>
            </div>
        @endif
    </div>
    @else
        <div class="bg-white p-8 rounded-lg shadow-sm text-center">
            <i class="fas fa-wrench text-4xl text-gray-400 mb-3"></i>
            <h3 class="text-lg font-medium text-gray-700 mb-2">Select Equipment to View Timeline</h3>
            <p class="text-gray-500">Choose equipment from the dropdown above to see its complete maintenance history.</p>
        </div>
    @endif
</div>
