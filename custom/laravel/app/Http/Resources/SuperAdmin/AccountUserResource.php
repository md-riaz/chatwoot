<?php

namespace App\Http\Resources\SuperAdmin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountUserResource extends JsonResource
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
            'user_id' => $this->user_id,
            'account_id' => $this->account_id,
            'role' => $this->role,
            'role_name' => $this->role_name,
            'availability' => $this->availability,
            'availability_name' => $this->availability_name,
            'active_at' => $this->active_at,
            'settings' => $this->settings,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relationships (when loaded)
            'user' => new UserResource($this->whenLoaded('user')),
            'account' => new AccountResource($this->whenLoaded('account')),
            
            // Statistics (when available)
            'stats' => $this->when(isset($this->stats), $this->stats),
        ];
    }
}