<?php

namespace App\Services;

use App\Models\InstallationConfig;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Global Configuration Service
 * 
 * Provides centralized configuration access with caching, type casting,
 * and environment variable fallback support.
 */
class GlobalConfigService
{
    private const CACHE_PREFIX = 'global_config:';
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get multiple configuration values with caching.
     */
    public static function get(array $keys): array
    {
        $result = [];
        $uncachedKeys = [];

        // Check cache for each key
        foreach ($keys as $key) {
            $cacheKey = self::CACHE_PREFIX . $key;
            $cached = Cache::get($cacheKey);
            
            if ($cached !== null) {
                $result[$key] = $cached;
            } else {
                $uncachedKeys[] = $key;
            }
        }

        // Load uncached keys from database
        if (!empty($uncachedKeys)) {
            $configs = InstallationConfig::whereIn('name', $uncachedKeys)->get();
            
            foreach ($uncachedKeys as $key) {
                $config = $configs->firstWhere('name', $key);
                $value = $config ? $config->getTypeCastedValue() : null;
                
                // Cache the value
                Cache::put(self::CACHE_PREFIX . $key, $value, self::CACHE_TTL);
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Load a single configuration value with environment variable fallback.
     */
    public static function load(string $key, $default = null)
    {
        // Check cache first
        $cacheKey = self::CACHE_PREFIX . $key;
        $cached = Cache::get($cacheKey);
        
        if ($cached !== null) {
            return $cached;
        }

        // Try database
        $config = InstallationConfig::where('name', $key)->first();
        
        if ($config) {
            $value = $config->getTypeCastedValue();
            Cache::put($cacheKey, $value, self::CACHE_TTL);
            return $value;
        }

        // Try environment variable
        $envValue = env($key);
        if ($envValue !== null) {
            // Auto-create InstallationConfig from environment variable
            try {
                $config = InstallationConfig::create([
                    'name' => $key,
                    'value' => $envValue,
                    'locked' => false,
                ]);
                
                $value = $config->getTypeCastedValue();
                Cache::put($cacheKey, $value, self::CACHE_TTL);
                
                Log::info("Auto-created configuration from environment variable", [
                    'key' => $key,
                    'value' => $envValue
                ]);
                
                return $value;
            } catch (\Exception $e) {
                Log::warning("Failed to auto-create configuration", [
                    'key' => $key,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $default;
    }

    /**
     * Set a configuration value and update cache.
     */
    public static function set(string $key, $value, bool $locked = false): bool
    {
        try {
            $config = InstallationConfig::updateOrCreate(
                ['name' => $key],
                [
                    'value' => $value,
                    'locked' => $locked,
                ]
            );

            // Update cache
            Cache::put(self::CACHE_PREFIX . $key, $config->getTypeCastedValue(), self::CACHE_TTL);
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to set configuration", [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Clear configuration cache.
     */
    public static function clearCache(string $key = null): void
    {
        if ($key) {
            Cache::forget(self::CACHE_PREFIX . $key);
        } else {
            // Clear all configuration cache
            $keys = Cache::get('global_config_keys', []);
            foreach ($keys as $cacheKey) {
                Cache::forget($cacheKey);
            }
            Cache::forget('global_config_keys');
        }
    }

    /**
     * Get configuration with metadata.
     */
    public static function getWithMetadata(string $key): ?array
    {
        $config = InstallationConfig::where('name', $key)->first();
        
        if (!$config) {
            return null;
        }

        return [
            'name' => $config->name,
            'value' => $config->getTypeCastedValue(),
            'display_title' => $config->display_title,
            'description' => $config->description,
            'type' => $config->type,
            'locked' => $config->locked,
            'options' => $config->options,
        ];
    }

    /**
     * Batch update configurations.
     */
    public static function batchUpdate(array $configs): array
    {
        $results = [];
        
        foreach ($configs as $key => $value) {
            $results[$key] = self::set($key, $value);
        }
        
        return $results;
    }

    /**
     * Get all configurations grouped by category.
     */
    public static function getAllGrouped(): array
    {
        $configs = InstallationConfig::all();
        $groups = InstallationConfig::getConfigGroups();
        $result = [];

        foreach ($groups as $group => $keys) {
            $result[$group] = [];
            
            foreach ($keys as $key) {
                $config = $configs->firstWhere('name', $key);
                if ($config) {
                    $result[$group][] = [
                        'name' => $config->name,
                        'value' => $config->getTypeCastedValue(),
                        'display_title' => $config->display_title,
                        'description' => $config->description,
                        'type' => $config->type,
                        'locked' => $config->locked,
                        'options' => $config->options,
                    ];
                }
            }
        }

        return $result;
    }
}