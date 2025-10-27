<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel (Laravel 11 compatible)
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG: Menu Permissions for maintenance@dembena-group.com ===\n\n";

// Get the user
$user = \App\Models\User::where('email', 'maintenance@dembena-group.com')->first();

if (!$user) {
    echo "❌ User not found!\n";
    exit;
}

echo "👤 User: {$user->name} ({$user->email})\n";
echo "🏷️  Roles: " . $user->roles->pluck('name')->join(', ') . "\n\n";

// Test specific permissions that are checked in the menu (using CORRECTED permission names)
$menuPermissions = [
    'maintenance.dashboard.view',
    'maintenance.equipment.view', 
    'maintenance.plan.view',
    'maintenance.corrective.view',
    'areas.view',
    'lines.view',
    'maintenance.technicians.view',
    'holidays.view',
    'maintenance.reports',
    'maintenance.corrective.manage'
];

// Get ACTUAL permissions from the role
echo "🔍 ACTUAL Permissions from maintenance-manager role:\n";
echo str_repeat("-", 50) . "\n";
$role = \Spatie\Permission\Models\Role::where('name', 'maintenance-manager')->first();
if ($role) {
    $actualPermissions = $role->permissions->pluck('name')->toArray();
    foreach ($actualPermissions as $perm) {
        echo "✅ $perm\n";
    }
} else {
    echo "❌ Role not found!\n";
}
echo "\n";

echo "🔍 Testing Menu Permissions:\n";
echo str_repeat("-", 50) . "\n";

foreach ($menuPermissions as $permission) {
    $hasPermission = $user->can($permission);
    $status = $hasPermission ? "✅ HAS" : "❌ MISSING";
    echo sprintf("%-30s %s\n", $permission, $status);
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "📋 SUMMARY:\n";

$hasAny = false;
foreach ($menuPermissions as $permission) {
    if ($user->can($permission)) {
        $hasAny = true;
        break;
    }
}

if ($hasAny) {
    echo "✅ User should see the Maintenance menu\n";
} else {
    echo "❌ User will NOT see the Maintenance menu\n";
}

// Check specific sub-menu items
echo "\n🔍 Sub-menu Access:\n";
echo str_repeat("-", 30) . "\n";

$subMenuChecks = [
    'Dashboard' => $user->can('maintenance.dashboard.view') || $user->can('maintenance.equipment.view') || $user->can('maintenance.plan.view') || $user->can('maintenance.corrective.view'),
    'Maintenance Plan' => $user->can('maintenance.plan.view'),
    'Equipment Management' => $user->can('maintenance.equipment.view'),
    'Line & Area' => $user->can('areas.view'),
    'Task Management' => $user->can('maintenance.plan.view'),
    'Corrective Maintenance' => $user->can('maintenance.corrective.view'),
    'Technicians' => $user->can('maintenance.technicians.view'),
    'Holidays' => $user->can('holidays.view')
];

foreach ($subMenuChecks as $menu => $hasAccess) {
    $status = $hasAccess ? "✅ YES" : "❌ NO";
    echo sprintf("%-25s %s\n", $menu, $status);
}

echo "\n" . str_repeat("=", 60) . "\n";
