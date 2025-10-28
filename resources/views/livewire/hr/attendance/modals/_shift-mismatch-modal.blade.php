{{-- Shift Mismatch Modal --}}
@if($showShiftMismatchModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-5xl max-h-[90vh] overflow-hidden m-4 transform transition-all duration-300">
            {{-- Modal Header --}}
            <div class="bg-gradient-to-r from-yellow-600 to-orange-600 px-6 py-4 rounded-t-xl">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">⚠️ Incompatibilidade de Turno Detectada</h3>
                            <p class="text-yellow-100 text-sm">Horários registados não correspondem ao turno do funcionário</p>
                        </div>
                    </div>
                    <button type="button" 
                        class="text-white/80 hover:text-white hover:bg-white/20 rounded-lg p-2 transition-all duration-200" 
                        wire:click="$set('showShiftMismatchModal', false)">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            {{-- Modal Body --}}
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-200px)]">
                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-4">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-yellow-600 mt-1 mr-3"></i>
                        <div>
                            <p class="text-sm text-yellow-800 font-medium">
                                Os seguintes funcionários têm <strong>horários incompatíveis</strong> com seus turnos atribuídos.
                            </p>
                            <p class="text-sm text-yellow-700 mt-2">
                                Isso pode indicar:
                            </p>
                            <ul class="list-disc list-inside text-sm text-yellow-700 mt-1 ml-4">
                                <li>Turno atribuído incorreto no sistema</li>
                                <li>Funcionário trabalhou fora do horário habitual</li>
                                <li>Erro no registro biométrico</li>
                            </ul>
                            <p class="text-sm text-yellow-800 mt-2 font-medium">
                                ⚠️ Revise e confirme se deseja importar mesmo assim.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    @foreach($shiftMismatches as $mismatch)
                        <div class="border border-yellow-200 rounded-lg p-4 bg-white hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between">
                                <div class="flex items-start flex-1">
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-r from-yellow-500 to-orange-600 flex items-center justify-center mr-3 flex-shrink-0">
                                        <span class="text-white font-bold text-sm">{{ strtoupper(substr($mismatch['employee_name'], 0, 1)) }}</span>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900 text-lg">{{ $mismatch['employee_name'] }}</h4>
                                        <p class="text-sm text-gray-600">
                                            <i class="fas fa-calendar text-yellow-500 mr-1"></i>
                                            {{ \Carbon\Carbon::parse($mismatch['date'])->format('d/m/Y') }}
                                            <span class="mx-2">•</span>
                                            <i class="fas fa-id-badge text-yellow-500 mr-1"></i>
                                            Emp ID: {{ $mismatch['emp_id'] }}
                                        </p>
                                        
                                        {{-- Horários Registrados --}}
                                        <div class="mt-3 bg-blue-50 border border-blue-200 rounded-lg p-3">
                                            <div class="text-xs font-semibold text-blue-800 mb-2">
                                                <i class="fas fa-fingerprint mr-1"></i> Horários Registrados no Biométrico:
                                            </div>
                                            <div class="grid grid-cols-2 gap-2">
                                                @if($mismatch['check_in'])
                                                    <div class="flex items-center">
                                                        <i class="fas fa-sign-in-alt text-green-600 mr-2"></i>
                                                        <div>
                                                            <div class="text-xs text-gray-600">Entrada</div>
                                                            <div class="text-sm font-bold text-gray-900">{{ $mismatch['check_in'] }}</div>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if($mismatch['check_out'])
                                                    <div class="flex items-center">
                                                        <i class="fas fa-sign-out-alt text-red-600 mr-2"></i>
                                                        <div>
                                                            <div class="text-xs text-gray-600">Saída</div>
                                                            <div class="text-sm font-bold text-gray-900">{{ $mismatch['check_out'] }}</div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Turno Atribuído --}}
                                        <div class="mt-2 bg-orange-50 border border-orange-200 rounded-lg p-3">
                                            <div class="text-xs font-semibold text-orange-800 mb-2">
                                                <i class="fas fa-clock mr-1"></i> Turno Atribuído:
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <div class="text-sm font-bold text-gray-900">{{ $mismatch['shift_name'] }}</div>
                                                    <div class="text-xs text-gray-600">
                                                        {{ $mismatch['shift_start'] }} - {{ $mismatch['shift_end'] }}
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-xs text-gray-600">Diferença</div>
                                                    <div class="text-sm font-bold text-orange-600">
                                                        {{ floor($mismatch['time_difference_minutes'] / 60) }}h {{ $mismatch['time_difference_minutes'] % 60 }}m
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="ml-4 flex-shrink-0">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        Incompatível
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-circle text-red-600 mt-1 mr-3"></i>
                        <div class="text-sm text-red-800">
                            <p class="font-medium">Total de incompatibilidades: {{ count($shiftMismatches) }}</p>
                            <p class="mt-1">Ao confirmar, estes registos serão importados com uma observação de incompatibilidade de turno.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="border-t border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 rounded-b-xl">
                <div class="flex justify-end items-center space-x-3">
                    <button type="button"
                        wire:click="$set('showShiftMismatchModal', false)"
                        class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar Importação
                    </button>
                    <button type="button"
                        wire:click="confirmShiftMismatches"
                        class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-yellow-600 to-orange-600 hover:from-yellow-700 hover:to-orange-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-all duration-200 transform hover:scale-105">
                        <i class="fas fa-check mr-2"></i>
                        Confirmar e Importar Mesmo Assim ({{ count($shiftMismatches) }})
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
