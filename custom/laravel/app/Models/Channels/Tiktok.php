<?php

namespace App\Models\Channels;

use App\Models\Account;
use App\Models\Inbox;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tiktok extends Model
{
    use HasFactory;

    protected $table = 'channel_tiktok';

    protected $fillable = [
        'account_id',
        'business_id',
        'access_token',
        'expires_at',
        'refresh_token',
        'refresh_token_expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'refresh_token_expires_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Tiktok channel may be attached to an inbox through polymorphic channel.
     */
    public function inbox(): BelongsTo
    {
        return $this->belongsTo(Inbox::class, 'id', 'channel_id');
    }

    /**
     * Get a validated access token, refreshing if necessary.
     */
    public function getValidatedAccessToken(): ?string
    {
        $tokenService = new \App\Services\Channels\Tiktok\TiktokTokenService($this);
        return $tokenService->getAccessToken();
    }

    /**
     * Get the channel name.
     */
    public function getName(): string
    {
        return 'TikTok';
    }
}
