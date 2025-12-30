<?php

namespace App\Jobs\Conversations;

use App\Actions\Assignment\AutoAssignConversationAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunAutoAssignConversationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $conversationId)
    {
    }

    public function handle(AutoAssignConversationAction $action): void
    {
        try {
            $action->handle($this->conversationId);
        } catch (\Throwable $e) {
            Log::error('RunAutoAssignConversationJob failed', ['conversation_id' => $this->conversationId, 'error' => $e->getMessage()]);
        }
    }
}
