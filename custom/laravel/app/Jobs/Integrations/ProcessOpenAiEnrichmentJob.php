<?php

namespace App\Jobs\Integrations;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessOpenAiEnrichmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $messageId;

    public function __construct(int $messageId)
    {
        $this->messageId = $messageId;
    }

    public function handle(): void
    {
        try {
            $svc = app(\App\Services\Integrations\OpenAIService::class);
            if (! method_exists($svc, 'enrichMessage')) {
                Log::warning('OpenAIService::enrichMessage not implemented');
                return;
            }

            $svc->enrichMessage($this->messageId);
        } catch (\Throwable $e) {
            Log::error('ProcessOpenAiEnrichmentJob failed', ['message_id' => $this->messageId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
