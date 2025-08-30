<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG: Permissões em Tempo Real ===\n\n";

// Get maintenance user
$maintenance = \App\Models\User::where('email', 'maintenance@dembena-group.com')->first();

if (!$maintenance) {
    echo "❌ Utilizador não encontrado!\n";
    exit;
}

echo "👤 Utilizador: {$maintenance->name} ({$maintenance->email})\n";
echo "🆔 ID: {$maintenance->id}\n";
echo "🏷️  Roles: " . $maintenance->roles->pluck('name')->join(', ') . "\n\n";

// Force refresh permissions
$maintenance->load('roles.permissions');

echo "🔄 TESTE DIRETO DAS PERMISSÕES:\n";
echo str_repeat("-", 40) . "\n";

// Test each permission individually
$menuPermissions = [
    'maintenance.dashboard.view',
    'maintenance.equipment.view', 
    'maintenance.plan.view',
    'maintenance.corrective.view',
    'areas.view',
    'lines.view',
    'maintenance.technicians.view',
    'holidays.view'
];

foreach ($menuPermissions as $permission) {
    // Test multiple ways
    $directCan = $maintenance->can($permission);
    $hasPermissionTo = $maintenance->hasPermissionTo($permission);
    
    echo sprintf("%-30s can(): %s hasPermissionTo(): %s\n", 
        $permission, 
        $directCan ? "✅" : "❌",
        $hasPermissionTo ? "✅" : "❌"
    );
}

echo "\n🔍 TESTE DO @canany COMPLETO:\n";
echo str_repeat("-", 40) . "\n";

$canAnyResult = $maintenance->canAny([
    'maintenance.dashboard.view',
    'maintenance.equipment.view',
    'maintenance.plan.view', 
    'maintenance.corrective.view',
    'areas.view',
    'lines.view',
    'maintenance.technicians.view',
    'holidays.view'
]);

echo "canAny() resultado: " . ($canAnyResult ? "✅ TRUE" : "❌ FALSE") . "\n";

// Test individual parts of canAny
echo "\n🧪 TESTE INDIVIDUAL DE CADA PERMISSÃO:\n";
echo str_repeat("-", 40) . "\n";

foreach ($menuPermissions as $permission) {
    $result = $maintenance->canAny([$permission]);
    echo sprintf("canAny(['%s']): %s\n", $permission, $result ? "✅" : "❌");
}

echo "\n📊 INFORMAÇÕES DA ROLE:\n";
echo str_repeat("-", 40) . "\n";

$role = $maintenance->roles->first();
if ($role) {
    echo "Role ID: {$role->id}\n";
    echo "Role Name: {$role->name}\n";
    echo "Guard Name: {$role->guard_name}\n";
    echo "Total Permissions: " . $role->permissions->count() . "\n";
    
    // Check if specific permissions exist in role
    echo "\n🔑 VERIFICAÇÃO DIRETA NA ROLE:\n";
    foreach ($menuPermissions as $permission) {
        $roleHas = $role->hasPermissionTo($permission);
        echo sprintf("%-30s %s\n", $permission, $roleHas ? "✅" : "❌");
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "🎯 CONCLUSÃO:\n";

if ($canAnyResult) {
    echo "✅ O utilizador DEVERIA ver o menu Maintenance\n";
    echo "🔍 Se não está a ver, pode ser:\n";
    echo "   1. Problema de cache do browser\n";
    echo "   2. Sessão desatualizada\n";
    echo "   3. JavaScript/CSS a esconder o menu\n";
    echo "   4. Middleware das rotas\n";
} else {
    echo "❌ O utilizador NÃO deveria ver o menu Maintenance\n";
    echo "🔧 Problema nas permissões da base de dados\n";
}

echo str_repeat("=", 50) . "\n";
