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
    ];

    protected $casts = [
        'serialized_value' => 'array',
        'locked' => 'boolean',
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
     * Clear cache after save.
     */
    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('installation_configs');
        });
    }

    /**
     * Get config by name.
     */
    public static function getConfig(string $name, $default = null)
    {
        $config = static::where('name', $name)->first();

        return $config ? $config->value : $default;
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
}
