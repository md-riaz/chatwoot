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
            
            // Additional Rails parity fields for frontend compatibility
            'access_token' => $this->access_token ?? null,
            'account_id' => $this->getActiveAccountId(),
            'available_name' => $this->display_name ?: $this->name,
            'message_signature' => $this->message_signature ?? '',
            'provider' => $this->provider ?? 'email',
            'pubsub_token' => $this->pubsub_token ?? null,
            'ui_settings' => $this->ui_settings ?? new \stdClass(),
            'uid' => $this->uid ?? null,
            
            // Spatie roles (platform-level)
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->pluck('name');
            }, []),
            
            // Account relationships with complete Rails parity
            'accounts' => $this->whenLoaded('accountUsers', function () {
                return $this->accountUsers->map(fn($accountUser) => [
                    'id' => $accountUser->account_id,
                    'name' => $accountUser->account->name ?? null,
                    'status' => $accountUser->account->status->getName(),
                    'role' => $accountUser->role->getName(), // Convert enum to string
                    'availability' => $accountUser->availability->getName(), // Convert enum to string
                    'availability_status' => $accountUser->availability->getName(), // Presence status
                    'auto_offline' => $accountUser->auto_offline ?? false,
                    'active_at' => $accountUser->active_at ? $accountUser->active_at->toISOString() : null,
                    'inviter_id' => $accountUser->inviter_id ?? null,
                    'permissions' => $accountUser->permissions ?? [],
                ]);
            }, []),
        ];
    }
    
    /**
     * Get the active account ID for the user
     */
    private function getActiveAccountId(): ?int
    {
        // If accountUsers are loaded, return the first account ID
        if ($this->relationLoaded('accountUsers') && $this->accountUsers->isNotEmpty()) {
            return $this->accountUsers->first()->account_id;
        }
        
        // Fallback: query for the first account
        $firstAccountUser = $this->accountUsers()->first();
        return $firstAccountUser ? $firstAccountUser->account_id : null;
    }
}
