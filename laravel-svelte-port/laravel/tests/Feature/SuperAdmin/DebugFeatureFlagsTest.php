<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebugFeatureFlagsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function debug_feature_flag_api_issue()
    {
        // Create super admin
        $superAdmin = User::factory()->create([
            'type' => 'SuperAdmin',
            'email_verified_at' => now(),
        ]);
        
        // Create account with no features
        $account = Account::factory()->create([
            'name' => 'Debug Account',
            'feature_flags' => 0,
            'custom_attributes' => [],
        ]);
        
        \Log::info('=== DEBUG: Initial Account State ===');
        \Log::info('Account ID: ' . $account->id);
        \Log::info('Initial feature_flags: ' . $account->feature_flags);
        \Log::info('Initial custom_attributes: ' . json_encode($account->custom_attributes));
        \Log::info('Initial getEnabledFeatures(): ' . json_encode($account->getEnabledFeatures()));
        
        // Test the exact payload that's failing - but simulate the API transformation
        // Frontend sends camelCase, API client transforms to snake_case
        $frontendPayload = [
            'name' => 'Alpha Net',
            'status' => 'active',
            'locale' => 'en',
            'domain' => '',
            'supportEmail' => '',
            'selectedFeatureFlags' => [
                'macros', 'labels', 'campaigns', 'webhooks', 'websiteWidget',
                'facebookIntegration', 'emailIntegration', 'instagramIntegration',
                'whatsappIntegration', 'twitterIntegration', 'cannedResponses',
                'conversationAssignment', 'conversationNotes', 'realTimeNotifications',
                'conversationStatus', 'fileAttachments', 'contactManagement',
                'automationRules', 'teamManagement', 'conversationSearch',
                'agentAvailability', 'apiAccess', 'csatSurveys', 'customRoles',
                'customBranding', 'saml', 'disableBranding', 'slaPolicies',
                'auditLogs', 'agentCapacity', 'advancedReporting'
            ],
            'settings' => [],
            'limits' => [],
            'customAttributes' => []
        ];
        
        // Simulate API client transformation (camelCase -> snake_case)
        $transformedPayload = [
            'name' => 'Alpha Net',
            'status' => 'active',
            'locale' => 'en',
            'domain' => '',
            'support_email' => '',
            'selected_feature_flags' => [
                'macros', 'labels', 'campaigns', 'webhooks', 'website_widget',
                'facebook_integration', 'email_integration', 'instagram_integration',
                'whatsapp_integration', 'twitter_integration', 'canned_responses',
                'conversation_assignment', 'conversation_notes', 'real_time_notifications',
                'conversation_status', 'file_attachments', 'contact_management',
                'automation_rules', 'team_management', 'conversation_search',
                'agent_availability', 'api_access', 'csat_surveys', 'custom_roles',
                'custom_branding', 'saml', 'disable_branding', 'sla_policies',
                'audit_logs', 'agent_capacity', 'advanced_reporting'
            ],
            'settings' => [],
            'limits' => [],
            'custom_attributes' => []
        ];
        
        \Log::info('=== DEBUG: Frontend Payload (camelCase) ===');
        \Log::info('Frontend selectedFeatureFlags: ' . json_encode($frontendPayload['selectedFeatureFlags']));
        
        \Log::info('=== DEBUG: Transformed Payload (snake_case) ===');
        \Log::info('Transformed selected_feature_flags: ' . json_encode($transformedPayload['selected_feature_flags']));
        
        // Make the API request with the transformed payload
        $response = $this->actingAs($superAdmin)
            ->putJson("/api/v1/super_admin/accounts/{$account->id}", $transformedPayload);
        
        \Log::info('=== DEBUG: API Response ===');
        \Log::info('Status: ' . $response->getStatusCode());
        \Log::info('Response: ' . json_encode($response->json(), JSON_PRETTY_PRINT));
        
        // Check database state after API call
        $account->refresh();
        
        \Log::info('=== DEBUG: Final Account State ===');
        \Log::info('Final feature_flags: ' . $account->feature_flags);
        \Log::info('Final custom_attributes: ' . json_encode($account->custom_attributes));
        \Log::info('Final getEnabledFeatures(): ' . json_encode($account->getEnabledFeatures()));
        
        // Test specific features
        $testFeatures = [
            'macros', 'labels', 'custom_branding', 'saml', 'sla_policies'
        ];
        
        \Log::info('=== DEBUG: Individual Feature Tests ===');
        foreach ($testFeatures as $feature) {
            $enabled = $account->feature_enabled($feature);
            \Log::info("Feature '{$feature}': " . ($enabled ? 'ENABLED' : 'DISABLED'));
        }
        
        // Check what the API response contains
        $responseData = $response->json('data');
        $selectedFeatures = $responseData['selected_feature_flags'] ?? [];
        
        \Log::info('=== DEBUG: API Response Features ===');
        \Log::info('selected_feature_flags count: ' . count($selectedFeatures));
        \Log::info('selected_feature_flags: ' . json_encode($selectedFeatures));
        
        // The test should pass if we can enable at least some features
        $this->assertTrue($account->feature_enabled('macros'), 'Macros should be enabled');
        $this->assertTrue($response->isOk(), 'API request should succeed');
        
        // NEW: Test that enterprise features are enabled
        $this->assertTrue($account->feature_enabled('custom_branding'), 'Custom branding should be enabled');
        $this->assertTrue($account->feature_enabled('saml'), 'SAML should be enabled');
    }
    
    /** @test */
    public function debug_account_model_methods()
    {
        $account = Account::factory()->create([
            'feature_flags' => 0,
            'custom_attributes' => [],
        ]);
        
        \Log::info('=== DEBUG: Testing Account Model Methods ===');
        
        // Test enableFeature directly
        \Log::info('Testing enableFeature(macros)...');
        $result = $account->enableFeature('macros');
        \Log::info('enableFeature result: ' . ($result ? 'true' : 'false'));
        \Log::info('feature_flags after macros: ' . $account->feature_flags);
        
        \Log::info('Testing enableFeature(saml)...');
        $result = $account->enableFeature('saml');
        \Log::info('enableFeature result: ' . ($result ? 'true' : 'false'));
        \Log::info('custom_attributes after saml: ' . json_encode($account->custom_attributes));
        
        // Test feature_enabled
        \Log::info('Testing feature_enabled(macros): ' . ($account->feature_enabled('macros') ? 'true' : 'false'));
        \Log::info('Testing feature_enabled(saml): ' . ($account->feature_enabled('saml') ? 'true' : 'false'));
        
        // Test getEnabledFeatures
        $enabled = $account->getEnabledFeatures();
        \Log::info('getEnabledFeatures(): ' . json_encode($enabled));
        
        $this->assertTrue(true); // Always pass, this is just for debugging
    }
}