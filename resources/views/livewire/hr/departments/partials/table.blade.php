<!-- Modern Table Card -->
<div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
    <!-- Table Header -->
    <div class="flex items-center bg-gradient-to-r from-purple-50 to-indigo-50 px-5 py-4 border-b border-gray-200">
        <div class="p-2 bg-white rounded-lg shadow-sm mr-3">
            <i class="fas fa-table text-purple-600"></i>
        </div>
        <h3 class="text-base font-semibold text-gray-800">
            Lista de Departamentos
        </h3>
        <span class="ml-auto px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm font-semibold">
            {{ $departments->total() }} Total
        </span>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" 
                        wire:click="sortBy('name')"
                        class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors duration-200">
                        <div class="flex items-center space-x-1">
                            <i class="fas fa-building text-gray-400"></i>
                            <span>Nome do Departamento</span>
                            @if($sortField === 'name')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-purple-600"></i>
                            @else
                                <i class="fas fa-sort text-gray-300"></i>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                        <div class="flex items-center space-x-1">
                            <i class="fas fa-align-left text-gray-400"></i>
                            <span>Descrição</span>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                        <div class="flex items-center space-x-1">
                            <i class="fas fa-user-tie text-gray-400"></i>
                            <span>Responsável</span>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                        <div class="flex items-center space-x-1">
                            <i class="fas fa-toggle-on text-gray-400"></i>
                            <span>Status</span>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                        <div class="flex items-center justify-center space-x-1">
                            <i class="fas fa-cog text-gray-400"></i>
                            <span>Ações</span>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($departments as $department)
                <tr class="hover:bg-purple-50 transition-colors duration-150">
                    <!-- Name -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-purple-100 to-indigo-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-building text-purple-600"></i>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-bold text-gray-900">{{ $department->name }}</div>
                            </div>
                        </div>
                    </td>

                    <!-- Description -->
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-600 max-w-xs truncate">
                            {{ $department->description ?? 'N/A' }}
                        </div>
                    </td>

                    <!-- Manager -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($department->manager)
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8 bg-gradient-to-br from-blue-100 to-cyan-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-blue-600 text-xs"></i>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $department->manager->full_name }}</div>
                                </div>
                            </div>
                        @else
                            <span class="text-sm text-gray-400 italic">Não atribuído</span>
                        @endif
                    </td>

                    <!-- Status -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($department->is_active)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i>
                                Ativo
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                <i class="fas fa-times-circle mr-1"></i>
                                Inativo
                            </span>
                        @endif
                    </td>

                    <!-- Actions -->
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="flex items-center justify-center space-x-2">
                            <button 
                                wire:click="edit({{ $department->id }})"
                                class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-all duration-200 transform hover:scale-105"
                                title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button 
                                wire:click="confirmDelete({{ $department->id }})"
                                class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-all duration-200 transform hover:scale-105"
                                title="Eliminar">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-20 h-20 bg-gradient-to-br from-purple-100 to-indigo-100 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-building text-purple-400 text-3xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Nenhum departamento encontrado</h3>
                            <p class="text-gray-500 mb-4">Comece por adicionar um departamento</p>
                            <button 
                                wire:click="create"
                                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl font-semibold hover:from-purple-700 hover:to-indigo-700 transition-all duration-200 transform hover:scale-105 shadow-lg">
                                <i class="fas fa-plus-circle mr-2"></i>
                                Adicionar Departamento
                            </button>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($departments->hasPages())
    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
        {{ $departments->links() }}
    </div>
    @endif
</div>
