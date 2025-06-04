<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-database mr-2 text-blue-600"></i> Backup do Banco de Dados
                    </h2>

                    <div class="mb-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Painel de Backup Manual -->
                            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                                <div class="flex items-center bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3 border-b border-gray-200">
                                    <i class="fas fa-cloud-download-alt text-white mr-2"></i>
                                    <h3 class="text-base font-medium text-white">Backup Manual</h3>
                                </div>
                                <div class="p-4">
                                    <p class="text-sm text-gray-600 mb-4">
                                        Crie um backup completo do banco de dados manualmente. O backup será armazenado localmente e poderá ser baixado ou restaurado quando necessário.
                                    </p>
                                    <div class="flex justify-center mb-4">
                                        <button wire:click="createDatabaseBackup" 
                                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200"
                                                wire:loading.attr="disabled"
                                                wire:target="createDatabaseBackup">
                                            <span wire:loading.remove wire:target="createDatabaseBackup">
                                                <i class="fas fa-database mr-2"></i> Criar Backup
                                            </span>
                                            <span wire:loading wire:target="createDatabaseBackup">
                                                <i class="fas fa-spinner fa-spin mr-2"></i> Criando Backup...
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Painel de Backup Automático -->
                            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                                <div class="flex items-center bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3 border-b border-gray-200">
                                    <i class="fas fa-clock text-white mr-2"></i>
                                    <h3 class="text-base font-medium text-white">Backup Automático</h3>
                                </div>
                                <div class="p-4">
                                    <form wire:submit.prevent="saveBackupSettings">
                                        <div class="space-y-4">
                                            <div>
                                                <label class="flex items-center cursor-pointer">
                                                    <div class="relative">
                                                        <input type="checkbox" wire:model.live="backupAutomation" id="backupAutomation" class="sr-only">
                                                        <div class="w-11 h-6 bg-gray-200 rounded-full shadow-inner transition-all duration-300 ease-in-out"></div>
                                                        <div class="dot absolute w-6 h-6 bg-white rounded-full shadow left-0 top-0 transition-transform duration-300 ease-in-out transform" 
                                                            :class="{'translate-x-5 bg-green-500': $wire.backupAutomation, 'bg-white': !$wire.backupAutomation}"></div>
                                                    </div>
                                                    <div class="ml-3">
                                                        <span class="text-sm font-medium text-gray-700">Ativar Backup Automático</span>
                                                    </div>
                                                </label>
                                            </div>

                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label for="backupFrequency" class="block text-sm font-medium text-gray-700">Frequência do Backup</label>
                                                    <select wire:model.live="backupFrequency" id="backupFrequency" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm" 
                                                            :disabled="!$wire.backupAutomation">
                                                        <option value="daily">Diariamente</option>
                                                        <option value="weekly">Semanalmente</option>
                                                        <option value="monthly">Mensalmente</option>
                                                    </select>
                                                    @error('backupFrequency') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                                </div>

                                                <div>
                                                    <label for="backupTime" class="block text-sm font-medium text-gray-700">Horário do Backup</label>
                                                    <input type="time" wire:model.live="backupTime" id="backupTime" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm" 
                                                           :disabled="!$wire.backupAutomation">
                                                    @error('backupTime') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                                </div>
                                            </div>

                                            <div>
                                                <label for="backupRetention" class="block text-sm font-medium text-gray-700">Dias de Retenção</label>
                                                <input type="number" wire:model.live="backupRetention" id="backupRetention" min="1" max="90" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm" 
                                                       :disabled="!$wire.backupAutomation">
                                                @error('backupRetention') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                            </div>

                                            <div class="pt-2">
                                                <button type="submit" 
                                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200"
                                                        :disabled="!$wire.backupAutomation">
                                                    <i class="fas fa-save mr-2"></i> Salvar Configurações
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Importar Backup -->
                    <div class="mb-6">
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                            <div class="flex items-center bg-gradient-to-r from-green-600 to-green-700 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-file-import text-white mr-2"></i>
                                <h3 class="text-base font-medium text-white">Importar Backup</h3>
                            </div>
                            <div class="p-4">
                                <form wire:submit.prevent="importBackup">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-grow">
                                            <label for="importBackupFile" class="block text-sm font-medium text-gray-700 mb-1">Selecionar Arquivo de Backup</label>
                                            <input type="file" wire:model.live="importBackupFile" id="importBackupFile" 
                                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                            @error('importBackupFile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="flex-shrink-0 pt-6">
                                            <button type="submit" 
                                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200"
                                                    wire:loading.attr="disabled"
                                                    wire:target="importBackup,importBackupFile">
                                                <span wire:loading.remove wire:target="importBackup">
                                                    <i class="fas fa-upload mr-2"></i> Importar e Restaurar
                                                </span>
                                                <span wire:loading wire:target="importBackup">
                                                    <i class="fas fa-spinner fa-spin mr-2"></i> Importando...
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Backups -->
                    <div x-data="{ showBackups: true }">
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                            <div class="flex items-center justify-between bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3 border-b border-gray-200">
                                <div class="flex items-center">
                                    <i class="fas fa-history text-white mr-2"></i>
                                    <h3 class="text-base font-medium text-white">Histórico de Backups</h3>
                                </div>
                                <button @click="showBackups = !showBackups" class="text-white hover:text-blue-100 focus:outline-none transition-all duration-200">
                                    <i class="fas" :class="showBackups ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                </button>
                            </div>
                            <div x-show="showBackups" x-transition>
                                <div class="overflow-x-auto">
                                    <div wire:loading wire:target="loadBackupFiles" class="w-full p-6 text-center">
                                        <i class="fas fa-spinner fa-spin text-blue-500 text-xl"></i>
                                        <p class="mt-2 text-gray-600">Carregando backups...</p>
                                    </div>
                                    
                                    <div wire:loading.remove wire:target="loadBackupFiles">
                                        @if(count($backupFiles) > 0)
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Arquivo</th>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tamanho</th>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    @foreach($backupFiles as $index => $file)
                                                        <tr class="hover:bg-gray-50">
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                                <div class="flex items-center">
                                                                    <i class="fas fa-file-alt text-blue-500 mr-2"></i>
                                                                    {{ $file['name'] }}
                                                                </div>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                {{ $file['size'] }}
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                {{ $file['date'] }}
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                                <div class="flex justify-end space-x-2">
                                                                    <button wire:click="downloadBackup({{ $index }})" class="text-blue-600 hover:text-blue-900 hover:scale-110 transition-all duration-200" title="Download">
                                                                        <i class="fas fa-download"></i>
                                                                    </button>
                                                                    <button wire:click="confirmRestoreBackup({{ $index }})" class="text-green-600 hover:text-green-900 hover:scale-110 transition-all duration-200" title="Restaurar">
                                                                        <i class="fas fa-redo-alt"></i>
                                                                    </button>
                                                                    <button wire:click="confirmDeleteBackup({{ $index }})" class="text-red-600 hover:text-red-900 hover:scale-110 transition-all duration-200" title="Excluir">
                                                                        <i class="fas fa-trash-alt"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @else
                                            <div class="p-6 text-center">
                                                <i class="fas fa-database text-gray-400 text-5xl mb-4"></i>
                                                <p class="text-gray-500">Nenhum backup encontrado</p>
                                                <p class="text-gray-400 text-sm mt-2">Clique em "Criar Backup" para criar seu primeiro backup</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmação -->
    @if($showConfirmModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="relative bg-white rounded-lg max-w-md w-full mx-auto overflow-hidden shadow-xl transform transition-all">
            <div class="bg-gradient-to-r from-red-600 to-red-700 px-4 py-3 flex justify-between items-center">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Confirmação Necessária
                </h3>
                <button wire:click="closeConfirmModal" class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6">
                <p class="text-gray-700 mb-4">{{ $confirmMessage }}</p>
                <div class="flex justify-end space-x-3">
                    <button wire:click="closeConfirmModal" class="inline-flex justify-center items-center px-4 py-2 bg-gray-200 border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </button>
                    <button wire:click="processConfirmedAction" class="inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200" wire:loading.attr="disabled">
                        <span wire:loading.remove>
                            <i class="fas fa-check mr-2"></i>
                            Confirmar
                        </span>
                        <span wire:loading>
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Processando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
