{{-- Delete Confirmation Modal for Payroll --}}
<div>
    <div x-data="{ open: @entangle('showDeleteModal') }" 
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
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
            <div class="transform transition-all duration-300 ease-in-out" 
                 x-transition:enter="transition ease-out duration-300" 
                 x-transition:enter-start="transform opacity-0 scale-95" 
                 x-transition:enter-end="transform opacity-100 scale-100" 
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="transform opacity-100 scale-100" 
                 x-transition:leave-end="transform opacity-0 scale-95">
                
                {{-- Header --}}
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                            <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">
                                {{ __('messages.confirm_delete') }}
                            </h3>
                        </div>
                    </div>
                </div>

                {{-- Body --}}
                <div class="mb-6">
                    <p class="text-sm text-gray-500 mb-4">
                        {{ __('messages.confirm_delete_payroll_message') }}
                    </p>
                    
                    @if($payrollToDelete)
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                                        <i class="fas fa-user text-red-600 text-sm"></i>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-red-900">
                                        {{ $payrollToDelete->employee->full_name }}
                                    </p>
                                    <p class="text-xs text-red-700 truncate">
                                        {{ $payrollToDelete->payrollPeriod->name ?? 'N/A' }} • 
                                        {{ number_format($payrollToDelete->net_salary ?? 0, 2) }} AOA
                                    </p>
                                </div>
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                        @if($payrollToDelete->status === 'draft') bg-yellow-100 text-yellow-800
                                        @elseif($payrollToDelete->status === 'rejected') bg-red-100 text-red-800
                                        @endif">
                                        {{ ucfirst($payrollToDelete->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <div class="mt-4 p-3 bg-yellow-50 border-l-4 border-yellow-400 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>{{ __('messages.warning') }}:</strong> 
                                    {{ __('messages.delete_payroll_warning') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end space-x-3">
                    <button 
                        type="button" 
                        wire:click="cancelDelete" 
                        class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105"
                    >
                        <i class="fas fa-times mr-2"></i>
                        {{ __('messages.cancel') }}
                    </button>
                    
                    <button 
                        type="button" 
                        wire:click="confirmDelete" 
                        wire:loading.attr="disabled"
                        class="inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed"
                    >
                        <span wire:loading.remove wire:target="confirmDelete">
                            <i class="fas fa-trash mr-2"></i>
                            {{ __('messages.delete') }}
                        </span>
                        <span wire:loading wire:target="confirmDelete" class="flex items-center">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            {{ __('messages.deleting') }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
