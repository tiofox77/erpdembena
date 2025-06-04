<div>
    <div class="container mx-auto py-4">
        <!-- Header com título e botão de adicionar -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">{{ trans('messages.unit_types') }}</h1>
            <button wire:click="create" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 focus:outline-none">
                <i class="fas fa-plus mr-1"></i> {{ trans('messages.add_unit_type') }}
            </button>
        </div>

        <!-- Filtros e pesquisa -->
        <div class="flex flex-col md:flex-row justify-between mb-4 space-y-2 md:space-y-0">
            <div class="flex items-center space-x-2">
                <select wire:model.live="filter" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="all">{{ trans('messages.all_categories') }}</option>
                    <option value="active">{{ trans('messages.active_only') }}</option>
                    <option value="inactive">{{ trans('messages.inactive_only') }}</option>
                    <optgroup label="{{ trans('messages.categories') }}">
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </optgroup>
                </select>
            </div>
            <div class="flex items-center space-x-2">
                <div class="relative">
                    <input type="text" 
                           wire:model.live.debounce.300ms="searchTerm"
                           placeholder="{{ trans('messages.search_unit_types') }}" 
                           class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm pl-10 py-2 w-full md:w-64">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </span>
                </div>
            </div>
        </div>

        <!-- Tabela de tipos de unidades -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.name') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.symbol') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.category') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.description') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.status') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($unitTypes as $unitType)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $unitType->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $unitType->symbol }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ $categories[$unitType->category] ?? $unitType->category }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 max-w-xs truncate">
                                    {{ $unitType->description ?: trans('messages.no_description') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $unitType->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $unitType->is_active ? trans('messages.active') : trans('messages.inactive') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <button wire:click="edit({{ $unitType->id }})" class="text-indigo-600 hover:text-indigo-900" title="{{ trans('messages.edit') }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button wire:click="toggleStatus({{ $unitType->id }})" class="{{ $unitType->is_active ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' }}" title="{{ $unitType->is_active ? trans('messages.deactivate') : trans('messages.activate') }}">
                                        <i class="fas {{ $unitType->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                    </button>
                                    <button wire:click="confirmDelete({{ $unitType->id }})" class="text-red-600 hover:text-red-900" title="{{ trans('messages.delete') }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                {{ trans('messages.no_unit_types_found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <div class="mt-4">
            {{ $unitTypes->links() }}
        </div>

        <!-- Modal de criação/edição -->
        @if($isOpen)
            <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4 z-50">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                    <div class="flex justify-between items-center p-6 border-b">
                        <h3 class="text-lg font-medium text-gray-900">
                            {{ $unit_type_id ? trans('messages.edit_unit_type') : trans('messages.add_unit_type') }}
                        </h3>
                        <button wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form wire:submit.prevent="save">
                        <div class="p-6 space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">{{ trans('messages.name') }} <span class="text-red-500">*</span></label>
                                <input type="text" id="name" wire:model="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="symbol" class="block text-sm font-medium text-gray-700">{{ trans('messages.symbol') }} <span class="text-red-500">*</span></label>
                                <input type="text" id="symbol" wire:model="symbol" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('symbol') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700">{{ trans('messages.category') }} <span class="text-red-500">*</span></label>
                                <select id="category" wire:model="category" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @foreach($categories as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('category') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">{{ trans('messages.description') }}</label>
                                <textarea id="description" wire:model="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" id="is_active" wire:model="is_active" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <label for="is_active" class="ml-2 block text-sm text-gray-900">{{ trans('messages.active') }}</label>
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 text-right rounded-b-lg">
                            <button type="button" wire:click="closeModal" class="px-4 py-2 bg-gray-500 text-white rounded mr-2 hover:bg-gray-600">
                                {{ trans('messages.cancel') }}
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                {{ trans('messages.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Modal de confirmação de exclusão -->
        @if($isDeleteModalOpen)
            <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4 z-50">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ trans('messages.confirm_delete') }}</h3>
                        <p class="text-gray-700">{{ trans('messages.unit_type_delete_confirmation') }}</p>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 text-right rounded-b-lg">
                        <button type="button" wire:click="closeDeleteModal" class="px-4 py-2 bg-gray-500 text-white rounded mr-2 hover:bg-gray-600">
                            {{ trans('messages.cancel') }}
                        </button>
                        <button type="button" wire:click="delete" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                            {{ trans('messages.delete') }}
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
