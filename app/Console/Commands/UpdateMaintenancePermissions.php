<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UpdateMaintenancePermissions extends Command
{
    protected $signature = 'permissions:update-maintenance';
    protected $description = 'Atualiza todas as permissões do módulo de manutenção com base no menu do sistema';

    public function handle()
    {
        $this->info('❌ Comando desativado para evitar recriação automática de roles.');
        $this->info('✅ Use o Gerenciador de Permissões em /admin/permissions-manager');
        return Command::SUCCESS;
        
        // CÓDIGO DESATIVADO
        /*
        $this->info('Atualizando permissões do módulo de manutenção...');

        try {
            // Verificar se a role existe
            $role = Role::where('name', 'maintenance-manager')->first();
            
            if (!$role) {
                $this->info("Criando a função 'maintenance-manager'...");
                $role = Role::create([
                    'name' => 'maintenance-manager',
                    'guard_name' => 'web'
                ]);
            }
            
            // Permissões exatas baseadas no menu completo de manutenção
            $permissionsBySection = [
                // Dashboard
                'Dashboard' => [
                    'maintenance.dashboard.view',
                    'maintenance.dashboard.manage',
                ],
                
                // Plano de Manutenção
                'Maintenance Plan' => [
                    'preventive.view',
                    'preventive.create',
                    'preventive.edit', 
                    'preventive.delete',
                    'preventive.manage',
                    'maintenance.plan.view',
                    'maintenance.plan.create',
                    'maintenance.plan.edit',
                    'maintenance.plan.delete',
                    'maintenance.plan.manage',
                ],
                
                // Equipamentos
                'Equipment Management' => [
                    'equipment.view',
                    'equipment.create',
                    'equipment.edit',
                    'equipment.delete',
                    'equipment.manage',
                    'maintenance.equipment.view',
                    'maintenance.equipment.create',
                    'maintenance.equipment.edit',
                    'maintenance.equipment.delete',
                    'maintenance.equipment.manage',
                ],
                
                // Peças e Estoque
                'Equipment Parts' => [
                    'parts.view',
                    'parts.create',
                    'parts.edit',
                    'parts.delete',
                    'parts.manage',
                    'equipment.parts.view',
                    'equipment.parts.manage',
                    'stock.view',
                    'stock.in',
                    'stock.out',
                    'stock.history',
                    'stock.manage',
                    'stocks.stockin',
                    'stocks.stockout',
                    'stocks.history',
                    'stocks.part-requests',
                ],
                
                // Linhas e Áreas
                'Line & Area' => [
                    'areas.view',
                    'areas.create',
                    'areas.edit',
                    'areas.delete',
                    'areas.manage',
                    'lines.view',
                    'lines.create',
                    'lines.edit',
                    'lines.delete',
                    'lines.manage',
                    'maintenance.linearea.view',
                    'maintenance.linearea.manage',
                ],
                
                // Gerenciamento de Tarefas
                'Task Management' => [
                    'task.view',
                    'task.create',
                    'task.edit',
                    'task.delete',
                    'task.manage',
                    'maintenance.task.view',
                    'maintenance.task.create',
                    'maintenance.task.edit',
                    'maintenance.task.delete',
                    'maintenance.task.manage',
                ],
                
                // Manutenção Corretiva
                'Corrective Maintenance' => [
                    'corrective.view',
                    'corrective.create',
                    'corrective.edit',
                    'corrective.delete',
                    'corrective.manage',
                    'maintenance.corrective.view',
                    'maintenance.corrective.create',
                    'maintenance.corrective.edit',
                    'maintenance.corrective.delete',
                    'maintenance.corrective.manage',
                ],
                
                // Configurações de Manutenção Corretiva
                'Maintenance Corrective Settings' => [
                    'maintenance.failure-modes',
                    'maintenance.failure-mode-categories',
                    'maintenance.failure-causes',
                    'maintenance.failure-cause-categories',
                ],
                
                // Gerenciamento de Usuários
                'User Management' => [
                    'users.view',
                    'users.create',
                    'users.edit',
                    'users.delete',
                    'users.manage',
                    'maintenance.users.view',
                    'maintenance.users.manage',
                ],
                
                // Técnicos
                'Technicians' => [
                    'technicians.view',
                    'technicians.create',
                    'technicians.edit',
                    'technicians.delete',
                    'technicians.manage',
                    'maintenance.technicians.view',
                    'maintenance.technicians.manage',
                ],
                
                // Funções e Permissões
                'Role Permissions' => [
                    'roles.view',
                    'roles.create',
                    'roles.edit',
                    'roles.delete',
                    'roles.manage',
                    'maintenance.roles.view',
                    'maintenance.roles.manage',
                ],
                
                // Feriados
                'Holidays' => [
                    'holidays.view',
                    'holidays.create',
                    'holidays.edit',
                    'holidays.delete',
                    'holidays.manage',
                    'maintenance.holidays.view',
                    'maintenance.holidays.manage',
                    'settings.manage',
                ],
                
                // Relatórios e Histórico
                'Reports & History' => [
                    'reports.view',
                    'reports.generate',
                    'reports.export',
                    'reports.equipment.availability',
                    'reports.equipment.reliability',
                    'reports.maintenance.types',
                    'reports.maintenance.compliance',
                    'reports.maintenance.plan',
                    'reports.resource.utilization',
                    'reports.failure.analysis',
                    'reports.downtime.impact',
                    'history.equipment.timeline',
                    'history.maintenance.audit',
                    'history.parts.lifecycle',
                    'history.team.performance',
                ],
                
                // Permissões gerais de manutenção
                'General Maintenance' => [
                    'maintenance.view',
                    'maintenance.create',
                    'maintenance.edit',
                    'maintenance.delete',
                    'maintenance.manage',
                    'maintenance.calendar',
                    'maintenance.schedule',
                    'maintenance.export',
                    'maintenance.reports',
                    'maintenance.settings',
                ],
            ];
            
            // Contador de permissões
            $createdCount = 0;
            $existingCount = 0;
            $allPermissions = [];
            
            // Criar todas as permissões e registrá-las por seção
            foreach ($permissionsBySection as $section => $permissions) {
                $this->info("Processando seção: {$section}");
                
                foreach ($permissions as $permName) {
                    $permission = Permission::firstOrCreate([
                        'name' => $permName,
                        'guard_name' => 'web'
                    ]);
                    
                    $allPermissions[] = $permission->id;
                    
                    if ($permission->wasRecentlyCreated) {
                        $this->line("  - Permissão criada: {$permName}");
                        $createdCount++;
                    } else {
                        $existingCount++;
                    }
                }
            }
            
            // Atribuir todas as permissões à role
            $role->syncPermissions($allPermissions);
            
            $this->info("\nFunção 'maintenance-manager' atualizada com sucesso!");
            $this->info("Novas permissões criadas: {$createdCount}");
            $this->info("Permissões existentes: {$existingCount}");
            $this->info("Total de permissões atribuídas: " . count($allPermissions));
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Erro: " . $e->getMessage());
            return Command::FAILURE;
        }
        */
        return Command::SUCCESS;
    }
}
