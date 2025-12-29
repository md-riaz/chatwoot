<?php

namespace App\Services\Voice;

use App\Models\Conversation;

class StatusUpdateService
{
    protected $conversation;
    protected $callSid;
    protected $callStatus;
    protected $payload;

    const TWILIO_STATUS_MAP = [
        'queued' => 'ringing',
        'initiated' => 'ringing',
        'ringing' => 'ringing',
        'in-progress' => 'in-progress',
        'inprogress' => 'in-progress',
        'answered' => 'in-progress',
        'completed' => 'completed',
        'busy' => 'no-answer',
        'no-answer' => 'no-answer',
        'failed' => 'failed',
        'canceled' => 'failed',
    ];

    public function __construct(Conversation $conversation, $callSid, $callStatus, $payload = [])
    {
        $this->conversation = $conversation;
        $this->callSid = $callSid;
        $this->callStatus = $callStatus;
        $this->payload = $payload;
    }

    public function perform()
    {
        $normalized = $this->normalizeStatus($this->callStatus);
        if (!$normalized) return;

        // Delegate to CallStatusManager to properly apply transitions and update messages
        try {
            $manager = new \App\Services\Voice\CallStatusManager($this->conversation);
            $manager->processStatusUpdate($normalized, $this->payloadDuration(), $this->payloadTimestamp());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Call status processing failed', ['error' => $e->getMessage()]);
        }
    }

    protected function normalizeStatus($status)
    {
        if (!$status) return null;
        $key = strtolower(trim($status));
        return self::TWILIO_STATUS_MAP[$key] ?? null;
    }

    protected function payloadDuration()
    {
        return $this->payload['CallDuration'] ?? $this->payload['call_duration'] ?? null;
    }

    protected function payloadTimestamp()
    {
        $ts = $this->payload['Timestamp'] ?? $this->payload['timestamp'] ?? null;
        if ($ts) return strtotime($ts);
        return null;
    }
}
