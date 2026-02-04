<?php

namespace App\Http\Resources\Contact;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'identifier' => $this->identifier,
            'blocked' => $this->blocked,
            'thumbnail' => $this->getAvatarUrl(), // Rails uses 'thumbnail' not 'avatar_url'
            'custom_attributes' => $this->custom_attributes ?? [],
            'additional_attributes' => $this->additional_attributes ?? [],
            'last_activity_at' => $this->last_activity_at?->timestamp, // Rails uses timestamp not ISO string
            'created_at' => $this->created_at?->timestamp,
            'updated_at' => $this->updated_at?->timestamp,

            // Relationships (when loaded)
            'conversations_count' => $this->whenCounted('conversations'),
        ];
    }
}
