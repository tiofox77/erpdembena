<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class OpcacheOptimize extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'opcache:optimize 
                            {--clear : Clear OPcache before optimizing}
                            {--status : Show OPcache status}
                            {--warm-up : Pre-compile all Laravel files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize Laravel application for OPcache performance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Laravel OPcache Optimization');
        $this->newLine();

        // Check if OPcache is available
        if (!function_exists('opcache_get_status')) {
            $this->error('❌ OPcache is not available!');
            return 1;
        }

        // Clear OPcache if requested
        if ($this->option('clear')) {
            $this->clearOpcache();
        }

        // Show status if requested
        if ($this->option('status')) {
            $this->showStatus();
            return 0;
        }

        // Run optimization
        $this->optimizeLaravel();

        // Warm up if requested
        if ($this->option('warm-up')) {
            $this->warmUp();
        }

        $this->newLine();
        $this->info('✅ Optimization completed successfully!');
        
        return 0;
    }

    /**
     * Clear OPcache
     */
    protected function clearOpcache()
    {
        $this->info('🧹 Clearing OPcache...');
        
        if (function_exists('opcache_reset')) {
            opcache_reset();
            $this->line('   ✓ OPcache cleared');
        } else {
            $this->warn('   ⚠ opcache_reset() not available');
        }
    }

    /**
     * Show OPcache status
     */
    protected function showStatus()
    {
        $status = opcache_get_status(false);
        
        if ($status === false) {
            $this->error('❌ Could not retrieve OPcache status. Make sure OPcache is properly configured.');
            $this->newLine();
            $this->warn('💡 Tip: Check if opcache.enable=1 in php.ini and restart your web server.');
            return;
        }
        
        $config = opcache_get_configuration();

        $this->info('📊 OPcache Status:');
        $this->newLine();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Enabled', $status['opcache_enabled'] ? '✅ Yes' : '❌ No'],
                ['Memory Usage', round($status['memory_usage']['used_memory'] / 1024 / 1024, 2) . ' MB'],
                ['Memory Available', round($status['memory_usage']['free_memory'] / 1024 / 1024, 2) . ' MB'],
                ['Memory Wasted', round($status['memory_usage']['wasted_memory'] / 1024 / 1024, 2) . ' MB'],
                ['Cached Scripts', number_format($status['opcache_statistics']['num_cached_scripts'])],
                ['Cached Keys', number_format($status['opcache_statistics']['num_cached_keys'])],
                ['Max Cached Keys', number_format($status['opcache_statistics']['max_cached_keys'])],
                ['Hits', number_format($status['opcache_statistics']['hits'])],
                ['Misses', number_format($status['opcache_statistics']['misses'])],
                ['Hit Rate', round($status['opcache_statistics']['opcache_hit_rate'], 2) . '%'],
                ['JIT Buffer Size', $config['directives']['opcache.jit_buffer_size']],
            ]
        );
    }

    /**
     * Optimize Laravel
     */
    protected function optimizeLaravel()
    {
        $this->info('⚙️  Optimizing Laravel...');
        $this->newLine();

        // Clear all caches first
        $this->line('   • Clearing caches...');
        Artisan::call('optimize:clear');

        // Config cache
        $this->line('   • Caching configuration...');
        Artisan::call('config:cache');

        // Route cache
        $this->line('   • Caching routes...');
        Artisan::call('route:cache');

        // View cache
        $this->line('   • Caching views...');
        Artisan::call('view:cache');

        // Event cache
        $this->line('   • Caching events...');
        Artisan::call('event:cache');

        $this->newLine();
        $this->line('   ✓ Laravel optimization completed');
    }

    /**
     * Warm up OPcache by pre-compiling files
     */
    protected function warmUp()
    {
        $this->info('🔥 Warming up OPcache...');
        $this->newLine();

        $directories = [
            app_path(),
            base_path('vendor'),
            base_path('config'),
            base_path('routes'),
        ];

        $fileCount = 0;
        $bar = $this->output->createProgressBar(100);
        $bar->start();

        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                continue;
            }

            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory)
            );

            foreach ($files as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    // Pre-compile the file
                    opcache_compile_file($file->getRealPath());
                    $fileCount++;
                    
                    if ($fileCount % 50 === 0) {
                        $bar->advance(1);
                    }
                }
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->line("   ✓ Pre-compiled {$fileCount} PHP files");
    }
}
