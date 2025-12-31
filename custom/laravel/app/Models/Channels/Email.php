<?php

namespace App\Models\Channels;

use App\Models\Inbox;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Email extends Model
{
    use HasFactory;

    protected $table = 'channel_email';

    protected $fillable = [
        'account_id',
        'email',
        'forward_to_email',
        'imap_address',
        'imap_port',
        'imap_login',
        'imap_password',
        'imap_enabled',
        'imap_enable_ssl',
        'smtp_address',
        'smtp_port',
        'smtp_login',
        'smtp_password',
        'smtp_enabled',
        'smtp_domain',
        'smtp_enable_starttls_auto',
        'smtp_authentication',
        'smtp_openssl_verify_mode',
        'smtp_enable_ssl_tls',
        'provider_config',
        'provider',
        'verified_for_sending',
    ];

    protected $hidden = [
        'imap_password',
        'smtp_password',
    ];

    protected $casts = [
        'imap_enabled' => 'boolean',
        'imap_enable_ssl' => 'boolean',
        'smtp_enabled' => 'boolean',
        'smtp_enable_starttls_auto' => 'boolean',
        'smtp_enable_ssl_tls' => 'boolean',
        'provider_config' => 'array',
        'verified_for_sending' => 'boolean',
    ];

    /**
     * Get the inbox for this channel.
     */
    public function inbox(): MorphOne
    {
        return $this->morphOne(Inbox::class, 'channel');
    }
}
