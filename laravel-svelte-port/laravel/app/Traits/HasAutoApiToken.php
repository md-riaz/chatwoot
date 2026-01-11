<?php

namespace App\Traits;

use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\NewAccessToken;

/**
 * Trait for models that need automatic API token creation using Sanctum.
 * 
 * This trait provides:
 * - Automatic token creation on model creation (via model events)
 * - Automatic token deletion on model deletion (cascade delete)
 * - Helper methods for accessing and resetting tokens
 * - Laravel Sanctum integration (standard Laravel approach)
 * - Plain text token storage for API response compatibility (Rails parity)
 * 
 * Models using this trait MUST:
 * - Use Laravel\Sanctum\HasApiTokens trait
 * - Have an 'access_token' column in their database table (for token storage)
 */
trait HasAutoApiToken
{
    /**
     * Boot the trait - auto-create token on model creation, delete on model deletion.
     */
    protected static function bootHasAutoApiToken(): void
    {
        static::created(function ($model) {
            $model->generateAndStoreAccessToken();
        });
        
        // Cascade delete tokens when model is deleted
        static::deleting(function ($model) {
            $model->tokens()->delete();
        });
    }

    /**
     * Check if this model has an access_token column.
     */
    protected function hasAccessTokenColumn(): bool
    {
        return in_array('access_token', $this->getFillable()) || 
               Schema::hasColumn($this->getTable(), 'access_token');
    }

    /**
     * Generate a new Sanctum token and store the plain text in the model.
     * 
     * This ensures the plain text token is available for API responses
     * (matching Rails behavior where tokens are stored in plain text).
     */
    public function generateAndStoreAccessToken(): string
    {
        // Create Sanctum token
        $newToken = $this->createToken('api-access');
        $plainTextToken = $newToken->plainTextToken;
        
        // Store plain text token in the model's access_token column if it exists
        if ($this->hasAccessTokenColumn()) {
            $this->forceFill(['access_token' => $plainTextToken])->saveQuietly();
        }
        
        return $plainTextToken;
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
        
        // Generate new token and store it
        return $this->generateAndStoreAccessToken();
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
     * or the existing stored token if one exists.
     */
    public function ensureApiToken(): string
    {
        // If we have a stored access_token, return it
        if ($this->hasAccessTokenColumn() && !empty($this->access_token)) {
            return $this->access_token;
        }
        
        // Check if Sanctum token exists but access_token column is empty
        $existingToken = $this->tokens()->where('name', 'api-access')->first();
        
        if ($existingToken) {
            // Token exists but plain text not stored - need to regenerate
            $this->tokens()->where('name', 'api-access')->delete();
        }
        
        // Generate new token
        return $this->generateAndStoreAccessToken();
    }
}
