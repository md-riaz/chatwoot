<?php

namespace App\Events\Conversation;

use App\Models\Conversation;
use App\Models\User;
use App\Services\WebSocket\BroadcastTargetService;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AssigneeChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Conversation $conversation,
        public ?User $previousAssignee,
        public ?User $newAssignee,
        public ?User $performer = null
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
        return 'assignee.changed';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'conversation' => $this->conversation->toArray(),
            'previous_assignee' => $this->previousAssignee ? [
                'id' => $this->previousAssignee->id,
                'name' => $this->previousAssignee->name,
                'avatar_url' => $this->previousAssignee->getAvatarUrl(),
            ] : null,
            'new_assignee' => $this->newAssignee ? [
                'id' => $this->newAssignee->id,
                'name' => $this->newAssignee->name,
                'avatar_url' => $this->newAssignee->getAvatarUrl(),
            ] : null,
            'performer' => $this->performer ? [
                'id' => $this->performer->id,
                'name' => $this->performer->name,
                'avatar_url' => $this->performer->getAvatarUrl(),
            ] : null,
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