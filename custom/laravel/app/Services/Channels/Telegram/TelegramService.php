<?php

namespace App\Services\Channels\Telegram;

use App\Models\Inbox;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected string $apiUrl = 'https://api.telegram.org/bot';
    protected ?string $botToken;

    public function __construct(?Inbox $inbox = null)
    {
        if ($inbox && $inbox->channel) {
            $this->botToken = $inbox->channel->bot_token ?? null;
        }
    }

    public function setToken(string $token): self
    {
        $this->botToken = $token;
        return $this;
    }

    /**
     * Get bot information
     */
    public function getMe(): ?array
    {
        return $this->makeRequest('getMe');
    }

    /**
     * Set webhook URL
     */
    public function setWebhook(string $url, ?string $secretToken = null): bool
    {
        $params = ['url' => $url];
        
        if ($secretToken) {
            $params['secret_token'] = $secretToken;
        }

        $result = $this->makeRequest('setWebhook', $params);
        return $result['ok'] ?? false;
    }

    /**
     * Delete webhook
     */
    public function deleteWebhook(): bool
    {
        $result = $this->makeRequest('deleteWebhook');
        return $result['ok'] ?? false;
    }

    /**
     * Get webhook info
     */
    public function getWebhookInfo(): ?array
    {
        return $this->makeRequest('getWebhookInfo');
    }

    /**
     * Send a text message
     */
    public function sendTextMessage(int|string $chatId, string $text, array $options = []): array
    {
        $params = array_merge([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ], $options);

        return $this->makeRequest('sendMessage', $params) ?? ['success' => false];
    }

    /**
     * Send a photo
     */
    public function sendPhoto(int|string $chatId, string $photo, ?string $caption = null, array $options = []): array
    {
        $params = array_merge([
            'chat_id' => $chatId,
            'photo' => $photo,
        ], $options);

        if ($caption) {
            $params['caption'] = $caption;
            $params['parse_mode'] = 'HTML';
        }

        return $this->makeRequest('sendPhoto', $params) ?? ['success' => false];
    }

    /**
     * Send a document
     */
    public function sendDocument(int|string $chatId, string $document, ?string $caption = null, array $options = []): array
    {
        $params = array_merge([
            'chat_id' => $chatId,
            'document' => $document,
        ], $options);

        if ($caption) {
            $params['caption'] = $caption;
            $params['parse_mode'] = 'HTML';
        }

        return $this->makeRequest('sendDocument', $params) ?? ['success' => false];
    }

    /**
     * Send audio
     */
    public function sendAudio(int|string $chatId, string $audio, ?string $caption = null, array $options = []): array
    {
        $params = array_merge([
            'chat_id' => $chatId,
            'audio' => $audio,
        ], $options);

        if ($caption) {
            $params['caption'] = $caption;
            $params['parse_mode'] = 'HTML';
        }

        return $this->makeRequest('sendAudio', $params) ?? ['success' => false];
    }

    /**
     * Send video
     */
    public function sendVideo(int|string $chatId, string $video, ?string $caption = null, array $options = []): array
    {
        $params = array_merge([
            'chat_id' => $chatId,
            'video' => $video,
        ], $options);

        if ($caption) {
            $params['caption'] = $caption;
            $params['parse_mode'] = 'HTML';
        }

        return $this->makeRequest('sendVideo', $params) ?? ['success' => false];
    }

    /**
     * Send location
     */
    public function sendLocation(int|string $chatId, float $latitude, float $longitude, array $options = []): array
    {
        $params = array_merge([
            'chat_id' => $chatId,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ], $options);

        return $this->makeRequest('sendLocation', $params) ?? ['success' => false];
    }

    /**
     * Send message with inline keyboard
     */
    public function sendMessageWithInlineKeyboard(int|string $chatId, string $text, array $keyboard): array
    {
        return $this->sendTextMessage($chatId, $text, [
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard,
            ]),
        ]);
    }

    /**
     * Send message with reply keyboard
     */
    public function sendMessageWithReplyKeyboard(int|string $chatId, string $text, array $keyboard, bool $oneTime = true, bool $resize = true): array
    {
        return $this->sendTextMessage($chatId, $text, [
            'reply_markup' => json_encode([
                'keyboard' => $keyboard,
                'one_time_keyboard' => $oneTime,
                'resize_keyboard' => $resize,
            ]),
        ]);
    }

    /**
     * Remove reply keyboard
     */
    public function removeReplyKeyboard(int|string $chatId, string $text): array
    {
        return $this->sendTextMessage($chatId, $text, [
            'reply_markup' => json_encode([
                'remove_keyboard' => true,
            ]),
        ]);
    }

    /**
     * Send chat action (typing, upload_photo, etc.)
     */
    public function sendChatAction(int|string $chatId, string $action = 'typing'): bool
    {
        $result = $this->makeRequest('sendChatAction', [
            'chat_id' => $chatId,
            'action' => $action,
        ]);

        return $result['ok'] ?? false;
    }

    /**
     * Get file info
     */
    public function getFile(string $fileId): ?array
    {
        $result = $this->makeRequest('getFile', ['file_id' => $fileId]);
        return $result['result'] ?? null;
    }

    /**
     * Get file download URL
     */
    public function getFileUrl(string $filePath): string
    {
        return "https://api.telegram.org/file/bot{$this->botToken}/{$filePath}";
    }

    /**
     * Download file
     */
    public function downloadFile(string $fileId): ?string
    {
        $fileInfo = $this->getFile($fileId);
        if (!$fileInfo || !isset($fileInfo['file_path'])) {
            return null;
        }

        try {
            $response = Http::get($this->getFileUrl($fileInfo['file_path']));
            if ($response->successful()) {
                return $response->body();
            }
        } catch (\Exception $e) {
            Log::error('Telegram download file failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Answer callback query
     */
    public function answerCallbackQuery(string $callbackQueryId, ?string $text = null, bool $showAlert = false): bool
    {
        $params = ['callback_query_id' => $callbackQueryId];

        if ($text) {
            $params['text'] = $text;
            $params['show_alert'] = $showAlert;
        }

        $result = $this->makeRequest('answerCallbackQuery', $params);
        return $result['ok'] ?? false;
    }

    /**
     * Edit message text
     */
    public function editMessageText(int|string $chatId, int $messageId, string $text, array $options = []): array
    {
        $params = array_merge([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ], $options);

        return $this->makeRequest('editMessageText', $params) ?? ['success' => false];
    }

    /**
     * Delete message
     */
    public function deleteMessage(int|string $chatId, int $messageId): bool
    {
        $result = $this->makeRequest('deleteMessage', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ]);

        return $result['ok'] ?? false;
    }

    /**
     * Process incoming webhook update
     */
    public function processWebhook(array $update): array
    {
        $result = [
            'update_id' => $update['update_id'],
        ];

        if (isset($update['message'])) {
            $result['type'] = 'message';
            $result['data'] = $this->parseMessage($update['message']);
        } elseif (isset($update['edited_message'])) {
            $result['type'] = 'edited_message';
            $result['data'] = $this->parseMessage($update['edited_message']);
        } elseif (isset($update['callback_query'])) {
            $result['type'] = 'callback_query';
            $result['data'] = $this->parseCallbackQuery($update['callback_query']);
        } elseif (isset($update['channel_post'])) {
            $result['type'] = 'channel_post';
            $result['data'] = $this->parseMessage($update['channel_post']);
        }

        return $result;
    }

    /**
     * Make API request
     */
    protected function makeRequest(string $method, array $params = []): ?array
    {
        try {
            $response = Http::post("{$this->apiUrl}{$this->botToken}/{$method}", $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Telegram API request failed', [
                'method' => $method,
                'response' => $response->json(),
            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Telegram API request exception', [
                'method' => $method,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Parse message from update
     */
    protected function parseMessage(array $message): array
    {
        $result = [
            'message_id' => $message['message_id'],
            'date' => $message['date'],
            'chat' => [
                'id' => $message['chat']['id'],
                'type' => $message['chat']['type'],
                'title' => $message['chat']['title'] ?? null,
                'username' => $message['chat']['username'] ?? null,
                'first_name' => $message['chat']['first_name'] ?? null,
                'last_name' => $message['chat']['last_name'] ?? null,
            ],
        ];

        if (isset($message['from'])) {
            $result['from'] = [
                'id' => $message['from']['id'],
                'is_bot' => $message['from']['is_bot'] ?? false,
                'first_name' => $message['from']['first_name'] ?? null,
                'last_name' => $message['from']['last_name'] ?? null,
                'username' => $message['from']['username'] ?? null,
                'language_code' => $message['from']['language_code'] ?? null,
            ];
        }

        if (isset($message['text'])) {
            $result['text'] = $message['text'];
            $result['entities'] = $message['entities'] ?? [];
        }

        if (isset($message['photo'])) {
            $result['photo'] = end($message['photo']);
        }

        if (isset($message['document'])) {
            $result['document'] = $message['document'];
        }

        if (isset($message['audio'])) {
            $result['audio'] = $message['audio'];
        }

        if (isset($message['video'])) {
            $result['video'] = $message['video'];
        }

        if (isset($message['voice'])) {
            $result['voice'] = $message['voice'];
        }

        if (isset($message['location'])) {
            $result['location'] = $message['location'];
        }

        if (isset($message['contact'])) {
            $result['contact'] = $message['contact'];
        }

        if (isset($message['sticker'])) {
            $result['sticker'] = $message['sticker'];
        }

        if (isset($message['caption'])) {
            $result['caption'] = $message['caption'];
        }

        if (isset($message['reply_to_message'])) {
            $result['reply_to_message_id'] = $message['reply_to_message']['message_id'];
        }

        return $result;
    }

    /**
     * Parse callback query
     */
    protected function parseCallbackQuery(array $query): array
    {
        return [
            'id' => $query['id'],
            'from' => [
                'id' => $query['from']['id'],
                'first_name' => $query['from']['first_name'] ?? null,
                'last_name' => $query['from']['last_name'] ?? null,
                'username' => $query['from']['username'] ?? null,
            ],
            'message' => isset($query['message']) ? $this->parseMessage($query['message']) : null,
            'data' => $query['data'] ?? null,
            'chat_instance' => $query['chat_instance'] ?? null,
        ];
    }
}
