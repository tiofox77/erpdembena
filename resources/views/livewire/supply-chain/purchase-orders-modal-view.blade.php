<!-- Modal de Visualização -->
<div 
    x-data="{ open: @entangle('showViewModal').live, tab: @entangle('activeTab').live }" 
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
                    {{ __('messages.view_purchase_order') }} - {{ $viewingOrder ? $viewingOrder->order_number : '' }}
                </h3>
                <button type="button" wire:click="closeViewModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Abas de navegação -->
            <div class="bg-gray-100 px-4 py-3 border-b border-gray-200">
                <div class="flex space-x-4">
                    <button 
                        type="button"
                        @click="tab = 'details'; $wire.changeTab('details')" 
                        class="px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150"
                        :class="tab === 'details' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'">
                        <i class="fas fa-info-circle mr-1"></i>
                        {{ __('messages.order_details') }}
                    </button>
                    <button 
                        type="button"
                        @click="tab = 'shipping'; $wire.changeTab('shipping')" 
                        class="px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150"
                        :class="tab === 'shipping' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'">
                        <i class="fas fa-ship mr-1"></i>
                        {{ __('messages.shipping_tracking') }}
                    </button>
                </div>
            </div>
            
            <!-- Conteúdo da Modal -->
            <div class="p-6 max-h-[70vh] overflow-y-auto">
                @if($viewingOrder)
                
                    <!-- Aba de Detalhes da Ordem -->
                    <div x-show="tab === 'details'" 
                         x-transition:enter="transition ease-out duration-300" 
                         x-transition:enter-start="opacity-0 transform scale-95" 
                         x-transition:enter-end="opacity-100 transform scale-100" 
                         x-transition:leave="transition ease-in duration-200" 
                         x-transition:leave-start="opacity-100 transform scale-100" 
                         x-transition:leave-end="opacity-0 transform scale-95">
                        <!-- Informações Gerais -->
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden mb-6">
                            <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                <h3 class="text-base font-medium text-gray-700">{{ __('messages.general_information') }}</h3>
                            </div>
                            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs font-medium text-gray-500">{{ __('messages.order_number') }}</p>
                                    <p class="text-sm font-medium text-gray-800">{{ $viewingOrder->order_number }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500">{{ __('messages.supplier') }}</p>
                                    <p class="text-sm font-medium text-gray-800">{{ $viewingOrder->supplier->name }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500">{{ __('messages.order_date') }}</p>
                                    <p class="text-sm font-medium text-gray-800">{{ date('d/m/Y', strtotime($viewingOrder->order_date)) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500">{{ __('messages.expected_delivery') }}</p>
                                    <p class="text-sm font-medium 
                                        {{ $viewingOrder->is_overdue ? 'text-red-600' : 
                                          (strtotime($viewingOrder->expected_delivery_date) <= strtotime('+15 days') && 
                                           strtotime($viewingOrder->expected_delivery_date) >= strtotime('now') ? 
                                           'text-amber-600' : 'text-gray-800') }}">
                                        {{ $viewingOrder->expected_delivery_date ? date('d/m/Y', strtotime($viewingOrder->expected_delivery_date)) : '-' }}
                                        @if($viewingOrder->is_overdue)
                                            <i class="fas fa-exclamation-circle ml-1 text-red-500 animate-pulse" 
                                               title="{{ __('messages.overdue_order') }}"></i>
                                        @elseif(strtotime($viewingOrder->expected_delivery_date) <= strtotime('+15 days') && 
                                               strtotime($viewingOrder->expected_delivery_date) >= strtotime('now'))
                                            <i class="fas fa-exclamation-triangle ml-1 text-amber-500" 
                                               title="{{ __('messages.delivery_approaching') }}" 
                                               x-data="{}" 
                                               x-tooltip.raw="{{ __('messages.delivery_within_15_days') }}"></i>
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500">{{ __('messages.status') }}</p>
                                    <p class="text-sm">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
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
                                                <i class="fas fa-dolly mr-1"></i>
                                            @elseif($viewingOrder->status == 'completed')
                                                <i class="fas fa-check-circle mr-1"></i>
                                            @elseif($viewingOrder->status == 'cancelled')
                                                <i class="fas fa-ban mr-1"></i>
                                            @endif
                                            {{ __('messages.' . $viewingOrder->status) }}
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500">{{ __('messages.created_by') }}</p>
                                    <p class="text-sm font-medium text-gray-800">{{ $viewingOrder->createdBy->name ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Itens da Ordem -->
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                            <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-list text-blue-600 mr-2"></i>
                                <h3 class="text-base font-medium text-gray-700">{{ __('messages.order_items') }}</h3>
                            </div>
                            <div class="p-4">
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
                                                    {{ __('messages.unit_price') }}
                                                </th>
                                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    {{ __('messages.total') }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @forelse($viewingOrder->items as $item)
                                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                                    <td class="px-4 py-3 whitespace-nowrap">
                                                        <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                                        <div class="text-xs text-gray-500">{{ $item->product->product_code }}</div>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <div class="text-sm text-gray-900">{{ $item->description }}</div>
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-right">
                                                        <div class="text-sm text-gray-900">{{ number_format($item->quantity, 0) }}</div>
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-right">
                                                        <div class="text-sm text-gray-900">{{ number_format($item->unit_price, 2) }}</div>
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-right">
                                                        <div class="text-sm font-medium text-gray-900">{{ number_format($item->quantity * $item->unit_price, 2) }}</div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="px-4 py-3 text-center text-sm text-gray-500">
                                                        {{ __('messages.no_items_found') }}
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        <tfoot class="bg-gray-50">
                                            <tr>
                                                <td colspan="4" class="px-4 py-3 text-right font-medium">
                                                    {{ __('messages.order_total') }}:
                                                </td>
                                                <td class="px-4 py-3 text-right font-bold text-gray-900">
                                                    {{ number_format($viewingOrder->items->sum(function($item) { return $item->quantity * $item->unit_price; }), 2) }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Aba de Rastreamento de Envio -->
                    <div x-show="tab === 'shipping'"
                         x-transition:enter="transition ease-out duration-300" 
                         x-transition:enter-start="opacity-0 transform scale-95" 
                         x-transition:enter-end="opacity-100 transform scale-100" 
                         x-transition:leave="transition ease-in duration-200" 
                         x-transition:leave-start="opacity-100 transform scale-100" 
                         x-transition:leave-end="opacity-0 transform scale-95">
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                            <div class="bg-gradient-to-r from-amber-600 to-amber-700 px-4 py-3">
                                <h2 class="text-lg font-medium text-white flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-shipping-fast mr-2"></i>
                                        {{ __('messages.shipping_tracking') }}
                                    </div>
                                    @if($viewingOrder->expected_delivery_date)
                                    <span class="text-xs bg-white bg-opacity-20 text-white px-2 py-1 rounded-full border border-white border-opacity-30 flex items-center">
                                        <i class="fas fa-calendar-alt mr-1"></i>
                                        {{ __('messages.expected_delivery') }}: {{ date('d/m/Y', strtotime($viewingOrder->expected_delivery_date)) }}
                                    </span>
                                    @endif
                                </h2>
                            </div>
                            
                            <div class="p-4">
                                <!-- Dados de Referência -->
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 mb-4">
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
                                
                                <!-- Status do Envio com Barra de Progresso e Timeline visual -->
                                <div class="mb-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    @php
                                        $shippingProgress = 0;
                                        $latestShippingNote = null;
                                        $lastUpdateDate = null;
                                        
                                        // Obter todas as shipping notes desta ordem
                                        $shippingNotes = \App\Models\SupplyChain\ShippingNote::where('purchase_order_id', $viewingOrder->id)
                                            ->orderBy('created_at', 'desc')
                                            ->get();
                                            
                                        if ($shippingNotes->count() > 0) {
                                            $latestShippingNote = $shippingNotes->first();
                                            $lastUpdateDate = $latestShippingNote->created_at;
                                            
                                            // Calcular progresso com base no status mais recente
                                            $progressMap = [
                                                'order_placed' => 10,
                                                'proforma_invoice_received' => 20,
                                                'payment_completed' => 30,
                                                'du_in_process' => 40,
                                                'goods_acquired' => 50,
                                                'shipped_to_port' => 60,
                                                'shipping_line_booking_confirmed' => 70,
                                                'container_loaded' => 75,
                                                'on_board' => 80,
                                                'arrived_at_port' => 85,
                                                'customs_clearance' => 90,
                                                'delivered' => 100
                                            ];
                                            
                                            $shippingProgress = $progressMap[$latestShippingNote->status] ?? 10;
                                            
                                            // Mapear os status para títulos mais amigáveis (já traduzidos)
                                            $statusTitles = [
                                                'order_placed' => __('messages.shipping_status_order_placed'),
                                                'proforma_invoice_received' => __('messages.shipping_status_proforma_invoice_received'),
                                                'payment_completed' => __('messages.shipping_status_payment_completed'),
                                                'du_in_process' => __('messages.shipping_status_du_in_process'),
                                                'goods_acquired' => __('messages.shipping_status_goods_acquired'),
                                                'shipped_to_port' => __('messages.shipping_status_shipped_to_port'),
                                                'shipping_line_booking_confirmed' => __('messages.shipping_status_shipping_line_booking_confirmed'),
                                                'container_loaded' => __('messages.shipping_status_container_loaded'),
                                                'on_board' => __('messages.shipping_status_on_board'),
                                                'arrived_at_port' => __('messages.shipping_status_arrived_at_port'),
                                                'customs_clearance' => __('messages.shipping_status_customs_clearance'),
                                                'delivered' => __('messages.shipping_status_delivered')
                                            ];
                                        }
                                    @endphp
                                    
                                    <!-- Status atual e última atualização -->
                                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-3">
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-500">{{ __('messages.current_status') }}</h4>
                                            @if($latestShippingNote)
                                                <p class="text-lg font-semibold text-gray-800">
                                                    {{ __('messages.shipping_status_'.$latestShippingNote->status) }}
                                                </p>
                                            @else
                                                <p class="text-lg font-semibold text-gray-600">{{ __('messages.no_shipping_info') }}</p>
                                            @endif
                                        </div>
                                        
                                        @if($lastUpdateDate)
                                        <div class="mt-2 sm:mt-0 bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-xs flex items-center">
                                            <i class="far fa-clock mr-1"></i>
                                            {{ __('messages.last_update') }}: {{ $lastUpdateDate->format('d/m/Y H:i') }}
                                        </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Barra de progresso principal -->
                                    <div class="mb-4">
                                        <div class="flex items-center justify-between mb-1">
                                            <p class="text-sm font-medium text-gray-700">{{ __('messages.shipping_progress') }}</p>
                                            <div class="flex items-center">
                                                <p class="text-sm font-medium mr-2 {{ $shippingProgress == 100 ? 'text-green-700' : 'text-amber-700' }}">{{ $shippingProgress }}%</p>
                                                @if($shippingProgress == 100)
                                                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-0.5 rounded-full">
                                                        <i class="fas fa-check-circle mr-1"></i>
                                                        {{ __('messages.completed') }}
                                                    </span>
                                                @elseif($shippingProgress > 0)
                                                    <span class="bg-amber-100 text-amber-800 text-xs font-medium px-2 py-0.5 rounded-full">
                                                        <i class="fas fa-shipping-fast mr-1"></i>
                                                        {{ __('messages.in_transit') }}
                                                    </span>
                                                @else
                                                    <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2 py-0.5 rounded-full">
                                                        <i class="fas fa-hourglass-start mr-1"></i>
                                                        {{ __('messages.waiting') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                            <div class="h-3 rounded-full {{ $shippingProgress == 100 ? 'bg-green-600' : ($shippingProgress > 50 ? 'bg-amber-600' : 'bg-amber-500') }}" 
                                                style="width: {{ $shippingProgress }}%; transition: width 1s ease-in-out;"></div>
                                        </div>
                                    </div>
                                    
                                    <!-- Detalhes sobre a etapa atual (apenas se houver shipping notes) -->
                                    @if($latestShippingNote)
                                    <div class="mt-3 p-3 border-t border-gray-200 pt-3">
                                        <div class="flex items-center mb-1 gap-2">
                                            <div class="w-8 h-8 rounded-full {{ $shippingProgress == 100 ? 'bg-green-100' : 'bg-amber-100' }} flex items-center justify-center">
                                                <i class="{{ $shippingProgress == 100 ? 'fas fa-check text-green-600' : 'fas fa-truck-loading text-amber-600' }}"></i>
                                            </div>
                                            <h5 class="text-sm font-medium text-gray-700">{{ __('messages.shipping_details') }}</h5>
                                        </div>
                                        <div class="ml-10 text-sm text-gray-600">
                                            <p>{{ $latestShippingNote->note }}</p>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                
                                <!-- Lista de Atualizações de Envio - Timeline -->
                                <div class="mb-4">
                                    <h3 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                                        <i class="fas fa-history mr-2 text-gray-500"></i>
                                        {{ __('messages.shipping_history') }}
                                    </h3>

                                    <!-- Timeline vertical com atualizações de envio -->
                                    <div class="relative mt-3">
                                        <!-- Linha vertical da timeline -->
                                        <div class="absolute left-5 top-0 h-full w-0.5 bg-gradient-to-b from-amber-500 to-gray-300"></div>
                                        
                                        <div class="space-y-5">
                                            @forelse($shippingNotes as $index => $note)
                                            <!-- Item da timeline -->
                                            <div class="relative flex items-start group">
                                                <!-- Círculo do marco na timeline -->
                                                <div class="absolute left-5 transform -translate-x-1/2">
                                                    <div class="w-10 h-10 rounded-full 
                                                        @if($note->status == 'order_placed') bg-gray-100
                                                        @elseif($note->status == 'proforma_invoice_received') bg-purple-100
                                                        @elseif($note->status == 'payment_completed') bg-blue-100
                                                        @elseif($note->status == 'du_in_process') bg-indigo-100
                                                        @elseif($note->status == 'goods_acquired') bg-teal-100
                                                        @elseif($note->status == 'shipped_to_port') bg-amber-100
                                                        @elseif($note->status == 'shipping_line_booking_confirmed') bg-yellow-100
                                                        @elseif($note->status == 'container_loaded') bg-orange-100
                                                        @elseif($note->status == 'on_board') bg-red-100
                                                        @elseif($note->status == 'arrived_at_port') bg-rose-100
                                                        @elseif($note->status == 'customs_clearance') bg-pink-100
                                                        @elseif($note->status == 'delivered') bg-green-100
                                                        @endif 
                                                        border-2 {{ $index === 0 ? 'border-amber-500 shadow-md' : 'border-gray-300' }}
                                                        flex items-center justify-center transition-all duration-200 ease-in-out">
                                                        <i class="
                                                            @if($note->status == 'order_placed') fas fa-shopping-cart text-gray-600
                                                            @elseif($note->status == 'proforma_invoice_received') fas fa-file-invoice text-purple-600
                                                            @elseif($note->status == 'payment_completed') fas fa-money-check-alt text-blue-600
                                                            @elseif($note->status == 'du_in_process') fas fa-file-contract text-indigo-600
                                                            @elseif($note->status == 'goods_acquired') fas fa-box-open text-teal-600
                                                            @elseif($note->status == 'shipped_to_port') fas fa-truck-loading text-amber-600
                                                            @elseif($note->status == 'shipping_line_booking_confirmed') fas fa-calendar-check text-yellow-600
                                                            @elseif($note->status == 'container_loaded') fas fa-truck-moving text-orange-600
                                                            @elseif($note->status == 'on_board') fas fa-ship text-red-600
                                                            @elseif($note->status == 'arrived_at_port') fas fa-anchor text-rose-600
                                                            @elseif($note->status == 'customs_clearance') fas fa-clipboard-check text-pink-600
                                                            @elseif($note->status == 'delivered') fas fa-check-circle text-green-600
                                                            @endif"></i>
                                                    </div>
                                                </div>
                                                
                                                <!-- Conteúdo da atualização -->
                                                <div class="ml-12 bg-white p-3 rounded-lg border {{ $index === 0 ? 'border-amber-200 shadow' : 'border-gray-200' }} 
                                                         hover:border-amber-300 hover:shadow transition-all duration-200 ease-in-out w-full">
                                                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start">
                                                        <h4 class="text-sm font-semibold text-gray-900 mb-1">
                                                            {{ __('messages.shipping_status_'.$note->status) }}
                                                        </h4>
                                                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full inline-flex items-center mb-2 sm:mb-0">
                                                            <i class="far fa-clock mr-1"></i>
                                                            {{ $note->created_at->format('d/m/Y H:i') }}
                                                        </span>
                                                    </div>
                                                    
                                                    <!-- Detalhes da nota -->
                                                    <div class="text-sm text-gray-700 mt-1 border-t pt-2 border-gray-100">
                                                        <p class="whitespace-pre-line">{{ $note->note }}</p>
                                                        
                                                        @if(isset($note->reference_number) && $note->reference_number)
                                                        <div class="mt-2 text-xs bg-gray-50 rounded px-2 py-1 flex items-center">
                                                            <i class="fas fa-hashtag mr-1 text-gray-500"></i>
                                                            <span>{{ __('messages.reference_number') }}: <span class="font-medium">{{ $note->reference_number }}</span></span>
                                                        </div>
                                                        @endif
                                                        
                                                        @if(isset($note->tracking_number) && $note->tracking_number)
                                                        <div class="mt-1 text-xs bg-blue-50 rounded px-2 py-1 flex items-center">
                                                            <i class="fas fa-barcode mr-1 text-blue-500"></i>
                                                            <span>{{ __('messages.tracking_number') }}: <span class="font-medium text-blue-700">{{ $note->tracking_number }}</span></span>
                                                        </div>
                                                        @endif
                                                    </div>
                                                    
                                                    <!-- Dados de Formulário Personalizado -->
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
                                                        <div class="mt-3 mb-3 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-md border border-blue-100 p-3 relative overflow-hidden">
                                                            <!-- Indicação visual de formulário -->
                                                            <div class="absolute top-0 right-0 w-16 h-16 opacity-10">
                                                                <i class="fas fa-clipboard-list text-5xl text-blue-500"></i>
                                                            </div>
                                                            
                                                            <!-- Título do formulário -->
                                                            <div class="flex items-center mb-3">
                                                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-1 rounded-full border border-blue-200 mr-2">
                                                                    <i class="fas fa-file-alt mr-1"></i>
                                                                    {{ __('messages.custom_form') }}
                                                                </span>
                                                                <h5 class="text-sm font-semibold text-blue-800">{{ $customForm->name }}</h5>
                                                            </div>
                                                            
                                                            <!-- Campos do formulário -->
                                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                                @forelse($submission->fieldValues as $fieldValue)
                                                                    @if($fieldValue->field)
                                                                        <div class="bg-white bg-opacity-60 p-2 rounded border border-blue-100 shadow-sm">
                                                                            <p class="text-xs text-gray-500 font-medium">{{ $fieldValue->field->label }}</p>
                                                                            <div class="text-sm text-gray-800">
                                                                                @if($fieldValue->field->type == 'checkbox')
                                                                                    <span class="inline-flex items-center {{ $fieldValue->value ? 'text-green-600' : 'text-red-600' }}">
                                                                                        <i class="fas {{ $fieldValue->value ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                                                                                        {{ $fieldValue->value ? __('messages.yes') : __('messages.no') }}
                                                                                    </span>
                                                                                @elseif($fieldValue->field->type == 'date' && !empty($fieldValue->value))
                                                                                    <span class="inline-flex items-center">
                                                                                        <i class="far fa-calendar-alt mr-1 text-blue-500"></i>
                                                                                        {{ \Carbon\Carbon::parse($fieldValue->value)->format('d/m/Y') }}
                                                                                    </span>
                                                                                @elseif($fieldValue->field->type == 'select')
                                                                                    @php
                                                                                        $displayValue = $fieldValue->value;
                                                                                        $options = $fieldValue->field->options;
                                                                                        
                                                                                        // Verifica se options é string (JSON) ou já é um array
                                                                                        if (is_string($options)) {
                                                                                            $options = json_decode($options, true) ?? [];
                                                                                        }
                                                                                        
                                                                                        if (is_array($options)) {
                                                                                            foreach ($options as $option) {
                                                                                        <div class="inline-flex items-center max-w-xs bg-gray-100 text-gray-800 text-xs rounded-full px-3 py-1 border border-gray-200">
                                                                                            <i class="fas fa-file mr-1 text-blue-500"></i>
                                                                                            <span class="truncate">
                                                                                                {{ basename($fieldValue->value) }}
                                                                                            </span>
                                                                                        </div>
                                                                                        <a href="{{ asset('storage/'.$fieldValue->value) }}" target="_blank" download 
                                                                                            class="text-blue-600 hover:text-blue-800 inline-flex items-center bg-white shadow-sm px-2 py-1 rounded-md text-xs border border-blue-100 transition-all hover:shadow">
                                                                                            <i class="fas fa-download mr-1"></i>
                                                                                            {{ __('messages.download') }}
                                                                                        </a>
                                                                                        <a href="{{ asset('storage/'.$fieldValue->value) }}" target="_blank" 
                                                                                            class="text-gray-600 hover:text-gray-800 inline-flex items-center bg-white shadow-sm px-2 py-1 rounded-md text-xs border border-gray-100 transition-all hover:shadow">
                                                                                            <i class="fas fa-external-link-alt mr-1"></i>
                                                                                            {{ __('messages.view') }}
                                                                                        </a>
                                                                                    </div>
                                                                                @else
                                                                                    <span>{{ $fieldValue->value }}</span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                @empty
                                                                    <div class="col-span-2 text-center p-3 bg-white bg-opacity-60 rounded">
                                                                        <p class="text-sm text-gray-500 italic">
                                                                            <i class="fas fa-info-circle mr-1"></i>
                                                                            {{ __('messages.no_data_available') }}
                                                                        </p>
                                                                    </div>
                                                                @endforelse
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif
                                                
                                                <div class="mt-2 flex items-center flex-wrap gap-2">
                                                    @if($note->updated_by)
                                                        @php
                                                            $author = \App\Models\User::find($note->updated_by);
                                                        @endphp
                                                        <p class="text-xs text-gray-500">
                                                            <i class="fas fa-user text-gray-400 mr-1"></i>
                                                            {{ $author ? $author->name : 'Usuário '.$note->updated_by }}
                                                        </p>
                                                    @endif
                                                    
                                                    @if($note->attachment_url)
                                                        <a href="{{ asset('storage/'.$note->attachment_url) }}" target="_blank" 
                                                           class="text-xs text-blue-600 hover:text-blue-800 flex items-center"
                                                           title="{{ basename($note->attachment_url) }}">
                                                            <i class="fas fa-paperclip mr-1"></i>
                                                            {{ Str::limit(basename($note->attachment_url), 20) }}
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                        <div class="text-center py-6 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                                            <i class="fas fa-shipping-fast text-gray-400 text-3xl mb-3"></i>
                                            <p class="text-gray-500">{{ __('messages.no_shipping_updates') }}</p>
                                            <p class="text-xs text-gray-400 mt-1">{{ __('messages.shipping_updates_appear_here') }}</p>
                                        </div>
                                    @endforelse
                                </div>
                                
                                <!-- Adicionar Nova Nota de Envio -->
                                <div class="mt-6 pt-6 border-t border-gray-200">
                                    <button wire:click="showShippingNotesModal" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                                        <i class="fas fa-plus-circle mr-2"></i>
                                        {{ __('messages.add_shipping_update') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                @endif
            </div>
            
            <!-- Rodapé com botões de ação -->
            <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                @if($viewingOrder && in_array($viewingOrder->status, ['draft', 'pending_approval']))
                    <button type="button" wire:click="editOrder({{ $viewingOrder->id }})" 
                        class="inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <i class="fas fa-edit mr-2"></i>
                        {{ __('messages.edit') }}
                    </button>
                @endif
                
                <button type="button" wire:click="generatePdf({{ $viewingOrder ? $viewingOrder->id : null }})" 
                    class="inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                    <i class="fas fa-file-pdf mr-2"></i>
                    {{ __('messages.generate_pdf') }}
                </button>
                
                <button type="button" wire:click="closeViewModal" 
                    class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                    <i class="fas fa-times mr-2"></i>
                    {{ __('messages.close') }}
                </button>
            </div>
        </div>
    </div>
</div>