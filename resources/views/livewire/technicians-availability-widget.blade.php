@if($technicians->count() > 0)
    <div class="space-y-3">
        @foreach($technicians as $technician)
            <div class="border border-gray-200 rounded-md p-3 bg-white hover:bg-purple-50 transition-colors hover:border-purple-200">
                <div class="flex items-start justify-between">
                    <div class="flex items-start space-x-3">
                        <div class="bg-purple-100 text-purple-600 p-2 rounded-full flex-shrink-0">
                            <i class="fas fa-user-hard-hat"></i>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-800">{{ $technician->name }}</h4>
                            <p class="text-sm text-gray-600">{{ $technician->email }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs bg-{{ $technician->assigned_maintenance_plans_count > 5 ? 'red' : ($technician->assigned_maintenance_plans_count > 2 ? 'yellow' : 'green') }}-100 
                                  text-{{ $technician->assigned_maintenance_plans_count > 5 ? 'red' : ($technician->assigned_maintenance_plans_count > 2 ? 'yellow' : 'green') }}-700 
                                  px-2 py-1 rounded-full inline-block">
                            {{ $technician->assigned_maintenance_plans_count }} {{ __('messages.active_tasks') }}
                        </div>
                        <div class="mt-1 text-sm font-medium text-gray-700">
                            <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                <div class="bg-{{ $technician->assigned_maintenance_plans_count > 5 ? 'red' : ($technician->assigned_maintenance_plans_count > 2 ? 'yellow' : 'green') }}-500 h-2 rounded-full" 
                                     style="width: {{ min(($technician->assigned_maintenance_plans_count / 10) * 100, 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="text-center py-6 flex flex-col items-center">
        <div class="bg-purple-50 p-3 rounded-full mb-2">
            <i class="fas fa-user-slash text-purple-400 text-xl"></i>
        </div>
        <p class="text-gray-500">{{ __('messages.no_technicians_available') }}</p>
    </div>
@endif
