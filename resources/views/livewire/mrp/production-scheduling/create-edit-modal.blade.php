<!-- Modal para Criar/Editar Programação de Produção -->
<div x-cloak
    class="fixed inset-0 z-30 flex items-center justify-center overflow-auto bg-gray-800 bg-opacity-75 transition-opacity"
    x-show="$wire.showModal"
    @keydown.escape.window="$wire.closeCreateEditModal()">
    <div class="relative w-full max-w-2xl mx-auto my-8 px-4 sm:px-0"
        x-show="$wire.showModal"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        @click.away="$wire.closeCreateEditModal()">
        <div class="relative bg-white rounded-lg shadow-xl overflow-hidden" @click.stop>
            <!-- Cabeçalho do Modal -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-4 sm:px-6 flex justify-between items-center">
                <h3 class="text-lg font-medium text-white">
                    <i class="fas {{ $editMode ? 'fa-edit' : 'fa-plus-circle' }} mr-2"></i>
                    {{ $editMode ? __('messages.edit_production_schedule') : __('messages.add_production_schedule') }}
                </h3>
                <button @click="$wire.closeCreateEditModal()" class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form wire:submit.prevent="{{ $editMode ? 'update' : 'store' }}">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Número da Programação -->
                        <div>
                            <label for="schedule_number" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.schedule_number') }}
                            </label>
                            <input type="text" id="schedule_number" 
                                wire:model="schedule.schedule_number"
                                readonly
                                class="mt-1 block w-full bg-gray-100 border-gray-300 rounded-md shadow-sm focus:ring-0 focus:border-gray-300 sm:text-sm">
                            <p class="mt-1 text-xs text-gray-500">{{ __('messages.auto_generated_field') }}</p>
                        </div>

                        <!-- Produto -->
                        <div class="{{ $editMode && $schedule->schedule_number ? '' : 'md:col-span-2' }}">
                            <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.product') }} <span class="text-red-500">*</span>
                            </label>
                            <select id="product_id" wire:model="schedule.product_id"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                <option value="">{{ __('messages.select_product') }}</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                @endforeach
                            </select>
                            @error('schedule.product_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Data Inicial -->
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.start_date') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="start_date" wire:model="schedule.start_date"
                                class="mt-1 block w-full pl-3 pr-3 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            @error('schedule.start_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Hora Inicial -->
                        <div>
                            <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.start_time') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="time" id="start_time" wire:model="schedule.start_time"
                                class="mt-1 block w-full pl-3 pr-3 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            @error('schedule.start_time')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Data Final -->
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.end_date') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="end_date" wire:model="schedule.end_date"
                                class="mt-1 block w-full pl-3 pr-3 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            @error('schedule.end_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Hora Final -->
                        <div>
                            <label for="end_time" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.end_time') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="time" id="end_time" wire:model="schedule.end_time"
                                class="mt-1 block w-full pl-3 pr-3 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            @error('schedule.end_time')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Quantidade -->
                        <div>
                            <label for="planned_quantity" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.quantity') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number" id="planned_quantity" wire:model="schedule.planned_quantity" step="0.01" min="0"
                                    class="focus:ring-blue-500 focus:border-blue-500 block w-full pr-12 sm:text-sm border-gray-300 rounded-md" 
                                    placeholder="0.00">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">{{ __('messages.units') }}</span>
                                </div>
                            </div>
                            @error('schedule.planned_quantity')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.status') }} <span class="text-red-500">*</span>
                            </label>
                            <select id="status" wire:model="schedule.status"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                <option value="draft">{{ __('messages.draft') }}</option>
                                <option value="confirmed">{{ __('messages.confirmed') }}</option>
                                <option value="in_progress">{{ __('messages.in_progress') }}</option>
                                <option value="completed">{{ __('messages.completed') }}</option>
                                <option value="cancelled">{{ __('messages.cancelled') }}</option>
                            </select>
                            @error('schedule.status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Prioridade -->
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.priority') }} <span class="text-red-500">*</span>
                            </label>
                            <select id="priority" wire:model="schedule.priority"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                <option value="low">{{ __('messages.priority_low') }}</option>
                                <option value="medium">{{ __('messages.priority_medium') }}</option>
                                <option value="high">{{ __('messages.priority_high') }}</option>
                                <option value="urgent">{{ __('messages.priority_urgent') }}</option>
                            </select>
                            @error('schedule.priority')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Responsável -->
                        <div>
                            <label for="responsible" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.responsible') }}
                            </label>
                            <input type="text" id="responsible" wire:model="schedule.responsible" maxlength="100"
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" 
                                placeholder="{{ __('messages.responsible_placeholder') }}">
                            @error('schedule.responsible')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Localização de Inventário -->
                        <div class="md:col-span-2">
                            <label for="location_id" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.location') }} <span class="text-red-500">*</span>
                            </label>
                            <select id="location_id" wire:model="schedule.location_id"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                <option value="">{{ __('messages.select_location') }}</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}">{{ $location->name }} - {{ $location->description }}</option>
                                @endforeach
                            </select>
                            @error('schedule.location_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Horas de Trabalho Diárias -->
                        <div>
                            <label for="working_hours_per_day" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.working_hours_per_day') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number" id="working_hours_per_day" wire:model="schedule.working_hours_per_day" 
                                    step="0.5" min="0.5" max="24"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                                    placeholder="8">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">h</span>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">{{ __('messages.hours_per_day_help') }}</p>
                            @error('schedule.working_hours_per_day')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Capacidade de Produção por Hora -->
                        <div>
                            <label for="hourly_production_rate" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.hourly_production_rate') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number" id="hourly_production_rate" wire:model="schedule.hourly_production_rate" 
                                    step="0.01" min="0.01"
                                    class="mt-1 block w-full pl-3 pr-14 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                                    placeholder="10.00">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">un/h</span>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">{{ __('messages.production_rate_help') }}</p>
                            @error('schedule.hourly_production_rate')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Dias de Trabalho -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.working_days') }}
                            </label>
                            <div class="grid grid-cols-7 gap-2">
                                @php 
                                    $days = [
                                        'mon' => __('messages.monday'),
                                        'tue' => __('messages.tuesday'),
                                        'wed' => __('messages.wednesday'),
                                        'thu' => __('messages.thursday'),
                                        'fri' => __('messages.friday'),
                                        'sat' => __('messages.saturday'),
                                        'sun' => __('messages.sunday')
                                    ];
                                @endphp

                                @foreach($days as $key => $day)
                                    <div class="flex items-center">
                                        <div class="flex items-center h-5">
                                            <input id="working_day_{{ $key }}" name="working_days[]" type="checkbox" 
                                                wire:model="schedule.working_days.{{ $key }}"
                                                class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                        </div>
                                        <div class="ml-2 text-sm">
                                            <label for="working_day_{{ $key }}" class="font-medium text-gray-700">{{ $day }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <p class="mt-1 text-xs text-gray-500">{{ __('messages.working_days_help') }}</p>
                            @error('schedule.working_days')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tempo de Setup (em minutos) -->
                        <div>
                            <label for="setup_time" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.setup_time') }}
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number" id="setup_time" wire:model="schedule.setup_time" 
                                    step="1" min="0"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                                    placeholder="30">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">min</span>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">{{ __('messages.setup_time_help') }}</p>
                            @error('schedule.setup_time')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tempo de Limpeza (em minutos) -->
                        <div>
                            <label for="cleanup_time" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.cleanup_time') }}
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number" id="cleanup_time" wire:model="schedule.cleanup_time" 
                                    step="1" min="0"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                                    placeholder="15">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">min</span>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">{{ __('messages.cleanup_time_help') }}</p>
                            @error('schedule.cleanup_time')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notas -->
                        <div class="md:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.notes') }}
                            </label>
                            <textarea id="notes" wire:model="schedule.notes" rows="3"
                                class="mt-1 block w-full pl-3 pr-3 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                                placeholder="{{ __('messages.schedule_notes_placeholder') }}"></textarea>
                            @error('schedule.notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Rodapé do Modal -->
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-col sm:flex-row-reverse space-y-2 sm:space-y-0 sm:space-x-2 sm:space-x-reverse">
                    <button type="submit" wire:loading.attr="disabled" 
                        class="inline-flex justify-center items-center w-full sm:w-auto px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out">
                        <div wire:loading.remove wire:target="update, store">
                            <i class="fas {{ $editMode ? 'fa-save' : 'fa-plus-circle' }} mr-2"></i>
                            {{ $editMode ? __('messages.save_changes') : __('messages.create_schedule') }}
                        </div>
                        <div wire:loading wire:target="update, store">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            {{ __('messages.saving') }}...
                        </div>
                    </button>
                    <button type="button" wire:click="closeCreateEditModal" wire:loading.attr="disabled" 
                        class="inline-flex justify-center items-center w-full sm:w-auto px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out">
                        <i class="fas fa-times mr-2"></i>
                        {{ __('messages.cancel') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
