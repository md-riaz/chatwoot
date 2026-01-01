<?php

namespace App\Jobs\Channels;

use App\Data\Channels\InboundMessageData;
use App\Models\Channels\Whatsapp;
use App\Models\Inbox;
use App\Models\Message;
use App\Services\Channels\InboundMessageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ProcessWhatsAppWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public array $payload)
    {
        $this->onQueue('low');
    }

    public function handle(): void
    {
        try {
            $channel = $this->findChannelFromPayload();
            
            if ($this->isChannelInactive($channel)) {
                Log::warning('Inactive WhatsApp channel', [
                    'phone_number' => $channel?->phone_number ?? $this->payload['phone_number'] ?? 'unknown'
                ]);
                return;
            }

            $this->processWebhookForChannel($channel);
            
        } catch (\Exception $e) {
            Log::error('WhatsApp webhook processing failed', [
                'payload' => $this->payload,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function findChannelFromPayload(): ?Whatsapp
    {
        // Handle WhatsApp Business Account payload format
        if (($this->payload['object'] ?? '') === 'whatsapp_business_account') {
            return $this->getChannelFromBusinessPayload();
        }

        // Handle direct phone number format
        if (isset($this->payload['phone_number'])) {
            return Whatsapp::where('phone_number', $this->payload['phone_number'])->first();
        }

        return null;
    }

    private function getChannelFromBusinessPayload(): ?Whatsapp
    {
        $entry = $this->payload['entry'][0] ?? null;
        if (!$entry) {
            return null;
        }

        $change = $entry['changes'][0] ?? null;
        if (!$change) {
            return null;
        }

        $metadata = $change['value']['metadata'] ?? null;
        if (!$metadata) {
            return null;
        }

        $phoneNumber = '+' . $metadata['display_phone_number'];
        $phoneNumberId = $metadata['phone_number_id'];

        $channel = Whatsapp::where('phone_number', $phoneNumber)->first();

        // Validate phone number ID matches for security
        if ($channel && ($channel->provider_config['phone_number_id'] ?? null) === $phoneNumberId) {
            return $channel;
        }

        return null;
    }

    private function isChannelInactive(?Whatsapp $channel): bool
    {
        if (!$channel) {
            return true;
        }

        if ($channel->reauthorizationRequired()) {
            return true;
        }

        if (!$channel->account->active) {
            return true;
        }

        return false;
    }

    private function processWebhookForChannel(Whatsapp $channel): void
    {
        $entries = $this->payload['entry'] ?? [$this->payload];
        
        foreach ($entries as $entry) {
            $this->processEntry($entry, $channel);
        }
    }

    private function processEntry(array $entry, Whatsapp $channel): void
    {
        foreach ($entry['changes'] ?? [] as $change) {
            $value = $change['value'] ?? [];
            
            // Process status updates
            if (!empty($value['statuses'])) {
                $this->processStatusUpdates($value['statuses']);
            }
            
            // Process incoming messages
            if (!empty($value['messages'])) {
                $this->processMessages($value['messages'], $value['contacts'] ?? [], $channel);
            }
        }
    }

    private function processStatusUpdates(array $statuses): void
    {
        foreach ($statuses as $status) {
            $messageId = $status['id'] ?? null;
            if (!$messageId) {
                continue;
            }

            $message = Message::where('source_id', $messageId)->first();
            if (!$message) {
                continue;
            }

            try {
                $message->update([
                    'status' => $this->mapWhatsAppStatus($status['status'] ?? ''),
                    'external_error' => $this->extractStatusError($status)
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to update message status', [
                    'message_id' => $message->id,
                    'status' => $status,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    private function processMessages(array $messages, array $contacts, Whatsapp $channel): void
    {
        $contactProfile = $contacts[0]['profile']['name'] ?? null;
        
        foreach ($messages as $msg) {
            $from = $msg['from'] ?? null;
            $messageId = $msg['id'] ?? null;
            
            if (!$from || !$messageId) {
                continue;
            }

            // Check for duplicate messages using Redis
            $lockKey = "whatsapp_message_lock:{$messageId}";
            if (!Redis::set($lockKey, true, 'EX', 300, 'NX')) {
                Log::info('Duplicate WhatsApp message ignored', ['message_id' => $messageId]);
                continue;
            }

            try {
                // Check if message already exists
                if (Message::where('external_source_id', $messageId)->exists()) {
                    continue;
                }

                $content = $this->extractMessageContent($msg);
                $attachments = $this->extractAttachments($msg, $channel);

                $service = app(InboundMessageService::class);
                $service->ingest(new InboundMessageData(
                    account_id: $channel->account_id,
                    inbox_id: $channel->inbox->id,
                    contact_identifier: 'whatsapp:' . $from,
                    contact_source: 'whatsapp',
                    contact_name: $contactProfile,
                    contact_email: null,
                    contact_phone: $from,
                    provider_contact_id: $from,
                    content: $content,
                    content_type: $this->mapContentType($msg['type'] ?? 'text'),
                    external_source_id: $messageId,
                    attachments: $attachments,
                    metadata: ['raw' => $msg]
                ));

            } finally {
                Redis::del($lockKey);
            }
        }
    }

    private function extractMessageContent(array $message): ?string
    {
        $type = $message['type'] ?? 'text';
        
        return match ($type) {
            'text' => $message['text']['body'] ?? null,
            'image', 'video', 'document' => $message[$type]['caption'] ?? null,
            'location' => $message['location']['name'] ?? 'Location shared',
            'contacts' => 'Contact shared',
            'interactive' => $this->extractInteractiveContent($message['interactive'] ?? []),
            default => null,
        };
    }

    private function extractInteractiveContent(array $interactive): ?string
    {
        $type = $interactive['type'] ?? '';
        
        if ($type === 'button_reply') {
            return $interactive['button_reply']['title'] ?? null;
        }
        
        if ($type === 'list_reply') {
            return $interactive['list_reply']['title'] ?? null;
        }
        
        return null;
    }

    private function extractAttachments(array $message, Whatsapp $channel): array
    {
        $type = $message['type'] ?? 'text';
        
        if (!in_array($type, ['image', 'video', 'audio', 'document', 'sticker'])) {
            return [];
        }

        $mediaData = $message[$type] ?? [];
        $mediaId = $mediaData['id'] ?? null;
        
        if (!$mediaId) {
            return [];
        }

        return [[
            'url' => $channel->getMediaUrl($mediaId),
            'content_type' => $mediaData['mime_type'] ?? null,
            'filename' => $mediaData['filename'] ?? null,
            'name' => $mediaData['filename'] ?? "attachment.{$type}",
        ]];
    }

    private function mapContentType(string $whatsappType): string
    {
        return match ($whatsappType) {
            'image', 'video', 'audio', 'document', 'sticker' => Message::CONTENT_ATTACHMENT,
            'location' => Message::CONTENT_LOCATION,
            'contacts' => Message::CONTENT_CONTACT,
            'interactive' => Message::CONTENT_INPUT_SELECT,
            default => Message::CONTENT_TEXT,
        };
    }

    private function mapWhatsAppStatus(string $status): string
    {
        return match ($status) {
            'sent' => Message::STATUS_SENT,
            'delivered' => Message::STATUS_DELIVERED,
            'read' => Message::STATUS_READ,
            'failed' => Message::STATUS_FAILED,
            default => Message::STATUS_SENT,
        };
    }

    private function extractStatusError(array $status): ?string
    {
        if ($status['status'] !== 'failed' || empty($status['errors'])) {
            return null;
        }

        $error = $status['errors'][0] ?? [];
        return ($error['code'] ?? '') . ': ' . ($error['title'] ?? 'Unknown error');
    }
}
