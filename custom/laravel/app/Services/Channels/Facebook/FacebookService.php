<?php

namespace App\Services\Channels\Facebook;

use App\Models\Inbox;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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

        if (($payload['object'] ?? null) !== 'page') {
            return $results;
        }

        foreach ($payload['entry'] as $entry) {
            $pageId = $entry['id'] ?? null;

            // Find an inbox associated with this Facebook page
            $inbox = Inbox::where('channel_type', 'Channel::FacebookPage')
                ->whereJsonContains('channel', ['page_id' => (string) $pageId])
                ->first();

            if (! $inbox) {
                Log::warning('No inbox found for facebook page during webhook processing', ['page_id' => $pageId]);
                continue;
            }

            foreach ($entry['messaging'] ?? [] as $event) {
                try {
                    if (isset($event['message'])) {
                        $message = $event['message'];
                        $mid = $message['mid'] ?? null;

                        // Idempotency: skip if we've already processed this mid
                        if ($mid && DB::table('facebook_message_events')->where('message_mid', $mid)->exists()) {
                            continue;
                        }

                        // Insert idempotency record (best-effort)
                        try {
                            DB::table('facebook_message_events')->insert([
                                'page_id' => $pageId,
                                'message_mid' => $mid,
                                'payload' => json_encode($event),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        } catch (\Throwable $e) {
                            // Unique constraint may race; continue processing if insert fails
                            Log::debug('facebook_message_events insert failed', ['mid' => $mid, 'error' => $e->getMessage()]);
                        }

                        $senderId = $event['sender']['id'] ?? null;

                        if (! $senderId) {
                            continue;
                        }

                        // Find or create contact by identifier
                        $contact = Contact::firstOrCreate([
                            'account_id' => $inbox->account_id,
                            'identifier' => 'facebook:' . $senderId,
                        ], [
                            'name' => null,
                            'source' => 'facebook',
                        ]);

                        // Find existing open conversation
                        $conversation = Conversation::where('account_id', $inbox->account_id)
                            ->where('inbox_id', $inbox->id)
                            ->where('contact_id', $contact->id)
                            ->where('status', Conversation::STATUS_OPEN)
                            ->first();

                        if (! $conversation) {
                            $conversation = Conversation::create([
                                'account_id' => $inbox->account_id,
                                'inbox_id' => $inbox->id,
                                'contact_id' => $contact->id,
                                'status' => Conversation::STATUS_OPEN,
                            ]);

                            event(new \App\Events\Conversation\ConversationCreated($conversation));
                        }

                        // Create message record
                        $msg = Message::create([
                            'account_id' => $inbox->account_id,
                            'conversation_id' => $conversation->id,
                            'inbox_id' => $inbox->id,
                            'sender_id' => $contact->id,
                            'sender_type' => Contact::class,
                            'message_type' => Message::TYPE_INCOMING,
                            'content' => $message['text'] ?? null,
                            'content_type' => Message::CONTENT_TEXT,
                            'external_source_id' => $mid,
                        ]);

                        try {
                            event(new \App\Events\Message\MessageCreated($msg));
                        } catch (\Throwable $e) {
                            Log::warning('Failed to dispatch MessageCreated event', ['error' => $e->getMessage()]);
                        }

                        $results[] = ['type' => 'message', 'mid' => $mid, 'message_id' => $msg->id];
                    } elseif (isset($event['postback'])) {
                        $postback = $event['postback'];

                        $senderId = $event['sender']['id'] ?? null;
                        if (! $senderId) {
                            continue;
                        }

                        $contact = Contact::firstOrCreate([
                            'account_id' => $inbox->account_id,
                            'identifier' => 'facebook:' . $senderId,
                        ], [
                            'name' => null,
                            'source' => 'facebook',
                        ]);

                        $conversation = Conversation::where('account_id', $inbox->account_id)
                            ->where('inbox_id', $inbox->id)
                            ->where('contact_id', $contact->id)
                            ->where('status', Conversation::STATUS_OPEN)
                            ->first();

                        if (! $conversation) {
                            $conversation = Conversation::create([
                                'account_id' => $inbox->account_id,
                                'inbox_id' => $inbox->id,
                                'contact_id' => $contact->id,
                                'status' => Conversation::STATUS_OPEN,
                            ]);

                            event(new \App\Events\Conversation\ConversationCreated($conversation));
                        }

                        $msg = Message::create([
                            'account_id' => $inbox->account_id,
                            'conversation_id' => $conversation->id,
                            'inbox_id' => $inbox->id,
                            'sender_id' => $contact->id,
                            'sender_type' => Contact::class,
                            'message_type' => Message::TYPE_INCOMING,
                            'content' => $postback['payload'] ?? ($postback['title'] ?? null),
                            'content_type' => Message::CONTENT_TEXT,
                        ]);

                        try {
                            event(new \App\Events\Message\MessageCreated($msg));
                        } catch (\Throwable $e) {
                            Log::warning('Failed to dispatch MessageCreated event (postback)', ['error' => $e->getMessage()]);
                        }

                        $results[] = ['type' => 'postback', 'message_id' => $msg->id];
                    }
                } catch (\Throwable $e) {
                    Log::error('Error processing facebook webhook event', ['error' => $e->getMessage(), 'event' => $event]);
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
