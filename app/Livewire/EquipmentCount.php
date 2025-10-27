<?php

namespace App\Livewire;

use App\Models\MaintenanceEquipment;
use Livewire\Component;

class EquipmentCount extends Component
{
    protected $listeners = [
        'equipmentUpdated' => '$refresh'
    ];

    public function render()
    {
        $count = MaintenanceEquipment::count();
        return view('livewire.equipment-count', [
            'count' => $count
        ]);
    }
}
