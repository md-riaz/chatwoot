<?php

namespace App\Jobs\Assignment;

use App\Actions\Assignment\AutoAssignConversationAction;
use App\Models\Conversation;
use App\Repositories\Conversation\ConversationRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class AutoAssignConversationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public ?int $inboxId = null
    ) {}

    public function handle(
        ConversationRepository $conversationRepository,
        AutoAssignConversationAction $autoAssignAction
    ): void {
        $query = Conversation::where('status', Conversation::STATUS_OPEN)
            ->whereNull('assignee_id');

        if ($this->inboxId) {
            $query->where('inbox_id', $this->inboxId);
        }

        $unassignedConversations = $query->get();

        $assignedCount = 0;
        foreach ($unassignedConversations as $conversation) {
            $agent = $autoAssignAction->handle($conversation->id);
            if ($agent) {
                $assignedCount++;
            }
        }

        Log::info('Auto-assign conversations completed', [
            'inbox_id' => $this->inboxId,
            'processed' => $unassignedConversations->count(),
            'assigned' => $assignedCount,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Auto-assign conversations failed', [
            'inbox_id' => $this->inboxId,
            'error' => $exception->getMessage(),
        ]);
    }
}
