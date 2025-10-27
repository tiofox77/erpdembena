<?php

namespace App\Livewire;

use App\Models\MaintenancePlan;
use Livewire\Component;

class ScheduledMaintenanceCount extends Component
{
    protected $listeners = [
        'maintenancePlanUpdated' => '$refresh'
    ];

    public function render()
    {
        // Contagem de planos de manutenção programados (status = schedule ou pending)
        $count = MaintenancePlan::whereIn('status', ['schedule', 'pending'])->count();
        return view('livewire.scheduled-maintenance-count', [
            'count' => $count
        ]);
    }
}
