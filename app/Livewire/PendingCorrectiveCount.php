<?php

namespace App\Livewire;

use App\Models\MaintenanceCorrective;
use Livewire\Component;
use Livewire\Attributes\On;

class PendingCorrectiveCount extends Component
{
    #[On('correctiveMaintenanceUpdated')]
    public function refresh(): void
    {
        // Refresh is handled automatically
    }

    public function render()
    {
        // Contagem de manutenções corretivas pendentes
        $count = MaintenanceCorrective::where('status', 'pending')->count();
        return view('livewire.pending-corrective-count', [
            'count' => $count
        ]);
    }
}
