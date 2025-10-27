{{-- Delete Disciplinary Measure Confirmation Modal --}}
@if($showDeleteModal)
<div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4">
        
        {{-- Header --}}
        <div class="bg-gradient-to-r from-red-600 to-red-700 p-6 text-white rounded-t-2xl">
            <div class="flex items-center space-x-3">
                <div class="bg-white/20 p-2 rounded-lg">
                    <i class="fas fa-exclamation-triangle text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold">{{ __('messages.delete_measure') }}</h2>
                    <p class="text-red-100 text-sm">{{ __('messages.confirm_delete_action') }}</p>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="p-6">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                    <i class="fas fa-gavel text-red-600 text-2xl"></i>
                </div>
                
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                    {{ __('messages.are_you_sure') }}
                </h3>
                
                <p class="text-sm text-gray-500 mb-6">
                    {{ __('messages.delete_measure_warning') }}
                </p>

                {{-- Measure Info --}}
                @php
                    $measure = App\Models\HR\DisciplinaryMeasure::find($disciplinaryMeasureId);
                @endphp
                
                @if($measure)
                    <div class="bg-gray-50 p-4 rounded-lg mb-6 text-left">
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">{{ __('messages.employee') }}:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $measure->employee->full_name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">{{ __('messages.measure_type') }}:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $measure->measure_type_name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">{{ __('messages.applied_date') }}:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $measure->applied_date->format('d/m/Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">{{ __('messages.reason') }}:</span>
                                <span class="text-sm font-medium text-gray-900">{{ Str::limit($measure->reason, 30) }}</span>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-6">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                        <p class="text-sm text-yellow-800">
                            {{ __('messages.this_action_cannot_be_undone') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t border-gray-200 rounded-b-2xl">
            <button wire:click="closeDeleteModal" 
                    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                {{ __('messages.cancel') }}
            </button>
            <button wire:click="confirmDelete" 
                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                <div wire:loading wire:target="confirmDelete" class="inline-flex items-center">
                    <i class="fas fa-spinner fa-spin mr-2"></i>
                </div>
                <div wire:loading.remove wire:target="confirmDelete" class="inline-flex items-center">
                    <i class="fas fa-trash mr-2"></i>
                </div>
                {{ __('messages.delete') }}
            </button>
        </div>
    </div>
</div>
@endif
