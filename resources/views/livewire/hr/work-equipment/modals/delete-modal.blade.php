{{-- Delete Confirmation Modal --}}
@if($showDeleteModal)
<div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        
        {{-- Header --}}
        <div class="bg-gradient-to-r from-red-600 to-red-700 p-6 text-white">
            <div class="flex items-center space-x-3">
                <div class="bg-white/20 p-2 rounded-lg">
                    <i class="fas fa-exclamation-triangle text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold">
                        {{ __('messages.confirm_deletion') }}
                    </h2>
                    <p class="text-red-100 text-sm">{{ __('messages.this_action_cannot_be_undone') }}</p>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="p-6">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <div class="h-12 w-12 rounded-full bg-red-100 flex items-center justify-center">
                        <i class="fas fa-trash text-red-600 text-xl"></i>
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-gray-700">
                        {{ __('messages.delete_equipment_warning') }}
                    </p>
                    <p class="text-sm text-gray-500 mt-2">
                        {{ __('messages.are_you_sure') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t">
            <button wire:click="closeDeleteModal" type="button" 
                class="px-6 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                <i class="fas fa-times mr-2"></i>
                {{ __('messages.cancel') }}
            </button>
            <button wire:click="delete" type="button" 
                wire:loading.attr="disabled"
                wire:target="delete"
                class="px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="delete">
                    <i class="fas fa-trash mr-2"></i>
                    {{ __('messages.delete') }}
                </span>
                <span wire:loading wire:target="delete" class="flex items-center">
                    <svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ __('messages.deleting') }}...
                </span>
            </button>
        </div>
    </div>
</div>
@endif
