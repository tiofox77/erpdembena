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
    public $isCheckingForUpdates = false;
    public $isUpdating = false;

    // Maintenance & Debug Settings
    public $maintenance_mode = false;
    public $debug_mode = false;
    public $systemLogs = [];
    public $logTypes = ['all', 'info', 'warning', 'error', 'notice', 'debug'];
    public $selectedLogType = 'all';
    public $logLimit = 50;
    public $commandHistory = [];
    public $diskUsage = [];
    public $isLoadingLogs = false;
    public $isCheckingDiskSpace = false;

    // System Requirements
    public $systemRequirements = [];
    public $isCheckingRequirements = false;
    public $requirementsStatus = [
        'passed' => 0,
        'warnings' => 0,
        'failed' => 0
    ];
    
    // Database Analysis
    public $databaseInfo = null;
    public $databaseTables = [];
    public $databaseSize = 0;
    public $databaseStats = [];
    public $isAnalyzingDatabase = false;
    
    // Alpine.js state variables
    public $formState = null;
    public $activeSection = 'tools';
    public $auto_check_updates = false;

    // Modal states
    public $showConfirmModal = false;
    public $confirmAction = '';
    public $confirmMessage = '';
    public $confirmData = null;
    
    // Seeder modal
    public $showSeederModal = false;
    public $selectedSeeder = '';
    public $availableSeeders = [];
    public $runningSeeder = false;
    public $seederOutput = '';

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
        $this->loadSettings();
        $this->current_version = config('app.version', '1.0.0');

        // Acionar ações específicas baseadas na aba selecionada
        if ($this->activeTab === 'requirements') {
            $this->checkSystemRequirements();
        } else if ($this->activeTab === 'database') {
            $this->analyzeDatabaseSQL();
        } else if ($this->activeTab === 'maintenance') {
            $this->loadSystemLogs();
            $this->checkDiskSpace();
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
        
        $this->github_repository = Setting::get('github_repository', $this->github_repository);
        $this->maintenance_mode = (bool) Setting::get('maintenance_mode', false);
        $this->debug_mode = (bool) Setting::get('debug_mode', false);
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
        // Garantir que o tab seja um valor válido
        $validTabs = ['general', 'updates', 'maintenance', 'requirements', 'database'];
        
        if (in_array($tab, $validTabs)) {
            // Registrar a mudança para depuração
            Log::info("Mudando aba para: {$tab}");
            
            // Atualizar o valor
            $this->activeTab = $tab;
            
            // Acionar ações específicas baseadas na aba selecionada
            if ($tab === 'requirements') {
                $this->checkSystemRequirements();
            } else if ($tab === 'database') {
                $this->analyzeDatabaseSQL();
            }
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
     * Check for updates
     */
    public function checkForUpdates()
    {
        $this->isCheckingForUpdates = true;
        $this->update_status = 'Checking for updates...';

        try {
            // Fetch repository information first - this checks if the repo exists and is accessible
            $infoResponse = Http::get("https://api.github.com/repos/{$this->github_repository}");

            if (!$infoResponse->successful()) {
                $this->update_status = "Repository not found or not accessible. Status code: {$infoResponse->status()}";
                Log::error("GitHub Repository Error: {$infoResponse->body()}");
                $this->dispatch('notify', type: 'error', message: "Repository not found or not accessible. Make sure the repository exists and is public or has proper access tokens configured.");
                $this->isCheckingForUpdates = false;
                return;
            }

            // If repository exists, fetch releases
            $response = Http::get("https://api.github.com/repos/{$this->github_repository}/releases");

            if ($response->successful()) {
                $releases = $response->json();

                if (empty($releases)) {
                    $this->update_status = "No releases found in the repository.";
                    $this->dispatch('notify', type: 'warning', message: "No releases found in the repository. Please create releases with version tags.");
                    $this->isCheckingForUpdates = false;
                    return;
                }

                // Get the latest release
                $latest = $releases[0];
                $this->latest_version = ltrim($latest['tag_name'] ?? '0.0.0', 'v');

                // Compare versions
                if (version_compare($this->latest_version, $this->current_version, '>')) {
                    $this->update_available = true;
                    $this->update_notes = [
                        'title' => $latest['name'] ?? 'New Release',
                        'body' => $latest['body'] ?? 'No release notes available.',
                        'published_at' => $latest['published_at'] ?? now(),
                        'download_url' => $latest['zipball_url'] ?? null
                    ];
                    $this->update_status = "Update available: v{$this->latest_version}";
                    $this->dispatch('notify', type: 'info', message: "Update v{$this->latest_version} is available for installation.");
                } else {
                    $this->update_available = false;
                    $this->update_status = "You are running the latest version: v{$this->current_version}";
                    $this->dispatch('notify', type: 'success', message: "Your system is up to date (v{$this->current_version}).");
                }
            } else {
                $this->update_status = "Could not fetch releases. Status code: {$response->status()}";
                Log::error("GitHub API Error: {$response->body()}");
                $this->dispatch('notify', type: 'error', message: "Could not fetch releases from GitHub. Please check repository settings.");
            }
        } catch (\Exception $e) {
            $this->update_status = "Error checking for updates: " . $e->getMessage();
            Log::error("Update check error: {$e->getMessage()}");
            $this->dispatch('notify', type: 'error', message: "Error checking for updates: {$e->getMessage()}");
        }

        $this->isCheckingForUpdates = false;
    }

    /**
     * Show confirmation modal before starting update
     */
    public function confirmStartUpdate()
    {
        if (!$this->update_available) {
            $this->dispatch('notify', type: 'error', message: 'No updates available to install.');
            return;
        }

        $this->confirmAction = 'startUpdate';
        $this->confirmMessage = "Are you sure you want to update to version {$this->latest_version}? This action will temporarily make your site unavailable during the update process.";
        $this->showConfirmModal = true;
    }

    /**
     * Start the update process
     */
    public function startUpdate()
    {
        $this->showConfirmModal = false;

        if (!$this->update_available) {
            $this->dispatch('notify', type: 'error', message: 'No updates available to install.');
            return;
        }

        $this->isUpdating = true;
        $this->update_progress = 0;
        $this->update_status = 'Starting update process...';

        // Create a log file for this update
        $timestamp = date('Y-m-d_H-i-s');
        $logFile = storage_path("logs/update_{$timestamp}.log");
        $this->logToFile($logFile, "Starting update process to version {$this->latest_version}");
        $this->logToFile($logFile, "Current version: {$this->current_version}");

        try {
            // Create backup if option selected
            if ($this->backup_before_update) {
                $this->update_status = 'Creating backup...';
                $this->update_progress = 10;
                $backupPath = $this->createSimpleBackup();
                $this->logToFile($logFile, "Backup created at: $backupPath");
            }

            // Put application in maintenance mode
            $this->update_status = 'Putting application in maintenance mode...';
            $this->update_progress = 20;
            $this->enableMaintenanceMode();
            $this->logToFile($logFile, "Maintenance mode enabled");

            // Download the update
            $this->update_status = 'Downloading update...';
            $this->update_progress = 30;
            $update_file = $this->downloadUpdate($this->update_notes['download_url']);
            $this->logToFile($logFile, "Update package downloaded to: $update_file");

            // Extract the update
            $this->update_status = 'Extracting update files...';
            $this->update_progress = 50;
            $updatedFiles = $this->extractUpdate($update_file);

            // Handle case where updatedFiles might not be an array
            if (!is_array($updatedFiles)) {
                $updatedFiles = [];
            }

            // Run database migrations
            $this->update_status = 'Running database migrations...';
            $this->update_progress = 70;
            $migrationsResult = $this->runMigrations($logFile);

            if ($migrationsResult['success']) {
                $this->logToFile($logFile, "Database migrations completed successfully");
            } else {
                $this->logToFile($logFile, "Database migrations failed: " . $migrationsResult['error']);
            }

            // Update version in configuration
            $this->update_status = 'Finalizing update...';
            $this->update_progress = 90;

            // Ensure the version is updated in the database
            try {
                // Make sure we get the current version from the database, not from memory
                $oldVersion = Setting::get('app_version', config('app.version', '1.0.0'));
                $this->logToFile($logFile, "Retrieved current version from database: {$oldVersion}");

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

                $this->logToFile($logFile, "Version updated in database from {$oldVersion} to {$this->latest_version}");
                $this->logToFile($logFile, "Verified new version in database: {$newVersionInDb}");

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

            $this->logToFile($logFile, "System version updated to: {$this->latest_version}");

            // Clear caches
            $this->clearSettingsCache();
            $this->clearCaches();
            $this->logToFile($logFile, "Caches cleared");

            // Bring application back online
            $this->disableMaintenanceMode();
            $this->logToFile($logFile, "Maintenance mode disabled");

            $this->update_status = 'Update completed successfully!';
            $this->update_progress = 100;
            $this->logToFile($logFile, "Update process completed successfully");

            $this->dispatch('notify', type: 'success', message: "System has been updated to version {$this->latest_version}");
            Log::info("System updated to version {$this->latest_version}", [
                'log_file' => $logFile,
                'updated_files' => count($updatedFiles)
            ]);
        } catch (\Exception $e) {
            $this->update_status = "Update failed: {$e->getMessage()}";
            $this->logToFile($logFile, "Update failed: {$e->getMessage()}");
            $this->logToFile($logFile, "Error trace: {$e->getTraceAsString()}");
            Log::error("Update process error: {$e->getMessage()}", [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'log_file' => $logFile
            ]);

            // Ensure site comes back online even if update fails
            $this->disableMaintenanceMode();
            $this->logToFile($logFile, "Maintenance mode disabled after error");

            $this->dispatch('notify', type: 'error', message: "Update failed: {$e->getMessage()}");
        }

        $this->isUpdating = false;
    }

    /**
     * Log message to update log file
     */
    protected function logToFile($logFile, $message)
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message" . PHP_EOL;

        // Create directory if it doesn't exist
        $logDir = dirname($logFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }

        file_put_contents($logFile, $logMessage, FILE_APPEND);
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
        $startTime = microtime(true);
        $status = 'success';
        $output = '';

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
                'queue:work --once',
                'schedule:run',
                'key:generate',
                'route:cache',
                'view:cache',
                'config:cache',
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
            $status = 'error';
            $output = $e->getMessage();
            Log::error("Error executing Artisan command: {$e->getMessage()}");
            $this->dispatch('notify', type: 'error', message: "Failed to execute command: {$e->getMessage()}");
        }
        
        // Registrar no histórico de comandos
        $executionTime = round(microtime(true) - $startTime, 2);
        $this->addCommandToHistory($command, $status === 'success', $output, $executionTime);
        
        // Atualizar logs após executar um comando
        if (in_array($command, ['optimize:clear', 'cache:clear', 'config:clear', 'view:clear', 'route:clear'])) {
            // Verificar uso de disco após limpar caches
            $this->checkDiskSpace();
        }
    }
    
    /**
     * Carregar logs do sistema
     */
    public function loadSystemLogs()
    {
        $this->isLoadingLogs = true;
        $this->systemLogs = [];
        
        try {
            $logFile = storage_path('logs/laravel.log');
            
            if (file_exists($logFile)) {
                // Ler as últimas X linhas do arquivo de log
                $logs = $this->tailCustom($logFile, $this->logLimit * 10); // Pegamos mais linhas para filtrar depois
                
                // Processar linhas de log
                $parsedLogs = $this->parseLogLines($logs);
                
                // Filtrar por tipo se necessário
                if ($this->selectedLogType !== 'all') {
                    $parsedLogs = array_filter($parsedLogs, function($log) {
                        return strtolower($log['level']) === strtolower($this->selectedLogType);
                    });
                }
                
                // Limitar ao número definido em $logLimit
                $this->systemLogs = array_slice($parsedLogs, 0, $this->logLimit);
                
                $logCount = count($this->systemLogs);
                $message = $logCount > 0 
                    ? "Loaded {$logCount} system log entries successfully." 
                    : "No log entries found matching your criteria.";
                
                $this->dispatch('notify', 
                    type: 'success', 
                    message: $message
                );
            } else {
                $this->systemLogs = [];
                $this->dispatch('notify', 
                    type: 'info', 
                    message: 'Log file not found. The system may not have generated any logs yet.'
                );
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', 
                type: 'error', 
                message: 'Error loading system logs: ' . $e->getMessage()
            );
            
            // Log the exception
            \Illuminate\Support\Facades\Log::error('Error loading system logs', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        $this->isLoadingLogs = false;
    }
    
    /**
     * Clear the system log file
     */
    public function clearSystemLogs()
    {
        try {
            $logFile = storage_path('logs/laravel.log');
            
            if (file_exists($logFile)) {
                // Save a backup of the log file before clearing it
                $backupFile = storage_path('logs/laravel_' . date('Y-m-d_H-i-s') . '.log.backup');
                copy($logFile, $backupFile);
                
                // Clear the log file
                file_put_contents($logFile, '');
                
                // Reset the logs array
                $this->systemLogs = [];
                
                // Add a command to history
                $this->addToCommandHistory('clear:logs', true, 'Log file cleared successfully');
                
                $this->dispatch('notify', 
                    type: 'success', 
                    message: 'System logs have been cleared successfully. A backup was created.'
                );
            } else {
                $this->dispatch('notify', 
                    type: 'info', 
                    message: 'No log file found to clear.'
                );
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', 
                type: 'error', 
                message: 'Error clearing system logs: ' . $e->getMessage()
            );
            
            // Log the exception
            \Illuminate\Support\Facades\Log::error('Error clearing system logs', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Parse log file content into structured array
     *
     * @param string $logContent The log content to parse
     * @return array Parsed log entries
     */
    private function parseLogLines($logContent)
    {
        $pattern = '/\[(?<date>\d{4}-\d{2}-\d{2}[T\s]\d{2}:\d{2}:\d{2}\.?\d*)\]\s(?<env>\w+)\.(?<level>\w+):\s(?<message>.*)/m';
        $logs = [];
        
        // Split the log content into lines
        $lines = explode("\n", $logContent);
        
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            
            if (preg_match($pattern, $line, $matches)) {
                $logs[] = [
                    'date' => $matches['date'],
                    'environment' => $matches['env'],
                    'level' => strtolower($matches['level']),
                    'message' => $matches['message'],
                    'full_text' => $line
                ];
            } else {
                // Se não corresponder ao padrão, pode ser uma continuação da mensagem anterior
                if (!empty($logs)) {
                    $lastIndex = count($logs) - 1;
                    $logs[$lastIndex]['message'] .= "\n" . $line;
                    $logs[$lastIndex]['full_text'] .= "\n" . $line;
                }
            }
        }
        
        return $logs;
    }

    /**
     * Custom implementation of tail function to get last n lines of a file
     */
    private function tailCustom($filepath, $lines = 100, $adaptive = true) 
    {
        $f = @fopen($filepath, "rb");
        if ($f === false) return false;
        
        // Jump to the end of the file
        fseek($f, 0, SEEK_END);
        
        // Initial buffer size
        $buffer = ($adaptive) ? min(64, $lines) : $lines;
        
        // Start reading
        $output = '';
        $chunk = '';
        
        // While we need to get more lines
        while (ftell($f) > 0 && $lines > 0) {
            // Figure out how far back we need to jump
            $seek = min(ftell($f), 4096);
            
            // Jump backwards
            fseek($f, -$seek, SEEK_CUR);
            
            // Read a chunk
            $chunk = fread($f, $seek);
            
            // Jump back to where we started reading
            fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
            
            // Count the number of lines in the chunk
            $lines -= substr_count($chunk, "\n");
            
            // Add the chunk to our output
            $output = $chunk . $output;
            
            if ($lines <= 0) {
                // We have enough lines, break the chunk at the proper line
                $output = implode("\n", array_slice(explode("\n", $output), $lines));
                break;
            }
        }
        
        // Close the file
        fclose($f);
        
        return $output;
    }

    /**
     * Verificar uso de disco
     */
    public function checkDiskSpace()
    {
        $this->isCheckingDiskSpace = true;
        $this->diskUsage = [];
        
        try {
            // Tamanho total do diretório de armazenamento
            $storagePath = storage_path();
            $storageTotalSize = $this->getDirectorySize($storagePath);
            
            // Tamanho do diretório de logs
            $logsPath = storage_path('logs');
            $logsTotalSize = $this->getDirectorySize($logsPath);
            
            // Tamanho do diretório de cache
            $cachePath = storage_path('framework/cache');
            $cacheTotalSize = $this->getDirectorySize($cachePath);
            
            // Tamanho do diretório de sessões
            $sessionsPath = storage_path('framework/sessions');
            $sessionsTotalSize = $this->getDirectorySize($sessionsPath);
            
            // Tamanho do diretório de views compiladas
            $viewsPath = storage_path('framework/views');
            $viewsTotalSize = $this->getDirectorySize($viewsPath);
            
            // Tamanho do arquivo de log principal
            $mainLogFile = storage_path('logs/laravel.log');
            $mainLogSize = file_exists($mainLogFile) ? filesize($mainLogFile) : 0;
            
            // Espaço livre no disco
            $diskFreeSpace = disk_free_space(base_path());
            $diskTotalSpace = disk_total_space(base_path());
            
            $this->diskUsage = [
                'storage' => [
                    'path' => $storagePath,
                    'size' => $storageTotalSize,
                    'size_formatted' => $this->formatBytes($storageTotalSize)
                ],
                'logs' => [
                    'path' => $logsPath,
                    'size' => $logsTotalSize,
                    'size_formatted' => $this->formatBytes($logsTotalSize),
                    'main_log_size' => $mainLogSize,
                    'main_log_size_formatted' => $this->formatBytes($mainLogSize)
                ],
                'cache' => [
                    'path' => $cachePath,
                    'size' => $cacheTotalSize,
                    'size_formatted' => $this->formatBytes($cacheTotalSize)
                ],
                'sessions' => [
                    'path' => $sessionsPath,
                    'size' => $sessionsTotalSize,
                    'size_formatted' => $this->formatBytes($sessionsTotalSize)
                ],
                'views' => [
                    'path' => $viewsPath,
                    'size' => $viewsTotalSize,
                    'size_formatted' => $this->formatBytes($viewsTotalSize)
                ],
                'disk' => [
                    'free' => $diskFreeSpace,
                    'free_formatted' => $this->formatBytes($diskFreeSpace),
                    'total' => $diskTotalSpace,
                    'total_formatted' => $this->formatBytes($diskTotalSpace),
                    'usage_percent' => round(($diskTotalSpace - $diskFreeSpace) / $diskTotalSpace * 100, 2)
                ]
            ];
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: "Error checking disk space: {$e->getMessage()}");
        }
        
        $this->isCheckingDiskSpace = false;
    }

    /**
     * Get directory size recursively
     */
    private function getDirectorySize($path) 
    {
        $size = 0;
        $path = realpath($path);
        
        if ($path !== false && file_exists($path) && is_dir($path)) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)) as $file) {
                $size += $file->getSize();
            }
        }
        
        return $size;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2) 
    { 
        $units = ['B', 'KB', 'MB', 'GB', 'TB']; 
       
        $bytes = max($bytes, 0); 
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
        $pow = min($pow, count($units) - 1); 
       
        $bytes /= pow(1024, $pow);
       
        return round($bytes, $precision) . ' ' . $units[$pow]; 
    }
    
    /**
     * Limpar arquivo de log
     */
    public function clearLogFile()
    {
        try {
            $logFile = storage_path('logs/laravel.log');
            if (file_exists($logFile)) {
                file_put_contents($logFile, '');
                $this->dispatch('notify', type: 'success', message: 'Log file has been cleared successfully');
                $this->loadSystemLogs();
                $this->checkDiskSpace();
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: "Failed to clear log file: {$e->getMessage()}");
        }
    }

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
     * Delete directory recursively
     */
    protected function deleteDirectory($dir)
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

            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
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
    /**
     * Analyze database structure and size
     */
    public function analyzeDatabaseSQL()
    {
        $this->isAnalyzingDatabase = true;
        $connection = DB::connection();
        $databaseName = $connection->getDatabaseName();
        
        try {
            // Get basic database information
            $this->databaseInfo = [
                'name' => $databaseName,
                'driver' => $connection->getDriverName(),
                'version' => $connection->select('SELECT version() as version')[0]->version ?? 'Unknown',
                'charset' => config('database.connections.' . config('database.default') . '.charset'),
                'collation' => config('database.connections.' . config('database.default') . '.collation'),
            ];
            
            // List all tables and their sizes
            $this->databaseTables = [];
            $this->databaseSize = 0;
            $tablesWithIssues = 0;
            
            $tables = $connection->select('SHOW TABLE STATUS');
            
            foreach ($tables as $table) {
                // Calculate size in MB (Data + Indexes)
                $dataSize = ($table->Data_length ?? 0) / 1024 / 1024;
                $indexSize = ($table->Index_length ?? 0) / 1024 / 1024;
                $totalSize = $dataSize + $indexSize;
                $this->databaseSize += $totalSize;
                
                // Count records
                $recordCount = $connection->table($table->Name)->count();
                
                // Check if table is in good state
                $status = 'healthy';
                $issues = [];
                
                // Check tables that need optimization
                if (isset($table->Data_free) && $table->Data_free > 0) {
                    $fragmentationPercent = ($table->Data_free / ($table->Data_length + 0.001)) * 100;
                    if ($fragmentationPercent > 20) {
                        $status = 'warning';
                        $tablesWithIssues++;
                        $issues[] = __('messages.high_fragmentation') . ': ' . number_format($fragmentationPercent, 2) . '%';
                    }
                }
                
                // Check tables without primary keys (except junction tables)
                $hasPrimaryKey = false;
                $indexes = $connection->select("SHOW INDEXES FROM `{$table->Name}` WHERE Key_name = 'PRIMARY'");
                if (count($indexes) > 0) {
                    $hasPrimaryKey = true;
                } else if (strpos($table->Name, '_') !== false && strpos($table->Name, '_has_') === false) {
                    // Tables that are not junction tables should have primary key
                    $status = 'warning';
                    $tablesWithIssues++;
                    $issues[] = __('messages.no_primary_key');
                }
                
                // Add table information
                $this->databaseTables[] = [
                    'name' => $table->Name,
                    'engine' => $table->Engine,
                    'rows' => $recordCount,
                    'data_size' => $dataSize,
                    'index_size' => $indexSize,
                    'total_size' => $totalSize,
                    'formatted_size' => $this->formatBytes($totalSize * 1024 * 1024),
                    'created_at' => $table->Create_time,
                    'updated_at' => $table->Update_time,
                    'collation' => $table->Collation,
                    'status' => $status,
                    'issues' => $issues,
                    'has_primary_key' => $hasPrimaryKey,
                ];
            }
            
            // Sort tables by size (largest first)
            usort($this->databaseTables, function($a, $b) {
                return $b['total_size'] <=> $a['total_size'];
            });
            
            // Update database stats
            $this->databaseStats = [
                'tables' => count($this->databaseTables),
                'size' => $this->formatBytes($this->databaseSize * 1024 * 1024),
                'size_raw' => $this->databaseSize * 1024 * 1024,
                'tables_with_issues' => $tablesWithIssues
            ];
            
            $this->dispatch('notify', 
                type: 'success', 
                message: __('messages.database_analysis_completed')
            );
        } catch (\Exception $e) {
            $this->dispatch('notify', 
                type: 'error', 
                message: __('messages.database_analysis_error') . ': ' . $e->getMessage()
            );
        }
        
        $this->isAnalyzingDatabase = false;
    }
    
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
            $status = $isLoaded ? 'passed' : ($isCritical ? 'failed' : 'warning');

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
                        'warning',
                        true
                    );
                }
            } catch (\Exception $e) {
                $this->addRequirement(
                    'cURL Test',
                    'Ability to make HTTP requests',
                    'Error: ' . $e->getMessage(),
                    'warning',
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

        $this->requirementsStatus[$status]++;
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
            $status = $isCritical ? 'failed' : 'warning';
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
                    $isCritical ? 'failed' : 'warning',
                    $isCritical
                );
                return;
            }
        }

        $isWritable = is_writable($path);
        $status = $isWritable ? 'passed' : ($isCritical ? 'failed' : 'warning');

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
     * Open the database seeder modal
     */
    public function openSeederModal()
    {
        // Get available seeders
        $this->availableSeeders = [
            'DatabaseSeeder',
            'UserSeeder',
            'PermissionSeeder',
            'SettingsSeeder',
            'MaintenanceSeeder',
            'SupplyChainSeeder'
        ];
        
        $this->showSeederModal = true;
        $this->runningSeeder = false;
        $this->seederOutput = '';
        $this->selectedSeeder = '';
    }
    
    /**
     * Close the database seeder modal
     */
    public function closeSeederModal()
    {
        $this->showSeederModal = false;
        $this->runningSeeder = false;
        $this->selectedSeeder = '';
    }
    
    /**
     * Run the selected database seeder
     */
    public function runSeeder()
    {
        if (empty($this->selectedSeeder)) {
            $this->dispatch('notify', type: 'error', message: 'Please select a seeder first.');
            return;
        }
        
        $this->runningSeeder = true;
        $this->seederOutput = "Running seeder: {$this->selectedSeeder}...\n";
        
        try {
            // Make sure the seeder class is valid (basic validation)
            if (!preg_match('/^[A-Za-z0-9_]+$/', $this->selectedSeeder)) {
                throw new \Exception('Invalid seeder class name');
            }
            
            // Run the seeder
            Artisan::call('db:seed', [
                '--class' => $this->selectedSeeder
            ]);
            
            // Get output
            $output = Artisan::output();
            $this->seederOutput .= $output;
            
            $this->dispatch('notify', type: 'success', message: "Seeder {$this->selectedSeeder} executed successfully.");
        } catch (\Exception $e) {
            $this->seederOutput .= "Error: {$e->getMessage()}\n";
            $this->dispatch('notify', type: 'error', message: "Error running seeder: {$e->getMessage()}");
            Log::error("Seeder execution failed: {$e->getMessage()}", [
                'seeder' => $this->selectedSeeder,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        } finally {
            $this->runningSeeder = false;
        }
    }

}
