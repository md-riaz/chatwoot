<?php

namespace App\Models;

use App\Traits\HasAutoApiToken;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class PlatformApp extends Model implements Authenticatable
{
    use HasFactory, HasApiTokens, HasAutoApiToken;

    protected $fillable = [
        'name',
    ];

    /**
     * Get the name of the unique identifier for the user.
     */
    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    /**
     * Get the unique identifier for the user.
     */
    public function getAuthIdentifier(): mixed
    {
        return $this->id;
    }

    /**
     * Get the password for the user.
     */
    public function getAuthPassword(): string
    {
        return '';
    }

    /**
     * Get the token value for the "remember me" session.
     */
    public function getRememberToken(): ?string
    {
        return null;
    }

    /**
     * Set the token value for the "remember me" session.
     */
    public function setRememberToken($value): void
    {
        // PlatformApps don't use remember tokens
    }

    /**
     * Get the column name for the "remember me" token.
     */
    public function getRememberTokenName(): string
    {
        return '';
    }

    /**
     * Get the password hash for the user.
     */
    public function getAuthPasswordName(): string
    {
        return '';
    }

    /**
     * Get the platform app permissibles.
     */
    public function permissibles(): HasMany
    {
        return $this->hasMany(PlatformAppPermissible::class);
    }
}
