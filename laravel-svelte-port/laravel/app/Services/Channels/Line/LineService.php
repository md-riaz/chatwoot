<?php

namespace App\Services\Channels\Line;

use App\Models\Inbox;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LineService
{
    protected string $apiUrl = 'https://api.line.me/v2/bot';
    protected ?string $channelAccessToken;
    protected ?string $channelSecret;

    public function __construct(?Inbox $inbox = null)
    {
        if ($inbox && $inbox->channel) {
            $this->channelAccessToken = $inbox->channel->channel_access_token ?? null;
            $this->channelSecret = $inbox->channel->channel_secret ?? null;
        }
    }

    /**
     * Send text message
     */
    public function sendTextMessage(string $userId, string $text): array
    {
        return $this->pushMessage($userId, [
            'type' => 'text',
            'text' => $text,
        ]);
    }

    /**
     * Send image message
     */
    public function sendImageMessage(string $userId, string $originalUrl, string $previewUrl): array
    {
        return $this->pushMessage($userId, [
            'type' => 'image',
            'originalContentUrl' => $originalUrl,
            'previewImageUrl' => $previewUrl,
        ]);
    }

    /**
     * Send video message
     */
    public function sendVideoMessage(string $userId, string $videoUrl, string $previewUrl): array
    {
        return $this->pushMessage($userId, [
            'type' => 'video',
            'originalContentUrl' => $videoUrl,
            'previewImageUrl' => $previewUrl,
        ]);
    }

    /**
     * Send audio message
     */
    public function sendAudioMessage(string $userId, string $audioUrl, int $duration): array
    {
        return $this->pushMessage($userId, [
            'type' => 'audio',
            'originalContentUrl' => $audioUrl,
            'duration' => $duration,
        ]);
    }

    /**
     * Send location message
     */
    public function sendLocationMessage(string $userId, string $title, string $address, float $latitude, float $longitude): array
    {
        return $this->pushMessage($userId, [
            'type' => 'location',
            'title' => $title,
            'address' => $address,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);
    }

    /**
     * Send sticker message
     */
    public function sendStickerMessage(string $userId, string $packageId, string $stickerId): array
    {
        return $this->pushMessage($userId, [
            'type' => 'sticker',
            'packageId' => $packageId,
            'stickerId' => $stickerId,
        ]);
    }

    /**
     * Send flex message
     */
    public function sendFlexMessage(string $userId, string $altText, array $contents): array
    {
        return $this->pushMessage($userId, [
            'type' => 'flex',
            'altText' => $altText,
            'contents' => $contents,
        ]);
    }

    /**
     * Send template message with buttons
     */
    public function sendButtonsTemplate(string $userId, string $altText, string $text, array $actions, ?string $thumbnailImageUrl = null, ?string $title = null): array
    {
        $template = [
            'type' => 'buttons',
            'text' => $text,
            'actions' => $actions,
        ];

        if ($thumbnailImageUrl) {
            $template['thumbnailImageUrl'] = $thumbnailImageUrl;
        }

        if ($title) {
            $template['title'] = $title;
        }

        return $this->pushMessage($userId, [
            'type' => 'template',
            'altText' => $altText,
            'template' => $template,
        ]);
    }

    /**
     * Send confirm template
     */
    public function sendConfirmTemplate(string $userId, string $altText, string $text, array $actions): array
    {
        return $this->pushMessage($userId, [
            'type' => 'template',
            'altText' => $altText,
            'template' => [
                'type' => 'confirm',
                'text' => $text,
                'actions' => $actions,
            ],
        ]);
    }

    /**
     * Send carousel template
     */
    public function sendCarouselTemplate(string $userId, string $altText, array $columns): array
    {
        return $this->pushMessage($userId, [
            'type' => 'template',
            'altText' => $altText,
            'template' => [
                'type' => 'carousel',
                'columns' => $columns,
            ],
        ]);
    }

    /**
     * Reply to a message
     */
    public function replyMessage(string $replyToken, array $messages): array
    {
        try {
            $response = Http::withToken($this->channelAccessToken)
                ->post("{$this->apiUrl}/message/reply", [
                    'replyToken' => $replyToken,
                    'messages' => is_array($messages[0] ?? null) ? $messages : [$messages],
                ]);

            if ($response->successful()) {
                return ['success' => true];
            }

            return [
                'success' => false,
                'error' => $response->json('message', 'Unknown error'),
            ];
        } catch (\Exception $e) {
            Log::error('LINE reply message failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Push message to user
     */
    public function pushMessage(string $to, array $message): array
    {
        try {
            $response = Http::withToken($this->channelAccessToken)
                ->post("{$this->apiUrl}/message/push", [
                    'to' => $to,
                    'messages' => [$message],
                ]);

            if ($response->successful()) {
                return ['success' => true];
            }

            return [
                'success' => false,
                'error' => $response->json('message', 'Unknown error'),
            ];
        } catch (\Exception $e) {
            Log::error('LINE push message failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get user profile
     */
    public function getProfile(string $userId): ?array
    {
        try {
            $response = Http::withToken($this->channelAccessToken)
                ->get("{$this->apiUrl}/profile/{$userId}");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('LINE get profile failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get content (media file)
     */
    public function getContent(string $messageId): ?string
    {
        try {
            $response = Http::withToken($this->channelAccessToken)
                ->get("https://api-data.line.me/v2/bot/message/{$messageId}/content");

            if ($response->successful()) {
                return $response->body();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('LINE get content failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Validate webhook signature
     */
    public function validateSignature(string $body, string $signature): bool
    {
        $hash = base64_encode(hash_hmac('sha256', $body, $this->channelSecret, true));
        return hash_equals($hash, $signature);
    }

    /**
     * Process incoming webhook
     */
    public function processWebhook(array $payload): array
    {
        $events = [];

        foreach ($payload['events'] ?? [] as $event) {
            $events[] = $this->parseEvent($event);
        }

        return $events;
    }

    /**
     * Parse webhook event
     */
    protected function parseEvent(array $event): array
    {
        $result = [
            'type' => $event['type'],
            'timestamp' => $event['timestamp'],
            'source' => $event['source'],
            'reply_token' => $event['replyToken'] ?? null,
        ];

        switch ($event['type']) {
            case 'message':
                $result['message'] = $this->parseMessage($event['message']);
                break;

            case 'follow':
            case 'unfollow':
                // Just the type and source
                break;

            case 'postback':
                $result['postback'] = [
                    'data' => $event['postback']['data'],
                    'params' => $event['postback']['params'] ?? null,
                ];
                break;

            case 'beacon':
                $result['beacon'] = [
                    'hwid' => $event['beacon']['hwid'],
                    'type' => $event['beacon']['type'],
                ];
                break;
        }

        return $result;
    }

    /**
     * Parse message from event
     */
    protected function parseMessage(array $message): array
    {
        $result = [
            'id' => $message['id'],
            'type' => $message['type'],
        ];

        switch ($message['type']) {
            case 'text':
                $result['text'] = $message['text'];
                $result['emojis'] = $message['emojis'] ?? [];
                break;

            case 'image':
            case 'video':
            case 'audio':
            case 'file':
                $result['content_provider'] = $message['contentProvider'] ?? null;
                if (isset($message['fileName'])) {
                    $result['file_name'] = $message['fileName'];
                }
                if (isset($message['fileSize'])) {
                    $result['file_size'] = $message['fileSize'];
                }
                break;

            case 'location':
                $result['title'] = $message['title'] ?? null;
                $result['address'] = $message['address'];
                $result['latitude'] = $message['latitude'];
                $result['longitude'] = $message['longitude'];
                break;

            case 'sticker':
                $result['package_id'] = $message['packageId'];
                $result['sticker_id'] = $message['stickerId'];
                break;
        }

        return $result;
    }
}
