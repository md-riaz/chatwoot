<?php

namespace App\Services\Integrations;

use App\Models\Integration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SlackService
{
    protected string $apiUrl = 'https://slack.com/api';
    protected ?string $botToken;

    public function __construct(?Integration $integration = null)
    {
        if ($integration && $integration->type === 'slack') {
            $this->botToken = $integration->credentials['bot_token'] ?? null;
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
     * Upload a file
     */
    public function uploadFile(string $channel, string $content, string $filename): array
    {
        return $this->makeRequest('files.upload', [
            'channels' => $channel,
            'content' => $content,
            'filename' => $filename,
        ]);
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
