<!-- View Request Details Modal -->
@if($showViewModal)
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Request Details - {{ $viewRequest->reference_number ?? '' }}
                                </h3>
                                <button wire:click="generatePDF({{ $viewRequest->id ?? 0 }})" class="ml-3 inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                                    <i class="fas fa-file-pdf text-red-500 mr-2"></i>Export PDF
                                </button>
                            </div>
                            
                            @if($viewRequest)
                                <div class="mt-4 border-t pt-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Reference Number</p>
                                            <p class="text-sm text-gray-900">{{ $viewRequest->reference_number }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Status</p>
                                            <p>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    {{ $viewRequest->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                    ($viewRequest->status === 'approved' ? 'bg-green-100 text-green-800' : 
                                                    ($viewRequest->status === 'rejected' ? 'bg-red-100 text-red-800' : 
                                                    ($viewRequest->status === 'ordered' ? 'bg-blue-100 text-blue-800' : 
                                                    'bg-purple-100 text-purple-800'))) }}">
                                                    {{ ucfirst($viewRequest->status) }}
                                                </span>
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Requested By</p>
                                            <p class="text-sm text-gray-900">{{ $viewRequest->requester->name ?? 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Suggested Vendor</p>
                                            <p class="text-sm text-gray-900">{{ $viewRequest->suggested_vendor ?: 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Delivery Date</p>
                                            <p class="text-sm text-gray-900">{{ $viewRequest->delivery_date ? $viewRequest->delivery_date->format('d/m/Y') : 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Created Date</p>
                                            <p class="text-sm text-gray-900">{{ $viewRequest->created_at->format('d/m/Y H:i') }}</p>
                                        </div>
                                        @if($viewRequest->status === 'approved')
                                            <div>
                                                <p class="text-sm font-medium text-gray-500">Approved By</p>
                                                <p class="text-sm text-gray-900">{{ $viewRequest->approver->name ?? 'N/A' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-500">Approved At</p>
                                                <p class="text-sm text-gray-900">{{ $viewRequest->approved_at ? $viewRequest->approved_at->format('d/m/Y H:i') : 'N/A' }}</p>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    @if($viewRequest->remarks)
                                        <div class="mb-4">
                                            <p class="text-sm font-medium text-gray-500">Remarks</p>
                                            <p class="text-sm text-gray-900 p-2 bg-gray-50 rounded">{{ $viewRequest->remarks }}</p>
                                        </div>
                                    @endif
                                    
                                    <!-- Items Table -->
                                    <div class="mt-4">
                                        <h4 class="text-md font-medium text-gray-900 mb-2">Requested Items</h4>
                                        <div class="overflow-x-auto border rounded">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Part Number</th>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Part Name</th>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier Reference</th>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    @foreach($viewRequest->items as $item)
                                                        <tr>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->part->part_number ?? 'N/A' }}</td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->part->name ?? 'N/A' }}</td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->supplier_reference ?: 'N/A' }}</td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->quantity_required }}</td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->unit }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    
                                    <!-- Images Gallery -->
                                    @if($viewRequest->images->count() > 0)
                                        <div class="mt-6">
                                            <h4 class="text-md font-medium text-gray-900 mb-2">Images ({{ $viewRequest->images->count() }})</h4>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                                @foreach($viewRequest->images as $image)
                                                    <div class="border rounded-md overflow-hidden bg-gray-50">
                                                        <img src="{{ Storage::url($image->image_path) }}" alt="Request Image" class="w-full h-48 object-contain p-2">
                                                        <div class="p-2 border-t">
                                                            <p class="text-sm text-gray-700 font-medium">{{ $image->caption ?: 'No caption' }}</p>
                                                            <p class="text-xs text-gray-500">{{ $image->original_filename }}</p>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button 
                        type="button" 
                        wire:click="closeViewModal"
                        class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
