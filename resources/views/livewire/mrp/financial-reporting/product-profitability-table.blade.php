<div class="overflow-x-auto bg-white shadow-md rounded-lg">
    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr>
                <th class="px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-left text-xs font-medium text-white uppercase tracking-wider">
                    {{ __('messages.product') }}
                </th>
                <th class="px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-left text-xs font-medium text-white uppercase tracking-wider">
                    {{ __('messages.sku') }}
                </th>
                <th class="px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-left text-xs font-medium text-white uppercase tracking-wider">
                    {{ __('messages.order_number') }}
                </th>
                <th class="px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-right text-xs font-medium text-white uppercase tracking-wider">
                    {{ __('messages.quantity') }}
                </th>
                <th class="px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-right text-xs font-medium text-white uppercase tracking-wider">
                    {{ __('messages.unit_cost') }}
                </th>
                <th class="px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-right text-xs font-medium text-white uppercase tracking-wider">
                    {{ __('messages.total_cost') }}
                </th>
                <th class="px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-right text-xs font-medium text-white uppercase tracking-wider">
                    {{ __('messages.unit_price') }}
                </th>
                <th class="px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-right text-xs font-medium text-white uppercase tracking-wider">
                    {{ __('messages.total_sales') }}
                </th>
                <th class="px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-right text-xs font-medium text-white uppercase tracking-wider">
                    {{ __('messages.profit') }}
                </th>
                <th class="px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-center text-xs font-medium text-white uppercase tracking-wider">
                    {{ __('messages.profit_margin') }}
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($reportData as $index => $row)
                <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-purple-50 transition-colors duration-200">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $row['product_name'] }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $row['product_sku'] }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $row['order_number'] }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                        {{ number_format($row['quantity'], 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                        {{ number_format($row['unit_cost'], 2) }} {{ __('messages.currency') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                        {{ number_format($row['total_cost'], 2) }} {{ __('messages.currency') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                        {{ number_format($row['unit_price'], 2) }} {{ __('messages.currency') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                        {{ number_format($row['total_sales'], 2) }} {{ __('messages.currency') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right
                        {{ $row['profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $row['profit'] >= 0 ? '+' : '' }}{{ number_format($row['profit'], 2) }} {{ __('messages.currency') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $row['profit_margin'] >= 20 ? 'bg-green-100 text-green-800' : 
                              ($row['profit_margin'] >= 10 ? 'bg-blue-100 text-blue-800' : 
                              ($row['profit_margin'] >= 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) }}">
                            {{ $row['profit_margin'] > 0 ? '+' : '' }}{{ number_format($row['profit_margin'], 1) }}%
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        {{ __('messages.no_data_available') }}
                    </td>
                </tr>
            @endforelse
            
            <!-- Rodapé da tabela com totais -->
            @if(count($reportData) > 0)
                <tr class="bg-gray-100 font-medium">
                    <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ __('messages.total') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                        {{ number_format(collect($reportData)->sum('quantity'), 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                        {{ number_format(collect($reportData)->avg('unit_cost'), 2) }} {{ __('messages.currency') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                        {{ number_format(collect($reportData)->sum('total_cost'), 2) }} {{ __('messages.currency') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                        {{ number_format(collect($reportData)->avg('unit_price'), 2) }} {{ __('messages.currency') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                        {{ number_format(collect($reportData)->sum('total_sales'), 2) }} {{ __('messages.currency') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-right
                        {{ collect($reportData)->sum('profit') >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ collect($reportData)->sum('profit') >= 0 ? '+' : '' }}{{ number_format(collect($reportData)->sum('profit'), 2) }} {{ __('messages.currency') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        @php
                            $totalSales = collect($reportData)->sum('total_sales');
                            $totalProfit = collect($reportData)->sum('profit');
                            $avgMargin = $totalSales > 0 ? ($totalProfit / $totalSales) * 100 : 0;
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $avgMargin >= 20 ? 'bg-green-100 text-green-800' : 
                              ($avgMargin >= 10 ? 'bg-blue-100 text-blue-800' : 
                              ($avgMargin >= 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) }}">
                            {{ $avgMargin > 0 ? '+' : '' }}{{ number_format($avgMargin, 1) }}%
                        </span>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
    
    <!-- Análise de Lucratividade -->
    @if(count($reportData) > 0)
        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Card - Margem Média -->
            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                <div class="text-sm text-gray-500 mb-1">{{ __('messages.average_margin') }}</div>
                <div class="flex items-center justify-between">
                    @php
                        $totalSales = collect($reportData)->sum('total_sales');
                        $totalProfit = collect($reportData)->sum('profit');
                        $avgMargin = $totalSales > 0 ? ($totalProfit / $totalSales) * 100 : 0;
                    @endphp
                    <div class="text-xl font-bold {{ $avgMargin >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($avgMargin, 1) }}%
                    </div>
                    <div class="p-2 rounded-full bg-{{ $avgMargin >= 20 ? 'green' : ($avgMargin >= 10 ? 'blue' : ($avgMargin >= 0 ? 'yellow' : 'red')) }}-100">
                        <i class="fas fa-percentage text-{{ $avgMargin >= 20 ? 'green' : ($avgMargin >= 10 ? 'blue' : ($avgMargin >= 0 ? 'yellow' : 'red')) }}-600"></i>
                    </div>
                </div>
            </div>
            
            <!-- Card - Produto Mais Lucrativo -->
            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                <div class="text-sm text-gray-500 mb-1">{{ __('messages.most_profitable_product') }}</div>
                @php
                    $mostProfitable = collect($reportData)->sortByDesc('profit_margin')->first();
                @endphp
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-medium text-gray-800">{{ $mostProfitable['product_name'] }}</div>
                        <div class="text-sm text-gray-500">{{ $mostProfitable['product_sku'] }}</div>
                    </div>
                    <div class="text-lg font-bold text-green-600">
                        {{ number_format($mostProfitable['profit_margin'], 1) }}%
                    </div>
                </div>
            </div>
            
            <!-- Card - Produto Menos Lucrativo -->
            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                <div class="text-sm text-gray-500 mb-1">{{ __('messages.least_profitable_product') }}</div>
                @php
                    $leastProfitable = collect($reportData)->sortBy('profit_margin')->first();
                @endphp
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-medium text-gray-800">{{ $leastProfitable['product_name'] }}</div>
                        <div class="text-sm text-gray-500">{{ $leastProfitable['product_sku'] }}</div>
                    </div>
                    <div class="text-lg font-bold {{ $leastProfitable['profit_margin'] >= 0 ? 'text-yellow-600' : 'text-red-600' }}">
                        {{ number_format($leastProfitable['profit_margin'], 1) }}%
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
