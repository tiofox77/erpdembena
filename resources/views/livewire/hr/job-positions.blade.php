<div class="min-h-screen bg-gray-50">
    {{-- Header com Gradiente --}}
    <div class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-800 shadow-lg">
        <div class="w-full px-6 py-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-white flex items-center">
                        <i class="fas fa-briefcase mr-3 text-indigo-200"></i>
                        {{ __('messages.job_positions') }}
                    </h1>
                    <p class="text-indigo-100 mt-2">{{ __('messages.manage_job_positions_description') }}</p>
                </div>
                <button
                    wire:click="create"
                    class="inline-flex items-center px-6 py-3 bg-white border border-transparent rounded-lg font-semibold text-indigo-600 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transition-all duration-200 ease-in-out transform hover:scale-105 shadow-lg"
                >
                    <i class="fas fa-plus mr-2"></i>
                    {{ __('messages.add_position') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Conteúdo Principal --}}
    <div class="w-full px-6 py-8">
        {{-- Filtros --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-filter text-indigo-600"></i>
                    <h3 class="text-lg font-semibold text-gray-800">{{ __('messages.filters_and_search') }}</h3>
                </div>
                @if($search || $filters['department_id'] || $filters['category_id'] || $filters['is_active'] !== '')
                    <button
                        wire:click="resetFilters"
                        class="text-sm text-gray-600 hover:text-indigo-600 flex items-center"
                    >
                        <i class="fas fa-undo mr-1"></i>
                        {{ __('messages.reset_filters') }}
                    </button>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Search --}}
                <div class="lg:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search text-gray-400 mr-1"></i>
                        {{ __('messages.search') }}
                    </label>
                    <input
                        type="text"
                        id="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="{{ __('messages.search_job_positions') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                    >
                </div>

                {{-- Department Filter --}}
                <div>
                    <label for="filterDepartment" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-building text-gray-400 mr-1"></i>
                        {{ __('messages.department') }}
                    </label>
                    <select
                        id="filterDepartment"
                        wire:model.live="filters.department_id"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                    >
                        <option value="">{{ __('messages.all_departments') }}</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Category Filter --}}
                <div>
                    <label for="filterCategory" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-tag text-gray-400 mr-1"></i>
                        {{ __('messages.category') }}
                    </label>
                    <select
                        id="filterCategory"
                        wire:model.live="filters.category_id"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                    >
                        <option value="">{{ __('messages.all_categories') }}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Filter --}}
                <div>
                    <label for="filterActive" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-toggle-on text-gray-400 mr-1"></i>
                        {{ __('messages.status') }}
                    </label>
                    <select
                        id="filterActive"
                        wire:model.live="filters.is_active"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                    >
                        <option value="">{{ __('messages.all_status') }}</option>
                        <option value="1">{{ __('messages.active') }}</option>
                        <option value="0">{{ __('messages.inactive') }}</option>
                    </select>
                </div>

                {{-- Per Page --}}
                <div>
                    <label for="perPage" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-list text-gray-400 mr-1"></i>
                        {{ __('messages.per_page') }}
                    </label>
                    <select
                        id="perPage"
                        wire:model.live="perPage"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                    >
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Tabela --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center space-x-1 cursor-pointer" wire:click="sortBy('title')">
                                    <i class="fas fa-briefcase text-gray-400"></i>
                                    <span>{{ __('messages.position') }}</span>
                                    @if($sortField === 'title')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-indigo-600"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center space-x-1 cursor-pointer" wire:click="sortBy('department_id')">
                                    <i class="fas fa-building text-gray-400"></i>
                                    <span>{{ __('messages.department') }}</span>
                                    @if($sortField === 'department_id')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-indigo-600"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-tag text-gray-400"></i>
                                    <span>{{ __('messages.category') }}</span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center space-x-1 cursor-pointer" wire:click="sortBy('is_active')">
                                    <i class="fas fa-toggle-on text-gray-400"></i>
                                    <span>{{ __('messages.status') }}</span>
                                    @if($sortField === 'is_active')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-indigo-600"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <i class="fas fa-cog text-gray-400 mr-1"></i>
                                {{ __('messages.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($positions as $position)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-semibold text-gray-900">{{ $position->title }}</div>
                                    @if($position->description)
                                        <div class="text-xs text-gray-500 mt-1 line-clamp-1">{{ $position->description }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center mr-2">
                                            <i class="fas fa-building text-indigo-600 text-xs"></i>
                                        </div>
                                        <div class="text-sm text-gray-900">{{ $position->department->name ?? __('messages.not_available') }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($position->category)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            <i class="fas fa-tag mr-1"></i>
                                            {{ $position->category->name }}
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-400">{{ __('messages.not_available') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                        {{ $position->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        <i class="fas fa-circle mr-1 text-xs"></i>
                                        {{ $position->is_active ? __('messages.active') : __('messages.inactive') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button
                                        wire:click="edit({{ $position->id }})"
                                        class="text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50 px-3 py-1 rounded-lg transition-all mr-2"
                                        title="{{ __('messages.edit') }}"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button
                                        wire:click="confirmDelete({{ $position->id }})"
                                        class="text-red-600 hover:text-red-900 hover:bg-red-50 px-3 py-1 rounded-lg transition-all"
                                        title="{{ __('messages.delete') }}"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="rounded-full bg-gray-100 p-6 mb-4">
                                            <i class="fas fa-briefcase text-gray-400 text-4xl"></i>
                                        </div>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('messages.no_job_positions_found') }}</h3>
                                        <p class="text-sm text-gray-500 mb-4">
                                            @if($search || $filters['department_id'] || $filters['category_id'] || $filters['is_active'] !== '')
                                                {{ __('messages.no_positions_match') }}
                                            @else
                                                {{ __('messages.no_job_positions_yet') }}
                                            @endif
                                        </p>
                                        @if($search || $filters['department_id'] || $filters['category_id'] || $filters['is_active'] !== '')
                                            <button
                                                wire:click="resetFilters"
                                                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all"
                                            >
                                                <i class="fas fa-undo mr-2"></i>
                                                {{ __('messages.clear_all_filters') }}
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginação --}}
            @if($positions->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $positions->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Criar/Editar --}}
    @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                {{-- Header --}}
                <div class="sticky top-0 bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 rounded-t-2xl">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-bold text-white flex items-center">
                            <i class="fas {{ $isEditing ? 'fa-edit' : 'fa-plus-circle' }} mr-3"></i>
                            {{ $isEditing ? __('messages.edit_position') : __('messages.add_position') }}
                        </h3>
                        <button type="button" class="text-white hover:text-gray-200 transition-all" wire:click="closeModal">
                            <i class="fas fa-times text-2xl"></i>
                        </button>
                    </div>
                </div>

                {{-- Erros --}}
                @if($errors->any())
                    <div class="mx-6 mt-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                            <p class="font-semibold text-red-800">{{ __('messages.please_correct_errors') }}</p>
                        </div>
                        <ul class="list-disc list-inside text-sm text-red-700 ml-6">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Form --}}
                <form wire:submit.prevent="save" class="p-6">
                    <div class="space-y-6">
                        {{-- Title --}}
                        <div>
                            <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-briefcase text-indigo-500 mr-1"></i>
                                {{ __('messages.position') }} *
                            </label>
                            <input type="text" id="title"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all @error('title') border-red-300 @enderror"
                                wire:model.live="title"
                                placeholder="{{ __('messages.enter_position_title') }}">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Department --}}
                            <div>
                                <label for="department_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-building text-indigo-500 mr-1"></i>
                                    {{ __('messages.department') }} *
                                </label>
                                <select id="department_id"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all @error('department_id') border-red-300 @enderror"
                                    wire:model.live="department_id">
                                    <option value="">{{ __('messages.select_department') }}</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <p class="mt-1 text-sm text-red-600 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Category --}}
                            <div>
                                <label for="category_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-tag text-indigo-500 mr-1"></i>
                                    {{ __('messages.category') }}
                                </label>
                                <select id="category_id"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all @error('category_id') border-red-300 @enderror"
                                    wire:model.live="category_id">
                                    <option value="">{{ __('messages.select_category') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <p class="mt-1 text-sm text-red-600 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        {{-- Description --}}
                        <div>
                            <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-align-left text-indigo-500 mr-1"></i>
                                {{ __('messages.description') }}
                            </label>
                            <textarea id="description" rows="3"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all @error('description') border-red-300 @enderror"
                                wire:model.live="description"
                                placeholder="{{ __('messages.enter_position_description') }}"></textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Responsibilities --}}
                        <div>
                            <label for="responsibilities" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-tasks text-indigo-500 mr-1"></i>
                                {{ __('messages.responsibilities') }}
                            </label>
                            <textarea id="responsibilities" rows="3"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all @error('responsibilities') border-red-300 @enderror"
                                wire:model.live="responsibilities"
                                placeholder="{{ __('messages.enter_responsibilities') }}"></textarea>
                            @error('responsibilities')
                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Requirements --}}
                        <div>
                            <label for="requirements" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-graduation-cap text-indigo-500 mr-1"></i>
                                {{ __('messages.requirements') }}
                            </label>
                            <textarea id="requirements" rows="3"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all @error('requirements') border-red-300 @enderror"
                                wire:model.live="requirements"
                                placeholder="{{ __('messages.enter_requirements') }}"></textarea>
                            @error('requirements')
                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Active Status --}}
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <input id="is_active" type="checkbox"
                                    class="h-5 w-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                    wire:model.live="is_active">
                                <label for="is_active" class="ml-3">
                                    <span class="text-sm font-semibold text-gray-700">{{ __('messages.active') }}</span>
                                    <p class="text-xs text-gray-500">{{ __('messages.set_position_active') }}</p>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Botões --}}
                    <div class="flex justify-end space-x-3 mt-8 pt-6 border-t">
                        <button type="button"
                            class="px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all"
                            wire:click="closeModal">
                            <i class="fas fa-times mr-2"></i>
                            {{ __('messages.cancel') }}
                        </button>
                        <button type="submit"
                            wire:loading.attr="disabled"
                            wire:target="save"
                            class="px-6 py-3 border border-transparent rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all disabled:opacity-50">
                            <span wire:loading.remove wire:target="save">
                                <i class="fas fa-save mr-2"></i>
                                {{ $isEditing ? __('messages.update') : __('messages.save') }}
                            </span>
                            <span wire:loading wire:target="save" class="flex items-center">
                                <svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ __('messages.saving') }}...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal Delete --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
                <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4 rounded-t-2xl">
                    <div class="flex items-center text-white">
                        <i class="fas fa-exclamation-triangle text-2xl mr-3"></i>
                        <h3 class="text-xl font-bold">{{ __('messages.delete_position') }}</h3>
                    </div>
                </div>

                <div class="p-6">
                    <p class="text-gray-700 mb-6">{{ __('messages.delete_position_confirmation') }}</p>

                    <div class="flex justify-end space-x-3">
                        <button type="button"
                            class="px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all"
                            wire:click="closeDeleteModal">
                            <i class="fas fa-times mr-2"></i>
                            {{ __('messages.cancel') }}
                        </button>
                        <button type="button"
                            wire:click="delete"
                            wire:loading.attr="disabled"
                            wire:target="delete"
                            class="px-6 py-3 border border-transparent rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all disabled:opacity-50">
                            <span wire:loading.remove wire:target="delete">
                                <i class="fas fa-trash mr-2"></i>
                                {{ __('messages.delete') }}
                            </span>
                            <span wire:loading wire:target="delete" class="flex items-center">
                                <svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ __('messages.deleting') }}...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('success', (message) => {
            toastr.success(message);
        });

        Livewire.on('error', (message) => {
            toastr.error(message);
        });
    });
</script>
@endpush
