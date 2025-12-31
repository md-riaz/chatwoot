<?php

namespace App\Jobs\Channels;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Data\Channels\InboundMessageData;
use App\Models\Inbox;
use App\Services\Channels\InboundMessageService;

class ProcessInboundEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $email;

    public function __construct(array $email)
    {
        // Expected keys: message_id, from, to, subject, body, attachments (optional)
        $this->email = $email;
    }

    public function handle(): void
    {
        try {
            $raw = $this->email;
            $messageId = $raw['message_id'] ?? null;
            $to = $raw['to'] ?? null;

            if (! $to) {
                Log::warning('ProcessInboundEmailJob: missing recipient', ['email' => $this->email]);
                return;
            }

            // Resolve inbox by recipient address (simple heuristic)
            $inbox = Inbox::whereHasMorph('channel', [\App\Models\Channels\Email::class], function ($q) use ($to) {
                $q->where('email', strtolower($to))
                    ->orWhere('forward_to_email', strtolower($to));
            })->first();

            if (! $inbox) {
                Log::warning('ProcessInboundEmailJob: no inbox for recipient', ['to' => $to]);
                return;
            }

            // Idempotency: skip if message with same external_source_id exists
            if ($messageId && \App\Models\Message::where('external_source_id', $messageId)->exists()) {
                Log::info('ProcessInboundEmailJob: duplicate message, skipping', ['message_id' => $messageId]);
                return;
            }

            $contactIdentifier = $this->email['from'] ? 'email:' . strtolower($this->email['from']) : 'email:unknown';

            $service = app(InboundMessageService::class);
            $messageData = new InboundMessageData(
                account_id: $inbox->account_id,
                inbox_id: $inbox->id,
                contact_identifier: $contactIdentifier,
                contact_source: 'email',
                contact_name: $this->email['from_name'] ?? null,
                contact_email: $this->email['from'] ?? null,
                contact_phone: null,
                provider_contact_id: $this->email['from'] ?? null,
                content: $this->email['body'] ?? null,
                content_type: \App\Models\Message::CONTENT_TEXT,
                external_source_id: $messageId,
                attachments: is_array($this->email['attachments'] ?? null) ? $this->email['attachments'] : [],
                metadata: [
                    'subject' => $this->email['subject'] ?? null,
                    'headers' => $raw['headers'] ?? ($raw['message'] ?? null),
                ]
            );

            $msg = $service->ingest($messageData);

        } catch (\Throwable $e) {
            Log::error('ProcessInboundEmailJob failed', ['error' => $e->getMessage(), 'email' => $this->email]);
            throw $e;
        }
    }
}
