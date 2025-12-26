<?php

namespace App\Services\Channels\Twitter;

use App\Models\Inbox;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwitterService
{
    protected string $apiUrl = 'https://api.twitter.com/2';
    protected ?string $bearerToken;
    protected ?string $accessToken;
    protected ?string $accessTokenSecret;
    protected ?string $consumerKey;
    protected ?string $consumerSecret;

    public function __construct(?Inbox $inbox = null)
    {
        if ($inbox && $inbox->channel) {
            $this->bearerToken = $inbox->channel->bearer_token ?? null;
            $this->accessToken = $inbox->channel->access_token ?? null;
            $this->accessTokenSecret = $inbox->channel->access_token_secret ?? null;
        }

        $this->consumerKey = config('services.twitter.client_id');
        $this->consumerSecret = config('services.twitter.client_secret');
    }

    /**
     * Send a direct message
     */
    public function sendDirectMessage(string $recipientId, string $text): array
    {
        try {
            $response = Http::withToken($this->bearerToken)
                ->post("{$this->apiUrl}/dm_conversations/with/{$recipientId}/messages", [
                    'text' => $text,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json('data'),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('errors.0.message', 'Unknown error'),
            ];
        } catch (\Exception $e) {
            Log::error('Twitter send DM failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send a direct message with media
     */
    public function sendDirectMessageWithMedia(string $recipientId, string $text, string $mediaId): array
    {
        try {
            $response = Http::withToken($this->bearerToken)
                ->post("{$this->apiUrl}/dm_conversations/with/{$recipientId}/messages", [
                    'text' => $text,
                    'attachments' => [
                        ['media_id' => $mediaId],
                    ],
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json('data'),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('errors.0.message', 'Unknown error'),
            ];
        } catch (\Exception $e) {
            Log::error('Twitter send DM with media failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get user by ID
     */
    public function getUser(string $userId): ?array
    {
        try {
            $response = Http::withToken($this->bearerToken)
                ->get("{$this->apiUrl}/users/{$userId}", [
                    'user.fields' => 'id,name,username,profile_image_url,description,location,url',
                ]);

            if ($response->successful()) {
                return $response->json('data');
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Twitter get user failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get user by username
     */
    public function getUserByUsername(string $username): ?array
    {
        try {
            $response = Http::withToken($this->bearerToken)
                ->get("{$this->apiUrl}/users/by/username/{$username}", [
                    'user.fields' => 'id,name,username,profile_image_url,description,location,url',
                ]);

            if ($response->successful()) {
                return $response->json('data');
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Twitter get user by username failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get DM events
     */
    public function getDmEvents(string $participantId = null, int $maxResults = 100): array
    {
        try {
            $params = [
                'dm_event.fields' => 'id,text,created_at,sender_id,dm_conversation_id,attachments,referenced_tweets',
                'max_results' => $maxResults,
            ];

            if ($participantId) {
                $params['participant_id'] = $participantId;
            }

            $response = Http::withToken($this->bearerToken)
                ->get("{$this->apiUrl}/dm_events", $params);

            if ($response->successful()) {
                return $response->json('data', []);
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Twitter get DM events failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Handle CRC challenge for webhook validation
     */
    public function handleCrcChallenge(string $crcToken): string
    {
        $hash = hash_hmac('sha256', $crcToken, $this->consumerSecret, true);
        return 'sha256=' . base64_encode($hash);
    }

    /**
     * Process incoming webhook
     */
    public function processWebhook(array $payload): array
    {
        $result = [];

        // Process direct message events
        if (isset($payload['direct_message_events'])) {
            foreach ($payload['direct_message_events'] as $event) {
                if ($event['type'] === 'message_create') {
                    $result['messages'][] = $this->parseDirectMessage($event, $payload['users'] ?? []);
                }
            }
        }

        // Process tweet create events (mentions)
        if (isset($payload['tweet_create_events'])) {
            foreach ($payload['tweet_create_events'] as $tweet) {
                $result['mentions'][] = $this->parseTweet($tweet);
            }
        }

        return $result;
    }

    /**
     * Generate OAuth authorization URL
     */
    public function getAuthorizationUrl(string $callbackUrl): array
    {
        try {
            $response = Http::asForm()->post('https://api.twitter.com/oauth/request_token', [
                'oauth_callback' => $callbackUrl,
            ]);

            if ($response->successful()) {
                parse_str($response->body(), $params);
                $oauthToken = $params['oauth_token'] ?? null;

                return [
                    'success' => true,
                    'url' => "https://api.twitter.com/oauth/authorize?oauth_token={$oauthToken}",
                    'oauth_token' => $oauthToken,
                    'oauth_token_secret' => $params['oauth_token_secret'] ?? null,
                ];
            }

            return ['success' => false, 'error' => 'Failed to get request token'];
        } catch (\Exception $e) {
            Log::error('Twitter get authorization URL failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Exchange OAuth tokens
     */
    public function exchangeTokens(string $oauthToken, string $oauthVerifier): array
    {
        try {
            $response = Http::asForm()->post('https://api.twitter.com/oauth/access_token', [
                'oauth_token' => $oauthToken,
                'oauth_verifier' => $oauthVerifier,
            ]);

            if ($response->successful()) {
                parse_str($response->body(), $params);

                return [
                    'success' => true,
                    'access_token' => $params['oauth_token'] ?? null,
                    'access_token_secret' => $params['oauth_token_secret'] ?? null,
                    'user_id' => $params['user_id'] ?? null,
                    'screen_name' => $params['screen_name'] ?? null,
                ];
            }

            return ['success' => false, 'error' => 'Failed to exchange tokens'];
        } catch (\Exception $e) {
            Log::error('Twitter exchange tokens failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Parse direct message from webhook
     */
    protected function parseDirectMessage(array $event, array $users): array
    {
        $messageData = $event['message_create'];
        $senderId = $messageData['sender_id'];
        $recipientId = $messageData['target']['recipient_id'];

        $sender = $users[$senderId] ?? null;

        return [
            'id' => $event['id'],
            'type' => 'direct_message',
            'created_at' => $event['created_timestamp'],
            'sender_id' => $senderId,
            'recipient_id' => $recipientId,
            'text' => $messageData['message_data']['text'] ?? '',
            'entities' => $messageData['message_data']['entities'] ?? [],
            'attachment' => $messageData['message_data']['attachment'] ?? null,
            'sender' => $sender ? [
                'id' => $sender['id'],
                'name' => $sender['name'],
                'screen_name' => $sender['screen_name'],
                'profile_image_url' => $sender['profile_image_url_https'] ?? null,
            ] : null,
        ];
    }

    /**
     * Parse tweet from webhook
     */
    protected function parseTweet(array $tweet): array
    {
        return [
            'id' => $tweet['id_str'],
            'type' => 'tweet',
            'created_at' => $tweet['created_at'],
            'text' => $tweet['text'],
            'user' => [
                'id' => $tweet['user']['id_str'],
                'name' => $tweet['user']['name'],
                'screen_name' => $tweet['user']['screen_name'],
                'profile_image_url' => $tweet['user']['profile_image_url_https'] ?? null,
            ],
            'in_reply_to_status_id' => $tweet['in_reply_to_status_id_str'] ?? null,
            'in_reply_to_user_id' => $tweet['in_reply_to_user_id_str'] ?? null,
            'entities' => $tweet['entities'] ?? [],
        ];
    }
}
