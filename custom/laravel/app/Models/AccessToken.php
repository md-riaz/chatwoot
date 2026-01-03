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

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->token)) {
                $model->token = Str::random(64);
            }
        });
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function regenerate(): string
    {
        $this->token = Str::random(64);
        $this->save();
        
        return $this->token;
    }
}