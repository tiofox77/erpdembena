<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\MaintenanceEquipment;
use App\Models\MaintenancePlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        // Verificar se a coluna 'status' existe antes de tentar usá-la
        if (Schema::hasColumn('maintenance_equipment', 'status')) {
            // Equipamento fora de serviço
            $this->equipmentOutOfService = MaintenanceEquipment::where('status', 'out_of_service')->count();

            // Equipamentos em manutenção - buscar em maintenance_plans os equipamentos com planos ativos
            $this->equipmentInMaintenance = MaintenancePlan::whereIn('status', ['pending', 'in_progress'])
                ->whereNotNull('equipment_id')
                ->distinct('equipment_id')
                ->count('equipment_id');
        } else {
            // Se a coluna não existe, definir valores padrão
            $this->equipmentOutOfService = 0;

            // Mesmo sem coluna status, podemos buscar equipamentos com manutenção planejada
            $this->equipmentInMaintenance = MaintenancePlan::whereIn('status', ['pending', 'in_progress'])
                ->whereNotNull('equipment_id')
                ->distinct('equipment_id')
                ->count('equipment_id');
        }

        // Carregar contagens de tarefas
        // Total de planos de manutenção (todos os planos)
        $this->scheduledTasks = MaintenancePlan::count();

        // Tarefas não atualizadas/vencidas (tarefas pendentes com data programada no passado)
        // Verificar primeiro se a coluna scheduled_date existe
        if (Schema::hasColumn('maintenance_plans', 'scheduled_date')) {
            $this->overdueTasks = MaintenancePlan::where('status', 'pending')
                ->where('scheduled_date', '<', now()->format('Y-m-d'))
                ->count();
        } else {
            // Se não tiver data, simplesmente contar as tarefas pendentes
            $this->overdueTasks = MaintenancePlan::where('status', 'pending')->count();
        }

        $this->completedTasks = MaintenancePlan::where('status', 'completed')->count();

        // Carregar alertas de manutenção
        $this->maintenanceAlerts = MaintenancePlan::whereIn('status', ['pending', 'in_progress'])
            ->limit(5)
            ->get()
            ->map(function ($task) {
                // Verificar se tem scheduled_date e usá-la para determinar status
                $isOverdue = false;
                if (isset($task->scheduled_date)) {
                    $isOverdue = \Carbon\Carbon::parse($task->scheduled_date)->isPast();
                }

                return [
                    'id' => $task->id,
                    'title' => $task->description ?? ('Task #' . $task->id),
                    'description' => $task->notes ?? 'No description available',
                    'date' => $task->scheduled_date ?? 'No date set',
                    'status' => $isOverdue ? 'overdue' : 'upcoming',
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
