<?php

namespace App\Events\Conversation;

use App\Http\Resources\Conversation\ConversationResource;
use App\Models\Conversation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Conversation $conversation)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("account.{$this->conversation->account_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'conversation.created';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation' => new ConversationResource($this->conversation->load('contact', 'inbox', 'assignee')),
        ];
    }
}
