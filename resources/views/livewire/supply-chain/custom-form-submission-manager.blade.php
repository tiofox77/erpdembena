<div>
    <!-- Modal para preenchimento de formulário -->
    @if($showFormModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full mx-auto">
            <div class="flex items-center justify-between bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 rounded-t-lg">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-clipboard-check mr-2"></i>
                    {{ isset($form) ? $form->name : 'Formulário de Status' }}
                </h3>
                <button wire:click="closeModal" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="submitForm">
                    @if(isset($fields) && $fields->count() > 0)
                        <div class="space-y-6">
                            @foreach($fields as $field)
                                <div class="@if($field->type === 'file') mb-8 @else mb-4 @endif">
                                    <label for="{{ $field->name }}" class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ $field->label }}
                                        @if($field->is_required) <span class="text-red-500">*</span> @endif
                                    </label>
                                    
                                    @if($field->description)
                                        <p class="text-xs text-gray-500 mb-1">{{ $field->description }}</p>
                                    @endif
                                    
                                    @switch($field->type)
                                        @case('text')
                                            <input 
                                                wire:model="formData.{{ $field->name }}" 
                                                type="text" 
                                                id="{{ $field->name }}"
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50
                                                      @error('formData.' . $field->name) border-red-300 @enderror">
                                            @break
                                        
                                        @case('textarea')
                                            <textarea 
                                                wire:model="formData.{{ $field->name }}" 
                                                id="{{ $field->name }}" 
                                                rows="3"
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50
                                                      @error('formData.' . $field->name) border-red-300 @enderror"></textarea>
                                            @break
                                        
                                        @case('number')
                                            <input 
                                                wire:model="formData.{{ $field->name }}" 
                                                type="number" 
                                                id="{{ $field->name }}"
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50
                                                      @error('formData.' . $field->name) border-red-300 @enderror">
                                            @break
                                        
                                        @case('email')
                                            <input 
                                                wire:model="formData.{{ $field->name }}" 
                                                type="email" 
                                                id="{{ $field->name }}"
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50
                                                      @error('formData.' . $field->name) border-red-300 @enderror">
                                            @break
                                        
                                        @case('date')
                                            <input 
                                                wire:model="formData.{{ $field->name }}" 
                                                type="date" 
                                                id="{{ $field->name }}"
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50
                                                      @error('formData.' . $field->name) border-red-300 @enderror">
                                            @break
                                        
                                        @case('select')
                                            <select 
                                                wire:model="formData.{{ $field->name }}" 
                                                id="{{ $field->name }}"
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50
                                                      @error('formData.' . $field->name) border-red-300 @enderror">
                                                <option value="">Selecione uma opção</option>
                                                @foreach($field->options as $option)
                                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                                @endforeach
                                            </select>
                                            @break
                                        
                                        @case('checkbox')
                                            <div class="mt-2">
                                                <input 
                                                    wire:model="formData.{{ $field->name }}" 
                                                    type="checkbox" 
                                                    id="{{ $field->name }}"
                                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50
                                                          @error('formData.' . $field->name) border-red-300 @enderror">
                                                <label for="{{ $field->name }}" class="ml-2 text-sm text-gray-700">Sim</label>
                                            </div>
                                            @break
                                        
                                        @case('radio')
                                            <div class="mt-2 space-y-2">
                                                @foreach($field->options as $option)
                                                    <div class="flex items-center">
                                                        <input 
                                                            wire:model="formData.{{ $field->name }}" 
                                                            type="radio" 
                                                            id="{{ $field->name }}_{{ $option['value'] }}"
                                                            value="{{ $option['value'] }}"
                                                            class="border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50
                                                                  @error('formData.' . $field->name) border-red-300 @enderror">
                                                        <label for="{{ $field->name }}_{{ $option['value'] }}" class="ml-2 text-sm text-gray-700">{{ $option['label'] }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                            @break
                                        
                                        @case('file')
                                            <div 
                                                x-data="{ uploading: false, progress: 0 }"
                                                x-on:livewire-upload-start="uploading = true"
                                                x-on:livewire-upload-finish="uploading = false"
                                                x-on:livewire-upload-error="uploading = false"
                                                x-on:livewire-upload-progress="progress = $event.detail.progress"
                                            >
                                                <input 
                                                    wire:model="fileUploads.{{ $field->name }}" 
                                                    type="file" 
                                                    id="{{ $field->name }}"
                                                    class="w-full border border-gray-300 px-3 py-2 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500
                                                          @error('fileUploads.' . $field->name) border-red-300 @enderror">
                                                
                                                <div x-show="uploading">
                                                    <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                                                        <div class="bg-blue-600 h-2.5 rounded-full" x-bind:style="'width: ' + progress + '%'"></div>
                                                    </div>
                                                </div>
                                                
                                                @if(isset($fileUploads[$field->name]))
                                                    <div class="mt-2 flex items-center space-x-2">
                                                        <i class="fas fa-check-circle text-green-500"></i>
                                                        <span class="text-sm text-gray-700">Arquivo selecionado: {{ $fileUploads[$field->name]->getClientOriginalName() }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            @break
                                        
                                        @default
                                            <input 
                                                wire:model="formData.{{ $field->name }}" 
                                                type="text" 
                                                id="{{ $field->name }}"
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50
                                                      @error('formData.' . $field->name) border-red-300 @enderror">
                                    @endswitch
                                    
                                    @error('formData.' . $field->name) 
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                    
                                    @error('fileUploads.' . $field->name) 
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="flex justify-end space-x-2 mt-6">
                            <button type="button" wire:click="closeModal" 
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Cancelar
                            </button>
                            <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Enviar Formulário
                            </button>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-6 space-y-2">
                            <div class="flex-shrink-0 bg-gray-100 p-3 rounded-full">
                                <i class="fas fa-exclamation-circle text-gray-400 text-2xl"></i>
                            </div>
                            <p class="text-gray-500 text-sm">Este formulário não possui campos definidos.</p>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Modal para visualização de submissão -->
    @if($showSubmissionDetailModal && $submission)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full mx-auto">
            <div class="flex items-center justify-between bg-gradient-to-r from-green-600 to-green-700 px-6 py-4 rounded-t-lg">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-clipboard-list mr-2"></i>
                    Detalhes da Submissão
                </h3>
                <button wire:click="closeModal" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6">
                <div class="mb-4 pb-4 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h4 class="text-lg font-medium text-gray-900">{{ $submission->form->name }}</h4>
                        <span class="text-sm text-gray-500">Enviado em: {{ $submission->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <p class="text-sm text-gray-500">
                        Por: {{ $submission->creator->name ?? 'Usuário não encontrado' }}
                    </p>
                </div>
                
                <div class="space-y-4">
                    @foreach($submission->fieldValues as $value)
                        <div class="bg-gray-50 p-3 rounded-md">
                            <h5 class="text-sm font-medium text-gray-700">{{ $value->field->label }}</h5>
                            
                            @if($value->field->type === 'file' && $value->hasAttachments())
                                <div class="mt-1">
                                    @foreach($value->attachments as $attachment)
                                        <div class="flex items-center text-sm text-gray-500 mt-1">
                                            <i class="fas {{ $attachment->icon }} text-blue-500 mr-2"></i>
                                            <span>{{ $attachment->original_filename }}</span>
                                            <span class="ml-2 text-xs text-gray-400">({{ $attachment->formatted_size }})</span>
                                            <button wire:click="downloadAttachment({{ $attachment->id }})" class="ml-2 text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-download"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-1 text-sm break-words">
                                    @if($value->field->type === 'checkbox')
                                        {{ $value->value ? 'Sim' : 'Não' }}
                                    @elseif($value->field->type === 'select' || $value->field->type === 'radio')
                                        {{ $value->formatted_value }}
                                    @elseif(empty($value->value))
                                        <span class="text-gray-400 italic">Não preenchido</span>
                                    @else
                                        {{ $value->value }}
                                    @endif
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
                
                <div class="flex justify-end mt-6">
                    <button type="button" wire:click="closeModal" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
