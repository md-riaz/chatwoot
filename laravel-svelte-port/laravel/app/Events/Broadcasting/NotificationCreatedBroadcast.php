<?php

namespace App\Events\Broadcasting;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationCreatedBroadcast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public User $user, public array $payload)
    {
    }

    public function broadcastOn(): array
    {
        // Using user ID channel for now. 
        // If strict Rails parity with pubsub_token is needed, we would use:
        // return [new Channel($this->user->pubsub_token)];
        // But typically Laravel uses PrivateChannel for authenticated users.
        return [
            new PrivateChannel('App.Models.User.' . $this->user->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
