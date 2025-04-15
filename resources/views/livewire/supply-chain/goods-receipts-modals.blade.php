<!-- Modal de Criar/Editar -->
<div 
    x-data="{ open: @entangle('showModal') }" 
    x-show="open" 
    x-cloak 
    class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50" 
    role="dialog" 
    aria-modal="true"
    x-transition:enter="transition ease-out duration-300" 
    x-transition:enter-start="opacity-0" 
    x-transition:enter-end="opacity-100" 
    x-transition:leave="transition ease-in duration-200" 
    x-transition:leave-start="opacity-100" 
    x-transition:leave-end="opacity-0">
    <div class="relative top-20 mx-auto p-1 w-full max-w-4xl">
        <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="transform opacity-0 scale-95" 
             x-transition:enter-end="transform opacity-100 scale-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="transform opacity-100 scale-100" 
             x-transition:leave-end="transform opacity-0 scale-95">
            <!-- Cabeçalho com gradiente -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas {{ $editMode ? 'fa-edit' : 'fa-plus-circle' }} mr-2"></i>
                    {{ $editMode ? __('messages.edit_goods_receipt') : __('messages.create_goods_receipt') }}
                </h3>
                <button type="button" wire:click="closeModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Conteúdo da Modal -->
            <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
                <!-- Informações Básicas -->
                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                    <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                        <h3 class="text-base font-medium text-gray-700">{{ __('messages.basic_information') }}</h3>
                    </div>
                    <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Número do Recibo -->
                        <div>
                            <label for="receipt_number" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.receipt_number') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="receipt_number" wire:model.defer="goodsReceipt.receipt_number" 
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                {{ $editMode ? 'readonly' : '' }}>
                            @error('goodsReceipt.receipt_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        <!-- Data do Recibo -->
                        <div>
                            <label for="receipt_date" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.receipt_date') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="receipt_date" wire:model.defer="goodsReceipt.receipt_date" 
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            @error('goodsReceipt.receipt_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        <!-- Ordem de Compra Associada -->
                        <div>
                            <label for="purchase_order_id" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.purchase_order') }}
                            </label>
                            <select id="purchase_order_id" wire:model="goodsReceipt.purchase_order_id" wire:change="loadPurchaseOrderItems" 
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                <option value="">{{ __('messages.select_purchase_order') }}</option>
                                @foreach($purchaseOrders ?? [] as $po)
                                    <option value="{{ $po->id }}">{{ $po->order_number }}</option>
                                @endforeach
                            </select>
                            @error('goodsReceipt.purchase_order_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        <!-- Fornecedor -->
                        <div>
                            <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.supplier') }} <span class="text-red-500">*</span>
                            </label>
                            <select id="supplier_id" wire:model.defer="goodsReceipt.supplier_id" 
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md {{ !empty($goodsReceipt['purchase_order_id']) ? 'bg-gray-100' : '' }}"
                                {{ !empty($goodsReceipt['purchase_order_id']) ? 'disabled' : '' }}>
                                <option value="">{{ __('messages.select_supplier') }}</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            @error('goodsReceipt.supplier_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        <!-- Local de Recebimento -->
                        <div>
                            <label for="location_id" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.location') }} <span class="text-red-500">*</span>
                            </label>
                            <select id="location_id" wire:model.defer="goodsReceipt.location_id" 
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                <option value="">{{ __('messages.select_location') }}</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                @endforeach
                            </select>
                            @error('goodsReceipt.location_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        <!-- Número de Referência -->
                        <div>
                            <label for="reference_number" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.reference_number') }}
                            </label>
                            <input type="text" id="reference_number" wire:model.defer="goodsReceipt.reference_number" 
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            @error('goodsReceipt.reference_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.status') }} <span class="text-red-500">*</span>
                            </label>
                            <select id="status" wire:model.defer="goodsReceipt.status" 
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                <option value="pending">{{ __('messages.pending') }}</option>
                                <option value="processing">{{ __('messages.processing') }}</option>
                                <option value="completed">{{ __('messages.completed') }}</option>
                                <option value="cancelled">{{ __('messages.cancelled') }}</option>
                            </select>
                            @error('goodsReceipt.status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        <!-- Observações -->
                        <div class="md:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.notes') }}
                            </label>
                            <textarea id="notes" wire:model.defer="goodsReceipt.notes" rows="3" 
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                            @error('goodsReceipt.notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Itens do Recibo -->
                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                    <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                        <i class="fas fa-box-open text-blue-600 mr-2"></i>
                        <h3 class="text-base font-medium text-gray-700">{{ __('messages.receipt_items') }}</h3>
                    </div>
                    <div class="p-4">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('messages.product') }}
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('messages.quantity') }}
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('messages.accepted_quantity') }}
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('messages.rejected_quantity') }}
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('messages.unit_cost') }}
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('messages.actions') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($receiptItems as $index => $item)
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $item['product_name'] }}</div>
                                            <div class="text-xs text-gray-500">{{ $item['product_code'] ?? '' }}</div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right">
                                            <input type="number" min="0" step="0.01" wire:model.defer="receiptItems.{{ $index }}.quantity" 
                                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right">
                                            <input type="number" min="0" step="0.01" wire:model="receiptItems.{{ $index }}.accepted_quantity" 
                                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right">
                                            <input type="number" min="0" step="0.01" wire:model="receiptItems.{{ $index }}.rejected_quantity" 
                                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right">
                                            <input type="number" min="0" step="0.01" wire:model="receiptItems.{{ $index }}.unit_cost" 
                                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right">
                                            <button wire:click="removeItem({{ $index }})" class="text-red-600 hover:text-red-900 transition-colors duration-150 transform hover:scale-110">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-3 text-center text-sm text-gray-500">
                                            {{ __('messages.no_items_added') }}
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Botão de adicionar item -->
                        <div class="mt-4 flex justify-end">
                            <button type="button" wire:click="addItem" 
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                <i class="fas fa-plus-circle mr-2"></i>
                                {{ __('messages.add_item') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Rodapé -->
            <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                <button type="button" wire:click="closeModal" 
                    class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                    <i class="fas fa-times mr-2"></i>
                    {{ __('messages.cancel') }}
                </button>
                
                <button type="button" wire:click="save" 
                    class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                    <i class="fas fa-save mr-2"></i>
                    {{ __('messages.save') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div 
    x-data="{ open: @entangle('showConfirmDelete') }" 
    x-show="open" 
    x-cloak 
    class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50" 
    role="dialog" 
    aria-modal="true"
    x-transition:enter="transition ease-out duration-300" 
    x-transition:enter-start="opacity-0" 
    x-transition:enter-end="opacity-100" 
    x-transition:leave="transition ease-in duration-200" 
    x-transition:leave-start="opacity-100" 
    x-transition:leave-end="opacity-0">
    <div class="relative top-20 mx-auto p-1 w-full max-w-md">
        <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="transform opacity-0 scale-95" 
             x-transition:enter-end="transform opacity-100 scale-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="transform opacity-100 scale-100" 
             x-transition:leave-end="transform opacity-0 scale-95">
            <!-- Cabeçalho com gradiente -->
            <div class="bg-gradient-to-r from-red-600 to-red-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2 animate-pulse"></i>
                    {{ __('messages.confirm_delete') }}
                </h3>
                <button type="button" wire:click="cancelDelete" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Conteúdo da Modal -->
            <div class="p-6 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <i class="fas fa-trash-alt text-red-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('messages.delete_goods_receipt') }}</h3>
                <p class="text-sm text-gray-500 mb-6">
                    {{ __('messages.delete_goods_receipt_confirm') }}
                </p>
                <div class="flex justify-center space-x-4">
                    <button type="button" wire:click="cancelDelete" 
                        class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <i class="fas fa-times mr-2"></i>
                        {{ __('messages.cancel') }}
                    </button>
                    
                    <button type="button" wire:click="delete" 
                        class="inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <i class="fas fa-trash-alt mr-2"></i>
                        {{ __('messages.delete') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Visualização -->
<div 
    x-data="{ open: @entangle('showViewModal') }" 
    x-show="open" 
    x-cloak 
    class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50" 
    role="dialog" 
    aria-modal="true"
    x-transition:enter="transition ease-out duration-300" 
    x-transition:enter-start="opacity-0" 
    x-transition:enter-end="opacity-100" 
    x-transition:leave="transition ease-in duration-200" 
    x-transition:leave-start="opacity-100" 
    x-transition:leave-end="opacity-0">
    <div class="relative top-20 mx-auto p-1 w-full max-w-4xl">
        <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="transform opacity-0 scale-95" 
             x-transition:enter-end="transform opacity-100 scale-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="transform opacity-100 scale-100" 
             x-transition:leave-end="transform opacity-0 scale-95">
            <!-- Cabeçalho com gradiente -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-eye mr-2"></i>
                    {{ __('messages.view_goods_receipt') }}
                </h3>
                <button type="button" wire:click="closeViewModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Conteúdo da Modal -->
            <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
                <!-- Tabs -->
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                        <button wire:click="changeTab('details')" type="button"
                            class="px-1 py-2 font-medium text-sm whitespace-nowrap transition-colors duration-200 focus:outline-none 
                            {{ $activeTab === 'details' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            <i class="fas fa-info-circle mr-1"></i>
                            {{ __('messages.details') }}
                        </button>
                        <button wire:click="changeTab('items')" type="button"
                            class="px-1 py-2 font-medium text-sm whitespace-nowrap transition-colors duration-200 focus:outline-none 
                            {{ $activeTab === 'items' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            <i class="fas fa-list mr-1"></i>
                            {{ __('messages.items') }}
                        </button>
                    </nav>
                </div>

                @if ($activeTab === 'details')
                <!-- Detalhes -->
                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                    <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                        <h3 class="text-base font-medium text-gray-700">{{ __('messages.receipt_information') }}</h3>
                    </div>
                    
                    <div class="p-4">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-4">
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">{{ __('messages.receipt_number') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $viewingReceipt->receipt_number ?? '' }}</dd>
                            </div>
                            
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">{{ __('messages.receipt_date') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $viewingReceipt ? date('d/m/Y', strtotime($viewingReceipt->receipt_date)) : '' }}</dd>
                            </div>
                            
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">{{ __('messages.status') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($viewingReceipt)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $viewingReceipt->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                        ($viewingReceipt->status == 'processing' ? 'bg-blue-100 text-blue-800' : 
                                        ($viewingReceipt->status == 'completed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')) }}">
                                        <i class="fas 
                                            {{ $viewingReceipt->status == 'pending' ? 'fa-clock' : 
                                            ($viewingReceipt->status == 'processing' ? 'fa-spinner' : 
                                            ($viewingReceipt->status == 'completed' ? 'fa-check-circle' : 'fa-times-circle')) }} mr-1.5"></i>
                                        {{ __('messages.status_'.$viewingReceipt->status) }}
                                    </span>
                                    @endif
                                </dd>
                            </div>
                            
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">{{ __('messages.supplier') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $viewingReceipt->supplier->name ?? '' }}</dd>
                            </div>
                            
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">{{ __('messages.location') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $viewingReceipt->location->name ?? '' }}</dd>
                            </div>
                            
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">{{ __('messages.purchase_order') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($viewingReceipt && $viewingReceipt->purchase_order)
                                        <a href="javascript:void(0)" wire:click="viewPurchaseOrder({{ $viewingReceipt->purchase_order_id }})"
                                            class="text-blue-600 hover:text-blue-800 hover:underline">
                                            {{ $viewingReceipt->purchase_order->order_number }}
                                        </a>
                                    @else
                                        {{ __('messages.none') }}
                                    @endif
                                </dd>
                            </div>
                            
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">{{ __('messages.reference_number') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $viewingReceipt->reference_number ?? __('messages.none') }}</dd>
                            </div>
                            
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">{{ __('messages.created_by') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $viewingReceipt->createdBy->name ?? '' }}</dd>
                            </div>
                            
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">{{ __('messages.created_at') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $viewingReceipt ? date('d/m/Y H:i', strtotime($viewingReceipt->created_at)) : '' }}</dd>
                            </div>
                            
                            @if($viewingReceipt && $viewingReceipt->notes)
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">{{ __('messages.notes') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900 p-2 bg-gray-50 rounded border border-gray-200">{{ $viewingReceipt->notes }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>
                @endif
                
                @if ($activeTab === 'items')
                <!-- Itens do Recebimento -->
                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                    <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                        <i class="fas fa-box-open text-blue-600 mr-2"></i>
                        <h3 class="text-base font-medium text-gray-700">{{ __('messages.receipt_items') }}</h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('messages.product') }}
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('messages.description') }}
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('messages.quantity') }}
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('messages.batch_number') }}
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('messages.expiry_date') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @if($viewingReceipt && $viewingReceipt->items)
                                    @forelse($viewingReceipt->items as $item)
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $item->product->name ?? $item->product_id }}</div>
                                            <div class="text-xs text-gray-500">{{ $item->product->sku ?? '' }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-sm text-gray-900">{{ $item->description }}</div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right">
                                            <div class="text-sm font-medium text-gray-900">{{ $item->quantity }} {{ $item->unit_of_measure }}</div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right">
                                            <div class="text-sm text-gray-900">{{ $item->batch_number ?? __('messages.none') }}</div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right">
                                            <div class="text-sm text-gray-900">
                                                {{ $item->expiry_date ? date('d/m/Y', strtotime($item->expiry_date)) : __('messages.none') }}
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-3 text-center text-sm text-gray-500">
                                            {{ __('messages.no_items_found') }}
                                        </td>
                                    </tr>
                                    @endforelse
                                @else
                                <tr>
                                    <td colspan="5" class="px-4 py-3 text-center text-sm text-gray-500">
                                        {{ __('messages.no_items_found') }}
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
            
            <!-- Rodapé -->
            <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                @if($viewingReceipt && $viewingReceipt->status === 'pending')
                <button type="button" wire:click="editReceipt({{ $viewingReceipt ? $viewingReceipt->id : null }})" 
                    class="inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                    <i class="fas fa-edit mr-2"></i>
                    {{ __('messages.edit') }}
                </button>
                @endif
                
                <button type="button" wire:click="closeViewModal" 
                    class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                    <i class="fas fa-times mr-2"></i>
                    {{ __('messages.close') }}
                </button>
            </div>
        </div>
    </div>
</div>