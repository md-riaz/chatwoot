<?php

namespace App\Services\Integrations\Slack;

use App\Models\Account;
use App\Models\Integration\Hook;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SlackService
{
    private const API_BASE_URL = 'https://slack.com/api';

    public function __construct(
        private Account $account
    ) {}

    public function createHook(string $code, ?int $inboxId = null): Hook
    {
        $accessToken = $this->exchangeCodeForToken($code);
        
        $hook = new Hook([
            'account_id' => $this->account->id,
            'inbox_id' => $inboxId,
            'app_id' => 'slack',
            'access_token' => $accessToken,
            'status' => Hook::STATUS_DISABLED,
            'hook_type' => $inboxId ? Hook::HOOK_TYPE_INBOX : Hook::HOOK_TYPE_ACCOUNT,
        ]);
        
        $hook->save();
        
        return $hook;
    }

    public function listChannels(): array
    {
        $hook = $this->getSlackHook();
        
        if (!$hook) {
            return ['error' => 'Slack integration not configured'];
        }

        try {
            $channels = [];
            
            // Fetch private channels
            $privateChannels = $this->fetchChannelsByType('private_channel', $hook->access_token);
            $channels = array_merge($channels, $privateChannels);
            
            // Fetch public channels
            $publicChannels = $this->fetchChannelsByType('public_channel', $hook->access_token);
            $channels = array_merge($channels, $publicChannels);
            
            return ['channels' => $channels];
        } catch (\Exception $e) {
            Log::error('Slack API error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function updateChannel(string $referenceId): ?Hook
    {
        $hook = $this->getSlackHook();
        
        if (!$hook) {
            return null;
        }

        try {
            $channel = $this->findChannel($referenceId, $hook->access_token);
            
            if (!$channel) {
                return null;
            }

            // Join channel if it's public
            if (!$channel['is_private']) {
                $this->joinChannel($channel['id'], $hook->access_token);
            }

            $hook->update([
                'reference_id' => $channel['id'],
                'settings' => ['channel_name' => $channel['name']],
                'status' => Hook::STATUS_ENABLED,
            ]);

            return $hook;
        } catch (\Exception $e) {
            Log::error('Slack channel update error: ' . $e->getMessage());
            return null;
        }
    }

    private function exchangeCodeForToken(string $code): string
    {
        $response = Http::post(self::API_BASE_URL . '/oauth.v2.access', [
            'client_id' => config('services.slack.client_id'),
            'client_secret' => config('services.slack.client_secret'),
            'code' => $code,
            'redirect_uri' => config('services.slack.redirect_uri'),
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to exchange Slack code for token');
        }

        $data = $response->json();
        
        if (!$data['ok']) {
            throw new \Exception('Slack OAuth error: ' . ($data['error'] ?? 'Unknown error'));
        }

        return $data['access_token'];
    }

    private function fetchChannelsByType(string $channelType, string $accessToken, int $limit = 1000): array
    {
        $channels = [];
        $cursor = null;

        do {
            $params = [
                'types' => $channelType,
                'exclude_archived' => 'true',
                'limit' => $limit,
            ];

            if ($cursor) {
                $params['cursor'] = $cursor;
            }

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$accessToken}",
            ])->get(self::API_BASE_URL . '/conversations.list', $params);

            if (!$response->successful()) {
                throw new \Exception("Failed to fetch Slack channels: {$response->status()}");
            }

            $data = $response->json();
            
            if (!$data['ok']) {
                throw new \Exception('Slack API error: ' . ($data['error'] ?? 'Unknown error'));
            }

            $channels = array_merge($channels, $data['channels'] ?? []);
            $cursor = $data['response_metadata']['next_cursor'] ?? null;
        } while ($cursor);

        return $channels;
    }

    private function findChannel(string $referenceId, string $accessToken): ?array
    {
        $privateChannels = $this->fetchChannelsByType('private_channel', $accessToken);
        $publicChannels = $this->fetchChannelsByType('public_channel', $accessToken);
        
        $allChannels = array_merge($privateChannels, $publicChannels);
        
        foreach ($allChannels as $channel) {
            if ($channel['id'] === $referenceId) {
                return $channel;
            }
        }

        return null;
    }

    private function joinChannel(string $channelId, string $accessToken): void
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$accessToken}",
        ])->post(self::API_BASE_URL . '/conversations.join', [
            'channel' => $channelId,
        ]);

        if (!$response->successful()) {
            throw new \Exception("Failed to join Slack channel: {$response->status()}");
        }

        $data = $response->json();
        
        if (!$data['ok']) {
            throw new \Exception('Slack join channel error: ' . ($data['error'] ?? 'Unknown error'));
        }
    }

    private function getSlackHook(): ?Hook
    {
        return $this->account->integrationHooks()
            ->where('app_id', 'slack')
            ->first();
    }
}