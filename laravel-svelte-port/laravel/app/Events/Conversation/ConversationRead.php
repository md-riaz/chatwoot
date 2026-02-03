<?php

namespace App\Events\Conversation;

use App\Models\Conversation;
use App\Models\User;
use App\Services\WebSocket\BroadcastTargetService;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Conversation $conversation,
        public User $reader
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
        return 'conversation.read';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'conversation' => [
                'id' => $this->conversation->id,
                'unread_count' => $this->conversation->unread_count ?? 0,
                'last_activity_at' => $this->conversation->last_activity_at?->toISOString(),
            ],
            'reader' => [
                'id' => $this->reader->id,
                'name' => $this->reader->name,
                'avatar_url' => $this->reader->getAvatarUrl(),
            ],
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Determine if this event should broadcast.
     */
    public function shouldBroadcast(): bool
    {
        return true;
    }
}