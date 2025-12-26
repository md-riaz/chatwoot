<?php

namespace App\Http\Resources\Account;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'locale' => $this->locale,
            'domain' => $this->domain,
            'support_email' => $this->support_email,
            'settings' => $this->settings,
            'features' => $this->features,
            'limits' => $this->limits,
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relationships (when loaded)
            'users_count' => $this->whenCounted('users'),
            'inboxes_count' => $this->whenCounted('inboxes'),
            'users' => $this->whenLoaded('users'),
            'inboxes' => $this->whenLoaded('inboxes'),
        ];
    }
}
