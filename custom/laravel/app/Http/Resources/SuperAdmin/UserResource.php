<?php

namespace App\Http\Resources\SuperAdmin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'display_name' => $this->display_name,
            'phone_number' => $this->phone_number,
            'avatar_url' => $this->getAvatarUrl(),
            'availability' => $this->availability,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // User type (SuperAdmin or User) - critical for authorization
            'type' => $this->type ?? 'User',
            
            // Counts (when loaded)
            'accounts_count' => $this->whenCounted('accounts'),
            
            // Relationships (when loaded)
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->pluck('name');
            }),
            'accounts' => AccountResource::collection($this->whenLoaded('accounts')),
            'account_users' => AccountUserResource::collection($this->whenLoaded('accountUsers')),
        ];
    }
}