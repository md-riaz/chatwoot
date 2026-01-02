<?php

namespace App\Services\Channels\Sms;

use App\Models\Channels\Sms;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SMS service for handling SMS operations.
 * Provides messaging functionality for SMS channels.
 * 
 * @see app/services/sms/send_on_sms_service.rb
 */
class SmsService
{
    protected Sms $channel;

    public function __construct(Sms $channel)
    {
        $this->channel = $channel;
    }

    /**
     * Send an SMS message.
     */
    public function sendMessage(string $to, string $message, array $options = []): array
    {
        try {
            if (!$this->channel->hasCompleteConfig()) {
                throw new \RuntimeException('SMS channel configuration is incomplete');
            }

            $messageId = $this->channel->sendTextMessage($to, $message);

            return [
                'success' => !empty($messageId),
                'message_id' => $messageId,
                'provider' => $this->channel->provider,
            ];
        } catch (\Exception $e) {
            Log::error('SMS service send failed', [
                'channel_id' => $this->channel->id,
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => $this->channel->provider,
            ];
        }
    }

    /**
     * Process incoming SMS webhook.
     */
    public function processIncomingWebhook(array $payload): array
    {
        try {
            // Parse Bandwidth webhook format
            $message = $this->parseBandwidthWebhook($payload);

            Log::info('SMS webhook processed', [
                'channel_id' => $this->channel->id,
                'from' => $message['from'] ?? null,
                'message_id' => $message['message_id'] ?? null,
            ]);

            return $message;
        } catch (\Exception $e) {
            Log::error('SMS webhook processing failed', [
                'channel_id' => $this->channel->id,
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            throw $e;
        }
    }

    /**
     * Parse Bandwidth webhook payload.
     */
    protected function parseBandwidthWebhook(array $payload): array
    {
        // Bandwidth webhook structure
        $message = $payload[0] ?? $payload;

        return [
            'from' => $message['from'] ?? null,
            'to' => $message['to'] ?? null,
            'text' => $message['text'] ?? null,
            'message_id' => $message['id'] ?? null,
            'timestamp' => $message['time'] ?? now()->toISOString(),
            'media' => $message['media'] ?? [],
            'direction' => $message['direction'] ?? 'in',
            'provider' => 'bandwidth',
        ];
    }

    /**
     * Get delivery status for a message.
     */
    public function getDeliveryStatus(string $messageId): array
    {
        try {
            $response = Http::withBasicAuth(
                $this->channel->provider_config['api_key'],
                $this->channel->provider_config['api_secret']
            )->get(
                $this->channel->getApiBasePath() . 
                "/users/{$this->channel->provider_config['account_id']}/messages/{$messageId}"
            );

            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'success' => true,
                    'status' => $data['direction'] ?? 'unknown',
                    'delivered_at' => $data['time'] ?? null,
                    'error' => $data['errorCode'] ?? null,
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to fetch delivery status',
            ];
        } catch (\Exception $e) {
            Log::error('SMS delivery status check failed', [
                'channel_id' => $this->channel->id,
                'message_id' => $messageId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Test the SMS configuration.
     */
    public function testConfiguration(): array
    {
        try {
            $isValid = $this->channel->validateProviderConfig();

            return [
                'success' => $isValid,
                'message' => $isValid ? 'SMS configuration is valid' : 'SMS configuration is invalid',
                'provider' => $this->channel->provider,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => $this->channel->provider,
            ];
        }
    }

    /**
     * Get supported message types.
     */
    public function getSupportedMessageTypes(): array
    {
        return [
            'text' => true,
            'media' => true,
            'location' => false,
            'contact' => false,
        ];
    }
}