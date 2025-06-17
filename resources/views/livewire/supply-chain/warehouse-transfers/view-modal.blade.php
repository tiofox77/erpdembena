<div x-data="{ open: @entangle('isOpenViewModal') }" 
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
    <div class="relative top-10 mx-auto p-4 w-full max-w-6xl">
        <div class="relative bg-white rounded-xl shadow-2xl transform transition-all duration-300 ease-in-out" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="transform opacity-0 scale-95" 
             x-transition:enter-end="transform opacity-100 scale-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="transform opacity-100 scale-100" 
             x-transition:leave-end="transform opacity-0 scale-95">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-t-xl px-6 py-4 flex justify-between items-center">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-eye mr-3 animate-pulse"></i>
                    {{ __('View Transfer Request') }} 
                    @if($transferRequest)
                        - {{ $transferRequest['request_number'] ?? '' }}
                    @endif
                </h3>
                <button type="button" 
                        wire:click="closeViewModal" 
                        class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="p-6">
                <!-- Status Badge -->
                @if($transferRequest)
                    <div class="mb-6 flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <span class="text-sm font-medium text-gray-600">Status:</span>
                            @php
                                $statusClasses = [
                                    'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                                    'approved' => 'bg-green-100 text-green-800 border-green-300',
                                    'rejected' => 'bg-red-100 text-red-800 border-red-300',
                                    'in_transit' => 'bg-blue-100 text-blue-800 border-blue-300',
                                    'completed' => 'bg-gray-100 text-gray-800 border-gray-300',
                                ];
                                $statusClass = $statusClasses[$transferRequest['status']] ?? 'bg-gray-100 text-gray-800 border-gray-300';
                                
                                $statusIcons = [
                                    'pending' => 'fa-clock',
                                    'approved' => 'fa-check-circle',
                                    'rejected' => 'fa-times-circle',
                                    'in_transit' => 'fa-truck',
                                    'completed' => 'fa-check-double',
                                ];
                                $statusIcon = $statusIcons[$transferRequest['status']] ?? 'fa-question-circle';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $statusClass }}">
                                <i class="fas {{ $statusIcon }} mr-1"></i>
                                {{ $statusOptions[$transferRequest['status']] ?? $transferRequest['status'] }}
                            </span>
                        </div>
                        <div class="flex items-center space-x-4">
                            <span class="text-sm font-medium text-gray-600">Priority:</span>
                            @php
                                $priorityClasses = [
                                    'low' => 'bg-gray-100 text-gray-800',
                                    'normal' => 'bg-blue-100 text-blue-800',
                                    'high' => 'bg-orange-100 text-orange-800',
                                    'urgent' => 'bg-red-100 text-red-800',
                                ];
                                $priorityClass = $priorityClasses[$transferRequest['priority']] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-medium {{ $priorityClass }}">
                                <i class="fas fa-flag mr-1"></i>
                                {{ ucfirst($transferRequest['priority']) }}
                            </span>
                        </div>
                    </div>
                @endif

                <!-- Transfer Details Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Source Warehouse -->
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-warehouse text-blue-600 mr-2"></i>
                            {{ __('Source Warehouse') }}
                        </h4>
                        <p class="text-lg font-medium text-gray-900">
                            {{ $this->sourceWarehouseName ?? 'N/A' }}
                        </p>
                    </div>

                    <!-- Destination Warehouse -->
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-warehouse text-green-600 mr-2"></i>
                            {{ __('Destination Warehouse') }}
                        </h4>
                        <p class="text-lg font-medium text-gray-900">
                            {{ $this->destinationWarehouseName ?? 'N/A' }}
                        </p>
                    </div>

                    <!-- Requested Date -->
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-calendar text-purple-600 mr-2"></i>
                            {{ __('Requested Date') }}
                        </h4>
                        <p class="text-lg font-medium text-gray-900">
                            @if($transferRequest['requested_date'])
                                @if(is_string($transferRequest['requested_date']))
                                    {{ \Carbon\Carbon::parse($transferRequest['requested_date'])->format('M d, Y') }}
                                @else
                                    {{ $transferRequest['requested_date']->format('M d, Y') }}
                                @endif
                            @else
                                N/A
                            @endif
                        </p>
                    </div>

                    <!-- Required By Date -->
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-calendar-check text-orange-600 mr-2"></i>
                            {{ __('Required By Date') }}
                        </h4>
                        <p class="text-lg font-medium text-gray-900">
                            @if($transferRequest['required_by_date'])
                                @if(is_string($transferRequest['required_by_date']))
                                    {{ \Carbon\Carbon::parse($transferRequest['required_by_date'])->format('M d, Y') }}
                                @else
                                    {{ $transferRequest['required_by_date']->format('M d, Y') }}
                                @endif
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Notes -->
                @if($transferRequest && $transferRequest['notes'])
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 mb-6">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-sticky-note text-yellow-600 mr-2"></i>
                            {{ __('Notes') }}
                        </h4>
                        <p class="text-gray-700">{{ $transferRequest['notes'] }}</p>
                    </div>
                @endif

                <!-- Items Table -->
                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                        <h4 class="text-base font-semibold text-gray-700 flex items-center">
                            <i class="fas fa-boxes text-blue-600 mr-2"></i>
                            {{ __('Transfer Items') }}
                        </h4>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Product') }}
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Quantity Requested') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Notes') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($items as $item)
                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $item['product_name'] }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="text-sm font-semibold text-gray-900">
                                                {{ number_format($item['quantity_requested'], 2) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $item['notes'] ?? '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                                            <i class="fas fa-inbox text-4xl mb-2"></i>
                                            <p>{{ __('No items in this transfer request') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Footer with Action Buttons -->
            <div class="bg-gray-50 px-6 py-4 rounded-b-xl flex justify-between items-center border-t border-gray-200">
                <button type="button" 
                        wire:click="closeViewModal" 
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                    <i class="fas fa-times mr-2"></i>
                    {{ __('Close') }}
                </button>
                
                @if($transferRequest && ($transferRequest['status'] === 'pending' || $transferRequest['status'] === 'pending_approval'))
                    <div class="flex space-x-3">
                        <!-- Debug info (remove after testing) -->
                        <div class="text-xs text-gray-500 mr-2">Status: {{ $transferRequest['status'] ?? 'null' }}</div>
                        
                        <!-- Approve Button (opens approval modal) -->
                        <button type="button" 
                                wire:click="openApprovalModal"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                            <i class="fas fa-clipboard-check mr-2"></i>
                            {{ __('Approve') }}
                        </button>
                        
                        <!-- Reject Button -->
                        <button type="button" 
                                wire:click="rejectTransferRequest"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="rejectTransferRequest">
                                <i class="fas fa-times-circle mr-2"></i>
                                {{ __('Reject') }}
                            </span>
                            <span wire:loading wire:target="rejectTransferRequest" class="inline-flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ __('Processing...') }}
                            </span>
                        </button>
                    </div>
                @else
                    <!-- Debug info for non-pending status -->
                    <div class="text-xs text-gray-500">
                        Status: {{ $transferRequest['status'] ?? 'null' }} (not showing buttons)
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div x-data="{ open: @entangle('isOpenApprovalModal') }" 
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
    <div class="relative top-20 mx-auto p-1 w-full max-w-2xl">
        <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="transform opacity-0 scale-95" 
             x-transition:enter-end="transform opacity-100 scale-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="transform opacity-100 scale-100" 
             x-transition:leave-end="transform opacity-0 scale-95">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-lg px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-clipboard-check mr-2"></i>
                    {{ __('Approve Transfer Request') }}
                </h3>
                <button type="button" wire:click="$set('isOpenApprovalModal', false)" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Body -->
            <div class="p-6">
                <div class="mb-4">
                    <p class="text-gray-700">{{ __('Are you sure you want to approve this transfer request?') }}</p>
                </div>
                
                <div class="mt-6">
                    <label for="approval_notes" class="block text-sm font-medium text-gray-700">
                        {{ __('Approval Notes') }} ({{ __('Optional') }})
                    </label>
                    <textarea id="approval_notes" wire:model.defer="approvalNotes" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white"></textarea>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button type="button" 
                        wire:click="$set('isOpenApprovalModal', false)" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    {{ __('Cancel') }}
                </button>
                
                <!-- Reject Button -->
                <button type="button" 
                        wire:click="rejectTransferRequest"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50">
                    <span wire:loading.remove wire:target="rejectTransferRequest">
                        <i class="fas fa-times-circle mr-2"></i>
                        {{ __('Reject') }}
                    </span>
                    <span wire:loading wire:target="rejectTransferRequest" class="inline-flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('Rejecting...') }}
                    </span>
                </button>
                
                <!-- Accept Button -->
                <button type="button" 
                        wire:click="acceptTransferRequest"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50">
                    <span wire:loading.remove wire:target="acceptTransferRequest">
                        <i class="fas fa-check-circle mr-2"></i>
                        {{ __('Accept') }}
                    </span>
                    <span wire:loading wire:target="acceptTransferRequest" class="inline-flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('Processing...') }}
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>