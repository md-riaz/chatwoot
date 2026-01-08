<?php

namespace App\Listeners;

use App\Events\Sla\SlaBreached;
use App\Jobs\Webhooks\SendWebhooksJob;
use Illuminate\Contracts\Logging\Log as LogContract;
use function Spatie\Activitylog\activity;

class HandleSlaBreached
{
    public function __construct(private LogContract $log) {}

    public function handle(SlaBreached $event): void
    {
        $conversation = $event->conversation;
        $payload = [
            'conversation_id' => $conversation->id,
            'policy_id' => $event->policy->id,
            'breaches' => $event->breaches,
            'applied_sla_id' => $event->appliedSla?->id,
        ];

        SendWebhooksJob::dispatch($conversation->account_id, 'sla_breached', $payload);

        activity()
            ->performedOn($conversation)
            ->withProperties([
                'policy_id' => $event->policy->id,
                'breaches' => $event->breaches,
                'applied_sla_id' => $event->appliedSla?->id,
            ])
            ->event('sla_breached')
            ->log('SLA breach detected');

        $this->log->warning('SLA breach recorded', [
            'conversation_id' => $conversation->id,
            'policy_id' => $event->policy->id,
            'breaches' => $event->breaches,
        ]);
    }
}
