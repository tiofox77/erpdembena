<div>
    <div class="container mx-auto px-4 py-6">
        <!-- Cabeçalho Principal -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
            <h1 class="text-2xl font-semibold text-gray-800 flex items-center">
                <i class="fas fa-boxes text-blue-600 mr-3"></i>
                {{ __('messages.bom_management') }}
            </h1>
            <div class="flex space-x-2">
                <button wire:click="create" 
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 hover:shadow-lg">
                    <i class="fas fa-plus-circle mr-2 animate-pulse"></i>
                    {{ __('messages.new_bom') }}
                </button>
            </div>
        </div>

        <!-- Tabs para navegação entre lista de BOMs e componentes -->
        <div class="mb-4 border-b border-gray-200">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                <li class="mr-2" role="presentation">
                    <button wire:click="setTab('header')" class="inline-block p-4 rounded-t-lg 
                        {{ $currentTab === 'header' ? 'border-b-2 border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-600 hover:border-gray-300' }}">
                        <i class="fas fa-list-ul mr-2"></i>
                        {{ __('messages.boms_list') }}
                    </button>
                </li>
                <li class="mr-2" role="presentation">
                    <button wire:click="setTab('components')" class="inline-block p-4 rounded-t-lg 
                        {{ $currentTab === 'components' ? 'border-b-2 border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-600 hover:border-gray-300' }}
                        {{ !$bomHeaderId ? 'opacity-50 cursor-not-allowed' : '' }}"
                        {{ !$bomHeaderId ? 'disabled' : '' }}>
                        <i class="fas fa-puzzle-piece mr-2"></i>
                        {{ __('messages.components') }}
                        @if($bomHeaderId)
                            <span class="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full">
                                {{ optional(App\Models\Mrp\BomHeader::find($bomHeaderId))->bom_number }}
                            </span>
                        @endif
                    </button>
                </li>
            </ul>
        </div>

        @if($currentTab === 'header')
            <!-- Cartão de Busca e Filtros para BOMs -->
            <div class="mb-6 bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 ease-in-out hover:shadow-lg transform hover:scale-[1.01]">
                <!-- Cabeçalho do cartão com gradiente -->
                <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                    <i class="fas fa-filter text-blue-600 mr-2"></i>
                    <h2 class="text-base font-medium text-gray-700">{{ __('messages.filters_and_search') }}</h2>
                </div>
                <!-- Conteúdo do cartão -->
                <div class="p-4">
                    <div class="flex flex-col gap-4">
                        <!-- Campo de busca -->
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-search text-gray-500 mr-1"></i>
                                {{ __('messages.search') }}
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input wire:model.debounce.300ms="search" id="search" 
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out" 
                                    placeholder="{{ __('messages.search_bom_placeholder') }}" 
                                    type="search">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">{{ __('messages.search_bom_help') }}</p>
                        </div>
                        
                        <!-- Linha de filtros -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Filtro de Status -->
                            <div>
                                <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-tag text-gray-500 mr-1"></i>
                                    {{ __('messages.status') }}
                                </label>
                                <select wire:model="statusFilter" id="statusFilter" 
                                    class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                    <option value="">{{ __('messages.all_statuses') }}</option>
                                    <option value="draft">{{ __('messages.status_draft') }}</option>
                                    <option value="active">{{ __('messages.status_active') }}</option>
                                    <option value="obsolete">{{ __('messages.status_obsolete') }}</option>
                                </select>
                            </div>
                            
                            <!-- Filtro de Produto -->
                            <div>
                                <label for="productFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-box text-gray-500 mr-1"></i>
                                    {{ __('messages.product') }}
                                </label>
                                <select wire:model="productFilter" id="productFilter" 
                                    class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                    <option value="">{{ __('messages.all_products') }}</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Registros por página -->
                            <div>
                                <label for="perPage" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-list-ol text-gray-500 mr-1"></i>
                                    {{ __('messages.items_per_page') }}
                                </label>
                                <select wire:model="perPage" id="perPage" 
                                    class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Botão de reset -->
                        <div class="flex justify-end">
                            <button wire:click="resetFilters" 
                                class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md text-sm font-medium text-gray-700 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                <i class="fas fa-redo-alt mr-2"></i>
                                {{ __('messages.reset_filters') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabela de BOMs -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden transition-all duration-300 ease-in-out hover:shadow-lg">
                <!-- Cabeçalho da Tabela -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
                    <h2 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-list-alt mr-2"></i>
                        {{ __('messages.boms_list') }}
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center cursor-pointer" wire:click="sortBy('bom_number')">
                                        {{ __('messages.bom_number') }}
                                        @if($sortField === 'bom_number')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                        @else
                                            <i class="fas fa-sort ml-1 opacity-50"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center cursor-pointer" wire:click="sortBy('product_id')">
                                        {{ __('messages.product') }}
                                        @if($sortField === 'product_id')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                        @else
                                            <i class="fas fa-sort ml-1 opacity-50"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center cursor-pointer" wire:click="sortBy('version')">
                                        {{ __('messages.version') }}
                                        @if($sortField === 'version')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                        @else
                                            <i class="fas fa-sort ml-1 opacity-50"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center cursor-pointer" wire:click="sortBy('effective_date')">
                                        {{ __('messages.effective_date') }}
                                        @if($sortField === 'effective_date')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                        @else
                                            <i class="fas fa-sort ml-1 opacity-50"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center cursor-pointer" wire:click="sortBy('status')">
                                        {{ __('messages.status') }}
                                        @if($sortField === 'status')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                        @else
                                            <i class="fas fa-sort ml-1 opacity-50"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('messages.actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($bomHeaders as $bom)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $bom->bom_number }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $bom->product->name ?? 'N/A' }}</div>
                                        <div class="text-xs text-gray-500">{{ $bom->product->sku ?? '' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        V{{ $bom->version }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">Efetiva: {{ optional($bom->effective_date)->format('d/m/Y') }}</div>
                                        @if($bom->expiration_date)
                                            <div class="text-xs text-gray-500">Validade: {{ $bom->expiration_date->format('d/m/Y') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($bom->status === 'draft') bg-gray-100 text-gray-800
                                            @elseif($bom->status === 'active') bg-green-100 text-green-800
                                            @elseif($bom->status === 'obsolete') bg-red-100 text-red-800
                                            @endif">
                                            <i class="mr-1
                                                @if($bom->status === 'draft') fas fa-pencil-alt
                                                @elseif($bom->status === 'active') fas fa-check-circle
                                                @elseif($bom->status === 'obsolete') fas fa-ban
                                                @endif"></i>
                                            @if($bom->status === 'draft') {{ __('messages.status_draft') }}
                                            @elseif($bom->status === 'active') {{ __('messages.status_active') }}
                                            @elseif($bom->status === 'obsolete') {{ __('messages.status_obsolete') }}
                                            @else {{ $bom->status }}
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-2">
                                            <button wire:click="viewBomDetails({{ $bom->id }})" 
                                                class="text-green-600 hover:text-green-900 transition-colors duration-150 transform hover:scale-110" title="{{ __('messages.view_details') }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button wire:click="viewComponents({{ $bom->id }})" 
                                                class="text-blue-600 hover:text-blue-900 transition-colors duration-150 transform hover:scale-110" title="{{ __('messages.view_components') }}">
                                                <i class="fas fa-puzzle-piece"></i>
                                            </button>
                                            <button wire:click="edit({{ $bom->id }})" 
                                                class="text-indigo-600 hover:text-indigo-900 transition-colors duration-150 transform hover:scale-110" title="{{ __('messages.edit_bom') }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button wire:click="confirmDelete({{ $bom->id }})" 
                                                class="text-red-600 hover:text-red-900 transition-colors duration-150 transform hover:scale-110" title="{{ __('messages.delete_bom') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col items-center justify-center py-6 space-y-2">
                                            <div class="flex-shrink-0 bg-gray-100 p-3 rounded-full">
                                                <i class="fas fa-boxes text-gray-400 text-2xl"></i>
                                            </div>
                                            <p class="text-gray-500 text-sm">{{ __('messages.no_boms_found') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginação -->
                <div class="px-4 py-3 bg-white border-t border-gray-200">
                    {{ $bomHeaders->links() }}
                </div>
            </div>
        @elseif($currentTab === 'components' && $bomHeaderId)
            <!-- Cabeçalho da BOM selecionada -->
            @php
                $selectedBom = App\Models\Mrp\BomHeader::with('product')->find($bomHeaderId);
            @endphp
            @if($selectedBom)
                <div class="mb-6">
                    <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-blue-500">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <div class="text-sm font-medium text-gray-500">{{ __('messages.bom_number') }}:</div>
                                <div class="text-lg font-bold text-gray-900">{{ $selectedBom->bom_number }}</div>
                                <div class="text-sm text-gray-500">{{ __('messages.version') }} {{ $selectedBom->version }}</div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-500">{{ __('messages.product') }}:</div>
                                <div class="text-lg font-bold text-gray-900">{{ $selectedBom->product->name ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500">{{ $selectedBom->product->sku ?? '' }}</div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-500">{{ __('messages.status') }}:</div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium
                                    @if($selectedBom->status === 'draft') bg-gray-100 text-gray-800
                                    @elseif($selectedBom->status === 'active') bg-green-100 text-green-800
                                    @elseif($selectedBom->status === 'obsolete') bg-red-100 text-red-800
                                    @endif">
                                    <i class="mr-1
                                        @if($selectedBom->status === 'draft') fas fa-pencil-alt
                                        @elseif($selectedBom->status === 'active') fas fa-check-circle
                                        @elseif($selectedBom->status === 'obsolete') fas fa-ban
                                        @endif"></i>
                                    @if($selectedBom->status === 'draft') {{ __('messages.status_draft') }}
                                    @elseif($selectedBom->status === 'active') {{ __('messages.status_active') }}
                                    @elseif($selectedBom->status === 'obsolete') {{ __('messages.status_obsolete') }}
                                    @else {{ $selectedBom->status }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botão para adicionar componente -->
                <div class="flex justify-end mb-4">
                    <button wire:click="addComponent" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 hover:shadow-lg">
                        <i class="fas fa-plus-circle mr-2"></i>
                        {{ __('messages.add_component') }}
                    </button>
                </div>

                <!-- Tabela de Componentes -->
                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                    <!-- Cabeçalho da Tabela -->
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
                        <h2 class="text-lg font-medium text-white flex items-center">
                            <i class="fas fa-puzzle-piece text-white mr-2"></i>
                            {{ __('messages.bom_components') }}
                        </h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('messages.component') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('messages.quantity') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('messages.level') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('messages.position') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('messages.critical') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('messages.actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @php 
                                    // Detectar qual variável usar na tabela
                                    // Isso garante que a tabela use a variável que tem dados
                                    $displayComponents = count($components ?? []) > 0 ? $components : $bomComponents ?? [];
                                @endphp
                                
                                @forelse ($displayComponents as $component)
                                    <tr class="hover:bg-gray-50 transition-colors duration-150 {{ (isset($component['is_critical']) && ($component['is_critical'] === true || $component['is_critical'] === 1 || $component['is_critical'] === 'Sim')) ? 'bg-red-50' : '' }}">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                @if(isset($component['component']) && is_array($component['component']) && isset($component['component']['name']))
                                                    {{ $component['component']['name'] }}
                                                @else
                                                    Componente #{{ $component['id'] ?? '?' }}
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                @if(isset($component['component']) && is_array($component['component']) && isset($component['component']['sku']))
                                                    {{ $component['component']['sku'] }}
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ number_format($component['quantity'], 4) }} {{ $component['uom'] }}</div>
                                            @if($component['scrap_percentage'] > 0)
                                                <div class="text-xs text-gray-500">Perda: {{ $component['scrap_percentage'] }}%</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $component['level'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $component['position'] ?: '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if($component['is_critical'])
                                                <span class="text-red-600" title="Componente crítico">
                                                    <i class="fas fa-exclamation-circle text-lg"></i>
                                                </span>
                                            @else
                                                <span class="text-gray-400" title="Componente não crítico">
                                                    <i class="fas fa-minus"></i>
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <button wire:click="editComponent({{ $component['id'] }})" 
                                                    class="text-indigo-600 hover:text-indigo-900 transition-colors duration-150 transform hover:scale-110" title="Editar Componente">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button wire:click="confirmDeleteComponent({{ $component['id'] }})" 
                                                    class="text-red-600 hover:text-red-900 transition-colors duration-150 transform hover:scale-110" title="Excluir Componente">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex flex-col items-center justify-center py-6 space-y-2">
                                                <div class="flex-shrink-0 bg-gray-100 p-3 rounded-full">
                                                    <i class="fas fa-puzzle-piece text-gray-400 text-2xl"></i>
                                                </div>
                                                <p class="text-gray-500 text-sm">{{ __('messages.no_components_found') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                {{ __('messages.bom_not_found') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>

    <!-- Modais -->
    @include('livewire.mrp.bom-management.bom-modal')
    @include('livewire.mrp.bom-management.component-modal')
    @include('livewire.mrp.bom-management.delete-modal')
    @include('livewire.mrp.bom-management.delete-component-modal')
    
    <!-- Modal de Detalhes da BOM -->
    @include('livewire.mrp.bom-management.modal-view-bom')
    
    <!-- Modal de visualização de detalhes da BOM implementado com Alpine.js -->
</div>
