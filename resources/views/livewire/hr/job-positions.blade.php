<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex items-center space-x-4">
                            <h2 class="text-xl font-semibold text-gray-800">
                                <i class="fas fa-briefcase mr-2 text-gray-600"></i>
                                {{ __('messages.job_positions') }}
                            </h2>
                            <x-hr-guide-link />
                        </div>
                        <button
                            wire:click="create"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                            <i class="fas fa-plus mr-1"></i>
                            {{ __('messages.add_position') }}
                        </button>
                    </div>

                    <!-- Filters and Search -->
                    <div class="mb-6 bg-white p-4 rounded-lg shadow-sm">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="md:col-span-2">
                                <label for="search" class="sr-only">{{ __('messages.search') }}</label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                    <input
                                        type="text"
                                        id="search"
                                        wire:model.live.debounce.300ms="search"
                                        placeholder="{{ __('messages.search_job_positions') }}"
                                        class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 pr-3 py-2 sm:text-sm border-gray-300 rounded-md"
                                    >
                                </div>
                            </div>

                            <div>
                                <label for="filterDepartment" class="sr-only">{{ __('messages.department') }}</label>
                                <select
                                    id="filterDepartment"
                                    wire:model.live="filters.department_id"
                                    class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                                    <option value="">{{ __('messages.all_departments') }}</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="filterCategory" class="sr-only">{{ __('messages.category') }}</label>
                                <select
                                    id="filterCategory"
                                    wire:model.live="filters.category_id"
                                    class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                                    <option value="">{{ __('messages.all_categories') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="filterActive" class="sr-only">{{ __('messages.status') }}</label>
                                <select
                                    id="filterActive"
                                    wire:model.live="filters.is_active"
                                    class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                                    <option value="">{{ __('messages.all_status') }}</option>
                                    <option value="1">{{ __('messages.active') }}</option>
                                    <option value="0">{{ __('messages.inactive') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Job Positions Table -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1 cursor-pointer" wire:click="sortBy('title')">
                                                <span>{{ __('messages.position') }}</span>
                                                @if($sortField === 'title')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                                @else
                                                    <i class="fas fa-sort"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1 cursor-pointer" wire:click="sortBy('department_id')">
                                                <span>{{ __('messages.department') }}</span>
                                                @if($sortField === 'department_id')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                                @else
                                                    <i class="fas fa-sort"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1">
                                                <span>{{ __('messages.category') }}</span>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1">
                                                <span>{{ __('messages.salary_range') }}</span>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1 cursor-pointer" wire:click="sortBy('is_active')">
                                                <span>{{ __('messages.status') }}</span>
                                                @if($sortField === 'is_active')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                                @else
                                                    <i class="fas fa-sort"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('messages.actions') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($positions as $position)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $position->title }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500">{{ $position->department->name ?? __('messages.not_available') }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500">{{ $position->category->name ?? __('messages.not_available') }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if($position->salary_range_min && $position->salary_range_max)
                                                    {{ number_format($position->salary_range_min, 2) }} - {{ number_format($position->salary_range_max, 2) }}
                                                @else
                                                    {{ __('messages.not_available') }}
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    {{ isset($position->is_active) && $position->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ isset($position->is_active) && $position->is_active ? __('messages.active') : __('messages.inactive') }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button
                                                    wire:click="edit({{ $position->id }})"
                                                    class="text-indigo-600 hover:text-indigo-900 mr-3"
                                                >
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button
                                                    wire:click="confirmDelete({{ $position->id }})"
                                                    class="text-red-600 hover:text-red-900"
                                                >
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-12 text-center">
                                                <div class="flex flex-col items-center justify-center text-gray-500">
                                                    <i class="fas fa-briefcase text-gray-400 text-4xl mb-4"></i>
                                                    <span class="text-lg font-medium">{{ __('messages.no_job_positions_found') }}</span>
                                                    <p class="text-sm mt-2">
                                                        @if($search || !empty($filters['department_id']) || !empty($filters['category_id']) || $filters['is_active'] !== '')
                                                            {{ __('messages.no_positions_match') }}
                                                            <button
                                                                wire:click="resetFilters"
                                                                class="text-blue-500 hover:text-blue-700 underline ml-1"
                                                            >
                                                                {{ __('messages.clear_all_filters') }}
                                                            </button>
                                                        @else
                                                            {{ __('messages.no_job_positions_yet') }}
                                                        @endif
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="px-4 py-3 bg-white border-t border-gray-200 sm:px-6">
                            @if(method_exists($positions, 'links'))
                                {{ $positions->links() }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Position Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">
                        <i class="fas {{ $isEditing ? 'fa-edit' : 'fa-plus-circle' }} mr-2"></i>
                        {{ $isEditing ? 'Edit' : 'Add' }} Job Position
                    </h3>
                    <button type="button" class="text-gray-500 hover:text-gray-700 text-xl" wire:click="closeModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
                        <p class="font-bold flex items-center">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            {{ __('messages.please_correct_errors') }}:
                        </p>
                        <ul class="mt-2 list-disc list-inside text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form wire:submit.prevent="save">
                    <div class="grid grid-cols-1 gap-4">
                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">{{ __('messages.position') }}</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="text" id="title"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('title') border-red-300 text-red-900 @enderror"
                                    wire:model.live="title">
                                @error('title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Department -->
                        <div>
                            <label for="department_id" class="block text-sm font-medium text-gray-700">{{ __('messages.department') }}</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <select id="department_id"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('department_id') border-red-300 text-red-900 @enderror"
                                    wire:model.live="department_id">
                                    <option value="">{{ __('messages.select_department') }}</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Category -->
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700">{{ __('messages.category') }}</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <select id="category_id"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('category_id') border-red-300 text-red-900 @enderror"
                                    wire:model.live="category_id">
                                    <option value="">{{ __('messages.select_category') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Salary Range Min -->
                        <div>
                            <label for="salary_range_min" class="block text-sm font-medium text-gray-700">{{ __('messages.minimum_salary') }}</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number" id="salary_range_min" step="0.01" min="0"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('salary_range_min') border-red-300 text-red-900 @enderror"
                                    wire:model.live="salary_range_min">
                                @error('salary_range_min')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Salary Range Max -->
                        <div>
                            <label for="salary_range_max" class="block text-sm font-medium text-gray-700">{{ __('messages.maximum_salary') }}</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number" id="salary_range_max" step="0.01" min="0"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('salary_range_max') border-red-300 text-red-900 @enderror"
                                    wire:model.live="salary_range_max">
                                @error('salary_range_max')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">{{ __('messages.description') }}</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <textarea id="description" rows="3"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('description') border-red-300 text-red-900 @enderror"
                                    wire:model.live="description"></textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Responsibilities -->
                        <div>
                            <label for="responsibilities" class="block text-sm font-medium text-gray-700">{{ __('messages.responsibilities') }}</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <textarea id="responsibilities" rows="3"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('responsibilities') border-red-300 text-red-900 @enderror"
                                    wire:model.live="responsibilities"></textarea>
                                @error('responsibilities')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Requirements -->
                        <div>
                            <label for="requirements" class="block text-sm font-medium text-gray-700">{{ __('messages.requirements') }}</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <textarea id="requirements" rows="3"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('requirements') border-red-300 text-red-900 @enderror"
                                    wire:model.live="requirements"></textarea>
                                @error('requirements')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Active Status -->
                        <div>
                            <div class="relative flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="is_active" type="checkbox"
                                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                                        wire:model.live="is_active">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="is_active" class="font-medium text-gray-700">{{ __('messages.active') }}</label>
                                    <p class="text-gray-500">{{ __('messages.set_position_active') }}</p>
                                </div>
                            </div>
                            @error('is_active')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
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
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-red-600">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        {{ __('messages.delete_position') }}
                    </h3>
                    <button type="button" class="text-gray-500 hover:text-gray-700 text-xl" wire:click="closeDeleteModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="mb-4">
                    <p class="text-gray-700">{{ __('messages.delete_position_confirmation') }}</p>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        wire:click="closeDeleteModal">
                        {{ __('messages.cancel') }}
                    </button>
                    <button type="button"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        wire:click="delete">
                        {{ __('messages.delete') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Flash Message -->
    @if (session()->has('message'))
        <div x-data="{ show: true }"
             x-init="setTimeout(() => show = false, 3000)"
             x-show="show"
             class="fixed bottom-4 right-4 bg-green-500 text-white py-2 px-4 rounded-md shadow-md">
            {{ session('message') }}
        </div>
    @endif
</div>
