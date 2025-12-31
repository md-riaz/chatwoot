<?php

namespace App\Listeners;

use App\Events\Contact\ContactCreated;
use App\Jobs\Contacts\SyncContactJob;
use App\Jobs\Webhooks\SendWebhooksJob;
use Illuminate\Contracts\Logging\Log as LogContract;
use function Spatie\Activitylog\activity;

class HandleContactCreated
{
    public function __construct(private LogContract $log) {}

    public function handle(ContactCreated $event): void
    {
        $contact = $event->contact;

        // Sync to external systems (best-effort)
        SyncContactJob::dispatch($contact->id);

        // Emit webhooks for 'contact_created'
        SendWebhooksJob::dispatch($contact->account_id, 'contact_created', ['contact_id' => $contact->id]);

        activity()
            ->performedOn($contact)
            ->withProperties(['event' => 'contact_created'])
            ->event('contact_created')
            ->log('Contact created');

        $this->log->info('HandleContactCreated dispatched side-effects', ['contact_id' => $contact->id]);
    }
}
