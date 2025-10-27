<div>
    <div class="container mx-auto px-4 py-6">
        <!-- Cabeçalho da página -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-800 flex items-center">
                <i class="fas fa-cogs text-blue-600 mr-3"></i>
                {{ __('messages.hr_settings') }}
            </h1>
            
            <div class="mt-4 sm:mt-0">
                <button wire:click="create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md flex items-center transition duration-200 ease-in-out transform hover:scale-105">
                    <i class="fas fa-plus mr-2"></i>
                    {{ __('messages.add_setting') }}
                </button>
            </div>
        </div>
        
        <!-- Filtros e pesquisa -->
        <div class="mb-6 bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                <h2 class="text-base font-medium text-gray-700 flex items-center">
                    <i class="fas fa-filter text-blue-600 mr-2"></i>
                    {{ __('messages.filters') }}
                </h2>
            </div>
            
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                    <!-- Pesquisa -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.search') }}</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input wire:model.live.debounce.300ms="search" type="text" class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-3 pr-10 py-2 sm:text-sm border-gray-300 rounded-md" placeholder="{{ __('messages.search_settings') }}">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filtro por grupo -->
                <div class="flex flex-wrap gap-2 mt-4 border-t pt-4">
                    <button wire:click="filterByGroup('all')" class="{{ $activeGroup == 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} transition px-3 py-1 rounded-full text-sm font-medium">
                        {{ __('messages.all_groups') }}
                    </button>
                    
                    @foreach($groups as $groupKey => $groupName)
                    <button wire:click="filterByGroup('{{ $groupKey }}')" class="{{ $activeGroup == $groupKey ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} transition px-3 py-1 rounded-full text-sm font-medium">
                        {{ __('messages.hr_setting_group_'.$groupKey, ['default' => $groupName]) }}
                    </button>
                    @endforeach
                </div>
                
                <!-- Botão de reset -->
                <div class="mt-4 text-right">
                    <button wire:click="resetFilters" class="text-sm text-blue-600 hover:text-blue-800 flex items-center justify-end ml-auto">
                        <i class="fas fa-undo mr-1"></i>
                        {{ __('messages.reset_filters') }}
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Tabela de configurações -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('messages.key') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('messages.value') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('messages.group') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('messages.description') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('messages.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($settings as $setting)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $setting->is_system ? 'text-blue-600' : 'text-gray-900' }}">
                                    @if($setting->is_system)
                                        <i class="fas fa-shield-alt text-blue-500 mr-1" title="{{ __('messages.system_setting') }}"></i>
                                    @endif
                                    {{ $setting->key }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $setting->value }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ __('messages.hr_setting_group_'.$setting->group, ['default' => $groups[$setting->group] ?? $setting->group]) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 max-w-md truncate">
                                    {{ $setting->description }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button wire:click="edit({{ $setting->id }})" class="text-indigo-600 hover:text-indigo-900 mx-1" title="{{ __('messages.edit') }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    @if(!$setting->is_system)
                                        <button wire:click="confirmDelete({{ $setting->id }})" class="text-red-600 hover:text-red-900 mx-1" title="{{ __('messages.delete') }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center py-8">
                                        <i class="fas fa-database text-gray-300 text-3xl mb-2"></i>
                                        <p>{{ __('messages.no_settings_found') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <div class="px-6 py-3 border-t border-gray-200 bg-white">
                {{ $settings->links() }}
            </div>
        </div>
        
        <!-- Modal de Edição/Criação -->
        @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6">
                <div class="text-lg font-medium text-gray-900 flex items-center">
                    @if($isEditing)
                        <i class="fas fa-edit text-blue-500 mr-2"></i>
                        {{ __('messages.edit_setting') }}
                    @else
                        <i class="fas fa-plus-circle text-green-500 mr-2"></i>
                        {{ __('messages.new_setting') }}
                    @endif
                </div>

                <div class="mt-6">
                    <form wire:submit.prevent="save">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Chave da configuração -->
                            <div>
                                <label for="key" class="block text-sm font-medium text-gray-700">{{ __('messages.key') }}</label>
                                <input type="text" wire:model.live="key" id="key" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" {{ $isEditing && $isSystemSetting ? 'readonly' : '' }}>
                                @error('key') <div class="mt-1 text-red-600 text-sm">{{ $message }}</div> @enderror
                            </div>
                            
                            <!-- Grupo da configuração -->
                            <div>
                                <label for="group" class="block text-sm font-medium text-gray-700">{{ __('messages.group') }}</label>
                                <select wire:model.live="group" id="group" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" {{ $isEditing && $isSystemSetting ? 'disabled' : '' }}>
                                    @foreach($groups as $groupKey => $groupName)
                                        <option value="{{ $groupKey }}">{{ __('messages.hr_setting_group_'.$groupKey, ['default' => $groupName]) }}</option>
                                    @endforeach
                                </select>
                                @error('group') <div class="mt-1 text-red-600 text-sm">{{ $message }}</div> @enderror
                            </div>
                            
                            <!-- Valor da configuração -->
                            <div class="md:col-span-2">
                                <label for="value" class="block text-sm font-medium text-gray-700">{{ __('messages.value') }}</label>
                                <input type="text" wire:model.live="value" id="value" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @error('value') <div class="mt-1 text-red-600 text-sm">{{ $message }}</div> @enderror
                            </div>
                            
                            <!-- Descrição da configuração -->
                            <div class="md:col-span-2">
                                <label for="description" class="block text-sm font-medium text-gray-700">{{ __('messages.description') }}</label>
                                <textarea wire:model.live="description" id="description" rows="3" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" {{ $isEditing && $isSystemSetting ? 'readonly' : '' }}></textarea>
                                @error('description') <div class="mt-1 text-red-600 text-sm">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        
                        @if($isEditing && $isSystemSetting)
                            <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                                <p class="text-yellow-800 text-sm flex items-start">
                                    <i class="fas fa-info-circle mt-1 mr-2"></i>
                                    <span>{{ __('messages.system_setting_warning') }}</span>
                                </p>
                            </div>
                        @endif
                        
                        <!-- Botões de ação -->
                        <div class="flex justify-end mt-6 gap-3">
                            <button type="button" wire:click="closeModal" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-times mr-2"></i>
                                {{ __('messages.cancel') }}
                            </button>
                            
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-save mr-2"></i>
                                {{ __('messages.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Modal de exclusão -->
        @if($showDeleteModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            {{ __('messages.confirm_deletion') }}
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                {{ __('messages.confirm_delete_setting', ['setting' => $key]) }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button wire:click="delete" type="button" 
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        <i class="fas fa-trash mr-2"></i>
                        {{ __('messages.delete') }}
                    </button>
                    <button wire:click="closeDeleteModal" type="button" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        <i class="fas fa-times mr-2"></i>
                        {{ __('messages.cancel') }}
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
