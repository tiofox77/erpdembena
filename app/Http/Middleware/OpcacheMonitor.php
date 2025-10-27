<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class OpcacheMonitor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only run in debug mode
        if (!config('app.debug') || !function_exists('opcache_get_status')) {
            return $next($request);
        }

        // Get status before request
        $before = $this->getOpcacheMetrics();
        $startTime = microtime(true);

        // Process request
        $response = $next($request);

        // Get status after request
        $after = $this->getOpcacheMetrics();
        $executionTime = (microtime(true) - $startTime) * 1000; // ms

        // Add OPcache stats to response headers (only in debug mode)
        if ($request->has('debug-opcache')) {
            $response->headers->set('X-OPcache-Hits', $after['hits'] - $before['hits']);
            $response->headers->set('X-OPcache-Misses', $after['misses'] - $before['misses']);
            $response->headers->set('X-OPcache-Hit-Rate', round($after['hit_rate'], 2) . '%');
            $response->headers->set('X-OPcache-Memory-Used', round($after['memory_used'], 2) . 'MB');
            $response->headers->set('X-Execution-Time', round($executionTime, 2) . 'ms');
        }

        // Log warnings if hit rate is low
        if ($after['hit_rate'] < 80) {
            Log::warning('Low OPcache hit rate detected', [
                'url' => $request->fullUrl(),
                'hit_rate' => $after['hit_rate'],
                'hits' => $after['hits'],
                'misses' => $after['misses'],
            ]);
        }

        return $response;
    }

    /**
     * Get current OPcache metrics
     */
    protected function getOpcacheMetrics(): array
    {
        $status = opcache_get_status();

        return [
            'hits' => $status['opcache_statistics']['hits'] ?? 0,
            'misses' => $status['opcache_statistics']['misses'] ?? 0,
            'hit_rate' => $status['opcache_statistics']['opcache_hit_rate'] ?? 0,
            'memory_used' => ($status['memory_usage']['used_memory'] ?? 0) / 1024 / 1024,
            'num_cached_scripts' => $status['opcache_statistics']['num_cached_scripts'] ?? 0,
        ];
    }
}
