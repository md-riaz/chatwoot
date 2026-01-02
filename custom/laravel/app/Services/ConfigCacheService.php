<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * Configuration Cache Service
 * 
 * Provides advanced configuration caching with warm-up, invalidation,
 * and performance optimization for production environments.
 */
class ConfigCacheService
{
    private const CACHE_PREFIX = 'config_cache:';
    private const CACHE_TTL = 7200; // 2 hours
    private const WARM_UP_BATCH_SIZE = 50;

    /**
     * Configuration keys that should be cached for performance.
     */
    private const CRITICAL_CONFIG_KEYS = [
        'app.name',
        'app.env',
        'app.debug',
        'app.url',
        'database.default',
        'cache.default',
        'queue.default',
        'mail.default',
        'broadcasting.default',
        'session.driver',
        'session.lifetime',
        'filesystems.default',
        'logging.default',
        'services.redis.host',
        'services.redis.port',
        'horizon.prefix',
        'horizon.waits_for',
        'horizon.trim.recent',
        'horizon.trim.pending',
        'horizon.trim.completed',
        'horizon.trim.failed',
    ];

    /**
     * Warm up configuration cache with critical settings.
     */
    public static function warmUp(): array
    {
        $results = [
            'cached_keys' => 0,
            'failed_keys' => 0,
            'errors' => [],
            'execution_time' => 0,
        ];

        $startTime = microtime(true);

        try {
            // Cache critical configuration keys
            foreach (array_chunk(self::CRITICAL_CONFIG_KEYS, self::WARM_UP_BATCH_SIZE) as $batch) {
                foreach ($batch as $key) {
                    try {
                        $value = Config::get($key);
                        $cacheKey = self::CACHE_PREFIX . $key;
                        
                        Cache::put($cacheKey, $value, self::CACHE_TTL);
                        $results['cached_keys']++;
                        
                    } catch (\Exception $e) {
                        $results['failed_keys']++;
                        $results['errors'][] = "Failed to cache {$key}: " . $e->getMessage();
                    }
                }
            }

            // Cache database connection configurations
            $dbConnections = Config::get('database.connections', []);
            foreach ($dbConnections as $name => $config) {
                try {
                    $cacheKey = self::CACHE_PREFIX . "database.connections.{$name}";
                    Cache::put($cacheKey, $config, self::CACHE_TTL);
                    $results['cached_keys']++;
                } catch (\Exception $e) {
                    $results['failed_keys']++;
                    $results['errors'][] = "Failed to cache DB connection {$name}: " . $e->getMessage();
                }
            }

            // Cache queue configurations
            $queueConnections = Config::get('queue.connections', []);
            foreach ($queueConnections as $name => $config) {
                try {
                    $cacheKey = self::CACHE_PREFIX . "queue.connections.{$name}";
                    Cache::put($cacheKey, $config, self::CACHE_TTL);
                    $results['cached_keys']++;
                } catch (\Exception $e) {
                    $results['failed_keys']++;
                    $results['errors'][] = "Failed to cache queue connection {$name}: " . $e->getMessage();
                }
            }

            $results['execution_time'] = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('Configuration cache warm-up completed', $results);

        } catch (\Exception $e) {
            $results['errors'][] = 'Configuration warm-up failed: ' . $e->getMessage();
            Log::error('Configuration cache warm-up failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $results;
    }

    /**
     * Get cached configuration value with fallback.
     */
    public static function get(string $key, $default = null)
    {
        $cacheKey = self::CACHE_PREFIX . $key;
        
        // Try cache first
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        // Fallback to Config::get and cache the result
        $value = Config::get($key, $default);
        
        if ($value !== null) {
            Cache::put($cacheKey, $value, self::CACHE_TTL);
        }

        return $value;
    }

    /**
     * Set configuration value and update cache.
     */
    public static function set(string $key, $value): void
    {
        Config::set($key, $value);
        
        $cacheKey = self::CACHE_PREFIX . $key;
        Cache::put($cacheKey, $value, self::CACHE_TTL);
    }

    /**
     * Clear configuration cache.
     */
    public static function clear(string $key = null): void
    {
        if ($key) {
            Cache::forget(self::CACHE_PREFIX . $key);
        } else {
            // Clear all configuration cache
            try {
                if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
                    $redis = Redis::connection();
                    $keys = $redis->keys(self::CACHE_PREFIX . '*');
                    
                    if (!empty($keys)) {
                        $redis->del($keys);
                    }
                } else {
                    // For other cache stores, clear known keys
                    foreach (self::CRITICAL_CONFIG_KEYS as $configKey) {
                        Cache::forget(self::CACHE_PREFIX . $configKey);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to clear config cache', [
                    'error' => $e->getMessage()
                ]);
                
                // Fallback: clear known keys
                foreach (self::CRITICAL_CONFIG_KEYS as $configKey) {
                    Cache::forget(self::CACHE_PREFIX . $configKey);
                }
            }
        }
    }

    /**
     * Get cache statistics.
     */
    public static function getStats(): array
    {
        $stats = [
            'total_keys' => 0,
            'cached_keys' => 0,
            'cache_hit_ratio' => 0,
            'memory_usage' => 0,
        ];

        try {
            if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
                $redis = Redis::connection();
                $keys = $redis->keys(self::CACHE_PREFIX . '*');
                $stats['cached_keys'] = count($keys);
                
                // Calculate memory usage
                $memory = 0;
                foreach ($keys as $key) {
                    $memory += $redis->memory('usage', $key) ?? 0;
                }
                $stats['memory_usage'] = $memory;
            } else {
                // For non-Redis stores, estimate based on cached keys
                $cachedCount = 0;
                foreach (self::CRITICAL_CONFIG_KEYS as $configKey) {
                    if (Cache::has(self::CACHE_PREFIX . $configKey)) {
                        $cachedCount++;
                    }
                }
                $stats['cached_keys'] = $cachedCount;
                $stats['memory_usage'] = $cachedCount * 1024; // Estimate 1KB per key
            }

            $stats['total_keys'] = count(self::CRITICAL_CONFIG_KEYS);
            $stats['cache_hit_ratio'] = $stats['total_keys'] > 0 
                ? round(($stats['cached_keys'] / $stats['total_keys']) * 100, 2) 
                : 0;

        } catch (\Exception $e) {
            Log::warning('Failed to get config cache stats', [
                'error' => $e->getMessage()
            ]);
            
            // Fallback stats
            $stats['cached_keys'] = 0;
            $stats['memory_usage'] = 0;
        }

        return $stats;
    }

    /**
     * Optimize configuration for production environment.
     */
    public static function optimizeForProduction(): array
    {
        $optimizations = [
            'config_cached' => false,
            'route_cached' => false,
            'view_cached' => false,
            'event_cached' => false,
            'opcache_enabled' => false,
            'errors' => [],
        ];

        try {
            // Check if configuration is cached
            if (app()->configurationIsCached()) {
                $optimizations['config_cached'] = true;
            } else {
                $optimizations['errors'][] = 'Configuration not cached. Run: php artisan config:cache';
            }

            // Check if routes are cached
            if (app()->routesAreCached()) {
                $optimizations['route_cached'] = true;
            } else {
                $optimizations['errors'][] = 'Routes not cached. Run: php artisan route:cache';
            }

            // Check if views are cached
            if (view()->getEngineResolver()->resolve('blade')->getCompiler()->isExpired('test')) {
                $optimizations['errors'][] = 'Views not cached. Run: php artisan view:cache';
            } else {
                $optimizations['view_cached'] = true;
            }

            // Check if events are cached
            if (app()->eventsAreCached()) {
                $optimizations['event_cached'] = true;
            } else {
                $optimizations['errors'][] = 'Events not cached. Run: php artisan event:cache';
            }

            // Check OPcache
            if (function_exists('opcache_get_status')) {
                $opcacheStatus = opcache_get_status();
                $optimizations['opcache_enabled'] = $opcacheStatus !== false && $opcacheStatus['opcache_enabled'];
            }

            if (!$optimizations['opcache_enabled']) {
                $optimizations['errors'][] = 'OPcache not enabled. Enable in php.ini for better performance.';
            }

        } catch (\Exception $e) {
            $optimizations['errors'][] = 'Failed to check production optimizations: ' . $e->getMessage();
        }

        return $optimizations;
    }

    /**
     * Preload frequently accessed configurations.
     */
    public static function preloadFrequentConfigs(): void
    {
        $frequentConfigs = [
            'app.timezone',
            'app.locale',
            'app.fallback_locale',
            'database.connections.' . Config::get('database.default'),
            'cache.stores.' . Config::get('cache.default'),
            'queue.connections.' . Config::get('queue.default'),
            'mail.mailers.' . Config::get('mail.default'),
            'session.cookie',
            'session.secure',
            'session.same_site',
        ];

        foreach ($frequentConfigs as $key) {
            self::get($key);
        }
    }

    /**
     * Monitor configuration cache performance.
     */
    public static function monitorPerformance(): array
    {
        $metrics = [
            'cache_hits' => 0,
            'cache_misses' => 0,
            'avg_response_time' => 0,
            'memory_efficiency' => 0,
        ];

        try {
            // Test cache performance with sample keys
            $testKeys = array_slice(self::CRITICAL_CONFIG_KEYS, 0, 10);
            $startTime = microtime(true);
            
            foreach ($testKeys as $key) {
                $cached = Cache::get(self::CACHE_PREFIX . $key);
                if ($cached !== null) {
                    $metrics['cache_hits']++;
                } else {
                    $metrics['cache_misses']++;
                }
            }

            $metrics['avg_response_time'] = round(
                ((microtime(true) - $startTime) / count($testKeys)) * 1000, 
                2
            );

            $stats = self::getStats();
            $metrics['memory_efficiency'] = $stats['memory_usage'] > 0 
                ? round($stats['cached_keys'] / ($stats['memory_usage'] / 1024), 2)
                : 0;

        } catch (\Exception $e) {
            Log::warning('Failed to monitor config cache performance', [
                'error' => $e->getMessage()
            ]);
        }

        return $metrics;
    }
}