<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformAppPermissible extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform_app_id',
        'permissible_type',
        'permissible_id',
    ];

    /**
     * Get the platform app.
     */
    public function platformApp(): BelongsTo
    {
        return $this->belongsTo(PlatformApp::class);
    }

    /**
     * Get the permissible model (Account or User).
     */
    public function permissible(): MorphTo
    {
        return $this->morphTo();
    }
}
