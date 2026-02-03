<?php

namespace App\Events\Conversation;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\WebSocket\BroadcastTargetService;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationMentioned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Conversation $conversation,
        public User $mentionedUser,
        public Message $message,
        public User $mentioner
    ) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $broadcastService = app(BroadcastTargetService::class);
        return $broadcastService->getUserChannels($this->mentionedUser->id);
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'conversation.mentioned';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'conversation' => $this->conversation->toArray(),
            'message' => $this->message->toArray(),
            'mentioned_user' => [
                'id' => $this->mentionedUser->id,
                'name' => $this->mentionedUser->name,
                'avatar_url' => $this->mentionedUser->getAvatarUrl(),
            ],
            'mentioner' => [
                'id' => $this->mentioner->id,
                'name' => $this->mentioner->name,
                'avatar_url' => $this->mentioner->getAvatarUrl(),
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