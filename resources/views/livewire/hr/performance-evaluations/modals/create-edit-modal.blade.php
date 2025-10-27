{{-- Create/Edit Performance Evaluation Modal --}}
@if($showModal)
<div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 p-4" x-data="{ loading: false }">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl max-h-[90vh] flex flex-col overflow-hidden">
        
        {{-- Header --}}
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-white/20 p-2 rounded-lg">
                        <i class="fas fa-chart-line text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold">
                            {{ $isEditing ? __('messages.edit_evaluation') : __('messages.new_evaluation') }}
                        </h2>
                        <p class="text-indigo-100">{{ __('messages.performance_evaluation_form_description') }}</p>
                    </div>
                </div>
                <button wire:click="closeModal" class="text-white/80 hover:text-white p-2">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        {{-- Form Content --}}
        <div class="flex-1 p-6 overflow-y-auto">
            <form wire:submit.prevent="save" class="space-y-6">
                
                {{-- Employee Selection --}}
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.employee') }} <span class="text-red-500">*</span>
                    </label>
                    
                    @if($selectedEmployee)
                        <div class="flex items-center justify-between bg-white p-3 rounded-lg border">
                            <div class="flex items-center space-x-3">
                                <div class="h-10 w-10 bg-indigo-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-medium text-sm">
                                        {{ substr($selectedEmployee->full_name, 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $selectedEmployee->full_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $selectedEmployee->id_card }}</div>
                                </div>
                            </div>
                            <button type="button" wire:click="openEmployeeSearch" class="text-indigo-600 hover:text-indigo-800">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    @else
                        <button type="button" wire:click="openEmployeeSearch" 
                                class="w-full flex items-center justify-center px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-indigo-400 hover:text-indigo-600 transition-colors">
                            <i class="fas fa-user-plus mr-2"></i>
                            {{ __('messages.select_employee') }}
                        </button>
                    @endif
                    @error('employee_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Evaluation Type --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.evaluation_type') }} <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="evaluation_type" class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @foreach(\App\Models\HR\PerformanceEvaluation::EVALUATION_TYPES as $key => $type)
                                <option value="{{ $key }}">{{ __('messages.evaluation_type_' . $key) }}</option>
                            @endforeach
                        </select>
                        @error('evaluation_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.status') }} <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="status" class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @foreach(\App\Models\HR\PerformanceEvaluation::STATUSES as $key => $status)
                                <option value="{{ $key }}">{{ __('messages.evaluation_status_' . $key) }}</option>
                            @endforeach
                        </select>
                        @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Evaluation Period --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.period_start') }} <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="period_start" type="date" 
                               class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @error('period_start') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.period_end') }} <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="period_end" type="date" 
                               class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @error('period_end') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Evaluation Dates --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.evaluation_date') }} <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="evaluation_date" type="date" 
                               class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @error('evaluation_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.next_evaluation_date') }}
                        </label>
                        <input wire:model="next_evaluation_date" type="date" 
                               class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @error('next_evaluation_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Performance Scores Section --}}
                <div class="bg-indigo-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-indigo-800 mb-4 flex items-center">
                        <i class="fas fa-star mr-2"></i>
                        {{ __('messages.performance_scores') }}
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {{-- Goals Achievement --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.goals_achievement') }}
                            </label>
                            <input wire:model.live="goals_achievement" type="number" step="0.1" min="0" max="10"
                                   class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="0.0 - 10.0">
                            @error('goals_achievement') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Technical Skills --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.technical_skills') }}
                            </label>
                            <input wire:model.live="technical_skills" type="number" step="0.1" min="0" max="10"
                                   class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="0.0 - 10.0">
                            @error('technical_skills') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Soft Skills --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.soft_skills') }}
                            </label>
                            <input wire:model.live="soft_skills" type="number" step="0.1" min="0" max="10"
                                   class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="0.0 - 10.0">
                            @error('soft_skills') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Attendance & Punctuality --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.attendance_punctuality') }}
                            </label>
                            <input wire:model.live="attendance_punctuality" type="number" step="0.1" min="0" max="10"
                                   class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="0.0 - 10.0">
                            @error('attendance_punctuality') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Teamwork & Collaboration --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.teamwork_collaboration') }}
                            </label>
                            <input wire:model.live="teamwork_collaboration" type="number" step="0.1" min="0" max="10"
                                   class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="0.0 - 10.0">
                            @error('teamwork_collaboration') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Initiative & Innovation --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.initiative_innovation') }}
                            </label>
                            <input wire:model.live="initiative_innovation" type="number" step="0.1" min="0" max="10"
                                   class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="0.0 - 10.0">
                            @error('initiative_innovation') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Quality of Work --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.quality_of_work') }}
                            </label>
                            <input wire:model.live="quality_of_work" type="number" step="0.1" min="0" max="10"
                                   class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="0.0 - 10.0">
                            @error('quality_of_work') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Overall Score (Auto-calculated) --}}
                        <div class="md:col-span-2 lg:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.overall_score') }}
                                <span class="text-xs text-gray-500">({{ __('messages.auto_calculated') }})</span>
                            </label>
                            <div class="flex items-center space-x-3">
                                <input wire:model="overall_score" type="number" step="0.01" min="0" max="10" readonly
                                       class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="0.00">
                                @if($overall_score)
                                    <div class="text-2xl">
                                        @if($overall_score >= 9.0)
                                            <span class="text-green-500">⭐</span>
                                        @elseif($overall_score >= 8.0)
                                            <span class="text-blue-500">👍</span>
                                        @elseif($overall_score >= 7.0)
                                            <span class="text-indigo-500">✅</span>
                                        @elseif($overall_score >= 6.0)
                                            <span class="text-yellow-500">⚡</span>
                                        @else
                                            <span class="text-red-500">⚠️</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            @error('overall_score') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                {{-- Feedback Section --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Strengths --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.strengths') }}
                        </label>
                        <textarea wire:model="strengths" rows="4" 
                                  class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="{{ __('messages.strengths_placeholder') }}"></textarea>
                        @error('strengths') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    {{-- Areas for Improvement --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.areas_for_improvement') }}
                        </label>
                        <textarea wire:model="areas_for_improvement" rows="4" 
                                  class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="{{ __('messages.areas_for_improvement_placeholder') }}"></textarea>
                        @error('areas_for_improvement') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Development Plan --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.development_plan') }}
                    </label>
                    <textarea wire:model="development_plan" rows="4" 
                              class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                              placeholder="{{ __('messages.development_plan_placeholder') }}"></textarea>
                    @error('development_plan') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- Additional Comments --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.additional_comments') }}
                    </label>
                    <textarea wire:model="additional_comments" rows="3" 
                              class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                              placeholder="{{ __('messages.additional_comments_placeholder') }}"></textarea>
                    @error('additional_comments') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- File Attachments --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.attachments') }}
                    </label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6">
                        <input wire:model="attachments" type="file" multiple 
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="text-xs text-gray-500 mt-2">
                            {{ __('messages.allowed_file_types') }}: PDF, DOC, DOCX, JPG, JPEG, PNG ({{ __('messages.max_size') }}: 10MB)
                        </p>
                    </div>

                    @if($attachments)
                        <div class="mt-3 space-y-2">
                            @foreach($attachments as $index => $attachment)
                                <div class="flex items-center justify-between bg-gray-50 p-2 rounded-lg">
                                    <span class="text-sm text-gray-700">{{ $attachment->getClientOriginalName() }}</span>
                                    <button type="button" wire:click="removeAttachment({{ $index }})" 
                                            class="text-red-500 hover:text-red-700">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    
                    @error('attachments.*') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </form>
        </div>

        {{-- Footer --}}
        <div class="flex-shrink-0 bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t border-gray-200">
            <button type="button" wire:click="closeModal" 
                    class="px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                {{ __('messages.cancel') }}
            </button>
            <button wire:click="save" 
                    class="px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                <div wire:loading wire:target="save" class="inline-flex items-center">
                    <i class="fas fa-spinner fa-spin mr-2"></i>
                </div>
                <div wire:loading.remove wire:target="save" class="inline-flex items-center">
                    <i class="fas fa-save mr-2"></i>
                </div>
                {{ $isEditing ? __('messages.update') : __('messages.save') }}
            </button>
        </div>
    </div>
</div>
@endif
