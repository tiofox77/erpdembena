<div>
    {{-- Do your work, then step back. --}}
    <div class="py-4">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col">
                <h1 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-file-alt mr-3 text-indigo-600"></i> Maintenance Plan Report
                </h1>

                <!-- Filter Card -->
                <div class="bg-white p-4 rounded-lg shadow-sm mb-6 border border-gray-200">
                    <h3 class="text-lg font-medium text-gray-700 mb-4 flex items-center">
                        <i class="fas fa-filter mr-2 text-blue-500"></i> {{ __('messages.filter_options') }}
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <!-- Month Filter -->
                        <div>
                            <label for="selectedMonth" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="far fa-calendar-alt text-gray-500 mr-1"></i>
                                {{ __('messages.select_month') }}
                            </label>
                            <input 
                                type="month" 
                                id="selectedMonth" 
                                wire:model.live="selectedMonth" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                        </div>
                        
                        <!-- Status Filter -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-tasks text-gray-500 mr-1"></i>
                                {{ __('messages.status') }}
                            </label>
                            <select 
                                id="status" 
                                wire:model.live="status" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="">{{ __('messages.all_statuses') }}</option>
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Type Filter -->
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-tag text-gray-500 mr-1"></i>
                                {{ __('messages.type') }}
                            </label>
                            <select 
                                id="type" 
                                wire:model.live="type" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="">{{ __('messages.all_types') }}</option>
                                @foreach($typeOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <!-- Equipment and Task Filters (Highlighted Section) -->
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-100 mb-4">
                        <h4 class="text-sm font-medium text-blue-800 mb-3 flex items-center">
                            <i class="fas fa-search-plus mr-1"></i>
                            {{ __('messages.advanced_filters') }}
                        </h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Equipment Filter -->
                            <div>
                                <label for="equipment_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-wrench text-gray-500 mr-1"></i>
                                    {{ __('messages.equipment') }}
                                </label>
                                <select 
                                    id="equipment_id" 
                                    wire:model.live="equipment_id" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-white"
                                >
                                    <option value="">{{ __('messages.all_equipment') }}</option>
                                    @foreach($equipments as $equipment)
                                        <option value="{{ $equipment->id }}">{{ $equipment->name }} - {{ $equipment->serial_number }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Task Filter -->
                            <div>
                                <label for="task_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-clipboard-list text-gray-500 mr-1"></i>
                                    {{ __('messages.task') }}
                                </label>
                                <select 
                                    id="task_id" 
                                    wire:model.live="task_id" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-white"
                                >
                                    <option value="">{{ __('messages.all_tasks') }}</option>
                                    @foreach($tasks as $task)
                                        <option value="{{ $task->id }}">{{ $task->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <!-- Line Filter -->
                        <div>
                            <label for="line_id" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-project-diagram text-gray-500 mr-1"></i>
                                {{ __('messages.line') }}
                            </label>
                            <select 
                                id="line_id" 
                                wire:model.live="line_id" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-white"
                            >
                                <option value="">{{ __('messages.all_lines') }}</option>
                                @foreach($lines as $line)
                                    <option value="{{ $line->id }}">{{ $line->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Area Filter -->
                        <div>
                            <label for="area_id" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-map-marked-alt text-gray-500 mr-1"></i>
                                {{ __('messages.area') }}
                            </label>
                            <select 
                                id="area_id" 
                                wire:model.live="area_id" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-white"
                            >
                                <option value="">{{ __('messages.all_areas') }}</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex justify-end items-end gap-2 mt-4">
                        <button 
                            wire:click="clearFilters" 
                            class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            <i class="fas fa-times mr-1"></i> Clear Filters
                        </button>
                        
                        <button 
                            wire:click="generatePdf" 
                            wire:loading.attr="disabled"
                            class="py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 flex items-center"
                        >
                            <span wire:loading.remove wire:target="generatePdf">
                                <i class="fas fa-file-pdf mr-1"></i> Generate PDF
                            </span>
                            <span wire:loading wire:target="generatePdf" class="flex items-center">
                                <i class="fas fa-spinner fa-spin mr-1"></i> Generating...
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Results Card -->
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                    <h3 class="text-lg font-medium text-gray-700 mb-4 flex items-center">
                        <i class="fas fa-list mr-2 text-blue-500"></i> Maintenance Plans
                    </h3>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('scheduled_date')">
                                        <div class="flex items-center">
                                            Date
                                            @if ($sortField === 'scheduled_date')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                            @else
                                                <i class="fas fa-sort ml-1 text-gray-400"></i>
                                            @endif
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Task
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Equipment
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Frequency
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Type
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Assigned To
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($plans as $plan)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if(isset($plan->calculatedOccurrences) && count($plan->calculatedOccurrences) > 0)
                                                <div class="flex flex-col gap-1">
                                                    @foreach($plan->calculatedOccurrences as $index => $date)
                                                        <span class="{{ $index > 0 ? 'border-t pt-1' : '' }}">
                                                            {{ $date->format(\App\Models\Setting::getSystemDateFormat()) }}
                                                            <span class="text-xs text-gray-500 ml-1">({{ $date->translatedFormat('l') }})</span>
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-400 italic">{{ __('messages.no_occurrences') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $plan->task ? $plan->task->title : 'No Task' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $plan->equipment ? $plan->equipment->name : 'No Equipment' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @switch($plan->frequency_type)
                                                @case('once')
                                                    Once
                                                    @break
                                                @case('daily')
                                                    Daily
                                                    @break
                                                @case('weekly')
                                                    Weekly
                                                    @break
                                                @case('monthly')
                                                    Monthly
                                                    @break
                                                @case('yearly')
                                                    Yearly
                                                    @break
                                                @case('custom')
                                                    Every {{ $plan->custom_days }} days
                                                    @break
                                                @default
                                                    {{ ucfirst($plan->frequency_type) }}
                                            @endswitch
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @switch($plan->type)
                                                @case('preventive')
                                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Preventive
                                                    </span>
                                                    @break
                                                @case('predictive')
                                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                        Predictive
                                                    </span>
                                                    @break
                                                @case('conditional')
                                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                                                        Conditional
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        {{ ucfirst($plan->type) }}
                                                    </span>
                                            @endswitch
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @switch($plan->status)
                                                @case('pending')
                                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        Pending
                                                    </span>
                                                    @break
                                                @case('in_progress')
                                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                        In Progress
                                                    </span>
                                                    @break
                                                @case('completed')
                                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Completed
                                                    </span>
                                                    @break
                                                @case('schedule')
                                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                                        Schedule
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        {{ ucfirst($plan->status) }}
                                                    </span>
                                            @endswitch
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $plan->assignedTo ? $plan->assignedTo->name : 'Unassigned' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            No maintenance plans found matching your filters
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $plans->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- PDF Download Script -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('pdfGenerated', (url) => {
                // Open the PDF in a new tab
                window.open(url, '_blank');
            });
        });
    </script>
</div>
