<?php

namespace App\Services\Channels\Whatsapp\Providers;

use App\Models\Message;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsApp360DialogProvider extends BaseWhatsAppProvider
{
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
            'to' => $phoneNumber,
            'template' => $this->createTemplateBodyParameters($templateInfo),
            'type' => 'template'
        ];

        $response = Http::withHeaders($this->getApiHeaders())
            ->post($this->getApiBaseUrl() . '/messages', $requestBody);

        return $this->processResponse($response, $message);
    }

    public function syncTemplates(): void
    {
        try {
            // Mark as updated to prevent repeated sync attempts on failure
            $this->whatsappChannel->markMessageTemplatesUpdated();
            
            $response = Http::withHeaders($this->getApiHeaders())
                ->get($this->getApiBaseUrl() . '/configs/templates');
                
            if ($response->successful()) {
                $templates = $response->json('waba_templates', []);
                
                $this->whatsappChannel->update([
                    'message_templates' => $templates,
                    'message_templates_last_updated' => now()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp 360Dialog template sync failed', [
                'channel_id' => $this->whatsappChannel->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function validateProviderConfig(): bool
    {
        try {
            $webhookUrl = config('app.url') . "/webhooks/whatsapp/{$this->whatsappChannel->phone_number}";
            
            $response = Http::withHeaders([
                'D360-API-KEY' => $this->whatsappChannel->provider_config['api_key'],
                'Content-Type' => 'application/json'
            ])->post($this->getApiBaseUrl() . '/configs/webhook', [
                'url' => $webhookUrl
            ]);
            
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('WhatsApp 360Dialog config validation failed', [
                'channel_id' => $this->whatsappChannel->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function getMediaUrl(string $mediaId): string
    {
        return $this->getApiBaseUrl() . "/media/{$mediaId}";
    }

    public function getApiHeaders(): array
    {
        return [
            'D360-API-KEY' => $this->whatsappChannel->provider_config['api_key'],
            'Content-Type' => 'application/json'
        ];
    }

    private function sendTextMessage(string $phoneNumber, Message $message): ?string
    {
        $requestBody = [
            'to' => $phoneNumber,
            'text' => ['body' => $message->content],
            'type' => 'text'
        ];

        $response = Http::withHeaders($this->getApiHeaders())
            ->post($this->getApiBaseUrl() . '/messages', $requestBody);

        return $this->processResponse($response, $message);
    }

    private function sendInteractiveMessage(string $phoneNumber, Message $message): ?string
    {
        $interactive = $this->createInteractiveComponents($message);
        
        $requestBody = [
            'to' => $phoneNumber,
            'type' => 'interactive',
            'interactive' => $interactive
        ];

        $response = Http::withHeaders($this->getApiHeaders())
            ->post($this->getApiBaseUrl() . '/messages', $requestBody);

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
            'to' => $phoneNumber,
            'type' => $type,
            $type => $mediaObject
        ];

        $response = Http::withHeaders($this->getApiHeaders())
            ->post($this->getApiBaseUrl() . '/messages', $requestBody);

        return $this->processResponse($response, $message);
    }

    private function getApiBaseUrl(): string
    {
        // Use environment variable for sandbox testing: 'https://waba-sandbox.360dialog.io/v1'
        return config('services.360dialog.base_url', 'https://waba.360dialog.io/v1');
    }
}