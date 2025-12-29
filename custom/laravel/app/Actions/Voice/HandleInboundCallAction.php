<?php

namespace App\Actions\Voice;

use App\Models\Inbox;
use App\Models\Contact;
use App\Models\ContactInbox;
use App\Models\Conversation;
use Lorisleiva\Actions\Concerns\AsAction;

class HandleInboundCallAction
{
    use AsAction;

    /**
     * Ensure an inbound call resolves to a conversation.
     */
    public function handle(Inbox $inbox, string $fromNumber, string $callSid): Conversation
    {
        $accountId = $inbox->account_id;

        $contact = Contact::firstOrCreate(
            ['phone_number' => $fromNumber, 'account_id' => $accountId],
            ['name' => $fromNumber]
        );

        $contactInbox = ContactInbox::firstOrCreate(
            [
                'contact_id' => $contact->id,
                'inbox_id' => $inbox->id,
            ],
            ['source_id' => $fromNumber]
        );

        $conversation = Conversation::firstOrCreate(
            [
                'account_id' => $accountId,
                'inbox_id' => $inbox->id,
                'contact_id' => $contact->id,
                'contact_inbox_id' => $contactInbox->id,
                'identifier' => $callSid,
            ],
            [
                'status' => Conversation::STATUS_OPEN,
                'last_activity_at' => now(),
            ]
        );

        return $conversation;
    }
}
