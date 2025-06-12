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
    <div class="relative top-4 sm:top-10 md:top-20 mx-auto p-1 w-11/12 sm:w-11/12 md:w-11/12 lg:w-11/12 xl:w-10/12 2xl:w-9/12 max-w-7xl">
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
            <div class="p-4 sm:p-6 space-y-4 sm:space-y-6 max-h-[80vh] overflow-y-auto">
                @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded-md shadow-sm animate-pulse">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500 text-lg"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-700">
                                {{ __('messages.validation_error') }}
                            </h3>
                            <div class="mt-2 text-sm text-red-600">
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @error('items')
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded-md shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-box-open text-red-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">{{ $message }}</p>
                        </div>
                    </div>
                </div>
                @enderror
                <!-- Informações Básicas -->
                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                    <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                        <h3 class="text-base font-medium text-gray-700">{{ __('messages.basic_information') }}</h3>
                    </div>
                    <div class="p-2 sm:p-4 grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                        <!-- Número do Recibo -->
                        <div>
                            <label for="receipt_number" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.receipt_number') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="receipt_number" wire:model.defer="goodsReceipt.receipt_number" 
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md transition duration-150 ease-in-out"
                                {{ $editMode ? 'readonly' : '' }}>
                            @error('goodsReceipt.receipt_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        <!-- Data do Recibo -->
                        <div>
                            <label for="receipt_date" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.receipt_date') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="receipt_date" wire:model.defer="goodsReceipt.receipt_date" 
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md transition duration-150 ease-in-out transition duration-150 ease-in-out">
                            @error('goodsReceipt.receipt_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        <!-- Ordem de Compra Associada -->
                        <div>
                            <label for="purchase_order_id" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.purchase_order') }}
                            </label>
                            <select id="purchase_order_id" wire:model="goodsReceipt.purchase_order_id" wire:change="loadPurchaseOrderItems" 
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md transition duration-150 ease-in-out">
                                <option value="">{{ __('messages.select_purchase_order') }}</option>
                                @foreach($purchaseOrders ?? [] as $po)
                                    <option value="{{ $po->id }}">{{ $po->order_number }}--{{ $po->other_reference }}</option>
                                @endforeach
                            </select>
                            @error('goodsReceipt.purchase_order_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        <!-- Fornecedor -->
                        <div>
                            <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.supplier') }} <span class="text-red-500">*</span>
                            </label>
                            <select id="supplier_id" wire:model="goodsReceipt.supplier_id" 
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md transition duration-150 ease-in-out {{ !empty($goodsReceipt['purchase_order_id']) ? 'bg-gray-100' : '' }}"
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
                            <select id="location_id" wire:model="goodsReceipt.location_id" 
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md transition duration-150 ease-in-out">
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
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md transition duration-150 ease-in-out">
                            @error('goodsReceipt.reference_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.status') }} <span class="text-red-500">*</span>
                            </label>
                            <select id="status" wire:model.defer="goodsReceipt.status" 
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md transition duration-150 ease-in-out">
                                <option value="pending">{{ __('messages.pending') }}</option>
                                <option value="partially_processed">{{ __('messages.partially_processed') }}</option>
                                <option value="completed">{{ __('messages.completed') }}</option>
                                <option value="discrepancy">{{ __('messages.discrepancy') }}</option>
                            </select>
                            @error('goodsReceipt.status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        <!-- Observações -->
                        <div class="md:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.notes') }}
                            </label>
                            <textarea id="notes" wire:model.defer="goodsReceipt.notes" rows="3" 
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md transition duration-150 ease-in-out"></textarea>
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
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider" title="{{ __('messages.ordered_quantity_help') }}">
                                            {{ __('messages.ordered') }}
                                            <div class="text-xs font-normal text-gray-400">{{ __('messages.quantity') }}</div>
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider" title="{{ __('messages.previously_received_help') }}">
                                            {{ __('messages.previously_received') }}
                                            <div class="text-xs font-normal text-gray-400">{{ __('messages.quantity') }}</div>
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider" title="{{ __('messages.remaining_quantity_help') }}">
                                            {{ __('messages.remaining') }}
                                            <div class="text-xs font-normal text-gray-400">{{ __('messages.quantity') }}</div>
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider" title="{{ __('messages.this_receipt_help') }}">
                                            {{ __('messages.this_receipt') }}
                                            <div class="text-xs font-normal text-gray-400">{{ __('messages.quantity') }}</div>
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider" title="{{ __('messages.rejected_quantity_help') }}">
                                            {{ __('messages.rejected_quantity') }}
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider" title="{{ __('messages.accepted_quantity_help') }}">
                                            {{ __('messages.accepted_quantity') }}
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('messages.unit_cost') }}
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('messages.actions') }}
                                        </th>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($receiptItems as $index => $item)
                                    @php
                                        $orderedQty = $item['ordered_quantity'] ?? $item['quantity'] ?? 0;
                                        $previouslyReceived = $item['previously_received'] ?? 0;
                                        $remainingQty = isset($item['remaining_quantity']) ? $item['remaining_quantity'] : max(0, $orderedQty - $previouslyReceived);
                                        $thisReceiptQty = $item['quantity'] ?? 0;
                                        $rejectedQty = $item['rejected_quantity'] ?? 0;
                                        // Always start at 0 if not explicitly set
                                        $acceptedQty = $item['accepted_quantity'] ?? 0;
                                        $isPartiallyProcessed = isset($goodsReceipt['status']) && $goodsReceipt['status'] === 'partially_processed';
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition-colors duration-150 {{ $isPartiallyProcessed ? 'bg-yellow-50' : '' }}">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $item['product_name'] ?? 'N/A' }}
                                                @if(isset($item['original_accepted']) || isset($item['original_rejected']))
                                                <div class="text-xs text-gray-500 mt-1">
                                                    {{ __('messages.previously_received') }}: 
                                                    @if(isset($item['original_accepted']) && $item['original_accepted'] > 0)
                                                        {{ number_format($item['original_accepted'], 2) }} {{ __('messages.accepted') }}
                                                    @endif
                                                    @if(isset($item['original_rejected']) && $item['original_rejected'] > 0)
                                                        <span class="text-red-500">
                                                            {{ number_format($item['original_rejected'], 2) }} {{ __('messages.rejected') }}
                                                        </span>
                                                    @endif
                                                </div>
                                                @endif
                                            </div>
                                            <div class="text-sm text-gray-500">{{ $item['product_code'] ?? '' }}</div>
                                        </td>
                                        
                                        <!-- Ordered Quantity -->
                                        <td class="px-4 py-3 whitespace-nowrap text-right">
                                            <span class="text-sm text-gray-900">{{ number_format($orderedQty, 2) }}</span>
                                        </td>
                                        
                                        <!-- Previously Received -->
                                        <td class="px-4 py-3 whitespace-nowrap text-right">
                                            <span class="text-sm text-gray-900">{{ number_format($previouslyReceived, 2) }}</span>
                                        </td>
                                        
                                        <!-- Remaining Quantity -->
                                        <td class="px-4 py-3 whitespace-nowrap text-right">
                                            <span class="text-sm text-gray-900">{{ number_format($remainingQty, 2) }}</span>
                                            <div class="text-xs text-blue-600">
                                                {{ __('messages.max_receivable') }}: {{ number_format($previouslyReceived, 2) }}
                                            </div>
                                        </td>
                                        
                                        <!-- This Receipt Quantity -->
                                        <td class="px-4 py-3 whitespace-nowrap text-right">
                                            <input type="text" 
                                                x-data="{
                                                    rawValue: '0.00',
                                                    isTyping: false,
                                                    formatNumber(value) {
                                                        if (!value) return '0,00';
                                                        const num = parseFloat(value);
                                                        if (isNaN(num)) return '0,00';
                                                        return num.toLocaleString('pt-PT', {
                                                            minimumFractionDigits: 2,
                                                            maximumFractionDigits: 2,
                                                            useGrouping: true
                                                        });
                                                    },
                                                    parseInput(value) {
                                                        // Remove all non-digit characters except comma
                                                        let clean = value.replace(/[^\d,]/g, '');
                                                        
                                                        // Handle multiple commas by keeping only the first one
                                                        const commaIndex = clean.indexOf(',');
                                                        if (commaIndex !== -1) {
                                                            const before = clean.substring(0, commaIndex + 1);
                                                            const after = clean.substring(commaIndex + 1).replace(/[^\d]/g, '');
                                                            clean = before + after;
                                                        }
                                                        
                                                        // Limit to 2 decimal places
                                                        const parts = clean.split(',');
                                                        if (parts.length > 1) {
                                                            parts[1] = parts[1].substring(0, 2);
                                                            clean = parts.join(',');
                                                        }
                                                        
                                                        return clean;
                                                    },
                                                    toRawNumber(value) {
                                                        if (!value) return '0.00';
                                                        return value.replace(/\./g, '').replace(',', '.');
                                                    }
                                                }"
                                                x-init="
                                                    // Set initial formatted value
                                                    $el.value = formatNumber(rawValue);
                                                    
                                                    // Watch for changes from Livewire
                                                    $watch('$wire.receiptItems[{{ $index }}].quantity', value => {
                                                        if (!isTyping && value !== undefined && value !== null) {
                                                            rawValue = value;
                                                            $el.value = formatNumber(value);
                                                        }
                                                    });
                                                "
                                                @input="
                                                    isTyping = true;
                                                    const formatted = parseInput($event.target.value);
                                                    $el.value = formatted;
                                                    
                                                    // Update raw value and Livewire
                                                    rawValue = toRawNumber(formatted);
                                                    $wire.set('receiptItems.{{ $index }}.quantity', rawValue, false);
                                                    $dispatch('receiptItemUpdated', { index: {{ $index }}, field: 'quantity', value: rawValue });
                                                "
                                                @blur="
                                                    isTyping = false;
                                                    // Ensure proper formatting on blur
                                                    $el.value = formatNumber(rawValue);
                                                "
                                                min="0" 
                                                step="0.01" 
                                                max="{{ $item['max_receivable'] ?? $remainingQty }}" 
                                                class="w-24 text-right border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150 ease-in-out"
                                                {{ ($editMode && $goodsReceipt['status'] !== 'pending' && $goodsReceipt['status'] !== 'partially_processed') ? 'readonly' : '' }}>
                                        </td>
                                        
                                        <!-- Rejected Quantity -->
                                        <td class="px-4 py-3 whitespace-nowrap text-right">
                                            <input type="text" 
                                                wire:model.live="receiptItems.{{ $index }}.rejected_quantity" 
                                                x-data="{
                                                    rawValue: '{{ $item['rejected_quantity'] ?? '0' }}',
                                                    isTyping: false,
                                                    formatNumber(value) {
                                                        if (!value) return '0,00';
                                                        const num = parseFloat(value);
                                                        if (isNaN(num)) return '0,00';
                                                        return num.toLocaleString('pt-PT', {
                                                            minimumFractionDigits: 2,
                                                            maximumFractionDigits: 2,
                                                            useGrouping: true
                                                        });
                                                    },
                                                    parseInput(value) {
                                                        // Remove all non-digit characters except comma
                                                        let clean = value.replace(/[^\d,]/g, '');
                                                        
                                                        // Handle multiple commas by keeping only the first one
                                                        const commaIndex = clean.indexOf(',');
                                                        if (commaIndex !== -1) {
                                                            const before = clean.substring(0, commaIndex + 1);
                                                            const after = clean.substring(commaIndex + 1).replace(/[^\d]/g, '');
                                                            clean = before + after;
                                                        }
                                                        
                                                        // Limit to 2 decimal places
                                                        const parts = clean.split(',');
                                                        if (parts.length > 1) {
                                                            parts[1] = parts[1].substring(0, 2);
                                                            clean = parts.join(',');
                                                        }
                                                        
                                                        return clean;
                                                    },
                                                    toRawNumber(value) {
                                                        if (!value) return '0.00';
                                                        return value.replace(/\./g, '').replace(',', '.');
                                                    }
                                                }"
                                                x-init="
                                                    // Set initial formatted value
                                                    $el.value = formatNumber(rawValue);
                                                    
                                                    // Watch for changes from Livewire
                                                    $watch('$wire.receiptItems[{{ $index }}].rejected_quantity', value => {
                                                        if (!isTyping && value !== undefined && value !== null) {
                                                            rawValue = value;
                                                            $el.value = formatNumber(value);
                                                        }
                                                    });
                                                "
                                                @input="
                                                    isTyping = true;
                                                    const formatted = parseInput($event.target.value);
                                                    $el.value = formatted;
                                                    
                                                    // Update raw value and Livewire
                                                    rawValue = toRawNumber(formatted);
                                                    $wire.set('receiptItems.{{ $index }}.rejected_quantity', rawValue, false);
                                                    $dispatch('receiptItemUpdated', { index: {{ $index }}, field: 'rejected_quantity', value: rawValue });
                                                "
                                                @blur="
                                                    isTyping = false;
                                                    // Ensure proper formatting on blur
                                                    $el.value = formatNumber(rawValue);
                                                "
                                                min="0" 
                                                step="0.01" 
                                                max="{{ $item['quantity'] ?? 0 }}" 
                                                class="w-24 text-right border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                {{ ($editMode && $goodsReceipt['status'] !== 'pending' && $goodsReceipt['status'] !== 'partially_processed') ? 'readonly' : '' }}>
                                        </td>
                                        
                                        <!-- Accepted Quantity -->
                                        <td class="px-4 py-3 whitespace-nowrap text-right">
                                            @if(($editMode && $goodsReceipt['status'] === 'partially_processed') || !$editMode)
                                                <input type="text" 
                                                    wire:model.live="receiptItems.{{ $index }}.accepted_quantity" 
                                                    x-data="{
                                                        rawValue: '{{ $editMode ? ($item['this_receipt_quantity'] ?? '0') : ($item['accepted_quantity'] ?? '0') }}',
                                                        isTyping: false,
                                                        formatNumber(value) {
                                                            if (!value) return '0,00';
                                                            const num = parseFloat(value);
                                                            if (isNaN(num)) return '0,00';
                                                            return num.toLocaleString('pt-PT', {
                                                                minimumFractionDigits: 2,
                                                                maximumFractionDigits: 2,
                                                                useGrouping: true
                                                            });
                                                        },
                                                        parseInput(value) {
                                                            // Remove all non-digit characters except comma
                                                            let clean = value.replace(/[^\d,]/g, '');
                                                            
                                                            // Handle multiple commas by keeping only the first one
                                                            const commaIndex = clean.indexOf(',');
                                                            if (commaIndex !== -1) {
                                                                const before = clean.substring(0, commaIndex + 1);
                                                                const after = clean.substring(commaIndex + 1).replace(/[^\d]/g, '');
                                                                clean = before + after;
                                                            }
                                                            
                                                            // Limit to 2 decimal places
                                                            const parts = clean.split(',');
                                                            if (parts.length > 1) {
                                                                parts[1] = parts[1].substring(0, 2);
                                                                clean = parts.join(',');
                                                            }
                                                            
                                                            return clean;
                                                        },
                                                        toRawNumber(value) {
                                                            if (!value) return '0.00';
                                                            return value.replace(/\./g, '').replace(',', '.');
                                                        },
                                                        validateQuantity(value) {
                                                            const numValue = parseFloat(value);
                                                            const maxQty = parseFloat({{ $orderedQty }});
                                                            
                                                            if (numValue > maxQty) {
                                                                $dispatch('notify', {
                                                                    type: 'error',
                                                                    title: '{{ __('messages.error') }}',
                                                                    message: '{{ __('messages.exceed_ordered_qty') }}'
                                                                });
                                                                return maxQty.toFixed(2);
                                                            }
                                                            return value;
                                                        }
                                                    }"
                                                    x-init="
                                                        // Set initial formatted value
                                                        $el.value = formatNumber(rawValue);
                                                        
                                                        // Watch for changes from Livewire
                                                        $watch('$wire.receiptItems[{{ $index }}].accepted_quantity', value => {
                                                            if (!isTyping && value !== undefined && value !== null) {
                                                                rawValue = value;
                                                                $el.value = formatNumber(value);
                                                            }
                                                        });
                                                    "
                                                    @input="
                                                        isTyping = true;
                                                        const formatted = parseInput($event.target.value);
                                                        $el.value = formatted;
                                                        
                                                        // Update raw value and Livewire
                                                        rawValue = toRawNumber(formatted);
                                                        $wire.set('receiptItems.{{ $index }}.accepted_quantity', rawValue, false);
                                                        $dispatch('receiptItemUpdated', { index: {{ $index }}, field: 'accepted_quantity', value: rawValue });
                                                    "
                                                    @blur="
                                                        isTyping = false;
                                                        
                                                        // Validate against max quantity
                                                        const validatedValue = validateQuantity(rawValue);
                                                        if (validatedValue !== rawValue) {
                                                            rawValue = validatedValue;
                                                            $wire.set('receiptItems.{{ $index }}.accepted_quantity', validatedValue, false);
                                                            $dispatch('receiptItemUpdated', { index: {{ $index }}, field: 'accepted_quantity', value: validatedValue });
                                                        }
                                                        
                                                        // Ensure proper formatting on blur
                                                        $el.value = formatNumber(rawValue);
                                                    "
                                                    "
                                                    min="0" 
                                                    step="0.01" 
                                                    max="{{ $orderedQty }}" 
                                                    class="w-24 text-right border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                    {{ ($editMode && $goodsReceipt['status'] !== 'pending' && $goodsReceipt['status'] !== 'partially_processed') ? 'readonly' : '' }}>
                                                @if(isset($item['original_accepted']))
                                                <div class="text-xs text-gray-500">
                                                    {{ __('messages.total') }}: {{ number_format($previouslyReceived + (float)($item['accepted_quantity'] ?? 0), 2) }}
                                                </div>
                                                @endif
                                                @error("receiptItems.{$index}.accepted_quantity") <div class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</div> @enderror
                                            @else
                                                <span class="text-sm text-gray-900">{{ number_format($acceptedQty, 2) }}</span>
                                                @if(isset($item['original_accepted']))
                                                <div class="text-xs text-gray-500">
                                                    {{ __('messages.total') }}: {{ number_format($previouslyReceived + (float)($item['accepted_quantity'] ?? 0), 2) }}
                                                </div>
                                                @endif
                                                @error("receiptItems.{$index}.accepted_quantity") <div class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</div> @enderror
                                            @endif
                                        </td>
                                        
                                        <!-- Unit Cost -->
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <input type="number" 
                                                min="0" 
                                                step="0.01" 
                                                wire:model="receiptItems.{{ $index }}.unit_cost" 
                                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md transition duration-150 ease-in-out">
                                        </td>
                                        
                                        <!-- Actions -->
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
                        
                        <!-- Botão de adicionar item foi removido -->
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
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-70 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="save"><i class="fas fa-save mr-2"></i> {{ __('messages.save') }}</span>
                    <span wire:loading wire:target="save" class="inline-flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('messages.saving') }}...
                    </span>
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
    <div class="relative top-4 sm:top-10 md:top-20 mx-auto p-1 w-11/12 sm:w-11/12 md:w-11/12 lg:w-11/12 xl:w-10/12 2xl:w-9/12 max-w-7xl">
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
            <div class="p-4 sm:p-6 space-y-4 sm:space-y-6 max-h-[80vh] overflow-y-auto">
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