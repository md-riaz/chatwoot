<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComprehensiveFeatureFlagsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_feature_flag_update_with_correct_payload()
    {
        // Create super admin
        $superAdmin = User::factory()->create([
            'type' => 'SuperAdmin',
            'email_verified_at' => now(),
        ]);
        
        // Create account with no features
        $account = Account::factory()->create([
            'name' => 'Test Account',
            'feature_flags' => 0,
            'custom_attributes' => [],
        ]);
        
        \Log::info('=== COMPREHENSIVE TEST: Initial State ===');
        \Log::info('Account ID: ' . $account->id);
        \Log::info('Initial feature_flags: ' . $account->feature_flags);
        \Log::info('Initial custom_attributes: ' . json_encode($account->custom_attributes));
        
        // Test with the exact snake_case payload that should work
        $payload = [
            'name' => 'Test Account Updated',
            'selected_feature_flags' => [
                'macros',
                'labels', 
                'custom_branding',
                'saml',
                'sla_policies',
                'agent_capacity',
                'disable_branding'
            ]
        ];
        
        \Log::info('=== COMPREHENSIVE TEST: Request Payload ===');
        \Log::info('Payload: ' . json_encode($payload, JSON_PRETTY_PRINT));
        
        // Make the API request
        $response = $this->actingAs($superAdmin)
            ->putJson("/api/v1/super_admin/accounts/{$account->id}", $payload);
        
        \Log::info('=== COMPREHENSIVE TEST: Response ===');
        \Log::info('Status: ' . $response->getStatusCode());
        
        if (!$response->isOk()) {
            \Log::error('Response body: ' . $response->getContent());
        }
        
        // Check database state
        $account->refresh();
        
        \Log::info('=== COMPREHENSIVE TEST: Final State ===');
        \Log::info('Final feature_flags: ' . $account->feature_flags);
        \Log::info('Final custom_attributes: ' . json_encode($account->custom_attributes));
        \Log::info('Final getEnabledFeatures(): ' . json_encode($account->getEnabledFeatures()));
        
        // Test individual features
        $expectedFeatures = ['macros', 'labels', 'custom_branding', 'saml', 'sla_policies', 'agent_capacity', 'disable_branding'];
        
        foreach ($expectedFeatures as $feature) {
            $enabled = $account->feature_enabled($feature);
            \Log::info("Feature '{$feature}': " . ($enabled ? 'ENABLED' : 'DISABLED'));
            
            $this->assertTrue($enabled, "Feature '{$feature}' should be enabled");
        }
        
        $this->assertTrue($response->isOk(), 'API request should succeed');
    }
    
    /** @test */
    public function test_account_model_feature_methods_work_correctly()
    {
        $account = Account::factory()->create([
            'feature_flags' => 0,
            'custom_attributes' => [],
        ]);
        
        \Log::info('=== MODEL TEST: Testing Individual Methods ===');
        
        // Test bit flag features
        $bitFlagFeatures = ['macros', 'labels', 'campaigns', 'webhooks'];
        
        foreach ($bitFlagFeatures as $feature) {
            \Log::info("Testing bit flag feature: {$feature}");
            
            $result = $account->enableFeature($feature);
            \Log::info("enableFeature('{$feature}') result: " . ($result ? 'true' : 'false'));
            
            $enabled = $account->feature_enabled($feature);
            \Log::info("feature_enabled('{$feature}') result: " . ($enabled ? 'true' : 'false'));
            
            $this->assertTrue($result, "enableFeature should return true for {$feature}");
            $this->assertTrue($enabled, "feature_enabled should return true for {$feature}");
        }
        
        // Test enterprise features (stored in custom_attributes)
        $enterpriseFeatures = ['custom_branding', 'saml', 'sla_policies', 'agent_capacity', 'disable_branding'];
        
        foreach ($enterpriseFeatures as $feature) {
            \Log::info("Testing enterprise feature: {$feature}");
            
            $result = $account->enableFeature($feature);
            \Log::info("enableFeature('{$feature}') result: " . ($result ? 'true' : 'false'));
            
            $enabled = $account->feature_enabled($feature);
            \Log::info("feature_enabled('{$feature}') result: " . ($enabled ? 'true' : 'false'));
            
            $this->assertTrue($result, "enableFeature should return true for {$feature}");
            $this->assertTrue($enabled, "feature_enabled should return true for {$feature}");
        }
        
        \Log::info('Final feature_flags: ' . $account->feature_flags);
        \Log::info('Final custom_attributes: ' . json_encode($account->custom_attributes));
        \Log::info('Final getEnabledFeatures(): ' . json_encode($account->getEnabledFeatures()));
    }
}