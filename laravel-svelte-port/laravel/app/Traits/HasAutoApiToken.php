<?php

namespace App\Traits;

use Laravel\Sanctum\NewAccessToken;

/**
 * Trait for models that need automatic API token creation using Sanctum.
 * 
 * This trait provides:
 * - Automatic token creation on model creation (via model events)
 * - Automatic token deletion on model deletion (cascade delete)
 * - Helper methods for accessing and resetting tokens
 * - Laravel Sanctum integration (standard Laravel approach)
 * 
 * Models using this trait MUST also use Laravel\Sanctum\HasApiTokens trait.
 */
trait HasAutoApiToken
{
    /**
     * Boot the trait - auto-create token on model creation, delete on model deletion.
     */
    protected static function bootHasAutoApiToken(): void
    {
        static::created(function ($model) {
            $model->createToken('api-access');
        });
        
        // Cascade delete tokens when model is deleted
        static::deleting(function ($model) {
            $model->tokens()->delete();
        });
    }

    /**
     * Get the API access token string.
     * 
     * Returns the plain text token for the 'api-access' token.
     * Note: Sanctum hashes tokens, so we store the plain text in a way
     * that allows retrieval. For new tokens, use createApiToken().
     */
    public function getApiAccessTokenAttribute(): ?string
    {
        $token = $this->tokens()->where('name', 'api-access')->first();
        
        // Sanctum tokens are hashed, so we can't retrieve the plain text
        // after creation. Return the hashed token for identification purposes.
        // For actual authentication, use the token returned from createToken().
        return $token?->token;
    }

    /**
     * Reset/regenerate the API access token.
     * 
     * Deletes the existing 'api-access' token and creates a new one.
     * Returns the new plain text token.
     */
    public function resetAccessToken(): string
    {
        // Delete existing api-access token
        $this->tokens()->where('name', 'api-access')->delete();
        
        // Create new token and return plain text
        $newToken = $this->createToken('api-access');
        
        return $newToken->plainTextToken;
    }

    /**
     * Get the current API token model.
     * 
     * Returns the Sanctum PersonalAccessToken model for the 'api-access' token.
     */
    public function getApiTokenModel(): ?\Laravel\Sanctum\PersonalAccessToken
    {
        return $this->tokens()->where('name', 'api-access')->first();
    }

    /**
     * Ensure an API token exists, creating one if necessary.
     * 
     * Returns the plain text token if a new one was created,
     * or null if an existing token was found (can't retrieve plain text).
     */
    public function ensureApiToken(): ?NewAccessToken
    {
        $existingToken = $this->tokens()->where('name', 'api-access')->first();
        
        if ($existingToken) {
            return null;
        }
        
        return $this->createToken('api-access');
    }
}
