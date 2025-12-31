<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    use HasFactory;

    // Campaign type constants
    public const TYPE_ONGOING = 0;

    public const TYPE_ONE_OFF = 1;

    // Campaign status constants
    public const STATUS_ACTIVE = 0;

    public const STATUS_COMPLETED = 1;

    public const STATUS_PARTIALLY_COMPLETED = 2;

    public const STATUS_FAILED = 3;

    protected $fillable = [
        'account_id',
        'inbox_id',
        'sender_id',
        'display_id',
        'title',
        'description',
        'message',
        'campaign_type',
        'campaign_status',
        'enabled',
        'trigger_only_during_business_hours',
        'scheduled_at',
        'trigger_rules',
        'audience',
        'template_params',
        'dispatched_at',
    ];

    protected $casts = [
        'campaign_type' => 'integer',
        'campaign_status' => 'integer',
        'enabled' => 'boolean',
        'trigger_only_during_business_hours' => 'boolean',
        'scheduled_at' => 'datetime',
        'trigger_rules' => 'array',
        'audience' => 'array',
        'template_params' => 'array',
        'dispatched_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function inbox(): BelongsTo
    {
        return $this->belongsTo(Inbox::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function isOngoing(): bool
    {
        return $this->campaign_type === self::TYPE_ONGOING;
    }

    public function isOneOff(): bool
    {
        return $this->campaign_type === self::TYPE_ONE_OFF;
    }

    public function isActive(): bool
    {
        return $this->campaign_status === self::STATUS_ACTIVE;
    }

    public function isCompleted(): bool
    {
        return $this->campaign_status === self::STATUS_COMPLETED;
    }
}
