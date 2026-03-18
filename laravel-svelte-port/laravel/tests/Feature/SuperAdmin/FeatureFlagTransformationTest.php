<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeatureFlagTransformationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_verifies_frontend_api_transformation_works()
    {
        $superAdmin = User::factory()->create([
            'type' => 'SuperAdmin',
            'email_verified_at' => now(),
        ]);
        
        $account = Account::factory()->create(['name' => 'Transformation Test']);
        
        // Simulate what the frontend sends after API transformation
        // Frontend: { selectedFeatureFlags: { inboundEmails: true, channelEmail: true } }
        // After transformation: { selected_feature_flags: { inbound_emails: true, channel_email: true } }
        $transformedPayload = [
            'selected_feature_flags' => [
                'inbound_emails' => true,
                'channel_email' => true,
                'help_center' => true,
            ]
        ];

        $response = $this->actingAs($superAdmin)
            ->putJson("/api/v1/super_admin/accounts/{$account->id}", $transformedPayload);

        $response->assertOk();
        
        $account->refresh();
        
        // Features should be enabled
        $this->assertNotEquals(0, $account->feature_flags);
        $enabledFeatures = $account->getEnabledFeatures();
        $this->assertContains('inbound_emails', $enabledFeatures);
        $this->assertContains('channel_email', $enabledFeatures);
        $this->assertContains('help_center', $enabledFeatures);
        
        dump('Transformation test - feature_flags:', $account->feature_flags);
        dump('Transformation test - enabled features:', $enabledFeatures);
        
        $this->assertTrue(true);
    }

    /** @test */
    public function it_normalizes_camelcase_feature_keys()
    {
        $superAdmin = User::factory()->create([
            'type' => 'SuperAdmin',
            'email_verified_at' => now(),
        ]);
        
        $account = Account::factory()->create(['name' => 'CamelCase Test']);
        
        // Simulate what happens if transformation doesn't work
        // Frontend sends: { selectedFeatureFlags: { inboundEmails: true, channelEmail: true } }
        // Without transformation: same camelCase keys reach the API
        $camelCasePayload = [
            'selected_feature_flags' => [
                'inboundEmails' => true,
                'channelEmail' => true,
                'helpCenter' => true,
            ]
        ];

        $response = $this->actingAs($superAdmin)
            ->putJson("/api/v1/super_admin/accounts/{$account->id}", $camelCasePayload);

        $response->assertOk();
        
        $account->refresh();
        
        $enabledFeatures = $account->getEnabledFeatures();

        $this->assertContains('inbound_emails', $enabledFeatures);
        $this->assertContains('channel_email', $enabledFeatures);
        $this->assertContains('help_center', $enabledFeatures);
    }
}
