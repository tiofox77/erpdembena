{{-- Import Modal --}}
@if($showImportModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden m-4 transform transition-all duration-300">
            {{-- Modal Header --}}
            <div class="bg-gradient-to-r from-indigo-600 to-purple-700 px-6 py-4 rounded-t-xl">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-file-import text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">{{ __('messages.import_attendance') }}</h3>
                            <p class="text-indigo-100 text-sm">{{ __('messages.import_attendance_description') }}</p>
                        </div>
                    </div>
                    <button type="button" 
                        class="text-white/80 hover:text-white hover:bg-white/20 rounded-lg p-2 transition-all duration-200" 
                        wire:click="closeImportModal">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            {{-- Modal Body --}}
            <div class="p-6">
                {{-- Download Sample Files --}}
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-download text-green-600 text-lg mr-3"></i>
                            <div>
                                <h4 class="text-sm font-semibold text-green-800">{{ __('messages.download_sample_files') }}</h4>
                                <p class="text-xs text-green-600">{{ __('messages.download_sample_description') }}</p>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ route('hr.attendance.sample.zktime') }}" 
                               class="inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-lg transition-colors">
                                <i class="fas fa-file-excel mr-1"></i>
                                ZKTime Sample
                            </a>
                            <a href="{{ route('hr.attendance.sample.standard') }}" 
                               class="inline-flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition-colors">
                                <i class="fas fa-file-excel mr-1"></i>
                                Standard Sample
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Instructions --}}
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-500 text-lg"></i>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-semibold text-blue-800 mb-2">{{ __('messages.import_instructions') }}</h4>
                            <ul class="text-sm text-blue-700 space-y-1 list-disc list-inside">
                                <li>{{ __('messages.attendance_import_instruction_1') }}</li>
                                <li>{{ __('messages.attendance_import_instruction_2') }}</li>
                                <li>{{ __('messages.attendance_import_instruction_3') }}</li>
                                <li>{{ __('messages.attendance_import_instruction_4') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- File Upload --}}
                <div class="space-y-4">
                    <label class="flex items-center text-sm font-medium text-gray-700">
                        <i class="fas fa-file-excel text-green-600 mr-2"></i>
                        {{ __('messages.select_file') }}
                    </label>
                    
                    <div class="relative">
                        <input type="file" 
                               wire:model="importFile" 
                               accept=".xlsx,.xls,.csv"
                               class="block w-full text-sm text-gray-600 
                                      file:mr-4 file:py-3 file:px-6 
                                      file:rounded-lg file:border-0 
                                      file:text-sm file:font-medium 
                                      file:bg-indigo-50 file:text-indigo-700 
                                      hover:file:bg-indigo-100 
                                      file:transition-all file:duration-200 
                                      border border-gray-300 rounded-lg 
                                      focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                                      cursor-pointer">
                    </div>

                    @error('importFile')
                        <p class="flex items-center text-sm text-red-600">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror

                    @if($importFile)
                        <div class="bg-green-50 border border-green-200 rounded-lg p-3 flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span class="text-sm text-green-700">
                                {{ __('messages.file_selected') }}: <strong>{{ $importFile->getClientOriginalName() }}</strong>
                            </span>
                        </div>
                    @endif

                    {{-- Expected Format Info --}}
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <h5 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                            <i class="fas fa-table text-gray-600 mr-2"></i>
                            {{ __('messages.expected_excel_format') }}
                        </h5>
                        
                        {{-- Formato ZKTime (Atual) --}}
                        <div>
                            <div class="flex items-center mb-3">
                                <span class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-semibold bg-green-100 text-green-800">
                                    <i class="fas fa-file-excel mr-2"></i> Formato ZKTime
                                </span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-xs border border-gray-200 rounded">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="px-2 py-1 text-left font-medium text-gray-700 border-r">Emp ID</th>
                                            <th class="px-2 py-1 text-left font-medium text-gray-700 border-r">Name</th>
                                            <th class="px-2 py-1 text-left font-medium text-gray-700 border-r">Time</th>
                                            <th class="px-2 py-1 text-left font-medium text-gray-700 border-r">Work Code</th>
                                            <th class="px-2 py-1 text-left font-medium text-gray-700 border-r">Attendance State</th>
                                            <th class="px-2 py-1 text-left font-medium text-gray-700">Device Name</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white">
                                        <tr class="border-t border-gray-200">
                                            <td class="px-2 py-1 text-gray-600 border-r">5</td>
                                            <td class="px-2 py-1 text-gray-600 border-r">Delfina.Paulo</td>
                                            <td class="px-2 py-1 text-gray-600 border-r">28/10/2025 08:14:54</td>
                                            <td class="px-2 py-1 text-gray-600 border-r"></td>
                                            <td class="px-2 py-1 text-gray-600 border-r font-semibold text-green-600">Check In</td>
                                            <td class="px-2 py-1 text-gray-600">escritorio</td>
                                        </tr>
                                        <tr class="border-t border-gray-200">
                                            <td class="px-2 py-1 text-gray-600 border-r">5</td>
                                            <td class="px-2 py-1 text-gray-600 border-r">Delfina.Paulo</td>
                                            <td class="px-2 py-1 text-gray-600 border-r">28/10/2025 16:25:29</td>
                                            <td class="px-2 py-1 text-gray-600 border-r"></td>
                                            <td class="px-2 py-1 text-gray-600 border-r font-semibold text-red-600">Check Out</td>
                                            <td class="px-2 py-1 text-gray-600">escritorio</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-xs text-gray-500 mt-2 italic">
                                <i class="fas fa-info-circle mr-1 text-blue-500"></i>
                                Cada Check-In e Check-Out numa linha separada
                            </p>
                            <p class="text-xs text-gray-600 mt-2 pt-2 border-t border-gray-200">
                                <i class="fas fa-fingerprint text-indigo-500 mr-1"></i>
                                <strong>Emp ID</strong> {{ __('messages.must_match_biometric_id') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="border-t border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 rounded-b-xl">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-600">
                        <i class="fas fa-info-circle mr-1"></i>
                        {{ __('messages.max_file_size') }}: <strong>10MB</strong>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button type="button"
                            class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200"
                            wire:click="closeImportModal">
                            <i class="fas fa-times mr-2"></i>
                            {{ __('messages.cancel') }}
                        </button>
                        <button type="button"
                            wire:click="importFromExcel"
                            wire:loading.attr="disabled"
                            :disabled="!$wire.importFile"
                            class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-upload mr-2" wire:loading.remove wire:target="importFromExcel"></i>
                            <i class="fas fa-spinner fa-spin mr-2" wire:loading wire:target="importFromExcel"></i>
                            <span wire:loading.remove wire:target="importFromExcel">{{ __('messages.import') }}</span>
                            <span wire:loading wire:target="importFromExcel">{{ __('messages.importing') }}...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
