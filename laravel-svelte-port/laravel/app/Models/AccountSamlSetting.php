<?php

namespace App\Models;

use App\Jobs\Saml\UpdateAccountUsersProviderJob;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Config;

class AccountSamlSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'enabled',
        'issuer',
        'sso_url',
        'certificate',
        'entity_id',
        'sp_entity_id',
        'idp_entity_id',
        'metadata',
        'role_mappings',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'metadata' => 'array',
        'role_mappings' => 'array',
    ];

    protected $hidden = [
        'certificate',
    ];

    protected static function booted(): void
    {
        static::creating(function (AccountSamlSetting $setting) {
            if (empty($setting->sp_entity_id)) {
                $setting->sp_entity_id = $setting->generateSpEntityId();
            }
        });

        static::updating(function (AccountSamlSetting $setting) {
            if (empty($setting->sp_entity_id)) {
                $setting->sp_entity_id = $setting->generateSpEntityId();
            }
        });

        static::created(function (AccountSamlSetting $setting) {
            UpdateAccountUsersProviderJob::dispatch($setting->account_id, 'saml');
        });

        static::deleted(function (AccountSamlSetting $setting) {
            UpdateAccountUsersProviderJob::dispatch($setting->account_id, 'email');
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Check if SAML is enabled and properly configured
     */
    public function samlEnabled(): bool
    {
        return !empty($this->sso_url) && !empty($this->certificate);
    }

    /**
     * Generate certificate fingerprint for SAML configuration
     */
    public function getCertificateFingerprintAttribute(): ?string
    {
        if (empty($this->certificate)) {
            return null;
        }

        try {
            $cert = openssl_x509_read($this->certificate);
            if (!$cert) {
                return null;
            }

            $fingerprint = openssl_x509_fingerprint($cert, 'sha1');
            if (!$fingerprint) {
                return null;
            }

            // Format fingerprint with colons like Rails implementation
            return strtoupper(implode(':', str_split($fingerprint, 2)));
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Generate SP Entity ID based on account ID
     */
    private function generateSpEntityId(): string
    {
        $baseUrl = Config::get('app.frontend_url', Config::get('app.url', 'http://localhost:3000'));
        return "{$baseUrl}/saml/sp/{$this->account_id}";
    }

    /**
     * Validate X.509 certificate format
     */
    public function validateCertificate(): bool
    {
        if (empty($this->certificate)) {
            return false;
        }

        try {
            $cert = openssl_x509_read($this->certificate);
            return $cert !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
