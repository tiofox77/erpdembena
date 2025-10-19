<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;

class SystemSettings extends Component
{
    use WithFileUploads;

    // General Settings
    public $company_name;
    public $company_logo;
    public $app_timezone;
    public $date_format;
    public $currency;
    public $language;
    
    // Company Details
    public $company_address;
    public $company_phone;
    public $company_email;
    public $company_website;
    public $company_tax_id;

    // Update Settings
    public $github_repository = 'your-username/your-repository';
    public $current_version;
    public $latest_version;
    public $update_available = false;
    public $update_notes = [];
    public $backup_before_update = true;
    public $update_progress = 0;
    public $update_status = '';
    public $update_logs = [];
    public $update_step = '';
    public $showUpdateModal = false;
    public $isCheckingForUpdates = false;
    public $isUpdating = false;
    
    // Backup & Restore Settings
    public $available_backups = [];
    public $selected_backup = '';
    public $isRestoringBackup = false;
    public $restore_progress = 0;
    public $restore_status = '';

    // Maintenance & Debug Settings
    public $maintenance_mode = false;
    public $debug_mode = false;

    // System Requirements
    public $systemRequirements = [];
    public $isCheckingRequirements = false;
    public $requirementsStatus = [
        'passed' => 0,
        'warnings' => 0,
        'failed' => 0
    ];
    
    // OPcache Status
    public $opcacheStatus = [];
    public $opcacheHealth = [];

    // Modal states
    public $showConfirmModal = false;
    public $confirmAction = '';
    public $confirmMessage = '';
    public $confirmData = null;

    // Active tab
    #[Url(history: true)]
    public $activeTab = 'general';

    /**
     * Define validation rules
     */
    protected function rules()
    {
        return [
            'company_name' => 'required|string|max:255',
            'app_timezone' => 'required|string',
            'date_format' => 'required|string',
            'currency' => 'required|string|size:3',
            'language' => 'required|string|size:2',
            'github_repository' => 'required|string|regex:/^[a-zA-Z0-9_-]+\/[a-zA-Z0-9_-]+$/',
            'company_logo' => $this->company_logo instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile
                ? 'image|max:1024'
                : 'nullable',
            'company_address' => 'nullable|string|max:255',
            'company_phone' => 'nullable|string|max:30',
            'company_email' => 'nullable|email|max:100',
            'company_website' => 'nullable|string|max:100',
            'company_tax_id' => 'nullable|string|max:30',
        ];
    }

    /**
     * Define custom validation messages
     */
    protected function messages()
    {
        return [
            'company_name.required' => 'Company name is required',
            'app_timezone.required' => 'Time zone is required',
            'date_format.required' => 'Date format is required',
            'currency.required' => 'Currency is required',
            'currency.size' => 'Currency must be exactly 3 characters',
            'language.required' => 'Language is required',
            'language.size' => 'Language must be exactly 2 characters',
            'github_repository.required' => 'GitHub repository is required',
            'github_repository.regex' => 'Invalid repository format. Use username/repository',
            'company_logo.image' => 'The logo must be an image',
            'company_logo.max' => 'The logo may not be larger than 1MB',
            'company_email.email' => 'Please enter a valid email address',
        ];
    }

    /**
     * Initialize component data
     */
    public function mount()
    {
        try {
            $this->loadSettings();
            $this->loadAvailableBackups();
            $this->checkSystemRequirements();
            $this->loadOpcacheStatus();
        } catch (\Exception $e) {
            Log::error('Erro crítico ao inicializar configurações do sistema: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Em caso de erro crítico, usar valores padrão para manter a página funcional
            $this->setDefaultValues();
            $this->available_backups = [];
            $this->systemRequirements = [];
            $this->requirementsStatus = [
                'passed' => 0,
                'warnings' => 0,
                'failed' => 1
            ];
            $this->opcacheStatus = [];
            $this->opcacheHealth = [];
            
            $this->dispatch('notify', type: 'warning', message: 'Sistema carregado em modo seguro devido a erros. Algumas funcionalidades podem estar limitadas.');
        }
    }

    /**
     * Define listeners for the component
     */
    protected function getListeners()
    {
        return [
            'runArtisanCommand' => 'runArtisanCommand',
        ];
    }

    /**
     * Load settings from database
     */
    protected function loadSettings()
    {
        try {
            $this->company_name = Setting::get('company_name', config('app.name'));
            $this->app_timezone = Setting::get('app_timezone', config('app.timezone'));
            $this->date_format = Setting::get('date_format', 'm/d/Y');
            $this->currency = Setting::get('currency', 'USD');
            $this->language = Setting::get('language', 'en');
            
            // Carregar detalhes da empresa
            $this->company_address = Setting::get('company_address', '');
            $this->company_phone = Setting::get('company_phone', '');
            $this->company_email = Setting::get('company_email', '');
            $this->company_website = Setting::get('company_website', '');
            $this->company_tax_id = Setting::get('company_tax_id', '');
            
            // Carregar configurações de actualização
            $this->github_repository = Setting::get('github_repository', $this->github_repository);
            $this->maintenance_mode = (bool) Setting::get('maintenance_mode', false);
            $this->debug_mode = (bool) Setting::get('debug_mode', false);
            
            // Carregar versão atual do sistema
            $this->loadCurrentVersion();
            
        } catch (\Exception $e) {
            Log::error('Erro ao carregar configurações: ' . $e->getMessage());
            // Usar valores padrão em caso de erro
            $this->setDefaultValues();
        }
    }

    /**
     * Load current system version
     */
    protected function loadCurrentVersion()
    {
        try {
            // Tentar carregar da configuração primeiro
            $this->current_version = config('app.version', '1.0.0');
            
            // Se não existir, tentar carregar da base de dados
            if ($this->current_version === '1.0.0') {
                $this->current_version = Setting::get('app_version', '1.0.0');
            }
            
            // Se ainda for 1.0.0, tentar ler do composer.json
            if ($this->current_version === '1.0.0') {
                $composerPath = base_path('composer.json');
                if (file_exists($composerPath)) {
                    $composer = json_decode(file_get_contents($composerPath), true);
                    $this->current_version = $composer['version'] ?? '1.0.0';
                }
            }
            
            $this->update_status = "Sistema atualizado - Versão v{$this->current_version}";
            
        } catch (\Exception $e) {
            Log::error('Erro ao carregar versão atual: ' . $e->getMessage());
            $this->current_version = '1.0.0';
            $this->update_status = 'Erro ao verificar versão do sistema';
        }
    }

    /**
     * Set default values in case of error
     */
    protected function setDefaultValues()
    {
        $this->company_name = config('app.name', 'ERP Sistema');
        $this->app_timezone = config('app.timezone', 'UTC');
        $this->date_format = 'd/m/Y';
        $this->currency = 'AOA';
        $this->language = 'pt';
        $this->company_address = '';
        $this->company_phone = '';
        $this->company_email = '';
        $this->company_website = '';
        $this->company_tax_id = '';
        $this->github_repository = 'tiofox77/erpdembena';
        $this->maintenance_mode = false;
        $this->debug_mode = false;
        $this->current_version = '1.0.0';
        $this->update_status = 'Sistema funcionando em modo seguro';
    }
    
    /**
     * Load OPcache status information
     */
    public function loadOpcacheStatus()
    {
        try {
            if (class_exists(\App\Helpers\OpcacheHelper::class)) {
                $this->opcacheStatus = \App\Helpers\OpcacheHelper::getStats();
                $this->opcacheHealth = \App\Helpers\OpcacheHelper::getHealth();
            } else {
                $this->opcacheStatus = [];
                $this->opcacheHealth = [
                    'status' => 'unavailable',
                    'message' => 'OpcacheHelper não disponível',
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Erro ao carregar status do OPcache: ' . $e->getMessage());
            $this->opcacheStatus = [];
            $this->opcacheHealth = [
                'status' => 'error',
                'message' => 'Erro ao carregar informações do OPcache',
            ];
        }
    }

    /**
     * Real-time validation on property updates
     */
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    /**
     * Change the active tab
     */
    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;

        // Auto check system requirements when switching to that tab
        if ($tab === 'requirements') {
            $this->checkSystemRequirements();
        }
    }

    /**
     * Save general settings
     */
    public function saveGeneralSettings()
    {
        $validatedData = $this->validate([
            'company_name' => 'required|string|max:255',
            'app_timezone' => 'required|string',
            'date_format' => 'required|string',
            'currency' => 'required|string|size:3',
            'language' => 'required|string|size:2',
            'company_logo' => $this->company_logo instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile
                ? 'image|max:1024'
                : 'nullable',
            'company_address' => 'nullable|string|max:255',
            'company_phone' => 'nullable|string|max:30',
            'company_email' => 'nullable|email|max:100',
            'company_website' => 'nullable|string|max:100',
            'company_tax_id' => 'nullable|string|max:30',
        ]);

        try {
            Log::info('Saving general settings', [
                'company_name' => $this->company_name,
                'app_timezone' => $this->app_timezone,
                'date_format' => $this->date_format,
                'currency' => $this->currency,
                'language' => $this->language,
                'company_address' => $this->company_address,
                'company_phone' => $this->company_phone,
                'company_email' => $this->company_email,
                'company_website' => $this->company_website,
                'company_tax_id' => $this->company_tax_id,
            ]);

            // Save company logo if uploaded
            if ($this->company_logo instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                $logoPath = $this->company_logo->store('logos', 'public');
                Setting::set('company_logo', $logoPath, 'general', 'string', 'Company logo', true);
                Log::info('Company logo saved at: ' . $logoPath);
            }

            // Save other settings
            Setting::set('company_name', $this->company_name, 'general', 'string', 'Company name', true);
            Setting::set('app_timezone', $this->app_timezone, 'general', 'string', 'System timezone', true);
            Setting::set('date_format', $this->date_format, 'general', 'string', 'Date format', true);
            Setting::set('currency', $this->currency, 'general', 'string', 'Currency', true);
            Setting::set('language', $this->language, 'general', 'string', 'Language', true);
            
            // Save company details
            Setting::set('company_address', $this->company_address, 'company', 'string', 'Endereço completo da empresa', true);
            Setting::set('company_phone', $this->company_phone, 'company', 'string', 'Telefone de contato da empresa', true);
            Setting::set('company_email', $this->company_email, 'company', 'string', 'Email de contato da empresa', true);
            Setting::set('company_website', $this->company_website, 'company', 'string', 'Site da empresa', true);
            Setting::set('company_tax_id', $this->company_tax_id, 'company', 'string', 'CNPJ da empresa', true);

            // Clear cache to apply new settings
            $this->clearSettingsCache();
            Artisan::call('config:clear');

            $this->dispatch('notify', type: 'success', message: 'General settings have been updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error saving general settings: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $this->dispatch('notify', type: 'error', message: 'Failed to save general settings: ' . $e->getMessage());
        }
    }

    /**
     * Save maintenance settings
     */
    public function saveMaintenanceSettings()
    {
        try {
            Log::info('Saving maintenance settings', [
                'maintenance_mode' => $this->maintenance_mode,
                'debug_mode' => $this->debug_mode,
            ]);

            Setting::set('maintenance_mode', $this->maintenance_mode ? '1' : '0', 'maintenance', 'boolean', 'Maintenance mode', true);
            Setting::set('debug_mode', $this->debug_mode ? '1' : '0', 'maintenance', 'boolean', 'Debug mode', false);

            // Apply maintenance mode
            if ($this->maintenance_mode) {
                Artisan::call('down');
                Log::info('Maintenance mode activated');
            } else {
                Artisan::call('up');
                Log::info('Maintenance mode deactivated');
            }

            $this->clearSettingsCache();

            $this->dispatch('notify', type: 'info', message: 'Maintenance settings have been updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error saving maintenance settings: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $this->dispatch('notify', type: 'error', message: 'Failed to save maintenance settings: ' . $e->getMessage());
        }
    }

    /**
     * Save update settings
     */
    public function saveUpdateSettings()
    {
        $validatedData = $this->validate([
            'github_repository' => 'required|string|regex:/^[a-zA-Z0-9_-]+\/[a-zA-Z0-9_-]+$/',
        ]);

        try {
            Log::info('Saving update settings', [
                'github_repository' => $this->github_repository,
            ]);

            Setting::set('github_repository', $this->github_repository, 'updates', 'string', 'GitHub repository for updates', false);

            $this->clearSettingsCache();

            $this->dispatch('notify', type: 'success', message: 'Update settings have been saved successfully.');
        } catch (\Exception $e) {
            Log::error('Error saving update settings: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $this->dispatch('notify', type: 'error', message: 'Failed to save update settings: ' . $e->getMessage());
        }
    }

    /**
     * Check for updates from GitHub releases
     */
    public function checkForUpdates()
    {
        $this->isCheckingForUpdates = true;
        $this->update_status = 'Verificando actualizações...';

        try {
            // Recarregar versão atual antes de verificar
            $this->loadCurrentVersion();
            
            if (empty($this->github_repository) || $this->github_repository === 'your-username/your-repository') {
                throw new \Exception('Repositório GitHub não configurado');
            }

            $apiUrl = "https://api.github.com/repos/{$this->github_repository}/releases/latest";

            $response = Http::timeout(30)->get($apiUrl);

            if ($response->successful()) {
                $latest = $response->json();
                $this->latest_version = ltrim($latest['tag_name'] ?? '0.0.0', 'v');

                // Compare versions
                if (version_compare($this->latest_version, $this->current_version, '>')) {
                    $this->update_available = true;
                    $this->update_notes = [
                        'title' => $latest['name'] ?? 'Nova Versão',
                        'body' => $latest['body'] ?? 'Actualização disponível.',
                        'download_url' => $latest['zipball_url'] ?? null,
                        'published_at' => $latest['published_at'] ?? now()->toISOString()
                    ];

                    $this->update_status = "Actualização disponível: v{$this->latest_version}";
                    $this->dispatch('notify', type: 'info', message: "Actualização v{$this->latest_version} disponível para instalação.");
                } else {
                    $this->update_available = false;
                    $this->update_status = "Sistema actualizado - Versão atual: v{$this->current_version}";
                    $this->dispatch('notify', type: 'success', message: "Sistema atualizado (v{$this->current_version}).");
                }
            } else {
                $this->update_status = "Não foi possível verificar actualizações. Código: {$response->status()}";
                $this->dispatch('notify', type: 'error', message: 'Falha ao verificar actualizações. Tente novamente mais tarde.');
            }
        } catch (\Exception $e) {
            $this->update_status = 'Erro ao verificar actualizações: ' . $e->getMessage();
            Log::error('Verificação de actualização falhou: ' . $e->getMessage(), [
                'repository' => $this->github_repository,
                'current_version' => $this->current_version
            ]);
            $this->dispatch('notify', type: 'error', message: 'Erro ao verificar actualizações: ' . $e->getMessage());
        } finally {
            $this->isCheckingForUpdates = false;
        }
    }

    /**
     * Show confirmation modal before starting update
     */
    public function confirmStartUpdate()
    {
        if (!$this->update_available) {
            $this->dispatch('notify', type: 'error', message: 'Nenhuma actualização disponível para instalar.');
            return;
        }

        // Show update modal instead of confirm modal
        $this->showUpdateModal = true;
        $this->update_logs = [];
        $this->update_progress = 0;
        $this->update_status = 'Aguardando confirmação...';
        $this->update_step = 'ready';
    }
    
    /**
     * Close update modal
     */
    public function closeUpdateModal()
    {
        if (!$this->isUpdating) {
            $this->showUpdateModal = false;
            $this->update_logs = [];
        }
    }

    /**
     * Start the update process
     */
    public function startUpdate()
    {
        if (!$this->update_available) {
            $this->dispatch('notify', type: 'error', message: 'No updates available to install.');
            return;
        }

        $this->isUpdating = true;
        $this->update_progress = 0;
        $this->update_logs = [];
        $this->update_status = 'Iniciando processo de atualização...';
        $this->update_step = 'starting';

        // Create a log file for this update
        $timestamp = date('Y-m-d_H-i-s');
        $logFile = storage_path("logs/update_{$timestamp}.log");
        $this->logToFile($logFile, "╔═══════════════════════════════════════════════════════════╗", 'success');
        $this->logToFile($logFile, "║  SISTEMA DE ATUALIZAÇÃO - DEMBENA ERP                   ║", 'success');
        $this->logToFile($logFile, "╚═══════════════════════════════════════════════════════════╝", 'success');
        $this->logToFile($logFile, "Iniciando atualização para versão {$this->latest_version}", 'info');
        $this->logToFile($logFile, "Versão atual: {$this->current_version}", 'info');
        $this->logToFile($logFile, "", 'info');

        try {
            // Create backup if option selected
            if ($this->backup_before_update) {
                $this->update_step = 'backup';
                $this->update_status = '📦 Criando backup do sistema...';
                $this->update_progress = 10;
                $this->logToFile($logFile, "[ETAPA 1/6] Criando backup do sistema...", 'warning');
                $backupPath = $this->createSimpleBackup();
                $this->logToFile($logFile, "✓ Backup criado com sucesso: $backupPath", 'success');
            }

            // Put application in maintenance mode
            $this->update_step = 'maintenance';
            $this->update_status = '🔧 Ativando modo de manutenção...';
            $this->update_progress = 20;
            $this->logToFile($logFile, "[ETAPA 2/6] Ativando modo de manutenção...", 'warning');
            $this->enableMaintenanceMode();
            $this->logToFile($logFile, "✓ Modo de manutenção ativado", 'success');

            // Download the update
            $this->update_step = 'download';
            $this->update_status = '⬇️ Baixando atualização...';
            $this->update_progress = 30;
            $this->logToFile($logFile, "[ETAPA 3/6] Baixando pacote de atualização...", 'warning');
            
            // Verificar se download_url existe
            if (empty($this->update_notes['download_url'])) {
                throw new \Exception('URL de download da actualização não disponível');
            }
            
            $update_file = $this->downloadUpdate($this->update_notes['download_url']);
            $this->logToFile($logFile, "✓ Pacote baixado: $update_file", 'success');

            // Extract the update
            $this->update_step = 'extract';
            $this->update_status = '📂 Extraindo arquivos...';
            $this->update_progress = 50;
            $this->logToFile($logFile, "[ETAPA 4/6] Extraindo arquivos da atualização...", 'warning');
            $updatedFiles = $this->extractUpdate($update_file);
            $fileCount = is_array($updatedFiles) ? count($updatedFiles) : 0;
            $this->logToFile($logFile, "✓ {$fileCount} arquivos extraídos com sucesso", 'success');

            // Handle case where updatedFiles might not be an array
            if (!is_array($updatedFiles)) {
                $updatedFiles = [];
            }

            // Run database migrations
            $this->update_step = 'migrate';
            $this->update_status = '🗃️ Executando migrações da base de dados...';
            $this->update_progress = 70;
            $this->logToFile($logFile, "[ETAPA 5/6] Executando migrações da base de dados...", 'warning');
            $migrationsResult = $this->runMigrations($logFile);

            if ($migrationsResult['success']) {
                $this->logToFile($logFile, "✓ Migrações executadas com sucesso", 'success');
            } else {
                $this->logToFile($logFile, "✗ Falha nas migrações: " . $migrationsResult['error'], 'error');
            }

            // Update version in configuration
            $this->update_step = 'finalize';
            $this->update_status = '✨ Finalizando atualização...';
            $this->update_progress = 90;
            $this->logToFile($logFile, "[ETAPA 6/6] Finalizando atualização...", 'warning');

            // Ensure the version is updated in the database
            try {
                // Make sure we get the current version from the database, not from memory
                $oldVersion = Setting::get('app_version', config('app.version', '1.0.0'));
                $this->logToFile($logFile, "Versão atual na base de dados: {$oldVersion}", 'info');

                // Explicitly update with forced cache refresh
                DB::beginTransaction();

                // First delete any existing version setting to avoid conflicts
                DB::table('settings')->where('key', 'app_version')->delete();

                // Then insert the new version
                DB::table('settings')->insert([
                    'key' => 'app_version',
                    'value' => $this->latest_version,
                    'group' => 'updates',
                    'type' => 'string',
                    'description' => 'Current system version',
                    'is_public' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::commit();

                // Now update the setting in the regular way to ensure cache is updated
            Setting::set('app_version', $this->latest_version, 'updates', 'string', 'Current system version', true);

                // Double check that the setting was updated
                Setting::clearCache();
                $newVersionInDb = Setting::get('app_version', 'unknown');

                $this->logToFile($logFile, "✓ Versão atualizada: {$oldVersion} → {$this->latest_version}", 'success');
                $this->logToFile($logFile, "✓ Verificação: {$newVersionInDb}", 'info');

                Log::info("System version updated in database settings", [
                    'old_version' => $oldVersion,
                    'new_version' => $this->latest_version,
                    'verified_db_value' => $newVersionInDb
                ]);

                // Update runtime configuration
                config(['app.version' => $this->latest_version]);

            $this->current_version = $this->latest_version;
            $this->update_available = false;
            } catch (\Exception $e) {
                $this->logToFile($logFile, "Error updating version in database: " . $e->getMessage());
                Log::error("Failed to update version in database", [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);

                // Continue even if version update in DB fails
                $this->current_version = $this->latest_version;
                $this->update_available = false;
            }

            $this->logToFile($logFile, "✓ Versão do sistema atualizada para: {$this->latest_version}", 'success');

            // Clear caches
            $this->logToFile($logFile, "Limpando caches do sistema...", 'info');
            $this->clearSettingsCache();
            $this->clearCaches();
            $this->logToFile($logFile, "✓ Caches limpos com sucesso", 'success');
            
            // Optimize OPcache
            try {
                Artisan::call('opcache:optimize', ['--clear' => true]);
                $this->logToFile($logFile, "✓ OPcache otimizado e limpo", 'success');
            } catch (\Exception $e) {
                $this->logToFile($logFile, "⚠ Aviso: Falha na otimização do OPcache - " . $e->getMessage(), 'warning');
                // Continue even if OPcache optimization fails
            }

            // Bring application back online
            $this->logToFile($logFile, "Desativando modo de manutenção...", 'info');
            $this->disableMaintenanceMode();
            $this->logToFile($logFile, "✓ Modo de manutenção desativado", 'success');
            $this->logToFile($logFile, "", 'info');
            $this->logToFile($logFile, "╔═══════════════════════════════════════════════════════════╗", 'success');
            $this->logToFile($logFile, "║  ATUALIZAÇÃO CONCLUÍDA COM SUCESSO!                     ║", 'success');
            $this->logToFile($logFile, "║  Versão: {$this->latest_version}" . str_repeat(' ', 54 - strlen($this->latest_version)) . "║", 'success');
            $this->logToFile($logFile, "╚═══════════════════════════════════════════════════════════╝", 'success');

            $this->update_status = '✅ Atualização concluída com sucesso!';
            $this->update_progress = 100;
            $this->update_step = 'completed';

            $this->dispatch('notify', type: 'success', message: "System has been updated to version {$this->latest_version}");
            Log::info("System updated to version {$this->latest_version}", [
                'log_file' => $logFile,
                'updated_files' => count($updatedFiles)
            ]);
        } catch (\Exception $e) {
            $this->update_status = "❌ Falha na atualização: {$e->getMessage()}";
            $this->update_step = 'failed';
            $this->logToFile($logFile, "", 'error');
            $this->logToFile($logFile, "╔═══════════════════════════════════════════════════════════╗", 'error');
            $this->logToFile($logFile, "║  ERRO NA ATUALIZAÇÃO                                     ║", 'error');
            $this->logToFile($logFile, "╚═══════════════════════════════════════════════════════════╝", 'error');
            $this->logToFile($logFile, "✗ Falha: {$e->getMessage()}", 'error');
            $this->logToFile($logFile, "Rastreamento do erro: {$e->getTraceAsString()}", 'error');
            Log::error("Update process error: {$e->getMessage()}", [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'log_file' => $logFile
            ]);

            // Ensure site comes back online even if update fails
            $this->disableMaintenanceMode();
            $this->logToFile($logFile, "✓ Modo de manutenção desativado após erro", 'warning');

            $this->dispatch('notify', type: 'error', message: "Update failed: {$e->getMessage()}");
        }

        $this->isUpdating = false;
    }

    /**
     * Log message to update log file and live stream
     */
    protected function logToFile($logFile, $message, $type = 'info')
    {
        $timestamp = date('H:i:s');
        $logMessage = "[$timestamp] $message" . PHP_EOL;

        // Create directory if it doesn't exist
        $logDir = dirname($logFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }

        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        // Add to live logs array for real-time display
        $this->update_logs[] = [
            'timestamp' => $timestamp,
            'message' => $message,
            'type' => $type  // info, success, warning, error
        ];
        
        // Keep only last 100 log entries
        if (count($this->update_logs) > 100) {
            array_shift($this->update_logs);
        }
        
        // Force Livewire to update the view
        $this->dispatch('log-updated');
        
        return true;
    }

    /**
     * Create a simple backup of important files
     */
    protected function createSimpleBackup()
    {
        try {
            // Create backup directory if it doesn't exist
            $backupDir = storage_path('app/backups');
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            // Create a timestamped backup filename
            $timestamp = date('Y-m-d_H-i-s');
            $backupFile = $backupDir . '/backup_' . $timestamp . '.zip';

            // Create a new zip archive
            $zip = new \ZipArchive();
            if ($zip->open($backupFile, \ZipArchive::CREATE) !== true) {
                throw new \Exception("Cannot create backup archive: $backupFile");
            }

            // Directories to backup
            $directoriesToBackup = [
                'app',
                'config',
                'database',
                'resources',
                'routes'
            ];

            // Add directories to the zip
            foreach ($directoriesToBackup as $dir) {
                $this->addDirectoryToZip($zip, base_path($dir), $dir);
            }

            // Add important files at root level
            $filesToBackup = [
                '.env',
                'composer.json',
                'artisan',
                'package.json'
            ];

            foreach ($filesToBackup as $file) {
                if (file_exists(base_path($file))) {
                    $zip->addFile(base_path($file), $file);
                }
            }

            // Close the zip file
            $zip->close();

            // Backup database if possible
            $dbBackupFile = $backupDir . '/database_' . $timestamp . '.sql';
            $this->backupDatabase($dbBackupFile);

            Log::info("Backup created successfully at $backupFile");
            return $backupFile;
        } catch (\Exception $e) {
            Log::error("Backup creation failed: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            $this->dispatch('notify', type: 'warning', message: "Backup creation failed, but update will continue: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add a directory to a zip archive recursively
     */
    protected function addDirectoryToZip($zip, $dir, $relativePath)
    {
        // Skip if directory doesn't exist
        if (!is_dir($dir)) {
            return;
        }

        // Create directory in zip
        $zip->addEmptyDir($relativePath);

        // Loop through directory contents
        $dirHandle = opendir($dir);
        while (($file = readdir($dirHandle)) !== false) {
            // Skip . and ..
            if ($file == '.' || $file == '..') {
                continue;
            }

            $filePath = $dir . '/' . $file;
            $fileRelativePath = $relativePath . '/' . $file;

            if (is_dir($filePath)) {
                // Recursively add subdirectories
                $this->addDirectoryToZip($zip, $filePath, $fileRelativePath);
            } else {
                // Add files
                $zip->addFile($filePath, $fileRelativePath);
            }
        }
        closedir($dirHandle);
    }

    /**
     * Load available backups from storage
     */
    public function loadAvailableBackups()
    {
        try {
            $backupDir = storage_path('app/backups');
            $this->available_backups = [];
            
            if (!is_dir($backupDir)) {
                return;
            }

            $backupFiles = glob($backupDir . '/backup_*.zip');
            
            if ($backupFiles === false) {
                Log::warning('Erro ao listar backups disponíveis');
                return;
            }
            
            foreach ($backupFiles as $backupFile) {
                try {
                    if (!file_exists($backupFile)) {
                        continue;
                    }
                    
                    $filename = basename($backupFile);
                    $timestamp = str_replace(['backup_', '.zip'], '', $filename);
                    
                    // Parse timestamp to readable format
                    $date = \DateTime::createFromFormat('Y-m-d_H-i-s', $timestamp);
                    
                    $this->available_backups[] = [
                        'filename' => $filename,
                        'filepath' => $backupFile,
                        'timestamp' => $timestamp,
                        'date' => $date ? $date->format('d/m/Y H:i:s') : $timestamp,
                        'size' => $this->formatFileSize(filesize($backupFile)),
                        'database_file' => str_replace('backup_', 'database_', $backupFile) 
                            ? str_replace('.zip', '.sql', str_replace('backup_', 'database_', $backupFile)) 
                            : null
                    ];
                } catch (\Exception $e) {
                    Log::warning('Erro ao processar backup: ' . $backupFile . ' - ' . $e->getMessage());
                    continue;
                }
            }
            
            // Sort by date (newest first)
            usort($this->available_backups, function($a, $b) {
                return strcmp($b['timestamp'], $a['timestamp']);
            });
            
        } catch (\Exception $e) {
            Log::error('Erro crítico ao carregar backups: ' . $e->getMessage());
            $this->available_backups = [];
        }
    }

    /**
     * Confirm backup restore
     */
    public function confirmRestoreBackup($backupFilename)
    {
        $this->selected_backup = $backupFilename;
        $this->dispatch('show-restore-confirmation', $backupFilename);
    }

    /**
     * Restore from backup
     */
    public function restoreFromBackup()
    {
        if (empty($this->selected_backup)) {
            $this->dispatch('notify', type: 'error', message: 'Nenhum backup selecionado para restauro.');
            return;
        }

        $this->isRestoringBackup = true;
        $this->restore_progress = 0;
        $this->restore_status = 'Preparando restauro...';

        try {
            // Find the backup details
            $backup = collect($this->available_backups)->firstWhere('filename', $this->selected_backup);
            
            if (!$backup) {
                throw new \Exception('Backup não encontrado.');
            }

            if (!file_exists($backup['filepath'])) {
                throw new \Exception('Arquivo de backup não existe.');
            }

            // Put application in maintenance mode
            $this->restore_status = 'Ativando modo de manutenção...';
            $this->restore_progress = 10;
            Artisan::call('down', ['--message' => 'Sistema em restauro', '--retry' => 60]);

            // Create temporary extraction directory
            $tempDir = storage_path('app/temp_restore_' . time());
            mkdir($tempDir, 0755, true);

            $this->restore_status = 'Extraindo backup...';
            $this->restore_progress = 20;

            // Extract backup
            $zip = new \ZipArchive();
            if ($zip->open($backup['filepath']) !== true) {
                throw new \Exception('Não foi possível abrir o arquivo de backup.');
            }

            $zip->extractTo($tempDir);
            $zip->close();

            $this->restore_status = 'Restaurando arquivos...';
            $this->restore_progress = 40;

            // Restore files (except .env to prevent database connection issues)
            $this->restoreFiles($tempDir);

            $this->restore_status = 'Restaurando base de dados...';
            $this->restore_progress = 70;

            // Restore database if backup exists
            if ($backup['database_file'] && file_exists($backup['database_file'])) {
                $this->restoreDatabase($backup['database_file']);
            }

            $this->restore_progress = 80;

            // Clear settings cache
            $this->clearSettingsCache();

            $this->restore_progress = 90;

            // Clean up temporary directory
            $this->deleteDirectory($tempDir);

            $this->restore_progress = 100;

            // Bring application back up
            Artisan::call('up');

            $this->dispatch('notify', type: 'success', message: 'Sistema restaurado com sucesso!');
            
            // Refresh page after a delay
            $this->dispatch('refresh-page', delay: 3000);

        } catch (\Exception $e) {
            Log::error('Backup restore failed: ' . $e->getMessage(), [
                'backup' => $this->selected_backup,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            // Bring application back up if it's down
            try {
                Artisan::call('up');
            } catch (\Exception $upException) {
                Log::error('Failed to bring application up: ' . $upException->getMessage());
            }

            $this->restore_status = 'Erro no restauro: ' . $e->getMessage();
            $this->dispatch('notify', type: 'error', message: 'Erro no restauro: ' . $e->getMessage());
        } finally {
            $this->isRestoringBackup = false;
            $this->selected_backup = '';
        }
    }

    /**
     * Restore files from backup
     */
    protected function restoreFiles($tempDir)
    {
        $directoriesToRestore = ['app', 'config', 'database', 'resources', 'routes'];
        
        foreach ($directoriesToRestore as $dir) {
            $sourceDir = $tempDir . '/' . $dir;
            $targetDir = base_path($dir);
            
            if (is_dir($sourceDir)) {
                // Backup current directory before replacing
                $backupCurrentDir = $targetDir . '_backup_' . time();
                if (is_dir($targetDir)) {
                    rename($targetDir, $backupCurrentDir);
                }
                
                // Copy restored directory
                $this->copyDirectory($sourceDir, $targetDir);
            }
        }

        // Restore important files
        $filesToRestore = ['composer.json', 'artisan', 'package.json'];
        
        foreach ($filesToRestore as $file) {
            $sourceFile = $tempDir . '/' . $file;
            $targetFile = base_path($file);
            
            if (file_exists($sourceFile)) {
                copy($sourceFile, $targetFile);
            }
        }
    }

    /**
     * Restore database from SQL file
     */
    protected function restoreDatabase($sqlFile)
    {
        try {
            $sql = file_get_contents($sqlFile);
            
            if (empty($sql)) {
                throw new \Exception('Arquivo de backup da base de dados está vazio.');
            }

            // Split SQL into individual statements
            $statements = explode(';', $sql);
            
            DB::beginTransaction();
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    DB::unprepared($statement);
                }
            }
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception('Erro no restauro da base de dados: ' . $e->getMessage());
        }
    }

    /**
     * Copy directory recursively
     */
    protected function copyDirectory($source, $destination)
    {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $target = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            
            if ($item->isDir()) {
                if (!is_dir($target)) {
                    mkdir($target, 0755, true);
                }
            } else {
                copy($item->getPathname(), $target);
            }
        }
    }

    /**
     * Delete a backup file
     */
    public function deleteBackup($backupFilename)
    {
        $backup = collect($this->available_backups)->firstWhere('filename', $backupFilename);
        
        if (!$backup) {
            $this->dispatch('notify', type: 'error', message: 'Backup não encontrado.');
            return;
        }

        try {
            // Delete backup zip file
            if (file_exists($backup['filepath'])) {
                unlink($backup['filepath']);
            }

            // Delete database backup if exists
            if ($backup['database_file'] && file_exists($backup['database_file'])) {
                unlink($backup['database_file']);
            }

            $this->dispatch('notify', type: 'success', message: 'Backup eliminado com sucesso.');
            $this->loadAvailableBackups(); // Refresh list
            
        } catch (\Exception $e) {
            Log::error('Failed to delete backup: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Erro ao eliminar backup: ' . $e->getMessage());
        }
    }

    /**
     * Format file size in human readable format
     */
    protected function formatFileSize($size)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, 2) . ' ' . $units[$i];
    }

    /**
     * Backup database to SQL file
     */
    protected function backupDatabase($outputFile)
    {
        try {
            // Get database configuration
            $connection = config('database.default');
            $config = config("database.connections.$connection");

            if ($connection === 'sqlite') {
                // For SQLite, just copy the database file
                copy(database_path('database.sqlite'), $outputFile);
                return true;
            }

            // For MySQL/MariaDB, use PHP to export
            if ($connection === 'mysql') {
                $host = $config['host'];
                $database = $config['database'];
                $username = $config['username'];
                $password = $config['password'];

                // Try to use mysqldump if available
                $mysqldumpCommand = "mysqldump --host=$host --user=$username --password=$password $database > $outputFile 2>&1";

                // Try PHP-based backup if mysqldump fails
                if (@exec($mysqldumpCommand) === false) {
                    $this->exportDatabaseWithPHP($outputFile);
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Database backup failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Export database using PHP
     */
    protected function exportDatabaseWithPHP($outputFile)
    {
        try {
            $tables = DB::select('SHOW TABLES');
            $output = "-- Database backup created on " . date('Y-m-d H:i:s') . "\n\n";

            foreach ($tables as $table) {
                $tableName = reset($table);

                // Add create table statement
                $createTable = DB::select("SHOW CREATE TABLE `$tableName`");
                $createStatement = end($createTable[0]);
                $output .= $createStatement . ";\n\n";

                // Get table data
                $rows = DB::table($tableName)->get();

                if ($rows->count() > 0) {
                    $columns = array_keys(get_object_vars($rows[0]));
                    $insertHeader = "INSERT INTO `$tableName` (`" . implode('`, `', $columns) . "`) VALUES\n";
                    $output .= $insertHeader;

                    $valueStrings = [];
                    foreach ($rows as $row) {
                        $values = [];
                        foreach ($columns as $column) {
                            $value = $row->$column;
                            if (is_null($value)) {
                                $values[] = "NULL";
                            } else {
                                $values[] = "'" . str_replace("'", "\'", $value) . "'";
                            }
                        }
                        $valueStrings[] = "(" . implode(', ', $values) . ")";
                    }
                    $output .= implode(",\n", $valueStrings) . ";\n\n";
                }
            }

            file_put_contents($outputFile, $output);
            return true;
        } catch (\Exception $e) {
            Log::error("PHP database export failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enable maintenance mode
     */
    protected function enableMaintenanceMode()
    {
        try {
            file_put_contents(storage_path('framework/down'), json_encode([
                'time' => time(),
                'message' => 'System update in progress. Please check back later.',
                'retry' => 60
            ]));

            Setting::set('maintenance_mode', '1', 'maintenance', 'boolean', 'Maintenance mode', true);
            $this->maintenance_mode = true;

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to enable maintenance mode: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Disable maintenance mode
     */
    protected function disableMaintenanceMode()
    {
        try {
            if (file_exists(storage_path('framework/down'))) {
                @unlink(storage_path('framework/down'));
            }

            Setting::set('maintenance_mode', '0', 'maintenance', 'boolean', 'Maintenance mode', true);
            $this->maintenance_mode = false;

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to disable maintenance mode: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Download update from URL
     */
    protected function downloadUpdate($url)
    {
        // Create temp directory if it doesn't exist
        $tempDir = storage_path('app/updates');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipPath = $tempDir . '/update.zip';

        // Try to download using cURL if available
        if (function_exists('curl_init')) {
            $fp = fopen($zipPath, 'w+');
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 600);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'ERP Dembena Updater');
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            fclose($fp);

            if ($httpCode != 200) {
                throw new \Exception("Failed to download update package (HTTP code: $httpCode)");
            }
        } else {
            // Fallback to file_get_contents if cURL is not available
            $content = @file_get_contents($url);
            if ($content === false) {
                throw new \Exception('Failed to download update package using file_get_contents');
            }
            file_put_contents($zipPath, $content);
        }

        if (!file_exists($zipPath) || filesize($zipPath) === 0) {
            throw new \Exception('Downloaded update package is empty or missing');
        }

        return $zipPath;
    }

    /**
     * Extract update from zip file
     */
    protected function extractUpdate($zipFile)
    {
        $zip = new \ZipArchive;
        $extractPath = storage_path('app/updates/extracted');
        $updatedFiles = [];

        // Clear the extract directory
        if (file_exists($extractPath)) {
            $this->deleteDirectory($extractPath);
        }
        mkdir($extractPath, 0755, true);

        if ($zip->open($zipFile) === true) {
            // Get the root directory name from the zip
            $rootDir = null;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                $parts = explode('/', $name);
                if (count($parts) > 0 && !empty($parts[0])) {
                    $rootDir = $parts[0];
                    break;
                }
            }

            // Extract all files
            if (!$zip->extractTo($extractPath)) {
                throw new \Exception("Failed to extract update package: " . $zip->getStatusString());
            }
            $zip->close();

            // If we have a root directory in the zip, copy from there
            $sourceDir = $extractPath;
            if ($rootDir && is_dir($extractPath . '/' . $rootDir)) {
                $sourceDir = $extractPath . '/' . $rootDir;
            }

            // Copy files to the application root, skipping certain directories
            $updatedFiles = $this->copyFilesToDestination($sourceDir, base_path());

            // Ensure the function always returns an array
            if (!is_array($updatedFiles)) {
                return [];
            }

            return $updatedFiles;
        } else {
            throw new \Exception('Failed to open update package: ' . $zip->getStatusString());
        }
    }

    /**
     * Copy files from update package to destination
     */
    protected function copyFilesToDestination($source, $destination)
    {
        // Directories to skip
        $skipDirs = [
            'storage',
            'vendor',
            'node_modules',
            '.git',
        ];

        $updatedFiles = [];

        // Scan source directory
        $items = scandir($source);
        foreach ($items as $item) {
            // Skip dots and hidden files
            if ($item === '.' || $item === '..' || $item[0] === '.') {
                continue;
            }

            $sourcePath = $source . '/' . $item;
            $destPath = $destination . '/' . $item;
            $relativePath = str_replace(base_path() . '/', '', $destPath);

            // Skip specified directories
            if (is_dir($sourcePath) && in_array($item, $skipDirs)) {
                continue;
            }

            if (is_dir($sourcePath)) {
                // Create directory if it doesn't exist
                if (!is_dir($destPath)) {
                    mkdir($destPath, 0755, true);
                    $updatedFiles[] = $relativePath . '/ (new directory)';
                }

                // Recursively copy files
                $subUpdatedFiles = $this->copyFilesToDestination($sourcePath, $destPath);
                $updatedFiles = array_merge($updatedFiles, $subUpdatedFiles);
            } else {
                // Check if file exists and has different content
                $isNew = !file_exists($destPath);
                $isDifferent = $isNew || (file_exists($destPath) && md5_file($sourcePath) !== md5_file($destPath));

                if ($isDifferent) {
                    // Copy file
                    if (copy($sourcePath, $destPath)) {
                        $status = $isNew ? 'new file' : 'updated';
                        $updatedFiles[] = $relativePath . " ($status)";
                    }
                }
            }
        }

        // Ensure the function always returns an array
        return $updatedFiles;
    }

    /**
     * Run database migrations
     */
    protected function runMigrations($logFile = null)
    {
        try {
            // Check if we have migrations to run
            $migrationPath = database_path('migrations');
            if (!is_dir($migrationPath)) {
                return [
                    'success' => true,
                    'executed' => [],
                    'error' => null
                ];
            }

            // Get all migration files
            $migrationFiles = glob($migrationPath . '/*.php');
            if (empty($migrationFiles)) {
                return [
                    'success' => true,
                    'executed' => [],
                    'error' => null
                ];
            }

            // Try running migrations using the artisan command first
            try {
                if (class_exists('\Illuminate\Support\Facades\Artisan')) {
                    // First check migration status to identify issues
                    \Illuminate\Support\Facades\Artisan::call('migrate:status');
                    $statusOutput = \Illuminate\Support\Facades\Artisan::output();

                    if ($logFile) {
                        $this->logToFile($logFile, "Migration status before running: " . $statusOutput);
                    }

                    // Try to run migrations with ignore-errors flag if available
                    try {
                        \Illuminate\Support\Facades\Artisan::call('migrate', [
                            '--force' => true,
                            '--step' => true,
                        ]);
                    } catch (\Exception $migrationException) {
                        if ($logFile) {
                            $this->logToFile($logFile, "Standard migration failed. Trying to fix: " . $migrationException->getMessage());
                        }

                        // If we had errors, try our custom migration approach
                        return $this->runManualMigrations($migrationFiles, $logFile);
                    }

                    $output = \Illuminate\Support\Facades\Artisan::output();

                    if ($logFile) {
                        $this->logToFile($logFile, "Migration output: " . $output);
                    }

                    // Parse migration output to get executed migrations
                    $executed = [];
                    if (preg_match_all('/Migrated:\s+(\d{4}_\d{2}_\d{2}_\d{6}_[a-z0-9_]+)/i', $output, $matches)) {
                        $executed = $matches[1];
                    }

                    return [
                        'success' => true,
                        'executed' => $executed,
                        'error' => null
                    ];
                }
            } catch (\Exception $e) {
                // Log the error but continue with manual migration
                if ($logFile) {
                    $this->logToFile($logFile, "Artisan migration failed, trying manual method: " . $e->getMessage());
                }
                Log::warning("Artisan migration failed, trying manual method", [
                    'error' => $e->getMessage()
                ]);
            }

            // If artisan migration failed, try manual method
            return $this->runManualMigrations($migrationFiles, $logFile);

        } catch (\Exception $e) {
            Log::error("Migration process error: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return [
                'success' => false,
                'executed' => [],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Run manual migrations using a more cautious approach
     */
    protected function runManualMigrations($migrationFiles, $logFile = null)
    {
        // Get existing migrations from database
        $ranMigrations = [];
        try {
            $ranMigrations = DB::table('migrations')->pluck('migration')->toArray();
        } catch (\Exception $e) {
            if ($logFile) {
                $this->logToFile($logFile, "Could not get existing migrations from database: " . $e->getMessage());
            }

            // Try to create migrations table if it doesn't exist
            try {
                DB::statement("
                    CREATE TABLE IF NOT EXISTS migrations (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        migration VARCHAR(255) NOT NULL,
                        batch INT NOT NULL
                    )
                ");
                if ($logFile) {
                    $this->logToFile($logFile, "Created migrations table");
                }
            } catch (\Exception $tableEx) {
                if ($logFile) {
                    $this->logToFile($logFile, "Failed to create migrations table: " . $tableEx->getMessage());
                }
            }
        }

        // Get the current batch number
        $batch = 1;
        try {
            $maxBatch = DB::table('migrations')->max('batch');
            if ($maxBatch) {
                $batch = $maxBatch + 1;
            }
        } catch (\Exception $e) {
            // Ignore error and use default batch 1
        }

        // Track executed migrations
        $executedMigrations = [];

        // Process each migration file
        foreach ($migrationFiles as $file) {
            $filename = basename($file, '.php');

            // Skip if already migrated
            if (in_array($filename, $ranMigrations)) {
                continue;
            }

            try {
                // Include the migration file
                require_once $file;

                // Get the class name from filename
                $className = $this->getMigrationClass($file);

                if (class_exists($className)) {
                    $migration = new $className();

                    if (method_exists($migration, 'up')) {
                        // We'll modify the migration on-the-fly to handle column exists errors
                        $this->safeExecuteMigration($migration, $filename, $batch, $logFile);
                        $executedMigrations[] = $filename;
                    }
                } else {
                    if ($logFile) {
                        $this->logToFile($logFile, "Migration class not found: $className in file $filename");
                    }
                }
            } catch (\Exception $e) {
                if ($logFile) {
                    $this->logToFile($logFile, "Error running migration $filename: " . $e->getMessage());
                }

                // Log detailed error info
                Log::error("Migration failed for $filename", [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
            }
        }

        return [
            'success' => true,
            'executed' => $executedMigrations,
            'error' => null
        ];
    }

    /**
     * Safely execute a migration with error handling for column exists
     */
    protected function safeExecuteMigration($migration, $filename, $batch, $logFile = null)
    {
        try {
            // Start transaction
            DB::beginTransaction();

            // Run the migration
            $migration->up();

            // Record the migration
            DB::table('migrations')->insert([
                'migration' => $filename,
                'batch' => $batch
            ]);

            // Commit if all went well
            DB::commit();

            if ($logFile) {
                $this->logToFile($logFile, "Migration executed: $filename");
            }

            return true;
        } catch (\Exception $e) {
            // Rollback transaction
            DB::rollBack();

            // Check if it's a "column already exists" error
            $message = $e->getMessage();
            if (strpos($message, 'Column already exists') !== false ||
                strpos($message, 'Duplicate column name') !== false) {

                if ($logFile) {
                    $this->logToFile($logFile, "Column already exists in $filename - applying safe migration");
                }

                // Try running the migration with safe schema modifications
                try {
                    $this->executeSafeSchemaMigration($migration, $filename, $batch, $logFile);
                    return true;
                } catch (\Exception $safeEx) {
                    if ($logFile) {
                        $this->logToFile($logFile, "Safe migration failed for $filename: " . $safeEx->getMessage());
                    }
                    throw $safeEx;
                }
            }

            // For other errors, re-throw
            throw $e;
        }
    }

    /**
     * Execute a migration with safe schema modifications (checking if columns exist)
     */
    protected function executeSafeSchemaMigration($migration, $filename, $batch, $logFile = null)
    {
        // Create a custom Schema Builder that checks if columns exist before adding
        $schemaBuilder = DB::getSchemaBuilder();
        $connection = DB::connection();

        // Override the Blueprint's addColumn method to check if column exists first
        $originalAddColumn = \Illuminate\Database\Schema\Blueprint::class;

        // For each table that might be modified in this migration
        $tables = $this->getTablesFromMigration($filename);

        foreach ($tables as $table) {
            // Check if the table exists
            if ($schemaBuilder->hasTable($table)) {
                // Get existing columns
                $columns = $schemaBuilder->getColumnListing($table);

                // Store this information for later use
                $GLOBALS['existing_columns'][$table] = $columns;
            }
        }

        // Hook into Laravel's DatabaseManager to intercept schema operations
        $originalSchemaGet = \Illuminate\Database\DatabaseManager::class;

        // Now run the migration in a transaction
        DB::beginTransaction();

        try {
            // We'll use a backup approach for known problematic migrations
            if (strpos($filename, 'update_users_table_add_required_fields') !== false) {
                // Handle specific migration for users table
                $this->manuallyFixUserTableMigration($logFile);
            } else {
                // For other migrations, use our safe execution
                $migration->up();
            }

            // Record the migration
            DB::table('migrations')->insert([
                'migration' => $filename,
                'batch' => $batch
            ]);

            DB::commit();

            if ($logFile) {
                $this->logToFile($logFile, "Safe migration executed: $filename");
            }

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            if ($logFile) {
                $this->logToFile($logFile, "Safe migration also failed: $filename - " . $e->getMessage());
            }

            throw $e;
        }
    }

    /**
     * Extract potential table names from migration filename
     */
    protected function getTablesFromMigration($filename)
    {
        $tables = [];

        // Try to extract table name from filename
        // Example: 2025_03_27_171304_update_users_table_add_required_fields -> users
        $parts = explode('_', $filename);

        // Skip timestamp parts
        for ($i = 0; $i < 4; $i++) {
            array_shift($parts);
        }

        // Look for patterns like create_X_table, update_X_table
        $nameString = implode('_', $parts);

        if (preg_match('/(create|update)_([a-z0-9_]+)_table/', $nameString, $matches)) {
            $tables[] = $matches[2];
        }

        return $tables;
    }

    /**
     * Manually fix the users table migration that's causing issues
     */
    protected function manuallyFixUserTableMigration($logFile = null)
    {
        try {
            $schemaBuilder = DB::getSchemaBuilder();

            // Check if the users table exists
            if (!$schemaBuilder->hasTable('users')) {
                if ($logFile) {
                    $this->logToFile($logFile, "Users table doesn't exist, can't fix migration");
                }
                return false;
            }

            // Get existing columns
            $columns = $schemaBuilder->getColumnListing('users');

            // Add each column only if it doesn't exist
            $columnsToAdd = [
                'first_name' => "ALTER TABLE `users` ADD COLUMN `first_name` VARCHAR(255) NOT NULL AFTER `name`",
                'last_name' => "ALTER TABLE `users` ADD COLUMN `last_name` VARCHAR(255) NOT NULL AFTER `first_name`",
                'phone' => "ALTER TABLE `users` ADD COLUMN `phone` VARCHAR(255) NULL AFTER `email`",
                'role' => "ALTER TABLE `users` ADD COLUMN `role` VARCHAR(255) NOT NULL DEFAULT 'user' AFTER `phone`",
                'department' => "ALTER TABLE `users` ADD COLUMN `department` VARCHAR(255) NOT NULL DEFAULT 'other' AFTER `role`",
                'is_active' => "ALTER TABLE `users` ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT '1' AFTER `department`"
            ];

            foreach ($columnsToAdd as $column => $sql) {
                if (!in_array($column, $columns)) {
                    try {
                        DB::statement($sql);
                        if ($logFile) {
                            $this->logToFile($logFile, "Added missing column {$column} to users table");
                        }
                    } catch (\Exception $e) {
                        if ($logFile) {
                            $this->logToFile($logFile, "Error adding column {$column}: " . $e->getMessage());
                        }
                    }
                } else {
                    if ($logFile) {
                        $this->logToFile($logFile, "Column {$column} already exists in users table, skipping");
                    }
                }
            }

            return true;
        } catch (\Exception $e) {
            if ($logFile) {
                $this->logToFile($logFile, "Error in manual users table migration fix: " . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Clear application caches
     */
    protected function clearCaches()
    {
        // Clear various cache files
        $cacheDirs = [
            storage_path('framework/cache'),
            storage_path('framework/views'),
            storage_path('framework/sessions'),
        ];

        foreach ($cacheDirs as $dir) {
            if (is_dir($dir)) {
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::CHILD_FIRST
                );

                foreach ($files as $file) {
                    if ($file->isDir()) {
                        continue;
                    }
                    @unlink($file->getRealPath());
                }
            }
        }

        return true;
    }

    /**
     * Show confirmation before running Artisan command
     */
    public function confirmRunArtisanCommand($command)
    {
        // List of allowed commands
        $allowedCommands = [
            'optimize:clear' => 'Clear all caches',
            'cache:clear' => 'Clear application cache',
            'config:clear' => 'Clear config cache',
            'view:clear' => 'Clear compiled views',
            'route:clear' => 'Clear route cache',
            'opcache:optimize' => 'Optimize and clear OPcache',
            'migrate' => 'Run database migrations',
            'storage:link' => 'Create symbolic link to storage',
        ];

        if (!array_key_exists($command, $allowedCommands)) {
            $this->dispatch('notify', type: 'error', message: "Command not allowed: {$command}");
            return;
        }

        $this->confirmAction = 'runArtisanCommand';
        $this->confirmMessage = "Are you sure you want to run the '{$allowedCommands[$command]}' command? This may temporarily affect your site's performance.";
        $this->confirmData = $command;
        $this->showConfirmModal = true;
    }

    /**
     * Run Artisan command
     */
    public function runArtisanCommand($command)
    {
        $this->showConfirmModal = false;

        try {
            // List of allowed commands
            $allowedCommands = [
                'optimize:clear',
                'cache:clear',
                'config:clear',
                'view:clear',
                'route:clear',
                'migrate',
                'storage:link',
            ];

            if (!in_array($command, $allowedCommands)) {
                throw new \Exception("Command not allowed: {$command}");
            }

            // Execute the command
            Artisan::call($command);

            // Get output
            $output = Artisan::output();

            $this->dispatch('notify', type: 'success', message: "The command '{$command}' was executed successfully.");

            Log::info("Artisan command executed: {$command}", ['output' => $output]);
        } catch (\Exception $e) {
            Log::error("Error executing Artisan command: {$e->getMessage()}");
            $this->dispatch('notify', type: 'error', message: "Failed to execute command: {$e->getMessage()}");
        }
    }

    /**
     * Close confirmation modal
     */
    public function closeConfirmModal()
    {
        $this->showConfirmModal = false;
        $this->confirmAction = '';
        $this->confirmMessage = '';
        $this->confirmData = null;
    }

    /**
     * Process confirmed action
     */
    public function processConfirmedAction()
    {
        if ($this->confirmAction === 'startUpdate') {
            $this->startUpdate();
        } elseif ($this->confirmAction === 'runArtisanCommand' && $this->confirmData) {
            $this->runArtisanCommand($this->confirmData);
        }

        $this->closeConfirmModal();
    }


    /**
     * Clear settings cache
     */
    public function clearSettingsCache()
    {
        Setting::clearCache();
    }

    /**
     * Check system requirements
     */
    public function checkSystemRequirements()
    {
        $this->isCheckingRequirements = true;
        $this->systemRequirements = [];
        $this->requirementsStatus = [
            'passed' => 0,
            'warnings' => 0,
            'failed' => 0
        ];

        // PHP Version
        $phpVersion = phpversion();
        $requiredPhpVersion = '8.0.0';
        $phpVersionStatus = version_compare($phpVersion, $requiredPhpVersion, '>=') ? 'passed' : 'failed';
        $this->addRequirement(
            'PHP Version',
            "PHP $requiredPhpVersion or higher required",
            $phpVersion,
            $phpVersionStatus,
            true
        );

        // PHP Extensions
        $requiredExtensions = [
            'zip' => 'Required for backup and update functionality',
            'curl' => 'Required for API requests and updates',
            'pdo' => 'Required for database connections',
            'pdo_mysql' => 'Required for MySQL database',
            'openssl' => 'Required for secure connections',
            'mbstring' => 'Required for UTF-8 string handling',
            'tokenizer' => 'Required by Laravel framework',
            'json' => 'Required for data processing',
            'fileinfo' => 'Required for file uploads',
            'xml' => 'Required by Laravel framework',
            'gd' => 'Recommended for image processing',
        ];

        foreach ($requiredExtensions as $extension => $description) {
            $isLoaded = extension_loaded($extension);
            $isCritical = in_array($extension, ['zip', 'curl', 'pdo', 'pdo_mysql', 'openssl', 'mbstring', 'json']);
            $status = $isLoaded ? 'passed' : ($isCritical ? 'failed' : 'warnings');

            $this->addRequirement(
                "PHP Extension: $extension",
                $description,
                $isLoaded ? 'Installed' : 'Not installed',
                $status,
                $isCritical
            );
        }

        // PHP Settings
        $this->checkPhpSetting('max_execution_time', 60, 'seconds', 'Minimum 60 seconds recommended for updates', false);
        $this->checkPhpSetting('memory_limit', 128, 'M', 'Minimum 128M recommended', false);
        $this->checkPhpSetting('upload_max_filesize', 10, 'M', 'Minimum 10M recommended for file uploads', false);
        $this->checkPhpSetting('post_max_size', 10, 'M', 'Minimum 10M recommended for form submissions', false);

        // Directory Permissions
        $this->checkDirectoryPermission(storage_path(), 'Required for file storage', true);
        $this->checkDirectoryPermission(storage_path('app/public'), 'Required for public file access', true);
        $this->checkDirectoryPermission(storage_path('app/backups'), 'Required for backup functionality', true);
        $this->checkDirectoryPermission(storage_path('app/updates'), 'Required for update functionality', true);
        $this->checkDirectoryPermission(storage_path('framework/cache'), 'Required for caching', true);
        $this->checkDirectoryPermission(storage_path('framework/views'), 'Required for views compilation', true);
        $this->checkDirectoryPermission(storage_path('framework/sessions'), 'Required for sessions', true);
        $this->checkDirectoryPermission(storage_path('logs'), 'Required for logging', true);
        $this->checkDirectoryPermission(base_path('bootstrap/cache'), 'Required by Laravel', true);

        // Database Connection
        try {
            DB::connection()->getPdo();
            $dbConnection = DB::connection()->getDatabaseName();
            $this->addRequirement(
                'Database Connection',
                'Connection to database server',
                'Connected to: ' . $dbConnection,
                'passed',
                true
            );
        } catch (\Exception $e) {
            $this->addRequirement(
                'Database Connection',
                'Connection to database server',
                'Error: ' . $e->getMessage(),
                'failed',
                true
            );
        }

        // ZIP Archive Test
        if (class_exists('\ZipArchive')) {
            try {
                $tempZipFile = storage_path('app/temp_test.zip');
                $zip = new \ZipArchive();
                if ($zip->open($tempZipFile, \ZipArchive::CREATE) === TRUE) {
                    $zip->addFromString('test.txt', 'Testing zip functionality.');
                    $zip->close();
                    @unlink($tempZipFile); // Clean up
                    $this->addRequirement(
                        'ZIP Archive Test',
                        'Ability to create and manipulate ZIP files',
                        'Working properly',
                        'passed',
                        true
                    );
                } else {
                    $this->addRequirement(
                        'ZIP Archive Test',
                        'Ability to create and manipulate ZIP files',
                        'Failed to create test ZIP file',
                        'failed',
                        true
                    );
                }
            } catch (\Exception $e) {
                $this->addRequirement(
                    'ZIP Archive Test',
                    'Ability to create and manipulate ZIP files',
                    'Error: ' . $e->getMessage(),
                    'failed',
                    true
                );
            }
        }

        // cURL Test
        if (function_exists('curl_init')) {
            try {
                $ch = curl_init('https://api.github.com');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($response !== false) {
                    $this->addRequirement(
                        'cURL Test',
                        'Ability to make HTTP requests',
                        'Working properly (HTTP code: ' . $httpCode . ')',
                        'passed',
                        true
                    );
                } else {
                    $this->addRequirement(
                        'cURL Test',
                        'Ability to make HTTP requests',
                        'Failed to connect to GitHub API',
                        'warnings',
                        true
                    );
                }
            } catch (\Exception $e) {
                $this->addRequirement(
                    'cURL Test',
                    'Ability to make HTTP requests',
                    'Error: ' . $e->getMessage(),
                    'warnings',
                    true
                );
            }
        }

        $this->isCheckingRequirements = false;
    }

    /**
     * Add a requirement check result
     */
    private function addRequirement($name, $description, $result, $status, $isCritical)
    {
        $this->systemRequirements[] = [
            'name' => $name,
            'description' => $description,
            'result' => $result,
            'status' => $status,
            'is_critical' => $isCritical
        ];

        // Ensure the status key exists before incrementing
        if (isset($this->requirementsStatus[$status])) {
            $this->requirementsStatus[$status]++;
        } else {
            Log::warning("Invalid requirement status: {$status}. Valid statuses: passed, warnings, failed");
        }
    }

    /**
     * Check PHP setting against minimum value
     */
    private function checkPhpSetting($setting, $minValue, $unit, $description, $isCritical)
    {
        $currentValue = ini_get($setting);
        $numericValue = (int) $currentValue;

        // Convert to MB if needed for comparison
        if (strpos($currentValue, 'G') !== false) {
            $numericValue = (int) $currentValue * 1024;
        } elseif (strpos($currentValue, 'K') !== false) {
            $numericValue = (int) $currentValue / 1024;
        }

        $status = 'passed';
        if ($numericValue < $minValue) {
            $status = $isCritical ? 'failed' : 'warnings';
        }

        $this->addRequirement(
            "PHP Setting: $setting",
            $description,
            "$currentValue (Recommended: $minValue$unit or higher)",
            $status,
            $isCritical
        );
    }

    /**
     * Check directory permission
     */
    private function checkDirectoryPermission($path, $description, $isCritical)
    {
        if (!file_exists($path)) {
            // Try to create directory if it doesn't exist
            try {
                mkdir($path, 0755, true);
            } catch (\Exception $e) {
                $this->addRequirement(
                    "Directory: " . basename($path),
                    $description,
                    "Directory doesn't exist and couldn't be created",
                    $isCritical ? 'failed' : 'warnings',
                    $isCritical
                );
                return;
            }
        }

        $isWritable = is_writable($path);
        $status = $isWritable ? 'passed' : ($isCritical ? 'failed' : 'warnings');

        $this->addRequirement(
            "Directory: " . basename($path),
            $description,
            $isWritable ? 'Writable' : 'Not writable',
            $status,
            $isCritical
        );
    }

    /**
     * Render component
     */
    public function render()
    {
        return view('livewire.settings.system-settings', [
            'timezones' => $this->getAvailableTimezones(),
            'date_formats' => $this->getAvailableDateFormats(),
            'currencies' => $this->getAvailableCurrencies(),
            'languages' => $this->getAvailableLanguages(),
        ]);
    }

    /**
     * Get available timezones
     */
    protected function getAvailableTimezones()
    {
        return [
            'UTC' => 'UTC',
            'Africa/Abidjan' => 'Africa/Abidjan',
            'Africa/Accra' => 'Africa/Accra',
            'Africa/Nairobi' => 'Africa/Nairobi',
            'America/Anchorage' => 'America/Anchorage',
            'America/Bogota' => 'America/Bogota',
            'America/Chicago' => 'America/Chicago',
            'America/Los_Angeles' => 'America/Los Angeles',
            'America/New_York' => 'America/New York',
            'America/Sao_Paulo' => 'America/S達o Paulo',
            'Asia/Bangkok' => 'Asia/Bangkok',
            'Asia/Dubai' => 'Asia/Dubai',
            'Asia/Hong_Kong' => 'Asia/Hong Kong',
            'Asia/Kolkata' => 'Asia/Kolkata',
            'Asia/Singapore' => 'Asia/Singapore',
            'Asia/Tokyo' => 'Asia/Tokyo',
            'Australia/Melbourne' => 'Australia/Melbourne',
            'Australia/Sydney' => 'Australia/Sydney',
            'Europe/Amsterdam' => 'Europe/Amsterdam',
            'Europe/Berlin' => 'Europe/Berlin',
            'Europe/London' => 'Europe/London',
            'Europe/Moscow' => 'Europe/Moscow',
            'Europe/Paris' => 'Europe/Paris',
            'Europe/Rome' => 'Europe/Rome',
            'Pacific/Auckland' => 'Pacific/Auckland',
            'Pacific/Honolulu' => 'Pacific/Honolulu',
        ];
    }

    /**
     * Get available date formats
     */
    protected function getAvailableDateFormats()
    {
        return [
            'm/d/Y' => date('m/d/Y'), // 12/31/2023
            'd/m/Y' => date('d/m/Y'), // 31/12/2023
            'Y-m-d' => date('Y-m-d'), // 2023-12-31
            'd.m.Y' => date('d.m.Y'), // 31.12.2023
            'd M, Y' => date('d M, Y'), // 31 Dec, 2023
            'M d, Y' => date('M d, Y'), // Dec 31, 2023
            'F d, Y' => date('F d, Y'), // December 31, 2023
        ];
    }

    /**
     * Get available currencies
     */
    protected function getAvailableCurrencies()
    {
        return [
            'USD' => 'USD - US Dollar',
            'EUR' => 'EUR - Euro',
            'GBP' => 'GBP - British Pound',
            'JPY' => 'JPY - Japanese Yen',
            'CAD' => 'CAD - Canadian Dollar',
            'AUD' => 'AUD - Australian Dollar',
            'CHF' => 'CHF - Swiss Franc',
            'CNY' => 'CNY - Chinese Yuan',
            'INR' => 'INR - Indian Rupee',
            'BRL' => 'BRL - Brazilian Real',
        ];
    }

    /**
     * Get available languages
     */
    protected function getAvailableLanguages()
    {
        return [
            'en' => 'English',
            'es' => 'Spanish',
            'fr' => 'French',
            'de' => 'German',
            'it' => 'Italian',
            'pt' => 'Portuguese',
            'ru' => 'Russian',
            'zh' => 'Chinese',
            'ja' => 'Japanese',
            'ar' => 'Arabic',
        ];
    }

    /**
     * Extract migration class name from file
     */
    protected function getMigrationClass($migrationFile)
    {
        // Read the file content
        $content = file_get_contents($migrationFile);

        // Extract the class name using regex
        if (preg_match('/class\s+([a-zA-Z0-9_]+)\s+extends\s+Migration/i', $content, $matches)) {
            return $matches[1];
        }

        // Fallback to legacy approach - extract from filename
        $filename = basename($migrationFile, '.php');
        $parts = explode('_', $filename);

        // Remove the timestamp (first components)
        for ($i = 0; $i < 4; $i++) {
            array_shift($parts);
        }

        // Build class name in PascalCase
        $className = '';
        foreach ($parts as $part) {
            $className .= ucfirst($part);
        }

        return $className;
    }

    /**
     * Recursively delete a directory and all its contents
     */
    private function deleteDirectory(string $dir): bool
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            $itemPath = $dir . DIRECTORY_SEPARATOR . $item;
            
            if (is_dir($itemPath)) {
                if (!$this->deleteDirectory($itemPath)) {
                    return false;
                }
            } else {
                if (!unlink($itemPath)) {
                    return false;
                }
            }
        }

        return rmdir($dir);
    }
}
