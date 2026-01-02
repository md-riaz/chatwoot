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

    /**
     * Get the Twitter API client for this channel.
     */
    public function getTwitterClient(): \App\Services\Channels\Twitter\TwitterApiClient
    {
        return new \App\Services\Channels\Twitter\TwitterApiClient($this);
    }

    /**
     * Create a contact inbox for a Twitter profile.
     */
    public function createContactInbox(string $profileId, string $name, array $additionalAttributes = []): mixed
    {
        // This would integrate with the contact inbox creation system
        // Implementation depends on the Laravel contact management system
        return null; // Placeholder for now
    }
}
