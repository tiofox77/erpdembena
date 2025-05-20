<!-- Modal para Visualizar Programação de Produção -->
@if($showViewModal)
<div class="fixed inset-0 z-30 flex items-center justify-center overflow-auto bg-gray-800 bg-opacity-75" id="viewModal">
    <div class="relative w-[90%] mx-auto my-8 bg-white rounded-lg shadow-xl overflow-y-auto" style="max-height: 90vh;">
        
        <!-- Cabeçalho do Modal -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3 flex justify-between items-center rounded-t-lg">
            <h3 class="text-lg font-medium text-white">
                <i class="fas fa-eye mr-2"></i>
                {{ __('messages.production_schedule_details') }}
            </h3>
            <button wire:click="closeViewModal" class="text-white hover:text-gray-200 focus:outline-none">
                <i class="fas fa-times-circle text-2xl"></i>
            </button>
        </div>

        <!-- Conteúdo do Modal -->
        @if($viewingSchedule)
        <div class="p-6">
            <div class="mb-4">
                <h4 class="text-lg font-bold text-gray-800">{{ $viewingSchedule->schedule_number }}</h4>
                <p class="text-sm text-gray-500">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $viewingSchedule->status === 'draft' ? 'bg-gray-100 text-gray-800' : 
                        ($viewingSchedule->status === 'confirmed' ? 'bg-blue-100 text-blue-800' : 
                        ($viewingSchedule->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 
                        ($viewingSchedule->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'))) }}">
                        <i class="fas {{ $viewingSchedule->status === 'draft' ? 'fa-pencil-alt' : 
                            ($viewingSchedule->status === 'confirmed' ? 'fa-check-circle' : 
                            ($viewingSchedule->status === 'in_progress' ? 'fa-hourglass-half' : 
                            ($viewingSchedule->status === 'completed' ? 'fa-flag-checkered' : 'fa-ban'))) }} mr-1"></i>
                        {{ __('messages.' . $viewingSchedule->status) }}
                    </span>
                    <span class="ml-2">
                        {{ $viewingSchedule->product->name ?? 'Produto não encontrado' }}
                    </span>
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h5 class="text-md font-semibold mb-2">Informações Gerais</h5>
                    <ul class="space-y-2">
                        <li><strong>Produto:</strong> {{ $viewingSchedule->product->name ?? 'N/A' }}</li>
                        <li><strong>Código:</strong> {{ $viewingSchedule->product->sku ?? 'N/A' }}</li>
                        <li><strong>Linha:</strong> {{ $viewingSchedule->line->name ?? 'N/A' }}</li>
                        <li><strong>Período:</strong> {{ $viewingSchedule->start_date->format('d/m/Y') }} a {{ $viewingSchedule->end_date->format('d/m/Y') }}</li>
                        <li><strong>Horário:</strong> {{ $viewingSchedule->start_time }} - {{ $viewingSchedule->end_time }}</li>
                    </ul>
                </div>
                
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h5 class="text-md font-semibold mb-2">Quantidades e Progresso</h5>
                    <ul class="space-y-2">
                        <li><strong>Qtd. Planejada:</strong> {{ number_format($viewingSchedule->planned_quantity, 2) }}</li>
                        <li><strong>Qtd. Produzida:</strong> {{ number_format($viewingSchedule->actual_quantity ?? 0, 2) }}</li>
                        <li><strong>Progresso:</strong> {{ $viewingSchedule->completionPercentage ?? 0 }}%</li>
                        <li><strong>Responsável:</strong> {{ $viewingSchedule->responsible ?? 'N/A' }}</li>
                        <li><strong>Prioridade:</strong> {{ __('messages.priority_' . $viewingSchedule->priority) }}</li>
                    </ul>
                </div>
            </div>

            @if($viewingSchedule->notes)
            <div class="bg-blue-50 p-4 rounded-lg mb-8">
                <h5 class="text-md font-semibold mb-2">Observações</h5>
                <p class="text-sm text-gray-700">{{ $viewingSchedule->notes }}</p>
            </div>
            @endif
        </div>
        @else
        <div class="p-6 text-center">
            <p class="text-gray-500">Dados não disponíveis para visualização.</p>
        </div>
        @endif

        <!-- Rodapé do Modal -->
        <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end border-t">
            <button wire:click="closeViewModal" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:w-auto">
                Fechar
            </button>
        </div>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Escutar por mensagens processadas do Livewire
    document.addEventListener('livewire:load', function() {
        window.Livewire.hook('message.processed', function() {
            console.log('Livewire message processed, checking if view modal is visible');
            var viewModal = document.getElementById('viewModal');
            if (viewModal && viewModal.style.display !== 'none') {
                console.log('View modal is visible');
            }
        });
    });
    
    // Adicionar manipulador de tecla Escape
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            console.log('Escape key pressed');
            if (window.Livewire) {
                window.Livewire.find('production-scheduling').closeViewModal();
            }
        }
    });
});
</script>
