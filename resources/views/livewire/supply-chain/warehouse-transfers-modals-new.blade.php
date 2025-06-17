<!-- ===============================================
     WAREHOUSE TRANSFER MODAL - MODERN DESIGN
     =============================================== -->

<!-- Transfer Request Modal with Tabs -->
<div x-data="{ 
    open: @entangle('isOpenRequestModal'), 
    currentTab: 'general',
    showProductSearch: false,
    selectedProducts: [],
    totalItems: 0
}" 
     x-show="open" 
     x-cloak 
     class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full z-50" 
     role="dialog" 
     aria-modal="true"
     x-transition:enter="transition ease-out duration-300" 
     x-transition:enter-start="opacity-0" 
     x-transition:enter-end="opacity-100" 
     x-transition:leave="transition ease-in duration-200" 
     x-transition:leave-start="opacity-100" 
     x-transition:leave-end="opacity-0"
     @keydown.escape="$wire.closeModal()">
    
    <div class="relative top-10 mx-auto p-4 w-full max-w-7xl">
        <div class="relative bg-white rounded-2xl shadow-2xl transform transition-all duration-300 ease-in-out" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="transform opacity-0 scale-95" 
             x-transition:enter-end="transform opacity-100 scale-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="transform opacity-100 scale-100" 
             x-transition:leave-end="transform opacity-0 scale-95">
            
            <!-- Enhanced Header with Progress -->
            <div class="bg-gradient-to-r from-blue-600 via-blue-700 to-purple-700 rounded-t-2xl px-8 py-6">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <div class="bg-white bg-opacity-20 rounded-full p-3">
                            <i class="fas {{ $selectedTransferRequestId ? 'fa-edit' : 'fa-shipping-fast' }} text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">
                                {{ $selectedTransferRequestId ? __('Edit Transfer Request') : __('New Transfer Request') }}
                            </h3>
                            <p class="text-blue-100 text-sm mt-1">
                                {{ __('Create transfer request between warehouses') }}
                            </p>
                        </div>
                    </div>
                    <button type="button" 
                            wire:click="closeModal" 
                            class="text-white hover:text-red-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90 bg-white bg-opacity-20 rounded-full p-2">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                
                <!-- Progress Indicator -->
                <div class="mt-6 flex items-center justify-center space-x-8">
                    <div class="flex items-center" :class="currentTab === 'general' ? 'text-white' : 'text-blue-200'">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold"
                             :class="currentTab === 'general' ? 'bg-white text-blue-600' : 'bg-blue-500 text-white'">1</div>
                        <span class="ml-2 text-sm font-medium">{{ __('General Info') }}</span>
                    </div>
                    <div class="h-0.5 w-16 bg-blue-400"></div>
                    <div class="flex items-center" :class="currentTab === 'products' ? 'text-white' : 'text-blue-200'">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold"
                             :class="currentTab === 'products' ? 'bg-white text-blue-600' : 'bg-blue-500 text-white'">2</div>
                        <span class="ml-2 text-sm font-medium">{{ __('Products') }}</span>
                    </div>
                    <div class="h-0.5 w-16 bg-blue-400"></div>
                    <div class="flex items-center" :class="currentTab === 'review' ? 'text-white' : 'text-blue-200'">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold"
                             :class="currentTab === 'review' ? 'bg-white text-blue-600' : 'bg-blue-500 text-white'">3</div>
                        <span class="ml-2 text-sm font-medium">{{ __('Review') }}</span>
                    </div>
                </div>
            </div>

            <!-- Modal Body with Tabs -->
            <div class="p-8 max-h-[70vh] overflow-y-auto">
                <form wire:submit.prevent="saveTransferRequest">
                    
                    <!-- Tab 1: General Information -->
                    <div x-show="currentTab === 'general'" 
                         x-transition:enter="transition ease-out duration-300" 
                         x-transition:enter-start="opacity-0 transform translate-x-4" 
                         x-transition:enter-end="opacity-100 transform translate-x-0">
                        
                        @include('livewire.supply-chain.warehouse-transfers.tab-general')
                        
                    </div>
                    
                    <!-- Tab 2: Products Selection -->
                    <div x-show="currentTab === 'products'" 
                         x-transition:enter="transition ease-out duration-300" 
                         x-transition:enter-start="opacity-0 transform translate-x-4" 
                         x-transition:enter-end="opacity-100 transform translate-x-0">
                        
                        @include('livewire.supply-chain.warehouse-transfers.tab-products')
                        
                    </div>
                    
                    <!-- Tab 3: Review -->
                    <div x-show="currentTab === 'review'" 
                         x-transition:enter="transition ease-out duration-300" 
                         x-transition:enter-start="opacity-0 transform translate-x-4" 
                         x-transition:enter-end="opacity-100 transform translate-x-0">
                        
                        @include('livewire.supply-chain.warehouse-transfers.tab-review')
                        
                    </div>

                </form>
            </div>

            <!-- Enhanced Footer -->
            <div class="bg-gray-50 px-8 py-6 rounded-b-2xl border-t border-gray-200">
                <div class="flex justify-between items-center">
                    <!-- Previous Button -->
                    <button type="button" 
                            x-show="currentTab !== 'general'"
                            @click="
                                if (currentTab === 'products') currentTab = 'general';
                                if (currentTab === 'review') currentTab = 'products';
                            "
                            class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105">
                        <i class="fas fa-arrow-left mr-2"></i>
                        {{ __('Previous') }}
                    </button>
                    
                    <div x-show="currentTab === 'general'" class="w-0"></div>
                    
                    <div class="flex space-x-3">
                        <!-- Cancel Button -->
                        <button type="button" 
                                wire:click="closeModal" 
                                class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105">
                            <i class="fas fa-times mr-2"></i>
                            {{ __('Cancel') }}
                        </button>
                        
                        <!-- Next/Save Button -->
                        <button type="button" 
                                x-show="currentTab !== 'review'"
                                @click="
                                    if (currentTab === 'general') currentTab = 'products';
                                    if (currentTab === 'products') currentTab = 'review';
                                "
                                class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105">
                            {{ __('Next') }}
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                        
                        <button type="submit" 
                                x-show="currentTab === 'review'"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center px-8 py-3 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 transform hover:scale-105 disabled:opacity-75">
                            <span wire:loading.remove wire:target="saveTransferRequest">
                                <i class="fas fa-save mr-2"></i>
                                {{ $selectedTransferRequestId ? __('Update Request') : __('Create Request') }}
                            </span>
                            <span wire:loading wire:target="saveTransferRequest" class="flex items-center">
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
    </div>
</div>
