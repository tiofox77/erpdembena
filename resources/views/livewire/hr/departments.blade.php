<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex items-center space-x-4">
                            <h2 class="text-xl font-semibold text-gray-800">
                                <i class="fas fa-building mr-2 text-gray-600"></i>
                                {{ __('messages.departments_management') }}
                            </h2>
                            <x-hr-guide-link />
                        </div>
                        <button
                            wire:click="create"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                            <i class="fas fa-plus mr-1"></i>
                            Add Department
                        </button>
                    </div>

                    <!-- Filters and Search -->
                    <div class="mb-6 bg-white p-4 rounded-lg shadow-sm">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="md:col-span-3">
                                <label for="search" class="sr-only">{{ __('messages.search') }}</label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                    <input
                                        type="text"
                                        id="search"
                                        wire:model.live.debounce.300ms="search"
                                        placeholder="{{ __('messages.search_departments') }}"
                                        class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 pr-3 py-2 sm:text-sm border-gray-300 rounded-md"
                                    >
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button
                                    wire:click="resetFilters"
                                    class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150"
                                >
                                    <i class="fas fa-redo mr-2"></i>
                                    {{ __('messages.reset') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Departments Table -->
                    <div class="overflow-x-auto bg-white rounded-lg shadow overflow-y-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('name')">
                                        <div class="flex items-center">
                                            {{ __('messages.department_name') }}
                                            @if($sortField === 'name')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                            @else
                                            <i class="fas fa-sort ml-1 text-gray-400"></i>
                                            @endif
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('messages.description') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('messages.manager') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('messages.status') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('messages.actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($departments as $department)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $department->name }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $department->description ?? __('messages.not_available') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $department->manager ? $department->manager->full_name : __('messages.not_available') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $department->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $department->is_active ? __('messages.active') : __('messages.inactive') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button wire:click="edit({{ $department->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button wire:click="confirmDelete({{ $department->id }})" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center py-6">
                                            <i class="fas fa-building text-3xl text-gray-400 mb-3"></i>
                                            <p>{{ __('messages.no_departments_found') }}</p>
                                            <button 
                                                wire:click="create"
                                                class="mt-3 px-4 py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                            >
                                                {{ __('messages.add_department') }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $departments->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Department Modal -->
    @if($showModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">
                    <i class="fas {{ $isEditing ? 'fa-edit' : 'fa-plus-circle' }} mr-2"></i>
                    {{ $isEditing ? __('messages.edit') : __('messages.add') }} {{ __('messages.department') }}
                </h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form wire:submit.prevent="save" class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">{{ __('messages.department_name') }}</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <input type="text" id="name" wire:model="name" 
                            class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-300 text-red-900 @enderror"
                            placeholder="{{ __('messages.enter_department_name') }}">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">{{ __('messages.description') }}</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <textarea id="description" wire:model="description" rows="3"
                            class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('description') border-red-300 text-red-900 @enderror"
                            placeholder="{{ __('messages.enter_department_description') }}"></textarea>
                        @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="manager_id" class="block text-sm font-medium text-gray-700">{{ __('messages.department_manager') }}</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <select id="manager_id" wire:model="manager_id" 
                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('manager_id') border-red-300 text-red-900 @enderror">
                            <option value="">{{ __('messages.select_manager') }}</option>
                            @foreach($employees ?? [] as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                            @endforeach
                        </select>
                        @error('manager_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-start mt-4">
                    <div class="flex items-center h-5">
                        <input id="is_active" wire:model="is_active" type="checkbox" 
                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="is_active" class="font-medium text-gray-700">{{ __('messages.active') }}</label>
                        <p class="text-gray-500">{{ __('messages.set_department_active') }}</p>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        wire:click="closeModal">
                        {{ __('messages.cancel') }}
                    </button>
                    <button type="submit"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        {{ $isEditing ? __('messages.update') : __('messages.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
            <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        {{ __('messages.delete_department') }}
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            {{ __('messages.delete_department_confirmation') }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                <button wire:click="delete" type="button" 
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                    {{ __('messages.delete') }}
                </button>
                <button wire:click="closeDeleteModal" type="button" 
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    {{ __('messages.cancel') }}
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
