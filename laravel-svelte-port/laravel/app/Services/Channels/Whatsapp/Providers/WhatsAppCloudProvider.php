<?php

namespace App\Services\Channels\Whatsapp\Providers;

use App\Models\Message;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppCloudProvider extends BaseWhatsAppProvider
{
    private const API_VERSION = 'v18.0';
    private const BASE_URL = 'https://graph.facebook.com';

    public function sendMessage(string $phoneNumber, Message $message): ?string
    {
        if ($message->attachments->isNotEmpty()) {
            return $this->sendAttachmentMessage($phoneNumber, $message);
        }

        if ($message->content_type === 'input_select') {
            return $this->sendInteractiveMessage($phoneNumber, $message);
        }

        return $this->sendTextMessage($phoneNumber, $message);
    }

    public function sendTemplate(string $phoneNumber, array $templateInfo, Message $message): ?string
    {
        $requestBody = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $phoneNumber,
            'type' => 'template',
            'template' => $this->createTemplateBodyParameters($templateInfo)
        ];

        $response = Http::withHeaders($this->getApiHeaders())
            ->post($this->getMessagesUrl(), $requestBody);

        return $this->processResponse($response, $message);
    }

    public function syncTemplates(): void
    {
        try {
            // Mark as updated to prevent repeated sync attempts on failure
            $this->whatsappChannel->markMessageTemplatesUpdated();
            
            $templates = $this->fetchAllTemplates();
            
            if (!empty($templates)) {
                $this->whatsappChannel->update([
                    'message_templates' => $templates,
                    'message_templates_last_updated' => now()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp Cloud template sync failed', [
                'channel_id' => $this->whatsappChannel->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function validateProviderConfig(): bool
    {
        try {
            $response = Http::withHeaders($this->getApiHeaders())
                ->get($this->getTemplatesUrl());
                
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('WhatsApp Cloud config validation failed', [
                'channel_id' => $this->whatsappChannel->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function getMediaUrl(string $mediaId): string
    {
        $phoneNumberId = $this->whatsappChannel->provider_config['phone_number_id'] ?? '';
        $url = $this->getApiBaseUrl() . "/{$mediaId}";
        
        if ($phoneNumberId) {
            $url .= "?phone_number_id={$phoneNumberId}";
        }
        
        return $url;
    }

    public function getApiHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->whatsappChannel->provider_config['api_key'],
            'Content-Type' => 'application/json'
        ];
    }

    private function sendTextMessage(string $phoneNumber, Message $message): ?string
    {
        $requestBody = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $phoneNumber,
            'type' => 'text',
            'text' => [
                'body' => $message->content
            ]
        ];

        $response = Http::withHeaders($this->getApiHeaders())
            ->post($this->getMessagesUrl(), $requestBody);

        return $this->processResponse($response, $message);
    }

    private function sendInteractiveMessage(string $phoneNumber, Message $message): ?string
    {
        $interactive = $this->createInteractiveComponents($message);
        
        $requestBody = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $phoneNumber,
            'type' => 'interactive',
            'interactive' => $interactive
        ];

        $response = Http::withHeaders($this->getApiHeaders())
            ->post($this->getMessagesUrl(), $requestBody);

        return $this->processResponse($response, $message);
    }

    private function sendAttachmentMessage(string $phoneNumber, Message $message): ?string
    {
        $attachment = $message->attachments->first();
        $type = in_array($attachment->file_type, ['image', 'audio', 'video']) 
            ? $attachment->file_type 
            : 'document';

        $mediaObject = [
            'link' => $attachment->file_url
        ];

        if (!in_array($type, ['audio', 'sticker']) && $message->content) {
            $mediaObject['caption'] = $message->content;
        }

        $requestBody = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $phoneNumber,
            'type' => $type,
            $type => $mediaObject
        ];

        $response = Http::withHeaders($this->getApiHeaders())
            ->post($this->getMessagesUrl(), $requestBody);

        return $this->processResponse($response, $message);
    }

    private function fetchAllTemplates(): array
    {
        $allTemplates = [];
        $url = $this->getTemplatesUrl();
        
        do {
            $response = Http::withHeaders($this->getApiHeaders())->get($url);
            
            if (!$response->successful()) {
                break;
            }
            
            $data = $response->json();
            $allTemplates = array_merge($allTemplates, $data['data'] ?? []);
            
            $url = $data['paging']['next'] ?? null;
        } while ($url);
        
        return $allTemplates;
    }

    private function getApiBaseUrl(): string
    {
        return self::BASE_URL . '/' . self::API_VERSION;
    }

    private function getMessagesUrl(): string
    {
        $phoneNumberId = $this->whatsappChannel->provider_config['phone_number_id'];
        return $this->getApiBaseUrl() . "/{$phoneNumberId}/messages";
    }

    private function getTemplatesUrl(): string
    {
        $businessAccountId = $this->whatsappChannel->provider_config['business_account_id'];
        return $this->getApiBaseUrl() . "/{$businessAccountId}/message_templates";
    }
}