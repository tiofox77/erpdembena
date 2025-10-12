{{-- Time Conflicts Modal --}}
@if($showTimeConflictsModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden m-4 transform transition-all duration-300">
            {{-- Modal Header --}}
            <div class="bg-gradient-to-r from-yellow-600 to-orange-600 px-6 py-4 rounded-t-xl">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">⚠️ Horas Duplicadas Detectadas</h3>
                            <p class="text-yellow-100 text-sm">Por favor, selecione as horas corretas para cada funcionário</p>
                        </div>
                    </div>
                    <button type="button" 
                        class="text-white/80 hover:text-white hover:bg-white/20 rounded-lg p-2 transition-all duration-200" 
                        wire:click="closeTimeConflictsModal">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            {{-- Modal Body --}}
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-200px)]">
                <div class="space-y-4">
                    @foreach($timeConflicts as $index => $conflict)
                        <div class="border border-orange-200 rounded-lg p-4 bg-orange-50">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <h4 class="font-semibold text-gray-900">{{ $conflict['employee_name'] }}</h4>
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-calendar text-orange-500 mr-1"></i>
                                        {{ \Carbon\Carbon::parse($conflict['date'])->format('d/m/Y') }}
                                        <span class="mx-2">•</span>
                                        <i class="fas fa-id-badge text-orange-500 mr-1"></i>
                                        Emp ID: {{ $conflict['emp_id'] }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Check-In --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-sign-in-alt text-green-500 mr-1"></i>
                                        Hora de Entrada
                                    </label>
                                    @if(count($conflict['check_in_options']) > 0)
                                        <div class="space-y-2">
                                            @foreach($conflict['check_in_options'] as $timeOption)
                                                @php
                                                    $isSelected = ($selectedTimes[$index]['check_in'] ?? null) === $timeOption;
                                                @endphp
                                                <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer transition-all {{ $isSelected ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:bg-gray-50 hover:border-blue-300' }}">
                                                    <input type="radio" 
                                                           wire:model.live="selectedTimes.{{ $index }}.check_in" 
                                                           value="{{ $timeOption }}"
                                                           class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                                    <span class="ml-3 text-lg font-mono font-semibold {{ $isSelected ? 'text-blue-700' : 'text-gray-900' }}">{{ $timeOption }}</span>
                                                    @if($isSelected)
                                                        <i class="fas fa-check-circle text-blue-600 ml-auto"></i>
                                                    @endif
                                                </label>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-gray-500 italic">Sem hora de entrada</p>
                                    @endif
                                </div>

                                {{-- Check-Out --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-sign-out-alt text-red-500 mr-1"></i>
                                        Hora de Saída
                                    </label>
                                    @if(count($conflict['check_out_options']) > 0)
                                        <div class="space-y-2">
                                            @foreach($conflict['check_out_options'] as $timeOption)
                                                @php
                                                    $isSelected = ($selectedTimes[$index]['check_out'] ?? null) === $timeOption;
                                                @endphp
                                                <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer transition-all {{ $isSelected ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:bg-gray-50 hover:border-blue-300' }}">
                                                    <input type="radio" 
                                                           wire:model.live="selectedTimes.{{ $index }}.check_out" 
                                                           value="{{ $timeOption }}"
                                                           class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                                    <span class="ml-3 text-lg font-mono font-semibold {{ $isSelected ? 'text-blue-700' : 'text-gray-900' }}">{{ $timeOption }}</span>
                                                    @if($isSelected)
                                                        <i class="fas fa-check-circle text-blue-600 ml-auto"></i>
                                                    @endif
                                                </label>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-gray-500 italic">Sem hora de saída</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Original Raw Data --}}
                            <div class="mt-3 p-2 bg-gray-100 rounded text-xs text-gray-600">
                                <strong>Dados originais:</strong> 
                                Check-In: <code>{{ $conflict['check_in_raw'] ?? 'N/A' }}</code> | 
                                Check-Out: <code>{{ $conflict['check_out_raw'] ?? 'N/A' }}</code>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-between items-center">
                <p class="text-sm text-gray-600">
                    <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                    Total de conflitos: <strong>{{ count($timeConflicts) }}</strong>
                </p>
                <div class="flex space-x-3">
                    <button type="button"
                        wire:click="closeTimeConflictsModal"
                        class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </button>
                    <button type="button"
                        wire:click="processConflictResolution"
                        wire:loading.attr="disabled"
                        class="px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="processConflictResolution">
                            <i class="fas fa-check mr-2"></i>
                            Confirmar e Importar
                        </span>
                        <span wire:loading wire:target="processConflictResolution" class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
