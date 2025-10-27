{{-- Create/Edit Attendance Modal --}}
@if($showModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 rounded-t-xl">
                <div class="flex justify-between items-center">
                    <div class="flex items-center text-white">
                        <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-3">
                            <i class="fas {{ $isEditing ? 'fa-edit' : 'fa-plus-circle' }} text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold">
                                {{ $isEditing ? __('attendance.edit_attendance') : __('attendance.record_attendance') }}
                            </h3>
                            <p class="text-blue-100 text-sm">{{ __('attendance.fill_form_below') }}</p>
                        </div>
                    </div>
                    <button type="button" class="text-white/80 hover:text-white hover:bg-white/20 rounded-lg p-2 transition-all" wire:click="closeModal">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div class="p-6">
                @if($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-lg">
                        <p class="font-bold flex items-center mb-2">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            {{ __('common.correct_following_errors') }}:
                        </p>
                        <ul class="list-disc list-inside text-sm space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form wire:submit.prevent="save">
                    <div class="space-y-6">
                        {{-- Employee --}}
                        <div>
                            <label for="employee_id" class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                <i class="fas fa-user text-blue-600 mr-2"></i>
                                {{ __('attendance.employee') }}
                            </label>
                            <select id="employee_id"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 transition-all @error('employee_id') border-red-300 @enderror"
                                wire:model.live="employee_id">
                                <option value="">{{ __('attendance.select_employee') }}</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Date --}}
                        <div>
                            <label for="date" class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                <i class="fas fa-calendar text-blue-600 mr-2"></i>
                                {{ __('attendance.date') }}
                            </label>
                            <input type="date" id="date"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 transition-all @error('date') border-red-300 @enderror"
                                wire:model.live="date">
                            @error('date')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Time Fields --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Time In --}}
                            <div>
                                <label for="time_in" class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                    <i class="fas fa-sign-in-alt text-green-600 mr-2"></i>
                                    {{ __('attendance.time_in') }}
                                </label>
                                <input type="time" id="time_in"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 transition-all @error('time_in') border-red-300 @enderror"
                                    wire:model.live="time_in">
                                @error('time_in')
                                    <p class="mt-2 text-sm text-red-600 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Time Out --}}
                            <div>
                                <label for="time_out" class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                    <i class="fas fa-sign-out-alt text-red-600 mr-2"></i>
                                    {{ __('attendance.time_out') }}
                                </label>
                                <input type="time" id="time_out"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 transition-all @error('time_out') border-red-300 @enderror"
                                    wire:model.live="time_out">
                                @error('time_out')
                                    <p class="mt-2 text-sm text-red-600 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        {{-- Status --}}
                        <div>
                            <label for="status" class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                {{ __('attendance.status') }}
                            </label>
                            <select id="status"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 transition-all @error('status') border-red-300 @enderror"
                                wire:model.live="status">
                                <option value="">{{ __('attendance.select_status') }}</option>
                                <option value="present">{{ __('attendance.present') }}</option>
                                <option value="absent">{{ __('attendance.absent') }}</option>
                                <option value="late">{{ __('attendance.late') }}</option>
                                <option value="half_day">{{ __('attendance.half_day') }}</option>
                            </select>
                            @error('status')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Remarks --}}
                        <div>
                            <label for="remarks" class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                <i class="fas fa-comment text-blue-600 mr-2"></i>
                                {{ __('attendance.remarks') }}
                            </label>
                            <textarea id="remarks" rows="3"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 transition-all @error('remarks') border-red-300 @enderror"
                                wire:model.live="remarks"
                                placeholder="{{ __('attendance.enter_remarks') }}"></textarea>
                            @error('remarks')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    {{-- Footer Actions --}}
                    <div class="flex justify-end items-center space-x-3 mt-8 pt-6 border-t border-gray-200">
                        <button type="button"
                            class="inline-flex items-center px-5 py-2.5 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all"
                            wire:click="closeModal">
                            <i class="fas fa-times mr-2"></i>
                            {{ __('common.cancel') }}
                        </button>
                        <button type="submit"
                            class="inline-flex items-center px-5 py-2.5 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all">
                            <i class="fas {{ $isEditing ? 'fa-save' : 'fa-plus' }} mr-2"></i>
                            {{ $isEditing ? __('common.update') : __('common.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
