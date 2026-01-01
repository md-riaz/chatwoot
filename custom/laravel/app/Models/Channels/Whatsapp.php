<?php

namespace App\Models\Channels;

use App\Models\Account;
use App\Models\Inbox;
use App\Models\Message;
use App\Services\Channels\Whatsapp\Providers\BaseService;
use App\Services\Channels\Whatsapp\Providers\WhatsappCloudService;
use App\Services\Channels\Whatsapp\Providers\Whatsapp360DialogService;
use App\Services\Channels\Whatsapp\WebhookSetupService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Whatsapp extends Model
{
    use HasFactory;
    use \App\Traits\Reauthorizable;

    protected $table = 'channel_whatsapp';

    // Provider constants
    public const PROVIDER_DEFAULT = 'default';
    public const PROVIDER_CLOUD = 'whatsapp_cloud';
    public const PROVIDER_360_DIALOG = '360dialog';

    public const PROVIDERS = [
        self::PROVIDER_DEFAULT,
        self::PROVIDER_CLOUD,
        self::PROVIDER_360_DIALOG,
    ];

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
        return match ($this->provider) {
            self::PROVIDER_CLOUD => WhatsappCloudService::make($this),
            default => Whatsapp360DialogService::make($this),
        };
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
