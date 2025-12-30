<?php

namespace App\Jobs\Webhooks;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Webhook;

class SendWebhooksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $accountId, public string $eventName, public array $payload = [])
    {
    }

    public function handle(): void
    {
        $webhooks = Webhook::where('account_id', $this->accountId)
            ->get();

        foreach ($webhooks as $wh) {
            $subs = (array) data_get($wh, 'subscriptions', []);
            if (! in_array($this->eventName, $subs) && ! in_array('*', $subs)) {
                continue;
            }

            try {
                $resp = Http::timeout(5)->post($wh->url, [
                    'event' => $this->eventName,
                    'data' => $this->payload,
                ]);

                if (! $resp->successful()) {
                    Log::warning('SendWebhooksJob: non-2xx response', ['url' => $wh->url, 'status' => $resp->status()]);
                }
            } catch (\Throwable $e) {
                Log::warning('SendWebhooksJob failed', ['url' => $wh->url, 'error' => $e->getMessage()]);
            }
        }
    }
}
