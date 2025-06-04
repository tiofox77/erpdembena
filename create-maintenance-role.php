<?php

require 'vendor/autoload.php';
require 'bootstrap/app.php';

use Illuminate\Foundation\Application;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Bootstrap the application
$app = new Application(dirname(__FILE__));
$app->singleton('app', fn () => $app);
$app->loadEnvironmentFrom('.env');
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    // Buscar todas as permissões relacionadas à manutenção
    $maintenancePermissions = Permission::where('name', 'like', 'maintenance.%')
        ->orWhere('name', 'like', 'maintenanceplan.%')
        ->orWhere('name', 'like', 'maintenancetask.%')
        ->orWhere('name', 'like', 'maintenanceequipment.%')
        ->orWhere('name', 'like', 'maintenancecategory.%')
        ->get();
    
    if ($maintenancePermissions->count() === 0) {
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
        echo "A função 'maintenance-manager' já existe. Atualizando permissões...\n";
        $role = $existingRole;
    } else {
        // Criar a nova role
        echo "Criando nova função 'maintenance-manager'...\n";
        $role = Role::create([
            'name' => 'maintenance-manager',
            'guard_name' => 'web'
        ]);
    }
    
    // Atribuir todas as permissões de manutenção à role
    $permissionsIds = $maintenancePermissions->pluck('id')->toArray();
    $role->syncPermissions($permissionsIds);
    
    echo "Função 'maintenance-manager' configurada com sucesso!\n";
    echo "Total de permissões atribuídas: " . count($permissionsIds) . "\n";
    
    // Exibir todas as permissões atribuídas
    echo "Permissões atribuídas:\n";
    foreach ($maintenancePermissions as $perm) {
        echo "- " . $perm->name . "\n";
    }
    
    echo "\nProcesso concluído com sucesso.\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
