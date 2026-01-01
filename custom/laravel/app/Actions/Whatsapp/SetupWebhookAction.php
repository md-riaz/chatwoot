<?php

namespace App\Actions\Whatsapp;

use App\Models\Channels\Whatsapp;
use App\Services\Channels\Whatsapp\WebhookSetupService;
use Lorisleiva\Actions\Concerns\AsAction;

class SetupWebhookAction
{
    use AsAction;

    public function handle(Whatsapp $whatsappChannel): array
    {
        try {
            $whatsappChannel->setupWebhooks();

            return [
                'success' => true,
                'message' => 'Webhook setup completed successfully',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}