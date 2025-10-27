<?php
/**
 * Fix Storage Directories
 * 
 * This script creates all necessary Laravel storage directories
 * Run this on your production server to fix session storage issues
 */

// Get the base path of the Laravel installation
$basePath = __DIR__;
$storagePath = $basePath . '/storage';

// Define all required storage directories
$directories = [
    $storagePath . '/app',
    $storagePath . '/app/public',
    $storagePath . '/framework',
    $storagePath . '/framework/cache',
    $storagePath . '/framework/cache/data',
    $storagePath . '/framework/sessions',
    $storagePath . '/framework/testing',
    $storagePath . '/framework/views',
    $storagePath . '/logs',
];

echo "Creating Laravel storage directories...\n";
echo "=====================================\n\n";

$created = 0;
$existed = 0;
$failed = 0;

foreach ($directories as $directory) {
    if (!is_dir($directory)) {
        if (mkdir($directory, 0755, true)) {
            echo "✓ Created: {$directory}\n";
            $created++;
        } else {
            echo "✗ Failed to create: {$directory}\n";
            $failed++;
        }
    } else {
        echo "- Already exists: {$directory}\n";
        $existed++;
    }
}

echo "\n=====================================\n";
echo "Summary:\n";
echo "- Created: {$created} directories\n";
echo "- Already existed: {$existed} directories\n";
echo "- Failed: {$failed} directories\n";

if ($failed === 0) {
    echo "\n✓ All storage directories are ready!\n";
    echo "\nNow run these commands to set proper permissions:\n";
    echo "chmod -R 755 storage\n";
    echo "chmod -R 775 storage/framework\n";
    echo "chmod -R 775 storage/logs\n";
} else {
    echo "\n✗ Some directories could not be created. Check permissions.\n";
}
