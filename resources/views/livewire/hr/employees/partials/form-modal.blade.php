<!-- Create/Edit Employee Modal -->
<!-- NOTA: Este é um placeholder. O conteúdo completo deve ser copiado das linhas 692-1656 do arquivo original employees.blade.php -->
<!-- O formulário completo inclui todas as seções: Personal Information, Contact, Bank, Employment, Salary, Benefits, Emergency Contact -->

@if($showModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-6xl max-h-[95vh] overflow-hidden m-4 transform transition-all duration-300">
            <!-- Modern Header with Gradient -->
            <div class="bg-gradient-to-r {{ $isEditing ? 'from-green-600 to-green-700' : 'from-blue-600 to-blue-700' }} px-6 py-6 sticky top-0 z-20">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                            <i class="fas {{ $isEditing ? 'fa-user-edit' : 'fa-user-plus' }} text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">
                                {{ $isEditing ? __('messages.edit') : __('messages.create') }} {{ __('messages.employee') }}
                            </h3>
                            <p class="text-blue-100 text-sm">
                                {{ $isEditing ? __('messages.update_employee_information') : __('messages.add_new_employee_information') }}
                            </p>
                        </div>
                    </div>
                    <button type="button" 
                        class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center text-white hover:bg-opacity-30 transition-all duration-200 transform hover:scale-105" 
                        wire:click="closeModal">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </div>

            <!-- Scrollable Content Area -->
            <div class="overflow-y-auto max-h-[calc(95vh-120px)]">
                <!-- Error Messages -->
                @if($errors->any())
                    <div class="mx-6 mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-exclamation-triangle text-red-600 text-sm"></i>
                                </div>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">{{ __('messages.please_correct_errors') }}</h3>
                                <ul class="mt-2 text-sm text-red-700 space-y-1">
                                    @foreach($errors->all() as $error)
                                        <li class="flex items-start">
                                            <i class="fas fa-chevron-right text-red-500 text-xs mt-1 mr-2"></i>
                                            {{ $error }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Form Container -->
                <form wire:submit.prevent="save" class="px-6 py-6">
                    {{-- 
                        IMPORTANTE: Copiar aqui todo o conteúdo do formulário do arquivo original
                        Linhas 748-1620 aproximadamente
                        
                        Seções incluídas:
                        1. Personal Information (Full Name, DOB, Gender, ID Card, Tax Number, Biometric ID, Marital Status, Dependents, Photo)
                        2. Contact Information (Email, Phone, Address)
                        3. Bank Information (Bank Name, Account, IBAN, INSS)
                        4. Employment Information (Department, Position, Hire Date, Status)
                        5. Salary & Benefits (Base Salary, Hourly Rate, Food Benefit, Transport Benefit, Bonus)
                        6. Emergency Contact (Name, Relationship, Phone, Address)
                        
                        Cada seção tem seu próprio design com ícones, validações e styling consistente.
                    --}}
                    
                    <!-- Placeholder: O formulário completo deve ser inserido aqui -->
                    <div class="text-center py-12">
                        <i class="fas fa-file-import text-gray-300 text-6xl mb-4"></i>
                        <p class="text-gray-500">
                            Este arquivo é um placeholder.<br>
                            Copie o conteúdo completo do formulário das linhas 748-1620 do arquivo original.
                        </p>
                    </div>

                </form>
            </div>

            <!-- Sticky Footer -->
            <div class="sticky bottom-0 bg-gradient-to-r from-gray-50 to-gray-100 border-t border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        <i class="fas fa-info-circle mr-2"></i>
                        <span class="text-red-500">*</span> {{ __('messages.required_fields') }}
                    </div>
                    <div class="flex items-center space-x-3">
                        <button type="button"
                            class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200"
                            wire:click="closeModal">
                            <i class="fas fa-times mr-2"></i>
                            {{ __('messages.cancel') }}
                        </button>
                        <button type="submit"
                            class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r {{ $isEditing ? 'from-green-600 to-green-700 hover:from-green-700 hover:to-green-800' : 'from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800' }} hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $isEditing ? 'focus:ring-green-500' : 'focus:ring-blue-500' }} transform transition-all duration-200 hover:scale-105"
                            wire:loading.attr="disabled">
                            <i class="fas fa-save mr-2" wire:loading.remove></i>
                            <i class="fas fa-spinner fa-spin mr-2" wire:loading></i>
                            <span wire:loading.remove>{{ $isEditing ? __('messages.update') : __('messages.save') }}</span>
                            <span wire:loading>{{ __('messages.saving') }}...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
