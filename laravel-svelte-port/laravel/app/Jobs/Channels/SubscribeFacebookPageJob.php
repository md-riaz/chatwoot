<?php

namespace App\Jobs\Channels;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SubscribeFacebookPageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    // Maximum number of attempts before the job is considered failed
    public int $tries = 5;

    // Default backoff intervals (seconds) for retries: 1m, 2m, 5m, 15m, 1h
    public function backoff(): array
    {
        return [60, 120, 300, 900, 3600];
    }

    public function __construct(public int $inboxId)
    {
    }

    public function handle(): void
    {
        try {
            $inbox = \App\Models\Inbox::find($this->inboxId);
            if (! $inbox) {
                Log::warning('SubscribeFacebookPageJob: inbox not found', ['inbox_id' => $this->inboxId]);
                return;
            }

            $service = new \App\Services\Channels\Facebook\FacebookService($inbox);
            $ok = $service->subscribeToPage();
            if (! $ok) {
                Log::warning('SubscribeFacebookPageJob: subscribeToPage returned false', ['inbox_id' => $this->inboxId]);
                // Throw to allow Laravel to retry per job settings
                throw new \Exception('subscribeToPage failed');
            }

            Log::info('SubscribeFacebookPageJob: subscribed successfully', ['inbox_id' => $this->inboxId]);
        } catch (\Throwable $e) {
            Log::error('SubscribeFacebookPageJob error', ['error' => $e->getMessage(), 'inbox_id' => $this->inboxId, 'attempts' => $this->attempts()]);
            // rethrow to allow retries
            throw $e;
        }
    }

    /**
     * Called when the job has failed after exhausting all retries.
     */
    public function failed(\Throwable $exception): void
    {
        try {
            Log::error('SubscribeFacebookPageJob permanently failed', ['inbox_id' => $this->inboxId, 'error' => $exception->getMessage()]);
        } catch (\Throwable $_) {
            // swallow
        }
    }
}
