<div>
@if($showModal)
<div class="fixed inset-0 bg-gray-600 bg-opacity-75 transition-opacity z-[9999] flex items-center justify-center overflow-y-auto h-full w-full" 
     x-data="{}" 
     x-transition:enter="transition ease-out duration-300" 
     x-transition:enter-start="opacity-0" 
     x-transition:enter-end="opacity-100" 
     x-transition:leave="transition ease-in duration-200" 
     x-transition:leave-start="opacity-100" 
     x-transition:leave-end="opacity-0">
    <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all w-full max-w-4xl z-[10000] relative top-20 mx-auto"
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="transform opacity-0 scale-95" 
         x-transition:enter-end="transform opacity-100 scale-100" 
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="transform opacity-100 scale-100" 
         x-transition:leave-end="transform opacity-0 scale-95">
        <!-- Modal Header com Gradiente -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 flex justify-between items-center rounded-t-lg">
            <h3 class="text-lg font-medium text-white flex items-center">
                <i class="fas fa-clipboard-list mr-2 animate-pulse"></i>
                @if($viewOnly)
                    {{ __('messages.maintenance_history') }}: {{ $task['title'] }} - {{ $task['equipment'] }}
                @else
                    {{ __('messages.maintenance_notes') }}: {{ $task['title'] }} - {{ $task['equipment'] }}
                @endif
            </h3>
            <button wire:click="closeModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="px-6 py-5 relative">
            <!-- Alerta para tarefas concluídas -->
            @if($task['status'] === 'completed')
            <div class="mb-4 rounded-md bg-blue-50 p-4 border border-blue-200">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-500"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">{{ __('messages.completed_task') }}</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>{{ __('messages.completed_task_readonly_notice') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            <div wire:loading.delay.longer wire:target="workFile, saveNote, downloadFile"
                class="absolute inset-0 bg-white bg-opacity-90 flex items-center justify-center z-[10001]"
                style="backdrop-filter: blur(2px);">
                <div class="flex flex-col items-center space-y-2 bg-white p-4 rounded-lg shadow-lg">
                    <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-blue-600 font-medium">{{ __('messages.processing_please_wait') }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Column 1: Task Details -->
                <div class="col-span-1">
                    <!-- Cartão de Detalhes da Tarefa -->
                    <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                        <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                            <i class="fas fa-tasks text-blue-600 mr-2"></i>
                            <h3 class="text-base font-medium text-gray-700">{{ __('messages.task_details') }}</h3>
                        </div>
                        <div class="p-4">
                            <div class="space-y-3">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-8 text-gray-500">
                                        <i class="fas fa-hashtag"></i>
                                    </div>
                                    <div class="flex-1">
                                        <span class="text-sm font-medium text-gray-500">{{ __('messages.id') }}:</span>
                                        <span class="text-sm text-gray-900 ml-1">{{ $task['id'] }}</span>
                                    </div>
                                </div>

                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-8 text-gray-500">
                                        <i class="fas fa-clipboard-check"></i>
                                    </div>
                                    <div class="flex-1">
                                        <span class="text-sm font-medium text-gray-500">{{ __('messages.task') }}:</span>
                                        <span class="text-sm text-gray-900 ml-1">{{ $task['title'] }}</span>
                                    </div>
                                </div>

                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-8 text-gray-500">
                                        <i class="fas fa-cogs"></i>
                                    </div>
                                    <div class="flex-1">
                                        <span class="text-sm font-medium text-gray-500">{{ __('messages.equipment') }}:</span>
                                        <span class="text-sm text-gray-900 ml-1">{{ $task['equipment'] }}</span>
                                    </div>
                                </div>

                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-8 text-gray-500">
                                        <i class="fas fa-info-circle"></i>
                                    </div>
                                    <div class="flex-1">
                                        <span class="text-sm font-medium text-gray-500">{{ __('messages.status') }}:</span>
                                        @php
                                            // Obter o status da última nota, se existir
                                            $latestNoteStatus = isset($notes[0]) ? $notes[0]['status'] : ($task['note_status'] ?? 'pending');
                                        @endphp
                                        <span class="px-2 py-0.5 text-xs rounded-full ml-1
                                            {{ $latestNoteStatus === 'in-progress' || $latestNoteStatus === 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $latestNoteStatus === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $latestNoteStatus === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $latestNoteStatus === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $latestNoteStatus === 'schedule' ? 'bg-purple-100 text-purple-800' : '' }}">
                                            {{ ucfirst(str_replace('_', '-', $latestNoteStatus)) }}
                                        </span>
                                        
                                        <!-- Status do plano (em cinza) -->
                                        <span class="px-2 py-0.5 text-xs rounded-full ml-1 bg-gray-100 text-gray-800" title="{{ __('messages.plan_status') }}">
                                            <i class="fas fa-tasks text-xs mr-1"></i>{{ ucfirst($task['status']) }}
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Legenda explicativa dos status -->
                                <div class="mt-3 p-2 border border-gray-200 rounded-md bg-gray-50 text-xs">
                                    <h6 class="font-medium text-gray-700 mb-1">{{ __('messages.status_explanation') }}:</h6>
                                    <div class="grid grid-cols-1 gap-1">
                                        <div class="flex items-center">
                                            <span class="inline-block w-2 h-2 rounded-full mr-1" style="background-color: #EFF6FF;"></span>
                                            <span><i class="fas fa-clipboard-check text-xs mr-1 text-blue-600"></i>{{ __('messages.note_status') }}</span>
                                            <span class="ml-1 text-gray-600">- {{ __('messages.tracks_execution_status') }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="inline-block w-2 h-2 rounded-full mr-1" style="background-color: #F3F4F6;"></span>
                                            <span><i class="fas fa-tasks text-xs mr-1 text-gray-600"></i>{{ __('messages.plan_status') }}</span>
                                            <span class="ml-1 text-gray-600">- {{ __('messages.tracks_overall_plan_status') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>

                <!-- Column 2: Note Form and History -->
                <div class="col-span-2">
                    <!-- Formulário de nova nota -->
                    @php
                        // Verificar se existe alguma nota com status completed no histórico
                        $hasCompletedNote = false;
                        foreach ($history as $note) {
                            if ($note['status'] === 'completed') {
                                $hasCompletedNote = true;
                                break;
                            }
                        }
                    @endphp
                    @if(!$viewOnly && !$hasCompletedNote)
                    <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden mb-5">
                        <div class="flex items-center bg-gradient-to-r from-green-50 to-green-100 px-4 py-3 border-b border-gray-200">
                            <i class="fas fa-plus-circle text-green-600 mr-2"></i>
                            <h3 class="text-base font-medium text-gray-700">{{ __('messages.add_new_note') }}</h3>
                        </div>
                        <div class="p-4">
                            <form wire:submit.prevent="saveNote" class="space-y-4">
                                <!-- Campo de descrição da atividade -->
                                <div>
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                                        <i class="fas fa-clipboard-list mr-1 text-gray-500"></i> {{ __('messages.activity_description') }} <span class="text-red-500 ml-1">*</span>
                                    </label>
                                    <div class="relative rounded-md shadow-sm">
                                        <textarea
                                            id="notes"
                                            wire:model.defer="notes"
                                            rows="4"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white"
                                            placeholder="{{ __('messages.describe_maintenance_activity') }}"
                                        ></textarea>
                                    </div>
                                    @error('notes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <!-- Campo para selecionar o status -->
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                                        <i class="fas fa-exchange-alt mr-1 text-gray-500"></i> {{ __('messages.status') }} <span class="text-red-500 ml-1">*</span>
                                    </label>
                                    <div class="relative rounded-md shadow-sm">
                                        <select
                                            id="status"
                                            wire:model.defer="selectedStatus"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white"
                                        >
                                            <option value="">{{ __('messages.select_status') }}</option>
                                            <option value="in_progress" class="text-blue-800">{{ __('messages.in_progress') }}</option>
                                            <option value="completed" class="text-green-800">{{ __('messages.completed') }}</option>
                                            <option value="cancelled" class="text-gray-800">{{ __('messages.cancelled') }}</option>
                                            <option value="pending" class="text-yellow-800">{{ __('messages.pending') }}</option>
                                            <option value="schedule" class="text-purple-800">{{ __('messages.schedule') }}</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                        </div>
                                    </div>
                                    @error('selectedStatus') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <!-- Campo para upload de arquivo -->
                                <div>
                                    <label for="workFile" class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                                        <i class="fas fa-file-upload mr-1 text-gray-500"></i> {{ __('messages.work_sheet') }} <span class="text-gray-400 ml-1">({{ __('messages.optional') }})</span>
                                    </label>
                                    <div class="relative rounded-md shadow-sm">
                                        <div class="relative border border-gray-300 rounded-md p-3 bg-white hover:bg-gray-50 transition-colors duration-200">
                                            <label for="workFile" class="inline-flex items-center justify-center w-full cursor-pointer">
                                                <span wire:loading.remove wire:target="workFile" class="flex items-center">
                                                    <i class="fas fa-paperclip text-gray-400 mr-2 text-lg"></i>
                                                    <span class="text-sm text-gray-600">{{ $workFile ? $workFile->getClientOriginalName() : __('messages.click_to_select_file') }}</span>
                                                </span>
                                                <span wire:loading wire:target="workFile" class="flex items-center">
                                                    <svg class="animate-spin h-5 w-5 text-blue-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    <span class="text-sm text-blue-600">{{ __('messages.loading_file') }}...</span>
                                                </span>
                                                <input
                                                    type="file"
                                                    id="workFile"
                                                    wire:model="workFile"
                                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                                    class="hidden"
                                                >
                                            </label>
                                        </div>
                                    </div>
                                    @error('workFile') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    <p class="mt-1 text-xs text-gray-500 flex items-center">
                                        <i class="fas fa-info-circle mr-1"></i> {{ __('messages.accepted_formats') }}: PDF, Word, JPG, PNG. {{ __('messages.max_size') }}: 10MB.
                                    </p>
                                </div>

                                <!-- Botão de salvar -->
                                <div class="flex justify-end pt-2">
                                    <button
                                        type="submit"
                                        wire:loading.attr="disabled"
                                        class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed"
                                    >
                                        <span wire:loading.remove wire:target="saveNote">
                                            <i class="fas fa-save mr-2"></i> {{ __('messages.save_note') }}
                                        </span>
                                        <span wire:loading wire:target="saveNote" class="inline-flex items-center">
                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            {{ __('messages.saving') }}...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Alerta para tarefas com nota completada no histórico -->
                    @if(!$viewOnly && $hasCompletedNote)
                    <div class="mb-5 rounded-md bg-blue-50 p-4 border border-blue-200">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-500"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">{{ __('messages.completed_task') }}</h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p>{{ __('messages.task_with_completed_note_cannot_be_modified') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Histórico de atividades -->
                    <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                        <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                            <i class="fas fa-history text-blue-600 mr-2"></i>
                            <h3 class="text-base font-medium text-gray-700">{{ __('messages.activity_history') }}</h3>
                        </div>
                        <div class="p-4">
                            <div class="space-y-3">
                                @if(count($history) === 0)
                                    <div class="flex justify-center items-center p-6 bg-gray-50 rounded-md border border-gray-200">
                                        <i class="fas fa-info-circle text-gray-400 mr-2 text-lg"></i>
                                        <p class="text-sm text-gray-500">{{ __('messages.no_activity_history_found') }}</p>
                                    </div>
                                @else
                                    @foreach($history as $note)
                                        <div class="p-4 border rounded-md bg-white shadow-sm hover:shadow-md transition-shadow duration-200">
                                            <div class="flex justify-between mb-2">
                                                <span class="text-xs font-medium text-gray-500 flex items-center">
                                                    <i class="fas fa-calendar-alt mr-1"></i> {{ $note['created_at'] }} 
                                                    <i class="fas fa-user ml-2 mr-1"></i> {{ $note['user'] }}
                                                </span>
                                                <span class="px-2 py-0.5 text-xs rounded-full flex items-center
                                                    {{ $note['status'] === 'in_progress' || $note['status'] === 'in-progress' ? 'bg-blue-100 text-blue-800' : '' }}
                                                    {{ $note['status'] === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $note['status'] === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                                                    {{ $note['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                    {{ $note['status'] === 'schedule' ? 'bg-purple-100 text-purple-800' : '' }}">
                                                    <i class="fas fa-circle text-xs mr-1"></i> {{ ucfirst(str_replace('_', '-', $note['status'])) }}
                                                </span>
                                            </div>
                                            <div class="p-3 bg-gray-50 rounded-md">
                                                <p class="text-sm text-gray-900 whitespace-pre-line">{{ $note['notes'] }}</p>
                                            </div>

                                            <!-- Exibir link para download se existir um arquivo -->
                                            @if(!empty($note['file_name']))
                                            <div class="mt-2 flex items-center">
                                                <i class="fas fa-paperclip text-gray-500 mr-1"></i>
                                                <a
                                                    href="#"
                                                    wire:click.prevent="downloadFile({{ $note['id'] }})"
                                                    class="text-xs text-blue-600 hover:text-blue-800 hover:underline flex items-center"
                                                >
                                                    <span>{{ $note['file_name'] }}</span>
                                                    <i class="fas fa-download ml-1 text-xs"></i>
                                                </a>
                                            </div>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>    
                </div>
            </div>
        </div>

        <!-- Modal Footer com Gradiente -->
        <div class="bg-gray-50 px-6 py-4 flex justify-end border-t border-gray-200">
            <button
                wire:click="closeModal"
                class="inline-flex justify-center items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200 ease-in-out transform hover:scale-105"
            >
                <i class="fas fa-times-circle mr-2"></i> {{ __('messages.close') }}
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
