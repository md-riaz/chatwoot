<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeatureFlagDebugTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_debugs_feature_flag_update_issue()
    {
        // Create super admin
        $superAdmin = User::factory()->create([
            'type' => 'SuperAdmin',
            'email_verified_at' => now(),
        ]);
        
        // Create account
        $account = Account::factory()->create(['name' => 'Debug Account']);
        
        dump('Initial state:');
        dump('feature_flags:', $account->feature_flags);
        dump('enabled features count:', count($account->getEnabledFeatures()));
        
        // Test 1: Send camelCase keys (what frontend currently sends - should not work)
        $camelCasePayload = [
            'selected_feature_flags' => [
                'inboundEmails' => true,
                'channelEmail' => true,
            ]
        ];
        
        dump('Sending camelCase keys (should not work):', $camelCasePayload);
        
        $response1 = $this->actingAs($superAdmin)
            ->putJson("/api/v1/super_admin/accounts/{$account->id}", $camelCasePayload);
        
        $response1->assertOk();
        $account->refresh();
        
        dump('After camelCase keys (should be 0):');
        dump('feature_flags:', $account->feature_flags);
        dump('enabled features count:', count($account->getEnabledFeatures()));
        
        // Test 2: Send snake_case keys (what should work)
        $snakeCasePayload = [
            'selected_feature_flags' => [
                'inbound_emails' => true,
                'channel_email' => true,
            ]
        ];
        
        dump('Sending snake_case keys (should work):', $snakeCasePayload);
        
        $response2 = $this->actingAs($superAdmin)
            ->putJson("/api/v1/super_admin/accounts/{$account->id}", $snakeCasePayload);
        
        $response2->assertOk();
        $account->refresh();
        
        dump('After snake_case keys (should have features):');
        dump('feature_flags:', $account->feature_flags);
        dump('enabled features count:', count($account->getEnabledFeatures()));
        
        // Test 3: Send empty array
        $emptyPayload = [
            'selected_feature_flags' => []
        ];
        
        dump('Sending empty payload:', $emptyPayload);
        
        $response3 = $this->actingAs($superAdmin)
            ->putJson("/api/v1/super_admin/accounts/{$account->id}", $emptyPayload);
        
        $response3->assertOk();
        $account->refresh();
        
        dump('After empty update:');
        dump('feature_flags:', $account->feature_flags);
        dump('enabled features count:', count($account->getEnabledFeatures()));
        
        // The test passes if we get here
        $this->assertTrue(true);
    }
}