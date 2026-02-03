<?php

namespace App\Events\Conversation;

use App\Models\Contact;
use App\Models\Conversation;
use App\Models\User;
use App\Services\WebSocket\BroadcastTargetService;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Conversation $conversation,
        public User|Contact $typer,
        public bool $isTyping = true
    ) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $broadcastService = app(BroadcastTargetService::class);
        return $broadcastService->getConversationChannels($this->conversation);
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return $this->isTyping ? 'conversation.typing_on' : 'conversation.typing_off';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversation->id,
            'typer' => [
                'id' => $this->typer->id,
                'name' => $this->typer->name,
                'avatar_url' => $this->typer instanceof User 
                    ? $this->typer->getAvatarUrl() 
                    : ($this->typer->avatar_url ?? null),
                'type' => $this->typer instanceof Contact ? 'contact' : 'agent',
            ],
            'is_typing' => $this->isTyping,
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Exclude the typer from receiving their own typing events.
     */
    public function broadcastToOthers(): bool
    {
        return true;
    }

    /**
     * Determine if this event should broadcast.
     */
    public function shouldBroadcast(): bool
    {
        return true;
    }
}