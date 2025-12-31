<?php

namespace App\Jobs\Channels;

use App\Data\Channels\InboundMessageData;
use App\Models\Inbox;
use App\Models\Channels\TwitterProfile;
use App\Services\Channels\InboundMessageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessTwitterWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public array $payload)
    {
        $this->onQueue('channels');
    }

    public function handle(): void
    {
        $events = $this->payload['direct_message_events'] ?? [];
        $users = $this->payload['users'] ?? [];

        foreach ($events as $event) {
            $eventId = $event['id'] ?? null;
            $messageCreate = $event['message_create'] ?? null;
            if (! $eventId || ! $messageCreate) {
                continue;
            }

            $senderId = $messageCreate['sender_id'] ?? null;
            $recipientId = $messageCreate['target']['recipient_id'] ?? null;
            $text = $messageCreate['message_data']['text'] ?? null;
            $attachments = [];

            if (! $recipientId) {
                Log::warning('Twitter webhook: missing recipient id', ['event_id' => $eventId]);
                continue;
            }

            foreach ($messageCreate['message_data']['attachment']['media'] ?? [] as $media) {
                $attachments[] = [
                    'url' => $media['media_url_https'] ?? $media['media_url'] ?? null,
                    'content_type' => $media['type'] ?? null,
                    'filename' => $media['id_str'] ?? null,
                    'meta' => $media,
                ];
            }

            // Determine inbox by matching recipient user id to channel config
            $inbox = Inbox::whereHasMorph('channel', [TwitterProfile::class], function ($q) use ($recipientId) {
                $q->where('profile_id', $recipientId)
                    ->orWhereRaw("provider_config->>'user_id' = ?", [$recipientId]);
            })->first();

            if (! $inbox && $recipientId) {
                $profile = TwitterProfile::where('profile_id', $recipientId)->first();
                if ($profile) {
                    $inbox = Inbox::where('channel_id', $profile->id)->first();
                }
            }

            if (! $inbox) {
                Log::warning('Twitter webhook: inbox not found', ['recipient_id' => $recipientId]);
                continue;
            }

            if (\App\Models\Message::where('external_source_id', $eventId)->exists()) {
                continue;
            }

            $senderProfile = $users[$senderId] ?? [];

            app(InboundMessageService::class)->ingest(new InboundMessageData(
                account_id: $inbox->account_id,
                inbox_id: $inbox->id,
                contact_identifier: 'twitter:' . $senderId,
                contact_source: 'twitter',
                contact_name: $senderProfile['name'] ?? null,
                contact_email: null,
                contact_phone: null,
                provider_contact_id: $senderId,
                content: $text,
                content_type: \App\Models\Message::CONTENT_TEXT,
                external_source_id: $eventId,
                attachments: $attachments,
                metadata: ['raw' => $event]
            ));
        }
    }
}
