<div class="py-6">
    <!-- Heading -->
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-flask text-blue-600 mr-3 animate-pulse"></i>
                    {{ __('messages.raw_material_report') }}
                </h1>
                <p class="mt-2 text-sm text-gray-600">
                    {{ __('messages.raw_material_report_description') }}
                </p>
            </div>
            <div class="flex space-x-2">
                <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                    <i class="fas fa-file-excel mr-2"></i> 
                    {{ __('messages.export_excel') }}
                </button>
                <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                    <i class="fas fa-file-pdf mr-2"></i>
                    {{ __('messages.export_pdf') }}
                </button>
            </div>
        </div>
    </div>
    
    <!-- Filters Section -->
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 mb-6">
        <div class="mb-6 bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 ease-in-out hover:shadow-lg transform hover:scale-[1.01]">
            <!-- Card Header with gradient -->
            <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                <i class="fas fa-filter text-blue-600 mr-2"></i>
                <h2 class="text-base font-medium text-gray-700">{{ __('messages.search_and_filters') }}</h2>
            </div>
            
            <!-- Card Content -->
            <div class="p-4">
                <div class="flex flex-col gap-4">
                    <!-- Search -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-search text-gray-500 mr-1"></i>
                            {{ __('messages.search') }}
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input 
                                type="text" 
                                wire:model.live="search" 
                                id="search"
                                wire:keydown.enter="$refresh"
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out" 
                                placeholder="{{ __('messages.search_raw_materials_placeholder') }}">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">{{ __('messages.search_hint') }}</p>
                    </div>
                    
                    <!-- Date Range Filters in a grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Start Date -->
                        <div>
                            <label for="startDate" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-calendar-alt text-gray-500 mr-1"></i>
                                {{ __('messages.start_date') }}
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-calendar-day text-gray-400"></i>
                                </div>
                                <input 
                                    type="date" 
                                    wire:model.live="startDate" 
                                    id="startDate" 
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out"
                                >
                            </div>
                        </div>
                        
                        <!-- End Date -->
                        <div>
                            <label for="endDate" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-calendar-alt text-gray-500 mr-1"></i>
                                {{ __('messages.end_date') }}
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-calendar-day text-gray-400"></i>
                                </div>
                                <input 
                                    type="date" 
                                    wire:model.live="endDate" 
                                    id="endDate" 
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out"
                                >
                            </div>
                        </div>

                        <!-- Per Page Selector -->
                        <div>
                            <label for="perPage" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-list-ol text-gray-500 mr-1"></i>
                                {{ __('messages.items_per_page') }}
                            </label>
                            <select 
                                wire:model.live="perPage" 
                                id="perPage" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Reset Filters Button -->
                    <div class="flex justify-end">
                        <button 
                            wire:click="resetFilters" 
                            class="flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                            <i class="fas fa-redo-alt mr-2"></i>
                            {{ __('messages.reset_filters') }}
                            @if($search || $startDate || $endDate)
                                <span class="ml-2 bg-blue-100 text-blue-800 text-xs px-2 py-0.5 rounded-full">
                                    {{ __('messages.active_filters') }}
                                </span>
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content - Raw Material Table -->
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden transition-all duration-300 ease-in-out hover:shadow-lg">
            <!-- Table Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3 border-b border-gray-200">
                <h2 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-flask mr-2"></i>
                    {{ __('messages.raw_materials_list') }}
                </h2>
            </div>
            
            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-barcode text-gray-400 mr-1"></i>
                                    {{ __('messages.sku') }}
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-tag text-gray-400 mr-1"></i>
                                    {{ __('messages.name') }}
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-warehouse text-gray-400 mr-1"></i>
                                    {{ __('messages.current_stock') }}
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-truck-loading text-gray-400 mr-1"></i>
                                    {{ __('messages.po_stock') }}
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-industry text-gray-400 mr-1"></i>
                                    {{ __('messages.consumed_quantity') }}
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-calendar-check text-gray-400 mr-1"></i>
                                    {{ __('messages.planned_quantity') }}
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-exclamation-circle text-gray-400 mr-1"></i>
                                    {{ __('messages.reorder_point') }}
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-shopping-cart text-gray-400 mr-1"></i>
                                    {{ __('messages.reorder_quantity') }}
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-balance-scale text-gray-400 mr-1"></i>
                                    {{ __('messages.unit_of_measure') }}
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($rawMaterials as $material)
                            <tr class="hover:bg-gray-50 transition-all duration-150 ease-in-out transform hover:scale-[1.01]">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $material->sku }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                                            <i class="fas fa-flask text-gray-500"></i>
                                        </div>
                                        <div class="ml-4">
                                            <button 
                                                wire:click="showDetails({{ $material->id }})" 
                                                class="text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline focus:outline-none"
                                                title="{{ __('messages.view_details') }}"
                                            >
                                                {{ $material->name }}
                                            </button>
                                            <div class="text-xs text-gray-500">{{ __('messages.raw_material') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($material->current_stock <= $material->reorder_point && $material->current_stock > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-exclamation-triangle mr-1 animate-pulse"></i>
                                            {{ number_format($material->current_stock, 2) }}
                                        </span>
                                        <div class="mt-1 text-xs text-amber-600 flex items-center">
                                            {{ __('messages.low_stock') }}
                                        </div>
                                    @elseif($material->current_stock <= 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-exclamation-circle mr-1 animate-pulse"></i>
                                            {{ number_format($material->current_stock, 2) }}
                                        </span>
                                        <div class="mt-1 text-xs text-red-600 flex items-center">
                                            {{ __('messages.out_of_stock') }}
                                        </div>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            {{ number_format($material->current_stock, 2) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ number_format($material->po_stock, 2) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-purple-100 text-purple-800">
                                        {{ number_format($material->consumed_quantity, 2) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-indigo-100 text-indigo-800">
                                        {{ number_format($material->planned_quantity, 2) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ number_format($material->reorder_point, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if ($material->reorder_quantity > 0)
                                        <div class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-shopping-cart mr-1"></i>
                                            {{ number_format($material->reorder_quantity, 2) }}
                                        </div>
                                    @else
                                        {{ number_format($material->reorder_quantity, 2) }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $material->unit_of_measure }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button 
                                        wire:click="showDetails({{ $material->id }})" 
                                        class="text-blue-600 hover:text-blue-900 transition-colors duration-200"
                                        title="{{ __('messages.view_details') }}"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-10 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-box-open text-4xl text-gray-300 mb-3"></i>
                                        <h3 class="text-lg font-medium text-gray-900 mb-1">{{ __('messages.no_raw_materials_found') }}</h3>
                                        <p class="text-sm text-gray-500 mb-4">{{ __('messages.try_different_filters') }}</p>
                                        <button wire:click="resetFilters" 
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                            <i class="fas fa-redo-alt mr-2"></i>
                                            {{ __('messages.reset_filters') }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-sm text-gray-600 bg-blue-50 px-3 py-2 rounded-md border border-blue-100 flex items-center">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                        {{ __('messages.showing') }} <span class="font-medium">{{ $rawMaterials->firstItem() ?? 0 }}</span> {{ __('messages.to') }} <span class="font-medium">{{ $rawMaterials->lastItem() ?? 0 }}</span> {{ __('messages.of') }} <span class="font-medium">{{ $rawMaterials->total() }}</span> {{ __('messages.results') }}
                    </div>
                    <div class="mt-2 sm:mt-0">
                        {{ $rawMaterials->onEachSide(1)->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Export Buttons -->
    <div class="mt-4 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-end space-x-3">
            <button
                type="button"
                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                <i class="fas fa-file-excel mr-2"></i>
                {{ __('messages.export_to_excel') }}
            </button>
            <button
                type="button"
                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                <i class="fas fa-file-pdf mr-2"></i>
                {{ __('messages.export_to_pdf') }}
            </button>
        </div>
    </div>
</div>

<!-- Modal de Detalhes -->
@if($showDetailsModal && $selectedMaterial)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Fundo escuro -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeDetailsModal"></div>

        <!-- ConteÃºdo da Modal -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <!-- CabeÃ§alho -->
                        <div class="flex justify-between items-center pb-3">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
                                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                {{ $selectedMaterial->name }} - {{ $selectedMaterial->sku }}
                            </h3>
                            <button type="button" wire:click="closeDetailsModal" class="text-gray-400 hover:text-gray-500">
                                <span class="sr-only">Fechar</span>
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>

                        <!-- Abas -->
                        <div class="border-b border-gray-200">
                            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                <button 
                                    wire:click="switchTab('warehouses')"
                                    class="{{ $activeTab === 'warehouses' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                                >
                                    <i class="fas fa-warehouse mr-2"></i>
                                    {{ __('messages.warehouses') }}
                                    <span class="bg-gray-100 text-gray-600 ml-2 py-0.5 px-2 rounded-full text-xs">
                                        {{ count($warehouseStockDetails) }}
                                    </span>
                                </button>

                                <button 
                                    wire:click="switchTab('purchase_orders')"
                                    class="{{ $activeTab === 'purchase_orders' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                                >
                                    <i class="fas fa-file-invoice-dollar mr-2"></i>
                                    {{ __('messages.purchase_orders') }}
                                    <span class="bg-gray-100 text-gray-600 ml-2 py-0.5 px-2 rounded-full text-xs">
                                        {{ count($purchaseOrderDetails) }}
                                    </span>
                                </button>

                                <button 
                                    wire:click="switchTab('production_schedules')"
                                    class="{{ $activeTab === 'production_schedules' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                                >
                                    <i class="fas fa-calendar-alt mr-2"></i>
                                    {{ __('messages.production_schedules') }}
                                    <span class="bg-gray-100 text-gray-600 ml-2 py-0.5 px-2 rounded-full text-xs">
                                        {{ count($productionScheduleDetails) }}
                                    </span>
                                </button>
                            </nav>
                        </div>

                        <!-- ConteÃºdo das Abas -->
                        <div class="mt-4 max-h-96 overflow-y-auto">
                            <!-- Aba de ArmazÃ©ns -->
                            @if($activeTab === 'warehouses')
                                @if(count($warehouseStockDetails) > 0)
                                    <div class="space-y-4">
                                        @foreach($warehouseStockDetails as $warehouse)
                                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                                <div class="flex justify-between items-center">
                                                    <div>
                                                        <h4 class="text-sm font-medium text-gray-900">{{ $warehouse['location_name'] }}</h4>
                                                        <p class="text-xs text-gray-500">{{ $warehouse['location_code'] }}</p>
                                                    </div>
                                                    <div class="text-right">
                                                        <p class="text-sm font-medium text-gray-900">{{ number_format($warehouse['quantity'], 2) }} {{ $selectedMaterial->unit_of_measure }}</p>
                                                        <p class="text-xs text-gray-500">{{ __('messages.available') }}: {{ number_format($warehouse['quantity_available'], 2) }}</p>
                                                    </div>
                                                </div>
                                                @if($warehouse['quantity_allocated'] > 0)
                                                    <div class="mt-2 pt-2 border-t border-gray-100">
                                                        <p class="text-xs text-amber-600">
                                                            <i class="fas fa-lock mr-1"></i>
                                                            {{ __('messages.allocated') }}: {{ number_format($warehouse['quantity_allocated'], 2) }} {{ $selectedMaterial->unit_of_measure }}
                                                        </p>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-8">
                                        <i class="fas fa-warehouse text-4xl text-gray-300 mb-2"></i>
                                        <p class="text-gray-500">{{ __('messages.no_warehouses_found') }}</p>
                                    </div>
                                @endif
                            @endif

                            <!-- Aba de Ordens de Compra -->
                            @if($activeTab === 'purchase_orders')
                                @if(count($purchaseOrderDetails) > 0)
                                    <div class="overflow-hidden">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.po_number') }}</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.supplier') }}</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.quantity') }}</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.status') }}</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.expected_date') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach($purchaseOrderDetails as $po)
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="px-3 py-3 whitespace-nowrap text-sm font-medium text-blue-600">
                                                            {{ $po['po_number'] }}
                                                        </td>
                                                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">
                                                            {{ $po['supplier'] }}
                                                        </td>
                                                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">
                                                            {{ number_format($po['quantity_ordered'], 2) }} {{ $selectedMaterial->unit_of_measure }}
                                                            @if($po['quantity_received'] > 0)
                                                                <div class="text-xs text-gray-400">
                                                                    {{ __('messages.received') }}: {{ number_format($po['quantity_received'], 2) }}
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td class="px-3 py-3 whitespace-nowrap">
                                                            @php
                                                                $statusColors = [
                                                                    'draft' => 'bg-gray-100 text-gray-800',
                                                                    'approved' => 'bg-blue-100 text-blue-800',
                                                                    'ordered' => 'bg-yellow-100 text-yellow-800',
                                                                    'partially_received' => 'bg-indigo-100 text-indigo-800',
                                                                    'completed' => 'bg-green-100 text-green-800',
                                                                    'cancelled' => 'bg-red-100 text-red-800',
                                                                ];
                                                                $statusColor = $statusColors[$po['status']] ?? 'bg-gray-100 text-gray-800';
                                                            @endphp
                                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColor }}">
                                                                {{ __("messages.status_{$po['status']}") }}
                                                            </span>
                                                        </td>
                                                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">
                                                            {{ $po['expected_date'] }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-8">
                                        <i class="fas fa-file-invoice-dollar text-4xl text-gray-300 mb-2"></i>
                                        <p class="text-gray-500">{{ __('messages.no_purchase_orders_found') }}</p>
                                    </div>
                                @endif
                            @endif

                            <!-- Aba de Cronogramas de ProduÃ§Ã£o -->
                            @if($activeTab === 'production_schedules')
                                @if(count($productionScheduleDetails) > 0)
                                    <div class="overflow-hidden">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.production_order') }}</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.product') }}</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.quantity') }}</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.status') }}</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.planned_date') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach($productionScheduleDetails as $schedule)
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="px-3 py-3 whitespace-nowrap text-sm font-medium text-blue-600">
                                                            {{ $schedule['production_order'] }}
                                                        </td>
                                                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">
                                                            {{ $schedule['product_name'] }}
                                                            <div class="text-xs text-gray-400">{{ $schedule['product_sku'] }}</div>
                                                        </td>
                                                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">
                                                            {{ number_format($schedule['quantity'], 2) }} {{ $selectedMaterial->unit_of_measure }}
                                                            @if($schedule['quantity_produced'] > 0)
                                                                <div class="text-xs text-gray-400">
                                                                    {{ __('messages.produced') }}: {{ number_format($schedule['quantity_produced'], 2) }}
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td class="px-3 py-3 whitespace-nowrap">
                                                            @php
                                                                $statusColors = [
                                                                    'draft' => 'bg-gray-100 text-gray-800',
                                                                    'planned' => 'bg-blue-100 text-blue-800',
                                                                    'in_progress' => 'bg-yellow-100 text-yellow-800',
                                                                    'partially_completed' => 'bg-indigo-100 text-indigo-800',
                                                                    'completed' => 'bg-green-100 text-green-800',
                                                                    'cancelled' => 'bg-red-100 text-red-800',
                                                                ];
                                                                $statusColor = $statusColors[$schedule['status']] ?? 'bg-gray-100 text-gray-800';
                                                            @endphp
                                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColor }}">
                                                                {{ __("messages.status_{$schedule['status']}") }}
                                                            </span>
                                                        </td>
                                                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">
                                                            {{ $schedule['planned_start_date'] }}
                                                            @if($schedule['planned_end_date'] && $schedule['planned_end_date'] !== $schedule['planned_start_date'])
                                                                <div class="text-xs text-gray-400">
                                                                    {{ __('messages.to') }} {{ $schedule['planned_end_date'] }}
                                                                </div>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-8">
                                        <i class="fas fa-calendar-alt text-4xl text-gray-300 mb-2"></i>
                                        <p class="text-gray-500">{{ __('messages.no_production_schedules_found') }}</p>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" wire:click="closeDetailsModal" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                    {{ __('messages.close') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endif
< s c r i p t >  
         d o c u m e n t . a d d E v e n t L i s t e n e r ( ' l i v e w i r e : i n i t i a l i z e d ' ,   ( )   = >   {  
                 / /   A d i c i o n a   u m   l i s t e n e r   p a r a   o   e v e n t o   d e t a i l s - l o a d e d  
                 L i v e w i r e . o n ( ' d e t a i l s - l o a d e d ' ,   ( )   = >   {  
                         c o n s o l e . l o g ( ' E v e n t o   d e t a i l s - l o a d e d   r e c e b i d o ' ) ;  
                         / /   R o l e   a   p Ã ¡ g i n a   p a r a   o   t o p o   p a r a   g a r a n t i r   q u e   a   m o d a l   s e j a   v i s Ã ­ v e l  
                         w i n d o w . s c r o l l T o ( {   t o p :   0 ,   b e h a v i o r :   ' s m o o t h '   } ) ;  
                 } ) ;  
         } ) ;  
 < / s c r i p t >  
 