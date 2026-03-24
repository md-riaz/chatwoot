<?php

namespace App\Models\Channels;

use App\Models\Account;
use App\Models\Inbox;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Http;

class Instagram extends Model
{
    use HasFactory;
    use \App\Traits\Reauthorizable;

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
        return $this->hasOne(Inbox::class, 'channel_id')->where('channel_type', 'Channel::Instagram');
    }

    public function name(): string
    {
        return 'Instagram';
    }

    public function subscribe(): bool
    {
        try {
            $response = Http::post("https://graph.instagram.com/v22.0/{$this->instagram_id}/subscribed_apps", [
                'subscribed_fields' => 'messages,message_reactions,messaging_seen',
                'access_token' => $this->access_token,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            \Log::error('Instagram subscription failed: ' . $e->getMessage());
            return false;
        }
    }

    public function unsubscribe(): bool
    {
        try {
            $response = Http::delete("https://graph.instagram.com/v22.0/{$this->instagram_id}/subscribed_apps", [
                'access_token' => $this->access_token,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            \Log::error('Instagram unsubscription failed: ' . $e->getMessage());
            return false;
        }
    }
}
