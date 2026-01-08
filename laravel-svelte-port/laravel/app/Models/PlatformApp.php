<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PlatformApp extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    protected $hidden = [
        'access_token',
    ];

    protected static function booted(): void
    {
        static::creating(function (PlatformApp $platformApp) {
            $platformApp->access_token = Str::random(64);
        });
    }

    /**
     * Get the platform app permissibles.
     */
    public function permissibles(): HasMany
    {
        return $this->hasMany(PlatformAppPermissible::class);
    }

    /**
     * Regenerate access token.
     */
    public function regenerateAccessToken(): string
    {
        $this->access_token = Str::random(64);
        $this->save();

        return $this->access_token;
    }
}
