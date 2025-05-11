<!-- Modal para Criar/Editar Tipos de Recurso -->
<div>
    <div x-data="{ open: @entangle('showResourceTypeModal') }" 
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
        <div class="relative top-20 mx-auto p-1 w-full max-w-lg">
            <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" 
                 x-transition:enter="transition ease-out duration-300" 
                 x-transition:enter-start="transform opacity-0 scale-95" 
                 x-transition:enter-end="transform opacity-100 scale-100" 
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="transform opacity-100 scale-100" 
                 x-transition:leave-end="transform opacity-0 scale-95">
                <!-- Cabeçalho do Modal com gradiente e animação -->
                <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <i class="fas {{ $editTypeMode ? 'fa-edit' : 'fa-plus-circle' }} mr-2 animate-pulse"></i>
                        {{ $editTypeMode ? trans('messages.edit_resource_type') : trans('messages.add_resource_type') }}
                    </h3>
                    <button type="button" wire:click="closeResourceTypeModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

            <!-- Formulário -->
            <form wire:submit.prevent="saveResourceType">
                <div class="px-4 py-5 bg-white sm:p-6">
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Nome do Tipo de Recurso -->
                        <div>
                            <label for="type_name" class="block text-sm font-medium text-gray-700">
                                {{ trans('messages.resource_type_name') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="type_name" wire:model.live="resourceType.name" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 @error('resourceType.name') border-red-500 @enderror">
                            @error('resourceType.name')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Descrição -->
                        <div>
                            <label for="type_description" class="block text-sm font-medium text-gray-700">
                                {{ trans('messages.description') }}
                            </label>
                            <textarea id="type_description" wire:model.live="resourceType.description" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 @error('resourceType.description') border-red-500 @enderror"></textarea>
                            @error('resourceType.description')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status (Ativo/Inativo) -->
                        <div>
                            <div class="flex items-center">
                                <input type="checkbox" id="type_active" wire:model.live="resourceType.active"
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <label for="type_active" class="ml-2 block text-sm text-gray-700">
                                    {{ trans('messages.resource_type_active') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rodapé com Botões de Ação com Estado de Loading e Animações -->
                <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                    <button type="button" wire:click="closeResourceTypeModal" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <i class="fas fa-times mr-2"></i>
                        {{ trans('messages.cancel') }}
                    </button>
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="saveResourceType">
                            <i class="fas {{ $editTypeMode ? 'fa-save' : 'fa-plus-circle' }} mr-2"></i>
                            {{ $editTypeMode ? trans('messages.update') : trans('messages.save') }}
                        </span>
                        <span wire:loading wire:target="saveResourceType" class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ trans('messages.processing') }}
                        </span>
                    </button>
                </div>
            </form>

            @if(!$editTypeMode)
                <!-- Lista de Tipos de Recurso Existentes -->
                <div class="px-4 py-3 bg-gray-100 border-t border-gray-200">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">{{ trans('messages.existing_resource_types') }}</h4>
                    <div class="max-h-60 overflow-y-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ trans('messages.name') }}
                                    </th>
                                    <th scope="col" class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ trans('messages.status') }}
                                    </th>
                                    <th scope="col" class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ trans('messages.actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($resourceTypes as $type)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                                            {{ $type->name }}
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-center">
                                            @if($type->active)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-check-circle mr-1"></i> Ativo
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <i class="fas fa-times-circle mr-1"></i> Inativo
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <button wire:click="editResourceType({{ $type->id }})" 
                                                    class="text-indigo-600 hover:text-indigo-900 transition-colors duration-150 transform hover:scale-110" title="{{ __('Editar') }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button wire:click="confirmDeleteResourceType({{ $type->id }})" 
                                                    class="text-red-600 hover:text-red-900 transition-colors duration-150 transform hover:scale-110" title="{{ trans('messages.delete') }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <button wire:click="toggleResourceTypeStatus({{ $type->id }})" 
                                                    class="{{ $type->active ? 'text-orange-600 hover:text-orange-900' : 'text-green-600 hover:text-green-900' }} transition-colors duration-150 transform hover:scale-110" 
                                                    title="{{ $type->active ? trans('messages.deactivate') : trans('messages.activate') }}">
                                                    <i class="fas {{ $type->active ? 'fa-ban' : 'fa-check-circle' }}"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-3 py-3 text-center text-gray-500">
                                            {{ trans('messages.no_resource_types_found') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
