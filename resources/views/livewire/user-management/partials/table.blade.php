<!-- Tabela Modernizada -->
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
        <h2 class="text-lg font-semibold text-white flex items-center">
            <i class="fas fa-users mr-2"></i>
            Users List
        </h2>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-hashtag text-gray-600"></i>
                            <a href="#" wire:click.prevent="sortBy('id')" class="flex items-center hover:text-green-600 transition-colors">
                                ID
                                @if($sortField === 'id')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-green-600"></i>
                                @endif
                            </a>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-user text-green-600"></i>
                            <a href="#" wire:click.prevent="sortBy('first_name')" class="flex items-center hover:text-green-600 transition-colors">
                                Name
                                @if($sortField === 'first_name')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-green-600"></i>
                                @endif
                            </a>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-envelope text-blue-600"></i>
                            <a href="#" wire:click.prevent="sortBy('email')" class="flex items-center hover:text-blue-600 transition-colors">
                                Email
                                @if($sortField === 'email')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-blue-600"></i>
                                @endif
                            </a>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-user-shield text-purple-600"></i>
                            <span>Role</span>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-building text-yellow-600"></i>
                            <a href="#" wire:click.prevent="sortBy('department')" class="flex items-center hover:text-yellow-600 transition-colors">
                                Department
                                @if($sortField === 'department')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-yellow-600"></i>
                                @endif
                            </a>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-toggle-on text-indigo-600"></i>
                            <span>Status</span>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <div class="flex items-center justify-end space-x-2">
                            <i class="fas fa-cogs text-gray-600"></i>
                            <span>Actions</span>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($users as $userItem)
                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-900 font-medium">#{{ $userItem->id }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center">
                                        <span class="text-white font-bold text-sm">
                                            {{ strtoupper(substr($userItem->first_name, 0, 1) . substr($userItem->last_name, 0, 1)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $userItem->first_name }} {{ $userItem->last_name }}</div>
                                    @if($userItem->phone)
                                        <div class="text-sm text-gray-500 flex items-center">
                                            <i class="fas fa-phone text-gray-400 text-xs mr-1"></i>
                                            {{ $userItem->phone }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <i class="fas fa-envelope text-blue-400 mr-2"></i>
                                <span class="text-sm text-gray-900">{{ $userItem->email }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($userItem->roles->isNotEmpty())
                                @foreach($userItem->roles as $role)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                        <i class="fas fa-shield-alt mr-1"></i>
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    No role
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <i class="fas fa-building text-yellow-500 mr-2"></i>
                                <span class="text-sm text-gray-900">{{ $departments[$userItem->department] ?? $userItem->department }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button
                                wire:click="toggleUserStatus({{ $userItem->id }})"
                                class="inline-flex items-center px-2.5 py-1.5 border rounded-full text-xs font-medium transition-all duration-200 {{ $userItem->is_active ? 'border-green-200 bg-green-50 text-green-800 hover:bg-green-100' : 'border-red-200 bg-red-50 text-red-800 hover:bg-red-100' }}"
                            >
                                <span class="flex h-2 w-2 mr-1.5 relative">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $userItem->is_active ? 'bg-green-400' : 'bg-red-400' }} opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 {{ $userItem->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                </span>
                                {{ $userItem->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-1">
                                <!-- Editar -->
                                <button wire:click="editUser({{ $userItem->id }})" 
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-yellow-600 hover:text-yellow-900 hover:bg-yellow-50 rounded-md transition-all duration-200"
                                        title="Edit User">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <!-- Excluir -->
                                <button wire:click="confirmDelete({{ $userItem->id }})" 
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-600 hover:text-red-900 hover:bg-red-50 rounded-md transition-all duration-200"
                                        title="Delete User">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-user-slash text-gray-300 text-5xl mb-3"></i>
                                <p class="text-lg font-medium text-gray-900 mb-2">No users found</p>
                                @if($search || $filterRole || $filterDepartment || $filterStatus !== '')
                                    <p class="text-sm text-gray-600 mb-2">
                                        No users match your search criteria. Try adjusting your filters.
                                    </p>
                                    <button
                                        wire:click="$set('search', ''); $set('filterRole', ''); $set('filterDepartment', ''); $set('filterStatus', '')"
                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium underline"
                                    >
                                        Clear all filters
                                    </button>
                                @else
                                    <p class="text-sm text-gray-600">
                                        There are no users in the system yet. Click "New User" to add one.
                                    </p>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
