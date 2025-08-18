{{-- View Performance Evaluation Modal --}}
@if($showViewModal && $selectedEmployee)
<div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden">
        
        {{-- Header --}}
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-white/20 p-2 rounded-lg">
                        <i class="fas fa-chart-line text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold">{{ __('messages.view_evaluation') }}</h2>
                        <p class="text-indigo-100">{{ __('messages.performance_evaluation_details') }}</p>
                    </div>
                </div>
                <button wire:click="closeViewModal" class="text-white/80 hover:text-white p-2">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        {{-- Content --}}
        <div class="flex-1 p-6 overflow-y-auto">
            @php
                $evaluation = \App\Models\HR\PerformanceEvaluation::with(['employee', 'evaluatedByUser'])->find($performanceEvaluationId);
            @endphp
            
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
                    <div>
                        <h4 class="text-xl font-bold text-gray-900">{{ $selectedEmployee->full_name }}</h4>
                        <p class="text-gray-600">{{ __('messages.id_card') }}: {{ $selectedEmployee->id_card }}</p>
                        @if($selectedEmployee->department)
                            <p class="text-gray-500 text-sm">{{ $selectedEmployee->department->name }}</p>
                        @endif
                    </div>
                </div>
            </div>

            @if($evaluation)
                {{-- Evaluation Overview --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white border border-gray-200 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-500 mb-1">{{ __('messages.evaluation_type') }}</h4>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium {{ $evaluation->evaluation_type_color }}">
                            {{ __('messages.evaluation_type_' . $evaluation->evaluation_type) }}
                        </span>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-500 mb-1">{{ __('messages.status') }}</h4>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium {{ $evaluation->status_color }}">
                            {{ __('messages.evaluation_status_' . $evaluation->status) }}
                        </span>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-500 mb-1">{{ __('messages.evaluation_period') }}</h4>
                        <p class="text-sm font-medium text-gray-900">
                            {{ $evaluation->period_start->format('d/m/Y') }} - {{ $evaluation->period_end->format('d/m/Y') }}
                        </p>
                    </div>
                </div>

                {{-- Overall Performance --}}
                @if($evaluation->overall_score)
                    <div class="bg-indigo-50 p-6 rounded-lg mb-6">
                        <h3 class="text-lg font-semibold text-indigo-800 mb-4 flex items-center">
                            <i class="fas fa-trophy mr-2"></i>
                            {{ __('messages.overall_performance') }}
                        </h3>
                        <div class="flex items-center space-x-6">
                            <div class="text-center">
                                <div class="text-4xl font-bold text-indigo-600">{{ number_format($evaluation->overall_score, 1) }}</div>
                                <div class="text-sm text-gray-600">{{ __('messages.out_of_10') }}</div>
                            </div>
                            <div class="flex-1">
                                <span class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium {{ $evaluation->performance_rating_color }}">
                                    {{ __('messages.rating_' . strtolower(str_replace(' ', '_', $evaluation->performance_rating))) }}
                                </span>
                                <div class="mt-2 bg-gray-200 rounded-full h-2">
                                    <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ ($evaluation->overall_score / 10) * 100 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Detailed Scores --}}
                <div class="bg-gray-50 p-6 rounded-lg mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-star mr-2"></i>
                        {{ __('messages.detailed_scores') }}
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @if($evaluation->goals_achievement)
                            <div class="bg-white p-4 rounded-lg border">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">{{ __('messages.goals_achievement') }}</h4>
                                <div class="flex items-center justify-between">
                                    <span class="text-2xl font-bold text-indigo-600">{{ number_format($evaluation->goals_achievement, 1) }}</span>
                                    <div class="w-16 bg-gray-200 rounded-full h-2">
                                        <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ ($evaluation->goals_achievement / 10) * 100 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($evaluation->technical_skills)
                            <div class="bg-white p-4 rounded-lg border">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">{{ __('messages.technical_skills') }}</h4>
                                <div class="flex items-center justify-between">
                                    <span class="text-2xl font-bold text-blue-600">{{ number_format($evaluation->technical_skills, 1) }}</span>
                                    <div class="w-16 bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ ($evaluation->technical_skills / 10) * 100 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($evaluation->soft_skills)
                            <div class="bg-white p-4 rounded-lg border">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">{{ __('messages.soft_skills') }}</h4>
                                <div class="flex items-center justify-between">
                                    <span class="text-2xl font-bold text-green-600">{{ number_format($evaluation->soft_skills, 1) }}</span>
                                    <div class="w-16 bg-gray-200 rounded-full h-2">
                                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ ($evaluation->soft_skills / 10) * 100 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($evaluation->attendance_punctuality)
                            <div class="bg-white p-4 rounded-lg border">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">{{ __('messages.attendance_punctuality') }}</h4>
                                <div class="flex items-center justify-between">
                                    <span class="text-2xl font-bold text-purple-600">{{ number_format($evaluation->attendance_punctuality, 1) }}</span>
                                    <div class="w-16 bg-gray-200 rounded-full h-2">
                                        <div class="bg-purple-600 h-2 rounded-full" style="width: {{ ($evaluation->attendance_punctuality / 10) * 100 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($evaluation->teamwork_collaboration)
                            <div class="bg-white p-4 rounded-lg border">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">{{ __('messages.teamwork_collaboration') }}</h4>
                                <div class="flex items-center justify-between">
                                    <span class="text-2xl font-bold text-yellow-600">{{ number_format($evaluation->teamwork_collaboration, 1) }}</span>
                                    <div class="w-16 bg-gray-200 rounded-full h-2">
                                        <div class="bg-yellow-600 h-2 rounded-full" style="width: {{ ($evaluation->teamwork_collaboration / 10) * 100 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($evaluation->initiative_innovation)
                            <div class="bg-white p-4 rounded-lg border">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">{{ __('messages.initiative_innovation') }}</h4>
                                <div class="flex items-center justify-between">
                                    <span class="text-2xl font-bold text-red-600">{{ number_format($evaluation->initiative_innovation, 1) }}</span>
                                    <div class="w-16 bg-gray-200 rounded-full h-2">
                                        <div class="bg-red-600 h-2 rounded-full" style="width: {{ ($evaluation->initiative_innovation / 10) * 100 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($evaluation->quality_of_work)
                            <div class="bg-white p-4 rounded-lg border">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">{{ __('messages.quality_of_work') }}</h4>
                                <div class="flex items-center justify-between">
                                    <span class="text-2xl font-bold text-teal-600">{{ number_format($evaluation->quality_of_work, 1) }}</span>
                                    <div class="w-16 bg-gray-200 rounded-full h-2">
                                        <div class="bg-teal-600 h-2 rounded-full" style="width: {{ ($evaluation->quality_of_work / 10) * 100 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Feedback Section --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    @if($evaluation->strengths)
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h4 class="text-lg font-semibold text-green-800 mb-3 flex items-center">
                                <i class="fas fa-thumbs-up mr-2"></i>
                                {{ __('messages.strengths') }}
                            </h4>
                            <p class="text-gray-700 whitespace-pre-wrap">{{ $evaluation->strengths }}</p>
                        </div>
                    @endif

                    @if($evaluation->areas_for_improvement)
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <h4 class="text-lg font-semibold text-yellow-800 mb-3 flex items-center">
                                <i class="fas fa-arrow-up mr-2"></i>
                                {{ __('messages.areas_for_improvement') }}
                            </h4>
                            <p class="text-gray-700 whitespace-pre-wrap">{{ $evaluation->areas_for_improvement }}</p>
                        </div>
                    @endif
                </div>

                @if($evaluation->development_plan)
                    <div class="bg-blue-50 p-4 rounded-lg mb-6">
                        <h4 class="text-lg font-semibold text-blue-800 mb-3 flex items-center">
                            <i class="fas fa-road mr-2"></i>
                            {{ __('messages.development_plan') }}
                        </h4>
                        <p class="text-gray-700 whitespace-pre-wrap">{{ $evaluation->development_plan }}</p>
                    </div>
                @endif

                @if($evaluation->additional_comments)
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h4 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-comment mr-2"></i>
                            {{ __('messages.additional_comments') }}
                        </h4>
                        <p class="text-gray-700 whitespace-pre-wrap">{{ $evaluation->additional_comments }}</p>
                    </div>
                @endif

                {{-- Attachments --}}
                @if($evaluation->attachments && count($evaluation->attachments) > 0)
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h4 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-paperclip mr-2"></i>
                            {{ __('messages.attachments') }}
                            <span class="ml-2 bg-gray-200 text-gray-600 text-xs px-2 py-1 rounded-full">
                                {{ count($evaluation->attachments) }}
                            </span>
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($evaluation->attachments as $index => $attachment)
                                <div class="bg-white p-3 rounded-lg border border-gray-200 hover:shadow-sm transition-shadow">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            @if(str_contains($attachment['type'], 'pdf'))
                                                <i class="fas fa-file-pdf text-red-500 text-xl"></i>
                                            @elseif(str_contains($attachment['type'], 'image'))
                                                <i class="fas fa-file-image text-blue-500 text-xl"></i>
                                            @else
                                                <i class="fas fa-file-alt text-gray-500 text-xl"></i>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $attachment['name'] }}</p>
                                            <p class="text-xs text-gray-500">{{ number_format($attachment['size'] / 1024, 1) }} KB</p>
                                        </div>
                                        <button wire:click="downloadAttachment({{ $index }})" 
                                                class="text-indigo-600 hover:text-indigo-800 p-1">
                                            <i class="fas fa-download"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Evaluation Information --}}
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        {{ __('messages.evaluation_information') }}
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('messages.evaluation_date') }}</p>
                            <p class="text-sm text-gray-900">{{ $evaluation->evaluation_date->format('d/m/Y') }}</p>
                        </div>
                        @if($evaluation->next_evaluation_date)
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('messages.next_evaluation_date') }}</p>
                                <p class="text-sm text-gray-900">{{ $evaluation->next_evaluation_date->format('d/m/Y') }}</p>
                            </div>
                        @endif
                        @if($evaluation->evaluatedByUser)
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('messages.evaluated_by') }}</p>
                                <div class="flex items-center space-x-2">
                                    <div class="h-6 w-6 bg-indigo-500 rounded-full flex items-center justify-center">
                                        <span class="text-white text-xs font-medium">
                                            {{ substr($evaluation->evaluatedByUser->name, 0, 1) }}
                                        </span>
                                    </div>
                                    <span class="text-sm text-gray-900">{{ $evaluation->evaluatedByUser->name }}</span>
                                </div>
                                <p class="text-xs text-gray-500">
                                    {{ __('messages.created_at') }}: {{ $evaluation->created_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        @endif
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
            <button wire:click="edit({{ $performanceEvaluationId }})" 
                    class="px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                <i class="fas fa-edit mr-2"></i>
                {{ __('messages.edit') }}
            </button>
        </div>
    </div>
</div>
@endif
