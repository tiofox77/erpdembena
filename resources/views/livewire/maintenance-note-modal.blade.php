<div>
@if($showModal)
<div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-[9999] flex items-center justify-center overflow-y-auto">
    <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all w-full max-w-4xl z-[10000]">
        <!-- Modal Header -->
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">
                @if($viewOnly)
                    Maintenance History: {{ $task['title'] }} - {{ $task['equipment'] }}
                @else
                    Maintenance Notes: {{ $task['title'] }} - {{ $task['equipment'] }}
                @endif
            </h3>
            <button wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="px-6 py-4 relative">
            <div wire:loading.delay.longer wire:target="workFile, saveNote, downloadFile"
                class="absolute inset-0 bg-white bg-opacity-90 flex items-center justify-center z-[10001]"
                style="backdrop-filter: blur(2px);">
                <div class="flex flex-col items-center space-y-2 bg-white p-4 rounded-lg shadow-lg">
                    <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-blue-600 font-medium">Processando, aguarde...</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Column 1: Task Details -->
                <div class="col-span-1">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-700 mb-3">Task Details</h4>

                        <div class="mb-2">
                            <span class="text-sm font-medium text-gray-500">ID:</span>
                            <span class="text-sm text-gray-900 ml-1">{{ $task['id'] }}</span>
                        </div>

                        <div class="mb-2">
                            <span class="text-sm font-medium text-gray-500">Task:</span>
                            <span class="text-sm text-gray-900 ml-1">{{ $task['title'] }}</span>
                        </div>

                        <div class="mb-2">
                            <span class="text-sm font-medium text-gray-500">Equipment:</span>
                            <span class="text-sm text-gray-900 ml-1">{{ $task['equipment'] }}</span>
                        </div>

                        <div class="mb-4">
                            <span class="text-sm font-medium text-gray-500">Status:</span>
                            <span class="px-2 py-0.5 text-xs rounded-full ml-1
                                {{ $task['status'] === 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $task['status'] === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $task['status'] === 'cancelled' ? 'bg-gray-100 text-gray-800' : '' }}">
                                {{ ucfirst($task['status']) }}
                            </span>
                        </div>

                        @if(!$viewOnly)
                        <h5 class="font-medium text-gray-700 text-sm mb-2">Update Status:</h5>
                        <div class="flex space-x-2">
                            <button wire:click="updateStatus('in_progress')" class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800 hover:bg-blue-200">
                                In Progress
                            </button>
                            <button wire:click="updateStatus('completed')" class="px-2 py-1 text-xs rounded bg-green-100 text-green-800 hover:bg-green-200">
                                Completed
                            </button>
                            <button wire:click="updateStatus('cancelled')" class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-800 hover:bg-gray-200">
                                Cancelled
                            </button>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Column 2: Note Form and History -->
                <div class="col-span-2">
                    @if(!$viewOnly)
                    <h4 class="font-medium text-gray-700 mb-3">Add New Note</h4>

                    <form wire:submit.prevent="saveNote" class="mb-4">
                        <div class="mb-3">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Activity Description</label>
                            <textarea
                                id="notes"
                                wire:model="notes"
                                rows="4"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Describe what was done during maintenance..."
                            ></textarea>
                            @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <!-- Novo campo para upload de arquivo -->
                        <div class="mb-3">
                            <label for="workFile" class="block text-sm font-medium text-gray-700 mb-1">Folha de Obra (Opcional)</label>
                            <div class="relative border border-gray-300 rounded-md p-2 bg-white">
                                <label for="workFile" class="inline-flex items-center justify-center w-full cursor-pointer">
                                    <span wire:loading.remove wire:target="workFile" class="flex items-center">
                                        <svg class="w-6 h-6 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                        </svg>
                                        <span class="text-sm text-gray-500">Clique para selecionar um arquivo ou arraste e solte aqui</span>
                                    </span>
                                    <span wire:loading wire:target="workFile" class="flex items-center text-blue-500">
                                        <svg class="animate-spin -ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>Carregando arquivo...</span>
                                    </span>
                                    <input
                                        type="file"
                                        id="workFile"
                                        wire:model="workFile"
                                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                        class="hidden"
                                    >
                                </label>

                                @if($workFile && !$uploadError)
                                <div class="mt-2 flex items-center p-2 bg-blue-50 rounded-md">
                                    <svg class="h-5 w-5 text-blue-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span class="text-sm text-blue-700">{{ $workFile->getClientOriginalName() }}</span>
                                </div>
                                @endif

                                @if($uploadError)
                                <div class="mt-2 flex items-center p-2 bg-red-50 rounded-md">
                                    <svg class="h-5 w-5 text-red-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span class="text-sm text-red-700">{{ $uploadError }}</span>
                                </div>
                                @endif
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Anexe a folha de obra ou documentação relevante (PDF, Word, imagens - max: 10MB)</p>
                            @error('workFile') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" wire:loading.attr="disabled" wire:loading.class="opacity-50" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <span wire:loading.remove wire:target="saveNote">Add Note</span>
                                <span wire:loading wire:target="saveNote">Salvando...</span>
                            </button>
                        </div>
                    </form>
                    @endif

                    <!-- Notes History -->
                    <div>
                        <h4 class="font-medium text-gray-700 mb-3 {{ !$viewOnly ? 'border-t pt-3' : '' }}">Activity History</h4>

                        @if(empty($history))
                            <p class="text-sm text-gray-500 italic">No activity records found</p>
                        @else
                            <div class="space-y-3">
                                @foreach($history as $note)
                                    <div class="p-3 border rounded-md bg-gray-50">
                                        <div class="flex justify-between mb-1">
                                            <span class="text-xs font-medium text-gray-500">
                                                {{ $note['created_at'] }} by {{ $note['user'] }}
                                            </span>
                                            <span class="px-2 py-0.5 text-xs rounded-full
                                                {{ $note['status'] === 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ $note['status'] === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $note['status'] === 'cancelled' ? 'bg-gray-100 text-gray-800' : '' }}">
                                                {{ ucfirst($note['status']) }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-900 whitespace-pre-line">{{ $note['notes'] }}</p>

                                        <!-- Exibir link para download se existir um arquivo -->
                                        @if(!empty($note['file_name']))
                                        <div class="mt-2 flex items-center">
                                            <svg class="h-4 w-4 text-gray-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                            </svg>
                                            <a
                                                href="#"
                                                wire:click.prevent="downloadFile({{ $note['id'] }})"
                                                class="text-xs text-blue-600 hover:text-blue-800 hover:underline"
                                            >
                                                {{ $note['file_name'] }}
                                            </a>
                                        </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="bg-gray-50 px-6 py-3 flex justify-end">
            <button
                wire:click="closeModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50"
            >
                Close
            </button>
        </div>
    </div>
</div>
@endif

<style>
/* Garantir que nenhum outro elemento tenha z-index maior que o modal */
body.modal-open [x-data],
body.modal-open .fixed,
body.modal-open .absolute {
    z-index: auto !important;
}

/* Ocultando qualquer outro overlay durante o upload */
body.modal-open::before {
    display: none !important;
}
</style>

<script>
// Adicionar a classe modal-open ao corpo quando o modal estiver aberto
document.addEventListener('livewire:initialized', () => {
    Livewire.hook('element.updated', (el, component) => {
        if (component.name === 'maintenance-note-modal') {
            const showModal = component.effects.dirty.includes('showModal');
            if (showModal) {
                if (component.data.showModal) {
                    document.body.classList.add('modal-open');
                } else {
                    document.body.classList.remove('modal-open');
                }
            }
        }
    });
});
</script>
</div>
