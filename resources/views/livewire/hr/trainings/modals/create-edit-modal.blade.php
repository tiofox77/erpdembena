{{-- Modal: Create/Edit Training (DO NOT RENAME) --}}
@if($showCreateEditModal)
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
                        <h2 class="text-2xl font-bold">
                            {{ $isEditMode ? __('messages.edit_training') : __('messages.create_training') }}
                        </h2>
                        <p class="text-purple-100">{{ __('messages.training_form_description') }}</p>
                    </div>
                </div>
                <button wire:click="closeCreateEditModal" class="text-white/80 hover:text-white p-2 rounded-lg hover:bg-white/10 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        {{-- Body --}}
        <div class="flex-1 overflow-y-auto p-6 space-y-6">
            <form wire:submit.prevent="save">
                {{-- Employee Selection --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.employee') }} <span class="text-red-500">*</span>
                    </label>
                    @if($selectedEmployee)
                        <div class="flex items-center justify-between p-4 bg-purple-50 border border-purple-200 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="h-10 w-10 rounded-full bg-purple-500 flex items-center justify-center">
                                    <span class="text-white font-medium">{{ substr($selectedEmployee->full_name, 0, 2) }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $selectedEmployee->full_name }}</p>
                                    <p class="text-sm text-gray-500">{{ __('messages.id_card') }}: {{ $selectedEmployee->id_card }}</p>
                                </div>
                            </div>
                            <button type="button" wire:click="openEmployeeSearch" class="text-purple-600 hover:text-purple-800 font-medium">
                                {{ __('messages.change_employee') }}
                            </button>
                        </div>
                    @else
                        <button type="button" wire:click="openEmployeeSearch" class="w-full p-4 border-2 border-dashed border-gray-300 rounded-lg text-center hover:border-purple-500 transition-colors">
                            <i class="fas fa-user-plus text-2xl text-gray-400 mb-2"></i>
                            <p class="text-gray-600">{{ __('messages.select_employee') }}</p>
                        </button>
                    @endif
                    @error('employee_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Training Type --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.training_type') }} <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="training_type" class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            @foreach(\App\Models\HR\Training::TRAINING_TYPES as $key => $type)
                                <option value="{{ $key }}">{{ __('messages.training_type_' . $key) }}</option>
                            @endforeach
                        </select>
                        @error('training_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.status') }} <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="status" class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            @foreach(\App\Models\HR\Training::STATUSES as $key => $status)
                                <option value="{{ $key }}">{{ __('messages.training_status_' . $key) }}</option>
                            @endforeach
                        </select>
                        @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Training Title --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.training_title') }} <span class="text-red-500">*</span>
                    </label>
                    <input wire:model="training_title" type="text" 
                           class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                           placeholder="{{ __('messages.training_title_placeholder') }}">
                    @error('training_title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- Training Description --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.training_description') }}
                    </label>
                    <textarea wire:model="training_description" rows="3" 
                              class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                              placeholder="{{ __('messages.training_description_placeholder') }}"></textarea>
                    @error('training_description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Provider --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.provider') }}
                        </label>
                        <input wire:model="provider" type="text" 
                               class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                               placeholder="{{ __('messages.provider_placeholder') }}">
                        @error('provider') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    {{-- Location --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.location') }}
                        </label>
                        <input wire:model="location" type="text" 
                               class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                               placeholder="{{ __('messages.location_placeholder') }}">
                        @error('location') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Training Dates --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.start_date') }} <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="start_date" type="date" 
                               class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        @error('start_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.end_date') }}
                        </label>
                        <input wire:model="end_date" type="date" 
                               class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        @error('end_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Duration Hours --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.duration_hours') }}
                        </label>
                        <input wire:model="duration_hours" type="number" step="0.5" min="0" 
                               class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                               placeholder="0.0">
                        @error('duration_hours') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    {{-- Cost --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.cost') }}
                        </label>
                        <input wire:model="cost" type="number" step="0.01" min="0" 
                               class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                               placeholder="0.00">
                        @error('cost') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    {{-- Budget Approved --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.budget_status') }}
                        </label>
                        <div class="flex items-center space-x-4 mt-2">
                            <label class="flex items-center">
                                <input wire:model="budget_approved" type="checkbox" value="1" 
                                       class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-700">{{ __('messages.budget_approved') }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Completion Section --}}
                <div class="bg-blue-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-blue-800 mb-4 flex items-center">
                        <i class="fas fa-tasks mr-2"></i>
                        {{ __('messages.completion_details') }}
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Completion Status --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.completion_status') }} <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="completion_status" class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @foreach(\App\Models\HR\Training::COMPLETION_STATUSES as $key => $status)
                                    <option value="{{ $key }}">{{ __('messages.completion_status_' . $key) }}</option>
                                @endforeach
                            </select>
                            @error('completion_status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Completion Date --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.completion_date') }}
                            </label>
                            <input wire:model="completion_date" type="date" 
                                   class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('completion_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Evaluation Score --}}
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.evaluation_score') }}
                        </label>
                        <input wire:model="evaluation_score" type="number" step="0.1" min="0" max="10" 
                               class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="0.0 - 10.0">
                        @error('evaluation_score') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Certification Section --}}
                <div class="bg-green-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-green-800 mb-4 flex items-center">
                        <i class="fas fa-certificate mr-2"></i>
                        {{ __('messages.certification') }}
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Certification Received --}}
                        <div>
                            <label class="flex items-center">
                                <input wire:model="certification_received" type="checkbox" value="1" 
                                       class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-700">{{ __('messages.certification_received') }}</span>
                            </label>
                        </div>

                        {{-- Certification Expiry Date --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.certification_expiry_date') }}
                            </label>
                            <input wire:model="certification_expiry_date" type="date" 
                                   class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            @error('certification_expiry_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                {{-- Trainer Information --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Trainer Name --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.trainer_name') }}
                        </label>
                        <input wire:model="trainer_name" type="text" 
                               class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                               placeholder="{{ __('messages.trainer_name_placeholder') }}">
                        @error('trainer_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    {{-- Trainer Email --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.trainer_email') }}
                        </label>
                        <input wire:model="trainer_email" type="email" 
                               class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                               placeholder="{{ __('messages.trainer_email_placeholder') }}">
                        @error('trainer_email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Skills Acquired --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.skills_acquired') }}
                    </label>
                    <textarea wire:model="skills_acquired" rows="3" 
                              class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                              placeholder="{{ __('messages.skills_acquired_placeholder') }}"></textarea>
                    @error('skills_acquired') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- Feedback --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.feedback') }}
                    </label>
                    <textarea wire:model="feedback" rows="3" 
                              class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                              placeholder="{{ __('messages.feedback_placeholder') }}"></textarea>
                    @error('feedback') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- Next Steps --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.next_steps') }}
                    </label>
                    <textarea wire:model="next_steps" rows="3" 
                              class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                              placeholder="{{ __('messages.next_steps_placeholder') }}"></textarea>
                    @error('next_steps') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.notes') }}
                    </label>
                    <textarea wire:model="notes" rows="3" 
                              class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                              placeholder="{{ __('messages.notes_placeholder') }}"></textarea>
                    @error('notes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- File Attachments --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.attachments') }}
                    </label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6">
                        <div class="text-center">
                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-4"></i>
                            <input type="file" wire:model="attachments" multiple 
                                   class="hidden" id="training-attachments" 
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <label for="training-attachments" class="cursor-pointer">
                                <span class="mt-2 block text-sm font-medium text-gray-900">
                                    {{ __('messages.click_to_upload_files') }}
                                </span>
                                <span class="text-xs text-gray-500 mt-1 block">
                                    {{ __('messages.supported_formats') }}: PDF, DOC, DOCX, JPG, PNG ({{ __('messages.max_size') }}: 10MB)
                                </span>
                            </label>
                        </div>
                    </div>

                    {{-- Existing Attachments --}}
                    @if(!empty($existingAttachments))
                        <div class="mt-4 space-y-2">
                            <h4 class="text-sm font-medium text-gray-700">{{ __('messages.existing_files') }}</h4>
                            @foreach($existingAttachments as $filename)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <i class="fas fa-file text-blue-500"></i>
                                        <span class="text-sm text-gray-700">{{ $filename }}</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button type="button" 
                                                wire:click="downloadAttachment({{ $trainingId }}, '{{ $filename }}')"
                                                class="text-blue-600 hover:text-blue-800 text-sm">
                                            <i class="fas fa-download"></i>
                                        </button>
                                        <button type="button" 
                                                wire:click="deleteAttachment('{{ $filename }}')"
                                                class="text-red-600 hover:text-red-800 text-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
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
            <button type="button" wire:click="closeCreateEditModal" 
                    class="px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors">
                <i class="fas fa-times mr-2"></i>
                {{ __('messages.cancel') }}
            </button>
            <button type="button" wire:click="save" 
                    class="px-6 py-3 border border-transparent rounded-lg text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors">
                <i class="fas fa-save mr-2"></i>
                {{ $isEditMode ? __('messages.update_training') : __('messages.create_training') }}
            </button>
        </div>
    </div>
</div>
@endif
