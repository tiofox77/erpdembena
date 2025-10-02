{{-- Delete Batch Modal --}}
@if($showDeleteModal && $batchToDelete)
<div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4">
        
        {{-- Header --}}
        <div class="bg-gradient-to-r from-red-600 to-red-700 p-6 text-white rounded-t-2xl">
            <div class="flex items-center space-x-3">
                <div class="bg-white/20 p-2 rounded-lg">
                    <i class="fas fa-exclamation-triangle text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold">{{ __('livewire/hr/payroll-batch.confirm_deletion') }}</h2>
                    <p class="text-red-100 text-sm">{{ __('livewire/hr/payroll-batch.action_cannot_be_undone') }}</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            {{-- Warning Message --}}
            <div class="mb-6">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                    <div class="flex items-start space-x-3">
                        <div class="bg-red-100 p-2 rounded-full flex-shrink-0">
                            <i class="fas fa-trash text-red-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-red-800 mb-2">
                                {{ __('livewire/hr/payroll-batch.delete_batch_title') }}
                            </h3>
                            <p class="text-red-700 text-sm mb-3">
                                {{ __('livewire/hr/payroll-batch.delete_batch_message') }} <strong>"{{ $batchToDelete->name }}"</strong>.
                            </p>
                            <div class="space-y-2 text-sm text-red-600">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-users text-xs"></i>
                                    <span>{{ $batchToDelete->total_employees }} {{ __('livewire/hr/payroll-batch.employees_will_be_removed') }}</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-calendar text-xs"></i>
                                    <span>{{ __('livewire/hr/payroll-batch.period_label') }}: {{ $batchToDelete->payrollPeriod->name ?? 'N/A' }}</span>
                                </div>
                                @if($batchToDelete->department)
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-building text-xs"></i>
                                        <span>{{ __('livewire/hr/payroll-batch.department_label') }}: {{ $batchToDelete->department->name }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Additional Warnings --}}
                <div class="space-y-3">
                    @if($batchToDelete->status === 'processing')
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                            <div class="flex items-center space-x-2 text-yellow-800">
                                <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                                <span class="text-sm font-medium">
                                    Atenção: Este lote está sendo processado no momento!
                                </span>
                            </div>
                        </div>
                    @endif

                    @if($batchToDelete->processed_employees > 0)
                        <div class="bg-orange-50 border border-orange-200 rounded-lg p-3">
                            <div class="flex items-center space-x-2 text-orange-800">
                                <i class="fas fa-info-circle text-orange-600"></i>
                                <span class="text-sm">
                                    {{ $batchToDelete->processed_employees }} funcionário(s) já foram processados neste lote.
                                </span>
                            </div>
                        </div>
                    @endif

                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                        <div class="flex items-start space-x-2 text-gray-700">
                            <i class="fas fa-lightbulb text-gray-500 mt-0.5"></i>
                            <div class="text-sm">
                                <p class="font-medium mb-1">O que acontecerá:</p>
                                <ul class="space-y-1 text-xs text-gray-600">
                                    <li>• O lote será removido permanentemente</li>
                                    <li>• Registros de funcionários no lote serão excluídos</li>
                                    <li>• Folhas de pagamento já processadas NÃO serão afetadas</li>
                                    <li>• Esta ação aparecerá nos logs do sistema</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Batch Summary --}}
            <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
                <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                    Resumo do Lote
                </h4>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Nome:</span>
                            <span class="font-medium text-gray-900 truncate ml-2">{{ $batchToDelete->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status:</span>
                            <span class="font-medium {{ $batchToDelete->status_color }}">{{ $batchToDelete->status_label }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Funcionários:</span>
                            <span class="font-medium text-gray-900">{{ $batchToDelete->total_employees }}</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Processados:</span>
                            <span class="font-medium text-gray-900">{{ $batchToDelete->processed_employees }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Criado:</span>
                            <span class="font-medium text-gray-900">{{ $batchToDelete->created_at->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Criado por:</span>
                            <span class="font-medium text-gray-900">{{ $batchToDelete->creator->name ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end space-x-4">
                <button
                    wire:click="closeDeleteModal"
                    class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-medium transition-colors"
                >
                    {{ __('livewire/hr/payroll-batch.cancel') }}
                </button>
                <button
                    wire:click="deleteBatch"
                    class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors flex items-center space-x-2"
                >
                    <i class="fas fa-trash"></i>
                    <span>{{ __('livewire/hr/payroll-batch.delete_batch') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endif
