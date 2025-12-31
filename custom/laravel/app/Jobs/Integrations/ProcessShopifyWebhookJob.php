<?php

namespace App\Jobs\Integrations;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Integration;

class ProcessShopifyWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $topic,
        public array $payload,
        public ?int $integrationId = null
    ) {}

    public function handle(): void
    {
        try {
            // Minimal processing placeholder: log and (optionally) emit events
            Log::info('Processing Shopify webhook', ['topic' => $this->topic, 'integration_id' => $this->integrationId]);

            // Example: when an order is created, you might map to a conversation or contact
            if ($this->topic === 'orders/create') {
                // Implement domain-specific mapping here
            }

            // Future: resolve integration and persist webhook payloads if needed
            if ($this->integrationId) {
                $integration = Integration::find($this->integrationId);
                if ($integration) {
                    // optional: persist webhook payload to integration hooks table
                }
            }
        } catch (\Throwable $e) {
            Log::error('Failed processing Shopify webhook', ['error' => $e->getMessage(), 'topic' => $this->topic]);
            throw $e;
        }
    }
}
