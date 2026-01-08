<?php

namespace App\Models\Channels;

use App\Models\Account;
use App\Models\Inbox;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Http;

class TikTok extends Model
{
    use HasFactory;
    use \App\Traits\Reauthorizable;

    protected $table = 'channel_tiktok';

    protected $fillable = [
        'account_id',
        'business_id',
        'access_token',
        'refresh_token',
        'expires_at',
        'refresh_token_expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'refresh_token_expires_at' => 'datetime',
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
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
        return 'TikTok';
    }

    public function getValidatedAccessToken(): string
    {
        // Check if token needs refresh
        if ($this->expires_at && $this->expires_at->isPast()) {
            $this->refreshAccessToken();
        }

        return $this->access_token;
    }

    public function refreshAccessToken(): bool
    {
        try {
            $response = Http::post('https://business-api.tiktok.com/open_api/v1.3/oauth2/refresh_token/', [
                'app_id' => config('services.tiktok.app_id'),
                'secret' => config('services.tiktok.secret'),
                'refresh_token' => $this->refresh_token,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                $this->update([
                    'access_token' => $data['data']['access_token'],
                    'refresh_token' => $data['data']['refresh_token'],
                    'expires_at' => now()->addSeconds($data['data']['access_token_expire_in']),
                    'refresh_token_expires_at' => now()->addSeconds($data['data']['refresh_token_expire_in']),
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            \Log::error('TikTok token refresh failed: ' . $e->getMessage());
            return false;
        }
    }

    public function subscribe(): bool
    {
        try {
            $response = Http::withHeaders([
                'Access-Token' => $this->getValidatedAccessToken(),
            ])->post('https://business-api.tiktok.com/open_api/v1.3/page/webhook/', [
                'business_id' => $this->business_id,
                'callback_url' => route('webhooks.tiktok', ['business_id' => $this->business_id]),
                'verify_token' => config('services.tiktok.verify_token'),
                'fields' => ['messages'],
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            \Log::error('TikTok subscription failed: ' . $e->getMessage());
            return false;
        }
    }

    public function unsubscribe(): bool
    {
        try {
            $response = Http::withHeaders([
                'Access-Token' => $this->getValidatedAccessToken(),
            ])->delete('https://business-api.tiktok.com/open_api/v1.3/page/webhook/', [
                'business_id' => $this->business_id,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            \Log::error('TikTok unsubscription failed: ' . $e->getMessage());
            return false;
        }
    }
}
