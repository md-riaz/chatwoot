<?php

namespace App\Jobs\Message;

use App\Actions\Assignment\AutoAssignConversationAction;
use App\Events\Message\MessageCreated;
use App\Models\Conversation;
use App\Models\Message;
use App\Repositories\Conversation\ConversationRepository;
use App\Repositories\Message\MessageRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessIncomingMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public int $messageId
    ) {}

    public function handle(
        MessageRepository $messageRepository,
        ConversationRepository $conversationRepository,
        AutoAssignConversationAction $autoAssignAction
    ): void {
        $message = $messageRepository->find($this->messageId);

        if (!$message) {
            Log::warning('Message not found for processing', ['message_id' => $this->messageId]);
            return;
        }

        // Update conversation last activity
        $conversationRepository->update($message->conversation_id, [
            'last_activity_at' => now(),
        ]);

        // Reopen conversation if it was resolved
        $conversation = $conversationRepository->find($message->conversation_id);
        if ($conversation->status === Conversation::STATUS_RESOLVED) {
            $conversationRepository->update($conversation->id, [
                'status' => Conversation::STATUS_OPEN,
            ]);
        }

        // Trigger auto-assignment if needed
        if (!$conversation->assignee_id) {
            $autoAssignAction->handle($conversation->id);
        }

        // Broadcast message created event
        event(new MessageCreated($message));

        Log::info('Processed incoming message', [
            'message_id' => $this->messageId,
            'conversation_id' => $message->conversation_id,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Process incoming message failed', [
            'message_id' => $this->messageId,
            'error' => $exception->getMessage(),
        ]);
    }
}
