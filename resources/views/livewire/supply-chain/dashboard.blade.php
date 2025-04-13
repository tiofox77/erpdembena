<div>
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-2xl font-semibold mb-6">{{ __('Supply Chain Dashboard') }}</h1>
        
        <!-- Metrics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            @foreach($metrics as $metric)
            <div class="bg-white rounded-lg shadow p-6 border-l-4 {{ $metric['color'] }}">
                <div class="flex items-center">
                    <div class="mr-4">
                        <i class="{{ $metric['icon'] }} text-2xl {{ str_replace('bg-', 'text-', $metric['color']) }}"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">{{ $metric['title'] }}</p>
                        <p class="text-xl font-semibold">{{ $metric['value'] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Low Stock Items -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4 flex items-center">
                    <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                    {{ __('Low Stock Alerts') }}
                </h2>
                
                @if(count($lowStockItems) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Product') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('SKU') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Current Stock') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Reorder Point') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($lowStockItems as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $item['name'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $item['sku'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $item['current_stock'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $item['reorder_point'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $item['status'] == 'critical' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $item['status'] == 'critical' ? __('Critical') : __('Warning') }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-gray-500">{{ __('No low stock items found.') }}</p>
                @endif
                
                <div class="mt-4">
                    <a href="{{ route('supply-chain.inventory') }}" class="text-blue-600 hover:underline flex items-center">
                        <span>{{ __('View All Inventory') }}</span>
                        <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
            
            <!-- Pending Orders -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4 flex items-center">
                    <i class="fas fa-clock text-blue-500 mr-2"></i>
                    {{ __('Pending Purchase Orders') }}
                </h2>
                
                @if(count($pendingOrders) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Order #') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Supplier') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Expected Delivery') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($pendingOrders as $order)
                            <tr class="{{ $order['is_overdue'] ? 'bg-red-50' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap">{{ $order['order_number'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $order['supplier'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($order['is_overdue'])
                                    <span class="text-red-600 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        {{ $order['expected_delivery'] }}
                                    </span>
                                    @else
                                    {{ $order['expected_delivery'] }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $order['status'] == 'approved' ? 'bg-blue-100 text-blue-800' : 
                                           ($order['status'] == 'ordered' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800') }}">
                                        {{ __(ucfirst($order['status'])) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ number_format($order['total'], 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-gray-500">{{ __('No pending purchase orders found.') }}</p>
                @endif
                
                <div class="mt-4">
                    <a href="{{ route('supply-chain.purchase-orders') }}" class="text-blue-600 hover:underline flex items-center">
                        <span>{{ __('View All Purchase Orders') }}</span>
                        <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Recent Transactions -->
        <div class="mt-8 bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4 flex items-center">
                <i class="fas fa-exchange-alt text-green-500 mr-2"></i>
                {{ __('Recent Inventory Transactions') }}
            </h2>
            
            @if(count($recentTransactions) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Date') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Product') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Type') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Quantity') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Source') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Destination') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentTransactions as $transaction)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $transaction['date'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $transaction['product'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $transaction['type'] == 'purchase_receipt' ? 'bg-green-100 text-green-800' : 
                                      ($transaction['type'] == 'transfer' ? 'bg-blue-100 text-blue-800' : 
                                      ($transaction['type'] == 'adjustment' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) }}">
                                    {{ __(str_replace('_', ' ', ucfirst($transaction['type']))) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $transaction['quantity'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $transaction['source'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $transaction['destination'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-gray-500">{{ __('No recent transactions found.') }}</p>
            @endif
            
            <div class="mt-4">
                <a href="{{ route('supply-chain.inventory') }}" class="text-blue-600 hover:underline flex items-center">
                    <span>{{ __('View All Transactions') }}</span>
                    <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>
