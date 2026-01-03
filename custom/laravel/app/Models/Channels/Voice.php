<?php

namespace App\Models\Channels;

use App\Models\Account;
use App\Models\Inbox;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Voice extends Model
{
    use HasFactory;

    protected $table = 'channel_voice';

    protected $fillable = [
        'account_id',
        'phone_number',
        'provider',
        'provider_config',
        'greeting_enabled',
        'greeting_message',
    ];

    protected $casts = [
        'provider_config' => 'array',
        'greeting_enabled' => 'boolean',
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
        return 'Voice';
    }

    public function makeCall(string $toNumber, string $message = null): array
    {
        // This would integrate with voice providers like Twilio Voice, etc.
        // For now, return a placeholder response
        return [
            'call_id' => 'call_' . uniqid(),
            'status' => 'initiated',
            'to' => $toNumber,
            'from' => $this->phone_number,
        ];
    }

    public function handleIncomingCall(array $callData): array
    {
        // Process incoming call webhook
        // Create conversation, handle IVR, etc.
        return [
            'status' => 'received',
            'conversation_id' => null, // Would create conversation
        ];
    }

    public function getCallbackUrl(): string
    {
        return route('webhooks.voice', ['channel_id' => $this->id]);
    }
}