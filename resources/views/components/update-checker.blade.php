<!-- Update Checker Component -->
<div class="relative">
    @if(session('update_available'))
        <div class="animate-pulse text-blue-500 hover:text-blue-600 cursor-pointer" wire:click="showUpdateInfo">
            <i class="fas fa-download text-xl"></i>
            <div class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></div>
        </div>
    @else
        <div class="text-gray-500 hover:text-gray-700">
            <!-- Botão para verificar atualizações manualmente - mostrar apenas para admins -->
            @can('settings.manage')
            <button wire:click="checkForUpdates" class="focus:outline-none" title="Verificar atualizações">
                <i class="fas fa-sync-alt text-xl {{ $checking ? 'animate-spin' : '' }}"></i>
            </button>
            @endcan
        </div>
    @endif

    <!-- Modal de atualização -->
    @if($showUpdateModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Nova atualização disponível</h3>
                        <button wire:click="hideUpdateInfo" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="mb-4">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-code-branch text-blue-500 mr-2"></i>
                            <span class="font-medium">Versão {{ $updateInfo['version'] ?? '1.0.0' }}</span>
                        </div>

                        <div class="text-sm text-gray-600 mb-4">
                            Data: {{ $updateInfo['date'] ?? now()->format('d/m/Y') }}
                        </div>

                        <h4 class="font-medium mb-2">O que há de novo:</h4>
                        <div class="bg-gray-50 p-3 rounded mb-4 max-h-60 overflow-y-auto">
                            @if(isset($updateInfo['features']))
                                <ul class="list-disc ml-5 space-y-1">
                                    @foreach($updateInfo['features'] as $feature)
                                        <li class="text-gray-700">{{ $feature }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-gray-500">Melhorias gerais e correções de bugs.</p>
                            @endif
                        </div>

                        @if(isset($updateInfo['warnings']) && count($updateInfo['warnings']) > 0)
                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 mb-4">
                                <div class="flex items-center mb-1">
                                    <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                                    <span class="font-medium">Atenção</span>
                                </div>
                                <ul class="list-disc ml-5 text-sm">
                                    @foreach($updateInfo['warnings'] as $warning)
                                        <li class="text-gray-700">{{ $warning }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button wire:click="hideUpdateInfo" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-700 transition-colors">
                            Mais tarde
                        </button>

                        <!-- Botão de atualização só deve ser visto por administradores -->
                        @can('settings.manage')
                        <button wire:click="startUpdate" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 rounded-lg text-white transition-colors flex items-center">
                            <i class="fas fa-download mr-2"></i> Atualizar agora
                        </button>
                        @endcan
                    </div>
                </div>

                <!-- Progress bar para atualização -->
                @if($updating)
                    <div class="p-6 pt-0">
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-blue-500 h-2.5 rounded-full" style="width: {{ $updateProgress }}%"></div>
                        </div>
                        <div class="text-center text-sm text-gray-500 mt-2">{{ $updateMessage }}</div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
