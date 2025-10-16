<!-- Modal de Formulário para Adicionar/Editar Adiantamento -->
<div>
    <div x-data="{ open: @entangle('showModal') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50" 
         role="dialog" 
         aria-modal="true"
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0">
        <div class="relative top-20 mx-auto p-1 w-full max-w-4xl">
            <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" 
                 x-transition:enter="transition ease-out duration-300" 
                 x-transition:enter-start="transform opacity-0 scale-95" 
                 x-transition:enter-end="transform opacity-100 scale-100" 
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="transform opacity-100 scale-100" 
                 x-transition:leave-end="transform opacity-0 scale-95">
                
                <!-- Cabeçalho com gradiente -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <i class="fas {{ $isEditing ? 'fa-edit' : 'fa-plus-circle' }} mr-2 animate-pulse"></i>
                        {{ $isEditing ? __('messages.edit_advance') : __('messages.add_advance') }}
                    </h3>
                    <button type="button" wire:click="closeModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form wire:submit.prevent="save">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="mb-6 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 transition-all duration-200 ease-in-out hover:shadow-md">
                            <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                <h2 class="text-base font-medium text-gray-700">{{ __('messages.advance_information') }}</h2>
                            </div>
                            
                            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Funcionário -->
                                <div>
                                    <label for="employee_id" class="block text-sm font-medium text-gray-700">{{ __('messages.employee') }} *</label>
                                    <select id="employee_id" wire:model="employee_id" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white">
                                        <option value="">{{ __('messages.select_employee') }}</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('employee_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <!-- Data do Pedido -->
                                <div>
                                    <label for="request_date" class="block text-sm font-medium text-gray-700">{{ __('messages.request_date') }} *</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-calendar-alt text-gray-400"></i>
                                        </div>
                                        <input type="date" id="request_date" wire:model="request_date" 
                                            placeholder="{{ __('messages.select_date') }}"
                                            class="pl-10 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white">
                                    </div>
                                    @error('request_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <!-- Valor do Adiantamento -->
                                <div>
                                    <label for="amount" class="block text-sm font-medium text-gray-700">{{ __('messages.amount') }} *</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">Kz</span>
                                        </div>
                                        <input type="number" step="0.01" id="amount" wire:model.live.debounce.500ms="amount" 
                                            placeholder="0.00"
                                            class="pl-10 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white">
                                    </div>
                                    @error('amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <!-- Número de Parcelas -->
                                <div>
                                    <label for="installments" class="block text-sm font-medium text-gray-700">{{ __('messages.installments') }} *</label>
                                    <input type="number" min="1" max="12" id="installments" wire:model.live.debounce.300ms="installments" 
                                        placeholder="{{ __('messages.enter_installments') }}"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white">
                                    @error('installments') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <!-- Valor da Parcela (Calculado) -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">{{ __('messages.installment_amount') }}</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">Kz</span>
                                        </div>
                                        <input type="text" readonly value="{{ $installment_amount ? number_format($installment_amount, 2) : '0.00' }}" 
                                            class="pl-10 block w-full bg-gray-100 border-gray-300 rounded-md shadow-sm sm:text-sm">
                                    </div>
                                </div>
                                
                                <!-- Data da Primeira Dedução -->
                                <div>
                                    <label for="first_deduction_date" class="block text-sm font-medium text-gray-700">{{ __('messages.first_deduction_date') }} *</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-calendar-alt text-gray-400"></i>
                                        </div>
                                        <input type="date" id="first_deduction_date" wire:model="first_deduction_date" 
                                            placeholder="{{ __('messages.select_date') }}"
                                            class="pl-10 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white">
                                    </div>
                                    @error('first_deduction_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-6 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 transition-all duration-200 ease-in-out hover:shadow-md">
                            <div class="flex items-center bg-gradient-to-r from-green-50 to-green-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-comment-alt text-green-600 mr-2"></i>
                                <h2 class="text-base font-medium text-gray-700">{{ __('messages.additional_information') }}</h2>
                            </div>
                            
                            <div class="p-4 grid grid-cols-1 gap-4">
                                <!-- Motivo -->
                                <div>
                                    <label for="reason" class="block text-sm font-medium text-gray-700">{{ __('messages.reason') }}</label>
                                    <textarea id="reason" wire:model="reason" rows="2" 
                                        placeholder="{{ __('messages.enter_reason') }}"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white"></textarea>
                                    @error('reason') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <!-- Notas -->
                                <div>
                                    <label for="notes" class="block text-sm font-medium text-gray-700">{{ __('messages.notes') }}</label>
                                    <textarea id="notes" wire:model="notes" rows="3" 
                                        placeholder="{{ __('messages.enter_notes') }}"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white"></textarea>
                                    @error('notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <!-- Documento Assinado -->
                                <div class="md:col-span-2">
                                    <label for="signed_document" class="block text-sm font-medium text-gray-700">
                                        {{ __('messages.signed_document') }}
                                        <span class="text-gray-500 text-xs">(PDF, JPG, PNG - Máx: 5MB)</span>
                                    </label>
                                    <div class="mt-1">
                                        @if($existing_signed_document)
                                            <div class="mb-2 flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-md">
                                                <div class="flex items-center">
                                                    <i class="fas fa-file-check text-green-600 mr-2"></i>
                                                    <span class="text-sm text-green-700">{{ __('messages.document_attached') }}</span>
                                                    <a href="{{ asset('storage/' . $existing_signed_document) }}" target="_blank" class="ml-2 text-blue-600 hover:text-blue-800 text-xs underline">
                                                        {{ __('messages.view') }}
                                                    </a>
                                                </div>
                                                <button type="button" wire:click="removeSignedDocument" class="text-red-600 hover:text-red-800">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        @endif
                                        <input type="file" id="signed_document" wire:model="signed_document" 
                                            accept=".pdf,.jpg,.jpeg,.png"
                                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                        @error('signed_document') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        <p class="mt-1 text-xs text-gray-500">{{ __('messages.upload_signed_advance_document') }}</p>
                                    </div>
                                </div>
                                
                                <!-- Status -->
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700">{{ __('messages.status') }} *</label>
                                    <select id="status" wire:model="status" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white">
                                        <option value="pending">{{ __('messages.pending') }}</option>
                                        <option value="approved">{{ __('messages.approved') }}</option>
                                        <option value="rejected">{{ __('messages.rejected') }}</option>
                                        <option value="completed">{{ __('messages.completed') }}</option>
                                    </select>
                                    @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                        <button type="button" wire:click="closeModal" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                            <i class="fas fa-times mr-2"></i>
                            {{ __('messages.cancel') }}
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="save">
                                <i class="fas {{ $isEditing ? 'fa-save' : 'fa-plus-circle' }} mr-2"></i>
                                {{ $isEditing ? __('messages.update') : __('messages.save') }}
                            </span>
                            <span wire:loading wire:target="save" class="inline-flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ __('messages.processing') }}
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
