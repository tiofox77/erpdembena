<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UpdateMaintenanceRole extends Command
{
    protected $signature = 'role:update-maintenance';
    protected $description = 'Atualiza as permissões da função maintenance-manager para todos os módulos de manutenção';

    public function handle()
    {
        $this->info('Atualizando permissões da função maintenance-manager...');

        try {
            // Verificar se a role existe
            $role = Role::where('name', 'maintenance-manager')->first();
            
            if (!$role) {
                $this->error("A função 'maintenance-manager' não existe. Execute 'php artisan role:create-maintenance' primeiro.");
                return Command::FAILURE;
            }
            
            // Lista completa de permissões para todas as áreas de manutenção do menu
            $permissionPrefixes = [
                // Menu principal de manutenção
                'maintenance',
                
                // Submenus específicos
                'dashboard',
                'maintenanceplan',
                'equipment',
                'equipmentparts',
                'line',
                'area',
                'task',
                'correctivemaintenance',
                'maintenancesettings',
                'reports.maintenance',
                'holidays',
                
                // Ações específicas para cada módulo
                'maintenancetask',
                'maintenanceequipment', 
                'maintenancecategory',
            ];
            
            // Ações comuns para cada módulo
            $actions = [
                'view', 
                'create', 
                'edit', 
                'delete', 
                'export', 
                'import', 
                'list',
                'manage'
            ];
            
            $createdCount = 0;
            $existingCount = 0;
            
            // Criar permissões para cada prefixo e ação se não existirem
            foreach ($permissionPrefixes as $prefix) {
                foreach ($actions as $action) {
                    $permName = "{$prefix}.{$action}";
                    
                    $permission = Permission::firstOrCreate(
                        ['name' => $permName, 'guard_name' => 'web']
                    );
                    
                    if ($permission->wasRecentlyCreated) {
                        $this->line("- Permissão criada: {$permName}");
                        $createdCount++;
                    } else {
                        $existingCount++;
                    }
                }
            }
            
            // Permissões específicas adicionais
            $specificPermissions = [
                'maintenance.calendar',
                'maintenance.schedule',
                'maintenance.reports',
                'equipment.inventory',
                'equipment.maintenance',
                'reports.view',
                'reports.generate',
                'dashboard.maintenance',
                'settings.maintenance'
            ];
            
            foreach ($specificPermissions as $permName) {
                $permission = Permission::firstOrCreate(
                    ['name' => $permName, 'guard_name' => 'web']
                );
                
                if ($permission->wasRecentlyCreated) {
                    $this->line("- Permissão específica criada: {$permName}");
                    $createdCount++;
                } else {
                    $existingCount++;
                }
            }
            
            // Buscar todas as permissões relacionadas à manutenção
            $allMaintPermissions = Permission::where(function($query) use ($permissionPrefixes) {
                foreach ($permissionPrefixes as $prefix) {
                    $query->orWhere('name', 'like', "{$prefix}.%");
                }
            })->get();
            
            // Atribuir todas as permissões à role
            $role->syncPermissions($allMaintPermissions);
            
            $this->info("Função 'maintenance-manager' atualizada com sucesso!");
            $this->info("Novas permissões criadas: {$createdCount}");
            $this->info("Permissões existentes: {$existingCount}");
            $this->info("Total de permissões atribuídas: " . $allMaintPermissions->count());
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Erro: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
