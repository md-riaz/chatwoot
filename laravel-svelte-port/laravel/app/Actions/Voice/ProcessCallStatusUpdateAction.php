<?php

namespace App\Actions\Voice;

use App\Models\Account;
use App\Models\Conversation;
use App\Services\Voice\CallStatus\ManagerService;
use Lorisleiva\Actions\Concerns\AsAction;

class ProcessCallStatusUpdateAction
{
    use AsAction;

    private const TWILIO_STATUS_MAP = [
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

    public function __construct(
        private ManagerService $callStatusManager
    ) {}

    /**
     * Process a call status update from Twilio webhook.
     */
    public function handle(Account $account, string $callSid, ?string $callStatus, array $payload = []): void
    {
        $normalizedStatus = $this->normalizeStatus($callStatus);
        
        if (!$normalizedStatus) {
            return;
        }

        $conversation = $account->conversations()->where('identifier', $callSid)->first();
        
        if (!$conversation) {
            return;
        }

        $this->callStatusManager->processStatusUpdate(
            $conversation,
            $callSid,
            $normalizedStatus,
            $this->extractDuration($payload),
            $this->extractTimestamp($payload)
        );
    }

    private function normalizeStatus(?string $status): ?string
    {
        if (empty($status)) {
            return null;
        }

        return self::TWILIO_STATUS_MAP[strtolower($status)] ?? null;
    }

    private function extractDuration(array $payload): ?int
    {
        $duration = $payload['CallDuration'] ?? $payload['call_duration'] ?? null;
        return $duration ? (int) $duration : null;
    }

    private function extractTimestamp(array $payload): ?int
    {
        $timestamp = $payload['Timestamp'] ?? $payload['timestamp'] ?? null;
        
        if (!$timestamp) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($timestamp)->timestamp;
        } catch (\Exception $e) {
            return null;
        }
    }
}