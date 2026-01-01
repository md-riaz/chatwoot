<?php

namespace App\Services\Channels\Whatsapp\Providers;

use App\Models\Message;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Whatsapp360DialogService extends BaseService
{
    public function sendMessage(string $phoneNumber, Message $message): ?string
    {
        if ($message->attachments->isNotEmpty()) {
            return $this->sendAttachmentMessage($phoneNumber, $message);
        }

        if ($message->content_type === 'input_select') {
            return $this->sendInteractiveTextMessage($phoneNumber, $message);
        }

        return $this->sendTextMessage($phoneNumber, $message);
    }

    public function sendTemplate(string $phoneNumber, array $templateInfo, Message $message): ?string
    {
        $payload = [
            'to' => $phoneNumber,
            'template' => $this->templateBodyParameters($templateInfo),
            'type' => 'template',
        ];

        try {
            $response = Http::withHeaders($this->apiHeaders())
                ->post($this->apiBasePath() . '/messages', $payload);

            return $this->processResponse($response->json(), $message);
        } catch (\Exception $e) {
            Log::error('WhatsApp 360Dialog template send failed', [
                'error' => $e->getMessage(),
                'phone' => $phoneNumber,
            ]);
            return null;
        }
    }

    public function syncTemplates(): void
    {
        // Mark as updated to prevent continuous sync attempts on error
        $this->whatsappChannel->update(['message_templates_last_updated' => now()]);

        try {
            $response = Http::withHeaders($this->apiHeaders())
                ->get($this->apiBasePath() . '/configs/templates');

            if ($response->successful()) {
                $templates = $response->json('waba_templates', []);
                $this->whatsappChannel->update([
                    'message_templates' => $templates,
                    'message_templates_last_updated' => now(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp 360Dialog template sync failed', ['error' => $e->getMessage()]);
        }
    }

    public function validateProviderConfig(): bool
    {
        try {
            $response = Http::withHeaders([
                'D360-API-KEY' => $this->whatsappChannel->provider_config['api_key'],
                'Content-Type' => 'application/json',
            ])->post($this->apiBasePath() . '/configs/webhook', [
                'url' => config('app.url') . '/webhooks/whatsapp/' . $this->whatsappChannel->phone_number,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('WhatsApp 360Dialog config validation failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getErrorMessage(array $response): ?string
    {
        return $response['meta']['developer_message'] ?? null;
    }

    public function mediaUrl(string $mediaId): string
    {
        return $this->apiBasePath() . '/media/' . $mediaId;
    }

    public function apiHeaders(): array
    {
        return [
            'D360-API-KEY' => $this->whatsappChannel->provider_config['api_key'],
            'Content-Type' => 'application/json',
        ];
    }

    private function apiBasePath(): string
    {
        return config('services.whatsapp.360dialog_base_url', 'https://waba.360dialog.io/v1');
    }

    private function sendTextMessage(string $phoneNumber, Message $message): ?string
    {
        $payload = [
            'to' => $phoneNumber,
            'text' => ['body' => $message->content],
            'type' => 'text',
        ];

        try {
            $response = Http::withHeaders($this->apiHeaders())
                ->post($this->apiBasePath() . '/messages', $payload);

            return $this->processResponse($response->json(), $message);
        } catch (\Exception $e) {
            Log::error('WhatsApp 360Dialog text send failed', [
                'error' => $e->getMessage(),
                'phone' => $phoneNumber,
            ]);
            return null;
        }
    }

    private function sendAttachmentMessage(string $phoneNumber, Message $message): ?string
    {
        $attachment = $message->attachments->first();
        $type = in_array($attachment->file_type, ['image', 'audio', 'video']) 
            ? $attachment->file_type 
            : 'document';

        $typeContent = ['link' => $attachment->download_url];

        if (!in_array($type, ['audio', 'sticker'])) {
            $typeContent['caption'] = $message->content;
        }

        if ($type === 'document') {
            $typeContent['filename'] = $attachment->file_name;
        }

        $payload = [
            'to' => $phoneNumber,
            'type' => $type,
            $type => $typeContent,
        ];

        try {
            $response = Http::withHeaders($this->apiHeaders())
                ->post($this->apiBasePath() . '/messages', $payload);

            return $this->processResponse($response->json(), $message);
        } catch (\Exception $e) {
            Log::error('WhatsApp 360Dialog attachment send failed', [
                'error' => $e->getMessage(),
                'phone' => $phoneNumber,
            ]);
            return null;
        }
    }

    private function sendInteractiveTextMessage(string $phoneNumber, Message $message): ?string
    {
        $payload = $this->createPayloadBasedOnItems($message);

        $requestBody = [
            'to' => $phoneNumber,
            'interactive' => $payload,
            'type' => 'interactive',
        ];

        try {
            $response = Http::withHeaders($this->apiHeaders())
                ->post($this->apiBasePath() . '/messages', $requestBody);

            return $this->processResponse($response->json(), $message);
        } catch (\Exception $e) {
            Log::error('WhatsApp 360Dialog interactive send failed', [
                'error' => $e->getMessage(),
                'phone' => $phoneNumber,
            ]);
            return null;
        }
    }

    private function templateBodyParameters(array $templateInfo): array
    {
        return [
            'name' => $templateInfo['name'],
            'namespace' => $templateInfo['namespace'],
            'language' => [
                'policy' => 'deterministic',
                'code' => $templateInfo['lang_code'],
            ],
            'components' => $templateInfo['parameters'] ?? [],
        ];
    }
}