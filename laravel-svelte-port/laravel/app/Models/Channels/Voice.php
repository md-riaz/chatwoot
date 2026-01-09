<?php

namespace App\Models\Channels;

use App\Models\Account;
use App\Models\Inbox;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Voice extends Model
{
    use HasFactory;

    protected $table = 'channel_voice';

    protected $fillable = [
        'account_id',
        'phone_number',
        'provider',
        'provider_config',
        'additional_attributes',
    ];

    protected $casts = [
        'provider_config' => 'array',
        'additional_attributes' => 'array',
    ];

    /**
     * Get the provider config attribute with default empty array
     */
    public function getProviderConfigAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * Get the additional attributes with default empty array
     */
    public function getAdditionalAttributesAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the inbox for this channel.
     */
    public function inbox(): MorphOne
    {
        return $this->morphOne(Inbox::class, 'channel');
    }

    public function getName(): string
    {
        return "Voice ({$this->phone_number})";
    }

    /**
     * Check if messaging window is enabled (Rails parity)
     */
    public function messagingWindowEnabled(): bool
    {
        return false;
    }

    /**
     * Initiate a call (placeholder for Twilio integration)
     */
    public function initiateCall(string $to, ?string $conferenceSid = null, ?int $agentId = null): array
    {
        // This would integrate with Twilio Voice API
        // For now, return a placeholder response
        return [
            'call_id' => 'call_' . uniqid(),
            'status' => 'initiated',
            'to' => $to,
            'from' => $this->phone_number,
            'conference_sid' => $conferenceSid,
            'agent_id' => $agentId,
        ];
    }

    /**
     * Get voice call webhook URL (Rails parity)
     */
    public function voiceCallWebhookUrl(): string
    {
        $digits = ltrim($this->phone_number, '+');
        return route('webhooks.twilio.voice.call', ['phone' => $digits]);
    }

    /**
     * Get voice status webhook URL (Rails parity)
     */
    public function voiceStatusWebhookUrl(): string
    {
        $digits = ltrim($this->phone_number, '+');
        return route('webhooks.twilio.voice.status', ['phone' => $digits]);
    }

    /**
     * Check if provider is Twilio
     */
    public function isTwilio(): bool
    {
        return $this->provider === 'twilio';
    }
}