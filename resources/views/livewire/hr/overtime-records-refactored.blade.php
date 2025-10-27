<div>
    <div class="p-4 sm:p-6 lg:p-8">
        <!-- Cabeçalho -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-clock mr-3 text-blue-600"></i>
                    {{ __('messages.overtime_records') }}
                </h1>
                <p class="mt-1 text-sm text-gray-600">{{ __('messages.manage_overtime_records') }}</p>
            </div>
            <button wire:click="create" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 border border-transparent rounded-md font-semibold text-white hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 shadow-lg hover:shadow-xl">
                <i class="fas fa-plus-circle mr-2"></i>
                {{ __('messages.add_overtime') }}
            </button>
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
                class="mb-6 bg-gradient-to-r from-green-50 to-green-100 border-l-4 border-green-500 p-4 rounded-r-lg shadow-md">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-600 mr-3 text-xl"></i>
                    <p class="text-green-800 font-medium">{{ session('message') }}</p>
                </div>
            </div>
        @endif

        <!-- Filtros -->
        <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-6 border border-gray-200">
            <div class="flex items-center mb-4">
                <i class="fas fa-filter text-blue-600 mr-2"></i>
                <h2 class="text-lg font-semibold text-gray-800">{{ __('messages.filters') }}</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Pesquisa -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-search text-gray-400 mr-1"></i>
                        {{ __('messages.search') }}
                    </label>
                    <input type="text" wire:model.live.debounce.300ms="filters.search" 
                        placeholder="{{ __('messages.search_employee') }}" 
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white">
                </div>

                <!-- Funcionário -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-user text-gray-400 mr-1"></i>
                        {{ __('messages.employee') }}
                    </label>
                    <select wire:model.live="filters.employee_id" 
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white">
                        <option value="">{{ __('messages.all_employees') }}</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-tag text-gray-400 mr-1"></i>
                        {{ __('messages.status') }}
                    </label>
                    <select wire:model.live="filters.status" 
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white">
                        <option value="">{{ __('messages.all_statuses') }}</option>
                        <option value="pending">{{ __('messages.pending') }}</option>
                        <option value="approved">{{ __('messages.approved') }}</option>
                        <option value="rejected">{{ __('messages.rejected') }}</option>
                    </select>
                </div>

                <!-- Período -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-calendar-alt text-gray-400 mr-1"></i>
                        {{ __('messages.date_range') }}
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="date" wire:model.live="filters.date_from" 
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white">
                        <input type="date" wire:model.live="filters.date_to" 
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white">
                    </div>
                </div>
            </div>

            <!-- Limpar filtros -->
            @if(array_filter($filters))
                <div class="mt-4 flex items-center justify-between bg-blue-50 p-3 rounded-md border border-blue-200">
                    <span class="text-sm text-blue-800 flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        {{ __('messages.filters_applied') }}
                    </span>
                    <button wire:click="resetFilters" 
                        class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center transition-colors duration-200">
                        <i class="fas fa-times-circle mr-1"></i>
                        {{ __('messages.clear_filters') }}
                    </button>
                </div>
            @endif
        </div>

        <!-- Tabela -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="#" wire:click.prevent="sortBy('employee_id')" class="flex items-center hover:text-blue-700 transition-colors duration-200 group">
                                    <div class="flex items-center">
                                        <i class="fas fa-user text-blue-500 mr-2 group-hover:scale-110 transition-transform duration-200"></i>
                                        {{ __('messages.employee') }}
                                        @if($sortField === 'employee_id')
                                            <span class="ml-1">
                                                @if($sortDirection === 'asc')
                                                    <i class="fas fa-sort-up text-blue-600"></i>
                                                @else
                                                    <i class="fas fa-sort-down text-blue-600"></i>
                                                @endif
                                            </span>
                                        @endif
                                    </div>
                                </a>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="#" wire:click.prevent="sortBy('date')" class="flex items-center hover:text-blue-700 transition-colors duration-200 group">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar text-green-500 mr-2 group-hover:scale-110 transition-transform duration-200"></i>
                                        {{ __('messages.date') }}
                                        @if($sortField === 'date')
                                            <span class="ml-1">
                                                @if($sortDirection === 'asc')
                                                    <i class="fas fa-sort-up text-blue-600"></i>
                                                @else
                                                    <i class="fas fa-sort-down text-blue-600"></i>
                                                @endif
                                            </span>
                                        @endif
                                    </div>
                                </a>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-clock text-blue-500 mr-2"></i>
                                    {{ __('messages.hours') }}
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-coins text-yellow-500 mr-2"></i>
                                    {{ __('messages.amount') }}
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-tag text-indigo-500 mr-2"></i>
                                    {{ __('messages.status') }}
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <i class="fas fa-cog text-gray-400 mr-1"></i>
                                {{ __('messages.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($overtimeRecords as $record)
                            <tr class="hover:bg-blue-50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                                            <span class="text-white font-bold">{{ substr($record->employee->full_name, 0, 1) }}</span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $record->employee->full_name }}</div>
                                            <div class="text-sm text-gray-500">ID: {{ $record->employee->employee_id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 font-medium">{{ $record->date->format('d/m/Y') }}</div>
                                    @if($record->input_type === 'time_range' && $record->start_time && $record->end_time)
                                        <div class="text-xs text-gray-500 flex items-center">
                                            <i class="fas fa-clock text-blue-500 mr-1"></i>
                                            {{ $record->start_time }} - {{ $record->end_time }}
                                        </div>
                                    @else
                                        <div class="text-xs text-gray-500 flex items-center">
                                            @if($record->period_type === 'day')
                                                <i class="fas fa-calendar-day text-green-500 mr-1"></i>
                                            @else
                                                <i class="fas fa-calendar-alt text-purple-500 mr-1"></i>
                                            @endif
                                            {{ number_format($record->hours, 2) }} h
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900 flex items-center">
                                        <i class="fas fa-stopwatch text-blue-500 mr-2"></i>
                                        {{ number_format($record->hours, 2) }} h
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-green-600 flex items-center">
                                        <i class="fas fa-money-bill-wave text-green-500 mr-2"></i>
                                        {{ number_format($record->amount, 2) }} Kz
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($record->status === 'pending')
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 border border-yellow-300">
                                            <i class="fas fa-clock mr-1"></i>
                                            {{ __('messages.pending') }}
                                        </span>
                                    @elseif($record->status === 'approved')
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 border border-green-300">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            {{ __('messages.approved') }}
                                        </span>
                                    @else
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 border border-red-300">
                                            <i class="fas fa-times-circle mr-1"></i>
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

                                        <!-- Aprovar (apenas se pending) -->
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
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
                                        <h3 class="text-lg font-medium text-gray-900 mb-1">{{ __('messages.no_records_found') }}</h3>
                                        <p class="text-sm text-gray-500">{{ __('messages.no_overtime_records_description') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    <!-- Footer com resumo -->
                    @if(!empty(array_filter($filters)) && $summary['total_records'] > 0)
                        <tfoot class="bg-gradient-to-r from-blue-50 to-indigo-50 border-t-2 border-blue-200">
                            <tr>
                                <td colspan="6" class="px-6 py-4">
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <!-- Total Horas -->
                                        <div class="bg-white rounded-lg p-3 shadow-sm border-l-4 border-blue-500">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-600">{{ __('messages.total_hours') }}</p>
                                                    <p class="text-xl font-bold text-blue-600">{{ number_format($summary['total_hours'], 2) }}h</p>
                                                </div>
                                                <div class="text-blue-500">
                                                    <i class="fas fa-clock text-2xl"></i>
                                                </div>
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ number_format($summary['total_amount'], 2) }} Kz
                                            </div>
                                        </div>

                                        <!-- Aprovadas -->
                                        <div class="bg-white rounded-lg p-3 shadow-sm border-l-4 border-green-500">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-600">{{ __('messages.approved_hours') }}</p>
                                                    <p class="text-xl font-bold text-green-600">{{ number_format($summary['approved_hours'], 2) }}h</p>
                                                </div>
                                                <div class="text-green-500">
                                                    <i class="fas fa-check-circle text-2xl"></i>
                                                </div>
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ number_format($summary['approved_amount'], 2) }} Kz
                                            </div>
                                        </div>

                                        <!-- Pendentes -->
                                        <div class="bg-white rounded-lg p-3 shadow-sm border-l-4 border-yellow-500">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-600">{{ __('messages.pending_hours') }}</p>
                                                    <p class="text-xl font-bold text-yellow-600">{{ number_format($summary['pending_hours'], 2) }}h</p>
                                                </div>
                                                <div class="text-yellow-500">
                                                    <i class="fas fa-hourglass-half text-2xl"></i>
                                                </div>
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ number_format($summary['pending_amount'], 2) }} Kz
                                            </div>
                                        </div>

                                        <!-- Média -->
                                        <div class="bg-white rounded-lg p-3 shadow-sm border-l-4 border-purple-500">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-600">{{ __('messages.average_hours') }}</p>
                                                    <p class="text-xl font-bold text-purple-600">
                                                        {{ number_format($summary['total_records'] > 0 ? $summary['total_hours'] / $summary['total_records'] : 0, 2) }}h
                                                    </p>
                                                </div>
                                                <div class="text-purple-500">
                                                    <i class="fas fa-chart-line text-2xl"></i>
                                                </div>
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ __('messages.per_record') }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <!-- Paginação -->
        <div class="mt-6">
            {{ $overtimeRecords->links() }}
        </div>
    </div>

    <!-- Modais -->
    @include('livewire.hr.overtime-records-complete')
</div>
