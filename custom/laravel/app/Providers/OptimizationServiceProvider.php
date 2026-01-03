<?php

namespace App\Providers;

use App\Actions\Config\ManageCacheAction;
use App\Actions\System\OptimizeDatabaseAction;
use App\Actions\System\OptimizeQueueAction;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

/**
 * Optimization Service Provider
 * 
 * Registers performance optimization services and applies
 * production-ready configurations for enhanced performance.
 */
class OptimizationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Actions are automatically resolved by Laravel's service container
        // No need to register them as singletons since they use the AsAction trait

        // Merge optimization configuration
        $this->mergeConfigFrom(
            __DIR__.'/../../config/optimization.php', 'optimization'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Apply optimizations in production environment
        if ($this->app->environment('production')) {
            $this->applyProductionOptimizations();
        }

        // Warm up configuration cache if enabled
        if (config('optimization.cache.config.warm_up_on_boot', true)) {
            $this->warmUpConfigurationCache();
        }

        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\OptimizePerformanceCommand::class,
            ]);
        }
    }

    /**
     * Apply production-specific optimizations.
     */
    private function applyProductionOptimizations(): void
    {
        try {
            // Optimize database connections
            if (config('optimization.database.queries.optimize_indexes', true)) {
                $this->optimizeDatabaseConnections();
            }

            // Configure queue optimizations
            if (config('optimization.queue.optimization.enabled', true)) {
                $this->optimizeQueueConfiguration();
            }

            // Apply memory optimizations
            $this->applyMemoryOptimizations();

            Log::info('Production optimizations applied successfully');

        } catch (\Exception $e) {
            Log::warning('Failed to apply production optimizations', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Warm up configuration cache.
     */
    private function warmUpConfigurationCache(): void
    {
        try {
            // Defer cache warm-up to avoid blocking application boot
            $this->app->booted(function () {
                if (config('optimization.cache.config.enabled', true)) {
                    ManageCacheAction::run()->warmUp();
                }

                if (config('optimization.cache.config.preload_frequent', true)) {
                    ManageCacheAction::run()->preloadFrequentConfigs();
                }
            });

        } catch (\Exception $e) {
            Log::warning('Failed to warm up configuration cache', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Optimize database connections.
     */
    private function optimizeDatabaseConnections(): void
    {
        // Apply connection pool settings
        $poolSize = config('optimization.database.connections.pool_size', 10);
        $maxConnections = config('optimization.database.connections.max_connections', 100);

        // Configure connection limits
        config([
            'database.pool_size' => $poolSize,
            'database.max_connections' => $maxConnections,
        ]);

        // Apply query optimization settings
        if (config('optimization.database.queries.log_slow_queries', true)) {
            config([
                'logging.channels.slow_queries' => [
                    'driver' => 'daily',
                    'path' => storage_path('logs/slow-queries.log'),
                    'level' => 'info',
                    'days' => 7,
                ],
            ]);
        }
    }

    /**
     * Optimize queue configuration.
     */
    private function optimizeQueueConfiguration(): void
    {
        // Apply priority-based queue routing
        if (config('optimization.queue.optimization.priority_routing', true)) {
            $this->configurePriorityQueues();
        }

        // Configure worker limits
        $this->configureWorkerLimits();

        // Set up auto-scaling if enabled
        if (config('optimization.queue.optimization.auto_scaling', true)) {
            $this->configureAutoScaling();
        }
    }

    /**
     * Configure priority-based queues.
     */
    private function configurePriorityQueues(): void
    {
        $priorities = ['critical', 'high', 'medium', 'low'];
        
        foreach ($priorities as $priority) {
            $workers = config("optimization.queue.workers.{$priority}", 2);
            $timeout = config("optimization.queue.timeouts.{$priority}", 300);
            $retries = config("optimization.queue.retries.{$priority}", 2);

            config([
                "queue.connections.redis_{$priority}.workers" => $workers,
                "queue.connections.redis_{$priority}.timeout" => $timeout,
                "queue.connections.redis_{$priority}.tries" => $retries,
            ]);
        }
    }

    /**
     * Configure worker limits.
     */
    private function configureWorkerLimits(): void
    {
        $memoryLimit = config('optimization.memory.limits.queue_memory_limit', '256M');
        
        config([
            'queue.worker_memory_limit' => $memoryLimit,
            'queue.worker_timeout' => config('optimization.queue.timeouts.medium', 300),
        ]);
    }

    /**
     * Configure auto-scaling.
     */
    private function configureAutoScaling(): void
    {
        // Configure Horizon auto-scaling parameters
        config([
            'horizon.auto_scaling' => true,
            'horizon.scale_up_threshold' => config('optimization.monitoring.alert_thresholds.queue_size', 1000),
            'horizon.scale_down_threshold' => 10,
            'horizon.max_workers_per_queue' => 20,
            'horizon.min_workers_per_queue' => 1,
        ]);
    }

    /**
     * Apply memory optimizations.
     */
    private function applyMemoryOptimizations(): void
    {
        // Configure PHP memory limits
        $memoryLimit = config('optimization.memory.limits.php_memory_limit', '512M');
        ini_set('memory_limit', $memoryLimit);

        // Configure garbage collection
        if (config('optimization.memory.gc.enabled', true)) {
            $probability = config('optimization.memory.gc.probability', 1);
            $divisor = config('optimization.memory.gc.divisor', 1000);
            
            ini_set('session.gc_probability', $probability);
            ini_set('session.gc_divisor', $divisor);
        }

        // Configure OPcache if available
        if (function_exists('opcache_get_status') && config('optimization.production.opcache.enabled', true)) {
            $this->configureOPcache();
        }
    }

    /**
     * Configure OPcache for optimal performance.
     */
    private function configureOPcache(): void
    {
        $opcacheConfig = config('optimization.production.opcache');

        if ($opcacheConfig['enabled']) {
            ini_set('opcache.validate_timestamps', $opcacheConfig['validate_timestamps'] ? '1' : '0');
            ini_set('opcache.revalidate_freq', $opcacheConfig['revalidate_freq']);
            ini_set('opcache.max_accelerated_files', $opcacheConfig['max_accelerated_files']);
            ini_set('opcache.memory_consumption', $opcacheConfig['memory_consumption']);
            
            // Additional OPcache optimizations
            ini_set('opcache.enable_cli', '1');
            ini_set('opcache.save_comments', '0');
            ini_set('opcache.fast_shutdown', '1');
            ini_set('opcache.enable_file_override', '1');
        }
    }

    /**
     * Get optimization status.
     */
    public function getOptimizationStatus(): array
    {
        return [
            'config_cache' => [
                'enabled' => config('optimization.cache.config.enabled', true),
                'stats' => ManageCacheAction::run()->getStats(),
            ],
            'database' => [
                'optimization_enabled' => config('optimization.database.queries.optimize_indexes', true),
                'metrics' => OptimizeDatabaseAction::run()->getPerformanceMetrics(),
            ],
            'queue' => [
                'optimization_enabled' => config('optimization.queue.optimization.enabled', true),
                'health' => OptimizeQueueAction::run()->monitorQueueHealth(),
            ],
            'memory' => [
                'php_memory_limit' => ini_get('memory_limit'),
                'opcache_enabled' => function_exists('opcache_get_status') && opcache_get_status() !== false,
            ],
        ];
    }
}