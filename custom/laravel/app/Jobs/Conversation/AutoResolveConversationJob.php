<?php

namespace App\Jobs\Conversation;

use App\Events\Conversation\ConversationStatusChanged;
use App\Models\Conversation;
use App\Repositories\Conversation\ConversationRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class AutoResolveConversationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public int $timeout = 120;

    public string $queue = 'conversations';

    public function __construct(
        public int $conversationId
    ) {}

    public function handle(ConversationRepository $repository): void
    {
        $conversation = $repository->find($this->conversationId);

        if (! $conversation || $conversation->status !== Conversation::STATUS_OPEN) {
            return;
        }

        $account = $conversation->account;
        $autoResolveMinutes = $account?->autoResolveAfterMinutes();

        if (! $autoResolveMinutes) {
            return;
        }

        if ($account->autoResolveIgnoreWaiting() && $conversation->waiting_since) {
            return;
        }

        $lastActivity = $conversation->last_activity_at ?? $conversation->updated_at ?? $conversation->created_at ?? now();
        $inactiveMinutes = now()->diffInMinutes($lastActivity);

        if ($inactiveMinutes < $autoResolveMinutes) {
            return;
        }

        $previousStatus = $conversation->status;

        $repository->update($conversation->id, [
            'status' => Conversation::STATUS_RESOLVED,
        ]);

        event(new ConversationStatusChanged(
            $conversation->fresh(),
            $previousStatus,
            Conversation::STATUS_RESOLVED
        ));

        Log::info('Auto-resolved conversation', [
            'conversation_id' => $this->conversationId,
            'inactive_minutes' => $inactiveMinutes,
            'threshold_minutes' => $autoResolveMinutes,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Auto-resolve conversation failed', [
            'conversation_id' => $this->conversationId,
            'error' => $exception->getMessage(),
        ]);
    }
}
