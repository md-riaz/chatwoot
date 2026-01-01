<?php

namespace App\Services\Channels\Whatsapp\Providers;

use App\Models\Channels\Whatsapp;
use App\Models\Message;
use Illuminate\Support\Facades\Log;

/**
 * Base WhatsApp Provider Service
 * 
 * To create a WhatsApp provider:
 * - Inherit this as the base class
 * - Implement `sendMessage` method in your child class
 * - Implement `sendTemplate` method in your child class
 * - Implement `syncTemplates` method in your child class
 * - Implement `validateProviderConfig` method in your child class
 * - Use ChildClass::make($whatsappChannel)->perform()
 */
abstract class BaseService
{
    protected Whatsapp $whatsappChannel;

    public function __construct(Whatsapp $whatsappChannel)
    {
        $this->whatsappChannel = $whatsappChannel;
    }

    public static function make(Whatsapp $whatsappChannel): static
    {
        return new static($whatsappChannel);
    }

    abstract public function sendMessage(string $phoneNumber, Message $message): ?string;

    abstract public function sendTemplate(string $phoneNumber, array $templateInfo, Message $message): ?string;

    abstract public function syncTemplates(): void;

    abstract public function validateProviderConfig(): bool;

    abstract public function getErrorMessage(array $response): ?string;

    abstract public function mediaUrl(string $mediaId): string;

    abstract public function apiHeaders(): array;

    /**
     * Process API response and handle errors
     */
    protected function processResponse(array $response, Message $message): ?string
    {
        if (isset($response['messages'][0]['id']) && empty($response['error'])) {
            return $response['messages'][0]['id'];
        }

        $this->handleError($response, $message);
        return null;
    }

    /**
     * Handle API errors
     */
    protected function handleError(array $response, Message $message): void
    {
        Log::error('WhatsApp API Error', ['response' => $response]);

        if (!$message) {
            return;
        }

        $errorMessage = $this->getErrorMessage($response);
        if (!$errorMessage) {
            return;
        }

        $message->update([
            'external_error' => $errorMessage,
            'status' => Message::STATUS_FAILED,
        ]);
    }

    /**
     * Create buttons for interactive messages
     */
    protected function createButtons(array $items): array
    {
        $buttons = [];
        foreach ($items as $item) {
            $buttons[] = [
                'type' => 'reply',
                'reply' => [
                    'id' => $item['value'],
                    'title' => $item['title'],
                ],
            ];
        }
        return $buttons;
    }

    /**
     * Create rows for list messages
     */
    protected function createRows(array $items): array
    {
        $rows = [];
        foreach ($items as $item) {
            $rows[] = [
                'id' => $item['value'],
                'title' => $item['title'],
            ];
        }
        return $rows;
    }

    /**
     * Create interactive payload
     */
    protected function createPayload(string $type, string $messageContent, array $action): array
    {
        return [
            'type' => $type,
            'body' => ['text' => $messageContent],
            'action' => $action,
        ];
    }

    /**
     * Create payload based on items count
     */
    protected function createPayloadBasedOnItems(Message $message): array
    {
        $items = $message->content_attributes['items'] ?? [];
        
        if (count($items) <= 3) {
            return $this->createButtonPayload($message);
        }

        return $this->createListPayload($message);
    }

    /**
     * Create button payload for interactive messages
     */
    protected function createButtonPayload(Message $message): array
    {
        $items = $message->content_attributes['items'] ?? [];
        $buttons = $this->createButtons($items);
        
        return $this->createPayload('button', $message->content, ['buttons' => $buttons]);
    }

    /**
     * Create list payload for interactive messages
     */
    protected function createListPayload(Message $message): array
    {
        $items = $message->content_attributes['items'] ?? [];
        $rows = $this->createRows($items);
        $sections = [['rows' => $rows]];
        
        return $this->createPayload('list', $message->content, [
            'button' => __('conversations.messages.whatsapp.list_button_label'),
            'sections' => $sections,
        ]);
    }
}