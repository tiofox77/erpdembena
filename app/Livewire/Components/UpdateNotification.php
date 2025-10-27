<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Illuminate\Support\Facades\Cache;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class UpdateNotification extends Component
{
    public $showModal = false;
    public $currentVersion;
    public $latestVersion;
    public $updateAvailable = false;
    public $updateNotes = [];

    protected function getListeners()
    {
        return [
            'openUpdateModal' => 'openModal'
        ];
    }

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
            Log::info("Update notification using version: {$this->currentVersion}", [
                'source' => !empty($dbVersion) ? 'database' : 'config'
            ]);
        } catch (\Exception $e) {
            // If database error, use config version
            $this->currentVersion = config('app.version', '1.0.0');
            Log::warning("Error getting version from database, using config: {$this->currentVersion}");
        }

        // Verifica se há atualização no cache
        if (Cache::has('update_status')) {
            $status = Cache::get('update_status');
            $this->updateAvailable = $status['available'] ?? false;
            $this->latestVersion = $status['latest_version'] ?? null;

            // Mostra o modal automaticamente apenas na primeira vez que detectar a atualização
            // e apenas se o usuário não tiver dispensado o aviso para esta versão
            $dismissedVersion = Setting::get('dismissed_update_version', '');

            if ($this->updateAvailable && $dismissedVersion !== $this->latestVersion) {
                // Modaliza com um pequeno atraso para não bloquear a renderização da página
                $this->dispatch('showUpdateModal');
            }
        }
    }

    /**
     * Buscar notas da atualização quando o modal for aberto
     */
    public function getUpdateNotes()
    {
        try {
            $repository = Setting::get('github_repository', 'tiofox77/erpdembena');
            $response = \Illuminate\Support\Facades\Http::get("https://api.github.com/repos/{$repository}/releases/latest");

            if ($response->successful()) {
                $data = $response->json();
                $this->updateNotes = [
                    'title' => $data['name'] ?? 'Nova versão',
                    'body' => $data['body'] ?? 'Detalhes não disponíveis',
                    'published_at' => \Carbon\Carbon::parse($data['published_at'] ?? now())->format('d/m/Y H:i'),
                ];
            }
        } catch (\Exception $e) {
            // Em caso de erro, exibe mensagem genérica
            $this->updateNotes = [
                'title' => 'Nova versão disponível',
                'body' => 'Detalhes não disponíveis no momento.',
                'published_at' => now()->format('d/m/Y H:i')
            ];
        }
    }

    /**
     * Abre o modal de atualização
     */
    public function openModal()
    {
        $this->getUpdateNotes();
        $this->showModal = true;
    }

    /**
     * Fecha o modal e marca como visto
     */
    public function closeModal()
    {
        $this->showModal = false;
    }

    /**
     * Dispensar este aviso para a versão atual
     */
    public function dismissUpdate()
    {
        Setting::set('dismissed_update_version', $this->latestVersion);
        $this->closeModal();
    }

    /**
     * Ir para a página de atualização
     */
    public function goToUpdatePage()
    {
        return redirect()->route('settings.system', ['activeTab' => 'updates']);
    }

    public function render()
    {
        return view('livewire.components.update-notification');
    }
}
