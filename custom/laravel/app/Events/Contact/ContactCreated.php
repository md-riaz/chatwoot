<?php

namespace App\Events\Contact;

use App\Http\Resources\Contact\ContactResource;
use App\Models\Contact;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContactCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Contact $contact) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("account.{$this->contact->account_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'contact.created';
    }

    public function broadcastWith(): array
    {
        return [
            'contact' => new ContactResource($this->contact),
        ];
    }
}
