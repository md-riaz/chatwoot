<?php

namespace App\Services\Integrations;

use App\Models\Integration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DialogflowService
{
    protected string $apiUrl;
    protected ?string $projectId;
    protected ?string $credentials;
    protected ?string $accessToken;

    public function __construct(?Integration $integration = null)
    {
        if ($integration && $integration->type === 'dialogflow') {
            $this->projectId = $integration->settings['project_id'] ?? null;
            $this->credentials = $integration->credentials['service_account_json'] ?? null;
        }
    }

    /**
     * Detect intent from text
     */
    public function detectIntent(string $sessionId, string $text, string $languageCode = 'en'): array
    {
        $this->authenticate();

        if (!$this->accessToken || !$this->projectId) {
            return ['success' => false, 'error' => 'Dialogflow not configured'];
        }

        try {
            $url = "https://dialogflow.googleapis.com/v2/projects/{$this->projectId}/agent/sessions/{$sessionId}:detectIntent";

            $response = Http::withToken($this->accessToken)
                ->post($url, [
                    'queryInput' => [
                        'text' => [
                            'text' => $text,
                            'languageCode' => $languageCode,
                        ],
                    ],
                ]);

            if ($response->successful()) {
                $result = $response->json('queryResult');

                return [
                    'success' => true,
                    'intent' => $result['intent']['displayName'] ?? null,
                    'fulfillment_text' => $result['fulfillmentText'] ?? null,
                    'fulfillment_messages' => $result['fulfillmentMessages'] ?? [],
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('error.message', 'Unknown error'),
            ];
        } catch (\Exception $e) {
            Log::error('Dialogflow detect intent failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Authenticate with Google Cloud
     */
    protected function authenticate(): void
    {
        if ($this->accessToken) {
            return;
        }

        if (!$this->credentials) {
            return;
        }

        try {
            $credentials = json_decode($this->credentials, true);

            // Use service account JWT for authentication
            $jwt = $this->createJwt($credentials);

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->successful()) {
                $this->accessToken = $response->json('access_token');
            }
        } catch (\Exception $e) {
            Log::error('Dialogflow authentication failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Create JWT for service account authentication
     */
    protected function createJwt(array $credentials): string
    {
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT',
        ];

        $now = time();
        $payload = [
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/dialogflow',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ];

        $segments = [];
        $segments[] = $this->base64UrlEncode(json_encode($header));
        $segments[] = $this->base64UrlEncode(json_encode($payload));

        $signingInput = implode('.', $segments);

        openssl_sign($signingInput, $signature, $credentials['private_key'], 'SHA256');

        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    /**
     * Base64 URL encode
     */
    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
