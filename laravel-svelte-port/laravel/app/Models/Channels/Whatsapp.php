<?php

namespace App\Models\Channels;

use App\Jobs\Channels\SyncWhatsAppTemplatesJob;
use App\Models\Account;
use App\Models\Inbox;
use App\Models\Message;
use App\Services\Channels\Whatsapp\Providers\BaseService;
use App\Services\Channels\Whatsapp\Providers\WhatsappCloudService;
use App\Services\Channels\Whatsapp\WebhookSetupService;
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
    public const PROVIDER_CLOUD = 'whatsapp_cloud';

    public const PROVIDERS = [
        self::PROVIDER_CLOUD,
    ];

    protected $fillable = [
        'account_id',
        'phone_number',
        'provider',
        'provider_config',
        'message_templates',
        'message_templates_last_updated',
    ];

    protected $casts = [
        'provider_config' => 'array',
        'message_templates' => 'array',
        'message_templates_last_updated' => 'datetime',
    ];

    /**
     * Get the provider config attribute with default empty array
     */
    public function getProviderConfigAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * Get the message templates attribute with default empty array
     */
    public function getMessageTemplatesAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

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
     * Get the provider service instance
     */
    public function providerService(): BaseService
    {
        if ($this->provider !== self::PROVIDER_CLOUD) {
            throw new \InvalidArgumentException("Unsupported WhatsApp provider [{$this->provider}]");
        }

        return WhatsappCloudService::make($this);
    }

    /**
     * Send message via provider service
     */
    public function sendMessage(string $phoneNumber, Message $message): ?string
    {
        return $this->providerService()->sendMessage($phoneNumber, $message);
    }

    /**
     * Send template via provider service
     */
    public function sendTemplate(string $phoneNumber, array $templateInfo, Message $message): ?string
    {
        return $this->providerService()->sendTemplate($phoneNumber, $templateInfo, $message);
    }

    /**
     * Ensure webhook verify token is set (Rails parity)
     * Only sets token for whatsapp_cloud provider in provider_config
     */
    public function ensureWebhookVerifyToken(): void
    {
        if ($this->provider === 'whatsapp_cloud') {
            $config = $this->provider_config ?? [];
            if (empty($config['webhook_verify_token'])) {
                $config['webhook_verify_token'] = bin2hex(random_bytes(16));
                $this->provider_config = $config;
            }
        }
    }

    /**
     * Sync templates via provider service
     */
    public function syncTemplates(): void
    {
        $this->providerService()->syncTemplates();
    }

    /**
     * Get media URL via provider service
     */
    public function mediaUrl(string $mediaId): string
    {
        return $this->providerService()->mediaUrl($mediaId);
    }

    /**
     * Get API headers via provider service
     */
    public function apiHeaders(): array
    {
        return $this->providerService()->apiHeaders();
    }

    /**
     * Setup webhooks for this channel
     */
    public function setupWebhooks(): void
    {
        $businessAccountId = $this->provider_config['business_account_id'] ?? null;
        $apiKey = $this->provider_config['api_key'] ?? null;

        if (!$businessAccountId || !$apiKey) {
            throw new \InvalidArgumentException('Business account ID and API key are required for webhook setup');
        }

        $webhookService = new WebhookSetupService($this, $businessAccountId, $apiKey);
        $webhookService->perform();
    }

    /**
     * Validate provider configuration
     */
    public function validateProviderConfig(): bool
    {
        return $this->providerService()->validateProviderConfig();
    }

    /**
     * Mark message templates as updated
     */
    public function markMessageTemplatesUpdated(): void
    {
        $this->update(['message_templates_last_updated' => now()]);
    }
}
