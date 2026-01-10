<?php

namespace App\Models\Concerns;

use App\Models\AccessToken;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait AccessTokenable
{
    protected static function bootAccessTokenable(): void
    {
        // Use 'created' event to match Rails after_create behavior
        static::created(function ($model) {
            $model->createAccessToken();
        });

        // Add cascade delete for Rails parity (dependent: :destroy_async)
        static::deleting(function ($model) {
            $model->accessTokenModel()->delete();
        });
    }

    /**
     * Get the access token for this model (Laravel relationship)
     */
    public function accessTokenModel(): MorphOne
    {
        return $this->morphOne(AccessToken::class, 'owner');
    }

    /**
     * Create a new access token
     */
    public function createAccessToken(): AccessToken
    {
        return $this->accessTokenModel()->create();
    }

    /**
     * Reset/regenerate the access token
     */
    public function resetAccessToken(): string
    {
        $accessToken = $this->accessTokenModel;
        if ($accessToken) {
            return $accessToken->regenerateToken();
        }
        
        return $this->createAccessToken()->token;
    }

    /**
     * Get access token string (Rails compatibility)
     */
    public function getAccessTokenAttribute(): ?string
    {
        return $this->accessTokenModel?->token;
    }
}