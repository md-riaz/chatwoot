<?php

namespace App\Jobs\Channels;

use App\Models\Channels\Whatsapp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncWhatsAppTemplatesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private Whatsapp $channel
    ) {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        try {
            Log::info('Starting WhatsApp template sync', [
                'channel_id' => $this->channel->id,
                'phone_number' => $this->channel->phone_number
            ]);

            $this->channel->syncTemplates();

            Log::info('WhatsApp template sync completed', [
                'channel_id' => $this->channel->id,
                'template_count' => count($this->channel->message_templates ?? [])
            ]);

        } catch (\Exception $e) {
            Log::error('WhatsApp template sync failed', [
                'channel_id' => $this->channel->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Mark as authorization error if it's a credentials issue
            if ($this->isAuthorizationError($e)) {
                $this->channel->authorizationError();
            }

            throw $e;
        }
    }

    private function isAuthorizationError(\Exception $e): bool
    {
        $message = strtolower($e->getMessage());
        
        return str_contains($message, 'unauthorized') ||
               str_contains($message, 'invalid token') ||
               str_contains($message, 'access denied') ||
               str_contains($message, 'authentication');
    }
}