<?php

namespace Tests\Feature\Onboarding;

use App\Models\Account;
use App\Models\AccountUser;
use App\Models\InstallationConfig;
use App\Models\User;
use App\Enums\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class SuperAdminOnboardingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Load configuration for testing
        $enabledFeatures = Feature::getEnabledByDefault();
        InstallationConfig::updateOrCreate(
            ['name' => 'ACCOUNT_LEVEL_FEATURE_DEFAULTS'],
            [
                'display_title' => 'Account Level Feature Defaults',
                'description' => 'Default features enabled for new accounts',
                'type' => 'array',
                'locked' => true,
                'serialized_value' => $enabledFeatures,
            ]
        );
        
        // Set onboarding flag
        Redis::set('chatwoot_installation_onboarding', true);
    }

    protected function tearDown(): void
    {
        Redis::del('chatwoot_installation_onboarding');
        parent::tearDown();
    }

    public function test_onboarding_creates_superadmin_with_default_features()
    {
        $userData = [
            'user' => [
                'name' => 'Super Admin',
                'company' => 'Test Company',
                'email' => 'admin@test.com',
                'password' => 'password123',
            ]
        ];

        $response = $this->postJson('/api/v1/installation/onboarding', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email', 'type'],
                'account' => ['id', 'name', 'enabled_features', 'feature_flags']
            ]);

        // Verify user was created as SuperAdmin
        $user = User::where('email', 'admin@test.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('SuperAdmin', $user->type);
        $this->assertNotNull($user->email_verified_at);

        // Verify account was created
        $account = Account::where('name', 'Test Company')->first();
        $this->assertNotNull($account);

        // Verify user is linked to account as administrator
        $accountUser = AccountUser::where('user_id', $user->id)
            ->where('account_id', $account->id)
            ->first();
        $this->assertNotNull($accountUser);
        $this->assertEquals('administrator', $accountUser->role->getName());

        // Verify default features are enabled
        $this->assertGreaterThan(0, $account->feature_flags);
        $enabledFeatures = $account->getEnabledFeatures();
        $this->assertNotEmpty($enabledFeatures);

        // Verify specific features are enabled
        $expectedFeatures = [
            'email_integration',
            'website_widget',
            'api_access',
            'team_management',
            'automation_rules',
        ];

        foreach ($expectedFeatures as $feature) {
            $this->assertTrue($account->feature_enabled($feature), "Feature {$feature} should be enabled");
        }

        // Verify onboarding flag was removed
        $this->assertNull(Redis::get('chatwoot_installation_onboarding'));
    }

    public function test_onboarding_fails_when_flag_not_set()
    {
        Redis::del('chatwoot_installation_onboarding');

        $userData = [
            'user' => [
                'name' => 'Super Admin',
                'company' => 'Test Company',
                'email' => 'admin@test.com',
                'password' => 'password123',
            ]
        ];

        $response = $this->postJson('/api/v1/installation/onboarding', $userData);

        $response->assertStatus(403)
            ->assertJson(['error' => 'Onboarding already completed.']);
    }

    public function test_onboarding_validates_required_fields()
    {
        $response = $this->postJson('/api/v1/installation/onboarding', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user.name', 'user.company', 'user.email', 'user.password']);
    }

    public function test_onboarding_prevents_duplicate_email()
    {
        // Create existing user
        User::factory()->create(['email' => 'admin@test.com']);

        $userData = [
            'user' => [
                'name' => 'Super Admin',
                'company' => 'Test Company',
                'email' => 'admin@test.com',
                'password' => 'password123',
            ]
        ];

        $response = $this->postJson('/api/v1/installation/onboarding', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user.email']);
    }

    public function test_account_observer_initializes_features()
    {
        // Create account directly to test observer
        $account = Account::create([
            'name' => 'Test Account',
            'locale' => 'en',
        ]);

        // Verify features were initialized
        $this->assertGreaterThan(0, $account->feature_flags);
        $enabledFeatures = $account->getEnabledFeatures();
        $this->assertNotEmpty($enabledFeatures);
    }

    public function test_feature_enum_loads_default_features()
    {
        $enabledFeatures = Feature::getEnabledByDefault();

        $this->assertNotEmpty($enabledFeatures);
        
        // Verify structure
        foreach ($enabledFeatures as $feature) {
            $this->assertArrayHasKey('name', $feature);
            $this->assertArrayHasKey('display_name', $feature);
            $this->assertArrayHasKey('enabled', $feature);
            $this->assertTrue($feature['enabled']);
        }
    }

    public function test_installation_config_stores_feature_defaults()
    {
        $config = InstallationConfig::where('name', 'ACCOUNT_LEVEL_FEATURE_DEFAULTS')->first();
        
        $this->assertNotNull($config);
        $this->assertEquals('array', $config->type);
        $this->assertTrue($config->locked);
        $this->assertIsArray($config->serialized_value);
        $this->assertNotEmpty($config->serialized_value);
    }

    public function test_onboarding_status_endpoint()
    {
        // Test when onboarding is pending
        $response = $this->getJson('/api/v1/installation/onboarding/status');
        $response->assertStatus(200)
            ->assertJson(['onboarding_pending' => true]);

        // Test when onboarding is completed
        Redis::del('chatwoot_installation_onboarding');
        $response = $this->getJson('/api/v1/installation/onboarding/status');
        $response->assertStatus(200)
            ->assertJson(['onboarding_pending' => false]);
    }
}