<?php

namespace App\Models\Channels;

use App\Models\Account;
use App\Models\Inbox;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class TwitterProfile extends Model
{
    use HasFactory;
    use \App\Traits\Reauthorizable;

    protected $table = 'channel_twitter_profiles';

    protected $fillable = [
        'account_id',
        'profile_id',
        'twitter_access_token',
        'twitter_access_token_secret',
        'tweets_enabled',
        'provider_config',
    ];

    protected $hidden = [
        'twitter_access_token',
        'twitter_access_token_secret',
    ];

    protected $casts = [
        'tweets_enabled' => 'boolean',
        'provider_config' => 'array',
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
        return 'Twitter';
    }
}
