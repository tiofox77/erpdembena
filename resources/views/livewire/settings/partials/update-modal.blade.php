<!-- Modern Update Modal with Live CLI -->
@if($showUpdateModal)
<div class="fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center z-[100] p-4" 
     x-data="updateModal()" 
     x-init="init()">
    <div class="bg-slate-900 rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] overflow-hidden border border-slate-700" 
         @click.stop>
        
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                    <template x-if="step === 'completed'">
                        <i class="fas fa-check text-white text-2xl"></i>
                    </template>
                    <template x-if="step === 'failed'">
                        <i class="fas fa-exclamation-triangle text-white text-2xl"></i>
                    </template>
                    <template x-if="isUpdatingLocal && step !== 'completed' && step !== 'failed'">
                        <i class="fas fa-cog fa-spin text-white text-2xl"></i>
                    </template>
                    <template x-if="!isUpdatingLocal && step !== 'completed' && step !== 'failed'">
                        <i class="fas fa-rocket text-white text-2xl"></i>
                    </template>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white">Sistema de Atualização</h3>
                    <p class="text-blue-100 text-sm" x-text="isUpdatingLocal ? 'Atualização em progresso...' : 'Pronto para atualizar'"></p>
                </div>
            </div>
            
            <button 
                :disabled="isUpdatingLocal && step !== 'completed' && step !== 'failed'"
                wire:click="closeUpdateModal"
                @click="stopPolling()"
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
                        <p class="text-sm font-semibold text-white" x-text="progress + '%'"></p>
                        <p class="text-xs text-slate-400">Progresso</p>
                    </div>
                    <div class="w-16 h-16 relative">
                        <svg class="transform -rotate-90 w-16 h-16">
                            <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4" fill="none" class="text-slate-700" />
                            <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4" fill="none" 
                                    class="text-blue-500 transition-all duration-1000" 
                                    :stroke-dasharray="2 * Math.PI * 28"
                                    :stroke-dashoffset="2 * Math.PI * 28 * (1 - progress / 100)" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <template x-if="step === 'completed'">
                                <i class="fas fa-check text-green-400 text-lg"></i>
                            </template>
                            <template x-if="step === 'failed'">
                                <i class="fas fa-times text-red-400 text-lg"></i>
                            </template>
                            <template x-if="isUpdatingLocal && step !== 'completed' && step !== 'failed'">
                                <i class="fas fa-cog fa-spin text-blue-400"></i>
                            </template>
                            <template x-if="!isUpdatingLocal && step !== 'completed' && step !== 'failed'">
                                <i class="fas fa-rocket text-slate-400"></i>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Steps -->
        <div class="bg-slate-800/50 px-6 py-4 border-b border-slate-700">
            <div class="flex items-center justify-between text-xs">
                <template x-for="(s, index) in stepsList" :key="s.id">
                    <div class="contents">
                        <div class="flex flex-col items-center flex-1">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center mb-2 transition-all duration-300"
                                 :class="{
                                    'bg-blue-500 text-white animate-pulse': step === s.id,
                                    'bg-green-500 text-white': getStepIndex(s.id) < getStepIndex(step) || step === 'completed',
                                    'bg-slate-700 text-slate-400': step !== s.id && getStepIndex(s.id) >= getStepIndex(step) && step !== 'completed'
                                 }">
                                <i class="fas" :class="s.icon"></i>
                            </div>
                            <span class="text-slate-400" x-text="s.label"></span>
                        </div>
                        <template x-if="index < stepsList.length - 1">
                            <div class="flex-1 h-0.5 self-center mb-6 mx-2 transition-all duration-300"
                                 :class="{
                                    'bg-green-500': getStepIndex(s.id) < getStepIndex(step) || step === 'completed',
                                    'bg-slate-700': getStepIndex(s.id) >= getStepIndex(step) && step !== 'completed'
                                 }"></div>
                        </template>
                    </div>
                </template>
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
                        <span class="text-slate-500 text-xs" x-text="logs.length + ' linhas'"></span>
                        <i class="fas fa-terminal text-slate-500 text-xs"></i>
                    </div>
                </div>
                
                <!-- Terminal Content -->
                <div class="bg-slate-950 p-4 h-96 overflow-y-auto font-mono text-sm" x-ref="logContainer">
                    <template x-if="logs.length === 0">
                        <div class="text-slate-500 text-center py-12">
                            <i class="fas fa-terminal text-4xl mb-4 opacity-50"></i>
                            <p>Aguardando início da atualização...</p>
                            <p class="text-xs mt-2">Os logs aparecerão aqui em tempo real</p>
                        </div>
                    </template>
                    
                    <template x-for="(log, index) in logs" :key="index">
                        <div class="mb-1 hover:bg-slate-900/50 px-2 py-1 rounded transition-colors duration-150">
                            <span class="text-slate-500" x-text="'[' + log.timestamp + ']'"></span>
                            <span :class="{
                                'text-green-400': log.type === 'success',
                                'text-red-400': log.type === 'error',
                                'text-yellow-400': log.type === 'warning',
                                'text-blue-300': log.type !== 'success' && log.type !== 'error' && log.type !== 'warning'
                            }" x-text="log.message"></span>
                        </div>
                    </template>
                    
                    <!-- Cursor -->
                    <template x-if="isUpdatingLocal && step !== 'completed' && step !== 'failed'">
                        <div class="inline-flex items-center">
                            <span class="text-green-400">▊</span>
                            <span class="ml-2 text-slate-500 text-xs animate-pulse">Processando...</span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Status Bar -->
        <div class="bg-slate-800 px-6 py-4 border-t border-slate-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <template x-if="step === 'completed'">
                        <div class="flex items-center space-x-3">
                            <div class="w-3 h-3 rounded-full bg-green-500 animate-pulse"></div>
                            <span class="text-green-400 font-medium" x-text="status"></span>
                        </div>
                    </template>
                    <template x-if="step === 'failed'">
                        <div class="flex items-center space-x-3">
                            <div class="w-3 h-3 rounded-full bg-red-500 animate-pulse"></div>
                            <span class="text-red-400 font-medium" x-text="status"></span>
                        </div>
                    </template>
                    <template x-if="isUpdatingLocal && step !== 'completed' && step !== 'failed'">
                        <div class="flex items-center space-x-3">
                            <div class="w-3 h-3 rounded-full bg-blue-500 animate-pulse"></div>
                            <span class="text-blue-400 font-medium" x-text="status"></span>
                        </div>
                    </template>
                    <template x-if="!isUpdatingLocal && step !== 'completed' && step !== 'failed'">
                        <div class="flex items-center space-x-3">
                            <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                            <span class="text-slate-400 font-medium" x-text="status"></span>
                        </div>
                    </template>
                </div>
                
                <div class="flex items-center space-x-3">
                    <!-- Ready to Start -->
                    <template x-if="step === 'ready' && !isUpdatingLocal">
                        <div class="flex items-center space-x-3">
                            <button 
                                wire:click="closeUpdateModal"
                                @click="stopPolling()"
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
                        </div>
                    </template>
                    
                    <!-- Completed -->
                    <template x-if="step === 'completed'">
                        <button 
                            wire:click="closeUpdateModal"
                            @click="stopPolling()"
                            class="px-6 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white rounded-lg transition-all duration-200 font-medium shadow-lg">
                            <i class="fas fa-check mr-2"></i>
                            Concluído
                        </button>
                    </template>
                    
                    <!-- Failed -->
                    <template x-if="step === 'failed'">
                        <button 
                            wire:click="closeUpdateModal"
                            @click="stopPolling()"
                            class="px-6 py-2.5 bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 text-white rounded-lg transition-all duration-200 font-medium shadow-lg">
                            <i class="fas fa-times mr-2"></i>
                            Fechar
                        </button>
                    </template>
                    
                    <!-- Updating -->
                    <template x-if="isUpdatingLocal && step !== 'completed' && step !== 'failed' && step !== 'ready'">
                        <div class="px-6 py-2.5 bg-slate-700 text-slate-400 rounded-lg cursor-not-allowed opacity-50 font-medium">
                            <i class="fas fa-cog fa-spin mr-2"></i>
                            Atualização em Progresso...
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateModal() {
    return {
        logs: @js($update_logs ?? []),
        progress: @js($update_progress ?? 0),
        status: @js($update_status ?? 'Aguardando confirmação...'),
        step: @js($update_step ?? 'ready'),
        isUpdatingLocal: @js($isUpdating ?? false),
        pollInterval: null,
        stepsList: [
            {id: 'backup', icon: 'fa-box', label: 'Backup'},
            {id: 'maintenance', icon: 'fa-tools', label: 'Manutenção'},
            {id: 'download', icon: 'fa-download', label: 'Download'},
            {id: 'extract', icon: 'fa-file-archive', label: 'Extração'},
            {id: 'migrate', icon: 'fa-database', label: 'Migração'},
            {id: 'finalize', icon: 'fa-check-circle', label: 'Finalizar'}
        ],
        
        init() {
            this.startPolling();
        },
        
        getStepIndex(stepId) {
            const ids = ['ready', 'starting', 'backup', 'maintenance', 'download', 'extract', 'migrate', 'finalize', 'completed', 'failed'];
            return ids.indexOf(stepId);
        },
        
        scrollToBottom() {
            this.$nextTick(() => {
                const el = this.$refs.logContainer;
                if (el) el.scrollTop = el.scrollHeight;
            });
        },
        
        startPolling() {
            this.pollInterval = setInterval(() => {
                fetch('/api/update-state')
                    .then(r => r.json())
                    .then(data => {
                        if (data && data.logs !== undefined) {
                            this.logs = data.logs || [];
                            this.progress = data.progress || 0;
                            this.status = data.status || '';
                            this.step = data.step || 'ready';
                            this.isUpdatingLocal = data.is_updating || false;
                            this.scrollToBottom();
                        }
                    })
                    .catch(() => {});
            }, 500);
        },
        
        stopPolling() {
            if (this.pollInterval) {
                clearInterval(this.pollInterval);
                this.pollInterval = null;
            }
        }
    }
}
</script>

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
