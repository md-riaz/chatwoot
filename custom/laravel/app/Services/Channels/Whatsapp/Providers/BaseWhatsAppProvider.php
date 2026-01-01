<?php

namespace App\Services\Channels\Whatsapp\Providers;

use App\Models\Channels\Whatsapp;
use App\Models\Message;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class BaseWhatsAppProvider
{
    protected Whatsapp $whatsappChannel;

    public function __construct(Whatsapp $whatsappChannel)
    {
        $this->whatsappChannel = $whatsappChannel;
    }

    /**
     * Send a message via WhatsApp
     */
    abstract public function sendMessage(string $phoneNumber, Message $message): ?string;

    /**
     * Send a template message
     */
    abstract public function sendTemplate(string $phoneNumber, array $templateInfo, Message $message): ?string;

    /**
     * Sync message templates from provider
     */
    abstract public function syncTemplates(): void;

    /**
     * Validate provider configuration
     */
    abstract public function validateProviderConfig(): bool;

    /**
     * Get media URL for downloading
     */
    abstract public function getMediaUrl(string $mediaId): string;

    /**
     * Get API headers for requests
     */
    abstract public function getApiHeaders(): array;

    /**
     * Process API response and handle errors
     */
    protected function processResponse($response, Message $message = null): ?string
    {
        if ($response->successful()) {
            $data = $response->json();
            
            if (empty($data['error'])) {
                return $data['messages'][0]['id'] ?? null;
            }
        }

        $this->handleError($response, $message);
        return null;
    }

    /**
     * Handle API errors
     */
    protected function handleError($response, Message $message = null): void
    {
        Log::error('WhatsApp API Error', [
            'status' => $response->status(),
            'body' => $response->body(),
            'message_id' => $message?->id,
            'channel_id' => $this->whatsappChannel->id
        ]);

        if ($message) {
            $errorMessage = $this->extractErrorMessage($response);
            if ($errorMessage) {
                $message->update(['external_error' => $errorMessage]);
            }
        }

        // Check if it's an authorization error
        if ($this->isAuthorizationError($response)) {
            $this->whatsappChannel->authorizationError();
        }
    }

    /**
     * Extract error message from response
     */
    protected function extractErrorMessage($response): ?string
    {
        $data = $response->json();
        
        if (isset($data['error']['message'])) {
            return $data['error']['message'];
        }

        if (isset($data['error']['error_data']['details'])) {
            return $data['error']['error_data']['details'];
        }

        return "HTTP {$response->status()}: {$response->body()}";
    }

    /**
     * Check if error is authorization related
     */
    protected function isAuthorizationError($response): bool
    {
        $status = $response->status();
        
        // Common authorization error status codes
        if (in_array($status, [401, 403])) {
            return true;
        }

        $data = $response->json();
        $errorCode = $data['error']['code'] ?? null;
        
        // WhatsApp specific authorization error codes
        return in_array($errorCode, [190, 102, 10, 2500]);
    }

    /**
     * Create template body parameters
     */
    protected function createTemplateBodyParameters(array $templateInfo): array
    {
        $templateBody = [
            'name' => $templateInfo['name'],
            'language' => [
                'code' => $templateInfo['lang_code'] ?? 'en'
            ]
        ];

        if (!empty($templateInfo['processed_params'])) {
            $templateBody['components'] = $templateInfo['processed_params'];
        }

        return $templateBody;
    }

    /**
     * Create interactive message components
     */
    protected function createInteractiveComponents(Message $message): array
    {
        $contentAttributes = $message->content_attributes ?? [];
        
        if (empty($contentAttributes['items'])) {
            return [];
        }

        $buttons = [];
        foreach ($contentAttributes['items'] as $item) {
            $buttons[] = [
                'type' => 'reply',
                'reply' => [
                    'id' => $item['value'] ?? $item['title'],
                    'title' => $item['title']
                ]
            ];
        }

        return [
            'type' => 'button',
            'body' => [
                'text' => $message->content
            ],
            'action' => [
                'buttons' => array_slice($buttons, 0, 3) // WhatsApp allows max 3 buttons
            ]
        ];
    }
}