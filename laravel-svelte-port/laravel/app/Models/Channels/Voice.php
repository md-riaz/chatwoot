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
     * Validation rules (Rails parity).
     */
    public static function validationRules(): array
    {
        return [
            'phone_number' => 'required|string|unique:channel_voice,phone_number|regex:/^\+[1-9]\d{1,14}$/',
            'provider' => 'required|string|in:twilio',
            'provider_config' => 'required|array',
        ];
    }

    /**
     * Boot the model and set up event listeners.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($voice) {
            if ($voice->isTwilio()) {
                $voice->provisionTwilioOnCreate();
            }
        });
    }

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

    /**
     * Validate provider configuration (Rails parity).
     */
    public function validateProviderConfig(): void
    {
        if (empty($this->provider_config)) {
            return;
        }

        if ($this->isTwilio()) {
            $this->validateTwilioConfig();
        }
    }

    /**
     * Get provider config as hash (Rails parity).
     */
    public function providerConfigHash(): array
    {
        if (is_array($this->provider_config)) {
            return $this->provider_config;
        }

        return json_decode($this->provider_config ?: '{}', true);
    }

    /**
     * Provision Twilio on create (Rails parity).
     */
    private function provisionTwilioOnCreate(): void
    {
        try {
            $service = new \App\Services\Voice\Provider\Twilio\WebhookSetupService($this);
            $appSid = $service->perform();
            
            if (empty($appSid)) {
                return;
            }

            $config = $this->providerConfigHash();
            $config['twiml_app_sid'] = $appSid;
            $this->provider_config = $config;
        } catch (\Exception $e) {
            $errorDetails = [
                'error_class' => get_class($e),
                'message' => $e->getMessage(),
                'phone_number' => $this->phone_number,
                'account_id' => $this->account_id,
                'backtrace' => array_slice($e->getTrace(), 0, 5),
            ];
            
            \Illuminate\Support\Facades\Log::error("TWILIO_VOICE_SETUP_ON_CREATE_ERROR: " . json_encode($errorDetails));
            
            // Don't throw - allow creation to continue but log the error
            // This matches Rails behavior where setup errors don't prevent creation
        }
    }

    /**
     * Validate Twilio configuration (Rails parity).
     */
    private function validateTwilioConfig(): void
    {
        $config = $this->providerConfigHash();
        $requiredKeys = ['account_sid', 'auth_token', 'api_key_sid', 'api_key_secret', 'twiml_app_sid'];
        
        foreach ($requiredKeys as $key) {
            if (empty($config[$key])) {
                throw new \InvalidArgumentException("{$key} is required for Twilio provider");
            }
        }
    }
}