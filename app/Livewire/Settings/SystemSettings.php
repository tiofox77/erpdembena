<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Livewire\WithFileUploads;
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
        ];
    }

    /**
     * Initialize component data
     */
    public function mount()
    {
        $this->loadSettings();
        $this->current_version = config('app.version', '1.0.0');
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
        $this->activeTab = $tab;
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
        ]);

        try {
            Log::info('Saving general settings', [
                'company_name' => $this->company_name,
                'app_timezone' => $this->app_timezone,
                'date_format' => $this->date_format,
                'currency' => $this->currency,
                'language' => $this->language,
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
            // Fetch releases from GitHub API
            $response = Http::get("https://api.github.com/repos/{$this->github_repository}/releases/latest");

            if ($response->successful()) {
                $latest = $response->json();
                $this->latest_version = ltrim($latest['tag_name'], 'v');

                // Compare versions
                if (version_compare($this->latest_version, $this->current_version, '>')) {
                    $this->update_available = true;
                    $this->update_notes = [
                        'title' => $latest['name'],
                        'body' => $latest['body'],
                        'published_at' => $latest['published_at'],
                        'download_url' => $latest['zipball_url']
                    ];
                    $this->update_status = "Update available: v{$this->latest_version}";
                    $this->dispatch('notify', type: 'info', message: "Update v{$this->latest_version} is available for installation.");
                } else {
                    $this->update_available = false;
                    $this->update_status = "You are running the latest version: v{$this->current_version}";
                    $this->dispatch('notify', type: 'success', message: "Your system is up to date (v{$this->current_version}).");
                }
            } else {
                $this->update_status = "Could not connect to GitHub. Status code: {$response->status()}";
                Log::error("GitHub API Error: {$response->body()}");
                $this->dispatch('notify', type: 'error', message: "Could not connect to GitHub. Please check your repository settings.");
            }
        } catch (\Exception $e) {
            $this->update_status = "Error checking for updates";
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

        try {
            // Create backup if option selected
            if ($this->backup_before_update) {
                $this->update_status = 'Creating backup...';
                $this->update_progress = 10;
                Artisan::call('backup:run');
            }

            // Put application in maintenance mode
            $this->update_status = 'Putting application in maintenance mode...';
            $this->update_progress = 20;
            Artisan::call('down');

            // Download the update
            $this->update_status = 'Downloading update...';
            $this->update_progress = 30;
            $update_file = $this->downloadUpdate($this->update_notes['download_url']);

            // Extract the update
            $this->update_status = 'Extracting update files...';
            $this->update_progress = 50;
            $this->extractUpdate($update_file);

            // Update dependencies
            $this->update_status = 'Updating dependencies...';
            $this->update_progress = 70;
            $this->runComposerUpdate();

            // Run migrations
            $this->update_status = 'Running database migrations...';
            $this->update_progress = 80;
            Artisan::call('migrate', ['--force' => true]);

            // Update version in configuration
            $this->update_status = 'Finalizing update...';
            $this->update_progress = 90;
            Setting::set('app_version', $this->latest_version, 'updates', 'string', 'Current system version', true);
            $this->current_version = $this->latest_version;
            $this->update_available = false;

            // Clear caches
            $this->clearSettingsCache();
            Artisan::call('optimize:clear');

            // Bring application back online
            Artisan::call('up');

            $this->update_status = 'Update completed successfully!';
            $this->update_progress = 100;

            $this->dispatch('notify', type: 'success', message: "System has been updated to version {$this->latest_version}");
        } catch (\Exception $e) {
            $this->update_status = "Update failed: {$e->getMessage()}";
            Log::error("Update process error: {$e->getMessage()}");

            // Ensure site comes back online even if update fails
            Artisan::call('up');

            $this->dispatch('notify', type: 'error', message: "Update failed: {$e->getMessage()}");
        }

        $this->isUpdating = false;
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

        // Download the update
        $response = Http::withOptions(['sink' => $zipPath])->get($url);

        if (!$response->successful() || !file_exists($zipPath)) {
            throw new \Exception('Failed to download update package');
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

        // Clear the extract directory
        if (file_exists($extractPath)) {
            $this->deleteDirectory($extractPath);
        }
        mkdir($extractPath, 0755, true);

        if ($zip->open($zipFile) === true) {
            $zip->extractTo($extractPath);
            $zip->close();

            // Move the extracted files to the correct locations
            $this->copyDirectory($extractPath, base_path());

            return true;
        } else {
            throw new \Exception('Failed to extract update package');
        }
    }

    /**
     * Run composer update
     */
    protected function runComposerUpdate()
    {
        // Run composer update in the background
        $composerPath = exec('which composer');
        if (!$composerPath) {
            throw new \Exception('Composer not found. Please make sure it is installed and available in the PATH.');
        }

        exec('cd ' . base_path() . ' && composer install --no-dev --optimize-autoloader --no-interaction 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            Log::error('Composer update failed: ' . implode("\n", $output));
            throw new \Exception('Failed to update dependencies');
        }

        return true;
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
     * Copy directory recursively
     */
    protected function copyDirectory($source, $destination)
    {
        if (!is_dir($source)) {
            return false;
        }

        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $dir = opendir($source);
        while (($file = readdir($dir)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $srcFile = $source . '/' . $file;
            $destFile = $destination . '/' . $file;

            if (is_dir($srcFile)) {
                $this->copyDirectory($srcFile, $destFile);
            } else {
                copy($srcFile, $destFile);
            }
        }
        closedir($dir);

        return true;
    }

    /**
     * Clear settings cache
     */
    public function clearSettingsCache()
    {
        Setting::clearCache();
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
            'America/Sao_Paulo' => 'America/São Paulo',
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
}