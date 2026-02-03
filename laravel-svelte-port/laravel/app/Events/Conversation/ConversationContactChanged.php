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

class ConversationContactChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Conversation $conversation,
        public Contact $previousContact,
        public Contact $newContact,
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
        return 'conversation.contact_changed';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'conversation' => $this->conversation->toArray(),
            'previous_contact' => $this->previousContact->toArray(),
            'new_contact' => $this->newContact->toArray(),
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