<div>
    <!-- Estrutura básica -->
    <div class="container mx-auto py-6">
        <!-- Cabeçalho e botões de ação -->
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold">Failure Cause Categories Management</h1>
            <div>
                <button wire:click="openCreateModal" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md">
                    <i class="fas fa-plus-circle mr-1"></i> Add Category
                </button>
            </div>
        </div>

        <!-- Barra de pesquisa e filtros -->
        <div class="mb-6 flex items-center justify-between">
            <div class="w-full max-w-md">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search"
                        class="block w-full rounded-md border-gray-300 pl-10 py-2 focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        placeholder="Search categories...">
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <label for="perPage" class="text-sm font-medium text-gray-700">Show:</label>
                <select id="perPage" wire:model.live="perPage" class="border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 sm:text-sm">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>

        <!-- Tabela com dados -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <!-- Cabeçalho da tabela -->
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($this->categories as $category)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $category->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $category->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $category->description ?: '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="edit({{ $category->id }})" class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button wire:click="confirmDelete({{ $category->id }})" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="flex flex-col items-center justify-center py-8">
                                    <i class="fas fa-folder-open text-gray-400 text-4xl mb-3"></i>
                                    <p class="text-gray-500 mb-3">No categories found</p>
                                    <button wire:click="openCreateModal" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md">
                                        <i class="fas fa-plus-circle mr-1"></i> Add your first category
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <!-- Paginação -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $this->categories->links() }}
            </div>
        </div>
    </div>

    <!-- Modal de criação/edição -->
    @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <!-- Cabeçalho do modal -->
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">
                        <i class="fas {{ $isEditing ? 'fa-edit' : 'fa-plus-circle' }} mr-2"></i>
                        {{ $isEditing ? 'Edit' : 'Create' }} Failure Cause Category
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Sumário de erros -->
                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
                        <p class="font-bold"><i class="fas fa-exclamation-circle mr-2"></i>Please correct the following errors:</p>
                        <ul class="mt-2 list-disc list-inside text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Formulário -->
                <form wire:submit.prevent="save">
                    <div class="mb-4">
                        <label for="category-name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input type="text" id="category-name"
                                class="block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm
                                @error('category.name') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                                wire:model.live="category.name">
                            @error('category.name')
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <i class="fas fa-exclamation-circle text-red-500"></i>
                                </div>
                            @enderror
                        </div>
                        @error('category.name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="category-description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <div class="mt-1">
                            <textarea id="category-description" rows="3"
                                class="block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm
                                @error('category.description') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                                wire:model.live="category.description"></textarea>
                        </div>
                        @error('category.description')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <div class="flex items-center">
                            <input type="checkbox" id="category-is-active"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                wire:model.live="category.is_active">
                            <label for="category-is-active" class="ml-2 block text-sm text-gray-700">
                                Active
                            </label>
                        </div>
                    </div>

                    <!-- Botões de ação -->
                    <div class="flex justify-end space-x-2 mt-6">
                        <button type="button" wire:click="closeModal"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </button>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas {{ $isEditing ? 'fa-save' : 'fa-plus' }} mr-1"></i>
                            {{ $isEditing ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal de confirmação de exclusão -->
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <div class="flex items-center justify-center text-red-600 mb-4">
                    <i class="fas fa-exclamation-circle text-5xl"></i>
                </div>
                <h3 class="text-xl font-bold text-center mb-2">Confirm Deletion</h3>
                <p class="text-center text-gray-600 mb-6">
                    Are you sure you want to delete this category? This action cannot be undone.
                </p>
                <div class="flex justify-center space-x-3">
                    <button wire:click="closeModal" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </button>
                    <button wire:click="deleteConfirmed" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <i class="fas fa-trash-alt mr-1"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- JavaScript para Notificações -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('notify', (params) => {
                if (window.toastr) {
                    toastr.options = {
                        closeButton: true,
                        progressBar: true,
                        positionClass: 'toast-top-right',
                        timeOut: 5000
                    };

                    // Exibir notificação baseada no tipo
                    if (params.type === 'success') {
                        toastr.success(params.message, params.title || 'Success');
                    } else if (params.type === 'error') {
                        toastr.error(params.message, params.title || 'Error');
                    } else if (params.type === 'warning') {
                        toastr.warning(params.message, params.title || 'Warning');
                    } else {
                        toastr.info(params.message, params.title || 'Information');
                    }
                } else {
                    alert(params.message);
                }
            });
        });
    </script>
</div>