{{-- Daily Attendance Import Modal --}}
@if($showDailyImportModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-hidden m-4 transform transition-all duration-300">
            {{-- Modal Header --}}
            <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-4 rounded-t-xl">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-file-excel text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">📅 Importar Atendimento Diário</h3>
                            <p class="text-green-100 text-sm">Arquivo Excel com múltiplas folhas (uma por dia)</p>
                        </div>
                    </div>
                    <button type="button" 
                        class="text-white/80 hover:text-white hover:bg-white/20 rounded-lg p-2 transition-all duration-200" 
                        wire:click="closeDailyImportModal">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            {{-- Modal Body --}}
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-200px)]">
                @if(!$showDailyImportPreview)
                    {{-- Info Box --}}
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                            <div>
                                <p class="text-sm text-blue-800 font-medium mb-2">
                                    Formato esperado do arquivo Excel:
                                </p>
                                <ul class="list-disc list-inside text-sm text-blue-700 space-y-1 ml-2">
                                    <li><strong>Múltiplas folhas:</strong> Uma folha por dia (ex: "atendimento dia 20-10-25")</li>
                                    <li><strong>Coluna A:</strong> Biometric_id</li>
                                    <li><strong>Coluna B:</strong> Nome</li>
                                    <li><strong>Coluna C:</strong> data</li>
                                    <li><strong>Coluna D:</strong> check_in (entrada)</li>
                                    <li><strong>Coluna E:</strong> check_out (saída)</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- File Upload --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-file-upload text-gray-500 mr-2"></i>
                        Selecione o arquivo Excel
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-green-500 transition-colors">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="daily-file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-green-600 hover:text-green-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-green-500">
                                    <span>Carregar arquivo</span>
                                    <input 
                                        id="daily-file-upload" 
                                        wire:model="dailyImportFile" 
                                        type="file" 
                                        accept=".xlsx,.xls" 
                                        class="sr-only">
                                </label>
                                <p class="pl-1">ou arraste aqui</p>
                            </div>
                            <p class="text-xs text-gray-500">
                                Formatos: .xlsx, .xls (máximo 10MB)
                            </p>
                        </div>
                    </div>
                    
                    @if($dailyImportFile)
                        <div class="mt-3 flex items-center justify-between bg-green-50 border border-green-200 rounded-lg p-3">
                            <div class="flex items-center">
                                <i class="fas fa-file-excel text-green-600 text-2xl mr-3"></i>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $dailyImportFile->getClientOriginalName() }}</p>
                                    <p class="text-xs text-gray-500">{{ number_format($dailyImportFile->getSize() / 1024, 2) }} KB</p>
                                </div>
                            </div>
                            <button type="button" wire:click="$set('dailyImportFile', null)" class="text-red-500 hover:text-red-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    @endif

                    @error('dailyImportFile')
                        <p class="mt-2 text-sm text-red-600">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Preview Info --}}
                @if($dailyImportFile)
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-600 mr-3"></i>
                            <div>
                                <p class="text-sm font-medium text-green-900">Arquivo pronto para preview</p>
                                <p class="text-xs text-green-700 mt-1">
                                    Clique em "Gerar Preview" para ver o que será importado.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
                @else
                    {{-- Preview Section --}}
                    <div class="space-y-4">
                        {{-- Summary Stats --}}
                        <div class="grid grid-cols-4 gap-4 mb-6">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold text-blue-600">{{ $dailyImportPreview['total_sheets'] ?? 0 }}</div>
                                <div class="text-xs text-blue-700 mt-1">Folhas</div>
                            </div>
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold text-green-600">{{ $dailyImportPreview['total_records'] ?? 0 }}</div>
                                <div class="text-xs text-green-700 mt-1">Registros</div>
                            </div>
                            <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold text-emerald-600">{{ $dailyImportPreview['employees_found'] ?? 0 }}</div>
                                <div class="text-xs text-emerald-700 mt-1">Encontrados</div>
                            </div>
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold text-red-600">{{ $dailyImportPreview['employees_not_found'] ?? 0 }}</div>
                                <div class="text-xs text-red-700 mt-1">Não Encontrados</div>
                            </div>
                        </div>

                        {{-- Sheets Preview --}}
                        @if(!empty($dailyImportPreview['sheets']))
                            <div class="space-y-3">
                                @foreach($dailyImportPreview['sheets'] as $sheet)
                                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                                        <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between">
                                            <div class="flex items-center">
                                                <i class="fas fa-file-alt text-gray-500 mr-2"></i>
                                                <span class="font-medium text-gray-700">{{ $sheet['name'] }}</span>
                                            </div>
                                            <span class="text-xs text-gray-500">{{ $sheet['total_rows'] }} registros</span>
                                        </div>
                                        <div class="p-3">
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full text-xs">
                                                    <thead class="bg-gray-50">
                                                        <tr>
                                                            <th class="px-2 py-1 text-left font-medium text-gray-600">ID</th>
                                                            <th class="px-2 py-1 text-left font-medium text-gray-600">Nome</th>
                                                            <th class="px-2 py-1 text-left font-medium text-gray-600">Data</th>
                                                            <th class="px-2 py-1 text-left font-medium text-gray-600">Entrada</th>
                                                            <th class="px-2 py-1 text-left font-medium text-gray-600">Saída</th>
                                                            <th class="px-2 py-1 text-left font-medium text-gray-600">Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-gray-200">
                                                        @foreach($sheet['sample_rows'] as $row)
                                                            <tr class="{{ $row['found'] ? 'bg-white' : 'bg-red-50' }}">
                                                                <td class="px-2 py-1">{{ $row['biometric_id'] }}</td>
                                                                <td class="px-2 py-1">{{ $row['name'] }}</td>
                                                                <td class="px-2 py-1">{{ $row['date'] }}</td>
                                                                <td class="px-2 py-1">{{ $row['check_in'] }}</td>
                                                                <td class="px-2 py-1">{{ $row['check_out'] }}</td>
                                                                <td class="px-2 py-1">
                                                                    @if($row['found'])
                                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                                            <i class="fas fa-check mr-1"></i> OK
                                                                        </span>
                                                                    @else
                                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                                            <i class="fas fa-times mr-1"></i> Não encontrado
                                                                        </span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            @if($sheet['total_rows'] > 5)
                                                <p class="text-xs text-gray-500 mt-2 text-center">
                                                    ... e mais {{ $sheet['total_rows'] - 5 }} registros
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Loading State --}}
                <div wire:loading wire:target="generateDailyImportPreview,confirmDailyImport" class="mt-4">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-center">
                        <svg class="animate-spin h-5 w-5 text-blue-600 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-blue-900">Processando...</p>
                            <p class="text-xs text-blue-700">Por favor, aguarde.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="border-t border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 rounded-b-xl">
                <div class="flex justify-end items-center space-x-3">
                    @if(!$showDailyImportPreview)
                        <button type="button"
                            wire:click="closeDailyImportModal"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </button>
                        <button type="button"
                            wire:click="generateDailyImportPreview"
                            wire:loading.attr="disabled"
                            @if(!$dailyImportFile) disabled @endif
                            class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="generateDailyImportPreview">
                                <i class="fas fa-eye mr-2"></i>
                                Gerar Preview
                            </span>
                            <span wire:loading wire:target="generateDailyImportPreview">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                Gerando...
                            </span>
                        </button>
                    @else
                        <button type="button"
                            wire:click="backToDailyFileSelection"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Voltar
                        </button>
                        <button type="button"
                            wire:click="confirmDailyImport"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 transform hover:scale-105">
                            <span wire:loading.remove wire:target="confirmDailyImport">
                                <i class="fas fa-check mr-2"></i>
                                Confirmar e Importar
                            </span>
                            <span wire:loading wire:target="confirmDailyImport">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                Importando...
                            </span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
