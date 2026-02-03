<?php

namespace App\Events\Conversation;

use App\Models\Conversation;
use App\Models\Team;
use App\Models\User;
use App\Services\WebSocket\BroadcastTargetService;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeamChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Conversation $conversation,
        public ?Team $previousTeam,
        public ?Team $newTeam,
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
        return 'team.changed';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'conversation' => $this->conversation->toArray(),
            'previous_team' => $this->previousTeam ? [
                'id' => $this->previousTeam->id,
                'name' => $this->previousTeam->name,
                'description' => $this->previousTeam->description,
            ] : null,
            'new_team' => $this->newTeam ? [
                'id' => $this->newTeam->id,
                'name' => $this->newTeam->name,
                'description' => $this->newTeam->description,
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