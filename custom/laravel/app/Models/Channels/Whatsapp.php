<?php

namespace App\Models\Channels;

use App\Models\Account;
use App\Models\Inbox;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Whatsapp extends Model
{
    use HasFactory;
    use \App\Traits\Reauthorizable;

    protected $table = 'channel_whatsapp';

    // Provider constants
    public const PROVIDER_DEFAULT = 'default';

    public const PROVIDER_CLOUD = 'whatsapp_cloud';

    public const PROVIDERS = [
        self::PROVIDER_DEFAULT,
        self::PROVIDER_CLOUD,
    ];

    protected $fillable = [
        'account_id',
        'phone_number',
        'provider',
        'provider_config',
        'message_templates',
        'message_templates_last_updated',
    ];

    protected $casts = [
        'provider_config' => 'array',
        'message_templates' => 'array',
        'message_templates_last_updated' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function inbox(): MorphOne
    {
        return $this->morphOne(Inbox::class, 'channel');
    }

    public function getName(): string
    {
        return 'Whatsapp';
    }

    public function isCloudProvider(): bool
    {
        return $this->provider === self::PROVIDER_CLOUD;
    }
}
