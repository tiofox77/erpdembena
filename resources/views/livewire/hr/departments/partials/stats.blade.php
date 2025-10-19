<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <!-- Total Departments -->
    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-200">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total de Departamentos</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $departments->total() }}</p>
                </div>
                <div class="p-4 bg-gradient-to-br from-purple-100 to-indigo-100 rounded-xl">
                    <i class="fas fa-building text-purple-600 text-2xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-r from-purple-50 to-indigo-50 px-6 py-2">
            <p class="text-xs text-purple-700 font-medium">
                <i class="fas fa-chart-line mr-1"></i>
                Todos os Departamentos
            </p>
        </div>
    </div>

    <!-- Active Departments -->
    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-200">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Ativos</p>
                    <p class="text-3xl font-bold text-green-600">
                        {{ $departments->where('is_active', true)->count() }}
                    </p>
                </div>
                <div class="p-4 bg-gradient-to-br from-green-100 to-emerald-100 rounded-xl">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-2">
            <p class="text-xs text-green-700 font-medium">
                <i class="fas fa-toggle-on mr-1"></i>
                Departamentos Ativos
            </p>
        </div>
    </div>

    <!-- Inactive Departments -->
    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-200">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Inativos</p>
                    <p class="text-3xl font-bold text-red-600">
                        {{ $departments->where('is_active', false)->count() }}
                    </p>
                </div>
                <div class="p-4 bg-gradient-to-br from-red-100 to-rose-100 rounded-xl">
                    <i class="fas fa-times-circle text-red-600 text-2xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-r from-red-50 to-rose-50 px-6 py-2">
            <p class="text-xs text-red-700 font-medium">
                <i class="fas fa-toggle-off mr-1"></i>
                Departamentos Inativos
            </p>
        </div>
    </div>

    <!-- With Manager -->
    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-200">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Com Responsável</p>
                    <p class="text-3xl font-bold text-blue-600">
                        {{ $departments->whereNotNull('manager_id')->count() }}
                    </p>
                </div>
                <div class="p-4 bg-gradient-to-br from-blue-100 to-cyan-100 rounded-xl">
                    <i class="fas fa-user-tie text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-r from-blue-50 to-cyan-50 px-6 py-2">
            <p class="text-xs text-blue-700 font-medium">
                <i class="fas fa-users mr-1"></i>
                Departamentos Geridos
            </p>
        </div>
    </div>
</div>
