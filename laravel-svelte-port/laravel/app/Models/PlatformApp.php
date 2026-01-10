<?php

namespace App\Models;

use App\Models\Concerns\AccessTokenable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlatformApp extends Model
{
    use HasFactory;
    use AccessTokenable;

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
