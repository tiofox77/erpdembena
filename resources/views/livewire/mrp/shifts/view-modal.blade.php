<div>
    <div x-data="{ open: @entangle('showViewModal') }" 
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
        <div class="relative top-20 mx-auto p-1 w-full max-w-3xl">
            <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" 
                 x-transition:enter="transition ease-out duration-300" 
                 x-transition:enter-start="transform opacity-0 scale-95" 
                 x-transition:enter-end="transform opacity-100 scale-100" 
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="transform opacity-100 scale-100" 
                 x-transition:leave-end="transform opacity-0 scale-95">
                
                <!-- Cabeçalho com gradiente verde -->
                <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-eye mr-2"></i>
                        {{ __('messages.view_shift_details') }}
                    </h3>
                    <button type="button" wire:click="closeViewModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <!-- Corpo do modal -->
                <div class="p-6 space-y-6">
                    @if(isset($shift))
                        <!-- Informações Principais -->
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                            <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                <h3 class="text-base font-medium text-gray-700">{{ __('messages.shift_details') }}</h3>
                            </div>
                            <div class="p-4">
                                <div class="flex items-center mb-4">
                                    <div class="w-5 h-5 rounded-full mr-2" style="background-color: {{ $shift['color_code'] ?? '#3B82F6' }}"></div>
                                    <h2 class="text-xl font-semibold text-gray-800">{{ $shift['name'] ?? 'N/A' }}</h2>
                                    <div class="ml-3">
                                        @if(isset($shift['is_active']) && $shift['is_active'])
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                {{ __('messages.active') }}
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                {{ __('messages.inactive') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                    <!-- Horários -->
                                    <div class="bg-gray-50 p-3 rounded-lg">
                                        <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                                            <i class="fas fa-clock text-blue-500 mr-2"></i>
                                            {{ __('messages.schedule') }}
                                        </h4>
                                        <div class="flex items-center mb-2">
                                            <span class="text-sm font-medium text-gray-500 mr-2">{{ __('messages.start_time') }}:</span>
                                            <span class="text-sm text-gray-800">{{ $shift['start_time'] ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex items-center mb-2">
                                            <span class="text-sm font-medium text-gray-500 mr-2">{{ __('messages.end_time') }}:</span>
                                            <span class="text-sm text-gray-800">{{ $shift['end_time'] ?? 'N/A' }}</span>
                                        </div>
                                        @php
                                            $duration = 0;
                                            $startTime = null;
                                            $endTime = null;
                                            
                                            // Função para verificar se uma string está no formato H:i
                                            $isValidTimeFormat = function($time) {
                                                return is_string($time) && preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time);
                                            };
                                            
                                            // Criar objetos Carbon apenas se o formato for válido
                                            if (isset($shift['start_time']) && $isValidTimeFormat($shift['start_time'])) {
                                                try {
                                                    $startTime = \Carbon\Carbon::createFromFormat('H:i', $shift['start_time']);
                                                } catch (\Exception $e) {
                                                    // Silenciosamente falha e mantém $startTime como null
                                                }
                                            }
                                            
                                            if (isset($shift['end_time']) && $isValidTimeFormat($shift['end_time'])) {
                                                try {
                                                    $endTime = \Carbon\Carbon::createFromFormat('H:i', $shift['end_time']);
                                                } catch (\Exception $e) {
                                                    // Silenciosamente falha e mantém $endTime como null
                                                }
                                            }
                                            
                                            if ($startTime && $endTime) {
                                                if ($endTime < $startTime) {
                                                    $endTime->addDay();
                                                }
                                                $duration = $startTime->diffInMinutes($endTime) / 60;
                                                
                                                // Subtrair intervalo se existir
                                                if (!empty($shift['break_start']) && !empty($shift['break_end']) && 
                                                    $isValidTimeFormat($shift['break_start']) && $isValidTimeFormat($shift['break_end'])) {
                                                    try {
                                                        $breakStart = \Carbon\Carbon::createFromFormat('H:i', $shift['break_start']);
                                                        $breakEnd = \Carbon\Carbon::createFromFormat('H:i', $shift['break_end']);
                                                        
                                                        // Verificar se o horário do fim do intervalo é menor que o início do intervalo (intervalo cruzando a meia-noite)
                                                        if ($breakEnd < $breakStart) {
                                                            $breakEnd->addDay();
                                                        }
                                                        
                                                        $breakDuration = $breakStart->diffInMinutes($breakEnd) / 60;
                                                        $duration -= $breakDuration;
                                                    } catch (\Exception $e) {
                                                        // Silenciosamente falha se houver algum erro
                                                    }
                                                }
                                            }
                                        @endphp
                                        <div class="flex items-center">
                                            <span class="text-sm font-medium text-gray-500 mr-2">{{ __('messages.total_duration') }}:</span>
                                            <span class="text-sm text-gray-800">{{ number_format($duration, 1) }} {{ __('messages.hours') }}</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Intervalo -->
                                    <div class="bg-gray-50 p-3 rounded-lg">
                                        <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                                            <i class="fas fa-coffee text-green-500 mr-2"></i>
                                            {{ __('messages.break_times') }}
                                        </h4>
                                        <div class="flex items-center mb-2">
                                            <span class="text-sm font-medium text-gray-500 mr-2">{{ __('messages.break_start') }}:</span>
                                            <span class="text-sm text-gray-800">{{ $shift['break_start'] ?? __('messages.not_set') }}</span>
                                        </div>
                                        <div class="flex items-center mb-2">
                                            <span class="text-sm font-medium text-gray-500 mr-2">{{ __('messages.break_end') }}:</span>
                                            <span class="text-sm text-gray-800">{{ $shift['break_end'] ?? __('messages.not_set') }}</span>
                                        </div>
                                        @php
                                            $breakDuration = 0;
                                            if (!empty($shift['break_start']) && !empty($shift['break_end'])) {
                                                $breakStart = \Carbon\Carbon::createFromFormat('H:i', $shift['break_start']);
                                                $breakEnd = \Carbon\Carbon::createFromFormat('H:i', $shift['break_end']);
                                                $breakDuration = $breakEnd->diffInMinutes($breakStart) / 60;
                                            }
                                        @endphp
                                        <div class="flex items-center">
                                            <span class="text-sm font-medium text-gray-500 mr-2">{{ __('messages.break_duration') }}:</span>
                                            <span class="text-sm text-gray-800">
                                                @if($breakDuration > 0)
                                                    {{ number_format($breakDuration, 1) }} {{ __('messages.hours') }}
                                                @else
                                                    {{ __('messages.not_set') }}
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <!-- Dias de Trabalho -->
                                    <div class="bg-gray-50 p-3 rounded-lg md:col-span-2">
                                        <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                                            <i class="fas fa-calendar-alt text-purple-500 mr-2"></i>
                                            {{ __('messages.working_days') }}
                                        </h4>
                                        <div class="flex flex-wrap gap-2">
                                            @php
                                                $days = isset($shift['working_days']) ? (is_array($shift['working_days']) ? $shift['working_days'] : json_decode($shift['working_days'], true)) : [];
                                                $daysLabels = [
                                                    'monday' => 'Segunda',
                                                    'tuesday' => 'Terça',
                                                    'wednesday' => 'Quarta',
                                                    'thursday' => 'Quinta',
                                                    'friday' => 'Sexta',
                                                    'saturday' => 'Sábado',
                                                    'sunday' => 'Domingo'
                                                ];
                                            @endphp
                                            
                                            @if(!empty($days))
                                                @foreach($days as $day)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        {{ $daysLabels[$day] ?? $day }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-sm text-gray-500">{{ __('messages.no_working_days_set') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Descrição -->
                                    @if(!empty($shift['description']))
                                        <div class="bg-gray-50 p-3 rounded-lg md:col-span-2">
                                            <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                                                <i class="fas fa-align-left text-gray-500 mr-2"></i>
                                                {{ __('messages.description') }}
                                            </h4>
                                            <p class="text-sm text-gray-800">{{ $shift['description'] }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Metadados -->
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                            <div class="flex items-center bg-gradient-to-r from-gray-50 to-gray-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-history text-gray-600 mr-2"></i>
                                <h3 class="text-base font-medium text-gray-700">{{ __('messages.metadata') }}</h3>
                            </div>
                            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <span class="text-sm font-medium text-gray-500">{{ __('messages.created_at') }}:</span>
                                    <span class="text-sm text-gray-800 ml-1">
                                        {{ isset($shift['created_at']) ? \Carbon\Carbon::parse($shift['created_at'])->format('d/m/Y H:i') : 'N/A' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500">{{ __('messages.updated_at') }}:</span>
                                    <span class="text-sm text-gray-800 ml-1">
                                        {{ isset($shift['updated_at']) ? \Carbon\Carbon::parse($shift['updated_at'])->format('d/m/Y H:i') : 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100 mb-4">
                                <i class="fas fa-exclamation-circle text-gray-600 text-lg"></i>
                            </div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                {{ __('messages.shift_not_found') }}
                            </h3>
                        </div>
                    @endif
                </div>
                
                <!-- Botões de Ação -->
                <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                    <button type="button" wire:click="closeViewModal" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <i class="fas fa-times mr-2"></i>
                        {{ __('messages.close') }}
                    </button>
                    @if(isset($shift['id']))
                        <button type="button" wire:click="edit({{ $shift['id'] }})" class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                            <i class="fas fa-edit mr-2"></i>
                            {{ __('messages.edit') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
