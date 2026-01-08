<?php

namespace App\Http\Resources\Message;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'conversation_id' => $this->conversation_id,
            'inbox_id' => $this->inbox_id,
            'sender_id' => $this->sender_id,
            'sender_type' => $this->sender_type,
            'message_type' => $this->message_type,
            'content' => $this->content,
            'content_attributes' => $this->content_attributes,
            'content_type' => $this->content_type,
            'status' => $this->status,
            'private' => $this->private,
            'external_source_id' => $this->external_source_id,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relationships (when loaded)
            'sender' => $this->whenLoaded('sender'),
            'attachments' => $this->whenLoaded('attachments'),
        ];
    }
}
