<!-- Modal para Criar ou Editar Plano de Capacidade -->
<div x-data="{ 
        showCalcInfo: false,
        calcEffectiveCapacity() {
            if (this.$wire.plan.available_capacity && this.$wire.plan.efficiency_factor) {
                return (parseFloat(this.$wire.plan.available_capacity) * parseFloat(this.$wire.plan.efficiency_factor) / 100).toFixed(2);
            }
            return 0;
        }
    }" 
    x-cloak
    class="fixed inset-0 z-30 flex items-center justify-center overflow-auto bg-gray-800 bg-opacity-75 transition-all duration-300 ease-in-out"
    x-show="$wire.showModal"
    @keydown.escape.window="$wire.closeModal()">
    <div class="relative w-full max-w-4xl mx-auto my-8 px-4 sm:px-0"
        x-show="$wire.showModal"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        @click.away="$wire.closeModal()">
        <div class="relative bg-white rounded-lg shadow-xl overflow-hidden" @click.stop>
            <!-- Cabeçalho do Modal -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-4 sm:px-6 flex justify-between items-center">
                <h3 class="text-lg font-medium text-white">
                    <i class="fas {{ $editMode ? 'fa-edit' : 'fa-plus-circle' }} mr-2"></i>
                    {{ $editMode ? __('messages.edit_capacity_plan') : __('messages.add_capacity_plan') }}
                </h3>
                <button @click="$wire.closeModal()" class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Formulário -->
            <form wire:submit.prevent="save">
                <div class="px-4 py-5 sm:p-6 space-y-6">
                    <!-- Nome e Descrição -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Nome do Plano -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.name') }} <span class="text-red-600">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" wire:model="plan.name" id="name" 
                                    class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('plan.name') border-red-500 @enderror"
                                    placeholder="{{ __('messages.capacity_plan_name_placeholder') }}">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-file-signature text-gray-400"></i>
                                </div>
                            </div>
                            @error('plan.name')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Descrição -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.description') }}
                            </label>
                            <div class="relative">
                                <input type="text" wire:model="plan.description" id="description" 
                                    class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('plan.description') border-red-500 @enderror"
                                    placeholder="{{ __('messages.description_placeholder') }}">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-align-left text-gray-400"></i>
                                </div>
                            </div>
                            @error('plan.description')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Seção de Recurso -->
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
                            <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-cogs text-blue-600 mr-2"></i>
                                {{ __('messages.resource_information') }}
                            </h4>
                            
                            <!-- Tipo de Recurso -->
                            <div class="mb-4">
                                <label for="resource_type_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.resource_type') }} <span class="text-red-600">*</span>
                                </label>
                                <div class="relative">
                                    <select wire:model="plan.resource_type_id" id="resource_type_id" 
                                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('plan.resource_type_id') border-red-500 @enderror">
                                        <option value="">{{ __('messages.select_resource_type') }}</option>
                                        @foreach($resourceTypes as $resourceType)
                                            <option value="{{ $resourceType->id }}">{{ $resourceType->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-cog text-gray-400"></i>
                                    </div>
                                </div>
                                @error('plan.resource_type_id')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Recurso -->
                            <div class="mb-4">
                                <label for="resource_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.resource') }} <span class="text-red-600">*</span>
                                </label>
                                <div class="relative">
                                    <select wire:model="plan.resource_id" id="resource_id" 
                                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('plan.resource_id') border-red-500 @enderror">
                                        <option value="">{{ __('messages.select_resource') }}</option>
                                        @foreach($resources as $resource)
                                            <option value="{{ $resource->id }}">{{ $resource->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-wrench text-gray-400"></i>
                                    </div>
                                </div>
                                @error('plan.resource_id')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                                @if(empty($plan['resource_type_id']))
                                    <p class="mt-1 text-sm text-blue-500">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        {{ __('messages.select_resource_type_first') }}
                                    </p>
                                @endif
                            </div>
                            
                            <!-- Departamento -->
                            <div class="mb-4">
                                <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.department') }} <span class="text-red-600">*</span>
                                </label>
                                <div class="relative">
                                    <select wire:model="plan.department_id" id="department_id" 
                                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('plan.department_id') border-red-500 @enderror">
                                        <option value="">{{ __('messages.select_department') }}</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-building text-gray-400"></i>
                                    </div>
                                </div>
                                @error('plan.department_id')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Localização -->
                            <div>
                                <label for="location_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.location') }} <span class="text-red-600">*</span>
                                </label>
                                <div class="relative">
                                    <select wire:model="plan.location_id" id="location_id" 
                                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('plan.location_id') border-red-500 @enderror">
                                        <option value="">{{ __('messages.select_location') }}</option>
                                        @foreach($locations as $location)
                                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-map-marker-alt text-gray-400"></i>
                                    </div>
                                </div>
                                @error('plan.location_id')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Seção de Capacidade -->
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
                            <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-chart-bar text-blue-600 mr-2"></i>
                                {{ __('messages.capacity_information') }}
                            </h4>
                            
                            <!-- Período: Data de Início -->
                            <div class="mb-4">
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.start_date') }} <span class="text-red-600">*</span>
                                </label>
                                <div class="relative">
                                    <input type="date" wire:model="plan.start_date" id="start_date" 
                                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('plan.start_date') border-red-500 @enderror">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-calendar-day text-gray-400"></i>
                                    </div>
                                </div>
                                @error('plan.start_date')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Período: Data de Término -->
                            <div class="mb-4">
                                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.end_date') }} <span class="text-red-600">*</span>
                                </label>
                                <div class="relative">
                                    <input type="date" wire:model="plan.end_date" id="end_date" 
                                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('plan.end_date') border-red-500 @enderror">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-calendar-alt text-gray-400"></i>
                                    </div>
                                </div>
                                @error('plan.end_date')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Unidade de Medida -->
                            <div class="mb-4">
                                <label for="capacity_uom" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.capacity_uom') }} <span class="text-red-600">*</span>
                                </label>
                                <div class="relative">
                                    <select wire:model="plan.capacity_uom" id="capacity_uom" 
                                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('plan.capacity_uom') border-red-500 @enderror">
                                        @foreach($capacityUnits as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-ruler text-gray-400"></i>
                                    </div>
                                </div>
                                @error('plan.capacity_uom')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Capacidade Disponível -->
                            <div class="mb-4">
                                <label for="available_capacity" class="block text-sm font-medium text-gray-700 mb-1 inline-flex items-center">
                                    {{ __('messages.available_capacity') }} <span class="text-red-600">*</span>
                                    <button type="button" @click="showCalcInfo = !showCalcInfo" class="ml-1 text-blue-500 hover:text-blue-700 focus:outline-none">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                </label>
                                <div class="relative">
                                    <input type="number" step="0.01" min="0" wire:model="plan.available_capacity" id="available_capacity" 
                                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('plan.available_capacity') border-red-500 @enderror"
                                        placeholder="0.00">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-chart-line text-gray-400"></i>
                                    </div>
                                </div>
                                @error('plan.available_capacity')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                                
                                <!-- Informações de cálculo -->
                                <div x-show="showCalcInfo" class="mt-2 bg-blue-50 p-3 rounded-md text-sm text-blue-700">
                                    <p class="font-medium mb-1">{{ __('messages.capacity_calculation_info') }}:</p>
                                    <ul class="list-disc list-inside space-y-1 ml-2">
                                        <li>{{ __('messages.hours') }}: {{ __('messages.capacity_hours_explanation') }}</li>
                                        <li>{{ __('messages.units') }}: {{ __('messages.capacity_units_explanation') }}</li>
                                        <li>{{ __('messages.volume') }}: {{ __('messages.capacity_volume_explanation') }}</li>
                                        <li>{{ __('messages.weight') }}: {{ __('messages.capacity_weight_explanation') }}</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <!-- Fator de Eficiência -->
                            <div class="mb-4">
                                <label for="efficiency_factor" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.efficiency_factor') }} (%) <span class="text-red-600">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number" step="1" min="1" max="200" wire:model="plan.efficiency_factor" id="efficiency_factor" 
                                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('plan.efficiency_factor') border-red-500 @enderror"
                                        placeholder="100">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-percentage text-gray-400"></i>
                                    </div>
                                </div>
                                @error('plan.efficiency_factor')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">{{ __('messages.efficiency_factor_help') }}</p>
                            </div>
                            
                            <!-- Capacidade Efetiva (Calculada) -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.effective_capacity') }}
                                </label>
                                <div class="relative">
                                    <div class="flex items-center justify-between px-3 py-2 border border-gray-300 bg-gray-50 rounded-md text-sm text-gray-700">
                                        <span x-text="calcEffectiveCapacity()"></span>
                                        <span>{{ $capacityUnits[$plan['capacity_uom']] ?? $plan['capacity_uom'] }}</span>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">{{ __('messages.effective_capacity_help') }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Seção Adicional -->
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
                        <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-clipboard-list text-blue-600 mr-2"></i>
                            {{ __('messages.additional_information') }}
                        </h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.status') }} <span class="text-red-600">*</span>
                                </label>
                                <div class="relative">
                                    <select wire:model="plan.status" id="status" 
                                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('plan.status') border-red-500 @enderror">
                                        @foreach($statuses as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-tag text-gray-400"></i>
                                    </div>
                                </div>
                                @error('plan.status')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Capacidade Planejada (opcional) -->
                            <div>
                                <label for="planned_capacity" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.planned_capacity') }}
                                </label>
                                <div class="relative">
                                    <input type="number" step="0.01" min="0" wire:model="plan.planned_capacity" id="planned_capacity" 
                                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('plan.planned_capacity') border-red-500 @enderror"
                                        placeholder="0.00">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-tasks text-gray-400"></i>
                                    </div>
                                </div>
                                @error('plan.planned_capacity')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">{{ __('messages.planned_capacity_help') }}</p>
                            </div>
                            
                            <!-- Notas -->
                            <div class="md:col-span-2">
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('messages.notes') }}
                                </label>
                                <div class="relative">
                                    <textarea wire:model="plan.notes" id="notes" rows="3" 
                                        class="block w-full pl-3 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('plan.notes') border-red-500 @enderror"
                                        placeholder="{{ __('messages.notes_placeholder') }}"></textarea>
                                </div>
                                @error('plan.notes')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Rodapé do Modal -->
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-col sm:flex-row justify-end gap-2">
                    <button type="button" wire:click="closeModal" 
                        class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out">
                        <i class="fas fa-times mr-2"></i>
                        {{ __('messages.cancel') }}
                    </button>
                    <button type="submit" 
                        wire:loading.attr="disabled"
                        class="inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out">
                        <span wire:loading.class="hidden">
                            <i class="fas {{ $editMode ? 'fa-save' : 'fa-plus-circle' }} mr-2"></i>
                            {{ $editMode ? __('messages.update') : __('messages.save') }}
                        </span>
                        <span wire:loading class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ __('messages.processing') }}...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
