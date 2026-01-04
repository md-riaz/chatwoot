<?php

namespace App\Jobs\Channels;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessTiktokWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
        * @var array<string, mixed>
        */
    public array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
        $this->onQueue('channels');
    }

    public function handle(): void
    {
        $eventId = $this->payload['event_id'] ?? $this->payload['event'] ?? null;
        $sender = $this->payload['from_user_id'] ?? null;
        $text = $this->payload['text'] ?? null;

        if (! $eventId || ! $sender) {
            Log::warning('TikTok webhook missing ids', ['payload' => $this->payload]);
            return;
        }

        if (\App\Models\Message::where('external_source_id', $eventId)->exists()) {
            return;
        }

        $inbox = \App\Models\Inbox::where('channel_type', \App\Models\Channels\TikTok::class)->first();
        if (! $inbox) {
            Log::warning('TikTok webhook: no inbox configured');
            return;
        }

        app(\App\Services\Channels\InboundMessageService::class)->ingest(new \App\Data\Channels\InboundMessageData(
            account_id: $inbox->account_id,
            inbox_id: $inbox->id,
            contact_identifier: 'tiktok:' . $sender,
            contact_source: 'tiktok',
            contact_name: null,
            contact_email: null,
            contact_phone: null,
            provider_contact_id: $sender,
            content: $text ?? '[event]',
            content_type: \App\Models\Message::CONTENT_TEXT,
            external_source_id: (string) $eventId,
            attachments: [],
            metadata: ['raw' => $this->payload]
        ));
    }
}
