<?php

namespace App\Models\Channels;

use App\Models\Account;
use App\Models\Inbox;
use App\Services\Voice\Provider\Twilio\AdapterService;
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
        'additional_attributes',
    ];

    protected $casts = [
        'provider_config' => 'array',
        'additional_attributes' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($channel) {
            $channel->validatePhoneNumber();
            $channel->validateProviderConfig();
        });

        static::updating(function ($channel) {
            if ($channel->isDirty('phone_number')) {
                $channel->validatePhoneNumber();
            }
            if ($channel->isDirty('provider_config')) {
                $channel->validateProviderConfig();
            }
        });
    }

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
        return "Voice ({$this->phone_number})";
    }

    /**
     * Initiate a call via the provider.
     */
    public function initiateCall(string $to, ?string $conferenceSid = null, ?int $agentId = null): array
    {
        switch ($this->provider) {
            case 'twilio':
                $adapter = app(AdapterService::class);
                return $adapter->initiateCall($this, $to, $conferenceSid, $agentId);
            default:
                throw new \InvalidArgumentException("Unsupported voice provider: {$this->provider}");
        }
    }

    /**
     * Get webhook URLs for provider configuration.
     */
    public function getVoiceCallWebhookUrl(): string
    {
        $digits = ltrim($this->phone_number, '+');
        return url("/api/v1/webhooks/voice/call/{$digits}");
    }

    public function getVoiceStatusWebhookUrl(): string
    {
        $digits = ltrim($this->phone_number, '+');
        return url("/api/v1/webhooks/voice/status/{$digits}");
    }

    /**
     * Validate phone number format (E.164).
     */
    private function validatePhoneNumber(): void
    {
        if (!preg_match('/^\+[1-9]\d{1,14}$/', $this->phone_number)) {
            throw new \InvalidArgumentException('Phone number must be in E.164 format (e.g., +1234567890)');
        }
    }

    /**
     * Validate provider configuration.
     */
    private function validateProviderConfig(): void
    {
        if (empty($this->provider_config)) {
            throw new \InvalidArgumentException('Provider config is required');
        }

        switch ($this->provider) {
            case 'twilio':
                $this->validateTwilioConfig();
                break;
            default:
                throw new \InvalidArgumentException("Unsupported provider: {$this->provider}");
        }
    }

    private function validateTwilioConfig(): void
    {
        $config = $this->provider_config;
        $requiredKeys = ['account_sid', 'auth_token'];
        
        foreach ($requiredKeys as $key) {
            if (empty($config[$key])) {
                throw new \InvalidArgumentException("{$key} is required for Twilio provider");
            }
        }
    }
}
