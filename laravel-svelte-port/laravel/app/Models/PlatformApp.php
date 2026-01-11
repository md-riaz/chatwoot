<?php

namespace App\Models;

use App\Traits\HasAutoApiToken;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class PlatformApp extends Model
{
    use HasFactory, HasApiTokens, HasAutoApiToken;

    protected $fillable = [
        'name',
    ];

    /**
     * Get the platform app permissibles.
     */
    public function permissibles(): HasMany
    {
        return $this->hasMany(PlatformAppPermissible::class);
    }
}
