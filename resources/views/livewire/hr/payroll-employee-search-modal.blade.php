{{-- Employee Search Modal for Payroll Processing --}}
<div>
    <div x-data="{ open: @entangle('showEmployeeSearch') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50" 
         role="dialog" 
         aria-modal="true"
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0">
        <div class="relative top-20 mx-auto p-1 w-full max-w-2xl">
            <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" 
                 x-transition:enter="transition ease-out duration-300" 
                 x-transition:enter-start="transform opacity-0 scale-95" 
                 x-transition:enter-end="transform opacity-100 scale-100" 
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="transform opacity-100 scale-100" 
                 x-transition:leave-end="transform opacity-0 scale-95">
                
                {{-- Header --}}
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-lg px-6 py-4 flex justify-between items-center">
                    <h3 class="text-xl font-medium text-white flex items-center">
                        <i class="fas fa-search mr-3 animate-pulse"></i>
                        {{ __('messages.search_employee_for_payroll') }}
                    </h3>
                    <button type="button" wire:click="closeEmployeeSearch" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>

                {{-- Body --}}
                <div class="p-6">
                    {{-- Search Input --}}
                    <div class="mb-6">
                        <label for="employee_search" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user-search text-blue-500 mr-1"></i>
                            {{ __('messages.search_employee') }}
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                id="employee_search"
                                wire:model.live.debounce.300ms="employeeSearch"
                                placeholder="{{ __('messages.type_employee_name_id') }}"
                                class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white"
                            >
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Month and Year Selection --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="selected_month" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar text-blue-500 mr-1"></i>
                                {{ __('messages.month') }}
                            </label>
                            <select 
                                id="selected_month" 
                                wire:model.live="selectedMonth"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white"
                            >
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ $selectedMonth == $i ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create(null, $i, 1)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label for="selected_year" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt text-purple-500 mr-1"></i>
                                {{ __('messages.year') }}
                            </label>
                            <select 
                                id="selected_year" 
                                wire:model.live="selectedYear"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white"
                            >
                                @for($year = (now()->year - 2); $year <= (now()->year + 1); $year++)
                                    <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    {{-- Search Results --}}
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @if(strlen($employeeSearch) >= 2)
                            @if(count($searchResults) > 0)
                                <div class="text-sm text-gray-600 mb-3 flex items-center">
                                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                    {{ count($searchResults) }} {{ __('messages.employees_found') }}
                                </div>
                                @foreach($searchResults as $employee)
                                    <div 
                                        wire:click="selectEmployee({{ $employee['id'] }})"
                                        class="bg-gradient-to-r from-gray-50 to-gray-100 border border-gray-200 rounded-lg p-4 cursor-pointer hover:from-blue-50 hover:to-blue-100 hover:border-blue-300 transition-all duration-200 transform hover:scale-[1.02]"
                                    >
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-4">
                                                <div class="flex-shrink-0">
                                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                                        {{ strtoupper(substr($employee['full_name'], 0, 1)) }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <h4 class="text-lg font-medium text-gray-900">{{ $employee['full_name'] }}</h4>
                                                    <div class="flex items-center text-sm text-gray-500 space-x-4">
                                                        <span class="flex items-center">
                                                            <i class="fas fa-id-badge text-gray-400 mr-1"></i>
                                                            BI: {{ $employee['id_card'] }}
                                                        </span>
                                                        <span class="flex items-center">
                                                            <i class="fas fa-envelope text-gray-400 mr-1"></i>
                                                            {{ $employee['email'] }}
                                                        </span>
                                                    </div>
                                                    @if(isset($employee['department_name']))
                                                        <div class="mt-1">
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                                <i class="fas fa-building text-blue-600 mr-1 text-xs"></i>
                                                                {{ $employee['department_name'] }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-chevron-right text-gray-400"></i>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-8">
                                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-user-slash text-gray-400 text-2xl"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('messages.no_employees_found') }}</h3>
                                    <p class="text-sm text-gray-500">{{ __('messages.try_different_search_term') }}</p>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-8">
                                <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-search text-blue-500 text-2xl"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('messages.start_typing_to_search') }}</h3>
                                <p class="text-sm text-gray-500">{{ __('messages.type_at_least_2_characters') }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Footer --}}
                <div class="bg-gray-50 px-6 py-4 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                    <button 
                        type="button" 
                        wire:click="closeEmployeeSearch" 
                        class="inline-flex justify-center items-center px-6 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105"
                    >
                        <i class="fas fa-times mr-2"></i>
                        {{ __('messages.cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
