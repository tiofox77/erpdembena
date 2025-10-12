<div class="container-fluid px-4">
    {{-- Header --}}
    <div class="mb-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-file-pdf text-red-600 mr-3"></i>
                    Relatórios de Folha de Pagamento
                </h1>
                <p class="text-gray-600 mt-1">Gere relatórios de batches (lotes) ou recibos individuais de pagamento</p>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            {{-- Report Type --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-list mr-1"></i>
                    Tipo de Relatório
                </label>
                <select 
                    wire:model.live="reportType"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="batch">Batch (Lotes)</option>
                    <option value="individual">Individual (Funcionários)</option>
                </select>
            </div>
            
            {{-- Search --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-search mr-1"></i>
                    Pesquisar
                </label>
                <input 
                    type="text" 
                    wire:model.live="search"
                    placeholder="{{ $reportType === 'batch' ? 'Nome do batch...' : 'Nome do funcionário...' }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
            </div>

            {{-- Period Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar mr-1"></i>
                    Período
                </label>
                <select 
                    wire:model.live="selectedPeriod"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="">Todos os Períodos</option>
                    @foreach($periods as $period)
                        <option value="{{ $period->id }}">{{ $period->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Department Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-building mr-1"></i>
                    Departamento
                </label>
                <select 
                    wire:model.live="selectedDepartment"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="">Todos os Departamentos</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Clear Filters --}}
            <div class="flex items-end">
                <button 
                    wire:click="clearFilters"
                    class="w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center justify-center"
                >
                    <i class="fas fa-times mr-2"></i>
                    Limpar Filtros
                </button>
            </div>
        </div>
    </div>

    {{-- Results --}}
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-list text-blue-600 mr-2"></i>
                @if($reportType === 'batch')
                    Batches Disponíveis ({{ $batches->total() }})
                @else
                    Pagamentos Individuais ({{ $individualPayrolls->total() }})
                @endif
            </h2>
        </div>

        @if($reportType === 'batch' && $batches->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Período</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departamento</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Funcionários</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Líquido</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($batches as $batch)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $batch->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $batch->formatted_batch_date }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $batch->payrollPeriod->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $batch->department->name ?? 'Todos' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        {{ $batch->status_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center text-sm font-semibold text-gray-900">
                                    {{ $batch->total_employees }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-bold text-green-700">
                                    {{ number_format($batch->total_net_amount, 2, ',', '.') }} AOA
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button 
                                        wire:click="generateBatchReport({{ $batch->id }})"
                                        class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors"
                                    >
                                        <i class="fas fa-file-pdf mr-2"></i>
                                        Gerar PDF
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $batches->links() }}
            </div>
        @elseif($reportType === 'individual' && $individualPayrolls->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Funcionário</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Período</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departamento</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Salário Bruto</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Deduções</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Salário Líquido</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($individualPayrolls as $payroll)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $payroll->employee->full_name ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">BI: {{ $payroll->employee->id_card ?? 'N/A' }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $payroll->payrollPeriod->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $payroll->employee->department->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-semibold text-green-700">
                                    {{ number_format($payroll->gross_salary ?? 0, 2, ',', '.') }} AOA
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-semibold text-red-600">
                                    -{{ number_format($payroll->total_deductions ?? 0, 2, ',', '.') }} AOA
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-bold text-blue-700">
                                    {{ number_format($payroll->net_salary ?? 0, 2, ',', '.') }} AOA
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button 
                                        wire:click="generateIndividualReceipt({{ $payroll->id }})"
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors"
                                    >
                                        <i class="fas fa-receipt mr-2"></i>
                                        Ver Recibo
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $individualPayrolls->links() }}
            </div>
        @else
            <div class="p-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                    <i class="fas fa-inbox text-3xl text-gray-400"></i>
                </div>
                @if($reportType === 'batch')
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum Batch Encontrado</h3>
                    <p class="text-gray-500">Não há batches processados disponíveis para gerar relatórios.</p>
                    <p class="text-gray-500 text-sm mt-2">Processe batches primeiro na seção de Payroll Batch.</p>
                @else
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum Pagamento Encontrado</h3>
                    <p class="text-gray-500">Não há pagamentos individuais processados disponíveis.</p>
                    <p class="text-gray-500 text-sm mt-2">Processe pagamentos primeiro na seção de Payroll Processing.</p>
                @endif
            </div>
        @endif
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('error'))
        <div class="fixed bottom-4 right-4 bg-red-100 border-l-4 border-red-500 p-4 rounded-lg shadow-lg z-50">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                <p class="text-red-700">{{ session('error') }}</p>
            </div>
        </div>
    @endif
</div>
