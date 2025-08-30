<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CORRIGIR: Permissão preventive.view ===\n\n";

// Check if permission exists
$permission = \Spatie\Permission\Models\Permission::where('name', 'preventive.view')->first();

if (!$permission) {
    echo "❌ Permissão 'preventive.view' não existe\n";
    echo "🔧 Criando permissão...\n";
    
    try {
        $permission = \Spatie\Permission\Models\Permission::create([
            'name' => 'preventive.view',
            'guard_name' => 'web'
        ]);
        echo "✅ Permissão 'preventive.view' criada com sucesso\n";
    } catch (Exception $e) {
        echo "❌ Erro ao criar permissão: " . $e->getMessage() . "\n";
        exit;
    }
} else {
    echo "✅ Permissão 'preventive.view' já existe\n";
}

// Add permission to maintenance-manager role
$maintenanceRole = \Spatie\Permission\Models\Role::where('name', 'maintenance-manager')->first();

if ($maintenanceRole) {
    if (!$maintenanceRole->hasPermissionTo('preventive.view')) {
        $maintenanceRole->givePermissionTo('preventive.view');
        echo "✅ Permissão 'preventive.view' adicionada à role 'maintenance-manager'\n";
    } else {
        echo "✅ Role 'maintenance-manager' já tem a permissão 'preventive.view'\n";
    }
} else {
    echo "❌ Role 'maintenance-manager' não encontrada\n";
}

// Add permission to super-admin role
$superAdminRole = \Spatie\Permission\Models\Role::where('name', 'super-admin')->first();

if ($superAdminRole) {
    if (!$superAdminRole->hasPermissionTo('preventive.view')) {
        $superAdminRole->givePermissionTo('preventive.view');
        echo "✅ Permissão 'preventive.view' adicionada à role 'super-admin'\n";
    } else {
        echo "✅ Role 'super-admin' já tem a permissão 'preventive.view'\n";
    }
}

// Clear permission cache
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
echo "✅ Cache de permissões limpo\n";

echo "\n" . str_repeat("=", 50) . "\n";
echo "🔍 VERIFICAÇÃO FINAL:\n";
echo str_repeat("-", 20) . "\n";

// Test with maintenance user
$maintenanceUser = \App\Models\User::where('email', 'maintenance@dembena-group.com')->first();

if ($maintenanceUser) {
    $hasPermission = $maintenanceUser->can('preventive.view');
    echo "Utilizador maintenance@dembena-group.com:\n";
    echo "   Permissão 'preventive.view': " . ($hasPermission ? "✅ TEM" : "❌ NÃO TEM") . "\n";
    
    if ($hasPermission) {
        echo "\n🎉 SUCESSO! Utilizador agora pode aceder a /maintenance/plan\n";
    } else {
        echo "\n❌ Ainda há problema. Verificar role do utilizador.\n";
        echo "   Roles: " . $maintenanceUser->roles->pluck('name')->join(', ') . "\n";
    }
} else {
    echo "❌ Utilizador maintenance@dembena-group.com não encontrado\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "✅ CORREÇÃO CONCLUÍDA\n";
echo "🔄 Testar novamente: http://erpdembena.test/maintenance/plan\n";
echo str_repeat("=", 50) . "\n";
