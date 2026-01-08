<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redis;

class CacheController extends Controller
{
    /**
     * Get cache statistics and information.
     */
    public function index(): JsonResponse
    {
        $stats = [
            'default_driver' => config('cache.default'),
            'stores' => $this->getCacheStoreStats(),
            'redis_info' => $this->getRedisInfo(),
        ];

        return response()->json(['data' => $stats]);
    }

    /**
     * Clear all cache.
     */
    public function clearAll(): JsonResponse
    {
        try {
            // Clear application cache
            Artisan::call('cache:clear');
            
            // Clear config cache
            Artisan::call('config:clear');
            
            // Clear route cache
            Artisan::call('route:clear');
            
            // Clear view cache
            Artisan::call('view:clear');
            
            // Clear compiled services
            Artisan::call('clear-compiled');

            return response()->json([
                'message' => 'All cache cleared successfully',
                'cleared' => [
                    'application_cache',
                    'config_cache',
                    'route_cache',
                    'view_cache',
                    'compiled_services'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to clear cache',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear cache by type.
     */
    public function clearByType(Request $request, string $type): JsonResponse
    {
        try {
            $cleared = [];

            switch ($type) {
                case 'application':
                case 'app':
                    Artisan::call('cache:clear');
                    $cleared[] = 'application_cache';
                    break;

                case 'config':
                    Artisan::call('config:clear');
                    $cleared[] = 'config_cache';
                    break;

                case 'route':
                case 'routes':
                    Artisan::call('route:clear');
                    $cleared[] = 'route_cache';
                    break;

                case 'view':
                case 'views':
                    Artisan::call('view:clear');
                    $cleared[] = 'view_cache';
                    break;

                case 'compiled':
                    Artisan::call('clear-compiled');
                    $cleared[] = 'compiled_services';
                    break;

                case 'redis':
                    $this->clearRedisCache();
                    $cleared[] = 'redis_cache';
                    break;

                case 'tags':
                    if ($request->has('tag')) {
                        $tag = $request->input('tag');
                        Cache::tags($tag)->flush();
                        $cleared[] = "tagged_cache_{$tag}";
                    } else {
                        return response()->json([
                            'error' => 'Tag parameter required for tag-based cache clearing',
                        ], 422);
                    }
                    break;

                default:
                    return response()->json([
                        'error' => 'Invalid cache type',
                        'available_types' => [
                            'application', 'config', 'route', 'view', 'compiled', 'redis', 'tags'
                        ],
                    ], 422);
            }

            return response()->json([
                'message' => "Cache type '{$type}' cleared successfully",
                'cleared' => $cleared,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => "Failed to clear {$type} cache",
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear cache by pattern.
     */
    public function clearByPattern(Request $request): JsonResponse
    {
        $validator = \Validator::make($request->all(), [
            'pattern' => 'required|string',
            'store' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $validator->errors(),
            ], 422);
        }

        try {
            $pattern = $request->input('pattern');
            $store = $request->input('store', 'default');
            $cleared = 0;

            if ($store === 'redis' || config('cache.default') === 'redis') {
                $cleared = $this->clearRedisByPattern($pattern);
            } else {
                // For other cache stores, we can't easily clear by pattern
                return response()->json([
                    'error' => 'Pattern-based clearing is only supported for Redis cache',
                ], 422);
            }

            return response()->json([
                'message' => "Cleared {$cleared} cache keys matching pattern '{$pattern}'",
                'pattern' => $pattern,
                'cleared_count' => $cleared,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to clear cache by pattern',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear cache for specific account.
     */
    public function clearAccount(Request $request, int $accountId): JsonResponse
    {
        try {
            $cleared = [];

            // Clear account-specific cache tags
            $accountTags = [
                "account_{$accountId}",
                "account_{$accountId}_settings",
                "account_{$accountId}_features",
                "account_{$accountId}_users",
                "account_{$accountId}_conversations",
            ];

            foreach ($accountTags as $tag) {
                Cache::tags($tag)->flush();
                $cleared[] = $tag;
            }

            // Clear specific cache keys
            $cacheKeys = [
                "account_{$accountId}_settings",
                "account_{$accountId}_features",
                "account_{$accountId}_stats",
            ];

            foreach ($cacheKeys as $key) {
                Cache::forget($key);
                $cleared[] = $key;
            }

            return response()->json([
                'message' => "Cache cleared for account {$accountId}",
                'account_id' => $accountId,
                'cleared' => $cleared,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => "Failed to clear cache for account {$accountId}",
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Warm up cache with common data.
     */
    public function warmup(): JsonResponse
    {
        try {
            $warmed = [];

            // Warm up configuration cache
            Artisan::call('config:cache');
            $warmed[] = 'config_cache';

            // Warm up route cache
            Artisan::call('route:cache');
            $warmed[] = 'route_cache';

            // Warm up view cache (if views exist)
            try {
                Artisan::call('view:cache');
                $warmed[] = 'view_cache';
            } catch (\Exception $e) {
                // View caching might fail if no views exist
            }

            return response()->json([
                'message' => 'Cache warmed up successfully',
                'warmed' => $warmed,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to warm up cache',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get cache store statistics.
     */
    private function getCacheStoreStats(): array
    {
        $stores = [];
        $cacheConfig = config('cache.stores');

        foreach ($cacheConfig as $name => $config) {
            $stores[$name] = [
                'driver' => $config['driver'],
                'is_default' => $name === config('cache.default'),
            ];

            // Add driver-specific info
            if ($config['driver'] === 'redis') {
                $stores[$name]['connection'] = $config['connection'] ?? 'default';
            } elseif ($config['driver'] === 'file') {
                $stores[$name]['path'] = $config['path'] ?? storage_path('framework/cache/data');
            }
        }

        return $stores;
    }

    /**
     * Get Redis information.
     */
    private function getRedisInfo(): array
    {
        try {
            $redis = Redis::connection();
            $info = $redis->info();

            return [
                'connected' => true,
                'version' => $info['redis_version'] ?? 'unknown',
                'used_memory_human' => $info['used_memory_human'] ?? 'unknown',
                'connected_clients' => $info['connected_clients'] ?? 0,
                'total_commands_processed' => $info['total_commands_processed'] ?? 0,
                'keyspace_hits' => $info['keyspace_hits'] ?? 0,
                'keyspace_misses' => $info['keyspace_misses'] ?? 0,
            ];
        } catch (\Exception $e) {
            return [
                'connected' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Clear Redis cache.
     */
    private function clearRedisCache(): void
    {
        $redis = Redis::connection();
        $redis->flushdb();
    }

    /**
     * Clear Redis keys by pattern.
     */
    private function clearRedisByPattern(string $pattern): int
    {
        $redis = Redis::connection();
        $keys = $redis->keys($pattern);
        
        if (!empty($keys)) {
            $redis->del($keys);
        }

        return count($keys);
    }
}