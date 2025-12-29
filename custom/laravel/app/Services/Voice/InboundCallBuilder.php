<?php

namespace App\Services\Voice;

use App\Models\Account;
use App\Models\Inbox;
use App\Models\Conversation;
use App\Models\Contact;
use App\Models\ContactInbox;

class InboundCallBuilder
{
    public function __construct(...$args)
    {
        throw new \LogicException('InboundCallBuilder is removed. Use App\\Actions\\Voice\\HandleInboundCallAction instead.');
    }
}
