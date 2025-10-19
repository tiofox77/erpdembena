<!-- Modern Filters Card -->
<div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden mb-6">
    <!-- Header -->
    <div class="flex items-center bg-gradient-to-r from-purple-50 to-indigo-50 px-5 py-4 border-b border-gray-200">
        <div class="p-2 bg-white rounded-lg shadow-sm mr-3">
            <i class="fas fa-filter text-purple-600"></i>
        </div>
        <h3 class="text-base font-semibold text-gray-800">Filtros</h3>
    </div>

    <!-- Filters Content -->
    <div class="p-5">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <!-- Search -->
            <div class="md:col-span-5">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-search text-gray-400 mr-1"></i>
                    Pesquisar
                </label>
                <div class="relative">
                    <input 
                        type="text" 
                        id="search" 
                        wire:model.live.debounce.300ms="search"
                        class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200"
                        placeholder="Pesquisar departamentos...">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                </div>
            </div>

            <!-- Status Filter -->
            <div class="md:col-span-3">
                <label for="status_filter" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-toggle-on text-gray-400 mr-1"></i>
                    Status
                </label>
                <select 
                    id="status_filter" 
                    wire:model.live="status_filter"
                    class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200">
                    <option value="all">Todos</option>
                    <option value="active">Ativos</option>
                    <option value="inactive">Inativos</option>
                </select>
            </div>

            <!-- Per Page -->
            <div class="md:col-span-2">
                <label for="perPage" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-list text-gray-400 mr-1"></i>
                    Por Página
                </label>
                <select 
                    id="perPage" 
                    wire:model.live="perPage"
                    class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>

            <!-- Reset Button -->
            <div class="md:col-span-2 flex items-end">
                <button 
                    wire:click="resetFilters"
                    class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-gray-100 to-gray-200 border border-gray-300 rounded-lg font-semibold text-sm text-gray-700 hover:from-gray-200 hover:to-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                    <i class="fas fa-redo mr-2"></i>
                    Limpar
                </button>
            </div>
        </div>
    </div>
</div>
