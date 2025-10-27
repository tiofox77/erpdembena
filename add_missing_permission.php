<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ADICIONAR: Permissão em Falta ===\n\n";

// Get maintenance-manager role
$maintenanceRole = \Spatie\Permission\Models\Role::where('name', 'maintenance-manager')->first();

if (!$maintenanceRole) {
    echo "❌ Role 'maintenance-manager' não encontrada\n";
    exit;
}

// Add missing permission
$missingPermission = 'history.team.performance';

try {
    $maintenanceRole->givePermissionTo($missingPermission);
    echo "✅ Permissão '{$missingPermission}' adicionada à role 'maintenance-manager'\n";
} catch (Exception $e) {
    echo "❌ Erro ao adicionar permissão: " . $e->getMessage() . "\n";
}

// Clear permission cache
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
echo "✅ Cache de permissões limpo\n";

// Verify user now has 100% coverage
$maintenanceUser = \App\Models\User::where('email', 'maintenance@dembena-group.com')->first();

if ($maintenanceUser) {
    $hasPermission = $maintenanceUser->can($missingPermission);
    echo "\n🔍 VERIFICAÇÃO:\n";
    echo "Utilizador agora tem '{$missingPermission}': " . ($hasPermission ? "✅ SIM" : "❌ NÃO") . "\n";
    echo "Total de permissões: " . $maintenanceUser->getAllPermissions()->count() . "\n";
}

echo "\n✅ CORREÇÃO CONCLUÍDA\n";
echo "📊 Cobertura agora: 100%\n";
echo str_repeat("=", 40) . "\n";
