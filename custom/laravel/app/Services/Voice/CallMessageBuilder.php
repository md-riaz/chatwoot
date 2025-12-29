<?php

namespace App\Services\Voice;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class CallMessageBuilder
{
    public function __construct(...$args)
    {
        throw new \LogicException('CallMessageBuilder is removed. Use App\\Actions\\Voice\\CreateCallMessageAction instead.');
    }
}
