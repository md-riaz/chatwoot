<?php

namespace App\Jobs\Channels;

use App\Data\Channels\InboundMessageData;
use App\Models\Channels\TwilioSms;
use App\Models\Inbox;
use App\Services\Channels\InboundMessageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSmsWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public array $payload)
    {
    }

    public function handle(): void
    {
        try {
            // Twilio webhook payload commonly includes: From, To, Body, MessageSid
            $from = $this->payload['From'] ?? $this->payload['from'] ?? null;
            $to = $this->payload['To'] ?? $this->payload['to'] ?? null;
            $body = $this->payload['Body'] ?? $this->payload['body'] ?? null;
            $sid = $this->payload['MessageSid'] ?? $this->payload['messageSid'] ?? null;

            if (! $from || ! $to) {
                return;
            }

            // Find inbox by phone number in channel
            $messagingServiceSid = $this->payload['MessagingServiceSid'] ?? null;
            $inbox = Inbox::whereHasMorph('channel', [TwilioSms::class], function ($q) use ($to, $messagingServiceSid) {
                $q->where('phone_number', (string) $to);
                if ($messagingServiceSid) {
                    $q->orWhere('messaging_service_sid', $messagingServiceSid);
                }
            })->first();

            if (! $inbox) {
                Log::warning('SMS webhook: no inbox found', ['to' => $to]);
                return;
            }

            if ($sid && \App\Models\Message::where('external_source_id', $sid)->exists()) {
                return;
            }

            $service = app(InboundMessageService::class);

            $messageData = new InboundMessageData(
                account_id: $inbox->account_id,
                inbox_id: $inbox->id,
                contact_identifier: 'sms:' . $from,
                contact_source: 'sms',
                contact_name: null,
                contact_email: null,
                contact_phone: $from,
                provider_contact_id: $from,
                content: $body,
                content_type: \App\Models\Message::CONTENT_TEXT,
                external_source_id: $sid,
                attachments: [],
                metadata: ['provider' => 'twilio', 'raw' => $this->payload]
            );

            $service->ingest($messageData);
        } catch (\Throwable $e) {
            Log::error('ProcessSmsWebhookJob failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
