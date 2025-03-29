<div>
    <!-- Estrutura básica -->
    <div class="container mx-auto py-6">
        <!-- Cabeçalho e botões de ação -->
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold">Failure Modes Management</h1>
            <div>
                <button wire:click="openCreateModal" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md">
                    <i class="fas fa-plus-circle mr-1"></i> Add Mode
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
                        placeholder="Search modes...">
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
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($this->modes as $mode)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $mode->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $mode->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $mode->category->name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $mode->description ?: '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $mode->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $mode->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="edit({{ $mode->id }})" class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button wire:click="confirmDelete({{ $mode->id }})" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <!-- Estado vazio -->
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center">
                                <div class="flex flex-col items-center justify-center py-8">
                                    <i class="fas fa-folder-open text-gray-400 text-4xl mb-3"></i>
                                    <p class="text-gray-500 mb-3">No failure modes found</p>
                                    <button wire:click="openCreateModal" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md">
                                        Add your first mode
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <div class="mt-4">
            {{ $this->modes->links() }}
        </div>

        <!-- Modal de Criação/Edição -->
        @if($showModal)
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                    <!-- Cabeçalho do modal -->
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium">
                            <i class="fas {{ $isEditing ? 'fa-edit' : 'fa-plus-circle' }} mr-2"></i>
                            {{ $isEditing ? 'Edit' : 'Create' }} Failure Mode
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
                            <label for="mode-category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select id="mode-category"
                                class="block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm
                                @error('mode.category_id') border-red-300 text-red-900 @enderror"
                                wire:model.live="mode.category_id">
                                <option value="">Select Category</option>
                                @foreach($this->allCategories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('mode.category_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="mode-name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="text" id="mode-name"
                                    class="block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm
                                    @error('mode.name') border-red-300 text-red-900 @enderror"
                                    wire:model.live="mode.name">
                                @error('mode.name')
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                    </div>
                                @enderror
                            </div>
                            @error('mode.name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="mode-description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea id="mode-description" rows="3"
                                class="block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                wire:model.live="mode.description"></textarea>
                        </div>

                        <div class="mb-4">
                            <div class="flex items-center">
                                <input type="checkbox" id="mode-active"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                    wire:model.live="mode.is_active">
                                <label for="mode-active" class="ml-2 block text-sm text-gray-700">Active</label>
                            </div>
                        </div>

                        <!-- Botões de ação -->
                        <div class="flex justify-end space-x-2">
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
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium">
                            <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                            Confirm Deletion
                        </h3>
                        <button wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="mb-6">
                        <p class="text-gray-700">Are you sure you want to delete this failure mode? This action cannot be undone.</p>
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button wire:click="closeModal"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </button>
                        <button wire:click="deleteConfirmed"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <i class="fas fa-trash-alt mr-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- JavaScript para Notificações -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('notify', (params) => {
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