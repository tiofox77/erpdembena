{{-- Create/Edit Disciplinary Measure Modal --}}
@if($showModal)
<div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 p-4" x-data="{ loading: false }">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden">
        
        {{-- Header --}}
        <div class="bg-gradient-to-r from-red-600 to-red-700 p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-white/20 p-2 rounded-lg">
                        <i class="fas fa-gavel text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold">
                            {{ $isEditing ? __('messages.edit_measure') : __('messages.new_measure') }}
                        </h2>
                        <p class="text-red-100">{{ __('messages.disciplinary_measure_form_description') }}</p>
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
                                <div class="h-10 w-10 bg-red-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-medium text-sm">
                                        {{ substr($selectedEmployee->full_name, 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $selectedEmployee->full_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $selectedEmployee->id_card }}</div>
                                </div>
                            </div>
                            <button type="button" wire:click="openEmployeeSearch" class="text-red-600 hover:text-red-800">
                                <i class="fas fa-exchange-alt"></i>
                            </button>
                        </div>
                    @else
                        <button type="button" wire:click="openEmployeeSearch" 
                                class="w-full flex items-center justify-center px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-red-400 hover:text-red-600 transition-colors">
                            <i class="fas fa-search mr-2"></i>
                            {{ __('messages.select_employee') }}
                        </button>
                    @endif
                    @error('employeeId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- Measure Details --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Measure Type --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.measure_type') }} <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="measureType" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            <option value="">{{ __('messages.select_measure_type') }}</option>
                            <option value="verbal_warning">{{ __('messages.verbal_warning') }}</option>
                            <option value="written_warning">{{ __('messages.written_warning') }}</option>
                            <option value="suspension">{{ __('messages.suspension') }}</option>
                            <option value="termination">{{ __('messages.termination') }}</option>
                            <option value="fine">{{ __('messages.fine') }}</option>
                            <option value="other">{{ __('messages.other') }}</option>
                        </select>
                        @error('measureType') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.status') }} <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            <option value="active">{{ __('messages.active') }}</option>
                            <option value="completed">{{ __('messages.completed') }}</option>
                            <option value="cancelled">{{ __('messages.cancelled') }}</option>
                        </select>
                        @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Dates --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Applied Date --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.applied_date') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="date" wire:model="appliedDate" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        @error('appliedDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    {{-- Effective Date --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.effective_date') }}
                        </label>
                        <input type="date" wire:model="effectiveDate" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        @error('effectiveDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Reason --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.reason') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" wire:model="reason" 
                           placeholder="{{ __('messages.enter_reason') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    @error('reason') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.description') }} <span class="text-red-500">*</span>
                    </label>
                    <textarea wire:model="description" rows="4" 
                              placeholder="{{ __('messages.enter_detailed_description') }}"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"></textarea>
                    @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.notes') }}
                    </label>
                    <textarea wire:model="notes" rows="3" 
                              placeholder="{{ __('messages.additional_notes') }}"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"></textarea>
                    @error('notes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- File Attachments --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.attachments') }}
                    </label>
                    
                    {{-- Upload Area --}}
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4">
                        <input type="file" wire:model="attachments" multiple 
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                        <p class="text-xs text-gray-500 mt-2">
                            {{ __('messages.allowed_files') }}: PDF, DOC, DOCX, JPG, JPEG, PNG. {{ __('messages.max_size') }}: 10MB
                        </p>
                    </div>
                    
                    {{-- Existing Attachments --}}
                    @if($existingAttachments && count($existingAttachments) > 0)
                        <div class="mt-4">
                            <h5 class="text-sm font-medium text-gray-700 mb-2">{{ __('messages.existing_attachments') }}</h5>
                            <div class="space-y-2">
                                @foreach($existingAttachments as $index => $attachment)
                                    <div class="flex items-center justify-between bg-gray-50 p-2 rounded">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-file text-gray-400"></i>
                                            <span class="text-sm text-gray-700">{{ $attachment['original_name'] ?? 'Attachment' }}</span>
                                        </div>
                                        <button type="button" wire:click="removeAttachment({{ $index }})" 
                                                class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
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
                    class="px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
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
