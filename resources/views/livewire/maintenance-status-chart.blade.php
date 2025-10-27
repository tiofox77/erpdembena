<div>
    <div class="flex flex-col md:flex-row md:space-x-4">
        <div class="w-full md:w-1/2 mb-6 md:mb-0">
            <div class="bg-white rounded-lg p-4 h-64">
                <canvas id="maintenanceStatusChart" wire:ignore></canvas>
            </div>
        </div>
        <div class="w-full md:w-1/2">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                    <div class="flex items-center justify-between">
                        <div class="text-yellow-700">
                            <div class="text-lg font-bold">{{ $counts['pending'] }}</div>
                            <div class="text-sm">{{ __('messages.pending') }}</div>
                        </div>
                        <div class="bg-yellow-100 p-3 rounded-full">
                            <i class="fas fa-clock text-yellow-500"></i>
                        </div>
                    </div>
                    <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ $percentages['pending'] }}%"></div>
                    </div>
                    <div class="text-right text-xs text-gray-500 mt-1">{{ $percentages['pending'] }}%</div>
                </div>
                
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <div class="flex items-center justify-between">
                        <div class="text-blue-700">
                            <div class="text-lg font-bold">{{ $counts['in_progress'] }}</div>
                            <div class="text-sm">{{ __('messages.in_progress') }}</div>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i class="fas fa-spinner text-blue-500"></i>
                        </div>
                    </div>
                    <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $percentages['in_progress'] }}%"></div>
                    </div>
                    <div class="text-right text-xs text-gray-500 mt-1">{{ $percentages['in_progress'] }}%</div>
                </div>
                
                <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                    <div class="flex items-center justify-between">
                        <div class="text-green-700">
                            <div class="text-lg font-bold">{{ $counts['completed'] }}</div>
                            <div class="text-sm">{{ __('messages.completed') }}</div>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <i class="fas fa-check text-green-500"></i>
                        </div>
                    </div>
                    <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $percentages['completed'] }}%"></div>
                    </div>
                    <div class="text-right text-xs text-gray-500 mt-1">{{ $percentages['completed'] }}%</div>
                </div>
                
                <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                    <div class="flex items-center justify-between">
                        <div class="text-purple-700">
                            <div class="text-lg font-bold">{{ $counts['schedule'] }}</div>
                            <div class="text-sm">{{ __('messages.schedule') }}</div>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-full">
                            <i class="fas fa-calendar-alt text-purple-500"></i>
                        </div>
                    </div>
                    <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-purple-500 h-2 rounded-full" style="width: {{ $percentages['schedule'] }}%"></div>
                    </div>
                    <div class="text-right text-xs text-gray-500 mt-1">{{ $percentages['schedule'] }}%</div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:load', function () {
        const ctxStatus = document.getElementById('maintenanceStatusChart');
        const statusData = @json($chartData);
        const statusOptions = @json($chartOptions);
        
        let statusChart = new Chart(ctxStatus, {
            type: 'pie',
            data: statusData,
            options: statusOptions
        });
        
        // Atualizar o gráfico quando os dados mudarem
        Livewire.on('refresh-charts', () => {
            statusChart.data = JSON.parse('@json($chartData)');
            statusChart.update();
        });
        
        Livewire.hook('message.processed', (message, component) => {
            if (component.fingerprint.name === 'maintenance-status-chart') {
                statusChart.data = JSON.parse(component.serverMemo.data.chartData);
                statusChart.options = JSON.parse(component.serverMemo.data.chartOptions);
                statusChart.update();
            }
        });
    });
</script>
@endpush
