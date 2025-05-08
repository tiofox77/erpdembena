<div>
    <div class="py-6 max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <!-- Header with title and add button -->
            <div class="p-4 sm:px-6 flex flex-col sm:flex-row justify-between sm:items-center border-b border-gray-200">
                <h1 class="text-lg font-medium text-gray-900 flex items-center">
                    <i class="fas fa-clipboard-list mr-3 text-gray-500"></i> {{ __('part_requests.part_request_management') }}
                </h1>
                <div class="mt-3 sm:mt-0 flex space-x-2">
                    <button 
                        type="button" 
                        wire:click="generateListPDF"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <i class="fas fa-file-pdf mr-2 text-red-500"></i> {{ __('part_requests.export_list') }}
                    </button>
                    <button 
                        type="button" 
                        wire:click="openModal"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <i class="fas fa-plus-circle mr-2"></i> {{ __('part_requests.new_request') }}
                    </button>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="p-4 sm:p-6 bg-white border-b border-gray-200">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Search -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.search') }}</label>
                        <input
                            type="text"
                            id="search"
                            wire:model.live.debounce.300ms="search"
                            placeholder="{{ __('part_requests.search_reference_part') }}"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        >
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.status') }}</label>
                        <select
                            id="status"
                            wire:model.live="status"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        >
                            <option value="">{{ __('part_requests.all_statuses') }}</option>
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Items Per Page -->
                    <div>
                        <label for="perPage" class="block text-sm font-medium text-gray-700 mb-1">{{ __('part_requests.items_per_page') }}</label>
                        <select
                            id="perPage"
                            wire:model.live="perPage"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        >
                            <option value="10">10 {{ __('part_requests.per_page') }}</option>
                            <option value="25">25 {{ __('part_requests.per_page') }}</option>
                            <option value="50">50 {{ __('part_requests.per_page') }}</option>
                            <option value="100">100 {{ __('part_requests.per_page') }}</option>
                        </select>
                    </div>
                </div>

                <!-- Clear Filters Button -->
                <div class="mt-4 flex justify-end">
                    <button 
                        wire:click="clearFilters" 
                        class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <i class="fas fa-times-circle mr-2"></i> {{ __('part_requests.clear_filters') }}
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('reference_number')">
                                    {{ __('part_requests.reference_number') }}
                                    @if($sortField === 'reference_number')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 text-gray-300"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('part_requests.item_description') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('supplier_reference')">
                                    {{ __('messages.supplier_reference') }}/CODE
                                    @if($sortField === 'supplier_reference')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 text-gray-300"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('quantity_required')">
                                    {{ __('messages.quantity_required') }}
                                    @if($sortField === 'quantity_required')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 text-gray-300"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('messages.units') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('suggested_vendor')">
                                    {{ __('messages.suggested_vendor') }} Name
                                    @if($sortField === 'suggested_vendor')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 text-gray-300"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('delivery_date')">
                                    E.T. Delivery Date
                                    @if($sortField === 'delivery_date')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 text-gray-300"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Remark/Item Picture
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('status')">
                                    Status
                                    @if($sortField === 'status')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 text-gray-300"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($requests as $request)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $request->reference_number }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    @if($request->items->count() > 0)
                                        <div class="space-y-1">
                                            @foreach($request->items as $item)
                                                <div class="{{ !$loop->last ? 'border-b pb-1 mb-1' : '' }}">
                                                    <span class="font-medium">{{ $item->part->name ?? 'N/A' }}</span>
                                                    @if($item->part->part_number)
                                                        <span class="text-xs text-gray-500 block">Part #: {{ $item->part->part_number }}</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-400">No items</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    @if($request->items->count() > 0)
                                        <div class="space-y-1">
                                            @foreach($request->items as $item)
                                                <div class="{{ !$loop->last ? 'border-b pb-1 mb-1' : '' }}">
                                                    {{ $item->supplier_reference ?? 'N/A' }}
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    @if($request->items->count() > 0)
                                        <div class="space-y-1">
                                            @foreach($request->items as $item)
                                                <div class="{{ !$loop->last ? 'border-b pb-1 mb-1' : '' }}">
                                                    {{ $item->quantity_required }}
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    @if($request->items->count() > 0)
                                        <div class="space-y-1">
                                            @foreach($request->items as $item)
                                                <div class="{{ !$loop->last ? 'border-b pb-1 mb-1' : '' }}">
                                                    {{ $item->unit }}
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $request->suggested_vendor ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $request->delivery_date ? $request->delivery_date->format(\App\Models\Setting::getSystemDateFormat()) : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($request->remarks)
                                        <span class="tooltip" title="{{ $request->remarks }}">
                                            <i class="fas fa-comment-alt text-blue-500 mr-1"></i> View
                                        </span>
                                    @endif
                                    @if($request->images->count() > 0)
                                        <span class="ml-2">
                                            <i class="fas fa-images text-green-500 mr-1"></i> {{ $request->images->count() }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $request->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                           ($request->status === 'approved' ? 'bg-green-100 text-green-800' : 
                                           ($request->status === 'rejected' ? 'bg-red-100 text-red-800' : 
                                           ($request->status === 'ordered' ? 'bg-blue-100 text-blue-800' : 
                                            'bg-purple-100 text-purple-800'))) }}">
                                        {{ $statusOptions[$request->status] ?? 'Unknown' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <button wire:click="viewDetails({{ $request->id }})" class="text-blue-600 hover:text-blue-900" title="{{ __('messages.view_details') }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button wire:click="generatePDF({{ $request->id }})" class="text-red-600 hover:text-red-900" title="{{ __('messages.generate_pdf') }}">
                                            <i class="fas fa-file-pdf"></i>
                                        </button>
                                        <button wire:click="edit({{ $request->id }})" class="text-indigo-600 hover:text-indigo-900" title="{{ __('messages.edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @if($request->status === 'pending')
                                            <button wire:click="changeStatus({{ $request->id }}, 'approved')" class="text-green-600 hover:text-green-900" title="{{ __('messages.approve') }}">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button wire:click="changeStatus({{ $request->id }}, 'rejected')" class="text-red-600 hover:text-red-900" title="{{ __('messages.reject') }}">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                        <button wire:click="confirmDelete({{ $request->id }})" class="text-red-600 hover:text-red-900" title="{{ __('messages.delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    {{ __('part_requests.no_part_requests_found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-4 py-3 bg-white border-t border-gray-200 sm:px-6">
                {{ $requests->links() }}
            </div>
        </div>
    </div>

    @include('livewire.equipment-part-requests.modals')
    @include('livewire.equipment-part-requests.view-modal')
</div>
