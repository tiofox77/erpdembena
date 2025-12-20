<!-- Tabela Modernizada -->
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
        <h2 class="text-lg font-semibold text-white flex items-center">
            <i class="fas fa-money-bill-wave mr-2"></i>
            {{ __('messages.salary_advances_list') }}
        </h2>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-user text-green-600"></i>
                            <a href="#" wire:click.prevent="sortBy('employee_id')" class="flex items-center hover:text-green-600 transition-colors">
                                {{ __('messages.employee') }}
                                @if($sortField === 'employee_id')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-green-600"></i>
                                @endif
                            </a>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-calendar text-blue-600"></i>
                            <a href="#" wire:click.prevent="sortBy('request_date')" class="flex items-center hover:text-blue-600 transition-colors">
                                {{ __('messages.date') }}
                                @if($sortField === 'request_date')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-blue-600"></i>
                                @endif
                            </a>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-coins text-yellow-600"></i>
                            <a href="#" wire:click.prevent="sortBy('amount')" class="flex items-center hover:text-yellow-600 transition-colors">
                                {{ __('messages.amount') }}
                                @if($sortField === 'amount')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-yellow-600"></i>
                                @endif
                            </a>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-list-ol text-purple-600"></i>
                            <span>{{ __('messages.installments') }}</span>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-chart-line text-indigo-600"></i>
                            <span>{{ __('messages.progress') }}</span>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-flag text-red-600"></i>
                            <span>{{ __('messages.status') }}</span>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <div class="flex items-center justify-end space-x-2">
                            <i class="fas fa-cogs text-gray-600"></i>
                            <span>{{ __('messages.actions') }}</span>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($advances as $advance)
                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center">
                                        <span class="text-white font-bold text-sm">
                                            {{ strtoupper(substr($advance->employee->full_name, 0, 2)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $advance->employee->full_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $advance->employee->employee_id ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <i class="fas fa-calendar-day text-blue-500 mr-2"></i>
                                <span class="text-sm text-gray-900">{{ $advance->request_date->format('d/m/Y') }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <i class="fas fa-money-bill-wave text-green-500 mr-2"></i>
                                <span class="text-sm font-semibold text-gray-900">{{ number_format($advance->amount, 2) }} Kz</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-900">
                                    {{ $advance->installments - $advance->remaining_installments }}/{{ $advance->installments }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    {{ number_format($advance->installment_amount, 2) }} Kz cada
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $progressPercentage = $advance->installments > 0 
                                    ? (($advance->installments - $advance->remaining_installments) / $advance->installments) * 100 
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
                            @if($advance->status === 'pending')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ __('messages.pending') }}
                                </span>
                            @elseif($advance->status === 'approved')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                    <i class="fas fa-check mr-1"></i>
                                    {{ __('messages.approved') }}
                                </span>
                            @elseif($advance->status === 'completed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                    <i class="fas fa-check-double mr-1"></i>
                                    {{ __('messages.completed') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                    <i class="fas fa-times mr-1"></i>
                                    {{ __('messages.rejected') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-1">
                                <!-- Visualizar -->
                                <button wire:click="view({{ $advance->id }})" 
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50 rounded-md transition-all duration-200"
                                        title="{{ __('messages.view') }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                <!-- Ver Relatório -->
                                <a href="{{ route('hr.salary-advance-report', $advance->id) }}" 
                                   target="_blank"
                                   class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-600 hover:text-red-900 hover:bg-red-50 rounded-md transition-all duration-200"
                                   title="{{ __('messages.view_report') }}">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                
                                <!-- Documento Assinado -->
                                @if($advance->signed_document)
                                    <a href="{{ asset('storage/' . $advance->signed_document) }}" 
                                       target="_blank"
                                       class="inline-flex items-center px-2 py-1 text-xs font-medium text-teal-600 hover:text-teal-900 hover:bg-teal-50 rounded-md transition-all duration-200"
                                       title="{{ __('messages.view_signed_document') }}">
                                        <i class="fas fa-file-signature"></i>
                                    </a>
                                @else
                                    <!-- Debug: Mostrar sempre o ícone em cinza se não houver documento -->
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-300 cursor-not-allowed rounded-md"
                                          title="{{ __('messages.no_signed_document') }}">
                                        <i class="fas fa-file-signature"></i>
                                    </span>
                                @endif
                                
                                <!-- Editar (pending sempre, approved apenas admin/super-admin) -->
                                @if($advance->status === 'pending' || ($advance->status === 'approved' && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('super-admin'))))
                                    <button wire:click="edit({{ $advance->id }})" 
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-yellow-600 hover:text-yellow-900 hover:bg-yellow-50 rounded-md transition-all duration-200"
                                            title="{{ __('messages.edit') }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                @endif
                                
                                <!-- Aprovar/Rejeitar (apenas se pending) -->
                                @if($advance->status === 'pending')
                                    <button wire:click="approve({{ $advance->id }})" 
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-600 hover:text-green-900 hover:bg-green-50 rounded-md transition-all duration-200"
                                            title="{{ __('messages.approve') }}">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button wire:click="reject({{ $advance->id }})" 
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-600 hover:text-red-900 hover:bg-red-50 rounded-md transition-all duration-200"
                                            title="{{ __('messages.reject') }}">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                                
                                <!-- Registrar Pagamento (apenas se approved com parcelas restantes) -->
                                @if($advance->status === 'approved' && $advance->remaining_installments > 0)
                                    <button wire:click="registerPaymentModal({{ $advance->id }})" 
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-md transition-all duration-200"
                                            title="{{ __('messages.register_payment') }}">
                                        <i class="fas fa-dollar-sign"></i>
                                    </button>
                                @endif
                                
                                <!-- Apagar (pending/rejected/completed sempre, approved apenas admin/super-admin) -->
                                @if(in_array($advance->status, ['pending', 'rejected', 'completed']) || ($advance->status === 'approved' && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('super-admin'))))
                                    <button wire:click="confirmDelete({{ $advance->id }})" 
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
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="bg-gray-100 rounded-full p-8 mb-4">
                                    <i class="fas fa-money-bill-wave text-gray-400 text-4xl"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('messages.no_salary_advances') }}</h3>
                                <p class="text-sm text-gray-500 mb-4">{{ __('messages.no_salary_advances_description') }}</p>
                                <button wire:click="create" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                                    <i class="fas fa-plus mr-2"></i>
                                    {{ __('messages.add_advance') }}
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
