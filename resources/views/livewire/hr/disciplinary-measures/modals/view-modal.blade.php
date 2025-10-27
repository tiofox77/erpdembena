{{-- View Disciplinary Measure Modal --}}
@if($showViewModal && $selectedEmployee)
<div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden">
        
        {{-- Header --}}
        <div class="bg-gradient-to-r from-gray-600 to-gray-700 p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-white/20 p-2 rounded-lg">
                        <i class="fas fa-eye text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold">{{ __('messages.view_measure') }}</h2>
                        <p class="text-gray-100">{{ __('messages.disciplinary_measure_details') }}</p>
                    </div>
                </div>
                <button wire:click="closeViewModal" class="text-white/80 hover:text-white p-2">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        {{-- Content --}}
        <div class="flex-1 p-6 overflow-y-auto">
            
            {{-- Employee Information --}}
            <div class="bg-blue-50 p-4 rounded-lg mb-6">
                <h3 class="text-lg font-semibold text-blue-800 mb-3 flex items-center">
                    <i class="fas fa-user mr-2"></i>
                    {{ __('messages.employee_information') }}
                </h3>
                <div class="flex items-center space-x-4">
                    <div class="h-16 w-16 bg-blue-500 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold text-xl">
                            {{ substr($selectedEmployee->full_name, 0, 1) }}
                        </span>
                    </div>
                    <div class="flex-1">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-blue-600 font-medium">{{ __('messages.name') }}</p>
                                <p class="text-lg text-blue-800 font-semibold">{{ $selectedEmployee->full_name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-blue-600 font-medium">{{ __('messages.id_card') }}</p>
                                <p class="text-lg text-blue-800 font-semibold">{{ $selectedEmployee->id_card }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-blue-600 font-medium">{{ __('messages.department') }}</p>
                                <p class="text-lg text-blue-800 font-semibold">{{ $selectedEmployee->department->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-blue-600 font-medium">{{ __('messages.position') }}</p>
                                <p class="text-lg text-blue-800 font-semibold">{{ $selectedEmployee->position->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Measure Details --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                
                {{-- Measure Type --}}
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-600 font-medium mb-1">{{ __('messages.measure_type') }}</p>
                    <div class="flex items-center">
                        @php
                            $measure = App\Models\HR\DisciplinaryMeasure::find($disciplinaryMeasureId);
                        @endphp
                        @if($measure)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $measure->measure_type_color }}">
                                {{ $measure->measure_type_name }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Status --}}
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-600 font-medium mb-1">{{ __('messages.status') }}</p>
                    <div class="flex items-center">
                        @if($measure)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $measure->status_color }}">
                                {{ $measure->status_name }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Applied Date --}}
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-600 font-medium mb-1">{{ __('messages.applied_date') }}</p>
                    <p class="text-lg text-gray-800 font-semibold">
                        {{ \Carbon\Carbon::parse($appliedDate)->format('d/m/Y') }}
                    </p>
                </div>

                {{-- Effective Date --}}
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-600 font-medium mb-1">{{ __('messages.effective_date') }}</p>
                    <p class="text-lg text-gray-800 font-semibold">
                        {{ $effectiveDate ? \Carbon\Carbon::parse($effectiveDate)->format('d/m/Y') : __('messages.not_specified') }}
                    </p>
                </div>
            </div>

            {{-- Reason --}}
            <div class="bg-yellow-50 p-4 rounded-lg mb-6">
                <h4 class="text-lg font-semibold text-yellow-800 mb-2 flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    {{ __('messages.reason') }}
                </h4>
                <p class="text-yellow-700">{{ $reason }}</p>
            </div>

            {{-- Description --}}
            <div class="bg-red-50 p-4 rounded-lg mb-6">
                <h4 class="text-lg font-semibold text-red-800 mb-2 flex items-center">
                    <i class="fas fa-file-alt mr-2"></i>
                    {{ __('messages.description') }}
                </h4>
                <div class="text-red-700 whitespace-pre-wrap">{{ $description }}</div>
            </div>

            {{-- Notes --}}
            @if($notes)
                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-2 flex items-center">
                        <i class="fas fa-sticky-note mr-2"></i>
                        {{ __('messages.notes') }}
                    </h4>
                    <div class="text-gray-700 whitespace-pre-wrap">{{ $notes }}</div>
                </div>
            @endif

            {{-- Attachments --}}
            @if($existingAttachments && count($existingAttachments) > 0)
                <div class="bg-green-50 p-4 rounded-lg mb-6">
                    <h4 class="text-lg font-semibold text-green-800 mb-3 flex items-center">
                        <i class="fas fa-paperclip mr-2"></i>
                        {{ __('messages.attachments') }}
                        <span class="ml-2 px-2 py-1 bg-green-100 text-green-800 rounded-full text-sm">
                            {{ count($existingAttachments) }}
                        </span>
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($existingAttachments as $attachment)
                            <div class="flex items-center space-x-3 bg-white p-3 rounded-lg border border-green-200">
                                <div class="flex-shrink-0">
                                    @php
                                        $extension = pathinfo($attachment['original_name'] ?? '', PATHINFO_EXTENSION);
                                        $iconClass = match(strtolower($extension)) {
                                            'pdf' => 'fas fa-file-pdf text-red-500',
                                            'doc', 'docx' => 'fas fa-file-word text-blue-500',
                                            'jpg', 'jpeg', 'png' => 'fas fa-file-image text-green-500',
                                            default => 'fas fa-file text-gray-500'
                                        };
                                    @endphp
                                    <i class="{{ $iconClass }} text-2xl"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $attachment['original_name'] ?? 'Attachment' }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ isset($attachment['size']) ? number_format($attachment['size'] / 1024, 1) . ' KB' : '' }}
                                    </p>
                                </div>
                                <div class="flex-shrink-0">
                                    <a href="{{ Storage::url($attachment['path']) }}" target="_blank" 
                                       class="text-green-600 hover:text-green-800">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Applied By Information --}}
            @if($measure && $measure->appliedByUser)
                <div class="bg-gray-100 p-4 rounded-lg">
                    <h4 class="text-sm font-semibold text-gray-600 mb-2">{{ __('messages.applied_by') }}</h4>
                    <div class="flex items-center space-x-2">
                        <div class="h-8 w-8 bg-gray-500 rounded-full flex items-center justify-center">
                            <span class="text-white font-medium text-xs">
                                {{ substr($measure->appliedByUser->name, 0, 1) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $measure->appliedByUser->name }}</p>
                            <p class="text-xs text-gray-500">
                                {{ __('messages.created_at') }}: {{ $measure->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Footer --}}
        <div class="flex-shrink-0 bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t border-gray-200">
            <button wire:click="closeViewModal" 
                    class="px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                <i class="fas fa-times mr-2"></i>
                {{ __('messages.close') }}
            </button>
            <button wire:click="edit({{ $disciplinaryMeasureId }})" 
                    class="px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                <i class="fas fa-edit mr-2"></i>
                {{ __('messages.edit') }}
            </button>
        </div>
    </div>
</div>
@endif
