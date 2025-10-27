{{-- Create Batch Modal --}}
@if($showBatchModal)
<div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50" x-data="{ loading: false }" x-init="console.log('Modal inicializada', { selected_employees: {{ json_encode($selected_employees) }} })">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl mx-4 max-h-[95vh] overflow-hidden">
        
        {{-- Header --}}
        <div class="bg-gradient-to-r from-purple-600 to-indigo-700 p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-white/20 p-2 rounded-lg">
                        <i class="fas fa-layer-group text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold">{{ __('payroll.create_batch_title') }}</h2>
                        <p class="text-purple-100">{{ __('payroll.create_batch_description') }}</p>
                    </div>
                </div>
                <button wire:click="closeBatchModal" class="text-white/80 hover:text-white p-2">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <div class="overflow-y-auto max-h-[calc(95vh-120px)]">
            <div class="p-8">
                {{-- Form Steps --}}
                <div class="mb-8">
                    <div class="flex items-center space-x-4 text-sm">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center font-semibold">1</div>
                            <span class="font-medium text-gray-900">{{ __('payroll.basic_settings') }}</span>
                        </div>
                        <div class="flex-1 h-px bg-gray-300"></div>
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 {{ $payroll_period_id ? 'bg-purple-600 text-white' : 'bg-gray-300 text-gray-500' }} rounded-full flex items-center justify-center font-semibold">2</div>
                            <span class="font-medium {{ $payroll_period_id ? 'text-gray-900' : 'text-gray-500' }}">{{ __('payroll.employee_selection') }}</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    {{-- Step 1: Basic Configuration --}}
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-6 border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-cog text-purple-500 mr-2"></i>
                            {{ __('payroll.basic_settings') }}
                        </h3>
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            {{-- Batch Name --}}
                            <div>
                                <label for="batch_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-tag text-gray-400 mr-1"></i>
                                    {{ __('payroll.batch_name_required') }}
                                </label>
                                <input
                                    type="text"
                                    id="batch_name"
                                    wire:model="batch_name"
                                    placeholder="{{ __('payroll.batch_name_placeholder') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                                    required
                                >
                                @error('batch_name') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                            </div>

                            {{-- Batch Date --}}
                            <div>
                                <label for="batch_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-calendar text-gray-400 mr-1"></i>
                                    {{ __('payroll.batch_date_required') }}
                                </label>
                                <input
                                    type="date"
                                    id="batch_date"
                                    wire:model="batch_date"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                                    required
                                >
                                @error('batch_date') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                            </div>

                            {{-- Payroll Period --}}
                            <div>
                                <label for="payroll_period_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-calendar-alt text-gray-400 mr-1"></i>
                                    {{ __('payroll.payroll_period_required') }}
                                </label>
                                <select
                                    id="payroll_period_id"
                                    wire:model.live="payroll_period_id"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                                    required
                                >
                                    <option value="">{{ __('payroll.select_period') }}</option>
                                    @foreach($payrollPeriods as $period)
                                        <option value="{{ $period->id }}">{{ $period->name }}</option>
                                    @endforeach
                                </select>
                                @error('payroll_period_id') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                            </div>

                            {{-- Payment Method --}}
                            <div>
                                <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-credit-card text-gray-400 mr-1"></i>
                                    {{ __('payroll.payment_method') }}
                                </label>
                                <select
                                    id="payment_method"
                                    wire:model="payment_method"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                                >
                                    <option value="bank_transfer">{{ __('payroll.bank_transfer') }}</option>
                                    <option value="cash">{{ __('payroll.cash') }}</option>
                                    <option value="check">{{ __('payroll.check') }}</option>
                                </select>
                            </div>

                            {{-- Department Filter --}}
                            <div class="lg:col-span-2">
                                <label for="department_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-building text-gray-400 mr-1"></i>
                                    {{ __('payroll.filter_by_department') }}
                                </label>
                                <select
                                    id="department_id"
                                    wire:model.live="department_id"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                                >
                                    <option value="">{{ __('payroll.all_departments_option') }}</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Description --}}
                            <div class="lg:col-span-2">
                                <label for="batch_description" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-file-alt text-gray-400 mr-1"></i>
                                    {{ __('payroll.description') }}
                                </label>
                                <textarea
                                    id="batch_description"
                                    wire:model="batch_description"
                                    rows="3"
                                    placeholder="{{ __('payroll.description_placeholder') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all resize-none"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Step 2: Employee Selection --}}
                    @if($payroll_period_id && count($eligible_employees) > 0)
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-users text-blue-500 mr-2"></i>
                                    {{ __('payroll.eligible_employees') }} ({{ count($eligible_employees) }} {{ __('payroll.employees_found') }})
                                </h3>
                                <div class="flex items-center space-x-3">
                                    <button
                                        type="button"
                                        wire:click="selectAllEmployees"
                                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm"
                                    >
                                        {{ __('payroll.select_all') }}
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="deselectAllEmployees"
                                        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm"
                                    >
                                        {{ __('payroll.deselect_all') }}
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-80 overflow-y-auto">
                                @foreach($eligible_employees as $employee)
                                    <div class="bg-white rounded-lg p-4 border border-gray-200 hover:border-blue-300 transition-all">
                                        <label class="flex items-start space-x-3 cursor-pointer">
                                            <div class="flex items-center h-5">
                                                <input
                                                    type="checkbox"
                                                    wire:click="toggleEmployee({{ $employee['id'] }})"
                                                    {{ in_array($employee['id'], $selected_employees) ? 'checked' : '' }}
                                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                                                >
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center space-x-2">
                                                    <div class="w-8 h-8 bg-gradient-to-br from-blue-100 to-blue-200 rounded-full flex items-center justify-center">
                                                        <i class="fas fa-user text-blue-600 text-sm"></i>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $employee['full_name'] }}</p>
                                                        <p class="text-xs text-gray-500 truncate">{{ $employee['id_card'] }}</p>
                                                    </div>
                                                </div>
                                                <div class="mt-2 space-y-1">
                                                    <div class="flex items-center space-x-2 text-xs text-gray-600">
                                                        <i class="fas fa-building text-gray-400"></i>
                                                        <span class="truncate">{{ $employee['department_name'] }}</span>
                                                    </div>
                                                    <div class="flex items-center space-x-2 text-xs text-gray-600">
                                                        <i class="fas fa-briefcase text-gray-400"></i>
                                                        <span class="truncate">{{ $employee['position_name'] }}</span>
                                                    </div>
                                                    <div class="flex items-center space-x-2 text-xs text-green-600 font-medium">
                                                        <i class="fas fa-money-bill text-green-500"></i>
                                                        <span>{{ number_format($employee['base_salary'], 0) }} AOA</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                            @if(count($selected_employees) > 0)
                                <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                                    <div class="flex items-center space-x-2 text-green-700">
                                        <i class="fas fa-check-circle"></i>
                                        <span class="font-medium">{{ count($selected_employees) }} {{ __('payroll.employees_selected') }}</span>
                                    </div>
                                </div>
                            @endif

                            @error('selected_employees') 
                                <div class="mt-2 text-red-500 text-sm">{{ $message }}</div> 
                            @enderror
                        </div>
                    @elseif($payroll_period_id && count($eligible_employees) === 0)
                        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6">
                            <div class="flex items-center space-x-3">
                                <div class="bg-yellow-100 p-2 rounded-full">
                                    <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-yellow-800">{{ __('payroll.no_eligible_employees') }}</h3>
                                    <p class="text-yellow-700 mt-1">
                                        {{ __('payroll.no_employees_message') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Form Actions --}}
                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                        <button
                            type="button"
                            wire:click="closeBatchModal"
                            class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-medium transition-colors"
                        >
                            {{ __('payroll.cancel') }}
                        </button>
                        <button
                            type="button"
                            wire:click="createBatch"
                            :disabled="loading || {{ count($selected_employees) === 0 ? 'true' : 'false' }}"
                            @click="loading = true; console.log('Botão clicado, chamando createBatch via wire:click...');"
                            class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition-colors disabled:opacity-50 flex items-center space-x-2"
                        >
                            <span x-show="!loading">
                                <i class="fas fa-save mr-2"></i>
                                {{ __('payroll.create_batch') }}
                            </span>
                            <span x-show="loading" class="flex items-center">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                {{ __('payroll.creating') }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
