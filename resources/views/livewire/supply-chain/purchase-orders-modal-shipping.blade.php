<!-- Modal de Shipping Notes -->
<div x-data="{ show: @entangle('showShippingNotesModal').live }"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform scale-90"
    x-transition:enter-end="opacity-100 transform scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform scale-100"
    x-transition:leave-end="opacity-0 transform scale-90"
    class="fixed inset-0 z-50 overflow-y-auto" 
    style="display: none;">
    
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-900 opacity-75"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <!-- Cabeçalho do Modal -->
            <div class="bg-gradient-to-r from-amber-500 to-amber-700 px-4 py-3 sm:px-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg leading-6 font-medium text-white flex items-center">
                        <i class="fas fa-shipping-fast mr-2"></i>
                        {{ __('messages.shipping_notes') }}
                    </h3>
                    <button @click="show = false" class="text-white hover:text-gray-200 focus:outline-none">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <!-- Conteúdo do Modal -->
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 max-h-[70vh] overflow-y-auto">
                <div class="flex flex-col gap-6">
                    <!-- Ordem atual -->
                    @if($viewingOrderId && $viewingOrder = \App\Models\SupplyChain\PurchaseOrder::find($viewingOrderId))
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <div>
                                <h4 class="text-lg font-medium text-gray-800">{{ $viewingOrder->order_number }}</h4>
                                <p class="text-sm text-gray-600">
                                    {{ __('messages.supplier') }}: {{ $viewingOrder->supplier->name ?? '-' }}
                                </p>
                            </div>
                            <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                @if($viewingOrder->status == 'draft') bg-gray-100 text-gray-800
                                @elseif($viewingOrder->status == 'pending_approval') bg-yellow-100 text-yellow-800
                                @elseif($viewingOrder->status == 'approved') bg-blue-100 text-blue-800
                                @elseif($viewingOrder->status == 'ordered') bg-indigo-100 text-indigo-800
                                @elseif($viewingOrder->status == 'partially_received') bg-purple-100 text-purple-800
                                @elseif($viewingOrder->status == 'completed') bg-green-100 text-green-800
                                @elseif($viewingOrder->status == 'cancelled') bg-red-100 text-red-800
                                @endif">
                                @if($viewingOrder->status == 'draft')
                                    <i class="fas fa-pencil-alt mr-1"></i>
                                @elseif($viewingOrder->status == 'pending_approval')
                                    <i class="fas fa-clock mr-1"></i>
                                @elseif($viewingOrder->status == 'approved')
                                    <i class="fas fa-check mr-1"></i>
                                @elseif($viewingOrder->status == 'ordered')
                                    <i class="fas fa-shopping-cart mr-1"></i>
                                @elseif($viewingOrder->status == 'partially_received')
                                    <i class="fas fa-truck mr-1"></i>
                                @elseif($viewingOrder->status == 'completed')
                                    <i class="fas fa-check-circle mr-1"></i>
                                @elseif($viewingOrder->status == 'cancelled')
                                    <i class="fas fa-times-circle mr-1"></i>
                                @endif
                                {{ __('messages.status_'.$viewingOrder->status) }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Formulário para adicionar nota -->
                    <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                        <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                            <i class="fas fa-plus-circle text-blue-600 mr-2"></i>
                            <h3 class="text-base font-medium text-gray-700">{{ __('messages.add_shipping_note') }}</h3>
                        </div>
                        <div class="p-4">
                            <form wire:submit.prevent="addShippingNote" class="grid grid-cols-1 gap-4" enctype="multipart/form-data">
                                <div>
                                    <label for="shipping_status" class="block text-sm font-medium text-gray-700">{{ __('messages.status') }} <span class="text-red-500">*</span></label>
                                    <select wire:model="shippingNote.status" id="shipping_status" wire:change="loadCustomFormFields"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white">
                                        <option value="">{{ __('messages.select_status') }}</option>
                                        
                                        <!-- Status padrão -->
                                        <optgroup label="{{ __('messages.standard_status') }}">
                                            <option value="order_placed">{{ __('messages.shipping_status_order_placed') }}</option>
                                            <option value="delivered">{{ __('messages.shipping_status_delivered') }}</option>
                                        </optgroup>
                                        
                                        <!-- Formulários personalizados -->
                                        @if(isset($customForms) && $customForms->count() > 0)
                                            <optgroup label="{{ __('messages.custom_forms') }}">
                                                @foreach($customForms as $form)
                                                    <option value="custom_form_{{ $form->id }}">{{ $form->name }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    </select>
                                    @error('shippingNote.status')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <!-- Renderizar campos do formulário personalizado quando selecionado -->
                                @if($renderCustomForm && $selectedCustomForm && !empty($customFormFields))
                                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200 my-2">
                                        <h4 class="text-blue-700 font-semibold mb-3">{{ $selectedCustomForm->name }}</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            @foreach($customFormFields as $field)
                                                <div class="@if($field['type'] == 'textarea') col-span-2 @endif">
                                                    <label for="custom_form_field_{{ $field['id'] }}" 
                                                           class="block text-sm font-medium text-gray-700">
                                                        {{ $field['label'] }}
                                                        @if($field['is_required'])<span class="text-red-500">*</span>@endif
                                                    </label>
                                                    
                                                    @switch($field['type'])
                                                        @case('text')
                                                            <input type="text" 
                                                                  id="custom_form_field_{{ $field['id'] }}" 
                                                                  wire:model="formData.{{ $field['name'] }}" 
                                                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm">
                                                            @break
                                                        
                                                        @case('textarea')
                                                            <textarea id="custom_form_field_{{ $field['id'] }}" 
                                                                     wire:model="formData.{{ $field['name'] }}" 
                                                                     rows="3"
                                                                     class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm"></textarea>
                                                            @break
                                                            
                                                        @case('number')
                                                            <input type="number" 
                                                                  id="custom_form_field_{{ $field['id'] }}" 
                                                                  wire:model="formData.{{ $field['name'] }}" 
                                                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm">
                                                            @break
                                                            
                                                        @case('select')
                                                            <select id="custom_form_field_{{ $field['id'] }}" 
                                                                   wire:model="formData.{{ $field['name'] }}" 
                                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm">
                                                                <option value="">{{ __('messages.select_option') }}</option>
                                                                @foreach($field['options'] as $option)
                                                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                                                @endforeach
                                                            </select>
                                                            @break
                                                            
                                                        @case('checkbox')
                                                            <div class="mt-2 space-y-2">
                                                                @foreach($field['options'] as $option)
                                                                    <div class="flex items-center">
                                                                        <input type="checkbox" 
                                                                              id="custom_form_field_{{ $field['id'] }}_{{ $option['value'] }}" 
                                                                              wire:model="formData.{{ $field['name'] }}.{{ $option['value'] }}" 
                                                                              class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                                                                        <label for="custom_form_field_{{ $field['id'] }}_{{ $option['value'] }}" class="ml-2 text-sm text-gray-700">{{ $option['label'] }}</label>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                            @break
                                                            
                                                        @case('date')
                                                            <input type="date" 
                                                                  id="custom_form_field_{{ $field['id'] }}" 
                                                                  wire:model="formData.{{ $field['name'] }}" 
                                                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm">
                                                            @break
                                                            
                                                        @case('file')
                                                            <div class="mt-1 flex flex-col">
                                                                <div class="flex items-center">
                                                                    <input type="file" 
                                                                        id="custom_form_field_{{ $field['id'] }}" 
                                                                        wire:model="formData.{{ $field['name'] }}" 
                                                                        class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 focus:outline-none">
                                                                    
                                                                    <div wire:loading wire:target="formData.{{ $field['name'] }}" class="ml-3">
                                                                        <i class="fas fa-spinner fa-spin text-blue-500"></i>
                                                                    </div>
                                                                </div>
                                                                
                                                                @if(!empty($formData[$field['name']]) && !is_object($formData[$field['name']]))
                                                                    <div class="mt-2 border border-blue-100 rounded-md p-2 bg-blue-50 flex items-center justify-between">
                                                                        <div class="flex items-center">
                                                                            <i class="fas fa-file-alt text-blue-500 mr-2"></i>
                                                                            <span class="text-sm text-gray-700 truncate max-w-xs">{{ basename($formData[$field['name']]) }}</span>
                                                                        </div>
                                                                        <a href="{{ asset('storage/' . $formData[$field['name']]) }}" target="_blank" class="text-blue-600 hover:text-blue-800 ml-2 px-2 py-1 bg-white rounded shadow-sm text-xs flex items-center">
                                                                            <i class="fas fa-download mr-1"></i>
                                                                            {{ __('messages.download') }}
                                                                        </a>
                                                                    </div>
                                                                @elseif(is_object($formData[$field['name']]) && method_exists($formData[$field['name']], 'getClientOriginalName'))
                                                                    <div class="mt-2 border border-blue-100 rounded-md p-2 bg-blue-50 flex items-center justify-between">
                                                                        <div class="flex items-center">
                                                                            <i class="fas fa-file-upload text-green-500 mr-2"></i>
                                                                            <span class="text-sm text-gray-700 truncate max-w-xs">{{ $formData[$field['name']]->getClientOriginalName() }}</span>
                                                                        </div>
                                                                        <span class="text-xs text-gray-500 italic px-2 py-1 bg-white rounded shadow-sm">
                                                                            <i class="fas fa-check-circle text-green-500 mr-1"></i>
                                                                            {{ __('messages.selected_file') }}
                                                                        </span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            @error('formData.' . $field['name'])
                                                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                                            @enderror
                                                            @break
                                                            
                                                        @default
                                                            <input type="text" 
                                                                  id="custom_form_field_{{ $field['id'] }}" 
                                                                  wire:model="formData.{{ $field['name'] }}" 
                                                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm">
                                                    @endswitch
                                                    
                                                    @if($field['description'])
                                                        <p class="mt-1 text-xs text-gray-500">{{ $field['description'] }}</p>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Campo de anexo removido conforme solicitado -->
                                
                                <div class="flex justify-end">
                                    <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <i class="fas fa-save mr-2"></i>
                                        {{ __('messages.add_note') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Histórico de notas -->
                    <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                        <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                            <i class="fas fa-history text-blue-600 mr-2"></i>
                            <h3 class="text-base font-medium text-gray-700">{{ __('messages.shipping_history') }}</h3>
                        </div>
                        
                        @php
                            $shippingNotes = \App\Models\SupplyChain\ShippingNote::where('purchase_order_id', $viewingOrderId)
                                ->orderBy('created_at', 'desc')
                                ->get();
                        @endphp
                        
                        <div class="divide-y divide-gray-200">
                            @forelse($shippingNotes as $note)
                                <div class="p-4 hover:bg-gray-50 transition-all duration-200 transform hover:scale-[1.005]">
                                    <div class="flex justify-between items-start">
                                        <div class="flex items-center space-x-3">
                                            <div class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                                @if($note->status == 'order_placed') bg-gray-100 text-gray-800
                                                @elseif($note->status == 'proforma_invoice_received') bg-purple-100 text-purple-800
                                                @elseif($note->status == 'payment_completed') bg-blue-100 text-blue-800
                                                @elseif($note->status == 'du_in_process') bg-indigo-100 text-indigo-800
                                                @elseif($note->status == 'goods_acquired') bg-teal-100 text-teal-800
                                                @elseif($note->status == 'shipped_to_port') bg-amber-100 text-amber-800
                                                @elseif($note->status == 'shipping_line_booking_confirmed') bg-yellow-100 text-yellow-800
                                                @elseif($note->status == 'container_loaded') bg-orange-100 text-orange-800
                                                @elseif($note->status == 'on_board') bg-red-100 text-red-800
                                                @elseif($note->status == 'arrived_at_port') bg-rose-100 text-rose-800
                                                @elseif($note->status == 'customs_clearance') bg-pink-100 text-pink-800
                                                @elseif($note->status == 'delivered') bg-green-100 text-green-800
                                                @endif border border-opacity-50
                                                @if($note->status == 'order_placed') border-gray-300
                                                @elseif($note->status == 'proforma_invoice_received') border-purple-300
                                                @elseif($note->status == 'payment_completed') border-blue-300
                                                @elseif($note->status == 'du_in_process') border-indigo-300
                                                @elseif($note->status == 'goods_acquired') border-teal-300
                                                @elseif($note->status == 'shipped_to_port') border-amber-300
                                                @elseif($note->status == 'shipping_line_booking_confirmed') border-yellow-300
                                                @elseif($note->status == 'container_loaded') border-orange-300
                                                @elseif($note->status == 'on_board') border-red-300
                                                @elseif($note->status == 'arrived_at_port') border-rose-300
                                                @elseif($note->status == 'customs_clearance') border-pink-300
                                                @elseif($note->status == 'delivered') border-green-300
                                                @endif">
                                                <i class="
                                                    @if($note->status == 'order_placed') fas fa-shopping-cart
                                                    @elseif($note->status == 'proforma_invoice_received') fas fa-file-invoice
                                                    @elseif($note->status == 'payment_completed') fas fa-money-check-alt
                                                    @elseif($note->status == 'du_in_process') fas fa-file-contract
                                                    @elseif($note->status == 'goods_acquired') fas fa-box-open
                                                    @elseif($note->status == 'shipped_to_port') fas fa-truck-loading
                                                    @elseif($note->status == 'shipping_line_booking_confirmed') fas fa-calendar-check
                                                    @elseif($note->status == 'container_loaded') fas fa-truck-moving
                                                    @elseif($note->status == 'on_board') fas fa-ship
                                                    @elseif($note->status == 'arrived_at_port') fas fa-anchor
                                                    @elseif($note->status == 'customs_clearance') fas fa-clipboard-check
                                                    @elseif($note->status == 'delivered') fas fa-check-circle
                                                    @endif mr-1 text-xs"></i>
                                                {{ __('messages.shipping_status_'.$note->status) }}
                                            </div>
                                            <span class="text-sm text-gray-500 whitespace-nowrap">
                                                {{ $note->created_at->format('d/m/Y H:i') }}
                                            </span>
                                        </div>
                                        
                                        <div class="flex space-x-2">
                                            @if($note->attachment_url)
                                                <a href="{{ asset('storage/'.$note->attachment_url) }}" target="_blank"
                                                    class="text-blue-600 hover:text-blue-900 transition-colors duration-150 transform hover:scale-110"
                                                    title="{{ basename($note->attachment_url) }}">
                                                    <i class="fas fa-paperclip mr-1"></i>
                                                    <span class="text-xs">{{ Str::limit(basename($note->attachment_url), 15) }}</span>
                                                </a>
                                            @endif
                                            
                                            <button wire:click="deleteShippingNote({{ $note->id }})"
                                                class="text-red-600 hover:text-red-900 transition-colors duration-150 transform hover:scale-110"
                                                title="{{ __('messages.delete') }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3 ml-2 text-sm text-gray-700 border-l-2 border-gray-200 pl-3">
                                        {{ $note->note }}
                                        
                                        @if($note->status == 'custom_form' && $note->custom_form_id)
                                            @php
                                                $customForm = \App\Models\SupplyChain\CustomForm::find($note->custom_form_id);
                                                $submission = \App\Models\SupplyChain\CustomFormSubmission::where('entity_id', $note->id)
                                                    ->where('form_id', $note->custom_form_id)
                                                    ->latest()
                                                    ->with('fieldValues.field')
                                                    ->first();
                                            @endphp
                                            
                                            @if($customForm && $submission)
                                                <div class="mt-3 bg-blue-50 p-3 rounded-md border border-blue-100">
                                                    <h5 class="text-blue-700 font-medium mb-2">{{ $customForm->name }}</h5>
                                                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2 text-sm">
                                                        @forelse($submission->fieldValues as $fieldValue)
                                                            @if($fieldValue->field)
                                                                <div class="col-span-1">
                                                                    <dt class="font-medium text-gray-700">{{ $fieldValue->field->label }}:</dt>
                                                                    <dd class="text-gray-600">
                                                                        @if($fieldValue->field->type == 'checkbox')
                                                                            {{ $fieldValue->value ? __('messages.yes') : __('messages.no') }}
                                                                        @elseif($fieldValue->field->type == 'date' && !empty($fieldValue->value))
                                                                            {{ \Carbon\Carbon::parse($fieldValue->value)->format('d/m/Y') }}
                                                                        @elseif($fieldValue->field->type == 'select')
                                                                            @php
                                                                                $displayValue = $fieldValue->value;
                                                                                $options = $fieldValue->field->options;
                                                                                
                                                                                // Verifica se options é uma string e tenta decodificar
                                                                                if (is_string($options)) {
                                                                                    $options = json_decode($options, true);
                                                                                }
                                                                                
                                                                                if (is_array($options)) {
                                                                                    foreach ($options as $option) {
                                                                                        if (isset($option['value']) && $option['value'] == $fieldValue->value) {
                                                                                            $displayValue = $option['label'];
                                                                                            break;
                                                                                        }
                                                                                    }
                                                                                }
                                                                            @endphp
                                                                            {{ $displayValue }}
                                                                        @elseif($fieldValue->field->type == 'file' && !empty($fieldValue->value))
                                                                            <div class="flex items-center space-x-2">
                                                                                <span class="inline-flex items-center bg-blue-50 text-xs rounded px-2 py-1 border border-blue-100">
                                                                                    <i class="fas fa-file-alt text-blue-500 mr-1"></i>
                                                                                    {{ Str::limit(basename($fieldValue->value), 15) }}
                                                                                </span>
                                                                                <a href="{{ asset('storage/'.$fieldValue->value) }}" 
                                                                                   download
                                                                                   class="text-blue-600 hover:text-blue-800 inline-flex items-center bg-white border border-blue-100 px-2 py-1 rounded text-xs shadow-sm hover:shadow transition-all">
                                                                                    <i class="fas fa-download mr-1"></i>
                                                                                    {{ __('messages.download') }}
                                                                                </a>
                                                                            </div>
                                                                        @else
                                                                            {{ $fieldValue->value }}
                                                                        @endif
                                                                    </dd>
                                                                </div>
                                                            @endif
                                                        @empty
                                                            <div class="col-span-2 text-gray-500 italic">
                                                                {{ __('messages.no_data_available') }}
                                                            </div>
                                                        @endforelse
                                                    </dl>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                    
                                    <div class="mt-2 text-xs text-gray-500 flex items-center">
                                        <i class="fas fa-user-edit text-gray-400 mr-1"></i>
                                        {{ __('messages.by') }}: 
                                        <span class="font-medium ml-1">
                                            @if($note->created_by)
                                                {{ optional(\App\Models\User::find($note->created_by))->name ?? $note->created_by }}
                                            @elseif($note->updated_by)
                                                {{ optional(\App\Models\User::find($note->updated_by))->name ?? $note->updated_by }}
                                            @else
                                                -
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="p-4 text-center">
                                    <div class="flex flex-col items-center justify-center py-6 space-y-2">
                                        <div class="flex-shrink-0 bg-gray-100 p-3 rounded-full">
                                            <i class="fas fa-shipping-fast text-gray-400 text-xl"></i>
                                        </div>
                                        <p class="text-gray-500 text-sm">{{ __('messages.no_shipping_notes') }}</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    @else
                        <div class="text-center py-6">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <div class="flex-shrink-0 bg-gray-100 p-3 rounded-full">
                                    <i class="fas fa-exclamation-circle text-gray-400 text-xl"></i>
                                </div>
                                <p class="text-gray-500">{{ __('messages.order_not_found') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Botões de Ação -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end">
                <button @click="show = false"
                    class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                    {{ __('messages.close') }}
                </button>
            </div>
        </div>
    </div>
</div>
