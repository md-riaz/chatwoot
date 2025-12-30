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
        // Map TikTok events to conversation/message flows and persist state.
        // Implement provider-specific parsing when TikTok event payloads are finalized.
        Log::info('Processing TikTok webhook payload', [
            'payload' => $this->payload,
        ]);
    }
}
