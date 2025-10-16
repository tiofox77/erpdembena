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
                            <input type="text" wire:model.debounce.300ms="filters.search" 
                                   placeholder="{{ __('messages.search_employee') }}" 
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200">
                        </div>
                    </div>
                    
                    <!-- Filtro de funcionário -->
                    <div>
                        <select wire:model="filters.employee_id" class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200">
                            <option value="">{{ __('messages.all_employees') }}</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Filtro de status -->
                    <div>
                        <select wire:model="filters.status" class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200">
                            <option value="">{{ __('messages.all_statuses') }}</option>
                            <option value="pending">{{ __('messages.pending') }}</option>
                            <option value="approved">{{ __('messages.approved') }}</option>
                            <option value="rejected">{{ __('messages.rejected') }}</option>
                        </select>
                    </div>
                    
                    <!-- Período -->
                    <div>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="date" wire:model="filters.date_from" 
                                   class="block w-full pl-3 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200">
                            <input type="date" wire:model="filters.date_to" 
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

    <!-- Modais -->
    @include('livewire.hr.overtime-records-complete')
</div>
