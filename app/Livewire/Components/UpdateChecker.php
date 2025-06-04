<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class UpdateChecker extends Component
{
    public $updateAvailable = false;
    public $currentVersion;
    public $latestVersion;
    public $checkingForUpdates = false;
    public $lastChecked = null;

    /**
     * Verifica se o usuário atual tem permissão para ver atualizações
     */
    private function userCanSeeUpdates()
    {
        // Verifica se o usuário está logado
        if (!auth()->check()) {
            return false;
        }

        // Verifica se o usuário tem role admin ou super-admin
        $user = auth()->user();

        // Para sistemas com Spatie Permission
        if (method_exists($user, 'hasRole')) {
            return $user->hasRole(['admin', 'super-admin']);
        }

        // Para sistemas com campo role na tabela users
        if (isset($user->role)) {
            return in_array($user->role, ['admin', 'super-admin']);
        }

        return false;
    }

    public function mount()
    {
        // Sai da função se o usuário não tem permissão para ver atualizações
        if (!$this->userCanSeeUpdates()) {
            $this->updateAvailable = false;
            return;
        }

        // First try to get version from database, then fall back to config
        try {
            $dbVersion = Setting::get('app_version');
            $this->currentVersion = !empty($dbVersion) ? $dbVersion : config('app.version', '1.0.0');

            // Log the version being used
            Log::info("Update checker using version: {$this->currentVersion}", [
                'source' => !empty($dbVersion) ? 'database' : 'config'
            ]);
        } catch (\Exception $e) {
            // If database error, use config version
            $this->currentVersion = config('app.version', '1.0.0');
            Log::warning("Error getting version from database, using config: {$this->currentVersion}");
        }

        $this->getUpdateStatusFromCache();
    }

    /**
     * Get update status from cache
     */
    protected function getUpdateStatusFromCache()
    {
        // Se o usuário não tem permissão, não verifica atualizações
        if (!$this->userCanSeeUpdates()) {
            return;
        }

        // Check if we have cached update status
        if (Cache::has('update_status')) {
            $status = Cache::get('update_status');
            $this->updateAvailable = $status['available'] ?? false;
            $this->latestVersion = $status['latest_version'] ?? null;
            $this->lastChecked = $status['checked_at'] ?? null;
        } else {
            // If no cache, do a background check
            $this->checkForUpdates(true);
        }
    }

    /**
     * Check for updates in background or foreground
     */
    public function checkForUpdates($background = false)
    {
        // Se o usuário não tem permissão, não verifica atualizações
        if (!$this->userCanSeeUpdates()) {
            return;
        }

        if (!$background) {
            $this->checkingForUpdates = true;
        }

        try {
            // Get GitHub repository from settings
            $repository = Setting::get('github_repository', 'tiofox77/erpdembena');

            if (empty($repository)) {
                Log::warning('Update checker: GitHub repository not configured in settings');
                return;
            }

            // Fetch latest release info from GitHub
            $response = Http::get("https://api.github.com/repos/{$repository}/releases/latest");

            if ($response->successful()) {
                $releaseData = $response->json();
                $this->latestVersion = ltrim($releaseData['tag_name'] ?? '', 'v');

                // Compare versions
                $this->updateAvailable = version_compare($this->latestVersion, $this->currentVersion, '>');

                // Store update status in cache
                Cache::put('update_status', [
                    'available' => $this->updateAvailable,
                    'latest_version' => $this->latestVersion,
                    'checked_at' => now()->toDateTimeString(),
                ], now()->addHours(12)); // Cache for 12 hours

                $this->lastChecked = now()->toDateTimeString();

                Log::info('Update check completed', [
                    'current_version' => $this->currentVersion,
                    'latest_version' => $this->latestVersion,
                    'update_available' => $this->updateAvailable,
                ]);
            } else {
                Log::error('Failed to check for updates', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error checking for updates: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
        } finally {
            $this->checkingForUpdates = false;
        }
    }

    /**
     * Go to update page
     */
    public function goToUpdatePage()
    {
        return redirect()->route('settings.system', ['activeTab' => 'updates']);
    }

    public function render()
    {
        return view('livewire.components.update-checker');
    }
}
