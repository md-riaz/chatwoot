<?php

namespace App\Services\Channels\Whatsapp;

use App\Models\Channels\Whatsapp;
use App\Services\Channels\Whatsapp\FacebookApiClient;
use Illuminate\Support\Facades\Log;

class WebhookSetupService
{
    private Whatsapp $channel;
    private string $wabaId;
    private string $accessToken;
    private FacebookApiClient $apiClient;

    public function __construct(Whatsapp $channel, string $wabaId, string $accessToken)
    {
        $this->channel = $channel;
        $this->wabaId = $wabaId;
        $this->accessToken = $accessToken;
        $this->apiClient = new FacebookApiClient($accessToken);
    }

    public function perform(): void
    {
        $this->validateParameters();

        // Register phone number if either condition is met:
        // 1. Phone number is not verified (code_verification_status != 'VERIFIED')
        // 2. Phone number needs registration (pending provisioning state)
        if (!$this->phoneNumberVerified() || $this->phoneNumberNeedsRegistration()) {
            $this->registerPhoneNumber();
        }

        $this->setupWebhook();
    }

    private function validateParameters(): void
    {
        if (empty($this->channel)) {
            throw new \InvalidArgumentException('Channel is required');
        }

        if (empty($this->wabaId)) {
            throw new \InvalidArgumentException('WABA ID is required');
        }

        if (empty($this->accessToken)) {
            throw new \InvalidArgumentException('Access token is required');
        }
    }

    private function registerPhoneNumber(): void
    {
        try {
            $phoneNumberId = $this->channel->provider_config['phone_number_id'];
            $pin = $this->fetchOrCreatePin();

            $this->apiClient->registerPhoneNumber($phoneNumberId, $pin);
            $this->storePin($pin);
        } catch (\Exception $e) {
            Log::warning('[WHATSAPP] Phone registration failed but continuing: ' . $e->getMessage());
            // Continue with webhook setup even if registration fails
            // This is just a warning, not a blocking error
        }
    }

    private function fetchOrCreatePin(): int
    {
        // Check if we have a stored PIN for this phone number
        $existingPin = $this->channel->provider_config['verification_pin'] ?? null;
        if ($existingPin) {
            return (int) $existingPin;
        }

        // Generate a new 6-digit PIN if none exists
        return random_int(100000, 999999);
    }

    private function storePin(int $pin): void
    {
        // Store the PIN in provider_config for future use
        $providerConfig = $this->channel->provider_config;
        $providerConfig['verification_pin'] = $pin;
        
        $this->channel->update(['provider_config' => $providerConfig]);
    }

    private function setupWebhook(): void
    {
        try {
            $callbackUrl = $this->buildCallbackUrl();
            $verifyToken = $this->channel->provider_config['webhook_verify_token'];

            $this->apiClient->subscribeWabaWebhook($this->wabaId, $callbackUrl, $verifyToken);
        } catch (\Exception $e) {
            Log::error('[WHATSAPP] Webhook setup failed: ' . $e->getMessage());
            throw new \Exception('Webhook setup failed: ' . $e->getMessage());
        }
    }

    private function buildCallbackUrl(): string
    {
        $frontendUrl = config('app.url');
        $phoneNumber = $this->channel->phone_number;

        return "{$frontendUrl}/webhooks/whatsapp/{$phoneNumber}";
    }

    private function phoneNumberVerified(): bool
    {
        try {
            $phoneNumberId = $this->channel->provider_config['phone_number_id'];

            // Check with WhatsApp API if the phone number code verification is complete
            // This checks code_verification_status == 'VERIFIED'
            $verified = $this->apiClient->phoneNumberVerified($phoneNumberId);
            Log::info("[WHATSAPP] Phone number {$phoneNumberId} code verification status: " . ($verified ? 'verified' : 'not verified'));

            return $verified;
        } catch (\Exception $e) {
            // If verification check fails, assume not verified to be safe
            Log::error('[WHATSAPP] Phone verification status check failed: ' . $e->getMessage());
            return false;
        }
    }

    private function phoneNumberNeedsRegistration(): bool
    {
        try {
            // Check if phone is in pending provisioning state based on health data
            // This is a separate check from phoneNumberVerified() which only checks code verification
            return $this->phoneNumberInPendingState();
        } catch (\Exception $e) {
            Log::error('[WHATSAPP] Phone registration check failed: ' . $e->getMessage());
            // Conservative approach: don't register if we can't determine the state
            return false;
        }
    }

    private function phoneNumberInPendingState(): bool
    {
        try {
            $healthService = new HealthService($this->channel);
            $healthData = $healthService->fetchHealthStatus();

            // Check if phone number is in "not provisioned" state based on health indicators
            // These conditions indicate the number is pending and needs registration:
            // - platform_type: "NOT_APPLICABLE" means not fully set up
            // - throughput.level: "NOT_APPLICABLE" means no messaging capacity assigned
            return $healthData['platform_type'] === 'NOT_APPLICABLE' ||
                   ($healthData['throughput']['level'] ?? null) === 'NOT_APPLICABLE';
        } catch (\Exception $e) {
            Log::error('[WHATSAPP] Health status check failed: ' . $e->getMessage());
            // If health check fails, assume registration is not needed to avoid errors
            return false;
        }
    }
}