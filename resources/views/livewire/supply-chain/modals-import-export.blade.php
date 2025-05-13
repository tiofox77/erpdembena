<!-- Modal de Importação -->
@if($showImportModal)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay de fundo -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <!-- Centralizador do modal -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Conteúdo do modal -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-gradient-to-r from-green-600 to-green-700 px-4 py-3">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-file-import mr-2"></i>
                    Importar Formulário
                </h3>
            </div>
            
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-4">Selecione um arquivo JSON de formulário previamente exportado para importar.</p>
                    
                    <div class="mt-3 relative border-2 border-dashed border-gray-300 rounded-md p-6"
                        x-data="{ isHovering: false }" 
                        x-on:dragover.prevent="isHovering = true" 
                        x-on:dragleave.prevent="isHovering = false" 
                        x-on:drop.prevent="isHovering = false"
                        x-bind:class="{ 'border-green-400 bg-green-50': isHovering }">
                        
                        <div class="space-y-2 text-center">
                            <div class="mx-auto h-14 w-14 text-gray-400 flex items-center justify-center">
                                <i class="fas fa-file-upload text-3xl"></i>
                            </div>
                            <div class="flex text-sm text-gray-600">
                                <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-green-600 hover:text-green-500 focus-within:outline-none">
                                    <span>Selecione um arquivo</span>
                                    <input wire:model="importFile" id="file-upload" type="file" class="sr-only" accept=".json">
                                </label>
                                <p class="pl-1">ou arraste e solte aqui</p>
                            </div>
                            <p class="text-xs text-gray-500">
                                JSON até 1MB
                            </p>
                        </div>
                        
                        @if($importFile)
                        <div class="mt-3 flex items-center justify-between bg-green-50 p-2 rounded-md">
                            <div class="flex items-center">
                                <i class="fas fa-file-code text-green-500 mr-2"></i>
                                <span class="text-sm font-medium text-gray-900">{{ $importFile->getClientOriginalName() }}</span>
                            </div>
                            <button type="button" wire:click="$set('importFile', null)" class="text-red-500 hover:text-red-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        @endif
                    </div>
                    @error('importFile') <span class="mt-1 text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
            
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" wire:click="importForm" wire:loading.attr="disabled"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    <span wire:loading.remove wire:target="importForm">Importar</span>
                    <span wire:loading wire:target="importForm" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Processando...
                    </span>
                </button>
                <button type="button" wire:click="closeModal"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Modal de Exportação -->
@if($showExportModal)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay de fundo -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <!-- Centralizador do modal -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Conteúdo do modal -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-4 py-3">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-file-export mr-2"></i>
                    Exportar Formulário
                </h3>
            </div>
            
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-4">Você está prestes a exportar um formulário personalizado. Isso criará um arquivo JSON que poderá ser importado em outro sistema ou usado como backup.</p>
                    
                    <div class="mt-3 bg-purple-50 p-4 rounded-md">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0 bg-purple-100 rounded-md p-2">
                                <i class="fas fa-file-export text-purple-600 text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">Exportação de Formulário</h4>
                                <p class="text-sm text-gray-500">O arquivo exportado incluirá o formulário e todos os seus campos configurados.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" wire:click="exportForm" wire:loading.attr="disabled"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    <span wire:loading.remove wire:target="exportForm">Exportar</span>
                    <span wire:loading wire:target="exportForm" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Processando...
                    </span>
                </button>
                <button type="button" wire:click="closeModal"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Script para download de arquivo -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        window.addEventListener('download-file', event => {
            const url = event.detail.url;
            const link = document.createElement('a');
            link.href = url;
            link.download = url.split('/').pop();
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    });
</script>
