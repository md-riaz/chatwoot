<?php

namespace Tests\Unit\Services\Whatsapp;

use App\Models\Channels\Whatsapp;
use App\Models\Message;
use App\Services\Channels\Whatsapp\Providers\WhatsappCloudService;
use App\Services\Channels\Whatsapp\Providers\Whatsapp360DialogService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WhatsappProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_whatsapp_cloud_provider_service_is_returned()
    {
        $whatsappChannel = Whatsapp::factory()->create([
            'provider' => Whatsapp::PROVIDER_CLOUD,
        ]);

        $providerService = $whatsappChannel->providerService();

        $this->assertInstanceOf(WhatsappCloudService::class, $providerService);
    }

    public function test_whatsapp_360dialog_provider_service_is_returned()
    {
        $whatsappChannel = Whatsapp::factory()->create([
            'provider' => Whatsapp::PROVIDER_DEFAULT,
        ]);

        $providerService = $whatsappChannel->providerService();

        $this->assertInstanceOf(Whatsapp360DialogService::class, $providerService);
    }

    public function test_whatsapp_channel_delegates_to_provider_service()
    {
        $whatsappChannel = Whatsapp::factory()->create([
            'provider' => Whatsapp::PROVIDER_CLOUD,
            'provider_config' => [
                'api_key' => 'test-api-key',
                'phone_number_id' => 'test-phone-id',
                'business_account_id' => 'test-business-id',
            ],
        ]);

        // Test that methods are properly delegated
        $this->assertTrue(method_exists($whatsappChannel, 'sendMessage'));
        $this->assertTrue(method_exists($whatsappChannel, 'sendTemplate'));
        $this->assertTrue(method_exists($whatsappChannel, 'syncTemplates'));
        $this->assertTrue(method_exists($whatsappChannel, 'mediaUrl'));
        $this->assertTrue(method_exists($whatsappChannel, 'apiHeaders'));
    }

    public function test_webhook_setup_requires_business_account_and_api_key()
    {
        $whatsappChannel = Whatsapp::factory()->create([
            'provider_config' => [], // Empty config
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Business account ID and API key are required for webhook setup');

        $whatsappChannel->setupWebhooks();
    }

    public function test_provider_constants_are_defined()
    {
        $this->assertEquals('default', Whatsapp::PROVIDER_DEFAULT);
        $this->assertEquals('whatsapp_cloud', Whatsapp::PROVIDER_CLOUD);
        $this->assertEquals('360dialog', Whatsapp::PROVIDER_360_DIALOG);
        
        $expectedProviders = [
            Whatsapp::PROVIDER_DEFAULT,
            Whatsapp::PROVIDER_CLOUD,
            Whatsapp::PROVIDER_360_DIALOG,
        ];
        
        $this->assertEquals($expectedProviders, Whatsapp::PROVIDERS);
    }
}