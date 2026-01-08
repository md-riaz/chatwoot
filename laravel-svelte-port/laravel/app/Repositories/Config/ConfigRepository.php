<?php

namespace App\Repositories\Config;

use App\Models\InstallationConfig;
use App\Repositories\BaseRepository;

class ConfigRepository extends BaseRepository
{
    public function __construct()
    {
        // No specific model for this repository as it works with InstallationConfig
    }

    /**
     * Get configuration value
     */
    public function getConfig(string $key, $default = null)
    {
        return InstallationConfig::getConfig($key, $default);
    }

    /**
     * Set configuration value
     */
    public function setConfig(string $key, $value, bool $locked = false): void
    {
        InstallationConfig::setConfig($key, $value, $locked);
    }

    /**
     * Check if configuration exists
     */
    public function hasConfig(string $key): bool
    {
        return InstallationConfig::where('name', $key)->exists();
    }

    /**
     * Delete configuration
     */
    public function deleteConfig(string $key): bool
    {
        return InstallationConfig::where('name', $key)->delete() > 0;
    }

    /**
     * Get all configurations
     */
    public function getAllConfigs(): array
    {
        return InstallationConfig::all()
            ->pluck('value', 'name')
            ->toArray();
    }
}