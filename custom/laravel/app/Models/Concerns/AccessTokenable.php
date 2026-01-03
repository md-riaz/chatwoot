<?php

namespace App\Models\Concerns;

use App\Models\AccessToken;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait AccessTokenable
{
    protected static function bootAccessTokenable()
    {
        static::created(function ($model) {
            $model->createAccessToken();
        });
    }

    public function accessToken(): MorphOne
    {
        return $this->morphOne(AccessToken::class, 'owner');
    }

    public function createAccessToken(): AccessToken
    {
        return $this->accessToken()->create();
    }

    public function resetAccessToken(): string
    {
        if ($this->accessToken) {
            return $this->accessToken->regenerate();
        }
        
        return $this->createAccessToken()->token;
    }

    public function getAccessTokenAttribute(): ?string
    {
        return $this->accessToken?->token;
    }
}