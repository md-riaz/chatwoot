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
                    'intent_confidence' => $result['intentDetectionConfidence'] ?? 0,
                    'fulfillment_text' => $result['fulfillmentText'] ?? null,
                    'fulfillment_messages' => $result['fulfillmentMessages'] ?? [],
                    'parameters' => $result['parameters'] ?? [],
                    'all_required_params_present' => $result['allRequiredParamsPresent'] ?? false,
                    'language_code' => $result['languageCode'] ?? $languageCode,
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
     * Detect intent from event
     */
    public function detectIntentFromEvent(string $sessionId, string $eventName, array $parameters = [], string $languageCode = 'en'): array
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
                        'event' => [
                            'name' => $eventName,
                            'parameters' => $parameters,
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
                    'parameters' => $result['parameters'] ?? [],
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('error.message', 'Unknown error'),
            ];
        } catch (\Exception $e) {
            Log::error('Dialogflow detect intent from event failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get intents list
     */
    public function getIntents(): array
    {
        $this->authenticate();

        if (!$this->accessToken || !$this->projectId) {
            return [];
        }

        try {
            $url = "https://dialogflow.googleapis.com/v2/projects/{$this->projectId}/agent/intents";

            $response = Http::withToken($this->accessToken)->get($url);

            if ($response->successful()) {
                return array_map(function ($intent) {
                    return [
                        'name' => $intent['name'],
                        'display_name' => $intent['displayName'],
                        'priority' => $intent['priority'] ?? 0,
                        'is_fallback' => $intent['isFallback'] ?? false,
                        'training_phrases_count' => count($intent['trainingPhrases'] ?? []),
                    ];
                }, $response->json('intents', []));
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Dialogflow get intents failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get agent info
     */
    public function getAgent(): ?array
    {
        $this->authenticate();

        if (!$this->accessToken || !$this->projectId) {
            return null;
        }

        try {
            $url = "https://dialogflow.googleapis.com/v2/projects/{$this->projectId}/agent";

            $response = Http::withToken($this->accessToken)->get($url);

            if ($response->successful()) {
                $agent = $response->json();

                return [
                    'parent' => $agent['parent'],
                    'display_name' => $agent['displayName'],
                    'default_language_code' => $agent['defaultLanguageCode'],
                    'supported_language_codes' => $agent['supportedLanguageCodes'] ?? [],
                    'time_zone' => $agent['timeZone'],
                    'description' => $agent['description'] ?? null,
                    'avatar_uri' => $agent['avatarUri'] ?? null,
                ];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Dialogflow get agent failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Test connection
     */
    public function test(): array
    {
        $agent = $this->getAgent();

        if ($agent) {
            return [
                'success' => true,
                'agent' => $agent,
            ];
        }

        return [
            'success' => false,
            'error' => 'Failed to connect to Dialogflow',
        ];
    }

    /**
     * Process bot response for ClearLine
     */
    public function processResponse(array $dialogflowResponse, array $context = []): array
    {
        $messages = [];

        // Add fulfillment text as main message
        if (!empty($dialogflowResponse['fulfillment_text'])) {
            $messages[] = [
                'type' => 'text',
                'content' => $dialogflowResponse['fulfillment_text'],
            ];
        }

        // Process fulfillment messages (rich responses)
        foreach ($dialogflowResponse['fulfillment_messages'] ?? [] as $message) {
            if (isset($message['text'])) {
                foreach ($message['text']['text'] ?? [] as $text) {
                    $messages[] = [
                        'type' => 'text',
                        'content' => $text,
                    ];
                }
            }

            if (isset($message['quickReplies'])) {
                $messages[] = [
                    'type' => 'quick_replies',
                    'title' => $message['quickReplies']['title'] ?? '',
                    'options' => $message['quickReplies']['quickReplies'] ?? [],
                ];
            }

            if (isset($message['card'])) {
                $messages[] = [
                    'type' => 'card',
                    'title' => $message['card']['title'] ?? '',
                    'subtitle' => $message['card']['subtitle'] ?? '',
                    'image_url' => $message['card']['imageUri'] ?? null,
                    'buttons' => array_map(function ($button) {
                        return [
                            'title' => $button['text'],
                            'url' => $button['postback'] ?? null,
                        ];
                    }, $message['card']['buttons'] ?? []),
                ];
            }
        }

        return [
            'messages' => $messages,
            'intent' => $dialogflowResponse['intent'] ?? null,
            'confidence' => $dialogflowResponse['intent_confidence'] ?? 0,
            'handoff_required' => $this->shouldHandoff($dialogflowResponse),
        ];
    }

    /**
     * Check if handoff to human agent is required
     */
    protected function shouldHandoff(array $response): bool
    {
        $handoffIntents = ['fallback', 'agent_handoff', 'talk_to_human', 'human_agent'];

        $intent = strtolower($response['intent'] ?? '');

        foreach ($handoffIntents as $handoffIntent) {
            if (str_contains($intent, $handoffIntent)) {
                return true;
            }
        }

        // Low confidence threshold
        if (($response['intent_confidence'] ?? 0) < 0.3) {
            return true;
        }

        return false;
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
