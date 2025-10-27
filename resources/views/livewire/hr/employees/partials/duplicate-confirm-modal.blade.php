<!-- Duplicate Document Confirmation Modal -->
@if($showDuplicateConfirmation)
<div class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center z-50" x-data="{}"
     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full mx-4"
         x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
        
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-amber-500 to-orange-600 text-white px-6 py-4 rounded-t-xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="bg-white bg-opacity-20 p-2 rounded-lg mr-3">
                        <i class="fas fa-exclamation-triangle text-white animate-pulse"></i>
                    </div>
                    <h3 class="text-lg font-semibold">{{ __('messages.document_exists_warning', ['type' => ucfirst(str_replace('_', ' ', $newDocumentType))]) }}</h3>
                </div>
            </div>
        </div>

        <!-- Modal Content -->
        <div class="p-6">
            <div class="mb-6">
                <div class="flex items-center mb-4">
                    <div class="bg-amber-100 p-3 rounded-full mr-4">
                        <i class="fas fa-file-alt text-amber-600 text-xl"></i>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900">{{ __('messages.existing_document') }}</h4>
                        <p class="text-sm text-gray-600">{{ $existingDocument->title ?? '' }}</p>
                        @if($existingDocument && $existingDocument->created_at)
                            <p class="text-xs text-gray-500">{{ __('messages.uploaded_on') }}: {{ \Carbon\Carbon::parse($existingDocument->created_at)->format('d/m/Y H:i') }}</p>
                        @endif
                    </div>
                </div>
                
                <div class="bg-gradient-to-r from-orange-50 to-amber-50 border border-orange-200 rounded-lg p-4">
                    <p class="text-sm text-gray-700 mb-2">
                        <i class="fas fa-info-circle text-orange-500 mr-2"></i>
                        {{ __('messages.replace_document_confirm') }}
                    </p>
                    <ul class="text-xs text-gray-600 space-y-1 ml-6">
                        <li>• {{ __('messages.old_document_will_be_deleted') }}</li>
                        <li>• {{ __('messages.new_document_will_be_saved') }}</li>
                        <li>• {{ __('messages.action_cannot_be_undone') }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="border-t border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 rounded-b-xl">
            <div class="flex justify-end space-x-3">
                <button type="button" wire:click="cancelDocumentReplacement"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                    <i class="fas fa-times mr-2"></i>
                    {{ __('messages.no_cancel') }}
                </button>
                <button type="button" wire:click="confirmDocumentReplacement"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-orange-500 to-amber-600 hover:from-orange-600 hover:to-amber-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200">
                    <i class="fas fa-check mr-2"></i>
                    {{ __('messages.yes_replace') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endif
