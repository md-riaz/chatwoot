<?php

namespace App\Services;

use App\Models\InstallationConfig;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Yaml\Yaml;

/**
 * Configuration Loader Service
 * 
 * Loads configuration from YAML files and reconciles with database.
 * Supports environment variable migration and feature flag management.
 */
class ConfigLoaderService
{
    private array $options;

    public function __construct(array $options = [])
    {
        $this->options = array_merge([
            'reconcile_only_new' => false,
            'migrate_env_vars' => true,
            'load_features' => true,
        ], $options);
    }

    /**
     * Process configuration loading from YAML files.
     */
    public function process(): array
    {
        $results = [
            'configs_loaded' => 0,
            'configs_updated' => 0,
            'configs_skipped' => 0,
            'features_loaded' => 0,
            'errors' => [],
        ];

        try {
            // Load installation configuration
            $configResults = $this->loadInstallationConfig();
            $results = array_merge($results, $configResults);

            // Load feature flags
            if ($this->options['load_features']) {
                $featureResults = $this->loadFeatureFlags();
                $results['features_loaded'] = $featureResults['features_loaded'];
                $results['errors'] = array_merge($results['errors'], $featureResults['errors']);
            }

            Log::info('Configuration loading completed', $results);

        } catch (\Exception $e) {
            $results['errors'][] = 'Configuration loading failed: ' . $e->getMessage();
            Log::error('Configuration loading failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $results;
    }

    /**
     * Load installation configuration from YAML.
     */
    private function loadInstallationConfig(): array
    {
        $results = [
            'configs_loaded' => 0,
            'configs_updated' => 0,
            'configs_skipped' => 0,
            'errors' => [],
        ];

        $configPath = config_path('installation_config.yml');
        
        if (!File::exists($configPath)) {
            // Create default configuration file
            $this->createDefaultConfigFile($configPath);
        }

        try {
            $yamlContent = File::get($configPath);
            $configs = Yaml::parse($yamlContent);

            if (!is_array($configs)) {
                $results['errors'][] = 'Invalid YAML format in installation_config.yml';
                return $results;
            }

            foreach ($configs as $config) {
                if (!isset($config['name'])) {
                    $results['errors'][] = 'Configuration missing name field';
                    continue;
                }

                try {
                    $result = $this->processConfigItem($config);
                    $results[$result]++;
                } catch (\Exception $e) {
                    $results['errors'][] = "Failed to process config {$config['name']}: " . $e->getMessage();
                }
            }

        } catch (\Exception $e) {
            $results['errors'][] = 'Failed to parse installation_config.yml: ' . $e->getMessage();
        }

        return $results;
    }

    /**
     * Process a single configuration item.
     */
    private function processConfigItem(array $config): string
    {
        $name = $config['name'];
        $existingConfig = InstallationConfig::where('name', $name)->first();

        // Skip if reconcile_only_new is true and config exists
        if ($this->options['reconcile_only_new'] && $existingConfig) {
            return 'configs_skipped';
        }

        // Check for environment variable migration
        $value = $config['value'] ?? null;
        if ($this->options['migrate_env_vars'] && $value === null) {
            $envValue = env($name);
            if ($envValue !== null) {
                $value = $envValue;
                Log::info("Migrated environment variable to config", [
                    'name' => $name,
                    'value' => $envValue
                ]);
            }
        }

        $configData = [
            'name' => $name,
            'serialized_value' => ['value' => $value],
            'display_title' => $config['display_title'] ?? $name,
            'description' => $config['description'] ?? null,
            'type' => $config['type'] ?? 'text',
            'locked' => $config['locked'] ?? false,
            'options' => $config['options'] ?? null,
        ];

        if ($existingConfig) {
            // Update existing config (preserve value if locked)
            if (!$existingConfig->locked) {
                $existingConfig->update($configData);
                return 'configs_updated';
            } else {
                return 'configs_skipped';
            }
        } else {
            // Create new config
            InstallationConfig::create($configData);
            return 'configs_loaded';
        }
    }

    /**
     * Load feature flags.
     */
    private function loadFeatureFlags(): array
    {
        $results = [
            'features_loaded' => 0,
            'errors' => [],
        ];

        try {
            FeatureFlagService::loadFeatureDefaults();
            $results['features_loaded'] = 1;
        } catch (\Exception $e) {
            $results['errors'][] = 'Failed to load feature flags: ' . $e->getMessage();
        }

        return $results;
    }

    /**
     * Create default configuration file.
     */
    private function createDefaultConfigFile(string $path): void
    {
        $defaultConfigs = InstallationConfig::getDefaultConfigurations();
        $yamlContent = Yaml::dump($defaultConfigs, 4, 2);
        
        File::put($path, $yamlContent);
        
        Log::info('Created default installation_config.yml', [
            'path' => $path,
            'configs_count' => count($defaultConfigs)
        ]);
    }

    /**
     * Export current configuration to YAML.
     */
    public function export(string $path = null): string
    {
        $path = $path ?? config_path('installation_config_export.yml');
        
        $configs = InstallationConfig::all()->map(function ($config) {
            return [
                'name' => $config->name,
                'display_title' => $config->display_title,
                'description' => $config->description,
                'value' => $config->value,
                'type' => $config->type,
                'locked' => $config->locked,
                'options' => $config->options,
            ];
        })->toArray();

        $yamlContent = Yaml::dump($configs, 4, 2);
        File::put($path, $yamlContent);

        Log::info('Configuration exported to YAML', [
            'path' => $path,
            'configs_count' => count($configs)
        ]);

        return $path;
    }

    /**
     * Validate configuration file.
     */
    public function validate(string $path = null): array
    {
        $path = $path ?? config_path('installation_config.yml');
        $errors = [];

        if (!File::exists($path)) {
            $errors[] = 'Configuration file does not exist: ' . $path;
            return $errors;
        }

        try {
            $yamlContent = File::get($path);
            $configs = Yaml::parse($yamlContent);

            if (!is_array($configs)) {
                $errors[] = 'Invalid YAML format';
                return $errors;
            }

            foreach ($configs as $index => $config) {
                if (!isset($config['name'])) {
                    $errors[] = "Configuration at index {$index} missing 'name' field";
                    continue;
                }

                // Validate type
                if (isset($config['type']) && !in_array($config['type'], array_keys(InstallationConfig::TYPES))) {
                    $errors[] = "Invalid type '{$config['type']}' for config '{$config['name']}'";
                }

                // Validate select options
                if ($config['type'] === 'select' && empty($config['options'])) {
                    $errors[] = "Select type config '{$config['name']}' missing options";
                }
            }

        } catch (\Exception $e) {
            $errors[] = 'Failed to parse YAML: ' . $e->getMessage();
        }

        return $errors;
    }

    /**
     * Get configuration statistics.
     */
    public function getStats(): array
    {
        $totalConfigs = InstallationConfig::count();
        $lockedConfigs = InstallationConfig::where('locked', true)->count();
        $configsByType = InstallationConfig::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        return [
            'total_configs' => $totalConfigs,
            'locked_configs' => $lockedConfigs,
            'editable_configs' => $totalConfigs - $lockedConfigs,
            'configs_by_type' => $configsByType,
            'groups' => array_keys(InstallationConfig::getConfigGroups()),
        ];
    }
}