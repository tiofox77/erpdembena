<div>
    <!-- JavaScript para Notificações -->
    <script>
        function showNotification(message, type = 'success') {
            if (window.toastr) {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-top-right',
                    timeOut: 5000
                };

                toastr[type](message);
            } else {
                alert(message);
            }
        }

        document.addEventListener('livewire:initialized', () => {
            Livewire.on('notify', (params) => {
                showNotification(params.message, params.type);
            });
        });
    </script>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Cabeçalho -->
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-user-shield text-gray-600 text-xl mr-3"></i>
                            <h1 class="text-2xl font-semibold text-gray-800">Gerenciamento de Funções e Permissões</h1>
                        </div>
                        <div class="flex items-center space-x-3">
                            <button
                                wire:click="openCreateRoleModal"
                                type="button"
                                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded-md flex items-center"
                            >
                                <i class="fas fa-plus mr-2"></i> Nova Função
                            </button>
                            <button
                                wire:click="openCreatePermissionModal"
                                type="button"
                                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded-md flex items-center"
                            >
                                <i class="fas fa-plus mr-2"></i> Nova Permissão
                            </button>
                        </div>
                    </div>

                    <!-- Navegação por abas -->
                    <div class="mb-6">
                        <ul class="flex border-b border-gray-200">
                            <li class="mr-1">
                                <a href="#"
                                    wire:click.prevent="$set('activeTab', 'roles')"
                                    class="{{ $activeTab === 'roles' ? 'bg-white border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700' }}
                                           py-3 px-6 inline-block border border-gray-200 rounded-t-md font-medium text-sm"
                                >
                                    <i class="fas fa-user-tag mr-2"></i> Funções
                                </a>
                            </li>
                            <li class="mr-1">
                                <a href="#"
                                    wire:click.prevent="$set('activeTab', 'permissions')"
                                    class="{{ $activeTab === 'permissions' ? 'bg-white border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700' }}
                                           py-3 px-6 inline-block border border-gray-200 rounded-t-md font-medium text-sm"
                                >
                                    <i class="fas fa-key mr-2"></i> Permissões
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Barra de pesquisa e filtros -->
                    <div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input
                                    wire:model.live="search"
                                    type="text"
                                    placeholder="{{ $activeTab === 'roles' ? 'Pesquisar funções...' : 'Pesquisar permissões...' }}"
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                            </div>

                            @if($activeTab === 'permissions')
                                <div>
                                    <select
                                        wire:model.live="filterPermissionGroup"
                                        class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                        <option value="">Todos os Módulos</option>
                                        @foreach($this->permissionGroupNames as $group)
                                            <option value="{{ $group }}">{{ ucfirst($group) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div>
                                <select
                                    wire:model.live="perPage"
                                    class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                                    <option value="10">10 por página</option>
                                    <option value="25">25 por página</option>
                                    <option value="50">50 por página</option>
                                    <option value="100">100 por página</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Funções -->
                    <div class="bg-white overflow-hidden rounded-lg shadow-sm border border-gray-200 {{ $activeTab === 'roles' ? '' : 'hidden' }}">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permissões</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($this->roles as $role)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-medium text-gray-900">{{ $role->name }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($role->permissions->take(5) as $permission)
                                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                                        {{ $permission->name }}
                                                    </span>
                                                @endforeach
                                                @if($role->permissions->count() > 5)
                                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                                                        +{{ $role->permissions->count() - 5 }} mais
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                            <button
                                                wire:click="editRole({{ $role->id }})"
                                                class="text-blue-600 hover:text-blue-900 mr-3"
                                                title="Editar"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            @if(!in_array($role->name, ['super-admin', 'admin']))
                                                <button
                                                    wire:click="confirmDelete({{ $role->id }}, 'role')"
                                                    class="text-red-600 hover:text-red-900"
                                                    title="Excluir"
                                                >
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-10 text-center">
                                            <div class="flex flex-col items-center justify-center space-y-3">
                                                <div class="h-16 w-16 text-gray-400 bg-gray-100 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-user-tag text-3xl"></i>
                                                </div>
                                                <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma função encontrada</h3>
                                                <p class="mt-1 text-sm text-gray-500">Comece criando sua primeira função.</p>
                                                <div class="mt-4">
                                                    <button
                                                        wire:click="openCreateRoleModal"
                                                        type="button"
                                                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                                    >
                                                        <i class="fas fa-plus mr-2"></i>
                                                        Nova Função
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="p-4 border-t border-gray-200">
                            {{ $this->roles->links() }}
                        </div>
                    </div>

                    <!-- Lista de Permissões -->
                    <div class="bg-white overflow-hidden rounded-lg shadow-sm border border-gray-200 {{ $activeTab === 'permissions' ? '' : 'hidden' }}">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Módulo</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($this->permissions as $permission)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-medium text-gray-900">{{ $permission->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $parts = explode('.', $permission->name);
                                                $module = $parts[0] ?? 'outro';
                                            @endphp
                                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                                                {{ ucfirst($module) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                            <button
                                                wire:click="editPermission({{ $permission->id }})"
                                                class="text-blue-600 hover:text-blue-900 mr-3"
                                                title="Editar"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button
                                                wire:click="confirmDelete({{ $permission->id }}, 'permission')"
                                                class="text-red-600 hover:text-red-900"
                                                title="Excluir"
                                            >
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-10 text-center">
                                            <div class="flex flex-col items-center justify-center space-y-3">
                                                <div class="h-16 w-16 text-gray-400 bg-gray-100 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-key text-3xl"></i>
                                                </div>
                                                <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma permissão encontrada</h3>
                                                <p class="mt-1 text-sm text-gray-500">Comece criando sua primeira permissão.</p>
                                                <div class="mt-4">
                                                    <button
                                                        wire:click="openCreatePermissionModal"
                                                        type="button"
                                                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                                    >
                                                        <i class="fas fa-plus mr-2"></i>
                                                        Nova Permissão
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="p-4 border-t border-gray-200">
                            {{ $this->permissions->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Criar/Editar Função -->
    @if($showRoleModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center">
            <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all w-full max-w-2xl">
                <!-- Cabeçalho do modal -->
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">
                        {{ $isEditing ? 'Editar Função' : 'Nova Função' }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Corpo do modal -->
                <div class="px-6 py-4">
                    @if($errors->any())
                        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
                            <p class="font-bold">Por favor, corrija os seguintes erros:</p>
                            <ul class="mt-2 list-disc list-inside text-sm">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form wire:submit.prevent="saveRole">
                        <div class="mb-4">
                            <label for="role-name" class="block text-sm font-medium text-gray-700">Nome da Função</label>
                            <input
                                type="text"
                                id="role-name"
                                wire:model.live="role.name"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('role.name') border-red-300 @enderror"
                                placeholder="ex: editor"
                            >
                            @error('role.name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Permissões</label>

                            @foreach($this->permissionGroups as $group => $permissions)
                                <div class="mb-4">
                                    <h4 class="text-sm font-semibold mb-2 bg-gray-100 p-2 rounded">
                                        {{ ucfirst($group) }}
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                        @foreach($permissions as $permission)
                                            <label class="inline-flex items-center text-sm">
                                                <input
                                                    type="checkbox"
                                                    value="{{ $permission->id }}"
                                                    wire:model.live="selectedPermissions"
                                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                                >
                                                <span class="ml-2">{{ $permission->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-5 flex justify-end">
                            <button
                                type="button"
                                wire:click="closeModal"
                                class="mr-3 inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                {{ $isEditing ? 'Atualizar' : 'Criar' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal para Criar/Editar Permissão -->
    @if($showPermissionModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center">
            <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all w-full max-w-lg">
                <!-- Cabeçalho do modal -->
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">
                        {{ $isEditing ? 'Editar Permissão' : 'Nova Permissão' }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Corpo do modal -->
                <div class="px-6 py-4">
                    @if($errors->any())
                        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
                            <p class="font-bold">Por favor, corrija os seguintes erros:</p>
                            <ul class="mt-2 list-disc list-inside text-sm">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form wire:submit.prevent="savePermission">
                        <div class="mb-4">
                            <label for="permission-name" class="block text-sm font-medium text-gray-700">Nome da Permissão</label>
                            <input
                                type="text"
                                id="permission-name"
                                wire:model.live="permission.name"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('permission.name') border-red-300 @enderror"
                                placeholder="ex: modulo.acao"
                            >
                            <p class="mt-1 text-xs text-gray-500">Recomendado usar o formato: modulo.acao (ex: equipment.view)</p>
                            @error('permission.name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="permission-guard" class="block text-sm font-medium text-gray-700">Guard</label>
                            <input
                                type="text"
                                id="permission-guard"
                                wire:model.live="permission.guard_name"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-gray-50 @error('permission.guard_name') border-red-300 @enderror"
                                readonly
                            >
                        </div>

                        <div class="mt-5 flex justify-end">
                            <button
                                type="button"
                                wire:click="closeModal"
                                class="mr-3 inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                {{ $isEditing ? 'Atualizar' : 'Criar' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de Confirmação de Exclusão -->
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center">
            <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all w-full max-w-md">
                <div class="bg-white px-6 py-4">
                    <div class="flex items-center justify-center">
                        <div class="h-12 w-12 rounded-full bg-red-100 flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-red-600 text-lg"></i>
                        </div>
                    </div>
                    <div class="mt-3 text-center">
                        <h3 class="text-lg font-medium text-gray-900">
                            Confirmar Exclusão
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Tem certeza que deseja excluir esta {{ $deleteType === 'role' ? 'função' : 'permissão' }}? Esta ação não pode ser desfeita.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-3 flex justify-end">
                    <button
                        type="button"
                        wire:click="closeModal"
                        class="mr-3 inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        wire:click="deleteConfirmed"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                    >
                        Excluir
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
