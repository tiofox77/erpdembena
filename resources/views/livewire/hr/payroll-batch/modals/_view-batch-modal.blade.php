{{-- View Batch Modal --}}
@if($showViewModal && $currentBatch)
<div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-7xl mx-4 max-h-[95vh] overflow-hidden">
        
        {{-- Header --}}
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-white/20 p-2 rounded-lg">
                        <i class="fas fa-eye text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold">{{ $currentBatch->name }}</h2>
                        <p class="text-blue-100">{{ $currentBatch->payrollPeriod->name ?? 'N/A' }} • {{ $currentBatch->formatted_batch_date }}</p>
                    </div>
                </div>
                <button wire:click="closeViewModal" class="text-white/80 hover:text-white p-2">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <div class="overflow-y-auto max-h-[calc(95vh-120px)]">
            <div class="p-8">
                {{-- Status Alert --}}
                @if($currentBatch->status === 'draft' || $currentBatch->status === 'ready_to_process')
                    <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                            <div>
                                <h4 class="text-sm font-semibold text-blue-800">Lote Pronto para Processar</h4>
                                <p class="text-sm text-blue-700 mt-1">Este lote contém {{ $currentBatch->total_employees }} funcionários e está pronto para ser processado. Clique em "Processar Lote" abaixo para iniciar.</p>
                            </div>
                        </div>
                    </div>
                @elseif($currentBatch->status === 'failed')
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-red-500 mt-1 mr-3"></i>
                            <div>
                                <h4 class="text-sm font-semibold text-red-800">Processamento Falhou</h4>
                                <p class="text-sm text-red-700 mt-1">O processamento deste lote encontrou erros. Reveja os detalhes abaixo e clique em "Reprocessar Lote" para tentar novamente.</p>
                            </div>
                        </div>
                    </div>
                @elseif($currentBatch->status === 'processing')
                    <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg">
                        <div class="flex items-start">
                            <i class="fas fa-spinner fa-spin text-yellow-500 mt-1 mr-3"></i>
                            <div>
                                <h4 class="text-sm font-semibold text-yellow-800">Processamento em Andamento</h4>
                                <p class="text-sm text-yellow-700 mt-1">Este lote está sendo processado. {{ $currentBatch->processed_employees }} de {{ $currentBatch->total_employees }} funcionários já foram processados.</p>
                            </div>
                        </div>
                    </div>
                @elseif($currentBatch->status === 'completed')
                    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
                        <div class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                            <div>
                                <h4 class="text-sm font-semibold text-green-800">Lote Processado com Sucesso</h4>
                                <p class="text-sm text-green-700 mt-1">Todos os {{ $currentBatch->total_employees }} funcionários foram processados. Você pode visualizar os recibos individuais na tabela abaixo.</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Batch Summary --}}
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-xl border border-blue-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-600 font-medium text-sm">{{ __('livewire/hr/payroll-batch.status') }}</p>
                                <p class="text-lg font-bold text-blue-700">{{ $currentBatch->status_label }}</p>
                            </div>
                            <div class="bg-blue-500 p-3 rounded-full">
                                <i class="fas fa-flag text-white"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-green-50 to-emerald-100 p-6 rounded-xl border border-green-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-600 font-medium text-sm">{{ __('livewire/hr/payroll-batch.employees_text') }}</p>
                                <p class="text-lg font-bold text-green-700">{{ $currentBatch->total_employees }}</p>
                            </div>
                            <div class="bg-green-500 p-3 rounded-full">
                                <i class="fas fa-users text-white"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 p-6 rounded-xl border border-yellow-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-yellow-600 font-medium text-sm">{{ __('livewire/hr/payroll-batch.processed_text') }}</p>
                                <p class="text-lg font-bold text-yellow-700">{{ $currentBatch->processed_employees }}</p>
                            </div>
                            <div class="bg-yellow-500 p-3 rounded-full">
                                <i class="fas fa-check-circle text-white"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-6 rounded-xl border border-purple-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-600 font-medium text-sm">{{ __('livewire/hr/payroll-batch.processing_progress') }}</p>
                                <p class="text-lg font-bold text-purple-700">{{ $currentBatch->progress_percentage }}%</p>
                            </div>
                            <div class="bg-purple-500 p-3 rounded-full">
                                <i class="fas fa-chart-pie text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Progress Bar --}}
                @if($currentBatch->total_employees > 0)
                <div class="mb-8">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-700">{{ __('livewire/hr/payroll-batch.processing_progress') }}</span>
                        <span class="text-sm text-gray-500">{{ $currentBatch->processed_employees }}/{{ $currentBatch->total_employees }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-gradient-to-r from-blue-500 to-purple-600 h-3 rounded-full transition-all duration-300" 
                             style="width: {{ $currentBatch->progress_percentage }}%"></div>
                    </div>
                </div>
                @endif

                {{-- Batch Details --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    {{-- Basic Information --}}
                    <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                            {{ __('livewire/hr/payroll-batch.basic_information') }}
                        </h3>
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('livewire/hr/payroll-batch.name') }}:</span>
                                <span class="font-medium text-gray-900">{{ $currentBatch->name }}</span>
                            </div>
                            @if($currentBatch->description)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">{{ __('livewire/hr/payroll-batch.description') }}:</span>
                                    <span class="font-medium text-gray-900">{{ $currentBatch->description }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('livewire/hr/payroll-batch.period') }}:</span>
                                <span class="font-medium text-gray-900">{{ $currentBatch->payrollPeriod->name ?? 'N/A' }}</span>
                            </div>
                            @if($currentBatch->department)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">{{ __('livewire/hr/payroll-batch.department') }}:</span>
                                    <span class="font-medium text-gray-900">{{ $currentBatch->department->name }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('livewire/hr/payroll-batch.payment_method') }}:</span>
                                <span class="font-medium text-gray-900">
                                    @switch($currentBatch->payment_method)
                                        @case('bank_transfer')
                                            {{ __('livewire/hr/payroll-batch.bank_transfer') }}
                                            @break
                                        @case('cash')
                                            {{ __('livewire/hr/payroll-batch.cash') }}
                                            @break
                                        @case('check')
                                            {{ __('livewire/hr/payroll-batch.check') }}
                                            @break
                                        @default
                                            {{ $currentBatch->payment_method }}
                                    @endswitch
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('livewire/hr/payroll-batch.batch_date_label') }}:</span>
                                <span class="font-medium text-gray-900">{{ $currentBatch->formatted_batch_date }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Financial Summary --}}
                    <div class="bg-green-50 rounded-xl p-6 border border-green-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-money-bill-wave text-green-500 mr-2"></i>
                            {{ __('livewire/hr/payroll-batch.financial_summary') }}
                        </h3>
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('livewire/hr/payroll-batch.total_gross_amount') }}:</span>
                                <span class="font-bold text-green-700 text-lg">{{ number_format($currentBatch->total_gross_amount, 2, ',', '.') }} AOA</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('livewire/hr/payroll-batch.total_deductions') }}:</span>
                                <span class="font-medium text-red-600">{{ number_format($currentBatch->total_deductions, 2, ',', '.') }} AOA</span>
                            </div>
                            <div class="border-t border-green-200 pt-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 font-medium">{{ __('livewire/hr/payroll-batch.total_net_amount') }}:</span>
                                    <span class="font-bold text-green-800 text-xl">{{ number_format($currentBatch->total_net_amount, 2, ',', '.') }} AOA</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Processing Timeline --}}
                @if($currentBatch->processing_started_at || $currentBatch->processing_completed_at)
                <div class="bg-blue-50 rounded-xl p-6 border border-blue-200 mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-clock text-blue-500 mr-2"></i>
                        {{ __('livewire/hr/payroll-batch.processing_timeline') }}
                    </h3>
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                            <div>
                                <span class="text-gray-600">{{ __('livewire/hr/payroll-batch.created_at') }}:</span>
                                <span class="font-medium text-gray-900 ml-2">{{ $currentBatch->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                        @if($currentBatch->processing_started_at)
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                                <div>
                                    <span class="text-gray-600">{{ __('livewire/hr/payroll-batch.processing_started') }}:</span>
                                    <span class="font-medium text-gray-900 ml-2">{{ $currentBatch->processing_started_at->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                        @endif
                        @if($currentBatch->processing_completed_at)
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                <div>
                                    <span class="text-gray-600">{{ __('livewire/hr/payroll-batch.processing_completed') }}:</span>
                                    <span class="font-medium text-gray-900 ml-2">{{ $currentBatch->processing_completed_at->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                            @if($currentBatch->processing_duration)
                                <div class="ml-6 text-sm text-gray-500">
                                    {{ __('livewire/hr/payroll-batch.duration') }}: {{ $currentBatch->processing_duration }} {{ __('livewire/hr/payroll-batch.duration_minutes') }}
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
                @endif

                {{-- Batch Items --}}
                <div class="bg-white rounded-xl border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-list text-purple-500 mr-2"></i>
                            {{ __('livewire/hr/payroll-batch.employees_in_batch') }} ({{ $currentBatch->batchItems->count() }})
                        </h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('livewire/hr/payroll-batch.employee') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('livewire/hr/payroll-batch.status') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('livewire/hr/payroll-batch.gross_salary') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('livewire/hr/payroll-batch.net_salary') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('livewire/hr/payroll-batch.processed_at') }}</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('livewire/hr/payroll-batch.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($currentBatch->batchItems as $item)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-10 h-10 bg-gradient-to-br from-blue-100 to-blue-200 rounded-full flex items-center justify-center mr-3">
                                                    <i class="fas fa-user text-blue-600"></i>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">{{ $item->employee->full_name ?? 'N/A' }}</div>
                                                    <div class="text-sm text-gray-500">{{ $item->employee->id_card ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                {{ $item->status === 'pending' ? 'bg-gray-100 text-gray-800' : '' }}
                                                {{ $item->status === 'processing' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $item->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $item->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                                                {{ $item->status === 'skipped' ? 'bg-orange-100 text-orange-800' : '' }}
                                            ">
                                                <div class="w-1.5 h-1.5 rounded-full mr-1.5
                                                    {{ $item->status === 'pending' ? 'bg-gray-400' : '' }}
                                                    {{ $item->status === 'processing' ? 'bg-yellow-400' : '' }}
                                                    {{ $item->status === 'completed' ? 'bg-green-400' : '' }}
                                                    {{ $item->status === 'failed' ? 'bg-red-400' : '' }}
                                                    {{ $item->status === 'skipped' ? 'bg-orange-400' : '' }}
                                                "></div>
                                                {{ $item->status_label }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($item->gross_salary)
                                                {{ number_format($item->gross_salary, 0, ',', '.') }} AOA
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($item->net_salary)
                                                {{ number_format($item->net_salary, 0, ',', '.') }} AOA
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($item->processed_at)
                                                {{ $item->processed_at->format('d/m/Y H:i') }}
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if($item->status === 'completed' && $item->payroll_id)
                                                <a 
                                                    href="{{ route('payroll.receipt.view.by-id', ['payrollId' => $item->payroll_id]) }}" 
                                                    target="_blank"
                                                    class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm hover:shadow-md"
                                                    title="Ver recibo de {{ $item->employee->full_name }}"
                                                >
                                                    <i class="fas fa-file-invoice mr-2"></i>
                                                    <span>Recibo</span>
                                                </a>
                                            @else
                                                <span class="text-gray-400 text-xs italic">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @if($item->error_message)
                                        <tr class="bg-red-50">
                                            <td colspan="6" class="px-6 py-2 text-sm text-red-600">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                {{ $item->error_message }}
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            {{ __('livewire/hr/payroll-batch.no_employees_in_batch') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Notes --}}
                @if($currentBatch->notes)
                <div class="mt-8 bg-yellow-50 rounded-xl p-6 border border-yellow-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2 flex items-center">
                        <i class="fas fa-sticky-note text-yellow-500 mr-2"></i>
                        {{ __('livewire/hr/payroll-batch.notes') }}
                    </h3>
                    <p class="text-gray-700">{{ $currentBatch->notes }}</p>
                </div>
                @endif

                {{-- Actions --}}
                <div class="flex items-center justify-between pt-6 border-t border-gray-200 mt-8">
                    <div class="flex items-center space-x-3">
                        @if($currentBatch->canBeProcessed())
                            <button
                                wire:click="processBatch({{ $currentBatch->id }})"
                                class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors flex items-center space-x-2 shadow-md hover:shadow-lg"
                            >
                                <i class="fas fa-play"></i>
                                <span>{{ $currentBatch->status === 'failed' ? 'Reprocessar Lote' : __('livewire/hr/payroll-batch.process_batch_button') }}</span>
                            </button>
                        @endif
                        
                        @if($currentBatch->status === 'completed')
                            <div class="px-4 py-2 bg-green-50 border border-green-200 rounded-lg flex items-center space-x-2">
                                <i class="fas fa-check-circle text-green-600"></i>
                                <span class="text-sm font-medium text-green-700">Lote processado com sucesso</span>
                            </div>
                        @endif
                        
                        @if($currentBatch->status === 'processing')
                            <div class="px-4 py-2 bg-yellow-50 border border-yellow-200 rounded-lg flex items-center space-x-2">
                                <i class="fas fa-spinner fa-spin text-yellow-600"></i>
                                <span class="text-sm font-medium text-yellow-700">Processamento em andamento...</span>
                            </div>
                        @endif
                    </div>
                    
                    <button
                        wire:click="closeViewModal"
                        class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-medium transition-colors"
                    >
                        {{ __('livewire/hr/payroll-batch.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
