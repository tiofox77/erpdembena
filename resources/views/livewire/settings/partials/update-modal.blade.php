<!-- Modern Update Modal with Live CLI -->
@if($showUpdateModal)
<div class="fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center z-[100] p-4" 
     x-data="{ scrollToBottom() { let el = $refs.logContainer; if(el) el.scrollTop = el.scrollHeight; } }"
     x-init="$watch('$wire.update_logs', () => { setTimeout(() => scrollToBottom(), 50) })"
     wire:poll.1000ms>
    <div class="bg-slate-900 rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] overflow-hidden border border-slate-700" 
         @click.stop>
        
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                    @if($update_step === 'completed')
                        <i class="fas fa-check text-white text-2xl"></i>
                    @elseif($update_step === 'failed')
                        <i class="fas fa-exclamation-triangle text-white text-2xl"></i>
                    @elseif($isUpdating)
                        <i class="fas fa-cog fa-spin text-white text-2xl"></i>
                    @else
                        <i class="fas fa-rocket text-white text-2xl"></i>
                    @endif
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white">Sistema de Atualização</h3>
                    <p class="text-blue-100 text-sm">{{ $isUpdating ? 'Atualização em progresso...' : 'Pronto para atualizar' }}</p>
                </div>
            </div>
            
            <button 
                @if($isUpdating) disabled @endif
                wire:click="closeUpdateModal"
                class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg p-2 transition-all duration-200 disabled:opacity-30 disabled:cursor-not-allowed">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Version Info -->
        <div class="bg-slate-800 px-6 py-4 border-b border-slate-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-6">
                    <div>
                        <span class="text-slate-400 text-sm">Versão Atual</span>
                        <p class="text-white font-mono font-semibold">v{{ $current_version }}</p>
                    </div>
                    <div>
                        <i class="fas fa-arrow-right text-slate-600"></i>
                    </div>
                    <div>
                        <span class="text-slate-400 text-sm">Nova Versão</span>
                        <p class="text-green-400 font-mono font-semibold">v{{ $latest_version }}</p>
                    </div>
                </div>
                
                <!-- Progress Indicator -->
                <div class="flex items-center space-x-3">
                    <div class="text-right">
                        <p class="text-sm font-semibold text-white">{{ $update_progress }}%</p>
                        <p class="text-xs text-slate-400">Progresso</p>
                    </div>
                    <div class="w-16 h-16 relative">
                        <svg class="transform -rotate-90 w-16 h-16">
                            <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4" fill="none" class="text-slate-700" />
                            <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4" fill="none" 
                                    class="text-blue-500 transition-all duration-1000" 
                                    stroke-dasharray="{{ 2 * pi() * 28 }}" 
                                    stroke-dashoffset="{{ 2 * pi() * 28 * (1 - $update_progress / 100) }}" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            @if($update_step === 'completed')
                                <i class="fas fa-check text-green-400 text-lg"></i>
                            @elseif($update_step === 'failed')
                                <i class="fas fa-times text-red-400 text-lg"></i>
                            @elseif($isUpdating)
                                <i class="fas fa-cog fa-spin text-blue-400"></i>
                            @else
                                <i class="fas fa-rocket text-slate-400"></i>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Steps -->
        <div class="bg-slate-800/50 px-6 py-4 border-b border-slate-700">
            <div class="flex items-center justify-between text-xs">
                @php
                $steps = [
                    ['id' => 'backup', 'icon' => 'fa-box', 'label' => 'Backup'],
                    ['id' => 'maintenance', 'icon' => 'fa-tools', 'label' => 'Manutenção'],
                    ['id' => 'download', 'icon' => 'fa-download', 'label' => 'Download'],
                    ['id' => 'extract', 'icon' => 'fa-file-archive', 'label' => 'Extração'],
                    ['id' => 'migrate', 'icon' => 'fa-database', 'label' => 'Migração'],
                    ['id' => 'finalize', 'icon' => 'fa-check-circle', 'label' => 'Finalizar']
                ];
                @endphp
                
                @foreach($steps as $index => $step)
                <div class="flex flex-col items-center flex-1">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center mb-2 transition-all duration-300
                        @if($update_step === $step['id']) bg-blue-500 text-white animate-pulse
                        @elseif(array_search($step['id'], array_column($steps, 'id')) < array_search($update_step, array_column($steps, 'id')) || $update_step === 'completed') bg-green-500 text-white
                        @else bg-slate-700 text-slate-400
                        @endif">
                        <i class="fas {{ $step['icon'] }}"></i>
                    </div>
                    <span class="text-slate-400">{{ $step['label'] }}</span>
                </div>
                @if(!$loop->last)
                <div class="flex-1 h-0.5 bg-slate-700 self-center mb-6 mx-2
                    @if(array_search($step['id'], array_column($steps, 'id')) < array_search($update_step, array_column($steps, 'id')) || $update_step === 'completed') bg-green-500 @endif">
                </div>
                @endif
                @endforeach
            </div>
        </div>

        <!-- Terminal / CLI Logs -->
        <div class="bg-slate-950 p-6 overflow-hidden">
            <div class="bg-slate-900 rounded-lg border border-slate-700 shadow-2xl overflow-hidden">
                <!-- Terminal Header -->
                <div class="bg-slate-800 px-4 py-2 flex items-center justify-between border-b border-slate-700">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                        <div class="w-3 h-3 rounded-full bg-green-500"></div>
                        <span class="ml-3 text-slate-400 text-xs font-mono">update-process.log</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-slate-500 text-xs">{{ count($update_logs) }} linhas</span>
                        <i class="fas fa-terminal text-slate-500 text-xs"></i>
                    </div>
                </div>
                
                <!-- Terminal Content -->
                <div class="bg-slate-950 p-4 h-96 overflow-y-auto font-mono text-sm" x-ref="logContainer">
                    @if(empty($update_logs))
                        <div class="text-slate-500 text-center py-12">
                            <i class="fas fa-terminal text-4xl mb-4 opacity-50"></i>
                            <p>Aguardando início da atualização...</p>
                            <p class="text-xs mt-2">Os logs aparecerão aqui em tempo real</p>
                        </div>
                    @else
                        @foreach($update_logs as $log)
                        <div class="mb-1 hover:bg-slate-900/50 px-2 py-1 rounded transition-colors duration-150"
                             wire:key="log-{{ $loop->index }}">
                            <span class="text-slate-500">[{{ $log['timestamp'] }}]</span>
                            <span class="
                                @if($log['type'] === 'success') text-green-400
                                @elseif($log['type'] === 'error') text-red-400
                                @elseif($log['type'] === 'warning') text-yellow-400
                                @else text-blue-300
                                @endif">
                                {{ $log['message'] }}
                            </span>
                        </div>
                        @endforeach
                        
                        <!-- Cursor -->
                        @if($isUpdating && $update_step !== 'completed' && $update_step !== 'failed')
                        <div class="inline-flex items-center">
                            <span class="text-green-400">▊</span>
                            <span class="ml-2 text-slate-500 text-xs animate-pulse">Processando...</span>
                        </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Status Bar -->
        <div class="bg-slate-800 px-6 py-4 border-t border-slate-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    @if($update_step === 'completed')
                        <div class="w-3 h-3 rounded-full bg-green-500 animate-pulse"></div>
                        <span class="text-green-400 font-medium">{{ $update_status }}</span>
                    @elseif($update_step === 'failed')
                        <div class="w-3 h-3 rounded-full bg-red-500 animate-pulse"></div>
                        <span class="text-red-400 font-medium">{{ $update_status }}</span>
                    @elseif($isUpdating)
                        <div class="w-3 h-3 rounded-full bg-blue-500 animate-pulse"></div>
                        <span class="text-blue-400 font-medium">{{ $update_status }}</span>
                    @else
                        <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                        <span class="text-slate-400 font-medium">{{ $update_status }}</span>
                    @endif
                </div>
                
                <div class="flex items-center space-x-3">
                    @if($update_step === 'ready' && !$isUpdating)
                        <!-- Ready to Start -->
                        <button 
                            wire:click="closeUpdateModal"
                            class="px-6 py-2.5 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition-all duration-200 font-medium">
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </button>
                        <button 
                            wire:click="startUpdate"
                            class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white rounded-lg transition-all duration-200 font-medium shadow-lg transform hover:scale-105">
                            <i class="fas fa-rocket mr-2"></i>
                            Iniciar Atualização
                        </button>
                    @elseif($update_step === 'completed')
                        <!-- Completed -->
                        <button 
                            wire:click="closeUpdateModal"
                            class="px-6 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white rounded-lg transition-all duration-200 font-medium shadow-lg">
                            <i class="fas fa-check mr-2"></i>
                            Concluído
                        </button>
                    @elseif($update_step === 'failed')
                        <!-- Failed -->
                        <button 
                            wire:click="closeUpdateModal"
                            class="px-6 py-2.5 bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 text-white rounded-lg transition-all duration-200 font-medium shadow-lg">
                            <i class="fas fa-times mr-2"></i>
                            Fechar
                        </button>
                    @else
                        <!-- Updating -->
                        <div class="px-6 py-2.5 bg-slate-700 text-slate-400 rounded-lg cursor-not-allowed opacity-50 font-medium">
                            <i class="fas fa-cog fa-spin mr-2"></i>
                            Atualização em Progresso...
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom scrollbar for terminal */
    div[x-ref="logContainer"]::-webkit-scrollbar {
        width: 8px;
    }
    div[x-ref="logContainer"]::-webkit-scrollbar-track {
        background: #0f172a;
    }
    div[x-ref="logContainer"]::-webkit-scrollbar-thumb {
        background: #334155;
        border-radius: 4px;
    }
    div[x-ref="logContainer"]::-webkit-scrollbar-thumb:hover {
        background: #475569;
    }
</style>
@endif
