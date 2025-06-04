<div>
    <div class="flex flex-col md:flex-row md:space-x-4">
        <div class="w-full md:w-1/2 mb-6 md:mb-0">
            <div class="bg-white rounded-lg p-4 h-64">
                <canvas id="maintenanceTypeChart" wire:ignore></canvas>
            </div>
        </div>
        <div class="w-full md:w-1/2">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                    <div class="flex items-center justify-between">
                        <div class="text-green-700">
                            <div class="text-lg font-bold">{{ $counts['preventive'] }}</div>
                            <div class="text-sm">{{ __('messages.preventive') }}</div>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <i class="fas fa-shield-alt text-green-500"></i>
                        </div>
                    </div>
                    <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $percentages['preventive'] }}%"></div>
                    </div>
                    <div class="text-right text-xs text-gray-500 mt-1">{{ $percentages['preventive'] }}%</div>
                </div>
                
                <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                    <div class="flex items-center justify-between">
                        <div class="text-yellow-700">
                            <div class="text-lg font-bold">{{ $counts['corrective'] }}</div>
                            <div class="text-sm">{{ __('messages.corrective') }}</div>
                        </div>
                        <div class="bg-yellow-100 p-3 rounded-full">
                            <i class="fas fa-wrench text-yellow-500"></i>
                        </div>
                    </div>
                    <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ $percentages['corrective'] }}%"></div>
                    </div>
                    <div class="text-right text-xs text-gray-500 mt-1">{{ $percentages['corrective'] }}%</div>
                </div>
                
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <div class="flex items-center justify-between">
                        <div class="text-blue-700">
                            <div class="text-lg font-bold">{{ $counts['predictive'] }}</div>
                            <div class="text-sm">{{ __('messages.predictive') }}</div>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i class="fas fa-chart-line text-blue-500"></i>
                        </div>
                    </div>
                    <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $percentages['predictive'] }}%"></div>
                    </div>
                    <div class="text-right text-xs text-gray-500 mt-1">{{ $percentages['predictive'] }}%</div>
                </div>
                
                <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                    <div class="flex items-center justify-between">
                        <div class="text-purple-700">
                            <div class="text-lg font-bold">{{ $counts['conditional'] }}</div>
                            <div class="text-sm">{{ __('messages.conditional') }}</div>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-full">
                            <i class="fas fa-stopwatch text-purple-500"></i>
                        </div>
                    </div>
                    <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-purple-500 h-2 rounded-full" style="width: {{ $percentages['conditional'] }}%"></div>
                    </div>
                    <div class="text-right text-xs text-gray-500 mt-1">{{ $percentages['conditional'] }}%</div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:load', function () {
        const ctxType = document.getElementById('maintenanceTypeChart');
        const typeData = @json($chartData);
        const typeOptions = @json($chartOptions);
        
        let typeChart = new Chart(ctxType, {
            type: 'bar',
            data: typeData,
            options: typeOptions
        });
        
        // Atualizar o gráfico quando os dados mudarem
        Livewire.on('refresh-charts', () => {
            typeChart.data = JSON.parse('@json($chartData)');
            typeChart.update();
        });
        
        Livewire.hook('message.processed', (message, component) => {
            if (component.fingerprint.name === 'maintenance-type-chart') {
                typeChart.data = JSON.parse(component.serverMemo.data.chartData);
                typeChart.options = JSON.parse(component.serverMemo.data.chartOptions);
                typeChart.update();
            }
        });
    });
</script>
@endpush
