<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\MaintenanceEquipment;
use App\Models\MaintenancePlan;
use Illuminate\Support\Facades\DB;

class MaintenanceDashboard extends Component
{
    public $equipmentCount;
    public $totalEquipment;
    public $equipmentInMaintenance;
    public $equipmentOutOfService;
    public $scheduledTasks;
    public $overdueTasks;
    public $completedTasks;
    public $maintenanceAlerts = [];

    public function mount()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        // Carregar contagens de equipamentos
        $this->equipmentCount = MaintenanceEquipment::count();
        $this->totalEquipment = $this->equipmentCount;
        $this->equipmentInMaintenance = MaintenanceEquipment::where('status', 'maintenance')->count();
        $this->equipmentOutOfService = MaintenanceEquipment::where('status', 'out_of_service')->count();

        // Carregar contagens de tarefas
        $this->scheduledTasks = MaintenancePlan::where('status', 'pending')->count();
        $this->overdueTasks = MaintenancePlan::where('status', ['pending', 'in_progress', 'delayed'])
            ->count();
        $this->completedTasks = MaintenancePlan::where('status', 'completed')->count();

        // Carregar alertas de manutenção
        $this->maintenanceAlerts = MaintenancePlan::where('status', ['pending', 'in_progress', 'delayed'])
            ->limit(5)
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'date' => $task->due_date,
                    'status' => $task->due_date < now() ? 'overdue' : 'upcoming',
                    'completed' => false
                ];
            })->toArray();
    }

    public function markAlertAsCompleted($alertId)
    {
        // Encontrar a tarefa no banco de dados
        $task = MaintenancePlan::find($alertId);
        if ($task) {
            // Atualizar status no banco de dados
            $task->update([
                'status' => 'completed',
                'completion_date' => now()
            ]);

            // Atualizar os alertas locais
            foreach ($this->maintenanceAlerts as $key => $alert) {
                if ($alert['id'] == $alertId) {
                    $this->maintenanceAlerts[$key]['completed'] = true;
                    break;
                }
            }

            // Atualizar contadores
            $this->overdueTasks--;
            $this->completedTasks++;

            session()->flash('message', 'Tarefa concluída com sucesso!');
        }
    }

    public function render()
    {
        return view('livewire.maintenance-dashboard', [
            'equipmentCount' => $this->equipmentCount,
            'totalEquipment' => $this->totalEquipment,
            'equipmentInMaintenance' => $this->equipmentInMaintenance,
            'equipmentOutOfService' => $this->equipmentOutOfService,
            'scheduledTasks' => $this->scheduledTasks,
            'overdueTasks' => $this->overdueTasks,
            'completedTasks' => $this->completedTasks,
            'maintenanceAlerts' => $this->maintenanceAlerts
        ]);
    }
}
