<div>
    <div class="container mx-auto px-4 py-6">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
            <h1 class="text-2xl font-semibold text-gray-800 flex items-center">
                <i class="fas fa-exchange-alt text-blue-600 mr-3"></i>
                {{ __('messages.warehouse_transfers_management') }}
            </h1>
            <button wire:click="openCreateModal" 
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 hover:shadow-lg">
                <i class="fas fa-plus-circle mr-2 animate-pulse"></i>
                {{ __('messages.create_transfer') }}
            </button>
        </div>

        <!-- Cartão de Busca e Filtros -->
        <div class="mb-6 bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 ease-in-out hover:shadow-lg transform hover:scale-[1.01]">
            <!-- Cabeçalho do cartão com gradiente -->
            <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                <i class="fas fa-filter text-blue-600 mr-2"></i>
                <h2 class="text-base font-medium text-gray-700">{{ __('messages.filters_and_search') }}</h2>
            </div>
            <!-- Conteúdo do cartão -->
            <div class="p-4">
                <div class="flex flex-col gap-4">
                    <!-- Cabeçalho com campo de busca e botão de resetar -->
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-grow mr-2">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-search text-gray-500 mr-1"></i>
                                {{ __('messages.search') }}
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input wire:model.live.debounce.300ms="search" id="search" 
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out" 
                                    placeholder="{{ __('messages.search_transfers') }}" 
                                    type="search">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">{{ __('messages.search_by_request_number_or_location') }}</p>
                        </div>
                        <button wire:click="resetFilters" 
                            class="mt-6 flex items-center px-3 py-2 border border-blue-300 text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-105">
                            <i class="fas fa-undo mr-1"></i>
                            {{ __('messages.reset_filters') }}
                        </button>
                    </div>
                    
                    <!-- Primeira linha de filtros -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Filtro de Status -->
                        <div>
                            <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-tag text-gray-500 mr-1"></i>
                                {{ __('messages.status') }}
                            </label>
                            <select wire:model.live="statusFilter" id="statusFilter" 
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md bg-white">
                                <option value="">{{ __('messages.all_statuses') }}</option>
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Filtro de Prioridade -->
                        <div>
                            <label for="priorityFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-flag text-gray-500 mr-1"></i>
                                {{ __('messages.priority') }}
                            </label>
                            <select wire:model.live="priorityFilter" id="priorityFilter" 
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md bg-white">
                                <option value="">{{ __('messages.all_priorities') }}</option>
                                @foreach ($priorityOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Intervalo de Data -->
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label for="dateFrom" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-calendar-alt text-gray-500 mr-1"></i>
                                    {{ __('messages.date_from') }}
                                </label>
                                <input type="date" wire:model.live="dateFrom" id="dateFrom" 
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md bg-white">
                            </div>
                            <div>
                                <label for="dateTo" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-calendar-alt text-gray-500 mr-1"></i>
                                    {{ __('messages.date_to') }}
                                </label>
                                <input type="date" wire:model.live="dateTo" id="dateTo" 
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md bg-white">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela de Pedidos de Transferência -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                <div class="flex items-center">
                    <i class="fas fa-list text-blue-600 mr-2"></i>
                    <h2 class="text-base font-medium text-gray-700">{{ __('messages.warehouse_transfer_requests') }}</h2>
                </div>
                <div class="flex items-center text-sm text-gray-500">
                    <span wire:loading wire:target="search, statusFilter, priorityFilter, dateFrom, dateTo, sortField, sortDirection" class="mr-2">
                        <i class="fas fa-spinner fa-spin"></i>
                        {{ __('messages.loading') }}...
                    </span>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('request_number')">
                                <div class="flex items-center">
                                    {{ __('messages.request_number') }}
                                    @if ($sortField === 'request_number')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('source_location_id')">
                                <div class="flex items-center">
                                    {{ __('messages.source_location') }}
                                    @if ($sortField === 'source_location_id')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('destination_location_id')">
                                <div class="flex items-center">
                                    {{ __('messages.destination_location') }}
                                    @if ($sortField === 'destination_location_id')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('status')">
                                <div class="flex items-center">
                                    {{ __('messages.status') }}
                                    @if ($sortField === 'status')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('priority')">
                                <div class="flex items-center">
                                    {{ __('messages.priority') }}
                                    @if ($sortField === 'priority')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('requested_date')">
                                <div class="flex items-center">
                                    {{ __('messages.requested_date') }}
                                    @if ($sortField === 'requested_date')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('messages.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($transferRequests as $transferRequest)
                            <tr class="hover:bg-gray-50 transition-all duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $transferRequest->request_number }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $transferRequest->sourceLocation->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $transferRequest->destinationLocation->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $transferRequest->getStatusColorClass() }}">
                                        <i class="fas {{ $transferRequest->getStatusIcon() }} mr-1"></i>
                                        {{ $statusOptions[$transferRequest->status] ?? $transferRequest->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $transferRequest->getPriorityColorClass() }}">
                                        <i class="fas {{ $transferRequest->getPriorityIcon() }} mr-1"></i>
                                        {{ $priorityOptions[$transferRequest->priority] ?? $transferRequest->priority }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $transferRequest->requested_date->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <!-- Botão de Visualizar -->
                                        <button wire:click="viewTransferRequest({{ $transferRequest->id }})" class="text-blue-600 hover:text-blue-900 transition-all duration-200 hover:scale-110">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <!-- Botão de Editar (disponível apenas se estiver em rascunho ou rejeitado) -->
                                        @if ($transferRequest->isEditable())
                                            <button wire:click="editTransferRequest({{ $transferRequest->id }})" class="text-yellow-600 hover:text-yellow-900 transition-all duration-200 hover:scale-110">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        @endif
                                        
                                        <!-- Botão de Aprovar (disponível apenas se estiver pendente e usuário tiver permissão) -->
                                        @if ($transferRequest->canBeApproved())
                                            <button wire:click="openApprovalModal({{ $transferRequest->id }})" class="text-green-600 hover:text-green-900 transition-all duration-200 hover:scale-110">
                                                <i class="fas fa-check-circle"></i>
                                            </button>
                                        @endif
                                        
                                        <!-- Botão de Excluir (disponível apenas se não estiver aprovado/em progresso/completo) -->
                                        @if ($transferRequest->isEditable())
                                            <button wire:click="confirmDeleteTransferRequest({{ $transferRequest->id }})" class="text-red-600 hover:text-red-900 transition-all duration-200 hover:scale-110">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    <div class="flex flex-col items-center justify-center py-8">
                                        <i class="fas fa-exchange-alt text-gray-300 text-5xl mb-4"></i>
                                        <p class="text-gray-500 text-lg">{{ __('messages.no_transfer_requests_found') }}</p>
                                        <p class="text-gray-400 text-sm mt-1">{{ __('messages.try_different_filters') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <div class="px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $transferRequests->links() }}
            </div>
        </div>
    </div>

    <!-- Include all modals -->
    @include('livewire.supply-chain.warehouse-transfers-modals')
</div>
