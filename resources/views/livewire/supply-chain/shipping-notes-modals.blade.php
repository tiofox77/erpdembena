<!-- Modal para Adicionar/Editar Nota de Envio -->
<div x-data="{ open: @entangle('showAddModal').defer }" x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        
        <!-- Modal Panel -->
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block w-full max-w-lg mt-16 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-4 py-3 flex justify-between items-center">
                <h3 class="text-lg font-medium text-white">
                    {{ $editMode ? __('messages.edit_shipping_note') : __('messages.add_shipping_note') }}
                </h3>
                <button @click="open = false" class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Content -->
            <div class="p-6">
                <form wire:submit.prevent="save">
                    <!-- Status -->
                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('messages.status') }} <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="status" id="status" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">{{ __('messages.select_status') }}</option>
                            @foreach ($statusList as $statusKey => $statusValue)
                                <option value="{{ $statusKey }}">{{ __('messages.shipping_status_'.$statusKey) }}</option>
                            @endforeach
                        </select>
                        @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <!-- Nota -->
                    <div class="mb-4">
                        <label for="note" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('messages.note') }}
                        </label>
                        <textarea wire:model="note" id="note" rows="4" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                        @error('note') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <!-- Anexo -->
                    <div class="mb-6">
                        <label for="attachment" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('messages.attachment') }}
                        </label>
                        <div class="flex items-center space-x-2">
                            <input wire:model="attachment" id="attachment" type="file" class="hidden">
                            <label for="attachment" class="px-3 py-2 border border-gray-300 rounded-md text-sm leading-4 font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-50 active:text-gray-800 transition duration-150 ease-in-out cursor-pointer">
                                <i class="fas fa-paperclip mr-2"></i>
                                {{ __('messages.choose_file') }}
                            </label>
                            @if ($attachment)
                                <span class="text-sm text-gray-500">{{ $attachment->getClientOriginalName() }}</span>
                            @elseif ($existingAttachment)
                                <div class="flex items-center">
                                    <span class="text-sm text-gray-500">{{ basename($existingAttachment) }}</span>
                                    <button type="button" wire:click="downloadAttachment({{ $editId }})" class="ml-2 text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </div>
                            @else
                                <span class="text-sm text-gray-500">{{ __('messages.no_file_selected') }}</span>
                            @endif
                        </div>
                        @error('attachment') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-3 pt-3 border-t border-gray-200">
                        <button type="button" wire:click="closeModal" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-times mr-2"></i>
                            {{ __('messages.cancel') }}
                        </button>
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-save mr-2"></i>
                            {{ __('messages.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para seleção de formulário personalizado -->
<div x-data="{ open: @entangle('showCustomFormModal').defer }" x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        
        <!-- Modal Panel -->
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block w-full max-w-lg overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-green-600 to-green-800 px-4 py-3 flex justify-between items-center">
                <h3 class="text-lg font-medium text-white">
                    {{ __('messages.select_custom_form') }}
                </h3>
                <button @click="open = false" class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Content -->
            <div class="p-6">
                <p class="mb-4 text-gray-600 text-sm">{{ __('messages.select_form_to_fill') }}</p>
                
                <div class="mb-4">
                    <label for="formSelect" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('messages.available_forms') }} <span class="text-red-500">*</span>
                    </label>
                    <select wire:model="selectedFormId" id="formSelect" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                        <option value="">{{ __('messages.select_form') }}</option>
                        @foreach ($customForms as $form)
                            <option value="{{ $form->id }}">{{ $form->name }}</option>
                        @endforeach
                    </select>
                    @error('selectedFormId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <!-- Form Actions -->
                <div class="flex justify-end space-x-3 pt-3 border-t border-gray-200">
                    <button type="button" wire:click="closeModal" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <i class="fas fa-times mr-2"></i>
                        {{ __('messages.cancel') }}
                    </button>
                    <button type="button" wire:click="selectCustomForm" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <i class="fas fa-check mr-2"></i>
                        {{ __('messages.continue') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts para comunicação entre componentes Livewire -->
<script>
    document.addEventListener('livewire:load', function () {
        // Escutar o evento openFormSubmission para abrir o formulário personalizado
        Livewire.on('openFormSubmission', function (noteId, formId) {
            // A submissão do formulário será gerenciada pelo componente CustomFormSubmissionManager
            // Enviar para o componente principal (geralmente o layout) para carregar o componente correto
            window.dispatchEvent(new CustomEvent('open-form-submission', {
                detail: {
                    noteId: noteId,
                    formId: formId
                }
            }));
        });
        
        // Escutar o evento viewFormSubmission para visualizar uma submissão específica
        Livewire.on('viewFormSubmission', function (submissionId) {
            window.dispatchEvent(new CustomEvent('view-form-submission', {
                detail: {
                    submissionId: submissionId
                }
            }));
        });
    });
</script>
