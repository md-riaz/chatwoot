<?php

namespace App\Http\Resources\SuperAdmin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'domain' => $this->domain,
            'status' => $this->status,
            'locale' => $this->locale,
            'support_email' => $this->support_email,
            'settings' => $this->settings,
            'features' => $this->features,
            'limits' => $this->limits,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Counts (when loaded)
            'users_count' => $this->whenCounted('users'),
            'inboxes_count' => $this->whenCounted('inboxes'),
            'conversations_count' => $this->whenCounted('conversations'),
            'contacts_count' => $this->whenCounted('contacts'),
            
            // Relationships (when loaded)
            'users' => UserResource::collection($this->whenLoaded('users')),
            'recent_conversations' => ConversationResource::collection($this->whenLoaded('recentConversations')),
        ];
    }
}