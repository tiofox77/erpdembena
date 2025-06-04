<div class="overflow-x-auto bg-white shadow-md rounded-lg">
    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr>
                <th class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-left text-xs font-medium text-white uppercase tracking-wider">
                    {{ __('messages.product') }}
                </th>
                <th class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-left text-xs font-medium text-white uppercase tracking-wider">
                    {{ __('messages.sku') }}
                </th>
                <th class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-right text-xs font-medium text-white uppercase tracking-wider">
                    {{ __('messages.total_quantity') }}
                </th>
                <th class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-right text-xs font-medium text-white uppercase tracking-wider">
                    {{ __('messages.avg_unit_price') }}
                </th>
                <th class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-right text-xs font-medium text-white uppercase tracking-wider">
                    {{ __('messages.total_cost') }}
                </th>
                <th class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-center text-xs font-medium text-white uppercase tracking-wider">
                    {{ __('messages.percentage') }}
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($reportData as $index => $row)
                <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-green-50 transition-colors duration-200">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $row['product_name'] }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $row['product_sku'] }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                        {{ number_format($row['total_quantity'], 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                        {{ number_format($row['avg_unit_price'], 2) }} {{ __('messages.currency') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                        {{ number_format($row['total_cost'], 2) }} {{ __('messages.currency') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        @php
                            $totalValue = collect($reportData)->sum('total_cost');
                            $percentage = $totalValue > 0 ? ($row['total_cost'] / $totalValue) * 100 : 0;
                        @endphp
                        <div class="flex items-center">
                            <div class="flex-1 bg-gray-200 rounded-full h-2.5">
                                <div class="bg-green-600 h-2.5 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                            <span class="ml-2 text-xs text-gray-500">{{ number_format($percentage, 1) }}%</span>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        {{ __('messages.no_data_available') }}
                    </td>
                </tr>
            @endforelse
            
            <!-- Rodapé da tabela com totais -->
            @if(count($reportData) > 0)
                <tr class="bg-gray-100 font-medium">
                    <td colspan="2" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ __('messages.total') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                        {{ number_format(collect($reportData)->sum('total_quantity'), 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                        {{ number_format(collect($reportData)->avg('avg_unit_price'), 2) }} {{ __('messages.currency') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                        {{ number_format(collect($reportData)->sum('total_cost'), 2) }} {{ __('messages.currency') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                        100%
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
