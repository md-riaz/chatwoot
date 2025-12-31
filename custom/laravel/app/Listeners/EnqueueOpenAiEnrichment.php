<?php

namespace App\Listeners;

use App\Events\Message\MessageCreated;
use App\Jobs\Integrations\ProcessOpenAiEnrichmentJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class EnqueueOpenAiEnrichment implements ShouldQueue
{
    public function handle(MessageCreated $event): void
    {
        try {
            $message = $event->message;
            // Only enqueue for incoming text messages
            if ($message->message_type !== \App\Models\Message::TYPE_INCOMING) {
                return;
            }

            if (empty($message->content)) {
                return;
            }

            ProcessOpenAiEnrichmentJob::dispatch($message->id);
        } catch (\Throwable $e) {
            Log::warning('EnqueueOpenAiEnrichment failed', ['error' => $e->getMessage()]);
        }
    }
}
