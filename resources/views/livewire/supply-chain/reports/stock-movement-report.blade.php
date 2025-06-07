<div class="py-6">
    <!-- Heading -->
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-exchange-alt text-blue-600 mr-3 animate-pulse"></i>
                    {{ __('messages.stock_movement_report') }}
                </h1>
                <p class="mt-2 text-sm text-gray-600">
                    {{ __('messages.stock_movement_report_description') }}
                </p>
            </div>
            <div class="flex space-x-2">
                <button type="button" 
                        wire:click="exportToExcel"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                    <i class="fas fa-file-excel mr-2"></i>
                    <span wire:loading.remove wire:target="exportToExcel">{{ __('messages.export_excel') }}</span>
                    <span wire:loading wire:target="exportToExcel">{{ __('messages.exporting') }}...</span>
                </button>
                <button type="button" 
                        wire:click="exportToPdf"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                    <i class="fas fa-file-pdf mr-2"></i>
                    <span wire:loading.remove wire:target="exportToPdf">{{ __('messages.export_pdf') }}</span>
                    <span wire:loading wire:target="exportToPdf">{{ __('messages.exporting') }}...</span>
                </button>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-blue-50 overflow-hidden shadow rounded-lg">
                <div class="px-4 py-3 sm:p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-2">
                            <i class="fas fa-arrow-down text-white text-sm"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-xs font-medium text-gray-500 truncate">{{ __('messages.total_entries') }}</p>
                            <p class="text-lg font-semibold text-gray-900">{{ number_format($totalIn, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-red-50 overflow-hidden shadow rounded-lg">
                <div class="px-4 py-3 sm:p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-red-500 rounded-md p-2">
                            <i class="fas fa-arrow-up text-white text-sm"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-xs font-medium text-gray-500 truncate">{{ __('messages.total_outputs') }}</p>
                            <p class="text-lg font-semibold text-gray-900">{{ number_format($totalOut, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-green-50 overflow-hidden shadow rounded-lg">
                <div class="px-4 py-3 sm:p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-2">
                            <i class="fas fa-exchange-alt text-white text-sm"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-xs font-medium text-gray-500 truncate">{{ __('messages.net_movement') }}</p>
                            <p class="text-lg font-semibold text-gray-900">{{ number_format($netMovement, 2) }}</p>
                        </div>
                    </div>
                </div>
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
                                wire:model.live.debounce.500ms="search" 
                                id="search"
                                wire:keydown.enter="$refresh"
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out" 
                                placeholder="{{ __('messages.search_transactions_placeholder') }}">
                        </div>
                    </div>
                    
                    <!-- Filters in a grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Product -->
                        <div>
                            <label for="productId" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-box text-gray-500 mr-1"></i>
                                {{ __('messages.product') }}
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-boxes text-gray-400"></i>
                                </div>
                                <select 
                                    id="productId" 
                                    wire:model.live="productId" 
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                    <option value="">{{ __('messages.all_products') }}</option>
                                    @foreach($products as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Location -->
                        <div>
                            <label for="locationId" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-warehouse text-gray-500 mr-1"></i>
                                {{ __('messages.location') }}
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-location-arrow text-gray-400"></i>
                                </div>
                                <select 
                                    id="locationId" 
                                    wire:model.live="locationId" 
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                    <option value="">{{ __('messages.all_locations') }}</option>
                                    @foreach($locations as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Transaction Type -->
                        <div>
                            <label for="transactionType" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-exchange-alt text-gray-500 mr-1"></i>
                                {{ __('messages.transaction_type') }}
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-tag text-gray-400"></i>
                                </div>
                                <select 
                                    id="transactionType" 
                                    wire:model.live="transactionType" 
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                    <option value="">{{ __('messages.all_types') }}</option>
                                    @foreach($transactionTypes as $value => $label)
                                        <option value="{{ $value }}">{{ __($label) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Start Date -->
                        <div>
                            <label for="startDate" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-calendar-day text-gray-500 mr-1"></i>
                                {{ __('messages.start_date') }}
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="far fa-calendar-alt text-gray-400"></i>
                                </div>
                                <input 
                                    type="date" 
                                    wire:model.live="startDate" 
                                    id="startDate" 
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                            </div>
                        </div>
                        
                        <!-- End Date -->
                        <div>
                            <label for="endDate" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-calendar-day text-gray-500 mr-1"></i>
                                {{ __('messages.end_date') }}
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="far fa-calendar-alt text-gray-400"></i>
                                </div>
                                <input 
                                    type="date" 
                                    wire:model.live="endDate" 
                                    id="endDate" 
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                            </div>
                        </div>

                        <!-- Items Per Page -->
                        <div>
                            <label for="perPage" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-list-ol text-gray-500 mr-1"></i>
                                {{ __('messages.items_per_page') }}
                            </label>
                            <select 
                                wire:model.live="perPage" 
                                id="perPage" 
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
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
                            @if($search || $productId || $locationId || $transactionType || $startDate || $endDate)
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

    <!-- Main Content - Stock Movement Table -->
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden transition-all duration-300 ease-in-out hover:shadow-lg">
            <!-- Table Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3 border-b border-gray-200">
                <h2 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-exchange-alt mr-2"></i>
                    {{ __('messages.stock_movement_transactions') }}
                </h2>
            </div>
            
            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="far fa-calendar-alt text-gray-400 mr-1"></i>
                                    {{ __('messages.date') }}
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-box text-gray-400 mr-1"></i>
                                    {{ __('messages.product') }}
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-warehouse text-gray-400 mr-1"></i>
                                    {{ __('messages.source_location') }}
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-warehouse text-gray-400 mr-1"></i>
                                    {{ __('messages.destination_location') }}
                                </div>
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-tag text-gray-400 mr-1"></i>
                                    {{ __('messages.type') }}
                                    <div class="ml-1 relative group">
                                        <i class="fas fa-info-circle text-gray-400 text-xs"></i>
                                        <div class="absolute left-0 bottom-full mb-2 w-48 p-2 bg-gray-800 text-white text-xs rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-10">
                                            {{ __('messages.transaction_type_help') }}
                                        </div>
                                    </div>
                                </div>
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-hashtag text-gray-400 mr-1"></i>
                                    {{ __('messages.reference') }}
                                </div>
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-info-circle text-gray-400 mr-1"></i>
                                    {{ __('messages.info') }}
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-boxes text-gray-400 mr-1"></i>
                                    {{ __('messages.quantity') }}
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-tag text-gray-400 mr-1"></i>
                                    {{ __('messages.unit_price') }}
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-money-bill-wave text-gray-400 mr-1"></i>
                                    {{ __('messages.total_value') }}
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-user text-gray-400 mr-1"></i>
                                    {{ __('messages.created_by') }}
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($transactions as $transaction)
                            <tr class="hover:bg-gray-50 transition-all duration-150 ease-in-out transform hover:scale-[1.01]">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y H:i') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                                            <i class="fas fa-box text-gray-500"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $transaction->product->name ?? 'N/A' }}</div>
                                            <div class="text-xs text-gray-500">{{ $transaction->product->sku ?? '' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $transaction->sourceLocation->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $transaction->destinationLocation->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @php
                                        $typeConfig = [
                                            'receipt' => [
                                                'color' => 'bg-green-100 text-green-800',
                                                'icon' => 'fa-arrow-down',
                                                'label' => __('messages.receipt')
                                            ],
                                            'issue' => [
                                                'color' => 'bg-red-100 text-red-800',
                                                'icon' => 'fa-arrow-up',
                                                'label' => __('messages.issue')
                                            ],
                                            'transfer' => [
                                                'color' => 'bg-blue-100 text-blue-800',
                                                'icon' => 'fa-exchange-alt',
                                                'label' => __('messages.transfer')
                                            ],
                                            'adjustment' => [
                                                'color' => 'bg-yellow-100 text-yellow-800',
                                                'icon' => 'fa-adjust',
                                                'label' => __('messages.adjustment')
                                            ],
                                            'default' => [
                                                'color' => 'bg-gray-100 text-gray-800',
                                                'icon' => 'fa-question-circle',
                                                'label' => $transaction->transaction_type
                                            ]
                                        ];
                                        $config = $typeConfig[$transaction->transaction_type] ?? $typeConfig['default'];
                                    @endphp
                                    <div class="flex items-center">
                                        <span class="px-3 py-1 inline-flex items-center text-xs leading-5 font-medium rounded-full {{ $config['color'] }} transition-all duration-200 hover:shadow-md">
                                            <i class="fas {{ $config['icon'] }} mr-1"></i>
                                            {{ $config['label'] }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center text-sm text-gray-700">
                                        @if($transaction->reference_type && $transaction->reference_id)
                                            <i class="fas fa-link mr-2 text-gray-400"></i>
                                            <span class="font-mono">{{ $transaction->reference_type }}-{{ $transaction->reference_id }}</span>
                                            @if($transaction->batch_number)
                                                <span class="ml-2 px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded">
                                                    {{ $transaction->batch_number }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                    @if(isset($error))
                                        <span class="text-xs text-red-500">{{ $error }}</span>
                                    @else
                                        <span class="text-xs text-gray-400">-
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium {{ $transaction->quantity > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ number_format($transaction->quantity, 2) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ number_format($transaction->unit_price, 2) }} {{ $transaction->product->unit_of_measure ?? '' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ number_format($transaction->quantity * $transaction->unit_price, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                            <i class="fas fa-user text-blue-600"></i>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">{{ $transaction->creator->name ?? 'System' }}</div>
                                            <div class="text-xs text-gray-500">
                                                {{ $transaction->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-6 py-10 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-exchange-alt text-4xl text-gray-300 mb-3"></i>
                                        <h3 class="text-lg font-medium text-gray-900 mb-1">{{ __('messages.no_transactions_found') }}</h3>
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
                        {{ __('messages.showing') }} <span class="font-medium">{{ $transactions->firstItem() ?? 0 }}</span> {{ __('messages.to') }} <span class="font-medium">{{ $transactions->lastItem() ?? 0 }}</span> {{ __('messages.of') }} <span class="font-medium">{{ $transactions->total() }}</span> {{ __('messages.results') }}
                    </div>
                    <div class="mt-2 sm:mt-0">
                        {{ $transactions->onEachSide(1)->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>



    @push('scripts')
        <script>
            document.addEventListener('livewire:initialized', () => {
                // Add any JavaScript initialization here
            });
        </script>
    @endpush
</div>
