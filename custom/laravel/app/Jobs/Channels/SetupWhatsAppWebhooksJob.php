<?php

namespace App\Jobs\Channels;

use App\Models\Channels\Whatsapp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SetupWhatsAppWebhooksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private Whatsapp $channel
    ) {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        try {
            Log::info('Starting WhatsApp webhook setup', [
                'channel_id' => $this->channel->id,
                'provider' => $this->channel->provider
            ]);

            if ($this->channel->provider === Whatsapp::PROVIDER_CLOUD) {
                $this->setupCloudWebhooks();
            } else {
                $this->setup360DialogWebhooks();
            }

            Log::info('WhatsApp webhook setup completed', [
                'channel_id' => $this->channel->id
            ]);

        } catch (\Exception $e) {
            Log::error('WhatsApp webhook setup failed', [
                'channel_id' => $this->channel->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Mark as authorization error if it's a credentials issue
            if ($this->isAuthorizationError($e)) {
                $this->channel->authorizationError();
            }

            throw $e;
        }
    }

    private function setupCloudWebhooks(): void
    {
        $config = $this->channel->provider_config;
        $businessAccountId = $config['business_account_id'];
        $accessToken = $config['api_key'];
        
        $webhookUrl = config('app.url') . "/webhooks/whatsapp/{$this->channel->phone_number}";
        $verifyToken = $config['webhook_verify_token'];

        // Subscribe to webhook events
        $response = Http::withToken($accessToken)
            ->post("https://graph.facebook.com/v18.0/{$businessAccountId}/subscribed_apps", [
                'subscribed_fields' => 'messages'
            ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to subscribe to WhatsApp webhook events: ' . $response->body());
        }

        Log::info('WhatsApp Cloud webhook subscribed successfully', [
            'channel_id' => $this->channel->id,
            'webhook_url' => $webhookUrl
        ]);
    }

    private function setup360DialogWebhooks(): void
    {
        $config = $this->channel->provider_config;
        $apiKey = $config['api_key'];
        
        $webhookUrl = config('app.url') . "/webhooks/whatsapp/{$this->channel->phone_number}";
        $baseUrl = config('services.360dialog.base_url', 'https://waba.360dialog.io/v1');

        $response = Http::withHeaders([
            'D360-API-KEY' => $apiKey,
            'Content-Type' => 'application/json'
        ])->post("{$baseUrl}/configs/webhook", [
            'url' => $webhookUrl
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to setup 360Dialog webhook: ' . $response->body());
        }

        Log::info('360Dialog webhook setup successfully', [
            'channel_id' => $this->channel->id,
            'webhook_url' => $webhookUrl
        ]);
    }

    private function isAuthorizationError(\Exception $e): bool
    {
        $message = strtolower($e->getMessage());
        
        return str_contains($message, 'unauthorized') ||
               str_contains($message, 'invalid token') ||
               str_contains($message, 'access denied') ||
               str_contains($message, 'authentication');
    }
}