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
                                    <select wire:model.defer="shippingNote.status" id="shipping_status" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white">
                                        <option value="">{{ __('messages.select_status') }}</option>
                                        <option value="order_placed">{{ __('messages.shipping_status_order_placed') }}</option>
                                        <option value="proforma_invoice_received">{{ __('messages.shipping_status_proforma_invoice_received') }}</option>
                                        <option value="payment_completed">{{ __('messages.shipping_status_payment_completed') }}</option>
                                        <option value="du_in_process">{{ __('messages.shipping_status_du_in_process') }}</option>
                                        <option value="goods_acquired">{{ __('messages.shipping_status_goods_acquired') }}</option>
                                        <option value="shipped_to_port">{{ __('messages.shipping_status_shipped_to_port') }}</option>
                                        <option value="shipping_line_booking_confirmed">{{ __('messages.shipping_status_shipping_line_booking_confirmed') }}</option>
                                        <option value="container_loaded">{{ __('messages.shipping_status_container_loaded') }}</option>
                                        <option value="on_board">{{ __('messages.shipping_status_on_board') }}</option>
                                        <option value="arrived_at_port">{{ __('messages.shipping_status_arrived_at_port') }}</option>
                                        <option value="customs_clearance">{{ __('messages.shipping_status_customs_clearance') }}</option>
                                        <option value="delivered">{{ __('messages.shipping_status_delivered') }}</option>
                                    </select>
                                    @error('shippingNote.status')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="shipping_note" class="block text-sm font-medium text-gray-700">{{ __('messages.note') }} <span class="text-red-500">*</span></label>
                                    <textarea wire:model.defer="shippingNote.note" id="shipping_note" rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white"
                                        placeholder="{{ __('messages.enter_shipping_details') }}"></textarea>
                                    @error('shippingNote.note')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="shipping_attachment" class="block text-sm font-medium text-gray-700">{{ __('messages.attachment') }}</label>
                                    <input type="file" wire:model="shippingAttachment" id="shipping_attachment"
                                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                    @error('shippingAttachment')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <div wire:loading wire:target="shippingAttachment" class="mt-1 text-sm text-blue-600">
                                        <i class="fas fa-spinner fa-spin mr-1"></i> {{ __('messages.uploading') }}
                                    </div>
                                </div>
                                
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
