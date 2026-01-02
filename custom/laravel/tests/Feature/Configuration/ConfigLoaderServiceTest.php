<?php

namespace Tests\Feature\Configuration;

use App\Models\InstallationConfig;
use App\Services\ConfigLoaderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ConfigLoaderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected string $testConfigPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testConfigPath = config_path('test_installation_config.yml');
    }

    protected function tearDown(): void
    {
        if (File::exists($this->testConfigPath)) {
            File::delete($this->testConfigPath);
        }
        parent::tearDown();
    }

    public function test_process_loads_configuration_from_yaml()
    {
        $yamlContent = "
- name: TEST_CONFIG_1
  display_title: 'Test Configuration 1'
  description: 'A test configuration'
  value: 'test_value'
  type: text
  locked: false

- name: TEST_CONFIG_2
  display_title: 'Test Configuration 2'
  value: 42
  type: integer
  locked: false
";

        File::put($this->testConfigPath, $yamlContent);

        // Mock the config path
        config(['app.config_path' => dirname($this->testConfigPath)]);

        $loader = new ConfigLoaderService();
        
        // Use reflection to test with custom path
        $reflection = new \ReflectionClass($loader);
        $method = $reflection->getMethod('loadInstallationConfig');
        $method->setAccessible(true);

        // Temporarily replace the config path in the method
        $originalMethod = $reflection->getMethod('loadInstallationConfig');
        $originalMethod->setAccessible(true);

        $results = $loader->process();

        $this->assertGreaterThan(0, $results['configs_loaded']);
        $this->assertEmpty($results['errors']);

        // Verify configurations were created
        $config1 = InstallationConfig::where('name', 'TEST_CONFIG_1')->first();
        $this->assertNotNull($config1);
        $this->assertEquals('test_value', $config1->value);
        $this->assertEquals('Test Configuration 1', $config1->display_title);

        $config2 = InstallationConfig::where('name', 'TEST_CONFIG_2')->first();
        $this->assertNotNull($config2);
        $this->assertEquals(42, $config2->value);
    }

    public function test_process_with_reconcile_only_new_option()
    {
        // Create existing configuration
        InstallationConfig::create([
            'name' => 'EXISTING_CONFIG',
            'serialized_value' => ['value' => 'existing_value'],
            'display_title' => 'Existing Config',
            'type' => 'text',
            'locked' => false,
        ]);

        $yamlContent = "
- name: EXISTING_CONFIG
  display_title: 'Updated Config'
  value: 'updated_value'
  type: text
  locked: false

- name: NEW_CONFIG
  display_title: 'New Config'
  value: 'new_value'
  type: text
  locked: false
";

        File::put($this->testConfigPath, $yamlContent);

        $loader = new ConfigLoaderService(['reconcile_only_new' => true]);
        $results = $loader->process();

        // Should skip existing config and only load new one
        $this->assertEquals(1, $results['configs_loaded']);
        $this->assertEquals(1, $results['configs_skipped']);

        // Verify existing config wasn't updated
        $existingConfig = InstallationConfig::where('name', 'EXISTING_CONFIG')->first();
        $this->assertEquals('existing_value', $existingConfig->value);
        $this->assertEquals('Existing Config', $existingConfig->display_title);

        // Verify new config was created
        $newConfig = InstallationConfig::where('name', 'NEW_CONFIG')->first();
        $this->assertNotNull($newConfig);
        $this->assertEquals('new_value', $newConfig->value);
    }

    public function test_process_with_environment_variable_migration()
    {
        putenv('TEST_ENV_CONFIG=env_value');

        $yamlContent = "
- name: TEST_ENV_CONFIG
  display_title: 'Test Env Config'
  description: 'Config from environment'
  type: text
  locked: false
";

        File::put($this->testConfigPath, $yamlContent);

        $loader = new ConfigLoaderService(['migrate_env_vars' => true]);
        $results = $loader->process();

        $this->assertGreaterThan(0, $results['configs_loaded']);

        // Verify environment variable was used
        $config = InstallationConfig::where('name', 'TEST_ENV_CONFIG')->first();
        $this->assertNotNull($config);
        $this->assertEquals('env_value', $config->value);

        // Clean up
        putenv('TEST_ENV_CONFIG');
    }

    public function test_process_skips_locked_configurations()
    {
        // Create locked configuration
        InstallationConfig::create([
            'name' => 'LOCKED_CONFIG',
            'serialized_value' => ['value' => 'locked_value'],
            'display_title' => 'Locked Config',
            'type' => 'text',
            'locked' => true,
        ]);

        $yamlContent = "
- name: LOCKED_CONFIG
  display_title: 'Updated Locked Config'
  value: 'new_value'
  type: text
  locked: true
";

        File::put($this->testConfigPath, $yamlContent);

        $loader = new ConfigLoaderService();
        $results = $loader->process();

        $this->assertEquals(1, $results['configs_skipped']);

        // Verify locked config wasn't updated
        $config = InstallationConfig::where('name', 'LOCKED_CONFIG')->first();
        $this->assertEquals('locked_value', $config->value);
        $this->assertEquals('Locked Config', $config->display_title);
    }

    public function test_validate_configuration_file()
    {
        $validYaml = "
- name: VALID_CONFIG
  display_title: 'Valid Configuration'
  value: 'test_value'
  type: text
  locked: false
";

        File::put($this->testConfigPath, $validYaml);

        $loader = new ConfigLoaderService();
        $errors = $loader->validate($this->testConfigPath);

        $this->assertEmpty($errors);
    }

    public function test_validate_invalid_configuration_file()
    {
        $invalidYaml = "
- display_title: 'Missing Name'
  value: 'test_value'
  type: text

- name: INVALID_TYPE
  value: 'test_value'
  type: invalid_type
";

        File::put($this->testConfigPath, $invalidYaml);

        $loader = new ConfigLoaderService();
        $errors = $loader->validate($this->testConfigPath);

        $this->assertNotEmpty($errors);
        $this->assertStringContains('missing \'name\' field', implode(' ', $errors));
        $this->assertStringContains('Invalid type \'invalid_type\'', implode(' ', $errors));
    }

    public function test_export_configuration_to_yaml()
    {
        // Create test configurations
        InstallationConfig::create([
            'name' => 'EXPORT_CONFIG_1',
            'serialized_value' => ['value' => 'export_value_1'],
            'display_title' => 'Export Config 1',
            'description' => 'First export config',
            'type' => 'text',
            'locked' => false,
        ]);

        InstallationConfig::create([
            'name' => 'EXPORT_CONFIG_2',
            'serialized_value' => ['value' => 42],
            'display_title' => 'Export Config 2',
            'description' => 'Second export config',
            'type' => 'integer',
            'locked' => true,
        ]);

        $loader = new ConfigLoaderService();
        $exportPath = $loader->export($this->testConfigPath);

        $this->assertEquals($this->testConfigPath, $exportPath);
        $this->assertTrue(File::exists($exportPath));

        $exportedContent = File::get($exportPath);
        $this->assertStringContains('EXPORT_CONFIG_1', $exportedContent);
        $this->assertStringContains('EXPORT_CONFIG_2', $exportedContent);
        $this->assertStringContains('export_value_1', $exportedContent);
        $this->assertStringContains('42', $exportedContent);
    }

    public function test_get_configuration_statistics()
    {
        // Create test configurations
        InstallationConfig::create([
            'name' => 'TEXT_CONFIG',
            'serialized_value' => ['value' => 'text_value'],
            'type' => 'text',
            'locked' => false,
        ]);

        InstallationConfig::create([
            'name' => 'BOOLEAN_CONFIG',
            'serialized_value' => ['value' => true],
            'type' => 'boolean',
            'locked' => true,
        ]);

        InstallationConfig::create([
            'name' => 'INTEGER_CONFIG',
            'serialized_value' => ['value' => 42],
            'type' => 'integer',
            'locked' => false,
        ]);

        $loader = new ConfigLoaderService();
        $stats = $loader->getStats();

        $this->assertEquals(3, $stats['total_configs']);
        $this->assertEquals(1, $stats['locked_configs']);
        $this->assertEquals(2, $stats['editable_configs']);

        $this->assertArrayHasKey('configs_by_type', $stats);
        $this->assertEquals(1, $stats['configs_by_type']['text']);
        $this->assertEquals(1, $stats['configs_by_type']['boolean']);
        $this->assertEquals(1, $stats['configs_by_type']['integer']);

        $this->assertArrayHasKey('groups', $stats);
        $this->assertIsArray($stats['groups']);
    }

    public function test_process_handles_yaml_parsing_errors()
    {
        $invalidYaml = "
invalid: yaml: content:
  - missing: proper
    structure
";

        File::put($this->testConfigPath, $invalidYaml);

        $loader = new ConfigLoaderService();
        $results = $loader->process();

        $this->assertNotEmpty($results['errors']);
        $this->assertStringContains('parse', implode(' ', $results['errors']));
    }

    public function test_process_loads_feature_flags()
    {
        $loader = new ConfigLoaderService(['load_features' => true]);
        $results = $loader->process();

        $this->assertEquals(1, $results['features_loaded']);

        // Verify feature defaults were created
        $defaultFeatures = InstallationConfig::getConfig('ACCOUNT_LEVEL_FEATURE_DEFAULTS', []);
        $this->assertIsArray($defaultFeatures);
        $this->assertNotEmpty($defaultFeatures);
    }

    public function test_process_skips_feature_flags_when_disabled()
    {
        $loader = new ConfigLoaderService(['load_features' => false]);
        $results = $loader->process();

        $this->assertEquals(0, $results['features_loaded']);
    }

    public function test_create_default_config_file_when_missing()
    {
        // Ensure config file doesn't exist
        if (File::exists(config_path('installation_config.yml'))) {
            File::delete(config_path('installation_config.yml'));
        }

        $loader = new ConfigLoaderService();
        $results = $loader->process();

        // Should create default config file and load configurations
        $this->assertTrue(File::exists(config_path('installation_config.yml')));
        $this->assertGreaterThan(0, $results['configs_loaded']);
    }
}