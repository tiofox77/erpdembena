<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CreateMaintenanceRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'role:create-maintenance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new maintenance management role';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info(trans('console.creating_maintenance_role'));

        try {
            // Buscar todas as permissões relacionadas à manutenção
            $maintenancePermissions = Permission::where('name', 'like', 'maintenance.%')
                ->orWhere('name', 'like', 'maintenanceplan.%')
                ->orWhere('name', 'like', 'maintenancetask.%')
                ->orWhere('name', 'like', 'maintenanceequipment.%')
                ->orWhere('name', 'like', 'maintenancecategory.%')
                ->get();
            
            if ($maintenancePermissions->count() === 0) {
                $this->info(trans('console.no_permissions_found'));
                
                // Se não existirem permissões específicas de manutenção, vamos criar algumas
                $baseMaintPermissions = [
                    'maintenance.view',
                    'maintenance.create',
                    'maintenance.edit',
                    'maintenance.delete',
                    'maintenance.calendar',
                    'maintenance.schedule',
                    'maintenance.export',
                    'maintenance.reports'
                ];
                
                foreach ($baseMaintPermissions as $permName) {
                    Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
                    $this->line(trans('console.permission_created', ['name' => $permName]));
                }
                
                // Buscar novamente após criar
                $maintenancePermissions = Permission::where('name', 'like', 'maintenance.%')
                    ->orWhere('name', 'like', 'maintenanceplan.%')
                    ->orWhere('name', 'like', 'maintenancetask.%')
                    ->orWhere('name', 'like', 'maintenanceequipment.%')
                    ->orWhere('name', 'like', 'maintenancecategory.%')
                    ->get();
            }
            
            // Verificar se a role já existe
            $existingRole = Role::where('name', 'maintenance-manager')->first();
            if ($existingRole) {
                $this->info(trans('console.role_exists'));
                $role = $existingRole;
            } else {
                // Criar a nova role
                $this->info(trans('console.creating_new_role'));
                $role = Role::create([
                    'name' => 'maintenance-manager',
                    'guard_name' => 'web'
                ]);
            }
            
            // Atribuir todas as permissões de manutenção à role
            $permissionsIds = $maintenancePermissions->pluck('id')->toArray();
            $role->syncPermissions($permissionsIds);
            
            $this->info(trans('console.role_configured'));
            $this->info(trans('console.total_permissions', ['count' => count($permissionsIds)]));
            
            // Exibir todas as permissões atribuídas
            $this->info(trans('console.assigned_permissions'));
            foreach ($maintenancePermissions as $perm) {
                $this->line("- " . $perm->name);
            }
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error(trans('console.error', ['message' => $e->getMessage()]));
            return Command::FAILURE;
        }
    }
}
