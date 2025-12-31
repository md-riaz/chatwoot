<?php

namespace App\Events\Conversation;

use App\Http\Resources\Conversation\ConversationResource;
use App\Models\Conversation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Conversation $conversation, public array $changes = []) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("account.{$this->conversation->account_id}"),
            new PrivateChannel("conversation.{$this->conversation->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'conversation.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation' => new ConversationResource($this->conversation),
            'changes' => $this->changes,
        ];
    }
}
