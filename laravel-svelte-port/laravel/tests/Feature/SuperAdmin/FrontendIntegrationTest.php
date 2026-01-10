<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_simulates_exact_frontend_scenario()
    {
        $superAdmin = User::factory()->create([
            'type' => 'SuperAdmin',
            'email_verified_at' => now(),
        ]);
        
        $account = Account::factory()->create(['name' => 'Frontend Integration Test']);
        
        // Simulate exactly what the frontend FeatureFlagManager sends
        // The component creates an object like: { inboundEmails: true, channelEmail: true }
        // The parent component sends this as: { selectedFeatureFlags: { inboundEmails: true, channelEmail: true } }
        // The API client should transform this to: { selected_feature_flags: { inbound_emails: true, channel_email: true } }
        
        // This is what should reach the Laravel API after transformation:
        $expectedTransformedPayload = [
            'selected_feature_flags' => [
                'inbound_emails' => true,
                'channel_email' => true,
                'help_center' => true,
            ]
        ];

        dump('Testing expected transformed payload:', $expectedTransformedPayload);

        $response = $this->actingAs($superAdmin)
            ->putJson("/api/v1/super_admin/accounts/{$account->id}", $expectedTransformedPayload);

        $response->assertOk();
        
        $account->refresh();
        
        // Verify features are enabled
        $this->assertNotEquals(0, $account->feature_flags);
        $enabledFeatures = $account->getEnabledFeatures();
        
        $expectedFeatures = ['inbound_emails', 'channel_email', 'help_center'];
        foreach ($expectedFeatures as $feature) {
            $this->assertContains($feature, $enabledFeatures, "Feature {$feature} should be enabled");
        }
        
        // Verify API response format
        $data = $response->json('data');
        $this->assertArrayHasKey('selected_feature_flags', $data);
        $this->assertArrayHasKey('all_features', $data);
        
        foreach ($expectedFeatures as $feature) {
            $this->assertContains($feature, $data['selected_feature_flags'], "API response should include {$feature}");
        }
        
        dump('Success! Features enabled:', $enabledFeatures);
        dump('API response selected_feature_flags:', $data['selected_feature_flags']);
        
        $this->assertTrue(true);
    }

    /** @test */
    public function it_demonstrates_the_problem_scenario()
    {
        $superAdmin = User::factory()->create([
            'type' => 'SuperAdmin',
            'email_verified_at' => now(),
        ]);
        
        $account = Account::factory()->create(['name' => 'Problem Scenario Test']);
        
        // This simulates what happens if the API transformation is NOT working
        // Frontend sends camelCase, but it reaches the API without transformation
        $problematicPayload = [
            'selected_feature_flags' => [
                'inboundEmails' => true,      // camelCase keys (wrong)
                'channelEmail' => true,       // camelCase keys (wrong)
                'helpCenter' => true,         // camelCase keys (wrong)
            ]
        ];

        dump('Testing problematic payload (camelCase keys):', $problematicPayload);

        $response = $this->actingAs($superAdmin)
            ->putJson("/api/v1/super_admin/accounts/{$account->id}", $problematicPayload);

        $response->assertOk();
        
        $account->refresh();
        
        // camelCase keys won't match the feature map, so no features should be enabled
        $this->assertEquals(0, $account->feature_flags, 'camelCase keys should not enable any features');
        $this->assertEmpty($account->getEnabledFeatures(), 'No features should be enabled with camelCase keys');
        
        dump('Problem confirmed: No features enabled with camelCase keys');
        
        $this->assertTrue(true);
    }
}