<?php

namespace App\Events\Sla;

use App\Models\AppliedSla;
use App\Models\Conversation;
use App\Models\SlaPolicy;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SlaBreached implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Conversation $conversation,
        public SlaPolicy $policy,
        public array $breaches,
        public ?AppliedSla $appliedSla = null
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("account.{$this->conversation->account_id}"),
            new PrivateChannel("conversation.{$this->conversation->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'sla.breached';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversation->id,
            'policy_id' => $this->policy->id,
            'breaches' => $this->breaches,
            'applied_sla_id' => $this->appliedSla?->id,
        ];
    }
}
