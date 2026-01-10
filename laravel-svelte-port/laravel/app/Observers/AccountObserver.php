<?php

namespace App\Observers;

use App\Models\Account;
use App\Enums\Feature;
use Illuminate\Support\Facades\Log;

class AccountObserver
{
    /**
     * Handle the Account "creating" event.
     * Initialize default features before saving to database.
     */
    public function creating(Account $account): void
    {
        // Initialize feature flags if not already set
        if ($account->feature_flags === null || $account->feature_flags === 0) {
            $this->enableDefaultFeatures($account);
        }
    }

    /**
     * Handle the Account "created" event.
     */
    public function created(Account $account): void
    {
        Log::info('Account created', [
            'account_id' => $account->id,
            'account_name' => $account->name,
            'enabled_features' => $account->getEnabledFeatures(),
        ]);
    }

    /**
     * Enable default features for the account based on configuration.
     */
    private function enableDefaultFeatures(Account $account): void
    {
        try {
            // Use Feature enum directly instead of wrapper service
            $enabledFeatures = Feature::getEnabledByDefault();
            
            // Extract feature names from metadata array
            $featureNames = collect($enabledFeatures)
                ->pluck('name')
                ->toArray();
            
            if (!empty($featureNames)) {
                $this->enableFeatures($account, $featureNames);
                
                Log::info('Default features enabled for account', [
                    'account_id' => $account->id ?? 'new',
                    'features_enabled' => $featureNames,
                ]);
            } else {
                $this->enableBasicFeatures($account);
            }

        } catch (\Exception $e) {
            Log::error('Failed to enable default features for account', [
                'account_id' => $account->id ?? 'new',
                'error' => $e->getMessage(),
            ]);
            
            // Fallback to basic features
            $this->enableBasicFeatures($account);
        }
    }

    /**
     * Enable specific features for the account.
     */
    private function enableFeatures(Account $account, array $featureNames): void
    {
        // Use the centralized feature flag map from Account model
        $flagMap = $account->getFeatureFlagMap();
        
        foreach ($featureNames as $featureName) {
            if (isset($flagMap[$featureName])) {
                $account->feature_flags |= $flagMap[$featureName];
            }
        }
    }

    /**
     * Enable basic features as fallback.
     */
    private function enableBasicFeatures(Account $account): void
    {
        $basicFeatures = [
            'channel_email',
            'channel_website',
            'integrations',
            'macros',
            'canned_responses',
            'labels',
            'custom_attributes',
            'file_attachments',
            'conversation_notes',
            'agent_availability',
            'conversation_status',
            'real_time_notifications',
            'team_management',
            'automation_rules',
            'csat_surveys',
            'campaigns',
        ];

        $this->enableFeatures($account, $basicFeatures);
        
        Log::info('Basic features enabled as fallback', [
            'account_id' => $account->id ?? 'new',
            'features_enabled' => $basicFeatures,
        ]);
    }
}