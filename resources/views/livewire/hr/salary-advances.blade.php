<div>
    <div class="p-4">
        <!-- Cabeçalho da página -->
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-semibold text-gray-900">{{ __('messages.salary_advances') }}</h1>
            <button wire:click="create" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors duration-200 transform hover:scale-105">
                <i class="fas fa-plus-circle mr-1"></i> {{ __('messages.add_advance') }}
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
        <div class="bg-white rounded-md shadow-sm p-4 mb-4 border border-gray-200">
            <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 mb-4 border-b border-gray-200 rounded-t-lg">
                <i class="fas fa-filter text-blue-600 mr-2"></i>
                <h2 class="text-base font-medium text-gray-700">{{ __('messages.filters') }}</h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.search') }}</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" wire:model.debounce.300ms="filters.search" 
                            placeholder="{{ __('messages.search_employee') }}" 
                            class="pl-10 w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.employee') }}</label>
                    <select wire:model="filters.employee_id" 
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                        <option value="">{{ __('messages.all_employees') }}</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.status') }}</label>
                    <select wire:model="filters.status" 
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                        <option value="">{{ __('messages.all_statuses') }}</option>
                        <option value="pending">{{ __('messages.pending') }}</option>
                        <option value="approved">{{ __('messages.approved') }}</option>
                        <option value="rejected">{{ __('messages.rejected') }}</option>
                        <option value="completed">{{ __('messages.completed') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.date_range') }}</label>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-calendar-alt text-gray-400"></i>
                            </div>
                            <input type="date" wire:model="filters.date_from" 
                                class="pl-10 w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-calendar-alt text-gray-400"></i>
                            </div>
                            <input type="date" wire:model="filters.date_to" 
                                class="pl-10 w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela de registos -->
        <div class="bg-white rounded-md shadow-sm overflow-hidden border border-gray-200">
            <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                <i class="fas fa-money-bill-wave text-blue-600 mr-2"></i>
                <h2 class="text-base font-medium text-gray-700">{{ __('messages.salary_advances_list') }}</h2>
            </div>
            
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="#" wire:click.prevent="sortBy('employee_id')" class="flex items-center">
                                {{ __('messages.employee') }}
                                @if($sortField === 'employee_id')
                                    <span class="ml-1">
                                        @if($sortDirection === 'asc')
                                            <i class="fas fa-sort-up"></i>
                                        @else
                                            <i class="fas fa-sort-down"></i>
                                        @endif
                                    </span>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="#" wire:click.prevent="sortBy('request_date')" class="flex items-center">
                                {{ __('messages.date') }}
                                @if($sortField === 'request_date')
                                    <span class="ml-1">
                                        @if($sortDirection === 'asc')
                                            <i class="fas fa-sort-up"></i>
                                        @else
                                            <i class="fas fa-sort-down"></i>
                                        @endif
                                    </span>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="#" wire:click.prevent="sortBy('amount')" class="flex items-center">
                                {{ __('messages.amount') }}
                                @if($sortField === 'amount')
                                    <span class="ml-1">
                                        @if($sortDirection === 'asc')
                                            <i class="fas fa-sort-up"></i>
                                        @else
                                            <i class="fas fa-sort-down"></i>
                                        @endif
                                    </span>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('messages.installments') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('messages.progress') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('messages.status') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('messages.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($salaryAdvances as $advance)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $advance->employee->full_name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-gray-900">{{ $advance->request_date->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-gray-900">{{ number_format($advance->amount, 2) }} Kz</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-gray-900">
                                    {{ $advance->installments - $advance->remaining_installments }}/{{ $advance->installments }}
                                    <span class="text-xs text-gray-500">({{ number_format($advance->installment_amount, 2) }} Kz)</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $progressPercentage = $advance->installments > 0 
                                        ? (($advance->installments - $advance->remaining_installments) / $advance->installments) * 100 
                                        : 0;
                                @endphp
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $progressPercentage }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500">{{ number_format($progressPercentage, 0) }}%</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($advance->status === 'pending')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        {{ __('messages.pending') }}
                                    </span>
                                @elseif($advance->status === 'approved')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ __('messages.approved') }}
                                    </span>
                                @elseif($advance->status === 'completed')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ __('messages.completed') }}
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        {{ __('messages.rejected') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="view({{ $advance->id }})" class="text-indigo-600 hover:text-indigo-900 mr-2 transition-all duration-200 transform hover:scale-110">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                @if($advance->status === 'pending')
                                    <button wire:click="approve({{ $advance->id }})" class="text-green-600 hover:text-green-900 mr-2 transition-all duration-200 transform hover:scale-110">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button wire:click="reject({{ $advance->id }})" class="text-red-600 hover:text-red-900 mr-2 transition-all duration-200 transform hover:scale-110">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                                
                                @if($advance->status === 'approved' && $advance->remaining_installments > 0)
                                    <button wire:click="registerPaymentModal({{ $advance->id }})" class="text-blue-600 hover:text-blue-900 mr-2 transition-all duration-200 transform hover:scale-110">
                                        <i class="fas fa-hand-holding-usd"></i>
                                    </button>
                                @endif
                                
                                @if($advance->status !== 'completed')
                                    <button wire:click="edit({{ $advance->id }})" class="text-blue-600 hover:text-blue-900 mr-2 transition-all duration-200 transform hover:scale-110">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button wire:click="confirmDelete({{ $advance->id }})" class="text-red-600 hover:text-red-900 transition-all duration-200 transform hover:scale-110">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                <i class="fas fa-search mr-1"></i>
                                {{ __('messages.no_records_found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Paginação -->
        <div class="mt-4">
            {{ $salaryAdvances->links() }}
        </div>
    </div>

    <!-- Incluir todas as modais -->
    @include('livewire.hr.salary-advances-form-modal')
    @include('livewire.hr.salary-advances-view-modal')
    @include('livewire.hr.salary-advances-payment-modal')
    @include('livewire.hr.salary-advances-delete-modal')
</div>
