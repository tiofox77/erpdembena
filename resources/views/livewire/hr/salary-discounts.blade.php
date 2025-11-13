<div>
    <!-- Cabeçalho da página -->
    <div class="mb-6 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="bg-gradient-to-r from-red-600 to-red-700 px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="bg-white bg-opacity-20 rounded-lg p-2 mr-3">
                        <i class="fas fa-minus-circle text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white">{{ __('messages.salary_discounts') }}</h1>
                        <p class="text-red-100 text-sm">{{ __('messages.salary_discounts_description') }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <button wire:click="create" class="px-4 py-2 bg-white text-red-600 rounded-lg hover:bg-red-50 transition-colors duration-200 flex items-center font-medium shadow-sm">
                        <i class="fas fa-plus mr-2"></i>
                        {{ __('messages.add_discount') }}
                    </button>
                    <button class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors duration-200 flex items-center font-medium shadow-sm">
                        <i class="fas fa-file-pdf mr-2"></i>
                        {{ __('messages.export_pdf') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                <!-- Campo de pesquisa -->
                <div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input wire:model.live="search" type="text" 
                               placeholder="{{ __('messages.search_employee') }}"
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200">
                    </div>
                </div>
                
                <!-- Filtro de status -->
                <div>
                    <select wire:model.live="statusFilter" class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200">
                        <option value="">{{ __('messages.all_statuses') }}</option>
                        <option value="pending">{{ __('messages.pending') }}</option>
                        <option value="approved">{{ __('messages.approved') }}</option>
                        <option value="rejected">{{ __('messages.rejected') }}</option>
                        <option value="completed">{{ __('messages.completed') }}</option>
                    </select>
                </div>
                
                <!-- Filtro de tipo -->
                <div>
                    <select wire:model.live="typeFilter" class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200">
                        <option value="">{{ __('messages.all_types') }}</option>
                        <option value="union">{{ __('messages.union_discount') }}</option>
                        <option value="others">{{ __('messages.other_discount') }}</option>
                        <option value="quixiquila">Quixiquila</option>
                    </select>
                </div>
                
                <!-- Data de -->
                <div>
                    <input type="date" wire:model.live="dateFrom" 
                           placeholder="{{ __('messages.date_from') }}"
                           class="block w-full pl-3 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200">
                </div>
                
                <!-- Data até -->
                <div>
                    <input type="date" wire:model.live="dateTo" 
                           placeholder="{{ __('messages.date_to') }}"
                           class="block w-full pl-3 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200">
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de descontos -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th wire:click="sortBy('employee_id')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            <div class="flex items-center">
                                <i class="fas fa-user mr-2 text-gray-400"></i>
                                {{ __('messages.employee') }}
                                @if($sortBy === 'employee_id')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-red-500"></i>
                                @endif
                            </div>
                        </th>
                        <th wire:click="sortBy('request_date')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            <div class="flex items-center">
                                <i class="fas fa-calendar-day mr-2 text-gray-400"></i>
                                {{ __('messages.request_date') }}
                                @if($sortBy === 'request_date')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-red-500"></i>
                                @endif
                            </div>
                        </th>
                        <th wire:click="sortBy('amount')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            <div class="flex items-center">
                                <i class="fas fa-money-bill-wave mr-2 text-gray-400"></i>
                                {{ __('messages.amount') }}
                                @if($sortBy === 'amount')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-red-500"></i>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center">
                                <i class="fas fa-calculator mr-2 text-gray-400"></i>
                                {{ __('messages.installments') }}
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center">
                                <i class="fas fa-chart-line mr-2 text-gray-400"></i>
                                {{ __('messages.progress') }}
                            </div>
                        </th>
                        <th wire:click="sortBy('discount_type')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            <div class="flex items-center">
                                <i class="fas fa-tag mr-2 text-gray-400"></i>
                                {{ __('messages.type') }}
                                @if($sortBy === 'discount_type')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-red-500"></i>
                                @endif
                            </div>
                        </th>
                        <th wire:click="sortBy('status')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            <div class="flex items-center">
                                <i class="fas fa-info-circle mr-2 text-gray-400"></i>
                                {{ __('messages.status') }}
                                @if($sortBy === 'status')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-red-500"></i>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <i class="fas fa-cogs mr-2 text-gray-400"></i>
                            {{ __('messages.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($discounts as $discount)
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-red-400 to-red-600 flex items-center justify-center">
                                            <span class="text-white font-bold text-sm">
                                                {{ strtoupper(substr($discount->employee->full_name, 0, 2)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $discount->employee->full_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $discount->employee->employee_id ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <i class="fas fa-calendar-day text-blue-500 mr-2"></i>
                                    <span class="text-sm text-gray-900">{{ $discount->request_date->format('d/m/Y') }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <i class="fas fa-money-bill-wave text-green-500 mr-2"></i>
                                    <span class="text-sm font-semibold text-gray-900">{{ number_format($discount->amount, 2) }} Kz</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-gray-900">
                                        {{ $discount->installments - $discount->remaining_installments }}/{{ $discount->installments }}
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        {{ number_format($discount->installment_amount, 2) }} Kz cada
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $progressPercentage = $discount->installments > 0 
                                        ? (($discount->installments - $discount->remaining_installments) / $discount->installments) * 100 
                                        : 0;
                                    $progressColor = $progressPercentage == 100 ? 'bg-green-500' : ($progressPercentage > 50 ? 'bg-blue-500' : 'bg-yellow-500');
                                @endphp
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2">
                                        <div class="{{ $progressColor }} h-2 rounded-full transition-all duration-300" style="width: {{ $progressPercentage }}%"></div>
                                    </div>
                                    <span class="ml-2 text-sm text-gray-600">{{ number_format($progressPercentage, 0) }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($discount->discount_type === 'union')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                        <i class="fas fa-users mr-1"></i>
                                        {{ __('messages.union_discount') }}
                                    </span>
                                @elseif($discount->discount_type === 'quixiquila')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 border border-orange-200">
                                        <i class="fas fa-hand-holding-usd mr-1"></i>
                                        Quixiquila
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 border border-purple-200">
                                        <i class="fas fa-tag mr-1"></i>
                                        {{ __('messages.other_discount') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($discount->status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">
                                        <i class="fas fa-clock mr-1"></i>
                                        {{ __('messages.pending') }}
                                    </span>
                                @elseif($discount->status === 'approved')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        {{ __('messages.approved') }}
                                    </span>
                                @elseif($discount->status === 'rejected')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        {{ __('messages.rejected') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                        <i class="fas fa-flag-checkered mr-1"></i>
                                        {{ __('messages.completed') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-1">
                                    <!-- Visualizar -->
                                    <button wire:click="view({{ $discount->id }})" 
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50 rounded-md transition-all duration-200"
                                            title="{{ __('messages.view') }}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    <!-- Gerar PDF -->
                                    <button wire:click="generatePDF({{ $discount->id }})" 
                                            wire:loading.attr="disabled"
                                            wire:target="generatePDF({{ $discount->id }})"
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-600 hover:text-red-900 hover:bg-red-50 rounded-md transition-all duration-200 disabled:opacity-50"
                                            title="Gerar PDF">
                                        <i class="fas fa-file-pdf" wire:loading.remove wire:target="generatePDF({{ $discount->id }})"></i>
                                        <i class="fas fa-spinner fa-spin" wire:loading wire:target="generatePDF({{ $discount->id }})"></i>
                                    </button>
                                    
                                    <!-- Documento Assinado -->
                                    @if($discount->signed_document)
                                        <a href="{{ asset('storage/' . $discount->signed_document) }}" 
                                           target="_blank"
                                           class="inline-flex items-center px-2 py-1 text-xs font-medium text-teal-600 hover:text-teal-900 hover:bg-teal-50 rounded-md transition-all duration-200"
                                           title="{{ __('messages.view_signed_document') }}">
                                            <i class="fas fa-file-signature"></i>
                                        </a>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-300 cursor-not-allowed rounded-md"
                                              title="{{ __('messages.no_signed_document') }}">
                                            <i class="fas fa-file-signature"></i>
                                        </span>
                                    @endif
                                    
                                    <!-- Editar (apenas se pending ou approved) -->
                                    @if(in_array($discount->status, ['pending', 'approved']))
                                        <button wire:click="edit({{ $discount->id }})" 
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-yellow-600 hover:text-yellow-900 hover:bg-yellow-50 rounded-md transition-all duration-200"
                                                title="{{ __('messages.edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @endif
                                    
                                    <!-- Aprovar/Rejeitar (apenas se pending) -->
                                    @if($discount->status === 'pending')
                                        <button wire:click="approve({{ $discount->id }})" 
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-600 hover:text-green-900 hover:bg-green-50 rounded-md transition-all duration-200"
                                                title="{{ __('messages.approve') }}">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button wire:click="reject({{ $discount->id }})" 
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-600 hover:text-red-900 hover:bg-red-50 rounded-md transition-all duration-200"
                                                title="{{ __('messages.reject') }}">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                    
                                    <!-- Registrar Pagamento (apenas se approved com parcelas restantes) -->
                                    @if($discount->status === 'approved' && $discount->remaining_installments > 0)
                                        <button wire:click="registerPaymentModal({{ $discount->id }})" 
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-md transition-all duration-200"
                                                title="{{ __('messages.register_payment') }}">
                                            <i class="fas fa-dollar-sign"></i>
                                        </button>
                                    @endif
                                    
                                    <!-- Apagar (agora incluindo approved) -->
                                    @if(in_array($discount->status, ['pending', 'approved', 'rejected', 'completed']))
                                        <button wire:click="confirmDelete({{ $discount->id }})" 
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-600 hover:text-red-900 hover:bg-red-50 rounded-md transition-all duration-200"
                                                title="{{ __('messages.delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="bg-gray-100 rounded-full p-4 mb-4">
                                        <i class="fas fa-minus-circle text-gray-400 text-3xl"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('messages.no_discounts_found') }}</h3>
                                    <p class="text-gray-500 mb-4">{{ __('messages.no_discounts_description') }}</p>
                                    <button wire:click="create" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200">
                                        <i class="fas fa-plus mr-2"></i>
                                        {{ __('messages.add_first_discount') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Paginação -->
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $discounts->links() }}
        </div>
    </div>

    <!-- Incluir modal de criação/edição -->
    @include('livewire.hr.salary-discounts-modal')
    
    <!-- Incluir modal de visualização -->
    @include('livewire.hr.salary-discounts-view-modal')
    
    <!-- Incluir modal de pagamento -->
    @include('livewire.hr.salary-discounts-payment-modal')
    
    <!-- Incluir modal de exclusão -->
    @include('livewire.hr.salary-discounts-delete-modal')
</div>
