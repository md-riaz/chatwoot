<?php

namespace App\Jobs\Channels;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Channels\Facebook\FacebookService;
use Illuminate\Support\Facades\Log;

class ProcessFacebookWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var array */
    public array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function handle(FacebookService $facebookService): void
    {
        try {
            $facebookService->processWebhook($this->payload);
        } catch (\Throwable $e) {
            Log::error('ProcessFacebookWebhookJob failed: ' . $e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }
}
