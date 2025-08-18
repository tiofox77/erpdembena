{{-- Delete Performance Evaluation Modal --}}
@if($showDeleteModal && $selectedEmployee)
<div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        
        {{-- Header --}}
        <div class="bg-gradient-to-r from-red-600 to-red-700 p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-white/20 p-2 rounded-lg">
                        <i class="fas fa-exclamation-triangle text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold">{{ __('messages.delete_evaluation') }}</h2>
                        <p class="text-red-100">{{ __('messages.confirm_deletion') }}</p>
                    </div>
                </div>
                <button wire:click="closeDeleteModal" class="text-white/80 hover:text-white p-2">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        {{-- Content --}}
        <div class="p-6">
            @php
                $evaluation = \App\Models\HR\PerformanceEvaluation::find($performanceEvaluationId);
            @endphp

            {{-- Employee Info --}}
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <div class="flex items-center space-x-3">
                    <div class="h-12 w-12 bg-red-500 rounded-full flex items-center justify-center">
                        <span class="text-white font-medium">
                            {{ substr($selectedEmployee->full_name, 0, 1) }}
                        </span>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $selectedEmployee->full_name }}</h3>
                        <p class="text-gray-600">{{ __('messages.id_card') }}: {{ $selectedEmployee->id_card }}</p>
                    </div>
                </div>
            </div>

            @if($evaluation)
                {{-- Evaluation Details --}}
                <div class="space-y-3 mb-6">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-500">{{ __('messages.evaluation_type') }}:</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $evaluation->evaluation_type_color }}">
                            {{ __('messages.evaluation_type_' . $evaluation->evaluation_type) }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-500">{{ __('messages.status') }}:</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $evaluation->status_color }}">
                            {{ __('messages.evaluation_status_' . $evaluation->status) }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-500">{{ __('messages.evaluation_period') }}:</span>
                        <span class="text-sm text-gray-900">
                            {{ $evaluation->period_start->format('d/m/Y') }} - {{ $evaluation->period_end->format('d/m/Y') }}
                        </span>
                    </div>

                    @if($evaluation->overall_score)
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-500">{{ __('messages.overall_score') }}:</span>
                            <div class="flex items-center space-x-2">
                                <span class="text-lg font-bold text-indigo-600">{{ number_format($evaluation->overall_score, 1) }}</span>
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $evaluation->performance_rating_color }}">
                                    {{ __('messages.rating_' . strtolower(str_replace(' ', '_', $evaluation->performance_rating))) }}
                                </span>
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-500">{{ __('messages.evaluation_date') }}:</span>
                        <span class="text-sm text-gray-900">{{ $evaluation->evaluation_date->format('d/m/Y') }}</span>
                    </div>
                </div>
            @endif

            {{-- Warning Message --}}
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">
                            {{ __('messages.irreversible_action') }}
                        </h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p>{{ __('messages.evaluation_delete_warning') }}</p>
                            <ul class="list-disc list-inside mt-2 space-y-1">
                                <li>{{ __('messages.evaluation_data_will_be_lost') }}</li>
                                <li>{{ __('messages.attachments_will_be_deleted') }}</li>
                                <li>{{ __('messages.performance_history_affected') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Confirmation --}}
            <div class="text-center">
                <p class="text-gray-600 mb-4">
                    {{ __('messages.type_delete_to_confirm') }}
                </p>
                <input wire:model="deleteConfirmation" 
                       type="text" 
                       placeholder="{{ __('messages.type_delete') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-center">
            </div>
        </div>

        {{-- Footer --}}
        <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t border-gray-200">
            <button wire:click="closeDeleteModal" 
                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                {{ __('messages.cancel') }}
            </button>
            <button wire:click="delete" 
                    class="px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled="{{ strtolower($deleteConfirmation ?? '') !== 'delete' }}">
                <div wire:loading wire:target="delete" class="inline-flex items-center">
                    <i class="fas fa-spinner fa-spin mr-2"></i>
                </div>
                <div wire:loading.remove wire:target="delete" class="inline-flex items-center">
                    <i class="fas fa-trash mr-2"></i>
                </div>
                {{ __('messages.delete_evaluation') }}
            </button>
        </div>
    </div>
</div>
@endif
