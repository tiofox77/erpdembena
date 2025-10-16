<div>
    <div class="p-4 bg-white rounded-lg shadow-md">
        <!-- Cabeçalho Principal -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 mb-4">
            <div class="flex items-center justify-between bg-gradient-to-r from-green-600 to-green-700 px-4 py-3">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-money-bill-wave mr-2 animate-pulse"></i>
                    {{ __('messages.salary_advances') }}
                </h3>
                <div class="flex items-center space-x-2">
                    <button wire:click="exportPDF" class="inline-flex items-center px-3 py-2 bg-white bg-opacity-20 hover:bg-opacity-30 text-white text-sm font-medium rounded-md transition-all duration-200 transform hover:scale-105">
                        <i class="fas fa-file-pdf mr-2"></i>
                        {{ __('messages.export_pdf') }}
                    </button>
                    <button wire:click="create" class="inline-flex items-center px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-md transition-all duration-200 transform hover:scale-105">
                        <i class="fas fa-plus mr-2"></i>
                        {{ __('messages.add_advance') }}
                    </button>
                </div>
            </div>
            
            @include('livewire.hr.salary-advances.partials.filters')
        </div>
        
        @include('livewire.hr.salary-advances.partials.messages')
        @include('livewire.hr.salary-advances.partials.table')
        
        <!-- Paginação -->
        <div class="mt-4">
            {{ $advances->links() }}
        </div>
    </div>

    <!-- Incluir todas as modais -->
    @include('livewire.hr.salary-advances.partials.form-modal')
    @include('livewire.hr.salary-advances.partials.view-modal')
    @include('livewire.hr.salary-advances.partials.payment-modal')
    @include('livewire.hr.salary-advances.partials.delete-modal')
</div>
