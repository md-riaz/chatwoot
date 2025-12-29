<?php

namespace App\Services\Voice;

use App\Models\Conversation;

class CallSessionSyncService
{
    protected $conversation;
    protected $callSid;
    protected $fromNumber;
    protected $toNumber;
    protected $direction;

    public function __construct(Conversation $conversation, $callSid, $fromNumber, $toNumber, $direction)
    {
        $this->conversation = $conversation;
        $this->callSid = $callSid;
        $this->fromNumber = $fromNumber;
        $this->toNumber = $toNumber;
        $this->direction = $direction;
    }

    public function perform()
    {
        $attrs = $this->conversation->additional_attributes ?? [];
        $attrs['call_direction'] = $this->direction;
        $attrs['call_status'] = $attrs['call_status'] ?? 'ringing';
        $attrs['conference_sid'] = $attrs['conference_sid'] ?? 'conf_' . $this->conversation->id;
        $attrs['meta'] = $attrs['meta'] ?? [];
        $attrs['meta']['initiated_at'] = $attrs['meta']['initiated_at'] ?? now()->toIso8601String();
        $this->conversation->additional_attributes = $attrs;
        $this->conversation->last_activity_at = now();
        $this->conversation->save();
        // Optionally: log or create a call message here
        return $this->conversation;
    }
}
