<?php
/**
 * OPcache Preload Script for Laravel
 * 
 * This script preloads frequently used Laravel files into OPcache memory.
 * Configure in php.ini: opcache.preload=/path/to/this/file/preload.php
 * 
 * IMPORTANT: Only use in production!
 */

// Preload only in production
if (getenv('APP_ENV') !== 'production') {
    return;
}

$basePath = __DIR__;

// Helper function to preload files
function preloadFile(string $file): void
{
    if (file_exists($file)) {
        opcache_compile_file($file);
    }
}

// Helper function to preload directory
function preloadDirectory(string $directory, array $exclude = []): void
{
    if (!is_dir($directory)) {
        return;
    }

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $realPath = $file->getRealPath();
            
            // Skip excluded paths
            $skip = false;
            foreach ($exclude as $excludePath) {
                if (strpos($realPath, $excludePath) !== false) {
                    $skip = true;
                    break;
                }
            }
            
            if (!$skip) {
                preloadFile($realPath);
            }
        }
    }
}

try {
    // 1. Preload Laravel Core
    preloadFile($basePath . '/vendor/autoload.php');
    
    // 2. Preload Laravel Framework
    preloadDirectory($basePath . '/vendor/laravel/framework/src/Illuminate', [
        '/Testing/',
        '/Console/',
    ]);
    
    // 3. Preload App Directory (your application code)
    preloadDirectory($basePath . '/app', [
        '/Console/Commands/', // Skip console commands
    ]);
    
    // 4. Preload commonly used packages
    $commonPackages = [
        '/vendor/symfony/http-foundation',
        '/vendor/symfony/http-kernel',
        '/vendor/symfony/routing',
        '/vendor/symfony/console',
        '/vendor/monolog/monolog',
        '/vendor/nesbot/carbon',
        '/vendor/guzzlehttp/guzzle',
        '/vendor/livewire/livewire',
    ];
    
    foreach ($commonPackages as $package) {
        preloadDirectory($basePath . $package);
    }
    
    // 5. Preload specific critical files
    $criticalFiles = [
        '/bootstrap/app.php',
        '/config/app.php',
        '/config/database.php',
        '/config/cache.php',
        '/config/session.php',
    ];
    
    foreach ($criticalFiles as $file) {
        preloadFile($basePath . $file);
    }
    
} catch (\Throwable $e) {
    // Log errors but don't fail
    error_log('OPcache Preload Error: ' . $e->getMessage());
}
