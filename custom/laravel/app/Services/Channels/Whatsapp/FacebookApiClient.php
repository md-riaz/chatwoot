<?php

namespace App\Services\Channels\Whatsapp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookApiClient
{
    private string $accessToken;
    private string $baseUrl;
    private string $apiVersion;

    public function __construct(string $accessToken)
    {
        $this->accessToken = $accessToken;
        $this->baseUrl = config('services.facebook.graph_url', 'https://graph.facebook.com');
        $this->apiVersion = config('services.facebook.graph_version', 'v15.0');
    }

    /**
     * Subscribe WABA webhook
     */
    public function subscribeWabaWebhook(string $wabaId, string $callbackUrl, string $verifyToken): array
    {
        $url = "{$this->baseUrl}/{$this->apiVersion}/{$wabaId}/subscribed_apps";
        
        $payload = [
            'override_callback_uri' => $callbackUrl,
            'verify_token' => $verifyToken,
        ];

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->post($url, $payload);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            Log::error('WhatsApp webhook subscription failed', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return [
                'success' => false,
                'error' => $response->json('error.message', 'Unknown error'),
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp webhook subscription exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Register phone number
     */
    public function registerPhoneNumber(string $phoneNumberId, int $pin): array
    {
        $url = "{$this->baseUrl}/{$this->apiVersion}/{$phoneNumberId}/register";
        
        $payload = [
            'messaging_product' => 'whatsapp',
            'pin' => $pin,
        ];

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->post($url, $payload);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            Log::error('WhatsApp phone registration failed', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return [
                'success' => false,
                'error' => $response->json('error.message', 'Unknown error'),
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp phone registration exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Check if phone number is verified
     */
    public function phoneNumberVerified(string $phoneNumberId): bool
    {
        $url = "{$this->baseUrl}/{$this->apiVersion}/{$phoneNumberId}";
        
        try {
            $response = Http::withHeaders($this->getHeaders())->get($url);

            if ($response->successful()) {
                $data = $response->json();
                return ($data['code_verification_status'] ?? '') === 'VERIFIED';
            }

            return false;
        } catch (\Exception $e) {
            Log::error('WhatsApp phone verification check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get phone number info
     */
    public function getPhoneNumberInfo(string $phoneNumberId): array
    {
        $url = "{$this->baseUrl}/{$this->apiVersion}/{$phoneNumberId}";
        
        try {
            $response = Http::withHeaders($this->getHeaders())->get($url);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            return [
                'success' => false,
                'error' => $response->json('error.message', 'Unknown error'),
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp phone info fetch failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get business account info
     */
    public function getBusinessAccountInfo(string $businessAccountId): array
    {
        $url = "{$this->baseUrl}/{$this->apiVersion}/{$businessAccountId}";
        
        try {
            $response = Http::withHeaders($this->getHeaders())->get($url);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            return [
                'success' => false,
                'error' => $response->json('error.message', 'Unknown error'),
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp business account info fetch failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Content-Type' => 'application/json',
        ];
    }
}