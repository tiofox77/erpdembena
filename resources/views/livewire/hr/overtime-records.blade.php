<div>
<div class="p-4">
    <!-- Cabeçalho da página -->
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold text-gray-900">{{ __('messages.overtime_records') }}</h1>
        <button wire:click="create" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors duration-200">
            <i class="fas fa-plus-circle mr-1"></i> {{ __('messages.add_overtime') }}
        </button>
    </div>
    
    <!-- Feedback de mensagens -->
    @if (session()->has('message'))
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
        class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
        <p>{{ session('message') }}</p>
    </div>
    @endif

    <!-- Filtros -->
    <div class="bg-white rounded-md shadow-sm p-4 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.search') }}</label>
                <input type="text" wire:model.live.debounce.300ms="filters.search" placeholder="{{ __('messages.search_employee') }}" 
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.employee') }}</label>
                <select wire:model.live="filters.employee_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">{{ __('messages.all_employees') }}</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.status') }}</label>
                <select wire:model.live="filters.status" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">{{ __('messages.all_statuses') }}</option>
                    <option value="pending">{{ __('messages.pending') }}</option>
                    <option value="approved">{{ __('messages.approved') }}</option>
                    <option value="rejected">{{ __('messages.rejected') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.date_range') }}</label>
                <div class="grid grid-cols-2 gap-2">
                    <input type="date" wire:model.live="filters.date_from" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <input type="date" wire:model.live="filters.date_to" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de registos -->
    <div class="bg-white rounded-md shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="#" wire:click.prevent="sortBy('employee_id')" class="flex items-center hover:text-blue-700 transition-colors duration-200">
                            <div class="flex items-center">
                                <i class="fas fa-user text-blue-500 mr-1"></i>
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
                        <a href="#" wire:click.prevent="sortBy('date')" class="flex items-center hover:text-blue-700 transition-colors duration-200">
                            <div class="flex items-center">
                                <i class="fas fa-calendar text-green-500 mr-1"></i>
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
                                <i class="fas fa-clock text-blue-500 mr-1"></i>
                                {{ __('messages.hours') }}
                            </div>
                        </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center">
                                <i class="fas fa-coins text-yellow-500 mr-1"></i>
                                {{ __('messages.amount') }}
                            </div>
                        </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center">
                                <i class="fas fa-tag text-indigo-500 mr-1"></i>
                                {{ __('messages.status') }}
                            </div>
                        </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('messages.actions') }}
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($overtimeRecords as $record)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900">{{ $record->employee->full_name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-gray-900">{{ $record->date->format('d/m/Y') }}</div>
                            @if($record->input_type === 'time_range' && $record->start_time && $record->end_time)
                                <div class="text-xs text-gray-500"><i class="fas fa-clock text-blue-500 mr-1"></i> {{ $record->start_time }} - {{ $record->end_time }}</div>
                            @else
                                <div class="text-xs text-gray-500">
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
                            <div class="text-gray-900 font-medium">{{ number_format($record->hours, 2) }} h</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-gray-900 font-medium">{{ number_format($record->amount, 2) }} Kz</div>
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
                            <button wire:click="view({{ $record->id }})" class="text-indigo-600 hover:text-indigo-900 mr-2">
                                <i class="fas fa-eye"></i>
                            </button>
                            
                            @if($record->status === 'pending')
                                <button wire:click="approve({{ $record->id }})" class="text-green-600 hover:text-green-900 mr-2">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button wire:click="reject({{ $record->id }})" class="text-red-600 hover:text-red-900 mr-2">
                                    <i class="fas fa-times"></i>
                                </button>
                            @endif
                            
                            <button wire:click="edit({{ $record->id }})" class="text-blue-600 hover:text-blue-900 mr-2">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button wire:click="confirmDelete({{ $record->id }})" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">{{ __('messages.no_records_found') }}</td>
                    </tr>
                @endforelse
            </tbody>
            
            <!-- Footer com resumo quando há filtros aplicados -->
            @if(!empty(array_filter($filters)) && $summary['total_records'] > 0)
            <tfoot class="bg-gray-100 border-t-2 border-gray-300">
                <tr>
                    <td colspan="6" class="px-6 py-4">
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fas fa-chart-bar text-blue-600 text-lg mr-2"></i>
                                    <h3 class="text-lg font-semibold text-gray-800">{{ __('messages.overtime_summary') }}</h3>
                                </div>
                                <div class="text-sm text-gray-600">
                                    <i class="fas fa-filter mr-1"></i>
                                    {{ $summary['total_records'] }} {{ __('messages.records_found') }}
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                                <!-- Total Geral -->
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
                                
                                <!-- Média por Registro -->
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
                            
                            <!-- Informações adicionais quando filtro de funcionário específico -->
                            @if(!empty($filters['employee_id']))
                                @php
                                    $selectedEmployee = $employees->firstWhere('id', $filters['employee_id']);
                                @endphp
                                <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                    <div class="flex items-center">
                                        <i class="fas fa-user text-blue-600 mr-2"></i>
                                        <span class="font-medium text-blue-800">
                                            {{ __('messages.showing_records_for') }}: {{ $selectedEmployee->full_name ?? __('messages.unknown_employee') }}
                                        </span>
                                    </div>
                                    @if(!empty($filters['date_from']) || !empty($filters['date_to']))
                                        <div class="flex items-center mt-2 text-sm text-blue-700">
                                            <i class="fas fa-calendar mr-2"></i>
                                            {{ __('messages.period') }}: 
                                            @if($filters['date_from'])
                                                {{ \Carbon\Carbon::parse($filters['date_from'])->format('d/m/Y') }}
                                            @else
                                                {{ __('messages.beginning') }}
                                            @endif
                                            {{ __('messages.to') }}
                                            @if($filters['date_to'])
                                                {{ \Carbon\Carbon::parse($filters['date_to'])->format('d/m/Y') }}
                                            @else
                                                {{ __('messages.today') }}
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
    
    <!-- Paginação -->
    <div class="mt-4">
        {{ $overtimeRecords->links() }}
    </div>
</div>

<!-- Modal de Adicionar/Editar Hora Extra -->
@include('livewire.hr.overtime-records-complete')

<!-- Modal de Visualização -->
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
                <!-- Cabeçalho com gradiente (verde para visualização) -->
                <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-eye mr-2 animate-pulse"></i>
                        {{ __('messages.overtime_details') }}
                    </h3>
                    <button type="button" wire:click="closeViewModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="p-6">
                    <!-- Seção principal: Detalhes do Funcionário e Horas Extra -->
                    <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden mb-6">
                        <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                            <i class="fas fa-user-clock text-blue-600 mr-2"></i>
                            <h3 class="text-base font-medium text-gray-700">{{ __('messages.employee_details') }}</h3>
                        </div>
                        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Detalhes do funcionário e data -->
                            <div class="bg-blue-50 p-3 rounded-lg">
                                <div class="flex items-center mb-3">
                                    <i class="fas fa-user text-blue-500 mr-2"></i>
                                    <h4 class="font-medium text-blue-800">{{ $employee_id ? $employees->firstWhere('id', $employee_id)->full_name : '-' }}</h4>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div>
                                        <span class="text-gray-600">{{ __('messages.employee_id') }}:</span>
                                        <span class="font-medium">{{ $employee_id ?: '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">{{ __('messages.date') }}:</span>
                                        <span class="font-medium">{{ $date ? date('d/m/Y', strtotime($date)) : '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">{{ __('messages.shift') }}:</span>
                                        <span class="font-medium">{{ $employee_shift_name ?: '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">{{ __('messages.period_type') }}:</span>
                                        <span class="font-medium">{{ $period_type ? ucfirst($period_type) : '-' }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Status e detalhes de aprovação -->
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <div class="flex items-center mb-3">
                                    <i class="fas fa-clipboard-check text-gray-500 mr-2"></i>
                                    <h4 class="font-medium text-gray-700">{{ __('messages.approval_details') }}</h4>
                                </div>
                                
                                <div class="mb-2">
                                    <span class="text-sm text-gray-600">{{ __('messages.status') }}:</span>
                                    @if($status === 'pending')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i> {{ __('messages.pending') }}
                                        </span>
                                    @elseif($status === 'approved')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i> {{ __('messages.approved') }}
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle mr-1"></i> {{ __('messages.rejected') }}
                                        </span>
                                    @endif
                                </div>
                                
                                <!-- Data de criação e atualização -->
                                <div class="grid grid-cols-1 gap-1 text-sm">
                                    <div>
                                        <span class="text-gray-600">{{ __('messages.created_at') }}:</span>
                                        <span class="font-medium">{{ isset($created_at) ? date('d/m/Y H:i', strtotime($created_at)) : '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">{{ __('messages.updated_at') }}:</span>
                                        <span class="font-medium">{{ isset($updated_at) ? date('d/m/Y H:i', strtotime($updated_at)) : '-' }}</span>
                                    </div>
                                    <!-- Aprovador, se aplicável -->
                                    @if($status === 'approved' || $status === 'rejected')
                                    <div class="mt-1">
                                        <span class="text-gray-600">{{ __('messages.approved_by') }}:</span>
                                        <span class="font-medium">{{ isset($approver_name) ? $approver_name : '-' }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Seção: Detalhes do Trabalho e Cálculos -->
                    <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden mb-6">
                        <div class="flex items-center bg-gradient-to-r from-green-50 to-green-100 px-4 py-3 border-b border-gray-200">
                            <i class="fas fa-calculator text-green-600 mr-2"></i>
                            <h3 class="text-base font-medium text-gray-700">{{ __('messages.work_details_and_calculation') }}</h3>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Detalhes de tempo e tipo de entrada -->
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <h4 class="font-medium text-gray-700 mb-3">{{ __('messages.time_details') }}</h4>
                                    <div class="grid grid-cols-2 gap-2 text-sm">
                                        <div>
                                            <span class="text-gray-600">{{ __('messages.input_type') }}:</span>
                                            <span class="font-medium">{{ $input_type ? ucfirst($input_type) : '-' }}</span>
                                        </div>
                                        @if($input_type === 'time_range')
                                        <div>
                                            <span class="text-gray-600">{{ __('messages.time_period') }}:</span>
                                            <span class="font-medium">{{ $start_time ?: '--:--' }} - {{ $end_time ?: '--:--' }}</span>
                                        </div>
                                        @elseif($input_type === 'daily')
                                        <div>
                                            <span class="text-gray-600">{{ __('messages.daily_hours') }}:</span>
                                            <span class="font-medium">{{ $direct_hours ? number_format($direct_hours, 2) : '-' }}</span>
                                        </div>
                                        @elseif($input_type === 'monthly')
                                        <div>
                                            <span class="text-gray-600">{{ __('messages.monthly_hours') }}:</span>
                                            <span class="font-medium">{{ $direct_hours ? number_format($direct_hours, 2) : '-' }}</span>
                                        </div>
                                        @endif
                                        <div>
                                            <span class="text-gray-600">{{ __('messages.total_hours') }}:</span>
                                            <span class="font-medium">{{ $hours ? number_format($hours, 2) : '-' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">{{ __('messages.is_night_shift') }}:</span>
                                            <span class="font-medium">
                                                @if(isset($is_night_shift) && $is_night_shift)
                                                    <span class="text-purple-700"><i class="fas fa-moon"></i> {{ __('messages.yes') }}</span>
                                                @else
                                                    <span class="text-gray-500"><i class="fas fa-sun"></i> {{ __('messages.no') }}</span>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Detalhes financeiros -->
                                <div class="bg-green-50 p-3 rounded-lg">
                                    <h4 class="font-medium text-green-800 mb-3">{{ __('messages.financial_details') }}</h4>
                                    <div class="grid grid-cols-2 gap-2 text-sm">
                                        <div>
                                            <span class="text-gray-600">{{ __('messages.base_hourly_rate') }}:</span>
                                            <span class="font-medium">{{ $hourly_rate ? number_format($hourly_rate, 2) . ' Kz' : '-' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">{{ __('messages.overtime_rate') }}:</span>
                                            <span class="font-medium">{{ $rate ? number_format($rate, 2) . ' Kz' : '-' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">{{ __('messages.weekend_multiplier') }}:</span>
                                            <span class="font-medium">{{ isset($weekend_multiplier) ? number_format($weekend_multiplier, 2) . 'x' : '-' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">{{ __('messages.night_multiplier') }}:</span>
                                            <span class="font-medium">{{ isset($night_shift_multiplier) ? number_format($night_shift_multiplier, 2) . 'x' : '-' }}</span>
                                        </div>
                                        <div class="col-span-2">
                                            <span class="text-gray-600">{{ __('messages.total_amount') }}:</span>
                                            <span class="font-medium text-green-700 text-lg">{{ $amount ? number_format($amount, 2) . ' Kz' : '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Descrição -->
                            <div class="mt-4 bg-blue-50 p-3 rounded-lg">
                                <h4 class="font-medium text-blue-700 mb-2">{{ __('messages.description') }}</h4>
                                <p class="text-gray-700">{{ $description ?: __('messages.no_description_provided') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-between space-x-3 border-t border-gray-200">
                    <!-- Botão para editar se estiver pendente -->
                    @if($status === 'pending')
                    <button type="button" wire:click="edit({{ $overtime_id }})" class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <i class="fas fa-edit mr-2"></i>
                        {{ __('messages.edit') }}
                    </button>
                    @else
                    <div></div> <!-- Espaçador para manter o layout -->
                    @endif
                    
                    <button type="button" wire:click="closeViewModal" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <i class="fas fa-times mr-2"></i>
                        {{ __('messages.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmação de exclusão -->
<div x-data="{ show: @entangle('showDeleteModal') }" x-show="show" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;
        
        <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            {{ __('messages.confirm_delete') }}
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                {{ __('messages.delete_overtime_confirmation') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" wire:click="delete" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                    {{ __('messages.delete') }}
                </button>
                <button type="button" wire:click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    {{ __('messages.cancel') }}
                </button>
            </div>
        </div>
    </div>
</div>
