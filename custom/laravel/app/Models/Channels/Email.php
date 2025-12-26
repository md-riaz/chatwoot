<?php

namespace App\Models\Channels;

use App\Models\Inbox;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Email extends Model
{
    use HasFactory;

    protected $table = 'channel_emails';

    protected $fillable = [
        'email',
        'forward_to_email',
        'imap_host',
        'imap_port',
        'imap_login',
        'imap_password',
        'imap_enabled',
        'smtp_host',
        'smtp_port',
        'smtp_login',
        'smtp_password',
        'smtp_enabled',
    ];

    protected $hidden = [
        'imap_password',
        'smtp_password',
    ];

    protected $casts = [
        'imap_enabled' => 'boolean',
        'smtp_enabled' => 'boolean',
    ];

    /**
     * Get the inbox for this channel.
     */
    public function inbox(): MorphOne
    {
        return $this->morphOne(Inbox::class, 'channel');
    }
}
