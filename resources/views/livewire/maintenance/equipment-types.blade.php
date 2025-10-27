<div class="py-4">
    <!-- Cabeçalho do componente -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-tags mr-3 text-blue-600"></i>
                {{ __('messages.equipment_types') }}
            </h1>
            <div class="flex space-x-3">
                <button wire:click="create()" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-200 active:bg-blue-700 transition ease-in-out duration-150" onclick="console.log('Botão Adicionar clicado')">
                    <i class="fas fa-plus-circle mr-2"></i>
                    {{ __('messages.add_new_type') }}
                </button>
            </div>
        </div>
        <p class="mt-2 text-sm text-gray-500">
            {{ __('messages.manage_equipment_types_description') }}
        </p>
    </div>
    
    <!-- Barra de ferramentas com pesquisa e filtros -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="md:flex md:justify-between">
            <div class="w-full md:w-1/3 mb-4 md:mb-0">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('messages.search_equipment_types') }}" class="pl-10 pr-4 py-2 border-gray-300 focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 block w-full shadow-sm rounded-md">
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="flex items-center">
                    <label for="perPage" class="block text-sm font-medium text-gray-700 mr-2">{{ __('messages.per_page') }}:</label>
                    <select id="perPage" wire:model.live="perPage" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabela principal -->
    <div class="overflow-x-auto bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-blue-600 to-blue-700">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider cursor-pointer" wire:click="sortBy('name')">
                        <div class="flex items-center">
                            <span>{{ __('messages.name') }}</span>
                            @if ($sortField === 'name')
                                <span class="ml-1">
                                    @if ($sortDirection === 'asc')
                                        <i class="fas fa-sort-up"></i>
                                    @else
                                        <i class="fas fa-sort-down"></i>
                                    @endif
                                </span>
                            @else
                                <span class="ml-1"><i class="fas fa-sort text-gray-300"></i></span>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                        {{ __('messages.description') }}
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider cursor-pointer" wire:click="sortBy('is_active')">
                        <div class="flex items-center">
                            <span>{{ __('messages.status') }}</span>
                            @if ($sortField === 'is_active')
                                <span class="ml-1">
                                    @if ($sortDirection === 'asc')
                                        <i class="fas fa-sort-up"></i>
                                    @else
                                        <i class="fas fa-sort-down"></i>
                                    @endif
                                </span>
                            @else
                                <span class="ml-1"><i class="fas fa-sort text-gray-300"></i></span>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                        {{ __('messages.color') }}
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                        {{ __('messages.actions') }}
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($types as $type)
                <tr class="hover:bg-gray-50 transition-colors duration-150 ease-in-out">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $type->name }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-600 line-clamp-2">{{ $type->description ?? '-' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $type->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $type->is_active ? __('messages.active') : __('messages.inactive') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="h-6 w-6 rounded-full mr-2" style="background-color: {{ $type->color_code }}"></div>
                            <span class="text-sm text-gray-500">{{ $type->color_code }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex space-x-2">
                            <button wire:click="edit({{ $type->id }})" class="text-blue-600 hover:text-blue-900 focus:outline-none">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button wire:click="confirmDelete({{ $type->id }})" class="text-red-600 hover:text-red-900 focus:outline-none">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 whitespace-nowrap">
                        <div class="flex justify-center items-center space-x-2">
                            <i class="fas fa-search text-gray-400"></i>
                            <span class="text-gray-500">{{ __('messages.no_types_found') }}</span>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Paginação -->
    <div class="mt-4">
        {{ $types->links() }}
    </div>

    <!-- Modal para Criação/Edição de Tipo de Equipamento -->
    <div>
        <div x-data="{ open: false }" 
             x-init="$watch('$wire.showModal', value => { open = value; console.log('showModal alterado para:', value); })" 
             x-show="open" 
             x-cloak 
             @keydown.escape.window="$wire.closeModal()" 
             class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50" 
             role="dialog" 
             aria-modal="true"
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0">
             <div class="fixed inset-0" @click="$wire.closeModal()"></div>
            <div class="relative top-20 mx-auto p-1 w-full max-w-2xl">
                <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" 
                     @click.stop
                     x-transition:enter="transition ease-out duration-300" 
                     x-transition:enter-start="transform opacity-0 scale-95" 
                     x-transition:enter-end="transform opacity-100 scale-100" 
                     x-transition:leave="transition ease-in duration-200" 
                     x-transition:leave-start="transform opacity-100 scale-100" 
                     x-transition:leave-end="transform opacity-0 scale-95">
                    <!-- Cabeçalho com gradiente -->
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <i class="fas {{ $editMode ? 'fa-edit' : 'fa-plus-circle' }} mr-2 animate-pulse"></i>
                            {{ $editMode ? __('messages.edit_equipment_type') : __('messages.add_equipment_type') }}
                        </h3>
                        <button type="button" wire:click="closeModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <!-- Corpo do modal com formulário -->
                    <form wire:submit.prevent="save">
                        <div class="p-4 space-y-4">
                            <!-- Nome do Tipo -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">{{ __('messages.name') }} <span class="text-red-500">*</span></label>
                                <div class="mt-1 relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-tag text-gray-400"></i>
                                    </div>
                                    <input type="text" id="name" wire:model="equipmentType.name" class="pl-10 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('messages.enter_type_name') }}">
                                </div>
                                @error('equipmentType.name') <span class="mt-1 text-sm text-red-500">{{ $message }}</span> @enderror
                            </div>

                            <!-- Descrição -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">{{ __('messages.description') }}</label>
                                <div class="mt-1">
                                    <textarea id="description" wire:model="equipmentType.description" rows="3" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="{{ __('messages.enter_type_description') }}"></textarea>
                                </div>
                                @error('equipmentType.description') <span class="mt-1 text-sm text-red-500">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Status (Toggle) -->
                                <div>
                                    <div class="flex items-center">
                                        <div>
                                            <label for="is_active" class="flex items-center cursor-pointer">
                                                <div class="relative">
                                                    <!-- Input escondido -->
                                                    <input type="checkbox" wire:model.lazy="equipmentType.is_active" id="is_active" class="sr-only">
                                                    <!-- Track (fundo do toggle) -->
                                                    <div class="w-12 h-6 bg-gray-300 rounded-full shadow-inner transition-all duration-300 ease-in-out"></div>
                                                    <!-- Dot (bolinha do toggle) -->
                                                    <div class="dot absolute w-6 h-6 bg-white rounded-full shadow left-0 top-0 transition-transform duration-300 ease-in-out transform" 
                                                        :class="{'translate-x-6 bg-green-500': $wire.equipmentType.is_active, 'bg-white': !$wire.equipmentType.is_active}"></div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="ml-3">
                                            <label for="is_active" class="text-sm font-medium text-gray-700 cursor-pointer">
                                                {{ __('messages.is_active') }}
                                            </label>
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ __('messages.active_type_info') }}
                                            </p>
                                        </div>
                                    </div>
                                    @error('equipmentType.is_active') <span class="mt-1 text-sm text-red-500">{{ $message }}</span> @enderror
                                </div>

                                <!-- Código de cor -->
                                <div>
                                    <label for="color_code" class="block text-sm font-medium text-gray-700">{{ __('messages.color_code') }}</label>
                                    <div class="mt-1 flex">
                                        <div class="relative rounded-l-md shadow-sm">
                                             <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                 <i class="fas fa-palette text-gray-400"></i>
                                             </div>
                                             <input type="text" id="color_code" wire:model.lazy="equipmentType.color_code" class="pl-10 block w-full shadow-sm sm:text-sm border-gray-300 rounded-l-md focus:ring-blue-500 focus:border-blue-500" placeholder="#FF5500">
                                         </div>
                                         <div class="h-10 w-10 border border-gray-300 border-l-0 rounded-r-md" 
                                             x-data="{}"
                                             x-bind:style="`background-color: ${ $wire.equipmentType.color_code || '#CCCCCC' }`"
                                             x-effect="$el.style.backgroundColor = $wire.equipmentType.color_code || '#CCCCCC'">
                                         </div>
                                    </div>
                                    @error('equipmentType.color_code') <span class="mt-1 text-sm text-red-500">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Rodapé com botões de ação -->
                        <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                            <button type="button" wire:click="closeModal" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                <i class="fas fa-times mr-2"></i>
                                {{ __('messages.cancel') }}
                            </button>
                            <button type="submit" wire:loading.attr="disabled" class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="save">
                                    <i class="fas {{ $editMode ? 'fa-save' : 'fa-plus-circle' }} mr-2"></i>
                                    {{ $editMode ? __('messages.update') : __('messages.save') }}
                                </span>
                                <span wire:loading wire:target="save" class="inline-flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
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
    
    <!-- Modal de confirmação de exclusão -->
    <div>
        <div x-data="{ open: false }" 
             x-init="$watch('$wire.showDeleteModal', value => { open = value; console.log('showDeleteModal alterado para:', value); })" 
             x-show="open" 
             x-cloak 
             @keydown.escape.window="$wire.closeDeleteModal()" 
             class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50" 
             role="dialog" 
             aria-modal="true"
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0">
             <div class="fixed inset-0" @click="$wire.closeDeleteModal()"></div>
            <div class="relative top-20 mx-auto p-1 w-full max-w-md">
                <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" @click.stop>
                    <!-- Cabeçalho vermelho para alerta -->
                    <div class="bg-gradient-to-r from-red-600 to-red-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <i class="fas fa-exclamation-triangle mr-2 animate-pulse"></i>
                            {{ __('messages.confirm_deletion') }}
                        </h3>
                        <button type="button" wire:click="closeDeleteModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <!-- Corpo do modal -->
                    <div class="p-4">
                        <p class="text-gray-700">{{ __('messages.delete_equipment_type_confirmation', ['name' => $typeToDelete ? $typeToDelete->name : '']) }}</p>
                        
                        @if($typeToDelete)
                        <div class="mt-4 p-3 bg-gray-50 rounded-md">
                            <h4 class="font-medium text-gray-700">{{ $typeToDelete->name }}</h4>
                            <p class="text-sm text-gray-500 mt-1">{{ $typeToDelete->description }}</p>
                        </div>
                        @endif
                        
                        <div class="mt-5 flex justify-end space-x-3">
                            <button type="button" wire:click="closeDeleteModal" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                                <i class="fas fa-times mr-2"></i>
                                {{ __('messages.cancel') }}
                            </button>
                            <button type="button" wire:click="delete" wire:loading.attr="disabled" class="inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-150 ease-in-out">
                                <span wire:loading.remove wire:target="delete">
                                    <i class="fas fa-trash-alt mr-2"></i>
                                    {{ __('messages.delete') }}
                                </span>
                                <span wire:loading wire:target="delete" class="inline-flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ __('messages.processing') }}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
