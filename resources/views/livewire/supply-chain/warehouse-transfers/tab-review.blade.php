<!-- ============================================
     TAB 3: REVIEW & SUBMIT
     ============================================ -->

<div class="space-y-8">
    <div class="text-center">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ __('Review Transfer Request') }}</h2>
        <p class="text-gray-600">{{ __('Please review all details before submitting your transfer request') }}</p>
    </div>

    <!-- Transfer Summary -->
    <div class="bg-gradient-to-br from-indigo-50 to-purple-100 rounded-xl border border-indigo-200 overflow-hidden shadow-sm">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
            <h3 class="text-lg font-semibold text-white flex items-center">
                <i class="fas fa-clipboard-check mr-3"></i>
                {{ __('Transfer Summary') }}
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column: Basic Info -->
                <div class="space-y-6">
                    <!-- Warehouses -->
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-route mr-2 text-blue-600"></i>
                            {{ __('Transfer Route') }}
                        </h4>
                        <div class="flex items-center justify-between">
                            <div class="text-center">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                    <i class="fas fa-warehouse text-blue-600"></i>
                                </div>
                                <p class="text-sm font-semibold text-gray-900">{{ __('From') }}</p>
                                <p class="text-sm text-gray-600">
                                    @if($this->hasSourceWarehouse)
                                        {{ $this->sourceWarehouseName }}
                                    @else
                                        {{ __('Not selected') }}
                                    @endif
                                </p>
                            </div>
                            <div class="flex-1 px-4">
                                <div class="h-px bg-gray-300 relative">
                                    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white px-2">
                                        <i class="fas fa-arrow-right text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                    <i class="fas fa-warehouse text-green-600"></i>
                                </div>
                                <p class="text-sm font-semibold text-gray-900">{{ __('To') }}</p>
                                <p class="text-sm text-gray-600">
                                    @if($this->hasDestinationWarehouse)
                                        {{ $this->destinationWarehouseName }}
                                    @else
                                        {{ __('Not selected') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Priority & Dates -->
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-calendar-alt mr-2 text-purple-600"></i>
                            {{ __('Schedule Details') }}
                        </h4>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">{{ __('Priority:') }}</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                    @if($transferRequest['priority'] === 'urgent') bg-red-100 text-red-800
                                    @elseif($transferRequest['priority'] === 'high') bg-orange-100 text-orange-800
                                    @elseif($transferRequest['priority'] === 'normal') bg-yellow-100 text-yellow-800
                                    @else bg-green-100 text-green-800
                                    @endif">
                                    @if($transferRequest['priority'] === 'urgent') 🔴
                                    @elseif($transferRequest['priority'] === 'high') 🟠  
                                    @elseif($transferRequest['priority'] === 'normal') 🟡
                                    @else 🟢
                                    @endif
                                    {{ $priorities[$transferRequest['priority']] ?? 'Not set' }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">{{ __('Request Date:') }}</span>
                                <span class="text-sm font-semibold text-gray-900">
                                    {{ $transferRequest['requested_date'] ? \Carbon\Carbon::parse($transferRequest['requested_date'])->format('M d, Y') : 'Not set' }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">{{ __('Required By:') }}</span>
                                <span class="text-sm font-semibold text-gray-900">
                                    {{ $transferRequest['required_by_date'] ? \Carbon\Carbon::parse($transferRequest['required_by_date'])->format('M d, Y') : 'Not set' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Statistics -->
                <div class="space-y-6">
                    <!-- Items Summary -->
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-chart-bar mr-2 text-green-600"></i>
                            {{ __('Items Summary') }}
                        </h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-3 bg-blue-50 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600">{{ count($items) }}</div>
                                <div class="text-sm text-blue-600">{{ __('Product Types') }}</div>
                            </div>
                            <div class="text-center p-3 bg-green-50 rounded-lg">
                                <div class="text-2xl font-bold text-green-600">
                                    {{ array_sum(array_column($items, 'quantity_requested')) }}
                                </div>
                                <div class="text-sm text-green-600">{{ __('Total Units') }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    @if(!empty($transferRequest['notes']))
                        <div class="bg-white rounded-lg p-4 border border-gray-200">
                            <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                                <i class="fas fa-sticky-note mr-2 text-yellow-600"></i>
                                {{ __('Special Instructions') }}
                            </h4>
                            <p class="text-sm text-gray-700 bg-gray-50 p-3 rounded-lg italic">
                                "{{ $transferRequest['notes'] }}"
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Items Details -->
    @if(count($items) > 0)
        <div class="bg-gradient-to-br from-blue-50 to-cyan-100 rounded-xl border border-blue-200 overflow-hidden shadow-sm">
            <div class="bg-gradient-to-r from-blue-600 to-cyan-600 px-6 py-4">
                <h3 class="text-lg font-semibold text-white flex items-center">
                    <i class="fas fa-list mr-3"></i>
                    {{ __('Items to Transfer') }}
                </h3>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white rounded-lg overflow-hidden shadow-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Product') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('SKU') }}
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Quantity') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Notes') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($items as $item)
                                @php
                                    $product = $products->find($item['product_id']);
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-purple-500 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-box text-white"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-semibold text-gray-900">
                                                    {{ $product ? $product->name : 'Product not found' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-mono text-sm text-gray-600">
                                            {{ $product ? $product->sku : 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                            {{ number_format($item['quantity_requested'], 2) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-600">
                                            {{ !empty($item['notes']) ? $item['notes'] : '—' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Validation Messages -->
    <div class="space-y-4">
        <!-- Missing Information Alerts -->
        @if(!$this->hasSourceWarehouse || !$this->hasDestinationWarehouse)
            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">{{ __('Missing Warehouse Information') }}</h3>
                        <div class="mt-2 text-sm text-red-700 space-y-1">
                            @if(!$this->hasSourceWarehouse)
                                <p class="flex items-center">
                                    <i class="fas fa-arrow-right text-red-400 mr-2 text-xs"></i>
                                    {{ __('Please select a source warehouse in the General tab.') }}
                                </p>
                            @endif
                            @if(!$this->hasDestinationWarehouse)
                                <p class="flex items-center">
                                    <i class="fas fa-arrow-right text-red-400 mr-2 text-xs"></i>
                                    {{ __('Please select a destination warehouse in the General tab.') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(empty($items))
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">{{ __('No Products Selected') }}</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>{{ __('Please add at least one product in the Products tab.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Success Message -->
        @if($this->hasSourceWarehouse && $this->hasDestinationWarehouse && count($items) > 0)
            <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800">{{ __('Ready to Submit') }}</h3>
                        <div class="mt-2 text-sm text-green-700">
                            <p>{{ __('Your transfer request is complete and ready to be submitted for approval.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Actions are handled by the parent modal component -->
</div>
