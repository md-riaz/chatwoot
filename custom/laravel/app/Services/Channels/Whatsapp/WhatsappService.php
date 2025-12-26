<?php

namespace App\Services\Channels\Whatsapp;

use App\Models\Inbox;
use App\Models\Message;
use App\Models\Contact;
use App\Models\Conversation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    protected string $apiUrl = 'https://graph.facebook.com/v18.0';
    protected ?string $accessToken;
    protected ?string $phoneNumberId;
    protected ?string $businessAccountId;

    public function __construct(?Inbox $inbox = null)
    {
        if ($inbox && $inbox->channel) {
            $this->accessToken = $inbox->channel->provider_config['api_key'] ?? null;
            $this->phoneNumberId = $inbox->channel->provider_config['phone_number_id'] ?? null;
            $this->businessAccountId = $inbox->channel->provider_config['business_account_id'] ?? null;
        }
    }

    /**
     * Send a text message
     */
    public function sendTextMessage(string $to, string $message): array
    {
        return $this->sendMessage($to, [
            'type' => 'text',
            'text' => ['body' => $message],
        ]);
    }

    /**
     * Send an image message
     */
    public function sendImageMessage(string $to, string $imageUrl, ?string $caption = null): array
    {
        $data = [
            'type' => 'image',
            'image' => ['link' => $imageUrl],
        ];

        if ($caption) {
            $data['image']['caption'] = $caption;
        }

        return $this->sendMessage($to, $data);
    }

    /**
     * Send a document message
     */
    public function sendDocumentMessage(string $to, string $documentUrl, ?string $filename = null, ?string $caption = null): array
    {
        $data = [
            'type' => 'document',
            'document' => ['link' => $documentUrl],
        ];

        if ($filename) {
            $data['document']['filename'] = $filename;
        }

        if ($caption) {
            $data['document']['caption'] = $caption;
        }

        return $this->sendMessage($to, $data);
    }

    /**
     * Send a template message
     */
    public function sendTemplateMessage(string $to, string $templateName, string $languageCode = 'en', array $components = []): array
    {
        $data = [
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => $languageCode],
            ],
        ];

        if (!empty($components)) {
            $data['template']['components'] = $components;
        }

        return $this->sendMessage($to, $data);
    }

    /**
     * Send interactive message with buttons
     */
    public function sendInteractiveButtons(string $to, string $bodyText, array $buttons, ?string $header = null, ?string $footer = null): array
    {
        $buttonData = array_map(function ($button, $index) {
            return [
                'type' => 'reply',
                'reply' => [
                    'id' => $button['id'] ?? "btn_$index",
                    'title' => substr($button['title'], 0, 20),
                ],
            ];
        }, $buttons, array_keys($buttons));

        $data = [
            'type' => 'interactive',
            'interactive' => [
                'type' => 'button',
                'body' => ['text' => $bodyText],
                'action' => ['buttons' => $buttonData],
            ],
        ];

        if ($header) {
            $data['interactive']['header'] = ['type' => 'text', 'text' => $header];
        }

        if ($footer) {
            $data['interactive']['footer'] = ['text' => $footer];
        }

        return $this->sendMessage($to, $data);
    }

    /**
     * Send interactive list message
     */
    public function sendInteractiveList(string $to, string $bodyText, string $buttonText, array $sections, ?string $header = null, ?string $footer = null): array
    {
        $data = [
            'type' => 'interactive',
            'interactive' => [
                'type' => 'list',
                'body' => ['text' => $bodyText],
                'action' => [
                    'button' => $buttonText,
                    'sections' => $sections,
                ],
            ],
        ];

        if ($header) {
            $data['interactive']['header'] = ['type' => 'text', 'text' => $header];
        }

        if ($footer) {
            $data['interactive']['footer'] = ['text' => $footer];
        }

        return $this->sendMessage($to, $data);
    }

    /**
     * Mark message as read
     */
    public function markAsRead(string $messageId): bool
    {
        try {
            $response = Http::withToken($this->accessToken)
                ->post("{$this->apiUrl}/{$this->phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'status' => 'read',
                    'message_id' => $messageId,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('WhatsApp mark as read failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get message templates
     */
    public function getTemplates(): array
    {
        try {
            $response = Http::withToken($this->accessToken)
                ->get("{$this->apiUrl}/{$this->businessAccountId}/message_templates");

            if ($response->successful()) {
                return $response->json('data', []);
            }

            return [];
        } catch (\Exception $e) {
            Log::error('WhatsApp get templates failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Download media from WhatsApp
     */
    public function downloadMedia(string $mediaId): ?string
    {
        try {
            // First get the media URL
            $response = Http::withToken($this->accessToken)
                ->get("{$this->apiUrl}/{$mediaId}");

            if (!$response->successful()) {
                return null;
            }

            $mediaUrl = $response->json('url');

            // Then download the actual file
            $mediaResponse = Http::withToken($this->accessToken)
                ->get($mediaUrl);

            if ($mediaResponse->successful()) {
                return $mediaResponse->body();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('WhatsApp download media failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Process incoming webhook
     */
    public function processWebhook(array $payload): ?array
    {
        $entry = $payload['entry'][0] ?? null;
        if (!$entry) {
            return null;
        }

        $changes = $entry['changes'][0] ?? null;
        if (!$changes || $changes['field'] !== 'messages') {
            return null;
        }

        $value = $changes['value'];
        $messages = $value['messages'] ?? [];
        $statuses = $value['statuses'] ?? [];
        $contacts = $value['contacts'] ?? [];

        $result = [];

        // Process messages
        foreach ($messages as $message) {
            $result['messages'][] = $this->parseIncomingMessage($message, $contacts);
        }

        // Process status updates
        foreach ($statuses as $status) {
            $result['statuses'][] = $this->parseStatusUpdate($status);
        }

        return $result;
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
    protected function sendMessage(string $to, array $messageData): array
    {
        try {
            $payload = array_merge([
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $to,
            ], $messageData);

            $response = Http::withToken($this->accessToken)
                ->post("{$this->apiUrl}/{$this->phoneNumberId}/messages", $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message_id' => $response->json('messages.0.id'),
                    'response' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('error.message', 'Unknown error'),
                'response' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp send message failed', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Parse incoming message
     */
    protected function parseIncomingMessage(array $message, array $contacts): array
    {
        $contact = $contacts[0] ?? [];

        return [
            'id' => $message['id'],
            'from' => $message['from'],
            'timestamp' => $message['timestamp'],
            'type' => $message['type'],
            'content' => $this->extractMessageContent($message),
            'contact_name' => $contact['profile']['name'] ?? null,
            'contact_wa_id' => $contact['wa_id'] ?? null,
        ];
    }

    /**
     * Extract content from message based on type
     */
    protected function extractMessageContent(array $message): array
    {
        $type = $message['type'];

        return match ($type) {
            'text' => ['text' => $message['text']['body']],
            'image' => [
                'media_id' => $message['image']['id'],
                'mime_type' => $message['image']['mime_type'],
                'caption' => $message['image']['caption'] ?? null,
            ],
            'document' => [
                'media_id' => $message['document']['id'],
                'mime_type' => $message['document']['mime_type'],
                'filename' => $message['document']['filename'] ?? null,
                'caption' => $message['document']['caption'] ?? null,
            ],
            'audio' => [
                'media_id' => $message['audio']['id'],
                'mime_type' => $message['audio']['mime_type'],
            ],
            'video' => [
                'media_id' => $message['video']['id'],
                'mime_type' => $message['video']['mime_type'],
                'caption' => $message['video']['caption'] ?? null,
            ],
            'location' => [
                'latitude' => $message['location']['latitude'],
                'longitude' => $message['location']['longitude'],
                'name' => $message['location']['name'] ?? null,
                'address' => $message['location']['address'] ?? null,
            ],
            'contacts' => ['contacts' => $message['contacts']],
            'interactive' => [
                'type' => $message['interactive']['type'],
                'response' => $message['interactive']['button_reply'] ?? $message['interactive']['list_reply'] ?? null,
            ],
            default => ['raw' => $message[$type] ?? null],
        };
    }

    /**
     * Parse status update
     */
    protected function parseStatusUpdate(array $status): array
    {
        return [
            'id' => $status['id'],
            'status' => $status['status'],
            'timestamp' => $status['timestamp'],
            'recipient_id' => $status['recipient_id'],
            'errors' => $status['errors'] ?? null,
        ];
    }
}
