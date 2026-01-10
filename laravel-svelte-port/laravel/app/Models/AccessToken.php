<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class AccessToken extends Model
{
    protected $fillable = [
        'owner_type',
        'owner_id',
        'token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * Hide token in JSON serialization by default for security.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'token',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->token)) {
                // Generate a secure random token similar to Rails has_secure_token
                $model->token = Str::random(64);
            }
        });
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Regenerate the access token with a new secure random value.
     * Named for Rails parity with regenerate_token method.
     *
     * @return string The new token value
     */
    public function regenerateToken(): string
    {
        $this->token = Str::random(64);
        $this->save();
        
        return $this->token;
    }
}