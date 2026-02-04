<?php

namespace App\Listeners;

use App\Events\Contact\ContactUpdated;
use App\Jobs\Contacts\SyncContactJob;
use App\Jobs\Webhooks\SendWebhooksJob;
use Psr\Log\LoggerInterface;
use function Spatie\Activitylog\activity;

class HandleContactUpdated
{
    public function __construct(private LoggerInterface $log) {}

    public function handle(ContactUpdated $event): void
    {
        $contact = $event->contact;

        // Sync changes to external systems
        SyncContactJob::dispatch($contact->id);

        // Emit webhooks for 'contact_updated'
        SendWebhooksJob::dispatch($contact->account_id, 'contact_updated', ['contact_id' => $contact->id]);

        \Spatie\Activitylog\Models\Activity::create([
            'log_name' => 'default',
            'description' => 'Contact updated',
            'subject_type' => get_class($contact),
            'subject_id' => $contact->id,
            'event' => 'contact_updated',
            'properties' => ['event' => 'contact_updated'],
        ]);

        $this->log->info('HandleContactUpdated dispatched side-effects', ['contact_id' => $contact->id]);
    }
}
