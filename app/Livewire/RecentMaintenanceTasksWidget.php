<?php

namespace App\Livewire;

use App\Models\MaintenanceTaskLog;
use Livewire\Component;

class RecentMaintenanceTasksWidget extends Component
{
    protected $listeners = [
        'taskLogUpdated' => '$refresh'
    ];

    public function render()
    {
        // Buscar as tarefas de manutenção mais recentes
        $recentTasks = MaintenanceTaskLog::with(['equipment', 'user'])
            ->orderByDesc('created_at')
            ->take(5)
            ->get();
            
        return view('livewire.recent-maintenance-tasks-widget', [
            'recentTasks' => $recentTasks
        ]);
    }
}
