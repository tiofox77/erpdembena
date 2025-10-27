{{-- Employee Search Modal --}}
@if($showEmployeeSearch)
<div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl mx-4 max-h-[80vh] overflow-hidden">
        
        {{-- Header --}}
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-white/20 p-2 rounded-lg">
                        <i class="fas fa-search text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold">{{ __('messages.search_employee') }}</h2>
                        <p class="text-blue-100">{{ __('messages.search_employee_description') }}</p>
                    </div>
                </div>
                <button wire:click="closeEmployeeSearch" class="text-white/80 hover:text-white p-2">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        {{-- Search Content --}}
        <div class="p-6">
            {{-- Search Input --}}
            <div class="mb-6">
                <div class="relative">
                    <input type="text" 
                           wire:model.live.debounce.300ms="employeeSearch"
                           placeholder="{{ __('messages.search_employee_by_name_id_email') }}"
                           class="w-full pl-12 pr-4 py-4 text-lg border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                           autofocus>
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 text-lg"></i>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-2">{{ __('messages.type_at_least_2_characters') }}</p>
            </div>

            {{-- Search Results --}}
            <div class="max-h-96 overflow-y-auto">
                @if($searchResults && $searchResults->count() > 0)
                    <div class="space-y-3">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-users text-blue-500 mr-2"></i>
                            {{ __('messages.employees') }} 
                            <span class="ml-2 px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                                {{ $totalResults }}
                            </span>
                        </h3>
                        
                        {{-- Employee Cards --}}
                        @foreach($searchResults as $employee)
                            <div wire:click="selectEmployee({{ $employee->id }})"
                                 class="bg-white border border-gray-200 hover:border-blue-300 hover:shadow-lg p-4 rounded-xl cursor-pointer transition-all duration-300 hover:scale-[1.01]">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        {{-- Employee Avatar --}}
                                        <div class="relative">
                                            <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg">
                                                {{ substr($employee->full_name, 0, 1) }}
                                            </div>
                                            {{-- Status indicator --}}
                                            <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white
                                                {{ $employee->employment_status === 'active' ? 'bg-green-500' : 'bg-yellow-500' }}">
                                            </div>
                                        </div>
                                        
                                        {{-- Employee Info --}}
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2 mb-1">
                                                <h4 class="text-lg font-semibold text-gray-900">{{ $employee->full_name }}</h4>
                                                <span class="px-2 py-1 text-xs font-medium rounded-full 
                                                    {{ $employee->employment_status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                    {{ __("messages.{$employee->employment_status}") }}
                                                </span>
                                            </div>
                                            
                                            {{-- Employee Details --}}
                                            <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 text-sm text-gray-600">
                                                <span class="flex items-center">
                                                    <i class="fas fa-id-badge text-blue-500 mr-1.5 w-4"></i>
                                                    {{ $employee->id_card ?? 'N/A' }}
                                                </span>
                                                <span class="flex items-center">
                                                    <i class="fas fa-building text-green-500 mr-1.5 w-4"></i>
                                                    {{ $employee->department->name ?? 'N/A' }}
                                                </span>
                                                <span class="flex items-center">
                                                    <i class="fas fa-envelope text-blue-500 mr-1.5 w-4"></i>
                                                    {{ Str::limit($employee->email, 25) ?? 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- Select Arrow --}}
                                    <div class="ml-4">
                                        <i class="fas fa-chevron-right text-blue-500 text-lg"></i>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @elseif(strlen($employeeSearch) >= 2)
                    <div class="text-center py-8">
                        <div class="text-gray-400 mb-4">
                            <i class="fas fa-search text-4xl"></i>
                        </div>
                        <p class="text-gray-600 text-lg">{{ __('messages.no_employees_found') }}</p>
                        <p class="text-gray-500 text-sm">{{ __('messages.try_different_search_terms') }}</p>
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="text-blue-400 mb-4">
                            <i class="fas fa-search text-4xl"></i>
                        </div>
                        <p class="text-gray-600 text-lg">{{ __('messages.start_typing_to_search') }}</p>
                        <p class="text-gray-500 text-sm">{{ __('messages.type_at_least_2_characters_to_start_searching') }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Footer --}}
        <div class="bg-gray-50 px-6 py-4 flex justify-end border-t border-gray-200">
            <button wire:click="closeEmployeeSearch" 
                    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                {{ __('messages.cancel') }}
            </button>
        </div>
    </div>
</div>
@endif
