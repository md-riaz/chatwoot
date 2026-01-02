<?php

namespace Tests\Feature\Configuration;

use App\Models\Account;
use App\Models\InstallationConfig;
use App\Services\FeatureFlagService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class FeatureFlagServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_get_feature_definitions()
    {
        $features = FeatureFlagService::getFeatureDefinitions();

        $this->assertIsArray($features);
        $this->assertArrayHasKey('shopify_integration', $features);
        $this->assertArrayHasKey('slack_integration', $features);

        // Check feature structure
        $shopifyFeature = $features['shopify_integration'];
        $this->assertArrayHasKey('display_name', $shopifyFeature);
        $this->assertArrayHasKey('description', $shopifyFeature);
        $this->assertArrayHasKey('enabled', $shopifyFeature);
        $this->assertArrayHasKey('premium', $shopifyFeature);
        $this->assertArrayHasKey('help_url', $shopifyFeature);

        // Check premium vs free features
        $this->assertTrue($shopifyFeature['premium']);
        $this->assertFalse($features['slack_integration']['premium']);
    }

    public function test_load_feature_defaults()
    {
        FeatureFlagService::loadFeatureDefaults();

        $defaultFeatures = InstallationConfig::getConfig('ACCOUNT_LEVEL_FEATURE_DEFAULTS', []);

        $this->assertIsArray($defaultFeatures);
        $this->assertContains('slack_integration', $defaultFeatures);
        $this->assertContains('team_management', $defaultFeatures);
        $this->assertNotContains('shopify_integration', $defaultFeatures); // Premium feature
    }

    public function test_assign_features_to_account()
    {
        // Load feature defaults first
        FeatureFlagService::loadFeatureDefaults();

        $account = Account::factory()->create([
            'features' => [],
        ]);

        FeatureFlagService::assignFeaturesToAccount($account);

        $account->refresh();
        $features = $account->features;

        $this->assertIsArray($features);
        $this->assertContains('slack_integration', $features);
        $this->assertContains('team_management', $features);
        $this->assertNotContains('shopify_integration', $features); // Premium feature
    }

    public function test_assign_features_preserves_existing()
    {
        // Load feature defaults first
        FeatureFlagService::loadFeatureDefaults();

        $account = Account::factory()->create([
            'features' => ['custom_existing_feature'],
        ]);

        FeatureFlagService::assignFeaturesToAccount($account);

        $account->refresh();
        $features = $account->features;

        $this->assertContains('custom_existing_feature', $features);
        $this->assertContains('slack_integration', $features);
    }

    public function test_is_feature_enabled()
    {
        $account = Account::factory()->create([
            'features' => ['slack_integration', 'team_management'],
        ]);

        $this->assertTrue(FeatureFlagService::isFeatureEnabled($account, 'slack_integration'));
        $this->assertTrue(FeatureFlagService::isFeatureEnabled($account, 'team_management'));
        $this->assertFalse(FeatureFlagService::isFeatureEnabled($account, 'shopify_integration'));
    }

    public function test_enable_feature_for_premium_account()
    {
        $account = Account::factory()->create([
            'features' => [],
        ]);

        // Mock premium account (in real implementation, this would check subscription)
        $this->assertTrue($account->isPremium());

        $result = FeatureFlagService::enableFeature($account, 'shopify_integration');

        $this->assertTrue($result);
        $account->refresh();
        $this->assertContains('shopify_integration', $account->features);
    }

    public function test_enable_feature_for_non_premium_account()
    {
        $account = Account::factory()->create([
            'features' => [],
        ]);

        // Mock non-premium account
        $account = $this->getMockBuilder(Account::class)
            ->onlyMethods(['isPremium'])
            ->setConstructorArgs([['features' => []]])
            ->getMock();
        
        $account->method('isPremium')->willReturn(false);

        $result = FeatureFlagService::enableFeature($account, 'shopify_integration');

        $this->assertFalse($result);
    }

    public function test_enable_free_feature()
    {
        $account = Account::factory()->create([
            'features' => [],
        ]);

        $result = FeatureFlagService::enableFeature($account, 'slack_integration');

        $this->assertTrue($result);
        $account->refresh();
        $this->assertContains('slack_integration', $account->features);
    }

    public function test_disable_feature()
    {
        $account = Account::factory()->create([
            'features' => ['slack_integration', 'team_management'],
        ]);

        $result = FeatureFlagService::disableFeature($account, 'slack_integration');

        $this->assertTrue($result);
        $account->refresh();
        $this->assertNotContains('slack_integration', $account->features);
        $this->assertContains('team_management', $account->features);
    }

    public function test_get_feature_metadata()
    {
        $metadata = FeatureFlagService::getFeatureMetadata('shopify_integration');

        $this->assertIsArray($metadata);
        $this->assertEquals('Shopify Integration', $metadata['display_name']);
        $this->assertEquals('Enable Shopify e-commerce integration', $metadata['description']);
        $this->assertTrue($metadata['premium']);
        $this->assertStringContains('shopify', $metadata['help_url']);
    }

    public function test_get_available_features_for_premium_account()
    {
        $account = Account::factory()->create([
            'features' => ['slack_integration'],
        ]);

        $availableFeatures = FeatureFlagService::getAvailableFeatures($account);

        $this->assertIsArray($availableFeatures);
        $this->assertArrayHasKey('shopify_integration', $availableFeatures); // Premium feature available
        $this->assertArrayHasKey('slack_integration', $availableFeatures);

        // Check enabled status
        $this->assertTrue($availableFeatures['slack_integration']['enabled_for_account']);
        $this->assertFalse($availableFeatures['shopify_integration']['enabled_for_account']);
    }

    public function test_get_enabled_features()
    {
        $account = Account::factory()->create([
            'features' => ['slack_integration', 'team_management'],
        ]);

        $enabledFeatures = FeatureFlagService::getEnabledFeatures($account);

        $this->assertIsArray($enabledFeatures);
        $this->assertArrayHasKey('slack_integration', $enabledFeatures);
        $this->assertArrayHasKey('team_management', $enabledFeatures);
        $this->assertArrayNotHasKey('shopify_integration', $enabledFeatures);

        // Check metadata is included
        $this->assertEquals('Slack Integration', $enabledFeatures['slack_integration']['display_name']);
    }

    public function test_bulk_update_account_features()
    {
        $account = Account::factory()->create([
            'features' => [],
        ]);

        $features = ['slack_integration', 'team_management', 'automation_rules'];

        $result = FeatureFlagService::updateAccountFeatures($account, $features);

        $this->assertTrue($result['success']);
        $this->assertEquals($features, $result['features']);
        $this->assertEmpty($result['errors']);

        $account->refresh();
        $this->assertEquals($features, $account->features);
    }

    public function test_bulk_update_with_invalid_features()
    {
        $account = Account::factory()->create([
            'features' => [],
        ]);

        $features = ['slack_integration', 'invalid_feature', 'team_management'];

        $result = FeatureFlagService::updateAccountFeatures($account, $features);

        $this->assertFalse($result['success']);
        $this->assertContains('Unknown feature: invalid_feature', $result['errors']);
        $this->assertEquals(['slack_integration', 'team_management'], $result['features']);
    }

    public function test_feature_caching()
    {
        $account = Account::factory()->create([
            'features' => ['slack_integration'],
        ]);

        // First call should cache the result
        $result1 = FeatureFlagService::isFeatureEnabled($account, 'slack_integration');
        $this->assertTrue($result1);

        // Verify cache was set
        $cacheKey = 'feature_flags:account:' . $account->id . ':slack_integration';
        $this->assertTrue(Cache::get($cacheKey));

        // Second call should use cache
        $result2 = FeatureFlagService::isFeatureEnabled($account, 'slack_integration');
        $this->assertTrue($result2);
    }

    public function test_clear_account_cache()
    {
        $account = Account::factory()->create([
            'features' => ['slack_integration'],
        ]);

        // Set up cache
        FeatureFlagService::isFeatureEnabled($account, 'slack_integration');

        // Clear cache
        FeatureFlagService::clearAccountCache($account);

        // Verify cache was cleared
        $cacheKey = 'feature_flags:account:' . $account->id;
        $this->assertNull(Cache::get($cacheKey));
    }

    public function test_feature_filtering_excludes_internal_features()
    {
        $account = Account::factory()->create();

        $availableFeatures = FeatureFlagService::getAvailableFeatures($account);

        // Should not contain any chatwoot_internal features
        foreach ($availableFeatures as $feature) {
            $this->assertFalse($feature['chatwoot_internal'] ?? false);
        }
    }
}