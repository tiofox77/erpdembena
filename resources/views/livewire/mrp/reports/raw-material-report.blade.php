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
                <button type="button" 
                        wire:click="exportPdf"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-50">
                    <i class="fas fa-file-pdf mr-2"></i>
                    <span wire:loading.remove wire:target="exportPdf">{{ __('messages.export_pdf') }}</span>
                    <span wire:loading wire:target="exportPdf">{{ __('messages.exporting') }}...</span>
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
                                        <a href="#" 
                                           wire:click.prevent="showDetails({{ $material->id }})"
                                           class="text-blue-600 hover:text-blue-800 hover:underline"
                                           title="{{ __('messages.view_details') }}">
                                            {{ $material->sku }}
                                        </a>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                                            <i class="fas fa-flask text-gray-500"></i>
                                        </div>
                                        <div class="ml-4">
                                            <a href="#" wire:click.prevent="showDetails({{ $material->id }})" class="text-blue-600 hover:text-blue-800 hover:underline">
                                                {{ $material->name }}
                                            </a>
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

    <!-- Raw Material Details Modal -->
    <div x-data="{ activeTab: 'warehouses' }" x-show="$wire.showDetailsModal" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" 
                 x-show="$wire.showDetailsModal" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0"
                 @click="$wire.closeDetailsModal()">
            </div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full"
                 x-show="$wire.showDetailsModal"
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                <!-- Modal header -->
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    <i class="fas fa-box-open text-blue-600 mr-2"></i>
                                    {{ $selectedMaterial->name ?? '' }} ({{ $selectedMaterial->sku ?? '' }})
                                </h3>
                                <button wire:click="closeDetailsModal" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                                    <i class="fas fa-times text-xl"></i>
                                </button>
                            </div>
                            
                            <!-- Tabs -->
                            <div class="border-b border-gray-200 mb-4">
                                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                    <button 
                                        @click="activeTab = 'warehouses'" 
                                        :class="{'border-blue-500 text-blue-600': activeTab === 'warehouses', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'warehouses'}" 
                                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                                        wire:click="switchTab('warehouses')">
                                        <i class="fas fa-warehouse mr-1"></i>
                                        {{ __('messages.warehouses') }}
                                        @if(isset($warehouseStockDetails) && count($warehouseStockDetails) > 0)
                                            <span class="ml-1 bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-0.5 rounded-full">
                                                {{ count($warehouseStockDetails) }}
                                            </span>
                                        @endif
                                    </button>
                                    
                                    <button 
                                        @click="activeTab = 'purchase_orders'" 
                                        :class="{'border-blue-500 text-blue-600': activeTab === 'purchase_orders', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'purchase_orders'}" 
                                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                                        wire:click="switchTab('purchase_orders')">
                                        <i class="fas fa-shopping-cart mr-1"></i>
                                        {{ __('messages.purchase_orders') }}
                                        @if(isset($purchaseOrderDetails) && count($purchaseOrderDetails) > 0)
                                            <span class="ml-1 bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-0.5 rounded-full">
                                                {{ count($purchaseOrderDetails) }}
                                            </span>
                                        @endif
                                    </button>
                                    
                                    <button 
                                        @click="activeTab = 'production_schedules'" 
                                        :class="{'border-blue-500 text-blue-600': activeTab === 'production_schedules', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'production_schedules'}" 
                                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                                        wire:click="switchTab('production_schedules')">
                                        <i class="fas fa-calendar-alt mr-1"></i>
                                        {{ __('messages.production_schedules') }}
                                        @if(isset($productionScheduleDetails) && count($productionScheduleDetails) > 0)
                                            <span class="ml-1 bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-0.5 rounded-full">
                                                {{ count($productionScheduleDetails) }}
                                            </span>
                                        @endif
                                    </button>
                                </nav>
                            </div>
                            
                            <!-- Tab Content -->
                            <div class="mt-4">
                                <!-- Warehouses Tab -->
                                <div x-show="activeTab === 'warehouses'" class="space-y-4">
                                    @if(isset($warehouseStockDetails) && count($warehouseStockDetails) > 0)
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            {{ __('messages.warehouse') }}
                                                        </th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            {{ __('messages.quantity_on_hand') }}
                                                        </th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            {{ __('messages.allocated') }}
                                                        </th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            {{ __('messages.available') }}
                                                        </th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            {{ __('messages.last_updated') }}
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    @foreach($warehouseStockDetails as $warehouse)
                                                        <tr class="hover:bg-gray-50">
                                                            <td class="px-6 py-4 whitespace-nowrap">
                                                                <div class="text-sm font-medium text-gray-900">
                                                                    {{ $warehouse['location_name'] ?? 'N/A' }}
                                                                    <div class="text-xs text-gray-500">{{ $warehouse['location_code'] ?? '' }}</div>
                                                                </div>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap">
                                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ ($warehouse['quantity'] ?? 0) > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                                    {{ number_format($warehouse['quantity'] ?? 0, 2) }}
                                                                </span>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                {{ number_format($warehouse['quantity_allocated'] ?? 0, 2) }}
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap">
                                                                @php
                                                                    $available = ($warehouse['quantity'] ?? 0) - ($warehouse['quantity_allocated'] ?? 0);
                                                                @endphp
                                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $available > 0 ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800' }}">
                                                                    {{ number_format($available, 2) }}
                                                                </span>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                {{ $warehouse['last_updated'] ?? 'N/A' }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-8 bg-gray-50 rounded-lg">
                                            <i class="fas fa-warehouse text-4xl text-gray-300 mb-3"></i>
                                            <p class="text-gray-500">{{ __('messages.no_warehouse_data_available') }}</p>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Purchase Orders Tab -->
                                <div x-show="activeTab === 'purchase_orders'" class="space-y-4">
                                    @if(isset($purchaseOrderDetails) && count($purchaseOrderDetails) > 0)
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            {{ __('messages.po_number') }}
                                                        </th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            {{ __('messages.supplier') }}
                                                        </th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            {{ __('messages.status') }}
                                                        </th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            {{ __('messages.quantity') }}
                                                        </th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            {{ __('messages.expected_date') }}
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    @foreach($purchaseOrderDetails as $po)
                                                        <tr class="hover:bg-gray-50">
                                                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-blue-600 hover:text-blue-800 sm:pl-6">
                                                                {{ $po['po_number'] ?? 'N/A' }}
                                                            </td>
                                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                                {{ $po['supplier'] ?? 'N/A' }}
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap">
                                                                @php
                                                                    $statusClass = [
                                                                        'draft' => 'bg-gray-100 text-gray-800',
                                                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                                                        'approved' => 'bg-blue-100 text-blue-800',
                                                                        'ordered' => 'bg-indigo-100 text-indigo-800',
                                                                        'partially_received' => 'bg-purple-100 text-purple-800',
                                                                        'completed' => 'bg-green-100 text-green-800',
                                                                        'cancelled' => 'bg-red-100 text-red-800',
                                                                    ][$po['status'] ?? 'draft'] ?? 'bg-gray-100 text-gray-800';
                                                                @endphp
                                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                                                    {{ ucfirst(str_replace('_', ' ', $po['status'] ?? 'draft')) }}
                                                                </span>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                {{ number_format($po['quantity_ordered'] ?? 0, 2) }} {{ $selectedMaterial->unit_of_measure ?? '' }}
                                                                @if(($po['quantity_received'] ?? 0) > 0)
                                                                    <div class="text-xs text-gray-500">
                                                                        {{ __('messages.received') }}: {{ number_format($po['quantity_received'] ?? 0, 2) }}
                                                                    </div>
                                                                @endif
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                @php
                                                                    $deliveryDate = $po['expected_delivery_date'] ?? null;
                                                                    $orderDate = $po['order_date'] ?? null;
                                                                    
                                                                    // Debug log
                                                                    \Log::info('Date Debug:', [
                                                                        'delivery_date' => $deliveryDate,
                                                                        'order_date' => $orderDate,
                                                                        'type_delivery' => gettype($deliveryDate),
                                                                        'type_order' => gettype($orderDate)
                                                                    ]);
                                                                @endphp
                                                                
                                                                @if(!empty($deliveryDate) && $deliveryDate !== 'N/A')
                                                                    @php
                                                                        try {
                                                                            // Try to parse the date in different formats
                                                                            if (is_string($deliveryDate)) {
                                                                                if (\Carbon\Carbon::hasFormat($deliveryDate, 'Y-m-d')) {
                                                                                    $formattedDeliveryDate = \Carbon\Carbon::createFromFormat('Y-m-d', $deliveryDate)->format('d/m/Y');
                                                                                } else if (\Carbon\Carbon::hasFormat($deliveryDate, 'd/m/Y')) {
                                                                                    $formattedDeliveryDate = \Carbon\Carbon::createFromFormat('d/m/Y', $deliveryDate)->format('d/m/Y');
                                                                                } else {
                                                                                    $formattedDeliveryDate = $deliveryDate; // Fallback to raw value
                                                                                }
                                                                            } else if ($deliveryDate instanceof \Carbon\Carbon) {
                                                                                $formattedDeliveryDate = $deliveryDate->format('d/m/Y');
                                                                            } else {
                                                                                $formattedDeliveryDate = 'Formato inválido';
                                                                            }
                                                                            
                                                                            // Format order date if available
                                                                            if ($orderDate) {
                                                                                if (is_string($orderDate)) {
                                                                                    if (\Carbon\Carbon::hasFormat($orderDate, 'Y-m-d')) {
                                                                                        $formattedOrderDate = \Carbon\Carbon::createFromFormat('Y-m-d', $orderDate)->format('d/m/Y');
                                                                                    } else if (\Carbon\Carbon::hasFormat($orderDate, 'd/m/Y')) {
                                                                                        $formattedOrderDate = \Carbon\Carbon::createFromFormat('d/m/Y', $orderDate)->format('d/m/Y');
                                                                                    } else {
                                                                                        $formattedOrderDate = $orderDate; // Fallback to raw value
                                                                                    }
                                                                                } else if ($orderDate instanceof \Carbon\Carbon) {
                                                                                    $formattedOrderDate = $orderDate->format('d/m/Y');
                                                                                } else {
                                                                                    $formattedOrderDate = 'Formato inválido';
                                                                                }
                                                                            } else {
                                                                                $formattedOrderDate = 'N/A';
                                                                            }
                                                                        } catch (\Exception $e) {
                                                                            \Log::error('Error formatting dates', [
                                                                                'error' => $e->getMessage(),
                                                                                'delivery_date' => $deliveryDate,
                                                                                'order_date' => $orderDate
                                                                            ]);
                                                                            $formattedDeliveryDate = 'Erro ao formatar';
                                                                            $formattedOrderDate = 'N/A';
                                                                        }
                                                                    @endphp
                                                                    {{ $formattedDeliveryDate }}
                                                                    @if($formattedOrderDate !== 'N/A')
                                                                        <div class="text-xs text-gray-400">
                                                                            {{ __('messages.ordered') }}: {{ $formattedOrderDate }}
                                                                        </div>
                                                                    @endif
                                                                @else
                                                                    N/A
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-8 bg-gray-50 rounded-lg">
                                            <i class="fas fa-shopping-cart text-4xl text-gray-300 mb-3"></i>
                                            <p class="text-gray-500">{{ __('messages.no_purchase_orders_found') }}</p>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Production Schedules Tab -->
                                <div x-show="activeTab === 'production_schedules'" class="space-y-4">
                                    @if(isset($productionScheduleDetails) && count($productionScheduleDetails) > 0)
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            {{ __('messages.product') }}
                                                        </th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            {{ __('messages.schedule') }}
                                                        </th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            {{ __('messages.planned_quantity') }}
                                                        </th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            {{ __('messages.material_needed') }}
                                                        </th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            {{ __('messages.dates') }}
                                                        </th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            {{ __('messages.priority') }}
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    @foreach($productionScheduleDetails as $schedule)
                                                        <tr class="hover:bg-gray-50">
                                                            <td class="px-6 py-4 whitespace-nowrap">
                                                                <div class="text-sm font-medium text-gray-900">
                                                                    {{ $schedule['product_name'] ?? 'N/A' }}
                                                                </div>
                                                                <div class="text-xs text-gray-500">
                                                                    {{ $schedule['product_sku'] ?? '' }}
                                                                </div>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                <div class="font-medium">{{ $schedule['schedule_number'] ?? 'N/A' }}</div>
                                                                <div class="mt-1">
                                                                    @php
                                                                        $statusClass = [
                                                                            'draft' => 'bg-gray-100 text-gray-800',
                                                                            'scheduled' => 'bg-blue-100 text-blue-800',
                                                                            'in_progress' => 'bg-yellow-100 text-yellow-800',
                                                                            'completed' => 'bg-green-100 text-green-800',
                                                                            'cancelled' => 'bg-red-100 text-red-800',
                                                                        ][$schedule['status'] ?? 'draft'] ?? 'bg-gray-100 text-gray-800';
                                                                    @endphp
                                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                                                        {{ ucfirst(str_replace('_', ' ', $schedule['status'] ?? 'draft')) }}
                                                                    </span>
                                                                </div>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                <div class="font-medium">{{ number_format($schedule['planned_quantity'] ?? 0, 2) }}</div>
                                                                <div class="text-xs text-gray-400">{{ $schedule['actual_quantity'] ? 'Actual: ' . number_format($schedule['actual_quantity'], 2) : '' }}</div>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                <div>{{ number_format($schedule['material_per_unit'] ?? 0, 4) }} x {{ $schedule['planned_quantity'] ?? 0 }} =</div>
                                                                <div class="font-medium">{{ number_format($schedule['total_material_needed'] ?? 0, 2) }} {{ $selectedMaterial->unit_of_measure ?? '' }}</div>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                <div class="font-medium">{{ $schedule['start_date'] ?? 'N/A' }}</div>
                                                                <div class="text-xs text-gray-400">{{ __('messages.to') }}</div>
                                                                <div>{{ $schedule['end_date'] ?? 'N/A' }}</div>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                @php
                                                                    $priorityClass = [
                                                                        'high' => 'bg-red-100 text-red-800',
                                                                        'medium' => 'bg-yellow-100 text-yellow-800',
                                                                        'low' => 'bg-green-100 text-green-800',
                                                                    ][$schedule['priority'] ?? 'medium'] ?? 'bg-gray-100 text-gray-800';
                                                                @endphp
                                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $priorityClass }}">
                                                                    {{ ucfirst($schedule['priority'] ?? 'medium') }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-8 bg-gray-50 rounded-lg">
                                            <i class="fas fa-calendar-alt text-4xl text-gray-300 mb-3"></i>
                                            <p class="text-gray-500">{{ __('messages.no_production_schedules_found') }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
