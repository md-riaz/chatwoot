<?php

namespace Tests\Feature\Configuration;

use App\Models\InstallationConfig;
use App\Services\GlobalConfigService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class GlobalConfigServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_get_multiple_configurations_with_caching()
    {
        // Create test configurations
        InstallationConfig::create([
            'name' => 'TEST_CONFIG_1',
            'serialized_value' => ['value' => 'test_value_1'],
            'type' => 'text',
        ]);

        InstallationConfig::create([
            'name' => 'TEST_CONFIG_2',
            'serialized_value' => ['value' => 42],
            'type' => 'integer',
        ]);

        // First call should hit database
        $result = GlobalConfigService::get(['TEST_CONFIG_1', 'TEST_CONFIG_2']);

        $this->assertEquals([
            'TEST_CONFIG_1' => 'test_value_1',
            'TEST_CONFIG_2' => 42,
        ], $result);

        // Verify cache was set
        $this->assertEquals('test_value_1', Cache::get('global_config:TEST_CONFIG_1'));
        $this->assertEquals(42, Cache::get('global_config:TEST_CONFIG_2'));
    }

    public function test_load_with_environment_variable_fallback()
    {
        // Set environment variable
        putenv('TEST_ENV_CONFIG=env_value');

        // Load configuration that doesn't exist in database
        $result = GlobalConfigService::load('TEST_ENV_CONFIG', 'default_value');

        $this->assertEquals('env_value', $result);

        // Verify InstallationConfig was created
        $config = InstallationConfig::where('name', 'TEST_ENV_CONFIG')->first();
        $this->assertNotNull($config);
        $this->assertEquals('env_value', $config->value);

        // Clean up
        putenv('TEST_ENV_CONFIG');
    }

    public function test_load_returns_default_when_no_config_or_env()
    {
        $result = GlobalConfigService::load('NON_EXISTENT_CONFIG', 'default_value');

        $this->assertEquals('default_value', $result);
    }

    public function test_set_configuration_and_update_cache()
    {
        $result = GlobalConfigService::set('NEW_CONFIG', 'new_value', true);

        $this->assertTrue($result);

        // Verify database
        $config = InstallationConfig::where('name', 'NEW_CONFIG')->first();
        $this->assertNotNull($config);
        $this->assertEquals('new_value', $config->value);
        $this->assertTrue($config->locked);

        // Verify cache
        $this->assertEquals('new_value', Cache::get('global_config:NEW_CONFIG'));
    }

    public function test_clear_cache()
    {
        // Set up cache
        Cache::put('global_config:TEST_KEY', 'test_value', 3600);

        GlobalConfigService::clearCache('TEST_KEY');

        $this->assertNull(Cache::get('global_config:TEST_KEY'));
    }

    public function test_get_with_metadata()
    {
        InstallationConfig::create([
            'name' => 'TEST_METADATA',
            'serialized_value' => ['value' => 'test_value'],
            'display_title' => 'Test Configuration',
            'description' => 'A test configuration',
            'type' => 'text',
            'locked' => false,
            'options' => ['option1', 'option2'],
        ]);

        $result = GlobalConfigService::getWithMetadata('TEST_METADATA');

        $this->assertEquals([
            'name' => 'TEST_METADATA',
            'value' => 'test_value',
            'display_title' => 'Test Configuration',
            'description' => 'A test configuration',
            'type' => 'text',
            'locked' => false,
            'options' => ['option1', 'option2'],
        ], $result);
    }

    public function test_batch_update_configurations()
    {
        $configs = [
            'BATCH_CONFIG_1' => 'value1',
            'BATCH_CONFIG_2' => 'value2',
            'BATCH_CONFIG_3' => 'value3',
        ];

        $results = GlobalConfigService::batchUpdate($configs);

        $this->assertEquals([
            'BATCH_CONFIG_1' => true,
            'BATCH_CONFIG_2' => true,
            'BATCH_CONFIG_3' => true,
        ], $results);

        // Verify database
        foreach ($configs as $key => $value) {
            $config = InstallationConfig::where('name', $key)->first();
            $this->assertNotNull($config);
            $this->assertEquals($value, $config->value);
        }
    }

    public function test_get_all_grouped_configurations()
    {
        // Create configurations for different groups
        InstallationConfig::create([
            'name' => 'ENABLE_ACCOUNT_SIGNUP',
            'serialized_value' => ['value' => true],
            'display_title' => 'Enable Account Signup',
            'type' => 'boolean',
        ]);

        InstallationConfig::create([
            'name' => 'FB_APP_ID',
            'serialized_value' => ['value' => 'test_app_id'],
            'display_title' => 'Facebook App ID',
            'type' => 'text',
        ]);

        $result = GlobalConfigService::getAllGrouped();

        $this->assertArrayHasKey('general', $result);
        $this->assertArrayHasKey('facebook', $result);

        // Check general group
        $generalConfigs = collect($result['general']);
        $signupConfig = $generalConfigs->firstWhere('name', 'ENABLE_ACCOUNT_SIGNUP');
        $this->assertNotNull($signupConfig);
        $this->assertTrue($signupConfig['value']);

        // Check facebook group
        $facebookConfigs = collect($result['facebook']);
        $fbConfig = $facebookConfigs->firstWhere('name', 'FB_APP_ID');
        $this->assertNotNull($fbConfig);
        $this->assertEquals('test_app_id', $fbConfig['value']);
    }

    public function test_type_casting_in_get_method()
    {
        // Create configurations with different types
        InstallationConfig::create([
            'name' => 'BOOLEAN_CONFIG',
            'serialized_value' => ['value' => 'true'],
            'type' => 'boolean',
        ]);

        InstallationConfig::create([
            'name' => 'INTEGER_CONFIG',
            'serialized_value' => ['value' => '42'],
            'type' => 'integer',
        ]);

        InstallationConfig::create([
            'name' => 'ARRAY_CONFIG',
            'serialized_value' => ['value' => ['item1', 'item2']],
            'type' => 'array',
        ]);

        $result = GlobalConfigService::get(['BOOLEAN_CONFIG', 'INTEGER_CONFIG', 'ARRAY_CONFIG']);

        $this->assertTrue($result['BOOLEAN_CONFIG']);
        $this->assertEquals(42, $result['INTEGER_CONFIG']);
        $this->assertEquals(['item1', 'item2'], $result['ARRAY_CONFIG']);
    }

    public function test_caching_performance()
    {
        // Create test configuration
        InstallationConfig::create([
            'name' => 'PERFORMANCE_TEST',
            'serialized_value' => ['value' => 'test_value'],
            'type' => 'text',
        ]);

        // First call - should hit database
        $start = microtime(true);
        $result1 = GlobalConfigService::get(['PERFORMANCE_TEST']);
        $firstCallTime = microtime(true) - $start;

        // Second call - should hit cache
        $start = microtime(true);
        $result2 = GlobalConfigService::get(['PERFORMANCE_TEST']);
        $secondCallTime = microtime(true) - $start;

        // Results should be identical
        $this->assertEquals($result1, $result2);

        // Second call should be significantly faster (cached)
        $this->assertLessThan($firstCallTime, $secondCallTime);
    }
}