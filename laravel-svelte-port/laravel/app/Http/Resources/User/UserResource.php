<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
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
            'custom_attributes' => $this->custom_attributes,
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // User type (SuperAdmin or User) - critical for frontend authorization
            'type' => $this->type ?? 'User',
            
            // Rails parity fields
            'confirmed' => !is_null($this->email_verified_at),
            'locked' => $this->custom_attributes['locked'] ?? false,
            
            // Spatie roles (platform-level)
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->pluck('name');
            }, []),
            
            // Account relationships with proper enum conversion
            'accounts' => $this->whenLoaded('accountUsers', function () {
                return $this->accountUsers->map(fn($accountUser) => [
                    'id' => $accountUser->account_id,
                    'name' => $accountUser->account->name ?? null,
                    'role' => $accountUser->role->getName(), // Convert enum to string
                    'availability' => $accountUser->availability->getName(), // Convert enum to string
                    'active_at' => $accountUser->active_at ? $accountUser->active_at->toISOString() : null,
                ]);
            }, []),
        ];
    }
}
