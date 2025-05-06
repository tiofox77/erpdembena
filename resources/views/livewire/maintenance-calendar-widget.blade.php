@if($upcomingMaintenance->count() > 0)
    <div class="space-y-3">
        @foreach($upcomingMaintenance as $plan)
            <div class="border border-gray-200 rounded-md p-3 bg-white hover:bg-blue-50 transition-colors hover:border-blue-200">
                <div class="flex items-start justify-between">
                    <div class="flex items-start space-x-3">
                        <div class="bg-blue-100 text-blue-600 p-2 rounded-full flex-shrink-0">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-800">{{ $plan->title }}</h4>
                            <p class="text-sm text-gray-600">{{ $plan->equipment->name ?? __('messages.no_equipment_specified') }}</p>
                            <div class="flex items-center mt-1 text-xs text-gray-500">
                                <i class="fas fa-user-hard-hat mr-1"></i>
                                <span>{{ $plan->assignedTo->name ?? __('messages.unassigned') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs bg-{{ $plan->status == 'schedule' ? 'blue' : 'yellow' }}-100 text-{{ $plan->status == 'schedule' ? 'blue' : 'yellow' }}-700 px-2 py-1 rounded-full inline-block">
                            {{ __('messages.'.$plan->status) }}
                        </div>
                        <div class="mt-1 text-sm font-medium text-gray-700">
                            {{ \Carbon\Carbon::parse($plan->next_date)->format('d/m/Y') }}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="text-center py-6 flex flex-col items-center">
        <div class="bg-blue-50 p-3 rounded-full mb-2">
            <i class="fas fa-calendar-times text-blue-400 text-xl"></i>
        </div>
        <p class="text-gray-500">{{ __('messages.no_scheduled_maintenance') }}</p>
    </div>
@endif
