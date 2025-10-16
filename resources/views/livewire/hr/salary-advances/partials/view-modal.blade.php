<!-- Modal de Visualização de Adiantamento -->
<div>
    <div x-data="{ open: @entangle('showViewModal') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50" 
         role="dialog" 
         aria-modal="true"
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0">
        <div class="relative top-20 mx-auto p-1 w-full max-w-4xl">
            <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" 
                 x-transition:enter="transition ease-out duration-300" 
                 x-transition:enter-start="transform opacity-0 scale-95" 
                 x-transition:enter-end="transform opacity-100 scale-100" 
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="transform opacity-100 scale-100" 
                 x-transition:leave-end="transform opacity-0 scale-95">
                
                <!-- Cabeçalho com gradiente -->
                <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-eye mr-2 animate-pulse"></i>
                        {{ __('messages.view_advance_details') }}
                    </h3>
                    <button type="button" wire:click="closeViewModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <!-- Sistema de navegação por abas -->
                <div class="mb-6 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 transition-all duration-200 ease-in-out hover:shadow-md mx-4 mt-4">
                    <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                        <i class="fas fa-layer-group text-blue-600 mr-2"></i>
                        <h2 class="text-base font-medium text-gray-700">{{ __('messages.advance_sections') }}</h2>
                    </div>
                    <div class="p-3 flex flex-wrap gap-2">
                        <button type="button" 
                            wire:click="setViewTab('details')" 
                            class="{{ $currentViewTab == 'details' ? 'bg-blue-100 border-blue-500 text-blue-700' : 'bg-gray-50 hover:bg-gray-100 text-gray-700' }} px-4 py-2 rounded-md border focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center transition-all duration-200 ease-in-out transform hover:scale-105">
                            <i class="fas fa-info-circle mr-2 transition-all duration-200 {{ $currentViewTab == 'details' ? 'text-blue-600 animate-pulse' : '' }}"></i>{{ __('messages.details') }}
                        </button>
                        <button type="button" 
                            wire:click="setViewTab('payments')" 
                            class="{{ $currentViewTab == 'payments' ? 'bg-blue-100 border-blue-500 text-blue-700' : 'bg-gray-50 hover:bg-gray-100 text-gray-700' }} px-4 py-2 rounded-md border focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center transition-all duration-200 ease-in-out transform hover:scale-105">
                            <i class="fas fa-money-bill-wave mr-2 transition-all duration-200 {{ $currentViewTab == 'payments' ? 'text-blue-600 animate-pulse' : '' }}"></i>{{ __('messages.payments') }}
                        </button>
                    </div>
                </div>
                
                <!-- Conteúdo das abas -->
                <div class="space-y-6 px-4 pb-4" x-data="{ activeTab: @entangle('currentViewTab') }" x-cloak>
                    <!-- Aba de Detalhes -->
                    <div x-show="activeTab == 'details'" 
                         x-transition:enter="transition ease-out duration-300" 
                         x-transition:enter-start="opacity-0 transform scale-95" 
                         x-transition:enter-end="opacity-100 transform scale-100" 
                         x-transition:leave="transition ease-in duration-200" 
                         x-transition:leave-start="opacity-100 transform scale-100" 
                         x-transition:leave-end="opacity-0 transform scale-95">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Informações do Adiantamento -->
                            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                                <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                    <h3 class="text-base font-medium text-gray-700">{{ __('messages.advance_information') }}</h3>
                                </div>
                                <div class="p-4 space-y-4">
                                    <div>
                                        <h4 class="text-xs font-medium text-gray-500">{{ __('messages.employee') }}</h4>
                                        <p class="text-sm font-medium text-gray-800">@if($employee_id)
                                            {{ \App\Models\HR\Employee::find($employee_id)->full_name ?? '-' }}
                                        @else
                                            -
                                        @endif</p>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-medium text-gray-500">{{ __('messages.request_date') }}</h4>
                                        <p class="text-sm font-medium text-gray-800">{{ $request_date ? \Carbon\Carbon::parse($request_date)->format('d/m/Y') : '-' }}</p>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-medium text-gray-500">{{ __('messages.amount') }}</h4>
                                        <p class="text-sm font-medium text-gray-800">{{ number_format($amount ?? 0, 2) }} Kz</p>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-medium text-gray-500">{{ __('messages.installments') }}</h4>
                                        <p class="text-sm font-medium text-gray-800">{{ $installments ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-medium text-gray-500">{{ __('messages.installment_amount') }}</h4>
                                        <p class="text-sm font-medium text-gray-800">{{ number_format($installment_amount ?? 0, 2) }} Kz</p>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-medium text-gray-500">{{ __('messages.first_deduction_date') }}</h4>
                                        <p class="text-sm font-medium text-gray-800">{{ $first_deduction_date ? \Carbon\Carbon::parse($first_deduction_date)->format('d/m/Y') : '-' }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Status e Progresso -->
                            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                                <div class="flex items-center bg-gradient-to-r from-green-50 to-green-100 px-4 py-3 border-b border-gray-200">
                                    <i class="fas fa-chart-line text-green-600 mr-2"></i>
                                    <h3 class="text-base font-medium text-gray-700">{{ __('messages.status_and_progress') }}</h3>
                                </div>
                                <div class="p-4 space-y-4">
                                    <div>
                                        <h4 class="text-xs font-medium text-gray-500">{{ __('messages.status') }}</h4>
                                        <div class="mt-1">
                                            @if($status === 'pending')
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    <i class="fas fa-clock mr-1"></i> {{ __('messages.pending') }}
                                                </span>
                                            @elseif($status === 'approved')
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    <i class="fas fa-check-circle mr-1"></i> {{ __('messages.approved') }}
                                                </span>
                                            @elseif($status === 'completed')
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    <i class="fas fa-check-double mr-1"></i> {{ __('messages.completed') }}
                                                </span>
                                            @else
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    <i class="fas fa-times-circle mr-1"></i> {{ __('messages.rejected') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-medium text-gray-500">{{ __('messages.progress') }}</h4>
                                        @php
                                            // Carregar o adiantamento atual para calcular o progresso
                                            $advance = \App\Models\HR\SalaryAdvance::find($advance_id);
                                            $remainingInstallments = $advance ? $advance->remaining_installments : 0;
                                            $progressPercentage = $installments && $installments > 0 
                                                ? (($installments - $remainingInstallments) / $installments) * 100 
                                                : 0;
                                        @endphp
                                        <div class="mt-1">
                                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                                <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $progressPercentage }}%"></div>
                                            </div>
                                            <div class="flex justify-between mt-1 text-xs text-gray-500">
                                                <span>{{ $installments ? ($installments - $remainingInstallments) : 0 }}/{{ $installments ?? 0 }} {{ __('messages.installments_paid') }}</span>
                                                <span>{{ number_format($progressPercentage, 0) }}%</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-medium text-gray-500">{{ __('messages.remaining_amount') }}</h4>
                                        <p class="text-sm font-medium text-gray-800">{{ number_format($viewAdvance?->remaining_amount ?? 0, 2) }} Kz</p>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-medium text-gray-500">{{ __('messages.paid_amount') }}</h4>
                                        <p class="text-sm font-medium text-gray-800">{{ number_format(($viewAdvance?->amount ?? 0) - ($viewAdvance?->remaining_amount ?? 0), 2) }} Kz</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Informações Adicionais -->
                            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden md:col-span-2">
                                <div class="flex items-center bg-gradient-to-r from-purple-50 to-purple-100 px-4 py-3 border-b border-gray-200">
                                    <i class="fas fa-comment-alt text-purple-600 mr-2"></i>
                                    <h3 class="text-base font-medium text-gray-700">{{ __('messages.additional_information') }}</h3>
                                </div>
                                <div class="p-4 space-y-4">
                                    <div>
                                        <h4 class="text-xs font-medium text-gray-500">{{ __('messages.reason') }}</h4>
                                        <p class="text-sm font-medium text-gray-800">{{ $viewAdvance?->reason ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-medium text-gray-500">{{ __('messages.notes') }}</h4>
                                        <p class="text-sm font-medium text-gray-800">{{ $viewAdvance?->notes ?? '-' }}</p>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @php
                                            $currentAdvance = $advance_id ? \App\Models\HR\SalaryAdvance::find($advance_id) : null;
                                        @endphp
                                        <div>
                                            <h4 class="text-xs font-medium text-gray-500">{{ __('messages.created_at') }}</h4>
                                            <p class="text-sm font-medium text-gray-800">{{ $currentAdvance?->created_at ? $currentAdvance->created_at->format('d/m/Y H:i') : '-' }}</p>
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-medium text-gray-500">{{ __('messages.updated_at') }}</h4>
                                            <p class="text-sm font-medium text-gray-800">{{ $currentAdvance?->updated_at ? $currentAdvance->updated_at->format('d/m/Y H:i') : '-' }}</p>
                                        </div>
                                    </div>
                                    @if($status !== 'pending')
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <h4 class="text-xs font-medium text-gray-500">{{ __('messages.approved_by') }}</h4>
                                            <p class="text-sm font-medium text-gray-800">{{ $currentAdvance?->approver?->name ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-medium text-gray-500">{{ __('messages.approval_date') }}</h4>
                                            <p class="text-sm font-medium text-gray-800">{{ $currentAdvance?->approved_at ? $currentAdvance->approved_at->format('d/m/Y H:i') : '-' }}</p>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Aba de Pagamentos -->
                    <div x-show="activeTab == 'payments'" 
                         x-transition:enter="transition ease-out duration-300" 
                         x-transition:enter-start="opacity-0 transform scale-95" 
                         x-transition:enter-end="opacity-100 transform scale-100" 
                         x-transition:leave="transition ease-in duration-200" 
                         x-transition:leave-start="opacity-100 transform scale-100" 
                         x-transition:leave-end="opacity-0 transform scale-95">
                        
                        <!-- Lista de Pagamentos -->
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                            <div class="flex items-center justify-between bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                                <div class="flex items-center">
                                    <i class="fas fa-money-bill-wave text-blue-600 mr-2"></i>
                                    <h3 class="text-base font-medium text-gray-700">{{ __('messages.payment_history') }}</h3>
                                </div>
                                @if($status === 'approved' && $currentAdvance && $currentAdvance->remaining_installments > 0)
                                <button wire:click="registerPaymentModal({{ $advance_id }})" class="px-3 py-1 text-xs bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200 transform hover:scale-105 flex items-center">
                                    <i class="fas fa-plus-circle mr-1"></i> {{ __('messages.register_payment') }}
                                </button>
                                @endif
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.payment_date') }}</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.amount') }}</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.processed_by') }}</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.notes') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($paymentHistory ?? [] as $payment)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $payment->payment_date->format('d/m/Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ number_format($payment->amount, 2) }} Kz
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $payment->processor->name ?? '-' }}
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-900">
                                                    {{ $payment->notes ?? '-' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-6 py-4 text-sm text-gray-500 text-center">
                                                    <i class="fas fa-info-circle mr-1"></i> {{ __('messages.no_payment_records') }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Botões de ação -->
                <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                    <button type="button" wire:click="closeViewModal" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <i class="fas fa-times mr-2"></i>
                        {{ __('messages.close') }}
                    </button>
                    
                    @if($status !== 'completed')
                        @if($status === 'pending')
                            <button type="button" wire:click="approve({{ $advance_id }})" class="inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                <i class="fas fa-check mr-2"></i>
                                {{ __('messages.approve') }}
                            </button>
                            <button type="button" wire:click="reject({{ $advance_id }})" class="inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                <i class="fas fa-times mr-2"></i>
                                {{ __('messages.reject') }}
                            </button>
                        @endif
                        
                        @if($status === 'approved' && $currentAdvance && $currentAdvance->remaining_installments > 0)
                            <button type="button" wire:click="registerPaymentModal({{ $advance_id }})" class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                <i class="fas fa-hand-holding-usd mr-2"></i>
                                {{ __('messages.register_payment') }}
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
