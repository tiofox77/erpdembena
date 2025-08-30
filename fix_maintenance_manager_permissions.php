<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CORRIGIR: Permissões da Role maintenance-manager ===\n\n";

// Get maintenance-manager role
$maintenanceManagerRole = \Spatie\Permission\Models\Role::where('name', 'maintenance-manager')->first();

if (!$maintenanceManagerRole) {
    echo "❌ Role maintenance-manager não encontrada!\n";
    exit;
}

// Permissions that maintenance-manager should have but doesn't
$missingPermissions = [
    'equipment.view',
    'equipment.create',
    'equipment.edit',
    'equipment.delete',
    'equipment.import',
    'equipment.export',
    'equipment.manage',
    'equipment.parts.view',
    'equipment.parts.manage',
    'lines.edit',
    'lines.delete',
    'parts.view',
    'parts.create',
    'parts.edit',
    'parts.delete',
    'parts.manage',
    'parts.request',
    'technicians.view',
    'technicians.create',
    'technicians.edit',
    'technicians.delete',
    'technicians.manage'
];

echo "🔧 ADICIONANDO PERMISSÕES EM FALTA:\n";
echo str_repeat("-", 50) . "\n";

$addedCount = 0;
$alreadyHasCount = 0;

foreach ($missingPermissions as $permissionName) {
    // Check if permission exists
    $permission = \Spatie\Permission\Models\Permission::where('name', $permissionName)->first();
    
    if (!$permission) {
        echo "⚠️  Permissão '{$permissionName}' não existe na base de dados\n";
        continue;
    }
    
    // Check if role already has this permission
    if ($maintenanceManagerRole->hasPermissionTo($permissionName)) {
        echo "✅ Já tem: {$permissionName}\n";
        $alreadyHasCount++;
        continue;
    }
    
    // Add permission to role
    try {
        $maintenanceManagerRole->givePermissionTo($permissionName);
        echo "➕ Adicionado: {$permissionName}\n";
        $addedCount++;
    } catch (Exception $e) {
        echo "❌ Erro ao adicionar '{$permissionName}': " . $e->getMessage() . "\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 RESUMO:\n";
echo "   Permissões adicionadas: {$addedCount}\n";
echo "   Já possuía: {$alreadyHasCount}\n";
echo "   Total verificadas: " . count($missingPermissions) . "\n";

// Verify final state
$finalPermissionCount = $maintenanceManagerRole->fresh()->permissions->count();
echo "   Total de permissões agora: {$finalPermissionCount}\n";

echo "\n✅ ROLE MAINTENANCE-MANAGER ATUALIZADA COM SUCESSO!\n";
echo str_repeat("=", 60) . "\n";
