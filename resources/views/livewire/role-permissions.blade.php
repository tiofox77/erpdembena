<div>


    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Cabeçalho -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                        <div class="flex items-center mb-4 sm:mb-0">
                            <i class="fas fa-user-shield text-gray-600 text-xl mr-3"></i>
                            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Gerenciamento de Funções e Permissões</h1>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <button
                                wire:click="openCreateRoleModal"
                                type="button"
                                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded-md flex items-center"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-75 cursor-wait"
                            >
                                <i class="fas fa-plus mr-2" wire:loading.class="hidden" wire:target="openCreateRoleModal"></i>
                                <i class="fas fa-spinner fa-spin mr-2 hidden" wire:loading.class.remove="hidden" wire:target="openCreateRoleModal"></i>
                                <span wire:loading.remove wire:target="openCreateRoleModal">Nova Função</span>
                                <span wire:loading wire:target="openCreateRoleModal">Carregando...</span>
                            </button>
                            <button
                                wire:click="openCreatePermissionModal"
                                type="button"
                                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded-md flex items-center"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-75 cursor-wait"
                            >
                                <i class="fas fa-plus mr-2" wire:loading.class="hidden" wire:target="openCreatePermissionModal"></i>
                                <i class="fas fa-spinner fa-spin mr-2 hidden" wire:loading.class.remove="hidden" wire:target="openCreatePermissionModal"></i>
                                <span wire:loading.remove wire:target="openCreatePermissionModal">Nova Permissão</span>
                                <span wire:loading wire:target="openCreatePermissionModal">Carregando...</span>
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
                                           py-3 px-6 inline-block border border-gray-200 rounded-t-md font-medium text-sm flex items-center"
                                >
                                    <i class="fas fa-user-tag mr-2"></i> Funções
                                </a>
                            </li>
                            <li class="mr-1">
                                <a href="#"
                                    wire:click.prevent="$set('activeTab', 'permissions')"
                                    class="{{ $activeTab === 'permissions' ? 'bg-white border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700' }}
                                           py-3 px-6 inline-block border border-gray-200 rounded-t-md font-medium text-sm flex items-center"
                                >
                                    <i class="fas fa-key mr-2"></i> Permissões
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Barra de pesquisa e filtros -->
                    <div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                        <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                            <i class="fas fa-filter mr-2 text-blue-500"></i> Filtros e Pesquisa
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="search" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-search mr-1 text-gray-500"></i> Pesquisar
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                    <input
                                        wire:model.live.debounce.300ms="search"
                                        id="search"
                                        type="text"
                                        placeholder="{{ $activeTab === 'roles' ? 'Pesquisar funções...' : 'Pesquisar permissões...' }}"
                                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                </div>
                            </div>

                            @if($activeTab === 'permissions')
                                <div>
                                    <label for="filterPermissionGroup" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                        <i class="fas fa-folder mr-1 text-gray-500"></i> Módulo
                                    </label>
                                    <div class="relative rounded-md shadow-sm">
                                        <select
                                            wire:model.live="filterPermissionGroup"
                                            id="filterPermissionGroup"
                                            class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        >
                                            <option value="">Todos os Módulos</option>
                                            @foreach($this->permissionGroupNames as $group)
                                                <option value="{{ $group }}">{{ ucfirst($group) }}</option>
                                            @endforeach
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div>
                                <label for="perPage" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <i class="fas fa-list-ol mr-1 text-gray-500"></i> Itens por página
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <select
                                        wire:model.live="perPage"
                                        id="perPage"
                                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                        <option value="10">10 por página</option>
                                        <option value="25">25 por página</option>
                                        <option value="50">50 por página</option>
                                        <option value="100">100 por página</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Funções -->
                    <div class="bg-white overflow-hidden rounded-lg shadow-sm border border-gray-200 {{ $activeTab === 'roles' ? '' : 'hidden' }}">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <div class="flex items-center space-x-1">
                                            <i class="fas fa-tag text-gray-400 mr-1"></i>
                                            <span>Nome</span>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <div class="flex items-center space-x-1">
                                            <i class="fas fa-key text-gray-400 mr-1"></i>
                                            <span>Permissões</span>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <div class="flex items-center justify-end space-x-1">
                                            <i class="fas fa-cog text-gray-400 mr-1"></i>
                                            <span>Ações</span>
                                        </div>
                                    </th>
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
                                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 flex items-center">
                                                        <i class="fas fa-check-circle mr-1 text-xs"></i>
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
                                            <div class="flex justify-end space-x-1 sm:space-x-2">
                                                <button
                                                    wire:click="editRole({{ $role->id }})"
                                                    class="text-blue-600 hover:text-blue-900 mr-3"
                                                    title="Editar"
                                                    wire:loading.attr="disabled"
                                                >
                                                    <i class="fas fa-edit w-4 h-4 sm:w-5 sm:h-5" wire:loading.class="hidden" wire:target="editRole({{ $role->id }})"></i>
                                                    <i class="fas fa-spinner fa-spin w-4 h-4 sm:w-5 sm:h-5 hidden" wire:loading.class.remove="hidden" wire:target="editRole({{ $role->id }})"></i>
                                                </button>
                                                @if(!in_array($role->name, ['super-admin', 'admin']))
                                                    <button
                                                        wire:click="confirmDelete({{ $role->id }}, 'role')"
                                                        class="text-red-600 hover:text-red-900"
                                                        title="Excluir"
                                                        wire:loading.attr="disabled"
                                                    >
                                                        <i class="fas fa-trash w-4 h-4 sm:w-5 sm:h-5" wire:loading.class="hidden" wire:target="confirmDelete({{ $role->id }}, 'role')"></i>
                                                        <i class="fas fa-spinner fa-spin w-4 h-4 sm:w-5 sm:h-5 hidden" wire:loading.class.remove="hidden" wire:target="confirmDelete({{ $role->id }}, 'role')"></i>
                                                    </button>
                                                @endif
                                            </div>
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
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <div class="flex items-center space-x-1">
                                            <i class="fas fa-key text-gray-400 mr-1"></i>
                                            <span>Nome</span>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <div class="flex items-center space-x-1">
                                            <i class="fas fa-folder text-gray-400 mr-1"></i>
                                            <span>Módulo</span>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <div class="flex items-center justify-end space-x-1">
                                            <i class="fas fa-cog text-gray-400 mr-1"></i>
                                            <span>Ações</span>
                                        </div>
                                    </th>
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
                                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 flex items-center inline-block">
                                                <i class="fas fa-folder mr-1"></i>
                                                {{ ucfirst($module) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                            <div class="flex justify-end space-x-1 sm:space-x-2">
                                                <button
                                                    wire:click="editPermission({{ $permission->id }})"
                                                    class="text-blue-600 hover:text-blue-900 mr-3"
                                                    title="Editar"
                                                    wire:loading.attr="disabled"
                                                >
                                                    <i class="fas fa-edit w-4 h-4 sm:w-5 sm:h-5" wire:loading.class="hidden" wire:target="editPermission({{ $permission->id }})"></i>
                                                    <i class="fas fa-spinner fa-spin w-4 h-4 sm:w-5 sm:h-5 hidden" wire:loading.class.remove="hidden" wire:target="editPermission({{ $permission->id }})"></i>
                                                </button>
                                                <button
                                                    wire:click="confirmDelete({{ $permission->id }}, 'permission')"
                                                    class="text-red-600 hover:text-red-900"
                                                    title="Excluir"
                                                    wire:loading.attr="disabled"
                                                >
                                                    <i class="fas fa-trash w-4 h-4 sm:w-5 sm:h-5" wire:loading.class="hidden" wire:target="confirmDelete({{ $permission->id }}, 'permission')"></i>
                                                    <i class="fas fa-spinner fa-spin w-4 h-4 sm:w-5 sm:h-5 hidden" wire:loading.class.remove="hidden" wire:target="confirmDelete({{ $permission->id }}, 'permission')"></i>
                                                </button>
                                            </div>
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

    <!-- Modal de Criação/Edição de Função -->
    @if($showRoleModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center overflow-y-auto p-2 sm:p-4">
            <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all w-full max-w-3xl max-h-[90vh] overflow-y-auto">
                <!-- Cabeçalho do modal -->
                <div class="bg-gray-50 px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 flex justify-between items-center sticky top-0 z-10">
                    <h3 class="text-base sm:text-lg font-medium text-gray-900 flex items-center">
                        <span class="bg-blue-100 text-blue-600 p-2 rounded-full mr-3">
                            <i class="fas {{ $isEditing ? 'fa-edit' : 'fa-plus' }} text-lg"></i>
                        </span>
                        {{ $isEditing ? 'Editar Função' : 'Nova Função' }}
                    </h3>
                    <button wire:click="$set('showRoleModal', false)" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Corpo do modal -->
                <div class="px-4 sm:px-6 py-4">
                    @if($errors->any())
                        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                            <p class="font-bold flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                Por favor, corrija os seguintes erros:
                            </p>
                            <ul class="mt-2 list-disc list-inside text-sm">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="space-y-6">
                        <!-- Nome da Função -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                                <i class="fas fa-tag mr-2 text-blue-500"></i> Informações da Função
                            </h4>
                            <div class="rounded-md border border-gray-300 p-4 bg-gray-50">
                                <label for="roleName" class="block text-sm font-medium text-gray-700 mb-1">Nome da Função</label>
                                <input
                                    id="roleName"
                                    type="text"
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('role.name') border-red-300 text-red-900 @enderror"
                                    wire:model="role.name"
                                    placeholder="Digite o nome da função"
                                >
                                @error('role.name')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Permissões -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                                <i class="fas fa-key mr-2 text-blue-500"></i> Permissões Associadas
                            </h4>
                            <div class="rounded-md border border-gray-300 p-4 bg-gray-50 max-h-80 overflow-y-auto">
                                <div class="mb-4">
                                    <input
                                        type="text"
                                        wire:model.live.debounce.300ms="permissionSearch"
                                        placeholder="Filtrar permissões..."
                                        class="block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach($this->permissionGroups as $group => $permissions)
                                        <div class="bg-white p-3 rounded-md shadow-sm">
                                            <h5 class="font-medium text-sm text-gray-700 mb-2 pb-1 border-b flex items-center">
                                                <i class="fas fa-folder mr-2 text-blue-400"></i>
                                                {{ ucfirst($group) }}
                                            </h5>

                                            <div class="space-y-2 max-h-40 overflow-y-auto pr-1">
                                                @foreach($permissions as $permission)
                                                    <label class="flex items-center space-x-3 py-1 hover:bg-gray-50 px-2 rounded-md cursor-pointer">
                                                        <input
                                                            type="checkbox"
                                                            value="{{ $permission->id }}"
                                                            wire:model="selectedPermissions"
                                                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                                        >
                                                        <span class="text-sm text-gray-800">{{ $permission->name }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @error('selectedPermissions')
                                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-4 sm:px-6 py-3 bg-gray-50 flex justify-end space-x-3 border-t border-gray-200">
                    <button
                        type="button"
                        wire:click="$set('showRoleModal', false)"
                        wire:loading.attr="disabled"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150 flex items-center"
                    >
                        <i class="fas fa-times mr-1"></i>
                        Cancelar
                    </button>

                    <button
                        type="button"
                        wire:click="saveRole"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-75 cursor-wait"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150 flex items-center"
                    >
                        <i class="fas fa-save mr-1" wire:loading.class="hidden" wire:target="saveRole"></i>
                        <i class="fas fa-spinner fa-spin mr-1 hidden" wire:loading.class.remove="hidden" wire:target="saveRole"></i>
                        <span wire:loading.remove wire:target="saveRole">Salvar</span>
                        <span wire:loading wire:target="saveRole">Salvando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de Criação/Edição de Permissão -->
    @if($showPermissionModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center overflow-y-auto p-2 sm:p-4">
            <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <!-- Cabeçalho do modal -->
                <div class="bg-gray-50 px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 flex justify-between items-center sticky top-0 z-10">
                    <h3 class="text-base sm:text-lg font-medium text-gray-900 flex items-center">
                        <span class="bg-blue-100 text-blue-600 p-2 rounded-full mr-3">
                            <i class="fas {{ $isEditing ? 'fa-edit' : 'fa-plus' }} text-lg"></i>
                        </span>
                        {{ $isEditing ? 'Editar Permissão' : 'Nova Permissão' }}
                    </h3>
                    <button wire:click="$set('showPermissionModal', false)" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Corpo do modal -->
                <div class="px-4 sm:px-6 py-4">
                    @if($errors->any())
                        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                            <p class="font-bold flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                Por favor, corrija os seguintes erros:
                            </p>
                            <ul class="mt-2 list-disc list-inside text-sm">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="space-y-6">
                        <div class="rounded-md border border-gray-300 p-4 bg-gray-50">
                            <div class="mb-4">
                                <label for="permissionName" class="block text-sm font-medium text-gray-700 mb-1">Nome da Permissão</label>
                                <input
                                    id="permissionName"
                                    type="text"
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('permissionName') border-red-300 text-red-900 @enderror"
                                    wire:model="permissionName"
                                    placeholder="Digite o nome da permissão (ex: users.view)"
                                >
                                @error('permissionName')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-2 text-sm text-gray-500">
                                    Recomendado usar o formato: <span class="font-mono">módulo.ação</span> (ex: users.create, users.edit)
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-4 sm:px-6 py-3 bg-gray-50 flex justify-end space-x-3 border-t border-gray-200">
                    <button
                        type="button"
                        wire:click="$set('showPermissionModal', false)"
                        wire:loading.attr="disabled"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150 flex items-center"
                    >
                        <i class="fas fa-times mr-1"></i>
                        Cancelar
                    </button>

                    <button
                        type="button"
                        wire:click="savePermission"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-75 cursor-wait"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150 flex items-center"
                    >
                        <i class="fas fa-save mr-1" wire:loading.class="hidden" wire:target="savePermission"></i>
                        <i class="fas fa-spinner fa-spin mr-1 hidden" wire:loading.class.remove="hidden" wire:target="savePermission"></i>
                        <span wire:loading.remove wire:target="savePermission">Salvar</span>
                        <span wire:loading wire:target="savePermission">Salvando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de Confirmação de Exclusão -->
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center overflow-y-auto p-2 sm:p-4">
            <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all w-full max-w-md">
                <div class="bg-red-50 px-4 sm:px-6 py-3 sm:py-4 border-b border-red-200 flex justify-between items-center">
                    <h3 class="text-base sm:text-lg font-medium text-red-800 flex items-center">
                        <span class="bg-red-100 text-red-600 p-2 rounded-full mr-3">
                            <i class="fas fa-exclamation-triangle text-lg"></i>
                        </span>
                        Confirmação de Exclusão
                    </h3>
                    <button wire:click="$set('showDeleteModal', false)" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="px-4 sm:px-6 py-4">
                    <div class="p-4 bg-red-50 rounded-md border border-red-200 mb-4">
                        <p class="text-gray-700">
                            Tem certeza que deseja excluir
                            <span class="font-semibold">
                                {{ $deleteType === 'role' ? 'a função' : 'a permissão' }}
                            </span>?
                        </p>

                        <p class="mt-2 text-sm text-red-600">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            Esta ação não pode ser desfeita.
                        </p>
                    </div>
                </div>

                <div class="px-4 sm:px-6 py-3 bg-gray-50 flex justify-end space-x-3 border-t border-gray-200">
                    <button
                        type="button"
                        wire:click="$set('showDeleteModal', false)"
                        wire:loading.attr="disabled"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150 flex items-center"
                    >
                        <i class="fas fa-times mr-1"></i>
                        Cancelar
                    </button>

                    <button
                        type="button"
                        wire:click="delete"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-75 cursor-wait"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-150 flex items-center"
                    >
                        <i class="fas fa-trash mr-1" wire:loading.class="hidden" wire:target="delete"></i>
                        <i class="fas fa-spinner fa-spin mr-1 hidden" wire:loading.class.remove="hidden" wire:target="delete"></i>
                        <span wire:loading.remove wire:target="delete">Excluir</span>
                        <span wire:loading wire:target="delete">Excluindo...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
