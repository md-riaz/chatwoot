<?php

namespace App\Models\Integration;

use App\Models\Account;
use App\Models\Inbox;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hook extends Model
{
    protected $table = 'integration_hooks';

    protected $fillable = [
        'account_id',
        'inbox_id',
        'app_id',
        'access_token',
        'reference_id',
        'settings',
        'status',
        'hook_type',
    ];

    protected $casts = [
        'settings' => 'array',
        'access_token' => 'encrypted',
    ];

    // Enums
    public const STATUS_ENABLED = 'enabled';
    public const STATUS_DISABLED = 'disabled';
    
    public const HOOK_TYPE_ACCOUNT = 'account';
    public const HOOK_TYPE_INBOX = 'inbox';

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function inbox(): BelongsTo
    {
        return $this->belongsTo(Inbox::class);
    }

    public function isSlack(): bool
    {
        return $this->app_id === 'slack';
    }

    public function isLinear(): bool
    {
        return $this->app_id === 'linear';
    }

    public function isShopify(): bool
    {
        return $this->app_id === 'shopify';
    }

    public function isDyte(): bool
    {
        return $this->app_id === 'dyte';
    }

    public function disable(): void
    {
        $this->update(['status' => self::STATUS_DISABLED]);
    }

    public function enable(): void
    {
        $this->update(['status' => self::STATUS_ENABLED]);
    }

    public function isEnabled(): bool
    {
        return $this->status === self::STATUS_ENABLED;
    }
}