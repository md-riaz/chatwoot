<?php

namespace App\Models\Channels;

use App\Models\Account;
use App\Models\Inbox;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Instagram extends Model
{
    use HasFactory;

    protected $table = 'channel_instagram';

    protected $fillable = [
        'account_id',
        'instagram_id',
        'access_token',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected $hidden = [
        'access_token',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function inbox(): HasOne
    {
        return $this->hasOne(Inbox::class, 'channel_id')->where('channel_type', self::class);
    }

    public function name(): string
    {
        return 'Instagram';
    }

    public function subscribe(): bool
    {
        // TODO: Implement Instagram subscription
        return true;
    }

    public function unsubscribe(): bool
    {
        // TODO: Implement Instagram unsubscription
        return true;
    }
}
