{{-- Employee Search Modal --}}
@if($showEmployeeSearch)
<div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 max-h-[80vh] overflow-hidden">
        
        {{-- Header --}}
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-white/20 p-2 rounded-lg">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold">{{ __('messages.select_employee') }}</h2>
                        <p class="text-blue-100">{{ __('messages.search_select_employee_for_evaluation') }}</p>
                    </div>
                </div>
                <button wire:click="closeEmployeeSearch" class="text-white/80 hover:text-white p-2">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        {{-- Search Input --}}
        <div class="p-6 border-b border-gray-200">
            <div class="relative">
                <input wire:model.live.debounce.300ms="employeeSearch" 
                       type="text" 
                       placeholder="{{ __('messages.search_employee_name_or_id') }}"
                       class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       autofocus>
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
            </div>
        </div>

        {{-- Employee List --}}
        <div class="flex-1 overflow-y-auto max-h-[400px]">
            @if(count($employees) > 0)
                <div class="divide-y divide-gray-200">
                    @foreach($employees as $employee)
                        <div wire:click="selectEmployee({{ $employee->id }})" 
                             class="p-4 hover:bg-blue-50 cursor-pointer transition-colors">
                            <div class="flex items-center space-x-4">
                                <div class="h-12 w-12 bg-blue-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-medium">
                                        {{ substr($employee->full_name, 0, 1) }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                {{ $employee->full_name }}
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                {{ __('messages.id_card') }}: {{ $employee->id_card }}
                                            </p>
                                            @if($employee->department)
                                                <p class="text-xs text-gray-400">
                                                    {{ $employee->department->name }}
                                                </p>
                                            @endif
                                        </div>
                                        <div class="text-blue-500">
                                            <i class="fas fa-chevron-right"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-8 text-center">
                    @if($employeeSearch)
                        <i class="fas fa-search text-4xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('messages.no_employees_found') }}</h3>
                        <p class="text-gray-500">{{ __('messages.try_different_search_term') }}</p>
                    @else
                        <i class="fas fa-keyboard text-4xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('messages.start_typing_to_search') }}</h3>
                        <p class="text-gray-500">{{ __('messages.search_by_name_or_id_card') }}</p>
                    @endif
                </div>
            @endif
        </div>

        {{-- Footer --}}
        <div class="bg-gray-50 px-6 py-4 flex justify-end border-t border-gray-200">
            <button wire:click="closeEmployeeSearch" 
                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                {{ __('messages.cancel') }}
            </button>
        </div>
    </div>
</div>
@endif
