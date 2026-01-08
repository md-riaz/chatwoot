<?php

namespace App\Services;

use App\Models\InstallationConfig;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Yaml\Yaml;

class ConfigLoaderService
{
    private array $options;
    private string $configPath;
    private array $stats = [];
    private array $errors = [];

    public function __construct(array $options = [])
    {
        $this->options = array_merge([
            'reconcile_only_new' => true,
            'migrate_env_vars' => true,
            'load_features' => true,
            'config_path' => null,
        ], $options);

        $this->configPath = $this->options['config_path'] ?? config_path();
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
            // Load general installation configs
            $generalResults = $this->reconcileGeneralConfig();
            $results['configs_loaded'] += $generalResults['loaded'];
            $results['configs_updated'] += $generalResults['updated'];
            $results['configs_skipped'] += $generalResults['skipped'];

            // Load feature configs if enabled
            if ($this->options['load_features']) {
                $featureResults = $this->reconcileFeatureConfig();
                $results['features_loaded'] = $featureResults['loaded'];
            }

            $results['errors'] = $this->errors;

        } catch (\Exception $e) {
            $this->errors[] = 'Failed to process configuration: ' . $e->getMessage();
            Log::error('ConfigLoaderService error', ['error' => $e->getMessage()]);
            $results['errors'] = $this->errors;
        }

        return $results;
    }

    /**
     * Validate configuration files.
     */
    public function validate(?string $configPath = null): array
    {
        $errors = [];
        $path = $configPath ?? $this->configPath . '/installation_config.yml';

        if (!File::exists($path)) {
            $errors[] = "Configuration file not found: {$path}";
            return $errors;
        }

        try {
            $content = File::get($path);
            $configs = Yaml::parse($content);

            if (!is_array($configs)) {
                $errors[] = 'Configuration file must contain an array of configurations';
                return $errors;
            }

            foreach ($configs as $index => $config) {
                if (!isset($config['name'])) {
                    $errors[] = "Configuration at index {$index} is missing 'name' field";
                }

                if (!array_key_exists('value', $config)) {
                    $errors[] = "Configuration '{$config['name']}' is missing 'value' field";
                }
            }

        } catch (\Exception $e) {
            $errors[] = 'Failed to parse YAML: ' . $e->getMessage();
        }

        return $errors;
    }

    /**
     * Export current configuration to YAML file.
     */
    public function export(?string $exportPath = null): string
    {
        $exportPath = $exportPath ?? $this->configPath . '/exported_installation_config.yml';
        
        $configs = InstallationConfig::all()->map(function ($config) {
            return [
                'name' => $config->name,
                'display_title' => $config->display_title,
                'description' => $config->description,
                'value' => $config->value,
                'type' => $config->type,
                'locked' => $config->locked,
            ];
        })->toArray();

        $yamlContent = Yaml::dump($configs, 4, 2);
        File::put($exportPath, $yamlContent);

        return $exportPath;
    }

    /**
     * Get configuration statistics.
     */
    public function getStats(): array
    {
        $totalConfigs = InstallationConfig::count();
        $lockedConfigs = InstallationConfig::where('locked', true)->count();
        $editableConfigs = $totalConfigs - $lockedConfigs;

        $configsByType = InstallationConfig::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        return [
            'total_configs' => $totalConfigs,
            'locked_configs' => $lockedConfigs,
            'editable_configs' => $editableConfigs,
            'configs_by_type' => $configsByType,
        ];
    }

    /**
     * Get default features from features.yml.
     */
    public function getDefaultFeatures(): array
    {
        $featuresPath = $this->configPath . '/features.yml';
        
        if (!File::exists($featuresPath)) {
            Log::warning('Features configuration file not found', ['path' => $featuresPath]);
            return [];
        }

        try {
            $content = File::get($featuresPath);
            $features = Yaml::parse($content);

            if (!is_array($features)) {
                Log::warning('Features configuration is not an array');
                return [];
            }

            return $features;

        } catch (\Exception $e) {
            Log::error('Failed to load features configuration', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get enabled default features.
     */
    public function getEnabledDefaultFeatures(): array
    {
        $features = $this->getDefaultFeatures();
        
        return array_filter($features, function ($feature) {
            return isset($feature['enabled']) && $feature['enabled'] === true;
        });
    }

    /**
     * Reconcile general installation configuration.
     */
    private function reconcileGeneralConfig(): array
    {
        $configPath = $this->configPath . '/installation_config.yml';
        $results = ['loaded' => 0, 'updated' => 0, 'skipped' => 0];

        if (!File::exists($configPath)) {
            $this->errors[] = "Installation config file not found: {$configPath}";
            return $results;
        }

        try {
            $content = File::get($configPath);
            $configs = Yaml::parse($content);

            if (!is_array($configs)) {
                $this->errors[] = 'Installation config must be an array';
                return $results;
            }

            foreach ($configs as $configData) {
                if (!isset($configData['name'])) {
                    continue;
                }

                $existing = InstallationConfig::where('name', $configData['name'])->first();
                
                if ($existing) {
                    if ($this->shouldUpdateConfig($existing, $configData)) {
                        $this->updateConfig($existing, $configData);
                        $results['updated']++;
                    } else {
                        $results['skipped']++;
                    }
                } else {
                    $this->createConfig($configData);
                    $results['loaded']++;
                }
            }

        } catch (\Exception $e) {
            $this->errors[] = 'Failed to process installation config: ' . $e->getMessage();
        }

        return $results;
    }

    /**
     * Reconcile feature configuration.
     */
    private function reconcileFeatureConfig(): array
    {
        $features = $this->getDefaultFeatures();
        $results = ['loaded' => 0];

        if (empty($features)) {
            return $results;
        }

        $config = InstallationConfig::where('name', 'ACCOUNT_LEVEL_FEATURE_DEFAULTS')->first();

        if ($config) {
            if ($this->options['reconcile_only_new']) {
                // Merge existing with new features
                $existingFeatures = is_array($config->value) ? $config->value : [];
                $mergedFeatures = $this->mergeFeatures($existingFeatures, $features);
                
                if ($mergedFeatures !== $existingFeatures) {
                    $config->value = $mergedFeatures; // Uses the setter
                    $config->save();
                    $results['loaded'] = 1;
                }
            } else {
                // Replace with new features
                $config->value = $features; // Uses the setter
                $config->save();
                $results['loaded'] = 1;
            }
        } else {
            // Create new feature config
            $config = new InstallationConfig([
                'name' => 'ACCOUNT_LEVEL_FEATURE_DEFAULTS',
                'display_title' => 'Account Level Feature Defaults',
                'description' => 'Default features enabled for new accounts',
                'type' => 'array',
                'locked' => true,
            ]);
            
            // Use the setter to properly format the value
            $config->value = $features;
            $config->save();
            $results['loaded'] = 1;
        }

        return $results;
    }

    /**
     * Check if config should be updated.
     */
    private function shouldUpdateConfig(InstallationConfig $existing, array $newData): bool
    {
        if ($this->options['reconcile_only_new']) {
            return false; // Don't update existing configs in reconcile mode
        }

        return $existing->value !== ($newData['value'] ?? null) ||
               $existing->locked !== ($newData['locked'] ?? true);
    }

    /**
     * Update existing configuration.
     */
    private function updateConfig(InstallationConfig $config, array $data): void
    {
        $config->update([
            'display_title' => $data['display_title'] ?? $config->display_title,
            'description' => $data['description'] ?? $config->description,
            'value' => $data['value'] ?? $config->value, // Uses the setter
            'type' => $data['type'] ?? $config->type,
            'locked' => $data['locked'] ?? true,
        ]);
    }

    /**
     * Create new configuration.
     */
    private function createConfig(array $data): void
    {
        $config = new InstallationConfig([
            'name' => $data['name'],
            'display_title' => $data['display_title'] ?? $data['name'],
            'description' => $data['description'] ?? '',
            'type' => $data['type'] ?? 'text',
            'locked' => $data['locked'] ?? true,
        ]);
        
        // Use the setter to properly format the value
        $config->value = $data['value'] ?? null;
        $config->save();
    }

    /**
     * Merge existing features with new features.
     */
    private function mergeFeatures(array $existing, array $new): array
    {
        $existingByName = collect($existing)->keyBy('name');
        $newByName = collect($new)->keyBy('name');

        // Start with existing features
        $merged = $existing;

        // Add new features that don't exist
        foreach ($newByName as $name => $feature) {
            if (!$existingByName->has($name)) {
                $merged[] = $feature;
            }
        }

        return $merged;
    }
}