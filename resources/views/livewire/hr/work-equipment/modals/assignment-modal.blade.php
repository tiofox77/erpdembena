{{-- Assignment Create/Edit Modal --}}
@if($showAssignmentModal)
<div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] flex flex-col overflow-hidden">
        
        {{-- Header --}}
        <div class="bg-gradient-to-r from-green-600 to-green-700 p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-white/20 p-2 rounded-lg">
                        <i class="fas fa-user-plus text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold">
                            {{ $isEditing ? __('messages.edit_assignment') : __('messages.new_assignment') }}
                        </h2>
                        <p class="text-green-100">{{ __('messages.assignment_form_description') }}</p>
                    </div>
                </div>
                <button wire:click="closeAssignmentModal" class="text-white/80 hover:text-white p-2">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        {{-- Form Content --}}
        <div class="flex-1 p-6 overflow-y-auto">
            <form wire:submit.prevent="saveAssignment" class="space-y-6">
                
                {{-- Selection --}}
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="fas fa-check-circle mr-2 text-green-600"></i>
                        {{ __('messages.assignment_details') }}
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.equipment') }} <span class="text-red-500">*</span>
                            </label>
                            <select wire:model.defer="equipment_id_assignment" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">{{ __('messages.select_equipment') }}</option>
                                @foreach($availableEquipment as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->asset_code }})</option>
                                @endforeach
                            </select>
                            @error('equipment_id_assignment') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.employee') }} <span class="text-red-500">*</span>
                            </label>
                            <select wire:model.defer="employee_id" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">{{ __('messages.select_employee') }}</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                                @endforeach
                            </select>
                            @error('employee_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.assigned_date') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="date" wire:model.defer="issue_date" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            @error('issue_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.return_date') }}
                            </label>
                            <input type="date" wire:model.defer="return_date" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            @error('return_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.status') }} <span class="text-red-500">*</span>
                            </label>
                            <select wire:model.defer="assignment_status" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">{{ __('messages.select_status') }}</option>
                                <option value="issued">{{ __('messages.issued') }}</option>
                                <option value="returned">{{ __('messages.returned') }}</option>
                                <option value="damaged">{{ __('messages.damaged') }}</option>
                                <option value="lost">{{ __('messages.lost') }}</option>
                            </select>
                            @error('assignment_status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.condition_on_issue') }}
                            </label>
                            <input type="text" wire:model.defer="condition_on_issue" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                placeholder="{{ __('messages.enter_condition') }}">
                            @error('condition_on_issue') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.purpose') }}
                        </label>
                        <textarea wire:model.defer="assignment_notes" rows="3" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                            placeholder="{{ __('messages.enter_purpose') }}"></textarea>
                        @error('assignment_notes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

            </form>
        </div>

        {{-- Footer --}}
        <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t">
            <button wire:click="closeAssignmentModal" type="button" 
                class="px-6 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                <i class="fas fa-times mr-2"></i>
                {{ __('messages.cancel') }}
            </button>
            <button wire:click="saveAssignment" type="button" 
                wire:loading.attr="disabled"
                wire:target="saveAssignment"
                class="px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="saveAssignment">
                    <i class="fas fa-save mr-2"></i>
                    {{ $isEditing ? __('messages.update') : __('messages.save') }}
                </span>
                <span wire:loading wire:target="saveAssignment" class="flex items-center">
                    <svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ __('messages.saving') }}...
                </span>
            </button>
        </div>
    </div>
</div>
@endif
