<!-- Modal para Criar/Editar Programação de Produção -->
<div x-cloak
    class="fixed inset-0 z-30 flex items-center justify-center overflow-auto bg-gray-800 bg-opacity-75 transition-opacity"
    x-show="$wire.showModal"
    x-init="console.log('Modal status:', $wire.showModal)"
    @keydown.escape.window="$wire.closeCreateEditModal()">
    <div class="relative w-[80%] mx-auto my-8 px-4 sm:px-0"
        style="min-height: 200px; max-height: 90vh;"
        x-show="$wire.showModal"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        @click.away="$wire.closeCreateEditModal()">
        <div class="relative bg-white rounded-lg shadow-xl overflow-y-auto" style="max-height: calc(90vh - 2rem);" @click.stop>
            <!-- Cabeçalho do Modal -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-5 sm:px-6 flex justify-between items-center rounded-t-lg shadow-lg">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <i class="fas {{ $editMode ? 'fa-edit' : 'fa-industry' }} mr-3 text-2xl text-amber-300 animate__animated animate__fadeIn"></i>
                    {{ $editMode ? __('messages.edit_production_schedule') : __('messages.add_production_schedule') }}
                </h3>
                <button @click="$wire.closeCreateEditModal()" class="text-white hover:text-red-300 focus:outline-none transition-all duration-200 transform hover:scale-110">
                    <i class="fas fa-times-circle text-2xl"></i>
                </button>
            </div>

            <form wire:submit.prevent.live="{{ $editMode ? 'update' : 'store' }}" class="space-y-6">
                <div class="p-6 bg-gray-50">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-8">
                        <!-- Número da Programação -->
                        <div class="transition duration-300 ease-in-out transform hover:scale-[1.02]">
                            <label for="schedule_number" class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                                <i class="fas fa-hashtag text-blue-500 mr-2"></i> {{ __('messages.schedule_number') }}
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm"><i class="fas fa-barcode"></i></span>
                                </div>
                                <input type="text" id="schedule_number" 
                                    wire:model.live="schedule.schedule_number"
                                    readonly
                                    class="block w-full pl-10 py-2 bg-gray-100 border-gray-300 rounded-md shadow-sm focus:ring-0 focus:border-gray-300 sm:text-sm">
                            </div>
                            <p class="mt-1 text-xs text-gray-500 italic flex items-center">
                                <i class="fas fa-info-circle mr-1 text-blue-400"></i> {{ __('messages.auto_generated_field') }}
                            </p>
                        </div>

                        <!-- Produto -->
                        <div class="{{ $editMode && $schedule['schedule_number'] ? '' : 'md:col-span-2 lg:col-span-2' }} transition duration-300 ease-in-out transform hover:scale-[1.02]">
                            <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                                <i class="fas fa-box-open text-blue-500 mr-2"></i> {{ __('messages.product') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm"><i class="fas fa-industry"></i></span>
                                </div>
                                <select id="product_id" wire:model.live="schedule.product_id"
                                    class="block w-full pl-10 pr-10 py-2 bg-white border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md shadow-sm @error('schedule.product_id') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @enderror">
                                    <option value="">{{ __('messages.select_product') }}</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">
                                        <span class="font-medium">{{ $product->name }}</span> <span class="text-gray-600">({{ $product->sku }})</span>
                                    </option>
                                @endforeach
                                </select>
                                @error('schedule.product_id')
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                @enderror
                            </div>
                            @error('schedule.product_id')
                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Data Inicial -->
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.start_date') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm"><i class="fas fa-calendar-alt"></i></span>
                                </div>
                                <input type="date" id="start_date" wire:model.live="schedule.start_date"
                                    class="block w-full pl-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md @error('schedule.start_date') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @enderror">
                                @error('schedule.start_date')
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                @enderror
                            </div>
                            @error('schedule.start_date')
                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>
                        
                        <!-- Hora Inicial -->
                        <div>
                            <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.start_time') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm"><i class="fas fa-hourglass-start"></i></span>
                                </div>
                                <input type="time" id="start_time" wire:model.live="schedule.start_time"
                                    class="block w-full pl-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md @error('schedule.start_time') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @enderror">
                                @error('schedule.start_time')
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                @enderror
                            </div>
                            @error('schedule.start_time')
                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Data Final -->
                        <div class="transition duration-300 ease-in-out transform hover:scale-[1.02]">
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                                <i class="fas fa-calendar-alt text-blue-500 mr-2"></i> {{ __('messages.end_date') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm"><i class="fas fa-calendar-check"></i></span>
                                </div>
                                <input type="date" id="end_date" wire:model.live="schedule.end_date"
                                    class="block w-full pl-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md @error('schedule.end_date') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @enderror">
                                @error('schedule.end_date')
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                @enderror
                            </div>
                            @error('schedule.end_date')
                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>
                        
                        <!-- Hora Final -->
                        <div class="transition duration-300 ease-in-out transform hover:scale-[1.02]">
                            <label for="end_time" class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                                <i class="fas fa-clock text-blue-500 mr-2"></i> {{ __('messages.end_time') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm"><i class="fas fa-hourglass-end"></i></span>
                                </div>
                                <input type="time" id="end_time" wire:model.live="schedule.end_time"
                                    class="block w-full pl-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md @error('schedule.end_time') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @enderror">
                                @error('schedule.end_time')
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                @enderror
                            </div>
                            @error('schedule.end_time')
                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Quantidade -->
                        <!-- Quantidade -->
                        <div class="transition duration-300 ease-in-out transform hover:scale-[1.02]">
                            <label for="planned_quantity" class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                                <i class="fas fa-cubes text-blue-500 mr-2"></i> {{ __('messages.quantity') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">  
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm"><i class="fas fa-hashtag"></i></span>
                                </div>
                                <input type="number" id="planned_quantity" 
                                    wire:model.live="schedule.planned_quantity" 
                                    wire:input.debounce.300ms="checkComponentAvailability"
                                    class="block w-full pl-10 pr-12 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md @error('schedule.planned_quantity') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @enderror"
                                    placeholder="100" step="1" min="1" max="999999">
                                @error('schedule.planned_quantity')
                                    <div class="absolute inset-y-0 right-8 pr-3 flex items-center pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                @enderror
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm font-medium">unid</span>
                                </div>
                            </div>
                            @error('schedule.planned_quantity')
                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                                </p>
                            @enderror
                            
                            <!-- Alerta de componentes suficientes/insuficientes -->
                            @if($schedule['product_id'] && is_numeric($schedule['planned_quantity']) && $schedule['planned_quantity'] > 0)
                                @if(!$showComponentWarning && $maxQuantityPossible > 0)
                                    <div class="mt-2 p-3 bg-green-50 border-l-4 border-green-400 rounded-md shadow-sm animate__animated animate__fadeIn">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-check-circle text-green-500 text-xl"></i>
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <p class="text-sm font-medium text-green-800">
                                                    {{ __('messages.sufficient_components') }}
                                                </p>
                                                <p class="text-xs text-green-700 mt-1">
                                                    {{ __('messages.maximum_capacity') }}: <span class="font-bold">{{ number_format($maxQuantityPossible, 0) }}</span> {{ __('messages.units') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @elseif($showComponentWarning && $maxQuantityPossible >= 0)
                                    <!-- Mantemos o alerta amarelo que já existe para caso de componentes insuficientes -->
                                @else
                                    <div class="mt-2 p-3 bg-blue-50 border-l-4 border-blue-400 rounded-md shadow-sm animate__animated animate__fadeIn">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-info-circle text-blue-500 text-xl"></i>
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <p class="text-sm font-medium text-blue-800">
                                                    {{ __('messages.checking_component_availability') }}...
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif
                            
                            <!-- Alerta de componentes insuficientes -->
                            @if($showComponentWarning)
                                <div class="mt-2 p-3 bg-yellow-50 border-l-4 border-yellow-400 rounded-md shadow-sm">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-exclamation-triangle text-yellow-500 text-xl animate__animated animate__pulse animate__infinite"></i>
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <p class="text-sm font-medium text-yellow-800">
                                                @if(count($insufficientComponents) == 1 && isset($insufficientComponents[0]['name']) && $insufficientComponents[0]['name'] == 'BOM com status inválido')
                                                    <span class="font-bold">{{ __('messages.bom_inactive_warning') }}</span>
                                                    <span class="block text-xs mt-1">{{ __('messages.activate_bom_instruction') }}</span>
                                                @else
                                                    {{ __('messages.insufficient_components_warning') }} 
                                                    <span class="font-bold">({{ __('messages.maximum_possible') }}: {{ $maxQuantityPossible }})</span>
                                                @endif
                                            </p>
                                            <div x-data="{showDetails: false}" class="mt-1">
                                                <button @click="showDetails = !showDetails" type="button" class="text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center">
                                                    <i class="fas" :class="showDetails ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                                                    <span class="ml-1">{{ __('messages.view_details') }}</span>
                                                </button>
                                                <div x-show="showDetails" class="mt-2 text-xs text-gray-700">
                                                    <ul class="space-y-2">
                                                        @foreach($insufficientComponents as $component)
                                                            <li class="border-l-2 border-red-400 pl-3 py-1 hover:bg-yellow-100 transition-colors duration-200">
                                                                <div class="font-medium flex items-center">
                                                                    <i class="fas fa-box-open text-gray-700 mr-1"></i>
                                                                    <span>{{ $component['name'] }}</span>
                                                                    <span class="text-xs text-gray-500 ml-1">({{ $component['sku'] }})</span>
                                                                </div>
                                                                <div class="text-xs flex justify-between mt-1">
                                                                    <span class="text-gray-700">
                                                                        <i class="fas fa-cubes text-green-600 mr-1"></i> {{ __('messages.available') }}: <span class="font-bold">{{ is_numeric($component['available']) ? number_format((float)$component['available'], 2) : $component['available'] }}</span>
                                                                    </span>
                                                                    <span class="text-gray-700">
                                                                        <i class="fas fa-clipboard-list text-blue-600 mr-1"></i> {{ __('messages.required') }}: <span class="font-bold">{{ is_numeric($component['required']) ? number_format((float)$component['required'], 2) : $component['required'] }}</span>
                                                                    </span>
                                                                    <span class="text-red-600 font-bold">
                                                                        <i class="fas fa-exclamation-circle mr-1"></i> {{ __('messages.missing') }}: {{ is_numeric($component['missing']) ? number_format((float)$component['missing'], 2) : $component['missing'] }}
                                                                    </span>
                                                                </div>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.status') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm"><i class="fas fa-circle"></i></span>
                                </div>
                                <select id="status" wire:model.live="schedule.status"
                                    class="block w-full pl-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md @error('schedule.status') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @enderror">
                                    <option value="draft">{{ __('messages.draft') }}</option>
                                    <option value="confirmed">{{ __('messages.confirmed') }}</option>
                                    <option value="in_progress">{{ __('messages.in_progress') }}</option>
                                    <option value="completed">{{ __('messages.completed') }}</option>
                                    <option value="cancelled">{{ __('messages.cancelled') }}</option>
                                </select>
                                @error('schedule.status')
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                @enderror
                            </div>
                            @error('schedule.status')
                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Prioridade -->
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.priority') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm"><i class="fas fa-exclamation"></i></span>
                                </div>
                                <select id="priority" wire:model.live="schedule.priority"
                                    class="block w-full pl-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md @error('schedule.priority') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @enderror">
                                    <option value="low">{{ __('messages.priority_low') }}</option>
                                    <option value="medium">{{ __('messages.priority_medium') }}</option>
                                    <option value="high">{{ __('messages.priority_high') }}</option>
                                    <option value="urgent">{{ __('messages.priority_urgent') }}</option>
                                </select>
                                @error('schedule.priority')
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                @enderror
                            </div>
                            @error('schedule.priority')
                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Responsável -->
                        <div>
                            <label for="responsible" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.responsible') }}
                            </label>
                            <input type="text" id="responsible" wire:model.live="schedule.responsible" maxlength="100"
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" 
                                placeholder="{{ __('messages.responsible_placeholder') }}">
                            @error('schedule.responsible')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Linha de Produção -->
                        <div class="transition duration-300 ease-in-out transform hover:scale-[1.02]">
                            <label for="line_id" class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                                <i class="fas fa-industry text-blue-500 mr-2"></i> {{ __('messages.production_line') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm"><i class="fas fa-cogs"></i></span>
                                </div>
                                <select id="line_id" wire:model.live="schedule.line_id"
                                    class="block w-full pl-10 pr-10 py-2 bg-white border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md shadow-sm @error('schedule.line_id') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @enderror">
                                    <option value="">{{ __('messages.select_production_line') }}</option>
                                    @foreach($productionLines as $line)
                                        <option value="{{ $line->id }}">{{ $line->name }}</option>
                                    @endforeach
                                </select>
                                @error('schedule.line_id')
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                @enderror
                            </div>
                            @error('schedule.line_id')
                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Turnos -->
                        <div class="lg:col-span-3 transition duration-300 ease-in-out transform hover:scale-[1.01]">
                            <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                                <i class="fas fa-clock text-blue-500 mr-2"></i> {{ __('messages.shifts') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 mt-3" x-data="{}" @refresh-shifts.window="setTimeout(() => { $wire.$refresh(); console.log('Refreshing shifts...'); }, 200)">
                                @foreach($shifts as $shift)
                                    <div class="relative">
                                        <input id="shift_{{ $shift->id }}" name="shifts[]" type="checkbox" 
                                            wire:model="selectedShifts"
                                            wire:change="toggleShift({{ $shift->id }})"
                                            value="{{ $shift->id }}"
                                            @if(in_array($shift->id, $selectedShifts ?? [])) checked @endif
                                            class="sr-only peer">
                                        <label for="shift_{{ $shift->id }}" 
                                            class="flex flex-col items-center justify-center p-3 bg-white border-2 border-gray-300 rounded-lg cursor-pointer 
                                            peer-checked:border-blue-500 peer-checked:bg-blue-50
                                            hover:bg-gray-50 transition-all duration-200 h-full">
                                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-100 text-gray-600 peer-checked:text-blue-600 peer-checked:bg-blue-100 mb-2">
                                                <i class="fas fa-users"></i>
                                            </div>
                                            <span class="text-sm font-medium text-center text-gray-900 peer-checked:text-blue-600">{{ $shift->name }}</span>
                                            <span class="text-xs text-gray-500 peer-checked:text-blue-500 mt-1">
                                                {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                                            </span>
                                        </label>
                                        <div class="absolute hidden w-3 h-3 rounded-full bg-blue-500 peer-checked:flex top-1 right-1"></div>
                                    </div>
                                @endforeach
                            </div>
                            <p class="mt-1 text-xs text-gray-500">{{ __('messages.shifts_help') }}</p>
                            @error('schedule.shifts')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Localização de Inventário -->
                        <div class="lg:col-span-2 md:col-span-2 transition duration-300 ease-in-out transform hover:scale-[1.02]">
                            <label for="location_id" class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                                <i class="fas fa-map-marker-alt text-blue-500 mr-2"></i> {{ __('messages.location') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm"><i class="fas fa-warehouse"></i></span>
                                </div>
                                <select id="location_id" wire:model.live="schedule.location_id"
                                    class="block w-full pl-10 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md shadow-sm">
                                    <option value="">{{ __('messages.select_location') }}</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}">{{ $location->name }} - {{ $location->description }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('schedule.location_id')
                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Horas de Trabalho Diárias (calculadas automaticamente) -->
                        <div class="transition duration-300 ease-in-out transform hover:scale-[1.02]">
                            <label for="working_hours_per_day" class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                                <i class="fas fa-business-time text-blue-500 mr-2"></i> {{ __('messages.working_hours_per_day') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm"><i class="fas fa-clock"></i></span>
                                </div>
                                <input type="number" id="working_hours_per_day" wire:model.live="schedule.working_hours_per_day" 
                                    step="0.5" min="0.5" max="24"
                                    class="block w-full pl-10 pr-12 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md shadow-sm"
                                    placeholder="8">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm font-medium">h</span>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 italic flex items-center">
                                <i class="fas fa-calculator mr-1 text-blue-400"></i> {{ __('Sugestão baseada nos turnos selecionados, mas ajustável manualmente') }}
                            </p>
                            @error('schedule.working_hours_per_day')
                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Capacidade de Produção por Hora (calculada automaticamente) -->
                        <div class="transition duration-300 ease-in-out transform hover:scale-[1.02]">
                            <label for="hourly_production_rate" class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                                <i class="fas fa-tachometer-alt text-blue-500 mr-2"></i> {{ __('messages.hourly_production_rate') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm"><i class="fas fa-rocket"></i></span>
                                </div>
                                <input type="number" id="hourly_production_rate" wire:model.live="schedule.hourly_production_rate" 
                                    step="0.01" min="0.01"
                                    class="block w-full pl-10 pr-14 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md shadow-sm"
                                    placeholder="10.00">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm font-medium">un/h</span>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 italic flex items-center">
                                <i class="fas fa-calculator mr-1 text-blue-400"></i> {{ __('Sugestão baseada na quantidade, período e turnos, mas ajustável manualmente') }}
                            </p>
                            @error('schedule.hourly_production_rate')
                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Dias de Trabalho -->
                        <div class="lg:col-span-3 md:col-span-2 transition duration-300 ease-in-out transform hover:scale-[1.01]">
                            <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                                <i class="fas fa-calendar-week text-blue-500 mr-2"></i> {{ __('messages.working_days') }}
                            </label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-2 mt-3">
                                @php 
                                    $days = [
                                        'mon' => ['name' => __('messages.monday'), 'icon' => 'calendar-day'],
                                        'tue' => ['name' => __('messages.tuesday'), 'icon' => 'calendar-day'],
                                        'wed' => ['name' => __('messages.wednesday'), 'icon' => 'calendar-day'],
                                        'thu' => ['name' => __('messages.thursday'), 'icon' => 'calendar-day'],
                                        'fri' => ['name' => __('messages.friday'), 'icon' => 'calendar-day'],
                                        'sat' => ['name' => __('messages.saturday'), 'icon' => 'calendar-day'],
                                        'sun' => ['name' => __('messages.sunday'), 'icon' => 'calendar-day']
                                    ];
                                @endphp

                                @foreach($days as $key => $day)
                                    <div class="relative">
                                        <input id="working_day_{{ $key }}" name="working_days[]" type="checkbox" 
                                            wire:model.live="schedule.working_days.{{ $key }}"
                                            class="sr-only peer">
                                        <label for="working_day_{{ $key }}" 
                                            class="flex flex-col items-center justify-center px-2 py-3 bg-white border-2 border-gray-300 rounded-lg cursor-pointer peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:bg-gray-50 transition-all duration-200">
                                            <i class="fas fa-{{ $day['icon'] }} text-gray-500 peer-checked:text-blue-600 mb-1"></i>
                                            <span class="text-xs font-medium text-center text-gray-700 peer-checked:text-blue-600">{{ $day['name'] }}</span>
                                        </label>
                                        <div class="absolute hidden w-3 h-3 rounded-full bg-blue-500 peer-checked:flex top-1 right-1"></div>
                                    </div>
                                @endforeach
                            </div>
                            <p class="mt-1 text-xs text-gray-500 italic flex items-center">
                                <i class="fas fa-calculator mr-1 text-blue-400"></i> {{ __('Dias selecionados automaticamente com base nos turnos escolhidos') }}
                            </p>
                            @error('schedule.working_days')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tempo de Setup (em minutos) -->
                        <div class="transition duration-300 ease-in-out transform hover:scale-[1.02]">
                            <label for="setup_time" class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                                <i class="fas fa-tools text-blue-500 mr-2"></i> {{ __('messages.setup_time') }}
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm"><i class="fas fa-stopwatch"></i></span>
                                </div>
                                <input type="number" id="setup_time" wire:model.live="schedule.setup_time" 
                                    step="1" min="0"
                                    class="block w-full pl-10 pr-12 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md shadow-sm"
                                    placeholder="30">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm font-medium">min</span>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 italic flex items-center">
                                <i class="fas fa-calculator mr-1 text-blue-400"></i> {{ __('Valor sugerido baseado na complexidade da produção e quantidade') }}
                            </p>
                            @error('schedule.setup_time')
                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Tempo de Limpeza (em minutos) -->
                        <div class="transition duration-300 ease-in-out transform hover:scale-[1.02]">
                            <label for="cleanup_time" class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                                <i class="fas fa-broom text-blue-500 mr-2"></i> {{ __('messages.cleanup_time') }}
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm"><i class="fas fa-clock"></i></span>
                                </div>
                                <input type="number" id="cleanup_time" wire:model.live="schedule.cleanup_time" 
                                    step="1" min="0"
                                    class="block w-full pl-10 pr-12 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md shadow-sm"
                                    placeholder="20">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm font-medium">min</span>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 italic flex items-center">
                                <i class="fas fa-calculator mr-1 text-blue-400"></i> {{ __('Valor sugerido baseado no tipo de produção e quantidade planejada') }}
                            </p>
                            @error('schedule.cleanup_time')
                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Turnos -->
                       

                        <!-- Notas -->
                        <div class="md:col-span-2 lg:col-span-3 transition duration-300 ease-in-out transform hover:scale-[1.01]">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                                <i class="fas fa-sticky-note text-blue-500 mr-2"></i> {{ __('messages.notes') }}
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute top-3 left-3 pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm"><i class="fas fa-pen"></i></span>
                                </div>
                                <textarea id="notes" wire:model.live="schedule.notes" rows="3"
                                    class="block w-full pl-10 pr-3 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md shadow-sm"
                                    placeholder="{{ __('messages.schedule_notes_placeholder') }}"></textarea>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 italic flex items-center">
                                <i class="fas fa-info-circle mr-1"></i> {{ __('messages.notes_help') }}
                            </p>
                            @error('schedule.notes')
                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Rodapé do Modal -->
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 sm:px-6 flex flex-col sm:flex-row-reverse space-y-3 sm:space-y-0 sm:space-x-3 sm:space-x-reverse border-t border-gray-200 shadow-inner">
                    <button type="submit" wire:loading.attr="disabled" 
                        class="inline-flex justify-center items-center w-full sm:w-auto px-5 py-2.5 border border-transparent shadow-md text-sm font-medium rounded-lg text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <div wire:loading.remove wire:target="update, store">
                            <i class="fas {{ $editMode ? 'fa-save' : 'fa-plus-circle' }} mr-2 text-blue-200"></i>
                            {{ $editMode ? __('messages.save_changes') : __('messages.create_schedule') }}
                        </div>
                        <div wire:loading wire:target="update, store">
                            <i class="fas fa-spinner fa-spin mr-2 text-blue-200"></i>
                            {{ __('messages.saving') }}...
                        </div>
                    </button>
                    <button type="button" wire:click="closeCreateEditModal()" wire:loading.attr="disabled" 
                        class="inline-flex justify-center items-center w-full sm:w-auto px-5 py-2.5 border border-gray-300 shadow-md text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <i class="fas fa-times-circle mr-2 text-gray-500"></i>
                        {{ __('messages.cancel') }}
                    </button>
                    <div class="flex-grow hidden sm:block"></div>
                    <div class="text-xs text-gray-500 italic hidden sm:flex items-center">
                        <i class="fas fa-info-circle mr-1"></i> 
                        Todos os campos marcados com <span class="text-red-500 mx-1">*</span> são obrigatórios
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
