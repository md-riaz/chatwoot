<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class InstallationConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'serialized_value',
        'locked',
        'display_title',
        'description',
        'type',
        'options',
    ];

    protected $casts = [
        'serialized_value' => 'array',
        'locked' => 'boolean',
        'options' => 'array',
    ];

    /**
     * Configuration types.
     */
    public const TYPES = [
        'text' => 'text',
        'boolean' => 'boolean',
        'integer' => 'integer',
        'float' => 'float',
        'array' => 'array',
        'select' => 'select',
        'secret' => 'secret',
        'code' => 'code',
    ];

    /**
     * Scope to get editable configs.
     */
    public function scopeEditable($query)
    {
        return $query->where('locked', false);
    }

    /**
     * Get the value attribute.
     */
    public function getValueAttribute()
    {
        return $this->serialized_value['value'] ?? null;
    }

    /**
     * Set the value attribute.
     */
    public function setValueAttribute($value): void
    {
        $this->serialized_value = ['value' => $value];
    }

    /**
     * Get type-casted value based on configuration type.
     */
    public function getTypeCastedValue()
    {
        $value = $this->value;
        
        if ($value === null) {
            return null;
        }

        return match ($this->type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            'array' => is_array($value) ? $value : json_decode($value, true),
            'select' => $value,
            'secret' => $value, // Keep as-is for secrets
            'code' => $value, // Keep as-is for code
            default => (string) $value,
        };
    }

    /**
     * Validate value against configuration type and options.
     */
    public function validateValue($value): bool
    {
        return match ($this->type) {
            'boolean' => is_bool($value) || in_array($value, ['true', 'false', '1', '0', 1, 0]),
            'integer' => is_numeric($value) && is_int($value + 0),
            'float' => is_numeric($value),
            'array' => is_array($value) || is_string($value),
            'select' => $this->options && in_array($value, $this->options),
            default => true, // text, secret, code accept any string
        };
    }

    /**
     * Clear cache after save.
     */
    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('installation_configs');
            // Clear specific config cache
            Cache::forget('global_config:' . static::$name ?? '');
        });

        static::deleted(function ($config) {
            Cache::forget('installation_configs');
            Cache::forget('global_config:' . $config->name);
        });
    }

    /**
     * Get config by name.
     */
    public static function getConfig(string $name, $default = null)
    {
        $config = static::where('name', $name)->first();

        return $config ? $config->getTypeCastedValue() : $default;
    }

    /**
     * Set config by name.
     */
    public static function setConfig(string $name, $value, bool $locked = false): self
    {
        return static::updateOrCreate(
            ['name' => $name],
            [
                'serialized_value' => ['value' => $value],
                'locked' => $locked,
            ]
        );
    }

    /**
     * Get config with metadata.
     */
    public static function getConfigWithMetadata(string $name): ?array
    {
        $config = static::where('name', $name)->first();
        
        if (!$config) {
            return null;
        }

        return [
            'name' => $config->name,
            'value' => $config->getTypeCastedValue(),
            'display_title' => $config->display_title,
            'description' => $config->description,
            'type' => $config->type,
            'locked' => $config->locked,
            'options' => $config->options,
        ];
    }

    /**
     * Get config groups mapping.
     */
    public static function getConfigGroups(): array
    {
        return [
            'general' => [
                'ENABLE_ACCOUNT_SIGNUP',
                'FIREBASE_PROJECT_ID',
                'FIREBASE_CREDENTIALS',
                'WEBHOOK_TIMEOUT',
                'MAXIMUM_FILE_UPLOAD_SIZE',
            ],
            'facebook' => [
                'FB_APP_ID',
                'FB_VERIFY_TOKEN',
                'FB_APP_SECRET',
                'IG_VERIFY_TOKEN',
                'FACEBOOK_API_VERSION',
                'ENABLE_MESSENGER_CHANNEL_HUMAN_AGENT',
            ],
            'shopify' => [
                'SHOPIFY_CLIENT_ID',
                'SHOPIFY_CLIENT_SECRET',
            ],
            'microsoft' => [
                'AZURE_APP_ID',
                'AZURE_APP_SECRET',
            ],
            'email' => [
                'MAILER_INBOUND_EMAIL_DOMAIN',
            ],
            'linear' => [
                'LINEAR_CLIENT_ID',
                'LINEAR_CLIENT_SECRET',
            ],
            'slack' => [
                'SLACK_CLIENT_ID',
                'SLACK_CLIENT_SECRET',
            ],
            'instagram' => [
                'INSTAGRAM_APP_ID',
                'INSTAGRAM_APP_SECRET',
                'INSTAGRAM_VERIFY_TOKEN',
                'INSTAGRAM_API_VERSION',
                'ENABLE_INSTAGRAM_CHANNEL_HUMAN_AGENT',
            ],
            'whatsapp_embedded' => [
                'WHATSAPP_APP_ID',
                'WHATSAPP_APP_SECRET',
                'WHATSAPP_CONFIGURATION_ID',
                'WHATSAPP_API_VERSION',
            ],
            'google' => [
                'GOOGLE_OAUTH_CLIENT_ID',
                'GOOGLE_OAUTH_CLIENT_SECRET',
                'GOOGLE_OAUTH_REDIRECT_URI',
                'ENABLE_GOOGLE_OAUTH_LOGIN',
            ],
        ];
    }

    /**
     * Get default configuration definitions.
     */
    public static function getDefaultConfigurations(): array
    {
        return [
            [
                'name' => 'ENABLE_ACCOUNT_SIGNUP',
                'display_title' => 'Enable Account Signup',
                'description' => 'Allow users to signup for new accounts',
                'value' => false,
                'type' => 'boolean',
                'locked' => false,
            ],
            [
                'name' => 'MAXIMUM_FILE_UPLOAD_SIZE',
                'display_title' => 'Attachment size limit (MB)',
                'description' => 'Maximum attachment size in MB allowed for uploads',
                'value' => 40,
                'type' => 'integer',
                'locked' => false,
            ],
            [
                'name' => 'WEBHOOK_TIMEOUT',
                'display_title' => 'Webhook Timeout (seconds)',
                'description' => 'Timeout for webhook requests in seconds',
                'value' => 5,
                'type' => 'integer',
                'locked' => false,
            ],
            [
                'name' => 'FB_APP_ID',
                'display_title' => 'Facebook App ID',
                'description' => 'Facebook App ID for Messenger integration',
                'value' => null,
                'type' => 'text',
                'locked' => false,
            ],
            [
                'name' => 'FB_VERIFY_TOKEN',
                'display_title' => 'Facebook Verify Token',
                'description' => 'Facebook webhook verification token',
                'value' => null,
                'type' => 'secret',
                'locked' => false,
            ],
        ];
    }
}
