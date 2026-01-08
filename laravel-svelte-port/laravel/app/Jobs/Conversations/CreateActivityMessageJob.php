<?php

namespace App\Jobs\Conversations;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateActivityMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Conversation $conversation, protected array $params = [])
    {
    }

    public function handle(): void
    {
        $content = $this->params['content'] ?? null;

        if (! $content) {
            Log::warning('CreateActivityMessageJob: no content provided');
            return;
        }

        $message = Message::create([
            'account_id' => $this->conversation->account_id,
            'conversation_id' => $this->conversation->id,
            'inbox_id' => $this->conversation->inbox_id,
            'message_type' => Message::TYPE_ACTIVITY,
            'content' => $content,
            'content_type' => Message::CONTENT_TEXT,
        ]);

        // Notify realtime listeners
        try {
            $message->sendUpdateEvent();
        } catch (\Throwable $e) {
            // swallow; best-effort
            Log::warning('CreateActivityMessageJob: sendUpdateEvent failed', ['error' => $e->getMessage()]);
        }
    }
}
