{{-- Modal: View Training (DO NOT RENAME) --}}
@if($showViewModal)
<div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[95vh] overflow-hidden flex flex-col">
        
        {{-- Header --}}
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 p-6 text-white flex-shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-white/20 p-3 rounded-lg">
                        <i class="fas fa-graduation-cap text-2xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold">{{ __('messages.training_details') }}</h2>
                        <p class="text-purple-100">{{ __('messages.view_training_information') }}</p>
                    </div>
                </div>
                <button wire:click="closeViewModal" class="text-white/80 hover:text-white p-2 rounded-lg hover:bg-white/10 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        {{-- Body --}}
        @if($selectedTraining)
        <div class="flex-1 overflow-y-auto p-6 space-y-6">
            
            {{-- Employee Information --}}
            <div class="bg-purple-50 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-purple-800 mb-4 flex items-center">
                    <i class="fas fa-user mr-2"></i>
                    {{ __('messages.employee_information') }}
                </h3>
                <div class="flex items-center space-x-4">
                    <div class="h-16 w-16 bg-purple-500 rounded-full flex items-center justify-center">
                        <span class="text-white text-xl font-medium">
                            {{ substr($selectedTraining->employee->full_name, 0, 2) }}
                        </span>
                    </div>
                    <div>
                        <h4 class="text-xl font-semibold text-gray-900">{{ $selectedTraining->employee->full_name }}</h4>
                        <p class="text-gray-600">{{ __('messages.id_card') }}: {{ $selectedTraining->employee->id_card }}</p>
                        @if($selectedTraining->employee->department)
                            <p class="text-gray-500">{{ $selectedTraining->employee->department->name }}</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Training Basic Information --}}
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-info-circle mr-2"></i>
                    {{ __('messages.basic_information') }}
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('messages.training_title') }}</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $selectedTraining->training_title }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('messages.training_type') }}</label>
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $selectedTraining->training_type_color }}">
                            {{ __('messages.training_type_' . $selectedTraining->training_type) }}
                        </span>
                    </div>
                    
                    @if($selectedTraining->training_description)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('messages.description') }}</label>
                        <p class="text-gray-900 whitespace-pre-line">{{ $selectedTraining->training_description }}</p>
                    </div>
                    @endif
                    
                    @if($selectedTraining->provider)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('messages.provider') }}</label>
                        <p class="text-gray-900">{{ $selectedTraining->provider }}</p>
                    </div>
                    @endif
                    
                    @if($selectedTraining->location)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('messages.location') }}</label>
                        <p class="text-gray-900">{{ $selectedTraining->location }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Training Dates and Duration --}}
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    {{ __('messages.dates_and_duration') }}
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('messages.start_date') }}</label>
                        <p class="text-gray-900">{{ $selectedTraining->start_date?->format('d/m/Y') }}</p>
                    </div>
                    
                    @if($selectedTraining->end_date)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('messages.end_date') }}</label>
                        <p class="text-gray-900">{{ $selectedTraining->end_date->format('d/m/Y') }}</p>
                    </div>
                    @endif
                    
                    @if($selectedTraining->duration_hours)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('messages.duration_hours') }}</label>
                        <p class="text-gray-900">{{ number_format($selectedTraining->duration_hours, 1) }}h</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Status Information --}}
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-tasks mr-2"></i>
                    {{ __('messages.status_information') }}
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('messages.status') }}</label>
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $selectedTraining->status_color }}">
                            {{ __('messages.training_status_' . $selectedTraining->status) }}
                        </span>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('messages.completion_status') }}</label>
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $selectedTraining->completion_status_color }}">
                            {{ __('messages.completion_status_' . $selectedTraining->completion_status) }}
                        </span>
                    </div>
                    
                    @if($selectedTraining->completion_date)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('messages.completion_date') }}</label>
                        <p class="text-gray-900">{{ $selectedTraining->completion_date->format('d/m/Y') }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Budget Information --}}
            @if($selectedTraining->cost || $selectedTraining->budget_approved)
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-dollar-sign mr-2"></i>
                    {{ __('messages.budget_information') }}
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if($selectedTraining->cost)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('messages.cost') }}</label>
                        <p class="text-gray-900">{{ number_format($selectedTraining->cost, 2) }} €</p>
                    </div>
                    @endif
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('messages.budget_status') }}</label>
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $selectedTraining->budget_approved ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $selectedTraining->budget_approved ? __('messages.approved') : __('messages.not_approved') }}
                        </span>
                    </div>
                </div>
            </div>
            @endif

            {{-- Certification Information --}}
            @if($selectedTraining->certification_received)
            <div class="bg-green-50 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-green-800 mb-4 flex items-center">
                    <i class="fas fa-certificate mr-2"></i>
                    {{ __('messages.certification') }}
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-green-700 mb-1">{{ __('messages.certification_status') }}</label>
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i>
                            {{ __('messages.certification_received') }}
                        </span>
                    </div>
                    
                    @if($selectedTraining->certification_expiry_date)
                    <div>
                        <label class="block text-sm font-medium text-green-700 mb-1">{{ __('messages.expiry_date') }}</label>
                        <p class="text-green-900">{{ $selectedTraining->certification_expiry_date->format('d/m/Y') }}</p>
                        
                        @if($selectedTraining->is_expired)
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded bg-red-100 text-red-800 mt-1">
                                <i class="fas fa-exclamation-triangle mr-1"></i>{{ __('messages.expired') }}
                            </span>
                        @elseif($selectedTraining->is_expiring_soon)
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded bg-yellow-100 text-yellow-800 mt-1">
                                <i class="fas fa-clock mr-1"></i>{{ __('messages.expiring_soon') }}
                            </span>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Trainer Information --}}
            @if($selectedTraining->trainer_name || $selectedTraining->trainer_email)
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-chalkboard-teacher mr-2"></i>
                    {{ __('messages.trainer_information') }}
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if($selectedTraining->trainer_name)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('messages.trainer_name') }}</label>
                        <p class="text-gray-900">{{ $selectedTraining->trainer_name }}</p>
                    </div>
                    @endif
                    
                    @if($selectedTraining->trainer_email)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('messages.trainer_email') }}</label>
                        <p class="text-gray-900">{{ $selectedTraining->trainer_email }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Evaluation and Feedback --}}
            @if($selectedTraining->evaluation_score || $selectedTraining->feedback)
            <div class="bg-blue-50 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-blue-800 mb-4 flex items-center">
                    <i class="fas fa-star mr-2"></i>
                    {{ __('messages.evaluation_and_feedback') }}
                </h3>
                
                <div class="space-y-4">
                    @if($selectedTraining->evaluation_score)
                    <div>
                        <label class="block text-sm font-medium text-blue-700 mb-1">{{ __('messages.evaluation_score') }}</label>
                        <div class="flex items-center space-x-3">
                            <span class="text-2xl font-bold text-blue-900">{{ number_format($selectedTraining->evaluation_score, 1) }}/10</span>
                            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $selectedTraining->evaluation_color }}">
                                {{ $selectedTraining->evaluation_rating }}
                            </span>
                        </div>
                    </div>
                    @endif
                    
                    @if($selectedTraining->feedback)
                    <div>
                        <label class="block text-sm font-medium text-blue-700 mb-1">{{ __('messages.feedback') }}</label>
                        <p class="text-blue-900 whitespace-pre-line">{{ $selectedTraining->feedback }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Skills and Development --}}
            @if($selectedTraining->skills_acquired || $selectedTraining->next_steps)
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-lightbulb mr-2"></i>
                    {{ __('messages.skills_and_development') }}
                </h3>
                
                <div class="space-y-4">
                    @if($selectedTraining->skills_acquired)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('messages.skills_acquired') }}</label>
                        <p class="text-gray-900 whitespace-pre-line">{{ $selectedTraining->skills_acquired }}</p>
                    </div>
                    @endif
                    
                    @if($selectedTraining->next_steps)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('messages.next_steps') }}</label>
                        <p class="text-gray-900 whitespace-pre-line">{{ $selectedTraining->next_steps }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Notes --}}
            @if($selectedTraining->notes)
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-sticky-note mr-2"></i>
                    {{ __('messages.notes') }}
                </h3>
                <p class="text-gray-900 whitespace-pre-line">{{ $selectedTraining->notes }}</p>
            </div>
            @endif

            {{-- Attachments --}}
            @if($selectedTraining->attachments && count($selectedTraining->attachments) > 0)
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-paperclip mr-2"></i>
                    {{ __('messages.attachments') }}
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($selectedTraining->attachments as $filename)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-file text-blue-500"></i>
                                <span class="text-sm text-gray-700">{{ $filename }}</span>
                            </div>
                            <button wire:click="downloadAttachment({{ $selectedTraining->id }}, '{{ $filename }}')"
                                    class="text-blue-600 hover:text-blue-800 transition-colors">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Creation Info --}}
            <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-600">
                <p>{{ __('messages.created_by') }}: {{ $selectedTraining->createdByUser->name ?? __('messages.system') }}</p>
                <p>{{ __('messages.created_at') }}: {{ $selectedTraining->created_at->format('d/m/Y H:i') }}</p>
                @if($selectedTraining->updated_at != $selectedTraining->created_at)
                    <p>{{ __('messages.updated_at') }}: {{ $selectedTraining->updated_at->format('d/m/Y H:i') }}</p>
                @endif
            </div>
        </div>
        @endif

        {{-- Footer --}}
        <div class="flex-shrink-0 bg-gray-50 px-6 py-4 flex justify-between items-center border-t border-gray-200">
            <div class="flex space-x-3">
                @if($selectedTraining)
                <button wire:click="edit({{ $selectedTraining->id }})" 
                        class="px-4 py-2 border border-purple-300 rounded-lg text-sm font-medium text-purple-700 bg-white hover:bg-purple-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors">
                    <i class="fas fa-edit mr-2"></i>
                    {{ __('messages.edit') }}
                </button>
                @endif
            </div>
            
            <button wire:click="closeViewModal" 
                    class="px-6 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                <i class="fas fa-times mr-2"></i>
                {{ __('messages.close') }}
            </button>
        </div>
    </div>
</div>
@endif
