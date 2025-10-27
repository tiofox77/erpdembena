@if($recentTasks->count() > 0)
    <div class="space-y-3">
        @foreach($recentTasks as $task)
            <div class="border border-gray-200 rounded-md p-3 bg-white hover:bg-green-50 transition-colors hover:border-green-200">
                <div class="flex items-start justify-between">
                    <div class="flex items-start space-x-3">
                        <div class="bg-green-100 text-green-600 p-2 rounded-full flex-shrink-0">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-800">{{ $task->equipment->name ?? __('messages.unknown_equipment') }}</h4>
                            <p class="text-sm text-gray-600">{{ \Illuminate\Support\Str::limit($task->notes, 40) }}</p>
                            <div class="flex items-center mt-1 text-xs text-gray-500">
                                <i class="fas fa-user mr-1"></i>
                                <span>{{ $task->user->name ?? __('messages.unknown_user') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs bg-{{ $task->status == 'completed' ? 'green' : ($task->status == 'pending' ? 'yellow' : 'blue') }}-100 
                                  text-{{ $task->status == 'completed' ? 'green' : ($task->status == 'pending' ? 'yellow' : 'blue') }}-700 
                                  px-2 py-1 rounded-full inline-block">
                            {{ __('messages.'.$task->status) }}
                        </div>
                        <div class="mt-1 text-sm font-medium text-gray-700">
                            {{ $task->completed_at ? \Carbon\Carbon::parse($task->completed_at)->format('d/m/Y') : \Carbon\Carbon::parse($task->created_at)->format('d/m/Y') }}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="text-center py-6 flex flex-col items-center">
        <div class="bg-green-50 p-3 rounded-full mb-2">
            <i class="fas fa-clipboard-check text-green-400 text-xl"></i>
        </div>
        <p class="text-gray-500">{{ __('messages.no_recent_tasks') }}</p>
    </div>
@endif
