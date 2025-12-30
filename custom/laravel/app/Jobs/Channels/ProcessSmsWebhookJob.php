<?php

namespace App\Jobs\Channels;

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
            $sid = $this->payload['MessageSid'] ?? $this->payload['MessageSid'] ?? null;

            if (! $from || ! $to) {
                return;
            }

            // Find inbox by phone number in channel
            $inbox = \App\Models\Inbox::where('channel_type', 'Channel::Sms')
                ->whereJsonContains('channel', ['phone_number' => (string) $to])
                ->first();

            if (! $inbox) {
                Log::warning('SMS webhook: no inbox found', ['to' => $to]);
                return;
            }

            // Find or create contact
            $contact = \App\Models\Contact::firstOrCreate(
                ['account_id' => $inbox->account_id, 'identifier' => 'sms:' . $from],
                ['name' => null, 'source' => 'sms']
            );

            // Idempotency: skip if message external id exists
            if ($sid && \App\Models\Message::where('external_id', $sid)->exists()) {
                return;
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
                'content' => $body,
                'message_type' => 'incoming',
                'external_id' => $sid,
                'sender_id' => null,
            ]);

            event(new \App\Events\Message\MessageCreated($message));
        } catch (\Throwable $e) {
            Log::error('ProcessSmsWebhookJob failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
