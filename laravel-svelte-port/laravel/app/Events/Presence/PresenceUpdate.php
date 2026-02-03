<?php

namespace App\Events\Presence;

use App\Models\Contact;
use App\Models\User;
use App\Services\WebSocket\BroadcastTargetService;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PresenceUpdate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User|Contact $user,
        public int $accountId,
        public string $status, // 'online', 'offline', 'away'
        public ?array $metadata = null
    ) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel("account.{$this->accountId}.presence"),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'presence.update';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'avatar_url' => $this->user instanceof User 
                    ? $this->user->getAvatarUrl() 
                    : ($this->user->avatar_url ?? null),
                'type' => $this->user instanceof Contact ? 'contact' : 'agent',
                'availability' => $this->user instanceof User 
                    ? $this->user->availability 
                    : null,
            ],
            'status' => $this->status,
            'metadata' => $this->metadata,
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