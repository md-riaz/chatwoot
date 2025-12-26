<?php

namespace App\Models\Channels;

use App\Models\Account;
use App\Models\Inbox;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class TwilioSms extends Model
{
    use HasFactory;

    protected $table = 'channel_twilio_sms';

    // Medium constants
    public const MEDIUM_SMS = 'sms';

    public const MEDIUM_WHATSAPP = 'whatsapp';

    protected $fillable = [
        'account_id',
        'phone_number',
        'messaging_service_sid',
        'account_sid',
        'auth_token',
        'medium',
    ];

    protected $hidden = [
        'auth_token',
    ];

    protected $casts = [
        'medium' => 'string',
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
        return 'Twilio SMS';
    }

    public function isSms(): bool
    {
        return $this->medium === self::MEDIUM_SMS;
    }

    public function isWhatsapp(): bool
    {
        return $this->medium === self::MEDIUM_WHATSAPP;
    }
}
