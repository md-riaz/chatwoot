<?php

namespace App\Models\Channels;

use App\Models\Account;
use App\Models\Inbox;
use App\Services\Channels\Whatsapp\Providers\WhatsAppCloudProvider;
use App\Services\Channels\Whatsapp\Providers\WhatsApp360DialogProvider;
use App\Jobs\Channels\SyncWhatsAppTemplatesJob;
use App\Jobs\Channels\SetupWhatsAppWebhooksJob;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;

class Whatsapp extends Model
{
    use HasFactory;
    use \App\Traits\Reauthorizable;

    protected $table = 'channel_whatsapp';

    // Provider constants
    public const PROVIDER_DEFAULT = 'default';
    public const PROVIDER_CLOUD = 'whatsapp_cloud';
    public const PROVIDERS = [
        self::PROVIDER_DEFAULT,
        self::PROVIDER_CLOUD,
    ];

    // Authorization error threshold for this channel type
    public const AUTHORIZATION_ERROR_THRESHOLD = 3;

    protected $fillable = [
        'account_id',
        'phone_number',
        'phone_number_id',
        'business_account_id',
        'access_token',
        'verify_token',
        'provider',
        'provider_config',
        'message_templates',
        'message_templates_last_updated',
    ];

    protected $casts = [
        'provider_config' => 'array',
        'message_templates' => 'array',
        'message_templates_last_updated' => 'datetime',
        'access_token' => 'encrypted',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($channel) {
            $channel->ensureWebhookVerifyToken();
        });

        static::created(function ($channel) {
            SyncWhatsAppTemplatesJob::dispatch($channel);
        });

        static::deleting(function ($channel) {
            // Teardown webhooks before deletion
            $channel->teardownWebhooks();
        });
    }

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
        return 'Whatsapp';
    }

    public function isCloudProvider(): bool
    {
        return $this->provider === self::PROVIDER_CLOUD;
    }

    /**
     * Get the appropriate provider service
     */
    public function getProviderService()
    {
        return match ($this->provider) {
            self::PROVIDER_CLOUD => new WhatsAppCloudProvider($this),
            default => new WhatsApp360DialogProvider($this),
        };
    }

    /**
     * Send a message via the provider
     */
    public function sendMessage(string $phoneNumber, $message): ?string
    {
        return $this->getProviderService()->sendMessage($phoneNumber, $message);
    }

    /**
     * Send a template message
     */
    public function sendTemplate(string $phoneNumber, array $templateInfo, $message): ?string
    {
        return $this->getProviderService()->sendTemplate($phoneNumber, $templateInfo, $message);
    }

    /**
     * Sync message templates
     */
    public function syncTemplates(): void
    {
        $this->getProviderService()->syncTemplates();
    }

    /**
     * Get media URL
     */
    public function getMediaUrl(string $mediaId): string
    {
        return $this->getProviderService()->getMediaUrl($mediaId);
    }

    /**
     * Get API headers
     */
    public function getApiHeaders(): array
    {
        return $this->getProviderService()->getApiHeaders();
    }

    /**
     * Validate provider configuration
     */
    public function validateConfig(): bool
    {
        return $this->getProviderService()->validateProviderConfig();
    }

    /**
     * Setup webhooks for the channel
     */
    public function setupWebhooks(): void
    {
        try {
            SetupWhatsAppWebhooksJob::dispatch($this);
        } catch (\Exception $e) {
            \Log::error('WhatsApp webhook setup failed', [
                'channel_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            
            $this->promptReauthorization();
        }
    }

    /**
     * Teardown webhooks
     */
    public function teardownWebhooks(): void
    {
        // Implementation depends on provider
        // For now, just log the action
        \Log::info('WhatsApp webhooks teardown', ['channel_id' => $this->id]);
    }

    /**
     * Mark message templates as updated
     */
    public function markMessageTemplatesUpdated(): void
    {
        $this->update(['message_templates_last_updated' => now()]);
    }

    /**
     * Ensure webhook verify token exists
     */
    protected function ensureWebhookVerifyToken(): void
    {
        if ($this->provider === self::PROVIDER_CLOUD) {
            $config = $this->provider_config ?? [];
            
            if (empty($config['webhook_verify_token'])) {
                $config['webhook_verify_token'] = Str::random(32);
                $this->provider_config = $config;
            }
        }
    }

    /**
     * Validation rules
     */
    public static function validationRules(): array
    {
        return [
            'account_id' => 'required|exists:accounts,id',
            'phone_number' => 'required|string|unique:channel_whatsapp,phone_number',
            'provider' => 'required|in:' . implode(',', self::PROVIDERS),
            'provider_config' => 'required|array',
            'provider_config.api_key' => 'required|string',
        ];
    }

    /**
     * Get validation rules for specific provider
     */
    public static function getProviderValidationRules(string $provider): array
    {
        $baseRules = self::validationRules();
        
        return match ($provider) {
            self::PROVIDER_CLOUD => array_merge($baseRules, [
                'provider_config.phone_number_id' => 'required|string',
                'provider_config.business_account_id' => 'required|string',
            ]),
            default => $baseRules,
        };
    }
}
