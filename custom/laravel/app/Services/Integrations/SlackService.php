<?php

namespace App\Services\Integrations;

use App\Models\Account;
use App\Models\Integration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\Auth\BaseRefreshOauthTokenService;

class SlackService
{
    protected string $apiUrl = 'https://slack.com/api';
    protected ?string $botToken;
    protected ?string $appToken;

    public function __construct(?Integration $integration = null)
    {
        if ($integration && $integration->type === 'slack') {
            $this->botToken = $integration->credentials['bot_token'] ?? null;
            $this->appToken = $integration->credentials['app_token'] ?? null;
        }
    }

    /**
     * Send a message to a channel
     */
    public function sendMessage(string $channel, string $text, array $options = []): array
    {
        $payload = array_merge([
            'channel' => $channel,
            'text' => $text,
        ], $options);

        return $this->makeRequest('chat.postMessage', $payload);
    }

    /**
     * Send a message with blocks
     */
    public function sendBlockMessage(string $channel, array $blocks, ?string $text = null): array
    {
        return $this->sendMessage($channel, $text ?? '', [
            'blocks' => $blocks,
        ]);
    }

    /**
     * Update a message
     */
    public function updateMessage(string $channel, string $ts, string $text, array $options = []): array
    {
        $payload = array_merge([
            'channel' => $channel,
            'ts' => $ts,
            'text' => $text,
        ], $options);

        return $this->makeRequest('chat.update', $payload);
    }

    /**
     * Delete a message
     */
    public function deleteMessage(string $channel, string $ts): array
    {
        return $this->makeRequest('chat.delete', [
            'channel' => $channel,
            'ts' => $ts,
        ]);
    }

    /**
     * Get list of channels
     */
    public function getChannels(bool $excludeArchived = true): array
    {
        $result = $this->makeRequest('conversations.list', [
            'exclude_archived' => $excludeArchived,
            'types' => 'public_channel,private_channel',
        ]);

        return $result['channels'] ?? [];
    }

    /**
     * Get channel info
     */
    public function getChannelInfo(string $channel): ?array
    {
        $result = $this->makeRequest('conversations.info', [
            'channel' => $channel,
        ]);

        return $result['channel'] ?? null;
    }

    /**
     * Get user info
     */
    public function getUserInfo(string $userId): ?array
    {
        $result = $this->makeRequest('users.info', [
            'user' => $userId,
        ]);

        return $result['user'] ?? null;
    }

    /**
     * Get list of users
     */
    public function getUsers(): array
    {
        $result = $this->makeRequest('users.list', []);
        return $result['members'] ?? [];
    }

    /**
     * Add reaction to message
     */
    public function addReaction(string $channel, string $ts, string $emoji): array
    {
        return $this->makeRequest('reactions.add', [
            'channel' => $channel,
            'timestamp' => $ts,
            'name' => $emoji,
        ]);
    }

    /**
     * Open a direct message channel
     */
    public function openDm(string $userId): ?string
    {
        $result = $this->makeRequest('conversations.open', [
            'users' => $userId,
        ]);

        return $result['channel']['id'] ?? null;
    }

    /**
     * Upload a file
     */
    public function uploadFile(string $channel, string $content, string $filename, ?string $title = null): array
    {
        return $this->makeRequest('files.upload', [
            'channels' => $channel,
            'content' => $content,
            'filename' => $filename,
            'title' => $title ?? $filename,
        ]);
    }

    /**
     * Open a modal
     */
    public function openModal(string $triggerId, array $view): array
    {
        return $this->makeRequest('views.open', [
            'trigger_id' => $triggerId,
            'view' => $view,
        ]);
    }

    /**
     * Update a modal
     */
    public function updateModal(string $viewId, array $view): array
    {
        return $this->makeRequest('views.update', [
            'view_id' => $viewId,
            'view' => $view,
        ]);
    }

    /**
     * Create notification blocks for a conversation
     */
    public function createConversationBlocks(array $conversation, array $contact, string $message): array
    {
        return [
            [
                'type' => 'header',
                'text' => [
                    'type' => 'plain_text',
                    'text' => "New message from {$contact['name']}",
                    'emoji' => true,
                ],
            ],
            [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => $message,
                ],
            ],
            [
                'type' => 'context',
                'elements' => [
                    [
                        'type' => 'mrkdwn',
                        'text' => "Conversation #" . ($conversation['display_id'] ?? $conversation['id']),
                    ],
                ],
            ],
            [
                'type' => 'actions',
                'elements' => [
                [
                    'type' => 'button',
                    'text' => [
                        'type' => 'plain_text',
                        'text' => 'Open in ClearLine',
                        'emoji' => true,
                    ],
                    'url' => $conversation['url'] ?? '#',
                    'action_id' => 'open_conversation',
                ],
                ],
            ],
        ];
    }

    /**
     * Process events webhook
     */
    public function processEventsWebhook(array $payload): array
    {
        // URL verification challenge
        if (isset($payload['challenge'])) {
            return ['challenge' => $payload['challenge']];
        }

        $event = $payload['event'] ?? null;
        if (!$event) {
            return [];
        }

        return [
            'type' => $event['type'],
            'user' => $event['user'] ?? null,
            'channel' => $event['channel'] ?? null,
            'text' => $event['text'] ?? null,
            'ts' => $event['ts'] ?? null,
            'thread_ts' => $event['thread_ts'] ?? null,
            'files' => $event['files'] ?? [],
        ];
    }

    /**
     * Process interactive components webhook
     */
    public function processInteractiveWebhook(array $payload): array
    {
        return [
            'type' => $payload['type'],
            'user' => $payload['user'],
            'action' => $payload['actions'][0] ?? null,
            'channel' => $payload['channel'] ?? null,
            'message' => $payload['message'] ?? null,
            'response_url' => $payload['response_url'] ?? null,
            'trigger_id' => $payload['trigger_id'] ?? null,
        ];
    }

    /**
     * Process slash command webhook
     */
    public function processCommandWebhook(array $payload): array
    {
        return [
            'command' => $payload['command'],
            'text' => $payload['text'],
            'user_id' => $payload['user_id'],
            'user_name' => $payload['user_name'],
            'channel_id' => $payload['channel_id'],
            'channel_name' => $payload['channel_name'],
            'response_url' => $payload['response_url'],
            'trigger_id' => $payload['trigger_id'],
        ];
    }

    /**
     * Verify request signature
     */
    public function verifySignature(string $signature, string $timestamp, string $body, string $signingSecret): bool
    {
        $baseString = "v0:{$timestamp}:{$body}";
        $expectedSignature = 'v0=' . hash_hmac('sha256', $baseString, $signingSecret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Attempt to refresh tokens if refresh_token + token_url are present.
     * Updates the integration credentials on success.
     *
     * @param Integration $integration
     * @return bool true when new token was obtained
     */
    public function refreshTokensIfNeeded(Integration $integration): bool
    {
        $creds = $integration->credentials ?? [];

        if (empty($creds['refresh_token']) || empty($creds['token_url'])) {
            return false;
        }

        try {
            $svc = new BaseRefreshOauthTokenService();
            $res = $svc->refreshTokenFor([
                'token_url' => $creds['token_url'],
                'client_id' => $creds['client_id'] ?? null,
                'client_secret' => $creds['client_secret'] ?? null,
                'refresh_token' => $creds['refresh_token'],
                'scope' => $creds['scope'] ?? null,
            ]);

            // Update integration credentials and local bot token
            $creds['bot_token'] = $res['access_token'];
            $creds['refresh_token'] = $res['refresh_token'] ?? $creds['refresh_token'];
            if ($res['expires_at'] ?? false) {
                $creds['expires_at'] = $res['expires_at']->toDateTimeString();
            }

            $integration->update(['credentials' => $creds]);

            $this->botToken = $creds['bot_token'];

            return true;
        } catch (\Throwable $e) {
            Log::warning('Slack token refresh failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Make API request
     */
    protected function makeRequest(string $method, array $params = []): array
    {
        try {
            $response = Http::withToken($this->botToken)
                ->asJson()
                ->post("{$this->apiUrl}/{$method}", $params);

            $data = $response->json();

            if (!($data['ok'] ?? false)) {
                Log::warning('Slack API request failed', [
                    'method' => $method,
                    'error' => $data['error'] ?? 'Unknown error',
                ]);
            }

            return $data;
        } catch (\Exception $e) {
            Log::error('Slack API request exception', [
                'method' => $method,
                'error' => $e->getMessage(),
            ]);

            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
