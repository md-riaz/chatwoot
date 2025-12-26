<?php

namespace App\Services\Channels\Facebook;

use App\Models\Inbox;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookService
{
    protected string $apiUrl = 'https://graph.facebook.com/v18.0';
    protected ?string $accessToken;
    protected ?string $pageId;

    public function __construct(?Inbox $inbox = null)
    {
        if ($inbox && $inbox->channel) {
            $this->accessToken = $inbox->channel->page_access_token ?? null;
            $this->pageId = $inbox->channel->page_id ?? null;
        }
    }

    /**
     * Send a text message
     */
    public function sendTextMessage(string $recipientId, string $message): array
    {
        return $this->sendMessage($recipientId, [
            'text' => $message,
        ]);
    }

    /**
     * Send an image attachment
     */
    public function sendImageMessage(string $recipientId, string $imageUrl): array
    {
        return $this->sendAttachment($recipientId, 'image', $imageUrl);
    }

    /**
     * Send a file attachment
     */
    public function sendFileMessage(string $recipientId, string $fileUrl): array
    {
        return $this->sendAttachment($recipientId, 'file', $fileUrl);
    }

    /**
     * Send quick reply buttons
     */
    public function sendQuickReplies(string $recipientId, string $text, array $quickReplies): array
    {
        $formattedReplies = array_map(function ($reply) {
            return [
                'content_type' => 'text',
                'title' => substr($reply['title'], 0, 20),
                'payload' => $reply['payload'] ?? $reply['title'],
            ];
        }, $quickReplies);

        return $this->sendMessage($recipientId, [
            'text' => $text,
            'quick_replies' => $formattedReplies,
        ]);
    }

    /**
     * Send button template
     */
    public function sendButtonTemplate(string $recipientId, string $text, array $buttons): array
    {
        $formattedButtons = array_map(function ($button) {
            return [
                'type' => $button['type'] ?? 'postback',
                'title' => substr($button['title'], 0, 20),
                'payload' => $button['payload'] ?? $button['title'],
            ];
        }, array_slice($buttons, 0, 3));

        return $this->sendMessage($recipientId, [
            'attachment' => [
                'type' => 'template',
                'payload' => [
                    'template_type' => 'button',
                    'text' => $text,
                    'buttons' => $formattedButtons,
                ],
            ],
        ]);
    }

    /**
     * Send generic template (carousel)
     */
    public function sendGenericTemplate(string $recipientId, array $elements): array
    {
        return $this->sendMessage($recipientId, [
            'attachment' => [
                'type' => 'template',
                'payload' => [
                    'template_type' => 'generic',
                    'elements' => $elements,
                ],
            ],
        ]);
    }

    /**
     * Get user profile
     */
    public function getUserProfile(string $userId): ?array
    {
        try {
            $response = Http::get("{$this->apiUrl}/{$userId}", [
                'fields' => 'first_name,last_name,profile_pic,locale,timezone,gender',
                'access_token' => $this->accessToken,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Facebook get user profile failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Set typing indicator
     */
    public function setTypingIndicator(string $recipientId, bool $on = true): bool
    {
        try {
            $response = Http::post("{$this->apiUrl}/me/messages", [
                'recipient' => ['id' => $recipientId],
                'sender_action' => $on ? 'typing_on' : 'typing_off',
                'access_token' => $this->accessToken,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Facebook typing indicator failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Mark message as seen
     */
    public function markAsSeen(string $recipientId): bool
    {
        try {
            $response = Http::post("{$this->apiUrl}/me/messages", [
                'recipient' => ['id' => $recipientId],
                'sender_action' => 'mark_seen',
                'access_token' => $this->accessToken,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Facebook mark as seen failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get pages for user
     */
    public function getPages(string $userAccessToken): array
    {
        try {
            $response = Http::get("{$this->apiUrl}/me/accounts", [
                'access_token' => $userAccessToken,
                'fields' => 'id,name,access_token,category,picture',
            ]);

            if ($response->successful()) {
                return $response->json('data', []);
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Facebook get pages failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Subscribe app to page
     */
    public function subscribeToPage(): bool
    {
        try {
            $response = Http::post("{$this->apiUrl}/{$this->pageId}/subscribed_apps", [
                'subscribed_fields' => 'messages,messaging_postbacks,messaging_optins,message_deliveries,message_reads,messaging_account_linking',
                'access_token' => $this->accessToken,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Facebook subscribe to page failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Process incoming webhook
     */
    public function processWebhook(array $payload): array
    {
        $results = [];

        if ($payload['object'] !== 'page') {
            return $results;
        }

        foreach ($payload['entry'] as $entry) {
            $pageId = $entry['id'];
            $time = $entry['time'];

            foreach ($entry['messaging'] ?? [] as $event) {
                $senderId = $event['sender']['id'];
                $recipientId = $event['recipient']['id'];
                $timestamp = $event['timestamp'];

                if (isset($event['message'])) {
                    $results[] = $this->parseMessage($event['message'], $senderId, $recipientId, $timestamp);
                } elseif (isset($event['postback'])) {
                    $results[] = $this->parsePostback($event['postback'], $senderId, $recipientId, $timestamp);
                } elseif (isset($event['delivery'])) {
                    $results[] = ['type' => 'delivery', 'data' => $event['delivery']];
                } elseif (isset($event['read'])) {
                    $results[] = ['type' => 'read', 'data' => $event['read']];
                }
            }
        }

        return $results;
    }

    /**
     * Verify webhook
     */
    public function verifyWebhook(string $mode, string $token, string $challenge, string $verifyToken): ?string
    {
        if ($mode === 'subscribe' && $token === $verifyToken) {
            return $challenge;
        }

        return null;
    }

    /**
     * Core send message method
     */
    protected function sendMessage(string $recipientId, array $message): array
    {
        try {
            $response = Http::post("{$this->apiUrl}/me/messages", [
                'recipient' => ['id' => $recipientId],
                'message' => $message,
                'access_token' => $this->accessToken,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message_id' => $response->json('message_id'),
                    'recipient_id' => $response->json('recipient_id'),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('error.message', 'Unknown error'),
            ];
        } catch (\Exception $e) {
            Log::error('Facebook send message failed', [
                'recipient' => $recipientId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send attachment
     */
    protected function sendAttachment(string $recipientId, string $type, string $url): array
    {
        return $this->sendMessage($recipientId, [
            'attachment' => [
                'type' => $type,
                'payload' => [
                    'url' => $url,
                    'is_reusable' => true,
                ],
            ],
        ]);
    }

    /**
     * Parse incoming message
     */
    protected function parseMessage(array $message, string $senderId, string $recipientId, int $timestamp): array
    {
        $result = [
            'type' => 'message',
            'sender_id' => $senderId,
            'recipient_id' => $recipientId,
            'timestamp' => $timestamp,
            'mid' => $message['mid'],
        ];

        if (isset($message['text'])) {
            $result['text'] = $message['text'];
        }

        if (isset($message['attachments'])) {
            $result['attachments'] = array_map(function ($attachment) {
                return [
                    'type' => $attachment['type'],
                    'url' => $attachment['payload']['url'] ?? null,
                    'sticker_id' => $attachment['payload']['sticker_id'] ?? null,
                    'coordinates' => $attachment['payload']['coordinates'] ?? null,
                ];
            }, $message['attachments']);
        }

        if (isset($message['quick_reply'])) {
            $result['quick_reply_payload'] = $message['quick_reply']['payload'];
        }

        return $result;
    }

    /**
     * Parse postback event
     */
    protected function parsePostback(array $postback, string $senderId, string $recipientId, int $timestamp): array
    {
        return [
            'type' => 'postback',
            'sender_id' => $senderId,
            'recipient_id' => $recipientId,
            'timestamp' => $timestamp,
            'payload' => $postback['payload'],
            'title' => $postback['title'] ?? null,
        ];
    }
}
