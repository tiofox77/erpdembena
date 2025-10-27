<!-- Document Upload Modal -->
@if($showDocumentModal)
<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden m-4 transform transition-all duration-300 ease-out">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-4 rounded-t-xl">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <div class="bg-white/20 p-2 rounded-lg mr-3">
                        <i class="fas fa-file-upload text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-white">{{ __('messages.upload_document') }}</h3>
                        <p class="text-blue-100 text-sm">{{ __('messages.upload_document_description') }}</p>
                    </div>
                </div>
                <button type="button" 
                    class="text-white/80 hover:text-white hover:bg-white/20 rounded-lg p-2 transition-all duration-200" 
                    wire:click="closeDocumentModal">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Modal Content -->
        <div class="max-h-[calc(90vh-120px)] overflow-y-auto">
            <!-- Error Messages -->
            @if($errors->any())
                <div class="mx-6 mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                        <p class="font-semibold text-red-700">{{ __('messages.please_correct_errors') }}</p>
                    </div>
                    <ul class="mt-2 text-sm text-red-600 space-y-1">
                        @foreach($errors->all() as $error)
                            <li class="flex items-center">
                                <i class="fas fa-dot-circle text-xs mr-2"></i>
                                {{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form wire:submit.prevent="uploadDocument" class="p-6" id="uploadDocument">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Document Type -->
                    <div class="md:col-span-2 space-y-2">
                        <label for="newDocumentType" class="flex items-center text-sm font-medium text-gray-700">
                            <i class="fas fa-file-alt text-indigo-500 mr-2"></i>
                            {{ __('messages.document_type') }} <span class="text-red-500 ml-1">*</span>
                        </label>
                        <div class="relative">
                            <select id="newDocumentType" wire:model="newDocumentType" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 appearance-none bg-white @error('newDocumentType') border-red-500 bg-red-50 @enderror">
                                <option value="">{{ __('messages.select_document_type') }}</option>
                                <option value="id_card">🆔 {{ __('messages.id_card') }}</option>
                                <option value="passport">🛂 {{ __('messages.passport') }}</option>
                                <option value="visa">✈️ {{ __('messages.visa') }}</option>
                                <option value="driving_license">🚗 {{ __('messages.driving_license') }}</option>
                                <option value="certificate">📜 {{ __('messages.certificate') }}</option>
                                <option value="diploma">🎓 {{ __('messages.diploma') }}</option>
                                <option value="professional_card">💼 {{ __('messages.professional_card') }}</option>
                                <option value="work_permit">📋 {{ __('messages.work_permit') }}</option>
                                <option value="contract">📄 {{ __('messages.contract') }}</option>
                                <option value="health_certificate">🏥 {{ __('messages.health_certificate') }}</option>
                                <option value="insurance">🛡️ {{ __('messages.insurance') }}</option>
                                <option value="tax_clearance">💰 {{ __('messages.tax_clearance') }}</option>
                                <option value="criminal_record">⚖️ {{ __('messages.criminal_record') }}</option>
                                <option value="other">📁 {{ __('messages.other') }}</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                @error('newDocumentType')
                                    <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                                @enderror
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </div>
                        </div>
                        @error('newDocumentType')
                            <p class="flex items-center text-sm text-red-600">
                                <i class="fas fa-info-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Document Title -->
                    <div class="space-y-2">
                        <label for="newDocumentTitle" class="flex items-center text-sm font-medium text-gray-700">
                            <i class="fas fa-tag text-green-500 mr-2"></i>
                            {{ __('messages.document_title') }} <span class="text-red-500 ml-1">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" id="newDocumentTitle" wire:model="newDocumentTitle" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 @error('newDocumentTitle') border-red-500 bg-red-50 @enderror"
                                placeholder="{{ __('messages.document_title_placeholder') }}">
                            @error('newDocumentTitle')
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <i class="fas fa-exclamation-circle text-red-500"></i>
                                </div>
                            @enderror
                        </div>
                        @error('newDocumentTitle')
                            <p class="flex items-center text-sm text-red-600">
                                <i class="fas fa-info-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Expiry Date -->
                    <div class="space-y-2">
                        <label for="newDocumentExpiryDate" class="flex items-center text-sm font-medium text-gray-700">
                            <i class="fas fa-calendar-alt text-yellow-500 mr-2"></i>
                            {{ __('messages.expiry_date_optional') }}
                        </label>
                        <div class="relative">
                            <input type="date" id="newDocumentExpiryDate" wire:model="newDocumentExpiryDate" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-all duration-200 @error('newDocumentExpiryDate') border-red-500 bg-red-50 @enderror">
                            @error('newDocumentExpiryDate')
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <i class="fas fa-exclamation-circle text-red-500"></i>
                                </div>
                            @enderror
                        </div>
                        @error('newDocumentExpiryDate')
                            <p class="flex items-center text-sm text-red-600">
                                <i class="fas fa-info-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Document File -->
                    <div class="md:col-span-2 space-y-2">
                        <label for="newDocumentFile" class="flex items-center text-sm font-medium text-gray-700">
                            <i class="fas fa-cloud-upload-alt text-purple-500 mr-2"></i>
                            {{ __('messages.upload_file') }} <span class="text-red-500 ml-1">*</span>
                        </label>
                        <div class="relative">
                            <input type="file" id="newDocumentFile" 
                                accept="image/*,.pdf,.doc,.docx"
                                class="block w-full text-sm text-gray-600 file:mr-4 file:py-3 file:px-6 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 file:transition-all file:duration-200 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 @error('newDocumentFile') border-red-500 bg-red-50 @enderror"
                                wire:model="newDocumentFile">
                        </div>
                        <p class="flex items-center text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            {{ __('messages.file_size_restrictions') }}
                        </p>
                        @error('newDocumentFile')
                            <p class="flex items-center text-sm text-red-600">
                                <i class="fas fa-info-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Remarks -->
                    <div class="md:col-span-2 space-y-2">
                        <label for="newDocumentRemarks" class="flex items-center text-sm font-medium text-gray-700">
                            <i class="fas fa-comment-alt text-cyan-500 mr-2"></i>
                            {{ __('messages.remarks_optional') }}
                        </label>
                        <div class="relative">
                            <textarea id="newDocumentRemarks" wire:model="newDocumentRemarks" rows="3" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-all duration-200 resize-none @error('newDocumentRemarks') border-red-500 bg-red-50 @enderror"
                                placeholder="{{ __('messages.document_remarks_placeholder') }}"></textarea>
                            @error('newDocumentRemarks')
                                <div class="absolute top-3 right-3 pointer-events-none">
                                    <i class="fas fa-exclamation-circle text-red-500"></i>
                                </div>
                            @enderror
                        </div>
                        @error('newDocumentRemarks')
                            <p class="flex items-center text-sm text-red-600">
                                <i class="fas fa-info-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="border-t border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 rounded-b-xl">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    {{ __('messages.upload_document_footer_info') }}
                </div>
                <div class="flex items-center space-x-3">
                    <button type="button" wire:click="closeDocumentModal"
                        class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                        <i class="fas fa-times mr-2"></i>
                        {{ __('messages.cancel') }}
                    </button>
                    <button type="submit" form="uploadDocument"
                        class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transform transition-all duration-200 hover:scale-105">
                        <i class="fas fa-cloud-upload-alt mr-2"></i>
                        {{ __('messages.upload_document') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
