<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountFeatureFlagsTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a super admin user
        $this->superAdmin = User::factory()->create([
            'type' => 'SuperAdmin',
            'email_verified_at' => now(),
        ]);
        
        // Create a test account
        $this->account = Account::factory()->create([
            'name' => 'Test Account',
            'feature_flags' => 0, // Start with no features enabled
            'custom_attributes' => [],
        ]);
    }

    /** @test */
    public function it_can_enable_bit_flag_features()
    {
        // Check initial state
        $initialFlags = $this->account->feature_flags;
        \Log::info("Initial feature_flags: {$initialFlags}");
        
        // Test enabling core features that use bit flags
        $this->account->enableFeature('macros');
        $this->account->enableFeature('labels');
        $this->account->enableFeature('custom_branding');
        
        // Refresh from database
        $this->account->refresh();
        
        $finalFlags = $this->account->feature_flags;
        \Log::info("Final feature_flags: {$finalFlags}");
        
        // Check bit flags are set correctly
        $this->assertTrue($this->account->feature_enabled('macros'));
        $this->assertTrue($this->account->feature_enabled('labels'));
        $this->assertTrue($this->account->feature_enabled('custom_branding'));
        
        // Check individual bits
        $macrosBit = 256;
        $labelsBit = 512;
        $customBrandingBit = 536870912;
        
        $this->assertTrue(($finalFlags & $macrosBit) !== 0, "Macros bit should be set");
        $this->assertTrue(($finalFlags & $labelsBit) !== 0, "Labels bit should be set");
        $this->assertTrue(($finalFlags & $customBrandingBit) !== 0, "Custom branding bit should be set");
        
        // Check getEnabledFeatures returns correct features
        $enabledFeatures = $this->account->getEnabledFeatures();
        $this->assertContains('macros', $enabledFeatures);
        $this->assertContains('labels', $enabledFeatures);
        $this->assertContains('custom_branding', $enabledFeatures);
    }

    /** @test */
    public function it_can_enable_enterprise_features()
    {
        // Test enabling enterprise features that use custom_attributes
        $this->account->enableFeature('saml');
        $this->account->enableFeature('sla_policies');
        $this->account->enableFeature('custom_roles');
        
        // Refresh from database
        $this->account->refresh();
        
        // Check enterprise features are enabled
        $this->assertTrue($this->account->feature_enabled('saml'));
        $this->assertTrue($this->account->feature_enabled('sla_policies'));
        $this->assertTrue($this->account->feature_enabled('custom_roles'));
        
        // Check custom_attributes contains enterprise features
        $customAttributes = $this->account->custom_attributes;
        $this->assertArrayHasKey('enabled_enterprise_features', $customAttributes);
        $this->assertContains('saml', $customAttributes['enabled_enterprise_features']);
        $this->assertContains('sla_policies', $customAttributes['enabled_enterprise_features']);
        $this->assertContains('custom_roles', $customAttributes['enabled_enterprise_features']);
        
        // Check getEnabledFeatures returns enterprise features
        $enabledFeatures = $this->account->getEnabledFeatures();
        $this->assertContains('saml', $enabledFeatures);
        $this->assertContains('sla_policies', $enabledFeatures);
        $this->assertContains('custom_roles', $enabledFeatures);
    }

    /** @test */
    public function it_can_disable_features()
    {
        // Enable some features first
        $this->account->enableFeature('macros');
        $this->account->enableFeature('saml');
        
        // Verify they're enabled
        $this->assertTrue($this->account->feature_enabled('macros'));
        $this->assertTrue($this->account->feature_enabled('saml'));
        
        // Disable them
        $this->account->disableFeature('macros');
        $this->account->disableFeature('saml');
        
        // Refresh from database
        $this->account->refresh();
        
        // Verify they're disabled
        $this->assertFalse($this->account->feature_enabled('macros'));
        $this->assertFalse($this->account->feature_enabled('saml'));
        
        // Check bit flags are cleared
        $this->assertEquals(0, $this->account->feature_flags & 256); // macros bit should be 0
        
        // Check enterprise features are removed from custom_attributes
        $customAttributes = $this->account->custom_attributes;
        $enterpriseFeatures = $customAttributes['enabled_enterprise_features'] ?? [];
        $this->assertNotContains('saml', $enterpriseFeatures);
    }

    /** @test */
    public function it_handles_feature_name_mappings()
    {
        // Test Rails compatibility mappings
        $this->account->enableFeature('email_integration'); // Should map to 'email' bit
        $this->account->enableFeature('website_widget'); // Should map to 'liveChat' bit
        
        // Refresh from database
        $this->account->refresh();
        
        // Check mapped features work
        $this->assertTrue($this->account->feature_enabled('email_integration'));
        $this->assertTrue($this->account->feature_enabled('website_widget'));
        
        // Check underlying bits are set
        $this->assertTrue($this->account->feature_enabled('email')); // Direct bit check
        $this->assertTrue($this->account->feature_enabled('liveChat')); // Direct bit check
    }

    /** @test */
    public function superadmin_api_can_update_account_features()
    {
        // Simulate the API request payload (after camelCase -> snake_case transformation)
        $payload = [
            'name' => 'Updated Account',
            'selected_feature_flags' => [
                'macros',
                'labels',
                'campaigns',
                'webhooks',
                'website_widget',
                'facebook_integration',
                'email_integration',
                'custom_branding',
                'disable_branding',
                'saml',
                'sla_policies',
                'custom_roles',
                'audit_logs'
            ]
        ];

        // Make API request
        $response = $this->actingAs($this->superAdmin)
            ->putJson("/api/v1/super_admin/accounts/{$this->account->id}", $payload);

        // Check response is successful
        $response->assertOk();

        // Refresh account from database
        $this->account->refresh();

        // Check account name was updated
        $this->assertEquals('Updated Account', $this->account->name);

        // Check bit flag features are enabled
        $this->assertTrue($this->account->feature_enabled('macros'));
        $this->assertTrue($this->account->feature_enabled('labels'));
        $this->assertTrue($this->account->feature_enabled('campaigns'));
        $this->assertTrue($this->account->feature_enabled('webhooks'));
        $this->assertTrue($this->account->feature_enabled('website_widget'));
        $this->assertTrue($this->account->feature_enabled('facebook_integration'));
        $this->assertTrue($this->account->feature_enabled('email_integration'));
        $this->assertTrue($this->account->feature_enabled('custom_branding'));
        $this->assertTrue($this->account->feature_enabled('disable_branding'));

        // Check enterprise features are enabled
        $this->assertTrue($this->account->feature_enabled('saml'));
        $this->assertTrue($this->account->feature_enabled('sla_policies'));
        $this->assertTrue($this->account->feature_enabled('custom_roles'));
        $this->assertTrue($this->account->feature_enabled('audit_logs'));

        // Check API response includes enabled features
        $responseData = $response->json('data');
        $selectedFeatures = $responseData['selected_feature_flags'];
        
        // Should include all the features we enabled
        $this->assertContains('macros', $selectedFeatures);
        $this->assertContains('custom_branding', $selectedFeatures);
        $this->assertContains('saml', $selectedFeatures);
    }

    /** @test */
    public function it_clears_existing_features_before_setting_new_ones()
    {
        // Enable some initial features
        $this->account->enableFeature('macros');
        $this->account->enableFeature('labels');
        $this->account->enableFeature('saml');
        
        // Verify they're enabled
        $this->assertTrue($this->account->feature_enabled('macros'));
        $this->assertTrue($this->account->feature_enabled('labels'));
        $this->assertTrue($this->account->feature_enabled('saml'));

        // Update with different features via API
        $payload = [
            'selected_feature_flags' => [
                'campaigns', // New feature
                'custom_branding', // New feature
                'sla_policies' // New enterprise feature
                // Note: macros, labels, saml are NOT in this list
            ]
        ];

        $response = $this->actingAs($this->superAdmin)
            ->putJson("/api/v1/super_admin/accounts/{$this->account->id}", $payload);

        $response->assertOk();

        // Refresh account from database
        $this->account->refresh();

        // Check old features are disabled
        $this->assertFalse($this->account->feature_enabled('macros'));
        $this->assertFalse($this->account->feature_enabled('labels'));
        $this->assertFalse($this->account->feature_enabled('saml'));

        // Check new features are enabled
        $this->assertTrue($this->account->feature_enabled('campaigns'));
        $this->assertTrue($this->account->feature_enabled('custom_branding'));
        $this->assertTrue($this->account->feature_enabled('sla_policies'));
    }

    /** @test */
    public function it_logs_feature_flag_operations_for_debugging()
    {
        // Enable logging for this test
        \Log::info('=== Starting Feature Flag Test ===');
        
        // Test the exact payload from the frontend issue
        $frontendPayload = [
            'macros', 'labels', 'campaigns', 'webhooks', 'website_widget',
            'facebook_integration', 'email_integration', 'instagram_integration',
            'whatsapp_integration', 'twitter_integration', 'canned_responses',
            'conversation_assignment', 'conversation_notes', 'real_time_notifications',
            'conversation_status', 'file_attachments', 'contact_management',
            'automation_rules', 'team_management', 'conversation_search',
            'agent_availability', 'api_access', 'csat_surveys', 'custom_roles',
            'custom_branding', 'saml', 'disable_branding', 'sla_policies',
            'audit_logs', 'agent_capacity', 'advanced_reporting'
        ];

        \Log::info('Frontend payload:', $frontendPayload);

        // Simulate API request
        $payload = ['selected_feature_flags' => $frontendPayload];
        
        $response = $this->actingAs($this->superAdmin)
            ->putJson("/api/v1/super_admin/accounts/{$this->account->id}", $payload);

        // Log response
        \Log::info('API Response Status:', ['status' => $response->getStatusCode()]);
        \Log::info('API Response Data:', $response->json());

        // Refresh and check database state
        $this->account->refresh();
        
        \Log::info('Account feature_flags (bit field):', ['flags' => $this->account->feature_flags]);
        \Log::info('Account custom_attributes:', ['attrs' => $this->account->custom_attributes]);
        \Log::info('getEnabledFeatures():', $this->account->getEnabledFeatures());

        // Check specific features
        $testFeatures = ['macros', 'custom_branding', 'saml', 'sla_policies'];
        foreach ($testFeatures as $feature) {
            $enabled = $this->account->feature_enabled($feature);
            \Log::info("Feature '{$feature}' enabled:", ['enabled' => $enabled]);
            $this->assertTrue($enabled, "Feature '{$feature}' should be enabled but isn't");
        }

        \Log::info('=== Feature Flag Test Complete ===');
    }
}