<?php

namespace App\Models\Concerns;

use App\Models\Inbox;
use App\Models\Label;
use App\Models\Team;
use Illuminate\Support\Facades\Redis;

trait CacheKeys
{
    private const CACHE_KEYS_EXPIRY = 259200; // 72 hours in seconds
    
    private static array $cacheableModels = [
        'label' => Label::class,
        'inbox' => Inbox::class,
        'team' => Team::class,
    ];

    public function getCacheKeys(): array
    {
        $keys = [];
        
        foreach (self::$cacheableModels as $key => $modelClass) {
            $keys[$key] = $this->fetchValueForKey($this->id, $key);
        }
        
        return $keys;
    }

    public function updateCacheKey(string $key): void
    {
        $this->updateCacheKeyForAccount($this->id, $key);
        $this->dispatchCacheUpdateEvent();
    }

    public function resetCacheKeys(): void
    {
        foreach (array_keys(self::$cacheableModels) as $key) {
            $this->updateCacheKeyForAccount($this->id, $key);
        }
        
        $this->dispatchCacheUpdateEvent();
    }

    private function updateCacheKeyForAccount(int $accountId, string $key): void
    {
        $prefixedCacheKey = $this->getPrefixedCacheKey($accountId, $key);
        Redis::setex($prefixedCacheKey, self::CACHE_KEYS_EXPIRY, now()->timestamp);
    }

    private function fetchValueForKey(int $accountId, string $key): string
    {
        $prefixedCacheKey = $this->getPrefixedCacheKey($accountId, $key);
        $valueFromCache = Redis::get($prefixedCacheKey);
        
        if ($valueFromCache !== null) {
            return $valueFromCache;
        }
        
        // Return zero epoch time if not found
        return '0000000000';
    }

    private function getPrefixedCacheKey(int $accountId, string $key): string
    {
        return "idb-cache-key-account-{$accountId}-{$key}";
    }

    private function dispatchCacheUpdateEvent(): void
    {
        // Dispatch cache invalidation event
        event('account.cache.invalidated', [
            'account' => $this,
            'cache_keys' => $this->getCacheKeys(),
        ]);
    }
}