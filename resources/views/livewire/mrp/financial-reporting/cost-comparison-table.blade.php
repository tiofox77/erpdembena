<div class="overflow-x-auto bg-white shadow-md rounded-lg">
    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr>
                <th class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 text-left text-xs font-medium text-white uppercase tracking-wider">
                    {{ __('messages.category') }}
                </th>
                <th class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 text-center text-xs font-medium text-white uppercase tracking-wider">
                    {{ __('messages.current_period') }}
                    <div class="text-xs font-normal mt-1 opacity-80">
                        {{ $reportData['current_period']['start'] }} - {{ $reportData['current_period']['end'] }}
                    </div>
                </th>
                <th class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 text-center text-xs font-medium text-white uppercase tracking-wider">
                    {{ __('messages.previous_period') }}
                    <div class="text-xs font-normal mt-1 opacity-80">
                        {{ $reportData['previous_period']['start'] }} - {{ $reportData['previous_period']['end'] }}
                    </div>
                </th>
                <th class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 text-center text-xs font-medium text-white uppercase tracking-wider">
                    {{ __('messages.variation') }}
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <!-- Linha 1: Custos de Produção -->
            <tr class="bg-white hover:bg-indigo-50 transition-colors duration-200">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 flex items-center">
                    <div class="w-3 h-3 bg-blue-500 rounded-sm mr-2"></div>
                    {{ __('messages.production_costs') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center font-medium">
                    {{ number_format($reportData['current_period']['production_costs'], 2) }} {{ __('messages.currency') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                    {{ number_format($reportData['previous_period']['production_costs'], 2) }} {{ __('messages.currency') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $reportData['variations']['production'] <= 0 ? 'bg-green-100 text-green-800' : 
                           ($reportData['variations']['production'] <= 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                        {{ $reportData['variations']['production'] > 0 ? '+' : '' }}{{ $reportData['variations']['production'] }}%
                        <i class="ml-1 fas fa-{{ $reportData['variations']['production'] > 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                    </span>
                </td>
            </tr>
            
            <!-- Linha 2: Custos de Compra -->
            <tr class="bg-gray-50 hover:bg-indigo-50 transition-colors duration-200">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-sm mr-2"></div>
                    {{ __('messages.purchase_costs') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center font-medium">
                    {{ number_format($reportData['current_period']['purchase_costs'], 2) }} {{ __('messages.currency') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                    {{ number_format($reportData['previous_period']['purchase_costs'], 2) }} {{ __('messages.currency') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $reportData['variations']['purchase'] <= 0 ? 'bg-green-100 text-green-800' : 
                           ($reportData['variations']['purchase'] <= 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                        {{ $reportData['variations']['purchase'] > 0 ? '+' : '' }}{{ $reportData['variations']['purchase'] }}%
                        <i class="ml-1 fas fa-{{ $reportData['variations']['purchase'] > 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                    </span>
                </td>
            </tr>
            
            <!-- Linha 3: Custos Totais -->
            <tr class="bg-gray-100 hover:bg-indigo-50 transition-colors duration-200 font-medium">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 flex items-center">
                    <div class="w-3 h-3 bg-indigo-500 rounded-sm mr-2"></div>
                    {{ __('messages.total_costs') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center font-bold">
                    {{ number_format($reportData['current_period']['total_costs'], 2) }} {{ __('messages.currency') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                    {{ number_format($reportData['previous_period']['total_costs'], 2) }} {{ __('messages.currency') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $reportData['variations']['total'] <= 0 ? 'bg-green-100 text-green-800' : 
                           ($reportData['variations']['total'] <= 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                        {{ $reportData['variations']['total'] > 0 ? '+' : '' }}{{ $reportData['variations']['total'] }}%
                        <i class="ml-1 fas fa-{{ $reportData['variations']['total'] > 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                    </span>
                </td>
            </tr>
        </tbody>
    </table>
    
    <!-- Análise Adicional -->
    <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
        <h4 class="text-sm font-medium text-gray-700 mb-2">{{ __('messages.analysis') }}</h4>
        <div class="text-sm text-gray-600">
            <p class="mb-2">
                @if($reportData['variations']['total'] > 0)
                    {{ __('messages.cost_increase_analysis', ['percentage' => abs($reportData['variations']['total'])]) }}
                @elseif($reportData['variations']['total'] < 0)
                    {{ __('messages.cost_decrease_analysis', ['percentage' => abs($reportData['variations']['total'])]) }}
                @else
                    {{ __('messages.cost_stable_analysis') }}
                @endif
            </p>
            
            <p>
                @if($reportData['variations']['production'] > $reportData['variations']['purchase'])
                    {{ __('messages.production_cost_focus_needed') }}
                @elseif($reportData['variations']['purchase'] > $reportData['variations']['production'])
                    {{ __('messages.purchase_cost_focus_needed') }}
                @else
                    {{ __('messages.balanced_cost_analysis') }}
                @endif
            </p>
        </div>
    </div>
</div>
