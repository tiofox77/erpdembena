<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DirectUpdateController extends Controller
{
    /**
     * Apply a direct update package to the system.
     *
     * Expected JSON payload:
     * {
     *   "version": "1.3.9",
     *   "description": "Bug fixes and improvements",
     *   "files": [
     *     { "path": "app/Livewire/Settings/SystemSettings.php", "content": "<?php ...", "action": "update" },
     *     { "path": "app/Models/NewModel.php", "content": "<?php ...", "action": "create" },
     *     { "path": "old/unused/file.php", "action": "delete" }
     *   ],
     *   "migrations": true,
     *   "clear_cache": true,
     *   "seed": false,
     *   "commands": ["optimize:clear"]
     * }
     */
    public function apply(Request $request)
    {
        $startTime = microtime(true);
        $logs = [];
        $errors = [];
        $filesProcessed = 0;

        try {
            // Validate token
            $token = $request->bearerToken() ?? $request->header('X-Update-Token');
            $expectedToken = config('app.direct_update_token', env('DIRECT_UPDATE_TOKEN'));

            if (empty($expectedToken) || $token !== $expectedToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or missing update token.',
                ], 401);
            }

            // Validate payload
            $request->validate([
                'version' => 'nullable|string|max:20',
                'description' => 'nullable|string|max:500',
                'files' => 'nullable|array',
                'files.*.path' => 'required|string',
                'files.*.content' => 'nullable|string',
                'files.*.action' => 'required|in:create,update,delete',
                'migrations' => 'nullable|boolean',
                'clear_cache' => 'nullable|boolean',
                'seed' => 'nullable|boolean',
                'commands' => 'nullable|array',
                'commands.*' => 'string',
            ]);

            $version = $request->input('version');
            $description = $request->input('description', 'Direct update');
            $files = $request->input('files', []);
            $runMigrations = $request->boolean('migrations', false);
            $clearCache = $request->boolean('clear_cache', true);
            $seed = $request->boolean('seed', false);
            $commands = $request->input('commands', []);

            $logs[] = $this->log('info', "═══ Direct Update Started ═══");
            $logs[] = $this->log('info', "Description: {$description}");
            if ($version) {
                $logs[] = $this->log('info', "Target version: {$version}");
            }
            $logs[] = $this->log('info', "Files to process: " . count($files));

            // Save update state
            $this->saveState('in_progress', 0, 'Starting update...');

            // ── Step 1: Process files ──
            if (!empty($files)) {
                $logs[] = $this->log('info', "── Processing files ──");
                $this->saveState('in_progress', 10, 'Processing files...');

                foreach ($files as $index => $file) {
                    $relativePath = $file['path'];
                    $action = $file['action'];
                    $content = $file['content'] ?? null;

                    // Security: block paths trying to escape base
                    if (str_contains($relativePath, '..') || str_starts_with($relativePath, '/')) {
                        $errors[] = "Blocked unsafe path: {$relativePath}";
                        $logs[] = $this->log('error', "✗ Blocked unsafe path: {$relativePath}");
                        continue;
                    }

                    // Security: block sensitive files
                    $blockedFiles = ['.env', '.env.backup', 'artisan'];
                    if (in_array(basename($relativePath), $blockedFiles) && dirname($relativePath) === '.') {
                        $errors[] = "Blocked protected file: {$relativePath}";
                        $logs[] = $this->log('error', "✗ Blocked protected file: {$relativePath}");
                        continue;
                    }

                    $absolutePath = base_path($relativePath);

                    try {
                        switch ($action) {
                            case 'create':
                            case 'update':
                                if ($content === null) {
                                    $errors[] = "No content for {$action}: {$relativePath}";
                                    $logs[] = $this->log('error', "✗ No content provided for: {$relativePath}");
                                    break;
                                }
                                $dir = dirname($absolutePath);
                                if (!File::isDirectory($dir)) {
                                    File::makeDirectory($dir, 0755, true);
                                    $logs[] = $this->log('info', "  Created directory: " . str_replace(base_path(), '', $dir));
                                }
                                // Create backup of existing file
                                if (File::exists($absolutePath) && $action === 'update') {
                                    $backupDir = storage_path('app/update-backups/' . date('Y-m-d_H-i-s'));
                                    $backupPath = $backupDir . '/' . $relativePath;
                                    if (!File::isDirectory(dirname($backupPath))) {
                                        File::makeDirectory(dirname($backupPath), 0755, true);
                                    }
                                    File::copy($absolutePath, $backupPath);
                                }
                                File::put($absolutePath, $content);
                                $filesProcessed++;
                                $logs[] = $this->log('success', "✓ {$action}: {$relativePath}");
                                break;

                            case 'delete':
                                if (File::exists($absolutePath)) {
                                    // Create backup before deleting
                                    $backupDir = storage_path('app/update-backups/' . date('Y-m-d_H-i-s'));
                                    $backupPath = $backupDir . '/' . $relativePath;
                                    if (!File::isDirectory(dirname($backupPath))) {
                                        File::makeDirectory(dirname($backupPath), 0755, true);
                                    }
                                    File::copy($absolutePath, $backupPath);
                                    File::delete($absolutePath);
                                    $filesProcessed++;
                                    $logs[] = $this->log('success', "✓ deleted: {$relativePath}");
                                } else {
                                    $logs[] = $this->log('warning', "⚠ File not found for deletion: {$relativePath}");
                                }
                                break;
                        }
                    } catch (\Exception $e) {
                        $errors[] = "Error processing {$relativePath}: {$e->getMessage()}";
                        $logs[] = $this->log('error', "✗ Error on {$relativePath}: {$e->getMessage()}");
                    }

                    // Update progress proportionally
                    $progress = 10 + (int)(($index + 1) / count($files) * 40);
                    $this->saveState('in_progress', $progress, "Processing file " . ($index + 1) . "/" . count($files));
                }
            }

            // ── Step 2: Run migrations ──
            if ($runMigrations) {
                $logs[] = $this->log('info', "── Running migrations ──");
                $this->saveState('in_progress', 55, 'Running migrations...');

                try {
                    Artisan::call('migrate', ['--force' => true]);
                    $output = trim(Artisan::output());
                    if (!empty($output)) {
                        foreach (explode("\n", $output) as $line) {
                            if (!empty(trim($line))) {
                                $logs[] = $this->log('info', "  " . trim($line));
                            }
                        }
                    }
                    $logs[] = $this->log('success', "✓ Migrations completed");
                } catch (\Exception $e) {
                    $errors[] = "Migration error: {$e->getMessage()}";
                    $logs[] = $this->log('error', "✗ Migration error: {$e->getMessage()}");
                }
            }

            // ── Step 3: Run seeders ──
            if ($seed) {
                $logs[] = $this->log('info', "── Running seeders ──");
                $this->saveState('in_progress', 65, 'Running seeders...');

                try {
                    Artisan::call('db:seed', ['--force' => true]);
                    $logs[] = $this->log('success', "✓ Seeders completed");
                } catch (\Exception $e) {
                    $errors[] = "Seed error: {$e->getMessage()}";
                    $logs[] = $this->log('error', "✗ Seed error: {$e->getMessage()}");
                }
            }

            // ── Step 4: Run custom commands ──
            if (!empty($commands)) {
                $logs[] = $this->log('info', "── Running commands ──");
                $this->saveState('in_progress', 75, 'Running commands...');

                $allowedCommands = [
                    'optimize:clear', 'cache:clear', 'config:clear', 'config:cache',
                    'view:clear', 'view:cache', 'route:clear', 'route:cache',
                    'event:clear', 'event:cache', 'storage:link',
                    'migrate', 'migrate:fresh', 'db:seed',
                    'queue:restart', 'schedule:clear-cache',
                ];

                foreach ($commands as $command) {
                    $cmdName = explode(' ', $command)[0];
                    if (!in_array($cmdName, $allowedCommands)) {
                        $errors[] = "Blocked command: {$command}";
                        $logs[] = $this->log('error', "✗ Blocked command: {$command}");
                        continue;
                    }

                    try {
                        Artisan::call($command);
                        $output = trim(Artisan::output());
                        $logs[] = $this->log('success', "✓ Command: {$command}" . ($output ? " → {$output}" : ''));
                    } catch (\Exception $e) {
                        $errors[] = "Command '{$command}' error: {$e->getMessage()}";
                        $logs[] = $this->log('error', "✗ Command '{$command}': {$e->getMessage()}");
                    }
                }
            }

            // ── Step 5: Clear cache ──
            if ($clearCache) {
                $logs[] = $this->log('info', "── Clearing caches ──");
                $this->saveState('in_progress', 85, 'Clearing caches...');

                try {
                    Artisan::call('optimize:clear');
                    $logs[] = $this->log('success', "✓ All caches cleared");
                } catch (\Exception $e) {
                    $logs[] = $this->log('warning', "⚠ Cache clear warning: {$e->getMessage()}");
                }

                // Clear OPcache if available
                if (function_exists('opcache_reset')) {
                    opcache_reset();
                    $logs[] = $this->log('success', "✓ OPcache reset");
                }
            }

            // ── Step 6: Update version ──
            if ($version) {
                $logs[] = $this->log('info', "── Updating version ──");
                $this->saveState('in_progress', 95, 'Updating version...');

                try {
                    $oldVersion = Setting::get('app_version', config('app.version', '1.0.0'));

                    DB::beginTransaction();
                    DB::table('settings')->where('key', 'app_version')->delete();
                    DB::table('settings')->insert([
                        'key' => 'app_version',
                        'value' => $version,
                        'group' => 'updates',
                        'type' => 'string',
                        'description' => 'Current system version',
                        'is_public' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    DB::commit();

                    Setting::set('app_version', $version, 'updates', 'string', 'Current system version', true);
                    Setting::clearCache();
                    config(['app.version' => $version]);

                    $logs[] = $this->log('success', "✓ Version updated: {$oldVersion} → {$version}");
                } catch (\Exception $e) {
                    $errors[] = "Version update error: {$e->getMessage()}";
                    $logs[] = $this->log('error', "✗ Version update error: {$e->getMessage()}");
                }
            }

            // Log the update history
            $this->logUpdateHistory($version, $description, $filesProcessed, $errors);

            $duration = round(microtime(true) - $startTime, 2);
            $logs[] = $this->log('info', "═══ Update completed in {$duration}s ═══");
            $logs[] = $this->log('info', "Files processed: {$filesProcessed}, Errors: " . count($errors));

            $this->saveState('completed', 100, 'Update completed');

            $success = empty($errors);

            Log::info('Direct update applied', [
                'version' => $version,
                'files_processed' => $filesProcessed,
                'errors' => count($errors),
                'duration' => $duration,
            ]);

            return response()->json([
                'success' => $success,
                'message' => $success
                    ? "Update applied successfully. {$filesProcessed} file(s) processed in {$duration}s."
                    : "Update completed with " . count($errors) . " error(s).",
                'version' => $version,
                'files_processed' => $filesProcessed,
                'errors' => $errors,
                'logs' => $logs,
                'duration' => $duration,
            ], $success ? 200 : 207);

        } catch (\Exception $e) {
            $this->saveState('failed', 0, $e->getMessage());

            Log::error('Direct update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage(),
                'errors' => [$e->getMessage()],
                'logs' => $logs,
            ], 500);
        }
    }

    /**
     * Get current system status and update history.
     */
    public function status(Request $request)
    {
        $token = $request->bearerToken() ?? $request->header('X-Update-Token');
        $expectedToken = config('app.direct_update_token', env('DIRECT_UPDATE_TOKEN'));

        if (empty($expectedToken) || $token !== $expectedToken) {
            return response()->json(['success' => false, 'message' => 'Invalid token.'], 401);
        }

        $currentVersion = Setting::get('app_version', config('app.version', '1.0.0'));

        // Get recent update history
        $historyFile = storage_path('app/direct-update-history.json');
        $history = [];
        if (File::exists($historyFile)) {
            $history = json_decode(File::get($historyFile), true) ?? [];
            $history = array_slice($history, -20); // Last 20 updates
        }

        return response()->json([
            'success' => true,
            'version' => $currentVersion,
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
            'debug' => config('app.debug'),
            'maintenance' => app()->isDownForMaintenance(),
            'history' => $history,
        ]);
    }

    /**
     * Rollback the last direct update using backups.
     */
    public function rollback(Request $request)
    {
        $token = $request->bearerToken() ?? $request->header('X-Update-Token');
        $expectedToken = config('app.direct_update_token', env('DIRECT_UPDATE_TOKEN'));

        if (empty($expectedToken) || $token !== $expectedToken) {
            return response()->json(['success' => false, 'message' => 'Invalid token.'], 401);
        }

        $logs = [];
        $backupDir = storage_path('app/update-backups');

        if (!File::isDirectory($backupDir)) {
            return response()->json([
                'success' => false,
                'message' => 'No backups found.',
            ], 404);
        }

        // Get the most recent backup folder
        $backupFolders = collect(File::directories($backupDir))->sort()->reverse();

        if ($backupFolders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No backup folders found.',
            ], 404);
        }

        $latestBackup = $backupFolders->first();
        $logs[] = $this->log('info', "Rolling back from: " . basename($latestBackup));

        try {
            $restoredFiles = 0;
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($latestBackup, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($iterator as $file) {
                $relativePath = str_replace($latestBackup . DIRECTORY_SEPARATOR, '', $file->getPathname());
                $targetPath = base_path($relativePath);

                $dir = dirname($targetPath);
                if (!File::isDirectory($dir)) {
                    File::makeDirectory($dir, 0755, true);
                }

                File::copy($file->getPathname(), $targetPath);
                $restoredFiles++;
                $logs[] = $this->log('success', "✓ Restored: {$relativePath}");
            }

            // Clear caches after rollback
            try {
                Artisan::call('optimize:clear');
                $logs[] = $this->log('success', "✓ Caches cleared after rollback");
            } catch (\Exception $e) {
                $logs[] = $this->log('warning', "⚠ Cache clear warning: {$e->getMessage()}");
            }

            // Delete the used backup
            File::deleteDirectory($latestBackup);
            $logs[] = $this->log('info', "Backup folder cleaned up");

            return response()->json([
                'success' => true,
                'message' => "Rollback completed. {$restoredFiles} file(s) restored.",
                'files_restored' => $restoredFiles,
                'logs' => $logs,
            ]);

        } catch (\Exception $e) {
            Log::error('Rollback failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Rollback failed: ' . $e->getMessage(),
                'logs' => $logs,
            ], 500);
        }
    }

    /**
     * Helper: save update state to file for real-time reading.
     */
    private function saveState(string $status, int $progress, string $message): void
    {
        $stateFile = storage_path('app/update_state.json');
        $state = [
            'status' => $message,
            'progress' => $progress,
            'step' => $status,
            'is_updating' => $status === 'in_progress',
            'updated_at' => now()->toIso8601String(),
        ];
        File::put($stateFile, json_encode($state, JSON_PRETTY_PRINT));
    }

    /**
     * Helper: create a log entry.
     */
    private function log(string $type, string $message): array
    {
        $entry = [
            'timestamp' => now()->format('H:i:s'),
            'type' => $type,
            'message' => $message,
        ];

        Log::channel('single')->info("[DirectUpdate] [{$type}] {$message}");

        return $entry;
    }

    /**
     * Helper: store update in history file.
     */
    private function logUpdateHistory(?string $version, string $description, int $filesProcessed, array $errors): void
    {
        $historyFile = storage_path('app/direct-update-history.json');
        $history = [];

        if (File::exists($historyFile)) {
            $history = json_decode(File::get($historyFile), true) ?? [];
        }

        $history[] = [
            'version' => $version,
            'description' => $description,
            'files_processed' => $filesProcessed,
            'errors_count' => count($errors),
            'errors' => array_slice($errors, 0, 10),
            'applied_at' => now()->toIso8601String(),
            'ip' => request()->ip(),
        ];

        // Keep only last 50 entries
        if (count($history) > 50) {
            $history = array_slice($history, -50);
        }

        File::put($historyFile, json_encode($history, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
