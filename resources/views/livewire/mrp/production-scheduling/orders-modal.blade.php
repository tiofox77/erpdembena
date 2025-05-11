<!-- Modal para Visualizar Ordens de Produção Relacionadas -->
<div x-cloak
    class="fixed inset-0 z-30 flex items-center justify-center overflow-auto bg-gray-800 bg-opacity-75 transition-opacity"
    x-show="$wire.showOrdersModal"
    @keydown.escape.window="$wire.closeOrdersModal()">
    <div class="relative w-full max-w-4xl mx-auto my-8 px-4 sm:px-0"
        x-show="$wire.showOrdersModal"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        @click.away="$wire.closeOrdersModal()">
        <div class="relative bg-white rounded-lg shadow-xl overflow-hidden" @click.stop>
            <!-- Cabeçalho do Modal -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-4 sm:px-6 flex justify-between items-center">
                <h3 class="text-lg font-medium text-white">
                    <i class="fas fa-clipboard-list mr-2"></i>
                    {{ __('messages.production_orders') }}
                    @if($selectedSchedule)
                        - {{ $selectedSchedule->schedule_number }}
                    @endif
                </h3>
                <button @click="$wire.closeOrdersModal()" class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            @if($selectedSchedule)
                <div class="p-6">
                    <!-- Informações da Programação -->
                    <div class="mb-6 bg-blue-50 rounded-lg p-4 border border-blue-100">
                        <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                            {{ __('messages.schedule_information') }}
                        </h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('messages.product') }}:</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $selectedSchedule->product->name ?? 'N/A' }}
                                    @if(isset($selectedSchedule->product) && isset($selectedSchedule->product->sku))
                                        <span class="text-xs text-gray-500">({{ $selectedSchedule->product->sku }})</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('messages.schedule_number') }}:</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $selectedSchedule->schedule_number }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('messages.planned_quantity') }}:</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ number_format($selectedSchedule->planned_quantity, 2) }}</dd>
                            </div>
                        </div>
                    </div>

                    <!-- Seção de Criação de Ordem -->
                    <div class="mb-6 bg-white border border-gray-200 rounded-lg shadow-sm p-4">
                        <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-plus-circle text-green-600 mr-2"></i>
                            {{ __('messages.create_new_order') }}
                        </h4>

                        <form wire:submit.prevent="createOrder" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Quantidade -->
                                <div>
                                    <label for="orderQuantity" class="block text-sm font-medium text-gray-700">{{ __('messages.quantity') }} *</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <input type="number" step="0.01" min="0.01" id="orderQuantity" wire:model="newOrder.quantity"
                                            class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                            placeholder="0.00" required>
                                    </div>
                                </div>

                                <!-- Data de Entrega -->
                                <div>
                                    <label for="orderDueDate" class="block text-sm font-medium text-gray-700">{{ __('messages.due_date') }} *</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <input type="date" id="orderDueDate" wire:model="newOrder.due_date"
                                            class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                            required>
                                    </div>
                                </div>
                            </div>

                            <!-- Descrição -->
                            <div>
                                <label for="orderDescription" class="block text-sm font-medium text-gray-700">{{ __('messages.description') }}</label>
                                <div class="mt-1">
                                    <textarea id="orderDescription" wire:model="newOrder.description" rows="2"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                        placeholder="{{ __('messages.order_description_placeholder') }}"></textarea>
                                </div>
                            </div>

                            <!-- Botão de Submissão -->
                            <div class="flex justify-end">
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="fas fa-plus-circle mr-2"></i>
                                    {{ __('messages.add_order') }}
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Tabela de Ordens -->
                    <div class="mb-4">
                        <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-clipboard-list text-blue-600 mr-2"></i>
                            {{ __('messages.related_orders') }}
                        </h4>
                        
                        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('messages.order_number') }}
                                            </th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('messages.product') }}
                                            </th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('messages.quantity') }}
                                            </th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('messages.status') }}
                                            </th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('messages.created_at') }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @if(isset($relatedOrders) && count($relatedOrders) > 0)
                                            @foreach($relatedOrders as $order)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $order->order_number }}
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $order->product->name ?? 'N/A' }}
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                                    {{ number_format($order->quantity, 2) }}
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                                        @if($order->status === 'completed') bg-green-100 text-green-800
                                                        @elseif($order->status === 'pending') bg-yellow-100 text-yellow-800
                                                        @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                                                        @elseif($order->status === 'in_progress') bg-blue-100 text-blue-800
                                                        @else bg-gray-100 text-gray-800
                                                        @endif">
                                                        <i class="mr-1 fas 
                                                            @if($order->status === 'completed') fa-check-circle
                                                            @elseif($order->status === 'pending') fa-clock
                                                            @elseif($order->status === 'cancelled') fa-ban
                                                            @elseif($order->status === 'in_progress') fa-spinner fa-spin
                                                            @else fa-question-circle
                                                            @endif"></i>
                                                        {{ __('messages.status_' . $order->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $order->created_at ? $order->created_at->format('d/m/Y H:i') : 'N/A' }}
                                                </td>
                                            </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="5" class="px-4 py-6 text-center">
                                                    <div class="flex flex-col items-center justify-center py-5">
                                                        <div class="h-12 w-12 text-gray-400 mb-3 rounded-full border border-gray-200 flex items-center justify-center">
                                                            <i class="fas fa-folder-open text-xl"></i>
                                                        </div>
                                                        <p class="text-gray-500 text-sm">
                                                            {{ __('messages.no_orders_for_this_schedule') }}
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="p-6 flex justify-center items-center">
                    <div class="flex flex-col items-center space-y-2">
                        <div class="flex-shrink-0 bg-gray-100 p-3 rounded-full">
                            <i class="fas fa-exclamation-circle text-gray-400 text-2xl"></i>
                        </div>
                        <p class="text-gray-500">{{ __('messages.no_schedule_selected') }}</p>
                    </div>
                </div>
            @endif
            
            <!-- Rodapé do Modal -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end">
                <button type="button" wire:click="closeOrdersModal" 
                    class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out">
                    <i class="fas fa-times mr-2"></i>
                    {{ __('messages.close') }}
                </button>
            </div>
        </div>
    </div>
</div>