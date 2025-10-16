<div>
    <div class="p-4 bg-white rounded-lg shadow-md">
        <!-- Cabeçalho Principal -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 mb-4">
            <div class="flex items-center justify-between bg-gradient-to-r from-green-600 to-green-700 px-4 py-3">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-users-cog mr-2 animate-pulse"></i>
                    User Management
                </h3>
                <div class="flex items-center space-x-2">
                    <button wire:click="openModal" class="inline-flex items-center px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-md transition-all duration-200 transform hover:scale-105">
                        <i class="fas fa-user-plus mr-2"></i>
                        New User
                    </button>
                </div>
            </div>
            
            @include('livewire.user-management.partials.filters')
        </div>

        @include('livewire.user-management.partials.table')

        <!-- Paginação -->
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Modais -->
    @include('livewire.user-management.partials.form-modal')
    @include('livewire.user-management.partials.delete-modal')
</div>
