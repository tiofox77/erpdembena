<!-- Modern Header with Gradient -->
<div class="bg-gradient-to-r from-purple-600 via-indigo-600 to-blue-600 rounded-2xl shadow-xl mb-6 overflow-hidden">
    <div class="px-6 py-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <!-- Title Section -->
            <div class="flex items-center mb-4 md:mb-0">
                <div class="p-3 bg-white bg-opacity-20 backdrop-blur-sm rounded-xl mr-4 shadow-lg">
                    <i class="fas fa-building text-white text-3xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-white flex items-center">
                        Gestão de Departamentos
                    </h1>
                    <p class="text-purple-100 text-sm mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        Gerir departamentos e responsáveis
                    </p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center space-x-3">
                <button 
                    wire:click="create"
                    class="inline-flex items-center px-6 py-3 bg-white text-purple-600 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Adicionar Departamento
                </button>
            </div>
        </div>
    </div>
</div>
