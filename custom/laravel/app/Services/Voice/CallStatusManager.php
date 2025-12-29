<?php

namespace App\Services\Voice;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Log;

class CallStatusManager
{
    public const ALLOWED_STATUSES = ['ringing', 'in-progress', 'completed', 'no-answer', 'failed'];
    public const TERMINAL_STATUSES = ['completed', 'no-answer', 'failed'];

    protected Conversation $conversation;

    public function __construct(Conversation $conversation)
    {
        $this->conversation = $conversation;
    }

    public function processStatusUpdate(string $status, ?int $duration = null, ?int $timestamp = null): void
    {
        if (!in_array($status, self::ALLOWED_STATUSES, true)) return;

        $current = $this->conversation->additional_attributes['call_status'] ?? null;
        if ($current === $status) return;

        $this->applyStatus($status, $duration, $timestamp);
        $this->updateMessage($status);
    }

    protected function applyStatus(string $status, ?int $duration, ?int $timestamp): void
    {
        $attrs = $this->conversation->additional_attributes ?? [];
        $attrs['call_status'] = $status;

        if ($status === 'in-progress') {
            $attrs['call_started_at'] = $attrs['call_started_at'] ?? ($timestamp ?? time());
        } elseif (in_array($status, self::TERMINAL_STATUSES, true)) {
            $attrs['call_ended_at'] = $timestamp ?? time();
            $attrs['call_duration'] = $this->resolvedDuration($attrs, $duration, $timestamp);
        }

        $this->conversation->additional_attributes = $attrs;
        $this->conversation->last_activity_at = now();
        $this->conversation->save();
    }

    protected function resolvedDuration(array $attrs, ?int $providedDuration, ?int $timestamp): ?int
    {
        if ($providedDuration) return $providedDuration;
        $startedAt = $attrs['call_started_at'] ?? null;
        if ($startedAt && $timestamp) {
            return max(0, $timestamp - (int) $startedAt);
        }
        return null;
    }

    protected function updateMessage(string $status): void
    {
        try {
            $message = Message::where('conversation_id', $this->conversation->id)
                ->where('content_type', Message::CONTENT_VOICE_CALL)
                ->orderBy('created_at', 'desc')
                ->first();

            if (! $message) return;

            $data = $message->content_attributes ?? [];
            if (! isset($data['data']) || ! is_array($data['data'])) {
                $data['data'] = [];
            }
            $data['data']['status'] = $status;
            $message->content_attributes = $data;
            $message->save();
        } catch (\Exception $e) {
            Log::warning('Failed to update voice call message status', ['error' => $e->getMessage()]);
        }
    }
}
