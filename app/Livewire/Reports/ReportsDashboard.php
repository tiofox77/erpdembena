<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\MaintenanceSchedule;
use App\Models\MaintenanceEquipment;

class ReportsDashboard extends Component
{
    public function render()
    {
        $totalEquipment = MaintenanceEquipment::count();
        $activeSchedules = MaintenanceSchedule::where('status', 'active')->count();
        $upcomingMaintenance = MaintenanceSchedule::where('status', 'active')
            ->where('next_maintenance_date', '>=', now())
            ->where('next_maintenance_date', '<=', now()->addDays(7))
            ->count();
        $overdueMaintenances = MaintenanceSchedule::where('status', 'active')
            ->where('next_maintenance_date', '<', now())
            ->count();

        return view('livewire.reports.reports-dashboard', [
            'totalEquipment' => $totalEquipment,
            'activeSchedules' => $activeSchedules,
            'upcomingMaintenance' => $upcomingMaintenance,
            'overdueMaintenances' => $overdueMaintenances,
        ]);
    }
}
