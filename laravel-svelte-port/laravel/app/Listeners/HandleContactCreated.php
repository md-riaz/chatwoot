<?php

namespace App\Listeners;

use App\Events\Contact\ContactCreated;
use App\Jobs\Contacts\SyncContactJob;
use App\Jobs\Webhooks\SendWebhooksJob;
use Psr\Log\LoggerInterface;
use function Spatie\Activitylog\activity;

class HandleContactCreated
{
    public function __construct(private LoggerInterface $log) {}

    public function handle(ContactCreated $event): void
    {
        $contact = $event->contact;

        // Sync to external systems (best-effort)
        SyncContactJob::dispatch($contact->id);

        // Emit webhooks for 'contact_created'
        SendWebhooksJob::dispatch($contact->account_id, 'contact_created', ['contact_id' => $contact->id]);

        \Spatie\Activitylog\Models\Activity::create([
            'log_name' => 'default',
            'description' => 'Contact created',
            'subject_type' => get_class($contact),
            'subject_id' => $contact->id,
            'event' => 'contact_created',
            'properties' => ['event' => 'contact_created'],
        ]);

        $this->log->info('HandleContactCreated dispatched side-effects', ['contact_id' => $contact->id]);
    }
}
