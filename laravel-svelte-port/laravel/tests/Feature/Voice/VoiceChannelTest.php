<?php

namespace Tests\Feature\Voice;

use App\Models\Account;
use App\Models\Channels\Voice;
use App\Models\Inbox;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoiceChannelTest extends TestCase
{
    use RefreshDatabase;

    public function test_voice_channel_creation()
    {
        $account = Account::factory()->create();
        
        $voice = Voice::factory()->demo()->create([
            'account_id' => $account->id,
            'phone_number' => '+15551234567',
        ]);

        $this->assertDatabaseHas('channel_voice', [
            'account_id' => $account->id,
            'phone_number' => '+15551234567',
            'provider' => 'twilio',
        ]);

        $this->assertTrue($voice->isTwilio());
        $this->assertEquals('Voice (+15551234567)', $voice->getName());
        $this->assertFalse($voice->messagingWindowEnabled());
    }

    public function test_voice_channel_relationships()
    {
        $account = Account::factory()->create();
        $voice = Voice::factory()->demo()->create(['account_id' => $account->id]);
        
        $inbox = Inbox::factory()->create([
            'account_id' => $account->id,
            'channel_type' => Voice::class,
            'channel_id' => $voice->id,
        ]);

        $this->assertEquals($account->id, $voice->account->id);
        $this->assertEquals($inbox->id, $voice->inbox->id);
    }

    public function test_voice_webhook_urls()
    {
        $voice = Voice::factory()->demo()->create([
            'phone_number' => '+15551234567',
        ]);

        $callUrl = $voice->voiceCallWebhookUrl();
        $statusUrl = $voice->voiceStatusWebhookUrl();

        $this->assertStringContains('voice/call/15551234567', $callUrl);
        $this->assertStringContains('voice/status/15551234567', $statusUrl);
    }

    public function test_voice_channel_validation()
    {
        $rules = Voice::validationRules();

        $this->assertArrayHasKey('phone_number', $rules);
        $this->assertArrayHasKey('provider', $rules);
        $this->assertArrayHasKey('provider_config', $rules);
        
        $this->assertStringContains('required', $rules['phone_number']);
        $this->assertStringContains('unique', $rules['phone_number']);
        $this->assertStringContains('regex', $rules['phone_number']);
    }

    public function test_provider_config_hash()
    {
        $config = [
            'account_sid' => 'test_sid',
            'auth_token' => 'test_token',
        ];

        $voice = Voice::factory()->create([
            'provider_config' => $config,
        ]);

        $this->assertEquals($config, $voice->providerConfigHash());
    }

    public function test_initiate_call_placeholder()
    {
        $voice = Voice::factory()->demo()->create();
        
        $result = $voice->initiateCall('+15559876543');

        $this->assertArrayHasKey('call_id', $result);
        $this->assertArrayHasKey('status', $result);
        $this->assertEquals('initiated', $result['status']);
        $this->assertEquals('+15559876543', $result['to']);
        $this->assertEquals($voice->phone_number, $result['from']);
    }
}