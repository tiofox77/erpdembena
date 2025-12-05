<div>
    <div class="p-4 bg-white rounded-lg shadow-md">
        <!-- Cabeçalho Principal -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 mb-4">
            <div class="flex items-center justify-between bg-gradient-to-r from-green-600 to-green-700 px-4 py-3">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-clock mr-2 animate-pulse"></i>
                    {{ __('messages.overtime_records') }}
                </h3>
                <div class="flex items-center space-x-2">
                    <button wire:click="create" class="inline-flex items-center px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-md transition-all duration-200 transform hover:scale-105">
                        <i class="fas fa-plus mr-2"></i>
                        {{ __('messages.add_overtime') }}
                    </button>
                </div>
            </div>

            
            <!-- Filtros -->
            <div class="p-4 bg-gray-50 border-b border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Campo de pesquisa -->
                    <div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" wire:model.live.debounce.300ms="filters.search" 
                                   placeholder="{{ __('messages.search_employee') }}" 
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200">
                        </div>
                    </div>
                    
                    <!-- Filtro de funcionário -->
                    <div>
                        <select wire:model.live="filters.employee_id" class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200">
                            <option value="">{{ __('messages.all_employees') }}</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Filtro de status -->
                    <div>
                        <select wire:model.live="filters.status" class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200">
                            <option value="">{{ __('messages.all_statuses') }}</option>
                            <option value="pending">{{ __('messages.pending') }}</option>
                            <option value="approved">{{ __('messages.approved') }}</option>
                            <option value="rejected">{{ __('messages.rejected') }}</option>
                        </select>
                    </div>
                    
                    <!-- Período -->
                    <div>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="date" wire:model.live="filters.date_from" 
                                   class="block w-full pl-3 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200">
                            <input type="date" wire:model.live="filters.date_to" 
                                   class="block w-full pl-3 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mensagens -->
        @if (session()->has('message'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-90"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-90"
                class="mb-4 bg-gradient-to-r from-green-50 to-green-100 border-l-4 border-green-500 p-4 rounded-r-lg shadow-md">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-600 mr-3 text-xl"></i>
                    <p class="text-green-800 font-medium">{{ session('message') }}</p>
                </div>
            </div>
        @endif

        <!-- Tabela Modernizada -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
                <h2 class="text-lg font-semibold text-white flex items-center">
                    <i class="fas fa-clock mr-2"></i>
                    {{ __('messages.overtime_records_list') }}
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
                                    <a href="#" wire:click.prevent="sortBy('date')" class="flex items-center hover:text-blue-600 transition-colors">
                                        {{ __('messages.date') }}
                                        @if($sortField === 'date')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-blue-600"></i>
                                        @endif
                                    </a>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-clock text-purple-600"></i>
                                    <span>{{ __('messages.hours') }}</span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-coins text-yellow-600"></i>
                                    <span>{{ __('messages.amount') }}</span>
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
                        @forelse($overtimeRecords as $record)
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center">
                                                <span class="text-white font-bold text-sm">
                                                    {{ strtoupper(substr($record->employee->full_name, 0, 2)) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $record->employee->full_name }}</div>
                                            <div class="text-sm text-gray-500">{{ $record->employee->employee_id ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar-day text-blue-500 mr-2"></i>
                                        <span class="text-sm text-gray-900">{{ $record->date->format('d/m/Y') }}</span>
                                    </div>
                                    @if($record->input_type === 'time_range' && $record->start_time && $record->end_time)
                                        <div class="text-xs text-gray-500 flex items-center mt-1">
                                            <i class="fas fa-clock text-gray-400 mr-1"></i>
                                            {{ $record->start_time }} - {{ $record->end_time }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <i class="fas fa-clock text-purple-500 mr-2"></i>
                                        <span class="text-sm font-semibold text-gray-900">{{ number_format($record->hours, 2) }} h</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <i class="fas fa-money-bill-wave text-green-500 mr-2"></i>
                                        <span class="text-sm font-semibold text-gray-900">{{ number_format($record->amount, 2) }} Kz</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($record->status === 'pending')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            {{ __('messages.pending') }}
                                        </span>
                                    @elseif($record->status === 'approved')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ __('messages.approved') }}
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            {{ __('messages.rejected') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-1">
                                        <!-- Visualizar -->
                                        <button wire:click="view({{ $record->id }})" 
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50 rounded-md transition-all duration-200"
                                                title="{{ __('messages.view') }}">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        <!-- Aprovar/Rejeitar (apenas se pending) -->
                                        @if($record->status === 'pending')
                                            <button wire:click="approve({{ $record->id }})" 
                                                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-600 hover:text-green-900 hover:bg-green-50 rounded-md transition-all duration-200"
                                                    title="{{ __('messages.approve') }}">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button wire:click="reject({{ $record->id }})" 
                                                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-600 hover:text-red-900 hover:bg-red-50 rounded-md transition-all duration-200"
                                                    title="{{ __('messages.reject') }}">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif

                                        <!-- Editar -->
                                        <button wire:click="edit({{ $record->id }})" 
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-yellow-600 hover:text-yellow-900 hover:bg-yellow-50 rounded-md transition-all duration-200"
                                                title="{{ __('messages.edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <!-- Excluir -->
                                        <button wire:click="confirmDelete({{ $record->id }})" 
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-600 hover:text-red-900 hover:bg-red-50 rounded-md transition-all duration-200"
                                                title="{{ __('messages.delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-inbox text-gray-300 text-5xl mb-3"></i>
                                        <p>{{ __('messages.no_records_found') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Paginação -->
        <div class="mt-4">
            {{ $overtimeRecords->links() }}
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <div x-data="{ open: @entangle('showDeleteModal') }" 
         x-show="open" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Overlay -->
            <div x-show="open" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                 aria-hidden="true"></div>

            <!-- Center modal -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Panel -->
            <div x-show="open" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                {{ __('messages.confirm_deletion') }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    {{ __('messages.confirm_delete_overtime_record') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" 
                            wire:click="delete" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        <i class="fas fa-trash mr-2"></i>
                        {{ __('messages.delete') }}
                    </button>
                    <button type="button" 
                            wire:click="$set('showDeleteModal', false)" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        {{ __('messages.cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modais -->
    @include('livewire.hr.overtime-records-complete')
</div>
