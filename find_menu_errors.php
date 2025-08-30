<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIND MENU ERRORS: Permissions in Menu but not in Database ===\n\n";

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

// Get all permissions from database
$allPermissions = \Spatie\Permission\Models\Permission::all()->pluck('name')->toArray();

// Find permissions in menu but not in database
$menuNotInDb = array_diff($menuPermissions, $allPermissions);

if (!empty($menuNotInDb)) {
    echo "❌ PERMISSIONS IN MENU BUT NOT IN DATABASE:\n";
    echo str_repeat("-", 50) . "\n";
    foreach ($menuNotInDb as $permission) {
        echo "🚫 {$permission}\n";
    }
    echo "\n🔧 THESE NEED TO BE FIXED IN THE MENU!\n";
    
    echo "\n📍 SEARCHING FOR THESE PERMISSIONS IN LAYOUT FILE:\n";
    echo str_repeat("-", 50) . "\n";
    
    foreach ($menuNotInDb as $permission) {
        echo "\n🔍 Searching for: {$permission}\n";
        
        // Find line numbers where this permission appears
        $lines = explode("\n", $layoutFile);
        foreach ($lines as $lineNum => $line) {
            if (strpos($line, $permission) !== false) {
                $actualLineNum = $lineNum + 1;
                echo "   📍 Line {$actualLineNum}: " . trim($line) . "\n";
            }
        }
    }
} else {
    echo "✅ All menu permissions exist in database\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
