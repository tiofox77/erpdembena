<!-- Modal para Criar/Editar Recursos -->
<div>
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
        <div class="relative top-20 mx-auto p-1 w-full max-w-2xl">
            <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" 
                 x-transition:enter="transition ease-out duration-300" 
                 x-transition:enter-start="transform opacity-0 scale-95" 
                 x-transition:enter-end="transform opacity-100 scale-100" 
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="transform opacity-100 scale-100" 
                 x-transition:leave-end="transform opacity-0 scale-95">
                <!-- Cabeçalho do Modal com gradiente e animação -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <i class="fas {{ $editMode ? 'fa-edit' : 'fa-plus-circle' }} mr-2 animate-pulse"></i>
                        {{ $editMode ? trans('messages.edit_resource') : trans('messages.add_resource') }}
                    </h3>
                    <button type="button" wire:click="closeModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

            <!-- Formulário -->
            <form wire:submit.prevent="save">
                <div class="px-4 py-5 bg-white sm:p-6">
                    <!-- Validation Errors Summary -->
                    @if($errors->any())
                        <div class="mb-4 bg-red-50 p-4 rounded-md border-l-4 border-red-400 animate-pulse">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-red-500"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700 font-medium">{{ trans('messages.please_fix_errors') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nome do Recurso -->
                        <div class="col-span-1 md:col-span-2">
                            <label for="name" class="block text-sm font-medium text-gray-700">
                                {{ trans('messages.resource_name') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="relative mt-1">
                                <input type="text" id="name" wire:model.live="resource.name" 
                                    class="block w-full rounded-md pr-10 border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 @error('resource.name') border-red-500 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                                @error('resource.name')
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                    </div>
                                @enderror
                            </div>
                            @error('resource.name')
                                <p class="mt-1 text-sm text-red-500 flex items-center">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">{{ trans('messages.resource_name_help') }}</p>
                        </div>

                        <!-- Tipo de Recurso -->
                        <div>
                            <label for="resource_type_id" class="block text-sm font-medium text-gray-700">
                                {{ trans('messages.resource_type') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="relative mt-1">
                                <select id="resource_type_id" wire:model.live="resource.resource_type_id" 
                                    class="block w-full rounded-md pr-10 border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 @error('resource.resource_type_id') border-red-500 text-red-900 focus:border-red-500 focus:ring-red-500 @enderror">
                                    <option value="">{{ trans('messages.select_type') }}</option>
                                    @foreach($resourceTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                @error('resource.resource_type_id')
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                    </div>
                                @enderror
                            </div>
                            @error('resource.resource_type_id')
                                <p class="mt-1 text-sm text-red-500 flex items-center">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">{{ trans('messages.resource_type_help') }}</p>
                        </div>

                        <!-- Departamento -->
                        <div>
                            <label for="department_id" class="block text-sm font-medium text-gray-700">
                                {{ trans('messages.department') }}
                            </label>
                            <select id="department_id" wire:model.live="resource.department_id" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 @error('resource.department_id') border-red-500 @enderror">
                                <option value="">{{ trans('messages.select_department') }}</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                            @error('resource.department_id')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Localização -->
                        <div>
                            <label for="location_id" class="block text-sm font-medium text-gray-700">
                                {{ trans('messages.location') }}
                            </label>
                            <select id="location_id" wire:model.live="resource.location_id" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 @error('resource.location_id') border-red-500 @enderror">
                                <option value="">{{ trans('messages.select_location') }}</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                @endforeach
                            </select>
                            @error('resource.location_id')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Capacidade -->
                        <div>
                            <label for="capacity" class="block text-sm font-medium text-gray-700">
                                {{ trans('messages.capacity') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 flex rounded-md shadow-sm relative">
                                <input type="number" id="capacity" wire:model.live="resource.capacity" min="0" step="0.01"
                                    class="flex-1 block w-full rounded-none rounded-l-md border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 @error('resource.capacity') border-red-500 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                                <select wire:model.live="resource.capacity_uom" 
                                    class="inline-flex items-center px-3 py-2 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm @error('resource.capacity_uom') border-red-500 bg-red-50 text-red-900 focus:border-red-500 focus:ring-red-500 @enderror">
                                    <option value="hours">{{ trans('messages.hours') }}</option>
                                    <option value="units">{{ trans('messages.units') }}</option>
                                    <option value="volume">{{ trans('messages.volume') }}</option>
                                    <option value="weight">{{ trans('messages.weight') }}</option>
                                </select>
                                @if($errors->has('resource.capacity') || $errors->has('resource.capacity_uom'))
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none z-10">
                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                    </div>
                                @endif
                            </div>
                            @error('resource.capacity')
                                <p class="mt-1 text-sm text-red-500 flex items-center">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                            @error('resource.capacity_uom')
                                <p class="mt-1 text-sm text-red-500 flex items-center">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">{{ trans('messages.capacity_help') }}</p>
                        </div>

                        <!-- Fator de Eficiência -->
                        <div>
                            <label for="efficiency_factor" class="block text-sm font-medium text-gray-700">
                                {{ trans('messages.efficiency_factor') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 flex rounded-md shadow-sm relative">
                                <input type="number" id="efficiency_factor" wire:model.live="resource.efficiency_factor" min="1" max="200" step="1"
                                    class="flex-1 block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 @error('resource.efficiency_factor') border-red-500 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                                <span class="inline-flex items-center px-3 py-2 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                                    %
                                </span>
                                @error('resource.efficiency_factor')
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none z-10">
                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                    </div>
                                @enderror
                            </div>
                            @error('resource.efficiency_factor')
                                <p class="mt-1 text-sm text-red-500 flex items-center">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">{{ trans('messages.efficiency_factor_help') }}</p>
                        </div>

                        <!-- Status (Ativo/Inativo) -->
                        <div class="col-span-1 md:col-span-2">
                            <div class="flex items-center">
                                <input type="checkbox" id="active" wire:model.live="resource.active"
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <label for="active" class="ml-2 block text-sm text-gray-700">
                                    {{ trans('messages.resource_active') }}
                                </label>
                            </div>
                        </div>

                        <!-- Descrição -->
                        <div class="col-span-1 md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700">
                                {{ trans('messages.description') }}
                            </label>
                            <div class="relative mt-1">
                                <textarea id="description" wire:model.live="resource.description" rows="3"
                                    class="block w-full rounded-md pr-10 border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 @error('resource.description') border-red-500 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror"></textarea>
                                @error('resource.description')
                                    <div class="absolute top-0 right-0 pr-3 pt-3 flex items-center pointer-events-none">
                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                    </div>
                                @enderror
                            </div>
                            @error('resource.description')
                                <p class="mt-1 text-sm text-red-500 flex items-center">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">{{ trans('messages.resource_description_help') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Rodapé com Botões de Ação com Estado de Loading e Animações -->
                <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                    <button type="button" wire:click="closeModal" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <i class="fas fa-times mr-2"></i>
                        {{ trans('messages.cancel') }}
                    </button>
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="save">
                            <i class="fas {{ $editMode ? 'fa-save' : 'fa-plus-circle' }} mr-2"></i>
                            {{ $editMode ? trans('messages.update') : trans('messages.save') }}
                        </span>
                        <span wire:loading wire:target="save" class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ trans('messages.processing') }}
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
