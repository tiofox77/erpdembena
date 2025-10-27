<?php

namespace App\Helpers;

class OpcacheHelper
{
    /**
     * Check if OPcache is enabled
     */
    public static function isEnabled(): bool
    {
        return function_exists('opcache_get_status') && opcache_get_status()['opcache_enabled'] ?? false;
    }

    /**
     * Get OPcache statistics
     */
    public static function getStats(): array
    {
        if (!self::isEnabled()) {
            return [];
        }

        $status = opcache_get_status();
        $config = opcache_get_configuration();

        return [
            'enabled' => true,
            'memory' => [
                'used' => round($status['memory_usage']['used_memory'] / 1024 / 1024, 2),
                'free' => round($status['memory_usage']['free_memory'] / 1024 / 1024, 2),
                'wasted' => round($status['memory_usage']['wasted_memory'] / 1024 / 1024, 2),
                'total' => $config['directives']['opcache.memory_consumption'],
                'usage_percent' => round(($status['memory_usage']['used_memory'] / ($config['directives']['opcache.memory_consumption'] * 1024 * 1024)) * 100, 2),
            ],
            'statistics' => [
                'num_cached_scripts' => $status['opcache_statistics']['num_cached_scripts'],
                'num_cached_keys' => $status['opcache_statistics']['num_cached_keys'],
                'max_cached_keys' => $status['opcache_statistics']['max_cached_keys'],
                'hits' => $status['opcache_statistics']['hits'],
                'misses' => $status['opcache_statistics']['misses'],
                'blacklist_misses' => $status['opcache_statistics']['blacklist_misses'],
                'start_time' => $status['opcache_statistics']['start_time'],
                'last_restart_time' => $status['opcache_statistics']['last_restart_time'],
                'oom_restarts' => $status['opcache_statistics']['oom_restarts'],
                'hash_restarts' => $status['opcache_statistics']['hash_restarts'],
                'manual_restarts' => $status['opcache_statistics']['manual_restarts'],
                'hit_rate' => round($status['opcache_statistics']['opcache_hit_rate'], 2),
            ],
            'jit' => [
                'enabled' => $config['directives']['opcache.jit_buffer_size'] > 0,
                'buffer_size' => $config['directives']['opcache.jit_buffer_size'],
                'mode' => $config['directives']['opcache.jit'],
            ],
            'configuration' => [
                'max_accelerated_files' => $config['directives']['opcache.max_accelerated_files'],
                'max_wasted_percentage' => $config['directives']['opcache.max_wasted_percentage'],
                'validate_timestamps' => $config['directives']['opcache.validate_timestamps'],
                'revalidate_freq' => $config['directives']['opcache.revalidate_freq'],
                'memory_consumption' => $config['directives']['opcache.memory_consumption'],
                'interned_strings_buffer' => $config['directives']['opcache.interned_strings_buffer'],
            ],
        ];
    }

    /**
     * Reset OPcache
     */
    public static function reset(): bool
    {
        if (!self::isEnabled() || !function_exists('opcache_reset')) {
            return false;
        }

        return opcache_reset();
    }

    /**
     * Invalidate a specific file in OPcache
     */
    public static function invalidate(string $file, bool $force = false): bool
    {
        if (!self::isEnabled() || !function_exists('opcache_invalidate')) {
            return false;
        }

        return opcache_invalidate($file, $force);
    }

    /**
     * Compile a file into OPcache
     */
    public static function compile(string $file): bool
    {
        if (!self::isEnabled() || !function_exists('opcache_compile_file')) {
            return false;
        }

        if (!file_exists($file)) {
            return false;
        }

        return opcache_compile_file($file);
    }

    /**
     * Get health status
     */
    public static function getHealth(): array
    {
        if (!self::isEnabled()) {
            return [
                'status' => 'disabled',
                'message' => 'OPcache is not enabled',
                'recommendations' => [
                    'Enable OPcache in php.ini',
                ],
            ];
        }

        $stats = self::getStats();
        $issues = [];
        $recommendations = [];

        // Check hit rate
        if ($stats['statistics']['hit_rate'] < 95) {
            $issues[] = 'Low hit rate: ' . $stats['statistics']['hit_rate'] . '%';
            $recommendations[] = 'Increase opcache.memory_consumption or opcache.max_accelerated_files';
        }

        // Check memory usage
        if ($stats['memory']['usage_percent'] > 90) {
            $issues[] = 'High memory usage: ' . $stats['memory']['usage_percent'] . '%';
            $recommendations[] = 'Increase opcache.memory_consumption';
        }

        // Check wasted memory
        $wastedPercent = ($stats['memory']['wasted'] / $stats['memory']['total']) * 100;
        if ($wastedPercent > 10) {
            $issues[] = 'High wasted memory: ' . round($wastedPercent, 2) . '%';
            $recommendations[] = 'Consider resetting OPcache or increase max_wasted_percentage';
        }

        // Check JIT
        if (!$stats['jit']['enabled']) {
            $issues[] = 'JIT is disabled';
            $recommendations[] = 'Enable JIT with opcache.jit_buffer_size=128M for PHP 8.x';
        }

        // Check cached files vs max
        $fileUsagePercent = ($stats['statistics']['num_cached_scripts'] / $stats['configuration']['max_accelerated_files']) * 100;
        if ($fileUsagePercent > 90) {
            $issues[] = 'High file cache usage: ' . round($fileUsagePercent, 2) . '%';
            $recommendations[] = 'Increase opcache.max_accelerated_files';
        }

        return [
            'status' => empty($issues) ? 'healthy' : 'warning',
            'message' => empty($issues) ? 'OPcache is running optimally' : 'OPcache needs attention',
            'issues' => $issues,
            'recommendations' => $recommendations,
            'metrics' => [
                'hit_rate' => $stats['statistics']['hit_rate'] . '%',
                'memory_usage' => $stats['memory']['usage_percent'] . '%',
                'cached_files' => $stats['statistics']['num_cached_scripts'],
                'jit_enabled' => $stats['jit']['enabled'],
            ],
        ];
    }

    /**
     * Warm up OPcache by compiling all application files
     */
    public static function warmUp(array $directories = []): int
    {
        if (!self::isEnabled()) {
            return 0;
        }

        if (empty($directories)) {
            $directories = [
                app_path(),
                base_path('config'),
                base_path('routes'),
            ];
        }

        $count = 0;

        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                continue;
            }

            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($files as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    if (self::compile($file->getRealPath())) {
                        $count++;
                    }
                }
            }
        }

        return $count;
    }
}
