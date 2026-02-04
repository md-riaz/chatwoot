<?php

namespace App\Listeners;

use App\Events\Portal\PortalUpdated;
use App\Jobs\Webhooks\SendWebhooksJob;
use Psr\Log\LoggerInterface;
use function Spatie\Activitylog\activity;

class HandlePortalUpdated
{
    public function __construct(private LoggerInterface $log) {}

    public function handle(PortalUpdated $event): void
    {
        $portal = $event->portal;
        $eventName = $this->resolveWebhookEvent($event->action);

        SendWebhooksJob::dispatch($portal->account_id, $eventName, [
            'portal_id' => $portal->id,
            'action' => $event->action,
        ]);

        activity()
            ->performedOn($portal)
            ->withProperties([
                'action' => $event->action,
                'account_id' => $portal->account_id,
            ])
            ->event($eventName)
            ->log('Portal lifecycle change');

        $this->log->info('Portal lifecycle event dispatched', [
            'portal_id' => $portal->id,
            'action' => $event->action,
        ]);
    }

    private function resolveWebhookEvent(string $action): string
    {
        return match ($action) {
            'created' => 'portal_created',
            'deleted' => 'portal_deleted',
            default => 'portal_updated',
        };
    }
}
