<div class="overflow-x-auto bg-white shadow-md rounded-lg">
    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr>
                <th class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-left text-xs font-medium text-white uppercase tracking-wider">
                    {{ __('messages.product') }}
                </th>
                <th class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-left text-xs font-medium text-white uppercase tracking-wider">
                    {{ __('messages.sku') }}
                </th>
                <th class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-right text-xs font-medium text-white uppercase tracking-wider">
                    {{ __('messages.planned_quantity') }}
                </th>
                <th class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-right text-xs font-medium text-white uppercase tracking-wider">
                    {{ __('messages.produced_quantity') }}
                </th>
                <th class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-right text-xs font-medium text-white uppercase tracking-wider">
                    {{ __('messages.avg_unit_cost') }}
                </th>
                <th class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-right text-xs font-medium text-white uppercase tracking-wider">
                    {{ __('messages.planned_total_cost') }}
                </th>
                <th class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-right text-xs font-medium text-white uppercase tracking-wider">
                    {{ __('messages.actual_total_cost') }}
                </th>
                <th class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-center text-xs font-medium text-white uppercase tracking-wider">
                    {{ __('messages.efficiency') }}
                </th>
                <th class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-center text-xs font-medium text-white uppercase tracking-wider">
                    {{ __('messages.cost_variance') }}
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($reportData as $index => $row)
                <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-blue-50 transition-colors duration-200">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $row['product_name'] }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $row['product_sku'] }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                        {{ number_format($row['planned_qty'], 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                        {{ number_format($row['produced_qty'], 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                        {{ number_format($row['avg_unit_cost'], 2) }} {{ __('messages.currency') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                        {{ number_format($row['total_planned_cost'], 2) }} {{ __('messages.currency') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                        {{ number_format($row['total_actual_cost'], 2) }} {{ __('messages.currency') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $row['efficiency'] >= 90 ? 'bg-green-100 text-green-800' : 
                               ($row['efficiency'] >= 75 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ $row['efficiency'] }}%
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $row['cost_variance'] <= 0 ? 'bg-green-100 text-green-800' : 
                               ($row['cost_variance'] <= 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ $row['cost_variance'] > 0 ? '+' : '' }}{{ $row['cost_variance'] }}%
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
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
                        {{ number_format(collect($reportData)->sum('planned_qty'), 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                        {{ number_format(collect($reportData)->sum('produced_qty'), 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                        {{ number_format(collect($reportData)->avg('avg_unit_cost'), 2) }} {{ __('messages.currency') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                        {{ number_format(collect($reportData)->sum('total_planned_cost'), 2) }} {{ __('messages.currency') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                        {{ number_format(collect($reportData)->sum('total_actual_cost'), 2) }} {{ __('messages.currency') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                        @php
                            $totalPlanned = collect($reportData)->sum('planned_qty');
                            $totalProduced = collect($reportData)->sum('produced_qty');
                            $avgEfficiency = $totalPlanned > 0 ? round(($totalProduced / $totalPlanned) * 100, 2) : 0;
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $avgEfficiency >= 90 ? 'bg-green-100 text-green-800' : 
                               ($avgEfficiency >= 75 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ $avgEfficiency }}%
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                        @php
                            $totalPlannedCost = collect($reportData)->sum('total_planned_cost');
                            $totalActualCost = collect($reportData)->sum('total_actual_cost');
                            $totalVariance = $totalPlannedCost > 0 ? round((($totalActualCost - $totalPlannedCost) / $totalPlannedCost) * 100, 2) : 0;
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $totalVariance <= 0 ? 'bg-green-100 text-green-800' : 
                               ($totalVariance <= 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ $totalVariance > 0 ? '+' : '' }}{{ $totalVariance }}%
                        </span>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
