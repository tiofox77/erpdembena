{{-- Modal: Delete Training (DO NOT RENAME) --}}
@if($showDeleteModal)
<div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        
        {{-- Header --}}
        <div class="bg-gradient-to-r from-red-600 to-red-700 p-6 text-white">
            <div class="flex items-center space-x-3">
                <div class="bg-white/20 p-3 rounded-lg">
                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold">{{ __('messages.delete_training') }}</h2>
                    <p class="text-red-100">{{ __('messages.confirm_training_deletion') }}</p>
                </div>
            </div>
        </div>

        {{-- Body --}}
        @if($selectedTraining)
        <div class="p-6 space-y-4">
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-start space-x-3">
                    <i class="fas fa-exclamation-triangle text-red-500 text-lg mt-0.5"></i>
                    <div>
                        <h3 class="font-medium text-red-800 mb-1">{{ __('messages.warning') }}</h3>
                        <p class="text-sm text-red-700">{{ __('messages.training_deletion_warning') }}</p>
                    </div>
                </div>
            </div>

            {{-- Training Details --}}
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-3">{{ __('messages.training_to_delete') }}</h4>
                
                <div class="space-y-2 text-sm">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-user text-gray-400"></i>
                        <span class="text-gray-600">{{ __('messages.employee') }}:</span>
                        <span class="font-medium text-gray-900">{{ $selectedTraining->employee->full_name }}</span>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-graduation-cap text-gray-400"></i>
                        <span class="text-gray-600">{{ __('messages.training') }}:</span>
                        <span class="font-medium text-gray-900">{{ $selectedTraining->training_title }}</span>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-tag text-gray-400"></i>
                        <span class="text-gray-600">{{ __('messages.type') }}:</span>
                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded {{ $selectedTraining->training_type_color }}">
                            {{ __('messages.training_type_' . $selectedTraining->training_type) }}
                        </span>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-calendar text-gray-400"></i>
                        <span class="text-gray-600">{{ __('messages.start_date') }}:</span>
                        <span class="font-medium text-gray-900">{{ $selectedTraining->start_date?->format('d/m/Y') }}</span>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-info-circle text-gray-400"></i>
                        <span class="text-gray-600">{{ __('messages.status') }}:</span>
                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded {{ $selectedTraining->status_color }}">
                            {{ __('messages.training_status_' . $selectedTraining->status) }}
                        </span>
                    </div>

                    @if($selectedTraining->attachments && count($selectedTraining->attachments) > 0)
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-paperclip text-gray-400"></i>
                        <span class="text-gray-600">{{ __('messages.attachments') }}:</span>
                        <span class="font-medium text-gray-900">{{ count($selectedTraining->attachments) }} {{ __('messages.files') }}</span>
                    </div>
                    @endif
                </div>
            </div>

        </div>
        @endif

        {{-- Footer --}}
        <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t border-gray-200">
            <button wire:click="closeDeleteModal" 
                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                <i class="fas fa-times mr-2"></i>
                {{ __('messages.cancel') }}
            </button>
            <button wire:click="confirmDelete" 
                    class="px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                <i class="fas fa-trash mr-2"></i>
                {{ __('messages.delete_training') }}
            </button>
        </div>
    </div>
</div>
@endif
