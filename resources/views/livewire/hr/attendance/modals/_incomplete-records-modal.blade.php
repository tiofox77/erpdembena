{{-- Incomplete Records Modal --}}
@if($showIncompleteModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden m-4 transform transition-all duration-300">
            {{-- Modal Header --}}
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4 rounded-t-xl">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-clock text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">⏰ Registos Incompletos Detectados</h3>
                            <p class="text-blue-100 text-sm">Encontrados funcionários com apenas 1 horário registado</p>
                        </div>
                    </div>
                    <button type="button" 
                        class="text-white/80 hover:text-white hover:bg-white/20 rounded-lg p-2 transition-all duration-200" 
                        wire:click="$set('showIncompleteModal', false)">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            {{-- Modal Body --}}
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-200px)]">
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                        <div>
                            <p class="text-sm text-blue-800">
                                Os seguintes funcionários têm apenas <strong>1 horário registado</strong> (entrada OU saída).
                                Isso pode acontecer quando:
                            </p>
                            <ul class="list-disc list-inside text-sm text-blue-700 mt-2 ml-4">
                                <li>O funcionário esqueceu de marcar a saída</li>
                                <li>O funcionário chegou tarde e só registou a saída</li>
                                <li>Houve falha no sistema biométrico</li>
                            </ul>
                            <p class="text-sm text-blue-800 mt-2 font-medium">
                                ⚠️ Confirme se deseja importar mesmo assim.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    @foreach($incompleteRecords as $record)
                        <div class="border border-blue-200 rounded-lg p-4 bg-white hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center flex-1">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center mr-3">
                                        <span class="text-white font-medium text-sm">{{ strtoupper(substr($record['employee_name'], 0, 1)) }}</span>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900">{{ $record['employee_name'] }}</h4>
                                        <p class="text-sm text-gray-600">
                                            <i class="fas fa-calendar text-blue-500 mr-1"></i>
                                            {{ \Carbon\Carbon::parse($record['date'])->format('d/m/Y') }}
                                            <span class="mx-2">•</span>
                                            <i class="fas fa-id-badge text-blue-500 mr-1"></i>
                                            Emp ID: {{ $record['emp_id'] }}
                                        </p>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    @if($record['type'] === 'check_in')
                                        <div class="flex items-center px-4 py-2 bg-green-100 text-green-800 rounded-lg">
                                            <i class="fas fa-sign-in-alt mr-2"></i>
                                            <div>
                                                <div class="text-xs font-medium">Entrada</div>
                                                <div class="text-sm font-bold">{{ $record['time'] }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex items-center px-4 py-2 bg-red-100 text-red-800 rounded-lg">
                                            <i class="fas fa-sign-out-alt mr-2"></i>
                                            <div>
                                                <div class="text-xs font-medium">Saída</div>
                                                <div class="text-sm font-bold">{{ $record['time'] }}</div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mt-1 mr-3"></i>
                        <div class="text-sm text-yellow-800">
                            <p class="font-medium">Total de registos incompletos: {{ count($incompleteRecords) }}</p>
                            <p class="mt-1">Ao confirmar, estes registos serão importados com apenas o horário disponível.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="border-t border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 rounded-b-xl">
                <div class="flex justify-end items-center space-x-3">
                    <button type="button"
                        wire:click="$set('showIncompleteModal', false)"
                        class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </button>
                    <button type="button"
                        wire:click="confirmIncompleteRecords"
                        class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105">
                        <i class="fas fa-check mr-2"></i>
                        Confirmar e Importar ({{ count($incompleteRecords) }})
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
