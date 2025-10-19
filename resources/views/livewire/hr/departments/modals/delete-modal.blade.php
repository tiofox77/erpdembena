@if($showDeleteModal)
<div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('showDeleteModal') }" x-show="show" style="display: none;">
    <!-- Background overlay -->
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75" x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="$wire.closeDeleteModal()">
        </div>

        <!-- Modal panel -->
        <div class="inline-block w-full max-w-md my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl"
             x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-5 bg-gradient-to-r from-red-600 to-rose-600 border-b border-red-700">
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-10 h-10 mr-3 bg-white rounded-lg shadow-sm">
                        <i class="fas fa-trash-alt text-red-600 text-lg"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white">
                        {{ __('hr.departments.delete_department') }}
                    </h3>
                </div>
                <button @click="$wire.closeDeleteModal()" type="button" 
                        class="text-white hover:text-gray-200 transition-colors duration-200 focus:outline-none">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full">
                            <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-semibold text-gray-900 mb-2">
                            {{ __('hr.departments.are_you_sure') }}?
                        </h4>
                        <div class="text-sm text-gray-600 space-y-2">
                            <p>{{ __('hr.departments.delete_department_confirmation') }}</p>
                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded-r-lg mt-3">
                                <div class="flex items-start">
                                    <i class="fas fa-exclamation-circle text-yellow-600 mt-0.5 mr-2"></i>
                                    <p class="text-xs text-yellow-800">
                                        {{ __('hr.departments.this_action_cannot_be_undone') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-2xl space-x-3">
                <button 
                    type="button"
                    @click="$wire.closeDeleteModal()"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                    <i class="fas fa-times mr-2"></i>
                    {{ __('hr.departments.cancel') }}
                </button>
                <button 
                    type="button"
                    wire:click="delete"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-red-600 to-rose-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:from-red-700 hover:to-rose-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                    <i class="fas fa-trash-alt mr-2" wire:loading.remove></i>
                    <i class="fas fa-spinner fa-spin mr-2" wire:loading></i>
                    <span wire:loading.remove>{{ __('hr.departments.delete') }}</span>
                    <span wire:loading>{{ __('hr.departments.deleting') }}...</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endif
