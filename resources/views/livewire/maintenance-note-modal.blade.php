<div>
@if($showModal)
<div class="fixed inset-0 bg-gray-600 bg-opacity-75 transition-opacity z-[9999] flex items-center justify-center overflow-y-auto h-full w-full"
     x-data="{show: true}" 
     x-show="show"
     x-transition:enter="transition ease-out duration-300" 
     x-transition:enter-start="opacity-0" 
     x-transition:enter-end="opacity-100" 
     x-transition:leave="transition ease-in duration-200" 
     x-transition:leave-start="opacity-100" 
     x-transition:leave-end="opacity-0">
    
    <!-- Modal panel -->
    <div x-show="show"
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="transform opacity-0 scale-95" 
         x-transition:enter-end="transform opacity-100 scale-100" 
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="transform opacity-100 scale-100" 
         x-transition:leave-end="transform opacity-0 scale-95" 
         class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all w-full max-w-4xl mx-auto max-h-[90vh] flex flex-col">

            <!-- Modal header -->
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 flex-none">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                            <i class="fas fa-clipboard-list mr-2 animate-pulse"></i>
                            @if($viewOnly)
                                {{ __('messages.maintenance_notes_and_history') }}: {{ $task['title'] }} - {{ $task['equipment'] }}
                            @else
                                {{ __('messages.maintenance_notes') }}: {{ $task['title'] }} - {{ $task['equipment'] }}
                            @endif
                        </h3>
                    </div>
                    <button wire:click="closeModal" type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <!-- Main content area -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 overflow-y-auto p-2 flex-1" style="max-height: calc(90vh - 180px);">
                <!-- Column 1: Task Details -->
                <div class="col-span-1">
                    <!-- Cartão de Detalhes da Tarefa -->
                    <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                        <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                            <i class="fas fa-tasks text-blue-600 mr-2"></i>
                            <h3 class="text-base font-semibold flex items-center"><i class="fas fa-clipboard-list mr-2"></i> {{ __('messages.add_maintenance_note') }}</h3>
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
                                        <span class="px-2 py-0.5 text-xs rounded-full ml-1
                                            {{ $currentStatus === 'in-progress' || $currentStatus === 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $currentStatus === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $currentStatus === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $currentStatus === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $currentStatus === 'schedule' ? 'bg-purple-100 text-purple-800' : '' }}">
                                            {{ ucfirst(str_replace('_', '-', $currentStatus)) }}
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
                    
                    <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                        <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                            <i class="fas fa-history text-blue-600 mr-2"></i>
                            <h3 class="text-base font-medium text-gray-700">{{ $viewOnly ? __('messages.maintenance_history') : __('messages.maintenance_notes_and_history') }}</h3>
                        </div>
                        
                        <div class="p-4">
                            @if(!$viewOnly)
                            <form wire:submit.prevent="saveNote">
                                <div class="mb-5 p-4 bg-blue-50 rounded-lg border border-blue-100">
                                    <h4 class="text-sm font-medium text-blue-800 mb-2 flex items-center">
                                        <i class="fas fa-edit mr-2"></i> {{ __('messages.add_note') }}
                                    </h4>
                                    
                                    <div class="mb-4">
                                        <label for="notes" class="block text-xs font-medium text-gray-700 mb-1">
                                            {{ __('messages.notes') }} <span class="text-red-500">*</span>
                                        </label>
                                        <textarea
                                            id="notes"
                                            wire:model="notes"
                                            rows="3"
                                            class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            placeholder="{{ __('messages.enter_maintenance_notes') }}"
                                        ></textarea>
                                        @error('notes')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <label for="status" class="block text-xs font-medium text-gray-700 mb-1">
                                                {{ __('messages.status') }} <span class="text-red-500">*</span>
                                            </label>
                                            <select
                                                id="status"
                                                wire:model.defer="selectedStatus"
                                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            >
                                                <option value="">{{ __('messages.select_maintenance_status') }}</option>
                                                <option value="pending">{{ __('messages.pending') }}</option>
                                                <option value="in_progress">{{ __('messages.in_progress') }}</option>
                                                <option value="completed">{{ __('messages.completed') }}</option>
                                                <option value="cancelled">{{ __('messages.cancelled') }}</option>
                                                <option value="schedule">{{ __('messages.schedule') }}</option>
                                            </select>
                                            @error('selectedStatus')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        
                                        <div>
                                            <label for="workFile" class="block text-xs font-medium text-gray-700 mb-1">
                                                {{ __('messages.attachment') }}
                                            </label>
                                            <div class="relative border border-gray-300 rounded-md shadow-sm py-1.5 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white">
                                                <input
                                                    id="workFile"
                                                    type="file"
                                                    wire:model="workFile"
                                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                                />
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs text-gray-500 truncate">
                                                        {{ $workFile ? $workFile->getClientOriginalName() : __('messages.choose_file') }}
                                                    </span>
                                                    <i class="fas fa-paperclip text-gray-400"></i>
                                                </div>
                                            </div>
                                            @if($workFile)
                                            <p class="mt-1 text-xs text-green-600">
                                                <i class="fas fa-check-circle mr-1"></i> {{ __('messages.file_selected') }}
                                            </p>
                                            @endif
                                            @error('workFile')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                            @if($uploadError)
                                            <p class="mt-1 text-xs text-red-600">{{ $uploadError }}</p>
                                            @endif
                                            <p class="mt-1 text-xs text-gray-500">{{ __('messages.allowed_files') }}: PDF, DOC, JPG, PNG ({{ __('messages.max') }}: 10MB)</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Botão de Salvar -->
                                    <div class="mt-4 flex justify-end">
                                        <button
                                            type="submit"
                                            class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105"
                                            wire:loading.attr="disabled"
                                        >
                                            <i class="fas fa-save mr-2"></i>
                                            <span>{{ __('messages.save') }}</span>
                                        </button>
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
                    <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden flex flex-col">
                        <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200 flex-none">
                            <i class="fas fa-history text-blue-600 mr-2"></i>
                            <h3 class="text-base font-semibold"><i class="fas fa-history mr-2"></i> {{ __('messages.maintenance_activity_history') }}</h3>
                        </div>
                        <div class="p-4 overflow-y-auto" style="max-height: 500px;">
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
                                                    <i class="fas fa-user ml-2 mr-1"></i> {{ $note['user_name'] }}
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
            
            <!-- Modal footer -->
            <div class="bg-white px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse flex-none">
                <button wire:click="closeModal" type="button" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-50 active:text-gray-800 transition ease-in-out duration-150 ml-3 text-sm">
                    <i class="fas fa-times-circle mr-2"></i> {{ __('messages.close') }}
                </button>
            </div>
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
