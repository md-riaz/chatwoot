<?php

namespace App\Jobs\Channels;

use App\Data\Channels\InboundMessageData;
use App\Models\Channels\Telegram;
use App\Models\Inbox;
use App\Services\Channels\InboundMessageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessTelegramWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $inboxId, public array $payload)
    {
        $this->onQueue('channels');
    }

    public function handle(): void
    {
        $inbox = Inbox::with('channel')->find($this->inboxId);
        if (! $inbox || ! $inbox->channel instanceof Telegram) {
            Log::warning('Telegram webhook: inbox missing or not telegram', ['inbox_id' => $this->inboxId]);
            return;
        }

        $message = $this->payload['message'] ?? $this->payload['edited_message'] ?? null;
        if (! $message) {
            return;
        }

        $chat = $message['chat'] ?? [];
        $from = $message['from'] ?? [];
        $externalId = $message['message_id'] ?? null;
        $text = $message['text'] ?? ($message['caption'] ?? null);

        if (! $chat || ! $externalId) {
            return;
        }

        $contactIdentifier = 'telegram:' . ($from['id'] ?? $chat['id']);
        $service = app(InboundMessageService::class);

        $service->ingest(new InboundMessageData(
            account_id: $inbox->account_id,
            inbox_id: $inbox->id,
            contact_identifier: $contactIdentifier,
            contact_source: 'telegram',
            contact_name: trim(($from['first_name'] ?? '') . ' ' . ($from['last_name'] ?? '')) ?: ($from['username'] ?? null),
            contact_email: null,
            contact_phone: null,
            provider_contact_id: (string) ($from['id'] ?? $chat['id']),
            content: $text,
            content_type: \App\Models\Message::CONTENT_TEXT,
            external_source_id: (string) $externalId,
            attachments: [],
            metadata: ['raw' => $message]
        ));
    }
}
