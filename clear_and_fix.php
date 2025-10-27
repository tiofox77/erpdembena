<?php
/**
 * Clear Cache and Fix Storage - Production Server
 * Run this on your production server to fix session issues
 */

$basePath = __DIR__;

echo "Laravel Cache Clear & Storage Fix\n";
echo "==================================\n\n";

// Create storage directories
$directories = [
    $basePath . '/storage/framework/sessions',
    $basePath . '/storage/framework/views',
    $basePath . '/storage/framework/cache',
    $basePath . '/storage/framework/cache/data',
    $basePath . '/storage/framework/testing',
    $basePath . '/bootstrap/cache',
];

echo "Creating directories...\n";
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
        echo "✓ Created: {$dir}\n";
    } else {
        echo "- Exists: {$dir}\n";
    }
}

echo "\nClearing Laravel cache...\n";

// Clear config cache
$configCache = $basePath . '/bootstrap/cache/config.php';
if (file_exists($configCache)) {
    unlink($configCache);
    echo "✓ Config cache cleared\n";
}

// Clear routes cache
$routesCache = $basePath . '/bootstrap/cache/routes-v7.php';
if (file_exists($routesCache)) {
    unlink($routesCache);
    echo "✓ Routes cache cleared\n";
}

// Clear services cache
$servicesCache = $basePath . '/bootstrap/cache/services.php';
if (file_exists($servicesCache)) {
    unlink($servicesCache);
    echo "✓ Services cache cleared\n";
}

// Clear compiled views
$viewsPath = $basePath . '/storage/framework/views';
$files = glob($viewsPath . '/*');
if ($files) {
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    echo "✓ Views cache cleared (" . count($files) . " files)\n";
}

// Clear cache files
$cachePath = $basePath . '/storage/framework/cache/data';
$cacheFiles = glob($cachePath . '/*');
if ($cacheFiles) {
    foreach ($cacheFiles as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    echo "✓ Cache files cleared (" . count($cacheFiles) . " files)\n";
}

echo "\n==================================\n";
echo "✓ Done! Now set permissions:\n\n";
echo "chmod -R 775 storage bootstrap/cache\n";
echo "chown -R [your-web-user]:[your-web-user] storage bootstrap/cache\n";
