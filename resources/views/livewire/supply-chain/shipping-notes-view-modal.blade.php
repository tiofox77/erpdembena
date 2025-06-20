<!-- Modal para Visualizar Detalhes da Nota de Envio -->
<div x-data="{ open: @entangle('showViewModal').defer }" x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        
        <!-- Modal Panel -->
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block w-full max-w-3xl mt-16 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-4 py-3 flex justify-between items-center">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-shipping-fast mr-2"></i>
                    {{ __('messages.shipping_note_details') }}
                </h3>
                <button @click="open = false" class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Content -->
            <div class="p-6">
                @if($viewingNote)
                    <div class="mb-6">
                        <!-- Cabeçalho com info da ordem -->
                        <div class="mb-6 pb-4 border-b border-gray-200">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-800">
                                        @if($viewingNote->purchaseOrder)
                                            {{ __('messages.order') }} #{{ $viewingNote->purchaseOrder->order_number }}
                                        @else
                                            {{ __('messages.order') }} #{{ $viewingNote->purchase_order_id }}
                                        @endif
                                    </h4>
                                    <p class="text-sm text-gray-600">
                                        {{ __('messages.created_on') }}: {{ $viewingNote->created_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                        @if($viewingNote->status == 'order_placed') bg-gray-100 text-gray-800 border border-gray-300
                                        @elseif($viewingNote->status == 'proforma_invoice_received') bg-indigo-100 text-indigo-800 border border-indigo-300
                                        @elseif($viewingNote->status == 'payment_completed') bg-green-100 text-green-800 border border-green-300
                                        @elseif($viewingNote->status == 'du_in_process') bg-orange-100 text-orange-800 border border-orange-300
                                        @elseif($viewingNote->status == 'goods_acquired') bg-blue-100 text-blue-800 border border-blue-300
                                        @elseif($viewingNote->status == 'shipped_to_port') bg-teal-100 text-teal-800 border border-teal-300
                                        @elseif($viewingNote->status == 'shipping_line_booking_confirmed') bg-cyan-100 text-cyan-800 border border-cyan-300
                                        @elseif($viewingNote->status == 'container_loaded') bg-purple-100 text-purple-800 border border-purple-300
                                        @elseif($viewingNote->status == 'on_board') bg-blue-100 text-blue-800 border border-blue-300
                                        @elseif($viewingNote->status == 'arrived_at_port') bg-emerald-100 text-emerald-800 border border-emerald-300
                                        @elseif($viewingNote->status == 'customs_clearance') bg-amber-100 text-amber-800 border border-amber-300
                                        @elseif($viewingNote->status == 'delivered') bg-green-100 text-green-800 border border-green-300
                                        @elseif($viewingNote->status == 'custom_form') bg-blue-100 text-blue-800 border border-blue-300
                                        @endif">
                                        <i class="
                                            @if($viewingNote->status == 'order_placed') fas fa-shopping-cart
                                            @elseif($viewingNote->status == 'proforma_invoice_received') fas fa-file-invoice-dollar
                                            @elseif($viewingNote->status == 'payment_completed') fas fa-money-bill-wave
                                            @elseif($viewingNote->status == 'du_in_process') fas fa-file-alt
                                            @elseif($viewingNote->status == 'goods_acquired') fas fa-boxes
                                            @elseif($viewingNote->status == 'shipped_to_port') fas fa-dolly
                                            @elseif($viewingNote->status == 'shipping_line_booking_confirmed') fas fa-calendar-check
                                            @elseif($viewingNote->status == 'container_loaded') fas fa-container-storage
                                            @elseif($viewingNote->status == 'on_board') fas fa-ship
                                            @elseif($viewingNote->status == 'arrived_at_port') fas fa-anchor
                                            @elseif($viewingNote->status == 'customs_clearance') fas fa-clipboard-check
                                            @elseif($viewingNote->status == 'delivered') fas fa-check-circle
                                            @elseif($viewingNote->status == 'custom_form') fas fa-clipboard-list
                                            @endif mr-1 text-xs"></i>
                                        @if($viewingNote->status == 'custom_form' && $viewingNote->customForm)
                                            {{ $viewingNote->customForm->name }}
                                        @else
                                            {{ $viewingNote->status_text }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Conteúdo principal -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Coluna da esquerda -->
                            <div>
                                <!-- Detalhes da Nota -->
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-4">
                                    <h5 class="font-medium text-gray-700 mb-2">{{ __('messages.note_content') }}</h5>
                                    <p class="text-gray-600 whitespace-pre-line">{{ $viewingNote->note }}</p>
                                </div>
                                
                                <!-- Informações do autor -->
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <h5 class="font-medium text-gray-700 mb-2">{{ __('messages.author_info') }}</h5>
                                    <div class="flex items-center">
                                        <div class="rounded-full bg-blue-100 w-10 h-10 flex items-center justify-center text-blue-600">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-700">
                                                {{ optional($viewingNote->updatedByUser)->name ?? '-' }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ __('messages.created_on') }}: {{ $viewingNote->created_at->format('d/m/Y H:i') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Coluna da direita -->
                            <div>
                                <!-- Anexo -->
                                @if($viewingNote->attachment_url)
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-4">
                                    <h5 class="font-medium text-gray-700 mb-2">{{ __('messages.attachment') }}</h5>
                                    <div class="flex items-center">
                                        <div class="rounded bg-blue-50 p-2 text-blue-600">
                                            <i class="fas fa-file-alt text-xl"></i>
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <p class="text-sm font-medium text-gray-700 truncate">
                                                {{ basename($viewingNote->attachment_url) }}
                                            </p>
                                            <div class="mt-1 flex">
                                                <a href="{{ asset('storage/' . $viewingNote->attachment_url) }}" target="_blank" 
                                                   class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                    <i class="fas fa-eye mr-1"></i>
                                                    {{ __('messages.view') }}
                                                </a>
                                                <a href="{{ asset('storage/' . $viewingNote->attachment_url) }}" download 
                                                   class="ml-2 inline-flex items-center px-2.5 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                    <i class="fas fa-download mr-1"></i>
                                                    {{ __('messages.download') }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                
                                <!-- Dados de Formulário Personalizado -->
                                @if($viewingNote->status == 'custom_form' && $viewingNote->form_data && $viewingNote->customForm)
                                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                    <h5 class="font-medium text-gray-700 mb-2 flex items-center">
                                        <i class="fas fa-clipboard-list text-blue-600 mr-2"></i>
                                        {{ __('messages.custom_form_data') }}: {{ $viewingNote->customForm->name }}
                                    </h5>
                                    
                                    <div class="mt-3 space-y-4">
                                        @foreach($viewingNote->customForm->fields as $field)
                                            <div class="border-b border-blue-100 pb-2">
                                                <p class="text-sm font-medium text-gray-700">{{ $field->label }}</p>
                                                <p class="text-sm text-gray-600">
                                                    @if(isset($viewingNote->form_data[$field->name]))
                                                        @if($field->type == 'checkbox')
                                                            {{ $viewingNote->form_data[$field->name] ? __('messages.yes') : __('messages.no') }}
                                                        @elseif($field->type == 'date')
                                                            {{ \Carbon\Carbon::parse($viewingNote->form_data[$field->name])->format('d/m/Y') }}
                                                        @elseif($field->type == 'select' && $field->options)
                                                            @php
                                                                $options = json_decode($field->options, true);
                                                                $value = $viewingNote->form_data[$field->name];
                                                                $displayValue = $value;
                                                                if (is_array($options)) {
                                                                    foreach ($options as $option) {
                                                                        if (isset($option['value']) && $option['value'] == $value && isset($option['label'])) {
                                                                            $displayValue = $option['label'];
                                                                            break;
                                                                        }
                                                                    }
                                                                }
                                                            @endphp
                                                            {{ $displayValue }}
                                                        @else
                                                            {{ $viewingNote->form_data[$field->name] }}
                                                        @endif
                                                    @else
                                                        <span class="text-gray-400">{{ __('messages.not_provided') }}</span>
                                                    @endif
                                                </p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                
                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                        <button type="button" wire:click="closeViewModal" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-times mr-2"></i>
                            {{ __('messages.close') }}
                        </button>
                        <button type="button" wire:click="editNote({{ $viewingNote->id }})" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-edit mr-2"></i>
                            {{ __('messages.edit') }}
                        </button>
                    </div>
                @else
                    <div class="text-center py-6">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100">
                            <i class="fas fa-exclamation-circle text-gray-500"></i>
                        </div>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('messages.no_note_selected') }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ __('messages.select_note_to_view') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
