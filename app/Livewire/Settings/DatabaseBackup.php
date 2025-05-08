<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Setting;

class DatabaseBackup extends Component
{
    use WithFileUploads;
    
    public $backupFiles = [];
    public $isCreatingBackup = false;
    public $isRestoringBackup = false;
    public $isLoadingBackups = false;
    public $selectedBackupFile = null;
    public $importBackupFile = null;
    
    // Configurações de backup
    public $backupFrequency = 'daily'; // daily, weekly, monthly
    public $backupTime = '00:00';
    public $backupRetention = 7; // dias
    public $backupAutomation = false;
    
    public $confirmAction = '';
    public $confirmMessage = '';
    public $showConfirmModal = false;
    
    protected function rules()
    {
        return [
            'backupFrequency' => 'required|in:daily,weekly,monthly',
            'backupTime' => 'required|date_format:H:i',
            'backupRetention' => 'required|integer|min:1|max:90',
            'backupAutomation' => 'boolean',
            'importBackupFile' => $this->importBackupFile instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile
                ? 'file|max:102400'
                : 'nullable',
        ];
    }
    
    public function mount()
    {
        $this->loadSettings();
        $this->loadBackupFiles();
    }
    
    protected function loadSettings()
    {
        $this->backupFrequency = Setting::get('backup_frequency', 'daily');
        $this->backupTime = Setting::get('backup_time', '00:00');
        $this->backupRetention = Setting::get('backup_retention', 7);
        $this->backupAutomation = (bool)Setting::get('backup_automation', false);
    }
    
    /**
     * Carrega a lista de arquivos de backup disponíveis
     */
    public function loadBackupFiles()
    {
        $this->isLoadingBackups = true;
        
        try {
            $backupDir = storage_path('app/systembackup');
            
            // Criar pasta de backups se não existir
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            $files = array_filter(scandir($backupDir) ?: [], function($file) {
                return !in_array($file, ['.', '..']) && pathinfo($file, PATHINFO_EXTENSION) === 'sql';
            });
            
            $backupFiles = [];
            
            foreach ($files as $file) {
                $fullPath = $backupDir . '/' . $file;
                
                $backupFiles[] = [
                    'name' => $file,
                    'size' => $this->formatBytes(filesize($fullPath)),
                    'date' => date('Y-m-d H:i:s', filemtime($fullPath)),
                    'path' => $fullPath
                ];
            }
            
            // Ordenar por data (mais recente primeiro)
            usort($backupFiles, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });
            
            $this->backupFiles = $backupFiles;
        } catch (\Exception $e) {
            Log::error('Erro ao carregar arquivos de backup: ' . $e->getMessage());
        }
        
        $this->isLoadingBackups = false;
    }
    
    /**
     * Cria um backup manual do banco de dados
     */
    public function createDatabaseBackup()
    {
        $this->isCreatingBackup = true;
        
        try {
            $dbName = config('database.connections.mysql.database');
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "backup_{$dbName}_{$timestamp}.sql";
            $backupPath = storage_path('app/systembackup/' . $filename);
            
            // Criar pasta de backups se não existir
            $backupDir = storage_path('app/systembackup');
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            // Executar backup através do mysqldump (mais confiável)
            if ($this->backupDatabaseMysqldump($backupPath)) {
                $this->dispatch('notify', 
                    type: 'success', 
                    title: __('messages.success'),
                    message: __('messages.database_backup_created')
                );
                
                // Atualizar lista de backups
                $this->loadBackupFiles();
            } else {
                // Tentar backup alternativo usando PHP
                $this->backupDatabaseAlternative($backupPath);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao criar backup: ' . $e->getMessage());
            $this->dispatch('notify', 
                type: 'error', 
                title: __('messages.error'),
                message: __('messages.database_backup_error') . ': ' . $e->getMessage()
            );
        }
        
        $this->isCreatingBackup = false;
    }
    
    /**
     * Executa o backup do banco de dados usando mysqldump
     */
    private function backupDatabaseMysqldump($outputFile)
    {
        $dbHost = config('database.connections.mysql.host');
        $dbPort = config('database.connections.mysql.port');
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        
        // Escapar senha para uso em linha de comando
        $dbPass = addslashes($dbPass);
        
        // Opções para o mysqldump
        $options = [
            '--single-transaction',
            '--skip-lock-tables',
            '--quick',
        ];
        
        // Criar comando mysqldump
        $command = sprintf(
            'mysqldump %s --host=%s --port=%s --user=%s --password="%s" %s > %s',
            implode(' ', $options),
            escapeshellarg($dbHost),
            escapeshellarg($dbPort),
            escapeshellarg($dbUser),
            $dbPass,
            escapeshellarg($dbName),
            escapeshellarg($outputFile)
        );
        
        // Executar comando
        $output = null;
        $returnVar = null;
        exec($command, $output, $returnVar);
        
        return $returnVar === 0;
    }
    
    /**
     * Backup alternativo usando PHP para casos onde mysqldump não está disponível
     */
    private function backupDatabaseAlternative($outputFile)
    {
        try {
            // Obter todas as tabelas do banco de dados
            $tables = DB::select('SHOW TABLES');
            $dbName = config('database.connections.mysql.database');
            $tableList = [];
            
            foreach ($tables as $table) {
                $tableName = "Tables_in_{$dbName}";
                $tableList[] = $table->$tableName;
            }
            
            $sql = "-- Database Backup criado por ERP DEMBENA em " . date('Y-m-d H:i:s') . "\n";
            $sql .= "-- ------------------------------------------------------\n";
            $sql .= "-- Host: " . config('database.connections.mysql.host') . "\n";
            $sql .= "-- Database: " . $dbName . "\n";
            $sql .= "-- ------------------------------------------------------\n\n";
            
            // Processar cada tabela
            foreach ($tableList as $table) {
                // Obter estrutura
                $createTableSql = DB::select("SHOW CREATE TABLE `{$table}`");
                $createStatement = $createTableSql[0]->{'Create Table'};
                
                $sql .= "\n\n-- Estrutura da tabela `{$table}`\n\n";
                $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $sql .= "{$createStatement};\n\n";
                
                // Obter dados
                $rows = DB::table($table)->get();
                
                if ($rows->count() > 0) {
                    $sql .= "-- Dados da tabela `{$table}`\n";
                    $sql .= "INSERT INTO `{$table}` VALUES \n";
                    
                    $rowCount = 0;
                    $totalRows = $rows->count();
                    
                    foreach ($rows as $row) {
                        $rowData = (array) $row;
                        $rowValues = [];
                        
                        foreach ($rowData as $value) {
                            if (is_null($value)) {
                                $rowValues[] = 'NULL';
                            } elseif (is_numeric($value)) {
                                $rowValues[] = $value;
                            } else {
                                $rowValues[] = "'" . addslashes($value) . "'";
                            }
                        }
                        
                        $rowCount++;
                        $sql .= '(' . implode(',', $rowValues) . ')' . ($rowCount < $totalRows ? ',' : ';') . "\n";
                        
                        // Salvar a cada 1000 linhas para evitar consumo excessivo de memória
                        if ($rowCount % 1000 === 0) {
                            file_put_contents($outputFile, $sql, FILE_APPEND);
                            $sql = '';
                        }
                    }
                    
                    $sql .= "\n";
                }
                
                // Salvar conteúdo da tabela atual
                file_put_contents($outputFile, $sql, FILE_APPEND);
                $sql = '';
            }
            
            $this->dispatch('notify', 
                type: 'success', 
                title: __('messages.success'),
                message: __('messages.database_backup_created')
            );
            
            // Atualizar lista de backups
            $this->loadBackupFiles();
            
            return true;
        } catch (\Exception $e) {
            Log::error('Erro no backup alternativo: ' . $e->getMessage());
            $this->dispatch('notify', 
                type: 'error', 
                title: __('messages.error'),
                message: __('messages.database_backup_error') . ': ' . $e->getMessage()
            );
            
            return false;
        }
    }
    
    /**
     * Fazer download de um arquivo de backup
     */
    public function downloadBackup($index)
    {
        if (isset($this->backupFiles[$index])) {
            $file = $this->backupFiles[$index];
            $path = $file['path'];
            
            return response()->download($path);
        }
        
        $this->dispatch('notify', 
            type: 'error', 
            title: __('messages.error'),
            message: __('messages.backup_file_not_found')
        );
    }
    
    /**
     * Confirmar restauração de backup
     */
    public function confirmRestoreBackup($index)
    {
        if (isset($this->backupFiles[$index])) {
            $this->selectedBackupFile = $this->backupFiles[$index];
            
            $this->confirmAction = 'restoreBackup';
            $this->confirmMessage = __('messages.confirm_restore_backup', ['file' => $this->selectedBackupFile['name']]);
            $this->showConfirmModal = true;
        }
    }
    
    /**
     * Restaurar banco de dados a partir de um arquivo de backup
     */
    public function restoreBackup()
    {
        if (!$this->selectedBackupFile) {
            $this->dispatch('notify', 
                type: 'error', 
                title: __('messages.error'),
                message: __('messages.backup_file_not_selected')
            );
            return;
        }
        
        $this->isRestoringBackup = true;
        
        try {
            $filePath = $this->selectedBackupFile['path'];
            
            // Verificar se o arquivo existe
            if (!file_exists($filePath)) {
                throw new \Exception(__('messages.backup_file_not_found'));
            }
            
            // Conexão com banco de dados
            $dbHost = config('database.connections.mysql.host');
            $dbPort = config('database.connections.mysql.port');
            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');
            
            // Escapar senha para uso em linha de comando
            $dbPass = addslashes($dbPass);
            
            // Criar comando mysql
            $command = sprintf(
                'mysql --host=%s --port=%s --user=%s --password="%s" %s < %s',
                escapeshellarg($dbHost),
                escapeshellarg($dbPort),
                escapeshellarg($dbUser),
                $dbPass,
                escapeshellarg($dbName),
                escapeshellarg($filePath)
            );
            
            // Executar comando
            $output = null;
            $returnVar = null;
            exec($command, $output, $returnVar);
            
            if ($returnVar !== 0) {
                throw new \Exception("Erro ao restaurar backup: código {$returnVar}");
            }
            
            // Limpar caches
            $this->clearCaches();
            
            $this->dispatch('notify', 
                type: 'success', 
                title: __('messages.success'),
                message: __('messages.database_restore_success')
            );
        } catch (\Exception $e) {
            Log::error('Erro ao restaurar backup: ' . $e->getMessage());
            $this->dispatch('notify', 
                type: 'error', 
                title: __('messages.error'),
                message: __('messages.database_restore_error') . ': ' . $e->getMessage()
            );
        }
        
        $this->isRestoringBackup = false;
        $this->selectedBackupFile = null;
        $this->showConfirmModal = false;
    }
    
    /**
     * Confirmar exclusão de backup
     */
    public function confirmDeleteBackup($index)
    {
        if (isset($this->backupFiles[$index])) {
            $this->selectedBackupFile = $this->backupFiles[$index];
            
            $this->confirmAction = 'deleteBackup';
            $this->confirmMessage = __('messages.confirm_delete_backup', ['file' => $this->selectedBackupFile['name']]);
            $this->showConfirmModal = true;
        }
    }
    
    /**
     * Excluir um arquivo de backup
     */
    public function deleteBackup()
    {
        if (!$this->selectedBackupFile) {
            $this->dispatch('notify', 
                type: 'error', 
                title: __('messages.error'),
                message: __('messages.backup_file_not_selected')
            );
            return;
        }
        
        try {
            $filePath = $this->selectedBackupFile['path'];
            
            if (file_exists($filePath) && unlink($filePath)) {
                $this->dispatch('notify', 
                    type: 'success', 
                    title: __('messages.success'),
                    message: __('messages.backup_deleted_successfully')
                );
                
                // Atualizar lista de backups
                $this->loadBackupFiles();
            } else {
                throw new \Exception(__('messages.backup_file_not_found'));
            }
        } catch (\Exception $e) {
            Log::error('Erro ao excluir backup: ' . $e->getMessage());
            $this->dispatch('notify', 
                type: 'error', 
                title: __('messages.error'),
                message: __('messages.backup_delete_error') . ': ' . $e->getMessage()
            );
        }
        
        $this->selectedBackupFile = null;
        $this->showConfirmModal = false;
    }
    
    /**
     * Importar arquivo de backup
     */
    public function importBackup()
    {
        $this->validate([
            'importBackupFile' => 'required|file|max:102400',
        ]);
        
        $this->isRestoringBackup = true;
        
        try {
            $tempPath = $this->importBackupFile->getRealPath();
            $originalName = $this->importBackupFile->getClientOriginalName();
            
            // Verificar se é um arquivo SQL
            if (pathinfo($originalName, PATHINFO_EXTENSION) !== 'sql') {
                throw new \Exception(__('messages.invalid_backup_file'));
            }
            
            // Salvar arquivo no diretório de backups
            $backupDir = storage_path('app/systembackup');
            
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "imported_{$timestamp}_" . $originalName;
            $targetPath = $backupDir . '/' . $filename;
            
            copy($tempPath, $targetPath);
            
            // Restaurar o banco de dados a partir do arquivo importado
            $dbHost = config('database.connections.mysql.host');
            $dbPort = config('database.connections.mysql.port');
            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');
            
            // Escapar senha para uso em linha de comando
            $dbPass = addslashes($dbPass);
            
            // Criar comando mysql
            $command = sprintf(
                'mysql --host=%s --port=%s --user=%s --password="%s" %s < %s',
                escapeshellarg($dbHost),
                escapeshellarg($dbPort),
                escapeshellarg($dbUser),
                $dbPass,
                escapeshellarg($dbName),
                escapeshellarg($targetPath)
            );
            
            // Executar comando
            $output = null;
            $returnVar = null;
            exec($command, $output, $returnVar);
            
            if ($returnVar !== 0) {
                throw new \Exception("Erro ao restaurar backup: código {$returnVar}");
            }
            
            // Limpar caches
            $this->clearCaches();
            
            $this->dispatch('notify', 
                type: 'success', 
                title: __('messages.success'),
                message: __('messages.database_restore_success')
            );
            
            // Atualizar lista de backups
            $this->loadBackupFiles();
        } catch (\Exception $e) {
            Log::error('Erro ao importar backup: ' . $e->getMessage());
            $this->dispatch('notify', 
                type: 'error', 
                title: __('messages.error'),
                message: __('messages.database_restore_error') . ': ' . $e->getMessage()
            );
        }
        
        $this->isRestoringBackup = false;
        $this->importBackupFile = null;
        $this->resetValidation('importBackupFile');
    }
    
    /**
     * Salvar configurações de backup automático
     */
    public function saveBackupSettings()
    {
        $this->validate([
            'backupFrequency' => 'required|in:daily,weekly,monthly',
            'backupTime' => 'required|date_format:H:i',
            'backupRetention' => 'required|integer|min:1|max:90',
            'backupAutomation' => 'boolean',
        ]);
        
        try {
            Setting::set('backup_frequency', $this->backupFrequency);
            Setting::set('backup_time', $this->backupTime);
            Setting::set('backup_retention', $this->backupRetention);
            Setting::set('backup_automation', $this->backupAutomation);
            
            // Agendar backup através do comando personalizado
            if ($this->backupAutomation) {
                // Criar ou atualizar tarefa agendada no sistema
                $this->scheduleBackupTask();
            } else {
                // Remover tarefa agendada se a automação foi desativada
                $this->removeBackupTask();
            }
            
            $this->dispatch('notify', 
                type: 'success', 
                title: __('messages.success'),
                message: __('messages.backup_settings_saved')
            );
        } catch (\Exception $e) {
            Log::error('Erro ao salvar configurações de backup: ' . $e->getMessage());
            $this->dispatch('notify', 
                type: 'error', 
                title: __('messages.error'),
                message: __('messages.backup_settings_error') . ': ' . $e->getMessage()
            );
        }
    }
    
    /**
     * Agendar tarefa de backup automático
     */
    private function scheduleBackupTask()
    {
        // Este método precisaria implementar a lógica específica para agendar tarefas
        // no sistema operacional (Windows Task Scheduler, Cron no Linux, etc.)
        // Como é algo específico do sistema, o método pode criar um arquivo de configuração
        // que um script externo utilizaria para configurar o agendamento.
        
        $configPath = storage_path('app/backup_schedule.json');
        $config = [
            'enabled' => true,
            'frequency' => $this->backupFrequency,
            'time' => $this->backupTime,
            'retention' => $this->backupRetention,
            'last_updated' => date('Y-m-d H:i:s'),
        ];
        
        file_put_contents($configPath, json_encode($config, JSON_PRETTY_PRINT));
        
        // Esta configuração seria lida por um script de linha de comando que executa
        // php artisan db:backup e que seria agendado no sistema
    }
    
    /**
     * Remover tarefa de backup automático
     */
    private function removeBackupTask()
    {
        $configPath = storage_path('app/backup_schedule.json');
        
        if (file_exists($configPath)) {
            $config = json_decode(file_get_contents($configPath), true);
            $config['enabled'] = false;
            $config['last_updated'] = date('Y-m-d H:i:s');
            
            file_put_contents($configPath, json_encode($config, JSON_PRETTY_PRINT));
        }
    }
    
    /**
     * Limpar caches do sistema
     */
    protected function clearCaches()
    {
        try {
            // Limpar cache de configuração
            \Artisan::call('config:clear');
            
            // Limpar cache de rotas
            \Artisan::call('route:clear');
            
            // Limpar cache de views
            \Artisan::call('view:clear');
            
            // Limpar cache da aplicação
            \Artisan::call('cache:clear');
            
            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao limpar caches: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Fechar o modal de confirmação
     */
    public function closeConfirmModal()
    {
        $this->showConfirmModal = false;
        $this->confirmAction = '';
        $this->confirmMessage = '';
        $this->selectedBackupFile = null;
    }
    
    /**
     * Processa a ação confirmada
     */
    public function processConfirmedAction()
    {
        switch ($this->confirmAction) {
            case 'restoreBackup':
                $this->restoreBackup();
                break;
            case 'deleteBackup':
                $this->deleteBackup();
                break;
        }
        
        $this->closeConfirmModal();
    }
    
    /**
     * Formata tamanho em bytes para uma unidade legível por humanos
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    public function render()
    {
        return view('livewire.settings.database-backup');
    }
}
