<?php

namespace App\Events\Notification;

use App\Models\Notification;
use App\Models\User;
use App\Services\WebSocket\BroadcastTargetService;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Notification $notification,
        public ?User $performer = null
    ) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $broadcastService = app(BroadcastTargetService::class);
        return $broadcastService->getUserChannels($this->notification->user_id);
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'notification.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'notification' => $this->notification->toArray(),
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