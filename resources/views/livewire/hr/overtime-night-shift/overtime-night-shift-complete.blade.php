<div>
    <!-- Modal Principal de Horas Extras -->
    <div x-data="{ open: @entangle('showModal') }" 
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
        
        <div class="relative top-20 mx-auto p-1 w-full max-w-5xl">
            <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" 
                 x-transition:enter="transition ease-out duration-300" 
                 x-transition:enter-start="transform opacity-0 scale-95" 
                 x-transition:enter-end="transform opacity-100 scale-100" 
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="transform opacity-100 scale-100" 
                 x-transition:leave-end="transform opacity-0 scale-95">
                
                <!-- Cabeçalho da Modal -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-lg px-6 py-4 flex justify-between items-center">
                    <h3 class="text-xl font-medium text-white flex items-center">
                        <i class="fas {{ $isEditing ? 'fa-edit' : 'fa-plus-circle' }} mr-3 animate-pulse"></i>
                        {{ $isEditing ? __('messages.edit_overtime') : __('messages.add_overtime') }}
                    </h3>
                    <button type="button" wire:click="closeModal" 
                            class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Formulário -->
                <form wire:submit.prevent="save">
                    <div class="p-6 max-h-[calc(100vh-200px)]" style="overflow-y: auto; overflow-x: visible;">
                        
                        <!-- Seção 1: Informações Básicas -->
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-visible mb-6">
                            <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-user-clock text-blue-600 mr-2"></i>
                                <h3 class="text-base font-medium text-gray-700">{{ __('messages.basic_information') }}</h3>
                            </div>
                            <div class="p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <!-- Funcionário com Busca -->
                                    <div class="relative z-50" x-data="{ 
                                        open: false, 
                                        search: '{{ $employee_id ? ($employees->firstWhere('id', $employee_id)->full_name ?? '') : '' }}'
                                    }" 
                                    x-init="search = '{{ $employee_id ? ($employees->firstWhere('id', $employee_id)->full_name ?? '') : '' }}'"
                                    @click.away="open = false">
                                        <label for="employee_search" class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ __('messages.employee') }} <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input 
                                                type="text" 
                                                x-model="search"
                                                value="{{ $employee_id ? ($employees->firstWhere('id', $employee_id)->full_name ?? '') : '' }}"
                                                @click="open = true"
                                                @input="open = true"
                                                placeholder="{{ __('messages.search_employee') }}"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white pl-10"
                                            >
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-search text-gray-400"></i>
                                            </div>
                                            
                                            <!-- Dropdown -->
                                            <div 
                                                x-show="open"
                                                x-transition
                                                class="absolute z-[9999] mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
                                            >
                                                @forelse($employees as $employee)
                                                    <div 
                                                        x-show="search === '' || '{{ strtolower($employee->full_name) }}'.includes(search.toLowerCase())"
                                                        wire:click="$set('employee_id', {{ $employee->id }})"
                                                        @click="open = false; search = '{{ $employee->full_name }}'"
                                                        class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-blue-50 {{ $employee_id == $employee->id ? 'bg-blue-100' : '' }}"
                                                    >
                                                        <div class="flex items-center">
                                                            <span class="font-medium block truncate {{ $employee_id == $employee->id ? 'text-blue-600' : 'text-gray-900' }}">
                                                                {{ $employee->full_name }}
                                                            </span>
                                                        </div>
                                                        @if($employee->employee_id)
                                                        <span class="text-gray-500 text-xs ml-2">{{ $employee->employee_id }}</span>
                                                        @endif
                                                        @if($employee_id == $employee->id)
                                                        <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-blue-600">
                                                            <i class="fas fa-check"></i>
                                                        </span>
                                                        @endif
                                                    </div>
                                                @empty
                                                    <div class="py-2 pl-3 pr-9 text-gray-500 text-sm">
                                                        {{ __('messages.no_employees_found') }}
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>
                                        @error('employee_id') 
                                            <p class="mt-1 text-sm text-red-600 flex items-center">
                                                <i class="fas fa-exclamation-circle mr-1"></i>
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>

                                    <!-- Data -->
                                    <div>
                                        <label for="date" class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ __('messages.date') }} <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input type="date" wire:model.lazy="date" id="date"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white pl-10">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-calendar text-gray-400"></i>
                                            </div>
                                        </div>
                                        @error('date') 
                                            <p class="mt-1 text-sm text-red-600 flex items-center">
                                                <i class="fas fa-exclamation-circle mr-1"></i>
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>
                                </div>
                                
                                <!-- Informações do Funcionário (Turno e Salário) -->
                                @if($employee_id)
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 p-4 bg-gradient-to-r from-green-50 to-green-100 rounded-lg border border-green-200">
                                    <!-- Turno do Funcionário -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-clock text-green-600 mr-1"></i>
                                            {{ __('messages.employee_shift') }}
                                        </label>
                                        <div class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 sm:text-sm shadow-sm">
                                            @if($employee_shift_name)
                                                <span class="text-green-700 font-medium">{{ $employee_shift_name }}</span>
                                            @else
                                                <span class="text-gray-500">{{ __('messages.no_shift_assigned') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Salário do Funcionário -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-money-bill-wave text-green-600 mr-1"></i>
                                            {{ __('messages.employee_salary') }}
                                        </label>
                                        <div class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 sm:text-sm shadow-sm">
                                            @if($employee_salary > 0)
                                                <span class="text-green-700 font-medium">
                                                    {{ number_format($employee_salary, 2, ',', '.') }} AKZ
                                                </span>
                                            @else
                                                <span class="text-gray-500">{{ __('messages.no_salary_defined') }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Taxa Diária do Funcionário -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-calendar-day text-green-600 mr-1"></i>
                                            {{ __('messages.daily_rate') }}
                                        </label>
                                        <div class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 sm:text-sm shadow-sm">
                                            @if($daily_rate > 0)
                                                <span class="text-green-700 font-medium">
                                                    {{ number_format($daily_rate, 2, ',', '.') }} AKZ/dia
                                                </span>
                                            @else
                                                <span class="text-gray-500">{{ __('messages.no_rate_calculated') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Seção 2: Entrada de Dias -->
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden mb-6">
                            <div class="flex items-center bg-gradient-to-r from-purple-50 to-purple-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-calendar-days text-purple-600 mr-2"></i>
                                <h3 class="text-base font-medium text-gray-700">{{ __('messages.days_worked') }}</h3>
                            </div>
                            <div class="p-4">
                                <!-- Card explicativo -->
                                <div class="mb-4 bg-gradient-to-r from-yellow-50 to-amber-50 border border-amber-200 rounded-lg p-3">
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-lightbulb text-amber-500 mr-2"></i>
                                        <h4 class="text-sm font-medium text-amber-800">{{ __('messages.night_shift_calculation') }}</h4>
                                    </div>
                                    <p class="text-xs text-amber-700">
                                        {{ __('messages.night_shift_explanation') }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="days" class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ __('messages.number_of_days') }} <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input type="number" step="0.5" min="0.5" max="31" wire:model.lazy="days" id="days"
                                                placeholder="Ex: 7"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white pl-10">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-calendar-alt text-gray-400"></i>
                                            </div>
                                            <div wire:loading wire:target="days" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                                <svg class="animate-spin h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        @error('days') 
                                            <p class="mt-1 text-sm text-red-600 flex items-center">
                                                <i class="fas fa-exclamation-circle mr-1"></i>
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ __('messages.daily_rate') }}
                                        </label>
                                        <div class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 px-3 py-2 sm:text-sm shadow-sm">
                                            @if($daily_rate > 0)
                                                <span class="text-gray-700 font-medium">
                                                    {{ number_format($daily_rate, 2, ',', '.') }} AKZ/dia
                                                </span>
                                            @else
                                                <span class="text-gray-500">{{ __('messages.select_employee_first') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Seção 4: Resultados dos Cálculos -->
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden mb-6">
                            <div class="flex items-center bg-gradient-to-r from-yellow-50 to-yellow-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-calculator text-yellow-600 mr-2"></i>
                                <h3 class="text-base font-medium text-gray-700">{{ __('messages.calculation_result') }}</h3>
                            </div>
                            
                            <!-- Alerta de Limites Legais -->
                            @error('legal_limit')
                            <div class="p-4 mb-4 bg-red-50 border-l-4 border-red-500">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-exclamation-triangle text-red-600 text-lg"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800">{{ __('messages.legal_limits_warning') }}</h3>
                                        <div class="mt-1 text-sm text-red-700">
                                            {{ $message }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @enderror
                            
                            <div class="p-4">
                                <!-- Valor Total -->
                                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4 border border-green-200 shadow-lg mb-6">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <p class="text-xs text-green-600 font-semibold uppercase tracking-wide mb-1">{{ __('messages.total_amount') }}</p>
                                            <div wire:loading wire:target="days" class="flex items-center text-green-600">
                                                <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                </svg>
                                                {{ __('messages.calculating') }}...
                                            </div>
                                            <div wire:loading.remove wire:target="days">
                                                <p class="text-3xl font-bold text-green-700">
                                                    {{ number_format($amount ?? 0, 2) }}
                                                </p>
                                                <p class="text-xs text-green-600 mt-1">Kwanzas (KZ)</p>
                                            </div>
                                        </div>
                                        <div class="ml-4 bg-green-200 rounded-full p-3">
                                            <i class="fas fa-money-bill-wave text-green-700 text-2xl"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Breakdown Detalhado dos Cálculos -->
                                @if($days > 0 && $employee_id)
                                <div x-data="{ showDetails: false }" class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-xl p-5 border border-purple-200 mb-4">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center">
                                            <div class="bg-purple-200 rounded-full p-2 mr-3">
                                                <i class="fas fa-chart-line text-purple-700"></i>
                                            </div>
                                            <h4 class="text-sm font-bold text-purple-900">{{ __('messages.calculation_breakdown') }}</h4>
                                        </div>
                                        <button 
                                            type="button"
                                            @click="showDetails = !showDetails"
                                            class="flex items-center justify-center w-8 h-8 rounded-full bg-purple-600 hover:bg-purple-700 text-white transition-all duration-200 transform hover:scale-110"
                                            :title="showDetails ? 'Ocultar detalhes' : 'Mostrar detalhes'"
                                        >
                                            <i class="fas fa-question text-sm"></i>
                                        </button>
                                    </div>
                                    
                                    <div x-show="showDetails" x-collapse class="space-y-3">
                                        <!-- Dias Trabalhados -->
                                        <div class="flex items-center justify-between bg-white bg-opacity-60 rounded-lg p-3">
                                            <div class="flex items-center">
                                                <i class="fas fa-calendar-day text-blue-600 mr-2"></i>
                                                <span class="text-sm text-gray-700">{{ __('messages.days_worked') }}:</span>
                                            </div>
                                            <span class="text-sm font-bold text-gray-900">{{ number_format($days ?? 0, 1) }} {{ __('messages.days') }}</span>
                                        </div>

                                        <!-- Taxa Diária -->
                                        <div class="flex items-center justify-between bg-white bg-opacity-60 rounded-lg p-3">
                                            <div class="flex items-center">
                                                <i class="fas fa-coins text-yellow-600 mr-2"></i>
                                                <span class="text-sm text-gray-700">{{ __('messages.daily_rate') }}:</span>
                                            </div>
                                            <span class="text-sm font-bold text-gray-900">{{ number_format($daily_rate ?? 0, 2) }} KZ/dia</span>
                                        </div>

                                        <!-- Subtotal -->
                                        <div class="flex items-center justify-between bg-blue-100 rounded-lg p-3">
                                            <div class="flex items-center">
                                                <i class="fas fa-calculator text-blue-600 mr-2"></i>
                                                <span class="text-sm font-semibold text-blue-900">{{ __('messages.subtotal') }}:</span>
                                            </div>
                                            <span class="text-sm font-bold text-blue-900">
                                                {{ number_format(($days ?? 0) * ($daily_rate ?? 0), 2) }} KZ
                                                <span class="text-xs text-blue-600 ml-1">({{ number_format($days ?? 0, 1) }} × {{ number_format($daily_rate ?? 0, 2) }})</span>
                                            </span>
                                        </div>

                                        <!-- Percentagem Night Shift (20%) -->
                                        <div class="flex items-center justify-between bg-purple-100 rounded-lg p-3">
                                            <div class="flex items-center">
                                                <i class="fas fa-moon text-purple-600 mr-2"></i>
                                                <span class="text-sm font-semibold text-purple-900">{{ __('messages.night_shift_bonus') }} (20%):</span>
                                            </div>
                                            <span class="text-sm font-bold text-purple-900">
                                                {{ number_format($amount ?? 0, 2) }} KZ
                                            </span>
                                        </div>

                                        <!-- Info Card explicativo -->
                                        <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-3">
                                            <div class="flex items-center text-xs text-indigo-700">
                                                <i class="fas fa-info-circle mr-2"></i>
                                                <span>{{ __('messages.night_shift_payment_info') }}</span>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Seção 5: Descrição -->
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden mb-6">
                            <div class="flex items-center bg-gradient-to-r from-gray-50 to-gray-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-file-alt text-gray-600 mr-2"></i>
                                <h3 class="text-base font-medium text-gray-700">{{ __('messages.additional_information') }}</h3>
                            </div>
                            <div class="p-4">
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.description') }}
                                </label>
                                <textarea wire:model.defer="description" id="description" rows="3"
                                    placeholder="{{ __('messages.enter_description') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white resize-none"></textarea>
                                @error('description') 
                                    <p class="mt-1 text-sm text-red-600 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Rodapé da Modal -->
                    <div class="bg-gray-50 px-6 py-4 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                        <button type="button" wire:click="closeModal" 
                            class="inline-flex justify-center items-center px-6 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                            <i class="fas fa-times mr-2"></i>
                            {{ __('messages.cancel') }}
                        </button>
                        <button type="submit" wire:loading.attr="disabled" 
                            class="inline-flex justify-center items-center px-6 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="save">
                                <i class="fas {{ $isEditing ? 'fa-save' : 'fa-plus-circle' }} mr-2"></i>
                                {{ $isEditing ? __('messages.update') : __('messages.save') }}
                            </span>
                            <span wire:loading wire:target="save" class="inline-flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                {{ __('messages.processing') }}
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
