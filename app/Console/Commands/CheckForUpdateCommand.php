<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class CheckForUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-for-updates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica se há atualizações disponíveis para o sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verificando por atualizações...');

        try {
            // Obtém configurações
            $currentVersion = config('app.version', '1.0.0');
            $repository = Setting::get('github_repository', 'tiofox77/erpdembena');

            if (empty($repository)) {
                $this->error('Repositório GitHub não está configurado nas configurações do sistema.');
                Log::warning('Update checker: GitHub repository not configured in settings');
                return 1;
            }

            // Busca a versão mais recente no GitHub
            $response = Http::get("https://api.github.com/repos/{$repository}/releases/latest");

            if ($response->successful()) {
                $releaseData = $response->json();
                $latestVersion = ltrim($releaseData['tag_name'] ?? '', 'v');

                // Compara versões
                $updateAvailable = version_compare($latestVersion, $currentVersion, '>');

                // Armazena status da atualização em cache
                Cache::put('update_status', [
                    'available' => $updateAvailable,
                    'latest_version' => $latestVersion,
                    'checked_at' => now()->toDateTimeString(),
                ], now()->addHours(12)); // Cache por 12 horas

                // Exibe informações no console
                if ($updateAvailable) {
                    $this->info("Atualização disponível: versão {$latestVersion} (atual: {$currentVersion})");
                    Log::info("Update found: {$currentVersion} -> {$latestVersion}");
                } else {
                    $this->info("Sistema atualizado: versão {$currentVersion}");
                    Log::info("System is up to date: {$currentVersion}");
                }

                return 0;
            } else {
                $this->error('Falha ao verificar atualizações: ' . $response->status());
                Log::error('Failed to check for updates', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('Erro ao verificar atualizações: ' . $e->getMessage());
            Log::error('Error checking for updates: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return 1;
        }
    }
}
