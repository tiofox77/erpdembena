<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\MaintenancePlan;
use Livewire\Component;

class TechniciansAvailabilityWidget extends Component
{
    protected $listeners = [
        'technicianUpdated' => '$refresh',
        'userUpdated' => '$refresh'
    ];

    public function render()
    {
        // Buscar usuários com papel de técnico
        // Assumindo que há uma coluna role ou um relacionamento roles
        // Adapte esta lógica conforme a estrutura real do seu banco de dados
        $users = User::where(function($query) {
                $query->where('role', 'technician')
                      ->orWhere('department', 'maintenance');
            })
            ->withCount(['assignedMaintenancePlans' => function($query) {
                $query->whereIn('status', ['pending', 'in_progress', 'schedule']);
            }])
            ->orderBy('assigned_maintenance_plans_count')
            ->take(5)
            ->get();
            
        // Como fallback, se não conseguirmos buscar por papel, buscaremos todos os usuários
        // que têm planos de manutenção atribuídos a eles
        if ($users->isEmpty()) {
            $userIds = MaintenancePlan::whereNotNull('assigned_to')
                ->distinct('assigned_to')
                ->pluck('assigned_to');
                
            $users = User::whereIn('id', $userIds)
                ->withCount(['assignedMaintenancePlans' => function($query) {
                    $query->whereIn('status', ['pending', 'in_progress', 'schedule']);
                }])
                ->orderBy('assigned_maintenance_plans_count')
                ->take(5)
                ->get();
        }
            
        return view('livewire.technicians-availability-widget', [
            'technicians' => $users
        ]);
    }
}
