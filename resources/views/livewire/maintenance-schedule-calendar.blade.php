<div class="bg-white rounded-lg shadow p-4">
    <!-- Calendar Header -->
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold text-gray-900">{{ $calendarTitle }}</h2>
        <div class="flex space-x-2">
            <button wire:click="previousMonth" class="p-2 bg-gray-100 rounded-full hover:bg-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            <button wire:click="resetToday" class="p-2 bg-blue-100 text-blue-800 font-medium rounded-md hover:bg-blue-200">
                Today
            </button>
            <button wire:click="nextMonth" class="p-2 bg-gray-100 rounded-full hover:bg-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="flex flex-wrap justify-between items-center mb-4 p-2 bg-gray-50 rounded-lg">
        <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 w-full">
            <!-- Filtro de Status do Plano -->
            <div class="w-full sm:w-1/2">
                <label for="planStatusFilter" class="block text-sm font-medium text-gray-700 mb-1">{{ trans('calendar_filters.plan_status') }}</label>
                <select id="planStatusFilter" wire:model="planStatusFilter" wire:change="updatePlanStatusFilter($event.target.value)" 
                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="all">{{ trans('calendar_filters.all_statuses') }}</option>
                    <option value="pending">{{ trans('calendar_filters.pending') }}</option>
                    <option value="in-progress">{{ trans('calendar_filters.in_progress') }}</option>
                    <option value="completed">{{ trans('calendar_filters.completed') }}</option>
                    <option value="cancelled">{{ trans('calendar_filters.cancelled') }}</option>
                </select>
            </div>
            
            <!-- Filtro de Status das Notas -->
            <div class="w-full sm:w-1/2">
                <label for="noteStatusFilter" class="block text-sm font-medium text-gray-700 mb-1">{{ trans('calendar_filters.note_status') }}</label>
                <select id="noteStatusFilter" wire:model="noteStatusFilter" wire:change="updateNoteStatusFilter($event.target.value)" 
                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="all">{{ trans('calendar_filters.all_statuses') }}</option>
                    <option value="pending">{{ trans('calendar_filters.pending') }}</option>
                    <option value="in-progress">{{ trans('calendar_filters.in_progress') }}</option>
                    <option value="completed">{{ trans('calendar_filters.completed') }}</option>
                    <option value="cancelled">{{ trans('calendar_filters.cancelled') }}</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="flex flex-wrap justify-start gap-4 mb-4 text-xs">
        <div class="flex items-center">
            <div class="w-3 h-3 bg-red-100 rounded-full mr-1"></div>
            <span>Holiday</span>
        </div>
        <div class="flex items-center">
            <div class="w-3 h-3 bg-gray-200 rounded-full mr-1"></div>
            <span>Sunday (Rest Day)</span>
        </div>
        <div class="flex items-center">
            <div class="w-3 h-3 bg-blue-500 rounded-full mr-1"></div>
            <span>Today</span>
        </div>
    </div>

    <!-- Days of Week -->
    <div class="grid grid-cols-7 gap-1 mb-2">
        <div class="text-center text-sm font-medium text-red-600">Sun</div>
        <div class="text-center text-sm font-medium text-gray-600">Mon</div>
        <div class="text-center text-sm font-medium text-gray-600">Tue</div>
        <div class="text-center text-sm font-medium text-gray-600">Wed</div>
        <div class="text-center text-sm font-medium text-gray-600">Thu</div>
        <div class="text-center text-sm font-medium text-gray-600">Fri</div>
        <div class="text-center text-sm font-medium text-gray-600">Sat</div>
    </div>

    <!-- Calendar Days -->
    <div class="grid grid-cols-7 gap-1">
        @foreach($calendarDays as $day)
            <div
                wire:click="selectDate('{{ $day['date'] }}')"
                class="h-24 p-1 border rounded-md
                      {{ $day['isCurrentMonth'] ? 'bg-white' : 'bg-gray-50 text-gray-500' }}
                      {{ $day['isToday'] ? 'ring-2 ring-blue-500' : '' }}
                      {{ $day['isWeekend'] ? 'bg-gray-200 text-red-600' : '' }}
                      {{ $day['isHoliday'] ? 'bg-red-100' : '' }}
                      {{ $day['date'] === $selectedDate ? 'bg-blue-50 border-blue-300' : '' }}
                      hover:bg-gray-50 transition-colors cursor-pointer"
            >
                <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-medium">{{ $day['day'] }}</span>
                    <div class="flex gap-1">
                        @if($day['isToday'])
                            <span class="text-xs px-1 rounded-full bg-blue-500 text-white">today</span>
                        @endif
                        @if($day['isHoliday'])
                            <span class="text-xs px-1 py-0.5 rounded-full bg-red-500 text-white" title="{{ $day['holidayTitle'] }}">🎉</span>
                        @endif
                        @if($day['isWeekend'])
                            <span class="text-xs px-1 py-0.5 rounded-full bg-gray-400 text-white" title="Sunday - Rest Day">😴</span>
                        @endif
                    </div>
                </div>

                <!-- Day Events -->
                <div class="space-y-1 overflow-y-auto max-h-16">
                    @if(isset($events[$day['date']]))
                        @foreach($events[$day['date']] as $event)
                            <div
                                wire:click.stop="editEvent({{ $event['id'] }})"
                                class="text-xs p-1 rounded truncate cursor-pointer
                                    {{ $event['color'] }}
                                    {{ $event['status'] === 'completed' ? 'opacity-60' : '' }}
                                    {{ $event['status'] === 'cancelled' ? 'opacity-40 line-through' : '' }}
                                    hover:bg-opacity-90"
                                title="{{ $event['title'] }} - {{ $event['equipment'] }}{{ isset($event['frequency']) && $event['frequency'] != 'once' ? ' (' . ucfirst($event['frequency']) . ')' : '' }}"
                            >
                                {{ Str::limit($event['title'], 18) }}
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Selected Date Events List -->
    <div class="mt-4 pt-3 border-t">
        <div class="flex justify-between items-center mb-2">
            <h3 class="text-md font-medium text-gray-900">
                Tasks for {{ Carbon\Carbon::parse($selectedDate)->format('m/d/Y') }}
                @if(Carbon\Carbon::parse($selectedDate)->isSunday())
                    <span class="text-xs px-2 py-1 ml-2 rounded-full bg-gray-200 text-gray-700">Sunday (Rest Day)</span>
                @endif
                @foreach($calendarDays as $day)
                    @if($day['date'] === $selectedDate && $day['isHoliday'])
                        <span class="text-xs px-2 py-1 ml-2 rounded-full bg-red-100 text-red-700">Holiday: {{ $day['holidayTitle'] }}</span>
                    @endif
                @endforeach
            </h3>

            <button
                wire:click="createEvent"
                class="px-3 py-1 text-xs font-medium text-white bg-blue-600 rounded hover:bg-blue-700 flex items-center"
            >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Task
            </button>
        </div>

        @if(empty($selectedDateEvents))
            <p class="text-sm text-gray-500 italic">No tasks scheduled for this date</p>
        @else
            <div class="space-y-2">
                @foreach($selectedDateEvents as $event)
                    <div wire:click="editEvent({{ $event['id'] }})" class="p-2 border rounded-md hover:bg-gray-50 cursor-pointer transition-colors">
                        <div class="flex justify-between">
                            <h4 class="font-medium text-gray-900">{{ $event['title'] }}</h4>
                            <span class="px-2 py-0.5 text-xs rounded-full {{ $event['color'] }}">
                                {{ ucfirst($event['status']) }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600">{{ $event['equipment'] }}
                            @if(isset($event['frequency']) && $event['frequency'] != 'once')
                               <span class="text-xs ml-2 text-blue-600">({{ ucfirst($event['frequency']) }})</span>
                            @endif
                        </p>
                        @if($event['description'])
                            <p class="text-xs text-gray-500 mt-1">{{ Str::limit($event['description'], 100) }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>