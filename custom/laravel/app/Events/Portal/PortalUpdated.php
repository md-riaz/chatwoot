<?php

namespace App\Events\Portal;

use App\Http\Resources\Portal\PortalResource;
use App\Models\Portal;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PortalUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Portal $portal, public string $action = 'updated') {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("account.{$this->portal->account_id}"),
            new PrivateChannel("portal.{$this->portal->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'portal.' . $this->action;
    }

    public function broadcastWith(): array
    {
        return [
            'portal' => new PortalResource($this->portal),
            'action' => $this->action,
        ];
    }
}
