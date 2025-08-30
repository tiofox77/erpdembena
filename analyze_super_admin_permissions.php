<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ANÁLISE: Permissões da Role Super-Admin ===\n\n";

// Get super-admin role
$superAdminRole = \Spatie\Permission\Models\Role::where('name', 'super-admin')->first();

if (!$superAdminRole) {
    echo "❌ Role super-admin não encontrada!\n";
    exit;
}

echo "🏷️  Role: {$superAdminRole->name}\n";
echo "📊 Total de Permissões: " . $superAdminRole->permissions->count() . "\n\n";

// Group permissions by area
$permissionsByArea = [];
foreach ($superAdminRole->permissions as $permission) {
    $parts = explode('.', $permission->name);
    $area = $parts[0];
    if (!isset($permissionsByArea[$area])) {
        $permissionsByArea[$area] = [];
    }
    $permissionsByArea[$area][] = $permission->name;
}

echo "🏢 PERMISSÕES POR ÁREA:\n";
echo str_repeat("=", 60) . "\n";

foreach ($permissionsByArea as $area => $permissions) {
    echo "\n📋 {$area}: " . count($permissions) . " permissões\n";
    echo str_repeat("-", 30) . "\n";
    foreach ($permissions as $permission) {
        echo "   ✅ {$permission}\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";

// Compare with maintenance-manager
$maintenanceManagerRole = \Spatie\Permission\Models\Role::where('name', 'maintenance-manager')->first();

if ($maintenanceManagerRole) {
    echo "🔍 COMPARAÇÃO: super-admin vs maintenance-manager\n";
    echo str_repeat("-", 50) . "\n";
    
    $superAdminPerms = $superAdminRole->permissions->pluck('name')->toArray();
    $maintenanceManagerPerms = $maintenanceManagerRole->permissions->pluck('name')->toArray();
    
    // Maintenance permissions that super-admin has but maintenance-manager doesn't
    $maintenancePermsInSuper = array_filter($superAdminPerms, function($perm) {
        return strpos($perm, 'maintenance.') === 0 || 
               strpos($perm, 'areas.') === 0 || 
               strpos($perm, 'lines.') === 0 || 
               strpos($perm, 'holidays.') === 0 ||
               strpos($perm, 'equipment.') === 0 ||
               strpos($perm, 'parts.') === 0 ||
               strpos($perm, 'technicians.') === 0;
    });
    
    $missingInMaintenance = array_diff($maintenancePermsInSuper, $maintenanceManagerPerms);
    
    if (!empty($missingInMaintenance)) {
        echo "\n❌ PERMISSÕES QUE MAINTENANCE-MANAGER DEVERIA TER:\n";
        foreach ($missingInMaintenance as $permission) {
            echo "   🚫 {$permission}\n";
        }
    } else {
        echo "\n✅ maintenance-manager tem todas as permissões de manutenção do super-admin\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";

// Check if super-admin has all permissions
$allPermissions = \Spatie\Permission\Models\Permission::all()->pluck('name')->toArray();
$superAdminPerms = $superAdminRole->permissions->pluck('name')->toArray();

$missingFromSuperAdmin = array_diff($allPermissions, $superAdminPerms);

if (!empty($missingFromSuperAdmin)) {
    echo "⚠️  PERMISSÕES QUE SUPER-ADMIN NÃO TEM:\n";
    foreach ($missingFromSuperAdmin as $permission) {
        echo "   ❓ {$permission}\n";
    }
} else {
    echo "✅ SUPER-ADMIN TEM TODAS AS PERMISSÕES DO SISTEMA\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
