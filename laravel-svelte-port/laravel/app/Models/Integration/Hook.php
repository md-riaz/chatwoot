<?php

namespace App\Models\Integration;

use App\Models\Account;
use App\Models\Inbox;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hook extends Model
{
    protected $table = 'integrations_hooks';

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

    // Status constants (mapping to existing integer values)
    public const STATUS_DISABLED = 0;
    public const STATUS_ENABLED = 1;
    
    // Hook type constants (mapping to existing integer values)
    public const HOOK_TYPE_ACCOUNT = 0;
    public const HOOK_TYPE_INBOX = 1;

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
        return $this->status == self::STATUS_ENABLED;
    }

    // Accessor for status as string (for backward compatibility)
    public function getStatusAttribute($value): string
    {
        return $value == self::STATUS_ENABLED ? 'enabled' : 'disabled';
    }

    // Mutator for status from string
    public function setStatusAttribute($value): void
    {
        if (is_string($value)) {
            $this->attributes['status'] = $value === 'enabled' ? self::STATUS_ENABLED : self::STATUS_DISABLED;
        } else {
            $this->attributes['status'] = $value;
        }
    }

    // Accessor for hook_type as string (for backward compatibility)
    public function getHookTypeAttribute($value): string
    {
        return $value == self::HOOK_TYPE_INBOX ? 'inbox' : 'account';
    }

    // Mutator for hook_type from string
    public function setHookTypeAttribute($value): void
    {
        if (is_string($value)) {
            $this->attributes['hook_type'] = $value === 'inbox' ? self::HOOK_TYPE_INBOX : self::HOOK_TYPE_ACCOUNT;
        } else {
            $this->attributes['hook_type'] = $value;
        }
    }
}