<?php

namespace App\Services\Channels\Line;

use App\Models\Channels\Line;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Line API client for handling Line Bot API interactions.
 * Provides messaging and profile management functionality.
 * 
 * @see app/models/channel/line.rb (client method)
 */
class LineApiClient
{
    protected Line $channel;
    protected string $baseUrl = 'https://api.line.me/v2/bot';

    public function __construct(Line $channel)
    {
        $this->channel = $channel;
    }

    /**
     * Push a message to a user.
     */
    public function pushMessage(string $userId, array $messages): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->channel->line_channel_token,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/message/push', [
                'to' => $userId,
                'messages' => is_array($messages[0] ?? null) ? $messages : [$messages],
            ]);

            Log::info('Line message sent', [
                'channel_id' => $this->channel->id,
                'user_id' => $userId,
                'status' => $response->status()
            ]);

            return [
                'status' => $response->status(),
                'body' => $response->body(),
                'success' => $response->successful(),
            ];
        } catch (\Exception $e) {
            Log::error('Line message send failed', [
                'channel_id' => $this->channel->id,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Get user profile information.
     */
    public function getProfile(string $userId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->channel->line_channel_token,
            ])->get($this->baseUrl . "/profile/{$userId}");

            Log::info('Line profile fetched', [
                'channel_id' => $this->channel->id,
                'user_id' => $userId,
                'status' => $response->status()
            ]);

            return [
                'status' => $response->status(),
                'body' => $response->body(),
                'success' => $response->successful(),
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('Line profile fetch failed', [
                'channel_id' => $this->channel->id,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Get message content (for media messages).
     */
    public function getMessageContent(string $messageId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->channel->line_channel_token,
            ])->get($this->baseUrl . "/message/{$messageId}/content");

            Log::info('Line message content fetched', [
                'channel_id' => $this->channel->id,
                'message_id' => $messageId,
                'status' => $response->status()
            ]);

            return [
                'status' => $response->status(),
                'body' => $response->body(),
                'success' => $response->successful(),
                'content_type' => $response->header('Content-Type'),
            ];
        } catch (\Exception $e) {
            Log::error('Line message content fetch failed', [
                'channel_id' => $this->channel->id,
                'message_id' => $messageId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Reply to a message.
     */
    public function replyMessage(string $replyToken, array $messages): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->channel->line_channel_token,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/message/reply', [
                'replyToken' => $replyToken,
                'messages' => is_array($messages[0] ?? null) ? $messages : [$messages],
            ]);

            Log::info('Line reply sent', [
                'channel_id' => $this->channel->id,
                'reply_token' => $replyToken,
                'status' => $response->status()
            ]);

            return [
                'status' => $response->status(),
                'body' => $response->body(),
                'success' => $response->successful(),
            ];
        } catch (\Exception $e) {
            Log::error('Line reply send failed', [
                'channel_id' => $this->channel->id,
                'reply_token' => $replyToken,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Multicast messages to multiple users.
     */
    public function multicast(array $userIds, array $messages): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->channel->line_channel_token,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/message/multicast', [
                'to' => $userIds,
                'messages' => is_array($messages[0] ?? null) ? $messages : [$messages],
            ]);

            Log::info('Line multicast sent', [
                'channel_id' => $this->channel->id,
                'user_count' => count($userIds),
                'status' => $response->status()
            ]);

            return [
                'status' => $response->status(),
                'body' => $response->body(),
                'success' => $response->successful(),
            ];
        } catch (\Exception $e) {
            Log::error('Line multicast send failed', [
                'channel_id' => $this->channel->id,
                'user_count' => count($userIds),
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Validate webhook signature.
     */
    public function validateSignature(string $body, string $signature): bool
    {
        $expectedSignature = base64_encode(hash_hmac('sha256', $body, $this->channel->line_channel_secret, true));
        
        return hash_equals($expectedSignature, $signature);
    }
}