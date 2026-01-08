<?php

namespace App\Services\Channels\Whatsapp\Providers;

use App\Models\Message;
use App\Services\Http\RetryableHttpClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappCloudService extends BaseService
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
        $templateBody = $this->templateBodyParameters($templateInfo);

        $requestBody = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $phoneNumber,
            'type' => 'template',
            'template' => $templateBody,
        ];

        try {
            $response = Http::withHeaders($this->apiHeaders())
                ->post($this->phoneIdPath() . '/messages', $requestBody);

            return $this->processResponse($response->json(), $message);
        } catch (\Exception $e) {
            Log::error('WhatsApp Cloud template send failed', [
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

        $url = $this->businessAccountPath() . '/message_templates';
        $templates = $this->fetchWhatsappTemplates($url);

        if (!empty($templates)) {
            $this->whatsappChannel->update([
                'message_templates' => $templates,
                'message_templates_last_updated' => now(),
            ]);
        }
    }

    public function validateProviderConfig(): bool
    {
        try {
            $response = Http::withHeaders($this->apiHeaders())
                ->get($this->businessAccountPath() . '/message_templates');

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('WhatsApp Cloud config validation failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getErrorMessage(array $response): ?string
    {
        return $response['error']['message'] ?? null;
    }

    public function mediaUrl(string $mediaId, ?string $phoneNumberId = null): string
    {
        $url = $this->apiBasePath() . '/v13.0/' . $mediaId;
        if ($phoneNumberId) {
            $url .= '?phone_number_id=' . $phoneNumberId;
        }
        return $url;
    }

    public function apiHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->whatsappChannel->provider_config['api_key'],
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Create CSAT template
     */
    public function createCsatTemplate(array $templateConfig): array
    {
        // Implementation would go here - delegated to CSAT template service
        return ['success' => false, 'message' => 'Not implemented'];
    }

    /**
     * Delete CSAT template
     */
    public function deleteCsatTemplate(?string $templateName = null): array
    {
        // Implementation would go here - delegated to CSAT template service
        return ['success' => false, 'message' => 'Not implemented'];
    }

    /**
     * Get template status
     */
    public function getTemplateStatus(string $templateName): array
    {
        // Implementation would go here - delegated to CSAT template service
        return ['success' => false, 'message' => 'Not implemented'];
    }

    private function apiBasePath(): string
    {
        return config('services.whatsapp.cloud_base_url', 'https://graph.facebook.com');
    }

    private function phoneIdPath(): string
    {
        return $this->apiBasePath() . '/v13.0/' . $this->whatsappChannel->provider_config['phone_number_id'];
    }

    private function businessAccountPath(): string
    {
        return $this->apiBasePath() . '/v14.0/' . $this->whatsappChannel->provider_config['business_account_id'];
    }

    private function sendTextMessage(string $phoneNumber, Message $message): ?string
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'context' => $this->whatsappReplyContext($message),
            'to' => $phoneNumber,
            'text' => ['body' => $message->content],
            'type' => 'text',
        ];

        try {
            $response = Http::withHeaders($this->apiHeaders())
                ->post($this->phoneIdPath() . '/messages', $payload);

            return $this->processResponse($response->json(), $message);
        } catch (\Exception $e) {
            Log::error('WhatsApp Cloud text send failed', [
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
            'messaging_product' => 'whatsapp',
            'context' => $this->whatsappReplyContext($message),
            'to' => $phoneNumber,
            'type' => $type,
            $type => $typeContent,
        ];

        try {
            $response = Http::withHeaders($this->apiHeaders())
                ->post($this->phoneIdPath() . '/messages', $payload);

            return $this->processResponse($response->json(), $message);
        } catch (\Exception $e) {
            Log::error('WhatsApp Cloud attachment send failed', [
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
            'messaging_product' => 'whatsapp',
            'to' => $phoneNumber,
            'interactive' => $payload,
            'type' => 'interactive',
        ];

        try {
            $response = Http::withHeaders($this->apiHeaders())
                ->post($this->phoneIdPath() . '/messages', $requestBody);

            return $this->processResponse($response->json(), $message);
        } catch (\Exception $e) {
            Log::error('WhatsApp Cloud interactive send failed', [
                'error' => $e->getMessage(),
                'phone' => $phoneNumber,
            ]);
            return null;
        }
    }

    private function templateBodyParameters(array $templateInfo): array
    {
        $templateBody = [
            'name' => $templateInfo['name'],
            'language' => [
                'policy' => 'deterministic',
                'code' => $templateInfo['lang_code'],
            ],
        ];

        if (!empty($templateInfo['parameters'])) {
            $templateBody['components'] = $templateInfo['parameters'];
        }

        return $templateBody;
    }

    private function whatsappReplyContext(Message $message): ?array
    {
        $replyTo = $message->content_attributes['in_reply_to_external_id'] ?? null;
        
        if (!$replyTo) {
            return null;
        }

        return ['message_id' => $replyTo];
    }

    private function fetchWhatsappTemplates(string $url): array
    {
        try {
            $response = Http::withHeaders($this->apiHeaders())->get($url);
            
            if (!$response->successful()) {
                return [];
            }

            $data = $response->json();
            $templates = $data['data'] ?? [];

            // Handle pagination
            $nextUrl = $data['paging']['next'] ?? null;
            if ($nextUrl) {
                $nextTemplates = $this->fetchWhatsappTemplates($nextUrl);
                $templates = array_merge($templates, $nextTemplates);
            }

            return $templates;
        } catch (\Exception $e) {
            Log::error('WhatsApp Cloud template fetch failed', ['error' => $e->getMessage()]);
            return [];
        }
    }
}