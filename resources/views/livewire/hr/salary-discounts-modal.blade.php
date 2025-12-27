<!-- Modal de Criação/Edição -->
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
            
            <!-- Cabeçalho -->
            <div class="bg-gradient-to-r from-red-600 to-red-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-minus-circle mr-2"></i>
                    {{ $isEditing ? __('messages.edit_discount') : __('messages.add_discount') }}
                </h3>
                <button wire:click="closeModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Conteúdo do formulário -->
            <div class="p-6 space-y-6">
                <form wire:submit.prevent="save" class="space-y-6">
                    <!-- Funcionário -->
                    <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                        <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                            <i class="fas fa-user text-blue-600 mr-2"></i>
                            <h3 class="text-base font-medium text-gray-700">{{ __('messages.employee_information') }}</h3>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Funcionário com Busca -->
                                <div x-data="{ 
                                    open: false, 
                                    search: '{{ $employee_id ? ($employees->firstWhere('id', $employee_id)->full_name ?? '') : '' }}'
                                }" 
                                x-init="search = '{{ $employee_id ? ($employees->firstWhere('id', $employee_id)->full_name ?? '') : '' }}'"
                                @click.away="open = false">
                                    <label for="employee_search" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-user mr-1 text-gray-400"></i>
                                        {{ __('messages.employee') }} *
                                    </label>
                                    <div class="relative">
                                        <input 
                                            type="text" 
                                            x-model="search"
                                            value="{{ $employee_id ? ($employees->firstWhere('id', $employee_id)->full_name ?? '') : '' }}"
                                            @click="open = true"
                                            @input="open = true"
                                            placeholder="{{ __('messages.search_employee') }}"
                                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 text-sm pl-10"
                                        >
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-search text-gray-400"></i>
                                        </div>
                                        
                                        <!-- Dropdown -->
                                        <div 
                                            x-show="open"
                                            x-transition
                                            class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
                                        >
                                            @forelse($employees as $employee)
                                                <div 
                                                    x-show="search === '' || '{{ strtolower($employee->full_name) }}'.includes(search.toLowerCase())"
                                                    wire:click="$set('employee_id', {{ $employee->id }})"
                                                    @click="open = false; search = '{{ $employee->full_name }}'"
                                                    class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-red-50 {{ $employee_id == $employee->id ? 'bg-red-100' : '' }}"
                                                >
                                                    <span class="font-medium block truncate {{ $employee_id == $employee->id ? 'text-red-600' : 'text-gray-900' }}">
                                                        {{ $employee->full_name }}
                                                        @if($employee->employee_id)
                                                        <span class="text-gray-500 text-xs ml-2">{{ $employee->employee_id }}</span>
                                                        @endif
                                                    </span>
                                                    @if($employee_id == $employee->id)
                                                    <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-red-600">
                                                        <i class="fas fa-check"></i>
                                                    </span>
                                                    @endif
                                                </div>
                                            @empty
                                                <div class="py-2 pl-3 pr-9 text-gray-500 text-sm">
                                                    {{ __('messages.no_employees_found') }}
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                    @error('employee_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label for="request_date" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-calendar-day mr-1 text-gray-400"></i>
                                        {{ __('messages.request_date') }} *
                                    </label>
                                    <input type="date" id="request_date" wire:model="request_date" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 text-sm">
                                    @error('request_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informações do Desconto -->
                    <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                        <div class="flex items-center bg-gradient-to-r from-green-50 to-green-100 px-4 py-3 border-b border-gray-200">
                            <i class="fas fa-minus-circle text-green-600 mr-2"></i>
                            <h3 class="text-base font-medium text-gray-700">{{ __('messages.discount_information') }}</h3>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="discount_type" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-tag mr-1 text-gray-400"></i>
                                        {{ __('messages.discount_type') }} *
                                    </label>
                                    <select id="discount_type" wire:model="discount_type" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 text-sm">
                                        <option value="others">{{ __('messages.other_discount') }}</option>
                                        <option value="union">{{ __('messages.union_discount') }}</option>
                                        <option value="quixiquila">Quixiquila</option>
                                    </select>
                                    @error('discount_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-money-bill-wave mr-1 text-gray-400"></i>
                                        {{ __('messages.amount') }} *
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 text-sm">Kz</span>
                                        </div>
                                        <input type="number" step="0.01" id="amount" wire:model="amount" wire:change="calculateInstallmentAmount" class="pl-12 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 text-sm" placeholder="0.00">
                                    </div>
                                    @error('amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informações de Parcelamento -->
                    <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                        <div class="flex items-center bg-gradient-to-r from-purple-50 to-purple-100 px-4 py-3 border-b border-gray-200">
                            <i class="fas fa-calculator text-purple-600 mr-2"></i>
                            <h3 class="text-base font-medium text-gray-700">{{ __('messages.installment_information') }}</h3>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="installments" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-list-ol mr-1 text-gray-400"></i>
                                        {{ __('messages.installments') }} *
                                    </label>
                                    <input type="number" id="installments" wire:model="installments" wire:change="calculateInstallmentAmount" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 text-sm" min="1">
                                    @error('installments') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label for="installment_amount" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-coins mr-1 text-gray-400"></i>
                                        {{ __('messages.installment_amount') }}
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 text-sm">Kz</span>
                                        </div>
                                        <input type="number" step="0.01" id="installment_amount" wire:model="installment_amount" class="pl-12 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 text-sm bg-gray-50" placeholder="0.00" readonly>
                                    </div>
                                    @error('installment_amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label for="first_deduction_date" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-calendar-check mr-1 text-gray-400"></i>
                                        {{ __('messages.first_deduction_date') }} *
                                    </label>
                                    <input type="date" id="first_deduction_date" wire:model="first_deduction_date" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 text-sm">
                                    @error('first_deduction_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Motivo e Observações -->
                    <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                        <div class="flex items-center bg-gradient-to-r from-yellow-50 to-yellow-100 px-4 py-3 border-b border-gray-200">
                            <i class="fas fa-clipboard-list text-yellow-600 mr-2"></i>
                            <h3 class="text-base font-medium text-gray-700">{{ __('messages.additional_information') }}</h3>
                        </div>
                        <div class="p-4">
                            <div class="space-y-4">
                                <div>
                                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-comment-alt mr-1 text-gray-400"></i>
                                        {{ __('messages.reason') }} *
                                    </label>
                                    <textarea id="reason" wire:model="reason" rows="3" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 text-sm" placeholder="{{ __('messages.reason_placeholder') }}"></textarea>
                                    @error('reason') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-sticky-note mr-1 text-gray-400"></i>
                                        {{ __('messages.notes') }}
                                    </label>
                                    <textarea id="notes" wire:model="notes" rows="3" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 text-sm" placeholder="{{ __('messages.notes_placeholder') }}"></textarea>
                                    @error('notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <!-- Upload de Documento Assinado -->
                                <div>
                                    <label for="signed_document" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-file-signature mr-1 text-gray-400"></i>
                                        {{ __('messages.signed_document') }}
                                    </label>
                                    
                                    @if($existing_signed_document)
                                        <div class="mb-2 p-3 bg-green-50 border border-green-200 rounded-md">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <i class="fas fa-file-pdf text-green-600 mr-2"></i>
                                                    <span class="text-sm text-green-700">{{ __('messages.document_uploaded') }}</span>
                                                </div>
                                                <div class="flex space-x-2">
                                                    <a href="{{ asset('storage/' . $existing_signed_document) }}" 
                                                       target="_blank"
                                                       class="text-green-600 hover:text-green-800 text-sm font-medium">
                                                        <i class="fas fa-eye mr-1"></i>
                                                        {{ __('messages.view') }}
                                                    </a>
                                                    <button type="button" 
                                                            wire:click="removeSignedDocument" 
                                                            class="text-red-600 hover:text-red-800 text-sm font-medium">
                                                        <i class="fas fa-trash mr-1"></i>
                                                        {{ __('messages.remove') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <input type="file" 
                                           id="signed_document" 
                                           wire:model="signed_document" 
                                           accept=".pdf,.jpg,.jpeg,.png"
                                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                                    
                                    @error('signed_document') 
                                        <span class="text-red-500 text-xs">{{ $message }}</span> 
                                    @enderror
                                    
                                    <p class="mt-1 text-xs text-gray-500">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        {{ __('messages.allowed_formats') }}: PDF, JPG, PNG ({{ __('messages.max_size') }}: 5MB)
                                    </p>
                                    
                                    @if($signed_document)
                                        <div class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-md">
                                            <div class="flex items-center">
                                                <i class="fas fa-upload text-blue-600 mr-2"></i>
                                                <span class="text-sm text-blue-700">{{ __('messages.file_ready_to_upload') }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Rodapé -->
            <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                <button wire:click="closeModal" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out">
                    <i class="fas fa-times mr-2"></i>
                    {{ __('messages.cancel') }}
                </button>
                <button wire:click="save" class="inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out">
                    <i class="fas fa-save mr-2"></i>
                    {{ $isEditing ? __('messages.update') : __('messages.save') }}
                </button>
            </div>
        </div>
    </div>
</div>
