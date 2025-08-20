<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Corrigindo permissões da role maintenance-manager...\n";

try {
    // Verificar se a role existe
    $role = Role::where('name', 'maintenance-manager')->first();
    
    if (!$role) {
        echo "Erro: A role 'maintenance-manager' não foi encontrada.\n";
        exit(1);
    }
    
    echo "Role encontrada: {$role->name}\n";
    
    // Permissões essenciais para o MENU PRINCIPAL aparecer (baseado no @canany do layout)
    $essentialPermissions = [
        'equipment.view',
        'preventive.view', 
        'corrective.view',
        'reports.view',
        'parts.view',
        'stock.manage',
        'settings.manage'
    ];
    
    $createdCount = 0;
    $assignedCount = 0;
    
    foreach ($essentialPermissions as $permissionName) {
        // Criar permissão se não existir
        $permission = Permission::firstOrCreate([
            'name' => $permissionName,
            'guard_name' => 'web'
        ]);
        
        if ($permission->wasRecentlyCreated) {
            echo "- Permissão criada: {$permissionName}\n";
            $createdCount++;
        }
        
        // Atribuir à role se não tiver
        if (!$role->hasPermissionTo($permissionName)) {
            $role->givePermissionTo($permissionName);
            echo "- Permissão atribuída: {$permissionName}\n";
            $assignedCount++;
        }
    }
    
    echo "\nResumo:\n";
    echo "- Permissões criadas: {$createdCount}\n";
    echo "- Permissões atribuídas: {$assignedCount}\n";
    echo "- Total de permissões da role: " . $role->permissions->count() . "\n";
    echo "\nPermissões atuais da role maintenance-manager:\n";
    
    foreach ($role->permissions as $perm) {
        echo "  - {$perm->name}\n";
    }
    
    echo "\nCorreção concluída! O menu de maintenance agora deve aparecer para usuários com a role maintenance-manager.\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
    exit(1);
}
