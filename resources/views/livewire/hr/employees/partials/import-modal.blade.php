<!-- Import Modal -->
@if($showImportModal)
<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto backdrop-blur-sm" 
     x-data="{ uploading: false }"
     @click.self="$wire.closeImportModal()">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[95vh] overflow-hidden m-4 transform transition-all duration-300"
         @click.stop>
        <!-- Modern Header with Gradient -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center">
                <div class="bg-white/20 rounded-full p-2 mr-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-white">{{ __('messages.import_employees') }}</h2>
            </div>
            <button type="button" 
                    wire:click="closeImportModal" 
                    wire:loading.attr="disabled"
                    wire:target="importFromExcel"
                    class="text-white/80 hover:text-white transition-colors duration-200 p-2 rounded-full hover:bg-white/10 disabled:opacity-50">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <!-- File Upload -->
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-file-upload text-indigo-600 mr-1"></i>
                        {{ __('messages.select_file') }}
                    </label>
                    <div class="relative">
                        <input wire:model="importFile" 
                               type="file" 
                               accept=".xlsx,.xls,.csv"
                               id="importFileInput"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-gray-300 rounded-lg cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all duration-200">
                        
                        <!-- File Upload Progress -->
                        <div wire:loading wire:target="importFile" class="mt-2">
                            <div class="flex items-center text-indigo-600 text-sm">
                                <svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                A carregar ficheiro...
                            </div>
                        </div>
                        
                        <!-- File Selected Indicator -->
                        @if($importFile)
                        <div class="mt-2 flex items-center text-sm text-green-600">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Ficheiro selecionado
                        </div>
                        @endif
                    </div>
                    @error('importFile') 
                        <div class="mt-2 flex items-center text-red-600 text-sm">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Download Template Button -->
                <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm text-indigo-700 font-medium mb-2">
                                Precisa de um modelo?
                            </p>
                            <button type="button"
                                    wire:click="downloadTemplate" 
                                    wire:loading.attr="disabled"
                                    wire:target="downloadTemplate"
                                    class="inline-flex items-center px-3 py-2 border border-indigo-300 text-sm font-medium rounded-lg text-indigo-700 bg-white hover:bg-indigo-100 transition-all duration-200 disabled:opacity-50 transform hover:scale-105">
                                <span wire:loading.remove wire:target="downloadTemplate">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                                    </svg>
                                    {{ __('messages.download_template') }}
                                </span>
                                <span wire:loading wire:target="downloadTemplate" class="flex items-center">
                                    <svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    A descarregar...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="flex items-center justify-between p-6 pt-4 border-t border-gray-200 bg-gray-50">
            <div class="text-xs text-gray-500">
                <i class="fas fa-info-circle mr-1"></i>
                Formatos aceites: XLSX, XLS, CSV
            </div>
            <div class="flex items-center space-x-3">
                <button type="button"
                        wire:click="closeImportModal" 
                        wire:loading.attr="disabled"
                        wire:target="importFromExcel"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-200 disabled:opacity-50">
                    {{ __('common.cancel') }}
                </button>
                <button type="button"
                        wire:click="importFromExcel" 
                        wire:loading.attr="disabled"
                        wire:target="importFromExcel"
                        x-data="{ importing: false }"
                        @click="importing = true"
                        x-on:livewire:finish="importing = false"
                        :disabled="!$wire.importFile || importing"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 border border-transparent rounded-lg hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                    <span wire:loading.remove wire:target="importFromExcel" class="flex items-center">
                        <i class="fas fa-upload mr-2"></i>
                        {{ __('messages.import') }}
                    </span>
                    <span wire:loading wire:target="importFromExcel" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="animate-pulse">Importando...</span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
@endif
