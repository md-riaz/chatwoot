<?php

namespace App\Jobs\Whatsapp;

use App\Models\Channels\Whatsapp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncTemplatesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Whatsapp $whatsappChannel;

    public function __construct(Whatsapp $whatsappChannel)
    {
        $this->whatsappChannel = $whatsappChannel;
    }

    public function handle(): void
    {
        try {
            Log::info('Starting WhatsApp template sync', [
                'channel_id' => $this->whatsappChannel->id,
                'provider' => $this->whatsappChannel->provider,
            ]);

            $this->whatsappChannel->syncTemplates();

            Log::info('WhatsApp template sync completed', [
                'channel_id' => $this->whatsappChannel->id,
                'template_count' => count($this->whatsappChannel->message_templates ?? []),
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp template sync failed', [
                'channel_id' => $this->whatsappChannel->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'whatsapp',
            'template-sync',
            'channel:' . $this->whatsappChannel->id,
        ];
    }
}