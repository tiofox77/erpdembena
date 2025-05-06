<div class="bg-white rounded-lg shadow p-4">
    <!-- Calendar Header -->
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold text-gray-900">{{ $calendarTitle }}</h2>
        <div class="flex items-center space-x-2">
            <!-- PDF Generation Button -->
            <button wire:click="generatePdf" class="p-2 bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition-colors flex items-center">
                <i class="fas fa-file-pdf mr-1"></i>
                {{ __('messages.export_to_pdf') }}
            </button>
            <button wire:click="previousMonth" class="p-2 bg-gray-100 rounded-full hover:bg-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            <button wire:click="resetToday" class="p-2 bg-blue-100 text-blue-800 font-medium rounded-md hover:bg-blue-200">
                {{ __('messages.today') }}
            </button>
            <button wire:click="nextMonth" class="p-2 bg-gray-100 rounded-full hover:bg-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Legend -->
    <div class="flex flex-wrap justify-start gap-4 mb-4 text-xs">
        <div class="flex items-center">
            <div class="w-3 h-3 bg-red-100 rounded-full mr-1"></div>
            <span>{{ __('messages.holiday') }}</span>
        </div>
        <div class="flex items-center">
            <div class="w-3 h-3 bg-gray-200 rounded-full mr-1"></div>
            <span>{{ __('messages.weekend') }}</span>
        </div>
        <div class="flex items-center">
            <div class="w-3 h-3 bg-blue-500 rounded-full mr-1"></div>
            <span>{{ __('messages.today') }}</span>
        </div>
        <div class="flex items-center">
            <div class="w-3 h-3 bg-red-100 rounded-full mr-1"></div>
            <span>{{ __('messages.open') }}</span>
        </div>
        <div class="flex items-center">
            <div class="w-3 h-3 bg-yellow-100 rounded-full mr-1"></div>
            <span>{{ __('messages.in_progress') }}</span>
        </div>
        <div class="flex items-center">
            <div class="w-3 h-3 bg-green-100 rounded-full mr-1"></div>
            <span>{{ __('messages.resolved') }}</span>
        </div>
    </div>

    <!-- Days of Week -->
    <div class="grid grid-cols-7 gap-1 mb-2">
        <div class="text-center text-sm font-medium text-red-600">{{ __('messages.sun') }}</div>
        <div class="text-center text-sm font-medium text-gray-600">{{ __('messages.mon') }}</div>
        <div class="text-center text-sm font-medium text-gray-600">{{ __('messages.tue') }}</div>
        <div class="text-center text-sm font-medium text-gray-600">{{ __('messages.wed') }}</div>
        <div class="text-center text-sm font-medium text-gray-600">{{ __('messages.thu') }}</div>
        <div class="text-center text-sm font-medium text-gray-600">{{ __('messages.fri') }}</div>
        <div class="text-center text-sm font-medium text-gray-600">{{ __('messages.sat') }}</div>
    </div>

    <!-- Calendar Days -->
    <div class="grid grid-cols-7 gap-1">
        @foreach($calendarDays as $day)
            <div
                wire:click="selectDate('{{ $day['date'] }}')"
                class="h-32 p-1 border rounded-md
                      {{ $day['isCurrentMonth'] ? 'bg-white' : 'bg-gray-50 text-gray-500' }}
                      {{ $day['isToday'] ? 'ring-2 ring-blue-500' : '' }}
                      {{ $day['isWeekend'] ? 'bg-gray-200 text-red-600' : '' }}
                      {{ $day['isHoliday'] ? 'bg-red-100' : '' }}
                      {{ $day['date'] === $selectedDate ? 'bg-blue-50 border-blue-300' : '' }}
                      hover:bg-gray-50 transition-colors cursor-pointer"
            >
                <div class="flex justify-between items-start">
                    <span class="text-sm {{ $day['isWeekend'] ? 'text-red-600' : 'text-gray-700' }} font-medium">
                        {{ $day['day'] }}
                    </span>
                    @if($day['isHoliday'])
                        <span class="text-xs text-red-600 bg-red-50 px-1 rounded" title="{{ __('messages.holiday') }}">
                            <i class="fas fa-star"></i>
                        </span>
                    @endif
                </div>

                <!-- Events for this day -->
                <div class="mt-1 space-y-1 overflow-y-auto text-xs" style="max-height: calc(100% - 20px);">
                    @if(isset($events[$day['date']]))
                        @foreach($events[$day['date']] as $event)
                            <div class="text-xs p-1 rounded-sm {{ $event['color'] }}" title="{{ $event['description'] }}">
                                <div class="font-medium truncate">{{ $event['title'] }}</div>
                                <div class="text-xs flex justify-between">
                                    <span>{{ $event['start_time'] }}</span>
                                    <span class="capitalize">{{ $event['status'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Selected Date Events -->
    <div class="mt-4 pt-3 border-t">
        <div class="flex justify-between items-center mb-2">
            <h3 class="text-md font-medium text-gray-900">
                {{ __('messages.events_for') }} {{ Carbon\Carbon::parse($selectedDate)->format(__('messages.date_format', ['default' => 'm/d/Y'])) }}
                @if(Carbon\Carbon::parse($selectedDate)->isSunday())
                    <span class="text-xs text-red-600">({{ __('messages.sunday') }})</span>
                @endif
                @if(isset($holidays[$selectedDate]))
                    <span class="text-xs text-red-600">({{ $holidays[$selectedDate] }})</span>
                @endif
            </h3>
        </div>

        @if(count($selectedDateEvents) > 0)
            <div class="space-y-2">
                @foreach($selectedDateEvents as $event)
                    <div class="p-2 rounded-md {{ $event['color'] }} shadow-sm">
                        <div class="flex justify-between items-center">
                            <div class="font-medium">{{ $event['title'] }}</div>
                            <div class="text-xs px-2 py-0.5 rounded-full capitalize
                                    {{ $event['status'] === 'open' ? 'bg-red-200 text-red-800' :
                                       ($event['status'] === 'in_progress' ? 'bg-yellow-200 text-yellow-800' :
                                       ($event['status'] === 'resolved' ? 'bg-green-200 text-green-800' :
                                       'bg-gray-200 text-gray-800')) }}">
                                {{ $event['status'] }}
                            </div>
                        </div>
                        <div class="text-sm mt-1">{{ $event['description'] }}</div>
                        <div class="flex justify-between items-center mt-1 text-xs">
                            <span><i class="far fa-clock mr-1"></i>{{ $event['start_time'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4 text-gray-500">
                <i class="far fa-calendar-times mr-1"></i> {{ __('messages.no_events') }}
            </div>
        @endif
    </div>
</div>
