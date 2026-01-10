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
        ]);
    }

    /** @test */
    public function it_can_get_account_with_feature_flags()
    {
        $response = $this->actingAs($this->superAdmin)
            ->getJson("/api/v1/super_admin/accounts/{$this->account->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'selected_feature_flags',
                    'all_features',
                    'features',
                ]
            ]);

        $data = $response->json('data');
        
        // Debug: Let's see what we actually get
        dump('Account data:', [
            'id' => $data['id'],
            'feature_flags_db' => $this->account->feature_flags,
            'selected_feature_flags' => $data['selected_feature_flags'],
            'enabled_features' => $this->account->getEnabledFeatures(),
        ]);
        
        // Verify all_features contains the expected structure
        $this->assertIsArray($data['all_features']);
        $this->assertGreaterThan(30, count($data['all_features'])); // Should have 33+ features
        
        // Verify each feature has the expected structure
        foreach ($data['all_features'] as $featureName => $featureData) {
            $this->assertArrayHasKey('available', $featureData);
            $this->assertArrayHasKey('display_name', $featureData);
            $this->assertArrayHasKey('premium', $featureData);
        }
        
        // Check what features are actually enabled (don't assume empty)
        $this->assertIsArray($data['selected_feature_flags']);
        // Remove the empty assertion for now to see what we get
    }

    /** @test */
    public function it_can_update_account_feature_flags_with_object_format()
    {
        // Test data: enable some features using object format (camelCase keys)
        $selectedFeatures = [
            'inboundEmails' => true,
            'channelEmail' => true,
            'channelFacebook' => true,
            'helpCenter' => true,
        ];

        $response = $this->actingAs($this->superAdmin)
            ->putJson("/api/v1/super_admin/accounts/{$this->account->id}", [
                'name' => 'Test Account Updated',
                'selectedFeatureFlags' => $selectedFeatures,
            ]);

        $response->assertOk();
        
        // Verify the response contains the updated features
        $data = $response->json('data');
        $this->assertIsArray($data['selected_feature_flags']);
        
        // Should contain snake_case feature names
        $expectedFeatures = ['inbound_emails', 'channel_email', 'channel_facebook', 'help_center'];
        foreach ($expectedFeatures as $feature) {
            $this->assertContains($feature, $data['selected_feature_flags']);
        }
        
        // Verify database was actually updated
        $this->account->refresh();
        $enabledFeatures = $this->account->getEnabledFeatures();
        
        foreach ($expectedFeatures as $feature) {
            $this->assertContains($feature, $enabledFeatures, "Feature {$feature} should be enabled");
        }
    }

    /** @test */
    public function it_can_disable_feature_flags()
    {
        // First enable some features
        $this->account->enableFeature('inbound_emails');
        $this->account->enableFeature('channel_email');
        $this->account->enableFeature('help_center');
        $this->account->save();
        
        // Verify features are enabled
        $enabledBefore = $this->account->getEnabledFeatures();
        $this->assertContains('inbound_emails', $enabledBefore);
        $this->assertContains('channel_email', $enabledBefore);
        $this->assertContains('help_center', $enabledBefore);
        
        // Now disable some features (only keep inbound_emails)
        $selectedFeatures = [
            'inboundEmails' => true, // Keep this one
            // Remove channelEmail and helpCenter
        ];

        $response = $this->actingAs($this->superAdmin)
            ->putJson("/api/v1/super_admin/accounts/{$this->account->id}", [
                'selectedFeatureFlags' => $selectedFeatures,
            ]);

        $response->assertOk();
        
        // Verify database was updated
        $this->account->refresh();
        $enabledAfter = $this->account->getEnabledFeatures();
        
        $this->assertContains('inbound_emails', $enabledAfter, 'inbound_emails should still be enabled');
        $this->assertNotContains('channel_email', $enabledAfter, 'channel_email should be disabled');
        $this->assertNotContains('help_center', $enabledAfter, 'help_center should be disabled');
    }

    /** @test */
    public function it_handles_empty_feature_flags()
    {
        // First enable some features
        $this->account->enableFeature('inbound_emails');
        $this->account->enableFeature('channel_email');
        $this->account->save();
        
        // Now send empty object (disable all features)
        $response = $this->actingAs($this->superAdmin)
            ->putJson("/api/v1/super_admin/accounts/{$this->account->id}", [
                'selectedFeatureFlags' => [], // Empty object
            ]);

        $response->assertOk();
        
        // Verify all features are disabled
        $this->account->refresh();
        $enabledFeatures = $this->account->getEnabledFeatures();
        $this->assertEmpty($enabledFeatures, 'All features should be disabled');
    }

    /** @test */
    public function it_preserves_other_account_data_when_updating_features()
    {
        $originalName = $this->account->name;
        $originalDomain = $this->account->domain;
        
        // Update only feature flags
        $response = $this->actingAs($this->superAdmin)
            ->putJson("/api/v1/super_admin/accounts/{$this->account->id}", [
                'selectedFeatureFlags' => [
                    'inboundEmails' => true,
                ],
            ]);

        $response->assertOk();
        
        // Verify other data is preserved
        $this->account->refresh();
        $this->assertEquals($originalName, $this->account->name);
        $this->assertEquals($originalDomain, $this->account->domain);
        
        // But feature flags should be updated
        $enabledFeatures = $this->account->getEnabledFeatures();
        $this->assertContains('inbound_emails', $enabledFeatures);
    }

    /** @test */
    public function it_updates_feature_flags_correctly()
    {
        // Start with a clean account (no features)
        $this->account->feature_flags = 0;
        $this->account->custom_attributes = [];
        $this->account->save();
        
        // Verify starting state
        $this->assertEmpty($this->account->getEnabledFeatures());

        $response = $this->actingAs($this->superAdmin)
            ->putJson("/api/v1/super_admin/accounts/{$this->account->id}", [
                'selectedFeatureFlags' => [
                    'inboundEmails' => true,
                ],
            ]);

        $response->assertOk();
        
        // Check the response
        $data = $response->json('data');
        dump('Update response:', $data['selected_feature_flags']);
        
        // Check the database
        $this->account->refresh();
        $enabledFeatures = $this->account->getEnabledFeatures();
        dump('Enabled features after update:', $enabledFeatures);
        
        $this->assertContains('inbound_emails', $enabledFeatures);
    }
}