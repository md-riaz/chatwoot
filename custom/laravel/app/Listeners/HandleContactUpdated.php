<?php

namespace App\Listeners;

use App\Events\Contact\ContactUpdated;
use App\Jobs\Contacts\SyncContactJob;
use App\Jobs\Webhooks\SendWebhooksJob;
use Illuminate\Contracts\Logging\Log as LogContract;

class HandleContactUpdated
{
    public function __construct(private LogContract $log) {}

    public function handle(ContactUpdated $event): void
    {
        $contact = $event->contact;

        // Sync changes to external systems
        SyncContactJob::dispatch($contact->id);

        // Emit webhooks for 'contact_updated'
        SendWebhooksJob::dispatch($contact->account_id, 'contact_updated', ['contact_id' => $contact->id]);

        $this->log->info('HandleContactUpdated dispatched side-effects', ['contact_id' => $contact->id]);
    }
}
