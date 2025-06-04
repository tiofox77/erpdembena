<?php

namespace App\Livewire;

use App\Models\MaintenancePlan;
use Carbon\Carbon;
use Livewire\Component;

class MaintenanceCalendarWidget extends Component
{
    protected $listeners = [
        'maintenancePlanUpdated' => '$refresh'
    ];

    public function render()
    {
        // Buscar as próximas manutenções agendadas para os próximos 7 dias
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(7);
        
        $upcomingMaintenance = MaintenancePlan::where(function($query) use ($startDate, $endDate) {
                $query->whereDate('next_date', '>=', $startDate)
                      ->whereDate('next_date', '<=', $endDate);
            })
            ->whereIn('status', ['schedule', 'pending'])
            ->with(['equipment', 'assignedTo'])
            ->orderBy('next_date')
            ->take(5)
            ->get();
            
        return view('livewire.maintenance-calendar-widget', [
            'upcomingMaintenance' => $upcomingMaintenance
        ]);
    }
}
