<div>
    <!-- Form Submission Modal -->
    @if($showFormModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full mx-auto">
            <div class="flex items-center justify-between bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 rounded-t-lg">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-clipboard-check mr-2"></i>
                    {{ isset($form) ? $form->name : __('messages.status_form') }}
                </h3>
                <button wire:click="closeModal" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6">
                @if (session()->has('message'))
                    <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
                        {{ session('message') }}
                    </div>
                @endif
                
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
                                    
                                    {{-- Debug info para ajudar na identificação do problema --}}
                                    @if($field->type === 'relationship')
                                        <p class="text-xs text-gray-500 mb-1">{{ json_encode(['fieldType' => $field->type, 'fieldName' => $field->name, 'config' => $field->relationship_config]) }}</p>
                                    @endif
                                    
                                    @php
                                        // Verificação adicional para forçar o tipo correto
                                        $fieldType = $field->type;
                                        $hasRelationshipConfig = isset($field->relationship_config) && !empty($field->relationship_config['model']);
                                        
                                        // Se tem configuração de relacionamento, sempre tratamos como relationship
                                        if ($hasRelationshipConfig) {
                                            $fieldType = 'relationship';
                                            // Se o campo não foi declarado como relationship, mas tem configuração, forçamos o tipo
                                            if ($field->type !== 'relationship') {
                                                // Isto é apenas para visualização, não altera o banco de dados
                                                $field->type = 'relationship';
                                            }
                                        }
                                    @endphp
                                    
                                    {{-- Verificamos primeiro se o campo é do tipo relationship, independente do switch/case --}}
                                    @if($field->type === 'relationship' || (isset($field->relationship_config) && !empty($field->relationship_config['model'])))
                                        @php
                                            $relationshipConfig = $field->relationship_config ?? [];
                                            $modelClass = $relationshipConfig['model'] ?? null;
                                            $displayField = $relationshipConfig['display_field'] ?? 'name';
                                            $isMultiple = ($relationshipConfig['relationship_type'] ?? 'belongsTo') === 'hasMany';
                                            
                                            // Usar os dados relacionados carregados pelo componente
                                            $relatedItems = isset($relatedData[$field->name]) ? $relatedData[$field->name] : [];
                                            $selectedValue = $formData[$field->name] ?? null;
                                        @endphp
                                        
                                        <select 
                                            wire:model="formData.{{ $field->name }}" 
                                            id="{{ $field->name }}"
                                            @if($isMultiple) multiple @endif
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50
                                                  @error('formData.' . $field->name) border-red-300 @enderror">
                                            <option value="">Selecione {{ $field->label }}</option>
                                            @forelse($relatedItems as $value => $label)
                                                <option value="{{ $value }}" @if($selectedValue == $value) selected @endif>
                                                    {{ $label }}
                                                </option>
                                            @empty
                                                <option disabled>Nenhum registro encontrado</option>
                                            @endforelse
                                        </select>
                                        
                                        @if(empty($relatedItems))
                                            <p class="mt-1 text-xs text-red-500">
                                                <i class="fas fa-exclamation-circle mr-1"></i>
                                                {{ __('messages.could_not_load_related_data') }}
                                            </p>
                                        @endif
                                        
                                        @if($isMultiple)
                                            <p class="mt-1 text-xs text-gray-500">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                {{ __('messages.hold_ctrl_to_select_multiple') }}
                                            </p>
                                        @endif
                                    @else
                                        @switch($fieldType)
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
                                                <option value="">{{ __('messages.select_an_option') }}</option>
                                                @foreach($field->options as $option)
                                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                                @endforeach
                                            </select>
                                            @break
                                        
                                        @case('checkbox')
                                             @php
                                                 // Verificar se temos opções para o checkbox
                                                 $hasOptions = isset($field->options) && !empty($field->options);
                                                 $existingValue = $formData[$field->name] ?? null;
                                                 
                                                 // Processar valores existentes de forma mais robusta
                                                 $checkboxValues = [];
                                                 
                                                 if (!empty($existingValue)) {
                                                     // Se for uma string JSON válida, decodificar
                                                     if (is_string($existingValue)) {
                                                         if ($existingValue === '{}' || $existingValue === '[]') {
                                                             $checkboxValues = [];
                                                         } elseif (str_starts_with($existingValue, '{') && str_ends_with($existingValue, '}')) {
                                                             $decoded = json_decode($existingValue, true);
                                                             if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                                 // Filtrar apenas valores true
                                                                 $checkboxValues = array_filter($decoded, function($value) {
                                                                     return $value === true || $value === 'true' || $value === 1 || $value === '1';
                                                                 });
                                                             }
                                                         }
                                                     } 
                                                     // Se for array/objeto, processar diretamente
                                                     elseif (is_array($existingValue) || is_object($existingValue)) {
                                                         $array = is_object($existingValue) ? (array)$existingValue : $existingValue;
                                                         // Filtrar apenas valores true
                                                         $checkboxValues = array_filter($array, function($value) {
                                                             return $value === true || $value === 'true' || $value === 1 || $value === '1';
                                                         });
                                                     }
                                                     // Para checkbox simples
                                                     elseif (!$hasOptions) {
                                                         $checkboxValues = ['value' => $existingValue === true || $existingValue === 'true' || $existingValue === 1 || $existingValue === '1'];
                                                     }
                                                 }
                                                 
                                                 // Garantir que checkboxValues seja um array
                                                 if (!is_array($checkboxValues)) {
                                                     $checkboxValues = [];
                                                 }
                                             @endphp
                                             
                                             <div class="mt-2 space-y-2">
                                                 @if($hasOptions)
                                                     <!-- Checkbox com múltiplas opções usando wire:model diretamente -->
                                                     @foreach($field->options as $option)
                                                         @php
                                                             $optionKey = $option['value'];
                                                             $wireModelPath = "formData.{$field->name}.{$optionKey}";
                                                         @endphp
                                                         <div class="flex items-center">
                                                             <input type="checkbox" 
                                                                 id="{{ $field->id }}_{{ $optionKey }}" 
                                                                 wire:model.live="{{ $wireModelPath }}"
                                                                 class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                                                             <label for="{{ $field->id }}_{{ $optionKey }}" 
                                                                 class="ml-2 text-sm text-gray-700">{{ $option['label'] }}</label>
                                                         </div>
                                                     @endforeach
                                                     
                                                 @else
                                                     <!-- Checkbox simples -->
                                                     <div class="flex items-center">
                                                         <input type="checkbox" 
                                                             id="{{ $field->id }}" 
                                                             wire:model.live="formData.{{ $field->name }}" 
                                                             class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                                                         <label for="{{ $field->id }}" class="ml-2 text-sm text-gray-700">{{ $field->label }}</label>
                                                     </div>
                                                 @endif
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
                                                x-on:livewire-upload-progress="progress = $event.detail.progress">
                                                <div class="mt-1 flex items-center">
                                                    <input type="file" 
                                                        id="{{ $field->name }}" 
                                                        wire:model.live="formData.{{ $field->name }}"
                                                        class="block w-full text-sm text-gray-500
                                                               file:mr-4 file:py-2 file:px-4
                                                               file:rounded-md file:border-0
                                                               file:text-sm file:font-semibold
                                                               file:bg-blue-50 file:text-blue-700
                                                               hover:file:bg-blue-100">
                                                </div>
                                                
                                                <div x-show="uploading" class="mt-2 w-full bg-gray-200 rounded-full h-2.5">
                                                    <div class="bg-blue-600 h-2.5 rounded-full" 
                                                         x-bind:style="`width: ${progress}%`"></div>
                                                </div>
                                                
                                                <div x-show="uploading" class="mt-1 text-xs text-gray-500">
                                                    {{ __('messages.uploading') }}... <span x-text="progress"></span>%
                                                </div>
                                                
                                                <div x-show="uploading" class="mt-1 text-xs text-gray-500">
                                                    {{ __('messages.uploading') }}... <span x-text="progress"></span>%
                                                </div>
                                                
                                                @if(isset($formData[$field->name]) && is_string($formData[$field->name]) && str_starts_with($formData[$field->name], 'storage/'))
                                                    <div class="mt-2 flex items-center">
                                                        <i class="fas fa-file-alt text-gray-400 mr-2"></i>
                                                        <span class="text-sm text-gray-600">{{ basename($formData[$field->name]) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            @break
                                            
                                        @case('relationship')
                                            @php
                                                $relationshipConfig = $field->relationship_config ?? [];
                                                $modelClass = $relationshipConfig['model'] ?? null;
                                                $displayField = $relationshipConfig['display_field'] ?? 'name';
                                                $isMultiple = ($relationshipConfig['relationship_type'] ?? 'belongsTo') === 'hasMany';
                                                
                                                // Usar os dados relacionados carregados pelo componente
                                                $relatedItems = isset($relatedData[$field->name]) ? $relatedData[$field->name] : [];
                                                $selectedValue = $formData[$field->name] ?? null;
                                            @endphp
                                            
                                            <select 
                                                wire:model="formData.{{ $field->name }}" 
                                                id="{{ $field->name }}"
                                                @if($isMultiple) multiple @endif
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50
                                                      @error('formData.' . $field->name) border-red-300 @enderror">
                                                <option value="">{{ __('messages.select') }} {{ $field->label }}</option>
                                                @forelse($relatedItems as $value => $label)
                                                    <option value="{{ $value }}" @if($selectedValue == $value) selected @endif>
                                                        {{ $label }}
                                                    </option>
                                                @empty
                                                    <option disabled>{{ __('messages.no_records_found') }}</option>
                                                @endforelse
                                            </select>
                                            
                                            @if(empty($relatedItems))
                                                <p class="mt-1 text-xs text-red-500">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                                    {{ __('messages.could_not_load_related_data') }}
                                                </p>
                                            @endif
                                            
                                            @if($isMultiple)
                                                <p class="mt-1 text-xs text-gray-500">
                                                    <i class="fas fa-info-circle mr-1"></i>
                                                    {{ __('messages.hold_ctrl_to_select_multiple') }}
                                                </p>
                                            @endif
                                            @break
                                        
                                        @default
                                            <input 
                                                wire:model="formData.{{ $field->name }}" 
                                                type="text" 
                                                id="{{ $field->name }}"
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50
                                                      @error('formData.' . $field->name) border-red-300 @enderror">
                                        @endswitch
                                    @endif
                                    
                                    @error('formData.' . $field->name) 
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                    
                                    @error('fileUploads.' . $field->name) 
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Mark as Completed Checkbox -->
                        <div class="flex items-center justify-between mt-4 p-4 bg-blue-50 rounded-lg border border-blue-100">
                            <div class="flex items-center">
                                <div class="flex items-center h-5">
                                    <input id="is_completed" name="is_completed" type="checkbox" 
                                           wire:model.defer="formData.is_completed"
                                           class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                </div>
                                <label for="is_completed" class="ml-3 text-sm font-medium text-gray-700">
                                    <i class="fas fa-check-circle text-blue-500 mr-1"></i>
                                    {{ __('Mark as Completed') }}
                                </label>
                            </div>
                            <p class="text-xs text-gray-500">{{ __('Check this box to mark this form as completed') }}</p>
                        </div>
                        
                        <!-- Mark as Completed Checkbox -->
                        <div class="flex items-center justify-between mt-6 p-4 bg-blue-50 rounded-lg border border-blue-100">
                            <div class="flex items-center">
                                <div class="flex items-center h-5">
                                    <input id="is_completed" name="is_completed" type="checkbox" 
                                           wire:model.defer="formData.is_completed"
                                           class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                </div>
                                <label for="is_completed" class="ml-3 text-sm font-medium text-gray-700">
                                    <i class="fas fa-check-circle text-blue-500 mr-1"></i>
                                    {{ __('Mark as Completed') }}
                                </label>
                            </div>
                            <p class="text-xs text-gray-500">{{ __('Check this box to mark this form as completed') }}</p>
                        </div>
                        
                        <div class="flex justify-end space-x-2 mt-6">
                            <!-- Debug Button -->
                            <button type="button" wire:click="debugFormData" 
                                class="inline-flex items-center px-3 py-2 border border-yellow-300 rounded-md shadow-sm text-sm font-medium text-yellow-700 bg-yellow-50 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                <i class="fas fa-bug mr-1"></i>
                                {{ __('Debug') }}
                            </button>
                            
                            <button type="button" wire:click="closeModal" 
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                {{ __('messages.cancel') }}
                            </button>
                            
                            <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-save mr-1"></i>
                                {{ isset($submission) ? __('Update') : __('Save') }}
                            </button>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-6 space-y-2">
                            <div class="flex-shrink-0 bg-gray-100 p-3 rounded-full">
                                <i class="fas fa-exclamation-circle text-gray-400 text-2xl"></i>
                            </div>
                            <p class="text-gray-500 text-sm">{{ __('messages.no_fields_defined') }}</p>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
    @endif
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
                    {{ __('messages.submission_details') }}
                </h3>
                <button wire:click="closeModal" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6">
                <div class="mb-4 pb-4 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h4 class="text-lg font-medium text-gray-900">{{ $submission->form->name }}</h4>
                        <span class="text-sm text-gray-500">{{ __('messages.submitted_at') }}: {{ $submission->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <p class="text-sm text-gray-500">
                        {{ __('messages.by') }}: {{ $submission->creator->name ?? __('messages.user_not_found') }}
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
                            @elseif($value->field->type === 'relationship' && $value->related)
                                @php
                                    $relationshipConfig = $value->field->relationship_config ?? [];
                                    $displayField = $relationshipConfig['display_field'] ?? 'name';
                                    $isMultiple = ($relationshipConfig['relationship_type'] ?? 'belongsTo') === 'hasMany';
                                    $relatedValues = $isMultiple 
                                        ? $value->field->values->where('submission_id', $value->submission_id)
                                        : collect([$value]);
                                @endphp
                                
                                <div class="mt-1">
                                    @if($isMultiple)
                                        <ul class="list-disc pl-5 space-y-1">
                                            @foreach($relatedValues as $relatedValue)
                                                @if($relatedValue->related)
                                                    <li class="text-sm text-gray-700">
                                                        {{ $relatedValue->related->{$displayField} ?? 'Item #' . $relatedValue->related_id }}
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-sm text-gray-700">
                                            {{ $value->related->{$displayField} ?? 'Item #' . $value->related_id }}
                                        </p>
                                    @endif
                                </div>
                            @else
                                <p class="mt-1 text-sm break-words">
                                    @if($value->field->type === 'checkbox')
                                        @php
                                            // Detectar se o valor está em formato JSON
                                            $isJsonValue = \Illuminate\Support\Str::startsWith($value->value, '{') && \Illuminate\Support\Str::endsWith($value->value, '}');
                                            
                                            if ($isJsonValue) {
                                                // Decodificar o JSON
                                                $checkboxValues = json_decode($value->value, true);
                                                
                                                // Verificar se o decode funcionou
                                                if (json_last_error() === JSON_ERROR_NONE) {
                                                    // É um checkbox com múltiplas opções
                                                    $hasSelectedOptions = !empty(array_filter($checkboxValues));
                                                } else {
                                                    // Erro no decode, tratar como checkbox simples
                                                    $checkboxValues = null;
                                                    $hasSelectedOptions = false;
                                                    $isJsonValue = false;
                                                }
                                            }
                                            
                                            // Processar como checkbox simples se não for JSON
                                            if (!$isJsonValue) {
                                                $checkboxValue = false;
                                                if ($value->value === 'true' || $value->value === '1' || $value->value === true || $value->value === 1) {
                                                    $checkboxValue = true;
                                                }
                                            }
                                        @endphp
                                        
                                        @if($isJsonValue)
                                            <!-- Exibir múltiplas opções de checkbox -->
                                            <div class="space-y-1">
                                                @foreach($checkboxValues as $optionKey => $optionValue)
                                                    <div class="flex items-center">
                                                        <span class="inline-flex items-center {{ $optionValue ? 'text-green-600' : 'text-gray-500' }}">
                                                            <i class="fas {{ $optionValue ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                                                            {{ $optionKey }}: {{ $optionValue ? __('messages.yes') : __('messages.no') }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <!-- Exibir checkbox simples -->
                                            <span class="{{ $checkboxValue ? 'text-green-600' : 'text-gray-500' }}">
                                                @if($checkboxValue)
                                                    <i class="fas fa-check-circle mr-1"></i> {{ __('messages.yes') }}
                                                @else
                                                    <i class="fas fa-times-circle mr-1"></i> {{ __('messages.no') }}
                                                @endif
                                            </span>
                                        @endif
                                    @elseif($value->field->type === 'select' || $value->field->type === 'radio')
                                        {{ $value->formatted_value }}
                                    @elseif(empty($value->value))
                                        <span class="text-gray-400 italic">{{ __('messages.not_filled') }}</span>
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
                        {{ __('messages.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
