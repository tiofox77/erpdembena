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
    protected $description = 'Cria uma nova função (role) para gerenciamento de manutenção';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Criando role para gerenciamento de manutenção...');

        try {
            // Buscar todas as permissões relacionadas à manutenção
            $maintenancePermissions = Permission::where('name', 'like', 'maintenance.%')
                ->orWhere('name', 'like', 'maintenanceplan.%')
                ->orWhere('name', 'like', 'maintenancetask.%')
                ->orWhere('name', 'like', 'maintenanceequipment.%')
                ->orWhere('name', 'like', 'maintenancecategory.%')
                ->get();
            
            if ($maintenancePermissions->count() === 0) {
                $this->info('Nenhuma permissão de manutenção encontrada. Criando permissões básicas...');
                
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
                    $this->line("- Permissão criada: $permName");
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
                $this->info("A função 'maintenance-manager' já existe. Atualizando permissões...");
                $role = $existingRole;
            } else {
                // Criar a nova role
                $this->info("Criando nova função 'maintenance-manager'...");
                $role = Role::create([
                    'name' => 'maintenance-manager',
                    'guard_name' => 'web'
                ]);
            }
            
            // Atribuir todas as permissões de manutenção à role
            $permissionsIds = $maintenancePermissions->pluck('id')->toArray();
            $role->syncPermissions($permissionsIds);
            
            $this->info("Função 'maintenance-manager' configurada com sucesso!");
            $this->info("Total de permissões atribuídas: " . count($permissionsIds));
            
            // Exibir todas as permissões atribuídas
            $this->info("Permissões atribuídas:");
            foreach ($maintenancePermissions as $perm) {
                $this->line("- " . $perm->name);
            }
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Erro: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
