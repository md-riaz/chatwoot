<?php

namespace Tests\Unit\Models;

use App\Models\InstallationConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class InstallationConfigTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_value_attribute_accessor_and_mutator()
    {
        $config = new InstallationConfig();
        $config->value = 'test_value';

        $this->assertEquals('test_value', $config->value);
        $this->assertEquals(['value' => 'test_value'], $config->serialized_value);
    }

    public function test_type_casting_for_boolean_values()
    {
        $config = InstallationConfig::create([
            'name' => 'BOOLEAN_TEST',
            'serialized_value' => ['value' => 'true'],
            'type' => 'boolean',
        ]);

        $this->assertTrue($config->getTypeCastedValue());

        $config->value = 'false';
        $config->save();

        $this->assertFalse($config->getTypeCastedValue());

        $config->value = '1';
        $config->save();

        $this->assertTrue($config->getTypeCastedValue());

        $config->value = '0';
        $config->save();

        $this->assertFalse($config->getTypeCastedValue());
    }

    public function test_type_casting_for_integer_values()
    {
        $config = InstallationConfig::create([
            'name' => 'INTEGER_TEST',
            'serialized_value' => ['value' => '42'],
            'type' => 'integer',
        ]);

        $this->assertEquals(42, $config->getTypeCastedValue());
        $this->assertIsInt($config->getTypeCastedValue());
    }

    public function test_type_casting_for_float_values()
    {
        $config = InstallationConfig::create([
            'name' => 'FLOAT_TEST',
            'serialized_value' => ['value' => '3.14'],
            'type' => 'float',
        ]);

        $this->assertEquals(3.14, $config->getTypeCastedValue());
        $this->assertIsFloat($config->getTypeCastedValue());
    }

    public function test_type_casting_for_array_values()
    {
        $config = InstallationConfig::create([
            'name' => 'ARRAY_TEST',
            'serialized_value' => ['value' => ['item1', 'item2', 'item3']],
            'type' => 'array',
        ]);

        $result = $config->getTypeCastedValue();
        $this->assertEquals(['item1', 'item2', 'item3'], $result);
        $this->assertIsArray($result);

        // Test JSON string array
        $config->value = '["json1", "json2"]';
        $config->save();

        $result = $config->getTypeCastedValue();
        $this->assertEquals(['json1', 'json2'], $result);
    }

    public function test_type_casting_for_select_values()
    {
        $config = InstallationConfig::create([
            'name' => 'SELECT_TEST',
            'serialized_value' => ['value' => 'option1'],
            'type' => 'select',
            'options' => ['option1', 'option2', 'option3'],
        ]);

        $this->assertEquals('option1', $config->getTypeCastedValue());
    }

    public function test_type_casting_for_secret_values()
    {
        $config = InstallationConfig::create([
            'name' => 'SECRET_TEST',
            'serialized_value' => ['value' => 'secret_value'],
            'type' => 'secret',
        ]);

        $this->assertEquals('secret_value', $config->getTypeCastedValue());
    }

    public function test_type_casting_for_text_values()
    {
        $config = InstallationConfig::create([
            'name' => 'TEXT_TEST',
            'serialized_value' => ['value' => 'text_value'],
            'type' => 'text',
        ]);

        $this->assertEquals('text_value', $config->getTypeCastedValue());
        $this->assertIsString($config->getTypeCastedValue());
    }

    public function test_type_casting_returns_null_for_null_values()
    {
        $config = InstallationConfig::create([
            'name' => 'NULL_TEST',
            'serialized_value' => ['value' => null],
            'type' => 'text',
        ]);

        $this->assertNull($config->getTypeCastedValue());
    }

    public function test_validate_value_for_boolean_type()
    {
        $config = new InstallationConfig(['type' => 'boolean']);

        $this->assertTrue($config->validateValue(true));
        $this->assertTrue($config->validateValue(false));
        $this->assertTrue($config->validateValue('true'));
        $this->assertTrue($config->validateValue('false'));
        $this->assertTrue($config->validateValue('1'));
        $this->assertTrue($config->validateValue('0'));
        $this->assertTrue($config->validateValue(1));
        $this->assertTrue($config->validateValue(0));
        $this->assertFalse($config->validateValue('invalid'));
    }

    public function test_validate_value_for_integer_type()
    {
        $config = new InstallationConfig(['type' => 'integer']);

        $this->assertTrue($config->validateValue(42));
        $this->assertTrue($config->validateValue('42'));
        $this->assertFalse($config->validateValue('42.5'));
        $this->assertFalse($config->validateValue('not_a_number'));
    }

    public function test_validate_value_for_float_type()
    {
        $config = new InstallationConfig(['type' => 'float']);

        $this->assertTrue($config->validateValue(3.14));
        $this->assertTrue($config->validateValue('3.14'));
        $this->assertTrue($config->validateValue(42));
        $this->assertTrue($config->validateValue('42'));
        $this->assertFalse($config->validateValue('not_a_number'));
    }

    public function test_validate_value_for_array_type()
    {
        $config = new InstallationConfig(['type' => 'array']);

        $this->assertTrue($config->validateValue(['item1', 'item2']));
        $this->assertTrue($config->validateValue('["item1", "item2"]'));
        $this->assertFalse($config->validateValue(42));
    }

    public function test_validate_value_for_select_type()
    {
        $config = new InstallationConfig([
            'type' => 'select',
            'options' => ['option1', 'option2', 'option3'],
        ]);

        $this->assertTrue($config->validateValue('option1'));
        $this->assertTrue($config->validateValue('option2'));
        $this->assertFalse($config->validateValue('invalid_option'));
    }

    public function test_validate_value_for_text_type()
    {
        $config = new InstallationConfig(['type' => 'text']);

        $this->assertTrue($config->validateValue('any string'));
        $this->assertTrue($config->validateValue(''));
        $this->assertTrue($config->validateValue(42)); // Will be cast to string
    }

    public function test_editable_scope()
    {
        InstallationConfig::create([
            'name' => 'EDITABLE_CONFIG',
            'serialized_value' => ['value' => 'editable'],
            'locked' => false,
        ]);

        InstallationConfig::create([
            'name' => 'LOCKED_CONFIG',
            'serialized_value' => ['value' => 'locked'],
            'locked' => true,
        ]);

        $editableConfigs = InstallationConfig::editable()->get();

        $this->assertCount(1, $editableConfigs);
        $this->assertEquals('EDITABLE_CONFIG', $editableConfigs->first()->name);
    }

    public function test_get_config_static_method()
    {
        InstallationConfig::create([
            'name' => 'TEST_GET_CONFIG',
            'serialized_value' => ['value' => 'test_value'],
            'type' => 'text',
        ]);

        $value = InstallationConfig::getConfig('TEST_GET_CONFIG', 'default_value');
        $this->assertEquals('test_value', $value);

        $defaultValue = InstallationConfig::getConfig('NON_EXISTENT_CONFIG', 'default_value');
        $this->assertEquals('default_value', $defaultValue);
    }

    public function test_set_config_static_method()
    {
        $config = InstallationConfig::setConfig('NEW_CONFIG', 'new_value', true);

        $this->assertInstanceOf(InstallationConfig::class, $config);
        $this->assertEquals('NEW_CONFIG', $config->name);
        $this->assertEquals('new_value', $config->value);
        $this->assertTrue($config->locked);

        // Test updating existing config
        $updatedConfig = InstallationConfig::setConfig('NEW_CONFIG', 'updated_value', false);

        $this->assertEquals($config->id, $updatedConfig->id);
        $this->assertEquals('updated_value', $updatedConfig->value);
        $this->assertFalse($updatedConfig->locked);
    }

    public function test_get_config_with_metadata_static_method()
    {
        InstallationConfig::create([
            'name' => 'METADATA_TEST',
            'serialized_value' => ['value' => 'test_value'],
            'display_title' => 'Test Configuration',
            'description' => 'A test configuration',
            'type' => 'text',
            'locked' => false,
            'options' => ['option1', 'option2'],
        ]);

        $result = InstallationConfig::getConfigWithMetadata('METADATA_TEST');

        $this->assertEquals([
            'name' => 'METADATA_TEST',
            'value' => 'test_value',
            'display_title' => 'Test Configuration',
            'description' => 'A test configuration',
            'type' => 'text',
            'locked' => false,
            'options' => ['option1', 'option2'],
        ], $result);

        $nullResult = InstallationConfig::getConfigWithMetadata('NON_EXISTENT');
        $this->assertNull($nullResult);
    }

    public function test_get_config_groups_static_method()
    {
        $groups = InstallationConfig::getConfigGroups();

        $this->assertIsArray($groups);
        $this->assertArrayHasKey('general', $groups);
        $this->assertArrayHasKey('facebook', $groups);
        $this->assertArrayHasKey('shopify', $groups);

        // Check that groups contain expected configurations
        $this->assertContains('ENABLE_ACCOUNT_SIGNUP', $groups['general']);
        $this->assertContains('FB_APP_ID', $groups['facebook']);
        $this->assertContains('SHOPIFY_CLIENT_ID', $groups['shopify']);
    }

    public function test_get_default_configurations_static_method()
    {
        $defaults = InstallationConfig::getDefaultConfigurations();

        $this->assertIsArray($defaults);
        $this->assertNotEmpty($defaults);

        // Check structure of first default config
        $firstConfig = $defaults[0];
        $this->assertArrayHasKey('name', $firstConfig);
        $this->assertArrayHasKey('display_title', $firstConfig);
        $this->assertArrayHasKey('description', $firstConfig);
        $this->assertArrayHasKey('value', $firstConfig);
        $this->assertArrayHasKey('type', $firstConfig);
        $this->assertArrayHasKey('locked', $firstConfig);

        // Check specific default configurations
        $signupConfig = collect($defaults)->firstWhere('name', 'ENABLE_ACCOUNT_SIGNUP');
        $this->assertNotNull($signupConfig);
        $this->assertEquals('boolean', $signupConfig['type']);
        $this->assertFalse($signupConfig['value']);
    }

    public function test_cache_clearing_on_save()
    {
        // Set up cache
        Cache::put('installation_configs', 'cached_data', 3600);
        Cache::put('global_config:TEST_CACHE', 'cached_value', 3600);

        $config = InstallationConfig::create([
            'name' => 'TEST_CACHE',
            'serialized_value' => ['value' => 'test_value'],
        ]);

        // Cache should be cleared after save
        $this->assertNull(Cache::get('installation_configs'));
    }

    public function test_cache_clearing_on_delete()
    {
        $config = InstallationConfig::create([
            'name' => 'DELETE_TEST',
            'serialized_value' => ['value' => 'test_value'],
        ]);

        // Set up cache
        Cache::put('installation_configs', 'cached_data', 3600);
        Cache::put('global_config:DELETE_TEST', 'cached_value', 3600);

        $config->delete();

        // Cache should be cleared after delete
        $this->assertNull(Cache::get('installation_configs'));
        $this->assertNull(Cache::get('global_config:DELETE_TEST'));
    }

    public function test_configuration_types_constant()
    {
        $types = InstallationConfig::TYPES;

        $this->assertIsArray($types);
        $this->assertArrayHasKey('text', $types);
        $this->assertArrayHasKey('boolean', $types);
        $this->assertArrayHasKey('integer', $types);
        $this->assertArrayHasKey('float', $types);
        $this->assertArrayHasKey('array', $types);
        $this->assertArrayHasKey('select', $types);
        $this->assertArrayHasKey('secret', $types);
        $this->assertArrayHasKey('code', $types);
    }

    public function test_fillable_attributes()
    {
        $config = new InstallationConfig();
        $fillable = $config->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('serialized_value', $fillable);
        $this->assertContains('locked', $fillable);
        $this->assertContains('display_title', $fillable);
        $this->assertContains('description', $fillable);
        $this->assertContains('type', $fillable);
        $this->assertContains('options', $fillable);
    }

    public function test_casts_attributes()
    {
        $config = new InstallationConfig();
        $casts = $config->getCasts();

        $this->assertEquals('array', $casts['serialized_value']);
        $this->assertEquals('boolean', $casts['locked']);
        $this->assertEquals('array', $casts['options']);
    }
}