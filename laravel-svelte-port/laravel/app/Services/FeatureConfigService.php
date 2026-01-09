<?php

namespace App\Services;

use App\Enums\Feature;
use App\Models\InstallationConfig;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class FeatureConfigService
{
    /**
     * Get all available features with metadata.
     */
    public function getAllFeatures(): Collection
    {
        return collect(Feature::cases())->map(function (Feature $feature) {
            $metadata = $feature->metadata();
            $metadata['name'] = $feature->value;
            return $metadata;
        });
    }

    /**
     * Get enabled features by default.
     */
    public function getEnabledByDefault(): array
    {
        return Feature::getEnabledByDefault();
    }

    /**
     * Get premium features.
     */
    public function getPremiumFeatures(): array
    {
        return Feature::getPremiumFeatures();
    }

    /**
     * Initialize account level feature defaults in database.
     */
    public function initializeAccountFeatureDefaults(): array
    {
        $enabledFeatures = $this->getEnabledByDefault();
        
        $config = InstallationConfig::updateOrCreate(
            ['name' => 'ACCOUNT_LEVEL_FEATURE_DEFAULTS'],
            [
                'display_title' => 'Account Level Feature Defaults',
                'description' => 'Default features enabled for new accounts',
                'type' => 'array',
                'locked' => true,
                'value' => $enabledFeatures,
            ]
        );

        return [
            'features_loaded' => count($enabledFeatures),
            'config_id' => $config->id,
            'updated' => $config->wasRecentlyCreated ? 'created' : 'updated',
        ];
    }

    /**
     * Get account feature defaults from database.
     */
    public function getAccountFeatureDefaults(): array
    {
        $config = InstallationConfig::where('name', 'ACCOUNT_LEVEL_FEATURE_DEFAULTS')->first();
        
        if (!$config) {
            // Initialize if not exists
            $this->initializeAccountFeatureDefaults();
            $config = InstallationConfig::where('name', 'ACCOUNT_LEVEL_FEATURE_DEFAULTS')->first();
        }

        return $config ? $config->value : [];
    }

    /**
     * Update account feature defaults.
     */
    public function updateAccountFeatureDefaults(array $features): bool
    {
        try {
            $config = InstallationConfig::where('name', 'ACCOUNT_LEVEL_FEATURE_DEFAULTS')->first();
            
            if ($config) {
                $config->value = $features;
                $config->save();
            } else {
                InstallationConfig::create([
                    'name' => 'ACCOUNT_LEVEL_FEATURE_DEFAULTS',
                    'display_title' => 'Account Level Feature Defaults',
                    'description' => 'Default features enabled for new accounts',
                    'type' => 'array',
                    'locked' => true,
                    'value' => $features,
                ]);
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to update account feature defaults', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Check if a feature is enabled by default.
     */
    public function isEnabledByDefault(string $featureName): bool
    {
        $feature = Feature::fromName($featureName);
        return $feature ? $feature->metadata()['enabled'] : false;
    }

    /**
     * Check if a feature is premium.
     */
    public function isPremium(string $featureName): bool
    {
        $feature = Feature::fromName($featureName);
        return $feature ? $feature->metadata()['premium'] : false;
    }

    /**
     * Get feature metadata by name.
     */
    public function getFeatureMetadata(string $featureName): ?array
    {
        $feature = Feature::fromName($featureName);
        if (!$feature) {
            return null;
        }

        $metadata = $feature->metadata();
        $metadata['name'] = $feature->value;
        return $metadata;
    }

    /**
     * Get configuration statistics.
     */
    public function getStats(): array
    {
        $allFeatures = $this->getAllFeatures();
        $enabledFeatures = $allFeatures->where('enabled', true);
        $premiumFeatures = $allFeatures->where('premium', true);

        return [
            'total_features' => $allFeatures->count(),
            'enabled_by_default' => $enabledFeatures->count(),
            'premium_features' => $premiumFeatures->count(),
            'standard_features' => $allFeatures->where('premium', false)->count(),
        ];
    }
}