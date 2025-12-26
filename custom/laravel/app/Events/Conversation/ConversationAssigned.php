<?php

namespace App\Events\Conversation;

use App\Http\Resources\Conversation\ConversationResource;
use App\Http\Resources\User\UserResource;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Conversation $conversation,
        public ?User $assignee,
        public ?User $previousAssignee = null
    ) {
    }

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel("account.{$this->conversation->account_id}"),
            new PrivateChannel("conversation.{$this->conversation->id}"),
        ];

        // Notify the new assignee
        if ($this->assignee) {
            $channels[] = new PrivateChannel("user.{$this->assignee->id}");
        }

        // Notify the previous assignee
        if ($this->previousAssignee) {
            $channels[] = new PrivateChannel("user.{$this->previousAssignee->id}");
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'conversation.assigned';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation' => new ConversationResource($this->conversation),
            'assignee' => $this->assignee ? new UserResource($this->assignee) : null,
            'previous_assignee' => $this->previousAssignee ? new UserResource($this->previousAssignee) : null,
        ];
    }
}
