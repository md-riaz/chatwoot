<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    use HasFactory;

    // File type constants
    public const TYPE_IMAGE = 0;
    public const TYPE_AUDIO = 1;
    public const TYPE_VIDEO = 2;
    public const TYPE_FILE = 3;
    public const TYPE_LOCATION = 4;
    public const TYPE_FALLBACK = 5;

    protected $fillable = [
        'message_id',
        'account_id',
        'file_type',
        'external_url',
        'coordinates_lat',
        'coordinates_long',
        'fallback_title',
        'extension',
    ];

    protected $casts = [
        'file_type' => 'integer',
    ];

    /**
     * Get the message that owns the attachment.
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Get the account that owns the attachment.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
