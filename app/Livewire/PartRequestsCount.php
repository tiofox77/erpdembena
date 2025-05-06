<?php

namespace App\Livewire;

use App\Models\EquipmentPartRequest;
use Livewire\Component;

class PartRequestsCount extends Component
{
    protected $listeners = [
        'partRequestUpdated' => '$refresh'
    ];

    public function render()
    {
        // Contagem de requisições de peças pendentes
        $count = EquipmentPartRequest::where('status', 'pending')->count();
        return view('livewire.part-requests-count', [
            'count' => $count
        ]);
    }
}
