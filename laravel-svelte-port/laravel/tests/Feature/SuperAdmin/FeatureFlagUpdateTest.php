<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeatureFlagUpdateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_update_account_feature_flags_via_api()
    {
        // Create super admin
        $superAdmin = User::factory()->create([
            'type' => 'SuperAdmin',
            'email_verified_at' => now(),
        ]);
        
        // Create account
        $account = Account::factory()->create([
            'name' => 'Test Account',
            'feature_flags' => 0,
            'custom_attributes' => [],
        ]);
        
        // Update with feature flags (snake_case as API client would send)
        $payload = [
            'name' => 'Updated Account',
            'selected_feature_flags' => [
                'macros',
                'labels', 
                'audit_logs',
                'saml',
                'sla'
            ]
        ];
        
        $response = $this->actingAs($superAdmin)
            ->putJson("/api/v1/super_admin/accounts/{$account->id}", $payload);
        
        $response->assertOk();
        
        // Verify features are enabled
        $account->refresh();
        
        $this->assertTrue($account->feature_enabled('macros'));
        $this->assertTrue($account->feature_enabled('labels'));
        $this->assertTrue($account->feature_enabled('audit_logs'));
        $this->assertTrue($account->feature_enabled('saml'));
        $this->assertTrue($account->feature_enabled('sla'));
        
        // Verify API response includes the features
        $responseData = $response->json('data');
        $selectedFeatures = $responseData['selected_feature_flags'];
        
        $this->assertContains('macros', $selectedFeatures);
        $this->assertContains('labels', $selectedFeatures);
        $this->assertContains('audit_logs', $selectedFeatures);
        $this->assertContains('saml', $selectedFeatures);
        $this->assertContains('sla', $selectedFeatures);
    }
}
