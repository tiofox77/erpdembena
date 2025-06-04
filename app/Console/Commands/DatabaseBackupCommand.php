<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Setting;

class DatabaseBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup {--force : Force backup execution regardless of scheduling}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a database backup based on system settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');
        
        if (!$force) {
            // Verificar se o backup está agendado para agora
            if (!$this->shouldRunBackupNow()) {
                $this->info('Backup not scheduled for current time. Use --force to run anyway.');
                return 0;
            }
        }

        $this->info('Starting database backup...');
        $result = $this->createDatabaseBackup();
        
        if ($result['success']) {
            $this->info('Backup created successfully: ' . $result['file']);
            $this->deleteOldBackups();
            return 0;
        } else {
            $this->error('Backup failed: ' . $result['message']);
            return 1;
        }
    }
    
    /**
     * Verificar se o backup deve ser executado agora, baseado nas configurações
     */
    protected function shouldRunBackupNow()
    {
        $backupAutomation = (bool)Setting::get('backup_automation', false);
        
        if (!$backupAutomation) {
            return false;
        }
        
        $backupFrequency = Setting::get('backup_frequency', 'daily');
        $backupTime = Setting::get('backup_time', '00:00');
        
        // Verificar se estamos no horário correto
        $currentTime = date('H:i');
        $scheduledHour = substr($backupTime, 0, 2);
        $currentHour = date('H');
        
        // Se não estiver na hora certa, não executar
        if ($currentHour != $scheduledHour) {
            return false;
        }
        
        // Verificar a frequência
        switch ($backupFrequency) {
            case 'daily':
                // Executar todos os dias no horário configurado
                return true;
                
            case 'weekly':
                // Executar apenas no domingo
                return date('w') == 0;
                
            case 'monthly':
                // Executar apenas no primeiro dia do mês
                return date('j') == 1;
                
            default:
                return false;
        }
    }
    
    /**
     * Criar um backup do banco de dados
     */
    protected function createDatabaseBackup()
    {
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
            
            // Tentar usar mysqldump primeiro (mais confiável)
            $result = $this->backupDatabaseMysqldump($backupPath);
            
            // Se falhar com mysqldump, usar método alternativo
            if (!$result) {
                $result = $this->backupDatabaseAlternative($backupPath);
            }
            
            if ($result) {
                // Registrar no log
                Log::info("Database backup created successfully: {$backupPath}");
                
                return [
                    'success' => true,
                    'file' => $backupPath
                ];
            } else {
                throw new \Exception("Failed to create backup using both methods");
            }
        } catch (\Exception $e) {
            Log::error('Error creating database backup: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Backup usando mysqldump
     */
    protected function backupDatabaseMysqldump($outputFile)
    {
        try {
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port', 3306);
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            
            // Caminhos possíveis para mysqldump
            $possibleMysqldumpPaths = [
                'mysqldump',                            // Disponível no PATH
                'C:\\laragon\\bin\\mysql\\bin\\mysqldump.exe', // Caminho Laragon no Windows  
                '/usr/bin/mysqldump',                   // Caminho comum no Linux
                '/usr/local/mysql/bin/mysqldump',       // Caminho comum no macOS/Linux
                '/opt/homebrew/bin/mysqldump',          // Homebrew no macOS
            ];
            
            // Encontrar o caminho do mysqldump
            $mysqldumpPath = null;
            foreach ($possibleMysqldumpPaths as $path) {
                if (strpos(PHP_OS, 'WIN') !== false) {
                    // Windows
                    $testCmd = 'where ' . $path . ' 2>nul';
                    exec($testCmd, $output, $returnVar);
                    if ($returnVar === 0) {
                        $mysqldumpPath = $path;
                        break;
                    }
                } else {
                    // Linux/macOS
                    $testCmd = 'which ' . $path . ' 2>/dev/null';
                    exec($testCmd, $output, $returnVar);
                    if ($returnVar === 0) {
                        $mysqldumpPath = $path;
                        break;
                    }
                }
            }
            
            // Se encontrou mysqldump, usar para fazer backup
            if ($mysqldumpPath) {
                $command = sprintf(
                    '"%s" --host="%s" --port=%d --user="%s" --password="%s" "%s" > "%s"',
                    $mysqldumpPath,
                    $host,
                    $port,
                    $username,
                    $password,
                    $database,
                    $outputFile
                );
                
                // No Windows, usar shell diferente
                if (strpos(PHP_OS, 'WIN') !== false) {
                    $command = 'cmd /c ' . $command;
                }
                
                exec($command, $output, $returnVar);
                
                // Verificar se o arquivo foi criado e tem conteúdo
                return (file_exists($outputFile) && filesize($outputFile) > 0);
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error('Error using mysqldump: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Backup alternativo usando PHP
     */
    protected function backupDatabaseAlternative($outputFile)
    {
        try {
            $output = "-- Database backup generated by ERPDEMBENA\n";
            $output .= "-- Generated at: " . date('Y-m-d H:i:s') . "\n\n";
            
            // Obter todas as tabelas
            $tables = [];
            $result = DB::select('SHOW TABLES');
            
            foreach ($result as $row) {
                $tables[] = array_values((array)$row)[0];
            }
            
            foreach ($tables as $table) {
                $this->info("Backing up table: $table");
                
                // Estrutura da tabela
                $output .= "-- Table structure for table `$table`\n";
                $output .= "DROP TABLE IF EXISTS `$table`;\n";
                
                $createTableResult = DB::select("SHOW CREATE TABLE `$table`");
                $createTableSql = array_values((array)$createTableResult[0])[1];
                $output .= $createTableSql . ";\n\n";
                
                // Dados da tabela
                $output .= "-- Data for table `$table`\n";
                
                $rows = DB::table($table)->get();
                
                if (count($rows) > 0) {
                    // Obter nomes das colunas
                    $columns = array_keys((array)$rows[0]);
                    $columnList = '`' . implode('`, `', $columns) . '`';
                    
                    foreach ($rows as $row) {
                        $rowData = [];
                        $row = (array)$row;
                        
                        foreach ($columns as $column) {
                            if (is_null($row[$column])) {
                                $rowData[] = 'NULL';
                            } elseif (is_numeric($row[$column])) {
                                $rowData[] = $row[$column];
                            } else {
                                $rowData[] = "'" . str_replace("'", "''", $row[$column]) . "'";
                            }
                        }
                        
                        $output .= "INSERT INTO `$table` ($columnList) VALUES (" . implode(', ', $rowData) . ");\n";
                    }
                }
                
                $output .= "\n\n";
            }
            
            file_put_contents($outputFile, $output);
            
            return (file_exists($outputFile) && filesize($outputFile) > 0);
        } catch (\Exception $e) {
            Log::error('Error using alternative backup method: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Deletar backups antigos com base na configuração de retenção
     */
    protected function deleteOldBackups()
    {
        try {
            $retentionDays = (int)Setting::get('backup_retention', 7);
            $backupDir = storage_path('app/systembackup');
            
            if (!file_exists($backupDir)) {
                return;
            }
            
            $this->info("Checking for old backups to delete (retention: $retentionDays days)");
            
            $files = array_filter(scandir($backupDir) ?: [], function($file) {
                return !in_array($file, ['.', '..']) && pathinfo($file, PATHINFO_EXTENSION) === 'sql';
            });
            
            $cutoffTime = time() - ($retentionDays * 24 * 60 * 60);
            
            foreach ($files as $file) {
                $fullPath = $backupDir . '/' . $file;
                $fileTime = filemtime($fullPath);
                
                if ($fileTime < $cutoffTime) {
                    if (unlink($fullPath)) {
                        $this->info("Deleted old backup: $file");
                        Log::info("Deleted old backup: $fullPath");
                    } else {
                        $this->error("Failed to delete old backup: $file");
                        Log::error("Failed to delete old backup: $fullPath");
                    }
                }
            }
        } catch (\Exception $e) {
            $this->error('Error deleting old backups: ' . $e->getMessage());
            Log::error('Error deleting old backups: ' . $e->getMessage());
        }
    }
}
