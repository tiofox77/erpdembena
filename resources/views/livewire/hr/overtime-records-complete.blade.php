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
                    <div class="p-6 max-h-[calc(100vh-200px)] overflow-y-auto">
                        
                        <!-- Seção 1: Informações Básicas -->
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden mb-6">
                            <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-user-clock text-blue-600 mr-2"></i>
                                <h3 class="text-base font-medium text-gray-700">{{ __('messages.basic_information') }}</h3>
                            </div>
                            <div class="p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <!-- Funcionário -->
                                    <div>
                                        <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ __('messages.employee') }} <span class="text-red-500">*</span>
                                        </label>
                                        <select wire:model.live="employee_id" id="employee_id" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white">
                                            <option value="">{{ __('messages.select_employee') }}</option>
                                            @foreach($employees as $employee)
                                                <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                                            @endforeach
                                        </select>
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
                                            <input type="date" wire:model.live="date" id="date"
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

                                    <!-- Taxa Horária do Funcionário -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-calculator text-green-600 mr-1"></i>
                                            {{ __('messages.hourly_rate') }}
                                        </label>
                                        <div class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 sm:text-sm shadow-sm">
                                            @if($hourly_rate > 0)
                                                <span class="text-green-700 font-medium">
                                                    {{ number_format($hourly_rate, 2, ',', '.') }} AKZ/h
                                                </span>
                                                <span class="text-xs text-gray-500 block mt-1">
                                                    ({{ __('messages.overtime_rate') }}: {{ number_format($rate, 2, ',', '.') }} AKZ/h)
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

                        <!-- Seção 2: Tipo de Entrada -->
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden mb-6">
                            <div class="flex items-center bg-gradient-to-r from-purple-50 to-purple-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-cogs text-purple-600 mr-2"></i>
                                <h3 class="text-base font-medium text-gray-700">{{ __('messages.input_type') }}</h3>
                            </div>
                            <div class="p-4">
                                <div class="flex flex-wrap gap-3">
                                    <!-- Range de Tempo -->
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" wire:model.live="input_type" value="time_range" class="sr-only">
                                        <div class="relative flex items-center px-4 py-2 rounded-lg border-2 transition-all duration-200 {{ $input_type === 'time_range' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-300 bg-white text-gray-700 hover:border-blue-300' }}">
                                            <i class="fas fa-clock mr-2 {{ $input_type === 'time_range' ? 'text-blue-600' : 'text-gray-500' }}"></i>
                                            {{ __('messages.time_range') }}
                                        </div>
                                    </label>

                                    <!-- Horas Diárias -->
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" wire:model.live="input_type" value="daily" class="sr-only">
                                        <div class="relative flex items-center px-4 py-2 rounded-lg border-2 transition-all duration-200 {{ $input_type === 'daily' ? 'border-green-500 bg-green-50 text-green-700' : 'border-gray-300 bg-white text-gray-700 hover:border-green-300' }}">
                                            <i class="fas fa-calendar-day mr-2 {{ $input_type === 'daily' ? 'text-green-600' : 'text-gray-500' }}"></i>
                                            {{ __('messages.daily_hours') }}
                                        </div>
                                    </label>

                                    <!-- Horas Mensais -->
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" wire:model.live="input_type" value="monthly" class="sr-only">
                                        <div class="relative flex items-center px-4 py-2 rounded-lg border-2 transition-all duration-200 {{ $input_type === 'monthly' ? 'border-orange-500 bg-orange-50 text-orange-700' : 'border-gray-300 bg-white text-gray-700 hover:border-orange-300' }}">
                                            <i class="fas fa-calendar-alt mr-2 {{ $input_type === 'monthly' ? 'text-orange-600' : 'text-gray-500' }}"></i>
                                            {{ __('messages.monthly_hours') }}
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Seção 3: Entrada de Dados Condicionais -->
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden mb-6">
                            <div class="flex items-center bg-gradient-to-r from-green-50 to-green-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-keyboard text-green-600 mr-2"></i>
                                <h3 class="text-base font-medium text-gray-700">{{ __('messages.time_input') }}</h3>
                            </div>
                            <div class="p-4">
                                
                                <!-- Entrada por Range de Tempo -->
                                @if($input_type === 'time_range')
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1">
                                                {{ __('messages.start_time') }} <span class="text-red-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <input type="time" wire:model.live="start_time" id="start_time"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white pl-10">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-play text-gray-400"></i>
                                                </div>
                                                <div wire:loading wire:target="start_time" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                                    <svg class="animate-spin h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            @error('start_time') 
                                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="end_time" class="block text-sm font-medium text-gray-700 mb-1">
                                                {{ __('messages.end_time') }} <span class="text-red-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <input type="time" wire:model.live="end_time" id="end_time"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white pl-10">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-stop text-gray-400"></i>
                                                </div>
                                                <div wire:loading wire:target="end_time" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                                    <svg class="animate-spin h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            @error('end_time') 
                                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                    </div>
                                @endif

                                <!-- Entrada Direta de Horas -->
                                @if($input_type === 'daily' || $input_type === 'monthly')
                                    <!-- Card explicativo do modo selecionado -->
                                    <div class="mb-4 bg-gradient-to-r from-yellow-50 to-amber-50 border border-amber-200 rounded-lg p-3 animate__animated animate__fadeIn">
                                        <div class="flex items-center mb-2">
                                            <i class="fas fa-lightbulb text-amber-500 mr-2"></i>
                                            <h4 class="text-sm font-medium text-amber-800">{{ $input_type === 'daily' ? __('messages.daily_mode_how_it_works') : __('messages.monthly_mode_how_it_works') }}</h4>
                                        </div>
                                        <p class="text-xs text-amber-700">
                                            @if($input_type === 'daily')
                                                {{ __('messages.daily_mode_explanation', ['limit' => number_format($dailyLimit, 1)]) }}
                                            @else
                                                {{ __('messages.monthly_mode_explanation', ['limit' => number_format($monthlyLimit, 1)]) }}
                                            @endif
                                        </p>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                         <div>
                                            <label for="direct_hours" class="block text-sm font-medium text-gray-700 mb-1">
                                                {{ $input_type === 'daily' ? __('messages.daily_hours') : __('messages.monthly_hours') }} <span class="text-red-500">*</span>
                                                <span class="ml-1 text-xs font-medium text-blue-600">{{ $input_type === 'daily' ? __('messages.only_overtime_hours') : __('messages.total_monthly_overtime') }}</span>
                                            </label>
                                            <div class="relative">
                                                <input type="number" step="0.01" wire:model.live="direct_hours" id="direct_hours"
                                                    placeholder="{{ $input_type === 'daily' ? __('messages.daily_example_placeholder') : __('messages.monthly_example_placeholder') }}"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white pl-10">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-hourglass-half text-gray-400"></i>
                                                </div>
                                                <div wire:loading wire:target="direct_hours" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                                    <svg class="animate-spin h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            @error('direct_hours') 
                                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                            
                                            <!-- Alerta específico para mínimo diário -->
                                            @if(session('error_hours_below_daily_minimum'))
                                                <div class="mt-2 p-2 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm">
                                                    <div class="flex items-center">
                                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                                        <p>{{ session('error_hours_below_daily_minimum') }}</p>
                                                    </div>
                                                </div>
                                            @endif
                                            
                                            <!-- Informação de limites legais em cartão temático -->
                                            <div class="mt-3 bg-blue-50 border border-blue-200 rounded-lg p-3">
                                                <div class="flex items-center mb-2">
                                                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                                    <h4 class="text-sm font-medium text-blue-800">{{ __('messages.legal_overtime_limits') }}</h4>
                                                </div>
                                                <ul class="text-xs space-y-1.5 text-blue-700">
                                                    @if($input_type === 'daily')
                                                    <li class="flex items-center">
                                                        <div class="w-3 h-3 bg-blue-200 rounded-full mr-2 flex-shrink-0"></div>
                                                        <span>{{ __('messages.maximum_daily') }}: <span class="font-medium bg-blue-100 px-1.5 py-0.5 rounded-md">{{ number_format($dailyLimit, 2) }}</span> {{ __('messages.hours') }}</span>
                                                    </li>
                                                    @endif
                                                    <li class="flex items-center">
                                                        <div class="w-3 h-3 bg-blue-300 rounded-full mr-2 flex-shrink-0"></div>
                                                        <span>{{ __('messages.maximum_monthly') }}: <span class="font-medium bg-blue-100 px-1.5 py-0.5 rounded-md">{{ number_format($monthlyLimit, 2) }}</span> {{ __('messages.hours') }}</span>
                                                    </li>
                                                    <li class="flex items-center">
                                                        <div class="w-3 h-3 bg-blue-400 rounded-full mr-2 flex-shrink-0"></div>
                                                        <span>{{ __('messages.maximum_yearly') }}: <span class="font-medium bg-blue-100 px-1.5 py-0.5 rounded-md">{{ number_format($yearlyLimit, 2) }}</span> {{ __('messages.hours') }}</span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>

                                        <div>
                                            <label for="hourly_rate" class="block text-sm font-medium text-gray-700 mb-1">
                                                {{ __('messages.hourly_rate') }} <span class="text-red-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <input type="number" step="0.01" min="0" wire:model.live="rate" id="hourly_rate"
                                                    placeholder="{{ __('messages.enter_hourly_rate') }}"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white pl-10">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-dollar-sign text-gray-400"></i>
                                                </div>
                                                <div wire:loading wire:target="rate" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                                    <svg class="animate-spin h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            @error('rate') 
                                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                    </div>
                                @endif

                                <!-- Taxa horária para range de tempo -->
                                @if($input_type === 'time_range')
                                    <div class="grid grid-cols-1 md:grid-cols-1 gap-4 mt-4">
                                        <div>
                                            <label for="hourly_rate_range" class="block text-sm font-medium text-gray-700 mb-1">
                                                {{ __('messages.hourly_rate') }} <span class="text-red-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <input type="number" step="0.01" min="0" wire:model.live="rate" id="hourly_rate_range"
                                                    placeholder="{{ __('messages.enter_hourly_rate') }}"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white pl-10">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-dollar-sign text-gray-400"></i>
                                                </div>
                                                <div wire:loading wire:target="rate" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                                    <svg class="animate-spin h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            @error('rate') 
                                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Seção 3.5: Configurações Especiais -->
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden mb-6">
                            <div class="flex items-center bg-gradient-to-r from-purple-50 to-purple-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-cogs text-purple-600 mr-2"></i>
                                <h3 class="text-base font-medium text-gray-700">{{ __('messages.special_settings') }}</h3>
                            </div>
                            <div class="p-4">
                                <!-- Toggle para turno noturno com estilo moderno -->
                                <div class="flex items-start">
                                    <div>
                                        <label for="is_night_shift" class="flex items-center cursor-pointer">
                                            <div class="relative">
                                                <!-- Input escondido -->
                                                <input type="checkbox" wire:model.live="is_night_shift" id="is_night_shift" class="sr-only">
                                                <!-- Track (fundo do toggle) -->
                                                <div class="w-12 h-6 bg-gray-300 rounded-full shadow-inner transition-all duration-300 ease-in-out"></div>
                                                <!-- Dot (bolinha do toggle) -->
                                                <div class="dot absolute w-6 h-6 bg-white rounded-full shadow left-0 top-0 transition-transform duration-300 ease-in-out transform" 
                                                    :class="{'translate-x-6 bg-purple-500': $wire.is_night_shift, 'bg-white': !$wire.is_night_shift}"></div>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="ml-3">
                                        <label for="is_night_shift" class="text-sm font-medium text-gray-700 cursor-pointer flex items-center">
                                            <i class="fas fa-moon text-purple-600 mr-2"></i>
                                            {{ __('messages.is_night_shift') }}
                                        </label>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ __('messages.night_shift_info', ['multiplier' => number_format($night_shift_multiplier ?? 1.25, 2)]) }}
                                        </p>
                                        <!-- Indicador de carregamento durante o cálculo -->
                                        <div wire:loading wire:target="is_night_shift" class="text-xs text-purple-600 flex items-center mt-1">
                                            <svg class="animate-spin h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                            </svg>
                                            {{ __('messages.recalculating') }}...
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Card informativo sobre turno noturno -->
                                <div class="mt-3 bg-purple-50 border border-purple-200 rounded-lg p-3" x-data="{ open: false }">
                                    <div class="flex items-center justify-between cursor-pointer" @click="open = !open">
                                        <div class="flex items-center">
                                            <i class="fas fa-info-circle text-purple-600 mr-2"></i>
                                            <h4 class="text-sm font-medium text-purple-800">{{ __('messages.night_shift_details') }}</h4>
                                        </div>
                                        <i class="fas" :class="{'fa-chevron-down': !open, 'fa-chevron-up': open}"></i>
                                    </div>
                                    <div x-show="open" x-transition class="mt-2 text-xs text-purple-700">
                                        <ul class="space-y-1.5">
                                            <li class="flex items-center">
                                                <div class="w-3 h-3 bg-purple-200 rounded-full mr-2 flex-shrink-0"></div>
                                                <span>{{ __('messages.night_shift_hours') }}: <span class="font-medium">22:00 - 06:00</span></span>
                                            </li>
                                            <li class="flex items-center">
                                                <div class="w-3 h-3 bg-purple-300 rounded-full mr-2 flex-shrink-0"></div>
                                                <span>{{ __('messages.night_shift_multiplier') }}: <span class="font-medium">{{ number_format($night_shift_multiplier ?? 1.25, 2) }}x</span></span>
                                            </li>
                                            <li class="flex items-center">
                                                <div class="w-3 h-3 bg-purple-400 rounded-full mr-2 flex-shrink-0"></div>
                                                <span>{{ __('messages.night_shift_detection') }}: 
                                                    @if($input_type === 'time_range')
                                                    <span class="font-medium">{{ __('messages.automatic_for_time_range') }}</span>
                                                    @else
                                                    <span class="font-medium">{{ __('messages.manual_selection') }}</span>
                                                    @endif
                                                </span>
                                            </li>
                                        </ul>
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
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Horas Calculadas -->
                                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm text-gray-600">{{ __('messages.calculated_hours') }}</p>
                                                <div wire:loading wire:target="start_time,end_time,direct_hours" class="flex items-center text-blue-600">
                                                    <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                    </svg>
                                                    {{ __('messages.calculating') }}...
                                                </div>
                                                <div wire:loading.remove wire:target="start_time,end_time,direct_hours">
                                                    <p class="text-2xl font-bold text-blue-600" x-data x-effect="$el.classList.add('animate-pulse'); setTimeout(() => $el.classList.remove('animate-pulse'), 500)">
                                                        {{ number_format($hours ?? 0, 2) }} {{ __('messages.hours') }}
                                                    </p>
                                                </div>
                                            </div>
                                            <i class="fas fa-clock text-blue-400 text-3xl"></i>
                                        </div>
                                    </div>

                                    <!-- Valor Total -->
                                    <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm text-gray-600">{{ __('messages.total_amount') }}</p>
                                                <div wire:loading wire:target="start_time,end_time,direct_hours,rate" class="flex items-center text-green-600">
                                                    <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                    </svg>
                                                    {{ __('messages.calculating') }}...
                                                </div>
                                                <div wire:loading.remove wire:target="start_time,end_time,direct_hours,rate">
                                                    <p class="text-2xl font-bold text-green-600" x-data x-effect="$el.classList.add('animate-pulse'); setTimeout(() => $el.classList.remove('animate-pulse'), 500)">
                                                        {{ number_format($amount ?? 0, 2) }} KZ
                                                    </p>
                                                </div>
                                            </div>
                                            <i class="fas fa-money-bill-wave text-green-400 text-3xl"></i>
                                        </div>
                                    </div>
                                </div>
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
