<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class FixMaintenanceRolePermissions extends Command
{
    protected $signature = 'role:fix-maintenance';
    protected $description = 'Corrige as permissões da role maintenance-manager para ter acesso a todas as áreas do menu de manutenção';

    public function handle()
    {
        $this->info('Corrigindo permissões da função maintenance-manager...');

        try {
            // Verificar se a role existe
            $role = Role::where('name', 'maintenance-manager')->first();
            
            if (!$role) {
                $this->error("A função 'maintenance-manager' não existe. Execute 'php artisan role:create-maintenance' primeiro.");
                return Command::FAILURE;
            }
            
            // Lista de permissões exatas conforme usadas no menu de manutenção
            $exactPermissions = [
                // Permissões principais do menu
                'preventive.view',
                'equipment.view',
                'areas.view',
                'corrective.view',
                'corrective.manage',
                'users.manage',
                'roles.manage',
                'settings.manage',
                'reports.view',
                
                // Permissões específicas
                'technicians.view',
                
                // Permissões de manutenção
                'maintenance.view',
                'maintenance.create',
                'maintenance.edit',
                'maintenance.delete', 
                'maintenance.manage',
                
                // Permissões de áreas e linhas
                'areas.manage',
                'lines.manage',
                
                // Permissões de corrective
                'corrective.view',
                'corrective.create',
                'corrective.edit',
                'corrective.delete',
                
                // Permissões de equipment
                'equipment.manage',
                'equipment.create',
                'equipment.edit',
                'equipment.delete',
                
                // Permissões de reports
                'reports.generate',
                'reports.export',
                
                // Permissões de usuários
                'users.view',
                'users.create',
                'users.edit',
                'users.delete',
                
                // Permissões de técnicos
                'technicians.create',
                'technicians.edit',
                'technicians.delete',
                'technicians.manage',
                
                // Permissões de partes/peças
                'parts.view',
                'parts.create',
                'parts.edit',
                'parts.delete',
                'parts.manage',
                
                // Permissões de estoque
                'stock.view',
                'stock.in',
                'stock.out',
                'stock.manage',
                'stock.history',
                
                // Outras permissões específicas visíveis no menu
                'dashboard.view',
                'holidays.view',
                'holidays.manage',
            ];
            
            // Verificar/criar todas as permissões exatas e atribuir à role
            $createdCount = 0;
            $existingCount = 0;
            
            foreach ($exactPermissions as $permName) {
                $permission = Permission::firstOrCreate(
                    ['name' => $permName, 'guard_name' => 'web']
                );
                
                if ($permission->wasRecentlyCreated) {
                    $this->line("- Permissão criada: {$permName}");
                    $createdCount++;
                } else {
                    $existingCount++;
                }
                
                // Garantir que a role tenha esta permissão
                if (!$role->hasPermissionTo($permName)) {
                    $role->givePermissionTo($permName);
                    $this->info("- Permissão {$permName} adicionada à role maintenance-manager");
                }
            }
            
            // Manter todas as permissões anteriores também
            $allMaintPermissions = Permission::where(function($query) {
                $query->where('name', 'like', 'maintenance.%')
                    ->orWhere('name', 'like', 'maintenanceplan.%')
                    ->orWhere('name', 'like', 'maintenancetask.%')
                    ->orWhere('name', 'like', 'maintenanceequipment.%')
                    ->orWhere('name', 'like', 'maintenancecategory.%');
            })->get();
            
            foreach ($allMaintPermissions as $permission) {
                if (!$role->hasPermissionTo($permission->name)) {
                    $role->givePermissionTo($permission->name);
                }
            }
            
            $this->info("Função 'maintenance-manager' atualizada com sucesso!");
            $this->info("Novas permissões criadas: {$createdCount}");
            $this->info("Permissões existentes: {$existingCount}");
            $this->info("Total de permissões na role: " . $role->permissions->count());
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Erro: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
