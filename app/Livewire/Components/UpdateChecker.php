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

    public function mount()
    {
        $this->currentVersion = config('app.version', '1.0.0');
        $this->getUpdateStatusFromCache();
    }

    /**
     * Get update status from cache
     */
    protected function getUpdateStatusFromCache()
    {
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
