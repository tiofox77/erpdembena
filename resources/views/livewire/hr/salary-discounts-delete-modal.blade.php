<!-- Modal de Exclusão de Desconto -->
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
    <div class="relative top-20 mx-auto p-1 w-full max-w-md">
        <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="transform opacity-0 scale-95" 
             x-transition:enter-end="transform opacity-100 scale-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="transform opacity-100 scale-100" 
             x-transition:leave-end="transform opacity-0 scale-95">
            
            <!-- Cabeçalho -->
            <div class="bg-gradient-to-r from-red-600 to-red-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-trash-alt mr-2 animate-pulse"></i>
                    {{ __('messages.delete_discount') }}
                </h3>
                <button wire:click="closeDeleteModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Conteúdo -->
            <div class="p-6">
                <!-- Aviso de Confirmação -->
                <div class="flex items-center mb-6">
                    <div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-600 text-lg"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-medium text-gray-900">{{ __('messages.confirm_deletion') }}</h4>
                        <p class="text-sm text-gray-500">{{ __('messages.delete_discount_warning') }}</p>
                    </div>
                </div>
                
                <!-- Informações do Desconto -->
                @if($discountToDelete)
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-6">
                        <h5 class="font-medium text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                            {{ __('messages.discount_to_delete') }}
                        </h5>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('messages.employee') }}:</span>
                                <span class="font-medium text-gray-900">{{ $discountToDelete->employee->full_name ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('messages.discount_type') }}:</span>
                                <span class="font-medium text-gray-900">
                                    @if($discountToDelete->discount_type === 'union')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-users mr-1"></i>
                                            {{ __('messages.union_discount') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            <i class="fas fa-tag mr-1"></i>
                                            {{ __('messages.other_discount') }}
                                        </span>
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('messages.amount') }}:</span>
                                <span class="font-medium text-gray-900">{{ number_format($discountToDelete->amount, 2) }} Kz</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('messages.status') }}:</span>
                                <span class="font-medium text-gray-900">
                                    @if($discountToDelete->status === 'pending')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i>
                                            {{ __('messages.pending') }}
                                        </span>
                                    @elseif($discountToDelete->status === 'approved')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            {{ __('messages.approved') }}
                                        </span>
                                    @elseif($discountToDelete->status === 'rejected')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            {{ __('messages.rejected') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-flag-checkered mr-1"></i>
                                            {{ __('messages.completed') }}
                                        </span>
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('messages.request_date') }}:</span>
                                <span class="font-medium text-gray-900">{{ $discountToDelete->request_date ? $discountToDelete->request_date->format('d/m/Y') : 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Avisos específicos baseados no status -->
                    @if($discountToDelete->status === 'approved' && $discountToDelete->payments->count() > 0)
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-yellow-600 text-lg"></i>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-medium text-yellow-800">{{ __('messages.warning') }}</h4>
                                    <p class="text-sm text-yellow-700">
                                        {{ __('messages.discount_has_payments_warning') }}
                                    </p>
                                    <p class="text-xs text-yellow-600 mt-1">
                                        {{ __('messages.payments_count') }}: {{ $discountToDelete->payments->count() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
                
                <!-- Confirmação de Exclusão -->
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-trash-alt text-red-600 text-lg"></i>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-red-800">{{ __('messages.irreversible_action') }}</h4>
                            <p class="text-sm text-red-700">
                                {{ __('messages.delete_discount_confirmation') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Rodapé -->
            <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                <button wire:click="closeDeleteModal" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out">
                    <i class="fas fa-times mr-2"></i>
                    {{ __('messages.cancel') }}
                </button>
                <button wire:click="delete" class="inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                    <i class="fas fa-trash-alt mr-2"></i>
                    {{ __('messages.delete') }}
                </button>
            </div>
        </div>
    </div>
</div>
