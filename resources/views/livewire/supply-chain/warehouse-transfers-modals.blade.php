<!-- ============================================
     WAREHOUSE TRANSFER REQUEST MODAL - MODERN TABBED INTERFACE
     ============================================ -->

<!-- Transfer Request Modal -->
<div x-data="{ open: @entangle('isOpenRequestModal') }" 
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
    <div class="relative top-10 mx-auto p-4 w-full max-w-7xl">
        <div class="relative bg-white rounded-xl shadow-2xl transform transition-all duration-300 ease-in-out" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="transform opacity-0 scale-95" 
             x-transition:enter-end="transform opacity-100 scale-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="transform opacity-100 scale-100" 
             x-transition:leave-end="transform opacity-0 scale-95">
            
            <!-- ============================================
                 HEADER WITH PROGRESS INDICATOR
                 ============================================ -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-t-xl px-6 py-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <i class="fas {{ $selectedTransferRequestId ? 'fa-edit' : 'fa-plus-circle' }} mr-3 animate-pulse"></i>
                        {{ $selectedTransferRequestId ? __('Edit Transfer Request') : __('New Transfer Request') }}
                    </h3>
                    <button type="button" wire:click="$set('isOpenRequestModal', false)" 
                            class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <!-- Progress Steps -->
                <div class="flex items-center justify-center space-x-4">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium transition-all duration-200
                            {{ $currentTab === 'general' ? 'bg-white text-blue-600' : 'bg-blue-500 text-white' }}">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <span class="ml-2 text-sm font-medium text-white">{{ __('General') }}</span>
                    </div>
                    <div class="w-8 h-px bg-blue-400"></div>
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium transition-all duration-200
                            {{ $currentTab === 'products' ? 'bg-white text-blue-600' : 'bg-blue-500 text-white' }}">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <span class="ml-2 text-sm font-medium text-white">{{ __('Products') }}</span>
                    </div>
                    <div class="w-8 h-px bg-blue-400"></div>
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium transition-all duration-200
                            {{ $currentTab === 'review' ? 'bg-white text-blue-600' : 'bg-blue-500 text-white' }}">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <span class="ml-2 text-sm font-medium text-white">{{ __('Review') }}</span>
                    </div>
                </div>
            </div>

            <!-- ============================================
                 TAB NAVIGATION
                 ============================================ -->
            <div class="bg-gray-50 border-b border-gray-200 rounded-none">
                <nav class="flex space-x-8 px-6" aria-label="Tabs">
                    <button type="button" 
                            wire:click="setTab('general')"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200 ease-in-out transform hover:scale-105
                                {{ $currentTab === 'general' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        <i class="fas fa-info-circle mr-2"></i>
                        {{ __('General Information') }}
                    </button>
                    <button type="button" 
                            wire:click="setTab('products')"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200 ease-in-out transform hover:scale-105
                                {{ $currentTab === 'products' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        <i class="fas fa-boxes mr-2"></i>
                        {{ __('Products') }}
                        @if(count($items) > 0)
                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ count($items) }}
                            </span>
                        @endif
                    </button>
                    <button type="button" 
                            wire:click="setTab('review')"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200 ease-in-out transform hover:scale-105
                                {{ $currentTab === 'review' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        <i class="fas fa-check-circle mr-2"></i>
                        {{ __('Review & Submit') }}
                    </button>
                </nav>
            </div>

            <!-- ============================================
                 TAB CONTENT CONTAINER
                 ============================================ -->
            <div class="max-h-[75vh] overflow-y-auto">
                <form wire:submit.prevent="submitRequest" wire:ignore.self class="h-full">
                    <div class="p-6 min-h-[600px]">
                        
                        <!-- TAB 1: GENERAL INFORMATION -->
                        @if($currentTab === 'general')
                            <div x-data="{ activeTab: 'general' }"
                                 x-show="activeTab === 'general'"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 transform translate-x-4"
                                 x-transition:enter-end="opacity-100 transform translate-x-0"
                                 x-transition:leave="transition ease-in duration-200"
                                 x-transition:leave-start="opacity-100 transform translate-x-0"
                                 x-transition:leave-end="opacity-0 transform translate-x-4">
                                @include('livewire.supply-chain.warehouse-transfers.tab-general')
                            </div>
                        @endif

                        <!-- TAB 2: PRODUCTS SELECTION -->
                        @if($currentTab === 'products')
                            <div x-data="{ activeTab: 'products' }"
                                 x-show="activeTab === 'products'"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 transform translate-x-4"
                                 x-transition:enter-end="opacity-100 transform translate-x-0"
                                 x-transition:leave="transition ease-in duration-200"
                                 x-transition:leave-start="opacity-100 transform translate-x-0"
                                 x-transition:leave-end="opacity-0 transform translate-x-4">
                                @include('livewire.supply-chain.warehouse-transfers.tab-products')
                            </div>
                        @endif

                        <!-- TAB 3: REVIEW & SUBMIT -->
                        @if($currentTab === 'review')
                            <div x-data="{ activeTab: 'review' }"
                                 x-show="activeTab === 'review'"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 transform translate-x-4"
                                 x-transition:enter-end="opacity-100 transform translate-x-0"
                                 x-transition:leave="transition ease-in duration-200"
                                 x-transition:leave-start="opacity-100 transform translate-x-0"
                                 x-transition:leave-end="opacity-0 transform translate-x-4">
                                @include('livewire.supply-chain.warehouse-transfers.tab-review')
                            </div>
                        @endif

                    </div>

                    <!-- ============================================
                         FOOTER WITH NAVIGATION BUTTONS
                         ============================================ -->
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 rounded-b-xl">
                        <div class="flex justify-between items-center">
                            <!-- Previous Button -->
                            <div>
                                @if($currentTab !== 'general')
                                    <button type="button" 
                                            wire:click="previousTab"
                                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                        <i class="fas fa-chevron-left mr-2"></i>
                                        {{ __('Previous') }}
                                    </button>
                                @endif
                            </div>

                            <!-- Tab Status & Actions -->
                            <div class="flex items-center space-x-3">
                                
                                <!-- Cancel Button -->
                                <button type="button" 
                                        wire:click="$set('isOpenRequestModal', false)" 
                                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                    <i class="fas fa-times mr-2"></i>
                                    {{ __('Cancel') }}
                                </button>

                                <!-- Next/Submit Button -->
                                @if($currentTab === 'review')
                                    <!-- Final Submit Buttons -->
                                    <button type="button" 
                                            wire:click.prevent="saveDraft"
                                            class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg font-semibold text-gray-700 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                        <i class="fas fa-save mr-2"></i>
                                        {{ __('Save Draft') }}
                                    </button>
                                    <button type="submit" 
                                            wire:loading.attr="disabled"
                                            @if(!$this->hasSourceWarehouse || !$this->hasDestinationWarehouse || empty($items))
                                                disabled
                                                class="inline-flex items-center px-6 py-2 bg-gray-300 border border-transparent rounded-lg font-semibold text-gray-500 cursor-not-allowed"
                                            @else
                                                class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-green-600 to-emerald-600 border border-transparent rounded-lg font-semibold text-white hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 ease-in-out transform hover:scale-105"
                                            @endif>
                                        <span wire:loading.remove wire:target="saveTransferRequest">
                                            <i class="fas fa-paper-plane mr-2"></i>
                                            {{ __('Submit Request') }}
                                        </span>
                                        <span wire:loading wire:target="saveTransferRequest" class="inline-flex items-center">
                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            {{ __('Submitting...') }}
                                        </span>
                                    </button>
                                @else
                                    <!-- Next Button -->
                                    <button type="button" 
                                            wire:click="nextTab"
                                            class="inline-flex items-center px-6 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                        {{ __('Next') }}
                                        <i class="fas fa-chevron-right ml-2"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ============================================
     VIEW TRANSFER REQUEST MODAL - READ-ONLY WITH ACTIONS
     ============================================ -->
@include('livewire.supply-chain.warehouse-transfers.view-modal')

<!-- Delete Confirmation Modal -->
<div x-data="{ open: @entangle('isOpenDeleteModal') }" 
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
            <!-- Header -->
            <div class="bg-gradient-to-r from-red-600 to-red-700 rounded-t-lg px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    {{ __('Confirm Deletion') }}
                </h3>
                <button type="button" wire:click="$set('isOpenDeleteModal', false)" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Body -->
            <div class="p-6">
                <div class="text-center">
                    <i class="fas fa-exclamation-circle text-red-500 text-5xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Are you sure?') }}</h3>
                    <p class="text-gray-500">
                        {{ __('This action cannot be undone. All items in this transfer request will be permanently deleted.') }}
                    </p>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button type="button" 
                        wire:click="$set('isOpenDeleteModal', false)" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    {{ __('Cancel') }}
                </button>
                <button type="button" 
                        wire:click="deleteTransferRequest"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50">
                    <span wire:loading.remove wire:target="deleteTransferRequest">
                        <i class="fas fa-trash mr-2"></i> {{ __('Delete') }}
                    </span>
                    <span wire:loading wire:target="deleteTransferRequest" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('Deleting...') }}
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>