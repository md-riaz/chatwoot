<?php

namespace App\Jobs\Channels;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
                    foreach ($value['messages'] ?? [] as $msg) {
                        $from = $msg['from'] ?? null; // sender
                        $to = $value['metadata']['phone_number_id'] ?? null; // page/phone id
                        $mid = $msg['id'] ?? null;
                        $text = $msg['text']['body'] ?? ($msg['caption'] ?? null);

                        if (! $from || ! $to) {
                            continue;
                        }

                        // Find inbox by channel phone_number or metadata id
                        $inbox = \App\Models\Inbox::where('channel_type', 'Channel::Whatsapp')
                            ->where(function ($q) use ($to) {
                                $q->whereJsonContains('channel', ['phone_number' => (string) $to])
                                  ->orWhereJsonContains('channel', ['phone_number_id' => (string) $to]);
                            })->first();

                        if (! $inbox) {
                            Log::warning('WhatsApp webhook: no inbox found', ['to' => $to]);
                            continue;
                        }

                        // Find or create contact
                        $contact = \App\Models\Contact::firstOrCreate(
                            ['account_id' => $inbox->account_id, 'identifier' => 'whatsapp:' . $from],
                            ['name' => null, 'source' => 'whatsapp']
                        );

                        // Idempotency: skip if message external id exists
                        if ($mid && \App\Models\Message::where('external_id', $mid)->exists()) {
                            continue;
                        }

                        $conversation = \App\Models\Conversation::where('account_id', $inbox->account_id)
                            ->where('inbox_id', $inbox->id)
                            ->where('contact_id', $contact->id)
                            ->where('status', \App\Models\Conversation::STATUS_OPEN)
                            ->first();

                        if (! $conversation) {
                            $conversation = \App\Models\Conversation::create([
                                'account_id' => $inbox->account_id,
                                'inbox_id' => $inbox->id,
                                'contact_id' => $contact->id,
                                'status' => \App\Models\Conversation::STATUS_OPEN,
                            ]);
                            event(new \App\Events\Conversation\ConversationCreated($conversation));
                        }

                        $message = \App\Models\Message::create([
                            'conversation_id' => $conversation->id,
                            'content' => $text,
                            'message_type' => 'incoming',
                            'external_id' => $mid,
                            'sender_id' => null,
                        ]);

                        event(new \App\Events\Message\MessageCreated($message));
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('ProcessWhatsAppWebhookJob failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
