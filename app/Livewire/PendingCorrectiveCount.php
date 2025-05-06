<?php

namespace App\Livewire;

use App\Models\MaintenanceCorrective;
use Livewire\Component;

class PendingCorrectiveCount extends Component
{
    protected $listeners = [
        'correctiveMaintenanceUpdated' => '$refresh'
    ];

    public function render()
    {
        // Contagem de manutenções corretivas pendentes
        $count = MaintenanceCorrective::where('status', 'pending')->count();
        return view('livewire.pending-corrective-count', [
            'count' => $count
        ]);
    }
}
