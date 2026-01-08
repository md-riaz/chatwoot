<?php

namespace App\Jobs\Channels;

use App\Data\Channels\InboundMessageData;
use App\Models\Channels\Instagram;
use App\Models\Inbox;
use App\Services\Channels\InboundMessageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessInstagramWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public array $payload)
    {
        $this->onQueue('channels');
    }

    public function handle(): void
    {
        $entries = $this->payload['entry'] ?? [];

        foreach ($entries as $entry) {
            $instagramId = $entry['id'] ?? null;
            if (! $instagramId) {
                continue;
            }

            $channel = Instagram::where('instagram_id', $instagramId)->first();
            $inbox = $channel?->inbox()->first();

            if (! $inbox) {
                Log::warning('Instagram webhook: inbox not found', ['instagram_id' => $instagramId]);
                continue;
            }

            foreach ($entry['messaging'] ?? [] as $messageEvent) {
                $message = $messageEvent['message'] ?? null;
                $senderId = $messageEvent['sender']['id'] ?? null;
                $mid = $message['mid'] ?? null;
                $text = $message['text'] ?? ($message['attachments'][0]['payload']['url'] ?? null);

                if (! $message || ! $senderId) {
                    continue;
                }

                if ($mid && \App\Models\Message::where('external_source_id', $mid)->exists()) {
                    continue;
                }

                app(InboundMessageService::class)->ingest(new InboundMessageData(
                    account_id: $inbox->account_id,
                    inbox_id: $inbox->id,
                    contact_identifier: 'instagram:' . $senderId,
                    contact_source: 'instagram',
                    contact_name: null,
                    contact_email: null,
                    contact_phone: null,
                    provider_contact_id: $senderId,
                    content: $text,
                    content_type: \App\Models\Message::CONTENT_TEXT,
                    external_source_id: $mid,
                    attachments: [],
                    metadata: ['raw' => $messageEvent]
                ));
            }
        }
    }
}
