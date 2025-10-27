<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationsPanel extends Component
{
    protected $listeners = ['verifyNotifications' => 'markAllAsRead'];
    public $notifications = [];
    
    public function mount()
    {
        $this->loadNotifications();
    }
    
    public function loadNotifications()
    {
        // Carregar notificações do usuário atual (não lidas)
        $this->notifications = Notification::where('user_id', Auth::id())
            ->where('read', false)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }
    
    /**
     * Marca todas as notificações como lidas
     */
    public function markAllAsRead()
    {
        try {
            // Atualizar todas as notificações não lidas do usuário atual
            Notification::where('user_id', Auth::id())
                ->where('read', false)
                ->update(['read' => true]);
            
            // Recarregar notificações
            $this->loadNotifications();
            
            // Emitir evento de sucesso
            $this->dispatch('toast', [
                'type' => 'success',
                'title' => trans('messages.success'),
                'message' => trans('messages.notifications_marked_as_read')
            ]);
            
        } catch (\Exception $e) {
            // Emitir evento de erro
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => trans('messages.error'),
                'message' => trans('messages.could_not_process_request') . ': ' . $e->getMessage()
            ]);
        }
    }
    
    public function render()
    {
        return view('livewire.notifications-panel');
    }
}
