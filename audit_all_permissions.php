<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== AUDIT: All Permissions vs Menu Navigation ===\n\n";

// Get all roles and their permissions
$roles = \Spatie\Permission\Models\Role::with('permissions')->get();

echo "📋 ROLES AND PERMISSIONS:\n";
echo str_repeat("=", 60) . "\n";

foreach ($roles as $role) {
    echo "🏷️  Role: {$role->name}\n";
    echo "   Permissions: " . $role->permissions->count() . "\n";
    foreach ($role->permissions as $permission) {
        echo "   ✅ {$permission->name}\n";
    }
    echo "\n";
}

echo str_repeat("=", 60) . "\n\n";

// Read the layout file to extract all @can/@canany checks
$layoutFile = file_get_contents(__DIR__ . '/resources/views/layouts/livewire.blade.php');

// Extract all @can and @canany patterns
preg_match_all('/@can(?:any)?\(\[?[\'"]([^\'"]+)[\'"].*?\]?\)/', $layoutFile, $canMatches);
preg_match_all('/@can(?:any)?\(\[([^\]]+)\]/', $layoutFile, $cananyMatches);

$menuPermissions = [];

// Process single @can permissions
foreach ($canMatches[1] as $permission) {
    $menuPermissions[] = trim($permission);
}

// Process @canany permissions (arrays)
foreach ($cananyMatches[1] as $permissionArray) {
    preg_match_all('/[\'"]([^\'"]+)[\'"]/', $permissionArray, $arrayPerms);
    foreach ($arrayPerms[1] as $perm) {
        $menuPermissions[] = trim($perm);
    }
}

$menuPermissions = array_unique($menuPermissions);
sort($menuPermissions);

echo "🔍 PERMISSIONS CHECKED IN MENU:\n";
echo str_repeat("-", 50) . "\n";
foreach ($menuPermissions as $permission) {
    echo "📋 {$permission}\n";
}

echo "\n" . str_repeat("=", 60) . "\n";

// Get all permissions from database
$allPermissions = \Spatie\Permission\Models\Permission::all()->pluck('name')->toArray();
sort($allPermissions);

echo "💾 ALL PERMISSIONS IN DATABASE:\n";
echo str_repeat("-", 50) . "\n";
foreach ($allPermissions as $permission) {
    echo "🔑 {$permission}\n";
}

echo "\n" . str_repeat("=", 60) . "\n";

// Find inconsistencies
echo "⚠️  INCONSISTENCIES ANALYSIS:\n";
echo str_repeat("-", 50) . "\n";

// Permissions in menu but not in database
$menuNotInDb = array_diff($menuPermissions, $allPermissions);
if (!empty($menuNotInDb)) {
    echo "❌ PERMISSIONS IN MENU BUT NOT IN DATABASE:\n";
    foreach ($menuNotInDb as $permission) {
        echo "   🚫 {$permission}\n";
    }
    echo "\n🔧 THESE NEED TO BE FIXED IN THE MENU!\n";
} else {
    echo "✅ All menu permissions exist in database\n";
}

echo "\n";

// Permissions in database but not used in menu
$dbNotInMenu = array_diff($allPermissions, $menuPermissions);
if (!empty($dbNotInMenu)) {
    echo "⚠️  PERMISSIONS IN DATABASE BUT NOT USED IN MENU:\n";
    foreach ($dbNotInMenu as $permission) {
        echo "   📝 {$permission}\n";
    }
} else {
    echo "✅ All database permissions are used in menu\n";
}

echo "\n" . str_repeat("=", 60) . "\n";

// Analyze by area/module
echo "📊 PERMISSIONS BY AREA/MODULE:\n";
echo str_repeat("-", 50) . "\n";

$areas = [];
foreach ($allPermissions as $permission) {
    $parts = explode('.', $permission);
    $area = $parts[0];
    if (!isset($areas[$area])) {
        $areas[$area] = [];
    }
    $areas[$area][] = $permission;
}

foreach ($areas as $area => $permissions) {
    echo "🏢 {$area}: " . count($permissions) . " permissions\n";
    foreach ($permissions as $permission) {
        $inMenu = in_array($permission, $menuPermissions) ? "✅" : "❌";
        echo "   {$inMenu} {$permission}\n";
    }
    echo "\n";
}

echo str_repeat("=", 60) . "\n";
echo "📈 SUMMARY:\n";
echo "   Total Permissions: " . count($allPermissions) . "\n";
echo "   Used in Menu: " . count(array_intersect($menuPermissions, $allPermissions)) . "\n";
echo "   Not Used in Menu: " . count($dbNotInMenu) . "\n";
echo "   Menu Errors: " . count($menuNotInDb) . "\n";
echo str_repeat("=", 60) . "\n";
