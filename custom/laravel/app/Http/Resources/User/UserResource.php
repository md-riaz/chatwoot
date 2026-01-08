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
            'avatar_url' => $this->avatar_url,
            'availability' => $this->availability,
            'custom_attributes' => $this->custom_attributes,
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            // User type (SuperAdmin or User) - critical for frontend authorization
            'type' => $this->type ?? 'User',
            // Add roles and accounts information
            'roles' => $this->roles->pluck('name'),
            'accounts' => $this->accountUsers->map(fn($accountUser) => [
                'id' => $accountUser->account_id,
                'name' => $accountUser->account->name ?? null,
                'role' => $accountUser->role,
                'availability' => $accountUser->availability,
                'active_at' => $accountUser->active_at?->toISOString(),
            ]),
        ];
    }
}
