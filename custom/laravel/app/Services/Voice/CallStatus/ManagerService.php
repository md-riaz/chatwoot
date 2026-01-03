<?php

namespace App\Services\Voice\CallStatus;

use App\Models\Conversation;
use App\Models\Message;

class ManagerService
{
    private const ALLOWED_STATUSES = ['ringing', 'in-progress', 'completed', 'no-answer', 'failed'];
    private const TERMINAL_STATUSES = ['completed', 'no-answer', 'failed'];

    /**
     * Process a call status update.
     */
    public function processStatusUpdate(
        Conversation $conversation,
        string $callSid,
        string $status,
        ?int $duration = null,
        ?int $timestamp = null
    ): void {
        if (!in_array($status, self::ALLOWED_STATUSES)) {
            return;
        }

        $currentStatus = $conversation->additional_attributes['call_status'] ?? null;
        
        if ($currentStatus === $status) {
            return;
        }

        $this->applyStatus($conversation, $status, $duration, $timestamp);
        $this->updateMessage($conversation, $status);
    }

    private function applyStatus(Conversation $conversation, string $status, ?int $duration, ?int $timestamp): void
    {
        $attrs = $conversation->additional_attributes ?? [];
        $attrs['call_status'] = $status;

        if ($status === 'in-progress') {
            $attrs['call_started_at'] = $timestamp ?? now()->timestamp;
        } elseif (in_array($status, self::TERMINAL_STATUSES)) {
            $attrs['call_ended_at'] = $timestamp ?? now()->timestamp;
            $attrs['call_duration'] = $this->resolveDuration($attrs, $duration, $timestamp);
        }

        $conversation->update([
            'additional_attributes' => $attrs,
            'last_activity_at' => now(),
        ]);
    }

    private function resolveDuration(array $attrs, ?int $providedDuration, ?int $timestamp): ?int
    {
        if ($providedDuration) {
            return $providedDuration;
        }

        $startedAt = $attrs['call_started_at'] ?? null;
        
        if (!$startedAt || !$timestamp) {
            return null;
        }

        return max($timestamp - $startedAt, 0);
    }

    private function updateMessage(Conversation $conversation, string $status): void
    {
        $message = $conversation->messages()
            ->where('content_type', Message::CONTENT_VOICE_CALL)
            ->orderByDesc('created_at')
            ->first();

        if (!$message) {
            return;
        }

        $contentAttributes = $message->content_attributes ?? [];
        $contentAttributes['data'] = $contentAttributes['data'] ?? [];
        $contentAttributes['data']['status'] = $status;

        $message->update(['content_attributes' => $contentAttributes]);
    }
}