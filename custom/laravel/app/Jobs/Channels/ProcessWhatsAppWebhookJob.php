<?php

namespace App\Jobs\Channels;

use App\Data\Channels\InboundMessageData;
use App\Models\Channels\Whatsapp;
use App\Models\Inbox;
use App\Services\Channels\InboundMessageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWhatsAppWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public array $payload)
    {
    }

    public function handle(): void
    {
        try {
            // Basic parse of WhatsApp webhook (supports common Cloud API format)
            $entries = $this->payload['entry'] ?? [$this->payload];
            foreach ($entries as $entry) {
                foreach ($entry['changes'] ?? [] as $change) {
                    $value = $change['value'] ?? [];
                    $contacts = $value['contacts'] ?? [];
                    $contactProfile = $contacts[0]['profile']['name'] ?? null;
                    foreach ($value['messages'] ?? [] as $msg) {
                        $from = $msg['from'] ?? null; // sender
                        $to = $value['metadata']['phone_number_id'] ?? null; // page/phone id
                        $mid = $msg['id'] ?? null;
                        $text = $msg['text']['body'] ?? ($msg['caption'] ?? null);

                        if (! $from || ! $to) {
                            continue;
                        }

                        // Find inbox by channel phone_number or metadata id
                        $inbox = Inbox::whereHasMorph('channel', [Whatsapp::class], function ($q) use ($to) {
                            $q->where('phone_number', (string) $to)
                                ->orWhereRaw("provider_config->>'phone_number_id' = ?", [(string) $to]);
                        })->first();

                        if (! $inbox) {
                            Log::warning('WhatsApp webhook: no inbox found', ['to' => $to]);
                            continue;
                        }

                        if ($mid && \App\Models\Message::where('external_source_id', $mid)->exists()) {
                            continue;
                        }

                        $service = app(InboundMessageService::class);
                        $service->ingest(new InboundMessageData(
                            account_id: $inbox->account_id,
                            inbox_id: $inbox->id,
                            contact_identifier: 'whatsapp:' . $from,
                            contact_source: 'whatsapp',
                            contact_name: $contactProfile,
                            contact_email: null,
                            contact_phone: $from,
                            provider_contact_id: $from,
                            content: $text,
                            content_type: \App\Models\Message::CONTENT_TEXT,
                            external_source_id: $mid,
                            attachments: [],
                            metadata: ['raw' => $msg]
                        ));
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('ProcessWhatsAppWebhookJob failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
