<?php

namespace App\Services\Voice;

use App\Models\Account;
use App\Models\Inbox;
use App\Models\Conversation;
use App\Models\Contact;
use App\Models\ContactInbox;

class InboundCallBuilder
{
    protected $account;
    protected $inbox;
    protected $fromNumber;
    protected $callSid;

    public function __construct(Account $account, Inbox $inbox, $fromNumber, $callSid)
    {
        $this->account = $account;
        $this->inbox = $inbox;
        $this->fromNumber = $fromNumber;
        $this->callSid = $callSid;
    }

    public function perform()
    {
        $contact = Contact::firstOrCreate(
            ['phone_number' => $this->fromNumber],
            ['name' => $this->fromNumber]
        );
        $contactInbox = ContactInbox::firstOrCreate(
            [
                'contact_id' => $contact->id,
                'inbox_id' => $this->inbox->id
            ],
            ['source_id' => $this->fromNumber]
        );
        $conversation = Conversation::firstOrCreate(
            [
                'account_id' => $this->account->id,
                'inbox_id' => $this->inbox->id,
                'contact_id' => $contact->id,
                'contact_inbox_id' => $contactInbox->id,
                'identifier' => $this->callSid
            ],
            [
                'status' => Conversation::STATUS_OPEN
            ]
        );
        // Optionally: update conversation, build voice message, etc.
        return $conversation;
    }
}
