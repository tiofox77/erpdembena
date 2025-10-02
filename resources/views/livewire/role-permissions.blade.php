<div>


    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Cabeçalho -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                        <div class="flex items-center mb-4 sm:mb-0">
                            <i class="fas fa-user-shield text-gray-600 text-xl mr-3"></i>
                            <div>
                                <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Gerenciamento de Funções e Permissões</h1>
                                <p class="text-sm text-gray-600 mt-1">Organizadas por módulos: Maintenance, MRP, Supply Chain e HR</p>
                            </div>
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
                                            @foreach($this->permissionGroups as $groupKey => $group)
                                                <option value="{{ $groupKey }}">
                                                    {{ $group['label'] }}
                                                </option>
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
                                                <button
                                                    wire:click="openDuplicateModal({{ $role->id }})"
                                                    class="text-green-600 hover:text-green-900 mr-3"
                                                    title="Duplicar"
                                                    wire:loading.attr="disabled"
                                                >
                                                    <i class="fas fa-copy w-4 h-4 sm:w-5 sm:h-5" wire:loading.class="hidden" wire:target="openDuplicateModal({{ $role->id }})"></i>
                                                    <i class="fas fa-spinner fa-spin w-4 h-4 sm:w-5 sm:h-5 hidden" wire:loading.class.remove="hidden" wire:target="openDuplicateModal({{ $role->id }})"></i>
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
                                                $module = $this->getPermissionModule($permission->name);
                                                $moduleLabel = $this->getModuleLabel($module);
                                                
                                                $moduleConfig = [
                                                    'maintenance' => ['icon' => 'fas fa-wrench', 'color' => 'bg-blue-100 text-blue-800'],
                                                    'mrp' => ['icon' => 'fas fa-industry', 'color' => 'bg-purple-100 text-purple-800'],
                                                    'supplychain' => ['icon' => 'fas fa-truck', 'color' => 'bg-green-100 text-green-800'],
                                                    'hr' => ['icon' => 'fas fa-users', 'color' => 'bg-yellow-100 text-yellow-800'],
                                                    'system' => ['icon' => 'fas fa-cogs', 'color' => 'bg-gray-100 text-gray-800'],
                                                    'reports' => ['icon' => 'fas fa-chart-bar', 'color' => 'bg-indigo-100 text-indigo-800'],
                                                    'other' => ['icon' => 'fas fa-question-circle', 'color' => 'bg-gray-100 text-gray-600']
                                                ];
                                                
                                                $config = $moduleConfig[$module] ?? $moduleConfig['other'];
                                            @endphp
                                            <span class="px-3 py-1 text-xs rounded-full {{ $config['color'] }} flex items-center inline-block font-medium">
                                                <i class="{{ $config['icon'] }} mr-2 text-sm"></i>
                                                {{ $moduleLabel }}
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
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center overflow-y-auto p-2 sm:p-4"
             x-data="{ showModal: true }"
             x-show="showModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all w-full max-w-5xl max-h-[90vh] overflow-y-auto"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                <!-- Cabeçalho do modal com gradiente melhorado -->
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-4 sm:px-6 py-4 sm:py-5 border-b border-gray-200 flex justify-between items-center sticky top-0 z-10">
                    <h3 class="text-lg sm:text-xl font-semibold text-white flex items-center">
                        <span class="bg-white bg-opacity-20 text-white p-2 rounded-full mr-3">
                            <i class="fas {{ $isEditing ? 'fa-edit' : 'fa-plus' }} text-lg"></i>
                        </span>
                        {{ $isEditing ? 'Editar Função' : 'Nova Função' }}
                    </h3>
                    <button wire:click="$set('showRoleModal', false)" 
                            class="text-white hover:text-gray-200 transition-colors duration-150 p-1 rounded-full hover:bg-white hover:bg-opacity-20">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                <!-- Corpo do modal -->
                <div class="px-4 sm:px-6 py-4">
                    @if($errors->any())
                        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-lg">
                            <p class="font-bold flex items-center">
                                <i class="fas fa-exclamation-circle mr-2 text-red-500"></i>
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
                            <h4 class="text-base font-semibold text-gray-700 mb-3 flex items-center">
                                <i class="fas fa-tag mr-2 text-blue-500"></i> Informações da Função
                            </h4>
                            <div class="rounded-lg border border-gray-300 p-4 bg-gradient-to-r from-blue-50 to-indigo-50">
                                <label for="roleName" class="block text-sm font-medium text-gray-700 mb-1">Nome da Função</label>
                                <input
                                    id="roleName"
                                    type="text"
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('roleData.name') border-red-300 text-red-900 @enderror"
                                    wire:model="roleData.name"
                                    placeholder="Digite o nome da função"
                                >
                                @error('roleData.name')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Permissões -->
                        <div>
                            <h4 class="text-base font-semibold text-gray-700 mb-3 flex items-center">
                                <i class="fas fa-key mr-2 text-blue-500"></i> Permissões Associadas
                            </h4>
                            <div class="rounded-lg border border-gray-300 p-4 bg-gray-50">
                                <!-- Controles de busca e filtro -->
                                <div class="mb-4 space-y-3">
                                    <div class="flex flex-col sm:flex-row gap-3">
                                        <div class="flex-1">
                                            <input
                                                type="text"
                                                wire:model.live.debounce.300ms="permissionSearch"
                                                placeholder="🔍 Buscar permissões..."
                                                class="block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            >
                                        </div>
                                        <div class="flex-1">
                                            <select
                                                wire:model.live="selectedModuleFilter"
                                                class="block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            >
                                                <option value="">🎯 Todos os módulos</option>
                                                @foreach($this->permissionGroups as $groupKey => $group)
                                                    <option value="{{ $groupKey }}">{{ $this->getModuleLabel($groupKey) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <!-- Botões de ação rápida -->
                                    <div class="flex flex-wrap gap-2">
                                        <button type="button" 
                                                wire:click="selectAllPermissions"
                                                class="inline-flex items-center px-3 py-1 border border-green-300 text-xs font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-150">
                                            <i class="fas fa-check-double mr-1"></i>
                                            Selecionar Todas
                                        </button>
                                        <button type="button" 
                                                wire:click="deselectAllPermissions"
                                                class="inline-flex items-center px-3 py-1 border border-red-300 text-xs font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-150">
                                            <i class="fas fa-times mr-1"></i>
                                            Desmarcar Todas
                                        </button>
                                    </div>
                                </div>

                                <!-- Grupos de permissões com scroll melhorado -->
                                <div class="max-h-96 overflow-y-auto pr-2" style="scrollbar-width: thin; scrollbar-color: #CBD5E0 #F7FAFC;">
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                        @foreach($this->permissionGroups as $groupKey => $group)
                                            @if(empty($selectedModuleFilter) || $selectedModuleFilter === $groupKey)
                                                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
                                                    <!-- Cabeçalho do grupo com botão de seleção -->
                                                    <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-200">
                                                        <h5 class="font-semibold text-sm text-gray-700 flex items-center">
                                                            @php
                                                                $moduleColors = [
                                                                    'maintenance' => '#3B82F6',
                                                                    'mrp' => '#8B5CF6', 
                                                                    'supplychain' => '#10B981',
                                                                    'hr' => '#F59E0B',
                                                                    'system' => '#6B7280',
                                                                    'reports' => '#6366F1',
                                                                    'other' => '#9CA3AF'
                                                                ];
                                                                $color = $moduleColors[$groupKey] ?? '#6B7280';
                                                            @endphp
                                                            <i class="fas fa-{{ $groupKey === 'maintenance' ? 'wrench' : ($groupKey === 'mrp' ? 'industry' : ($groupKey === 'supplychain' ? 'truck' : ($groupKey === 'hr' ? 'users' : ($groupKey === 'system' ? 'cogs' : ($groupKey === 'reports' ? 'chart-bar' : 'question-circle'))))) }} mr-2 text-lg" style="color: {{ $color }}"></i>
                                                            {{ $group['label'] }}
                                                        </h5>
                                                        <div class="flex items-center space-x-2">
                                                            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">{{ count($group['permissions']) }}</span>
                                                            <button type="button" 
                                                                    wire:click="toggleModulePermissions('{{ $groupKey }}')"
                                                                    class="text-xs px-2 py-1 rounded-md border transition-colors duration-150 hover:shadow-sm"
                                                                    style="color: {{ $color }}; border-color: {{ $color }}">
                                                                <i class="fas fa-toggle-on"></i>
                                                                Alternar
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <!-- Lista de permissões -->
                                                    <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                                                        @foreach($group['permissions'] as $permission)
                                                            @if(
                                                                empty($permissionSearch) || 
                                                                str_contains(strtolower($permission->name), strtolower($permissionSearch))
                                                            )
                                                                <label class="flex items-center space-x-3 py-2 px-2 hover:bg-gray-50 rounded-md cursor-pointer transition-colors duration-150 group">
                                                                    <input
                                                                        type="checkbox"
                                                                        value="{{ $permission->id }}"
                                                                        wire:model="selectedPermissions"
                                                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                                                    >
                                                                    <div class="flex-1 min-w-0">
                                                                        <span class="text-sm font-medium text-gray-800 group-hover:text-gray-900">{{ $permission->name }}</span>
                                                                    </div>
                                                                </label>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                                
                                @error('selectedPermissions')
                                    <p class="mt-2 text-xs text-red-600 flex items-center">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resumo das permissões selecionadas -->
                @if(count($selectedPermissions) > 0)
                    <div class="px-4 sm:px-6 py-3 bg-blue-50 border-t border-blue-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center text-sm text-blue-700">
                                <i class="fas fa-info-circle mr-2"></i>
                                <span class="font-medium">{{ count($selectedPermissions) }} permissões selecionadas</span>
                            </div>
                            <button type="button" 
                                    wire:click="showSelectedPermissionsList = !showSelectedPermissionsList"
                                    class="text-xs text-blue-600 hover:text-blue-800 underline">
                                {{ $showSelectedPermissionsList ?? false ? 'Ocultar' : 'Ver lista' }}
                            </button>
                        </div>
                        @if($showSelectedPermissionsList ?? false)
                            <div class="mt-2 text-xs text-blue-600 max-h-20 overflow-y-auto">
                                @foreach($selectedPermissions as $permId)
                                    @php
                                        $perm = App\Models\Permission::find($permId);
                                    @endphp
                                    @if($perm)
                                        <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded-full mr-1 mb-1">{{ $perm->name }}</span>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Rodapé melhorado -->
                <div class="px-4 sm:px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 flex justify-between items-center border-t border-gray-200">
                    <div class="text-xs text-gray-500">
                        <i class="fas fa-lightbulb mr-1"></i>
                        Dica: Use os filtros para encontrar permissões específicas
                    </div>
                    <div class="flex space-x-3">
                        <button
                            type="button"
                            wire:click="$set('showRoleModal', false)"
                            wire:loading.attr="disabled"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-150 flex items-center"
                        >
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </button>

                        <button
                            type="button"
                            wire:click="saveRole"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-75 cursor-wait"
                            class="px-6 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 border border-transparent rounded-lg shadow-sm hover:from-blue-700 hover:to-indigo-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-150 flex items-center transform hover:scale-105"
                        >
                            <i class="fas fa-save mr-2" wire:loading.class="hidden" wire:target="saveRole"></i>
                            <i class="fas fa-spinner fa-spin mr-2 hidden" wire:loading.class.remove="hidden" wire:target="saveRole"></i>
                            <span wire:loading.remove wire:target="saveRole">{{ $isEditing ? 'Atualizar' : 'Criar' }} Função</span>
                            <span wire:loading wire:target="saveRole">{{ $isEditing ? 'Atualizando' : 'Criando' }}...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de Criação/Edição de Permissão -->
    @if($showPermissionModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center overflow-y-auto p-2 sm:p-4"
             x-data="{ showModal: true }"
             x-show="showModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all w-full max-w-3xl max-h-[90vh] overflow-y-auto"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                <!-- Cabeçalho do modal com gradiente -->
                <div class="bg-gradient-to-r from-purple-500 to-indigo-600 px-4 sm:px-6 py-4 sm:py-5 border-b border-gray-200 flex justify-between items-center sticky top-0 z-10">
                    <h3 class="text-lg sm:text-xl font-semibold text-white flex items-center">
                        <span class="bg-white bg-opacity-20 text-white p-2 rounded-full mr-3">
                            <i class="fas {{ $isEditing ? 'fa-edit' : 'fa-plus' }} text-lg"></i>
                        </span>
                        {{ $isEditing ? 'Editar Permissão' : 'Nova Permissão' }}
                    </h3>
                    <button wire:click="$set('showPermissionModal', false)" 
                            class="text-white hover:text-gray-200 transition-colors duration-150 p-1 rounded-full hover:bg-white hover:bg-opacity-20">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                <!-- Corpo do modal -->
                <div class="px-4 sm:px-6 py-4">
                    @if($errors->any())
                        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-lg">
                            <p class="font-bold flex items-center">
                                <i class="fas fa-exclamation-circle mr-2 text-red-500"></i>
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
                        <!-- Informações da Permissão -->
                        <div>
                            <h4 class="text-base font-semibold text-gray-700 mb-3 flex items-center">
                                <i class="fas fa-key mr-2 text-purple-500"></i> Informações da Permissão
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Nome da Permissão -->
                                <div class="md:col-span-2">
                                    <div class="rounded-lg border border-gray-300 p-4 bg-gradient-to-r from-purple-50 to-indigo-50">
                                        <label for="permissionName" class="block text-sm font-medium text-gray-700 mb-1">Nome da Permissão</label>
                                        <input
                                            id="permissionName"
                                            type="text"
                                            class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm @error('permissionName') border-red-300 text-red-900 @enderror"
                                            wire:model.live="permissionName"
                                            placeholder="Digite o nome da permissão (ex: users.view)"
                                        >
                                        @error('permissionName')
                                            <p class="mt-1 text-xs text-red-600 flex items-center">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Descrição -->
                                <div class="md:col-span-2">
                                    <div class="rounded-lg border border-gray-300 p-4 bg-gray-50">
                                        <label for="permissionDescription" class="block text-sm font-medium text-gray-700 mb-1">Descrição (Opcional)</label>
                                        <textarea
                                            id="permissionDescription"
                                            rows="3"
                                            class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm"
                                            wire:model="permissionDescription"
                                            placeholder="Descreva o que esta permissão permite fazer..."
                                        ></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sugestões de Módulo -->
                        <div>
                            <h4 class="text-base font-semibold text-gray-700 mb-3 flex items-center">
                                <i class="fas fa-lightbulb mr-2 text-yellow-500"></i> Sugestões de Estrutura
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Seletor de Módulo -->
                                <div class="rounded-lg border border-gray-300 p-4 bg-yellow-50">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Módulo Sugerido</label>
                                    <select
                                        wire:model.live="selectedPermissionModule"
                                        class="block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm"
                                    >
                                        <option value="">Selecione um módulo...</option>
                                        <option value="maintenance">Maintenance (Manutenção)</option>
                                        <option value="mrp">MRP (Production Planning)</option>
                                        <option value="supplychain">Supply Chain (Cadeia de Suprimentos)</option>
                                        <option value="hr">HR (Recursos Humanos)</option>
                                        <option value="system">System (Sistema)</option>
                                        <option value="reports">Reports (Relatórios)</option>
                                    </select>
                                </div>

                                <!-- Seletor de Ação -->
                                <div class="rounded-lg border border-gray-300 p-4 bg-blue-50">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Ação Sugerida</label>
                                    <select
                                        wire:model.live="selectedPermissionAction"
                                        class="block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                        <option value="">Selecione uma ação...</option>
                                        <option value="view">View (Visualizar)</option>
                                        <option value="create">Create (Criar)</option>
                                        <option value="edit">Edit (Editar)</option>
                                        <option value="delete">Delete (Excluir)</option>
                                        <option value="manage">Manage (Gerenciar)</option>
                                        <option value="approve">Approve (Aprovar)</option>
                                        <option value="export">Export (Exportar)</option>
                                        <option value="dashboard">Dashboard (Painel)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Botão para aplicar sugestão -->
                            @if($selectedPermissionModule && $selectedPermissionAction)
                                <div class="mt-3 flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-lg">
                                    <div class="flex items-center text-sm text-green-700">
                                        <i class="fas fa-magic mr-2"></i>
                                        <span>Sugestão: <strong>{{ $selectedPermissionModule }}.{{ $selectedPermissionAction }}</strong></span>
                                    </div>
                                    <button type="button" 
                                            wire:click="applyPermissionSuggestion"
                                            class="inline-flex items-center px-3 py-1 border border-green-300 text-xs font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-150">
                                        <i class="fas fa-arrow-right mr-1"></i>
                                        Aplicar
                                    </button>
                                </div>
                            @endif
                        </div>

                        <!-- Exemplos de Uso -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h5 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                                <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                                Exemplos de Nomes de Permissões
                            </h5>
                            <div class="text-xs text-gray-600 space-y-1">
                                <div class="flex items-center">
                                    <span class="font-mono bg-blue-100 text-blue-800 px-2 py-1 rounded mr-2">users.manage</span>
                                    <span>Gerenciar usuários</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="font-mono bg-green-100 text-green-800 px-2 py-1 rounded mr-2">inventory.view</span>
                                    <span>Visualizar inventário</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="font-mono bg-purple-100 text-purple-800 px-2 py-1 rounded mr-2">reports.export</span>
                                    <span>Exportar relatórios</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rodapé melhorado -->
                <div class="px-4 sm:px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 flex justify-between items-center border-t border-gray-200">
                    <div class="text-xs text-gray-500">
                        <i class="fas fa-lightbulb mr-1"></i>
                        Dica: Use a estrutura módulo.ação para melhor organização
                    </div>
                    <div class="flex space-x-3">
                        <button
                            type="button"
                            wire:click="$set('showPermissionModal', false)"
                            wire:loading.attr="disabled"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-150 flex items-center"
                        >
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </button>

                        <button
                            type="button"
                            wire:click="savePermission"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-75 cursor-wait"
                            class="px-6 py-2 text-sm font-medium text-white bg-gradient-to-r from-purple-600 to-indigo-600 border border-transparent rounded-lg shadow-sm hover:from-purple-700 hover:to-indigo-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-150 flex items-center transform hover:scale-105"
                        >
                            <i class="fas fa-save mr-2" wire:loading.class="hidden" wire:target="savePermission"></i>
                            <i class="fas fa-spinner fa-spin mr-2 hidden" wire:loading.class.remove="hidden" wire:target="savePermission"></i>
                            <span wire:loading.remove wire:target="savePermission">{{ $isEditing ? 'Atualizar' : 'Criar' }} Permissão</span>
                            <span wire:loading wire:target="savePermission">{{ $isEditing ? 'Atualizando' : 'Criando' }}...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de Duplicação de Função -->
    @if($showDuplicateModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center overflow-y-auto p-2 sm:p-4"
             x-data="{ showModal: true }"
             x-show="showModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all w-full max-w-md max-h-[90vh] overflow-y-auto"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                <!-- Cabeçalho do modal -->
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-4 sm:px-6 py-4 sm:py-5 border-b border-gray-200 flex justify-between items-center sticky top-0 z-10">
                    <h3 class="text-lg sm:text-xl font-semibold text-white flex items-center">
                        <span class="bg-white bg-opacity-20 text-white p-2 rounded-full mr-3">
                            <i class="fas fa-copy text-lg"></i>
                        </span>
                        Duplicar Função
                    </h3>
                    <button wire:click="closeDuplicateModal" 
                            class="text-white hover:text-gray-200 transition-colors duration-150 p-1 rounded-full hover:bg-white hover:bg-opacity-20">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                <!-- Corpo do modal -->
                <div class="px-4 sm:px-6 py-4">
                    @if($errors->has('duplicateRoleName'))
                        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-lg">
                            <p class="font-bold flex items-center">
                                <i class="fas fa-exclamation-circle mr-2 text-red-500"></i>
                                Erro de validação:
                            </p>
                            <p class="mt-1 text-sm">{{ $errors->first('duplicateRoleName') }}</p>
                        </div>
                    @endif

                    <div class="space-y-4">
                        <!-- Informação sobre a função original -->
                        <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm text-blue-700 flex items-center">
                                <i class="fas fa-info-circle mr-2"></i>
                                Duplicando função: <strong class="ml-1">{{ $duplicateRoleId ? App\Models\Role::find($duplicateRoleId)?->name : '' }}</strong>
                            </p>
                            <p class="text-xs text-blue-600 mt-1">
                                A nova função terá todas as permissões da função original.
                            </p>
                        </div>

                        <!-- Nome da nova função -->
                        <div>
                            <label for="duplicateRoleName" class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                <i class="fas fa-tag mr-2 text-green-500"></i> Nome da Nova Função
                            </label>
                            <div class="rounded-lg border border-gray-300 p-4 bg-gradient-to-r from-green-50 to-emerald-50">
                                <input
                                    id="duplicateRoleName"
                                    type="text"
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm @error('duplicateRoleName') border-red-300 text-red-900 @enderror"
                                    wire:model.live="duplicateRoleName"
                                    placeholder="Digite o nome da nova função"
                                    maxlength="255"
                                >
                                <p class="text-xs text-gray-500 mt-1">
                                    Máximo 255 caracteres. O nome deve ser único.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rodapé -->
                <div class="px-4 sm:px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 flex justify-between items-center border-t border-gray-200">
                    <div class="text-xs text-gray-500">
                        <i class="fas fa-lightbulb mr-1"></i>
                        A duplicação copiará todas as permissões
                    </div>
                    <div class="flex space-x-3">
                        <button
                            type="button"
                            wire:click="closeDuplicateModal"
                            wire:loading.attr="disabled"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-150 flex items-center"
                        >
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </button>

                        <button
                            type="button"
                            wire:click="duplicateRole"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-75 cursor-wait"
                            class="px-6 py-2 text-sm font-medium text-white bg-gradient-to-r from-green-600 to-emerald-600 border border-transparent rounded-lg shadow-sm hover:from-green-700 hover:to-emerald-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-150 flex items-center transform hover:scale-105"
                        >
                            <i class="fas fa-copy mr-2" wire:loading.class="hidden" wire:target="duplicateRole"></i>
                            <i class="fas fa-spinner fa-spin mr-2 hidden" wire:loading.class.remove="hidden" wire:target="duplicateRole"></i>
                            <span wire:loading.remove wire:target="duplicateRole">Duplicar Função</span>
                            <span wire:loading wire:target="duplicateRole">Duplicando...</span>
                        </button>
                    </div>
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
                        wire:click="deleteConfirmed"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-75 cursor-wait"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-150 flex items-center"
                    >
                        <i class="fas fa-trash mr-1" wire:loading.class="hidden" wire:target="deleteConfirmed"></i>
                        <i class="fas fa-spinner fa-spin mr-1 hidden" wire:loading.class.remove="hidden" wire:target="deleteConfirmed"></i>
                        <span wire:loading.remove wire:target="deleteConfirmed">Excluir</span>
                        <span wire:loading wire:target="deleteConfirmed">Excluindo...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
